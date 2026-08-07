<?php

namespace Tests\Feature;

use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The AP event form mounts Vue on #app with the runtime template compiler and no render
 * function, so everything inside that element is compiled as a Vue template. Blade's
 * escaping does not help: a value of "{{ 7*7 }}" survives it intact and is then evaluated
 * by Vue, and CSP unsafe-eval is on by design.
 *
 * The "Add to schedules" tab is the exposed part, because User::availableEventSchedules()
 * includes schedules the viewer merely FOLLOWS when accept_requests is on - so their name,
 * request terms and sub-schedule names are written by somebody else.
 *
 * Each guarded value must reach the browser inside a v-pre subtree.
 */
class EventFormTemplateInjectionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * Verified to run in a real browser against this app: with the guards removed it sets
     * window.__pwned, with them in place the string is displayed verbatim. The classic
     * `constructor.constructor(...)` form does NOT reach Function under Vue 3 - the compiler
     * scopes it to the component proxy - but the render helper in scope does.
     */
    private const PAYLOAD = '{{ _openBlock.constructor("window.__pwned=1")() }}';

    /** Everything between <div id="app"> and the end of the form is Vue-compiled. */
    private function mountedHtml(string $html): string
    {
        $start = strpos($html, '<div id="app">');
        $this->assertNotFalse($start, 'the event form should still mount Vue on #app');

        return substr($html, $start);
    }

    /**
     * True when every occurrence of $needle sits inside an element carrying v-pre.
     *
     * Deliberately crude but conservative: it walks back to the nearest enclosing tag and
     * checks that tag, or a wrapping <span v-pre> from <x-user-text>, opts out.
     */
    private function assertGuarded(string $html, string $needle, string $label): void
    {
        $escaped = e($needle);
        $offset = 0;
        $found = 0;

        while (($pos = strpos($html, $escaped, $offset)) !== false) {
            $found++;
            $offset = $pos + 1;

            // The opening tag of whatever directly contains this text.
            $tagStart = strrpos(substr($html, 0, $pos), '<');
            $this->assertNotFalse($tagStart, "{$label}: no enclosing tag");
            $openTag = substr($html, $tagStart, strpos($html, '>', $tagStart) - $tagStart + 1);

            // ...or the element wrapping it, for `<p v-pre>text {{ x }}</p>` style markup.
            $before = substr($html, max(0, $pos - 400), min($pos, 400));

            $this->assertTrue(
                str_contains($openTag, 'v-pre') || str_contains($before, 'v-pre'),
                "{$label}: rendered outside a v-pre subtree, so Vue will compile it.\nTag: {$openTag}"
            );
        }

        $this->assertGreaterThan(0, $found, "{$label}: payload never rendered, so this test proves nothing");
    }

    public function test_another_users_schedule_name_and_terms_are_not_compiled_by_vue(): void
    {
        $victim = $this->createOwner();
        $ownSchedule = $this->createRole($victim, 'venue');

        // A schedule someone else owns that the victim follows, and which takes submissions -
        // exactly what availableEventSchedules() pulls into the form.
        $attacker = $this->createOwner();
        $hostile = $this->createRole($attacker, 'curator', [
            'name' => self::PAYLOAD,
            'accept_requests' => true,
            'request_terms' => self::PAYLOAD,
        ]);
        $this->followRole($victim, $hostile, 'follower');

        $hostileGroup = new Group;
        $hostileGroup->role_id = $hostile->id;
        $hostileGroup->name = self::PAYLOAD;
        $hostileGroup->slug = 'hostile-group';
        $hostileGroup->save();

        $html = $this->actingAs($victim)
            ->get(route('event.create', ['subdomain' => $ownSchedule->subdomain]))
            ->assertOk()
            ->getContent();

        $mounted = $this->mountedHtml($html);

        $this->assertStringContainsString(e(self::PAYLOAD), $mounted,
            'the hostile schedule should be listed, otherwise nothing is being tested');

        $this->assertGuarded($mounted, self::PAYLOAD, 'schedule name / request terms / sub-schedule name');
    }

    public function test_the_editing_schedules_own_sub_schedule_names_are_guarded_too(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $group = new Group;
        $group->role_id = $role->id;
        $group->name = self::PAYLOAD;
        $group->slug = 'own-group';
        $group->save();

        $html = $this->actingAs($owner)
            ->get(route('event.create', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        $this->assertGuarded($this->mountedHtml($html), self::PAYLOAD, 'own sub-schedule name');
    }
}
