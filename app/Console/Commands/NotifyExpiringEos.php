<?php

namespace App\Console\Commands;

use App\Models\ExecutiveOrder;
use App\Models\User;
use App\Notifications\EoExpiringNotification;
use Illuminate\Console\Command;

class NotifyExpiringEos extends Command
{
    protected $signature   = 'eo:notify-expiring
                                {--days=30 : Total retention period in days (must match eo:prune-deleted)}
                                {--warn=5  : How many days before deletion to send the warning}';

    protected $description = 'Notify administrators of archived EOs that are about to be permanently deleted.';

    public function handle(): int
    {
        $retentionDays = (int) $this->option('days');
        $warnDays      = (int) $this->option('warn');

        // An EO should be warned when it has exactly $warnDays left before deletion,
        // i.e. it was soft-deleted ($retentionDays - $warnDays) days ago.
        $targetDays = $retentionDays - $warnDays;

        // Match EOs deleted between the start and end of the target day
        $windowStart = now()->subDays($targetDays + 1)->endOfDay();
        $windowEnd   = now()->subDays($targetDays)->endOfDay();

        $expiring = ExecutiveOrder::onlyTrashed()
            ->whereBetween('deleted_at', [$windowStart, $windowEnd])
            ->get();

        if ($expiring->isEmpty()) {
            $this->info("No archived EOs expiring in {$warnDays} day(s). Nothing to notify.");
            return self::SUCCESS;
        }

        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found. Skipping notifications.');
            return self::SUCCESS;
        }

        foreach ($expiring as $eo) {
            foreach ($admins as $admin) {
                $admin->notify(new EoExpiringNotification($eo, $warnDays));
            }

            $this->info("Notified admins about expiring EO: {$eo->eo_number}");
        }

        $this->info("Done. Sent expiry warnings for {$expiring->count()} EO(s).");
        return self::SUCCESS;
    }
}
