<style>
[v-cloak] { display: none !important; }
.chat-panel {
    background: linear-gradient(135deg, #ffffff 0%, #fefefe 30%, #f9fafb 100%);
    box-shadow: 0 4px 16px -2px rgba(0,0,0,0.08), 0 10px 25px rgba(0,0,0,0.06);
}
.dark .chat-panel {
    background: linear-gradient(135deg, #2e2e31 0%, #2a2a2d 30%, #252526 100%);
    box-shadow: inset 0 1px 0 0 rgba(255,255,255,0.06), inset 0 0 0 1px rgba(255,255,255,0.03), 0 10px 25px rgba(0,0,0,0.4), 0 0 0 1px rgba(0,0,0,0.2);
}
.chat-panel::after {
    content: '';
    position: absolute;
    top: 0;
    left: 10%;
    right: 10%;
    height: 1px;
    z-index: 10;
    background: transparent;
}
.dark .chat-panel::after {
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
}
.chat-bubble-admin {
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.dark .chat-bubble-admin {
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.chat-bubble-user {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.chat-input-area {
    border-top: 1px solid rgba(0,0,0,0.06);
}
.dark .chat-input-area {
    border-top: 1px solid rgba(255,255,255,0.06);
}
</style>
<div id="support-chat-widget" v-cloak>
    {{-- Launcher button --}}
    <button v-if="showButton" @click="togglePanel"
        class="fixed bottom-6 end-6 z-50 w-14 h-14 rounded-full bg-gradient-to-br from-[var(--brand-button-bg-light)] to-[var(--brand-button-bg)] hover:from-[var(--brand-button-bg)] hover:to-[var(--brand-button-bg-hover)] text-white transition-all duration-200 flex items-center justify-center"
        :class="{ 'scale-0': !entered, 'scale-100': entered }"
        style="box-shadow: inset 0 1px 0 0 rgba(255,255,255,0.2), 0 2px 8px rgba(0,0,0,0.15), 0 4px 16px -4px rgba(0,0,0,0.2); transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s;">
        {{-- Chat icon --}}
        <svg v-if="!panelOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        {{-- Close icon --}}
        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        {{-- Available dot --}}
        <span v-if="available && !panelOpen" class="absolute bottom-0 start-0 w-3.5 h-3.5 bg-green-500 border-2 border-white dark:border-gray-900 rounded-full"></span>
        {{-- Unread badge --}}
        <span v-if="unreadCount > 0 && !panelOpen" class="absolute -top-1 -end-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold text-white bg-red-500 rounded-full">@{{ unreadCount }}</span>
    </button>

    {{-- Chat panel --}}
    <div v-if="panelOpen"
        class="fixed z-[60] flex flex-col chat-panel rounded-2xl overflow-hidden transition-all duration-200"
        :class="isMobile ? 'inset-2 top-8' : ''"
        :style="isMobile ? '' : 'bottom: 90px; width: 380px; height: 520px; ' + (isRtl ? 'left: 24px;' : 'right: 24px;')">
        {{-- Header --}}
        <div class="text-white px-5 py-4 shrink-0 relative z-10" style="background: linear-gradient(135deg, var(--brand-button-bg-light), var(--brand-button-bg)); box-shadow: inset 0 1px 0 0 rgba(255,255,255,0.15), 0 4px 6px -1px rgba(0,0,0,0.2), 0 8px 16px -4px rgba(0,0,0,0.15);">
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-semibold text-base">Support</div>
                    <div class="flex items-center gap-1.5 mt-1 text-sm opacity-90">
                        <span :class="available ? 'bg-green-400' : 'bg-gray-400'" class="w-2 h-2 rounded-full inline-block"></span>
                        <span v-if="available">We're online</span>
                        <span v-else>Leave a message</span>
                    </div>
                    <div class="text-xs opacity-75 mt-0.5">
                        <span v-if="available">We typically reply in a few minutes</span>
                        <span v-else>We'll reply by email</span>
                    </div>
                </div>
                <button @click="panelOpen = false" class="p-1 rounded hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div ref="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-3">
            {{-- Welcome message --}}
            <div v-if="messages.length === 0" class="flex justify-start">
                <div class="max-w-[80%] rounded-2xl px-4 py-2.5 text-sm bg-gray-100 dark:bg-[#2d2d30] text-gray-900 dark:text-gray-100 chat-bubble-admin">
                    Hi! How can we help you today?
                </div>
            </div>
            <template v-for="(msg, idx) in messages" :key="msg.id">
                <div v-if="showTimestamp(idx)" class="text-center text-xs text-gray-400 dark:text-gray-500 py-1">@{{ formatGroupTime(msg.created_at) }}</div>
                <div :class="msg.is_from_admin ? 'flex justify-start' : 'flex justify-end'">
                    <div :class="[
                        'max-w-[80%] rounded-2xl px-4 py-2.5 text-sm whitespace-pre-wrap break-words',
                        msg.is_from_admin
                            ? 'bg-gray-100 dark:bg-[#2d2d30] text-gray-900 dark:text-gray-100 chat-bubble-admin'
                            : 'bg-[var(--brand-button-bg)] text-white chat-bubble-user'
                    ]">@{{ msg.body }}</div>
                </div>
            </template>
        </div>

        {{-- Input --}}
        <div class="p-3 shrink-0 chat-input-area">
            <div class="flex items-end gap-2">
                <textarea v-model="inputText" @keydown.enter.exact.prevent="sendMessage" rows="1" placeholder="Type a message..." class="flex-1 resize-none rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-[#252526] text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:border-transparent" style="max-height: 100px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.06);"></textarea>
                <button v-if="inputText.trim()" @click="sendMessage" class="p-2 rounded-xl bg-gradient-to-br from-[var(--brand-button-bg-light)] to-[var(--brand-button-bg)] hover:from-[var(--brand-button-bg)] hover:to-[var(--brand-button-bg-hover)] text-white transition-all duration-200 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-7 7m7-7l7 7" transform="rotate(45 12 12)"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}"{!! nonce_attr() !!}><\/script>')</script>
<script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function() {
        Vue.createApp({
            data() {
                return {
                    panelOpen: false,
                    showButton: false,
                    isDashboard: window.location.pathname === '/dashboard',
                    available: false,
                    unreadCount: 0,
                    messages: [],
                    inputText: '',
                    sending: false,
                    entered: false,
                    isMobile: window.innerWidth < 640,
                    isRtl: document.documentElement.dir === 'rtl' || document.body.dir === 'rtl',
                    openPollInterval: null,
                    closedPollInterval: null,
                };
            },
            watch: {
                panelOpen(open) {
                    this.setupPolling();
                    if (open) {
                        this.fetchMessages();
                        this.markRead();
                    } else if (!this.isDashboard) {
                        this.showButton = false;
                    }
                },
            },
            mounted() {
                if (document.getElementById('support-admin-app')) return;
                this.fetchStatus();
                this.setupPolling();
                if (this.isDashboard) {
                    setTimeout(() => { this.entered = true; this.showButton = true; }, 1000);
                } else {
                    this.entered = true;
                }
                window.addEventListener('resize', () => {
                    this.isMobile = window.innerWidth < 640;
                });
                window.addEventListener('show-support-chat', () => {
                    this.showButton = true;
                    this.panelOpen = true;
                });
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        this.clearAllPolling();
                    } else {
                        this.fetchStatus();
                        if (this.panelOpen) this.fetchMessages();
                        this.setupPolling();
                    }
                });
            },
            beforeUnmount() {
                this.clearAllPolling();
            },
            methods: {
                clearAllPolling() {
                    if (this.openPollInterval) clearInterval(this.openPollInterval);
                    if (this.closedPollInterval) clearInterval(this.closedPollInterval);
                    this.openPollInterval = null;
                    this.closedPollInterval = null;
                },
                setupPolling() {
                    this.clearAllPolling();
                    if (this.panelOpen) {
                        this.openPollInterval = setInterval(() => {
                            this.fetchMessages();
                            this.markRead();
                        }, 5000);
                    } else {
                        this.closedPollInterval = setInterval(() => {
                            this.fetchStatus();
                        }, 30000);
                    }
                },
                fetchStatus() {
                    fetch('/support-chat/status')
                        .then(r => r.json())
                        .then(data => {
                            this.available = data.available;
                            this.unreadCount = data.unread_count;
                            this.updateSidebarBadge();
                        })
                        .catch(() => {});
                },
                fetchMessages() {
                    fetch('/support-chat/messages')
                        .then(r => r.json())
                        .then(data => {
                            var hadCount = this.messages.length;
                            this.messages = data.messages;
                            this.available = data.available;
                            if (data.messages.length !== hadCount) {
                                this.$nextTick(() => this.scrollToBottom());
                            }
                            this.unreadCount = 0;
                            this.updateSidebarBadge();
                        })
                        .catch(() => {});
                },
                sendMessage() {
                    var text = this.inputText.trim();
                    if (!text || this.sending) return;
                    this.sending = true;
                    this.inputText = '';
                    fetch('/support-chat/messages', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ body: text })
                    })
                    .then(r => { if (!r.ok) throw r; return r.json(); })
                    .then(data => {
                        this.sending = false;
                        if (data.message) {
                            this.messages.push(data.message);
                            this.$nextTick(() => this.scrollToBottom());
                        }
                    })
                    .catch(() => {
                        this.sending = false;
                        this.inputText = text;
                    });
                },
                markRead() {
                    if (this.unreadCount > 0) {
                        fetch('/support-chat/mark-read', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        }).then(() => {
                            this.unreadCount = 0;
                            this.updateSidebarBadge();
                        }).catch(() => {});
                    }
                },
                togglePanel() {
                    this.panelOpen = !this.panelOpen;
                },
                scrollToBottom() {
                    var c = this.$refs.chatMessages;
                    if (c) c.scrollTop = c.scrollHeight;
                },
                showTimestamp(idx) {
                    if (idx === 0) return true;
                    var curr = new Date(this.messages[idx].created_at);
                    var prev = new Date(this.messages[idx - 1].created_at);
                    return (curr - prev) > 300000;
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
                updateSidebarBadge() {
                    var self = this;
                    document.querySelectorAll('.js-support-chat-sidebar-badge').forEach(function(badge) {
                        if (self.unreadCount > 0) {
                            badge.textContent = self.unreadCount;
                            badge.style.display = '';
                        } else {
                            badge.style.display = 'none';
                        }
                    });
                },
            },
        }).mount('#support-chat-widget');
    });
</script>
