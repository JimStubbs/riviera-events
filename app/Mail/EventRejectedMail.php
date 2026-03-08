<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Event $event) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on your event submission — ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.event-rejected',
            with: [
                'event'      => $this->event,
                'submitUrl'  => route('submit.create'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
