<x-app-admin-layout>

    <x-slot name="head">
        <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
    </x-slot>

    <div id="app" class="max-w-3xl mx-auto px-4">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('messages.checkin_dashboard') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('sales') }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-[#2d2d30] border border-gray-300 dark:border-[#2d2d30] rounded-lg hover:bg-gray-50 dark:hover:bg-[#3d3d40] transition-colors">
                    <svg class="w-4 h-4 me-1.5" fill="currentColor" viewBox="0 0 24 24"><path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z"/></svg>
                    {{ __('messages.sales') }}
                </a>
                <a href="{{ route('ticket.scan') }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-[#4E81FA] rounded-lg hover:bg-blue-600 transition-colors">
                    <svg class="w-4 h-4 me-1.5" fill="currentColor" viewBox="0 0 24 24"><path d="M4,4H10V10H4V4M20,4V10H14V4H20M14,15H16V13H14V11H16V13H18V11H20V13H18V15H20V18H18V20H16V18H13V20H11V16H14V15M16,15V18H18V15H16M4,20V14H10V20H4M6,6V8H8V6H6M16,6V8H18V6H16M6,16V18H8V16H6M2,2V6H0V2A2,2 0 0,1 2,0H6V2H2M22,0A2,2 0 0,1 24,2V6H22V2H18V0H22M2,18V22H6V24H2A2,2 0 0,1 0,22V18H2M22,22V18H24V22A2,2 0 0,1 22,24H18V22H22Z"/></svg>
                    {{ __('messages.scan_ticket') }}
                </a>
            </div>
        </div>

        <!-- Selectors -->
        <div class="flex flex-col sm:flex-row gap-3 mb-6">
            <div class="flex-1">
                <x-event-selector />
            </div>
            <select v-if="availableDates.length > 0" v-model="selectedDate" @change="fetchStats" class="sm:w-48 rounded-lg border-gray-300 dark:border-[#2d2d30] dark:bg-[#1e1e1e] dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option v-for="date in availableDates" :key="date" :value="date">@{{ date }}</option>
            </select>
        </div>

        <!-- Empty state -->
        <div v-if="!selectedEventId" class="text-center py-16">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <p class="mt-4 text-gray-500 dark:text-gray-400">{{ __('messages.select_event') }}</p>
        </div>

        <!-- Loading -->
        <div v-if="selectedEventId && loading" class="text-center py-16">
            <svg class="animate-spin mx-auto h-8 w-8 text-[#4E81FA]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>

        <!-- Dashboard -->
        <div v-if="stats && !loading">

            <!-- Overall progress -->
            <div class="bg-white dark:bg-[#1e1e1e] rounded-lg shadow-sm border border-gray-200 dark:border-[#2d2d30] p-6 mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('messages.checked_in') }}</h3>
                    <span class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        @{{ stats.total_checked_in }} / @{{ stats.total_sold }}
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-[#2d2d30] rounded-full h-4">
                    <div class="bg-[#4E81FA] h-4 rounded-full transition-all duration-500"
                        :style="{ width: progressPercent + '%' }"></div>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-[#9ca3af]">@{{ progressPercent }}%</p>
            </div>

            <!-- Per ticket type cards -->
            <div v-if="stats.tickets.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div v-for="ticket in stats.tickets" :key="ticket.type"
                    class="bg-white dark:bg-[#1e1e1e] rounded-lg shadow-sm border border-gray-200 dark:border-[#2d2d30] p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-gray-700 dark:text-[#d1d5db]">@{{ ticket.type }}</h4>
                        <span class="text-sm font-semibold text-gray-600 dark:text-[#9ca3af]">
                            @{{ ticket.checked_in }} / @{{ ticket.sold }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-[#2d2d30] rounded-full h-2.5">
                        <div class="bg-[#4E81FA] h-2.5 rounded-full transition-all duration-500"
                            :style="{ width: ticketPercent(ticket) + '%' }"></div>
                    </div>
                </div>
            </div>

            <!-- No check-ins yet -->
            <div v-if="stats.total_checked_in === 0 && stats.total_sold > 0" class="text-center py-8 mb-6">
                <p class="text-gray-500 dark:text-gray-400">{{ __('messages.no_checkins_yet') }}</p>
            </div>

            <!-- Recent activity -->
            <div v-if="stats.recent_checkins.length > 0"
                class="bg-white dark:bg-[#1e1e1e] rounded-lg shadow-sm border border-gray-200 dark:border-[#2d2d30] p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('messages.recent_checkins') }}</h3>
                <div class="divide-y divide-gray-100 dark:divide-[#2d2d30]">
                    <div v-for="(checkin, index) in stats.recent_checkins" :key="index"
                        class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div>
                            <p class="font-medium text-gray-800 dark:text-[#d1d5db]">@{{ checkin.name }}</p>
                            <p class="text-sm text-gray-500 dark:text-[#9ca3af]">@{{ checkin.ticket_type }}</p>
                        </div>
                        <span class="text-sm text-gray-400 dark:text-[#9ca3af] whitespace-nowrap ms-4">@{{ relativeTime(checkin.timestamp) }}</span>
                    </div>
                </div>
            </div>

            <!-- No sales -->
            <div v-if="stats.total_sold === 0" class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">{{ __('messages.no_sales_yet') }}</p>
            </div>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        const { createApp } = Vue

        createApp({
            data() {
                return {
                    events: @json($events),
                    selectedEventId: @json($selectedEventId ?? ''),
                    selectedDate: '{{ now()->format('Y-m-d') }}',
                    availableDates: [],
                    stats: null,
                    loading: false,
                    pollInterval: null,
                    dropdownOpen: false,
                }
            },
            computed: {
                selectedEvent() {
                    return this.events.find(e => e.id === this.selectedEventId) || null;
                },
                progressPercent() {
                    if (!this.stats || this.stats.total_sold === 0) return 0;
                    return Math.round((this.stats.total_checked_in / this.stats.total_sold) * 100);
                }
            },
            methods: {
                ticketPercent(ticket) {
                    if (ticket.sold === 0) return 0;
                    return Math.round((ticket.checked_in / ticket.sold) * 100);
                },
                relativeTime(timestamp) {
                    const now = Math.floor(Date.now() / 1000);
                    const diff = now - timestamp;
                    if (diff < 60) return @json(__('messages.just_now'));
                    const mins = Math.floor(diff / 60);
                    if (mins < 60) return mins + ' ' + @json(__('messages.minutes_ago'));
                    const hrs = Math.floor(mins / 60);
                    if (hrs < 24) return hrs + 'h ' + @json(__('messages.ago'));
                    return new Date(timestamp * 1000).toLocaleDateString();
                },
                toggleDropdown() {
                    this.dropdownOpen = !this.dropdownOpen;
                },
                closeDropdown() {
                    this.dropdownOpen = false;
                },
                onEventChange(eventId) {
                    this.selectedEventId = eventId;
                    this.closeDropdown();
                    this.stats = null;
                    this.availableDates = [];
                    if (this.selectedEventId) {
                        this.fetchStats();
                    }
                    this.startPolling();
                },
                fetchStats() {
                    if (!this.selectedEventId) return;

                    const isInitial = !this.stats;
                    if (isInitial) this.loading = true;

                    const url = '/checkin/' + this.selectedEventId + '/stats?date=' + encodeURIComponent(this.selectedDate);

                    fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => {
                        if (!r.ok) throw new Error();
                        return r.json();
                    })
                    .then(data => {
                        this.stats = data;
                        this.availableDates = data.available_dates || [];

                        // If selectedDate not in available dates, pick first
                        if (this.availableDates.length > 0 && !this.availableDates.includes(this.selectedDate)) {
                            // Pick today if available, otherwise first
                            const today = new Date().toISOString().slice(0, 10);
                            this.selectedDate = this.availableDates.includes(today) ? today : this.availableDates[0];
                            this.fetchStats();
                            return;
                        }

                        this.loading = false;
                    })
                    .catch(() => {
                        this.loading = false;
                    });
                },
                startPolling() {
                    this.stopPolling();
                    if (this.selectedEventId) {
                        this.pollInterval = setInterval(() => {
                            if (!document.hidden) {
                                this.fetchStats();
                            }
                        }, 10000);
                    }
                },
                stopPolling() {
                    if (this.pollInterval) {
                        clearInterval(this.pollInterval);
                        this.pollInterval = null;
                    }
                }
            },
            mounted() {
                if (this.selectedEventId) {
                    this.fetchStats();
                    this.startPolling();
                }

                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden && this.selectedEventId) {
                        this.fetchStats();
                    }
                });

                document.addEventListener('click', (e) => {
                    const el = document.getElementById('event-selector-dropdown');
                    if (el && !el.contains(e.target)) {
                        this.closeDropdown();
                    }
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        this.closeDropdown();
                    }
                });
            },
            beforeUnmount() {
                this.stopPolling();
            }
        }).mount('#app')
    </script>

</x-app-admin-layout>
