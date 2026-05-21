<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->application->status;
        $title = optional($this->application->call)->title;

        return (new MailMessage)
            ->subject('Application update: '.$title)
            ->greeting('Hi '.$notifiable->name.',')
            ->line('The status of your application for "'.$title.'" is now: '.strtoupper($status).'.')
            ->action('View details', config('app.frontend_url').'/profile');
    }
}
