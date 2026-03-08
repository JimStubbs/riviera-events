<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Event $event) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your event is live — ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.event-approved',
            with: [
                'event'    => $this->event,
                'eventUrl' => route('events.show', $this->event),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
