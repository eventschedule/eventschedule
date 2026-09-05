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
 * The filter also has two lists with genuinely different reach, and reading the file
 * cannot tell you whether an entry lands in the right one. ignoreMessages is matched
 * only against the exception value and type; ignoreAnywhere is matched against the
 * whole serialized event, which is a much wider net - it used to be the ONLY net, and
 * a real crash was discarded whenever 'Load failed' or 'Network Error' appeared in an
 * unrelated breadcrumb.
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
