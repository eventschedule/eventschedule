// The stage: the camera, the floor, the light, and the glass slab.
// Everything is drawn from t alone. Nothing here remembers the frame before.
'use strict';
(() => {
const { clamp, lerp, range, DEG, E, mk, vis, rgba, rng, $ } = F;
// The render scale: the page is laid out at 1920x1080 and the stage is scaled to the frame, so
// every canvas carries that many more pixels and code keeps drawing in 1920x1080.
const S = F.S = F.RENDER ? +(F.Q.get('scale') || 1) : Math.min(2, window.devicePixelRatio || 1);
const FLOOR = F.FLOOR = 800;        // the floor, in world y (down is positive)
const scene = $('#scene'), world = $('#world');
F.world = world;

/* ---------- palette: what code paints at runtime ---------- */
const C = F.C = {
  void: '3,5,11', navy: '13,22,48', key: '78,129,250', cyan: '34,211,238', sky: '56,189,248',
  rim: '191,216,255', warm: '255,180,94', hot: '255,226,184', ok: '52,211,153', white: '244,247,255',
};

/* ---------- canvases ---------- */
const mkCtx = id => { const c = $(id); c.width = Math.round(1920 * S); c.height = Math.round(1080 * S); return c.getContext('2d'); };
const bg = F.bg = mkCtx('#bg'), fx = F.fx = mkCtx('#fx');
F.beginFrame = () => {
  for (const g of [bg, fx]) { g.setTransform(S, 0, 0, S, 0, 0); g.globalCompositeOperation = 'source-over'; g.globalAlpha = 1; g.filter = 'none'; g.clearRect(0, 0, 1920, 1080); }
};

/* ---------- camera ----------
   x, y, z: the point looked at, which lands on the centre of the frame at scale `zoom`.
   yaw: the camera orbits to the right (degrees); pitch: it looks down; roll: it banks.
   P: the lens, as a CSS perspective. 2600 is long (product), 1100 wide (the opening, the room). */
let CAM = { x: 960, y: 540, z: 0, yaw: 0, pitch: 0, roll: 0, zoom: 1, P: 2600 };
F.setCam = c => {
  const k = CAM = { x: 960, y: 540, z: 0, yaw: 0, pitch: 0, roll: 0, zoom: 1, P: 2600, ...c };
  scene.style.perspective = k.P.toFixed(1) + 'px';
  world.style.transform = `translate3d(960px,540px,0) scale(${k.zoom.toFixed(5)}) rotateZ(${k.roll.toFixed(3)}deg) rotateX(${(-k.pitch).toFixed(3)}deg) rotateY(${(-k.yaw).toFixed(3)}deg) translate3d(${(-k.x).toFixed(2)}px,${(-k.y).toFixed(2)}px,${(-k.z).toFixed(2)}px)`;
};
F.cam = () => CAM;
// Where a world point lands on the 1920x1080 frame: [x, y, scale there, depth toward the lens].
// The same sums the browser does for #world, so light painted on a canvas stands exactly on
// what the DOM draws.
F.project = (x, y, z) => {
  const c = CAM, X = x - c.x, Y = y - c.y, Z = z - c.z;
  const a = -c.yaw * DEG, ca = Math.cos(a), sa = Math.sin(a);
  const X1 = X * ca + Z * sa, Z1 = -X * sa + Z * ca;
  const b = -c.pitch * DEG, cb = Math.cos(b), sb = Math.sin(b);
  const Y2 = Y * cb - Z1 * sb, Z2 = Y * sb + Z1 * cb;
  const r = c.roll * DEG, cr = Math.cos(r), sr = Math.sin(r);
  const X3 = X1 * cr - Y2 * sr, Y3 = X1 * sr + Y2 * cr;
  const w = c.P / Math.max(1, c.P - Z2), s = w * c.zoom;
  return [960 + X3 * s, 540 + Y3 * s, s, Z2];
};

/* ---------- the floor ---------- */
const floor = mk('div', null, world); floor.id = 'floor';
const grid = mk('div', null, floor); grid.id = 'grid';
// o: {on, a: line strength, fog: how far the grid reaches}. The fog is centred under what the
// camera looks at, so the grid never shows an edge.
F.setFloor = (o = {}) => {
  const on = o.on !== false; vis(floor, on); if (!on) return;
  floor.style.transform = `translate3d(0,${FLOOR}px,0) rotateX(90deg)`;
  // The plane is laid back, so #grid's own x runs with the world's x and its y with the world's z.
  // It follows the camera a whole cell at a time (the lines never swim) and the fog is centred
  // exactly under what the camera looks at.
  const cx = o.x == null ? CAM.x : o.x, cz = o.z == null ? CAM.z : o.z, sx = Math.round(cx / 240) * 240, sz = Math.round(cz / 240) * 240;
  grid.style.transform = `translate(${sx}px,${sz}px)`;
  grid.style.setProperty('--ga', (o.a == null ? .4 : o.a).toFixed(3));
  grid.style.setProperty('--fog', (o.fog || 2100) + 'px');
  grid.style.setProperty('--mx', (4000 + cx - sx).toFixed(0) + 'px');
  grid.style.setProperty('--mz', (4000 + cz - sz).toFixed(0) + 'px');
};

/* ---------- light, painted behind the world ---------- */
// The air of the room: a cool key from the upper left, and a band of haze where the floor fogs out.
F.air = (o = {}) => {
  const k = o.k == null ? 1 : o.k, g = bg;
  g.save();
  g.fillStyle = `rgb(${C.void})`; g.fillRect(0, 0, 1920, 1080);
  if (k > .003) {
    // the horizon: where a far point of the floor lands
    const hy = clamp(F.project(CAM.x, FLOOR, CAM.z - 60000)[1], -400, 1500);
    let gr = g.createLinearGradient(0, hy - 520, 0, hy + 360);
    gr.addColorStop(0, rgba(C.navy, 0)); gr.addColorStop(.52, rgba(C.navy, .9 * k)); gr.addColorStop(.62, rgba('20,34,78', .75 * k)); gr.addColorStop(1, rgba(C.navy, 0));
    g.fillStyle = gr; g.fillRect(0, hy - 520, 1920, 880);
    gr = g.createRadialGradient(200, -160, 0, 200, -160, 1500);
    gr.addColorStop(0, rgba(C.key, .3 * k * (o.key == null ? 1 : o.key))); gr.addColorStop(.45, rgba(C.key, .09 * k)); gr.addColorStop(1, rgba(C.key, 0));
    g.globalCompositeOperation = 'lighter'; g.fillStyle = gr; g.fillRect(0, 0, 1920, 1080);
  }
  g.restore();
};
// A pool of coloured light, on either canvas. `at` is a world point; rx, ry are its screen radii
// before perspective (so it shrinks with distance).
F.pool = (g, at, rx, ry, rgb, a, add = true) => {
  if (a <= .003) return;
  const [x, y, s] = F.project(at[0], at[1], at[2]);
  g.save(); g.globalCompositeOperation = add ? 'lighter' : 'source-over';
  g.translate(x, y); g.scale(1, ry / rx);
  const r = rx * s, gr = g.createRadialGradient(0, 0, 0, 0, 0, r);
  gr.addColorStop(0, rgba(rgb, a)); gr.addColorStop(.4, rgba(rgb, a * .42)); gr.addColorStop(1, rgba(rgb, 0));
  g.fillStyle = gr; g.fillRect(-r, -r, r * 2, r * 2); g.restore();
};

/* ---------- motes: a few soft discs of light, near ones large and out of focus ---------- */
const MOTES = (() => { const r = rng(58), out = []; for (let i = 0; i < 34; i++) out.push({ x: r() * 4200 - 1140, y: 120 + r() * 660, z: -900 + r() * 1700, ax: 40 + r() * 110, ay: 16 + r() * 50, k: 1 + Math.floor(r() * 3), ph: r() * 6.283, s: 1.2 + r() * 2.6, a: .25 + r() * .75 }); return out; })();
F.motes = (t, k = 1, focusZ = 0) => {
  if (k <= .003) return;
  const g = fx, w = 2 * Math.PI / 29;
  g.save(); g.globalCompositeOperation = 'lighter';
  for (const m of MOTES) {
    const [sx, sy, s, d] = F.project(CAM.x - 960 + m.x + Math.sin(t * w * m.k + m.ph) * m.ax, m.y + Math.cos(t * w * (m.k + 1) + m.ph * 1.7) * m.ay, CAM.z + m.z);
    if (sx < -80 || sx > 2000 || sy < -80 || sy > 1160 || s <= 0) continue;
    const blur = Math.abs(d - focusZ) / 240, r = (m.s * s + blur * 3.2), a = .5 * m.a * k / (1 + blur * blur * .9);
    const gr = g.createRadialGradient(sx, sy, 0, sx, sy, r * 2.4);
    gr.addColorStop(0, rgba('190,214,255', a)); gr.addColorStop(.55, rgba('150,190,255', a * .55)); gr.addColorStop(1, rgba('150,190,255', 0));
    g.fillStyle = gr; g.beginPath(); g.arc(sx, sy, r * 2.4, 0, 6.2832); g.fill();
  }
  g.restore();
};

/* ---------- the glass slab ----------
   new F.Slab({w, h, plate | dom, r, d, parent}). put({x, y, z, ry, rx, rz, s}) stands it in the
   world by its centre; glint is 0..1, one authored pass of the specular band. The rim's bright
   corner and the band both follow how the slab is turned to the lens, never time alone. */
class Slab {
  constructor(o) {
    const w = this.w = o.w, h = this.h = o.h, r = this.r = o.r == null ? 20 : o.r, d = this.d = o.d == null ? 14 : o.d;
    this.plate = o.plate ? PLATES[o.plate] : null;
    if (o.plate && !this.plate) throw new Error('no plate ' + o.plate);
    const root = this.root = mk('div', 'slab ' + (o.cls || ''), o.parent || world);
    // Images are <img decoding="sync"> and never CSS backgrounds: a frame is taken the moment it is
    // drawn, and only an <img> can be waited on (F.decodeAll) and is never painted late.
    const pic = (tag, cls, parent, src) => { const e = mk(tag, cls, parent); if (tag === 'img') { e.decoding = 'sync'; e.alt = ''; e.src = src; } return e; };
    const leaf = (cls, z, extra, tag = 'i', src) => { const e = pic(tag, cls, root, src); Object.assign(e.style, { width: w + 'px', height: h + 'px', marginLeft: -w / 2 + 'px', marginTop: -h / 2 + 'px', borderRadius: r + 'px', transform: `translateZ(${z}px)` }, extra || {}); return e; };
    if (this.plate && o.glow !== false) {
      const gw = w * 1.7, gh = h * 1.9;
      this.glow = leaf('sl-glow', -d - 46, { width: gw + 'px', height: gh + 'px', marginLeft: -gw / 2 + 'px', marginTop: -gh / 2 + 'px' }, 'img', this.plate.glow);
    }
    leaf('sl-back', -d);
    // four true sides, so a turned slab shows its thickness
    const side = (sw, sh, tr, sd) => { const e = mk('i', 'sl-side', root); Object.assign(e.style, { width: sw + 'px', height: sh + 'px', marginLeft: -sw / 2 + 'px', marginTop: -sh / 2 + 'px', transform: tr }); e.style.setProperty('--sd', sd); return e; };
    side(d, h - 2 * r, `translate3d(${-w / 2}px,0,${-d / 2}px) rotateY(-90deg)`, '90deg');
    side(d, h - 2 * r, `translate3d(${w / 2}px,0,${-d / 2}px) rotateY(90deg)`, '270deg');
    side(w - 2 * r, d, `translate3d(0,${-h / 2}px,${-d / 2}px) rotateX(90deg)`, '180deg');
    side(w - 2 * r, d, `translate3d(0,${h / 2}px,${-d / 2}px) rotateX(-90deg)`, '0deg');
    const face = this.face = leaf('sl-face', 0);
    if (this.plate) {
      const im = this.img = mk('img', null, face); im.decoding = 'sync'; im.alt = ''; im.src = this.plate.src;
      // crop: [x, y, w, h] in the plate's own pixels: the face shows that region and nothing else
      this.crop = o.crop || null;
      if (o.crop) { const [cx, cy, cw, ch] = o.crop, k = w / cw; Object.assign(im.style, { width: this.plate.w * k + 'px', height: this.plate.h * k + 'px', left: -cx * k + 'px', top: -cy * k + 'px' }); }
    }
    if (o.dom) { this.dom = mk('div', 'dom', face); if (typeof o.dom === 'string') this.dom.innerHTML = o.dom; else this.dom.appendChild(o.dom); }
    // A light screen is a lamp: it is held under the grade's bloom so its own text stays crisp, its
    // glass does not grey its foot, and the light it throws behind is kept low.
    this.lit = !!(this.plate && this.plate.lum > .55);
    this.dim = leaf('sl-dim', .3);
    leaf('sl-glass' + (this.lit ? ' lit' : ''), .5);
    this.rim = leaf('sl-rim', .7);
    this.spec = leaf('sl-spec', .9);
    // the reflection is its own leaf, a sibling in the world, so it can be masked
    if (this.plate && o.reflect !== false) {
      this.refl = pic('img', 'refl', o.parent || world, this.plate.blur);
      Object.assign(this.refl.style, { width: w + 'px', height: h + 'px', marginLeft: -w / 2 + 'px', marginTop: -h / 2 + 'px', borderRadius: r + 'px' });
    }
    this.cur = null;
  }
  // Plate pixels to the slab's own pixels (the crop's corner is the slab's corner).
  k() { return this.w / (this.crop ? this.crop[2] : this.plate ? this.plate.w : this.w); }
  loc(u, v) { const c = this.crop || [0, 0], k = this.k(); return [(u - c[0]) * k, (v - c[1]) * k]; }
  // Where a point of the screen (in plate pixels) stands in the world, as the slab was last put.
  pt(u, v, lift = 0) {
    const [lx, ly] = this.loc(u, v), c = this.cur || { x: 0, y: 0, z: 0, ry: 0, s: 1 }, a = c.ry * DEG;
    const X = (lx - this.w / 2) * c.s, Y = (ly - this.h / 2) * c.s;
    return [c.x + X * Math.cos(a) + lift * Math.sin(a), c.y + Y, c.z - X * Math.sin(a) + lift * Math.cos(a)];
  }
  // A box laid on the screen, given in plate pixels: [x, y, w, h]. Returns the element.
  box(rect, cls, css) {
    if (!this.dom) this.dom = mk('div', 'dom', this.face);
    const e = mk('div', cls || null, this.dom), [x, y] = this.loc(rect[0], rect[1]), k = this.k();
    Object.assign(e.style, { position: 'absolute', left: x + 'px', top: y + 'px', width: rect[2] * k + 'px', height: rect[3] * k + 'px' }, css || {});
    return e;
  }
  // Part of another state of the same screen, laid over this one: rect in plate pixels. The two
  // plates are registered to the pixel, so showing it is that part of the screen changing.
  reveal(plateId, rect) {
    const p = PLATES[plateId]; if (!p) throw new Error('no plate ' + plateId);
    const e = this.box(rect, null, { overflow: 'hidden' }), k = this.k(), im = mk('img', null, e); im.decoding = 'sync'; im.alt = ''; im.src = p.src;
    Object.assign(im.style, { position: 'absolute', width: p.w * k + 'px', height: p.h * k + 'px', left: -rect[0] * k + 'px', top: -rect[1] * k + 'px', maxWidth: 'none' });
    return e;
  }
  // swap what the face shows (another state of the same screen, registered to the pixel)
  show(plateId) { const p = PLATES[plateId]; if (!p) throw new Error('no plate ' + plateId); if (this.img && this._p !== plateId) { this._p = plateId; this.img.src = p.src; } }
  put(p) {
    const on = p.on !== false; vis(this.root, on); if (this.refl) vis(this.refl, on && p.reflect !== false); if (!on) return;
    const x = p.x || 0, y = p.y == null ? FLOOR - this.h / 2 : p.y, z = p.z || 0, ry = p.ry || 0, rx = p.rx || 0, rz = p.rz || 0, s = p.s == null ? 1 : p.s;
    this.root.style.transform = `translate3d(${x.toFixed(2)}px,${y.toFixed(2)}px,${z.toFixed(2)}px) rotateY(${ry.toFixed(3)}deg) rotateX(${rx.toFixed(3)}deg) rotateZ(${rz.toFixed(3)}deg) scale(${s.toFixed(5)})`;
    const c = F.cam(), turn = ry - c.yaw;                 // how far it is turned from the lens
    // the rim: its bright corner swings with the turn
    const ra = 140 - turn * 1.6; if (this._ra !== (this._ra = ra.toFixed(1))) this.rim.style.setProperty('--rim-a', this._ra + 'deg');
    // the specular band: driven by the turn and by where the lens stands, plus one authored glint
    const g = p.glint == null ? -1 : p.glint, base = 50 - turn * 3.4 + (c.x - x) * .035;
    const sx = g >= 0 && g <= 1 ? lerp(-30, 116, E.io(g)) : base;
    const sa = (g >= 0 && g <= 1 ? .1 + .2 * Math.sin(Math.PI * g) : .1) * (this.lit ? .45 : 1);
    this.spec.style.setProperty('--spec-x', sx.toFixed(2) + '%'); this.spec.style.setProperty('--spec-a', sa.toFixed(3));
    this.dim.style.setProperty('--dim', (p.dim == null ? (this.lit ? .2 : .1) : p.dim).toFixed(3));
    if (this.glow) this.glow.style.setProperty('--glow', ((p.glow == null ? .5 : p.glow) * (this.lit ? .5 : 1)).toFixed(3));
    if (this.refl && p.reflect !== false) {
      const bottom = y + this.h * s / 2, gap = FLOOR - bottom;       // how far it floats
      const yr = FLOOR + gap + this.h * s / 2;
      this.refl.style.transform = `translate3d(${x.toFixed(2)}px,${yr.toFixed(2)}px,${z.toFixed(2)}px) rotateY(${ry.toFixed(3)}deg) rotateX(${(-rx).toFixed(3)}deg) rotateZ(${(-rz).toFixed(3)}deg) scale(${s.toFixed(5)}) scaleY(-1)`;
      this.refl.style.setProperty('--ro', ((p.ro == null ? .3 : p.ro) / (1 + Math.max(0, gap) / 260)).toFixed(3));
    }
    this.cur = { x, y, z, ry, s };
    // the light it throws: on the floor in front of it, and in the haze behind
    if (this.plate && p.spill !== 0) {
      const k = p.spill == null ? 1 : p.spill, hw = this.w * s / 2, tint = this.plate.tint;
      F.pool(bg, [x, FLOOR, z + 120], hw * 1.5, hw * .42, tint, .2 * k);
      F.pool(bg, [x, y, z - 200], hw * 1.7, this.h * s * .95, tint, .14 * k);
    }
  }
}
F.Slab = Slab;

// A slab standing out of focus, in the fore or the background: one leaf showing the softened copy.
class Far {
  constructor(o) {
    this.w = o.w; this.h = o.h; const p = this.plate = PLATES[o.plate]; if (!p) throw new Error('no plate ' + o.plate);
    const e = this.root = mk('img', 'far', o.parent || world); e.decoding = 'sync'; e.alt = ''; e.src = p.blur;
    Object.assign(e.style, { width: o.w + 'px', height: o.h + 'px', marginLeft: -o.w / 2 + 'px', marginTop: -o.h / 2 + 'px' });
  }
  put(p) {
    const on = p.on !== false; vis(this.root, on); if (!on) return;
    const x = p.x || 0, y = p.y == null ? FLOOR - this.h / 2 : p.y, z = p.z || 0, s = p.s == null ? 1 : p.s;
    this.root.style.transform = `translate3d(${x.toFixed(2)}px,${y.toFixed(2)}px,${z.toFixed(2)}px) rotateY(${(p.ry || 0).toFixed(3)}deg) scale(${s.toFixed(5)})`;
    this.root.style.opacity = (p.o == null ? .5 : p.o).toFixed(3);
    this.root.style.filter = p.blur ? `blur(${p.blur.toFixed(1)}px)` : '';
    if (p.spill !== 0) F.pool(bg, [x, FLOOR, z + 100], this.w * s * .7, this.w * s * .2, this.plate.tint, .12 * (p.spill == null ? 1 : p.spill) * (p.o == null ? .5 : p.o) * 2);
  }
}
F.Far = Far;

/* ---------- every image a frame needs, decoded before the frame is taken ---------- */
F.decodeAll = root => Promise.all([...(root || document).querySelectorAll('img')].filter(i => i.offsetParent !== null || i.getClientRects().length).map(i => i.decode().catch(() => {})));
})();
