@php
    $followUser = auth()->user();
    $followIsGuest = ! $followUser;
    $followInitiallyDismissed = $followUser ? (bool) $followUser->follow_consent_dismissed : false;
@endphp

<div id="follow-consent-modal-app" data-vue-root
    data-is-guest="{{ $followIsGuest ? '1' : '0' }}"
    data-user-dismissed="{{ $followInitiallyDismissed ? '1' : '0' }}">
    <follow-consent-modal></follow-consent-modal>
</div>

<script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}"{!! nonce_attr() !!}><\/script>')</script>
<script {!! nonce_attr() !!}>
// Following changes something, so it is sent as a form with the page's token and never opened
// as an address: an address can be loaded by a page, a form with the token cannot be sent by one.
window.esPostFollow = function (url) {
    var form = document.createElement('form');
    var token = document.querySelector('meta[name="csrf-token"]');
    var field = document.createElement('input');
    form.method = 'POST';
    form.action = url;
    field.type = 'hidden';
    field.name = '_token';
    field.value = token ? token.getAttribute('content') : '';
    form.appendChild(field);
    document.body.appendChild(form);
    form.submit();
};

// Fallback if Vue fails to load - clicks on Follow triggers still follow.
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-follow-trigger]');
    if (!btn || e.defaultPrevented) return;
    const url = btn.dataset.followUrl;
    if (url) window.esPostFollow(url);
});

document.addEventListener('DOMContentLoaded', function() {
    if (typeof Vue === 'undefined') {
        return;
    }

    const mountEl = document.getElementById('follow-consent-modal-app');
    if (!mountEl) return;

    const isGuest = mountEl.dataset.isGuest === '1';
    const initiallyDismissed = mountEl.dataset.userDismissed === '1';

    const { createApp, ref, computed, onMounted, onBeforeUnmount, nextTick } = Vue;

    const app = createApp({
        setup() {
            const open = ref(false);
            const dontAskAgain = ref(false);
            const submitting = ref(false);
            const followUrl = ref('');
            const subscribeUrl = ref('');
            // Whether confirming will actually mint an account for THIS target, so the note below
            // is a statement rather than a promise. It cannot be decided in Blade: one modal serves
            // every Follow trigger on the page, and on an event page each performer is a different
            // schedule. The trigger carries the answer, and a page cached from before this shipped
            // carries nothing - which correctly reads as false.
            const accountNote = ref(false);
            // The panel renders this button's label through Role::customLabel(), so hardcoding the
            // translated string here gave an owner who customised it two different labels for the
            // same action on the same page. The trigger carries the resolved value.
            const subscribeLabel = ref(@json(__('messages.email_me_new_events'), JSON_UNESCAPED_UNICODE));
            const email = ref('');
            const subscriberName = ref('');
            // Honeypot. It has to live in the Vue template below rather than as a Blade honeypot
            // component beside the mount: Vue's template option REPLACES the mount element's
            // contents, so a server-rendered field there is destroyed before anything reads it.
            //
            // And do NOT write the component's tag name literally in a comment here - Blade
            // compiles component tags anywhere in the file, JS comments included, which silently
            // emits a second real honeypot into the page.
            const website = ref('');
            const resultMessage = ref('');
            const resultSuccess = ref(false);
            const done = ref(false);
            const scheduleName = ref('');
            const scheduleImage = ref('');
            const accentColor = ref('#4E81FA');
            const contrastColor = ref('#ffffff');
            const confirmButtonRef = ref(null);
            let triggerEl = null;

            const shouldSkip = () => {
                // Only ever skips for a signed-in user. For a guest the modal is no longer a
                // consent gate in front of a signup redirect - it is the subscribe form itself,
                // so skipping it would skip the whole feature.
                if (isGuest) return false;
                if (initiallyDismissed) return true;
                try {
                    return localStorage.getItem('follow_consent_dismissed') === '1';
                } catch (e) {
                    return false;
                }
            };

            const handleTriggerClick = (e) => {
                const btn = e.target.closest('[data-follow-trigger]');
                if (!btn) return;
                e.preventDefault();
                e.stopPropagation();

                triggerEl = btn;
                followUrl.value = btn.dataset.followUrl || '';
                subscribeUrl.value = btn.dataset.subscribeUrl || '';
                accountNote.value = btn.dataset.accountNote === '1';
                subscribeLabel.value = btn.dataset.subscribeLabel
                    || @json(__('messages.email_me_new_events'), JSON_UNESCAPED_UNICODE);
                email.value = '';
                subscriberName.value = '';
                website.value = '';
                resultMessage.value = '';
                resultSuccess.value = false;
                done.value = false;
                scheduleName.value = btn.dataset.scheduleName || '';
                scheduleImage.value = btn.dataset.scheduleImage || '';
                accentColor.value = btn.dataset.accentColor || '#4E81FA';
                contrastColor.value = btn.dataset.contrastColor || '#ffffff';

                if (shouldSkip()) {
                    if (followUrl.value) window.esPostFollow(followUrl.value);
                    return;
                }

                dontAskAgain.value = false;
                open.value = true;
                document.body.style.overflow = 'hidden';
                nextTick(() => {
                    if (confirmButtonRef.value) confirmButtonRef.value.focus();
                });
            };

            const close = () => {
                open.value = false;
                document.body.style.overflow = '';
                if (triggerEl) {
                    try { triggerEl.focus(); } catch (e) {}
                }
            };

            const confirm = () => {
                if (submitting.value) return;
                submitting.value = true;
                let url = followUrl.value;

                if (dontAskAgain.value) {
                    try {
                        localStorage.setItem('follow_consent_dismissed', '1');
                    } catch (e) {}

                    try {
                        const u = new URL(url, window.location.origin);
                        u.searchParams.set('follow_consent_dismissed', '1');
                        url = u.toString();
                    } catch (e) {
                        url += (url.indexOf('?') === -1 ? '?' : '&') + 'follow_consent_dismissed=1';
                    }
                }

                window.esPostFollow(url);
            };

            const subscribeBody = computed(() =>
                @json(__('messages.subscribe_panel_body', ['schedule' => '__SCHEDULE__']), JSON_UNESCAPED_UNICODE)
                    // Function replacement, not a string: String.replace interprets $&, $`, $'
                    // and $1 inside a string replacement, so a schedule called "Rock $' Roll"
                    // would render mangled prose.
                    .replace('__SCHEDULE__', () => scheduleName.value)
            );

            const submitSubscribe = async () => {
                if (submitting.value) return;
                // A page cached from before this shipped has no data-subscribe-url. Fall back to
                // the account route rather than leaving a button that does nothing.
                if (!subscribeUrl.value) return confirm();

                // The `required` on the name input below is inert - these fields are not inside a
                // <form>, and the submit control is a type="button" that calls this directly - so
                // without this check an empty name always costs a round trip. That matters here
                // because role.audience.join is throttled at 5/minute per IP: fumble the form six
                // times and the 429 comes back through the !response.ok branch below as the
                // generic invalid_request, which says nothing about the name. The address is
                // deliberately left to the server, exactly as it was before.
                if (!subscriberName.value.trim()) {
                    resultSuccess.value = false;
                    resultMessage.value = @json(__('messages.subscribe_name_required'), JSON_UNESCAPED_UNICODE);
                    return;
                }

                submitting.value = true;
                resultMessage.value = '';

                try {
                    const response = await fetch(subscribeUrl.value, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({
                            email: email.value,
                            name: subscriberName.value,
                            // Sent explicitly: a hidden input alone is not part of a hand-built
                            // JSON payload.
                            website: website.value,
                            source: 'modal',
                        }),
                    });

                    if (!response.ok) {
                        resultSuccess.value = false;
                        resultMessage.value = @json(__('messages.invalid_request'), JSON_UNESCAPED_UNICODE);
                        return;
                    }

                    const data = await response.json();
                    resultSuccess.value = !!data.success;
                    resultMessage.value = data.message || '';
                    if (data.success) done.value = true;
                } catch (e) {
                    resultSuccess.value = false;
                    resultMessage.value = @json(__('messages.invalid_request'), JSON_UNESCAPED_UNICODE);
                } finally {
                    submitting.value = false;
                }
            };

            const handleKeydown = (e) => {
                if (!open.value) return;
                if (e.key === 'Escape') {
                    close();
                    return;
                }
                if (e.key === 'Tab') {
                    const dialog = document.querySelector('#follow-consent-modal-app [role="dialog"]');
                    if (!dialog) return;
                    const focusable = dialog.querySelectorAll(
                        'button:not([disabled]), [href], input:not([disabled]), [tabindex]:not([tabindex="-1"])'
                    );
                    if (!focusable.length) return;
                    const first = focusable[0];
                    const last = focusable[focusable.length - 1];
                    if (e.shiftKey && document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    } else if (!e.shiftKey && document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            };

            onMounted(() => {
                document.addEventListener('click', handleTriggerClick, true);
                document.addEventListener('keydown', handleKeydown);
            });

            onBeforeUnmount(() => {
                document.removeEventListener('click', handleTriggerClick, true);
                document.removeEventListener('keydown', handleKeydown);
            });

            return {
                open, dontAskAgain, submitting,
                scheduleName, scheduleImage, accentColor, contrastColor,
                isGuest, confirmButtonRef,
                subscribeUrl, subscribeLabel, email, subscriberName, website, subscribeBody,
                accountNote,
                resultMessage, resultSuccess, done,
                close, confirm, submitSubscribe,
            };
        },
        template: `
<transition name="follow-consent-fade">
<div v-if="open"
    class="gk-fcm-backdrop"
    @click.self="close"
    role="dialog"
    aria-modal="true"
    aria-labelledby="follow-consent-title"
    aria-describedby="follow-consent-desc">
    <div class="gk-fcm" @click.stop>

        <div class="gk-fcm-head">
            <img v-if="scheduleImage" :src="scheduleImage" :alt="scheduleName" class="gk-fcm-avatar">
            <h3 id="follow-consent-title" class="gk-fcm-title">
                {{ __('messages.follow_consent_title') }} <span v-text="scheduleName" dir="auto"></span>
            </h3>
            <button type="button" @click="close" class="gk-fcm-close" aria-label="{{ __('messages.close') }}">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div id="follow-consent-desc" class="gk-fcm-body">
            {{-- Signed in: unchanged. Following still means a real account and a role_user row. --}}
            <template v-if="!isGuest">
                <p class="gk-fcm-lead">
                    <span v-text="scheduleName" dir="auto"></span> {{ __('messages.follow_consent_body') }}
                </p>
                <p class="gk-fcm-fine">
                    {{ __('messages.follow_consent_body_privacy') }}
                </p>
                <label class="gk-fcm-check">
                    <input type="checkbox" v-model="dontAskAgain">
                    <span>{{ __('messages.follow_consent_dont_ask_again') }}</span>
                </label>
            </template>

            {{-- Signed out: the modal IS the subscribe form. It used to be a consent gate in front
                 of a redirect to sign_up, which is why 139k guest page views produced 764
                 followers. --}}
            <template v-else>
                <div v-if="done" class="gk-fcm-lead">
                    <p v-text="resultMessage"></p>
                </div>
                <template v-else>
                    {{-- v-text, not a Blade-rendered string: the schedule name lives in Vue
                         state, and v-text sets textContent so a name containing markup or a
                         mustache is inert. --}}
                    <p class="gk-fcm-lead" v-text="subscribeBody"></p>
                    <div>
                        <label for="follow-consent-email" class="gk-fcm-label">{{ __('messages.subscribe_your_email') }}</label>
                        <input id="follow-consent-email" type="email" dir="ltr" v-model="email" required autocomplete="email"
                            @keyup.enter="submitSubscribe" class="gk-fcm-input">
                    </div>
                    <div>
                        <label for="follow-consent-name" class="gk-fcm-label">{{ __('messages.subscribe_your_name') }}</label>
                        <input id="follow-consent-name" type="text" v-model="subscriberName" required autocomplete="name"
                            @keyup.enter="submitSubscribe" class="gk-fcm-input">
                    </div>
                    <div class="hidden" aria-hidden="true">
                        <input type="text" v-model="website" tabindex="-1" autocomplete="off">
                    </div>
                    {{-- The signed-in branch above discloses that the schedule sees the name and
                         email (follow_consent_body_privacy). The guest branch is the one that
                         COLLECTS an address from somebody with no account, and it disclosed
                         nothing - while the owner sees every subscriber's name and email on the
                         followers tab. Static translated text, so no CSTI surface. --}}
                    <p class="gk-fcm-fine">
                        {{ __('messages.subscribe_privacy_note') }}
                        <a href="{{ policy_url('privacy') }}" target="_blank" rel="noopener" class="gk-fcm-link">{{ __('messages.privacy_policy') }}</a>
                    </p>
                    {{-- Same sentence the subscribe panel carries, on the same gate. Static
                         translated text, so no CSTI surface. --}}
                    <p v-if="accountNote" class="gk-fcm-fine">
                        {{ __('messages.subscribe_account_note') }}
                    </p>
                    <p v-if="resultMessage && !resultSuccess" class="gk-fcm-err" role="alert" v-text="resultMessage"></p>
                </template>
            </template>
        </div>

        <div class="gk-fcm-foot">
            <button v-if="!done" type="button" @click="close" :disabled="submitting" class="gk-fcm-btn gk-fcm-btn-alt">
                {{ __('messages.cancel') }}
            </button>
            {{-- The guest footer used to carry a muted "Sign up and follow" button here, next to
                 the primary. It went with the subscribe panel's matching link: subscribing IS the
                 account now, so offering the account separately was a choice between a thing and
                 the same thing. confirm() is still the signed-in path, and the no-Vue fallback at
                 the top of this file still navigates to followUrl. The trigger's own colours: on an
                 event page each performer's Follow is that schedule's. --}}
            <button v-if="!done" type="button" ref="confirmButtonRef"
                @click="isGuest ? submitSubscribe() : confirm()" :disabled="submitting"
                :style="{ backgroundColor: accentColor, color: contrastColor, borderColor: accentColor }"
                class="gk-fcm-btn gk-fcm-btn-main">
                <svg v-if="submitting" class="animate-spin" width="16" height="16" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span v-if="isGuest" v-text="subscribeLabel"></span>
                <span v-else>{{ __('messages.follow') }}</span>
            </button>
            <button v-if="done" type="button" @click="close"
                :style="{ backgroundColor: accentColor, color: contrastColor, borderColor: accentColor }"
                class="gk-fcm-btn gk-fcm-btn-main">
                {{ __('messages.done') }}
            </button>
        </div>
    </div>
</div>
</transition>
`
        });

    app.mount('#follow-consent-modal-app[data-vue-root]');
});
</script>

<style {!! nonce_attr() !!}>
.follow-consent-fade-enter-active,
.follow-consent-fade-leave-active {
    transition: opacity 0.15s ease;
}
.follow-consent-fade-enter-from,
.follow-consent-fade-leave-to {
    opacity: 0;
}
{{-- The dialog in the stage's own colours (GuestTheme's --gk-stage-* on body): it opens over the
     sheet, so it is a card raised on it, in the same inks. The kit's tokens are re-pointed here as
     .gk-stage does, so the kit's pieces inside read on it. --}}
.gk-fcm-backdrop { position: fixed; inset: 0; z-index: 100; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgb(0 0 0 / .6); }
.gk-fcm { --gk-ink: var(--gk-stage-ink, #111827); --gk-ink-2: var(--gk-stage-ink-2, #374151); --gk-ink-3: var(--gk-stage-ink-3, #5f6875); --gk-line: color-mix(in srgb, var(--gk-ink) 14%, transparent); box-sizing: border-box; width: 100%; max-width: 28rem; max-height: calc(100vh - 2rem); overflow: auto; border-radius: 1.5rem; background: var(--gk-stage-raised, #ffffff); color: var(--gk-ink); box-shadow: 0 2rem 4rem -1rem rgb(0 0 0 / .5); }
.gk-fcm-head { display: flex; align-items: center; gap: .75rem; padding: 1.5rem 1.5rem .75rem; }
.gk-fcm-avatar { flex: none; width: 2.75rem; height: 2.75rem; border-radius: .75rem; object-fit: cover; }
.gk-fcm-title { flex: 1 1 auto; min-width: 0; margin: 0; font-size: 1.25rem; font-weight: 750; line-height: 1.25; color: var(--gk-ink); overflow-wrap: anywhere; }
.gk-fcm-close { display: grid; flex: none; place-items: center; width: 2.25rem; height: 2.25rem; border: 0; border-radius: .625rem; background: none; color: var(--gk-ink-3); cursor: pointer; }
.gk-fcm-close:hover { background: var(--gk-stage-well); color: var(--gk-ink); }
.gk-fcm-body { display: flex; flex-direction: column; gap: .875rem; padding: 0 1.5rem 1.25rem; }
.gk-fcm-lead { margin: 0; font-size: 1rem; line-height: 1.55; color: var(--gk-ink-2); }
.gk-fcm-lead p { margin: 0; }
.gk-fcm-fine { margin: 0; font-size: .8125rem; line-height: 1.55; color: var(--gk-ink-3); }
.gk-fcm-link { color: var(--es-accent-readable); font-weight: 600; text-decoration: underline; text-underline-offset: 3px; }
.gk-fcm-label { display: block; margin-bottom: .375rem; font-size: .875rem; font-weight: 500; color: var(--gk-ink-2); }
.gk-fcm .gk-fcm-input { display: block; box-sizing: border-box; width: 100%; height: 3.125rem; padding: 0 .875rem; border: 1px solid var(--gk-line); border-radius: .75rem; background: var(--gk-stage, #ffffff); color: var(--gk-ink); font: inherit; font-size: 1rem; box-shadow: none; }
.gk-fcm .gk-fcm-input:focus { border-color: var(--es-accent-readable); outline: none; box-shadow: 0 0 0 1px var(--es-accent-readable); }
.gk-fcm-check { display: flex; align-items: center; gap: .625rem; font-size: .9375rem; color: var(--gk-ink-2); cursor: pointer; }
.gk-fcm-check input { width: 1.125rem; height: 1.125rem; accent-color: var(--es-accent); }
.gk-fcm-err { margin: 0; font-size: .875rem; font-weight: 600; color: var(--gk-bad); }
.gk-fcm-foot { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: .75rem; padding: 1rem 1.5rem 1.5rem; border-top: 1px solid var(--gk-line); }
.gk-fcm-btn { display: inline-flex; flex: 1 1 8rem; align-items: center; justify-content: center; gap: .5rem; min-height: 3.125rem; padding: 0 1.25rem; border: 1px solid transparent; border-radius: .75rem; font: inherit; font-size: 1rem; font-weight: 650; cursor: pointer; transition: background-color var(--gk-swap, 120ms), filter var(--gk-swap, 120ms); }
.gk-fcm-btn:disabled { opacity: .55; cursor: not-allowed; }
.gk-fcm-btn:focus-visible, .gk-fcm-close:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
.gk-fcm-btn-alt { border-color: color-mix(in srgb, var(--gk-ink) 22%, transparent); background: transparent; color: var(--gk-ink); }
.gk-fcm-btn-alt:hover { background: var(--gk-stage-well); }
.gk-fcm-btn-main:hover { filter: brightness(1.08); }
</style>
