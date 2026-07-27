<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Sale;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Every weekday open 09:00-17:00 so any near date has slots. */
    private function allDays(): array
    {
        return array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]);
    }

    private function firstSlotUtc(string $subdomain, string $slug): string
    {
        $from = Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $response = $this->getJson(route('appointments.slots', ['subdomain' => $subdomain, 'typeSlug' => $slug]).'?from='.$from.'&days=1');
        $response->assertOk();
        $days = $response->json('days');
        $firstDate = array_key_first($days);

        return $days[$firstDate][0]['utc'];
    }

    public function test_booking_picker_template_methods_are_defined(): void
    {
        // The server render succeeds even if a Vue method is missing; the mount then throws at
        // runtime. Guard that gap: every method the template invokes must be defined in the component.
        //
        // The call list is DERIVED from the template rather than hardcoded, so adding a new binding
        // without its method fails here instead of only in a browser.
        $src = file_get_contents(resource_path('views/appointments/book-type.blade.php'));

        // Template only: the <script> body is the definitions, and <style>/CSS is full of fn() syntax.
        $template = preg_replace('/<script\b.*?<\/script>/s', '', $src);
        $template = preg_replace('/<style\b.*?<\/style>/s', '', $template);

        // Calls inside Vue bindings (@click, :aria-label, v-if, ...) and @{{ }} interpolations.
        $expressions = [];
        preg_match_all('/(?:@|:|v-)[\w.-]+="([^"]*)"/', $template, $bindings);
        preg_match_all('/@\{\{(.*?)\}\}/s', $template, $mustaches);
        $expressions = array_merge($bindings[1] ?? [], $mustaches[1] ?? []);

        // Not component methods: JS built-ins and locals introduced by v-for.
        $builtins = ['Array', 'String', 'Number', 'Object', 'Boolean', 'Date', 'JSON', 'Math',
            'parseInt', 'parseFloat', 'isNaN', 'filter', 'map', 'some', 'every', 'indexOf',
            'includes', 'replace', 'slice', 'split', 'join', 'sort', 'find', 'concat', 'padStart',
            'toLowerCase', 'toUpperCase', 'trim', 'matches', 'focus', 'scrollIntoView'];

        $called = [];
        foreach ($expressions as $expr) {
            if (preg_match_all('/\b([a-z][A-Za-z0-9_]*)\s*\(/', $expr, $m)) {
                foreach ($m[1] as $name) {
                    if (! in_array($name, $builtins, true)) {
                        $called[$name] = true;
                    }
                }
            }
        }

        // Sanity-check the extraction itself, so a broken regex cannot silently pass everything.
        $this->assertArrayHasKey('hasSlots', $called);
        $this->assertGreaterThanOrEqual(8, count($called));

        foreach (array_keys($called) as $method) {
            $this->assertMatchesRegularExpression(
                '/\b'.preg_quote($method, '/').'\s*\([^)]*\)\s*\{/',
                $src,
                "The booking picker template invokes {$method}() but the Vue component does not define it."
            );
        }
    }

    public function test_book_pages_render(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);

        // The picker page renders the Vue app container.
        $this->get(route('appointments.book_type', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]))
            ->assertOk()
            ->assertSee('booking-app');

        // A single active type redirects the chooser straight to it.
        $this->get(route('appointments.book', ['subdomain' => $role->subdomain]))
            ->assertRedirect(route('appointments.book_type', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]));
    }

    public function test_free_booking_creates_confirmed_sale_and_consumes_slot(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);

        $slot = $this->firstSlotUtc($role->subdomain, $type->slug);

        $response = $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane Guest',
            'email' => 'jane@gmail.com',
            'slot' => $slot,
            'guest_timezone' => 'America/New_York',
            'notes' => 'Looking forward to it',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('/appointment/view/', $response->json('redirect_url'));

        $event = Event::where('appointment_type_id', $type->id)->firstOrFail();
        $this->assertTrue((bool) $event->is_private);
        $this->assertFalse((bool) $event->feedback_enabled);
        $this->assertFalse((bool) $event->tickets_enabled);
        $this->assertSame(0.5, (float) $event->duration);
        $this->assertTrue((bool) $event->roles()->where('roles.id', $role->id)->first()->pivot->is_accepted);

        $sale = Sale::where('event_id', $event->id)->firstOrFail();
        $this->assertSame('paid', $sale->status);
        $this->assertNotNull($sale->confirmed_at);
        $this->assertSame(32, strlen((string) $sale->secret));

        $ticket = Ticket::where('event_id', $event->id)->firstOrFail();
        $this->assertSame(1, array_sum(json_decode($ticket->sold, true) ?: [])); // one seat held

        // The slot is no longer offered.
        $from = Carbon::parse($sale->event_date)->format('Y-m-d');
        $slots = $this->getJson(route('appointments.slots', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]).'?from='.$from.'&days=1')->json('days');
        $labels = collect($slots[$from] ?? [])->pluck('utc');
        $this->assertNotContains($slot, $labels);
    }

    public function test_double_booking_same_slot_is_rejected(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);
        $slot = $this->firstSlotUtc($role->subdomain, $type->slug);

        $params = ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug];
        $payload = ['name' => 'A', 'email' => 'a@gmail.com', 'slot' => $slot, 'guest_timezone' => 'America/New_York'];

        $this->postJson(route('appointments.book.store', $params), $payload)->assertOk();

        $second = $this->postJson(route('appointments.book.store', $params), array_merge($payload, ['email' => 'b@gmail.com']));
        $second->assertStatus(422);
        $this->assertNotNull($second->json('error'));

        $this->assertSame(1, Event::where('appointment_type_id', $type->id)->count());
    }

    public function test_guest_can_cancel_and_free_the_slot(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);
        $slot = $this->firstSlotUtc($role->subdomain, $type->slug);

        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot, 'guest_timezone' => 'America/New_York',
        ])->assertOk();

        $event = Event::where('appointment_type_id', $type->id)->firstOrFail();
        $sale = Sale::where('event_id', $event->id)->firstOrFail();

        $this->post(route('appointments.manage_cancel', [
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]));

        $this->assertSame('cancelled', $sale->fresh()->status);
        $this->assertTrue((bool) $event->fresh()->is_cancelled);

        // Slot is offered again.
        $from = Carbon::parse($sale->event_date)->format('Y-m-d');
        $slots = $this->getJson(route('appointments.slots', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]).'?from='.$from.'&days=1')->json('days');
        $this->assertContains($slot, collect($slots[$from] ?? [])->pluck('utc'));
    }

    public function test_requires_approval_creates_pending_booking(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays(), 'requires_approval' => true]);
        $slot = $this->firstSlotUtc($role->subdomain, $type->slug);

        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot, 'guest_timezone' => 'America/New_York',
        ])->assertOk();

        $event = Event::where('appointment_type_id', $type->id)->firstOrFail();
        $pivot = $event->roles()->where('roles.id', $role->id)->first()->pivot;
        $this->assertNull($pivot->is_accepted); // pending approval

        $sale = Sale::where('event_id', $event->id)->firstOrFail();
        $this->assertNull($sale->confirmed_at); // not confirmed until accepted
    }

    /**
     * Books a late-evening slot so the guest's calendar date differs from the schedule's, and
     * returns [$event, $sale, $startUtc].
     */
    private function bookAcrossDateBoundary(string $guestTimezone): array
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '22:00', 'end' => '23:00']]),
        ]);

        // 22:00 in New York is always the following day in Paris, whatever the DST offsets are.
        $scheduleDay = Carbon::now('America/New_York')->addDays(3)->format('Y-m-d');
        $startUtc = Carbon::createFromFormat('Y-m-d H:i', $scheduleDay.' 22:00', 'America/New_York')->utc();

        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane',
            'email' => 'jane@gmail.com',
            'slot' => $startUtc->format('Y-m-d\TH:i:s\Z'),
            'guest_timezone' => $guestTimezone,
        ])->assertOk();

        $event = Event::where('appointment_type_id', $type->id)->firstOrFail();
        $sale = Sale::where('event_id', $event->id)->firstOrFail();

        // Guard the fixture: without a real boundary this test proves nothing.
        $this->assertNotSame(
            $scheduleDay,
            $startUtc->copy()->setTimezone($guestTimezone)->format('Y-m-d'),
            'fixture must straddle a date boundary'
        );

        return [$event, $sale, $startUtc];
    }

    public function test_manage_page_renders_the_guest_timezone_across_a_date_boundary(): void
    {
        [$event, $sale, $startUtc] = $this->bookAcrossDateBoundary('Europe/Paris');

        $guestLocal = $startUtc->copy()->setTimezone('Europe/Paris');
        $scheduleLocal = $startUtc->copy()->setTimezone('America/New_York');

        $html = $this->get(route('appointments.manage', [
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]))->assertOk()->getContent();

        // Guest-local DATE and TIME are primary. Rendering the schedule-local date with a guest-local
        // clock is the specific bug a naive getStartEndTime($sale->event_date, ...) fix introduces.
        $this->assertStringContainsString($guestLocal->translatedFormat('l, F j, Y'), $html);
        $this->assertStringContainsString($guestLocal->format('g:i A'), $html);
        $this->assertStringContainsString('Europe/Paris', $html);
        $this->assertStringNotContainsString($scheduleLocal->translatedFormat('l, F j, Y'), $html);

        // The schedule's own zone and clock stay available as a secondary line.
        $this->assertStringContainsString('America/New_York', $html);
        $this->assertStringContainsString($scheduleLocal->format('g:i A'), $html);
    }

    public function test_confirmation_email_renders_the_guest_timezone(): void
    {
        [$event, $sale, $startUtc] = $this->bookAcrossDateBoundary('Europe/Paris');
        $guestLocal = $startUtc->copy()->setTimezone('Europe/Paris');

        $body = (new \App\Mail\AppointmentConfirmed($sale, $event, $event->creatorRole, $event->appointmentType))
            ->render();

        $this->assertStringContainsString($guestLocal->translatedFormat('l, F j, Y'), $body);
        $this->assertStringContainsString($guestLocal->format('g:i A'), $body);
        $this->assertStringContainsString('Europe/Paris', $body);
        // Schedule zone still disclosed, so nobody loses the host's clock.
        $this->assertStringContainsString('America/New_York', $body);
    }

    public function test_ical_download_is_served_behind_the_secret_link(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['name' => 'Intro Chat', 'weekly_windows' => $this->allDays()]);
        $slot = $this->firstSlotUtc($role->subdomain, $type->slug);

        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot, 'guest_timezone' => 'America/New_York',
        ])->assertOk();

        $event = Event::where('appointment_type_id', $type->id)->firstOrFail();
        $sale = Sale::where('event_id', $event->id)->firstOrFail();
        $encoded = \App\Utils\UrlUtils::encodeId($event->id);

        $response = $this->get(route('appointments.ical', ['event_id' => $encoded, 'secret' => $sale->secret]))->assertOk();
        $this->assertStringContainsString('text/calendar', $response->headers->get('content-type'));
        $this->assertStringContainsString('appointment.ics', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('BEGIN:VEVENT', $response->getContent());

        // A wrong secret must not hand out the booking.
        $this->get(route('appointments.ical', ['event_id' => $encoded, 'secret' => str_repeat('x', 32)]))
            ->assertNotFound();

        // Documents WHY this route exists: bookings are is_private, so the normal event iCal download
        // 404s for the very guest who made the booking.
        $this->get($event->getAppleCalendarUrl())->assertNotFound();

        // The Google/Outlook links carry the type name, not the "Type - Guest" event name.
        $this->assertStringContainsString(urlencode('Intro Chat'), \App\Utils\IcsUtils::googleUrl($event, $sale));
        $this->assertStringContainsString(urlencode('Intro Chat'), \App\Utils\IcsUtils::outlookUrl($event, $sale));
    }

    /**
     * The guest timezone is validated on READ, not on write.
     *
     * It used to carry `nullable|timezone`, which tests DateTimeZone::ALL - a list that omits
     * backward-compat aliases. Browsers report those aliases (Intl follows the host tz database, not
     * PHP's canonical list), so the rule rejected real guests and their booking could not complete at
     * all. The contract is now: accept what the browser said, and neutralise it on the way out.
     */
    public function test_a_browser_reported_timezone_alias_can_book_and_is_honoured(): void
    {
        // Absent from DateTimeZone::listIdentifiers() but constructible - the exact case that 422'd.
        $this->assertNotContains('Asia/Calcutta', \DateTimeZone::listIdentifiers(), 'fixture premise');

        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);
        $slot = $this->firstSlotUtc($role->subdomain, $type->slug);

        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot, 'guest_timezone' => 'Asia/Calcutta',
        ])->assertOk();

        $sale = Sale::whereNotNull('event_id')->latest('id')->firstOrFail();
        $this->assertSame('Asia/Calcutta', $sale->guestTimezone(), 'the alias must survive to the render');

        // And it renders in the guest's own zone, not silently in the schedule's.
        $this->get(route('appointments.manage', [
            'event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id),
            'secret' => $sale->secret,
        ]))->assertOk()->assertSee('Asia/Calcutta');
    }

    public function test_an_unusable_guest_timezone_is_neutralised_on_read_rather_than_blocking_the_booking(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);
        $slot = $this->firstSlotUtc($role->subdomain, $type->slug);

        // Garbage no longer costs the guest their booking...
        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot, 'guest_timezone' => 'Not/AZone',
        ])->assertOk();

        $event = Event::where('appointment_type_id', $type->id)->firstOrFail();
        $sale = Sale::where('event_id', $event->id)->firstOrFail();

        // ...and reading it falls back to the schedule's zone instead of throwing.
        $this->assertNull($sale->guestTimezone());
        $this->get(route('appointments.manage', [
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]))->assertOk()->assertSee('America/New_York');

        // Same for a row a backup restore put there directly.
        $sale->forceFill(['guest_timezone' => 'Garbage/Value'])->saveQuietly();
        $this->assertNull($sale->fresh()->guestTimezone());
    }
}
