<?php

namespace App\Notifications;

use App\Models\ExecutiveOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EoStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public ExecutiveOrder $eo,
        public string $oldStatus,
        public string $newStatus,
        public User $changedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $oldLabel = ucfirst(str_replace('_', ' ', $this->oldStatus));
        $newLabel = ucfirst(str_replace('_', ' ', $this->newStatus));

        return [
            'type'          => 'eo_status_changed',
            'eo_id'         => $this->eo->id,
            'eo_number'     => $this->eo->eo_number,
            'title'         => $this->eo->title,
            'old_status'    => $this->oldStatus,
            'new_status'    => $this->newStatus,
            'changed_by_id' => $this->changedBy->id,
            'changed_by_name' => $this->changedBy->name,
            'message'       => "{$this->eo->eo_number} status changed from {$oldLabel} to {$newLabel} by {$this->changedBy->name}",
        ];
    }
}
