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

    /**
     * The guest calendar is a third Vue mount, #calendar-app, and its <noscript> fallback list is
     * inside it.
     *
     * That block is easy to assume safe and is not. A browser with scripting on parses <noscript>
     * as raw text and serializes it back verbatim into innerHTML - which is what Vue's runtime
     * compiler receives - and Vue does not treat <noscript> as raw text, so it compiles the
     * mustaches. Verified directly against Vue's own compiler: `<noscript>{{ 7*7 }}</noscript>`
     * yields `_toDisplayString(7*7)`.
     *
     * The names here are written by somebody else: on a curator they come from the source
     * schedules, and venue names are invented by calendar sync from third-party calendars.
     */
    public function test_event_names_in_the_calendar_noscript_are_not_compiled_by_vue(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->createEvent($role, [
            'name' => self::PAYLOAD,
            'starts_at' => now()->addDays(3)->setTime(19, 0)->format('Y-m-d H:i:s'),
        ]);

        $html = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        $start = strpos($html, 'id="calendar-app"');
        $this->assertNotFalse($start, 'the guest calendar should still mount Vue on #calendar-app');
        $mounted = substr($html, $start);

        $this->assertStringContainsString(e(self::PAYLOAD), $mounted,
            'the event should be listed in the noscript fallback, otherwise this test proves nothing');

        $this->assertGuarded($mounted, self::PAYLOAD, 'calendar noscript event name');
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

    /**
     * The guest cart added a second Vue mount, #es-cart-app, on every browsing surface of the
     * guest portal - and its error panel is server-rendered Blade inside that mount.
     *
     * The value is attacker-controlled: TicketController::refuseCartLeg() interpolates
     * $event->translatedName() into the flashed message, so an event named with a Vue mustache
     * executes on the schedule's own origin as soon as a visitor's cart is refused for that leg.
     */
    public function test_the_guest_cart_error_is_not_compiled_by_vue(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $html = $this->withSession([
            'cart_submitted' => true,
            'cart_invalid_legs' => ['abc|'],
            'error' => __('messages.cart_event_unavailable', ['event' => self::PAYLOAD]),
        ])
            ->get(route('role.view_guest', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        $start = strpos($html, '<div id="es-cart-app"');
        $this->assertNotFalse($start, 'the guest cart should still mount Vue on #es-cart-app');
        $mounted = substr($html, $start);

        $this->assertStringContainsString(e(self::PAYLOAD), $mounted,
            'the cart error panel should be rendered, otherwise this test proves nothing');

        $this->assertGuarded($mounted, self::PAYLOAD, 'guest cart error message');
    }

    /**
     * Text of the event itself and of the schedule that made it. The form is opened by every
     * schedule the event is listed on, so a description, a category name or an agenda part's name
     * typed on one schedule is read by another schedule's admin.
     *
     * The description was the worst of them: a <textarea> is not exempt (Vue compiles the
     * mustaches inside one), the field takes any length, and it was run in a browser against this
     * form before the guard went in: the payload executed and the description came back empty.
     */
    public function test_the_events_own_text_is_not_compiled_by_vue(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', [
            'event_categories' => [['id' => 100, 'name' => 'cat '.self::PAYLOAD]],
        ]);
        \App\Models\SeatingPlan::create(['role_id' => $venue->id, 'name' => 'plan '.self::PAYLOAD]);
        $event = $this->createEvent($venue, [
            'creator_role_id' => $venue->id,
            'description' => 'desc '.self::PAYLOAD,
        ]);
        $part = \App\Models\EventPart::create(['event_id' => $event->id, 'name' => 'part '.self::PAYLOAD, 'sort_order' => 0]);
        foreach ([false, true] as $approved) {
            \App\Models\EventComment::create([
                'event_id' => $event->id, 'event_part_id' => $part->id, 'guest_name' => 'Guest', 'guest_email' => 'guest@gmail.com',
                'comment' => 'Lovely', 'is_approved' => $approved,
            ]);
        }

        $html = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $venue->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();
        $mounted = $this->mountedHtml($html);

        $this->assertGuarded($mounted, 'desc '.self::PAYLOAD, 'event description');
        $this->assertGuarded($mounted, 'cat '.self::PAYLOAD, 'category name');
        $this->assertGuarded($mounted, 'plan '.self::PAYLOAD, 'seating plan name');
        $this->assertGuarded($mounted, 'part '.self::PAYLOAD, 'agenda part name beside fan content');
        // assertGuarded accepts a v-pre anywhere in the 400 characters before the text, and a
        // comment's own guarded text sits that close: the part's name is held to its own element,
        // once in the pending list and once in the approved one.
        $this->assertSame(2, substr_count($mounted, '<span v-pre>'.e('part '.self::PAYLOAD).'</span>'));

        // The same name stands beside a video and a photo, which this event has none of: every
        // place the view prints it is the guarded one.
        $view = file_get_contents(resource_path('views/event/edit.blade.php'));
        $this->assertSame(6, substr_count($view, '->eventPart->name'));
        $this->assertSame(6, preg_match_all('/<span v-pre>\{\{ \$(video|comment|photo)->eventPart \? \$\1->eventPart->name : /', $view));
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

    /**
     * A different way for a name to break the form, with no Vue in it: inside a script block the
     * characters `<!--<script` put the HTML parser in a state where the block's own closing tag
     * no longer ends it, so the script runs on into the markup after it, fails to parse, and the
     * form never starts. Checked in a browser with two static files, one per encoding.
     *
     * The form prints three names somebody else can write into its script: the event's own, the
     * schedule's sub-schedules, and its last 200 events (a guest's request, a curator's listing, a
     * calendar feed). Each was handed to the json directive as an expression with a comma in it,
     * which the directive reads as its own options, and so printed without the escaping of tags.
     * One event with that name stopped the form for every event of the schedule.
     */
    public function test_a_name_cannot_swallow_the_script_it_is_printed_in(): void
    {
        $name = 'Jazz <!--<script night';
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $group = new Group;
        $group->role_id = $venue->id;
        $group->name = 'Room '.$name;
        $group->slug = 'room';
        $group->save();

        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'name' => $name]);
        $other = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'name' => 'An ordinary evening']);

        foreach ([$event, $other] as $opened) {
            $html = $this->actingAs($owner)
                ->get(route('event.edit', ['subdomain' => $venue->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($opened->id)]))
                ->assertOk()
                ->getContent();

            // Nowhere on the page as written: escaped as HTML where it is text, as JSON where it
            // is script.
            $this->assertStringNotContainsString('<!--<script night', $html, $opened->name);
            $this->assertStringContainsString('"name":"Jazz \u003C!--\u003Cscript night"', $html, 'in the list of events');
            $this->assertStringContainsString('"name":"Room Jazz \u003C!--\u003Cscript night"', $html, 'in the list of sub-schedules');

            if ($opened->is($event)) {
                $this->assertStringContainsString('eventName: "Jazz \u003C!--\u003Cscript night"', $html, 'as the name of the event being edited');
            }
        }
    }

    /**
     * The ticket currency is a free string on the event (varchar 255; the form posted whatever it
     * was given), and the Payment row prints it in a notice when no connected gateway can take it.
     * That notice is read by every admin of every schedule the event is listed on.
     */
    public function test_the_ticket_currency_in_the_payment_notice_is_not_compiled_by_vue(): void
    {
        // Connected to something (an install-wide Payfast, which settles in rand only), and to
        // nothing that takes this event's "currency".
        config([
            'app.hosted' => false,
            'payments.payfast.merchant_id' => '20000200',
            'payments.payfast.merchant_key' => 'platform-merchant-key',
            'payments.payfast.passphrase' => 'platform-passphrase',
            'payments.payfast.sandbox' => true,
            // The suite forces a platform Stripe key, and Stripe is asked about no currency at all.
            'services.stripe_platform.secret' => null,
        ]);
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'tickets_enabled' => true]);
        \Illuminate\Support\Facades\DB::table('events')->where('id', $event->id)->update(['ticket_currency_code' => 'cur '.self::PAYLOAD]);

        $html = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $venue->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();
        $mounted = $this->mountedHtml($html);

        // Held to their own elements: assertGuarded's 400-character window would be satisfied by
        // any v-pre nearby.
        foreach (['no_payment_method_for_currency', 'no_payment_method_for_currency_body'] as $key) {
            $sentence = e(__('messages.'.$key, ['currency' => 'cur '.self::PAYLOAD]));
            $this->assertSame(1, preg_match('/<(div|p)\b([^>]*)>'.preg_quote($sentence, '/').'<\/\1>/', $mounted, $m), $key.' is on the page, alone in its element');
            $this->assertStringContainsString('v-pre', $m[2], $key.' is printed outside v-pre, so Vue compiles the currency');
        }
    }

    /**
     * A message under a field. None of them carries anyone's text today, and the component they
     * all go through must not depend on that staying true: it is used some thirty times inside
     * this mount, and a rule that names what was typed is one edit away.
     */
    public function test_a_message_under_a_field_is_not_compiled_by_vue(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);

        $html = $this->actingAs($owner)
            ->withSession(['errors' => (new \Illuminate\Support\ViewErrorBag)->put('default', new \Illuminate\Support\MessageBag(['name' => ['msg '.self::PAYLOAD]]))])
            ->get(route('event.edit', ['subdomain' => $venue->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();
        $mounted = $this->mountedHtml($html);

        $this->assertStringContainsString('<li v-pre>'.e('msg '.self::PAYLOAD).'</li>', $mounted);
        $this->assertSame(0, preg_match('/<li>'.preg_quote(e('msg '.self::PAYLOAD), '/').'/', $mounted), 'and nowhere without it');
    }

    /** The currency is one the pickers offer, or the save is refused: nothing else is stored. */
    public function test_a_made_up_ticket_currency_is_refused(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'ticket_currency_code' => 'USD']);
        $update = route('event.update', ['subdomain' => $venue->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]);
        $save = fn (array $data) => $this->actingAs($owner)->put($update, array_merge(['name' => 'Named', 'starts_at' => $event->starts_at, 'duration' => 2], $data));

        $save(['ticket_currency_code' => self::PAYLOAD])->assertSessionHasErrors('ticket_currency_code');
        $this->assertSame('USD', $event->fresh()->ticket_currency_code);

        $save(['ticket_currency_code' => 'EUR'])->assertSessionHasNoErrors();
        $this->assertSame('EUR', $event->fresh()->ticket_currency_code, 'a real one still saves');

        // Not posted at all (a locked select sends nothing), and posted blank: neither is refused.
        $save([])->assertSessionHasNoErrors();
        $save(['ticket_currency_code' => ''])->assertSessionHasNoErrors();

        // And a new event.
        $this->actingAs($owner)->post(route('event.store', ['subdomain' => $venue->subdomain]), [
            'name' => 'New one', 'starts_at' => $event->starts_at, 'duration' => 2, 'ticket_currency_code' => self::PAYLOAD,
        ])->assertSessionHasErrors('ticket_currency_code');
    }
}
