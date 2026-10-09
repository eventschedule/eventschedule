// One entry point for the preview and for the offline render.
//   A.Score.render({score, cues, solo, part, wetOnly, window}) -> Promise<AudioBuffer>   (48 kHz stereo, A.DUR long)
//   A.Score.events({score, cues})                              -> the onsets a render should contain
//   A.Score.job(opts) / A.Score.chunk(i)                       -> how render-audio.mjs takes the samples out
// score: 'dawn' | 'bloom' | 'pulse' | null (effects only). solo: 'drums' | 'bass' | 'music' | 'sfx'.
// Cues come from opts.cues, else the picture's own timeline (window.CUES), else the hand-written draft.
'use strict';
(() => {
const A = window.A, E = A.E, SR = A.SR;
const cuesOf = o => (o && o.cues) || window.CUES || A.CUES_DRAFT || [];
const cueHash = cues => E.hash32(JSON.stringify(cues)).toString(16);

async function prepare() { if (!A.ONE) A.ONE = await E.renderOneShots(); if (!A.IR) A.IR = E.IRS(); }

function build(ctx, opts = {}) {
  const S = E.state(ctx, opts); E.graph(S);
  const sc = opts.score ? A.SCORES[opts.score] : null;
  if (opts.score && !sc) throw new Error('no such score: ' + opts.score);
  if (sc && opts.solo !== 'sfx') sc.arrange(S);
  if (!opts.noSfx) A.SFX.arrange(S, cuesOf(opts));
  E.applyCurves(S, sc);
  return S;
}

// The graph is built two seconds at a time, a quarter second ahead of the render, and finished
// voices are disconnected, so a 58 s render never carries more than a few hundred live nodes.
async function render(opts = {}) {
  await prepare();
  const ctx = new OfflineAudioContext(2, Math.round(A.DUR * SR), SR), S = build(ctx, opts), W = 2;
  S.ev.sort((a, b) => a.t - b.t);
  let i = 0; const flush = until => { while (i < S.ev.length && S.ev[i].t < until) S.ev[i++].fn(); };
  flush(W + .25);
  for (let k = 1; k * W < A.DUR; k++) { const T = k * W; ctx.suspend(T).then(() => { S.sweep(T); flush(T + W + .25); ctx.resume(); }); }
  const buf = await ctx.startRendering();
  render.last = S;
  return buf;
}

function events(opts = {}) { const ctx = new OfflineAudioContext(2, 128, SR); A.IR = A.IR || E.IRS(); const S = build(ctx, opts); return { marks: S.marks.sort((a, b) => a.t - b.t), kicks: S.kicks, unknown: S.sfxUnknown || [], cueHash: cueHash(cuesOf(opts)), cues: cuesOf(opts).length }; }

// --- for render-audio.mjs: start, poll window.__audio, then pull one second at a time ---
function job(opts = {}) {
  const t0 = performance.now(); window.__audio = { state: 'rendering' };
  render(opts).then(buf => {
    const n = buf.length, L = buf.getChannelData(0), R = buf.getChannelData(1), out = new Float32Array(n * 2); let peak = 0;
    for (let i = 0; i < n; i++) { out[2 * i] = L[i]; out[2 * i + 1] = R[i]; const a = Math.max(Math.abs(L[i]), Math.abs(R[i])); if (a > peak) peak = a; }
    const u = A._pcm = new Uint8Array(out.buffer); let h = 0x811c9dc5; for (let i = 0; i < u.length; i++) { h ^= u[i]; h = Math.imul(h, 0x01000193); }
    const S = render.last;
    window.__audio = { state: 'done', frames: n, bytes: u.length, checksum: h >>> 0, peak, ms: Math.round(performance.now() - t0), nodes: S.ev.length, kicks: S.kicks.length, unknown: S.sfxUnknown || [], cueHash: cueHash(cuesOf(opts)) };
    A._marks = S.marks.sort((a, b) => a.t - b.t);
  }).catch(e => { window.__audio = { state: 'error', error: String((e && e.stack) || e) }; });
  return 0;
}
const CHUNK = SR * 2 * 4;   // one second of interleaved stereo float32
function chunk(i) { const u = A._pcm.subarray(i * CHUNK, Math.min(A._pcm.length, (i + 1) * CHUNK)); let s = ''; for (let k = 0; k < u.length; k += 32768) s += String.fromCharCode.apply(null, u.subarray(k, k + 32768)); return btoa(s); }

/* ---------- Stage 0: the engine, before any music ---------- */
async function selfTest() {
  await prepare();
  const out = { oneShots: A.ONE_INFO, ir: {} };
  // a kick alone at exactly 1.000 s: its first audible sample must be at sample 48000
  { const ctx = new OfflineAudioContext(2, 2 * SR, SR), S = E.state(ctx); E.graph(S); S.inst('kick', 'drums', { level: -8 }); S.at = (t, f) => f(); E.V.shot(S, S.bus.kick, 'kickP', 1, 1); const b = await ctx.startRendering(), d = b.getChannelData(0); let peak = 0, first = -1; for (let i = 0; i < d.length; i++) peak = Math.max(peak, Math.abs(d[i])); for (let i = 0; i < d.length; i++) if (Math.abs(d[i]) > .01 * peak) { first = i; break; } let pre = 0; for (let i = 0; i < SR; i++) pre = Math.max(pre, Math.abs(d[i])); out.kick = { first, peakDb: +(20 * Math.log10(peak)).toFixed(2), before: pre }; }
  for (const k of ['hall', 'plate']) { const b = A.IR[k], L = b.getChannelData(0), R = b.getChannelData(1); let eL = 0, eR = 0, x = 0, mL = 0, mR = 0; for (let i = 0; i < b.length; i++) { eL += L[i] * L[i]; eR += R[i] * R[i]; x += L[i] * R[i]; mL += L[i]; mR += R[i]; } out.ir[k] = { energyL: +eL.toFixed(5), energyR: +eR.toFixed(5), corr: +(x / Math.sqrt(eL * eR)).toFixed(4), lastL: L[b.length - 1], lastR: R[b.length - 1], meanL: mL / b.length, meanR: mR / b.length, seconds: b.length / SR }; }
  return out;
}

A.Score = { prepare, build, render, events, job, chunk, selfTest, cuesOf, cueHash };
})();
