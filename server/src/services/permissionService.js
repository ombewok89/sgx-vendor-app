const { get, run, query, exec } = require('../config/database');
const { logAudit } = require('./auditService');

// Master Modules Definition
const DEFAULT_MODULES = [
  // ADMIN & CORE WORKFLOW
  { id: 'admin_dashboard', name: 'Dashboard Operasional', section: 'UTAMA', icon: 'LayoutDashboard', sort_order: 1 },
  { id: 'admin_spk', name: 'Pekerjaan / SPK', section: 'WORK MANAGEMENT', icon: 'FileText', sort_order: 2 },
  { id: 'admin_review', name: 'Review & Revisi', section: 'WORK MANAGEMENT', icon: 'CheckSquare', sort_order: 3 },
  { id: 'admin_evidence', name: 'Evidence Gallery', section: 'DOCUMENTATION', icon: 'Camera', sort_order: 4 },
  { id: 'admin_issues', name: 'Kendala Teknis', section: 'DOCUMENTATION', icon: 'AlertTriangle', sort_order: 5 },
  { id: 'admin_ba', name: 'BA Opname', section: 'REPORTING', icon: 'FileCheck2', sort_order: 6 },
  { id: 'admin_reports', name: 'Laporan & Statistik', section: 'REPORTING', icon: 'BarChart3', sort_order: 7 },
  
  // MASTER DATA
  { id: 'admin_vendors', name: 'Master Vendor', section: 'MASTER DATA', icon: 'Building2', sort_order: 8 },
  { id: 'admin_teams', name: 'Tim Lapangan', section: 'MASTER DATA', icon: 'Users', sort_order: 9 },
  { id: 'admin_areas', name: 'Master Area', section: 'MASTER DATA', icon: 'MapPin', sort_order: 10 },
  { id: 'admin_jobtypes', name: 'Jenis Pekerjaan', section: 'MASTER DATA', icon: 'Briefcase', sort_order: 11 },
  
  // SYSTEM & INTEGRATION
  { id: 'admin_templates', name: 'Template Dokumen', section: 'SYSTEM', icon: 'FileCode', sort_order: 12 },
  { id: 'admin_notifications', name: 'WhatsApp Logs', section: 'SYSTEM', icon: 'Bell', sort_order: 13 },
  { id: 'admin_audit', name: 'Audit Trail', section: 'SYSTEM', icon: 'History', sort_order: 14 },
  
  // FIELD TEAM
  { id: 'field_dashboard', name: 'Dashboard Lapangan', section: 'FIELD TEAM', icon: 'Smartphone', sort_order: 15 },
  { id: 'field_tasks', name: 'Pekerjaan Saya (Mobile)', section: 'FIELD TEAM', icon: 'CheckSquare', sort_order: 16 },
  { id: 'field_history', name: 'Riwayat Tugas', section: 'FIELD TEAM', icon: 'History', sort_order: 17 },
  
  // CLIENT / PRINCIPAL PORTAL (INDOMARCO, SMARTFREN, DLL)
  { id: 'client_dashboard', name: 'Dashboard Monitoring Klien', section: 'CLIENT', icon: 'LayoutDashboard', sort_order: 18 },
  { id: 'client_tasks', name: 'Progres & Evidensi Toko', section: 'CLIENT', icon: 'Store', sort_order: 19 },
  { id: 'client_ba', name: 'Dokumen BA Opname Klien', section: 'CLIENT', icon: 'FileCheck2', sort_order: 20 },
  { id: 'vendor_dashboard', name: 'Dashboard Vendor (Alias)', section: 'CLIENT', icon: 'LayoutDashboard', sort_order: 21 },
  { id: 'vendor_tasks', name: 'Daftar Pekerjaan (Alias)', section: 'CLIENT', icon: 'Store', sort_order: 22 },
  { id: 'vendor_ba', name: 'Dokumen BA (Alias)', section: 'CLIENT', icon: 'FileCheck2', sort_order: 23 },
];

/**
 * Initialize Tables and Seed Default Permissions
 */
async function initPermissionsSchema() {
  await exec(`
    CREATE TABLE IF NOT EXISTS modules (
      id TEXT PRIMARY KEY,
      name TEXT NOT NULL,
      section TEXT NOT NULL,
      icon TEXT,
      sort_order INTEGER DEFAULT 0
    );

    CREATE TABLE IF NOT EXISTS role_permissions (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      role_code TEXT NOT NULL,
      module_id TEXT NOT NULL REFERENCES modules(id) ON DELETE CASCADE,
      can_view INTEGER DEFAULT 1,
      can_create INTEGER DEFAULT 0,
      can_update INTEGER DEFAULT 0,
      can_delete INTEGER DEFAULT 0,
      UNIQUE(role_code, module_id)
    );
  `);

  // Seed modules if empty or missing
  for (const mod of DEFAULT_MODULES) {
    await run(`
      INSERT OR REPLACE INTO modules (id, name, section, icon, sort_order)
      VALUES (?, ?, ?, ?, ?)
    `, [mod.id, mod.name, mod.section, mod.icon, mod.sort_order]);
  }

  // Seed default permissions for ADMIN if not set
  const adminCount = await get(`SELECT COUNT(*) as count FROM role_permissions WHERE role_code = 'ADMIN'`);
  if (!adminCount || adminCount.count === 0) {
    for (const mod of DEFAULT_MODULES) {
      const isField = mod.section === 'FIELD TEAM';
      const isVendor = mod.section === 'VENDOR';
      if (!isField && !isVendor) {
        await run(`
          INSERT OR REPLACE INTO role_permissions (role_code, module_id, can_view, can_create, can_update, can_delete)
          VALUES ('ADMIN', ?, 1, 1, 1, 1)
        `, [mod.id]);
      }
    }
  }

  // Seed default permissions for FIELD_TEAM
  const fieldCount = await get(`SELECT COUNT(*) as count FROM role_permissions WHERE role_code = 'FIELD_TEAM'`);
  if (!fieldCount || fieldCount.count === 0) {
    for (const mod of DEFAULT_MODULES) {
      const isField = mod.section === 'FIELD TEAM';
      if (isField) {
        await run(`
          INSERT OR REPLACE INTO role_permissions (role_code, module_id, can_view, can_create, can_update, can_delete)
          VALUES ('FIELD_TEAM', ?, 1, 1, 1, 0)
        `, [mod.id]);
      }
    }
  }

  // Seed default permissions for VENDOR / CLIENT
  for (const mod of DEFAULT_MODULES) {
    if (mod.section === 'CLIENT' || mod.section === 'VENDOR') {
      await run(`
        INSERT OR REPLACE INTO role_permissions (role_code, module_id, can_view, can_create, can_update, can_delete)
        VALUES ('VENDOR', ?, 1, 0, 0, 0)
      `, [mod.id]);
    }
  }
}

/**
 * Get full permission matrix for a specific role or all roles
 */
async function getPermissionMatrix(roleCode = 'ADMIN') {
  await initPermissionsSchema();
  const modules = await query(`SELECT * FROM modules ORDER BY sort_order ASC`);
  const permissions = await query(`SELECT * FROM role_permissions WHERE role_code = ?`, [roleCode]);

  const permMap = {};
  for (const p of permissions) {
    permMap[p.module_id] = {
      can_view: Boolean(p.can_view),
      can_create: Boolean(p.can_create),
      can_update: Boolean(p.can_update),
      can_delete: Boolean(p.can_delete)
    };
  }

  const matrix = modules.map(m => ({
    ...m,
    can_view: permMap[m.id]?.can_view ?? false,
    can_create: permMap[m.id]?.can_create ?? false,
    can_update: permMap[m.id]?.can_update ?? false,
    can_delete: permMap[m.id]?.can_delete ?? false
  }));

  const roles = await query(`SELECT code, name FROM roles ORDER BY id ASC`);

  return {
    role: roleCode,
    roles,
    matrix
  };
}

/**
 * Save/Update permissions matrix for a role (Superuser only)
 */
async function updateRolePermissions({ role_code, permissions }, currentUser, ipAddress) {
  await initPermissionsSchema();
  if (!role_code) throw new Error('Role code wajib disertakan.');

  for (const item of permissions) {
    await run(`
      INSERT INTO role_permissions (role_code, module_id, can_view, can_create, can_update, can_delete)
      VALUES (?, ?, ?, ?, ?, ?)
      ON CONFLICT(role_code, module_id) DO UPDATE SET
        can_view = excluded.can_view,
        can_create = excluded.can_create,
        can_update = excluded.can_update,
        can_delete = excluded.can_delete
    `, [
      role_code,
      item.module_id || item.id,
      item.can_view ? 1 : 0,
      item.can_create ? 1 : 0,
      item.can_update ? 1 : 0,
      item.can_delete ? 1 : 0
    ]);
  }

  await logAudit({
    userId: currentUser.id,
    userName: currentUser.name,
    action: 'UPDATE_ROLE_PERMISSIONS',
    entityType: 'ROLE_PERMISSIONS',
    entityId: 0,
    newValue: { role_code, count: permissions.length },
    ipAddress
  });

  return await getPermissionMatrix(role_code);
}

/**
 * Get active user permissions map
 */
async function getUserPermissions(currentUser) {
  await initPermissionsSchema();
  if (!currentUser) return {};

  // Superuser has 100% unconditional access to all modules and CRUD actions
  if (currentUser.role === 'SUPERUSER') {
    const modules = await query(`SELECT * FROM modules`);
    const superMap = {};
    for (const m of modules) {
      superMap[m.id] = { can_view: true, can_create: true, can_update: true, can_delete: true };
    }
    // Also include generic superuser modules
    superMap['dashboard'] = { can_view: true, can_create: true, can_update: true, can_delete: true };
    superMap['users'] = { can_view: true, can_create: true, can_update: true, can_delete: true };
    superMap['settings'] = { can_view: true, can_create: true, can_update: true, can_delete: true };
    superMap['audit_logs'] = { can_view: true, can_create: true, can_update: true, can_delete: true };
    superMap['permissions'] = { can_view: true, can_create: true, can_update: true, can_delete: true };
    return superMap;
  }

  const rows = await query(`SELECT module_id, can_view, can_create, can_update, can_delete FROM role_permissions WHERE role_code = ?`, [currentUser.role]);
  const userMap = {};
  for (const r of rows) {
    userMap[r.module_id] = {
      can_view: Boolean(r.can_view),
      can_create: Boolean(r.can_create),
      can_update: Boolean(r.can_update),
      can_delete: Boolean(r.can_delete)
    };
  }

  return userMap;
}

module.exports = {
  initPermissionsSchema,
  getPermissionMatrix,
  updateRolePermissions,
  getUserPermissions
};
