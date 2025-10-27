# Apple Wallet Pass Configuration Guide

This guide walks through preparing an Apple Wallet pass that you can issue from EventSchedule once you have enrolled in the Apple Developer Program. It highlights all assets, certificates, and configuration values you need so you can align the app's pass generation code with Apple's requirements.

## 1. Confirm Developer Program Assets

Before you start, sign in to the [Apple Developer account portal](https://developer.apple.com/account/resources/). Verify that you have access to:

1. **Team ID** – You will use this identifier in the pass JSON payload (`teamIdentifier`).
2. **Organization Name** – Appears as the pass issuer (`organizationName`).
3. **Pass Type Identifier** – Create a new Pass Type ID (e.g., `pass.com.yourcompany.eventschedule`). This string becomes the `passTypeIdentifier` in each pass.

Document these values so you can add them to the app's configuration later.

## 2. Create a Pass Type ID Certificate

Apple Wallet passes must be signed with a certificate generated for your Pass Type ID.

1. In Certificates, Identifiers & Profiles, open **Identifiers → Pass Type IDs** and select the identifier you just created.
2. Press **Create Certificate**, then download the Certificate Signing Request (CSR) utility from Apple or use Keychain Access to generate one (Keychain Access → Certificate Assistant → Request a Certificate From a Certificate Authority). Save the `.certSigningRequest` file locally.
3. Upload the CSR, download the resulting certificate (`pass.cer`), and double-click it to import it into your macOS keychain.
4. Export the certificate and the private key together as a `.p12` file (Keychain Access → right-click the certificate → **Export**). Apple will prompt for a password: remember it—you will add it to your environment secrets for EventSchedule.

> **Tip:** Convert the `.p12` file into a PEM pair if your deployment environment requires it:
>
> ```bash
> # OpenSSL 3 (macOS 12+/Homebrew) requires the `-legacy` flag for RC2-encrypted bundles
> openssl pkcs12 -in Certificates.p12 -out PassCertificate.pem -clcerts -nokeys -legacy
> openssl pkcs12 -in Certificates.p12 -out PassKey.pem -nocerts -nodes -legacy
> ```

## 3. Prepare Pass Assets

Every pass template includes at least:

- `pass.json` – The metadata payload.
- `icon.png` and `icon@2x.png` – Required icons (29×29 and 58×58).
- `logo.png` and `logo@2x.png` – Branding at 160×50 and 320×100.
- `background.png` / `background@2x.png` – Optional background image.
- `strip.png` / `strip@2x.png` – Optional strip image for event tickets.

Keep assets in a folder structure such as `resources/passes/<pass-type>/`. Retina variants must be exactly 2× the base size, PNG format, and RGB colorspace.

## 4. Draft `pass.json`

Create a `pass.json` file that matches Apple's schema. Below is a minimal example for an event ticket issued by EventSchedule:

```json
{
  "formatVersion": 1,
  "passTypeIdentifier": "pass.com.yourcompany.eventschedule",
  "serialNumber": "EVENT-2024-0001",
  "teamIdentifier": "ABCDE12345",
  "organizationName": "Your Company",
  "description": "EventSchedule Admission",
  "eventTicket": {
    "primaryFields": [
      {
        "key": "event",
        "label": "Event",
        "value": "Sample Conference"
      }
    ],
    "secondaryFields": [
      {
        "key": "date",
        "label": "Date",
        "value": "May 24, 2024"
      }
    ],
    "auxiliaryFields": [
      {
        "key": "venue",
        "label": "Venue",
        "value": "Main Hall"
      }
    ]
  },
  "barcode": {
    "format": "PKBarcodeFormatQR",
    "message": "https://eventschedule.example.com/passes/EVENT-2024-0001",
    "messageEncoding": "iso-8859-1"
  }
}
```

### Localization

If you need localized field labels or content, add `.lproj` folders (e.g., `en.lproj/pass.strings`) alongside your pass assets. Each localization folder contains `pass.strings` with key/value pairs for translated strings.

## 5. Create the Manifest and Signature

Apple Wallet requires a manifest file (`manifest.json`) and a detached signature (`signature`) generated with your certificate.

1. From inside the pass folder, compute SHA-1 hashes for each asset:

   ```bash
   /usr/bin/openssl sha1 *.png pass.json > hashes.txt
   ```

2. Convert `hashes.txt` into valid JSON, mapping filenames to hash strings, and save it as `manifest.json`.
3. Sign the manifest with your Pass Type ID certificate:

   ```bash
   /usr/bin/openssl smime -binary -sign \
     -certfile WWDR.pem \
     -signer PassCertificate.pem \
     -inkey PassKey.pem \
     -in manifest.json \
     -out signature \
     -outform DER
   ```

   - `WWDR.pem` is the Apple Worldwide Developer Relations intermediate certificate (download from Apple).
   - `PassCertificate.pem` and `PassKey.pem` come from converting your `.p12` export, or you can sign directly with the `.p12` using `-pkcs12` on newer OpenSSL versions.

4. Zip the pass contents (assets, `pass.json`, `manifest.json`, and `signature`) into a `.pkpass` archive:

   ```bash
   zip -r EventTicket.pkpass pass.json manifest.json signature icon.png icon@2x.png logo.png logo@2x.png
   ```

## 6. Integrate with EventSchedule

Update your application configuration with the certificate paths and passwords:

- Store the `.p12` (or PEM equivalents) in a secure location accessible to your app server.
- Expose the password via environment variable (e.g., `APPLE_WALLET_CERT_PASSWORD`).
- Configure the pass template path and default fields inside EventSchedule's settings or environment config so the pass generator can fill dynamic data like attendee name, event date, and QR payload.

When generating a pass, your code should:

1. Copy the template assets to a temporary working directory.
2. Inject dynamic values into `pass.json` (serial number, attendee info, event metadata).
3. Rebuild `manifest.json`, sign it, and create a fresh `.pkpass` bundle per request.
4. Return the `.pkpass` file with `Content-Type: application/vnd.apple.pkpass`.

## 7. Test on Devices

1. Use the Wallet simulator in Xcode (`Debug → Simulate iOS Device`) or email/Airdrop the `.pkpass` to your own device.
2. Ensure the pass opens without warnings. If you see a signature or format error, re-check the certificate password and the manifest hashes.
3. Confirm the barcode scans correctly at the venue or using an iOS Wallet pass tester.

## 8. Prepare for Production

- Monitor certificate expiration dates; Pass Type ID certificates expire after one year.
- If you rotate certificates, keep distributing the old certificate alongside the new one until all older passes are reissued.
- Store pass serial numbers so you can send push updates (using the Apple Wallet web service) when event details change.
- Configure your production domain to serve the Apple Wallet web service endpoints if you plan to support pass updates or voiding.

## Additional References

- [Apple Wallet Developer Guide](https://developer.apple.com/library/archive/documentation/UserExperience/Conceptual/PassKit_PG/Chapters/Introduction.html)
- [PassKit Package Format Reference](https://developer.apple.com/documentation/walletpasses/pass_package_format)
- [WWDR Intermediate Certificate](https://www.apple.com/certificateauthority/)

With these items in place, your EventSchedule deployment can generate and sign Wallet passes that satisfy Apple's validation rules.
