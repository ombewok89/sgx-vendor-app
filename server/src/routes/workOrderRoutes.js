const express = require('express');
const router = express.Router();
const workOrderService = require('../services/workOrderService');
const { authenticate } = require('../middleware/auth');
const { requireRoles, checkPermission } = require('../middleware/rbac');

router.use(authenticate);

// Get Work Orders list
router.get('/', async (req, res) => {
  try {
    const { status, vendor_id, area_id, search } = req.query;
    const workOrders = await workOrderService.getWorkOrders({
      status,
      vendorId: vendor_id,
      areaId: area_id,
      search,
      currentUser: req.user
    });
    res.json({ success: true, data: workOrders });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// Get Single Work Order Details
router.get('/:id', async (req, res) => {
  try {
    const workOrder = await workOrderService.getWorkOrderById(req.params.id, req.user);
    res.json({ success: true, data: workOrder });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// Create Work Order (Granular check: admin_spk.create)
router.post('/', checkPermission('admin_spk', 'create'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const newWorkOrder = await workOrderService.createWorkOrder(req.body, req.user, ipAddress);
    res.status(201).json({ success: true, data: newWorkOrder });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// Assign Team to Work Order (Granular check: admin_spk.update)
router.post('/:id/assign', checkPermission('admin_spk', 'update'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const updated = await workOrderService.assignTeam(req.params.id, req.body, req.user, ipAddress);
    res.json({ success: true, data: updated });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// Submit Work Order (Field Team, Admin & Superuser)
router.post('/:id/submit', requireRoles('FIELD_TEAM', 'ADMIN', 'SUPERUSER'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const updated = await workOrderService.submitWorkOrder(req.params.id, req.user, ipAddress);
    res.json({ success: true, data: updated, message: 'Pekerjaan berhasil diajukan untuk direview.' });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

module.exports = router;
