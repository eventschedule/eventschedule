<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
            {{ __('messages.payment_methods') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.ticket_payment_methods_help') }}
        </p>
    </header>

    @if (is_demo_mode())
    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-yellow-800 dark:text-yellow-200 text-sm">
        {{ __('messages.demo_mode_settings_disabled') }}
    </div>
    @endif


    @php
        // The tab strip and its bodies both come from the registry, so a new gateway appears here by
        // existing rather than by having a hardcoded tab added. Cash has nothing to configure and is
        // filtered out by withSettings(). label(null) gives the product name without an account
        // suffix, which is what a tab wants.
        $settingsGateways = payment_gateways()->withSettings();
    @endphp

    <!-- Tab Navigation -->
    <div class="ap-tab-container border-b border-gray-200 dark:border-gray-700 mb-6 mt-6">
        <nav class="flex space-x-4 overflow-x-auto scrollbar-hide" aria-label="Tabs">
            @foreach ($settingsGateways as $gatewayKey => $gateway)
                <button type="button"
                    class="payment-tab text-center px-3 py-2 text-sm font-medium border-b-2 {{ $loop->first ? 'border-[var(--brand-blue)] text-[var(--brand-blue)]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}"
                    data-tab="{{ str_replace('_', '-', $gatewayKey) }}">
                    {{ $gateway->label(null) }}
                </button>
            @endforeach
        </nav>
    </div>

    @foreach ($settingsGateways as $gatewayKey => $gateway)
        <div id="payment-tab-{{ str_replace('_', '-', $gatewayKey) }}" class="payment-tab-content{{ $loop->first ? '' : ' hidden' }}">
            @if ($gateway->settingsView())
                @include($gateway->settingsView())
            @else
                @include('profile.partials.payments.credentials', ['gateway' => $gateway, 'gatewayKey' => $gatewayKey])
            @endif
        </div>
    @endforeach

</section>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    // Alert buttons for demo mode
    document.querySelectorAll('[data-alert]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            alert(this.dataset.alert);
        });
    });

    // Connect Stripe button
    var connectStripeBtn = document.getElementById('connect-stripe-btn');
    if (connectStripeBtn) {
        connectStripeBtn.addEventListener('click', function() {
            window.location.href = this.dataset.href;
        });
    }

    const paymentTabs = document.querySelectorAll('.payment-tab');
    const paymentTabContents = document.querySelectorAll('.payment-tab-content');

    // Restore active tab from localStorage
    const savedPaymentTab = localStorage.getItem('paymentActiveTab');
    if (savedPaymentTab) {
        switchPaymentTab(savedPaymentTab);
    }

    // "Change credentials" reveals the pre-filled form over the connected state.
    const invoiceninjaChangeBtn = document.getElementById('invoiceninja-change-btn');
    const invoiceninjaChangeForm = document.getElementById('invoiceninja-change-form');
    if (invoiceninjaChangeBtn && invoiceninjaChangeForm) {
        invoiceninjaChangeBtn.addEventListener('click', function() {
            invoiceninjaChangeForm.classList.remove('hidden');
            const urlField = document.getElementById('invoiceninja_change_api_url');
            if (urlField) {
                urlField.focus();
            }
        });
    }

    // The connect request is synchronous and can take up to 30s, so show progress.
    ['invoiceninja-connect-form', 'invoiceninja-change-form'].forEach(function(formId) {
        const form = document.getElementById(formId);
        if (!form) {
            return;
        }
        form.addEventListener('submit', function() {
            const button = form.querySelector('button[type="submit"], button:not([type])');
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

    @if (session('invoiceninja_error'))
        // A failed connection redirects back here, so make sure the panel is on screen.
        switchPaymentTab('invoiceninja');
        try { localStorage.setItem('paymentActiveTab', 'invoiceninja'); } catch (e) {}
        if (invoiceninjaChangeForm) {
            invoiceninjaChangeForm.classList.remove('hidden');
        }
    @endif

    paymentTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            switchPaymentTab(tabName);
            localStorage.setItem('paymentActiveTab', tabName);
        });
    });

    function switchPaymentTab(tabName) {
        paymentTabs.forEach(tab => {
            if (tab.dataset.tab === tabName) {
                tab.classList.add('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
                tab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            } else {
                tab.classList.remove('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
                tab.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            }
        });

        paymentTabContents.forEach(content => {
            const contentId = content.id.replace('payment-tab-', '');
            if (contentId === tabName) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });
    }
});
</script>
