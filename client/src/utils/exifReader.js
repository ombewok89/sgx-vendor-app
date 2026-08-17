/**
 * High-Performance Universal EXIF Metadata Reader using exifr
 * Supports JPEG, HEIC, PNG, WebP, TIFF formats.
 * Extracts GPS Coordinates (Latitude, Longitude) and Original Camera DateTime.
 */

import exifr from 'exifr';

export async function extractExifFromImage(file) {
  if (!file) return null;

  try {
    const [gps, tags] = await Promise.all([
      exifr.gps(file).catch(() => null),
      exifr.parse(file, {
        pick: ['DateTimeOriginal', 'CreateDate', 'ModifyDate', 'Make', 'Model', 'GPSLatitude', 'GPSLongitude']
      }).catch(() => null)
    ]);

    const latitude = gps?.latitude ?? tags?.latitude ?? tags?.GPSLatitude ?? null;
    const longitude = gps?.longitude ?? tags?.longitude ?? tags?.GPSLongitude ?? null;

    let originalDate = null;
    const rawDate = tags?.DateTimeOriginal || tags?.CreateDate || tags?.ModifyDate;
    if (rawDate) {
      const parsed = new Date(rawDate);
      if (!isNaN(parsed.getTime())) {
        originalDate = parsed;
      }
    }

    return {
      latitude: latitude != null ? Number(latitude) : null,
      longitude: longitude != null ? Number(longitude) : null,
      dateTimeOriginal: originalDate,
      cameraModel: tags?.Model || tags?.Make || null
    };
  } catch (err) {
    console.warn('Failed to extract EXIF from image:', err);
    return null;
  }
}
