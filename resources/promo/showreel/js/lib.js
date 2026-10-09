// Shared maths, easing and DOM helpers for the film. Everything here is a pure function of its
// arguments: the film draws any frame from t alone, so nothing may remember the frame before.
'use strict';
const F = window.F = {};
F.W = 1920; F.H = 1080;
F.Q = new URLSearchParams(location.search);
F.RENDER = F.Q.has('render');
F.LIGHT = document.documentElement.dataset.theme === 'light';

/* ---------- math ---------- */
const clamp = (x, a = 0, b = 1) => Math.min(b, Math.max(a, x));
const lerp = (a, b, t) => a + (b - a) * t;
const range = (t, a, b) => clamp((t - a) / (b - a));
const DEG = Math.PI / 180;
function bezier(x1, y1, x2, y2) {
  const cx = 3 * x1, bx = 3 * (x2 - x1) - cx, ax = 1 - cx - bx;
  const cy = 3 * y1, by = 3 * (y2 - y1) - cy, ay = 1 - cy - by;
  const sx = s => ((ax * s + bx) * s + cx) * s, sy = s => ((ay * s + by) * s + cy) * s;
  const dx = s => (3 * ax * s + 2 * bx) * s + cx;
  return x => {
    if (x <= 0) return 0; if (x >= 1) return 1;
    let s = x;
    for (let i = 0; i < 8; i++) { const e = sx(s) - x, d = dx(s); if (Math.abs(e) < 1e-6 || !d) break; s -= e / d; }
    return sy(clamp(s));
  };
}
const E = {
  lin: t => t,
  hold: t => (t >= 1 ? 1 : 0),
  out: t => 1 - Math.pow(1 - t, 3),
  in: t => t * t * t,
  io: t => t < .5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2,
  ioSine: t => .5 - .5 * Math.cos(Math.PI * t),
  outQuint: t => 1 - Math.pow(1 - t, 5),
  outExpo: t => t >= 1 ? 1 : 1 - Math.pow(2, -10 * t),
  inExpo: t => t <= 0 ? 0 : Math.pow(2, 10 * t - 10),
  ioExpo: t => t <= 0 ? 0 : t >= 1 ? 1 : t < .5 ? Math.pow(2, 20 * t - 10) / 2 : (2 - Math.pow(2, -20 * t + 10)) / 2,
  outBack: t => { const s = 1.70158; return 1 + (s + 1) * Math.pow(t - 1, 3) + s * Math.pow(t - 1, 2); },
  inBack: t => { const s = 1.70158; return (s + 1) * t * t * t - s * t * t; },
  rise: bezier(.22, 1, .36, 1),      // the homepage's own ease-out
  snap: bezier(.7, 0, .15, 1),
  // slow in, fast out: a wind-up
  antic: bezier(.5, 0, .9, .4),
};
// Damped spring from 0 to 1; dt in seconds since release.
function spring(dt, freq = 1.6, damp = .5) {
  if (dt <= 0) return 0;
  const w = 2 * Math.PI * freq, z = damp, wd = w * Math.sqrt(1 - z * z);
  return 1 - Math.exp(-z * w * dt) * (Math.cos(wd * dt) + (z * w / wd) * Math.sin(wd * dt));
}
// A decaying wobble around zero: what is left over after a move ends (follow-through).
function wobble(dt, freq = 3, decay = 5, phase = 0) {
  if (dt <= 0) return 0;
  return Math.exp(-decay * dt) * Math.sin(2 * Math.PI * freq * dt + phase);
}
// A bump that rises and falls once between a and b (sin half-wave).
const bump = (t, a, b) => { const u = range(t, a, b); return u <= 0 || u >= 1 ? 0 : Math.sin(Math.PI * u); };
function rng(seed) {
  let a = seed >>> 0;
  return () => { a = (a + 0x6D2B79F5) >>> 0; let t = a; t = Math.imul(t ^ (t >>> 15), t | 1); t ^= t + Math.imul(t ^ (t >>> 7), t | 61); return ((t ^ (t >>> 14)) >>> 0) / 4294967296; };
}
const hash = n => rng(n * 9973 + 17)();

// A keyframed value. keys: [[time, value, easeIntoThisKey], ...] in time order. Before the first
// key it holds the first value, after the last the last. The ease named on a key shapes the
// stretch that ARRIVES at it, which is how an animator reads a dope sheet.
function track(keys, defEase = 'io') {
  const K = keys.map(k => [k[0], k[1], typeof k[2] === 'function' ? k[2] : E[k[2] || defEase]]);
  return t => {
    if (t <= K[0][0]) return K[0][1];
    for (let i = 1; i < K.length; i++) {
      if (t <= K[i][0]) { const a = K[i - 1], b = K[i]; return lerp(a[1], b[1], b[2]((t - a[0]) / (b[0] - a[0]))); }
    }
    return K[K.length - 1][1];
  };
}
// A performance blocked in key poses, the way an animator works: keys are [time, pose, ease],
// a pose is a plain object of numbers (and arrays of numbers), and every channel moves into a
// key on that key's ease. 'outBack' overshoots, which is the settle after a fast move.
function poses(keys, defEase = 'io') {
  const K = keys.map(k => [k[0], k[1], typeof k[2] === 'function' ? k[2] : E[k[2] || defEase]]);
  const mixv = (a, b, u) => Array.isArray(a) ? a.map((v, i) => v + (b[i] - v) * u) : a + (b - a) * u;
  return t => {
    if (t <= K[0][0]) return { ...K[0][1] };
    for (let i = 1; i < K.length; i++) {
      if (t <= K[i][0]) { const a = K[i - 1], b = K[i], u = b[2]((t - a[0]) / (b[0] - a[0])), o = {}; for (const c in b[1]) o[c] = mixv(a[1][c], b[1][c], u); return o; }
    }
    return { ...K[K.length - 1][1] };
  };
}
// One pose part of the way to another (the same channels as poses()).
const mixPose = (a, b, u) => { const o = {}; for (const c in a) o[c] = Array.isArray(a[c]) ? a[c].map((v, i) => v + (b[c][i] - v) * u) : a[c] + (b[c] - a[c]) * u; return o; };
// Quadratic bezier point.
const qbez = (p0, p1, p2, u) => { const q = 1 - u; return [q * q * p0[0] + 2 * q * u * p1[0] + u * u * p2[0], q * q * p0[1] + 2 * q * u * p1[1] + u * u * p2[1]]; };

/* ---------- dom ---------- */
const $ = s => document.querySelector(s);
function mk(tag, cls, parent, text) {
  const e = document.createElement(tag);
  if (cls) e.className = cls;
  if (text != null) e.textContent = text;
  if (parent) parent.appendChild(e);
  return e;
}
function html(parent, markup) { const d = document.createElement('div'); d.innerHTML = markup.trim(); const e = d.firstElementChild; if (parent) parent.appendChild(e); return e; }
const vis = (e, on) => { const v = on ? '' : 'none'; if (e.style.display !== v) e.style.display = v; };
const css = (e, o) => Object.assign(e.style, o);
// Centre-anchored 2D placement for flat things (captions, chips on the wall).
function place(e, x, y, s = 1, r = 0, o = 1, extra = '') {
  e.style.transform = `translate(${x}px,${y}px) rotate(${r}deg) scale(${s})${extra} translate(-50%,-50%)`;
  e.style.opacity = o;
}
const rgba = (rgb, a) => `rgba(${rgb},${Math.max(0, a).toFixed(4)})`;

Object.assign(F, { clamp, lerp, range, DEG, bezier, E, spring, wobble, bump, rng, hash, track, poses, mixPose, qbez, $, mk, html, vis, css, place, rgba });
