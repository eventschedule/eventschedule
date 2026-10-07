{{-- The guest pages' three browser-side counts (App\Utils\GuestFunnel): a tap from a list into an
     event, a form opened, Add to calendar used. Included by role/show-guest and event/show-guest.

     Printed ONLY when this visit counts: the beacon has no session and cannot tell a schedule's
     own team from its audience, so the page decides. Nothing about the visitor is sent, only the
     name of the stage; the server counts one visitor per day by the daily-salted hash the page-view
     counters use, and keeps a number.

     Each stage is sent once per page. Inline and vanilla for the reasons partials/realtime-beacon
     gives, and it must never throw: an error in an inline script is reported against the page.

     $formOpen: the server already knows the form opens with the page (?tickets=true, ?rsvp=true).
     A Blade comment, not a script one: this text must not reach the browser. --}}
@if (\App\Utils\GuestFunnel::counts(request(), $role))
<script {!! nonce_attr() !!}>
(function () {
    var url = @json(\App\Utils\GuestFunnel::beaconPath());
    var sent = {};

    function count(stage) {
        try {
            if (sent[stage] || ! navigator.sendBeacon) {
                return;
            }
            sent[stage] = true;
            navigator.sendBeacon(url, JSON.stringify({ s: stage }));
        } catch (e) {}
    }

    window.esGuestFunnel = count;

    try {
        window.addEventListener('show-event-form', function () { count('form_open'); });

        document.addEventListener('click', function (e) {
            var el = e.target && e.target.closest ? e.target.closest('[data-funnel]') : null;
            if (el) {
                count(el.getAttribute('data-funnel'));
            }
        }, true);

        @if (! empty($formOpen))
        count('form_open');
        @endif
    } catch (e) {}
})();
</script>
@endif
