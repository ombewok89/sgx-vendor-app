const { query, get, run } = require('../config/database');

/**
 * Notification Feed Service
 * Real-time In-App Notification Feed with Dynamic Client Isolation & Multi-Role Support
 */

async function createNotification({
  work_order_id = null,
  client_id = null,
  target_user_id = null,
  target_role = 'ALL',
  category = 'GENERAL', // 'GPS_CHECKIN', 'EVIDENCE_UPLOAD', 'ISSUE_REPORTED', 'BA_ISSUED', 'REVIEW', 'ASSIGNMENT'
  title,
  message
}) {
  const now = new Date().toISOString();

  // If client_id is not provided but work_order_id is, resolve client_id from work_order
  let resolvedClientId = client_id;
  if (!resolvedClientId && work_order_id) {
    const wo = await get('SELECT vendor_id FROM work_orders WHERE id = ?', [work_order_id]);
    if (wo) {
      resolvedClientId = wo.vendor_id;
    }
  }

  const result = await run(`
    INSERT INTO notifications_feed (
      work_order_id, client_id, target_user_id, target_role, category, title, message, is_read, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)
  `, [
    work_order_id,
    resolvedClientId,
    target_user_id,
    target_role,
    category,
    title,
    message,
    now
  ]);

  return result.id;
}

async function getNotificationsForUser(user, options = {}) {
  const limit = parseInt(options.limit || '30', 10);
  const userId = user?.id || 1;
  const role = user?.role || 'ADMIN';
  const clientId = user?.vendor_id || null;

  let rows = [];

  if (role === 'VENDOR') {
    // STRICT CLIENT ISOLATION:
    // Any existing or newly created client (user.vendor_id) will only see notifications related to their own SPKs/company
    if (!clientId) {
      return [];
    }

    rows = await query(`
      SELECT nf.*, 
             wo.spk_number, 
             wo.title as work_order_title, 
             wo.location_name,
             (CASE WHEN urn.id IS NOT NULL THEN 1 ELSE nf.is_read END) as is_read
      FROM notifications_feed nf
      LEFT JOIN work_orders wo ON wo.id = nf.work_order_id
      LEFT JOIN user_read_notifications urn ON urn.notification_id = nf.id AND urn.user_id = ?
      WHERE (nf.client_id = ? OR wo.vendor_id = ? OR wo.created_by = ?)
      ORDER BY nf.id DESC
      LIMIT ?
    `, [userId, clientId, clientId, userId, limit]);

  } else if (role === 'FIELD_TEAM') {
    // Field team members see team-relevant updates
    rows = await query(`
      SELECT nf.*, 
             wo.spk_number, 
             wo.title as work_order_title, 
             wo.location_name,
             (CASE WHEN urn.id IS NOT NULL THEN 1 ELSE nf.is_read END) as is_read
      FROM notifications_feed nf
      LEFT JOIN work_orders wo ON wo.id = nf.work_order_id
      LEFT JOIN user_read_notifications urn ON urn.notification_id = nf.id AND urn.user_id = ?
      WHERE (nf.target_role IN ('ALL', 'FIELD_TEAM') OR nf.target_user_id = ?)
      ORDER BY nf.id DESC
      LIMIT ?
    `, [userId, userId, limit]);

  } else {
    // ADMIN & SUPERUSER: Omnipresent view of all operational feeds
    rows = await query(`
      SELECT nf.*, 
             wo.spk_number, 
             wo.title as work_order_title, 
             wo.location_name,
             v.name as client_name,
             (CASE WHEN urn.id IS NOT NULL THEN 1 ELSE nf.is_read END) as is_read
      FROM notifications_feed nf
      LEFT JOIN work_orders wo ON wo.id = nf.work_order_id
      LEFT JOIN vendors v ON (v.id = nf.client_id OR v.id = wo.vendor_id)
      LEFT JOIN user_read_notifications urn ON urn.notification_id = nf.id AND urn.user_id = ?
      ORDER BY nf.id DESC
      LIMIT ?
    `, [userId, limit]);
  }

  return rows;
}

async function markAsRead(notificationId, userId) {
  const now = new Date().toISOString();
  try {
    await run(`
      INSERT OR REPLACE INTO user_read_notifications (notification_id, user_id, read_at)
      VALUES (?, ?, ?)
    `, [notificationId, userId, now]);
  } catch (e) {
    // Fallback direct update
    await run(`UPDATE notifications_feed SET is_read = 1 WHERE id = ?`, [notificationId]);
  }
}

async function markAllAsRead(userId, user) {
  const notifs = await getNotificationsForUser(user, { limit: 100 });
  const now = new Date().toISOString();
  for (const n of notifs) {
    try {
      await run(`
        INSERT OR REPLACE INTO user_read_notifications (notification_id, user_id, read_at)
        VALUES (?, ?, ?)
      `, [n.id, userId, now]);
    } catch (e) {}
  }
}

/**
 * Auto-Seed Initial Dynamic Notifications for Testing
 */
async function seedInitialNotifications() {
  const count = await get('SELECT COUNT(*) as count FROM notifications_feed');
  if (count && count.count > 0) return;

  const orders = await query('SELECT * FROM work_orders LIMIT 5');
  const now = new Date();

  for (let i = 0; i < orders.length; i++) {
    const order = orders[i];
    const pastMinutes = (i + 1) * 15;
    const timeStr = new Date(now.getTime() - pastMinutes * 60000).toISOString();

    if (order.status === 'COMPLETED' || order.status === 'BA_OPNAME') {
      await run(`
        INSERT INTO notifications_feed (work_order_id, client_id, target_role, category, title, message, is_read, created_at)
        VALUES (?, ?, 'ALL', 'BA_ISSUED', ?, ?, 0, ?)
      `, [
        order.id,
        order.vendor_id,
        `Berita Acara (BA) Terbit: ${order.spk_number}`,
        `Pekerjaan fisik ${order.title} telah selesai 100% dan dokumen Berita Acara resmi telah disahkan siap unduh.`,
        timeStr
      ]);
    } else if (order.status === 'SUBMITTED' || order.status === 'APPROVED') {
      await run(`
        INSERT INTO notifications_feed (work_order_id, client_id, target_role, category, title, message, is_read, created_at)
        VALUES (?, ?, 'ALL', 'EVIDENCE_UPLOAD', ?, ?, 0, ?)
      `, [
        order.id,
        order.vendor_id,
        `Evidensi Foto Diunggah: ${order.spk_number}`,
        `Teknisi lapangan telah menyelesaikan dan mengunggah seluruh foto bukti fisik (Before, Process, After).`,
        timeStr
      ]);
    } else {
      await run(`
        INSERT INTO notifications_feed (work_order_id, client_id, target_role, category, title, message, is_read, created_at)
        VALUES (?, ?, 'ALL', 'GPS_CHECKIN', ?, ?, 0, ?)
      `, [
        order.id,
        order.vendor_id,
        `Teknisi Check-In di Lokasi: ${order.spk_number}`,
        `Tim teknisi SGX telah tiba dan melakukan verifikasi check-in GPS di cabang ${order.location_name}.`,
        timeStr
      ]);
    }
  }
}

module.exports = {
  createNotification,
  getNotificationsForUser,
  markAsRead,
  markAllAsRead,
  seedInitialNotifications
};
