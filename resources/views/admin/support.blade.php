<x-app-admin-layout>
    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            /* The inbox: who is writing, and the conversation that is open. Two panes from a
               tablet up; on a phone the list comes first, a conversation opens over it and its
               head has the way back. Which pane shows on a phone is the page's own script
               (isMobile, mobileShowConversation); this is only how they look. */
            .sup-avail {
              display: flex;
              align-items: center;
              justify-content: space-between;
              gap: 1rem;
            }
            .sup-avail-text {
              min-width: 0;
            }
            .sup-avail-text strong {
              display: block;
              font-size: 0.875rem;
              font-weight: 600;
              color: rgb(var(--ap-ink));
            }
            .sup-avail-text .event-status {
              align-items: baseline;
            }
            .sup-avail-text .event-status::before {
              flex: none;
              transform: translateY(-1px);
            }
            .sup-frame {
              display: flex;
              gap: 1rem;
              height: calc(100vh - 27rem);
              height: calc(100dvh - 27rem);
              min-height: 24rem;
            }
            .sup-pane {
              display: flex;
              flex-direction: column;
              min-width: 0;
              overflow: hidden;
            }
            .sup-list {
              flex: 0 0 min(22rem, 36%);
            }
            .sup-thread {
              flex: 1 1 0;
            }
            @media (max-width: 767.98px) {
              .sup-frame {
                height: calc(100vh - 25rem);
                height: calc(100dvh - 25rem);
              }
              .sup-list,
              .sup-thread {
                flex: 1 1 100%;
              }
            }
            .sup-pane-head {
              display: flex;
              align-items: center;
              justify-content: space-between;
              gap: 0.75rem;
              border-bottom: 1px solid rgb(var(--ap-border));
              padding: 0.875rem 1rem;
            }
            .sup-scroll {
              flex: 1;
              overflow-y: auto;
            }
            .sup-none {
              margin: 0;
              padding: 2rem 1rem;
              font-size: 0.875rem;
              text-align: center;
              color: rgb(var(--ap-ink-3));
            }
            /* One conversation in the list. A button, so a keyboard reaches it: it was a div
               with a click handler. */
            button.sup-conv {
              display: block;
              width: 100%;
              border: 0;
              border-bottom: 1px solid var(--ap-hairline);
              padding: 0.75rem 1rem;
              background: none;
              text-align: start;
              cursor: pointer;
              transition: background-color 0.2s;
            }
            button.sup-conv:hover {
              background: var(--ap-tint-1);
            }
            button.sup-conv[aria-current="true"] {
              background: var(--ap-tint-2);
              box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.06);
            }
            .dark button.sup-conv[aria-current="true"] {
              box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5);
            }
            button.sup-conv:focus-visible {
              outline: 2px solid var(--brand-blue);
              outline-offset: -2px;
            }
            .sup-conv-top {
              display: flex;
              align-items: center;
              justify-content: space-between;
              gap: 0.5rem;
            }
            .sup-conv-who {
              display: flex;
              align-items: center;
              gap: 0.375rem;
              min-width: 0;
            }
            .sup-conv-who b {
              overflow: hidden;
              font-size: 0.875rem;
              font-weight: 600;
              text-overflow: ellipsis;
              white-space: nowrap;
              color: rgb(var(--ap-ink));
            }
            .sup-conv-who .event-chip {
              flex: none;
              margin-inline-start: 0;
            }
            .sup-dot {
              flex: none;
              width: 0.5rem;
              height: 0.5rem;
              border-radius: 50%;
              background: #22c55e;
            }
            .sup-conv-text,
            .sup-conv-time {
              display: block;
              overflow: hidden;
              font-size: 0.8125rem;
              text-overflow: ellipsis;
              white-space: nowrap;
              color: rgb(var(--ap-ink-3));
            }
            .sup-conv-text {
              margin-top: 0.125rem;
            }
            .sup-conv.is-unread .sup-conv-text {
              font-weight: 500;
              color: rgb(var(--ap-ink));
            }
            .sup-conv-time {
              margin-top: 0.125rem;
              font-size: 0.75rem;
              color: rgb(var(--ap-ink-4));
            }
            .sup-who {
              display: flex;
              align-items: flex-start;
              gap: 0.625rem;
              min-width: 0;
            }
            .sup-who > div {
              min-width: 0;
            }
            .sup-who-name {
              display: flex;
              flex-wrap: wrap;
              align-items: center;
              gap: 0.125rem 0.625rem;
              font-size: 0.9375rem;
              font-weight: 600;
              color: rgb(var(--ap-ink));
              overflow-wrap: anywhere;
            }
            .sup-who-line {
              display: flex;
              flex-wrap: wrap;
              align-items: center;
              gap: 0.25rem 0.5rem;
              margin-top: 0.125rem;
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
              overflow-wrap: anywhere;
            }
            .sup-who-line .event-chip {
              margin-inline-start: 0;
            }
            a.event-chip:hover {
              color: var(--brand-blue);
            }
            .sup-back {
              flex: none;
              border: 0;
              border-radius: 0.5rem;
              padding: 0.25rem;
              background: none;
              color: rgb(var(--ap-ink-3));
              cursor: pointer;
            }
            .sup-back:hover {
              background: var(--ap-tint-2);
              color: rgb(var(--ap-ink));
            }
            .sup-back svg {
              width: 1.25rem;
              height: 1.25rem;
            }
            [dir="rtl"] .sup-back svg {
              transform: scaleX(-1);
            }
            .sup-messages {
              display: flex;
              flex: 1;
              flex-direction: column;
              gap: 0.625rem;
              overflow-y: auto;
              padding: 1rem;
            }
            .sup-stamp {
              padding: 0.375rem 0;
              font-size: 0.75rem;
              text-align: center;
              color: rgb(var(--ap-ink-4));
            }
            .sup-msg {
              display: flex;
              flex-direction: column;
              align-items: flex-start;
            }
            .sup-msg.is-mine {
              align-items: flex-end;
            }
            .sup-bubble {
              max-width: min(34rem, 80%);
              border-radius: 1rem;
              border-end-start-radius: 0.25rem;
              padding: 0.5rem 0.875rem;
              background: var(--ap-tint-2);
              font-size: 0.875rem;
              line-height: 1.45;
              white-space: pre-wrap;
              overflow-wrap: anywhere;
              color: rgb(var(--ap-ink));
            }
            .sup-msg.is-mine .sup-bubble {
              border-end-start-radius: 1rem;
              border-end-end-radius: 0.25rem;
              background: var(--brand-button-bg);
              color: #fff;
            }
            .sup-seen {
              margin-top: 0.125rem;
              font-size: 0.6875rem;
              color: rgb(var(--ap-ink-4));
            }
            .sup-compose {
              display: flex;
              align-items: flex-end;
              gap: 0.5rem;
              border-top: 1px solid rgb(var(--ap-border));
              padding: 0.75rem 1rem;
            }
            .sup-compose textarea {
              flex: 1;
              min-width: 0;
              max-height: 8rem;
              resize: none;
            }
            .sup-empty {
              display: flex;
              flex: 1;
              align-items: center;
              justify-content: center;
              padding: 2rem;
              font-size: 0.875rem;
              color: rgb(var(--ap-ink-3));
            }
        </style>
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'support'])

    {{-- Outside the Vue mount on purpose: Vue compiles every text node inside one as a template,
         and this line is a translation an admin can override. --}}
    <div class="page-head">
        <p class="page-lead">{{ __('messages.admin_support_lead') }}</p>
    </div>

    <div id="support-admin-app" class="page-shell page-stack" v-cloak>
        {{-- Whether the chat is shown as staffed. A dot and its words, then the switch. --}}
        <div class="ap-card rounded-xl page-card sup-avail">
            <div class="sup-avail-text">
                <strong>Support availability</strong>
                <span class="event-status" :class="presence.available ? 'is-on' : (presence.online ? 'is-warn' : '')">@{{ availabilityText }}</span>
            </div>
            <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                <input type="checkbox" :checked="presence.online" @change="toggleAvailability($event.target)" class="sr-only peer" aria-label="Available for chat">
                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] peer-focus-visible:ring-2 peer-focus-visible:ring-[var(--brand-blue)] transition-colors"></div>
                <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
            </label>
        </div>

        <div class="sup-frame">
            {{-- The list of conversations --}}
            <section v-show="!mobileShowConversation || !isMobile" class="ap-card rounded-xl sup-pane sup-list" aria-label="Conversations">
                <div class="sup-pane-head">
                    <h2 class="page-card-title">Conversations</h2>
                </div>
                <div class="sup-scroll">
                    <p v-if="conversations.length === 0" class="sup-none">No conversations yet</p>
                    <button type="button" v-for="conv in conversations" :key="conv.id" data-conv
                        @click="selectConversation(conv)"
                        class="sup-conv" :class="{ 'is-unread': conv.unread_count > 0 }"
                        :aria-current="selectedConversation && selectedConversation.id === conv.id ? 'true' : null">
                        <span class="sup-conv-top">
                            <span class="sup-conv-who">
                                <span v-if="conv.online" class="sup-dot" :title="conv.is_guest ? 'On the site now' : 'In the app now'"></span>
                                <span v-if="conv.guest_country" class="shrink-0 text-sm leading-none" :title="countryName(conv.guest_country)">@{{ countryFlag(conv.guest_country) }}</span>
                                <b><bdi>@{{ conv.display_name }}</bdi></b>
                                <span v-if="conv.is_guest" class="event-chip">Visitor</span>
                            </span>
                            <span class="flex items-center gap-2 shrink-0">
                                <span v-if="conv.unread_count > 0" class="ap-tab-count is-waiting">@{{ conv.unread_count }}</span>
                                <span v-if="conv.status === 'closed'" class="event-status">Closed</span>
                            </span>
                        </span>
                        <span class="sup-conv-text"><bdi>@{{ conv.last_message_preview }}</bdi></span>
                        <span class="sup-conv-time">@{{ formatTime(conv.last_message_at) }}</span>
                    </button>
                </div>
            </section>

            {{-- The conversation that is open --}}
            <section v-show="!isMobile || mobileShowConversation" class="ap-card rounded-xl sup-pane sup-thread" aria-label="Messages">
                <template v-if="selectedConversation">
                    <div class="sup-pane-head">
                        <div class="sup-who">
                            <button type="button" v-if="isMobile" @click="mobileShowConversation = false" class="sup-back" aria-label="Conversations">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                            </button>
                            <div>
                                <div class="sup-who-name">
                                    <bdi>@{{ conversationUser.name || conversationUser.email || 'Website visitor' }}</bdi>
                                    <span v-if="conversationUser.online" class="event-status is-on">@{{ conversationUser.is_guest ? 'On the site now' : 'In the app now' }}</span>
                                </div>
                                <template v-if="conversationUser.is_guest">
                                    <div class="sup-who-line">
                                        <x-link v-if="conversationUser.email" v-bind:href="'mailto:' + conversationUser.email">@{{ conversationUser.email }}</x-link>
                                        <span v-else>No email left yet</span>
                                        <span v-if="conversationUser.country">@{{ countryFlag(conversationUser.country) }} @{{ countryName(conversationUser.country) }}</span>
                                    </div>
                                    <div class="sup-who-line">
                                        <span v-if="conversationUser.current_page">Now on <x-link v-bind:href="marketingUrl(conversationUser.current_page)" target="_blank">@{{ conversationUser.current_page }}</x-link></span>
                                        <span v-if="conversationUser.started_on">Started on <x-link v-bind:href="marketingUrl(conversationUser.started_on)" target="_blank">@{{ conversationUser.started_on }}</x-link></span>
                                        {{-- The visitor typed this address and nobody verified it, so it only MATCHES an account. --}}
                                        <a v-if="conversationUser.has_account" :href="'/admin/users?search=' + encodeURIComponent(conversationUser.email)" class="event-chip">Email matches an account</a>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="sup-who-line">
                                        <a :href="'/admin/users?search=' + encodeURIComponent(conversationUser.email)" class="event-link">@{{ conversationUser.email }}</a>
                                    </div>
                                    <div v-if="conversationUser.roles && conversationUser.roles.length" class="sup-who-line">
                                        <a v-for="role in conversationUser.roles" :key="role.subdomain" :href="'/' + role.subdomain" target="_blank" class="event-chip"><bdi>@{{ role.name }}</bdi></a>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="shrink-0">
                            <button type="button" v-if="conversationStatus === 'open'" @click="closeConversation" class="page-tool">Close</button>
                            <span v-else class="event-status">Closed</span>
                        </div>
                    </div>

                    {{-- Messages --}}
                    <div ref="adminMessagesContainer" class="sup-messages" aria-live="polite">
                        <template v-for="(msg, idx) in conversationMessages" :key="msg.id">
                            <div v-if="showTimestamp(idx)" class="sup-stamp">@{{ formatGroupTime(msg.created_at) }}</div>
                            <div class="sup-msg" :class="{ 'is-mine': msg.is_from_admin }">
                                <div class="sup-bubble" dir="auto" v-html="linkify(msg.body)"></div>
                                <div v-if="idx === lastSeenAdminIndex" class="sup-seen">Seen</div>
                            </div>
                        </template>
                    </div>

                    {{-- The reply --}}
                    <div class="sup-compose">
                        <textarea v-model="adminReplyText" @keydown.enter.exact.prevent="sendAdminReply" @input="sendTyping" rows="1" dir="auto" placeholder="Type a reply..." aria-label="Reply"
                            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"></textarea>
                        <x-brand-button size="sm" @click="sendAdminReply" v-bind:disabled="!adminReplyText.trim()">Send</x-brand-button>
                    </div>
                </template>
                <template v-else>
                    <div class="sup-empty">Select a conversation</div>
                </template>
            </section>
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
