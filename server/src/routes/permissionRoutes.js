const express = require('express');
const router = express.Router();
const permissionService = require('../services/permissionService');
const { authenticate } = require('../middleware/auth');
const { requireRoles } = require('../middleware/rbac');

router.use(authenticate);

// Get Active Logged-in User's Permission Map
router.get('/my-permissions', async (req, res) => {
  try {
    const permissions = await permissionService.getUserPermissions(req.user);
    res.json({ success: true, data: permissions });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// Get Permission Matrix for a Role (Supervisor / Superuser only)
router.get('/matrix', requireRoles('SUPERUSER'), async (req, res) => {
  try {
    const roleCode = req.query.role || 'ADMIN';
    const matrix = await permissionService.getPermissionMatrix(roleCode);
    res.json({ success: true, data: matrix });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// Update Role Permissions (Supervisor / Superuser only)
router.post('/matrix', requireRoles('SUPERUSER'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const result = await permissionService.updateRolePermissions(req.body, req.user, ipAddress);
    res.json({
      success: true,
      message: `Hak akses role '${req.body.role_code}' berhasil diperbarui!`,
      data: result
    });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

module.exports = router;
