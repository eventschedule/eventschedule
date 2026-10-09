// "Pulse": the driving one. A light four-on-the-floor kick, a sixteenth-note pluck figure with
// accents on a 3-3-2 pattern, a pad that pumps with the kick, a bell that sings the motif.
//
// The composer's score A, on the approved 29-bar form (A.ROOTS), with the same hit points,
// silences and effects track as the other two scores. The pluck's accents over a G bar begin
// F#4 A4 B4 D5, the first half of the shared hook (A.HOOK); the bell plays the whole hook in the
// one full bar at 48.0.
//
// By section (bars; a bar is 2 s):
//   1-3   intro      the title: pad from nothing and one soft arrival; accents on the cut at 2.0 and at 2.5,
//                    the figure at half strength, then the A bar with a bell on the lock at 5.0
//   4-7   verse 1    kick on one and three, off-beat hats; from bar 6 four on the floor, sixteenth hats, a rim
//   8-11  verse 2    clap on two and four, the ghost notes, the bell's motif
//   12    lift       a bass fill and a roll into 24.0
//   13-16 chorus 1   open hats, the bass in eighths, the pluck doubled an octave up; bar 16 thins and stops dead at 31.75
//   17-19 stamp      32.0 belongs to the stamp: no kick, no bass, one big D chord and one F#5 into the delay;
//                    then the accents at half strength, rising
//   20-21 breakdown  a thin fifth, then nothing: the chime at 40.0 has the room to itself; sub A at 41.0
//   22-23 build      the kick comes back under the seats' hook, then everything climbs
//   24    peak       46.0: one chord, held, and half a second of tails
//   25    chorus 2   everything, for one bar
//   26    wide       half-time, D then A, and silence at 51.75
//   27-29 button     the last chord at 52.0, ringing to the end of the film
'use strict';
(() => {
const A = window.A, E = A.E, V = E.V, { db, bq, seeded } = E, bar = A.bar;
const CH = { D: [57, 64, 66, 69], A: [57, 62, 64, 69], G: [59, 62, 66, 69], Bm: [57, 62, 66, 71], Em: [59, 62, 64, 67], A2: [59, 64, 69, 71], BIG: [57, 62, 66, 69, 76], THIN: [69, 76], BTN: [57, 62, 69, 76, 78] };
// the six accents of a bar (steps 0 3 6 8 11 14)
const ACC = { G: [66, 69, 71, 74, 71, 69], Bm: [66, 69, 71, 74, 76, 74], D: [69, 71, 74, 78, 74, 71], A: [71, 69, 76, 74, 71, 76], Em: [64, 67, 71, 74, 71, 67], Arise: [64, 69, 71, 76, 71, 69], Apeak: [69, 71, 76, 81, 76, 71] };
const STEPS = [0, 3, 6, 8, 11, 14], GHOST_D = [1, 4, 7, 9, 12, 15], GHOST_A = [2, 5, 10, 13];
const LEAD = { G: [[0, 83], [6, 81], [12, 78]], Bm: [[0, 81], [8, 78], [12, 74]], D: [[0, 78], [6, 81], [12, 86]], A: [[0, 88], [8, 83]] };
const bright = E.bp([[0, .15], [6, .35], [14, .5], [22, .7], [24, .85], [31.75, .9], [32, .95], [33.5, .4], [38, .3], [38.01, .25], [42, .4], [46, 1], [50, 1], [50.01, .6], [52, .7], [58, .4]]);

A.SCORES = A.SCORES || {};
A.SCORES.pulse = {
  id: 'pulse', name: 'Pulse', bright,
  // the pump: bass -15 dB (70 ms release); everything musical -4 dB and the pad 1.5 dB more; the reverbs -2.5 dB
  duck: { bass: [-15, 70], music: [-4, 90], fx: [-2.5, 110], inst: { pad: [-1.5, 110] } },
  // a ride per section, in dB (music, bass and drums; not the effects), set by measurement
  section: E.bp([[0, 0], [6, 0], [6.02, -1], [14, -1], [14.02, 0], [42, 0], [42.02, -3.5], [44, -3.5], [44.02, -.5], [46, -.5], [46.08, 4.6], [47.5, 4.6], [48, 0], [50, 0], [50.02, -.5], [51.75, -.5], [52, -3], [58, -3]]),
  // per-part trims in dB, set by measurement (render-audio.mjs --parts, analyze.mjs --parts)
  trim: { kick: -4, hat: 4, hatO: 3, clap: .5, rim: 4, roll: 0, sub: -2, bassMid: 6, arp: 8.5, pad: -3.5, lead: 0, air: -6, bloom: 0, swell: 0, noise: 0 },
  arrange(S) {
    const ctx = S.ctx, I = S.inst, T = this.trim, B = S.bus;
    I('kick', 'drums', { level: -8, trim: T.kick }); I('hat', 'drums', { level: -23, P: -18, trim: T.hat }); I('hatO', 'drums', { level: -25, P: -18, trim: T.hatO }); I('clap', 'drums', { level: -15, P: -9, H: -16, trim: T.clap }); I('rim', 'drums', { level: -19, P: -10, trim: T.rim }); I('roll', 'drums', { level: 0, P: -12, trim: T.roll });
    I('sub', 'bass', { level: -13, trim: T.sub }); I('bassMid', 'bass', { level: -20, trim: T.bassMid });
    I('arp', 'music', { level: -21, D: -12, P: -14, H: -22, trim: T.arp }); I('pad', 'music', { level: -32, H: -8, trim: T.pad, duck: true }); I('lead', 'music', { level: -22, D: -10, H: -10, trim: T.lead });
    I('air', 'music', { level: -36, H: -4, trim: T.air }); I('bloom', 'music', { level: 0, H: -3, trim: T.bloom }); I('swell', 'music', { level: -20, H: -10, trim: T.swell }); I('noise', 'music', { level: 0, H: -6, trim: T.noise });
    const padIn = ctx.createGain(), padLP = bq(ctx, 'lowpass', 500, 0), padHP = bq(ctx, 'highpass', 180, -3); padIn.connect(padLP); padLP.connect(padHP); padHP.connect(B.pad); E.setCurve(padLP.frequency, E.fnCurve(t => 500 * Math.pow(4.4, bright(t))));
    const leadIn = ctx.createStereoPanner(); leadIn.pan.value = .15; leadIn.connect(B.lead);
    S.keep.push(padIn, padLP, padHP, leadIn); S.alive(padIn); S.alive(leadIn);

    // The last chord (52.0) rings to the end of the film, under the end card. Each of its voices falls as it
    // always did until it is well down (the pad 8 dB, the others 20), and only then slows: the hit and its
    // first two seconds are what they were, and what rings on sits under the loudness gate, so the film's
    // level into the limiter does not move. The master's closing fade (56.5 to 57.95) was always there.
    const LAST = { pad: { s: .38, r: 20000 }, note: { s: .1, r: 12000 } };
    /* ----- pad: [from, to, chord, attack ms, release ms, dB, ring] ----- */
    const pads = [[0, 4, 'D', 1900, 600, -1], [4, 6, 'A', 350, 600, 0]];   // from nothing under the title, full on the cut at 2.0
    for (let b = 4; b <= 11; b++) pads.push([bar(b), bar(b + 1), A.chordAt(b), 350, 600, 0]);
    pads.push([22, 23, 'G', 350, 600, 0], [23, 24, 'A', 350, 400, 1]);
    for (let b = 13; b <= 15; b++) pads.push([bar(b), bar(b + 1), A.chordAt(b), 350, 600, 0]);
    pads.push([30, 31.7, 'A', 350, 40, 0], [32, 34, 'BIG', 8, 1400, 0], [34, 36, 'G', 350, 600, 0], [36, 38, 'A', 350, 300, 0], [38.2, 39.75, 'THIN', 900, 200, -8],
      [41, 42, 'A', 900, 600, -3], [42, 44, 'Em', 350, 600, 0], [44, 45, 'G', 350, 600, 1], [45, 46, 'A', 350, 300, 2], [46, 47.35, 'A2', 20, 200, 2],
      [48, 49, 'G', 60, 600, 1], [49, 50, 'Bm', 60, 600, 1], [50, 51, 'D', 300, 600, 0], [51, 51.7, 'A', 300, 40, 0], [52, 53.5, 'BTN', 8, 3500, 1, LAST.pad]);
    for (const [a, b, c, at, rel, lv, ring] of pads) V.pad(S, padIn, a, CH[c], { a: at, r: rel, gate: b - a, det: 9, width: .6, vel: db(lv), ring });

    /* ----- the pluck: accents on 0 3 6 8 11 14, ghosts on the sixteenths between ----- */
    const peakHz = t => Math.max(2600, 900 * Math.pow(4.67, bright(t)));   // a bright attack from the first note; the body follows the arc
    const pl = (t, m, v, p) => V.arp(S, B.arp, t, m, v, p, peakHz(t), { fEnd: 500 + 700 * bright(t), d: 320 });
    const note = (b, s, m, v) => { const t = bar(b, s); pl(t, m, v * (1 + .1 * (seeded('pv' + b + ':' + s)() - .5)), s % 2 ? .25 : -.25); S.mark('arp', A.snap(t), 'music'); };
    // one bar. ch: the chord, or [first half, second half]. o: {v, ghost, oct, from, to}
    const figure = (b, ch, o = {}) => {
      const two = Array.isArray(ch) ? ch : [ch, ch], v = o.v == null ? 1 : o.v, from = o.from || 0, to = o.to == null ? 16 : o.to;
      STEPS.forEach((s, i) => { if (s < from || s >= to) return; const m = ACC[two[s < 8 ? 0 : 1]][i]; note(b, s, m, v); if (o.oct) pl(bar(b, s), m + 12, v * .5, s % 2 ? -.25 : .25); });
      if (o.ghost) { GHOST_D.forEach(s => { if (s >= from && s < to) note(b, s, 62, .38 * o.ghost); }); GHOST_A.forEach(s => { if (s >= from && s < to) note(b, s, 69, .5 * o.ghost); }); }
    };
    // The film opens on its title (0.0 to 2.0): the pad from nothing and one soft, warm arrival, a low D and
    // its fifth and third on a pluck with its top taken off, and no figure yet. The pulse starts on the cut
    // to the flyer: an accent at 2.0, another as the flyer snaps into the import box (2.5), the accents at
    // half strength while the app reads it, then the A bar in full, with a bell on the lock at 5.0.
    const warm = (t, m, v, p) => { V.arp(S, B.arp, t, m, v, p, 1100, { a: 10, d: 900, fEnd: 420, sweep: 380, q: 0 }); S.mark('arp', A.snap(t), 'music'); };
    warm(0, 62, .35, -.2); warm(0, 69, .23, .2); warm(0, 66, .18, 0);
    pl(2, 74, .6, -.25); pl(2, 86, .25, .25); pl(2.5, 66, 1, .25); pl(2.5, 78, .45, -.25);
    figure(2, 'D', { v: .5, from: 6 }); figure(3, 'A', { v: .75, oct: true });
    figure(4, 'G'); figure(5, 'Bm'); figure(6, 'D', { ghost: .5 }); figure(7, 'A', { ghost: 1 });
    for (let b = 8; b <= 11; b++) figure(b, A.chordAt(b), { ghost: 1 });
    figure(12, ['G', 'Arise'], { ghost: 1 });
    for (let b = 13; b <= 15; b++) figure(b, A.chordAt(b), { ghost: 1, oct: true });
    figure(16, 'A', { ghost: 1, oct: true, to: 14 });
    pl(32, 78, .8, 0); figure(18, 'G', { v: .5 }); figure(19, 'A', { v: .7 });
    figure(22, 'Em', { v: .5, from: 11 }); figure(23, ['G', 'Apeak'], { ghost: 1 });
    figure(25, ['G', 'Bm'], { ghost: 1, oct: true });
    figure(26, ['D', 'A'], { v: .7, to: 14 });
    for (const m of [74, 81, 86]) pl(52, m, .5, 0);

    /* ----- bass: a tight sub and a filtered saw an octave above it ----- */
    const root = (b, s = 0) => A.ROOT_MIDI[A.chordAt(b, s)], tight = { a: 12, d: 60, s: .8, r: 60, gate: .19 }, whole = { a: 40, d: 0, s: 1, r: 250 };
    const bass = (b, s, v) => { const t = bar(b, s), m = root(b, s); if (m == null) return; V.sub(S, B.sub, t, m, v, tight); V.bassMid(S, B.bassMid, t, m + 12, v); };
    for (let b = 4; b <= 11; b++) { [2, 6, 10, 14].forEach(s => bass(b, s, 1)); if (b % 2 === 0) bass(b, 15, .6); }
    [1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15].forEach((s, i) => bass(12, s, .6 + .4 * i / 11));
    for (let b = 13; b <= 16; b++) [2, 3, 6, 7, 10, 11, 14, 15].forEach((s, i) => { if (b === 16 && s > 11) return; bass(b, s, i % 2 ? .7 : 1); });
    V.sub(S, B.sub, 34, root(18), .5, { ...whole, gate: 1.75 }); V.sub(S, B.sub, 36, root(19), .55, { ...whole, gate: 1.75 });
    V.sub(S, B.sub, 41, root(21), .5, { ...whole, gate: .85 }); V.sub(S, B.sub, 42, root(22), .5, { ...whole, gate: 1.75 });
    for (let s = 0; s < 16; s += 2) bass(23, s, .7 + .3 * s / 14);
    V.sub(S, B.sub, 46, 33, .6, { ad: [12, 1400] });
    [2, 3, 6, 7, 10, 11, 14, 15].forEach((s, i) => bass(25, s, i % 2 ? .7 : 1));
    V.sub(S, B.sub, 50, root(26, 0), .7, { ...whole, gate: .9 }); V.bassMid(S, B.bassMid, 50, root(26, 0) + 12, 1); V.sub(S, B.sub, 51, root(26, 8), .7, { ...whole, r: 60, gate: .62 });
    V.sub(S, B.sub, 52, 38, .6, { ad: [12, 3000], ring: LAST.note });

    /* ----- drums ----- */
    const kick = (t, v) => { S.kicks.push({ t, vel: v }); S.mark('kick', A.snap(t), 'drums'); V.shot(S, B.kick, 'kickP', t, v); };
    const HV = [.55, .3, 1, .35];
    const hat = (b, s, k = 1) => { const t = bar(b, s); S.mark('hat', t, 'drums'); V.shot(S, B.hat, 'hatC' + ((b + s) % 4), t, k * HV[s % 4] * db(3 * (seeded('ph' + b + ':' + s)() - .5)), .2); };
    const open = (b, s) => { const t = bar(b, s); S.mark('hat', t, 'drums'); V.shot(S, B.hatO, 'hatO' + (s % 8 ? 1 : 0), t, 1, -.2, t + .125); };
    const hats16 = (b, o = {}) => { for (let s = o.from || 0; s < (o.to == null ? 16 : o.to); s++) { if (o.open && o.open.includes(s)) open(b, s); else hat(b, s, o.k || 1); } };
    const clap = (b, s) => { S.mark('clap', bar(b, s), 'drums'); V.shot(S, B.clap, 'clap' + (s === 4 ? 0 : 1), bar(b, s), 1); };
    const rim = (b, s) => { S.mark('rim', bar(b, s), 'drums'); V.shot(S, B.rim, 'rim', bar(b, s), 1); };
    const roll = (b, steps, a, z) => steps.forEach((s, i) => { S.mark('roll', bar(b, s), 'drums'); V.shot(S, B.roll, 'snare' + (i % 2), bar(b, s), db(a + (z - a) * i / Math.max(1, steps.length - 1))); });
    const four = (b, drop) => [1, .85, .95, .85].forEach((v, i) => { if (!(drop && drop.includes(i * 4))) kick(bar(b, i * 4), v); });
    for (const b of [4, 5]) { kick(bar(b, 0), 1); kick(bar(b, 8), .95); [2, 6, 10, 14].forEach(s => hat(b, s)); }
    four(6); hats16(6); rim(6, 4); rim(6, 12);
    four(7, [12]); hats16(7, { open: [14] }); rim(7, 4); rim(7, 12);
    for (let b = 8; b <= 11; b++) { four(b); hats16(b, { open: [14] }); clap(b, 4); clap(b, 12); }
    four(12, [12]); hats16(12, { open: [14] }); clap(12, 4); roll(12, [8, 9, 10, 11, 12, 13, 14, 15], -24, -12);
    for (let b = 13; b <= 15; b++) { four(b); hats16(b, { open: [2, 6, 10, 14] }); clap(b, 4); clap(b, 12); }
    four(16, [12]); hats16(16, { open: [2, 6, 10], to: 13 }); clap(16, 4);
    kick(43, .4); kick(43.5, .4);
    [.63, .71, .84, 1].forEach((v, i) => kick(bar(23, i * 4), v)); for (let s = 0; s < 8; s += 2) hat(23, s, 1 / HV[s % 4] * .8); hats16(23, { from: 8 }); roll(23, [0, 2, 4, 6], -26, -21); roll(23, [8, 9, 10, 11, 12, 13, 14, 15], -21, -15.5);
    kick(46, 1);
    four(25); hats16(25, { open: [2, 6, 10, 14] }); clap(25, 4); clap(25, 12);
    kick(50, 1); kick(bar(26, 10), .6); [2, 6, 10].forEach(s => hat(26, s, .63)); rim(26, 8);
    kick(52, .85);

    /* ----- the bell: the lock, the motif, the hook ----- */
    V.bell(S, leadIn, 2.5, 78, .6); V.bell(S, leadIn, 5, 86, .9); V.bell(S, leadIn, 5, 81, .6);
    const motif = (b, ch, to = 16) => LEAD[ch].forEach(([s, m]) => { if (s < to) V.bell(S, leadIn, bar(b, s), m, 1); });
    for (let b = 4; b <= 7; b++) V.bell(S, leadIn, bar(b), ACC[A.chordAt(b)][0] + 12, .45);   // a glint on each bar of verse 1
    for (let b = 8; b <= 11; b++) motif(b, A.chordAt(b));
    motif(12, 'G', 8);
    for (let b = 13; b <= 15; b++) motif(b, A.chordAt(b));
    motif(16, 'A', 12);
    A.HOOK.forEach((m, i) => V.bell(S, leadIn, bar(25, i * 2), m + 12, 1));
    LEAD.D.slice(0, 2).forEach(([s, m]) => V.bell(S, leadIn, bar(26, s), m, .8));
    V.bell(S, leadIn, 52, 86, 1, LAST.note);

    /* ----- air, risers, crashes, swells ----- */
    V.air(S, B.air, 0, 23.7, .5, { a: 3000, r: 300 }); V.air(S, B.air, 24, 7.6, 1); V.air(S, B.air, 44, 3.4, 1, { a: 1800, r: 500 }); V.air(S, B.air, 48, 3.6, 1, { a: 200, r: 150 }); V.air(S, B.air, 52, 1, 1, { a: 60, r: 2500 });
    V.bloom(S, B.bloom, 32, CH.BIG, db(-18));
    V.riser(S, B.noise, 4, 6, db(-24)); V.riser(S, B.noise, 12.5, 14, db(-22)); V.riser(S, B.noise, 20, 24, db(-18)); V.riser(S, B.noise, 42, 46, db(-17));
    [[6, -26], [14, -24], [24, -22], [32, -21], [46, -20], [48, -20], [50, -28], [52, -22]].forEach(([t, lv]) => V.crash(S, B.noise, t, db(lv)));
    V.reverse(S, B.swell, 31.25, 31.75, CH.A); V.reverse(S, B.swell, 47.62, 48, CH.G);
  },
};
})();
