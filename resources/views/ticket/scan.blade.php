<x-app-admin-layout>

    <x-slot name="head">

        <script src="{{ asset('js/vue.global.prod.js') }}"></script>
        <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>

        <style>
            #reader {
                border: none !important;
                box-shadow: none !important;
            }
            #reader video {
                border-radius: 1rem !important;
            }
            #html5-qrcode-button-camera-permission {
                @apply bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors;
            }
            #html5-qrcode-button-camera-start, 
            #html5-qrcode-button-camera-stop {
                @apply bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors mt-2;
            }
            .html5-qrcode-element {
                @apply mb-4;
            }
        </style>
    
    </x-slot>

    <div id="app" class="max-w-2xl mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">{{ __('messages.scan_ticket') }}</h2>
            
            <div id="reader" class="max-w-md mx-auto"></div>
            
            <div v-if="scanResult" class="mt-6 text-center">
                <div :class="[
                    'border rounded-lg p-4',
                    errorMessage ? 'bg-red-50 border-red-200' : 
                    hasUsedSeats ? 'bg-orange-50 border-orange-200' : 'bg-green-50 border-green-200'
                ]">
                    <div class="flex flex-col items-center justify-center">
                        <!-- Success Icon -->
                        <svg v-if="!errorMessage && !hasUsedSeats" class="w-12 h-12 text-green-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4C12.76,4 13.5,4.11 14.2,4.31L15.77,2.74C14.61,2.26 13.34,2 12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12M7.91,10.08L6.5,11.5L11,16L21,6L19.59,4.58L11,13.17L7.91,10.08Z"></path>
                        </svg>
                        <!-- Warning Icon -->
                        <svg v-if="hasUsedSeats && !errorMessage" class="w-12 h-12 text-orange-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <!-- Error Icon -->
                        <svg v-if="errorMessage" class="w-12 h-12 text-red-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <p :class="[
                            'font-medium text-center',
                            errorMessage ? 'text-red-800' :
                            hasUsedSeats ? 'text-orange-800' : 'text-green-800'
                        ]">
                            <template v-if="errorMessage">@{{ errorMessage }}</template>
                            <template v-else>{{ __('messages.ticket_scanned_successfully') }}</template>
                        </p>
                    </div>
                    
                    <div v-if="eventDetails && !errorMessage" class="mt-4 text-left">
                        <h3 class="text-xl font-semibold text-gray-800">@{{ eventDetails.event }}</h3>
                        <p class="text-gray-600 mt-1">@{{ eventDetails.date }}</p>
                        
                        <div class="mt-6 pb-2 text-gray-700">
                            <p><span class="font-medium">{{ __('messages.attendee') }}:</span> @{{ eventDetails.attendee }}</p>
                        </div>
                        
                        <div class="mt-4">
                            <div v-for="ticket in eventDetails.tickets" :key="ticket.type" class="mb-3">
                                <h4 class="font-medium text-gray-700">@{{ ticket.type }} {{ __('messages.ticket') }}</h4>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <div v-for="(status, seat) in ticket.seats" 
                                         :key="seat"
                                         :class="[
                                             'w-12 h-12 rounded-lg flex items-center justify-center font-medium border',
                                             status ? 'bg-orange-100 text-orange-700 border-orange-200' : 'bg-green-100 text-green-700 border-green-200'
                                         ]">
                                        @{{ seat }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-if="hasUsedSeats" class="text-red-500 font-medium text-center py-2">{{ __('messages.warning_ticket_used') }}</p>
                    </div>

                </div>

                <button @click="startNewScan" class="mt-6 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
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
                    if (!this.eventDetails) return false;
                    return this.eventDetails.tickets.some(ticket => 
                        Object.values(ticket.seats).some(seatValue => seatValue > 0)
                    );
                }
            },
            methods: {
                onScanSuccess(decodedText, decodedResult) {
                    this.qrScanner.clear();
                    this.scanResult = decodedText;

                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch(decodedText, {
                        method: 'POST',
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
                        this.errorMessage = error.message;
                    });
                },
                onScanFailure(error) {
                    console.warn(`Scan failed: ${error}`);
                    this.errorMessage = error.message;
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
                        this.qrScanner.render(this.onScanSuccess, this.onScanFailure);
                    @endif
                }
            },
            mounted() {
                this.initializeScanner();
            }
        }).mount('#app')
    </script>

</x-app-admin-layout>