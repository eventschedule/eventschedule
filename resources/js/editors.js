// The Markdown editors (EasyMDE): window.initHtmlEditors(), window.initTinyMDE() and the editors a
// page draws with .html-editor. About a third of a megabyte of script, so a guest page that has no
// editor is served resources/js/guest.js, which leaves this file out (layouts/app-guest decides).
import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';
import {
    applyEditorDirection,
    attachDirectionShortcut,
    attachEditorChrome,
    editorToolbar,
    attachEditorObserver,
    attachPasteSanitizer,
    resolveEditorDirection,
    unlockEditorDirection,
} from './editor-helpers';

function initEasyMDEOn(element) {
    if (element._easyMDE) return;

    const initialDir = resolveEditorDirection(element, element.value);
    const easyMDE = new EasyMDE({
        element: element,
        toolbar: editorToolbar(EasyMDE, element),
        minHeight: "200px",
        spellChecker: true,
        nativeSpellcheck: true,
        status: false,
        // Passing the direction here rather than only setting it afterwards avoids a
        // full CodeMirror redraw on init (setDirection invalidates every line's order).
        direction: initialDir,
    });

    element._easyMDE = easyMDE;

    applyEditorDirection(easyMDE, element);
    attachEditorObserver(easyMDE);
    attachDirectionShortcut(easyMDE, element);
    attachPasteSanitizer(easyMDE);
    attachEditorChrome(easyMDE, element);

    easyMDE.codemirror.on('change', function(cm, changeObj) {
        unlockEditorDirection(element, changeObj && changeObj.origin);
        applyEditorDirection(easyMDE, element);
        element.dispatchEvent(new Event('change', { bubbles: true }));
    });
}

window.initHtmlEditors = function() {
    document.querySelectorAll('.html-editor').forEach(initEasyMDEOn);
};

document.addEventListener('DOMContentLoaded', () => {
    // Skip textareas inside Vue's #app — Vue mounts after DOMContentLoaded and uses
    // the container's innerHTML as its template (Vue 3 CSR), which would capture and
    // then destroy any EasyMDE wrapper we'd create here. Vue pages call
    // window.initHtmlEditors() themselves once Vue has rendered.
    const appEl = document.getElementById('app');
    document.querySelectorAll('.html-editor').forEach(element => {
        if (appEl && appEl.contains(element)) return;
        initEasyMDEOn(element);
    });
});

// Make EasyMDE available globally for Vue components (tiny version for agenda descriptions)
window.initTinyMDE = function(element, onChange) {
    const initialDir = resolveEditorDirection(element, element.value);
    const easyMDE = new EasyMDE({
        element: element,
        toolbar: editorToolbar(EasyMDE, element, { compact: true }),
        minHeight: "80px",
        spellChecker: false,
        status: false,
        direction: initialDir,
    });

    element._easyMDE = easyMDE;

    applyEditorDirection(easyMDE, element);
    attachEditorObserver(easyMDE);
    attachDirectionShortcut(easyMDE, element);
    attachPasteSanitizer(easyMDE);
    attachEditorChrome(easyMDE, element);

    easyMDE.codemirror.on('change', function(cm, changeObj) {
        unlockEditorDirection(element, changeObj && changeObj.origin);
        applyEditorDirection(easyMDE, element);
        if (onChange) onChange();
    });

    return easyMDE;
};
