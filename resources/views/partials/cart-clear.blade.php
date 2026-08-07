{{--
    Drop the legs a buyer has just paid for from the guest cart.

    The cart lives in localStorage under es_cart_<subdomain> and is rendered only by the guest
    layout. Both post-purchase landing pages go through x-app-layout, so the widget is not on the
    page to empty itself - and a cart still holding a completed purchase shows a live CHECKOUT
    button on the buyer's next visit to the schedule, which would charge them for it again.

    Driven by the 'cart_purchased' flash that TicketController::redirectToPurchaseLanding() sets, so
    it fires only on the redirect that follows a purchase. A ticket link is permanent and gets
    reopened long afterwards; clearing on every view would silently empty a cart the buyer had
    since refilled. An abandoned payment (checkout.cancel) sets no flash and keeps its cart.
--}}
@php
    $cartPurchasedLegs = collect(session('cart_purchased', []))
        ->filter(fn ($leg) => ! empty($leg['subdomain']) && ! empty($leg['event_id']))
        ->values();
@endphp
@if ($cartPurchasedLegs->isNotEmpty())
<script {!! nonce_attr() !!}>
(function () {
    var purchased = @json($cartPurchasedLegs);

    purchased.forEach(function (leg) {
        var key = 'es_cart_' + leg.subdomain;

        try {
            var raw = localStorage.getItem(key);
            if (! raw) {
                return;
            }

            var stored = JSON.parse(raw);
            if (! Array.isArray(stored)) {
                return;
            }

            // Matched on event AND date, not just the key, so a leg the buyer added but did not
            // buy survives - including the other date of a recurring event. A stored leg with no
            // date is a one-time event, whose sale row carries a date derived server-side, so it
            // can never match on equality and is matched on the event alone.
            var remaining = stored.filter(function (entry) {
                if (entry.event_id !== leg.event_id) {
                    return true;
                }

                return entry.event_date ? entry.event_date !== leg.event_date : false;
            });

            if (remaining.length === stored.length) {
                return;
            }

            if (remaining.length) {
                localStorage.setItem(key, JSON.stringify(remaining));
            } else {
                localStorage.removeItem(key);
            }
        } catch (e) {}
    });
})();
</script>
@endif
