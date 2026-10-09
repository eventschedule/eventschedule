// The set, the camera and the paper rig.
'use strict';
(() => {
const { clamp, lerp, range, DEG, E, mk, html, vis, css, rgba, rng, $ } = F;
const LIGHT = F.LIGHT;
// The ground line sits at y=790 of 1080: the homepage lays a veil and a pill over the bottom
// quarter of the picture, so everything that matters stands above it.
const FLOOR = 790, WALL_Z = -260, PERSP = 2400;
Object.assign(F, { FLOOR, WALL_Z, PERSP });

/* ---------- palette: what code paints at runtime ---------- */
const T = F.T = LIGHT ? {
  shadow: '24,38,84', shadowA: .26, contactA: .3, wallShadowA: .12,
  add: 'source-over', glowWall: .34, glowFloor: .42,
  shadeDark: '34,70,160', shadeLight: '255,255,255', kDark: .42, kLight: .5,
  dust: '120,150,215', dustA: .2, puff: '150,172,224', puffA: .6,
} : {
  shadow: '0,0,0', shadowA: .6, contactA: .7, wallShadowA: .5,
  add: 'lighter', glowWall: .5, glowFloor: .55,
  shadeDark: '2,4,14', shadeLight: '200,222,255', kDark: .7, kLight: .3,
  dust: '170,205,255', dustA: .34, puff: '160,190,255', puffA: .5,
};

/* ---------- set ---------- */
const world = $('#world'), wall = $('#wall'), wallo = $('#wallo'), flooro = $('#flooro'), props = $('#props');
const cove = mk('div'); cove.id = 'wallcove'; wall.insertBefore(cove, $('#wallfx'));
const nightWall = mk('div'); nightWall.id = 'nightwall'; wall.insertBefore(nightWall, $('#wallfx'));
const beams = mk('div'); beams.id = 'beams'; wall.insertBefore(beams, $('#wallfx'));
const nightFloor = mk('div'); nightFloor.id = 'nightfloor'; $('#floor .pl').appendChild(nightFloor);
// n: 0 day, 1 night (the door and the room). What is lettered on the set takes the night's inks.
F.night = 0;
F.setNight = (n, t = 0) => {
  F.night = n; nightWall.style.opacity = n; nightFloor.style.opacity = n; cove.style.opacity = 1 - n;
  beams.style.opacity = n; beams.style.transform = `rotate(${(4 * Math.sin(t * 2 * Math.PI * 3 / DUR)).toFixed(3)}deg)`;
  $('#stage').classList.toggle('night', n > .5);
};
const dark = () => !LIGHT || F.night > .5;
Object.assign(F, { world, wall, wallo, flooro, props });
const wctx = $('#wallfx').getContext('2d'), fctx = $('#floorfx').getContext('2d'), fx = $('#fx').getContext('2d');
F.fx = fx; F.wctx = wctx; F.fctx = fctx;
// world -> canvas pixels
const fpt = (x, z) => [x + 400, z + 260];
const wpt = (X, Y) => [X + 200, Y + 300];
F.fpt = fpt; F.wpt = wpt;

/* ---------- camera ---------- */
let CAM = { x: 960, y: 520, dz: 0, rx: 0, ry: 0, rz: 0, zoom: 1 };
F.setCam = c => {
  CAM = { x: 960, y: 520, dz: 0, rx: 0, ry: 0, rz: 0, zoom: 1, ...c };
  const k = CAM;
  world.style.transform = `translate3d(960px,520px,0) scale(${k.zoom.toFixed(5)}) rotateZ(${k.rz.toFixed(3)}deg) rotateX(${k.rx.toFixed(3)}deg) rotateY(${k.ry.toFixed(3)}deg) translate3d(${(-k.x).toFixed(2)}px,${(-k.y).toFixed(2)}px,${k.dz.toFixed(2)}px)`;
};
// Where a point of the world lands on the 1920x1080 frame (and its scale there), for what is
// painted in screen space: dust, puffs, confetti.
F.project = (x, y, z) => {
  const c = CAM, X = x - c.x, Y = y - c.y, Z = z + c.dz;
  const cy = Math.cos(c.ry * DEG), sy = Math.sin(c.ry * DEG);
  const X2 = X * cy + Z * sy, Z2 = -X * sy + Z * cy;
  const cx = Math.cos(c.rx * DEG), sx = Math.sin(c.rx * DEG);
  const Y3 = Y * cx - Z2 * sx, Z3 = Y * sx + Z2 * cx;
  const cz = Math.cos(c.rz * DEG), sz = Math.sin(c.rz * DEG), X4 = X2 * cz - Y3 * sz, Y4 = X2 * sz + Y3 * cz;
  const s = PERSP / (PERSP - Z3) * c.zoom;
  return [960 + X4 * s, 520 + Y4 * s, s];
};

F.beginFrame = () => {
  wctx.setTransform(1, 0, 0, 1, 0, 0); fctx.setTransform(1, 0, 0, 1, 0, 0);
  wctx.clearRect(0, 0, 2320, 1090); fctx.clearRect(0, 0, 2720, 1100); fx.clearRect(0, 0, 1920, 1080);
  wctx.filter = 'none'; fctx.filter = 'none';
};

/* ---------- light and shadow ----------
   The key light stands up and to the left, a little behind the props, so shadows fall forward
   and to the right across the floor, where the camera can see them. */
const L = [-.42, -.62, .66];           // toward the light, for shading a sheet's face
const SK = [.4, .56];                  // floor shadow: x and z travelled per unit of height
const S0 = L[2];                       // a sheet facing the camera, at rest

function poly(g, pts) { g.beginPath(); pts.forEach((p, i) => i ? g.lineTo(p[0], p[1]) : g.moveTo(p[0], p[1])); g.closePath(); }
// A soft pool of coloured light on the floor or the wall.
F.floorGlow = (x, z, rx, rz, rgb, a) => {
  if (a <= .003) return;
  const [cx, cy] = fpt(x, z), g = fctx;
  g.save(); g.globalCompositeOperation = dark() ? 'lighter' : 'source-over'; g.translate(cx, cy); g.scale(1, rz / rx);
  const gr = g.createRadialGradient(0, 0, 0, 0, 0, rx);
  gr.addColorStop(0, rgba(rgb, a)); gr.addColorStop(.5, rgba(rgb, a * .35)); gr.addColorStop(1, rgba(rgb, 0));
  g.fillStyle = gr; g.fillRect(-rx, -rx, rx * 2, rx * 2); g.restore();
};
F.wallGlow = (X, Y, r, rgb, a) => {
  if (a <= .003) return;
  const [cx, cy] = wpt(X, Y), g = wctx;
  g.save(); g.globalCompositeOperation = dark() ? 'lighter' : 'source-over';
  const gr = g.createRadialGradient(cx, cy, 0, cx, cy, r);
  gr.addColorStop(0, rgba(rgb, a)); gr.addColorStop(.45, rgba(rgb, a * .4)); gr.addColorStop(1, rgba(rgb, 0));
  g.fillStyle = gr; g.fillRect(cx - r, cy - r, r * 2, r * 2); g.restore();
};
// The shadow an upright card throws: a slanted patch on the floor, a tight line where it
// stands, and a wide soft one on the wall behind. o: {x, z, lift, ry, hw, top, lean, k}
F.cardShadow = o => {
  const k = o.k == null ? 1 : o.k; if (k <= .003) return;
  const lift = o.lift || 0, c = Math.cos((o.ry || 0) * DEG), s = Math.sin((o.ry || 0) * DEG), hw = o.hw;
  const bx = o.x + lift * SK[0], bz = o.z + lift * SK[1];
  const bl = [bx - hw * c, bz + hw * s], br = [bx + hw * c, bz - hw * s];
  const lean = o.lean || 0, ox = lean * s + o.top * SK[0], oz = lean * c + o.top * SK[1];
  const g = fctx;
  g.save();
  const P = [fpt(...bl), fpt(...br), fpt(br[0] + ox, br[1] + oz), fpt(bl[0] + ox, bl[1] + oz)];
  const m0 = fpt(bx, bz), m1 = fpt(bx + ox, bz + oz);
  const gr = g.createLinearGradient(m0[0], m0[1], m1[0], m1[1]);
  const fade = 1 / (1 + lift / 220);
  const SC = dark() ? '0,0,0' : T.shadow, SA = dark() ? .6 : T.shadowA;
  gr.addColorStop(0, rgba(SC, SA * k * fade)); gr.addColorStop(1, rgba(SC, SA * k * fade * .12));
  g.filter = `blur(${(9 + lift * .07).toFixed(1)}px)`; g.fillStyle = gr; poly(g, P); g.fill();
  if (lift < 70) {
    const q = 1 - lift / 70, a = fpt(...bl), b = fpt(...br);
    g.filter = `blur(${(4 + lift * .12).toFixed(1)}px)`; g.strokeStyle = rgba(SC, (dark() ? .7 : T.contactA) * k * q); g.lineWidth = 9; g.lineCap = 'round';
    g.beginPath(); g.moveTo(a[0], a[1] + 3); g.lineTo(b[0], b[1] + 3); g.stroke();
  }
  g.restore();
  // wall
  const gap = o.z - WALL_Z, dx = gap * .2 + 8, dy = gap * .09 + 4, w = Math.max(6, hw * Math.abs(c) * 2), h = o.top;
  const [x0, y0] = wpt(o.x - w / 2 + dx, FLOOR - lift - h + dy);
  const hh = Math.min(h, FLOOR + 300 - y0);
  if (hh > 2) {
    wctx.save(); wctx.filter = `blur(${(gap * .11 + 10 + lift * .05).toFixed(1)}px)`; wctx.fillStyle = rgba(SC, (dark() ? .5 : T.wallShadowA) * k);
    wctx.beginPath(); wctx.roundRect(x0, y0, w, hh, 24); wctx.fill(); wctx.restore();
  }
};

/* ---------- paper ----------
   A sheet that can bend. n strips, each hinged to the one below: joint 0 stands on the floor.
   pose() takes the whole body (place, heading, lean, squash) and one curve along its height,
   bend, given as [low, mid, top] in degrees: how far that third of the sheet curls back (+) or
   forward (-). There is no twist: strips turned against each other step at their edges, so a
   sheet looks around with its whole body (ry) and the curl of its top. */
class Paper {
  constructor(o) {
    const n = this.n = o.n || 20, w = this.w = o.w, h = this.h = o.h, sh = this.sh = h / n, r = o.r == null ? 22 : o.r;
    this.root = mk('div', 'paper ' + (o.cls || ''), o.parent || props);
    this.g = []; this.shF = []; this.shB = []; this.faces = {}; this._s = [];
    let parent = this.root;
    for (let k = 0; k < n; k++) {
      const g = mk('div', 'pg', parent);
      const box = { left: -w / 2 + 'px', top: (-sh - .6) + 'px', width: w + 'px', height: (sh + 1.2) + 'px' };
      const rad = k === n - 1 ? `${r}px ${r}px 0 0` : k === 0 ? `0 0 ${r}px ${r}px` : '';
      const ps = mk('div', 'ps', g); css(ps, box); ps.style.borderRadius = rad;
      for (const key in o.faces) {
        const f = html(ps, o.faces[key]);
        f.style.top = (-(h - (k + 1) * sh) + .6) + 'px';
        (this.faces[key] = this.faces[key] || []).push(f);
      }
      this.shF.push(mk('div', 'sh', ps));
      // a sheet has an edge: one layer of card between the front and the back
      const pe = mk('div', 'pe', g); css(pe, box); pe.style.borderRadius = rad; pe.style.transform = 'translateZ(-2.5px)';
      const pb = mk('div', 'pb', g); css(pb, box); pb.style.borderRadius = rad; pb.style.transform = 'translateZ(-5px) rotateY(180deg)';
      if (o.back) pb.style.background = o.back;
      this.shB.push(mk('div', 'sh', pb));
      this.g.push(g); parent = g;
    }
    // how much of each control a joint carries (joint 0 is the foot: it only leans)
    this.wl = []; this.wm = []; this.wt = [];
    let sl = 0, sm = 0, st = 0;
    for (let k = 0; k < n; k++) {
      const u = k / (n - 1), l = k ? Math.max(0, 1 - 2 * u) : 0, m = k ? 1 - Math.abs(2 * u - 1) : 0, t = Math.max(0, 2 * u - 1);
      this.wl.push(l); this.wm.push(m); this.wt.push(t); sl += l; sm += m; st += t;
    }
    for (let k = 0; k < n; k++) { this.wl[k] /= sl; this.wm[k] /= sm; this.wt[k] /= st; }
    this.cur = null; this.top = h; this.lean = 0;
  }
  // every copy of one node of one face (a face is cut into n strips, so it exists n times)
  each(key, sel, fn) { for (const f of this.faces[key]) { const e = sel ? f.querySelector(sel) : f; if (e) fn(e); } }
  // which face shows: a key, or {key: opacity}
  show(m) {
    if (typeof m === 'string') m = { [m]: 1 };
    const sig = JSON.stringify(m); if (sig === this._show) return; this._show = sig;
    for (const key in this.faces) { const o = m[key] || 0; for (const f of this.faces[key]) { f.style.display = o > 0 ? '' : 'none'; f.style.opacity = o; } }
  }
  set(name, v) { if (this['_v' + name] !== v) { this['_v' + name] = v; this.root.style.setProperty(name, v); } }
  pose(p) {
    const n = this.n, sh = this.sh;
    const x = p.x || 0, lift = p.lift || 0, z = p.z || 0, ry = p.ry || 0, rz = p.rz || 0, rx = p.rx || 0;
    const sx = p.sx == null ? 1 : p.sx, sy = p.sy == null ? 1 : p.sy, s = p.s == null ? 1 : p.s;
    const bend = p.bend || [0, 0, 0];
    const on = p.on !== false; vis(this.root, on); if (!on) return;
    this.root.style.transform = `translate3d(${x.toFixed(2)}px,${(FLOOR - lift).toFixed(2)}px,${z.toFixed(2)}px) rotateY(${ry.toFixed(2)}deg) rotateZ(${rz.toFixed(2)}deg) scale3d(${(sx * s).toFixed(4)},${(sy * s).toFixed(4)},1)`;
    let A = 0, Y = 0, Zo = 0;
    const B = ry;
    const S = this._s;
    for (let k = 0; k < n; k++) {
      const a = k ? bend[0] * this.wl[k] + bend[1] * this.wm[k] + bend[2] * this.wt[k] : rx;
      this.g[k].style.transform = k ? `translateY(${-sh}px) rotateX(${a.toFixed(2)}deg)` : `rotateX(${a.toFixed(2)}deg)`;
      A += a;
      const ca = Math.cos(A * DEG), sa = Math.sin(A * DEG);
      S[k] = L[0] * ca * Math.sin(B * DEG) + L[1] * -sa + L[2] * ca * Math.cos(B * DEG);
      Y += sh * ca * sy * s; Zo -= sh * sa * s;
    }
    // shade each strip from its own slope, blended into its neighbours so the curve is smooth
    const col = v => { const d = v - S0; return d < 0 ? rgba(T.shadeDark, Math.min(.8, -d * T.kDark)) : rgba(T.shadeLight, Math.min(.55, d * T.kLight)); };
    for (let k = 0; k < n; k++) {
      const lo = k ? (S[k - 1] + S[k]) / 2 : S[k], hi = k < n - 1 ? (S[k] + S[k + 1]) / 2 : S[k];
      const f = `linear-gradient(to top,${col(lo)},${col(hi)})`, b = `linear-gradient(to top,${col(-lo - .25)},${col(-hi - .25)})`;
      if (this.shF[k]._c !== f) { this.shF[k]._c = f; this.shF[k].style.background = f; }
      if (this.shB[k]._c !== b) { this.shB[k]._c = b; this.shB[k].style.background = b; }
    }
    this.top = Y; this.lean = Zo;
    this.cur = { x, lift, z, ry, hw: this.w * sx * s / 2, top: Y, lean: Zo };
    if (p.shadow !== 0) F.cardShadow({ ...this.cur, k: p.shadow == null ? 1 : p.shadow });
  }
}
F.Paper = Paper;

/* ---------- screen-space bits ---------- */
// Dust in the light: a few motes that drift on closed loops, so the last frame is the first.
const MOTES = (() => { const r = rng(31), out = []; for (let i = 0; i < 46; i++) out.push({ x: r() * 2300 - 190, y: 60 + r() * 700, z: -200 + r() * 760, ax: 40 + r() * 90, ay: 20 + r() * 60, kx: 1 + Math.floor(r() * 2), ky: 1 + Math.floor(r() * 3), ph: r() * 6.283, s: 1.4 + r() * 3.2, a: .35 + r() * .65 }); return out; })();
F.dust = (t, k = 1) => {
  if (k <= .003 || T.dustA <= 0) return;
  const g = fx, w = 2 * Math.PI / DUR;
  g.save(); g.globalCompositeOperation = T.add;
  for (const m of MOTES) {
    const [sx, sy, s] = F.project(m.x + Math.sin(t * w * m.kx + m.ph) * m.ax, m.y + Math.cos(t * w * m.ky + m.ph * 1.7) * m.ay, m.z);
    const r = m.s * s * (1 + Math.max(0, m.z) / 260), a = T.dustA * m.a * k * (.55 + .45 * Math.sin(t * w * 3 + m.ph * 2.3)) / (1 + Math.max(0, m.z) / 300);
    const gr = g.createRadialGradient(sx, sy, 0, sx, sy, r * 2.2);
    gr.addColorStop(0, rgba(T.dust, a)); gr.addColorStop(1, rgba(T.dust, 0));
    g.fillStyle = gr; g.beginPath(); g.arc(sx, sy, r * 2.2, 0, 6.2832); g.fill();
  }
  g.restore();
};
// A puff of dust where something lands: soft balls that roll out along the floor and thin away.
F.puff = (t, t0, x, z, n = 9, seed = 1, power = 1) => {
  const dt = t - t0; if (dt <= 0 || dt > .9) return;
  const r = rng(seed * 77 + 5), g = fx;
  g.save();
  for (let i = 0; i < n; i++) {
    const side = i % 2 ? 1 : -1, sp = (90 + r() * 170) * power, life = .45 + r() * .4, up = 14 + r() * 46, size = 12 + r() * 20, back = (r() - .5) * 120;
    if (dt > life) continue;
    const u = dt / life, d = sp * (1 - Math.exp(-5 * u)) / 1.0;
    const [sx, sy, s] = F.project(x + side * (60 + d), F.FLOOR - up * Math.sin(Math.PI * Math.min(1, u * .9)) * .6 - 4, z + back * u);
    const rr = size * s * (.5 + .9 * E.out(u)), a = T.puffA * (1 - u) * (1 - u) * .8;
    const gr = g.createRadialGradient(sx, sy, 0, sx, sy, rr);
    gr.addColorStop(0, rgba(T.puff, a)); gr.addColorStop(.6, rgba(T.puff, a * .55)); gr.addColorStop(1, rgba(T.puff, 0));
    g.fillStyle = gr; g.beginPath(); g.arc(sx, sy, rr, 0, 6.2832); g.fill();
  }
  g.restore();
};

/* ---------- grain: just enough to stop the wide gradients banding in the encode ---------- */
const grc = $('#grain').getContext('2d');
const grainFrames = (() => {
  const out = [], r = rng(42);
  for (let f = 0; f < 4; f++) {
    const id = grc.createImageData(960, 540), d = id.data;
    for (let i = 0; i < d.length; i += 4) { const v = r() * 255; d[i] = d[i + 1] = d[i + 2] = v; d[i + 3] = 255; }
    out.push(id);
  }
  return out;
})();
F.grain = t => grc.putImageData(grainFrames[Math.floor(t * 30) % 4], 0, 0);
})();
