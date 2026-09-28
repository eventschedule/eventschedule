{{--
    The guest portal's one photo viewer, for every set of photos a page shows: the organizer's
    gallery ("gallery"), fan photos ("fan") and the flyer ("flyer"). Include it once, at the end of
    the page; each set registers itself as window.EsLightboxSets[name] = [...items], and any link
    carrying data-lightbox-set opens it:

        data-lightbox-set="gallery" data-lightbox-index="3"   the fourth photo
        data-lightbox-set="gallery" data-lightbox-grid         every photo, as a grid
        data-lightbox-set="fan"                                the photo whose src is the link's href

    Every trigger is a real link to the original, so without JavaScript - or before this has booted
    - a click simply opens the image, and a modified or middle click is left to the browser.

    Item shape: {src, srcset, thumb, w, h, color, caption, credit, meta}. src is the original,
    thumb whatever the page already loaded (shown at once, then replaced by the sharp image).

    Mounted on its own element at the end of the page rather than near its triggers: the cards
    around them have a backdrop-filter, which would become the containing block of a
    position:fixed overlay. The template is a JS string; captions, credits and names are bound
    with {{ }} and :attr, never compiled.

    History: opening pushes an entry for the SAME url (state esLightbox), and the grid-then-photo
    path a second one, so a phone's Back button closes the viewer a level at a time instead of
    leaving the page. Moving between photos replaces nothing. There is no per-photo URL: a shared
    link could not preview that photo anyway (og:image cannot follow a #hash).
--}}
@php
    $lightboxLabels = [
        'viewer' => __('messages.gallery_viewer'),
        'close' => __('messages.close'),
        'previous' => __('messages.previous'),
        'next' => __('messages.next'),
        'all_photos' => __('messages.gallery_all_photos'),
        'photo_n' => __('messages.gallery_photo_n'),
        'credit' => __('messages.gallery_credit_line'),
        'zoom_in' => __('messages.gallery_zoom_in'),
        'zoom_out' => __('messages.gallery_zoom_out'),
    ];
@endphp
<div id="es-lightbox-app"></div>

<script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}"{!! nonce_attr() !!}><\/script>')</script>
<script {!! nonce_attr() !!}>
(function () {
    var L = @json($lightboxLabels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    var RTL = @json((bool) ($rtl ?? false));

    function mountLightbox() {
        var mountEl = document.getElementById('es-lightbox-app');

        if (! mountEl || typeof Vue === 'undefined' || mountEl.__es_mounted) {
            return;
        }
        mountEl.__es_mounted = true;

        // A reload while the viewer was open leaves its history entry as the current one.
        if (history.state && history.state.esLightbox) {
            try { history.replaceState(null, '', location.href); } catch (e) {}
        }

        var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        Vue.createApp({
            data: function () {
                return {
                    open: false,
                    mode: 'single',
                    setName: '',
                    items: [],
                    index: 0,
                    loaded: false,
                    scale: 1,
                    x: 0,
                    y: 0,
                    dragging: false,
                    swipeX: 0,
                    swipeY: 0,
                    announce: '',
                    L: L,
                    rtl: RTL,
                    reducedMotion: reducedMotion,
                };
            },
            computed: {
                item: function () { return this.items[this.index] || null; },
                many: function () { return this.items.length > 1; },
                counter: function () { return (this.index + 1) + ' / ' + this.items.length; },
                imageStyle: function () {
                    var swipe = this.scale === 1 ? 'translate(' + this.swipeX + 'px,' + Math.max(0, this.swipeY) + 'px) ' : '';
                    return {
                        transform: swipe + 'translate(' + this.x + 'px,' + this.y + 'px) scale(' + this.scale + ')',
                        transition: (this.dragging || this.reducedMotion) ? 'none' : 'transform 200ms ease-out',
                        opacity: this.scale === 1 && this.swipeY > 0 ? Math.max(0.3, 1 - this.swipeY / 400) : 1,
                        cursor: this.scale > 1 ? (this.dragging ? 'grabbing' : 'grab') : 'zoom-in',
                    };
                },
                sizes: function () { return this.scale > 1 ? '400vw' : '100vw'; },
            },
            watch: {
                index: function () { this.resetZoom(); this.loaded = false; this.say(); },
            },
            mounted: function () {
                this._onClick = this.onDocumentClick.bind(this);
                this._onKey = this.onKey.bind(this);
                this._onPop = this.onPop.bind(this);
                document.addEventListener('click', this._onClick);
                document.addEventListener('keydown', this._onKey);
                window.addEventListener('popstate', this._onPop);
            },
            methods: {
                t: function (text, params) {
                    var out = String(text || '');
                    Object.keys(params || {}).forEach(function (key) { out = out.split(':' + key).join(String(params[key])); });
                    return out;
                },
                say: function () {
                    if (this.mode === 'single' && this.item) {
                        this.announce = this.t(L.photo_n, { n: this.index + 1, total: this.items.length });
                    }
                },
                onDocumentClick: function (e) {
                    var link = e.target && e.target.closest ? e.target.closest('[data-lightbox-set]') : null;

                    // Anything but a plain primary click is the browser's: a new tab, a new window.
                    if (! link || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
                        return;
                    }

                    var sets = window.EsLightboxSets || {};
                    var items = sets[link.getAttribute('data-lightbox-set')];

                    if (! items || ! items.length) {
                        return;
                    }

                    e.preventDefault();

                    var index = parseInt(link.getAttribute('data-lightbox-index'), 10);
                    if (isNaN(index)) {
                        var href = link.getAttribute('href');
                        index = Math.max(0, items.findIndex(function (item) { return item.src === href; }));
                    }

                    // data-lightbox-grid-from: a grid only from that width up. The mosaic's fifth tile
                    // stands for "all photos" where the mosaic stops at five, but on a phone every
                    // photo is in the strip and it is just the fifth photo.
                    var gridFrom = link.getAttribute('data-lightbox-grid-from');
                    var grid = link.hasAttribute('data-lightbox-grid')
                        || (gridFrom !== null && window.matchMedia && window.matchMedia('(min-width: ' + parseInt(gridFrom, 10) + 'px)').matches);

                    this.show(link.getAttribute('data-lightbox-set'), items, Math.min(index, items.length - 1), grid && items.length > 1 ? 'grid' : 'single', link);
                },
                // History: this.depth is how many entries the viewer has pushed, 1 or 2. Opening
                // pushes one; picking a photo from the grid pushes a second, so Back returns to
                // the grid. Going from that photo back to the grid is a real Back, and going to
                // the grid from a photo opened directly replaces its entry, so the depth never
                // passes 2 and closing always takes exactly the entries the viewer added.
                show: function (name, items, index, mode, trigger) {
                    if (this.open) {
                        return;
                    }
                    this.setName = name;
                    this.items = items;
                    this.index = index;
                    this.loaded = false;
                    this.resetZoom();
                    this.trigger = trigger;
                    this.open = true;
                    this.closing = false;
                    document.body.style.overflow = 'hidden';
                    this.depth = 0;
                    try { history.pushState({ esLightbox: mode }, '', location.href); this.depth = 1; } catch (e) {}
                    this.mode = mode;
                    this.say();
                    this.$nextTick(this.focusFirst);
                },
                pick: function (index) {
                    this.index = index;
                    this.loaded = false;
                    this.mode = 'single';
                    if (this.depth === 1) {
                        try { history.pushState({ esLightbox: 'single' }, '', location.href); this.depth = 2; } catch (e) {}
                    }
                    this.say();
                    this.$nextTick(this.focusFirst);
                },
                showGrid: function () {
                    this.resetZoom();
                    if (this.depth === 2) {
                        // This photo was picked from the grid: go back to it. onPop switches mode.
                        history.back();
                        return;
                    }
                    this.mode = 'grid';
                    try { history.replaceState({ esLightbox: 'grid' }, '', location.href); } catch (e) {}
                    this.$nextTick(this.focusFirst);
                },
                close: function () {
                    // A second press (or a held Escape) while the first is still travelling back
                    // must not go further than the viewer's own entries, off the page.
                    if (this.closing) {
                        return;
                    }
                    if (this.depth > 0) {
                        this.closing = true;
                        history.go(-this.depth);
                    } else {
                        this.hide();
                    }
                },
                hide: function () {
                    this.open = false;
                    this.closing = false;
                    this.depth = 0;
                    this.resetZoom();
                    document.body.style.overflow = '';
                    var trigger = this.trigger;
                    this.trigger = null;
                    if (trigger && trigger.focus) {
                        try { trigger.focus({ preventScroll: true }); } catch (e) {}
                    }
                },
                onPop: function (e) {
                    if (! this.open) {
                        return;
                    }
                    var state = e.state && e.state.esLightbox;
                    if (state && ! this.closing) {
                        // Back from a photo picked in the grid: the grid, one entry deep.
                        this.mode = state;
                        this.depth = 1;
                        this.resetZoom();
                        this.$nextTick(this.focusFirst);
                    } else {
                        this.hide();
                    }
                },
                onBackdropClick: function () {
                    // The click that ends a drag lands on the backdrop whenever the pointer was let
                    // go off the photo; it is the end of the drag, not a request to close.
                    if (this.suppressClick) {
                        this.suppressClick = false;
                        return;
                    }
                    if (this.scale === 1) {
                        this.close();
                    }
                },
                go: function (delta) {
                    if (! this.many) {
                        return;
                    }
                    this.index = (this.index + delta + this.items.length) % this.items.length;
                },
                onKey: function (e) {
                    if (! this.open) {
                        return;
                    }
                    if (e.key === 'Escape') {
                        e.preventDefault();
                        this.close();
                    } else if (this.mode === 'single' && (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) {
                        e.preventDefault();
                        var forward = e.key === 'ArrowRight' ? ! this.rtl : this.rtl;
                        this.go(forward ? 1 : -1);
                    } else if (e.key === 'Tab') {
                        this.trapFocus(e);
                    }
                },
                focusables: function () {
                    var root = this.$refs.dialog;
                    return root ? Array.prototype.slice.call(root.querySelectorAll('button, [href], [tabindex]:not([tabindex="-1"])')).filter(function (el) { return ! el.disabled && el.offsetParent !== null; }) : [];
                },
                focusFirst: function () {
                    var list = this.focusables();
                    var close = this.$refs.close;
                    (close || list[0] || this.$refs.dialog || { focus: function () {} }).focus();
                },
                trapFocus: function (e) {
                    var list = this.focusables();
                    if (! list.length) {
                        return;
                    }
                    var first = list[0];
                    var last = list[list.length - 1];
                    if (e.shiftKey && document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    } else if (! e.shiftKey && document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                },

                // ---- Zoom and gestures ----------------------------------------------------

                resetZoom: function () {
                    this.scale = 1;
                    this.x = 0;
                    this.y = 0;
                    this.swipeX = 0;
                    this.swipeY = 0;
                    this.pointers = {};
                    this.dragging = false;
                },
                clamp: function () {
                    var img = this.$refs.image;
                    if (! img) {
                        return;
                    }
                    var maxX = Math.max(0, (img.offsetWidth * this.scale - img.offsetWidth) / 2);
                    var maxY = Math.max(0, (img.offsetHeight * this.scale - img.offsetHeight) / 2);
                    this.x = Math.max(-maxX, Math.min(maxX, this.x));
                    this.y = Math.max(-maxY, Math.min(maxY, this.y));
                },
                zoomAt: function (clientX, clientY, scale) {
                    var img = this.$refs.image;
                    if (! img) {
                        return;
                    }
                    var rect = img.getBoundingClientRect();
                    // The point under the finger, relative to the image's unscaled centre.
                    var px = (clientX - (rect.left + rect.width / 2)) / this.scale + 0;
                    var py = (clientY - (rect.top + rect.height / 2)) / this.scale + 0;
                    this.scale = scale;
                    this.x = scale === 1 ? 0 : -px * (scale - 1);
                    this.y = scale === 1 ? 0 : -py * (scale - 1);
                    this.clamp();
                },
                toggleZoom: function (clientX, clientY) {
                    if (this.scale > 1) {
                        this.resetZoom();
                    } else {
                        this.zoomAt(clientX, clientY, 2.5);
                    }
                },
                zoomButton: function () {
                    var img = this.$refs.image;
                    if (! img) {
                        return;
                    }
                    var rect = img.getBoundingClientRect();
                    this.toggleZoom(rect.left + rect.width / 2, rect.top + rect.height / 2);
                },
                onPointerDown: function (e) {
                    this.suppressClick = false;
                    this.pointers = this.pointers || {};
                    this.pointers[e.pointerId] = { x: e.clientX, y: e.clientY };
                    var ids = Object.keys(this.pointers);
                    // No pointer capture: it would retarget the click that follows onto the
                    // backdrop, which closes the viewer.
                    if (ids.length === 1) {
                        var onImage = !! (this.$refs.image && e.target && (e.target === this.$refs.image || this.$refs.image.parentNode.contains(e.target)));
                        this.gesture = { startX: e.clientX, startY: e.clientY, x: this.x, y: this.y, time: Date.now(), moved: false, type: e.pointerType, onImage: onImage };
                    } else if (ids.length === 2) {
                        var a = this.pointers[ids[0]];
                        var b = this.pointers[ids[1]];
                        this.pinch = { dist: Math.hypot(a.x - b.x, a.y - b.y), scale: this.scale, x: this.x, y: this.y };
                        this.swipeX = 0;
                        this.swipeY = 0;
                        if (this.gesture) { this.gesture.pinched = true; }
                    }
                    this.dragging = true;
                },
                onPointerMove: function (e) {
                    if (! this.pointers || ! this.pointers[e.pointerId]) {
                        return;
                    }
                    this.pointers[e.pointerId] = { x: e.clientX, y: e.clientY };
                    var ids = Object.keys(this.pointers);

                    if (ids.length >= 2 && this.pinch) {
                        var a = this.pointers[ids[0]];
                        var b = this.pointers[ids[1]];
                        var dist = Math.hypot(a.x - b.x, a.y - b.y);
                        var scale = Math.max(1, Math.min(4, this.pinch.scale * dist / this.pinch.dist));
                        this.x = this.pinch.x * scale / this.pinch.scale;
                        this.y = this.pinch.y * scale / this.pinch.scale;
                        this.scale = scale;
                        this.clamp();
                        if (this.gesture) { this.gesture.moved = true; }
                        return;
                    }

                    var g = this.gesture;
                    if (! g) {
                        return;
                    }
                    var dx = e.clientX - g.startX;
                    var dy = e.clientY - g.startY;
                    if (Math.abs(dx) > 6 || Math.abs(dy) > 6) {
                        g.moved = true;
                    }
                    if (this.scale > 1) {
                        this.x = g.x + dx;
                        this.y = g.y + dy;
                        this.clamp();
                    } else if (g.type !== 'mouse') {
                        // A finger drags the photo along, so the swipe is felt before it lands.
                        if (Math.abs(dy) > Math.abs(dx) && dy > 0) {
                            this.swipeY = dy;
                            this.swipeX = 0;
                        } else if (this.many) {
                            this.swipeX = dx;
                            this.swipeY = 0;
                        }
                    }
                },
                onPointerUp: function (e) {
                    if (! this.pointers) {
                        return;
                    }
                    delete this.pointers[e.pointerId];
                    var remainingIds = Object.keys(this.pointers);
                    if (remainingIds.length > 0) {
                        // One finger of a pinch lifted: carry on from where the other one is, so
                        // its next move pans from here rather than from where the first finger
                        // started, and so letting go of it is never read as a swipe or a close.
                        var rest = this.pointers[remainingIds[0]];
                        this.pinch = null;
                        this.gesture = { startX: rest.x, startY: rest.y, x: this.x, y: this.y, time: Date.now(), moved: true, type: e.pointerType, onImage: true, pinched: true };
                        return;
                    }
                    this.dragging = false;
                    this.pinch = null;
                    var g = this.gesture;
                    this.gesture = null;
                    if (! g) {
                        return;
                    }

                    var dx = e.clientX - g.startX;
                    var dy = e.clientY - g.startY;

                    if (g.moved) {
                        this.suppressClick = true;
                    }

                    if (g.pinched) {
                        this.swipeX = 0;
                        this.swipeY = 0;
                        return;
                    }

                    if (! g.moved) {
                        // A tap on the backdrop is the backdrop's click, which closes the viewer.
                        if (! g.onImage) {
                            return;
                        }
                        // A tap. A mouse click zooms straight away; a finger needs a double tap,
                        // so a single one never zooms by accident.
                        if (g.type === 'mouse') {
                            this.toggleZoom(e.clientX, e.clientY);
                        } else {
                            var now = Date.now();
                            if (this.lastTap && now - this.lastTap.time < 320 && Math.hypot(e.clientX - this.lastTap.x, e.clientY - this.lastTap.y) < 30) {
                                this.toggleZoom(e.clientX, e.clientY);
                                this.lastTap = null;
                            } else {
                                this.lastTap = { time: now, x: e.clientX, y: e.clientY };
                            }
                        }
                        return;
                    }

                    if (this.scale === 1 && g.type !== 'mouse') {
                        if (dy > 90 && Math.abs(dy) > Math.abs(dx)) {
                            this.swipeY = 0;
                            this.close();
                            return;
                        }
                        if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) {
                            var forward = dx < 0 ? ! this.rtl : this.rtl;
                            this.swipeX = 0;
                            this.go(forward ? 1 : -1);
                            return;
                        }
                    }
                    this.swipeX = 0;
                    this.swipeY = 0;
                },
            },
            template: `
<div v-if="open" ref="dialog" tabindex="-1" role="dialog" aria-modal="true" :aria-label="L.viewer"
    class="fixed inset-0 z-[70] flex flex-col bg-black/95 text-white outline-none"
    style="font-family: ui-sans-serif, system-ui, sans-serif; padding: env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left);">
    <p class="sr-only" aria-live="polite">@{{ announce }}</p>

    <div class="flex h-14 shrink-0 items-center justify-between gap-2 px-2 sm:px-4">
        <div class="flex min-w-0 items-center gap-2">
            <span v-if="mode === 'single' && many" dir="ltr" class="px-2 text-sm tabular-nums text-white/75">@{{ counter }}</span>
            <span v-else-if="mode === 'grid'" class="px-2 text-sm font-medium text-white/85">@{{ L.all_photos }}</span>
        </div>
        <div class="flex items-center gap-1">
            <button v-if="mode === 'single' && many" type="button" @click="showGrid" :aria-label="L.all_photos" :title="L.all_photos"
                class="flex h-11 w-11 items-center justify-center rounded-full text-white/80 hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
            </button>
            <button v-if="mode === 'single'" type="button" @click="zoomButton" :aria-label="scale > 1 ? L.zoom_out : L.zoom_in" :title="scale > 1 ? L.zoom_out : L.zoom_in"
                class="hidden sm:flex h-11 w-11 items-center justify-center rounded-full text-white/80 hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                <svg v-if="scale > 1" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM13.5 10.5h-6" /></svg>
                <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" /></svg>
            </button>
            <button ref="close" type="button" @click="close" :aria-label="L.close" :title="L.close"
                class="flex h-11 w-11 items-center justify-center rounded-full text-white/80 hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </div>

    <div v-if="mode === 'grid'" class="flex-1 overflow-y-auto px-2 pb-6 sm:px-6">
        <div class="mx-auto grid max-w-6xl grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
            <button v-for="(entry, i) in items" :key="i" type="button" @click="pick(i)" :aria-label="t(L.photo_n, { n: i + 1, total: items.length }) + (entry.caption ? ': ' + entry.caption : '')"
                class="group relative aspect-square overflow-hidden rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                :style="{ backgroundColor: entry.color || '#1f2937' }">
                <img :src="entry.thumb || entry.src" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover motion-safe:transition-transform motion-safe:duration-300 motion-safe:group-hover:scale-105">
            </button>
        </div>
    </div>

    <template v-else-if="item">
        <div class="relative flex min-h-0 flex-1 items-center justify-center overflow-hidden"
            style="touch-action: none;"
            @pointerdown="onPointerDown" @pointermove="onPointerMove" @pointerup="onPointerUp" @pointercancel="onPointerUp"
            @click.self="onBackdropClick">
            <div class="relative flex max-h-full max-w-full items-center justify-center" :style="imageStyle">
                <img v-if="item.thumb && !loaded" :src="item.thumb" alt="" aria-hidden="true"
                    class="pointer-events-none absolute inset-0 m-auto max-h-[calc(100dvh-9rem)] max-w-[100vw] object-contain select-none sm:max-w-[92vw]"
                    :style="{ width: item.w ? item.w + 'px' : null }" draggable="false">
                <img ref="image" :key="index" :src="item.src" :srcset="item.srcset || null" :sizes="item.srcset ? sizes : null"
                    :width="item.w || null" :height="item.h || null"
                    :alt="item.caption || ''" @load="loaded = true" draggable="false"
                    class="relative max-h-[calc(100dvh-9rem)] max-w-[100vw] select-none object-contain sm:max-w-[92vw]"
                    :class="loaded ? 'opacity-100' : 'opacity-0'"
                    style="height: auto; width: auto; transition: opacity 200ms ease-out;">
            </div>

            <button v-if="many" type="button" @click.stop="go(rtl ? 1 : -1)" @pointerdown.stop :aria-label="rtl ? L.next : L.previous"
                class="absolute left-2 top-1/2 hidden -translate-y-1/2 sm:flex h-11 w-11 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </button>
            <button v-if="many" type="button" @click.stop="go(rtl ? -1 : 1)" @pointerdown.stop :aria-label="rtl ? L.previous : L.next"
                class="absolute right-2 top-1/2 hidden -translate-y-1/2 sm:flex h-11 w-11 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </button>
        </div>

        <div class="min-h-[4rem] shrink-0 px-4 pb-3 pt-2 sm:px-8">
            <div v-if="item.caption || item.credit || item.meta" class="mx-auto flex max-w-3xl flex-wrap items-baseline justify-between gap-x-6 gap-y-1 text-sm">
                <p v-if="item.caption" dir="auto" class="line-clamp-3 text-white/90">@{{ item.caption }}</p>
                <p v-if="item.credit" dir="auto" class="ms-auto text-white/60">@{{ t(L.credit, { credit: item.credit }) }}</p>
                <p v-if="item.meta" dir="auto" class="text-white/60">@{{ item.meta }}</p>
            </div>
        </div>

        <div v-if="many" class="hidden">
            <img v-for="n in [1, -1]" :key="n" :src="items[(index + n + items.length) % items.length].src" :srcset="items[(index + n + items.length) % items.length].srcset || null" sizes="100vw" alt="" loading="eager">
        </div>
    </template>
</div>`
        }).mount(mountEl);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountLightbox);
    } else {
        mountLightbox();
    }
})();
</script>
