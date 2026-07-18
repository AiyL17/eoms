<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\User;
use App\Notifications\DocExpiringNotification;
use Illuminate\Console\Command;

class NotifyExpiringDocs extends Command
{
    protected $signature   = 'doc:notify-expiring
                                {--days=30 : Total retention period in days (must match doc:prune-deleted)}
                                {--warn=5  : How many days before deletion to send the warning}';

    protected $description = 'Notify administrators of archived documents that are about to be permanently deleted.';

    public function handle(): int
    {
        $retentionDays = (int) $this->option('days');
        $warnDays      = (int) $this->option('warn');

        // A document should be warned when it has exactly $warnDays left before deletion,
        // i.e. it was soft-deleted ($retentionDays - $warnDays) days ago.
        $targetDays = $retentionDays - $warnDays;

        // Match documents deleted between the start and end of the target day
        $windowStart = now()->subDays($targetDays + 1)->endOfDay();
        $windowEnd   = now()->subDays($targetDays)->endOfDay();

        $expiring = Document::onlyTrashed()
            ->whereBetween('deleted_at', [$windowStart, $windowEnd])
            ->get();

        if ($expiring->isEmpty()) {
            $this->info("No archived documents expiring in {$warnDays} day(s). Nothing to notify.");
            return self::SUCCESS;
        }

        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found. Skipping notifications.');
            return self::SUCCESS;
        }

        foreach ($expiring as $doc) {
            foreach ($admins as $admin) {
                $admin->notify(new DocExpiringNotification($doc, $warnDays));
            }

            $this->info("Notified admins about expiring document: {$doc->doc_number}");
        }

        $this->info("Done. Sent expiry warnings for {$expiring->count()} document(s).");
        return self::SUCCESS;
    }
}
