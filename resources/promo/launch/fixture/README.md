# The launch film's copy of the app, and its plates

The film shows The Indigo Room, the venue the Product Hunt gallery shows, seeded the gallery kit's
way (`~/.claude/plans/product-hunt/`, another session's: read and copied, never edited or served).
Everything here runs against a private copy of the app with its own schema,
`eventschedule_test_launchfilm`. Nothing touches the dev database, the repo's `.env`, the Herd site
or `public/build`.

```bash
S=<scratchpad>; COPY=$S/launchfilm-app          # the copy's folder must end in launchfilm-app
cd resources/promo/launch
export LAUNCHFILM_SCRATCH=$S/launchfilm-capture  # Chrome's profile and the capture's temp files
```

## From nothing

```bash
bash fixture/make-copy.sh "$COPY" 8091           # clone the working tree, write .env, own schema, migrate
sed -i '' 's/:y="-10"/:y="-26"/' "$COPY/resources/js/components/SeatingPicker.vue"   # the kit's one staged edit, in the copy only
(cd "$COPY" && npm run build)                    # never npm install
node capture.mjs --poster                        # the Jazz Night poster: plates/art, fixture/poster
bash fixture/seed.sh "$COPY" onsale              # migrate:fresh, app:setup-demo, the kit's seeds, film.php
bash fixture/serve.sh "$COPY"                    # eight workers; proves the listener is this copy
```

`make-copy.sh` refuses ports 8000, 9515 and 8073. `serve.sh --status` and `--stop` go by PID and by
the listener's command line, never by port alone. The owner's login is written by the kit's
`retheme.php` to `~/.claude/plans/product-hunt-video/seed/.login`.

## The three states of the house

`bash fixture/seed.sh "$COPY" <state> --film` switches in a few seconds (it reruns `film.php` only).

| state | Jazz Night, tonight 8:00 PM | plates |
| --- | --- | --- |
| `onsale` | 96 of 150 sold (VIP 12, General Admission 84); C7 and C8 free | everything a visitor sees, the import, the newsletter, the embed dialog |
| `prescan` | 150 sold, 140 in; Avery Lane has C7 and C8 and is not scanned yet | `scan`, `scan-ok` (the scan is real, and checks them in) |
| `doors` | 150 sold, 142 in (VIP 16/16, General Admission 126/134); Avery Lane scanned a minute ago | `ticket`, `checkin`, `sales`, `realtime` |

`film.php` also: makes Jazz Night a seated event (one plan, one level, a STAGE block, VIP as four
tables of four, General Admission as rows A to H with a centre aisle, the lone-seat rule off),
turns every sale into a card sale, gives the venue 940 followers and a newsletter sent this morning
to all of them, adds three more things to book, renames what the demo left of its cartoon on the
other schedules, switches Realtime on, and writes `~/.claude/plans/product-hunt-video/fixture.json`,
which is where `plates.config.mjs` gets every address.

## Shooting

```bash
node capture.mjs --list                 # the plates; * marks the ones the seeded state can shoot
node capture.mjs                        # every plate of the seeded state
node capture.mjs --only=picker-0,ticket # some
node capture.mjs --sheet                # plates/_contact.jpg
```

The whole list, in order:

```bash
bash fixture/seed.sh "$COPY" onsale --film  && node capture.mjs
bash fixture/seed.sh "$COPY" prescan --film && node capture.mjs
bash fixture/seed.sh "$COPY" doors --film   && node capture.mjs
node capture.mjs --sheet
```

Each plate writes `plates/<id>.png`, `<id>.txt` (its visible text, which is linted for the demo's
cartoon names, local addresses, error text, the word "cash" and any email not on `.example`) and
its entry in `plates/MANIFEST.json`: the clip, the pixel size, rectangles of named parts, layers,
what was answered in the browser instead of by the app, and `staged`, which says plainly what in
the plate is not the app's own doing.

Things worth knowing before changing a plate:

- A capture reply above about 3 MB never arrives over the DevTools socket, and the session is dead
  after it. `grab()` shoots in bands and stitches them. Do not "simplify" it.
- A page left alone follows the Mac's appearance, which turns dark at sunset. Every plate sets its
  scheme outright.
- A pressed seat is held for that browser for twelve minutes and reads as taken to the next one:
  the seat plates run `fixture/release.php` first.
- A schedule page is shown in the schedule's own language; `?lang=` does not switch it. The
  language plates set the schedule's language with `fixture/lang.php` and put English back.
- The ticket's code is drawn by the server. The plate answers that one request in the browser with
  a code for `https://eventschedule.com` from the app's own generator.
- `realtime:simulate` writes visits that stop counting as "now" after about two minutes, and
  spreads them over eight schedules picked at random. The Realtime plate runs it itself, just
  before the shot, and then `fixture/visitors.php`, which brings those visits to the venue's pages.
- An owner's page has a 64 px bar stuck to the top of the window: a clip is scrolled to sit 88 px
  below the top (`clipTop`), or the bar covers its first rows.
- The door's scan is real. Chrome is given a stand-in camera that plays a clip made from the
  ticket's own code (`--use-file-for-fake-video-capture`), and the page reads it and checks the
  buyer in. `scan-byhand` is the fallback if the camera will not start (`--only=scan-byhand`).
- The schema is dropped by any PHPUnit run once it has been idle for three days
  (`tests/bootstrap.php` prunes `eventschedule_test_*`). `make-copy.sh` brings it back.
