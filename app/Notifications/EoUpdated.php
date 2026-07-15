<?php

namespace App\Notifications;

use App\Models\ExecutiveOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        $channels = ['database'];

        if (config('mail.default') !== 'log') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("EO Updated: {$this->eo->eo_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->updatedBy->name} made changes to an executive order.")
            ->line("**{$this->eo->eo_number}** — {$this->eo->title}")
            ->action('View Executive Order', route('executive-orders.show', $this->eo))
            ->line('You are receiving this because you are associated with this executive order or are an EOMS administrator.');
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
