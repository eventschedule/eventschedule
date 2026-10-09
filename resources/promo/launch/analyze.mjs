#!/usr/bin/env node
// Numbers that stand in for ears. Nobody on the build can hear the score, so every stage is
// measured against a target and the result is written down.
//
//   node analyze.mjs --score=dawn            the acceptance checks on stems, mix, premaster and master
//                                            -> out/audio/dawn.report.json, dawn.spectrum.png, dawn.wave.png
//   node analyze.mjs --score=dawn --parts    loudness of every instrument alone (after render-audio.mjs --parts)
//   node analyze.mjs --compare               the three masters side by side (must be within 0.3 LU)
//   node analyze.mjs --file=x.wav            loudness, peak and bands of any file
//
// Plain Node: a WAV reader, K-weighting (BS.1770), an FFT, biquads. ffmpeg is asked only for the
// figures a delivery spec quotes (integrated loudness, range, true peak) and for the pictures.
import fs from 'node:fs';
import path from 'node:path';
import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const OUT = path.join(here, 'out/audio');
export const FFMPEG = process.env.FFMPEG || '/opt/homebrew/bin/ffmpeg';
export const SR = 48000, DUR = 58;
const dB = x => 20 * Math.log10(Math.max(x, 1e-12)), pdB = x => 10 * Math.log10(Math.max(x, 1e-20));
const r1 = x => Math.round(x * 10) / 10, r2 = x => Math.round(x * 100) / 100;

/* ---------- files ---------- */
export function readWav(file) {
  const b = fs.readFileSync(file); let p = 12, fmt = null, data = null;
  while (p + 8 <= b.length) { const id = b.toString('ascii', p, p + 4), len = b.readUInt32LE(p + 4); if (id === 'fmt ') fmt = { tag: b.readUInt16LE(p + 8), ch: b.readUInt16LE(p + 10), sr: b.readUInt32LE(p + 12), bits: b.readUInt16LE(p + 22), sub: len >= 26 ? b.readUInt16LE(p + 32) : null }; if (id === 'data') { data = b.subarray(p + 8, Math.min(b.length, p + 8 + len)); break; } p += 8 + len + (len & 1); }
  if (!fmt || !data) throw new Error('not a WAV: ' + file);
  const tag = fmt.tag === 0xFFFE ? fmt.sub : fmt.tag, bytes = fmt.bits / 8, n = Math.floor(data.length / (bytes * fmt.ch)), ch = Array.from({ length: fmt.ch }, () => new Float32Array(n));
  for (let i = 0; i < n; i++) for (let c = 0; c < fmt.ch; c++) { const o = (i * fmt.ch + c) * bytes; ch[c][i] = tag === 3 ? (bytes === 4 ? data.readFloatLE(o) : data.readDoubleLE(o)) : bytes === 2 ? data.readInt16LE(o) / 32768 : bytes === 3 ? data.readIntLE(o, 3) / 8388608 : data.readInt32LE(o) / 2147483648; }
  if (ch.length === 1) ch.push(ch[0]);
  return { file, sr: fmt.sr, n, L: ch[0], R: ch[1], bits: fmt.bits, float: tag === 3 };
}
const exists = f => fs.existsSync(f);
const sumOf = (...ws) => { const n = ws[0].n, L = new Float32Array(n), R = new Float32Array(n); for (const w of ws) for (let i = 0; i < n; i++) { L[i] += w.L[i]; R[i] += w.R[i]; } return { n, L, R, sr: SR }; };

/* ---------- filters ---------- */
function biquad(x, b0, b1, b2, a1, a2) { const y = new Float32Array(x.length); let x1 = 0, x2 = 0, y1 = 0, y2 = 0; for (let i = 0; i < x.length; i++) { const v = b0 * x[i] + b1 * x1 + b2 * x2 - a1 * y1 - a2 * y2; x2 = x1; x1 = x[i]; y2 = y1; y1 = v; y[i] = v; } return y; }
function rbj(type, f, q = Math.SQRT1_2) { const w = 2 * Math.PI * f / SR, c = Math.cos(w), al = Math.sin(w) / (2 * q), a0 = 1 + al; return type === 'lp' ? [(1 - c) / 2 / a0, (1 - c) / a0, (1 - c) / 2 / a0, -2 * c / a0, (1 - al) / a0] : [(1 + c) / 2 / a0, -(1 + c) / a0, (1 + c) / 2 / a0, -2 * c / a0, (1 - al) / a0]; }
const lp = (x, f, n = 1) => { for (let i = 0; i < n; i++) x = biquad(x, ...rbj('lp', f)); return x; };
const hp = (x, f, n = 1) => { for (let i = 0; i < n; i++) x = biquad(x, ...rbj('hp', f)); return x; };
// BS.1770 K-weighting at 48 kHz
const kw = x => biquad(biquad(x, 1.53512485958697, -2.69169618940638, 1.19839281085285, -1.69065929318241, .73248077421585), 1, -2, 1, -1.99004745483398, .99007225036621);

/* ---------- loudness ---------- */
// K-weighted cumulative power, so the loudness of any window is two subtractions
export function meter(w) {
  if (w._m) return w._m;
  const kl = kw(w.L), kr = w.R === w.L ? kl : kw(w.R), c = new Float64Array(w.n + 1); for (let i = 0; i < w.n; i++) c[i + 1] = c[i] + kl[i] * kl[i] + kr[i] * kr[i];
  const pow = (a, b) => { const i0 = Math.max(0, Math.round(a * SR)), i1 = Math.min(w.n, Math.round(b * SR)); return (c[i1] - c[i0]) / Math.max(1, i1 - i0); };
  const lufs = (a, b) => -.691 + pdB(pow(a, b));
  // momentary: 400 ms windows every 100 ms, by window centre
  const mom = []; for (let t = 0; t + .4 <= w.n / SR + 1e-9; t += .1) mom.push({ t: r2(t + .2), l: lufs(t, t + .4), p: pow(t, t + .4) });
  const a1 = mom.filter(m => m.l > -70), rel = a1.length ? -.691 + pdB(a1.reduce((s, m) => s + m.p, 0) / a1.length) - 10 : -70, a2 = a1.filter(m => m.l > rel);
  const integrated = a2.length ? -.691 + pdB(a2.reduce((s, m) => s + m.p, 0) / a2.length) : -Infinity;
  return (w._m = { lufs, mom, integrated });
}
export const stats = w => { let peak = 0, sq = 0, dcL = 0, dcR = 0, maxDiff = 0; for (let i = 0; i < w.n; i++) { const a = Math.abs(w.L[i]), b = Math.abs(w.R[i]); if (a > peak) peak = a; if (b > peak) peak = b; sq += w.L[i] * w.L[i] + w.R[i] * w.R[i]; dcL += w.L[i]; dcR += w.R[i]; if (i) { const d = Math.max(Math.abs(w.L[i] - w.L[i - 1]), Math.abs(w.R[i] - w.R[i - 1])); if (d > maxDiff) maxDiff = d; } } const rms = Math.sqrt(sq / (2 * w.n)); return { peakDb: dB(peak), rmsDb: dB(rms), crestDb: dB(peak) - dB(rms), dc: Math.max(Math.abs(dcL), Math.abs(dcR)) / w.n, maxDiff }; };
const seg = (w, a, b) => { const i0 = Math.round(a * SR), i1 = Math.min(w.n, Math.round(b * SR)); return { n: i1 - i0, L: w.L.subarray(i0, i1), R: w.R.subarray(i0, i1), sr: SR }; };
const peakIn = (w, a, b) => { let p = 0; for (let i = Math.max(0, Math.round(a * SR)); i < Math.min(w.n, Math.round(b * SR)); i++) p = Math.max(p, Math.abs(w.L[i]), Math.abs(w.R[i])); return p; };
const rmsIn = (x, a, b) => { let s = 0, i0 = Math.max(0, Math.round(a * SR)), i1 = Math.min(x.length, Math.round(b * SR)); for (let i = i0; i < i1; i++) s += x[i] * x[i]; return Math.sqrt(s / Math.max(1, i1 - i0)); };
export function ffLoud(file) {   // the quotable figures, from ffmpeg
  const r = spawnSync(FFMPEG, ['-hide_banner', '-nostats', '-i', file, '-af', 'ebur128=peak=true:framelog=quiet', '-f', 'null', '-'], { encoding: 'utf8' }), s = r.stderr.slice(r.stderr.lastIndexOf('Summary:'));
  const g = re => { const m = s.match(re); return m ? +m[1] : null; };
  return { I: g(/I:\s+(-?[\d.]+) LUFS/), LRA: g(/LRA:\s+(-?[\d.]+) LU/), TP: g(/Peak:\s+(-?[\d.]+) dBFS/) };
}

/* ---------- spectrum ---------- */
function fft(re, im) { const n = re.length; for (let i = 1, j = 0; i < n; i++) { let bit = n >> 1; for (; j & bit; bit >>= 1) j ^= bit; j ^= bit; if (i < j) { let t = re[i]; re[i] = re[j]; re[j] = t; t = im[i]; im[i] = im[j]; im[j] = t; } } for (let len = 2; len <= n; len <<= 1) { const ang = -2 * Math.PI / len, wr = Math.cos(ang), wi = Math.sin(ang); for (let i = 0; i < n; i += len) { let cr = 1, ci = 0; for (let k = 0; k < len / 2; k++) { const a = i + k, b = a + len / 2, xr = re[b] * cr - im[b] * ci, xi = re[b] * ci + im[b] * cr; re[b] = re[a] - xr; im[b] = im[a] - xi; re[a] += xr; im[a] += xi; const t = cr * wr - ci * wi; ci = cr * wi + ci * wr; cr = t; } } } }
// mean power spectrum of x over [a, b] s (Hann, 16384 points, half overlap)
function spectrum(x, a, b, N = 16384) { const i0 = Math.max(0, Math.round(a * SR)), i1 = Math.min(x.length, Math.round(b * SR)), P = new Float64Array(N / 2 + 1); let frames = 0; for (let s = i0; s + N <= Math.max(i1, i0 + N) && s + N <= x.length; s += N / 2) { const re = new Float64Array(N), im = new Float64Array(N); for (let i = 0; i < N; i++) re[i] = x[s + i] * (.5 - .5 * Math.cos(2 * Math.PI * i / N)); fft(re, im); for (let k = 0; k <= N / 2; k++) P[k] += re[k] * re[k] + im[k] * im[k]; frames++; } for (let k = 0; k < P.length; k++) P[k] /= Math.max(1, frames); P.N = N; return P; }
const band = (P, f0, f1) => { let s = 0; const k0 = Math.max(1, Math.round(f0 * P.N / SR)), k1 = Math.min(P.length - 1, Math.round(f1 * P.N / SR)); for (let k = k0; k <= k1; k++) s += P[k]; return s; };
const OCT = [31.25, 62.5, 125, 250, 500, 1000, 2000, 4000, 8000, 16000];
const octaves = P => OCT.map(f => band(P, f / Math.SQRT2, Math.min(f * Math.SQRT2, 23900)));
const stereoSpec = (w, a, b) => { const p = spectrum(w.L, a, b), q = spectrum(w.R, a, b); for (let k = 0; k < p.length; k++) p[k] += q[k]; return p; };
const centroid = P => { let a = 0, b = 0; for (let k = 1; k < P.length; k++) { a += k * SR / P.N * P[k]; b += P[k]; } return a / Math.max(b, 1e-30); };
const corr = (a, b, i0 = 0, i1 = a.length) => { let x = 0, y = 0, z = 0; for (let i = i0; i < i1; i++) { x += a[i] * b[i]; y += a[i] * a[i]; z += b[i] * b[i]; } return y && z ? x / Math.sqrt(y * z) : 1; };

/* ---------- targets ---------- */
// Section loudness against the film's integrated loudness (LU). `max` rows are ceilings.
// Dawn follows the composer's curve for a level film (verses a little under the average, the two
// full sections a little over): what grows across the film is the tone, not the volume. A first
// half 6 LU down would be inaudible on a laptop at low volume and would cost the limiter later.
// The first two seconds are the title card (a ceiling: soft); the hook is 2 to 6 s.
const CURVES = {
  dawn: [[0, 2, -3, 'max'], [2, 6, -4], [6, 14, -1.5], [14, 22, -.5], [22, 24, .5], [24, 31.7, 1.5], [32.6, 38, -1.5], [38.3, 39.9, -10, 'max'], [42, 44, -4], [44, 46, 0], [46, 47.5, 1.5], [47.55, 47.85, -7, 'max'], [48, 50, 2.5], [50, 51.7, -.5], [54, 56, -8, 'max']],
  // the composer's curves for his scores A and B, moved onto the 29-bar form; the opening is held
  // to the same standard as Dawn's (it is the hook), not the composer's -6 to -13
  pulse: [[0, 2, -3, 'max'], [2, 6, -4], [6, 14, -1.5], [14, 22, 0], [22, 24, 1], [24, 31.7, 2], [32.6, 38, -2], [38.3, 39.9, -10, 'max'], [42, 44, -4], [44, 46, 0], [46, 47.5, 1.5], [47.55, 47.85, -7, 'max'], [48, 50, 2.5], [50, 51.7, -.5], [54, 56, -8, 'max']],
  bloom: [[0, 2, -3, 'max'], [2, 6, -4], [6, 14, -2.5], [14, 22, -1.5], [22, 24, -.5], [24, 31.7, 1.5], [32.6, 38, -2], [38.3, 39.9, -10, 'max'], [42, 44, -4.5], [44, 46, -1], [46, 47.5, 1.5], [47.55, 47.85, -7, 'max'], [48, 50, 3], [50, 51.7, -1.5], [54, 56, -8, 'max']],
};
// Octave bands of the full section (48 to 50 s) against the 63 Hz band, dB. Pink noise reads flat.
// A sanity envelope for the genre, not a substitute for a reference track.
const BANDS = {
  dawn: [[-10, 8], [0, 0], [-4, 3], [-7.5, 3], [-9, 3], [-10.5, 3], [-13.5, 3], [-17, 3.5], [-19, 3.5], [-26, 5]],
  // the composer's two envelopes; 31 Hz is one-sided in all three (the kick and the roots sit at 49 to 82 Hz)
  pulse: [[-10, 8], [0, 0], [-4, 2.5], [-8, 2.5], [-9.5, 2.5], [-10.5, 2.5], [-13.5, 2.5], [-16.5, 3], [-17.5, 3], [-24, 4]],
  bloom: [[-10, 8], [0, 0], [-3.5, 2.5], [-6.5, 2.5], [-8, 2.5], [-10, 2.5], [-14, 2.5], [-18, 3], [-21, 3], [-28, 4]],
};
const RANGES = {
  dawn: { pre: -21, wash: 2, lra: [4, 12], crest: [12, 18], phase: [.4, .92], side: [-14, -5], foldLoss: 2, kickOverBass: 6 },
  pulse: { pre: -20, wash: 4.5, lra: [3, 10], crest: [12, 16], phase: [.55, .92], side: [-14, -7], foldLoss: 1.5, kickOverBass: 11 },
  bloom: { pre: -21, wash: null, lra: [4, 13], crest: [13, 18], phase: [.4, .9], side: [-14, -6], foldLoss: 2, kickOverBass: 6 },
};
// where each part should sit when everything plays (48 to 50 s), in LU against the score without effects
const PARTS = {
  dawn: { arp: -7.5, pad: -8, lead: -10.5, kick: -7.5, sub: -5.5, wet: -10 },
  pulse: { kick: -6.5, hat: -14, hatO: -16, clap: -11, sub: -7, bassMid: -12, arp: -6.5, pad: -10, lead: -10.5, wet: -11.5 },
  bloom: { kick: -8, snap: -14, shaker: -17, sub: -5, piano: -7, pad: -7, ost: -11, bell: -14, wet: -9 },
};
const FULL = [48, 50];

/* ---------- the acceptance run ---------- */
function accept(score) {
  const f = n => path.join(OUT, n), need = ['drums', 'bass', 'music', 'mix'].map(s => f(`${score}.${s}.wav`)).concat(f('sfx.wav'));
  for (const n of need) if (!exists(n)) throw new Error('missing ' + n + ' (run render-audio.mjs --score=' + score + ' --all)');
  const drums = readWav(need[0]), bass = readWav(need[1]), music = readWav(need[2]), mix = readWav(need[3]), sfx = readWav(need[4]);
  const pre = exists(f(`${score}.premaster.wav`)) ? readWav(f(`${score}.premaster.wav`)) : null, prelim = exists(f(`${score}.prelim.wav`)) ? readWav(f(`${score}.prelim.wav`)) : null, master = exists(f(`${score}.master.wav`)) ? readWav(f(`${score}.master.wav`)) : null;
  const ev = exists(f(`${score}.events.json`)) ? JSON.parse(fs.readFileSync(f(`${score}.events.json`), 'utf8')) : { marks: [], kicks: [] };
  const R = RANGES[score] || RANGES.dawn, checks = [], add = (id, what, value, target, pass, note) => checks.push({ id, what, value, target, pass: !!pass, ...(note ? { note } : {}) });
  const within = (v, [a, b]) => v >= a && v <= b;

  // --- stems
  const stemSum = sumOf(drums, bass, music, sfx); let nul = 0; for (let i = 0; i < mix.n; i++) nul = Math.max(nul, Math.abs(stemSum.L[i] - mix.L[i]), Math.abs(stemSum.R[i] - mix.R[i]));
  add('null', 'the four stems sum to the mix', r1(dB(nul)) + ' dBFS', 'below -100', dB(nul) < -100);
  for (const [n, w] of [['drums', drums], ['bass', bass], ['music', music], ['sfx', sfx]]) { const s = stats(w); add('dc.' + n, `DC offset, ${n} stem`, s.dc.toExponential(1), 'below 5e-4', s.dc < 5e-4); }
  const sb = stats(bass), sm = stats(music), sx = stats(sfx);
  add('click.bass', 'largest sample-to-sample step, bass stem', r2(sb.maxDiff), 'below 0.06', sb.maxDiff < .06);
  add('click.music', 'largest sample-to-sample step, music stem', r2(sm.maxDiff), 'below 0.15', sm.maxDiff < .15);
  add('sfx.peak', 'effects stem sample peak', r1(sx.peakDb) + ' dBFS', 'at or below -4', sx.peakDb <= -3.95);
  const bassLow = corr(lp(bass.L, 120, 2), lp(bass.R, 120, 2)); add('bass.mono', 'bass stem is mono', r2(bassLow), '1.00', bassLow > .999);
  { const P = stereoSpec(bass, 24, 50), hi = pdB(band(P, 8000, 23900) / band(P, 20, 23900)); add('alias.bass', 'bass stem energy above 8 kHz', r1(hi) + ' dB of total', 'below -60', hi < -60); }

  // --- onsets after silence: nothing may be early, nothing late by more than 1 ms
  const onset = (w, t, win = .05) => { const i0 = Math.round(t * SR), pk = peakIn(w, t, t + win); for (let i = i0 - 480; i < i0 + win * SR; i++) if (Math.max(Math.abs(w.L[i]), Math.abs(w.R[i])) > .05 * pk) return (i - i0) / SR * 1000; return null; };   // 5% of the hit: the tails that come back when the vacuum lifts (2 ms early, by design) are far below it
  for (const [id, w, t, what] of [['stamp', sfx, 32, 'the stamp (effects stem)'], ['logo', sfx, 52, 'the last chord (effects stem)'], ['kick24', drums, 24, 'first kick, 24.0 (drums stem)'], ['kick52', drums, 52, 'last kick, 52.0 (drums stem)']]) { const ms = onset(w, t); add('onset.' + id, 'onset of ' + what, ms == null ? 'none' : r2(ms) + ' ms', '0 to +1 ms, never early', ms != null && ms >= -.011 && ms <= 1); }
  { const t = 24, pk = peakIn(sfx, t, t + .2); let ms = null; for (let i = Math.round(t * SR); i < (t + .1) * SR; i++) if (Math.abs(sfx.L[i]) > .1 * pk) { ms = (i - t * SR) / SR * 1000; break; } add('onset.subdrop', 'sub drop at 24.0 reaches 10% of its peak', ms == null ? 'none' : r1(ms) + ' ms', '+2 to +12 ms', ms != null && ms >= 2 && ms <= 12); }

  // --- kick against bass, in the choruses (30 to 150 Hz, the 60 ms after each kick)
  const chorusKicks = ev.kicks.filter(k => (k.t >= 24 && k.t < 30) || (k.t >= 48 && k.t < 50));
  if (chorusKicks.length) { const dl = lp(hp(drums.L, 30), 150), bl = lp(hp(bass.L, 30), 150); let worst = Infinity, pkRatio = []; for (const k of chorusKicks) { const a = dB(rmsIn(dl, k.t, k.t + .06)) - dB(rmsIn(bl, k.t, k.t + .06)); worst = Math.min(worst, a); pkRatio.push(dB(peakIn(drums, k.t, k.t + .06)) - dB(peakIn(bass, k.t, k.t + .06))); } add('kickbass', 'kick over bass, 30 to 150 Hz, worst chorus kick', r1(worst) + ' dB', `at least ${R.kickOverBass}`, worst >= R.kickOverBass, `kick peak over bass peak, median ${r1(pkRatio.sort((a, b) => a - b)[pkRatio.length >> 1])} dB`); }
  // the 10 ms after each chorus kick against the 10 ms before it (a wash would fill the gap)
  if (chorusKicks.length && pre && R.wash != null) { let worst = Infinity; for (const k of chorusKicks.filter(k => k.t >= 24.4)) worst = Math.min(worst, dB(rmsIn(pre.L, k.t, k.t + .01)) - dB(rmsIn(pre.L, k.t - .015, k.t - .005))); add('wash', 'a chorus kick stands above what precedes it', r1(worst) + ' dB', `at least ${R.wash}`, worst >= R.wash); }

  // --- the chime's hole
  { const others = sumOf(drums, bass, music), d = meter(sfx).lufs(40, 40.4) - meter(others).lufs(40, 40.4); add('chimehole', 'the chime over the music, 40.0 to 40.4', r1(d) + ' LU', 'at least 6', d >= 6); }

  const final = master || pre || mix, which = master ? 'master' : pre ? 'premaster' : 'mix', M = meter(final), sf = stats(final), ff = ffLoud(final.file);
  // --- delivery
  if (pre) { const mp = meter(pre), sp = stats(pre); add('pre.loudness', 'premaster integrated loudness', r1(mp.integrated) + ' LUFS', `${R.pre} +/- 1.5`, Math.abs(mp.integrated - R.pre) <= 1.5); add('pre.stamp', 'premaster sample peak at the stamp', r1(dB(peakIn(pre, 32, 32.5))) + ' dBFS', 'at or below -4', dB(peakIn(pre, 32, 32.5)) <= -3.9); add('pre.peak', 'premaster sample peak, whole film', r1(sp.peakDb) + ' dBFS', 'at or below -4', sp.peakDb <= -3.9); add('pre.crest', 'premaster crest', r1(sp.crestDb) + ' dB', '15 to 21', within(sp.crestDb, [15, 21])); if (chorusKicks.length) { const kp = chorusKicks.map(k => dB(peakIn(pre, k.t, k.t + .05))).sort((a, b) => a - b); add('pre.kicks', 'premaster peak on chorus kicks (median)', r1(kp[kp.length >> 1]) + ' dBFS', '-11 to -6.5', within(kp[kp.length >> 1], [-11, -6.5])); } }
  if (master) {
    add('len', 'length', `${master.n} frames at ${master.sr} Hz`, `${DUR * SR} at 48000`, master.n === DUR * SR && master.sr === SR);
    add('loudness', 'integrated loudness', `${ff.I} LUFS`, '-14.0 +/- 0.3', Math.abs(ff.I + 14) <= .3, `own meter ${r2(M.integrated)}`);
    add('truepeak', 'true peak', `${ff.TP} dBTP`, 'at or below -1.0', ff.TP <= -1);
    add('lra', 'loudness range', `${ff.LRA} LU`, `${R.lra[0]} to ${R.lra[1]}`, within(ff.LRA, R.lra));
    add('crest', 'crest factor, whole film', r1(sf.crestDb) + ' dB', `${R.crest[0]} to ${R.crest[1]}`, within(sf.crestDb, R.crest));
    const cf = stats(seg(final, ...FULL)); add('crest.full', 'crest factor, 48 to 50 s', r1(cf.crestDb) + ' dB', '9 to 14', within(cf.crestDb, [9, 14]));
    add('click.master', 'largest sample-to-sample step, master', r2(sf.maxDiff), 'below 0.45', sf.maxDiff < .45);
    add('dc.master', 'DC offset, master', sf.dc.toExponential(1), 'below 5e-4', sf.dc < 5e-4);
    let head = 0, tail = 0; for (let i = 0; i < 16; i++) head = Math.max(head, Math.abs(final.L[i]), Math.abs(final.R[i])); const first = Math.max(Math.abs(final.L[0]), Math.abs(final.R[0])); for (let i = final.n - 64; i < final.n; i++) tail = Math.max(tail, Math.abs(final.L[i]), Math.abs(final.R[i]));
    add('ends', 'first sample, first 16 and last 64 samples', `${r1(dB(first))} / ${r1(dB(head))} / ${r1(dB(tail))} dBFS`, 'below -90 / below -40 / below -90 (the film starts on a hit, under a 5 ms fade)', dB(first) < -90 && dB(head) < -40 && dB(tail) < -90);
    if (prelim) {   // what the limiter took: 5 ms peaks, master against the same signal before the limiter
      const blk = Math.round(.005 * SR), nb = Math.floor(master.n / blk), ratio = [], at = []; for (let b = 0; b < nb; b++) { const p0 = peakIn(prelim, b * blk / SR, (b + 1) * blk / SR), p1 = peakIn(master, b * blk / SR, (b + 1) * blk / SR); if (p0 > .03) { ratio.push(dB(p1) - dB(p0)); at.push(b * blk / SR); } }
      const quiet = ratio.filter((r, i) => dB(peakIn(prelim, at[i], at[i] + .005)) < -9).sort((a, b) => a - b), g0 = quiet[quiet.length >> 1] || 0, gr = (a, b) => { let m = 0; ratio.forEach((r, i) => { if (at[i] >= a && at[i] < b) m = Math.max(m, g0 - r); }); return m; };
      const st = gr(32, 32.6); let kk = 0; for (const k of chorusKicks) kk = Math.max(kk, gr(k.t, k.t + .06)); let any = 0, anyT = 0; ratio.forEach((r, i) => { if (g0 - r > any && !(at[i] >= 32 && at[i] < 32.6)) { any = g0 - r; anyT = at[i]; } });
      add('limit.stamp', 'limiter gain reduction at the stamp', r1(st) + ' dB', 'at most 4', st <= 4);
      add('limit.kicks', 'limiter gain reduction on chorus kicks', r1(kk) + ' dB', 'at most 2', kk <= 2);
      add('limit.other', 'limiter gain reduction anywhere else', `${r1(any)} dB at ${r2(anyT)} s`, 'at most 3', any <= 3);
    }
  }
  // --- the stamp is the biggest moment
  { const inside = M.mom.filter(m => m.t >= 32 && m.t <= 32.5), outside = M.mom.filter(m => m.t < 31.9 || m.t > 33), top = M.mom.reduce((a, m) => m.l > a.l ? m : a), a = Math.max(...inside.map(m => m.l)), o = outside.reduce((x, m) => m.l > x.l ? m : x); add('stamp', `the stamp is the loudest moment (${which})`, `${r1(a)} LUFS at the stamp; next ${r1(o.l)} at ${o.t} s; film maximum at ${top.t} s`, 'loudest, by 1 LU or more', top.t >= 31.95 && top.t <= 32.75 && a - o.l >= 1); }
  // --- the last chord rings to the end of the film: still there at 57.3 s, no step on the way down, and
  //     nothing cut at 58.0 (the closing fade ends at 57.95)
  if (master) { const pk = (a, b) => dB(peakIn(final, a, b)); let last = 0; for (let i = final.n - 1; i > 0; i--) if (Math.max(Math.abs(final.L[i]), Math.abs(final.R[i])) > .0031623) { last = i / SR; break; }
    let step = 0, stepT = 0; for (let t = 52.5; t + .04 <= last; t += .02) { const d = pk(t, t + .02) - pk(t + .02, t + .04); if (d > step) { step = d; stepT = t; } }
    const at573 = pk(57.3, 57.35), before = pk(57.8, 57.9);
    add('tail', 'the last chord rings to the end', `${r1(at573)} dBFS at 57.3 s; above -50 dBFS until ${r2(last)} s; largest fall between 20 ms peaks ${r1(step)} dB at ${r2(stepT)} s; ${r1(before)} dBFS at 57.8 to 57.9 s`, 'above -50 at 57.3; above -50 until 57.5 or later; no fall over 4 dB in 20 ms; still sounding (above -90) at 57.8', at573 > -50 && last >= 57.5 && step <= 4 && before > -90); }
  // --- true silence before the stamp and before the last chord
  for (const t of [32, 52]) { const v = dB(Math.max(rmsIn(final.L, t - .2, t - .01), rmsIn(final.R, t - .2, t - .01))); add('vacuum.' + t, `silence before ${t}.0`, r1(v) + ' dBFS', 'at or below -50', v <= -50); }
  // --- energy by section
  const sections = (CURVES[score] || []).map(([a, b, want, kind]) => { const got = M.lufs(a, b) - M.integrated; return { from: a, to: b, want, kind: kind || 'target', got: r1(got), pass: kind === 'max' ? got <= want : Math.abs(got - want) <= 1.5 }; });
  add('curve', 'energy by section', `${sections.filter(s => s.pass).length} of ${sections.length} sections on target`, 'all within 1.5 LU', sections.every(s => s.pass));
  // --- tone
  const P = stereoSpec(final, ...FULL), o = octaves(P), rel = o.map(v => r1(pdB(v / o[1]))), env = BANDS[score] || BANDS.dawn, bandRows = OCT.map((fq, i) => ({ hz: fq, got: rel[i], want: env[i][0], tol: env[i][1], pass: i === 1 || (i === 0 ? rel[i] <= env[i][0] + env[i][1] : Math.abs(rel[i] - env[i][0]) <= env[i][1]) }));   // 31 Hz has a ceiling only: less rumble is not a fault
  add('bands', 'octave bands, 48 to 50 s', `${bandRows.filter(b => b.pass).length} of 10 inside the envelope`, 'all', bandRows.every(b => b.pass));
  add('mud.1', '250 Hz band under the 125 Hz band', r1(rel[2] - rel[3]) + ' dB', 'at least 3', rel[2] - rel[3] >= 3);
  { const v = pdB((o[3] + o[4]) / (o[5] + o[6])); add('mud.2', '250+500 Hz over 1k+2k Hz', r1(v) + ' dB', 'at most 7', v <= 7); }
  { const v = pdB(band(P, 500, 2000) / band(P, 2000, 5000)); add('harsh.1', '0.5 to 2 kHz over 2 to 5 kHz', r1(v) + ' dB', 'at least 5', v >= 5); }
  { const fc = []; for (let f = 1250; f <= 8000; f *= Math.pow(2, 1 / 3)) fc.push(f); const t = fc.map(f => pdB(band(P, f / Math.pow(2, 1 / 6), f * Math.pow(2, 1 / 6)))); let worst = -Infinity, wf = 0; for (let i = 1; i < fc.length - 1; i++) if (fc[i] >= 2000 && fc[i] <= 5000) { const d = t[i] - (t[i - 1] + t[i + 1]) / 2; if (d > worst) { worst = d; wf = fc[i]; } } add('harsh.2', 'a third-octave band standing out between 2 and 5 kHz', `${r1(worst)} dB at ${Math.round(wf)} Hz`, 'at most 3', worst <= 3); }
  // --- stereo
  { const c = corr(final.L, final.R); add('phase', 'left-right correlation, whole film', r2(c), `${R.phase[0]} to ${R.phase[1]}`, within(c, R.phase)); let worst = 1, wt = 0; for (let t = 0; t + 1 <= DUR; t += .5) { if (dB(peakIn(final, t, t + 1)) < -45) continue; const v = corr(final.L, final.R, Math.round(t * SR), Math.round((t + 1) * SR)); if (v < worst) { worst = v; wt = t; } } add('phase.min', 'lowest correlation in any second', `${r2(worst)} at ${wt} s`, 'at least 0.1', worst >= .1);
    const low = corr(lp(final.L, 120, 2), lp(final.R, 120, 2)); add('lowmono', 'correlation below 120 Hz', r2(low), 'at least 0.97', low >= .97);
    let mid = 0, side = 0; for (let i = 0; i < final.n; i++) { const m = (final.L[i] + final.R[i]) / 2, s = (final.L[i] - final.R[i]) / 2; mid += m * m; side += s * s; } const sm2 = pdB(side / mid); add('side', 'side over mid energy', r1(sm2) + ' dB', `${R.side[0]} to ${R.side[1]}`, within(sm2, R.side));
    const monoL = new Float32Array(final.n); for (let i = 0; i < final.n; i++) monoL[i] = (final.L[i] + final.R[i]) / 2; const mono = { n: final.n, L: monoL, R: monoL }, loss = M.integrated - meter(mono).integrated; add('fold', 'loudness lost in mono', r1(loss) + ' LU', `at most ${R.foldLoss}`, loss <= R.foldLoss);
    const pm = spectrum(monoL, ...FULL), om = octaves(pm), drop = OCT.map((fq, i) => r1(pdB(o[i] / 2 / om[i]))), wd = Math.max(...drop.slice(1, 9)); add('fold.bands', 'worst octave band lost in mono (63 Hz to 8 kHz)', `${wd} dB`, 'at most 3', wd <= 3); }
  // --- the opening, as a laptop plays it (nothing below 200 Hz). The film opens on its title: the first
  //     two seconds are soft but must be there at low volume; the hook (2.0 to 6.0) must be heard, and the
  //     accents on the cut at 2.0, at 2.5 and on the lock at 5.0 must stand out of what precedes them
  { const lap = { n: final.n, L: hp(final.L, 200, 2), R: hp(final.R, 200, 2) }, ml = meter(lap), a = ml.lufs(0, 2) - ml.integrated, b = ml.lufs(2, 6) - ml.integrated;
    add('open.level', 'the title (0 to 2 s) and the hook (2 to 6 s) above 200 Hz, against the film above 200 Hz', `${r1(a)} / ${r1(b)} LU`, 'the title -13 to -4 and at least 3 under the hook; the hook at least -4.5', a >= -13 && a <= -4 && b - a >= 3 && b >= -4.5);
    const lead = t => dB(rmsIn(lap.L, t, t + .12)) - dB(rmsIn(lap.L, t - .15, t - .03)), a2 = lead(2), a25 = lead(2.5), a5 = lead(5), mh = hp(music.L, 200, 2), soft = dB(rmsIn(mh, 2, 2.15)) - dB(rmsIn(mh, 0, .15));   // the arrival is judged in the music stem: the film's own 0.0 is the effects' sub drop
    add('open.accents', 'accents above 200 Hz: 2.0, 2.5 and 5.0 against what precedes them; the cut at 2.0 against the arrival at 0.0 (music stem)', `+${r1(a2)} / +${r1(a25)} / +${r1(a5)} dB; ${r1(soft)} dB over the arrival`, 'at least +3 each; the cut at least 3 dB over the arrival', a2 >= 3 && a25 >= 3 && a5 >= 3 && soft >= 3);
    // where each of the four lands in the master: the first sample (above 200 Hz, either channel) to pass a
    // quarter of the way from what precedes the hit to the hit's own peak in its first 30 ms. Bloom rolls its
    // chords low to high, 8 ms a note: the first note is on the frame, and that is what this finds
    const env = i => Math.max(Math.abs(lap.L[i]), Math.abs(lap.R[i]));
    const land = t => { const i0 = Math.round(t * SR); let base = 0, pk = 0; for (let i = Math.max(0, i0 - Math.round(.06 * SR)); i < i0 - Math.round(.004 * SR); i++) base = Math.max(base, env(i)); for (let i = i0; i < i0 + Math.round(.03 * SR); i++) pk = Math.max(pk, env(i)); if (pk < 1.5 * base) return null; const th = base + .25 * (pk - base); for (let i = Math.max(0, i0 - Math.round(.004 * SR)); i < i0 + Math.round(.03 * SR); i++) if (env(i) > th) return (i - i0) / SR * 1000; return null; };
    const at = [0, 2, 2.5, 5].map(land), okAt = at.every(ms => ms != null && ms >= -.5 && ms <= 1000 / 60);
    add('open.onsets', 'the intro lands on the picture: 0.0, 2.0, 2.5 and 5.0 in the master', at.map(ms => ms == null ? 'none' : (ms >= 0 ? '+' : '') + r1(ms) + ' ms').join(' / '), 'never early, and inside the frame it is cut on (16.7 ms at 60 fps); the arrival sits under a 5 ms fade-in, and a felt piano chord takes about 10 ms to speak', okAt); }
  // --- air before 24 s: the top must not be absent while the film is still cold, nor harsh
  { const share = (a, b) => { const p = stereoSpec(final, a, b); return pdB(band(p, 3000, 20000) / band(p, 150, 20000)); }, v = share(6, 22), c = share(24, 31.7), pv = stereoSpec(final, 6, 22), hv = pdB(band(pv, 500, 2000) / band(pv, 2000, 5000));
    add('air.verse', 'energy above 3 kHz as a share of everything above 150 Hz, 6 to 22 s', `${r1(v)} dB (24 to 31.7 s: ${r1(c)})`, 'at least -22, and within 8 dB of the first full section', v >= -22 && c - v <= 8);
    add('harsh.verse', '0.5 to 2 kHz over 2 to 5 kHz, 6 to 22 s', r1(hv) + ' dB', 'at least 5', hv >= 5); }
  // --- the film warms: the music's upper mids (0.8 to 3 kHz) grow against its lower mids (150 to
  //     800 Hz) from verse 1 to verse 2, and the build opens fast
  { const tilt = (a, b) => { const p = stereoSpec(music, a, b); return pdB(band(p, 800, 3000) / band(p, 150, 800)); }, c = (a, b) => centroid(stereoSpec(music, a, b)), v1 = tilt(20, 22) - tilt(8, 10), v2 = c(44, 46) / c(42, 44);
    add('motion', 'the music opens: upper mids over lower mids, bar 11 against bar 5; centroid, bar 23 over bar 22', `+${r1(v1)} dB, ${r2(v2)}x`, 'at least +1.5 dB and 1.5x', v1 >= 1.5 && v2 >= 1.5); }
  // --- a burst of highs that no scheduled onset explains is a click (an onset covers its eight echoes;
  //     a drum hit covers the 60 ms of noise it is made of)
  { const h = hp(final.L, 12000, 2), w = Math.round(.002 * SR), e = []; for (let i = 0; i + w <= h.length; i += w) { let s = 0; for (let k = 0; k < w; k++) s += h[i + k] * h[i + k]; e.push(s / w); } const on = new Set(); for (const m of ev.marks) for (let k = 0; k <= (m.part === 'drums' ? 0 : 8); k++) for (let d = -1; d <= (m.part === 'drums' ? 30 : 2); d++) on.add(Math.floor((m.t + k * .375) / .002) + d); let n = 0; const where = []; for (let i = 10; i < e.length - 10; i++) { if (e[i] < 1e-7 || on.has(i)) continue; const nb = e.slice(i - 10, i).concat(e.slice(i + 1, i + 11)).sort((a, b) => a - b), med = nb[10]; if (pdB(e[i] / Math.max(med, 1e-12)) > 20) { n++; if (where.length < 6) where.push(r2(i * .002)); } } add('click.hf', 'unexplained bursts above 12 kHz', n + (where.length ? ' (' + where.join(', ') + ' s)' : ''), 'none', n === 0); }

  const report = { score, measuredOn: which, cues: { hash: ev.cueHash, count: ev.cues }, stage0: exists(f('stage0.json')) ? (() => { const s = JSON.parse(fs.readFileSync(f('stage0.json'), 'utf8')); return { kick: s.kick, ir: s.ir, determinism: s.determinism }; })() : null,
    summary: { pass: checks.filter(c => c.pass).length, fail: checks.filter(c => !c.pass).length, failed: checks.filter(c => !c.pass).map(c => c.id) },
    loudness: { integrated: r2(M.integrated), ffmpeg: ff, peakDb: r2(sf.peakDb), crestDb: r2(sf.crestDb) }, checks, sections, bands: bandRows,
    stems: Object.fromEntries([['drums', drums], ['bass', bass], ['music', music], ['sfx', sfx]].map(([n, w]) => [n, { peakDb: r1(stats(w).peakDb), full: r1(meter(w).lufs(...FULL)), chorus1: r1(meter(w).lufs(24, 30)) }])) };
  fs.writeFileSync(f(`${score}.report.json`), JSON.stringify(report, null, 1));
  if (master || pre) { const src = (master || pre).file; spawnSync(FFMPEG, ['-y', '-hide_banner', '-loglevel', 'error', '-i', src, '-lavfi', 'showspectrumpic=s=1740x512:legend=1:scale=log:fscale=log:color=intensity:drange=96', f(`${score}.spectrum.png`)]); spawnSync(FFMPEG, ['-y', '-hide_banner', '-loglevel', 'error', '-i', src, '-lavfi', 'showwavespic=s=1740x360:split_channels=1:colors=0x4E81FA|0x22D3EE', f(`${score}.wave.png`)]); }
  const w = Math.max(...checks.map(c => c.what.length));
  for (const c of checks) console.log(`${c.pass ? 'ok  ' : 'FAIL'}  ${c.what.padEnd(w)}  ${String(c.value)}   [${c.target}]${c.note ? '  (' + c.note + ')' : ''}`);
  console.log('\nsection            want    got'); for (const s of sections) console.log(`${s.pass ? 'ok  ' : 'FAIL'} ${String(s.from).padStart(5)}-${String(s.to).padEnd(5)}  ${(s.kind === 'max' ? '<=' : '  ') + String(s.want).padStart(5)}  ${String(s.got).padStart(5)}`);
  console.log('\nband Hz   want   got'); for (const b of bandRows) console.log(`${b.pass ? 'ok  ' : 'FAIL'} ${String(b.hz).padStart(6)}  ${String(b.want).padStart(5)}  ${String(b.got).padStart(5)}  (+/-${b.tol})`);
  console.log(`\n${report.summary.pass} pass, ${report.summary.fail} fail on the ${which}; integrated ${r2(M.integrated)} LUFS`);
  return report;
}

function parts(score) {
  const dir = path.join(OUT, 'parts'), files = fs.readdirSync(dir).filter(n => n.startsWith(score + '.') && n.endsWith('.wav')), all = readWav(path.join(dir, `${score}.nosfx.wav`)), M = meter(all).lufs(...FULL), C = meter(all).lufs(24, 30);
  console.log(`reference: the score without effects reads ${r1(M)} LUFS at 48-50 s and ${r1(C)} at 24-30 s, integrated ${r1(meter(all).integrated)}, peak ${r1(stats(all).peakDb)} dBFS`);
  console.log('part        48-50 s   rel M   want   move   24-30 s   rel     peak');
  const want = PARTS[score] || {};
  for (const n of files) { const w = readWav(path.join(dir, n)), m = meter(w), a = m.lufs(...FULL), b = m.lufs(24, 30), name = n.slice(score.length + 1, -4), t = want[name]; console.log(`${name.padEnd(10)} ${String(r1(a)).padStart(7)}  ${String(r1(a - M)).padStart(6)}  ${String(t == null ? '' : t).padStart(5)}  ${String(t == null || a < -100 ? '' : (t - (a - M) > 0 ? '+' : '') + r1(t - (a - M))).padStart(5)}  ${String(r1(b)).padStart(7)}  ${String(r1(b - C)).padStart(6)}  ${String(r1(stats(w).peakDb)).padStart(6)}`); }
}

// the three masters side by side: they must be within 0.3 LU, or the choice is "the louder one"
function compare() {
  const rows = ['dawn', 'bloom', 'pulse'].filter(s => exists(path.join(OUT, `${s}.master.wav`))).map(s => { const file = path.join(OUT, `${s}.master.wav`), l = ffLoud(file), w = readWav(file), m = meter(w), st = stats(w), a = exists(path.join(OUT, `${s}.master.m4a`)) ? ffLoud(path.join(OUT, `${s}.master.m4a`)) : null, rep = exists(path.join(OUT, `${s}.report.json`)) ? JSON.parse(fs.readFileSync(path.join(OUT, `${s}.report.json`), 'utf8')).summary : null; return { score: s, I: l.I, own: r2(m.integrated), TP: l.TP, LRA: l.LRA, crest: r1(st.crestDb), frames: w.n, m4aI: a && a.I, m4aTP: a && a.TP, checks: rep ? `${rep.pass} pass, ${rep.fail} fail` : '' }; });
  for (const r of rows) console.log(`${r.score.padEnd(6)} I ${r.I} LUFS (own meter ${r.own})  TP ${r.TP} dBTP  LRA ${r.LRA} LU  crest ${r.crest} dB  ${r.frames} frames   m4a: I ${r.m4aI}, TP ${r.m4aTP}   ${r.checks}`);
  const own = rows.map(r => r.own), spread = Math.max(...own) - Math.min(...own); console.log(`spread between the masters: ${r2(spread)} LU (own meter) ${spread <= .3 ? 'ok' : 'FAIL'}  [at most 0.3]`);
  fs.writeFileSync(path.join(OUT, 'compare.json'), JSON.stringify({ rows, spread: r2(spread), pass: spread <= .3 }, null, 1));
}

// loudness of each stem in each section: who is carrying what
function breakdown(score) {
  const names = ['drums', 'bass', 'music', 'sfx', 'mix'], ws = names.map(n => readWav(path.join(OUT, n === 'sfx' ? 'sfx.wav' : `${score}.${n}.wav`))), ms = ws.map(meter);
  const rows = [[0, 2], [2, 6], [6, 14], [14, 22], [22, 24], [24, 31.7], [32, 32.5], [32.6, 38], [38.3, 39.9], [40, 41], [41, 42], [42, 44], [44, 46], [46, 47.5], [47.55, 47.95], [48, 50], [50, 51.7], [52, 53], [54, 56]];
  console.log('section        ' + names.map(n => n.padStart(7)).join('') + '   peak(mix)');
  for (const [a, b] of rows) console.log(`${String(a).padStart(5)}-${String(b).padEnd(6)}  ` + ms.map(m => String(r1(m.lufs(a, b))).padStart(7)).join('') + '   ' + r1(dB(peakIn(ws[4], a, b))));
  console.log('integrated     ' + ms.map(m => String(r1(m.integrated)).padStart(7)).join(''));
  const tops = (m, label) => { const seen = [], out = []; for (const x of m.mom.slice().sort((p, q) => q.l - p.l)) { if (seen.some(t => Math.abs(t - x.t) < .6)) continue; seen.push(x.t); out.push(`${x.t}s ${r1(x.l)}`); if (out.length >= 7) break; } console.log(`loudest moments of the ${label}: ` + out.join(', ')); };
  tops(ms[4], 'mix'); const mf = path.join(OUT, `${score}.master.wav`); if (exists(mf)) tops(meter(readWav(mf)), 'master');
}

if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  const ARG = Object.fromEntries(process.argv.slice(2).map(a => { const [k, v] = a.replace(/^--/, '').split('='); return [k, v ?? true]; }));
  if (ARG.file) { const w = readWav(path.resolve(ARG.file)), m = meter(w), s = stats(w), o = octaves(stereoSpec(w, +(ARG.from ?? 0), +(ARG.to ?? w.n / SR))); console.log(JSON.stringify({ integrated: r2(m.integrated), window: ARG.from != null ? r2(m.lufs(+ARG.from, +ARG.to)) : undefined, ...Object.fromEntries(Object.entries(s).map(([k, v]) => [k, +v.toPrecision(4)])), ffmpeg: ffLoud(w.file), bandsVs63: OCT.map((f, i) => [f, r1(pdB(o[i] / o[1]))]) })); }
  else if (ARG.compare) compare();
  else if (ARG.breakdown) breakdown(ARG.score);
  else if (ARG.parts) parts(ARG.score);
  else if (ARG.score) accept(ARG.score);
  else console.log('see the header of this file');
}
