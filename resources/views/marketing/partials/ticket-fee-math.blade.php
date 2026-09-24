{{--
    The ticket fee formula, for the scripts that recompute a calculator while the visitor types.

    It is App\Utils\TicketFees::costOf() line for line, in the same order of operations, so a
    keystroke lands on the same numbers the server rendered with. The rates themselves never appear
    here: each calculator hands its script TicketFees::forScript() as a data-rates attribute. Change
    the formula in one place and you have to change it in the other.

    Included by <x-marketing.fee-calculator> and by /pricing. The assignment is guarded, so a page
    that carries both still defines it once.
--}}
<script {!! nonce_attr() !!}>
    window.esTicketFeeCost = window.esTicketFeeCost || function esTicketFeeCost(rate, stripe, tickets, price) {
        tickets = Number(tickets) || 0;
        price = Number(price) || 0;

        // Nothing sold, or nothing charged: no platform takes a fee on a free ticket.
        if (tickets <= 0 || price <= 0) {
            return 0;
        }

        // Several plans (Luma's free and Plus): the cheaper one for this event.
        if (rate.plans && rate.plans.length) {
            var best = null;
            rate.plans.forEach(function (plan) {
                var merged = {};
                Object.keys(rate).forEach(function (key) { if (key !== 'plans') { merged[key] = rate[key]; } });
                Object.keys(plan).forEach(function (key) { merged[key] = plan[key]; });
                var planCost = esTicketFeeCost(merged, stripe, tickets, price);
                if (best === null || planCost < best) { best = planCost; }
            });
            return best;
        }

        var revenue = tickets * price;
        var perTicket = price * (rate.percent || 0) + (rate.fixed || 0);
        var cost = tickets * perTicket + revenue * (rate.processing || 0) + (rate.monthly || 0);

        if (rate.stripe !== false) {
            cost += revenue * stripe.percent + tickets * stripe.fixed;
        }

        return cost;
    };
</script>
