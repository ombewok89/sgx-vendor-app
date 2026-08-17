const jwt = require('jsonwebtoken');
const { get } = require('../config/database');

function getJwtSecret() {
  const secret = process.env.JWT_SECRET;
  if (!secret) {
    throw new Error('FATAL SECURITY ERROR: process.env.JWT_SECRET is not configured! Please provide JWT_SECRET in your .env file or hosting environment.');
  }
  return secret;
}

async function authenticate(req, res, next) {
  try {
    const authHeader = req.headers.authorization;
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res.status(401).json({ success: false, message: 'Authentication required. Missing Bearer token.' });
    }

    const token = authHeader.split(' ')[1];
    const decoded = jwt.verify(token, getJwtSecret());

    const user = await get('SELECT id, name, email, phone, role, vendor_id, is_active FROM users WHERE id = ?', [decoded.id]);
    if (!user || !user.is_active) {
      return res.status(401).json({ success: false, message: 'Invalid or inactive user account.' });
    }

    req.user = user;
    next();
  } catch (error) {
    return res.status(401).json({ success: false, message: 'Invalid or expired token', error: error.message });
  }
}

module.exports = {
  authenticate,
  getJwtSecret
};
