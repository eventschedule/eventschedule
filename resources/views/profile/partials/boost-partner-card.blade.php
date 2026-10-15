{{-- The card the platform's monthly boost fee is charged to. Rendered only for a partner account
     (profile/edit, the Boosts tab). Adding or changing it is a Stripe SetupIntent on the
     platform's own account, as boost/create loads Stripe.js: ask the server for the intent (it
     answers with the client secret and the publishable key), confirm the card in Stripe's own
     field, then hand the confirmed intent back to be saved. Nothing here is a form post.

     Expects $bpSettingsPartner (BoostPartner). --}}
@php
    $bpCard = $bpSettingsPartner;
    $bpCardRate = rtrim(rtrim(number_format((float) $bpCard->fee_rate * 100, 2, '.', ''), '0'), '.');
    $bpCardOn = (bool) config('services.stripe_platform.secret');
    $bpCardSecondary = 'ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800';
@endphp
<section>
    @include('profile.partials.heading', ['asideUrl' => null])
    <p class="form-kit-lead">{{ __('messages.boost_partner_card_lead', ['rate' => $bpCardRate]) }}</p>

    <div class="form-kit-fields space-y-4" id="bp-card" data-no-dirty
        data-intent-url="{{ route('boost.partner.settings.card_intent') }}" data-save-url="{{ route('boost.partner.settings.card') }}"
        data-failed="{{ __('messages.boost_partner_card_try_again') }}">
        <div class="event-picked">
            <div class="settings-picked-main">
                @if ($bpCard->hasCard())
                <span class="event-status is-on">{{ __('messages.saved_card_on_file', ['brand' => ucfirst((string) ($bpCard->pm_brand ?: 'card')), 'last4' => $bpCard->pm_last_four]) }}</span>
                @else
                <span class="event-status is-warn">{{ __('messages.boost_partner_no_card') }}</span>
                @endif
            </div>
            <div class="settings-picked-actions">
                <a href="{{ route('boost.partner.statements') }}" class="event-link">{{ __('messages.boost_partner_tab_statements') }}</a>
            </div>
        </div>

        @if ($bpCardOn)
        <div id="bp-card-form" class="space-y-3" hidden>
            <div id="bp-card-element" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-3"></div>
            <div class="settings-save" style="border-top: 0; padding-top: 0">
                <span class="settings-save-status"></span>
                <span class="flex flex-wrap items-center gap-3">
                    <button type="button" id="bp-card-cancel" class="{{ $bpCardSecondary }}">{{ __('messages.cancel') }}</button>
                    <x-brand-button type="button" id="bp-card-save">{{ __('messages.boost_partner_save_card') }}</x-brand-button>
                </span>
            </div>
        </div>
        <p id="bp-card-error" class="text-sm text-red-600 dark:text-red-400" role="alert" hidden></p>
        <div id="bp-card-start-row">
            <x-brand-button type="button" id="bp-card-start">{{ $bpCard->hasCard() ? __('messages.boost_partner_update_card') : __('messages.boost_partner_add_card') }}</x-brand-button>
        </div>
        @else
        @include('profile.partials.notice', ['noticeText' => __('messages.boost_partner_card_not_set_up'), 'noticeClass' => 'mb-0'])
        @endif
    </div>
</section>

@if ($bpCardOn)
<script src="https://js.stripe.com/v3/" {!! nonce_attr() !!}></script>
<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function () {
    var box = document.getElementById('bp-card');
    var start = document.getElementById('bp-card-start');
    var startRow = document.getElementById('bp-card-start-row');
    var form = document.getElementById('bp-card-form');
    var save = document.getElementById('bp-card-save');
    var cancel = document.getElementById('bp-card-cancel');
    var error = document.getElementById('bp-card-error');
    if (! box || ! start || ! form) {
        return;
    }
    var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var stripe = null;
    var card = null;
    var secret = null;

    function say(message) {
        error.textContent = message || box.getAttribute('data-failed');
        error.hidden = false;
    }
    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body || {}),
        }).then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (data) {
                if (! response.ok) {
                    throw new Error(data.error || '');
                }
                return data;
            });
        });
    }

    start.addEventListener('click', function () {
        if (typeof Stripe === 'undefined') {
            say();
            return;
        }
        start.disabled = true;
        error.hidden = true;
        post(box.getAttribute('data-intent-url')).then(function (data) {
            if (! data.client_secret || ! data.key) {
                throw new Error(data.error || '');
            }
            secret = data.client_secret;
            if (! stripe) {
                stripe = Stripe(data.key);
            }
            if (! card) {
                var dark = document.documentElement.classList.contains('dark');
                card = stripe.elements().create('card', {
                    style: { base: { fontSize: '16px', color: dark ? '#f3f4f6' : '#111827', '::placeholder': { color: '#9ca3af' } } },
                });
                card.mount('#bp-card-element');
            }
            form.hidden = false;
            startRow.hidden = true;
            card.focus();
        }).catch(function (e) {
            say(e.message);
        }).finally(function () {
            start.disabled = false;
        });
    });

    cancel.addEventListener('click', function () {
        form.hidden = true;
        startRow.hidden = false;
        error.hidden = true;
        start.focus();
    });

    save.addEventListener('click', function () {
        if (! stripe || ! card || ! secret) {
            return;
        }
        save.disabled = true;
        error.hidden = true;
        stripe.confirmCardSetup(secret, { payment_method: { card: card } }).then(function (result) {
            if (result.error) {
                throw new Error(result.error.message || '');
            }
            return post(box.getAttribute('data-save-url'), { setup_intent: result.setupIntent.id });
        }).then(function (data) {
            if (! data.ok) {
                throw new Error(data.error || '');
            }
            window.location.hash = 'section-boost-card';
            window.location.reload();
        }).catch(function (e) {
            say(e.message);
            save.disabled = false;
        });
    });
});
</script>
@endif
