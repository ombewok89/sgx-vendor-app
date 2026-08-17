const express = require('express');
const router = express.Router();
const { getAuditLogs } = require('../services/auditService');
const { getNotifications } = require('../services/notificationService');
const { getSystemSettings, updateSystemSetting } = require('../services/masterDataService');
const { authenticate } = require('../middleware/auth');
const { requireRoles } = require('../middleware/rbac');

router.use(authenticate);

// Audit logs (Admin & Superuser)
router.get('/audit-logs', requireRoles('ADMIN', 'SUPERUSER'), async (req, res) => {
  try {
    const { entity_type, entity_id, limit } = req.query;
    const logs = await getAuditLogs({
      entityType: entity_type,
      entityId: entity_id,
      limit: parseInt(limit, 10) || 100
    });
    res.json({ success: true, data: logs });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

// Notifications logs (Admin & Superuser)
router.get('/notifications', requireRoles('ADMIN', 'SUPERUSER'), async (req, res) => {
  try {
    const list = await getNotifications(parseInt(req.query.limit, 10) || 50);
    res.json({ success: true, data: list });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

// System settings (Superuser)
router.get('/settings', requireRoles('SUPERUSER'), async (req, res) => {
  try {
    const settings = await getSystemSettings();
    res.json({ success: true, data: settings });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.put('/settings', requireRoles('SUPERUSER'), async (req, res) => {
  try {
    const { key, value } = req.body;
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const updated = await updateSystemSetting(key, value, req.user, ipAddress);
    res.json({ success: true, data: updated, message: 'Pengaturan sistem berhasil diperbarui.' });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

module.exports = router;
