<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPremiumEventPendingNotification extends Notification implements ShouldQueue
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
            ->subject('⭐ New Premium Event Paid & Pending Approval — ' . $this->event->title)
            ->greeting('New Premium Event Payment Received')
            ->line('A premium event listing payment has been completed and is awaiting your approval.')
            ->line('**' . $this->event->title . '**')
            ->line('Date: ' . $this->event->start_date->format('F j, Y'))
            ->action('Review in Admin', url('/admin/events'))
            ->line('Please review and approve this premium listing.');
    }

    public function toArray(object $notifiable): array
    {
        return ['event_id' => $this->event->id];
    }
}
