const express = require('express');
const router = express.Router();
const notificationFeedService = require('../services/notificationFeedService');
const { authenticate } = require('../middleware/auth');

router.use(authenticate);

// GET all in-app notifications for current authenticated user (with role & dynamic client filtering)
router.get('/', async (req, res) => {
  try {
    const currentUser = req.user || { id: 1, role: 'ADMIN' };
    await notificationFeedService.seedInitialNotifications();
    const notifs = await notificationFeedService.getNotificationsForUser(currentUser, req.query);
    const unreadCount = notifs.filter(n => !n.is_read).length;
    res.json({ success: true, data: notifs, unreadCount });
  } catch (err) {
    console.error('Error fetching notification feed:', err);
    res.status(500).json({ success: false, message: err.message });
  }
});

// POST mark specific notification as read
router.post('/mark-read/:id', async (req, res) => {
  try {
    const currentUser = req.user || { id: 1, role: 'ADMIN' };
    await notificationFeedService.markAsRead(req.params.id, currentUser.id);
    res.json({ success: true, message: 'Notification marked as read' });
  } catch (err) {
    console.error('Error marking notification as read:', err);
    res.status(500).json({ success: false, message: err.message });
  }
});

// POST mark all notifications as read
router.post('/mark-all-read', async (req, res) => {
  try {
    const currentUser = req.user || { id: 1, role: 'ADMIN' };
    await notificationFeedService.markAllAsRead(currentUser.id, currentUser);
    res.json({ success: true, message: 'All notifications marked as read' });
  } catch (err) {
    console.error('Error marking all notifications as read:', err);
    res.status(500).json({ success: false, message: err.message });
  }
});

module.exports = router;
