const { run, query, get } = require('../config/database');

/**
 * Sends a notification via Fonnte Gateway (or Mock in Development)
 * Never throws an error that breaks core workflow.
 */
async function sendNotification({ recipient, messageType, payload, title, text }) {
  const now = new Date().toISOString();
  let status = 'SENT';
  let errorMsg = null;

  try {
    const setting = await get(`SELECT value FROM system_settings WHERE key = 'fonnte_api_key'`);
    const apiKey = setting ? setting.value : null;

    // Simulated Fonnte API dispatch
    // In live production, fetch('https://api.fonnte.com/send', { method: 'POST', headers: { Authorization: apiKey }, body: ... })
    console.log(`[WhatsApp Gateway - Fonnte] Sending ${messageType} to ${recipient}: "${text || title}" (API Key: ${apiKey ? 'Configured' : 'Missing'})`);

    // Record notification in DB
    await run(
      `INSERT INTO notifications (provider, recipient, message_type, payload, status, error, sent_at, created_at)
       VALUES ('FONNTE', ?, ?, ?, ?, ?, ?, ?)`,
      [recipient, messageType, JSON.stringify({ title, text, ...payload }), status, errorMsg, now, now]
    );

    return { success: true, status };
  } catch (err) {
    console.error('Notification dispatch failure (Non-blocking):', err.message);
    await run(
      `INSERT INTO notifications (provider, recipient, message_type, payload, status, error, sent_at, created_at)
       VALUES ('FONNTE', ?, ?, ?, 'FAILED', ?, ?, ?)`,
      [recipient, messageType, JSON.stringify({ title, text, ...payload }), err.message, now, now]
    );
    return { success: false, error: err.message };
  }
}

async function getNotifications(limit = 50) {
  return await query(`SELECT * FROM notifications ORDER BY id DESC LIMIT ?`, [limit]);
}

module.exports = {
  sendNotification,
  getNotifications
};
