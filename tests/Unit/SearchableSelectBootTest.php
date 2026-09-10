<?php

namespace Tests\Unit;

use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * Boots public/js/searchable-select.js in Node against a stubbed document.
 *
 * EVENTSCHEDULE-JS-39 (iOS Safari, on an embed): the file is deferred, so by the time it runs the
 * parser has built <body> - unless something removed it, or moved it out from under <html> where
 * document.body stops finding it. The watcher for dynamically added selects then waited for a
 * DOMContentLoaded that arrived with no body either, and handed null to MutationObserver.observe(),
 * which throws.
 *
 * The stub throws exactly what WebKit threw, and the file under test is the one we ship.
 */
class SearchableSelectBootTest extends TestCase
{
    /**
     * Load the script the way a browser would, then finish parsing and fire DOMContentLoaded.
     *
     * @param  string  $readyState  document.readyState when the script runs.
     * @param  bool  $bodyAtRun  Whether <body> exists when the script runs.
     * @param  bool  $bodyAtDomReady  Whether <body> exists when DOMContentLoaded fires.
     * @param  bool  $hasRoot  Whether the document has an <html> element at all.
     * @return array{errors: array<int, string>, observed: array<int, string>, observedBeforeParserEnd: array<int, string>}
     */
    private function boot(string $readyState, bool $bodyAtRun, bool $bodyAtDomReady, bool $hasRoot = true): array
    {
        $harness = <<<'JS'
        var fs = require('fs');
        var vm = require('vm');
        var scenario = JSON.parse(process.argv[3]);

        function Node(name) { this.name = name; }
        var root = scenario.hasRoot ? new Node('html') : null;
        var body = new Node('body');
        var domReadyListeners = [];
        var observed = [];
        var errors = [];

        global.window = global;
        global.MutationObserver = function () {};
        global.MutationObserver.prototype.observe = function (target) {
            // Verbatim what WebKit threw in EVENTSCHEDULE-JS-39.
            if (!(target instanceof Node)) {
                throw new TypeError("Argument 1 ('target') to MutationObserver.observe must be an instance of Node");
            }
            observed.push(target.name);
        };
        global.document = {
            readyState: scenario.readyState,
            documentElement: root,
            body: scenario.bodyAtRun ? body : null,
            querySelectorAll: function () { return []; },
            addEventListener: function (type, listener) {
                if (type === 'DOMContentLoaded') domReadyListeners.push(listener);
            },
        };

        // A throw while the file loads counts as much as one from a listener.
        try {
            vm.runInThisContext(fs.readFileSync(process.argv[2], 'utf8'));
        } catch (e) {
            errors.push(e.message);
        }
        var observedBeforeParserEnd = observed.slice();

        // The parser finishes and DOMContentLoaded fires.
        document.readyState = 'interactive';
        document.body = scenario.bodyAtDomReady ? body : null;
        domReadyListeners.forEach(function (listener) {
            try {
                listener();
            } catch (e) {
                errors.push(e.message);
            }
        });

        process.stdout.write(JSON.stringify({
            errors: errors,
            observed: observed,
            observedBeforeParserEnd: observedBeforeParserEnd,
        }));
        JS;

        $tempFile = tempnam(sys_get_temp_dir(), 'searchableselect');
        $harnessFile = $tempFile.'.js';

        try {
            file_put_contents($harnessFile, $harness);

            $process = new Process([
                'node',
                $harnessFile,
                base_path('public/js/searchable-select.js'),
                json_encode(compact('readyState', 'bodyAtRun', 'bodyAtDomReady', 'hasRoot')),
            ]);
            $process->run();

            // Deliberately not markTestSkipped, for the reason SentryJsFilterTest gives: a skip
            // would let this file pin nothing on CI and nobody would find out.
            $this->assertTrue(
                $process->isSuccessful(),
                "searchable-select.js did not boot to completion in Node.\n"
                    .'If node is missing, install it - this test must not be skipped.'."\n"
                    .$process->getErrorOutput()
            );

            return json_decode($process->getOutput(), true);
        } finally {
            @unlink($harnessFile);
            @unlink($tempFile);
        }
    }

    /** EVENTSCHEDULE-JS-39: a deferred run that finds no body, then a DOMContentLoaded that brings none. */
    public function test_a_deferred_run_with_no_body_does_not_throw(): void
    {
        $result = $this->boot('interactive', bodyAtRun: false, bodyAtDomReady: false);

        $this->assertSame([], $result['errors']);
        // Still watched, via the root: a select added under a body that was moved out from under
        // <html> is still enhanced.
        $this->assertSame(['html'], $result['observed']);
    }

    /** The ordinary deferred run: the body is there, and it is what gets watched, once. */
    public function test_a_deferred_run_watches_the_body(): void
    {
        $result = $this->boot('interactive', bodyAtRun: true, bodyAtDomReady: true);

        $this->assertSame([], $result['errors']);
        $this->assertSame(['body'], $result['observed']);
    }

    /**
     * Loaded without defer, in <head>, the watch has to wait for the parser. Watching the root
     * straight away could enhance a select as soon as the parser inserts it, before its options
     * have been parsed.
     */
    public function test_an_early_run_waits_for_the_parser(): void
    {
        $result = $this->boot('loading', bodyAtRun: false, bodyAtDomReady: true);

        $this->assertSame([], $result['errors']);
        $this->assertSame([], $result['observedBeforeParserEnd']);
        $this->assertSame(['body'], $result['observed']);
    }

    /** A document with no root at all (emptied by document.open()) has nothing to watch. */
    public function test_a_document_with_no_root_does_not_throw(): void
    {
        $result = $this->boot('interactive', bodyAtRun: false, bodyAtDomReady: false, hasRoot: false);

        $this->assertSame([], $result['errors']);
        $this->assertSame([], $result['observed']);
    }
}
