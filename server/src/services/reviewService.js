const { get, run, query } = require('../config/database');
const { calculateProgress } = require('../utils/helpers');
const { logAudit } = require('./auditService');
const { sendNotification } = require('./notificationService');

/**
 * Approve Work Order (Only Admin / Superuser)
 */
async function approveWorkOrder({ workOrderId, reviewNotes }, currentUser, ipAddress) {
  const now = new Date().toISOString();
  const workOrder = await get(`SELECT * FROM work_orders WHERE id = ?`, [workOrderId]);
  if (!workOrder) throw new Error('Work Order tidak ditemukan');

  // Insert review record
  const revRes = await run(`
    INSERT INTO reviews (work_order_id, reviewer_user_id, status, review_notes, created_at)
    VALUES (?, ?, 'APPROVED', ?, ?)
  `, [workOrderId, currentUser.id, reviewNotes || 'Dokumentasi dan hasil pekerjaan disetujui.', now]);

  // Update work order status to APPROVED
  const newStatus = 'APPROVED';
  const progress = calculateProgress(newStatus);
  await run(`
    UPDATE work_orders SET status = ?, progress_percent = ?, updated_at = ? WHERE id = ?
  `, [newStatus, progress, now, workOrderId]);

  // Notify Field Team & Vendor
  if (workOrder.pic_user_id) {
    const pic = await get(`SELECT phone, name FROM users WHERE id = ?`, [workOrder.pic_user_id]);
    if (pic && pic.phone) {
      await sendNotification({
        recipient: pic.phone,
        messageType: 'WORK_ORDER_APPROVED',
        payload: { workOrderId, spkNumber: workOrder.spk_number },
        text: `Selamat ${pic.name}, hasil pekerjaan [${workOrder.spk_number}] - ${workOrder.title} telah DISETUJUI oleh Admin. Berita Acara (BA) dapat segera diproses.`
      });
    }
  }

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'APPROVE_WORK_ORDER',
    entityType: 'WORK_ORDER',
    entityId: workOrderId,
    oldValue: { status: workOrder.status },
    newValue: { status: newStatus, reviewNotes },
    ipAddress
  });

  return { success: true, status: newStatus, reviewId: revRes.lastID };
}

/**
 * Request Specific Revision (Only Admin / Superuser)
 */
async function requestRevision({ workOrderId, targetStage, reason }, currentUser, ipAddress) {
  const now = new Date().toISOString();
  if (!reason || !reason.trim()) {
    throw new Error('Alasan revisi wajib diisi secara jelas dan spesifik.');
  }

  const workOrder = await get(`SELECT * FROM work_orders WHERE id = ?`, [workOrderId]);
  if (!workOrder) throw new Error('Work Order tidak ditemukan');

  // Insert review record
  const revRes = await run(`
    INSERT INTO reviews (work_order_id, reviewer_user_id, status, review_notes, created_at)
    VALUES (?, ?, 'REVISION_REQUESTED', ?, ?)
  `, [workOrderId, currentUser.id, reason, now]);

  // Insert structured revision row
  const revisionRes = await run(`
    INSERT INTO revisions (work_order_id, review_id, target_stage, reason, requested_by, requested_at, status)
    VALUES (?, ?, ?, ?, ?, ?, 'OPEN')
  `, [workOrderId, revRes.lastID, targetStage || 'AFTER', reason, currentUser.id, now]);

  // Update work order status to REVISION
  const newStatus = 'REVISION';
  const progress = calculateProgress(newStatus);
  await run(`
    UPDATE work_orders SET status = ?, progress_percent = ?, updated_at = ? WHERE id = ?
  `, [newStatus, progress, now, workOrderId]);

  // Notify Field Team PIC
  if (workOrder.pic_user_id) {
    const pic = await get(`SELECT phone, name FROM users WHERE id = ?`, [workOrder.pic_user_id]);
    if (pic && pic.phone) {
      await sendNotification({
        recipient: pic.phone,
        messageType: 'REVISION_REQUESTED',
        payload: { workOrderId, spkNumber: workOrder.spk_number, targetStage, reason },
        text: `Perhatian ${pic.name}: Pekerjaan [${workOrder.spk_number}] memerlukan REVISI pada bagian [${targetStage}]. Catatan: "${reason}". Mohon perbaiki dan submit kembali.`
      });
    }
  }

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'REQUEST_REVISION',
    entityType: 'WORK_ORDER',
    entityId: workOrderId,
    oldValue: { status: workOrder.status },
    newValue: { status: newStatus, targetStage, reason },
    ipAddress
  });

  return { success: true, status: newStatus, revisionId: revisionRes.lastID };
}

module.exports = {
  approveWorkOrder,
  requestRevision
};
