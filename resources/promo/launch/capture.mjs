#!/usr/bin/env node
// Shoots the launch film's "plates": real screens of the app, from the film's private copy, sharp
// enough to fly a camera through. It grew out of the Product Hunt kit's shooter
// (~/.claude/plans/product-hunt/web.mjs: one headless Chrome over the DevTools protocol, a job per
// picture, signing in by the login form, real mouse presses, real files on a file input, clips by
// selector) and adds what a film needs. No npm packages.
//
//   node capture.mjs --list                          the plates, and which the seeded state can shoot
//   node capture.mjs                                 every plate of the seeded state
//   node capture.mjs --only=gp-indigo-top,ticket     some of them
//   node capture.mjs --poster                        the venue's Jazz Night poster (fixture/poster/)
//   node capture.mjs --sheet                         plates/_contact.jpg from what is there
//   node capture.mjs --probe=/indigo-room --out=x.png [--auth] [--scheme=dark] [--dsf=1] [--w=1440] [--h=900] [--js=...] [--text]
//
// What was added to the kit's shooter:
//   - Chrome picks its own debugging port, and cannot reach any host but 127.0.0.1;
//   - a plate waits for requests to settle, fonts and the pictures in the shot, then for two
//     screenshots in a row to match, and a script that throws stops the plate;
//   - both colour schemes, set outright (left alone a page follows the Mac, which turns dark at sunset);
//   - states of one interaction in one page, layers on a transparent ground, rectangles of named
//     parts, data pulled from the page, and the visible text of every plate (which is linted);
//   - requests answered in the browser instead of by the app: the AI parse, and the ticket's code;
//   - printed addresses read as on the hosted product (indigo-room.eventschedule.com);
//   - captures taken in small bands and stitched (see grab()).

import { spawnSync } from 'node:child_process';
import crypto from 'node:crypto';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { launchChrome, sleep } from './tools/cdp.mjs';

const here = path.dirname(fileURLToPath(import.meta.url));
const A = Object.fromEntries(process.argv.slice(2).map(a => { const [k, ...v] = a.replace(/^--/, '').split('='); return [k, v.length ? v.join('=') : true]; }));
const KEEP = process.env.LAUNCHFILM_KEEP || path.join(os.homedir(), '.claude/plans/product-hunt-video');
const FIXTURE = process.env.LAUNCHFILM_FIXTURE || path.join(KEEP, 'fixture.json');
const LOGIN = process.env.LAUNCHFILM_LOGIN || path.join(KEEP, 'seed/.login');
const OUT = path.resolve(A.dir || path.join(here, 'plates'));
const SCRATCH = process.env.LAUNCHFILM_SCRATCH || path.join(os.tmpdir(), 'launchfilm-capture');
const PROFILE = path.join(SCRATCH, 'chrome-profile');

/* ------------------------------------------------------------------ helpers */

const py = (code, ...args) => {
  const r = spawnSync('python3', ['-c', code, ...args], { encoding: 'utf8', maxBuffer: 1 << 26 });
  if (r.status !== 0) throw new Error(`python: ${r.stderr}`);
  return r.stdout.trim();
};
const sha = file => crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex').slice(0, 16);

/**
 * One screenshot of a clip, as a PNG file.
 *
 * Taken in horizontal bands and stitched. A reply from Chrome that carries more than about three
 * megabytes of PNG never arrives over Node's WebSocket, and the session is dead after it: measured,
 * a 4320 px wide band 300 px tall of a photograph is lost every time, three bands of 100 px arrive
 * in 0.2 s each. So a band is sized for the worst picture (noise, at about 3.5 bytes a pixel), not
 * the usual one. Bands end on whole device pixels, so the seams are exact.
 *
 * The clip must be inside the viewport unless beyond is set: captureBeyondViewport lays the whole
 * document out as one surface at the device scale.
 */
let grabSeq = 0;
async function grab(cdp, clip, dsf, { transparent = false, beyond = false } = {}) {
  const tmp = path.join(SCRATCH, 'tiles'); fs.mkdirSync(tmp, { recursive: true });
  // With a fractional scale a band must start and end on a device pixel: 2 CSS px at 4.5x, and so on.
  const step = Number.isInteger(dsf) ? 1 : Number.isInteger(dsf * 2) ? 2 : Number.isInteger(dsf * 4) ? 4 : 0;
  if (!step) throw new Error(`a device scale of ${dsf} cannot be banded exactly`);
  if (step > 1) clip = { x: Math.floor(clip.x / step) * step, y: Math.floor(clip.y / step) * step, width: Math.ceil(clip.width / step) * step, height: Math.ceil(clip.height / step) * step };
  let band = Math.max(step, Math.floor(0.6e6 / (clip.width * dsf * dsf)));
  band -= band % step;
  if (transparent) await cdp.send('Emulation.setDefaultBackgroundColorOverride', { color: { r: 0, g: 0, b: 0, a: 0 } });
  const tiles = [];
  try {
    for (let y = 0; y < clip.height; y += band) {
      const h = Math.min(band, clip.height - y);
      const { data } = await cdp.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: beyond, clip: { x: clip.x, y: clip.y + y, width: clip.width, height: h, scale: 1 } }, 30000);
      const t = path.join(tmp, `t${++grabSeq}.png`); fs.writeFileSync(t, Buffer.from(data, 'base64')); tiles.push(t);
    }
  } finally {
    if (transparent) await cdp.send('Emulation.setDefaultBackgroundColorOverride', {}).catch(() => {});
  }
  const out = path.join(tmp, `g${++grabSeq}.png`);
  py(`
import sys
from PIL import Image
tiles = [Image.open(p) for p in sys.argv[2:]]
w = tiles[0].width; h = sum(t.height for t in tiles)
if any(t.width != w for t in tiles): sys.exit('bands of different widths')
out = Image.new(tiles[0].mode, (w, h)); y = 0
for t in tiles:
    out.paste(t, (0, y)); y += t.height
out.save(sys.argv[1], optimize=False, compress_level=6)
`, out, ...tiles);
  tiles.forEach(t => fs.rmSync(t));
  return { file: out, clip };
}

/** Runs before any page script, on every document. */
const BOOT = scheme => `(() => {
  // Requests in flight, so a shot waits for the page's own data.
  window.__pending = 0; window.__lastNet = performance.now();
  const bump = d => { window.__pending += d; window.__lastNet = performance.now(); };
  const f = window.fetch;
  if (f) window.fetch = function (...a) { bump(1); return f.apply(this, a).finally(() => bump(-1)); };
  const X = XMLHttpRequest.prototype, send = X.send;
  X.send = function (...a) { bump(1); this.addEventListener('loadend', () => bump(-1), { once: true }); return send.apply(this, a); };
  try { localStorage.setItem('theme', ${JSON.stringify(scheme)}); } catch (e) {}
  // The cookie notice is answered before it can stand over a plate (the record is
  // "denied.<unix second>": resources/js/consent-state.js). Realtime being on is what raises it.
  try { document.cookie = 'cookie_consent=denied.' + Math.floor(Date.now() / 1000 - 3600) + '; path=/'; } catch (e) {}
  // Nothing mid-move in a still: transitions and animations jump to their end state.
  const still = () => {
    const s = document.createElement('style'); s.id = '__film_still';
    s.textContent = '*,*::before,*::after{transition-duration:0s!important;transition-delay:0s!important;animation-duration:0.001s!important;animation-delay:0s!important;animation-iteration-count:1!important;caret-color:transparent!important;scroll-behavior:auto!important}';
    (document.head || document.documentElement).appendChild(s);
  };
  if (document.documentElement) still(); else new MutationObserver((m, o) => { if (document.documentElement) { o.disconnect(); still(); } }).observe(document, { childList: true });
})();`;

/** 127.0.0.1:port/indigo-room reads as indigo-room.eventschedule.com, as on the hosted product. */
const REWRITE = `(() => {
  const local = /(https?:\\/\\/)?(127\\.0\\.0\\.1|localhost)(:\\d+)?/;
  const app = ['ticket','checkin','scan','analytics','newsletters','dashboard','login','sign_up','sales','events','settings','nl','tmp','images','build','api','docs','features'];
  const fix = t => t
    .replace(/(https?:\\/\\/)?(?:127\\.0\\.0\\.1|localhost)(?::\\d+)?\\/([a-z0-9-]+)((?:\\/[^\\s"'<]*)?)/g, (m, scheme, sub, rest) => (scheme ? 'https://' : '') + (app.includes(sub) ? 'eventschedule.com/' + sub + rest : sub + '.eventschedule.com' + rest))
    .replace(/https?:\\/\\/(?:127\\.0\\.0\\.1|localhost)(?::\\d+)?/g, 'https://eventschedule.com')
    .replace(/(?:127\\.0\\.0\\.1|localhost)(?::\\d+)?/g, 'eventschedule.com');
  const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
  const nodes = []; while (walker.nextNode()) nodes.push(walker.currentNode);
  for (const n of nodes) if (local.test(n.nodeValue) && !n.parentElement.closest('script, style')) n.nodeValue = fix(n.nodeValue);
  document.querySelectorAll('input[type=text], input:not([type]), input[type=url], textarea').forEach(el => { if (local.test(el.value)) el.value = fix(el.value); });
  return true;
})()`;

const VISIBLE_TEXT = `(() => {
  const out = [];
  const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
  while (walker.nextNode()) {
    const n = walker.currentNode, p = n.parentElement;
    if (!p || p.closest('script, style, noscript, template')) continue;
    const t = n.nodeValue.replace(/\\s+/g, ' ').trim();
    if (!t) continue;
    const r = p.getBoundingClientRect(), cs = getComputedStyle(p);
    if (r.width === 0 || r.height === 0 || cs.visibility === 'hidden' || cs.display === 'none') continue;
    out.push(t);
  }
  document.querySelectorAll('input:not([type=hidden]):not([type=password]), textarea').forEach(el => { if (el.value && el.getBoundingClientRect().width) out.push('[field] ' + el.value); });
  return out.join('\\n');
})()`;

/* ------------------------------------------------------------------ the browser */

class Shooter {
  constructor(fixture) {
    this.f = fixture; this.base = fixture.base;
    this.signedIn = false; this.external = []; this.fakes = []; this.faked = [];
  }

  async start() {
    fs.mkdirSync(SCRATCH, { recursive: true });
    const { cdp, close } = await launchChrome({ profileDir: PROFILE, width: 1440, height: 900, offline: true, args: this.chromeArgs || [] });
    this.cdp = cdp; this.close = close; this.signedIn = false; this.bootId = null;
    // the venue's own clock, so a time picker shows the venue's hours and not this machine's
    await cdp.send('Emulation.setTimezoneOverride', { timezoneId: this.f.timezone });
    await cdp.send('Emulation.setLocaleOverride', { locale: 'en-US' }).catch(() => {});
    // the door's scan page asks for a camera; the stand-in one is allowed without a prompt
    await cdp.send('Browser.grantPermissions', { origin: this.base, permissions: ['videoCapture'] }).catch(() => {});
    cdp.on('Network.requestWillBeSent', p => {
      const u = p.request.url;
      if (/^(data|blob|about|chrome|devtools):/.test(u)) return;
      if (!u.startsWith(this.base) && !u.startsWith('file://')) this.external.push(u);
    });
    // What the page must not really ask for is answered here, in the browser.
    await cdp.send('Fetch.enable', { patterns: [{ urlPattern: '*', requestStage: 'Request' }] });
    cdp.on('Fetch.requestPaused', async p => {
      try {
        const fake = this.fakes.find(k => k.match.test(p.request.url) && (!k.method || k.method === p.request.method));
        if (!fake) { await cdp.send('Fetch.continueRequest', { requestId: p.requestId }); return; }
        const r = fake.respond(this.f, p.request.url, p.request.postData || '');
        this.faked.push(`${p.request.method} ${p.request.url.replace(this.base, '')}`);
        const bytes = r.file ? fs.readFileSync(r.file) : Buffer.from(typeof r.body === 'string' ? r.body : JSON.stringify(r.body));
        await cdp.send('Fetch.fulfillRequest', { requestId: p.requestId, responseCode: r.status || 200, responseHeaders: [{ name: 'Content-Type', value: r.type || 'application/json' }, { name: 'Cache-Control', value: 'no-store' }], body: bytes.toString('base64') });
      } catch (e) { /* the request went away */ }
    });
  }

  async restart() { try { this.close(); } catch {} await sleep(400); await this.start(); }

  async viewport(w, h, dsf, mobile = false) {
    await this.cdp.send('Emulation.setDeviceMetricsOverride', { width: w, height: h, deviceScaleFactor: dsf, mobile });
  }

  async boot(scheme) {
    await this.cdp.send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-color-scheme', value: scheme }, { name: 'prefers-reduced-motion', value: 'reduce' }] });
    if (this.bootId) await this.cdp.send('Page.removeScriptToEvaluateOnNewDocument', { identifier: this.bootId });
    this.bootId = (await this.cdp.send('Page.addScriptToEvaluateOnNewDocument', { source: BOOT(scheme) })).identifier;
  }

  async go(url) {
    this.external = []; this.cdp.pageErrors = [];
    const full = /^(https?|file):/.test(url) ? url : this.base + url;
    await this.cdp.send('Page.navigate', { url: 'about:blank' });
    const nav = await this.cdp.send('Page.navigate', { url: full });
    if (nav.errorText) throw new Error(`navigation to ${full} failed: ${nav.errorText}`);
    await this.cdp.until(`location.href !== 'about:blank' && document.readyState === 'complete'`, { timeout: 45000, what: `${full} to load` });
    return full;
  }

  /** The wait before a shot. */
  async settle({ waitFor = null, quiet = 350, timeout = 45000 } = {}) {
    const c = this.cdp;
    await c.until(`document.readyState === 'complete'`, { timeout, what: 'the load event' });
    await c.until(`(window.__pending || 0) <= 0 && performance.now() - (window.__lastNet || 0) > ${quiet}`, { timeout, what: 'requests to settle' });
    await c.eval(`document.fonts.ready.then(() => true)`);
    // Pictures that are in the shot must have arrived. One that is lazy and off screen never loads.
    const SEEN = `(i => { const b = i.getBoundingClientRect(); return b.width > 0 && b.height > 0 && b.bottom > 0 && b.top < innerHeight && b.right > 0 && b.left < innerWidth; })`;
    try { await c.until(`[...document.images].filter(${SEEN}).every(i => i.complete)`, { timeout: 20000, what: 'pictures' }); }
    catch (e) { throw new Error(`pictures never finished loading: ${(await c.eval(`[...document.images].filter(${SEEN}).filter(i => !i.complete).map(i => i.currentSrc || i.src).slice(0, 6)`)).join(' , ')}`); }
    const broken = await c.eval(`[...document.images].filter(${SEEN}).filter(i => i.complete && i.naturalWidth === 0 && (i.currentSrc || i.src)).map(i => i.currentSrc || i.src)`);
    if (broken.length && !this.allowBroken) throw new Error(`broken pictures: ${[...new Set(broken)].join(', ')}`);
    await c.until(`!document.querySelector('[v-cloak]')`, { timeout, what: 'Vue to mount' });
    if (waitFor) await c.until(waitFor, { timeout, what: `the plate's own condition (${waitFor.slice(0, 90)})` });
    await c.eval(`new Promise(r => requestAnimationFrame(() => requestAnimationFrame(() => r(true))))`);
  }

  /** Two small shots in a row must match before the real one is taken. */
  async stable({ tries = 14 } = {}) {
    const vp = await this.cdp.eval(`({ w: innerWidth, h: innerHeight, x: scrollX, y: scrollY })`);
    const k = Math.min(0.34, 520 / vp.w);
    const small = () => this.cdp.send('Page.captureScreenshot', { format: 'png', clip: { x: vp.x, y: vp.y, width: vp.w, height: Math.min(vp.h, 1000), scale: k } }, 30000).then(r => r.data);
    let prev = await small();
    for (let i = 0; i < tries; i++) { await sleep(300); const next = await small(); if (next === prev) return; prev = next; }
    throw new Error('the page never held still (two screenshots in a row never matched)');
  }

  // The kit's way in: the login form, filled and submitted. The password goes to the page and nowhere else.
  async signIn() {
    if (this.signedIn) return;
    const [email, password] = fs.readFileSync(LOGIN, 'utf8').split('\n');
    await this.viewport(1280, 900, 1);
    await this.boot('dark');
    await this.go('/login');
    await this.cdp.until(`!!document.querySelector('form[action$="/login"]')`, { timeout: 20000, what: 'the login form' });
    await this.cdp.eval(`(() => { const f = document.querySelector('form[action$="/login"]'); f.querySelector('input[type="email"], input[name="email"]').value = ${JSON.stringify(email)}; f.querySelector('input[type="password"]').value = ${JSON.stringify(password)}; f.submit(); return true; })()`);
    for (let k = 0; ; k++) { await sleep(150); const at = await this.cdp.eval(`document.readyState === 'complete' ? location.pathname : ''`).catch(() => ''); if (at && at !== '/login') break; if (k > 200) throw new Error('sign-in did not leave the login page'); }
    this.signedIn = true;
  }

  async signOut() { if (this.signedIn) { await this.cdp.send('Network.clearBrowserCookies'); this.signedIn = false; } }

  /** A real press of the mouse at a point of the viewport, as the kit does it. */
  async press(pt) {
    for (const type of ['mouseMoved', 'mousePressed', 'mouseReleased']) await this.cdp.send('Input.dispatchMouseEvent', { type, x: pt.x, y: pt.y, button: type === 'mouseMoved' ? 'none' : 'left', clickCount: type === 'mouseMoved' ? 0 : 1 });
  }
}

/* ------------------------------------------------------------------ one plate */

async function rectOf(cdp, selector, pad = 0) {
  const r = await cdp.eval(`(() => { const el = document.querySelector(${JSON.stringify(selector)}); if (!el) return null; const b = el.getBoundingClientRect(); return { x: b.left + scrollX, y: b.top + scrollY, width: b.width, height: b.height }; })()`);
  if (!r || !r.width || !r.height) throw new Error(`nothing to shoot at ${selector}`);
  return { x: Math.max(0, Math.floor(r.x - pad)), y: Math.max(0, Math.floor(r.y - pad)), width: Math.ceil(r.width + pad * 2), height: Math.ceil(r.height + pad * 2) };
}

function run(cmd) {
  const r = spawnSync(cmd[0], cmd.slice(1), { encoding: 'utf8' });
  if (r.status !== 0) throw new Error(`${cmd.slice(0, 4).join(' ')} failed: ${(r.stderr || r.stdout || '').trim().split('\n').pop()}`);
}

async function shootPlate(sh, plate, manifest) {
  const f = sh.f, cdp = sh.cdp;
  const [w, h] = plate.viewport || [1440, 900];
  const dsf = +(A.dsf || plate.dsf || 3);
  const js = v => (typeof v === 'function' ? v(f) : v);
  const scheme = plate.scheme || (plate.auth ? 'dark' : 'light');
  const file = id => path.join(OUT, `${id}.png`);

  // before: commands run against the copy first (a language switched, visitors simulated)
  for (const cmd of js(plate.before) || []) run(cmd);

  // A visitor arrives with no cookies at all: a seat pressed in an earlier plate is not theirs.
  if (plate.auth) await sh.signIn(); else { await sh.signOut(); await cdp.send('Network.clearBrowserCookies'); }
  sh.fakes = plate.fakes || []; sh.faked = [];

  await sh.viewport(w, h, dsf, !!plate.mobile);
  await sh.boot(scheme);
  const url = await sh.go(js(plate.url));
  await sleep(plate.settle ?? 600);
  await sh.settle({ waitFor: js(plate.waitFor) });
  // the admin's own switch: its palette follows data-theme, which this sets
  if (plate.auth) await cdp.eval(`(window.setTheme && window.setTheme(${JSON.stringify(scheme)}), true)`);
  // what a visitor never sees in a product shot: the cookie notice
  await cdp.eval(`([...document.querySelectorAll('body *')].filter(e => getComputedStyle(e).position === 'fixed' && /Allow all/.test(e.textContent) && e.getBoundingClientRect().height < 420).forEach(e => e.style.setProperty('display', 'none', 'important')), true)`);

  const step = async s => {
    if (s.pre) await cdp.eval(`(async () => { ${js(s.pre)}; return true; })()`);
    // files: real files on a file input, as a person choosing them would put them
    if (s.files) {
      const { root } = await cdp.send('DOM.getDocument', { depth: 0 });
      const { nodeId } = await cdp.send('DOM.querySelector', { nodeId: root.nodeId, selector: s.files.selector });
      if (!nodeId) throw new Error(`no file input at ${s.files.selector}`);
      await cdp.send('DOM.setFileInputFiles', { nodeId, files: js(s.files.paths) });
      await sleep(s.files.wait ?? 1200);
    }
    // press: snippets that each return a point of the viewport; each is pressed with the real mouse.
    // One snippet per press, run just before it: a page that moves after the first press would
    // leave a second, stale point behind.
    for (const snippet of [].concat(s.press || [])) {
      const pt = await cdp.eval(`(async () => { ${js(snippet)} })()`);
      if (!pt) throw new Error('nothing to press');
      await sh.press(pt);
      await sleep(s.pressWait ?? 700);
    }
    if (s.post) await cdp.eval(`(async () => { ${js(s.post)}; return true; })()`);
    if (s.pre || s.files || s.press || s.post) await sh.settle({ waitFor: js(s.waitAfter) });
  };
  await step(plate);
  await cdp.eval(REWRITE);
  if (plate.hide) await cdp.eval(`(() => { const s = document.createElement('style'); s.textContent = ${JSON.stringify(js(plate.hide).join(',') + '{visibility:hidden!important}')}; document.head.appendChild(s); return true; })()`);

  const record = { id: plate.id, url: url.replace(sh.base, ''), viewport: [w, h], dsf, scheme, state: f.state, auth: !!plate.auth, files: {}, rects: {}, layers: {}, staged: plate.staged || null };

  // Where to cut: a rectangle already known, a script that returns one, a selector, or the window.
  const cutOf = async ({ fixed, clipJs, clipSel, clipPad }) => {
    if (fixed) return fixed;
    if (clipJs) { const r = await cdp.eval(`(() => { ${js(clipJs)} })()`); if (!r) throw new Error('the clip script found nothing'); return { x: Math.floor(r.x), y: Math.floor(r.y), width: Math.ceil(r.width), height: Math.ceil(r.height) }; }
    if (clipSel) return rectOf(cdp, js(clipSel), clipPad);
    return null;
  };
  // Hidden for the shot: everything but one element, on a transparent ground.
  const isolate = async sel => cdp.eval(`(() => {
    document.getElementById('__film_iso')?.remove(); document.querySelectorAll('.__iso').forEach(e => e.classList.remove('__iso'));
    if (!${JSON.stringify(sel || '')}) return true;
    const s = document.createElement('style'); s.id = '__film_iso';
    s.textContent = 'html,body{background:transparent!important;background-image:none!important} body::before,body::after{display:none!important} body *{visibility:hidden!important} .__iso,.__iso *{visibility:visible!important}';
    document.head.appendChild(s);
    const els = [...document.querySelectorAll(${JSON.stringify(sel || '')})]; if (!els.length) throw new Error('nothing to isolate at ' + ${JSON.stringify(sel || '')});
    els.forEach(e => e.classList.add('__iso')); return true;
  })()`);

  const shot = async (id, o = {}) => {
    const opt = { clipSel: plate.clip, clipJs: plate.clipJs, clipPad: plate.clipPad || 0, maxH: plate.maxH, side: true, scale: dsf, iso: plate.isolate, ...o };
    if (opt.scale !== dsf) { await sh.viewport(w, h, opt.scale, !!plate.mobile); await sleep(350); }
    let clip = await cutOf(opt);
    // stay: shot where it stands (a bar stuck to the foot of the window moves when the page does)
    if (clip && !opt.stay) { await cdp.eval(`(scrollTo(0, Math.max(0, ${clip.y} - ${plate.clipTop ?? (plate.auth ? 88 : 16)})), true)`); await sleep(250); if (!opt.fixed) clip = await cutOf(opt); }
    if (!clip) clip = { x: await cdp.eval('scrollX'), y: await cdp.eval('scrollY'), width: w, height: h };
    if (opt.maxH && clip.height > opt.maxH) clip.height = opt.maxH;
    if (opt.iso) await isolate(js(opt.iso));
    await sh.stable();
    const view = await cdp.eval(`({ top: scrollY, bottom: scrollY + innerHeight })`);
    if (clip.y < view.top - 1 || clip.y + clip.height > view.bottom + 1) throw new Error(`the clip (${clip.height} px tall at y ${clip.y}) does not fit the ${h} px viewport: give the plate a taller viewport or a maxH`);
    const g = await grab(cdp, clip, opt.scale, { transparent: !!(plate.transparent || opt.iso) });
    clip = g.clip; fs.mkdirSync(OUT, { recursive: true }); fs.renameSync(g.file, file(id));
    if (opt.iso) await isolate(null);
    if (opt.side) fs.writeFileSync(path.join(OUT, `${id}.txt`), await cdp.eval(VISIBLE_TEXT) + '\n');
    record.files[id] = { clip, px: [Math.round(clip.width * opt.scale), Math.round(clip.height * opt.scale)], scale: opt.scale, sha: sha(file(id)), bytes: fs.statSync(file(id)).size };

    // Where things are, in THIS picture's pixels.
    const rects = {};
    for (const [name, sel] of Object.entries(js(plate.rects) || {})) {
      const all = await cdp.eval(`[...document.querySelectorAll(${JSON.stringify(sel)})].map(el => { const b = el.getBoundingClientRect(); return [b.left + scrollX, b.top + scrollY, b.width, b.height]; })`);
      rects[name] = all.map(([x, y, bw, bh]) => [(x - clip.x) * opt.scale, (y - clip.y) * opt.scale, bw * opt.scale, bh * opt.scale].map(v => Math.round(v * 100) / 100));
    }
    if (Object.keys(rects).length) record.rects[id] = rects;
    const extract = 'extract' in o ? o.extract : (opt.side ? plate.extract : null);
    if (extract) {
      const data = await cdp.eval(`(${js(extract)})(${JSON.stringify({ clip, dsf: opt.scale, id })})`);
      const name = o.json || (id === plate.id ? plate.json : null) || id;
      if (data) { fs.writeFileSync(path.join(OUT, `${name}.json`), JSON.stringify({ plate: id, size: record.files[id].px, rects, ...data }, null, 1) + '\n'); record.files[id].json = `${name}.json`; }
    }
    console.log(`  ${id}  ${record.files[id].px.join('x')}  ${(record.files[id].bytes / 1048576).toFixed(2)} MB`);
    // the same moment through another window, or closer
    if (opt.side) for (const extra of o.also || (id === plate.id ? plate.also : null) || []) {
      await shot(extra.id, { clipSel: extra.clip ?? null, clipJs: extra.clipJs ?? null, clipPad: extra.pad || 0, maxH: extra.maxH, side: false, scale: extra.dsf || dsf, iso: extra.isolate ?? null, fixed: null, stay: !!extra.stay, also: [] });
    }
    if (opt.scale !== dsf) { await sh.viewport(w, h, dsf, !!plate.mobile); await sleep(350); }
    return clip;
  };

  const baseClip = await shot(plate.id);

  // Successive states of one interaction, in the same page, so they register.
  for (const st of plate.states || []) {
    await step(st);
    await cdp.eval(REWRITE);
    const o = { also: st.also || [], extract: st.extract ?? null, json: st.json };
    if (st.iso !== undefined) o.iso = st.iso;
    if (plate.sameClip && st.clip === undefined && !st.clipJs) o.fixed = baseClip;
    if (st.clip !== undefined) { o.clipSel = st.clip; o.clipJs = null; o.clipPad = st.clipPad || 0; o.maxH = st.maxH; }
    if (st.clipJs) { o.clipJs = st.clipJs; o.clipSel = null; }
    if (st.extract === undefined) delete o.extract;
    await shot(st.id, o);
  }

  // Layers: each kept element alone on a transparent ground, in the plate's own frame unless it
  // asks for its own rectangle.
  for (const layer of js(plate.layers) || []) {
    await cdp.eval(`(() => {
      document.getElementById('__film_layer')?.remove();
      document.querySelectorAll('.__keep').forEach(e => e.classList.remove('__keep'));
      const s = document.createElement('style'); s.id = '__film_layer';
      s.textContent = ${JSON.stringify(layer.css || 'html,body{background:transparent!important;background-image:none!important} body::before,body::after{display:none!important} body *{visibility:hidden!important} .__keep,.__keep *{visibility:visible!important}')};
      document.head.appendChild(s);
      const els = ${layer.selector ? `[...document.querySelectorAll(${JSON.stringify(layer.selector)})]` : '[]'};
      if (${layer.selector ? 'true' : 'false'} && !els.length) throw new Error('layer selector matched nothing: ' + ${JSON.stringify(layer.selector || '')});
      els.forEach(e => e.classList.add('__keep'));
      ${layer.hide ? `document.querySelectorAll(${JSON.stringify(layer.hide)}).forEach(e => { e.style.setProperty('visibility', 'hidden', 'important'); e.classList.add('__film_hid'); });` : ''}
      return true;
    })()`);
    await sh.stable();
    const clip = layer.own ? await rectOf(cdp, layer.selector, layer.pad || 0) : baseClip;
    const g = await grab(cdp, clip, dsf, { transparent: !layer.opaque });
    fs.renameSync(g.file, file(layer.id));
    record.layers[layer.id] = { rect: [(g.clip.x - baseClip.x) * dsf, (g.clip.y - baseClip.y) * dsf, g.clip.width * dsf, g.clip.height * dsf], bytes: fs.statSync(file(layer.id)).size };
    console.log(`  ${layer.id}  ${Math.round(g.clip.width * dsf)}x${Math.round(g.clip.height * dsf)}`);
    await cdp.eval(`(document.querySelectorAll('.__film_hid').forEach(e => { e.style.removeProperty('visibility'); e.classList.remove('__film_hid'); }), true)`);
  }
  if (plate.layers) await cdp.eval(`(document.getElementById('__film_layer')?.remove(), document.querySelectorAll('.__keep').forEach(e => e.classList.remove('__keep')), true)`);
  // the layers' rectangles go in the plate's own .json too, beside the rectangles of its parts
  const side = record.files[plate.id]?.json && path.join(OUT, record.files[plate.id].json);
  if (side && Object.keys(record.layers).length) fs.writeFileSync(side, JSON.stringify({ ...JSON.parse(fs.readFileSync(side, 'utf8')), layers: record.layers }, null, 1) + '\n');

  for (const cmd of js(plate.after) || []) run(cmd);

  record.external = [...new Set(sh.external)];
  record.faked = [...new Set(sh.faked)];
  record.pageErrors = cdp.pageErrors.slice();
  if (record.external.length) console.log(`  ! tried to reach outside: ${record.external.slice(0, 4).join(' ')}`);
  if (record.pageErrors.length) console.log(`  ! page errors: ${record.pageErrors.slice(0, 2).join(' | ').slice(0, 300)}`);
  manifest.plates[plate.id] = record;
}

/* ------------------------------------------------------------------ lint, sheet, poster */

const BANNED = [
  [/simpson|springfield|moe'?s\b|krusty|\bhomer\b|\bbart\b|\bmarge\b|shelbyville|aztec|\bduff\b|bleeding gums|sideshow|flanders|milhouse|isotopes|carbon rod|steamed hams|tomacco|be sharps|choo-choo|max power|monkey dishwasher|s-m-r-t|pin pals|beer baron|greyhounds|le grille|lemon tree|gabbo|mendoza|thrillho|stonecutter|retirement castle|fallout boy/i, 'the demo cartoon\'s vocabulary'],
  [/127\.0\.0\.1|localhost/i, 'a local address'],
  [/SQLSTATE|Whoops|Stack trace|Undefined (variable|array key|index)/i, 'error text'],
  [/\bcash\b/i, 'the word "cash"'],
];
const EMAIL = /[A-Z0-9._%+-]+@([A-Z0-9.-]+\.[A-Z]{2,})/gi;

function lint(dir) {
  const problems = [];
  for (const name of fs.readdirSync(dir)) {
    if (!name.endsWith('.txt')) continue;
    const text = fs.readFileSync(path.join(dir, name), 'utf8');
    for (const [re, what] of BANNED) { const m = text.match(re); if (m) problems.push(`${name}: ${what} ("${m[0]}")`); }
    // the product's own contact address (in the admin's help dialog) is not a person's
    for (const m of text.matchAll(EMAIL)) if (!/\.example$/i.test(m[1]) && !/^eventschedule\.com$/i.test(m[1])) problems.push(`${name}: an address not on .example (${m[0]})`);
  }
  return problems;
}

function sheet() {
  const files = fs.readdirSync(OUT).filter(n => n.endsWith('.png') && !n.includes('@')).sort().map(n => path.join(OUT, n));
  const r = spawnSync('python3', [path.join(here, 'tools/sheet.py'), path.join(OUT, '_contact.jpg'), ...files, '--cols=6', '--cell=460', '--ratio=0.8', '--label'], { encoding: 'utf8' });
  console.log(r.stdout.trim() || r.stderr.trim());
}

// The venue's own Jazz Night poster, from the kit's page: for the film (4x), for the app's flyer, for the import box.
async function poster() {
  const dir = path.join(here, 'fixture/poster');
  fs.mkdirSync(path.join(OUT, 'art'), { recursive: true });
  const { cdp, close } = await launchChrome({ profileDir: PROFILE + '-poster', width: 600, height: 800, offline: true, args: ['--allow-file-access-from-files'] });
  try {
    for (const [scale, target] of [[4, path.join(OUT, 'art/jazz-night@4x.png')], [2, path.join(dir, 'jazz-night.png')]]) {
      await cdp.send('Emulation.setDeviceMetricsOverride', { width: 600, height: 800, deviceScaleFactor: scale, mobile: false });
      await cdp.send('Page.navigate', { url: pathToFileURL(path.join(dir, 'poster.html')).href });
      await cdp.until(`document.readyState === 'complete'`, { timeout: 20000 });
      await cdp.eval(`document.fonts.ready.then(() => true)`);
      const bad = await cdp.eval(`[...document.fonts].filter(f => f.status !== 'loaded').map(f => f.family + ':' + f.status)`);
      if (bad.length) throw new Error(`the poster's face did not load: ${bad.join(', ')}`);
      await sleep(300);
      const g = await grab(cdp, { x: 0, y: 0, width: 600, height: 800 }, scale);
      fs.renameSync(g.file, target);
      console.log(`  ${path.relative(here, target)}  ${600 * scale}x${800 * scale}`);
    }
    // the app's copy of it: a JPEG, as an uploaded flyer would be
    py(`
import sys
from PIL import Image
Image.open(sys.argv[1]).convert('RGB').save(sys.argv[2], 'JPEG', quality=92, optimize=True)
`, path.join(dir, 'jazz-night.png'), path.join(dir, 'demo_launch_jazz_night.jpg'));
    console.log('  fixture/poster/demo_launch_jazz_night.jpg');
  } finally { close(); }
}

/* ------------------------------------------------------------------ main */

async function main() {
  if (A.poster) return poster();
  if (A.sheet) return sheet();

  const fixture = JSON.parse(fs.readFileSync(FIXTURE, 'utf8'));
  const sh = new Shooter(fixture);

  if (A.probe) {
    sh.allowBroken = true;
    await sh.start();
    try {
      if (A.auth) await sh.signIn();
      const w = +(A.w || 1440), h = +(A.h || 900), dsf = +(A.dsf || 1), scheme = A.scheme && A.scheme !== true ? String(A.scheme) : (A.auth ? 'dark' : 'light');
      await sh.viewport(w, h, dsf, !!A.mobile);
      await sh.boot(scheme);
      await sh.go(String(A.probe));
      await sleep(+(A.settle || 600));
      await sh.settle();
      if (A.auth) await sh.cdp.eval(`(window.setTheme && window.setTheme(${JSON.stringify(scheme)}), true)`);
      if (A.js) await sh.cdp.eval(`(async () => { ${A.js}; return true; })()`);
      await sleep(+(A.pause || 300));
      await sh.cdp.eval(REWRITE);
      if (A.y != null) await sh.cdp.eval(`(scrollTo(0, ${+A.y}), true)`);
      const clip = A.clip && A.clip !== true ? await rectOf(sh.cdp, String(A.clip)) : { x: 0, y: await sh.cdp.eval('scrollY'), width: w, height: h };
      const out = path.resolve(String(A.out || 'probe.png'));
      fs.renameSync((await grab(sh.cdp, clip, dsf)).file, out);
      if (A.text) fs.writeFileSync(out.replace(/\.png$/, '.txt'), await sh.cdp.eval(VISIBLE_TEXT));
      if (A.eval) console.log(JSON.stringify(await sh.cdp.eval(`(async () => { ${A.eval} })()`)));
      console.log(out, `(${clip.width}x${clip.height} @${dsf}x ${scheme})`, sh.external.length ? `external: ${[...new Set(sh.external)].slice(0, 5).join(' ')}` : '', sh.cdp.pageErrors.length ? `errors: ${sh.cdp.pageErrors.join(' | ').slice(0, 300)}` : '');
    } finally { sh.close(); }
    return;
  }

  const { default: plates } = await import(pathToFileURL(path.join(here, 'plates.config.mjs')).href);
  const only = A.only && A.only !== true ? String(A.only).split(',') : null;
  const fits = p => !p.state || p.state === 'any' || p.state === fixture.state;
  // A plate marked manual is a stand-in for another and is shot only when asked for by name.
  const todo = plates.filter(p => (only ? only.includes(p.id) : !p.manual) && fits(p));

  if (A.list) {
    for (const p of plates) console.log(`${fits(p) ? '*' : ' '} ${p.id.padEnd(24)} ${String(p.state || 'any').padEnd(7)} ${p.auth ? 'owner  ' : 'visitor'} ${(p.scheme || (p.auth ? 'dark' : 'light')).padEnd(5)} ${(() => { try { return typeof p.url === 'function' ? p.url(fixture) : p.url; } catch { return '(an address this state does not have)'; } })()}`);
    console.log(`\nseeded state: ${fixture.state} (${fixture.made_at}). * = can be shot now.`);
    return;
  }
  if (only) { const no = plates.filter(p => only.includes(p.id) && !fits(p)); if (no.length) console.log(`not shot (they need another state than "${fixture.state}"): ${no.map(p => p.id).join(', ')}`); }

  // Before a single plate: the thing answering must be the film's copy.
  const probe = await fetch(fixture.base + '/indigo-room').then(r => r.text()).catch(() => '');
  if (!probe.includes('The Indigo Room')) throw new Error(`${fixture.base} is not serving the film's copy. Run fixture/serve.sh.`);

  fs.mkdirSync(OUT, { recursive: true });
  const manifestFile = path.join(OUT, 'MANIFEST.json');
  const manifest = fs.existsSync(manifestFile) ? JSON.parse(fs.readFileSync(manifestFile, 'utf8')) : { plates: {} };
  manifest.base = fixture.base; manifest.timezone = fixture.timezone;

  // Signed-out plates first: signing out clears every cookie, so there is one sign-in per run.
  todo.sort((a, b) => (a.auth ? 1 : 0) - (b.auth ? 1 : 0));
  for (const p of todo) if (p.setup) await p.setup(fixture, { SCRATCH, here });
  sh.chromeArgs = [...new Set(todo.flatMap(p => p.chromeArgs ? p.chromeArgs(fixture, { SCRATCH }) : []))];
  await sh.start();
  const failed = [];
  try {
    for (const plate of todo) {
      console.log(plate.id);
      let err = null;
      for (let attempt = 0; attempt < 2; attempt++) {
        try { await shootPlate(sh, plate, manifest); err = null; break; }
        catch (e) { err = e; if (!/CDP timeout|WebSocket/.test(e.message)) break; console.log(`  (Chrome stopped answering: restarting it and trying once more)`); await sh.restart(); }
      }
      if (err) { failed.push(plate.id); console.log(`  FAILED: ${err.message.split('\n')[0]}`); if (/CDP timeout|WebSocket/.test(err.message)) await sh.restart(); }
      manifest.shot_at = new Date().toISOString();
      fs.writeFileSync(manifestFile, JSON.stringify(manifest, null, 1) + '\n');
    }
  } finally { sh.close(); }

  const problems = lint(OUT);
  console.log(problems.length ? `\nLINT: ${problems.length} problem(s)\n  ` + problems.join('\n  ') : '\nlint: clean');
  if (failed.length) { console.log(`FAILED plates: ${failed.join(', ')}`); process.exitCode = 1; }
}

main().catch(e => { console.error(e); process.exit(1); });
