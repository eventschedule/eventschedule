---
name: growth-review
version: 1.0.0
description: "Pull and analyse Event Schedule's growth data to decide what to build or change. Use whenever the user asks how to grow, improve conversion or retention, get more organizers selling tickets or paying, what to build next, how a release or experiment did, or mentions signups, the funnel, MRR, churn, sellers, comps, traffic, acquisition channels, the hero test, or 'pull the growth data'."
---

# Growth review

The goal is decisions, not reports: what to build or change so more organizers sign up, publish,
**sell tickets**, and pay. The data is the pseudonymous growth payload from the hosted install.
Read `docs/GROWTH_DATA.md` for what each field means.

## 1. Load what we already know

Before touching data, read the growth memories. `hub_growth_funnel` in MEMORY.md links them all:

- the most recent `project_growth_*` read, which holds the last findings and what shipped in response;
- `project_growth_experiments_ledger`, which holds the open hypotheses, each with the metric it should
  move and when to check it.

Every hypothesis whose check date has passed gets an answer in this review: confirmed, refuted, or
"can't tell yet" with the reason.

## 2. Get the data

```bash
ls -la storage/app/growth/ 2>/dev/null | tail -5   # what is already here, and how old
php artisan app:pull-growth                        # download + summary vs the previous pull
php artisan app:pull-growth --local                # summary only, if latest.json is under a day old
php artisan app:pull-growth --range=last_90_days   # a wider funnel window, when 30 days is too thin
```

If the pull fails, the command names the problem.
- **404:** the server has no `GROWTH_DATA_TOKEN`, or it is not deployed yet.
- **401:** the token here differs from the server's.
- **429:** wait, then retry.

Walk the user through `docs/GROWTH_DATA.md#setup`. Never improvise requests against production to
debug it. A malformed request pages the team through Sentry.

## 3. Read the data dictionary, then query, never Read

Read `docs/GROWTH_DATA.md`, at least **Populations and windows** and **Caveats**. Then read
`jq '.meta.notes' storage/app/growth/latest.json`, because the notes are the caveats that travel
with this exact pull.

The file is over 1MB of row tables. Do not open it with Read. Query it with `jq` or `python3`:

```bash
F=storage/app/growth/latest.json
jq '.meta | {schema_version, app_version, generated_at, partial_month, truncated}' $F
jq '.funnel.stages[] | [.key, .count, .step_conv] | @tsv' -r $F
jq '.monetization | del(.gmv_by_currency)' $F
jq -r '.traffic[] | [.month, .visitors_basis, .visitors, .signup_views, .verified_signups] | @tsv' $F
jq '.acquisition.by_referrer_channel' $F
```

For the row tables, turn them into dicts:

```python
import json; d = json.load(open('storage/app/growth/latest.json'))
rows = lambda t: [dict(zip(d[t]['columns'], r)) for r in d[t]['rows']]
S, U = rows('schedules'), rows('signups')

# The partition that has mattered most so far: does selling predict paying?
def seg(s):
    if s['paid_tickets_90d'] > 0: return '1 sold in 90d'
    if s['first_paid_sale_month']: return '2 sold, not recently'
    if s['paid_ticket_types'] > 0: return '3 paid ticket type, never sold'
    if s['ticket_types'] > 0: return '4 free ticket types only'
    return '5 no ticket type'
from collections import Counter
n, pay = Counter(seg(s) for s in S), Counter(seg(s) for s in S if s['billing'])
for k in sorted(n): print(k, n[k], pay[k], f"{pay[k]/n[k]:.1%}")

# Sellers, the accounts the ticketing business actually is
for s in sorted(S, key=lambda s: -s['paid_tickets_90d'])[:15]:
    print(s['sid'], s['type'], s['plan'], s['billing'], s['paid_tickets_90d'], s['gmv_recent_by_currency'])

# Channel -> activation -> selling, organizers only
org = [u for u in U if u['signup_intent'] in (None, 'organizer')]
by = Counter(u['referrer_channel'] or '(none)' for u in org)
```

To compare two pulls, keep the same `schema_version`; ids changed length at 8. Join on `sid` to
find sellers who stopped, comps that started paying, and schedules that newly sold.

## 4. Put the numbers next to what shipped

- `git log --since=<previous pull date> --oneline` shows what changed in between.
- `git tag --sort=-creatordate --format='%(refname:short) %(creatordate:short)' | head` gives
  release dates.
- Deploys are manual, so a tag date is only an upper bound on when something went live.
  `meta.app_version` is what was live for this pull.

## 5. Discipline (each of these has been gotten wrong before)

- **Paying is `billing`, not `plan`.** Most paid-tier schedules have been admin comps. Before schema
  8, `payers_vs_free` compared comps with free schedules.
- **Populations differ.** `funnel` is the organizer cohort in the range. `activation`, `cohorts` and
  `acquisition` are every verified user, all time. Never divide one by the other.
- **Small numbers.** Conversions, sellers and cancellations are single digits per month. At one a
  month, Poisson puts P(0) at about 37%. Always give counts with their denominators. Say "can't tell
  yet" instead of reading an effect into n < 10.
- **Partial month.** `meta.partial_month` is month-to-date. Compare it to the same days of the
  previous month, or not at all.
- **Visitor basis.** Never compare visit counts across `visitors_basis` values; the 2026-09-07 beacon
  rebase is unexplained.
- **Instrumentation age.** A counter that is null, or recently zero, may simply be young. Check
  `meta.notes` and the column's tracked-from date before calling it a broken feature.
- **Verify in code before calling something a bug.** Read the code path. Two "obvious bugs" in past
  reads were designed behaviour.
- **Data artifacts are not findings.** If a number looks wrong, suspect the definition first, for
  example demo sales or capped rows (`meta.truncated`).

## 6. What to deliver

1. **What moved** since the last pull: the summary table, plus anything the row diffs show.
2. **The binding constraint** right now, in one sentence with its numbers.
3. **3-5 ranked actions.** For each:
   - what to build or change;
   - the evidence for it;
   - the metric it should move, and the expected direction and rough size;
   - how and when we will know;
   - effort.

   Prefer actions aimed at the binding constraint over polish elsewhere.
4. **Data gaps:** questions this data cannot answer, and the smallest instrumentation that would. If
   it is worth building, follow "Changing the payload" in `docs/GROWTH_DATA.md`.

## 7. Record it, privately

- Save the conclusions as a dated `project_growth_*` memory, or update the latest one, and link it
  from `hub_growth_funnel`.
- Add each new hypothesis to `project_growth_experiments_ledger`, with its metric, expected direction
  and check-after date. Mark the ones this review resolved.
- **Never put figures from a pull in a committed file.** The repository is public.
- **Never try to re-identify a `uid` or `sid`**, and never send row-level data to an outside
  service. If a decision needs to know which account a `sid` is, ask the user; they can look it up
  on the server.
