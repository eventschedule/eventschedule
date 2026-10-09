// What is drawn for the film rather than photographed from the app: the tap, the lights of the
// room, the shockwave, the streak.
'use strict';
(() => {
const { clamp, lerp, range, E, spring, rgba, rng, hash, qbez } = F;
const C = F.C, FLOOR = F.FLOOR;

/* ---------- a tap: never an arrow cursor ----------
   A dot that lands, presses on the beat, and a ring that leaves. `at` is a world point. */
F.tap = (t, t0, at, o = {}) => {
  const d = t - t0; if (d < -.22 || d > .7) return;
  const g = F.fx, [x, y, s] = F.project(at[0], at[1], at[2] || 0), rgb = o.rgb || C.white, k = (o.k || 1) * s;
  g.save(); g.globalCompositeOperation = 'lighter';
  const inn = E.out(range(d, -.22, 0)), press = d >= 0 ? 1 - .28 * Math.exp(-d / .05) : 1, gone = 1 - range(d, .28, .55);
  const r = 15 * k * lerp(1.7, 1, inn) * press;
  g.fillStyle = rgba(rgb, .9 * inn * gone); g.beginPath(); g.arc(x, y, r, 0, 6.2832); g.fill();
  if (d >= 0) { const u = clamp(d / .6); g.strokeStyle = rgba(rgb, .7 * (1 - u) * (1 - u)); g.lineWidth = 3.4 * k * (1 - u * .6); g.beginPath(); g.arc(x, y, (18 + 78 * E.out(u)) * k, 0, 6.2832); g.stroke(); }
  g.restore();
};

/* ---------- a ring of light crossing the floor ---------- */
F.shock = (t, t0, at, o = {}) => {
  const d = t - t0, life = o.life || 1.1; if (d <= 0 || d >= life) return;
  const g = F.bg, u = d / life, R = (o.r0 || 80) + (o.r1 || 2400) * E.out(u), a = (o.a || .5) * (1 - u) * (1 - u), n = 72;
  g.save(); g.globalCompositeOperation = 'lighter'; g.strokeStyle = rgba(o.rgb || C.sky, a); g.lineWidth = lerp(7, 1.5, u);
  g.beginPath();
  for (let i = 0; i <= n; i++) { const q = i / n * 6.2832, p = F.project(at[0] + Math.cos(q) * R, FLOOR, at[2] + Math.sin(q) * R); i ? g.lineTo(p[0], p[1]) : g.moveTo(p[0], p[1]); }
  g.stroke(); g.restore();
};

/* ---------- one anamorphic streak, across the frame ---------- */
F.streak = (x, y, a, o = {}) => {
  if (a <= .003) return;
  const g = F.fx, w = o.w || 1500, h = o.h || 5, rgb = o.rgb || '170,210,255';
  g.save(); g.globalCompositeOperation = 'lighter';
  let gr = g.createLinearGradient(x - w, 0, x + w, 0);
  gr.addColorStop(0, rgba(rgb, 0)); gr.addColorStop(.42, rgba(rgb, a * .35)); gr.addColorStop(.5, rgba('255,255,255', a)); gr.addColorStop(.58, rgba(rgb, a * .35)); gr.addColorStop(1, rgba(rgb, 0));
  g.fillStyle = gr; g.fillRect(x - w, y - h / 2, w * 2, h);
  gr = g.createRadialGradient(x, y, 0, x, y, 190); gr.addColorStop(0, rgba('255,255,255', a * .55)); gr.addColorStop(.3, rgba(rgb, a * .22)); gr.addColorStop(1, rgba(rgb, 0));
  g.fillStyle = gr; g.fillRect(x - 190, y - 190, 380, 380);
  g.restore();
};

/* ---------- the room ----------
   The seat plan lies on the floor. Each of the 142 who arrive is a warm light on their own seat:
   a disc on the plan, a small light lifted off it, and that light's reflection. They come from
   the door as sparks. F.room.draw(t, o) is used by the set piece and by everything after it,
   which never leaves the room: o.defocus turns the lights into soft discs for type to sit on. */
const SEATS = F.SEATS = (() => {
  // The real plan comes from the app (plates/seats.js, written from the capture). Until it is
  // there, a plan of the same shape: a stage, tables in front, a floor, a balcony. 150 seats.
  const D = window.PDATA && PDATA.seats;
  if (D && D.seats && D.seats.length) return { w: D.image[0], h: D.image[1], stage: { x: D.stage.x, y: D.stage.y, w: D.stage.width, h: D.stage.height },
    seats: D.seats.map(s => ({ x: s.cx, y: s.cy, r: s.r, section: s.section, label: s.row ? s.row + s.number : s.name })) };
  const seats = [], row = 'ABCDEF';
  for (let tb = 0; tb < 5; tb++) for (let k = 0; k < 6; k++) { const a = k / 6 * 6.2832 + .52; seats.push({ x: 240 + tb * 280 + Math.cos(a) * 56, y: 300 + Math.sin(a) * 56, r: 17, section: 'Front tables', label: `T${tb + 1}-${k + 1}` }); }
  for (let r = 0; r < 6; r++) for (let k = 0; k < 15; k++) seats.push({ x: 164 + k * 86 + (k > 6 ? 68 : 0), y: 470 + r * 58, r: 17, section: 'Main floor', label: row[r] + (k + 1) });
  for (let r = 0; r < 2; r++) for (let k = 0; k < 15; k++) seats.push({ x: 164 + k * 86 + (k > 6 ? 68 : 0), y: 880 + r * 58, r: 17, section: 'Balcony', label: 'B' + row[r] + (k + 1) });
  return { w: 1600, h: 1020, stage: { x: 470, y: 70, w: 660, h: 110 }, seats, synthetic: true };
})();
const K = SEATS.synthetic ? 1.15 : .96;                  // world px per plan px
const PX = x => 960 + (x - SEATS.w / 2) * K, PZ = y => (y - SEATS.h / 2) * K;
const room = F.room = { K, PX, PZ, w: SEATS.w * K, h: SEATS.h * K };
// who sits where: the buyer's two seats first, eight who never come, and the rest in an order
// that fills the room from all over rather than row by row
(() => {
  const S = SEATS.seats, by = l => S.findIndex(s => s.label === l);
  let c7 = by('C7'), c8 = by('C8'); if (c7 < 0 || c8 < 0) { c7 = Math.floor(S.length / 2); c8 = c7 + 1; }
  const noshow = new Set(); const R = rng(142);
  while (noshow.size < S.length - TL.ROOM.in) { const i = Math.floor(R() * S.length); if (i !== c7 && i !== c8) noshow.add(i); }
  const rest = S.map((_, i) => i).filter(i => i !== c7 && i !== c8 && !noshow.has(i)).map(i => [hash(i + 7), i]).sort((a, b) => a[0] - b[0]).map(v => v[1]);
  const order = [c7, c8, ...rest];
  S.forEach(s => { s.t = Infinity; });
  order.forEach((i, n) => { S[i].t = TL.ARRIVALS[n]; S[i].n = n; });
  room.c7 = S[c7]; room.c8 = S[c8];
  room.door = [PX(SEATS.w + 260), PZ(SEATS.h + 120)];     // where the tickets are scanned
})();
room.draw = (t, o = {}) => {
  const g = F.fx, cam = F.cam(), df = o.defocus || 0, k = o.k == null ? 1 : o.k, flight = .42;
  const sinp = Math.max(.08, Math.sin(cam.pitch * F.DEG));   // how flat the floor lies to the lens
  g.save();
  for (const s of SEATS.seats) {
    if (o.max != null && !(s.n < o.max)) continue;
    if (o.every && s.n % o.every) continue;                 // out of focus, a third of the lights say it all
    const x = PX(s.x), z = PZ(s.y), d = t - s.t;
    // on its way from the door
    if (!df && d < 0 && d > -flight) {
      const u = E.io(1 + d / flight), a = room.door, mx = (a[0] + x) / 2, mz = (a[1] + z) / 2, lift = 260 * Math.sin(Math.PI * u);
      const wx = lerp(lerp(a[0], mx, u), lerp(mx, x, u), u), wz = lerp(lerp(a[1], mz, u), lerp(mz, z, u), u);
      const [sx, sy, sc] = F.project(wx, FLOOR - 30 - lift, wz), r = 7 * sc;
      g.globalCompositeOperation = 'lighter';
      const gr = g.createRadialGradient(sx, sy, 0, sx, sy, r * 5); gr.addColorStop(0, rgba(C.hot, .95 * k)); gr.addColorStop(.25, rgba(C.warm, .5 * k)); gr.addColorStop(1, rgba(C.warm, 0));
      g.fillStyle = gr; g.beginPath(); g.arc(sx, sy, r * 5, 0, 6.2832); g.fill();
      continue;
    }
    if (d < 0) continue;
    const pop = d < .5 && !o.calm ? 1 + .9 * Math.exp(-d / .09) : 1, tw = 1 + .09 * Math.sin(t * (1.7 + 1.3 * hash(s.n + 21)) + s.n * 1.7);   // lands hot, then breathes
    // no two lights in a room are the same lamp: each has its own warmth and its own size
    const hu = hash(s.n + 5), WC = hu < .28 ? '255,160,78' : hu < .8 ? C.warm : '255,216,164', sz = .86 + .3 * hash(s.n + 9);
    const lift = (o.lift == null ? 34 : o.lift) * E.out(clamp(d / .35));
    const [fx_, fy_, fs] = F.project(x, FLOOR, z);
    if (fx_ < -200 || fx_ > 2120 || fy_ < -200 || fy_ > 1280) continue;
    g.globalCompositeOperation = 'lighter';
    if (df < .6) {
      // the seat itself, lit: a disc lying on the plan
      const rr = s.r * K * fs * 1.08, a = (1 - df / .6) * k;
      g.save(); g.translate(fx_, fy_); g.scale(1, sinp);
      let gr = g.createRadialGradient(0, 0, 0, 0, 0, rr * 2.6); gr.addColorStop(0, rgba(C.warm, .5 * a)); gr.addColorStop(.38, rgba(C.warm, .24 * a)); gr.addColorStop(1, rgba(C.warm, 0));
      g.fillStyle = gr; g.beginPath(); g.arc(0, 0, rr * 2.6, 0, 6.2832); g.fill();
      g.fillStyle = rgba(C.hot, .78 * a); g.beginPath(); g.arc(0, 0, rr * .92, 0, 6.2832); g.fill();
      g.restore();
    }
    // the light, lifted off the seat, and its reflection in the floor
    const [ox, oy, os] = F.project(x, FLOOR - lift, z), R0 = s.r * K * os * sz * (1 + df * (2.1 + 1.6 * hash(s.n + 3))) * pop * tw, a0 = k / (1 + df * 4.4) * (df ? .55 + .9 * hash(s.n + 11) : 1);
    if (sinp < .97 || df) {
      if (!df) { const [rx, ry] = F.project(x, FLOOR + lift * 1.6, z); const gr = g.createRadialGradient(rx, ry, 0, rx, ry, R0 * 2.2); gr.addColorStop(0, rgba(WC, .26 * a0)); gr.addColorStop(1, rgba(WC, 0)); g.fillStyle = gr; g.save(); g.translate(rx, ry); g.scale(1, 1.9); g.beginPath(); g.arc(0, 0, R0 * 2.2, 0, 6.2832); g.fill(); g.restore(); }
      const gr = g.createRadialGradient(ox, oy, 0, ox, oy, R0 * (df ? 1.25 : 3.1));
      if (df) { gr.addColorStop(0, rgba(WC, .3 * a0)); gr.addColorStop(.8, rgba(WC, .3 * a0)); gr.addColorStop(.93, rgba(C.hot, .5 * a0)); gr.addColorStop(1, rgba(WC, 0)); }
      else { gr.addColorStop(0, rgba('255,244,226', .98 * a0)); gr.addColorStop(.16, rgba(C.hot, .82 * a0)); gr.addColorStop(.4, rgba(WC, .3 * a0)); gr.addColorStop(1, rgba(WC, 0)); }
      g.fillStyle = gr; g.beginPath(); g.arc(ox, oy, R0 * (df ? 1.25 : 3.1), 0, 6.2832); g.fill();
    }
    // the ring it lands with
    if (!df && !o.calm && d < .45) { const u = d / .45; g.strokeStyle = rgba(C.hot, .7 * (1 - u) * (1 - u) * k); g.lineWidth = 2.6 * fs; g.save(); g.translate(fx_, fy_); g.scale(1, sinp); g.beginPath(); g.arc(0, 0, s.r * K * fs * (1.2 + 3.4 * E.out(u)), 0, 6.2832); g.stroke(); g.restore(); }
  }
  g.restore();
};
})();
