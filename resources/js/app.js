import './bootstrap';

import Alpine from 'alpinejs';
import { registerMediaLibraryComponents, initMediaPickers } from './media-picker';

window.Alpine = Alpine;
registerMediaLibraryComponents(Alpine);
Alpine.start();

const themeStorageKey = 'theme';
const themeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
const forcedTheme = document.documentElement.dataset.forceTheme;

const isValidTheme = (value) => ['light', 'dark', 'system'].includes(value);

const getStoredThemePreference = () => {
    if (isValidTheme(forcedTheme)) {
        return forcedTheme;
    }

    const stored = window.localStorage.getItem(themeStorageKey);

    if (isValidTheme(stored)) {
        return stored;
    }

    return 'system';
};

let lastResolvedTheme = null;
let lastThemePreference = null;

const resolveTheme = (preference) => {
    if (isValidTheme(forcedTheme)) {
        return forcedTheme;
    }

    if (preference === 'system') {
        return themeMediaQuery.matches ? 'dark' : 'light';
    }

    return preference;
};

const dispatchThemeChange = (preference, resolved) => {
    document.dispatchEvent(new CustomEvent('theme:change', {
        detail: {
            preference,
            resolved,
        },
    }));
};

const applyThemePreference = (preference, { notify = true } = {}) => {
    const resolved = resolveTheme(preference);
    const root = document.documentElement;

    root.classList.toggle('dark', resolved === 'dark');
    root.dataset.themePreference = isValidTheme(forcedTheme) ? forcedTheme : preference;
    root.dataset.themeResolved = resolved;
    root.style.colorScheme = resolved;

    if (notify && (preference !== lastThemePreference || resolved !== lastResolvedTheme)) {
        dispatchThemeChange(preference, resolved);
    }

    lastThemePreference = preference;
    lastResolvedTheme = resolved;
};

const setThemePreference = (preference) => {
    if (isValidTheme(forcedTheme)) {
        if (isValidTheme(preference)) {
            window.localStorage.setItem(themeStorageKey, preference);
        }

        applyThemePreference(forcedTheme);
        return;
    }

    if (!isValidTheme(preference)) {
        return;
    }

    window.localStorage.setItem(themeStorageKey, preference);
    applyThemePreference(preference);
};

const refreshThemePreference = () => {
    applyThemePreference(getStoredThemePreference());
};

const getThemeSelects = () => Array.from(document.querySelectorAll('[data-theme-select]'));

const syncThemeSelects = (preference = getStoredThemePreference()) => {
    getThemeSelects().forEach((select) => {
        if (select instanceof HTMLSelectElement) {
            select.value = preference;
        }
    });
};

const bindThemeSelects = () => {
    getThemeSelects().forEach((select) => {
        if (!(select instanceof HTMLSelectElement)) {
            return;
        }

        select.addEventListener('change', (event) => {
            const target = event.target;

            if (target instanceof HTMLSelectElement) {
                setThemePreference(target.value);
            }
        });
    });
};

const removeDuplicateFloatingSelectors = () => {
    const selects = getThemeSelects();
    const floatingSelects = selects.filter((select) => select.hasAttribute('data-theme-floating'));

    if (floatingSelects.length && selects.length > floatingSelects.length) {
        floatingSelects.forEach((select) => {
            const wrapper = select.closest('[data-theme-floating-wrapper]');

            if (wrapper) {
                wrapper.remove();
            } else {
                select.remove();
            }
        });
    }
};

if (!window.theme) {
    window.theme = {};
}

window.theme.get = getStoredThemePreference;
window.theme.set = setThemePreference;
window.theme.apply = applyThemePreference;
window.theme.refresh = refreshThemePreference;

const handleSystemThemeChange = () => {
    if (getStoredThemePreference() === 'system') {
        applyThemePreference('system');
    }
};

if (typeof themeMediaQuery.addEventListener === 'function') {
    themeMediaQuery.addEventListener('change', handleSystemThemeChange);
} else if (typeof themeMediaQuery.addListener === 'function') {
    themeMediaQuery.addListener(handleSystemThemeChange);
}

window.addEventListener('storage', (event) => {
    if (event.key === themeStorageKey) {
        refreshThemePreference();
    }
});

document.addEventListener('theme:change', (event) => {
    const preference = event.detail?.preference;

    if (isValidTheme(preference)) {
        syncThemeSelects(preference);
    }
});

const bootstrapPickers = () => initMediaPickers();

const initUserPasswordSections = () => {
    const sections = document.querySelectorAll('[data-user-password-section]');

    if (!sections.length) {
        return;
    }

    sections.forEach((section) => {
        if (!(section instanceof HTMLElement)) {
            return;
        }

        if (section.dataset.userPasswordOptional !== 'true') {
            return;
        }

        const fieldContainer = section.querySelector('[data-user-password-fields]');
        const toggles = section.querySelectorAll('[data-user-password-toggle]');
        const inputs = section.querySelectorAll('[data-user-password-input]');

        if (!fieldContainer || !toggles.length || !inputs.length) {
            return;
        }

        const applyMode = (mode) => {
            const enableFields = mode === 'set';

            section.dataset.userPasswordActiveMode = mode;

            if (fieldContainer instanceof HTMLElement) {
                fieldContainer.hidden = !enableFields;
                fieldContainer.setAttribute('aria-hidden', String(!enableFields));
            }

            inputs.forEach((input) => {
                if (!(input instanceof HTMLInputElement)) {
                    return;
                }

                input.disabled = !enableFields;

                if (!enableFields) {
                    input.value = '';
                }

                const shouldRequire = input.dataset.passwordRequired === 'true';

                if (enableFields && shouldRequire) {
                    input.setAttribute('required', 'required');
                } else if (!enableFields) {
                    input.removeAttribute('required');
                }
            });
        };

        const handleToggle = (toggle) => {
            const mode = toggle.dataset.userPasswordToggle || toggle.value;
            applyMode(mode === 'set' ? 'set' : 'keep');
        };

        toggles.forEach((toggle) => {
            if (!(toggle instanceof HTMLInputElement)) {
                return;
            }

            toggle.addEventListener('change', () => {
                if (toggle.checked) {
                    handleToggle(toggle);
                }
            });
        });

        const defaultMode = section.dataset.userPasswordDefaultMode === 'set' ? 'set' : 'keep';
        const defaultToggle = Array.from(toggles).find((toggle) => {
            if (!(toggle instanceof HTMLInputElement)) {
                return false;
            }

            const mode = toggle.dataset.userPasswordToggle || toggle.value;
            return (mode === 'set' ? 'set' : 'keep') === defaultMode;
        });

        if (defaultToggle instanceof HTMLInputElement) {
            defaultToggle.checked = true;
        }

        applyMode(defaultMode);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrapPickers);
} else {
    bootstrapPickers();
}

import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';

// Expose flatpickr globally for legacy inline scripts that expect a global
// reference (e.g. event create/edit pages initialising date pickers).
window.flatpickr = flatpickr;

//import Toastify from 'toastify-js';
//import 'toastify-js/src/toastify.css';

import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';
document.addEventListener('DOMContentLoaded', () => {
    applyThemePreference(getStoredThemePreference(), { notify: false });
    syncThemeSelects();
    bindThemeSelects();
    removeDuplicateFloatingSelectors();
    initUserPasswordSections();

    document.querySelectorAll('.html-editor').forEach(element => {
        const easyMDE = new EasyMDE({
            element: element,
            toolbar: [
                {
                    name: "bold",
                    action: EasyMDE.toggleBold,
                    className: "editor-button-text",
                    title: "Bold",
                    text: "B"
                },
                {
                    name: "italic",
                    action: EasyMDE.toggleItalic,
                    className: "editor-button-text",
                    title: "Italic",
                    text: "I"
                },
                {
                    name: "heading",
                    action: EasyMDE.toggleHeadingSmaller,
                    className: "editor-button-text",
                    title: "Heading",
                    text: "H"
                },
                "|",
                {
                    name: "quote",
                    action: EasyMDE.toggleBlockquote,
                    className: "editor-button-text",
                    title: "Quote",
                    text: "\""
                },
                {
                    name: "unordered-list",
                    action: EasyMDE.toggleUnorderedList,
                    className: "editor-button-text",
                    title: "Unordered List",
                    text: "UL"
                },
                {
                    name: "ordered-list",
                    action: EasyMDE.toggleOrderedList,
                    className: "editor-button-text",
                    title: "Ordered List",
                    text: "OL"
                },
                "|",
                {
                    name: "preview",
                    action: EasyMDE.togglePreview,
                    className: "editor-button-text no-disable",
                    title: "Toggle Preview",
                    text: "👁"
                },
                {
                    name: "guide",
                    action: "https://www.markdownguide.org/basic-syntax/",
                    className: "editor-button-text",
                    title: "Markdown Guide",
                    text: "?"
                }
            ],
            minHeight: "200px",
            spellChecker: true,
            nativeSpellcheck: true,  
            status: false,
        });
    });
});

import './admin/image-library';
