{{-- Back from a payment provider before its confirmation reached us (ticket/view, ticket/order).

     The element marked data-confirming says how many seconds the page may go on saying so and
     where to ask (GET ticket.status: one word, a counter of its own). Asked after 3, 4, 6, 9, 13,
     20 and 30 seconds, which is about eight asks in a minute and a half where it used to be the
     whole page every four seconds, and again whenever the tab is looked at. When the answer
     changes, or the seconds run out, the page is loaded once more and shows whatever is true. --}}
<script {!! nonce_attr() !!}>
(function () {
    try {
        var box = document.querySelector('[data-confirming]');
        if (! box) { return; }

        var url = box.getAttribute('data-status-url');
        var deadline = Date.now() + (parseInt(box.getAttribute('data-confirming'), 10) || 0) * 1000;
        var waits = [3, 4, 6, 9, 13, 20, 30];
        var turn = 0, timer = null, asking = false, done = false;
        var main = document.getElementById('main-content');

        var settle = function (status) {
            if (done) { return; }
            done = true;
            clearTimeout(timer);
            {{-- For the ticket to arrive properly once it is paid (ticket/view reads it). --}}
            try {
                if (status === 'paid' && main && main.getAttribute('data-ticket')) {
                    sessionStorage.setItem('es_ticket_arrived_' + main.getAttribute('data-ticket'), '1');
                }
            } catch (e) {}
            window.location.reload();
        };
        var next = function () {
            var left = deadline - Date.now();
            if (left <= 0) { settle('unpaid'); return; }
            timer = setTimeout(ask, Math.min(waits[Math.min(turn++, waits.length - 1)] * 1000, left + 500));
        };
        var ask = function () {
            if (asking || done) { return; }
            asking = true;
            clearTimeout(timer);
            fetch(url, { credentials: 'same-origin', cache: 'no-store', headers: { 'Accept': 'application/json' } })
                .then(function (response) { return response.ok ? response.json() : null; })
                .then(function (answer) {
                    asking = false;
                    if (answer && answer.status && answer.status !== 'unpaid') { settle(answer.status); } else { next(); }
                })
                .catch(function () { asking = false; next(); });
        };

        next();
        document.addEventListener('visibilitychange', function () { if (! document.hidden) { ask(); } });
    } catch (e) {}
})();
</script>
