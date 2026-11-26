<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}" class="overflow-x-clip">
<head class="h-full bg-white">
    <title>{{ $title ?? 'Event Schedule' }}</title>
    <!-- Version: {{ config('self-update.version_installed') }} -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @if ($icon)
    <link rel="icon" href="{{ $icon }}">
    <link rel="apple-touch-icon" href="{{ $icon }}">
    @else
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-96.png') }}" sizes="96x96">
    @endif
    {{-- <link rel="manifest"> and <meta name="theme-color"> are deliberately NOT here. They name
         and colour the site as an app, and this layout is the shell for the guest portal as well
         as the admin portal, so a single value here brands every schedule's site as ours: it made
         each of them installable as an app called "Event Schedule", whose launch splash is our
         logo. Each inner layout supplies its own instead - see AppController::manifest(). --}}
    {{-- Host-aware: this layout's head renders on guest pages too, custom domains included. --}}
    <link rel="sitemap" type="application/xml" href="{{ sitemap_url() }}">

    @if (\App\Services\OneSignalService::isConfigured() && ! request()->embed)
        @include('partials.onesignal')
    @endif
    
    @if (browser_error_reporting())
        <script {!! nonce_attr() !!}>
            @include('layouts.sentry')
        </script>
        @include('partials.sentry-sdk')
    @endif

    @if (isset($meta))
        {{ $meta }}
    @else
        <meta name="robots" content="noindex, nofollow">
        <link rel="canonical" href="{{ url()->current() }}">
        <meta name="description" content="The simple and free way to share your event schedule">
        <meta property="og:title" content="Event Schedule">
        <meta property="og:description" content="The simple and free way to share your event schedule">
        <meta property="og:image" content="{{ config('app.url') }}/images/social/home.jpg">
        <meta property="og:url" content="{{ str_replace('http://', 'https://', request()->url()) }}">
        <meta property="og:site_name" content="Event Schedule">
        <meta name="twitter:title" content="Event Schedule">
        <meta name="twitter:description" content="The simple and free way to share your event schedule">
        <meta name="twitter:image" content="{{ config('app.url') }}/images/social/home.jpg">
        <meta name="twitter:image:alt" content="Event Schedule">
        <meta name="twitter:card" content="summary_large_image">
    @endif    

    {{-- What the portal used jQuery for, in plain script, here where jQuery was loaded so that
         everything that ran after it still does. Each helper does what the jQuery call it replaced
         did, step for step, quirks included; tests/Browser/SharedScriptsJourneyTest.php was run
         green on jQuery and holds them to it.

         esReady(fn)        - $(document).ready(fn): after DOMContentLoaded, each in its own
                              timer, in the order asked (jQuery 3 runs ready callbacks async).
         esShow / esHide    - .show() / .hide(): hiding remembers the inline display, showing
                              puts it back, or the display a fresh element of the tag would have
                              when only a stylesheet (the hidden class) is hiding it.
         esVal / esSetVal   - .val() / .val(v): a select-one with nothing chosen reads null, a
                              null or undefined is written as '', \r is dropped from what is read.
         esPost(url, data, success, error) - $.post: form-encoded, X-Requested-With sent, a JSON
                              answer parsed, error(xhr, status, the HTTP status text). --}}
    <script {!! nonce_attr() !!}>
    (function () {
        var readyQueue = [];
        var isReady = false;
        document.addEventListener('DOMContentLoaded', function () {
            isReady = true;
            readyQueue.splice(0).forEach(function (fn) { window.setTimeout(fn); });
        });
        window.esReady = function (fn) {
            if (isReady) {
                window.setTimeout(fn);
            } else {
                readyQueue.push(fn);
            }
        };

        var defaultDisplays = {};
        function defaultDisplay(el) {
            var name = el.nodeName;
            if (defaultDisplays[name]) {
                return defaultDisplays[name];
            }
            var probe = el.ownerDocument.body.appendChild(el.ownerDocument.createElement(name));
            var display = getComputedStyle(probe).display;
            probe.parentNode.removeChild(probe);
            if (display === 'none') {
                display = 'block';
            }
            defaultDisplays[name] = display;
            return display;
        }
        function hiddenWithinTree(el) {
            return el.style.display === 'none'
                || (el.style.display === '' && el.isConnected && getComputedStyle(el).display === 'none');
        }
        window.esShow = function (el) {
            if (!el || !el.style) {
                return;
            }
            var value = null;
            if (el.style.display === 'none') {
                value = el.__esDisplay || null;
                if (!value) {
                    el.style.display = '';
                }
            }
            if (el.style.display === '' && hiddenWithinTree(el)) {
                value = defaultDisplay(el);
            }
            if (value != null) {
                el.style.display = value;
            }
        };
        window.esHide = function (el) {
            if (!el || !el.style) {
                return;
            }
            var display = el.style.display;
            if (display !== 'none') {
                el.__esDisplay = display;
                el.style.display = 'none';
            }
        };

        function optionValue(option) {
            var value = option.getAttribute('value');
            return value != null ? value : (option.textContent.match(/[^\x20\t\r\n\f]+/g) || []).join(' ');
        }
        window.esVal = function (el) {
            if (!el) {
                return undefined;
            }
            if (el.nodeName === 'SELECT') {
                var options = el.options, index = el.selectedIndex, one = el.type === 'select-one';
                var values = one ? null : [], max = one ? index + 1 : options.length;
                for (var i = index < 0 ? max : (one ? index : 0); i < max; i++) {
                    var option = options[i];
                    if ((option.selected || i === index) && !option.disabled
                        && (!option.parentNode.disabled || option.parentNode.nodeName !== 'OPTGROUP')) {
                        if (one) {
                            return optionValue(option);
                        }
                        values.push(optionValue(option));
                    }
                }
                return values;
            }
            var value = el.value;
            return typeof value === 'string' ? value.replace(/\r/g, '') : (value == null ? '' : value);
        };
        window.esSetVal = function (el, value) {
            if (!el || el.nodeType !== 1) {
                return;
            }
            if (value == null) {
                value = '';
            } else if (typeof value === 'number') {
                value += '';
            } else if (Array.isArray(value)) {
                value = value.map(function (v) { return v == null ? '' : v + ''; });
            }
            if (el.nodeName === 'SELECT') {
                var wanted = Array.isArray(value) ? value : [value];
                var chosen = false, options = el.options, i = options.length;
                while (i--) {
                    if ((options[i].selected = wanted.indexOf(optionValue(options[i])) > -1)) {
                        chosen = true;
                    }
                }
                if (!chosen) {
                    el.selectedIndex = -1;
                }
                return;
            }
            el.value = value;
        };

        window.esPost = function (url, data, success, error) {
            var headers = {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'Accept': '*/*',
            };
            {{-- Only to this site, as jQuery did: elsewhere the header would ask for a preflight. --}}
            if (new URL(url, window.location.href).origin === window.location.origin) {
                headers['X-Requested-With'] = 'XMLHttpRequest';
            }
            var body = Object.keys(data).map(function (key) {
                var value = data[key];
                return encodeURIComponent(key) + '=' + encodeURIComponent(value == null ? '' : value);
            }).join('&');
            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: headers,
                body: body,
            }).then(function (response) {
                response.text().then(function (text) {
                    var ok = response.status >= 200 && response.status < 300 || response.status === 304;
                    if (!ok) {
                        error(response, 'error', response.statusText);
                        return;
                    }
                    if (response.status === 204) {
                        success(undefined, 'nocontent', response);
                        return;
                    }
                    var parsed = text;
                    if (/\bjson\b/.test(response.headers.get('Content-Type') || '')) {
                        try {
                            parsed = JSON.parse(text);
                        } catch (e) {
                            error(response, 'parsererror', e);
                            return;
                        }
                    }
                    success(parsed, 'success', response);
                }, function () {
                    error(response, 'error', '');
                });
            }, function () {
                error(null, 'error', '');
            });
        };
    })();
    </script>
    <script type="text/javascript" src="{{ asset('js/toastify-js.js') }}" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>window._ssI18n={noResults:@json(__('messages.no_results_found'))};</script>
    <script src="{{ asset('js/searchable-select.js') }}" defer {!! nonce_attr() !!}></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/toastify.min.css') }}">

    @include('partials.consent-state')
    @include('partials.google-analytics')

    <script {!! nonce_attr() !!}>
    {{-- The pop-up menus. $toggle is the button that was pressed: the shared click handler below
         passes it, and ticket/sales passes an event of its own whose currentTarget is the button. --}}
    function onPopUpClick(id, event, toggle) {
        event.stopPropagation();
        {{-- A plain id is looked up directly, anything else as a selector, as $('#' + id) did: an
             empty one throws rather than quietly closing whatever is open. --}}
        var menu = /^[\w-]+$/.test(id) ? document.getElementById(id) : document.querySelector('#' + id);
        {{-- jQuery's :hidden - no box at all. A menu that is not on the page is not hidden either,
             so a toggle for one closes whatever is open, as it always did. --}}
        if (menu && !(menu.offsetWidth || menu.offsetHeight || menu.getClientRects().length)) {
            // Get button position
            var button = toggle || event.currentTarget;
            var rect = button.getBoundingClientRect();

            // Position dropdown with fixed positioning
            var isRtl = document.documentElement.dir === 'rtl';
            menu.style.position = 'fixed';
            menu.style.top = (rect.bottom + 4) + 'px';
            menu.style.zIndex = '1000';
            if (isRtl) {
                menu.style.left = rect.left + 'px';
                menu.style.right = 'auto';
            } else {
                menu.style.right = (window.innerWidth - rect.right) + 'px';
                menu.style.left = 'auto';
            }

            // The class, not an inline display: event/show-guest toggles these same menus with
            // classList.toggle('hidden'). Once an inline display exists the class can never win
            // again, so a single Escape press - hidePopUp is bound globally on keydown - used to
            // kill "Add to Calendar" for the rest of the session. Every .pop-up-menu in the app
            // ships with the hidden class.
            menu.classList.remove('hidden');
            menu.style.display = '';

            // Kept on the screen. A menu hangs from its button's end edge, which is right for a
            // button at the end of a row and puts the menu past the viewport for one at the start
            // of a narrow row: the dashboard's phone menu opened off the left edge, with New
            // schedule and Customize out of reach. Measured after it is shown, since a hidden box
            // has no width; a menu that already fits is left exactly where it was.
            var box = menu.getBoundingClientRect();
            var gutter = 8;
            if (box.left < gutter) {
                menu.style.left = Math.max(gutter, Math.min(rect.left, window.innerWidth - box.width - gutter)) + 'px';
                menu.style.right = 'auto';
            } else if (box.right > window.innerWidth - gutter) {
                menu.style.right = Math.max(gutter, Math.min(window.innerWidth - rect.right, window.innerWidth - box.width - gutter)) + 'px';
                menu.style.left = 'auto';
            }

            // Several toggles ship a hardcoded aria-expanded that never changes. Keep it honest
            // here rather than per call site. Both attribute spellings are handled: most toggles
            // use data-popup-target, but ticket/sales.blade.php delegates a second family through
            // data-popup-toggle, and setting the attribute on open without clearing it on close
            // would strand those permanently expanded.
            button.setAttribute('aria-expanded', 'true');
            documentClicks.push(hidePopUp);
        } else {
            hidePopUp();
        }
    }

    function hidePopUp() {
        document.querySelectorAll('.pop-up-menu').forEach(function (menu) {
            menu.classList.add('hidden');
            menu.style.display = '';
        });
        document.querySelectorAll('[data-popup-target][aria-expanded="true"], [data-popup-toggle][aria-expanded="true"]').forEach(function (toggle) {
            toggle.setAttribute('aria-expanded', 'false');
        });
        documentClicks = documentClicks.filter(function (fn) { return fn !== hidePopUp; });
    }

    {{-- Who hears a click, in the order jQuery used to call them, because what a menu does depends
         on it. First the rules below, element by element from the one clicked outwards (each
         element's matching rules in the order listed), then what is bound to the document itself
         (hidePopUp, while a menu is open). The walk ends as soon as anything stops the event's
         propagation: that is how a click inside a menu, or on a toggle, never closes it. --}}
    var documentClicks = [];
    var documentClickRules = [
        ['.pop-up-menu', function (event) {
            event.stopPropagation();
        }],
        ['.popup-toggle[data-popup-target]', function (event, element) {
            onPopUpClick(element.getAttribute('data-popup-target'), event, element);
        }],
        ['div[data-popup-target]', function (event, element) {
            onPopUpClick(element.getAttribute('data-popup-target'), event, element);
        }],
    ];

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            hidePopUp();
        }
    });

    document.addEventListener('click', function (e) {
        var stopped = false;
        var stoppedNow = false;
        e.stopPropagation = function () {
            stopped = true;
            Event.prototype.stopPropagation.call(e);
        };
        e.stopImmediatePropagation = function () {
            stopped = stoppedNow = true;
            Event.prototype.stopImmediatePropagation.call(e);
        };

        var queue = [];
        {{-- Not for a middle or right click, and a disabled element has no rules, as with jQuery. --}}
        if (!(e.button >= 1)) {
            for (var element = e.target; element && element !== document; element = element.parentNode || document) {
                if (element.nodeType !== 1 || element.disabled === true) {
                    continue;
                }
                var matched = documentClickRules.filter(function (rule) { return element.matches(rule[0]); });
                if (matched.length) {
                    queue.push([element, matched.map(function (rule) { return rule[1]; })]);
                }
            }
        }
        if (documentClicks.length) {
            queue.push([document, documentClicks.slice()]);
        }

        for (var i = 0; i < queue.length && !stopped; i++) {
            for (var j = 0; j < queue[i][1].length && !stoppedNow; j++) {
                queue[i][1][j](e, queue[i][0]);
            }
        }
    });
    </script>

    <script {!! nonce_attr() !!}>
    // CSP-safe delegated handlers. The nonce-based CSP blocks inline on* attributes (script-src-attr),
    // so these replace onclick/onsubmit/onchange across the app. Capture phase ('true') is required so
    // handlers fire even for elements inside .pop-up-menu, whose bubble-phase click handler above calls
    // stopPropagation().
    (function () {
        // data-confirm on a <form>: gate submission behind window.confirm(). This is the single
        // source of truth for data-confirm app-wide; the former per-page .form-confirm (role/show-admin),
        // payments, and google-calendar confirm handlers were consolidated into this block.
        // Skip .js-confirm-form: those ~11 pages still keep their own per-page submit handler, so
        // matching them here too would show the confirm dialog twice.
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (form && form.matches && form.matches('form[data-confirm]:not(.js-confirm-form)')) {
                if (! window.confirm(form.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            }
        }, true);

        // data-submit-on-load on a form: submit it as soon as the DOM is ready.
        //
        // For gateways whose checkout takes a signed POST rather than a redirect (Payfast's Custom
        // integration), so the interstitial is invisible in practice. The form keeps a real submit
        // button as the no-JS fallback, and living in this nonce'd block means no CSP exception.
        //
        // MUST wait for DOMContentLoaded: this script block is in <head>, so a bare querySelector
        // here runs before <body> exists and finds nothing - which is exactly the bug the first
        // version shipped with, leaving every buyer to click the fallback button by hand.
        document.addEventListener('DOMContentLoaded', function () {
            var pending = document.querySelector('form[data-submit-on-load]');
            if (pending) pending.submit();
        });

        // data-print on a button: print the page. A delegated handler rather than an inline
        // onclick, which the CSP blocks.
        document.addEventListener('click', function (e) {
            if (e.target.closest && e.target.closest('[data-print]')) {
                window.print();
            }
        }, true);

        // data-auto-submit on a control (e.g. <select>): submit its form on change
        document.addEventListener('change', function (e) {
            if (! e.target.closest) return;
            var el = e.target.closest('[data-auto-submit]');
            if (! el) return;
            var form = el.form || el.closest('form');
            if (form) form.submit();
        }, true);

        document.addEventListener('click', function (e) {
            if (! e.target.closest) return;

            // data-confirm on a non-form trigger (button/link). A click inside a form[data-confirm]
            // resolves to the form here, which is handled by the submit listener above - so skip it.
            var confirmEl = e.target.closest('[data-confirm]');
            if (confirmEl && confirmEl.tagName !== 'FORM') {
                if (! window.confirm(confirmEl.getAttribute('data-confirm'))) {
                    e.preventDefault();
                    return;
                }
            }

            // data-modal-open="NAME": open the x-modal component with that name. It is Alpine and
            // listens for an 'open-modal' window event, so a plain CustomEvent reaches it without
            // this call site having to declare an Alpine island of its own (and without an inline
            // handler, which CSP blocks).
            //
            // Do not write the component's tag form in this comment: Blade compiles component tags
            // even inside JavaScript comments, and an unclosed one silently produces invalid PHP.
            var modalTrigger = e.target.closest('[data-modal-open]');
            if (modalTrigger) {
                e.preventDefault();
                window.dispatchEvent(new CustomEvent('open-modal', {
                    detail: modalTrigger.getAttribute('data-modal-open'),
                }));
                return;
            }

            // Generic collapse: data-collapse="next|parent-next|child" (+ data-collapse-child, data-arrow)
            var collapse = e.target.closest('[data-collapse]');
            if (collapse) {
                var mode = collapse.getAttribute('data-collapse');
                var panel = null;
                if (mode === 'next') {
                    panel = collapse.nextElementSibling;
                } else if (mode === 'parent-next') {
                    var parentDiv = collapse.closest('div');
                    panel = parentDiv ? parentDiv.nextElementSibling : null;
                } else if (mode === 'child') {
                    var childSel = collapse.getAttribute('data-collapse-child');
                    panel = childSel ? collapse.parentElement.querySelector(childSel) : null;
                }
                if (panel) panel.classList.toggle('hidden');
                var arrowSel = collapse.getAttribute('data-arrow');
                if (arrowSel) {
                    var arrow = collapse.querySelector(arrowSel);
                    if (arrow) arrow.classList.toggle('rotate-90');
                }
            }

            // Explicit toggle: data-toggle-target="#id" toggles .hidden on the target element
            var toggle = e.target.closest('[data-toggle-target]');
            if (toggle) {
                var target = document.querySelector(toggle.getAttribute('data-toggle-target'));
                if (target) target.classList.toggle('hidden');
            }
        }, true);
    })();
    </script>

    <script {!! nonce_attr() !!}>
        window.appLocale = "{{ app()->getLocale() }}";
        window.editorTranslations = {
            bold: @json(__('messages.editor_bold')),
            italic: @json(__('messages.editor_italic')),
            heading: @json(__('messages.editor_heading')),
            link: @json(__('messages.editor_link')),
            quote: @json(__('messages.editor_quote')),
            unorderedList: @json(__('messages.editor_unordered_list')),
            orderedList: @json(__('messages.editor_ordered_list')),
            preview: @json(__('messages.editor_preview')),
            guide: @json(__('messages.editor_guide')),
            textDirection: @json(__('messages.editor_text_direction')),
            write: @json(__('messages.editor_write')),
            previewTab: @json(__('messages.preview')),
            markdownNote: @json(__('messages.editor_markdown_note')),
            formattingHelp: @json(__('messages.editor_formatting_help'))
        };
    </script>

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    ])

    <style {!! nonce_attr() !!}>
        .rtl {
            direction: rtl;
            text-align: right;
        }
        
        [dir="rtl"] select,
        select.rtl,
        .rtl select {
            text-align: right;
            direction: rtl;
            background-position: left 0.5rem center;
            padding-left: 2.5rem;
            padding-right: 0.75rem;
        }

        [dir="rtl"] select option,
        select.rtl option,
        .rtl select option {
            text-align: right;
            direction: rtl;
        }
        
        /* RTL flex direction adjustments */
        .rtl .space-x-3 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }
        .rtl .space-x-10 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }
        .rtl .space-x-8 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }
        
        /* RTL text alignment fixes */
        .rtl .text-left { text-align: right; }
        .rtl .lg\\:text-left { text-align: right; }

        /* RTL positioning adjustments */
        .rtl .right-4 { right: auto; left: 1rem; }
        .rtl .absolute.right-0 { right: auto; left: 0; }

        /* RTL flex justification adjustments */
        .rtl .justify-start { justify-content: flex-end; }
        .rtl .justify-end { justify-content: flex-start; }
        .rtl .lg\\:justify-start { justify-content: flex-end; }
        .rtl .lg\\:justify-end { justify-content: flex-start; }
        .rtl .sm\\:justify-end { justify-content: flex-start; }


        /* EasyMDE Toolbar Fixes */
        .editor-toolbar {
            background-color: #f8f9fa !important; /* Temporarily change to light gray for debugging */
            border-bottom: 1px solid #d1d5db !important;
        }

        .editor-toolbar button,
        .editor-toolbar a,
        .editor-toolbar .fa,
        .editor-toolbar i {
            color: #374151 !important;
            background-color: transparent !important;
            border: none !important;
            text-shadow: 0 0 1px rgba(0,0,0,0.5) !important; /* Add text shadow for visibility */
        }

        .editor-toolbar button:hover,
        .editor-toolbar a:hover {
            background-color: #f3f4f6 !important;
            color: #111827 !important;
        }

        .editor-toolbar button.active,
        .editor-toolbar a.active {
            background-color: #e5e7eb !important;
            color: #111827 !important;
        }

        .editor-toolbar .separator {
            border-left: 1px solid #d1d5db !important;
            border-right: none !important;
            color: transparent !important;
            font-size: 0 !important;
            width: 1px !important;
            height: 18px !important;
            margin: 0 8px !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }

        /* Additional EasyMDE icon fixes */
        .editor-toolbar button:before,
        .editor-toolbar a:before {
            color: #374151 !important;
            text-shadow: 0 0 1px rgba(0,0,0,0.5) !important;
        }

        .editor-toolbar button:hover:before,
        .editor-toolbar a:hover:before {
            color: #111827 !important;
        }

        /* More specific EasyMDE fixes for different icon types */
        .CodeMirror .editor-toolbar > * {
            color: #374151 !important;
        }

        .editor-toolbar > * {
            color: #374151 !important;
        }

        .editor-toolbar > button > i,
        .editor-toolbar > a > i {
            color: #374151 !important;
        }

        /* Force visibility of all toolbar elements */
        .editor-toolbar > * {
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Style text-based buttons */
        .editor-toolbar .editor-button-text {
            font-weight: bold !important;
            font-size: 14px !important;
            padding: 6px 8px !important;
            min-width: 24px !important;
            text-align: center !important;
            display: inline-block !important;
        }

        /* EasyMDE Dark Mode Styles - Using custom Tailwind colors */
        .dark .editor-toolbar {
            background-color: rgb(var(--ap-bg)) !important; /* gray-900 */
            border: none !important;
        }

        .dark .editor-toolbar button,
        .dark .editor-toolbar a,
        .dark .editor-toolbar .fa,
        .dark .editor-toolbar i {
            color: #d4d4d4 !important; /* gray-300 */
        }

        .dark .editor-toolbar button:hover,
        .dark .editor-toolbar a:hover {
            background-color: rgb(var(--ap-bg)) !important; /* gray-900 */
            color: #f9f9f9 !important; /* gray-50 */
        }

        .dark .editor-toolbar button.active,
        .dark .editor-toolbar a.active {
            background-color: rgb(var(--ap-border-strong)) !important; /* gray-600 */
            color: #f9f9f9 !important; /* gray-50 */
        }

        .dark .editor-toolbar .separator {
            border-left: 1px solid rgb(var(--ap-border-strong)) !important; /* gray-600 */
        }

        .dark .editor-toolbar button:before,
        .dark .editor-toolbar a:before {
            color: #d4d4d4 !important; /* gray-300 */
        }

        .dark .editor-toolbar button:hover:before,
        .dark .editor-toolbar a:hover:before {
            color: #f9f9f9 !important; /* gray-50 */
        }

        .dark .CodeMirror .editor-toolbar > * {
            color: #d4d4d4 !important; /* gray-300 */
        }

        .dark .editor-toolbar > * {
            color: #d4d4d4 !important; /* gray-300 */
        }

        .dark .editor-toolbar > button > i,
        .dark .editor-toolbar > a > i {
            color: #d4d4d4 !important; /* gray-300 */
        }

        /* The editor's body is a field, and is filled and edged like one (x-text-input in a dark
           palette: gray-900 on a gray-700 line). It had the card's own surface and no border, so
           on a card it was invisible: a toolbar floating over nothing. */
        .dark .CodeMirror {
            background-color: rgb(var(--ap-gray-900)) !important;
            color: #f9f9f9 !important; /* gray-50 */
            border: 1px solid rgb(var(--ap-gray-700)) !important;
        }

        .dark .CodeMirror-cursor {
            border-color: #f9f9f9 !important; /* gray-50 */
        }

        .dark .CodeMirror-selected {
            background-color: rgb(var(--ap-border-strong)) !important; /* gray-600 */
        }

        .dark .CodeMirror-line::selection,
        .dark .CodeMirror-line > span::selection,
        .dark .CodeMirror-line > span > span::selection {
            background-color: rgb(var(--ap-border-strong)) !important; /* gray-600 */
        }

        .dark .CodeMirror-gutters {
            background-color: rgb(var(--ap-border)) !important; /* gray-700 */
            border-right: 1px solid rgb(var(--ap-border-strong)) !important; /* gray-600 */
        }

        .dark .CodeMirror-linenumber {
            color: #737373 !important; /* gray-500 */
        }

        .dark .CodeMirror-focused .CodeMirror-selected {
            background-color: rgb(var(--ap-border-strong)) !important; /* gray-600 */
        }

        .dark .EasyMDEContainer {
            border: 1px solid rgb(var(--ap-border)) !important; /* gray-700 - match form inputs */
            border-radius: 0.5rem !important; /* rounded-lg */
        }

        .dark .EasyMDEContainer .CodeMirror,
        .dark .EasyMDEContainer .CodeMirror-focused {
            border: 1px solid rgb(var(--ap-gray-700)) !important;
            border-top: 0 !important;
        }

    </style>

    {{-- The markdown-output rules live in a partial so the standalone legal-page
         layout can reuse them; kept in place here so the cascade is unchanged. --}}
    @include('partials.custom-content-styles')

    {{-- The admin portal's fields (below) or the guest pages' own: the theme variants are the
         portal's opt-in, never a guest page's (CLAUDE.md, "Theme variants are opt-in per layout"). --}}
    @php
        $apFields = (bool) ($themeVariants ?? false);
    @endphp
    <style {!! nonce_attr() !!}>

        .cm-s-easymde .cm-header-1 { font-size: 1.8rem !important; }
        .cm-s-easymde .cm-header-2 { font-size: 1.55rem !important; }
        .cm-s-easymde .cm-header-3 { font-size: 1.3rem !important; }
        .cm-s-easymde .cm-header-4,
        .cm-s-easymde .cm-header-5,
        .cm-s-easymde .cm-header-6 { font-size: 1.15rem !important; }

        /* RTL editor support. The editing area direction is handled by CodeMirror's
           own `direction` option (set in editor-helpers.js); here we only flip the
           rendered preview. The toolbar is intentionally left to inherit the page
           direction so it never flips based on content. */
        .EasyMDEContainer.editor-rtl .editor-preview,
        .EasyMDEContainer.editor-rtl .editor-preview-side {
            direction: rtl;
            text-align: right;
        }

        {{-- Fields as the 2026-11 redesign draws them, in the admin portal and its onboarding
             (the pages that opt into the theme variants: layouts/app-admin, /getting-started,
             the first schedule's form): 16px type, 52px tall, 12px corners, the palette's strong
             border, and on focus the design-language review's blue border and 3px ring
             (--ap-focus-ring, which the buttons share). The corners and the border are said
             without !important, so a field that is meant to be a pill (rounded-full) or
             borderless keeps its own. A guest page shares this shell and keeps the fields it had:
             these selectors outrank its own (.gk-input's radius, .gk-pg-input's focus in the
             schedule's accent), so there they would paint the platform's blue over the owner's
             colours. --}}
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="url"],
        select,
        textarea {
            transition: border-color 0.2s, box-shadow 0.2s;
            font-size: {{ $apFields ? '1rem' : '1.15rem' }} !important;
            line-height: 1.5 !important;
            padding: {{ $apFields ? '0.8125rem 0.875rem' : '0.75rem 1rem' }} !important;
            font-family: inherit !important;
        }
        @if ($apFields)
        input:is([type="text"], [type="email"], [type="password"], [type="number"], [type="url"], [type="tel"], [type="search"]):not(.rounded-full):not(.rounded-none),
        select:not(.rounded-full),
        textarea:not(.rounded-none) {
            border-radius: 0.75rem;
        }
        input:is([type="text"], [type="email"], [type="password"], [type="number"], [type="url"], [type="tel"], [type="search"]):focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--brand-blue);
            box-shadow: 0 0 0 3px var(--ap-focus-ring, rgba(36, 86, 230, 0.18));
        }
        @endif

        /* A phone field is the size of the fields beside it. It has a rule of its own because the
           country widget (intl-tel-input) makes room for its flag and dial code with an inline
           padding on one side: an !important on the sides would overrule that, and the number
           would be typed under the flag. So only top and bottom are said firmly. */
        input[type="tel"] {
            transition: border-color 0.2s, box-shadow 0.2s;
            font-size: {{ $apFields ? '1rem' : '1.15rem' }} !important;
            line-height: 1.5 !important;
            padding-block: {{ $apFields ? '0.8125rem' : '0.75rem' }} !important;
            padding-inline: 1rem;
            font-family: inherit !important;
        }
        /* The dial code is read as part of the number, so it is the number's size. */
        .iti__selected-dial-code {
            font-size: {{ $apFields ? '1rem' : '1.15rem' }};
            line-height: 1.5;
        }
        /* The flag is a button, and a page's own rule for its buttons (the event form's
           min-width: 100px) stretched it: the widget measures that button to place the number,
           so the number began a third of the way along the field. */
        .iti__selected-country {
            min-width: 0;
            min-height: 0;
        }
        /* The widget zeroes its field's margin, so the gap under the label is given back on the
           wrapper: without it the field sits a little above the one beside it. */
        .iti:has(> .iti__tel-input.mt-1) {
            margin-top: 0.25rem;
        }


    </style>

    <script {!! nonce_attr() !!}>
        esReady(function() {

            {{-- JSON_HEX_TAG: these messages can carry a schedule's name, and a "<!--<script"
                 inside a script block would otherwise swallow the rest of it.

                 $flashOnPage: the keys a page of the admin portal has already printed as a
                 notice (x-page-flash leaves them on the request). The toast for such a key
                 stands down; every other key is toasted as before. --}}
            @php $flashOnPage = request()->attributes->get('pageFlashShown', []); @endphp
            @if (session('message') && ! in_array('message', $flashOnPage, true))
            (function() {
                var key = '{{ uniqid("toast_") }}';
                if (!sessionStorage.getItem(key)) {
                    sessionStorage.setItem(key, '1');
                    Toastify({
                        text: @json(session('message'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG),
                        duration: 3000,
                        position: 'center',
                        stopOnFocus: true,
                        style: {
                            background: '#4BB543',
                        }
                    }).showToast();
                }
            })();
            {{-- subscribe_error is the audience panel's own error key, and it deliberately does
                 NOT toast: the panel renders it inline and respond() sends the visitor back to the
                 panel with a fragment, so a toast at the top of the viewport would be a second
                 notification for something already on screen. It stays a separate key from
                 session('error') because event/show-guest.blade.php force-opens the ticket form on
                 session('error'). See RoleSubscriberController::respond(). --}}
            @elseif (session('error') && ! in_array('error', $flashOnPage, true))
            (function() {
                var key = '{{ uniqid("toast_") }}';
                if (!sessionStorage.getItem(key)) {
                    sessionStorage.setItem(key, '1');
                    Toastify({
                        text: @json(session('error'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG),
                        close: true,
                        duration: 10000,
                        position: 'center',
                        stopOnFocus: true,
                        style: {
                            background: '#FF0000',
                        }
                    }).showToast();
                }
            })();
            @elseif (session('warning') && ! in_array('warning', $flashOnPage, true))
            (function() {
                var key = '{{ uniqid("toast_") }}';
                if (!sessionStorage.getItem(key)) {
                    sessionStorage.setItem(key, '1');
                    Toastify({
                        text: @json(session('warning'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG),
                        close: true,
                        duration: 8000,
                        position: 'center',
                        stopOnFocus: true,
                        style: {
                            background: '#F59E0B',
                        }
                    }).showToast();
                }
            })();
            @endif
        });

    </script>

    @include('partials.theme-script', ['variants' => $themeVariants ?? false, 'forceMode' => $forceMode ?? null, 'modeChosen' => $modeChosen ?? false, 'carryDomain' => config('app.hosted') ? _base_domain() : null, 'carryHosts' => $carryHosts ?? []])

    {{ isset($head) ? $head : '' }}

</head> 
<body class="font-sans antialiased h-full bg-gray-50 dark:bg-gray-900 overflow-x-clip">

    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-[var(--brand-button-bg)] focus:px-4 focus:py-3 focus:text-base focus:text-white focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] ltr:focus:left-4 rtl:focus:right-4">
        {{ __('accessibility.skip_to_main') }}
    </a>

    {{ $slot }}

    <div id="es-lightbox" style="display:none" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/90">
        <button id="es-lightbox-close" class="absolute top-3 ltr:right-3 rtl:left-3 text-white/80 hover:text-white text-4xl leading-none z-10 w-10 h-10 flex items-center justify-center">&times;</button>
        <img id="es-lightbox-img" class="max-w-[96vw] max-h-[90vh] object-contain pointer-events-none" src="" alt="">
    </div>

    <script {!! nonce_attr() !!}>
        (function() {
            var overlay = document.getElementById('es-lightbox');
            var img = document.getElementById('es-lightbox-img');
            var closeBtn = document.getElementById('es-lightbox-close');

            function open(src) {
                img.src = src;
                overlay.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            function close() {
                overlay.style.display = 'none';
                img.src = '';
                document.body.style.overflow = '';
            }

            document.addEventListener('click', function(e) {
                var el = e.target?.closest('[data-lightbox-src]');
                if (!el) return;
                var src = el.getAttribute('data-lightbox-src') || el.src;
                if (src && src !== '#' && !src.endsWith('#') && src !== window.location.href) {
                    open(src);
                }
            });

            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) close();
            });

            closeBtn.addEventListener('click', close);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && overlay.style.display !== 'none') close();
            });

            window.showLightbox = open;
        })();
    </script>

    @include('partials.cookie-banner')

    @if ($realtime)
        @include('partials.realtime-beacon', [
            'surface' => $realtimeSurface ?: (auth()->check() ? 'ap' : 'gp'),
            'role' => $realtimeRole,
            'event' => $realtimeEvent,
        ])
    @endif

    {{-- Global operator-configured footer code (guest pages only); rendered here,
         after $slot and outside Vue's #app mount, so injected <script> tags run. --}}
    {{ isset($footCode) ? $footCode : '' }}

    {{-- @if (config('app.is_testing'))
        <style {!! nonce_attr() !!}>
            @keyframes es-ping { 0% { transform: scale(1); opacity: 0.5; } 100% { transform: scale(3); opacity: 0; } }
        </style>
        <div class="fixed bottom-4 right-4 z-50 pointer-events-none" style="width:24px;height:24px">
            <div style="position:absolute;inset:0;border-radius:9999px;background:#f97316;animation:es-ping 3s cubic-bezier(0,0,0.2,1) infinite"></div>
            <div style="position:absolute;inset:0;border-radius:9999px;background:#f97316;animation:es-ping 3s cubic-bezier(0,0,0.2,1) 1.5s infinite"></div>
            <div style="position:absolute;inset:0;border-radius:9999px;background:#f97316"></div>
        </div>
    @endif --}}

</body>
</html>
