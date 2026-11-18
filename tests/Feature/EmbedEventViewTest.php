<?php

namespace Tests\Feature;

use App\Models\AnalyticsDaily;
use App\Models\AnalyticsEventsDaily;
use App\Models\AnalyticsReferrersDaily;
use App\Models\Event;
use App\Models\MarketingDailyStat;
use App\Models\Role;
use App\Models\Setting;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An event shown inside an embedded calendar (App\Support\EmbedEventView, ?embed=true&view=event),
 * on the organizer's own website: the Embed dialog's "Show it on your website", ?events=onsite.
 *
 * What it must be: the event's own content, with none of the site's chrome; drawn for a signed-out
 * visitor whoever is signed in, because a page any site may frame must carry nobody's member tools;
 * every link leaving the frame but Back and the other dates; one way to buy, in a new tab, because
 * a cross-site frame has no session; framable; noindex; and counted, as the click that used to open
 * the event's page in a new tab was. And what it must not change: a calendar embed's own loads stay
 * uncounted, and code without the parameter keeps opening events in a new tab.
 */
class EmbedEventViewTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Hall']);
        $this->event = $this->createEvent($this->role, [
            'name' => 'Lantern Night',
            'creator_role_id' => $this->role->id,
            'tickets_enabled' => true,
            'ticket_currency_code' => 'USD',
        ]);
        $this->createTicket($this->event, ['type' => 'Balcony', 'price' => 25]);
        $this->event = $this->event->fresh();
    }

    /** A real browser's headers, so the view counters and the guest funnel take the request. */
    private function browser(string $ip = '203.0.113.40'): array
    {
        return [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120 Safari/537.36',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
            'HTTP_ACCEPT_LANGUAGE' => 'en-GB,en;q=0.9',
            'HTTP_CF_CONNECTING_IP' => $ip,
            'REMOTE_ADDR' => $ip,
        ];
    }

    private function framed(?Event $event = null, string $extra = ''): string
    {
        return $this->guestEventUrl($this->role, $event ?? $this->event).'?embed=true&view=event&events=onsite'.$extra;
    }

    public function test_the_event_is_shown_with_its_content_and_none_of_the_sites_chrome(): void
    {
        $response = $this->get($this->framed())->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('Lantern Night', $html);
        $this->assertStringContainsString('<base target="_blank">', $html);
        $this->assertStringContainsString('data-embed-heading', $html);
        $this->assertStringContainsString('data-embed-back', $html);
        $this->assertStringContainsString(__('messages.embed_back_to_calendar'), $html);
        $this->assertStringContainsString(__('messages.embed_view_full_page'), $html);

        // Prices to read, not a form to buy with.
        $this->assertStringContainsString('data-embed-tickets', $html);
        $this->assertStringContainsString('Balcony', $html);
        $this->assertStringContainsString('$25', $html);
        $this->assertStringNotContainsString('id="gp-event-form"', $html);
        $this->assertStringNotContainsString('id="ticket-selector"', $html);

        // No phone bar, cart, follow modal, mailing list or "create your own".
        $this->assertStringNotContainsString('id="gp-mobile-cta"', $html);
        $this->assertStringNotContainsString('id="gp-create-your-own"', $html);
        $this->assertStringNotContainsString('id="es-cart-app"', $html);

        // Framable from any site, never indexed.
        $this->assertStringContainsString('frame-ancestors *', $response->headers->get('Content-Security-Policy'));
        $this->assertNull($response->headers->get('X-Frame-Options'));
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);

        // The frame's half of the conversation with embed.js.
        $this->assertStringContainsString('window.esEmbedFrame', $html);
    }

    /** The one way to buy: the event's own page, in a new tab, with the form open. */
    public function test_buying_and_registering_open_the_events_page_in_a_new_tab(): void
    {
        $html = $this->get($this->framed())->assertOk()->getContent();

        $this->assertSame(1, preg_match('/<a href="([^"]+)" target="_blank" rel="noopener" data-embed-checkout="tickets" aria-label="([^"]+)"/', $html, $buy));
        $this->assertStringContainsString('tickets=true', html_entity_decode($buy[1]));
        $this->assertStringNotContainsString('embed=true', html_entity_decode($buy[1]), 'the event page itself, not another frame');
        $this->assertStringContainsString(__('messages.opens_in_new_tab'), html_entity_decode($buy[2]));

        $rsvp = $this->createEvent($this->role, ['name' => 'Open Rehearsal', 'creator_role_id' => $this->role->id, 'rsvp_enabled' => true]);
        $html = $this->get($this->framed($rsvp))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/<a href="([^"]+)" target="_blank" rel="noopener" data-embed-checkout="rsvp"/', $html, $register));
        $this->assertStringContainsString('rsvp=true', html_entity_decode($register[1]));
        $this->assertStringNotContainsString('id="rsvp-form"', $html);
    }

    /**
     * A page any site may frame carries nobody's tools: the owner, signed in, sees what a visitor
     * sees. And only for that render: they are still signed in on the next request.
     */
    public function test_a_signed_in_owner_sees_it_as_a_visitor_and_stays_signed_in(): void
    {
        $owner = $this->role->user;
        $editUrl = route('event.edit', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($this->event->id)], false);

        $own = $this->actingAs($owner)->get($this->guestEventUrl($this->role, $this->event))->assertOk()->getContent();
        $this->assertStringContainsString($editUrl, $own, 'the owner has an edit link on the ordinary page');

        $framed = $this->actingAs($owner)->get($this->framed())->assertOk()->getContent();
        $this->assertStringNotContainsString($editUrl, $framed);
        $this->assertStringNotContainsString(__('messages.edit_event'), $framed);

        $this->assertTrue(auth()->check(), 'the session is untouched');
        $this->assertSame('web', auth()->getDefaultDriver(), 'the guard is put back');
        $again = $this->actingAs($owner)->get($this->guestEventUrl($this->role, $this->event))->assertOk()->getContent();
        $this->assertStringContainsString($editUrl, $again);
    }

    /** A draft is members-only, and the frame is nobody's: even its owner gets "not available". */
    public function test_a_draft_or_missing_event_shows_the_unavailable_state_with_a_way_back(): void
    {
        $draft = $this->createEvent($this->role, ['name' => 'Secret Draft', 'is_draft' => true, 'creator_role_id' => $this->role->id]);

        $html = $this->actingAs($this->role->user)->get($this->framed($draft))->assertNotFound()->getContent();
        $this->assertStringContainsString('data-embed-unavailable="missing"', $html);
        $this->assertStringNotContainsString('Secret Draft', $html);
        auth()->logout();

        $gone = $this->get('/'.$this->role->subdomain.'/no-such-event/'.UrlUtils::encodeId(999999).'?embed=true&view=event&events=onsite&month=3&year=2027')->assertNotFound();
        $html = $gone->getContent();
        $this->assertStringContainsString('data-embed-unavailable="missing"', $html);
        $this->assertSame(1, preg_match('/<a href="([^"]+)" target="_self" data-embed-back/', $html, $back));
        $this->assertStringContainsString('embed=true', html_entity_decode($back[1]));
        $this->assertStringContainsString('events=onsite', html_entity_decode($back[1]));
        $this->assertStringContainsString('month=3', html_entity_decode($back[1]));
        $this->assertStringContainsString('frame-ancestors *', $gone->headers->get('Content-Security-Policy'));

        // An embed without view=event keeps its quiet empty 404.
        $this->get('/'.$this->role->subdomain.'/no-such-event/'.UrlUtils::encodeId(999999).'?embed=true')->assertNotFound()->assertContent('');
    }

    /** Its form would come back to a frame with no session: the frame links to the page instead. */
    public function test_a_password_event_links_out_and_keeps_its_name_to_itself(): void
    {
        $locked = $this->createEvent($this->role, ['name' => 'Members Night', 'event_password' => 'hunter2', 'creator_role_id' => $this->role->id]);

        $html = $this->get($this->framed($locked), $this->browser())->assertOk()->getContent();
        $this->assertStringContainsString('data-embed-unavailable="password"', $html);
        $this->assertStringNotContainsString('Members Night', $html);
        $this->assertSame(0, AnalyticsEventsDaily::where('event_id', $locked->id)->count(), 'nothing shown, nothing counted');
    }

    /**
     * Back returns to the calendar the frame came from, with its theme, language and month, and is
     * built from those keys alone: no address the request names can become the way back.
     */
    public function test_back_carries_the_frames_settings_and_never_an_address_from_the_request(): void
    {
        $this->role->forceFill(['language_code' => 'en', 'translation_language_code' => 'es'])->save();

        $html = $this->get($this->framed(null, '&dark=true&lang=es&month=3&year=2027&layout=list&back=https://evil.example/x'))
            ->assertOk()->getContent();

        $this->assertSame(1, preg_match('/<a href="([^"]+)" target="_self" data-embed-back/', $html, $back));
        $href = html_entity_decode($back[1]);
        foreach (['embed=true', 'events=onsite', 'dark=true', 'lang=es', 'month=3', 'year=2027', 'layout=list'] as $part) {
            $this->assertStringContainsString($part, $href);
        }
        $this->assertStringStartsWith(route('role.view_guest', ['subdomain' => $this->role->subdomain]), $href);
        $this->assertStringNotContainsString('evil.example', $html);
    }

    /** The other dates of a series stay in the frame, with what the frame carries. */
    public function test_the_other_dates_of_a_series_stay_in_the_frame(): void
    {
        $series = $this->createRecurringEvent($this->role, ['name' => 'Weekly Swing', 'creator_role_id' => $this->role->id]);

        $html = $this->get($this->framed($series, '&dark=false'))->assertOk()->getContent();

        $this->assertSame(1, preg_match('/data-event-more-dates>(.*?)<\/div>/s', $html, $dates));
        $this->assertSame(1, preg_match('/<a href="([^"]+)" class="gk-link" rel="nofollow"\s+target="_self"\s*>/', $dates[1], $first));
        $href = html_entity_decode($first[1]);
        $this->assertStringContainsString('view=event', $href);
        $this->assertStringContainsString('dark=false', $href);
    }

    /**
     * Counted as a view of the event from the organizer's website, as the click to the event's
     * page in a new tab was. The calendar's own loads stay uncounted, and so does its team.
     */
    public function test_it_is_counted_as_an_event_view_from_the_website_and_the_calendar_is_not(): void
    {
        $this->get('/'.$this->role->subdomain.'?embed=true&events=onsite', $this->browser('203.0.113.41'))->assertOk();
        $this->assertSame(0, AnalyticsDaily::where('role_id', $this->role->id)->count(), 'a calendar embed is not a page view');

        $this->actingAs($this->role->user)->get($this->framed(), $this->browser('203.0.113.42'))->assertOk();
        $this->assertSame(0, AnalyticsEventsDaily::where('event_id', $this->event->id)->count(), 'its own team');
        auth()->logout();

        $this->get($this->framed(), $this->browser('203.0.113.43'))->assertOk();
        $this->assertSame(1, AnalyticsEventsDaily::where('event_id', $this->event->id)->count());
        $this->assertSame(1, AnalyticsReferrersDaily::where('role_id', $this->role->id)->where('source', 'embed')->count());
        $this->assertSame(1, (int) MarketingDailyStat::where('date', now()->toDateString())->value('gp_event_visitors'));
    }

    /** The owner's traffic card names the new source. */
    public function test_the_traffic_sources_card_has_a_name_for_the_website(): void
    {
        $card = File::get(resource_path('views/analytics/_web.blade.php'));

        $this->assertStringContainsString("'embed' => 'embed_source'", $card);
        $this->assertStringContainsString("'embed' => 'embed_source_description'", $card);
        $this->assertStringContainsString("'embed' => 'M17.25", $card);
    }

    /**
     * events=onsite reaches the calendar of an embed and nothing else, and its absence keeps
     * today's behaviour: an embed opens events in a new tab.
     */
    public function test_events_onsite_reaches_the_embedded_calendar_and_only_it(): void
    {
        $this->get('/'.$this->role->subdomain.'?embed=true&events=onsite')->assertOk()->assertSee('embedOnsite: true', false);
        $this->get('/'.$this->role->subdomain.'?embed=true')->assertOk()->assertSee('embedOnsite: false', false);
        $this->get('/'.$this->role->subdomain.'?events=onsite')->assertOk()->assertSee('embedOnsite: false', false);

        $embed = $this->get('/'.$this->role->subdomain.'?embed=true&events=onsite&layout=calendar')->getContent();
        $this->assertStringContainsString('data-embed-root', $embed);
        $this->assertStringContainsString('window.esEmbedFrame', $embed);
        // The month keeps its canonical, new-tab links: a plain click is taken over by the script.
        $this->assertSame(1, preg_match('/<a class="gk-cal-ev" :class="chip\.cls" :href="chip\.url"\s+target="_blank" rel="noopener"/', $embed));
    }

    /**
     * Every place the calendar opens an event takes a plain click to the organizer's site when it
     * is asked to, and leaves a modified or middle click to the browser. A source scan, as in
     * CalendarCrawlableLinksTest: these templates only run in the browser.
     */
    public function test_every_click_path_goes_through_the_one_opener(): void
    {
        $calendar = File::get(resource_path('views/role/partials/calendar.blade.php'));
        $script = File::get(resource_path('views/role/partials/month-script.blade.php'));
        $peek = File::get(resource_path('views/role/partials/month-peek.blade.php'));

        $this->assertSame(1, preg_match('/embedOpenEvent\(url, name, e\) \{(.*?)\n        \},/s', $calendar, $opener));
        foreach (['!this.embedOnsite', 'e.defaultPrevented', 'e.button !== 0', 'e.metaKey', 'e.ctrlKey', 'e.shiftKey', 'e.altKey'] as $guard) {
            $this->assertStringContainsString($guard, $opener[1], $guard);
        }
        $this->assertStringContainsString("target.searchParams.set('view', 'event')", $opener[1]);
        $this->assertStringContainsString("frame.post('open'", $opener[1]);
        $this->assertStringContainsString('path: target.pathname + target.search', $opener[1], 'a path, never an address');
        // Nothing reactive in the message: postMessage cannot clone a Vue proxy, and the refusal
        // sent every click to the in-frame fallback (found by EmbedEventJourneyTest).
        $this->assertStringContainsString('labels: { close: String(this.embedLabels.close), dialog: String(this.embedLabels.dialog) },', $opener[1]);
        $this->assertStringNotContainsString('labels: this.embedLabels', $opener[1]);
        $this->assertStringContainsString('target.origin === window.location.origin', $opener[1]);
        $this->assertStringContainsString('this.rememberList();', $opener[1], 'the list keeps its place for Back');

        $this->assertSame(1, preg_match('/onEventLinkClick\(event, e\) \{(.*?)\n        \},/s', $calendar, $link));
        $this->assertStringContainsString('this.embedOpenEvent(this.getEventUrl(event), event.name, e);', $link[1]);
        $this->assertSame(1, preg_match('/navigateToEvent\(event, e\) \{(.*?)\n        \},/s', $calendar, $card));
        $this->assertStringContainsString('this.embedOpenEvent(url, event.name, e)', $card[1]);

        // getEventUrl() carries what the frame was set to, only when asked to.
        $this->assertSame(1, preg_match('/getEventUrl\(event, occurrenceDate = null\) \{(.*?)\n        \},/s', $calendar, $url));
        $this->assertStringContainsString('if (this.embedOnsite) {', $url[1]);
        foreach (["'events=onsite'", "'dark=' + this.embedDark", "'month=' + encodeURIComponent(this.pageMonth)"] as $part) {
            $this->assertStringContainsString($part, $url[1]);
        }

        // The month: its chips, the card's links and the day's panel.
        $this->assertSame(1, preg_match('/monthClick\(ev\) \{(.*?)\n        \},/s', $script, $month));
        $this->assertStringContainsString('this.embedOpenEvent(el.getAttribute(\'href\')', $month[1]);
        $this->assertStringContainsString('monthEmbedFollow(ev, id) {', $script);
        $this->assertSame(5, substr_count($peek, 'monthEmbedFollow($event,'), 'the picture, the name, Details, View Event and a row of the day');
        $this->assertStringContainsString('monthPeek.go.plain && monthEmbedFollow($event, monthPeek.id)', $peek, 'a button that buys keeps its new tab');
    }

    /** The Embed dialog's choice, and the code it hands over for each. */
    public function test_the_embed_dialog_offers_both_choices_and_the_code_for_each(): void
    {
        $owner = $this->role->user;
        $html = $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']))
            ->assertOk()->getContent();

        $this->assertStringContainsString('<select id="embed-events"', $html);
        $this->assertStringContainsString('<option value="onsite">'.e(__('messages.embed_events_onsite')).'</option>', $html);
        $this->assertStringContainsString('<option value="">'.e(__('messages.embed_events_new_tab')).'</option>', $html);
        $this->assertStringContainsString(e(__('messages.embed_events_help')), $html);

        // The copied code, as rendered before the script rebuilds it: the default is onsite, with
        // the script, a title for screen readers and the permissions the overlay's buttons use.
        $this->assertSame(1, preg_match('/<textarea id="iframe-code"[^>]*>(.*?)<\/textarea>/s', $html, $code));
        $snippet = html_entity_decode($code[1]);
        $this->assertStringContainsString('?embed=true&events=onsite"', $snippet);
        $this->assertStringContainsString('title="'.e(__('messages.embed_calendar_iframe_title', ['schedule' => 'Harbour Hall'])).'"', $snippet);
        $this->assertStringContainsString('allow="clipboard-write; web-share; fullscreen"', $snippet);
        $this->assertStringContainsString('<script src="'.app_url('/embed.js').'" async></script>', $snippet);

        // And the script that rebuilds it on every change: no script and no parameter for a new tab.
        $this->assertStringContainsString("selectedEmbedValue('embed-events') === 'onsite'", $html);
        $this->assertStringContainsString("params.push('events=onsite');", $html);
        $this->assertStringContainsString("(onsite ? '\\n<script src=\"' + embedScriptUrl + '\" async><' + '/script>' : '')", $html);
        $this->assertStringContainsString("'embed-events'].includes(event.target.id)", $html);

        // The preview has the same script beside it, so a click in it does what the code will do.
        $this->assertStringContainsString('<script src="'.app_url('/embed.js').'" async', $html);
    }

    /**
     * The ticket and registration widgets posted their form from the frame, which has no session
     * there: they list what is on offer and open the event's page in a new tab with the form open.
     */
    public function test_the_ticket_and_rsvp_widgets_open_the_events_page_in_a_new_tab(): void
    {
        $html = $this->get($this->guestEventUrl($this->role, $this->event).'?tickets=true&embed=true&promo=SPRING10')->assertOk()->getContent();
        $this->assertStringContainsString('data-embed-tickets', $html);
        $this->assertStringContainsString('Balcony', $html);
        $this->assertStringNotContainsString('id="ticket-selector"', $html);
        $this->assertSame(1, preg_match('/<a href="([^"]+)" target="_blank" rel="noopener"\s+data-embed-checkout="tickets"/', $html, $buy));
        $href = html_entity_decode($buy[1]);
        $this->assertStringContainsString('tickets=true', $href);
        $this->assertStringContainsString('promo=SPRING10', $href);
        $this->assertStringNotContainsString('embed=true', $href);

        $rsvp = $this->createEvent($this->role, ['name' => 'Open Rehearsal', 'creator_role_id' => $this->role->id, 'rsvp_enabled' => true]);
        $html = $this->get($this->guestEventUrl($this->role, $rsvp).'?rsvp=true&embed=true')->assertOk()->getContent();
        $this->assertStringNotContainsString('id="rsvp-form"', $html);
        $this->assertSame(1, preg_match('/<a href="([^"]+)" target="_blank" rel="noopener"\s+data-embed-checkout="rsvp"/', $html, $register));
        $this->assertStringContainsString('rsvp=true', html_entity_decode($register[1]));
    }

    /**
     * The live view leaves a schedule's own team and the installation's admins out, and the framed
     * page is drawn as nobody: its beacon still names who is really there.
     */
    public function test_the_live_views_beacon_knows_the_owner_on_a_page_drawn_as_nobody(): void
    {
        Setting::set('realtime_enabled', '1');
        $context = function (string $html): array {
            $this->assertSame(1, preg_match('/var ctx = (\{.*?\});/', $html, $match), 'the page carries no realtime beacon');

            return json_decode($match[1], true);
        };

        $owner = $this->role->user;
        $framed = $context($this->actingAs($owner)->get($this->framed())->assertOk()->getContent());
        $this->assertSame(UrlUtils::encodeId($owner->id), $framed['u']);
        $this->assertSame(1, $framed['tm'] ?? 0, 'the owner is the team, not a visitor');
        $this->assertSame(1, $framed['em']);

        $admin = $this->createOwner(true);
        $this->assertSame(1, $context($this->actingAs($admin)->get($this->framed())->assertOk()->getContent())['a']);
        auth()->logout();

        $visitor = $context($this->get($this->framed())->assertOk()->getContent());
        $this->assertSame('', $visitor['u']);
        $this->assertArrayNotHasKey('tm', $visitor);
    }

    /** The "not available" card is drawn as nobody too, like the page it stands in for. */
    public function test_the_unavailable_card_is_drawn_as_nobody(): void
    {
        $draft = $this->createEvent($this->role, ['name' => 'Secret Draft', 'is_draft' => true, 'creator_role_id' => $this->role->id]);

        $html = $this->actingAs($this->role->user)->get($this->framed($draft))->assertNotFound()->getContent();
        $this->assertStringContainsString('data-embed-unavailable="missing"', $html);
        // The layout's member-only scripts ask auth(): nothing of the owner's reaches the page.
        $this->assertStringNotContainsString($this->role->user->email, $html);
        $this->assertTrue(auth()->check());
        $this->assertSame('web', auth()->getDefaultDriver());
    }

    /**
     * Under the framed event's <base target="_blank"> an in-page link (the skip link, a heading in
     * a description) would open the frame's bare address in a new tab: the frame keeps it on the
     * page. And the frame's height is measured from its content, never from where the credit chip
     * sits: the chip is pushed to the foot of a body at least as tall as the frame, and measuring
     * it grew the frame on every report.
     */
    public function test_in_page_links_stay_on_the_page_and_the_height_does_not_chase_the_chip(): void
    {
        $frame = File::get(resource_path('views/partials/embed-frame.blade.php'));

        $this->assertStringContainsString("target.closest('a[href^=\"#\"]')", $frame);
        $this->assertStringContainsString("document.querySelector('base[target]')", $frame);
        $this->assertStringContainsString("document.querySelectorAll('[data-embed-root]')", $frame);
        $this->assertStringNotContainsString("querySelectorAll('[data-embed-root], .es-credit-chip')", $frame);
        $this->assertStringContainsString('bottom += chip.offsetHeight;', $frame);

        // The skip link the layout prints is such a link, on the framed page.
        $html = $this->get($this->framed())->assertOk()->getContent();
        $this->assertStringContainsString('<base target="_blank">', $html);
        $this->assertSame(1, preg_match('/<a [^>]*href="#main-content"/', $html), 'the skip link is a #link');

        // Once greeted, the frame says who it is again, to the host now known: embed.js trusts a
        // frame only after that, from its own origin (EmbedScriptTest).
        $this->assertStringContainsString('window.parent.postMessage(readyMessage(), frame.host);', $frame);
    }

    /**
     * The ticket widget's code carries the same embed.js, so the frame fits the widget (the widget
     * page reports its height once greeted), with a fallback height for a builder that strips
     * scripts, and the event's name as the frame's title.
     */
    public function test_the_ticket_widget_code_fits_its_frame(): void
    {
        $this->event->forceFill(['name' => 'Lantern "Night" & Day'])->save();

        $html = $this->actingAs($this->role->user)
            ->get(route('event.edit', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($this->event->id)]))
            ->assertOk()->getContent();

        $this->assertSame(1, preg_match('/<textarea id="embed-ticket-iframe-code"[^>]*>(.*?)<\/textarea>/s', $html, $code));
        $snippet = html_entity_decode($code[1]);
        $this->assertStringContainsString('?tickets=true&embed=true" title="Lantern &quot;Night&quot; &amp; Day" width="100%" height="420"', $snippet, 'the name is escaped for its attribute');
        $this->assertStringContainsString("\n<script src=\"".app_url('/embed.js').'" async></script>', $snippet);
        $this->assertStringNotContainsString('height="700"', $snippet);
        $this->assertStringContainsString('<script src="'.app_url('/embed.js').'" async', $html, 'the preview fits itself too');

        // The widget page carries the frame's half, and the root it measures.
        $widget = $this->get($this->guestEventUrl($this->role, $this->event).'?tickets=true&embed=true')->assertOk()->getContent();
        $this->assertStringContainsString('window.esEmbedFrame', $widget);
        $this->assertStringContainsString('data-embed-root', $widget);
    }

    /** The frame's half of the protocol listens to the window that framed it, and only then talks. */
    public function test_the_frame_script_hears_only_its_parent_and_answers_only_its_host(): void
    {
        $frame = File::get(resource_path('views/partials/embed-frame.blade.php'));

        $this->assertStringContainsString('if (window.parent === window) {', $frame, 'inert outside a frame');
        $this->assertStringContainsString('e.source !== window.parent', $frame);
        $this->assertStringContainsString('window.parent.postMessage(message, frame.host);', $frame);
        $this->assertStringContainsString("frame.share = (typeof e.data.share === 'string'", $frame);
        $this->assertStringContainsString('/^https?:\\/\\//i.test(e.data.share)', $frame);
        // Nothing it sends is the old type the signup form's code resizes ANY frame on.
        $this->assertSame(1, substr_count($frame, 'eventschedule:resize'), 'named once, in the note that says it is never sent');
        $this->assertStringNotContainsString("post('resize'", $frame);
    }
}
