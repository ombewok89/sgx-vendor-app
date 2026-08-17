const express = require('express');
const router = express.Router();
const checkInService = require('../services/checkInService');
const { authenticate } = require('../middleware/auth');
const { requireRoles } = require('../middleware/rbac');

router.use(authenticate);

router.post('/', requireRoles('FIELD_TEAM', 'SUPERUSER'), async (req, res) => {
  try {
    const { work_order_id, latitude, longitude, accuracy, client_timestamp, address_note } = req.body;
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;

    const result = await checkInService.performCheckIn({
      workOrderId: work_order_id,
      latitude: parseFloat(latitude),
      longitude: parseFloat(longitude),
      accuracy: parseFloat(accuracy || 0),
      clientTimestamp: client_timestamp,
      addressNote: address_note
    }, req.user, ipAddress);

    res.status(201).json({
      success: true,
      message: 'Check-in berhasil tercatat dengan lokasi GPS resmi.',
      data: result
    });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

module.exports = router;
