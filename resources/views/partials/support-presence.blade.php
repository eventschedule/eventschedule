{{-- The admin's side of the support chat's presence, on every AP page (layouts/app-admin, for a
     hosted admin). App\Utils\SupportPresence is the server half.

     - Heartbeat: while chat is switched on, pings every 15s (60s in a background tab, which is
       about as often as a browser lets a hidden tab's timer fire anyway). No ping for
       SupportPresence::AWAY_AFTER_MINUTES and visitors stop seeing the chat.
     - Hourly check: asks "Still available for chat?" once confirm_in runs out, and counts down
       the grace window. Unanswered, the server lapses on its own; this only shows it. Only the
       admin who switched chat on (the agent) is asked; another admin's pings do not count.
     - Alerts: a new unread support message raises a toast, a soft chime and a flashing tab
       title, so a visitor on the marketing site is answered while they are still there.
     - Sync: talks to the sidebar popover through support-presence-changed / -set window events,
       and to the admin's other tabs through a localStorage write.

     English only, like /admin/support. Nothing server-rendered and user-controlled is echoed in
     this Vue mount: the state arrives through the JSON blob and is interpolated by Vue. --}}
@php
    // Built here, not inline: @json() splits its argument on every comma.
    $supportPresenceEndpoints = [
        'ping' => route('support-chat.presence.ping', [], false),
        'confirm' => route('support-chat.presence.confirm', [], false),
        'online' => route('support-chat.presence.online', [], false),
        'offline' => route('support-chat.presence.offline', [], false),
    ];
@endphp
<style {!! nonce_attr() !!}>#support-presence-app[v-cloak] { display: none !important; }</style>
<script type="application/json" id="support-presence-state">@json(\App\Utils\SupportPresence::state(auth()->user()))</script>

<div id="support-presence-app" v-cloak>
    {{-- New message toast --}}
    <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-2" leave-active-class="transition duration-150 ease-in" leave-to-class="opacity-0 translate-y-2">
        {{-- Positioned by a wrapper: .ap-card sets position: relative, which beats `fixed`. --}}
        <div v-if="toast" class="fixed bottom-6 end-6 z-[55] w-[calc(100%-2rem)] max-w-sm">
        <div role="status" aria-live="polite" class="ap-card rounded-xl p-4 shadow-lg">
            <div class="flex items-start gap-3">
                <span class="dashboard-icon p-2 rounded-xl bg-green-50 dark:bg-green-500/10 shrink-0" style="--icon-glow: rgba(34,197,94,0.35);">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    {{-- sender and preview are only sent while the admin re-auth is fresh --}}
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">@{{ toast.sender || (toast.is_guest ? 'New visitor message' : 'New support message') }}</div>
                    <div v-if="toast.page" class="text-xs text-gray-500 dark:text-gray-400 truncate">Viewing @{{ toast.page }}</div>
                    <p v-if="toast.preview" class="mt-1 text-sm text-gray-700 dark:text-gray-300 break-words">@{{ toast.preview }}</p>
                    <div class="mt-3 flex items-center gap-2">
                        <button type="button" @click="toast = null"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] transition-all duration-200">
                            Later
                        </button>
                        <a :href="'/admin/support?c=' + encodeURIComponent(toast.conversation_id)"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] transition-all duration-200">
                            Reply
                        </a>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </transition>

    {{-- Hourly "still here?" --}}
    <transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-2" leave-active-class="transition duration-150 ease-in" leave-to-class="opacity-0 translate-y-2">
        <div v-if="prompt || sessionExpired" class="fixed inset-x-4 bottom-4 z-[56] sm:inset-x-0 sm:bottom-6 sm:flex sm:justify-center sm:pointer-events-none">
        <div role="alertdialog" aria-labelledby="support-presence-title" aria-describedby="support-presence-desc"
            class="ap-card rounded-xl p-5 shadow-lg sm:w-[26rem] sm:pointer-events-auto">
            <div class="flex items-start gap-3">
                <span class="dashboard-icon p-2 rounded-xl shrink-0"
                    :class="prompt === 'confirm' || sessionExpired ? 'bg-amber-50 dark:bg-amber-500/10' : 'bg-gray-100 dark:bg-gray-700'"
                    :style="prompt === 'confirm' || sessionExpired ? '--icon-glow: rgba(245,158,11,0.35)' : ''">
                    <svg class="w-5 h-5" :class="prompt === 'confirm' || sessionExpired ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500 dark:text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <template v-if="sessionExpired">
                        <h2 id="support-presence-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Your session expired</h2>
                        <p id="support-presence-desc" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Reload the page to change your chat availability.
                        </p>
                    </template>
                    <template v-else-if="prompt === 'confirm'">
                        <h2 id="support-presence-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Still available for chat?</h2>
                        <p id="support-presence-desc" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            You're shown as online. We'll switch you offline in <span class="font-semibold tabular-nums text-gray-900 dark:text-gray-100">@{{ countdown }}</span> unless you confirm.
                        </p>
                    </template>
                    <template v-else>
                        <h2 id="support-presence-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">You're offline now</h2>
                        <p v-if="offlineReason === 'lapsed'" id="support-presence-desc" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Nobody confirmed you were still here, so you're now shown as away.
                        </p>
                        <p v-else id="support-presence-desc" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Chat was switched off, which happens after an app update. Switch it back on to be shown as available.
                        </p>
                    </template>
                </div>
            </div>
            {{-- Secondary actions carry the secondary-link component's classes: they are buttons,
                 and that component renders a link. --}}
            <div class="mt-4 flex flex-wrap justify-end gap-2">
                <template v-if="sessionExpired">
                    <button type="button" @click="sessionExpired = false" class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        Dismiss
                    </button>
                    <x-brand-button @click="reload">
                        Reload
                    </x-brand-button>
                </template>
                <template v-else-if="prompt === 'confirm'">
                    <button type="button" @click="setAvailable(false)" v-bind:disabled="busy" class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50">
                        Go offline
                    </button>
                    <x-brand-button @click="confirmPresence" v-bind:disabled="busy" ref="confirmButton">
                        I'm still here
                    </x-brand-button>
                </template>
                <template v-else>
                    <button type="button" @click="prompt = null" class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        Dismiss
                    </button>
                    <x-brand-button @click="setAvailable(true)" v-bind:disabled="busy">
                        Go back online
                    </x-brand-button>
                </template>
            </div>
        </div>
        </div>
    </transition>
</div>

<script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}"{!! nonce_attr() !!}><\/script>')</script>
<script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Vue === 'undefined') return;

        var SYNC_KEY = 'es_support_presence_sync';
        var ALERTED_KEY = 'es_support_alerted_id';
        var ENDPOINTS = @json($supportPresenceEndpoints);

        function readStorage(key) {
            try { return localStorage.getItem(key); } catch (e) { return null; }
        }
        function writeStorage(key, value) {
            try { localStorage.setItem(key, value); } catch (e) {}
        }

        var initial = {};
        try {
            initial = JSON.parse(document.getElementById('support-presence-state').textContent || '{}');
        } catch (e) {}

        Vue.createApp({
            data() {
                return {
                    presence: initial,
                    unreadCount: null,
                    // Deadlines as local timestamps, derived from the server's RELATIVE seconds at
                    // the moment they arrived, so a skewed clock on this machine does not matter.
                    confirmAt: null,
                    expireAt: null,
                    prompt: null,
                    countdown: '',
                    toast: null,
                    busy: false,
                    sessionExpired: false,
                    // Why the "You're offline now" card is up: the hour lapsed unanswered, or the
                    // state vanished under us (a deploy starts every container with an empty cache).
                    offlineReason: null,
                    promptFocused: false,
                    pingStopped: false,
                    pingTimer: null,
                    confirmTimer: null,
                    tickTimer: null,
                    flashTimer: null,
                    toastTimer: null,
                    originalTitle: document.title,
                    audio: null,
                    onInbox: !!document.getElementById('support-admin-app'),
                };
            },
            mounted() {
                this.applyPresence(this.presence, false);
                // Once even when offline: it fills the sidebar's unread badge. Only a ping while
                // online counts as a heartbeat (SupportChatController::presencePing).
                this.ping();

                window.addEventListener('support-presence-set', (e) => {
                    this.setAvailable(!!(e.detail && e.detail.available));
                });
                // Another surface on this page (the inbox's own switch) changed the state.
                // A deliberate change made on this page, just not by this app.
                window.addEventListener('support-presence-changed', (e) => {
                    if (e.detail && e.detail.source !== 'presence' && e.detail.presence) {
                        this.applyPresence(e.detail.presence, true);
                    }
                });
                // Another tab changed the state: catch up, without calling it unexpected.
                window.addEventListener('storage', (e) => {
                    if (e.key === SYNC_KEY) this.ping(true);
                });
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) {
                        this.stopFlash();
                        if (this.presence.online) this.ping();
                    }
                    this.schedulePing();
                });
                window.addEventListener('focus', () => this.stopFlash());

                // Browsers only let a page make sound after the user has interacted with it,
                // so the audio context is created on the first click or key press.
                var unlock = () => {
                    if (this.audio) return;
                    try {
                        var Ctx = window.AudioContext || window.webkitAudioContext;
                        if (Ctx) this.audio = new Ctx();
                    } catch (e) {}
                };
                document.addEventListener('pointerdown', unlock, { once: true, capture: true });
                document.addEventListener('keydown', unlock, { once: true, capture: true });
            },
            methods: {
                request(url) {
                    var token = document.querySelector('meta[name="csrf-token"]');
                    return fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token ? token.content : '',
                        },
                        credentials: 'same-origin',
                    }).then((r) => {
                        if (!r.ok) throw r;
                        return r.json();
                    });
                },
                isSessionError(r) {
                    return !!r && (r.status === 401 || r.status === 419);
                },
                // A background ping on a dead session stops quietly. It never reloads: the admin
                // may be halfway through an unsaved form on this page.
                ping(fromSync) {
                    if (this.pingStopped) return Promise.resolve();
                    return this.request(ENDPOINTS.ping)
                        .then((data) => this.applyPayload(data, false, !!fromSync))
                        .catch((r) => {
                            if (this.isSessionError(r)) {
                                this.pingStopped = true;
                                clearInterval(this.pingTimer);
                            }
                        });
                },
                // Something the admin clicked: a dead session says so, instead of doing nothing.
                act(url) {
                    this.busy = true;
                    this.request(url)
                        .then((data) => this.applyPayload(data, true))
                        .catch((r) => {
                            if (this.isSessionError(r)) this.sessionExpired = true;
                        })
                        .finally(() => { this.busy = false; });
                },
                confirmPresence() {
                    this.act(ENDPOINTS.confirm);
                },
                setAvailable(available) {
                    this.act(available ? ENDPOINTS.online : ENDPOINTS.offline);
                },
                reload() {
                    window.location.reload();
                },
                applyPayload(data, changedHere, fromSync) {
                    if (typeof data.unread_count === 'number') {
                        this.unreadCount = data.unread_count;
                    }
                    this.applyPresence(data.presence || {}, changedHere, fromSync);
                    this.maybeAlert(data.latest_unread);
                },
                applyPresence(presence, changedHere, fromSync) {
                    var wasOnline = !!this.presence.online;
                    var wasAgent = !!this.presence.is_agent;
                    var now = Date.now();
                    // Past the deadline this tab was counting to, allowing for timer drift.
                    var lapsed = !!this.expireAt && now >= this.expireAt - 5000;
                    this.presence = presence;
                    this.confirmAt = presence.online ? now + presence.confirm_in * 1000 : null;
                    this.expireAt = presence.online ? now + presence.expires_in * 1000 : null;

                    if (presence.online) {
                        // Only the agent is asked; another admin just sees who is online.
                        this.prompt = presence.is_agent && presence.confirm_in <= 0 ? 'confirm' : null;
                    } else if (wasOnline && wasAgent && !changedHere && !fromSync) {
                        // Went offline without anyone here asking to: say so, or the admin only
                        // finds out from a grey dot.
                        this.offlineReason = lapsed ? 'lapsed' : 'switched';
                        this.prompt = 'expired';
                    } else if (this.prompt === 'confirm') {
                        this.prompt = null;
                    }

                    if (changedHere) {
                        writeStorage(SYNC_KEY, String(now));
                        if (presence.online) this.prompt = null;
                    }

                    this.scheduleConfirm();
                    this.schedulePing();
                    this.syncTick();

                    window.dispatchEvent(new CustomEvent('support-presence-changed', {
                        detail: { presence: presence, unread_count: this.unreadCount, source: 'presence' },
                    }));
                },
                scheduleConfirm() {
                    clearTimeout(this.confirmTimer);
                    if (!this.presence.online || !this.presence.is_agent || this.prompt) return;
                    // Re-ask the server first when the hour is up: another tab may already have
                    // confirmed, in which case this one just reschedules.
                    this.confirmTimer = setTimeout(() => this.ping(), Math.max(0, this.confirmAt - Date.now()) + 500);
                },
                schedulePing() {
                    clearInterval(this.pingTimer);
                    if (!this.presence.online || this.pingStopped) return;
                    this.pingTimer = setInterval(() => this.ping(), document.hidden ? 60000 : 15000);
                },
                syncTick() {
                    clearInterval(this.tickTimer);
                    if (this.prompt !== 'confirm') {
                        this.promptFocused = false;
                    }
                    if (this.prompt === 'confirm') {
                        this.updateCountdown();
                        this.tickTimer = setInterval(() => this.updateCountdown(), 1000);
                        this.startFlash('Still online?');
                        // Once, when the prompt appears. This runs again on every ping while it
                        // is up, and re-focusing each time pulled the admin off whatever they had
                        // tabbed to.
                        if (this.promptFocused) return;
                        this.promptFocused = true;
                        this.$nextTick(() => {
                            // Never pull focus out of something the admin is typing into.
                            var active = document.activeElement;
                            var typing = active && active !== document.body
                                && (/^(INPUT|TEXTAREA|SELECT)$/.test(active.tagName) || active.isContentEditable);
                            if (this.$refs.confirmButton && !document.hidden && !typing) {
                                this.$refs.confirmButton.focus({ preventScroll: true });
                            }
                        });
                    } else if (this.prompt !== 'expired') {
                        this.stopFlash();
                    }
                },
                updateCountdown() {
                    var left = Math.max(0, Math.round((this.expireAt - Date.now()) / 1000));
                    this.countdown = Math.floor(left / 60) + ':' + String(left % 60).padStart(2, '0');
                    if (left === 0) {
                        clearInterval(this.tickTimer);
                        this.ping();
                    }
                },
                // Several AP tabs all ping, and the first to claim a message is the one that alerts.
                // A visible tab claims at once. A hidden tab waits 20 seconds first, so a visible
                // tab (pinging every 15) gets to show the toast where the admin is looking; the
                // hidden tab only speaks up when no visible tab did.
                maybeAlert(latest) {
                    if (!latest) return;
                    if (latest.id <= this.alertedId()) return;
                    if (!document.hidden) {
                        this.claimAlert(latest);
                        return;
                    }
                    setTimeout(() => {
                        if (latest.id > this.alertedId()) this.claimAlert(latest);
                    }, 20000);
                },
                alertedId() {
                    return parseInt(readStorage(ALERTED_KEY) || '0', 10);
                },
                claimAlert(latest) {
                    writeStorage(ALERTED_KEY, String(latest.id));
                    // Only while switched on: offline, the email and push are the only channels.
                    if (!this.presence.online) return;

                    this.chime();
                    this.startFlash(latest.is_guest ? 'New visitor chat' : 'New support message');
                    if (!this.onInbox) {
                        this.toast = latest;
                        clearTimeout(this.toastTimer);
                        this.toastTimer = setTimeout(() => { this.toast = null; }, 20000);
                    }
                },
                chime() {
                    var ctx = this.audio;
                    if (!ctx) return;
                    try {
                        if (ctx.state === 'suspended') ctx.resume();
                        var t = ctx.currentTime;
                        [[880, 0], [1318.5, 0.14]].forEach(function(note) {
                            var osc = ctx.createOscillator();
                            var gain = ctx.createGain();
                            osc.type = 'sine';
                            osc.frequency.value = note[0];
                            gain.gain.setValueAtTime(0.0001, t + note[1]);
                            gain.gain.exponentialRampToValueAtTime(0.08, t + note[1] + 0.02);
                            gain.gain.exponentialRampToValueAtTime(0.0001, t + note[1] + 0.35);
                            osc.connect(gain).connect(ctx.destination);
                            osc.start(t + note[1]);
                            osc.stop(t + note[1] + 0.4);
                        });
                    } catch (e) {}
                },
                startFlash(text) {
                    if (this.flashTimer) return;
                    if (!document.hidden && document.hasFocus()) return;
                    var on = false;
                    this.flashTimer = setInterval(() => {
                        on = !on;
                        document.title = on ? '(1) ' + text : this.originalTitle;
                    }, 1200);
                },
                stopFlash() {
                    if (!this.flashTimer) return;
                    clearInterval(this.flashTimer);
                    this.flashTimer = null;
                    document.title = this.originalTitle;
                },
            },
        }).mount('#support-presence-app');
    });
</script>
