<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    public array $backoff = [60, 300, 900];

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify your email — CineBook')
            ->greeting('Welcome to CineBook!')
            ->line('Confirm your email address to finish setting up your account and start booking seats.')
            ->action('Verify email address', $this->verificationUrl($notifiable))
            ->line('This link is valid for 60 minutes. If it expires, open the verify page and request a new one.')
            ->line('If you did not create a CineBook account, no further action is required.')
            ->salutation('— CineBook Downtown');
    }
}
