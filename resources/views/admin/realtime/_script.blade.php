<style {!! nonce_attr() !!}>
    @keyframes realtime-fresh { from { background-color: var(--brand-blue-a10); } to { background-color: transparent; } }
    .realtime-fresh { animation: realtime-fresh 2s ease-out 1; }
    @media (prefers-reduced-motion: reduce) { .realtime-fresh { animation: none; } }

    /* The Trends tiles: a recessed cell, pressed while it is the one the chart draws. On the --ap-*
       tokens, so every palette gets its own shade. */
    .realtime-stat { background: var(--ap-tint-sunken); }
    .realtime-stat:not(:disabled):hover { background: var(--ap-tint-2); }
    .realtime-stat:disabled { cursor: default; }
    .realtime-stat-on, .realtime-stat-on:not(:disabled):hover { background: var(--brand-blue-a10); box-shadow: var(--ap-inset-pressed); }
    /* The Activity counts: one row of chips, pressed while one filters the feed. Plain CSS, like
       every new look here: a utility the built stylesheet lacks draws nothing. */
    .realtime-chip {
        display: inline-flex; align-items: center; gap: 0.375rem;
        padding: 0.25rem 0.625rem; border-radius: 9999px;
        font-size: 0.875rem; line-height: 1.25rem; font-weight: 500;
        color: rgb(var(--ap-ink-2)); background: var(--ap-tint-sunken);
        transition: background-color 0.2s, box-shadow 0.2s;
    }
    .realtime-chip:not(:disabled):hover { background: var(--ap-tint-2); }
    .realtime-chip:disabled { cursor: default; opacity: 0.55; }
    .realtime-chip:focus-visible { outline: 2px solid var(--brand-blue); outline-offset: 1px; }
    .realtime-chip-on, .realtime-chip-on:not(:disabled):hover { color: rgb(var(--ap-ink)); background: var(--brand-blue-a10); box-shadow: var(--ap-inset-pressed); }
    /* One short pulse when a count grows, never a loop. */
    @keyframes realtime-bump { 0% { transform: scale(1); } 35% { transform: scale(1.04); } 100% { transform: scale(1); } }
    .realtime-bump { animation: realtime-bump 0.5s ease-out 1; }
    @media (prefers-reduced-motion: reduce) { .realtime-bump { animation: none; } }

    /* The Right now board (_now): a recessed column per part of the site, pressed while it is the
       Surface filter, and a row per page, tinted while it is the Page filter. */
    .realtime-area { background: var(--ap-tint-sunken); }
    .realtime-area-on { background: var(--brand-blue-a10); box-shadow: var(--ap-inset-pressed); }
    .realtime-area-head:hover { background: var(--ap-tint-2); }
    .realtime-area-on .realtime-area-head:hover { background: transparent; }
    /* While one part of the site is the filter, the others stay on the board, quieter. */
    .realtime-area-dim { opacity: 0.55; transition: opacity 0.2s; }
    .realtime-area-dim:hover, .realtime-area-dim:focus-within { opacity: 1; }
    .realtime-board-row { transition: background-color 0.2s; }
    .realtime-board-row:hover { background: var(--ap-tint-2); }
    .realtime-board-row-on, .realtime-board-row-on:hover { background: var(--brand-blue-a10); }
    /* Who is on a page: overlapping faces, each ringed in the card's colour so the overlap reads.
       Solid, never a translucent tint, or the face beneath shows through. */
    .realtime-face {
        display: inline-flex; align-items: center; justify-content: center; flex: none;
        width: 1.5rem; height: 1.5rem; border-radius: 9999px; overflow: hidden;
        font-size: 0.625rem; font-weight: 600; line-height: 1;
        color: rgb(var(--ap-ink-3)); background: rgb(var(--ap-surface));
        box-shadow: 0 0 0 2px rgb(var(--ap-surface));
    }
    .realtime-face + .realtime-face { margin-inline-start: -0.375rem; }
    /* Initials need their letters: beside a face that carries them, the overlap is a sliver. */
    .realtime-face-user + .realtime-face, .realtime-face + .realtime-face-user { margin-inline-start: -0.125rem; }
    .realtime-face-user { color: var(--brand-blue); background: linear-gradient(var(--brand-blue-a10), var(--brand-blue-a10)), rgb(var(--ap-surface)); }
    button.realtime-face { cursor: pointer; transition: box-shadow 0.2s; }
    button.realtime-face:hover, button.realtime-face:focus-visible { position: relative; z-index: 1; outline: none; box-shadow: 0 0 0 2px var(--brand-blue); }
    /* The board's columns (boardColumns: Sign up & log in stacks under the app, never a column of
       its own), and the Trends tiles: one line of them only where each still has room for a page's
       name or a figure and its change. In a column of two, the lower one takes what is left, so
       the column ends where the others do. */
    .realtime-board { grid-template-columns: minmax(0, 1fr); }
    @media (min-width: 640px) { .realtime-board { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1024px) { .realtime-board.is-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .realtime-board.is-1 { grid-template-columns: minmax(0, 1fr); }
    .realtime-board-col > .realtime-area:last-child { flex: 1 1 auto; }
    /* Three columns in a grid of two would leave the bottom right empty: there the last one runs
       across, its areas side by side. */
    @media (min-width: 640px) and (max-width: 1023.98px) {
        .realtime-board.is-3 > .realtime-board-col:last-child { grid-column: span 2 / span 2; flex-direction: row; }
        .realtime-board.is-3 > .realtime-board-col:last-child > .realtime-area { flex: 1 1 0; }
    }
    /* The Now card's head: the number, page views per minute, and what the number leaves out. One
       row where there is room; stacked below that, the consent split before the chart. */
    .realtime-now-head { display: grid; gap: 1rem 1.5rem; grid-template-columns: minmax(0, 1fr); align-items: center; }
    .realtime-now-consent { order: 1; }
    .realtime-now-chart { order: 2; }
    @media (min-width: 1024px) {
        .realtime-now-head { grid-template-columns: auto minmax(0, 1fr) minmax(0, 18rem); }
        .realtime-now-head.is-quiet { grid-template-columns: auto minmax(0, 1fr); }
        .realtime-now-consent { order: 2; }
        .realtime-now-chart { order: 1; }
    }
    /* The top: the Now card, and on hosted the Revenue card in the corner. From xl they take the
       Today grid's three columns, so the corner card stands over the Activity rail; below xl the
       corner card follows the Now card. */
    @media (min-width: 1280px) {
        .realtime-top[data-corner] { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .realtime-top[data-corner] > :not(.realtime-corner) { grid-column: 1 / span 2; grid-row: 1; }
        .realtime-top[data-corner] > .realtime-corner { grid-column: 3; grid-row: 1; }
    }
    /* In those two thirds the Now card's head has room for its three parts only once the frame is
       near its full width: below that the chart was squeezed to a sliver between the number and
       the consent line, which goes under the two instead. */
    @media (min-width: 1280px) and (max-width: 1599.98px) {
        .realtime-top[data-corner] .realtime-now-head { grid-template-columns: auto minmax(0, 1fr); }
        .realtime-top[data-corner] .realtime-now-consent { grid-column: 1 / -1; }
    }
    /* What stands in a card's place until its numbers arrive. */
    .realtime-skeleton { background: var(--ap-tint-sunken); }
    .realtime-skeleton-card { min-height: 100%; }
    @media (prefers-reduced-motion: no-preference) {
        .realtime-skeleton, .realtime-skeleton-card { animation: realtime-wait 1.6s ease-in-out infinite; }
    }
    @keyframes realtime-wait { 50% { opacity: 0.55; } }
    /* A first poll that was refused never fills the skeleton: the banner above says why, and the
       skeleton stops saying it is on its way. */
    .realtime-now-skeleton.is-still .realtime-skeleton { animation: none; }
    /* Today: Activity first in one column; from xl, Sign-ups over two columns and Activity the
       rail beside it, as tall as Sign-ups. */
    @media (min-width: 1280px) {
        .realtime-today { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .realtime-today > .realtime-signups { grid-column: 1 / span 2; grid-row: 1; }
        .realtime-today > .realtime-rail { grid-column: 3; grid-row: 1; }
        /* The whole feed open: Sign-ups keeps its own height rather than stretching empty beside it. */
        .realtime-today.is-open > .realtime-signups { align-self: start; }
    }
    /* At xl both lists keep their height; what does not fit fades out over "N more". */
    @media (min-width: 1280px) {
        .realtime-clip { overflow: hidden; }
        .realtime-capped { max-height: 26rem; overflow: hidden; }
    }
    .realtime-fade {
        position: absolute; inset-inline: 0; bottom: 0; height: 3.5rem; pointer-events: none;
        background: linear-gradient(to bottom, rgb(var(--ap-card-to) / 0), rgb(var(--ap-card-to)));
    }
    /* Three breakdowns: one column, then Top pages over two with Sources and Countries under it,
       then three in a row, so the grid never has a hole. Three only from 1440: beside the sidebar
       at 1280 a card of three cut every page name short. */
    .realtime-breakdowns { grid-template-columns: minmax(0, 1fr); }
    @media (min-width: 768px) {
        .realtime-breakdowns { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .realtime-breakdowns > :first-child { grid-column: span 2 / span 2; }
    }
    @media (min-width: 1440px) {
        .realtime-breakdowns { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .realtime-breakdowns > :first-child { grid-column: auto; }
    }
    .realtime-now-pill { padding: 0 0.375rem; border-radius: 9999px; background: rgb(var(--ap-surface) / 0.85); }
    /* The Visitors list's columns: who, page, came from, active. */
    .realtime-row { grid-template-columns: minmax(0, 1fr) auto; }
    @media (min-width: 768px) { .realtime-row { grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr) 8rem; } }
    @media (min-width: 1280px) { .realtime-row { grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr) 11rem 8rem; } }
    /* A name cut short keeps its own start: dir="auto" on the element decides which end is cut,
       and this keeps it on the page's side. With a <bdi> inside a right-to-left box, "Lisa
       Simpson Quartet" was cut to "... Simpson Quartet". */
    .realtime-cut { text-align: left; }
    [dir="rtl"] .realtime-cut { text-align: right; }
    .realtime-tiles { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    @media (min-width: 640px) { .realtime-tiles { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (min-width: 1440px) { .realtime-tiles { grid-template-columns: repeat(6, minmax(0, 1fr)); } }
    /* A legend swatch takes the colour of its part of the site from surfaceTone()'s text class. */
    .realtime-swatch { background: currentColor; }
</style>
<script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function () {
        var MSG = @json($realtimeMsg);
        var CONFIG = @json($realtimeConfig);
        var INITIAL = @json($payload);
        var BASE_TITLE = document.title;
        var LOCALE = CONFIG.locale;

        var numberFormat = new Intl.NumberFormat(LOCALE + '-u-nu-latn');
        var percentFormat = null;
        try { percentFormat = new Intl.NumberFormat(LOCALE + '-u-nu-latn', { style: 'percent', maximumFractionDigits: 0, signDisplay: 'exceptZero' }); } catch (e) {}
        var relative = null, regionNames = null, clockFormat = null;
        var relativeExact = null;
        try { relative = new Intl.RelativeTimeFormat(LOCALE, { numeric: 'auto', style: 'short' }); } catch (e) {}
        // "Left now" reads like a contradiction, so a phrase that already says when uses a number.
        try { relativeExact = new Intl.RelativeTimeFormat(LOCALE, { numeric: 'always', style: 'short' }); } catch (e) {}
        try { regionNames = new Intl.DisplayNames([LOCALE], { type: 'region' }); } catch (e) {}
        // In the zone Trends uses (CONFIG.timezone), so the page reads one clock.
        try { clockFormat = new Intl.DateTimeFormat(LOCALE + '-u-nu-latn', { hour: 'numeric', minute: '2-digit', timeZone: CONFIG.timezone }); } catch (e) {
            try { clockFormat = new Intl.DateTimeFormat(LOCALE + '-u-nu-latn', { hour: 'numeric', minute: '2-digit' }); } catch (e2) {}
        }

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
                    feed: (INITIAL.activity && INITIAL.activity.feed) || INITIAL.feed || null,
                    signupsAll: false,
                    bumped: {},
                    expandedCards: { pages: false, sources: false, countries: false },
                    // The country whose block menu is open (_breakdown), or null.
                    blockMenu: null,
                    // The breakdown cards' span: '30m' is the live poll, the rest the hourly totals.
                    cardsRange: CONFIG.cards || '30m',
                    rangeCards: null,
                    // How many rows of each list are out of sight at xl (measureOverflow).
                    hiddenBelow: { feed: 0, signups: 0 },
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
                    // Asked the first time the card nears the screen (watchTrends), never with the page.
                    trends: null,
                    trendRange: CONFIG.trend || '24h',
                    trendsWatched: false,
                    // The card has come near the screen and been asked for (watchTrends).
                    trendsAsked: false,
                    // The corner card's figures (hosted): null until they arrive.
                    revenue: null,
                    trendMetric: 'views',
                    trendsLoading: false,
                    trendTries: 0,
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
                    var query = this.urlParams().toString();
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
                // The live half hour from the poll, or a longer range from the hourly totals
                // (fetchRangeCards), whose rows are counts only and filter nothing.
                cards: function () {
                    var live = this.cardsRange === '30m';
                    var b = live ? (this.p.breakdowns || {}) : (this.rangeCards || {});
                    var views = live ? this.msg.unitViews30 : this.msg.unitViews[this.cardsRange];
                    var visits = live ? this.msg.unitVisits30 : this.msg.unitVisits[this.cardsRange];
                    var note = !live && this.p.filtered ? ' · ' + this.msg.notFiltered : '';
                    var loading = !live && !this.rangeCards;
                    return [
                        { id: 'pages', title: this.msg.topPages, unit: views + note, filter: 'page', rows: b.pages || [], loading: loading },
                        { id: 'sources', title: this.msg.sources, unit: visits + note, filter: 'source', rows: b.sources || [], loading: loading },
                        { id: 'countries', title: this.msg.countries, unit: views + note, filter: 'country', rows: b.countries || [], loading: loading },
                    ];
                },
                cardsShown: function () {
                    return this.cardsRange !== '30m' || this.hasAnyone;
                },
                cardsRangeOptions: function () {
                    var self = this;
                    return ['30m', '24h', '7d', '30d'].map(function (value) { return { value: value, label: value === '30m' ? self.msg.range30m : self.msg.range[value] }; });
                },
                // "Counted from ..." while the totals began inside the range.
                cardsSince: function () {
                    if (this.cardsRange === '30m' || !this.rangeCards || !this.rangeCards.since) return '';
                    try {
                        return this.msg.trendSince.replace(':time', new Intl.DateTimeFormat(CONFIG.locale, { timeZone: CONFIG.timezone, dateStyle: 'medium', timeStyle: 'short' }).format(new Date(this.rangeCards.since)));
                    } catch (e) {
                        return '';
                    }
                },
                // The board's columns: one per part of the site, except Sign up & log in, a doorway
                // with a row or two, which stacks under the app. A fixed rule, never "the shortest",
                // so a column does not move between polls.
                boardColumns: function () {
                    var areas = (this.p.overview && this.p.overview.now_by_surface) || [], columns = [], door = null;
                    areas.forEach(function (area) {
                        if (area.key === 'auth') door = area; else columns.push([area]);
                    });
                    if (door) {
                        var app = columns.find(function (column) { return column[0].key === 'ap'; });
                        if (app) app.push(door); else columns.push([door]);
                    }
                    return columns;
                },
                boardHasAnyone: function () {
                    return ((this.p.overview && this.p.overview.now_by_surface) || []).some(function (area) { return area.count > 0; });
                },
                hasSignupMarks: function () {
                    return (this.p.minutes || []).some(function (minute) { return minute.signups && minute.signups.length; });
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
                rangeOptions: function () {
                    var self = this;
                    return ['24h', '7d', '30d'].map(function (value) { return { value: value, label: self.msg.range[value] }; });
                },
                // One tile per figure, each against the same span before it. A comparison the hourly
                // counts cannot make yet (they began inside that span) says so instead of a number.
                trendTiles: function () {
                    var t = this.trends, self = this;
                    if (!t) return [];
                    var labels = { views: this.msg.views, visits: this.msg.visits, signup: this.msg.tileSignups, schedule: this.msg.tileSchedules, events: this.msg.tileEvents, order: this.msg.tileOrders };
                    // In the 24 hours, the four activity figures are the live counts the Activity
                    // chips show: the same rolling day, polled every ten seconds. This card is asked
                    // every five minutes, and its number ran a sign-up behind the chips beside it.
                    var live = {};
                    if (t.range === '24h') ((this.p.activity && this.p.activity.stats) || []).forEach(function (stat) { live[stat.type] = stat.count; });
                    return ['views', 'visits', 'signup', 'schedule', 'events', 'order'].map(function (key) {
                        var before = t.previous[key], fresh = live[key] !== undefined;
                        var value = fresh ? live[key] : t.totals[key];
                        return {
                            key: key,
                            label: labels[key],
                            value: value,
                            change: fresh ? (before ? Math.round(100 * (value - before) / before) : null) : t.change[key],
                            before: before === null ? self.msg.noComparison : self.msg.before[t.range].replace(':count', self.fmt(before)),
                        };
                    });
                },
                trendIsTraffic: function () {
                    return this.trendMetric === 'views' || this.trendMetric === 'visits';
                },
                trendChartLabel: function () {
                    var metric = this.trendMetric, tile = this.trendTiles.find(function (t) { return t.key === metric; });
                    return (tile ? tile.label : '') + ', ' + (this.msg.range[this.trendRange] || '');
                },
                chartSummary: function () {
                    var total = (this.p.minutes || []).reduce(function (sum, m) { return sum + m.consented + m.unidentified; }, 0);
                    return this.msg.viewsPerMinute + ': ' + this.fmt(total);
                },
            },

            methods: {
                fmt: function (n) { return numberFormat.format(n || 0); },
                nMore: function (n) { return this.msg.nMore.replace(':count', this.fmt(n)); },
                ago: function (secs, exact) {
                    secs = Math.max(0, secs || 0);
                    var format = exact ? relativeExact : relative;
                    if (!format) return this.duration(secs);
                    var text;
                    if (secs < 45) text = exact ? format.format(-Math.max(1, secs), 'second') : format.format(0, 'second');
                    else if (secs < 3600) text = format.format(-Math.round(secs / 60), 'minute');
                    else if (secs < 86400) text = format.format(-Math.round(secs / 3600), 'hour');
                    else text = format.format(-Math.round(secs / 86400), 'day');
                    // Hebrew's short forms carry the number again in brackets ("לפני שע׳ (1)").
                    return text.replace(/\s*[\u200e\u200f]?\(\d+\)[\u200e\u200f]?\s*$/, '');
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
                    if (['order', 'gift_card', 'upgrade', 'resume', 'boost'].indexOf(type) >= 0) return tones.green;
                    if (['cancel', 'deleted', 'schedule_deleted', 'account_deleted'].indexOf(type) >= 0) return tones.gray;
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
                // The Right now board: the pages of one part of the site, and the "N more pages" line.
                boardPages: function (surface) {
                    var board = this.p.overview && this.p.overview.now_board;
                    return (board && board[surface] && board[surface].pages) || [];
                },
                boardMore: function (surface) {
                    var board = this.p.overview && this.p.overview.now_board;
                    return (board && board[surface] && board[surface].more_text) || '';
                },
                faceTitle: function (face) {
                    return face.kind === 'user' ? (face.name || '') : (this.countryName(face.country) || this.msg.anonymous);
                },
                pct: function (n) {
                    return percentFormat ? percentFormat.format(n / 100) : (n > 0 ? '+' : '') + n + '%';
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
                setFeed: function (type) {
                    if (this.feed === type) return;
                    this.toggleFeed(type === null ? this.feed : type);
                },
                // At xl the feed and the sign-ups keep their height: count the rows out of sight, for
                // the fade and "N more". Measured again once when the count changes, because the
                // button it brings takes room from the list.
                measureOverflow: function (again) {
                    var wide = false;
                    try { wide = window.matchMedia('(min-width: 1280px)').matches; } catch (e) {}
                    var below = function (list) {
                        if (!wide || !list || list.offsetParent === null) return 0;
                        var bottom = list.getBoundingClientRect().bottom + 1, n = 0;
                        Array.prototype.forEach.call(list.children, function (child) {
                            if (child.offsetParent !== null && child.getBoundingClientRect().bottom > bottom) n++;
                        });
                        return n;
                    };
                    var next = {
                        feed: this.activityAll ? 0 : below(this.$refs.feedList),
                        signups: this.signupsAll ? 0 : below(this.$refs.signupList),
                    };
                    if (next.feed === this.hiddenBelow.feed && next.signups === this.hiddenBelow.signups) return;
                    this.hiddenBelow = next;
                    if (!again) this.$nextTick(function () { this.measureOverflow(true); });
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
                // The page's own address also keeps the Trends range, which the poll does not need.
                urlParams: function () {
                    var params = this.params();
                    if (this.trendRange !== '24h') params.set('trend', this.trendRange);
                    if (this.cardsRange !== '30m') params.set('cards', this.cardsRange);
                    return params;
                },
                setCardsRange: function (range) {
                    if (range === this.cardsRange) return;
                    this.cardsRange = range;
                    this.rangeCards = null;
                    this.blockMenu = null;
                    this.syncUrl();
                    if (range !== '30m') this.fetchRangeCards();
                },
                // Its own address, like Trends: the totals move by the hour, so a range change asks
                // at once and otherwise the cards are refreshed every five minutes.
                fetchRangeCards: function () {
                    var self = this, range = this.cardsRange;
                    if (range === '30m' || !CONFIG.breakdownsUrl) return;
                    fetch(CONFIG.breakdownsUrl + '?range=' + encodeURIComponent(range), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    }).then(function (r) {
                        if (!r.ok) throw r;
                        return r.json();
                    }).then(function (data) {
                        if (data && data.range === self.cardsRange) self.rangeCards = data;
                    }).catch(function () {
                        // The cards keep what they have; the poll's status line says when the page
                        // has lost its connection.
                    });
                },
                syncUrl: function () {
                    var query = this.urlParams().toString();
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
                // "Until Nov 2, 2026" in the page's clock, or "Until removed".
                refusedUntil: function (row) {
                    if (!row.blocked_until) return this.msg.refusedForever;
                    try {
                        return this.fill(this.msg.refusedUntil, new Intl.DateTimeFormat(CONFIG.locale, { timeZone: CONFIG.timezone, dateStyle: 'medium' }).format(new Date(row.blocked_until)));
                    } catch (e) {
                        return this.fill(this.msg.refusedUntil, row.blocked_until.slice(0, 10));
                    }
                },
                closeBlockMenu: function (key) {
                    this.blockMenu = null;
                    this.$nextTick(function () {
                        var toggle = document.querySelector('[aria-controls="realtime-block-' + key + '"]');
                        if (toggle) toggle.focus();
                    });
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

                    var first = this.p.state === 'loading';
                    this.p = data;
                    this.fresh = fresh;
                    clearTimeout(this.freshTimer);
                    this.freshTimer = setTimeout(function () { self.fresh = {}; }, 2100);
                    this.$nextTick(function () {
                        self.measureOverflow();
                        self.watchTrends();
                        if (first) self.announceDrawn();
                        if (self.$refs.trendChart && (!self.trendChart || self.trendChart.canvas !== self.$refs.trendChart)) self.initTrendChart();
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
                setRange: function (range) {
                    if (range === this.trendRange) return;
                    this.trendRange = range;
                    this.syncUrl();
                    this.fetchTrends();
                },
                setMetric: function (metric) {
                    this.trendMetric = metric;
                    this.updateTrendChart();
                },
                // Its own address and its own pace: the figures move by the hour, so a range change
                // asks at once and otherwise the card is refreshed every five minutes.
                fetchTrends: function (quietly) {
                    var self = this, range = this.trendRange;
                    if (!CONFIG.trendsUrl) return;
                    if (!quietly) this.trendsLoading = true;
                    fetch(CONFIG.trendsUrl + '?range=' + encodeURIComponent(range), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    }).then(function (r) {
                        if (!r.ok) throw r;
                        return r.json();
                    }).then(function (data) {
                        if (!data || data.range !== self.trendRange) return;
                        self.trends = data;
                        self.$nextTick(function () { self.initTrendChart(); });
                    }).catch(function () {
                        // The card keeps what it has; the poll's own status line says when the page
                        // has lost its connection.
                    }).finally(function () {
                        if (range === self.trendRange) self.trendsLoading = false;
                    });
                },
                // The cards under the live part wait for it to be drawn (admin/dashboard/_cards-script):
                // while it is a skeleton the page is short, and every panel looked near the screen.
                announceDrawn: function () {
                    window.adminDashboardLiveDrawn = true;
                    window.dispatchEvent(new CustomEvent('admin-dashboard:live-drawn'));
                },
                // Trends is asked the first time its card comes near the viewport,
                // not with the page: the card exists once the first poll has answered, so this
                // runs after each one until it has an observer to hand the card to.
                watchTrends: function () {
                    var self = this, card = this.$refs.trendsCard;
                    if (this.trendsWatched || !card) return;
                    this.trendsWatched = true;
                    if (!('IntersectionObserver' in window)) { this.trendsAsked = true; this.fetchTrends(); return; }
                    var observer = new IntersectionObserver(function (entries) {
                        if (!entries.some(function (entry) { return entry.isIntersecting; })) return;
                        observer.disconnect();
                        self.trendsAsked = true;
                        self.fetchTrends();
                    }, { rootMargin: '0px 0px 200px 0px' });
                    observer.observe(card);
                },
                // The corner card (hosted). A failure keeps what it had, or says so if it had nothing.
                fetchRevenue: function () {
                    var self = this;
                    if (!CONFIG.revenueUrl) return;
                    fetch(CONFIG.revenueUrl, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    }).then(function (r) {
                        if (!r.ok) throw r;
                        return r.json();
                    }).then(function (data) {
                        // A refresh that could not be read keeps the figures already shown.
                        if (data && data.failed && self.revenue && !self.revenue.failed) return;
                        self.revenue = data;
                    }).catch(function () {
                        if (!self.revenue) self.revenue = { failed: true };
                    });
                },
                trendColors: function () {
                    var self = this, colors = { blue: chartColors().blue };
                    ['wp', 'gp', 'ap', 'auth'].forEach(function (surface) {
                        colors[surface] = surface === 'auth' ? probe('text-gray-400 dark:text-gray-500', 'color') : probe(self.surfaceTone(surface).text, 'color');
                    });
                    return colors;
                },
                initTrendChart: function () {
                    var self = this, canvas = this.$refs.trendChart;
                    if (!this.trends) return;
                    if (typeof Chart === 'undefined' || !canvas) {
                        if (this.trendTries++ < 100) setTimeout(this.initTrendChart, 50);
                        return;
                    }
                    if (this.trendChart && this.trendChart.canvas === canvas) { this.updateTrendChart(); return; }
                    if (this.trendChart) { try { this.trendChart.destroy(); } catch (e) {} }
                    var colors = chartColors();
                    this.trendChart = new Chart(canvas, {
                        type: 'bar',
                        data: { labels: [], datasets: [] },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    filter: function (item) { return item.raw !== null && item.raw !== undefined; },
                                    callbacks: {
                                        title: function (items) {
                                            var bucket = self.trends && self.trends.buckets[items[0].dataIndex];
                                            return bucket ? bucket.title : '';
                                        },
                                        label: function (item) {
                                            return (item.dataset.label ? item.dataset.label + ': ' : '') + numberFormat.format(item.raw || 0);
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
                                    ticks: { color: colors.ink, font: { size: 11 }, maxRotation: 0, autoSkip: true, autoSkipPadding: 14 },
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    suggestedMax: 5,
                                    position: CONFIG.rtl ? 'right' : 'left',
                                    grid: { color: colors.grid },
                                    border: { display: false },
                                    ticks: { color: colors.ink, font: { size: 11 }, precision: 0, maxTicksLimit: 4 },
                                },
                            },
                        },
                    });
                    this.updateTrendChart();
                },
                // Page views and visits are stacked by part of the site, the rest is one bar. The
                // bar still filling (this hour, today) is drawn lighter, so it is not read as a drop.
                updateTrendChart: function () {
                    var chart = this.trendChart, t = this.trends, self = this;
                    if (!chart || !t) return;
                    var colors = this.trendColors(), metric = this.trendMetric;
                    var last = t.buckets.length - 1;
                    // The brand blue arrives as hex, the area colours as rgb().
                    var fade = function (color) {
                        var value = (color || '').trim(), match = /^rgba?\(([^)]+)\)$/.exec(value);
                        if (match) return 'rgba(' + match[1].split(',').slice(0, 3).join(',') + ', 0.45)';
                        var hex = /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.exec(value);
                        if (!hex) return value;
                        var digits = hex[1].length === 3 ? hex[1].replace(/./g, '$&$&') : hex[1];
                        var rgb = [0, 2, 4].map(function (at) { return parseInt(digits.substr(at, 2), 16); });
                        return 'rgba(' + rgb.join(',') + ', 0.45)';
                    };
                    var shade = function (color) {
                        return t.buckets.map(function (bucket, index) { return index === last && bucket.current ? fade(color) : color; });
                    };
                    var bar = { borderRadius: 3, borderSkipped: 'start', barPercentage: 0.85, categoryPercentage: 0.9, maxBarThickness: 28 };
                    chart.data.labels = t.buckets.map(function (bucket) { return bucket.label; });
                    if (metric === 'views' || metric === 'visits') {
                        chart.data.datasets = t.surfaces.map(function (surface) {
                            return Object.assign({
                                label: self.msg.surface[surface],
                                backgroundColor: shade(colors[surface]),
                                data: t.buckets.map(function (bucket) { return bucket[metric] ? bucket[metric][surface] : null; }),
                            }, bar);
                        });
                    } else {
                        var tile = this.trendTiles.find(function (one) { return one.key === metric; });
                        chart.data.datasets = [Object.assign({
                            label: tile ? tile.label : '',
                            backgroundColor: shade(colors.blue),
                            data: t.buckets.map(function (bucket) { return bucket[metric]; }),
                        }, bar)];
                    }
                    var ink = chartColors();
                    chart.options.scales.x.ticks.color = ink.ink;
                    chart.options.scales.y.ticks.color = ink.ink;
                    chart.options.scales.y.grid.color = ink.grid;
                    chart.update();
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
                        // While the first poll is out there is no canvas yet; apply() builds it.
                        if (this.p.state !== 'off' && this.p.state !== 'loading' && this.chartTries++ < 100) setTimeout(this.initChart, 50);
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
                    this.updateTrendChart();
                },
            },

            mounted: function () {
                var self = this;
                this.initChart();
                this.updateTitle();
                this.fetchRevenue();
                // The live part is not in the page: ask for it at once, then every ten seconds.
                // With tracking off there is nothing to ask, and the page is drawn already.
                if (this.p.state === 'loading') this.poll(); else { this.schedule(); this.$nextTick(this.announceDrawn); }
                this.$nextTick(function () { self.measureOverflow(); });
                window.addEventListener('resize', function () { self.measureOverflow(); });
                this.$watch('activityAll', function () { self.$nextTick(function () { self.measureOverflow(); }); });
                this.$watch('signupsAll', function () { self.$nextTick(function () { self.measureOverflow(); }); });

                if (this.cardsRange !== '30m') this.fetchRangeCards();

                // A click anywhere but the open block menu closes it (the menu stops its own clicks).
                document.addEventListener('click', function () { self.blockMenu = null; });

                setInterval(function () { self.nowMs = Date.now(); }, 5000);
                setInterval(function () {
                    if (document.hidden || self.status !== 'live') return;
                    // A card asked once and refused (a 429, a blip) is asked again here, or it would
                    // stay a skeleton until somebody pressed a range.
                    if (self.trends || self.trendsAsked) self.fetchTrends(!!self.trends);
                    if (self.cardsRange !== '30m') self.fetchRangeCards();
                    self.fetchRevenue();
                }, 300000);

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
