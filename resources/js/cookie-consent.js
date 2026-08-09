const STORAGE_KEY = 'cookie_consent';
const GRANTED = 'granted';
const DENIED = 'denied';
const VALUES = new Set([GRANTED, DENIED]);

/** 180 days, in seconds. */
const COOKIE_MAX_AGE = 60 * 60 * 24 * 180;

const banner = () => document.querySelector('[data-cookie-consent]');

/**
 * Broadcast so every consent-gated third party on the page can react without a reload.
 * gtag was the only consumer until the Stay22 accommodation map; do not add a second
 * consumer by polling localStorage, and do not reach for the `storage` event - it does
 * not fire in the tab that wrote the value.
 */
export const CONSENT_EVENT = 'es:consent-change';

const notify = (value) => {
    document.dispatchEvent(new CustomEvent(CONSENT_EVENT, { detail: { value } }));
};

const read = () => {
    try {
        const v = localStorage.getItem(STORAGE_KEY);
        return VALUES.has(v) ? v : null;
    } catch (_) {
        return null;
    }
};

/**
 * Mirror the choice into a cookie so the SERVER can honour it too: CaptureUtmParameters
 * writes the 30-day attribution cookies only once this reads 'granted'. localStorage stays
 * the source of truth for the in-page consumers, so this is a mirror, not a replacement.
 * The cookie is exempt from Laravel's cookie encryption (bootstrap/app.php) because Laravel
 * cannot decrypt a cookie the browser wrote; the value is a public two-value enum.
 * Host-only, like the utm_* cookies it gates and like localStorage itself.
 */
const writeCookie = (value) => {
    const secure = location.protocol === 'https:' ? '; Secure' : '';
    const age = value === null ? 0 : COOKIE_MAX_AGE;
    document.cookie = `${STORAGE_KEY}=${value ?? ''}; path=/; max-age=${age}; SameSite=Lax${secure}`;
};

const write = (value) => {
    try { localStorage.setItem(STORAGE_KEY, value); } catch (_) {}
    writeCookie(value);
};

const clear = () => {
    try { localStorage.removeItem(STORAGE_KEY); } catch (_) {}
    writeCookie(null);
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

/** null (never answered) | 'granted' | 'denied' */
export const readConsent = read;

const init = () => {
    const stored = read();

    if (stored === null) {
        show();

        return;
    }

    // Re-assert the mirror: a visitor who answered before the cookie existed has the choice
    // in localStorage only, and would silently lose the attribution cookies they consented
    // to. This also rolls the 180-day window forward on every visit.
    writeCookie(stored);
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
        notify(value);
        hide();
        return;
    }

    // GDPR Article 7(3): withdrawing consent must be as easy as giving it.
    const reopen = e.target.closest('[data-cookie-consent-reopen]');
    if (reopen) {
        clear();
        updateGtag(DENIED);
        // null, not DENIED: the stored choice is genuinely cleared, and consumers must
        // fall back to their unconsented state rather than treat this as a fresh refusal.
        notify(null);
        show();
    }
});
