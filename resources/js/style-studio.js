// The Style tab of the schedule form (resources/views/role/edit.blade.php, #section-style): its
// preview and its pickers, each a small island on an EMPTY element, each a view of a field that
// stays in the page's markup and carries the value. See resources/js/style/state.js.

import { createApp } from 'vue';
import StylePreview from './components/StylePreview.vue';
import StyleFontPicker from './components/StyleFontPicker.vue';
import StyleGradientPicker from './components/StyleGradientPicker.vue';
import StylePictureWall from './components/StylePictureWall.vue';
import StyleColorField from './components/StyleColorField.vue';
import { state, read, readFonts, theme, backgroundCss, headerPicture, cssUrl, bringIntoRoom } from './style/state.js';

const ISLANDS = [
    ['.vue-style-preview', StylePreview],
    ['.vue-style-font', StyleFontPicker],
    ['.vue-style-gradient', StyleGradientPicker],
    ['.vue-style-wall', StylePictureWall],
    ['.vue-style-color', StyleColorField],
];

function start() {
    const section = document.getElementById('section-style');
    if (! section || window.StyleStudio) {
        return;
    }

    // Read first, mount second: every island starts from what the page shows. By now the page's
    // own script has put back what a refused save had typed.
    readFonts();
    read();

    ISLANDS.forEach(([selector, component]) => {
        section.querySelectorAll(selector).forEach((el) => {
            createApp(component, JSON.parse(el.dataset.props || '{}')).mount(el);
        });
    });

    // A field of the tab changed, by a person or by one of the pickers.
    section.addEventListener('input', read);
    section.addEventListener('change', read);
    // The schedule's name and its language live on the Details tab; the preview draws both.
    ['name', 'language_code'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', read);
            el.addEventListener('change', read);
        }
    });
    // The page says so itself where a field changed without an event (a file chosen or cleared,
    // a picture deleted, the logo wall dragged, the AI generator's results). The rows' lines are
    // drawn again after the read: a chosen file is read after its change event has gone by, so
    // the chip in the row was painted before the picture was there.
    document.addEventListener('style:sync', () => {
        read();
        if (window.FormKit) {
            window.FormKit.refresh();
        }
    });
    document.addEventListener('formkit:dirty', read);
    // The admin's own theme, which the preview starts in.
    new MutationObserver(read).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    // A change of language rebuilds the font list to the faces that have its letters.
    const fonts = document.getElementById('font_family');
    if (fonts) {
        new MutationObserver(() => {
            readFonts();
            read();
        }).observe(fonts, { childList: true });
    }

    // A row that opens is brought into the room between the top bar and the save bar, the page
    // moving no further than it takes: a wall of pictures used to open under the bar. Not while
    // the page is still setting itself up (it holds the scroll at the top until it has).
    document.addEventListener('formkit:row', (event) => {
        read();
        const detail = event.detail || {};
        if (detail.group !== 'style' || ! detail.open || ! detail.pane || ! window.FormKit || ! window.FormKit.isArmed()) {
            return;
        }
        const row = section.querySelector('button[aria-controls="' + detail.pane.id + '"]');
        window.requestAnimationFrame(() => bringIntoRoom(row, detail.pane));
    });

    // Whether the preview is holding its place: a mark at the foot of the heading has gone under
    // the top bar. Known, not guessed from how far the page has scrolled.
    const mark = section.querySelector('.st-mark');
    const side = section.querySelector('.st-side');
    if (mark && side && 'IntersectionObserver' in window) {
        new IntersectionObserver((entries) => {
            const entry = entries[0];
            side.classList.toggle('is-stuck', ! entry.isIntersecting && entry.boundingClientRect.top < 200);
        }, { rootMargin: '-90px 0px 0px 0px' }).observe(mark);
    }

    // What the page's own script asks of the tab: the chips on two of its rows, and the colours
    // a browser test compares with the public page's.
    window.StyleStudio = {
        state,
        read,
        theme,
        backgroundChip: () => backgroundCss(),
        headerChip: () => {
            const picture = headerPicture();

            return picture ? cssUrl(picture) + ' center / cover no-repeat' : '';
        },
    };
    state.ready = true;
    document.dispatchEvent(new CustomEvent('style:ready'));
}

// After the page's own DOMContentLoaded handlers, which seed the fields. A module runs before
// that event; if this one arrived late, the load event (or the finished document) starts it.
if (document.readyState === 'complete') {
    start();
} else {
    document.addEventListener('DOMContentLoaded', start);
    window.addEventListener('load', start);
}
