<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class CrmAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var list<int> */
    public array $backoff = [30, 120, 300];

    public function __construct(
        public readonly string $category,
        public readonly string $title,
        public readonly string $message,
        public readonly ?string $actionUrl = null,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof User || ! $notifiable->is_active) {
            return [];
        }

        $channels = [];

        if ($notifiable->wantsNotificationChannel($this->category, 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->wantsNotificationChannel($this->category, 'mail')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->line($this->message);

        if ($this->actionUrl !== null) {
            $mail->action('View in Sales CRM', $this->actionUrl);
        }

        return $mail;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category,
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
        ];
    }
}
