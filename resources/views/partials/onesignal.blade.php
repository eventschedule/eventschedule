{{--
    OneSignal web-push bootstrap. Included from the AP and GP layouts ONLY when
    OneSignalService::isConfigured() is true, so an unconfigured (e.g. selfhost)
    install never loads the SDK and makes no external calls.

    This is a standalone nonced <script> (NOT inside a Vue mount root) so the
    injected values are never compiled as a Vue template. The only injected
    user value is the current user's own encoded id, which is not attacker-
    controlled and is json-encoded regardless.

    We never auto-prompt for permission. The init only loads the SDK and, for
    logged-in users, ties the browser to the user (external_id). UI code calls
    window.esPush.enable() / .status() on an explicit user action.
--}}
@php
    $oneSignalAppId = config('services.onesignal.app_id');
    $oneSignalSafariWebId = config('services.onesignal.safari_web_id');
    $oneSignalExternalId = auth()->check() ? \App\Utils\UrlUtils::encodeId(auth()->id()) : null;
@endphp

<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer {!! nonce_attr() !!}></script>
<script {!! nonce_attr() !!}>
    window.OneSignalDeferred = window.OneSignalDeferred || [];
    window.OneSignalDeferred.push(async function (OneSignal) {
        try {
            await OneSignal.init({
                appId: @json($oneSignalAppId),
                @if ($oneSignalSafariWebId) safari_web_id: @json($oneSignalSafariWebId), @endif
                allowLocalhostAsSecureOrigin: {{ app()->environment('local') ? 'true' : 'false' }},
                // We drive prompting ourselves; do not show OneSignal's auto prompts.
                autoResubscribe: true,
            });

            @if ($oneSignalExternalId)
                // Tie this browser's subscription to the logged-in user so the
                // server can target them by external_id.
                await OneSignal.login(@json($oneSignalExternalId));
            @endif

            // Some pages (e.g. ticket confirmation) set a guest email alias so
            // account-less buyers can be targeted. They push a callback that
            // runs once the SDK is ready.
            if (Array.isArray(window.__esPushAliasQueue)) {
                window.__esPushAliasQueue.forEach(function (alias) {
                    try { OneSignal.User.addAlias('es_email', alias); } catch (e) {}
                });
            }
        } catch (e) {
            // Never let push setup break the page.
            if (window.console) console.warn('OneSignal init failed', e);
        }
    });

    // Helpers for our UI (enable button, status display). All no-op safely if
    // the SDK has not loaded.
    window.esPush = {
        // Returns 'enabled' | 'blocked' | 'default' | 'unsupported'
        status: function () {
            return new Promise(function (resolve) {
                if (!window.OneSignalDeferred) return resolve('unsupported');
                window.OneSignalDeferred.push(async function (OneSignal) {
                    try {
                        var permission = OneSignal.Notifications.permission; // boolean
                        var native = (typeof Notification !== 'undefined') ? Notification.permission : 'default';
                        if (native === 'denied') return resolve('blocked');
                        var optedIn = OneSignal.User && OneSignal.User.PushSubscription
                            ? OneSignal.User.PushSubscription.optedIn : false;
                        resolve(permission && optedIn ? 'enabled' : 'default');
                    } catch (e) { resolve('unsupported'); }
                });
            });
        },
        // Prompt for permission and opt the device in. Resolves to the new status.
        enable: function () {
            return new Promise(function (resolve) {
                if (!window.OneSignalDeferred) return resolve('unsupported');
                window.OneSignalDeferred.push(async function (OneSignal) {
                    try {
                        await OneSignal.Notifications.requestPermission();
                        if (OneSignal.User && OneSignal.User.PushSubscription) {
                            await OneSignal.User.PushSubscription.optIn();
                        }
                        var native = (typeof Notification !== 'undefined') ? Notification.permission : 'default';
                        resolve(native === 'granted' ? 'enabled' : (native === 'denied' ? 'blocked' : 'default'));
                    } catch (e) { resolve('unsupported'); }
                });
            });
        },
    };
</script>
