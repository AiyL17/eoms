<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocExpirationWarning extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Document $doc,
        public readonly int $daysLeft,
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
        $urgency = $this->daysLeft <= 3 ? '🚨' : '⚠️';

        return (new MailMessage)
            ->subject("{$urgency} Document Expiring in {$this->daysLeft} Day(s): {$this->doc->reference_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("The following document will **expire in {$this->daysLeft} day(s)** on **{$this->doc->expiration_date->format('F d, Y')}**.")
            ->line("**{$this->doc->reference_number}** — {$this->doc->title}")
            ->line("Office / Origin: {$this->doc->received_from}")
            ->line("Date Received: {$this->doc->date_issued->format('F d, Y')}")
            ->action('View Document', route('documents.show', $this->doc))
            ->line('Please take the necessary action before the expiration date to avoid disruption.')
            ->line('You are receiving this notification because you are a member of the DTMS team.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'doc_expiration_warning',
            'doc_id'          => $this->doc->id,
            'reference_number' => $this->doc->reference_number,
            'title'           => $this->doc->title,
            'days_left'       => $this->daysLeft,
            'expiration_date' => $this->doc->expiration_date->toDateString(),
            'message'         => "{$this->doc->reference_number} — \"{$this->doc->title}\" expires in {$this->daysLeft} day(s) on {$this->doc->expiration_date->format('M d, Y')}.",
            'url'             => route('documents.show', $this->doc),
        ];
    }
}
