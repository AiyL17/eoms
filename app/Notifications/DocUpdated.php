<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public Document $doc,
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
            ->subject("Document Updated: {$this->doc->reference_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->updatedBy->name} made changes to a document.")
            ->line("**{$this->doc->reference_number}** — {$this->doc->title}")
            ->action('View Document', route('documents.show', $this->doc))
            ->line('You are receiving this because you are associated with this document or are an DTMS administrator.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'doc_updated',
            'doc_id'          => $this->doc->id,
            'reference_number' => $this->doc->reference_number,
            'title'           => $this->doc->title,
            'updated_by_id'   => $this->updatedBy->id,
            'updated_by_name' => $this->updatedBy->name,
            'message'         => "{$this->doc->reference_number} was updated by {$this->updatedBy->name}",
        ];
    }
}
