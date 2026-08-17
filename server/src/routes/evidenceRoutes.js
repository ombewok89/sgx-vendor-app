const express = require('express');
const router = express.Router();
const evidenceService = require('../services/evidenceService');
const { authenticate } = require('../middleware/auth');
const { requireRoles, checkPermission } = require('../middleware/rbac');
const { uploadEvidence } = require('../middleware/upload');

router.use(authenticate);

// 1. Get All Evidence Photos for Media Gallery & Forensics
router.get('/photos', async (req, res) => {
  try {
    const { stage, vendor_id, area_id, work_order_id, search, limit, offset } = req.query;
    const data = await evidenceService.getAllEvidencePhotos({
      stage,
      vendorId: vendor_id,
      areaId: area_id,
      workOrderId: work_order_id,
      search,
      limit,
      offset
    }, req.user);

    res.json({ success: true, data });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// 2. Upload photo evidence (Field Team & Superuser)
router.post('/upload', requireRoles('FIELD_TEAM', 'SUPERUSER'), uploadEvidence.single('file'), async (req, res) => {
  try {
    const { work_order_id, item_id, stage, sequence, latitude, longitude, accuracy, notes } = req.body;
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;

    const result = await evidenceService.uploadPhotoEvidence({
      file: req.file,
      workOrderId: work_order_id,
      itemId: item_id,
      stage,
      sequence,
      latitude,
      longitude,
      accuracy,
      notes
    }, req.user, ipAddress);

    res.status(201).json({
      success: true,
      message: `Foto ${stage} berhasil diunggah dengan checksum hash SHA-256 terverifikasi.`,
      data: result
    });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// 2b. Delete photo evidence (Field Team, Admin, Superuser)
router.delete('/photos/:id', requireRoles('FIELD_TEAM', 'ADMIN', 'SUPERUSER'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const result = await evidenceService.deletePhotoEvidence(parseInt(req.params.id, 10), req.user, ipAddress);
    res.json({
      success: true,
      message: 'Foto bukti berhasil dihapus.',
      data: result
    });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// 2c. Stream Evidence Photo File Securely (Authenticated & Scoped)
router.get('/photos/:id/file', async (req, res) => {
  try {
    const { photo, fullPath } = await evidenceService.getEvidenceFile(parseInt(req.params.id, 10), req.user);
    res.setHeader('Content-Type', photo.mime_type || 'image/jpeg');
    res.setHeader('Content-Disposition', `inline; filename="${photo.file_name || 'evidence.jpg'}"`);
    res.sendFile(fullPath);
  } catch (error) {
    res.status(404).json({ success: false, message: error.message });
  }
});

// 3. Get Field Technical Issues
router.get('/issues', async (req, res) => {
  try {
    const { status, issue_type, vendor_id, work_order_id, search } = req.query;
    const data = await evidenceService.getFieldIssues({
      status,
      issueType: issue_type,
      vendorId: vendor_id,
      workOrderId: work_order_id,
      search
    }, req.user);

    res.json({ success: true, data });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// 4. Report Technical Issue / Kendala (Field Team)
router.post('/issues', requireRoles('FIELD_TEAM', 'SUPERUSER'), async (req, res) => {
  try {
    const { work_order_id, has_issue, issue_type, notes } = req.body;
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;

    const result = await evidenceService.reportIssue({
      workOrderId: work_order_id,
      hasIssue: has_issue,
      issueType: issue_type,
      notes
    }, req.user, ipAddress);

    res.status(201).json({
      success: true,
      message: 'Status kendala teknis berhasil disimpan.',
      data: result
    });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

// 5. Resolve Field Technical Issue (Granular check: admin_issues.update)
router.post('/issues/:id/resolve', checkPermission('admin_issues', 'update'), async (req, res) => {
  try {
    const { resolution_notes, status } = req.body;
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;

    const result = await evidenceService.resolveIssue(
      parseInt(req.params.id, 10),
      { resolution_notes, status: status || 'RESOLVED' },
      req.user,
      ipAddress
    );

    res.json({
      success: true,
      message: 'Kendala teknis berhasil ditangani & diselesaikan.',
      data: result
    });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

module.exports = router;
