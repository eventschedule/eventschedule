/**
 * Landing page interaction engine.
 *
 * Scroll reveals, scrubbed scroll scenes (video straighten, horizontal
 * gallery, how-it-works story), pointer choreography (hero spotlight,
 * chip parallax, 3D card tilt, magnetic buttons), velocity-reactive
 * marquee, odometer stats, count-ups, the video facade, the URL claim
 * input, and the confetti finale.
 *
 * Everything here is progressive enhancement over a fully readable
 * static page: `html.es-anim` (added by an inline script in the view,
 * only when motion is allowed) gates the hidden pre-reveal states, and
 * every system below checks reduced-motion and pointer capability
 * before engaging.
 */

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
const desktop = window.matchMedia('(min-width: 1024px)');

const clamp = (v, min, max) => Math.min(max, Math.max(min, v));
const lerp = (a, b, t) => a + (b - a) * t;

// Synchronous viewport check used to trigger elements already in view at
// init time immediately, instead of waiting a frame for the observer's
// first async delivery (also keeps screenshot tools and anchor landings
// from catching pre-reveal states).
function inViewNow(el) {
    const r = el.getBoundingClientRect();
    return (r.width || r.height)
        && r.top < window.innerHeight * 0.95
        && r.bottom > 0
        && r.left < window.innerWidth
        && r.right > 0;
}

/* ------------------------------------------------------------------ */
/* Scroll reveals                                                      */
/* ------------------------------------------------------------------ */

function initReveals() {
    const els = Array.from(document.querySelectorAll('[data-reveal]'));
    if (!els.length) {
        return;
    }

    if (reduceMotion.matches || !('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('is-revealed'));
        return;
    }

    document.querySelectorAll('[data-reveal-group]').forEach((group) => {
        const step = parseInt(group.getAttribute('data-reveal-group'), 10) || 90;
        group.querySelectorAll('[data-reveal]').forEach((el, i) => {
            el.style.setProperty('--reveal-delay', Math.min(i * step, 700) + 'ms');
        });
    });

    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }
            entry.target.classList.add('is-revealed');
            io.unobserve(entry.target);
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -6% 0px' });

    els.forEach((el) => {
        if (inViewNow(el)) {
            el.classList.add('is-revealed');
        } else {
            io.observe(el);
        }
    });
}

/* ------------------------------------------------------------------ */
/* Scrubbed scroll scenes                                              */
/* ------------------------------------------------------------------ */

const scenes = [];
let scrollQueued = false;

function runScenes() {
    if (scrollQueued) {
        return;
    }
    scrollQueued = true;
    requestAnimationFrame(() => {
        scrollQueued = false;
        scenes.forEach((scene) => scene());
    });
}

function initShowcaseScene() {
    const wrap = document.querySelector('[data-scene="showcase"]');
    if (!wrap) {
        return;
    }
    const frame = wrap.querySelector('.es-frame');
    const glow = wrap.querySelector('.es-frame-glow');
    if (!frame) {
        return;
    }

    scenes.push(() => {
        if (!desktop.matches) {
            frame.style.transform = '';
            if (glow) {
                glow.style.opacity = '';
            }
            return;
        }
        const rect = wrap.getBoundingClientRect();
        const vh = window.innerHeight;
        // 0 while the frame is still below the fold, 1 once it has
        // risen to roughly the top third of the viewport.
        const p = clamp((vh - rect.top) / (vh * 0.85), 0, 1);
        const rx = (1 - p) * 16;
        const sc = 0.93 + p * 0.07;
        frame.style.transform = 'perspective(1200px) rotateX(' + rx.toFixed(2) + 'deg) scale(' + sc.toFixed(3) + ')';
        if (glow) {
            glow.style.opacity = (p * 0.85).toFixed(2);
        }
    });
}

function initGalleryScene() {
    const section = document.querySelector('[data-scene="gallery"]');
    if (!section) {
        return;
    }
    const rail = section.querySelector('.es-rail');
    const clip = section.querySelector('.es-rail-clip');
    const bar = section.querySelector('.es-rail-progress');
    if (!rail || !clip) {
        return;
    }

    scenes.push(() => {
        if (!desktop.matches) {
            rail.style.transform = '';
            return;
        }
        const rect = section.getBoundingClientRect();
        const vh = window.innerHeight;
        const total = rect.height - vh;
        if (total <= 0) {
            return;
        }
        const p = clamp(-rect.top / total, 0, 1);
        const max = Math.max(0, rail.scrollWidth - clip.clientWidth);
        rail.style.transform = 'translate3d(' + (-p * max).toFixed(1) + 'px, 0, 0)';
        if (bar) {
            bar.style.transform = 'scaleX(' + p.toFixed(3) + ')';
        }
    });
}

function initStepsScene() {
    const section = document.querySelector('[data-scene="steps"]');
    if (!section) {
        return;
    }
    const typed = section.querySelector('.es-type-url');
    const fullUrl = typed ? (typed.getAttribute('data-full') || typed.textContent) : '';
    const bar = section.querySelector('.es-steps-progress');

    scenes.push(() => {
        if (!desktop.matches) {
            if (section.getAttribute('data-active-step') !== 'all') {
                section.setAttribute('data-active-step', 'all');
                if (typed) {
                    typed.textContent = fullUrl;
                }
            }
            return;
        }
        const rect = section.getBoundingClientRect();
        const vh = window.innerHeight;
        const total = rect.height - vh;
        if (total <= 0) {
            return;
        }
        const p = clamp(-rect.top / total, 0, 1);
        const step = Math.min(2, Math.floor(p * 3));
        if (section.getAttribute('data-active-step') !== String(step)) {
            section.setAttribute('data-active-step', String(step));
        }
        if (bar) {
            bar.style.transform = 'scaleY(' + p.toFixed(3) + ')';
        }
        if (typed && fullUrl) {
            // Type the URL out over the first two thirds of step 2.
            const seg = clamp((p * 3 - 1) * 1.6, 0, 1);
            const text = fullUrl.slice(0, Math.round(seg * fullUrl.length));
            if (typed.textContent !== text) {
                typed.textContent = text;
            }
        }
    });
}

function initScenes() {
    if (reduceMotion.matches) {
        return;
    }
    initShowcaseScene();
    initGalleryScene();
    initStepsScene();
    if (scenes.length) {
        window.addEventListener('scroll', runScenes, { passive: true });
        window.addEventListener('resize', runScenes, { passive: true });
        runScenes();
    }
}

/* ------------------------------------------------------------------ */
/* Pointer choreography                                                */
/* ------------------------------------------------------------------ */

function initPointer() {
    if (reduceMotion.matches || !finePointer.matches) {
        return;
    }

    // Hero: cursor spotlight + depth parallax on the floating chips.
    const hero = document.querySelector('.es-hero');
    if (hero) {
        const chips = Array.from(hero.querySelectorAll('.es-chip'));
        const spot = hero.querySelector('.es-spot');
        let targetX = 0;
        let targetY = 0;
        let curX = 0;
        let curY = 0;
        let raf = null;

        const settle = () => {
            curX = lerp(curX, targetX, 0.09);
            curY = lerp(curY, targetY, 0.09);
            chips.forEach((chip) => {
                const depth = parseFloat(chip.getAttribute('data-depth')) || 20;
                chip.style.transform = 'translate3d(' + (curX * depth).toFixed(1) + 'px, ' + (curY * depth).toFixed(1) + 'px, 0)';
            });
            if (Math.abs(curX - targetX) > 0.001 || Math.abs(curY - targetY) > 0.001) {
                raf = requestAnimationFrame(settle);
            } else {
                raf = null;
            }
        };

        hero.addEventListener('pointermove', (e) => {
            const rect = hero.getBoundingClientRect();
            if (spot) {
                spot.style.setProperty('--mx', (e.clientX - rect.left) + 'px');
                spot.style.setProperty('--my', (e.clientY - rect.top) + 'px');
                spot.style.opacity = '1';
            }
            targetX = (e.clientX - rect.left) / rect.width - 0.5;
            targetY = (e.clientY - rect.top) / rect.height - 0.5;
            if (!raf) {
                raf = requestAnimationFrame(settle);
            }
        });
        hero.addEventListener('pointerleave', () => {
            if (spot) {
                spot.style.opacity = '0';
            }
            targetX = 0;
            targetY = 0;
            if (!raf) {
                raf = requestAnimationFrame(settle);
            }
        });
    }

    // 3D tilt + cursor-tracked glare on cards.
    document.querySelectorAll('[data-tilt]').forEach((card) => {
        const inner = card.querySelector('.es-tilt-inner') || card;
        const max = parseFloat(card.getAttribute('data-tilt')) || 5;
        card.addEventListener('pointermove', (e) => {
            const r = card.getBoundingClientRect();
            const px = (e.clientX - r.left) / r.width;
            const py = (e.clientY - r.top) / r.height;
            inner.style.transform = 'perspective(900px) rotateX(' + ((0.5 - py) * max).toFixed(2) + 'deg) rotateY(' + ((px - 0.5) * max).toFixed(2) + 'deg)';
            card.style.setProperty('--gx', (px * 100).toFixed(1) + '%');
            card.style.setProperty('--gy', (py * 100).toFixed(1) + '%');
        });
        card.addEventListener('pointerleave', () => {
            inner.style.transform = '';
        });
    });

    // Magnetic buttons: pull toward the cursor, spring back on leave.
    document.querySelectorAll('[data-magnetic]').forEach((btn) => {
        const strength = parseFloat(btn.getAttribute('data-magnetic')) || 0.22;
        btn.addEventListener('pointermove', (e) => {
            const r = btn.getBoundingClientRect();
            const dx = e.clientX - (r.left + r.width / 2);
            const dy = e.clientY - (r.top + r.height / 2);
            btn.style.transition = 'transform 0.1s linear';
            btn.style.transform = 'translate(' + (dx * strength).toFixed(1) + 'px, ' + (dy * strength).toFixed(1) + 'px)';
        });
        btn.addEventListener('pointerleave', () => {
            btn.style.transition = 'transform 0.5s cubic-bezier(0.22, 1, 0.36, 1)';
            btn.style.transform = '';
        });
    });
}

/* ------------------------------------------------------------------ */
/* Velocity-reactive marquee                                           */
/* ------------------------------------------------------------------ */

function initMarquee() {
    const rows = Array.from(document.querySelectorAll('[data-marquee]'));
    if (!rows.length || reduceMotion.matches) {
        return;
    }

    const state = rows.map((row) => ({
        row: row,
        track: row.querySelector('.es-marquee-track'),
        dir: parseFloat(row.getAttribute('data-marquee')) || 1,
        pos: null,
        half: 0,
        hover: false,
        visible: true,
    }));

    state.forEach((s) => {
        if (!s.track) {
            return;
        }
        // Take over from the CSS keyframe fallback.
        s.track.style.animation = 'none';
        s.row.addEventListener('pointerenter', () => { s.hover = true; });
        s.row.addEventListener('pointerleave', () => { s.hover = false; });
    });

    if ('IntersectionObserver' in window) {
        const vis = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                const s = state.find((x) => x.row === entry.target);
                if (s) {
                    s.visible = entry.isIntersecting;
                }
            });
        });
        state.forEach((s) => vis.observe(s.row));
    }

    let lastY = window.scrollY;
    let velocity = 0;
    let last = performance.now();

    const frame = (now) => {
        const dt = Math.min((now - last) / 1000, 0.05);
        last = now;
        const y = window.scrollY;
        if (dt > 0) {
            velocity = lerp(velocity, clamp((y - lastY) / dt / 1000, -1.5, 1.5), 0.12);
        }
        lastY = y;

        state.forEach((s) => {
            if (!s.track || !s.visible) {
                return;
            }
            if (!s.half) {
                s.half = s.track.scrollWidth / 2;
                if (!s.half) {
                    return;
                }
                s.pos = s.dir === -1 ? -s.half : 0;
            }
            const speed = s.hover ? 0 : (34 + Math.abs(velocity) * 320);
            s.pos -= speed * dt * s.dir;
            if (s.pos <= -s.half) {
                s.pos += s.half;
            }
            if (s.pos > 0) {
                s.pos -= s.half;
            }
            const skew = clamp(velocity * 7, -9, 9);
            s.track.style.transform = 'translate3d(' + s.pos.toFixed(1) + 'px, 0, 0) skewX(' + skew.toFixed(2) + 'deg)';
        });
        requestAnimationFrame(frame);
    };
    requestAnimationFrame(frame);
}

/* ------------------------------------------------------------------ */
/* Odometer stats                                                      */
/* ------------------------------------------------------------------ */

function initOdometers() {
    const els = document.querySelectorAll('[data-odometer]');
    if (!els.length || reduceMotion.matches || !('IntersectionObserver' in window)) {
        // The markup already contains the final value as text.
        return;
    }

    const DIGITS = '0123456789';

    els.forEach((el) => {
        const value = (el.getAttribute('data-odometer') || el.textContent).trim();
        el.textContent = '';
        el.setAttribute('role', 'img');
        el.setAttribute('aria-label', value);
        const strips = [];

        value.split('').forEach((ch) => {
            if (DIGITS.indexOf(ch) !== -1) {
                const col = document.createElement('span');
                col.className = 'es-od-col';
                col.setAttribute('aria-hidden', 'true');
                const strip = document.createElement('span');
                strip.className = 'es-od-strip';
                // Two full 0-9 runs, then the target digit: index 20.
                (DIGITS + DIGITS + ch).split('').forEach((d) => {
                    const digit = document.createElement('span');
                    digit.textContent = d;
                    strip.appendChild(digit);
                });
                col.appendChild(strip);
                el.appendChild(col);
                strips.push(strip);
            } else {
                const span = document.createElement('span');
                span.textContent = ch;
                span.setAttribute('aria-hidden', 'true');
                el.appendChild(span);
            }
        });

        const roll = () => {
            strips.forEach((strip, i) => {
                strip.style.transitionDelay = (i * 120) + 'ms';
                requestAnimationFrame(() => {
                    strip.style.transform = 'translateY(-20em)';
                });
            });
        };

        if (inViewNow(el)) {
            roll();
            return;
        }
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                io.disconnect();
                roll();
            });
        }, { threshold: 0.5 });
        io.observe(el);
    });
}

/* ------------------------------------------------------------------ */
/* Count-up numbers                                                    */
/* ------------------------------------------------------------------ */

function initCounters() {
    const els = document.querySelectorAll('[data-count-to]');
    if (!els.length) {
        return;
    }
    if (reduceMotion.matches || !('IntersectionObserver' in window)) {
        // The markup already contains the final value as text.
        return;
    }

    const animate = (el) => {
        const raw = el.getAttribute('data-count-to');
        const target = parseFloat(raw.replace(/[^0-9.]/g, '')) || 0;
        const grouped = raw.indexOf(',') !== -1;
        const start = performance.now();
        const duration = 1400;
        const tick = (now) => {
            const t = clamp((now - start) / duration, 0, 1);
            const eased = 1 - Math.pow(1 - t, 3);
            const n = Math.round(target * eased);
            el.textContent = grouped ? n.toLocaleString('en-US') : String(n);
            if (t < 1) {
                requestAnimationFrame(tick);
            }
        };
        requestAnimationFrame(tick);
    };

    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }
            io.unobserve(entry.target);
            animate(entry.target);
        });
    }, { threshold: 0.6 });

    els.forEach((el) => {
        if (inViewNow(el)) {
            return;
        }
        el.textContent = '0';
        io.observe(el);
    });
}

/* ------------------------------------------------------------------ */
/* Video facade                                                        */
/* ------------------------------------------------------------------ */

function initVideoFacade() {
    const facade = document.querySelector('[data-video-facade]');
    if (!facade) {
        return;
    }
    facade.addEventListener('click', (e) => {
        e.preventDefault();
        const src = facade.getAttribute('data-video-src');
        if (!src) {
            return;
        }
        const shell = facade.parentElement;
        const iframe = document.createElement('iframe');
        iframe.className = 'absolute inset-0 h-full w-full';
        iframe.src = src + (src.indexOf('?') !== -1 ? '&' : '?') + 'autoplay=1';
        iframe.title = facade.getAttribute('data-video-title') || 'Video';
        iframe.setAttribute('frameborder', '0');
        iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
        iframe.setAttribute('allowfullscreen', '');
        facade.remove();
        shell.appendChild(iframe);
    });
}

/* ------------------------------------------------------------------ */
/* URL claim input                                                     */
/* ------------------------------------------------------------------ */

function initClaim() {
    const input = document.getElementById('es-claim-input');
    if (!input) {
        return;
    }
    input.addEventListener('input', () => {
        const slug = input.value.toLowerCase()
            .replace(/['’]/g, '')
            .replace(/[^a-z0-9-]+/g, '-')
            .replace(/-{2,}/g, '-')
            .replace(/^-+/, '')
            .slice(0, 30);
        if (input.value !== slug) {
            input.value = slug;
        }
    });
}

/* ------------------------------------------------------------------ */
/* Confetti finale                                                     */
/* ------------------------------------------------------------------ */

function initConfetti() {
    const target = document.querySelector('[data-confetti]');
    if (!target || reduceMotion.matches || !('IntersectionObserver' in window)) {
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting || typeof window.confetti !== 'function') {
                return;
            }
            io.disconnect();
            const colors = ['#4E81FA', '#0EA5E9', '#22D3EE', '#ffffff'];
            window.confetti({
                particleCount: 80,
                angle: 60,
                spread: 60,
                startVelocity: 55,
                origin: { x: 0.06, y: 0.95 },
                colors: colors,
                disableForReducedMotion: true,
            });
            window.confetti({
                particleCount: 80,
                angle: 120,
                spread: 60,
                startVelocity: 55,
                origin: { x: 0.94, y: 0.95 },
                colors: colors,
                disableForReducedMotion: true,
            });
        });
    }, { threshold: 0.5 });
    io.observe(target);
}

/* ------------------------------------------------------------------ */

function init() {
    initReveals();
    initScenes();
    initPointer();
    initMarquee();
    initOdometers();
    initCounters();
    initVideoFacade();
    initClaim();
    initConfetti();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
