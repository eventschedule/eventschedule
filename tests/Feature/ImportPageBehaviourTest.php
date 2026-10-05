<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Process\Process;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What the import page does with a list, by running the page's own script.
 *
 * The page is rendered as an editor gets it, the script that mounts #event-import-app is lifted
 * out, and it is run in Node with a stand-in for Vue: the page's data, its computed properties as
 * getters and its methods, on one object. `fetch` answers a preview and records every save. No
 * DOM and no rendering, which none of this needs: ImportPageRenderTest holds the markup.
 *
 * Every scenario was a defect found by reading the page after it shipped to `main` unpushed:
 * a date sent twice, a date saved at another date's venue or with another date's end time, a
 * list left at "0 of 0 selected", a row that looked ticked and was half sent. The lines that
 * decide these are spread over a dozen methods, and a test that only looks for the lines passed
 * with most of them reversed.
 *
 * One Node run answers every test here. It is not skipped when node is missing, for the reason
 * SentryJsFilterTest gives: a skip would let this file pin nothing on CI.
 */
class ImportPageBehaviourTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private static ?array $results = null;

    private function results(): array
    {
        if (self::$results !== null) {
            return self::$results;
        }

        config(['services.google.gemini_key' => 'test-key', 'services.openai.api_key' => null]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $html = $this->actingAs($owner)
            ->get(route('event.show_import_ai', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        $harness = <<<'JS'
        const fs = require('fs');
        const vm = require('vm');
        const realTimeout = setTimeout;
        const realClear = clearTimeout;

        const html = fs.readFileSync(process.argv[2], 'utf8');
        const code = [...html.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/g)].map(m => m[1]).find(s => s.includes("mount('#event-import-app')"));
        if (! code) { throw new Error('the script that mounts #event-import-app was not found'); }

        // One page per scenario: its own data, its own record of what was sent.
        function page() {
            const box = { options: null, saves: [], nav: [], focused: [], failNames: [], limitNames: [], preview: null, refuse: null, waiting: [], manual: false, timers: 0, inflight: 0, editors: 0 };
            const live = new Set();
            const element = (id) => ({ focus() { box.focused.push(id); }, scrollIntoView() {}, value: '', style: {}, dataset: {}, classList: { add() {}, remove() {}, toggle() {}, contains: () => false },
                addEventListener() {}, removeEventListener() {}, setAttribute() {}, removeAttribute() {}, querySelector: () => null, querySelectorAll: () => [], getBoundingClientRect: () => ({ top: 0, left: 0, width: 0, height: 0 }), click() {}, closest: () => null });
            const sandbox = {
                console: { log() {}, error() {}, warn() {} }, JSON, Math, Date, Promise, Object, Array, String, Number, Boolean, RegExp, Error, Intl, parseInt, parseFloat, isNaN,
                URL, URLSearchParams, FormData, AbortController, Response, Headers,
                // Every wait on the page is cut to nothing, and counted, so a scenario can tell when the page has gone quiet.
                setTimeout: (fn) => { box.timers++; const id = realTimeout(() => { live.delete(id); box.timers--; fn(); }, 0); live.add(id); return id; },
                clearTimeout: (id) => { if (live.has(id)) { live.delete(id); box.timers--; realClear(id); } },
                setInterval: () => 0, clearInterval() {},
                confirm: () => true, alert() {}, Toastify: (options) => ({ showToast() { box.toasts = (box.toasts || []).concat(options.text); } }), flatpickr: () => ({}),
                localStorage: { getItem: () => null, setItem() {}, removeItem() {} }, navigator: { userAgent: 'node', clipboard: {} },
                document: { addEventListener() {}, removeEventListener() {}, getElementById: element, querySelector: () => null, querySelectorAll: () => [], createElement: element, body: element('body'), documentElement: element('html'), activeElement: null, hidden: false },
                Vue: { createApp(options) { box.options = options; const app = { mount: () => ({}), component: () => app, use: () => app, directive: () => app, config: { globalProperties: {}, compilerOptions: {} } }; return app; } },
                // A description editor, as the page's own gives one: each says which it is.
                initTinyMDE: () => { const text = 'editor ' + (++box.editors); return { value: () => text, toTextArea() {}, _stopEditorObserver() {} }; },
                fetch: async (url, options = {}) => {
                    box.inflight++;
                    try {
                        let body = null;
                        try { body = JSON.parse(options.body); } catch (e) {}
                        if (body && 'starts_at' in body) {
                            box.saves.push(body);
                            if (box.manual) { await new Promise(resolve => box.waiting.push(resolve)); }
                            if (box.limitNames.includes(body.name)) {
                                return new Response(JSON.stringify({ error: 'Daily limit reached.', code: 'event_create_limit' }), { status: 422, headers: { 'Content-Type': 'application/json' } });
                            }
                            if (box.failNames.includes(body.name)) {
                                return new Response(JSON.stringify({ error: 'That one could not be saved.' }), { status: 422, headers: { 'Content-Type': 'application/json' } });
                            }
                            return new Response(JSON.stringify({ success: true, event: { id: 'e' + box.saves.length, view_url: '#', edit_url: '#' } }), { status: 200, headers: { 'Content-Type': 'application/json' } });
                        }
                        // Anything that is not a POST is the curator's "Select".
                        if ((options.method || 'GET') === 'GET') {
                            return new Response(JSON.stringify({ success: true, event_url: '#' }), { status: 200, headers: { 'Content-Type': 'application/json' } });
                        }
                        if (box.refuse) {
                            return new Response(JSON.stringify(box.refuse), { status: 422, headers: { 'Content-Type': 'application/json' } });
                        }
                        return new Response(JSON.stringify(box.preview), { status: 200, headers: { 'Content-Type': 'application/json' } });
                    } finally {
                        box.inflight--;
                    }
                },
            };
            sandbox.window = sandbox; sandbox.globalThis = sandbox; sandbox.addEventListener = () => {}; sandbox.removeEventListener = () => {};
            sandbox.location = { get href() { return 'https://app.test/s/import/ai'; }, set href(value) { box.nav.push(value); }, search: '', pathname: '/s/import/ai', origin: 'https://app.test' };
            sandbox.history = { back() { box.nav.push('history.back'); }, replaceState() {} };
            vm.runInContext(code, vm.createContext(sandbox));

            const computed = box.options.computed || {};
            const methods = box.options.methods || {};
            const state = {};
            const app = new Proxy(state, {
                get(target, key) {
                    if (typeof key === 'symbol' || key in target) { return target[key]; }
                    if (key in computed) { const c = computed[key]; return (typeof c === 'function' ? c : c.get).call(app); }
                    if (key in methods) { return methods[key].bind(app); }
                    if (key === '$nextTick') { return fn => Promise.resolve().then(() => fn && fn.call(app)); }
                    if (key === '$refs') { return new Proxy({}, { get: () => ({ focus() {}, indeterminate: false }) }); }
                    return undefined;
                },
                set(target, key, value) { target[key] = value; return true; },
            });
            Object.assign(state, box.options.data.call(app));

            // Quiet: no wait pending, and every request either answered or held at the gate.
            box.quiet = async () => {
                for (let calm = 0; calm < 3;) {
                    await new Promise(resolve => realTimeout(resolve, 1));
                    calm = box.timers === 0 && box.inflight === box.waiting.length ? calm + 1 : 0;
                }
            };
            box.release = (count = Infinity) => { while (box.waiting.length && count-- > 0) { box.waiting.shift()(); } };
            box.read = async (rows, meta = {}, wholePage = false) => {
                box.preview = { parsed: JSON.parse(JSON.stringify(rows)), meta: Object.assign({ source: 'ics', host: 'example.org', found: rows.length, shown: rows.length, already_on_schedule: 0,
                    skipped: { past: 0, cancelled: 0, private: 0, unreadable: 0 }, text_truncated: false, calendar_truncated: false, can_read_whole_page: false, timezone: 'America/New_York', import_token: null }, meta) };
                app.eventDetails = 'https://example.org/feed.ics';
                await app.fetchPreview(wholePage);
                await box.quiet();
                if (! app.preview || ! app.preview.parsed) { throw new Error('no preview: ' + app.errorMessage); }
            };

            return [box, app];
        }

        const row = (name, at, extra = {}) => Object.assign({ event_name: name, event_date_time: at, event_duration: 2, venue_name: '', event_address: '', registration_url: '', is_all_day: false,
            local_time_zone: null, sort_at: at, recurrence: null, series: null, performers: [] }, extra);
        const series = (position, count) => ({ id: 'abc123', position, count, more: false });
        const sent = body => body.name + ' ' + body.starts_at.slice(5, 16);
        const footer = app => app.isAddingAll ? 'saving' : (app.addSummary ? app.summaryLabel : app.selectedLabel);
        const twice = saves => { const seen = {}; saves.forEach(b => { seen[sent(b)] = (seen[sent(b)] || 0) + 1; }); return Object.keys(seen).filter(key => seen[key] > 1); };
        const jam = () => [row('Jam', '2026-11-06 20:00', { series: series(1, 3) }), row('Jam', '2026-12-04 20:00', { series: series(2, 3) }), row('Alpha', '2026-12-08 19:00'),
            row('Beta', '2026-12-10 19:00'), row('Jam (moved)', '2027-01-02 21:00', { series: series(3, 3) })];

        const scenarios = {
            // A card's Save on a series, and "Add" pressed while its second date is on the wire.
            async card_save_is_locked() {
                const [box, app] = page();
                await box.read(jam());
                box.manual = true;
                const saving = app.saveRow(0);
                await box.quiet(); box.release(1); await box.quiet();
                const midway = { first_date_saved: !! app.savedEvents[0], locked: app.isAddingAll, add_button_live: ! (app.isAddingAll || app.allDone || app.selectedCount === 0), button: app.addLabel };
                const adding = app.addSelected();
                app.handleRemoveEvent(2);
                box.manual = false; box.release();
                await Promise.all([saving, adding]); await box.quiet();
                return { midway, sent: box.saves.map(sent), twice: twice(box.saves), left: box.nav.length, rows_left: app.preview.parsed.length, footer: footer(app), button: app.addLabel, toasts: box.toasts || [] };
            },

            async a_date_that_fails_can_be_tried_again_from_the_card() {
                const [box, app] = page();
                box.failNames = ['Jam (moved)'];
                await box.read(jam());
                await app.saveRow(0); await box.quiet();
                const after = { row: app.rowState(0), first_date_saved: !! app.savedEvents[0], save_live: app.canSaveRow(0), footer: footer(app), left: box.nav.length };
                box.failNames = []; const before = box.saves.length;
                await app.saveRow(0); await box.quiet();
                return { after, retry: box.saves.slice(before).map(sent), row: app.rowState(0), left: box.nav.length };
            },

            async a_series_keeps_what_is_its_own() {
                const out = {};
                const at = body => body.name + ' ' + body.starts_at.slice(11, 16) + ' x' + body.duration + ' at ' + (body.venue_id || [body.venue_name, body.venue_address1, body.venue_city || '-'].join('/'));
                const matched = () => [
                    row('Jam', '2026-11-06 20:00', { series: series(1, 3), venue_name: 'Main Hall', event_address: '1 Main St', venue_id: 'V1', matched_venue_name: 'Main Hall' }),
                    row('Jam', '2026-12-04 20:00', { series: series(2, 3), venue_name: 'Other Hall', event_address: '9 Side Rd', venue_id: 'V2', matched_venue_name: 'Other Hall' }),
                    row('Jam (late)', '2027-01-02 21:00', { series: series(3, 3), venue_name: 'Main Hall', event_address: '1 Main St', venue_id: 'V1', matched_venue_name: 'Main Hall' }),
                    row('One-off', '2027-02-01 19:00'),
                ];
                let [box, app] = page();
                await box.read(matched());
                await app.addSelected(); await box.quiet();
                out.untouched = box.saves.slice(0, 3).map(at);

                [box, app] = page();
                await box.read(matched());
                app.preview.parsed[0].event_name = 'Friday Jam';
                app.preview.parsed[0].event_start_time = app.formatMinutesToTime(19 * 60 + 30);
                app.preview.parsed[0].event_end_time = app.formatMinutesToTime(21 * 60 + 30);
                app.onVenueSelect(0, { id: 'V3', name: 'New Room', address1: '5 New St', city: 'Town' });
                await app.addSelected(); await box.quiet();
                out.card_edited = box.saves.slice(0, 3).map(at);

                [box, app] = page();
                await box.read([
                    row('Jam', '2026-11-06 20:00', { series: series(1, 3), venue_name: 'Main Hall', event_address: '1 Main St' }),
                    row('Jam', '2026-12-04 20:00', { series: series(2, 3), venue_name: 'Other Hall', event_address: '9 Side Rd' }),
                    row('Jam', '2027-01-02 20:00', { series: series(3, 3), venue_name: 'Main Hall', event_address: '1 Main St' }),
                    row('One-off', '2027-02-01 19:00'),
                ]);
                app.preview.parsed[0].event_city = 'Springfield'; app.preview.parsed[0].event_address = '1 Main Street';
                await app.addSelected(); await box.quiet();
                out.typed_venues = box.saves.slice(0, 3).map(at);

                // 20:00 to 22:00; one week it started late (21:00 to 22:00), one week it ran long
                // (20:00 to 23:00). The card moves the series to 18:00 to 20:00.
                [box, app] = page();
                await box.read([
                    row('Jam', '2026-11-06 20:00', { series: series(1, 3), event_duration: 2 }),
                    row('Jam', '2026-12-04 21:00', { series: series(2, 3), event_duration: 1 }),
                    row('Jam', '2027-01-02 20:00', { series: series(3, 3), event_duration: 3 }),
                    row('One-off', '2027-02-01 19:00'),
                ]);
                app.preview.parsed[0].event_start_time = app.formatMinutesToTime(18 * 60);
                app.preview.parsed[0].event_end_time = app.formatMinutesToTime(20 * 60);
                await app.addSelected(); await box.quiet();
                out.hours = box.saves.slice(0, 3).map(body => body.starts_at.slice(11, 16) + ' x' + body.duration);

                return out;
            },

            async a_list_with_nothing_left_finishes() {
                const out = {};
                // Each row saved from its own card.
                let [box, app] = page();
                await box.read([row('Alpha', '2026-11-02 19:00'), row('Beta', '2026-12-10 19:00'), row('Gamma', '2026-12-11 19:00')]);
                box.manual = true;
                const first = app.saveRow(0); await box.quiet();
                out.during_a_card_save = { button: app.addLabel, progress: app.progressLabel };
                box.manual = false; box.release(); await first; await box.quiet();
                out.after_one_card = { footer: footer(app), button: app.addLabel, left: box.nav.length, toasts: box.toasts || [] };
                await app.saveRow(1); await app.saveRow(2); await box.quiet();
                out.after_every_card = { footer: footer(app), button: app.addLabel, left: box.nav };

                // A row fails and is removed, and that was the last one not added.
                [box, app] = page(); box.failNames = ['Bad'];
                await box.read([row('Good', '2026-11-02 19:00'), row('Bad', '2026-11-03 19:00'), row('Fine', '2026-11-04 19:00')]);
                await app.addSelected(); await box.quiet();
                out.after_a_failure = { footer: footer(app), left: box.nav.length };
                app.handleRemoveEvent(1); await box.quiet();
                out.failed_row_removed = { footer: footer(app), button: app.addLabel, left: box.nav.length };

                // The same with a row still to add: the foot of the list is about that row.
                [box, app] = page(); box.failNames = ['Bad'];
                await box.read([row('Good', '2026-11-02 19:00'), row('Bad', '2026-11-03 19:00'), row('Later', '2026-11-04 19:00')]);
                app.toggleRow(2);
                await app.addSelected(); await box.quiet();
                app.handleRemoveEvent(1); await box.quiet();
                out.removed_with_one_left = { footer: footer(app), left: box.nav.length };

                // Rows that were added and then taken off the list are still added.
                [box, app] = page(); box.failNames = ['Jam (moved)'];
                await box.read(jam());
                await app.addSelected(); await box.quiet();
                app.handleRemoveEvent(0); await box.quiet();
                out.removed_after_adding = { footer: footer(app), button: app.addLabel, left: box.nav.length };

                // The day's allowance runs out part way. The rows it stopped at are removed.
                [box, app] = page(); box.limitNames = ['Third'];
                await box.read([row('First', '2026-11-02 19:00'), row('Second', '2026-11-03 19:00'), row('Third', '2026-11-04 19:00'), row('Fourth', '2026-11-05 19:00')]);
                await app.addSelected(); await box.quiet();
                out.stopped_by_the_limit = { footer: footer(app), sent: box.saves.length, left: box.nav.length };
                app.handleRemoveEvent(3); app.handleRemoveEvent(2); await box.quiet();
                out.limit_rows_removed = { footer: footer(app), left: box.nav.length };

                // In the moment between "all added" and leaving, a row left unticked stays as it is.
                [box, app] = page();
                await box.read([row('Alpha', '2026-11-02 19:00'), row('Beta', '2026-12-10 19:00'), row('Not this one', '2026-12-11 19:00')]);
                app.toggleRow(2);
                await app.addSelected(); await box.quiet();
                app.expandRow(2); await app.saveRow(2); await box.quiet();
                out.after_all_done = { done: app.allDone, sent: box.saves.map(sent), opened: app.expandedRow, left: box.nav.length };

                return out;
            },

            async a_row_is_ticked_counted_and_sent_whole() {
                const out = {};
                // The first date of a series has no name: nothing of it goes until its card has one.
                let [box, app] = page();
                await box.read([row('', '2026-11-06 20:00', { series: series(1, 2) }), row('Jam', '2026-12-04 20:00', { series: series(2, 2) }), row('One-off', '2027-02-01 19:00')]);
                out.nameless_first = { ticked: app.rowSelected(0), can_tick: app.rowComplete(0), button: app.addLabel };
                app.preview.parsed[0].event_name = 'Named now'; app.toggleRow(0);
                out.named = { ticked: app.rowSelected(0), button: app.addLabel, names: [0, 1].map(i => app.effectiveRow(i).event_name) };

                // The schedule requires a category, which no feed supplies: chosen on the card, it is every date's.
                [box, app] = page();
                app.requiredFields = Object.assign({}, app.requiredFields, { category_id: true });
                await box.read([row('Jam', '2026-11-06 20:00', { series: series(1, 2) }), row('Jam', '2026-12-04 20:00', { series: series(2, 2) }), row('One-off', '2027-02-01 19:00')]);
                app.toggleAll();
                out.category_required = { button: app.addLabel, selected: app.selectedCount };
                app.preview.parsed[0].category_id = 3; app.toggleRow(0);
                out.category_chosen = { button: app.addLabel, categories: [0, 1].map(i => app.effectiveRow(i).category_id) };

                // The schedule requires a ticket link. The first date has one and the second does not.
                [box, app] = page();
                app.requiredFields = Object.assign({}, app.requiredFields, { registration_url: true });
                await box.read([row('Jam', '2026-11-06 20:00', { series: series(1, 2), registration_url: 'https://tickets.test/jam' }), row('Jam', '2026-12-04 20:00', { series: series(2, 2) }),
                    row('One-off', '2027-02-01 19:00', { registration_url: 'https://tickets.test/one' })]);
                out.required_field_missing_on_a_date = { ticked: app.rowSelected(0), can_tick: app.rowComplete(0), button: app.addLabel, links: [0, 1].map(i => app.effectiveRow(i).registration_url) };

                // A series is ticked, and then its card is emptied of the name. Its second date has a name of its own.
                [box, app] = page();
                await box.read([row('Jam', '2026-11-06 20:00', { series: series(1, 2) }), row('Jam (late)', '2026-12-04 21:00', { series: series(2, 2) }), row('One-off', '2027-02-01 19:00')]);
                app.preview.parsed[0].event_name = '';
                out.emptied_after_ticking = { ticked: app.rowSelected(0), button: app.addLabel };
                await app.addSelected(); await box.quiet();
                out.emptied_after_ticking.sent = box.saves.map(sent);

                // One date of a series looks like an event the schedule already has.
                [box, app] = page();
                await box.read([row('Jam', '2026-11-06 20:00', { series: series(1, 3) }), row('Jam', '2026-12-04 20:00', { series: series(2, 3), event_url: 'https://app.test/e/1', event_id: 'abc' }),
                    row('Jam', '2027-01-02 20:00', { series: series(3, 3) }), row('One-off', '2027-02-01 19:00')]);
                out.lookalike_arrives = { ticked: app.rowSelected(0), note: app.rowProblem(0), listed_date: app.rowListedIndex(0), button: app.addLabel, selected: app.selectedLabel };
                await app.addSelected(); await box.quiet();
                out.lookalike_left_unticked = box.saves.map(sent);
                [box, app] = page();
                await box.read([row('Jam', '2026-11-06 20:00', { series: series(1, 3) }), row('Jam', '2026-12-04 20:00', { series: series(2, 3), event_url: 'https://app.test/e/1', event_id: 'abc' }),
                    row('Jam', '2027-01-02 20:00', { series: series(3, 3) }), row('One-off', '2027-02-01 19:00')]);
                app.toggleRow(0);
                out.lookalike_ticked = { button: app.addLabel };
                await app.addSelected(); await box.quiet();
                out.lookalike_ticked.sent = box.saves.map(sent);

                return out;
            },

            async back_goes_to_what_was_added() {
                const out = {};
                let [box, app] = page();
                await box.read([row('Only', '2026-11-02 19:00')]);
                await app.saveRow(0); await box.quiet();
                app.handleClear();
                out.after_clear = { preview: !! app.preview, added_any: app.addedAny };

                // A curator takes a look-alike with "Select" instead of saving it. It is the last row.
                [box, app] = page();
                app.isCurator = true;
                await box.read([row('Alpha', '2026-11-02 19:00'), row('Beta', '2026-12-10 19:00', { event_url: 'https://app.test/e/2', event_id: 'xyz' })]);
                await app.saveRow(0); await box.quiet();
                await app.handleSelect(1); await box.quiet();
                out.select = { added_any: app.addedAny, left: box.nav.length, footer: footer(app) };

                // "Select" and nothing else, with a row still on the list.
                [box, app] = page();
                app.isCurator = true;
                await box.read([row('Alpha', '2026-11-02 19:00', { event_url: 'https://app.test/e/3', event_id: 'pqr' }), row('Beta', '2026-12-10 19:00'), row('Gamma', '2026-12-11 19:00')]);
                await app.handleSelect(0); await box.quiet();
                out.select_only = { added_any: app.addedAny, left: box.nav.length, sent: box.saves.length };

                [box, app] = page();
                out.nothing_yet = app.addedAny;

                return out;
            },

            async one_event_from_a_link() {
                const [box, app] = page();
                await box.read([row('Only one', '2026-11-02 19:00')], { source: 'page', found: 3, shown: 1, already_on_schedule: 2, can_read_whole_page: true });
                const out = { list: app.listMode, header: app.showsReadSummary, focused: box.focused.includes('import-list-heading') };

                // Its card has an editor. "Read the whole page" brings another event: the editor goes with the first.
                app.showAllFields = true; app.initDescriptionEditors();
                await box.read([row('Another', '2026-11-09 19:00', { event_details: 'the second text' })], {}, true);
                await app.saveRow(0); await box.quiet();
                out.description_sent = box.saves[0].description;

                return out;
            },

            async what_the_page_says_of_a_google_calendar() {
                const out = {};
                let [box, app] = page();
                await box.read([row('Class', '2026-11-02 19:00'), row('Show', '2026-11-03 19:00')], { source: 'google', host: 'Studio classes', calendar_truncated: true });
                out.cut_short = app.listNotes;
                [box, app] = page();
                await box.read([row('Class', '2026-11-02 19:00'), row('Show', '2026-11-03 19:00')], { source: 'google', host: 'Studio classes' });
                out.whole = app.listNotes;

                // The connection stops working between listing the calendars and reading one.
                [box, app] = page();
                app.source = 'google'; app.google.connected = true; app.google.loaded = true;
                box.refuse = { error: 'Your Google connection has expired.', reason: 'reconnect' };
                await app.fetchPreview(false, { id: 'classes', name: 'Studio classes' }); await box.quiet();
                out.reconnect = { connected: app.google.connected, loaded: app.google.loaded, said: app.google.error, page_error: app.errorMessage || null, loading: app.isLoading };

                // Any other refusal of a read is said where every failed read is said.
                [box, app] = page();
                app.source = 'google'; app.google.connected = true; app.google.loaded = true;
                box.refuse = { error: 'That calendar is gone.', reason: 'calendar_gone' };
                await app.fetchPreview(false, { id: 'classes', name: 'Studio classes' }); await box.quiet();
                out.gone = { connected: app.google.connected, page_error: app.errorMessage || null };

                return out;
            },

            async dates_are_read_as_written() {
                const [box, app] = page();
                const inputs = ['2026-11-02 19:30', '2026-11-02T19:30:00', '2026-03-08 02:30', '2026-11-02 24:00', '2026-11-02 7:30 PM', '2026-11-02 12:05 am', '2026-11-02 7:30 p.m.', '2026-11-02 19:30 America/New_York', '2026-11-02',
                    '2026-13-45 99:99', '2026-02-30 19:00', '2026-11-02 24:30', '2026-11-02 13:00 PM', '2026-11-02 12:60', '2026-00-10 10:00'];
                await box.read(inputs.map((at, n) => row('Row ' + n, at)));
                const clock = text => { const minutes = app.parseTimeToMinutes(text); return minutes === null ? '-' : String(Math.floor(minutes / 60)).padStart(2, '0') + ':' + String(minutes % 60).padStart(2, '0'); };
                const out = {};
                app.preview.parsed.forEach((event, n) => { out[inputs[n]] = (event.event_date || '-') + ' ' + clock(event.event_start_time); });
                return { zone: Intl.DateTimeFormat().resolvedOptions().timeZone, read: out };
            },
        };

        (async () => {
            const results = {};
            for (const name of Object.keys(scenarios)) {
                results[name] = await scenarios[name]();
            }
            process.stdout.write(JSON.stringify(results));
        })().catch(error => { process.stderr.write(String(error && error.stack || error)); process.exit(1); });
        JS;

        $base = tempnam(sys_get_temp_dir(), 'importpage');
        $pageFile = $base.'.html';
        $harnessFile = $base.'.cjs';

        try {
            file_put_contents($pageFile, $html);
            file_put_contents($harnessFile, $harness);

            // West of UTC, where a date with no time handed to the browser is the day before.
            $process = new Process(['node', $harnessFile, $pageFile], null, ['TZ' => 'America/Los_Angeles']);
            $process->setTimeout(60);
            $process->run();

            $this->assertTrue(
                $process->isSuccessful(),
                "The import page's script did not run to completion in Node.\n"
                    .'If node is missing, install it - this test must not be skipped.'."\n"
                    .$process->getErrorOutput()
            );

            return self::$results = json_decode($process->getOutput(), true);
        } finally {
            @unlink($pageFile);
            @unlink($harnessFile);
            @unlink($base);
        }
    }

    private function added(int $count): string
    {
        return __('messages.import_added_all', ['count' => $count]);
    }

    public function test_a_cards_save_holds_the_list_until_its_last_date_is_back(): void
    {
        $result = $this->results()['card_save_is_locked'];

        // One date in, the next on the wire: nothing else on the page can send a row. The
        // button goes on counting what is ticked (four are left), and is dead meanwhile.
        $this->assertSame(['first_date_saved' => true, 'locked' => true, 'add_button_live' => false, 'button' => __('messages.import_add_many', ['count' => 4])], $result['midway']);
        // "Add" and "Remove" pressed then did nothing: three requests for three dates, and
        // five rows still on the page. It was six requests, with one date sent twice.
        $this->assertSame(['Jam 11-06 20:00', 'Jam 12-04 20:00', 'Jam (moved) 01-02 21:00'], $result['sent']);
        $this->assertSame([], $result['twice']);
        $this->assertSame(5, $result['rows_left']);
        // Two rows are still to add, so the page stays, and the foot of the list is about them.
        $this->assertSame(0, $result['left']);
        $this->assertSame(__('messages.import_selected_count', ['selected' => 2, 'total' => 2]), $result['footer']);
        $this->assertSame(__('messages.import_add_many', ['count' => 2]), $result['button']);
        $this->assertSame([$this->added(3)], $result['toasts']);
    }

    public function test_a_series_with_a_date_that_failed_keeps_its_save(): void
    {
        $result = $this->results()['a_date_that_fails_can_be_tried_again_from_the_card'];

        // Its first date went in. The row is not done, and its card can still save.
        $this->assertSame('error', $result['after']['row']);
        $this->assertTrue($result['after']['first_date_saved']);
        $this->assertTrue($result['after']['save_live']);
        $this->assertSame(__('messages.import_added_summary', ['added' => 2, 'failed' => 1]), $result['after']['footer']);
        $this->assertSame(0, $result['after']['left']);

        // Pressed again it sends what is left, and only that.
        $this->assertSame(['Jam (moved) 01-02 21:00'], $result['retry']);
        $this->assertSame('saved', $result['row']);
        $this->assertSame(0, $result['left'], 'two other rows are still on the list');
    }

    public function test_a_date_of_a_series_keeps_the_venue_the_name_and_the_hours_the_source_gave_it(): void
    {
        $result = $this->results()['a_series_keeps_what_is_its_own'];

        // Nothing edited: the second date is somewhere else and is saved there. Every date
        // went to the first date's venue.
        $this->assertSame(['Jam 20:00 x2 at V1', 'Jam 20:00 x2 at V2', 'Jam (late) 21:00 x2 at V1'], $result['untouched']);

        // A new name, new hours and another venue on the card reach the dates that had the
        // same: the second date takes the name and the hours and keeps its venue, the third
        // keeps its name and its hours and moves with the venue it shared.
        $this->assertSame(['Friday Jam 19:30 x2 at V3', 'Friday Jam 19:30 x2 at V2', 'Jam (late) 21:00 x2 at V3'], $result['card_edited']);

        // A venue is one thing. A city typed on the card is the first date's venue's city, not
        // the city of a date somewhere else.
        $this->assertSame(
            ['Jam 20:00 x2 at Main Hall/1 Main Street/Springfield', 'Jam 20:00 x2 at Other Hall/9 Side Rd/-', 'Jam 20:00 x2 at Main Hall/1 Main Street/Springfield'],
            $result['typed_venues']
        );

        // So are a date's hours. The date that started late was saved as starting at 21:00
        // and ending at the card's 20:00: twenty-three hours.
        $this->assertSame(['18:00 x2', '21:00 x1', '20:00 x3'], $result['hours']);
    }

    public function test_a_list_with_nothing_left_to_add_finishes_however_it_got_there(): void
    {
        $result = $this->results()['a_list_with_nothing_left_finishes'];
        // A card's own Save is not the button's work: the button goes on saying what is
        // ticked, and afterwards so does the foot of the list. The card says it with a toast.
        $this->assertSame(__('messages.import_add_many', ['count' => 3]), $result['during_a_card_save']['button']);
        $this->assertSame(__('messages.import_selected_count', ['selected' => 2, 'total' => 2]), $result['after_one_card']['footer']);
        $this->assertSame(__('messages.import_add_many', ['count' => 2]), $result['after_one_card']['button']);
        $this->assertSame(0, $result['after_one_card']['left']);
        $this->assertSame([__('messages.event_created')], $result['after_one_card']['toasts']);

        // The last row saved from its card: the list says how many and goes to the schedule.
        // It sat at "0 of 0 selected" beside a dead "Add 0 events".
        $this->assertSame($this->added(3), $result['after_every_card']['footer']);
        $this->assertSame($this->added(3), $result['after_every_card']['button']);
        $this->assertCount(1, $result['after_every_card']['left']);
        $this->assertStringEndsWith('/import/done', parse_url($result['after_every_card']['left'][0], PHP_URL_PATH));

        // A row that failed is removed and was the last one not added: the same.
        $this->assertSame(__('messages.import_added_summary', ['added' => 2, 'failed' => 1]), $result['after_a_failure']['footer']);
        $this->assertSame(0, $result['after_a_failure']['left']);
        $this->assertSame(['footer' => $this->added(2), 'button' => $this->added(2), 'left' => 1], $result['failed_row_removed']);

        // With a row still to add, the page stays and stops counting the row that is gone.
        $this->assertSame(['footer' => __('messages.import_selected_count', ['selected' => 0, 'total' => 1]), 'left' => 0], $result['removed_with_one_left']);

        // Four went in, then a row holding two of them was taken off the list: still four.
        $this->assertSame(['footer' => $this->added(4), 'button' => $this->added(4), 'left' => 1], $result['removed_after_adding']);

        // Stopped by the day's allowance, the page says so and stays. With the rows it could
        // not add removed, it finishes, and no longer says it stopped.
        $this->assertSame(3, $result['stopped_by_the_limit']['sent'], 'nothing is sent after the row that met the limit');
        $this->assertStringContainsString(__('messages.import_stopped_daily_limit'), $result['stopped_by_the_limit']['footer']);
        $this->assertSame(0, $result['stopped_by_the_limit']['left']);
        $this->assertSame(['footer' => $this->added(2), 'left' => 1], $result['limit_rows_removed']);

        // Once the list has said "all added" it is on its way out: nothing more is opened or sent.
        $this->assertSame(['done' => true, 'sent' => ['Alpha 11-02 19:00', 'Beta 12-10 19:00'], 'opened' => null, 'left' => 1], $result['after_all_done']);
    }

    public function test_a_row_is_ticked_counted_and_sent_whole(): void
    {
        $result = $this->results()['a_row_is_ticked_counted_and_sent_whole'];

        // A series whose first date has no name: not ticked, not tickable, and none of its
        // dates counted. Its second date used to be sent on its own.
        $this->assertSame(['ticked' => false, 'can_tick' => false, 'button' => __('messages.import_add_one')], $result['nameless_first']);
        // Named on the card it is all there, and the date that had a name keeps it.
        $this->assertSame(['ticked' => true, 'button' => __('messages.import_add_many', ['count' => 3]), 'names' => ['Named now', 'Jam']], $result['named']);

        // A field the schedule requires, filled in on the card, is every date's.
        $this->assertSame(['button' => __('messages.import_add_many', ['count' => 0]), 'selected' => 0], $result['category_required']);
        $this->assertSame(['button' => __('messages.import_add_many', ['count' => 2]), 'categories' => [3, 3]], $result['category_chosen']);

        // A date that arrived without a field the schedule requires takes the first date's, so
        // the row can be added: nothing on its card could have filled it in.
        $this->assertSame([
            'ticked' => true,
            'can_tick' => true,
            'button' => __('messages.import_add_many', ['count' => 3]),
            'links' => ['https://tickets.test/jam', 'https://tickets.test/jam'],
        ], $result['required_field_missing_on_a_date']);

        // A row emptied of its name after it was ticked is not sent in part: its second date,
        // which has a name of its own, used to go alone.
        $this->assertSame(['ticked' => false, 'button' => __('messages.import_add_one'), 'sent' => ['One-off 02-01 19:00']], $result['emptied_after_ticking']);

        // One date of a series resembles an event already on the schedule. The row says so
        // and starts unticked, whole: it showed as ticked with "3 dates" and sent two.
        $this->assertSame([
            'ticked' => false,
            'note' => __('messages.import_already_listed'),
            'listed_date' => 1,
            'button' => __('messages.import_add_one'),
            'selected' => __('messages.import_selected_count', ['selected' => 1, 'total' => 2]),
        ], $result['lookalike_arrives']);
        $this->assertSame(['One-off 02-01 19:00'], $result['lookalike_left_unticked']);
        // Ticked by the person, it is added whole, and the button said so before it was pressed.
        $this->assertSame(__('messages.import_add_many', ['count' => 4]), $result['lookalike_ticked']['button']);
        $this->assertSame(['Jam 11-06 20:00', 'Jam 12-04 20:00', 'Jam 01-02 20:00', 'One-off 02-01 19:00'], $result['lookalike_ticked']['sent']);
    }

    public function test_back_goes_to_what_was_added_after_the_page_is_cleared(): void
    {
        $result = $this->results()['back_goes_to_what_was_added'];

        $this->assertFalse($result['nothing_yet']);
        // "Clear" empties the list of saved rows the Back button used to look in.
        $this->assertSame(['preview' => false, 'added_any' => true], $result['after_clear']);
        // A curator's "Select" puts an event on the schedule too, and as the last row of a
        // list it ends the list like any other.
        $this->assertTrue($result['select']['added_any']);
        $this->assertSame(1, $result['select']['left']);
        // By itself, with rows still to add, it stays on the page and is remembered for Back.
        $this->assertSame(['added_any' => true, 'left' => 0, 'sent' => 0], $result['select_only']);
    }

    public function test_one_event_from_a_link_is_announced_and_saved_with_its_own_text(): void
    {
        $result = $this->results()['one_event_from_a_link'];

        $this->assertSame(['list' => false, 'header' => true, 'focused' => true], array_intersect_key($result, array_flip(['list', 'header', 'focused'])));
        // "Read the whole page" replaced the event. The first event's editor was left in
        // place, and the new event was saved with what it held ("editor 1").
        $this->assertSame('editor 2', $result['description_sent']);
    }

    public function test_what_the_page_says_of_a_google_calendar(): void
    {
        $result = $this->results()['what_the_page_says_of_a_google_calendar'];

        // A calendar too long to read whole says so among the list's notes, and only then.
        $this->assertContains(__('messages.import_calendar_truncated'), $result['cut_short']);
        $this->assertNotContains(__('messages.import_calendar_truncated'), $result['whole']);
        $this->assertContains(__('messages.import_one_time_copy_google'), $result['whole']);

        // A connection that stopped working puts the Connect button back, with why, where the
        // calendars were. It read as a general error over a list of calendars that no longer worked.
        $this->assertSame(['connected' => false, 'loaded' => false, 'said' => 'Your Google connection has expired.', 'page_error' => null, 'loading' => false], $result['reconnect']);
        $this->assertSame(['connected' => true, 'page_error' => 'That calendar is gone.'], $result['gone']);
    }

    public function test_a_date_is_read_as_it_is_written(): void
    {
        $result = $this->results()['dates_are_read_as_written'];

        $this->assertSame('America/Los_Angeles', $result['zone'], 'run west of UTC, where the browser gets a bare date wrong');
        $this->assertSame([
            '2026-11-02 19:30' => '2026-11-02 19:30',
            '2026-11-02T19:30:00' => '2026-11-02 19:30',
            // 02:30 does not exist that night in Los Angeles. It is the schedule's time, not the browser's.
            '2026-03-08 02:30' => '2026-03-08 02:30',
            // The midnight that ends the day.
            '2026-11-02 24:00' => '2026-11-03 00:00',
            '2026-11-02 7:30 PM' => '2026-11-02 19:30',
            '2026-11-02 12:05 am' => '2026-11-02 00:05',
            '2026-11-02 7:30 p.m.' => '2026-11-02 19:30',
            // What follows the time is not read. A zone's name that begins "Am" is not "AM".
            '2026-11-02 19:30 America/New_York' => '2026-11-02 19:30',
            // No time given: the day is kept and the time is asked for. It was the evening before.
            '2026-11-02' => '2026-11-02 -',
            // The shape of a date, and not one.
            '2026-13-45 99:99' => '- -',
            '2026-02-30 19:00' => '- -',
            '2026-11-02 24:30' => '- -',
            '2026-11-02 13:00 PM' => '- -',
            '2026-11-02 12:60' => '- -',
            '2026-00-10 10:00' => '- -',
        ], $result['read']);
    }
}
