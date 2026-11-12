<?php

namespace Tests\Feature;

use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The bar on an example schedule's own pages (partials/example-schedule-bar): a visitor who opens
 * a demo from /examples or an audience page gets a way back to sign-up, tagged ?ref=example, which
 * sign-up keeps as users.arrival_intent = example. Only on a demo (Role::isDemoContent(): the
 * showcase schedules carry DemoService::DEMO_EMAIL as their contact address), only on the nexus,
 * never in an embed.
 */
class ExampleScheduleBarTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.is_nexus' => true]);
    }

    public function test_an_example_schedule_and_its_event_carry_the_bar(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['email' => DemoService::DEMO_EMAIL]);
        $event = $this->createEvent($role);

        foreach (['/'.$role->subdomain, $this->guestEventUrl($role, $event)] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('data-example-bar', $html, $url);
            $this->assertStringContainsString(e(__('messages.example_schedule_bar')), $html, $url);
            $this->assertStringContainsString(e(app_url('/sign_up?type=venue&ref=example')), $html, $url);
        }
    }

    public function test_the_link_carries_the_schedules_own_type(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['email' => DemoService::DEMO_EMAIL]);

        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString(e(app_url('/sign_up?type=talent&ref=example')), $html);
    }

    public function test_a_real_schedule_has_no_bar(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role);

        $this->assertStringNotContainsString('data-example-bar', $this->get('/'.$role->subdomain)->assertOk()->getContent());
        $this->assertStringNotContainsString('data-example-bar', $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent());
    }

    public function test_an_embed_of_an_example_has_no_bar(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['email' => DemoService::DEMO_EMAIL]);
        $this->createEvent($role);

        $html = $this->get('/'.$role->subdomain.'?embed=true')->assertOk()->getContent();

        $this->assertStringNotContainsString('data-example-bar', $html);
    }

    public function test_off_the_nexus_there_is_no_bar(): void
    {
        config(['app.is_nexus' => false]);
        $role = $this->createRole($this->createOwner(), 'venue', ['email' => DemoService::DEMO_EMAIL]);

        $this->assertStringNotContainsString('data-example-bar', $this->get('/'.$role->subdomain)->assertOk()->getContent());
    }

    public function test_sign_up_keeps_ref_example_as_the_arrival_intent(): void
    {
        $this->get(route('sign_up', ['type' => 'venue', 'ref' => 'example']))->assertOk();

        $this->assertSame('example', session('signup_arrival_intent'));
        $this->assertSame('venue', session('signup_role_type'));
    }
}
