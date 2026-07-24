<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Document $doc,
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
        $deletionDate = $this->doc->deleted_at->addDays(30)->format('M d, Y');

        return (new MailMessage)
            ->subject("⚠️ Archived Document Expiring Soon: {$this->doc->reference_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("The following archived document will be **permanently deleted in {$this->daysLeft} day(s)** on {$deletionDate}.")
            ->line("**{$this->doc->reference_number}** — {$this->doc->title}")
            ->line("Archived on: {$this->doc->deleted_at->format('M d, Y')}")
            ->action('Go to Archive', route('documents.archive'))
            ->line('To prevent permanent deletion, restore the record from the Archive page before the deadline.')
            ->line('You are receiving this notification because you are a DTMS administrator.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'        => 'doc_expiring',
            'doc_id'      => $this->doc->id,
            'reference_number' => $this->doc->reference_number,
            'title'       => $this->doc->title,
            'days_left'   => $this->daysLeft,
            'deleted_at'  => $this->doc->deleted_at->toDateString(),
            'message'     => "{$this->doc->reference_number} ({$this->doc->title}) will be permanently deleted in {$this->daysLeft} day(s).",
        ];
    }
}
