// The film's clock: one source for picture and sound. No DOM in here.
// 120 BPM, a beat is half a second and a bar two. Every cut sits on a quarter second, which is a
// whole frame at 60 and at 30 fps; sixteenths are for sound and motion only.
'use strict';
(() => {
const BPM = 120, BEAT = 60 / BPM, BAR = 4 * BEAT, DUR = 58;

// The cut. `cap` is the line a shot carries in the lower left (a string, or two lines); a line that
// carries across cuts is written once and repeated by reference, so it never re-animates.
// `big` puts the line at statement size in the middle band (the hook: YouTube's bars cover the
// bottom of an embed for its first seconds). `say` is what the SRT says when the picture's own
// type is the line (a card).
const L = {
  paste: 'Paste a flyer.',
  looks: ['For venues, performers', 'and communities.'],
  share: 'Share it everywhere.',
  door: 'Scan tickets at the door.',
  seats: 'Buyers pick their own seats.',
};
const SHOTS = [
  // The film opens on its title: the name and what the product is, before anything is shown.
  { id: 's00', a: 0, say: 'Event Schedule. Open source event calendar, ticketing and bookings.' },
  { id: 's01', a: 2, cap: L.paste, big: true, capAt: 2.42 },
  { id: 's02', a: 3, cap: L.paste, big: true },
  { id: 's03', a: 4, cap: 'AI turns it into an event.', big: true, capAt: 4.2 },
  { id: 's05', a: 6, say: 'Plan.' },
  { id: 's06', a: 7, cap: 'Your calendar, filled.', capAt: 7.85 },
  { id: 's07', a: 9, cap: 'Your events, live in minutes.' },
  { id: 's08', a: 10.5, cap: 'Syncs with Google Calendar.' },
  { id: 's09', a: 12, cap: 'Take bookings, too.' },
  { id: 's10', a: 14, say: 'Promote.' },
  { id: 's11', a: 15, cap: L.looks },
  { id: 's12', a: 15.5, cap: L.looks },
  { id: 's13', a: 16, cap: L.looks },
  { id: 's14', a: 16.5, cap: L.looks },
  { id: 's15', a: 17, cap: 'In 12 languages.' },
  { id: 's16', a: 18, cap: L.share },
  { id: 's17', a: 18.25, cap: L.share },
  { id: 's18', a: 18.5, cap: L.share },
  { id: 's19', a: 18.75, cap: L.share },
  { id: 's20', a: 19, cap: 'Newsletters, built in.' },
  { id: 's21', a: 21, cap: 'Watch your audience grow.' },
  { id: 's22', a: 22.5, cap: 'Every sale, as it lands.' },
  { id: 's23', a: 24, say: 'Sell.' },
  { id: 's24', a: 25, cap: 'Sell tickets.' },
  { id: 's25', a: 27, cap: L.seats },
  { id: 's26', a: 28, cap: L.seats },
  { id: 's27', a: 28.5, cap: 'A QR ticket for every buyer.', capAt: 28.75 },
  { id: 's28', a: 31 },
  { id: 's29', a: 32, say: '0% platform fees.' },
  { id: 's30', a: 33.5, cap: ['Paid straight into your own', 'Stripe or PayPal.'] },
  { id: 's31', a: 36, cap: ['Selfhost it.', 'Every paid feature included.'] },
  { id: 's32', a: 38, cap: L.door },
  { id: 's33', a: 39, cap: L.door },
  { id: 's34', a: 39.5, cap: L.door },
  { id: 's35', a: 40, cap: L.door },
  { id: 's36', a: 41, count: true, say: '142 of 150 through the door.' },
  { id: 's37', a: 48, cap: 'And a lot more.' },
  { id: 's38', a: 50, say: 'Free and open source. Forever.' },
  { id: 's39', a: 52, say: 'Plan, promote, and sell from your event calendar. Free event calendar. No credit card. eventschedule.com' },
];
SHOTS.forEach((s, i) => { s.i = i; s.b = SHOTS[i + 1] ? SHOTS[i + 1].a : DUR; });
const shotAt = t => { let s = SHOTS[0]; for (const k of SHOTS) if (t >= k.a) s = k; return s; };
const byId = Object.fromEntries(SHOTS.map(s => [s.id, s]));
// where a carried line began (the first shot of its run)
const capStart = s => { let k = s; while (k.i > 0 && SHOTS[k.i - 1].cap === s.cap && !!SHOTS[k.i - 1].big === !!s.big) k = SHOTS[k.i - 1]; return k.capAt != null ? k.capAt : k.a + 2 / 60; };

/* ---------- the room: when each of the 142 arrives ----------
   Avery Lane's two seats light with the scan; eight arrivals play the score's hook on eighths;
   then the rush, landing #142 exactly on the bar line. */
const ROOM = { a: 41, tiltA: 41.5, tiltB: 43.5, rushA: 44, peak: 46, total: 150, in: 142 };
const ARRIVALS = (() => {
  const t = [41, 41.25];
  for (let k = 0; k < 8; k++) t.push(41.5 + k * .25);
  const n = ROOM.in - t.length, e3 = Math.exp(3);
  for (let i = 1; i <= n; i++) t.push(ROOM.rushA + 2 * Math.log(1 + (i / n) * (e3 - 1)) / 3);
  return t;
})();
const through = t => { let n = 0; for (const a of ARRIVALS) if (t >= a) n++; return n; };

/* ---------- the feature names that land in s37 ---------- */
const MORE = ['Recurring Events', 'Online Events', 'Gift Cards & Passes', 'Event Polls', 'Custom Fields', 'Private Events', 'Sub-schedules', 'Team Scheduling', 'White-label Branding', 'Fan Videos & Comments'];
const MORE_T = [48, 48.25, 48.5, 48.75, 49, 49.125, 49.25, 49.375, 49.5, 49.625];

/* ---------- sound cues ----------
   {t, type, intensity, dur?, pan?, n?}. The score reads these, so a sound cannot drift from the
   picture: move a moment here and both move. Whooshes only on the three whips. */
const CUES = [];
const cue = (t, type, intensity = 1, o = {}) => CUES.push({ t, type, intensity, ...o });
// the title, then the hook: the flyer at 2.0, into the box at 2.5, read by 4.0, the page locked at 5.0
cue(0, 'subdrop', .5); cue(2, 'tap', .4); cue(2.5, 'tap', .9);
[3.25, 3.5, 3.75].forEach((t, n) => cue(t, 'tick', .8, { n, pan: -.3 + n * .3 }));
cue(5, 'chime', .45);
// plan
cue(6, 'subdrop', .6);
cue(7.25, 'tap', .8);
for (let n = 0; n < 8; n++) cue(7.5 + n * .125, 'count', .7, { n });
cue(8.5, 'tap', .6);
for (let n = 0; n < 5; n++) cue(9.25 + n * .25, 'tick', .6, { n, pan: -.2 });
cue(10.75, 'tick', .8, { n: 0, pan: -.5 }); cue(11.25, 'tick', .8, { n: 1, pan: .5 }); cue(11.5, 'tap', .6);
cue(12.5, 'tap', .7); cue(13, 'chime', .4);
// promote
cue(14, 'subdrop', .8);
[15, 15.5, 16, 16.5].forEach((t, n) => cue(t, 'tick', .9, { n, pan: n % 2 ? .4 : -.4 }));
for (let n = 0; n < 8; n++) cue(17 + n * .125, 'count', .6, { n });
[18, 18.25, 18.5, 18.75].forEach((t, n) => cue(t, 'tick', .8, { n, pan: n % 2 ? .4 : -.4 }));
cue(19.5, 'tap', .8); cue(20, 'tick', 1, { n: 2 });
cue(21, 'whoosh', .8, { dur: .4, pan: 1 });
for (let n = 0; n < 6; n++) cue(21.25 + n * .125, 'count', .6, { n });
cue(23, 'chime', .4);
// sell
cue(24, 'subdrop', 1);
cue(25.5, 'tap', .7); cue(25.5, 'seat', .9, { n: 0 }); cue(26, 'tap', .7); cue(26, 'seat', .9, { n: 1 }); cue(26.5, 'tick', .7, { n: 2 });
cue(28, 'tap', 1);
cue(28.5, 'whoosh', .8, { dur: .4, pan: -1 });
cue(29, 'chime', .5); cue(30, 'tick', .7, { n: 1 });
cue(31, 'riser', .9, { dur: .75 });
cue(32, 'stamp', 1);
for (let n = 0; n < 6; n++) cue(33.75 + n * .25, 'tick', .5, { n, pan: .35 });
{ const r = [.1, .19, .27, .4, .49, .56, .7, .78, .9, .98, 1.08, 1.2, 1.29, 1.37]; r.forEach((d, n) => cue(36 + d, 'tick', .25, { n: n % 3, pan: -.3 })); }
cue(37.5, 'tap', .6);
// the door
cue(38, 'powerdown', 1);
cue(39, 'tick', .7, { n: 0 }); cue(39.5, 'scan', 1, { dur: .5 }); cue(40, 'chime', 1);
cue(41, 'whoosh', .7, { dur: .4, pan: 0 });
ARRIVALS.forEach((t, n) => cue(t, 'seat', n < 2 ? 1 : n < 10 ? .9 : .6, { n, pan: ((n * 37) % 100) / 50 - 1 }));
cue(ROOM.rushA, 'shimmer', 1, { dur: ROOM.peak - ROOM.rushA });
cue(ROOM.peak, 'peak', 1);
// out
MORE_T.forEach((t, n) => cue(t, 'tick', .7, { n, pan: n % 2 ? .35 : -.35 }));
cue(50, 'subdrop', .5);
cue(51, 'riser', .6, { dur: .75 });
cue(52, 'logo', 1);
cue(52.5, 'tick', .4, { n: 0 }); cue(52.75, 'tick', .4, { n: 1 }); cue(53.5, 'tick', .4, { n: 2 });
CUES.sort((p, q) => p.t - q.t);

/* ---------- captions as an SRT (YouTube's closed captions) ---------- */
function srt() {
  const out = [], stamp = t => { const ms = Math.round(t * 1000), h = Math.floor(ms / 3600000), m = Math.floor(ms / 60000) % 60, s = Math.floor(ms / 1000) % 60; return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')},${String(ms % 1000).padStart(3, '0')}`; };
  let i = 0;
  while (i < SHOTS.length) {
    const s = SHOTS[i], text = s.cap ? [].concat(s.cap).join('\n') : s.say;
    if (!text) { i++; continue; }
    let j = i; while (s.cap && SHOTS[j + 1] && SHOTS[j + 1].cap === s.cap) j++;
    out.push({ a: s.cap ? capStart(s) : s.a, b: SHOTS[j].b, text });
    i = j + 1;
  }
  return out.map((c, n) => `${n + 1}\n${stamp(c.a)} --> ${stamp(c.b - .04)}\n${c.text}\n`).join('\n');
}

const TL = { BPM, BEAT, BAR, DUR, SHOTS, shotAt, byId, capStart, ROOM, ARRIVALS, through, MORE, MORE_T, CUES, srt, CUTS: SHOTS.slice(1).map(s => s.a) };
if (typeof window !== 'undefined') { window.TL = TL; window.CUES = CUES; }
if (typeof module !== 'undefined') module.exports = TL;
})();
