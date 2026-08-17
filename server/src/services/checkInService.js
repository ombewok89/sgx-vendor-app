const { get, run, query } = require('../config/database');
const { calculateProgress, calculateDistanceMeters } = require('../utils/helpers');
const { logAudit } = require('./auditService');

/**
 * Performs official GPS check-in by field team member with Geofence verification
 */
async function performCheckIn({ workOrderId, latitude, longitude, accuracy, clientTimestamp, addressNote }, currentUser, ipAddress) {
  const now = new Date().toISOString();

  if (!latitude || !longitude) {
    throw new Error('Koordinat GPS (latitude & longitude) wajib tersedia untuk check-in resmi.');
  }

  const workOrder = await get(`SELECT * FROM work_orders WHERE id = ?`, [workOrderId]);
  if (!workOrder) {
    throw new Error('Work Order tidak ditemukan');
  }

  // Authorization check: User must be PIC or assigned member
  if (currentUser.role === 'FIELD_TEAM') {
    const assignment = await get(
      `SELECT * FROM work_order_assignments WHERE work_order_id = ? AND user_id = ?`,
      [workOrderId, currentUser.id]
    );
    if (!assignment && workOrder.pic_user_id !== currentUser.id) {
      throw new Error('Akses ditolak: Anda tidak ditugaskan pada pekerjaan ini.');
    }
  }

  // Geofencing verification calculation (Point 3.1)
  let distanceMeters = null;
  let isOutOfRange = 0;
  let geofenceStatus = 'NOT_CONFIGURED';

  const targetLat = parseFloat(workOrder.target_lat);
  const targetLng = parseFloat(workOrder.target_lng);
  const checkinLat = parseFloat(latitude);
  const checkinLng = parseFloat(longitude);

  if (!isNaN(targetLat) && !isNaN(targetLng) && !isNaN(checkinLat) && !isNaN(checkinLng) && targetLat !== 0 && targetLng !== 0) {
    distanceMeters = calculateDistanceMeters(checkinLat, checkinLng, targetLat, targetLng);
    const maxRadius = workOrder.geofence_radius || 200;
    const isGeofenceRequired = workOrder.require_geofence !== 0; // Checklist toggle

    if (isGeofenceRequired) {
      if (distanceMeters <= maxRadius) {
        isOutOfRange = 0;
        geofenceStatus = 'VERIFIED_MATCH';
      } else {
        isOutOfRange = 1;
        geofenceStatus = 'OUT_OF_RANGE';
      }
    } else {
      geofenceStatus = 'VERIFICATION_DISABLED'; // Checklist is OFF
    }
  }

  // Insert check-in record
  const res = await run(`
    INSERT INTO check_ins (
      work_order_id, user_id, server_timestamp, client_timestamp,
      latitude, longitude, accuracy, address_note,
      distance_meters, is_out_of_range, geofence_status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `, [
    workOrderId,
    currentUser.id,
    now,
    clientTimestamp || now,
    checkinLat,
    checkinLng,
    accuracy || 0,
    addressNote || '',
    distanceMeters,
    isOutOfRange,
    geofenceStatus,
    now
  ]);

  // If status is ASSIGNED or READY, update status to CHECKED_IN
  let newStatus = workOrder.status;
  if (['READY', 'ASSIGNED'].includes(workOrder.status)) {
    newStatus = 'CHECKED_IN';
    const progress = calculateProgress(newStatus);
    await run(`
      UPDATE work_orders SET status = ?, progress_percent = ?, updated_at = ? WHERE id = ?
    `, [newStatus, progress, now, workOrderId]);
  }

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'CHECK_IN',
    entityType: 'WORK_ORDER',
    entityId: workOrderId,
    newValue: { latitude, longitude, accuracy, serverTimestamp: now },
    ipAddress
  });

  // Trigger in-app notification feed for Supervisor & Client
  try {
    const notificationFeedService = require('./notificationFeedService');
    await notificationFeedService.createNotification({
      work_order_id: workOrderId,
      client_id: workOrder.vendor_id,
      category: 'GPS_CHECKIN',
      title: `Teknisi Tiba di Lokasi: ${workOrder.spk_number}`,
      message: `${currentUser.name} telah check-in GPS terverifikasi di cabang ${workOrder.location_name}.`
    });
  } catch (e) {
    console.error('Failed to dispatch check-in notification:', e);
  }

  return await get(`SELECT * FROM check_ins WHERE id = ?`, [res.lastID]);
}

module.exports = {
  performCheckIn
};
