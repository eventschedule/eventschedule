// Loads the "Chat with a person" widget on the marketing site, but only when there is a
// reason to: the admin is online, or this visitor already has a conversation worth showing.
// Everyone else pays for one small, deferred JSON request and nothing more - the widget's own
// chunk is a dynamic import.
//
// The page this runs on is edge-cached HTML shared by every visitor, so nothing about the
// visitor or the admin's presence can be in it; see layouts/marketing.blade.php and
// SupportChatGuestController.

export const TOKEN_KEY = 'es_support_chat_token';
export const ACTIVE_KEY = 'es_support_chat_active';
const STATUS_CACHE_KEY = 'es_support_chat_status';
const STATUS_CACHE_MS = 60000;
const RESUME_HASH = /(?:^#|&)support-chat=([A-Za-z0-9]{32,64})/;

export function storageGet(storage, key) {
    try {
        return window[storage].getItem(key);
    } catch (e) {
        return null;
    }
}

export function storageSet(storage, key, value) {
    try {
        if (value === null) {
            window[storage].removeItem(key);
        } else {
            window[storage].setItem(key, value);
        }
    } catch (e) {
        // Private mode or blocked storage: the chat still works for this page view.
    }
}

// An emailed reply links back with the token in the FRAGMENT (SendSupportReplyEmail), which the
// server never sees. Adopt it and take it out of the address bar so it is not bookmarked or shared.
function adoptResumeToken() {
    const match = window.location.hash.match(RESUME_HASH);
    if (!match) {
        return null;
    }
    storageSet('localStorage', TOKEN_KEY, match[1]);
    try {
        history.replaceState(null, '', window.location.pathname + window.location.search);
    } catch (e) {
        // ignore
    }
    return match[1];
}

function fetchStatus(url) {
    const cached = storageGet('sessionStorage', STATUS_CACHE_KEY);
    if (cached) {
        try {
            const parsed = JSON.parse(cached);
            if (Date.now() - parsed.at < STATUS_CACHE_MS) {
                return Promise.resolve(parsed.data);
            }
        } catch (e) {
            // fall through
        }
    }

    return fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
        .then((r) => (r.ok ? r.json() : null))
        .then((data) => {
            if (data) {
                storageSet('sessionStorage', STATUS_CACHE_KEY, JSON.stringify({ at: Date.now(), data }));
            }
            return data;
        })
        .catch(() => null);
}

function hasUnreadReply(config, token) {
    return fetch(config.messagesUrl, {
        headers: { Accept: 'application/json', 'X-Support-Chat-Token': token },
        credentials: 'same-origin',
    })
        .then((r) => {
            if (r.status === 404) {
                storageSet('localStorage', TOKEN_KEY, null);
            }
            return r.ok ? r.json() : null;
        })
        .then((data) => !!(data && data.unread_count > 0))
        .catch(() => false);
}

function mount(host, config, options) {
    import('./components/SupportChatWidget.vue').then(({ default: SupportChatWidget }) => {
        const { createApp } = window.Vue;
        createApp(SupportChatWidget, { config, ...options }).mount(host);
    });
}

export function bootSupportChat() {
    const host = document.getElementById('es-support-chat-host');
    const configEl = document.getElementById('es-support-chat-config');
    if (!host || !configEl || !window.Vue) {
        return;
    }

    let config;
    try {
        config = JSON.parse(configEl.textContent || '{}');
    } catch (e) {
        return;
    }

    const resumed = adoptResumeToken();
    const token = storageGet('localStorage', TOKEN_KEY);
    const activeThisSession = storageGet('sessionStorage', ACTIVE_KEY) === '1';

    const run = () => {
        fetchStatus(config.statusUrl).then((status) => {
            const available = !!(status && status.available);
            const agent = status ? status.agent : null;

            if (available || resumed || (token && activeThisSession)) {
                mount(host, config, { initialAvailable: available, initialAgent: agent, openOnMount: !!resumed });
                return;
            }

            // Offline, but they asked something earlier and an answer is waiting.
            if (token) {
                hasUnreadReply(config, token).then((unread) => {
                    if (unread) {
                        mount(host, config, { initialAvailable: false, initialAgent: agent, openOnMount: false });
                    }
                });
            }
        });
    };

    // After the page has finished loading and gone idle: this must never compete with the
    // page's own first paint.
    const whenIdle = () => {
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(run, { timeout: 3000 });
        } else {
            setTimeout(run, 1500);
        }
    };

    if (document.readyState === 'complete') {
        whenIdle();
    } else {
        window.addEventListener('load', whenIdle, { once: true });
    }
}
