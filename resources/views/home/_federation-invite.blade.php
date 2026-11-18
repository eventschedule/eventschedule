{{-- The invitation to put this install's events on eventschedule.com (the Event Schedule network).

     A modal on the dashboard, at most three times thirty days apart, never over owed work and never
     in the middle of the setup guide: FederationService::shouldInvite(), counted by
     HomeController::home() before this is drawn. So Not now, Escape and the backdrop only close it:
     the visit has already been counted. "Don't ask again" posts federation_prompt_dismissed, and
     "Choose what to share" opens the network card on /admin/settings, through the admin password
     confirm, which says why it is asking (from=invite).

     Its own small Vue island, like the Customize dialog (home/_customize): x-modal is Alpine. Every
     word comes from MSG and the poster from POSTER, printed by Vue's interpolation, because a name
     printed by the server inside a Vue mount would be compiled as a template.

     The picture is the admin's own next event as /browse would draw it, from this install's own data
     (FederationService::invitePoster()); the page never calls the network. No picture, no poster. --}}
@php
    $inviteSite = (string) (parse_url((string) config('app.nexus_url'), PHP_URL_HOST) ?: 'eventschedule.com');
    $inviteConfig = [
        'poster' => $invite['poster'] ?? null,
        'see' => rtrim((string) config('app.nexus_url'), '/').'/browse?show=network',
    ];
    $inviteMsg = [
        'title' => __('messages.federation_invite_title', ['site' => $inviteSite]),
        'lead' => __('messages.federation_invite_lead'),
        'see' => __('messages.federation_invite_see'),
        'newTab' => __('messages.opens_in_new_tab'),
        'facts' => [
            __('messages.federation_invite_fact_backlink'),
            __('messages.federation_invite_fact_pick'),
            __('messages.federation_invite_fact_off'),
        ],
        'preview' => __('messages.federation_invite_preview', ['site' => $inviteSite]),
        'dontAsk' => __('messages.federation_invite_dont_ask'),
        'notNow' => __('messages.not_now'),
        'choose' => __('messages.federation_invite_choose'),
    ];
@endphp

<div id="federation-invite" data-vue-root v-cloak>
    <div v-if="open" class="fi-backdrop fixed inset-0 z-50 flex items-end sm:items-center justify-center p-3 sm:p-6" @click.self="later" @keydown="keydown">
        <div ref="dialog" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="federation-invite-title" aria-describedby="federation-invite-lead"
             class="ap-card rounded-2xl w-full max-h-full overflow-y-auto focus:outline-none" :class="poster ? 'max-w-2xl' : 'max-w-lg'">
            <div class="p-5 sm:p-7" :class="poster ? 'fi-body has-poster' : 'fi-body'">
                {{-- The picture: their own event, as a poster on the network's wall would show it,
                     with the badge naming their site. The words are said again by the caption, so a
                     screen reader is given the line above it and the caption, not the picture. --}}
                <figure v-if="poster" class="fi-poster">
                    <figcaption class="fi-poster-say">@{{ msg.preview }}</figcaption>
                    <div class="fi-frame" aria-hidden="true">
                        <img class="fi-glow" :src="poster.image" alt="" decoding="async">
                        <div class="fi-clip">
                            <img class="fi-img" :src="poster.image" alt="" decoding="async">
                        </div>
                        <span class="fi-globe">
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3z" /></svg>
                        </span>
                    </div>
                    <div class="fi-cap">
                        <p class="fi-when"><b><bdi>@{{ poster.when }}</bdi></b> <span v-if="poster.time"><bdi dir="ltr">@{{ poster.time }}</bdi></span></p>
                        <p class="fi-name"><bdi>@{{ poster.name }}</bdi></p>
                        <p v-if="poster.place" class="fi-place"><bdi>@{{ poster.place }}</bdi></p>
                        <p v-if="poster.schedule" class="fi-who"><bdi>@{{ poster.schedule }}</bdi></p>
                        <p v-if="poster.source" class="fi-src">
                            <svg class="fi-src-globe" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3z" /></svg>
                            <bdi dir="ltr">@{{ poster.source }}</bdi>
                            <svg class="fi-src-out" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M9 7h8v8" /></svg>
                        </p>
                    </div>
                </figure>

                <div class="min-w-0 flex-1">
                    <h2 id="federation-invite-title" class="text-xl sm:text-2xl font-semibold text-gray-900 dark:text-white">@{{ msg.title }}</h2>
                    <p id="federation-invite-lead" class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        @{{ msg.lead }}
                        <a href="{{ $inviteConfig['see'] }}" target="_blank" rel="noopener" class="font-medium text-[var(--brand-blue)] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] rounded-sm whitespace-nowrap">@{{ msg.see }}<span class="sr-only"> (@{{ msg.newTab }})</span><svg class="fi-out" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M9 7h8v8" /></svg></a>
                    </p>
                    <ul class="mt-4 space-y-3">
                        <li v-for="(fact, index) in msg.facts" :key="index" class="flex items-start gap-3 text-sm text-gray-800 dark:text-gray-200">
                            <svg class="w-5 h-5 shrink-0 text-[var(--brand-blue)]" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>@{{ fact }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Don't ask again at the start and small; the forward action at the end. On a phone they
                 stack, the forward action last and the width of the dialog. --}}
            <div class="fi-actions px-5 sm:px-7 py-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                <form method="POST" action="{{ route('home.federation_prompt_dismiss') }}" class="fi-dont">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">@{{ msg.dontAsk }}</button>
                </form>
                <button type="button" @click="later" class="ap-secondary-btn inline-flex items-center justify-center w-full sm:w-auto px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">@{{ msg.notNow }}</button>
                <x-brand-link :href="route('admin.settings', ['card' => 'federation', 'from' => 'invite'])" class="w-full sm:w-auto">@{{ msg.choose }}</x-brand-link>
            </div>
        </div>
    </div>
</div>

<style {!! nonce_attr() !!}>
    [v-cloak] { display: none; }
    .fi-backdrop { background: rgba(0, 0, 0, 0.5); }
    .fi-body.has-poster { display: flex; flex-direction: column; gap: 1.25rem; }
    .fi-actions { border-top: 1px solid var(--ap-hairline); }
    .fi-out { display: inline-block; width: 0.8em; height: 0.8em; margin-inline-start: 0.2em; vertical-align: -0.05em; }
    [dir="rtl"] .fi-out, [dir="rtl"] .fi-src-out { transform: scaleX(-1); }

    /* The poster: a tile from /browse's wall (marketing/partials/browse-wall), drawn on the
       portal's own tokens. The picture keeps its shape inside a 4:5 frame, over a blurred copy of
       itself, which is the light it throws on the wall. */
    .fi-poster { margin: 0; width: 11.5rem; align-self: center; flex-shrink: 0; }
    .fi-poster-say { margin: 0 0 0.625rem; font-size: 0.75rem; line-height: 1.35; color: rgb(var(--ap-ink-3)); text-align: center; }
    .fi-frame { position: relative; aspect-ratio: 4 / 5; }
    .fi-glow { position: absolute; inset: 12% 6% -4%; width: 88%; height: 92%; object-fit: cover; filter: blur(22px) saturate(1.4); opacity: 0.45; border-radius: 1rem; }
    .fi-clip { position: relative; height: 100%; overflow: hidden; border-radius: 0.875rem; background: rgb(var(--ap-ink) / 0.08); box-shadow: 0 1px 2px rgba(0, 0, 0, 0.12), 0 8px 24px -8px rgba(0, 0, 0, 0.35); }
    .fi-img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .fi-globe { position: absolute; top: 0.5rem; inset-inline-end: 0.5rem; display: inline-flex; width: 1.75rem; height: 1.75rem; align-items: center; justify-content: center; border-radius: 9999px; background: rgba(15, 23, 42, 0.62); color: #fff; backdrop-filter: blur(6px); }
    .fi-globe svg { width: 1rem; height: 1rem; }
    .fi-cap { margin-top: 0.75rem; font-size: 0.8125rem; line-height: 1.35; color: rgb(var(--ap-ink-2)); }
    .fi-cap p { margin: 0; }
    .fi-when b { font-weight: 600; color: rgb(var(--ap-ink)); }
    .fi-when span { color: rgb(var(--ap-ink-3)); }
    .fi-name { margin-top: 0.125rem !important; font-size: 0.9375rem; font-weight: 600; color: rgb(var(--ap-ink)); overflow-wrap: anywhere; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .fi-place, .fi-who { color: rgb(var(--ap-ink-3)); overflow-wrap: anywhere; }
    .fi-src { margin-top: 0.375rem !important; display: flex; align-items: center; gap: 0.3rem; font-size: 0.75rem; color: rgb(var(--ap-ink-3)); overflow-wrap: anywhere; }
    .fi-src-globe, .fi-src-out { width: 0.875rem; height: 0.875rem; flex-shrink: 0; }

    /* A phone: the poster beside its caption, so the dialog's words and buttons stay in view. */
    @media (max-width: 639px) {
        .fi-poster { width: 100%; display: grid; grid-template-columns: 6.5rem minmax(0, 1fr); column-gap: 0.875rem; align-items: start; }
        .fi-poster-say { grid-column: 1 / -1; text-align: start; }
        .fi-cap { margin-top: 0; }
        .fi-glow { display: none; }
    }
    @media (min-width: 640px) {
        .fi-dont { margin-inline-end: auto; }
        .fi-body.has-poster { flex-direction: row; gap: 1.75rem; align-items: flex-start; }
        .fi-poster { align-self: flex-start; }
    }
</style>
<script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}"{!! nonce_attr() !!}><\/script>')</script>
<script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function () {
        var CONFIG = @json($inviteConfig);
        var MSG = @json($inviteMsg);
        var opener = null;

        var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select, textarea, [tabindex]:not([tabindex="-1"])';

        Vue.createApp({
            data: function () {
                return { open: true, msg: MSG, config: CONFIG, poster: CONFIG.poster };
            },
            mounted: function () {
                var self = this;
                opener = document.activeElement;
                document.body.style.overflow = 'hidden';
                this.$nextTick(function () { if (self.$refs.dialog) { self.$refs.dialog.focus(); } });
            },
            methods: {
                // Not now, Escape and the backdrop: the dashboard counted this ask when it drew it.
                later: function () {
                    this.open = false;
                    document.body.style.overflow = '';
                    var back = opener && opener !== document.body && document.contains(opener) ? opener : document.getElementById('main-content');
                    if (back && back.focus) { back.focus(); }
                },
                keydown: function (event) {
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        this.later();
                        return;
                    }
                    if (event.key !== 'Tab' || !this.$refs.dialog) {
                        return;
                    }
                    // Focus stays inside the dialog while it is open.
                    var items = Array.prototype.filter.call(this.$refs.dialog.querySelectorAll(FOCUSABLE), function (el) {
                        return el.offsetParent !== null;
                    });
                    if (!items.length) {
                        event.preventDefault();
                        return;
                    }
                    var first = items[0];
                    var last = items[items.length - 1];
                    if (event.shiftKey && (document.activeElement === first || document.activeElement === this.$refs.dialog)) {
                        event.preventDefault();
                        last.focus();
                    } else if (!event.shiftKey && document.activeElement === last) {
                        event.preventDefault();
                        first.focus();
                    }
                },
            },
        }).mount('#federation-invite[data-vue-root]');
    });
</script>
