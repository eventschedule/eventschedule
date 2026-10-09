# Plan, promote, sell: the Event Schedule showreel

A 28-second film built as one HTML page, so every frame is code: no After Effects project, no stock
footage, nothing to license. It is a short sales piece in the homepage's own look (Red Hat Display,
cool paper and navy, the "YOUR NAME" poster, the ticket, the 0% stamp, the room of 150 lights), cut
as about thirty shots on a half-second beat. It renders in two cuts on one timeline, light and dark,
and plays in the homepage's "See it in action" frame (`resources/views/marketing/index.blade.php`,
`initShowreel()` in `resources/js/marketing-home.js`), which shows the cut matching the site theme
and carries on from the same moment when the visitor switches.

| File in `public/videos/` | What it is |
|---|---|
| `event-schedule-showreel.mp4` / `.webm` | Dark cut, 1920x1080, 60fps, 28s, silent |
| `event-schedule-showreel-light.mp4` / `.webm` | Light cut, same |
| `event-schedule-showreel-mobile.mp4`, `-light-mobile.mp4` | 960x540, 30fps, served under 768px |
| `event-schedule-showreel-poster.jpg`, `-light-poster.jpg` | Posters: the "No platform fees." frame |

## Files

| File | What it is |
|---|---|
| `showreel.html` | The shell: the stage, the preview player, and the film's length (`DUR`, which `render.mjs` reads). |
| `film.css` | Every colour, as tokens, and the drawing of every object. |
| `js/lib.js` | Maths, easing, and `poses()`: a performance blocked in key poses. |
| `js/rig.js` | The set (a wall and a floor in real perspective), the camera, light and shadow, and `Paper`: a sheet made of hinged strips that can bend. |
| `js/film.js` | The film. `world(t)` draws everything that exists at time `t`; `SHOTS` is the cut. |
| `js/main.js` | `seek(t)` and the preview player. |
| `fonts/` | Red Hat Display (a copy of `public/vendor/fonts/Red_Hat_Display/`), Inter for the logo's wordmark, and in `p/` ten of the app's bundled display faces for the wall of posters. Copies, so the page opens straight from disk. |
| `img/` | Copies of `public/images/demo/demo_flyer_*.webp`, on some of the wall's posters. |
| `render.mjs` | Renders stills or the video through headless Chrome. No npm packages. |
| `blend.py` | Averages the motion-blur sub-frames (needs only Pillow). |

`seek(t)` draws the frame at time `t` as a pure function of `t`, so any frame can be rendered on its
own and renders the same twice. Nothing may remember the frame before.

## How the film is put together

There is one world: a wall with a month on it, a floor, the poster, and the things it makes (an
event card, copies of itself, a ticket, a stamp, a room of lights, a wall of other posters).
`world(t)` in `js/film.js` says what is there at `t` and what it is doing.

The cut is the `SHOTS` list under it. Each shot says when it starts, where the camera is (the point
it looks at, its zoom and roll, and any move inside the shot) and the one line it carries. A shot
runs until the next begins, and every cut is hard. To re-cut the film, edit that list: a new entry is
a new shot. Four kinds of shot have no camera, because they are full-frame cards: the three verbs,
the bill, and the logo.

`C` at the top of `js/film.js` holds the section starts. Everything else is timed from those.

## Preview

Open `showreel.html` in Chrome. Space plays or pauses, the arrow keys step one frame (Shift steps
half a second), the scrubber seeks, and T (or the half-moon button) switches between the dark and the
light cut on the same frame. URL parameters help when working on one section:

- `?t=16.6` opens paused on one frame
- `?from=13&to=17` loops one section
- `?theme=light` or `?theme=dark` picks the cut; without it the preview follows the OS setting

## Two cuts

Every colour lives in the tokens at the top of `film.css` (overridden under
`:root[data-theme="dark"]`) or, for what code paints on a canvas, in the palette object `F.T` in
`js/rig.js`. Never write a colour inline: give it a token, or the other cut silently keeps the wrong
value. The dark cut is not an inversion: glows add light (`'lighter'`) there, where the light cut
tints, and shadows are black instead of navy.

The door and the room are night in both cuts (`#stage.night`, `--night-wall`): the lights go down,
and what is bright is the room's own lights. Check both cuts side by side whenever a shot changes.

## Render

Needs Google Chrome, Python 3 with Pillow, and the ffmpeg that Playwright installs
(`~/Library/Caches/ms-playwright/ffmpeg-1011/ffmpeg-mac`; override with `FFMPEG=` or `CHROME=`). The MP4
needs x264, which Playwright's build lacks: `brew install ffmpeg` (or point `FFMPEG_FULL=` at one).

```bash
node render.mjs --preview --theme=light --out=preview.webm       # 960x540, 30fps, no blur, about a minute
node render.mjs --stills=1.6,16.6,20.7 --theme=light --out=stills  # PNG stills (half scale; --scale=1 for full)
```

All six outputs ship together, so re-render all of them:

```bash
V=../../../public/videos
node render.mjs --final --theme=dark --out=$V/event-schedule-showreel.webm --mp4=$V/event-schedule-showreel.mp4 \
                --poster=$V/event-schedule-showreel-poster.jpg --poster-at=16.6
node render.mjs --final --theme=light --out=$V/event-schedule-showreel-light.webm --mp4=$V/event-schedule-showreel-light.mp4 \
                --poster=$V/event-schedule-showreel-light-poster.jpg --poster-at=16.6
node render.mjs --mobile --theme=dark --mp4=$V/event-schedule-showreel-mobile.mp4
node render.mjs --mobile --theme=light --mp4=$V/event-schedule-showreel-light-mobile.mp4
```

`--from`/`--to` render a range, `--workers=N` sets parallel Chrome instances, `--port-base=N` moves
them off 9400 so two renders can run at once, `--keep` leaves the frame JPEGs behind for inspection.

Motion blur is real: each frame averages 8 renders spread across a 180-degree shutter, and 16 inside
the fast windows the page lists in `F.FAST` (the jump, the flying fields, the copies, the stubs, the
stamp, the whip). Add a window there when you add a fast move.

A frame's blur samples stay on its own side of a cut. The page lists its cuts in `F.CUTS` (built from
`SHOTS`), and `render.mjs` clamps each frame's samples to the shot the frame belongs to: a frame that
straddled a cut would average two shots into a one-frame dissolve.

The poster is a chosen moment (`--poster-at`), taken as its own clean still with no motion blur: it
is what a visitor sees before the reel starts, and all they ever see under reduced motion, Save-Data
or iOS Low Power Mode.

**After re-rendering, bump `$showreelV` in `resources/views/marketing/index.blade.php`.** The files keep
their names and are cached for a month, so that query string is the only thing that makes returning
visitors fetch the new cut.

## Rules the homepage puts on the film

- **Keep the bottom clear.** The page lays a veil over the bottom quarter of the picture and a
  "Watch the 3-minute overview" pill over its bottom centre. The ground line is at y = 790 of 1080,
  nothing that matters stands below it, and a shot's line sits at the top left.
- **The last frame is the first frame.** The page loops the reel. The film's last half second is a
  cut back to its opening shot, held, and every drift (the dust in the light) is periodic over `DUR`.
  Check with stills at `0` and `27.9999`: they should differ only by grain.
- **Nothing in the frame may look clickable.** The whole picture is a link to the overview video, so
  a button drawn in the film would promise a click it cannot keep. The address is lettered on the
  wall with a caret, not drawn as a field, and the logo card's lines are plain text.
- **It must read on a phone**, where the picture is about 358 CSS pixels wide. One film pixel is a
  fifth of a pixel there, so each shot carries one line at 62px or more, the verbs and the counts are
  far larger, and anything smaller (the slips on the calendar, a card's fields) is texture.

## Shot list

The cuts sit on a quarter-second grid (a 120 BPM track drops straight in).

| Time | Shot | Line |
|---|---|---|
| 0.00 | Close on the address, lettered on the wall. `blue-note` is typed | |
| 1.25 | The blank poster: the name fills it from the foot, a notch a letter | |
| 2.00 | Wide: it crouches, jumps and lands | Your events, live in minutes. |
| 3.00 | Four cuts, half a second each: a costume and a pose each | Made for a jazz club, a comedy club, a yoga studio, a street festival |
| 5.00 | Card | Plan. |
| 5.75 | Close on the poster as a beam reads it | Paste a poster. |
| 6.75 | Three fields fly into an event card | AI turns it into an event. |
| 7.75 | The event lands on the month, and the month fills | Your calendar, filled. |
| 9.00 | Open hours; a booking lands on Tuesday | Bookings, built in. |
| 9.75 | Card | Promote. |
| 10.50 | It breathes in and blows copies of itself out | Share it everywhere. |
| 11.25 | Four quarter-second close-ups: a phone, a QR code, an embed, an envelope | |
| 12.25 | Close on the count: 12,480 page views, 940 followers | Watch your audience grow. |
| 13.25 | Card | Sell. |
| 14.00 | A ticket hops out from behind the poster | Sell tickets. |
| 14.75 | Stubs spray off the ticket as the count runs to 150 | 150 sold. |
| 15.75 | A shadow grows, the 0% stamp lands (the poster frame is 16.6), a whip to the ticket | No platform fees. |
| 16.75 | Lights down. Very close on the QR code: a beam, a check | Scan at the door. |
| 17.50 | The one long shot: each guest comes in from the door as a light, and at 142 it jumps | 142 of 150 through the door |
| 21.50 | Card: the homepage's twelve names, on the beat | Also on the bill: twelve more |
| 23.50 | It hops up among 51 other posters; a light passes along the wall | Free and open source. Forever. |
| 26.00 | Card: the logo | Free event calendar. No credit card. |
| 27.50 | Back to the opening shot, held | |

## Copy rules

The film follows the repo's writing rules: no em-dashes, "selfhost" without a hyphen, and no prices
or currency symbols. Selling paid tickets is a Pro feature (`docs/FEATURES.md`), so the film says
"No platform fees." and never pairs "free" with selling tickets. The four casts, the twelve names on
the bill, "Free and open source. Forever." and "Free event calendar. No credit card." are the
homepage's own (`$hpCasts`, `$moreFeatures` and the hero in `index.blade.php`): change them there
first. The counts are the page's too: 150 tickets, 142 through the door.

The page follows the film in turn: its feature section is three acts, and each opens on the verb
card drawn here (the count, three bars, the verb; `hp-vcard` in `index.blade.php`). A change to the
cards or to the order of the acts wants the same change there.
