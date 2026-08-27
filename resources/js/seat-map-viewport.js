import { onBeforeUnmount, reactive, ref } from 'vue';

/**
 * Zoom and pan for a seat map, shared by the designer, the guest picker and the box office.
 *
 * All three draw into a fixed, canvas-sized viewBox and move the content with a
 * `translate(pan) scale(zoom)` group, rather than fitting the viewBox to the content. Fitting the
 * viewBox is what letterboxed a wide, shallow room inside a tall box and left no way to get closer
 * than whatever the container happened to be: a 2,000-seat house came out at a few pixels a seat.
 *
 * The designer used to own a private half of this - buttons and a mouse-only drag, no wheel and no
 * touch - and the other two owned none of it. This is that logic, finished and shared.
 */

// How far a press may wander before it counts as a drag rather than a click.
const PAN_THRESHOLD = 3;

const MIN_ZOOM = 0.2;
const MAX_ZOOM = 3;

const clampZoom = (z) => Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, z));

/**
 * @param {object}   opts
 * @param {import('vue').Ref<SVGSVGElement|null>} opts.svgEl  the <svg> being driven
 * @param {() => ({minX:number, minY:number, w:number, h:number}|null)} opts.contentBounds
 *        bounding box of everything drawn, in content units
 * @param {() => boolean} [opts.canPan] false while something else owns the drag (the designer is
 *        moving a section, a table or a seat), so panning never fights an element drag
 * @param {boolean} [opts.panFromChildren] may a press that landed on something DRAWN start a pan?
 *        True for the guest picker and the box office, where dragging off a seat is how a finger
 *        moves the map. False for the designer, where everything drawn is itself draggable.
 */
export function useMapViewport({ svgEl, contentBounds, canPan = () => true, panFromChildren = true, wheelNeedsModifier = false }) {
    const zoom = ref(1);
    const pan = reactive({ x: 40, y: 60 });
    const canvas = reactive({ w: 900, h: 540 });

    /**
     * Has the user taken control of the viewport?
     *
     * The canvas height is derived from the content's proportions, so the element resizes shortly
     * AFTER the first fit - which left the framing computed against a stale height and the room
     * sitting low in a half-empty box. Re-fitting on resize solves that, but re-fitting after the
     * user has zoomed in on a seat would snatch the map back from under them. So: auto-fit until
     * they adjust it, then never again.
     *
     * Set by an actual pan or pinch MOVEMENT, and by the zoom controls - never by a bare press,
     * which is how a plain seat click used to switch the re-fit off.
     */
    let userAdjusted = false;

    // Live pointers, keyed by pointerId: one is a pan, two are a pinch.
    const pointers = new Map();
    let panFrom = null;
    let pinchFrom = null;

    /** Keep the unit-per-pixel mapping true when the pane resizes. */
    function measure() {
        const el = svgEl.value;
        if (!el) return;
        const r = el.getBoundingClientRect();
        if (r.width > 0 && r.height > 0) {
            canvas.w = Math.round(r.width);
            canvas.h = Math.round(r.height);
        }
    }

    /** Content units for a client point, at the current zoom and pan. */
    function toContent(clientX, clientY) {
        const el = svgEl.value;
        if (!el) return { x: 0, y: 0 };
        const r = el.getBoundingClientRect();
        // The viewBox is canvas-sized, so client pixels and viewBox units differ only by the
        // browser's own scaling of the <svg> box.
        const sx = canvas.w / (r.width || 1);
        const sy = canvas.h / (r.height || 1);
        return {
            x: ((clientX - r.left) * sx - pan.x) / zoom.value,
            y: ((clientY - r.top) * sy - pan.y) / zoom.value,
        };
    }

    /**
     * Zoom while holding one content point still under the cursor.
     *
     * Without the anchor, wheeling toward a seat in the corner walks it off screen and the user
     * has to pan back after every notch.
     */
    function zoomAt(nextZoom, clientX, clientY) {
        const z = clampZoom(nextZoom);
        if (z === zoom.value) return;

        const el = svgEl.value;
        if (!el) { zoom.value = z; return; }

        const r = el.getBoundingClientRect();
        const px = (clientX - r.left) * (canvas.w / (r.width || 1));
        const py = (clientY - r.top) * (canvas.h / (r.height || 1));
        const before = toContent(clientX, clientY);

        zoom.value = z;
        pan.x = px - before.x * z;
        pan.y = py - before.y * z;
    }

    function zoomBy(d) {
        userAdjusted = true;
        const el = svgEl.value;
        if (!el) { zoom.value = clampZoom(zoom.value + d); return; }
        // The buttons have no cursor, so anchor on the middle of the canvas.
        const r = el.getBoundingClientRect();
        zoomAt(zoom.value + d, r.left + r.width / 2, r.top + r.height / 2);
    }

    /**
     * Frame an arbitrary box, in content units.
     *
     * fit() can only ever frame EVERYTHING, which is no help on a house too big to read at once -
     * the guest picker uses this to zoom into one section.
     */
    function fitTo(b) {
    // A zero-size box is fine (it clamps to MAX_ZOOM); a NaN one is not - clampZoom(NaN) is NaN and
    // the pan follows it, which blanks the map with no error anywhere.
    if (! b || ! Number.isFinite(b.w) || ! Number.isFinite(b.h)) return;

        measure();
        if (!b) return;

        userAdjusted = true;
        const pad = Math.max(10, Math.min(32, Math.min(canvas.w, canvas.h) * 0.05));
        const z = Math.min((canvas.w - pad * 2) / b.w, (canvas.h - pad * 2) / b.h);
        zoom.value = clampZoom(Math.round(z * 100) / 100);
        pan.x = Math.round((canvas.w - b.w * zoom.value) / 2 - b.minX * zoom.value);
        pan.y = Math.round((canvas.h - b.h * zoom.value) / 2 - b.minY * zoom.value);
    }

    /** Fill the canvas with the content, centred. */
    function fit({ auto = false } = {}) {
        if (auto && userAdjusted) return;
        measure();
        const b = contentBounds();
        if (!b) { zoom.value = 1; pan.x = 40; pan.y = 40; return; }

        // Proportional, not a flat 32: in the checkout form the map gets a ~300px column, where a
        // fixed 32px margin each side is a fifth of the width and costs enough zoom to drop the
        // seat numbers below the size at which they are drawn.
        const pad = Math.max(10, Math.min(32, Math.min(canvas.w, canvas.h) * 0.05));
        const z = Math.min((canvas.w - pad * 2) / b.w, (canvas.h - pad * 2) / b.h);
        zoom.value = clampZoom(Math.round(z * 100) / 100);
        pan.x = Math.round((canvas.w - b.w * zoom.value) / 2 - b.minX * zoom.value);
        pan.y = Math.round((canvas.h - b.h * zoom.value) / 2 - b.minY * zoom.value);
    }

    /**
     * Bring a content point into view, used when the keyboard moves focus to a seat that is
     * currently off screen - otherwise arrowing gives you a focus ring you cannot see.
     */
    function revealPoint(x, y, margin = 40) {
        const sx = x * zoom.value + pan.x;
        const sy = y * zoom.value + pan.y;

        if (sx < margin) pan.x += margin - sx;
        else if (sx > canvas.w - margin) pan.x -= sx - (canvas.w - margin);

        if (sy < margin) pan.y += margin - sy;
        else if (sy > canvas.h - margin) pan.y -= sy - (canvas.h - margin);
    }

    const distance = (a, b) => Math.hypot(a.x - b.x, a.y - b.y);
    const midpoint = (a, b) => ({ x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 });

    function onPointerDown(evt) {
        if (!canPan()) return;

        // A press that landed on something DRAWN is that thing's press, not the canvas's.
        //
        // `canPan` alone cannot cover this: the designer's draggable elements stop `mousedown`,
        // but `pointerdown` is a separate event that fires FIRST, so the flag it checks
        // (`drag.mode`) is still null when this runs. Dragging a section therefore started a pan
        // as well, and the whole view slid under the section being moved.
        if (!panFromChildren && evt.target !== evt.currentTarget) return;

        pointers.set(evt.pointerId, { x: evt.clientX, y: evt.clientY });

        if (pointers.size === 1) {
            // Armed, not active. Capture is deliberately NOT taken here - see onPointerMove.
            panFrom = {
                x: evt.clientX, y: evt.clientY, panX: pan.x, panY: pan.y,
                el: evt.currentTarget, id: evt.pointerId, active: false,
            };
            pinchFrom = null;
        } else if (pointers.size === 2) {
            const [a, b] = [...pointers.values()];
            pinchFrom = { dist: distance(a, b) || 1, zoom: zoom.value };
            panFrom = null;
        }
    }

    function onPointerMove(evt) {
        if (!pointers.has(evt.pointerId)) return;
        pointers.set(evt.pointerId, { x: evt.clientX, y: evt.clientY });

        if (pinchFrom && pointers.size >= 2) {
            const [a, b] = [...pointers.values()];
            const mid = midpoint(a, b);
            userAdjusted = true;
            zoomAt(pinchFrom.zoom * (distance(a, b) / pinchFrom.dist), mid.x, mid.y);
            return;
        }

        if (!panFrom) return;

        const dx = evt.clientX - panFrom.x;
        const dy = evt.clientY - panFrom.y;

        /**
         * A press only becomes a pan once it has actually moved.
         *
         * Capture used to be taken on pointerdown. With panFromChildren (the picker and the box
         * office), that meant pressing a SEAT gave the <svg> the pointer - and the browser then
         * dispatches the resulting `click` at the capturing element, never at the <circle> that
         * carries the click handler. The seat still took focus on mousedown, so selecting a seat
         * needed a click AND a press of Enter. Deferring capture past a dead zone leaves an
         * ordinary click to reach the seat, while a finger that moves still pans, because it
         * crosses the threshold within a pixel or two of setting off.
         */
        if (!panFrom.active) {
            if (Math.hypot(dx, dy) <= PAN_THRESHOLD) return;

            panFrom.active = true;
            // Taking control is a MOVE, not a press: setting this on pointerdown meant the first
            // tap on a seat switched off the automatic re-fit for the rest of the session.
            userAdjusted = true;
            panFrom.el?.setPointerCapture?.(panFrom.id);
        }

        const el = svgEl.value;
        const r = el ? el.getBoundingClientRect() : null;
        // Drag in client pixels, move in viewBox units.
        const sx = r ? canvas.w / (r.width || 1) : 1;
        const sy = r ? canvas.h / (r.height || 1) : 1;
        pan.x = panFrom.panX + dx * sx;
        pan.y = panFrom.panY + dy * sy;
    }

    function onPointerUp(evt) {
        // Only ever taken once a drag was real, so only ever released here.
        if (panFrom?.active && panFrom.id === evt.pointerId) {
            try {
                panFrom.el?.releasePointerCapture?.(evt.pointerId);
            } catch (e) {
                // Already released, or the pointer is gone. Nothing to do either way.
            }
        }

        pointers.delete(evt.pointerId);
        if (pointers.size < 2) pinchFrom = null;
        if (pointers.size === 0) panFrom = null;
    }

    /** True while the map is refusing the wheel, so a host can explain why. */
    const wheelBlocked = ref(false);
    let wheelBlockTimer = null;

    function onWheel(evt) {
        // On the guest map this sits inside a long checkout form, where swallowing every wheel
        // event traps the page: the buyer scrolls, nothing moves, and the only way past is to move
        // the pointer off the map. Google Maps solved this the same way - hold a modifier to zoom,
        // otherwise the page scrolls - and the -/+/Fit buttons and pinch are unaffected either way.
        if (wheelNeedsModifier && ! (evt.ctrlKey || evt.metaKey)) {
            wheelBlocked.value = true;
            clearTimeout(wheelBlockTimer);
            wheelBlockTimer = setTimeout(() => { wheelBlocked.value = false; }, 1600);

            return;
        }

        wheelBlocked.value = false;
        // Without this the page scrolls behind the map and the map never zooms.
        evt.preventDefault();
        userAdjusted = true;
        // Trackpads report small deltas continuously, mice report ~100 a notch. Normalising on the
        // sign keeps both feeling the same.
        zoomAt(zoom.value * (evt.deltaY < 0 ? 1.12 : 1 / 1.12), evt.clientX, evt.clientY);
    }

    /** Spread onto the <svg>. */
    const bind = {
        onPointerdown: onPointerDown,
        onPointermove: onPointerMove,
        onPointerup: onPointerUp,
        onPointercancel: onPointerUp,
        onPointerleave: onPointerUp,
        onWheel,
    };

    let ro = null;
    if (typeof ResizeObserver !== 'undefined') {
        ro = new ResizeObserver(() => {
            const before = canvas.h;
            measure();
            // The height is content-derived, so it settles a frame or two after the first paint.
            if (canvas.h !== before) fit({ auto: true });
        });
    }

    function observe() {
        measure();
        if (ro && svgEl.value) ro.observe(svgEl.value);
    }

    onBeforeUnmount(() => {
        ro?.disconnect();
        // The wheel-block timer outlives the component otherwise, and fires a write into a ref
        // nothing is watching any more.
        clearTimeout(wheelBlockTimer);
    });

    return { zoom, pan, canvas, bind, fit, fitTo, zoomBy, zoomAt, measure, observe, revealPoint, toContent, wheelBlocked };
}
