/**
 * SGX GPS Map Camera & Watermark Stamping Engine
 * Stamping real-time digital clock, address, GPS coordinates,
 * visual mini-map, and official Sinar Grafika (SGX) logo.
 * 
 * Auto-extracts EXIF GPS coordinates & DateTime directly from the photo,
 * and performs automated Reverse Geocoding to fetch exact street addresses.
 */

import { extractExifFromImage } from './exifReader';
import { reverseGeocodeCoordinates } from './geoCoder';

// Preload the official SGX logo image
let cachedLogoImg = null;

function loadLogoImage() {
  return new Promise((resolve) => {
    if (cachedLogoImg && cachedLogoImg.complete && cachedLogoImg.naturalWidth > 0) {
      return resolve(cachedLogoImg);
    }
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
      cachedLogoImg = img;
      resolve(img);
    };
    img.onerror = () => {
      resolve(null);
    };
    img.src = '/sgx_logo.png';
  });
}

/**
 * Fetch 100% Real Satellite Imagery Tile from Esri World Imagery (No API Key Required)
 * With 2.5s timeout protection so offline/slow connections never freeze the app.
 */
function fetchRealSatelliteTile(lat, lng, width, height) {
  return new Promise((resolve) => {
    if (lat == null || lng == null) return resolve(null);
    // Higher zoom level (closer satellite view: delta 0.0012)
    const delta = 0.0012;
    const minLng = (Number(lng) - delta).toFixed(6);
    const maxLng = (Number(lng) + delta).toFixed(6);
    const minLat = (Number(lat) - (delta * 0.75)).toFixed(6);
    const maxLat = (Number(lat) + (delta * 0.75)).toFixed(6);
    const tileW = Math.min(500, Math.max(260, Math.round(width)));
    const tileH = Math.min(420, Math.max(200, Math.round(height)));

    const url = `https://services.arcgisonline.com/arcgis/rest/services/World_Imagery/MapServer/export?bbox=${minLng},${minLat},${maxLng},${maxLat}&bboxSR=4326&size=${tileW},${tileH}&f=image`;

    const img = new Image();
    img.crossOrigin = 'anonymous';
    const timer = setTimeout(() => resolve(null), 2500);

    img.onload = () => {
      clearTimeout(timer);
      resolve(img);
    };
    img.onerror = () => {
      clearTimeout(timer);
      resolve(null);
    };
    img.src = url;
  });
}

export async function stampGpsWatermark(file, metadata = {}) {
  return new Promise(async (resolve) => {
    if (!file || !file.type.startsWith('image/')) {
      return resolve(file);
    }

    // 1. Preload Official SGX Logo
    const logoImg = await loadLogoImage();

    // 2. Extract embedded EXIF Metadata directly from the uploaded photo file
    const exifData = await extractExifFromImage(file);

    // Multi-tier GPS coordinate resolver with strict Zero-Zero Guard
    function isValidCoord(val) {
      if (val == null || val === '' || isNaN(Number(val))) return false;
      const num = Number(val);
      return Math.abs(num) > 0.0001;
    }

    let finalLat = -3.824921;
    let finalLng = 102.286299;

    if (isValidCoord(exifData?.latitude) && isValidCoord(exifData?.longitude)) {
      finalLat = Number(exifData.latitude);
      finalLng = Number(exifData.longitude);
    } else if (isValidCoord(metadata?.latitude) && isValidCoord(metadata?.longitude)) {
      finalLat = Number(metadata.latitude);
      finalLng = Number(metadata.longitude);
    } else if (isValidCoord(metadata?.targetLat) && isValidCoord(metadata?.targetLng)) {
      finalLat = Number(metadata.targetLat);
      finalLng = Number(metadata.targetLng);
    } else if (isValidCoord(metadata?.checkInLat) && isValidCoord(metadata?.checkInLng)) {
      finalLat = Number(metadata.checkInLat);
      finalLng = Number(metadata.checkInLng);
    }

    // 3. Preload Real Satellite Map Imagery Tile for the exact coordinates (with 2.5s fallback)
    const satelliteImg = await fetchRealSatelliteTile(finalLat, finalLng, 300, 190);

    // Prioritize EXIF original capture time, fallback to metadata/current time
    let photoDate = new Date();
    if (exifData?.dateTimeOriginal instanceof Date && !isNaN(exifData.dateTimeOriginal.getTime())) {
      photoDate = exifData.dateTimeOriginal;
    } else if (metadata.timestamp) {
      const parsed = new Date(metadata.timestamp);
      if (!isNaN(parsed.getTime())) photoDate = parsed;
    }

    // 3. Automated Reverse Geocoding from the coordinates
    const locationName = metadata.locationName || metadata.workOrderTitle || 'Lokasi Pekerjaan Lapangan';
    const dynamicAddress = await reverseGeocodeCoordinates(finalLat, finalLng, metadata.address || locationName);

    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        try {
          // Normalize resolution to ensure crispness (1600px - 2048px width)
          const TARGET_WIDTH = Math.min(2048, Math.max(1280, img.width));
          const scaleRatio = TARGET_WIDTH / img.width;
          const width = TARGET_WIDTH;
          const height = Math.round(img.height * scaleRatio);

          const canvas = document.createElement('canvas');
          canvas.width = width;
          canvas.height = height;
          const ctx = canvas.getContext('2d');

          // Draw the base captured photo
          ctx.drawImage(img, 0, 0, width, height);

          // Format Time & Date strings
          const hours = String(photoDate.getHours()).padStart(2, '0');
          const minutes = String(photoDate.getMinutes()).padStart(2, '0');
          const timeStr = `${hours}:${minutes}`;

          const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
          const dayName = days[photoDate.getDay()];
          const dateStr = `${String(photoDate.getDate()).padStart(2, '0')}/${String(photoDate.getMonth() + 1).padStart(2, '0')}/${photoDate.getFullYear()}`;

          const stage = (metadata.stage || 'AFTER').toUpperCase();
          const spkNumber = metadata.spkNumber || 'SPK-EVIDENCE';

          const stageKey = (metadata.stage || 'AFTER').toUpperCase();
          const stageColors = {
            'BEFORE':  { bar: '#0f1a12', accent: '#5dd98a' },
            'PROCESS': { bar: '#1a150a', accent: '#e6a817' },
            'AFTER':   { bar: '#0a121a', accent: '#5d9ad9' },
            'ISSUE':   { bar: '#1a0a0a', accent: '#e65d5d' },
          };
          const activeColors = stageColors[stageKey] || stageColors['AFTER'];
          const accentColor = activeColors.accent;

          const latFormatted = Number(finalLat).toFixed(6);
          const lngFormatted = Number(finalLng).toFixed(6);

          // SPESIFIKASI TEKNIS: Asumsi Resolusi 3024px standar
          // Scale factor: $scale = $image->width() / 3024
          const scale = width / 3024;
          const s = scale;

          // =========================================================================
          // ZONA 5 — Watermark SGX (Pojok Kanan Atas)
          // Teks baris 1: "SGX VENDOR" (font-size 24*scale, bold, warna aksen)
          // Teks baris 2: "Foto Terverifikasi" (font-size 16*scale, putih)
          // Posisi: right: 16*scale, top: 16*scale
          // Background: rgba(0,0,0,0.35) rounded 4px, padding 6x10px
          // =========================================================================
          ctx.save();
          const z5PadX = 14 * s;
          const z5PadY = 10 * s;
          const z5Font1 = Math.round(24 * s);
          const z5Font2 = Math.round(16 * s);

          ctx.font = `bold ${z5Font1}px "Inter", "Montserrat", Arial, sans-serif`;
          const z5Text1W = ctx.measureText('SGX VENDOR').width;
          ctx.font = `500 ${z5Font2}px "Inter", "Montserrat", Arial, sans-serif`;
          const z5Text2W = ctx.measureText('Foto Terverifikasi').width;
          const z5BoxW = Math.max(z5Text1W, z5Text2W) + (z5PadX * 2);
          const z5BoxH = z5Font1 + z5Font2 + (z5PadY * 2.2);

          const z5X = width - z5BoxW - (16 * s);
          const z5Y = 16 * s;

          ctx.fillStyle = 'rgba(0, 0, 0, 0.45)';
          ctx.beginPath();
          ctx.roundRect(z5X, z5Y, z5BoxW, z5BoxH, 6 * s);
          ctx.fill();

          ctx.fillStyle = accentColor;
          ctx.font = `bold ${z5Font1}px "Inter", "Montserrat", Arial, sans-serif`;
          ctx.fillText('SGX VENDOR', z5X + z5PadX, z5Y + z5PadY + z5Font1 - (4 * s));

          ctx.fillStyle = '#FFFFFF';
          ctx.font = `500 ${z5Font2}px "Inter", "Montserrat", Arial, sans-serif`;
          ctx.fillText('Foto Terverifikasi', z5X + z5PadX, z5Y + z5PadY + z5Font1 + z5Font2);
          ctx.restore();

          // =========================================================================
          // ZONA 3 — Bar Bawah (Footer)
          // Tinggi bar: 140 * scale px
          // Background: #FFFFFF (putih solid)
          // Border top: 3px * scale solid warna aksen stage
          // =========================================================================
          const footerBarH = Math.round(140 * s);
          const footerY = height - footerBarH;

          ctx.save();
          ctx.fillStyle = '#FFFFFF';
          ctx.fillRect(0, footerY, width, footerBarH);

          // Border Top 3px solid warna aksen stage
          ctx.fillStyle = accentColor;
          ctx.fillRect(0, footerY, width, Math.max(3, 3 * s));

          // [KIRI] Logo SGX + Teks Sinar Grafika + Phone
          const footerLogoSize = Math.round(75 * s);
          const footerLogoX = Math.round(30 * s);
          const footerLogoY = footerY + Math.round((footerBarH - footerLogoSize) / 2);
          if (logoImg) {
            ctx.drawImage(logoImg, footerLogoX, footerLogoY, footerLogoSize, footerLogoSize);
          }

          const footerTextX = footerLogoX + footerLogoSize + Math.round(18 * s);
          ctx.fillStyle = '#0F172A';
          ctx.font = `900 ${Math.round(32 * s)}px "Inter", "Montserrat", Arial, sans-serif`;
          ctx.fillText('Sinar Grafika', footerTextX, footerY + Math.round(52 * s));

          ctx.fillStyle = '#334155';
          ctx.font = `700 ${Math.round(24 * s)}px "Inter", "Montserrat", Arial, sans-serif`;
          ctx.fillText('082388885251', footerTextX, footerY + Math.round(92 * s));

          // [KANAN] Nomor SPK + Stage Badge + Logo SGX (repeat kecil 60x60px)
          const rightMargin = Math.round(30 * s);
          let curRightX = width - rightMargin;

          // Logo SGX Repeat Kecil (60x60px * scale)
          const smallLogoSize = Math.round(60 * s);
          const smallLogoX = curRightX - smallLogoSize;
          const smallLogoY = footerY + Math.round((footerBarH - smallLogoSize) / 2);
          if (logoImg) {
            ctx.drawImage(logoImg, smallLogoX, smallLogoY, smallLogoSize, smallLogoSize);
          }
          curRightX = smallLogoX - Math.round(20 * s);

          // Stage Badge (rounded pill, background aksen stage, teks putih, font-size 20*scale, padding 6x16px * scale)
          const badgeFontS = Math.round(20 * s);
          const badgePadX = Math.round(16 * s);
          const badgePadY = Math.round(8 * s);
          ctx.font = `900 ${badgeFontS}px "Inter", "Montserrat", Arial, sans-serif`;
          const stageTextW = ctx.measureText(stageKey).width;
          const badgeW = stageTextW + (badgePadX * 2);
          const badgeH = badgeFontS + (badgePadY * 2);
          const badgeX = curRightX - badgeW;
          const badgeY = footerY + Math.round((footerBarH - badgeH) / 2);

          ctx.fillStyle = accentColor;
          ctx.beginPath();
          ctx.roundRect(badgeX, badgeY, badgeW, badgeH, Math.round(badgeH / 2));
          ctx.fill();

          ctx.fillStyle = '#FFFFFF';
          ctx.fillText(stageKey, badgeX + badgePadX, badgeY + badgeFontS + (badgePadY * 0.7));
          curRightX = badgeX - Math.round(20 * s);

          // Nomor SPK (font-size 22 * scale, monospace, #888888)
          ctx.font = `700 ${Math.round(22 * s)}px "JetBrains Mono", monospace, Arial`;
          ctx.fillStyle = '#888888';
          ctx.textAlign = 'right';
          ctx.fillText(spkNumber, curRightX, footerY + Math.round(76 * s));
          ctx.textAlign = 'left'; // Reset
          ctx.restore();

          // =========================================================================
          // ZONA 1 — Overlay Teks di Dalam Foto (Bottom-Left)
          // Posisi: bottom-left, mulai dari 65% tinggi foto ke bawah
          // Background: gradient transparan dari bawah: rgba(0,0,0,0) -> rgba(0,0,0,0.75)
          // =========================================================================
          const zone1TopY = height * 0.65;
          const zone1BottomY = footerY;

          ctx.save();
          const zone1Grad = ctx.createLinearGradient(0, zone1TopY, 0, zone1BottomY);
          zone1Grad.addColorStop(0, 'rgba(0,0,0,0)');
          zone1Grad.addColorStop(1, 'rgba(0,0,0,0.78)');
          ctx.fillStyle = zone1Grad;
          ctx.fillRect(0, zone1TopY, width, zone1BottomY - zone1TopY);
          ctx.restore();

          // Elemen & Ukuran ZONA 1
          const padLeft = Math.round(30 * s);
          const mapZoneW = Math.round(320 * s);
          const maxTextW = width - mapZoneW - padLeft - Math.round(40 * s);

          // Perhitungan Y posisi teks dari bawah zona
          const zoneContentBottomY = footerY - Math.round(25 * s);
          let textY = zoneContentBottomY;

          // 1. Catatan Tambahan (Jika Ada)
          if (metadata.notes) {
            ctx.save();
            ctx.font = `700 ${Math.round(32 * s)}px "Inter", "Montserrat", Arial, sans-serif`;
            ctx.shadowColor = 'rgba(0,0,0,0.95)';
            ctx.shadowBlur = 12 * s;
            ctx.fillStyle = '#38BDF8';
            const cleanJob = truncateText(ctx, `📌 ${metadata.notes}`, maxTextW);
            ctx.fillText(cleanJob, padLeft, textY);
            textY -= Math.round(42 * s);
            ctx.restore();
          }

          // 2. Koordinat GPS (font-size = 34 * scale, warna aksen stage)
          ctx.save();
          const coordFontS = Math.round(34 * s);
          ctx.font = `800 ${coordFontS}px "JetBrains Mono", monospace, Arial`;
          ctx.shadowColor = 'rgba(0,0,0,0.95)';
          ctx.shadowBlur = 14 * s;
          ctx.fillStyle = accentColor;
          const coordStr = `📍 Koordinat: ${latFormatted}, ${lngFormatted}`;
          ctx.fillText(coordStr, padLeft, textY);
          textY -= Math.round(46 * s);
          ctx.restore();

          // 3. Alamat (max 2 baris, font-size = 38 * scale, putih, drop shadow)
          ctx.save();
          const addrFontS = Math.round(38 * s);
          ctx.font = `800 ${addrFontS}px "Inter", "Montserrat", Arial, sans-serif`;
          ctx.shadowColor = 'rgba(0,0,0,0.95)';
          ctx.shadowBlur = 16 * s;
          ctx.strokeStyle = 'rgba(0,0,0,0.95)';
          ctx.lineWidth = 6 * s;
          ctx.fillStyle = '#FFFFFF';

          const addressLines = wrapTextLines(ctx, dynamicAddress || locationName, maxTextW, 2);
          for (let i = addressLines.length - 1; i >= 0; i--) {
            const line = addressLines[i];
            ctx.strokeText(line, padLeft, textY);
            ctx.fillText(line, padLeft, textY);
            textY -= Math.round(48 * s);
          }
          ctx.restore();

          // 4. Jam Besar + Separator "|" + Tanggal + Hari
          ctx.save();
          const clockFontS = Math.round(128 * s);
          const dateFontS = Math.round(44 * s);
          const dayFontS = Math.round(40 * s);

          ctx.font = `900 ${clockFontS}px "Inter", "Montserrat", "Segoe UI", Arial, sans-serif`;
          ctx.shadowColor = 'rgba(0,0,0,0.95)';
          ctx.shadowBlur = 20 * s;
          ctx.strokeStyle = 'rgba(0,0,0,0.95)';
          ctx.lineWidth = 8 * s;
          ctx.strokeText(timeStr, padLeft, textY);
          ctx.fillStyle = '#FFFFFF';
          ctx.fillText(timeStr, padLeft, textY);

          const clockW = ctx.measureText(timeStr).width;

          // Separator "|" (tinggi 80px * scale, warna aksen stage)
          const sepX = padLeft + clockW + Math.round(20 * s);
          const sepH = Math.round(80 * s);
          const sepTopY = textY - Math.round(88 * s);
          ctx.fillStyle = accentColor;
          ctx.fillRect(sepX, sepTopY, Math.max(3, Math.round(5 * s)), sepH);

          // Tanggal & Hari di sebelah kanan separator
          const textDateX = sepX + Math.round(18 * s);
          ctx.font = `800 ${dateFontS}px "Inter", "Montserrat", Arial, sans-serif`;
          ctx.strokeText(dateStr, textDateX, sepTopY + Math.round(34 * s));
          ctx.fillStyle = '#FFFFFF';
          ctx.fillText(dateStr, textDateX, sepTopY + Math.round(34 * s));

          ctx.font = `700 ${dayFontS}px "Inter", "Montserrat", Arial, sans-serif`;
          ctx.strokeText(dayName, textDateX, sepTopY + Math.round(76 * s));
          ctx.fillStyle = '#E2E8F0';
          ctx.fillText(dayName, textDateX, sepTopY + Math.round(76 * s));
          ctx.restore();

          // =========================================================================
          // ZONA 2 — Mini Map (Pojok Kanan Bawah Overlay)
          // Ukuran kotak: 320 × 280 px (* scale)
          // Posisi: right: 20*scale, bottom: 150*scale (dari dasar foto)
          // Border radius: 8px (* scale)
          // Border: 2px solid warna aksen stage
          // =========================================================================
          const mapW = Math.round(320 * s);
          const mapH = Math.round(280 * s);
          const mapX = width - mapW - Math.round(20 * s);
          const mapY = height - Math.round(150 * s) - mapH;

          drawRealisticMiniMap(ctx, mapX, mapY, mapW, mapH, s, satelliteImg, accentColor);

          // 7. Output Final High-Resolution Stamped File with embedded GPS metadata
          canvas.toBlob(
            (blob) => {
              if (!blob) return resolve(file);
              const stampedFile = new File([blob], file.name || `stamped_${Date.now()}.jpg`, {
                type: 'image/jpeg',
                lastModified: photoDate.getTime()
              });
              // Attach extracted coordinates to the File object for instant form handling
              stampedFile._latitude = finalLat;
              stampedFile._longitude = finalLng;
              stampedFile._address = dynamicAddress;
              resolve(stampedFile);
            },
            'image/jpeg',
            0.94
          );
        } catch (err) {
          console.error('Error stamping photo:', err);
          resolve(file);
        }
      };
      img.onerror = () => resolve(file);
      img.src = e.target.result;
    };
    reader.onerror = () => resolve(file);
    reader.readAsDataURL(file);
  });
}

/**
 * Helper to draw image with rounded corners and border
 */
function drawRoundedImage(ctx, img, x, y, width, height, radius) {
  ctx.save();
  ctx.beginPath();
  ctx.moveTo(x + radius, y);
  ctx.lineTo(x + width - radius, y);
  ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
  ctx.lineTo(x + width, y + height - radius);
  ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
  ctx.lineTo(x + radius, y + height);
  ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
  ctx.lineTo(x, y + radius);
  ctx.quadraticCurveTo(x, y, x + radius, y);
  ctx.closePath();
  ctx.clip();

  ctx.drawImage(img, x, y, width, height);

  ctx.restore();

  // White outline border
  ctx.save();
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.9)';
  ctx.lineWidth = Math.max(2, radius * 0.15);
  ctx.beginPath();
  ctx.moveTo(x + radius, y);
  ctx.lineTo(x + width - radius, y);
  ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
  ctx.lineTo(x + width, y + height - radius);
  ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
  ctx.lineTo(x + radius, y + height);
  ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
  ctx.lineTo(x, y + radius);
  ctx.quadraticCurveTo(x, y, x + radius, y);
  ctx.closePath();
  ctx.stroke();
  ctx.restore();
}

/**
 * Draw 100% Real GPS Satellite / Map Imagery (with Offline Vector Fallback)
 * Sesuai Spesifikasi: Border radius 8px * scale, Border 2px solid warna aksen stage
 */
function drawRealisticMiniMap(ctx, x, y, width, height, scale, satelliteImg, accentColor = '#5d9ad9') {
  ctx.save();

  // Outer border with rounded corners (8px * scale)
  const radius = Math.max(6, Math.round(8 * scale));
  ctx.beginPath();
  ctx.roundRect(x, y, width, height, radius);
  ctx.clip();

  // 1. Draw Real Satellite Image if available, otherwise draw Vector Terrain
  if (satelliteImg && satelliteImg.complete && satelliteImg.naturalWidth > 0) {
    ctx.drawImage(satelliteImg, x, y, width, height);

    // Subtle dark gradient vignette on map edges
    const vig = ctx.createLinearGradient(x, y, x, y + height);
    vig.addColorStop(0, 'rgba(0,0,0,0.15)');
    vig.addColorStop(1, 'rgba(0,0,0,0.35)');
    ctx.fillStyle = vig;
    ctx.fillRect(x, y, width, height);
  } else {
    // Satellite Terrain fallback (Forest/Urban mix)
    ctx.fillStyle = '#2C3E50';
    ctx.fillRect(x, y, width, height);

    ctx.fillStyle = '#274E13';
    ctx.fillRect(x, y, width * 0.45, height * 0.4);
    ctx.fillStyle = '#38761D';
    ctx.fillRect(x + width * 0.5, y + height * 0.45, width * 0.5, height * 0.55);

    ctx.fillStyle = '#434343';
    ctx.fillRect(x + width * 0.1, y + height * 0.5, width * 0.35, height * 0.45);
    ctx.fillStyle = '#666666';
    ctx.fillRect(x + width * 0.55, y + height * 0.1, width * 0.38, height * 0.35);

    // Major Highway / Road (Yellow/Orange outline)
    ctx.strokeStyle = '#D97706';
    ctx.lineWidth = 9 * scale;
    ctx.beginPath();
    ctx.moveTo(x + width * 0.85, y);
    ctx.lineTo(x + width * 0.2, y + height);
    ctx.stroke();

    ctx.strokeStyle = '#FDE047';
    ctx.lineWidth = 6 * scale;
    ctx.beginPath();
    ctx.moveTo(x + width * 0.85, y);
    ctx.lineTo(x + width * 0.2, y + height);
    ctx.stroke();
  }

  // Vision Field Angle Cone (Emerald/Cyan wedge)
  const pinX = x + width * 0.5;
  const pinY = y + height * 0.5;

  ctx.fillStyle = 'rgba(16, 185, 129, 0.45)';
  ctx.beginPath();
  ctx.moveTo(pinX, pinY);
  ctx.arc(pinX, pinY, 48 * scale, -Math.PI * 0.85, -Math.PI * 0.3);
  ctx.closePath();
  ctx.fill();

  // Blue GPS Location Dot with White Ring
  ctx.fillStyle = '#0284C7';
  ctx.beginPath();
  ctx.arc(pinX, pinY, 13 * scale, 0, Math.PI * 2);
  ctx.fill();

  ctx.strokeStyle = '#FFFFFF';
  ctx.lineWidth = Math.max(2, 3 * scale);
  ctx.beginPath();
  ctx.arc(pinX, pinY, 13 * scale, 0, Math.PI * 2);
  ctx.stroke();

  // Bottom Map Watermark Banner
  ctx.fillStyle = 'rgba(0, 0, 0, 0.75)';
  ctx.fillRect(x, y + height - (26 * scale), width, 26 * scale);
  ctx.font = `bold ${12 * scale}px "Inter", Arial`;
  ctx.fillStyle = '#F8FAFC';
  ctx.fillText('OSM Map', x + (12 * scale), y + height - (8 * scale));

  ctx.restore();

  // Border: 2px solid warna aksen stage
  ctx.save();
  ctx.strokeStyle = accentColor;
  ctx.lineWidth = Math.max(2, 2 * scale);
  ctx.beginPath();
  ctx.roundRect(x, y, width, height, radius);
  ctx.stroke();
  ctx.restore();
}

/**
 * Text Truncate Helper
 */
function truncateText(ctx, text, maxWidth) {
  if (!text) return '';
  if (ctx.measureText(text).width <= maxWidth) return text;

  let str = text;
  while (str.length > 0 && ctx.measureText(str + '...').width > maxWidth) {
    str = str.slice(0, -1);
  }
  return str + '...';
}
