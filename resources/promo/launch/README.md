# Doors: the Event Schedule launch film

A 58-second film for the Product Hunt launch, built as one HTML page, so every frame is code: no
After Effects project, no stock footage, nothing to license. Unlike the homepage film beside it
(`../showreel`, drawn and silent), this one shows the real product: screens photographed from a
seeded copy of the app stand in a dark room as slabs of lit glass, and it has an original score.

It goes to YouTube at 2560x1440, 60 fps. Outputs are written to `storage/app/launch-video/`
(ignored by git); nothing here is served by the app.

## Files

| File | What it is |
|---|---|
| `launch.html` | The shell: the stage and the preview player. |
| `js/timeline.js` | The film's one clock, with no DOM in it: the cut (`SHOTS`), every caption, when each of the 142 arrives, the sound cues, and the SRT. Picture and sound both read it. |
| `js/lib.js` | Maths and easing (a copy of the showreel's). |
| `js/stage.js` | The camera, the floor, the light, and `Slab`: a real screen inside a box of glass. |
| `js/type.js` | The line each shot carries, and the hook's large lines. |
| `js/drawn.js` | What is drawn rather than photographed: the tap, the room's lights, the shockwave. |
| `js/film.js` | The shots. Each builds what it needs once and draws itself from `t` alone. |
| `js/audio/` | The score and the effects (Web Audio), and the preview's player. |
| `js/main.js` | `seek(t)`, `seekAsync(t)` and the preview. |
| `film.css` | Every colour, as tokens, and the drawing of every object. |
| `plates/` | The real screens (ignored by git). `plates/manifest.js` is written by `tools/variants.py`. |
| `capture.mjs`, `fixture/` | How the plates are shot: a served copy of the app with its own schema and the film's venue. |
| `render.mjs`, `blend.py` | Frames, through headless Chrome, with real motion blur. No npm packages. |
| `grade/grade.fg`, `encode.sh` | The grade (one ffmpeg graph for stills and for the film) and the deliverables. |
| `render-audio.mjs`, `master.mjs`, `analyze.mjs` | The score out of Chrome, its mastering, and the checks that stand in for ears. |
| `tools/verify.mjs` | The cut, the captions, the copy and a flash test. Run it before any render someone will watch. |
| `tools/variants.py` | For each plate: a softened copy, the light it throws, its tint and brightness; and the manifest. |
| `tools/grade-still.sh`, `tools/sheet.mjs` | A graded still, and a contact sheet of stills. |

`seek(t)` draws the frame at time `t` as a pure function of `t`. Images are a cache, not state:
`seekAsync(t)` draws, waits until every image the frame shows is decoded, and draws again.

## The cut

`TL.SHOTS` in `js/timeline.js`: 39 shots on a 120 BPM grid (a beat is half a second, a bar two).
A shot runs until the next begins and every cut is hard. Cuts sit on quarter seconds, which are
whole frames at 60 and at 30 fps. To re-cut, edit that list; to change what a shot shows, edit its
`shot('sNN', ...)` in `js/film.js`. Sound follows by itself, because the score reads the same file.

| Time | What happens | Line |
|---|---|---|
| 0 to 2 | The title (`s00`): the name and what it is, over the event page. It opens the film at Hillel's request; the first rough cut opened on the flyer | |
| 2 to 6 | A flyer, pasted into the real import box; its facts cross to the event; the event page locks together | Paste a flyer. / AI turns it into an event. |
| 6 to 14 | **Plan.** The month fills; the schedule's page; calendars sync; a time is booked (the first warm light) | |
| 14 to 24 | **Promote.** Four schedules, twelve languages, four ways to share, the newsletter, the people arriving | |
| 24 to 32 | **Sell.** Two seats are chosen, checkout, the ticket, into its code | |
| 32 to 38 | 0% platform fees; paid into your own account; selfhost it | |
| 38 to 41 | Lights down; the ticket is scanned | |
| 41 to 48 | The room: the seat plan becomes the floor and 142 of 150 arrive as lights | |
| 48 to 58 | Everything else; free and open source; the end card, over those lights | |

## Rules

- **The look.** Dark, never pure black (`--void`). Cool is the software, warm is people: warmth
  arrives with the booked time, the chosen seats and the room. One screen a slab, framed tight;
  never a shrunken full page, a drawn browser bar or an arrow cursor. Light screens are lamps: a
  slab holds a bright plate under the grade's bloom so its own text stays crisp.
- **Leaves only.** Inside `#scene` only transforms go on nodes that carry 3D children: a filter,
  an opacity, a mask, an overflow or a blend mode there flattens everything under it.
- **Images are `<img decoding="sync">`, never CSS backgrounds.** A frame is taken the moment it is
  drawn, and only an `<img>` can be waited on.
- **Captions.** One line a shot, lower left, clear of the bottom 15% (YouTube's bar), two lines of
  30 characters at most, on screen 0.3 s a word or more. The hook's two lines are large and in the
  middle band, because a player's bars cover the bottom of an embed for its first seconds.
- **Copy.** No em-dashes; "selfhost"; never "free" beside selling tickets (a priced ticket is Pro);
  always "platform fees", never "no fees" or "keep 100%"; no plan prices, competitor names or
  counts. `tools/verify.mjs` holds these.
- **What is drawn is the film's.** Labels the film adds ("Sent to 940 followers", the language
  count) are in the film's own type and never dressed as the app's interface.
- **Nothing from the demo.** The app's demo data is Simpsons-themed and must not appear. The
  venue is The Indigo Room, the same invented venue as the Product Hunt gallery
  (`~/.claude/plans/product-hunt/`), so the two match.

## Preview

Open `launch.html` in Chrome. Space plays and pauses, the arrows step a frame (Shift: a beat),
`[` and `]` jump a shot, and the menu picks a score (the picture then follows the audio clock).
`?t=32` opens paused on a frame.

## Render

Needs Google Chrome, Python 3 with Pillow, and a full ffmpeg (`brew install ffmpeg`).

```bash
node tools/verify.mjs                                              # the cut, the captions, the copy
node render.mjs --stills=0.3,32.6,46.9 --scale=1 --out=out/stills  # PNG stills
tools/grade-still.sh out/stills/t32.60.png out/graded.png          # one of them, graded
node render.mjs --preset=animatic --mp4=out/animatic.mp4 --audio=out/audio/dawn.master.wav   # 960x540, about a minute
node render.mjs --preset=master                                    # 2560x1440, 60 fps, motion blur: frames only
./encode.sh ~/.claude/plans/product-hunt-video/render/master-2560x1440-60 out/audio/dawn.master.wav ../../../storage/app/launch-video
```

Frames are kept (`~/.claude/plans/product-hunt-video/render/<preset>`), and a frame that exists is
not taken again, so a render can stop and carry on; `--fresh` forgets them, `--shots=s29,s30`
renders some. Motion blur is real: each frame averages 8 renders across a 180-degree shutter, 16
inside the windows `F.FAST` lists, and a frame's samples stay on its own side of a cut.

## Plates

`python3 tools/variants.py` after any plate changes. A shot names its plate by id and a stand-in
(`slabOf(set, 'ticket', 'ph/tickets', ...)`); when the real plate exists it is used. `plates/ph/`
holds the screens the gallery session shot of the same venue.
