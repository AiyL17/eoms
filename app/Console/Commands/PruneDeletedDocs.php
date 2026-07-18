<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneDeletedDocs extends Command
{
    protected $signature   = 'doc:prune-deleted {--days=30 : Permanently delete documents soft-deleted more than this many days ago}';
    protected $description = 'Permanently purge soft-deleted documents (and their archived PDFs) older than the given retention period.';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $candidates = Document::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays($days))
            ->get();

        if ($candidates->isEmpty()) {
            $this->info("No soft-deleted documents older than {$days} days found.");
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($candidates as $doc) {
            // Remove the live PDF if it still exists
            if ($doc->pdf_path && Storage::disk('local')->exists($doc->pdf_path)) {
                Storage::disk('local')->delete($doc->pdf_path);
            }

            // Remove any archived (versioned) PDFs
            $archiveDir = 'documents-archive/' . $doc->id;
            if (Storage::disk('local')->directoryExists($archiveDir)) {
                Storage::disk('local')->deleteDirectory($archiveDir);
            }

            $doc->forceDelete();
            $count++;
        }

        $this->info("Permanently purged {$count} soft-deleted document(s) older than {$days} days.");
        return self::SUCCESS;
    }
}
