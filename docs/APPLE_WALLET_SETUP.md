# Apple Wallet setup

"Add to Apple Wallet" on a ticket: a signed `.pkpass` file the install builds when the buyer taps
the button. The user-facing copy of this page is `resources/views/marketing/docs/selfhost/apple-wallet.blade.php`
(`/docs/selfhost/apple-wallet`); keep the two in step.

## Off until configured

`App\Services\Wallet\AppleWalletService::isConfigured()` is false unless all of these hold, and then
nothing shows anywhere and `GET /ticket/wallet/apple/{event}/{secret}` answers 404:

- `APPLE_WALLET_PASS_TYPE_ID` and `APPLE_WALLET_TEAM_ID` are set (whitespace does not count);
- `APPLE_WALLET_CERTIFICATE` is an absolute path to, or the base64 of, the Pass Type ID certificate
  with its private key: a `.p12` with `APPLE_WALLET_CERTIFICATE_PASSWORD`, or a PEM file holding the
  certificate and the key (password for an encrypted key, otherwise empty), and the key matches;
- PHP has `ZipArchive` and `openssl_pkcs7_sign()`.

Nothing is ever sent to Apple. The pass names no `webServiceURL`, so it is a snapshot (no live
updates), like the Google pass.

## Setup

1. Apple Developer Program membership. In Certificates, Identifiers & Profiles add a **Pass Type
   ID** (for example `pass.com.example.tickets`); your Team ID is on the Membership page.
2. Create the Pass Type ID's certificate from a certificate signing request (Keychain Access:
   Certificate Assistant > Request a Certificate From a Certificate Authority), download it, open
   it so Keychain pairs it with its key, and export both as a `.p12` with a password.
3. If OpenSSL 3 cannot read the `.p12` (an older Keychain export), convert it once:
   `openssl pkcs12 -legacy -in pass.p12 -out pass.pem -nodes`, and use the PEM with an empty password.
4. Set the variables (`.env`, or the DigitalOcean app spec with the certificate base64-encoded as an
   encrypted variable).
5. Optional: Apple's "Add to Apple Wallet" badges are licensed per developer account, so the
   repository does not ship them. Put Apple's files in `public/images/wallet/apple/` as
   `{language}.svg` (pages) and `{language}.png` (email); English stands in for any missing
   language. Without them the button is plain text. The folder is in `.gitignore` and never part
   of the release zip, so an update keeps the files; in Docker, mount it as a volume. Never commit
   them to this repository.

Apple's WWDR G4 intermediate (`resources/wallet/AppleWWDRCAG4.pem`, valid to 2030-12-10) is
shipped; `APPLE_WALLET_WWDR_CERTIFICATE` overrides it when Apple issues a newer one.

## How the pass is built

`pass.json` (eventTicket style: event name; date and time with `ignoresTimeZone`, so it shows the
schedule's clock; venue; ticket type, attendee, guests; back: address, seats, ticket number, ticket
notes, link to the ticket page), `relevantDate` at the start, `expirationDate` three hours after
the end (as Google's validTimeInterval), the schedule's colour with a readable foreground, icon and
logo from the schedule's profile picture (the app icon otherwise), all hashed into
`manifest.json`, which is signed with `openssl_pkcs7_sign()` (detached, binary, WWDR as an extra
certificate) and the DER signature taken from the S/MIME output. `openssl_cms_sign()` with the
WWDR as an extra certificate wrote a signature that did not verify (PHP 8.3, OpenSSL 3.6), which is
why it is not used.

The barcode is byte-identical to the ticket page's QR (`TicketController::qrCode()`), so the door
scanner reads a pass as it reads the page. Eligibility (`WalletPasses::eligible()`) is shared with
Google Wallet.
