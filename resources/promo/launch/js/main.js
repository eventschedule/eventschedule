// seek(t), and the preview player.
'use strict';
(() => {
const { clamp, $ } = F;
function seek(t) {
  t = clamp(t, 0, DUR - 1e-4);
  F.beginFrame();
  if (F.Q.has('thumb')) return F.thumb();
  return F.film(t);
}
// What the renderer calls: draw the frame, wait until every image it shows is decoded, draw again.
// Images are a cache, not state, so the frame is still a function of t alone.
async function seekAsync(t) { seek(t); await F.decodeAll(); seek(t); }
window.seek = seek; window.seekAsync = seekAsync;
window.DUR = DUR; window.CUTS = TL.CUTS; window.FAST = F.FAST;

const frame = $('#frame'), stage = $('#stage'), Q = F.Q;
function fit() { const sc = Q.has('scale') && F.RENDER ? +Q.get('scale') : frame.clientWidth / F.W; stage.style.transform = `scale(${sc})`; }
new ResizeObserver(fit).observe(frame); fit();
const ready = (async () => {
  await Promise.all(['800 100px RHD', '400 100px RHD', '700 100px RHD', '500 40px SG', '700 100px InterV'].map(f => document.fonts.load(f).catch(() => {})));
  await document.fonts.ready;
})();
window.ready = ready.then(async () => { await seekAsync(Q.has('t') ? +Q.get('t') : 0); window.__ready = true; });
if (F.RENDER) { document.body.classList.add('render'); return; }

/* ---------- preview ----------
   Space plays and pauses, the arrows step a frame (Shift: a beat), [ and ] jump a shot.
   ?t=32 opens paused on a frame; ?from=24&to=32 loops a stretch. With a score chosen the picture
   follows the audio clock. */
const playBtn = $('#play'), playIco = $('#playIco'), scrub = $('#scrub'), tc = $('#tc'), shotid = $('#shotid'), scoreSel = $('#score');
scrub.max = DUR;
const ICON_PLAY = '<path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5z"/>';
const ICON_PAUSE = '<rect x="6" y="5" width="4" height="14" rx="1.2"/><rect x="14" y="5" width="4" height="14" rx="1.2"/>';
const from = Q.has('from') ? +Q.get('from') : 0, to = Q.has('to') ? +Q.get('to') : DUR;
let playing = false, cur = Q.has('t') ? +Q.get('t') : from, base = 0;
const audio = () => (window.A && A.Player && scoreSel.value) ? A.Player : null;
function setPlaying(p) {
  playing = p; playIco.innerHTML = p ? ICON_PAUSE : ICON_PLAY; playBtn.setAttribute('aria-label', p ? 'Pause' : 'Play');
  if (p) { if (cur >= to - .01) cur = from; base = performance.now() - (cur - from) * 1000; const a = audio(); if (a) a.play(cur); } else { const a = audio(); if (a) a.stop(); }
}
function ui(s) { scrub.value = cur; tc.textContent = `${cur.toFixed(2).padStart(5, '0')} / ${DUR.toFixed(2)}`; if (s) shotid.textContent = s.id; }
playBtn.onclick = () => setPlaying(!playing);
scrub.oninput = () => { cur = +scrub.value; if (playing) setPlaying(true); ui(seek(cur)); };
scoreSel.onchange = async () => { const was = playing; setPlaying(false); if (window.A && A.Player && scoreSel.value) await A.Player.load(scoreSel.value); if (was) setPlaying(true); };
addEventListener('keydown', e => {
  if (e.code === 'Space') { e.preventDefault(); setPlaying(!playing); }
  const step = d => { setPlaying(false); cur = clamp(cur + d, 0, DUR - 1e-3); ui(seek(cur)); };
  if (e.code === 'ArrowRight') step(e.shiftKey ? .5 : 1 / 60);
  if (e.code === 'ArrowLeft') step(e.shiftKey ? -.5 : -1 / 60);
  if (e.code === 'BracketRight') { const s = TL.shotAt(cur); step(s.b - cur + 1e-3); }
  if (e.code === 'BracketLeft') { const s = TL.shotAt(cur), p = TL.SHOTS[Math.max(0, s.i - (cur - s.a < .2 ? 1 : 0))]; step(p.a - cur); }
});
window.ready.then(() => {
  setPlaying(false); ui(seek(cur));
  const tick = now => {
    if (playing) {
      const a = audio(); cur = a ? a.time() : from + (now - base) / 1000;
      if (cur >= to) { cur = from; base = now; if (a) a.play(cur); }
      ui(seek(cur));
    }
    requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
});
})();
