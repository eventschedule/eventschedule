{{-- The page is server-rendered and holds people's, schedules' and events' names, so there is no
     Vue mount over it: Vue would compile those names as a template. What moves is small enough for
     plain DOM script - the two charts, "Show more", and the marks on what is new since the last
     visit - which is the pattern partials/form-kit-script uses for the same reason. --}}
@php
    $series = $active['series'] ?? [];
    $chartData = [
        'signups' => [
            'labels' => array_column($signups['days'] ?? [], 'label'),
            'organizers' => array_column($signups['days'] ?? [], 'organizers'),
            'others' => array_column($signups['days'] ?? [], 'others'),
        ],
        'active' => [
            'labels' => array_column($series, 'label'),
            'exact' => array_map(fn ($point) => $point['exact'] ? $point['active'] : null, $series),
            'estimate' => array_map(fn ($point) => $point['exact'] ? null : $point['active'], $series),
        ],
        'rtl' => is_rtl(),
    ];
    $chartText = [
        'organizers' => __('messages.admin_dash_organizers'),
        'others' => __('messages.admin_dash_other_accounts'),
        'exact' => __('messages.admin_dash_exact'),
        'estimate' => __('messages.admin_dash_estimate'),
        'exactFrom' => ($active['exact_from_label'] ?? null) ? __('messages.admin_dash_exact_from', ['date' => $active['exact_from_label']]) : null,
    ];
@endphp

<script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
<script {!! nonce_attr() !!}>
    (function () {
        var DATA = @json($chartData);
        var TEXT = @json($chartText);
        var root = document.getElementById('admin-dashboard');

        if (!root) {
            return;
        }

        /* ---- Show more: both lists at once ---- */
        root.addEventListener('click', function (event) {
            var button = event.target.closest('[data-show-more]');
            if (!button) {
                return;
            }

            var open = button.getAttribute('aria-expanded') !== 'true';
            root.querySelectorAll('[data-more-row]').forEach(function (row) { row.classList.toggle('hidden', !open); });
            root.querySelectorAll('[data-show-more]').forEach(function (each) {
                each.setAttribute('aria-expanded', open ? 'true' : 'false');
                each.querySelector('[data-show-more-label]').textContent = each.getAttribute(open ? 'data-less' : 'data-more');
                each.querySelector('svg').style.transform = open ? 'rotate(180deg)' : '';
            });
        });

        /* ---- New since the last visit, remembered in this browser only ---- */
        try {
            var KEY = 'adminDashboardSeenAt';
            var seen = parseInt(window.localStorage.getItem(KEY) || '', 10);

            if (seen) {
                root.querySelectorAll('[data-new-list]').forEach(function (list) {
                    var count = 0;
                    list.querySelectorAll('[data-created]').forEach(function (row) {
                        if (parseInt(row.getAttribute('data-created'), 10) * 1000 > seen) {
                            count++;
                            var dot = row.querySelector('[data-new-dot]');
                            if (dot) { dot.classList.remove('hidden'); }
                        }
                    });

                    var chip = list.querySelector('[data-new-chip]');
                    if (chip && count > 0) {
                        chip.textContent = chip.getAttribute('data-label') + ' ' + count;
                        chip.classList.remove('hidden');
                    }
                });
            }

            /* The server's time, as the rows carry: a browser clock five minutes fast would
               otherwise hide the next five minutes of rows. */
            var now = parseInt(root.getAttribute('data-now') || '', 10) * 1000;
            window.localStorage.setItem(KEY, String(now || Date.now()));
        } catch (e) {
            /* No storage: nothing is marked, and nothing else depends on it. */
        }

        /* ---- Charts ---- */
        if (typeof Chart === 'undefined') {
            return;
        }

        var charts = [];
        var still = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function probe(className, property) {
            var el = document.createElement('span');
            el.className = className;
            el.style.position = 'absolute';
            el.style.visibility = 'hidden';
            document.body.appendChild(el);
            var value = getComputedStyle(el)[property];
            el.remove();

            return value;
        }

        /* Read off the page, so the charts follow whichever of the six palettes is active. */
        function colors() {
            return {
                blue: getComputedStyle(document.documentElement).getPropertyValue('--brand-blue').trim() || '#4E81FA',
                gray: probe('bg-gray-400 dark:bg-gray-500', 'backgroundColor'),
                grid: probe('border-gray-200 dark:border-gray-700', 'borderTopColor'),
                ink: probe('text-gray-500 dark:text-gray-400', 'color'),
                surface: probe('bg-white dark:bg-gray-800', 'backgroundColor')
            };
        }

        function soft(color) {
            var match = color.match(/^#([0-9a-f]{6})$/i);

            return match ? color + '1f' : color.replace(/^rgb\((.+)\)$/, 'rgba($1, 0.12)');
        }

        function axes(c, stacked) {
            return {
                x: {
                    stacked: stacked, reverse: DATA.rtl, grid: { display: false }, border: { display: false },
                    ticks: { color: c.ink, font: { size: 11 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 6 }
                },
                y: {
                    stacked: stacked, beginAtZero: true, suggestedMax: 4, position: DATA.rtl ? 'right' : 'left',
                    grid: { color: c.grid }, border: { display: false },
                    ticks: { color: c.ink, font: { size: 11 }, maxTicksLimit: 4, precision: 0 }
                }
            };
        }

        function build() {
            charts.forEach(function (chart) { chart.destroy(); });
            charts = [];

            var c = colors();
            var base = {
                responsive: true, maintainAspectRatio: false, animation: still ? false : { duration: 300 },
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip: { padding: 10, cornerRadius: 8, boxPadding: 4, usePointStyle: true, rtl: DATA.rtl } }
            };

            var signups = document.getElementById('dash-signups-chart');
            if (signups) {
                charts.push(new Chart(signups, {
                    type: 'bar',
                    data: {
                        labels: DATA.signups.labels,
                        datasets: [
                            { label: TEXT.organizers, data: DATA.signups.organizers, backgroundColor: c.blue, borderRadius: 3, borderSkipped: false, maxBarThickness: 18, borderColor: c.surface, borderWidth: { top: 0, bottom: 1 } },
                            { label: TEXT.others, data: DATA.signups.others, backgroundColor: c.gray, borderRadius: 3, borderSkipped: false, maxBarThickness: 18 }
                        ]
                    },
                    options: Object.assign({}, base, { scales: axes(c, true) })
                }));
            }

            var active = document.getElementById('dash-active-chart');
            if (active) {
                var exact = DATA.active.exact;
                var last = exact.length - 1;
                var firstExact = exact.findIndex(function (value) { return value !== null; });

                /* A rule where the exact count begins. The two lines are never joined: the
                   estimate runs low, and a riser between them would read as growth. */
                var marker = {
                    id: 'exactFrom',
                    afterDatasetsDraw: function (chart) {
                        if (firstExact <= 0 || !TEXT.exactFrom) {
                            return;
                        }

                        var scale = chart.scales.x, area = chart.chartArea, ctx = chart.ctx;
                        var x = (scale.getPixelForValue(firstExact - 1) + scale.getPixelForValue(firstExact)) / 2;
                        var before = DATA.rtl ? 1 : -1;

                        ctx.save();
                        ctx.strokeStyle = c.ink;
                        ctx.globalAlpha = 0.45;
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(x, area.top);
                        ctx.lineTo(x, area.bottom);
                        ctx.stroke();
                        ctx.globalAlpha = 1;
                        ctx.fillStyle = c.ink;
                        ctx.font = '11px Inter, system-ui, sans-serif';
                        ctx.textAlign = DATA.rtl ? 'left' : 'right';
                        ctx.fillText(TEXT.exactFrom, x + before * 6, area.bottom - 6);
                        ctx.restore();
                    }
                };

                charts.push(new Chart(active, {
                    type: 'line',
                    data: {
                        labels: DATA.active.labels,
                        datasets: [
                            { label: TEXT.estimate, data: DATA.active.estimate, borderColor: c.gray, borderWidth: 2, borderDash: [5, 4], pointRadius: 0, pointHoverRadius: 4, tension: 0.3, fill: false },
                            { label: TEXT.exact, data: exact, borderColor: c.blue, backgroundColor: soft(c.blue), fill: true, borderWidth: 2, pointRadius: exact.map(function (value, index) { return index === last && value !== null ? 4 : 0; }), pointBackgroundColor: c.blue, pointBorderColor: c.surface, pointBorderWidth: 2, pointHoverRadius: 4, tension: 0.3 }
                        ]
                    },
                    options: Object.assign({}, base, { scales: axes(c, false), layout: { padding: { right: 6, top: 4 } } }),
                    plugins: [marker]
                }));
            }
        }

        build();

        /* A palette or light/dark switch repaints the charts; rebuilding is cheaper to get right
           than recolouring in place (Chart.js keeps the old element colours on update('none')). */
        new MutationObserver(build).observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });
    })();
</script>
