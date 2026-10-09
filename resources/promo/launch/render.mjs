#!/usr/bin/env node
// Frame-accurate renderer for launch.html. No npm dependencies: Chrome is driven over the DevTools
// protocol with Node's built-in WebSocket. Started from the showreel's renderer; what changed is
// that this one can stop and carry on: frames are kept, a frame that exists is not taken again.
//
//   node render.mjs --stills=0.5,32.4,46 --out=out/stills            PNG stills (half size; --scale=1 for full)
//   node render.mjs --preset=animatic --mp4=out/animatic.mp4         960x540, 30 fps, no blur
//   node render.mjs --preset=review   --mp4=out/review.mp4           1920x1080, 30 fps, no blur
//   node render.mjs --preset=master                                  2560x1440, 60 fps, 8 to 16-sample blur (frames only: encode.sh grades and encodes)
//
// Options: --from=S --to=S, --shots=s24,s25, --workers=N (default 3), --scale, --fps, --samples,
//          --frames=DIR (where frames are kept; default ~/.claude/plans/product-hunt-video/render/<preset>),
//          --fresh (forget kept frames), --audio=FILE with --mp4 (mux a score), --grade (grade the mp4), --crf=N
//
// A frame's motion-blur samples stay on its own side of a hard cut (window.CUTS), and the page's
// fastest windows (window.FAST) get twice the samples.

import { spawn, spawnSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const A = Object.fromEntries(process.argv.slice(2).map(a => { const i = a.indexOf('='); return i < 0 ? [a.replace(/^--/, ''), true] : [a.slice(2, i), a.slice(i + 1)]; }));
const CHROME = process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const FFMPEG = process.env.FFMPEG || '/opt/homebrew/bin/ffmpeg';
const PRESETS = {
  animatic: { scale: .5, fps: 30, samples: 1, fast: 1 },
  review: { scale: 1, fps: 30, samples: 1, fast: 1 },
  master: { scale: 4 / 3, fps: 60, samples: 8, fast: 16 },
};
const presetName = A.preset || 'animatic', preset = PRESETS[presetName];
if (!preset) throw new Error(`--preset must be one of ${Object.keys(PRESETS).join(', ')}`);
const scale = +(A.scale ?? (A.stills ? .5 : preset.scale)), fps = +(A.fps ?? preset.fps), samples = +(A.samples ?? preset.samples), fastSamples = +(A['fast-samples'] ?? Math.max(samples, preset.fast));
const W = Math.round(1920 * scale), H = Math.round(1080 * scale);
const shutter = .5;                                   // a 180 degree shutter
const workers = +(A.workers ?? 3);                    // other sessions share this machine
const page = pathToFileURL(path.join(here, 'launch.html')).href + `?render=1&scale=${scale}` + (A.query ? '&' + A.query : '');   // --query=thumb=1 draws the thumbnail
const sleep = ms => new Promise(r => setTimeout(r, ms));

class CDP {
  constructor(url) {
    this.ws = new WebSocket(url); this.id = 0; this.pending = new Map(); this.errors = [];
    this.ws.onmessage = e => {
      const m = JSON.parse(e.data);
      if (m.id && this.pending.has(m.id)) { const p = this.pending.get(m.id); this.pending.delete(m.id); m.error ? p.rej(new Error(`${p.method}: ${JSON.stringify(m.error)}`)) : p.res(m.result); return; }
      if (m.method === 'Runtime.exceptionThrown') { const d = m.params.exceptionDetails; this.errors.push(d?.exception?.description || d?.text); console.error('[page]', d?.exception?.description || d?.text); }
      if (m.method === 'Runtime.consoleAPICalled' && m.params.type === 'error') console.error('[console]', m.params.args.map(a => a.value ?? a.description).join(' '));
    };
  }
  open() { return new Promise((res, rej) => { this.ws.onopen = res; this.ws.onerror = rej; }); }
  send(method, params = {}, timeout = 90000) {
    const id = ++this.id;
    return new Promise((res, rej) => {
      const to = setTimeout(() => { this.pending.delete(id); rej(new Error(`CDP timeout: ${method}`)); }, timeout);
      this.pending.set(id, { method, res: v => { clearTimeout(to); res(v); }, rej: e => { clearTimeout(to); rej(e); } });
      this.ws.send(JSON.stringify({ id, method, params }));
    });
  }
  // evaluate, and treat a throw in the page as a failure here: a frame taken after a throwing
  // seek would be the frame before it, with no complaint
  async eval(expression, awaitPromise = false) {
    const r = await this.send('Runtime.evaluate', { expression, awaitPromise, returnByValue: true });
    if (r.exceptionDetails) throw new Error(`page threw: ${r.exceptionDetails.exception?.description || r.exceptionDetails.text}\n  in: ${expression.slice(0, 120)}`);
    return r.result.value;
  }
}

async function launch(tag) {
  // Port 0: Chrome picks a free one and writes it down, so nothing collides with another render.
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), `launchfilm-chrome-${tag}-`));
  const proc = spawn(CHROME, ['--headless=new', '--remote-debugging-port=0', `--user-data-dir=${dir}`, '--hide-scrollbars', '--force-device-scale-factor=1', '--force-color-profile=srgb',
    '--no-first-run', '--no-default-browser-check', '--disable-background-timer-throttling', '--disable-renderer-backgrounding', '--mute-audio', '--allow-file-access-from-files', 'about:blank'], { stdio: 'ignore' });
  let port;
  for (let k = 0; k < 200 && !port; k++) { try { port = +fs.readFileSync(path.join(dir, 'DevToolsActivePort'), 'utf8').split('\n')[0]; } catch {} if (!port) await sleep(50); }
  if (!port) { proc.kill('SIGKILL'); throw new Error('Chrome did not open a debugging port'); }
  let targets;
  for (let k = 0; k < 100; k++) { try { targets = await (await fetch(`http://127.0.0.1:${port}/json`)).json(); if (targets.some(t => t.type === 'page')) break; } catch {} await sleep(100); }
  const cdp = new CDP(targets.find(t => t.type === 'page').webSocketDebuggerUrl);
  await cdp.open();
  await cdp.send('Runtime.enable'); await cdp.send('Page.enable');
  await cdp.send('Emulation.setDeviceMetricsOverride', { width: W, height: H, deviceScaleFactor: 1, mobile: false });
  await cdp.send('Page.navigate', { url: page });
  for (let k = 0; ; k++) {
    const ok = await cdp.eval('window.__ready === true').catch(() => false);
    if (ok) break;
    if (cdp.errors.length) throw new Error('the page failed to start: ' + cdp.errors[0]);
    if (k > 400) throw new Error('page never became ready');
    await sleep(50);
  }
  return { cdp, close: () => { proc.kill('SIGKILL'); try { fs.rmSync(dir, { recursive: true, force: true, maxRetries: 10, retryDelay: 100 }); } catch {} } };
}

async function grab(cdp, t, format = 'jpeg') {
  await cdp.eval(`seekAsync(${t}).then(() => new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r))))`, true);
  const { data } = await cdp.send('Page.captureScreenshot', format === 'png' ? { format: 'png' } : { format: 'jpeg', quality: 95, optimizeForSpeed: true });
  return Buffer.from(data, 'base64');
}

async function stills() {
  const out = path.resolve(A.out || 'out/stills'); fs.mkdirSync(out, { recursive: true });
  const { cdp, close } = await launch('stills');
  try {
    for (const t of String(A.stills).split(',').map(Number)) {
      const file = path.join(out, `t${t.toFixed(2).padStart(5, '0')}.png`);
      fs.writeFileSync(file, await grab(cdp, t, 'png'));
      console.log(file);
    }
  } finally { close(); }
}

async function video() {
  const { cdp: c0, close: close0 } = await launch('probe');
  let DUR, CUTS, FAST, SHOTS;
  try { DUR = await c0.eval('window.DUR'); CUTS = await c0.eval('window.CUTS || []'); FAST = await c0.eval('window.FAST || []'); SHOTS = await c0.eval('TL.SHOTS.map(s => ({id: s.id, a: s.a, b: s.b}))'); } finally { close0(); }
  let from = +(A.from ?? 0), to = +(A.to ?? DUR);
  let ranges = [[from, to]];
  if (A.shots) ranges = String(A.shots).split(',').map(id => { const s = SHOTS.find(k => k.id === id); if (!s) throw new Error('no shot ' + id); return [s.a, s.b]; });
  const frames = path.resolve(String(A.frames || path.join(os.homedir(), '.claude/plans/product-hunt-video/render', `${presetName}-${W}x${H}-${fps}`)).replace(/^~/, os.homedir()));
  if (A.fresh) fs.rmSync(frames, { recursive: true, force: true });
  fs.mkdirSync(frames, { recursive: true });
  // The disk is shared and nearly full: a master is about 3 GB of frames.
  const free = +spawnSync('df', ['-k', frames]).stdout.toString().trim().split('\n').pop().split(/\s+/)[3] / 1048576;
  if (free < 8) throw new Error(`only ${free.toFixed(1)} GB free: not rendering`);

  const want = []; for (const [a, b] of ranges) for (let f = Math.round(a * fps); f < Math.round(b * fps); f++) want.push(f);
  const file = f => path.join(frames, `f${String(f).padStart(5, '0')}.jpg`);
  const todo = want.filter(f => !fs.existsSync(file(f)));
  console.log(`${want.length} frames at ${W}x${H} ${fps}fps, ${samples} sample(s) (${fastSamples} in fast windows), ${workers} worker(s); ${want.length - todo.length} kept, ${todo.length} to take -> ${frames}`);

  // A frame's blur samples stay on its own side of a hard cut: a frame that straddled one would
  // average two shots into a one-frame dissolve.
  const frameTimes = f => {
    const c = f / fps, n = FAST.some(([a, b]) => c >= a && c <= b) ? fastSamples : samples, ts = [];
    const lo = Math.max(0, ...CUTS.filter(k => k <= c + 1e-9)), hi = Math.min(DUR, ...CUTS.filter(k => k > c + 1e-9));
    for (let s = 0; s < n; s++) ts.push(Math.min(hi - 1e-4, Math.max(lo, (f + (n > 1 ? ((s + .5) / n - .5) * shutter : 0)) / fps)));
    return ts;
  };

  const started = Date.now(); let done = 0, next = 0;
  await Promise.all(Array.from({ length: Math.min(workers, Math.max(1, todo.length)) }, async (_, w) => {
    if (!todo.length) return;
    const { cdp, close } = await launch('w' + w);
    const blend = fastSamples > 1 ? spawn('python3', [path.join(here, 'blend.py'), frames], { stdio: ['pipe', 'ignore', 'inherit'] }) : null;
    try {
      while (next < todo.length) {
        const f = todo[next++], shots = [];
        for (const t of frameTimes(f)) shots.push(await grab(cdp, t));
        if (!blend) { fs.writeFileSync(file(f) + '.part', shots[0]); fs.renameSync(file(f) + '.part', file(f)); }
        else {
          const hdr = Buffer.alloc(8); hdr.writeUInt32LE(f, 0); hdr.writeUInt32LE(shots.length, 4);
          const parts = [hdr]; for (const s of shots) { const l = Buffer.alloc(4); l.writeUInt32LE(s.length, 0); parts.push(l, s); }
          if (!blend.stdin.write(Buffer.concat(parts))) await new Promise(r => blend.stdin.once('drain', r));
        }
        if (++done % 60 === 0) { const el = (Date.now() - started) / 1000; process.stdout.write(`  ${done}/${todo.length} frames, ${el.toFixed(0)}s elapsed, ~${(el / done * (todo.length - done)).toFixed(0)}s left\n`); }
      }
    } finally {
      close();
      if (blend) { blend.stdin.end(); await new Promise(r => blend.on('close', r)); }
    }
  }));
  const missing = want.filter(f => !fs.existsSync(file(f)));
  if (missing.length) throw new Error(`${missing.length} frames missing, first ${missing[0]}`);
  console.log(`taken in ${((Date.now() - started) / 1000).toFixed(0)}s`);

  // A quick look: no grade (encode.sh does the real one). Frames are full-range BT.601 JPEGs;
  // convert to limited-range BT.709 and say so in the stream.
  if (A.mp4) {
    const mp4 = path.resolve(A.mp4); fs.mkdirSync(path.dirname(mp4), { recursive: true });
    const list = path.join(frames, `list-${process.pid}.txt`);
    fs.writeFileSync(list, want.map(f => `file '${file(f)}'\nduration ${1 / fps}`).join('\n') + '\n');
    const start = want[0] / fps, args = ['-y', '-hide_banner', '-loglevel', 'error', '-f', 'concat', '-safe', '0', '-r', String(fps), '-i', list];
    if (A.audio) args.push('-ss', String(start), '-i', path.resolve(A.audio));
    // --grade: the film's own grade (grade/grade.fg), so a rough cut is judged in its real light
    if (A.grade) {
      const vg = path.join(here, 'grade', `vignette-${W}x${H}.png`);
      if (!fs.existsSync(vg)) spawnSync('python3', [path.join(here, 'tools/vignette.py'), String(W), String(H), vg], { stdio: 'inherit' });
      const graph = fs.readFileSync(path.join(here, 'grade/grade.fg'), 'utf8').split('\n').filter(l => l && !l.startsWith('#')).join('').replaceAll('@W@', W).replaceAll('@H@', H)
        .replace('[0:v]format=gbrp16le', '[0:v]scale=in_range=pc:in_color_matrix=bt601,format=gbrp16le').replace('[1:v]format=gbrp16le[vg]', '[9:v]format=gbrp16le[vg]').replace(/\[out\]$/, '[g];[g]scale=out_color_matrix=bt709:out_range=tv,format=yuv420p[v]');
      // the vignette is one more input, looped; it is given the index the graph names
      const vi = A.audio ? 2 : 1; args.push('-loop', '1', '-framerate', String(fps), '-i', vg);
      args.push('-filter_complex', graph.replace('[9:v]', `[${vi}:v]`), '-map', '[v]'); if (A.audio) args.push('-map', '1:a');
      args.push('-t', String(want.length / fps), '-r', String(fps),
        '-color_range', 'tv', '-colorspace', 'bt709', '-color_primaries', 'bt709', '-color_trc', 'bt709',
        '-c:v', 'libx264', '-preset', 'medium', '-crf', String(A.crf ?? 19), '-g', String(fps), '-movflags', '+faststart');
    } else
    args.push('-vf', 'scale=in_range=pc:out_range=tv:in_color_matrix=bt601:out_color_matrix=bt709,format=yuv420p', '-r', String(fps),
      '-color_range', 'tv', '-colorspace', 'bt709', '-color_primaries', 'bt709', '-color_trc', 'bt709',
      '-c:v', 'libx264', '-preset', 'medium', '-crf', String(A.crf ?? 19), '-g', String(fps), '-movflags', '+faststart');
    if (A.audio) args.push('-c:a', 'aac', '-b:a', '192k', '-shortest');
    args.push(mp4);
    const r = spawnSync(FFMPEG, args, { stdio: 'inherit' }); fs.rmSync(list, { force: true });
    if (r.status) throw new Error('ffmpeg exited ' + r.status);
    console.log(`${mp4} (${(fs.statSync(mp4).size / 1048576).toFixed(1)} MB)`);
  }
}

(A.stills ? stills() : video()).catch(e => { console.error(e); process.exit(1); });
