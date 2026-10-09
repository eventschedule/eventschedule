// The preview's sound. The page renders the score once (the same offline render the film is
// mastered from) and plays that buffer; nothing is scheduled in real time, so what is heard here
// is the deliverable before mastering. The picture follows the audio clock:
//
//   await A.Player.load('dawn');            // or load('dawn', {cues}) ; re-load when cues change
//   A.Player.play(12.5);                    // inside a click or key handler (the context resumes there)
//   requestAnimationFrame(function tick() { seek(A.Player.time()); requestAnimationFrame(tick); });
//   A.Player.stop();                        // time() then holds where it stopped
//
// A.Player.loadFile('out/audio/dawn.master.wav') plays a mastered file through an <audio>
// element instead (works from file://), with the same play / stop / time.
'use strict';
(() => {
const A = window.A;
const P = A.Player = {
  ctx: null, buf: null, src: null, el: null, id: null, playing: false, started: false, token: null, at: 0, t0film: 0, t0ctx: 0, loop: null,
  async load(scoreId, opts = {}) { P.stop(); P.el = null; P.buf = await A.Score.render({ score: scoreId, ...opts }); P.id = scoreId; return P.buf; },
  loadFile(url) { P.stop(); P.buf = null; P.el = new Audio(url); P.el.preload = 'auto'; P.id = url; return new Promise((res, rej) => { P.el.oncanplaythrough = () => res(P.el); P.el.onerror = () => rej(new Error('cannot play ' + url)); }); },
  // loop: [from, to] in seconds, or null
  play(fromT = 0, loop = null) {
    P.stop(); P.at = Math.max(0, Math.min(A.DUR - .01, fromT)); P.loop = loop;
    if (P.el) { P.el.currentTime = P.at; P.el.loop = false; P.el.play(); P.playing = true; return; }
    if (!P.buf) return;
    if (!P.ctx) { P.ctx = new AudioContext({ sampleRate: A.SR }); P.out = P.ctx.createGain(); P.out.gain.value = Math.pow(10, 5 / 20); const lim = P.ctx.createDynamicsCompressor(); lim.threshold.value = -6; lim.knee.value = 4; lim.ratio.value = 12; lim.attack.value = .003; lim.release.value = .12; P.out.connect(lim); lim.connect(P.ctx.destination); }
    // resume() is called here, inside the click; the source is scheduled once the clock really runs
    // (a context can take half a second to open its device), and time() holds at `at` until then.
    const token = P.token = {}; P.playing = true; P.started = false;
    P.ctx.resume().then(() => {
      if (P.token !== token || !P.playing) return;
      const src = P.src = P.ctx.createBufferSource(); src.buffer = P.buf; src.connect(P.out);
      if (loop) { src.loop = true; src.loopStart = loop[0]; src.loopEnd = loop[1]; }
      P.t0ctx = P.ctx.currentTime + .06; P.t0film = P.at; src.start(P.t0ctx, P.at); P.started = true;
      src.onended = () => { if (P.src === src) { P.at = A.DUR; P.playing = false; } };
    });
  },
  stop() { if (P.playing) P.at = P.time(); P.playing = false; P.token = null; if (P.src) { const s = P.src; P.src = null; try { s.onended = null; s.stop(); } catch (e) { /* not started */ } } if (P.el) P.el.pause(); },
  // film time from the audio clock (what is leaving the speakers now)
  time() {
    if (!P.playing) return P.at;
    if (P.el) return P.el.currentTime;
    if (!P.started) return P.at;
    let t = P.t0film + (P.ctx.currentTime - P.t0ctx) - (P.ctx.outputLatency || P.ctx.baseLatency || 0);
    if (P.loop && t > P.loop[1]) t = P.loop[0] + ((t - P.loop[0]) % (P.loop[1] - P.loop[0]));
    return Math.max(P.t0film, Math.min(A.DUR, t));
  },
};
})();
