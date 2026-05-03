<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\RecurringEventSeries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecurringSeriesEndingMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Event $event,
        public readonly RecurringEventSeries $series,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your recurring event "' . $this->event->title . '" is ending soon',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.recurring-series-ending',
            with: [
                'event'        => $this->event,
                'series'       => $this->series,
                'dashboardUrl' => route('dashboard'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
