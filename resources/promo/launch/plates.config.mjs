// The plates capture.mjs shoots: real screens of the film's private copy of the app, at The Indigo
// Room, the venue the Product Hunt gallery also shows.
//
//   id        the file (plates/<id>.png, <id>.txt, and <id>.json or `json` where data is pulled)
//   state     the house state it needs: onsale, prescan or doors (fixture/seed.sh)
//   url       f => path, built from fixture.json (ids change with the copy, never hardcode one)
//   auth      signed in as the venue's owner. Guest pages are shot signed out.
//   scheme    'light' or 'dark'. Default: light for a visitor (as the gallery), dark for the owner.
//   viewport  [w, h] CSS px; dsf: device scale (3 = hero); mobile: a phone
//   clip / clipJs   what to cut: a selector, or a script returning {x,y,width,height} in page px
//   isolate   a selector shot alone on a transparent ground
//   before / after  commands run against the copy around the plate (a language, simulated visitors)
//   pre, files, press, post   the kit's steps: a script, real files on an input, real mouse presses
//   states    further shots of the same page, in order; sameClip keeps the first shot's rectangle
//   also      the same moment through another window: { id, clip | clipJs, dsf, isolate, stay }
//   layers    { id, selector } alone on transparent, in the plate's own frame
//   rects     { name: selector } rectangles in the picture's pixels (MANIFEST.json, and the .json)
//   extract   a page function whose result is written to the .json
//   fakes     requests answered in the browser, never by the app
//   staged    said plainly: what in this plate is not the app's own doing
//   manual    a stand-in, shot only when named with --only

import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const KEEP = process.env.LAUNCHFILM_KEEP || path.join(os.homedir(), '.claude/plans/product-hunt-video');
const RUN = path.join(KEEP, 'seed/run.php');
const POSTER = path.join(here, 'fixture/poster/jazz-night.png');
const HERO = 3;
const wait = ms => `await new Promise(r => setTimeout(r, ${ms}));`;
const php = (f, script, ...args) => ['php', RUN, f.copy, path.join(here, 'fixture', script), ...args];

// The card an element sits in: the first ancestor with a ground of its own that is at least minW wide.
const panel = (sel, minW, pad = 24) => `let e = document.querySelector(${JSON.stringify(sel)}); if (!e) return null;
  while (e && e !== document.body && !(e.getBoundingClientRect().width >= ${minW} && getComputedStyle(e).backgroundColor !== 'rgba(0, 0, 0, 0)')) e = e.parentElement;
  if (!e || e === document.body) return null; const b = e.getBoundingClientRect();
  return { x: Math.max(0, b.left + scrollX - ${pad}), y: Math.max(0, b.top + scrollY - ${pad}), width: b.width + ${pad * 2}, height: b.height + ${pad * 2} };`;
// The admin card (.ap-card) an element sits in.
const apCard = find => `let e = ${find}; while (e && !(e.classList && e.classList.contains('ap-card'))) e = e.parentElement; if (!e) return null; const b = e.getBoundingClientRect(); return { x: b.left + scrollX, y: b.top + scrollY, width: b.width, height: b.height };`;

// A second plate of the same thing in the other colour scheme.
const twin = (plate, scheme, suffix = `-${scheme}`) => {
  const re = id => `${id}${suffix}`;
  return { ...plate, id: re(plate.id), scheme, json: null, extract: null, rects: null, layers: null,
    states: (plate.states || []).map(s => ({ ...s, id: re(s.id), json: null, extract: null, also: (s.also || []).map(a => ({ ...a, id: re(a.id) })) })),
    also: (plate.also || []).map(a => ({ ...a, id: re(a.id) })) };
};

/* ---------------------------------------------------------------- schedule pages (signed out) */

const HEAD_RECTS = { header: '#gp-header', logo: '#gp-profile-image', name: '#gp-header h1', actions: '#gp-header .gk-head-actions' };
const top = (id, url, extra = {}) => ({
  id, state: 'onsale', viewport: [1440, 900], dsf: HERO, url,
  waitFor: `!!(document.querySelector('#gp-header h1') && document.querySelector('#gp-events'))`,
  rects: HEAD_RECTS, ...extra,
});

const CARDS = '#gp-events div.rounded-2xl.shadow-sm.overflow-hidden';
// From under the header to the foot of the fifth event.
const FIVE = `const c = [...document.querySelectorAll('${CARDS}')].slice(0, 5); if (c.length < 5) return null;
  const head = document.querySelector('#gp-header').getBoundingClientRect(), a = c[0].getBoundingClientRect(), z = c[4].getBoundingClientRect();
  const y = head.bottom + scrollY + 8; return { x: a.left + scrollX - 12, y, width: a.width + 24, height: z.bottom + scrollY + 12 - y };`;
const list = { id: 'gp-indigo-list', state: 'onsale', viewport: [1440, 3200], dsf: HERO, url: f => f.venue.url,
  waitFor: `document.querySelectorAll('${CARDS}').length >= 5`, clipJs: FIVE, clipTop: 420,
  extract: `(({ clip, dsf }) => ({ rows: [...document.querySelectorAll('${CARDS}')].slice(0, 5).map(e => { const b = e.getBoundingClientRect(); return { name: (e.querySelector('h2,h3')?.textContent || '').trim(), rect: [(b.left + scrollX - clip.x) * dsf, (b.top + scrollY - clip.y) * dsf, b.width * dsf, b.height * dsf].map(Math.round) }; }) }))` };

// The month after this one, as the kit's calendar job asks for it.
const NEXT = (() => { const [y, m] = new Intl.DateTimeFormat('en-CA', { timeZone: 'America/New_York' }).format(new Date()).split('-').map(Number); return m === 12 ? { y: y + 1, m: 1 } : { y, m: m + 1 }; })();
const CELLS = '#gp-calendar .grid.grid-cols-7.gap-px > div';
const month = {
  id: 'gp-indigo-month', state: 'onsale', viewport: [1440, 1300], dsf: HERO,
  url: f => `${f.venue.url}?layout=calendar&year=${NEXT.y}&month=${NEXT.m}`,
  waitFor: `document.querySelectorAll('${CELLS}').length >= 28 && document.querySelectorAll('#gp-calendar .grid.grid-cols-7 li').length >= 10`,
  hide: ['#gp-header-bar'],
  clip: '#gp-calendar', clipTop: 24, sameClip: true,
  rects: { title: '#month-year-title', weekdays: '#gp-calendar .grid.grid-cols-7.gap-px' },
  extract: `(({ clip, dsf }) => {
    const px = v => Math.round(v * dsf * 100) / 100, box = b => [px(b.left + scrollX - clip.x), px(b.top + scrollY - clip.y), px(b.width), px(b.height)];
    const names = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const cells = [...document.querySelectorAll('${CELLS}')].filter(c => c.querySelector('time')).map(cell => {
      const date = cell.querySelector('time').getAttribute('datetime'), [y, m, d] = date.split('-').map(Number);
      return { date, day: d, weekday: names[new Date(Date.UTC(y, m - 1, d)).getUTCDay()], in_month: m === ${NEXT.m}, rect: box(cell.getBoundingClientRect()),
        events: [...cell.querySelectorAll('li')].map(li => ({ name: li.innerText.replace(/\\s+/g, ' ').trim(), rect: box(li.getBoundingClientRect()) })) };
    });
    const jazz = cells.filter(c => c.in_month && c.events.some(e => e.name === 'Jazz Night')).map(c => ({ date: c.date, day: c.day, weekday: c.weekday, cell: c.rect, entry: c.events.find(e => e.name === 'Jazz Night').rect }));
    return { month: (document.querySelector('#month-year-title')?.textContent || '').trim(), jazz_night_saturday: jazz.find(c => c.weekday === 'Sat') || null, jazz_night: jazz, cells };
  })`,
  // the same frame with no events in it: hidden, never removed, so nothing moves
  states: [{ id: 'gp-indigo-month-empty', pre: `const s = document.createElement('style'); s.textContent = '#gp-calendar .grid.grid-cols-7 li { visibility: hidden !important; }'; document.head.appendChild(s);`, waitAfter: 'true', extract: null }],
};

// The same header in other languages. A schedule page is shown in the schedule's own language, so
// the setting is changed in the copy for the shot and put back after.
const lang = code => ({
  id: `lang-${code}`, state: 'onsale', viewport: [1440, 900], dsf: HERO, url: f => f.venue.url,
  before: f => [php(f, 'lang.php', code)], after: f => [php(f, 'lang.php', 'en')],
  waitFor: `!!document.querySelector('#gp-header h1')`, clip: '#gp-header', rects: HEAD_RECTS,
  staged: `The schedule's language setting was set to "${code}" in the copy's database for this shot and put back to English after it. (A schedule page renders in the schedule's own language. ?lang= only switches to the one language the schedule is translated into.)`,
});

/* ---------------------------------------------------------------- Jazz Night's page */

const event = {
  id: 'event-jazz', state: 'onsale', viewport: [1440, 900], dsf: HERO, url: f => f.jazz.url,
  waitFor: `!!(document.querySelector('#gp-event-cta') && document.querySelector('#gp-flyer img')?.complete)`,
  rects: { flyer: '#gp-flyer', flyer_image: '#gp-flyer img', details: '#gp-event-details', title: '#gp-event-title', date: '#gp-event-date', location: '#gp-event-location', price: '#gp-event-price', button: '#gp-event-cta', about: '#gp-about' },
  extract: `(() => ({}))`,
  layers: [
    { id: 'event-jazz.L-bg', selector: null, opaque: true, css: 'body *{visibility:hidden!important}' },
    { id: 'event-jazz.L-left', selector: '#gp-flyer' },
    { id: 'event-jazz.L-right', selector: '#gp-event-details' },
    { id: 'event-jazz.L-rest', selector: '.gk-event-col > *:not(#gp-flyer):not(#gp-event-details)' },
  ],
};

/* ---------------------------------------------------------------- the seat picker */

const PICKER = '.seating-picker-mount';
const release = f => [php(f, 'release.php')];
const SEAT = (row, n) => `[...document.querySelectorAll('${PICKER} circle.seatpick-target')].find(c => new RegExp('^General Admission, Row ${row}, Seat ${n},').test(c.getAttribute('aria-label')))`;
// Where a seat is in the window, for a real press of the mouse.
const seatPoint = (row, n) => `const e = ${SEAT(row, n)}; if (!e) return null; if (e.getAttribute('aria-disabled') === 'true') throw new Error('seat ${row}${n} is taken or held'); const r = e.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 };`;
const pressed = (row, n) => `(() => { const e = ${SEAT(row, n)}; return !!e && e.getAttribute('aria-pressed') === 'true'; })()`;

// Every seat of a map, in the picture's pixels.
const seatsOf = svgSel => `(({ clip, dsf }) => {
  const svg = document.querySelector(${JSON.stringify(svgSel)}), px = v => Math.round(v * dsf * 100) / 100;
  const at = b => ({ x: px(b.left + scrollX - clip.x), y: px(b.top + scrollY - clip.y), width: px(b.width), height: px(b.height) });
  const stageText = [...svg.querySelectorAll('text')].find(t => /^stage$/i.test(t.textContent.trim()));
  const stage = stageText ? stageText.parentElement.querySelector('rect') : null;
  const mount = document.querySelector('${PICKER}').getBoundingClientRect(), m = { x: Math.floor(mount.left + scrollX), y: Math.floor(mount.top + scrollY) };
  const seats = [...svg.querySelectorAll('circle.seatpick-target')].map(c => {
    const b = c.parentElement.querySelector('circle').getBoundingClientRect(), label = c.getAttribute('aria-label') || '';
    const p = label.match(/^(.*?), (?:(Table \\d+), )?(?:Row (\\w+), )?Seat (\\d+)/) || [];
    return { section: p[1] || null, table: p[2] || null, row: p[3] || null, number: p[4] ? +p[4] : null, name: p[3] ? p[3] + p[4] : (p[2] || '') + ', seat ' + p[4],
      cx: px(b.left + b.width / 2 + scrollX - clip.x), cy: px(b.top + b.height / 2 + scrollY - clip.y), r: px(b.width / 2),
      taken: c.getAttribute('aria-disabled') === 'true', chosen: c.getAttribute('aria-pressed') === 'true' };
  });
  return { image: [px(clip.width), px(clip.height)], scale: dsf, map: at(svg.getBoundingClientRect()), stage: stage ? at(stage.getBoundingClientRect()) : null,
    // where this map's picture sits inside picker-0, in picker-0's pixels (both are shot at the same scale)
    map_in_picker_0: { x: px(Math.floor(svg.getBoundingClientRect().left + scrollX) - m.x), y: px(Math.floor(svg.getBoundingClientRect().top + scrollY) - m.y), width: px(Math.ceil(svg.getBoundingClientRect().width)), height: px(Math.ceil(svg.getBoundingClientRect().height)) },
    count: seats.length, sections: Object.fromEntries([...new Set(seats.map(s => s.section))].map(n => [n, seats.filter(s => s.section === n).length])),
    taken: seats.filter(s => s.taken).length, chosen: seats.filter(s => s.chosen).map(s => s.name), seats };
})`;

// The map with every seat in one look. The picker's own drawing is copied, and in the copy each
// seat is replaced by a copy of a seat of its section that the picker drew free (or taken). The
// picker is not on the page for a sold-out house, and the on-sale house is part sold.
const repaint = look => `
  const live = document.querySelector('${PICKER} svg[role=group]:not(#__film_map)');
  document.getElementById('__film_map')?.remove();
  const svg = live.cloneNode(true); svg.id = '__film_map'; svg.style.removeProperty('display'); live.style.display = 'none'; live.parentElement.insertBefore(svg, live);
  const groups = [...svg.querySelectorAll('circle.seatpick-target')].map(t => t.parentElement);
  const target = g => g.querySelector('circle.seatpick-target'), section = g => (target(g).getAttribute('aria-label') || '').split(',')[0];
  const want = '${look === 'sold' ? 'true' : 'false'}', model = {};
  for (const g of groups) if ((target(g).getAttribute('aria-disabled') === 'true') === (want === 'true') && target(g).getAttribute('aria-pressed') !== 'true') model[section(g)] ??= g;
  for (const g of groups) {
    const from = model[section(g)] || Object.values(model)[0]; if (!from) throw new Error('no seat to copy the ${look} look from');
    const copy = from.cloneNode(true); copy.setAttribute('transform', g.getAttribute('transform'));
    const mine = [...g.querySelectorAll('text')].pop(), theirs = [...copy.querySelectorAll('text')].pop(); if (mine && theirs) theirs.textContent = mine.textContent;
    target(copy).setAttribute('aria-label', target(g).getAttribute('aria-label').replace(/, (Available|Taken|Selected)/, ', ${look === 'sold' ? 'Taken' : 'Available'}'));
    target(copy).setAttribute('aria-disabled', want); target(copy).setAttribute('aria-pressed', 'false');
    g.replaceWith(copy);
  }`;

const SEATED = { state: 'onsale', viewport: [1440, 1400], dsf: HERO, url: f => f.jazz.tickets_url, settle: 2200, before: release,
  waitFor: `document.querySelectorAll('${PICKER} circle.seatpick-target').length === 150`, hide: ['#gp-header-bar'], clipTop: 60, sameClip: true };

const seatmap = { ...SEATED, id: 'seatmap',
  pre: repaint('free'), waitAfter: `!!document.querySelector('#__film_map')`, clip: '#__film_map', isolate: '#__film_map',
  json: 'seats', extract: seatsOf('#__film_map'),
  also: [{ id: 'seatmap-6x', clip: '#__film_map', isolate: '#__film_map', dsf: 6 }],
  states: [{ id: 'seatmap-sold', pre: repaint('sold'), waitAfter: `!!document.querySelector('#__film_map')`, extract: null, also: [{ id: 'seatmap-sold-6x', clip: '#__film_map', isolate: '#__film_map', dsf: 6 }] }],
  staged: 'The map is the seat picker\'s own drawing, copied in the page and repainted so every seat wears one look: each seat was replaced by a copy of a seat of its section that the picker itself drew free (seatmap) or taken (seatmap-sold). Geometry, colours and numbers are the app\'s. The picker does not mount for a sold-out house, and the on-sale house is part sold, so neither look exists as a page.',
};

// The picker block, with room below for the "Your seats" panel it grows when a seat is chosen.
const GROW = 112;
const ROW = `const b = [...document.querySelectorAll('button')].find(e => /^Checkout/.test(e.textContent.trim()) && e.getBoundingClientRect().width); if (!b) return null; let row = b.parentElement; while (row && row.getBoundingClientRect().width < 500) row = row.parentElement; const r = row.getBoundingClientRect(); return { x: r.left + scrollX, y: r.top + scrollY, width: r.width, height: r.height };`;
const picker = { ...SEATED, id: 'picker-0',
  clipJs: `const b = document.querySelector('${PICKER}').getBoundingClientRect(); return { x: b.left + scrollX, y: b.top + scrollY, width: b.width, height: b.height + ${GROW} };`,
  rects: { picker: PICKER, map: `${PICKER} svg[role=group]` },
  json: 'picker', extract: seatsOf(`${PICKER} svg[role=group]`),
  states: [
    { id: 'picker-1', press: [seatPoint('C', 7)], pressWait: 1500, waitAfter: pressed('C', 7), post: `document.activeElement && document.activeElement.blur()`, extract: null },
    { id: 'picker-2', press: [seatPoint('C', 8)], pressWait: 1500, waitAfter: pressed('C', 8), post: `document.activeElement && document.activeElement.blur()`, extract: null,
      also: [{ id: 'checkout-btn', clipJs: ROW, dsf: 4.5, stay: true }] },
  ],
};

/* ---------------------------------------------------------------- the ticket, and the door */

const QR_FAKE = [{ match: /\/ticket\/qr_code\//, respond: f => ({ file: f.qr_file, type: 'image/png' }) }];
const ticket = {
  id: 'ticket', state: 'doors', viewport: [1440, 1500], dsf: HERO, url: f => f.buyer.ticket_url,
  waitFor: `!!document.querySelector('#ticket .gk-tk-qr img')?.complete`,
  fakes: QR_FAKE, clip: '#ticket', clipPad: 60, clipTop: 76,
  rects: { ticket: '#ticket', qr: '#ticket .gk-tk-qr img', body: '#ticket .gk-ticket-a', stub: '#ticket .gk-ticket-b' },
  extract: `(() => ({}))`,
  also: [{ id: 'ticket-qr', clip: '#ticket .gk-tk-qr img', dsf: 6 }],
  layers: [{ id: 'ticket.L-body', selector: '#ticket .gk-ticket-a' }, { id: 'ticket.L-stub', selector: '#ticket .gk-ticket-b' }],
  staged: 'The code on the ticket is drawn by the server (TicketController::qrCode), not in the browser. The request for it is answered in the browser with a code for https://eventschedule.com, made by the app\'s own generator (QrCodeUtils), because the real one encodes this copy\'s local address.',
};

// Chrome is given a camera that plays a clip: an empty doorway, then the buyer's own code.
const DOOR_CLIP = scratch => path.join(scratch, 'door.y4m');
const SCAN_OK = `/Scanned Successfully/i.test(document.body.innerText)`;
const scan = {
  id: 'scan', state: 'prescan', auth: true, viewport: [1440, 1000], dsf: HERO, url: f => f.room.scan_url, settle: 1500,
  setup: async (f, { SCRATCH }) => {
    fs.mkdirSync(SCRATCH, { recursive: true });
    const qr = path.join(SCRATCH, 'door-qr.png');
    const r = await fetch(f.base + f.buyer.qr_route); if (!r.ok) throw new Error('could not fetch the ticket\'s code for the door camera');
    fs.writeFileSync(qr, Buffer.from(await r.arrayBuffer()));
    const ff = spawnSync('ffmpeg', ['-y', '-loglevel', 'error', '-f', 'lavfi', '-i', 'color=c=0x1b1f2a:s=640x480:r=5:d=30', '-loop', '1', '-t', '25', '-r', '5', '-i', qr,
      '-filter_complex', '[1:v]scale=220:220:flags=lanczos,pad=300:300:40:40:color=white,pad=640:480:170:90:color=0x1b1f2a,fps=5,format=yuv420p[q];[0:v]format=yuv420p[b];[b][q]concat=n=2:v=1[v]', '-map', '[v]', '-pix_fmt', 'yuv420p', DOOR_CLIP(SCRATCH)], { encoding: 'utf8' });
    if (ff.status !== 0) throw new Error('ffmpeg could not make the door clip: ' + String(ff.stderr || ff.error).slice(-300));
  },
  chromeArgs: (f, { SCRATCH }) => ['--use-fake-device-for-media-stream', '--use-fake-ui-for-media-stream', `--use-file-for-fake-video-capture=${DOOR_CLIP(SCRATCH)}`],
  waitFor: `!!document.querySelector('#reader')`,
  pre: `const btn = re => [...document.querySelectorAll('#reader button')].find(e => re.test(e.textContent) && e.getBoundingClientRect().width);
        const video = () => { const v = document.querySelector('#reader video'); return v && v.readyState >= 2 && v.videoWidth > 0; };
        if (btn(/Camera Permissions/i)) btn(/Camera Permissions/i).click();
        for (let i = 0; i < 100 && !video() && !btn(/Start Scanning/i); i++) ${wait(100)}
        if (!video() && btn(/Start Scanning/i)) btn(/Start Scanning/i).click();
        for (let i = 0; i < 100 && !video(); i++) ${wait(100)}
        if (!video()) throw new Error('the stand-in camera did not start');`,
  clip: '#main-content', sameClip: true,
  states: [{ id: 'scan-ok', pre: `for (let i = 0; i < 700 && !(${SCAN_OK}); i++) ${wait(100)} if (!(${SCAN_OK})) throw new Error('the door camera never read the code: ' + document.querySelector('#main-content').innerText.replace(/\\s+/g, ' ').slice(0, 200));`, waitAfter: SCAN_OK }],
  staged: 'The camera is Chrome\'s stand-in device playing a clip made from this ticket\'s own code (the server\'s picture of it). The page, its scanner, the scan request and the answer are the app\'s own, and the check-in it records is real.',
};
// If Chrome's stand-in camera will not play: the ticket's address is handed to the page's own
// handler. The scan request and the answer on screen are still the app's.
const scanByHand = { id: 'scan-byhand', manual: true, state: 'prescan', auth: true, viewport: [1440, 1000], dsf: HERO, url: f => f.room.scan_url, settle: 1500,
  waitFor: `!!document.querySelector('#reader')`, clip: '#main-content', sameClip: true,
  states: [{ id: 'scan-ok', pre: f => `document.querySelector('#app').__vue_app__._instance.proxy.onScanSuccess(location.origin + ${JSON.stringify(f.buyer ? f.buyer.ticket_url : '')});`, waitAfter: SCAN_OK }],
  staged: 'The camera did not run: the ticket\'s address was handed to the scan page\'s own handler by script. The scan request, the check-in and what the page shows for it are the app\'s own.' };

const main = (id, url, extra = {}) => ({ id, state: 'onsale', auth: true, viewport: [1440, 1300], dsf: HERO, url, clip: '#main-content', ...extra });

/* ---------------------------------------------------------------- the import, with its AI answered here */

// What the app's own parser returns for the poster, in the shape event/import.blade.php reads
// (GeminiUtils::parseEvent). The request never reaches the copy's server, and no key is used.
const todayNY = () => new Intl.DateTimeFormat('en-CA', { timeZone: 'America/New_York' }).format(new Date());
const PARSED = f => ({ parsed: [{
  event_name: 'Jazz Night', short_description: 'Two sets from The Indigo Quartet.', event_date_time: `${todayNY()} 20:00`, event_duration: 3,
  event_details: 'The Indigo Room presents Jazz Night. Tonight at 8:00 PM.', event_address: '', event_city: 'Brooklyn',
  venue_name: 'The Indigo Room', venue_id: f.venue.encoded, venue_subdomain: 'indigo-room', matched_venue_name: 'The Indigo Room',
  ticket_price: 25, ticket_currency_code: 'USD', performers: [], social_image: '/tmp/event_launchfilm_jazz_night.png',
}] });
const IMPORT_FAKES = [
  { match: /\/indigo-room\/parse(\?|$)/, method: 'POST', respond: f => ({ body: PARSED(f) }) },
  { match: /\/tmp\/event-image\//, respond: () => ({ file: POSTER, type: 'image/png' }) },
];
const CARD = `const i = [...document.querySelectorAll('#event-import-app input')].find(e => e.value === 'Jazz Night'); if (!i) return null; let el = i; while (el && !(el.classList && el.classList.contains('ap-card'))) el = el.parentElement; if (!el) return null; el.classList.add('__parsed'); const b = el.getBoundingClientRect(); return { x: b.left + scrollX, y: b.top + scrollY, width: b.width, height: Math.min(b.height, 1380) };`;
const READ = `const b = [...document.querySelectorAll('#event-import-app button')].find(e => /Read flyer/i.test(e.textContent) && !e.disabled && e.getBoundingClientRect().width); if (!b) return null; const r = b.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 };`;
const importPlate = {
  id: 'import-empty', state: 'onsale', auth: true, viewport: [1440, 1500], dsf: HERO, url: f => f.import_url, settle: 1800,
  waitFor: `!!(window.__importApp && document.querySelector('#event_details'))`,
  fakes: IMPORT_FAKES,
  clipJs: apCard(`document.querySelector('#event_details')`),   // each state cuts its own card: the box loses a line of help when a flyer is in it
  rects: { box: '#event_details' },
  states: [
    // the poster, put on the page's own file input as a person choosing it would
    { id: 'import-flyer', files: { selector: '#event-import-app input[type=file]', paths: () => [POSTER], wait: 1800 }, waitAfter: `!!window.__importApp.detailsImageUrl` },
    { id: 'import-parsed', press: [READ], pressWait: 2500, waitAfter: `!!(window.__importApp.preview && window.__importApp.preview.parsed && window.__importApp.preview.parsed.length && [...document.querySelectorAll('#event-import-app input')].some(e => e.value === 'Jazz Night'))`, clipJs: CARD,
      json: 'import-parsed',
      extract: `(({ clip, dsf }) => { const card = document.querySelector('.__parsed'); const px = v => Math.round(v * dsf * 100) / 100, box = e => { const b = e.getBoundingClientRect(); return [px(b.left + scrollX - clip.x), px(b.top + scrollY - clip.y), px(b.width), px(b.height)]; };
        const shown = e => e.getBoundingClientRect().width > 0 && e.getBoundingClientRect().bottom + scrollY <= clip.y + clip.height;
        const fields = [...card.querySelectorAll('input:not([type=hidden]):not([type=radio]):not([type=checkbox]):not([type=file]), textarea, select')].filter(shown).map(e => ({ name: e.getAttribute('name') || e.id || '', value: e.tagName === 'SELECT' ? (e.selectedOptions[0]?.textContent || '').trim() : e.value, rect: box(e) })).filter(x => x.value);
        const by = v => fields.find(x => x.value === v) || null, img = [...card.querySelectorAll('img')].find(shown);
        const venue = [...card.querySelectorAll('p, a, span, option:checked, div')].filter(e => e.children.length === 0 && /The Indigo Room/.test(e.textContent) && e.getBoundingClientRect().width > 0).map(box);
        return { title: by('Jazz Night')?.rect || null, date: fields.filter(x => /^20\\d\\d-\\d\\d-\\d\\d$|^\\w{3} \\d{1,2}, 20\\d\\d$/.test(x.value)).map(x => x.rect), time: fields.filter(x => /^\\d{1,2}:\\d\\d ?([AP]M)?$/i.test(x.value)).map(x => ({ value: x.value, rect: x.rect })), venue, image: img ? box(img) : null, fields }; })` },
    // the same card with what was read taken out of it: the frame the values land in
    { id: 'import-parsed-blank', clipJs: CARD, extract: null, waitAfter: 'true',
      pre: `const s = document.createElement('style'); s.textContent = '.__parsed input, .__parsed textarea, .__parsed select, .__parsed .ss-main, .__parsed .ss-main * { color: transparent !important; -webkit-text-fill-color: transparent !important; } .__parsed img, .__parsed .text-green-600, .__parsed .CodeMirror-code, .__parsed .editor-preview { visibility: hidden !important; } .__parsed input::placeholder, .__parsed textarea::placeholder { color: transparent !important; }'; document.head.appendChild(s);
            [...document.querySelectorAll('.__parsed p, .__parsed a, .__parsed span')].filter(e => e.children.length === 0 && /The Indigo Room|Brooklyn/.test(e.textContent)).forEach(e => e.style.setProperty('visibility', 'hidden', 'important'));` },
  ],
  staged: 'The reading of the flyer is answered in the browser with the event the poster names (the copy has no AI key, and no request reaches the parse route or anything behind it). The box, the button, the request and the card that shows the result are the app\'s own. import-parsed-blank is import-parsed with the read values made invisible by a style, nothing moved.',
};

/* ---------------------------------------------------------------- the list */

// The app's own simulator (a headless browser is a bot to the tracker, so nothing else can make a
// visit), then the film's script that brings those visitors to the venue's pages.
const simulate = f => [['php', path.join(f.copy, 'artisan'), 'realtime:simulate', '--purge'], ['php', path.join(f.copy, 'artisan'), 'realtime:simulate', '--visitors=46', '--unidentified=70'], php(f, 'visitors.php')];
const heading = re => `[...document.querySelectorAll('#main-content h1, #main-content h2, #main-content h3, #main-content h4, #main-content div, #main-content span, #main-content p')].find(e => e.children.length === 0 && ${re}.test(e.textContent.trim()))`;
const nextDay = `const days = [...document.querySelectorAll('#booking-app button.aspect-square')].filter(b => !b.disabled); const d = days[1] || days[0]; if (!d) return null; const r = d.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 };`;
const slot3 = `const b = [...document.querySelectorAll('#booking-app button')].find(e => e.textContent.trim() === '3:00 PM'); if (!b) return null; const r = b.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 };`;

const checkin = main('checkin', f => f.room.checkin_url, { state: 'doors', viewport: [1440, 1500], settle: 1500, waitFor: `/142/.test(document.querySelector('#main-content')?.innerText || '')`, maxH: 1400 });
const sales = main('sales', f => f.room.sales_url, { state: 'doors', viewport: [1440, 1100], waitFor: `/Avery Lane/.test(document.body.innerText)`,
  clipJs: `const t = document.querySelector('#main-content table'); if (!t) return null; const rows = [...t.querySelectorAll('tbody tr')].filter(r => r.getBoundingClientRect().height > 20); if (rows.length < 6) return null; const a = t.getBoundingClientRect(), z = rows[5].getBoundingClientRect(); return { x: a.left + scrollX, y: a.top + scrollY, width: a.width, height: z.bottom - a.top };`,
  rects: { rows: '#main-content table tbody tr' }, also: [{ id: 'sales-page', clip: '#main-content', maxH: 1000 }] });
const realtime = main('realtime', f => f.analytics.realtime_url, { state: 'doors', before: simulate,
  staged: 'The visitors are the app\'s own simulator\'s (realtime:simulate: a headless browser cannot make a visit, the tracker drops it as a bot), and the film\'s fixture/visitors.php points the simulated schedule-page visits at The Indigo Room and at Jazz Night\'s page. The sales, check-ins and followers beside them are the seeded records, read by the page as it stands.', settle: 4000, viewport: [1440, 1500], waitFor: `/Right now/i.test(document.querySelector('#main-content')?.innerText || '')`,
  clipJs: apCard(heading('/^Right now$/i')),
  also: [{ id: 'realtime-rail', clipJs: apCard(heading('/^Activity$/i')).replace('height: b.height', 'height: Math.min(b.height, 1100)') }, { id: 'realtime-page', clip: '#main-content', maxH: 1380 }] });

const plates = [
  // ---- signed out, on sale
  top('gp-indigo-top', f => f.venue.url),
  twin(top('gp-indigo-top', f => f.venue.url), 'dark'),
  list, twin(list, 'dark'),
  month,
  top('gp-late-laughs-top', f => f.looks['late-laughs'].url),
  top('gp-eastside-top', f => f.looks.eastside.url),
  top('gp-riverside-top', f => f.looks.riverside.url),
  top('gp-orpheum-top', f => f.looks.orpheum.url),
  ...['es', 'de', 'fr', 'it', 'pt', 'nl', 'he', 'ar'].map(lang),
  event, twin(event, 'dark'),
  { id: 'event-phone', state: 'onsale', viewport: [390, 844], dsf: HERO, mobile: true, url: f => f.jazz.url, waitFor: `!!document.querySelector('#gp-flyer img')?.complete` },
  seatmap, twin(seatmap, 'dark'),   // before the picker: a pressed seat stays held for a while
  picker,
  { id: 'book-types', state: 'onsale', viewport: [1440, 900], dsf: HERO, url: f => f.booking.url, settle: 1800, waitFor: `document.querySelectorAll('.es-type-card').length >= 2`, clipJs: panel('.es-type-card', 800) },
  { id: 'book-slots-0', state: 'onsale', viewport: [1440, 900], dsf: HERO, url: f => f.booking.type_url, settle: 2600, waitFor: `document.querySelectorAll('#booking-app button.aspect-square').length > 27`,
    press: [nextDay], pressWait: 1500, waitAfter: `[...document.querySelectorAll('#booking-app button')].some(e => e.textContent.trim() === '3:00 PM')`,
    post: `document.activeElement && document.activeElement.blur()`,
    clipJs: panel('#booking-app button.aspect-square', 900), sameClip: true,
    states: [{ id: 'book-slots', press: [slot3], pressWait: 1200, post: `document.activeElement && document.activeElement.blur()`, waitAfter: `!!document.querySelector('#booking-app .es-slot-armed')` }] },
  { id: 'signin', state: 'onsale', viewport: [1440, 900], dsf: HERO, url: () => '/login', waitFor: `!!document.querySelector('form[action$="/login"]')`, clipJs: `const c = document.querySelector('.auth-card').getBoundingClientRect(); const marks = [...document.querySelectorAll('a, img, svg')].map(e => e.getBoundingClientRect()).filter(b => b.width > 60 && b.bottom <= c.top + 4 && b.top > c.top - 200); const t = marks.length ? Math.min(...marks.map(b => b.top)) : c.top; return { x: c.left + scrollX - 56, y: t + scrollY - 48, width: c.width + 112, height: c.bottom - t + 104 };` },

  // ---- signed in, on sale
  importPlate, twin(importPlate, 'light'),
  main('newsletter-email', f => f.newsletter.edit_url, { settle: 4500, viewport: [1440, 1500], waitFor: `!!document.querySelector('iframe.block')`, clip: 'iframe.block', maxH: 1200,
    also: [{ id: 'newsletter-builder', clip: '#main-content', maxH: 1380 }] }),
  main('newsletter-sent', f => f.newsletter.stats_url, { settle: 2500, viewport: [1440, 1300], waitFor: `/940/.test(document.querySelector('#main-content')?.innerText || '')`,
    clipJs: `const c = document.querySelector('#main-content .ap-card'), h = document.querySelector('#main-content h1'); if (!c || !h) return null; const cb = c.getBoundingClientRect(), hb = h.getBoundingClientRect(); return { x: cb.left + scrollX - 24, y: hb.top + scrollY - 44, width: cb.width + 48, height: cb.bottom - hb.top + 44 + 24 };`,
    also: [{ id: 'newsletter-stats', clip: '#main-content', maxH: 1200 }] }),
  main('newsletter-list', f => f.newsletter.list_url, { viewport: [1440, 900], waitFor: `/940/.test(document.querySelector('#main-content')?.innerText || '')`, maxH: 520 }),
  main('embed-dialog', f => f.admin_url, { viewport: [1440, 1100], settle: 2500, pre: `document.getElementById('embed-schedule-link').click(); ${wait(1500)}`, waitAfter: `!!document.querySelector('#embed-preview-iframe')`, clipJs: panel('#embed-preview-box', 600, 32) }),

  // ---- the door: a real scan first (prescan), then the full house (doors)
  scan, scanByHand,
  ticket,
  checkin, twin(checkin, 'light'),
  sales, twin(sales, 'light'),
  realtime, twin(realtime, 'light'),
];

export default plates;
