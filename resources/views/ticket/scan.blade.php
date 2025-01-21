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
            html5QrcodeScanner.clear();

            document.getElementById('result').innerHTML = `
            <p>Scanned Link: <a href="${decodedText}" target="_blank">${decodedText}</a></p>
            `;

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