// The sound engine: seeded maths, waves, noise, the two reverbs and the delay, the bus graph,
// the automation curves and every voice. Nothing here knows a tune: the scores are data
// (score-*.js) and the effects are cue recipes (sfx.js).
//
// Rules that keep a render identical twice and its onsets on the grid:
// - no Math.random, no clock; every random choice comes from seeded(key)
// - no DynamicsCompressorNode, AudioWorklet or setTargetAtTime (dynamics happen in ffmpeg on stems)
// - envelopes are ramps anchored by a preceding event; no attack under 0.5 ms
// - bus automation is one 1 kHz curve per parameter, set once with setValueCurveAtTime
// - lowpass and highpass Q is in dB here (-3 is Butterworth); bandpass and peaking Q is linear
'use strict';
(() => {
const A = window.A, SR = A.SR;
const db = x => Math.pow(10, x / 20), mtof = m => 440 * Math.pow(2, (m - 69) / 12);
function rng(seed) { let a = seed >>> 0; return () => { a = (a + 0x6D2B79F5) >>> 0; let t = a; t = Math.imul(t ^ (t >>> 15), t | 1); t ^= t + Math.imul(t ^ (t >>> 7), t | 61); return ((t ^ (t >>> 14)) >>> 0) / 4294967296; }; }
function hash32(s) { let h = 0x811c9dc5; for (let i = 0; i < s.length; i++) { h ^= s.charCodeAt(i); h = Math.imul(h, 0x01000193); } return h >>> 0; }
const seeded = key => rng(hash32(String(key)));
const snap = A.snap;
const E = A.E = { db, mtof, rng, hash32, seeded, snap };

/* ---------- waves (all start at 0: sine-phase partials) ---------- */
const WAVES = {
  SOFTSAW: Array.from({ length: 32 }, (_, i) => 1 / Math.pow(i + 1, 1.35)),
  FELT: [1, .42, .18, .09, .05, .025, .012, .006],
  SUB: [1, .30, .10, .04],
  BELL: [1, .5, .3, .2, .12, .08, .05, .03],
};
E.waves = ctx => { const o = {}; for (const k in WAVES) { const im = new Float32Array(WAVES[k].length + 1), re = new Float32Array(im.length); WAVES[k].forEach((v, i) => { im[i + 1] = v; }); o[k] = ctx.createPeriodicWave(re, im); } return o; };

/* ---------- noise: one seeded 4 s white buffer per channel ---------- */
let NOISE = null;
E.noise = () => {
  if (!NOISE) NOISE = [0, 1].map(ch => { const b = new AudioBuffer({ length: 4 * SR, sampleRate: SR, numberOfChannels: 1 }), d = b.getChannelData(0), r = rng(7001 + 101 * ch); for (let i = 0; i < d.length; i++) d[i] = r() * 2 - 1; return b; });
  return NOISE;
};

/* ---------- reverb impulse responses ----------
   Noise under an exponential decay, darkening as it decays, a few early taps, a clean end and
   unit energy per channel, so a send level in dB means what it says. Left and right come from
   different seeds: the width is decorrelation, never polarity. */
const TAP_LIFT = 8;   // early taps stand this far above one sample of the tail, or they are just more noise
E.makeIR = ({ seed, len, rt60, pre, fc0, fc1, taps }) => {
  const n = Math.round(len * SR), buf = new AudioBuffer({ length: n, sampleRate: SR, numberOfChannels: 2 });
  for (let ch = 0; ch < 2; ch++) {
    const d = buf.getChannelData(ch), r = rng(seed + 101 * ch), p = Math.round(pre * SR); let lp = 0;
    for (let i = p; i < n; i++) {
      const t = (i - p) / SR, w = 2 * (r() + r() + r() - 1.5);
      const fc = fc0 * Math.pow(fc1 / fc0, Math.min(1, t / rt60)), a = 1 - Math.exp(-2 * Math.PI * fc / SR);
      lp += a * (w - lp);
      d[i] = lp * Math.exp(-6.908 * t / rt60) * Math.min(1, t / .004);
    }
    for (let k = 0; k < taps; k++) { const i = Math.round((pre + .007 + .011 * k * (1 + .35 * r())) * SR), sign = r() < .5 ? -1 : 1; if (i < n) d[i] += TAP_LIFT * .5 * Math.pow(.78, k) * sign * ((k % 2) === ch ? 1 : .6); }
    const f0 = Math.floor(n * .85); for (let i = f0; i < n; i++) d[i] *= .5 * (1 + Math.cos(Math.PI * (i - f0) / (n - 1 - f0)));
    let m = 0; for (let i = p; i < n; i++) m += d[i]; m /= (n - p); for (let i = p; i < n - 1; i++) d[i] -= m; d[n - 1] = 0;
    let e = 0; for (let i = 0; i < n; i++) e += d[i] * d[i]; const k = 1 / Math.sqrt(e); for (let i = 0; i < n; i++) d[i] *= k;
  }
  return buf;
};
// Eight echoes, 0.375 s apart (a dotted eighth at 120), each 7.5 dB under the last and passed once
// more through a 3.2 kHz lowpass and a 350 Hz highpass, alternating left and right.
E.makeEcho = () => {
  const step = Math.round(.375 * SR), taps = 8, klen = 4096, n = step * taps + klen, buf = new AudioBuffer({ length: n, sampleRate: SR, numberOfChannels: 2 }), L = buf.getChannelData(0), R = buf.getChannelData(1);
  const co = (type, f) => { const w = 2 * Math.PI * f / SR, c = Math.cos(w), al = Math.sin(w) / Math.SQRT2, a0 = 1 + al; return type === 'lp' ? [(1 - c) / 2 / a0, (1 - c) / a0, (1 - c) / 2 / a0, -2 * c / a0, (1 - al) / a0] : [(1 + c) / 2 / a0, -(1 + c) / a0, (1 + c) / 2 / a0, -2 * c / a0, (1 - al) / a0]; };
  const run = (x, [b0, b1, b2, a1, a2]) => { const y = new Float64Array(x.length); let x1 = 0, x2 = 0, y1 = 0, y2 = 0; for (let i = 0; i < x.length; i++) { const v = b0 * x[i] + b1 * x1 + b2 * x2 - a1 * y1 - a2 * y2; x2 = x1; x1 = x[i]; y2 = y1; y1 = v; y[i] = v; } return y; };
  let k = new Float64Array(klen); k[0] = 1;
  for (let t = 1; t <= taps; t++) { k = run(run(k, co('lp', 3200)), co('hp', 350)); const g = Math.pow(.42, t - 1), p = t % 2 ? -.7 : .7, gl = g * Math.cos((p + 1) * Math.PI / 4), gr = g * Math.sin((p + 1) * Math.PI / 4), o = step * t; for (let i = 0; i < klen; i++) { const f = i > klen - 256 ? (klen - i) / 256 : 1; L[o + i - 0] += k[i] * gl * f; R[o + i] += k[i] * gr * f; } }
  return buf;
};
E.IRS = () => ({ echo: E.makeEcho(), hall: E.makeIR({ seed: 9001, len: 3.6, rt60: 2.8, pre: .03, fc0: 11000, fc1: 2200, taps: 10 }), plate: E.makeIR({ seed: 9002, len: 1.5, rt60: 1.1, pre: .01, fc0: 13000, fc1: 5000, taps: 6 }) });

const bq = E.bq = (ctx, type, f, q, gain) => { const b = ctx.createBiquadFilter(); b.type = type; b.frequency.value = f; if (q != null) b.Q.value = q; if (gain != null) b.gain.value = gain; return b; };

/* ---------- plate, hall and a dotted-eighth ping-pong delay ---------- */
// Every node made here is also kept in `keep`. A node that nothing in JavaScript refers to can be
// collected while it is still wired up, and Chrome then lets it finish its tail and removes it: a
// long render lost the later echoes of its last notes that way, on some runs and not on others.
E.fx = (ctx, IR) => {
  const out = ctx.createGain(), keep = [out];
  const ret = buf => { const inp = ctx.createGain(), c = ctx.createConvolver(); c.normalize = false; c.buffer = buf; const hp = bq(ctx, 'highpass', 250, -3), lp = bq(ctx, 'lowpass', 9500, -3), pk = bq(ctx, 'peaking', 3000, 1, -3); inp.connect(c); c.connect(hp); hp.connect(lp); lp.connect(pk); pk.connect(out); keep.push(inp, c, hp, lp, pk); return inp; };
  const plate = ret(IR.plate), hall = ret(IR.hall);
  // The dotted-eighth ping-pong is one convolution, not delay nodes. A feedback loop of two
  // DelayNodes is rendered in an order that changes from run to run (every second echo moved by a
  // render quantum), and a chain of eight DelayNodes lost its later echoes on some renders. An
  // impulse response with eight filtered taps is the same sound and renders the same every time.
  const delay = ctx.createGain(), dc = ctx.createConvolver(); delay.channelCount = 1; delay.channelCountMode = 'explicit'; dc.normalize = false; dc.buffer = IR.echo; delay.connect(dc); dc.connect(out); keep.push(dc);
  keep.push(delay);
  return { plate, hall, delay, out, keep };
};

/* ---------- envelopes (a, d, r in ms) ---------- */
// A gain is 1 until its first event. A voice whose oscillator starts a little before t (the
// seeded phase) would sound at full level until then, so every envelope first sets the gain's
// resting value to 0.
// A note that rings on. From level L at t0 the gain falls at the rate that would take it to L / 1000
// after d seconds (the voice's own fall), but only as far as L * ring.s; from there it takes ring.r
// ms to die away. So the first moments of the note are exactly what they are without a ring.
function RING(g, t0, L, d, ring) {
  const t1 = t0 + d * Math.log(ring.s) / Math.log(.001), t2 = t1 + ring.r / 1000;
  g.exponentialRampToValueAtTime(L * ring.s, t1); g.exponentialRampToValueAtTime(Math.max(1e-6, L * ring.s * .001), t2); g.linearRampToValueAtTime(0, t2 + .005);
  return t2 + .005;
}
function AD(g, t, a, d, peak = 1, ring) {
  a /= 1000; d /= 1000; g.value = 0;
  g.setValueAtTime(0, t); g.linearRampToValueAtTime(peak, t + a);
  if (ring) return RING(g, t + a, peak, d, ring);
  g.exponentialRampToValueAtTime(peak * .001, t + a + d); g.linearRampToValueAtTime(0, t + a + d + .005);
  return t + a + d + .005;
}
function ADSR(g, t, a, d, s, r, gate, peak = 1, ring) {
  a /= 1000; d /= 1000; r /= 1000; g.value = 0;
  g.setValueAtTime(0, t);
  let L;
  if (gate <= a) { L = peak * gate / a; g.linearRampToValueAtTime(L, t + gate); }
  else {
    g.linearRampToValueAtTime(peak, t + a);
    if (d > 0 && s < 1) {
      if (gate >= a + d) { g.exponentialRampToValueAtTime(peak * s, t + a + d); L = peak * s; g.setValueAtTime(L, t + gate); }
      else { L = peak * Math.pow(s, (gate - a) / d); g.exponentialRampToValueAtTime(L, t + gate); }
    } else { L = peak; g.setValueAtTime(L, t + gate); }
  }
  if (ring) return RING(g, t + gate, L, r, ring);
  g.exponentialRampToValueAtTime(Math.max(1e-6, L * .001), t + gate + r); g.linearRampToValueAtTime(0, t + gate + r + .005);
  return t + gate + r + .005;
}
E.AD = AD; E.ADSR = ADSR;
const curveOf = (n, fn) => { const c = new Float32Array(n); for (let i = 0; i < n; i++) c[i] = fn(i / (n - 1)); return c; };
E.curveOf = curveOf;

/* ---------- the build state ---------- */
// at(t, fn): fn creates its nodes when the render gets near t (the graph stays small);
// done(t, node): node can be disconnected after t; mark(): an expected onset, for the checks.
E.state = (ctx, opts = {}) => {
  const S = { ctx, opts, ev: [], marks: [], kicks: [], sfxDucks: [], doneList: [], keep: [], bus: {}, instInfo: {}, instDuck: {}, waves: E.waves(ctx), noise: E.noise(), ONE: A.ONE, IR: A.IR, shapers: {} };
  S.at = (t, fn) => { if (!opts.window || (t >= opts.window[0] - .5 && t < opts.window[1])) S.ev.push({ t: Math.max(0, t), fn }); };
  S.mark = (kind, t, part) => S.marks.push({ t, kind, part });
  S.done = (t, node) => S.doneList.push({ t, node });
  S.sweep = T => { const keep = []; for (const d of S.doneList) { if (d.t < T - .05) { try { d.node.disconnect(); } catch (e) { /* already gone */ } } else keep.push(d); } S.doneList = keep; };
  return S;
};
const shaper = (S, k) => { const key = 'k' + k; if (!S.shapers[key]) { const n = 2049, c = new Float32Array(n), q = Math.tanh(k); for (let i = 0; i < n; i++) c[i] = Math.tanh(k * (i / (n - 1) * 2 - 1)) / q; S.shapers[key] = c; } const w = S.ctx.createWaveShaper(); w.curve = S.shapers[key]; w.oversample = 'none'; return w; };

/* ---------- buses ----------
   DRUMS in ------------------------------.
   BASS  in -> HP 30 -> duckBass --------- +-> muteMusic -> sectionGain -.
   MUSIC in -> HP 150 -> duckMusic ------- |                             +-> PREMASTER
   FX-M (plate, hall, delay) -> duckFx --- '                             |
   SFX in ---------------. +-> muteSfx ----------------------------------'
   FX-S (a second copy) -'
   Each instrument has its own gate (solo), taken before its sends, so a solo render carries only
   its own wet and the four stems sum to the mix. */
E.graph = S => {
  const ctx = S.ctx, G = () => ctx.createGain(), o = S.opts;
  const pre = G(); pre.connect(ctx.destination);
  const musicSum = G(), muteMusic = G(), section = G(); musicSum.connect(muteMusic); muteMusic.connect(section); section.connect(pre);
  const sfxSum = G(), muteSfx = G(); sfxSum.connect(muteSfx); muteSfx.connect(pre);
  const drums = G(); drums.connect(musicSum);
  const bass = G(), bassHP = bq(ctx, 'highpass', 30, -3), duckBass = G(); bass.connect(bassHP); bassHP.connect(duckBass); duckBass.connect(musicSum);
  const music = G(), musicHP = bq(ctx, 'highpass', 150, -3), duckMusic = G(); music.connect(musicHP); musicHP.connect(duckMusic); duckMusic.connect(musicSum);
  const fxM = E.fx(ctx, S.IR), duckFx = G(); fxM.out.connect(duckFx); duckFx.connect(musicSum);
  const sfx = G(); sfx.connect(sfxSum); const fxS = E.fx(ctx, S.IR); fxS.out.connect(sfxSum);
  S.group = { drums, bass, music, sfx }; S.fxM = fxM; S.fxS = fxS; S.nodes = { duckBass, duckMusic, duckFx, muteMusic, muteSfx, section, pre };
  S.keep.push(pre, musicSum, muteMusic, section, sfxSum, muteSfx, drums, bass, bassHP, duckBass, music, musicHP, duckMusic, duckFx, sfx, ...fxM.keep, ...fxS.keep);
  // One silent source that never stops feeds every long-lived node. When the last voice into a bus
  // ends, Chrome switches the bus and everything after it off from the main thread, at a moment
  // that depends on how busy that thread is; the later echoes of the film's last notes were lost
  // on some renders and kept on others. With a live source upstream nothing is ever switched off.
  const alive = ctx.createConstantSource(); alive.offset.value = 0; alive.start(0); S.keep.push(alive);
  S.alive = node => alive.connect(node);
  for (const n of [drums, bass, music, sfx, fxM.plate, fxM.hall, fxM.delay, fxS.plate, fxS.hall, fxS.delay]) S.alive(n);
  // inst(name, group, {level dB, trim dB, P, H, D sends in dB relative to the voice, duck: own duck gain})
  S.inst = (name, group, c = {}) => {
    const on = (!o.solo || o.solo === group) && (!o.part || o.part === name) && !(o.mute && o.mute.includes(name));
    const inp = G(), g = G(); g.gain.value = on ? db((c.level || 0) + (c.trim || 0)) : 0; inp.connect(g);
    let tap = g;
    if (c.duck) { const d = G(); g.connect(d); S.instDuck[name] = d; tap = d; }
    const dry = G(); dry.gain.value = o.wetOnly ? 0 : 1; tap.connect(dry); dry.connect(S.group[group]); S.keep.push(inp, g, tap, dry);
    const fx = group === 'sfx' ? fxS : fxM;
    for (const [k, node] of [['P', fx.plate], ['H', fx.hall], ['D', fx.delay]]) if (c[k] != null) { const s = G(); s.gain.value = db(c[k]); tap.connect(s); s.connect(node); S.keep.push(s); }
    S.alive(inp); S.bus[name] = inp; S.instInfo[name] = { group, level: c.level || 0, trim: c.trim || 0 };
    return inp;
  };
};

/* ---------- automation curves, 1 kHz ---------- */
const NC = Math.round(A.DUR * 1000) + 1;
// The pump: full depth from 5 ms before a kick to 45 ms after it, then it lets go.
E.duckCurve = (hits, depthDb, relMs, hold = [.005, .045], attMs = 3) => {
  const tgt = new Float32Array(NC).fill(1), c = new Float32Array(NC);
  for (const h of hits) { const d = 1 - (1 - db(h.depth != null ? h.depth : depthDb)) * (h.vel == null ? 1 : h.vel), i0 = Math.max(0, Math.round((h.t - (h.pre != null ? h.pre : hold[0])) * 1000)), i1 = Math.min(NC, Math.round((h.t + (h.len != null ? h.len : hold[1])) * 1000)); for (let i = i0; i < i1; i++) if (d < tgt[i]) tgt[i] = d; }
  const kA = 1 - Math.exp(-1 / attMs), kR = 1 - Math.exp(-1 / relMs); let g = 1;
  for (let i = 0; i < NC; i++) { g += (tgt[i] - g) * (tgt[i] < g ? kA : kR); c[i] = g; }
  return c;
};
E.muteCurve = () => { const c = new Float32Array(NC).fill(1); for (const [a, b] of A.VACUUMS) for (let i = Math.round(a * 1000); i <= Math.round(b * 1000) && i < NC; i++) { const t = i / 1000; c[i] = t < a + .025 ? 1 - (t - a) / .025 : t < b - .004 ? 0 : t < b - .002 ? (t - (b - .004)) / .002 : 1; } return c; };
E.fnCurve = fn => { const c = new Float32Array(NC); for (let i = 0; i < NC; i++) c[i] = fn(i / 1000); return c; };
E.setCurve = (param, curve) => param.setValueCurveAtTime(curve, 0, A.DUR);
// linear breakpoints [[t, v], ...] as a function of t
E.bp = pts => t => { if (t <= pts[0][0]) return pts[0][1]; for (let i = 1; i < pts.length; i++) if (t <= pts[i][0]) { const a = pts[i - 1], b = pts[i]; return a[1] + (b[1] - a[1]) * ((t - a[0]) / Math.max(1e-9, b[0] - a[0])); } return pts[pts.length - 1][1]; };
E.applyCurves = (S, sc) => {
  const d = (sc && sc.duck) || {}, kicks = S.kicks, N = S.nodes, mul = (a, b) => { const c = new Float32Array(NC); for (let i = 0; i < NC; i++) c[i] = a[i] * b[i]; return c; };
  // the stamp and the sub drops own the low end while they sound
  const sfxDuck = E.duckCurve(S.sfxDucks, 0, 150);
  const bassDuck = d.bass ? E.duckCurve(kicks, d.bass[0], d.bass[1]) : new Float32Array(NC).fill(1);
  E.setCurve(N.duckBass.gain, mul(bassDuck, sfxDuck));
  if (d.music) E.setCurve(N.duckMusic.gain, E.duckCurve(kicks, d.music[0], d.music[1]));
  if (d.fx) E.setCurve(N.duckFx.gain, E.duckCurve(kicks, d.fx[0], d.fx[1]));
  for (const name in (d.inst || {})) if (S.instDuck[name]) E.setCurve(S.instDuck[name].gain, E.duckCurve(kicks, d.inst[name][0], d.inst[name][1]));
  const mute = E.muteCurve(); E.setCurve(N.muteMusic.gain, mute); E.setCurve(N.muteSfx.gain, mute);
  if (sc && sc.section) E.setCurve(N.section.gain, E.fnCurve(t => db(sc.section(t))));
};

/* ---------- voices ----------
   Each takes the build state, the node it plays into, a start time and its own numbers. Levels
   are set by the instrument bus; a voice at velocity 1 peaks near 1. */
const V = E.V = {};
const nz = (S, ch, t, dur, key) => { const src = S.ctx.createBufferSource(); src.buffer = S.noise[ch]; src.loop = true; src.start(t, seeded('nz:' + key)() * 3.9); src.stop(t + dur); return src; };
const pan = (S, node, p, out) => { if (!p) { node.connect(out); return node; } const sp = S.ctx.createStereoPanner(); sp.pan.value = p; node.connect(sp); sp.connect(out); return sp; };
// an oscillator whose phase at t is seeded (pads and plucks) or zero (lows)
const osc = (S, wave, f, t, stop, det, phaseKey) => { const o = S.ctx.createOscillator(); if (typeof wave === 'string') o.type = wave; else o.setPeriodicWave(wave); o.frequency.value = f; if (det) o.detune.value = det; o.start(phaseKey == null ? t : Math.max(0, t - seeded('ph:' + phaseKey)() / f)); o.stop(stop); return o; };

// a pre-rendered one-shot (kick, hats, clap, rim, snare, shaker)
V.shot = (S, out, name, t, gain, p = 0, choke = null) => {
  t = snap(t);
  S.at(t, () => { const ctx = S.ctx, src = ctx.createBufferSource(), g = ctx.createGain(); src.buffer = S.ONE[name]; g.gain.value = gain; src.connect(g); if (choke != null) { g.gain.setValueAtTime(gain, choke - .005); g.gain.linearRampToValueAtTime(0, choke); } const last = pan(S, g, p, out); src.start(t); S.done(t + src.buffer.duration + .01, last); });
};
V.kickSynth = (S, out, t, o, vel = 1) => {
  const ctx = S.ctx, body = ctx.createOscillator(), g = ctx.createGain(), sum = ctx.createGain(), sh = shaper(S, o.k);
  body.type = 'sine'; body.frequency.setValueAtTime(o.f0, t); body.frequency.exponentialRampToValueAtTime(o.f1, t + o.sweep / 1000);
  const end = AD(g.gain, t, o.a, o.d); body.connect(g); g.connect(sh); sh.connect(sum); sum.gain.value = vel;
  const bt = ctx.createOscillator(), bg = ctx.createGain(); bt.type = 'sine'; bt.frequency.setValueAtTime(900, t); bt.frequency.exponentialRampToValueAtTime(120, t + .012); AD(bg.gain, t, .5, 18, db(o.beat)); bt.connect(bg); bg.connect(sum);
  let last = sum; if (o.lp) { last = bq(ctx, 'lowpass', o.lp, -3); sum.connect(last); }
  last.connect(out); body.start(t); body.stop(end + .01); bt.start(t); bt.stop(t + .04); S.done(end + .02, last);
};
E.KICK_P = { f0: 165, f1: 55, sweep: 70, a: 1.5, d: 260, k: 1.6, beat: -12 };
E.KICK_B = { f0: 110, f1: 55, sweep: 90, a: 3, d: 420, k: 1.3, beat: -22, lp: 2500 };
V.hat = (S, out, t, o, key) => { const ctx = S.ctx, n = nz(S, 0, t, (o.a + o.d) / 1000 + .02, key), h1 = bq(ctx, 'highpass', o.hp, -3), h2 = bq(ctx, 'highpass', o.hp, -3), lp = bq(ctx, 'lowpass', 15000, -3), g = ctx.createGain(); const end = AD(g.gain, t, o.a, o.d); n.connect(h1); h1.connect(h2); h2.connect(lp); lp.connect(g); g.connect(out); S.done(end + .02, g); };
E.HAT_C = { hp: 7000, a: .5, d: 35 }; E.HAT_O = { hp: 6000, a: 1, d: 180 };
V.clap = (S, out, t, key) => {
  const ctx = S.ctx;
  for (const ch of [0, 1]) {
    const n = nz(S, ch, t, .2, key + ch), bp = bq(ctx, 'bandpass', 1300, 1), hp = bq(ctx, 'highpass', 600, -3), lp = bq(ctx, 'lowpass', 6000, -3), g = ctx.createGain(), a = g.gain;
    a.setValueAtTime(0, t); a.linearRampToValueAtTime(.7, t + .0005); a.exponentialRampToValueAtTime(.07, t + .008); a.setValueAtTime(.07, t + .011); a.linearRampToValueAtTime(.8, t + .0115); a.exponentialRampToValueAtTime(.08, t + .019); a.setValueAtTime(.08, t + .023); a.linearRampToValueAtTime(1, t + .0235); a.exponentialRampToValueAtTime(.001, t + .1735); a.linearRampToValueAtTime(0, t + .1785);
    n.connect(bp); bp.connect(hp); hp.connect(lp); lp.connect(g); const last = pan(S, g, ch ? .3 : -.3, out); S.done(t + .2, last);
  }
};
V.rim = (S, out, t, key) => { const ctx = S.ctx, sum = ctx.createGain(), g = ctx.createGain(), o1 = osc(S, 'triangle', 440, t, t + .05), o2 = osc(S, 'sine', 1174.7, t, t + .05); AD(g.gain, t, .5, 30); o1.connect(g); o2.connect(g); g.connect(sum); const n = nz(S, 0, t, .03, key), hp = bq(ctx, 'highpass', 5000, -3), ng = ctx.createGain(); AD(ng.gain, t, .5, 8, db(-10)); n.connect(hp); hp.connect(ng); ng.connect(sum); sum.connect(out); S.done(t + .06, sum); };
V.snare = (S, out, t, key) => { const ctx = S.ctx, sum = ctx.createGain(), g = ctx.createGain(), n = nz(S, 0, t, .12, key), bp = bq(ctx, 'bandpass', 1800, .7), o = osc(S, 'sine', 220, t, t + .12), og = ctx.createGain(); og.gain.value = .5; n.connect(bp); bp.connect(sum); o.connect(og); og.connect(sum); sum.connect(g); AD(g.gain, t, 1, 90); g.connect(out); S.done(t + .12, g); };
V.shaker = (S, out, t, key) => { const ctx = S.ctx, n = nz(S, 0, t, .08, key), bp = bq(ctx, 'bandpass', 6500, 1.2), g = ctx.createGain(); AD(g.gain, t, 8, 50); n.connect(bp); bp.connect(g); g.connect(out); S.done(t + .08, g); };

// sub: SUB wave from phase 0. o: {a,d,s,r,gate} or {ad:[a,d]}
V.sub = (S, out, t, midi, vel, o) => {
  t = snap(t); S.mark('sub', t);
  S.at(t, () => { const ctx = S.ctx, g = ctx.createGain(), end = o.ad ? AD(g.gain, t, o.ad[0], o.ad[1], vel, o.ring) : ADSR(g.gain, t, o.a, o.d, o.s, o.r, o.gate, vel, o.ring), os = osc(S, S.waves.SUB, mtof(midi), t, end + .01); os.connect(g); g.connect(out); S.done(end + .02, g); });
};
V.bassMid = (S, out, t, midi, vel) => {
  t = snap(t);
  S.at(t, () => { const ctx = S.ctx, g = ctx.createGain(), lp = bq(ctx, 'lowpass', 1200, 3), hp = bq(ctx, 'highpass', 110, -3), end = AD(g.gain, t, 3, 180, vel), os = osc(S, 'sawtooth', mtof(midi), t, end + .01); lp.frequency.setValueAtTime(1200, t); lp.frequency.exponentialRampToValueAtTime(280, t + .12); os.connect(lp); lp.connect(hp); hp.connect(g); g.connect(out); S.done(end + .02, g); });
};
// the pluck: two detuned soft saws through a lowpass that closes
V.arp = (S, out, t, midi, vel, p, fcPeak, o = {}) => {
  t = snap(t); const key = 'arp:' + t.toFixed(4) + ':' + midi;
  S.at(t - .03, () => {
    const ctx = S.ctx, f = mtof(midi), g = ctx.createGain(), lp = bq(ctx, 'lowpass', fcPeak, o.q == null ? 2 : o.q), hp = bq(ctx, 'highpass', o.hp || 250, -3), end = AD(g.gain, t, o.a || 2, o.d || 220, vel * .5), det = o.det || 7;
    const fc = Math.max(520, fcPeak * (1 + .16 * (seeded(key)() - .5)));
    lp.frequency.setValueAtTime(fc, t); lp.frequency.exponentialRampToValueAtTime(o.fEnd || 500, t + (o.sweep || 140) / 1000);
    for (const [d, k] of [[-det, 'a'], [det, 'b']]) osc(S, S.waves.SOFTSAW, f, t, end + .01, d, key + k).connect(lp);
    lp.connect(hp); hp.connect(g); const last = pan(S, g, p, out); S.done(end + .02, last);
  });
};
// a chord for the pad: per note three soft saws (left, centre, right). o: {a, r, gate, det, width, vel, swell}
V.pad = (S, out, t, notes, o) => {
  t = snap(t); const key = 'pad:' + t.toFixed(3);
  S.at(t - .05, () => {
    const ctx = S.ctx, legs = [[-o.det, -o.width], [0, 0], [o.det, o.width]]; let end = t;
    legs.forEach(([det, p], li) => { const g = ctx.createGain(); end = ADSR(g.gain, t, o.a, 0, 1, o.r, o.gate, o.vel == null ? 1 : o.vel, o.ring); notes.forEach(m => osc(S, S.waves.SOFTSAW, mtof(m), t, end + .01, det, key + ':' + m + ':' + li).connect(g)); let last = g; if (o.swell) { const sw = ctx.createGain(); sw.gain.value = .8; sw.gain.setValueCurveAtTime(curveOf(64, u => .8 + .2 * Math.pow(Math.sin(Math.PI * u), 2)), t, o.gate); g.connect(sw); last = sw; } S.done(end + .02, pan(S, last, p, out)); });
  });
};
// the felt piano: two soft-partialled oscillators through a lowpass that closes, a hammer of
// lowpassed noise, a long natural fall that is shorter the higher the note (ring: see RING, the last chord)
V.felt = (S, out, t, midi, vel = 1, ring) => {
  t = snap(t); const key = 'felt:' + t.toFixed(4) + ':' + midi;
  S.at(t - .03, () => {
    const ctx = S.ctx, f = mtof(midi), T0 = Math.max(.7, 2.8 - (midi - 50) * .0444), g = ctx.createGain(), a = g.gain, lp = bq(ctx, 'lowpass', 1000, -3), sum = ctx.createGain(), f0 = Math.min(6000, f * (3 + 5 * Math.min(1, vel)));
    a.value = 0; a.setValueAtTime(0, t); a.linearRampToValueAtTime(vel, t + .004); a.exponentialRampToValueAtTime(vel * .45, t + .12);
    // the long fall runs from 45% at 120 ms to a thousandth at T0; a ring leaves it at ring.s (of the note's peak)
    let T = T0; if (ring) T = RING(a, t + .12, vel * .45, (T0 - .12) * Math.log(.001) / Math.log(.001 / .45), { s: ring.s / .45, r: ring.r }) - t - .005; else { a.exponentialRampToValueAtTime(vel * .001, t + T); a.linearRampToValueAtTime(0, t + T + .005); }
    lp.frequency.setValueAtTime(f0, t); lp.frequency.exponentialRampToValueAtTime(Math.min(f0, 1.5 * f), t + .6);
    [[0, 1], [4, .6]].forEach(([det, lv], i) => { const og = ctx.createGain(); og.gain.value = lv / 1.6; osc(S, S.waves.FELT, f, t, t + T + .02, det, key + i).connect(og); og.connect(lp); });
    lp.connect(g); g.connect(sum);
    const n = nz(S, 0, t, .03, key), nlp = bq(ctx, 'lowpass', 1200, -3), ng = ctx.createGain(); AD(ng.gain, t, 1, 12, vel * db(-24)); n.connect(nlp); nlp.connect(ng); ng.connect(sum);
    S.done(t + T + .03, pan(S, sum, Math.max(-.3, Math.min(.3, (midi - 66) / 40)), out));
  });
};
// ring {s, r}: see RING (the last chord)
V.bell = (S, out, t, midi, vel, ring) => {
  t = snap(t); S.mark('bell', t);
  S.at(t, () => {
    const ctx = S.ctx, f = mtof(midi), sum = ctx.createGain(), g1 = ctx.createGain(), g2 = ctx.createGain(); AD(g1.gain, t, 3, 220, vel * .5); const end = AD(g2.gain, t, 3, 900, vel, ring);
    osc(S, S.waves.BELL, f, t, t + .25).connect(g1); osc(S, 'sine', f, t, end + .01).connect(g2); g1.connect(sum); g2.connect(sum); sum.connect(out); S.done(end + .02, sum);
  });
};
// one note of the chime voice: a sine and two soft partials, each with its own decay
V.chimeNote = (S, out, t, midi, vel = 1, mul = 1) => {
  t = snap(t);
  S.at(t, () => { const ctx = S.ctx, f = mtof(midi), sum = ctx.createGain(); let end = t; [[1, 0, 700], [2, -10, 350], [3, -18, 180]].forEach(([h, lv, d]) => { if (f * h > 18000) return; const g = ctx.createGain(); end = Math.max(end, AD(g.gain, t, 3, d * mul, vel * db(lv))); osc(S, 'sine', f * h, t, t + (3 + d * mul) / 1000 + .02).connect(g); g.connect(sum); }); sum.connect(out); S.done(end + .02, sum); });
};
V.air = (S, out, t, gate, vel = 1, o = {}) => {
  t = snap(t);
  S.at(t, () => { const ctx = S.ctx, g = ctx.createGain(), end = ADSR(g.gain, t, o.a || 800, 0, 1, o.r || 1200, gate, vel), ng = ctx.createGain(), hp = bq(ctx, 'highpass', 9000, -3); ng.gain.value = db(2); for (const m of [86, 93]) osc(S, 'sine', mtof(m), t, end + .01, 0, 'air' + m + t).connect(g); nz(S, 0, t, end - t + .01, 'air' + t).connect(hp); hp.connect(ng); ng.connect(g); g.connect(out); S.done(end + .02, g); });
};
// a swell that opens after the hit and rings: the chord an octave up as sines, and air
V.bloom = (S, out, t, notes, vel = 1) => {
  t = snap(t);
  S.at(t, () => { const ctx = S.ctx, g = ctx.createGain(), a = g.gain, hp = bq(ctx, 'highpass', 6000, -3), ng = ctx.createGain(); a.value = 0; a.setValueAtTime(0, t); a.linearRampToValueAtTime(vel, t + .45); a.exponentialRampToValueAtTime(vel * .001, t + 3.45); a.linearRampToValueAtTime(0, t + 3.455); ng.gain.value = db(-14); notes.forEach(m => { const og = ctx.createGain(); og.gain.value = 1 / notes.length; osc(S, 'sine', mtof(m + 12), t, t + 3.47, 0, 'bl' + m + t).connect(og); og.connect(g); }); nz(S, 1, t, 3.47, 'bloom' + t).connect(hp); hp.connect(ng); ng.connect(g); g.connect(out); S.done(t + 3.5, g); });
};
// chord tones that swell into a hit and stop 8 ms short of it
V.reverse = (S, out, t0, tHit, notes, vel = 1) => {
  t0 = snap(t0); const dur = tHit - .008 - t0;
  S.at(t0, () => { const ctx = S.ctx, g = ctx.createGain(); g.gain.value = 0; g.gain.setValueCurveAtTime(curveOf(256, u => vel * Math.exp(5 * (u - 1)) * Math.min(1, u * 40)), t0, dur - .006); g.gain.linearRampToValueAtTime(0, t0 + dur); notes.forEach(m => { const og = ctx.createGain(); og.gain.value = 1 / notes.length; osc(S, 'sine', mtof(60 + ((m - 60) % 12 + 12) % 12 + 12), t0, t0 + dur + .01, 0, 'rv' + m + t0).connect(og); og.connect(g); }); g.connect(out); S.done(t0 + dur + .02, g); });
};
// two decorrelated bands of noise that climb, cut 10 ms before the hit
V.riser = (S, out, t0, t1, vel = 1) => {
  t0 = snap(t0); const dur = t1 - t0;
  S.at(t0, () => { const ctx = S.ctx; for (const ch of [0, 1]) { const n = nz(S, ch, t0, dur + .01, 'riser' + t0 + ch), bp = bq(ctx, 'bandpass', 400, 1.5), hp = bq(ctx, 'highpass', 300, -3), g = ctx.createGain(); g.gain.value = 0; bp.frequency.setValueAtTime(400, t0); bp.frequency.exponentialRampToValueAtTime(6000, t1); g.gain.setValueCurveAtTime(curveOf(256, u => vel * u * u), t0, dur - .012); g.gain.linearRampToValueAtTime(0, t1 - .001); n.connect(bp); bp.connect(hp); hp.connect(g); S.done(t1 + .02, pan(S, g, ch ? .7 : -.7, out)); } });
};
V.crash = (S, out, t, vel = 1) => {
  t = snap(t);
  S.at(t, () => { const ctx = S.ctx; for (const ch of [0, 1]) { const n = nz(S, ch, t, 1.65, 'crash' + t + ch), hp = bq(ctx, 'highpass', 5000, -3), lp = bq(ctx, 'lowpass', 12000, -3), g = ctx.createGain(); AD(g.gain, t, 2, 1600, vel); n.connect(hp); hp.connect(lp); lp.connect(g); S.done(t + 1.65, pan(S, g, ch ? .8 : -.8, out)); } });
};

/* ---------- one-shots: rendered once, tested alone, played from a buffer ---------- */
E.renderOneShots = async () => {
  const defs = { kickP: [.34, 1, (S, o) => V.kickSynth(S, o, 0, E.KICK_P)], kickB: [.5, 1, (S, o) => V.kickSynth(S, o, 0, E.KICK_B)], rim: [.06, 1, (S, o) => V.rim(S, o, 0, 'rim')] };
  for (let i = 0; i < 4; i++) { defs['hatC' + i] = [.06, 1, (S, o) => V.hat(S, o, 0, E.HAT_C, 'hatC' + i)]; defs['shaker' + i] = [.08, 1, (S, o) => V.shaker(S, o, 0, 'shk' + i)]; }
  for (let i = 0; i < 2; i++) { defs['hatO' + i] = [.21, 1, (S, o) => V.hat(S, o, 0, E.HAT_O, 'hatO' + i)]; defs['clap' + i] = [.2, 2, (S, o) => V.clap(S, o, 0, 'clap' + i)]; defs['snare' + i] = [.12, 1, (S, o) => V.snare(S, o, 0, 'snr' + i)]; }
  const out = {}, info = {};
  for (const name in defs) {
    const [dur, ch, fn] = defs[name], ctx = new OfflineAudioContext(ch, Math.ceil(dur * SR), SR), S = E.state(ctx); S.at = (t, f) => f();
    fn(S, ctx.destination);
    const buf = await ctx.startRendering(); let peak = 0;
    for (let c = 0; c < ch; c++) { const d = buf.getChannelData(c); for (let i = 0; i < d.length; i++) peak = Math.max(peak, Math.abs(d[i])); }
    for (let c = 0; c < ch; c++) { const d = buf.getChannelData(c), n = d.length; for (let i = 0; i < n; i++) d[i] /= peak; for (let i = 0; i < 64; i++) d[n - 1 - i] *= i / 64; d[0] = 0; }
    out[name] = buf; info[name] = { rawPeakDb: +(20 * Math.log10(peak)).toFixed(2), frames: buf.length, first: buf.getChannelData(0)[0], last: buf.getChannelData(0)[buf.length - 1] };
  }
  A.ONE_INFO = info;
  return out;
};
})();
