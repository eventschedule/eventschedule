<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'support'])

    <div id="support-admin-app" class="mt-6">
        {{-- Top bar with availability toggle --}}
        <div class="ap-card rounded-xl p-4 mb-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Support availability</span>
                <div :class="available ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'" class="text-sm">
                    @{{ available ? 'Online' : 'Offline' }}
                </div>
            </div>
            <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                <input type="checkbox" :checked="available" @change="toggleAvailability" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
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
                                ? 'bg-gray-100 dark:bg-[#2d2d30]'
                                : 'hover:bg-gray-50 dark:hover:bg-[#252526]'
                        ]">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">@{{ conv.user_name || conv.user_email }}</span>
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
                            <button v-if="isMobile" @click="mobileShowConversation = false" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 shrink-0">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">@{{ conversationUser.name || conversationUser.email }}</div>
                                <a :href="'/admin/users?search=' + encodeURIComponent(conversationUser.email)" class="text-xs text-[var(--brand-blue)] hover:underline truncate block">@{{ conversationUser.email }}</a>
                                <div v-if="conversationUser.roles && conversationUser.roles.length" class="flex flex-wrap gap-1 mt-1">
                                    <a v-for="role in conversationUser.roles" :key="role.subdomain" :href="'/' + role.subdomain" target="_blank" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-[#2d2d30] text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-[#3d3d40] transition-colors">@{{ role.name }}</a>
                                </div>
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
                    <div ref="adminMessagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3">
                        <template v-for="(msg, idx) in conversationMessages" :key="msg.id">
                            <div v-if="showTimestamp(idx)" class="text-center text-xs text-gray-400 dark:text-gray-500 py-2">@{{ formatGroupTime(msg.created_at) }}</div>
                            <div :class="msg.is_from_admin ? 'flex justify-end' : 'flex justify-start'">
                                <div :class="[
                                    'max-w-[75%] rounded-2xl px-4 py-2.5 text-sm whitespace-pre-wrap break-words',
                                    msg.is_from_admin
                                        ? 'bg-[var(--brand-button-bg)] text-white'
                                        : 'bg-gray-100 dark:bg-[#2d2d30] text-gray-900 dark:text-gray-100'
                                ]">@{{ msg.body }}</div>
                            </div>
                        </template>
                    </div>

                    {{-- Input --}}
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex gap-2">
                            <textarea v-model="adminReplyText" @keydown.enter.exact.prevent="sendAdminReply" rows="1" placeholder="Type a reply..." class="flex-1 resize-none rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-[#1e1e1e] text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:border-transparent"></textarea>
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

    <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            Vue.createApp({
                data() {
                    return {
                        available: false,
                        conversations: [],
                        selectedConversation: null,
                        conversationMessages: [],
                        conversationUser: {},
                        conversationStatus: '',
                        adminReplyText: '',
                        pollInterval: null,
                        msgPollInterval: null,
                        mobileShowConversation: false,
                        isMobile: window.innerWidth < 768,
                    };
                },
                mounted() {
                    this.fetchConversations();
                    this.startPolling();
                    window.addEventListener('resize', () => {
                        this.isMobile = window.innerWidth < 768;
                    });
                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden) {
                            this.stopPolling();
                        } else {
                            this.fetchConversations();
                            if (this.selectedConversation) {
                                this.fetchMessages(this.selectedConversation.id);
                            }
                            this.startPolling();
                        }
                    });
                },
                beforeUnmount() {
                    this.stopPolling();
                },
                methods: {
                    startPolling() {
                        this.stopPolling();
                        this.pollInterval = setInterval(() => {
                            this.fetchConversations();
                        }, 5000);
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
                    fetchConversations() {
                        fetch('/admin/support/conversations')
                            .then(r => r.json())
                            .then(data => {
                                this.conversations = data.conversations;
                                this.available = data.available;
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
                        fetch('/admin/support/' + convId + '/messages')
                            .then(r => r.json())
                            .then(data => {
                                var hadMessages = this.conversationMessages.length;
                                this.conversationMessages = data.messages;
                                this.conversationUser = data.user;
                                this.conversationStatus = data.status;
                                if (!silent || data.messages.length !== hadMessages) {
                                    this.$nextTick(() => this.scrollToBottom());
                                }
                                if (!silent) {
                                    this.markRead(convId);
                                }
                            })
                            .catch(() => {});
                    },
                    sendAdminReply() {
                        var text = this.adminReplyText.trim();
                        if (!text || !this.selectedConversation) return;
                        this.adminReplyText = '';
                        fetch('/admin/support/' + this.selectedConversation.id + '/reply', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify({ body: text })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.message) {
                                this.conversationMessages.push(data.message);
                                this.$nextTick(() => this.scrollToBottom());
                            }
                        })
                        .catch(() => {});
                    },
                    markRead(convId) {
                        fetch('/admin/support/' + convId + '/mark-read', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        }).then(() => {
                            var conv = this.conversations.find(c => c.id === convId);
                            if (conv) conv.unread_count = 0;
                        }).catch(() => {});
                    },
                    toggleAvailability() {
                        fetch('/admin/support/toggle-availability', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        })
                        .then(r => r.json())
                        .then(data => { this.available = data.available; })
                        .catch(() => {});
                    },
                    closeConversation() {
                        if (!this.selectedConversation) return;
                        fetch('/admin/support/' + this.selectedConversation.id + '/close', {
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
