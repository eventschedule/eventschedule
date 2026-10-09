// The timeline and the preview player.
'use strict';
(() => {
const { clamp, $ } = F;
const W = F.W;
function seek(t) {
  t = clamp(t, 0, DUR - 1e-4);
  F.beginFrame();
  F.film(t);
  F.dust(t, 1);
  F.grain(t);
}
window.seek = seek;
window.DUR = DUR;
// Windows fast enough that render.mjs spends extra motion-blur samples on them.
window.FAST = F.FAST;
// Hard cuts: render.mjs keeps a frame's motion-blur samples on its own side of each.
window.CUTS = F.CUTS;
window.THEME = F.LIGHT ? 'light' : 'dark';

/* ---------- player ---------- */
const frame = $('#frame'), stage = $('#stage'), Q = F.Q;
function fit() { const sc = Q.has('scale') ? +Q.get('scale') : frame.clientWidth / W; stage.style.transform = `scale(${sc})`; }
new ResizeObserver(fit).observe(frame); fit();
const ready = (async () => {
  await document.fonts.load('880 100px RHD'); await document.fonts.load('400 100px RHD'); await document.fonts.load('700 100px InterV');
  for (let i = 0; i < 10; i++) await document.fonts.load(`400 40px P${i}`).catch(() => {});
  await document.fonts.ready;
  await Promise.all([...document.images].map(i => i.decode().catch(() => {})));
})();
window.ready = ready.then(() => { window.__ready = true; });
if (F.RENDER) { document.body.classList.add('render'); ready.then(() => seek(Q.has('t') ? +Q.get('t') : 0)); return; }

const playBtn = $('#play'), playIco = $('#playIco'), scrub = $('#scrub'), tc = $('#tc'), loopBtn = $('#loop'), themeBtn = $('#theme');
scrub.max = DUR;
function switchTheme() { const q = new URLSearchParams(location.search); q.set('theme', F.LIGHT ? 'dark' : 'light'); q.set('t', cur.toFixed(3)); location.search = q.toString(); }
themeBtn.onclick = switchTheme;
const ICON_PLAY = '<path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5z"/>';
const ICON_PAUSE = '<rect x="6" y="5" width="4" height="14" rx="1.2"/><rect x="14" y="5" width="4" height="14" rx="1.2"/>';
const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
const from = Q.has('from') ? +Q.get('from') : 0, to = Q.has('to') ? +Q.get('to') : DUR;
let playing = !reduce && !Q.has('t'), loop = true, cur = Q.has('t') ? +Q.get('t') : from, base = 0;
function setPlaying(p) { playing = p; playIco.innerHTML = p ? ICON_PAUSE : ICON_PLAY; playBtn.setAttribute('aria-label', p ? 'Pause' : 'Play'); if (p) { if (cur >= to - .01) cur = from; base = performance.now() - (cur - from) * 1000; } }
function ui() { scrub.value = cur; tc.textContent = `${cur.toFixed(1).padStart(4, '0')} / ${DUR.toFixed(1)}`; }
playBtn.onclick = () => setPlaying(!playing);
loopBtn.onclick = () => { loop = !loop; loopBtn.setAttribute('aria-pressed', loop); };
scrub.oninput = () => { cur = +scrub.value; if (playing) base = performance.now() - (cur - from) * 1000; seek(cur); ui(); };
addEventListener('keydown', e => {
  if (e.code === 'Space') { e.preventDefault(); setPlaying(!playing); }
  if (e.code === 'KeyT') switchTheme();
  if (e.code === 'ArrowRight') { cur = Math.min(DUR, cur + 1 / 60 * (e.shiftKey ? 30 : 1)); seek(cur); ui(); }
  if (e.code === 'ArrowLeft') { cur = Math.max(0, cur - 1 / 60 * (e.shiftKey ? 30 : 1)); seek(cur); ui(); }
});
ready.then(() => {
  setPlaying(playing); seek(cur); ui();
  const tick = now => { if (playing) { cur = from + (now - base) / 1000; if (cur >= to) { if (loop) { cur = from; base = now; } else { cur = to - 1e-3; setPlaying(false); } } seek(cur); ui(); } requestAnimationFrame(tick); };
  requestAnimationFrame(tick);
});
})();
