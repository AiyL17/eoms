<?php

namespace App\Notifications;

use App\Models\ExecutiveOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EoDeleted extends Notification
{
    use Queueable;

    public function __construct(
        public string $eoNumber,
        public string $eoTitle,
        public User $deletedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'             => 'eo_archived',
            'eo_number'        => $this->eoNumber,
            'title'            => $this->eoTitle,
            'deleted_by_id'    => $this->deletedBy->id,
            'deleted_by_name'  => $this->deletedBy->name,
            'message'          => "{$this->eoNumber} ({$this->eoTitle}) was archived by {$this->deletedBy->name}",
        ];
    }
}
