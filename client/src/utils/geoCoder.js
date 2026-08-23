/**
 * Reverse Geocoding Utility (Format Standar Google Maps Indonesia)
 * Mengurai dan mengonstruksi alamat terstruktur dari BigDataCloud & Nominatim OSM:
 * [Nama Jalan/Dusun] No.[Nomor], RT.XX/RW.X, [Kelurahan], Kec. [Kecamatan], [Kota/Kabupaten], [Provinsi] [Kode Pos]
 */

// Smart Cache Memory (TTL: 30 Menit, Toleransi Jarak: 50 Meter)
const geocodeCache = new Map();
const CACHE_TTL = 30 * 60 * 1000; // 1.800.000 ms

/**
 * Helper: Validasi & Pembersihan Nilai String
 */
function cleanText(val) {
  if (val == null) return '';
  const str = String(val).trim();
  const invalidValues = ['undefined', 'null', '0', 'nan', '-', 'unknown', 'none'];
  if (invalidValues.includes(str.toLowerCase()) || str === '') {
    return '';
  }
  return str;
}

/**
 * Helper: Normalisasi Huruf Kapital Indonesia
 * Mengkapitalisasi awal kata kecuali preposisi pendek, dan menjaga singkatan resmi (RT, RW, Kec., Kab., No., Jl.)
 */
function titleCaseIndonesian(str) {
  if (!str) return '';
  
  const lowerPrepositions = ['di', 'ke', 'dari', 'dan', 'atau', 'pada', 'untuk', 'yang'];
  const fixedAbbreviations = {
    'rt': 'RT',
    'rw': 'RW',
    'kec': 'Kec.',
    'kec.': 'Kec.',
    'kecamatan': 'Kec.',
    'kab': 'Kab.',
    'kab.': 'Kab.',
    'kabupaten': 'Kab.',
    'no': 'No.',
    'no.': 'No.',
    'jl': 'Jl.',
    'jl.': 'Jl.',
    'jalan': 'Jl.',
    'dsn': 'Dsn.',
    'dsn.': 'Dsn.',
    'kel': 'Kel.',
    'kel.': 'Kel.',
    'ds': 'Ds.',
    'ds.': 'Ds.',
    'dki': 'DKI',
    'diy': 'DIY'
  };

  return str
    .split(/\s+/)
    .map((word, index) => {
      const cleanWord = word.replace(/[^a-zA-Z0-9.]/g, '');
      const lowerWord = cleanWord.toLowerCase();

      if (fixedAbbreviations[lowerWord]) {
        return fixedAbbreviations[lowerWord];
      }

      if (index > 0 && lowerPrepositions.includes(lowerWord)) {
        return lowerWord;
      }

      // Pertahankan pola RT.XX/RW.XX
      if (/^rt\.?\d+\/rw\.?\d+$/i.test(word)) {
        return word.toUpperCase().replace(/^RT\.?(\d+)\/RW\.?(\d+)$/i, 'RT.$1/RW.$2');
      }

      return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
    })
    .join(' ');
}

/**
 * Helper: Hitung Jarak 2 Koordinat (Haversine Formula dalam Meter)
 */
function getDistanceMeters(lat1, lon1, lat2, lon2) {
  const R = 6371e3; // Radius Bumi dalam meter
  const φ1 = (lat1 * Math.PI) / 180;
  const φ2 = (lat2 * Math.PI) / 180;
  const Δφ = ((lat2 - lat1) * Math.PI) / 180;
  const Δλ = ((lon2 - lon1) * Math.PI) / 180;

  const a =
    Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
    Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

  return R * c;
}

// ==========================================
// 1. PROVIDER UTAMA: BIGDATACLOUD (BDC)
// ==========================================

function getStreetNameBDC(data) {
  if (Array.isArray(data?.localityInfo?.informational)) {
    const roadInfo = data.localityInfo.informational.find((info) => {
      const desc = (info.description || '').toLowerCase();
      return (
        desc.includes('tertiary') ||
        desc.includes('secondary') ||
        desc.includes('residential') ||
        desc.includes('road') ||
        desc.includes('street') ||
        desc.includes('trunk') ||
        desc.includes('primary')
      );
    });
    if (roadInfo?.name) {
      return cleanText(roadInfo.name);
    }
  }
  return cleanText(data?.locality) || cleanText(data?.neighbourhood) || '';
}

function getHouseNumberBDC(data) {
  if (Array.isArray(data?.localityInfo?.informational)) {
    const numInfo = data.localityInfo.informational.find((info) => {
      const desc = (info.description || '').toLowerCase();
      const name = (info.name || '').trim();
      return desc === 'house_number' || /^\d+[a-zA-Z]?$/.test(name);
    });
    if (numInfo?.name) {
      return cleanText(numInfo.name);
    }
  }
  return '';
}

function getRtRwBDC(data) {
  if (Array.isArray(data?.localityInfo?.informational)) {
    const rtInfo = data.localityInfo.informational.find((info) => {
      const name = (info.name || '').trim();
      const desc = (info.description || '').toLowerCase();
      return /rt\.?\s*\d+\s*\/?\s*rw\.?\s*\d+/i.test(name) || desc === 'residential';
    });

    if (rtInfo?.name) {
      const match = rtInfo.name.match(/rt\.?\s*(\d+)\s*\/?\s*rw\.?\s*(\d+)/i);
      if (match) {
        return `RT.${match[1]}/RW.${match[2]}`;
      }
    }
  }
  return '';
}

function getAdminLevelBDC(data, levels) {
  if (Array.isArray(data?.localityInfo?.administrative)) {
    const target = data.localityInfo.administrative.find((adm) => {
      const lvl = adm.adminLevel || adm.order;
      return levels.includes(lvl);
    });
    if (target?.name) {
      return cleanText(target.name);
    }
  }
  return '';
}

function getKotaKabBDC(data) {
  let name = getAdminLevelBDC(data, [5]);
  if (!name) {
    name = cleanText(data?.city);
  }
  if (!name) return '';

  // Normalisasi prefix Kota / Kab.
  if (/^kota\s+/i.test(name) || /^kab\.?\s+/i.test(name) || /^kabupaten\s+/i.test(name)) {
    return titleCaseIndonesian(name.replace(/^kabupaten\s+/i, 'Kab. '));
  }

  // Deteksi dari jenis tipe city
  const isCity = String(data?.city || '').toLowerCase().includes('kota');
  const isRegency = String(data?.city || '').toLowerCase().includes('kab');

  if (isCity) return `Kota ${titleCaseIndonesian(name)}`;
  if (isRegency) return `Kab. ${titleCaseIndonesian(name)}`;

  return titleCaseIndonesian(name);
}

/**
 * Konstruksi Alamat Terstruktur dari BigDataCloud
 */
export function buildAddressFromBDC(data) {
  if (!data) return { formatted: '', components: {} };

  // Komponen 1: Nama Jalan
  const street = titleCaseIndonesian(getStreetNameBDC(data));

  // Komponen 2: Nomor Rumah
  const number = getHouseNumberBDC(data);

  // Komponen 3: RT/RW
  const rtRw = getRtRwBDC(data);

  // Komponen 4: Kelurahan / Desa
  let kelurahan = getAdminLevelBDC(data, [7, 8]);
  if (!kelurahan) kelurahan = cleanText(data?.locality);
  kelurahan = titleCaseIndonesian(kelurahan);

  // Komponen 5: Kecamatan
  let kecamatan = getAdminLevelBDC(data, [6]);
  if (!kecamatan) {
    const fallbackKec = data?.localityInfo?.administrative?.find((a) =>
      (a.name || '').toLowerCase().includes('kecamatan')
    );
    if (fallbackKec) kecamatan = fallbackKec.name;
  }
  if (kecamatan) {
    kecamatan = kecamatan.replace(/^Kecamatan\s+/i, '').trim();
    if (kecamatan.length > 20) {
      const words = kecamatan.split(/\s+/);
      kecamatan = words.slice(0, 3).join(' ') + '.';
    }
    kecamatan = `Kec. ${titleCaseIndonesian(kecamatan)}`;
  }

  // Komponen 6: Kota / Kabupaten
  const kota = getKotaKabBDC(data);

  // Komponen 7: Provinsi
  let provinsi = getAdminLevelBDC(data, [4]);
  if (!provinsi) provinsi = cleanText(data?.principalSubdivision);
  provinsi = titleCaseIndonesian(provinsi);

  // Komponen 8: Kode Pos
  const postcode = cleanText(data?.postcode);

  // --- PERAKITAN STRING ALAMAT ---
  const parts = [];

  // Baris 1: Jalan + No + RT/RW
  let line1 = street;
  if (number) line1 += ` No.${number}`;
  if (rtRw) line1 += `, ${rtRw}`;
  if (line1) parts.push(line1);

  // Baris 2: Kelurahan
  if (kelurahan && kelurahan !== street) parts.push(kelurahan);

  // Baris 3: Kecamatan
  if (kecamatan) parts.push(kecamatan);

  // Baris 4: Kota/Kabupaten
  if (kota) parts.push(kota);

  // Baris 5: Provinsi + Kode Pos
  const lastPart = [provinsi, postcode].filter(Boolean).join(' ');
  if (lastPart) parts.push(lastPart);

  let formatted = parts.join(', ');

  // Validasi & Truncate jika > 200 karakter
  if (formatted.length > 200) {
    formatted = formatted.substring(0, 197) + '...';
  }

  return {
    formatted,
    components: {
      street,
      number,
      rtRw,
      kelurahan,
      kecamatan,
      kota,
      provinsi,
      postcode
    }
  };
}

// ==========================================
// 2. PROVIDER CADANGAN: OPENSTREETMAP NOMINATIM
// ==========================================

/**
 * Konstruksi Alamat Terstruktur dari Nominatim OSM
 */
export function buildAddressFromNominatim(data) {
  if (!data || !data.address) return { formatted: '', components: {} };

  const addr = data.address || {};

  // Komponen 1: Nama Jalan
  const street = titleCaseIndonesian(cleanText(addr.road || addr.pedestrian || addr.footway || addr.suburb || ''));

  // Komponen 2: Nomor Rumah
  const number = cleanText(addr.house_number);

  // Komponen 3: RT/RW (quarter / neighbourhood)
  let rtRw = '';
  const quarter = cleanText(addr.quarter || addr.neighbourhood);
  if (/rt\.?\s*\d+\s*\/?\s*rw\.?\s*\d+/i.test(quarter)) {
    const match = quarter.match(/rt\.?\s*(\d+)\s*\/?\s*rw\.?\s*(\d+)/i);
    if (match) rtRw = `RT.${match[1]}/RW.${match[2]}`;
  }

  // Komponen 4: Kelurahan
  let kelurahan = cleanText(addr.village || addr.suburb || addr.hamlet || addr.neighbourhood || '');
  if (kelurahan === street) kelurahan = '';
  kelurahan = titleCaseIndonesian(kelurahan);

  // Komponen 5: Kecamatan
  let kecamatan = cleanText(addr.city_district || addr.county || addr.municipality || '');
  if (kecamatan) {
    kecamatan = kecamatan.replace(/^Kecamatan\s+/i, '').replace(/^Kec\.?\s+/i, '').trim();
    if (kecamatan.length > 20) {
      const words = kecamatan.split(/\s+/);
      kecamatan = words.slice(0, 3).join(' ') + '.';
    }
    kecamatan = `Kec. ${titleCaseIndonesian(kecamatan)}`;
  }

  // Komponen 6: Kota / Kabupaten
  let kota = cleanText(addr.city || addr.town || addr.municipality || addr.county || '');
  if (kota) {
    if (/^kota\s+/i.test(kota) || /^kab\.?\s+/i.test(kota) || /^kabupaten\s+/i.test(kota)) {
      kota = titleCaseIndonesian(kota.replace(/^kabupaten\s+/i, 'Kab. '));
    } else {
      kota = `Kota ${titleCaseIndonesian(kota)}`;
    }
  }

  // Komponen 7: Provinsi
  const provinsi = titleCaseIndonesian(cleanText(addr.state || addr.province || ''));

  // Komponen 8: Kode Pos
  const postcode = cleanText(addr.postcode);

  // --- PERAKITAN STRING ALAMAT ---
  const parts = [];

  let line1 = street;
  if (number) line1 += ` No.${number}`;
  if (rtRw) line1 += `, ${rtRw}`;
  if (line1) parts.push(line1);

  if (kelurahan) parts.push(kelurahan);
  if (kecamatan) parts.push(kecamatan);
  if (kota) parts.push(kota);

  const lastPart = [provinsi, postcode].filter(Boolean).join(' ');
  if (lastPart) parts.push(lastPart);

  let formatted = parts.join(', ');

  if (formatted.length > 200) {
    formatted = formatted.substring(0, 197) + '...';
  }

  return {
    formatted,
    components: {
      street,
      number,
      rtRw,
      kelurahan,
      kecamatan,
      kota,
      provinsi,
      postcode
    }
  };
}

// ==========================================
// 3. MAIN FUNCTION: REVERSE GEOCODE
// ==========================================

export async function reverseGeocodeCoordinates(latitude, longitude, fallbackName = '') {
  if (latitude == null || longitude == null) {
    return fallbackName || 'Indonesia';
  }

  const numLat = Number(latitude);
  const numLng = Number(longitude);
  const cacheKey = `${numLat.toFixed(4)},${numLng.toFixed(4)}`;

  // Periksa Smart Cache Memory (TTL 30 Menit & Toleransi Jarak < 50m)
  const cached = geocodeCache.get(cacheKey);
  const now = Date.now();
  if (cached && (now - cached.timestamp < CACHE_TTL)) {
    const dist = getDistanceMeters(numLat, numLng, cached.lat, cached.lng);
    if (dist <= 50 && cached.formatted) {
      return cached.formatted;
    }
  }

  // 1. Eksekusi Provider Utama: BigDataCloud
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3500);

    const res = await fetch(
      `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${numLat}&longitude=${numLng}&localityLanguage=id`,
      { signal: controller.signal }
    );
    clearTimeout(timeoutId);

    if (res.ok) {
      const data = await res.json();
      const result = buildAddressFromBDC(data);

      // Validasi Minimum 3 Komponen
      const validComponentsCount = Object.values(result.components).filter(Boolean).length;
      if (validComponentsCount >= 3 && result.formatted) {
        geocodeCache.set(cacheKey, {
          formatted: result.formatted,
          components: result.components,
          provider: 'bigdatacloud',
          timestamp: now,
          lat: numLat,
          lng: numLng
        });
        return result.formatted;
      }
    }
  } catch (err) {
    // Failover ke provider cadangan Nominatim jika timeout/gagal
  }

  // 2. Eksekusi Provider Cadangan: OpenStreetMap Nominatim
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3500);

    const res = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${numLat}&lon=${numLng}&zoom=18&addressdetails=1`,
      {
        headers: { 'Accept-Language': 'id' },
        signal: controller.signal
      }
    );
    clearTimeout(timeoutId);

    if (res.ok) {
      const data = await res.json();
      const result = buildAddressFromNominatim(data);

      const validComponentsCount = Object.values(result.components).filter(Boolean).length;
      if (validComponentsCount >= 3 && result.formatted) {
        geocodeCache.set(cacheKey, {
          formatted: result.formatted,
          components: result.components,
          provider: 'nominatim',
          timestamp: now,
          lat: numLat,
          lng: numLng
        });
        return result.formatted;
      }
    }
  } catch (err) {
    // Fallback jika offline/koneksi bermasalah
  }

  // 3. Fallback jika seluruh provider tidak menghasilkan alamat valid
  const fallback = fallbackName || `Area Koordinat ${numLat.toFixed(4)}, ${numLng.toFixed(4)}`;
  geocodeCache.set(cacheKey, {
    formatted: fallback,
    components: {},
    provider: 'fallback',
    timestamp: now,
    lat: numLat,
    lng: numLng
  });

  return fallback;
}
