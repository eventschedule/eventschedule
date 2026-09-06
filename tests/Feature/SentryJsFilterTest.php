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
     * Run each payload through the rendered partial's beforeSend.
     *
     * @param  array  $events  Sentry event payloads.
     * @param  string|null  $partial  Overrides the rendered partial, for A/B against an older copy.
     * @return array<int, string> 'dropped' or 'kept', positionally.
     */
    private function verdicts(array $events, ?string $partial = null): array
    {
        $partial ??= view('layouts.sentry')->render();

        $harness = <<<JS
        global.window = {};
        var captured = null;
        global.Sentry = { init: function (options) { captured = options; } };

        {$partial}

        window.sentryOnLoad();

        var payloads = JSON.parse(require('fs').readFileSync(process.argv[2], 'utf8'));
        process.stdout.write(JSON.stringify(payloads.map(function (event) {
            return captured.beforeSend(event) === null ? 'dropped' : 'kept';
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

    /** The one verdict for a single payload. */
    private function verdict(array $event, ?string $partial = null): string
    {
        return $this->verdicts([$event], $partial)[0];
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
        $this->assertSame('dropped', $this->verdict($this->ourError([
            'exception' => ['values' => [[
                'type' => 'TypeError',
                'value' => "Cannot read properties of undefined (reading 'push')",
                'stacktrace' => ['frames' => [[
                    'filename' => 'https://house-show.eventschedule.com/cdn-cgi/scripts/'
                        .'7d0fa10a/cloudflare-static/rocket-loader.min.js',
                ]]],
            ]]],
        ])));
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
}
