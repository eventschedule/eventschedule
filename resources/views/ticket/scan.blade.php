<x-app-admin-layout>

    <x-slot name="head">

        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

        <style>
            #scanner {
                text-align: center;
                margin-top: 40px;
            }

            #reader {
                width: 300px;
                margin: auto;
            }

            #result {
                margin-top: 20px;
                font-size: 18px;
            }
        </style>
    
    </x-slot>

    <div id="scanner">
        <div id="reader"></div>
        <div id="result"></div>
    </div>

    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // Display the result
            document.getElementById('result').innerHTML = `
            <p>Scanned Link: <a href="${decodedText}" target="_blank">${decodedText}</a></p>
            `;
            // Stop scanning after a successful scan
            html5QrcodeScanner.clear();
        }

        function onScanFailure(error) {
            // Log scan errors, if any
            console.warn(`Scan failed: ${error}`);
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { 
                fps: 10, 
                qrbox: 250, 
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA], 
                rememberLastUsedCamera: true,
                showTorchButtonIfSupported: true,
            },
            /* verbose= */ false
        );

        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

    </script>

</x-app-admin-layout>