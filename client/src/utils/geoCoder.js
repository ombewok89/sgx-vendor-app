/**
 * Reverse Geocoding Utility
 * Converts Latitude & Longitude into exact structured Indonesian street address,
 * village/subdistrict, district, city/regency, province, and postal code.
 */

const geocodeCache = new Map();

export async function reverseGeocodeCoordinates(latitude, longitude, fallbackName = '') {
  if (latitude == null || longitude == null) {
    return fallbackName || 'Indonesia';
  }

  const cacheKey = `${Number(latitude).toFixed(4)},${Number(longitude).toFixed(4)}`;
  if (geocodeCache.has(cacheKey)) {
    return geocodeCache.get(cacheKey);
  }

  // 1. Try BigDataCloud reverse geocode client (Fast, generous rate limits, Indonesian localization)
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3500);

    const res = await fetch(
      `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${latitude}&longitude=${longitude}&localityLanguage=id`,
      { signal: controller.signal }
    );
    clearTimeout(timeoutId);

    if (res.ok) {
      const data = await res.json();
      const parts = [];

      // Village / Kelurahan / Suburb
      if (data.locality) parts.push(data.locality);
      else if (data.neighbourhood) parts.push(data.neighbourhood);

      // District / Kecamatan
      if (data.city && data.city.toLowerCase().includes('kec')) {
        parts.push(data.city);
      } else if (data.localityInfo?.administrative) {
        const district = data.localityInfo.administrative.find(
          (a) => a.order === 6 || a.name?.toLowerCase().includes('kecamatan') || a.adminLevel === 6
        );
        if (district) parts.push(district.name.replace(/^Kecamatan\s+/i, 'Kec. '));
      }

      // Regency / City / Kabupaten / Kota
      const city = data.principalSubdivision ? data.city : (data.city || data.localityInfo?.administrative?.find(a => a.order === 5)?.name);
      if (city && !parts.includes(city)) parts.push(city);

      // Province / State
      if (data.principalSubdivision && !parts.includes(data.principalSubdivision)) {
        parts.push(data.principalSubdivision);
      }

      // Postcode
      if (data.postcode) parts.push(data.postcode);

      if (parts.length > 0) {
        const fullAddress = parts.join(', ');
        geocodeCache.set(cacheKey, fullAddress);
        return fullAddress;
      }
    }
  } catch (err) {
    // Failover to secondary OpenStreetMap Nominatim
  }

  // 2. Try OpenStreetMap Nominatim with timeout
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3500);

    const res = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&zoom=18&addressdetails=1`,
      {
        headers: { 'Accept-Language': 'id' },
        signal: controller.signal
      }
    );
    clearTimeout(timeoutId);

    if (res.ok) {
      const data = await res.json();
      const addr = data.address || {};
      const parts = [];

      // Road / Place
      if (addr.road) parts.push(addr.road);
      else if (addr.suburb) parts.push(addr.suburb);
      else if (addr.village) parts.push(addr.village);

      // District / Kecamatan
      if (addr.city_district) parts.push(`Kec. ${addr.city_district.replace(/^Kecamatan\s+/i, '')}`);
      else if (addr.county && addr.county.toLowerCase().includes('kec')) parts.push(addr.county);

      // City / Regency
      const city = addr.city || addr.town || addr.municipality || addr.county;
      if (city) parts.push(city);

      // Province
      if (addr.state) parts.push(addr.state);

      // Postcode
      if (addr.postcode) parts.push(addr.postcode);

      if (parts.length > 0) {
        const fullAddress = parts.join(', ');
        geocodeCache.set(cacheKey, fullAddress);
        return fullAddress;
      }
    }
  } catch (err) {
    // If network fails or offline, return fallback
  }

  const fallback = fallbackName || `Area Koordinat ${Number(latitude).toFixed(4)}, ${Number(longitude).toFixed(4)}`;
  geocodeCache.set(cacheKey, fallback);
  return fallback;
}
