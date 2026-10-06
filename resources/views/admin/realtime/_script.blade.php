<style {!! nonce_attr() !!}>
    @keyframes realtime-fresh { from { background-color: var(--brand-blue-a10); } to { background-color: transparent; } }
    .realtime-fresh { animation: realtime-fresh 2s ease-out 1; }
    @media (prefers-reduced-motion: reduce) { .realtime-fresh { animation: none; } }

    /* The Activity count buttons: a recessed cell, pressed while it filters the feed. On the --ap-*
       tokens, so every palette gets its own shade. */
    .realtime-stat { background: var(--ap-tint-sunken); }
    .realtime-stat:not(:disabled):hover { background: var(--ap-tint-2); }
    .realtime-stat:disabled { cursor: default; }
    .realtime-stat-on, .realtime-stat-on:not(:disabled):hover { background: var(--brand-blue-a10); box-shadow: var(--ap-inset-pressed); }
    /* One short pulse when a count grows, never a loop. */
    @keyframes realtime-bump { 0% { transform: scale(1); } 35% { transform: scale(1.04); } 100% { transform: scale(1); } }
    .realtime-bump { animation: realtime-bump 0.5s ease-out 1; }
    @media (prefers-reduced-motion: reduce) { .realtime-bump { animation: none; } }
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
                green: probe('text-green-500', 'color'),
                white: probe('text-white', 'color'),
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
                    feed: (INITIAL.activity && INITIAL.activity.feed) || null,
                    signupsAll: false,
                    bumped: {},
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
                signups: function () {
                    return (this.p.activity && this.p.activity.signups) || { total: 0, rows: [], steps: [], stage_titles: [], hours: [] };
                },
                // The server sends the filtered feed; this filters what is already loaded too, so
                // a press answers at once and the complete list replaces it a moment later.
                feedItems: function () {
                    var feed = this.feed, items = (this.p.activity && this.p.activity.items) || [];
                    return feed ? items.filter(function (item) { return item.type === feed; }) : items;
                },
                feedLabel: function () {
                    var feed = this.feed;
                    var stat = ((this.p.activity && this.p.activity.stats) || []).find(function (s) { return s.type === feed; });
                    return stat ? stat.label : '';
                },
                splitTotal: function () {
                    var o = this.p.overview || {};
                    return (o.win_views || 0) + (o.unidentified_views || 0);
                },
                splitShare: function () {
                    return this.splitTotal ? Math.round(100 * this.p.overview.win_views / this.splitTotal) : 0;
                },
                splitLabel: function () {
                    return this.msg.acceptedCookies + ': ' + this.fmt(this.p.overview.win_views) + ', '
                        + this.msg.notAccepted + ': ' + this.fmt(this.p.overview.unidentified_views);
                },
                // One bar per rolling hour, oldest first. A lone sign-up is half height, so a busier
                // hour still has somewhere to go.
                hourBars: function () {
                    var self = this, hours = this.signups.hours || [];
                    var max = Math.max(2, hours.reduce(function (m, n) { return Math.max(m, n); }, 0));
                    return hours.map(function (count, index) {
                        return {
                            count: count,
                            height: Math.round(100 * count / max),
                            title: self.fmt(count) + ' · ' + self.ago((hours.length - index) * 3600, true),
                        };
                    });
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
                // Which part of the site: one colour and icon each, used wherever a person or a page
                // is shown. Checked as a set (every pair, light and dark, normal vision and simulated
                // red-green colour blindness) and kept clear of the colours this page already gives a
                // meaning: green is live, amber is stuck, blue is the brand. Sign up & log in is a
                // doorway between two of them, so it stays grey. The label is always beside it.
                surfaceTone: function (surface) {
                    return {
                        wp: { bg: 'bg-cyan-50 dark:bg-cyan-500/10', text: 'text-cyan-600' },
                        gp: { bg: 'bg-pink-50 dark:bg-pink-500/10', text: 'text-pink-700 dark:text-pink-600' },
                        ap: { bg: 'bg-purple-50 dark:bg-purple-500/10', text: 'text-purple-600 dark:text-purple-500' },
                    }[surface] || { bg: 'bg-gray-100 dark:bg-gray-700', text: 'text-gray-500 dark:text-gray-400' };
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
                stepShare: function (step) {
                    var total = this.signups.total || 0;
                    return total ? Math.round(100 * step.count / total) : 0;
                },
                // "On the site now" in the feed and the sign-ups opens that person's row, when the
                // Visitors list holds one for them (it may be filtered, or past its row cap).
                canShow: function (id) {
                    return !!(id && this.findPerson(id));
                },
                showPerson: function (id) {
                    var person = id && this.findPerson(id);
                    if (!person) return;
                    if (this.expanded !== person.id) this.toggleExpand(person);
                    var section = document.getElementById('realtime-visitors'), still = false;
                    try { still = window.matchMedia('(prefers-reduced-motion: reduce)').matches; } catch (e) {}
                    if (section) section.scrollIntoView({ behavior: still ? 'auto' : 'smooth', block: 'start' });
                },
                // Unlike changed(), this leaves an open visitor row alone: the feed is another card.
                toggleFeed: function (type) {
                    this.feed = this.feed === type ? null : type;
                    this.activityAll = false;
                    this.syncUrl();
                    clearTimeout(this.debounce);
                    this.debounce = setTimeout(this.poll, 0);
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
                    if (this.feed) params.set('feed', this.feed);
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

                    // A changed feed filter brings a different list: nothing in it is news. A sign-up
                    // row and its feed item share a key, so one entry here highlights both.
                    var refiltered = ((data.activity && data.activity.feed) || null) !== ((this.p.activity && this.p.activity.feed) || null);
                    var seenItems = {};
                    ((this.p.activity && this.p.activity.items) || []).forEach(function (item) { seenItems[item.key] = true; });
                    ((this.p.activity && this.p.activity.signups && this.p.activity.signups.rows) || []).forEach(function (row) { seenItems[row.key] = true; });
                    if (this.p.activity) {
                        if (!refiltered) ((data.activity && data.activity.items) || []).forEach(function (item) { if (!seenItems[item.key]) fresh[item.key] = true; });
                        ((data.activity && data.activity.signups && data.activity.signups.rows) || []).forEach(function (row) { if (!seenItems[row.key]) fresh[row.key] = true; });
                    }

                    // A count that grew gives its button one pulse.
                    var counted = {}, bumped = {};
                    ((this.p.activity && this.p.activity.stats) || []).forEach(function (stat) { counted[stat.type] = stat.count; });
                    ((data.activity && data.activity.stats) || []).forEach(function (stat) {
                        if (counted[stat.type] !== undefined && stat.count > counted[stat.type]) bumped[stat.type] = true;
                    });
                    this.bumped = bumped;
                    clearTimeout(this.bumpTimer);
                    this.bumpTimer = setTimeout(function () { self.bumped = {}; }, 700);

                    // A filter whose kind has aged out of the window has nothing left to show.
                    var feed = this.feed;
                    if (feed && data.activity && !(data.activity.stats || []).some(function (stat) { return stat.type === feed && stat.count > 0; })) {
                        this.feed = null;
                        this.syncUrl();
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
                    // A sign-up is marked on the minute it happened in: a dot in the band above the
                    // plot (layout.padding.top), a dashed line down to that minute's bar, and the
                    // number in the dot when two or more people signed up in the one minute.
                    var signupMarks = {
                        id: 'realtimeSignupMarks',
                        afterDatasetsDraw: function (chart) {
                            var marks = chart.$signupMarks;
                            if (!marks) return;
                            var ctx = chart.ctx, area = chart.chartArea, lower = chart.getDatasetMeta(0).data, upper = chart.getDatasetMeta(1).data;
                            var tint = chart.$markColors || colors, radius = 7, cy = area.top - 11;
                            marks.forEach(function (list, index) {
                                if (!list || !list.length || !lower[index]) return;
                                var x = lower[index].x, barTop = Math.min(lower[index].y, upper[index] ? upper[index].y : area.bottom);
                                ctx.save();
                                ctx.strokeStyle = tint.green; ctx.globalAlpha = 0.55; ctx.lineWidth = 1; ctx.setLineDash([2, 3]);
                                ctx.beginPath(); ctx.moveTo(x, cy + radius + 1); ctx.lineTo(x, Math.max(cy + radius + 1, barTop - 2)); ctx.stroke();
                                ctx.restore();
                                ctx.save();
                                ctx.fillStyle = tint.green; ctx.beginPath(); ctx.arc(x, cy, radius, 0, Math.PI * 2); ctx.fill();
                                ctx.strokeStyle = tint.white; ctx.fillStyle = tint.white; ctx.lineWidth = 1.6; ctx.lineCap = 'round';
                                if (list.length > 1) {
                                    ctx.font = '600 10px ' + (getComputedStyle(document.body).fontFamily || 'sans-serif');
                                    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                                    ctx.fillText(numberFormat.format(list.length), x, cy + 0.5);
                                } else {
                                    ctx.beginPath(); ctx.moveTo(x - 3, cy); ctx.lineTo(x + 3, cy); ctx.moveTo(x, cy - 3); ctx.lineTo(x, cy + 3); ctx.stroke();
                                }
                                ctx.restore();
                            });
                        },
                    };
                    this.chart = new Chart(this.$refs.chart, {
                        type: 'bar',
                        plugins: [signupMarks],
                        data: {
                            labels: this.axisLabels(),
                            datasets: [
                                Object.assign({ label: this.msg.acceptedCookies, data: [], backgroundColor: colors.blue }, bar),
                                Object.assign({ label: this.msg.notAccepted, data: [], backgroundColor: colors.gray }, bar),
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: { duration: 400 },
                            layout: { padding: { top: 22 } },
                            // The whole column answers, not just the bar: a sign-up can fall in a
                            // minute with no page view, and its marker still has to explain itself.
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        title: function (items) {
                                            var index = items[0].dataIndex;
                                            return index === 29 ? self.msg.thisMinute : unit(-(29 - index), 'minute');
                                        },
                                        afterBody: function (items) {
                                            var list = (self.chart && self.chart.$signupMarks || [])[items[0].dataIndex];
                                            return list && list.length ? [''].concat(list) : [];
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
                    this.chart.$signupMarks = this.p.minutes.map(function (m) { return m.signups || []; });
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
                    this.chart.$markColors = colors;
                    // Not update('none'): in that mode Chart.js keeps each bar's shared options, so
                    // the new colours never reached the bars and they stayed in the old palette.
                    this.chart.update();
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
