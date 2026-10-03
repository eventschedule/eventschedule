# Booked Solid: the Event Schedule showreel

A 34-second motion piece built as one HTML page, so every frame is code: no After Effects project, no
stock footage, nothing to license. It renders in two cuts on one timeline, dark and light, and plays in
the homepage's "See it in action" frame (`resources/views/marketing/index.blade.php`, `initShowreel()` in
`resources/js/marketing-home.js`), which shows the cut matching the site theme and carries on from the
same moment when the visitor switches.

| File in `public/videos/` | What it is |
|---|---|
| `event-schedule-showreel.mp4` / `.webm` | Dark cut, 1920x1080, 60fps, 34s, silent |
| `event-schedule-showreel-light.mp4` / `.webm` | Light cut, same |
| `event-schedule-showreel-mobile.mp4`, `-light-mobile.mp4` | 960x540, 30fps, served under 768px |
| `event-schedule-showreel-poster.jpg`, `-light-poster.jpg` | Posters: the "Booked solid." frame |

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
half a second), the scrubber seeks, and T (or the half-moon button) switches between the dark and the
light cut on the same frame. URL parameters help when working on one section:

- `?t=14.5` opens paused on one frame
- `?from=8&to=13` loops one section
- `?theme=light` or `?theme=dark` picks the cut; without it the preview follows the OS setting

## Two cuts

Every colour lives in the CSS tokens on `:root` (overridden under `:root[data-theme="light"]`) or, for
what code paints at runtime, in the palette object `T`. Never write a colour inline: give it a token, or
the light cut silently keeps the dark value. The light cut is not an inversion. Additive glows
(`'lighter'`) vanish on a pale ground, so it paints the same marks source-over in deeper brand hues
(`tone()` maps each dark glow colour to its light one), takes depth from soft blue-grey shadows, and
deepens the cyan end of the gradient so text on white clears contrast. Check both cuts side by side
whenever you change a scene.

## Render

Needs Google Chrome, Python 3 with Pillow, and the ffmpeg that Playwright installs
(`~/Library/Caches/ms-playwright/ffmpeg-1011/ffmpeg-mac`; override with `FFMPEG=` or `CHROME=`). The MP4
needs x264, which Playwright's build lacks: `brew install ffmpeg` (or point `FFMPEG_FULL=` at one).

```bash
node render.mjs --preview --theme=light --out=preview.webm      # 960x540, 30fps, no blur, ~2 min
node render.mjs --stills=6.8,14.5,29.9 --theme=light --out=stills # PNG stills (half scale; --scale=1 for full)
```

All six outputs ship together, so re-render all of them (about 15 minutes on a 12-core machine):

```bash
V=../../../public/videos
node render.mjs --final --theme=dark --out=$V/event-schedule-showreel.webm --mp4=$V/event-schedule-showreel.mp4 \
                --poster=$V/event-schedule-showreel-poster.jpg --poster-at=7.5
node render.mjs --final --theme=light --out=$V/event-schedule-showreel-light.webm --mp4=$V/event-schedule-showreel-light.mp4 \
                --poster=$V/event-schedule-showreel-light-poster.jpg --poster-at=7.5
node render.mjs --mobile --theme=dark --mp4=$V/event-schedule-showreel-mobile.mp4
node render.mjs --mobile --theme=light --mp4=$V/event-schedule-showreel-light-mobile.mp4
```

`--from`/`--to` render a range, `--workers=N` sets parallel Chrome instances, `--port-base=N` moves
them off 9400 so two renders can run at once, `--keep` leaves the frame JPEGs behind for inspection.
Motion blur is real: each frame averages 8 renders spread across a 180-degree shutter, and 16 inside the
fast windows the page lists in `window.FAST` (whip pans, slashes, the dive, odometer rolls, the
pull-back). Fewer samples than that turn a whip into a row of ghosts instead of a smear, so add a
window there when you add a fast move.

The poster is a chosen frame (`--poster-at`), not the last one: it is what a visitor sees before the reel
starts, and all they ever see under reduced motion, Save-Data or iOS Low Power Mode.

**After re-rendering, bump `$showreelV` in `resources/views/marketing/index.blade.php`.** The files keep
their names and are cached for a month, so that query string is the only thing that makes returning
visitors fetch the new cut.

## Rules the homepage puts on the film

- **Keep the bottom clear.** The page lays a "Watch the 3-minute overview" pill over the bottom centre,
  which on a phone covers roughly y > 830 across the middle third. Nothing that matters goes below
  y = 840 (captions sit at the top).
- **The last frame is the first frame.** The page loops the reel, so the end card collapses into the
  same seed frame 0 opens on (built through the same camera rig, so it rasterizes identically), and
  every drift in the background is periodic over `DUR`. Check with stills at `0` and `33.9999`: they
  should differ only by grain.
- **Nothing in the frame may look clickable.** The whole picture is a link to the overview video, so a
  button drawn in the film would promise a click it cannot keep. The end card's line is plain text.

## Cue sheet

The cuts sit on a 120 BPM grid (one beat is 0.5s), so a track at that tempo drops straight in.

| Time | Hit |
|---|---|
| 0.00 | The seed, already lit, over a faint lattice of the month's days |
| 0.50, 1.00 | Seed pulses, rippling through the lattice |
| 1.50 | Detonation: a cell lands on every dot |
| 2.66, 3.14, 3.60, 4.03, 4.42, 4.78 | Whip-pan landings: Musicians, Venues, DJs, Food Trucks, Comedians, Yoga Studios |
| 5.00 to 5.50 | Pull back, the calendar books solid |
| 5.72 | "Your calendar." rises |
| 6.45 | "Booked solid." slams in (the poster frame is 7.5) |
| 7.86 | Slash 1 splits the headline |
| 9.00 to 9.60 | Poster scan |
| 9.50 to 10.25 | Fields fly into the form, Publish press |
| 10.62 | Event lands on Friday the 17th |
| 11.00 to 11.60 | Calendar sync pills, URL types out |
| 11.95 to 12.41 | Tickets button flips through the languages |
| 12.40 to 12.70 | Dive through the button |
| 13.25 to 13.80 | Ticket flips, tossed from centre to the left |
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
| 31.10 to 33.50 | Tagline, URL and hold |
| 33.50 to 34.00 | The end card implodes into the seed, and the loop begins again |

## Copy rules

The film follows the repo's writing rules: no em-dashes, "selfhost" without a hyphen, and no prices
or currency symbols. Selling paid tickets is a Pro feature (`docs/FEATURES.md`), so the film says
"No platform fees." and never pairs "free" with selling tickets. The language flip uses the app's own
`messages.tickets` strings from `resources/lang/`.
