{{-- The cards under the live part of /admin/dashboard. They hold people's, schedules' and events'
     names, so there is no Vue mount over them: Vue would compile those names as a template. What
     moves is plain DOM script, the pattern partials/form-kit-script uses for the same reason:

     - Each panel (data-dash-panel) is fetched the first time it comes within a screen's reach of
       the viewport, never with the page: the old dashboard ran every card's queries before it
       could send a byte. A panel that fails says so with a Retry; one refused because the admin
       re-auth window lapsed (423) links to the password prompt.
     - Then, on what came back: "Show more", the marks on what is new since the last visit, and
       the two charts, whose numbers ride in the fragment's JSON block. --}}
<script {!! nonce_attr() !!}>
    (function () {
        var root = document.getElementById('admin-dashboard');

        if (!root) {
            return;
        }

        var DATA = null;
        var TEXT = {};
        var charts = [];
        var still = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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
        function markNew(fragment) {
            if (!fragment.querySelector('[data-new-list]')) {
                return;
            }

            try {
                var KEY = 'adminDashboardSeenAt';
                var seen = parseInt(window.localStorage.getItem(KEY) || '', 10);

                if (seen) {
                    fragment.querySelectorAll('[data-new-list]').forEach(function (list) {
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
                   otherwise hide the next five minutes of rows. Written once the lists are marked,
                   so a visit that never scrolled down to them does not count as having seen them. */
                var now = parseInt(fragment.getAttribute('data-now') || '', 10) * 1000;
                window.localStorage.setItem(KEY, String(now || Date.now()));
            } catch (e) {
                /* No storage: nothing is marked, and nothing else depends on it. */
            }
        }

        /* ---- Charts ---- */
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

            if (!DATA || typeof Chart === 'undefined') {
                return;
            }

            var c = colors();
            var base = {
                responsive: true, maintainAspectRatio: false, animation: still ? false : { duration: 300 },
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip: { padding: 10, cornerRadius: 8, boxPadding: 4, usePointStyle: true, rtl: DATA.rtl } }
            };

            var signups = root.querySelector('#dash-signups-chart');
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

            var active = root.querySelector('#dash-active-chart');
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

        /* A palette or light/dark switch repaints the charts; rebuilding is cheaper to get right
           than recolouring in place (Chart.js keeps the old element colours on update('none')). */
        new MutationObserver(build).observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });

        /* ---- Loading the panels ---- */
        var panelUrl = root.getAttribute('data-panel-url');
        var sample = root.hasAttribute('data-sample');

        /* Retry is pressed inside the slot it replaces: keep keyboard focus in the slot, on the
           first thing in it that takes focus, rather than dropping it to the top of the page. */
        function swap(slot, content) {
            var focused = slot.hasAttribute('data-keep-focus') || slot.contains(document.activeElement);
            slot.removeAttribute('data-keep-focus');
            slot.replaceChildren(content);
            if (focused) {
                var target = content.querySelector('a[href], button');
                if (target) {
                    target.focus({ preventScroll: true });
                }
            }
        }

        function settle(slot, fragment) {
            /* The placeholder's height and columns were for the skeleton. */
            slot.className = '';
            slot.removeAttribute('style');
            slot.removeAttribute('aria-busy');
            slot.removeAttribute('aria-label');
            swap(slot, fragment);
            markNew(fragment);

            var json = fragment.querySelector('script[data-dash-charts]');
            if (json) {
                try {
                    var parsed = JSON.parse(json.textContent);
                    DATA = parsed.data;
                    TEXT = parsed.text;
                } catch (e) {
                    DATA = null;
                }
                build();
            }

            /* A new install has one card to show, and charts of nothing under it would say
               "nothing yet" four more times. */
            if (fragment.hasAttribute('data-first-run')) {
                root.querySelectorAll('[data-dash-panel="growth"]').forEach(function (other) { other.remove(); });
            }
        }

        function trouble(slot, reauth) {
            slot.className = '';
            slot.removeAttribute('style');
            slot.removeAttribute('aria-busy');
            slot.removeAttribute('aria-label');
            var box = document.createElement('div');
            box.className = 'ap-card rounded-xl p-4 flex flex-wrap items-center justify-between gap-3';
            var text = document.createElement('p');
            text.className = 'text-sm text-gray-700 dark:text-gray-300';
            text.textContent = root.getAttribute(reauth ? 'data-reauth' : 'data-failed');
            box.appendChild(text);

            var action;
            if (reauth) {
                action = document.createElement('a');
                /* The address the page is on, which keeps its filters and its Trends range. */
                action.href = window.location.href;
            } else {
                action = document.createElement('button');
                action.type = 'button';
                action.addEventListener('click', function () {
                    /* A flag, not `disabled`: a button that is disabled while it has focus loses it. */
                    if (action.hasAttribute('aria-disabled')) {
                        return;
                    }
                    action.setAttribute('aria-disabled', 'true');
                    slot.setAttribute('data-keep-focus', '');
                    load(slot);
                });
            }
            action.className = 'text-sm font-medium text-[var(--brand-blue)] hover:underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]';
            action.textContent = root.getAttribute(reauth ? 'data-confirm' : 'data-retry');
            box.appendChild(action);
            swap(slot, box);
        }

        function load(slot) {
            var name = slot.getAttribute('data-dash-panel');
            slot.setAttribute('aria-busy', 'true');

            /* Accept anything: with text/html the admin middleware takes this for a page and
               answers a lapsed password confirmation with a redirect to its form, which fetch
               follows, and the form's page was put where the panel goes. As a script's request it
               gets the 423 the poll gets. */
            fetch(panelUrl.replace('__panel__', encodeURIComponent(name)) + (sample ? '?sample=1' : ''), {
                headers: { 'Accept': '*/*', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            }).then(function (response) {
                if (!response.ok || response.redirected) {
                    trouble(slot, response.status === 423 || response.redirected);
                    return null;
                }

                return response.text();
            }).then(function (html) {
                if (html === null) {
                    return;
                }

                var template = document.createElement('template');
                template.innerHTML = html.trim();
                var fragment = template.content.firstElementChild;
                if (fragment && fragment.matches('[data-dash-fragment]')) {
                    settle(slot, fragment);
                } else {
                    trouble(slot, false);
                }
            }).catch(function () {
                trouble(slot, false);
            });
        }

        var slots = Array.prototype.slice.call(root.querySelectorAll('[data-dash-panel]'));

        if (!('IntersectionObserver' in window)) {
            slots.forEach(load);
            return;
        }

        /* A little ahead of the screen, so a panel is usually there by the time it is scrolled to. */
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    observer.unobserve(entry.target);
                    load(entry.target);
                }
            });
        }, { rootMargin: '0px 0px 200px 0px' });

        /* Not before the live part above is drawn (its script says when): until then it is a
           skeleton, the page is short, and every panel looked near the screen. A poll that never
           answers (a lapsed password confirmation) must not keep the cards away for good. */
        var started = false;
        function start() {
            if (started) {
                return;
            }
            started = true;
            slots.forEach(function (slot) { observer.observe(slot); });
        }

        if (window.adminDashboardLiveDrawn) {
            start();
        } else {
            window.addEventListener('admin-dashboard:live-drawn', start);
            setTimeout(start, 8000);
        }
    })();
</script>
