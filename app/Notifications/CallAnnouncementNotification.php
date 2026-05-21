<?php

namespace App\Notifications;

use App\Models\Call;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CallAnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Call $call,
        public string $announcementSubject,
        public string $announcementBody,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->announcementSubject)
            ->greeting('Hi '.$notifiable->name.',')
            ->line('Update regarding "'.$this->call->title.'":')
            ->line($this->announcementBody)
            ->action('View opportunity', config('app.frontend_url').'/calls/'.$this->call->id);
    }
}
