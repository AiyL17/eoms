<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocTypeChanged extends Notification
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
        $oldLabel    = ucfirst($this->oldType);
        $newLabel    = ucfirst($this->newType);
        $changerRole = ucfirst($this->changedBy->role);

        return (new MailMessage)
            ->subject("Document Type Changed: {$this->doc->reference_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->changedBy->name} ({$changerRole}) changed the document type of **{$this->doc->reference_number}** from **{$oldLabel}** to **{$newLabel}**.")
            ->line("**Document:** {$this->doc->title}")
            ->line("**Office / Origin:** {$this->doc->received_from}")
            ->line("**Recipient:** {$this->doc->recipient}")
            ->action('View Document', route('documents.show', $this->doc))
            ->line('You are receiving this because you are a member of the DTMS team.');
    }

    public function toArray(object $notifiable): array
    {
        $oldLabel    = ucfirst($this->oldType);
        $newLabel    = ucfirst($this->newType);
        $changerRole = ucfirst($this->changedBy->role);

        return [
            'type'            => 'doc_type_changed',
            'doc_id'          => $this->doc->id,
            'reference_number' => $this->doc->reference_number,
            'title'           => $this->doc->title,
            'old_type'        => $this->oldType,
            'new_type'        => $this->newType,
            'changed_by_id'   => $this->changedBy->id,
            'changed_by_name' => $this->changedBy->name,
            'message'         => "{$this->changedBy->name} ({$changerRole}) changed {$this->doc->reference_number} document type from {$oldLabel} to {$newLabel}.",
            'url'             => route('documents.show', $this->doc),
        ];
    }
}
