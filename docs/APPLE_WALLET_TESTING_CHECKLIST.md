# Apple Wallet Pass Regression Checklist

Use this checklist after deploying a build that touches Apple Wallet signing or ticket delivery. The steps verify the full pass lifecycle, from generation to on-device installation.

## 1. Pre-flight sanity checks

1. **Confirm configuration**
   - `php artisan tinker --execute="dump(config('wallet.apple'))"` to ensure the pass type identifier, team identifier, certificate paths, and password values are present.
   - On the server, verify the referenced certificate files exist and have the correct permissions (`ls -l /path/to/certs`).
2. **Clear cache** (if you changed configuration):
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

## 2. Generate a fresh pass

1. Purchase or comp a ticket in the environment you just updated.
2. Navigate to the ticket detail page and tap **Add to Apple Wallet**. Confirm the browser starts a download with the `application/vnd.apple.pkpass` content type.

## 3. Inspect the `.pkpass` bundle

1. Download the pass file directly:
   ```bash
   curl -o test.pkpass "https://<your-domain>/ticket/wallet/apple/{event_id}/{secret}"
   ```
2. Unzip and list its contents:
   ```bash
   unzip -l test.pkpass
   ```
   Ensure you see:
   - `pass.json`
   - `manifest.json`
   - `signature`
   - Required image assets (icon/logo pairs, strip/background if configured)
3. Inspect `pass.json` to confirm the event name, attendee name, serial number, and barcodes look correct:
   ```bash
   unzip -p test.pkpass pass.json | jq
   ```

## 4. Validate the manifest signature

1. Extract the signature and manifest:
   ```bash
   unzip -p test.pkpass signature > signature
   unzip -p test.pkpass manifest.json > manifest.json
   ```
2. Verify the detached signature with OpenSSL (requires the WWDR intermediate and Apple Root CA):
   ```bash
   openssl smime -verify -inform DER \
     -in signature \
     -content manifest.json \
     -certfile /path/to/WWDR.pem \
     -CAfile /path/to/AppleRootCA.pem \
     -nointern -noverify > /dev/null
   ```
   A zero exit status confirms the signature was generated with the expected certificate chain.

## 5. Device installation smoke test

1. Send the `test.pkpass` file to an iOS device (AirDrop, email, or direct download).
2. Add it to Wallet and confirm:
   - The pass shows without the “Cannot Add Pass” error.
   - The barcode renders and matches the ticket secret/URL.
   - Relevant fields (event name, dates, venue, seat info) are populated.

## 6. Event updates (optional)

If you rely on Wallet push updates, trigger an event edit that should update the pass and confirm the device receives the change. Check the server logs for any pass update errors.

## 7. Clean up

Remove temporary files created during inspection:
```bash
rm -f test.pkpass signature manifest.json
```

Document any anomalies you encounter and roll back if the pass fails signature verification or cannot be installed on a device.
