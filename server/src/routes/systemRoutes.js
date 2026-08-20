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

// Test WhatsApp Gateway
router.post('/test-whatsapp', requireRoles('SUPERUSER', 'ADMIN'), async (req, res) => {
  try {
    const { phone, message } = req.body;
    if (!phone) {
      return res.status(400).json({ success: false, message: 'Nomor telepon WhatsApp tujuan wajib diisi.' });
    }

    let target = String(phone).replace(/[^0-9]/g, '');
    if (target.startsWith('08')) {
      target = '628' + target.substring(2);
    }

    const settings = await getSystemSettings();
    const fonnteKeySetting = settings.find(s => s.key === 'fonnte_api_key');
    const apiKey = fonnteKeySetting?.value || process.env.FONNTE_API_KEY || 'GoPzcxdiUP2yt5HbByUK';

    const msg = message || `🔔 *SGX Work Evidence System Test*\n\nUji coba konektivitas WhatsApp Gateway Fonnte berhasil terhubung secara normal pada ${new Date().toLocaleString('id-ID')} WIB.`;

    const https = require('https');
    const payload = JSON.stringify({
      target: target,
      message: msg,
      countryCode: '62'
    });

    const options = {
      hostname: 'api.fonnte.com',
      path: '/send',
      method: 'POST',
      headers: {
        'Authorization': apiKey,
        'Content-Type': 'application/json',
        'Content-Length': Buffer.byteLength(payload)
      },
      timeout: 15000
    };

    const request = https.request(options, (response) => {
      let data = '';
      response.on('data', chunk => { data += chunk; });
      response.on('end', () => {
        try {
          const parsed = JSON.parse(data);
          return res.json({
            success: true,
            message: 'Uji coba pengiriman notifikasi WhatsApp berhasil dikirim.',
            data: parsed
          });
        } catch (e) {
          return res.json({
            success: true,
            message: 'Uji coba pengiriman notifikasi WhatsApp berhasil dikirim.',
            data: { raw: data }
          });
        }
      });
    });

    request.on('error', (e) => {
      return res.status(500).json({
        success: false,
        message: 'Gagal menghubungi server Fonnte: ' + e.message
      });
    });

    request.write(payload);
    request.end();
  } catch (err) {
    return res.status(500).json({
      success: false,
      message: 'Gagal mengirim pesan WhatsApp: ' + err.message
    });
  }
});

module.exports = router;
