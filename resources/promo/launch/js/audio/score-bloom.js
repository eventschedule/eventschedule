// "Bloom": half-time and spacious. A felt piano plays the hook, a slow wide pad breathes under
// it, an eighth-note ostinato on D and A keeps time, and the drums are a deep kick on one and a
// snap on three. The film's fast cuts ride over a slow groove on purpose.
//
// The composer's score B, on the approved 29-bar form (A.ROOTS), with the same hit points,
// silences and effects track as the other two scores. The piano's motif (P1) over G then Bm is
// the shared hook (A.HOOK), so the first eight arrivals in the room finish the tune:
//   G  bar: F#4 . . A4 B4 . D5       left hand G2 D3      Bm bar: F#5 . . . . E5 D5      B2 F#3
//   D  bar: A4 . . D5 F#5 . E5        left hand D3 A3      A  bar: E5 . . B4 . A4 (E4)    A2 E3
// Every second time round the A bar ends on D5 instead of A4.
//
// By section (bars; a bar is 2 s):
//   1-3   intro      the title: pad from nothing and one soft, low chord; the accent chord on the cut at 2.0,
//                    F#5 at 2.5, then the A bar with a bell-like fifth on the lock at 5.0
//   4-7   verse 1    the motif alone, then with the left hand; kick on one; snap and shaker from bar 6
//   8-11  verse 2    the motif an octave up; the ostinato enters
//   12    lift       G then A, sixteenths climbing, a swell into 24.0
//   13-16 chorus 1   the motif in octaves; kick on one and the "and" of three; bar 16 thins and stops dead at 31.75
//   17-19 stamp      32.0 belongs to the stamp: no kick, no bass, one big D chord; then the motif, single and soft, rising
//   20-21 breakdown  a thin fifth, then nothing: the chime at 40.0 has the room to itself; sub A at 41.0
//   22-23 build      two notes while the first eight seats play the hook; then eighths, sixteenths, the kick doubling
//   24    peak       46.0: one six-note chord, held, and half a second of tails
//   25    chorus 2   the hook in eighths, in octaves, for one bar
//   26    wide       D then A, single notes, and silence at 51.75
//   27-29 button     a rolled D chord at 52.0, ringing to the end of the film
'use strict';
(() => {
const A = window.A, E = A.E, V = E.V, { db, bq, seeded } = E, bar = A.bar;
const CH = { G: [55, 62, 69, 71, 78], Bm: [57, 62, 66, 71, 76], D: [54, 62, 64, 69, 78], A: [52, 57, 59, 64, 69], Em: [55, 59, 62, 64, 66], A2: [57, 64, 69, 74, 76], BIG: [50, 57, 62, 66, 69, 76], THIN: [69, 76], BTN: [50, 57, 62, 66, 69, 76] };
const RH = { G: [[0, 66], [6, 69], [8, 71], [12, 74]], Bm: [[0, 78], [10, 76], [12, 74]], D: [[0, 69], [6, 74], [8, 78], [12, 76]], A: [[0, 76], [8, 71], [12, 69], [14, 64, .6]] };
const LH = { G: [43, 50], Bm: [47, 54], D: [50, 57], A: [45, 52], Em: [52, 59] };
const bright = E.bp([[0, .1], [6, .3], [14, .45], [22, .6], [24, .8], [31.75, .85], [32, .95], [33.5, .4], [38, .3], [38.01, .2], [41, .2], [46, 1], [50, 1], [50.01, .55], [52, .7], [58, .3]]);

A.SCORES = A.SCORES || {};
A.SCORES.bloom = {
  id: 'bloom', name: 'Bloom', bright,
  duck: { bass: [-12, 100], music: [-7, 150] },
  // a ride per section, in dB (music, bass and drums; not the effects), set by measurement
  section: E.bp([[0, -1.5], [6, -1.5], [6.02, -3.4], [14, -3.4], [14.02, -2.9], [22, -2.9], [22.02, -2.1], [24, -2.1], [24.02, -0.2], [42, -0.2], [42.02, -5.5], [44, -5.5], [44.02, -3.7], [46, -3.7], [46.08, 1.8], [47.5, 1.8], [48, -0.7], [50, -0.7], [50.02, -4.4], [51.75, -4.4], [52, -2.5], [58, -2.5]]),
  // per-part trims in dB, set by measurement (render-audio.mjs --parts, analyze.mjs --parts)
  trim: { kick: -4, snap: 6, clap: 6, shaker: 12.5, sub: -1, piano: 3, pad: -1.5, ost: 10, bell: -8, air: 2, bloom: 0, swell: 0, noise: 0 },
  arrange(S) {
    const ctx = S.ctx, I = S.inst, T = this.trim, B = S.bus;
    I('kick', 'drums', { level: -9, trim: T.kick }); I('snap', 'drums', { level: -17, P: -7, H: -14, trim: T.snap }); I('clap', 'drums', { level: -25, P: -9, H: -16, trim: T.clap }); I('shaker', 'drums', { level: -30, P: -16, trim: T.shaker });
    I('sub', 'bass', { level: -14, trim: T.sub });
    I('piano', 'music', { level: -17, H: -9, D: -16, trim: T.piano }); I('pad', 'music', { level: -33, H: -5, trim: T.pad }); I('ost', 'music', { level: -26, P: -12, trim: T.ost }); I('bell', 'music', { level: -26, D: -10, H: -10, trim: T.bell });
    I('air', 'music', { level: -39, H: -4, trim: T.air }); I('bloom', 'music', { level: 0, H: -3, trim: T.bloom }); I('swell', 'music', { level: -20, H: -10, trim: T.swell }); I('noise', 'music', { level: 0, H: -6, trim: T.noise });
    const padIn = ctx.createGain(), padLP = bq(ctx, 'lowpass', 400, -3), padHP = bq(ctx, 'highpass', 150, -3); padIn.connect(padLP); padLP.connect(padHP); padHP.connect(B.pad); E.setCurve(padLP.frequency, E.fnCurve(t => 400 * Math.pow(6, bright(t))));
    const pianoIn = bq(ctx, 'highpass', 90, -3); pianoIn.connect(B.piano);
    S.keep.push(padIn, padLP, padHP, pianoIn); S.alive(padIn); S.alive(pianoIn);

    // The last chord (52.0) rings to the end of the film, under the end card. Each of its voices falls as it
    // always did until it is well down (the pad 12 dB, the piano, the bell and the sub 20), and only then slows: the hit and its
    // first two seconds are what they were, and what rings on sits under the loudness gate, so the film's
    // level into the limiter does not move. The master's closing fade (56.5 to 57.95) was always there.
    const LAST = { pad: { s: .25, r: 20000 }, note: { s: .1, r: 12000 } };
    /* ----- pad: [from, to, chord, attack ms, release ms, dB, ring] ----- */
    const pads = [[0, 4, 'D', 1900, 1200, -1], [4, 6, 'A', 900, 1200, 0]];   // from nothing under the title, full on the cut at 2.0
    for (let b = 4; b <= 11; b++) pads.push([bar(b), bar(b + 1), A.chordAt(b), 900, 1200, 0]);
    pads.push([22, 23, 'G', 700, 900, 0], [23, 24, 'A', 700, 500, 1]);
    for (let b = 13; b <= 15; b++) pads.push([bar(b), bar(b + 1), A.chordAt(b), 900, 1200, 0]);
    pads.push([30, 31.7, 'A', 700, 40, 0], [32, 34, 'BIG', 8, 1400, -2], [34, 36, 'G', 900, 1200, 0], [36, 38, 'A', 900, 300, 0], [38.2, 39.75, 'THIN', 900, 200, -8],
      [41, 42, 'A', 900, 1200, -3], [42, 44, 'Em', 900, 1200, 0], [44, 45, 'G', 500, 800, 1], [45, 46, 'A', 400, 300, 2], [46, 47.35, 'A2', 20, 200, 0],
      [48, 49, 'G', 60, 600, 1], [49, 50, 'Bm', 60, 600, 1], [50, 51, 'D', 300, 800, 0], [51, 51.7, 'A', 300, 40, 0], [52, 53.5, 'BTN', 8, 3500, 1, LAST.pad]);
    for (const [a, b, c, at, rel, lv, ring] of pads) V.pad(S, padIn, a, CH[c], { a: at, r: rel, gate: b - a, det: 11, width: .7, vel: db(lv), swell: b - a >= 1.9, ring });

    /* ----- the piano. A note off a bar line breathes by up to 8 ms; a chord is rolled low to high. ----- */
    const pn = (t, m, v, exact, ring) => { const r = seeded('pn' + t.toFixed(3) + ':' + m), tt = exact ? t : Math.max(0, t + (r() - .5) * .016); V.felt(S, pianoIn, tt, m, v * (.9 + .2 * r()), ring); S.mark('piano', A.snap(tt), 'music'); };
    const roll = (t, ms, v, ring) => ms.forEach((m, i) => pn(t + i * .008, m, v, true, ring));
    const motif = (b, chord, v, o = {}) => {
      (o.end && chord === 'A' ? [[0, 76], [8, 71], [12, 74]] : RH[chord]).forEach(([s, m, k]) => { if (o.until != null && s > o.until) return; pn(bar(b, s), m + (o.up || 0), v * (k || 1), s === 0); if (o.oct) pn(bar(b, s), m + 12, v * .5 * (k || 1), s === 0); });
      if (o.lh) roll(bar(b), LH[chord], v * o.lh);
    };
    // The film opens on its title (0.0 to 2.0): the pad from nothing and one soft, warm arrival, a low D chord
    // rolled quietly. The tune starts on the cut to the flyer: the accent chord at 2.0, F#5 over F#4 as the flyer snaps
    // into the import box (2.5), two falling notes while the app reads it, E5 and the left hand on the cut
    // at 4.0, a bell-like fifth on the lock at 5.0 (where the A bar's B4 would be), and the bar's last two notes.
    roll(0, [50, 57, 62, 66], .45);
    [62, 69, 74].forEach((m, i) => pn(2 + i * .004, m, .85, true));   // on a hard cut the chord is rolled twice as fast: all of it inside half a frame
    pn(2.5, 78, 1, true); pn(2.5, 66, .5, true); pn(3, 74, .45); pn(3.5, 69, .55);
    motif(3, 'A', .8, { lh: .7, until: 0 }); roll(5, [81, 86], 1); pn(5.5, 69, .8); pn(5.75, 64, .48);
    motif(4, 'G', 1); motif(5, 'Bm', 1); motif(6, 'D', 1, { lh: .8 }); motif(7, 'A', 1, { lh: .8 });
    motif(8, 'G', 1, { up: 12, lh: .85 }); motif(9, 'Bm', 1, { up: 12, lh: .85 }); motif(10, 'D', 1, { up: 12, lh: .85 }); motif(11, 'A', 1, { up: 12, lh: .85, end: true });
    pn(22, 78, 1, true); pn(bar(12, 6), 81, .9); roll(22, LH.G, .8); roll(23, LH.A, .8);
    [64, 69, 71, 76, 69, 71, 76, 81].forEach((m, i) => pn(bar(12, 8 + i), m, .55 + .45 * i / 7, i === 0));
    motif(13, 'Bm', 1, { oct: true, lh: .8 }); motif(14, 'G', 1, { oct: true, lh: .8 }); motif(15, 'D', 1, { oct: true, lh: .8 }); motif(16, 'A', 1, { oct: true, lh: .8, until: 8 });
    pn(32.5, 78, .5); motif(18, 'G', .6, { lh: .5 }); motif(19, 'A', .68, { lh: .55, until: 12 });
    pn(42, 64, .7, true); pn(43, 67, .7, true); roll(42, LH.Em, .6);
    [66, 69, 71, 74].forEach((m, i) => pn(bar(23, i * 2), m, .7 + .05 * i, i === 0)); roll(44, LH.G, .8);
    [69, 71, 76, 81, 71, 76, 81, 83].forEach((m, i) => pn(bar(23, 8 + i), m, .6 + .4 * i / 7, i === 0)); roll(45, LH.A, .8);
    roll(46, [57, 59, 64, 69, 76, 83], .9);
    A.HOOK.forEach((m, i) => { const t = bar(25, i * 2); pn(t, m, 1, i % 4 === 0); pn(t, m + 12, .55, i % 4 === 0); V.bell(S, B.bell, t, m + 12, 1); });
    roll(48, LH.G, .8); roll(49, LH.Bm, .8);
    [[0, 69], [3, 74], [4, 78], [6, 76], [8, 76], [12, 71]].forEach(([s, m]) => pn(bar(26, s), m, .6, s % 8 === 0)); roll(50, LH.D, .6); roll(51, LH.A, .6);
    roll(52, [38, 50, 57, 62, 66, 69, 74], .55, LAST.note); V.bell(S, B.bell, 52, 86, 1, LAST.note);

    /* ----- the ostinato: D4 and A4 in eighths, a soft pluck ----- */
    let on = 0;
    const ost = (t, v, m) => V.arp(S, B.ost, t, m || (on % 2 ? 69 : 62), v, (on++ % 2) ? .35 : -.35, 700 * Math.pow(3.1, bright(t)), { det: 5, q: 0, a: 6, d: 260, fEnd: 500, sweep: 90 });
    const eighths = (b, v0, v1, s0 = 0, s1 = 16) => { for (let s = s0; s < s1; s += 2) ost(bar(b, s), (s % 4 ? .65 : 1) * (v0 + (v1 - v0) * (s - s0) / Math.max(1, s1 - s0 - 2))); };
    const sixteenths = (b, v0, v1, s0 = 0, s1 = 16) => { for (let s = s0; s < s1; s++) ost(bar(b, s), (s % 2 ? .65 : 1) * (v0 + (v1 - v0) * (s - s0) / Math.max(1, s1 - s0 - 1))); };
    for (let b = 8; b <= 11; b++) eighths(b, 1, 1);
    eighths(12, .8, 1, 0, 8); sixteenths(12, .8, 1, 8, 16);
    for (let b = 13; b <= 15; b++) { eighths(b, 1, 1); for (let s = 0; s < 16; s += 4) ost(bar(b, s), .5, s % 8 ? 81 : 74); }
    eighths(16, 1, 1, 0, 12);
    eighths(22, .4, .55); eighths(23, .75, .75, 0, 8); sixteenths(23, .75, 1, 8, 16);
    sixteenths(25, 1, 1); eighths(26, .4, .4, 0, 8);

    /* ----- low end ----- */
    const root = (b, s = 0) => A.ROOT_MIDI[A.chordAt(b, s)], whole = { a: 40, d: 0, s: 1, r: 250 };
    // every bass note also sounds an octave up, 10 dB under: the roots sit at 49 to 82 Hz, which a laptop
    // cannot play, and nothing else in this score lives between 90 and 170 Hz
    const subV = V.sub; const sub = (out, t, m, v, o) => { subV(S, out, t, m, v, o); subV(S, out, t, m + 12, v * .3, o); };
    for (let b = 4; b <= 11; b++) sub(B.sub, bar(b), root(b), .6, { ...whole, gate: 1.75 });
    sub(B.sub, 22, root(12, 0), .7, { ...whole, gate: .85 }); sub(B.sub, 23, root(12, 8), .7, { ...whole, gate: .85 });
    for (let b = 13; b <= 15; b++) sub(B.sub, bar(b), root(b), .75, { ...whole, gate: 1.75 });
    sub(B.sub, 30, root(16), .75, { ...whole, r: 100, gate: 1.55 });
    sub(B.sub, 34, root(18), .5, { ...whole, gate: 1.75 }); sub(B.sub, 36, root(19), .55, { ...whole, gate: 1.75 });
    sub(B.sub, 41, root(21), .5, { ...whole, gate: .85 }); sub(B.sub, 42, root(22), .6, { ...whole, gate: 1.75 });
    sub(B.sub, 44, root(23, 0), .8, { ...whole, gate: .85 }); sub(B.sub, 45, root(23, 8), .9, { ...whole, r: 100, gate: .85 });
    sub(B.sub, 46, 33, .6, { ad: [12, 1400] });
    sub(B.sub, 48, root(25, 0), 1, { ...whole, r: 120, gate: .85 }); sub(B.sub, 49, root(25, 8), 1, { ...whole, r: 120, gate: .85 });
    sub(B.sub, 50, root(26, 0), .7, { ...whole, gate: .9 }); sub(B.sub, 51, root(26, 8), .7, { ...whole, r: 60, gate: .62 });
    sub(B.sub, 52, 38, .6, { ad: [12, 3000], ring: LAST.note });

    /* ----- drums: a deep kick on one, a snap on three, a shaker ----- */
    // every kick ducks fully, the soft ones too: a ghost kick under a full sub would otherwise vanish
    const kick = (t, v) => { S.kicks.push({ t, vel: 1 }); S.mark('kick', A.snap(t), 'drums'); V.shot(S, B.kick, 'kickB', t, v); };
    const snap = (t, clap) => { S.mark('snap', A.snap(t), 'drums'); V.shot(S, B.snap, 'rim', t, 1); if (clap) V.shot(S, B.clap, 'clap0', t, 1); };
    const shake = (b, s, v) => { const t = bar(b, s) + (seeded('sh' + b + ':' + s)() - .5) * .008; S.mark('shaker', A.snap(t), 'drums'); V.shot(S, B.shaker, 'shaker' + (s % 4), t, v, .3); };
    const shake8 = (b, k = 1, s0 = 0, s1 = 16) => { for (let s = s0; s < s1; s += 2) shake(b, s, k * (s % 4 ? 1 : .6)); };
    const shake16 = (b, k = 1, s0 = 0, s1 = 16) => { for (let s = s0; s < s1; s++) shake(b, s, k * (s % 2 ? 1 : .6)); };
    for (let b = 4; b <= 11; b++) { kick(bar(b), 1); if (b >= 8 || b === 6) kick(bar(b, 10), .6); if (b >= 6) { snap(bar(b, 8)); shake8(b); } }
    kick(22, 1); snap(23); shake8(12);
    for (let b = 13; b <= 15; b++) { kick(bar(b), 1); kick(bar(b, 10), .6); snap(bar(b, 8), true); shake8(b); }
    kick(30, 1); shake8(16, 1, 0, 12);
    kick(42, .5); kick(43, .63); shake8(22, .5);
    kick(44, .7); kick(44.5, .75); [8, 10, 12, 14].forEach((s, i) => kick(bar(23, s), .8 + .067 * i)); shake8(23, 1, 0, 8); shake16(23, 1, 8, 16);
    kick(46, .9);
    kick(48, 1); kick(bar(25, 7), .5); kick(bar(25, 10), .6); snap(49, true); shake16(25);
    kick(52, .6);

    /* ----- swells, air and the two crashes that lift the stamp and the peak ----- */
    V.air(S, B.air, 0, 23.7, .8, { a: 3000, r: 300 }); V.air(S, B.air, 24, 7.6, .7); V.air(S, B.air, 44, 3.4, .7, { a: 1800, r: 500 }); V.air(S, B.air, 48, 3.6, .7, { a: 200, r: 150 }); V.air(S, B.air, 52, 1, .7, { a: 60, r: 2500 });
    [[6, 'G', -30], [14, 'G', -27], [24, 'Bm', -24], [32, 'BIG', -22], [46, 'A2', -22], [48, 'G', -22], [50, 'D', -28], [52, 'BTN', -24]].forEach(([t, c, lv]) => V.bloom(S, B.bloom, t, CH[c], db(lv)));
    V.reverse(S, B.swell, 5, 6, CH.G); V.reverse(S, B.swell, 13, 14, CH.G); V.reverse(S, B.swell, 23, 24, CH.Bm); V.reverse(S, B.swell, 47.62, 48, CH.G);
    [[32, -23], [46, -24]].forEach(([t, lv]) => V.crash(S, B.noise, t, db(lv)));
  },
};
})();
