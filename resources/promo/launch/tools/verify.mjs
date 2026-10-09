// Checks that need no eyes and no ears. Run it before every render that someone will watch.
//
//   node tools/verify.mjs                 the cut, the captions and the copy
//   node tools/verify.mjs --srt=FILE      also write the captions as an SRT
//   node tools/verify.mjs --flash=FILE    also measure a rendered film for flashes (needs ffmpeg)
//
// What it holds: every cut on a quarter second; a line is on screen 0.3 s a word at least, in
// two lines of 30 characters at most; no em-dash, no "self-host", no "keep 100%", no "free
// ticket", no "no fees", no plan price in anything the film or its upload text says.
import { spawnSync } from 'node:child_process';
import vm from 'node:vm';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url)), root = path.join(here, '..');
const A = Object.fromEntries(process.argv.slice(2).map(a => { const i = a.indexOf('='); return i < 0 ? [a.replace(/^--/, ''), true] : [a.slice(2, i), a.slice(i + 1)]; }));
// The timeline is a classic script for the page (the repo's package.json makes .js a module here), so run it as one.
const win = {}; vm.runInNewContext(fs.readFileSync(path.join(root, 'js/timeline.js'), 'utf8'), { window: win });
const TL = win.TL;
const bad = [], ok = [];
const fail = m => bad.push(m), pass = m => ok.push(m);

/* ---------- the cut ---------- */
let grid = true;
TL.SHOTS.forEach((s, i) => {
  if (Math.abs(s.a * 4 - Math.round(s.a * 4)) > 1e-9) { grid = false; fail(`${s.id} starts at ${s.a}, off the quarter-second grid`); }
  if (i && s.a <= TL.SHOTS[i - 1].a) fail(`${s.id} does not start after ${TL.SHOTS[i - 1].id}`);
});
if (grid) pass(`${TL.SHOTS.length} shots, every cut on a quarter second, ${TL.DUR} s`);
if (TL.ARRIVALS.length !== TL.ROOM.in) fail(`${TL.ARRIVALS.length} arrivals, not ${TL.ROOM.in}`);
if (Math.abs(TL.ARRIVALS[TL.ARRIVALS.length - 1] - TL.ROOM.peak) > 1e-6) fail(`the last arrival lands at ${TL.ARRIVALS.at(-1)}, not on ${TL.ROOM.peak}`);
else pass(`${TL.ROOM.in} arrivals, the last on ${TL.ROOM.peak}`);
if (TL.CUES.some((c, i) => i && c.t < TL.CUES[i - 1].t)) fail('cues are not in time order');
if (TL.CUES.some(c => c.t < 0 || c.t >= TL.DUR)) fail('a cue lies outside the film');
pass(`${TL.CUES.length} sound cues`);

/* ---------- the captions ---------- */
let i = 0, caps = 0;
while (i < TL.SHOTS.length) {
  const s = TL.SHOTS[i];
  if (!s.cap) { i++; continue; }
  let j = i; while (TL.SHOTS[j + 1] && TL.SHOTS[j + 1].cap === s.cap) j++;
  const lines = [].concat(s.cap), text = lines.join(' '), words = text.split(/\s+/).length, on = TL.SHOTS[j].b - TL.capStart(s), need = words * .3;
  caps++;
  if (on + .05 < need) fail(`"${text}" is on for ${on.toFixed(2)} s; ${words} words want ${need.toFixed(1)} s`);
  if (lines.length > 2) fail(`"${text}" is ${lines.length} lines`);
  if (!s.big) lines.forEach(l => { if (l.length > 30) fail(`"${l}" is ${l.length} characters (30 at most)`); });
  i = j + 1;
}
pass(`${caps} caption lines, each on screen 0.3 s a word or more`);

/* ---------- the copy: everything the film says, and the upload text ---------- */
const BANNED = [
  [/—|–/, 'an em or en dash'], [/self-host(?!ing)/i, '"self-host"'], [/keep(s|ing)? (100|every)/i, '"keep 100%"'],
  [/free tickets?\b/i, '"free ticket"'], [/\bno fees\b/i, '"no fees" (say platform fees)'], [/\b0% fees\b/i, '"0% fees" (say platform fees)'],
  [/[$€£]\s?\d/, 'a price'], [/\brole\b/i, '"role" (say schedule)'], [/apple wallet|zapier|wordpress plugin/i, 'a claim the product does not make'],
];
const said = [];
TL.SHOTS.forEach(s => { if (s.cap) said.push([s.id, [].concat(s.cap).join(' ')]); if (s.say) said.push([s.id, s.say]); });
TL.MORE.forEach(n => said.push(['s37', n]));
// the type the shots build for themselves
for (const f of ['js/film.js', 'js/type.js', 'js/drawn.js']) {
  const src = fs.readFileSync(path.join(root, f), 'utf8');
  for (const m of src.matchAll(/>([A-Z0-9][^<>`$]{3,})</g)) said.push([f, m[1]]);
  for (const m of src.matchAll(/maskLine\('([^']+)'/g)) said.push([f, m[1].replace(/<[^>]+>/g, '')]);
}
for (const f of [A.text, path.join(root, 'out/youtube.txt')].filter(Boolean)) if (fs.existsSync(f)) said.push([path.basename(f), fs.readFileSync(f, 'utf8')]);
let clean = true;
for (const [where, text] of said) for (const [re, what] of BANNED) if (re.test(text)) { clean = false; fail(`${where}: ${what} in "${text.slice(0, 70)}"`); }
if (clean) pass(`${said.length} strings, none banned`);

if (A.srt) { fs.mkdirSync(path.dirname(path.resolve(A.srt)), { recursive: true }); fs.writeFileSync(A.srt, TL.srt()); pass(`captions written to ${A.srt}`); }

/* ---------- flashes: no more than three large opposing jumps of brightness in any second ---------- */
if (A.flash) {
  const r = spawnSync('/opt/homebrew/bin/ffmpeg', ['-hide_banner', '-i', A.flash, '-vf', 'scale=160:90,signalstats,metadata=mode=print:key=lavfi.signalstats.YAVG:file=-', '-f', 'null', '-'], { encoding: 'utf8', maxBuffer: 1 << 28 });
  const y = [...r.stdout.matchAll(/YAVG=([\d.]+)/g)].map(m => +m[1] / 255), tt = [...r.stdout.matchAll(/pts_time:([\d.]+)/g)].map(m => +m[1]);
  if (!y.length) fail('could not read the film for flashes');
  else {
    // a jump is a change of a tenth of full brightness between neighbouring frames; a flash is two
    // opposing jumps. The general flash threshold allows three flashes in any one second.
    const jumps = []; for (let k = 1; k < y.length; k++) { const d = y[k] - y[k - 1]; if (Math.abs(d) >= .1) jumps.push([tt[k], Math.sign(d)]); }
    let worst = 0, at = 0;
    for (let a = 0; a < jumps.length; a++) { let n = 0, last = jumps[a][1]; for (let b = a + 1; b < jumps.length && jumps[b][0] - jumps[a][0] <= 1; b++) if (jumps[b][1] !== last) { n++; last = jumps[b][1]; } const flashes = Math.floor((n + 1) / 2); if (flashes > worst) { worst = flashes; at = jumps[a][0]; } }
    (worst > 3 ? fail : pass)(`flashes: at most ${worst} in any second${worst ? ` (near ${at.toFixed(2)} s)` : ''}, ${jumps.length} large jumps in all`);
  }
}

ok.forEach(m => console.log('  ok   ' + m));
bad.forEach(m => console.log('  FAIL ' + m));
process.exit(bad.length ? 1 : 0);
