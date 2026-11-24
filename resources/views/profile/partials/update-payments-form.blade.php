@php
    // Nothing connected yet, on an install that offers Stripe Connect: Stripe is the one way most
    // organizers get paid, so it is offered on its own, with its button, and the others are
    // listed under it (the 2026-11 redesign). Its row and pane stay in the page, folded away, for
    // the Help link and for the pane's own messages.
    $nothingConnected = ! collect($paymentStates)->contains(fn ($state) => $state['on'] || $state['warn']);
    $stripeHero = $nothingConnected && config('app.hosted') && isset($settingsGateways['stripe'])
        && ! $user->stripe_account_id && ! is_demo_mode();
@endphp
<section>
    @include('profile.partials.heading', ['headingPill' => $nothingConnected ? __('messages.settings_none_connected') : null])
    {{-- Which of these a buyer pays through is chosen on each event (its Tickets tab), so more
         than one can be connected: said here, where five rows gave no hint of it. --}}
    <p class="form-kit-lead">{{ __('messages.ticket_payment_methods_help') }}. {{ __('messages.settings_payment_per_event') }}</p>

    @include('profile.partials.notice', ['noticeDemo' => true])

    {{-- One row per payment method, where a strip of tabs used to be: the row says whether the
         method is connected, so all of them can be read at once. The rows and their panes both
         come from the registry ($settingsGateways and $paymentStates, set in profile/edit), so a
         new gateway appears here by existing rather than by having a row added. Cash has nothing
         to configure and is filtered out by withSettings(). The pane ids are what the Help link
         and PaymentGatewayRoutesTest read. --}}
    @if ($stripeHero)
    <div class="payment-hero">
        <div class="min-w-0">
            <p class="payment-hero-title">{{ $settingsGateways['stripe']->label(null) }}</p>
            <p class="payment-hero-help">{{ $paymentStates['stripe']['text'] }}</p>
        </div>
        <x-brand-link href="{{ route('stripe.link') }}" class="gap-2 flex-none">
            {{ __('messages.connect_stripe') }}
            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
        </x-brand-link>
    </div>
    <p class="payment-other-label">{{ __('messages.other_ways_to_get_paid') }}</p>
    @endif

    <div class="event-subrows payment-rows">
        @foreach ($settingsGateways as $gatewayKey => $gateway)
            @php
                $gatewayTab = str_replace('_', '-', $gatewayKey);
                $gatewayOff = ! $paymentStates[$gatewayKey]['on'] && ! $paymentStates[$gatewayKey]['warn'];
            @endphp
            <x-form-row group="payment" :tab="$gatewayTab"
                :class="'payment-tab'.($stripeHero && $gatewayKey === 'stripe' ? ' is-hero-twin' : '')"
                :title="$gateway->label(null)"
                :summary="$paymentStates[$gatewayKey]['text']"
                :warn="$paymentStates[$gatewayKey]['warn']"
                :muted="$gatewayOff"
                :data-row-action="$gatewayOff ? __('messages.row_action_set_up') : null" />
            <div id="payment-tab-{{ $gatewayTab }}" class="event-subrow-body" hidden>
                <div class="form-kit-fields">
                    @if ($gateway->settingsView())
                        @include($gateway->settingsView())
                    @else
                        @include('profile.partials.payments.credentials', ['gateway' => $gateway, 'gatewayKey' => $gatewayKey])
                    @endif
                    @include('profile.partials.payments.payout-note')
                </div>
            </div>
        @endforeach
    </div>

</section>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    // The connect request is synchronous and can take up to 30s, so show progress.
    ['invoiceninja-connect-form', 'invoiceninja-change-form'].forEach(function(formId) {
        const form = document.getElementById(formId);
        if (!form) {
            return;
        }
        form.addEventListener('submit', function() {
            const button = form.querySelector('button[type="submit"]');
            if (!button || button.disabled) {
                return;
            }
            button.disabled = true;
            button.innerHTML = '<svg class="animate-spin h-4 w-4 me-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">'
                + '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>'
                + '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>'
                + '</svg>' + @json(__('messages.connecting'), JSON_UNESCAPED_UNICODE);
        });
    });

    @if ($nothingConnected && ! $stripeHero)
        // Nothing is connected yet: the first method opens by itself, as its tab used to, so
        // "Connect" is on the page and five closed rows are not all there is. After everything
        // else has had its say about which row to open (a refused form, the row just acted on).
        window.addEventListener('load', function() {
            var firstMethod = document.querySelector('button[data-row-group="payment"]');
            if (firstMethod && ! FormKit.openTab('payment')) {
                FormKit.openRow('payment', firstMethod.getAttribute('data-tab'));
            }
        });
    @endif

    @if (session('invoiceninja_error'))
        // A failed connection redirects back here, so make sure its row and its form are on screen.
        FormKit.openRow('payment', 'invoiceninja');
        var invoiceninjaChangeForm = document.getElementById('invoiceninja-change-form');
        if (invoiceninjaChangeForm) {
            invoiceninjaChangeForm.hidden = false;
        }
    @endif
});
</script>
