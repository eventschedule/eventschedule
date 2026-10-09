#!/usr/bin/env node
// Renders the score offline in headless Chrome (score.html, an OfflineAudioContext) and writes
// 48 kHz stereo float WAVs. No npm dependencies: Chrome is driven over the DevTools protocol
// with Node's built-in WebSocket. One Chrome, closed by PID when done.
//
//   node render-audio.mjs --test                      the engine's own tests (kick onset, reverbs, one-shots, two identical renders)
//   node render-audio.mjs --score=dawn --all          sfx.wav (once; --sfx re-renders it), dawn.{drums,bass,music,mix}.wav, dawn.events.json
//   node render-audio.mjs --score=dawn --parts        every instrument alone, and the wet alone, into out/audio/parts/ (for balancing)
//   node render-audio.mjs --score=dawn --solo=music --out=x.wav
//   node render-audio.mjs --sfx                       the effects stem alone
//   node render-audio.mjs --player-test               loads js/audio/player.js and checks its clock runs
//
// The samples leave the page one second at a time (base64 of interleaved float32) and are
// checked against the page's own checksum. Outputs go to out/audio/ (ignored by git).
import { spawn } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import crypto from 'node:crypto';
import { fileURLToPath, pathToFileURL } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const ARG = Object.fromEntries(process.argv.slice(2).map(a => { const [k, v] = a.replace(/^--/, '').split('='); return [k, v ?? true]; }));
const CHROME = process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const SCRATCH = process.env.SCRATCH || '/private/tmp/claude-501/-Users-hillel-Code-eventschedule/946aee1e-abfe-42fe-87f6-32e6abf160a9/scratchpad';
const OUT = path.join(here, 'out/audio');
const SR = 48000, DUR = 58;
const sleep = ms => new Promise(r => setTimeout(r, ms));

class CDP {
  constructor(url) { this.ws = new WebSocket(url); this.id = 0; this.pending = new Map(); this.ws.onmessage = e => { const m = JSON.parse(e.data); if (m.id && this.pending.has(m.id)) { const p = this.pending.get(m.id); this.pending.delete(m.id); clearTimeout(p.to); m.error ? p.rej(new Error(`${p.method}: ${JSON.stringify(m.error)}`)) : p.res(m.result); } else if (m.method === 'Runtime.exceptionThrown') console.error('[page]', m.params.exceptionDetails?.exception?.description || m.params.exceptionDetails?.text); else if (m.method === 'Runtime.consoleAPICalled' && ['error', 'warning'].includes(m.params.type)) console.error('[console]', m.params.args.map(a => a.value ?? a.description).join(' ')); }; }
  open() { return new Promise((res, rej) => { this.ws.onopen = res; this.ws.onerror = rej; }); }
  send(method, params = {}, timeout = 60000) { const id = ++this.id; return new Promise((res, rej) => { const to = setTimeout(() => { this.pending.delete(id); rej(new Error(`CDP timeout: ${method}`)); }, timeout); this.pending.set(id, { method, res, rej, to }); this.ws.send(JSON.stringify({ id, method, params })); }); }
  // evaluate and return the value; a throwing expression is an error here, never a silent undefined
  async eval(expression, awaitPromise = false, timeout = 60000) { const r = await this.send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise }, timeout); if (r.exceptionDetails) throw new Error('page threw: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text)); return r.result.value; }
}

async function launch() {
  const base = fs.existsSync(SCRATCH) ? SCRATCH : os.tmpdir(), dir = fs.mkdtempSync(path.join(base, 'doors-audio-chrome-'));
  const proc = spawn(CHROME, ['--headless=new', '--remote-debugging-port=0', `--user-data-dir=${dir}`, '--no-first-run', '--no-default-browser-check', '--mute-audio', '--autoplay-policy=no-user-gesture-required', '--disable-background-timer-throttling', '--disable-renderer-backgrounding', '--allow-file-access-from-files', 'about:blank'], { stdio: 'ignore' });
  let port = null;
  for (let k = 0; k < 150 && !port; k++) { try { port = +fs.readFileSync(path.join(dir, 'DevToolsActivePort'), 'utf8').split('\n')[0]; } catch { await sleep(100); } }
  if (!port) { proc.kill('SIGKILL'); throw new Error('Chrome did not open a DevTools port'); }
  let targets; for (let k = 0; k < 100; k++) { try { targets = await (await fetch(`http://127.0.0.1:${port}/json`)).json(); if (targets.some(t => t.type === 'page')) break; } catch { /* not up yet */ } await sleep(100); }
  const cdp = new CDP(targets.find(t => t.type === 'page').webSocketDebuggerUrl); await cdp.open(); await cdp.send('Runtime.enable'); await cdp.send('Page.enable');
  await cdp.send('Page.navigate', { url: pathToFileURL(path.join(here, 'score.html')).href });
  for (let k = 0; ; k++) { const ok = await cdp.eval('window.__ready === true && !!(window.A && A.Score)').catch(() => false); if (ok) break; if (k > 200) throw new Error('score.html never became ready'); await sleep(50); }
  const close = async () => { try { cdp.ws.close(); } catch { /* gone */ } proc.kill('SIGTERM'); await sleep(300); try { process.kill(proc.pid, 0); proc.kill('SIGKILL'); } catch { /* already exited */ } try { fs.rmSync(dir, { recursive: true, force: true, maxRetries: 10, retryDelay: 100 }); } catch { /* still flushing */ } };
  return { cdp, close, pid: proc.pid };
}

function writeWav(file, pcm) {   // pcm: Buffer of interleaved float32 LE, stereo, 48 kHz
  const h = Buffer.alloc(44); h.write('RIFF', 0); h.writeUInt32LE(36 + pcm.length, 4); h.write('WAVE', 8); h.write('fmt ', 12); h.writeUInt32LE(16, 16); h.writeUInt16LE(3, 20); h.writeUInt16LE(2, 22); h.writeUInt32LE(SR, 24); h.writeUInt32LE(SR * 8, 28); h.writeUInt16LE(8, 32); h.writeUInt16LE(32, 34); h.write('data', 36); h.writeUInt32LE(pcm.length, 40);
  fs.mkdirSync(path.dirname(file), { recursive: true }); fs.writeFileSync(file, Buffer.concat([h, pcm]));
}
const fnv = buf => { let h = 0x811c9dc5; for (let i = 0; i < buf.length; i++) { h ^= buf[i]; h = Math.imul(h, 0x01000193); } return h >>> 0; };

async function job(cdp, opts, file) {
  await cdp.eval(`A.Score.job(${JSON.stringify(opts)})`);
  let info; const t0 = Date.now();
  for (;;) { info = JSON.parse(await cdp.eval('JSON.stringify(window.__audio)')); if (info.state === 'done') break; if (info.state === 'error') throw new Error('render failed: ' + info.error); if (Date.now() - t0 > 300000) throw new Error('render took more than five minutes'); await sleep(200); }
  if (info.frames !== Math.round(DUR * SR)) throw new Error(`expected ${DUR * SR} frames, got ${info.frames}`);
  const parts = []; for (let i = 0; i * SR * 8 < info.bytes; i++) parts.push(Buffer.from(await cdp.eval(`A.Score.chunk(${i})`), 'base64'));
  const pcm = Buffer.concat(parts);
  if (pcm.length !== info.bytes || fnv(pcm) !== info.checksum) throw new Error('the samples that arrived are not the samples that were rendered');
  if (file) writeWav(file, pcm);
  const sha = crypto.createHash('sha256').update(pcm).digest('hex');
  console.log(`${file ? path.relative(here, file) : '(not saved)'}  peak ${(20 * Math.log10(info.peak || 1e-9)).toFixed(2)} dBFS  render ${(info.ms / 1000).toFixed(1)} s  ${info.nodes} voices${info.unknown.length ? '  UNKNOWN CUES: ' + info.unknown.join(',') : ''}`);
  return { ...info, sha, pcm, peakDb: 20 * Math.log10(info.peak || 1e-9) };
}

const { cdp, close, pid } = await launch();
try {
  fs.mkdirSync(OUT, { recursive: true });
  const score = ARG.score && ARG.score !== true ? ARG.score : null;
  const cues = await cdp.eval('({n: A.Score.cuesOf().length, hash: A.Score.cueHash(A.Score.cuesOf()), from: window.CUES ? "js/timeline.js" : "js/audio/cues-draft.js"})');
  console.log(`chrome pid ${pid}; ${cues.n} cues from ${cues.from} (${cues.hash})`);
  if (ARG.test) {
    const t = await cdp.eval('A.Score.selfTest()', true, 120000);
    const a = await job(cdp, { score: null, solo: 'sfx' }, null), b = await job(cdp, { score: null, solo: 'sfx' }, null);
    // The hall's FFT convolution rounds differently from run to run (about 1e-7), so two renders are
    // compared by their largest difference, not by their bytes. One sfx.wav is rendered and reused.
    const fa = new Float32Array(a.pcm.buffer, a.pcm.byteOffset, a.pcm.length / 4), fb = new Float32Array(b.pcm.buffer, b.pcm.byteOffset, b.pcm.length / 4); let md = 0; for (let i = 0; i < fa.length; i++) md = Math.max(md, Math.abs(fa[i] - fb[i]));
    t.determinism = { bitIdentical: a.sha === b.sha, maxDifference: md, maxDifferenceDb: md ? +(20 * Math.log10(md)).toFixed(1) : null, pass: md < 1e-6, renderSeconds: a.ms / 1000 };
    fs.writeFileSync(path.join(OUT, 'stage0.json'), JSON.stringify(t, null, 1));
    console.log(JSON.stringify({ kick: t.kick, ir: t.ir, determinism: t.determinism }, null, 1));
  } else if (ARG['player-test']) {
    // the preview player, without ears: load a score, play from 10 s, and watch the audio clock move
    const r = await cdp.eval(`(async () => { await A.Player.load(${JSON.stringify(score || 'dawn')}); A.Player.play(10); for (let k = 0; k < 100 && !A.Player.started; k++) await new Promise(r => setTimeout(r, 100)); await new Promise(r => setTimeout(r, 700)); const t1 = A.Player.time(); await new Promise(r => setTimeout(r, 500)); const t2 = A.Player.time(); A.Player.stop(); const held = A.Player.time(); A.Player.play(20, [20, 22]); for (let k = 0; k < 100 && !A.Player.started; k++) await new Promise(r => setTimeout(r, 100)); await new Promise(r => setTimeout(r, 300)); const t3 = A.Player.time(); A.Player.stop(); return { state: A.Player.ctx.state, sampleRate: A.Player.ctx.sampleRate, seconds: A.Player.buf.duration, t1, t2, advanced: t2 - t1, held, loopT: t3, clock: A.Player.ctx.currentTime, outputLatency: A.Player.ctx.outputLatency, baseLatency: A.Player.ctx.baseLatency }; })()`, true, 120000);
    console.log(JSON.stringify(r));
  } else if (ARG.parts) {
    if (!score) throw new Error('--parts needs --score');
    const ev = await cdp.eval(`(() => { A.Score.events({score: ${JSON.stringify(score)}, noSfx: true}); return Object.keys(A.Score.build(new OfflineAudioContext(2, 128, 48000), {score: ${JSON.stringify(score)}, noSfx: true}).instInfo); })()`);
    const dir = path.join(OUT, 'parts');
    for (const part of ev) await job(cdp, { score, part, noSfx: true }, path.join(dir, `${score}.${part}.wav`));
    await job(cdp, { score, wetOnly: true, noSfx: true }, path.join(dir, `${score}.wet.wav`));
    await job(cdp, { score, noSfx: true }, path.join(dir, `${score}.nosfx.wav`));
  } else if (score && ARG.all) {
    const sfx = path.join(OUT, 'sfx.wav'), meta = path.join(OUT, 'sfx.json'), old = fs.existsSync(meta) ? JSON.parse(fs.readFileSync(meta, 'utf8')) : null;
    if (ARG.sfx || !fs.existsSync(sfx) || !old || old.cueHash !== cues.hash) { const r = await job(cdp, { score: null, solo: 'sfx' }, sfx); fs.writeFileSync(meta, JSON.stringify({ cueHash: cues.hash, cues: cues.n, from: cues.from, sha: r.sha, peakDb: r.peakDb }, null, 1)); }
    else console.log('out/audio/sfx.wav kept (cues unchanged)');
    const res = {}; for (const solo of ['drums', 'bass', 'music']) res[solo] = await job(cdp, { score, solo }, path.join(OUT, `${score}.${solo}.wav`));
    res.mix = await job(cdp, { score }, path.join(OUT, `${score}.mix.wav`));
    const ev = await cdp.eval(`A.Score.events({score: ${JSON.stringify(score)}})`);
    fs.writeFileSync(path.join(OUT, `${score}.events.json`), JSON.stringify({ ...ev, renders: Object.fromEntries(Object.entries(res).map(([k, v]) => [k, { sha: v.sha, peakDb: +v.peakDb.toFixed(2), seconds: v.ms / 1000 }])) }));
  } else if (ARG.sfx && !score) {
    const r = await job(cdp, { score: null, solo: 'sfx' }, path.join(OUT, 'sfx.wav'));
    fs.writeFileSync(path.join(OUT, 'sfx.json'), JSON.stringify({ cueHash: cues.hash, cues: cues.n, from: cues.from, sha: r.sha, peakDb: r.peakDb }, null, 1));
  } else if (score) {
    const opts = { score, ...(ARG.solo ? { solo: ARG.solo } : {}), ...(ARG.part ? { part: ARG.part } : {}), ...(ARG.wet ? { wetOnly: true } : {}), ...(ARG.nosfx ? { noSfx: true } : {}) };
    await job(cdp, opts, path.resolve(ARG.out || path.join(OUT, `${score}.${ARG.solo || ARG.part || 'mix'}.wav`)));
  } else console.log('nothing to do: see the header of this file');
} finally { await close(); }
