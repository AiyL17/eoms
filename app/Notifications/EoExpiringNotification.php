<?php

namespace App\Notifications;

use App\Models\ExecutiveOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EoExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ExecutiveOrder $eo,
        public int $daysLeft
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
        $deletionDate = $this->eo->deleted_at->addDays(30)->format('M d, Y');

        return (new MailMessage)
            ->subject("⚠️ Archived EO Expiring Soon: {$this->eo->eo_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("The following archived executive order will be **permanently deleted in {$this->daysLeft} day(s)** on {$deletionDate}.")
            ->line("**{$this->eo->eo_number}** — {$this->eo->title}")
            ->line("Archived on: {$this->eo->deleted_at->format('M d, Y')}")
            ->action('Go to Archive', route('executive-orders.archive'))
            ->line('To prevent permanent deletion, restore the record from the Archive page before the deadline.')
            ->line('You are receiving this because you are an administrator of EOMS.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'        => 'eo_expiring',
            'eo_id'       => $this->eo->id,
            'eo_number'   => $this->eo->eo_number,
            'title'       => $this->eo->title,
            'days_left'   => $this->daysLeft,
            'deleted_at'  => $this->eo->deleted_at->toDateString(),
            'message'     => "{$this->eo->eo_number} ({$this->eo->title}) will be permanently deleted in {$this->daysLeft} day(s).",
        ];
    }
}
