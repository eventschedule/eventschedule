<style {!! nonce_attr() !!}>
    @keyframes realtime-fresh { from { background-color: var(--brand-blue-a10); } to { background-color: transparent; } }
    .realtime-fresh { animation: realtime-fresh 2s ease-out 1; }
    @media (prefers-reduced-motion: reduce) { .realtime-fresh { animation: none; } }
</style>
<script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function () {
        var MSG = @json($realtimeMsg);
        var CONFIG = @json($realtimeConfig);
        var INITIAL = @json($payload);
        var BASE_TITLE = document.title;
        var LOCALE = CONFIG.locale;

        var numberFormat = new Intl.NumberFormat(LOCALE + '-u-nu-latn');
        var relative = null, regionNames = null, clockFormat = null;
        var relativeExact = null;
        try { relative = new Intl.RelativeTimeFormat(LOCALE, { numeric: 'auto', style: 'short' }); } catch (e) {}
        // "Left now" reads like a contradiction, so a phrase that already says when uses a number.
        try { relativeExact = new Intl.RelativeTimeFormat(LOCALE, { numeric: 'always', style: 'short' }); } catch (e) {}
        try { regionNames = new Intl.DisplayNames([LOCALE], { type: 'region' }); } catch (e) {}
        try { clockFormat = new Intl.DateTimeFormat(LOCALE + '-u-nu-latn', { hour: 'numeric', minute: '2-digit' }); } catch (e) {}

        function unit(value, name) {
            try {
                return new Intl.NumberFormat(LOCALE + '-u-nu-latn', { style: 'unit', unit: name, unitDisplay: 'narrow' }).format(value);
            } catch (e) {
                return value + ' ' + name;
            }
        }

        // Colours are read from the live palette, never hardcoded: the theme picker switches between
        // six palettes without a reload, so they are re-read whenever the html element changes.
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
                gray: probe('bg-gray-300 dark:bg-gray-600', 'backgroundColor'),
                grid: probe('border-gray-200 dark:border-gray-700', 'borderTopColor'),
                ink: probe('text-gray-500 dark:text-gray-400', 'color'),
            };
        }

        Vue.createApp({
            data: function () {
                return {
                    msg: MSG,
                    icons: CONFIG.icons,
                    p: INITIAL,
                    status: 'live',
                    stopMessage: null,
                    pinging: false,
                    who: INITIAL.who || 'all',
                    all: !!INITIAL.all,
                    admins: !!INITIAL.admins,
                    filters: Object.assign({}, INITIAL.filters || {}),
                    expanded: null,
                    held: null,
                    pointerIn: false,
                    focusIn: false,
                    chartTries: 0,
                    freshTimer: null,
                    activityAll: false,
                    expandedCards: { pages: false, sources: false, countries: false, surfaces: false },
                    fresh: {},
                    lastOk: Date.now(),
                    nowMs: Date.now(),
                    failures: 0,
                    retryAt: 0,
                    inflight: false,
                    queued: false,
                    timer: null,
                    debounce: null,
                    titleDot: false,
                    seenCursor: INITIAL.activity ? INITIAL.activity.cursor : null,
                };
            },

            computed: {
                statusWord: function () {
                    return this.status === 'live' ? this.msg.live : (this.status === 'reconnecting' ? this.msg.reconnecting : this.msg.stopped);
                },
                lastUpdatedText: function () {
                    return this.msg.lastUpdated.replace(':time', this.ago(Math.round((this.nowMs - this.lastOk) / 1000), true));
                },
                // Derived from the state, not location.href, so it is reactive and carries the filters.
                pageHref: function () {
                    var query = this.params().toString();
                    return CONFIG.pageUrl + (query ? '?' + query : '');
                },
                whoOptions: function () {
                    var counts = this.p.counts || { all: 0, signed_in: 0, anonymous: 0 };
                    return [
                        { value: 'all', label: this.msg.all, count: counts.all },
                        { value: 'signed_in', label: this.msg.signedIn, count: counts.signed_in },
                        { value: 'anonymous', label: this.msg.anonymous, count: counts.anonymous },
                    ];
                },
                filterChips: function () {
                    var self = this, labels = this.p.filter_labels || {};
                    var types = { surface: this.msg.filterSurface, page: this.msg.filterPage, source: this.msg.filterSource, country: this.msg.filterCountry };
                    return Object.keys(this.filters).map(function (key) {
                        var label = labels[key] || self.filters[key];
                        return { key: key, type: types[key] || key, label: key === 'country' ? self.countryName(label) : label };
                    });
                },
                // While the pointer or focus is in the list, or a row is open, values keep updating but
                // the order and the group a row sits in are held, so nothing jumps out from under a click.
                visitorRows: function () {
                    var groups = { now: (this.p.visitors && this.p.visitors.now) || [], earlier: (this.p.visitors && this.p.visitors.earlier) || [] };
                    if (!this.held) return groups;

                    var byId = {}, used = {}, out = { now: [], earlier: [] };
                    groups.now.concat(groups.earlier).forEach(function (person) {
                        byId[person.id] = person;
                        person.aliases.forEach(function (alias) { byId[alias] = byId[alias] || person; });
                    });
                    var self = this;
                    ['now', 'earlier'].forEach(function (group) {
                        self.held[group].forEach(function (id) {
                            var person = byId[id];
                            if (person && !used[person.id]) { used[person.id] = true; out[group].push(person); }
                        });
                    });
                    ['now', 'earlier'].forEach(function (group) {
                        groups[group].forEach(function (person) {
                            if (!used[person.id]) { used[person.id] = true; out[group].push(person); }
                        });
                    });
                    return out;
                },
                details: function () {
                    var data = this.p.expanded;
                    if (!data || !this.expanded) return null;
                    if (data.person_id === this.expanded) return data;
                    var person = this.findPerson(this.expanded);
                    return person && (person.id === data.person_id || person.aliases.indexOf(data.person_id) >= 0) ? data : null;
                },
                cards: function () {
                    var b = this.p.breakdowns || { pages: [], sources: [], countries: [], surfaces: [] };
                    return [
                        { id: 'pages', title: this.msg.topPages, unit: this.msg.views, filter: 'page', rows: b.pages },
                        { id: 'sources', title: this.msg.sources, unit: this.msg.visits, filter: 'source', rows: b.sources },
                        { id: 'countries', title: this.msg.countries, unit: this.msg.views, filter: 'country', rows: b.countries },
                        { id: 'surfaces', title: this.msg.surfaces, unit: this.msg.views, filter: 'surface', rows: b.surfaces },
                    ];
                },
                hasAnyone: function () {
                    return this.cards.some(function (card) { return card.rows.length > 0; }) || this.filterChips.length > 0;
                },
                chartSummary: function () {
                    var total = (this.p.minutes || []).reduce(function (sum, m) { return sum + m.consented + m.unidentified; }, 0);
                    return this.msg.viewsPerMinute + ': ' + this.fmt(total);
                },
            },

            methods: {
                fmt: function (n) { return numberFormat.format(n || 0); },
                ago: function (secs, exact) {
                    secs = Math.max(0, secs || 0);
                    var format = exact ? relativeExact : relative;
                    if (!format) return this.duration(secs);
                    if (secs < 45) return exact ? format.format(-Math.max(1, secs), 'second') : format.format(0, 'second');
                    if (secs < 3600) return format.format(-Math.round(secs / 60), 'minute');
                    if (secs < 86400) return format.format(-Math.round(secs / 3600), 'hour');
                    return format.format(-Math.round(secs / 86400), 'day');
                },
                duration: function (secs) {
                    secs = Math.max(0, secs || 0);
                    if (secs < 60) return unit(secs, 'second');
                    if (secs < 3600) return unit(Math.floor(secs / 60), 'minute');
                    return unit(Math.floor(secs / 3600), 'hour');
                },
                clock: function (atAgo) {
                    var date = new Date(Date.now() - atAgo * 1000);
                    return clockFormat ? clockFormat.format(date) : date.toLocaleTimeString();
                },
                countryName: function (code) {
                    if (!code) return '';
                    try { return (regionNames && regionNames.of(code)) || code; } catch (e) { return code; }
                },
                deviceLine: function (person) {
                    return [person.os, person.browser].filter(function (v) { return v && v !== 'Other'; }).join(' · ');
                },
                badgeLabel: function (badge) {
                    return { new: this.msg.badgeNew, demo: this.msg.badgeDemo, pro: this.msg.badgePro, enterprise: this.msg.badgeEnterprise }[badge] || badge;
                },
                activityTone: function (type) {
                    var tones = {
                        blue: { bg: 'bg-blue-50 dark:bg-blue-500/10', text: 'text-blue-600 dark:text-blue-400' },
                        green: { bg: 'bg-green-50 dark:bg-green-500/10', text: 'text-green-600 dark:text-green-400' },
                        amber: { bg: 'bg-amber-50 dark:bg-amber-500/10', text: 'text-amber-600 dark:text-amber-400' },
                        gray: { bg: 'bg-gray-100 dark:bg-gray-700', text: 'text-gray-500 dark:text-gray-400' },
                    };
                    if (['order', 'gift_card', 'upgrade'].indexOf(type) >= 0) return tones.green;
                    if (type === 'cancel') return tones.gray;
                    if (type === 'support') return tones.amber;
                    return tones.blue;
                },
                rowLabel: function (card, row) {
                    return card.id === 'countries' ? this.countryName(row.key) : row.label;
                },
                barWidth: function (card, row) {
                    var max = card.rows.reduce(function (m, r) { return Math.max(m, r.views); }, 0);
                    return Math.max(2, max ? (row.views / max) * 100 : 0) + '%';
                },
                isActive: function (filter, key) { return this.filters[filter] === key; },
                allPeople: function (payload) {
                    return payload && payload.visitors ? payload.visitors.now.concat(payload.visitors.earlier) : [];
                },
                // The exact id before an alias: a user keeps v:{key} as an alias while their browsing
                // after signing out is its own v:{key} person (RealtimeDashboard's class docblock).
                matchPerson: function (people, id) {
                    return people.find(function (person) { return person.id === id; })
                        || people.find(function (person) { return person.aliases.indexOf(id) >= 0; })
                        || null;
                },
                findPerson: function (id) {
                    return this.matchPerson(this.allPeople(this.p), id);
                },

                toggleFilter: function (filter, key) {
                    if (this.filters[filter] === key) {
                        delete this.filters[filter];
                    } else {
                        this.filters[filter] = key;
                    }
                    this.changed();
                },
                clearFilter: function (key) { delete this.filters[key]; this.changed(); },
                clearFilters: function () { this.filters = {}; this.changed(); },
                setWho: function (value) { this.who = value; this.changed(); },
                showAllVisitors: function () { this.all = true; this.changed(); },
                toggleAdmins: function () { this.admins = !this.admins; this.changed(); },
                changed: function () {
                    this.held = null;
                    this.expanded = null;
                    this.syncUrl();
                    clearTimeout(this.debounce);
                    this.debounce = setTimeout(this.poll, 300);
                },
                params: function () {
                    var params = new URLSearchParams(), self = this;
                    Object.keys(this.filters).forEach(function (key) { params.set(key, self.filters[key]); });
                    if (this.who !== 'all') params.set('who', this.who);
                    if (this.all) params.set('all', '1');
                    if (this.admins) params.set('admins', '1');
                    return params;
                },
                syncUrl: function () {
                    var query = this.params().toString();
                    try { history.replaceState(null, '', CONFIG.pageUrl + (query ? '?' + query : '')); } catch (e) {}
                },

                // The order is held while a mouse pointer OR keyboard focus is in the list, or a row
                // is open. Two flags, so clicking blank space (a focusout) does not release a hold the
                // pointer still needs. Touch and pen are ignored (a tap fires an enter that no leave
                // ever clears), and so is the focus a mouse click leaves on a row's button, or one
                // click would freeze the order until the admin clicked somewhere else.
                holdSnapshot: function () {
                    if (this.held) return;
                    this.held = {
                        now: this.visitorRows.now.map(function (p) { return p.id; }),
                        earlier: this.visitorRows.earlier.map(function (p) { return p.id; }),
                    };
                },
                releaseHold: function () {
                    if (!this.pointerIn && !this.focusIn && !this.expanded) this.held = null;
                },
                onPointer: function (inside, event) {
                    if (event && event.pointerType && event.pointerType !== 'mouse') return;
                    this.pointerIn = inside;
                    if (inside) this.holdSnapshot(); else this.releaseHold();
                },
                onFocusIn: function (event) {
                    var keyboard = true;
                    try { keyboard = event.target.matches(':focus-visible'); } catch (e) {}
                    if (!keyboard) return;
                    this.focusIn = true;
                    this.holdSnapshot();
                },
                onFocusOut: function (event) {
                    if (!event.currentTarget.contains(event.relatedTarget)) {
                        this.focusIn = false;
                        this.releaseHold();
                    }
                },
                panelId: function (person) {
                    return 'realtime-person-' + person.id.replace(/[^A-Za-z0-9]/g, '');
                },
                fill: function (template, label) {
                    // A function, so a "$&" in a page title or source is not a replacement pattern.
                    return template.replace(':label', function () { return label; });
                },
                toggleExpand: function (person) {
                    if (this.expanded === person.id) {
                        this.expanded = null;
                        this.releaseHold();
                        return;
                    }
                    this.holdSnapshot();
                    this.expanded = person.id;
                    clearTimeout(this.debounce);
                    this.debounce = setTimeout(this.poll, 0);
                },

                poll: function () {
                    var self = this;
                    if (this.status === 'reauth' || this.status === 'stopped') return;
                    // A 429 holds every poll, including the ones a filter change or tab return asks for.
                    if (this.retryAt > Date.now()) { this.schedule(); return; }
                    if (this.inflight) { this.queued = true; return; }
                    this.inflight = true;
                    clearTimeout(this.timer);

                    var params = this.params();
                    var asked = params.toString();
                    if (this.expanded) params.set('expand', this.expanded);

                    fetch(CONFIG.url + '?' + params.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    }).then(function (r) {
                        if (r.status === 423) { self.status = 'reauth'; document.title = BASE_TITLE; throw null; }
                        if (r.status === 401 || r.status === 403) {
                            self.status = 'stopped';
                            document.title = BASE_TITLE;
                            // A 401 is Laravel's untranslated "Unauthenticated." (signed out in another
                            // tab), so it gets the generic stopped text. EnsureUserIsAdmin's 403s carry
                            // their translated reason under `error`.
                            if (r.status === 401) { self.stopMessage = null; throw null; }
                            return r.json().catch(function () { return {}; }).then(function (body) {
                                self.stopMessage = (body && (body.error || body.message)) || null;
                                throw null;
                            });
                        }
                        if (r.status === 429) {
                            // Back off without crying wolf: the data is just a little older.
                            self.retryAt = Date.now() + 1000 * (parseInt(r.headers.get('Retry-After') || '30', 10) || 30);
                            throw null;
                        }
                        if (!r.ok) throw r;
                        return r.json();
                    }).then(function (data) {
                        // Answered for filters that have changed since: a queued poll, or the debounced
                        // one changed() is about to send, will bring the right data.
                        if (self.queued || self.params().toString() !== asked) return;
                        self.apply(data);
                        self.status = 'live';
                        self.failures = 0;
                        self.lastOk = Date.now();
                        self.ping();
                    }).catch(function (error) {
                        if (error === null) return;
                        self.failures++;
                        self.status = 'reconnecting';
                    }).finally(function () {
                        self.inflight = false;
                        if (self.queued) {
                            self.queued = false;
                            self.poll();
                        } else {
                            self.schedule();
                        }
                    });
                },
                schedule: function () {
                    clearTimeout(this.timer);
                    if (this.status === 'reauth' || this.status === 'stopped') return;
                    var delay = document.hidden ? 60000 : 10000;
                    if (this.failures) delay = [10000, 20000, 40000, 60000][Math.min(this.failures - 1, 3)];
                    if (this.retryAt > Date.now()) delay = Math.max(delay, this.retryAt - Date.now());
                    this.timer = setTimeout(this.poll, delay);
                },
                ping: function () {
                    var self = this;
                    this.pinging = false;
                    this.$nextTick(function () {
                        self.pinging = true;
                        setTimeout(function () { self.pinging = false; }, 1000);
                    });
                },
                apply: function (data) {
                    var self = this, before = {}, fresh = {};

                    this.allPeople(this.p).forEach(function (person) {
                        before[person.id] = true;
                        person.aliases.forEach(function (alias) { before[alias] = true; });
                    });
                    var added = this.allPeople(data).filter(function (person) {
                        return !before[person.id] && !person.aliases.some(function (alias) { return before[alias]; });
                    });
                    // A burst of arrivals would turn the list into a light show; only a few get the fade.
                    if (added.length && added.length <= 3) added.forEach(function (person) { fresh[person.id] = true; });

                    var seenItems = {};
                    ((this.p.activity && this.p.activity.items) || []).forEach(function (item) { seenItems[item.key] = true; });
                    if (this.p.activity) {
                        ((data.activity && data.activity.items) || []).forEach(function (item) { if (!seenItems[item.key]) fresh[item.key] = true; });
                    }

                    if (document.hidden && data.activity && data.activity.cursor && this.seenCursor && data.activity.cursor > this.seenCursor) {
                        var newest = data.activity.items[0];
                        if (newest && (newest.type === 'signup' || newest.type === 'order')) this.titleDot = true;
                    }
                    if (!document.hidden && data.activity) this.seenCursor = data.activity.cursor;

                    if (this.expanded) {
                        var match = this.matchPerson(this.allPeople(data), this.expanded);
                        if (match) {
                            this.expanded = match.id;
                        } else {
                            // Filtered out or gone from the window: close it, or the held order
                            // would stay frozen with nothing on screen able to release it.
                            this.expanded = null;
                            this.releaseHold();
                        }
                    }

                    this.p = data;
                    this.fresh = fresh;
                    clearTimeout(this.freshTimer);
                    this.freshTimer = setTimeout(function () { self.fresh = {}; }, 2100);
                    this.$nextTick(function () {
                        // The canvas exists only while tracking is on: build the chart the first time
                        // it appears, and again if switching off and on replaced the element.
                        var canvas = self.$refs.chart;
                        if (canvas && (!self.chart || self.chart.canvas !== canvas)) {
                            if (self.chart) { try { self.chart.destroy(); } catch (e) {} }
                            self.chart = null;
                            // Only a new canvas gets a fresh retry budget: resetting it on every poll
                            // meant a chart.min.js that never loads was retried forever.
                            if (self.chartCanvas !== canvas) { self.chartCanvas = canvas; self.chartTries = 0; }
                            self.initChart();
                        } else {
                            self.updateChart();
                        }
                    });
                    this.updateTitle();
                },
                updateTitle: function () {
                    // A stopped page shows no count: it is no longer live, and returning to the tab
                    // would otherwise put the last one back.
                    if (!this.p.overview || this.status === 'reauth' || this.status === 'stopped') { document.title = BASE_TITLE; return; }
                    document.title = '(' + this.fmt(this.p.overview.now) + ') ' + (this.titleDot ? '● ' : '') + BASE_TITLE;
                },

                // Bucket i is (29 - i) whole minutes ago: RealtimeDashboard::minutes() ends on the
                // current minute.
                axisLabels: function () {
                    var labels = [];
                    for (var i = 0; i < 30; i++) {
                        labels.push(i === 29 ? this.msg.axisNow : (i === 0 || i === 14 ? unit(-(29 - i), 'minute') : ''));
                    }
                    return labels;
                },
                initChart: function () {
                    var self = this;
                    if (typeof Chart === 'undefined' || !this.$refs.chart) {
                        // Bounded: if chart.min.js never loads, stop asking after five seconds.
                        if (this.p.state !== 'off' && this.chartTries++ < 100) setTimeout(this.initChart, 50);
                        return;
                    }
                    var colors = chartColors();
                    var bar = { borderRadius: 3, borderSkipped: 'start', barPercentage: 0.8, categoryPercentage: 0.9, maxBarThickness: 16, stack: 'views' };
                    this.chart = new Chart(this.$refs.chart, {
                        type: 'bar',
                        data: {
                            labels: this.axisLabels(),
                            datasets: [
                                Object.assign({ label: this.msg.acceptedCookies, data: [], backgroundColor: colors.blue }, bar),
                                Object.assign({ label: this.msg.notIdentified, data: [], backgroundColor: colors.gray }, bar),
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: { duration: 400 },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        title: function (items) {
                                            var index = items[0].dataIndex;
                                            return index === 29 ? self.msg.thisMinute : unit(-(29 - index), 'minute');
                                        },
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    reverse: CONFIG.rtl,
                                    grid: { display: false },
                                    border: { display: false },
                                    // inner: the first and last labels sit inside the plot, or
                                    // "now" is clipped at the edge (in RTL it is the left edge).
                                    ticks: { color: colors.ink, font: { size: 11 }, autoSkip: false, maxRotation: 0, align: 'inner' },
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    suggestedMax: 5,
                                    position: CONFIG.rtl ? 'right' : 'left',
                                    grid: { color: colors.grid },
                                    border: { display: false },
                                    ticks: { color: colors.ink, font: { size: 11 }, precision: 0, maxTicksLimit: 3 },
                                },
                            },
                        },
                    });
                    this.updateChart();
                    this.chart.options.animation = false;
                },
                updateChart: function () {
                    if (!this.chart || !this.p.minutes) return;
                    this.chart.data.datasets[0].data = this.p.minutes.map(function (m) { return m.consented; });
                    this.chart.data.datasets[1].data = this.p.minutes.map(function (m) { return m.unidentified; });
                    this.chart.update('none');
                },
                recolorChart: function () {
                    if (!this.chart) return;
                    var colors = chartColors();
                    this.chart.data.datasets[0].backgroundColor = colors.blue;
                    this.chart.data.datasets[1].backgroundColor = colors.gray;
                    this.chart.options.scales.x.ticks.color = colors.ink;
                    this.chart.options.scales.y.ticks.color = colors.ink;
                    this.chart.options.scales.y.grid.color = colors.grid;
                    this.chart.update('none');
                },
            },

            mounted: function () {
                var self = this;
                this.initChart();
                this.updateTitle();
                this.schedule();

                setInterval(function () { self.nowMs = Date.now(); }, 5000);

                document.addEventListener('visibilitychange', function () {
                    if (!document.hidden) {
                        self.titleDot = false;
                        self.seenCursor = self.p.activity ? self.p.activity.cursor : self.seenCursor;
                        self.updateTitle();
                        if (Date.now() - self.lastOk > 10000) {
                            self.poll();
                            return;
                        }
                    }
                    self.schedule();
                });

                new MutationObserver(function () { self.recolorChart(); })
                    .observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });
            },
        }).mount('#realtime-app');
    });
</script>
