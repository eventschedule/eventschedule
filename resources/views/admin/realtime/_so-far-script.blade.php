{{-- Plain script, included INSIDE _script's DOMContentLoaded closure (it reads MSG and numberFormat
     there), never on its own. Brought over from Stackapella's Growth page (web/js/admin/growth-chart.js
     and FairCompare::judge), 2026-11-12. Its template is a string, so nothing a person typed can
     reach it: it prints numbers and the page's own words. Its colours are the --ap-* tokens through
     the so-far-* classes in _script's stylesheet, so every palette draws it in its own inks. --}}

        // How a gap between a count and what was expected is said, the same way everywhere on the
        // page: `n` now against `expected` if nothing had changed, where the baseline's own noise is
        // `k` times its size (1 for one day or span, 1/4 for the mean of four). Under 5 on both sides
        // nothing is said but the counts; within two chance-sized steps it is "About the same"; a
        // change is a count under 20 and a percentage from there, and "3 times as many" past +200%.
        function fairVerdict(n, expected, k) {
            var e = Math.max(0, expected || 0);
            var sigma = Math.sqrt(e * (1 + k));
            var out = { verdict: 'none', change: null, expected: e, low: Math.max(0, Math.round(e - 2 * sigma)), high: Math.round(e + 2 * sigma) };
            if (n === 0 && e < 0.5) return out;
            if (Math.max(n, e) < 5) { out.verdict = 'tooFew'; return out; }
            if (e < 0.5) { out.verdict = 'new'; return out; }
            var z = (n - e) / sigma;
            out.verdict = z >= 2 ? 'up' : (z <= -2 ? 'down' : (z >= 1.5 ? 'maybeUp' : (z <= -1.5 ? 'maybeDown' : 'same')));
            out.change = e < 20 ? { kind: 'count', value: Math.round(n - e) }
                : (n > 3 * e ? { kind: 'times', value: Math.round((n / e) * 2) / 2 } : { kind: 'percent', value: Math.round(((n - e) / e) * 100) });
            return out;
        }

        function changeText(change) {
            if (!change) return '';
            if (change.kind === 'times') return MSG.soFar.times.replace(':n', numberFormat.format(change.value));
            // The locale's own percent ("+41 %" in German); the caller sets the figure left to right,
            // or a Hebrew page reads "41%+".
            if (change.kind === 'percent' && percentFormat) return percentFormat.format(change.value / 100);
            var sign = change.value > 0 ? '+' : (change.value < 0 ? '\u2212' : '');
            return sign + numberFormat.format(Math.abs(change.value)) + (change.kind === 'percent' ? '%' : '');
        }

        // Up and down are blue and orange, never green and red: they hold apart for every common
        // colour blindness, and an ordinary dip should not read as an alarm. Always with a word.
        function verdictArrow(verdict) {
            return verdict === 'up' || verdict === 'maybeUp' || verdict === 'new' ? '↑' : (verdict === 'down' || verdict === 'maybeDown' ? '↓' : '');
        }

        var SO_FAR_H = 220;
        var SO_FAR_M = { top: 20, right: 14, bottom: 26, left: 40 };

        function soFarRunning(counts) {
            var sum = 0;
            return (counts || []).map(function (n) {
                if (n === null || n === undefined) return null;
                sum += n;
                return sum;
            });
        }

        // Three or four round gridline values from 0 to just above max.
        function soFarTicks(max) {
            if (!(max > 0)) return [0, 1];
            var raw = max / 3, pow = Math.pow(10, Math.floor(Math.log10(raw)));
            var step = (pow < 10 ? [1, 2, 5, 10] : [1, 2, 2.5, 5, 10]).map(function (m) { return m * pow; }).find(function (s) { return s >= raw; }) || 10 * pow;
            if (step < 1) step = 1;
            var out = [];
            for (var v = 0; v <= max + step * 0.001; v += step) out.push(Math.round(v * 100) / 100);
            if (out[out.length - 1] < max) out.push(out[out.length - 1] + step);
            return out;
        }

        // A sentence inside the chart, which is set left to right: isolated, so a Hebrew one keeps
        // its own order around its numbers.
        function soFarIsolate(text) {
            return '\u2068' + text + '\u2069';
        }

        function soFarRound(n) {
            return Math.abs(n) >= 1000 ? Math.round(n / 10) * 10 : Math.round(n);
        }

        var SoFarChart = {
            props: {
                chart: { type: Object, required: true },
                metric: { type: String, required: true },
                label: { type: String, required: true },
            },
            data: function () {
                return { width: 560, hover: null, pinned: null, table: false, H: SO_FAR_H, M: SO_FAR_M, t: MSG.soFar, verdicts: MSG.verdict, pageDir: CONFIG.rtl ? 'rtl' : 'ltr' };
            },
            template: `
<figure class="min-w-0 space-y-2" :data-chart="metric">
    <figcaption class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <span class="text-sm font-semibold text-gray-900 dark:text-white" v-text="label"></span>
        <span v-if="m && m.previousTotal !== null" class="text-xs text-gray-500 dark:text-gray-400" v-text="t.before_had.replace(':n', fmt(m.previousTotal))"></span>
    </figcaption>
    <p v-if="!m" class="py-10 text-sm text-gray-500 dark:text-gray-400" v-text="t.collecting"></p>
    <template v-else>
        <ul class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600 dark:text-gray-400">
            <li class="inline-flex items-center gap-1.5"><svg width="18" height="6" aria-hidden="true"><line x1="0" y1="3" x2="18" y2="3" class="so-far-ink" stroke-width="2"></line></svg><span v-text="t.today"></span></li>
            <li v-if="s.before" class="inline-flex items-center gap-1.5"><svg width="18" height="6" aria-hidden="true"><line x1="0" y1="3" x2="18" y2="3" class="so-far-muted" stroke-width="1.5" stroke-dasharray="4 3"></line></svg><span v-text="t.yesterday"></span></li>
            <li v-if="s.usual" class="inline-flex items-center gap-1.5"><svg width="18" height="8" aria-hidden="true"><rect x="0" y="0" width="18" height="8" rx="2" class="so-far-band"></rect></svg><span v-text="t.usual.replace(':weekday', chart.weekday)"></span></li>
            <li v-if="s.projection" class="inline-flex items-center gap-1.5"><svg width="18" height="6" aria-hidden="true"><line x1="0" y1="3" x2="18" y2="3" class="so-far-ink" stroke-width="2" stroke-dasharray="1.5 3" stroke-linecap="round"></line></svg><span v-text="t.projected_legend"></span></li>
        </ul>
        <div v-show="!table" ref="box" class="relative" dir="ltr" @pointerleave="hover = pinned">
            <svg :width="width" :height="H" class="so-far-svg block max-w-full touch-pan-y select-none" role="img" :aria-label="summary" @pointermove="point" @pointerdown="pin">
                <g class="so-far-axis">
                    <g v-for="v in yTicks" :key="'y' + v">
                        <line :x1="M.left" :x2="width - M.right" :y1="sy(v)" :y2="sy(v)" class="so-far-grid" stroke-width="1" :opacity="v === 0 ? 1 : 0.6"></line>
                        <text :x="M.left - 6" :y="sy(v) + 3.5" text-anchor="end" class="so-far-muted-fill" v-text="fmt(v)"></text>
                    </g>
                    <text v-for="(tick, i) in m.ticks" :key="'x' + i" :x="sx(i * 6)" :y="H - 6" text-anchor="middle" class="so-far-muted-fill" v-text="tick"></text>
                </g>
                <g v-if="s.usual">
                    <path :d="band(s.usual.low, s.usual.high)" class="so-far-band"></path>
                    <path :d="line(s.usual.mean)" fill="none" class="so-far-muted" stroke-width="1" opacity="0.7"></path>
                </g>
                <path v-if="s.before" :d="line(s.before)" fill="none" class="so-far-muted" stroke-width="1.5" stroke-dasharray="5 4" data-line-before></path>
                <g v-if="s.projection" data-projection>
                    <path :d="band(s.projection.lowPath, s.projection.highPath)" class="so-far-fan"></path>
                    <path :d="line(s.projection.mean)" fill="none" class="so-far-ink" stroke-width="2" stroke-dasharray="1.5 4" stroke-linecap="round"></path>
                    <circle :cx="sx(24)" :cy="sy(s.projection.value)" r="4.5" class="so-far-ring" stroke-width="1.5"></circle>
                    <text :x="sx(24) - 8" :y="sy(s.projection.value) - 9" text-anchor="end" class="so-far-label so-far-ink-fill" v-text="projectedLabel"></text>
                </g>
                <line :x1="sx(nowX)" :x2="sx(nowX)" :y1="M.top - 6" :y2="H - M.bottom" class="so-far-muted" stroke-width="1" opacity="0.6"></line>
                <text :x="sx(nowX)" :y="M.top - 9" text-anchor="middle" class="so-far-axis so-far-muted-fill" v-text="isolate(t.now)"></text>
                <path :d="line(s.today)" fill="none" class="so-far-ink" stroke-width="2" stroke-linejoin="round" data-line-today></path>
                <circle :cx="sx(nowX)" :cy="sy(s.soFar)" r="4" class="so-far-dot" stroke-width="2"></circle>
                <text :x="sx(nowX) + nowLabel.dx" :y="sy(s.soFar) + nowLabel.dy" :text-anchor="nowLabel.anchor" class="so-far-label so-far-strong so-far-ink-fill" v-text="atNowLabel"></text>
                <line v-if="hover !== null" :x1="sx(hover)" :x2="sx(hover)" :y1="M.top" :y2="H - M.bottom" class="so-far-ink" stroke-width="1" opacity="0.35"></line>
            </svg>
            <div v-if="hover !== null" class="so-far-tip pointer-events-none absolute top-1 rounded-lg px-2.5 py-1.5 text-xs shadow" :style="tipStyle" :dir="pageDir" v-text="tip"></div>
        </div>
        <dl v-if="rows.length" class="space-y-1.5 border-t so-far-rule pt-3">
            <div v-for="row in rows" :key="row.key" class="text-sm" :data-row="row.key" :data-verdict="row.v.verdict">
                <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                    <dt class="min-w-0 text-gray-600 dark:text-gray-400" v-text="row.name"></dt>
                    <dd class="flex shrink-0 items-baseline gap-2 tabular-nums">
                        <span class="text-gray-500 dark:text-gray-400" v-text="fmt(Math.round(row.v.expected))"></span>
                        <b v-if="row.v.change" class="font-semibold text-gray-900 dark:text-white" dir="ltr" v-text="changeText(row.v.change)"></b>
                        <span class="so-far-chip" :class="'is-' + row.v.verdict"><span v-if="arrow(row.v.verdict)" class="so-far-arrow" aria-hidden="true" v-text="arrow(row.v.verdict)"></span><span v-text="verdicts[row.v.verdict]"></span></span>
                    </dd>
                </div>
                <p v-if="row.range" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" v-text="row.range"></p>
            </div>
        </dl>
        <p v-if="!rows.length" class="text-sm text-gray-500 dark:text-gray-400" v-text="t.no_count"></p>
        <p v-if="hiddenLine" class="text-xs text-gray-500 dark:text-gray-400" v-text="hiddenLine"></p>
        <button type="button" class="event-link text-xs" :aria-expanded="table ? 'true' : 'false'" @click="table = !table" v-text="table ? t.show_chart : t.show_table"></button>
        <div v-if="table" class="max-h-96 overflow-auto">
            <table class="w-full text-sm">
                <caption class="pb-1 text-start text-xs text-gray-500 dark:text-gray-400" v-text="t.table_caption.replace(':what', label)"></caption>
                <thead class="text-xs text-gray-500 dark:text-gray-400"><tr>
                    <th scope="col" class="py-1 pe-2 text-start font-semibold" v-text="t.col_by"></th>
                    <th scope="col" class="px-2 py-1 text-end font-semibold" v-text="t.today"></th>
                    <th v-if="s.before" scope="col" class="px-2 py-1 text-end font-semibold" v-text="t.yesterday"></th>
                    <th v-if="s.usual" scope="col" class="px-2 py-1 text-end font-semibold" v-text="t.col_usual"></th>
                    <th v-if="s.projection" scope="col" class="ps-2 py-1 text-end font-semibold" v-text="t.col_projected"></th>
                </tr></thead>
                <tbody class="tabular-nums">
                    <tr v-for="r in tableRows" :key="r.hour">
                        <th scope="row" class="py-1 pe-2 text-start font-normal text-gray-500 dark:text-gray-400" v-text="r.by"></th>
                        <td class="px-2 py-1 text-end text-gray-900 dark:text-white" v-text="r.today"></td>
                        <td v-if="s.before" class="px-2 py-1 text-end text-gray-900 dark:text-white" v-text="r.before"></td>
                        <td v-if="s.usual" class="px-2 py-1 text-end text-gray-500 dark:text-gray-400" v-text="r.usual"></td>
                        <td v-if="s.projection" class="ps-2 py-1 text-end text-gray-500 dark:text-gray-400" v-text="r.projected"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </template>
</figure>`,
            computed: {
                m: function () {
                    return (this.chart.metrics || {})[this.metric] || null;
                },
                // How far this metric is counted today: visits a few minutes behind the clock.
                cut: function () {
                    return (this.m && this.m.cut) || { slot: 0, fraction: 0, label: '' };
                },
                nowX: function () {
                    return (this.cut.slot || 0) + (this.cut.fraction || 0);
                },
                // Beside the dot; above it where the projection runs on from it, and below it
                // where the projection's label would sit on it (the evening, when the two meet).
                nowLabel: function () {
                    var p = this.s.projection, x = this.sx(this.nowX);
                    var end = !!p || x > this.width * 0.6;
                    var below = p && Math.abs(this.sy(this.s.soFar) - this.sy(p.value)) < 18 && this.sy(this.s.soFar) + 17 < this.H - this.M.bottom - 4;
                    var dy = p ? (below ? 17 : -9) : 4;
                    return { dx: end ? -8 : 8, dy: dy, anchor: end ? 'end' : 'start' };
                },
                // Each line as [hour, running total] points, x from 0 (midnight) to 24.
                s: function () {
                    var m = this.m, slot = this.cut.slot || 0, nowX = this.nowX;
                    if (!m) return { today: [[0, 0]], before: null, usual: null, projection: null, soFar: 0 };
                    var today = soFarRunning(m.now);
                    var soFar = m.soFar;
                    var points = function (arr) { return [[0, 0]].concat(arr.map(function (v, i) { return [i + 1, v]; })); };
                    var todayPts = [[0, 0]].concat(today.slice(0, slot).map(function (v, i) { return [i + 1, v]; })).concat([[nowX, soFar]]);
                    var p = m.projection;
                    // A running total cannot end below what is already in, so the fan is held at it.
                    var along = function (arr) { return [[nowX, soFar]].concat((arr || []).map(function (v, j) { return [slot + j + 1, Math.max(v, soFar)]; })); };
                    return {
                        today: todayPts,
                        before: m.previous ? points(soFarRunning(m.previous)) : null,
                        usual: m.usual ? { low: points(m.usual.low), high: points(m.usual.high), mean: points(m.usual.mean) } : null,
                        projection: p && p.shown && p.path ? { mean: along(p.path.mean), lowPath: along(p.path.low), highPath: along(p.path.high), value: p.value, low: p.low, high: p.high } : null,
                        soFar: soFar,
                    };
                },
                yMax: function () {
                    var s = this.s, values = [];
                    [s.today, s.before, s.usual && s.usual.high, s.projection && s.projection.highPath].forEach(function (pts) {
                        (pts || []).forEach(function (pt) { if (isFinite(pt[1])) values.push(pt[1]); });
                    });
                    // Headroom over the now dot, so its label never sits on the "Now" label.
                    return Math.max.apply(null, [1, s.soFar * 1.15].concat(values));
                },
                yTicks: function () {
                    return soFarTicks(this.yMax);
                },
                atNowLabel: function () {
                    return soFarIsolate(this.t.at_now.replace(':n', this.fmt(this.s.soFar)).replace(':time', this.cut.label));
                },
                projectedLabel: function () {
                    var p = this.s.projection;
                    return p ? soFarIsolate(this.t.projected.replace(':n', this.fmt(soFarRound(p.value))).replace(':low', this.fmt(soFarRound(p.low))).replace(':high', this.fmt(soFarRound(p.high)))) : '';
                },
                hiddenLine: function () {
                    var p = this.m && this.m.projection;
                    if (!p || p.shown) return '';
                    if (p.hidden === 'early') return p.showsFrom === null ? '' : this.t.hidden_early.replace(':time', p.showsFrom);
                    return p.hidden === 'tooFew' ? this.t.hidden_too_few : this.t.hidden_no_history;
                },
                // Against yesterday by now, and against a usual day of this weekday by now.
                rows: function () {
                    var m = this.m, t = this.t, out = [];
                    if (!m) return out;
                    if (m.previousByNow !== null) {
                        out.push({ key: 'previous', name: t.yesterday_by.replace(':time', this.cut.label), v: fairVerdict(m.soFar, m.previousByNow, 1), range: '' });
                    }
                    if (m.usual) {
                        var v = fairVerdict(m.soFar, m.usual.byNow, 1 / m.usual.available);
                        var said = v.verdict !== 'none' && v.verdict !== 'tooFew';
                        // The range the band draws at this time (the lowest and highest of those
                        // days), not the verdict's chance range: one card, one "usual".
                        out.push({ key: 'usual', name: t.usual_by.replace(':weekday', this.chart.weekday).replace(':time', this.cut.label), v: v, range: said ? t.usually.replace(':low', this.fmt(Math.round(m.usual.lowByNow))).replace(':high', this.fmt(Math.round(m.usual.highByNow))) : '' });
                    }
                    return out;
                },
                summary: function () {
                    var before = this.s.before ? this.valueAt(this.s.before, this.nowX) : null;
                    if (before === null) return this.t.summary_alone.replace(':what', this.label).replace(':n', this.fmt(this.s.soFar)).replace(':time', this.cut.label);
                    return this.t.summary.replace(':what', this.label).replace(':n', this.fmt(this.s.soFar)).replace(':time', this.cut.label)
                        .replace(':before', this.fmt(Math.round(before)));
                },
                tip: function () {
                    var x = this.hover, t = this.t, s = this.s;
                    if (x === null) return '';
                    var parts = [t.tip_by.replace(':time', this.m.hours[x % 24])];
                    if (x <= this.nowX) parts.push(t.tip_today.replace(':n', this.fmt(Math.round(this.valueAt(s.today, x)))));
                    if (s.before) parts.push(t.tip_yesterday.replace(':n', this.fmt(Math.round(this.valueAt(s.before, x)))));
                    if (s.usual) parts.push(t.tip_usual.replace(':low', this.fmt(Math.round(this.valueAt(s.usual.low, x)))).replace(':high', this.fmt(Math.round(this.valueAt(s.usual.high, x)))));
                    if (s.projection && x > this.nowX) parts.push(t.tip_projected.replace(':n', this.fmt(soFarRound(this.valueAt(s.projection.mean, x)))));
                    return parts.join(', ');
                },
                tipStyle: function () {
                    var x = this.sx(this.hover || 0);
                    // Physical sides: the chart runs left to right on every page, while the tip
                    // itself takes the page's direction, which would turn inline sides around.
                    return x > this.width / 2 ? { right: (this.width - x + 8) + 'px' } : { left: (x + 8) + 'px' };
                },
                tableRows: function () {
                    var self = this, s = this.s, slot = this.cut.slot || 0;
                    var rows = [];
                    for (var i = 0; i < 24; i++) {
                        var x = i + 1;
                        rows.push({
                            hour: i,
                            by: i === slot ? self.cut.label : (i === 23 ? self.m.ticks[4] : self.m.hours[i + 1]),
                            today: i <= slot ? self.fmt(Math.round(self.valueAt(s.today, Math.min(x, self.nowX)))) : '',
                            // The row for the hour under way is read at the cut, every column of it.
                            before: s.before ? self.fmt(Math.round(self.valueAt(s.before, i === slot ? self.nowX : x))) : '',
                            usual: s.usual ? self.t.range.replace(':low', self.fmt(Math.round(self.valueAt(s.usual.low, i === slot ? self.nowX : x)))).replace(':high', self.fmt(Math.round(self.valueAt(s.usual.high, i === slot ? self.nowX : x)))) : '',
                            projected: s.projection && i > slot ? self.fmt(soFarRound(s.projection.mean[i - slot + 1][1])) : '',
                        });
                    }
                    return rows;
                },
            },
            mounted: function () {
                this.watchBox();
            },
            // The box exists only once the metric has numbers, which can be after mounting.
            updated: function () {
                this.watchBox();
            },
            beforeUnmount: function () {
                if (this.observer) this.observer.disconnect();
            },
            methods: {
                fmt: function (n) { return numberFormat.format(n || 0); },
                changeText: changeText,
                arrow: verdictArrow,
                isolate: soFarIsolate,
                watchBox: function () {
                    var self = this, box = this.$refs.box;
                    if (!box || this.watched === box) return;
                    this.watched = box;
                    this.fit();
                    if (!('ResizeObserver' in window)) return;
                    if (!this.observer) this.observer = new ResizeObserver(function () { self.fit(); });
                    this.observer.observe(box);
                },
                fit: function () {
                    var w = this.$refs.box && this.$refs.box.clientWidth;
                    if (w) this.width = Math.max(260, Math.round(w));
                },
                sx: function (hour) {
                    // Left to right on every page, like the Trends chart: a clock runs that way.
                    return this.M.left + (hour / 24) * (this.width - this.M.left - this.M.right);
                },
                sy: function (v) {
                    var top = this.yTicks[this.yTicks.length - 1] || 1;
                    return this.M.top + (1 - v / top) * (this.H - this.M.top - this.M.bottom);
                },
                line: function (pts) {
                    var self = this;
                    return (pts || []).map(function (pt, i) { return (i ? 'L' : 'M') + self.sx(pt[0]).toFixed(1) + ' ' + self.sy(pt[1]).toFixed(1); }).join(' ');
                },
                band: function (low, high) {
                    var self = this;
                    if (!low || !high) return '';
                    return this.line(low) + ' ' + high.slice().reverse().map(function (pt) { return 'L' + self.sx(pt[0]).toFixed(1) + ' ' + self.sy(pt[1]).toFixed(1); }).join(' ') + ' Z';
                },
                // A line's value at hour x, straight between its points.
                valueAt: function (pts, x) {
                    if (!pts || !pts.length) return 0;
                    for (var i = 1; i < pts.length; i++) {
                        var a = pts[i - 1], b = pts[i];
                        if (x >= a[0] && x <= b[0]) return b[0] === a[0] ? b[1] : a[1] + ((b[1] - a[1]) * (x - a[0])) / (b[0] - a[0]);
                    }
                    return pts[pts.length - 1][1];
                },
                point: function (event) {
                    if (this.pinned !== null) return;
                    this.hover = this.hourAt(event);
                },
                pin: function (event) {
                    var h = this.hourAt(event);
                    this.pinned = this.pinned === h ? null : h;
                    this.hover = this.pinned === null ? h : this.pinned;
                },
                hourAt: function (event) {
                    var box = event.currentTarget.getBoundingClientRect();
                    var x = event.clientX - box.left;
                    var h = ((x - this.M.left) / (this.width - this.M.left - this.M.right)) * 24;
                    return Math.min(24, Math.max(0, Math.round(h)));
                },
            },
        };

        // The longer periods (7 days, 4 weeks, this month; RealtimeSoFar::periodSeries()), day by
        // day as columns: this period in ink, the same day of the period before as a grey tick
        // across its column, today lighter, and the rest of today as a dashed outline up to its
        // projection, with a whisker for the range. Brought over from Stackapella's column charts.
        var SO_FAR_COL_M = { top: 20, right: 10, bottom: 26, left: 40 };

        var SoFarColumns = {
            props: {
                chart: { type: Object, required: true },
                metric: { type: String, required: true },
                label: { type: String, required: true },
            },
            data: function () {
                return { width: 560, hover: null, table: false, H: 200, M: SO_FAR_COL_M, t: MSG.soFar, verdicts: MSG.verdict, pageDir: CONFIG.rtl ? 'rtl' : 'ltr' };
            },
            template: `
<figure class="min-w-0 space-y-2" :data-chart="metric" data-chart-kind="days">
    <figcaption class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <span class="text-sm font-semibold text-gray-900 dark:text-white" v-text="label"></span>
        <span v-if="m && m.previousTotal !== null" class="text-xs text-gray-500 dark:text-gray-400" v-text="header"></span>
    </figcaption>
    <p v-if="!m" class="py-10 text-sm text-gray-500 dark:text-gray-400" v-text="t.collecting"></p>
    <template v-else>
        <ul class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600 dark:text-gray-400">
            <li class="inline-flex items-center gap-1.5"><svg width="10" height="10" aria-hidden="true"><rect x="0" y="0" width="10" height="10" rx="2" class="so-far-col"></rect></svg><span v-text="t.this_period"></span></li>
            <li class="inline-flex items-center gap-1.5"><svg width="14" height="6" aria-hidden="true"><line x1="0" y1="3" x2="14" y2="3" class="so-far-muted" stroke-width="2"></line></svg><span v-text="t['before_' + chart.period]"></span></li>
            <li v-if="dayProjection" class="inline-flex items-center gap-1.5"><svg width="12" height="12" aria-hidden="true"><rect x="0.5" y="0.5" width="11" height="11" fill="none" class="so-far-ink" stroke-dasharray="2 2"></rect></svg><span v-text="t.projected_legend"></span></li>
        </ul>
        <div v-show="!table" ref="box" class="relative" dir="ltr" @pointerleave="hover = null">
            <svg :width="width" :height="H" class="so-far-svg block max-w-full touch-pan-y select-none" role="img" :aria-label="summary" @pointermove="point">
                <g class="so-far-axis">
                    <g v-for="v in yTicks" :key="'y' + v">
                        <line :x1="M.left" :x2="width - M.right" :y1="sy(v)" :y2="sy(v)" class="so-far-grid" stroke-width="1" :opacity="v === 0 ? 1 : 0.6"></line>
                        <text :x="M.left - 6" :y="sy(v) + 3.5" text-anchor="end" class="so-far-muted-fill" v-text="fmt(v)"></text>
                    </g>
                    <template v-for="(slot, i) in m.slots" :key="'x' + i">
                        <text v-if="showLabel(i)" :x="cx(i)" :y="H - 6" text-anchor="middle" class="so-far-muted-fill" v-text="slot.label"></text>
                    </template>
                </g>
                <rect v-if="hover !== null" :x="cx(hover) - band / 2" :y="M.top" :width="band" :height="H - M.top - M.bottom" class="so-far-hover"></rect>
                <g v-for="(n, i) in m.now" :key="'c' + i">
                    <rect v-if="n !== null && n > 0" :x="cx(i) - colW / 2" :y="sy(n)" :width="colW" :height="Math.max(0, sy(0) - sy(n))" rx="2" class="so-far-col" :class="i === m.todayIndex ? 'is-today' : ''"></rect>
                </g>
                <g v-if="dayProjection" data-projection>
                    <rect :x="cx(m.todayIndex) - colW / 2" :y="sy(dayProjection.value)" :width="colW" :height="Math.max(0, sy(m.now[m.todayIndex] || 0) - sy(dayProjection.value))" fill="none" class="so-far-ink" stroke-width="1" stroke-dasharray="3 2"></rect>
                    <line :x1="cx(m.todayIndex)" :x2="cx(m.todayIndex)" :y1="sy(dayProjection.low)" :y2="sy(dayProjection.high)" class="so-far-ink" stroke-width="1"></line>
                    <line :x1="cx(m.todayIndex) - 3" :x2="cx(m.todayIndex) + 3" :y1="sy(dayProjection.high)" :y2="sy(dayProjection.high)" class="so-far-ink" stroke-width="1"></line>
                </g>
                <g v-for="(n, i) in m.previous" :key="'p' + i">
                    <line v-if="n !== null" :x1="cx(i) - band * 0.42" :x2="cx(i) + band * 0.42" :y1="sy(n)" :y2="sy(n)" class="so-far-muted" stroke-width="2" stroke-linecap="round"></line>
                </g>
            </svg>
            <div v-if="hover !== null" class="so-far-tip pointer-events-none absolute top-1 rounded-lg px-2.5 py-1.5 text-xs shadow" :style="tipStyle" :dir="pageDir" v-text="tip"></div>
        </div>
        <dl v-if="rows.length" class="space-y-1.5 border-t so-far-rule pt-3">
            <div v-for="row in rows" :key="row.key" class="text-sm" :data-row="row.key" :data-verdict="row.v.verdict">
                <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                    <dt class="min-w-0 text-gray-600 dark:text-gray-400" v-text="row.name"></dt>
                    <dd class="flex shrink-0 items-baseline gap-2 tabular-nums">
                        <span class="text-gray-500 dark:text-gray-400" v-text="fmt(Math.round(row.v.expected))"></span>
                        <b v-if="row.v.change" class="font-semibold text-gray-900 dark:text-white" dir="ltr" v-text="changeText(row.v.change)"></b>
                        <span class="so-far-chip" :class="'is-' + row.v.verdict"><span v-if="arrow(row.v.verdict)" class="so-far-arrow" aria-hidden="true" v-text="arrow(row.v.verdict)"></span><span v-text="verdicts[row.v.verdict]"></span></span>
                    </dd>
                </div>
                <p v-if="row.range" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" v-text="row.range"></p>
            </div>
        </dl>
        <p v-if="!rows.length" class="text-sm text-gray-500 dark:text-gray-400" v-text="t.no_count"></p>
        <p v-if="projectionLine" class="text-xs text-gray-500 dark:text-gray-400" v-text="projectionLine"></p>
        <button type="button" class="event-link text-xs" :aria-expanded="table ? 'true' : 'false'" @click="table = !table" v-text="table ? t.show_chart : t.show_table"></button>
        <div v-if="table" class="max-h-96 overflow-auto">
            <table class="w-full text-sm">
                <caption class="pb-1 text-start text-xs text-gray-500 dark:text-gray-400" v-text="label"></caption>
                <thead class="text-xs text-gray-500 dark:text-gray-400"><tr>
                    <th scope="col" class="py-1 pe-2 text-start font-semibold" v-text="t.col_day"></th>
                    <th scope="col" class="px-2 py-1 text-end font-semibold" v-text="t.this_period"></th>
                    <th scope="col" class="ps-2 py-1 text-end font-semibold" v-text="t['before_' + chart.period]"></th>
                </tr></thead>
                <tbody class="tabular-nums">
                    <tr v-for="(slot, i) in m.slots" :key="slot.date">
                        <th scope="row" class="py-1 pe-2 text-start font-normal text-gray-500 dark:text-gray-400" v-text="slot.title"></th>
                        <td class="px-2 py-1 text-end text-gray-900 dark:text-white" v-text="m.now[i] === null ? '' : (i === m.todayIndex ? t.so_far.replace(':n', fmt(m.now[i])) : fmt(m.now[i]))"></td>
                        <td class="ps-2 py-1 text-end text-gray-900 dark:text-white" v-text="m.previous[i] === null ? '' : fmt(m.previous[i])"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </template>
</figure>`,
            computed: {
                m: function () {
                    return (this.chart.metrics || {})[this.metric] || null;
                },
                count: function () {
                    return this.m ? this.m.slots.length : 1;
                },
                band: function () {
                    return (this.width - this.M.left - this.M.right) / this.count;
                },
                colW: function () {
                    return Math.max(2, Math.min(28, this.band * 0.6));
                },
                dayProjection: function () {
                    var p = this.m && this.m.projection;
                    return p && p.day ? p.day : null;
                },
                yMax: function () {
                    var m = this.m, values = [1];
                    if (!m) return 1;
                    m.now.concat(m.previous).forEach(function (n) { if (n !== null) values.push(n); });
                    if (this.dayProjection) values.push(this.dayProjection.high);
                    return Math.max.apply(null, values);
                },
                yTicks: function () {
                    return soFarTicks(this.yMax);
                },
                header: function () {
                    var m = this.m;
                    var words = this.chart.period === 'month' ? this.t.last_month_had : this.t.period_had;
                    return this.t.so_far.replace(':n', this.fmt(m.soFar)) + ' · ' + words.replace(':n', this.fmt(m.previousTotal));
                },
                // Against the period before, cut at the same time; 7 days against a usual week too.
                rows: function () {
                    var m = this.m, t = this.t, out = [], time = this.m ? this.m.cut.label : '';
                    if (!m) return out;
                    var prev = m.compare.previous;
                    if (prev) {
                        var name = prev.scaled ? t.previous_month_scaled : t['previous_' + this.chart.period].replace(':time', time);
                        out.push({ key: 'previous', name: name, v: fairVerdict(m.soFar, prev.expected, 1), range: '' });
                    }
                    var usual = m.compare.usual;
                    if (usual) {
                        var v = fairVerdict(m.soFar, usual.expected, 1 / usual.available);
                        var said = v.verdict !== 'none' && v.verdict !== 'tooFew';
                        out.push({ key: 'usual', name: t.usual_week.replace(':time', time), v: v, range: said ? t.usually.replace(':low', this.fmt(usual.low)).replace(':high', this.fmt(usual.high)) : '' });
                    }
                    return out;
                },
                projectionLine: function () {
                    var p = this.m && this.m.projection, t = this.t;
                    if (!p) return '';
                    if (p.shown) {
                        var words = this.chart.period === 'month' ? t.projected_month : t.projected_period;
                        return words.replace(':n', this.fmt(soFarRound(p.value))).replace(':low', this.fmt(soFarRound(p.low))).replace(':high', this.fmt(soFarRound(p.high)));
                    }
                    if (p.hidden === 'early') return this.chart.period === 'month' ? t.hidden_month_early : '';
                    return p.hidden === 'tooFew' ? (this.chart.period === 'month' ? t.hidden_too_few_month : t.hidden_too_few) : t.hidden_no_history;
                },
                summary: function () {
                    return this.m ? this.t.summary_period.replace(':what', this.label).replace(':n', this.fmt(this.m.soFar)) : this.label;
                },
                tip: function () {
                    var i = this.hover, m = this.m, t = this.t;
                    if (i === null || !m) return '';
                    var parts = [m.slots[i].title];
                    if (m.now[i] !== null) parts.push(i === m.todayIndex ? t.so_far.replace(':n', this.fmt(m.now[i])) : this.fmt(m.now[i]));
                    if (m.previous[i] !== null) parts.push(t.tip_before.replace(':n', this.fmt(m.previous[i])));
                    return parts.join(', ');
                },
                tipStyle: function () {
                    var x = this.cx(this.hover || 0);
                    return x > this.width / 2 ? { right: (this.width - x + 8) + 'px' } : { left: (x + 8) + 'px' };
                },
            },
            mounted: function () {
                this.watchBox();
            },
            updated: function () {
                this.watchBox();
            },
            beforeUnmount: function () {
                if (this.observer) this.observer.disconnect();
            },
            methods: {
                fmt: function (n) { return numberFormat.format(n || 0); },
                changeText: changeText,
                arrow: verdictArrow,
                watchBox: SoFarChart.methods.watchBox,
                fit: SoFarChart.methods.fit,
                cx: function (i) {
                    return this.M.left + (i + 0.5) * this.band;
                },
                sy: function (v) {
                    var top = this.yTicks[this.yTicks.length - 1] || 1;
                    return this.M.top + (1 - v / top) * (this.H - this.M.top - this.M.bottom);
                },
                // Every day for a week; a week apart for a longer period.
                showLabel: function (i) {
                    return this.count <= 7 || i % 7 === 0;
                },
                point: function (event) {
                    var box = event.currentTarget.getBoundingClientRect();
                    var i = Math.floor((event.clientX - box.left - this.M.left) / this.band);
                    this.hover = i >= 0 && i < this.count ? i : null;
                },
            },
        };
