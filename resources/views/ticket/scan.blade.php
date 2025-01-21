<x-app-admin-layout>

    <x-slot name="head">

        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
        <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

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
                    <p class="text-green-800 font-medium">Successfully scanned!</p>
                    <p class="text-sm text-green-600 mt-1">
                        <span class="font-medium">Link:</span> 
                        <a :href="scanResult" class="underline hover:text-green-700" target="_blank">
                            @{{ scanResult }}
                        </a>
                    </p>
                    <button @click="startNewScan" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                        Scan Again
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue

        createApp({
            data() {
                return {
                    html5QrcodeScanner: null,
                    scanResult: null
                }
            },
            methods: {
                onScanSuccess(decodedText, decodedResult) {
                    this.html5QrcodeScanner.clear();
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
                        console.log('Success:', data);
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
                    this.html5QrcodeScanner = new Html5QrcodeScanner(
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
                    this.html5QrcodeScanner.render(this.onScanSuccess, this.onScanFailure);
                }
            },
            mounted() {
                this.initializeScanner();
            }
        }).mount('#app')
    </script>

</x-app-admin-layout>