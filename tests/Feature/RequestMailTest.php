<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewRequestsNotification;
use App\Services\RequestNotifier;
use App\Utils\RequestSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The email that tells a schedule's owners a request has arrived (issue #126: "the requests
 * fields, to have more details when looking up the mails").
 *
 * It was a count. It now spells each new request out, once: which requests are new is a stamp
 * on the request's own row (event_role.request_notified_at), and both rails send through
 * RequestNotifier. When and how often a mail is sent is GuestSubmitProtectionTest's and
 * NotificationEmailTest's to hold, and neither changed.
 */
class RequestMailTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const FIELDS = [
        'new_0' => ['name' => 'Open to the public', 'type' => 'dropdown', 'options' => 'Open,Closed', 'private' => true, 'show_on_request' => true, 'index' => 1],
        'new_1' => ['name' => 'Room', 'type' => 'multiselect', 'options' => 'Foyer,Garden,Hall', 'show_on_request' => true, 'index' => 2],
        'new_2' => ['name' => 'Step-free', 'type' => 'switch', 'show_on_request' => true, 'index' => 3],
        'new_3' => ['name' => 'Internal code', 'type' => 'string', 'show_on_request' => false, 'index' => 4],
    ];

    /** @return array{0: User, 1: Role} */
    private function venue(array $attrs = []): array
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', $attrs + [
            'name' => 'Student House', 'timezone' => 'Europe/Berlin', 'use_24_hour_time' => true,
            'accept_requests' => true, 'require_approval' => true, 'event_custom_fields' => self::FIELDS,
        ]);

        return [$owner, $venue];
    }

    private function request(Role $role, string $name, array $attrs = []): Event
    {
        $event = $this->createEvent($role, $attrs + [
            'name' => $name,
            'creator_role_id' => $role->id,
            'custom_field_values_role_id' => $role->id,
            'is_guest_submission' => true,
            'contact_name' => 'Sam Guest',
            'contact_email' => 'sam.guest@gmail.com',
            'starts_at' => '2026-10-14 16:00:00',
            'custom_field_values' => ['new_0' => 'Closed', 'new_1' => 'Foyer, Garden', 'new_2' => '1', 'new_3' => 'X-99'],
        ]);
        DB::table('event_role')->where('event_id', $event->id)->where('role_id', $role->id)->update(['is_accepted' => null]);

        return $event->fresh();
    }

    private function stamped(Role $role): int
    {
        return DB::table('event_role')->where('role_id', $role->id)->whereNotNull('request_notified_at')->count();
    }

    /** The mail the notifier sent $user, built in their language as the mailer builds it. */
    private function sentTo(User $user): array
    {
        $mails = [];
        Notification::assertSentTo($user, NewRequestsNotification::class, function ($notification) use (&$mails, $user) {
            $mail = $notification->toMail($user);
            $mails[] = ['subject' => $mail->subject, 'html' => (string) $mail->render(), 'data' => $mail->viewData];

            return true;
        });

        return $mails;
    }

    public function test_the_mail_says_what_is_asked_who_asked_when_and_what_they_answered(): void
    {
        Notification::fake();
        [$owner, $venue] = $this->venue();
        $this->request($venue, 'Reading Circle', ['contact_phone' => '+49 561 555 0100', 'description' => 'We need room for twenty.']);

        $this->assertTrue(app(RequestNotifier::class)->announce($venue));

        $mail = $this->sentTo($owner)[0];
        // The event first: a list of search results shows only how a subject begins.
        $this->assertSame('Request: "Reading Circle" (Student House)', $mail['subject']);
        foreach ([
            'Reading Circle', 'Sam Guest', 'sam.guest@gmail.com', '+49 561 555 0100',
            // The schedule's clock (Berlin), on the schedule's 24 hours.
            'Wed, Oct 14, 2026', '18:00',
            'Room', 'Foyer, Garden', 'Step-free', 'Yes',
            // Owner-only, so a private answer is here as it is on the Requests tab.
            'Open to the public', 'Closed',
            'We need room for twenty.',
        ] as $expected) {
            $this->assertStringContainsString($expected, $mail['html'], $expected.' is missing from the mail');
        }
        // A field the request form does not ask is not an answer to it.
        $this->assertStringNotContainsString('X-99', $mail['html']);
    }

    public function test_each_request_is_spelled_out_in_one_mail_and_no_other(): void
    {
        Notification::fake();
        [$owner, $venue] = $this->venue();
        $this->request($venue, 'Reading Circle');

        app(RequestNotifier::class)->announce($venue);
        $this->assertSame(1, $this->stamped($venue));
        $this->assertFalse(app(RequestNotifier::class)->hasUnannounced($venue));

        $this->request($venue, 'Choir Practice');
        app(RequestNotifier::class)->announce($venue->fresh());

        $mails = $this->sentTo($owner);
        $this->assertCount(2, $mails);
        $this->assertStringContainsString('Choir Practice', $mails[1]['html']);
        $this->assertStringNotContainsString('Reading Circle', $mails[1]['html'], 'a request already told of was told again');
        // What is still waiting is said as a number, under the new one.
        $this->assertSame(2, $mails[1]['data']['requestCount']);
        $this->assertStringContainsString('In all, 2 requests are waiting for your answer.', $mails[1]['html']);
    }

    /** One that arrived inside the quarter of an hour is the noon summary's to spell out. */
    public function test_the_noon_summary_spells_out_what_no_mail_has(): void
    {
        Notification::fake();
        Mail::fake();
        [$owner, $venue] = $this->venue();
        $this->request($venue, 'Reading Circle');
        app(RequestNotifier::class)->announce($venue);
        $this->request($venue, 'Choir Practice');
        $this->request($venue, 'Board Games');

        $this->artisan('app:notify-request-changes');

        $mails = $this->sentTo($owner);
        $this->assertCount(2, $mails);
        $this->assertSame('Requests: "Choir Practice" and 1 more (Student House)', $mails[1]['subject']);
        $this->assertStringContainsString('Choir Practice', $mails[1]['html']);
        $this->assertStringContainsString('Board Games', $mails[1]['html']);
        $this->assertStringNotContainsString('Reading Circle', $mails[1]['html']);

        // And it has nothing more to say tomorrow.
        $this->artisan('app:notify-request-changes');
        Notification::assertSentToTimes($owner, NewRequestsNotification::class, 2);
    }

    /**
     * The count this used to be could not see one: the owner answers an old request while a new
     * one arrives inside the quarter of an hour, the number waiting is what it was, and the new
     * request was never announced by anything.
     */
    public function test_a_request_is_told_even_when_the_number_waiting_has_not_grown(): void
    {
        Notification::fake();
        Mail::fake();
        [$owner, $venue] = $this->venue();
        $old = $this->request($venue, 'Reading Circle');
        app(RequestNotifier::class)->announce($venue);

        $this->request($venue, 'Choir Practice');
        DB::table('event_role')->where('event_id', $old->id)->where('role_id', $venue->id)->update(['is_accepted' => true]);

        $this->artisan('app:notify-request-changes');

        $mails = $this->sentTo($owner);
        $this->assertCount(2, $mails, 'one request waiting before and one after: the old rule sent nothing');
        $this->assertStringContainsString('Choir Practice', $mails[1]['html']);
    }

    /** Field keys collide across schedules: every schedule's first field is new_0. */
    public function test_answers_another_schedules_form_wrote_are_not_printed_as_this_ones(): void
    {
        Notification::fake();
        [$owner, $venue] = $this->venue();
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Quartet', 'event_custom_fields' => ['new_0' => ['name' => 'Fee', 'type' => 'string', 'index' => 1]]]);
        $event = $this->createEvent($act, ['name' => 'Quartet Night', 'creator_role_id' => $act->id, 'custom_field_values_role_id' => $act->id, 'custom_field_values' => ['new_0' => 'Fee 500', 'new_1' => 'Hall']]);
        $event->roles()->attach($venue->id, ['is_accepted' => null]);

        app(RequestNotifier::class)->announce($venue);

        $mail = $this->sentTo($owner)[0];
        $this->assertStringContainsString('Quartet Night', $mail['html']);
        $this->assertStringContainsString('The Quartet', $mail['html'], 'a request from a schedule names the schedule');
        $this->assertStringNotContainsString('Fee 500', $mail['html'], "the act's fee was printed under the venue's first question");
        $this->assertSame([], RequestSummary::describe($event->fresh(), $venue)['answers']);

        // The card on the Requests tab reads the same answers by the same rule.
        $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $venue->subdomain, 'tab' => 'requests']))
            ->assertOk()->assertSee('Quartet Night')->assertDontSee('Fee 500');
    }

    /** The mail and the card are one request read twice. */
    public function test_the_mail_and_the_requests_tab_print_the_same_answers(): void
    {
        Notification::fake();
        [$owner, $venue] = $this->venue();
        $event = $this->request($venue, 'Reading Circle');

        app(RequestNotifier::class)->announce($venue);
        $mail = $this->sentTo($owner)[0]['html'];
        $tab = $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $venue->subdomain, 'tab' => 'requests']))->assertOk()->getContent();

        $answers = RequestSummary::describe($event, $venue)['answers'];
        $this->assertSame(['Open to the public', 'Room', 'Step-free'], array_column($answers, 'label'));
        foreach ($answers as $answer) {
            $this->assertStringContainsString(e($answer['label']), $mail);
            $this->assertStringContainsString(e($answer['label']), $tab);
            foreach ($answer['values'] ?: [$answer['value']] as $value) {
                $this->assertStringContainsString(e($value), $mail);
                $this->assertStringContainsString(e($value), $tab);
            }
        }
    }

    public function test_a_mail_that_could_not_be_sent_leaves_the_request_for_the_next_one(): void
    {
        [$owner, $venue] = $this->venue();
        $this->request($venue, 'Reading Circle');
        Notification::shouldReceive('send')->andThrow(new \RuntimeException('smtp is down'));

        $this->assertFalse(app(RequestNotifier::class)->announce($venue));

        $this->assertSame(0, $this->stamped($venue));
        $this->assertTrue(app(RequestNotifier::class)->hasUnannounced($venue));
    }

    /**
     * A request is marked only when a mail that lists it has left. An owner who had the notice
     * off, or a shared address confirmed this afternoon, still gets today's request at noon.
     */
    public function test_with_nobody_to_tell_the_request_waits_for_somebody(): void
    {
        Notification::fake();
        Mail::fake();
        [$owner, $venue] = $this->venue();
        $venue->users()->updateExistingPivot($owner->id, ['notification_settings' => json_encode(['new_request' => false])]);
        $this->request($venue, 'Reading Circle');

        $this->assertFalse(app(RequestNotifier::class)->announce($venue));
        Notification::assertNothingSent();
        $this->assertSame(0, $this->stamped($venue));

        $venue->users()->updateExistingPivot($owner->id, ['notification_settings' => json_encode(['new_request' => true])]);
        $this->artisan('app:notify-request-changes');

        $this->assertStringContainsString('Reading Circle', $this->sentTo($owner)[0]['html']);
        $this->assertSame(1, $this->stamped($venue));
    }

    /**
     * The push follows the mail. One that cannot be queued used to be able to end the loop with
     * the mail already gone and nothing marked, so the same request was mailed to the first
     * editor again by every run after it, and never reached the second.
     */
    public function test_a_push_that_fails_costs_nobody_their_mail_and_repeats_nothing(): void
    {
        config(['services.onesignal.app_id' => 'app', 'services.onesignal.rest_api_key' => 'key']);
        Notification::fake();
        [$owner, $venue] = $this->venue();
        $owner->forceFill(['push_settings' => ['enabled' => true]])->save();
        $second = $this->createOwner();
        $venue->users()->attach($second->id, ['level' => 'admin']);
        $this->request($venue, 'Reading Circle');
        Bus::shouldReceive('dispatch')->andThrow(new \RuntimeException('the queue is down'));

        $this->assertTrue(app(RequestNotifier::class)->announce($venue));

        Notification::assertSentToTimes($owner, NewRequestsNotification::class, 1);
        Notification::assertSentToTimes($second, NewRequestsNotification::class, 1);
        $this->assertSame(1, $this->stamped($venue));
        $this->assertFalse(app(RequestNotifier::class)->announce($venue->fresh()), 'the same request was announced twice');
    }

    /** An appointment waiting to be confirmed has had its own mail: here it is a number. */
    public function test_a_booking_waiting_to_be_confirmed_is_counted_and_not_spelled_out(): void
    {
        Notification::fake();
        [$owner, $venue] = $this->venue();
        $type = $this->createAppointmentType($venue);
        $booking = $this->request($venue, 'Dana Guest', ['appointment_type_id' => $type->id, 'contact_name' => null, 'contact_email' => null]);

        $this->assertTrue(app(RequestNotifier::class)->announce($venue));

        $mail = $this->sentTo($owner)[0];
        $this->assertSame([], $mail['data']['requests']);
        $this->assertSame('1 pending request(s) for Student House', $mail['subject']);
        $this->assertStringNotContainsString('Dana Guest', $mail['html'], 'a booking is named after its guest');
        $this->assertSame(1, $this->stamped($venue), 'counted once, and not again tomorrow');
    }

    /**
     * Requests filed before answers recorded whose form wrote them (2026-09-29) are still
     * waiting on some schedules. The card showed their answers, and still does.
     */
    public function test_a_request_from_before_answers_recorded_their_schedule_keeps_its_answers(): void
    {
        Notification::fake();
        [$owner, $venue] = $this->venue();
        $event = $this->request($venue, 'Reading Circle', ['custom_field_values_role_id' => null, 'creator_role_id' => null]);

        $this->assertSame(['Open to the public', 'Room', 'Step-free'], array_column(RequestSummary::describe($event, $venue)['answers'], 'label'));
        $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $venue->subdomain, 'tab' => 'requests']))
            ->assertOk()->assertSee('Foyer');
    }

    /** One stored answer that is not text must not take the card and the mail down with it. */
    public function test_an_answer_that_is_not_text_is_skipped(): void
    {
        Notification::fake();
        [$owner, $venue] = $this->venue();
        $event = $this->request($venue, 'Reading Circle', ['custom_field_values' => ['new_0' => ['Closed'], 'new_1' => 'Hall', 'new_2' => null]]);

        app(RequestNotifier::class)->announce($venue);

        $this->assertSame(['Room'], array_column($this->sentTo($owner)[0]['data']['requests'][0]['answers'], 'label'));
        $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $venue->subdomain, 'tab' => 'requests']))->assertOk()->assertSee('Hall');
    }

    /** In a right-to-left line "8:00 PM - 10:00 PM" reads "PM - 10:00 PM 8:00" unless it is its own run. */
    public function test_the_hours_stay_left_to_right_in_a_right_to_left_mail(): void
    {
        Notification::fake();
        [$owner, $venue] = $this->venue(['use_24_hour_time' => false]);
        $owner->forceFill(['language_code' => 'he'])->save();
        $event = $this->request($venue, 'Reading Circle');

        app(RequestNotifier::class)->announce($venue);

        $previous = app()->getLocale();
        app()->setLocale('he');
        try {
            $mail = $this->sentTo($owner)[0];
            $summary = RequestSummary::describe($event, $venue);
        } finally {
            app()->setLocale($previous);
        }
        $this->assertStringContainsString('<bdi dir="ltr">6:00 PM - 8:00 PM</bdi>', $mail['html']);
        $this->assertStringContainsString("\u{2066}6:00 PM - 8:00 PM\u{2069}", $summary['when'], 'the one-line form, for a text part');
    }

    /** The hours are the event's own clock: say whose, when it is not the reader's. */
    public function test_the_zone_is_named_when_the_event_is_not_on_the_schedules_clock(): void
    {
        [$owner, $venue] = $this->venue();
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Quartet', 'timezone' => 'Europe/Lisbon']);
        $hall = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Hall', 'city' => 'Porto']);
        $event = $this->createEvent($act, ['name' => 'Quartet Night', 'creator_role_id' => $act->id, 'starts_at' => '2026-10-14 19:00:00']);
        $event->roles()->attach($hall->id, ['is_accepted' => true]);
        $event->roles()->attach($venue->id, ['is_accepted' => null]);

        $summary = RequestSummary::describe($event->fresh(), $venue);

        $this->assertStringContainsString('20:00', $summary['time']);
        $this->assertStringContainsString('WEST', $summary['time']);
        // And where: a schedule asking to be listed is asked "where?" first.
        $this->assertSame('Harbour Hall, Porto', $summary['where']);
        $this->assertStringNotContainsString('CEST', RequestSummary::describe($this->request($venue, 'Local'), $venue)['time']);
    }

    /** What the forms themselves write, not what a fixture says they write. */
    public function test_a_request_sent_through_each_form_is_spelled_out_with_its_answers(): void
    {
        Notification::fake();
        Mail::fake();
        $answers = ['new_0' => 'Closed', 'new_1' => ['Foyer', 'Garden'], 'new_2' => '1'];
        $event = [
            'name' => 'Jazz Night', 'starts_at' => now()->addDays(10)->format('Y-m-d').' 19:15:00', 'duration' => 2,
            'ticket_currency_code' => 'USD', 'coupon_discount_type' => Event::DEFAULT_COUPON_DISCOUNT_TYPE, 'website' => '',
            'venue_name' => 'The Blue Room', 'venue_city' => 'Springfield', 'venue_country_code' => 'us',
        ];
        $expect = function (User $owner, array $more) {
            $mail = $this->sentTo($owner)[0];
            foreach (array_merge(['Open to the public', 'Closed', 'Foyer, Garden', 'Step-free'], $more) as $text) {
                $this->assertStringContainsString($text, $mail['html'], $text.' is missing from the mail');
            }
        };

        // The submit page, where an account is asked for: the request is from the new schedule.
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['name' => 'City Listings', 'accept_requests' => true, 'require_account' => true, 'require_approval' => true, 'event_custom_fields' => self::FIELDS]);
        $this->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), $event + [
            'custom_field_values' => $answers, 'account_mode' => 'register', 'account_name' => 'New Person',
            'account_email' => 'newperson'.random_int(10000, 99999).'@gmail.com', 'account_password' => 'password123', 'terms' => true,
        ])->assertOk()->assertJsonPath('event.status', 'pending');
        $expect($owner, ['Jazz Night', 'The Blue Room']);
        // That visitor is signed in now, and the next two are strangers.
        auth()->logout();

        // The import page of a schedule that asks for no account.
        $owner = $this->createOwner();
        $open = $this->createCurator($owner, ['name' => 'Open Listings', 'accept_requests' => true, 'require_account' => false, 'require_approval' => true, 'event_custom_fields' => self::FIELDS]);
        $this->postJson(route('event.guest_import.store', ['subdomain' => $open->subdomain]), $event + ['custom_field_values' => $answers])
            ->assertOk()->assertJsonPath('event.status', 'pending');
        $expect($owner, ['Jazz Night']);

        // The booking form.
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent', ['name' => 'The Quartet', 'accept_requests' => true, 'require_approval' => true, 'event_custom_fields' => self::FIELDS]);
        $this->postJson(route('event.booking_request.store', ['subdomain' => $talent->subdomain]), [
            'event_name' => 'Wedding Reception', 'date' => now()->addDays(20)->format('Y-m-d'), 'start_time' => '19:15', 'description' => 'Two sets.',
            'venue_name' => 'The Blue Room', 'venue_city' => 'Springfield', 'venue_country_code' => 'us',
            'contact_name' => 'Sam Rivera', 'contact_email' => 'sam.rivera'.random_int(1000, 9999).'@gmail.com', 'website' => '',
            'custom_field_values' => $answers,
        ])->assertOk();
        $expect($owner, ['Wedding Reception', 'Sam Rivera', 'Two sets.', 'The Blue Room']);
    }

    /**
     * "Save all" on the import page sends a programme as one request per event. Five a mail was
     * five a day: a mail spells out its first five and names every other new one in a line, and
     * all of them have then been told of.
     */
    public function test_more_than_a_mail_spells_out_are_named_in_the_same_mail(): void
    {
        Notification::fake();
        [$owner, $venue] = $this->venue();
        foreach (range(1, RequestNotifier::LISTED + 2) as $number) {
            $this->request($venue, 'Request number '.$number, $number === 6 ? ['starts_at' => '2026-10-16 16:00:00'] : []);
        }

        $this->assertTrue(app(RequestNotifier::class)->announce($venue));

        $mail = $this->sentTo($owner)[0];
        $this->assertCount(RequestNotifier::LISTED, $mail['data']['requests']);
        $this->assertSame('Requests: "Request number 1" and 6 more (Student House)', $mail['subject']);
        $this->assertStringContainsString('7 new requests', $mail['html']);
        $this->assertStringContainsString('Request 1 of 7', $mail['html'], 'its place in words: "1 / 7" reads "7 / 1" right to left');
        $this->assertStringContainsString('Also new', $mail['html']);
        $this->assertSame([
            ['title' => 'Request number 6', 'from' => 'Sam Guest', 'day' => 'Fri, Oct 16, 2026'],
            ['title' => 'Request number 7', 'from' => 'Sam Guest', 'day' => 'Wed, Oct 14, 2026'],
        ], $mail['data']['also']);
        $this->assertStringContainsString('Request number 6', $mail['html']);
        // In the HTML the day's spaces do not break: a line that runs over wraps before the date.
        $this->assertStringContainsString("Fri,\u{00A0}Oct\u{00A0}16,\u{00A0}2026", $mail['html']);
        $this->assertStringNotContainsString('follow in the next email', $mail['html'], 'every one of them is in this mail');
        $this->assertStringNotContainsString('requests are waiting', $mail['html'], 'the heading has said seven');

        $text = view('emails.new_requests_text', $mail['data'])->render();
        $this->assertStringContainsString("Also new\n- Request number 6 · Sam Guest · Fri, Oct 16, 2026\n- Request number 7 · Sam Guest · Wed, Oct 14, 2026", $text);

        // Named is told: no later mail owes any of the seven.
        $this->assertSame(RequestNotifier::LISTED + 2, $this->stamped($venue));
        $this->assertFalse(app(RequestNotifier::class)->hasUnannounced($venue));
        $this->assertFalse(app(RequestNotifier::class)->announce($venue->fresh()));
        Notification::assertSentToTimes($owner, NewRequestsNotification::class, 1);
    }

    /** Past what one mail names, the rest are the next mail's, and it says how many. */
    public function test_requests_past_what_a_mail_names_follow_in_the_next_one(): void
    {
        Notification::fake();
        [$owner, $venue] = $this->venue();
        $inOne = RequestNotifier::LISTED + RequestNotifier::NAMED;
        foreach (range(1, $inOne + 2) as $number) {
            $this->request($venue, 'Request number '.$number);
        }

        app(RequestNotifier::class)->announce($venue);

        $mail = $this->sentTo($owner)[0];
        $this->assertCount(RequestNotifier::LISTED, $mail['data']['requests']);
        $this->assertCount(RequestNotifier::NAMED, $mail['data']['also']);
        $this->assertStringContainsString($inOne.' new requests', $mail['html']);
        $this->assertStringContainsString('2 more follow in the next email.', $mail['html']);
        $this->assertStringNotContainsString('Request number '.($inOne + 1), $mail['html']);
        $this->assertSame($inOne, $this->stamped($venue), 'a request the mail did not name was marked as told');

        app(RequestNotifier::class)->announce($venue->fresh());

        $next = $this->sentTo($owner)[1];
        $this->assertSame(['Request number '.($inOne + 1), 'Request number '.($inOne + 2)], array_column($next['data']['requests'], 'title'));
        $this->assertSame([], $next['data']['also']);
        $this->assertSame($inOne + 2, $this->stamped($venue));
        $this->assertFalse(app(RequestNotifier::class)->hasUnannounced($venue));
    }

    /**
     * Long requests: fewer are spelled out, and the ones that gave way are named with the rest
     * rather than left out of the count the mail opens with.
     */
    public function test_a_request_too_long_to_spell_out_is_named_instead(): void
    {
        Notification::fake();
        $fields = [];
        foreach (range(0, 9) as $number) {
            $fields['new_'.$number] = ['name' => 'Question '.$number, 'type' => 'multiline_string', 'show_on_request' => true, 'index' => $number + 1];
        }
        [$owner, $venue] = $this->venue(['event_custom_fields' => $fields]);
        // Marks that grow sixfold when escaped, so ten short answers weigh what ten long ones do.
        $answers = array_fill_keys(array_keys($fields), str_repeat('"', 150));
        foreach (range(1, RequestNotifier::LISTED) as $number) {
            $this->request($venue, 'Request number '.$number, ['custom_field_values' => $answers]);
        }

        app(RequestNotifier::class)->announce($venue);

        $mail = $this->sentTo($owner)[0];
        $full = count($mail['data']['requests']);
        $this->assertGreaterThan(0, $full);
        $this->assertLessThan(RequestNotifier::LISTED, $full, 'the fixture no longer fills a mail: make the answers longer');
        $this->assertCount(RequestNotifier::LISTED - $full, $mail['data']['also']);
        $this->assertSame('Request number '.($full + 1), $mail['data']['also'][0]['title']);
        $this->assertStringContainsString(RequestNotifier::LISTED.' new requests', $mail['html']);
        $this->assertSame(RequestNotifier::LISTED, $this->stamped($venue));
    }

    /**
     * The sender's name, address, number and message ride on the event, and the event goes on:
     * the act that accepted a booking adds a venue to it. To the venue it is the act's request,
     * and who wrote to the act is the act's to know.
     */
    public function test_a_form_senders_details_are_told_only_to_the_schedule_they_wrote_to(): void
    {
        [$owner, $venue] = $this->venue();
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Quartet']);
        $event = $this->createEvent($act, [
            'name' => 'Wedding Reception', 'creator_role_id' => $act->id, 'is_guest_submission' => true,
            'contact_name' => 'Sam Rivera', 'contact_email' => 'sam.rivera@gmail.com', 'contact_phone' => '555 0100', 'description' => 'Two sets, please.',
        ]);
        $event->roles()->attach($venue->id, ['is_accepted' => null]);
        $event = $event->fresh();

        $toTheAct = RequestSummary::describe($event, $act);
        $this->assertSame(['Sam Rivera', 'sam.rivera@gmail.com', '555 0100', 'Two sets, please.'], [$toTheAct['from'], $toTheAct['email'], $toTheAct['phone'], $toTheAct['note']]);

        $toTheVenue = RequestSummary::describe($event, $venue);
        $this->assertSame(['The Quartet', null, null, null], [$toTheVenue['from'], $toTheVenue['email'], $toTheVenue['phone'], $toTheVenue['note']]);

        // The one-line form a crowded mail names a request in follows the same rule.
        $this->assertSame('Sam Rivera', RequestSummary::line($event, $act)['from']);
        $this->assertSame('The Quartet', RequestSummary::line($event, $venue)['from']);

        $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $venue->subdomain, 'tab' => 'requests']))
            ->assertOk()->assertSee('Wedding Reception')->assertSee('The Quartet')
            ->assertDontSee('Sam Rivera')->assertDontSee('sam.rivera@gmail.com')->assertDontSee('555 0100');
    }

    /** A row from before answers recorded their schedule is this schedule's only if its form can have written it. */
    public function test_an_old_row_another_schedule_made_is_not_read_as_this_schedules_answers(): void
    {
        [$owner, $venue] = $this->venue();
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Quartet']);
        $event = $this->createEvent($act, ['name' => 'Quartet Night', 'creator_role_id' => $act->id, 'custom_field_values' => ['new_1' => 'Hall']]);
        $event->roles()->attach($venue->id, ['is_accepted' => null]);

        $this->assertNull($event->fresh()->custom_field_values_role_id);
        $this->assertSame([], RequestSummary::describe($event->fresh(), $venue)['answers']);
    }

    /** Accepting a request is what publishes an answer to a ticked field, and the card says which. */
    public function test_the_requests_tab_marks_the_answers_that_accepting_will_publish(): void
    {
        $fields = self::FIELDS;
        $fields['new_1']['show_on_event'] = true;
        $fields['new_0']['show_on_event'] = true; // private: never public, whatever is ticked
        [$owner, $venue] = $this->venue(['event_custom_fields' => $fields]);
        $this->request($venue, 'Reading Circle');

        $html = $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $venue->subdomain, 'tab' => 'requests']))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'data-answer-public'));
    }

    /**
     * A pivot update goes through the pivot model's fill(), which drops any column its $fillable
     * does not list without a word: the booking that goes back to waiting has to be able to
     * clear its mark (AppointmentService), or the noon summary never counts it again.
     */
    public function test_a_row_that_waits_again_can_have_its_mark_cleared(): void
    {
        [$owner, $venue] = $this->venue();
        $event = $this->request($venue, 'Reading Circle');
        DB::table('event_role')->where('event_id', $event->id)->update(['request_notified_at' => now(), 'is_accepted' => true]);

        $event->roles()->updateExistingPivot($venue->id, ['is_accepted' => null, 'request_notified_at' => null]);

        $this->assertNull(DB::table('event_role')->where('event_id', $event->id)->value('request_notified_at'));
        $this->assertTrue(app(RequestNotifier::class)->hasUnannounced($venue));
    }

    /** An appointment waiting to be confirmed has had its own mail; here it is a number. */
    public function test_a_mail_with_nothing_to_spell_out_is_the_count_it_was(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'Student House']);

        $mail = (new NewRequestsNotification($venue, 3))->toMail($owner);

        $this->assertSame('3 pending request(s) for Student House', $mail->subject);
        $this->assertStringContainsString('pending requests for Student House', (string) $mail->render());
    }

    /** A date in the reader's own order, on the schedule's clock, in the reader's language. */
    public function test_the_mail_is_written_in_the_readers_language(): void
    {
        Notification::fake();
        [$owner, $venue] = $this->venue();
        $owner->forceFill(['language_code' => 'de'])->save();
        $this->request($venue, 'Lesekreis');

        app(RequestNotifier::class)->announce($venue);

        Notification::assertSentTo($owner, NewRequestsNotification::class, fn ($notification) => $notification->locale === 'de');
        $previous = app()->getLocale();
        app()->setLocale('de');
        try {
            $mail = $this->sentTo($owner)[0];
        } finally {
            app()->setLocale($previous);
        }
        $this->assertSame('Anfrage: "Lesekreis" (Student House)', $mail['subject']);
        $this->assertStringContainsString('Mi., 14. Okt 2026', $mail['html'], 'day, month and year in German order');
        $this->assertStringContainsString('18:00', $mail['html']);
        $this->assertStringContainsString('Offene Anfragen ansehen', $mail['html']);
    }

    /**
     * One request is told at length. Several are each cut shorter, which is what lets five of
     * them fit a mail: without it the list simply stops sooner, and a mailbox search for the
     * fourth request finds nothing.
     */
    public function test_several_requests_are_each_told_more_briefly_than_one(): void
    {
        [$owner, $venue] = $this->venue(['event_custom_fields' => ['new_0' => ['name' => 'Notes', 'type' => 'multiline_string', 'show_on_request' => true, 'index' => 1]]]);
        $long = str_repeat('A sentence of an answer. ', 40);
        $requests = collect(['Reading Circle', 'Choir Practice'])->map(fn ($name) => $this->request($venue, $name, ['description' => $long, 'custom_field_values' => ['new_0' => $long]]));

        $one = (new NewRequestsNotification($venue, 2, $requests->take(1), 1))->toMail($owner)->viewData['requests'][0];
        $several = (new NewRequestsNotification($venue, 2, $requests, 2))->toMail($owner)->viewData['requests'];

        $this->assertGreaterThan(500, mb_strlen($one['answers'][0]['value']));
        $this->assertGreaterThan(500, mb_strlen($one['note']));
        $this->assertCount(2, $several);
        $this->assertLessThan(200, mb_strlen($several[1]['answers'][0]['value']));
        $this->assertLessThan(200, mb_strlen($several[1]['note']));
    }

    public function test_the_text_part_carries_the_same_request(): void
    {
        Notification::fake();
        [$owner, $venue] = $this->venue();
        $this->request($venue, 'Tom & Ann\'s Evening');

        app(RequestNotifier::class)->announce($venue);

        $data = $this->sentTo($owner)[0]['data'];
        $text = view('emails.new_requests_text', $data)->render();
        // Plain text, so an ampersand is an ampersand.
        $this->assertStringContainsString("Tom & Ann's Evening", $text);
        $this->assertStringContainsString('Room: Foyer, Garden', $text);
        $this->assertStringContainsString('From: Sam Guest', $text);
    }
}
