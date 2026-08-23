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
 */
export function useMapViewport({ svgEl, contentBounds, canPan = () => true }) {
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
     * they touch it, then never again.
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
        userAdjusted = true;
        pointers.set(evt.pointerId, { x: evt.clientX, y: evt.clientY });

        if (pointers.size === 1) {
            panFrom = { x: evt.clientX, y: evt.clientY, panX: pan.x, panY: pan.y };
            pinchFrom = null;
            evt.currentTarget?.setPointerCapture?.(evt.pointerId);
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
            zoomAt(pinchFrom.zoom * (distance(a, b) / pinchFrom.dist), mid.x, mid.y);
            return;
        }

        if (!panFrom) return;
        const el = svgEl.value;
        const r = el ? el.getBoundingClientRect() : null;
        // Drag in client pixels, move in viewBox units.
        const sx = r ? canvas.w / (r.width || 1) : 1;
        const sy = r ? canvas.h / (r.height || 1) : 1;
        pan.x = panFrom.panX + (evt.clientX - panFrom.x) * sx;
        pan.y = panFrom.panY + (evt.clientY - panFrom.y) * sy;
    }

    function onPointerUp(evt) {
        pointers.delete(evt.pointerId);
        if (pointers.size < 2) pinchFrom = null;
        if (pointers.size === 0) panFrom = null;
    }

    function onWheel(evt) {
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

    onBeforeUnmount(() => ro?.disconnect());

    return { zoom, pan, canvas, bind, fit, zoomBy, zoomAt, measure, observe, revealPoint, toContent };
}
