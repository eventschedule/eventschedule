{{-- The three answers a partner gives a request, each a native <dialog> (it traps focus, closes on
     Escape and dims the page): approve (with an optional "Payment already received" part, so one
     submit can do both), decline with a note the Business reads, and record a payment. One copy
     of each for the page: the button that opens it (data-bp-open, boost/partials/
     partner-decisions) carries where the form posts and what the dialog says about the request,
     and focus goes back to that button when the dialog closes.

     Plain DOM script, as on the portal's other list pages: nothing here is a Vue mount. --}}
@php
    $bpDlgField = 'mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]';
    $bpDlgSecondary = 'ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800';
@endphp

<dialog id="bp-dialog-approve" class="bp-dialog" aria-labelledby="bp-dialog-approve-title">
    <form method="POST" action="">
        @csrf
        <input type="hidden" name="version" value="" data-bp-value="version">
        <h2 id="bp-dialog-approve-title" class="bp-dialog-title">{{ __('messages.boost_partner_approve_title') }}</h2>
        <p class="bp-dialog-lead"><bdi data-bp-fill="name"></bdi> &middot; <span data-bp-fill="version-label"></span></p>
        {{-- Says the payment is still to come, so it is hidden once the payment is in. --}}
        <p class="bp-dialog-text" data-bp-unpaid-text>{{ __('messages.boost_partner_approve_text') }}</p>
        <div data-bp-payment-wrap>
            <x-toggle name="with_payment" id="bp-approve-with-payment" :label="__('messages.boost_partner_payment_already_received')" data-bp-payment-toggle />
            <div class="bp-dialog-part mt-3" data-bp-payment-part hidden>
                <p class="bp-dialog-text">{{ __('messages.ad_budget') }}: <span data-bp-fill="budget"></span></p>
                <div>
                    <x-input-label for="bp-approve-reference" :value="__('messages.boost_partner_your_reference')" />
                    <input type="text" id="bp-approve-reference" name="reference" maxlength="255" dir="ltr" autocomplete="off" class="{{ $bpDlgField }}">
                </div>
                <div>
                    <label for="bp-approve-amount" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.boost_partner_amount_received') }} (<span data-bp-fill="currency"></span>)</label>
                    <input type="number" id="bp-approve-amount" name="amount" min="0" max="1000000" step="0.01" inputmode="decimal" dir="ltr" class="{{ $bpDlgField }}">
                </div>
            </div>
        </div>
        <div class="page-form-actions">
            <button type="button" data-bp-close class="{{ $bpDlgSecondary }}">{{ __('messages.cancel') }}</button>
            <x-brand-button type="submit">{{ __('messages.approve') }}</x-brand-button>
        </div>
    </form>
</dialog>

<dialog id="bp-dialog-decline" class="bp-dialog" aria-labelledby="bp-dialog-decline-title">
    <form method="POST" action="">
        @csrf
        <h2 id="bp-dialog-decline-title" class="bp-dialog-title">{{ __('messages.boost_partner_decline_title') }}</h2>
        <p class="bp-dialog-lead"><bdi data-bp-fill="name"></bdi></p>
        <div>
            <x-input-label for="bp-decline-note" :value="__('messages.boost_partner_decline_note')" />
            <textarea id="bp-decline-note" name="note" rows="4" maxlength="1000" required dir="auto" class="{{ $bpDlgField }}"></textarea>
            <p class="bp-hint">{{ __('messages.boost_partner_decline_note_help') }}</p>
        </div>
        <div class="page-form-actions">
            <button type="button" data-bp-close class="{{ $bpDlgSecondary }}">{{ __('messages.cancel') }}</button>
            <x-danger-button>{{ __('messages.decline') }}</x-danger-button>
        </div>
    </form>
</dialog>

<dialog id="bp-dialog-payment" class="bp-dialog" aria-labelledby="bp-dialog-payment-title">
    <form method="POST" action="">
        @csrf
        <h2 id="bp-dialog-payment-title" class="bp-dialog-title">{{ __('messages.boost_partner_payment_received') }}</h2>
        <p class="bp-dialog-lead"><bdi data-bp-fill="name"></bdi></p>
        <p class="bp-dialog-text">{{ __('messages.boost_partner_payment_text') }} {{ __('messages.ad_budget') }}: <span data-bp-fill="budget"></span></p>
        <div>
            <x-input-label for="bp-payment-reference" :value="__('messages.boost_partner_your_reference')" />
            <input type="text" id="bp-payment-reference" name="reference" maxlength="255" required dir="ltr" autocomplete="off" class="{{ $bpDlgField }}">
        </div>
        <div>
            <label for="bp-payment-amount" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.boost_partner_amount_received') }} (<span data-bp-fill="currency"></span>)</label>
            <input type="number" id="bp-payment-amount" name="amount" min="0" max="1000000" step="0.01" inputmode="decimal" required dir="ltr" class="{{ $bpDlgField }}">
        </div>
        <div class="page-form-actions">
            <button type="button" data-bp-close class="{{ $bpDlgSecondary }}">{{ __('messages.cancel') }}</button>
            <x-brand-button type="submit">{{ __('messages.boost_partner_record_payment') }}</x-brand-button>
        </div>
    </form>
</dialog>

{{-- Stop a running boost from the Campaigns list, with a reason the Business is told. The
     form asks once more before it goes, as Stop does on the boost's own page. --}}
<dialog id="bp-dialog-stop" class="bp-dialog" aria-labelledby="bp-dialog-stop-title">
    <form method="POST" action="" data-confirm="{{ __('messages.boost_partner_stop_confirm_partner') }}">
        @csrf
        <h2 id="bp-dialog-stop-title" class="bp-dialog-title">{{ __('messages.boost_partner_stop_title') }}</h2>
        <p class="bp-dialog-lead"><bdi data-bp-fill="name"></bdi></p>
        <p class="bp-dialog-text">{{ __('messages.boost_partner_stop_lead') }}</p>
        <div>
            <x-input-label for="bp-stop-dialog-reason" :value="__('messages.boost_partner_stop_reason')" />
            <textarea id="bp-stop-dialog-reason" name="reason" rows="2" maxlength="500" dir="auto" class="{{ $bpDlgField }}"></textarea>
        </div>
        <div class="page-form-actions">
            <button type="button" data-bp-close class="{{ $bpDlgSecondary }}">{{ __('messages.cancel') }}</button>
            <x-danger-button>{{ __('messages.boost_partner_stop_boost') }}</x-danger-button>
        </div>
    </form>
</dialog>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function () {
    var opener = null;

    // The approve dialog's payment part: its fields are asked for, and sent, only while it is on.
    function syncPayment(dialog) {
        var toggle = dialog ? dialog.querySelector('[data-bp-payment-toggle]') : null;
        var part = dialog ? dialog.querySelector('[data-bp-payment-part]') : null;
        if (! toggle || ! part) {
            return;
        }
        part.hidden = ! toggle.checked;
        part.querySelectorAll('input').forEach(function (input) {
            input.required = toggle.checked;
            input.disabled = ! toggle.checked;
        });
    }

    document.addEventListener('click', function (event) {
        if (! event.target.closest) {
            return;
        }
        var trigger = event.target.closest('[data-bp-open]');
        if (trigger) {
            var dialog = document.getElementById('bp-dialog-' + trigger.getAttribute('data-bp-open'));
            if (! dialog || typeof dialog.showModal !== 'function') {
                return;
            }
            event.preventDefault();
            var form = dialog.querySelector('form');
            form.reset();
            form.action = trigger.getAttribute('data-action');
            dialog.querySelectorAll('[data-bp-fill]').forEach(function (el) {
                el.textContent = trigger.getAttribute('data-' + el.getAttribute('data-bp-fill')) || '';
            });
            dialog.querySelectorAll('[data-bp-value]').forEach(function (el) {
                el.value = trigger.getAttribute('data-' + el.getAttribute('data-bp-value')) || '';
            });
            // A payment already recorded for this budget is not asked for again.
            var wrap = dialog.querySelector('[data-bp-payment-wrap]');
            if (wrap) {
                wrap.hidden = trigger.getAttribute('data-paid') === '1';
            }
            var unpaidText = dialog.querySelector('[data-bp-unpaid-text]');
            if (unpaidText) {
                unpaidText.hidden = trigger.getAttribute('data-paid') === '1';
            }
            syncPayment(dialog);
            opener = trigger;
            dialog.showModal();
            return;
        }
        var close = event.target.closest('[data-bp-close]');
        if (close && close.closest('dialog')) {
            close.closest('dialog').close();
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.matches && event.target.matches('[data-bp-payment-toggle]')) {
            syncPayment(event.target.closest('dialog'));
        }
    });

    document.querySelectorAll('dialog.bp-dialog').forEach(function (dialog) {
        dialog.addEventListener('close', function () {
            if (opener && document.body.contains(opener)) {
                opener.focus();
            }
            opener = null;
        });
        // A press on the dimmed page around the dialog closes it, as Escape does.
        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });
});
</script>
