<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Event $event) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Event Submission — ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.event-verification',
            with: [
                'event'           => $this->event,
                'verificationUrl' => route('submit.verify', $this->event->verification_token),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
