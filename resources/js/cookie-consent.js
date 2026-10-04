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
 * Write the choice into the `cookie_consent` cookie, which is the RECORD of it (consent-state.js
 * explains the format and why localStorage is only this origin's copy). The server honours it too
 * (consent_granted() in app/helpers.php): CaptureUtmParameters writes the 30-day attribution
 * cookies only with marketing consent, and checkout records it on the sale for the Meta Conversions
 * API. The cookie is exempt from Laravel's cookie encryption (bootstrap/app.php) because Laravel
 * cannot decrypt a cookie the browser wrote. Dots, not commas: RFC 6265 leaves commas out of a
 * cookie value.
 *
 * It lives exactly as long as the choice and is not rolled forward on later visits: a choice
 * lapses twelve months after it was made, and the banner then asks again.
 *
 * Written on config('session.domain') when the server supplies one, so on the hosted service one
 * cookie covers the marketing site, the app and every schedule subdomain, exactly like the utm_*
 * cookies it gates: a choice made, or withdrawn, on any of them holds on all of them. Empty on a
 * custom domain and on a bare selfhost, which keeps it host-only there.
 */
// From a meta tag rather than the banner: init() runs on every page load, including pages where
// the banner is not rendered (an admin session, or an install that turned consent_required() off
// after visitors had answered).
const cookieDomain = () => document.querySelector('meta[name="cookie-domain"]')?.content || '';

const writeCookie = (state) => {
    const secure = location.protocol === 'https:' ? '; Secure' : '';
    const domain = cookieDomain();
    const scope = domain ? `; domain=${domain}` : '';
    let value = '';
    let age = 0;

    if (state && state.v >= consent.VERSION) {
        value = `${state.c.length ? state.c.join('.') : 'denied'}.${Math.floor(state.t / 1000)}`;
        age = Math.max(0, Math.floor((consent.MAX_AGE_MS - (Date.now() - state.t)) / 1000));
    } else if (state && state.c.length) {
        // A version-1 "granted", until it stops counting.
        value = 'granted';
        age = Math.max(0, Math.floor((consent.LEGACY_UNTIL - Date.now()) / 1000));
    } else if (state) {
        value = 'denied';
        age = Math.floor(consent.MAX_AGE_MS / 1000);
    }

    // An older build wrote a host-only copy; two same-named cookies would make the answer depend
    // on which one a reader happens to see first.
    if (domain) {
        document.cookie = `${consent.KEY}=; path=/; max-age=0; SameSite=Lax${secure}`;
    }
    document.cookie = `${consent.KEY}=${value}; path=/${scope}; max-age=${age}; SameSite=Lax${secure}`;
};

const writeStored = (state) => {
    try {
        if (state) {
            localStorage.setItem(consent.KEY, JSON.stringify(state));
        } else {
            localStorage.removeItem(consent.KEY);
        }
    } catch (_) {}
};

/**
 * Third-party cookies the consented scripts leave behind, by the category that allowed them.
 * Withdrawing a category deletes them, as GDPR Article 7(3) expects withdrawal to undo what
 * consent allowed. utm_* are absent on purpose: they are HttpOnly, so the server expires them on
 * the next request once the mirrored cookie stops saying "marketing".
 */
const TRACKING_COOKIES = {
    analytics: [/^_ga$/, /^_ga_/, /^_gid$/],
    // __gads, __gpi and __eoi are AdSense's first-party cookies.
    marketing: [/^_gcl_/, /^_fbp$/, /^_fbc$/, /^__gads$/, /^__gpi$/, /^__eoi$/, /^es_attribution$/],
};

const TRACKING_SESSION_KEYS = {
    analytics: ['es_hero', 'es_hero_clicked'],
    marketing: [],
};

/**
 * Expired host-only and on the host itself, and on the hosted service also on every parent domain
 * up to the install's cookie domain: gtag and Meta's pixel write on the registrable domain
 * (".eventschedule.com" for a schedule subdomain), which a host-only expiry would miss.
 *
 * Never above the host where there is no cookie domain (a custom domain, a bare selfhost): there
 * the parent belongs to someone else, and "events.venue.com" must not delete the _ga that
 * venue.com set under its own consent. Our own Google Analytics writes host-only there (the
 * cookie_domain in partials/google-analytics.blade.php), so the host is where its cookies are.
 */
const expireEverywhere = (name) => {
    const host = location.hostname;
    const scope = cookieDomain().replace(/^\./, '');
    const domains = ['', `; domain=${host}`];

    if (scope && (host === scope || host.endsWith(`.${scope}`))) {
        const labels = host.split('.');
        for (let i = 1; i < labels.length; i++) {
            const parent = labels.slice(i).join('.');
            if (parent.length < scope.length) break;
            domains.push(`; domain=.${parent}`);
        }
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

// Where focus was when the banner was reopened, so closing it can put focus back.
let opener = null;

const firstControl = (el) => el.querySelector('button:not([disabled]), input:not([disabled]), a[href]');

const show = ({ withChoices = false, from = null, focusDelay = 0 } = {}) => {
    const el = banner();
    if (!el) return;
    el.removeAttribute('data-state');
    el.hidden = false;

    // Global Privacy Control already declines everything, whatever is picked here, so say so
    // rather than offer switches that would do nothing.
    const gpc = consent.gpc();
    const note = el.querySelector('[data-cookie-consent-gpc]');
    if (note) note.hidden = !gpc;
    el.querySelectorAll('[data-cookie-consent-category], [data-cookie-consent-action="all"]').forEach((control) => {
        control.disabled = gpc;
    });

    if (withChoices) {
        openChoices();
    }

    // Only when someone asked for it: the banner that appears by itself on a first visit must
    // not pull focus away from the page.
    // A delay lets a closing dialog hand focus back first, so the banner keeps it.
    if (from) {
        opener = from;
        const focusIn = () => firstControl(el)?.focus();
        if (focusDelay) setTimeout(focusIn, focusDelay); else focusIn();
    }
};

const hide = () => {
    const el = banner();
    if (!el) return;
    const restore = el.contains(document.activeElement) ? opener : null;
    opener = null;
    el.setAttribute('data-state', 'closing');
    setTimeout(() => {
        el.hidden = true;
        el.removeAttribute('data-state');
        const panel = choices();
        if (panel) panel.hidden = true;
        chooseButton()?.setAttribute('aria-expanded', 'false');
        // Not into a dialog that has closed since: a hidden element cannot take focus.
        if (restore && document.contains(restore) && restore.offsetParent !== null) restore.focus();
    }, 150);
};

const save = (categories) => {
    const before = consent.granted();
    // Whole seconds, the precision the cookie keeps, so the two copies compare equal.
    const state = {
        v: consent.VERSION,
        t: Math.floor(Date.now() / 1000) * 1000,
        c: consent.CATEGORIES.filter((c) => categories.includes(c)),
    };

    writeStored(state);
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
    const cookie = consent.readCookie();
    const stored = consent.readStored();

    // The cookie is the record. When it carries a dated choice this origin's copy has not seen
    // (made or withdrawn on another part of the install), adopt it, and delete what a category
    // this origin had allowed but the record no longer does left behind on this host.
    if (cookie && cookie.v >= consent.VERSION && (!stored || stored.v < consent.VERSION || stored.t !== cookie.t)) {
        if (stored && !consent.lapsed(stored)) {
            const withdrawn = stored.c.filter((category) => !cookie.c.includes(category));
            if (withdrawn.length) clearTracking(withdrawn);
        }
        writeStored(cookie);
    }

    const state = consent.read();

    // Global Privacy Control is a refusal of everything: no banner, and anything an earlier
    // "Allow" left behind goes. The server reads the Sec-GPC header itself, so the record is left
    // alone rather than rewritten on every page.
    if (consent.gpc()) {
        if (state && state.c.length) {
            clearTracking(consent.CATEGORIES);
        }

        return;
    }

    if (consent.answered()) {
        // The record went missing but this origin still holds a current choice: Safari keeps a
        // cookie written by script for seven days, and cookies can be cleared without site data.
        if (!cookie || cookie.v < consent.VERSION) {
            writeCookie(state);
        }

        return;
    }

    if (state && state.v >= consent.VERSION) {
        // Lapsed after twelve months: nothing it allowed is allowed any more.
        clearTracking(consent.CATEGORIES);
        writeStored(null);
        writeCookie(null);
    } else if (state && !cookie) {
        // A version-1 choice stays in force (analytics only) until the visitor answers again, or
        // until LEGACY_UNTIL, whichever is first.
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
        // A control inside a modal (the app's About dialog) closes it, so the banner is not
        // opened underneath it.
        const modal = reopen.getAttribute('data-close-modal');
        if (modal) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: modal }));
        }
        show({ withChoices: true, from: reopen, focusDelay: modal ? 250 : 0 });
    }
});
