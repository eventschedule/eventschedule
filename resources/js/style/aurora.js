// Aurora in the Style tab: a port of the colour arithmetic of App\Utils\Aurora, so the preview and
// the palette swatches draw the colours the public page will. The page hands over the palettes and
// the compositions (the PHP class is their only source); what is ported is how a seed is read and
// how a source colour is lit for each mode. tests/fixtures/aurora.json and the browser test hold
// the two equal: change a number here and change it there.

const GREY_BELOW = 0.08;
const PEAK = { light: 0.9, dark: 0.82 };
const GRAIN = { light: 0.07, dark: 0.11 };
const INK_LIGHT = [0x33, 0x38, 0x3c];
const INK_DARK = [255, 255, 255];

function rgb(hex) {
    const h = String(hex).replace('#', '');

    return [parseInt(h.slice(0, 2), 16), parseInt(h.slice(2, 4), 16), parseInt(h.slice(4, 6), 16)];
}

export function toHex(c) {
    return '#' + c.map((v) => {
        const whole = Math.round(Math.max(0, Math.min(255, v)));

        return (whole < 16 ? '0' : '') + whole.toString(16);
    }).join('');
}

export function triplet(c) {
    return c.map((v) => Math.round(Math.max(0, Math.min(255, v)))).join(' ');
}

// [hue 0-360, saturation 0-1, lightness 0-1], as Aurora::toHsl().
function toHsl(c) {
    const r = c[0] / 255;
    const g = c[1] / 255;
    const b = c[2] / 255;
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    let h = 0;
    let s = 0;
    const l = (max + min) / 2;
    if (max !== min) {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        if (max === r) {
            h = (g - b) / d + (g < b ? 6 : 0);
        } else if (max === g) {
            h = (b - r) / d + 2;
        } else {
            h = (r - g) / d + 4;
        }
        h *= 60;
    }

    return [h, s, l];
}

// PHP's fmod() keeps the sign of the dividend, as JavaScript's % does.
function fromHsl(hsl) {
    let [h, s, l] = hsl;
    h = ((h % 360) + 360) % 360;
    const c = (1 - Math.abs(2 * l - 1)) * s;
    const x = c * (1 - Math.abs(((h / 60) % 2) - 1));
    const m = l - c / 2;
    let parts;
    if (h < 60) {
        parts = [c, x, 0];
    } else if (h < 120) {
        parts = [x, c, 0];
    } else if (h < 180) {
        parts = [0, c, x];
    } else if (h < 240) {
        parts = [0, x, c];
    } else if (h < 300) {
        parts = [x, 0, c];
    } else {
        parts = [c, 0, x];
    }

    return [(parts[0] + m) * 255, (parts[1] + m) * 255, (parts[2] + m) * 255];
}

function luminance(c) {
    const lin = (v) => {
        v /= 255;

        return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
    };

    return 0.2126 * lin(c[0]) + 0.7152 * lin(c[1]) + 0.0722 * lin(c[2]);
}

function contrast(a, b) {
    const x = luminance(a);
    const y = luminance(b);

    return (Math.max(x, y) + 0.05) / (Math.min(x, y) + 0.05);
}

function over(fg, a, bg) {
    return [fg[0] * a + bg[0] * (1 - a), fg[1] * a + bg[1] * (1 - a), fg[2] * a + bg[2] * (1 - a)];
}

export function hex(value) {
    let v = String(value === null || value === undefined ? '' : value).trim();
    const short = /^#([0-9a-f])([0-9a-f])([0-9a-f])$/i.exec(v);
    if (short) {
        v = '#' + short[1] + short[1] + short[2] + short[2] + short[3] + short[3];
    }

    return /^#[0-9a-f]{6}$/i.test(v) ? v.toLowerCase() : null;
}

export function decode(seed, compositions) {
    return {
        comp: seed % compositions,
        mirror: (seed >> 3) & 1,
        phase: (seed >> 4) % 60,
        still: ((seed >> 6) & 15) / 15,
        harmony: (seed >> 10) % 3,
    };
}

export function harmonise(accent, harmony) {
    const [h, s] = toHsl(rgb(accent));
    if (s < GREY_BELOW) {
        return [accent.toLowerCase(), toHex(fromHsl([h, 0, 0.55])), toHex(fromHsl([h, 0, 0.75]))];
    }
    const offsets = [[0, 28, -34], [0, 40, 190], [0, -62, 118]][harmony % 3];

    return offsets.map((o) => toHex(fromHsl([h + o, s, 0.55])));
}

function relight(hex, mode, i, ground, alpha) {
    let [h, s] = toHsl(rgb(hex));
    const grey = s < GREY_BELOW;
    if (mode === 'light') {
        s = grey ? 0 : Math.max(0.7, Math.min(0.95, s));
        let l = [0.56, 0.60, 0.64, 0.54][i];
        let c = fromHsl([h, s, l]);
        for (let n = 0; n < 40 && contrast(over(c, alpha, ground), INK_LIGHT) < 4.5; n++) {
            l += 0.01;
            c = fromHsl([h, s, l]);
        }

        return c;
    }
    s = grey ? 0 : Math.max(0.6, Math.min(0.9, s));
    let l = [0.50, 0.46, 0.42, 0.56][i];
    let c = fromHsl([h, s, l]);
    for (let n = 0; n < 50 && contrast(over(c, alpha, ground), INK_DARK) < 4.5; n++) {
        l -= 0.01;
        c = fromHsl([h, s, l]);
    }

    return c;
}

// The three sources of a palette, as Aurora::fromParts() picks them.
export function sources(palette, accent, ownColors, seed, palettes, compositions) {
    if (palettes[palette]) {
        return palettes[palette].colors.map((c) => c.toLowerCase());
    }
    const own = (ownColors || []).map(hex).filter(Boolean);
    if ((palette === 'custom' || palette === 'header') && own.length === 3) {
        return own;
    }

    return harmonise(hex(accent) || '#4e81fa', decode(seed, compositions).harmony);
}

export function tokens(srcs, accent, mode) {
    const [h0, s0] = toHsl(rgb(srcs[0]));
    const grey = s0 < GREY_BELOW;
    const ground = mode === 'light'
        ? fromHsl([h0, grey ? 0 : Math.min(s0, 0.6) * 0.55, 0.972])
        : fromHsl([h0, grey ? 0 : 0.32, 0.062]);
    const alpha = PEAK[mode];
    const lights = [srcs[0], srcs[1], srcs[2], accent].map((c, i) => relight(c, mode, i, ground, alpha));

    return { ground, lights, alpha, grain: GRAIN[mode] };
}

// The custom properties a layer's colours ride in (Aurora::css(), one mode).
export function auroraVars(t) {
    const out = { '--aurora-ground': triplet(t.ground), '--aurora-a': String(t.alpha), '--aurora-grain': String(t.grain) };
    t.lights.forEach((light, i) => {
        out['--aurora-c' + i] = triplet(light);
    });

    return out;
}

// Each light's style, as Aurora::lightStyles() writes it, its keyframes named for the composition
// (writeKeyframes()), so a layer fading out keeps its own drift while the next one fades in.
export function lightStyles(seed, compositions) {
    const d = decode(seed, compositions.length);

    return compositions[d.comp].map(([x, y, w, h, c, a, dx, dy, scale, turn, period], i) => ({
        '--x': x,
        '--y': y,
        '--w': w,
        '--h': h,
        '--c': 'var(--aurora-c' + c + ')',
        '--a': a,
        '--dx': dx + '%',
        '--dy': dy + '%',
        '--s': scale,
        '--r': turn + 'deg',
        '--t': Math.round(d.still * 10000) / 10000,
        animationName: 'aurora-k' + d.comp + '-' + i,
        animationDuration: period + 's',
        animationDelay: '-' + ((d.phase + i * 13) % 60) + 's',
    }));
}

// The drift of every composition, with the numbers written out (Aurora::keyframes()), in one
// <style> of the page's, written once.
export function writeKeyframes(compositions) {
    let css = '';
    compositions.forEach((lights, comp) => {
        lights.forEach(([, , , , , , dx, dy, scale, turn], i) => {
            css += '@keyframes aurora-k' + comp + '-' + i + '{from{transform:translate3d(0,0,0) scale(1) rotate(0deg)}'
                + 'to{transform:translate3d(' + dx + '%,' + dy + '%,0) scale(' + scale + ') rotate(' + turn + 'deg)}}';
        });
    });
    let el = document.getElementById('aurora-keyframes');
    if (! el) {
        el = document.createElement('style');
        el.id = 'aurora-keyframes';
        const nonce = document.querySelector('script[nonce]');
        if (nonce && nonce.nonce) {
            el.nonce = nonce.nonce;
        }
        document.head.appendChild(el);
    }
    if (el.textContent !== css) {
        el.textContent = css;
    }
}

// One still frame as stacked gradients (Aurora::staticGradient()), with the ground under it: for
// the palette swatches and the row's chip.
export function staticGradient(t, seed, compositions) {
    const d = decode(seed, compositions.length);
    const layers = compositions[d.comp].slice(0, 4).map(([x, y, w, h, c, a]) => {
        const at = d.mirror ? 100 - x : x;
        const light = triplet(t.lights[c]);

        return 'radial-gradient(' + Math.round(w * 0.75) + '% ' + h + '% at ' + at + '% ' + y + '%, rgb(' + light + ' / '
            + (Math.round(a * t.alpha * 100) / 100) + '), rgb(' + light + ' / ' + (Math.round(a * t.alpha * 0.35 * 100) / 100) + ') 45%, transparent 75%)';
    });

    return layers.concat(['rgb(' + triplet(t.ground) + ')']).join(', ');
}

// A new arrangement that looks different: another composition, or the same one mirrored.
export function nextSeed(seed, compositions) {
    const now = decode(seed, compositions);
    for (let tries = 0; tries < 64; tries++) {
        const next = Math.floor(Math.random() * 65536);
        const then = decode(next, compositions);
        if (then.comp !== now.comp || then.mirror !== now.mirror) {
            return next;
        }
    }

    return (seed + 1) % 65536;
}

// The three colours of a picture the browser may read (a file picked on this visit, a built-in
// header's thumbnail), for the preview until Save: the server samples the saved picture itself
// (Aurora::dominantColors(), the same method). Resolves to null where the canvas is not allowed.
export function sampleImage(src) {
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => {
            try {
                const canvas = document.createElement('canvas');
                canvas.width = 24;
                canvas.height = 24;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, 24, 24);
                const data = ctx.getImageData(0, 0, 24, 24).data;
                const px = [];
                for (let i = 0; i < data.length; i += 4) {
                    const c = [data[i], data[i + 1], data[i + 2]];
                    const [, s, l] = toHsl(c);
                    if (l > 0.06 && l < 0.96) {
                        px.push([c, 0.25 + s]);
                    }
                }
                resolve(px.length < 3 ? null : kmeans(px));
            } catch (error) {
                resolve(null);
            }
        };
        img.onerror = () => resolve(null);
        // A picture on another host (the CDN) can be read only where it allows it; where it does
        // not, the load fails and the colours the server sampled stand.
        if (/^https?:/i.test(src) && new URL(src, window.location.href).origin !== window.location.origin) {
            img.crossOrigin = 'anonymous';
        }
        img.src = src;
    });
}

function kmeans(px) {
    const dist = (a, b) => (a[0] - b[0]) ** 2 + (a[1] - b[1]) ** 2 + (a[2] - b[2]) ** 2;
    const centres = [px[0][0]];
    while (centres.length < 3) {
        let best = null;
        let bestD = -1;
        px.forEach(([c, w]) => {
            const d = Math.min(...centres.map((q) => dist(c, q))) * w;
            if (d > bestD) {
                bestD = d;
                best = c;
            }
        });
        centres.push(best);
    }
    let groups = [];
    for (let round = 0; round < 10; round++) {
        groups = [0, 1, 2].map(() => ({ sum: [0, 0, 0], w: 0 }));
        px.forEach(([c, w]) => {
            let k = 0;
            let kd = Infinity;
            centres.forEach((q, j) => {
                const d = dist(c, q);
                if (d < kd) {
                    kd = d;
                    k = j;
                }
            });
            groups[k].w += w;
            [0, 1, 2].forEach((i) => {
                groups[k].sum[i] += c[i] * w;
            });
        });
        groups.forEach((g, j) => {
            if (g.w > 0) {
                centres[j] = g.sum.map((v) => v / g.w);
            }
        });
    }

    return [0, 1, 2].sort((a, b) => groups[b].w - groups[a].w).map((j) => toHex(centres[j]));
}
