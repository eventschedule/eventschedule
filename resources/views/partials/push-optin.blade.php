{{--
    Reusable guest-portal push opt-in prompt. Include with:
        @include('partials.push-optin', ['pushEmail' => $sale->email, 'pushName' => $event->name])

    Only renders when OneSignal is configured. We never auto-prompt: the native
    browser permission request fires only when the visitor clicks Enable. For
    account-less guests we then attach a hashed-email alias so the server can
    target them (matching OneSignalService::emailAlias()).
--}}
@if (\App\Services\OneSignalService::isConfigured())
@php
    $pushOptinEmail = $pushEmail ?? null;
    $pushOptinAlias = $pushOptinEmail ? \App\Services\OneSignalService::emailAlias($pushOptinEmail) : null;
    $pushOptinName = $pushName ?? '';
@endphp
<div id="es-push-optin" data-alias="{{ $pushOptinAlias }}"
    class="hidden rounded-2xl p-[20px] shadow-sm"
    style="background-color: var(--brand-button-bg); color:#fff;">
    <div class="flex items-start gap-3">
        <svg class="w-6 h-6 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <div class="flex-1">
            <h3 class="text-[15px] font-semibold">{{ __('messages.push_optin_title') }}</h3>
            <p class="text-[13px] opacity-90 mt-1">{{ __('messages.push_optin_body', ['name' => $pushOptinName]) }}</p>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <button type="button" id="es-push-optin-enable"
                    class="inline-flex items-center justify-center px-4 py-2 text-[14px] font-semibold rounded-lg bg-white text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white/70 transition-colors">
                    {{ __('messages.push_optin_enable') }}
                </button>
                <button type="button" id="es-push-optin-dismiss"
                    class="inline-flex items-center justify-center px-3 py-2 text-[13px] font-medium text-white/90 hover:text-white focus:outline-none transition-colors">
                    {{ __('messages.push_optin_dismiss') }}
                </button>
            </div>
        </div>
    </div>
</div>
<script {!! nonce_attr() !!}>
(function () {
    var el = document.getElementById('es-push-optin');
    if (!el || !window.esPush) return;
    var KEY = 'es_push_optin_dismissed';
    try { if (localStorage.getItem(KEY) === '1') return; } catch (e) {}

    var alias = el.getAttribute('data-alias');

    // Only surface the prompt when push is supported and not already enabled/blocked.
    window.esPush.status().then(function (status) {
        if (status === 'enabled' || status === 'blocked' || status === 'unsupported') return;
        el.classList.remove('hidden');
    });

    var enableBtn = document.getElementById('es-push-optin-enable');
    var dismissBtn = document.getElementById('es-push-optin-dismiss');

    enableBtn.addEventListener('click', function () {
        enableBtn.disabled = true;
        window.esPush.enable().then(function (status) {
            if (status === 'enabled' && alias && window.OneSignalDeferred) {
                window.OneSignalDeferred.push(function (OneSignal) {
                    try { OneSignal.User.addAlias('es_email', alias); } catch (e) {}
                });
            }
            el.classList.add('hidden');
        });
    });

    dismissBtn.addEventListener('click', function () {
        try { localStorage.setItem(KEY, '1'); } catch (e) {}
        el.classList.add('hidden');
    });
})();
</script>
@endif
