<?php

namespace App\Notifications;

use App\Models\ExecutiveOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EoReviewDue extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ExecutiveOrder $eo,
        public readonly int $years = 1,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Review Reminder: {$this->eo->eo_number} is {$this->years}-year(s) old")
            ->greeting("Hello {$notifiable->name},")
            ->line("Executive Order **{$this->eo->eo_number}** — *{$this->eo->title}* — has been active for {$this->years} year(s) and may be due for a periodic review.")
            ->line("Date Issued: {$this->eo->date_issued->format('F d, Y')}")
            ->line("Signed By: {$this->eo->signed_by}")
            ->action('Review EO', route('executive-orders.show', $this->eo))
            ->line('If no action is needed, you may dismiss this notification.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'eo_review_due',
            'eo_id'      => $this->eo->id,
            'eo_number'  => $this->eo->eo_number,
            'eo_title'   => $this->eo->title,
            'years'      => $this->years,
            'message'    => "{$this->eo->eo_number} is due for a {$this->years}-year review.",
            'url'        => route('executive-orders.show', $this->eo),
        ];
    }
}
