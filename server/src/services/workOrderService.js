const { query, get, run } = require('../config/database');
const { calculateProgress } = require('../utils/helpers');
const { logAudit } = require('./auditService');
const { sendNotification } = require('./notificationService');

/**
 * Get all work orders with RBAC & Vendor isolation filtering
 */
async function getWorkOrders({ status, vendorId, areaId, search, currentUser }) {
  let sql = `
    SELECT 
      wo.*,
      v.name as vendor_name,
      v.code as vendor_code,
      a.name as area_name,
      jt.name as job_type_name,
      pic.name as pic_name,
      pic.phone as pic_phone
    FROM work_orders wo
    LEFT JOIN vendors v ON wo.vendor_id = v.id
    LEFT JOIN areas a ON wo.area_id = a.id
    LEFT JOIN job_types jt ON wo.job_type_id = jt.id
    LEFT JOIN users pic ON wo.pic_user_id = pic.id
    WHERE 1=1
  `;
  const params = [];

  // Strict Vendor Isolation
  if (currentUser.role === 'VENDOR') {
    sql += ` AND wo.vendor_id = ?`;
    params.push(currentUser.vendor_id);
  } else if (vendorId) {
    sql += ` AND wo.vendor_id = ?`;
    params.push(vendorId);
  }

  // Field Team Isolation (Only show assigned work orders)
  if (currentUser.role === 'FIELD_TEAM') {
    sql += ` AND (wo.pic_user_id = ? OR wo.id IN (SELECT work_order_id FROM work_order_assignments WHERE user_id = ?))`;
    params.push(currentUser.id, currentUser.id);
  }

  if (status) {
    sql += ` AND wo.status = ?`;
    params.push(status);
  }

  if (areaId) {
    sql += ` AND wo.area_id = ?`;
    params.push(areaId);
  }

  if (search) {
    sql += ` AND (wo.spk_number LIKE ? OR wo.title LIKE ? OR wo.location_name LIKE ?)`;
    const s = `%${search}%`;
    params.push(s, s, s);
  }

  sql += ` ORDER BY wo.id DESC`;
  return await query(sql, params);
}

/**
 * Get comprehensive single Work Order details
 */
async function getWorkOrderById(id, currentUser) {
  let sql = `
    SELECT 
      wo.*,
      v.name as vendor_name,
      v.code as vendor_code,
      v.contact_person as vendor_contact,
      v.phone as vendor_phone,
      a.name as area_name,
      jt.name as job_type_name,
      pic.name as pic_name,
      pic.phone as pic_phone
    FROM work_orders wo
    LEFT JOIN vendors v ON wo.vendor_id = v.id
    LEFT JOIN areas a ON wo.area_id = a.id
    LEFT JOIN job_types jt ON wo.job_type_id = jt.id
    LEFT JOIN users pic ON wo.pic_user_id = pic.id
    WHERE wo.id = ?
  `;

  const workOrder = await get(sql, [id]);
  if (!workOrder) {
    throw new Error(`Work Order dengan ID ${id} tidak ditemukan`);
  }

  // Vendor Privacy Isolation Check
  if (currentUser.role === 'VENDOR' && currentUser.vendor_id !== workOrder.vendor_id) {
    throw new Error('Akses ditolak: Anda tidak memiliki izin untuk melihat pekerjaan vendor lain.');
  }

  // Field Team Assignment Check
  if (currentUser.role === 'FIELD_TEAM') {
    const isAssigned = await get(
      `SELECT id FROM work_order_assignments WHERE work_order_id = ? AND user_id = ?`,
      [id, currentUser.id]
    );
    if (!isAssigned && workOrder.pic_user_id !== currentUser.id) {
      throw new Error('Akses ditolak: Anda belum ditugaskan pada pekerjaan ini.');
    }
  }

  // Fetch Assignments
  workOrder.assignments = await query(`
    SELECT woa.*, u.name as user_name, u.email as user_email, u.phone as user_phone
    FROM work_order_assignments woa
    JOIN users u ON woa.user_id = u.id
    WHERE woa.work_order_id = ?
    ORDER BY woa.role_in_team DESC
  `, [id]);

  // Fetch Check-ins
  workOrder.check_ins = await query(`
    SELECT ci.*, u.name as user_name
    FROM check_ins ci
    JOIN users u ON ci.user_id = u.id
    WHERE ci.work_order_id = ?
    ORDER BY ci.id DESC
  `, [id]);

  // Fetch Evidence Photos
  workOrder.evidence_photos = await query(`
    SELECT ep.*, u.name as uploader_name
    FROM evidence_photos ep
    JOIN users u ON ep.user_id = u.id
    WHERE ep.work_order_id = ?
    ORDER BY ep.stage ASC, ep.sequence ASC, ep.id ASC
  `, [id]);

  // Fetch Issues
  workOrder.issues = await query(`
    SELECT iss.*, u.name as reporter_name, ru.name as resolver_name
    FROM issues iss
    JOIN users u ON iss.user_id = u.id
    LEFT JOIN users ru ON iss.resolved_by = ru.id
    WHERE iss.work_order_id = ?
    ORDER BY iss.id DESC
  `, [id]);

  // Fetch Reviews & Revisions
  workOrder.reviews = await query(`
    SELECT r.*, u.name as reviewer_name
    FROM reviews r
    JOIN users u ON r.reviewer_user_id = u.id
    WHERE r.work_order_id = ?
    ORDER BY r.id DESC
  `, [id]);

  workOrder.revisions = await query(`
    SELECT rev.*, u.name as requester_name
    FROM revisions rev
    JOIN users u ON rev.requested_by = u.id
    WHERE rev.work_order_id = ?
    ORDER BY rev.id DESC
  `, [id]);

  // Fetch BA Document if exists
  workOrder.ba_document = await get(`
    SELECT ba.*, u.name as generator_name
    FROM ba_documents ba
    LEFT JOIN users u ON ba.generated_by = u.id
    WHERE ba.work_order_id = ?
  `, [id]);

  // Fetch Work Order Sub-Items (Multi-Item Location Hub)
  workOrder.items = await query(`
    SELECT woi.*, jt.name as job_type_name
    FROM work_order_items woi
    LEFT JOIN job_types jt ON woi.job_type_id = jt.id
    WHERE woi.work_order_id = ?
    ORDER BY woi.id ASC
  `, [id]);

  return workOrder;
}

/**
 * Create a new Work Order (SPK)
 */
async function createWorkOrder(data, currentUser, ipAddress) {
  const now = new Date().toISOString();

  // Generate SPK number if not provided
  let spkNumber = data.spk_number;
  if (!spkNumber) {
    const countRes = await get(`SELECT COUNT(*) as count FROM work_orders`);
    const nextSeq = String(countRes.count + 1).padStart(5, '0');
    const year = new Date().getFullYear();
    spkNumber = `SPK-${year}-${nextSeq}`;
  }

  let contractValue = parseFloat(data.contract_value) || 0;
  if (contractValue === 0 && data.job_type_id) {
    const jt = await get(`SELECT standard_price FROM job_types WHERE id = ?`, [data.job_type_id]);
    if (jt && jt.standard_price) {
      contractValue = jt.standard_price;
    }
  }

  const requireGeofence = data.require_geofence !== undefined ? (data.require_geofence ? 1 : 0) : 1;
  const geofenceRadius = parseInt(data.geofence_radius, 10) || 200;
  const status = data.pic_user_id ? 'ASSIGNED' : 'READY';
  const progress = calculateProgress(status);

  const res = await run(`
    INSERT INTO work_orders (
      spk_number, title, vendor_id, area_id, job_type_id, location_name,
      target_lat, target_lng, pic_user_id, start_date, deadline,
      doc_mode, require_checkin, require_geofence, geofence_radius, contract_value, status, progress_percent, notes,
      created_by, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `, [
    spkNumber,
    data.title,
    data.vendor_id,
    data.area_id,
    data.job_type_id || null,
    data.location_name,
    data.target_lat || null,
    data.target_lng || null,
    data.pic_user_id || null,
    data.start_date,
    data.deadline,
    data.doc_mode || 'BEFORE_PROCESS_AFTER',
    data.require_checkin !== undefined ? (data.require_checkin ? 1 : 0) : 1,
    requireGeofence,
    geofenceRadius,
    contractValue,
    status,
    progress,
    data.notes || '',
    currentUser.id,
    now,
    now
  ]);

  const workOrderId = res.lastID;

  // Insert Sub-Items (Multi-Item Location Hub)
  if (data.items && Array.isArray(data.items) && data.items.length > 0) {
    for (const itm of data.items) {
      if (itm.item_name && itm.item_name.trim()) {
        await run(`
          INSERT INTO work_order_items (
            work_order_id, item_name, job_type_id, doc_mode, weight_percent, status, notes, created_at
          ) VALUES (?, ?, ?, ?, ?, 'PENDING', ?, ?)
        `, [
          workOrderId,
          itm.item_name.trim(),
          itm.job_type_id || data.job_type_id || null,
          itm.doc_mode || data.doc_mode || 'BEFORE_PROCESS_AFTER',
          itm.weight_percent || Math.round(100 / data.items.length),
          itm.notes || '',
          now
        ]);
      }
    }
  } else {
    // Default 1 item
    await run(`
      INSERT INTO work_order_items (
        work_order_id, item_name, job_type_id, doc_mode, weight_percent, status, notes, created_at
      ) VALUES (?, ?, ?, ?, 100, 'PENDING', ?, ?)
    `, [
      workOrderId,
      data.title,
      data.job_type_id || null,
      data.doc_mode || 'BEFORE_PROCESS_AFTER',
      data.notes || '',
      now
    ]);
  }

  // Auto assign PIC if specified
  if (data.pic_user_id) {
    await run(`
      INSERT INTO work_order_assignments (work_order_id, user_id, role_in_team, assigned_at)
      VALUES (?, ?, 'PIC', ?)
    `, [workOrderId, data.pic_user_id, now]);

    // Send notification to PIC
    const picUser = await get(`SELECT phone, name FROM users WHERE id = ?`, [data.pic_user_id]);
    if (picUser && picUser.phone) {
      await sendNotification({
        recipient: picUser.phone,
        messageType: 'NEW_ASSIGNMENT',
        payload: { workOrderId, spkNumber, title: data.title },
        text: `Halo ${picUser.name}, Anda telah ditugaskan sebagai PIC untuk pekerjaan baru [${spkNumber}] - ${data.title}. Lokasi: ${data.location_name}. Deadline: ${data.deadline}.`
      });
    }
  }

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'CREATE_WORK_ORDER',
    entityType: 'WORK_ORDER',
    entityId: workOrderId,
    newValue: { spkNumber, title: data.title, status },
    ipAddress
  });

  return await getWorkOrderById(workOrderId, currentUser);
}

/**
 * Assign or Update Field Team for Work Order
 */
async function assignTeam(workOrderId, { picUserId, memberUserIds = [] }, currentUser, ipAddress) {
  const now = new Date().toISOString();
  const workOrder = await get(`SELECT * FROM work_orders WHERE id = ?`, [workOrderId]);
  if (!workOrder) throw new Error('Work Order tidak ditemukan');

  if (!picUserId) throw new Error('PIC wajib ditentukan');

  // Clear existing assignments
  await run(`DELETE FROM work_order_assignments WHERE work_order_id = ?`, [workOrderId]);

  // Insert PIC
  await run(`
    INSERT INTO work_order_assignments (work_order_id, user_id, role_in_team, assigned_at)
    VALUES (?, ?, 'PIC', ?)
  `, [workOrderId, picUserId, now]);

  // Insert Members
  for (const memberId of memberUserIds) {
    if (memberId !== picUserId) {
      await run(`
        INSERT INTO work_order_assignments (work_order_id, user_id, role_in_team, assigned_at)
        VALUES (?, ?, 'MEMBER', ?)
      `, [workOrderId, memberId, now]);
    }
  }

  // Update Work order PIC & status if currently READY/DRAFT
  let newStatus = workOrder.status;
  if (workOrder.status === 'READY' || workOrder.status === 'DRAFT') {
    newStatus = 'ASSIGNED';
  }

  const newProgress = calculateProgress(newStatus);

  await run(`
    UPDATE work_orders
    SET pic_user_id = ?, status = ?, progress_percent = ?, updated_at = ?
    WHERE id = ?
  `, [picUserId, newStatus, newProgress, now, workOrderId]);

  // Send WhatsApp notification to PIC
  const pic = await get(`SELECT name, phone FROM users WHERE id = ?`, [picUserId]);
  if (pic && pic.phone) {
    await sendNotification({
      recipient: pic.phone,
      messageType: 'ASSIGNMENT_UPDATED',
      payload: { workOrderId, spkNumber: workOrder.spk_number },
      text: `Halo ${pic.name}, Anda telah ditugaskan untuk memimpin pekerjaan ${workOrder.spk_number} (${workOrder.title}) di ${workOrder.location_name}.`
    });
  }

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'ASSIGN_TEAM',
    entityType: 'WORK_ORDER',
    entityId: workOrderId,
    oldValue: { pic_user_id: workOrder.pic_user_id, status: workOrder.status },
    newValue: { picUserId, memberUserIds, status: newStatus },
    ipAddress
  });

  return await getWorkOrderById(workOrderId, currentUser);
}

/**
 * Server-Side Validation Gate and Task Submission by Field Team
 */
async function submitWorkOrder(workOrderId, currentUser, ipAddress) {
  const now = new Date().toISOString();
  const workOrder = await getWorkOrderById(workOrderId, currentUser);

  // 1. Verify Status
  if (!['ASSIGNED', 'CHECKED_IN', 'IN_PROGRESS', 'REVISION'].includes(workOrder.status)) {
    throw new Error(`Pekerjaan dengan status '${workOrder.status}' tidak dapat di-submit.`);
  }

  // 2. Verify Check-in
  if (workOrder.require_checkin && (!workOrder.check_ins || workOrder.check_ins.length === 0)) {
    throw new Error('Gagal Submit: Pekerjaan ini mewajibkan check-in GPS resmi di lokasi pekerjaan sebelum submit.');
  }

  // 3. Verify Photo Evidence completeness per Item
  const photos = workOrder.evidence_photos || [];

  if (workOrder.items && workOrder.items.length > 0) {
    for (const item of workOrder.items) {
      const itemPhotos = photos.filter(p => p.item_id === item.id || (!p.item_id && workOrder.items.length === 1));
      const beforePhotos = itemPhotos.filter(p => p.stage === 'BEFORE');
      const processPhotos = itemPhotos.filter(p => p.stage === 'PROCESS');
      const afterPhotos = itemPhotos.filter(p => p.stage === 'AFTER');

      if (item.doc_mode === 'AFTER_ONLY') {
        if (afterPhotos.length < 1) {
          throw new Error(`Gagal Submit: Foto AFTER pada sub-pekerjaan "${item.item_name}" belum lengkap.`);
        }
      } else {
        if (beforePhotos.length < 1) {
          throw new Error(`Gagal Submit: Foto BEFORE pada sub-pekerjaan "${item.item_name}" belum lengkap.`);
        }
        if (processPhotos.length < 1) {
          throw new Error(`Gagal Submit: Foto PROCESS pada sub-pekerjaan "${item.item_name}" belum lengkap.`);
        }
        if (afterPhotos.length < 1) {
          throw new Error(`Gagal Submit: Foto AFTER pada sub-pekerjaan "${item.item_name}" belum lengkap.`);
        }
      }

      // Mark item status as COMPLETED
      await run(`UPDATE work_order_items SET status = 'COMPLETED' WHERE id = ?`, [item.id]);
    }
  } else {
    const beforePhotos = photos.filter(p => p.stage === 'BEFORE');
    const processPhotos = photos.filter(p => p.stage === 'PROCESS');
    const afterPhotos = photos.filter(p => p.stage === 'AFTER');

    if (workOrder.doc_mode === 'AFTER_ONLY') {
      if (afterPhotos.length < 1) {
        throw new Error('Gagal Submit: Dokumentasi AFTER minimal memerlukan 1 foto bukti pekerjaan selesai.');
      }
    } else {
      if (beforePhotos.length < 1 || processPhotos.length < 1 || afterPhotos.length < 1) {
        throw new Error('Gagal Submit: Foto BEFORE, PROCESS, dan AFTER wajib lengkap minimal 1 foto tiap tahap.');
      }
    }
  }

  // 4. Verify Open Revisions (if in REVISION status)
  if (workOrder.status === 'REVISION') {
    const openRevisions = (workOrder.revisions || []).filter(r => r.status === 'OPEN');
    for (const rev of openRevisions) {
      await run(`UPDATE revisions SET status = 'RESOLVED', resolved_at = ? WHERE id = ?`, [now, rev.id]);
    }
  }

  // 5. Update Status to SUBMITTED
  const newStatus = 'SUBMITTED';
  const newProgress = calculateProgress(newStatus);

  await run(`
    UPDATE work_orders
    SET status = ?, progress_percent = ?, updated_at = ?
    WHERE id = ?
  `, [newStatus, newProgress, now, workOrderId]);

  // Send Notification to Admins
  const admins = await query(`SELECT phone FROM users WHERE role = 'ADMIN' AND is_active = 1`);
  for (const admin of admins) {
    if (admin.phone) {
      await sendNotification({
        recipient: admin.phone,
        messageType: 'WORK_ORDER_SUBMITTED',
        payload: { workOrderId, spkNumber: workOrder.spk_number },
        text: `Notifikasi: Pekerjaan [${workOrder.spk_number}] - ${workOrder.title} telah selesai dikerjakan oleh Tim Lapangan dan siap untuk direview.`
      });
    }
  }

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'SUBMIT_WORK_ORDER',
    entityType: 'WORK_ORDER',
    entityId: workOrderId,
    oldValue: { status: workOrder.status },
    newValue: { status: newStatus },
    ipAddress
  });

  return await getWorkOrderById(workOrderId, currentUser);
}

module.exports = {
  getWorkOrders,
  getWorkOrderById,
  createWorkOrder,
  assignTeam,
  submitWorkOrder
};
