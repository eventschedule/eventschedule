# Google Wallet Setup

> The published version of this guide is at `/docs/selfhost/google-wallet`
> (`resources/views/marketing/docs/selfhost/google-wallet.blade.php`). Keep the two in step.

Lets ticket buyers, and guests who register for a free event, save their ticket into Google Wallet
with an "Add to Google Wallet" badge: in its own row along the bottom of the ticket page, on the
multi-event order page (once for each event) and in the confirmation email.

The pass carries the same QR code the ticket page shows, so it scans through the existing door
scanner with no change at all, and it works offline once saved.

The feature is **off until it is configured**. With `GOOGLE_WALLET_ISSUER_ID` and
`GOOGLE_WALLET_SERVICE_ACCOUNT` unset, no button renders anywhere and the app never contacts
Google. It is free on every plan.

## Prerequisites

- A Google account you are happy to run the issuer under. Passes are issued in this account's name,
  so on a hosted platform it belongs to the operator, not to individual schedule owners.
- A Google Cloud project.

## Setup Instructions

### 1. Create a Google Wallet issuer account

1. Go to the [Google Pay and Wallet Console](https://pay.google.com/business/console).
2. Sign up for the Google Wallet API and accept the terms.
3. Copy your **Issuer ID**, a long number. This is `GOOGLE_WALLET_ISSUER_ID`.

### 2. Enable the API and create a service account

1. In the [Google Cloud Console](https://console.cloud.google.com), select or create a project.
2. Enable the **Google Wallet API** for it.
3. Create a **service account** and generate a **JSON key** for it. Download the key file.

### 3. Give the service account access to the issuer

1. Back in the Google Pay and Wallet Console, open **Users**.
2. Invite the service account's email address (`...@....iam.gserviceaccount.com`).
3. Set the access level to **Developer**.

Skipping this step is the usual cause of a button that appears but never produces a pass: the
credentials are valid, they just cannot write to your issuer.

### 4. Configure the app

```env
GOOGLE_WALLET_ISSUER_ID=3388000000012345678
GOOGLE_WALLET_SERVICE_ACCOUNT=/var/www/secrets/wallet-service-account.json
GOOGLE_WALLET_ID_PREFIX=es
```

`GOOGLE_WALLET_SERVICE_ACCOUNT` accepts **either** an absolute path to the JSON key file **or** the
base64-encoded contents of that file:

```bash
base64 -i wallet-service-account.json | tr -d '\n'
```

The second form is for hosts with no writable file mount, such as DigitalOcean App Platform, where
production config is the app spec rather than a `.env` file.

`GOOGLE_WALLET_ID_PREFIX` namespaces the pass classes and objects this installation creates. It
defaults to `es`, and only letters, digits, dots, underscores and hyphens are kept.
**Google cannot delete a class or an object once it exists**, only expire it, so two installations
sharing one issuer account and one prefix would collide permanently. Give a staging installation
its own prefix.

### 5. Request publishing access

A new issuer account starts in **demo mode**. Only Google accounts you have registered as test
accounts in the Google Pay and Wallet Console can save a pass, and passes carry a demo banner.
Everyone else sees the button and gets nothing, which looks like a broken feature rather than a
pending approval.

Request publishing access from the console when you are ready to go live.

## What is sent to Google

When a buyer taps the button, and only then, the app sends Google:

- the attendee name, the event name, the venue name and address, and the start time
- the ticket type, any seat labels, and the number of guests when a ticket admits more than one
- the event's ticket notes, truncated to 200 characters
- the schedule's name and accent colour, the event's public URL and, when `APP_URL` is publicly
  reachable over https, the URLs of the schedule's profile image (the Event Schedule logo when it
  has none) and the event's image
- the venue's coordinates, when it has them, so Google can notify the attendee when they come
  within its own radius of the venue. Coordinates come from geocoding, which only runs when
  `BACKEND_GOOGLE_KEY` is set, so a complete address is not enough on its own. Coordinates are
  looked up when a venue is saved, so after adding the key, re-save the venues you already have
- the ticket URL, which contains that sale's secret

The ticket URL has to be there: it is what the pass's QR code encodes, and the door scanner reads
that exact URL. Nothing is sent for a buyer who never taps the button.

## How it works

- **The class** describes one occurrence: branding, venue, date and time. It is created once over
  the Wallet REST API and then cached, because a JWT carrying both the class and the object exceeds
  the 1800 characters Google documents as the safe length of an encoded JWT.
- **The object** is the individual ticket. It rides inside a signed JWT in the save link, so there
  is no per-sale API call. Note that Google does create the object on its side once the buyer
  saves, and it cannot be deleted afterwards, only expired.
- **One pass per event.** An order that spans several events gets a pass for each. A season pass
  is a single pass for its whole run rather than one per date, so its class carries no date.
- **The pass is a snapshot.** Neither half is patched once written. Cancelling or fully refunding
  an order does not remove a pass from someone's phone, but the QR stops working: the door scanner
  checks the order's live status and refuses a cancelled or fully refunded ticket exactly as the
  ticket page does. A partial refund leaves the order paid, so its pass keeps scanning. Renaming or
  rescheduling an event after the first buyer has tapped leaves the original details on every pass
  saved from then on, because the class is written once per occurrence, and the same goes for
  branding: a new profile image or accent colour never reaches a class that already exists.
- Class and object IDs are derived from the event and sale IDs, so re-saving the same ticket
  returns the original pass rather than creating a second one. It is not refreshed.
- **A pass expires** three hours after the event ends (an event shorter than an hour, or with no
  length set, counts as an hour long), and Google moves it out of the main list. Two cases never
  expire: a season pass whose ticket leaves Valid for (days) blank, and an all-day event or a
  recurring event with no start time.
- **Very long passes drop optional details.** Google caps the save link, so when a pass would not
  fit, the extra rows go first (the ticket notes and the guest count), then the seat list, then the
  ticket type. The QR code and the attendee name are always kept. The event name, venue and images
  never count against the cap, because they live on the class rather than in the link.

## Troubleshooting

**The button does not appear.** The feature is unconfigured, or the ticket is not eligible. A
badge is only offered for an order that is complete (paid, or a free registration), not deleted,
not on an installment plan that has fallen behind, whose event is not cancelled, and which is not
an appointment booking.

**The button appears but the buyer lands back on their ticket with an error.** The error reads
"The wallet pass could not be created. Please try again." The pass could not be built, or the call
to Google failed. Check `storage/logs` for `Google Wallet token exchange failed` (bad or revoked key),
`Google Wallet class lookup failed` (usually the Developer access in step 3, and the line you will
see most often since that check runs first), or `Google Wallet class insert failed`. A failure is
cached for five minutes, so fix it and wait a moment rather than retrying in a loop. A success is
cached for a day, and the access token for just under an hour.

**A wrong path or a truncated key reads as unconfigured, silently.** A path that is not a file, an
unreadable file, a line-wrapped base64 paste, or JSON missing `client_email` or `private_key` all
leave the feature off with nothing logged: the button simply never appears. Check the path resolves
to a file and that the JSON decodes with both keys.

**The pass saves for you but not for anyone else.** The issuer is still in demo mode; see step 5.

**The pass shows the Event Schedule logo, or old branding.** The pass logo is the schedule's
profile image, and the Event Schedule logo stands in when the schedule has none, so give the
schedule a profile image. Branding is written into an occurrence's pass class once, so a change
reaches only occurrences nobody has saved a pass for yet.

**The pass has no logo or banner image.** Google fetches those from your own installation, so they
are only sent when `APP_URL` is an https address Google could actually reach. A LAN or plain-http
installation deliberately sends no image rather than shipping a broken one.

## Adding another wallet provider

`App\Services\Wallet\` and `resources/views/partials/wallet-buttons.blade.php` are shaped for more
than one provider. An Apple Wallet implementation would add its own service and its own block in
that partial, with no change at any call site. It is not implemented: it needs a paid Apple
Developer account and a Pass Type ID certificate, which most selfhost operators do not have.
