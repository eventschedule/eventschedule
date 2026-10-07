<?php

namespace Tests\Feature;

use App\Models\Newsletter;
use App\Models\NewsletterRecipient;
use App\Models\Role;
use App\Models\RoleSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The pages a schedule's own mail links to: confirm, manage, unsubscribe.
 *
 * They stood under the platform's logo, which linked to the platform's marketing site. Somebody
 * who follows a venue's newsletter and presses "unsubscribe" in it has never heard of us: the
 * page that answers is the venue's, so it carries the venue's name and logo and the way back to
 * the venue's page. The credit a schedule's own page carries, it carries too, by the same rule.
 */
class GuestMailLinkPagesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function subscriber(Role $role): RoleSubscriber
    {
        return RoleSubscriber::create([
            'role_id' => $role->id, 'email' => 'fan@fans.test',
            'token' => RoleSubscriber::newToken(), 'confirmed_at' => now(),
        ]);
    }

    public function test_they_stand_under_the_schedules_name_and_logo(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'The Blue Room', 'profile_image_url' => 'profile_abc.png']);
        $sub = $this->subscriber($role);

        foreach (['/sub/u/'.$sub->token, '/sub/m/'.$sub->token] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertStringContainsString('<title>The Blue Room</title>', $html, $path);
            $this->assertSame(1, preg_match('/<a href="'.preg_quote(e($role->fresh()->getGuestUrl()), '/').'" data-auth-schedule[^>]*>(.*?)<\/a>/s', $html, $m), $path.' leads back to the schedule');
            $this->assertStringContainsString('The Blue Room', $m[1]);
            $this->assertStringContainsString('<img src="'.e($role->fresh()->profile_image_url).'"', $m[1]);
            // And not under ours: no platform logo over it, linked to the platform's own site.
            $this->assertStringNotContainsString('<a href="'.e(marketing_url()).'">', $html, $path);
        }
    }

    public function test_they_carry_the_credit_the_schedules_own_page_carries_and_only_then(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'The Blue Room']);
        $path = '/sub/u/'.$this->subscriber($role)->token;

        // A selfhost install: every schedule's page carries the attribution.
        config(['app.hosted' => false]);
        $this->assertSame('https://eventschedule.com?utm_source=selfhost&utm_medium=footer', $role->fresh()->creditChipUrl());
        $this->assertStringContainsString('href="https://eventschedule.com?utm_source=selfhost&amp;utm_medium=footer"', $this->get($path)->assertOk()->getContent());

        // A paying schedule on eventschedule.com: none on its page, so none here.
        config(['app.hosted' => true, 'app.is_nexus' => true]);
        $this->assertNull($role->fresh()->creditChipReason(), 'fixture: a paid plan, not a granted one');
        $this->assertNull($role->fresh()->creditChipUrl());
        $html = $this->get($path)->assertOk()->getContent();
        $this->assertStringNotContainsString('https://eventschedule.com', $html);
        $this->assertStringNotContainsString('utm_medium=footer', $html);
    }

    public function test_the_platforms_own_pages_keep_its_logo(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Event Schedule</title>', $html);
        $this->assertStringNotContainsString('data-auth-schedule', $html);
        $this->assertStringContainsString('<a href="'.e(marketing_url()).'">', $html);

        // A link that has expired names no schedule, and stands under the platform too. The
        // page is rendered: the mark lives in the layout, so the view's own source never held
        // it and reading the file could not fail.
        $expired = $this->get('/sub/c/'.Str::random(40));
        $expired->assertStatus(410);
        $this->assertStringNotContainsString('data-auth-schedule', $expired->getContent());
        $this->assertStringContainsString('<a href="'.e(marketing_url()).'">', $expired->getContent());
    }

    /**
     * The newsletter unsubscribe page serves two senders: a schedule's newsletter, and the
     * platform's own to its account holders. Only the first is the schedule's page.
     */
    public function test_a_schedules_newsletter_page_is_its_own_and_the_platforms_is_the_platforms(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'The Blue Room']);
        $recipient = fn (array $newsletter) => NewsletterRecipient::create([
            'newsletter_id' => Newsletter::create($newsletter + [
                'user_id' => $owner->id, 'subject' => 'News', 'status' => 'sent', 'template' => 'modern', 'blocks' => [],
            ])->id,
            'email' => 'fan@fans.test', 'name' => 'Fan', 'token' => Str::random(64), 'status' => 'sent', 'sent_at' => now(),
        ]);

        $theirs = $this->get('/nl/u/'.$recipient(['role_id' => $role->id, 'type' => 'schedule'])->token)->assertOk()->getContent();
        $this->assertStringContainsString('data-auth-schedule', $theirs);
        $this->assertStringContainsString('<title>The Blue Room</title>', $theirs);

        $ours = $this->get('/nl/u/'.$recipient(['role_id' => null, 'type' => 'admin'])->token)->assertOk()->getContent();
        $this->assertStringNotContainsString('data-auth-schedule', $ours);
        $this->assertStringNotContainsString('The Blue Room', $ours);
        $this->assertStringContainsString('<a href="'.e(marketing_url()).'">', $ours);
    }

    /**
     * A schedule nobody has claimed has no public page, and Role::getGuestUrl() answers '' for
     * it. The header was a link to '', which reloads the page it is on.
     */
    public function test_an_unclaimed_schedules_name_is_not_a_link_to_nowhere(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Not Yet Live', 'email_verified_at' => null]);
        $this->assertSame('', $role->fresh()->getGuestUrl(), 'fixture: unclaimed');

        $html = $this->get('/sub/u/'.$this->subscriber($role)->token)->assertOk()->getContent();

        $this->assertStringNotContainsString('href=""', $html);
        $this->assertSame(1, preg_match('/<div data-auth-schedule[^>]*>(.*?)<\/div>/s', $html, $m), 'its name stands, unlinked');
        $this->assertStringContainsString('Not Yet Live', $m[1]);
    }
}
