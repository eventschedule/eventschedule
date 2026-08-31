<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventComment;
use App\Models\EventVideo;
use App\Models\Sale;
use App\Models\TicketWaitlist;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The hidden `website` decoy on public forms.
 *
 * Three things are worth pinning down, and only the first is obvious:
 *
 * 1. A filled honeypot must stop the write.
 * 2. An EMPTY or ABSENT honeypot must not. Every existing caller and every third-party API
 *    client omits the field, so a check built on has() instead of filled() would break all
 *    of them at once - hence the "allowed" cases below.
 * 3. The rejection must be VISIBLE. x-auth-layout renders only per-field errors and drops
 *    session('error') on the floor, so auth forms need a ValidationException while guest
 *    pages need the flash. Getting this backwards fails silently, which is the exact bug
 *    the honeypot work set out to remove.
 */
class HoneypotTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const TRAP = 'http://spam.example';

    /**
     * import.blade.php swaps the whole form for a setup guide when no AI provider is
     * configured, and CI has no key. Without this the import assertions pass locally
     * and fail (or worse, falsely pass) in CI.
     */
    private function pinAiKey(): void
    {
        config(['services.google.gemini_key' => 'test-key']);
    }

    // -----------------------------------------------------------------
    // Fan engagement: video / photo / comment
    // -----------------------------------------------------------------

    public function test_filled_honeypot_blocks_a_guest_comment(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role);

        $response = $this->post(route('event.submit_comment', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
        ]), [
            'comment' => 'Buy cheap watches',
            'guest_name' => 'Spam Bot',
            'guest_email' => 'bot@spam.example',
            'website' => self::TRAP,
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(0, EventComment::where('event_id', $event->id)->count());
    }

    public function test_filled_honeypot_blocks_a_guest_video(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role);

        $this->post(route('event.submit_video', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
        ]), [
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'guest_name' => 'Spam Bot',
            'guest_email' => 'bot@spam.example',
            'website' => self::TRAP,
        ])->assertSessionHas('error');

        $this->assertSame(0, EventVideo::where('event_id', $event->id)->count());
    }

    public function test_filled_honeypot_blocks_a_guest_photo_before_the_file_is_stored(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role);

        $this->post(route('event.submit_photo', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
        ]), [
            'photo' => UploadedFile::fake()->image('spam.jpg'),
            'guest_name' => 'Spam Bot',
            'guest_email' => 'bot@spam.example',
            'website' => self::TRAP,
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('event_photos', 0);
    }

    // -----------------------------------------------------------------
    // The honeypot must not fire on real submissions
    // -----------------------------------------------------------------

    public function test_empty_honeypot_is_allowed(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role);

        // An empty string is what every real browser submits, so filled() must let it through.
        $this->post(route('event.submit_comment', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
        ]), [
            'comment' => 'Great show',
            'guest_name' => 'Dana Guest',
            'guest_email' => 'dana@gmail.com',
            'website' => '',
        ]);

        $this->assertSame(1, EventComment::where('event_id', $event->id)->count());
    }

    public function test_absent_honeypot_is_allowed(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role);

        // API clients and older embeds never send the key at all.
        $this->post(route('event.submit_comment', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
        ]), [
            'comment' => 'Great show',
            'guest_name' => 'Dana Guest',
            'guest_email' => 'dana@gmail.com',
        ]);

        $this->assertSame(1, EventComment::where('event_id', $event->id)->count());
    }

    // -----------------------------------------------------------------
    // Ticketing and waitlist
    // -----------------------------------------------------------------

    public function test_filled_honeypot_blocks_an_rsvp(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['rsvp_enabled' => true]);

        $this->post(route('event.rsvp', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Spam Bot',
            'email' => 'bot@spam.example',
            'website' => self::TRAP,
        ])->assertSessionHas('error');

        $this->assertSame(0, Sale::where('event_id', $event->id)->count());
    }

    public function test_filled_honeypot_blocks_a_waitlist_join(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['rsvp_enabled' => true, 'rsvp_limit' => 1]);

        // 200 with success:false, because the caller throws a generic "Request failed" on
        // any non-OK status and only renders data.message on a 200.
        $this->postJson(route('waitlist.join', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Spam Bot',
            'email' => 'bot@spam.example',
            'website' => self::TRAP,
        ])->assertOk()->assertJson(['success' => false]);

        $this->assertSame(0, TicketWaitlist::where('event_id', $event->id)->count());
    }

    // -----------------------------------------------------------------
    // Guest event submission
    // -----------------------------------------------------------------

    public function test_filled_honeypot_blocks_a_guest_event_import(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator', ['accept_requests' => true]);

        $this->postJson(route('event.guest_import.store', ['subdomain' => $role->subdomain]), [
            'name' => 'Spam Event',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'website' => self::TRAP,
        ])->assertStatus(422);

        $this->assertSame(0, Event::where('name', 'Spam Event')->count());
    }

    public function test_filled_honeypot_blocks_the_ai_parse_before_it_reaches_gemini(): void
    {
        $role = $this->createRole($this->createOwner(), 'curator', ['accept_requests' => true]);

        $this->postJson(route('event.guest_parse', ['subdomain' => $role->subdomain]), [
            'event_details' => 'Spam night, tomorrow',
            'website' => self::TRAP,
        ])->assertStatus(422);
    }

    public function test_filled_honeypot_blocks_a_guest_image_upload(): void
    {
        $role = $this->createRole($this->createOwner(), 'curator', ['accept_requests' => true]);

        $this->post(route('event.guest_upload_image', ['subdomain' => $role->subdomain]), [
            'image' => UploadedFile::fake()->image('spam.jpg'),
            'website' => self::TRAP,
        ])->assertOk()->assertJson(['success' => false]);
    }

    // -----------------------------------------------------------------
    // Auth forms: the rejection has to land in a per-field error slot,
    // because x-auth-layout never renders session('error').
    // -----------------------------------------------------------------

    public function test_filled_honeypot_blocks_registration(): void
    {
        config(['app.hosted' => true]);

        $this->post('/sign_up', [
            'name' => 'Spam Bot',
            'email' => 'bot@gmail.com',
            'password' => 'password',
            'website' => self::TRAP,
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_filled_honeypot_blocks_login(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'website' => self::TRAP,
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_filled_honeypot_blocks_a_password_reset_request(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->post('/reset-password', [
            'email' => $user->email,
            'website' => self::TRAP,
        ])->assertSessionHasErrors('email');
    }

    // -----------------------------------------------------------------
    // Markup
    // -----------------------------------------------------------------

    public function test_guest_event_page_renders_a_honeypot_in_every_fan_form(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertGreaterThan(0, substr_count($html, 'name="guest_name"'));

        // The guest cart posts to checkout and carries a honeypot of its own without being a fan
        // form, so it is the one honeypot on this page with no matching guest_name. Counted
        // explicitly rather than relaxing this to >=, which would let a fan form lose its honeypot
        // unnoticed - the exact regression this test exists to catch.
        $cartHoneypots = 1;

        // Same for the audience subscribe panel (partials/subscribe-panel.blade.php): a public
        // form, so it carries a honeypot, but it has no guest_name field. Counted explicitly for
        // the reason above - relaxing this to >= would let a fan form lose its honeypot unnoticed.
        $subscribePanelHoneypots = 1;

        $this->assertSame(
            substr_count($html, 'name="guest_name"') + $cartHoneypots + $subscribePanelHoneypots,
            substr_count($html, 'name="website"'),
            'every signed-out fan form must carry a honeypot'
        );
    }

    public function test_guest_event_page_never_emits_an_id_on_the_honeypot(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        // role/edit.blade.php owns id="website" for the real field, and Dusk selects it by id.
        $this->assertStringNotContainsString('id="website"', $html);
    }

    public function test_signed_in_visitor_gets_no_honeypot_on_the_event_page(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $html = $this->actingAs($owner)->get($this->guestEventUrl($role, $event))
            ->assertOk()->getContent();

        // A password manager must never be handed a decoy on an authenticated submission.
        $this->assertStringNotContainsString('name="website"', $html);
    }

    public function test_guest_ai_import_page_renders_a_honeypot(): void
    {
        // import.blade.php is shared with the authenticated flow, so the field is gated on
        // $isGuest. If that gate is written the wrong way round the guest page silently
        // loses its honeypot while the AP page gains one a password manager can fill.
        $this->pinAiKey();

        // require_account defaults to TRUE, and showGuestImport bounces those curators to the
        // structured guest-submit page instead of rendering this one.
        $role = $this->createRole($this->createOwner(), 'curator', [
            'accept_requests' => true,
            'require_account' => false,
        ]);

        $html = $this->get(route('event.guest_import', ['subdomain' => $role->subdomain]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('name="website"', $html);
    }

    public function test_authenticated_import_page_renders_no_honeypot(): void
    {
        $this->pinAiKey();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');

        // show_import is the chooser hub; show_import_ai is the page that renders the shared
        // import.blade.php form with $isGuest unset.
        $html = $this->actingAs($owner)
            ->get(route('event.show_import_ai', ['subdomain' => $role->subdomain]))
            ->assertOk()->getContent();

        // Guard against a false pass: the form must actually be on the page for
        // "no honeypot here" to mean anything.
        $this->assertStringContainsString('id="event-import-app"', $html);
        $this->assertStringNotContainsString('name="website"', $html);
    }

    public function test_booking_request_page_renders_a_honeypot(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['accept_requests' => true]);

        $html = $this->get(route('event.booking_request', ['subdomain' => $role->subdomain]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('name="website"', $html);
    }

    public function test_public_auth_forms_render_a_honeypot(): void
    {
        foreach (['/login', '/sign_up', '/reset-password'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertStringContainsString('name="website"', $html, "{$path} must render a honeypot");
        }
    }

    // -----------------------------------------------------------------
    // Negative guard: `website` is a real field on the schedule form.
    // -----------------------------------------------------------------

    public function test_authenticated_schedule_update_still_accepts_a_real_website(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'new_subdomain' => $role->subdomain,
            'timezone' => $role->timezone,
            'website' => 'https://example.org',
        ]);

        $this->assertSame('https://example.org', $role->fresh()->website);
    }
}
