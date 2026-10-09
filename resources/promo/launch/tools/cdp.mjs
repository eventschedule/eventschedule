// A small Chrome DevTools Protocol client with no npm dependencies (Node 22's own WebSocket), and
// a launcher for a headless Chrome that picks its own debugging port.
//
//   import { launchChrome } from './tools/cdp.mjs';
//   const { cdp, close } = await launchChrome({ profileDir, width: 1440, height: 900 });
//   await cdp.send('Page.navigate', { url });
//   const value = await cdp.eval('document.title');          // throws if the page threw
//   close();
//
// Differences from the showreel's renderer, on purpose: the port is 0 (Chrome chooses; the other
// session renders on fixed ports from 9400), every evaluate checks exceptionDetails (a throwing
// script otherwise does nothing, silently), and events can be subscribed to (Fetch interception).

import { spawn } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

export const CHROME = process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const sleep = ms => new Promise(r => setTimeout(r, ms));

export class CDP {
  constructor(url) {
    this.ws = new WebSocket(url);
    this.id = 0;
    this.pending = new Map();
    this.listeners = new Map();
    this.pageErrors = [];
    this.ws.onmessage = e => {
      const m = JSON.parse(e.data);
      if (m.id && this.pending.has(m.id)) {
        const p = this.pending.get(m.id);
        this.pending.delete(m.id);
        m.error ? p.rej(new Error(`${p.method}: ${JSON.stringify(m.error)}`)) : p.res(m.result);
        return;
      }
      if (m.method === 'Runtime.exceptionThrown') {
        const d = m.params.exceptionDetails;
        this.pageErrors.push(d?.exception?.description || d?.text || 'unknown page error');
      }
      for (const fn of this.listeners.get(m.method) || []) fn(m.params);
    };
  }

  open() { return new Promise((res, rej) => { this.ws.onopen = res; this.ws.onerror = rej; }); }

  on(method, fn) {
    if (!this.listeners.has(method)) this.listeners.set(method, []);
    this.listeners.get(method).push(fn);
  }

  send(method, params = {}, timeout = 60000) {
    const id = ++this.id;
    return new Promise((res, rej) => {
      const to = setTimeout(() => { this.pending.delete(id); rej(new Error(`CDP timeout after ${timeout} ms: ${method}`)); }, timeout);
      this.pending.set(id, { method, res: v => { clearTimeout(to); res(v); }, rej: e => { clearTimeout(to); rej(e); } });
      this.ws.send(JSON.stringify({ id, method, params }));
    });
  }

  /** Evaluate an expression (a promise is awaited) and return its value. Throws if the page threw. */
  async eval(expression, { timeout = 60000 } = {}) {
    const r = await this.send('Runtime.evaluate', { expression, awaitPromise: true, returnByValue: true, userGesture: true }, timeout);
    if (r.exceptionDetails) {
      const d = r.exceptionDetails;
      throw new Error(`page script threw: ${d.exception?.description || d.text}\n  in: ${expression.slice(0, 200)}`);
    }
    return r.result?.value;
  }

  /** Poll a boolean expression until it is true. */
  async until(expression, { timeout = 30000, every = 100, what = expression } = {}) {
    const start = Date.now();
    for (;;) {
      if (await this.eval(`!!(${expression})`)) return;
      if (Date.now() - start > timeout) throw new Error(`timed out after ${timeout} ms waiting for: ${what}`);
      await sleep(every);
    }
  }
}

/**
 * Start one headless Chrome with its own profile and connect to its first page.
 *
 * offline: true maps every host but 127.0.0.1 to nowhere, so a plate cannot fetch anything from
 * outside (no CDN, no analytics, no AI service) even if a page asks.
 */
export async function launchChrome({ profileDir, width = 1440, height = 900, offline = true, args = [] } = {}) {
  fs.rmSync(profileDir, { recursive: true, force: true });
  fs.mkdirSync(profileDir, { recursive: true });

  const flags = [
    '--headless=new', '--remote-debugging-port=0', `--user-data-dir=${profileDir}`, `--window-size=${width},${height}`,
    '--hide-scrollbars', '--force-device-scale-factor=1', '--force-color-profile=srgb', '--no-first-run',
    '--no-default-browser-check', '--disable-background-timer-throttling', '--disable-renderer-backgrounding',
    '--disable-backgrounding-occluded-windows', '--mute-audio', '--disable-features=Translate,MediaRouter',
    ...(offline ? ['--host-resolver-rules=MAP * ~NOTFOUND , EXCLUDE 127.0.0.1'] : []),
    ...args, 'about:blank',
  ];
  const proc = spawn(CHROME, flags, { stdio: 'ignore' });

  // Chrome writes the port it chose to DevToolsActivePort in the profile directory.
  const portFile = path.join(profileDir, 'DevToolsActivePort');
  let port = null;
  for (let k = 0; k < 200 && !port; k++) {
    try { port = +fs.readFileSync(portFile, 'utf8').split('\n')[0]; } catch { /* not yet */ }
    if (!port) await sleep(50);
  }
  if (!port) { proc.kill('SIGKILL'); throw new Error('Chrome did not report a debugging port'); }

  let targets;
  for (let k = 0; k < 100; k++) {
    try { targets = await (await fetch(`http://127.0.0.1:${port}/json`)).json(); if (targets.some(t => t.type === 'page')) break; } catch { /* not yet */ }
    await sleep(50);
  }
  const cdp = new CDP(targets.find(t => t.type === 'page').webSocketDebuggerUrl);
  await cdp.open();
  await cdp.send('Runtime.enable');
  await cdp.send('Page.enable');
  await cdp.send('Network.enable');

  const close = () => {
    try { proc.kill('SIGKILL'); } catch { /* already gone */ }
    // A killed Chrome can still be flushing its profile; a failed cleanup must not fail the run.
    try { fs.rmSync(profileDir, { recursive: true, force: true, maxRetries: 10, retryDelay: 100 }); } catch { /* leave it */ }
  };

  return { cdp, close, port, pid: proc.pid };
}

export { sleep };
