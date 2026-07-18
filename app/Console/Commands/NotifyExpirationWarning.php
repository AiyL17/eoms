<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\User;
use App\Notifications\DocExpirationWarning;
use Illuminate\Console\Command;

class NotifyExpirationWarning extends Command
{
    protected $signature = 'doc:notify-expiration-warning
                                {--days=* : Days-before-expiration thresholds to notify on (default: 30 7 3 1)}';

    protected $description = 'Notify all admins and staff when a document\'s expiration date is approaching.';

    public function handle(): int
    {
        // Default warning thresholds: 30, 7, 3, and 1 day(s) before expiration
        $thresholds = array_map('intval', $this->option('days') ?: [30, 7, 3, 1]);

        $recipients = User::whereIn('role', ['admin', 'staff'])->get();

        if ($recipients->isEmpty()) {
            $this->warn('No admin or staff users found. Skipping notifications.');
            return self::SUCCESS;
        }

        $totalNotified = 0;

        foreach ($thresholds as $days) {
            // Match documents whose expiration_date is exactly $days from today
            $targetDate = now()->addDays($days)->toDateString();

            $expiring = Document::whereNotNull('expiration_date')
                ->whereDate('expiration_date', $targetDate)
                ->whereNull('deleted_at')
                ->get();

            if ($expiring->isEmpty()) {
                $this->info("No documents expiring in exactly {$days} day(s). Skipping.");
                continue;
            }

            foreach ($expiring as $doc) {
                foreach ($recipients as $user) {
                    $user->notify(new DocExpirationWarning($doc, $days));
                }

                $this->info("Notified {$recipients->count()} user(s) — {$doc->doc_number} expires in {$days} day(s).");
                $totalNotified++;
            }
        }

        if ($totalNotified > 0) {
            $this->info("Done. Sent expiration warnings for {$totalNotified} document(s).");
        } else {
            $this->info('No expiration warnings to send today.');
        }

        return self::SUCCESS;
    }
}
