<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocDeleted extends Notification
{
    use Queueable;

    public function __construct(
        public string $docNumber,
        public string $docTitle,
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
            ->subject("Document Archived: {$this->docNumber}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->deletedBy->name} archived a document.")
            ->line("**{$this->docNumber}** — {$this->docTitle}")
            ->line('The record has been moved to the archive. Administrators can restore or permanently delete it from the Archive page.')
            ->action('Go to Archive', route('documents.archive'))
            ->line('You are receiving this notification because you uploaded this document or are a DTMS administrator.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'doc_archived',
            'reference_number' => $this->docNumber,
            'title'           => $this->docTitle,
            'deleted_by_id'   => $this->deletedBy->id,
            'deleted_by_name' => $this->deletedBy->name,
            'message'         => "{$this->docNumber} ({$this->docTitle}) was archived by {$this->deletedBy->name}",
        ];
    }
}
