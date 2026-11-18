export const MAX_SOURCE_BYTES = 20 * 1024 * 1024;
const MAX_SIDE = 1600;
const QUALITY = 0.8;

export function fitWithin(width, height, max) {
    const scale = Math.min(1, max / Math.max(width, height));
    return { width: Math.round(width * scale), height: Math.round(height * scale) };
}

/** Downscales a photo in the browser so large camera files upload as a light WebP. */
export async function compressImage(file) {
    if (file.size > MAX_SOURCE_BYTES) throw new Error('La imagen no puede superar los 20 MB.');
    const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
    const { width, height } = fitWithin(bitmap.width, bitmap.height, MAX_SIDE);
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    canvas.getContext('2d').drawImage(bitmap, 0, 0, width, height);
    bitmap.close();
    const encode = (type) => new Promise((resolve) => canvas.toBlob(resolve, type, QUALITY));
    let blob = await encode('image/webp');
    // Browsers without a WebP encoder silently return a heavy PNG instead.
    if (blob?.type !== 'image/webp') blob = await encode('image/jpeg');
    if (!blob) throw new Error('No se pudo procesar la imagen.');
    const extension = blob.type === 'image/webp' ? 'webp' : 'jpg';
    return new File([blob], `${file.name.replace(/\.[^.]+$/, '')}.${extension}`, { type: blob.type });
}
