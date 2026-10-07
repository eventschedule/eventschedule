{{-- Keeps the dashboard's live figures current: the Realtime tile, the "visitors now" column of
     the schedules list, and a brand-new organizer's first-visitor card. It asks
     RealtimeController::summary() every 30 seconds while the tab is visible, not at all while it
     is hidden, and stops for good when the server says there is no live view to give (signed out,
     switched off, no longer theirs).

     Plain DOM script on purpose: the page around it is server-rendered and holds people's own
     event and schedule names, and a Vue mount over it would compile them as a template. Every
     value written here is a number the server computed, set with textContent. --}}
<script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function () {
        var url = @json(route('analytics.realtime.summary'));
        var locale = @json(app()->getLocale());
        var EVERY_MS = 30000;
        var number = new Intl.NumberFormat(locale + '-u-nu-latn');
        var timer = null, gone = false;

        function setText(selector, value, root) {
            (root || document).querySelectorAll(selector).forEach(function (el) {
                var text = number.format(value);
                if (el.textContent !== text) { el.textContent = text; }
            });
        }

        function draw(data) {
            var quiet = !data.views_5m && !data.visitors_now;

            setText('[data-live-views]', data.views_5m);
            setText('[data-live-visitors]', data.visitors_now);
            setText('[data-live-total]', data.views_30m);

            // The class, not the `hidden` attribute: an element that also carries .flex or .grid
            // keeps its display, and the attribute loses to it.
            document.querySelectorAll('[data-live-quiet]').forEach(function (el) {
                el.classList.toggle('hidden', !quiet);
                el.classList.toggle('flex', quiet);
            });
            document.querySelectorAll('[data-live-figures]').forEach(function (el) {
                el.classList.toggle('hidden', quiet);
                el.classList.toggle('grid', !quiet);
            });
            document.querySelectorAll('[data-live-tile] [data-tile-dot]').forEach(function (el) {
                el.classList.toggle('bg-green-500', !quiet);
                el.classList.toggle('bg-gray-400', quiet);
            });

            document.querySelectorAll('[data-live-bars]').forEach(function (strip) {
                var peak = Math.max.apply(null, data.minutes.concat([1]));
                var tone = strip.getAttribute('data-tone');
                var bars = strip.children;
                data.minutes.forEach(function (value, index) {
                    var bar = bars[index];
                    if (!bar) return;
                    bar.className = 'flex-1 max-w-[0.5rem] rounded-sm ' + (value ? tone : 'bg-gray-200 dark:bg-gray-700');
                    bar.style.height = (value ? Math.max(14, Math.round(value / peak * 100)) : 6) + '%';
                });
            });

            document.querySelectorAll('[data-live-schedule]').forEach(function (cell) {
                var now = data.by_schedule[cell.getAttribute('data-live-schedule')] || 0;
                var count = cell.querySelector('[data-live-schedule-count]');
                var dot = cell.querySelector('[data-live-schedule-dot]');
                if (count) {
                    count.textContent = number.format(now);
                    count.className = now ? 'font-medium text-green-700 dark:text-green-400' : 'text-gray-500 dark:text-gray-400';
                }
                if (dot) { dot.classList.toggle('hidden', !now); }
            });
            document.querySelectorAll('[data-live-schedule-phone]').forEach(function (cell) {
                var now = data.by_schedule[cell.getAttribute('data-live-schedule-phone')] || 0;
                setText('[data-live-schedule-count]', now, cell);
                cell.classList.toggle('hidden', !now);
                cell.classList.toggle('inline-flex', !!now);
            });

            // The first visitor came. The card turns once and stays turned.
            if (data.views_30m > 0) {
                document.querySelectorAll('[data-first-visitor-waiting]').forEach(function (el) { el.classList.add('hidden'); });
                document.querySelectorAll('[data-first-visitor-came]').forEach(function (el) { el.classList.remove('hidden'); });
            }
        }

        function plan() {
            clearTimeout(timer);
            if (!gone && document.visibilityState === 'visible') { timer = setTimeout(poll, EVERY_MS); }
        }
        function poll() {
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function (response) {
                    if ([401, 403, 404, 419].indexOf(response.status) !== -1) { gone = true; return null; }
                    return response.ok ? response.json() : null;
                })
                .then(function (data) { if (data) { draw(data); } })
                .catch(function () {})
                .then(plan);
        }

        document.addEventListener('visibilitychange', function () {
            clearTimeout(timer);
            if (document.visibilityState === 'visible' && !gone) { poll(); }
        });

        document.querySelectorAll('[data-copy-link]').forEach(function (button) {
            button.addEventListener('click', function () {
                var label = button.textContent;
                try {
                    navigator.clipboard.writeText(button.getAttribute('data-copy-link')).then(function () {
                        button.textContent = button.getAttribute('data-copied');
                        setTimeout(function () { button.textContent = label; }, 1600);
                    }, function () {});
                } catch (e) {}
            });
        });

        plan();
    });
</script>
