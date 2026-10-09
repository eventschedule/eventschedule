// The sound effects: one cue from the picture's timeline, one sound. The same track serves all
// three scores, so nothing here may depend on which score is playing: pitches come from D major
// pentatonic, from A and E, from the shared hook, or from the root under the cue (A.rootAt).
//
// A cue is {t, type, intensity, dur?, pan?, n?}. Gain is 0.4 + 0.6 * intensity. Times:
//   whoosh    t is its PEAK (it starts 0.45 * dur earlier)
//   riser, scan, shimmer, glint    t is the START and dur the length; they end at t + dur
//   everything else    t is the onset
// Lows are mono and dry. Levels are dBFS peak at intensity 1.
'use strict';
(() => {
const A = window.A, E = A.E, V = E.V, { db, mtof, seeded, snap, AD, bq, curveOf } = E;
const HIGH = { n: 8 };          // body and slap stand 8 dB above (the ring 13) the composer's sketch, so the stamp out-measures every other moment
const STAMP_DB = -10.5;       // the boom's level, set by measurement so the whole stamp peaks at -4 dBFS
const SEAT_FIRST = [81, 86]; // the buyer's two seats: A5, D6

A.SFX = {
  types: ['whoosh', 'tick', 'tap', 'count', 'glint', 'subdrop', 'stamp', 'powerdown', 'scan', 'chime', 'seat', 'shimmer', 'peak', 'riser', 'logo'],
  arrange(S, cues) {
    const ctx = S.ctx, I = (n, c) => S.inst(n, 'sfx', c);
    I('whoosh', { level: -20, H: -14 }); I('tick', { level: -28, P: -16 }); I('tap', { level: -24, P: -18 }); I('count', { level: -32, P: -16 });
    I('ping', { level: -18, H: -6 }); I('blip', { level: -32, H: -6 }); I('subdrop', { level: -17 });   // 8 dB under the sketch: a held low sine measures as loud as the stamp otherwise
    I('stamp', { level: STAMP_DB }); I('stampAir', { level: STAMP_DB - 18, H: 0 }); I('stampRing', { level: STAMP_DB - 20, H: -6 });
    I('powerdown', { level: -24, H: -18 }); I('scan', { level: -30, H: -12 });
    I('chime', { level: -14, H: -8, D: -14 }); I('chimeSmall', { level: -17, H: -10 });
    I('seat', { level: -30, H: -10, D: -18 }); I('shimmer', { level: -22, H: -6 });
    I('peakBell', { level: -26, H: -4 }); I('peakAir', { level: -24, H: -8 });   // four bells at once: -26 each lands the whole near -13
    I('riser', { level: -16 }); I('riserTone', { level: -18, H: -10 });
    I('logoSub', { level: -19 }); I('logoBell', { level: -24, H: -4, D: -12 }); I('logoAir', { level: -34, H: -2 });   // the composer's balance, 8 dB down: the whole peaks at -8
    const B = S.bus, sc = c => .4 + .6 * (c.intensity == null ? 1 : c.intensity);
    const sine = (f, t, stop) => { const o = ctx.createOscillator(); o.type = 'sine'; o.frequency.value = f; o.start(t); o.stop(stop); return o; };
    const noise = (ch, t, dur, key) => { const s = ctx.createBufferSource(); s.buffer = S.noise[ch]; s.loop = true; s.start(t, seeded('sfx:' + key)() * 3.9); s.stop(t + dur); return s; };
    const panTo = (node, p, out) => { if (!p) { node.connect(out); return node; } const sp = ctx.createStereoPanner(); sp.pan.value = p; node.connect(sp); sp.connect(out); return sp; };
    const mark = (c, kind) => S.mark(kind || c.type, snap(c.t), 'sfx');

    const tickLike = (c, bus, midi) => {
      const t = snap(c.t); mark(c);
      S.at(t, () => { const f = mtof(midi), o = sine(f, t, t + .05), g = ctx.createGain(), sum = ctx.createGain(), n = noise(0, t, .02, 'tick' + t), hp = bq(ctx, 'highpass', 6000, -3), ng = ctx.createGain(); o.frequency.setValueAtTime(1.5 * f, t); o.frequency.exponentialRampToValueAtTime(f, t + .008); AD(g.gain, t, 1, 28, sc(c)); AD(ng.gain, t, .5, 4, sc(c) * db(-12)); o.connect(g); g.connect(sum); n.connect(hp); hp.connect(ng); ng.connect(sum); S.done(t + .06, panTo(sum, .5 * (c.pan || 0), bus)); });
    };
    const chimePair = (c, bus, mul) => { const t = snap(c.t); V.chimeNote(S, bus, t, 81, sc(c), mul); V.chimeNote(S, bus, t + .125, 86, sc(c), mul); };

    const H = {
      tick: c => tickLike(c, B.tick, [86, 90, 93][Math.floor(seeded('tick' + (c.n || 0))() * 3)]),
      count: c => tickLike(c, B.count, A.PENT5[(c.n || 0) % 5] + 12 * Math.floor((c.n || 0) / 5)),
      tap: c => {
        const t = snap(c.t); mark(c);
        S.at(t, () => { const sum = ctx.createGain(), g = ctx.createGain(), n = noise(0, t, .02, 'tap' + t), lp = bq(ctx, 'lowpass', 2000, -3), ng = ctx.createGain(); AD(g.gain, t, 1.5, 45, sc(c) * .6); AD(ng.gain, t, .5, 6, sc(c) * db(-14)); for (const m of [74, 81]) sine(mtof(m), t, t + .07).connect(g); g.connect(sum); n.connect(lp); lp.connect(ng); ng.connect(sum); S.done(t + .08, panTo(sum, .5 * (c.pan || 0), B.tap)); });
      },
      whoosh: c => {
        const dur = c.dur || .4, t0 = Math.max(0, snap(c.t - .45 * dur)), dir = (c.pan || 0) < 0 ? -1 : 1, wide = c.pan ? .6 : .15;
        S.at(t0, () => { for (const ch of [0, 1]) { const n = noise(ch, t0, dur + .02, 'whoosh' + t0 + ch), bp = bq(ctx, 'bandpass', 300, 1.2), hp = bq(ctx, 'highpass', 250, -3), g = ctx.createGain(), sp = ctx.createStereoPanner(); bp.frequency.setValueAtTime(300, t0); bp.frequency.exponentialRampToValueAtTime(3000, t0 + .6 * dur); bp.frequency.exponentialRampToValueAtTime(1200, t0 + dur); g.gain.setValueCurveAtTime(curveOf(128, u => sc(c) * (u < .45 ? Math.pow(Math.sin(Math.PI / 2 * u / .45), 2) : Math.pow(Math.cos(Math.PI / 2 * (u - .45) / .55), 2))), t0, dur); const off = ch ? .2 : -.2; sp.pan.setValueAtTime(Math.max(-1, Math.min(1, -wide * dir + off)), t0); sp.pan.linearRampToValueAtTime(Math.max(-1, Math.min(1, wide * dir + off)), t0 + dur); n.connect(bp); bp.connect(hp); hp.connect(g); g.connect(sp); sp.connect(B.whoosh); S.done(t0 + dur + .03, sp); } });
      },
      // a low sine that falls onto the root under it: the verb cards
      subdrop: c => {
        const t = snap(c.t), f0 = A.rootAt(t + .01); mark(c); S.sfxDucks.push({ t, depth: -9, pre: 0, len: .5 });
        S.at(t, () => { const g = ctx.createGain(), a = g.gain, k = sc(c); a.setValueAtTime(0, t); a.linearRampToValueAtTime(k, t + .03); a.setValueAtTime(k, t + .3); a.exponentialRampToValueAtTime(k * .001, t + 1.4); a.linearRampToValueAtTime(0, t + 1.405); [[1, 1], [2, db(-10)]].forEach(([h, lv]) => { const o = sine(f0 * h, t, t + 1.42), og = ctx.createGain(); og.gain.value = lv; o.frequency.setValueAtTime(3 * f0 * h, t); o.frequency.exponentialRampToValueAtTime(f0 * h, t + .28); o.connect(og); og.connect(g); }); g.connect(B.subdrop); S.done(t + 1.45, g); });
      },
      // the biggest sound in the film: boom, body, slap, air, ring. The boom falls over 1400 ms, not the
      // sketch's 900: the same peak, more weight in the 400 ms a loudness meter (and an ear) integrates.
      stamp: c => {
        const t = snap(c.t), k = sc(c); mark(c); S.sfxDucks.push({ t, depth: -18, pre: .02, len: .6 });
        S.at(t, () => {
          const boom = ctx.createGain(), sh = ctx.createWaveShaper(), n = 2049, cv = new Float32Array(n); for (let i = 0; i < n; i++) cv[i] = Math.tanh(3 * (i / (n - 1) * 2 - 1)) / Math.tanh(3); sh.curve = cv; sh.oversample = 'none';   // driven harder than the sketch (k 3, not 2): more weight for the same peak
          AD(boom.gain, t, 2, 1400, k); [[220, 73.42, 1], [110, 36.71, .5]].forEach(([a, b, lv]) => { const o = sine(a, t, t + 1.43), og = ctx.createGain(); og.gain.value = lv; o.frequency.setValueAtTime(a, t); o.frequency.exponentialRampToValueAtTime(b, t + .09); o.connect(og); og.connect(boom); }); const blp = bq(ctx, 'lowpass', 1800, -3); boom.connect(sh); sh.connect(blp); blp.connect(B.stamp); S.done(t + 1.45, blp);
          const body = ctx.createGain(), nb = noise(0, t, .14, 'stampBody'), lp = bq(ctx, 'lowpass', 900, -3), bp = bq(ctx, 'bandpass', 180, .7); AD(body.gain, t, 1, 120, k * db(-10 + HIGH.n)); nb.connect(lp); nb.connect(bp); lp.connect(body); bp.connect(body); body.connect(B.stamp); S.done(t + .15, body);
          for (const ch of [0, 1]) { const ns = noise(ch, t, .04, 'stampSlap' + ch), hp = bq(ctx, 'highpass', 2000, -3), l2 = bq(ctx, 'lowpass', 7000, -3), g = ctx.createGain(); AD(g.gain, t, .5, 25, k * db(-16 + HIGH.n)); ns.connect(hp); hp.connect(l2); l2.connect(g); S.done(t + .05, panTo(g, ch ? .8 : -.8, B.stamp)); }
          const air = ctx.createGain(), na = noise(1, t, 1.45, 'stampAir'), hpa = bq(ctx, 'highpass', 6000, -3); AD(air.gain, t, 30, 1400, k); na.connect(hpa); hpa.connect(air); air.connect(B.stampAir); S.done(t + 1.46, air);
          const ring = ctx.createGain(); AD(ring.gain, t, 3, 1800, k * db(HIGH.n + 5) / 3); for (const m of [50, 57, 62]) sine(mtof(m), t, t + 1.83).connect(ring); ring.connect(B.stampRing); S.done(t + 1.85, ring);
        });
      },
      powerdown: c => {
        const t = snap(c.t), k = sc(c); mark(c);
        S.at(t, () => { const g = ctx.createGain(), o = sine(220, t, t + 1.05); o.frequency.setValueAtTime(220, t); o.frequency.exponentialRampToValueAtTime(55, t + 1); AD(g.gain, t, 20, 1000, k); o.connect(g); const n = noise(0, t, 1.03, 'pd'), lp = bq(ctx, 'lowpass', 4000, -3), ng = ctx.createGain(); lp.frequency.setValueAtTime(4000, t); lp.frequency.exponentialRampToValueAtTime(200, t + 1); AD(ng.gain, t, 20, 1000, k * db(-6)); n.connect(lp); lp.connect(ng); ng.connect(g); g.connect(B.powerdown); S.done(t + 1.06, g); });
      },
      // the beam reading the code: it rises, trembling, and stops 20 ms short of the chime
      scan: c => {
        const t = snap(c.t), dur = (c.dur || .9) - .02, k = sc(c);
        S.at(t, () => { const o = sine(587, t, t + dur + .01), trem = ctx.createGain(), g = ctx.createGain(), lfo = sine(24, t, t + dur + .01), lg = ctx.createGain(); o.frequency.setValueAtTime(587, t); o.frequency.exponentialRampToValueAtTime(1175, t + dur); trem.gain.value = .75; lg.gain.value = .25; lfo.connect(lg); lg.connect(trem.gain); g.gain.setValueAtTime(0, t); g.gain.linearRampToValueAtTime(k * .3, t + .01); g.gain.linearRampToValueAtTime(k, t + dur - .01); g.gain.linearRampToValueAtTime(0, t + dur); o.connect(trem); trem.connect(g); g.connect(B.scan); S.done(t + dur + .02, g); });
      },
      // A5 then D6. The full one belongs to the scan at the door; a quiet one is shorter and drier.
      chime: c => { mark(c); if ((c.intensity == null ? 1 : c.intensity) >= .75) chimePair(c, B.chime, 1); else chimePair(c, B.chimeSmall, .6); },
      // sparkle for dur, then one ping (A5 and E6 together) at t + dur
      glint: c => {
        const t = snap(c.t), dur = c.dur || 2, k = sc(c), r = seeded('glint' + t), n = Math.max(4, Math.round(8 * dur));
        for (let i = 0; i < n; i++) { const tt = snap(t + dur * (.1 + .85 * Math.sqrt((i + r()) / n))), m = A.PENT5[Math.floor(r() * 5)] + 12, p = (r() - .5) * 1.2; S.at(tt, () => { const g = ctx.createGain(); AD(g.gain, tt, 2, 90, k); sine(mtof(m), tt, tt + .11).connect(g); S.done(tt + .12, panTo(g, p, B.blip)); }); }
        S.mark('ping', snap(t + dur), 'sfx'); V.chimeNote(S, B.ping, t + dur, 81, k * .7); V.chimeNote(S, B.ping, t + dur, 88, k * .7);
      },
      shimmer: c => {
        const t = snap(c.t), dur = c.dur || 2, k = sc(c);
        S.at(t, () => {
          const g = ctx.createGain(); g.gain.setValueCurveAtTime(curveOf(256, u => k * Math.pow(u, 1.5)), t, dur - .012); g.gain.linearRampToValueAtTime(0, t + dur - .001);
          [86, 90, 93, 95].forEach((m, i) => { const r = seeded('shim' + m), o = sine(mtof(m), t, t + dur), tr = ctx.createGain(), lfo = sine(5 + 4 * r(), t, t + dur), lg = ctx.createGain(); tr.gain.value = .2; lg.gain.value = .08; lfo.connect(lg); lg.connect(tr.gain); o.connect(tr); S.done(t + dur + .02, panTo(tr, [-.5, .2, -.2, .5][i], g)); });
          const nn = noise(1, t, dur, 'shimN'), hp = bq(ctx, 'highpass', 8000, -3), st = ctx.createGain(), r = seeded('shimStep'); for (let i = 0; i < Math.ceil(dur * 32); i++) st.gain.setValueAtTime(db(-8) * (.25 + .75 * r()), t + i / 32); nn.connect(hp); hp.connect(st); st.connect(g);
          g.connect(B.shimmer); S.done(t + dur + .02, g);
        });
      },
      peak: c => {
        const t = snap(c.t), k = sc(c); mark(c);
        for (const m of [81, 83, 88, 93]) V.chimeNote(S, B.peakBell, t, m, k, 1.4);
        S.at(t, () => { const n = noise(0, t, .93, 'peak'), hp = bq(ctx, 'highpass', 7000, -3), g = ctx.createGain(); AD(g.gain, t, 2, 900, k); n.connect(hp); hp.connect(g); g.connect(B.peakAir); S.done(t + .95, g); });
      },
      // noise that climbs, and a tone gliding A4 to A5 that trembles faster as it goes
      riser: c => {
        const t = snap(c.t), dur = c.dur || 1.75, k = sc(c);
        V.riser(S, B.riser, t, t + dur, k);
        S.at(t, () => { const g = ctx.createGain(), lp = bq(ctx, 'lowpass', 800, -3), tr = ctx.createGain(), lfo = sine(8, t, t + dur), lg = ctx.createGain(); lp.frequency.setValueAtTime(800, t); lp.frequency.exponentialRampToValueAtTime(5000, t + dur); lfo.frequency.setValueAtTime(8, t); lfo.frequency.exponentialRampToValueAtTime(32, t + dur); tr.gain.value = .85; lg.gain.value = .15; lfo.connect(lg); lg.connect(tr.gain); g.gain.setValueCurveAtTime(curveOf(256, u => k * u * u), t, dur - .017); g.gain.linearRampToValueAtTime(0, t + dur - .001); const o1 = ctx.createOscillator(); o1.setPeriodicWave(S.waves.SOFTSAW); const o2 = sine(440, t, t + dur); for (const o of [o1, o2]) { o.frequency.setValueAtTime(440, t); o.frequency.exponentialRampToValueAtTime(880, t + dur); } o1.start(t); o1.stop(t + dur); const og = ctx.createGain(); og.gain.value = .5; o1.connect(og); o2.connect(og); og.connect(lp); lp.connect(tr); tr.connect(g); g.connect(B.riserTone); S.done(t + dur + .02, g); });
      },
      // the last chord's own weight and bells
      logo: c => {
        const t = snap(c.t), k = sc(c); mark(c); S.sfxDucks.push({ t, depth: -9, pre: 0, len: .5 });
        S.at(t, () => { const g = ctx.createGain(); AD(g.gain, t, 2, 1400, k); [[147, 73.42, 1], [73.4, 36.71, db(-8)]].forEach(([a, b, lv]) => { const o = sine(a, t, t + 1.43), og = ctx.createGain(); og.gain.value = lv; o.frequency.setValueAtTime(a, t); o.frequency.exponentialRampToValueAtTime(b, t + .08); o.connect(og); og.connect(g); }); g.connect(B.logoSub); S.done(t + 1.45, g); for (const ch of [0, 1]) { const n = noise(ch, t, 2.56, 'logoAir' + ch), hp = bq(ctx, 'highpass', 6000, -3), ng = ctx.createGain(); AD(ng.gain, t, 30, 2500, k); n.connect(hp); hp.connect(ng); S.done(t + 2.58, panTo(ng, ch ? .8 : -.8, B.logoAir)); } });
        for (const m of [74, 81, 86, 90]) V.chimeNote(S, B.logoBell, t, m, k, 3);
      },
    };
    H.impact = H.subdrop; H.ping = c => { mark(c); V.chimeNote(S, B.ping, c.t, 81, sc(c)); V.chimeNote(S, B.ping, c.t, 88, sc(c)); };

    // Seats. The buyer's two (n 0 and 1) are A5 and D6; the next eight play the hook; the rest
    // climb the pentatonic, get quieter as they crowd, and thin to 16 a second under the shimmer.
    const seats = cues.filter(c => c.type === 'seat').sort((a, b) => a.t - b.t); let last = -1;
    seats.forEach(c => {
      const n = c.n || 0, t = snap(c.t), rate = seats.filter(s => Math.abs(s.t - c.t) <= .25).length * 2;
      if (n >= 10) { if (t - last < 1 / 16 - 1e-6) return; last = t; }
      const midi = n < 2 ? SEAT_FIRST[n] : n < 10 ? A.HOOK[n - 2] + 12 : A.PENT5[(2 * n + Math.floor(3 * seeded('seat' + n)())) % 5] + 12 * Math.floor(2 * n / 142);
      const lift = n < 2 ? 6 : n < 10 ? 5 : -10 * Math.log10(Math.max(1, rate / 4)), d = n < 2 ? 260 : n < 10 ? 170 : 90, k = sc(c) * db(lift);
      S.mark('seat', t, 'sfx');
      S.at(t, () => { const sum = ctx.createGain(), g1 = ctx.createGain(), g2 = ctx.createGain(), f = mtof(midi); const end = AD(g1.gain, t, 2, d, k); AD(g2.gain, t, 2, d, k * db(-12)); sine(f, t, end + .01).connect(g1); sine(2 * f, t, end + .01).connect(g2); g1.connect(sum); g2.connect(sum); S.done(end + .02, panTo(sum, .7 * (c.pan || 0), B.seat)); });
    });
    const unknown = {};
    for (const c of cues) { if (c.type === 'seat') continue; if (H[c.type]) H[c.type](c); else unknown[c.type] = 1; }
    S.sfxUnknown = Object.keys(unknown);
  },
};
A.SFX.stampLift = n => { HIGH.n = n; };
})();
