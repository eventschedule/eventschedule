<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The audience-capture surfaces echo a SCHEDULE NAME server-side, and two of the three land inside
 * a Vue mount: the RSVP opt-in label inside #rsvp-form, and the checkout opt-in label inside
 * #ticket-selector. Both were shipped unguarded and had to be fixed - tickets.blade.php already
 * carried a v-pre block a few hundred lines further down for exactly this reason.
 *
 * Same payload and the same crude-but-conservative guard check as
 * EventFormTemplateInjectionTest, which verified it against a real browser: with the guard removed
 * it sets window.__pwned.
 *
 * The name is attacker-controlled in the ordinary case, not an exotic one: anyone can create a
 * schedule, and a curator or venue lists events whose creator_role_id points at somebody else's.
 */
class AudienceTemplateInjectionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const PAYLOAD = '{{ _openBlock.constructor("window.__pwned=1")() }}';

    /** True when every occurrence of $needle sits inside an element carrying v-pre. */
    private function assertGuarded(string $html, string $needle, string $label): void
    {
        $escaped = e($needle);
        $offset = 0;
        $found = 0;

        while (($pos = strpos($html, $escaped, $offset)) !== false) {
            $found++;
            $offset = $pos + 1;

            $tagStart = strrpos(substr($html, 0, $pos), '<');
            $this->assertNotFalse($tagStart, "{$label}: no enclosing tag");
            $openTag = substr($html, $tagStart, strpos($html, '>', $tagStart) - $tagStart + 1);
            $before = substr($html, max(0, $pos - 400), min($pos, 400));

            $this->assertTrue(
                str_contains($openTag, 'v-pre') || str_contains($before, 'v-pre'),
                "{$label}: rendered outside a v-pre subtree, so Vue will compile it.\nTag: {$openTag}"
            );
        }

        $this->assertGreaterThan(0, $found, "{$label}: payload never rendered, so this test proves nothing");
    }

    private function mounted(string $html, string $mountId): string
    {
        $start = strpos($html, 'id="'.$mountId.'"');
        $this->assertNotFalse($start, "the page should still mount Vue on #{$mountId}");

        return substr($html, $start);
    }

    private function hostileSchedule(): Role
    {
        return $this->createRole($this->createOwner(), 'venue', ['name' => self::PAYLOAD]);
    }

    public function test_the_rsvp_opt_in_label_is_not_compiled_by_vue(): void
    {
        $role = $this->hostileSchedule();
        $event = $this->createEvent($role, ['rsvp_enabled' => true]);
        $event->forceFill(['creator_role_id' => $role->id])->save();

        // Both forms are includes on the guest event page, not routes of their own.
        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertGuarded($this->mounted($html, 'rsvp-form'), self::PAYLOAD, 'RSVP opt-in label');
    }

    public function test_the_checkout_opt_in_label_is_not_compiled_by_vue(): void
    {
        $role = $this->hostileSchedule();
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $event->forceFill(['creator_role_id' => $role->id])->save();
        $this->createTicket($event);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertGuarded($this->mounted($html, 'ticket-selector'), self::PAYLOAD, 'checkout opt-in label');
    }

    public function test_the_subscribe_panel_is_not_compiled_by_vue(): void
    {
        // Currently sits just outside #calendar-app, but by a margin of a few hundred characters,
        // so it carries v-pre of its own. This pins that.
        $role = $this->hostileSchedule();
        $this->createEvent($role);

        $html = $this->get($role->getGuestUrl())->assertOk()->getContent();

        $panel = strpos($html, 'id="subscribe-panel"');
        $this->assertNotFalse($panel, 'the subscribe panel should render for a signed-out visitor');

        $this->assertGuarded(substr($html, $panel - 200), self::PAYLOAD, 'subscribe panel schedule name');
    }
}
