const bcrypt = require('bcryptjs');
const { query, get, run } = require('../config/database');
const { logAudit } = require('./auditService');

// --- VENDORS ---
async function getVendors(currentUser) {
  if (currentUser && currentUser.role === 'VENDOR') {
    return await query(`SELECT * FROM vendors WHERE is_active = 1 AND id = ? ORDER BY id DESC`, [currentUser.vendor_id]);
  }
  return await query(`SELECT * FROM vendors WHERE is_active = 1 ORDER BY id DESC`);
}

async function createVendor(data, currentUser, ipAddress) {
  const now = new Date().toISOString();
  const res = await run(
    `INSERT INTO vendors (code, name, contact_person, phone, email, address, is_active, created_at)
     VALUES (?, ?, ?, ?, ?, ?, 1, ?)`,
    [data.code, data.name, data.contact_person || '', data.phone || '', data.email || '', data.address || '', now]
  );

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'CREATE_VENDOR',
    entityType: 'VENDOR',
    entityId: res.lastID,
    newValue: data,
    ipAddress
  });

  return await get(`SELECT * FROM vendors WHERE id = ?`, [res.lastID]);
}

async function updateVendor(id, data, currentUser, ipAddress) {
  const old = await get(`SELECT * FROM vendors WHERE id = ?`, [id]);
  if (!old) throw new Error('Vendor tidak ditemukan');

  await run(
    `UPDATE vendors SET code = ?, name = ?, contact_person = ?, phone = ?, email = ?, address = ? WHERE id = ?`,
    [
      data.code || old.code,
      data.name || old.name,
      data.contact_person !== undefined ? data.contact_person : old.contact_person,
      data.phone !== undefined ? data.phone : old.phone,
      data.email !== undefined ? data.email : old.email,
      data.address !== undefined ? data.address : old.address,
      id
    ]
  );

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'UPDATE_VENDOR',
    entityType: 'VENDOR',
    entityId: id,
    oldValue: old,
    newValue: data,
    ipAddress
  });

  return await get(`SELECT * FROM vendors WHERE id = ?`, [id]);
}

async function deleteVendor(id, currentUser, ipAddress) {
  const old = await get(`SELECT * FROM vendors WHERE id = ?`, [id]);
  if (!old) throw new Error('Vendor tidak ditemukan');

  // Check if vendor has active work orders
  const woCount = await get(`SELECT COUNT(*) as count FROM work_orders WHERE vendor_id = ?`, [id]);
  if (woCount && woCount.count > 0) {
    // Soft delete to maintain referential integrity
    await run(`UPDATE vendors SET is_active = 0 WHERE id = ?`, [id]);
  } else {
    await run(`DELETE FROM vendors WHERE id = ?`, [id]);
  }

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'DELETE_VENDOR',
    entityType: 'VENDOR',
    entityId: id,
    oldValue: old,
    ipAddress
  });

  return { success: true, message: 'Vendor berhasil dihapus' };
}

// --- AREAS ---
async function getAreas() {
  return await query(`SELECT * FROM areas ORDER BY name ASC`);
}

async function createArea(data, currentUser, ipAddress) {
  const now = new Date().toISOString();
  const res = await run(
    `INSERT INTO areas (name, province, city, district, created_at)
     VALUES (?, ?, ?, ?, ?)`,
    [data.name, data.province || '', data.city || '', data.district || '', now]
  );

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'CREATE_AREA',
    entityType: 'AREA',
    entityId: res.lastID,
    newValue: data,
    ipAddress
  });

  return await get(`SELECT * FROM areas WHERE id = ?`, [res.lastID]);
}

async function updateArea(id, data, currentUser, ipAddress) {
  const old = await get(`SELECT * FROM areas WHERE id = ?`, [id]);
  if (!old) throw new Error('Area tidak ditemukan');

  await run(
    `UPDATE areas SET name = ?, province = ?, city = ?, district = ? WHERE id = ?`,
    [
      data.name || old.name,
      data.province !== undefined ? data.province : old.province,
      data.city !== undefined ? data.city : old.city,
      data.district !== undefined ? data.district : old.district,
      id
    ]
  );

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'UPDATE_AREA',
    entityType: 'AREA',
    entityId: id,
    oldValue: old,
    newValue: data,
    ipAddress
  });

  return await get(`SELECT * FROM areas WHERE id = ?`, [id]);
}

async function deleteArea(id, currentUser, ipAddress) {
  const old = await get(`SELECT * FROM areas WHERE id = ?`, [id]);
  if (!old) throw new Error('Area tidak ditemukan');

  await run(`DELETE FROM areas WHERE id = ?`, [id]);

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'DELETE_AREA',
    entityType: 'AREA',
    entityId: id,
    oldValue: old,
    ipAddress
  });

  return { success: true, message: 'Area berhasil dihapus' };
}

// --- JOB TYPES ---
async function getJobTypes() {
  return await query(`SELECT * FROM job_types WHERE is_active = 1 ORDER BY name ASC`);
}

async function createJobType(data, currentUser, ipAddress) {
  const now = new Date().toISOString();
  const price = parseFloat(data.standard_price) || 0;
  const res = await run(
    `INSERT INTO job_types (code, name, standard_price, doc_mode, min_photos_per_stage, is_active, created_at)
     VALUES (?, ?, ?, ?, ?, 1, ?)`,
    [data.code, data.name, price, data.doc_mode || 'BEFORE_PROCESS_AFTER', data.min_photos_per_stage || 3, now]
  );

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'CREATE_JOB_TYPE',
    entityType: 'JOB_TYPE',
    entityId: res.lastID,
    newValue: data,
    ipAddress
  });

  return await get(`SELECT * FROM job_types WHERE id = ?`, [res.lastID]);
}

async function updateJobType(id, data, currentUser, ipAddress) {
  const old = await get(`SELECT * FROM job_types WHERE id = ?`, [id]);
  if (!old) throw new Error('Jenis pekerjaan tidak ditemukan');

  const price = data.standard_price !== undefined ? parseFloat(data.standard_price) || 0 : old.standard_price;
  const minPhotos = data.min_photos_per_stage !== undefined ? parseInt(data.min_photos_per_stage) || 3 : old.min_photos_per_stage;

  await run(
    `UPDATE job_types SET code = ?, name = ?, standard_price = ?, doc_mode = ?, min_photos_per_stage = ? WHERE id = ?`,
    [
      data.code || old.code,
      data.name || old.name,
      price,
      data.doc_mode || old.doc_mode,
      minPhotos,
      id
    ]
  );

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'UPDATE_JOB_TYPE',
    entityType: 'JOB_TYPE',
    entityId: id,
    oldValue: old,
    newValue: data,
    ipAddress
  });

  return await get(`SELECT * FROM job_types WHERE id = ?`, [id]);
}

async function deleteJobType(id, currentUser, ipAddress) {
  const old = await get(`SELECT * FROM job_types WHERE id = ?`, [id]);
  if (!old) throw new Error('Jenis pekerjaan tidak ditemukan');

  // Soft delete
  await run(`UPDATE job_types SET is_active = 0 WHERE id = ?`, [id]);

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'DELETE_JOB_TYPE',
    entityType: 'JOB_TYPE',
    entityId: id,
    oldValue: old,
    ipAddress
  });

  return { success: true, message: 'Jenis pekerjaan berhasil dihapus' };
}

// --- FIELD TEAMS ---
async function getFieldTeams() {
  const teams = await query(`
    SELECT ft.*, u.name as leader_name, u.phone as leader_phone, a.name as area_name
    FROM field_teams ft
    LEFT JOIN users u ON ft.leader_user_id = u.id
    LEFT JOIN areas a ON ft.area_id = a.id
    WHERE ft.is_active = 1
    ORDER BY ft.id DESC
  `);

  for (const team of teams) {
    const members = await query(`
      SELECT u.id, u.name, u.email, u.phone
      FROM field_team_members ftm
      JOIN users u ON ftm.user_id = u.id
      WHERE ftm.team_id = ?
    `, [team.id]);
    team.members = members;
  }

  return teams;
}

async function createFieldTeam(data, currentUser, ipAddress) {
  const now = new Date().toISOString();
  const res = await run(
    `INSERT INTO field_teams (name, leader_user_id, area_id, is_active, created_at)
     VALUES (?, ?, ?, 1, ?)`,
    [data.name, data.leader_user_id || null, data.area_id || null, now]
  );

  const teamId = res.lastID;

  if (data.member_ids && Array.isArray(data.member_ids)) {
    for (const userId of data.member_ids) {
      await run(`INSERT INTO field_team_members (team_id, user_id, created_at) VALUES (?, ?, ?)`, [teamId, userId, now]);
    }
  }

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'CREATE_FIELD_TEAM',
    entityType: 'FIELD_TEAM',
    entityId: teamId,
    newValue: data,
    ipAddress
  });

  return await get(`SELECT * FROM field_teams WHERE id = ?`, [teamId]);
}

async function updateFieldTeam(id, data, currentUser, ipAddress) {
  const old = await get(`SELECT * FROM field_teams WHERE id = ?`, [id]);
  if (!old) throw new Error('Tim lapangan tidak ditemukan');

  await run(
    `UPDATE field_teams SET name = ?, leader_user_id = ?, area_id = ? WHERE id = ?`,
    [
      data.name || old.name,
      data.leader_user_id !== undefined ? data.leader_user_id : old.leader_user_id,
      data.area_id !== undefined ? data.area_id : old.area_id,
      id
    ]
  );

  if (data.member_ids && Array.isArray(data.member_ids)) {
    await run(`DELETE FROM field_team_members WHERE team_id = ?`, [id]);
    const now = new Date().toISOString();
    for (const userId of data.member_ids) {
      await run(`INSERT INTO field_team_members (team_id, user_id, created_at) VALUES (?, ?, ?)`, [id, userId, now]);
    }
  }

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'UPDATE_FIELD_TEAM',
    entityType: 'FIELD_TEAM',
    entityId: id,
    oldValue: old,
    newValue: data,
    ipAddress
  });

  return await get(`SELECT * FROM field_teams WHERE id = ?`, [id]);
}

async function deleteFieldTeam(id, currentUser, ipAddress) {
  const old = await get(`SELECT * FROM field_teams WHERE id = ?`, [id]);
  if (!old) throw new Error('Tim lapangan tidak ditemukan');

  await run(`UPDATE field_teams SET is_active = 0 WHERE id = ?`, [id]);

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'DELETE_FIELD_TEAM',
    entityType: 'FIELD_TEAM',
    entityId: id,
    oldValue: old,
    ipAddress
  });

  return { success: true, message: 'Tim lapangan berhasil dihapus' };
}

// --- USERS ---
async function getUsers({ role, vendorId }) {
  let sql = `
    SELECT u.id, u.name, u.email, u.phone, u.role, u.vendor_id, u.is_active, u.created_at, v.name as vendor_name
    FROM users u
    LEFT JOIN vendors v ON u.vendor_id = v.id
    WHERE 1=1
  `;
  const params = [];

  if (role) {
    sql += ` AND u.role = ?`;
    params.push(role);
  }
  if (vendorId) {
    sql += ` AND u.vendor_id = ?`;
    params.push(vendorId);
  }

  sql += ` ORDER BY u.id DESC`;
  return await query(sql, params);
}

async function createUser(data, currentUser, ipAddress) {
  const now = new Date().toISOString();
  const passwordHash = await bcrypt.hash(data.password || 'sgx12345', 10);

  const res = await run(
    `INSERT INTO users (name, email, password_hash, phone, role, vendor_id, is_active, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?)`,
    [data.name, data.email, passwordHash, data.phone || '', data.role, data.vendor_id || null, now, now]
  );

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'CREATE_USER',
    entityType: 'USER',
    entityId: res.lastID,
    newValue: { email: data.email, role: data.role },
    ipAddress
  });

  return await get(`SELECT id, name, email, phone, role, vendor_id, is_active FROM users WHERE id = ?`, [res.lastID]);
}

async function updateUser(id, data, currentUser, ipAddress) {
  const old = await get(`SELECT * FROM users WHERE id = ?`, [id]);
  if (!old) throw new Error('User tidak ditemukan');

  const now = new Date().toISOString();
  let passwordHash = old.password_hash;
  if (data.password && data.password.trim().length > 0) {
    passwordHash = await bcrypt.hash(data.password, 10);
  }

  await run(
    `UPDATE users SET name = ?, email = ?, password_hash = ?, phone = ?, role = ?, vendor_id = ?, is_active = ?, updated_at = ? WHERE id = ?`,
    [
      data.name || old.name,
      data.email || old.email,
      passwordHash,
      data.phone !== undefined ? data.phone : old.phone,
      data.role || old.role,
      data.vendor_id !== undefined ? (data.vendor_id || null) : old.vendor_id,
      data.is_active !== undefined ? (data.is_active ? 1 : 0) : old.is_active,
      now,
      id
    ]
  );

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'UPDATE_USER',
    entityType: 'USER',
    entityId: id,
    oldValue: { name: old.name, email: old.email, role: old.role },
    newValue: { name: data.name, email: data.email, role: data.role },
    ipAddress
  });

  return await get(`SELECT id, name, email, phone, role, vendor_id, is_active FROM users WHERE id = ?`, [id]);
}

async function deleteUser(id, currentUser, ipAddress) {
  const old = await get(`SELECT * FROM users WHERE id = ?`, [id]);
  if (!old) throw new Error('User tidak ditemukan');

  if (old.role === 'SUPERUSER' && old.id === currentUser.id) {
    throw new Error('Tidak dapat menghapus akun Superuser yang sedang login.');
  }

  await run(`DELETE FROM users WHERE id = ?`, [id]);

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'DELETE_USER',
    entityType: 'USER',
    entityId: id,
    oldValue: { email: old.email, name: old.name },
    ipAddress
  });

  return { success: true, message: 'User berhasil dihapus' };
}

// --- TEMPLATES ---
async function getTemplates() {
  return await query(`SELECT * FROM document_templates ORDER BY is_default DESC, id ASC`);
}

async function getTemplateById(id) {
  return await get(`SELECT * FROM document_templates WHERE id = ?`, [id]);
}

async function createTemplate(data, files, currentUser, ipAddress) {
  const now = new Date().toISOString();
  let logoUrl = data.logo_url || null;
  let headerImageUrl = data.header_image_url || null;
  let backgroundImageUrl = data.background_image_url || null;
  let footerImageUrl = data.footer_image_url || null;

  if (files) {
    if (files.logo && files.logo[0]) logoUrl = `/uploads/${files.logo[0].filename}`;
    if (files.background_image && files.background_image[0]) backgroundImageUrl = `/uploads/${files.background_image[0].filename}`;
    if (files.header_image && files.header_image[0]) headerImageUrl = `/uploads/${files.header_image[0].filename}`;
    if (files.footer_image && files.footer_image[0]) footerImageUrl = `/uploads/${files.footer_image[0].filename}`;
  }

  const isDefault = data.is_default ? 1 : 0;
  if (isDefault === 1) {
    await run(`UPDATE document_templates SET is_default = 0`);
  }

  let signatoriesJson = null;
  if (data.signatories_json) {
    signatoriesJson = typeof data.signatories_json === 'string' ? data.signatories_json : JSON.stringify(data.signatories_json);
  } else if (data.signatories) {
    signatoriesJson = typeof data.signatories === 'string' ? data.signatories : JSON.stringify(data.signatories);
  }

  const res = await run(
    `INSERT INTO document_templates (
      name, code, logo_url, header_image_url, background_image_url, footer_image_url,
      header_html, footer_html, body_template, signatories_json,
      signatory_first_party_name, signatory_first_party_role,
      signatory_second_party_name, signatory_second_party_role,
      is_default, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
    [
      data.name,
      data.code || `TMPL-${Date.now()}`,
      logoUrl,
      headerImageUrl,
      backgroundImageUrl,
      footerImageUrl,
      data.header_html || '',
      data.footer_html || '',
      data.body_template || '',
      signatoriesJson,
      data.signatory_first_party_name || 'Dian Anggraini',
      data.signatory_first_party_role || 'Koordinator Pengawas Proyek',
      data.signatory_second_party_name || 'Andi Pratama',
      data.signatory_second_party_role || 'Project Manager Mitra Vendor',
      isDefault,
      now
    ]
  );

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'CREATE_TEMPLATE',
    entityType: 'TEMPLATE',
    entityId: res.lastID,
    newValue: data,
    ipAddress
  });

  return await getTemplateById(res.lastID);
}

async function updateTemplate(id, data, files, currentUser, ipAddress) {
  const old = await getTemplateById(id);
  if (!old) throw new Error('Template dokumen tidak ditemukan');

  let logoUrl = old.logo_url;
  let headerImageUrl = old.header_image_url;
  let backgroundImageUrl = old.background_image_url;
  let footerImageUrl = old.footer_image_url;

  // Handle explicit removal requests
  if (data.remove_logo === '1' || data.remove_logo === 'true' || data.logo_url === '') {
    logoUrl = null;
  }
  if (data.remove_background === '1' || data.remove_background === 'true' || data.background_image_url === '') {
    backgroundImageUrl = null;
    headerImageUrl = null;
  }
  if (data.remove_footer === '1' || data.remove_footer === 'true' || data.footer_image_url === '') {
    footerImageUrl = null;
  }

  if (files) {
    if (files.logo && files.logo[0]) logoUrl = `/uploads/${files.logo[0].filename}`;
    if (files.background_image && files.background_image[0]) {
      backgroundImageUrl = `/uploads/${files.background_image[0].filename}`;
      headerImageUrl = `/uploads/${files.background_image[0].filename}`;
    }
    if (files.header_image && files.header_image[0]) headerImageUrl = `/uploads/${files.header_image[0].filename}`;
    if (files.footer_image && files.footer_image[0]) footerImageUrl = `/uploads/${files.footer_image[0].filename}`;
  }

  if (data.is_default && parseInt(data.is_default) === 1) {
    await run(`UPDATE document_templates SET is_default = 0`);
  }

  let signatoriesJson = old.signatories_json;
  if (data.signatories_json !== undefined) {
    signatoriesJson = typeof data.signatories_json === 'string' ? data.signatories_json : JSON.stringify(data.signatories_json);
  } else if (data.signatories !== undefined) {
    signatoriesJson = typeof data.signatories === 'string' ? data.signatories : JSON.stringify(data.signatories);
  }

  await run(
    `UPDATE document_templates SET
      name = ?,
      code = ?,
      logo_url = ?,
      header_image_url = ?,
      background_image_url = ?,
      footer_image_url = ?,
      header_html = ?,
      footer_html = ?,
      body_template = ?,
      signatories_json = ?,
      signatory_first_party_name = ?,
      signatory_first_party_role = ?,
      signatory_second_party_name = ?,
      signatory_second_party_role = ?,
      is_default = COALESCE(?, is_default)
    WHERE id = ?`,
    [
      data.name || old.name,
      data.code || old.code,
      logoUrl,
      headerImageUrl,
      backgroundImageUrl,
      footerImageUrl,
      data.header_html !== undefined ? data.header_html : old.header_html,
      data.footer_html !== undefined ? data.footer_html : old.footer_html,
      data.body_template !== undefined ? data.body_template : old.body_template,
      signatoriesJson,
      data.signatory_first_party_name || old.signatory_first_party_name,
      data.signatory_first_party_role || old.signatory_first_party_role,
      data.signatory_second_party_name || old.signatory_second_party_name,
      data.signatory_second_party_role || old.signatory_second_party_role,
      data.is_default !== undefined ? (data.is_default ? 1 : 0) : null,
      id
    ]
  );

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'UPDATE_TEMPLATE',
    entityType: 'TEMPLATE',
    entityId: id,
    oldValue: old,
    newValue: data,
    ipAddress
  });

  return await getTemplateById(id);
}

async function setDefaultTemplate(id, currentUser, ipAddress) {
  await run(`UPDATE document_templates SET is_default = 0`);
  await run(`UPDATE document_templates SET is_default = 1 WHERE id = ?`, [id]);

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'SET_DEFAULT_TEMPLATE',
    entityType: 'TEMPLATE',
    entityId: id,
    ipAddress
  });

  return { success: true, message: 'Template default berhasil diubah' };
}

async function deleteTemplate(id, currentUser, ipAddress) {
  const old = await getTemplateById(id);
  if (!old) throw new Error('Template dokumen tidak ditemukan');
  if (old.is_default) throw new Error('Tidak dapat menghapus template yang sedang aktif sebagai Default.');

  await run(`DELETE FROM document_templates WHERE id = ?`, [id]);

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'DELETE_TEMPLATE',
    entityType: 'TEMPLATE',
    entityId: id,
    oldValue: old,
    ipAddress
  });

  return { success: true, message: 'Template berhasil dihapus' };
}

// --- SYSTEM SETTINGS ---
async function getSystemSettings() {
  return await query(`SELECT * FROM system_settings ORDER BY id ASC`);
}

async function updateSystemSetting(key, value, currentUser, ipAddress) {
  const now = new Date().toISOString();
  const old = await get(`SELECT * FROM system_settings WHERE key = ?`, [key]);
  await run(`UPDATE system_settings SET value = ?, updated_at = ? WHERE key = ?`, [value, now, key]);

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'UPDATE_SYSTEM_SETTING',
    entityType: 'SETTING',
    oldValue: old ? old.value : null,
    newValue: value,
    ipAddress
  });

  return await get(`SELECT * FROM system_settings WHERE key = ?`, [key]);
}

module.exports = {
  getVendors,
  createVendor,
  updateVendor,
  deleteVendor,
  getAreas,
  createArea,
  updateArea,
  deleteArea,
  getJobTypes,
  createJobType,
  updateJobType,
  deleteJobType,
  getFieldTeams,
  createFieldTeam,
  updateFieldTeam,
  deleteFieldTeam,
  getUsers,
  createUser,
  updateUser,
  deleteUser,
  getTemplates,
  getTemplateById,
  createTemplate,
  updateTemplate,
  setDefaultTemplate,
  deleteTemplate,
  getSystemSettings,
  updateSystemSetting
};
