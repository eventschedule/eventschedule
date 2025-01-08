<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Code Scanner</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      padding: 20px;
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
</head>
<body>
  <div id="reader"></div>
  <div id="result">Scan a QR code to see the link here.</div>

  <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
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
      { fps: 10, qrbox: 250 },
      /* verbose= */ false
    );
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
  </script>
</body>
</html>
