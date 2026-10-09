// "Dawn": the lead score. Arpeggio-led, and it warms as the film does.
//
// The idea: one plucked figure (the hook, A.HOOK) over a slow pad, with a filter that opens from
// cold to warm across the whole film and is widest when the room is full (46.0). There is no
// drum kit before "Sell." (24.0): until then the only low end is a soft sub on one and three.
// From 24.0 a deep kick on one and three and an off-beat hat; four on the floor only in the
// build and the one-bar chorus at 48.0. Sparse on purpose: pure synthesis is most convincing
// when few things play, and the effects stay audible at low volume.
//
// Harmony is the shared grid (A.ROOTS), D major pentatonic over Asus chords, so the hook is
// consonant over every bar:
//   G  bar: F#4 A4 B4 D5   (maj7, 9, 3, 5)       Bm bar: F#5 E5 D5 A4   (5, 11, 3, 7)
//   D  bar: A4 B4 D5 F#5   (the answer, rising)   A  bar: E5 D5 B4 A4    (falling home)
// The four notes of a bar sit on steps 0, 3, 6 and 10; the dotted-eighth delay answers each one
// three steps later, which is what fills the bar.
//
// By section (bars; a bar is 2 s):
//   1-3   intro      the title: pad from nothing and one soft arrival; accents on the cut at 2.0 and at 2.5;
//                    the A bar brings the figure in, with a bell on the lock at 5.0
//   4-7   verse 1    the figure, the pad, a soft sub on one and three
//   8-11  verse 2    ghost notes join; the filter keeps opening
//   12    lift       sixteenths climbing G then A, a swell into 24.0
//   13-16 chorus 1   kick on one and three, off-beat hat, whole-bar sub, the hook an octave up on a bell;
//                    bar 16 drops the kick, climbs, and stops dead at 31.75
//   17-19 stamp      32.0 belongs to the stamp: no kick, no bass, one big D chord and one F#5 into the delay;
//                    then the hook at half speed under "your own Stripe" and "your own server", rising
//   20-21 breakdown  a thin fifth, then nothing: the chime at 40.0 has the room to itself; sub A at 41.0
//   22-23 build      no arpeggio while the first eight seats play the hook; then sixteenths, four on the floor
//   24    peak       46.0: one chord, held, and half a second of tails
//   25    chorus 2   everything, for one bar
//   26    wide       half-time, D then A, and silence at 51.75
//   27-29 button     the last chord at 52.0, ringing to the end of the film
'use strict';
(() => {
const A = window.A, E = A.E, V = E.V, { db, bq, seeded } = E, bar = A.bar;
const CH = { D: [57, 64, 66, 69], A: [57, 62, 64, 69], G: [59, 62, 66, 69], Bm: [57, 62, 66, 71], Em: [59, 62, 64, 67], A2: [59, 64, 69, 71, 76], BIG: [57, 62, 66, 69, 76], THIN: [69, 76], BTN: [57, 62, 69, 76, 78] };
const AR = { G: [[0, 66], [3, 69], [6, 71], [10, 74]], Bm: [[0, 78], [3, 76], [6, 74], [10, 69]], D: [[0, 69], [3, 71], [6, 74], [10, 78]], A: [[0, 76], [3, 74], [6, 71], [10, 69]], Em: [[0, 64], [3, 67], [6, 71], [10, 74]] };
const bright = E.bp([[0, .05], [6, .12], [14, .3], [22, .66], [24, .68], [31.75, .7], [32, .95], [33.5, .5], [38, .4], [38.01, .15], [41, .2], [44, .5], [46, 1], [47.5, 1], [48, .95], [50, .9], [50.01, .6], [52, .75], [58, .35]]);

A.SCORES = A.SCORES || {};
A.SCORES.dawn = {
  id: 'dawn', name: 'Dawn', bright,
  // every kick makes room for itself: bass -8 dB (100 ms release), everything musical -5 dB (110 ms).
  // Without it the kick, the pluck and the pad land on the same sample and the limiter pays for it.
  duck: { bass: [-8, 100], music: [-5, 110] },
  // a ride per section, in dB (music, bass and drums; not the effects). The verses are lifted so the
  // film's loudness is fairly level and it is the tone that grows: a quiet first half would be lost
  // at low volume, and would make the limiter work harder on everything after it.
  section: E.bp([[0, 1], [6, 1], [6.02, 1.2], [14, 1.2], [14.02, 2.5], [22, 2.5], [22.02, 1.5], [23.9, 1.5], [24, -.5], [24.6, 0], [42, 0], [42.02, -1.5], [44, -1.5], [44.02, -2], [46, -2], [46.08, .9], [47.5, .9], [48, -.5], [50, -.5], [50.02, -1], [51.75, -1], [52, -2.3], [58, -2.3]]),
  // per-part trims in dB, set by measurement (render-audio.mjs --parts, analyze.mjs --parts)
  trim: { kick: -4.6, hat: 0, clap: 0, roll: 0, sub: 0, bassMid: 3.5, arp: 7, pad: -1.5, lead: 0, air: 0, bloom: 0, swell: -3, noise: 0 },
  arrange(S) {
    const ctx = S.ctx, I = S.inst, T = this.trim, B = S.bus;
    I('kick', 'drums', { level: -9, trim: T.kick }); I('hat', 'drums', { level: -24, P: -18, trim: T.hat }); I('clap', 'drums', { level: -18, P: -9, H: -16, trim: T.clap }); I('roll', 'drums', { level: 0, P: -12, trim: T.roll });
    I('sub', 'bass', { level: -14, trim: T.sub }); I('bassMid', 'bass', { level: -20, trim: T.bassMid });
    I('arp', 'music', { level: -21, D: -7, P: -14, H: -22, trim: T.arp }); I('pad', 'music', { level: -33, H: -9, trim: T.pad }); I('lead', 'music', { level: -22, D: -10, H: -10, trim: T.lead });
    I('air', 'music', { level: -39, H: -4, trim: T.air }); I('bloom', 'music', { level: 0, H: -3, trim: T.bloom }); I('swell', 'music', { level: -20, H: -10, trim: T.swell }); I('noise', 'music', { level: 0, H: -6, trim: T.noise });
    // the pad's filter follows the film: 400 Hz cold, 3200 Hz warm
    const padIn = ctx.createGain(), padLP = bq(ctx, 'lowpass', 400, -3), padHP = bq(ctx, 'highpass', 200, -3); padIn.connect(padLP); padLP.connect(padHP); padHP.connect(B.pad); E.setCurve(padLP.frequency, E.fnCurve(t => 400 * Math.pow(8, bright(t))));
    const leadIn = ctx.createStereoPanner(); leadIn.pan.value = .15; leadIn.connect(B.lead);
    S.keep.push(padIn, padLP, padHP, leadIn); S.alive(padIn); S.alive(leadIn);   // long-lived nodes: see E.fx and E.graph

    // The last chord (52.0) rings to the end of the film, under the end card. Each of its voices falls as it
    // always did until it is well down (the pad 10 dB, the others 20), and only then slows: the hit and its
    // first two seconds are what they were, and what rings on sits under the loudness gate, so the film's
    // level into the limiter does not move. The master's closing fade (56.5 to 57.95) was always there.
    const LAST = { pad: { s: .31, r: 20000 }, note: { s: .1, r: 12000 } };
    /* ----- pad: [from, to, chord, attack ms, release ms, dB, ring] ----- */
    const pads = [[0, 4, 'D', 1900, 1200, -1], [4, 6, 'A', 900, 1200, 0]];   // from nothing under the title, full on the cut at 2.0
    for (let b = 4; b <= 11; b++) pads.push([bar(b), bar(b + 1), A.chordAt(b), 900, 1200, 0]);
    pads.push([22, 23, 'G', 600, 900, 0], [23, 24, 'A', 600, 500, 1]);
    for (let b = 13; b <= 15; b++) pads.push([bar(b), bar(b + 1), A.chordAt(b), 700, 1200, -1]);
    pads.push([30, 31.7, 'A', 600, 40, -1], [32, 34, 'BIG', 8, 1400, 2], [34, 36, 'G', 900, 1200, 0], [36, 38, 'A', 900, 300, 0], [38.2, 39.75, 'THIN', 900, 200, -8],
      [41, 42, 'A', 900, 1200, -3], [42, 44, 'Em', 900, 1200, 0], [44, 45, 'G', 500, 800, 1], [45, 46, 'A', 400, 300, 2], [46, 47.35, 'A2', 20, 200, 0],
      [48, 49, 'G', 60, 600, 1], [49, 50, 'Bm', 60, 600, 1], [50, 51, 'D', 300, 800, 0], [51, 51.7, 'A', 300, 40, 0], [52, 53.5, 'BTN', 8, 3500, 1, LAST.pad]);
    for (const [a, b, c, at, rel, lv, ring] of pads) V.pad(S, padIn, a, CH[c], { a: at, r: rel, gate: b - a, det: 10, width: .65, vel: db(lv), ring });

    /* ----- the arpeggio ----- */
    let nth = 0;
    const tone = t => ({ a: 4, fEnd: 500 + 2000 * bright(t), sweep: 140 + 80 * bright(t), d: 360 + 160 * bright(t) });   // the pluck rings longer and brighter as the film warms
    const peakHz = t => Math.max(2600, 900 * Math.pow(5.6, bright(t)));   // the pluck's attack is bright from the first note: the top is never absent
    const note = (t, m, v) => { V.arp(S, B.arp, t, m, v, (nth++ % 2) ? .25 : -.25, peakHz(t), tone(t)); S.mark('arp', A.snap(t), 'music'); };
    const high = (t, m, v, p) => V.arp(S, B.arp, t, m + 12, v, p, peakHz(t), tone(t));
    const figure = (b, chord, v, opt = {}) => {
      AR[chord].forEach(([s, m], i) => { const t = bar(b, s), vv = v * [1, .82, .9, .86][i]; note(t, m, vv); if (opt.oct) high(t, m, vv * (opt.oct === true ? .45 : opt.oct), i % 2 ? -.3 : .3); });
      if (opt.ghost) { note(bar(b, 13), 62, .3 * opt.ghost); note(bar(b, 14), 69, .4 * opt.ghost); if (opt.fill) note(bar(b, 8), 62, .35 * opt.ghost); }
    };
    const run = (b, s0, ms, v0, v1) => ms.forEach((m, i) => note(bar(b, s0 + i), m, v0 + (v1 - v0) * (i / Math.max(1, ms.length - 1))));
    // The film opens on its title (0.0 to 2.0): the pad from nothing and one soft, warm arrival, a low D and
    // its fifth and third on a pluck with its top taken off. The hook starts on the cut to the flyer:
    // an accent at 2.0, another as the flyer snaps into the import box (2.5), one note while the app reads it,
    // then the A bar's figure from the cut at 4.0, and a bell on the lock at 5.0.
    const warm = (t, m, v, p) => { V.arp(S, B.arp, t, m, v, p, 1100, { a: 10, d: 900, fEnd: 420, sweep: 380, q: 0 }); S.mark('arp', A.snap(t), 'music'); };
    warm(0, 62, .42, -.2); warm(0, 69, .28, .2); warm(0, 66, .22, 0);
    note(2, 74, .7); high(2, 74, .3, .3);
    note(2.5, 66, 1); high(2.5, 66, .5, -.3); note(3.5, 69, .7); high(3.5, 69, .28, .3);
    figure(3, 'A', .7, { oct: .35 });
    for (let b = 4; b <= 7; b++) figure(b, A.chordAt(b), 1, { oct: .3 });
    for (let b = 8; b <= 11; b++) figure(b, A.chordAt(b), 1, { oct: .3, ghost: b === 8 ? .5 : 1, fill: b >= 10 });
    run(12, 0, [62, 66, 69, 71, 74, 71, 74, 78, 64, 69, 71, 76, 69, 71, 76, 81], .5, 1);
    for (let b = 13; b <= 15; b++) figure(b, A.chordAt(b), 1, { oct: true, ghost: 1, fill: true });
    AR.A.slice(0, 3).forEach(([s, m]) => note(bar(16, s), m, 1)); run(16, 8, [64, 69, 71, 76, 81, 83], .6, 1);
    note(32, 78, .8); note(33, 74, .5);
    [66, 69, 71, 74].forEach((m, i) => note(bar(18, i * 4), m, .55)); [78, 76, 74, 69].forEach((m, i) => note(bar(19, i * 4), m, .62 + .05 * i));
    run(23, 0, [62, 66, 69, 71, 74, 78, 74, 78, 69, 71, 76, 81, 76, 81, 83, 88], .55, 1);
    [[0, 66], [2, 69], [3, 71], [5, 74], [6, 71], [8, 78], [10, 76], [11, 74], [13, 69], [14, 74]].forEach(([s, m], i) => { const t = bar(25, s); note(t, m, 1); high(t, m, .38, i % 2 ? -.3 : .3); });
    [[0, 69], [3, 71], [6, 74], [8, 76], [11, 74], [13, 71]].forEach(([s, m]) => note(bar(26, s), m, .7));
    for (const m of [74, 81, 86]) note(52, m, .5);

    /* ----- low end ----- */
    const root = (b, s = 0) => A.ROOT_MIDI[A.chordAt(b, s)];
    const soft = { a: 40, d: 0, s: 1, r: 250 }, tight = { a: 12, d: 60, s: .8, r: 60, gate: .19 };
    for (let b = 4; b <= 11; b++) for (const s of [0, 8]) V.sub(S, B.sub, bar(b, s), root(b, s), .4, { ...soft, gate: .6 });
    V.sub(S, B.sub, 22, root(12, 0), .55, { ...soft, gate: .85 }); V.sub(S, B.sub, 23, root(12, 8), .55, { ...soft, gate: .85 });
    for (let b = 13; b <= 15; b++) V.sub(S, B.sub, bar(b), root(b), .42, { ...soft, gate: 1.75 });
    V.sub(S, B.sub, 30, root(16), .45, { ...soft, r: 100, gate: 1.55 });
    V.sub(S, B.sub, 33, root(17), .35, { ...soft, gate: .85 }); V.sub(S, B.sub, 34, root(18), .35, { ...soft, gate: 1.75 }); V.sub(S, B.sub, 36, root(19), .45, { ...soft, gate: 1.75 });
    V.sub(S, B.sub, 41, root(21), .35, { ...soft, gate: .85 }); V.sub(S, B.sub, 42, root(22), .3, { ...soft, gate: 1.75 });
    for (let s = 0; s < 16; s += 2) V.sub(S, B.sub, bar(23, s), root(23, s), .5 + .3 * s / 14, tight);
    V.sub(S, B.sub, 46, 33, .45, { ad: [12, 1400] });
    [2, 3, 6, 7, 10, 11, 14, 15].forEach((s, i) => { const v = i % 2 ? .7 : 1; V.sub(S, B.sub, bar(25, s), root(25, s), .9 * v, tight); V.bassMid(S, B.bassMid, bar(25, s), root(25, s) + 12, v); });
    V.sub(S, B.sub, 50, root(26, 0), .5, { ...soft, gate: .9 }); V.sub(S, B.sub, 51, root(26, 8), .5, { ...soft, r: 60, gate: .62 });
    V.sub(S, B.sub, 52, 38, .5, { ad: [12, 3000], ring: LAST.note });

    /* ----- drums: nothing before 24.0 ----- */
    const kick = (t, v) => { S.kicks.push({ t, vel: v }); S.mark('kick', A.snap(t), 'drums'); V.shot(S, B.kick, 'kickB', t, v); };
    const hat = (t, v, i) => { S.mark('hat', A.snap(t), 'drums'); V.shot(S, B.hat, 'hatC' + (i % 4), t, v * db(3 * (seeded('dh' + t)() - .5)), .2); };
    for (let b = 13; b <= 15; b++) { kick(bar(b, 0), 1); kick(bar(b, 8), .9); [2, 6, 10, 14].forEach((s, i) => hat(bar(b, s), i % 2 ? 1 : .8, b + i)); }
    kick(bar(16, 0), 1); hat(bar(16, 2), .8, 1); hat(bar(16, 6), 1, 2);
    kick(bar(22, 0), .5); kick(bar(22, 8), .5);
    [.63, .71, .84, 1].forEach((v, i) => kick(bar(23, i * 4), v)); for (let s = 0; s < 16; s += 2) hat(bar(23, s), .5 + .5 * s / 14, s);
    for (let s = 8; s < 16; s++) { const t = bar(23, s); S.mark('roll', t, 'drums'); V.shot(S, B.roll, 'snare' + (s % 2), t, db(-24 + 12 * (s - 8) / 7)); }
    kick(46, .8);
    [1, .85, .95, .85].forEach((v, i) => kick(bar(25, i * 4), v)); for (let s = 0; s < 16; s++) hat(bar(25, s), [.55, .3, 1, .35][s % 4] * db(1), s);
    for (const s of [4, 12]) { S.mark('clap', bar(25, s), 'drums'); V.shot(S, B.clap, 'clap' + (s === 4 ? 0 : 1), bar(25, s), 1); }
    kick(50, .7); [2, 6, 10].forEach((s, i) => hat(bar(26, s), .63, i));
    kick(52, .75);

    /* ----- the bell: the opening's accents, a glint on each verse bar, then the hook an octave up ----- */
    V.bell(S, leadIn, 2.5, 78, .9); V.bell(S, leadIn, 5, 86, .75); V.bell(S, leadIn, 5, 81, .5);
    for (let b = 4; b <= 11; b++) { const c = AR[A.chordAt(b)]; V.bell(S, leadIn, bar(b, 0), c[0][1] + 12, .45); if (b >= 8) V.bell(S, leadIn, bar(b, 6), c[2][1] + 12, .4); }
    [[13, 0, 78], [13, 4, 81], [13, 8, 83], [13, 12, 86], [14, 0, 90], [14, 6, 88], [14, 8, 86], [14, 12, 81], [15, 0, 78], [15, 6, 81], [15, 12, 86], [16, 0, 88], [16, 8, 83]].forEach(([b, s, m]) => V.bell(S, leadIn, bar(b, s), m, 1));
    A.HOOK.forEach((m, i) => V.bell(S, leadIn, bar(25, i * 2), m + 12, 1));
    V.bell(S, leadIn, 52, 86, 1, LAST.note);

    /* ----- air, swells, risers, crashes ----- */
    V.air(S, B.air, 0, 23.7, .8, { a: 3000, r: 300 }); V.air(S, B.air, 24, 7.6, .7); V.air(S, B.air, 44, 3.4, .7, { a: 1800, r: 500 }); V.air(S, B.air, 48, 3.6, .7, { a: 200, r: 150 }); V.air(S, B.air, 52, 1, .7, { a: 60, r: 2500 });
    [[6, 'G', -30], [14, 'G', -27], [24, 'Bm', -28], [32, 'BIG', -18], [46, 'A2', -21], [48, 'G', -23], [52, 'BTN', -24]].forEach(([t, c, lv]) => V.bloom(S, B.bloom, t, CH[c], db(lv)));
    V.reverse(S, B.swell, 13.25, 14, CH.G); V.reverse(S, B.swell, 23, 24, CH.Bm); V.reverse(S, B.swell, 47.62, 48, CH.G);
    V.riser(S, B.noise, 22, 24, db(-24)); V.riser(S, B.noise, 28, 31.75, db(-20)); V.riser(S, B.noise, 44, 46, db(-15));
    [[24, -30], [32, -21], [46, -22], [48, -22], [52, -24]].forEach(([t, lv]) => V.crash(S, B.noise, t, db(lv)));   // 32.0: the stamp has the lows, the score gives it air
  },
};
})();
