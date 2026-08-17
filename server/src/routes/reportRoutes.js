const express = require('express');
const router = express.Router();
const { query, get } = require('../config/database');
const { authenticate } = require('../middleware/auth');

router.use(authenticate);

// Operational KPI Dashboard metrics
router.get('/dashboard-kpis', async (req, res) => {
  try {
    const user = req.user;
    let vendorFilter = '';
    const params = [];

    if (user.role === 'VENDOR') {
      vendorFilter = ' AND vendor_id = ?';
      params.push(user.vendor_id);
    }

    const totalOrders = await get(`SELECT COUNT(*) as count FROM work_orders WHERE 1=1 ${vendorFilter}`, params);
    const inProgress = await get(`SELECT COUNT(*) as count FROM work_orders WHERE status IN ('IN_PROGRESS', 'CHECKED_IN') ${vendorFilter}`, params);
    const waitingCheckin = await get(`SELECT COUNT(*) as count FROM work_orders WHERE status IN ('READY', 'ASSIGNED') ${vendorFilter}`, params);
    const waitingReview = await get(`SELECT COUNT(*) as count FROM work_orders WHERE status = 'SUBMITTED' ${vendorFilter}`, params);
    const revisionCount = await get(`SELECT COUNT(*) as count FROM work_orders WHERE status = 'REVISION' ${vendorFilter}`, params);
    const approvedCount = await get(`SELECT COUNT(*) as count FROM work_orders WHERE status = 'APPROVED' ${vendorFilter}`, params);
    const completedCount = await get(`SELECT COUNT(*) as count FROM work_orders WHERE status IN ('BA_OPNAME', 'COMPLETED') ${vendorFilter}`, params);

    // Check overdue orders (deadline < today and status not completed)
    const today = new Date().toISOString().split('T')[0];
    const overdueCount = await get(
      `SELECT COUNT(*) as count FROM work_orders WHERE deadline < ? AND status NOT IN ('COMPLETED', 'BA_OPNAME', 'APPROVED') ${vendorFilter}`,
      [today, ...params]
    );

    // Recent activity alerts
    const alerts = [];
    if (overdueCount.count > 0) {
      alerts.push({ type: 'danger', message: `${overdueCount.count} pekerjaan melewati tanggal deadline!` });
    }
    if (waitingReview.count > 0) {
      alerts.push({ type: 'warning', message: `${waitingReview.count} pekerjaan menunggu review admin.` });
    }
    if (revisionCount.count > 0) {
      alerts.push({ type: 'info', message: `${revisionCount.count} pekerjaan sedang dalam perbaikan revisi.` });
    }

    res.json({
      success: true,
      data: {
        total: totalOrders.count,
        in_progress: inProgress.count,
        waiting_checkin: waitingCheckin.count,
        waiting_review: waitingReview.count,
        revision: revisionCount.count,
        approved: approvedCount.count,
        completed: completedCount.count,
        overdue: overdueCount.count,
        alerts
      }
    });
  } catch (err) {
    res.status(400).json({ success: false, message: err.message });
  }
});

module.exports = router;
