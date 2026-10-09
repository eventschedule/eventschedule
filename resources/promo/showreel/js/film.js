// PLAN, PROMOTE, SELL: the film. One world (a wall with a month on it, a floor, a poster and
// the things it makes), and a director who cuts between shots of it on a half-second beat.
// Everything is a pure function of t, so any frame can be rendered alone.
'use strict';
(() => {
const { clamp, lerp, range, E, spring, wobble, poses, mixPose, qbez, mk, html, vis, place, rgba, rng, hash, $ } = F;
const FLOOR = F.FLOOR, T = F.T, HS = 1.16;
const hud = $('#hud'), stageEl = $('#stage');
const arc = (t, a, b, h) => { const u = range(t, a, b); return u <= 0 || u >= 1 ? 0 : h * (1 - Math.pow(2 * u - 1, 2)); };
const stand = (e, x, y, z, ry, s) => { e.style.transform = `translate3d(${x}px,${y}px,${z}px) rotateY(${ry}deg) translate(-50%,-100%) scale(${Math.max(.001, s).toFixed(4)})`; };
const hang = (e, x, y, z, s, r = 0) => { e.style.transform = `translate3d(${x}px,${y}px,${z}px) translate(-50%,-50%) scale(${Math.max(.001, s).toFixed(4)}) rotate(${r}deg)`; };
const num = n => n.toLocaleString('en-US');

/* ================= the cut ================= */
// Section starts. Every cut sits on a quarter second.
const C = { hook: 0, who: 3, plan: 5, promote: 9.75, sell: 13.25, night: 16.75, room: 17.5, bill: 21.5, wall: 23.5, logo: 26, loop: 27.5 };

/* ================= the hero ================= */
// The four casts are the homepage's own ($hpCasts).
const CASTS = [
  { key: 'jazz', w: ['a jazz', 'club'], venue: 'The Blue Note', l1: 'Jazz', l2: 'Night', when: 'Sat · Doors 8:00 PM', glow: '56,150,245', edge: '#1b4cc0' },
  { key: 'comedy', w: ['a comedy', 'club'], venue: 'The Cellar Club', l1: 'Late', l2: 'Laughs', when: 'Sat · Doors 9:30 PM', glow: '255,186,96', edge: '#0d0703' },
  { key: 'yoga', w: ['a yoga', 'studio'], venue: 'Stillpoint Yoga', l1: 'Night', l2: 'Flow', when: 'Sat · Doors 7:00 PM', glow: '34,200,220', edge: '#083a50' },
  { key: 'fest', w: ['a street', 'festival'], venue: 'Riverside Fest', l1: 'Summer', l2: 'Fest', when: 'Sat · Gates 6:00 PM', glow: '80,180,245', edge: '#0b76ad' },
];
const named = (cls, name, slug, extra = '') => `<div class="face ${cls}"><div class="ft"><i>On the wall</i><i>This week</i></div><div class="fn">${name}</div><div class="ff">${slug}.eventschedule.com</div>${extra}</div>`;
const gig = (c, extra = '') => `<div class="face f-named f-gig f-${c.key}">${c.key === 'fest' ? '<div class="sun"></div>' : ''}<div class="ft"><i>${c.venue} presents</i></div><div class="fn">${c.l1}<br>${c.l2}</div><div class="fw">${c.when}</div>${extra}</div>`;
const faces = { blank: named('f-blank', 'Your<br>name', 'your-name', '<div class="wave"></div>'), named: named('f-named f-jazz', 'Blue<br>Note', 'blue-note') };
CASTS.forEach(c => { faces['g' + c.key] = gig(c, c.key === 'jazz' ? '<div class="scan"></div><div class="sbox sb1"></div><div class="sbox sb2"></div><div class="sbox sb3"></div>' : ''); });
const hero = new F.Paper({ w: 330, h: 440, n: 20, cls: 'hero', faces });
hero.each('gcomedy', '.fn', e => { e.style.fontSize = '62px'; });
hero.each('gfest', '.fn', e => { e.style.fontSize = '57px'; });
hero.each('gyoga', '.fn', e => { e.style.fontSize = '66px'; });
const titleOf = s => { const [a, b] = s.split('-'); return b == null ? a : `${a}<br>${b}`; };

/* ================= on the wall ================= */
// the address (not a field: the whole picture is a link)
const addr = html(F.wallo, '<div class="addr"><div class="sm"><div class="a1"><span class="ty"></span><span class="caret"></span><span class="ph">your-name</span></div></div><div class="sm"><div class="a2 sx">.eventschedule.com</div></div></div>');
const aTy = addr.querySelector('.ty'), aPh = addr.querySelector('.ph'), aCaret = addr.querySelector('.caret'), aSx = addr.querySelector('.sx');
const KEYS = [[.3, 'b'], [.41, 'l'], [.51, 'u'], [.62, 'e'], [.78, '-'], [.9, 'n'], [.99, 'o'], [1.08, 't'], [1.17, 'e']];
const K0 = KEYS[0][0], KN = KEYS[8][0], FILL0 = 1.0, NOTCH = .075, FILLN = FILL0 + 8 * NOTCH;
// how far the name has filled the sheet: a notch a letter, each with a little bounce
const filled = t => { let v = 0; for (let i = 0; i < 9; i++) v += E.outBack(range(t, FILL0 + i * NOTCH, FILL0 + i * NOTCH + .15)); return v / 9; };

// a month on the wall's own seven columns: the page is about a calendar
const CW = 264, CH = 132, CX0 = 36, CY0 = 130, FEAT = 19, BOOK = 8;
const cellXY = k => [CX0 + (k % 7) * CW, CY0 + Math.floor(k / 7) * CH];
const month = mk('div', 'a', F.wallo);
['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].forEach((d, i) => { const e = mk('div', 'mhead', month, d); e.style.transform = `translate(${CX0 + i * CW}px,96px)`; });
const SHOWS = ['Open Mic', 'Vinyl Night', 'Comedy Hour', 'Salsa Social', 'Quiz Night', 'Karaoke', 'Poetry Slam', 'Brunch Set', 'DJ Night', 'Art Walk', 'Jam Session', 'Film Club', 'Swing Dance', 'Book Club', 'Sound Bath', 'Night Market', 'Blues & Brews', 'Wine Tasting', 'Indie Fest', 'Latin Night'];
const DOT = ['#2f66ea', '#0ea5e9', '#22d3ee', '#16a34a', '#f59e0b'];
const cells = (() => {
  const R = rng(11), out = [], [fx, fy] = cellXY(FEAT);
  for (let k = 0; k < 35; k++) {
    const [x, y] = cellXY(k), e = mk('div', 'mcell' + (k < 1 || k > 31 ? ' dim' : ''), month);
    e.style.transform = `translate(${x}px,${y}px)`;
    mk('u', null, e, String(k < 1 ? 30 : k > 31 ? k - 31 : k));
    let chip = null, t0 = 0;
    const want = k === FEAT || k === BOOK || (R() < .62 && k !== 17 && k !== 24 && k !== 10 && k !== 3);
    if (want) {
      chip = html(e, `<div class="wchip"><i></i><span></span></div>`);
      chip.querySelector('span').textContent = k === FEAT ? 'Jazz Night' : k === BOOK ? 'Booked 3:00 PM' : SHOWS[(k * 7) % SHOWS.length];
      chip.style.setProperty('--c', k === BOOK ? '#16a34a' : DOT[k % DOT.length]);
      t0 = k === FEAT ? 8.23 : k === BOOK ? 9.42 : 8.3 + Math.hypot(x - fx, y - fy) * .0004 + R() * .08;
    } else R();
    out.push({ e, chip, t0 });
  }
  return out;
})();
// whose it is, large on the wall beside it
const castw = html(F.wallo, '<div class="castw"><small>Made for</small><div class="sm"><div class="c1"></div></div><div class="sm"><div class="c2 gtext"></div></div></div>');
const cw1 = castw.querySelector('.c1'), cw2 = castw.querySelector('.c2');
// the stamp, and the count above the room
const stamp = html(F.wallo, '<div class="stamp"><div><b>0%</b><span>platform<br>fees</span></div></div>');
const count = html(F.wallo, '<div class="count"><b>0</b><span>of 150 through the door</span></div>');
const countN = count.querySelector('b');

/* ================= props ================= */
const CARD = { x: 1420, z: -36, ry: -11, w: 430, h: 392 };
const card = html(F.props, '<div class="ecard"><div class="ek">Read from your poster</div><h4><span></span></h4><div class="er"><span></span></div><div class="er"><span></span></div></div>');
const cardSlots = [card.querySelector('h4 span'), ...card.querySelectorAll('.er span')];
const FIELDS = [
  { txt: 'Jazz Night', from: [140, 274], to: [150, 106], t0: 6.85 },
  { txt: 'Sat · 8:00 PM', from: [160, 390], to: [140, 185], t0: 7.0 },
  { txt: 'The Blue Note', from: [150, 33], to: [150, 259], t0: 7.15 },
];
const FLY = .42;
FIELDS.forEach(f => { f.e = mk('div', 'fly', F.props, f.txt); });
const slip = html(F.props, '<div class="wchip"><i></i><span>Jazz Night</span></div>');
const slots = html(F.props, '<div class="prop slots"><div class="ek">Open hours</div><div class="sl"><span>2:00 PM</span></div><div class="sl pk"><i></i><span>3:00 PM</span></div><div class="sl"><span>4:00 PM</span></div></div>');
// The homepage's own QR drawing ($hpQr).
const QR = 'M0 0h9v9H0V0zm2 2v5h5V2H2zm1 1h3v3H3V3zm17-3h9v9h-9V0zm2 2v5h5V2h-5zm1 1h3v3h-3V3zM0 20h9v9H0v-9zm2 2v5h5v-5H2zm1 1h3v3H3v-3zM12 0h2v2h-2V0zm3 0h2v4h-2V0zm-3 4h2v3h-2V4zm3 3h4v2h-4V7zm-3 3h3v2h-3v-2zm5 0h2v3h-2v-3zm7 1h2v2h-2v-2zm3-1h2v4h-2v-4zM0 12h2v2H0v-2zm3 0h4v2H3v-2zm5 1h2v4H8v-4zm3 3h2v2h-2v-2zm3-2h3v2h-3v-2zm5 1h2v3h-2v-3zm3 1h4v2h-4v-2zm5 1h2v2h-2v-2zm-15 4h4v2h-4v-2zm5 1h2v2h-2v-2zm3-2h2v4h-2v-4zm3 2h4v2h-4v-2zm-7 3h2v4h-2v-4zm-3 1h2v3h-2v-3zm8 0h3v2h-3v-2zm5-1h2v4h-2v-4z';
const qrSvg = `<svg viewBox="0 0 29 29" fill="currentColor"><path d="${QR}"/></svg>`;
const phone = html(F.props, '<div class="prop phone"><i></i></div>');
const qrc = html(F.props, `<div class="prop qrc">${qrSvg}</div>`);
const brow = html(F.props, '<div class="prop brow"></div>');
const env = html(F.props, '<div class="prop env"></div>');
// where each copy of the poster goes, when it leaves and when it lands (each landing is its own shot)
const DEST = [
  { x: 1290, y: FLOOR - 153, z: -24, s: 1.5, a: 10.95, b: 11.3 }, { x: 1420, y: 320, z: -236, s: 1, a: 11.08, b: 11.55 },
  { x: 1690, y: 400, z: -236, s: 1.7, a: 11.26, b: 11.8 }, { x: 1620, y: FLOOR - 78, z: -6, s: 1.1, a: 11.5, b: 12.05 },
];
DEST.forEach(d => { d.e = html(F.props, '<div class="mini"><b>Jazz<br>Night</b><u></u></div>'); });
const chart = html(F.props, '<div class="prop chart"><div class="ek">This week</div><h4></h4><p>page views</p><div class="fl">940 followers</div><div class="bars">' + [.3, .22, .42, .5, .66, 1, .58].map((h, i) => `<i${i === 5 ? ' class="pk"' : ''} style="height:${h * 100}%"></i>`).join('') + '</div></div>');
const chartN = chart.querySelector('h4'), chartBars = [...chart.querySelectorAll('.bars i')], chartFl = chart.querySelector('.fl');
const ticket = new F.Paper({ w: 240, h: 380, n: 10, r: 26, cls: 'ticket', faces: { f: `<div class="face"><div class="th"><small>Event Schedule</small><strong>Jazz Night</strong><span>Sat · 8:00 PM</span></div><div class="tb"><div class="tq">${qrSvg}<div class="tl"></div></div><div class="tfo"><span>GA x1</span><span>#0042</span></div></div></div>` } });
const chk = html(F.props, '<div class="chk"><svg width="70" height="70" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5" pathLength="1" stroke-dasharray="1" stroke-dashoffset="1"/></svg></div>');
const chkPath = chk.querySelector('path');

/* ================= the room: 150 lights, of which 142 come on ================= */
const T_SCAN = [16.85, 17.25], T_CHK = 17.3, T_FIRST = 17.92, FLIGHT = .42;
const DOOR = [1496, 690];            // where the ticket is scanned, in the wide shot: the lights come from there
const SEATS = (() => {
  const stay = new Set([7, 23, 41, 58, 76, 97, 118, 139]), out = [];
  const ROW = [[474, .5, 7, .55], [530, .62, 9, .68], [597, .76, 11.5, .8], [674, .9, 14.5, .92], [760, 1.05, 18, 1]];
  for (let s = 0; s < 150; s++) { const r = Math.floor(s / 30), c = s % 30, [y, w, rad, o] = ROW[r]; out.push({ x: 960 + (c / 29 - .5) * 1900 * w, y, rad, o, in: !stay.has(s), i: (s * 37) % 150 }); }
  const live = out.filter(s => s.in).sort((a, b) => a.i - b.i);
  live.forEach((s, n) => { s.t = T_FIRST + (n ? .45 + 1.9 * Math.pow(n / (live.length - 1), .62) : 0); });   // one, then a few, then the rush
  return out;
})();
const T_FULL = Math.max(...SEATS.filter(s => s.in).map(s => s.t)), JUMP2 = [T_FULL + .18, T_FULL + .68];
const through = t => { let n = 0; for (const s of SEATS) if (s.in && t >= s.t) n++; return n; };
function drawRoom(t) {
  const g = F.wctx; g.save();
  for (const s of SEATS) {
    const [x, y] = F.wpt(s.x, s.y), d = s.in ? t - s.t : -1;
    if (d < 0) {
      g.globalCompositeOperation = 'source-over'; g.fillStyle = rgba('58,74,128', .5 * s.o); g.beginPath(); g.arc(x, y, s.rad, 0, 6.2832); g.fill();
      const u = s.in ? range(t, s.t - FLIGHT, s.t) : 0;
      if (u > 0 && u < 1) {            // on its way in from the door
        const a = F.wpt(DOOR[0], DOOR[1]), e = E.io(u), q = qbez(a, [(a[0] + x) / 2, Math.min(a[1], y) - 150], [x, y], e), r = lerp(4, s.rad * .9, e);
        g.globalCompositeOperation = 'lighter';
        const gl = g.createRadialGradient(q[0], q[1], 0, q[0], q[1], r * 4); gl.addColorStop(0, rgba('160,240,255', .9)); gl.addColorStop(.3, rgba('34,211,238', .5)); gl.addColorStop(1, rgba('34,211,238', 0));
        g.fillStyle = gl; g.beginPath(); g.arc(q[0], q[1], r * 4, 0, 6.2832); g.fill();
      }
      continue;
    }
    const u = clamp(d / .6), sc = u < .4 ? lerp(1, 1.9, E.out(u / .4)) : lerp(1.9, 1, E.io((u - .4) / .6)), white = u < .4 ? u / .4 : 1 - (u - .4) / .6;
    g.globalCompositeOperation = 'lighter';
    const gl = g.createRadialGradient(x, y, 0, x, y, s.rad * 3.6); gl.addColorStop(0, rgba('34,211,238', .5 * s.o)); gl.addColorStop(1, rgba('34,211,238', 0));
    g.fillStyle = gl; g.beginPath(); g.arc(x, y, s.rad * 3.6, 0, 6.2832); g.fill();
    g.globalCompositeOperation = 'source-over';
    g.fillStyle = `rgba(${Math.round(lerp(103, 255, white))},${Math.round(lerp(232, 255, white))},${Math.round(lerp(249, 255, white))},${s.o * .35 + .65})`;
    g.beginPath(); g.arc(x, y, s.rad * sc, 0, 6.2832); g.fill();
  }
  g.restore();
}

/* ================= tickets sold: a fountain of stubs ================= */
const SOLD = [14.8, 15.6], STUBS = (() => { const R = rng(150), o = []; for (let i = 0; i < 150; i++) o.push({ t: SOLD[0] + (i / 149) * (SOLD[1] - SOLD[0] - .1), a: -1.9 + R() * 1.5, v: 760 + R() * 900, r: (R() - .5) * 14, life: .55 + R() * .35 }); return o; })();
const soldAt = t => Math.round(150 * E.out(range(t, SOLD[0], SOLD[1])));
function drawStubs(t, src) {
  if (t < SOLD[0] || t > SOLD[1] + 1) return;
  const g = F.fx, [sx, sy, sc] = F.project(src[0], src[1], src[2]); g.save();
  for (const s of STUBS) {
    const d = t - s.t; if (d <= 0 || d >= s.life) continue;
    const x = sx + Math.cos(s.a) * s.v * d * sc, y = sy + (Math.sin(s.a) * s.v * d + 1500 * d * d) * sc, k = 1 - d / s.life;
    g.translate(x, y); g.rotate(s.r * d); g.fillStyle = rgba('47,102,234', Math.min(1, k * 2.2));
    g.beginPath(); g.roundRect(-13 * sc, -8 * sc, 26 * sc, 16 * sc, 3.5 * sc); g.fill();
    g.fillStyle = rgba('255,255,255', .55 * Math.min(1, k * 2.2)); g.fillRect(-8 * sc, -1.5 * sc, 11 * sc, 3 * sc); g.setTransform(1, 0, 0, 1, 0, 0);
  }
  g.restore();
}

/* ================= the wall of everyone else's posters ================= */
const SLOT = { x: 1728, y: 128, s: .697 }, HOP = [C.wall + .12, C.wall + .65];
const PAL = [['linear-gradient(150deg,#2b5fe3,#0b8fd8 55%,#22d3ee)', '#fff'], ['#0a1020', '#67e8f9'], ['#f4f6fb', '#0a1020'], ['linear-gradient(160deg,#fbbf24,#d97706)', '#1a0f0a'], ['linear-gradient(160deg,#10b981,#047857)', '#fff'], ['linear-gradient(170deg,#7dd3fc,#0284c7)', '#0a1020'], ['#1a0f0a', '#ffc46b'], ['linear-gradient(165deg,#0b4f6c,#0e7490 45%,#22d3ee)', '#fff'], ['#123e8c', '#fde68a']];
const FONTW = [.46, .62, .42, .58, .62, .7, .5, .56, .5, .64];
const WSHOWS = SHOWS.concat(['Pub Quiz', 'Gallery Late', 'Soul Train', 'Open Decks', 'Big Band', 'Folk Club', 'Dance Class', 'Improv Jam', 'Farmers Market', 'Drag Brunch', 'Life Drawing', 'Choir Night', 'Supper Club', 'Record Fair', 'Tap Takeover', 'Story Slam', 'Craft Fair', 'Bingo Night', 'Tango Milonga', 'Paint & Sip', 'Run Club', 'Chess Night', 'Zine Fest', 'Harvest Fair', 'Lantern Walk', 'Garden Party', 'Roller Disco', 'Movie Night', 'Winter Market', 'Mic Check', 'Yoga Flow', 'Food Trucks']);
const ROOMS = ['The Cellar', 'Riverside', 'Stillpoint', 'The Loft', 'Corner Stage', 'Old Mill', 'Harbour Hall', 'The Annex'];
const WHEN = ['Fri 8 PM', 'Sat 9 PM', 'Thu 7 PM', 'Sun 11 AM', 'Wed 7:30 PM', 'Sat 2 PM', 'Fri 10 PM', 'Tue 6 PM'];
const IMGS = ['jazz', 'party', 'rock', 'dj', 'comedy', 'openmic', 'special'];
const posters = (() => {
  const R = rng(2026), out = []; let n = 0;
  [-536, -204, 128, 460].forEach((y, row) => {
    for (let k = -6; k <= 6; k++) {
      if (k === 3 && row === 2) continue;
      const e = mk('div', 'wp', F.wallo), name = WSHOWS[n % WSHOWS.length], fi = Math.floor(R() * 10), photo = R() < .26;
      const pal = PAL[(n * 5 + row) % PAL.length], words = name.split(' '), longest = Math.max(...words.map(w => w.length)), fs = clamp(196 / (longest * FONTW[fi]), 30, 70);
      e.innerHTML = (photo ? `<img alt="" src="img/demo_flyer_${IMGS[n % IMGS.length]}.webp" decoding="sync"><div class="sh2"></div>` : '')
        + `<div class="k">${ROOMS[(n * 3) % ROOMS.length]}</div><div class="n" style="font-family:P${fi},var(--font);font-size:${fs.toFixed(0)}px${fi === 6 ? ';text-transform:none;line-height:1.02' : ''}">${words.join('<br>')}</div><div class="d">${WHEN[(n * 5 + 1) % WHEN.length]}</div>`;
      if (!photo) { e.style.background = pal[0]; e.style.color = pal[1]; if (pal[0] === '#f4f6fb') e.style.boxShadow = 'inset 0 0 0 3px #2f66ea,0 22px 46px -22px rgba(10,16,32,.6)'; }
      const x = 960 + k * 256 + (R() - .5) * 14, yy = y + (R() - .5) * 12;
      out.push({ e, x, y: yy, rot: (R() - .5) * 5, t0: C.wall + .04 + Math.hypot(x - SLOT.x, yy - SLOT.y) * .0003 + R() * .08 });
      n++;
    }
  });
  return out;
})();
const mine = html(F.wallo, `<div class="wp mine hero"><div class="face f-named f-gig f-jazz"><div class="ft"><i>The Blue Note presents</i></div><div class="fn">Jazz<br>Night</div><div class="fw">Sat · Doors 8:00 PM</div></div></div>`);

/* ================= screen space: captions, cards, words, the logo ================= */
const cap = mk('div', 'cap2', hud);
const bignum = html(hud, '<div class="bignum"><b>0</b><span>sold.</span></div>'), bigN = bignum.querySelector('b');
const VERBS = [[C.plan, 'Plan.'], [C.promote, 'Promote.'], [C.sell, 'Sell.']], CARD_LEN = .75;
const vcards = VERBS.map(([a, w], i) => { const e = html(hud, `<div class="vcard"><div class="ix">0${i + 1} / 03</div><div class="bars">${[0, 1, 2].map(j => `<i class="${j === i ? 'on' : j < i ? 'done' : ''}"></i>`).join('')}</div><div class="vb gtext">${w}</div></div>`); return { e, a, vb: e.querySelector('.vb') }; });
const billc = mk('div', 'billc', hud);
{
  let i = 0; const n = s => `<span class="n${i++ % 2 ? ' bl' : ''}">${s}</span>`, row = (c, r) => `<span class="r ${c}">${r.map(n).join('')}</span>`;
  billc.innerHTML = '<div class="top"><span>Also on the bill</span><span>Twelve more</span></div><div class="rows">'
    + row('r1', ['Online Events', 'Event Polls']) + row('r1', ['Reserved Seating']) + row('r2', ['Fan Videos &amp; Comments', 'Sub-schedules']) + row('r2', ['Event Graphics', 'Custom Fields'])
    + row('r3', ['Private Events', 'Recurring Events', 'White-label Branding']) + row('r3', ['Team Scheduling', 'Gift Cards &amp; Passes']) + '</div>';
}
const billTop = billc.querySelector('.top'), billRows = billc.querySelector('.rows'), billNames = [...billc.querySelectorAll('.n')];
const veil = mk('div', null, hud); veil.id = 'veil';
const fin = html(hud, '<div class="fin"><div class="sm"><div class="f1">Free and open source.</div></div><div class="sm"><div class="f2 gtext">Forever.</div></div></div>');
const f1 = fin.querySelector('.f1'), f2 = fin.querySelector('.f2');
const E_PATH = 'M20 35 L69 35 L58 53 L42 53 L42 61 L58 61 L67 77 L42 77 L42 85 L66 85 L55 103 L20 103 Z';
const S_PATH = 'M80 35 L123 35 L110.5 53 L85 53 L85 61 L101 61 C116 61 125 66 125 76 L125 88 C125 97 119 103 109 103 L62 103 L73.5 86 L105 86 L105 78 L81 78 C70 78 63 72 63 63 L63 55 C63 50 64 45 67 41 Z';
const lcard = html(hud, `<div class="lcard"><div class="lock"><svg viewBox="14 28 118 82"><path class="e" d="${E_PATH}" fill="#048EEB"/><path class="s" d="${S_PATH}" fill="var(--ink2)"/></svg><div class="w"><b>Event</b><i>Schedule</i></div></div><div class="tagl">Free event calendar. No credit card.</div><div class="site">eventschedule.com</div></div>`);
const lock = lcard.querySelector('.lock'), lockE = lcard.querySelector('.e'), lockS = lcard.querySelector('.s'), tagl = lcard.querySelector('.tagl'), site = lcard.querySelector('.site');
[lock, tagl, site].forEach(e => { e.style.zIndex = 2; });

/* ================= the hero's performance =================
   Key poses: ry where it faces (+ is screen right), rz a sideways lean, rx a lean back, b the curl
   of its lower, middle and upper third (+ back, - forward), sy its stretch. */
const P = {
  slump: { ry: 30, rz: 3, rx: -2, b: [18, -36, -66], sy: .97 }, sink: { ry: 30, rz: 4, rx: -2, b: [22, -46, -84], sy: .94 },
  tall: { ry: -5, rz: 0, rx: 2, b: [0, 6, 12], sy: 1.07 }, proud: { ry: -8, rz: 0, rx: 1, b: [0, 8, 12], sy: 1.04 },
  crouch: { ry: -24, rz: 0, rx: 0, b: [-58, 128, -78], sy: .8 }, launch: { ry: -20, rz: -2, rx: -3, b: [8, 12, 30], sy: 1.2 },
  apex: { ry: -10, rz: -4, rx: 0, b: [0, 8, 14], sy: 1.02 }, fall: { ry: -4, rz: 2, rx: 2, b: [0, -8, -18], sy: 1.1 },
  landed: { ry: 0, rz: 0, rx: 0, b: [-46, 96, -54], sy: .84 }, stand: { ry: 0, rz: 0, rx: 0, b: [0, 2, 3], sy: 1 },
  rigid: { ry: 0, rz: 0, rx: 1, b: [0, 0, 0], sy: 1.06 }, right: { ry: 27, rz: 3, rx: 0, b: [0, 6, -12], sy: 1 },
  upR: { ry: 22, rz: 2, rx: 5, b: [0, 18, 42], sy: 1.03 }, upL: { ry: -24, rz: -3, rx: 5, b: [0, 16, 38], sy: 1.03 }, left: { ry: -30, rz: -3, rx: 0, b: [0, 6, -14], sy: 1 },
  inhale: { ry: 18, rz: -2, rx: 6, b: [10, 26, 34], sy: 1.08 }, blow: { ry: 24, rz: 3, rx: -3, b: [-6, -22, -48], sy: .96 }, watch: { ry: 26, rz: 2, rx: 0, b: [0, 6, 8], sy: 1 },
  down: { ry: 30, rz: 4, rx: -1, b: [0, -10, -42], sy: 1 }, brace: { ry: 24, rz: -3, rx: 0, b: [-20, 46, -32], sy: .94 }, jolt: { ry: 14, rz: 4, rx: 2, b: [0, 10, 22], sy: 1.07 },
  see: { ry: -32, rz: -3, rx: 4, b: [0, 12, 30], sy: 1.03 }, lean: { ry: -38, rz: -6, rx: 2, b: [0, 8, 14], sy: 1.05 }, lit: { ry: -16, rz: 0, rx: 2, b: [0, 8, 12], sy: 1.04 },
  fly: { ry: 0, rz: 0, rx: 0, b: [0, 10, 18], sy: 1.08 }, flat: { ry: 0, rz: 0, rx: 0, b: [0, 0, 0], sy: 1 },
};
const SIG = [{ ry: -8, rz: 0, rx: 1, b: [0, 8, 12], sy: 1.04 }, { ry: 10, rz: 9, rx: 7, b: [0, 16, 24], sy: 1.02 }, { ry: 0, rz: 0, rx: 0, b: [0, -2, -4], sy: 1.13 }, { ry: -6, rz: -5, rx: 2, b: [0, 8, 14], sy: 1.03 }];
const JUMP = [2.26, 2.75];
const body = poses([[0, P.slump], [FILL0, P.sink], [FILLN + .16, P.tall], [1.9, P.proud], [2.05, P.proud], [2.24, P.crouch], [2.32, P.launch, 'out'], [2.5, P.apex], [2.72, P.fall, 'in'], [2.75, P.fall], [2.81, P.landed, 'out'], [3.0, P.stand, 'outBack'],
  [5.75, P.stand], [5.84, P.rigid, 'out'], [6.55, P.rigid], [6.75, P.stand], [6.95, P.right, 'outBack'], [7.75, P.right], [8.1, P.upR], [8.4, P.upR], [8.75, P.upL], [9.0, P.left], [9.75, P.left],
  [10.5, P.stand], [10.56, P.stand], [10.8, P.inhale], [10.88, P.inhale], [10.98, P.blow, 'out'], [11.25, P.blow], [12.25, P.watch], [13.25, P.watch],
  [14.0, P.down], [14.5, P.down], [14.75, P.right, 'outBack'], [15.75, P.right], [16.1, P.brace], [16.25, P.brace], [16.33, P.jolt, 'out'], [16.7, P.stand, 'outBack'],
  [C.room, P.slump], [T_FIRST + .04, P.slump], [T_FIRST + .3, P.see, 'outBack'], [19.0, P.see], [19.7, P.lean], [T_FULL, P.lean], [JUMP2[0] - .02, P.crouch], [JUMP2[0] + .08, P.launch, 'out'], [JUMP2[0] + .28, P.apex], [JUMP2[1], P.apex, 'in'], [JUMP2[1] + .06, P.landed, 'out'], [JUMP2[1] + .3, P.lit, 'outBack'],
  [C.bill, P.lit], [C.wall, P.stand], [C.wall + .1, P.crouch], [C.wall + .2, P.fly, 'out'], [HOP[1], P.flat], [C.loop, P.flat]]);

/* ================= the world at time t ================= */
function world(t) {
  const loop = t >= C.loop, night = t >= C.night && t < C.bill, room = t >= C.room && t < C.bill;
  F.setNight(night ? 1 : 0, t);
  const on = (e, v) => { vis(e, v); return v; };

  /* --- the address, and the month --- */
  const tk = loop ? 0 : t;           // the last half second is the first frame again
  let typed = ''; for (const [kt, ch] of KEYS) if (tk >= kt) typed += ch;
  if (on(addr, (t < C.who && !(t >= 1.25 && t < 2)) || loop)) {
    addr.style.transform = 'translate(26px,258px)';
    if (aTy._t !== typed) { aTy._t = typed; aTy.textContent = typed; aPh.textContent = 'your-name'.slice(typed.length); }
    aSx.style.color = tk > KN ? 'var(--ink2)' : '';
    aCaret.style.opacity = tk > KN + .2 && Math.floor(t * 2.5) % 2 ? 0 : 1;     // steady until the name is typed, so the loop's seam holds
  }
  const calOn = on(month, t >= C.plan && t < C.night);
  if (calOn) for (const c of cells) { if (!c.chip) continue; const d = t - c.t0; vis(c.chip, d > 0); if (d > 0) c.chip.style.transform = `scale(${lerp(1.5, 1, spring(d, 2.4, .5)).toFixed(4)})`; }

  /* --- the hero --- */
  const side = room ? 1 : 0, wallAct = t >= C.wall && t < C.logo;
  let hx = t >= C.sell && t < C.night + .75 ? 620 : 960, face = 'blank', castI = 0, k = body(t);
  if (t >= FILL0 && t < FILLN + .16) k = mixPose(P.sink, P.tall, clamp(filled(t), 0, 1.12));
  const p = { s: HS, x: hx, z: 0, lift: arc(t, JUMP[0], JUMP[1], 195), ry: k.ry, rz: k.rz, rx: k.rx, sy: k.sy, bend: k.b.slice() };
  const land = t - JUMP[1]; if (land > 0 && land < 1) { p.rz += 2.2 * wobble(land, 3.1, 5.5); p.bend[2] += 10 * wobble(land - .05, 2.9, 4.6) * (1 - range(land, .7, 1)); }
  if (t >= FILL0 && t < C.who) face = t < FILLN + .2 ? { blank: 1, named: 1 } : 'named';
  if (t >= C.who && t < C.plan) {    // made for: a cut a cast, each with its own pose
    castI = Math.min(3, Math.floor((t - C.who) / .5)); const d = t - C.who - castI * .5, g = mixPose(P.stand, SIG[castI], E.out(range(d, 0, .16)));
    Object.assign(p, { ry: g.ry, rz: g.rz, rx: g.rx, sy: g.sy, bend: g.b.slice() }); p.s = HS * (1 + .1 * Math.exp(-d / .06));
    if (castI === 3) p.lift = 34 * Math.abs(Math.sin(Math.PI * clamp(d / .36)));
    face = 'g' + CASTS[castI].key;
  }
  if (t >= C.plan && t < C.logo) face = 'gjazz';
  if (t > 6.55 && t < 7.05) { const d = t - 6.55, e = 1 - range(d, 0, .5); p.ry += 9 * Math.sin(d * 2 * Math.PI * 7.5) * e; p.bend[2] += 8 * Math.sin(d * 2 * Math.PI * 7.5 + 1) * e; }   // tickled by the beam
  p.lift += arc(t, 16.25, 16.55, 46) + arc(t, JUMP2[0], JUMP2[1], 120);
  for (const b of [19.1, 19.55, 19.95]) p.lift += arc(t, b, b + .22, 22);
  if (side) { p.x = 1672; p.s = HS * .6; }
  let heroOn = !loop && t < C.logo && !(t >= HOP[1] && wallAct);
  if (wallAct && t < HOP[1]) {
    const u = E.io(range(t, HOP[0], HOP[1])), v = range(t, HOP[0], HOP[1]);
    p.x = lerp(960, SLOT.x, u); p.z = lerp(0, F.WALL_Z + 6, u); p.s = lerp(HS, SLOT.s, u); p.lift = lerp(0, FLOOR - (SLOT.y + 440 * SLOT.s), u) + 130 * Math.sin(Math.PI * v); p.ry += 360 * E.io(v); p.shadow = 1 - u;
  }
  if (loop) { heroOn = true; Object.assign(p, { ...P.slump, bend: P.slump.b.slice(), x: 960, s: HS, lift: 0 }); face = 'blank'; }
  p.sx = 1 / Math.pow(p.sy, .7);
  const cast = CASTS[castI], fill = tk < FILL0 ? 0 : clamp(filled(tk));
  let typedP = 0; for (let i = 0; i < 9; i++) if (tk >= FILL0 + i * NOTCH) typedP++;
  const nm = 'blue-note'.slice(0, typedP);
  if (hero._typed !== nm) { hero._typed = nm; hero.each('named', '.fn', e => { e.innerHTML = titleOf(nm || '&nbsp;'); }); }
  hero.set('--fill', (tk < FILLN + .2 ? fill * 100 : 100).toFixed(2) + '%'); hero.set('--wave', (tk > FILL0 && tk < FILLN + .2 ? 1 - range(tk, FILLN + .06, FILLN + .2) : 0).toFixed(3));
  hero.set('--edge', face === 'blank' || (fill < .5 && tk < C.who) ? 'var(--edge-blank)' : cast.edge);
  const sc = E.io(range(t, 5.95, 6.55));
  hero.set('--scan', (sc * 100).toFixed(2) + '%'); hero.set('--scan-o', (t > 5.93 && t < 6.75 ? 1 - range(t, 6.55, 6.75) : 0).toFixed(3));
  [[6.0, 2], [6.25, 0], [6.45, 1]].forEach(([t0, fi], i) => hero.set('--b' + (i + 1), (clamp(E.outBack(range(t, t0, t0 + .18))) * (1 - range(t, FIELDS[fi].t0, FIELDS[fi].t0 + .16))).toFixed(3)));
  if (heroOn) {
    hero.show(face);
    const burst = tk > FILLN ? Math.exp(-(tk - FILLN) / .3) : 0, inRoom = clamp(through(t) / 142);
    const glowK = (face === 'blank' ? 0 : tk < C.who ? fill * (1 + .9 * burst) : 1) * (night ? lerp(.1, 1.6, room ? inRoom : 0) : 1) * (1 + .5 * (land > 0 ? Math.exp(-land / .25) : 0));
    F.wallGlow(p.x, FLOOR - p.lift - 440 * p.s * .52, 560 * p.s / HS, cast.glow, T.glowWall * glowK * (side ? .7 : 1));
    F.floorGlow(p.x + 40, 150, 560 * p.s / HS, 300, cast.glow, T.glowFloor * glowK / (1 + p.lift / 160));
    hero.pose(p);
  } else hero.pose({ on: false });
  F.puff(t, JUMP[1], 960, 0, 12, 3, 1.1); F.puff(t, JUMP2[1], 1672, 0, 8, 5, .6);

  /* --- made for --- */
  if (on(castw, t >= C.who && t < C.plan)) {
    const d = t - C.who - castI * .5, r = E.rise(range(d, 0, .26));
    if (castw._i !== castI) { castw._i = castI; cw1.textContent = cast.w[0]; cw2.textContent = cast.w[1]; }
    cw1.style.transform = `translateY(${((1 - r) * 110).toFixed(2)}%)`; cw2.style.transform = `translateY(${((1 - E.rise(range(d, .05, .31))) * 110).toFixed(2)}%)`;
  }

  /* --- plan: the poster is read into an event, and the event goes on the calendar --- */
  const T_CARD = 6.56, FOLD = [7.8, 8.25], cu = spring(t - T_CARD, 2.1, .52), fold = E.io(range(t, FOLD[0], FOLD[1]));
  const [fcx, fcy] = cellXY(FEAT), dst = [fcx + 120, fcy + 93, F.WALL_Z + 3], src = [CARD.x, FLOOR - CARD.h * .5];
  const fp = qbez(src, [(src[0] + dst[0]) / 2 + 150, Math.min(src[1], dst[1]) - 160], [dst[0], dst[1]], fold);
  if (on(card, t >= T_CARD && t < FOLD[1])) {
    card.style.transform = `translate3d(${lerp(CARD.x, fp[0], fold)}px,${lerp(FLOOR, fp[1] + CARD.h * .11, fold)}px,${lerp(CARD.z, dst[2], fold)}px) rotateY(${lerp(CARD.ry, 0, fold)}deg) translate(-50%,-100%) scale(${Math.max(.001, cu * lerp(1, .22, fold)).toFixed(4)}) rotate(${(-14 * Math.sin(Math.PI * fold)).toFixed(2)}deg)`;
    card.style.opacity = 1 - range(fold, .72, 1);
    F.cardShadow({ x: CARD.x, z: CARD.z, lift: 0, ry: CARD.ry, hw: CARD.w / 2 * cu, top: CARD.h * cu, lean: 0, k: clamp(cu) * (1 - range(fold, 0, .3)) });
  }
  if (on(slip, fold > .5 && t < 8.23)) { slip.style.transform = `translate3d(${fp[0]}px,${fp[1]}px,${lerp(CARD.z, dst[2], fold)}px) translate(-50%,-50%) scale(${lerp(1.5, 1.1, range(fold, .5, 1))})`; slip.style.opacity = range(fold, .5, .8); }
  FIELDS.forEach((f, i) => {
    const u = E.io(range(t, f.t0, f.t0 + FLY)), go = on(f.e, t >= f.t0 && u < 1 && t < C.promote);
    const n = t < T_CARD ? 0 : Math.round(f.txt.length * range(t, f.t0 + FLY - .04, f.t0 + FLY + .2)), slot = cardSlots[i]; if (slot._n !== n) { slot._n = n; slot.textContent = f.txt.slice(0, n); }
    if (!go) return;
    const a = [960 + (f.from[0] - 165) * HS, FLOOR - (440 - f.from[1]) * HS], b = [CARD.x + (f.to[0] - CARD.w / 2), FLOOR - CARD.h + f.to[1]], q = qbez(a, [(a[0] + b[0]) / 2, Math.min(a[1], b[1]) - 190 + i * 40], b, u);
    f.e.style.transform = `translate3d(${q[0]}px,${q[1]}px,40px) translate(-50%,-50%) rotate(${(-7 * (1 - u) + 7 * Math.sin(Math.PI * u)).toFixed(2)}deg) scale(${lerp(1.12, .96, u)})`; f.e.style.opacity = clamp(u * 9) * (1 - range(u, .88, 1));
  });
  if (on(slots, t >= 8.9 && t < C.promote)) { const s = spring(t - 8.9, 2.2, .52); stand(slots, 500, FLOOR, -30, 10, s); slots.style.setProperty('--pick', E.out(range(t, 9.2, 9.36)).toFixed(3)); F.cardShadow({ x: 500, z: -30, lift: 0, ry: 10, hw: 200 * s, top: 356 * s, lean: 0, k: clamp(s) }); }

  /* --- promote: copies of it go everywhere, and the audience is counted --- */
  const shareOn = t >= C.promote + .5 && t < 12.25, sp = spring(t - C.promote - .3, 2.2, .52);
  [[phone, 1290, -8, 78, 306, -30], [env, 1620, -14, 118, 160, -10]].forEach(([e, x, ry, hw, h, z]) => { if (!on(e, shareOn)) return; stand(e, x, FLOOR, z, ry, sp); F.cardShadow({ x, z: -20, lift: 0, ry, hw: hw * sp, top: h * sp, lean: 0, k: .8 }); });
  if (on(qrc, shareOn)) { hang(qrc, 1420, 320, -240, sp, -5); qrc.firstChild.style.opacity = E.out(range(t, DEST[1].b - .04, DEST[1].b + .16)); }
  if (on(brow, shareOn)) hang(brow, 1690, 400, -240, sp, 3);
  DEST.forEach((d, i) => {
    if (!on(d.e, shareOn && t >= d.a)) return;
    const u = E.io(range(t, d.a, d.b)), a = [1020, FLOOR - 330], q = qbez(a, [(a[0] + d.x) / 2, Math.min(a[1], d.y) - 170 + i * 26], [d.x, d.y], u), gone = (i === 1 || i === 3) ? 1 - range(t, d.b - .04, d.b + .1) : 1;
    d.e.style.transform = `translate3d(${q[0]}px,${q[1]}px,${lerp(30, d.z + 2, u)}px) translate(-50%,-50%) rotate(${(lerp(-24, 0, u) + 18 * Math.sin(Math.PI * u)).toFixed(2)}deg) scale(${lerp(.7, d.s, E.out(u)).toFixed(4)})`; d.e.style.opacity = clamp(u * 8) * gone;
  });
  if (on(chart, t >= 12.25 && t < C.sell)) {
    const s = spring(t - 12.25, 2.2, .52); stand(chart, 1640, FLOOR, CARD.z, CARD.ry, s);
    chartBars.forEach((b, i) => { b.style.transform = `scaleY(${clamp(spring(t - 12.38 - i * .05, 2.4, .5), .04, 1.4).toFixed(4)})`; });
    const v = num(Math.round(12480 * E.out(range(t, 12.3, 13.0)))); if (chartN._t !== v) { chartN._t = v; chartN.textContent = v; }
    chartFl.style.transform = `scale(${spring(t - 12.78, 2.4, .5).toFixed(4)})`;
    F.cardShadow({ x: 1640, z: CARD.z, lift: 0, ry: CARD.ry, hw: 220 * s, top: 392 * s, lean: 0, k: clamp(s) });
  }

  /* --- sell: a ticket, 150 of them, no platform fee, and the door --- */
  const TK0 = C.sell + .77, tkOn = t >= TK0 && t < C.bill;
  let tkTop = [1010, 500, 10];
  if (tkOn) {
    const out = E.io(range(t, TK0, TK0 + .5)), ecu = t >= C.night ? 1 : 0;
    const tp = { s: 1.02 * lerp(.9, 1, out) * (side ? .66 : 1), x: side ? 1496 : lerp(690, 1010, out), z: lerp(-30, 6, out), lift: arc(t, TK0 + .05, TK0 + .5, 70) + arc(t, 16.28, 16.58, 52) + (side ? 0 : 0),
      ry: ecu ? 0 : lerp(46, -14, out) + (t < TK0 + 1.6 ? 8 * wobble(t - TK0 - .5, 2.6, 5) : 0), rz: t < TK0 + 1.6 ? 3 * wobble(t - TK0 - .5, 2.2, 4) : 0, rx: 0,
      sy: 1 - (t > SOLD[0] && t < SOLD[1] ? .035 * Math.sin((t - SOLD[0]) * 2 * Math.PI * 9) : 0), bend: [0, 4 + (t < TK0 + 1.8 ? 14 * wobble(t - TK0 - .5, 2.4, 4.4) : 0), 8] };
    ticket.show('f'); ticket.set('--edge', '#dfe6f3');
    const lp = E.io(range(t, T_SCAN[0], T_SCAN[1]));
    ticket.set('--tl', (10 + lp * 78).toFixed(2) + '%'); ticket.set('--tlo', t > T_SCAN[0] - .04 && t < T_SCAN[1] + .1 ? '1' : '0');
    ticket.pose(tp); tkTop = [tp.x, FLOOR - tp.lift - 330 * tp.s, 10];
    if (on(chk, t >= T_CHK)) { chk.style.transform = `translate3d(${tp.x}px,${FLOOR - tp.lift - (380 - 247) * tp.s}px,24px) translate(-50%,-50%) scale(${(spring(t - T_CHK, 1.9, .45) * tp.s).toFixed(4)})`; chkPath.setAttribute('stroke-dashoffset', 1 - E.out(range(t, T_CHK + .06, T_CHK + .24))); }
  } else { ticket.pose({ on: false }); vis(chk, false); }
  drawStubs(t, tkTop);
  const ST = { x: 1520, y: 430, t: 16.25 }, sShadow = range(t, ST.t - .48, ST.t);
  if (sShadow > 0 && t < ST.t) { const g = F.wctx, [x, y] = F.wpt(ST.x + 20 * (1 - sShadow), ST.y + 14 * (1 - sShadow)); g.save(); g.filter = `blur(${lerp(60, 14, E.in(sShadow)).toFixed(1)}px)`; g.fillStyle = rgba(T.shadow, .36 * E.in(sShadow)); g.beginPath(); g.arc(x, y, lerp(250, 168, E.in(sShadow)), 0, 6.2832); g.fill(); g.restore(); }
  if (on(stamp, t >= ST.t - .1 && t < C.night)) {
    const hit = range(t, ST.t - .1, ST.t), s = lerp(2.5, 1, E.in(hit)) * (1 + .07 * (t - ST.t < .8 ? wobble(t - ST.t, 5, 9) : 0));
    stamp.style.transform = `translate(${ST.x}px,${ST.y}px) translate(-50%,-50%) rotate(-12deg) scale(${s.toFixed(4)})`; stamp.style.opacity = clamp(hit * 2.2);
    const d = t - ST.t; if (d > 0 && d < .5) { const g = F.wctx, [x, y] = F.wpt(ST.x, ST.y); g.save(); g.strokeStyle = rgba('47,102,234', .5 * (1 - d / .5)); g.lineWidth = 8 * (1 - d / .5); g.beginPath(); g.arc(x, y, 170 + 280 * E.out(d / .5), 0, 6.2832); g.stroke(); g.restore(); }
  }
  if (room) drawRoom(t);
  if (on(count, room && t >= T_FIRST - .02)) {
    const a = spring(t - T_FIRST, 2, .5), full = t - T_FULL;
    place(count, 900, 318, lerp(.7, 1, a) * (1 + (full > 0 && full < .8 ? .08 * wobble(full, 3, 5, 1.57) : 0)), 0, clamp((t - T_FIRST) / .12));
    const v = String(through(t)); if (countN._t !== v) { countN._t = v; countN.textContent = v; }
  }

  /* --- the wall of everyone else's posters --- */
  const lx = lerp(-700, 2700, E.ioSine(range(t, C.wall + .7, C.logo))), wash = F.LIGHT ? [.52, .55] : [.42, 1];
  posters.forEach(pp => {
    const d = t - pp.t0; if (!on(pp.e, wallAct && d > 0)) return;
    const kk = Math.max(clamp(1 - Math.abs(pp.x - lx) / 560), clamp(1 - Math.hypot(pp.x - SLOT.x, pp.y - SLOT.y) / 420) * .7);
    pp.e.style.transform = `translate(${pp.x}px,${pp.y}px) translate(-50%,0) rotate(${pp.rot.toFixed(2)}deg) scale(${Math.max(.001, spring(d, 2.3, .56)).toFixed(4)})`;
    pp.e.style.opacity = lerp(wash[0], 1, kk); pp.e.style.filter = kk > .98 ? '' : `grayscale(${(wash[1] * (1 - kk)).toFixed(3)})`;
  });
  if (on(mine, wallAct && t >= HOP[1])) { mine.style.transform = `translate(${SLOT.x}px,${SLOT.y}px) translate(-50%,0)`; F.wallGlow(SLOT.x, SLOT.y + 153, 520, CASTS[0].glow, T.glowWall); }
}

/* ================= the director =================
   Each shot: when it starts, where the camera is (it may move inside the shot), and the one line
   it carries. A shot runs until the next begins. Cuts are hard. */
const SHOTS = [
  { a: 0, cam: t => ({ x: 310, y: 350, zoom: lerp(2.05, 2.16, E.out(range(t, .2, 1.25))) }) },
  { a: 1.25, cam: t => ({ x: 960, y: 548, zoom: lerp(1.42, 1.5, range(t, 1.25, 2)) }) },
  { a: 2, cam: t => { const l = t - JUMP[1], sh = l > 0 ? Math.exp(-l / .09) : 0, fr = Math.floor(t * 60); return { x: 960 + (hash(fr) - .5) * 7 * sh, y: 520 + (hash(fr + 9) - .5) * 9 * sh + 5 * sh, zoom: lerp(1.06, 1, E.out(range(t, 2, 2.5))) }; }, cap: 'Your events, live in minutes.' },
  ...[0, 1, 2, 3].map(i => ({ a: C.who + i * .5, cam: t => ({ x: 800, y: 520, zoom: lerp(1.32, 1.22, E.outExpo(range(t, C.who + i * .5, C.who + i * .5 + .2))), rz: [-3, 3, -2, 2][i] }) })),
  { a: C.plan, card: 0 },
  { a: 5.75, cam: t => ({ x: 960, y: 540, zoom: lerp(1.58, 1.72, range(t, 5.75, 6.75)), rz: -3 }), cap: 'Paste a poster.' },
  { a: 6.75, cam: t => ({ x: 1190, y: 545, zoom: lerp(1.28, 1.35, range(t, 6.75, 7.75)) }), cap: 'AI turns it into an event.' },
  { a: 7.75, cam: t => ({ x: 1010, y: lerp(520, 440, E.io(range(t, 7.75, 8.5))), zoom: lerp(1.12, 1.24, E.io(range(t, 7.75, 9))) }), cap: 'Your calendar, filled.' },
  { a: 9, cam: t => ({ x: 470, y: 520, zoom: lerp(1.7, 1.78, range(t, 9, 9.75)), rz: 2 }), cap: 'Bookings, built in.' },
  { a: C.promote, card: 1 },
  { a: 10.5, cam: t => ({ x: 1040, y: 540, zoom: lerp(1.3, 1.38, range(t, 10.5, 11.25)) }), cap: 'Share it everywhere.' },
  { a: 11.25, cam: () => ({ x: 1290, y: 640, zoom: 2.2, rz: -2 }), cap: 'Share it everywhere.' },
  { a: 11.5, cam: () => ({ x: 1420, y: 320, zoom: 2.6, rz: 2 }), cap: 'Share it everywhere.' },
  { a: 11.75, cam: () => ({ x: 1690, y: 400, zoom: 2.2, rz: -2 }), cap: 'Share it everywhere.' },
  { a: 12, cam: () => ({ x: 1620, y: 690, zoom: 2.3, rz: 2 }), cap: 'Share it everywhere.' },
  { a: 12.25, cam: t => ({ x: 1650, y: 580, zoom: lerp(1.9, 2.02, range(t, 12.25, 13.25)) }), cap: 'Watch your audience grow.' },
  { a: C.sell, card: 2 },
  { a: 14, cam: t => ({ x: lerp(760, 900, E.io(range(t, 14, 14.6))), y: 560, zoom: 1.55, rz: 2 }), cap: 'Sell tickets.' },
  { a: 14.75, cam: t => ({ x: 1080, y: 520, zoom: lerp(1.22, 1.28, range(t, 14.75, 15.75)) }), big: true },
  { a: 15.75, cam: t => { const d = t - 16.25, sh = d > 0 ? Math.exp(-d / .08) : 0, fr = Math.floor(t * 60), w = E.inExpo(range(t, 16.6, 16.75)); return { x: lerp(1060, 1010, w) + (hash(fr) - .5) * 10 * sh, y: lerp(500, 600, w) + (hash(fr + 3) - .5) * 10 * sh, zoom: (1.2 + .05 * (d > 0 ? Math.exp(-d / .2) : 0)) * lerp(1, 2.1, w) }; }, cap: [16.3, 'No platform fees.'] },
  { a: C.night, cam: t => ({ x: 1010, y: 645, zoom: lerp(3.1, 3.3, range(t, C.night, C.room)) * lerp(1, .62, E.inExpo(range(t, C.room - .14, C.room))) }), cap: 'Scan at the door.' },
  { a: C.room, cam: t => ({ x: 960, y: 500, zoom: lerp(1, 1.08, range(t, C.room, C.bill)) }) },
  { a: C.bill, bill: true },
  { a: C.wall, cam: t => { const u = E.io(range(t, C.wall, C.wall + .8)); return { x: 960, y: lerp(520, 300, u), dz: lerp(0, -720, u) + 60 * range(t, C.wall + .8, C.logo) }; } },
  { a: C.logo, logo: true },
  { a: C.loop, cam: () => ({ x: 310, y: 350, zoom: 2.05 }) },
];
SHOTS.forEach((s, i) => { s.b = SHOTS[i + 1] ? SHOTS[i + 1].a : DUR; });
F.CUTS = SHOTS.slice(1).map(s => s.a);
F.FAST = [[2.2, 2.9], [6.8, 7.6], [7.8, 8.3], [10.9, 12.1], [14, 14.6], [14.75, 15.75], [16.15, 16.45], [16.55, 16.75], [17.3, 17.5], [20.4, 21], [23.5, 24.3]];
// a breath of light on the hits: each card's cut, the stamp, the check, the room filling
const FLASHES = [[C.plan, .16], [C.promote, .16], [C.sell, .16], [16.25, .22], [T_CHK, .14], [C.room, .1], [C.bill, .12], [C.logo, .12]];
const flashEl = $('#flash');

F.film = t => {
  let shot = SHOTS[0]; for (const s of SHOTS) if (t >= s.a) shot = s;
  world(t);
  if (shot.cam) F.setCam(shot.cam(t));
  const night = t >= C.night && t < C.bill;

  /* the line this shot carries: it rises once, and stays put across cuts that keep it */
  let text = null, at = shot.a;
  if (shot.cap) { if (Array.isArray(shot.cap)) { if (t >= shot.cap[0]) { text = shot.cap[1]; at = shot.cap[0]; } } else { text = shot.cap; for (let i = SHOTS.indexOf(shot) - 1; i >= 0 && SHOTS[i].cap === text; i--) at = SHOTS[i].a; } }
  vis(cap, !!text);
  if (text) { if (cap._t !== text) { cap._t = text; cap.textContent = text; } const r = E.rise(range(t, at + .04, at + .4)); cap.style.transform = `translate(0,${(84 + (1 - r) * 26).toFixed(1)}px)`; cap.style.opacity = clamp(r * 1.6); }
  vis(bignum, !!shot.big);
  if (shot.big) { const v = String(soldAt(t)); if (bigN._t !== v) { bigN._t = v; bigN.textContent = v; } bignum.style.transform = `translate(0,${(236 + (1 - E.rise(range(t, shot.a, shot.a + .3))) * 30).toFixed(1)}px) scale(${(1 + .03 * Math.sin((t - shot.a) * 2 * Math.PI * 9) * (t < SOLD[1] ? 1 : 0)).toFixed(4)})`; }

  /* cards */
  vcards.forEach((v, i) => { const onc = shot.card === i; vis(v.e, onc); if (onc) { const d = t - shot.a; v.vb.style.transform = `scale(${(lerp(1.22, 1, E.outExpo(clamp(d / .3))) * (1 + .03 * d)).toFixed(4)})`; v.vb.style.opacity = clamp(d / .05); } });
  vis(billc, !!shot.bill);
  if (shot.bill) {
    const d = t - shot.a;
    billTop.style.transform = `scaleX(${E.rise(range(d, 0, .35)).toFixed(4)})`; billRows.style.transform = `scale(${(1 + .012 * d).toFixed(4)})`;
    billNames.forEach((e, i) => { const q = d - .16 - i * .1; e.style.transform = `scale(${lerp(1.36, 1, E.outExpo(clamp(q / .22))).toFixed(4)})`; e.style.opacity = clamp(q / .04); });
  }
  const wallAct = t >= C.wall && t < C.logo, vk = E.io(range(t, C.wall + .7, C.wall + 1.05));
  vis(veil, wallAct && vk > .002); veil.style.opacity = vk; vis(fin, wallAct && t >= C.wall + .85);
  if (wallAct) { fin.style.transform = 'translate(150px,300px)'; f1.style.transform = `translateY(${((1 - E.rise(range(t, C.wall + .9, C.wall + 1.5))) * 115).toFixed(2)}%)`; f2.style.transform = `translateY(${((1 - E.rise(range(t, C.wall + 1.2, C.wall + 1.8))) * 112).toFixed(2)}%)`; }
  vis(lcard, !!shot.logo);
  if (shot.logo) {
    const d = t - shot.a, a = spring(d - .04, 1.9, .56), sl = (1 - E.outExpo(range(d, .04, .5))) * 46;
    lock.style.transform = `translate(960px,400px) translate(-50%,-50%) scale(${(lerp(.84, 1, a) * (1 + .012 * d)).toFixed(4)})`; lock.style.opacity = clamp(d / .08);
    lockE.setAttribute('transform', `translate(${-.51 * sl} ${.86 * sl})`); lockS.setAttribute('transform', `translate(${.51 * sl} ${-.86 * sl})`);
    const a2 = E.rise(range(d, .3, .85)), a3 = E.rise(range(d, .42, .97));
    place(tagl, 960, 566 + 30 * (1 - a2), 1, 0, a2); place(site, 960, 648 + 30 * (1 - a3), 1, 0, a3);
  }
  stageEl.classList.toggle('night', night);
  let fl = 0; for (const [a, k] of FLASHES) if (t >= a) fl += k * Math.exp(-(t - a) / .07);
  flashEl.style.opacity = (Math.min(.5, fl) * (F.LIGHT && !night ? .45 : 1)).toFixed(4);     // on paper a flash is a tint: keep it faint
};
})();
