// Shared helpers for the EasyMDE (CodeMirror 5) markdown editors used across the
// app: the full and tiny editors in app.js and the per-block editors in
// NewsletterBuilder.vue. Centralizes four concerns so every editor behaves the same:
//
//   1. RTL/LTR direction handling (smart auto-detect, without flipping mid-typing)
//   2. A manual direction override: a toolbar button plus the Ctrl+Shift shortcut
//   3. Paste sanitizing, so invisible characters do not come along for the ride
//   4. A "blank until focus" fix: CodeMirror cannot measure an element that is
//      display:none at init time (hidden sections, tabs, v-show, etc.), so it
//      renders empty until a focus event forces a repaint. We refresh() it the
//      moment it first becomes visible.

// Strong directional character ranges (Hebrew + Arabic blocks and their presentation
// forms vs. Latin), used to work out which way the content reads.
const RTL_CHAR = /[֐-׿יִ-ﭏ؀-ۿݐ-ݿࢠ-ࣿﭐ-﷿ﹰ-﻿]/;
const LTR_CHAR = /[A-Za-zÀ-ɏḀ-ỿ]/;

// Majority of the strong directional characters, with the first strong character as the
// tiebreak. Majority beats plain first-strong (dir="auto") detection for real content:
// "DJ Mike presents: <hebrew>" is Hebrew text that first-strong would call LTR.
function detectDir(text) {
    if (!text) return null;
    let rtl = 0;
    let ltr = 0;
    let first = null;
    for (const ch of text) {
        if (RTL_CHAR.test(ch)) {
            rtl++;
            if (!first) first = 'rtl';
        } else if (LTR_CHAR.test(ch)) {
            ltr++;
            if (!first) first = 'ltr';
        }
    }
    if (rtl === ltr) return first;
    return rtl > ltr ? 'rtl' : 'ltr';
}

function pageDir() {
    return (document.documentElement.getAttribute('dir') || '').toLowerCase() === 'rtl' ? 'rtl' : 'ltr';
}

function applyDir(cm, dir) {
    if (cm.getOption('direction') !== dir) {
        cm.setOption('direction', dir);
    }
    // CodeMirror defaults rtlMoveVisually to true everywhere except Windows, so the
    // arrow keys move the caret *visually* while Backspace/Delete stay *logical*. The
    // caret then sits in one place and the deleted character is somewhere else, which
    // is what makes mixed Hebrew/Latin text feel broken. Logical movement everywhere
    // keeps the two in agreement.
    if (cm.getOption('rtlMoveVisually') !== false) {
        cm.setOption('rtlMoveVisually', false);
    }
    const container = cm.getWrapperElement().closest('.EasyMDEContainer');
    if (!container) return;
    // Only the editing area + preview go RTL; the toolbar keeps its inherited
    // (page) direction, so it never flips based on content.
    container.classList.toggle('editor-rtl', dir === 'rtl');
    container.querySelectorAll('.editor-preview, .editor-preview-side').forEach(node => {
        node.setAttribute('dir', dir);
    });
}

// Resolve an editor's text direction. Priority:
//   1. data-dir-override, set by the toolbar button / the Ctrl+Shift shortcut
//   2. an explicit dir="rtl"|"ltr" attribute on the textarea
//   3. the direction of the content itself
//   4. data-content-dir, stamped server-side from the schedule's content language,
//      then the admin UI direction (the page <html dir>)
//
// Content outranks data-content-dir, which is only the direction to start typing in
// while the field is still empty. Anything else pins a Hebrew description written in an
// English-language schedule to an LTR base direction, which is the worst case for bidi
// editing.
//
// Anti-flip lock: once a direction is detected for non-empty content we stop
// re-detecting on every keystroke, so it cannot flip while typing. Pasting or replacing
// the content clears the lock (see unlockEditorDirection) because that is exactly when
// the direction needs re-deciding.
//
// Takes the value separately so it can also run before the EasyMDE instance exists,
// to seed the `direction` constructor option.
export function resolveEditorDirection(element, value) {
    const override = (element.dataset.dirOverride || '').toLowerCase();
    if (override === 'rtl' || override === 'ltr') return override;

    const explicit = (element.getAttribute('dir') || '').toLowerCase();
    if (explicit === 'rtl' || explicit === 'ltr') return explicit;

    if (value && value.trim()) {
        if (element._editorDirLocked && element._editorDir) return element._editorDir;

        const detected = detectDir(value);
        if (detected) {
            element._editorDirLocked = true;
            element._editorDir = detected;
            return detected;
        }
    }

    element._editorDirLocked = false;
    element._editorDir = null;

    const contentDir = (element.dataset.contentDir || '').toLowerCase();
    if (contentDir === 'rtl' || contentDir === 'ltr') return contentDir;

    return pageDir();
}

export function applyEditorDirection(easyMDE, element) {
    if (!easyMDE || !element || !easyMDE.codemirror) return;

    const dir = resolveEditorDirection(element, easyMDE.value());
    applyDir(easyMDE.codemirror, dir);
    syncDirectionButton(easyMDE, dir);
}

// Origins that replace text wholesale rather than typing into it. The anti-flip lock
// must not survive these: pasting a Hebrew article into an editor that was last used for
// English is precisely when the direction has to be re-detected.
const DIR_RESET_ORIGINS = ['paste', 'cut', 'setValue', 'undo', 'redo'];

export function unlockEditorDirection(element, origin) {
    if (!element) return;
    if (origin && !DIR_RESET_ORIGINS.includes(origin)) return;
    element._editorDirLocked = false;
    element._editorDir = null;
}

// Pin the editor to a direction, overriding detection for the rest of the session.
export function setEditorDirection(easyMDE, element, dir) {
    if (!easyMDE || !element || (dir !== 'rtl' && dir !== 'ltr')) return;
    element.dataset.dirOverride = dir;
    applyEditorDirection(easyMDE, element);
    easyMDE.codemirror.focus();
}

export function toggleEditorDirection(easyMDE, element) {
    if (!easyMDE || !element || !easyMDE.codemirror) return;
    const current = easyMDE.codemirror.getOption('direction') === 'rtl' ? 'rtl' : 'ltr';
    setEditorDirection(easyMDE, element, current === 'rtl' ? 'ltr' : 'rtl');
}

// The toolbar button shows the direction the editor is currently in. EasyMDE keys
// toolbarElements by the button's `name`.
function syncDirectionButton(easyMDE, dir) {
    const button = easyMDE.toolbarElements && easyMDE.toolbarElements.direction;
    if (!button) return;
    button.innerText = dir === 'rtl' ? 'RTL' : 'LTR';
}

function shiftSide(event) {
    if (event.location === 1) return 'rtl'; // DOM_KEY_LOCATION_LEFT
    if (event.location === 2) return 'ltr'; // DOM_KEY_LOCATION_RIGHT
    if (event.code === 'ShiftLeft') return 'rtl';
    if (event.code === 'ShiftRight') return 'ltr';
    return null;
}

// Ctrl/Cmd + Left Shift -> RTL, Ctrl/Cmd + Right Shift -> LTR, the convention Word and
// Windows use. CodeMirror types into an off-screen textarea and paints the text into
// <pre> elements, so the browser's own Ctrl+Shift bidi toggle only flips the invisible
// textarea and has no visible effect - we have to implement it ourselves.
//
// The flip happens on the Shift *keyup*, and only when no other key was pressed while
// Shift was held. Acting on the keydown would hijack Ctrl+Shift+Z (redo),
// Ctrl+Shift+Arrow (selection) and every other Ctrl+Shift chord.
export function attachDirectionShortcut(easyMDE, element) {
    if (!easyMDE || !element || !easyMDE.codemirror) return;
    const wrap = easyMDE.codemirror.getWrapperElement();
    let pending = null;

    wrap.addEventListener('keydown', event => {
        if (event.key !== 'Shift') {
            pending = null;
            return;
        }
        pending = (event.ctrlKey || event.metaKey) ? shiftSide(event) : null;
    });

    wrap.addEventListener('keyup', event => {
        if (event.key !== 'Shift') return;
        const dir = pending;
        pending = null;
        if (dir) setEditorDirection(easyMDE, element, dir);
    });

    wrap.addEventListener('blur', () => { pending = null; }, true);
}

// Characters that ride along with text pasted from Word, WhatsApp or a web page and then
// make the editor feel broken. They are invisible, so each one costs its own Backspace
// press with nothing appearing to happen, and the bidi controls among them fight the
// direction handling above. Listed as code points because the characters themselves are
// unreadable in source.
const INVISIBLE_RANGES = [
    [0x0000, 0x0008], // C0 controls, keeping tab (0x09) and newline (0x0a)
    [0x000c, 0x001f], // the rest of them, including a stray carriage return
    [0x007f, 0x007f], // delete
    [0x061c, 0x061c], // arabic letter mark
    [0x200b, 0x200f], // zero width space/non-joiner/joiner, LRM, RLM
    [0x202a, 0x202e], // bidi embeddings and overrides
    [0x2066, 0x2069], // bidi isolates
    [0xfeff, 0xfeff], // byte order mark
];

// What Word and Google Docs paste for a soft line break. These become real newlines
// rather than being dropped.
const LINE_SEPARATORS = [0x000b, 0x2028, 0x2029];

// Look like a space, are not one, and break markdown that expects one.
const ODD_SPACES = [0x00a0, 0x202f];

export function sanitizePastedLine(line) {
    let out = '';

    for (const ch of String(line)) {
        const code = ch.codePointAt(0);

        if (LINE_SEPARATORS.includes(code)) {
            out += '\n';
        } else if (ODD_SPACES.includes(code)) {
            out += ' ';
        } else if (!INVISIBLE_RANGES.some(([low, high]) => code >= low && code <= high)) {
            out += ch;
        }
    }

    return out;
}

export function attachPasteSanitizer(easyMDE) {
    if (!easyMDE || !easyMDE.codemirror) return;

    easyMDE.codemirror.on('beforeChange', (cm, change) => {
        if (change.origin !== 'paste' || typeof change.update !== 'function') return;

        // CodeMirror hands us one array entry per pasted line; sanitizing can introduce
        // a newline, so re-split rather than assuming the line count is unchanged.
        const cleaned = change.text.flatMap(line => sanitizePastedLine(line).split('\n'));
        const changed = cleaned.length !== change.text.length
            || cleaned.some((line, index) => line !== change.text[index]);

        if (changed) {
            change.update(null, null, cleaned);
        }
    });
}

// Refresh CodeMirror the moment the editor first becomes visible, fixing the
// "blank until I click into it" bug for editors initialized while display:none.
export function attachEditorObserver(easyMDE) {
    if (!easyMDE || !easyMDE.codemirror) return;
    const cm = easyMDE.codemirror;
    const wrap = cm.getWrapperElement();

    const refresh = () => requestAnimationFrame(() => {
        if (wrap.isConnected) cm.refresh();
    });

    if (typeof IntersectionObserver === 'function') {
        const obs = new IntersectionObserver(entries => {
            for (const entry of entries) {
                if (!entry.target.isConnected) {
                    obs.disconnect(); // editor was torn down (toTextArea)
                    return;
                }
                if (entry.isIntersecting) refresh();
            }
        }, { threshold: 0 });
        obs.observe(wrap);
        easyMDE._stopEditorObserver = () => obs.disconnect();
    }

    // Editors already visible at init still need one refresh.
    refresh();
}
