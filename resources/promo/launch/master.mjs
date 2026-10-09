#!/usr/bin/env node
// Stems to master, in ffmpeg. All dynamics live here, not in the Web Audio graph.
//
//   node master.mjs --score=dawn
//     out/audio/dawn.{drums,bass,music}.wav + sfx.wav
//       -> dawn.premaster.wav   stems summed (bus compression on drums and music, bass forced mono), ends faded
//       -> dawn.prelim.wav      tone, glue and gain, before the limiter (kept so the limiter's work can be measured)
//       -> dawn.master.wav      48 kHz 24-bit, -14.0 LUFS integrated, true peak at or below -1.0 dBTP
//       -> dawn.master.m4a      the same, AAC 192k with the index first, for a review page
//
// Three things measured on this ffmpeg build (9.0.2) and kept:
//   alimiter's default level=true adds 6 dB under a -6 dB limit: always level=false
//   asoftclip with oversampling attenuates: not used
//   one-pass loudnorm is dynamic and outputs 192 kHz: two passes, must report "linear", always -ar 48000
import fs from 'node:fs';
import path from 'node:path';
import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import { readWav, meter, FFMPEG, DUR } from './analyze.mjs';

const here = path.dirname(fileURLToPath(import.meta.url)), OUT = path.join(here, 'out/audio'), TMP = path.join(OUT, 'tmp');
const ARG = Object.fromEntries(process.argv.slice(2).map(a => { const [k, v] = a.replace(/^--/, '').split('='); return [k, v ?? true]; }));
const score = ARG.score; if (!score || score === true) { console.log('usage: node master.mjs --score=dawn'); process.exit(1); }
const f = n => path.join(OUT, n), FULL = [48, 50];
const ff = args => { const r = spawnSync(FFMPEG, ['-y', '-hide_banner', '-nostats', ...args], { encoding: 'utf8', maxBuffer: 1 << 26 }); if (r.status !== 0) throw new Error('ffmpeg failed: ' + r.stderr.slice(-1500)); return r.stderr; };
fs.mkdirSync(TMP, { recursive: true });

/* ---------- M1: bus compression, tuned by measurement ----------
   The threshold is found so the stem's loudness where everything plays (48 to 50 s) drops 1 to
   2 LU with no makeup; the makeup then gives that back. If no threshold lands there the
   compressor is left out: the design does not depend on it. */
function tune(stem, head, comp) {
  const src = f(`${score}.${stem}.wav`), base = meter(readWav(src)).lufs(...FULL);
  if (!(base > -60)) return { use: false, why: 'silent where it would be tuned' };
  const drop = th => { const out = path.join(TMP, `${score}.${stem}.tune.wav`); ff(['-i', src, '-af', `${head}acompressor=threshold=${th}dB:${comp}:makeup=1`, '-c:a', 'pcm_f32le', '-ar', '48000', out]); return base - meter(readWav(out)).lufs(...FULL); };
  let lo = -42, hi = -6, best = null;
  for (let i = 0; i < 9; i++) { const th = (lo + hi) / 2, d = drop(th); if (d >= 1 && d <= 2 && (!best || Math.abs(d - 1.5) < Math.abs(best.d - 1.5))) best = { th, d }; if (d > 1.5) lo = th; else hi = th; if (best && Math.abs(best.d - 1.5) < .1) break; }
  return best ? { use: true, threshold: +best.th.toFixed(2), drop: +best.d.toFixed(2) } : { use: false, why: 'no threshold gave a 1 to 2 LU drop' };
}
const DCOMP = 'ratio=3:attack=20:release=120:knee=6dB:detection=rms:link=maximum', MCOMP = 'ratio=2:attack=30:release=200:knee=6dB:detection=rms:link=maximum';
const td = ARG.nocomp ? { use: false, why: '--nocomp' } : tune('drums', '', DCOMP), tm = ARG.nocomp ? { use: false, why: '--nocomp' } : tune('music', 'highpass=f=120:poles=2,', MCOMP);
const dChain = td.use ? `acompressor=threshold=${td.threshold}dB:${DCOMP}:makeup=${td.drop}dB` : 'anull';
const mChain = 'highpass=f=120:poles=2' + (tm.use ? `,acompressor=threshold=${tm.threshold}dB:${MCOMP}:makeup=${tm.drop}dB` : '');
ff(['-i', f(`${score}.drums.wav`), '-i', f(`${score}.bass.wav`), '-i', f(`${score}.music.wav`), '-i', f('sfx.wav'), '-filter_complex',
  `[0:a]${dChain}[d];[1:a]highpass=f=30:poles=2,pan=stereo|c0=0.5*c0+0.5*c1|c1=0.5*c0+0.5*c1[b];[2:a]${mChain}[m];` +
  `[d][b][m][3:a]amix=inputs=4:normalize=0:duration=longest,afade=t=in:st=0:d=0.005,afade=t=out:st=${DUR - 1.5}:d=1.45,apad=whole_dur=${DUR},atrim=0:${DUR}[o]`,
  '-map', '[o]', '-c:a', 'pcm_f32le', '-ar', '48000', f(`${score}.premaster.wav`)]);

/* ---------- M2: tone, glue, limiter, loudness ---------- */
// Tone per score: the composer's defaults (280 Hz -1.5, 3.2 kHz -1, shelf at 10 kHz +1.5), each moved by
// at most 3 dB toward the band targets in analyze.mjs after measuring. `mid` is one more bell at 800 Hz.
const TONE = { default: { g280: -1.5, mid: 0, g3200: -1, shelf: 1.5 }, dawn: { g280: -.5, mid: -3, g3200: 2, shelf: -1 }, bloom: { g280: 0, mid: -3, g3200: 2, shelf: -1 }, pulse: { g280: -1.5, mid: -3, g3200: 0, shelf: -3 } };
const tn = TONE[score] || TONE.default;
const PRE = `highpass=f=28:poles=2,equalizer=f=280:t=q:w=0.9:g=${tn.g280},equalizer=f=800:t=q:w=0.8:g=${tn.mid},equalizer=f=3200:t=q:w=1.2:g=${tn.g3200},highshelf=f=10000:t=s:w=0.7:g=${tn.shelf},acompressor=threshold=-18dB:ratio=1.6:attack=30:release=250:knee=6dB:makeup=1dB:detection=rms:link=maximum`;
const loud = s => { const t = s.slice(s.lastIndexOf('Summary:')); return { I: +t.match(/I:\s+(-?[\d.]+) LUFS/)[1], TP: +(t.match(/Peak:\s+(-?[\d.]+) dBFS/) || [0, NaN])[1] }; };
const I0 = loud(ff(['-i', f(`${score}.premaster.wav`), '-af', `${PRE},ebur128=peak=true:framelog=quiet`, '-f', 'null', '-'])).I;
const G = +(-13.7 - I0).toFixed(2);   // 0.3 LU hot, so loudnorm's trim is downward and stays linear
const LIM = `volume=${G}dB,aresample=192000,alimiter=limit=-1.3dB:attack=5:release=80:asc=1:asc_level=0.5:level=false:latency=true,aresample=48000`;
ff(['-i', f(`${score}.premaster.wav`), '-af', `${PRE},volume=${G}dB`, '-c:a', 'pcm_f32le', '-ar', '48000', f(`${score}.prelim.wav`)]);
const p1 = ff(['-i', f(`${score}.premaster.wav`), '-af', `${PRE},${LIM},loudnorm=I=-14:TP=-1.0:LRA=20:print_format=json`, '-f', 'null', '-']);
const lastJson = t => JSON.parse(t.match(/\{[^{}]*\}/g).pop());
const m1 = lastJson(p1);
const p2 = ff(['-i', f(`${score}.premaster.wav`), '-af', `${PRE},${LIM},loudnorm=I=-14:TP=-1.0:LRA=20:measured_I=${m1.input_i}:measured_TP=${m1.input_tp}:measured_LRA=${m1.input_lra}:measured_thresh=${m1.input_thresh}:offset=${m1.target_offset}:linear=true:print_format=json`, '-ar', '48000', '-c:a', 'pcm_s24le', f(`${score}.master.wav`)]);
const m2 = lastJson(p2);
if (m2.normalization_type !== 'linear') throw new Error('loudnorm went dynamic: the master would be flattened. Lower the level into the limiter and run again.');
const out = loud(ff(['-i', f(`${score}.master.wav`), '-af', 'ebur128=peak=true:framelog=quiet', '-f', 'null', '-']));
// a small copy to listen to in a browser (the film itself is muxed from the WAV at 384k)
ff(['-i', f(`${score}.master.wav`), '-c:a', 'aac', '-b:a', '192k', '-ar', '48000', '-ac', '2', '-movflags', '+faststart', f(`${score}.master.m4a`)]);
const m4a = loud(ff(['-i', f(`${score}.master.m4a`), '-af', 'ebur128=peak=true:framelog=quiet', '-f', 'null', '-']));
const info = { score, tone: tn, drumsBus: td, musicBus: tm, premasterIntegrated: I0, gainIntoLimiter: G, loudnormIn: { I: +m1.input_i, TP: +m1.input_tp, LRA: +m1.input_lra }, normalization: m2.normalization_type, master: out, m4a };
fs.writeFileSync(f(`${score}.master.json`), JSON.stringify(info, null, 1));
fs.rmSync(TMP, { recursive: true, force: true });
console.log(JSON.stringify(info));
