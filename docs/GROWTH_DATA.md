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
- **Registration links** (`schedules.external_tickets_90d`): the link is organizer-typed, so only
  its host is read and only as one name from a fixed list in the code
  (`GrowthExportService::TICKET_PLATFORMS`); any other host reads `other`. A name carried by fewer
  than 5 schedules is folded into `other_ticketing` or `other`, the same threshold as `country`,
  because a platform one country uses says where a schedule is. Contracted box offices share one
  `box_office` name for the same reason.
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
| `hero_test` | Homepage visitors given a headline (nexus only), and EVERY account created carrying a current variant, with no verified or demo filter | Since `hero_test.reset_at`, all time when null |

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
- `event_form`: what events made by hand are saved with, by the month they were created. It is the
  read on the 2026-10 event form redesign, which moved an event's location and its ticket choice
  onto the first tab.
  - `by_month{month}` is `{first, later}`, each `{events, with_location, with_signup, with_flyer}`.
    - Population: non-demo events with no `import_source`, so typed into the form, by
      `events.created_at`. `first` is an event that is the first its account ever made; `later` is
      every other.
    - `with_location`: a venue schedule is attached, or the event has an online link.
    - `with_signup`: tickets or registration is on, or the event links to tickets elsewhere.
    - `with_flyer`: the event has a flyer image.
  - `new_venues{month}` is `{created, with_email}`: venue schedules made that month that a
    hand-made event is at, and how many carry an email address. The venue's email moved behind a
    link in the redesign, so this is where a drop would show.
  - **It is the state when pulled, not when first saved.** An event given a venue a week later
    counts as having one, so the newest month keeps filling in. Compare a month with the same
    month in an earlier pull, or months that are both at least a few weeks old.
  - **Months before `events.import_source` existed (it shipped in 2026-10) count imports too:** an
    older import reads as made by hand. Use them as a loose baseline, not as the form's own rate.
  - Use the SHARES (`with_location / events`), never the counts: the population changes with
    signups.
  - What the form asks, for reading the shares: location is the Event tab's second section, on
    the first screen; sign-up is chosen on the Tickets tab (three tiles), whose name in the
    sidebar reads "No tickets" under it until something is chosen. The save bar no longer points
    to either (it did, with "Add location" and "Add tickets", for the first days of 2026-10).
- `claims`: `unclaimed_total`, `unclaimed_with_event`, `claimable_with_contact`,
  `auto_created{month}`, `claimed{month}` (null before 2026-09).
  - `claimable_with_contact` is the placeholders the claim page will hand to somebody
    (`Role::scopeClaimable()`: ownerless, and no verified email or phone stamp) that also carry an
    email or a phone number, which the "Claim this page" button and the invitation both need. It is
    the pool still open, a stock: a claimed placeholder leaves it. `unclaimed_total` is wider, and
    includes placeholders nobody can be invited to.
  - An invitation is narrower still. It is sent only when the organizer ticks the box on the event
    form, and only for a venue or talent that has not unsubscribed.
  - `claimed` is a floor. Two paths take ownership without writing the `schedule.claim` audit row
    it counts: signing up or in from an SMS invitation link (`User::claimRolesByPhone()`), and the
    "I manage this venue" box on an import, which needs no contact on the placeholder at all.
  - `auto_created` counts placeholders that are STILL ownerless, so a claimed one leaves its month.
- `meta.releases`: `[{version, first_seen_at}]`, when each version first ran here. It is stamped by
  the scheduler heartbeat (`App\Utils\ReleaseHistory`), so it is empty before 2026-10-01. Use it to
  date a change in `daily` to its release.
- `daily` (columnar, one row per UTC day) holds:
  - `signups_organizer`, `signups_other`;
  - `first_schedule`, `first_event` (users reaching each for the first time);
  - `events_created`, and `events_imported` (schema 12): the ones an import or a calendar pull
    made, a subset of it;
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
  - The cadence changed on 2026-10-02, from 1h / 24h / 72h after signup to 1h / 48h / 168h, so a
    signup that activates on day 1 or 2 now counts at stage 1 rather than stage 2. Compare
    cohorts on either side of that date, never one stage's rate across it.
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
- `hero_test`: the homepage headline A/B test (`App\Utils\HeroExperiment`), as the `/admin/growth`
  card shows it. Null off the nexus, and null if the test could not be evaluated.
  - `phase` is where the test stands:
    - `clicks`: learning, the traffic split still follows sign-up clicks;
    - `signups`: enough signups have come in to decide on them alone;
    - `candidate`: one variant is leading and waiting out its hold;
    - `winner`: locked, and every visitor now sees it.
  - `rows` has one entry per CURRENT variant, largest `share` first:
    - `key`, `headline`, `subtitle`, and `is_default` for the control;
    - `share`: the fraction of new visitors being given it now. Every variant keeps at least 0.05
      until a winner is locked, and one with under 300 visitors gets at least an even share;
    - `visitors`: people given that headline, counted once per visitor per day per variant, bots
      and signed-in users excluded;
    - `clicks`: their first click on any sign-up link on the homepage, counted the same way;
    - `signups`: accounts created carrying the variant, on the sign-up link or in the
      attribution cookie;
    - `click_rate`, `signup_rate`: per visitor, null with no visitors;
    - `p_best`: the probability it is the best variant on signups; `p_best_clicks`: on clicks.
  - `click_share`: how far the split still follows clicks rather than signups. It falls from 1 to
    0 as signups reach 60 across all variants.
  - `candidate` (`{key, date}`): the signup leader and the day it took the lead. It needs `p_best`
    of 0.95, 25 signups and 800 visitors, with every variant past 300 visitors, and is dropped if
    it loses the lead or falls under 0.90.
  - `lock_date`: the day the candidate becomes the winner if it holds, 7 days after it took the
    lead.
  - `winner` (`{key, date}`): the locked variant and the day it locked.
  - `reset_at`: the day the counts were last started over from `/admin/growth`, or null.
  - Reading it:
    - **Join `signups.hero_variant` to `schedules` on `uid` to judge a variant on the sellers it
      produced**, not on signups alone.
    - **The two signup counts will not match.** `rows[].signups` is every account since
      `reset_at`; the `signups` rows are verified, non-demo users, all time, and carry only
      `created_month`.
    - **Retired variants are absent here** but still appear in `signups.hero_variant`, which is
      the raw column.
    - **The rates are not bounded.** Visitors and clicks are deduplicated beacons, and a signup
      is credited from the variant on its sign-up link or in the attribution cookie, neither
      of which needs a counted click, so `signups` can exceed `clicks`.
    - **`p_best` moves between pulls on the same counts.** It is a 4000-draw simulation, and the
      section is cached for up to 10 minutes.
    - **How a signup reaches a variant changed twice**, in two releases committed on 2026-10-04.
      - Schema 9 and 10 pulls taken after the consent release: the attribution cookie only,
        which is written only with marketing consent. The beacons need none, so a visitor who
        did not allow it counts in `visitors` and `clicks` and never in `signups`.
      - From schema 11: the homepage also puts the variant on its sign-up and sign-in links,
        which stores nothing in the browser and needs no consent. A visitor who clicks through
        and signs up in that visit counts whatever they answered.
      - Still not counted without marketing consent: a visitor who signs up from another page
        or on a later visit, a browser that sends no `Referer` (the link is only accepted with
        the marketing site as the referrer, since a link can be copied), a visitor who signs up
        from inside the demo (leaving it ends the session), and a code email's continue link
        opened on another device. `signup_rate` understates every variant alike by that much.
      - Never counted, whatever the consent: a stub account (a follower or an invitee) made
        before `reset_at` that then finishes signing up. Its `hero_variant` is stored, but the
        count goes by the day the account was created.
      - With both carriers present the cookie is used: when they differ it is the headline
        the visitor saw last.
      - Without analytics consent the headline is picked afresh on each page view, so one
        person can count as a visitor of several variants.
      - Compare variants with each other, never a rate here with a funnel number, and never
        across either release. `reset_at` says when the current counts began.


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
| `reached_schedule_form` | Opened the new-schedule form, or saved a schedule. From 2026-10-02 the onboarding email's button opens the form for anyone who had picked a type, so this rises for email clicks that are not new progress: judge onboarding on `saved_schedule` per organizer signup, not on the form-to-save step |
| `saved_schedule` | Ever saved a non-demo schedule (deleted ones count) |
| `saved_event` | Has an event with `events.user_id` = them (includes imported and guest-submitted ones) |
| `saved_ticket`, `saved_paid_ticket` | Has a live, non-add-on ticket type on one of their events; a priced one |
| `schedules_count` | Live (non-deleted) schedules |
| `days_to_first_schedule` | Signup to first schedule, days |
| `hero_variant` | The homepage headline variant they saw before signing up (our own key), or null |
| `referred` | Signed up through a referral link |
| `logins_90d` | Sign-ins in the last 90 days, bucketed `0`/`1`/`2-5`/`6+`. A lower bound: a remember-me session writes no row |
| `event_edits_90d` | Events they created or edited in the last 90 days, bucketed; system edits (imports, syncs) excluded |
| `setup_guide` | How far through the setup guide they got (schema 13): `started`, `live` (their schedule went live with the guide watching), `shared` (copied the schedule's address), `embedded` (copied the embed code), `finished`. The furthest one reached. Null for anyone who never had a guide: everyone who signed up before it shipped, and anyone whose first schedule was not saved through the wizard (guest-submit, a claim, the API, a restore, a transfer) |
| `setup_guide_hidden` | They hid the guide and have not brought it back. False when `setup_guide` is null |
| `suggestions_off` | They turned suggestions off for the whole account (schema 13) and have not turned them back on: no setup guide, no next steps on the dashboard, no "List on the network" prompt, and none of the reminder emails that ask the same things. Independent of `setup_guide_hidden`, and possible for an account that never had a guide |

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
| `features` | Settings switched on: `gcal` (a Google sync direction is set; before schema 12 it read a column nothing wrote), `mscal`, `caldav`, `custom_domain` (working, not failed or pending), `custom_css`, `custom_fields`, `banner`, `feedback`, `carpool`, `gift_cards` (enabled), `accept_requests` (on for nearly everyone by default), `sponsors`, `own_smtp`, `event_interest`, `no_subscribe_panel`, `stay22`, `federation`, `announce_events`, `fan_content`; on the owner's account: `api_key`, `webhooks`; and features actually used: `passes`, `seating`, `promo_codes`, `waitlist`, `sub_schedules`, `newsletter_sent`, `gift_cards_sold`, `gallery`, `boost`, `ai_import`, `team` (anyone but the owner has access) |
| `days_to_upgrade` | Creation to first real subscription, days; null = never |
| `country` | ISO country code, or `(other)` when fewer than 5 schedules share it |
| `gateways` | Payment gateways on the owner's account: `stripe` (Connect onboarding completed), `paypal`, `payfast`, `invoiceninja` |
| `stripe_connected_month` | Month the owner completed Stripe Connect (the only gateway that records when) |
| `dismissed_steps` | Dashboard next steps dismissed for this schedule (`tickets`, `payments`, ...) |
| `events_by_source` | Of its listed events: `created` by it, from `other_schedules`, `guest` submissions, linked to a `google` entry by the owner's sync (in either direction), synced from `caldav`, `auto_sourced` (curator rules). These overlap; they do not sum to `events_total`. From schema 12 also `imported` and one `imported_{source}` per import source. See below |
| `external_tickets_90d` | Events it lists with a registration link instead of our tickets or RSVP: `{events, priced, self_serve, box_office, platforms}`, or null when it has none. See below |

#### Reading the `imported` buckets of `events_by_source`

`imported` counts the events a schedule **created through an import**, and `imported_{source}`
splits it by which one. They are a subset of `created`, credited to the schedule that did the
importing and not to a venue or talent the event is also listed on.

| Bucket | The event came from |
|---|---|
| `imported_ai` | text or a flyer read by the model on the import page |
| `imported_ics` | a calendar feed pasted as a link |
| `imported_page` | a web page's own event data |
| `imported_page_ai` | a web page whose text the model read |
| `imported_eventbrite` | the Eventbrite import |
| `imported_google`, `imported_microsoft`, `imported_caldav` | a calendar: a standing sync's pull, or a one-time import from it |

- **They start at the release that added `events.import_source`.** Nothing recorded how an earlier
  event was made, so every older import reads as made by hand. A schedule with `imported` 0 and a
  long history did not necessarily type its events in.
- **`google` is not `imported_google`.** `google` counts events linked to a Google entry, which
  includes every event pushed out to Google. `imported_google` counts events that came in from it.
- **Count owners, not schedules.** A calendar pull creates a venue schedule for each location it
  meets, owned by the same account. Asking "how many organizers have a full calendar" per schedule
  gets a share that falls as importing works. Group by `uid` and take each owner's fullest
  schedule, as the pull summary's "Organizers with 5+ events" row does.

#### Reading `external_tickets_90d`

Most live schedules have no ticket type of ours. This says which of them send people somewhere
else, and to what kind of place.

- **Counted:** published events the schedule created in the last 90 days, with our tickets and
  RSVP both off, whose registration link the event page shows as a link.
- **`self_serve`:** the events whose link is a platform an organizer signs up to and sells through
  alone: `eventbrite`, `ticket_tailor`, `luma`, `humanitix`, `payment_link`, and the smaller ones
  under `other_ticketing`.
- **`box_office`:** the events whose link is a system a venue or promoter contracts with, such as
  Ticketmaster, AXS or Dice. A link there is nearly always somebody else's sale. They share one
  `box_office` name.
- **`priced`:** the events with a price above zero, typed or read by AI import. It is the admission
  price, set beside any kind of link, so it does not say the tickets are sold there.
- **`platforms`:** `{name: {events, priced}}`. A name is one of the above, or `meetup`, `facebook`,
  `form`, `event_schedule` (a page on this install) or `other`. The host is never exported, and a
  name fewer than 5 schedules carry is folded into `other_ticketing` or `other`. The four counts
  beside it are never folded.

`self_serve` is a ceiling on the sellers who could sell here instead, never a count of them:

- The link says where tickets are sold, not who sells them. A talent's link is often its venue's
  or promoter's page, so read it by schedule type.
- One event is enough to count. Read it against `events_recent_90d`, and prefer schedules with two
  or more.
- A schedule is not a person. Count distinct `uid`.
- An owner whose `signups.signup_intent` is `request` got their schedule by submitting an event to
  someone else's. Leave them out.

Left out on purpose:

- anonymous guest submissions (the link is the submitter's);
- every event of a schedule that imports events on a timer, because an imported event keeps the
  page it was read from as its link;
- drafts;
- an event with our tickets on that the plan blocks from selling, though its page falls back to
  the link;
- anything created more than 90 days ago, a long-running recurring series included.

Also not seen: an Eventbrite import that brought ticket types becomes in-app tickets, and an event
created over the API or WhatsApp cannot carry a price.

## Caveats that outlast any one pull

- **Visitor counts were rebased on 2026-09-07**, when counting moved to a JS beacon on edge-cached
  pages. They fell about 4x across that line, and whether that is bots removed or the beacon
  under-counting is not established. Never compare visit counts across `visitors_basis` values;
  check Cloudflare's numbers before trusting one.
- **A first touch on an edge-cached marketing page needs marketing consent, from the release
  committed on 2026-10-04.** The first-touch cookie is written only once a visitor allows marketing cookies, and
  no answer counts as no. It is the only carrier off an edge-cached marketing page, so a visitor
  who did not allow it and first touched one signs up with `landing_path` `/sign_up` or `/login`
  and no UTM or referrer. `hero_variant` is the exception from schema 11: the homepage puts it on
  the sign-up link, which needs no consent (see `hero_test`). A first touch on a page that
  has a session is still recorded without consent: a schedule's page, `/sign_up` reached straight
  from another site, or the few marketing pages that are not cached (contact, search, `?lang=`). The stored values stay with the signup, so every pull keeps full
  attribution for signups made before that release. Compare marketing-page channels and landing
  pages as shares of attributed signups, never as counts across it.
- **History lost before 2026-10-01.** `audit_logs` was pruned at 90 days, so anything read from it
  is missing before roughly early July 2026: checkout `source`, trial `started_from`, claims. The
  growth-relevant actions are kept forever from this release on (`PruneAuditLogs::KEEP_ACTIONS`).
- **Pruned rows shrink history, from 2026-10-04.** `app:prune-personal-data` deletes interest-list
  and waitlist rows 30 days after their event, and unconfirmed sign-ups after 30 days, so
  `interests_added` for past weeks and the `waitlist` used-feature flag fall between pulls as those
  rows go. Compare them only within one pull.
- **Tiny numbers.** Conversions, sellers and cancellations are small counts. At one a month, a month
  with none and a month with two are both ordinary; never read a single month's change in a small
  count as an effect.
- **Schema changes move definitions.** Compare pulls only within one `schema_version`, or read the
  changelog below first.

## Changelog (`meta.schema_version`)

- **14** (2026-10-05)
  - **New `event_form` section:** hand-made events by created month, first event against later
    ones, with a location, with a way to sign up, with a flyer; and new venues with an email. It
    exists to read the event form redesign (the essentials on the first tab, the rest behind tabs
    with summaries). The section ships in the same release as the redesign, so no pull from before
    it carries it: the baseline is the months before the deploy, read from the first pull after
    it. Nothing else changed shape or meaning.
- **13** (2026-10-05)
  - **New `signups` columns:** `setup_guide`, `setup_guide_hidden` and `suggestions_off`. The
    setup guide replaced the three-circle step band on a new organizer's first screens; it
    starts when a first own schedule is saved through the wizard. Use `setup_guide IS NOT NULL`
    as the cohort of people who had it: `created_month` cannot separate them, because it did
    not start on the 1st. `suggestions_off` is the account-wide switch that shipped with it.
  - **Activation nudges reach fewer people.** An owner with `suggestions_off` gets none of the
    nudges that ask for something (every key but `first_sale`), and one who answered "No tickets
    needed" in the guide gets neither ticket nudge for that schedule. Neither writes a
    `dismissed_steps` row.
  - **`next_step_tickets` is offered to fewer schedules.** A free schedule that already takes
    registrations is no longer told to add them, which is the rule its email always had.
  - **`dismissed_steps` can read lower for that cohort.** While a guide is showing, the dashboard
    hides the Next steps rows the guide is asking for itself, so those rows are neither shown nor
    dismissed until the guide ends.
  - Everything else compares with schema 12.
- **12** (2026-10-04)
  - **New in `events_by_source`:** `imported` and one `imported_{source}` per import source
    (`ai`, `ics`, `page`, `page_ai`, `eventbrite`, `google`, `microsoft`, `caldav`). Zero for every
    event made before this release.
  - **New `daily` column:** `events_imported`.
  - **Two meanings changed.** `events_by_source.google` and `features.gcal` read columns nothing
    had written since April 2026 (the ids moved to `calendar_syncs` and the owner's pivot), so both
    were frozen in every earlier pull. `google` is now events linked to a Google entry by the
    owner's sync; `gcal` is a Google sync direction being set. Do not compare either across this
    version.
  - Everything else compares with schema 11.
- **11** (2026-10-04)
  - **A meaning changed; no field was added or removed.** `hero_test.rows[].signups` (and so
    `signup_rate`, `p_best` and `click_share`) and the `signups.hero_variant` column: a headline
    variant now reaches sign-up on the link as well as in the consented cookie, so both count
    visitors that a schema 9 or 10 pull taken after the consent release could not. See
    `hero_test`, "Reading it".
  - Everything else compares with schema 10.
- **10** (2026-10-04)
  - **Additive.** Nothing in schema 9 changed meaning, so a schema 9 pull compares with a
    schema 10 one on every field they share.
  - **New schedule column:** `external_tickets_90d`.
  - **New field:** `claims.claimable_with_contact`.
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
