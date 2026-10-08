{{-- The month's script: how a day is written, what a card and a day's panel hold, and the hands
     (pointer, keyboard, touch) that open them. A mixin of the calendar's Vue app
     (role/partials/calendar: `mixins: [window.monthMixin]`), in a file of its own because that
     one is five thousand lines and several hands are in it.

     Vue draws the month from `monthWeeks` (role/partials/month) and the card and the panel
     from `monthPeek` and `monthDayPanel` (role/partials/month-peek). Everything a template
     prints is worked out here once, into plain values, so a redraw computes nothing.
     What Vue cannot know is done to the page after it has drawn (monthFit()): how many more
     events a day's row has room for, where a name has to be cut. Those touch only classes,
     attributes and text that no binding owns, so a redraw leaves them alone.

     Nothing here prints anybody's text as markup: a name reaches the page through v-clamp or
     v-text. --}}
@php
    // The month's own words. The ones an owner can rename go through $label().
    $monthLabels = [
        'today' => __('messages.today'), 'tomorrow' => __('messages.tomorrow'),
        'now' => __('messages.month_now'), 'happening_now' => __('messages.happening_now'),
        'sold_out' => __('messages.sold_out'), 'few_left' => __('messages.few_left'),
        'free' => $label('free_entry'), 'cancelled' => __('messages.cancelled'),
        'draft' => __('messages.draft'), 'internal' => __('messages.internal'),
        'more' => __('messages.month_more'), 'earlier' => __('messages.month_earlier'), 'drafts' => __('messages.month_drafts'),
        'events' => __('messages.events'), 'password' => __('messages.password_protected'),
        'view_event' => __('messages.view_event'), 'edit_event' => __('messages.edit_event'), 'details' => __('messages.details'),
        'register' => $label('register'), 'get_tickets' => $label('get_tickets'), 'buy_tickets' => $label('buy_tickets'),
        'share' => __('messages.share'), 'copied' => __('messages.link_copied'),
        'online' => __('messages.online'), 'coupon' => __('messages.coupon_code'),
        'n_of' => __('messages.month_n_of'), 'nothing_in' => __('messages.nothing_scheduled_in'),
        'next_up' => __('messages.next_up'), 'go_to' => __('messages.go_to_month'),
        'add_event' => __('messages.add_event'), 'unavailable' => __('messages.unavailable'),
    ];
    // A plus on a day, for whoever may add an event: on the Schedule tab where the header's own
    // Add Event button shows (a verified schedule, not a viewer), on the dashboard to the first
    // schedule the dashboard's own Add Event offers. The date is put in by the script.
    $monthAdd = null;
    if ($route === 'admin' && ($tab ?? '') === 'schedule' && $role->email_verified_at && ! (auth()->check() && auth()->user()->isViewer($role->subdomain))) {
        $monthAdd = route('event.create', ['subdomain' => $role->subdomain, 'date' => 'MONTH-DATE']);
    } elseif ($route === 'home') {
        // The dashboard's own list of where Add Event may go (HomeDashboard::addTargets():
        // schedules this person EDITS that can take an event yet), so the plus never leads to
        // a refusal. The first schedule the person belongs to could be one they only view.
        $monthFirst = $dashboard['addTargets'][0]['add'] ?? null;
        $monthAdd = $monthFirst ? $monthFirst.(str_contains($monthFirst, '?') ? '&' : '?').'date=MONTH-DATE' : null;
    }
    // The days team members marked themselves away (the Schedule tab only), by name as written.
    $monthAway = [];
    if ($route === 'admin' && ($tab ?? '') === 'schedule') {
        // Only the days this page shows: a member's whole history of days away is not the month's.
        $monthFrom = isset($startOfMonth) ? $startOfMonth->format('Y-m-d') : '0000-00-00';
        $monthTo = isset($endOfMonth) ? $endOfMonth->format('Y-m-d') : '9999-99-99';
        foreach (($unavailableMembers ?? []) as $member) {
            $dates = array_values(array_filter((array) ($member['dates'] ?? []), fn ($date) => is_string($date) && $date >= $monthFrom && $date <= $monthTo));
            if ($dates) {
                $monthAway[] = ['name' => (string) ($member['name'] ?? ''), 'dates' => $dates];
            }
        }
    }
    // What each page's card may offer (the plan's "what differs by page"). An embed's card
    // has neither: the embed guide tells owners those live on the event page.
    $monthCan = [
        'calendar' => $route === 'guest' && ! $guestEmbed,
        'share' => ! $guestEmbed,
        'guest' => $route === 'guest',
        'add' => $monthAdd,
    ];
@endphp
<script {!! nonce_attr() !!}>
window.monthMixin = (function () {
'use strict';

const OPEN_MS = 240;        // rest on an event this long before its card opens
const SWITCH_MS = 70;       // once one is open, the next opens almost at once
const TOWARD_MS = 320;      // unless the pointer is on its way to the card
const CLOSE_MS = 340;       // and it stays this long after the pointer leaves both
const NARROW = 150;         // px of day below which the card says less and is three days wide
const L = @json($monthLabels);
const CAN = @json($monthCan);
const AWAY = @json($monthAway);

const two = (n) => String(n).padStart(2, '0');
const ymd = (d) => d.getFullYear() + '-' + two(d.getMonth() + 1) + '-' + two(d.getDate());
const day = (s) => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
const shift = (s, n) => { const d = day(s); d.setDate(d.getDate() + n); return ymd(d); };
const say = (text, values) => Object.keys(values).reduce((out, k) => out.replace(':' + k, values[k]), String(text || ''));
const fineHover = () => !!(window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches);
// A leading mark (an emoji, a symbol) is never left alone on a line: it is tied to the first
// word, at every width. Where the pair is longer than the whole line the month is set a size
// smaller (monthFit()), and only then does the word break inside.
const tied = (name) => String(name || '').replace(/^([^\p{L}\p{N}\s"'(\[]{1,8})\s+(?=\S)/u, '$1\u00A0');

return {
    data() {
        return {
            // Moved once a minute: "Now", "1 earlier today" and what is over follow the clock.
            monthTick: 0,
            // The card (monthShow()) and the day's panel (monthOpenDay()), as plain values.
            monthPeek: null,
            monthDayPanel: null,
            // The side the month last arrived from ('gk-cal-weeks-next', '-prev' or ''), for its slide.
            monthCame: '',
        };
    },

    computed: {
        // Which column is today's weekday, in the month that holds today; -1 elsewhere.
        monthTodayCol() {
            void this.monthTick;
            const today = this.scheduleDay(0);
            if (today.slice(0, 7) !== this.monthYearDatetime) return -1;
            return this.calendarDays.slice(0, 7).findIndex((d) => day(d.date).getDay() === day(today).getDay());
        },

        // The month, a week at a time, with everything a day prints already decided.
        monthWeeks() {
            void this.monthTick;
            const today = this.scheduleDay(0);
            const inThisMonth = today.slice(0, 7) === this.monthYearDatetime;
            const days = this.calendarDays;
            const weeks = [];
            for (let i = 0; i < days.length; i += 7) {
                const cells = days.slice(i, i + 7).map((d) => ({ date: d.date, n: d.day, inMonth: d.isCurrentMonth, today: d.date === today, past: d.date < today }));
                const runs = this.monthLanes(cells);
                // Written small: a week that is wholly over, and only in the month that holds
                // today. Today's week, and a month somebody went back to, keep their size.
                const small = inThisMonth && cells.every((c) => c.past);
                weeks.push({ key: cells[0].date, small, days: cells.map((c, col) => this.monthDay(c, runs, col, small)) });
            }
            return weeks;
        },

        // Nothing at all on the days of this month (its neighbours' days do not count).
        monthIsBare() {
            return !this.monthWeeks.some((w) => w.days.some((d) => d.inMonth && d.count > 0));
        },

        // What an empty month says: that it is empty, and the next event the page holds, if any
        // (a guest's page holds what is to come; the admin's months hold only themselves).
        monthEmpty() {
            void this.monthTick;
            const lang = this.languageCode;
            const title = say(L.nothing_in, { month: new Date(this.pageYear, this.pageMonth - 1, 1).toLocaleDateString(lang, { month: 'long' }) });
            const last = ymd(new Date(this.pageYear, this.pageMonth, 0));
            const today = this.scheduleDay(0);
            const nx = CAN.guest ? this.allMobileOccurrences.find((e) => e.occurrenceDate && e.occurrenceDate > last && e.occurrenceDate >= today) : null;
            // Whoever may add an event is offered that, on today or the month's first day.
            const add = CAN.add ? CAN.add.replace('MONTH-DATE', today.slice(0, 7) === this.monthYearDatetime ? today : this.monthYearDatetime + '-01') : null;
            if (!nx) return { title, next: null, add };
            const d = day(nx.occurrenceDate);
            return { title, add, next: {
                name: this.getEventDisplayName(nx), date: nx.occurrenceDate,
                when: d.toLocaleDateString(lang, { weekday: 'short', month: 'short', day: 'numeric' }),
                go: say(L.go_to, { month: d.toLocaleDateString(lang, { month: 'long' }) }),
            } };
        },
    },

    methods: {
        /* ---- an event on a day ------------------------------------------------------------ */

        monthOn(date) {
            return this.getEventsForDate(date).filter((e) => this.isEventVisible(e));
        },

        // "7pm", "7:30pm" in the month (short), "7:00 PM" in the card; 24-hour where the schedule is.
        monthTime(hhmm, short) {
            if (!hhmm) return '';
            const [h, mi] = hhmm.split(':').map(Number);
            const d = new Date(2000, 0, 1, h, mi);
            const lang = this.languageCode;
            if (this.use24Hour) return d.toLocaleTimeString(lang, { hour: '2-digit', minute: '2-digit', hour12: false });
            if (!short) return d.toLocaleTimeString(lang, { hour: 'numeric', minute: '2-digit', hour12: true });
            const parts = new Intl.DateTimeFormat(lang, { hour: 'numeric', minute: '2-digit', hour12: true }).formatToParts(d);
            let out = '';
            parts.forEach((p, i) => {
                if (mi === 0 && (p.type === 'minute' || (p.type === 'literal' && parts[i + 1] && parts[i + 1].type === 'minute'))) return;
                out += p.value;
            });
            return /^en/.test(lang) ? out.replace(/\s+/g, '').toLowerCase() : out.trim();
        },

        // The run of days an event that lasts several is on, around a date: [first, last].
        monthRun(e, date) {
            const series = !!(e.days_of_week && e.days_of_week.length);
            if (!series && e.local_date && e.local_end_date) return [e.local_date, e.local_end_date];
            if (series) {
                // One occurrence of a series: from its own day to the day it ends. The days a
                // series is on are its STARTS, so walking them made a weekly weekend end on the
                // day it began, and a daily one a single run that answered for its first day.
                const start = (e.local_starts_at || '').slice(11, 16);
                if (!start || !(e.duration > 0)) return [date, date];
                const s = day(date); const [h, mi] = start.split(':').map(Number);
                return [date, ymd(new Date(s.getFullYear(), s.getMonth(), s.getDate(), h, mi + Math.round(e.duration * 60) - 1))];
            }
            const has = (d) => (this.eventsMap[d] || []).includes(e.id);
            let a = date, b = date;
            for (let i = 0; i < 40 && has(shift(a, -1)); i++) a = shift(a, -1);
            for (let i = 0; i < 40 && has(shift(b, 1)); i++) b = shift(b, 1);
            return [a, b];
        },

        // What is true of an event on a day. Its tickets are asked of the list's own helpers
        // (rowSoldOut(), rowLow(), rowPrice() ...), through a COPY of the row that carries the
        // day: those read the day off the row, and the shared row of a series holds its first.
        monthFacts(e, date) {
            const start = (e.local_starts_at || '').slice(11, 16);
            const span = !!e.is_multi_day;
            const [first, last] = span ? this.monthRun(e, date) : [date, date];
            const row = Object.assign({}, e, { occurrenceDate: first, _originalOccurrenceDate: first });
            const now = this.monthNow(e.zone);
            let live = false, ended = false, end = '', endDay = last;
            if (start) {
                const hours = e.duration > 0 ? e.duration : 2;
                const s = day(first); const [h, mi] = start.split(':').map(Number);
                const endAt = new Date(s.getFullYear(), s.getMonth(), s.getDate(), h, mi + Math.round(hours * 60));
                const endStr = ymd(endAt) + ' ' + two(endAt.getHours()) + ':' + two(endAt.getMinutes());
                const began = now >= first + ' ' + start;
                live = began && now < endStr;
                ended = now >= endStr;
                if (e.duration > 0) { end = two(endAt.getHours()) + ':' + two(endAt.getMinutes()); endDay = ymd(endAt); }
            }
            // By its own end where it has a time: a 10pm night is not over at midnight.
            const past = start ? ended : last < now.slice(0, 10);
            const over = this.rowSalesOver(row);
            const locked = !!e.is_password_protected;
            const cancelled = !!e.is_cancelled;
            const soldOut = !cancelled && this.rowSoldOut(row);
            const low = !cancelled && !soldOut && this.rowLow(row);
            const price = this.rowPrice(row);
            const freeTicket = !!e.ticket_free;
            const free = !!((freeTicket && !over) || (e.rsvp_enabled && !locked && !past));
            const elsewhere = this.rowSoldElsewhere(row);
            // endDay: the day the hour in `end` belongs to. `last` is the last day an event is ON,
            // which for one that ends on the stroke of midnight is the day before.
            return { start, end, endDay, span, first, last, past, over, live, ended, locked, soldOut, low, price, free, freeTicket, elsewhere, cancelled,
                draft: !!e.is_draft, internal: !!e.is_internal };
        },

        // The one thing a day may say under an event's name: [tone, words, is it a price].
        // Sold out is said plainly, not in red: it is the event a visitor can do least about.
        monthNote(e, f) {
            if (f.cancelled) return ['out', L.cancelled];
            if (f.past) return null;
            if (f.soldOut) return ['out', L.sold_out];
            if (f.low) return ['warn', L.few_left];
            if (f.free) return ['ok', L.free];
            if (f.price) return ['', f.price, true];
            if (f.elsewhere) return e.ticket_price == 0 ? ['ok', L.free] : ['', this.formatPrice(e.ticket_price, e.ticket_currency_code), true];
            return null;
        },

        // The schedule's clock, read once a second at the most: a month of 275 events asked it
        // more than a thousand times a draw.
        monthNow(zone) {
            // A watcher reads the month before created() has made monthHands, and on a page
            // that is handed its events with the page (?graphic=1) that first read gets here.
            const m = this.monthHands, stamp = Math.floor(Date.now() / 1000);
            if (!m) return this.scheduleNow(zone);
            if (!m.now || m.now.stamp !== stamp) m.now = { stamp, at: {} };
            const key = zone || '';
            return m.now.at[key] || (m.now.at[key] = this.scheduleNow(zone));
        },

        // What only a member is sent, and is told in words wherever the event is written.
        monthMark(e) {
            if (e.is_internal) return ['warn', L.internal, 'internal'];
            if (e.is_draft) return ['', L.draft, 'draft'];
            return null;
        },

        // Bars for the events that run over several days: per week, which lane each takes.
        monthLanes(cells) {
            const runs = [];
            const seen = {};
            cells.forEach((cell, col) => {
                this.monthOn(cell.date).filter((e) => e.is_multi_day).forEach((e) => {
                    if (seen[e.id] != null && seen[e.id] >= col) return;
                    let end = col;
                    // A series is on a day because an occurrence STARTS there: each is its own bar.
                    const series = !!(e.days_of_week && e.days_of_week.length);
                    while (!series && end < 6 && (this.eventsMap[cells[end + 1].date] || []).includes(e.id)) end++;
                    seen[e.id] = end;
                    const [first, last] = this.monthRun(e, cell.date);
                    // An occurrence of a series is drawn on its own day only, so it is not
                    // given the edge that says a bar goes on: nothing is drawn where it would lead.
                    runs.push({ e, col, end, in: first < cell.date, out: !series && last > cells[end].date, first });
                });
            });
            runs.sort((x, y) => x.col - y.col || (y.end - y.col) - (x.end - x.col));
            const taken = [];
            runs.forEach((r) => {
                let lane = 0;
                while (taken[lane] && taken[lane].some((o) => !(o.end < r.col || o.col > r.end))) lane++;
                (taken[lane] = taken[lane] || []).push(r);
                r.lane = lane;
            });
            return runs;
        },

        // One event in a day, as the template prints it. opt.sub: 'all' says its state and its
        // price, 'state' only a state, 'none' nothing. opt.small: the name alone.
        monthChip(e, date, opt) {
            const f = this.monthFacts(e, date);
            const note = this.monthNote(e, f);
            const mark = this.monthMark(e);
            const time = f.start && !opt.small ? this.monthTime(f.start, true) : '';
            const notes = [];
            // What a member is told about an event (Draft, Internal, Cancelled) is said on every
            // day it is written, one that is over too: an owner looks back for the one that was
            // never published. What is said about its tickets is for a day still to come.
            const told = opt.sub !== 'none';
            if (mark) notes.push({ k: 'mark', cls: 'gk-cal-note-say', text: mark[1], mark: mark[2] });
            if (told && f.live) notes.push({ k: 'now', cls: 'gk-cal-note-say gk-cal-now', text: L.now, mark: null });
            if (note && !note[2] && (told || f.cancelled)) notes.push({ k: 'state', cls: 'gk-cal-note-say' + (note[0] ? ' gk-cal-note-' + note[0] : ''), text: note[1], mark: null });
            if (note && note[2] && opt.sub === 'all') notes.push({ k: 'price', cls: 'gk-cal-note-price', text: note[1], mark: null });
            const cls = [];
            if (f.cancelled) cls.push('gk-cal-ev-off');
            if (f.past) cls.push('gk-cal-ev-past');
            // The time ends the event's last line. Where a line under the name says a state or a
            // price, that is the last line, and the time stands at its end.
            if (notes.length && time) cls.push('gk-cal-ev-under');
            let art = null, tall = false;
            if (opt.art) {
                art = (!f.locked && (e.image_thumb_url || e.image_url || e.flyer_thumb_url || e.flyer_url)) || null;
                tall = !!(e.flyer_width && e.flyer_height && e.flyer_height > e.flyer_width * 1.12);
                if (art || !opt.lead) cls.push('gk-cal-ev-feat');
                if (art && opt.lead) cls.push('gk-cal-ev-lead');
                if (!art && !opt.lead) cls.push('gk-cal-ev-bare');
            }
            const name = this.getEventDisplayName(e);
            const said = [f.start ? this.monthTime(f.start) : '', f.live ? L.now : '', note ? note[1] : '', mark ? mark[1] : ''].filter(Boolean).join(', ');
            return {
                key: e.id + '|' + date, id: e.id, url: e.guest_url ? this.getEventUrl(e, date) : null, cls: cls.join(' '),
                label: name + (said ? ', ' + said : ''), name: tied(name), dir: this.getEventDisplayDir(e),
                dot: this.getEventDotColor(e), lock: f.locked, time, notes, art, tall,
                spare: !!opt.spare, at: f.start, draft: !!e.is_draft,
            };
        },

        // What a day says about the events it does not show: how many, how many of them are
        // drafts, and the hours they cover (last, and the first thing to go where the line
        // has no room: a time is never cut short).
        monthMore(lateN, goneN, hours, drafts, past) {
            const sorted = hours.filter(Boolean).sort();
            const span = sorted.length ? (sorted[0] === sorted[sorted.length - 1] ? this.monthTime(sorted[0], true) : this.monthTime(sorted[0], true) + '–' + this.monthTime(sorted[sorted.length - 1], true)) : '';
            return {
                words: lateN ? say(L.more, { count: lateN + goneN }) : say(L.earlier, { count: goneN }),
                drafts: drafts ? say(L.drafts, { count: drafts }) : '',
                hours: past ? '' : span,
            };
        },

        // A day of the month, as the template prints it.
        monthDay(cell, runs, col, small) {
            const all = this.monthOn(cell.date);
            const singles = all.filter((e) => !e.is_multi_day);
            const d = day(cell.date);
            const full = d.toLocaleDateString(this.languageCode, { weekday: 'long', month: 'long', day: 'numeric' });
            const cls = [];
            if (!cell.inMonth) cls.push('gk-cal-day-out');
            if (cell.past) cls.push('gk-cal-day-past');
            if (cell.today) cls.push('gk-cal-day-today');
            // Team members away that day (the admin's Schedule tab): a tint and a mark, and who.
            const away = AWAY.filter((member) => member.dates.includes(cell.date)).map((member) => member.name);
            if (away.length) cls.push('gk-cal-day-away');

            const mine = runs.filter((r) => r.col <= col && r.end >= col);
            const laneCount = mine.length ? Math.max.apply(null, mine.map((r) => r.lane)) + 1 : 0;
            const lanes = [];
            for (let i = 0; i < laneCount; i++) {
                const r = mine.find((x) => x.lane === i);
                if (r && r.col === col) {
                    const f = this.monthFacts(r.e, cell.date);
                    const name = this.getEventDisplayName(r.e);
                    lanes.push({
                        id: r.e.id, url: r.e.guest_url ? this.getEventUrl(r.e, r.first) : null, cols: r.end - r.col + 1, name: tied(name), label: name, dir: this.getEventDisplayDir(r.e),
                        cls: (r.in ? 'gk-cal-span-in ' : '') + (r.out ? 'gk-cal-span-out ' : '') + (f.past ? 'gk-cal-span-past' : ''),
                    });
                } else {
                    lanes.push(null);
                }
            }

            // What is over today gives its place to what is not, and does not count against it.
            // When nothing is left today there is nobody to give the place to: the day is then
            // written as any day that is over, its events quiet, and not as a bare
            // "2 earlier today" with no name on it.
            let gone = cell.today ? singles.filter((e) => this.monthFacts(e, cell.date).ended) : [];
            if (gone.length === singles.length) gone = [];
            const live = singles.filter((e) => !gone.includes(e));
            const n = live.length;
            // How much each event may say. A small row has room for about four lines: one event
            // takes three, two take two each. Any other day that is over keeps its size and goes
            // quiet: in today's week the row is that tall anyway.
            let lines = 2, sub = 'state', keep = 3, art = false;
            if (small) { lines = n === 1 ? 3 : n === 2 ? 2 : 1; sub = 'none'; keep = 2; } else {
                if (n <= 2) lines = 3;
                art = n === 1;
                sub = cell.past ? 'none' : (n <= 2 ? 'all' : 'state');
            }
            // "+1 more" would take the line the one event could have had.
            const shown = n <= keep + 1 ? live : live.slice(0, keep);
            const late = live.filter((e) => !shown.includes(e));
            const hidden = late.concat(gone);
            // Today leads with the picture of what is on now or next: on the busiest schedule
            // there is then one thing to look at before anything is hovered.
            const lead = cell.today && !small && n > 1 && !this.monthFacts(shown[0], cell.date).ended ? shown[0] : null;
            const chips = shown.map((e) => this.monthChip(e, cell.date, { sub, art: art || e === lead, lead: e === lead, small }));
            let more = null;
            if (hidden.length) {
                // A coming day's other events are in the page too, out of sight: monthFill()
                // shows as many as the week's row already has room for.
                if (!small && !cell.past) late.forEach((e) => chips.push(this.monthChip(e, cell.date, { sub, small, spare: true })));
                more = this.monthMore(late.length, gone.length, late.map((e) => (e.local_starts_at || '').slice(11, 16)), hidden.filter((e) => e.is_draft).length, cell.past);
                more.gone = gone.length;
                more.goneDrafts = gone.filter((e) => e.is_draft).length;
                more.label = more.words + ', ' + full;
            }
            return {
                date: cell.date, col, inMonth: cell.inMonth, today: cell.today, past: cell.past, cls: cls.join(' '),
                num: cell.n === 1 ? d.toLocaleDateString(this.languageCode, { month: 'short', day: 'numeric' }) : String(cell.n),
                count: all.length, label: full + (all.length ? ', ' + L.events + ': ' + all.length : ''), full,
                away: away.length ? L.unavailable + ': ' + away.join(', ') : '',
                add: CAN.add ? CAN.add.replace('MONTH-DATE', cell.date) : null, addLabel: L.add_event + ': ' + full,
                lanes, chips, more,
                listCls: 'gk-cal-list-' + lines + (lines > 1 ? ' gk-cal-list-wrap' : '') + (art ? ' gk-cal-list-feat' : ''),
            };
        },

        /* ---- what no stylesheet can do, done to the page after Vue has drawn it ------------ */

        // A picture that turns out to be a portrait flyer is shown whole.
        monthArtLoaded(ev) {
            const img = ev.target;
            if (img.naturalHeight > img.naturalWidth * 1.12) img.parentNode.classList.add('gk-cal-art-tall');
        },

        // Where a week's row is taller than a coming day needs (today's picture, a neighbour's
        // long names), that day shows more of its events before "+N more": as many as fit
        // without making the row any taller.
        monthFill(root) {
            root.querySelectorAll('.gk-cal-spare').forEach((li) => { li.hidden = true; });
            root.querySelectorAll('.gk-cal-more-li').forEach((li) => { li.hidden = false; });
            root.querySelectorAll('.gk-cal-week').forEach((week) => {
                const days = Array.from(week.querySelectorAll('.gk-cal-day')).filter((d) => d.querySelector('.gk-cal-spare'));
                if (!days.length) return;
                const h = week.getBoundingClientRect().height;
                days.forEach((d) => {
                    const spare = Array.from(d.querySelectorAll('.gk-cal-spare'));
                    const btn = d.querySelector('.gk-cal-more');
                    if (!btn) return;
                    const gone = Number(btn.dataset.gone || 0);
                    let n = 0;
                    for (let i = 0; i < spare.length; i++) {
                        const last = i === spare.length - 1 && !gone;
                        spare[i].hidden = false;
                        if (last) btn.parentNode.hidden = true;   // the last one takes the place of "+1 more"
                        if (week.getBoundingClientRect().height > h + 0.5) { spare[i].hidden = true; if (last) btn.parentNode.hidden = false; break; }
                        n++;
                    }
                    const rest = spare.slice(n);
                    if (!rest.length && !gone) return;
                    // What is left behind the button, said again. Its three parts are always in
                    // the page (role/partials/month), so only their text and their hiding change.
                    const more = this.monthMore(rest.length, gone, rest.map((x) => x.dataset.at), rest.filter((x) => x.hasAttribute('data-draft')).length + Number(btn.dataset.goneDrafts || 0), false);
                    const part = (cls, text) => { const el = btn.querySelector(cls); if (el) { el.textContent = text; el.hidden = !text; } };
                    part('.gk-cal-more-n', more.words);
                    part('.gk-cal-more-say', more.drafts);
                    part('.gk-cal-more-from', more.hours);
                    btn.setAttribute('aria-label', more.words + ', ' + (d.dataset.full || ''));
                });
            });
        },

        // Run after every draw of the month and on a change of its width:
        // 1. a day shows as many events as its week's row already has room for (monthFill());
        // 2. a first word (with the mark tied to it) longer than a whole line sets the month a
        //    size smaller before it is let break inside (the whole month, so no day stands out);
        // 3. a name that would be cut takes the lines its row still has to spare;
        // 4. a name that still runs past its lines ends in an ellipsis, cut so that the time
        //    still stands at the end of its last line. There is none to be had from CSS on a
        //    line that shares its end. The whole name stays in data-full and in the link's label.
        monthFit() {
            const root = this.$refs.monthRoot;
            if (!root || !root.offsetParent) return;
            this.monthFill(root);
            const parts = Array.from(root.querySelectorAll('.gk-cal-list-wrap .gk-cal-ev'))
                .map((a) => ({ a, name: a.querySelector('.gk-cal-name'), nm: a.querySelector('.gk-cal-nm') }))
                .filter((p) => p.name && p.nm);
            parts.forEach((p) => {
                const full = p.nm.dataset.full || '';
                if (p.nm.textContent !== full) p.nm.textContent = full;
                p.name.classList.remove('gk-cal-name-cut');
                p.name.style.maxHeight = '';
                delete p.nm.dataset.shown;
            });
            root.classList.remove('gk-cal-tight');
            const seen = parts.filter((p) => p.a.offsetParent);
            const range = document.createRange();
            root.classList.toggle('gk-cal-tight', seen.some((p) => {
                const node = p.nm.firstChild;
                if (!node || !node.length) return false;
                const end = node.data.search(/[ \t\n]/);
                range.setStart(node, 0); range.setEnd(node, end < 0 ? node.length : end);
                const tops = Array.from(range.getClientRects()).map((r) => r.top);
                return tops.length > 1 && Math.max.apply(null, tops) - Math.min.apply(null, tops) > 6;
            }));
            const rowH = new Map();
            seen.filter((p) => p.name.scrollHeight > p.name.clientHeight + 1 && !p.a.closest('.gk-cal-week-past')).forEach((p) => {
                const week = p.a.closest('.gk-cal-week');
                if (!week) return;
                if (!rowH.has(week)) rowH.set(week, week.getBoundingClientRect().height);
                const lh = parseFloat(getComputedStyle(p.name).lineHeight) || 18;
                const base = Math.round(p.name.clientHeight / lh);
                let kept = 0;
                for (let lines = base + 1; lines <= 4; lines++) {
                    p.name.style.maxHeight = (lines * lh + 0.5) + 'px';
                    if (week.getBoundingClientRect().height > rowH.get(week) + 0.5) break;
                    kept = lines;
                    if (p.name.scrollHeight <= p.name.clientHeight + 1) break;
                }
                p.name.style.maxHeight = kept ? (kept * lh + 0.5) + 'px' : '';
            });
            seen.filter((p) => p.name.scrollHeight > p.name.clientHeight + 1).forEach((p) => {
                const chars = Array.from(p.nm.dataset.full || '');
                const put = (k) => { p.nm.textContent = chars.slice(0, k).join('').replace(/[\s\u00A0.,:;(\[\-\u2013]+$/u, '') + '\u2026'; };
                let lo = 0, hi = chars.length - 1;
                while (lo < hi) {
                    const mid = Math.ceil((lo + hi) / 2);
                    put(mid);
                    if (p.name.scrollHeight > p.name.clientHeight + 1) hi = mid - 1; else lo = mid;
                }
                put(lo);
                p.nm.dataset.shown = lo;
                p.name.classList.add('gk-cal-name-cut');
            });
            const m = this.monthHands;
            // A day, a bar and an event carry a :class of their own, and when that changes (an
            // event ends, midnight passes) Vue writes the whole attribute: what the script had
            // added for an open card or panel is put back.
            if (m.cur && m.cur.el.isConnected) {
                if (m.cur.el.dataset.ev === m.cur.id) m.cur.el.classList.add('gk-cal-ev-on');
                const shown = this.filteredEvents.find((x) => String(x.id) === m.cur.id);
                if (shown) this.monthKin(shown, m.cur.date);
            }
            if (m.day && m.day.cell && m.day.cell.isConnected) m.day.cell.classList.add('gk-cal-day-open');
            this.monthPlace();
            this.monthPlaceDay();
            if (m.refocus && !this.isLoadingEvents) {
                m.refocus = false;
                // A month with nothing on it has no day to hold the focus: its own button, from
                // which Page Up and Page Down still work, or failing that the page's month buttons.
                const first = root.querySelector('.gk-cal-day:not(.gk-cal-day-out) button.gk-cal-num') || root.querySelector('button.gk-cal-num')
                    || root.querySelector('.gk-cal-empty-btn') || document.querySelector('#month-nav-controls button, #month-nav-controls a');
                if (first) first.focus({ preventScroll: true });
            }
        },

        /* ---- the card ---------------------------------------------------------------------- */

        // The event's other coming dates in the month that is shown.
        monthAlso(id, date) {
            const today = this.scheduleDay(0);
            return Object.keys(this.eventsMap).sort().filter((d) => d !== date && d >= today && (this.eventsMap[d] || []).includes(id));
        },

        // While a card is open the event's other dates are ringed: its own line where it has
        // one, else the "+N more" (or the number) it is behind.
        monthKin(e, date) {
            const root = this.$refs.monthRoot;
            if (!root) return;
            root.querySelectorAll('.gk-cal-kin').forEach((n) => n.classList.remove('gk-cal-kin'));
            if (!e) return;
            if (e.is_multi_day) {
                root.querySelectorAll('.gk-cal-span[data-ev="' + e.id + '"]').forEach((n) => n.classList.add('gk-cal-kin'));
                return;
            }
            const today = this.scheduleDay(0);
            root.querySelectorAll('.gk-cal-day').forEach((cell) => {
                const on = cell.dataset.date;
                if (on === date || on < today || !(this.eventsMap[on] || []).includes(e.id)) return;
                const chip = Array.from(cell.querySelectorAll('[data-ev="' + e.id + '"]')).find((n) => n.offsetParent);
                const mark = chip || cell.querySelector('.gk-cal-more') || cell.querySelector('.gk-cal-num');
                if (mark) mark.classList.add('gk-cal-kin');
            });
        },

        // The card's one button: what it does, and what it costs where it sells.
        // A filled button sells or signs up; View Event is always the quiet one.
        monthAction(e, f, url) {
            const withParam = (p) => url + (url.includes('?') ? '&' : '?') + p;
            const view = { label: L.view_event, href: url, kind: 'secondary', plain: true };
            // On the admin's pages the month is the owner's: edit what may be edited.
            if (!CAN.guest) return e.can_edit ? { label: L.edit_event, href: e.edit_url || url, kind: 'primary', plain: true, edit: true } : view;
            if (!url || f.locked || f.past || f.cancelled || f.soldOut) return view;
            if (e.rsvp_enabled) return { label: L.register, href: withParam('rsvp=true'), kind: 'primary', price: L.free };
            if (f.price || (f.freeTicket && !f.over)) return { label: f.freeTicket ? L.get_tickets : L.buy_tickets, href: withParam('tickets=true'), kind: 'primary', price: f.freeTicket ? L.free : f.price };
            return view;
        },

        // The .ics and the two calendars' own pages, from the event's address. Not from
        // getEventUrl(), which carries the list's filters as a query string.
        monthCalendarLinks(e, date) {
            const page = this.monthPage(e, date);
            if (!page) return null;
            return { apple: page + '/ical', google: page + '/ical?to=google', outlook: page + '/ical?to=outlook' };
        },

        // The event's own address, a series' with its night, and nothing else: getEventUrl()
        // carries the list's filters and layout, which is right for a way back and wrong for
        // an address somebody passes on. Empty where the event has no public page.
        monthPage(e, date) {
            if (!e.guest_url) return '';
            const series = !!(e.days_of_week && e.days_of_week.length);
            return String(e.guest_url).replace(/\/+$/, '') + (series && date ? '/' + date : '');
        },

        // Everything the card prints, as plain values (role/partials/month-peek).
        monthPeekModel(e, date) {
            const f = this.monthFacts(e, date);
            const lang = this.languageCode;
            // An event with no public page (on the dashboard, one on schedules nobody has claimed)
            // is given no address: built from an empty one, its links went to the page they were on.
            const url = e.guest_url ? this.getEventUrl(e, f.first) : null;
            const src = (!f.locked && (e.image_url || e.image_thumb_url || e.flyer_url)) || null;
            const d = day(f.first);
            const short = { weekday: 'short', month: 'short', day: 'numeric' };
            // The venue's own clock, named where the reader's is another.
            let zone = '';
            try {
                const here = Intl.DateTimeFormat().resolvedOptions().timeZone;
                if (e.zone && here && here !== e.zone) {
                    // Asked at noon UTC of the day, not at the reader's own midnight, which on a
                    // day the clocks change is the other side of the change for half the world.
                    const part = new Intl.DateTimeFormat(lang, { timeZone: e.zone, timeZoneName: 'short' }).formatToParts(new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate(), 12))).find((x) => x.type === 'timeZoneName');
                    if (part) zone = ' ' + part.value;
                }
            } catch (x) { /* an unknown zone names nothing */ }
            const when = [];
            let clock = '';
            if (f.span) {
                // Both ends in full: a start time alone on three days says nothing about the other two.
                when.push({ clock: false, text: d.toLocaleDateString(lang, short) + (f.start ? ', ' : '') });
                if (f.start) when.push({ clock: true, text: this.monthTime(f.start) });
                when.push({ clock: false, text: ' – ' + day(f.end ? f.endDay : f.last).toLocaleDateString(lang, short) + (f.end ? ', ' : '') });
                if (f.end) when.push({ clock: true, text: this.monthTime(f.end) + zone });
            } else {
                clock = f.start ? this.monthTime(f.start) + (f.end ? ' – ' + this.monthTime(f.end) : '') + zone : '';
                when.push({ clock: false, text: d.toLocaleDateString(lang, short) + (clock ? ' · ' : '') });
                if (clock) when.push({ clock: true, text: clock });
            }

            // One place for what state it is in: on the picture, or beside the date where there is none.
            const pills = [];
            if (f.internal) pills.push({ text: L.internal, cls: 'gk-peek-pill-warn' });
            if (f.draft) pills.push({ text: L.draft, cls: '' });
            if (f.cancelled) pills.push({ text: L.cancelled, cls: '' });
            else if (f.live) pills.push({ text: L.happening_now, cls: 'gk-peek-pill-live' });
            else if (!f.past && f.soldOut) pills.push({ text: L.sold_out, cls: '' });
            else if (!f.past && f.low) pills.push({ text: L.few_left, cls: 'gk-peek-pill-warn' });

            const go = this.monthAction(e, f, url);
            const chips = [];
            const free = { text: L.free, cls: 'gk-peek-chip-free' };
            if (CAN.guest && go.plain && !f.past && !f.cancelled && !f.soldOut) {
                // The button does not sell here, so the price is said on its own.
                if (f.elsewhere) {
                    chips.push(e.ticket_price == 0 ? free : { text: this.formatPrice(e.ticket_price, e.ticket_currency_code), cls: '' });
                    if (e.ticket_price != 0 && e.coupon_code) chips.push({ text: L.coupon + ': ' + e.coupon_code, cls: 'gk-peek-chip-accent' });
                } else if (f.price) chips.push({ text: f.price, cls: '' });
                else if (f.free) chips.push(free);
            }
            if (!CAN.guest && !f.past && !f.cancelled) {
                if (f.free) chips.push(free); else if (f.price) chips.push({ text: f.price, cls: '' });
            }

            // Its other dates, named, and one press away: what the rings in the month are about.
            const others = f.span ? [] : this.monthAlso(e.id, date);
            const sold = (x) => (e.sold_out_dates || []).includes(x);
            const also = others.slice(0, 4).map((x) => ({
                date: x, out: sold(x), label: day(x).toLocaleDateString(lang, { month: 'short', day: 'numeric' }),
                aria: sold(x) ? day(x).toLocaleDateString(lang, { month: 'long', day: 'numeric' }) + ', ' + L.sold_out : null,
            }));

            const ownVenue = !!(e.venue_subdomain && e.venue_subdomain === this.subdomain);
            const acts = (e.talent || []).filter((t) => t.guest_url !== null || (e.talent || []).length > 1);
            const links = CAN.calendar && !f.locked && !f.past && !f.cancelled ? this.monthCalendarLinks(e, f.first) : null;
            const sibs = this.monthOn(date);
            const at = sibs.findIndex((x) => x.id === e.id);
            return {
                id: e.id, date, url, name: this.getEventDisplayName(e), locked: f.locked,
                src, glow: src ? (e.image_thumb_url || src) : null,
                tall: !!(e.flyer_width && e.flyer_height && e.flyer_height > e.flyer_width * 1.12),
                tile: { month: d.toLocaleDateString(lang, { month: 'short' }), day: d.getDate(), weekday: d.toLocaleDateString(lang, { weekday: 'long' }), clock },
                span: f.span, word: this.dayWord(f.first), when, pills,
                where: e.venue_name && !f.locked && !ownVenue ? { name: e.venue_name, url: e.venue_guest_url || null } : null,
                online: !!(e.is_online && !e.venue_name),
                faces: f.locked ? [] : acts.filter((t) => t.profile_image).slice(0, 3).map((t) => t.profile_image),
                who: f.locked || !acts.length ? '' : acts.slice(0, 3).map((t) => t.name).join(', ') + (acts.length > 3 ? ' +' + (acts.length - 3) : ''),
                desc: e.short_description && !f.locked ? e.short_description : '', descDir: e.description_dir || e.dir || 'auto',
                also, alsoMore: Math.max(0, others.length - 4), chips,
                go, details: go.plain ? null : url,
                view: !CAN.guest && e.can_edit ? url : null,
                edit: CAN.guest && e.can_edit ? e.edit_url : null,
                links, share: CAN.share && !f.draft && !f.internal && !!e.guest_url, page: this.monthPage(e, f.first), menu: false, copied: false,
                step: sibs.length > 1 ? {
                    label: day(date).toLocaleDateString(lang, short) + ' · ' + say(L.n_of, { a: at + 1, b: sibs.length }),
                    prev: at > 0, next: at < sibs.length - 1,
                } : null,
            };
        },

        monthShow(el, opts) {
            opts = opts || {};
            const m = this.monthHands;
            const peek = this.$refs.monthPeekEl;
            const id = String(opts.id || el.dataset.ev), date = opts.date || el.dataset.date;
            const e = this.filteredEvents.find((x) => String(x.id) === id);
            if (!e || !peek) return;
            const was = !!m.cur;
            if (m.cur) m.cur.el.classList.remove('gk-cal-ev-on');
            m.cur = { id, date, el };
            m.pinned = !!opts.pin; m.held = false;
            if (el.dataset.ev === id) el.classList.add('gk-cal-ev-on');
            this.monthKin(e, date);
            this.monthPeek = this.monthPeekModel(e, date);
            this.$nextTick(() => {
                if (!m.cur || m.cur.el !== el || m.cur.id !== id) return;
                peek.classList.toggle('gk-peek-pinned', m.pinned);
                peek.classList.toggle('gk-peek-first', !was);
                peek.classList.remove('gk-peek-swap');
                if (was) { void peek.offsetWidth; peek.classList.add('gk-peek-swap'); }
                this.monthPlace();
                // Placing it found its event gone (a redraw under a resting pointer): nothing to show.
                if (!m.cur) return;
                peek.classList.add('gk-peek-shown');
                if (opts.focus) { const again = peek.querySelector('.gk-peek-title a'); if (again) again.focus({ preventScroll: true }); }
            });
        },

        // A picture that turns out to be a portrait flyer changes the card's height.
        monthPeekImg(ev) {
            const img = ev.target;
            if (img.naturalHeight > img.naturalWidth * 1.12) { img.parentNode.classList.add('gk-peek-media-tall'); this.monthPlace(); }
        },

        // Beside the day, never over it, and as wide as whole days so no name is cut down its
        // middle. Before the day where that many days stand there (what has been read gives
        // way, what is still to come stays in sight); after it for the first days of a week.
        monthPlace() {
            const m = this.monthHands;
            const peek = this.$refs.monthPeekEl, root = this.$refs.monthRoot;
            if (!m || !m.cur || !peek || !root) return;
            const el = m.cur.el;
            if (!el.isConnected || !el.offsetParent) return this.monthHide(true);
            const card = peek.querySelector('.gk-peek-card');
            if (!card) return;
            const cell = el.closest('.gk-cal-day') || el;
            const c = cell.getBoundingClientRect();
            const chip = el.getBoundingClientRect();
            const vw = document.documentElement.clientWidth, vh = window.innerHeight;
            const gap = 10, edge = 8;
            const colW = c.width;
            const cols = Math.min(3, Math.max(2, Math.ceil(288 / colW)));
            const W = Math.min(416, Math.max(280, Math.round(cols * colW - gap)));
            peek.style.width = W + 'px';
            peek.classList.toggle('gk-peek-compact', colW < NARROW);
            // Beside laptop-sized days the price is always under the button's words, whatever the
            // words are; on a wider card it goes under only when the two will not share a line.
            const goBtn = peek.querySelector('.gk-peek-go');
            const goPrice = goBtn && goBtn.querySelector('.gk-peek-go-price');
            if (goPrice) {
                goBtn.classList.remove('gk-peek-go-2');
                goBtn.classList.toggle('gk-peek-go-2', (W < 336 && peek.querySelectorAll('.gk-peek-actions > *').length > 1) || goPrice.offsetTop > goBtn.querySelector('.gk-peek-go-l').offsetTop + 4);
            }
            card.style.minHeight = '';
            let real = peek.offsetHeight;
            const H = real;
            // A bar over several days is docked outside the whole of it.
            const span = el.classList.contains('gk-cal-span');
            const first = Number(cell.dataset.col || 0);
            const lastCol = span ? Math.min(6, first + Number(el.dataset.cols || 1) - 1) : first;
            const rtl = this.isRtl;
            const leftX = span ? Math.min(chip.left, chip.right) : c.left;
            const rightX = span ? Math.max(chip.left, chip.right) : c.right;
            const daysLeft = rtl ? 6 - lastCol : first;
            const daysRight = rtl ? first : 6 - lastCol;
            // "Before" in reading order is the left in a left-to-right month and the right otherwise.
            const beforeIsLeft = !rtl;
            const daysBefore = beforeIsLeft ? daysLeft : daysRight, daysAfter = beforeIsLeft ? daysRight : daysLeft;
            const wantBefore = daysBefore >= cols || (daysAfter < cols && daysBefore >= daysAfter);
            let side = (wantBefore === beforeIsLeft) ? 'left' : 'right';
            let x = side === 'left' ? leftX - gap - W : rightX + gap;
            if (x < edge || x + W > vw - edge) {
                const other = side === 'left' ? rightX + gap : leftX - gap - W;
                if (other >= edge && other + W <= vw - edge) { x = other; side = side === 'left' ? 'right' : 'left'; } else { side = 'over'; x = Math.max(edge, Math.min(c.left, vw - W - edge)); }
            }
            // Kept under whatever of the page stays at the top of the window while it scrolls.
            const roof = this.monthRoof(edge);
            let y;
            if (side === 'over') {
                y = chip.bottom + 8; if (y + H > vh - edge) y = Math.max(roof, chip.top - H - 8);
            } else {
                // Its foot on the week's foot, whatever it has to say: the button stays where it
                // is while the pointer runs down a day, and the card grows up, over the weeks
                // behind. It never leaves the weeks: beside a month's first weeks, where it would
                // rise over the weekday names, it hangs from its own week's top line and grows down.
                const weeksTop = root.querySelector('.gk-cal-weeks').getBoundingClientRect().top;
                y = c.bottom - H + 2;
                if (y < weeksTop) y = Math.max(weeksTop, c.top) - 1;
                y = Math.max(roof, Math.min(y, vh - H - edge));
                if (chip.top + chip.height / 2 < y + 18) y = Math.max(roof, chip.top - 18);
                if (chip.top + chip.height / 2 > y + H - 18) y = Math.min(vh - H - edge, chip.bottom + 18 - H);
                // A top edge inside a week's row of numbers would leave half a number at each
                // corner: the card is stretched up to that week's own line.
                for (const w of root.querySelectorAll('.gk-cal-week')) {
                    const wr = w.getBoundingClientRect();
                    const head = w.querySelector('.gk-cal-dayhead');
                    const band = head ? head.getBoundingClientRect().bottom + 4 : wr.top + 32;
                    if (y > wr.top - 3 && y < band) {
                        const lift = Math.min(y - roof, y - (wr.top - 1));
                        if (lift > 0) { card.style.minHeight = Math.round(real + lift) + 'px'; y -= lift; real += lift; }
                        break;
                    }
                }
            }
            const cy = Math.max(16, Math.min(real - 28, chip.top + chip.height / 2 - y - 6));
            const media = peek.querySelector('.gk-peek-media');
            peek.classList.toggle('gk-peek-nocaret', !!media && cy < media.offsetHeight + 4);
            peek.dataset.side = side;
            peek.style.setProperty('--peek-cy', cy + 'px');
            peek.style.setProperty('--peek-ox', side === 'left' ? '100%' : '0px');
            peek.style.setProperty('--peek-oy', cy + 'px');
            peek.style.setProperty('--peek-from', side === 'left' ? '.375rem' : '-.375rem');
            peek.style.transform = 'translate3d(' + Math.round(x) + 'px,' + Math.round(y) + 'px,0)';
            m.side = side;
            // A bar that starts under the card and comes out the far side would show the tail
            // of its name: it goes without its words while the card stands on it.
            const box = { l: Math.round(x), r: Math.round(x) + W, t: y, b: y + real };
            root.querySelectorAll('.gk-cal-span').forEach((s) => {
                const r = s.getBoundingClientRect();
                const startX = rtl ? r.right - 12 : r.left + 12;
                const under = startX > box.l && startX < box.r && r.top < box.b && r.bottom > box.t;
                const out = r.left < box.l - 4 || r.right > box.r + 4;
                s.classList.toggle('gk-cal-span-veiled', under && out && s !== el);
            });
        },

        // How far down the window the page's own bar reaches: the admin's is sticky, a guest
        // page's is fixed and slides away (out of sight, its foot is above the window).
        monthRoof(edge) {
            let roof = edge;
            document.querySelectorAll('[data-month-top], .gk-headbar').forEach((bar) => {
                const stays = getComputedStyle(bar).position;
                if (stays !== 'sticky' && stays !== 'fixed') return;
                const r = bar.getBoundingClientRect();
                if (r.height && r.top <= edge && r.bottom > 0) roof = Math.max(roof, r.bottom + edge);
            });
            return roof;
        },

        // Focus given back to an event when its card closes: without opening the card again.
        monthBack(el) {
            if (!el || !el.isConnected) return;
            const m = this.monthHands;
            m.mute = el; el.focus({ preventScroll: true }); m.mute = null;
        },

        monthHide() {
            const m = this.monthHands;
            const peek = this.$refs.monthPeekEl, root = this.$refs.monthRoot;
            if (!m) return;
            clearTimeout(m.timer);
            if (!m.cur || !peek) return;
            m.cur.el.classList.remove('gk-cal-ev-on');
            this.monthKin(null);
            const back = peek.contains(document.activeElement) ? m.cur.el : null;
            m.cur = null; m.pinned = false; m.held = false;
            if (root) root.querySelectorAll('.gk-cal-span-veiled').forEach((s) => s.classList.remove('gk-cal-span-veiled'));
            peek.classList.remove('gk-peek-shown', 'gk-peek-pinned', 'gk-peek-swap');
            if (this.monthPeek) this.monthPeek.menu = false;
            this.monthBack(back);
        },

        // Hover intent: an event the pointer rests on is wanted; nothing is wanted when it leaves.
        monthWant(el, delay) {
            const m = this.monthHands;
            clearTimeout(m.timer);
            if (el && m.cur && m.cur.el === el) return;
            if (!el) { if (!m.pinned) m.timer = setTimeout(() => this.monthHide(), delay != null ? delay : CLOSE_MS); return; }
            if (m.pinned) return;
            const toward = m.cur && m.side !== 'over' && ((m.side === 'right' ? 1 : -1) * m.last.dx > Math.abs(m.last.dy) * 0.6) && Math.abs(m.last.dx) > 1.5;
            const ms = delay != null ? delay : (m.cur ? (toward ? TOWARD_MS : SWITCH_MS) : OPEN_MS);
            m.timer = setTimeout(() => this.monthShow(el), ms);
        },

        /* The card's own buttons. */
        monthPeekClose() { const m = this.monthHands; const el = m.cur && m.cur.el; this.monthHide(); this.monthBack(el); },
        monthPeekStep(dir) {
            const m = this.monthHands;
            if (!m.cur) return;
            const sibs = this.monthOn(m.cur.date);
            const nx = sibs[sibs.findIndex((x) => String(x.id) === m.cur.id) + dir];
            if (!nx) return;
            // An event under "+N more" has no line of its own: the card stays where it is.
            const el = Array.from(this.$refs.monthRoot.querySelectorAll('[data-ev="' + nx.id + '"][data-date="' + m.cur.date + '"]')).find((n) => n.offsetParent);
            this.monthShow(el || m.cur.el, { pin: true, id: nx.id, date: m.cur.date });
        },
        // A date named in the card: the card goes to it.
        monthPeekGo(date, ev) {
            const m = this.monthHands;
            if (!m.cur) return;
            const cell = this.$refs.monthRoot.querySelector('.gk-cal-day[data-date="' + date + '"]');
            const el = cell && (Array.from(cell.querySelectorAll('[data-ev="' + m.cur.id + '"]')).find((n) => n.offsetParent) || cell.querySelector('.gk-cal-more') || cell.querySelector('.gk-cal-num'));
            const byKey = ev.detail === 0;
            // Pinned as it was before the calendar menu held it: the menu's hold is not a pin.
            if (el) this.monthShow(el, { pin: (m.held ? !!m.wasPinned : m.pinned) || byKey, id: m.cur.id, date, focus: byKey });
        },
        monthPeekMenu(ev) {
            const m = this.monthHands;
            if (!this.monthPeek) return;
            this.monthPeek.menu = !this.monthPeek.menu;
            // Held while the menu is open, and as it was once it is closed: left pinned, the
            // card stayed for good and no other event's would open.
            if (this.monthPeek.menu) { if (!m.held) { m.held = true; m.wasPinned = m.pinned; } m.pinned = true; } else if (m.held) { m.held = false; m.pinned = !!m.wasPinned; }
            if (this.monthPeek.menu) this.$nextTick(() => { const a = this.$refs.monthPeekEl.querySelector('.gk-peek-menu a'); if (a) a.focus({ preventScroll: true }); });
        },
        // One of the menu's links was pressed: the menu closes, and the focus goes to its button
        // and not to nowhere (the link it was on has just been hidden).
        monthPeekPick() {
            if (!this.monthPeek || !this.monthPeek.menu) return;
            this.monthPeekMenu();
            const b = this.$refs.monthPeekEl.querySelector('[data-peek-cal]');
            if (b) b.focus({ preventScroll: true });
        },
        monthPeekShare() {
            const p = this.monthPeek;
            if (!p) return;
            if (!p.page) return;
            const url = new URL(p.page, window.location.href).href;
            const done = () => { p.copied = true; setTimeout(() => { p.copied = false; }, 1600); };
            // The Clipboard API exists only on a secure page: a selfhosted install on plain http
            // copies through a hidden field, as the list's Copy link does. "Copied" is said only
            // when something was.
            const fallback = () => {
                // Selecting the field takes the focus out of the card, which a card that is not
                // pinned answers by closing: it is held meanwhile, and the focus is given back.
                const m = this.monthHands, was = m.pinned, back = document.activeElement;
                m.pinned = true;
                const field = document.createElement('textarea');
                field.value = url;
                field.setAttribute('readonly', '');
                field.style.position = 'fixed';
                field.style.opacity = '0';
                document.body.appendChild(field);
                field.select();
                try { if (document.execCommand('copy')) done(); } catch (err) { /* nothing was copied, nothing is said */ }
                document.body.removeChild(field);
                if (back && back.focus) back.focus({ preventScroll: true });
                m.pinned = was;
            };
            if (navigator.share && !fineHover()) navigator.share({ title: p.name, url }).catch(() => {});
            else if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(url).then(done).catch(fallback);
            else fallback();
        },
        monthPeekLeave(ev) { if (ev.pointerType === 'mouse') this.monthWant(null); },
        monthPeekEnter() { clearTimeout(this.monthHands.timer); },
        monthPeekFocusOut(ev) {
            const m = this.monthHands, peek = this.$refs.monthPeekEl;
            if (!peek.contains(ev.relatedTarget) && !(m.cur && m.cur.el === ev.relatedTarget) && !m.pinned) this.monthWant(null, 80);
        },
        monthPeekKey(ev) {
            const m = this.monthHands, peek = this.$refs.monthPeekEl, root = this.$refs.monthRoot;
            if (ev.key !== 'Tab' || !m.cur) return;
            const f = Array.from(peek.querySelectorAll('a[href]:not([tabindex="-1"]), button:not([disabled])')).filter((n) => n.offsetParent);
            const i = f.indexOf(document.activeElement);
            if (ev.shiftKey && i <= 0) { ev.preventDefault(); m.cur.el.focus(); } else if (!ev.shiftKey && i === f.length - 1) {
                // Out of the card and on to whatever follows the event it belongs to.
                ev.preventDefault();
                const all = Array.from(root.querySelectorAll('.gk-cal-span, .gk-cal-ev, .gk-cal-more, button.gk-cal-num')).filter((n) => n.offsetParent);
                const next = all[all.indexOf(m.cur.el) + 1];
                const el = m.cur.el; this.monthHide(); (next || el).focus();
            }
        },

        /* ---- the day's panel --------------------------------------------------------------- */

        // The nearest day before or after that has anything on it, among the days the month shows.
        monthDayBeside(date, dir) {
            const days = this.monthWeeks.flatMap((w) => w.days).filter((d) => d.count).map((d) => d.date);
            const at = days.indexOf(date);
            return at === -1 ? null : (days[at + dir] || null);
        },

        // Everything the panel prints, as plain values (role/partials/month-peek).
        monthDayModel(date) {
            const lang = this.languageCode;
            const root = this.$refs.monthRoot;
            const list = this.monthOn(date);
            const rows = list.map((e) => {
                const f = this.monthFacts(e, date);
                const note = this.monthNote(e, f);
                const own = !!(e.venue_subdomain && e.venue_subdomain === this.subdomain);
                const sub = [];
                if (f.live) sub.push({ k: 'now', venue: false, cls: 'gk-cal-now', text: L.now });
                if (e.venue_name && !f.locked && !own) sub.push({ k: 'venue', venue: true, cls: '', text: e.venue_name });
                if (note) sub.push({ k: 'note', venue: false, cls: 'gk-dayp-note' + (note[0] ? ' gk-cal-note-' + note[0] : ''), text: note[1] });
                if (f.internal) sub.push({ k: 'internal', venue: false, cls: 'gk-dayp-note gk-cal-note-warn', text: L.internal });
                if (f.draft) sub.push({ k: 'draft', venue: false, cls: 'gk-dayp-note', text: L.draft });
                return {
                    key: e.id + '|' + date, id: e.id, url: e.guest_url ? this.getEventUrl(e, f.first) : null, cls: (f.past ? 'gk-dayp-row-past ' : '') + (f.cancelled ? 'gk-dayp-row-off' : ''),
                    time: f.span && f.first < date ? '…' : this.monthTime(f.start), dot: this.getEventDotColor(e),
                    name: this.getEventDisplayName(e), locked: f.locked, sub,
                    img: (!f.locked && (e.image_thumb_url || e.image_url || e.flyer_url)) || null,
                };
            });
            // In short where the panel is narrow.
            const brief = root && root.clientWidth / 7 < 176;
            return {
                date, word: this.dayWord(date), rows,
                title: day(date).toLocaleDateString(lang, brief ? { weekday: 'short', month: 'short', day: 'numeric' } : { weekday: 'long', month: 'long', day: 'numeric' }),
                count: L.events + ': ' + list.length,
                prev: !!this.monthDayBeside(date, -1), next: !!this.monthDayBeside(date, 1),
                away: AWAY.filter((member) => member.dates.includes(date)).map((member) => member.name).join(', '),
                add: CAN.add ? CAN.add.replace('MONTH-DATE', date) : null,
            };
        },

        monthOpenDay(date, opener, byKey, focusStep) {
            const m = this.monthHands;
            const root = this.$refs.monthRoot, panel = this.$refs.monthDayEl;
            if (!root || !panel) return;
            this.monthHide();
            this.monthDayPanel = this.monthDayModel(date);
            const cell = root.querySelector('.gk-cal-day[data-date="' + date + '"]');
            m.day = { date, opener, cell };
            if (cell) {
                cell.classList.add('gk-cal-day-open');
                cell.querySelectorAll('[data-day-open]').forEach((b) => b.setAttribute('aria-expanded', 'true'));
            }
            this.$nextTick(() => {
                if (!m.day || m.day.date !== date) return;
                this.monthPlaceDay();
                void panel.offsetWidth;
                panel.classList.add('gk-dayp-shown');
                // The focus goes in however it was opened: the panel stands at the foot of the
                // page's markup, and a screen reader's own press is not always told from a pointer's.
                const to = focusStep ? (panel.querySelector('[data-day-step="' + focusStep + '"]:not([disabled])') || panel.querySelector('[data-day-close]')) : (panel.querySelector('.gk-dayp-row') || panel.querySelector('[data-day-close]'));
                if (to) to.focus({ preventScroll: true });
            });
        },

        // Over its own day: from that day's own line and exactly two days wide (three where days
        // are narrow), so it never ends in the middle of a neighbour. Kept inside the month.
        monthPlaceDay() {
            const m = this.monthHands;
            const root = this.$refs.monthRoot, panel = this.$refs.monthDayEl;
            if (!m || !m.day || !root || !panel) return;
            if (!m.day.cell || !m.day.cell.isConnected || !m.day.cell.offsetParent) return this.monthCloseDay(true);
            const c = m.day.cell.getBoundingClientRect();
            const grid = root.getBoundingClientRect();
            const vw = document.documentElement.clientWidth, vh = window.innerHeight, edge = 12;
            const cols = c.width < NARROW ? 3 : 2;
            const W = Math.min(Math.round(cols * c.width), vw - 2 * edge);
            panel.style.width = W + 'px';
            const H = panel.offsetHeight;
            let x = this.isRtl ? c.right - W : c.left;
            x = Math.max(grid.left, Math.min(x, grid.right - W));
            x = Math.max(edge, Math.min(x, vw - W - edge));
            let y = c.top;
            if (H <= grid.height) y = Math.max(grid.top, Math.min(y, grid.bottom - H));
            y = Math.max(this.monthRoof(edge), Math.min(y, vh - H - edge));
            panel.style.setProperty('--dayp-ox', Math.round((this.isRtl ? c.right - 20 : c.left + 20) - x) + 'px');
            panel.style.setProperty('--dayp-oy', Math.round(c.top + 16 - y) + 'px');
            panel.style.transform = 'translate3d(' + Math.round(x) + 'px,' + Math.round(y) + 'px,0)';
        },

        monthCloseDay(quiet) {
            const m = this.monthHands, panel = this.$refs.monthDayEl;
            if (!m || !m.day) return;
            const o = m.day; m.day = null;
            if (panel) panel.classList.remove('gk-dayp-shown');
            if (o.cell) {
                o.cell.classList.remove('gk-cal-day-open');
                o.cell.querySelectorAll('[data-day-open]').forEach((b) => b.setAttribute('aria-expanded', 'false'));
            }
            if (!quiet && o.opener && o.opener.isConnected && panel && panel.contains(document.activeElement)) o.opener.focus({ preventScroll: true });
        },

        monthDayStep(dir) {
            const m = this.monthHands;
            if (!m.day) return;
            const to = this.monthDayBeside(m.day.date, dir);
            const opener = m.day.opener;
            if (to) { this.monthCloseDay(true); this.monthOpenDay(to, opener, false, String(dir)); }
        },
        monthDayKey(ev) {
            if (ev.key === 'Tab') {
                // Past either end of the panel it closes, and the focus is back on what opened
                // it: left open, it stood over the month with the focus somewhere behind it.
                const stops = Array.from(this.$refs.monthDayEl.querySelectorAll('a[href], button:not([disabled])')).filter((n) => n.offsetParent);
                const at = stops.indexOf(document.activeElement);
                if (ev.shiftKey ? at <= 0 : at === stops.length - 1) { ev.preventDefault(); this.monthCloseDay(); }
                return;
            }
            const rows = Array.from(this.$refs.monthDayEl.querySelectorAll('.gk-dayp-row'));
            const i = rows.indexOf(document.activeElement);
            if (ev.key === 'ArrowDown' && rows[i + 1]) { ev.preventDefault(); rows[i + 1].focus(); }
            if (ev.key === 'ArrowUp' && rows[i - 1]) { ev.preventDefault(); rows[i - 1].focus(); }
        },

        /* ---- hands: what the month itself hears -------------------------------------------- */

        // One of the month's own words, for a binding that picks between two.
        monthL(key) { return L[key]; },
        monthChipOf(n) { return n && n.closest ? n.closest('.gk-cal-ev, .gk-cal-span') : null; },
        monthStops(cell) { return Array.from(cell.querySelectorAll('.gk-cal-span, .gk-cal-ev, .gk-cal-more')).filter((n) => n.offsetParent); },

        monthOver(ev) {
            if (ev.pointerType !== 'mouse') return;
            const el = this.monthChipOf(ev.target);
            if (el && !(ev.relatedTarget && el.contains(ev.relatedTarget))) this.monthWant(el);
        },
        monthOut(ev) {
            if (ev.pointerType !== 'mouse') return;
            const el = this.monthChipOf(ev.target);
            if (el && !(ev.relatedTarget && el.contains(ev.relatedTarget)) && !this.monthChipOf(ev.relatedTarget)) this.monthWant(null);
        },
        // The keyboard sees what the pointer sees: focus opens the card at once.
        monthFocusIn(ev) {
            const m = this.monthHands;
            const el = this.monthChipOf(ev.target);
            if (el && el !== m.mute && el.matches(':focus-visible')) { clearTimeout(m.timer); this.monthShow(el); }
        },
        monthFocusOut(ev) {
            const m = this.monthHands, peek = this.$refs.monthPeekEl;
            if (this.monthChipOf(ev.target) && !(peek && peek.contains(ev.relatedTarget)) && !this.monthChipOf(ev.relatedTarget) && !m.pinned) this.monthWant(null, 80);
        },
        monthClick(ev) {
            const m = this.monthHands;
            const open = ev.target.closest('[data-day-open]');
            if (open) {
                ev.preventDefault();
                const same = m.day && m.day.date === open.dataset.dayOpen;
                this.monthCloseDay(true);
                if (!same) this.monthOpenDay(open.dataset.dayOpen, open, ev.detail === 0);
                return;
            }
            const el = this.monthChipOf(ev.target);
            if (!el) return;
            // A finger has no hover: the first tap shows the card, the card's own button goes on.
            if ((m.pointer === 'touch' || m.pointer === 'pen' || !fineHover()) && !(m.cur && m.cur.el === el)) {
                ev.preventDefault(); clearTimeout(m.timer); this.monthShow(el, { pin: true });
                return;
            }
            // Into the event: one of the guest pages' daily counts, as a press on a row of the list is.
            this.countListTap();
        },
        monthKey(ev) {
            const m = this.monthHands, root = this.$refs.monthRoot, peek = this.$refs.monthPeekEl;
            // From anywhere in the month, an empty month's own button included: that is where
            // the focus is left when a month with nothing on it arrives.
            if (ev.key === 'PageDown' || ev.key === 'PageUp') { ev.preventDefault(); this.monthTurn(ev.key === 'PageDown' ? 1 : -1); return; }
            const el = ev.target.closest('.gk-cal-span, .gk-cal-ev, .gk-cal-more, .gk-cal-num');
            if (!el) return;
            const cell = el.closest('.gk-cal-day');
            if (ev.key === 'Tab' && !ev.shiftKey && m.cur && m.cur.el === el) {
                const first = peek && peek.querySelector('.gk-peek-title a');
                if (first) { ev.preventDefault(); m.pinned = false; first.focus({ preventScroll: true }); }
                return;
            }
            const move = { ArrowDown: [0, 1], ArrowUp: [0, -1], ArrowRight: [this.isRtl ? -1 : 1, 0], ArrowLeft: [this.isRtl ? 1 : -1, 0] }[ev.key];
            if (!move) return;
            const cells = Array.from(root.querySelectorAll('.gk-cal-day'));
            const ci = cells.indexOf(cell);
            const stops = this.monthStops(cell);
            const si = stops.indexOf(el);
            let target = null;
            if (move[1]) {
                if (si < 0 && move[1] > 0 && stops.length) target = stops[0];
                else if (si >= 0 && stops[si + move[1]]) target = stops[si + move[1]];
                else {
                    // The same weekday above or below, past any day with nothing on it.
                    for (let i = ci + move[1] * 7; i >= 0 && i < cells.length; i += move[1] * 7) {
                        const s = this.monthStops(cells[i]);
                        if (s.length) { target = move[1] > 0 ? s[0] : s[s.length - 1]; break; }
                    }
                }
            } else {
                for (let i = ci + move[0]; i >= 0 && i < cells.length; i += move[0]) {
                    const s = this.monthStops(cells[i]);
                    if (s.length) { target = s[Math.min(Math.max(si, 0), s.length - 1)]; break; }
                }
            }
            if (target && target.focus) { ev.preventDefault(); target.focus(); }
        },
        // Straight to the month a date is in (an empty month's way to the next event).
        monthGoTo(date) {
            this.monthHands.refocus = true;
            const [y, mo] = date.split('-').map(Number);
            this.pageMonth = mo === 1 ? 12 : mo - 1;
            this.pageYear = mo === 1 ? y - 1 : y;
            this.navigateMonth(1);
        },
        // The month before or after. A guest's page changes month in place; the admin's pages
        // do it by their own two links, so those are followed.
        monthTurn(dir) {
            // The key that turned the month came from inside it, and what held the focus is
            // about to go: the new month is given it back once it is drawn (monthFit()).
            this.monthHands.refocus = true;
            if (CAN.guest) return this.navigateMonth(dir);
            const link = document.querySelector('#month-nav-controls a:' + (dir > 0 ? 'last-of-type' : 'first-of-type'));
            if (link) link.click();
        },
    },

    watch: {
        // The month was drawn again (another month, a filter, the minute turning): fit it again.
        // A card or a panel whose day is gone closes itself in the placing that follows.
        // Fitted once everything of that draw is in the page, the names v-clamp prints included.
        monthWeeks: { handler() { this.$nextTick(() => this.monthFit()); }, flush: 'post' },
        // Which way the month turned, for the side its weeks come in from.
        monthYearDatetime(now, was) { this.monthCame = now > was ? 'gk-cal-weeks-next' : 'gk-cal-weeks-prev'; },
        // A month that is on its way has no days to stand beside: the card and the panel close.
        isLoadingEvents(now) { if (now) { this.monthHide(); this.monthCloseDay(true); } },
    },

    created() {
        // What the hands hold between events. Not reactive: none of it is printed.
        this.monthHands = { cur: null, pinned: false, timer: 0, side: '', last: { x: 0, y: 0, dx: 0, dy: 0 }, pointer: 'mouse', mute: null, day: null, off: [] };
    },

    mounted() {
        const m = this.monthHands, root = this.$refs.monthRoot;
        if (!root) return;
        const on = (target, type, fn, opt) => { target.addEventListener(type, fn, opt); m.off.push(() => target.removeEventListener(type, fn, opt)); };
        on(document, 'pointerdown', (ev) => {
            m.pointer = ev.pointerType || 'mouse';
            const peek = this.$refs.monthPeekEl, panel = this.$refs.monthDayEl;
            if (m.cur && !(peek && peek.contains(ev.target)) && !this.monthChipOf(ev.target)) this.monthHide();
            if (m.day && !(panel && panel.contains(ev.target)) && !(peek && peek.contains(ev.target)) && !ev.target.closest('[data-day-open]')) this.monthCloseDay(true);
        }, true);
        on(document, 'pointermove', (ev) => { m.last = { x: ev.clientX, y: ev.clientY, dx: ev.clientX - m.last.x, dy: ev.clientY - m.last.y }; }, { passive: true });
        on(document, 'keydown', (ev) => {
            if (ev.key !== 'Escape') return;
            if (this.monthPeek && this.monthPeek.menu) {
                this.monthPeekMenu();
                const b = this.$refs.monthPeekEl.querySelector('[data-peek-cal]'); if (b) b.focus();
                return;
            }
            if (m.cur) { this.monthHide(); return; }
            if (m.day) this.monthCloseDay();
        });
        let raf = 0;
        const again = () => { cancelAnimationFrame(raf); raf = requestAnimationFrame(() => { this.monthPlace(); this.monthPlaceDay(); }); };
        on(window, 'scroll', again, { passive: true, capture: true });
        on(window, 'resize', again);
        // A name that fits at one width may not at another, and a month that was out of sight
        // (the list was showing, the events were loading) has a width only once it is shown.
        if (window.ResizeObserver) {
            let seen = 0, fit = 0;
            const ro = new ResizeObserver(() => {
                const w = root.clientWidth;
                if (w === seen) return;
                seen = w;
                cancelAnimationFrame(fit);
                fit = requestAnimationFrame(() => this.monthFit());
            });
            ro.observe(root);
            m.off.push(() => ro.disconnect());
        }
        const tick = setInterval(() => { this.monthTick++; }, 60000);
        m.off.push(() => clearInterval(tick));
        this.$nextTick(() => this.monthFit());
    },

    beforeUnmount() {
        (this.monthHands.off || []).forEach((off) => off());
    },
};
})();

// A name is put on the page as text, and never again unless it changes: monthFit() shortens
// what is on the page, and a redraw for any other reason must leave that alone.
window.monthClamp = {
    // Before the element is put in, and before it is updated: a watcher that waits for the
    // draw (`flush: 'post'`) runs AHEAD of a directive's mounted and updated hooks of the same
    // draw, so with those the month was fitted around names that were not there yet.
    beforeMount(el, binding) {
        el.textContent = binding.value == null ? '' : String(binding.value);
        el.dataset.full = el.textContent;
    },
    beforeUpdate(el, binding) {
        if (binding.value === binding.oldValue) return;
        el.textContent = binding.value == null ? '' : String(binding.value);
        el.dataset.full = el.textContent;
        delete el.dataset.shown;
    },
};
</script>
