<?php

namespace Tests\Feature;

use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * Executes the browser-side Sentry filter in layouts/sentry.blade.php for real.
 *
 * beforeSend has to RETURN the event for it to be sent, so a TypeError on an event
 * shape nobody guarded does not fail loudly - it silently loses that event, and if
 * the mistake sits on a path every event takes it silently takes all browser error
 * reporting with it. Nothing else in the suite would notice.
 *
 * The filter also has three lists with genuinely different reach, and reading the file
 * cannot tell you whether an entry lands in the right one. ignoreMessages is matched
 * only against the exception value and type; ignoreFrameFunctions only against frame
 * function names; ignoreAnywhere against the whole serialized event, which is a much
 * wider net - it used to be the ONLY net, and a real crash was discarded whenever
 * 'Load failed' or 'Network Error' appeared in an unrelated breadcrumb.
 *
 * So this stubs the SDK, evals the partial as shipped, and runs payloads through the
 * captured beforeSend in Node. It tests the JavaScript, not a PHP restatement of it.
 */
class SentryJsFilterTest extends TestCase
{
    /**
     * The frame EVENTSCHEDULE-JS-3A arrived with, verbatim.
     *
     * PayPal Honey is a macOS Safari APP extension, a native .appex bundle, so WebKit reports its
     * injected script by absolute filesystem path with no URL scheme. Kept whole, percent-encoded
     * space and all, because the encoding is exactly what stops a vendor-name regex working.
     */
    private const HONEY_FRAME = '/Applications/PayPal%20Honey.app/Contents/PlugIns/Extension.appex'
        .'/Contents/Resources/Honey.safariextension/h0.js';

    /** A frame in our own Vite output, for the stacks that mix ours with a third party's. */
    private const OUR_FRAME = 'https://app.eventschedule.com/build/assets/app-abc123.js';

    /**
     * Run each payload through one stage of the rendered partial, in Node.
     *
     * $verdictBody defines `function verdict(event)` and has `captured` in scope - the options
     * object the partial handed to Sentry.init, so a stage can read beforeSend or denyUrls.
     *
     * @param  string  $verdictBody  JS defining function verdict(event): string.
     * @param  array  $events  Sentry event payloads.
     * @param  string|null  $partial  Overrides the rendered partial, for A/B against an older copy.
     * @return array<int, string> One verdict per payload, positionally.
     */
    private function runInNode(string $verdictBody, array $events, ?string $partial = null): array
    {
        $partial ??= view('layouts.sentry')->render();

        $harness = <<<JS
        global.window = {};
        var captured = null;
        global.Sentry = { init: function (options) { captured = options; } };

        {$partial}

        window.sentryOnLoad();

        {$verdictBody}

        var payloads = JSON.parse(require('fs').readFileSync(process.argv[2], 'utf8'));
        process.stdout.write(JSON.stringify(payloads.map(function (event) {
            return verdict(event);
        })));
        JS;

        $harnessFile = tempnam(sys_get_temp_dir(), 'sentryjs').'.js';
        $payloadFile = tempnam(sys_get_temp_dir(), 'sentryjs').'.json';

        try {
            file_put_contents($harnessFile, $harness);
            file_put_contents($payloadFile, json_encode($events, JSON_UNESCAPED_SLASHES));

            $process = new Process(['node', $harnessFile, $payloadFile]);
            $process->run();

            // Deliberately not markTestSkipped: a skip here would let this file pin nothing
            // on CI and nobody would find out. The feature-tests job sets up PHP only, but
            // security-audit already runs `npm ci` with no actions/setup-node, so the repo
            // already leans on the ubuntu-latest runner's preinstalled Node.
            $this->assertTrue(
                $process->isSuccessful(),
                "The Sentry beforeSend filter did not run to completion in Node.\n"
                    .'If node is missing, install it - this test must not be skipped.'."\n"
                    .$process->getErrorOutput()
            );

            return json_decode($process->getOutput(), true);
        } finally {
            @unlink($harnessFile);
            @unlink($payloadFile);
        }
    }

    /**
     * Run each payload through the rendered partial's beforeSend.
     *
     * @param  array  $events  Sentry event payloads.
     * @param  string|null  $partial  Overrides the rendered partial, for A/B against an older copy.
     * @return array<int, string> 'dropped' or 'kept', positionally.
     */
    private function verdicts(array $events, ?string $partial = null): array
    {
        return $this->runInNode(
            'function verdict(event) { return captured.beforeSend(event) === null ? "dropped" : "kept"; }',
            $events,
            $partial
        );
    }

    /** The one verdict for a single payload. */
    private function verdict(array $event, ?string $partial = null): string
    {
        return $this->verdicts([$event], $partial)[0];
    }

    /**
     * Run each payload through the partial's denyUrls, the stage that runs BEFORE beforeSend.
     *
     * Unlike verdicts() this restates SDK internals, because denyUrls is data and the code that
     * reads it is Sentry's: @sentry/browser 8.55.2, InboundFilters._getEventFilterUrl ->
     * _getLastValidUrl -> stringMatchesSomePattern, transcribed below. Our regex list is still
     * the thing under test; what is restated is only which single string it gets matched against.
     *
     * It is restated rather than imported because @sentry/browser is not an npm dependency here -
     * the loader in config('app.sentry_js_dsn') fetches the bundle at runtime and its version is
     * chosen in Sentry's UI, so a test that fetched it would be a network call whose subject can
     * change without a commit. Keep the version named here and in the partial's comment in step.
     *
     * @param  array  $events  Sentry event payloads.
     * @param  string|null  $partial  Overrides the rendered partial, for A/B against an older copy.
     * @return array<int, string> 'denied' or 'kept', positionally.
     */
    private function denyVerdicts(array $events, ?string $partial = null): array
    {
        $stage = <<<'JS'
        function verdict(event) {
            // Frames are ordered oldest first, so this walks BACKWARDS to the innermost one -
            // the frame that actually threw. It skips only '<anonymous>' and '[native code]',
            // reads filename and never abs_path, and stops at the first frame it accepts: an
            // innermost frame with no filename yields no URL rather than falling through to the
            // frame below it. A frameless event yields no URL either, which is why nothing in
            // denyUrls could ever have caught EVENTSCHEDULE-JS-35.
            var frames;
            try { frames = event.exception.values[0].stacktrace.frames; } catch (e) {}

            var url = null;
            for (var i = (frames || []).length - 1; i >= 0; i--) {
                var frame = frames[i];
                if (frame && frame.filename !== '<anonymous>' && frame.filename !== '[native code]') {
                    url = frame.filename || null;
                    break;
                }
            }

            if (! url) {
                return 'kept';
            }

            return (captured.denyUrls || []).some(function (pattern) {
                return pattern instanceof RegExp ? pattern.test(url) : url.indexOf(pattern) !== -1;
            }) ? 'denied' : 'kept';
        }
        JS;

        return $this->runInNode($stage, $events, $partial);
    }

    /** The one deny verdict for a single payload. */
    private function denyVerdict(array $event, ?string $partial = null): string
    {
        return $this->denyVerdicts([$event], $partial)[0];
    }

    /** An error of ours, with a frame in our own bundle. */
    private function ourError(array $overrides = []): array
    {
        return array_merge([
            'exception' => ['values' => [[
                'type' => 'TypeError',
                'value' => "Cannot read properties of null (reading 'dataset')",
                'stacktrace' => ['frames' => [[
                    'filename' => 'https://house-show.eventschedule.com/build/assets/app-abc123.js',
                ]]],
            ]]],
            'request' => ['url' => 'https://house-show.eventschedule.com/a-very-star-shaped-back-to-school-bash'],
        ], $overrides);
    }

    /**
     * EVENTSCHEDULE-JS-3A's shape: PayPal Honey's Safari App Extension, Safari 27 on macOS.
     *
     * @param  array<int, array<string, mixed>>|null  $frames  Oldest first, as Sentry orders them.
     *                                                         Defaults to the one reported frame.
     */
    private function honeyError(?array $frames = null): array
    {
        return [
            'exception' => ['values' => [[
                'type' => 'UnavailableError',
                'value' => 'UnavailableError',
                'mechanism' => ['type' => 'onunhandledrejection', 'handled' => false],
                'stacktrace' => ['frames' => $frames ?? [[
                    'function' => 'v',
                    'filename' => self::HONEY_FRAME,
                    'lineno' => 135,
                    'colno' => 859082,
                ]]],
            ]]],
            'contexts' => ['browser' => ['name' => 'Safari', 'version' => '27.0']],
            'request' => ['url' => 'https://app.eventschedule.com/loom/schedule?year=2026&month=9'],
        ];
    }

    /**
     * An event whose stack is exactly these filenames, oldest first.
     *
     * For the deny stage, which reads nothing but the innermost frame's filename, so the type and
     * message are deliberately ordinary - anything that denies one of these denied it on the URL.
     *
     * @param  array<int, string>  $filenames
     */
    private function frameEvent(array $filenames): array
    {
        return [
            'exception' => ['values' => [[
                'type' => 'TypeError',
                'value' => "Cannot read properties of null (reading 'dataset')",
                'stacktrace' => ['frames' => array_map(fn ($filename) => [
                    'filename' => $filename,
                ], $filenames)],
            ]]],
        ];
    }

    /**
     * EVENTSCHEDULE-JS-36's shape, with the frame function names left to the caller.
     *
     * One filename (our own page URL), one line, rising column offsets: what an injected
     * script looks like once WebKit has attributed it to the document. Sentry orders frames
     * oldest first, so the caller passes them that way too.
     *
     * @param  array<int, string>  $functions
     */
    private function injectedError(array $functions): array
    {
        $url = 'https://house-show.eventschedule.com/a-very-star-shaped-back-to-school-bash/9V8YDn';

        return [
            'exception' => ['values' => [[
                'type' => 'InvalidAccessError',
                'value' => 'The object does not support the operation or argument.',
                'mechanism' => ['type' => 'onunhandledrejection', 'handled' => false],
                'stacktrace' => ['frames' => array_map(fn ($function) => [
                    'filename' => $url,
                    'function' => $function,
                    'lineno' => 1,
                ], $functions)],
            ]]],
            'contexts' => ['browser' => ['name' => 'Instagram', 'version' => '445.0.0']],
            'request' => ['url' => $url],
        ];
    }

    /**
     * The report that prompted the filter: EVENTSCHEDULE-JS-35, Mobile Safari on iOS.
     *
     * browser.storage.local.set() is the WebExtension API, so a document cannot call it -
     * this is a visitor's extension, injected into the page. It arrives with no JS frame,
     * which is why denyUrls' safari-web-extension:// entry cannot catch it.
     */
    public function test_a_browser_extension_rejection_with_no_frames_is_dropped(): void
    {
        $this->assertSame('dropped', $this->verdict([
            'exception' => ['values' => [[
                'type' => 'Error',
                'value' => 'Invalid call to browser.storage.local.set(). Failed to insert value '
                    .'{"availWidth":440,"availHeight":956,"width":440,"height":956} for key screen.',
                'mechanism' => ['type' => 'onunhandledrejection', 'handled' => false],
            ]]],
            'request' => ['url' => 'https://house-show.eventschedule.com/a-very-star-shaped-back-to-school-bash'],
        ]));
    }

    /** The rest of the extension-API family, none of which a document can produce either. */
    public function test_the_extension_api_family_is_dropped(): void
    {
        $messages = [
            'Extension context invalidated.',
            'Could not establish connection. Receiving end does not exist.',
            'The message port closed before a response was received.',
            'chrome.runtime.sendMessage() called from a webpage must specify an Extension ID',
        ];

        $events = array_map(fn ($message) => [
            'exception' => ['values' => [['type' => 'Error', 'value' => $message]]],
        ], $messages);

        $this->assertSame(
            array_fill(0, count($messages), 'dropped'),
            $this->verdicts($events)
        );
    }

    /**
     * The regression the two-list split exists to fix.
     *
     * A console breadcrumb mentioning 'Load failed' says nothing about the crash that
     * followed it, but the old whole-event match discarded the whole report anyway.
     */
    public function test_a_real_error_survives_an_ignored_term_in_a_breadcrumb(): void
    {
        $event = $this->ourError([
            'breadcrumbs' => [[
                'category' => 'console',
                'level' => 'warning',
                'message' => 'Load failed',
            ]],
        ]);

        $this->assertSame('kept', $this->verdict($event));

        // And prove that is a change: the same payload against the pre-split filter.
        $before = file_get_contents(base_path('tests/fixtures/sentry-filter-before-split.js'));
        $this->assertSame('dropped', $this->verdict($event, $before));
    }

    /** The control: nothing incidental, so nothing to argue about. */
    public function test_an_ordinary_error_of_ours_is_reported(): void
    {
        $this->assertSame('kept', $this->verdict($this->ourError()));
    }

    /** ignoreAnywhere still has to reach a frame path, which is the whole reason it stayed. */
    public function test_a_third_party_frame_path_is_still_matched_anywhere(): void
    {
        $event = $this->ourError([
            'exception' => ['values' => [[
                'type' => 'TypeError',
                'value' => "Cannot read properties of undefined (reading 'push')",
                'stacktrace' => ['frames' => [[
                    'filename' => 'https://house-show.eventschedule.com/cdn-cgi/scripts/'
                        .'7d0fa10a/cloudflare-static/rocket-loader.min.js',
                ]]],
            ]]],
        ]);

        $this->assertSame('dropped', $this->verdict($event));

        // With the caveat that in production beforeSend never sees this one: denyUrls runs first
        // and /cdn-cgi/ already denies it. ignoreAnywhere earns 'cloudflare-static' on the shapes
        // the deny stage cannot reach - a breadcrumb, a request URL, a frame that is not the
        // innermost one - not on this payload.
        $this->assertSame('denied', $this->denyVerdict($event));
    }

    /**
     * EVENTSCHEDULE-JS-36: Meta's in-app browser (Instagram, iOS), injected into our own page.
     *
     * denyUrls' iabjs:// entry was already live when this arrived and could not catch it - on
     * iOS the script is injected with evaluateJavaScript rather than loaded from a URL of its
     * own, so every frame carries OUR page URL. ignoreMessages cannot take it either, because a
     * bare InvalidAccessError is something our own code can raise. Only the function name is
     * left.
     *
     * The second payload is the A/B and is the whole point of the test: identical but for the
     * frame function names, so keeping it proves the drop comes from ignoreFrameFunctions and
     * not from the exception type, the message, the page URL or the rejection mechanism.
     */
    public function test_a_meta_in_app_browser_injected_error_is_dropped(): void
    {
        $stacks = [
            // EVENTSCHEDULE-JS-36 exactly as reported.
            ['mutationObserverCallback', 'logLoginFieldDetected', 'sendPostMessage', 'dispatchToBridge'],
            // Its outer frames alone: a path that throws before it reaches the login-field
            // logger or the bridge, which whole-name matching on those two would have missed.
            ['mutationObserverCallback', 'None'],
            // The same entry points under other names, which is what the stems are for.
            ['onDomChange', 'logLoginFieldFocused'],
            ['scanForms', 'postToBridge'],
            // The A/B, and the point of the test: same exception type, same message, same page
            // URL, same rejection mechanism, ordinary function names. Anything that drops this
            // would drop our own errors too.
            ['onMutation', 'detectFields', 'postToParent', 'sendMessage'],
        ];

        $this->assertSame(
            ['dropped', 'dropped', 'dropped', 'dropped', 'kept'],
            $this->verdicts(array_map(fn ($stack) => $this->injectedError($stack), $stacks))
        );
    }

    /**
     * The exemption that makes matching on stems safe.
     *
     * A frame from our own bundle is ours whatever it happens to be called. Without this, the day
     * someone writes a sendPostMessage() of our own its crashes stop arriving and nothing says
     * so - a filter that has gone quiet looks exactly like a filter that is working.
     */
    public function test_a_colliding_function_name_in_our_own_bundle_is_reported(): void
    {
        $this->assertSame('kept', $this->verdict($this->ourError([
            'exception' => ['values' => [[
                'type' => 'TypeError',
                'value' => "Cannot read properties of null (reading 'postMessage')",
                'stacktrace' => ['frames' => [[
                    'filename' => 'https://house-show.eventschedule.com/build/assets/app-abc123.js',
                    'function' => 'sendPostMessage',
                ]]],
            ]]],
        ])));
    }

    /**
     * Webviews that probe for their host app's bridge and never define it.
     *
     * These arrive as a ReferenceError message with no useful frame, so ignoreMessages is the
     * list that can see them. A document cannot reach any of these globals, exactly as with the
     * extension-API family above.
     */
    public function test_the_webview_injected_global_family_is_dropped(): void
    {
        $messages = [
            "Can't find variable: __gCrWeb",
            "Can't find variable: _AutofillCallbackHandler",
            "Can't find variable: instantSearchSDKJSBridgeClearHighlight",
            "Can't find variable: msDiscoverChatAvailable",
        ];

        $events = array_map(fn ($message) => [
            'exception' => ['values' => [['type' => 'ReferenceError', 'value' => $message]]],
        ], $messages);

        $this->assertSame(
            array_fill(0, count($messages), 'dropped'),
            $this->verdicts($events)
        );
    }

    /**
     * The over-filtering guard: we key on the injector, never on the browser or on a DOM
     * exception type our own code can raise. The same error from our own bundle, in the same
     * in-app browser, is a real bug and has to survive.
     */
    public function test_an_ordinary_error_of_ours_in_the_instagram_browser_is_reported(): void
    {
        $this->assertSame('kept', $this->verdict($this->ourError([
            'exception' => ['values' => [[
                'type' => 'InvalidAccessError',
                'value' => 'The object does not support the operation or argument.',
                'stacktrace' => ['frames' => [[
                    'filename' => 'https://house-show.eventschedule.com/build/assets/app-abc123.js',
                    'function' => 'openSeatMap',
                ]]],
            ]]],
            'contexts' => ['browser' => ['name' => 'Instagram', 'version' => '445.0.0']],
        ])));
    }

    /**
     * An exception value with no stacktrace and no ignored message, so it reaches the frame loop.
     *
     * Nothing else here exercises that shape - the extension family returns on the message long
     * before the frames are read - so an unguarded .stacktrace.frames would ship green.
     */
    public function test_an_exception_with_no_stacktrace_does_not_throw(): void
    {
        $this->assertSame('kept', $this->verdict([
            'exception' => ['values' => [['type' => 'TypeError', 'value' => 'r.focus is not a function']]],
        ]));
    }

    /** ChunkLoadError names the exception TYPE, so the value alone would miss it. */
    public function test_an_ignored_exception_type_is_matched(): void
    {
        $this->assertSame('dropped', $this->verdict([
            'exception' => ['values' => [['type' => 'ChunkLoadError', 'value' => 'timeout']]],
        ]));
    }

    /** The pre-existing shape check, which is why "value":"undefined" stayed in ignoreAnywhere. */
    public function test_an_undefined_exception_value_is_dropped(): void
    {
        $this->assertSame('dropped', $this->verdict([
            'exception' => ['values' => [['type' => 'Error', 'value' => 'undefined']]],
        ]));
    }

    /**
     * captureMessage events have no exception key at all.
     *
     * Reaching into event.exception.values without a guard throws here, and a throwing
     * beforeSend never returns an event, so every report would vanish.
     */
    public function test_an_event_with_no_exception_does_not_throw(): void
    {
        $this->assertSame('kept', $this->verdict([
            'message' => 'Checkout started',
            'level' => 'info',
        ]));
    }

    /**
     * EVENTSCHEDULE-JS-3A: PayPal Honey's macOS Safari App Extension.
     *
     * The second assertion is the point of the test. The frozen pre-split copy carries the same
     * eight denyUrls entries this had before Honey, safari-(web-)?extension:// among them, so a
     * 'kept' there and a 'denied' here proves the drop comes from the two .appex entries and not
     * from the scheme regex having covered a filesystem path all along.
     */
    public function test_a_macos_safari_app_extension_is_denied(): void
    {
        $this->assertSame('denied', $this->denyVerdict($this->honeyError()));

        $before = file_get_contents(base_path('tests/fixtures/sentry-filter-before-split.js'));
        $this->assertSame('kept', $this->denyVerdict($this->honeyError(), $before));
    }

    /**
     * And beforeSend must KEEP it, so the deny stage is provably the only thing catching it.
     *
     * This is also what locks 'UnavailableError' out of ignoreMessages. Every term in that list
     * passes 'a document cannot produce this string' - browser.storage, __gCrWeb,
     * webkit.messageHandlers. UnavailableError is a bare name with no API surface behind it, and
     * ignoreMessages matches the exception TYPE, so adding it would drop every UnavailableError
     * from anywhere, ours included. This test fails the day someone tries.
     */
    public function test_the_honey_payload_is_not_caught_by_before_send(): void
    {
        $this->assertSame('kept', $this->verdict($this->honeyError()));
    }

    /**
     * denyUrls reads the INNERMOST frame, which is the whole reason it is safe to deny on a path.
     *
     * A third party merely triggering our bug leaves our own frame innermost, and that report is
     * still ours to fix. A harness that walked frames forwards, or matched any frame in the
     * stack, passes the test above and fails the middle case here.
     */
    public function test_only_the_innermost_frame_decides_the_deny(): void
    {
        $stacks = [
            // The extension threw: ours called into it, it blew up.
            [self::OUR_FRAME, self::HONEY_FRAME],
            // We threw: the extension called into us. Not theirs to answer for.
            [self::HONEY_FRAME, self::OUR_FRAME],
            // '[native code]' is skipped rather than treated as the frame that threw.
            [self::OUR_FRAME, self::HONEY_FRAME, '[native code]'],
            // As is '<anonymous>'.
            [self::OUR_FRAME, self::HONEY_FRAME, '<anonymous>'],
        ];

        $this->assertSame(
            ['denied', 'kept', 'denied', 'denied'],
            $this->denyVerdicts(array_map(fn ($stack) => $this->frameEvent($stack), $stacks))
        );
    }

    /**
     * Every denyUrls entry, pinned. Until this test the whole list was asserted by nothing.
     *
     * The kept rows matter as much as the denied ones: /beacon\.min\.js/ and /\.appex\//
     * are unanchored substrings, so a near miss is the way one of them starts eating our own
     * reports quietly.
     */
    public function test_every_deny_url_entry_matches_what_it_claims_to(): void
    {
        $denied = [
            'https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015',
            'https://house-show.eventschedule.com/cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js',
            'https://static.cloudflareinsights.com/rum.js',
            'chrome-extension://gighmmpiobklfepjocnamgkkbiglidom/content.js',
            'moz-extension://a1b2c3d4-e5f6-4789-abcd-ef0123456789/content.js',
            'safari-web-extension://A1B2C3D4-E5F6-4789-ABCD-EF0123456789/content.js',
            // The optional (web-)? group, which nothing else here exercises.
            'safari-extension://com.example.helper-ABCDE12345/injected.js',
            'chrome://global/content/elements/browser-custom-element.js',
            'iabjs://bridge/inject.js',
            // EVENTSCHEDULE-JS-3A, then the two shapes that make each .appex entry load-bearing
            // on its own: an app extension with no .safariextension/ folder inside it, and the
            // legacy ~/Library layout with no .appex above it. Drop either entry and one of
            // these rows starts being reported.
            self::HONEY_FRAME,
            '/Applications/Some%20Helper.app/Contents/PlugIns/Extension.appex/Contents/Resources/injected.js',
            '/Users/someone/Library/Safari/Extensions/Legacy.safariextension/inject.js',
        ];

        $kept = [
            self::OUR_FRAME,
            'https://app.eventschedule.com/loom/schedule?year=2026&month=9',
            // Near misses on the unanchored entries above.
            'https://app.eventschedule.com/js/vendor/beacon-loader.js',
            'https://app.eventschedule.com/build/assets/appex-calendar-abc123.js',
        ];

        $this->assertSame(
            array_merge(
                array_fill(0, count($denied), 'denied'),
                array_fill(0, count($kept), 'kept')
            ),
            $this->denyVerdicts(array_map(
                fn ($filename) => $this->frameEvent([$filename]),
                array_merge($denied, $kept)
            ))
        );
    }

    /**
     * Why the two historical misses were misses, so the reach stays documented by assertion.
     *
     * Both of these are dropped in production - by ignoreMessages and ignoreFrameFunctions
     * respectively - and the point here is only that the deny stage could never have done it.
     */
    public function test_the_deny_stage_cannot_reach_an_event_with_no_usable_frame(): void
    {
        // EVENTSCHEDULE-JS-35: a browser extension rejection with no stacktrace at all.
        $frameless = [
            'exception' => ['values' => [[
                'type' => 'Error',
                'value' => 'Invalid call to browser.storage.local.set().',
                'mechanism' => ['type' => 'onunhandledrejection', 'handled' => false],
            ]]],
        ];

        // And an innermost frame carrying a function but no filename: the walk stops there and
        // returns nothing rather than falling through to the Honey frame below it.
        $filenameless = $this->honeyError([
            ['function' => 'v', 'filename' => self::HONEY_FRAME],
            ['function' => 'promiseReactionJob'],
        ]);

        $this->assertSame(['kept', 'kept'], $this->denyVerdicts([$frameless, $filenameless]));
    }
}
