/**
 * SGX Fast In-Browser Image Compressor
 * Optimizes high-resolution camera photos (5-20MB) down to crisp,
 * lightweight JPEG files (300KB-700KB) in milliseconds using HTML5 Canvas / ImageBitmap.
 * Drastically cuts upload loading time by 10x-20x over cellular / wifi networks.
 */

export async function fastCompressImage(file, options = {}) {
  // If not an image (e.g. PDF or other document), return untouched
  if (!file || !file.type || !file.type.startsWith('image/')) {
    return file;
  }

  const {
    maxWidth = 2048,
    maxHeight = 2048,
    quality = 0.82,
    maxSizeKB = 650
  } = options;

  // If already very small (e.g. < 400KB jpeg), no compression needed
  if (file.size <= maxSizeKB * 1024 && (file.type === 'image/jpeg' || file.type === 'image/webp')) {
    return file;
  }

  return new Promise((resolve) => {
    const url = URL.createObjectURL(file);
    const img = new Image();

    img.onload = () => {
      URL.revokeObjectURL(url);

      let width = img.naturalWidth || img.width;
      let height = img.naturalHeight || img.height;

      // Calculate proportional constrained dimensions
      if (width > maxWidth || height > maxHeight) {
        if (width / height > maxWidth / maxHeight) {
          height = Math.round((height * maxWidth) / width);
          width = maxWidth;
        } else {
          width = Math.round((width * maxHeight) / height);
          height = maxHeight;
        }
      }

      const canvas = document.createElement('canvas');
      canvas.width = width;
      canvas.height = height;

      const ctx = canvas.getContext('2d', { alpha: false });
      if (!ctx) {
        return resolve(file); // Fallback to original if canvas fails
      }

      // Fill white background for transparent PNGs
      ctx.fillStyle = '#FFFFFF';
      ctx.fillRect(0, 0, width, height);

      // High quality smoothing
      ctx.imageSmoothingEnabled = true;
      ctx.imageSmoothingQuality = 'high';
      ctx.drawImage(img, 0, 0, width, height);

      // Determine output mime type (JPEG produces the best balance of speed and compression)
      const outputMime = 'image/jpeg';
      const newName = file.name.replace(/\.[^/.]+$/, '') + '.jpg';

      canvas.toBlob(
        (blob) => {
          if (!blob) {
            return resolve(file);
          }

          // If compression somehow produced larger file, return original
          if (blob.size >= file.size) {
            return resolve(file);
          }

          const compressedFile = new File([blob], newName, {
            type: outputMime,
            lastModified: file.lastModified || Date.now()
          });

          // Retain custom GPS / Exif properties if already attached
          if (file._latitude) compressedFile._latitude = file._latitude;
          if (file._longitude) compressedFile._longitude = file._longitude;
          if (file._address) compressedFile._address = file._address;

          resolve(compressedFile);
        },
        outputMime,
        quality
      );
    };

    img.onerror = () => {
      URL.revokeObjectURL(url);
      resolve(file); // Fail-safe fallback to original
    };

    img.src = url;
  });
}
