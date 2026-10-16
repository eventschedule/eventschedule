{{-- The page's script. The filters reload the page with the new selection; the event picker is
     a small Vue app (it searches a list of the schedule's events); everything else is plain DOM on
     purpose. The cards hold names somebody else wrote (an event, a site, a campaign), and a Vue
     mount over them would compile those names as a template. So the switches in a card and "Show
     all" are a few listeners, and the charts are drawn from the plain data each tab pushes onto
     window.anCharts. --}}
<script {!! nonce_attr() !!}>
(function () {
    var go = function (mutate) {
        var url = new URL(window.location.href);
        mutate(url.searchParams);
        window.location.href = url.toString();
    };
    var roleFilter = document.getElementById('role-filter');
    if (roleFilter) {
        roleFilter.addEventListener('change', function () {
            var value = this.value;
            go(function (q) {
                value ? q.set('role_id', value) : q.delete('role_id');
                q.delete('event_id');
            });
        });
    }
    var dateRange = document.getElementById('date-range');
    if (dateRange) {
        dateRange.addEventListener('change', function () {
            var value = this.value;
            go(function (q) { q.set('range', value); });
        });
    }

    // A switch inside a card: one panel at a time, arrows move along it.
    document.querySelectorAll('.an [role="tablist"]').forEach(function (list) {
        var tabs = Array.prototype.slice.call(list.querySelectorAll('[data-an-tab]'));
        var select = function (tab, focus) {
            tabs.forEach(function (t) {
                var on = t === tab;
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                t.tabIndex = on ? 0 : -1;
                var panel = document.getElementById(t.getAttribute('aria-controls'));
                if (panel) {
                    panel.hidden = ! on;
                }
            });
            if (focus) {
                tab.focus();
            }
        };
        tabs.forEach(function (tab, i) {
            tab.tabIndex = tab.getAttribute('aria-selected') === 'true' ? 0 : -1;
            tab.addEventListener('click', function () { select(tab, false); });
            tab.addEventListener('keydown', function (e) {
                var rtl = document.documentElement.dir === 'rtl';
                var step = { ArrowRight: rtl ? -1 : 1, ArrowLeft: rtl ? 1 : -1 }[e.key];
                if (step) {
                    e.preventDefault();
                    select(tabs[(i + step + tabs.length) % tabs.length], true);
                }
            });
        });
    });

    // "Show all": the rest of a list is in the page already.
    document.querySelectorAll('.an [data-an-more]').forEach(function (button) {
        var list = button.previousElementSibling;
        var more = button.firstChild.textContent;
        button.addEventListener('click', function () {
            var open = button.getAttribute('aria-expanded') !== 'true';
            list.querySelectorAll('[data-an-extra]').forEach(function (row) { row.hidden = ! open; });
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
            button.firstChild.textContent = open ? button.dataset.less + ' ' : more;
        });
    });
})();

@if ($selectedRoleId && $tab !== 'realtime')
(function () {
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                events: @json($events),
                selectedEventId: @json($selectedEventId ? \App\Utils\UrlUtils::encodeId($selectedEventId) : ''),
                dropdownOpen: false,
                searchQuery: '',
                highlightedIndex: 0,
            };
        },
        computed: {
            filteredEvents() {
                if (!this.searchQuery) return this.events;
                const q = this.normalize(this.searchQuery).trim();
                if (!q) return this.events;
                const startsWith = [];
                const contains = [];
                for (const e of this.events) {
                    const i = this.normalize(e.name).indexOf(q);
                    if (i === 0) startsWith.push(e);
                    else if (i > 0) contains.push(e);
                }
                return [...startsWith, ...contains];
            },
        },
        watch: {
            searchQuery(val) {
                this.highlightedIndex = val ? 0 : (this.selectedEventId ? 0 : -1);
            },
        },
        methods: {
            normalize(s) {
                return (s || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
            },
            highlightMatch(name) {
                const safe = (name || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
                if (!this.searchQuery) return safe;
                const nName = this.normalize(name);
                const nQuery = this.normalize(this.searchQuery).trim();
                if (!nQuery) return safe;
                const idx = nName.indexOf(nQuery);
                if (idx < 0 || idx + nQuery.length > safe.length) return safe;
                return safe.substring(0, idx) +
                    '<mark class="bg-yellow-200 dark:bg-yellow-500/30 text-inherit rounded-sm px-0.5">' +
                    safe.substring(idx, idx + nQuery.length) +
                    '</mark>' +
                    safe.substring(idx + nQuery.length);
            },
            scrollHighlightedIntoView() {
                this.$nextTick(() => {
                    const el = document.querySelector('#event-selector-dropdown [data-event-idx="' + this.highlightedIndex + '"]');
                    if (el && typeof el.scrollIntoView === 'function') {
                        el.scrollIntoView({ block: 'nearest' });
                    }
                });
            },
            moveHighlight(delta) {
                const minIdx = this.searchQuery ? 0 : -1;
                const total = (this.searchQuery ? 0 : 1) + this.filteredEvents.length;
                if (total === 0) return;
                const shifted = ((this.highlightedIndex - minIdx) + delta + total) % total;
                this.highlightedIndex = shifted + minIdx;
                this.scrollHighlightedIntoView();
            },
            selectHighlighted() {
                if (!this.searchQuery && this.highlightedIndex === -1) {
                    this.onEventChange('');
                    return;
                }
                const ev = this.filteredEvents[this.highlightedIndex];
                if (ev) this.onEventChange(ev.id);
            },
            toggleDropdown() {
                if (this.dropdownOpen) {
                    this.closeDropdown();
                    return;
                }
                this.dropdownOpen = true;
                this.searchQuery = '';
                const selIdx = this.events.findIndex(e => e.id === this.selectedEventId);
                this.highlightedIndex = selIdx >= 0 ? selIdx : (this.selectedEventId ? 0 : -1);
                this.$nextTick(() => {
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                    this.scrollHighlightedIntoView();
                });
            },
            closeDropdown() {
                this.dropdownOpen = false;
                this.searchQuery = '';
            },
            onEventChange(eventId) {
                this.selectedEventId = eventId;
                this.closeDropdown();
                const url = new URL(window.location.href);
                if (eventId) {
                    url.searchParams.set('event_id', eventId);
                } else {
                    url.searchParams.delete('event_id');
                }
                window.location.href = url.toString();
            },
        },
        mounted() {
            document.addEventListener('click', (e) => {
                const el = document.getElementById('event-selector-dropdown');
                if (el && !el.contains(e.target)) {
                    this.closeDropdown();
                }
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeDropdown();
                }
            });
        },
    }).mount('#event-picker-app');
})();
@endif

// A date the server printed in one fixed order ("Oct 6, 2026"), written again in the reader's own:
// the weekday and the day for a day, the week's span, the month and year.
(function () {
    // Western digits, as every figure the server prints and the Realtime tab: in Arabic the bare
    // locale gave Arabic-Indic ones beside them.
    var locale = (document.documentElement.lang || 'en') + '-u-nu-latn';
    document.querySelectorAll('.an [data-an-date]').forEach(function (el) {
        try {
            var p = el.dataset.anDate.split('-').map(Number), period = el.dataset.anPeriod, d, text;
            if (period === 'monthly') {
                text = new Intl.DateTimeFormat(locale, { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(p[0], p[1] - 1, 1)));
            } else if (period === 'weekly') {
                var jan4 = new Date(Date.UTC(p[0], 0, 4));
                d = new Date(Date.UTC(p[0], 0, 4 - ((jan4.getUTCDay() || 7) - 1) + (p[1] - 1) * 7));
                var f = new Intl.DateTimeFormat(locale, { month: 'short', day: 'numeric', timeZone: 'UTC' });
                text = f.formatRange ? f.formatRange(d, new Date(d.getTime() + 6 * 864e5)) : f.format(d);
            } else {
                text = new Intl.DateTimeFormat(locale, { weekday: 'short', month: 'short', day: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(p[0], p[1] - 1, p[2])));
            }
            el.textContent = text;
        } catch (e) {}
    });
})();

// The charts. Their colours come from the page's own tokens, read once, so a palette and dark
// mode follow; marks are thin, the grid is a hairline on one axis only, the tooltip lists every
// series at the date under the pointer, and a hairline follows the pointer.
(function () {
    var queue = window.anCharts || [];
    if (! queue.length) {
        return;
    }
    var start = function () {
        if (typeof Chart === 'undefined') {
            setTimeout(start, 50);
            return;
        }
        var root = document.querySelector('.an') || document.documentElement;
        var css = getComputedStyle(root);
        var tok = function (name) { return css.getPropertyValue(name).trim(); };
        var rgb = function (name) { return 'rgb(' + tok(name) + ')'; };
        var alpha = function (color, a) {
            var m = color.match(/^#([0-9a-f]{6})$/i);
            if (m) {
                var n = parseInt(m[1], 16);
                return 'rgba(' + (n >> 16) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + a + ')';
            }
            var r = color.match(/rgba?\(([^)]+)\)/);
            if (r) {
                var parts = r[1].replace(/\//g, ' ').split(/[\s,]+/).filter(Boolean).slice(0, 3);
                return 'rgba(' + parts.join(',') + ',' + a + ')';
            }
            return color;
        };
        var tones = { views: tok('--an-views'), previous: tok('--an-previous'), boost: tok('--an-boost'), newsletter: tok('--an-newsletter') };
        var ink = rgb('--ap-ink'), ink2 = rgb('--ap-ink-2'), ink3 = rgb('--ap-ink-3');
        var surface = rgb('--ap-surface'), border = rgb('--ap-border'), strong = rgb('--ap-border-strong');
        var hairline = tok('--ap-hairline');
        var rtl = document.documentElement.dir === 'rtl';
        var still = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        var utc = function (y, m, d) { return new Date(Date.UTC(y, m, d)); };
        var dateOf = function (key, period) {
            var p = String(key).split('-').map(Number);
            if (period === 'monthly') return utc(p[0], p[1] - 1, 1);
            if (period === 'weekly') {
                var jan4 = utc(p[0], 0, 4);
                var monday = utc(p[0], 0, 4 - ((jan4.getUTCDay() || 7) - 1) + (p[1] - 1) * 7);
                return monday;
            }
            return utc(p[0], p[1] - 1, p[2]);
        };
        var label = function (key, period, locale, long) {
            if (period === 'hourly') {
                return new Intl.DateTimeFormat(locale, { hour: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(2020, 0, 1, Number(key))));
            }
            var d = dateOf(key, period);
            if (period === 'monthly') {
                return new Intl.DateTimeFormat(locale, { month: long ? 'long' : 'short', year: 'numeric', timeZone: 'UTC' }).format(d);
            }
            var f = new Intl.DateTimeFormat(locale, { month: 'short', day: 'numeric', timeZone: 'UTC' });
            if (period === 'weekly' && long) {
                var end = new Date(d.getTime() + 6 * 864e5);
                return f.formatRange ? f.formatRange(d, end) : f.format(d);
            }
            if (long) {
                return new Intl.DateTimeFormat(locale, { weekday: 'short', month: 'short', day: 'numeric', timeZone: 'UTC' }).format(d);
            }
            return f.format(d);
        };

        var crosshair = {
            id: 'anCrosshair',
            beforeDatasetsDraw: function (chart) {
                var active = chart.tooltip && chart.tooltip.getActiveElements();
                if (! active || ! active.length) return;
                var x = active[0].element.x, area = chart.chartArea, ctx = chart.ctx;
                ctx.save();
                ctx.strokeStyle = strong;
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(Math.round(x) + 0.5, area.top);
                ctx.lineTo(Math.round(x) + 0.5, area.bottom);
                ctx.stroke();
                ctx.restore();
            },
        };

        // The highest point of the main line, with its number: the one value worth printing.
        var peak = {
            id: 'anPeak',
            afterDatasetsDraw: function (chart) {
                var ds = chart.data.datasets[0], meta = chart.getDatasetMeta(0);
                if (! chart.$anCount || ! ds || chart.config.type === 'bar' || ds.data.length < 3) return;
                var max = Math.max.apply(null, ds.data);
                if (! (max > 0)) return;
                var i = ds.data.indexOf(max), pt = meta.data[i];
                if (! pt) return;
                var ctx = chart.ctx;
                ctx.save();
                ctx.beginPath();
                ctx.arc(pt.x, pt.y, 4, 0, Math.PI * 2);
                ctx.fillStyle = ds.borderColor;
                ctx.strokeStyle = surface;
                ctx.lineWidth = 2;
                ctx.fill();
                ctx.stroke();
                ctx.font = '600 11px ' + getComputedStyle(document.body).fontFamily;
                ctx.fillStyle = ink2;
                ctx.textAlign = pt.x > chart.chartArea.right - 24 ? 'right' : (pt.x < chart.chartArea.left + 24 ? 'left' : 'center');
                ctx.textBaseline = 'bottom';
                ctx.fillText(chart.$anCount(max), pt.x, pt.y - 8);
                ctx.restore();
            },
        };
        // The events of the window: a flyer (or a dot) under the date each falls on, so a rise
        // reads against the show it led up to. Several on one date stand side by side.
        var images = {};
        var marks = {
            id: 'anMarks',
            afterDraw: function (chart) {
                var events = chart.$anEvents;
                if (! events || ! events.length) return;
                var x = chart.scales.x, ctx = chart.ctx, y = x.bottom + 15, r = 11;
                var byKey = {};
                events.forEach(function (e) { (byKey[e.key] = byKey[e.key] || []).push(e); });
                Object.keys(byKey).forEach(function (key) {
                    var index = chart.data.labels.indexOf(key);
                    if (index < 0) return;
                    var cx = x.getPixelForValue(index), list = byKey[key].slice(0, 3);
                    list.forEach(function (e, n) {
                        var px = cx + (n - (list.length - 1) / 2) * (r * 1.5);
                        ctx.save();
                        ctx.beginPath();
                        ctx.arc(px, y, r + 2, 0, Math.PI * 2);
                        ctx.fillStyle = surface;
                        ctx.fill();
                        ctx.beginPath();
                        ctx.arc(px, y, r, 0, Math.PI * 2);
                        var img = e.image && images[e.image];
                        if (img && img.complete && img.naturalWidth) {
                            ctx.clip();
                            var side = Math.min(img.naturalWidth, img.naturalHeight);
                            ctx.drawImage(img, (img.naturalWidth - side) / 2, (img.naturalHeight - side) / 2, side, side, px - r, y - r, r * 2, r * 2);
                        } else {
                            ctx.fillStyle = tones.views;
                            ctx.fill();
                        }
                        ctx.restore();
                        ctx.save();
                        ctx.beginPath();
                        ctx.arc(px, y, r, 0, Math.PI * 2);
                        ctx.strokeStyle = tones.views;
                        ctx.lineWidth = 1.5;
                        ctx.stroke();
                        ctx.restore();
                    });
                });
            },
        };

        // One chart that fails must not take the others with it.
        queue.forEach(function (item) { try { draw(item); } catch (e) { if (window.console) console.error(e); } });

        function draw(item) {
            var canvas = document.getElementById(item.canvas);
            if (! canvas) return;
            var d = item.data, locale = (d.locale || 'en') + '-u-nu-latn', period = d.period, bar = item.kind === 'bar';
            var count = function (v) { return new Intl.NumberFormat(locale).format(v); };
            // minimumFractionDigits with every maximum: a currency's own minimum (2) above the
            // maximum asked for is a RangeError in older engines.
            var money = d.currency ? new Intl.NumberFormat(locale, { style: 'currency', currency: d.currency, minimumFractionDigits: 0, maximumFractionDigits: 0 }) : null;
            var moneyExact = d.currency ? new Intl.NumberFormat(locale, { style: 'currency', currency: d.currency }) : null;
            var compact = d.currency
                ? new Intl.NumberFormat(locale, { style: 'currency', currency: d.currency, notation: 'compact', minimumFractionDigits: 0, maximumFractionDigits: 1 })
                : new Intl.NumberFormat(locale, { notation: 'compact', minimumFractionDigits: 0, maximumFractionDigits: 1 });
            var few = d.keys.length < 3;
            var last = d.keys.length - 1;
            var events = d.events || [];
            var eventsAt = {};
            events.forEach(function (e) { (eventsAt[e.key] = eventsAt[e.key] || []).push(e.name); });

            var datasets = d.series.map(function (s) {
                var color = tones[s.tone] || tones.views;
                var main = s.id === d.series[0].id;
                if (bar && s.id === 'previous') {
                    return {
                        type: 'line', id: s.id, label: s.label, data: s.values,
                        borderColor: color, backgroundColor: 'transparent', borderWidth: 1.5,
                        tension: 0.35, cubicInterpolationMode: 'monotone', pointRadius: 0, pointHoverRadius: 3.5,
                        pointHoverBackgroundColor: color, pointHoverBorderColor: surface, pointHoverBorderWidth: 2, order: 0,
                    };
                }
                if (bar) {
                    return {
                        id: s.id, label: s.label, data: s.values, order: 1,
                        backgroundColor: s.values.map(function (v, i) { return alpha(color, d.partial && i === last && ! few ? 0.4 : 0.85); }),
                        hoverBackgroundColor: color, borderColor: color,
                        borderRadius: { topLeft: 4, topRight: 4 }, borderSkipped: 'bottom',
                        maxBarThickness: 24, categoryPercentage: 0.8, barPercentage: 0.9,
                    };
                }
                return {
                    id: s.id, label: s.label, data: s.values,
                    borderColor: color,
                    borderWidth: main ? 2 : (s.id === 'previous' ? 1.5 : 1.75),
                    backgroundColor: main ? function (ctx) {
                        var area = ctx.chart.chartArea;
                        if (! area) return alpha(color, 0.1);
                        var g = ctx.chart.ctx.createLinearGradient(0, area.top, 0, area.bottom);
                        g.addColorStop(0, alpha(color, 0.2));
                        g.addColorStop(1, alpha(color, 0));
                        return g;
                    } : 'transparent',
                    fill: main ? 'origin' : false,
                    segment: {
                        borderDash: function (ctx) { return d.partial && main && ctx.p1DataIndex === last ? [4, 4] : undefined; },
                    },
                    tension: 0.35,
                    cubicInterpolationMode: 'monotone',
                    pointRadius: few ? 3 : 0,
                    pointBackgroundColor: color,
                    pointHoverRadius: 4.5,
                    pointHoverBorderWidth: 2,
                    pointHoverBorderColor: surface,
                    pointHoverBackgroundColor: color,
                    order: main ? 0 : (s.id === 'previous' ? 3 : 1),
                };
            });

            var drawn = {};
            events.forEach(function (e) {
                drawn[e.key] = (drawn[e.key] || 0) + 1;
                if (drawn[e.key] <= 3 && e.image && ! images[e.image]) {
                    var img = new Image();
                    img.onload = function () { Chart.instances && Object.values(Chart.instances).forEach(function (c) { c.draw(); }); };
                    img.src = e.image;
                    images[e.image] = img;
                }
            });
            var chart = new Chart(canvas.getContext('2d'), {
                type: bar ? 'bar' : 'line',
                data: { labels: d.keys, datasets: datasets },
                plugins: bar ? [crosshair, marks] : [crosshair, peak, marks],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: still ? false : { duration: 500, easing: 'easeOutQuart' },
                    interaction: { mode: 'index', intersect: false },
                    layout: { padding: { top: 18, bottom: events.length ? 32 : 0 } },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            rtl: rtl,
                            backgroundColor: surface,
                            borderColor: border,
                            borderWidth: 1,
                            titleColor: ink3,
                            titleFont: { size: 12, weight: '500' },
                            bodyColor: ink,
                            bodyFont: { size: 13, weight: '600' },
                            footerColor: ink2,
                            footerFont: { size: 12, weight: '500' },
                            footerMarginTop: 8,
                            padding: { x: 12, y: 10 },
                            cornerRadius: 10,
                            boxWidth: 12,
                            boxHeight: 2,
                            boxPadding: 6,
                            caretSize: 0,
                            displayColors: d.series.length > 1,
                            filter: function (ctx) { return ! (bar && ctx.dataset.id === 'previous' && ! ctx.parsed.y && ! ctx.chart.data.datasets[0].data[ctx.dataIndex]); },
                            callbacks: {
                                title: function (items) {
                                    if (! items.length) return '';
                                    var i = items[0].dataIndex, text = label(d.keys[i], period, locale, true);
                                    return d.partial && i === last && d.soFar ? text + ' · ' + d.soFar : text;
                                },
                                footer: function (items) {
                                    return items.length ? (eventsAt[d.keys[items[0].dataIndex]] || []) : [];
                                },
                                label: function (ctx) {
                                    var v = ctx.parsed.y;
                                    var value = money ? (v % 1 ? moneyExact.format(v) : money.format(v)) : count(v);
                                    var text = d.series.length > 1 ? value + '  ' + ctx.dataset.label : value;
                                    if (ctx.dataset.id === 'previous' && d.previousKeys && d.previousKeys[ctx.dataIndex]) {
                                        text += ' (' + label(d.previousKeys[ctx.dataIndex], period, locale, false) + ')';
                                    }
                                    return text;
                                },
                                labelColor: function (ctx) {
                                    var c = ctx.dataset.borderColor;
                                    return { borderColor: c, backgroundColor: c, borderWidth: 0, borderRadius: 1 };
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: {
                                color: ink3,
                                font: { size: 11 },
                                maxRotation: 0,
                                autoSkip: true,
                                autoSkipPadding: 16,
                                callback: function (value, index) { return label(d.keys[index], period, locale, false); },
                            },
                        },
                        y: {
                            beginAtZero: true,
                            grace: '8%',
                            grid: { color: hairline, drawTicks: false },
                            border: { display: false },
                            ticks: {
                                color: ink3,
                                font: { size: 11 },
                                padding: 8,
                                maxTicksLimit: 5,
                                precision: 0,
                                callback: function (value) { return compact.format(value); },
                            },
                        },
                    },
                },
            });
            chart.$anEvents = events;
            chart.$anCount = function (v) { return money ? money.format(v) : count(v); };
            chart.draw();
        }
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
</script>
