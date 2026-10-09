// The Style tab of the schedule form (resources/views/role/edit.blade.php, #section-style).
//
// The tab's fields are server-rendered and carry the values: a <select> for the font, the
// gradient and the two pictures, radios, colour inputs. The pickers and the preview
// (resources/js/components/Style*.vue) are views of those fields. This module is the one place
// that knows the fields by id: it reads them into `state`, and `setField()` is how a picker
// changes one.
//
// Two rules hold the page together:
//  - setField() dispatches `input` and `change`, which is what marks the tab unsaved, redraws
//    the row's line and reaches the page's own handlers. It is never called while starting up:
//    the save bar takes any such event inside the form as a change.
//  - state is read AFTER the page's inline script has put back what a refused save had typed
//    (it sets the font and the gradient with jQuery's val(), which fires no event), and again
//    whenever the page says `style:sync`.

import { reactive } from 'vue';

export const DEFAULT_ACCENT = '#4e81fa';

// The Header Image values that name no picture (Role::HEADER_IMAGE_KEYWORDS).
export const HEADER_KEYWORDS = ['none', 'gradient', 'logos'];

export const state = reactive({
    ready: false,
    name: '',
    lang: '',
    dark: false,
    dirty: false,
    openRow: '',
    accent: DEFAULT_ACCENT,
    font: '',
    fontLabel: '',
    fonts: [],
    background: 'gradient',
    backgroundColor: '#888888',
    gradient: '',
    gradientLabel: '',
    custom1: '',
    custom2: '',
    rotation: '0',
    backgroundImage: '',
    backgroundImageLabel: '',
    backgroundUpload: '',
    headerStyle: 'banner',
    headerImage: 'none',
    headerImageLabel: '',
    headerUpload: '',
    layout: 'list',
    animation: 'none',
    logo: '',
    logoWall: [],
});

const byId = (id) => document.getElementById(id);

function value(id) {
    const el = byId(id);

    return el ? String(el.value || '') : '';
}

function radio(name) {
    const el = document.querySelector('input[type="radio"][name="' + name + '"]:checked');

    return el ? el.value : '';
}

function chosen(id) {
    const el = byId(id);
    const option = el && el.selectedOptions ? el.selectedOptions[0] : null;

    return option ? option.textContent.trim() : '';
}

function source(el) {
    const src = el ? el.getAttribute('src') : '';

    return src && src !== '#' ? src : '';
}

// A picture that was just chosen and is waiting for Save: the page shows the block that holds
// its Remove, and puts the picture in the <img> beside it.
function pending(clearId, imageId) {
    const clear = byId(clearId);

    return clear && clear.style.display !== 'none' ? source(byId(imageId)) : '';
}

export function config(name) {
    const section = byId('section-style');

    return section ? (section.dataset[name] || '') : '';
}

export function headerThumb(name) {
    return config('headerThumbs') + '/' + name + '.jpg';
}

export function backgroundThumb(name) {
    return config('backgroundThumbs') + '/' + name + '.jpg';
}

export function isBuiltInHeader(name) {
    return !! name && HEADER_KEYWORDS.indexOf(name) === -1;
}

// The font list is the <select>'s own options: the page narrows them to the faces that have the
// schedule's letters (Hebrew, Arabic, Cyrillic) whenever the language changes.
export function readFonts() {
    const select = byId('font_family');
    state.fonts = select ? Array.prototype.map.call(select.options, (option) => ({
        value: option.value,
        label: option.textContent.trim(),
    })) : [];
}

export function read() {
    state.name = value('name').trim();
    state.lang = value('language_code');
    state.dark = document.documentElement.classList.contains('dark');
    state.accent = normalizeHex(value('accent_color')) || DEFAULT_ACCENT;
    state.font = value('font_family');
    state.fontLabel = chosen('font_family');
    state.background = radio('background') || 'gradient';
    state.backgroundColor = normalizeHex(value('background_color')) || '#888888';
    state.gradient = value('background_colors');
    state.gradientLabel = chosen('background_colors');
    state.custom1 = normalizeHex(value('custom_color1')) || '#000000';
    state.custom2 = normalizeHex(value('custom_color2')) || '#000000';
    state.rotation = value('background_rotation') || '0';
    state.backgroundImage = value('background_image');
    state.backgroundImageLabel = chosen('background_image');
    state.backgroundUpload = pending('background_image_preview_clear', 'background_image_preview')
        || source(document.querySelector('#background_image_existing img'));
    state.headerStyle = radio('header_style') || 'banner';
    state.headerImage = value('header_image');
    state.headerImageLabel = chosen('header_image');
    state.headerUpload = pending('header_image_url_preview_clear', 'header_image_url_preview')
        || source(document.querySelector('#delete_header_image_button img'));
    state.layout = radio('event_layout') || 'list';
    state.animation = radio('list_animation') || 'none';
    state.logo = pending('profile_image_preview_clear', 'profile_image_preview') || source(byId('profile_image_stored'));
    state.logoWall = Array.prototype.slice.call(document.querySelectorAll('#logo-wall-list img'), 0, 12)
        .map((img) => img.getAttribute('src'))
        .filter(Boolean);
    state.openRow = window.FormKit ? window.FormKit.openTab('style') : '';
    state.dirty = !! (window.FormKit && window.FormKit.dirtySections().indexOf('section-style') !== -1);

    loadFont(state.font);
}

// Set a field as a person would have: the value, then `input` and `change`. Nothing happens (and
// nothing is said) when the field already holds the value, or cannot hold it.
export function setField(id, next) {
    const el = byId(id);
    if (! el || el.value === next) {
        return false;
    }
    el.value = next;
    if (el.value !== next) {
        return false;
    }
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));

    return true;
}

// The copy of a font this install serves (php artisan fonts:download), never Google Fonts. One
// stylesheet a face, asked for the first time the face is wanted.
const loadedFonts = {};

export function loadFont(name) {
    const key = String(name || '').trim().replace(/ /g, '_');
    if (! key || loadedFonts[key]) {
        return;
    }
    loadedFonts[key] = true;
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = config('fontBase') + '/' + encodeURIComponent(key) + '/font.css';
    document.head.appendChild(link);
}

export function fontStack(label) {
    return "'" + String(label || '').replace(/['"\\]/g, '') + "', sans-serif";
}

// What the background is, as CSS: for the preview's ground and for the chip on the row.
export function backgroundCss() {
    if (state.background === 'gradient') {
        const stops = state.gradient ? gradientStops(state.gradient).join(', ') : state.custom1 + ', ' + state.custom2;

        return 'linear-gradient(' + (parseInt(state.rotation, 10) || 0) + 'deg, ' + stops + ')';
    }
    if (state.background === 'image') {
        const picture = state.backgroundImage ? backgroundThumb(state.backgroundImage) : state.backgroundUpload;

        return picture ? cssUrl(picture) + ' center / cover no-repeat' : '#8a8f98';
    }

    return state.backgroundColor;
}

// The header's picture, where it has one: a built-in, or the owner's own.
export function headerPicture() {
    if (state.headerStyle !== 'banner') {
        return '';
    }
    if (isBuiltInHeader(state.headerImage)) {
        return headerThumb(state.headerImage);
    }

    return state.headerImage === '' ? state.headerUpload : '';
}

export function cssUrl(address) {
    return 'url("' + String(address).replace(/["\\\n\r]/g, '') + '")';
}

// A stored gradient is a list of hex colours; a few of the built-in ones were saved without "#".
export function gradientStops(stored) {
    return String(stored).split(',')
        .map((stop) => normalizeHex(stop.trim()))
        .filter(Boolean);
}

// ---------------------------------------------------------------------------------------------
// Colour. A port of App\Utils\ColorUtils and of GuestTheme::fromAccent(), so the preview's main
// button and its icons take the colours the public page takes, not the accent as typed: a grey
// accent is not used as a button, and one the dark panel would swallow is lifted. The browser
// test compares the two for a handful of accents; change one and change the other.
// ---------------------------------------------------------------------------------------------

const INK = '#16181d';
const PAPER = '#f3f4f6';
const LIGHT_PANELS = ['#ffffff', '#f9fafb', '#f3f4f6'];
const DARK_PANELS = ['#1e1e1e', '#252526', '#2d2d30'];
const GREY_BELOW = 0.08;

export function normalizeHex(hex) {
    const match = /^#?([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i.exec(String(hex === null || hex === undefined ? '' : hex).trim());
    if (! match) {
        return null;
    }
    let digits = match[1].toLowerCase();
    if (digits.length <= 4) {
        digits = digits[0] + digits[0] + digits[1] + digits[1] + digits[2] + digits[2];
    }

    return '#' + digits.slice(0, 6);
}

export function toRgb(hex) {
    return [parseInt(hex.slice(1, 3), 16), parseInt(hex.slice(3, 5), 16), parseInt(hex.slice(5, 7), 16)];
}

function fromRgb(rgb) {
    return '#' + rgb.map((channel) => {
        const whole = Math.round(Math.max(0, Math.min(255, channel)));

        return (whole < 16 ? '0' : '') + whole.toString(16);
    }).join('');
}

function mix(colour, base, weight) {
    const a = toRgb(colour);
    const b = toRgb(base);

    return fromRgb([0, 1, 2].map((i) => a[i] * weight + b[i] * (1 - weight)));
}

export function toHsl(hex) {
    const [r, g, b] = toRgb(hex).map((channel) => channel / 255);
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    const l = (max + min) / 2;
    if (max === min) {
        return [0, 0, l];
    }
    const d = max - min;
    const s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
    let h;
    if (max === r) {
        h = (g - b) / d + (g < b ? 6 : 0);
    } else if (max === g) {
        h = (b - r) / d + 2;
    } else {
        h = (r - g) / d + 4;
    }

    return [h / 6, s, l];
}

function fromHsl(h, s, l) {
    if (s === 0) {
        return fromRgb([l * 255, l * 255, l * 255]);
    }
    const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
    const p = 2 * l - q;
    const channel = (t) => {
        t = t < 0 ? t + 1 : (t > 1 ? t - 1 : t);
        if (t < 1 / 6) {
            return p + (q - p) * 6 * t;
        }
        if (t < 1 / 2) {
            return q;
        }
        if (t < 2 / 3) {
            return p + (q - p) * (2 / 3 - t) * 6;
        }

        return p;
    };

    return fromRgb([channel(h + 1 / 3) * 255, channel(h) * 255, channel(h - 1 / 3) * 255]);
}

function luminance(hex) {
    const [r, g, b] = toRgb(hex).map((channel) => {
        const c = channel / 255;

        return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
    });

    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

export function contrastRatio(a, b) {
    const la = luminance(a);
    const lb = luminance(b);

    return (Math.max(la, lb) + 0.05) / (Math.min(la, lb) + 0.05);
}

// Black or white on a fill, by the rule the pages have always used.
export function contrastColor(hex) {
    return luminance(hex) > 0.25 ? '#000000' : '#ffffff';
}

function shiftUntil(colour, backgrounds, darker, ratio) {
    const [h, s, l] = toHsl(colour);
    for (let step = 0; step <= 100; step++) {
        const candidate = fromHsl(h, s, Math.max(0, Math.min(1, l + (darker ? -0.01 : 0.01) * step)));
        if (backgrounds.every((background) => contrastRatio(candidate, background) >= ratio)) {
            return candidate;
        }
    }

    return null;
}

export function theme(accent) {
    const base = normalizeHex(accent) || DEFAULT_ACCENT;
    const [hue, saturation, lightness] = toHsl(base);
    const channels = toRgb(base);
    const neutral = (Math.max(...channels) - Math.min(...channels)) / 255 < GREY_BELOW;

    let fill = base;
    let fillDark = base;
    if (neutral) {
        // A grey reads as a button only when it is nearly the opposite of the panel.
        if (contrastRatio(base, LIGHT_PANELS[0]) < 7) {
            fill = INK;
        }
        if (contrastRatio(base, DARK_PANELS[0]) < 7) {
            fillDark = PAPER;
        }
    } else if (contrastRatio(base, DARK_PANELS[0]) < 1.6) {
        // A colour the dark panel swallows: the same hue, lifted until it stands out.
        fillDark = shiftUntil(base, DARK_PANELS, false, 3) || PAPER;
    }

    const text = neutral ? fromHsl(0, 0, lightness) : base;
    const tint = mix(base, LIGHT_PANELS[0], 0.12);
    const tintDark = mix(base, DARK_PANELS[0], 0.24);

    return {
        fill,
        onFill: contrastColor(fill),
        fillDark,
        onFillDark: contrastColor(fillDark),
        readable: shiftUntil(text, LIGHT_PANELS.concat([tint]), true, 4.5) || INK,
        readableDark: shiftUntil(text, DARK_PANELS.concat([tintDark]), false, 4.5) || PAPER,
        glow: neutral ? '#94a3b8' : fromHsl(hue, saturation, Math.max(lightness, 0.55)),
    };
}

// The room a row or a list has: under the top bar (and, on a narrow screen, under the pinned
// preview), above the save bar.
export function room() {
    const side = document.querySelector('#section-style .st-side');
    const bar = byId('form-save-bar');
    const wide = window.matchMedia('(min-width: 1280px)').matches;

    return {
        top: wide || ! side ? 80 : Math.round(side.getBoundingClientRect().height) + 72,
        floor: window.innerHeight - (bar ? bar.offsetHeight : 0) - 16,
    };
}

// Bring something into that room, moving the page no further than it takes.
export function bringIntoRoom(first, last) {
    if (! first || ! last) {
        return;
    }
    const space = room();
    const over = last.getBoundingClientRect().bottom - space.floor;
    if (over <= 0) {
        return;
    }
    const by = Math.min(over, first.getBoundingClientRect().top - space.top);
    if (by > 0) {
        const still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        window.scrollTo({ top: window.scrollY + by, behavior: still ? 'auto' : 'smooth' });
    }
}
