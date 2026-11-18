<?php

namespace Tests\Feature;

use App\Http\Controllers\EmbedScriptController;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * embed.js, the script the Embed dialog's "Show it on your website" code puts on an organizer's own
 * page (resources/embed/embed.js). It runs on somebody else's website and takes messages from
 * frames, so what it must never do is the point: act on a message from a window that is not one of
 * its calendars, open an address that is not a path on that calendar's own origin, or answer the
 * old eventschedule:resize type, which the signup form's code resizes ANY frame on.
 *
 * Served by a route with a short cache, because the address is frozen into customers' HTML.
 *
 * The JavaScript is run for real in Node, against a stub of the few DOM pieces it touches, as
 * SentryJsFilterTest runs its partial: a PHP restatement of the checks would test nothing.
 */
class EmbedScriptTest extends TestCase
{
    public function test_it_is_served_with_a_short_cache_and_no_cookie(): void
    {
        $response = $this->get('/embed.js')->assertOk();

        $this->assertStringStartsWith('application/javascript', $response->headers->get('Content-Type'));
        $cache = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cache);
        $this->assertStringContainsString('max-age='.EmbedScriptController::MAX_AGE, $cache);
        $this->assertLessThanOrEqual(600, EmbedScriptController::MAX_AGE, 'a fix must reach every site that pasted it within minutes');
        $this->assertSame([], $response->headers->getCookies(), 'outside the web group: no session, no cookie');
        $this->assertSame(file_get_contents(resource_path('embed/embed.js')), $response->getContent());
        $this->assertSame(app_url('/embed.js'), url('/embed.js'));
    }

    public function test_it_names_only_its_own_message_types(): void
    {
        $script = file_get_contents(resource_path('embed/embed.js'));

        // The window's own resize event, to size the overlay, is the one "resize" in it.
        $this->assertSame(1, substr_count($script, 'resize'), 'never the old message type');
        $this->assertStringContainsString("window.addEventListener('resize', size);", $script);
        $this->assertStringContainsString('data.type.indexOf(PREFIX) !== 0', $script);
        $this->assertStringContainsString('e.source === overlay.iframe.contentWindow', $script);
        $this->assertStringContainsString('var iframe = frameFor(e.source);', $script);
        $this->assertStringContainsString('resolve(data.path, e.origin)', $script);
        $this->assertStringContainsString('wrapped = window.self !== window.top;', $script);
    }

    /**
     * Run the script in Node against a stubbed page and report what it did.
     *
     * @return array<string, mixed>
     */
    private function runScenarios(): array
    {
        $harness = <<<'JS'
        var src = require('fs').readFileSync(process.argv[2], 'utf8');

        function env(opts) {
            opts = opts || {};
            var posted = [];
            var emitted = [];
            var history = { back: 0, replaced: null, state: null };
            var winListeners = {};
            var focused = null;

            function makeEl(tag) {
                var el = {
                    tagName: tag.toUpperCase(),
                    attrs: {},
                    children: [],
                    listeners: {},
                    parentNode: null,
                    style: { overflow: '', props: {}, setProperty: function (k, v) { this.props[k] = v; }, removeProperty: function (k) { delete this.props[k]; } },
                    setAttribute: function (k, v) { this.attrs[k] = String(v); },
                    getAttribute: function (k) { return Object.prototype.hasOwnProperty.call(this.attrs, k) ? this.attrs[k] : null; },
                    removeAttribute: function (k) { delete this.attrs[k]; },
                    appendChild: function (c) { this.children.push(c); c.parentNode = this; return c; },
                    removeChild: function (c) { this.children = this.children.filter(function (x) { return x !== c; }); c.parentNode = null; return c; },
                    addEventListener: function (t, f) { (this.listeners[t] = this.listeners[t] || []).push(f); },
                    focus: function () { focused = this; },
                    showModal: function () { this.open = true; },
                    close: function () { this.open = false; },
                };
                if (tag === 'iframe') {
                    el.contentWindow = { postMessage: function (message, origin) { posted.push({ frame: el, message: message, origin: origin }); } };
                }
                return el;
            }

            var iframes = (opts.iframes || []).map(function (src) {
                var f = makeEl('iframe');
                f.setAttribute('src', src);
                return f;
            });

            var location = {
                href: 'https://organizer.example/events',
                pathname: '/events',
                search: '',
                _hash: opts.hash || '',
                get hash() { return this._hash; },
                set hash(v) { this._hash = v ? (v.charAt(0) === '#' ? v : '#' + v) : ''; },
            };

            var doc = {
                readyState: 'complete',
                documentElement: makeEl('html'),
                head: makeEl('head'),
                body: makeEl('body'),
                getElementsByTagName: function (t) { return t === 'iframe' ? iframes : []; },
                createElement: makeEl,
                addEventListener: function () {},
            };

            var win = {
                location: location,
                history: {
                    get state() { return null; },
                    back: function () { history.back++; },
                    replaceState: function (s, t, u) { history.replaced = u; location._hash = ''; },
                },
                addEventListener: function (t, f) { (winListeners[t] = winListeners[t] || []).push(f); },
                dispatchEvent: function (e) { emitted.push({ type: e.type, detail: e.detail }); },
                matchMedia: function () { return { matches: false }; },
            };
            win.self = win;
            win.top = opts.wrapped ? {} : win;

            function CE(type, init) { this.type = type; this.detail = init && init.detail; }

            new Function('window', 'document', 'CustomEvent', src)(win, doc, CE);

            return {
                win: win,
                doc: doc,
                iframes: iframes,
                posted: posted,
                emitted: emitted,
                history: history,
                listeners: winListeners,
                fire: function (data, origin, source) {
                    (winListeners.message || []).forEach(function (f) { f({ data: data, origin: origin, source: source }); });
                },
                dialog: function () {
                    return doc.body.children.filter(function (c) { return c.tagName === 'DIALOG'; })[0] || null;
                },
            };
        }

        var CAL = 'https://sub.example.com';
        var GOOD = '/sub/lantern-night/abc?embed=true&view=event&events=onsite';
        var out = {};

        var R = env().win.EventScheduleEmbed.resolve;
        out.resolve = {
            good: R(GOOD, CAL),
            javascript: R('javascript:alert(1)', CAL),
            schemeRelative: R('//evil.example/x?embed=true&view=event', CAL),
            backslash: R('/\\evil.example/x?embed=true&view=event', CAL),
            absolute: R('https://evil.example/x?embed=true&view=event', CAL),
            notTheEventView: R('/sub/lantern-night/abc?embed=true', CAL),
            badOrigin: R(GOOD, 'javascript:alert(1)'),
            nullOrigin: R(GOOD, 'null'),
            whitespace: R('/sub/x y?embed=true&view=event', CAL),
            notAString: R({ toString: function () { return GOOD; } }, CAL),
        };

        // The handshake: a calendar that says it is ready is greeted, at its own origin.
        var e = env({ iframes: [CAL + '/sub?embed=true&events=onsite'] });
        out.greetedOnLoad = e.posted.map(function (p) { return [p.message.type, p.origin]; });
        e.posted.length = 0;
        e.fire({ type: 'eventschedule:ready', v: 1 }, CAL, e.iframes[0].contentWindow);
        out.helloOnReady = e.posted.map(function (p) { return [p.message.type, p.origin]; });

        // Height: the new type fits the frame; the old type does nothing; nonsense does nothing.
        e.fire({ type: 'eventschedule:resize', height: 999 }, CAL, e.iframes[0].contentWindow);
        out.afterOldResize = e.iframes[0].style.height || null;
        e.fire({ type: 'eventschedule:height', height: 1234.2 }, CAL, e.iframes[0].contentWindow);
        out.afterHeight = e.iframes[0].style.height || null;
        e.fire({ type: 'eventschedule:height', height: 'x' }, CAL, e.iframes[0].contentWindow);
        out.afterBadHeight = e.iframes[0].style.height || null;

        // A window that is not one of its calendars is not heard.
        e.fire({ type: 'eventschedule:open', path: GOOD }, CAL, { postMessage: function () {} });
        out.dialogFromStranger = !!e.dialog();

        // A path that is not one is not opened.
        e.fire({ type: 'eventschedule:open', path: 'javascript:alert(1)' }, CAL, e.iframes[0].contentWindow);
        out.dialogFromJavascript = !!e.dialog();

        // A real event opens over the page, from the calendar's own origin, and says so.
        e.fire({ type: 'eventschedule:open', path: GOOD, name: 'Lantern Night', lang: 'he', dir: 'rtl', labels: { close: 'Sluiten' } }, CAL, e.iframes[0].contentWindow);
        var d = e.dialog();
        out.opened = d ? {
            src: d.children[0].getAttribute('src'),
            allow: d.children[0].getAttribute('allow'),
            label: d.getAttribute('aria-label'),
            dir: d.getAttribute('dir'),
            lang: d.getAttribute('lang'),
            close: d.children[1].getAttribute('aria-label'),
            modal: !!d.open,
            hash: e.win.location.hash,
            locked: e.doc.documentElement.style.overflow,
        } : null;
        out.emitted = e.emitted.map(function (x) { return [x.type, x.detail && x.detail.url]; });

        // Only the overlay's own frame, from its own origin, may close it.
        var overlayWin = d.children[0].contentWindow;
        e.fire({ type: 'eventschedule:close' }, 'https://evil.example', overlayWin);
        out.openAfterForeignClose = !!e.dialog();
        e.fire({ type: 'eventschedule:checkout', path: '/sub/lantern-night/abc', form: 'tickets' }, CAL, overlayWin);
        out.checkout = e.emitted.filter(function (x) { return x.type === 'eventschedule:checkout'; }).map(function (x) { return x.detail; });
        e.posted.length = 0;
        e.fire({ type: 'eventschedule:close' }, CAL, overlayWin);
        out.closed = {
            gone: !e.dialog(),
            back: e.history.back,
            unlocked: e.doc.documentElement.style.overflow,
            toldTheCalendar: e.posted.map(function (p) { return [p.message.type, p.origin, p.frame === e.iframes[0]]; }),
        };

        // A signup form is the signup snippet's to size.
        var s = env({ iframes: [CAL + '/sub?embed=true&form=subscribe'] });
        s.fire({ type: 'eventschedule:height', height: 400 }, CAL, s.iframes[0].contentWindow);
        out.subscribeHeight = s.iframes[0].style.height || null;
        out.subscribeGreeted = s.posted.length;

        // Another service's embed (it has embed=true in its address too) is never a calendar: it
        // said nothing, so it opens nothing and sizes nothing, from its own origin or ours.
        var FORMS = 'https://forms.office.example';
        var m = env({ iframes: [FORMS + '/Pages/ResponsePage.aspx?id=1&embed=true', CAL + '/sub?embed=true&events=onsite'] });
        m.fire({ type: 'eventschedule:open', path: '/x?embed=true&view=event' }, FORMS, m.iframes[0].contentWindow);
        m.fire({ type: 'eventschedule:height', height: 50000 }, FORMS, m.iframes[0].contentWindow);
        out.untrustedFrame = { dialog: !!m.dialog(), height: m.iframes[0].style.height || null };
        // A calendar that announces itself from another origin than its address is not trusted.
        m.posted.length = 0;
        m.fire({ type: 'eventschedule:ready', v: 1 }, FORMS, m.iframes[1].contentWindow);
        m.fire({ type: 'eventschedule:open', path: GOOD }, FORMS, m.iframes[1].contentWindow);
        out.wrongOrigin = { greeted: m.posted.length, dialog: !!m.dialog() };
        // Nor does a trusted calendar's frame count once a message comes from another origin.
        m.fire({ type: 'eventschedule:ready', v: 1 }, CAL, m.iframes[1].contentWindow);
        m.fire({ type: 'eventschedule:height', height: 700 }, FORMS, m.iframes[1].contentWindow);
        out.trustedFromElsewhere = m.iframes[1].style.height || null;

        // A link to the page with an event open opens it again, validated first, and only once a
        // calendar has said who it is.
        var h = env({ iframes: [CAL + '/sub?embed=true&events=onsite'], hash: '#es-event=' + encodeURIComponent(GOOD) });
        out.deepLinkBeforeReady = !!h.dialog();
        h.fire({ type: 'eventschedule:ready', v: 1 }, CAL, h.iframes[0].contentWindow);
        out.deepLink = h.dialog() ? h.dialog().children[0].getAttribute('src') : null;
        var bad = env({ iframes: [CAL + '/sub?embed=true&events=onsite'], hash: '#es-event=' + encodeURIComponent('javascript:alert(1)') });
        bad.fire({ type: 'eventschedule:ready', v: 1 }, CAL, bad.iframes[0].contentWindow);
        out.deepLinkJavascript = !!bad.dialog();
        var away = env({ iframes: [CAL + '/sub?embed=true&events=onsite'], hash: '#es-event=' + encodeURIComponent('//evil.example/x?embed=true&view=event') });
        away.fire({ type: 'eventschedule:ready', v: 1 }, CAL, away.iframes[0].contentWindow);
        out.deepLinkElsewhere = !!away.dialog();
        // A link to a crafted address with only the untrusted frame on the page: nothing.
        var forms = env({ iframes: [FORMS + '/Pages/ResponsePage.aspx?embed=true'], hash: '#es-event=' + encodeURIComponent('/Pages/x?embed=true&view=event') });
        out.deepLinkUntrusted = !!forms.dialog();
        // On an install that serves a schedule under a path, only an event under that path.
        var SELF = 'https://events.example.org';
        var p1 = env({ iframes: [SELF + '/harbour?embed=true&events=onsite'], hash: '#es-event=' + encodeURIComponent('/other/show/abc?embed=true&view=event') });
        p1.fire({ type: 'eventschedule:ready', v: 1 }, SELF, p1.iframes[0].contentWindow);
        out.deepLinkOtherPath = !!p1.dialog();
        var p2 = env({ iframes: [SELF + '/harbour?embed=true&events=onsite'], hash: '#es-event=' + encodeURIComponent('/harbour/show/abc?embed=true&view=event') });
        p2.fire({ type: 'eventschedule:ready', v: 1 }, SELF, p2.iframes[0].contentWindow);
        out.deepLinkOwnPath = p2.dialog() ? p2.dialog().children[0].getAttribute('src') : null;

        // Pasted into a builder's own frame: it leaves the calendars to themselves.
        var w = env({ wrapped: true, iframes: [CAL + '/sub?embed=true&events=onsite'] });
        out.wrapped = { flag: w.win.EventScheduleEmbed.wrapped, listening: !!(w.listeners.message || []).length, greeted: w.posted.length };

        process.stdout.write(JSON.stringify(out));
        JS;

        $harnessFile = tempnam(sys_get_temp_dir(), 'embedjs').'.js';

        try {
            file_put_contents($harnessFile, $harness);
            $process = new Process(['node', $harnessFile, resource_path('embed/embed.js')]);
            $process->run();

            // Not markTestSkipped, for the reason SentryJsFilterTest gives.
            $this->assertTrue(
                $process->isSuccessful(),
                "embed.js did not run to completion in Node.\nIf node is missing, install it - this test must not be skipped.\n".$process->getErrorOutput()
            );

            return json_decode($process->getOutput(), true);
        } finally {
            @unlink($harnessFile);
        }
    }

    public function test_the_script_behaves_as_its_protocol_says(): void
    {
        $out = $this->runScenarios();

        $good = 'https://sub.example.com/sub/lantern-night/abc?embed=true&view=event&events=onsite';
        $this->assertSame($good, $out['resolve']['good']);
        foreach (['javascript', 'schemeRelative', 'backslash', 'absolute', 'notTheEventView', 'badOrigin', 'nullOrigin', 'whitespace', 'notAString'] as $case) {
            $this->assertNull($out['resolve'][$case], $case);
        }

        $this->assertSame([['eventschedule:hello', 'https://sub.example.com']], $out['greetedOnLoad']);
        $this->assertSame([['eventschedule:hello', 'https://sub.example.com']], $out['helloOnReady']);

        $this->assertNull($out['afterOldResize'], 'the old type is never answered');
        $this->assertSame('1235px', $out['afterHeight']);
        $this->assertSame('1235px', $out['afterBadHeight']);

        $this->assertFalse($out['dialogFromStranger']);
        $this->assertFalse($out['dialogFromJavascript']);

        $this->assertSame([
            'src' => $good,
            'allow' => 'clipboard-write; web-share; fullscreen',
            'label' => 'Lantern Night',
            'dir' => 'rtl',
            'lang' => 'he',
            'close' => 'Sluiten',
            'modal' => true,
            'hash' => '#es-event='.rawurlencode('/sub/lantern-night/abc?embed=true&view=event&events=onsite'),
            'locked' => 'hidden',
        ], $out['opened']);
        $this->assertSame([['eventschedule:event-opened', $good]], $out['emitted']);

        $this->assertTrue($out['openAfterForeignClose']);
        $this->assertSame([['url' => 'https://sub.example.com/sub/lantern-night/abc', 'form' => 'tickets']], $out['checkout']);
        $this->assertSame(['gone' => true, 'back' => 1, 'unlocked' => '', 'toldTheCalendar' => [['eventschedule:closed', 'https://sub.example.com', true]]], $out['closed']);

        $this->assertNull($out['subscribeHeight']);
        $this->assertSame(0, $out['subscribeGreeted']);

        $this->assertSame(['dialog' => false, 'height' => null], $out['untrustedFrame'], 'an embed of another service');
        $this->assertSame(['greeted' => 0, 'dialog' => false], $out['wrongOrigin']);
        $this->assertNull($out['trustedFromElsewhere']);

        $this->assertFalse($out['deepLinkBeforeReady'], 'waits for a calendar that said who it is');
        $this->assertSame($good, $out['deepLink']);
        $this->assertFalse($out['deepLinkUntrusted']);
        $this->assertFalse($out['deepLinkOtherPath']);
        $this->assertSame('https://events.example.org/harbour/show/abc?embed=true&view=event', $out['deepLinkOwnPath']);
        $this->assertFalse($out['deepLinkJavascript']);
        $this->assertFalse($out['deepLinkElsewhere']);

        $this->assertSame(['flag' => true, 'listening' => false, 'greeted' => 0], $out['wrapped']);
    }
}
