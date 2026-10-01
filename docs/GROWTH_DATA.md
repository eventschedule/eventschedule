# Growth data

The growth payload is one JSON document describing the hosted install's funnel, activation,
monetization and retention. It is built by `App\Services\GrowthExportService::build()`. The same
build renders `/admin/growth`; the full document is pulled to a dev machine for analysis:

```bash
php artisan app:pull-growth                      # last_30_days funnel window
php artisan app:pull-growth --range=last_90_days # or last_7_days, all_time
php artisan app:pull-growth --local              # re-print the summary of the latest pull, no download
```

Each pull is saved as `storage/app/growth/growth-YYYY-MM-DD-His.json` and copied to
`storage/app/growth/latest.json` (the folder is gitignored), with `meta.pulled_range` added: the
`--range` it was pulled with, so the summary can compare a pull with one over the same window. The command prints about fifteen
headline numbers against the previous pull of the same schema, plus any `meta.notes` that are new.

**Never commit a pull, and never put figures from one into a committed file.** This repository is
public. Analysis belongs in conversation and in Claude's private memory, not in the repo.

How to analyse a pull (with Claude): `.claude/skills/growth-review/SKILL.md`.

## Setup

The pull calls `GET /api/internal/growth` (`App\Http\Controllers\GrowthDataController`) with a
bearer token.

1. Generate a token: `openssl rand -hex 32`.
2. Server: add `GROWTH_DATA_TOKEN` as an **encrypted, app-level** env var in the DigitalOcean
   console, then deploy. Use the console, not a spec file: a stored spec wipes the custom domains
   added since it was written.
3. Dev machine: put the same value in `.env` as `GROWTH_DATA_TOKEN`. `GROWTH_DATA_URL` defaults
   to `https://eventschedule.com`. The command sends the token only to that host or to a local one
   (`localhost`, `127.0.0.1`, `*.test`), whatever `--url` says; another host takes an edit to `.env`.

The endpoint is hosted-only. It answers 404 while the token is unset or shorter than 32 characters,
and it is throttled per real client IP (10 a minute). Only one build runs at a time. Responses are
`no-store`. Every pull is written to the audit log with the caller's IP and the build's duration
and peak memory. A rejected token is logged too, but only the first one per caller per hour, so the
public path cannot be used to fill the table. The disabled 404 and the throttle's 429 are not
logged. The command names each failure: 401, 404, 429, a redirect, a Cloudflare challenge.

`php artisan app:export-growth --range=... --path=...` builds the same payload from a shell on the
server itself.

## Privacy

The payload is pseudonymous and aggregate by construction. `GrowthDataEndpointTest` and
`GrowthExportTest` fail the build if a name, email address, IP, street address, payment
identifier, subdomain, custom domain or token appears in it.

- **Ids:** `uid` (user) and `sid` (schedule) are 12 hex characters of an HMAC keyed by `APP_KEY`.
  They are stable across pulls, so pulls can be diffed and the two row tables joined. They cannot
  be reversed without the server's key. Do not try to re-identify one.
- **No free text:** cancellation comments, names, descriptions and event titles are never exported.
- **Attribution columns** (`utm_source`, `utm_medium`, `referrer_domain`, `landing_path`) are
  visitor-controlled strings, so:
  - A value shared by fewer than 3 signups (`GrowthExportService::MIN_ATTRIBUTION_GROUP`) becomes
    `(other)`.
  - Exempt from that rule:
    - our own marketing and docs pages (the keys of `config/sitemap_lastmod.php`);
    - published blog posts (as `/blog/<slug>`);
    - known platforms, canonicalised (`google.co.uk` and the Android search app both read `google`).
  - Always replaced, whatever the count:
    - a utm or landing path containing `@` becomes `(redacted)`;
    - an IP referrer becomes `(ip)`;
    - a schedule's subdomain, its custom domain, or another host under that domain
      (`tickets.venue.com` beside `events.venue.com`) as referrer becomes `(schedule)`;
    - token-shaped path segments become `:token`.
  - Residual risk: a personal website that refers 3 or more signups appears by name.
- **Country** (`schedules.country`): a country shared by fewer than 5 schedules reads `(other)`.
  The threshold is higher than attribution's 3 because a schedule row carries more beside it.
- **Activity** (`logins_90d`, `event_edits_90d`): exported as buckets (`0`, `1`, `2-5`, `6+`),
  never exact counts.
- **The audience** (`buyers`): computed in SQL and exported only as monthly counts. No buyer
  address leaves the database, hashed or not.

## Populations and windows

Different sections count different populations over different windows. Read this before comparing
any two numbers.

| Section | Population | Window |
|---|---|---|
| `funnel`, `funnel_trend` | Organizer cohort: verified, non-demo users with `signup_intent` null or `organizer` | `meta.range` |
| `activation`, `cohorts`, `acquisition`, `segments.by_signup_intent` | Every verified non-demo user (attendee intents included), from the `signups` rows | All time |
| `segments.by_schedule_type`, `free_pressure`, `payers_vs_free`, `retention` | Owned, non-deleted, non-demo schedules, from the `schedules` rows | All time, plus trailing windows named in the field |
| `monetization` | Owned, non-deleted, non-demo schedules and their subscriptions | Now, plus monthly history in `gmv_by_currency` |
| `traffic` | Marketing-site counters (nexus only) and verified signups | Monthly, all time |
| `claims` | OWNERLESS placeholder schedules, which every other section excludes | Monthly, last 6 months |
| `churn` | `subscription_cancellations` rows | All time, since 2026-09-28 |
| `daily` | Mixed: organizer and other signups, real schedules and events, sellers, subscriptions | Last 180 UTC days |
| `nudge_outcomes` | Schedules sent an activation nudge | All time; outcomes 14 days after each nudge |
| `onboarding_nudges` | Organizer cohort signed up since 2026-08-10 | Since 2026-08-10 |
| `buyers` | Everyone with a paid sale on a non-demo event (money, or free RSVP) | Last 12 months |
| `reach` | Guest traffic and audience on non-demo schedules | Last 26 ISO weeks |
| `usage`, `boost`, `referrals`, `federation` | Install-wide | Recent months, as named |
| `geography` | Same as the `schedules` rows | All time |

`meta.range_applies_to` lists the range-scoped sections. `meta.partial_month` names the month in
progress: every month-keyed count for it is month-to-date.

"Demo" means demo **content** (`Role::constrainDemoContent()`): the demo account, schedules whose
contact address is the demo's, and the showcase schedules. A real schedule on a `demo-*` subdomain is
real.

**Paying** means **billing**: a live, non-trialing Stripe subscription, the same definition as MRR
(`RecurringRevenue::billingRoleIds()`). A paid **tier** (`plan`) also covers admin grants, referral
credits, legacy `plan_expires` rows and trials, which is most paid-tier schedules.

**Sales, revenue and ticket types belong to the seller**: the schedule that created the event
(`events.creator_role_id`). Legacy events with no creator fall back to every schedule listed on them.

## Sections

- `meta` holds:
  - `generated_at`;
  - `range` (start/end of the funnel window) and `range_applies_to`;
  - `recent_months`, the 6 months that `*_recent` arrays cover, oldest first;
  - `partial_month`, `is_hosted`, `is_nexus`, `app_version`, `releases` (see below), `schema_version`;
  - `row_cap` and `truncated` (whether a row table hit the cap, with the true total);
  - `notes`: caveats that travel with the data. Read them.
- `funnel` is the onboarding funnel for the window.
  - `stages[]` is `{key, group, count, width, step_conv, drop_count}`. Groups:
    - `traffic`: visited, signup_view;
    - `email_code`;
    - `cohort`: account, reached_schedule, saved_schedule, reached_event, saved_event;
    - `tickets`: saved_ticket, saved_paid_ticket;
    - `plan`: hit_ticket_paywall, reached_checkout, subscribed.
  - Step conversions are never drawn across groups.
  - Also: `cohort_size`, `excluded_intents`, `first_event_conv` (signup to first event, %) and its
    `first_event_conv_change` against the previous equal window, `visitor_to_event_conv`,
    `biggest_drop` and `traffic_tracked`.
  - The previous window's cohort is older, so it has had longer to mature.
- `funnel_trend`: `labels`, `periods` (YYYY-MM-DD, ISO YYYY-Www or YYYY-MM), `granularity`,
  `visitors_basis` per period, `visitor_to_signup`, `signup_to_schedule`, `signup_to_event` (%),
  and `last_index` (the newest period, always immature).
- `activation`: `accounts`, `reached_schedule_form`, `saved_schedule`, `saved_event`.
- `cohorts[]`: the same per signup month, plus `median_days_to_first_schedule`.
- `acquisition`: `by_utm_source`, `by_utm_medium`, `by_referrer_domain`, `by_referrer_channel`,
  `by_landing_path`, `by_auth`. Each is a list of
  `{key, signups, saved_schedule, saved_event, saved_ticket, saved_paid_ticket}`, biggest first.
  The top 50 are listed and the rest fold into one `(rest)` row carrying `groups`; null is `(none)`.
- `segments`:
  - `by_signup_intent` has the shape above.
  - `by_schedule_type[]` is `{key, schedules, with_event, with_public_event, with_ticket_type,
    with_paid_ticket_type, with_paid_sale, paid_plan, billing}`. `paid_plan` is the tier;
    `billing` is who pays.
- `free_pressure`: whether free-plan limits ever bind, over free-TIER schedules.
  - `peak_month_paid_tickets` buckets the best of the last 6 months.
  - `ever_sold_paid` is from the first-ever sale.
  - Also `newsletter_emails_this_month` (month-to-date), `appointment_types` and `photos` buckets.
- `payers_vs_free`: `paid` (billing) vs `free` (everyone else, comps included), each
  `{schedules, features{flag: %}, averages{metric: mean}}`.
- `monetization` holds:
  - `plan_counts` (tiers);
  - `by_plan_source`, the paid tiers by where they came from: `stripe` (billing), `stripe_trial`,
    `trial`, `legacy`, `admin`, `referral`;
  - `by_plan_term`, `subscription_status`;
  - `mrr`, `arr` and `arpu` (platform currency, recognised prices only);
  - `billing_subscriptions`, `trialing_subscriptions`, `unrecognized_price_subscriptions`;
  - `list_prices`;
  - `median_days_to_upgrade`, over everyone who ever subscribed, churned included;
  - `gmv_by_currency[]` (`{currency, month, amount, sales}`): ticket money organizers took, not
    ours, demo excluded;
  - `ticket_trials` (`{started, running, sold_during, converted, expired_unconverted,
    started_from}`): the card-free selling trial, which is not a plan.
- `churn`: `cancelled`, `transferred`, `resumed`, `with_reason`, `by_reason`, `by_source`,
  `by_month`.
- `nudges`: per activation-nudge key, `{total, last_7_days, last_sent_at}`.
- `owner_digests`: per ISO week (owner-local), `{owners, schedules}`.
- `retention[]`: per schedule-creation month, `{month, schedules, with_event, active_recently,
  visited_recently, paid}`.
  - `active_recently` means an event created, or a paid ticket sold, in the last 90 days.
  - `paid` means billing.
- `traffic[]` (per month) holds:
  - `month` and `visitors_basis`;
  - `visitors`, `page_views`, `docs_visitors`, `docs_page_views`, `commercial_visitors`,
    `pricing_visitors`, `pricing_views`;
  - `signup_views`, `signup_code_requests`, `signup_code_verified`, `signup_code_invalid`;
  - `verified_signups`.

  A null is "not tracked yet", never zero.
- `claims`: `unclaimed_total`, `unclaimed_with_event`, `auto_created{month}`,
  `claimed{month}` (null before 2026-09).
- `meta.releases`: `[{version, first_seen_at}]`, when each version first ran here. It is stamped by
  the scheduler heartbeat (`App\Utils\ReleaseHistory`), so it is empty before 2026-10-01. Use it to
  date a change in `daily` to its release.
- `daily` (columnar, one row per UTC day) holds:
  - `signups_organizer`, `signups_other`;
  - `first_schedule`, `first_event` (users reaching each for the first time);
  - `events_created`;
  - per selling schedule, firsts: `first_ticket_type`, `first_paid_ticket_type`, `first_paid_sale`;
  - `paid_orders`, `stripe_connected` (Stripe Connect onboarding completed), `paywall_views`
    (first view per user), `trial_starts`;
  - `subscriptions_started` (not declined), `subscriptions_ended`, `cancellations`.
- `nudge_outcomes`: per activation-nudge key, `{sent, matured, acted_event, acted_ticket_type,
  acted_stripe_connected, acted_paid_sale}`.
  - `acted_*` counts matured nudges (14+ days old) whose schedule did that within 14 days.
  - There is no holdout group: compare keys and periods, never read a rate as the nudge's effect.
- `onboarding_nudges[]`: `{stage, users, saved_schedule}`. The stage is how many pre-schedule
  onboarding emails the user was sent (0-3); emails stop once a schedule exists.
- `dismissed_steps`: per dashboard next step (`tickets`, `payments`, `first_event`, ...),
  `{total, by_month}`. Each one is an owner explicitly saying "not for me".
- `buyers[]` (per month) holds:
  - `paid_orders`, `buyers`, `new_buyers` (first purchase on the platform), `returning_buyers`;
  - `rsvps`, `rsvp_people`;
  - `new_attendees` (first paid or free registration);
  - `attendees_who_became_organizers`: by the month they first attended, those whose account
    later created a real schedule. This is the viral loop.
- `reach` (columnar, one row per ISO week) holds:
  - `page_views`, plus `views_direct`, `views_search`, `views_social`, `views_email`, `views_other`;
  - `followers_added`, `subscribers_added`, `interests_added`;
  - `event_page_views`, `event_page_sales`.
- `usage`: `{operation: {month: {count, schedules}}}` from `usage_daily`. For example,
  `gemini_parse_event` is AI import and `gcal_sync` is Google Calendar sync. `schedules` counts
  distinct schedules; install-level operations count toward `count` only.
- `geography[]`: `{country, schedules, with_event, with_paid_sale, billing}`, over the
  k-anonymised `schedules.country`.
- `boost`:
  - `net_markup[]` (`{month, currency, net_markup}`) is our revenue from paid promotions, net of
    refunds;
  - `campaigns[]` is `{month, campaigns, schedules}`.
- `referrals`: `by_status`, `created_by_month`.
- `federation`: the selfhost installs that registered for federation, which is only a floor on
  selfhost installs. Fields: `by_status`, `active_30d`, `by_version`, `registered_by_month`.
- `hero_test`: the homepage headline experiment, as the `/admin/growth` card shows it (nexus only,
  else null). Join `signups.hero_variant` to judge a variant on sellers, not signups.

## Row tables

Both are columnar: read `columns[]`, then `rows[][]`. They are newest first and capped at
`meta.row_cap`.

### `signups` (one row per verified, non-demo user)

| Column | Meaning |
|---|---|
| `uid` | Pseudonymous user id; joins `schedules.uid` |
| `created_month` | Signup month |
| `signup_intent` | Why they signed up: null or `organizer` for organizers, else an attendee intent (`follow`, `ticket`, ...) |
| `utm_source`, `utm_medium` | First-touch UTMs, lowercased; see Privacy |
| `referrer_domain` | First-touch referrer host, or a canonical platform name or placeholder; see Privacy |
| `referrer_channel` | `search`, `ai`, `social`, `community`, `email`, `messaging`, `calendar` (an invite), `auth` (back from Google sign-in, real referrer lost), `own` (our own domain), `schedule`, `other`; null = no referrer. For any host `/admin/realtime` classifies, the channel is its (`RealtimeTracker::hostChannel()`), so the two never disagree: reddit and discord are `social` there and here |
| `landing_path` | First page seen, lowercased, leading slash. Tenant pages record their PATH only, so a schedule homepage reads `/` like ours |
| `auth` | `google`, `facebook`, `email`, `other` |
| `reached_schedule_form` | Opened the new-schedule form, or saved a schedule |
| `saved_schedule` | Ever saved a non-demo schedule (deleted ones count) |
| `saved_event` | Has an event with `events.user_id` = them (includes imported and guest-submitted ones) |
| `saved_ticket`, `saved_paid_ticket` | Has a live, non-add-on ticket type on one of their events; a priced one |
| `schedules_count` | Live (non-deleted) schedules |
| `days_to_first_schedule` | Signup to first schedule, days |
| `hero_variant` | The homepage headline variant they saw before signing up (our own key), or null |
| `referred` | Signed up through a referral link |
| `logins_90d` | Sign-ins in the last 90 days, bucketed `0`/`1`/`2-5`/`6+`. A lower bound: a remember-me session writes no row |
| `event_edits_90d` | Events they created or edited in the last 90 days, bucketed; system edits (imports, syncs) excluded |

### `schedules` (one row per owned, non-deleted, non-demo schedule)

| Column | Meaning |
|---|---|
| `sid`, `uid` | Pseudonymous schedule id, and its owner's `uid` |
| `created_month`, `type` | Creation month; `talent`, `venue` or `curator` |
| `plan`, `plan_source` | Tier (`free`, `pro`, `enterprise`) and where a paid tier came from (null = Stripe or legacy) |
| `billing` | Has a live, non-trialing subscription right now (paying) |
| `ever_subscribed` | Ever had a subscription that got past checkout (not `incomplete`) |
| `events_total`, `events_public` | Events it created or accepted; the public, published ones |
| `events_recent_90d` | Of those, created in the last 90 days |
| `ticket_types`, `paid_ticket_types` | Live non-add-on ticket types on events it created; the priced ones |
| `paid_tickets_total`, `paid_tickets_90d` | Paid tickets sold, all time and last 90 days (no RSVPs, imports, add-ons, appointments) |
| `paid_tickets_recent` | Paid tickets per month for `meta.recent_months` |
| `first_paid_sale_month` | Month of its first paid sale; null = never sold |
| `gmv_currency`, `gmv_recent` | Its single sale currency and money taken per recent month; null if none or several currencies |
| `gmv_recent_by_currency` | `{currency: [per recent month]}`, for any number of currencies |
| `views_90d` | Guest-page views, last 90 days (embeds are not counted) |
| `followers`, `subscribers` | Followers with an account; confirmed email subscribers without one |
| `interests_90d`, `interests_total` | Confirmed "notify me" addresses on its events, last 90 days and all time |
| `appointment_types`, `photos` | Non-deleted appointment types; event photos |
| `newsletter_emails_this_month` | Newsletter emails sent, month-to-date |
| `features` | Settings switched on: `gcal`, `mscal`, `caldav`, `custom_domain` (working, not failed or pending), `custom_css`, `custom_fields`, `banner`, `feedback`, `carpool`, `gift_cards` (enabled), `accept_requests` (on for nearly everyone by default), `sponsors`, `own_smtp`, `event_interest`, `no_subscribe_panel`, `stay22`, `federation`, `announce_events`, `fan_content`; on the owner's account: `api_key`, `webhooks`; and features actually used: `passes`, `seating`, `promo_codes`, `waitlist`, `sub_schedules`, `newsletter_sent`, `gift_cards_sold`, `gallery`, `boost`, `ai_import`, `team` (anyone but the owner has access) |
| `days_to_upgrade` | Creation to first real subscription, days; null = never |
| `country` | ISO country code, or `(other)` when fewer than 5 schedules share it |
| `gateways` | Payment gateways on the owner's account: `stripe` (Connect onboarding completed), `paypal`, `payfast`, `invoiceninja` |
| `stripe_connected_month` | Month the owner completed Stripe Connect (the only gateway that records when) |
| `dismissed_steps` | Dashboard next steps dismissed for this schedule (`tickets`, `payments`, ...) |
| `events_by_source` | Of its listed events: `created` by it, from `other_schedules`, `guest` submissions, synced from `google` or `caldav`, `auto_sourced` (curator rules). These overlap; they do not sum to `events_total` |

## Caveats that outlast any one pull

- **Visitor counts were rebased on 2026-09-07**, when counting moved to a JS beacon on edge-cached
  pages. They fell about 4x across that line, and whether that is bots removed or the beacon
  under-counting is not established. Never compare visit counts across `visitors_basis` values;
  check Cloudflare's numbers before trusting one.
- **History lost before 2026-10-01.** `audit_logs` was pruned at 90 days, so anything read from it
  is missing before roughly early July 2026: checkout `source`, trial `started_from`, claims. The
  growth-relevant actions are kept forever from this release on (`PruneAuditLogs::KEEP_ACTIONS`).
- **Tiny numbers.** Conversions, sellers and cancellations are small counts. At one a month, a month
  with none and a month with two are both ordinary; never read a single month's change in a small
  count as an effect.
- **Schema changes move definitions.** Compare pulls only within one `schema_version`, or read the
  changelog below first.

## Changelog (`meta.schema_version`)

- **9** (2026-10-01)
  - **New sections:** `daily`, `nudge_outcomes`, `onboarding_nudges`, `dismissed_steps`,
    `buyers`, `reach`, `usage`, `geography`, `boost`, `referrals`, `federation`, `hero_test`, and
    `meta.releases`.
  - **New signup columns:** `hero_variant`, `referred`, `logins_90d`, `event_edits_90d`.
  - **New schedule columns:** `country`, `gateways`, `stripe_connected_month`, `dismissed_steps`,
    `events_by_source`.
  - **`features` changes:**
    - it gains settings flags and the used-feature flags;
    - `custom_domain` now means a working one.
- **8** (2026-10-01)
  - **Payload changes:**
    - pulled with `app:pull-growth` instead of downloaded;
    - attribution anonymised (see Privacy), plus `referrer_channel` and `acquisition.by_referrer_channel`;
    - ids widened to 12 characters, so they do not match earlier pulls;
    - rollups end in `(rest)`;
    - new fields: `schedules.billing`, `ever_subscribed` and `paid_tickets_90d`,
      `segments.by_schedule_type[].billing`, `meta.partial_month` and `range_applies_to`,
      `funnel_trend.periods`, `granularity` and `visitors_basis`;
    - weekly trend keys are ISO weeks.
  - **Definitions corrected:**
    - paying means billing (`payers_vs_free`, `retention.paid`);
    - sales, revenue and ticket types are credited to the seller;
    - event counts only include created or accepted events, and `events_recent_90d` uses `created_at`;
    - `active_recently` uses a real 90-day sales window;
    - `ever_sold_paid` means ever;
    - ticket types exclude add-ons;
    - `days_to_upgrade` ignores declined checkouts;
    - `median_days_to_upgrade` includes churned subscribers;
    - `by_plan_source` no longer labels every null source `stripe`;
    - `subscription_status` excludes demo and deleted schedules;
    - demo exclusion is by content.
- **7**
  - `mrr` from `RecurringRevenue`, the dashboard's ARR definition: trials excluded, unrecognised
    prices at zero.
- **6**
  - `gmv_recent_by_currency`.
  - `gmv_by_currency` excludes the hourly re-seeded demo sales. Discard USD figures from any
    earlier pull.
- **3**
  - The `claims` section.

## Changing the payload

1. Bump `GrowthExportService::SCHEMA_VERSION` whenever a field's shape **or meaning** changes.
2. Update this file: the section or column, and a changelog entry. `GrowthDataDictionaryTest`
   fails the build if a top-level key, a row column or the current version is missing here.
3. Add a `meta.notes` line when the change alters how an existing number must be read.
4. Keep the Privacy guarantees: anything visitor-controlled or person-shaped goes through the same
   group-size rule or is aggregated, and `GrowthDataEndpointTest`'s sentinel list grows with it.
