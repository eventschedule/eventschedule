{{-- The frame's half of the conversation with embed.js, the script the Embed dialog's "Show it on
     your website" code puts on the organizer's page (EmbedScriptController serves it, and its file
     says the other half). Printed on every embed page (layouts/app-guest), and inert until the page
     it sits in greets it: an old snippet, a site builder that strips scripts or one that wraps the
     code in a frame of its own sends nothing, and then the calendar opens an event in this frame.

     The protocol is versioned (v: 1) and only ever grows. Every type is new: never
     eventschedule:resize, which the signup form's code resizes ANY frame on.

       frame -> host   eventschedule:ready     on load, to whoever framed us ('*': it says nothing),
                                               and again to the host once it has said hello
       host -> frame   eventschedule:hello     embed.js is there; {overlay, share} for its overlay
       frame -> host   eventschedule:height    this calendar's height, once greeted
       frame -> host   eventschedule:open      open an event: a PATH, its name, language, direction
       frame -> host   eventschedule:close     the overlay's event page asks to be closed
       frame -> host   eventschedule:checkout  Get tickets or Register was pressed
       host -> frame   eventschedule:closed    the overlay this calendar opened was closed

     Once greeted, everything goes to that host's origin and nowhere else, and only messages from
     the window that greeted us, from that origin, are heard.

     The host is the window that framed us, or the TOP page when a site builder wraps the pasted
     code in a frame of its own (Hostinger, Wix): there embed.js runs in the top page, from the
     site's own header code or reached from inside the builder's frame, so ready is said to the
     top page too, and said again for a few seconds, because the top page's script may arrive
     after this frame has loaded. The host's share address is used for
     one thing, the overlay's Share button, and only as an http(s) address. --}}
<script {!! nonce_attr() !!}>
(function () {
    if (window.parent === window) {
        return;
    }

    var frame = window.esEmbedFrame = { greeted: false, overlay: false, host: null, share: null };
    // The window that greeted us: the parent, or the top page above a builder's frame.
    var hostWindow = null;
    var nested = window.top !== window.parent;
    var waiting = [];

    // What the host may label its overlay with when it opened one from its own address (#es-event=)
    // and so was told nothing: this page's language, direction, name and the word for Close.
    function readyMessage() {
        var heading = document.querySelector('[data-embed-heading]');

        return {
            type: 'eventschedule:ready',
            v: 1,
            lang: document.documentElement.getAttribute('lang') || '',
            dir: document.documentElement.getAttribute('dir') === 'rtl' ? 'rtl' : 'ltr',
            // Where this calendar is: a top page that cannot see our iframe element (a builder's
            // frame from another origin is in between) checks a deep link's path against it.
            path: window.location.pathname,
            name: heading ? heading.textContent.replace(/\s+/g, ' ').trim().slice(0, 200) : '',
            close: @json(__('messages.close')),
        };
    }

    frame.whenGreeted = function (fn) {
        if (frame.greeted) {
            fn();
        } else {
            waiting.push(fn);
        }
    };

    frame.post = function (type, data) {
        if (!frame.greeted || !frame.host) {
            return false;
        }
        var message = { type: 'eventschedule:' + type, v: 1 };
        data = data || {};
        for (var key in data) {
            if (Object.prototype.hasOwnProperty.call(data, key)) {
                message[key] = data[key];
            }
        }
        try {
            hostWindow.postMessage(message, frame.host);
            return true;
        } catch (e) {
            return false;
        }
    };

    window.addEventListener('message', function (e) {
        if ((e.source !== window.parent && e.source !== window.top) || !e.data || typeof e.data !== 'object') {
            return;
        }
        if (typeof e.origin !== 'string' || !/^https?:\/\/[^\/]+$/i.test(e.origin)) {
            return;
        }
        if (e.data.type === 'eventschedule:hello') {
            if (frame.greeted) {
                return;
            }
            frame.host = e.origin;
            hostWindow = e.source;
            frame.overlay = e.data.overlay === true;
            frame.share = (typeof e.data.share === 'string' && e.data.share.length < 4096 && /^https?:\/\//i.test(e.data.share)) ? e.data.share : null;
            frame.greeted = true;
            document.documentElement.setAttribute('data-embed-host', frame.overlay ? 'overlay' : 'page');
            // Said again, to the host now known, and before anything else: embed.js acts only on
            // a frame that announced itself from its own origin, and a host whose script loaded
            // after this page's first announcement greeted it without having heard it.
            try { hostWindow.postMessage(readyMessage(), frame.host); } catch (err) {}
            var queued = waiting;
            waiting = [];
            queued.forEach(function (fn) {
                try { fn(); } catch (err) {}
            });
        } else if (e.data.type === 'eventschedule:closed' && frame.greeted && e.source === hostWindow && e.origin === frame.host) {
            document.dispatchEvent(new CustomEvent('es-embed:closed'));
        }
    });

    // The calendar's height, so the host can fit the frame to it. Not the overlay's, which the
    // host sizes to the window. Measured from what the page draws, never from the body, which is
    // at least as tall as the frame and so could never let it shrink.
    frame.whenGreeted(function () {
        if (frame.overlay) {
            return;
        }
        var last = 0;
        var queued = false;
        // The content root, and the credit chip's own height under it: never where the chip is,
        // which the page's body (at least as tall as the frame) pushes to the frame's foot, so a
        // taller frame would move it down and ask for a taller frame again, for ever.
        function measure() {
            queued = false;
            var bottom = 0;
            document.querySelectorAll('[data-embed-root]').forEach(function (el) {
                var box = el.getBoundingClientRect();
                if (box.height > 0) {
                    bottom = Math.max(bottom, box.bottom + (window.pageYOffset || 0));
                }
            });
            var chip = document.querySelector('.es-credit-chip');
            if (bottom > 0 && chip && chip.offsetHeight) {
                bottom += chip.offsetHeight;
            }
            var height = Math.ceil(bottom + 8);
            if (bottom > 0 && Math.abs(height - last) > 4) {
                last = height;
                frame.post('height', { height: height });
            }
        }
        function queue() {
            if (!queued) {
                queued = true;
                requestAnimationFrame(measure);
            }
        }
        queue();
        if (window.ResizeObserver) {
            try {
                var watch = new ResizeObserver(queue);
                document.querySelectorAll('[data-embed-root]').forEach(function (el) { watch.observe(el); });
            } catch (e) {}
        }
        window.addEventListener('load', queue);
        window.addEventListener('resize', queue);
    });

    // Inside the overlay: the event's heading takes the focus, and Escape, which a key pressed in
    // a frame never reaches the host page with, asks the host to close.
    frame.whenGreeted(function () {
        if (!frame.overlay) {
            return;
        }
        var heading = document.querySelector('[data-embed-heading]');
        if (heading) {
            try { heading.focus({ preventScroll: true }); } catch (e) {}
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !e.defaultPrevented) {
                frame.post('close');
            }
        });
    });

    document.addEventListener('click', function (e) {
        var target = e.target && e.target.closest ? e.target : null;
        if (!target || e.defaultPrevented) {
            return;
        }
        var plain = e.button === 0 && !e.metaKey && !e.ctrlKey && !e.shiftKey && !e.altKey;

        // A link to a place on this page (the skip link, a heading in a description) stays on it:
        // under the framed event's <base target="_blank"> it would open the frame's bare address
        // in a new tab.
        var local = target.closest('a[href^="#"]');
        if (local && document.querySelector('base[target]')) {
            e.preventDefault();
            var id = local.getAttribute('href').slice(1);
            var place = null;
            try { place = id ? document.getElementById(decodeURIComponent(id)) : null; } catch (err) {}
            if (place) {
                place.scrollIntoView();
                if (!place.hasAttribute('tabindex') && !/^(A|BUTTON|INPUT|SELECT|TEXTAREA)$/.test(place.tagName)) {
                    place.setAttribute('tabindex', '-1');
                }
                try { place.focus({ preventScroll: true }); } catch (err) {}
            }
            return;
        }

        // Back to the calendar. In the overlay, the overlay closes. In this frame, the calendar it
        // came from is the page before this one, where the list remembers its place (recallList());
        // otherwise the link's own address, which the server built (EmbedEventView::calendarUrl()).
        var back = target.closest('[data-embed-back]');
        if (back && plain) {
            if (frame.greeted && frame.overlay) {
                e.preventDefault();
                frame.post('close');
                return;
            }
            try {
                var from = document.referrer ? new URL(document.referrer) : null;
                if (from && from.origin === window.location.origin && from.searchParams.get('embed')
                    && from.searchParams.get('view') !== 'event' && window.history.length > 1) {
                    e.preventDefault();
                    window.history.back();
                }
            } catch (err) {}
            return;
        }

        // Get tickets or Register: the link opens the event's page in a new tab as it is; the
        // host hears about it, for the organizer's own analytics.
        var checkout = target.closest('[data-embed-checkout]');
        if (checkout) {
            try {
                frame.post('checkout', { path: new URL(checkout.href).pathname, form: checkout.getAttribute('data-embed-checkout') });
            } catch (err) {}
            return;
        }

        // Share: inside the overlay, the organizer's own page with this event open on it.
        var share = target.closest('[data-embed-share]');
        if (share) {
            e.preventDefault();
            var url = (frame.greeted && frame.overlay && frame.share) ? frame.share : share.getAttribute('data-share-url');
            var done = function () {
                share.setAttribute('data-copied', '');
                setTimeout(function () { share.removeAttribute('data-copied'); }, 2000);
            };
            if (navigator.share) {
                navigator.share({ title: share.getAttribute('data-share-title') || '', url: url }).catch(function () {});
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(done).catch(function () {});
            }
        }
    });

    function announce() {
        if (frame.greeted) {
            return;
        }
        try {
            window.parent.postMessage(readyMessage(), '*');
        } catch (e) {}
        if (nested) {
            try {
                window.top.postMessage(readyMessage(), '*');
            } catch (e) {}
        }
    }
    announce();
    if (nested) {
        [1000, 3000, 8000].forEach(function (ms) { setTimeout(announce, ms); });
    }
})();
</script>
