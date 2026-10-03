#!/usr/bin/env node
// Frame-accurate renderer for showreel.html. No npm dependencies: Chrome is driven over the
// DevTools protocol with Node's built-in WebSocket, and frames are encoded by the ffmpeg that
// Playwright ships (mjpeg in, VP8 WebM out). See README.md.
//
//   node render.mjs --preview --out=preview.webm       960x540, 30fps, no motion blur
//   node render.mjs --final --out=showreel.webm        1920x1080, 60fps, 8 to 16-sample motion blur
//   node render.mjs --mobile --mp4=showreel-m.mp4      960x540, 30fps, motion blur, H.264 only
//   node render.mjs --stills=1,6.8,14.5 --out=dir      PNG stills at those times
//
// Options: --theme=dark|light (default dark), --from=S --to=S (seconds), --workers=N, --scale=F,
//          --fps=N, --samples=N, --keep, --port-base=N (default 9400; give two parallel renders
//          different bases), --mp4=FILE (also write H.264; needs a full ffmpeg on PATH or
//          FFMPEG_FULL), --poster=FILE with --poster-at=S (the frame at S seconds; default the last)

import { spawn } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const A = Object.fromEntries(process.argv.slice(2).map(a => { const [k, v] = a.replace(/^--/, '').split('='); return [k, v ?? true]; }));
const CHROME = process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const FFMPEG = process.env.FFMPEG || path.join(os.homedir(), 'Library/Caches/ms-playwright/ffmpeg-1011/ffmpeg-mac');
const DUR = +fs.readFileSync(path.join(here, 'showreel.html'), 'utf8').match(/\bDUR = (\d+(?:\.\d+)?)/)[1];
// The presets are exclusive: --final would win the preset while --mobile still dropped the WebM,
// leaving a stale WebM beside a new MP4, i.e. two different films behind one <video>.
if (A.final && A.mobile) throw new Error('--final and --mobile are separate renders: pass one');
// --mobile is the cut phones get (the homepage serves it under 768px): a quarter of the pixels and
// half the frames of --final, so about a quarter of the bytes, still with real motion blur, which
// matters more at 30fps than at 60.
const preset = A.final ? { scale: 1, fps: 60, samples: 8, fast: 16, bitrate: '3800k' }
  : A.mobile ? { scale: .5, fps: 30, samples: 8, fast: 16, bitrate: '900k' }
  : { scale: .5, fps: 30, samples: 1, fast: 1, bitrate: '2000k' };
const scale = +(A.scale ?? preset.scale), fps = +(A.fps ?? preset.fps), samples = +(A.samples ?? preset.samples), fastSamples = +(A['fast-samples'] ?? Math.max(samples, preset.fast));
const shutter = .5; // 180 degree shutter
const workers = +(A.workers ?? Math.max(1, Math.min(6, os.cpus().length - 4)));
const theme = A.theme === 'light' ? 'light' : 'dark';
const portBase = +(A['port-base'] ?? 9400);
// Phones all decode H.264, so the mobile cut skips the VP8 WebM fallback.
const webm = !A.mobile;
if (!webm && !A.mp4 && !A.stills) throw new Error('--mobile writes H.264 only: pass --mp4=FILE');
const out = path.resolve(A.out || (A.stills ? 'stills' : 'showreel.webm'));
const page = pathToFileURL(path.join(here, 'showreel.html')).href + `?render=1&scale=${scale}&theme=${theme}`;
const sleep = ms => new Promise(r => setTimeout(r, ms));

class CDP {
  constructor(url) {
    this.ws = new WebSocket(url); this.id = 0; this.pending = new Map();
    this.ws.onmessage = e => {
      const m = JSON.parse(e.data);
      if (m.id && this.pending.has(m.id)) { const p = this.pending.get(m.id); this.pending.delete(m.id); m.error ? p.rej(new Error(`${p.method}: ${JSON.stringify(m.error)}`)) : p.res(m.result); return; }
      if (m.method === 'Runtime.exceptionThrown') console.error('[page]', m.params.exceptionDetails?.exception?.description || m.params.exceptionDetails?.text);
      if (m.method === 'Runtime.consoleAPICalled' && m.params.type === 'error') console.error('[console]', m.params.args.map(a => a.value ?? a.description).join(' '));
    };
  }
  open() { return new Promise((res, rej) => { this.ws.onopen = res; this.ws.onerror = rej; }); }
  send(method, params = {}, timeout = 60000) {
    const id = ++this.id;
    return new Promise((res, rej) => {
      const to = setTimeout(() => { this.pending.delete(id); rej(new Error(`CDP timeout: ${method}`)); }, timeout);
      this.pending.set(id, { method, res: v => { clearTimeout(to); res(v); }, rej: e => { clearTimeout(to); rej(e); } });
      this.ws.send(JSON.stringify({ id, method, params }));
    });
  }
}

async function launch(i) {
  const port = portBase + i, dir = fs.mkdtempSync(path.join(os.tmpdir(), 'showreel-chrome-'));
  const proc = spawn(CHROME, ['--headless=new', `--remote-debugging-port=${port}`, `--user-data-dir=${dir}`, '--hide-scrollbars', '--force-device-scale-factor=1',
    '--no-first-run', '--no-default-browser-check', '--disable-background-timer-throttling', '--disable-renderer-backgrounding', '--mute-audio', 'about:blank'], { stdio: 'ignore' });
  let targets;
  for (let k = 0; k < 100; k++) { try { targets = await (await fetch(`http://127.0.0.1:${port}/json`)).json(); if (targets.some(t => t.type === 'page')) break; } catch {} await sleep(100); }
  const cdp = new CDP(targets.find(t => t.type === 'page').webSocketDebuggerUrl);
  await cdp.open();
  await cdp.send('Runtime.enable'); await cdp.send('Page.enable');
  await cdp.send('Emulation.setDeviceMetricsOverride', { width: Math.round(1920 * scale), height: Math.round(1080 * scale), deviceScaleFactor: 1, mobile: false });
  await cdp.send('Page.navigate', { url: page });
  for (let k = 0; ; k++) {
    const r = await cdp.send('Runtime.evaluate', { expression: 'window.__ready === true', returnByValue: true }).catch(() => null);
    if (r?.result?.value) break;
    if (k > 300) throw new Error('page never became ready');
    await sleep(50);
  }
  const shown = (await cdp.send('Runtime.evaluate', { expression: 'window.THEME', returnByValue: true })).result.value;
  if (shown !== theme) throw new Error(`asked for the ${theme} cut but the page rendered ${shown}`);
  // A SIGKILLed Chrome can still be flushing its profile while we delete it (ENOTEMPTY), and a
  // failed cleanup must not abort a render whose frames are already captured.
  return { cdp, close: () => { proc.kill('SIGKILL'); try { fs.rmSync(dir, { recursive: true, force: true, maxRetries: 10, retryDelay: 100 }); } catch {} } };
}

async function grab(cdp, t, format = 'jpeg') {
  await cdp.send('Runtime.evaluate', { expression: `seek(${t});new Promise(r=>requestAnimationFrame(()=>requestAnimationFrame(r)))`, awaitPromise: true });
  const { data } = await cdp.send('Page.captureScreenshot', format === 'png' ? { format: 'png' } : { format: 'jpeg', quality: 95, optimizeForSpeed: true });
  return Buffer.from(data, 'base64');
}

// The page lists its fastest windows in window.FAST; frames inside them get fastSamples.
let FAST = [];
function frameTimes(f) {
  const n = FAST.some(([a, b]) => f / fps >= a && f / fps <= b) ? fastSamples : samples, ts = [];
  for (let s = 0; s < n; s++) ts.push(Math.min(DUR - 1e-4, Math.max(0, (f + (n > 1 ? ((s + .5) / n - .5) * shutter : 0)) / fps)));
  return ts;
}

async function stills() {
  fs.mkdirSync(out, { recursive: true });
  const { cdp, close } = await launch(0);
  try {
    for (const t of String(A.stills).split(',').map(Number)) {
      const file = path.join(out, `t${t.toFixed(2).padStart(5, '0')}.png`);
      fs.writeFileSync(file, await grab(cdp, t, 'png'));
      console.log(file);
    }
  } finally { close(); }
}

async function video() {
  const from = +(A.from ?? 0), to = +(A.to ?? DUR);
  const f0 = Math.round(from * fps), f1 = Math.round(to * fps);
  const tmp = fs.mkdtempSync(path.join(os.tmpdir(), 'showreel-frames-'));
  console.log(`${f1 - f0} frames at ${Math.round(1920 * scale)}x${Math.round(1080 * scale)} ${fps}fps, ${samples} sample(s) (${fastSamples} in fast windows), ${workers} worker(s) -> ${tmp}`);
  const started = Date.now();
  let done = 0;
  const chunk = Math.ceil((f1 - f0) / workers);
  await Promise.all(Array.from({ length: workers }, async (_, w) => {
    const a = f0 + w * chunk, b = Math.min(f1, a + chunk);
    if (a >= b) return;
    const { cdp, close } = await launch(w);
    FAST = (await cdp.send('Runtime.evaluate', { expression: 'window.FAST || []', returnByValue: true })).result.value;
    const blend = fastSamples > 1 ? spawn('python3', [path.join(here, 'blend.py'), tmp], { stdio: ['pipe', 'ignore', 'inherit'] }) : null;
    try {
      for (let f = a; f < b; f++) {
        const shots = [];
        for (const t of frameTimes(f)) shots.push(await grab(cdp, t));
        if (!blend) fs.writeFileSync(path.join(tmp, `f${String(f).padStart(5, '0')}.jpg`), shots[0]);
        else {
          const hdr = Buffer.alloc(8); hdr.writeUInt32LE(f, 0); hdr.writeUInt32LE(shots.length, 4);
          const parts = [hdr]; for (const s of shots) { const l = Buffer.alloc(4); l.writeUInt32LE(s.length, 0); parts.push(l, s); }
          if (!blend.stdin.write(Buffer.concat(parts))) await new Promise(r => blend.stdin.once('drain', r));
        }
        if (++done % 60 === 0) { const el = (Date.now() - started) / 1000; process.stdout.write(`  ${done}/${f1 - f0} frames, ${el.toFixed(0)}s elapsed, ~${(el / done * (f1 - f0 - done)).toFixed(0)}s left\n`); }
      }
    } finally {
      close();
      if (blend) { blend.stdin.end(); await new Promise(r => blend.on('close', r)); }
    }
  }));
  console.log(`captured in ${((Date.now() - started) / 1000).toFixed(0)}s, encoding...`);

  const files = fs.readdirSync(tmp).filter(f => f.endsWith('.jpg')).sort();
  if (files.length !== f1 - f0) throw new Error(`expected ${f1 - f0} frames, found ${files.length}`);
  const log = path.join(tmp, 'pass');
  const input = ['-y', '-hide_banner', '-loglevel', 'error', '-f', 'image2pipe', '-framerate', String(fps), '-c:v', 'mjpeg', '-i', 'pipe:0'];
  const pipeFrames = (bin, args, label) => new Promise((res, rej) => {
    const p = spawn(bin, [...input, ...args], { stdio: ['pipe', 'inherit', 'inherit'] });
    p.on('error', rej);
    p.on('close', c => c ? rej(new Error(`${label} exited ${c}`)) : res());
    (async () => { for (const f of files) { if (!p.stdin.write(fs.readFileSync(path.join(tmp, f)))) await new Promise(r => p.stdin.once('drain', r)); } p.stdin.end(); })();
  });
  const vp8 = pass => ['-c:v', 'libvpx', '-b:v', A.bitrate || preset.bitrate, '-maxrate', '9000k', '-bufsize', '9000k', '-qmin', '2', '-qmax', '42', '-auto-alt-ref', '1', '-lag-in-frames', '16',
    '-deadline', 'good', '-cpu-used', pass === 1 ? '4' : '1', '-pix_fmt', 'yuv420p', '-pass', String(pass), '-passlogfile', log,
    ...(pass === 1 ? ['-f', 'webm', '/dev/null'] : [out])];
  if (webm) { await pipeFrames(FFMPEG, vp8(1), 'webm pass 1'); await pipeFrames(FFMPEG, vp8(2), 'webm pass 2'); }

  // Safari and iOS play VP8 unreliably, so --mp4 also writes H.264. Playwright's ffmpeg has no
  // x264, so this needs a full build (brew install ffmpeg); FFMPEG_FULL overrides the PATH lookup.
  if (A.mp4) {
    const mp4 = path.resolve(A.mp4), full = process.env.FFMPEG_FULL || 'ffmpeg', xlog = path.join(tmp, 'x264');
    // The frames are JPEGs (full-range BT.601). Left alone, x264 keeps them as yuvj420p, which some
    // hardware decoders render with crushed contrast, and untagged HD is assumed to be BT.709, which
    // shifts the brand blues. Convert to limited-range BT.709 and say so in the stream.
    const x264 = pass => ['-vf', 'scale=in_range=pc:out_range=tv:in_color_matrix=bt601:out_color_matrix=bt709,format=yuv420p',
      '-color_range', 'tv', '-colorspace', 'bt709', '-color_primaries', 'bt709', '-color_trc', 'bt709',
      '-c:v', 'libx264', '-preset', 'slow', '-profile:v', 'high', '-b:v', A.bitrate || preset.bitrate, '-maxrate', '6M', '-bufsize', '8M',
      '-pass', String(pass), '-passlogfile', xlog, ...(pass === 1 ? ['-an', '-f', 'mp4', '/dev/null'] : ['-movflags', '+faststart', mp4])];
    await pipeFrames(full, x264(1), 'mp4 pass 1'); await pipeFrames(full, x264(2), 'mp4 pass 2');
    console.log(`${mp4} (${(fs.statSync(mp4).size / 1048576).toFixed(1)} MB)`);
  }
  // The poster is what a visitor sees before the reel plays, and all they ever see under reduced
  // motion, Save-Data or iOS Low Power Mode, so it is a chosen frame (--poster-at), not just the
  // last one. Re-encoded at quality 82: it is a 1080p frame that phones load too.
  if (A.poster) {
    const at = A['poster-at'] != null ? Math.round(+A['poster-at'] * fps) - f0 : files.length - 1;
    if (at < 0 || at >= files.length) throw new Error(`--poster-at=${A['poster-at']} is outside the rendered range`);
    await new Promise((res, rej) => {
      const p = spawn('python3', ['-c', 'import sys; from PIL import Image; Image.open(sys.argv[1]).save(sys.argv[2], quality=82, optimize=True, progressive=True)', path.join(tmp, files[at]), path.resolve(A.poster)], { stdio: 'inherit' });
      p.on('close', c => c ? rej(new Error(`poster encode exited ${c}`)) : res());
    });
    console.log(`${path.resolve(A.poster)} (frame at ${((at + f0) / fps).toFixed(2)}s)`);
  }
  if (A.keep) console.log(`frames kept in ${tmp}`); else fs.rmSync(tmp, { recursive: true, force: true });
  if (webm) console.log(`${out} (${(fs.statSync(out).size / 1048576).toFixed(1)} MB) in ${((Date.now() - started) / 1000).toFixed(0)}s`);
}

(A.stills ? stills() : video()).catch(e => { console.error(e); process.exit(1); });
