<?php

namespace App\Notifications;

use App\Models\ExecutiveOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EoUploaded extends Notification
{
    use Queueable;

    public function __construct(
        public ExecutiveOrder $eo,
        public User $uploader
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'         => 'eo_uploaded',
            'eo_id'        => $this->eo->id,
            'eo_number'    => $this->eo->eo_number,
            'title'        => $this->eo->title,
            'uploader_id'  => $this->uploader->id,
            'uploader_name'=> $this->uploader->name,
            'message'      => "{$this->uploader->name} uploaded a new executive order: {$this->eo->eo_number}",
        ];
    }
}
