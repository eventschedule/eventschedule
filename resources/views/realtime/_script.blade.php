{{-- The owner Realtime page's behaviour: one small Vue app over the JSON RealtimeController::data()
     answers with. It polls every 15 seconds while the tab is visible and every 60 while it is not,
     gives up after half an hour hidden (and picks up again when the tab comes back), and stops for
     good when the server says the page is no longer theirs to see.

     The chart is kept outside Vue's data on purpose: a reactive proxy around a Chart.js instance
     breaks its internals. --}}
<script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function () {
        var MSG = @json($scheduleRealtimeMsg);
        var CONFIG = @json($scheduleRealtimeConfig);
        var INITIAL = @json($payload);
        var ICONS = @json($deviceIcons);
        var LOCALE = CONFIG.locale;

        var VISIBLE_MS = 15000, HIDDEN_MS = 60000, GIVE_UP_HIDDEN_MS = 30 * 60 * 1000;

        var numberFormat = new Intl.NumberFormat(LOCALE + '-u-nu-latn');
        var relative = null, relativeExact = null, regionNames = null;
        try { relative = new Intl.RelativeTimeFormat(LOCALE, { numeric: 'auto', style: 'short' }); } catch (e) {}
        try { relativeExact = new Intl.RelativeTimeFormat(LOCALE, { numeric: 'always', style: 'short' }); } catch (e) {}
        try { regionNames = new Intl.DisplayNames([LOCALE], { type: 'region' }); } catch (e) {}

        function unit(value, name, display) {
            try {
                return new Intl.NumberFormat(LOCALE + '-u-nu-latn', { style: 'unit', unit: name, unitDisplay: display || 'narrow' }).format(value);
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
            };
        }

        var chart = null;
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
        function buildChart(canvas, minutes) {
            if (chart) { chart.destroy(); chart = null; }
            if (!canvas || typeof Chart === 'undefined') return;
            var colors = chartColors();
            chart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: minutes.map(function (_, index) { return barTitle(index); }),
                    datasets: [{ label: MSG.pageViews, data: minutes.slice(), backgroundColor: colors.blue, borderRadius: 3, maxBarThickness: 18 }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: reducedMotion ? false : { duration: 300 },
                    plugins: { legend: { display: false }, tooltip: { padding: 10, cornerRadius: 8, displayColors: false } },
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
                    msg: MSG,
                    schedules: CONFIG.schedules,
                    selected: CONFIG.selected || '',
                    shareUrl: CONFIG.shareUrl,
                    deviceIcons: ICONS,
                    status: 'live',
                    receivedAt: Date.now(),
                    clock: 0,
                    fresh: {},
                    bumped: { views: false, visitors: false },
                    copied: false,
                };
            },
            computed: {
                // Nothing at all in the half hour: no page view and nobody to list.
                empty: function () {
                    return !this.p.overview.views_30m && !this.p.visitors.now.length && !this.p.visitors.earlier.length;
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
                    var total = (seconds || 0) + (this.clock >= 0 ? Math.max(0, Math.floor((Date.now() - this.receivedAt) / 1000)) : 0);
                    var minutes = Math.floor(total / 60);
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
                kindLine: function (visitor) {
                    var kind = visitor.kind === 'event' ? this.msg.eventPage : this.msg.schedulePage;
                    return visitor.schedule ? kind + ' · ' + visitor.schedule : kind;
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
                changeSchedule: function () {
                    window.location.href = CONFIG.pageUrl + (this.selected ? '?schedule=' + encodeURIComponent(this.selected) : '');
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

                    var bump = {
                        views: payload.overview.views_5m > this.p.overview.views_5m,
                        visitors: payload.overview.visitors_now > this.p.overview.visitors_now,
                    };

                    this.p = payload;
                    this.receivedAt = Date.now();
                    this.fresh = marks;
                    this.bumped = bump;
                    setTimeout(function () { self.fresh = {}; self.bumped = { views: false, visitors: false }; }, 2200);

                    if (chart) {
                        chart.data.datasets[0].data = payload.minutes.slice();
                        chart.update();
                    }
                },
            },
            mounted: function () {
                buildChart(this.$refs.chart, this.p.minutes);
            },
        }).mount('#schedule-realtime');

        // A second hand for "time on page". Only while someone can see it.
        setInterval(function () {
            if (document.visibilityState === 'visible' && app.p.visitors.now.length) { app.clock++; }
        }, 1000);

        var timer = null, hiddenSince = null, failures = 0, gone = false, paused = false;

        function plan() {
            clearTimeout(timer);
            if (gone || paused) return;
            var base = document.visibilityState === 'visible' ? VISIBLE_MS : HIDDEN_MS;
            timer = setTimeout(poll, base * Math.min(4, failures + 1));
        }
        function poll() {
            if (hiddenSince !== null && Date.now() - hiddenSince > GIVE_UP_HIDDEN_MS) {
                paused = true;
                return;
            }
            var url = CONFIG.url + (app.selected ? '?schedule=' + encodeURIComponent(app.selected) : '');
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function (response) {
                    // Signed out, no longer theirs, or switched off for the install: not coming back.
                    if ([401, 403, 404, 419].indexOf(response.status) !== -1) {
                        gone = true;
                        app.status = 'stopped';
                        return null;
                    }
                    if (!response.ok) throw new Error('status ' + response.status);
                    return response.json();
                })
                .then(function (payload) {
                    if (!payload) return;
                    failures = 0;
                    app.status = 'live';
                    app.apply(payload);
                })
                .catch(function () {
                    failures++;
                    app.status = 'reconnecting';
                })
                .then(plan);
        }

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                hiddenSince = null;
                if (gone) return;
                paused = false;
                clearTimeout(timer);
                poll();
            } else {
                hiddenSince = Date.now();
                plan();
            }
        });

        // The theme picker changes the palette without a reload; the chart re-reads its colours.
        new MutationObserver(function () {
            buildChart(app.$refs.chart, app.p.minutes);
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });

        if (document.visibilityState !== 'visible') { hiddenSince = Date.now(); }
        plan();
    });
</script>
