<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Voca')
            ->greeting('Welcome, '.$notifiable->name.'!')
            ->line('Your account has been created successfully.')
            ->line('Discover seminars, workshops, camps and more tailored to your interests.')
            ->action('Browse opportunities', config('app.frontend_url').'/calls')
            ->line('Thank you for joining Voca!');
    }
}
