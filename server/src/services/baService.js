const { get, run, query } = require('../config/database');
const { calculateProgress } = require('../utils/helpers');
const { logAudit } = require('./auditService');
const { sendNotification } = require('./notificationService');

/**
 * Generate BA Opname Document for Approved Work Order
 */
async function generateBaOpname({ workOrderId, templateId, customBaNumber, baDate }, currentUser, ipAddress) {
  const now = new Date().toISOString();
  const workOrder = await get(`
    SELECT wo.*, v.name as vendor_name, v.code as vendor_code, a.name as area_name, pic.name as pic_name, pic.phone as pic_phone
    FROM work_orders wo
    LEFT JOIN vendors v ON wo.vendor_id = v.id
    LEFT JOIN areas a ON wo.area_id = a.id
    LEFT JOIN users pic ON wo.pic_user_id = pic.id
    WHERE wo.id = ?
  `, [workOrderId]);

  if (!workOrder) throw new Error('Work Order tidak ditemukan');

  // Allow generating BA if APPROVED, BA_OPNAME, or COMPLETED
  if (!['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(workOrder.status)) {
    throw new Error(`BA Opname hanya dapat diterbitkan untuk pekerjaan yang telah disetujui (Status saat ini: ${workOrder.status}).`);
  }

  // Generate unique BA Number
  let baNumber = customBaNumber;
  if (!baNumber) {
    const year = new Date().getFullYear();
    const month = String(new Date().getMonth() + 1).padStart(2, '0');
    const countRes = await get(`SELECT COUNT(*) as count FROM ba_documents`);
    const seq = String(countRes.count + 1).padStart(4, '0');
    baNumber = `BA/${year}/${month}/${seq}`;
  }

  // Get Template
  let tmpl;
  if (templateId) {
    tmpl = await get(`SELECT * FROM document_templates WHERE id = ?`, [templateId]);
  }
  if (!tmpl) {
    tmpl = await get(`SELECT * FROM document_templates WHERE is_default = 1 LIMIT 1`);
  }

  // Gather check-in, sub-items, and evidence photos
  const checkIn = await get(`SELECT * FROM check_ins WHERE work_order_id = ? ORDER BY id DESC LIMIT 1`, [workOrderId]);
  const items = await query(`
    SELECT woi.*, jt.name as job_type_name
    FROM work_order_items woi
    LEFT JOIN job_types jt ON woi.job_type_id = jt.id
    WHERE woi.work_order_id = ?
    ORDER BY woi.id ASC
  `, [workOrderId]);
  const photos = await query(`SELECT * FROM evidence_photos WHERE work_order_id = ? ORDER BY stage ASC, sequence ASC`, [workOrderId]);

  const contentJson = JSON.stringify({
    ba_number: baNumber,
    ba_date: baDate || new Date().toISOString().split('T')[0],
    work_order: {
      id: workOrder.id,
      spk_number: workOrder.spk_number,
      title: workOrder.title,
      vendor_name: workOrder.vendor_name,
      location_name: workOrder.location_name,
      area_name: workOrder.area_name,
      pic_name: workOrder.pic_name,
      pic_phone: workOrder.pic_phone,
      start_date: workOrder.start_date,
      deadline: workOrder.deadline
    },
    items: items || [],
    check_in: checkIn || null,
    photos: photos.map(p => ({
      item_id: p.item_id,
      stage: p.stage,
      file_path: p.file_path,
      file_name: p.file_name,
      file_hash: p.file_hash,
      server_timestamp: p.server_timestamp,
      latitude: p.latitude,
      longitude: p.longitude
    }))
  });

  // Check if BA already exists, update or insert
  const existingBa = await get(`SELECT id FROM ba_documents WHERE work_order_id = ?`, [workOrderId]);
  let baId;

  if (existingBa) {
    await run(`
      UPDATE ba_documents
      SET ba_number = ?, ba_date = ?, template_id = ?, generated_by = ?, content_json = ?, status = 'FINAL'
      WHERE id = ?
    `, [baNumber, baDate || new Date().toISOString().split('T')[0], tmpl ? tmpl.id : null, currentUser.id, contentJson, existingBa.id]);
    baId = existingBa.id;
  } else {
    const res = await run(`
      INSERT INTO ba_documents (work_order_id, ba_number, ba_date, template_id, generated_by, content_json, status, created_at)
      VALUES (?, ?, ?, ?, ?, ?, 'FINAL', ?)
    `, [workOrderId, baNumber, baDate || new Date().toISOString().split('T')[0], tmpl ? tmpl.id : null, currentUser.id, contentJson, now]);
    baId = res.lastID;
  }

  // Advance status to BA_OPNAME (or keep COMPLETED)
  let newStatus = 'BA_OPNAME';
  if (workOrder.status === 'COMPLETED') newStatus = 'COMPLETED';
  const progress = calculateProgress(newStatus);
  await run(`UPDATE work_orders SET status = ?, progress_percent = ?, updated_at = ? WHERE id = ?`, [newStatus, progress, now, workOrderId]);

  // Send Notification to Vendor
  const vendorUser = await get(`SELECT phone, name FROM users WHERE vendor_id = ? AND role = 'VENDOR' LIMIT 1`, [workOrder.vendor_id]);
  if (vendorUser && vendorUser.phone) {
    await sendNotification({
      recipient: vendorUser.phone,
      messageType: 'BA_OPNAME_ISSUED',
      payload: { workOrderId, baNumber, spkNumber: workOrder.spk_number },
      text: `Yth. ${vendorUser.name}, Berita Acara Opname (Nomor: ${baNumber}) untuk pekerjaan [${workOrder.spk_number}] telah resmi diterbitkan dan dapat diunduh pada portal vendor.`
    });
  }

  // Trigger in-app notification feed for Supervisor & Client
  try {
    const notificationFeedService = require('./notificationFeedService');
    await notificationFeedService.createNotification({
      work_order_id: workOrderId,
      client_id: workOrder.vendor_id,
      category: 'BA_ISSUED',
      title: `Berita Acara Terbit: ${workOrder.spk_number}`,
      message: `Dokumen Berita Acara resmi (${baNumber}) telah disahkan untuk ${workOrder.title} di cabang ${workOrder.location_name}.`
    });
  } catch (e) {
    console.error('Failed to dispatch BA notification:', e);
  }

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'GENERATE_BA_OPNAME',
    entityType: 'BA_DOCUMENT',
    entityId: baId,
    newValue: { baNumber, workOrderId },
    ipAddress
  });

  return await getBaDocumentById(baId);
}

/**
 * Get BA Document detail by ID
 */
async function getBaDocumentById(id, currentUser) {
  const ba = await get(`
    SELECT ba.*, wo.spk_number, wo.title as work_order_title, wo.vendor_id, v.name as vendor_name, u.name as generator_name,
           t.header_html, t.footer_html, t.body_template, t.logo_url, t.header_image_url, t.background_image_url, t.footer_image_url,
           t.signatories_json, t.signatory_first_party_name, t.signatory_first_party_role, t.signatory_second_party_name, t.signatory_second_party_role
    FROM ba_documents ba
    JOIN work_orders wo ON ba.work_order_id = wo.id
    LEFT JOIN vendors v ON wo.vendor_id = v.id
    LEFT JOIN users u ON ba.generated_by = u.id
    LEFT JOIN document_templates t ON ba.template_id = t.id
    WHERE ba.id = ?
  `, [id]);

  if (!ba) throw new Error('Berita Acara tidak ditemukan');

  // Vendor isolation check
  if (currentUser && currentUser.role === 'VENDOR' && ba.vendor_id !== currentUser.vendor_id) {
    throw new Error('Akses ditolak: Anda tidak memiliki akses ke dokumen Berita Acara vendor lain.');
  }

  if (ba.content_json) {
    ba.content = JSON.parse(ba.content_json);
  }
  return ba;
}

/**
 * Get all generated BA documents
 */
async function getBaDocuments({ currentUser }) {
  let sql = `
    SELECT ba.*, wo.spk_number, wo.title as work_order_title, wo.vendor_id, v.name as vendor_name, u.name as generator_name
    FROM ba_documents ba
    JOIN work_orders wo ON ba.work_order_id = wo.id
    LEFT JOIN vendors v ON wo.vendor_id = v.id
    LEFT JOIN users u ON ba.generated_by = u.id
    WHERE 1=1
  `;
  const params = [];

  if (currentUser.role === 'VENDOR') {
    sql += ` AND wo.vendor_id = ?`;
    params.push(currentUser.vendor_id);
  }

  sql += ` ORDER BY ba.id DESC`;
  return await query(sql, params);
}

/**
 * Mark Work Order as COMPLETED
 */
async function completeWorkOrder(workOrderId, currentUser, ipAddress) {
  const now = new Date().toISOString();
  const workOrder = await get(`SELECT * FROM work_orders WHERE id = ?`, [workOrderId]);
  if (!workOrder) throw new Error('Work Order tidak ditemukan');

  const newStatus = 'COMPLETED';
  const progress = 100;
  await run(`UPDATE work_orders SET status = ?, progress_percent = ?, updated_at = ? WHERE id = ?`, [newStatus, progress, now, workOrderId]);

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'COMPLETE_WORK_ORDER',
    entityType: 'WORK_ORDER',
    entityId: workOrderId,
    oldValue: { status: workOrder.status },
    newValue: { status: newStatus },
    ipAddress
  });

  return { success: true, status: newStatus, progress };
}

module.exports = {
  generateBaOpname,
  getBaDocumentById,
  getBaDocuments,
  completeWorkOrder
};
