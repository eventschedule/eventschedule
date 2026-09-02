# Edge caching of marketing HTML

How anonymous marketing (WP) pages are made cacheable by a shared cache, what the origin
promises, and the Cloudflare rules the operator has to add for it to take effect.

Selfhosted installs are unaffected unless they put a CDN in front of the app: the origin
headers change either way, but nothing else does.

## Why

Every marketing page used to respond `Cache-Control: no-cache, private` with a
`laravel_session` and an `XSRF-TOKEN` cookie, so `cf-cache-status` was `BYPASS` on all ~150
pages and every visitor paid a 0.39 to 0.59 s origin round trip before the first byte. That
TTFB is the floor under every Core Web Vital on the whole marketing site.

## The origin contract

`App\Http\Middleware\CacheableMarketingResponse` (prepended to the `web` group, right after
`EnsureSelfhostSetup`, so it wraps `StartSession`) marks a response public only when the
request is provably anonymous and the page is provably identical for every visitor.

A request is eligible when **all** of these hold:

- `config('app.is_nexus')` is true (marketing pages only exist on the nexus);
- the method is `GET` or `HEAD`;
- there is no query string at all, so `?lang=`, `?utm_*`, `?ref=` and everything else stays
  dynamic and keeps today's session behaviour;
- the host equals `_base_domain()` (never the `app.` subdomain, a tenant subdomain or a
  custom domain);
- the request carries no session cookie (`config('session.cookie')`), no `remember_*`
  cookie and no `Authorization` header;
- the matched route name starts with `marketing.` and is not `marketing.contact`,
  `marketing.search`, `marketing.browse` or `marketing.docs.search_index`.

For an eligible request the middleware switches the session driver to `array` **before**
`StartSession` resolves it, so `csrf_token()`, `session()->has()` and everything downstream
keep working against an in-memory store that is never persisted.

After the response is built it is marked public only if it is **also**:

- HTTP 200;
- still unauthenticated; and
- carrying no cookie other than `laravel_session` and `XSRF-TOKEN`.

That last check is what keeps the rule honest. A response that queued a cookie of its own
(the consented attribution cookies, a withdrawn-consent expiry, anything a controller
queued) is visitor-specific by definition, so it keeps `no-cache, private` **and** keeps its
cookies.

The two framework cookies are removed from **every** eligible response, whether or not it
ends up public. The session behind them was an in-memory one that was never written
anywhere, so handing the browser its id would make Cloudflare bypass the cache for the rest
of that visitor's session in exchange for a session that resolves to nothing. This matters
most for a visitor who accepted cookies: their first marketing page writes an attribution
cookie and so cannot be public, and without this they would then bypass the edge on every
page after it too.

For the same reason, `CaptureUtmParameters` treats an existing attribution **cookie** as
first-touch evidence alongside the session. Without that, a consented visitor rewrote
`utm_landing_page` on every page (there is no persistent session to remember it), which both
made every page uncacheable and quietly turned 30-day first-touch attribution into
last-touch. With it, a consented visitor's first page is dynamic and everything after it is
cached.

When all of it holds, the response is marked with:

```
Cache-Control: public, max-age=0, s-maxage=600
```

`max-age=0` means browsers revalidate on every navigation, so a visitor never sees a stale
page from their own cache. `s-maxage=600` is the shared-cache (edge) TTL.

There is deliberately no `stale-while-revalidate`. Firefox and Safari honour it in the
**browser** cache on an ordinary navigation, which would let a visitor who has just signed in
keep being painted the stored anonymous copy - guest header, guest-only scripts - for as long
as the directive lasted. Serving stale while revalidating is a shared-cache behaviour, so get
it from Cloudflare's own **Caching -> Configuration -> Serve stale content** setting, where no
browser can act on it.

Why the cookies have to be removed at all: in this Laravel version
`StartSession::sessionIsPersistent()` only checks that the driver is non-null, so the `array`
driver still queues `laravel_session`, and `ValidateCsrfToken` queues `XSRF-TOKEN` on every
response regardless.

`tests/Feature/MarketingEdgeCacheTest.php` pins the whole rule.

## The two stateless support routes

`CacheableMarketingResponse::STATELESS_ROUTES` - `marketing.visit` (the page-view beacon) and
`marketing.docs.search_index` (the docs search index) - get that same `array` driver and the
same cookie strip, on any method, whenever the request is anonymous (no session cookie, no
`remember_*` cookie, no `Authorization` header) and arrived on `_base_domain()`. Neither is a
page and neither holds anything per visitor.

Without this, edge caching stopped working after ONE page. The beacon is a POST, so it was
ineligible, so its 204 carried `Set-Cookie: laravel_session` and wrote a `sessions` row per
anonymous visitor. The next navigation carried that cookie, the Cloudflare rule bypassed on
it and the origin refused to mark the response public - in exchange for a session nothing
ever read. `GET /docs/search-index.json` did the same with `XSRF-TOKEN` as well.

Neither response is ever marked public by this middleware: the beacon stays `no-cache,
private`, and the search index keeps the `public, max-age=3600` its own controller sets (it
is in `EXCLUDED_ROUTES` precisely so the 10-minute page header cannot overwrite the hour).
`CaptureUtmParameters` also stands down on both, so a `/docs/search-index.json` fetch can
never be recorded as a landing page, and `TrackMarketingVisit::NON_PAGE_ROUTES` (the same two
routes) keeps it out of the page-view counters.

## Cloudflare Cache Rule (operator action, outside the repo)

Rules -> Cache Rules -> Create rule.

**Name:** `Marketing HTML edge cache`

**When incoming requests match** (expression editor):

```
((http.host eq "eventschedule.com")
  and not starts_with(http.request.uri.path, "/admin")
  and not starts_with(http.request.uri.path, "/api")
  and not starts_with(http.request.uri.path, "/sitemap")
  and not starts_with(http.request.uri.path, "/login")
  and not starts_with(http.request.uri.path, "/sign_up")
  and not (http.cookie contains "laravel_session")
  and not (http.cookie contains "remember_"))
```

**Then:**

- Cache eligibility: **Eligible for cache**
- Edge TTL: **Use cache-control header if present, use default otherwise** (this is what
  makes `s-maxage=600` the edge TTL rather than a fixed number in the dashboard)
- Browser TTL: **Respect origin TTL**

The two cookie clauses are the important ones: they are what keeps a signed-in visitor on
dynamic pages. Combined with the origin rule (a request carrying either cookie is never
marked public), a cached anonymous copy can never be handed to a signed-in user.

Both clauses are needed. A remembered visitor whose 2-hour session has lapsed sends a
`remember_*` cookie and no `laravel_session`, and the login form ships with **Remember me
checked by default**, so this is the common case rather than the exotic one. On the
`laravel_session` clause alone the origin would (correctly) refuse to mark their page public
while the edge would (incorrectly) hand them a stored anonymous copy of it.

`laravel_session` is `config('session.cookie')`, which is `SESSION_COOKIE` if it is set and
otherwise the `APP_NAME` slug plus `_session`. With the shipped `APP_NAME=Laravel` that is
`laravel_session`; change either and this expression has to change with it, or the bypass
silently stops matching and every signed-in visitor is served the anonymous copy.

The path exclusions are belt and braces. The origin already refuses to mark anything but a
`marketing.*` page public, and `/sitemap*` and the manifest already opt out of the `web`
group entirely.

## Cloudflare redirect rule (optional, separate)

`http://www.eventschedule.com/...` currently takes two hops: `http` to `https://www.`, then
`www.` to the apex. One Cloudflare redirect rule collapses it to a single 301.

Rules -> Redirect Rules -> Create rule.

**When incoming requests match:**

```
(http.host eq "www.eventschedule.com")
```

**Then** - Dynamic redirect:

- Expression: `concat("https://eventschedule.com", http.request.uri.path)`
- Status: `301`
- Preserve query string: on

This has to sit ahead of whatever currently performs the `www` to apex redirect, and needs
the HTTP (port 80) scheme included, which is what removes the extra hop.

## Deploys

A cached copy survives up to 10 minutes past a deploy, plus however long Cloudflare's
serve-stale setting allows. That is fine for content changes and wrong for
anything urgent, so for an urgent marketing fix purge the zone (Caching -> Configuration ->
Purge Everything, or purge the affected URLs) after the deploy finishes. Automating a zone
purge from the release flow is the obvious follow-up and is not wired up.

## What moved into the browser

Two things the origin used to do per page view cannot happen when the origin never sees the
view. Both now run as nonce'd inline scripts at the end of `layouts/marketing.blade.php`,
for guests only.

### 1. The page-view beacon

`navigator.sendBeacon('/marketing/visit', {"route": "<route name>"})`, with a
`fetch(..., {keepalive: true})` fallback. It feeds the same `marketing_daily_stats` buckets
the `/admin/users` onboarding funnel reads.

- The endpoint is `MarketingController::recordVisit`, throttled `120,1` and excluded from
  CSRF verification only (a beacon cannot carry a token, and this moves a counter rather
  than changing state).
- The route **name** is validated against the router
  (`TrackMarketingVisit::isCountableRouteName()`) rather than trusted, and the same helper
  is what decides whether the layout ships the beacon at all, so the two cannot disagree.
- Counting itself is one implementation, `TrackMarketingVisit::record()`, shared by the
  beacon and the middleware. The layout flags the request when it renders the beacon and
  `TrackMarketingVisit` then stands down, so exactly one of the two counts any given view.
  A marketing response with no beacon is still counted by the middleware; the two routes in
  `TrackMarketingVisit::NON_PAGE_ROUTES` are counted by neither, so a reader who opens the
  docs search no longer counts as a second docs view.
- One filter is relaxed for the beacon: `PageView::isSuspiciousRequest()` treats a wildcard
  `Accept` header as a bot signal, which is correct for a document navigation and impossible
  for a beacon (`sendBeacon` takes no headers and both it and `fetch` default to `*/*`). The
  UA blocklist, the `Accept-Language` check and the per-IP+UA daily dedup all apply
  unchanged.
- Known limit: a visitor with JavaScript disabled is not counted.

### 2. First-touch attribution

When no `es_attribution` cookie exists, the second script writes a session-scoped (no
expiry) first-party cookie on `config('session.domain')` holding the landing path, the
off-site referrer, the `utm_*` values and `ref` from the query string, JSON-encoded and
capped at ~2 KB.

It carries exactly what the server session used to hold for the marketing-to-signup hop, so
it is strictly necessary in the same sense the session cookie it stands in for was, and it
is deliberately **not** gated on cookie consent. The consented 30-day attribution cookies
`CaptureUtmParameters` writes are a different thing (cross-session marketing attribution)
and are unchanged.

`CaptureUtmParameters::clientAttribution()` reads it back defensively (malformed JSON
ignored, unknown keys dropped, every value through the same sanitiser and length caps), and
it is the **last** fallback: after the session and after the consented cookies.

`CaptureUtmParameters::handle()` applies that order once, for everyone, by seeding the
session from the cookie on the first request that has a real session - normally `/sign_up`
itself - before its own first-touch capture runs. Without that, the session had no landing
page (the marketing pages that had one were served from the edge), so the capture stored
`sign_up`, and `sign_up` then beat the cookie at every read site. Seeding puts the cookie
exactly where that session write used to go, which covers the sites that read the session
and the consented cookies only:

- `SocialAuthController::handleGoogleCallback()` (Google sign-up)
- `TicketController`'s two stub-account paths

Two sites additionally consult `clientAttribution()` themselves, as belt and braces:

- `RegisteredUserController::store()` (utm, referrer, landing page and `referral_code`)
- `EventController`'s two guest-submit account-creation paths

Because the browser writes it, it never appears in a server response and so cannot make a
page uncacheable. It is exempt from cookie encryption in `bootstrap/app.php` for the same
reason `cookie_consent` is: Laravel would silently drop a cookie it cannot decrypt.

## The CSP nonce

Everyone served the same cached copy shares one nonce for up to 10 minutes. Marketing pages
contain no user-controlled content - every inline script on them is authored in the repo -
and nothing that renders visitor-supplied content is cacheable, so a shared nonce cannot be
reused against a page an attacker can write into. The reasoning is repeated in
`SecurityHeaders.php` where the nonce is generated.

## What to watch after deploy

- `curl -sI https://eventschedule.com/pricing` - expect `cache-control: public, max-age=0,
  s-maxage=600`, no `set-cookie`, and `cf-cache-status: MISS` then `HIT` on the second call.
- `curl -sI 'https://eventschedule.com/pricing?lang=fr'` - expect `no-cache, private` and a
  `set-cookie`.
- `curl -sI -X POST https://eventschedule.com/marketing/visit` and
  `curl -sI https://eventschedule.com/docs/search-index.json` - expect no `set-cookie` on
  either. A `laravel_session` on either one takes the visitor off the edge for the rest of
  their session.
- `/admin/users` funnel: "Visited site", page views, docs and pricing buckets should stay in
  the same range as the week before. A sharp drop means the beacon is not firing (check the
  browser console for a CSP violation and the `marketing.visit` route for 419s or 429s); a
  sharp rise means something is double-counting.
- New sign-ups should keep non-null `utm_source` / `landing_page` / `referrer_url` at
  roughly the previous rate.
