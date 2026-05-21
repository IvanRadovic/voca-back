<?php

namespace App\Notifications;

use App\Models\Call;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Call $call)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Application received: '.$this->call->title)
            ->greeting('Hi '.$notifiable->name.',')
            ->line('We received your application for "'.$this->call->title.'".')
            ->line('You will be notified once the organizer reviews it.')
            ->action('View opportunity', config('app.frontend_url').'/calls/'.$this->call->id);
    }
}
