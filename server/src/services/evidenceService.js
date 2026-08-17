const path = require('path');
const fs = require('fs');
const { get, run, query } = require('../config/database');
const { calculateFileHash, calculateProgress } = require('../utils/helpers');
const { logAudit } = require('./auditService');

/**
 * Upload and record a structured photo evidence
 */
async function uploadPhotoEvidence({ file, workOrderId, itemId, stage, sequence, latitude, longitude, accuracy, notes }, currentUser, ipAddress) {
  const now = new Date().toISOString();

  if (!file) {
    throw new Error('File foto bukti wajib diunggah.');
  }

  const validStages = ['BEFORE', 'PROCESS', 'AFTER', 'ISSUE'];
  const normalizedStage = (stage || 'AFTER').toUpperCase();
  if (!validStages.includes(normalizedStage)) {
    throw new Error(`Stage '${stage}' tidak valid. Pilihan: ${validStages.join(', ')}`);
  }

  const workOrder = await get(`SELECT * FROM work_orders WHERE id = ?`, [workOrderId]);
  if (!workOrder) {
    throw new Error('Work Order tidak ditemukan');
  }

  // 1. Vendor Isolation check (Point 1.3)
  if (currentUser.role === 'VENDOR' && workOrder.vendor_id !== currentUser.vendor_id) {
    throw new Error('Akses ditolak: Anda tidak memiliki akses ke pekerjaan vendor lain.');
  }

  // 2. Field Team Assignment IDOR check (Point 1.4)
  if (currentUser.role === 'FIELD_TEAM') {
    const assignment = await get(
      `SELECT * FROM work_order_assignments WHERE work_order_id = ? AND user_id = ?`,
      [workOrderId, currentUser.id]
    );
    if (!assignment && workOrder.pic_user_id !== currentUser.id) {
      throw new Error('Akses ditolak: Anda tidak ditugaskan pada pekerjaan ini.');
    }
  }

  // Calculate SHA-256 Hash for evidence integrity
  const fileHash = calculateFileHash(file.path);
  const relativePath = `/uploads/${file.filename}`;

  // Get next sequence for stage if not provided
  let seq = parseInt(sequence, 10);
  if (isNaN(seq) || seq <= 0) {
    let sqlSeq = `SELECT MAX(sequence) as maxSeq FROM evidence_photos WHERE work_order_id = ? AND stage = ?`;
    const paramsSeq = [workOrderId, normalizedStage];
    if (itemId) {
      sqlSeq += ` AND item_id = ?`;
      paramsSeq.push(itemId);
    }
    const lastSeq = await get(sqlSeq, paramsSeq);
    seq = (lastSeq && lastSeq.maxSeq ? lastSeq.maxSeq : 0) + 1;
  }

  const res = await run(`
    INSERT INTO evidence_photos (
      work_order_id, item_id, user_id, stage, sequence, file_path, file_name,
      file_size, mime_type, file_hash, server_timestamp,
      latitude, longitude, accuracy, notes, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `, [
    workOrderId,
    itemId ? parseInt(itemId, 10) : null,
    currentUser.id,
    normalizedStage,
    seq,
    relativePath,
    file.originalname,
    file.size,
    file.mimetype,
    fileHash,
    now,
    latitude ? parseFloat(latitude) : null,
    longitude ? parseFloat(longitude) : null,
    accuracy ? parseFloat(accuracy) : null,
    notes || '',
    now
  ]);

  // Update status to IN_PROGRESS if CHECKED_IN or ASSIGNED
  if (['CHECKED_IN', 'ASSIGNED'].includes(workOrder.status)) {
    const newStatus = 'IN_PROGRESS';
    const progress = calculateProgress(newStatus, { before: 1, process: 0, after: 0 });
    await run(`UPDATE work_orders SET status = ?, progress_percent = ?, updated_at = ? WHERE id = ?`, [newStatus, progress, now, workOrderId]);
  }

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'UPLOAD_EVIDENCE_PHOTO',
    entityType: 'EVIDENCE_PHOTO',
    entityId: res.lastID,
    newValue: { workOrderId, stage: normalizedStage, fileHash, fileName: file.originalname, filePath: relativePath },
    ipAddress
  });

  // Trigger in-app notification feed for Supervisor & Client
  try {
    const notificationFeedService = require('./notificationFeedService');
    await notificationFeedService.createNotification({
      work_order_id: workOrderId,
      client_id: workOrder.vendor_id,
      category: 'EVIDENCE_UPLOAD',
      title: `Foto ${normalizedStage} Diunggah: ${workOrder.spk_number}`,
      message: `Bukti foto tahap ${normalizedStage} untuk ${workOrder.title} telah diunggah oleh ${currentUser.name}.`
    });
  } catch (e) {
    console.error('Failed to dispatch photo notification:', e);
  }

  return await get(`SELECT * FROM evidence_photos WHERE id = ?`, [res.lastID]);
}

/**
 * Report Technical Issue / Kendala
 */
async function reportIssue({ workOrderId, hasIssue, issueType, notes }, currentUser, ipAddress) {
  const now = new Date().toISOString();
  const workOrder = await get(`SELECT * FROM work_orders WHERE id = ?`, [workOrderId]);
  if (!workOrder) throw new Error('Work Order tidak ditemukan');

  // 1. Vendor Isolation check (Point 1.3)
  if (currentUser.role === 'VENDOR' && workOrder.vendor_id !== currentUser.vendor_id) {
    throw new Error('Akses ditolak: Anda tidak memiliki akses ke pekerjaan vendor lain.');
  }

  // 2. Field Team Assignment IDOR check (Point 1.4)
  if (currentUser.role === 'FIELD_TEAM') {
    const assignment = await get(
      `SELECT * FROM work_order_assignments WHERE work_order_id = ? AND user_id = ?`,
      [workOrderId, currentUser.id]
    );
    if (!assignment && workOrder.pic_user_id !== currentUser.id) {
      throw new Error('Akses ditolak: Anda tidak ditugaskan pada pekerjaan ini.');
    }
  }

  const res = await run(`
    INSERT INTO issues (work_order_id, user_id, has_issue, issue_type, notes, created_at)
    VALUES (?, ?, ?, ?, ?, ?)
  `, [
    workOrderId,
    currentUser.id,
    hasIssue ? 1 : 0,
    issueType || '',
    notes || '',
    now
  ]);

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'REPORT_ISSUE',
    entityType: 'WORK_ORDER',
    entityId: workOrderId,
    newValue: { hasIssue, issueType, notes },
    ipAddress
  });

  // Trigger in-app notification feed for Supervisor & Client
  try {
    const notificationFeedService = require('./notificationFeedService');
    await notificationFeedService.createNotification({
      work_order_id: workOrderId,
      client_id: workOrder.vendor_id,
      category: 'ISSUE_REPORTED',
      title: `Kendala Lapangan: ${workOrder.spk_number}`,
      message: `Kendala (${issueType || 'Teknis'}): "${notes}" dilaporkan di cabang ${workOrder.location_name}.`
    });
  } catch (e) {
    console.error('Failed to dispatch issue notification:', e);
  }

  return await get(`SELECT * FROM issues WHERE id = ?`, [res.lastID]);
}

/**
 * Get all evidence photos with filtering for Gallery & Audit
 */
async function getAllEvidencePhotos(filters = {}, currentUser) {
  const { stage, vendorId, areaId, workOrderId, search, limit = 100, offset = 0 } = filters;
  let sql = `
    SELECT ep.*, 
           wo.spk_number, wo.title as work_order_title, wo.location_name,
           v.id as vendor_id, v.name as vendor_name,
           a.id as area_id, a.name as area_name,
           u.name as uploader_name, u.role as uploader_role,
           woi.item_name as work_item_name
    FROM evidence_photos ep
    JOIN work_orders wo ON ep.work_order_id = wo.id
    LEFT JOIN vendors v ON wo.vendor_id = v.id
    LEFT JOIN areas a ON wo.area_id = a.id
    LEFT JOIN users u ON ep.user_id = u.id
    LEFT JOIN work_order_items woi ON ep.item_id = woi.id
    WHERE 1=1
  `;
  const params = [];

  // Vendor isolation scoping (Point 1.3)
  if (currentUser && currentUser.role === 'VENDOR') {
    sql += ` AND wo.vendor_id = ?`;
    params.push(currentUser.vendor_id);
  } else if (vendorId) {
    sql += ` AND wo.vendor_id = ?`;
    params.push(vendorId);
  }

  if (stage && stage !== 'ALL') {
    sql += ` AND ep.stage = ?`;
    params.push(stage.toUpperCase());
  }

  if (areaId) {
    sql += ` AND wo.area_id = ?`;
    params.push(areaId);
  }

  if (workOrderId) {
    sql += ` AND ep.work_order_id = ?`;
    params.push(workOrderId);
  }

  if (search) {
    sql += ` AND (wo.spk_number LIKE ? OR wo.title LIKE ? OR wo.location_name LIKE ? OR ep.notes LIKE ?)`;
    const s = `%${search}%`;
    params.push(s, s, s, s);
  }

  sql += ` ORDER BY ep.id DESC LIMIT ? OFFSET ?`;
  params.push(parseInt(limit, 10), parseInt(offset, 10));

  return await query(sql, params);
}

/**
 * Get all field issues
 */
async function getFieldIssues(filters = {}, currentUser) {
  const { status, issueType, vendorId, workOrderId, search } = filters;
  let sql = `
    SELECT i.*, 
           wo.spk_number, wo.title as work_order_title, wo.location_name,
           v.id as vendor_id, v.name as vendor_name,
           a.name as area_name,
           u.name as reporter_name, u.phone as reporter_phone,
           ru.name as resolver_name
    FROM issues i
    JOIN work_orders wo ON i.work_order_id = wo.id
    LEFT JOIN vendors v ON wo.vendor_id = v.id
    LEFT JOIN areas a ON wo.area_id = a.id
    LEFT JOIN users u ON i.user_id = u.id
    LEFT JOIN users ru ON i.resolved_by = ru.id
    WHERE 1=1
  `;
  const params = [];

  // Vendor isolation scoping (Point 1.3)
  if (currentUser && currentUser.role === 'VENDOR') {
    sql += ` AND wo.vendor_id = ?`;
    params.push(currentUser.vendor_id);
  } else if (vendorId) {
    sql += ` AND wo.vendor_id = ?`;
    params.push(vendorId);
  }

  if (status && status !== 'ALL') {
    sql += ` AND i.status = ?`;
    params.push(status);
  }

  if (issueType && issueType !== 'ALL') {
    sql += ` AND i.issue_type = ?`;
    params.push(issueType);
  }

  if (workOrderId) {
    sql += ` AND i.work_order_id = ?`;
    params.push(workOrderId);
  }

  if (search) {
    sql += ` AND (wo.spk_number LIKE ? OR wo.title LIKE ? OR i.notes LIKE ? OR i.resolution_notes LIKE ?)`;
    const s = `%${search}%`;
    params.push(s, s, s, s);
  }

  sql += ` ORDER BY CASE WHEN i.status = 'OPEN' THEN 0 ELSE 1 END, i.id DESC`;

  return await query(sql, params);
}

/**
 * Resolve / Update field issue
 */
async function resolveIssue(id, { resolution_notes, status = 'RESOLVED' }, currentUser, ipAddress) {
  const now = new Date().toISOString();
  const issue = await get(`SELECT * FROM issues WHERE id = ?`, [id]);
  if (!issue) throw new Error('Kendala teknis tidak ditemukan');

  const workOrder = await get(`SELECT * FROM work_orders WHERE id = ?`, [issue.work_order_id]);
  if (currentUser.role === 'VENDOR' && workOrder && workOrder.vendor_id !== currentUser.vendor_id) {
    throw new Error('Akses ditolak: Anda tidak memiliki akses ke SPK vendor lain.');
  }

  await run(`
    UPDATE issues
    SET status = ?, resolution_notes = ?, resolved_by = ?, resolved_at = ?
    WHERE id = ?
  `, [status, resolution_notes || '', currentUser.id, now, id]);

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'RESOLVE_FIELD_ISSUE',
    entityType: 'ISSUE',
    entityId: id,
    newValue: { status, resolution_notes },
    ipAddress
  });

  return await get(`SELECT * FROM issues WHERE id = ?`, [id]);
}

/**
 * Delete a photo evidence
 */
async function deletePhotoEvidence(photoId, currentUser, ipAddress) {
  const photo = await get('SELECT * FROM evidence_photos WHERE id = ?', [photoId]);
  if (!photo) {
    throw new Error('Foto bukti tidak ditemukan.');
  }

  const workOrder = await get('SELECT * FROM work_orders WHERE id = ?', [photo.work_order_id]);
  if (!workOrder) {
    throw new Error('Work order tidak ditemukan.');
  }

  // 1. Vendor Isolation check (Point 1.3)
  if (currentUser.role === 'VENDOR' && workOrder.vendor_id !== currentUser.vendor_id) {
    throw new Error('Akses ditolak: Anda tidak memiliki akses ke pekerjaan vendor lain.');
  }

  // 2. Field Team Assignment IDOR check (Point 1.4)
  if (currentUser.role === 'FIELD_TEAM') {
    const assignment = await get(
      `SELECT * FROM work_order_assignments WHERE work_order_id = ? AND user_id = ?`,
      [photo.work_order_id, currentUser.id]
    );
    if (!assignment && workOrder.pic_user_id !== currentUser.id) {
      throw new Error('Akses ditolak: Anda tidak ditugaskan pada pekerjaan ini.');
    }
  }

  // Permissions check: Field team can only delete on non-completed work orders
  if (currentUser.role === 'FIELD_TEAM' && ['COMPLETED', 'BA_OPNAME'].includes(workOrder.status)) {
    throw new Error('Foto tidak dapat dihapus karena pekerjaan sudah berstatus selesai / terbit Berita Acara.');
  }

  // Delete physical file from uploads directory if exists
  if (photo.file_path) {
    try {
      const fullPath = path.resolve(__dirname, '../../..', '.' + photo.file_path);
      if (fs.existsSync(fullPath)) {
        fs.unlinkSync(fullPath);
      }
    } catch (err) {
      console.warn('Physical file deletion warning:', err.message);
    }
  }

  // Delete row from database
  await run('DELETE FROM evidence_photos WHERE id = ?', [photoId]);

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'DELETE_EVIDENCE_PHOTO',
    entityType: 'EVIDENCE_PHOTO',
    entityId: photoId,
    oldValue: {
      workOrderId: photo.work_order_id,
      stage: photo.stage,
      fileName: photo.file_name,
      filePath: photo.file_path
    },
    ipAddress
  });

  return { success: true, id: photoId, workOrderId: photo.work_order_id, stage: photo.stage };
}

/**
 * Securely fetch photo record & verify permissions before streaming file
 */
async function getEvidenceFile(photoId, currentUser) {
  const photo = await get('SELECT * FROM evidence_photos WHERE id = ?', [photoId]);
  if (!photo) {
    throw new Error('Foto bukti tidak ditemukan.');
  }

  const workOrder = await get('SELECT * FROM work_orders WHERE id = ?', [photo.work_order_id]);
  if (!workOrder) {
    throw new Error('Work order tidak ditemukan.');
  }

  // Vendor Isolation check (Point 1.3 & 2.3)
  if (currentUser && currentUser.role === 'VENDOR' && workOrder.vendor_id !== currentUser.vendor_id) {
    throw new Error('Akses ditolak: Anda tidak memiliki akses ke foto bukti vendor lain.');
  }

  const uploadsBase = process.env.UPLOADS_DIR || path.resolve(__dirname, '../../../uploads');
  const fullPath = path.resolve(uploadsBase, path.basename(photo.file_path));

  if (!fs.existsSync(fullPath)) {
    throw new Error('File foto fisik tidak ditemukan di server.');
  }

  return { photo, fullPath };
}

module.exports = {
  uploadPhotoEvidence,
  deletePhotoEvidence,
  reportIssue,
  getAllEvidencePhotos,
  getFieldIssues,
  resolveIssue,
  getEvidenceFile
};
