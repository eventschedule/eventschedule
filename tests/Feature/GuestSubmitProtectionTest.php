<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedPush;
use App\Models\Event;
use App\Models\Role;
use App\Models\UsageDaily;
use App\Models\User;
use App\Notifications\NewRequestsNotification;
use App\Services\UsageTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What the public "Submit your event" endpoint owes the person using it.
 *
 * Each test here is a thing the page told a visitor that was not true, or a refusal that cost
 * them something first: an account made for an event that was never saved, a ticked terms box
 * nobody recorded, "we will review it" from a schedule with nobody to review it.
 */
class GuestSubmitProtectionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function curator(array $attrs = []): Role
    {
        return $this->createCurator($this->createOwner(), $attrs + [
            'accept_requests' => true,
            'require_account' => true,
            'require_approval' => true,
            'country_code' => 'us',
        ]);
    }

    /** The keys the page posts for a new account. */
    private function body(array $over = []): array
    {
        return $over + [
            'name' => 'Jazz Night',
            'starts_at' => now()->addDays(10)->format('Y-m-d').' 19:15:00',
            'duration' => 2,
            'ticket_currency_code' => 'USD',
            'coupon_discount_type' => Event::DEFAULT_COUPON_DISCOUNT_TYPE,
            'custom_field_values' => [],
            'account_mode' => 'register',
            'account_name' => 'New Person',
            'account_email' => 'newperson'.random_int(10000, 99999).'@gmail.com',
            'account_password' => 'password123',
            'terms' => true,
            'website' => '',
            'venue_name' => 'The Blue Room',
            'venue_city' => 'Springfield',
            'venue_country_code' => 'us',
        ];
    }

    private function submit(Role $role, array $over = [])
    {
        return $this->postJson(route('event.guest_import.store', ['subdomain' => $role->subdomain]), $this->body($over));
    }

    /** What the import page posts (a schedule that asks for no account): the event, and an account only if one is asked for. */
    private function importBody(array $over = []): array
    {
        $body = $this->body($over);
        if (! ($over['create_account'] ?? false)) {
            unset($body['account_mode'], $body['account_name'], $body['account_email'], $body['account_password'], $body['terms']);
        } else {
            unset($body['account_mode']);
        }

        return $over + $body;
    }

    // ---- the terms box --------------------------------------------------------------------

    public function test_a_new_account_records_that_the_terms_were_accepted(): void
    {
        config(['app.hosted' => true]);
        $body = $this->body();

        $this->postJson(route('event.guest_import.store', ['subdomain' => $this->curator()->subdomain]), $body)
            ->assertOk()->assertJsonPath('success', true);

        $this->assertNotNull(User::where('email', $body['account_email'])->firstOrFail()->terms_accepted_at);
    }

    public function test_a_new_account_is_refused_without_the_terms_and_nothing_is_created(): void
    {
        config(['app.hosted' => true]);
        $curator = $this->curator();
        $users = User::count();

        $this->submit($curator, ['terms' => false])->assertStatus(422)->assertJsonValidationErrors('terms');

        $body = $this->body();
        unset($body['terms']);
        $this->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), $body)
            ->assertStatus(422)->assertJsonValidationErrors('terms');

        $this->assertSame($users, User::count());
        $this->assertSame(0, Event::count());
    }

    public function test_a_selfhost_install_does_not_ask_for_the_terms(): void
    {
        config(['app.hosted' => false]);
        $body = $this->body();
        unset($body['terms']);

        $this->postJson(route('event.guest_import.store', ['subdomain' => $this->curator()->subdomain]), $body)
            ->assertOk()->assertJsonPath('success', true);
    }

    public function test_someone_signing_in_is_not_asked_for_the_terms_again(): void
    {
        config(['app.hosted' => true]);
        $member = User::factory()->create(['password' => bcrypt('their-password'), 'email_verified_at' => now()]);

        $this->submit($this->curator(), [
            'account_mode' => 'login',
            'account_email' => $member->email,
            'account_password' => 'their-password',
            'terms' => false,
        ])->assertOk()->assertJsonPath('success', true);
    }

    // ---- who approves -----------------------------------------------------------------------

    public function test_a_schedule_nobody_owns_does_not_promise_a_review(): void
    {
        $placeholder = new Role;
        $placeholder->subdomain = 'placeholder'.random_int(100, 999);
        $placeholder->type = 'venue';
        $placeholder->name = 'The Unclaimed Hall';
        $placeholder->timezone = 'America/New_York';
        // Said, not left to the columns' defaults: it asks for approval, and has nobody to give it.
        $placeholder->accept_requests = true;
        $placeholder->require_account = true;
        $placeholder->require_approval = true;
        $placeholder->save();

        $this->submit($placeholder->fresh())->assertOk()->assertJsonPath('event.status', 'live');

        $pivot = Event::latest('id')->firstOrFail()->roles()->where('roles.id', $placeholder->id)->firstOrFail()->pivot;
        $this->assertSame(1, (int) $pivot->is_accepted);
    }

    public function test_a_schedule_that_reviews_still_holds_the_event(): void
    {
        $curator = $this->curator();

        $this->submit($curator)->assertOk()->assertJsonPath('event.status', 'pending');

        $pivot = Event::latest('id')->firstOrFail()->roles()->where('roles.id', $curator->id)->firstOrFail()->pivot;
        $this->assertNull($pivot->is_accepted);
    }

    public function test_approval_off_and_the_approved_list_still_publish_at_once(): void
    {
        $this->submit($this->curator(['require_approval' => false]))->assertOk()->assertJsonPath('event.status', 'live');

        // A schedule on the approved list, posting as itself.
        auth()->logout();
        $member = $this->createOwner();
        $talent = $this->createRole($member, 'talent', ['name' => 'The Nightjars']);
        $curator = $this->curator(['approved_subdomains' => [$talent->subdomain]]);

        $this->actingAs($member)->submit($curator)->assertOk()->assertJsonPath('event.status', 'live');
    }

    // ---- refused before anything is created ---------------------------------------------------

    #[DataProvider('refusedValues')]
    public function test_a_value_the_save_cannot_take_is_refused_before_the_account_exists(string $field, $value): void
    {
        $curator = $this->curator();
        $users = User::count();
        $schedules = Role::count();

        $this->submit($curator, [$field => $value])->assertStatus(422)->assertJsonValidationErrors($field);

        $this->assertSame($users, User::count(), 'an account was created for an event that was refused');
        $this->assertSame($schedules, Role::count(), 'a schedule was created for an event that was refused');
        $this->assertSame(0, Event::count());
    }

    public static function refusedValues(): array
    {
        return [
            'a start time that is not one' => ['starts_at', 'tomorrow evening'],
            'no start time' => ['starts_at', null],
            'a venue name longer than its column' => ['venue_name', str_repeat('x', 400)],
            'a street longer than its column' => ['venue_address1', str_repeat('x', 400)],
            'a city longer than its column' => ['venue_city', str_repeat('x', 400)],
            'a price that is not a number' => ['ticket_price', 'ten dollars'],
            'a price too large for its column' => ['ticket_price', 99999999999],
            'a description longer than its column can hold' => ['description', str_repeat('x', 70000)],
            // 16,500 four-byte characters are 66,000 bytes, more than the 65,535 a TEXT column
            // holds, and fewer than the 20,000 characters the rule first allowed.
            'a description of characters that are four bytes each' => ['description', str_repeat("\u{1F3B7}", 16500)],
            'a page name longer than its column' => ['schedule_name', str_repeat('x', 300)],
            'a link longer than its column' => ['event_url', 'https://example.com/'.str_repeat('x', 600)],
            'a promo code longer than its column' => ['coupon_code', str_repeat('x', 300)],
            'a category that is not a number' => ['category_id', 'jazz'],
        ];
    }

    /** The flyer reader answers a postal code as a number now and then; the page posts it as it came. */
    public function test_a_postal_code_that_arrives_as_a_number_is_text(): void
    {
        $this->submit($this->curator(), ['venue_postal_code' => 90210, 'venue_address1' => 12])
            ->assertOk()->assertJsonPath('success', true);

        $this->assertSame('90210', Event::latest('id')->firstOrFail()->venue->postal_code);
    }

    // ---- the server's half of three page fixes ------------------------------------------------
    //
    // The next three passed before the page was rebuilt: the server already took a page name from
    // someone signed in, a start at any minute, and a price of 0. The page was what did not send
    // them (GuestSubmitPageTest and tests/Browser/GuestSubmitJourneyTest.php hold its half). They
    // are here so the endpoint cannot stop taking what the page now relies on.

    /** Someone signed in with no page of their own yet may name the one they are about to get. */
    public function test_a_signed_in_visitor_with_no_page_names_the_one_they_get(): void
    {
        $member = $this->createOwner();

        $this->actingAs($member)->submit($this->curator(), ['schedule_name' => 'The Nightjars'])->assertOk();

        $this->assertSame('The Nightjars', $member->fresh()->talents()->firstOrFail()->name);
    }

    public function test_any_minute_of_the_hour_is_kept(): void
    {
        $curator = $this->curator(['timezone' => 'America/New_York']);

        $this->submit($curator, ['starts_at' => '2027-01-15 19:15:00'])->assertOk();

        // 19:15 in New York in January is 00:15 UTC the next day.
        $this->assertSame('2027-01-16 00:15:00', (string) Event::latest('id')->firstOrFail()->starts_at);
    }

    public function test_a_free_event_satisfies_a_required_price(): void
    {
        $curator = $this->curator(['import_config' => ['fields' => [], 'required_fields' => ['ticket_price' => true]]]);

        $this->submit($curator, ['ticket_price' => 0])->assertOk()->assertJsonPath('success', true);
    }

    /** saveEvent() refuses a schedule at its daily cap. On the import page that came after the account. */
    public function test_a_schedule_at_its_daily_cap_refuses_before_an_account_is_made(): void
    {
        config(['app.hosted' => true]);
        $curator = $this->curator(['require_account' => false]);
        UsageDaily::create([
            'date' => now()->toDateString(),
            'operation' => UsageTrackingService::EVENT_CREATE,
            'role_id' => $curator->id,
            'count' => 100000,
        ]);
        $users = User::count();

        $this->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), $this->importBody(['create_account' => true]))
            ->assertStatus(422)->assertJsonPath('code', 'event_create_limit');

        $this->assertSame($users, User::count(), 'an account was created for an event the cap refused');
        $this->assertSame(0, Event::count());
    }

    // ---- the owner hears about it when it arrives ----------------------------------------------

    public function test_the_owner_is_told_when_a_submission_arrives_and_not_again_at_noon(): void
    {
        Notification::fake();
        Mail::fake();
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['accept_requests' => true, 'require_account' => true, 'require_approval' => true]);
        $touched = $curator->updated_at;

        $this->travel(5)->minutes();
        $this->submit($curator)->assertOk()->assertJsonPath('event.status', 'pending');

        Notification::assertSentToTimes($owner, NewRequestsNotification::class, 1);
        $this->assertSame(1, (int) $curator->fresh()->last_notified_request_count);
        // Stored as operational state: the schedule itself has not "changed", which is what the
        // sitemap would publish as its page's lastmod.
        $this->assertEquals($touched->timestamp, $curator->fresh()->updated_at->timestamp);

        $this->artisan('app:notify-request-changes');
        Notification::assertSentToTimes($owner, NewRequestsNotification::class, 1);
    }

    public function test_an_event_that_goes_live_at_once_mails_nobody(): void
    {
        Notification::fake();
        Mail::fake();
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['accept_requests' => true, 'require_account' => true, 'require_approval' => false]);

        $this->submit($curator)->assertOk()->assertJsonPath('event.status', 'live');

        Notification::assertNothingSent();
    }

    public function test_the_import_page_tells_the_owner_too(): void
    {
        Notification::fake();
        Mail::fake();
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['accept_requests' => true, 'require_account' => false, 'require_approval' => true]);

        $this->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), $this->importBody())
            ->assertOk()->assertJsonPath('event.status', 'pending');

        Notification::assertSentToTimes($owner, NewRequestsNotification::class, 1);
    }

    /**
     * Anyone can send a request, and each one was its own email to every owner and admin. One
     * per schedule per quarter of an hour; what arrives in between is the noon summary's to say.
     */
    public function test_a_second_request_inside_the_window_waits_for_the_noon_summary(): void
    {
        Notification::fake();
        Mail::fake();
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['accept_requests' => true, 'require_account' => true, 'require_approval' => true]);

        $this->submit($curator)->assertOk()->assertJsonPath('event.status', 'pending');
        $this->submit($curator, ['name' => 'Second Night'])->assertOk()->assertJsonPath('event.status', 'pending');

        Notification::assertSentToTimes($owner, NewRequestsNotification::class, 1);
        // Left at what was announced, which is how the summary knows there is more to say.
        $this->assertSame(1, (int) $curator->fresh()->last_notified_request_count);

        $this->artisan('app:notify-request-changes');
        Notification::assertSentToTimes($owner, NewRequestsNotification::class, 2);
        $this->assertSame(2, (int) $curator->fresh()->last_notified_request_count);

        // And once the window has passed, the next one is told at once again.
        $this->travel(16)->minutes();
        $this->submit($curator, ['name' => 'Third Night'])->assertOk();
        Notification::assertSentToTimes($owner, NewRequestsNotification::class, 3);
    }

    /** It is sent from inside a guest's request, whose language is the guest's. */
    public function test_the_owner_is_written_to_in_their_own_language(): void
    {
        Notification::fake();
        Mail::fake();
        $owner = $this->createOwner();
        $owner->forceFill(['language_code' => 'he'])->save();
        $curator = $this->createCurator($owner, ['accept_requests' => true, 'require_account' => true, 'require_approval' => true]);

        $this->submit($curator)->assertOk();

        Notification::assertSentTo($owner, NewRequestsNotification::class, fn ($notification) => $notification->locale === 'he');
    }

    /** The noon summary sends a push with its email. Told at once, the owner gets both at once. */
    public function test_the_owner_gets_the_push_the_noon_summary_would_have_sent(): void
    {
        config(['services.onesignal.app_id' => 'app', 'services.onesignal.rest_api_key' => 'key']);
        Notification::fake();
        Mail::fake();
        Bus::fake([SendQueuedPush::class]);
        $owner = $this->createOwner();
        $owner->forceFill(['push_settings' => ['enabled' => true]])->save();
        $curator = $this->createCurator($owner, ['accept_requests' => true, 'require_account' => true, 'require_approval' => true]);

        $this->submit($curator)->assertOk()->assertJsonPath('event.status', 'pending');

        Bus::assertDispatchedTimes(SendQueuedPush::class, 1);
    }

    /**
     * The button in that email. Neither route names a host, so a bare route() answered with the
     * host of the request that sent the mail: from a guest's submission, the schedule's own,
     * where /{slug}/{id} is a guest route and the owner's button opened "not found". No request in
     * this suite can show that (under APP_TESTING there is one host), so the mail is built here
     * the way the hosted install builds it: hosted, not testing, on a schedule's host.
     */
    public function test_the_owners_email_opens_on_the_app_host_whoever_sends_it(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['accept_requests' => true, 'require_approval' => true]);
        config(['app.hosted' => true, 'app.is_testing' => false, 'app.env' => 'production']);
        URL::forceRootUrl('https://'.$curator->subdomain.'.'._base_domain());

        $mail = (new NewRequestsNotification($curator, 1))->toMail($owner);

        $app = 'https://app.'._base_domain();
        $this->assertSame($app.'/'.$curator->subdomain.'/requests', $mail->viewData['actionUrl']);
        $this->assertStringStartsWith($app.'/unsubscribe?', $mail->viewData['unsubscribeUrl']);
    }

    /**
     * The request is saved by then; a mail server that is down is the owner's problem, not the
     * submitter's. The count is left alone, so the noon summary still says what this could not.
     */
    public function test_a_mail_that_cannot_be_sent_does_not_refuse_the_submission(): void
    {
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('smtp is down'));
        $curator = $this->curator();

        $this->submit($curator)->assertOk()->assertJsonPath('success', true);

        $this->assertSame(1, Event::count());
        $this->assertSame(0, (int) $curator->fresh()->last_notified_request_count);
    }

    // ---- the image ------------------------------------------------------------------------------

    public function test_a_refused_image_says_why_in_words_the_page_shows(): void
    {
        $curator = $this->curator();

        $response = $this->post(
            route('event.guest_upload_image', ['subdomain' => $curator->subdomain]),
            ['image' => UploadedFile::fake()->create('flyer.svg', 4, 'image/svg+xml'), 'website' => ''],
            ['Accept' => 'application/json']
        );

        $response->assertStatus(400)->assertJsonPath('message', 'Use a JPG, PNG, GIF or WebP image.');
    }

    // ---- the page for schedules that do not require an account ---------------------------------

    public function test_a_request_waiting_for_approval_is_not_sent_to_a_page_that_is_not_there(): void
    {
        $curator = $this->curator(['require_account' => false, 'require_approval' => true, 'name' => 'Springfield Live']);
        $response = $this->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), $this->importBody())
            ->assertOk()
            ->assertJsonPath('event.status', 'pending')
            ->assertJsonPath('event.view_url', null);

        $this->assertStringContainsString('Springfield Live reviews each event', $response->json('event.message'));
        $this->get($response->json('event.schedule_url'))->assertOk();
    }

    public function test_a_request_that_needs_no_approval_goes_to_its_event(): void
    {
        $curator = $this->curator(['require_account' => false, 'require_approval' => false]);
        $response = $this->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), $this->importBody())
            ->assertOk()
            ->assertJsonPath('event.status', 'live');

        $this->get($response->json('event.view_url'))->assertOk();
    }
}
