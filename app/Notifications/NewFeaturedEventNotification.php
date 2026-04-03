<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFeaturedEventNotification extends Notification implements ShouldQueue
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
            ->subject('★ New Featured Event Payment Received — ' . $this->event->title)
            ->greeting('New Featured Event Payment Received')
            ->line('An organizer has paid to feature their event in the carousel for 30 days.')
            ->line('**' . $this->event->title . '**')
            ->line('Date: ' . $this->event->start_date->format('F j, Y'))
            ->line('Featured until: ' . now()->addDays(30)->format('F j, Y'))
            ->action('View Featured Events', url('/admin/featured-events'))
            ->line('The event is now live in the featured carousel.');
    }

    public function toArray(object $notifiable): array
    {
        return ['event_id' => $this->event->id];
    }
}
