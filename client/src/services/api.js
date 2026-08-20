const API_BASE = import.meta.env.VITE_API_URL || '/api';
const BACKEND_BASE = API_BASE.replace(/\/api\/?$/, '');

export function getFileUrl(filePath) {
  if (!filePath) return '';
  if (filePath.startsWith('http://') || filePath.startsWith('https://') || filePath.startsWith('data:')) {
    return filePath;
  }
  const cleanPath = filePath.replace(/^\/?storage\//, '').replace(/^\//, '');
  return `/stream.php?file=${encodeURIComponent(cleanPath)}`;
}

function getAuthHeaders() {
  const token = localStorage.getItem('sgx_token');
  return token ? { Authorization: `Bearer ${token}` } : {};
}

async function request(endpoint, options = {}) {
  const url = `${API_BASE}${endpoint}`;
  const headers = {
    'Accept': 'application/json',
    ...getAuthHeaders(),
    ...(options.headers || {})
  };

  // If not FormData, default to application/json
  if (!(options.body instanceof FormData) && !headers['Content-Type']) {
    headers['Content-Type'] = 'application/json';
  }

  const response = await fetch(url, {
    ...options,
    headers
  });

  const data = await response.json();
  if (!response.ok) {
    throw new Error(data.message || 'Terjadi kesalahan pada server');
  }
  return data;
}

export const api = {
  // Auth
  login: (email, password) => request('/auth/login', { method: 'POST', body: JSON.stringify({ email, password }) }),
  getMe: () => request('/auth/me'),
  updateProfile: (data) => request('/auth/profile', { method: 'PUT', body: JSON.stringify(data) }),

  // Work Orders
  getWorkOrders: (params = {}) => {
    const qs = new URLSearchParams(params).toString();
    return request(`/work-orders${qs ? `?${qs}` : ''}`);
  },
  getWorkOrderById: (id) => request(`/work-orders/${id}`),
  createWorkOrder: (data) => request('/work-orders', { method: 'POST', body: JSON.stringify(data) }),
  updateWorkOrder: (id, data) => request(`/work-orders/${id}/update`, { method: 'POST', body: JSON.stringify(data) }),
  updateWorkOrderLocation: (id, lat, lng) => request('/work-orders/update-location', { method: 'POST', body: JSON.stringify({ work_order_id: id, target_lat: lat, target_lng: lng }) }),
  toggleWorkOrderCheckin: (id, require_checkin) => request('/work-orders/toggle-checkin', { method: 'POST', body: JSON.stringify({ work_order_id: id, require_checkin }) }),
  assignTeam: (id, data) => request(`/work-orders/${id}/assign`, { method: 'POST', body: JSON.stringify(data) }),
  submitWorkOrder: (id) => request(`/work-orders/${id}/submit`, { method: 'POST' }),

  // Check-In
  checkIn: (data) => request('/check-ins', { method: 'POST', body: JSON.stringify(data) }),

  // Evidence & Field Issues
  getEvidencePhotos: (params = {}) => {
    const qs = new URLSearchParams(params).toString();
    return request(`/evidence/photos${qs ? `?${qs}` : ''}`);
  },
  uploadEvidence: (formData) => request('/evidence/upload', { method: 'POST', body: formData }),
  deleteEvidencePhoto: (id) => request(`/evidence/photos/${id}`, { method: 'DELETE' }),
  getFieldIssues: (params = {}) => {
    const qs = new URLSearchParams(params).toString();
    return request(`/evidence/issues${qs ? `?${qs}` : ''}`);
  },
  reportIssue: (data) => request('/evidence/issues', { method: 'POST', body: JSON.stringify(data) }),
  resolveFieldIssue: (id, data) => request(`/evidence/issues/${id}/resolve`, { method: 'POST', body: JSON.stringify(data) }),

  // Reviews & Revisions
  approveWorkOrder: (data) => request('/reviews/approve', { method: 'POST', body: JSON.stringify(data) }),
  requestRevision: (data) => request('/reviews/request-revision', { method: 'POST', body: JSON.stringify(data) }),

  // BA Opname
  generateBa: (data) => request('/ba/generate', { method: 'POST', body: JSON.stringify(data) }),
  getBaList: () => request('/ba'),
  getBaById: (id) => request(`/ba/${id}`),
  getBaByWorkOrderId: (workOrderId) => request(`/ba/${workOrderId}`),

  // Master Data CRUD
  getVendors: () => request('/master/vendors'),
  createVendor: (data) => request('/master/vendors', { method: 'POST', body: JSON.stringify(data) }),
  updateVendor: (id, data) => request(`/master/vendors/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteVendor: (id) => request(`/master/vendors/${id}`, { method: 'DELETE' }),

  getAreas: () => request('/master/areas'),
  createArea: (data) => request('/master/areas', { method: 'POST', body: JSON.stringify(data) }),
  updateArea: (id, data) => request(`/master/areas/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteArea: (id) => request(`/master/areas/${id}`, { method: 'DELETE' }),

  getJobTypes: () => request('/master/job-types'),
  createJobType: (data) => request('/master/job-types', { method: 'POST', body: JSON.stringify(data) }),
  updateJobType: (id, data) => request(`/master/job-types/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteJobType: (id) => request(`/master/job-types/${id}`, { method: 'DELETE' }),

  getFieldTeams: () => request('/master/field-teams'),
  createFieldTeam: (data) => request('/master/field-teams', { method: 'POST', body: JSON.stringify(data) }),
  updateFieldTeam: (id, data) => request(`/master/field-teams/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteFieldTeam: (id) => request(`/master/field-teams/${id}`, { method: 'DELETE' }),

  getUsers: (params = {}) => {
    const qs = new URLSearchParams(params).toString();
    return request(`/master/users${qs ? `?${qs}` : ''}`);
  },
  createUser: (data) => request('/master/users', { method: 'POST', body: JSON.stringify(data) }),
  updateUser: (id, data) => request(`/master/users/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteUser: (id) => request(`/master/users/${id}`, { method: 'DELETE' }),

  getTemplates: () => request('/master/templates'),
  getTemplateById: (id) => request(`/master/templates/${id}`),
  createTemplate: (formData) => request('/master/templates', { method: 'POST', body: formData }),
  updateTemplate: (id, formData) => request(`/master/templates/${id}`, { method: 'PUT', body: formData }),
  setDefaultTemplate: (id) => request(`/master/templates/${id}/set-default`, { method: 'POST' }),
  deleteTemplate: (id) => request(`/master/templates/${id}`, { method: 'DELETE' }),

  // Reports, KPIs & System
  getDashboardKpis: () => request('/reports/dashboard-kpis'),
  getAuditLogs: (params = {}) => {
    const qs = new URLSearchParams(params).toString();
    return request(`/system/audit-logs${qs ? `?${qs}` : ''}`);
  },
  getSettings: () => request('/system/settings'),
  updateSetting: (key, value) => request('/system/settings', { method: 'PUT', body: JSON.stringify({ key, value }) }),
  testWhatsApp: (phone, message) => request('/system/test-whatsapp', { method: 'POST', body: JSON.stringify({ phone, message }) }),

  // Dynamic RBAC & Permissions (Supervisor only)
  getPermissionMatrix: (role = 'ADMIN') => request(`/permissions/matrix?role=${role}`),
  updateRolePermissions: (data) => request('/permissions/matrix', { method: 'POST', body: JSON.stringify(data) }),
  getMyPermissions: () => request('/permissions/my-permissions'),

  // In-App Notification Feed (Role-Based & Client Isolated)
  getNotificationFeed: (params = {}) => {
    const qs = new URLSearchParams(params).toString();
    return request(`/notifications-feed${qs ? '?' + qs : ''}`);
  },
  markNotificationRead: (id) => request(`/notifications-feed/mark-read/${id}`, { method: 'POST' }),
  markAllNotificationsRead: () => request('/notifications-feed/mark-all-read', { method: 'POST' })
};
