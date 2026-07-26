<?php

namespace Tests\Feature;

use App\Jobs\RegenerateRoleTranslations;
use App\Jobs\SendQueuedEmail;
use App\Mail\AppointmentCancelled;
use App\Models\AppointmentType;
use App\Models\Event;
use App\Models\Sale;
use App\Services\AppointmentService;
use App\Utils\IcsUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Regression tests for the post-v1.0.119 review fixes:
 *   - the Bookings tab filtered a 500-row cap in PHP, hiding older bookings
 *   - deleting an already-cancelled appointment sale re-sent the guest cancellation
 *   - .ics invites carried no LOCATION for in-person / phone appointment types
 *   - a talent changing its translation target wiped _en on venue-governed events
 *   - isSlotAvailable() ran the next-available lookahead inside the booking row lock
 */
class ReleaseReviewFixesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function allDays(): array
    {
        return array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]);
    }

    private function book($role, AppointmentType $type): array
    {
        $from = Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $slots = app(AppointmentService::class)->availableSlots($type, $from, 1);
        $slot = $slots['days'][array_key_first($slots['days'])][0]['utc'];
        $sale = app(AppointmentService::class)->book($type, $role, ['name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot]);

        return [$sale->event, $sale];
    }

    private function queuedMailables(string $class): array
    {
        $found = [];
        foreach (Queue::pushed(SendQueuedEmail::class) as $job) {
            $ref = new \ReflectionProperty($job, 'mailable');
            $ref->setAccessible(true);
            $mailable = $ref->getValue($job);
            if ($mailable instanceof $class) {
                $found[] = $mailable;
            }
        }

        return $found;
    }

    /** A booking rooted at an explicit UTC instant, built directly so the slot windows don't matter. */
    private function bookingAt($role, AppointmentType $type, string $startsAtUtc, string $guest): array
    {
        $event = new Event;
        $event->name = 'Consult - '.$guest;
        $event->starts_at = $startsAtUtc;
        $event->duration = 0.5;
        $event->timezone = $role->timezone;
        $event->is_private = true;
        $event->creator_role_id = $role->id;
        $event->user_id = $role->user_id;
        $event->appointment_type_id = $type->id;
        $event->slug = 'x-'.strtolower(Str::random(10));
        $event->save();
        $event->roles()->attach($role->id, ['is_accepted' => true]);

        $sale = new Sale;
        $sale->event_id = $event->id;
        $sale->subdomain = $role->subdomain;
        $sale->name = $guest;
        $sale->email = strtolower($guest).'@gmail.com';
        $sale->event_date = Carbon::parse($startsAtUtc, 'UTC')->setTimezone($role->timezone)->format('Y-m-d');
        $sale->status = 'paid';
        $sale->payment_method = 'cash';
        $sale->payment_amount = 0;
        $sale->secret = strtolower(Str::random(32));
        $sale->confirmed_at = now();
        $sale->save();

        return [$event, $sale];
    }

    // ---------------------------------------------------------------- Bookings tab row cap

    public function test_past_bookings_survive_beyond_the_row_cap(): void
    {
        config(['app.hosted' => false]); // skip the Pro gate on the tab
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);

        // The one PAST booking, created first so it has the lowest sale id.
        [, $oldSale] = $this->bookingAt($role, $type, now('UTC')->subDays(30)->format('Y-m-d H:i:s'), 'Rosalind');

        // 520 newer upcoming bookings, bulk-inserted from a real row so every NOT NULL column is
        // populated. Under the old limit(500)->filter() the past booking fell outside the window.
        [$tplEvent, $tplSale] = $this->bookingAt($role, $type, now('UTC')->addDays(5)->format('Y-m-d H:i:s'), 'Filler');
        $eventRow = collect($tplEvent->getAttributes())->except('id')->all();
        $saleRow = collect($tplSale->getAttributes())->except('id')->all();

        foreach (array_chunk(range(1, 520), 130) as $chunk) {
            $events = [];
            foreach ($chunk as $i) {
                $events[] = array_merge($eventRow, [
                    'slug' => 'bulk-'.$i.'-'.strtolower(Str::random(6)),
                    'starts_at' => now('UTC')->addDays(5)->addMinutes($i)->format('Y-m-d H:i:s'),
                ]);
            }
            DB::table('events')->insert($events);
        }

        $newEventIds = Event::where('slug', 'like', 'bulk-%')->pluck('id');
        $this->assertCount(520, $newEventIds);

        foreach ($newEventIds->chunk(130) as $chunk) {
            $sales = [];
            foreach ($chunk as $eventId) {
                $sales[] = array_merge($saleRow, [
                    'event_id' => $eventId,
                    'secret' => strtolower(Str::random(32)),
                ]);
            }
            DB::table('sales')->insert($sales);
        }

        $response = $this->actingAs($owner)->get(
            route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']).'?view=bookings&filter=past'
        );

        $response->assertOk();
        $response->assertSee('Rosalind');
        $response->assertDontSee('Filler'); // upcoming bookings must not leak into the past filter
    }

    public function test_booking_filters_are_mutually_exclusive(): void
    {
        config(['app.hosted' => false]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);

        // Distinctive guest names: the filter pills themselves render words like "Past".
        $this->bookingAt($role, $type, now('UTC')->addDays(3)->format('Y-m-d H:i:s'), 'Ada');
        $this->bookingAt($role, $type, now('UTC')->subDays(3)->format('Y-m-d H:i:s'), 'Grace');
        [$cancelledEvent, $cancelledSale] = $this->bookingAt($role, $type, now('UTC')->addDays(4)->format('Y-m-d H:i:s'), 'Hedy');
        $cancelledSale->status = 'cancelled';
        $cancelledSale->save();
        [$pendingEvent] = $this->bookingAt($role, $type, now('UTC')->addDays(2)->format('Y-m-d H:i:s'), 'Marie');
        $pendingEvent->roles()->updateExistingPivot($role->id, ['is_accepted' => null]);

        $url = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']).'?view=bookings&filter=';

        // A pending booking in the future is also "upcoming" - same semantics as before the fix.
        $upcoming = $this->actingAs($owner)->get($url.'upcoming');
        $upcoming->assertSee('Ada')->assertSee('Marie')->assertDontSee('Grace')->assertDontSee('Hedy');

        $past = $this->actingAs($owner)->get($url.'past');
        $past->assertSee('Grace')->assertDontSee('Ada')->assertDontSee('Hedy');

        $pending = $this->actingAs($owner)->get($url.'pending');
        $pending->assertSee('Marie')->assertDontSee('Ada')->assertDontSee('Grace');

        $cancelled = $this->actingAs($owner)->get($url.'cancelled');
        $cancelled->assertSee('Hedy')->assertDontSee('Ada')->assertDontSee('Grace');

        $this->assertTrue($cancelledEvent->fresh()->is_cancelled);
    }

    public function test_deleted_sales_are_hidden_from_the_bookings_tab(): void
    {
        config(['app.hosted' => false]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);
        [, $sale] = $this->bookingAt($role, $type, now('UTC')->addDays(3)->format('Y-m-d H:i:s'), 'Ghost');

        $sale->forceFill(['is_deleted' => true])->saveQuietly();

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']).'?view=bookings&filter=upcoming')
            ->assertOk()
            ->assertDontSee('Ghost');
    }

    // ---------------------------------------------------------------- Duplicate cancellation email

    public function test_deleting_an_already_cancelled_booking_does_not_re_email_the_guest(): void
    {
        config(['app.hosted' => false, 'mail.default' => 'smtp']);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);
        [, $sale] = $this->book($role, $type);

        Queue::fake();

        // Cancel: the guest is told once.
        $this->actingAs($owner)->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]), ['action' => 'cancel']);
        $this->assertCount(1, $this->queuedMailables(AppointmentCancelled::class));

        // Deleting the now-cancelled sale must not tell them again.
        $this->actingAs($owner)->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]), ['action' => 'delete']);
        $this->assertCount(1, $this->queuedMailables(AppointmentCancelled::class),
            'deleting an already-cancelled booking must not re-send the cancellation email');
    }

    public function test_deleting_a_live_booking_still_emails_the_guest(): void
    {
        config(['app.hosted' => false, 'mail.default' => 'smtp']);
        Queue::fake();
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);
        [, $sale] = $this->book($role, $type);

        $this->actingAs($owner)->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]), ['action' => 'delete']);

        $this->assertCount(1, $this->queuedMailables(AppointmentCancelled::class));
    }

    // ---------------------------------------------------------------- .ics LOCATION

    public function test_ics_carries_the_address_for_an_in_person_type(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => $this->allDays(),
            'location_type' => 'in_person',
            'location_address' => '12 Rue Cuvier, Paris',
        ]);
        [$event, $sale] = $this->book($role, $type);

        $ics = IcsUtils::buildInvite($event->fresh(), $role, $sale);

        // Commas are escaped per RFC 5545.
        $this->assertStringContainsString('LOCATION:12 Rue Cuvier\\, Paris', $ics);
    }

    public function test_ics_carries_the_number_for_a_phone_type(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => $this->allDays(),
            'location_type' => 'phone',
            'location_phone' => '+1 555 0100',
        ]);
        [$event, $sale] = $this->book($role, $type);

        $this->assertStringContainsString('LOCATION:+1 555 0100', IcsUtils::buildInvite($event->fresh(), $role, $sale));
    }

    public function test_ics_still_uses_the_url_for_an_online_type(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => $this->allDays(),
            'location_type' => 'online',
            'location_url' => 'https://meet.example.com/abc',
        ]);
        [$event, $sale] = $this->book($role, $type);

        $this->assertStringContainsString('LOCATION:https://meet.example.com/abc', IcsUtils::buildInvite($event->fresh(), $role, $sale));
    }

    // ---------------------------------------------------------------- Translation reset scoping

    public function test_talent_target_change_leaves_venue_governed_events_alone(): void
    {
        Queue::fake(); // the model hook dispatches; we run the job by hand below

        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent', ['language_code' => 'he', 'translation_language_code' => 'en']);
        $venue = $this->createRole($owner, 'venue', ['language_code' => 'he', 'translation_language_code' => 'en']);

        $event = $this->createEvent($talent, ['name' => 'מופע']);
        $event->roles()->syncWithoutDetaching([$venue->id => ['is_accepted' => true]]);
        $event->forceFill(['name_en' => 'Show'])->saveQuietly();

        // The venue decides this event's target (Event::getTranslationLanguageCode), so a talent
        // changing its own target must not blank the cached translation.
        $this->assertSame('en', $event->fresh()->getTranslationLanguageCode());

        $talent->translation_language_code = 'fr';
        $talent->save();
        (new RegenerateRoleTranslations($talent->fresh()))->handle();

        $this->assertSame('Show', $event->fresh()->name_en,
            'a talent changing its target must not wipe a venue-governed event translation');
    }

    public function test_talent_target_change_still_resets_its_own_venueless_events(): void
    {
        Queue::fake();

        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent', ['language_code' => 'he', 'translation_language_code' => 'en']);

        $event = $this->createEvent($talent, ['name' => 'מופע']);
        $event->forceFill(['name_en' => 'Show'])->saveQuietly();

        $this->assertSame('en', $event->fresh()->getTranslationLanguageCode());

        $talent->translation_language_code = 'fr';
        $talent->save();
        (new RegenerateRoleTranslations($talent->fresh()))->handle();

        $this->assertNull($event->fresh()->name_en,
            'an event with no venue is governed by the talent, so its stale translation must clear');
    }

    public function test_venue_target_change_resets_its_events(): void
    {
        Queue::fake();

        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent', ['language_code' => 'he', 'translation_language_code' => 'en']);
        $venue = $this->createRole($owner, 'venue', ['language_code' => 'he', 'translation_language_code' => 'en']);

        $event = $this->createEvent($talent, ['name' => 'מופע']);
        $event->roles()->syncWithoutDetaching([$venue->id => ['is_accepted' => true]]);
        $event->forceFill(['name_en' => 'Show'])->saveQuietly();

        $venue->translation_language_code = 'fr';
        $venue->save();
        (new RegenerateRoleTranslations($venue->fresh()))->handle();

        $this->assertNull($event->fresh()->name_en,
            'the venue governs the target, so it is the role that must reset the translation');
    }

    // ---------------------------------------------------------------- Slot lookahead

    public function test_is_slot_available_skips_the_next_available_lookahead(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        // Open Mondays only, and look at a closed day: the old code fell into nextAvailableDate()
        // and re-queried busy intervals for every 31-day chunk up to max_advance_days.
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => ['0' => [], '1' => [['start' => '09:00', 'end' => '17:00']], '2' => [], '3' => [], '4' => [], '5' => [], '6' => []],
            'max_advance_days' => 730,
        ]);

        $closedSunday = Carbon::now('America/New_York')->next(Carbon::SUNDAY)->setTime(12, 0)->setTimezone('UTC');

        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        $available = app(AppointmentService::class)->isSlotAvailable($type, $closedSunday->format('Y-m-d\TH:i:s\Z'));

        $this->assertFalse($available);
        $this->assertLessThanOrEqual(4, $queries,
            "isSlotAvailable() only needs one day's busy intervals; the next-available scan must not run inside the booking lock");
    }
}
