/**
 * Shrink a photo in the browser before it is uploaded.
 *
 * A COPY of the gallery editor's decode/drawTo/toBlob (partials/gallery-editor.blade.php), not a
 * move: that script lives inside the event form's mount and no browser test covers it, so it is
 * left exactly as it is. Redrawing on a canvas also drops the EXIF block, and with it the GPS
 * position a phone writes into every photo.
 */

function decode(file) {
    const viaBitmap = typeof createImageBitmap === 'function'
        ? createImageBitmap(file, { imageOrientation: 'from-image' }).catch(() => createImageBitmap(file))
        : Promise.reject(new Error('no bitmap'));

    return viaBitmap.catch(() => new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = reader.result;
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
    }));
}

function drawTo(source, maxSide) {
    const width = source.naturalWidth || source.width;
    const height = source.naturalHeight || source.height;
    const scale = Math.min(1, maxSide / Math.max(width, height));
    const canvas = document.createElement('canvas');
    canvas.width = Math.max(1, Math.round(width * scale));
    canvas.height = Math.max(1, Math.round(height * scale));
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(source, 0, 0, canvas.width, canvas.height);
    return canvas;
}

function toBlob(canvas, type, quality) {
    return new Promise((resolve, reject) => {
        canvas.toBlob((blob) => (blob ? resolve(blob) : reject(new Error('encode'))), type, quality);
    });
}

/** A JPEG Blob no larger than maxSide on its long edge. */
export async function shrinkImage(file, maxSide = 1600, quality = 0.85) {
    const source = await decode(file);
    const canvas = drawTo(source, maxSide);
    if (typeof source.close === 'function') source.close();
    return toBlob(canvas, 'image/jpeg', quality);
}
