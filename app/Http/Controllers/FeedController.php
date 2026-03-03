<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;

class FeedController extends Controller
{
    public function icalFeed($subdomain)
    {
        $role = Role::subdomain($subdomain)->first();

        if (! $role || ! $role->isClaimed()) {
            abort(404);
        }

        $now = Carbon::now('UTC');

        $events = Event::where(function ($query) use ($now) {
            $query->where('starts_at', '>=', $now)
                ->orWhereNotNull('days_of_week');
        })
            ->whereIn('id', function ($query) use ($role) {
                $query->select('event_id')
                    ->from('event_role')
                    ->where('role_id', $role->id)
                    ->where('is_accepted', true);
            })
            ->where('is_private', false)
            ->whereNull('event_password')
            ->with(['roles', 'venue'])
            ->orderBy('starts_at')
            ->get();

        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'eventschedule.com';
        $timezone = $role->timezone ?: 'UTC';
        $calName = $role->name ?: $subdomain;

        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Event Schedule//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $ical .= 'X-WR-CALNAME:'.$this->escapeIcalText($calName)."\r\n";
        $ical .= 'X-WR-TIMEZONE:'.$timezone."\r\n";

        foreach ($events as $event) {
            if ($event->days_of_week) {
                // Recurring event: generate individual entries for next 90 days
                $checkDate = Carbon::now($timezone)->startOfDay();
                $endDate = $checkDate->copy()->addDays(90);

                while ($checkDate->lte($endDate)) {
                    if ($event->matchesDate($checkDate)) {
                        $ical .= $this->buildVevent($event, $domain, $role, $checkDate->format('Y-m-d'));
                    }
                    $checkDate->addDay();
                }
            } else {
                $ical .= $this->buildVevent($event, $domain, $role);
            }
        }

        $ical .= 'END:VCALENDAR';

        return response($ical, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function rssFeed($subdomain)
    {
        $role = Role::subdomain($subdomain)->first();

        if (! $role || ! $role->isClaimed()) {
            abort(404);
        }

        $now = Carbon::now('UTC');
        $timezone = $role->timezone ?: 'UTC';

        $events = Event::where(function ($query) use ($now) {
            $query->where('starts_at', '>=', $now)
                ->orWhereNotNull('days_of_week');
        })
            ->whereIn('id', function ($query) use ($role) {
                $query->select('event_id')
                    ->from('event_role')
                    ->where('role_id', $role->id)
                    ->where('is_accepted', true);
            })
            ->where('is_private', false)
            ->whereNull('event_password')
            ->with(['roles', 'venue'])
            ->orderBy('starts_at')
            ->get();

        // For non-recurring events, take up to 50
        // For recurring events, include next occurrence only
        $items = collect();

        foreach ($events as $event) {
            if ($event->days_of_week) {
                $checkDate = Carbon::now($timezone)->startOfDay();
                $endDate = $checkDate->copy()->addDays(90);

                while ($checkDate->lte($endDate)) {
                    if ($event->matchesDate($checkDate)) {
                        $items->push([
                            'event' => $event,
                            'date' => $checkDate->format('Y-m-d'),
                        ]);
                        break;
                    }
                    $checkDate->addDay();
                }
            } else {
                $items->push([
                    'event' => $event,
                    'date' => null,
                ]);
            }

            if ($items->count() >= 50) {
                break;
            }
        }

        return response()
            ->view('feed.rss', [
                'role' => $role,
                'items' => $items,
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    private function buildVevent(Event $event, string $domain, Role $role, ?string $date = null): string
    {
        $uid = $date
            ? "event-{$event->id}-{$date}@{$domain}"
            : "event-{$event->id}@{$domain}";

        $title = $event->getTitle();
        $description = $event->description_html ? strip_tags($event->description_html) : '';
        $location = $event->venue ? $event->venue->bestAddress() : '';
        $duration = $event->duration > 0 ? $event->duration : 2;
        $startAt = $event->getStartDateTime($date);
        $startDate = $startAt->format('Ymd\THis\Z');
        $endDate = $startAt->copy()->addSeconds($duration * 3600)->format('Ymd\THis\Z');
        $url = $event->getGuestUrl($role->subdomain, $date);

        $vevent = "BEGIN:VEVENT\r\n";
        $vevent .= 'UID:'.$uid."\r\n";
        $vevent .= 'DTSTART:'.$startDate."\r\n";
        $vevent .= 'DTEND:'.$endDate."\r\n";
        $vevent .= 'SUMMARY:'.$this->escapeIcalText($title)."\r\n";
        if ($description) {
            $vevent .= 'DESCRIPTION:'.$this->escapeIcalText($description)."\r\n";
        }
        if ($location) {
            $vevent .= 'LOCATION:'.$this->escapeIcalText($location)."\r\n";
        }
        if ($url) {
            $vevent .= 'URL:'.$url."\r\n";
        }
        $vevent .= "END:VEVENT\r\n";

        return $vevent;
    }

    private function escapeIcalText(string $text): string
    {
        return str_replace(
            ['\\', ';', ',', "\r\n", "\r", "\n"],
            ['\\\\', '\\;', '\\,', '\\n', '\\n', '\\n'],
            $text
        );
    }
}
