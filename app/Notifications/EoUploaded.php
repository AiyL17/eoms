<?php

namespace App\Notifications;

use App\Models\ExecutiveOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EoUploaded extends Notification
{
    use Queueable;

    public function __construct(
        public ExecutiveOrder $eo,
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
            ->subject("New EO Uploaded: {$this->eo->eo_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->uploader->name} uploaded a new executive order.")
            ->line("**{$this->eo->eo_number}** — {$this->eo->title}")
            ->line("Status: {$this->eo->status_label} | Date Issued: {$this->eo->date_issued->format('M d, Y')}")
            ->action('View Executive Order', route('executive-orders.show', $this->eo))
            ->line('You are receiving this because you are an administrator of EOMS.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'eo_uploaded',
            'eo_id'         => $this->eo->id,
            'eo_number'     => $this->eo->eo_number,
            'title'         => $this->eo->title,
            'uploader_id'   => $this->uploader->id,
            'uploader_name' => $this->uploader->name,
            'message'       => "{$this->uploader->name} uploaded a new executive order: {$this->eo->eo_number}",
        ];
    }
}
