const express = require('express');
const router = express.Router();
const reviewService = require('../services/reviewService');
const { authenticate } = require('../middleware/auth');
const { checkPermission } = require('../middleware/rbac');

router.use(authenticate);
router.use(checkPermission('admin_review', 'update'));

// Approve Work Order
router.post('/approve', async (req, res) => {
  try {
    const { work_order_id, review_notes } = req.body;
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;

    const result = await reviewService.approveWorkOrder({
      workOrderId: work_order_id,
      reviewNotes: review_notes
    }, req.user, ipAddress);

    res.json({
      success: true,
      message: 'Pekerjaan berhasil disetujui (APPROVED).',
      data: result
    });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// Request Specific Revision
router.post('/request-revision', async (req, res) => {
  try {
    const { work_order_id, target_stage, reason } = req.body;
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;

    const result = await reviewService.requestRevision({
      workOrderId: work_order_id,
      targetStage: target_stage,
      reason
    }, req.user, ipAddress);

    res.json({
      success: true,
      message: 'Permintaan revisi berhasil dikirim ke tim lapangan.',
      data: result
    });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

module.exports = router;
