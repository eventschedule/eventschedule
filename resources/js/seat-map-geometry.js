/**
 * Section-frame geometry, shared by the designer, the guest picker and the box office.
 *
 * A section carries a `rotation`, and every seat coordinate inside it is relative to that rotated
 * frame. The designer applied the rotation when drawing and nothing else did: the picker received
 * `rotation` in its payload and ignored it, the box office payload omitted the field entirely, and
 * the printed report never looked. An angled side block - the whole reason the control exists -
 * rendered straight to the buyer, to staff at the door, and on the front-of-house sheet, so the only
 * person who ever saw the room as drawn was the person drawing it.
 *
 * Three places per renderer need the rotated frame: the `<g>` transform, the fit bounds, and
 * revealPoint() when the keyboard or a seat lookup pans to a seat.
 */

/** A point in a section's own coordinates, expressed in canvas space. */
export function toCanvasFrame(s, x, y) {
    const deg = Number(s?.rotation) || 0;
    if (! deg) return [s.x + x, s.y + y];

    const r = (deg * Math.PI) / 180;
    const cos = Math.cos(r);
    const sin = Math.sin(r);

    return [s.x + x * cos - y * sin, s.y + x * sin + y * cos];
}

/** A canvas-space delta, expressed in the section's own (rotated) coordinates. */
export function toSectionFrame(s, dx, dy) {
    const deg = Number(s?.rotation) || 0;
    if (! deg) return [dx, dy];

    const r = (-deg * Math.PI) / 180;
    const cos = Math.cos(r);
    const sin = Math.sin(r);

    return [Math.round(dx * cos - dy * sin), Math.round(dx * sin + dy * cos)];
}

/** The SVG transform for a section group. Rotation is applied about the section's own origin. */
export function sectionTransform(s) {
    const deg = Number(s?.rotation) || 0;

    return deg ? `translate(${s.x} ${s.y}) rotate(${deg})` : `translate(${s.x} ${s.y})`;
}
