const express = require('express');
const router = express.Router();
const authService = require('../services/authService');
const { authenticate } = require('../middleware/auth');

router.post('/login', async (req, res) => {
  try {
    const { email, password } = req.body;
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const result = await authService.login({ email, password, ipAddress });
    res.json({ success: true, ...result });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

router.get('/me', authenticate, async (req, res) => {
  res.json({ success: true, user: req.user });
});

router.put('/profile', authenticate, async (req, res) => {
  try {
    const { name, email, phone, currentPassword, newPassword } = req.body;
    const ipAddress = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const result = await authService.updateProfile({
      userId: req.user.id,
      name,
      email,
      phone,
      currentPassword,
      newPassword,
      ipAddress
    });
    res.json({ success: true, message: 'Profil berhasil diperbarui.', ...result });
  } catch (error) {
    res.status(400).json({ success: false, message: error.message });
  }
});

module.exports = router;
