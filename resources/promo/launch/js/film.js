// DOORS: the film. Each shot builds what it needs once, into its own set (in the world) and its
// own layer (in screen space), and draws itself from t alone. The cut is TL.SHOTS in
// js/timeline.js: a shot runs until the next begins, and every cut is hard.
'use strict';
(() => {
const { clamp, lerp, range, E, spring, wobble, bump, mk, html, vis, rgba, rng, hash, $ } = F;
const FLOOR = F.FLOOR, C = F.C, hud = $('#hud'), world = F.world, room = F.room, SEATS = F.SEATS;
const DEFS = {};
// shot(id, {build(set, layer), draw(t, d, shot)}): d is seconds into the shot.
const shot = (id, def) => { DEFS[id] = def; };
// A plate by its id, or a stand-in while the real one is still to be shot.
const P = (id, dev) => PLATES[id] ? id : (PLATES[dev] ? dev : Object.keys(PLATES)[0]);
// size: {h} or {w} (the other follows the plate, or the crop when one is given: [x, y, w, h] in plate pixels)
const slabOf = (set, id, dev, size, o = {}) => { const p = P(id, dev), pl = PLATES[p], c = p === id ? o.crop : o.devCrop, ar = c ? c[2] / c[3] : pl.w / pl.h; const w = size.w || size.h * ar, h = size.h || size.w / ar; return new F.Slab({ w, h, plate: p, parent: set, ...o, crop: c }); };
const farOf = (set, id, dev, size) => { const p = P(id, dev), pl = PLATES[p]; const w = size.w || size.h * pl.w / pl.h, h = size.h || size.w * pl.h / pl.w; return new F.Far({ w, h, plate: p, parent: set }); };
const darkEl = $('#dark'), stampEl = $('#stamp');
const dark = a => { darkEl.style.opacity = clamp(a).toFixed(4); };
const wash = (a, css) => { stampEl.style.opacity = clamp(a).toFixed(4); if (css && stampEl._c !== css) { stampEl._c = css; stampEl.style.background = css; } };
const GRAD = 'linear-gradient(135deg,#4e81fa,#0ea5e9 55%,#22d3ee)';
// the light a push-through leaves behind on the first frames of the next shot
const through = (d, css = GRAD, life = .14) => { if (d < life) wash(Math.pow(1 - d / life, 1.6) * .92, css); };
const maskLine = (s, cls = '') => `<span class="mask"><span class="${cls}">${s}</span></span>`;
const riseAll = (els, t, at, step = .08, dur = .34) => els.forEach((e, i) => { e.style.transform = `translateY(${((1 - E.rise(range(t, at + i * step, at + i * step + dur))) * 112).toFixed(2)}%)`; });
// The mark with the curved join, as the Product Hunt thumbnail draws it (the two sit side by side):
// the E is three plain bars, and the channel between the letters is cut out of it by the S's own
// outline grown by five units.
const S_PATH = 'M83 35 L123 35 L110.5 53 L85 53 L85 61 L101 61 C116 61 125 66 125 76 L125 88 C125 97 119 103 109 103 L62 103 C65.5 98 70.5 91.5 74.5 86 L105 86 L105 78 L81 78 C70 78 63 72 63 63 L63 57 C63 44.5 71.5 35 83 35 Z';
const E_PATH = 'M20 35 H96 V53 H42 V61 H96 V77 H42 V85 H96 V103 H20 Z';
let lockN = 0;
const lockup = () => { const id = 'cut' + (lockN++); return `<div class="lock"><svg viewBox="14 28 118 82"><defs><mask id="${id}" maskUnits="userSpaceOnUse" x="0" y="0" width="150" height="140"><rect width="150" height="140" fill="#fff"/><path d="${S_PATH}" fill="#000" stroke="#000" stroke-width="10" stroke-linejoin="round"/></mask></defs><g class="e"><path d="${E_PATH}" fill="#2f9bff" mask="url(#${id})"/></g><path class="s" d="${S_PATH}" fill="#e8eefc"/></svg><div class="w"><b>Event</b><i>Schedule</i></div></div>`; };

// the one camera most product shots start from: screens stand in the upper band, the line below
const up = (o = {}) => { const c = { x: 960, y: 430, z: 0, yaw: 0, pitch: 7, zoom: 1, P: 2600, ...o }; c.zoom *= 1.1; c.y -= 28; return c; };
// a point on a quadratic arc between two world points, lifted in the middle
const arc3 = (a, b, u, lift = 120) => [lerp(a[0], b[0], u), lerp(a[1], b[1], u) - lift * Math.sin(Math.PI * u), lerp(a[2], b[2], u)];
// a flat element standing in the world, placed by its centre
const stand = (e, p, s = 1, ry = 0) => { e.style.transform = `translate3d(${p[0].toFixed(2)}px,${p[1].toFixed(2)}px,${p[2].toFixed(2)}px) translate(-50%,-50%) rotateY(${ry}deg) scale(${Math.max(.001, s).toFixed(4)})`; };
// warm light on a point of the world (a chosen seat, a booked slot)
const warm = (at, r, a) => { F.pool(F.fx, at, r, r, C.warm, a); F.pool(F.fx, at, r * .4, r * .4, C.hot, a * .8); };

/* ====================== a plain slab shot ======================
   One real screen as a slab, framed above the line it carries, with a camera that is already
   moving on its first frame. Most of the middle of the film starts from this and adds to it. */
const plain = (id, plate, dev, o = {}) => shot(id, {
  build(set) {
    this.s = slabOf(set, plate, dev, o.size || { h: 640 }, o.crop ? { crop: o.crop } : {});
    this.f = (o.fars || []).map(f => farOf(set, f[0], f[1], { h: f[2] || 520 }));
    if (o.build) o.build.call(this, set);
  },
  draw(t, d, s) {
    const dir = o.dir || 1, len = s.b - s.a, u = d / len;
    F.setCam({ x: 940 + dir * 26 * u, y: 480, z: 0, yaw: dir * lerp(9, 5.5, E.out(u)), pitch: 8, zoom: lerp(.94, .985, u), P: 2600, ...(o.cam ? o.cam(d, u) : {}) });
    F.air({}); F.setFloor({});
    (o.fars || []).forEach((f, i) => this.f[i].put({ x: f[3], z: f[4], ry: f[5] || 0, o: .45, blur: 3 }));
    this.s.put({ x: 1040, y: 392, ry: -dir * 7, ...(o.put ? o.put.call(this, d, u, t) : {}) });
    F.motes(t, 1, 0);
    if (o.over) o.over.call(this, t, d, u);
  },
});

/* ====================== the title and the hook: 0 to 6 ======================
   The name first (s00, further down, beside the page it stands over). Then one flyer, pasted: it
   is read, and the real event page locks together out of its parts. */
const D = window.PDATA || {};
const pad = (r, n) => [r[0] - n, r[1] - n, r[2] + 2 * n, r[3] + 2 * n];
const POSTER = { head: [90, 95, 900, 80], title: [90, 800, 1020, 500], foot: [90, 1380, 900, 100] };   // on the flyer, when it is 1200 wide
const BOX = { crop: [600, 190, 2064, 710], thumb: [663, 646, 187, 190] };                              // the import box, and where it shows the flyer
shot('s01', {
  build(set) {
    this.box = slabOf(set, 'import-flyer-light', 'ph/import-box', { w: 940 }, { crop: BOX.crop, r: 22 });
    this.patch = this.box.box(pad(BOX.thumb, 10), 'patch');
    this.poster = slabOf(set, 'art/jazz-night@4x', 'ph/poster', { h: 640 }, { r: 12, d: 8 });
  },
  draw(t, d) {
    const u = E.outExpo(range(d, 0, .45));
    F.setCam({ x: lerp(1230, 1060, u) + 14 * d, y: lerp(560, 520, u), z: 0, yaw: lerp(30, 7, u), pitch: 6, zoom: lerp(2.5, 1, u), P: 1400 });
    F.air({ key: 1.3 }); F.setFloor({});
    this.box.put({ x: 1400, y: 640, z: -120, ry: -8 });
    // the flyer goes into the box: the picture the app now holds
    const th = BOX.thumb, fl = E.snap(range(d, .5, .74)), to = this.box.pt(th[0] + th[2] / 2, th[1] + th[3] / 2, 3), k1 = th[2] * this.box.k() / this.poster.w;
    this.poster.put({ on: fl < 1, x: lerp(1300, to[0], fl), y: lerp(340, to[1], fl), z: lerp(40, to[2], fl), ry: lerp(-6, -8, fl), s: lerp(1, k1, fl), glint: range(d, .04, .5), reflect: fl < .05, spill: 1 - fl });
    vis(this.patch, fl < 1);
    const [sx, sy] = F.project(1240, 540, 46); F.streak(sx, sy, .9 * Math.exp(-d / .16), { w: 1700 });
    F.tap(t, t - d + .5, [1300, 340, 60], { k: 1.6 });
    if (d >= .74) wash(.2 * Math.exp(-(d - .74) / .05), '#dbe8ff');
    F.motes(t, 1, 0);
  },
});
// The app has read it: the event's fields, filled one after another from what the flyer says.
const PARSED = D['import-parsed'];
shot('s02', {
  build(set) {
    const s = this.s = slabOf(set, 'import-parsed-blank-light', 'ph/event-card', { h: 580 }, { r: 22 });
    const f = PARSED.fields.map(x => x.rect), im = PARSED.image, q = im[2] / 1200;
    this.to = [f[0], [f[1][0], f[1][1], f[1][2], f[3][1] + f[3][3] - f[1][1]], f[4]];
    this.val = this.to.map(r => s.reveal('import-parsed-light', pad(r, 8)));
    this.from = [POSTER.title, POSTER.foot, POSTER.head].map(r => [im[0] + r[0] * q, im[1] + r[1] * q, r[2] * q, r[3] * q]);
    s.reveal('import-parsed-light', im);
    this.brk = this.from.map(r => s.box(pad(r, 14), 'ring'));
    this.blade = s.box([im[0], im[1], im[2], 16], 'scanblade'); this.im = im;
    this.fly = ['Jazz Night', 'Tonight · 8:00 PM', 'The Indigo Room'].map(tx => html(set, `<div class="fly">${tx}</div>`));
  },
  draw(t, d) {
    F.setCam(up({ x: 1170 + 16 * d, y: 505, yaw: lerp(-7, -4, d), zoom: lerp(1, 1.03, d) }));
    F.air({ key: 1.2 }); F.setFloor({});
    const s = this.s; s.put({ x: 1190, y: 326, ry: -5, glint: range(d, -.1, .3) });
    const im = this.im, k = s.k(), sc = E.io(range(d, .02, .7));
    this.blade.style.top = s.loc(0, im[1] + sc * (im[3] - 16))[1] + 'px'; this.blade.style.opacity = d < .78 ? 1 : 0;
    const land = [.25, .5, .75], FLY = .2;
    this.fly.forEach((e, i) => {
      const r = this.from[i], v = this.to[i], a = s.pt(r[0] + r[2] / 2, r[1] + r[3] / 2, 36), b = s.pt(v[0] + v[2] * .28, v[1] + v[3] / 2, 36);
      const u = range(d, land[i] - FLY, land[i]), on = u > 0 && u < 1;
      vis(e, on); if (on) { stand(e, arc3(a, b, E.io(u), 70), lerp(1.1, .9, u), -5); e.style.opacity = clamp(u * 8) * (1 - range(u, .85, 1)); }
      const q = E.outBack(range(d, .03 + i * .12, .19 + i * .12)); this.brk[i].style.opacity = clamp(q) * (1 - range(d, land[i] - FLY, land[i] - FLY + .08)); this.brk[i].style.transform = `scale(${lerp(1.1, 1, clamp(q)).toFixed(4)})`;
      vis(this.val[i], d >= land[i]);
      const fl = d - land[i]; if (fl > 0 && fl < .3) F.pool(F.fx, b, 260, 90, C.sky, .5 * (1 - fl / .3));
    });
    F.motes(t, 1, 0);
  },
});
// The event page, in its parts as the app drew them: the schedule's own ground, the flyer, the
// facts, and what lies below. `ex` is how far the parts stand apart (1 exploded, 0 locked).
const EV = { crop: [0, 0, 4320, 2400], W: 1080 };
const eventPage = set => {
  const W = EV.W, H = W * EV.crop[3] / EV.crop[2], full = W * 2700 / 4320;
  const bg = slabOf(set, 'event-jazz.L-bg', 'ph/event-card', { w: W }, { crop: EV.crop, r: 26, d: 10 });
  const layer = id => { const e = mk('div', null, set); Object.assign(e.style, { position: 'absolute', left: 0, top: 0, width: W + 'px', height: H + 'px', margin: `${-H / 2}px 0 0 ${-W / 2}px`, overflow: 'hidden', borderRadius: '26px' }); const im = mk('img', null, e); im.decoding = 'sync'; im.alt = ''; im.src = PLATES[id].src; Object.assign(im.style, { position: 'absolute', left: 0, top: 0, width: W + 'px', height: full + 'px', maxWidth: 'none' }); return e; };
  const rest = layer('event-jazz.L-rest'), left = layer('event-jazz.L-left'), right = layer('event-jazz.L-right');
  return { put(o) {
    const ex = o.ex || 0, x = o.x == null ? 1190 : o.x, y = 312, br = `brightness(${(1 - (o.dim == null ? .1 : o.dim) * .9).toFixed(3)})`;
    bg.put({ x, y, z: -24 - 240 * ex, ry: -5 * ex, dim: o.dim, glint: o.glint, spill: 1 - .5 * ex });
    const at = (e, dx, dy, z, ry) => { e.style.transform = `translate3d(${x + dx * ex}px,${y + dy * ex}px,${z}px) rotateY(${(ry * ex).toFixed(2)}deg)`; e.style.filter = br; };
    at(rest, 30, 90, 2 + 30 * ex, 2); rest.style.opacity = (1 - .75 * ex).toFixed(3);
    at(left, -150, -40, 4 + 110 * ex, 11); at(right, 170, 34, 6 + 300 * ex, -15);
  } };
};
shot('s03', {
  build(set) { this.page = eventPage(set); },
  draw(t, d) {
    const u = E.out(range(d, 0, 1)), lock = d - 1;
    F.setCam(up({ x: 1180, y: 510, yaw: lerp(34, 6, E.out(range(d, 0, 1.1))) - 1.5 * Math.max(0, d - 1.1), zoom: lerp(.9, 1, u) * (1 + .02 * Math.max(0, d - 1)) }));
    F.air({ key: 1.2 }); F.setFloor({ a: .4 + (lock > 0 ? .4 * Math.exp(-lock / .25) : 0) });
    this.page.put({ ex: 1 - E.snap(range(d, .25, 1)), glint: range(d, 1.45, 1.9) });
    if (lock > 0) { wash(.28 * Math.exp(-lock / .07), '#cfe2ff'); F.shock(t, t - lock, [1190, 0, 0], { r0: 500, r1: 1700, a: .5, life: .8 }); }
    F.motes(t, 1, 0);
  },
});
// The title, which opens the film: the name and what the product is, over the page the hook is
// about to make. The mark is lit on the first frame; the room comes up behind it.
shot('s00', {
  build(set, layer) {
    this.page = eventPage(set);
    const c = html(layer, `<div class="card"><div class="veil"></div>${lockup()}<div class="stmt" style="top:432px;font-size:112px">${maskLine('<span class="gtext">Open source</span> event calendar,')}${maskLine('ticketing and bookings')}</div></div>`);
    this.lock = c.querySelector('.lock'); this.e = c.querySelector('.e'); this.sp = c.querySelector('.s'); this.w = c.querySelector('.w'); this.lines = [...c.querySelectorAll('.mask>span')]; this.veil = c.querySelector('.veil');
  },
  draw(t, d) {
    const lit = E.out(range(d, 0, .7));
    F.setCam(up({ x: 1180 - 30 * E.out(d / 2), y: 510, yaw: 3 - 1.5 * d, zoom: 1.04 * (1 + .02 * d) }));
    F.air({ key: lerp(.5, 1.2, lit) }); F.setFloor({ a: lerp(.2, 1, lit) });
    this.page.put({ dim: lerp(.88, .62, lit), glint: range(d, .5, 1.5) });
    F.motes(t, 1, 0);
    this.veil.style.opacity = 1;
    const a = spring(d + .06, 2.1, .6), sl = (1 - E.outExpo(range(d, -.14, .3))) * 40;     // nearly joined on frame 0: a first frame is also a poster
    this.lock.style.transform = `translate(150px,300px) scale(${lerp(.94, 1, a).toFixed(4)})`; this.lock.style.opacity = 1;
    this.e.setAttribute('transform', `translate(${-.51 * sl} ${.86 * sl})`); this.sp.setAttribute('transform', `translate(${.51 * sl} ${-.86 * sl})`);
    this.w.style.opacity = E.out(range(d, -.12, .2)); this.w.style.transform = `translateX(${((1 - E.rise(range(d, -.1, .34))) * 26).toFixed(2)}px)`;
    riseAll(this.lines, d, .1, .14, .36);
  },
});

/* ====================== the three verbs ======================
   The full stop is the rounded square of light. The card slams in on the bar line and leaves by
   pushing through its own full stop. */
const verb = (id, i, word) => shot(id, {
  build(set, layer) {
    const c = html(layer, `<div class="card lines"><div class="vix label">0${i + 1} / 03</div><div class="vbars">${[0, 1, 2].map(j => `<i class="${j === i ? 'on' : j < i ? 'done' : ''}"></i>`).join('')}</div><div class="vw" style="position:absolute;left:0;top:0;width:1920px;height:1080px"><div class="vword">${word}<i class="stop"></i></div><div class="vstop"></div></div></div>`);
    this.vw = c.querySelector('.vw'); this.word = c.querySelector('.vword'); this.stop = c.querySelector('.stop'); this.sq = c.querySelector('.vstop'); this.ix = c.querySelector('.vix'); this.bars = c.querySelector('.vbars');
  },
  draw(t, d) {
    F.setCam({}); F.air({ key: 1.25 }); F.setFloor({ on: false });
    // where the full stop is (measured once the type has its font)
    if (!this.m) { const x = this.word.offsetLeft + this.stop.offsetLeft, y = this.word.offsetTop + this.stop.offsetTop, w = this.stop.offsetWidth; this.m = { x: x + w / 2, y: y + w / 2, w }; Object.assign(this.sq.style, { width: w + 'px', height: w + 'px', left: x + 'px', top: y + 'px', borderRadius: w * .26 + 'px' }); }
    const m = this.m, push = E.inExpo(range(d, .7, 1)), slam = lerp(1.14, 1, E.outExpo(clamp(d / .3))) * (1 + .012 * d);
    this.vw.style.transformOrigin = push > 0 ? `${m.x}px ${m.y}px` : '150px 560px';
    this.vw.style.transform = `scale(${(push > 0 ? 1 + 62 * push : slam).toFixed(4)})`;
    this.vw.style.opacity = clamp(d / .04);
    this.word.style.opacity = 1 - range(d, .72, .88);
    const o = clamp(d / .2) * (1 - range(d, .7, .8)); this.ix.style.opacity = o; this.bars.style.opacity = o;
  },
});
verb('s05', 0, 'Plan'); verb('s10', 1, 'Promote'); verb('s23', 2, 'Sell');

/* ====================== plan: 7 to 14 ====================== */
// The month, as the schedule's own page shows it: the empty month, and each day's events laid
// over it from the full one (the two plates are registered).
const MON = D['gp-indigo-month'], MCROP = [150, 60, 3420, 1900], JAZZ = MON.jazz_night_saturday;
const calSlab = (set, w) => {
  const s = slabOf(set, 'gp-indigo-month-empty', 'ph/calendar', { w }, { crop: MCROP, r: 22 });
  s.cells = MON.cells.filter(c => c.events.length && c.rect[1] + c.rect[3] < MCROP[1] + MCROP[3]).map(c => ({ c, dist: Math.hypot((c.rect[0] - JAZZ.cell[0]) / 473, (c.rect[1] - JAZZ.cell[1]) / 465), e: s.reveal('gp-indigo-month', [c.rect[0] + 3, c.rect[1] + 66, c.rect[2] - 6, c.rect[3] - 69]) }));
  return s;
};
shot('s06', {
  build(set) { this.s = calSlab(set, 1160); this.hl = this.s.box(pad(JAZZ.entry, 6), 'ring'); },
  draw(t, d) {
    this.s.put({ x: 1150, y: 338, ry: -5 });
    // from inside Saturday, out to the month
    const k = JAZZ.cell, f = this.s.pt(k[0] + k[2] / 2, k[1] + k[3] / 2), u = E.snap(range(d, .12, 1.25));
    F.setCam(up({ x: lerp(f[0], 1100, u), y: lerp(f[1], 500, u), z: lerp(f[2], 0, u), yaw: lerp(-5, 5, u) + 2 * Math.max(0, d - 1.25), zoom: lerp(4.2, 1, u) * (1 + .012 * d) }));
    F.air({ key: 1.2 }); F.setFloor({});
    this.s.put({ x: 1150, y: 338, ry: -5 });
    this.s.cells.forEach(o => vis(o.e, d >= (o.dist < .01 ? .25 : .5 + o.dist * .15)));
    const q = d - .25; this.hl.style.opacity = q > 0 ? clamp(q / .06) * (1 - range(d, 1.1, 1.4)) : 0; this.hl.style.transform = `scale(${(1 + .25 * Math.exp(-Math.max(0, q) / .08)).toFixed(4)})`;
    through(d);
    F.motes(t, range(d, .8, 1.4), 0);
  },
});
// the schedule's page: who it is, and what is on
const LST = D['gp-indigo-list'];
shot('s07', {
  build(set) {
    const r = LST.rows, c = [0, r[0].rect[1] - 70, 2760, r[2].rect[1] + r[2].rect[3] + 70 - (r[0].rect[1] - 70)];
    this.h = slabOf(set, 'gp-indigo-top', 'ph/sched-phone', { h: 560 }, { r: 26 });
    this.l = slabOf(set, 'gp-indigo-list', 'ph/sched-phone', { h: 640 }, { crop: c, r: 26 });
    this.hl = r.slice(0, 3).map(x => this.l.box(pad(x.rect, 10), 'ring', { borderRadius: '26px', borderWidth: '7px' }));
  },
  draw(t, d) {
    F.setCam(up({ x: 1080 + 22 * d, y: 505, yaw: lerp(-8, -4, E.out(d)), zoom: lerp(.97, 1.02, d / 1.5) }));
    F.air({ key: 1.2 }); F.setFloor({});
    this.h.put({ x: 800, y: 316, ry: 7, glint: range(d, .1, .7) });
    const a = spring(d - .12, 2.2, .6);
    this.l.put({ x: 1560, y: 330 + 40 * (1 - clamp(a)), z: -20, ry: -9, s: lerp(.92, 1, clamp(a)) });
    // one row after another, on the beat, coming to rest on tonight's
    const step = Math.min(2, Math.floor((d - .25) / .25)); this.hl.forEach((e, i) => { e.style.opacity = d >= .25 && i === step ? 1 : 0; });
    F.motes(t, 1, 0);
  },
});
shot('s08', {
  build(set) {
    this.s = calSlab(set, 1000);
    this.chips = ['Google Calendar', 'Outlook', 'CalDAV'].map(n => html(set, `<div class="chip">${n}</div>`));
    const c = this.cell = this.s.cells.find(o => o.c.day === 9); this.hl = this.s.box(pad(c.c.events[0].rect, 6), 'ring');
  },
  draw(t, d) {
    F.setCam(up({ x: 990 + 18 * d, y: 505, yaw: lerp(4, 1, d / 1.5), zoom: 1 + .02 * d }));
    F.air({ key: 1.2 }); F.setFloor({});
    this.s.put({ x: 1340, y: 312, ry: -10 });
    this.s.cells.forEach(o => vis(o.e, o !== this.cell || d >= 1));
    const Y = [190, 320, 450], edge = this.s.pt(MCROP[0], 900, 0);
    this.chips.forEach((e, i) => { const a = spring(d - .04 - i * .07, 2.2, .6); stand(e, [470 + 20 * i, Y[i], 40], lerp(.8, 1, clamp(a))); e.style.opacity = clamp(a * 3); });
    // a line from each calendar to the schedule, and light running both ways along it
    const g = F.fx; g.save(); g.globalCompositeOperation = 'lighter';
    Y.forEach((y, i) => {
      const a = F.project(700 + 20 * i, y, 40), b = F.project(edge[0] - 10, 240 + i * 80, edge[2]);
      g.strokeStyle = rgba(C.rim, .22); g.lineWidth = 2; g.beginPath(); g.moveTo(a[0], a[1]); g.bezierCurveTo(a[0] + 150, a[1], b[0] - 150, b[1], b[0], b[1]); g.stroke();
      for (const [t0, dirn] of [[.25, 1], [.75, -1]]) {
        const u = range(d, t0 + i * .05, t0 + i * .05 + .32); if (u <= 0 || u >= 1) continue; const q = dirn > 0 ? E.io(u) : 1 - E.io(u), m = 1 - q;
        const x = m * m * m * a[0] + 3 * m * m * q * (a[0] + 150) + 3 * m * q * q * (b[0] - 150) + q * q * q * b[0], yy = m * m * m * a[1] + 3 * m * m * q * a[1] + 3 * m * q * q * b[1] + q * q * q * b[1];
        const gr = g.createRadialGradient(x, yy, 0, x, yy, 26); gr.addColorStop(0, rgba('255,255,255', .95)); gr.addColorStop(.3, rgba(C.sky, .6)); gr.addColorStop(1, rgba(C.sky, 0)); g.fillStyle = gr; g.beginPath(); g.arc(x, yy, 26, 0, 6.2832); g.fill();
      }
    });
    g.restore();
    // and a day's events that came across
    const q = d - 1; this.hl.style.opacity = q > 0 ? clamp(q / .06) : 0; this.hl.style.transform = `scale(${(1 + .25 * Math.exp(-Math.max(0, q) / .08)).toFixed(4)})`;
    F.motes(t, 1, 0);
  },
});
// bookings: a time is chosen, and it is the film's first warm light
const SLOT = [2075, 796, 583, 112];
shot('s09', {
  build(set) {
    this.a = slabOf(set, 'book-types', 'ph/book-phone', { w: 940 }, { r: 24 });
    this.b = slabOf(set, 'book-slots-0', 'ph/book-phone', { w: 1180 }, { r: 24 });
  },
  draw(t, d) {
    const u = E.io(range(d, .55, 1.0));
    F.setCam(up({ x: lerp(880, 1180, u) + 10 * d, y: 505, yaw: lerp(7, -4, u), zoom: lerp(1, 1.08, u) }));
    F.air({ key: 1.2 }); F.setFloor({});
    this.a.put({ x: lerp(900, 420, u), y: 310, z: lerp(0, -260, u), ry: lerp(6, 24, u), dim: lerp(.2, .5, u) });
    const a = spring(d - .55, 2.2, .62);
    this.b.put({ on: d > .5, x: 1220, y: 320 + 50 * (1 - clamp(a)), z: 60, ry: -6, s: lerp(.9, 1, clamp(a)), glint: range(d, .7, 1.3) });
    this.b.show(d >= 1 ? 'book-slots' : 'book-slots-0');
    if (d < .6) F.tap(t, t - d + .5, this.a.pt(700, 560, 6));
    const c = this.b.pt(SLOT[0] + SLOT[2] / 2, SLOT[1] + SLOT[3] / 2, 6), q = d - 1;
    if (d > .7) F.tap(t, t - d + 1, c, { rgb: C.hot });
    if (q > 0) warm(c, 300, .3 * (.6 + .4 * Math.exp(-q / .3)));
    F.motes(t, 1, 0);
  },
});

/* ====================== promote: 15 to 24 ====================== */
// four schedules, one after another in the same frame: each lights the room in its own colour
['gp-indigo-top', 'gp-late-laughs-top', 'gp-eastside-top', 'gp-riverside-top'].forEach((p, i) => shot('s1' + (i + 1), {
  build(set) { this.s = slabOf(set, p, 'ph/sched-phone', { w: 980 }, { r: 26 }); },
  draw(t, d) {
    F.setCam(up({ x: 1000, y: 505, yaw: i % 2 ? -5 : 5, zoom: lerp(1.06, 1.0, E.outExpo(clamp(d / .25))) * (1 + .02 * d) }));
    F.air({ key: .7 }); F.setFloor({});
    this.s.put({ x: 1190, y: 312, ry: i % 2 ? 4 : -4, spill: 2.6, glow: .9 });
    if (i === 0) through(d);
    F.motes(t, 1, 0);
  },
}));
// twelve languages: the same header, said eight ways, and a count that runs to twelve
const LANGS = ['es', 'de', 'fr', 'he', 'it', 'pt', 'ar', 'nl'];
shot('s15', {
  build(set, layer) {
    this.s = LANGS.map(l => slabOf(set, 'lang-' + l, 'ph/sched-phone', { w: 1040 }, { r: 24 }));
    const c = html(layer, '<div class="card"><div class="odo gtext"></div><div class="label" style="position:absolute;left:0;top:0;font-size:34px">languages</div></div>');
    this.odo = c.querySelector('.odo'); this.lab = c.querySelector('.label');
  },
  draw(t, d) {
    F.setCam(up({ x: 1000, y: 505, yaw: 4 - 2 * d, zoom: 1.04 + .03 * d }));
    F.air({ key: 1 }); F.setFloor({});
    const n = Math.min(7, Math.floor(d / .125));
    this.s.forEach((s, i) => s.put({ on: i === n, x: 850, y: 300, ry: 5 }));
    const v = Math.min(12, Math.round(lerp(4, 12, clamp(d / .875))));
    if (this.odo._t !== v) { this.odo._t = v; this.odo.textContent = v; }
    this.odo.style.transform = `translate(1480px,300px) scale(${(1 + .05 * Math.exp(-(d % .125) / .03)).toFixed(4)})`;
    this.lab.style.transform = 'translate(1492px,612px)';
    F.motes(t, 1, 0);
  },
});
// share it: four quick looks
plain('s16', 'embed-dialog', 'ph/import-box', { size: { w: 900 }, crop: [0, 330, 2208, 1420], cam: () => ({ roll: 4, zoom: 1.1, y: 500, x: 1000 }), put: () => ({ x: 1150, y: 320, ry: -6 }) });
shot('s17', {
  build(set) {
    // the schedule, sitting in somebody's own website (the page around it is drawn plain on purpose)
    this.s = new F.Slab({ w: 1000, h: 600, parent: set, dom: '<div class="fakesite"><i style="width:26%;height:26px"></i><i style="width:44%;height:54px;margin-top:34px"></i><i style="width:36%;height:20px;margin-top:18px"></i><i style="width:40%;height:20px;margin-top:12px"></i></div>' });
    const pl = PLATES['gp-indigo-list'], r = LST.rows, c = [0, r[0].rect[1] - 40, 2760, 3000], k = 470 / c[2], host = this.s.dom.querySelector('.fakesite');
    const wrap = mk('div', null, host); Object.assign(wrap.style, { position: 'absolute', right: '40px', top: '34px', width: '470px', height: '532px', borderRadius: '16px', overflow: 'hidden', boxShadow: '0 12px 40px -12px rgba(13,22,48,.45)' });
    const im = mk('img', null, wrap); im.decoding = 'sync'; im.alt = ''; im.src = pl.src; Object.assign(im.style, { position: 'absolute', maxWidth: 'none', width: pl.w * k + 'px', height: pl.h * k + 'px', left: -c[0] * k + 'px', top: -c[1] * k + 'px' });
  },
  draw(t, d) { F.setCam(up({ x: 1000, y: 500, roll: -4, zoom: 1.1 })); F.air({}); F.setFloor({}); this.s.put({ x: 1150, y: 320, ry: 6, dim: .2, spill: 0 }); F.pool(F.bg, [1150, FLOOR, 120], 900, 260, '160,180,230', .2); F.motes(t, 1, 0); },
});
plain('s18', 'graphic', 'ph/graphic', { size: { h: 600 }, cam: () => ({ roll: 4, zoom: 1.1, y: 500, x: 1000 }), put: () => ({ x: 1150, y: 320, ry: -6 }) });
plain('s19', 'event-phone', 'ph/sched-phone', { size: { h: 700 }, cam: () => ({ roll: -4, zoom: 1.1, y: 500, x: 1000 }), put: () => ({ x: 1150, y: 330, ry: 10 }) });
// the newsletter: the email itself, and the app's own word that it went
shot('s20', {
  build(set) {
    this.s = slabOf(set, 'newsletter-email', 'ph/newsletter', { h: 640 }, { crop: [0, 0, 1608, 1960], r: 22 });
    this.sent = slabOf(set, 'newsletter-sent', 'ph/checkin-stats', { w: 900 }, { r: 20 });
  },
  draw(t, d) {
    const v = E.io(range(d, .3, 1.3));
    F.setCam(up({ x: lerp(1040, 1200, v) + 12 * d, y: 505, yaw: lerp(7, -4, v), zoom: 1 + .02 * d }));
    F.air({ key: 1.1 }); F.setFloor({});
    const send = d - .5, sent = d - 1;
    this.s.put({ x: 860, y: 322, ry: 7, s: 1 - (send > 0 ? .03 * Math.exp(-send / .08) : 0), glint: range(d, .5, 1.1) });
    const a = spring(sent, 2.2, .58);
    this.sent.put({ on: sent > 0, x: 1500, y: 330 + 40 * (1 - clamp(a)), z: 60, ry: -9, s: lerp(.88, 1, clamp(a)), dim: .04 });
    // it leaves: light running off toward the people it is for
    if (send > 0 && send < .7) { const g = F.fx; g.save(); g.globalCompositeOperation = 'lighter'; for (let i = 0; i < 14; i++) { const u = clamp((send - i * .02) / .5); if (u <= 0 || u >= 1) continue; const p = F.project(860 + 280 + 900 * E.out(u), 322 - 240 + i * 36 + 60 * Math.sin(i * 1.7) * u, 60 + 200 * u); const r = 9 * (1 - u) + 3; const gr = g.createRadialGradient(p[0], p[1], 0, p[0], p[1], r * 3); gr.addColorStop(0, rgba('255,255,255', .9 * (1 - u))); gr.addColorStop(1, rgba(C.sky, 0)); g.fillStyle = gr; g.beginPath(); g.arc(p[0], p[1], r * 3, 0, 6.2832); g.fill(); } g.restore(); }
    F.motes(t, 1, 0);
  },
});
plain('s21', 'realtime-light', 'ph/checkin-recent', { size: { w: 1080 }, put: (d) => ({ x: 1190, y: 312, ry: -6, glint: range(d, .2, .9) }), cam: (d, u) => ({ y: 505, x: 1010 + 20 * u, zoom: lerp(1, 1.05, u) }) });
shot('s22', {
  build(set) { this.s = slabOf(set, 'realtime-rail-light', 'ph/checkin-recent', { h: 640 }, { crop: [0, 0, 1074, 1480], r: 22 }); this.hl = this.s.box([30, 668, 1014, 150], 'ring'); },
  draw(t, d) {
    F.setCam(up({ x: 1040 + 14 * d, y: 500, yaw: lerp(-6, -3, d / 1.5), zoom: 1.12 + .03 * d }));
    F.air({ key: 1.1 }); F.setFloor({});
    this.s.put({ x: 1180, y: 320, ry: 6 });
    const q = d - .5; this.hl.style.opacity = q > 0 ? clamp(q / .06) : 0; this.hl.style.transform = `scale(${(1 + .12 * Math.exp(-Math.max(0, q) / .08)).toFixed(4)})`;
    if (q > 0) warm(this.s.pt(537, 743, 8), 340, .22 * (.5 + .5 * Math.exp(-q / .3)));
    F.motes(t, 1, 0);
  },
});

/* ====================== sell: 25 to 32 ====================== */
// The seat picker, as the app shows it for Jazz Night: 150 seats, two tapped on the beat. They
// stay warm for the rest of the film.
const PK = D.picker, PCROP = [0, 640, 1782, 1700];
const seatIn = (n) => PK.seats.find(x => x.row === 'C' && x.number === n);
const C7 = seatIn(7), C8 = seatIn(8);
shot('s24', {
  build(set) {
    this.s = slabOf(set, 'picker-0', 'ph/seatmap', { h: 650 }, { crop: PCROP, r: 22 });
    this.f1 = farOf(set, 'gp-indigo-top', 'ph/calendar', { h: 520 }); this.f2 = farOf(set, 'event-jazz', 'ph/tickets', { h: 520 });
  },
  draw(t, d) {
    F.setCam(up({ x: 1010 + 13 * d, y: 505, yaw: lerp(9, 5, E.out(d / 2)), zoom: lerp(1, 1.07, d / 2) }));
    F.air({ key: 1.2 }); F.setFloor({});
    this.f1.put({ x: -160, z: -1000, ry: 26, o: .45, blur: 3 }); this.f2.put({ x: 2360, z: -820, ry: -30, o: .4, blur: 3 });
    this.s.put({ x: 1190, y: 372, ry: -7, glint: range(d, .1, .45) });
    this.s.show(d >= 1 ? 'picker-2' : d >= .5 ? 'picker-1' : 'picker-0');
    [[.5, C7], [1, C8]].forEach(([at, p]) => { const w = this.s.pt(p.cx, p.cy, 8), q = d - at; F.tap(t, t - d + at, w, { rgb: C.hot, k: .8 }); if (q > 0) warm(w, 80, .55 * (.6 + .4 * Math.exp(-q / .3))); });
    through(d);
    F.motes(t, 1, 0);
  },
});
// the button, pressed on the cut
shot('s26', {
  build(set) { this.s = slabOf(set, 'checkout-btn', 'ph/tickets', { w: 1240 }, { r: 26 }); },
  draw(t, d) {
    F.setCam(up({ x: 1000, y: 470, yaw: 3, zoom: 1.08 - .05 * Math.exp(-d / .06) }));
    F.air({ key: 1.2 }); F.setFloor({});
    this.s.put({ x: 1040, y: 380, ry: -4, s: 1 - .04 * Math.exp(-d / .07) });
    const c = this.s.pt(2418, 201, 8);
    F.tap(t, t - d, c, { k: 1.7 });
    F.pool(F.fx, c, 420, 150, '255,214,10', .34 * Math.exp(-d / .18));
    F.motes(t, 1, 0);
  },
});
// The ticket, in the two parts the app draws it in: the code, and the stub that tears off.
const TK = D.ticket.rects, TQ = TK.qr[0];
const ticketOf = set => {
  const b = TK.body[0], st = TK.stub[0], w = 392;
  const body = slabOf(set, 'ticket', 'ph/tickets', { w }, { crop: b, r: 30, d: 8 });
  const stub = slabOf(set, 'ticket', 'ph/tickets', { w }, { crop: [st[0], st[1], st[2], 760], r: 30, d: 8 });
  const k = w / TQ[2] * (TQ[2] / b[2]);
  const qr = new F.Slab({ w: TQ[2] * body.k(), h: TQ[3] * body.k(), plate: 'ticket-qr', parent: set, r: 4, d: 1, glow: false, reflect: false }); qr.img.style.imageRendering = 'pixelated';
  return { body, stub, qr, put(o) {
    const x = o.x, top = o.top == null ? 60 : o.top, ry = o.ry || 0, a = (o.hinge || 0) * F.DEG, hb = body.h, hs = stub.h, s = o.s || 1;
    body.put({ x, y: top + hb / 2, z: o.z || 0, ry, glint: o.glint, s, spill: o.spill, dim: .04 });
    stub.put({ on: o.stub !== false, x, y: top + hb + 6 + hs / 2 * Math.cos(a), z: (o.z || 0) + hs / 2 * Math.sin(a), ry, rx: (o.hinge || 0), s, spill: 0, dim: .04 });
    const c = body.pt(TQ[0] + TQ[2] / 2, TQ[1] + TQ[3] / 2, 3); qr.put({ on: !!o.sharp, x: c[0], y: c[1], z: c[2], ry, spill: 0, dim: 0 });
    return c;
  } };
};
shot('s27', {
  build(set) { this.tk = ticketOf(set); },
  draw(t, d) {
    const u = E.out(d / 2.5), rack = E.io(range(d, 1.9, 2.5));
    F.setCam(up({ x: 900 + 30 * u, y: lerp(500, 450, rack), yaw: lerp(-24, -8, u), zoom: lerp(1, 1.34, rack) }));
    F.air({ key: 1.2 }); F.setFloor({});
    const hinge = 9 * (spring(d - 1.5, 2.4, .5)) * (d > 1.5 ? 1 : 0);
    const c = this.tk.put({ x: 1180, top: 40, ry: 6, glint: range(d, .45, 1.0), s: 1 + .03 * Math.exp(-d / .08), hinge, sharp: rack > .3 });
    if (d > .5 && d < 1.1) F.pool(F.fx, c, 300, 220, C.ok, .32 * (1 - (d - .5) / .6));
    F.motes(t, 1, 0);
  },
});
// into the code, until its squares fill the frame, and the dark
shot('s28', {
  build(set) { this.tk = ticketOf(set); },
  draw(t, d) {
    const p = range(d, 0, .78), u = p * p, e = 1 - Math.pow(1 - p, 3);
    const c = this.tk.put({ x: 1180, top: 40, ry: 6 * (1 - e), hinge: 9, sharp: true });
    const z = 1.34 * Math.pow(26 / 1.34, u), k = 1.34 * (1 - e) / z;
    F.setCam(up({ x: c[0] + (930 - c[0]) * k, y: c[1] + 28 + (422 - c[1]) * k, yaw: lerp(-8, 0, e), zoom: z }));
    F.air({ key: 1.2 }); F.setFloor({});
    this.tk.put({ x: 1180, top: 40, ry: 6 * (1 - e), hinge: 9, sharp: true });
    dark(range(d, .68, .76));
  },
});

/* "0%": lit type standing on the mirror floor */
shot('s29', {
  build(set) {
    const z = t => html(set, `<div class="zero ${t} gtext">0%</div>`), p = t => html(set, `<div class="pf ${t}">platform fees</div>`);
    this.z = z(''); this.zr = z('r'); this.p = p(''); this.pr = p('r'); this.X = 330; this.base = FLOOR - 150;
  },
  draw(t, d) {
    const X = this.X, kick = .06 * Math.exp(-d / .09), fr = Math.floor(t * 60), sh = Math.exp(-d / .08);
    // through the zero, into the dark
    const push = E.inExpo(range(d, 1.1, 1.5));
    F.setCam({ x: lerp(960, X + 236, push) + (hash(fr) - .5) * 9 * sh, y: lerp(470, FLOOR - 430, push) + (hash(fr + 5) - .5) * 9 * sh, z: 0, yaw: lerp(-5, -3, d / 1.5), pitch: 6, zoom: (1 + kick + .013 * d) * lerp(1, 7.5, push), P: 2600 });
    F.air({ key: 1.5 }); F.setFloor({ a: .4 + .5 * Math.exp(-d / .25), fog: 2600 });
    // the zero and the percent stand on the line; "platform fees" stands on the floor before them
    const zy = this.base, set = (e, y, refl) => { e.style.transformOrigin = '0 0'; e.style.transform = `translate3d(${X}px,${y}px,0)` + (refl ? ' scaleY(-1)' : ''); };
    if (!this.h) { this.h = this.z.offsetHeight; this.ph = this.p.offsetHeight; }
    set(this.z, zy - this.h); set(this.zr, FLOOR + (FLOOR - zy) + this.h, true);
    set(this.p, FLOOR - this.ph - 6); set(this.pr, FLOOR + this.ph + 6, true);
    this.p.style.opacity = E.out(range(d, .12, .3)); this.pr.style.opacity = .16 * E.out(range(d, .12, .3));
    const fade = 1 - range(d, 1.3, 1.46); this.z.style.opacity = fade; this.zr.style.opacity = .22 * fade;
    F.pool(F.bg, [X + 520, FLOOR - 380, -260], 1200, 620, C.key, .34 + .5 * Math.exp(-d / .2));
    F.pool(F.bg, [X + 520, FLOOR, 140], 1100, 300, C.sky, .2 + .4 * Math.exp(-d / .2));
    F.shock(t, t - d, [X + 520, 0, 0], { r1: 3000, a: .7 });
    F.shock(t, t - d + .09, [X + 520, 0, 0], { r1: 2200, a: .35, life: .9 });
    const [sx, sy] = F.project(X + 300, zy - 300, 0); F.streak(sx, sy, .95 * Math.exp(-d / .22));
    F.motes(t, 1, 0);
    wash(.55 * Math.exp(-d / .06), '#dbe8ff');
    dark(range(d, 1.38, 1.5));
  },
});
// the money: real sales, and the count that sold out
shot('s30', {
  build(set) {
    this.rows = slabOf(set, 'sales-light', 'ph/checkin-recent', { w: 1180 }, { r: 22 });
    this.sold = slabOf(set, 'ph/checkin-stats', 'ph/checkin-stats', { w: 980 }, { r: 20 });
  },
  draw(t, d) {
    F.setCam(up({ x: 1010 + 16 * d, y: 505, yaw: lerp(-7, -3, E.out(d / 2.5)), zoom: 1 + .016 * d }));
    F.air({ key: 1.2 }); F.setFloor({});
    const a = spring(d - .1, 2.1, .6), b = spring(d - .32, 2.1, .6);
    this.sold.put({ x: 1160, y: 70 - 30 * (1 - clamp(a)), z: -60, ry: 6, s: lerp(.9, 1, clamp(a)), dim: lerp(.9, .2, clamp(a * 2)), reflect: false });
    this.rows.put({ x: 1130, y: 378 + 40 * (1 - clamp(b)), ry: 7, s: lerp(.92, 1, clamp(b)), dim: lerp(.9, .2, clamp(b * 2)) });
    dark(1 - range(d, 0, .1));
    F.motes(t, 1, 0);
  },
});

/* selfhost: a terminal types the README's own two lines, and the app comes up behind it */
shot('s31', {
  build(set) {
    this.term = new F.Slab({ w: 1180, h: 500, dom: '<div class="term"></div>', parent: set, r: 18 });
    this.body = this.term.dom.querySelector('.term');
    this.app = slabOf(set, 'gp-indigo-top', 'ph/event-card', { h: 540 });
  },
  draw(t, d) {
    F.setCam({ x: 900 + 24 * d, y: 470, yaw: lerp(-9, -5, E.out(d / 2)), pitch: 8, zoom: lerp(.94, .98, d / 2) });
    F.air({}); F.setFloor({});
    const L1 = 'git clone https://github.com/eventschedule/dockerfiles.git', L2 = 'docker compose up --build -d';
    const n1 = Math.round(L1.length * range(d, .1, .62)), n2 = Math.round(L2.length * range(d, .8, 1.18)), up = d >= 1.5;
    const caret = '<span class="k"></span>';
    const txt = `<span class="p">$</span> ${L1.slice(0, n1)}${n1 < L1.length ? caret : ''}` + (d >= .72 ? `\n<span class="p">$</span> ${L2.slice(0, n2)}${n2 < L2.length || !up ? caret : ''}` : '') + (up ? `\n<span class="o">The app is at</span> <span class="u">http://localhost:8080</span>` : '');
    if (this.body._t !== txt) { this.body._t = txt; this.body.innerHTML = txt; }
    const a = spring(d - 1.5, 2, .55);
    this.app.put({ x: 1560, y: 430, z: -520, ry: -24, s: lerp(.86, 1, a), dim: lerp(.9, .1, clamp(a)), glow: .5 * clamp(a), spill: clamp(a), ro: .3 * clamp(a) });
    this.term.put({ x: 900, y: 330, ry: 8, spill: 0 });
    F.pool(F.bg, [900, FLOOR, 140], 900, 240, C.key, .16);
    F.motes(t, 1, 0);
  },
});

/* ====================== the door: 38 to 41 ====================== */
shot('s32', {
  build(set) {
    const ps = [['gp-indigo-top', 'ph/event-card'], ['realtime-light', 'ph/calendar'], ['event-jazz', 'ph/checkin-recent'], ['sales-light', 'ph/event-card'], ['gp-riverside-top', 'ph/calendar'], ['gp-indigo-month', 'ph/calendar']];
    this.sl = ps.map(([p, dv]) => slabOf(set, p, dv, { h: 560 }, { d: 12 }));
    this.door = html(set, '<div class="door"></div>');
    this.lab = html(set, '<div class="label" style="position:absolute;left:0;top:0;font-size:44px;color:#ffd9a8;white-space:nowrap;letter-spacing:.2em">TONIGHT · DOORS 8:00 PM</div>');
  },
  draw(t, d) {
    // down the hall: screens on both walls, the door at the far end (the homepage hero's own room)
    F.setCam({ x: 960, y: 470, z: lerp(0, -160, E.out(d)), yaw: 0, pitch: 3, zoom: 1, P: 1200 });
    F.air({ k: lerp(1, .3, range(d, 0, .6)) }); F.setFloor({ a: lerp(.4, .2, range(d, 0, .6)), fog: 3200 });
    // they die nearest first, on both walls at once
    this.sl.forEach((s, i) => {
      const side = i % 2 ? 1 : -1, row = Math.floor(i / 2), k = 1 - clamp((d - .04 - row * .13) / .14);
      s.put({ x: 960 + side * 880, y: 470, z: -260 - row * 760, ry: side * -62, dim: lerp(.95, .12, k), glow: .5 * k, spill: k, ro: .3 * k });
    });
    const op = E.out(range(d, .42, .9)), w = 360 * op, h = 760, dx = 960, dz = -2100;
    Object.assign(this.door.style, { width: w.toFixed(1) + 'px', height: h + 'px', transform: `translate3d(${(dx - w / 2).toFixed(1)}px,${FLOOR - h}px,${dz}px)` });
    vis(this.door, op > .01);
    F.pool(F.bg, [dx, FLOOR - 300, dz - 60], 1300, 1100, C.warm, .42 * op);
    F.pool(F.bg, [dx, FLOOR, dz + 1200], 420 + 300 * op, 2600, C.warm, .34 * op);
    F.pool(F.bg, [dx, FLOOR, dz + 300], 300, 700, C.hot, .3 * op);
    if (!this.lw) this.lw = this.lab.offsetWidth;
    this.lab.style.transform = `translate3d(${dx - this.lw / 2}px,${FLOOR - h - 110}px,${dz}px)`; this.lab.style.opacity = E.out(range(d, .62, .9));
    F.motes(t, .6, 0);
  },
});

// at the door: the code on the ticket, the scanner on it, and the app's own answer
const VIEW = [1341, 966, 784, 772];          // the scan page's camera frame, in its pixels
shot('s33', {
  build(set) { this.tk = ticketOf(set); },
  draw(t, d) {
    const c = this.tk.put({ x: 1180, top: 40, ry: 4, hinge: 9, sharp: true, spill: .5 });
    F.setCam(up({ x: c[0] - 150, y: c[1] + 60, yaw: -4, zoom: 2.3 + .08 * d, P: 1700 }));
    F.air({ k: .3 }); F.setFloor({ a: .2 });
    this.tk.put({ x: 1180, top: 40, ry: 4, hinge: 9, sharp: true, spill: .5 });
    F.pool(F.bg, [300, FLOOR - 300, -900], 700, 900, C.warm, .22); F.motes(t, .5, 0);
  },
});
shot('s34', {
  build(set) {
    this.s = slabOf(set, 'scan', 'ph/import-box', { h: 600 }, { crop: [575, 429, 2302, 1783], r: 26 });
    // what the camera sees: the ticket's code, held up to it
    const v = pad(VIEW, -90), e = this.code = this.s.box(v, null, { background: '#fff', borderRadius: '10px', overflow: 'hidden' }), im = mk('img', null, e);
    im.decoding = 'sync'; im.alt = ''; im.src = PLATES['ticket-qr'].src; Object.assign(im.style, { position: 'absolute', inset: '6%', width: '88%', height: '88%', imageRendering: 'pixelated' });
    this.beam = this.s.box([VIEW[0], VIEW[1], VIEW[2], 14], 'scanblade');
  },
  draw(t, d) {
    const fr = Math.floor(t * 60);
    F.setCam(up({ x: 1100 + (hash(fr) - .5) * 5, y: 500 + (hash(fr + 3) - .5) * 5, yaw: 5, zoom: 1.16 + .04 * d, roll: (hash(fr + 7) - .5) * .5, P: 1700 }));
    F.air({ k: .3 }); F.setFloor({ a: .2 });
    this.s.put({ x: 1180, y: 320, ry: -5, spill: .5 });
    const k = lerp(1.25, 1, E.out(clamp(d / .2))); this.code.style.transform = `scale(${k.toFixed(4)}) rotate(${((1 - E.out(clamp(d / .25))) * -5).toFixed(2)}deg)`; this.code.style.opacity = clamp(d / .08);
    this.beam.style.top = this.s.loc(0, VIEW[1] + E.io(clamp(d / .5)) * (VIEW[3] - 14))[1] + 'px';
    F.pool(F.bg, [300, FLOOR - 300, -900], 700, 900, C.warm, .22); F.motes(t, .5, 0);
  },
});
shot('s35', {
  build(set) { this.s = slabOf(set, 'scan-ok', 'ph/checkin-stats', { h: 590 }, { crop: [600, 840, 2256, 1140], r: 26 }); },
  draw(t, d) {
    // it whips down toward the floor as it leaves, into the seat map
    const w = E.inExpo(range(d, .78, 1));
    F.setCam(up({ x: 1090, y: 500 + 500 * w, yaw: 4, pitch: 7 + 40 * w, zoom: (1.02 + .04 * d) * (1 + .5 * w), P: 1700 }));
    F.air({ k: .4 }); F.setFloor({ a: .2 });
    this.s.put({ x: 1130, y: 322, ry: -5, s: 1 + .04 * Math.exp(-d / .07), spill: 1.6 });
    wash(.34 * Math.exp(-d / .08), '#bff5dd');
    F.pool(F.fx, [1180, 316, 40], 760, 340, C.ok, .3 * Math.exp(-d / .3));
    F.pool(F.bg, [300, FLOOR - 300, -900], 700, 900, C.warm, .22); F.motes(t, .5, 0);
  },
});

/* ====================== the room: 41 to 58 ======================
   The set piece, and everything after it, which never leaves the room. The plan on the floor is
   the app's own seat map, in its dark scheme. */
const ROOMSET = (() => {
  const K = room.K, W = room.w, H = room.h, set = mk('div', 'set', world);
  const flat = (e, lift) => { Object.assign(e.style, { position: 'absolute', left: 0, top: 0, width: W + 'px', height: H + 'px', transformOrigin: '0 0', transform: `translate3d(${room.PX(0)}px,${FLOOR - lift}px,${room.PZ(0)}px) rotateX(90deg)` }); return e; };
  const pic = id => { const e = mk('img', null, set); e.decoding = 'sync'; e.alt = ''; e.src = PLATES[id].src; return e; };
  const dark = flat(pic('seatmap-sold-6x-dark'), 1);
  // the stage: the plan's own block, with height
  const st = SEATS.stage, sw = st.w * K, sd = st.h * K, sx = room.PX(st.x), sz = room.PZ(st.y);
  const top = html(set, '<div style="position:absolute;left:0;top:0;transform-origin:0 0;background:radial-gradient(ellipse 60% 90% at 50% 50%,#4a4f78,#232f5c 70%,#16203f);border:2px solid rgba(214,226,255,.62);border-radius:10px;display:grid;place-items:center;font-family:var(--label);font-weight:500;letter-spacing:.3em;font-size:30px;color:#c3d2f4">STAGE</div>');
  // the front of the stage catches the wash along its lip
  const front = html(set, '<div style="position:absolute;left:0;top:0;transform-origin:0 100%;background:linear-gradient(180deg,rgba(255,206,140,.95) 0,rgba(255,180,94,.5) 5%,#22305e 16%,#0b1228);border-radius:0 0 8px 8px"></div>');
  Object.assign(top.style, { width: sw + 'px', height: sd + 'px' }); Object.assign(front.style, { width: sw + 'px', height: '100px' });
  // the screens that stand round the room once the camera pulls back
  const ring = ['gp-indigo-top', 'event-jazz', 'gp-indigo-month', 'realtime-light', 'sales-light', 'gp-late-laughs-top', 'gp-riverside-top', 'import-parsed-light'].map(p => farOf(set, p, 'ph/calendar', { h: 520 }));
  return { set, dark, top, front, sx, sz, sw, sd, ring };
})();
// o.plan: 0 for no plan (the lights alone, for type to sit on).
function drawRoom(t, o = {}) {
  const R = ROOMSET, rise = o.rise == null ? 1 : o.rise, H = 86 * rise, on = o.plan !== 0;
  vis(R.set, true); vis(R.dark, on);
  vis(R.top, on && rise > .003); vis(R.front, on && rise > .003);
  R.top.style.transform = `translate3d(${R.sx}px,${FLOOR - 3 - H}px,${R.sz}px) rotateX(90deg)`; R.top.style.opacity = clamp(rise * 4);
  R.front.style.transform = `translate3d(${R.sx}px,${FLOOR - 100}px,${R.sz + R.sd}px) scaleY(${Math.max(.001, H / 100).toFixed(4)})`;
  // the stage wash: light coming down onto the stage, and the haze it lifts
  const w = o.wash || 0, cx = R.sx + R.sw / 2, cz = R.sz + R.sd / 2;
  if (w > .003) {
    const g = F.bg; g.save(); g.globalCompositeOperation = 'lighter'; g.filter = 'blur(26px)';
    for (let i = 0; i < 7; i++) {
      const u = i / 6 - .5, bx = cx + u * R.sw * 1.05, tp = F.project(cx + u * 300, FLOOR - 1700, cz - 260), a = F.project(bx - 70, FLOOR - H, cz), b = F.project(bx + 70, FLOOR - H, cz);
      const gr = g.createLinearGradient(tp[0], tp[1], (a[0] + b[0]) / 2, a[1]); const rgb = i % 2 ? C.hot : '176,206,255';
      gr.addColorStop(0, rgba(rgb, 0)); gr.addColorStop(.5, rgba(rgb, .05 * w)); gr.addColorStop(1, rgba(rgb, .2 * w));
      g.fillStyle = gr; g.beginPath(); g.moveTo(tp[0] - 6, tp[1]); g.lineTo(tp[0] + 6, tp[1]); g.lineTo(b[0], b[1]); g.lineTo(a[0], a[1]); g.closePath(); g.fill();
    }
    g.restore();
    F.pool(F.bg, [cx, FLOOR - H - 40, cz], R.sw * .8, 260, C.hot, .42 * w);
    F.pool(F.bg, [cx, FLOOR - 520, cz - 300], 1500, 900, C.warm, .16 * w);
  }
  // the warmth of the room itself grows with the people in it
  const inn = TL.through(t) / TL.ROOM.in;
  F.pool(F.bg, [960, FLOOR, 0], 1700, 760, C.warm, (o.warm == null ? .16 : o.warm) * inn);
  room.draw(t, o);
  R.ring.forEach((f, i) => {
    const show = (o.ring || 0) > .003; if (!show) { f.put({ on: false }); return; }
    const a = (-150 + i * (300 / 7)) * F.DEG - Math.PI / 2, rad = 2100, x = 960 + Math.cos(a) * rad * 1.15, z = -360 + Math.sin(a) * rad;
    f.put({ x, z, ry: -(i - 3.5) * 13, o: .5 * o.ring, blur: 2, y: FLOOR - f.h / 2 });
  });
}
const seatMid = [(room.PX(room.c7.x) + room.PX(room.c8.x)) / 2, room.PZ(room.c7.y)];
const wide = d => ({ x: 800, y: FLOOR, z: 340, pitch: 30, yaw: -9, zoom: .95 * (1 + .012 * d), P: 1500 });
// The close on the seat map (27.0) is the plan on the floor seen from straight above, and the room
// shot opens on that same frame fourteen seconds later: the two seats the buyer chose, warm.
const above = x => ({ x, y: FLOOR, z: seatMid[1], pitch: 90, yaw: 0, zoom: 3.1, P: 2600 });
shot('s25', {
  draw(t, d) {
    F.setCam(above(seatMid[0] - 50 + 50 * E.out(d)));
    F.air({ k: .2 }); F.setFloor({ a: .16, fog: 2600 });
    drawRoom(100, { rise: 0, wash: 0, warm: 0, max: 2, calm: true });
  },
});
shot('s36', {
  build(set, layer) {
    const p = PLATES['ph/checkin-stats'];
    this.card = html(layer, `<div class="hudcard" style="width:720px;height:${(720 * p.h / p.w).toFixed(1)}px"><img alt="" decoding="sync" src="${p.src}"></div>`);
  },
  draw(t, d) {
    const u = E.io(range(d, .5, 2.5)), w = wide(Math.max(0, d - 2.5)), a0 = above(seatMid[0]);
    F.setCam({ x: lerp(a0.x, w.x, u), y: lerp(a0.y, w.y, u), z: lerp(a0.z, w.z, u), pitch: lerp(a0.pitch, w.pitch, u), yaw: lerp(0, w.yaw, u), zoom: lerp(a0.zoom, w.zoom, E.out(u)), P: lerp(a0.P, w.P, u) });
    F.air({ k: lerp(.2, .9, u) }); F.setFloor({ a: lerp(.16, .3, u), fog: 2600 });
    const pk = t - TL.ROOM.peak;
    drawRoom(t, { rise: E.io(range(d, .6, 2.2)), wash: pk > 0 ? lerp(.35, 1, E.out(clamp(pk / .5))) : .35 * range(d, 1.6, 2.6) * (TL.through(t) / 142) });
    // the door's real card, once its numbers are true
    const ca = spring(pk - .25, 1.7, .6), e = this.card, on = pk > .25;
    vis(e, on); if (on) { e.style.opacity = clamp(ca * 2.5); e.style.transform = `translate(1090px,${(96 - 26 * (1 - clamp(ca))).toFixed(1)}px) perspective(1600px) rotateY(-9deg) rotateX(4deg) scale(${lerp(.94, 1, clamp(ca)).toFixed(4)})`; }
    if (pk > 0) wash(.22 * Math.exp(-pk / .1), '#ffe2b8');
    else wash(Math.max(0, .35 * Math.exp(-d / .06)), '#bff5dd');
    F.motes(t, .7 * u, 0);
  },
});
shot('s37', {
  build(set, layer) {
    const cls = ['a', 'b gtext', 'c', 'b', 'a gtext', 'c', 'b', 'c', 'a', 'b gtext'];
    const c = html(layer, `<div class="card"><div class="veil" style="opacity:.55"></div><div class="more">${TL.MORE.map((n, i) => `<span class="${cls[i]}">${F.esc(n)}</span>`).join('')}</div></div>`);
    this.n = [...c.querySelectorAll('.more span')];
  },
  draw(t, d) {
    const u = E.out(range(d, 0, 2)), w = wide(5 + d);
    F.setCam({ ...w, x: w.x - 620 * u, y: w.y - 560 * u, z: w.z + 200 * u, pitch: w.pitch + 2 * u, zoom: w.zoom * lerp(1, .6, u), yaw: w.yaw + 3 * u });
    F.air({ k: .9 }); F.setFloor({ a: .26, fog: 3400 });
    drawRoom(t, { wash: 1, ring: E.out(range(d, 0, .5)) });
    this.n.forEach((e, i) => { const q = t - TL.MORE_T[i]; e.style.transform = `scale(${lerp(1.3, 1, E.outExpo(clamp(q / .22))).toFixed(4)})`; e.style.opacity = clamp(q / .04); });
    F.motes(t, .7, 0);
  },
});
// the lights, out of focus, for type to sit on: close and low, so the discs are large and apart
const soft = (t, d, k = 1) => {
  F.setCam({ x: 420 + 10 * d, y: FLOOR - 190, z: 300, pitch: 8, yaw: 20 - .5 * d, zoom: 1.34 * (1 + .006 * d), P: 1300 });
  F.air({ k: .55 }); F.setFloor({ on: false });
  drawRoom(t, { wash: .5, defocus: k, plan: 0, rise: 1, warm: .3, lift: 60, k: 2.4, every: 3 });
};
shot('s38', {
  build(set, layer) {
    const c = html(layer, `<div class="card"><div class="veil"></div><div class="stmt">${maskLine('Free and open source.')}${maskLine('Forever.', 'gtext')}</div></div>`);
    this.lines = [...c.querySelectorAll('.mask>span')];
  },
  draw(t, d) { soft(t, d, .8); riseAll(this.lines, d, .04, .16, .36); },
});
shot('s39', {
  build(set, layer) {
    const c = html(layer, `<div class="card end"><div class="veil"></div>${lockup()}<div class="h1">${maskLine('Plan, promote, and sell')}${maskLine('from your event calendar', 'gtext')}</div><div class="pill">Free event calendar. No credit card.</div><div class="fine">Set up in under 2 minutes. Open source, and yours to selfhost.</div><div class="site">eventschedule.com</div></div>`);
    this.lock = c.querySelector('.lock'); this.e = c.querySelector('.e'); this.sp = c.querySelector('.s'); this.w = c.querySelector('.w');
    this.lines = [...c.querySelectorAll('.h1 .mask>span')]; this.pill = c.querySelector('.pill'); this.fine = c.querySelector('.fine'); this.site = c.querySelector('.site');
  },
  draw(t, d) {
    soft(t, 2 + d, 1);
    const a = spring(d - .02, 2.1, .6), sl = (1 - E.outExpo(range(d, .02, .5))) * 40;
    this.lock.style.transform = `translate(150px,176px) scale(${lerp(.9, 1, a).toFixed(4)})`; this.lock.style.opacity = clamp(d / .08);
    this.e.setAttribute('transform', `translate(${-.51 * sl} ${.86 * sl})`); this.sp.setAttribute('transform', `translate(${.51 * sl} ${-.86 * sl})`);
    this.w.style.opacity = E.out(range(d, .14, .5)); this.w.style.transform = `translateX(${((1 - E.rise(range(d, .14, .6))) * 26).toFixed(2)}px)`;
    riseAll(this.lines, d, .5, .25, .38);
    const up = (e, at, y = 22) => { const r = E.rise(range(d, at, at + .5)); e.style.opacity = clamp((d - at) / .2); e.style.transform = `translateY(${((1 - r) * y).toFixed(2)}px)`; };
    up(this.pill, 1.5); up(this.fine, 2); up(this.site, 2);
    wash(.3 * Math.exp(-d / .09), '#dbe8ff');
  },
});

/* ====================== the director ====================== */
const built = {};
function ensure(s) {
  if (built[s.id]) return built[s.id];
  const def = DEFS[s.id] || {}, set = mk('div', 'set', world), layer = mk('div', 'layer', hud);
  set.dataset.shot = layer.dataset.shot = s.id;
  if (def.build) def.build.call(def, set, layer);
  return built[s.id] = { def, set, layer };
}
TL.SHOTS.forEach(ensure);
let shown = null;
F.film = t => {
  const s = TL.shotAt(t), b = ensure(s);
  if (shown !== s.id) { for (const id in built) { const on = id === s.id; vis(built[id].set, on); vis(built[id].layer, on); } shown = s.id; }
  dark(0); wash(0); vis(ROOMSET.set, false);
  if (b.def.draw) b.def.draw.call(b.def, t, t - s.a, s);
  else { F.setCam({}); F.air({}); F.setFloor({}); }
  F.caption(t, s);
  return s;
};
/* ====================== the thumbnail ======================
   Not a frame of the film: a still composed for 1280x720, where YouTube lays a play button on the
   centre and a duration on the bottom right. `?thumb=1` draws it (render.mjs --query=thumb=1). */
const thumbLayer = html(hud, `<div class="layer thumb" style="display:none"><div class="veil" style="background:radial-gradient(ellipse 1300px 900px at 22% 46%,rgba(3,5,11,.9),rgba(3,5,11,.55) 55%,rgba(3,5,11,0) 100%)"></div>${lockup()}<div class="tz gtext">0%</div><div class="tp">platform fees</div><div class="pill" style="left:126px;top:868px;font-size:40px;padding:18px 36px 20px 28px">Open source event calendar</div></div>`);
F.thumb = () => {
  for (const id in built) { vis(built[id].set, false); vis(built[id].layer, false); } shown = null;
  dark(0); wash(0); vis(thumbLayer, true);
  const t = 47.4, w = wide(1.4);
  F.setCam({ ...w, x: w.x - 520, z: w.z - 40, zoom: w.zoom * 1.06, yaw: -14 });
  F.air({ k: .9 }); F.setFloor({ a: .3, fog: 2600 });
  drawRoom(t, { wash: 1 });
  F.motes(t, .7, 0);
  thumbLayer.querySelector('.lock').style.transform = 'translate(126px,84px) scale(1.2)'; thumbLayer.querySelector('.lock').style.transformOrigin = '0 0';
  F.caption(0, { i: -1 });
};
// Windows fast enough that the renderer spends more motion-blur samples on them: the pushes
// through the three full stops and the zero, the whips, the crane, the rush.
F.FAST = [[2, 2.5], [6.7, 7.2], [14.7, 15.1], [20.9, 21.2], [24.7, 25.1], [28.4, 28.7], [31, 32.1], [33.1, 33.5], [40.8, 43.6], [44, 46.1]];
})();
