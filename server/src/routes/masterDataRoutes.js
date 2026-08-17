const express = require('express');
const router = express.Router();
const masterDataService = require('../services/masterDataService');
const { authenticate } = require('../middleware/auth');
const { requireRoles, checkPermission } = require('../middleware/rbac');
const { uploadEvidence } = require('../middleware/upload');

const templateUpload = uploadEvidence.fields([
  { name: 'logo', maxCount: 1 },
  { name: 'background_image', maxCount: 1 },
  { name: 'header_image', maxCount: 1 },
  { name: 'footer_image', maxCount: 1 }
]);

router.use(authenticate);

// --- VENDORS ---
router.get('/vendors', async (req, res) => {
  try {
    const data = await masterDataService.getVendors(req.user);
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.post('/vendors', checkPermission('admin_vendors', 'create'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.createVendor(req.body, req.user, ipAddress);
    res.status(201).json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.put('/vendors/:id', checkPermission('admin_vendors', 'update'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.updateVendor(parseInt(req.params.id), req.body, req.user, ipAddress);
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.delete('/vendors/:id', checkPermission('admin_vendors', 'delete'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.deleteVendor(parseInt(req.params.id), req.user, ipAddress);
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

// --- AREAS ---
router.get('/areas', async (req, res) => {
  try {
    const data = await masterDataService.getAreas();
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.post('/areas', checkPermission('admin_areas', 'create'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.createArea(req.body, req.user, ipAddress);
    res.status(201).json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.put('/areas/:id', checkPermission('admin_areas', 'update'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.updateArea(parseInt(req.params.id), req.body, req.user, ipAddress);
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.delete('/areas/:id', checkPermission('admin_areas', 'delete'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.deleteArea(parseInt(req.params.id), req.user, ipAddress);
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

// --- JOB TYPES ---
router.get('/job-types', async (req, res) => {
  try {
    const data = await masterDataService.getJobTypes();
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.post('/job-types', checkPermission('admin_jobtypes', 'create'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.createJobType(req.body, req.user, ipAddress);
    res.status(201).json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.put('/job-types/:id', checkPermission('admin_jobtypes', 'update'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.updateJobType(parseInt(req.params.id), req.body, req.user, ipAddress);
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.delete('/job-types/:id', checkPermission('admin_jobtypes', 'delete'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.deleteJobType(parseInt(req.params.id), req.user, ipAddress);
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

// --- FIELD TEAMS ---
router.get('/field-teams', async (req, res) => {
  try {
    const data = await masterDataService.getFieldTeams();
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.post('/field-teams', checkPermission('admin_teams', 'create'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.createFieldTeam(req.body, req.user, ipAddress);
    res.status(201).json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.put('/field-teams/:id', checkPermission('admin_teams', 'update'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.updateFieldTeam(parseInt(req.params.id), req.body, req.user, ipAddress);
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.delete('/field-teams/:id', checkPermission('admin_teams', 'delete'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.deleteFieldTeam(parseInt(req.params.id), req.user, ipAddress);
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

// --- USERS (SUPERUSER ONLY) ---
router.get('/users', requireRoles('ADMIN', 'SUPERUSER'), async (req, res) => {
  try {
    const { role, vendor_id } = req.query;
    const data = await masterDataService.getUsers({ role, vendorId: vendor_id });
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.post('/users', requireRoles('SUPERUSER'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.createUser(req.body, req.user, ipAddress);
    res.status(201).json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.put('/users/:id', requireRoles('SUPERUSER'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.updateUser(parseInt(req.params.id), req.body, req.user, ipAddress);
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.delete('/users/:id', requireRoles('SUPERUSER'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.deleteUser(parseInt(req.params.id), req.user, ipAddress);
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

// --- TEMPLATES ---
router.get('/templates', async (req, res) => {
  try {
    const data = await masterDataService.getTemplates();
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.get('/templates/:id', async (req, res) => {
  try {
    const data = await masterDataService.getTemplateById(parseInt(req.params.id));
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.post('/templates', checkPermission('admin_templates', 'create'), templateUpload, async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.createTemplate(req.body, req.files, req.user, ipAddress);
    res.status(201).json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.put('/templates/:id', checkPermission('admin_templates', 'update'), templateUpload, async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.updateTemplate(parseInt(req.params.id), req.body, req.files, req.user, ipAddress);
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.post('/templates/:id/set-default', checkPermission('admin_templates', 'update'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.setDefaultTemplate(parseInt(req.params.id), req.user, ipAddress);
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

router.delete('/templates/:id', checkPermission('admin_templates', 'delete'), async (req, res) => {
  try {
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const data = await masterDataService.deleteTemplate(parseInt(req.params.id), req.user, ipAddress);
    res.json({ success: true, data });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

module.exports = router;
