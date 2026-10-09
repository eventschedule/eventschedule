// Type in screen space: the line each shot carries, the hook's large lines, and the cards.
'use strict';
(() => {
const { clamp, lerp, range, E, mk, html, vis, $ } = F;
const hud = $('#hud');
const esc = s => String(s).replace(/[&<>]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
const lines = v => [].concat(v);
// A line that rises out of a mask: 0.3 s, starting two frames after the cut.
const rise = (t, at, d = .3) => E.rise(range(t, at, at + d));

/* ---------- the caption ---------- */
const capscrim = mk('div', 'capscrim', hud), cap = mk('div', 'cap', hud);
const capBody = mk('div', null, cap);
const bigscrim = mk('div', 'bigscrim', hud), big = mk('div', 'big', hud);
const setLines = (host, v) => {
  const sig = lines(v).join('\n'); if (host._sig === sig) return host._spans;
  host._sig = sig; host.innerHTML = lines(v).map(l => `<span class="mask"><span>${l}</span></span>`).join('');
  return host._spans = [...host.querySelectorAll('.mask>span')];
};
// The counting line of the room: the number itself is the caption.
const countLine = n => `<b>${n}</b> of ${TL.ROOM.total} through the door`;

F.caption = (t, shot) => {
  let text = shot.cap, at = text ? TL.capStart(shot) : 0, warm = false;
  if (shot.count) { const n = TL.through(t); text = countLine(Math.max(2, n)); at = shot.a + 2 / 60; warm = true; }
  const isBig = !!shot.big && !!text, isCap = !!text && !isBig;
  vis(cap, isCap); vis(capscrim, isCap); vis(big, isBig); vis(bigscrim, isBig);
  if (isCap) {
    cap.classList.toggle('warm', warm);
    if (shot.count) { if (capBody._sig !== text) { capBody._sig = text; capBody.innerHTML = `<span class="mask"><span>${text}</span></span>`; capBody._spans = [capBody.querySelector('.mask>span')]; } }
    else setLines(capBody, lines(text).map(esc));
    const spans = capBody._spans;
    spans.forEach((s, i) => { s.style.transform = `translateY(${((1 - rise(t, at + i * .07)) * 112).toFixed(2)}%)`; });
    const o = clamp((t - at) / .1); cap.style.opacity = o; capscrim.style.opacity = o * (shot.scrim == null ? 1 : shot.scrim);
  }
  if (isBig) {
    const spans = setLines(big, lines(text).map(esc));
    spans.forEach((s, i) => { s.style.transform = `translateY(${((1 - rise(t, at + i * .07, .34)) * 112).toFixed(2)}%)`; });
    const o = clamp((t - at) / .1); big.style.opacity = o; bigscrim.style.opacity = o;
  }
};
F.esc = esc; F.rise = rise;
})();
