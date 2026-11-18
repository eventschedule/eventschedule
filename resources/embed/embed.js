/*!
 * Event Schedule embed helper (protocol v1).
 *
 * Paste it beside an embedded Event Schedule calendar and an event a visitor opens is shown over
 * this page, in a window of its own, instead of on eventschedule.com; the calendar's frame is also
 * fitted to the calendar's height. Without it the calendar still works: it shows the event inside
 * its own frame.
 *
 * It listens for messages from the calendars on this page and does nothing else. Every address it
 * opens is a path on the calendar's own site, checked before use; it reads nothing of this page
 * and sends nothing anywhere but to those calendars.
 *
 * Events on this window, for your own analytics:
 *   eventschedule:event-opened  detail: { url, name }
 *   eventschedule:checkout      detail: { url, form }   (Get tickets or Register was pressed)
 */
(function () {
    'use strict';

    var VERSION = 1;
    var PREFIX = 'eventschedule:';
    var HASH = '#es-event=';

    if (window.EventScheduleEmbed && window.EventScheduleEmbed.version) {
        return;
    }

    var api = { version: VERSION };
    window.EventScheduleEmbed = api;

    function isHttpOrigin(origin) {
        return typeof origin === 'string' && /^https?:\/\/[^\/?#\s\\@]+$/i.test(origin);
    }

    // A path a calendar sent, made into an address on that calendar's own origin, or null. Only an
    // event shown for a frame (embed=true&view=event) is ever opened: never another origin, never a
    // scheme-relative or backslashed path, never javascript: or data:.
    function resolve(path, origin) {
        if (typeof path !== 'string' || path.length > 2048 || !isHttpOrigin(origin)) {
            return null;
        }
        if (path.charAt(0) !== '/' || path.charAt(1) === '/' || /[\s\\]/.test(path) || /[\u0000-\u001f\u007f]/.test(path)) {
            return null;
        }
        var base;
        var url;
        try {
            base = new URL(origin);
            url = new URL(path, base.origin);
        } catch (e) {
            return null;
        }
        if ((base.protocol !== 'https:' && base.protocol !== 'http:') || url.origin !== base.origin) {
            return null;
        }
        if (url.searchParams.get('embed') !== 'true' || url.searchParams.get('view') !== 'event') {
            return null;
        }
        url.hash = '';

        return url.href;
    }

    api.resolve = resolve;

    // Pasted into a builder that puts the code inside a frame of its own (Wix, and others): an
    // overlay could only fill that small frame, so the calendars are left to show events inside
    // themselves, which they do when nobody greets them.
    var wrapped;
    try {
        wrapped = window.self !== window.top;
    } catch (e) {
        wrapped = true;
    }
    api.wrapped = wrapped;
    if (wrapped) {
        return;
    }

    function text(value, max) {
        return typeof value === 'string' ? value.replace(/[\u0000-\u001f\u007f]/g, ' ').slice(0, max) : '';
    }

    function srcOf(iframe) {
        try {
            return new URL(iframe.getAttribute('src') || '', window.location.href);
        } catch (e) {
            return null;
        }
    }

    // A calendar (or an event widget) of ours: an embed address. Never the signup form, whose own
    // snippet fits it to its height.
    function isCalendarFrame(iframe) {
        var src = srcOf(iframe);
        if (!src || (src.protocol !== 'https:' && src.protocol !== 'http:')) {
            return false;
        }
        var embed = src.searchParams.get('embed');

        return (embed === 'true' || embed === '1') && src.searchParams.get('form') !== 'subscribe';
    }

    // The calendars that have said who they are: an embed frame that sent eventschedule:ready from
    // the very origin its src names. An embed address alone proves nothing (other services put
    // embed=true in theirs too), and a message's origin must be the frame's own, so a frame that
    // has wandered elsewhere, or anything else on the page, is never acted on.
    var trusted = [];

    function originOf(iframe) {
        var src = srcOf(iframe);

        return src ? src.origin : null;
    }

    function isTrusted(iframe, origin) {
        return trusted.indexOf(iframe) !== -1 && origin === originOf(iframe);
    }

    function frameFor(source) {
        if (!source) {
            return null;
        }
        var frames = document.getElementsByTagName('iframe');
        for (var i = 0; i < frames.length; i++) {
            if (frames[i].contentWindow === source) {
                return frames[i];
            }
        }

        return null;
    }

    function greet(win, origin, extra) {
        if (!win || !isHttpOrigin(origin)) {
            return;
        }
        var message = { type: PREFIX + 'hello', v: VERSION };
        if (extra) {
            for (var key in extra) {
                if (Object.prototype.hasOwnProperty.call(extra, key)) {
                    message[key] = extra[key];
                }
            }
        }
        try {
            win.postMessage(message, origin);
        } catch (e) {}
    }

    function emit(name, detail) {
        var event;
        try {
            event = new CustomEvent(name, { detail: detail });
        } catch (e) {
            event = document.createEvent('CustomEvent');
            event.initCustomEvent(name, false, false, detail);
        }
        window.dispatchEvent(event);
    }

    function css(el, styles) {
        for (var key in styles) {
            if (Object.prototype.hasOwnProperty.call(styles, key)) {
                el.style.setProperty(key, styles[key]);
            }
        }
    }

    // The dim behind the overlay. A page whose policy refuses inline styles keeps the browser's own.
    var backdropAdded = false;
    function addBackdrop() {
        if (backdropAdded) {
            return;
        }
        backdropAdded = true;
        try {
            var style = document.createElement('style');
            style.textContent = 'dialog[data-eventschedule-overlay]::backdrop{background:rgba(15,23,42,.6)}';
            (document.head || document.documentElement).appendChild(style);
        } catch (e) {}
    }

    var overlay = null;
    var savedOverflow = null;

    function lockScroll() {
        var html = document.documentElement;
        var body = document.body;
        savedOverflow = [html.style.overflow, body ? body.style.overflow : ''];
        html.style.overflow = 'hidden';
        if (body) {
            body.style.overflow = 'hidden';
        }
    }

    function unlockScroll() {
        if (!savedOverflow) {
            return;
        }
        document.documentElement.style.overflow = savedOverflow[0];
        if (document.body) {
            document.body.style.overflow = savedOverflow[1];
        }
        savedOverflow = null;
    }

    // A full-screen sheet on a phone, a window over the page from there up. Sized to the window,
    // never to the event, which scrolls inside it.
    function size() {
        if (!overlay) {
            return;
        }
        var phone = window.matchMedia && window.matchMedia('(max-width: 639px)').matches;
        var d = overlay.dialog;
        if (phone) {
            css(d, { width: '100vw', 'max-width': '100vw', height: '100vh', 'max-height': '100vh', margin: '0', 'border-radius': '0' });
            d.style.setProperty('height', '100dvh');
            d.style.setProperty('max-height', '100dvh');
        } else {
            css(d, { width: 'min(960px, calc(100vw - 32px))', 'max-width': 'calc(100vw - 32px)', height: 'calc(100vh - 32px)', 'max-height': 'calc(100vh - 32px)', margin: 'auto', 'border-radius': '12px' });
            d.style.setProperty('height', 'calc(100dvh - 32px)');
            d.style.setProperty('max-height', 'calc(100dvh - 32px)');
        }
    }

    function ourHash() {
        var hash = window.location.hash || '';

        return hash.indexOf(HASH) === 0 ? hash.slice(HASH.length) : null;
    }

    function shareUrl(path) {
        return window.location.href.split('#')[0] + HASH + encodeURIComponent(path);
    }

    function label(dialog, closeButton, frame, meta) {
        var labels = meta.labels && typeof meta.labels === 'object' ? meta.labels : {};
        var name = text(meta.name, 200);
        var closeLabel = text(labels.close || meta.close, 60);
        if (name || !dialog.getAttribute('aria-label')) {
            dialog.setAttribute('aria-label', name || text(labels.dialog, 80) || 'Event');
            frame.setAttribute('title', name || text(labels.dialog, 80) || 'Event');
        }
        if (closeLabel) {
            closeButton.setAttribute('aria-label', closeLabel);
            closeButton.setAttribute('title', closeLabel);
        }
        var dir = meta.dir === 'rtl' ? 'rtl' : (meta.dir === 'ltr' ? 'ltr' : null);
        if (dir) {
            dialog.setAttribute('dir', dir);
            closeButton.style.setProperty(dir === 'rtl' ? 'left' : 'right', '10px');
            closeButton.style.removeProperty(dir === 'rtl' ? 'right' : 'left');
        }
        if (typeof meta.lang === 'string' && /^[A-Za-z]{2,3}(-[A-Za-z0-9]{2,8})*$/.test(meta.lang)) {
            dialog.setAttribute('lang', meta.lang);
        }
    }

    function open(url, meta, opener, openerOrigin, fromHash) {
        if (overlay) {
            close(true);
        }
        addBackdrop();

        var target = new URL(url);
        var path = target.pathname + target.search;
        var dark = meta.theme === 'dark';

        var dialog = document.createElement('dialog');
        dialog.setAttribute('data-eventschedule-overlay', '');
        css(dialog, { padding: '0', border: '0', overflow: 'hidden', background: dark ? '#111827' : '#ffffff', 'box-shadow': '0 25px 50px -12px rgba(0,0,0,.45)', 'box-sizing': 'border-box', 'z-index': '2147483647' });

        var frame = document.createElement('iframe');
        frame.setAttribute('allow', 'clipboard-write; web-share; fullscreen');
        css(frame, { border: '0', width: '100%', height: '100%', display: 'block' });

        // On the host's side of the frame, so it is there and works whatever the event page does.
        var closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.setAttribute('aria-label', 'Close');
        closeButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
        css(closeButton, { position: 'absolute', top: '10px', right: '10px', width: '44px', height: '44px', display: 'flex', 'align-items': 'center', 'justify-content': 'center', 'border-radius': '9999px', border: '0', cursor: 'pointer', background: dark ? 'rgba(31,41,55,.92)' : 'rgba(255,255,255,.92)', color: dark ? '#f9fafb' : '#111827', 'box-shadow': '0 1px 3px rgba(0,0,0,.3)', padding: '0', margin: '0' });
        closeButton.addEventListener('click', function () {
            close();
        });

        label(dialog, closeButton, frame, meta);
        dialog.appendChild(frame);
        dialog.appendChild(closeButton);

        dialog.addEventListener('cancel', function (e) {
            e.preventDefault();
            close();
        });
        // A press on the dim around the window closes it. Clicks inside are this overlay's own
        // and are not handed on to the page's listeners.
        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) {
                close();
            }
            e.stopPropagation();
        });
        dialog.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                e.stopPropagation();
            }
        });

        overlay = {
            dialog: dialog,
            iframe: frame,
            closeButton: closeButton,
            origin: target.origin,
            opener: opener,
            openerOrigin: openerOrigin,
            share: shareUrl(path),
            pushedHash: false,
        };

        frame.addEventListener('load', function () {
            if (!overlay || overlay.iframe !== frame) {
                return;
            }
            try {
                frame.focus();
            } catch (e) {}
            greet(frame.contentWindow, overlay.origin, { overlay: true, share: overlay.share });
        });

        (document.body || document.documentElement).appendChild(dialog);
        size();
        lockScroll();
        frame.setAttribute('src', url);
        if (typeof dialog.showModal === 'function') {
            try {
                dialog.showModal();
            } catch (e) {
                dialog.setAttribute('open', '');
            }
        } else {
            dialog.setAttribute('open', '');
            css(dialog, { position: 'fixed', inset: '0' });
        }

        // The address of this page with the event open on it: shared, or opened again, it opens
        // the event again. A hash and not a new address, so the site's own router never sees it,
        // and the browser's Back closes the overlay.
        if (!fromHash) {
            overlay.pushedHash = true;
            window.location.hash = HASH.slice(1) + encodeURIComponent(path);
        }

        emit(PREFIX + 'event-opened', { url: url, name: text(meta.name, 200) });
    }

    function close(fromHistory) {
        if (!overlay) {
            return;
        }
        var was = overlay;
        overlay = null;
        try {
            was.dialog.close();
        } catch (e) {}
        if (was.dialog.parentNode) {
            was.dialog.parentNode.removeChild(was.dialog);
        }
        unlockScroll();

        if (!fromHistory && ourHash() !== null) {
            if (was.pushedHash) {
                window.history.back();
            } else {
                try {
                    window.history.replaceState(window.history.state, '', window.location.pathname + window.location.search);
                } catch (e) {}
            }
        }

        if (was.opener) {
            try {
                was.opener.focus();
            } catch (e) {}
            if (was.opener.contentWindow && was.openerOrigin) {
                try {
                    was.opener.contentWindow.postMessage({ type: PREFIX + 'closed', v: VERSION }, was.openerOrigin);
                } catch (e) {}
            }
        }
    }

    api.close = function () {
        close();
    };

    function checkoutUrl(data, origin) {
        var path = data && data.path;
        if (typeof path !== 'string' || path.charAt(0) !== '/' || path.charAt(1) === '/' || /[\s\\]/.test(path)) {
            return null;
        }
        try {
            var url = new URL(path, origin);

            return url.origin === new URL(origin).origin ? url.href : null;
        } catch (e) {
            return null;
        }
    }

    window.addEventListener('message', function (e) {
        var data = e.data;
        if (!data || typeof data !== 'object' || typeof data.type !== 'string' || data.type.indexOf(PREFIX) !== 0) {
            return;
        }
        if (!isHttpOrigin(e.origin)) {
            return;
        }
        var type = data.type.slice(PREFIX.length);

        // The event in the overlay.
        if (overlay && e.source === overlay.iframe.contentWindow) {
            if (e.origin !== overlay.origin) {
                return;
            }
            if (type === 'ready') {
                label(overlay.dialog, overlay.closeButton, overlay.iframe, { name: data.name, lang: data.lang, dir: data.dir, close: data.close });
                greet(e.source, e.origin, { overlay: true, share: overlay.share });
            } else if (type === 'close') {
                close();
            } else if (type === 'checkout') {
                emit(PREFIX + 'checkout', { url: checkoutUrl(data, e.origin), form: data.form === 'rsvp' ? 'rsvp' : 'tickets' });
            }

            return;
        }

        // A calendar on this page, from its own origin.
        var iframe = frameFor(e.source);
        if (!iframe || !isCalendarFrame(iframe) || e.origin !== originOf(iframe)) {
            return;
        }
        if (type === 'ready') {
            if (trusted.indexOf(iframe) === -1) {
                trusted.push(iframe);
            }
            greet(e.source, e.origin);
            // A link to this page with an event open waits for a calendar it can trust.
            openFromHash();

            return;
        }
        if (!isTrusted(iframe, e.origin)) {
            return;
        }
        if (type === 'height') {
            var height = Number(data.height);
            if (isFinite(height) && height >= 50 && height <= 50000) {
                iframe.style.height = Math.ceil(height) + 'px';
            }
        } else if (type === 'open') {
            var url = resolve(data.path, e.origin);
            if (url) {
                open(url, data, iframe, e.origin, false);
            }
        } else if (type === 'checkout') {
            emit(PREFIX + 'checkout', { url: checkoutUrl(data, e.origin), form: data.form === 'rsvp' ? 'rsvp' : 'tickets' });
        }
    });

    function openFromHash() {
        var raw = ourHash();
        if (raw === null || overlay) {
            return;
        }
        var path;
        try {
            path = decodeURIComponent(raw);
        } catch (e) {
            return;
        }
        // Only a calendar that has said who it is, and on an install that serves schedules under
        // a path (example.com/{schedule}), only an event under that schedule's path: the address
        // is anybody's to write.
        for (var i = 0; i < trusted.length; i++) {
            var frame = trusted[i];
            var src = srcOf(frame);
            if (!src || !isCalendarFrame(frame)) {
                continue;
            }
            var base = src.pathname.replace(/\/+$/, '');
            if (base !== '' && path.indexOf(base + '/') !== 0) {
                continue;
            }
            var url = resolve(path, src.origin);
            if (url) {
                open(url, {}, frame, src.origin, true);

                return;
            }
        }
    }

    window.addEventListener('hashchange', function () {
        if (overlay) {
            // Back, past the address the overlay added: the overlay goes with it.
            if (ourHash() === null) {
                close(true);
            }

            return;
        }
        openFromHash();
    });

    window.addEventListener('resize', size);

    function init() {
        var frames = document.getElementsByTagName('iframe');
        for (var i = 0; i < frames.length; i++) {
            if (isCalendarFrame(frames[i])) {
                greet(frames[i].contentWindow, srcOf(frames[i]).origin);
            }
        }
        openFromHash();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
