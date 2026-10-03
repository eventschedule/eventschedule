{{--
    OneSignal web-push bootstrap. Included from the AP and GP layouts ONLY when
    OneSignalService::isConfigured() is true, and never in an embed, so an unconfigured (e.g.
    selfhost) install never loads the SDK and makes no external calls.

    This is a standalone nonced <script> (NOT inside a Vue mount root) so the
    injected values are never compiled as a Vue template. The only injected
    user value is the current user's own encoded id, which is not attacker-
    controlled and is json-encoded regardless.

    The SDK is not even downloaded until push is something this browser asked for: either it
    already granted this site notification permission (a returning subscriber), or the visitor
    has just clicked an Enable button (window.esPush.enable()). Everyone else never contacts
    OneSignal, and is never tied to an account there - OneSignal.login() runs only inside the
    SDK's ready callback. We never auto-prompt.
--}}
@php
    $oneSignalAppId = config('services.onesignal.app_id');
    $oneSignalSafariWebId = config('services.onesignal.safari_web_id');
    $oneSignalExternalId = auth()->check() ? \App\Utils\UrlUtils::encodeId(auth()->id()) : null;
@endphp

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
        } catch (e) {
            // Never let push setup break the page.
            if (window.console) console.warn('OneSignal init failed', e);
        }
    });

    window.esPushLoad = (function () {
        // Read while this runs: the page body carries no per-request value.
        var nonce = (document.currentScript && document.currentScript.nonce) || '';
        var requested = false;
        return function () {
            if (requested) return;
            requested = true;
            var script = document.createElement('script');
            script.src = 'https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js';
            script.defer = true;
            script.nonce = nonce;
            document.head.appendChild(script);
        };
    })();

    // A returning subscriber: this browser already gave the site notification permission.
    try {
        if (typeof Notification !== 'undefined' && Notification.permission === 'granted') {
            window.esPushLoad();
        }
    } catch (e) {}

    // Helpers for our UI (enable button, status display). All no-op safely if
    // the SDK has not loaded.
    window.esPush = {
        // Returns 'enabled' | 'blocked' | 'default' | 'unsupported'
        status: function () {
            return new Promise(function (resolve) {
                if (!window.OneSignalDeferred || typeof Notification === 'undefined') return resolve('unsupported');
                // Answered from the browser alone until push is actually on, so asking whether to
                // show an Enable button never loads the SDK.
                if (Notification.permission === 'denied') return resolve('blocked');
                if (Notification.permission !== 'granted') return resolve('default');
                window.esPushLoad();
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
        // Prompt for permission and opt the device in. Resolves to the new status. The browser
        // prompt is requested inside the click itself, before the SDK has downloaded: Safari shows
        // it only within a user gesture, and an async script load would lose that.
        enable: function () {
            return new Promise(function (resolve) {
                if (!window.OneSignalDeferred || typeof Notification === 'undefined') return resolve('unsupported');
                var asked;
                try {
                    asked = Notification.requestPermission();
                } catch (e) {
                    asked = null;
                }
                Promise.resolve(asked).then(function (permission) {
                    permission = permission || Notification.permission;
                    if (permission !== 'granted') {
                        return resolve(permission === 'denied' ? 'blocked' : 'default');
                    }
                    window.esPushLoad();
                    window.OneSignalDeferred.push(async function (OneSignal) {
                        try {
                            if (OneSignal.User && OneSignal.User.PushSubscription) {
                                await OneSignal.User.PushSubscription.optIn();
                            }
                            resolve('enabled');
                        } catch (e) { resolve('unsupported'); }
                    });
                }, function () { resolve('unsupported'); });
            });
        },
    };
</script>
