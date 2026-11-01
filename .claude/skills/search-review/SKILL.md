---
name: search-review
version: 1.0.0
description: "The weekly Google Search Console review for eventschedule.com: pull the search data, compare it with last week's, check the content backlog and the pending fixes, and propose (or apply) changes that improve the site. Use when the user says 'weekly search review', 'search review', 'review the search data', 'how is SEO doing', or when the Monday search-data notification fires."
---

# Weekly search review

The goal is changes to the site, not a report: a title that matches what people search, a page
that needs content, a page that fell out of the index. Small and steady, once a week.

## 1. Load what we already know

Read these memories first (MEMORY.md links them):

- `project_seo_content_backlog`: the queries we rank on page 2 to 4 for, and what each needs.
- `project_search_console_seller_pages_2026_10`: earlier reads, the pages that fell out of Google
  and came back, and what to ignore.

Every fix listed there with a re-check date that has passed gets an answer in this review.

## 2. Pull

```bash
ls -la storage/app/growth/search-console/ | tail -5      # how fresh the latest pull is
php artisan app:pull-search-console --days=7             # this week vs last week
php artisan app:pull-search-console                      # 28 days vs the 28 before, for trends
php artisan app:pull-search-console --days=7 --inspect=/some-page   # is a page indexed
```

Never pass `--dir`. The data lags about three days, so "this week" ends three days ago. If it
fails, the command says why: a 403 means the service account is not a user on the property, a
missing key means `SEARCH_CONSOLE_CREDENTIALS` is not in `.env`.

For the searches behind a page, pull page and query pairs (read-only):

```bash
php artisan tinker --execute='file_put_contents(storage_path("app/growth/search-console/page-query.json"), json_encode(app(\App\Services\SearchConsole::class)->query(["page","query"], now()->subDays(30)->toDateString(), now()->subDays(3)->toDateString(), 25000)));'
```

Query the JSON with `python3` or `jq`; never Read a whole pull.

## 3. Read it with these rules (each was gotten wrong once)

- **Separate our pages from customers' schedule pages.** `*.eventschedule.com` schedule pages are
  a third of clicks and swing with their events' seasons and deletions. Judge the site on
  `eventschedule.com` and `blog.eventschedule.com` pages only.
- **Find the artifacts before reading CTR.** A competitor's brand query ("allevents api") put
  12,000 zero-click impressions on two of our pages and halved the site's CTR in October 2026.
  Look at the queries behind any page whose impressions jumped.
- **Small numbers.** A page with 5 clicks a week is noise week to week; use the 28-day pull for
  anything below about 20 clicks.
- **Position 1 with 0 clicks** is usually rank-tracking tools, not people. Do not act on it alone.
- **A deleted schedule answers 404 on purpose**, like a subdomain that never existed
  (`HiddenScheduleGuestRoutesTest`). It drops from Google on its own; never "fix" it with a 410.

## 4. What to look for, in order

1. **Indexing:** a page of ours whose impressions went to zero, or a sitemap with errors. Inspect
   it (`--inspect`). This is the most expensive kind of loss (the /luma-alternative fall of 2026).
2. **Title mismatches:** our page at position 3 to 10 for a real query with near-zero clicks. The
   fix is a title that leads with the query people typed, keeping the page's keyword from
   `config/marketing_keywords.php` (`MarketingKeywordMapTest`, `MarketingMetaLengthTest`).
3. **Content gaps:** position 11 to 40 with real impressions. Add to
   `project_seo_content_backlog`; write the page only when asked.
4. **Wins to protect:** pages that gained clicks. Note what changed on them.

## 5. Deliver

1. What moved since last week, in a few lines, with our own pages and schedule pages apart.
2. The fixes, ranked: what to change, the evidence (page, query, position, impressions), the
   expected effect, and when to re-check (about four weeks for a title or content change).
3. Apply the ones that are code changes when the user asks, following CLAUDE.md's marketing rules
   (`php artisan sitemap:lastmod` in the same commit as a marketing view change). A blog post's
   title is changed in `/admin/blog` (it has a search box), which is the user's to do.

## 6. Record it, privately

- Update `project_seo_content_backlog` (add new gaps, mark what shipped, with its commit and
  re-check date) and add the week's headline to `project_search_console_seller_pages_2026_10`.
- **Never commit figures from a pull.** The repository is public; pulls stay in
  `storage/app/growth/search-console/`, which is not in git.
