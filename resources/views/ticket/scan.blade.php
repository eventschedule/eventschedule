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
                            {{ __('messages.season_pass') }}
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

                        <!-- Pass: check-in context -->
                        <template v-if="isPass">
                            <div class="mt-2 text-gray-700 dark:text-[#d1d5db] space-y-1">
                                <p v-if="passStatus === 'already_today' && eventDetails.checked_in_at">{{ __('messages.pass_entered_at') }} @{{ eventDetails.checked_in_at }}</p>
                                <p v-if="passStatus === 'too_early' && eventDetails.check_in_opens">{{ __('messages.pass_check_in_opens_at') }} @{{ eventDetails.check_in_opens }}</p>
                                <p class="text-sm text-gray-500 dark:text-[#9ca3af]">{{ __('messages.pass_checkins_label') }}: @{{ eventDetails.pass_checkin_count }}</p>
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
                    errorMessage: null
                }
            },
            computed: {
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
                    const map = {
                        valid: @json(__('messages.pass_welcome_checked_in')),
                        already_today: @json(__('messages.pass_already_checked_in_today')),
                        no_event_today: @json(__('messages.pass_no_event_today')),
                        too_early: @json(__('messages.pass_valid')),
                        event_over: @json(__('messages.pass_event_over')),
                    };
                    return map[this.passStatus] || @json(__('messages.ticket_scanned_successfully'));
                }
            },
            methods: {
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
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch(scanUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        }
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
                this.initializeScanner();
            }
        }).mount('#app')
    </script>

</x-app-admin-layout>