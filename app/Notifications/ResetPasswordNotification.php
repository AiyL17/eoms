<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Reset Your DTMS Password')
            ->greeting('Password Reset Request')
            ->line('We received a request to reset the password for your **DTMS** account.')
            ->line('Click the button below to set a new password. This link will expire in **' . config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60) . ' minutes**.')
            ->action('Reset My Password', $url)
            ->line('If you did not request a password reset, no action is needed — your account remains secure.')
            ->salutation('— DTMS Security Team · City Government');
    }
}
