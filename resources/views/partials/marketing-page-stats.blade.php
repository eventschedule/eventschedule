{{-- Per-page counters for the marketing site and the blog (App\Models\MarketingPageDaily): a
     `view` when the page opens, `reached_end` when its last section (the #claim finale, or the
     footer on a page without one) comes into view, and `signup_click` on a click of any sign-up
     link. Included by layouts/marketing inside @guest; the page is edge-cached, so all of it is
     sent from here. Each is sent once per page view; the server counts it once per visitor per
     day per page. Counts only: no cookie and no storage, so it runs without consent.

     The address has no host (POST /api/marketing-page answers on every host, the blog's too), so
     it is always same-origin and needs no session. sendBeacon never holds up the navigation a
     sign-up click starts. The viewport width is read once, so a visitor's view and click land on
     the same device. --}}
@if (config('app.is_nexus') && \App\Models\MarketingPageDaily::isCountablePage($statsPage ?? null))
    <script {!! nonce_attr() !!}>
        (function () {
            var url = @json(parse_url(route('marketing_page.beacon'), PHP_URL_PATH) ?: '/api/marketing-page');
            var page = @json($statsPage);
            var w = Math.round(window.innerWidth || document.documentElement.clientWidth || 0);
            if (!w) {
                return;
            }
            var sent = {};
            var send = function (kind) {
                if (sent[kind]) {
                    return;
                }
                sent[kind] = true;
                var body = JSON.stringify({ page: page, kind: kind, w: w });
                try {
                    if (navigator.sendBeacon && navigator.sendBeacon(url, body)) {
                        return;
                    }
                } catch (e) {}
                try {
                    fetch(url, { method: 'POST', keepalive: true, credentials: 'omit', body: body }).catch(function () {});
                } catch (e) {}
            };

            send('view');

            document.addEventListener('click', function (e) {
                var link = e.target && e.target.closest ? e.target.closest('a[href*="/sign_up"]') : null;
                if (link) {
                    send('signup_click');
                }
            }, true);

            var watch = function () {
                var end = document.getElementById('claim');
                if (!end) {
                    var footers = document.getElementsByTagName('footer');
                    end = footers.length ? footers[footers.length - 1] : null;
                }
                if (!end || !('IntersectionObserver' in window)) {
                    return;
                }
                var seen = new IntersectionObserver(function (entries) {
                    for (var i = 0; i < entries.length; i++) {
                        if (entries[i].isIntersecting) {
                            send('reached_end');
                            seen.disconnect();
                            return;
                        }
                    }
                });
                seen.observe(end);
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', watch);
            } else {
                watch();
            }
        })();
    </script>
@endif
