# Branding Matrix

Which Event Schedule branding a guest page shows, for every combination of deployment mode and
plan tier. Four inputs decide it, read at eleven render sites, so this file is the single place
they are written down together. Keep it in sync when any of those sites change.

## The three inputs

| Input | Source | Meaning |
|---|---|---|
| `config('app.hosted')` | `IS_HOSTED`, `config/app.php` | A multi-tenant platform with live plan tiers and Stripe |
| `config('app.is_nexus')` | `IS_NEXUS`, `config/app.php` | This install *is* eventschedule.com. Independent of `hosted` |
| `Role::actualPlanTier()` | `app/Models/Role.php` | `'free' \| 'pro' \| 'enterprise'`; short-circuits to `'enterprise'` when not hosted |
| `Role::servesOnCustomDomain()` | `app/Models/Role.php` | The schedule *is* the site: `custom_domain` + `direct` + `active`. Head metadata only |

The three deployment modes those combine into:

- **nexus** - `IS_HOSTED=true`, `IS_NEXUS=true`. eventschedule.com itself. Ad-free.
- **selfhosted SaaS** - `IS_HOSTED=true`, `IS_NEXUS=false`. An operator running their own
  multi-tenant platform, with their own tiers, Stripe and `APP_MARKETING_URL`.
- **selfhost** - `IS_HOSTED=false`. Single-tenant. Every schedule resolves to `'enterprise'`.

## The two predicates

```php
Role::showBranding()      // false on selfhost; else actualPlanTier() === 'free'
Role::creditChipReason()  // 'selfhost' | 'saas' | 'granted_plan' | null
```

They start from different questions, and `creditChipReason()` calls `showBranding()` only to stand
down where the strip already renders:

- **The strip turns on the tenant's tier.** It is a growth CTA belonging to whoever runs the
  platform, so it is a free-tier thing and a paid tenant loses it.
- **The chip turns on the deployment.** It is the license credit, owed by whoever redistributes the
  software, so off the nexus every schedule is owed it whatever its plan. An operator's paying
  tenant is as much a part of that redistribution as their free one, and that tenant's subscription
  is between them and the operator. eventschedule.com is the only install that sells white-label,
  so it is the only install where the chip depends on a plan for any other reason.
- **The strip wins where the two would meet.** A page carries one credit, so `creditChipReason()`
  answers null wherever `showBranding()` is true. The deployment argument therefore decides an
  operator's paid tiers, and their free tier shows the strip alone.

`showBranding()` is also **not** the inverse of `isWhiteLabeled()` - `isWhiteLabeled()` returns
`true` unconditionally when not hosted, which is what left the old selfhost branch permanently
`false`.

`granted_plan` requires all of: `is_nexus`, `plan_source === 'admin'`,
`actualPlanTier() === 'enterprise'`, and no active Enterprise Stripe subscription.

## The matrix

`--` = nothing shown.

| Surface | nexus free | nexus paid | nexus Ent *admin-granted* | SaaS free | SaaS paid | selfhost (any tier) |
|---|---|---|---|---|---|---|
| Dark footer strip | yes, + "Supported by Invoice Ninja" | -- | -- | yes, links the operator's domain | -- | -- |
| Corner credit chip | -- | -- | yes, `utm_source=granted-plan` | -- (the strip has it) | yes, `utm_source=saas` | yes, `utm_source=selfhost` |
| Event-page "Create your own" card | yes | -- | -- | yes | -- | -- |
| Ads / promo slot | -- (nexus is ad-free) | -- | -- | yes, if the operator enabled ads | -- | -- |
| Calendar embed, inside the iframe | -- | -- | -- | -- | -- | -- |
| Embed snippet line, on the host's page | yes | -- | -- | yes | -- | -- |
| Ticket/RSVP embed frame | yes | -- | -- | yes | -- | -- |
| Newsletter email footer | yes | -- | -- | yes | -- | -- |
| Head metadata: `<title>`, `og:site_name` | -- | -- | -- | -- | -- | -- |
| Head metadata: `BreadcrumbList` root | `marketing_url()` | `marketing_url()` | `marketing_url()` | `marketing_url()` | `marketing_url()` | `marketing_url()` |
| Head metadata: `og:image` | the owner's, or none | the owner's, or none | the owner's, or none | the owner's, or none | the owner's, or none | the owner's, or none |
| Head metadata: web app manifest | the schedule's own | the schedule's own | the schedule's own | the schedule's own | the schedule's own | the schedule's own |
| Head metadata: `theme-color` | the accent, or nothing | the accent, or nothing | the accent, or nothing | the accent, or nothing | the accent, or nothing | the accent, or nothing |
| AP footer (not a guest surface) | support email | support email | support email | support email | support email | "Powered by eventschedule.com" + version |

The first two rows are mutually exclusive column by column, and that is the invariant a reader
should be able to check by eye: no cell has a `yes` in both. `GuestBrandingTest::test_no_guest_page_ever_carries_both_credits`
asserts it over all six deployment-by-tier cells.

The two head-metadata rows do **not** turn on the plan - they turn on the domain, which is why they
are flat across every column. `<title>` and `og:site_name` carry the schedule's own name
everywhere, on every tier and every install. The breadcrumb root is the only platform string left in
the head, and `servesOnCustomDomain()` removes that one too.

## Render sites

| Surface | File | Gate |
|---|---|---|
| Dark footer strip | `resources/views/layouts/app-guest.blade.php` | `! request()->embed && config('app.hosted') && $role->showBranding()`; the `is_nexus` branch inside adds the Invoice Ninja credit |
| Corner credit chip | `resources/views/layouts/app-guest.blade.php` | `! request()->embed && $role->creditChipReason()` (the predicate itself stands down where the strip renders) |
| Event-page card | `resources/views/event/show-guest.blade.php` | `$role->showBranding()` |
| Ads / promo slot | `resources/views/partials/promo-slot.blade.php` via `AppGuestLayout::$adSlot` | `Role::showAds()` + `AdsService::isEligible()` |
| Embed snippet line | `resources/views/components/embed-modal.blade.php`, `components/embed-ticket-modal.blade.php` | `$role->showBranding()` |
| Ticket embed frame | `resources/views/event/show-guest-ticket-embed.blade.php` | `$role->showBranding()` |
| Newsletter footer | `resources/views/emails/newsletter.blade.php` via `NewsletterService` | `$role->showBranding()` |
| `<title>` | `App\View\Components\AppGuestLayout::guestTitle()` | none - never branded |
| `og:site_name` | `resources/views/layouts/app-guest.blade.php` (4 branches) | none - never branded |
| `BreadcrumbList` root | `resources/views/layouts/app-guest.blade.php` (2 branches) | `! $role->servesOnCustomDomain()` |
| `og:image` / `twitter:image` | `resources/views/layouts/app-guest.blade.php` (5 branches incl. JSON-LD) | none - never branded; omitted entirely when the owner has no image |
| `og:image` on ticket / order / installment / Payfast | `resources/views/partials/private-page-meta.blade.php` | none - those four set a `meta` slot purely to avoid the shell's default |
| Web app manifest (tenant) | `AppController::scheduleManifest()` via `resources/views/partials/web-app-manifest.blade.php` | none - never plan-gated, and never installable on Android |
| `theme-color` (tenant) | `resources/views/partials/web-app-manifest.blade.php` | `Role::manifestThemeColor()`, the same accessor the manifest JSON reads |
| AP footer | `resources/views/layouts/app-admin.blade.php` | `! config('app.hosted')`, no plan check |

## Rules that are easy to break

1. **`marketing_url()` vs the hardcoded domain is a meaningful distinction.** A `marketing_url()`
   link is the *operator's* growth CTA and follows `APP_MARKETING_URL`. A hardcoded
   `https://eventschedule.com` link is the license attribution and is not the operator's to
   rebrand. Do not "fix" the chip to use `marketing_url()`.
2. **The strip and the chip are alternatives, and the strip wins.** `creditChipReason()` answers
   null wherever `showBranding()` is true, so no guest page carries both. What survives is the
   asymmetry underneath: off the nexus the chip is owed by the deployment rather than the tier, so
   an operator's *paid* tenants carry it and only their free tier is covered by the strip instead.
   Two consequences that read like bugs and are not. Upgrading a tenant on an operator's platform
   *adds* the chip rather than removing it. And an operator's free tier carries no Event Schedule
   attribution at all, because their strip links `marketing_url()`, which is their own site: the
   credit on that page is theirs, not ours.
3. **`request()->embed` suppresses both layout blocks.** Embeds carry attribution through the
   snippet line and the ticket-frame footer instead, never inside the calendar iframe.
   `?embed=1` never renders `event/show-guest.blade.php` (see `RoleController::viewGuest`), so the
   event-page card needs no embed guard.
4. **Custom domains keep their body branding.** A lapsed Enterprise schedule keeps its custom domain
   and drops to free, so the strip renders there. Deliberate: it is the only genuine external
   backlink the hosted platform earns. Ads *are* suppressed on custom domains (AdSense policy),
   branding is not - the asymmetry is intentional. The one thing a custom domain *does* change is
   the `BreadcrumbList` root, and that is a correctness fix rather than a concession: a breadcrumb
   whose first item sits on another domain is discarded by Google, so the old `marketing_url()` root
   bought nothing and cost the schedule its breadcrumb. Do not extend `servesOnCustomDomain()` to
   any body surface.
5. **The deployment-wide credit is guest pages only.** The newsletter footer, both embed snippets
   and the ticket-embed frame stay keyed on `showBranding()` everywhere, so a paid tenant on an
   operator's platform and every schedule on a selfhost install leave them unbranded. Putting our
   name into someone's outgoing mail, or into HTML they paste on a client's site, is a different
   decision and has not been made.
6. **The head is white-label surface too, and it hid two leaks for years.** Both were fallbacks,
   which is why no gate caught them: a gate chooses between the tenant's asset and a neutral one,
   while a fallback fires only when the tenant *has* no asset - exactly the free, unfinished
   schedule least able to notice. `og:image` fell back to `/images/social/home.png`, our 1200x630
   marketing card, on five branches of `app-guest.blade.php`, so a logo-less schedule's WhatsApp
   preview was an Event Schedule advert next to an `og:site_name` bearing their name. Four more
   views (`ticket/view`, `ticket/order`, `installment/pay`, `payments/payfast/redirect`) set no
   `meta` slot at all and inherited the shell's default, which names us outright. **The correct
   fallback in the head is nothing.** Note what that does and does not promise: X with a
   `summary` card shows no image, but Facebook's crawler falls back to selecting one from the page
   body, so "no og:image" is not "no picture". The claim worth making, and the only one true on
   every platform, is that the picture is never OURS - it degrades to the owner's own page, which
   an advert of ours does not. Coverage:
   `tests/Feature/GuestSocialImageTest.php`.
7. **The tenant manifest is advertised but deliberately not installable.** An Android WebAPK claims
   every link tapped on its host from another app and shows its launch splash before the page, so
   an install branded as ours put our logo full screen in front of every schedule's audience.
   `display_override: ['browser']` is what now stops Chrome minting one; `display` stays
   `standalone` because Safari ignores `display_override` and iOS only exposes the Push API inside
   a Home Screen web app, which the ticket page's opt-in depends on. Do not "tidy" these into one
   field, and do not add a second `display_override` entry in front of `browser`. `sizes: "any"`
   is not a defence - Chromium treats it as satisfying every size requirement. The historic
   `/manifest.webmanifest` path stays served so WebAPKs installed before v1.0.124 re-brand
   themselves off it. **`icons` is always present, and that is load-bearing rather than tidy.**
   Omitting it reads like "no icon of ours", but a WebAPK re-brands itself by re-reading this
   document, so an absent key leaves an install minted while the static manifest was live holding
   our mark with *nothing to replace it with* - which is why owners' audiences still saw our logo
   full screen months after that file was deleted. You cannot uninstall an app from someone's home
   screen remotely and an Android splash always paints something, so handing it a different icon
   is the only lever there is: the schedule's own logo, or `/images/schedule-icon.png`, a neutral
   calendar glyph carrying no wordmark and none of the brand blue. Note the deliberate asymmetry
   with item 6: the right fallback for `og:image` is *nothing*, because it degrades to the owner's
   own page, while the right fallback here is *something*, because the alternative degrades to us.
   `background_color` follows the accent unconditionally now - it was briefly gated on the
   schedule having a logo, to avoid standing our mark on their colour, but that gate only
   described the bug and made the change a no-op on exactly the logo-less schedules still showing
   our mark. Removing the possibility is what retires the gate. `theme_color` was never gated that
   way: it tints the page, which is theirs regardless. Coverage:
   `tests/Feature/GuestManifestTest.php`.
8. **The WP documents this matrix publicly** and is written to not overclaim. Any change here needs
   `resources/views/marketing/white-label.blade.php` (the seven-row register, section 05, the
   selfhost and operator FAQs which also feed the FAQ JSON-LD, and the file's own design comment),
   the operator-facing `marketing/saas.blade.php` and `marketing/selfhost.blade.php`, and
   `marketing/docs/schedule-styling.blade.php#remove-branding` to move with it.

## SEO note

Nexus tenant pages live on `*.eventschedule.com`, so the strip's link is an *internal* link - a
conversion CTA, worth nothing as a backlink. Genuine external dofollow links come from three
places only: embed snippets pasted on third-party sites, custom-domain guest pages, and selfhost
installs. That is why the selfhost chip exists and why it is dofollow (`rel="noopener"`, no
`nofollow`). The Invoice Ninja link is deliberately `nofollow`.

An operator's own platform is a fourth, and a partial one: the chip is on their paid tiers only, so
a free schedule there sends us nothing. Its strip is an outbound link to the operator instead.

The head metadata was never part of that. `<title>` and `og:site_name` are unlinked text, so they
earned recall and no link equity, while occupying the highest-value slot on a tenant's page - a
suffix repeated across every page of every schedule is also a standard trigger for Google rewriting
the title. Dropping them costs the platform nothing measurable and hands each schedule a title that
matches its own name, which is what `og:title` had been sending all along.

Coverage: `tests/Feature/GuestBrandingTest.php` (matrix),
`tests/Feature/GrantedPlanCreditTest.php` (the nexus granted-plan case in depth),
`tests/Feature/GuestSocialImageTest.php` (the head's `og:image`) and
`tests/Feature/GuestManifestTest.php` (the web app manifest and `theme-color`).
