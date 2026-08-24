import { ref, reactive, computed } from 'vue';
import { api } from '../services/api';

const state = reactive({
  user: null,
  token: localStorage.getItem('sgx_token') || null,
  permissions: {},
  loading: true
});

let initialized = false;

async function fetchPermissions() {
  try {
    const res = await api.getMyPermissions();
    state.permissions = res.data || {};
  } catch (err) {
    console.warn('Failed to load user permissions:', err);
    state.permissions = {};
  }
}

async function initialize() {
  if (initialized) return;
  initialized = true;

  if (state.token) {
    try {
      // Parallel loading: fetch user profile and permissions concurrently
      const [userRes, permRes] = await Promise.all([
        api.getMe(),
        api.getMyPermissions().catch(() => ({ data: {} }))
      ]);

      if (userRes?.user) {
        state.user = userRes.user;
        state.permissions = permRes.data || {};
      } else {
        throw new Error('User not returned');
      }
    } catch (err) {
      console.error('Session expired or invalid:', err);
      logout();
    }
  }
  state.loading = false;
}

async function login(email, password) {
  state.loading = true;
  try {
    const res = await api.login(email, password);
    localStorage.setItem('sgx_token', res.token);
    state.token = res.token;
    state.user = res.user;

    // Parallel fetch permissions immediately after login
    api.getMyPermissions()
      .then(permRes => { state.permissions = permRes.data || {}; })
      .catch(() => { state.permissions = {}; });

    return res.user;
  } finally {
    state.loading = false;
  }
}

async function updateProfile(profileData) {
  const res = await api.updateProfile(profileData);
  if (res.user) {
    state.user = res.user;
  }
  return res;
}

function logout() {
  localStorage.removeItem('sgx_token');
  state.token = null;
  state.user = null;
  state.permissions = {};
}

/**
 * Check if the active user has a specific permission
 * @param {string} moduleId - e.g. 'admin_spk'
 * @param {'view' | 'create' | 'update' | 'delete'} action
 */
function hasPermission(moduleId, action = 'view') {
  // Superuser has 100% unconditional access to everything
  if (state.user?.role === 'SUPERUSER') {
    return true;
  }

  if (!state.permissions || Object.keys(state.permissions).length === 0) {
    return true; // Fallback to role defaults during loading
  }

  const modPerm = state.permissions[moduleId];
  if (!modPerm) return false;

  const key = action === 'view' || action === 'read' ? 'can_view' :
              action === 'create' || action === 'add' ? 'can_create' :
              action === 'update' || action === 'edit' ? 'can_update' :
              action === 'delete' || action === 'remove' ? 'can_delete' : 'can_view';

  return Boolean(modPerm[key]);
}

export function useAuth() {
  if (!initialized) {
    initialize();
  }

  return {
    state,
    user: computed(() => state.user),
    token: computed(() => state.token),
    permissions: computed(() => state.permissions),
    loading: computed(() => state.loading),
    hasPermission,
    fetchPermissions,
    login,
    updateProfile,
    logout
  };
}
