const { run, query } = require('../config/database');

/**
 * Records an immutable audit log entry
 */
async function logAudit({ userId, userName, action, entityType, entityId, oldValue = null, newValue = null, ipAddress = null }) {
  try {
    const now = new Date().toISOString();
    const oldStr = oldValue ? (typeof oldValue === 'object' ? JSON.stringify(oldValue) : String(oldValue)) : null;
    const newStr = newValue ? (typeof newValue === 'object' ? JSON.stringify(newValue) : String(newValue)) : null;

    await run(
      `INSERT INTO audit_logs (user_id, user_name, action, entity_type, entity_id, old_value, new_value, ip_address, created_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [userId || null, userName || 'SYSTEM', action, entityType, entityId || null, oldStr, newStr, ipAddress, now]
    );
  } catch (err) {
    console.error('Failed to write audit log:', err.message);
  }
}

/**
 * Retrieves audit logs with optional filters
 */
async function getAuditLogs({ entityType, entityId, limit = 100 }) {
  let sql = `SELECT * FROM audit_logs WHERE 1=1`;
  const params = [];

  if (entityType) {
    sql += ` AND entity_type = ?`;
    params.push(entityType);
  }
  if (entityId) {
    sql += ` AND entity_id = ?`;
    params.push(entityId);
  }

  sql += ` ORDER BY id DESC LIMIT ?`;
  params.push(limit);

  return await query(sql, params);
}

module.exports = {
  logAudit,
  getAuditLogs
};
