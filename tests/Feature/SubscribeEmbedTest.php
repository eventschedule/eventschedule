<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\SubscriptionConfirmation;
use App\Models\Role;
use App\Models\RoleSubscriber;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The signup form a schedule embeds on its own website (issue #125).
 *
 * The page is ?embed=true&form=subscribe on the guest route, and it posts to
 * role.audience.join_embed. Both run inside a cross-site iframe, where the SameSite=lax session
 * cookie is never sent. So the POST has to be CSRF-exempt, and its outcome has to be rendered
 * rather than flashed. Everything past that is RoleSubscriberController::store(), which
 * RoleSubscriberTest covers.
 */
class SubscribeEmbedTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->role = $this->createRole($this->createOwner());
    }

    private function embedUrl(array $query = []): string
    {
        return route('role.view_guest', ['subdomain' => $this->role->subdomain, 'embed' => 'true', 'form' => 'subscribe'] + $query);
    }

    private function postUrl(): string
    {
        return route('role.audience.join_embed', ['subdomain' => $this->role->subdomain]);
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + ['email' => 'fan@fans.test', 'name' => 'A Fan', 'embed' => 'true', 'source' => 'embed', 'lang' => 'en'];
    }

    public function test_the_embed_renders_only_the_form_and_can_be_framed(): void
    {
        $response = $this->get($this->embedUrl())->assertOk();

        $this->assertStringContainsString('frame-ancestors *', $response->headers->get('Content-Security-Policy'));
        $this->assertFalse($response->headers->has('X-Frame-Options'));

        $html = $response->getContent();
        $this->assertStringContainsString('action="'.$this->postUrl().'"', $html);
        $this->assertStringContainsString('name="source" value="embed"', $html);
        $this->assertStringContainsString('name="embed" value="true"', $html);
        $this->assertStringContainsString('name="website"', $html, 'the honeypot must be on the embedded form');
        $this->assertStringContainsString('eventschedule:resize', $html);
        $this->assertStringNotContainsString('id="gp-calendar"', $html, 'the signup embed is not the calendar embed');
        // Kept to the one ask: the calendar feed line belongs to the full guest page.
        $this->assertStringNotContainsString(route('feed.ical', ['subdomain' => $this->role->subdomain]), $html);
    }

    public function test_the_embed_ignores_the_owners_panel_switch_and_a_signed_in_viewer(): void
    {
        // Pasting the snippet is the owner asking for the form. And the owner's own preview in
        // the Embed dialog is signed in, which would blank it if the panel's guard applied.
        $this->role->forceFill(['show_subscribe_panel' => false])->save();

        $this->actingAs($this->role->user)
            ->get($this->embedUrl())
            ->assertOk()
            ->assertSee('action="'.$this->postUrl().'"', false);
    }

    public function test_the_calendar_embed_still_hides_the_panel(): void
    {
        $html = $this->get(route('role.view_guest', ['subdomain' => $this->role->subdomain, 'embed' => 'true']))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('id="gp-subscribe"', $html);
    }

    public function test_a_pinned_theme_survives_the_post(): void
    {
        // theme-script reads ?dark= from window.location.search, so the pin has to be in the
        // form's action URL. A hidden field would never reach the script.
        $html = $this->get($this->embedUrl(['dark' => 'false']))->assertOk()->getContent();

        $this->assertStringContainsString('action="'.route('role.audience.join_embed', ['subdomain' => $this->role->subdomain, 'dark' => 'false']).'"', $html);
    }

    public function test_a_demo_schedule_has_no_embed(): void
    {
        $demoOwner = $this->createOwner();
        $demoOwner->forceFill(['email' => \App\Services\DemoService::DEMO_EMAIL])->save();
        $demo = $this->createRole($demoOwner, 'venue', ['accept_requests' => false]);
        $this->assertTrue(is_demo_role($demo->fresh()));

        $this->get(route('role.view_guest', ['subdomain' => $demo->subdomain, 'embed' => 'true', 'form' => 'subscribe']))
            ->assertNotFound();
    }

    public function test_an_embedded_signup_is_stored_confirmed_by_email_and_rendered_in_place(): void
    {
        Queue::fake();

        $response = $this->post($this->postUrl(), $this->payload(['lang' => 'de']))->assertOk();

        $sub = RoleSubscriber::where('role_id', $this->role->id)->first();
        $this->assertNotNull($sub);
        $this->assertSame('embed', $sub->source);
        $this->assertSame('de', $sub->locale);
        $this->assertNull($sub->confirmed_at, 'double opt-in: nothing is mailable until confirmed');

        Queue::assertPushed(SendQueuedEmail::class, function ($job) {
            $mailable = (new \ReflectionProperty($job, 'mailable'))->getValue($job);

            return $mailable instanceof SubscriptionConfirmation;
        });

        // Rendered, not redirected: the iframe has no session to carry a flash. Framable, in the
        // done state, and never naming the address (the guest-surface rule).
        $this->assertFalse($response->headers->has('X-Frame-Options'));
        $this->assertStringContainsString('frame-ancestors *', $response->headers->get('Content-Security-Policy'));
        app()->setLocale('de');
        $response->assertSee(__('messages.subscribe_done_heading'), false);
        $response->assertDontSee('fan@fans.test', false);
    }

    public function test_only_the_embed_route_can_mark_a_row_as_embed(): void
    {
        // The "Website" chip on the Followers tab is only true if a posted field cannot fake it.
        $this->post(route('role.audience.join', ['subdomain' => $this->role->subdomain]), $this->payload());

        $this->assertSame('guest_panel', RoleSubscriber::first()->source);
    }

    public function test_a_rejected_address_is_shown_inline_with_the_typed_values(): void
    {
        $response = $this->post($this->postUrl(), $this->payload(['email' => 'not-an-email', 'name' => 'Typed Name']))
            ->assertOk();

        $this->assertSame(0, RoleSubscriber::count());
        $response->assertSee('role="alert"', false);
        $response->assertSee('value="not-an-email"', false);
        $response->assertSee('value="Typed Name"', false);
        $response->assertSee('aria-invalid="true"', false);
    }

    public function test_a_tripped_honeypot_writes_nothing(): void
    {
        $this->post($this->postUrl(), $this->payload(['website' => 'http://spam.example']))
            ->assertOk()
            ->assertSee(__('messages.invalid_request'), false);

        $this->assertSame(0, RoleSubscriber::count());
    }

    public function test_the_ip_throttle_renders_the_form_rather_than_the_platform_error_page(): void
    {
        // App\Http\Middleware\ThrottleRequests is a no-op under app.is_testing, so the 429 is
        // raised by hand and handed to the exception handler, wrapped in SecurityHeaders the way
        // the global middleware wraps it for real.
        $request = Request::create($this->postUrl(), 'POST', $this->payload(['lang' => 'de']));
        $route = app('router')->getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);

        $response = (new \App\Http\Middleware\SecurityHeaders)->handle($request, fn ($r) => app(\Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->render($r, new \Illuminate\Http\Exceptions\ThrottleRequestsException('Too Many Attempts.', null, ['Retry-After' => 60])));

        $this->assertSame(429, $response->getStatusCode());
        $this->assertSame('60', $response->headers->get('Retry-After'));
        $html = $response->getContent();
        app()->setLocale('de');
        $this->assertStringContainsString(e(__('messages.too_many_attempts')), $html, 'in the language the form was in');
        $this->assertStringContainsString('action="'.$this->postUrl().'"', $html, 'the form, not the platform error page');
        $this->assertStringContainsString('value="fan@fans.test"', $html, 'what was typed survives');
        $this->assertFalse($response->headers->has('X-Frame-Options'), 'the error has to render inside the owner\'s iframe');
    }

    public function test_only_the_embedded_join_is_exempt_from_csrf(): void
    {
        // Tests bypass CSRF entirely (runningUnitTests), so the exemption list is checked
        // directly. Both path shapes: hosted serves the schedule at its host root, selfhost under
        // /{subdomain}/. The guest page's own join must stay protected.
        $middleware = app(ValidateCsrfToken::class);
        $method = new \ReflectionMethod($middleware, 'inExceptArray');

        $this->assertTrue($method->invoke($middleware, Request::create('/audience/embed', 'POST')));
        $this->assertTrue($method->invoke($middleware, Request::create('/myschedule/audience/embed', 'POST')));
        $this->assertFalse($method->invoke($middleware, Request::create('/audience/join', 'POST')));
        $this->assertFalse($method->invoke($middleware, Request::create('/myschedule/audience/join', 'POST')));
    }

    public function test_the_followers_tab_offers_the_signup_embed(): void
    {
        $html = $this->actingAs($this->role->user)
            ->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'followers']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('js-open-subscribe-embed', $html);
        $this->assertStringContainsString('id="embed-widget"', $html);
    }

    public function test_the_dialog_warns_only_when_announcements_are_off(): void
    {
        $url = route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'followers']);

        $this->actingAs($this->role->user)->get($url)
            ->assertDontSee(__('messages.embed_subscribe_announcements_off'));

        $this->role->forceFill(['announce_new_events' => false])->save();

        $this->actingAs($this->role->user)->get($url)
            ->assertSee(__('messages.embed_subscribe_announcements_off'));
    }

    public function test_the_website_chip_marks_only_embedded_signups(): void
    {
        foreach (['embed' => 'web@fans.test', 'guest_panel' => 'panel@fans.test'] as $source => $email) {
            RoleSubscriber::create([
                'role_id' => $this->role->id,
                'email' => $email,
                'name' => 'Fan',
                'locale' => 'en',
                'source' => $source,
                'token' => RoleSubscriber::newToken(),
            ]);
        }

        $html = $this->actingAs($this->role->user)
            ->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'followers']))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, 'data-subscriber-source="embed"'));
        $this->assertStringContainsString(__('messages.subscriber_source_website'), $html);
    }
}
