// Shared helpers for the EasyMDE (CodeMirror 5) markdown editors used across the
// app: the full and tiny editors in app.js and the per-block editors in
// NewsletterBuilder.vue. Centralizes two concerns so every editor behaves the same:
//
//   1. RTL/LTR direction handling (smart auto-detect, without flipping mid-typing)
//   2. A "blank until focus" fix: CodeMirror cannot measure an element that is
//      display:none at init time (hidden sections, tabs, v-show, etc.), so it
//      renders empty until a focus event forces a repaint. We refresh() it the
//      moment it first becomes visible.

// First strong directional character ranges (Hebrew + Arabic blocks and their
// presentation forms vs. Latin). Used for dir="auto"-style detection.
const RTL_CHAR = /[֐-׿יִ-ﭏ؀-ۿݐ-ݿࢠ-ࣿﭐ-﷿ﹰ-﻿]/;
const LTR_CHAR = /[A-Za-zÀ-ɏḀ-ỿ]/;

function firstStrongDir(text) {
    if (!text) return null;
    for (const ch of text) {
        if (RTL_CHAR.test(ch)) return 'rtl';
        if (LTR_CHAR.test(ch)) return 'ltr';
    }
    return null;
}

function pageDir() {
    return (document.documentElement.getAttribute('dir') || '').toLowerCase() === 'rtl' ? 'rtl' : 'ltr';
}

function applyDir(cm, dir) {
    if (cm.getOption('direction') !== dir) {
        cm.setOption('direction', dir);
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

// Resolve and apply an editor's text direction. Priority:
//   1. an explicit dir="rtl"|"ltr" attribute on the textarea
//   2. data-content-dir, stamped server-side from the schedule's content language
//   3. the first strong directional character of the content (dir="auto" style)
//   4. the admin UI direction (the page <html dir>)
//
// Anti-flip lock: once content-detected direction is set on a non-empty editor we
// stop re-detecting on every keystroke, so it does not flip while editing. Explicit
// and data-content-dir directions always win. Clearing the editor unlocks it.
export function applyEditorDirection(easyMDE, element) {
    if (!easyMDE || !element || !easyMDE.codemirror) return;
    const cm = easyMDE.codemirror;

    const explicit = (element.getAttribute('dir') || '').toLowerCase();
    if (explicit === 'rtl' || explicit === 'ltr') {
        element._editorDirLocked = false;
        applyDir(cm, explicit);
        return;
    }

    const contentDir = (element.dataset.contentDir || '').toLowerCase();
    if (contentDir === 'rtl' || contentDir === 'ltr') {
        element._editorDirLocked = false;
        applyDir(cm, contentDir);
        return;
    }

    const value = easyMDE.value();
    if (!value || !value.trim()) {
        // Empty: anchor to the page direction and allow future re-detection.
        element._editorDirLocked = false;
        applyDir(cm, pageDir());
        return;
    }

    if (element._editorDirLocked) return; // keep current direction, no re-detect

    applyDir(cm, firstStrongDir(value) || pageDir());
    element._editorDirLocked = true;
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
