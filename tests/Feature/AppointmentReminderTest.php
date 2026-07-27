<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Utils\IcsUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class AppointmentReminderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // The reminder command enforces the hosted SMTP transport gate; run these tests in
        // selfhost mode so reminders send without per-schedule email settings. Keep the default
        // log mailer: the sync queue executes SendQueuedEmail inline, and a real smtp transport
        // would throw, tripping the command's retry path (which resets reminder_sent_at).
        config(['app.hosted' => false]);
    }

    /**
     * Unfold an .ics the way a client does before parsing (RFC 5545 3.1).
     *
     * buildInvite() now folds to the 75-octet limit, so a raw substring match against a long line -
     * `mailto:` on the ATTENDEE line, for instance - fails on a boundary that is entirely legal.
     */
    private function unfold(string $ics): string
    {
        return str_replace("\r\n ", '', $ics);
    }

    /** A confirmed (or pending) appointment $hoursOut from now, with a paid cash sale. */
    private function appointment(Role $role, int $hoursOut, $accepted = true): array
    {
        $type = $this->createAppointmentType($role);

        $event = new Event;
        $event->name = 'Consult - Jane';
        $event->starts_at = now()->addHours($hoursOut)->format('Y-m-d H:i:s');
        $event->duration = 0.5;
        $event->timezone = 'America/New_York';
        $event->is_private = true;
        $event->creator_role_id = $role->id;
        $event->user_id = $role->user_id;
        $event->appointment_type_id = $type->id;
        $event->slug = 'x-'.strtolower(Str::random(8));
        $event->save();
        $event->roles()->attach($role->id, ['is_accepted' => $accepted]);

        $sale = new Sale;
        $sale->event_id = $event->id;
        $sale->subdomain = $role->subdomain;
        $sale->name = 'Jane';
        $sale->email = 'jane@gmail.com';
        $sale->event_date = now()->addHours($hoursOut)->setTimezone('America/New_York')->format('Y-m-d');
        $sale->status = 'paid';
        $sale->payment_method = 'cash';
        $sale->payment_amount = 0;
        $sale->secret = strtolower(Str::random(32));
        $sale->confirmed_at = now();
        $sale->save();

        return [$event, $sale];
    }

    public function test_reminder_sent_once_for_confirmed_booking_in_window(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        [, $sale] = $this->appointment($role, 12);

        $this->artisan('app:send-appointment-reminders')->assertSuccessful();
        $this->assertNotNull($sale->fresh()->reminder_sent_at);

        $firstSentAt = $sale->fresh()->reminder_sent_at->timestamp;
        $this->artisan('app:send-appointment-reminders')->assertSuccessful();
        $this->assertSame($firstSentAt, $sale->fresh()->reminder_sent_at->timestamp); // no re-send
    }

    public function test_pending_booking_is_not_reminded(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        [, $sale] = $this->appointment($role, 12, null); // pivot null = pending approval

        $this->artisan('app:send-appointment-reminders')->assertSuccessful();
        $this->assertNull($sale->fresh()->reminder_sent_at);
    }

    public function test_booking_outside_window_is_not_reminded(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        [, $sale] = $this->appointment($role, 48); // more than 24h out

        $this->artisan('app:send-appointment-reminders')->assertSuccessful();
        $this->assertNull($sale->fresh()->reminder_sent_at);
    }

    public function test_ics_invite_is_valid(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        [$event] = $this->appointment($role, 12);

        $ics = IcsUtils::buildInvite($event->fresh(), $role);
        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('BEGIN:VEVENT', $ics);
        $this->assertStringContainsString('SUMMARY:', $ics);
        $this->assertStringContainsString('DTSTART:', $ics);
    }

    /**
     * The PUBLISH/REQUEST split is deliberate and easy for a future refactor to flatten. PUBLISH means
     * "add this to your calendar" and each import makes a NEW entry; REQUEST is a real iTIP invitation
     * that updates the entry the client already has. Only the reschedule mail wants the latter, and it
     * needs the ORGANIZER/ATTENDEE pair to work at all - SEQUENCE alone does nothing under PUBLISH.
     */
    public function test_publish_is_the_default_and_request_carries_organizer_and_attendee(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        [$event, $sale] = $this->appointment($role, 12);
        $event = $event->fresh();

        $publish = IcsUtils::buildInvite($event, $role, $sale);
        $this->assertStringContainsString('METHOD:PUBLISH', $publish);
        $this->assertStringNotContainsString('ORGANIZER', $publish);
        $this->assertStringNotContainsString('ATTENDEE', $publish);

        $request = IcsUtils::buildInvite($event, $role, $sale, 'REQUEST');
        $this->assertStringContainsString('METHOD:REQUEST', $request);
        $this->assertStringContainsString('ORGANIZER;CN=', $request);
        $this->assertStringContainsString('PARTSTAT=ACCEPTED', $request);
        // Unfolded: the ATTENDEE line is over 75 octets, so mailto: legitimately spans a fold.
        $this->assertStringContainsString('mailto:'.$sale->email, $this->unfold($request));

        // Same UID either way, so a REQUEST can update an entry a PUBLISH created.
        $uid = fn ($ics) => trim(explode("\r\n", explode('UID:', $ics)[1])[0]);
        $this->assertSame($uid($publish), $uid($request));

        // An unknown method must not smuggle itself into the header.
        $this->assertStringContainsString('METHOD:PUBLISH', IcsUtils::buildInvite($event, $role, $sale, 'NONSENSE'));
    }

    /**
     * CN is a param-value, where backslash escaping is undefined and a colon ENDS the value. TEXT-escaping
     * it meant a guest called "Dr: Smith" produced a CAL-ADDRESS of "Smith:mailto:..." - so no ATTENDEE
     * matched the recipient, which is the one property iTIP needs to update an existing entry, and the
     * whole reason the reschedule mail uses REQUEST silently stopped working.
     */
    public function test_a_guest_name_with_punctuation_keeps_the_attendee_address_parseable(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);

        foreach (['Dr: Smith', 'Doe, Jane', 'O;Brien', 'Quote"Name'] as $name) {
            [$event, $sale] = $this->appointment($role, 12);
            $sale->forceFill(['name' => $name])->save();

            $ics = IcsUtils::buildInvite($event->fresh(), $role, $sale->fresh(), 'REQUEST');

            // Unfold before parsing, exactly as a real client does.
            $unfolded = str_replace("\r\n ", '', $ics);
            $attendee = null;
            foreach (explode("\r\n", $unfolded) as $line) {
                if (str_starts_with($line, 'ATTENDEE')) {
                    $attendee = $line;
                }
            }
            $this->assertNotNull($attendee, "no ATTENDEE line for [$name]");

            // The CAL-ADDRESS is everything after the LAST colon that closes the parameter list. Split
            // the way a parser does: params end at the first colon that is not inside a quoted string.
            $inQuotes = false;
            $valueAt = null;
            for ($i = 0; $i < strlen($attendee); $i++) {
                if ($attendee[$i] === '"') {
                    $inQuotes = ! $inQuotes;
                } elseif ($attendee[$i] === ':' && ! $inQuotes) {
                    $valueAt = $i;
                    break;
                }
            }
            $this->assertNotNull($valueAt, "no unquoted value separator for [$name]");
            $this->assertSame(
                'mailto:'.$sale->email,
                substr($attendee, $valueAt + 1),
                "the CAL-ADDRESS is corrupted for [$name]"
            );
        }
    }

    /**
     * RFC 5545 3.1: content lines MUST NOT exceed 75 octets. The ATTENDEE prefix alone is 73 before the
     * name, so every REQUEST invite broke this and strict parsers reject them.
     */
    public function test_every_line_is_folded_to_the_octet_limit_without_corrupting_utf8(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        [$event, $sale] = $this->appointment($role, 12);

        // A long multibyte name plus a long description: both must fold and both must survive.
        $name = str_repeat('יעל בן־דוד ', 6);
        $sale->forceFill(['name' => $name])->save();
        $event->forceFill(['description' => str_repeat('日本語の説明テキスト ', 12)])->save();

        $ics = IcsUtils::buildInvite($event->fresh(), $role, $sale->fresh(), 'REQUEST');

        foreach (explode("\r\n", $ics) as $line) {
            $this->assertLessThanOrEqual(75, strlen($line), 'over the 75-octet limit: '.$line);
            $this->assertTrue(mb_check_encoding($line, 'UTF-8'), 'a fold split a multibyte character');
        }

        // Unfolding restores the values exactly.
        $unfolded = str_replace("\r\n ", '', $ics);
        $this->assertStringContainsString(trim($name), $unfolded);
        $this->assertStringContainsString('日本語の説明テキスト', $unfolded);
    }

    public function test_a_booking_awaiting_approval_is_tentative(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        [$event, $sale] = $this->appointment($role, 12);

        $ics = IcsUtils::buildInvite($event->fresh(), $role, $sale, 'REQUEST');
        $this->assertStringNotContainsString('STATUS:TENTATIVE', $ics);
        $this->assertStringContainsString('PARTSTAT=ACCEPTED', $ics);

        // Back to pending: the guest's calendar must not show a solid entry for an unapproved time.
        $event->roles()->updateExistingPivot($event->creator_role_id, ['is_accepted' => null]);

        $pending = IcsUtils::buildInvite($event->fresh(), $role, $sale, 'REQUEST');
        $this->assertStringContainsString('STATUS:TENTATIVE', $pending);
        $this->assertStringContainsString('PARTSTAT=NEEDS-ACTION', $pending);
    }
}
