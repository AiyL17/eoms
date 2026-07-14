<?php

namespace App\Notifications;

use App\Models\ExecutiveOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EoUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public ExecutiveOrder $eo,
        public User $updatedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'eo_updated',
            'eo_id'           => $this->eo->id,
            'eo_number'       => $this->eo->eo_number,
            'title'           => $this->eo->title,
            'updated_by_id'   => $this->updatedBy->id,
            'updated_by_name' => $this->updatedBy->name,
            'message'         => "{$this->eo->eo_number} was updated by {$this->updatedBy->name}",
        ];
    }
}
