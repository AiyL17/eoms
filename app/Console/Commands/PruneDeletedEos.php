<?php

namespace App\Console\Commands;

use App\Models\ExecutiveOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneDeletedEos extends Command
{
    protected $signature   = 'eo:prune-deleted {--days=30 : Permanently delete EOs soft-deleted more than this many days ago}';
    protected $description = 'Permanently purge soft-deleted Executive Orders (and their archived PDFs) older than the given retention period.';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $candidates = ExecutiveOrder::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays($days))
            ->get();

        if ($candidates->isEmpty()) {
            $this->info("No soft-deleted EOs older than {$days} days found.");
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($candidates as $eo) {
            // Remove the live PDF if it still exists
            if ($eo->pdf_path && Storage::disk('local')->exists($eo->pdf_path)) {
                Storage::disk('local')->delete($eo->pdf_path);
            }

            // Remove any archived (versioned) PDFs
            $archiveDir = 'executive-orders-archive/' . $eo->id;
            if (Storage::disk('local')->directoryExists($archiveDir)) {
                Storage::disk('local')->deleteDirectory($archiveDir);
            }

            $eo->forceDelete();
            $count++;
        }

        $this->info("Permanently purged {$count} soft-deleted EO(s) older than {$days} days.");
        return self::SUCCESS;
    }
}
