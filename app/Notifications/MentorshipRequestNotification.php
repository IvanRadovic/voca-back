<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentorshipRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $requester, public string $body)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New mentorship request via BIP TECH')
            ->line($this->requester->name.' would like a mentoring conversation.')
            ->line('Message: '.$this->body)
            ->line('Reply to: '.$this->requester->email);
    }
}
