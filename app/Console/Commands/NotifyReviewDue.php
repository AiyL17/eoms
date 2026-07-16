<?php

namespace App\Console\Commands;

use App\Models\ExecutiveOrder;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\EoReviewDue;
use Illuminate\Console\Command;

class NotifyReviewDue extends Command
{
    protected $signature = 'eo:notify-review-due
                                {--years=1 : Trigger when an EO has been active for this many years}';

    protected $description = 'Notify administrators of active EOs that are due for an annual review.';

    public function handle(): int
    {
        $years = (int) $this->option('years');

        // Find EOs that have been active and were issued exactly $years year(s) ago today,
        // or whose date_effective hits the anniversary today
        $targetDate = now()->subYears($years)->toDateString();

        $due = ExecutiveOrder::where('status', 'active')
            ->where(function ($q) use ($targetDate) {
                $q->whereDate('date_issued', $targetDate)
                  ->orWhere(function ($q2) use ($targetDate) {
                      $q2->whereNotNull('date_effective')
                         ->whereDate('date_effective', $targetDate);
                  });
            })
            ->get();

        if ($due->isEmpty()) {
            $this->info("No EOs due for review today (anniversary: {$targetDate}). Nothing to notify.");
            return self::SUCCESS;
        }

        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found. Skipping notifications.');
            return self::SUCCESS;
        }

        foreach ($due as $eo) {
            foreach ($admins as $admin) {
                $admin->notify(new EoReviewDue($eo, $years));
            }
            $this->info("Notified admins about review-due EO: {$eo->eo_number} ({$years}-year anniversary)");
        }

        $this->info("Done. Sent review reminders for {$due->count()} EO(s).");
        return self::SUCCESS;
    }
}
