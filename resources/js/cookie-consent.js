import './consent-state';

// Defined by consent-state.js, which is also inlined into <head> so that inline scripts can read
// the choice before this module loads. Everything that READS consent goes through it.
const consent = window.esConsent;

const banner = () => document.querySelector('[data-cookie-consent]');

/**
 * Broadcast so every consent-gated item on the page can react without a reload. Do not add a
 * consumer that polls localStorage, and do not reach for the `storage` event - it does not fire in
 * the tab that wrote the value. detail.categories is what is granted NOW: an empty list is a
 * refusal or a withdrawal, and every consumer must fall back to its unconsented state.
 */
export const CONSENT_EVENT = 'es:consent-change';

/** Whether a category ('analytics' | 'marketing') is granted right now. */
export const hasConsent = (category) => consent.has(category);

const notify = () => {
    document.dispatchEvent(new CustomEvent(CONSENT_EVENT, { detail: { categories: consent.granted() } }));
};

/**
 * Mirror the choice into a cookie so the SERVER can honour it too (consent_granted() in
 * app/helpers.php): CaptureUtmParameters writes the 30-day attribution cookies only with marketing
 * consent, and checkout records it on the sale for the Meta Conversions API. localStorage stays the
 * source of truth for the page, so this is a mirror, not a replacement. The cookie is exempt from
 * Laravel's cookie encryption (bootstrap/app.php) because Laravel cannot decrypt a cookie the
 * browser wrote; the value is the granted categories joined with '.', or 'denied'. Not a comma:
 * RFC 6265 leaves commas out of a cookie value.
 *
 * It lives exactly as long as the choice it mirrors and is not rolled forward on later visits: a
 * choice lapses twelve months after it was made, and the banner then asks again.
 *
 * Written on config('session.domain') when the server supplies one, so it spans the install
 * exactly like the utm_* cookies it gates. Host-only was wrong on hosted: the attribution
 * cookies are set on '.<base>' by Laravel's CookieJar, so a choice made on the apex was
 * invisible on app.<base> - where the server then read "no consent" and tried to expire those
 * cookies on every single request. Empty on a custom domain and on a bare selfhost, which
 * keeps it host-only there.
 */
// From a meta tag rather than the banner: init() re-asserts the stored choice on every page
// load, including pages where the banner is not rendered (an admin session, or an install that
// turned consent_required() off after visitors had answered). Reading it off the banner meant
// those loads wrote a second, host-only cookie beside the domain-scoped one.
const cookieDomain = () => document.querySelector('meta[name="cookie-domain"]')?.content || '';

/** A version-1 choice ("granted"/"denied") has no timestamp; its mirror lasts until it is re-asked. */
const LEGACY_MIRROR_MS = 30 * 24 * 60 * 60 * 1000;

const writeCookie = (state) => {
    const secure = location.protocol === 'https:' ? '; Secure' : '';
    const domain = cookieDomain();
    const scope = domain ? `; domain=${domain}` : '';
    let value = '';
    let age = 0;

    if (state) {
        value = state.c.length ? state.c.join('.') : 'denied';
        const remaining = state.v >= consent.VERSION
            ? consent.MAX_AGE_MS - (Date.now() - state.t)
            : LEGACY_MIRROR_MS;
        age = Math.max(0, Math.floor(remaining / 1000));
    }

    document.cookie = `${consent.KEY}=${value}; path=/${scope}; max-age=${age}; SameSite=Lax${secure}`;
};

/**
 * Third-party cookies the consented scripts leave behind, by the category that allowed them.
 * Withdrawing a category deletes them, as GDPR Article 7(3) expects withdrawal to undo what
 * consent allowed. utm_* are absent on purpose: they are HttpOnly, so the server expires them on
 * the next request once the mirrored cookie stops saying "marketing".
 */
const TRACKING_COOKIES = {
    analytics: [/^_ga$/, /^_ga_/, /^_gid$/],
    marketing: [/^_gcl_/, /^_fbp$/, /^_fbc$/, /^es_attribution$/],
};

const TRACKING_SESSION_KEYS = {
    analytics: ['es_hero', 'es_hero_clicked'],
    marketing: [],
};

/**
 * Expired on the host and on every parent domain: gtag's cookie_domain 'auto' and Meta's pixel
 * write on the registrable domain (".venue.com" for events.venue.com), which a host-only expiry
 * would miss. A browser ignores the attempt on a public suffix such as ".com".
 */
const expireEverywhere = (name) => {
    const labels = location.hostname.split('.');
    const domains = [''];
    for (let i = 0; i < labels.length - 1; i++) {
        domains.push(`; domain=.${labels.slice(i).join('.')}`);
    }
    domains.forEach((domain) => {
        document.cookie = `${name}=; path=/${domain}; max-age=0`;
    });
};

const clearTracking = (categories) => {
    const names = document.cookie.split(';').map((pair) => pair.split('=')[0].trim()).filter(Boolean);

    names.forEach((name) => {
        if (categories.some((category) => (TRACKING_COOKIES[category] || []).some((pattern) => pattern.test(name)))) {
            expireEverywhere(name);
        }
    });

    categories.forEach((category) => {
        (TRACKING_SESSION_KEYS[category] || []).forEach((key) => {
            try { sessionStorage.removeItem(key); } catch (_) {}
        });
    });
};

const updateGtag = () => {
    if (typeof window.gtag !== 'function') return;
    const analytics = consent.has('analytics') ? 'granted' : 'denied';
    const marketing = consent.has('marketing') ? 'granted' : 'denied';
    window.gtag('consent', 'update', {
        ad_storage: marketing,
        ad_user_data: marketing,
        ad_personalization: marketing,
        analytics_storage: analytics,
    });
};

const choices = () => banner()?.querySelector('[data-cookie-consent-choices]');
const chooseButton = () => banner()?.querySelector('[data-cookie-consent-choose]');

/** Each toggle starts from what is granted now, so reopening the banner shows the current choice. */
const prefill = () => {
    banner()?.querySelectorAll('[data-cookie-consent-category]').forEach((input) => {
        input.checked = consent.has(input.getAttribute('data-cookie-consent-category'));
    });
};

const openChoices = () => {
    const panel = choices();
    if (!panel) return;
    prefill();
    panel.hidden = false;
    chooseButton()?.setAttribute('aria-expanded', 'true');
};

const show = ({ withChoices = false } = {}) => {
    const el = banner();
    if (!el) return;
    el.removeAttribute('data-state');
    el.hidden = false;
    if (withChoices) {
        openChoices();
    }
};

const hide = () => {
    const el = banner();
    if (!el) return;
    el.setAttribute('data-state', 'closing');
    setTimeout(() => {
        el.hidden = true;
        el.removeAttribute('data-state');
        const panel = choices();
        if (panel) panel.hidden = true;
        chooseButton()?.setAttribute('aria-expanded', 'false');
    }, 150);
};

const save = (categories) => {
    const before = consent.granted();
    const state = { v: consent.VERSION, t: Date.now(), c: consent.CATEGORIES.filter((c) => categories.includes(c)) };

    try { localStorage.setItem(consent.KEY, JSON.stringify(state)); } catch (_) {}
    writeCookie(state);

    const withdrawn = before.filter((category) => !state.c.includes(category));
    if (withdrawn.length) {
        clearTracking(withdrawn);
    }

    updateGtag();
    notify();
    hide();
};

const init = () => {
    const state = consent.read();

    // Global Privacy Control is a refusal of everything: no banner, and anything an earlier
    // "Allow" left behind goes. The server reads the Sec-GPC header itself, so the mirror is
    // left alone rather than rewritten on every page.
    if (consent.gpc()) {
        if (state && state.c.length) {
            clearTracking(consent.CATEGORIES);
        }

        return;
    }

    if (consent.answered()) {
        writeCookie(state);

        return;
    }

    if (state && state.v >= consent.VERSION) {
        // Lapsed after twelve months: nothing it allowed is allowed any more.
        clearTracking(consent.CATEGORIES);
        writeCookie(null);
    } else if (state) {
        // A version-1 choice stays in force (analytics only) until the visitor answers again.
        writeCookie(state);
    }

    show();
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
        if (value === 'denied') {
            save([]);
        } else if (value === 'all') {
            save(consent.CATEGORIES);
        } else if (value === 'save') {
            const picked = [...(banner()?.querySelectorAll('[data-cookie-consent-category]') || [])]
                .filter((input) => input.checked)
                .map((input) => input.getAttribute('data-cookie-consent-category'));
            save(picked);
        }

        return;
    }

    const choose = e.target.closest('[data-cookie-consent-choose]');
    if (choose) {
        const panel = choices();
        if (panel && panel.hidden) {
            openChoices();
        } else if (panel) {
            panel.hidden = true;
            choose.setAttribute('aria-expanded', 'false');
        }

        return;
    }

    // GDPR Article 7(3): withdrawing consent must be as easy as giving it. Reopening shows the
    // current choice with both toggles; Decline, or Save with a toggle off, withdraws.
    const reopen = e.target.closest('[data-cookie-consent-reopen]');
    if (reopen) {
        e.preventDefault();
        show({ withChoices: true });
    }
});
