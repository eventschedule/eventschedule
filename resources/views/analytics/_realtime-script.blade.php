{{-- The Realtime tab's behaviour (analytics/_realtime): one small Vue app over two answers.

     The traffic (RealtimeController::data()) is polled every 15 seconds while the tab is visible
     and every 60 while it is not; it gives up after half an hour hidden (and picks up again when
     the tab comes back), and stops for good when the server says the page is no longer theirs to
     see. It carries the marks on the chart and the checkout figures, which are two cheap reads.

     The Activity rail and the door card (RealtimeController::activity()) are heavier and change
     more slowly, so they are asked for once a minute by the clock, and at once when a mark lands
     on the chart, which is the one moment the rail is known to be stale. Never while the tab is
     hidden. An answer says which schedule it is for and one that does not match is dropped; a
     traffic answer never touches the rail.

     The chart is kept outside Vue's data on purpose: a reactive proxy around a Chart.js instance
     breaks its internals. --}}
<script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function () {
        var MSG = @json($scheduleRealtimeMsg);
        var CONFIG = @json($scheduleRealtimeConfig);
        var INITIAL = @json($payload);
        var ACTIVITY = @json($activity);
        var ICONS = @json($deviceIcons);
        var ACTIVITY_ICONS = @json($activityIcons);
        var ACTIVITY_TONES = @json($activityTones);
        var LOCALE = CONFIG.locale;

        var VISIBLE_MS = 15000, HIDDEN_MS = 60000, GIVE_UP_HIDDEN_MS = 30 * 60 * 1000;
        // The rail is due "every minute", and it is asked about on the traffic poll's tick. An
        // answer lands a moment after its tick, so a bare 60000 was never due on the fourth tick
        // and every minute was 75 seconds.
        var ACTIVITY_MS = 55000;
        // An answer that has not come in this long is not coming: without a limit a hung request
        // left the page saying "Live" over numbers that had stopped.
        var FETCH_TIMEOUT_MS = 20000;
        // The longest a list waits for a pointer or the keyboard to leave it (see hold).
        var HOLD_MS = 60000;

        var numberFormat = new Intl.NumberFormat(LOCALE + '-u-nu-latn');
        var relative = null, relativeExact = null, regionNames = null;
        try { relative = new Intl.RelativeTimeFormat(LOCALE, { numeric: 'auto', style: 'short' }); } catch (e) {}
        try { relativeExact = new Intl.RelativeTimeFormat(LOCALE, { numeric: 'always', style: 'short' }); } catch (e) {}
        try { regionNames = new Intl.DisplayNames([LOCALE], { type: 'region' }); } catch (e) {}

        // One formatter to a unit, kept: the second hand asks for these for every row listed.
        var unitFormats = {};
        function unit(value, name, display) {
            var key = name + '|' + (display || 'narrow');
            try {
                if (!unitFormats[key]) unitFormats[key] = new Intl.NumberFormat(LOCALE + '-u-nu-latn', { style: 'unit', unit: name, unitDisplay: display || 'narrow' });
                return unitFormats[key].format(value);
            } catch (e) {
                return value + ' ' + name;
            }
        }

        // Colours are read from the live palette, never hardcoded: the theme picker switches
        // between six palettes without a reload.
        function probe(className, property) {
            var el = document.createElement('span');
            el.className = className;
            el.style.display = 'none';
            document.body.appendChild(el);
            var value = getComputedStyle(el)[property];
            el.remove();
            return value;
        }
        function chartColors() {
            return {
                blue: getComputedStyle(document.documentElement).getPropertyValue('--brand-blue').trim() || '#4E81FA',
                grid: probe('border-gray-200 dark:border-gray-700', 'borderTopColor'),
                ink: probe('text-gray-500 dark:text-gray-400', 'color'),
                green: probe('bg-green-500', 'backgroundColor'),
                // The ring round a mark is the card it stands on, so a mark on top of a bar reads
                // as a disc and not as part of the bar.
                surface: 'rgb(' + (getComputedStyle(document.documentElement).getPropertyValue('--ap-surface').trim() || '255 255 255') + ')',
            };
        }

        var chart = null;
        // What the chart's tooltip reads. Kept beside the chart and not looked up through the app:
        // the chart is built while the app is still mounting.
        var current = INITIAL;
        var reducedMotion = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
        var LAST = INITIAL.minutes.length - 1;

        // Ticks a person can read without counting bars: half an hour ago, then every ten, then now.
        function axisLabel(index) {
            if (index === LAST) return MSG.axisNow;
            var back = LAST + 1 - index;
            return back % 10 === 0 ? unit(back, 'minute', 'short') : '';
        }
        function barTitle(index) {
            if (index === LAST) return MSG.axisNow;
            try { return relativeExact.format(-(LAST - index), 'minute'); } catch (e) { return String(LAST - index); }
        }
        // A minute with a sale or a registration in it carries a mark, standing on top of that
        // minute's bar (on the floor of the chart when nobody was looking at a page just then).
        function markData(payload) {
            var marks = payload.marks || { sales: [], registrations: [] };
            return payload.minutes.map(function (views, index) {
                return (marks.sales[index] || 0) + (marks.registrations[index] || 0) > 0 ? views : null;
            });
        }
        // Sized to the room a minute has: thirty bars across a phone leave about nine pixels each,
        // and a mark wider than its bar's pitch runs into the next one.
        function markRadius(context) {
            var area = context.chart.chartArea;
            if (!area) return 4;
            return Math.max(3, Math.min(6, (area.width / current.minutes.length) * 0.32));
        }
        function tooltipLines(item) {
            if (item.datasetIndex === 0) return MSG.pageViews + ': ' + numberFormat.format(item.raw || 0);
            var marks = current.marks || { sales: [], registrations: [] }, lines = [];
            if (marks.sales[item.dataIndex]) lines.push(MSG.sales + ': ' + numberFormat.format(marks.sales[item.dataIndex]));
            if (marks.registrations[item.dataIndex]) lines.push(MSG.registrations + ': ' + numberFormat.format(marks.registrations[item.dataIndex]));
            return lines;
        }
        function buildChart(canvas, payload) {
            if (chart) { chart.destroy(); chart = null; }
            if (!canvas || typeof Chart === 'undefined') return;
            var colors = chartColors();
            var minutes = payload.minutes;
            chart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: minutes.map(function (_, index) { return barTitle(index); }),
                    datasets: [
                        { label: MSG.pageViews, data: minutes.slice(), backgroundColor: colors.blue, borderRadius: 3, maxBarThickness: 18, order: 1 },
                        // Counts only, on purpose: the tooltip says how many sales a minute held
                        // and never which event, so a mark cannot be read as "that visitor bought".
                        {
                            type: 'line', label: MSG.saleMark, data: markData(payload), showLine: false, order: 0, clip: false,
                            pointStyle: 'circle', pointRadius: markRadius, pointHoverRadius: markRadius,
                            pointBackgroundColor: colors.green, pointHoverBackgroundColor: colors.green,
                            pointBorderColor: colors.surface, pointHoverBorderColor: colors.surface, pointBorderWidth: 2, pointHoverBorderWidth: 2,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: reducedMotion ? false : { duration: 300 },
                    // A mark stands on top of its bar; without room the one on the tallest bar is
                    // cut in half by the edge of the canvas.
                    layout: { padding: { top: 8 } },
                    // One tooltip to a minute, wherever in its column the pointer is: a mark is a
                    // few pixels wide and nobody should have to hit it.
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            padding: 10, cornerRadius: 8, displayColors: false,
                            filter: function (item) { return item.datasetIndex === 0 || item.raw !== null; },
                            callbacks: { label: tooltipLines },
                        },
                    },
                    scales: {
                        x: {
                            reverse: !!CONFIG.rtl,
                            grid: { display: false },
                            border: { display: false },
                            ticks: { color: colors.ink, font: { size: 11 }, maxRotation: 0, autoSkip: false, callback: function (value, index) { return axisLabel(index); } },
                        },
                        y: {
                            position: CONFIG.rtl ? 'right' : 'left',
                            beginAtZero: true,
                            suggestedMax: 4,
                            grid: { color: colors.grid },
                            border: { display: false },
                            ticks: { color: colors.ink, font: { size: 11 }, maxTicksLimit: 4, precision: 0 },
                        },
                    },
                },
            });
        }

        var app = Vue.createApp({
            data: function () {
                return {
                    p: INITIAL,
                    a: ACTIVITY,
                    msg: MSG,
                    selected: CONFIG.selected || '',
                    shareUrl: CONFIG.shareUrl,
                    deviceIcons: ICONS,
                    activityIcons: ACTIVITY_ICONS,
                    salesUrl: CONFIG.salesUrl,
                    status: 'live',
                    // Answers missed in a row. One is said beside "Live"; the panel waits for two.
                    failures: 0,
                    receivedAt: Date.now(),
                    // When the tab stopped for good (signed out, switched off): from then on a
                    // "time on page" must not go on counting over numbers nobody is refreshing.
                    frozenAt: 0,
                    clock: 0,
                    fresh: {},
                    // What grew in the last refresh, by name: 'views', 'visitors', 'tile:sale',
                    // 'door:<key>'. Each pulses once and is forgotten.
                    bumped: {},
                    copied: false,
                    // The rail: which count is pressed, whether a phone shows every row, the rows
                    // that just arrived, and the lists a refresh brought while the pointer was in
                    // the list (see hold()).
                    feed: '',
                    activityAll: false,
                    freshRows: {},
                    listsAt: Date.now(),
                    pointerIn: false,
                    focusIn: false,
                    holding: false,
                    held: null,
                };
            },
            computed: {
                // Nothing at all in the half hour: no page view (on a page or in a calendar embedded
                // elsewhere), nobody to list, no checkout and no sale to mark. Then the left side
                // is one line and not a card of zeros.
                empty: function () {
                    return !this.p.overview.views_30m && !this.p.overview.embed_views_30m
                        && !this.p.visitors.now.length && !this.p.visitors.earlier.length
                        && !this.p.checkouts.started && !this.hasMarks;
                },
                // Somebody opened a page, or is listed. Only then do the list of people and the four
                // breakdowns have anything to say: a half hour of nothing but views of a calendar
                // embedded elsewhere (or a checkout with no page view) is not "quiet", and five
                // cards saying "nothing" beside its one real figure are worse than none.
                hasTraffic: function () {
                    return !!(this.p.overview.views_30m || this.p.visitors.now.length || this.p.visitors.earlier.length);
                },
                // Nothing on either side of the page: quiet, an empty rail and no door card. The
                // two cards left are then drawn as a pair of equal height (.rt-still).
                still: function () {
                    return this.empty && !this.a.lists.all.length && !this.a.door.events.length;
                },
                // More in the day than the list shows. Not while rows are held back: the counts
                // have moved on and the rows have not, and "the newest 2 of 3" would be untrue.
                moreThanShown: function () {
                    return !this.held && this.feedTotal > this.feedItems.length;
                },
                hasMarks: function () {
                    var marks = this.p.marks;
                    return marks.sales.some(function (count) { return count > 0; }) || marks.registrations.some(function (count) { return count > 0; });
                },
                hasActivity: function () {
                    return this.a.stats.some(function (stat) { return stat.count > 0; });
                },
                feedItems: function () {
                    return (this.feed ? this.a.lists[this.feed] : this.a.lists.all) || [];
                },
                // How many there were in the day, of which the list shows the newest.
                feedTotal: function () {
                    var feed = this.feed;
                    if (!feed) return this.a.total;
                    var stat = this.a.stats.filter(function (each) { return each.type === feed; })[0];
                    return stat ? stat.count : 0;
                },
                cardGroups: function () {
                    var b = this.p.breakdowns;
                    return [
                        { id: 'rt-ps', cards: [
                            { id: 'pages', title: this.msg.topPages, unit: this.msg.unitViews, rows: b.pages },
                            { id: 'sources', title: this.msg.sources, unit: this.msg.unitVisits, rows: b.sources },
                        ] },
                        { id: 'rt-cd', cards: [
                            { id: 'countries', title: this.msg.countries, unit: this.msg.unitViews, rows: b.countries },
                            { id: 'devices', title: this.msg.devices, unit: this.msg.unitViews, rows: b.devices },
                        ] },
                    ];
                },
                shareLabel: function () {
                    return String(this.shareUrl || '').replace(/^https?:\/\//, '').replace(/\/$/, '');
                },
            },
            methods: {
                number: function (value) { return numberFormat.format(value || 0); },
                // Time on the page, counted on between polls so the list does not jump every 15s.
                duration: function (seconds) {
                    var until = this.frozenAt || Date.now();
                    var total = (seconds || 0) + (this.clock >= 0 ? Math.max(0, Math.floor((until - this.receivedAt) / 1000)) : 0);
                    var minutes = Math.floor(total / 60);
                    // Past the hour the seconds are noise, and "183m 12s" is arithmetic for the reader.
                    if (minutes >= 60) return unit(Math.floor(minutes / 60), 'hour') + ' ' + unit(minutes % 60, 'minute');
                    return minutes ? unit(minutes, 'minute') + ' ' + unit(total % 60, 'second') : unit(total, 'second');
                },
                ago: function (seconds) {
                    try { return relative.format(-Math.max(1, Math.round(seconds / 60)), 'minute'); } catch (e) { return ''; }
                },
                leftAgo: function (seconds) {
                    var when = '';
                    try { when = relativeExact.format(-Math.max(1, Math.round((seconds || 0) / 60)), 'minute'); } catch (e) {}
                    return this.msg.left.replace(':time', when);
                },
                countryName: function (code) {
                    if (!code) return this.msg.other;
                    try { return (regionNames && regionNames.of(code)) || code; } catch (e) { return code; }
                },
                // The schedule's name is printed beside this by the template, in an element of its
                // own, so that it keeps its own text direction.
                kindLabel: function (visitor) {
                    return visitor.kind === 'event' ? this.msg.eventPage : this.msg.schedulePage;
                },
                rowLabel: function (card, row) {
                    if (row.other) return this.msg.other;
                    if (card.id === 'countries') return this.countryName(row.key);
                    if (card.id === 'devices') return this.msg.device[row.key] || this.msg.other;
                    return row.label;
                },
                barWidth: function (card, row) {
                    var max = card.rows.reduce(function (most, each) { return Math.max(most, each.views); }, 1);
                    return Math.max(2, Math.round(row.views / max * 100)) + '%';
                },
                newest: function (shown, total) {
                    return this.msg.showingNewest.replace(':shown', this.number(shown)).replace(':total', this.number(total));
                },
                nowCount: function (count) {
                    return this.msg.nowCount.replace(':count', this.number(count));
                },
                countOf: function (count, total) {
                    return this.msg.countOf.replace(':count', this.number(count)).replace(':total', this.number(total));
                },
                // "42+ of 120" when the count is a floor: the plus belongs to what was counted.
                arrivedOf: function (event) {
                    return this.msg.countOf.replace(':count', this.number(event.checked_in) + (event.floor ? '+' : '')).replace(':total', this.number(event.sold));
                },
                doorShare: function (event) {
                    return (event.sold > 0 ? Math.min(100, Math.round(event.checked_in / event.sold * 100)) : 0) + '%';
                },
                tone: function (kind) {
                    return ACTIVITY_TONES[kind] || ACTIVITY_TONES.other;
                },
                // How long ago a row of the rail happened, counted on from when its list arrived.
                since: function (seconds) {
                    var total = Math.max(0, (seconds || 0) + Math.floor((Date.now() - this.listsAt) / 1000));
                    try {
                        return total < 3600
                            ? relative.format(-Math.max(1, Math.round(total / 60)), 'minute')
                            : relative.format(-Math.round(total / 3600), 'hour');
                    } catch (e) { return ''; }
                },
                // Line two of a row: what it is, then what it held. Labels and numbers, each a
                // piece of its own, so nothing here is a sentence that needs a plural. A
                // schedule's name is marked, and the template gives it its own text direction.
                // It comes last because it is the piece a narrow rail may cut short; when it
                // happened stands at the end of the line and is never cut.
                lineTwo: function (item) {
                    var parts = [];
                    if (item.title) parts.push({ text: this.msg.kind[item.kind] });
                    if (item.unit && item.quantity) parts.push({ text: this.msg.unit[item.unit] + ' ' + this.number(item.quantity) });
                    if (item.note) parts.push({ text: this.msg.rowNote[item.note] });
                    if (item.when) parts.push({ text: item.when });
                    if (item.schedule) parts.push({ text: item.schedule, name: true });
                    return parts;
                },
                toggleFeed: function (type) {
                    this.feed = this.feed === type ? '' : type;
                    this.activityAll = false;
                    // Another list is on screen now: nothing is being held still for anyone, and
                    // the element whose "leave" would have said so may be gone.
                    this.release();
                },
                // hold: a refresh that arrives while a mouse pointer or keyboard focus is in the
                // list keeps its rows back until they leave, so a row does not move from under a
                // finger. Two flags, so that clicking blank space (a focusout) does not release
                // what the pointer still needs. Three things do NOT hold, because each has an
                // "enter" that no "leave" follows: a tap or a pen, the focus a mouse click leaves
                // on a link (open Sales in a new tab, come back, and the list would wait for
                // ever), and a pointer or focus that has not stirred for a minute (see
                // applyActivity). `holdTouched` is when it last stirred; it is kept outside the
                // app's data on purpose, since it changes on every mouse move.
                onPointer: function (inside, event) {
                    if (event && event.pointerType && event.pointerType !== 'mouse') return;
                    holdTouched = Date.now();
                    this.pointerIn = inside;
                    this.holdChanged();
                },
                // A pointer that moves is still there. It keeps a hold from running out under it,
                // and takes one up again after it has: no "enter" follows a release while the
                // pointer stays inside, so without this every later refresh moved the rows.
                onPointerMove: function (event) {
                    if (event && event.pointerType && event.pointerType !== 'mouse') return;
                    holdTouched = Date.now();
                    if (!this.pointerIn) {
                        this.pointerIn = true;
                        this.holdChanged();
                    }
                },
                onFocusIn: function (event) {
                    var keyboard = true;
                    try { keyboard = event.target.matches(':focus-visible'); } catch (e) {}
                    if (!keyboard) return;
                    holdTouched = Date.now();
                    this.focusIn = true;
                    this.holdChanged();
                },
                onFocusOut: function (event) {
                    // Tab from one row to the next stays inside the list: that is not leaving it.
                    if (event.currentTarget.contains(event.relatedTarget)) return;
                    this.focusIn = false;
                    this.holdChanged();
                },
                // The same for the keyboard as a move is for the pointer.
                onHoldKey: function (event) {
                    holdTouched = Date.now();
                    if (!this.focusIn) this.onFocusIn(event);
                },
                holdChanged: function () {
                    var on = this.pointerIn || this.focusIn;
                    this.holding = on;
                    if (!on) this.release();
                },
                release: function () {
                    this.pointerIn = false;
                    this.focusIn = false;
                    this.holding = false;
                    if (this.held) {
                        this.a = Object.assign({}, this.a, { lists: this.held.lists });
                        this.listsAt = this.held.at;
                        this.held = null;
                    }
                },
                pulse: function (names) {
                    var self = this, keys = Object.keys(names);
                    if (!keys.length) return;
                    this.bumped = Object.assign({}, this.bumped, names);
                    setTimeout(function () {
                        var left = Object.assign({}, self.bumped);
                        keys.forEach(function (key) { delete left[key]; });
                        self.bumped = left;
                    }, 2200);
                },
                reload: function () { window.location.reload(); },
                copyLink: function () {
                    var self = this;
                    var done = function () { self.copied = true; setTimeout(function () { self.copied = false; }, 1600); };
                    try {
                        navigator.clipboard.writeText(this.shareUrl).then(done, function () {});
                    } catch (e) {}
                },
                apply: function (payload) {
                    var self = this, before = {}, marks = {};
                    this.p.visitors.now.forEach(function (visitor) { before[visitor.id] = true; });
                    payload.visitors.now.forEach(function (visitor) { if (!before[visitor.id]) marks[visitor.id] = true; });

                    var grew = {};
                    if (payload.overview.views_5m > this.p.overview.views_5m) grew.views = true;
                    if (payload.overview.visitors_now > this.p.overview.visitors_now) grew.visitors = true;

                    this.p = payload;
                    current = payload;
                    this.receivedAt = Date.now();
                    this.fresh = marks;
                    this.pulse(grew);
                    setTimeout(function () { self.fresh = {}; }, 2200);

                    if (chart) {
                        chart.data.datasets[0].data = payload.minutes.slice();
                        chart.data.datasets[1].data = markData(payload);
                        chart.update();
                    }
                },
                applyActivity: function (payload) {
                    var self = this, before = {}, arrived = {}, added = 0, grew = {}, counts = {}, doors = {};
                    this.a.lists.all.forEach(function (item) { before[item.key] = true; });
                    payload.lists.all.forEach(function (item) { if (!before[item.key]) { arrived[item.key] = true; added++; } });

                    this.a.stats.forEach(function (stat) { counts[stat.type] = stat.count; });
                    payload.stats.forEach(function (stat) { if (stat.count > (counts[stat.type] || 0)) grew['tile:' + stat.type] = true; });
                    this.a.door.events.forEach(function (event) { doors[event.key] = event.checked_in; });
                    payload.door.events.forEach(function (event) {
                        if (event.checked_in !== null && doors[event.key] !== undefined && doors[event.key] !== null && event.checked_in > doors[event.key]) grew['door:' + event.key] = true;
                    });

                    // A hold does not outlive a minute of stillness: a pointer left resting on the
                    // list, or a "leave" that never came, must not stop the rows for good. Counted
                    // from the last move or key press, not from when it began, so someone reading
                    // down the list keeps it for as long as they are reading.
                    if (this.holding && Date.now() - holdTouched > HOLD_MS) this.release();

                    // The counts and the door move at once. The rows wait while someone is in them.
                    if (this.holding) {
                        this.held = { lists: payload.lists, at: Date.now() };
                        this.a = Object.assign({}, payload, { lists: this.a.lists });
                    } else {
                        this.a = payload;
                        this.listsAt = Date.now();
                        this.held = null;
                        // One, two or three rows are news and are tinted for a moment. More than
                        // that is a busy night, and twenty rows flashing at once say nothing.
                        this.freshRows = added > 0 && added <= 3 ? arrived : {};
                        setTimeout(function () { self.freshRows = {}; }, 2200);
                    }
                    this.pulse(grew);

                    // A filter whose kind has gone (bookings stop being a button on a day without
                    // one) is let go, or the list would be empty with nothing to press.
                    if (this.feed && !payload.stats.some(function (stat) { return stat.type === self.feed; })) this.feed = '';
                },
            },
            mounted: function () {
                buildChart(this.$refs.chart, this.p);
            },
        }).mount('#schedule-realtime');

        // A second hand for "time on page". Only while someone can see it, and not after the tab
        // has stopped for good (see stop()).
        var clockTimer = setInterval(function () {
            if (document.visibilityState === 'visible' && app.p.visitors.now.length) { app.clock++; }
        }, 1000);

        var timer = null, hiddenSince = null, failures = 0, gone = false, paused = false;
        // One traffic request at a time; and when the server asked us to slow down (a 429), the
        // moment it said to come back at, and how many times running it has said so.
        var inflight = false, notBefore = 0, throttled = 0;
        // When a pointer or the keyboard last stirred in the rail's list (see hold).
        var holdTouched = 0;
        var activityAt = Date.now(), activityBusy = false, activityDirty = false;
        // The newest mark the rail has been asked about: its second, how many marks that second
        // holds (two sales in one second are two pieces of news, and the second alone would hide
        // the later one), and how many marks the chart holds in all (a sale whose time is OLDER
        // than the newest seen is news too, and only the total shows it).
        function markTotal(marks) {
            var total = 0;
            if (!marks) return total;
            (marks.sales || []).concat(marks.registrations || []).forEach(function (count) { total += count || 0; });
            return total;
        }
        var lastMark = (INITIAL.marks && INITIAL.marks.last) || 0, lastMarkCount = (INITIAL.marks && INITIAL.marks.at_last) || 0, lastMarkTotal = markTotal(INITIAL.marks);

        // A request that gives up: fetch() has no limit of its own, and one that hangs would hold
        // its poll open for good. The limit runs until the BODY is read, not until the headers
        // arrive: a response whose headers came and whose body never did left the poll waiting
        // for ever, the tab saying "Live", and coming back to the tab did not wake it.
        function request(url) {
            var controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
            var limit = controller ? setTimeout(function () { controller.abort(); }, FETCH_TIMEOUT_MS) : null;
            var done = function () { if (limit) { clearTimeout(limit); limit = null; } };
            return fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                signal: controller ? controller.signal : undefined,
            }).then(function (response) {
                var answer = { status: response.status, ok: response.ok, retryAfter: response.headers.get('Retry-After'), payload: null };
                if (!response.ok) { done(); return answer; }
                return response.json().then(function (payload) { done(); answer.payload = payload; return answer; });
            }).catch(function (error) { done(); throw error; });
        }

        // Signed out, no longer theirs, or switched off for the install: not coming back. The
        // numbers on screen are the last ones, so nothing goes on counting over them.
        function stop() {
            gone = true;
            clearTimeout(timer);
            clearInterval(clockTimer);
            app.frozenAt = Date.now();
            app.status = 'stopped';
        }

        // The rail and the door card. Its failures are quiet: the rail keeps what it has and is
        // asked for again in a minute, and whether the tab may be seen at all is the traffic
        // poll's to find out. That goes for an answer that says the read itself failed
        // (`failed`): it is an empty rail, and taking it would wipe a full one.
        function fetchActivity() {
            if (gone) return;
            // Asked for while one is on its way (a second sale landed): once more when it is back.
            if (activityBusy) { activityDirty = true; return; }
            activityBusy = true;
            // Stamped as it is asked, not as it lands: "every minute" is a minute between
            // questions, and a slow answer must not push the next one a tick further each time.
            activityAt = Date.now();
            var asked = app.selected || '';
            request(CONFIG.activityUrl + (asked ? '?schedule=' + encodeURIComponent(asked) : ''))
                .then(function (answer) {
                    var payload = answer.payload;
                    // An answer for another schedule than the one on screen is dropped.
                    if (payload && !payload.failed && payload.schedule === asked && payload.lists && payload.door) app.applyActivity(payload);
                })
                .catch(function () {})
                .then(function () {
                    activityBusy = false;
                    if (activityDirty) { activityDirty = false; fetchActivity(); }
                });
        }

        function plan() {
            clearTimeout(timer);
            if (gone || paused) return;
            var base = document.visibilityState === 'visible' ? VISIBLE_MS : HIDDEN_MS;
            // Not before the server said, when it asked us to slow down (a 429).
            timer = setTimeout(poll, Math.max(base * Math.min(4, failures + 1), notBefore - Date.now()));
        }
        function poll() {
            if (gone) return;
            if (hiddenSince !== null && Date.now() - hiddenSince > GIVE_UP_HIDDEN_MS) {
                paused = true;
                return;
            }
            // Coming back to the tab asks at once; if an answer is already on its way, that one
            // will do, and two in flight could land in the wrong order.
            if (inflight) return;
            inflight = true;
            var url = CONFIG.url + (app.selected ? '?schedule=' + encodeURIComponent(app.selected) : '');
            request(url)
                .then(function (answer) {
                    if ([401, 403, 404, 419].indexOf(answer.status) !== -1) {
                        stop();
                        return;
                    }
                    // Asked to slow down: wait as long as it says (at least a second, thirty if it
                    // does not say). Once is the server pacing us and nothing to announce. Twice
                    // running, the numbers on screen are getting old and the tab says so.
                    if (answer.status === 429) {
                        var seconds = parseInt(answer.retryAfter, 10);
                        notBefore = Date.now() + Math.min(120, isNaN(seconds) ? 30 : Math.max(1, seconds)) * 1000;
                        throttled++;
                        if (throttled > 1) {
                            app.failures = throttled;
                            app.status = 'reconnecting';
                        }
                        return;
                    }
                    if (!answer.ok || !answer.payload) throw new Error('status ' + answer.status);

                    failures = 0;
                    throttled = 0;
                    app.failures = 0;
                    app.status = 'live';
                    app.apply(answer.payload);

                    // A mark that was not there before is a sale the rail has not heard of; and
                    // by the clock the rail is due once a minute. Not while nobody is looking,
                    // and then nothing is taken as seen either: the first poll after the tab
                    // comes back still finds the sale news.
                    if (document.visibilityState !== 'visible') return;
                    var marks = answer.payload.marks;
                    var mark = (marks && marks.last) || 0, count = (marks && marks.at_last) || 0, total = markTotal(marks);
                    var news = mark > lastMark || (mark === lastMark && count > lastMarkCount) || total > lastMarkTotal;
                    if (mark >= lastMark) { lastMark = mark; lastMarkCount = count; }
                    lastMarkTotal = total;
                    if (news || Date.now() - activityAt >= ACTIVITY_MS) fetchActivity();
                })
                .catch(function () {
                    failures++;
                    app.failures = failures;
                    app.status = 'reconnecting';
                })
                .then(function () {
                    inflight = false;
                    plan();
                });
        }

        // The tab is in view again: ask at once, unless the server asked us to wait.
        function shown() {
            hiddenSince = null;
            if (gone) return;
            paused = false;
            clearTimeout(timer);
            if (Date.now() < notBefore) plan(); else poll();
        }

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                shown();
            } else {
                hiddenSince = Date.now();
                // Nobody is hovering a tab they cannot see, and the "leave" may never be sent.
                app.release();
                plan();
            }
        });
        // Back from a row's link, the page may be handed back exactly as it was left: the hold,
        // and a "hidden since" that no visibilitychange will clear, after which the polls would
        // pause for good under a tab that says "Live".
        window.addEventListener('pageshow', function (event) {
            if (!event.persisted) return;
            app.release();
            if (document.visibilityState === 'visible') shown();
        });

        // The theme picker changes the palette without a reload; the chart re-reads its colours.
        new MutationObserver(function () {
            buildChart(app.$refs.chart, app.p);
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });

        if (document.visibilityState !== 'visible') { hiddenSince = Date.now(); }
        plan();
    });
</script>
