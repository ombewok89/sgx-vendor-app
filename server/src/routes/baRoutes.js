const express = require('express');
const router = express.Router();
const baService = require('../services/baService');
const { authenticate } = require('../middleware/auth');
const { checkPermission } = require('../middleware/rbac');

router.use(authenticate);

// Generate BA Opname (Granular check: admin_ba.create)
router.post('/generate', checkPermission('admin_ba', 'create'), async (req, res) => {
  try {
    const { work_order_id, template_id, ba_number, ba_date } = req.body;
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;

    const result = await baService.generateBaOpname({
      workOrderId: work_order_id,
      templateId: template_id,
      customBaNumber: ba_number,
      baDate: ba_date
    }, req.user, ipAddress);

    res.status(201).json({
      success: true,
      message: 'Berita Acara (BA) Opname berhasil diterbitkan.',
      data: result
    });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// Get List of BA Documents
router.get('/', async (req, res) => {
  try {
    const list = await baService.getBaDocuments({ currentUser: req.user });
    res.json({ success: true, data: list });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// Get Single BA Document by ID
router.get('/:id', async (req, res) => {
  try {
    const ba = await baService.getBaDocumentById(req.params.id, req.user);
    res.json({ success: true, data: ba });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// Complete Work Order (Granular check: admin_ba.update)
router.post('/complete/:work_order_id', checkPermission('admin_ba', 'update'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const result = await baService.completeWorkOrder(req.params.work_order_id, req.user, ipAddress);
    res.json({ success: true, message: 'Pekerjaan dinyatakan selesai secara menyeluruh (COMPLETED).', data: result });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

module.exports = router;
