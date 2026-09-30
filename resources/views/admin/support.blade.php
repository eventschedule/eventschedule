<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'support'])

    <div id="support-admin-app" class="mt-6">
        {{-- Top bar with availability toggle --}}
        <div class="ap-card rounded-xl p-4 mb-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-2.5 h-2.5 rounded-full shrink-0"
                    :class="presence.available ? 'bg-green-500' : (presence.online ? 'bg-amber-500' : 'bg-gray-400 dark:bg-gray-500')"></span>
                <div class="min-w-0">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Support availability</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">@{{ availabilityText }}</div>
                </div>
            </div>
            <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                <input type="checkbox" :checked="presence.online" @change="toggleAvailability($event.target)" class="sr-only peer" aria-label="Available for chat">
                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] peer-focus-visible:ring-2 peer-focus-visible:ring-[var(--brand-blue)] transition-colors"></div>
                <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
            </label>
        </div>

        {{-- Split panel layout --}}
        <div class="flex gap-4" style="height: calc(100vh - 280px); min-height: 400px;">
            {{-- Left panel: conversation list --}}
            <div v-show="!mobileShowConversation || !isMobile" :class="isMobile ? 'w-full' : 'w-1/3'" class="ap-card rounded-xl flex flex-col overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Conversations</h3>
                </div>
                <div class="flex-1 overflow-y-auto">
                    <div v-if="conversations.length === 0" class="p-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                        No conversations yet
                    </div>
                    <div v-for="conv in conversations" :key="conv.id"
                        @click="selectConversation(conv)"
                        :class="[
                            'p-4 cursor-pointer border-b border-gray-100 dark:border-gray-700/50 transition-all duration-200',
                            selectedConversation && selectedConversation.id === conv.id
                                ? 'bg-gray-100 dark:bg-gray-700'
                                : 'hover:bg-gray-50 dark:hover:bg-gray-800'
                        ]">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span v-if="conv.online" class="w-2 h-2 rounded-full bg-green-500 shrink-0" :title="conv.is_guest ? 'On the site now' : 'In the app now'"></span>
                                <span v-if="conv.guest_country" class="shrink-0 text-sm leading-none" :title="countryName(conv.guest_country)">@{{ countryFlag(conv.guest_country) }}</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">@{{ conv.display_name }}</span>
                                <span v-if="conv.is_guest" class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300">Visitor</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span v-if="conv.unread_count > 0" class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-xs font-bold text-white bg-red-500 rounded-full">@{{ conv.unread_count }}</span>
                                <span v-if="conv.status === 'closed'" class="text-xs text-gray-400 dark:text-gray-500">closed</span>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">@{{ conv.last_message_preview }}</div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">@{{ formatTime(conv.last_message_at) }}</div>
                    </div>
                </div>
            </div>

            {{-- Right panel: message thread --}}
            <div v-show="!isMobile || mobileShowConversation" :class="isMobile ? 'w-full' : 'w-2/3'" class="ap-card rounded-xl flex flex-col overflow-hidden">
                <template v-if="selectedConversation">
                    {{-- Header --}}
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <button v-if="isMobile" @click="mobileShowConversation = false" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 shrink-0" aria-label="Back">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">@{{ conversationUser.name || conversationUser.email || 'Website visitor' }}</span>
                                    <span v-if="conversationUser.online" class="shrink-0 inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>@{{ conversationUser.is_guest ? 'On the site now' : 'In the app now' }}
                                    </span>
                                </div>
                                <template v-if="conversationUser.is_guest">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        <x-link v-if="conversationUser.email" v-bind:href="'mailto:' + conversationUser.email">@{{ conversationUser.email }}</x-link>
                                        <span v-else>No email left yet</span>
                                        <template v-if="conversationUser.country"> · @{{ countryFlag(conversationUser.country) }} @{{ countryName(conversationUser.country) }}</template>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        <span v-if="conversationUser.current_page">Now on <x-link v-bind:href="marketingUrl(conversationUser.current_page)" target="_blank">@{{ conversationUser.current_page }}</x-link></span>
                                        <span v-if="conversationUser.started_on">Started on <x-link v-bind:href="marketingUrl(conversationUser.started_on)" target="_blank">@{{ conversationUser.started_on }}</x-link></span>
                                        {{-- The visitor typed this address and nobody verified it, so it only MATCHES an account. --}}
                                        <a v-if="conversationUser.has_account" :href="'/admin/users?search=' + encodeURIComponent(conversationUser.email)" class="inline-flex items-center px-2 py-0.5 rounded-full font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-[#3d3d40] transition-colors">Email matches an account</a>
                                    </div>
                                </template>
                                <template v-else>
                                    <a :href="'/admin/users?search=' + encodeURIComponent(conversationUser.email)" class="text-xs text-[var(--brand-blue)] hover:underline truncate block">@{{ conversationUser.email }}</a>
                                    <div v-if="conversationUser.roles && conversationUser.roles.length" class="flex flex-wrap gap-1 mt-1">
                                        <a v-for="role in conversationUser.roles" :key="role.subdomain" :href="'/' + role.subdomain" target="_blank" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-[#3d3d40] transition-colors">@{{ role.name }}</a>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button v-if="conversationStatus === 'open'" @click="closeConversation" class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200">
                                Close
                            </button>
                            <span v-else class="px-3 py-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg">Closed</span>
                        </div>
                    </div>

                    {{-- Messages --}}
                    <div ref="adminMessagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3" aria-live="polite">
                        <template v-for="(msg, idx) in conversationMessages" :key="msg.id">
                            <div v-if="showTimestamp(idx)" class="text-center text-xs text-gray-400 dark:text-gray-500 py-2">@{{ formatGroupTime(msg.created_at) }}</div>
                            <div :class="msg.is_from_admin ? 'flex flex-col items-end' : 'flex flex-col items-start'">
                                <div :class="[
                                    'max-w-[75%] rounded-2xl px-4 py-2.5 text-sm whitespace-pre-wrap break-words',
                                    msg.is_from_admin
                                        ? 'bg-[var(--brand-button-bg)] text-white'
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100'
                                ]" v-html="linkify(msg.body)"></div>
                                <div v-if="idx === lastSeenAdminIndex" class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">Seen</div>
                            </div>
                        </template>
                    </div>

                    {{-- Input --}}
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex gap-2">
                            <textarea v-model="adminReplyText" @keydown.enter.exact.prevent="sendAdminReply" @input="sendTyping" rows="1" placeholder="Type a reply..." aria-label="Reply" class="flex-1 resize-none rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:border-transparent"></textarea>
                            <button @click="sendAdminReply" :disabled="!adminReplyText.trim()" class="px-4 py-2.5 rounded-xl bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] text-white text-sm font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                Send
                            </button>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="flex-1 flex items-center justify-center text-gray-400 dark:text-gray-500 text-sm">
                        Select a conversation
                    </div>
                </template>
            </div>
        </div>
    </div>

    @php
        // Built here, not inline: @json() splits its argument on every comma.
        $supportMarketingBase = config('app.is_testing') || config('app.env') === 'local'
            ? rtrim(url('/'), '/')
            : 'https://'._base_domain();
    @endphp
    <script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}"{!! nonce_attr() !!}><\/script>')</script>
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            var MARKETING_BASE = @json($supportMarketingBase);
            var PRESELECT = new URLSearchParams(window.location.search).get('c');

            Vue.createApp({
                data() {
                    return {
                        presence: @json(\App\Utils\SupportPresence::state(auth()->user())),
                        presenceReceivedAt: Date.now(),
                        now: Date.now(),
                        conversations: [],
                        selectedConversation: null,
                        conversationMessages: [],
                        conversationUser: {},
                        conversationStatus: '',
                        adminReplyText: '',
                        adminSending: false,
                        lastTypingAt: 0,
                        pollInterval: null,
                        msgPollInterval: null,
                        clockInterval: null,
                        mobileShowConversation: false,
                        isMobile: window.innerWidth < 768,
                    };
                },
                computed: {
                    availabilityText() {
                        if (!this.presence.online) {
                            return 'Offline. You are shown as away.';
                        }
                        if (!this.presence.is_agent) {
                            return (this.presence.agent_name || 'Another admin') + ' is online. Switching off takes the chat offline for everyone.';
                        }
                        if (!this.presence.available) {
                            return 'Online, waiting for this page to check in.';
                        }
                        var elapsed = Math.floor((this.now - this.presenceReceivedAt) / 1000);
                        var minutes = Math.ceil(Math.max(0, this.presence.confirm_in - elapsed) / 60);
                        return minutes > 0
                            ? 'Online and shown as available. Next check in ' + minutes + ' min.'
                            : 'Online. Confirm you are still here to stay online.';
                    },
                    // "Seen" goes under the newest admin message the other side has read.
                    lastSeenAdminIndex() {
                        for (var i = this.conversationMessages.length - 1; i >= 0; i--) {
                            var msg = this.conversationMessages[i];
                            if (msg.is_from_admin) {
                                return msg.read ? i : -1;
                            }
                        }
                        return -1;
                    },
                },
                mounted() {
                    this.fetchConversations(true);
                    this.startPolling();
                    this.clockInterval = setInterval(() => { this.now = Date.now(); }, 30000);
                    window.addEventListener('resize', () => {
                        this.isMobile = window.innerWidth < 768;
                    });
                    // The sidebar popover or the hourly prompt changed availability.
                    window.addEventListener('support-presence-changed', (e) => {
                        if (e.detail && e.detail.source !== 'inbox' && e.detail.presence) {
                            this.setPresence(e.detail.presence);
                        }
                    });
                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden) {
                            this.stopPolling();
                        } else {
                            this.fetchConversations();
                            if (this.selectedConversation) {
                                this.fetchMessages(this.selectedConversation.id, true);
                            }
                            this.startPolling();
                        }
                    });
                },
                beforeUnmount() {
                    this.stopPolling();
                    clearInterval(this.clockInterval);
                },
                methods: {
                    linkify(text) {
                        if (!text) return '';
                        var escaped = String(text)
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;')
                            .replace(/'/g, '&#39;');
                        return escaped.replace(/(https?:\/\/[^\s<]+|www\.[^\s<]+)/gi, function(m) {
                            var trailing = '';
                            var punct = m.match(/[.,!?)\]]+$/);
                            if (punct) {
                                trailing = punct[0];
                                m = m.slice(0, m.length - trailing.length);
                            }
                            var href = /^www\./i.test(m) ? 'https://' + m : m;
                            return '<a href="' + href + '" target="_blank" rel="noopener noreferrer nofollow" style="color: inherit; text-decoration: underline;">' + m + '</a>' + trailing;
                        });
                    },
                    setPresence(presence) {
                        this.presence = presence;
                        this.presenceReceivedAt = Date.now();
                        this.now = Date.now();
                    },
                    // Server-built paths only (SupportChatGuestController::normalizePage), so
                    // this can only ever point at the marketing site.
                    marketingUrl(path) {
                        return MARKETING_BASE + path;
                    },
                    countryFlag(code) {
                        if (!code || !/^[A-Z]{2}$/.test(code)) return '';
                        return String.fromCodePoint.apply(null, code.split('').map(function(c) { return 127397 + c.charCodeAt(0); }));
                    },
                    countryName(code) {
                        try {
                            return new Intl.DisplayNames(undefined, { type: 'region' }).of(code) || code;
                        } catch (e) {
                            return code;
                        }
                    },
                    startPolling() {
                        this.stopPolling();
                        this.pollInterval = setInterval(() => {
                            this.fetchConversations();
                        }, 5000);
                        // 5 seconds, not faster: the admin group throttles each route at 30 a
                        // minute per user, and two open inbox windows share that budget.
                        this.msgPollInterval = setInterval(() => {
                            if (this.selectedConversation) {
                                this.fetchMessages(this.selectedConversation.id, true);
                            }
                        }, 5000);
                    },
                    stopPolling() {
                        if (this.pollInterval) clearInterval(this.pollInterval);
                        if (this.msgPollInterval) clearInterval(this.msgPollInterval);
                    },
                    adminFetch(url, options) {
                        options = options || {};
                        // Without these the request is a plain fetch with Accept: */*, so
                        // Request::expectsJson() is false and EnsureUserIsAdmin answers a lapsed
                        // re-auth window with a 302 to HTML. fetch follows it, r.json() throws, and
                        // every catch below is silent - the page would just freeze mid-poll.
                        options.headers = Object.assign({
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }, options.headers || {});

                        return fetch(url, options).then(r => {
                            if (r.status === 423) {
                                // The re-auth window lapsed. Stop polling first, or the reload
                                // races another 5-second tick.
                                this.stopPolling();
                                window.location.reload();
                                throw r;
                            }
                            return r;
                        });
                    },
                    fetchConversations(initial) {
                        this.adminFetch('/admin/support/conversations')
                            .then(r => r.json())
                            .then(data => {
                                this.conversations = data.conversations;
                                if (data.presence) {
                                    this.setPresence(data.presence);
                                }
                                if (this.selectedConversation) {
                                    var updated = data.conversations.find(c => c.id === this.selectedConversation.id);
                                    if (updated) this.selectedConversation = updated;
                                } else if (initial && PRESELECT) {
                                    // Arrived from an alert, email or push about one conversation.
                                    var target = data.conversations.find(c => c.id === PRESELECT);
                                    if (target) this.selectConversation(target);
                                }
                            })
                            .catch(() => {});
                    },
                    selectConversation(conv) {
                        this.selectedConversation = conv;
                        this.mobileShowConversation = true;
                        this.fetchMessages(conv.id);
                        this.markRead(conv.id);
                    },
                    fetchMessages(convId, silent) {
                        this.adminFetch('/admin/support/' + convId + '/messages')
                            .then(r => r.json())
                            .then(data => {
                                var hadMessages = this.conversationMessages.length;
                                this.conversationMessages = data.messages;
                                this.conversationUser = data.user;
                                this.conversationStatus = data.status;
                                if (!silent || data.messages.length !== hadMessages) {
                                    this.$nextTick(() => this.scrollToBottom());
                                }
                                // A message that arrives while the thread is open in front of the
                                // admin has been read. Without this it stayed unread until the
                                // thread was clicked again, and the other side never saw "Seen".
                                // hasFocus() too, not just a visible tab: a window left showing
                                // behind another app must not mark anything read, or the admin's
                                // alert is silenced and the visitor sees "Seen" from nobody.
                                var hasUnread = data.messages.some(m => !m.is_from_admin && !m.read);
                                if (!silent || (hasUnread && !document.hidden && document.hasFocus())) {
                                    this.markRead(convId);
                                }
                            })
                            .catch(() => {});
                    },
                    sendTyping() {
                        if (!this.selectedConversation || !this.adminReplyText.trim()) return;
                        var now = Date.now();
                        if (now - this.lastTypingAt < 3000) return;
                        this.lastTypingAt = now;
                        this.adminFetch('/admin/support/' + this.selectedConversation.id + '/typing', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        }).catch(() => {});
                    },
                    sendAdminReply() {
                        var text = this.adminReplyText.trim();
                        if (!text || !this.selectedConversation || this.adminSending) return;
                        this.adminSending = true;
                        this.adminReplyText = '';
                        this.lastTypingAt = 0;
                        this.adminFetch('/admin/support/' + this.selectedConversation.id + '/reply', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify({ body: text })
                        })
                        .then(r => { if (!r.ok) throw r; return r.json(); })
                        .then(data => {
                            this.adminSending = false;
                            if (data.message) {
                                this.conversationMessages.push(data.message);
                                this.$nextTick(() => this.scrollToBottom());
                            }
                        })
                        .catch(() => {
                            this.adminSending = false;
                            this.adminReplyText = text;
                        });
                    },
                    markRead(convId) {
                        this.adminFetch('/admin/support/' + convId + '/mark-read', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        }).then(() => {
                            var conv = this.conversations.find(c => c.id === convId);
                            if (conv) conv.unread_count = 0;
                        }).catch(() => {});
                    },
                    toggleAvailability(checkbox) {
                        var available = checkbox.checked;
                        this.adminFetch('/admin/support/toggle-availability', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify({ available: available })
                        })
                        .then(r => { if (!r.ok) throw r; return r.json(); })
                        .then(data => {
                            this.setPresence(data.presence);
                            window.dispatchEvent(new CustomEvent('support-presence-changed', {
                                detail: { presence: data.presence, source: 'inbox' },
                            }));
                        })
                        .catch(() => {
                            // presence.online did not change, so Vue will not repaint the box:
                            // put it back by hand or it shows a state the server never took.
                            checkbox.checked = !!this.presence.online;
                        });
                    },
                    closeConversation() {
                        if (!this.selectedConversation) return;
                        this.adminFetch('/admin/support/' + this.selectedConversation.id + '/close', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        })
                        .then(r => r.json())
                        .then(() => {
                            this.conversationStatus = 'closed';
                            this.fetchConversations();
                        })
                        .catch(() => {});
                    },
                    scrollToBottom() {
                        var c = this.$refs.adminMessagesContainer;
                        if (c) c.scrollTop = c.scrollHeight;
                    },
                    showTimestamp(idx) {
                        if (idx === 0) return true;
                        var curr = new Date(this.conversationMessages[idx].created_at);
                        var prev = new Date(this.conversationMessages[idx - 1].created_at);
                        return (curr - prev) > 300000; // 5 min gap
                    },
                    formatGroupTime(iso) {
                        var d = new Date(iso);
                        var now = new Date();
                        var opts = { hour: 'numeric', minute: '2-digit' };
                        if (d.toDateString() === now.toDateString()) {
                            return 'Today ' + d.toLocaleTimeString(undefined, opts);
                        }
                        var yesterday = new Date(now);
                        yesterday.setDate(yesterday.getDate() - 1);
                        if (d.toDateString() === yesterday.toDateString()) {
                            return 'Yesterday ' + d.toLocaleTimeString(undefined, opts);
                        }
                        return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) + ' ' + d.toLocaleTimeString(undefined, opts);
                    },
                    formatTime(iso) {
                        if (!iso) return '';
                        var d = new Date(iso);
                        var now = new Date();
                        if (d.toDateString() === now.toDateString()) {
                            return d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
                        }
                        return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
                    },
                },
            }).mount('#support-admin-app');
        });
    </script>
</x-app-admin-layout>
