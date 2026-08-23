/**
 * Enhanced Super-Detailed Reverse Geocoding Utility for Indonesia
 * Converts Latitude & Longitude into full detailed Indonesian street address:
 * [Nama Jalan / Gang / No. Bangunan], [Kelurahan/Desa], [Kecamatan], [Kota/Kabupaten], [Provinsi] [Kode Pos]
 */

const geocodeCache = new Map();

export async function reverseGeocodeCoordinates(latitude, longitude, fallbackName = '') {
  if (latitude == null || longitude == null) {
    return fallbackName || 'Mendeteksi alamat satelit...';
  }

  const latNum = Number(latitude);
  const lngNum = Number(longitude);
  const cacheKey = `${latNum.toFixed(5)},${lngNum.toFixed(5)}`;

  if (geocodeCache.has(cacheKey)) {
    return geocodeCache.get(cacheKey);
  }

  // 1. PRIORITAS UTAMA: OpenStreetMap Nominatim (Zoom 18 - Level Jalan & Nomor Rumah Presisi)
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 4000);

    const res = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latNum}&lon=${lngNum}&zoom=18&addressdetails=1`,
      {
        headers: {
          'Accept-Language': 'id,id-ID;q=0.9,en;q=0.8',
          'User-Agent': 'SGX-Vendor-TimestampCamera/2.0'
        },
        signal: controller.signal
      }
    );
    clearTimeout(timeoutId);

    if (res.ok) {
      const data = await res.json();
      const addr = data.address || {};
      const parts = [];

      // A. Nama Jalan & Nomor Bangunan / Gang / Landmark
      let roadStr = '';
      if (addr.road) {
        roadStr = addr.road.startsWith('Jalan') || addr.road.startsWith('Jl.') ? addr.road : `Jl. ${addr.road}`;
        if (addr.house_number) {
          roadStr += ` No. ${addr.house_number}`;
        }
      } else if (addr.building || addr.amenity || addr.shop || addr.office) {
        roadStr = addr.building || addr.amenity || addr.shop || addr.office;
      } else if (addr.pedestrian || addr.footway || addr.path) {
        roadStr = addr.pedestrian || addr.footway || addr.path;
      }

      if (roadStr) parts.push(roadStr);

      // B. RT / RW / Dusun / Komplek
      if (addr.hamlet || addr.residential) {
        const sub = addr.hamlet || addr.residential;
        if (!parts.includes(sub)) parts.push(sub);
      }

      // C. Kelurahan / Desa
      const kelurahan = addr.village || addr.suburb || addr.neighbourhood;
      if (kelurahan && !parts.includes(kelurahan)) {
        const cleanKel = kelurahan.replace(/^Kelurahan\s+/i, 'Kel. ').replace(/^Desa\s+/i, 'Desa ');
        parts.push(cleanKel);
      }

      // D. Kecamatan
      const kecamatan = addr.city_district || (addr.county && addr.county.toLowerCase().includes('kec') ? addr.county : null);
      if (kecamatan) {
        const cleanKec = `Kec. ${kecamatan.replace(/^Kecamatan\s+/i, '').replace(/^Kec\.\s*/i, '')}`;
        if (!parts.includes(cleanKec)) parts.push(cleanKec);
      }

      // E. Kota / Kabupaten
      const kota = addr.city || addr.town || addr.municipality || (addr.county && !addr.county.toLowerCase().includes('kec') ? addr.county : null);
      if (kota && !parts.includes(kota)) {
        parts.push(kota);
      }

      // F. Provinsi
      if (addr.state && !parts.includes(addr.state)) {
        parts.push(addr.state);
      }

      // G. Kode Pos
      if (addr.postcode) {
        parts.push(addr.postcode);
      }

      if (parts.length >= 2) {
        const fullAddress = parts.join(', ');
        geocodeCache.set(cacheKey, fullAddress);
        return fullAddress;
      }
    }
  } catch (err) {
    // Failover to secondary engine
  }

  // 2. PRIORITAS CADANGAN: BigDataCloud Reverse Geocoding dengan Parse Hierarki Lengkap
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3500);

    const res = await fetch(
      `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${latNum}&longitude=${lngNum}&localityLanguage=id`,
      { signal: controller.signal }
    );
    clearTimeout(timeoutId);

    if (res.ok) {
      const data = await res.json();
      const parts = [];

      // Jalan / Gang / Lingkungan
      if (data.locality) parts.push(data.locality);
      else if (data.neighbourhood) parts.push(data.neighbourhood);

      // Kelurahan / Desa
      const adminList = data.localityInfo?.administrative || [];
      const kelItem = adminList.find(a => a.order === 7 || a.adminLevel === 7 || a.name?.toLowerCase().includes('kel') || a.name?.toLowerCase().includes('desa'));
      if (kelItem && !parts.includes(kelItem.name)) {
        parts.push(kelItem.name);
      }

      // Kecamatan
      const kecItem = adminList.find(a => a.order === 6 || a.adminLevel === 6 || a.name?.toLowerCase().includes('kecamatan'));
      if (kecItem) {
        const cleanKec = `Kec. ${kecItem.name.replace(/^Kecamatan\s+/i, '')}`;
        if (!parts.includes(cleanKec)) parts.push(cleanKec);
      } else if (data.city && data.city.toLowerCase().includes('kec')) {
        parts.push(data.city);
      }

      // Kota / Kabupaten
      const kotaItem = adminList.find(a => a.order === 5 || a.adminLevel === 5 || a.name?.toLowerCase().includes('kota') || a.name?.toLowerCase().includes('kabupaten'));
      if (kotaItem && !parts.includes(kotaItem.name)) {
        parts.push(kotaItem.name);
      } else if (data.city && !parts.includes(data.city)) {
        parts.push(data.city);
      }

      // Provinsi
      if (data.principalSubdivision && !parts.includes(data.principalSubdivision)) {
        parts.push(data.principalSubdivision);
      }

      // Kode Pos
      if (data.postcode) {
        parts.push(data.postcode);
      }

      if (parts.length > 0) {
        const fullAddress = parts.join(', ');
        geocodeCache.set(cacheKey, fullAddress);
        return fullAddress;
      }
    }
  } catch (err) {
    // Failover
  }

  // Fallback representatif berdasarkan koordinat
  const fallback = fallbackName || `Area Koordinat (${latNum.toFixed(5)}, ${lngNum.toFixed(5)})`;
  return fallback;
}
