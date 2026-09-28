# Booked Solid: the Event Schedule showreel

A 33-second motion piece built as one HTML page, so every frame is code: no After Effects project, no
stock footage, nothing to license. The rendered result is `public/videos/event-schedule-showreel.mp4`
and `.webm` (1920x1080, 60fps, 33s, silent) with `public/videos/event-schedule-showreel-poster.jpg` as its
poster. It plays in the homepage's "See it in action" frame (`resources/views/marketing/index.blade.php`,
`initShowreel()` in `resources/js/marketing-home.js`).

## Files

| File | What it is |
|---|---|
| `showreel.html` | The whole film. `seek(t)` draws the frame at time `t` as a pure function of `t`, so any frame can be rendered on its own and renders the same twice. |
| `InterVariable.woff2` | A copy of `resources/fonts/InterVariable.woff2`, so the page opens straight from disk. The weight axis is animated. |
| `img/` | Downscaled copies of `public/images/screenshots/` guest-portal shots, used for the wall of schedules. |
| `render.mjs` | Renders stills or the video through headless Chrome. No npm packages. |
| `blend.py` | Averages the motion-blur sub-frames (needs only Pillow). |

## Preview

Open `showreel.html` in Chrome. Space plays or pauses, the arrow keys step one frame (Shift steps
half a second), and the scrubber seeks. URL parameters help when working on one section:

- `?t=14.5` opens paused on one frame
- `?from=8&to=13` loops one section

## Render

Needs Google Chrome, Python 3 with Pillow, and the ffmpeg that Playwright installs
(`~/Library/Caches/ms-playwright/ffmpeg-1011/ffmpeg-mac`; override with `FFMPEG=` or `CHROME=`). The MP4
needs x264, which Playwright's build lacks: `brew install ffmpeg` (or point `FFMPEG_FULL=` at one).

```bash
node render.mjs --preview --out=preview.webm        # 960x540, 30fps, no blur, ~2 min
node render.mjs --final --out=../../../public/videos/event-schedule-showreel.webm \
                --mp4=../../../public/videos/event-schedule-showreel.mp4 \
                --poster=poster.jpg                  # 1080p60, 8 to 16-sample motion blur, ~8 min
node render.mjs --stills=6.8,14.5,29.9 --out=stills  # PNG stills
```

`--from`/`--to` render a range, `--workers=N` sets parallel Chrome instances, `--keep` leaves the
frame JPEGs behind for inspection. Motion blur is real: each frame averages 8 renders spread across a
180-degree shutter, and 16 inside the fast windows the page lists in `window.FAST` (whip pans, slashes,
the dive, odometer rolls, the pull-back). Fewer samples than that turn a whip into a row of ghosts
instead of a smear, so add a window there when you add a fast move.

**After re-rendering, bump `$showreelV` in `resources/views/marketing/index.blade.php`.** The files keep
their names and are cached for a month, so that query string is the only thing that makes returning
visitors fetch the new cut. Copy the new poster (`--poster`, the last frame) to
`public/videos/event-schedule-showreel-poster.jpg` too.

## Cue sheet

The cuts sit on a 120 BPM grid (one beat is 0.5s), so a track at that tempo drops straight in.

| Time | Hit |
|---|---|
| 0.50, 1.00 | Seed pulses |
| 1.50 | Detonation into the calendar grid |
| 2.66, 3.14, 3.60, 4.03, 4.42, 4.78 | Whip-pan landings: Musicians, Venues, DJs, Food Trucks, Comedians, Yoga Studios |
| 5.00 to 5.50 | Pull back, the calendar books solid |
| 6.45 | "Booked solid." slams in |
| 7.86 | Slash 1 splits the headline |
| 9.00 to 9.60 | Poster scan |
| 9.50 to 10.25 | Fields fly into the form, Publish press |
| 10.62 | Event lands on Friday the 17th |
| 11.00 to 11.60 | Calendar sync pills, URL types out |
| 11.95 to 12.41 | Tickets button flips through the languages |
| 12.40 to 12.70 | Dive through the button |
| 13.25 to 13.72 | Ticket flips |
| 14.42 | Check-in (impact, camera shake) |
| 15.35 | "No platform fees." |
| 16.02 | Slash 2 opens the feature run |
| 16.50 to 24.00 | Ten feature cuts, 0.75s (1.5 beats) each |
| 23.95 to 25.30 | Infinite zoom out to the wall of schedules |
| 26.05 | "Forever." |
| 28.00 to 28.50 | Wall implodes to a point |
| 28.62 | Slash 3 |
| 29.20 | Badge lands |
| 30.20 | Wordmark reveals |
| 31.10 to 33.00 | Call to action and hold |

## Copy rules

The film follows the repo's writing rules: no em-dashes, "selfhost" without a hyphen, and no prices
or currency symbols. Selling paid tickets is a Pro feature (`docs/FEATURES.md`), so the film says
"No platform fees." and never pairs "free" with selling tickets. The language flip uses the app's own
`messages.tickets` strings from `resources/lang/`.
