<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        $channels = ['database'];

        if (config('mail.default') !== 'log') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("EO Archived: {$this->eoNumber}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->deletedBy->name} archived an executive order.")
            ->line("**{$this->eoNumber}** — {$this->eoTitle}")
            ->line('The record has been moved to the archive. Administrators can restore or permanently delete it from the Archive page.')
            ->action('Go to Archive', route('executive-orders.archive'))
            ->line('You are receiving this because you uploaded this executive order or are an EOMS administrator.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'eo_archived',
            'eo_number'       => $this->eoNumber,
            'title'           => $this->eoTitle,
            'deleted_by_id'   => $this->deletedBy->id,
            'deleted_by_name' => $this->deletedBy->name,
            'message'         => "{$this->eoNumber} ({$this->eoTitle}) was archived by {$this->deletedBy->name}",
        ];
    }
}
