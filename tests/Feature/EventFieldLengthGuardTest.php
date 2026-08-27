<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Event;
use App\Models\Role;
use App\Services\AppointmentService;
use App\Services\BackupService;
use App\Utils\TextUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The short varchar columns on events, which EventRepo::saveEvent()'s blanket
 * fill($request->all()) funnels straight from the POST body. Under a strict connection an
 * over-long value is a QueryException (MySQL 1406), not a truncation, so the save fails and the
 * user loses the whole edit.
 *
 * Production report EVENTSCHEDULE-PHP-49: an event update 500'd on agenda_ai_prompt. The value was
 * exactly 500 characters and the textarea feeding it already carried maxlength="500" - the browser
 * had done its job. maxlength constrains a textarea's API value, where a line break is one LF, but
 * a form submits it as CRLF, so six line breaks put 506 characters on the wire. The field is not
 * even on screen for that journey: it rides along in a hidden input, and the user was editing
 * ticket settings.
 *
 * Two layers are pinned here - the FormRequest rules for the visible fields, and the Event saving
 * hook that backs up every writer with no FormRequest (calendar sync, WhatsApp webhook, import).
 */
class EventFieldLengthGuardTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    /**
     * The shape of the value from the production report: a pasted Zoom invite, clipped by the
     * browser to exactly 500 characters with six line breaks still in it.
     */
    private function productionPrompt(): string
    {
        $lf = "Teeya Smith is inviting you to a scheduled Zoom meeting.\n"
            ."\n"
            ."Topic: Hub Office Hours\n"
            ."Time: Sep 11, 2026 10:00 AM Pacific Time (US and Canada)\n"
            ."        Every month on the Second Fri, 36 occurrence(s)\n"
            ."Please download and import the following iCalendar (.ics) files to your calendar system.\n"
            .'Monthly: https://us06web.zoom.us/meeting/';

        return $lf.str_repeat('x', 500 - mb_strlen($lf));
    }

    // -----------------------------------------------------------------
    // The production regression.
    // -----------------------------------------------------------------

    public function test_a_full_length_prompt_submitted_with_crlf_saves_and_keeps_every_character(): void
    {
        $prompt = $this->productionPrompt();
        $this->assertSame(500, mb_strlen($prompt), 'the fixture must stay the value that actually overflowed');
        $this->assertSame(6, substr_count($prompt, "\n"), 'the line breaks are what push it over the column');

        $wire = str_replace("\n", "\r\n", $prompt);
        $this->assertSame(506, mb_strlen($wire), 'a form serializes each line break as CRLF');

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role);

        $response = $this->putUpdateEvent($owner, $role, $event, ['agenda_ai_prompt' => $wire]);

        $response->assertSessionHasNoErrors();
        $this->assertSame($prompt, $event->fresh()->agenda_ai_prompt, 'nothing may be truncated');
    }

    public function test_the_same_value_on_the_schedule_is_guarded_too(): void
    {
        // roles.agenda_ai_prompt is the identical varchar(500), written from the same form via
        // save_ai_prompt_default.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $role->agenda_ai_prompt = str_replace("\n", "\r\n", $this->productionPrompt());
        $role->save();

        $this->assertSame($this->productionPrompt(), $role->fresh()->agenda_ai_prompt);
    }

    // -----------------------------------------------------------------
    // The FormRequest rules - the visible fields, where rejecting beats clamping.
    // -----------------------------------------------------------------

    /**
     * A truncated URL is a silently broken link, so these are refused rather than cut. Not tested
     * for agenda_ai_prompt on purpose: it is a hidden input on every event save, so a rule there
     * would reject an unrelated edit with a page-top message naming a field that is not on screen.
     */
    public static function overlongFieldProvider(): array
    {
        return collect(Event::CLAMPED_COLUMNS)
            // Deliberately unruled - see the note above.
            ->except('agenda_ai_prompt')
            ->map(fn ($width, $column) => [$column, $width])
            ->all();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('overlongFieldProvider')]
    public function test_an_over_long_value_is_refused_rather_than_stored(string $field, int $width): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role);

        $response = $this->putUpdateEvent($owner, $role, $event, [$field => $this->fill($field, $width + 100)]);

        $response->assertSessionHasErrors($field);
        $this->assertNull($event->fresh()->{$field});
    }

    /**
     * Driven from the same constant, so a rule that drifts from its column - the way the API's
     * event_url sat at 255 after the column went to 500 - fails here rather than in production.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('overlongFieldProvider')]
    public function test_a_value_at_exactly_the_declared_width_is_accepted(string $field, int $width): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role);

        $value = $this->fill($field, $width);

        // is_private because EventRepo::saveEvent() strips event_password from anything that is not
        // Unlisted - a stale password would keep a published event password-locked. Harmless for
        // the other columns, which persist regardless of visibility.
        $this->putUpdateEvent($owner, $role, $event, [$field => $value, 'is_private' => 1])
            ->assertSessionHasNoErrors();

        $this->assertSame($value, $event->fresh()->{$field}, "a value at the ceiling of $field must store whole");
    }

    /** A syntactically valid value of exactly $length characters for the given column. */
    private function fill(string $field, int $length): string
    {
        $prefix = str_contains($field, '_url') ? 'https://example.org/' : '';

        return $prefix.str_repeat('a', $length - mb_strlen($prefix));
    }

    public function test_guest_submission_refuses_a_url_its_column_cannot_hold(): void
    {
        // bookingRequest() validates event_url at max:500 and events.event_url is a varchar(500),
        // so the rule and the column agree. They did not always: the rule allowed 500 into a
        // varchar(255), and a 256-500 character URL passed validation and then 22001'd.
        $owner = $this->createOwner();
        // A talent schedule uses the booking form, and accept_requests gates a claimed one.
        $role = $this->createRole($owner, 'talent', ['accept_requests' => true]);

        $response = $this->post(route('event.booking_request.store', ['subdomain' => $role->subdomain]), [
            'event_name' => 'Guest Submitted',
            'date' => '2026-09-15',
            'start_time' => '20:00',
            'event_url' => 'https://example.org/'.str_repeat('a', 600),
            'contact_name' => 'Guest',
            'contact_email' => 'guest@example.org',
        ]);

        $response->assertSessionHasErrors('event_url');
    }

    // -----------------------------------------------------------------
    // The model hook - the backstop for every writer with no FormRequest.
    // -----------------------------------------------------------------

    public function test_saving_an_event_directly_clamps_rather_than_throwing(): void
    {
        // Covers the callers that never see a FormRequest: inbound calendar sync, the WhatsApp
        // webhook and the curator-import cron.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role);

        $event->agenda_ai_prompt = str_repeat('p', 600);
        $event->event_url = 'https://example.org/'.str_repeat('a', 600);
        $event->terms_url = 'https://example.org/'.str_repeat('b', 300);
        $event->save();

        $fresh = $event->fresh();
        $this->assertSame(500, mb_strlen($fresh->agenda_ai_prompt));
        $this->assertSame(500, mb_strlen($fresh->event_url));
        $this->assertSame(255, mb_strlen($fresh->terms_url), 'terms_url is still a varchar(255)');
    }

    public function test_a_save_that_does_not_touch_these_fields_leaves_them_untouched(): void
    {
        // Guarded on isDirty: re-clamping an untouched legacy value would rewrite stored data
        // during an unrelated save, turning the fix into a lazy backfill.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role);

        $event->agenda_ai_prompt = "legacy\r\nvalue";
        $event->saveQuietly();

        $event->name = 'Renamed';
        $event->save();

        $this->assertSame("legacy\r\nvalue", $event->fresh()->agenda_ai_prompt);
    }

    /**
     * event_password is stored in plaintext and checked with hash_equals() against a raw,
     * un-normalized request value (RoleController::checkEventPassword). Normalizing the stored side
     * alone would make a password containing a CR permanently unmatchable, so the newline handling
     * is deliberately scoped to agenda_ai_prompt. This pins that so a later "normalize them all"
     * tidy-up cannot silently break password-gated events.
     */
    public function test_an_event_password_is_never_newline_normalized(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role);

        $submitted = "pass\r\nword";
        $event->event_password = $submitted;
        $event->save();

        $stored = $event->fresh()->event_password;
        $this->assertSame($submitted, $stored, 'the stored value must stay byte-for-byte');
        $this->assertTrue(hash_equals($stored, $submitted));
    }

    public function test_restoring_a_backup_carrying_an_over_long_value_does_not_throw(): void
    {
        // importEvent() persists with saveQuietly(), which fires no model events - so the saving
        // hook cannot help here and the clamp has to be re-applied by hand. A hand-edited backup,
        // or one from an install whose schema differs, is how an over-long value reaches this path.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->createEvent($role);

        $svc = app(BackupService::class);

        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $data['schedules'][0]['events'][0]['agenda_ai_prompt'] = str_repeat('p', 900);
        // importRole() is saveQuietly() too, and roles.agenda_ai_prompt is the same varchar(500).
        $data['schedules'][0]['role']['agenda_ai_prompt'] = str_repeat('r', 900);

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Event::query()->orderByDesc('id')->firstOrFail();
        $restoredRole = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();

        $this->assertSame(500, mb_strlen($restored->agenda_ai_prompt));
        $this->assertSame(500, mb_strlen($restoredRole->agenda_ai_prompt));
    }

    // -----------------------------------------------------------------
    // Interactions the guard must not disturb.
    // -----------------------------------------------------------------

    public function test_a_change_the_clamp_cancels_out_does_not_re_queue_federation(): void
    {
        // event_url and event_password are in FEDERATION_FIELDS, so the clamp has to run BEFORE
        // the federation dirty-check: an over-long URL that cuts back to the value already stored
        // is not a change, and invalidating federated_at for it re-publishes an unchanged event.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role);

        $event->event_url = 'https://example.org/'.str_repeat('a', 600);
        $event->save();

        $stored = $event->fresh()->event_url;
        $this->assertSame(500, mb_strlen($stored), 'precondition: the value was clamped');

        $event->fresh()->forceFill(['federated_at' => now()])->saveQuietly();

        // Submit the same over-long value again: it clamps to exactly what is already stored.
        $event = $event->fresh();
        $event->event_url = 'https://example.org/'.str_repeat('a', 600);
        $event->save();

        $this->assertNotNull($event->fresh()->federated_at, 'a no-op change must not re-queue federation');
    }

    public function test_an_appointment_booking_keeps_its_full_meeting_url(): void
    {
        // appointment_types.location_url is a varchar(500) validated at max:500, and
        // AppointmentService::book() copies it verbatim onto the event it creates. While
        // events.event_url was a varchar(255) that was a 1406 on every booking of a
        // Teams-length join URL. Driven through the real booking route so it pins that copy,
        // not just the column width.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);

        $url = 'https://teams.microsoft.com/l/meetup-join/'.str_repeat('a', 358);
        $this->assertSame(400, mb_strlen($url), 'fixture must be longer than the old 255 ceiling');

        $type = $this->createAppointmentType($role, [
            'weekly_windows' => array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]),
            'location_type' => 'online',
            'location_url' => $url,
        ]);
        $this->assertSame($url, $type->location_url, 'precondition: the type stores the full URL');

        $from = Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $slots = app(AppointmentService::class)->availableSlots($type, $from, 1);
        $slot = $slots['days'][array_key_first($slots['days'])][0]['utc'];

        $this->postJson(route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot, 'guest_timezone' => 'America/New_York',
        ])->assertOk();

        $booked = Event::where('appointment_type_id', $type->id)->firstOrFail();

        $this->assertSame($url, $booked->event_url, 'the guest must get the whole join link');
    }

    public function test_the_api_can_write_back_an_event_url_the_web_produced(): void
    {
        // The API validates event_url on update. While that rule sat at 255 and the column at 500,
        // an ordinary read-modify-write - GET an event, PUT it back - 422'd on a field the client
        // never touched, because the web form and appointment bookings can both produce 500.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role);

        $url = 'https://teams.microsoft.com/l/meetup-join/'.str_repeat('a', 358);
        $event->event_url = $url;
        $event->save();
        $this->assertSame($url, $event->fresh()->event_url, 'precondition: the web path stored 400 characters');

        $raw = 'testapikey_'.Str::random(24);
        $owner->api_key = substr(hash('sha256', $raw), 0, 8);
        $owner->api_key_hash = Hash::make($raw);
        $owner->save();

        $this->withHeaders(['X-API-Key' => $raw])
            ->putJson('/api/events/'.UrlUtils::encodeId($event->id), [
                'name' => 'Renamed via API',
                'event_url' => $url,
            ])
            ->assertOk();

        $this->assertSame($url, $event->fresh()->event_url);
    }

    // -----------------------------------------------------------------
    // Schema drift.
    // -----------------------------------------------------------------

    public function test_every_declared_width_matches_the_column_it_protects(): void
    {
        $columns = collect(Schema::getColumns('events'))->keyBy('name');

        foreach (Event::CLAMPED_COLUMNS as $column => $width) {
            $this->assertTrue($columns->has($column), "events.$column no longer exists");
            $this->assertSame(
                "varchar($width)",
                $columns[$column]['type'],
                "events.$column changed width without CLAMPED_COLUMNS being updated"
            );
        }
    }

    public function test_the_clamp_is_wired_to_the_normalizer_for_the_multiline_field_only(): void
    {
        // A guard on the guard: if normalizeNewlines() ever stops being applied, the production
        // value goes back over the ceiling and this fails before a user finds out.
        $this->assertSame(
            500,
            mb_strlen(TextUtils::clamp(
                TextUtils::normalizeNewlines(str_replace("\n", "\r\n", $this->productionPrompt())),
                500
            ))
        );
    }
}
