<x-app-admin-layout>

    <x-slot name="head">

        <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

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
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">{{ __('messages.scan_qr_code') }}</h2>
            
            <div id="reader" class="max-w-md mx-auto"></div>
            
            <div v-if="scanResult" class="mt-6 text-center">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-green-800 font-medium">Ticket Scanned Successfully!</p>
                    
                    <div v-if="eventDetails" class="mt-4 text-left">
                        <h3 class="text-xl font-semibold text-gray-800">@{{ eventDetails.event }}</h3>
                        <p class="text-gray-600 mt-1">@{{ eventDetails.date }}</p>
                        
                        <div class="mt-4">
                            <div v-for="ticket in eventDetails.tickets" :key="ticket.type" class="mb-3">
                                <h4 class="font-medium text-gray-700">@{{ ticket.type }} Ticket</h4>
                                <div class="text-sm text-gray-600">
                                    Seats: @{{ Object.keys(ticket.seats).join(', ') }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <button @click="startNewScan" class="mt-6 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                        {{ __('messages.scan_another_ticket') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue

        createApp({
            data() {
                return {
                    qrScanner: null,
                    scanResult: null,
                    eventDetails: null
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
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            this.scanResult = null;
                            alert(data.error);
                        } else {
                            this.eventDetails = data;
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                    });
                },
                onScanFailure(error) {
                    console.warn(`Scan failed: ${error}`);
                },
                startNewScan() {
                    this.scanResult = null;
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

                    @if (config('app.env') == 'local')
                        this.scanResult = true;
                        this.eventDetails = {"event":"Test Schedule","date":"Saturday, January 25th \u2022 8:00 PM","tickets":[{"type":"VIP","seats":{"1":1737465972,"2":1737465972}}]};
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