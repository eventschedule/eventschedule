# Facebook Login setup

Adds **Continue with Facebook** to the login and sign-up pages and a **Facebook Settings** section
in Settings (connect, disconnect, and "Verify with Facebook" before setting a password).

## Off until configured

Everything is gated on `facebook_login_enabled()` (`app/helpers.php`), which is true only when
BOTH `FACEBOOK_CLIENT_ID` and `FACEBOOK_CLIENT_SECRET` are set. With either one missing:

- no Facebook button on the login or sign-up page
- no Facebook Settings link or section in Settings, and no "Verify with Facebook" button
- no Facebook entry in the privacy policy's processor list
- every `/auth/facebook*` URL returns 404

This app is separate from Boost's Meta app (`META_APP_ID` / `META_APP_SECRET`). Do not reuse
those variables.

## 1. Create the app

1. Go to <https://developers.facebook.com/apps> and click **Create App**.
2. Use case: **Authenticate and request data from users with Facebook Login**.
3. App type: **Consumer**, if you are asked. Avoid **Business**: it needs business verification
   before `email` works for anyone outside the app's roles.

## 2. Basic settings

**App settings > Basic**:

| Field | Value |
|---|---|
| App domains | `eventschedule.com` (selfhost: your domain) |
| Privacy policy URL | `https://eventschedule.com/privacy` (or whatever `policy_url('privacy')` resolves to) |
| Terms of service URL | `https://eventschedule.com/terms-of-service` (or `policy_url('terms')`) |
| User data deletion | Choose **Data deletion instructions URL** and point it at the privacy policy. Users delete their account in Settings > Delete Account; only the Facebook user ID is stored, so no callback endpoint is needed. |
| App icon | 1024 x 1024, required before going Live |
| Category | Required before going Live |

Copy the **App ID** and **App Secret**.

## 3. Facebook Login settings

**Use cases > Facebook Login > Settings**:

- Client OAuth login: **On**
- Web OAuth login: **On**
- Enforce HTTPS: **On**
- Use Strict Mode for redirect URIs: **On**
- **Valid OAuth Redirect URIs**. Add all three, on the host `APP_URL` points at. Hosted auth
  routes are served from `app.eventschedule.com` (`RedirectToAppSubdomain`):

```
https://app.eventschedule.com/auth/facebook/callback
https://app.eventschedule.com/auth/facebook/connect/callback
https://app.eventschedule.com/auth/facebook/set-password/callback
```

A missing URI breaks only its own flow (sign-in, connect from Settings, or verify before setting a
password), and Facebook's error names none of them, so add all three now.

For local testing, add the same three paths on your local `APP_URL` as well. Facebook accepts
`http://localhost` only while the app is in Development mode.

## 4. Permissions

**Use cases > Facebook Login > Customize**: make sure `email` and `public_profile` are added. On a
Consumer app both have Advanced Access by default, and no App Review is needed.

## 5. Test in Development mode

Only people with a role on the app can sign in until it is Live. Add yourself under
**App roles > Roles**, and create a test user under **App roles > Test users**.

## 6. Configure Event Schedule

**Selfhost** (`.env`):

```
FACEBOOK_CLIENT_ID=<App ID>
FACEBOOK_CLIENT_SECRET=<App Secret>
```

Then `php artisan config:clear`. `FACEBOOK_REDIRECT_URI` can stay unset: every call passes its own
redirect URI.

**Hosted**: add both as encrypted env vars on the **web** component in the DigitalOcean app spec,
then deploy. The worker does not need them.

## 7. Check it

Run through this in Development mode, then again after going Live:

- [ ] With the vars unset: no Facebook anywhere on login, sign-up or Settings; `/auth/facebook` is 404.
- [ ] New sign-up with Facebook lands on getting-started with a verified email (and terms stamped on hosted).
- [ ] Existing password account, same email: you are asked to log in once, and Facebook is linked afterwards ("Facebook Account Connected").
- [ ] Press **Cancel** on the Facebook dialog: back on the login page with no error.
- [ ] Untick the email permission: the login page offers **Try again**, which asks for email again.
- [ ] Settings > Facebook Settings: connect, then disconnect. With no password and no Google, disconnect is refused.
- [ ] Facebook-only account: Settings > Set Password > **Verify with Facebook**, then set a password.
- [ ] The login page shows a **Last used** chip on the button you last used.
- [ ] Light mode, dark mode, Hebrew (RTL), and phone width.

## 8. Go Live

Switch **App Mode** to **Live** at the top of the app dashboard. Until then, real users see
"App not active".

## Keep the same Meta app

Facebook gives each app its own ID for a person (an app-scoped user ID), and that is what
`users.facebook_id` stores. Pointing an install at a DIFFERENT Meta app later means no stored ID
matches again: people with a password or Google are asked to log in once to re-link, and
Facebook-only people must use **Reset password**. To rotate credentials, reset the App Secret on
the same app instead of creating a new one.

## Turning it off

Unset either variable and redeploy. All Facebook UI disappears and the routes return 404. Linked
`facebook_id` values stay on the users, so turning it back on restores those sign-ins. Someone who
only ever signed in with Facebook gets back in with **Reset password** on the login page.

## How linking works

| Facebook sign-in finds... | Result |
|---|---|
| A user with this `facebook_id` | Signed in |
| No email from Facebook | Login page with a **Try again** that re-requests the email permission |
| A user with this email, who is an invited placeholder (no password, no Google, no Facebook) | Linked and signed in |
| A user with this email who has a password or Google | Asked to log in with those once; the Facebook ID is held in the session for 10 minutes and linked on that login (password, 2FA or Google), only if the signed-in email matches |
| A user with this email already linked to a different Facebook account | Refused |
| Nobody | New account, same rules as the sign-up form (`public_registration_enabled()`) |
