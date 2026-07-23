<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocUploaded extends Notification
{
    use Queueable;

    public function __construct(
        public Document $doc,
        public User $uploader
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Send email only if a mailer is configured (not the default log driver)
        if (config('mail.default') !== 'log') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Document Uploaded: {$this->doc->reference_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->uploader->name} registered a new document.")
            ->line("**{$this->doc->reference_number}** — {$this->doc->title}")
            ->line("Date Received: {$this->doc->date_issued->format('M d, Y')}")
            ->action('View Document', route('documents.show', $this->doc))
            ->line('You are receiving this because you are an administrator of DTMS.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'doc_uploaded',
            'doc_id'        => $this->doc->id,
            'reference_number' => $this->doc->reference_number,
            'title'         => $this->doc->title,
            'uploader_id'   => $this->uploader->id,
            'uploader_name' => $this->uploader->name,
            'message'       => "{$this->uploader->name} uploaded a new document: {$this->doc->reference_number}",
        ];
    }
}
