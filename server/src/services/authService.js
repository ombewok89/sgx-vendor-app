const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const { get, run, query } = require('../config/database');
const { getJwtSecret } = require('../middleware/auth');
const { logAudit } = require('./auditService');

async function login({ email, password, ipAddress }) {
  const user = await get(`
    SELECT u.id, u.name, u.email, u.password_hash, u.phone, u.role, u.vendor_id, u.is_active, v.name as vendor_name
    FROM users u
    LEFT JOIN vendors v ON u.vendor_id = v.id
    WHERE u.email = ?
  `, [email]);

  if (!user) {
    throw new Error('Email atau password tidak ditemukan');
  }

  if (!user.is_active) {
    throw new Error('Akun Anda dinonaktifkan. Silakan hubungi Superuser.');
  }

  const isMatch = await bcrypt.compare(password, user.password_hash);
  if (!isMatch) {
    throw new Error('Email atau password salah');
  }

  const token = jwt.sign(
    { id: user.id, role: user.role, vendor_id: user.vendor_id },
    getJwtSecret(),
    { expiresIn: '7d' }
  );

  await logAudit({
    userId: user.id,
    userName: user.name,
    action: 'USER_LOGIN',
    entityType: 'USER',
    entityId: user.id,
    ipAddress
  });

  return {
    token,
    user: {
      id: user.id,
      name: user.name,
      email: user.email,
      phone: user.phone,
      role: user.role,
      vendor_id: user.vendor_id,
      vendor_name: user.vendor_name
    }
  };
}

async function updateProfile({ userId, name, email, phone, currentPassword, newPassword, ipAddress }) {
  const user = await get(`SELECT * FROM users WHERE id = ?`, [userId]);
  if (!user) {
    throw new Error('Pengguna tidak ditemukan.');
  }

  // Check email uniqueness if email changed
  if (email && email.toLowerCase() !== user.email.toLowerCase()) {
    const existing = await get(`SELECT id FROM users WHERE LOWER(email) = LOWER(?) AND id != ?`, [email, userId]);
    if (existing) {
      throw new Error('Alamat email tersebut sudah digunakan oleh akun lain.');
    }
  }

  let finalPasswordHash = user.password_hash;

  // If changing password, verify old password strictly
  if (newPassword) {
    if (!currentPassword) {
      throw new Error('Silakan masukkan kata sandi lama Anda untuk keamanan.');
    }
    const isMatch = await bcrypt.compare(currentPassword, user.password_hash);
    if (!isMatch) {
      throw new Error('Kata sandi lama yang Anda masukkan salah.');
    }
    if (newPassword.length < 6) {
      throw new Error('Kata sandi baru minimal 6 karakter.');
    }
    finalPasswordHash = await bcrypt.hash(newPassword, 10);
  }

  const updatedName = name ? name.trim() : user.name;
  const updatedEmail = email ? email.trim().toLowerCase() : user.email;
  const updatedPhone = phone !== undefined ? phone.trim() : user.phone;

  if (newPassword) {
    await run(`
      UPDATE users 
      SET name = ?, email = ?, phone = ?, password_hash = ?, updated_at = CURRENT_TIMESTAMP
      WHERE id = ?
    `, [updatedName, updatedEmail, updatedPhone, finalPasswordHash, userId]);
  } else {
    await run(`
      UPDATE users 
      SET name = ?, email = ?, phone = ?, updated_at = CURRENT_TIMESTAMP
      WHERE id = ?
    `, [updatedName, updatedEmail, updatedPhone, userId]);
  }

  await logAudit({
    userId,
    userName: updatedName,
    action: 'UPDATE_PROFILE',
    entityType: 'USER',
    entityId: userId,
    newValue: { name: updatedName, email: updatedEmail, phone: updatedPhone, passwordChanged: Boolean(newPassword) },
    ipAddress
  });

  const updatedUser = await get(`
    SELECT u.id, u.name, u.email, u.phone, u.role, u.vendor_id, v.name as vendor_name
    FROM users u
    LEFT JOIN vendors v ON u.vendor_id = v.id
    WHERE u.id = ?
  `, [userId]);

  return {
    user: updatedUser
  };
}

module.exports = {
  login,
  updateProfile
};


