// The score's clock and harmony: one grid for the three scores and the sound effects.
// 120 BPM, 4/4, D major using only D E F# G A B (C# never sounds). 29 bars, 58.0 s.
// Bar n starts at 2(n-1) s. This is the approved cut's form, not the composer's 32-bar draft.
'use strict';
(() => {
const A = window.A = window.A || {};
A.SR = 48000; A.BPM = 120; A.BEAT = .5; A.BAR = 2; A.STEP = .125; A.BARS = 29; A.DUR = 58;
// bar n (from 1), step in sixteenths (0..15)
A.bar = (n, step = 0) => (n - 1) * A.BAR + step * A.STEP;
A.snap = t => Math.round(t * A.SR) / A.SR;

// Chord per half bar. One entry holds the whole bar. null: nothing is rooted (the breakdown).
A.ROOTS = {
  1: ['D'], 2: ['D'], 3: ['A'],
  4: ['G'], 5: ['Bm'], 6: ['D'], 7: ['A'],
  8: ['G'], 9: ['Bm'], 10: ['D'], 11: ['A'], 12: ['G', 'A'],
  13: ['Bm'], 14: ['G'], 15: ['D'], 16: ['A'],
  17: ['D'], 18: ['G'], 19: ['A'],
  20: [null], 21: ['A'],
  22: ['Em'], 23: ['G', 'A'],
  24: ['A2'],
  25: ['G', 'Bm'], 26: ['D', 'A'],
  27: ['D'], 28: ['D'], 29: ['D'],
};
// Bass roots, all in one sub octave: G1 49.00, A1 55.00, B1 61.74, D2 73.42, E2 82.41 Hz.
A.ROOT_MIDI = { G: 31, A: 33, A2: 33, Bm: 35, D: 38, Em: 40 };
A.chordAt = (bar, step = 0) => { const r = A.ROOTS[Math.max(1, Math.min(A.BARS, bar))]; return r[Math.min(r.length - 1, step >= 8 ? 1 : 0)]; };
A.chordAtT = t => { const b = Math.floor(t / A.BAR) + 1, s = Math.floor((t - (b - 1) * A.BAR) / A.STEP + 1e-6); return A.chordAt(b, s); };
// the root under time t as a sub frequency (49 to 82 Hz); D where nothing is rooted
A.rootAt = t => 440 * Math.pow(2, (A.ROOT_MIDI[A.chordAtT(t) || 'D'] - 69) / 12);

// The form (bars inclusive).
A.FORM = [
  { id: 'intro', a: 1, b: 3 }, { id: 'verse1', a: 4, b: 7 }, { id: 'verse2', a: 8, b: 11 }, { id: 'lift', a: 12, b: 12 },
  { id: 'chorus1', a: 13, b: 16 }, { id: 'stamp', a: 17, b: 19 }, { id: 'breakdown', a: 20, b: 21 }, { id: 'build', a: 22, b: 23 },
  { id: 'peak', a: 24, b: 24 }, { id: 'chorus2', a: 25, b: 25 }, { id: 'wide', a: 26, b: 26 }, { id: 'button', a: 27, b: 29 },
];
A.HITS = {
  arrival: 0, accents: [2, 2.5], lock: 5, cards: [6, 14, 24], riser: 31, stamp: 32, powerdown: 38, scan: [39, 39.5],
  chime: 40, subIn: 41, build: [42, 46], peak: 46, hang: [47.5, 48], drop4: 48, wide: 50, button: 52, end: 58,
};
// True silence before the stamp and before the final chord.
A.VACUUMS = [[31.75, 32], [51.75, 52]];
// The hook all three scores share, so the first eight arrivals in the room can play it (one
// effects track serves every score). F#4 A4 B4 D5 | F#5 E5 D5 A4: D major pentatonic.
A.HOOK = [66, 69, 71, 74, 78, 76, 74, 69];
A.PENT5 = [74, 76, 78, 81, 83];
})();
