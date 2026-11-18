<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class TwoFactorCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $code,
        public readonly int $expiresInMinutes = 10,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your '.config('app.name').' verification code')
            ->greeting('Sign-in verification')
            ->line('Use the code below to finish signing in to your account:')
            ->line("**{$this->code}**")
            ->line("This code expires in {$this->expiresInMinutes} minutes.")
            ->line('If you did not try to sign in, change your password immediately.');
    }
}
