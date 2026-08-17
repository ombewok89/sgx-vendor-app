const permissionService = require('../services/permissionService');

/**
 * Middleware to restrict route access to specific roles
 * @param  {...string} allowedRoles
 */
function requireRoles(...allowedRoles) {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({ success: false, message: 'Authentication required' });
    }

    if (req.user.role === 'SUPERUSER') {
      return next(); // Superuser has unconditional access to all routes
    }

    if (!allowedRoles.includes(req.user.role)) {
      return res.status(403).json({
        success: false,
        message: `Forbidden. Role '${req.user.role}' is not authorized to access this resource. Allowed: [${allowedRoles.join(', ')}]`
      });
    }

    next();
  };
}

/**
 * Dynamic Granular Permission Check Middleware (Create, Read, Update, Delete)
 * @param {string} moduleId - e.g. 'admin_spk', 'admin_vendors'
 * @param {string} action - 'view' | 'create' | 'update' | 'delete'
 */
function checkPermission(moduleId, action = 'view') {
  return async (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({ success: false, message: 'Authentication required' });
    }

    if (req.user.role === 'SUPERUSER') {
      return next(); // Superuser has 100% unconditional access
    }

    try {
      const userPermissions = await permissionService.getUserPermissions(req.user);
      const modPerm = userPermissions[moduleId];

      const actionKey = action === 'view' || action === 'read' ? 'can_view' :
                        action === 'create' || action === 'add' ? 'can_create' :
                        action === 'update' || action === 'edit' ? 'can_update' :
                        action === 'delete' || action === 'remove' ? 'can_delete' : 'can_view';

      if (!modPerm || !modPerm[actionKey]) {
        return res.status(403).json({
          success: false,
          message: `Akses Ditolak: Anda tidak memiliki hak akses '${action.toUpperCase()}' pada modul '${moduleId}'. Silakan hubungi Supervisor/Superuser.`
        });
      }

      next();
    } catch (err) {
      return res.status(500).json({ success: false, message: `Permission Check Error: ${err.message}` });
    }
  };
}

/**
 * Ensures vendor queries only retrieve data belonging to the logged-in vendor
 */
function vendorIsolation(req, res, next) {
  if (!req.user) {
    return res.status(401).json({ success: false, message: 'Authentication required' });
  }

  if (req.user.role === 'VENDOR') {
    if (!req.user.vendor_id) {
      return res.status(403).json({ success: false, message: 'Vendor account is not associated with any vendor organization.' });
    }
    req.vendorScopeId = req.user.vendor_id;
  } else {
    req.vendorScopeId = null; // Admin and Superuser can see all
  }

  next();
}

module.exports = {
  requireRoles,
  checkPermission,
  vendorIsolation
};
