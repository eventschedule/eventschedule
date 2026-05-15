const STORAGE_KEY = 'cookie_consent';
const GRANTED = 'granted';
const DENIED = 'denied';
const VALUES = new Set([GRANTED, DENIED]);

const banner = () => document.querySelector('[data-cookie-consent]');

const read = () => {
    try {
        const v = localStorage.getItem(STORAGE_KEY);
        return VALUES.has(v) ? v : null;
    } catch (_) {
        return null;
    }
};

const write = (value) => {
    try { localStorage.setItem(STORAGE_KEY, value); } catch (_) {}
};

const clear = () => {
    try { localStorage.removeItem(STORAGE_KEY); } catch (_) {}
};

const updateGtag = (value) => {
    if (typeof window.gtag !== 'function') return;
    window.gtag('consent', 'update', {
        ad_storage: value,
        ad_user_data: value,
        ad_personalization: value,
        analytics_storage: value,
    });
};

const show = () => {
    const el = banner();
    if (!el) return;
    el.removeAttribute('data-state');
    el.hidden = false;
};

const hide = () => {
    const el = banner();
    if (!el) return;
    el.setAttribute('data-state', 'closing');
    setTimeout(() => {
        el.hidden = true;
        el.removeAttribute('data-state');
    }, 150);
};

const init = () => {
    if (read() === null) show();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

document.addEventListener('click', (e) => {
    const action = e.target.closest('[data-cookie-consent-action]');
    if (action) {
        const value = action.getAttribute('data-cookie-consent-action');
        if (!VALUES.has(value)) return;
        write(value);
        updateGtag(value);
        hide();
        return;
    }

    // GDPR Article 7(3): withdrawing consent must be as easy as giving it.
    const reopen = e.target.closest('[data-cookie-consent-reopen]');
    if (reopen) {
        clear();
        updateGtag(DENIED);
        show();
    }
});
