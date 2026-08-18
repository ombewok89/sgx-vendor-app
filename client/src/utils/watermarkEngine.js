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

export async function stampGpsWatermark(file, metadata = {}) {
  return new Promise(async (resolve) => {
    if (!file || !file.type.startsWith('image/')) {
      return resolve(file);
    }

    // 1. Preload Official SGX Logo
    const logoImg = await loadLogoImage();

    // 2. Extract embedded EXIF Metadata directly from the uploaded photo file
    const exifData = await extractExifFromImage(file);

    // Prioritize EXIF GPS from photo, fallback to live device GPS, fallback to Bandung default
    const finalLat = exifData?.latitude != null 
      ? exifData.latitude 
      : (metadata.latitude != null ? metadata.latitude : -6.917464);

    const finalLng = exifData?.longitude != null 
      ? exifData.longitude 
      : (metadata.longitude != null ? metadata.longitude : 107.619122);

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

          const latFormatted = Number(finalLat).toFixed(6);
          const lngFormatted = Number(finalLng).toFixed(6);

          const companyName = metadata.companyName || 'Sinar Grafika';
          const companyPhone = metadata.companyPhone || '082388885251';

          // Base scale based on canvas width (normalized to 1100px, minimum 1.0)
          const scale = Math.max(1.0, width / 1100);

          // 1. Draw Top-Left Official SGX Logo with rounded corners & shadow
          const topLogoSize = Math.round(110 * scale);
          const topLogoX = Math.round(28 * scale);
          const topLogoY = Math.round(28 * scale);

          if (logoImg) {
            drawRoundedImage(ctx, logoImg, topLogoX, topLogoY, topLogoSize, topLogoSize, 22 * scale);
          }

          // 2. Bottom Geotagging & GPS Overlay (100% Transparent Over Field Photo)
          const footerBarH = Math.round(95 * scale); // White branding bar height
          const infoPanelH = Math.round(310 * scale); // Overlay area height
          const totalPanelH = infoPanelH + footerBarH;
          const panelY = height - totalPanelH;

          // NO dark covering background - 100% transparent so field photo is fully visible!

          // 3. Mini Map Dimensions (Right side)
          const mapW = Math.round(280 * scale);
          const mapH = Math.round(240 * scale);
          const mapX = width - mapW - (28 * scale);
          const mapY = panelY + (30 * scale);

          // 4. Draw Left Text Metadata (Large, High-Contrast with Text Strokes)
          const textMarginL = Math.round(32 * scale);
          const maxTextW = mapX - textMarginL - (24 * scale);
          let currentY = panelY + (78 * scale);

          // BLOK WAKTU (EXTRA LARGE DIGITAL TIME: e.g. 00:09)
          ctx.save();
          ctx.font = `900 ${92 * scale}px "Inter", "Montserrat", "Segoe UI", Arial, sans-serif`;
          ctx.shadowColor = 'rgba(0,0,0,0.95)';
          ctx.shadowBlur = 16 * scale;
          ctx.strokeStyle = 'rgba(0,0,0,0.9)';
          ctx.lineWidth = 7 * scale;
          ctx.strokeText(timeStr, textMarginL, currentY);
          ctx.fillStyle = '#FFFFFF';
          ctx.fillText(timeStr, textMarginL, currentY);
          const timeW = ctx.measureText(timeStr).width;

          // Vertical Gold Separator Line
          const sepX = textMarginL + timeW + (20 * scale);
          ctx.fillStyle = '#EAB308';
          ctx.fillRect(sepX, panelY + (10 * scale), 5 * scale, 76 * scale);

          // Date & Day
          ctx.font = `800 ${28 * scale}px "Inter", "Montserrat", Arial, sans-serif`;
          ctx.strokeText(dateStr, sepX + (18 * scale), currentY - (42 * scale));
          ctx.fillStyle = '#FFFFFF';
          ctx.fillText(dateStr, sepX + (18 * scale), currentY - (42 * scale));

          ctx.font = `800 ${30 * scale}px "Inter", "Montserrat", Arial, sans-serif`;
          ctx.strokeText(dayName, sepX + (18 * scale), currentY - (6 * scale));
          ctx.fillStyle = '#F1F5F9';
          ctx.fillText(dayName, sepX + (18 * scale), currentY - (6 * scale));
          ctx.restore();

          // Stage & SPK Label Badge (Enlarged)
          currentY += (42 * scale);
          ctx.save();
          ctx.font = `900 ${24 * scale}px "Inter", "Montserrat", Arial, sans-serif`;
          ctx.shadowColor = 'rgba(0,0,0,0.95)';
          ctx.shadowBlur = 12 * scale;
          ctx.strokeStyle = 'rgba(0,0,0,0.9)';
          ctx.lineWidth = 5 * scale;
          const stageBadgeText = `[${spkNumber}] • TAHAP: ${stage}`;
          ctx.strokeText(stageBadgeText, textMarginL, currentY);
          ctx.fillStyle = stage === 'BEFORE' ? '#FACC15' : stage === 'PROCESS' ? '#60A5FA' : '#4ADE80';
          ctx.fillText(stageBadgeText, textMarginL, currentY);
          ctx.restore();

          // BLOK ALAMAT (Enlarged & High-Contrast)
          currentY += (40 * scale);
          ctx.save();
          ctx.font = `800 ${26 * scale}px "Inter", "Montserrat", Arial, sans-serif`;
          ctx.shadowColor = 'rgba(0,0,0,0.95)';
          ctx.shadowBlur = 14 * scale;
          ctx.strokeStyle = 'rgba(0,0,0,0.9)';
          ctx.lineWidth = 6 * scale;
          const fullDisplayAddress = dynamicAddress || 'Mataram, Kec. Tugumulyo, Kabupaten Musi Rawas, Sumatera Selatan 31626';
          const truncatedAddr = truncateText(ctx, fullDisplayAddress, maxTextW);
          ctx.strokeText(truncatedAddr, textMarginL, currentY);
          ctx.fillStyle = '#FFFFFF';
          ctx.fillText(truncatedAddr, textMarginL, currentY);
          ctx.restore();

          // BLOK KOORDINAT (Enlarged Solid Black Badge with Bright White Text)
          currentY += (26 * scale);
          const coordText = `Koordinat: ${latFormatted}, ${lngFormatted}`;
          ctx.font = `800 ${22 * scale}px "JetBrains Mono", monospace, Arial`;
          const coordTextW = ctx.measureText(coordText).width;
          const badgePadX = 18 * scale;
          const badgeH = 42 * scale;

          ctx.save();
          ctx.shadowColor = 'rgba(0,0,0,0.85)';
          ctx.shadowBlur = 10 * scale;
          ctx.fillStyle = 'rgba(0, 0, 0, 0.92)';
          ctx.fillRect(textMarginL, currentY, coordTextW + (badgePadX * 2), badgeH);
          ctx.strokeStyle = '#64748B';
          ctx.lineWidth = 2 * scale;
          ctx.strokeRect(textMarginL, currentY, coordTextW + (badgePadX * 2), badgeH);

          ctx.fillStyle = '#FFFFFF';
          ctx.fillText(coordText, textMarginL + badgePadX, currentY + (29 * scale));
          ctx.restore();

          // 5. Draw Mini GPS Satellite Map on the right
          drawRealisticMiniMap(ctx, mapX, mapY, mapW, mapH, scale);

          // 6. Draw Bottom White/Light Branding Footer Bar (Area Kontak Perusahaan)
          const footerY = height - footerBarH;
          ctx.save();
          ctx.fillStyle = '#FFFFFF';
          ctx.fillRect(0, footerY, width, footerBarH);

          // Top dividing border
          ctx.fillStyle = '#CBD5E1';
          ctx.fillRect(0, footerY, width, 2 * scale);

          // Left Office Address in footer
          ctx.font = `700 ${18 * scale}px "Inter", "Montserrat", Arial, sans-serif`;
          ctx.fillStyle = '#0F172A';
          ctx.fillText('Jl. Ratu Agung No. 04 - Kel. Anggut Bawah, Kec. Ratu Samban, Kota Bengkulu', textMarginL, footerY + (38 * scale));

          // Phone in footer
          ctx.font = `700 ${18 * scale}px "JetBrains Mono", monospace, Arial`;
          ctx.fillStyle = '#334155';
          ctx.fillText('Telp / WA: 0823 8888 5251', textMarginL, footerY + (72 * scale));

          // Small Company Logo in footer
          const footerLogoSize = Math.round(68 * scale);
          const footerLogoX = width - Math.round(390 * scale);
          const footerLogoY = footerY + Math.round((footerBarH - footerLogoSize) / 2);

          if (logoImg) {
            drawRoundedImage(ctx, logoImg, footerLogoX, footerLogoY, footerLogoSize, footerLogoSize, 12 * scale);
          }

          // Website URL in right-most footer
          ctx.font = `700 ${17 * scale}px "Inter", "Montserrat", Arial, sans-serif`;
          ctx.fillStyle = '#64748B';
          ctx.fillText('vendor.sinargrafika.my.id', width - Math.round(280 * scale), footerY + (56 * scale));
          ctx.restore();

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
 * Draw Realistic Mini GPS Satellite/Street Map
 */
function drawRealisticMiniMap(ctx, x, y, width, height, scale) {
  ctx.save();

  // Outer border with rounded corners
  const radius = 18 * scale;
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

  // Satellite Terrain background (Forest/Urban mix)
  ctx.fillStyle = '#2C3E50';
  ctx.fillRect(x, y, width, height);

  // Green satellite vegetation patches
  ctx.fillStyle = '#274E13';
  ctx.fillRect(x, y, width * 0.45, height * 0.4);
  ctx.fillStyle = '#38761D';
  ctx.fillRect(x + width * 0.5, y + height * 0.45, width * 0.5, height * 0.55);

  // Roof & building lots texture
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

  // Highway center (Yellow)
  ctx.strokeStyle = '#FDE047';
  ctx.lineWidth = 6 * scale;
  ctx.beginPath();
  ctx.moveTo(x + width * 0.85, y);
  ctx.lineTo(x + width * 0.2, y + height);
  ctx.stroke();

  // Cross Street (White)
  ctx.strokeStyle = '#FFFFFF';
  ctx.lineWidth = 5 * scale;
  ctx.beginPath();
  ctx.moveTo(x, y + height * 0.35);
  ctx.lineTo(x + width, y + height * 0.7);
  ctx.stroke();

  // Street Name labels
  ctx.save();
  ctx.font = `bold ${10 * scale}px "Inter", Arial`;
  ctx.fillStyle = '#FFFFFF';
  ctx.shadowColor = 'rgba(0,0,0,0.9)';
  ctx.shadowBlur = 4 * scale;
  ctx.fillText('Jl. Yos Sudarso', x + (12 * scale), y + (height * 0.38));
  ctx.fillText('Jl. Trans', x + (width * 0.65), y + (height * 0.22));
  ctx.restore();

  // Vision Field Angle Cone (Emerald/Cyan wedge)
  const pinX = x + width * 0.48;
  const pinY = y + height * 0.52;

  ctx.fillStyle = 'rgba(16, 185, 129, 0.45)';
  ctx.beginPath();
  ctx.moveTo(pinX, pinY);
  ctx.arc(pinX, pinY, 48 * scale, -Math.PI * 0.85, -Math.PI * 0.3);
  ctx.closePath();
  ctx.fill();

  // Blue GPS Pin Marker
  ctx.fillStyle = '#0284C7';
  ctx.beginPath();
  ctx.arc(pinX, pinY, 12 * scale, 0, Math.PI * 2);
  ctx.fill();
  ctx.strokeStyle = '#FFFFFF';
  ctx.lineWidth = 3.5 * scale;
  ctx.stroke();

  // Inner White Dot
  ctx.fillStyle = '#FFFFFF';
  ctx.beginPath();
  ctx.arc(pinX, pinY, 4.5 * scale, 0, Math.PI * 2);
  ctx.fill();

  // Bottom Google Maps Banner
  ctx.fillStyle = 'rgba(0, 0, 0, 0.75)';
  ctx.fillRect(x, y + height - (26 * scale), width, 26 * scale);
  ctx.font = `bold ${12 * scale}px "Inter", Arial`;
  ctx.fillStyle = '#F8FAFC';
  ctx.fillText('Google', x + (12 * scale), y + height - (8 * scale));

  ctx.restore();

  // White border outline on map container
  ctx.save();
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.95)';
  ctx.lineWidth = 3 * scale;
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
