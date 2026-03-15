<?php

namespace App\Http\Controllers;

use App\Models\Event;
use DateTimeImmutable;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event as ICalEvent;
use Eluceo\iCal\Domain\ValueObject\DateTime as ICalDateTime;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class ICalController extends Controller
{
    public function event(Event $event): Response
    {
        abort_unless($event->status === 'approved', 404);

        $ics = $this->buildCalendar([$event]);

        return response($ics, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $event->slug . '.ics"',
        ]);
    }

    public function feed(): Response
    {
        $ics = Cache::remember('ics_feed', 1800, function () {
            $events = Event::approved()
                ->upcoming()
                ->with('location')
                ->orderBy('start_date')
                ->get();

            return $this->buildCalendar($events->all());
        });

        return response($ics, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="riviera-events.ics"',
        ]);
    }

    private function buildCalendar(array $events): string
    {
        $calendar = new Calendar();

        foreach ($events as $event) {
            $startDt = DateTimeImmutable::createFromMutable($event->start_date->toDateTime());
            $endDt   = DateTimeImmutable::createFromMutable(($event->end_date ?? $event->start_date)->toDateTime());

            $vEvent = new ICalEvent(new UniqueIdentifier('riviera-event-' . $event->id));
            $vEvent->setSummary($event->title)
                ->setDescription(strip_tags($event->description ?? ''))
                ->setUrl(new \Eluceo\iCal\Domain\ValueObject\Uri(route('events.show', $event)))
                ->setOccurrence(new TimeSpan(
                    new ICalDateTime($startDt, false),
                    new ICalDateTime($endDt, false),
                ));

            if ($event->location) {
                $vEvent->setLocation(
                    new \Eluceo\iCal\Domain\ValueObject\Location($event->location->city . ', ' . $event->location->state)
                );
            }

            $calendar->addEvent($vEvent);
        }

        $factory = new CalendarFactory();

        return (string) $factory->createCalendar($calendar);
    }
}
