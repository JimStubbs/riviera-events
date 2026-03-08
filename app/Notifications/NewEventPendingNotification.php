<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEventPendingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Event $event) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Event Pending Approval — ' . $this->event->title)
            ->greeting('New Event Submission')
            ->line('A new event has been verified and is awaiting your approval.')
            ->line('**' . $this->event->title . '**')
            ->line('Submitted by: ' . $this->event->submitter_email)
            ->line('Date: ' . $this->event->start_date->format('F j, Y'))
            ->action('Review in Admin', url('/admin/events'))
            ->line('Please review and approve or reject this event.');
    }

    public function toArray(object $notifiable): array
    {
        return ['event_id' => $this->event->id];
    }
}
