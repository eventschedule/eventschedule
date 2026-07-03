<x-app-admin-layout>

    <x-slot name="head">

        <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
        <script src="{{ asset('js/html5-qrcode.min.js') }}" {!! nonce_attr() !!}></script>

        <style {!! nonce_attr() !!}>
            #reader {
                border: none !important;
                box-shadow: none !important;
            }
            #reader video {
                border-radius: 1rem !important;
            }
            #html5-qrcode-button-camera-permission {
                background-color: var(--brand-button-bg); color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; transition: background-color 0.15s;
            }
            #html5-qrcode-button-camera-permission:hover {
                background-color: var(--brand-button-bg-hover);
            }
            #html5-qrcode-button-camera-start,
            #html5-qrcode-button-camera-stop {
                @apply bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors mt-2;
            }
            .dark #html5-qrcode-button-camera-start,
            .dark #html5-qrcode-button-camera-stop {
                background-color: #2d2d30;
                color: #d1d5db;
            }
            .dark #html5-qrcode-button-camera-start:hover,
            .dark #html5-qrcode-button-camera-stop:hover {
                background-color: #3d3d40;
            }
            .html5-qrcode-element {
                @apply mb-4;
            }
        </style>
    
    </x-slot>

    <div id="app" class="max-w-2xl mx-auto px-4">
        <div class="bg-white dark:bg-[#1e1e1e] rounded-xl shadow-lg dark:shadow-none dark:border dark:border-[#2d2d30] p-6">
            <h2 class="text-2xl font-bold text-center text-gray-800 dark:text-gray-100 mb-6">{{ __('messages.scan_ticket') }}</h2>

            <!-- Event context: which event the operator is scanning at (governs subscription redemption) -->
            @if (!empty($events) && count($events) > 0)
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('messages.scanning_at_event') }}</label>
                <x-event-selector />
                <p v-if="selectedEvent" class="mt-1.5 text-xs text-gray-500 dark:text-[#9ca3af]">
                    {{ __('messages.scanning_at') }}: <span class="font-medium text-gray-700 dark:text-gray-300">@{{ selectedEvent.name }}</span><template v-if="selectedEvent.starts_at"> &middot; @{{ selectedEvent.starts_at }}</template>
                </p>
            </div>
            @endif

            <div id="reader" class="max-w-md mx-auto"></div>
            
            <div v-if="scanResult" class="mt-6 text-center">
                <div :class="['border rounded-lg p-4', toneBoxClass]">
                    <div class="flex flex-col items-center justify-center">
                        <!-- Success Icon -->
                        <svg v-if="resultTone === 'success'" class="w-12 h-12 text-green-600 dark:text-green-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4C12.76,4 13.5,4.11 14.2,4.31L15.77,2.74C14.61,2.26 13.34,2 12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12M7.91,10.08L6.5,11.5L11,16L21,6L19.59,4.58L11,13.17L7.91,10.08Z"></path>
                        </svg>
                        <!-- Warning Icon -->
                        <svg v-else-if="resultTone === 'warning'" class="w-12 h-12 text-orange-600 dark:text-orange-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <!-- Info Icon -->
                        <svg v-else-if="resultTone === 'info'" class="w-12 h-12 text-blue-600 dark:text-blue-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <!-- Error Icon -->
                        <svg v-else class="w-12 h-12 text-red-600 dark:text-red-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>

                        <!-- Season Pass badge -->
                        <span v-if="isPass" class="mb-2 inline-flex items-center gap-1 rounded-full bg-blue-50 dark:bg-blue-900/30 px-3 py-1 text-xs font-semibold text-[var(--brand-blue)]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5h14a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H5a2 2 0 01-2-2v-3a2 2 0 000-4V7a2 2 0 012-2z"/></svg>
                            @{{ passBadgeLabel }}
                        </span>

                        <p :class="['font-medium text-center', toneTextClass]">
                            <template v-if="errorMessage">@{{ errorMessage }}</template>
                            <template v-else-if="isPass">@{{ passTitle }}</template>
                            <template v-else>{{ __('messages.ticket_scanned_successfully') }}</template>
                        </p>
                    </div>

                    <div v-if="eventDetails && !errorMessage" class="mt-4 text-start">
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">@{{ eventDetails.event }}</h3>
                        <p v-if="eventDetails.date" class="text-gray-600 dark:text-[#9ca3af] mt-1">
                            <span v-if="isPass && passStatus === 'no_event_today'" class="font-medium">{{ __('messages.pass_next_event') }}: </span>@{{ eventDetails.date }}
                        </p>

                        <div class="mt-6 pb-2 text-gray-700 dark:text-[#d1d5db]">
                            <p><span class="font-medium">{{ __('messages.attendee') }}:</span> @{{ eventDetails.attendee }}</p>
                        </div>

                        <!-- Pass / subscription: redemption context -->
                        <template v-if="isPass">
                            <div class="mt-2 text-gray-700 dark:text-[#d1d5db] space-y-1">
                                <p v-if="passStatus === 'already_today' && eventDetails.checked_in_at">{{ __('messages.pass_entered_at') }} @{{ eventDetails.checked_in_at }}</p>
                                <p v-if="passStatus === 'too_early' && eventDetails.check_in_opens">{{ __('messages.pass_check_in_opens_at') }} @{{ eventDetails.check_in_opens }}</p>
                                <p v-if="passUsesLabel" class="text-sm font-medium">@{{ passUsesLabel }}</p>
                                <template v-if="admitsPerEvent > 1 && (passStatus === 'valid' || passStatus === 'already_today')">
                                    <p v-if="admitsLabel" class="text-sm font-semibold">@{{ admitsLabel }}</p>
                                    <p v-if="passStatus === 'valid' && admitsRemaining > 0" class="text-sm text-green-700 dark:text-green-300">{{ __('messages.pass_scan_again_for_guest') }}</p>
                                    <p v-else-if="passStatus === 'valid' && admitsRemaining === 0" class="text-sm text-green-700 dark:text-green-300">{{ __('messages.pass_all_guests_admitted') }}</p>
                                </template>
                                <p v-if="eventDetails.valid_until" class="text-sm text-gray-500 dark:text-[#9ca3af]">{{ __('messages.pass_valid_until') }} @{{ eventDetails.valid_until }}</p>
                            </div>
                        </template>

                        <!-- Standard ticket: seat grid -->
                        <template v-else>
                            <div class="mt-4">
                                <div v-for="ticket in eventDetails.tickets" :key="ticket.type" class="mb-3">
                                    <h4 class="font-medium text-gray-700 dark:text-[#d1d5db]">@{{ ticket.type }} {{ __('messages.ticket') }}</h4>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <div v-for="(status, seat) in ticket.seats"
                                             :key="seat"
                                             :class="[
                                                 'w-12 h-12 rounded-lg flex items-center justify-center font-medium border',
                                                 status ? 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-300 dark:border-orange-800' : 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800'
                                             ]">
                                            @{{ seat }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-if="hasUsedSeats" class="text-red-500 dark:text-red-400 font-medium text-center py-2">{{ __('messages.warning_ticket_used') }}</p>
                        </template>
                    </div>

                </div>

                <button v-if="isPass && passStatus === 'valid' && admitsRemaining > 0" @click="admitNextGuest"
                        class="mt-6 me-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        {{ __('messages.pass_admit_guest') }} (@{{ admitsRemaining }})
                </button>
                <button @click="startNewScan" class="mt-6 bg-[var(--brand-button-bg)] text-white px-4 py-2 rounded-lg hover:bg-[var(--brand-button-bg-hover)] transition-colors">
                        {{ __('messages.scan_another_ticket') }}
                </button>
            </div>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        const { createApp } = Vue

        createApp({
            data() {
                return {
                    qrScanner: null,
                    scanResult: null,
                    eventDetails: null,
                    errorMessage: null,
                    lastScanUrl: null,
                    events: @json($events ?? []),
                    selectedEventId: @json($selectedEventId ?? ''),
                    dropdownOpen: false,
                }
            },
            computed: {
                selectedEvent() {
                    return this.events.find(e => e.id === this.selectedEventId) || null;
                },
                hasUsedSeats() {
                    if (!this.eventDetails || this.eventDetails.is_pass) return false;
                    return (this.eventDetails.tickets || []).some(ticket =>
                        Object.values(ticket.seats).some(seatValue => seatValue > 0)
                    );
                },
                isPass() {
                    return !!(this.eventDetails && this.eventDetails.is_pass);
                },
                passStatus() {
                    return this.isPass ? this.eventDetails.pass_status : null;
                },
                resultTone() {
                    if (this.errorMessage) return 'error';
                    if (this.isPass) {
                        if (this.passStatus === 'valid') return 'success';
                        if (this.passStatus === 'already_today') return 'warning';
                        if (['limit_reached', 'expired', 'not_covered'].includes(this.passStatus)) return 'error';
                        return 'info';
                    }
                    return this.hasUsedSeats ? 'warning' : 'success';
                },
                toneBoxClass() {
                    return {
                        error: 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800',
                        warning: 'bg-orange-50 border-orange-200 dark:bg-orange-900/20 dark:border-orange-800',
                        info: 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800',
                        success: 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800',
                    }[this.resultTone];
                },
                toneTextClass() {
                    return {
                        error: 'text-red-800 dark:text-red-300',
                        warning: 'text-orange-800 dark:text-orange-300',
                        info: 'text-blue-800 dark:text-blue-300',
                        success: 'text-green-800 dark:text-green-300',
                    }[this.resultTone];
                },
                passTitle() {
                    // A fully-admitted multi-admit pass reads "all admissions used",
                    // not the single-admit "already checked in today".
                    if (this.passStatus === 'already_today' && this.admitsPerEvent > 1) {
                        return @json(__('messages.pass_all_admits_used')).replace(':count', this.admitsPerEvent);
                    }
                    const map = {
                        valid: @json(__('messages.pass_welcome_checked_in')),
                        already_today: @json(__('messages.pass_already_checked_in_today')),
                        no_event_today: @json(__('messages.pass_no_event_today')),
                        too_early: @json(__('messages.pass_valid')),
                        event_over: @json(__('messages.pass_event_over')),
                        limit_reached: @json(__('messages.pass_limit_reached')),
                        expired: @json(__('messages.pass_expired')),
                        not_covered: @json(__('messages.pass_not_covered')),
                    };
                    return map[this.passStatus] || @json(__('messages.ticket_scanned_successfully'));
                },
                passBadgeLabel() {
                    const type = this.eventDetails && this.eventDetails.pass_usage_type;
                    if (type === 'per_occurrence') return @json(__('messages.season_pass'));
                    return @json(__('messages.subscription'));
                },
                passUsesLabel() {
                    if (!this.isPass || !this.eventDetails) return null;
                    const type = this.eventDetails.pass_usage_type;
                    const count = this.eventDetails.pass_usage_count;
                    const max = this.eventDetails.pass_max_uses;
                    if (type === 'total' && max) {
                        return @json(__('messages.pass_visit_x_of_n')).replace(':used', count).replace(':total', max);
                    }
                    if (type === 'unlimited' || type === 'per_occurrence') {
                        return @json(__('messages.pass_unlimited_visits'));
                    }
                    if (type === 'per_event') {
                        return @json(__('messages.pass_one_per_event'));
                    }
                    return null;
                },
                admitsPerEvent() {
                    return (this.eventDetails && this.eventDetails.admits_per_event) || 1;
                },
                admitsUsed() {
                    return (this.eventDetails && this.eventDetails.admits_used) || 0;
                },
                admitsRemaining() {
                    return Math.max(0, this.admitsPerEvent - this.admitsUsed);
                },
                admitsLabel() {
                    if (this.admitsPerEvent <= 1) return null;
                    // Clamp to the limit so a lowered admits cap can't read "3 of 2".
                    const used = Math.min(this.admitsPerEvent, this.admitsUsed || this.admitsPerEvent);
                    return @json(__('messages.pass_admitted_x_of_n')).replace(':used', used).replace(':total', this.admitsPerEvent);
                }
            },
            methods: {
                toggleDropdown() {
                    this.dropdownOpen = !this.dropdownOpen;
                },
                closeDropdown() {
                    this.dropdownOpen = false;
                },
                onEventChange(eventId) {
                    this.selectedEventId = eventId;
                    this.closeDropdown();
                    try { localStorage.setItem('scan_event_id', eventId); } catch (e) {}
                },
                onScanSuccess(decodedText, decodedResult) {
                    this.qrScanner.clear();
                    this.scanResult = decodedText;
                    this.errorMessage = null;

                    let url;
                    try {
                        url = new URL(decodedText);
                    } catch (e) {
                        this.errorMessage = @json(__('messages.invalid_ticket_qr_code'));
                        return;
                    }
                    if (!/^\/ticket\/view\/[^/]+\/[^/]+/.test(url.pathname)) {
                        this.errorMessage = @json(__('messages.invalid_ticket_qr_code'));
                        return;
                    }

                    const scanUrl = window.location.origin + url.pathname;
                    this.lastScanUrl = scanUrl;
                    this.postScan(scanUrl);
                },
                postScan(scanUrl) {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch(scanUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ scan_event_id: this.selectedEventId || null })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP Error: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            this.errorMessage = data.error;
                        } else {
                            this.eventDetails = data;
                        }
                    })
                    .catch((error) => {
                        this.errorMessage = error.message || @json(__('messages.an_error_occurred'));
                    });
                },
                // Admit the next person (holder's guest) on a multi-admit pass
                // without re-aiming the camera at the same QR. Reuses the scan
                // endpoint, which increments the admits counter server-side.
                admitNextGuest() {
                    if (this.lastScanUrl) {
                        this.postScan(this.lastScanUrl);
                    }
                },
                onScanFailure(error) {
                    console.warn(`Scan failed: ${error}`);
                },
                startNewScan() {
                    this.scanResult = null;
                    this.eventDetails = null;
                    this.errorMessage = null;
                    this.initializeScanner();
                },
                initializeScanner() {
                    this.qrScanner = new Html5QrcodeScanner(
                        "reader",
                        { 
                            fps: 10, 
                            qrbox: { width: 250, height: 250 },
                            formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                            supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA], 
                            rememberLastUsedCamera: true,
                            showTorchButtonIfSupported: true,
                        },
                        false
                    );

                    @if (false && config('app.env') == 'local')
                        this.scanResult = true;
                        this.eventDetails = {"attendee":"Test Attendee","event":"Test Schedule","date":"Saturday, January 25th \u2022 8:00 PM","tickets":[{"type":"VIP","seats":{"1":null,"2":null}}]};
                    @else
                        this.qrScanner.render(this.onScanSuccess, this.onScanFailure).catch((error) => {
                            console.warn('QR scanner render failed:', error);
                        });
                    @endif
                }
            },
            mounted() {
                try {
                    const saved = localStorage.getItem('scan_event_id');
                    if (saved && this.events.some(e => e.id === saved)) {
                        this.selectedEventId = saved;
                    }
                } catch (e) {}

                document.addEventListener('click', (e) => {
                    const el = document.getElementById('event-selector-dropdown');
                    if (el && !el.contains(e.target)) {
                        this.closeDropdown();
                    }
                });
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') this.closeDropdown();
                });

                this.initializeScanner();
            }
        }).mount('#app')
    </script>

</x-app-admin-layout>