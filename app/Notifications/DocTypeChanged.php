<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocTypeChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Document $doc,
        public readonly User $changedBy,
        public readonly string $oldType,
        public readonly string $newType,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $oldLabel = ucfirst($this->oldType);
        $newLabel = ucfirst($this->newType);

        return (new MailMessage)
            ->subject("Document Type Changed: {$this->doc->doc_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->changedBy->name} changed the document type of **{$this->doc->doc_number}** from **{$oldLabel}** to **{$newLabel}**.")
            ->line("**Document:** {$this->doc->title}")
            ->line("**Office / Origin:** {$this->doc->received_from}")
            ->line("**Recipient:** {$this->doc->recipient}")
            ->action('View Document', route('documents.show', $this->doc))
            ->line('You are receiving this because you are a member of the DTMS team.');
    }

    public function toArray(object $notifiable): array
    {
        $oldLabel = ucfirst($this->oldType);
        $newLabel = ucfirst($this->newType);

        return [
            'type'            => 'doc_type_changed',
            'doc_id'          => $this->doc->id,
            'doc_number'      => $this->doc->doc_number,
            'title'           => $this->doc->title,
            'old_type'        => $this->oldType,
            'new_type'        => $this->newType,
            'changed_by_id'   => $this->changedBy->id,
            'changed_by_name' => $this->changedBy->name,
            'message'         => "{$this->doc->doc_number} — \"{$this->doc->title}\" was changed from {$oldLabel} to {$newLabel} by {$this->changedBy->name}.",
            'url'             => route('documents.show', $this->doc),
        ];
    }
}
