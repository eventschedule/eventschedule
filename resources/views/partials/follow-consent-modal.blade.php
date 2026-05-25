@php
    $followUser = auth()->user();
    $followIsGuest = ! $followUser;
    $followInitiallyDismissed = $followUser ? (bool) $followUser->follow_consent_dismissed : false;
@endphp

<div id="follow-consent-modal-app"
    data-is-guest="{{ $followIsGuest ? '1' : '0' }}"
    data-user-dismissed="{{ $followInitiallyDismissed ? '1' : '0' }}">
    <follow-consent-modal></follow-consent-modal>
</div>

<script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}"{!! nonce_attr() !!}><\/script>')</script>
<script {!! nonce_attr() !!}>
// Fallback navigation if Vue fails to load - clicks on Follow triggers still navigate.
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-follow-trigger]');
    if (!btn || e.defaultPrevented) return;
    const url = btn.dataset.followUrl;
    if (url) window.location.href = url;
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
            const scheduleName = ref('');
            const scheduleImage = ref('');
            const accentColor = ref('#4E81FA');
            const contrastColor = ref('#ffffff');
            const confirmButtonRef = ref(null);
            let triggerEl = null;

            const shouldSkip = () => {
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
                scheduleName.value = btn.dataset.scheduleName || '';
                scheduleImage.value = btn.dataset.scheduleImage || '';
                accentColor.value = btn.dataset.accentColor || '#4E81FA';
                contrastColor.value = btn.dataset.contrastColor || '#ffffff';

                if (shouldSkip()) {
                    if (followUrl.value) window.location.href = followUrl.value;
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

                window.location.href = url;
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
                close, confirm,
            };
        },
        template: `
<transition name="follow-consent-fade">
<div v-if="open"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
    @click.self="close"
    role="dialog"
    aria-modal="true"
    aria-labelledby="follow-consent-title"
    aria-describedby="follow-consent-desc">
    <div class="bg-white dark:bg-[#1e1e1e] rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-gray-200 dark:border-[#2d2d30]"
        @click.stop>

        <div class="px-6 pt-6 pb-2 flex items-center gap-3">
            <img v-if="scheduleImage" :src="scheduleImage" :alt="scheduleName"
                class="w-10 h-10 rounded-full object-cover flex-shrink-0 border border-gray-200 dark:border-[#2d2d30]">
            <h3 id="follow-consent-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex-1">
                {{ __('messages.follow_consent_title') }} <span v-text="scheduleName"></span>
            </h3>
            <button type="button" @click="close"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors p-1 rounded focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]"
                aria-label="{{ __('messages.close') }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div id="follow-consent-desc" class="px-6 pb-4 space-y-3">
            <p class="text-sm text-gray-700 dark:text-gray-300">
                <span v-text="scheduleName"></span> {{ __('messages.follow_consent_body') }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ __('messages.follow_consent_body_privacy') }}
            </p>
            <p v-if="isGuest" class="text-xs text-gray-500 dark:text-gray-400">
                {{ __('messages.follow_consent_body_guest') }}
            </p>
        </div>

        <div class="px-6 pb-4">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                <input type="checkbox" v-model="dontAskAgain"
                    class="rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] dark:bg-[#2d2d30]">
                <span>{{ __('messages.follow_consent_dont_ask_again') }}</span>
            </label>
        </div>

        <div class="px-6 pb-6 pt-2 flex justify-end gap-3 border-t border-gray-100 dark:border-[#2d2d30] bg-gray-50 dark:bg-[#252526]">
            <button type="button" @click="close" :disabled="submitting"
                class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-[#1e1e1e] border border-gray-300 dark:border-[#2d2d30] rounded-lg hover:bg-gray-50 dark:hover:bg-[#2d2d30] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-[#1e1e1e] transition-colors disabled:opacity-50">
                {{ __('messages.cancel') }}
            </button>
            <button type="button" ref="confirmButtonRef" @click="confirm" :disabled="submitting"
                :style="{ backgroundColor: accentColor, color: contrastColor, borderColor: accentColor }"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-[#1e1e1e] transition-all disabled:opacity-50 disabled:cursor-not-allowed hover:opacity-90">
                <svg v-if="submitting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span v-if="isGuest">{{ __('messages.follow_consent_signup_button') }}</span>
                <span v-else>{{ __('messages.follow') }}</span>
            </button>
        </div>
    </div>
</div>
</transition>
`
        });

    app.mount('#follow-consent-modal-app');
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
</style>
