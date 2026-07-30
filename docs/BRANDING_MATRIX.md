# Branding Matrix

Which Event Schedule branding a guest page shows, for every combination of deployment mode and
plan tier. Three inputs decide it, read at eight render sites, so this file is the single place
they are written down together. Keep it in sync when any of those sites change.

## The three inputs

| Input | Source | Meaning |
|---|---|---|
| `config('app.hosted')` | `IS_HOSTED`, `config/app.php` | A multi-tenant platform with live plan tiers and Stripe |
| `config('app.is_nexus')` | `IS_NEXUS`, `config/app.php` | This install *is* eventschedule.com. Independent of `hosted` |
| `Role::actualPlanTier()` | `app/Models/Role.php` | `'free' \| 'pro' \| 'enterprise'`; short-circuits to `'enterprise'` when not hosted |

The three deployment modes those combine into:

- **nexus** - `IS_HOSTED=true`, `IS_NEXUS=true`. eventschedule.com itself. Ad-free.
- **self-hosted SaaS** - `IS_HOSTED=true`, `IS_NEXUS=false`. An operator running their own
  multi-tenant platform, with their own tiers, Stripe and `APP_MARKETING_URL`.
- **selfhost** - `IS_HOSTED=false`. Single-tenant. Every schedule resolves to `'enterprise'`.

## The two predicates

```php
Role::showBranding()      // false on selfhost; else actualPlanTier() === 'free'
Role::creditChipReason()  // 'selfhost' | 'saas_free' | 'granted_plan' | null
```

`showBranding()` is **not** the inverse of `isWhiteLabeled()` - `isWhiteLabeled()` returns `true`
unconditionally when not hosted, which is what left the old selfhost branch permanently `false`.

`granted_plan` requires all of: `is_nexus`, `hosted`, `plan_source === 'admin'`,
`actualPlanTier() === 'enterprise'`, and no active Enterprise Stripe subscription.

## The matrix

`--` = nothing shown.

| Surface | nexus free | nexus paid | nexus Ent *admin-granted* | SaaS free | SaaS paid | selfhost (any tier) |
|---|---|---|---|---|---|---|
| Dark footer strip | yes, + "Supported by Invoice Ninja" | -- | -- | yes, links the operator's domain | -- | -- |
| Corner credit chip | -- | -- | yes, `utm_source=granted-plan` | yes, `utm_source=saas` | -- | yes, `utm_source=selfhost` |
| Event-page "Create your own" card | yes | -- | -- | yes | -- | -- |
| Ads / promo slot | -- (nexus is ad-free) | -- | -- | yes, if the operator enabled ads | -- | -- |
| Calendar embed, inside the iframe | -- | -- | -- | -- | -- | -- |
| Embed snippet line, on the host's page | yes | -- | -- | yes | -- | -- |
| Ticket/RSVP embed frame | yes | -- | -- | yes | -- | -- |
| Newsletter email footer | yes | -- | -- | yes | -- | -- |
| AP footer (not a guest surface) | support email | support email | support email | support email | support email | "Powered by eventschedule.com" + version |

## Render sites

| Surface | File | Gate |
|---|---|---|
| Dark footer strip | `resources/views/layouts/app-guest.blade.php` | `! request()->embed && config('app.hosted') && $role->showBranding()`; the `is_nexus` branch inside adds the Invoice Ninja credit |
| Corner credit chip | `resources/views/layouts/app-guest.blade.php` | `! request()->embed && $role->creditChipReason()` |
| Event-page card | `resources/views/event/show-guest.blade.php` | `$role->showBranding()` |
| Ads / promo slot | `resources/views/partials/promo-slot.blade.php` via `AppGuestLayout::$adSlot` | `Role::showAds()` + `AdsService::isEligible()` |
| Embed snippet line | `resources/views/components/embed-modal.blade.php`, `components/embed-ticket-modal.blade.php` | `$role->showBranding()` |
| Ticket embed frame | `resources/views/event/show-guest-ticket-embed.blade.php` | `$role->showBranding()` |
| Newsletter footer | `resources/views/emails/newsletter.blade.php` via `NewsletterService` | `$role->showBranding()` |
| AP footer | `resources/views/layouts/app-admin.blade.php` | `! config('app.hosted')`, no plan check |

## Rules that are easy to break

1. **`marketing_url()` vs the hardcoded domain is a meaningful distinction.** A `marketing_url()`
   link is the *operator's* growth CTA and follows `APP_MARKETING_URL`. A hardcoded
   `https://eventschedule.com` link is the license attribution and is not the operator's to
   rebrand. Do not "fix" the chip to use `marketing_url()`.
2. **The strip and the chip are not alternatives.** On a self-hosted SaaS free tier both render, by
   design (`8852635c`): the strip promotes the operator, the chip credits us.
3. **`request()->embed` suppresses both layout blocks.** Embeds carry attribution through the
   snippet line and the ticket-frame footer instead, never inside the calendar iframe.
   `?embed=1` never renders `event/show-guest.blade.php` (see `RoleController::viewGuest`), so the
   event-page card needs no embed guard.
4. **Custom domains keep their branding.** A lapsed Enterprise schedule keeps its custom domain and
   drops to free, so the strip renders there. Deliberate: it is the only genuine external backlink
   the hosted platform earns. Ads *are* suppressed on custom domains (AdSense policy), branding is
   not - the asymmetry is intentional.
5. **Selfhost attribution is guest pages only.** The newsletter footer, both embed snippets and the
   ticket-embed frame stay unbranded on selfhost. Adding our name to a selfhoster's outgoing mail
   or to HTML they paste on a client's site is a different decision, not made yet.
6. **The WP documents this matrix publicly** and is written to not overclaim. Any change here needs
   `resources/views/marketing/white-label.blade.php` (the seven-row register, section 05, the
   selfhost FAQ which also feeds the FAQ JSON-LD, and the file's own design comment) plus
   `marketing/docs/schedule-styling.blade.php#remove-branding` to move with it.

## SEO note

Nexus tenant pages live on `*.eventschedule.com`, so the strip's link is an *internal* link - a
conversion CTA, worth nothing as a backlink. Genuine external dofollow links come from three
places only: embed snippets pasted on third-party sites, custom-domain guest pages, and selfhost
installs. That is why the selfhost chip exists and why it is dofollow (`rel="noopener"`, no
`nofollow`). The Invoice Ninja link is deliberately `nofollow`.

Coverage: `tests/Feature/GuestBrandingTest.php` (matrix) and
`tests/Feature/GrantedPlanCreditTest.php` (the nexus granted-plan case in depth).
