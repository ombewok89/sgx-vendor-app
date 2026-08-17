const crypto = require('crypto');
const fs = require('fs');

/**
 * Generates a SHA-256 hash for a given file buffer or file path
 */
function calculateFileHash(bufferOrPath) {
  const hash = crypto.createHash('sha256');
  if (typeof bufferOrPath === 'string') {
    const fileBuffer = fs.readFileSync(bufferOrPath);
    hash.update(fileBuffer);
  } else {
    hash.update(bufferOrPath);
  }
  return hash.digest('hex');
}

/**
 * Calculates haversine distance in meters between two GPS coordinates
 */
function calculateDistanceMeters(lat1, lon1, lat2, lon2) {
  if (!lat1 || !lon1 || !lat2 || !lon2) return null;
  const R = 6371e3; // Earth radius in meters
  const φ1 = (lat1 * Math.PI) / 180;
  const φ2 = (lat2 * Math.PI) / 180;
  const Δφ = ((lat2 - lat1) * Math.PI) / 180;
  const Δλ = ((lon2 - lon1) * Math.PI) / 180;

  const a =
    Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
    Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

  return Math.round(R * c);
}

/**
 * Standard Status Flow definitions
 */
const WORK_ORDER_STATUSES = [
  'DRAFT',
  'READY',
  'ASSIGNED',
  'CHECKED_IN',
  'IN_PROGRESS',
  'SUBMITTED',
  'UNDER_REVIEW',
  'REVISION',
  'APPROVED',
  'BA_OPNAME',
  'COMPLETED'
];

/**
 * Calculates automatic progress percentage based on status and evidence
 */
function calculateProgress(status, evidenceCounts = { before: 0, process: 0, after: 0 }) {
  switch (status) {
    case 'DRAFT': return 5;
    case 'READY': return 10;
    case 'ASSIGNED': return 25;
    case 'CHECKED_IN': return 35;
    case 'IN_PROGRESS': {
      let p = 40;
      if (evidenceCounts.before > 0) p += 15;
      if (evidenceCounts.process > 0) p += 15;
      if (evidenceCounts.after > 0) p += 15;
      return Math.min(85, p);
    }
    case 'SUBMITTED':
    case 'UNDER_REVIEW': return 90;
    case 'REVISION': return 75;
    case 'APPROVED': return 95;
    case 'BA_OPNAME': return 98;
    case 'COMPLETED': return 100;
    default: return 0;
  }
}

module.exports = {
  calculateFileHash,
  calculateDistanceMeters,
  WORK_ORDER_STATUSES,
  calculateProgress
};
