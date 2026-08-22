<template>
  <!-- 0. Public Guest Live Tracking (No Login Required) -->
  <PublicSpkTracker
    v-if="publicTrackToken"
    :token="publicTrackToken"
  />

  <!-- 0.1 Public Standalone Timestamp Camera (No Login Required) -->
  <PublicTimestampCamera
    v-else-if="isPublicTimestampCamera"
  />

  <!-- 1. Full Screen Loading State -->
  <div v-else-if="auth.loading.value" class="min-h-screen bg-slate-950 flex flex-col items-center justify-center space-y-4">
    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-600 p-1.5 shadow-2xl shadow-amber-500/20 border border-white/20 animate-pulse">
      <img src="/sgx_logo.png" alt="Sinar Grafika Logo" class="w-full h-full object-contain rounded-xl" />
    </div>
    <div class="flex items-center gap-2 text-xs font-bold text-slate-400 font-mono">
      <span class="w-2 h-2 rounded-full bg-purple-500 animate-ping"></span>
      <span>Memuat Sesi SGX Vendor...</span>
    </div>
  </div>

  <!-- 2. Unauthenticated: Show Enterprise Login Page -->
  <LoginPage
    v-else-if="!auth.user.value"
    @login-success="handleLoginSuccess"
  />

  <!-- 3. Authenticated: Full Application Stage -->
  <div v-else class="min-h-screen bg-mesh-header flex flex-col selection:bg-brand-500 selection:text-white">
    <!-- Navbar Header with Glassmorphism -->
    <Navbar
      @menu-toggle="sidebarOpen = !sidebarOpen"
      @select-spk="handleSelectWorkOrder"
      @open-ba="handleOpenBa"
    />

    <div class="flex-1 flex">
      <!-- Sidebar Navigation -->
      <Sidebar
        v-model:activeTab="activeTab"
        :isOpen="sidebarOpen"
        @close="sidebarOpen = false"
      />

      <!-- Main Content Stage with Smooth Transitions -->
      <main
        class="flex-1 lg:pl-64 p-3.5 sm:p-6 md:p-8 max-w-[1600px] w-full mx-auto animate-fade-in"
        :class="{ 'pb-24 lg:pb-8': auth.state.user?.role === 'FIELD_TEAM' || auth.state.user?.role === 'VENDOR' }"
      >
        <component
          :is="currentView"
          :key="activeTab"
          v-bind="currentViewProps"
          @open-create-spk="showCreateModal = true"
          @open-create="showCreateModal = true"
          @navigate-to-spk="activeTab = 'admin_spk'"
          @open-review="activeTab = 'admin_review'"
          @select-work-order="handleSelectWorkOrder"
          @open-detail="handleSelectWorkOrder"
          @navigate="handleFieldNavigate"
          @approved-success="handleApprovedSuccess"
          @open-ba="handleOpenBa"
          @preview-ba="handleOpenBa"
          @switch-tab="activeTab = $event"
        />
      </main>
    </div>

    <!-- Global Glassmorphic Modals -->
    <WorkOrderCreateModal
      v-if="showCreateModal"
      @close="showCreateModal = false"
      @success="handleCreateSuccess"
    />

    <WorkOrderDetailModal
      v-if="selectedWorkOrderId"
      :workOrderId="selectedWorkOrderId"
      @close="selectedWorkOrderId = null"
      @open-review="handleOpenReviewFromDetail"
      @open-ba="handleOpenBa"
      @refresh-list="() => {}"
    />

    <BaOpnameViewer
      v-if="activeBaPreview"
      :baData="activeBaPreview"
      @close="activeBaPreview = null"
    />

    <!-- Global User Profile & Security Modal -->
    <UserProfileModal
      v-if="showProfileModal"
      @close="showProfileModal = false"
    />

    <!-- Mobile Bottom Navigation Bar (Field Team Dedicated) -->
    <FieldBottomNav
      v-if="auth.state.user?.role === 'FIELD_TEAM'"
      v-model:activeTab="activeTab"
      :activeTaskCount="fieldActiveTaskCount"
      @open-profile="showProfileModal = true"
    />

    <!-- Mobile Bottom Navigation Bar (Client / Vendor Dedicated) -->
    <ClientBottomNav
      v-if="auth.state.user?.role === 'VENDOR'"
      v-model:activeTab="activeTab"
      :storeCount="clientStoreCount"
      :baCount="clientBaCount"
      @open-profile="showProfileModal = true"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useAuth } from './composables/useAuth';
import { api } from './services/api';

// Auth & Public Standalone Pages
import LoginPage from './pages/auth/LoginPage.vue';
import PublicSpkTracker from './pages/public/PublicSpkTracker.vue';
import PublicTimestampCamera from './pages/public/PublicTimestampCamera.vue';

// Components
import Navbar from './components/Navbar.vue';
import Sidebar from './components/Sidebar.vue';
import BaOpnameViewer from './components/BaOpnameViewer.vue';
import UserProfileModal from './components/UserProfileModal.vue';
import FieldBottomNav from './components/FieldBottomNav.vue';
import ClientBottomNav from './components/ClientBottomNav.vue';

// Admin Pages
import AdminDashboard from './pages/admin/AdminDashboard.vue';
import WorkOrderList from './pages/admin/WorkOrderList.vue';
import WorkOrderCreateModal from './pages/admin/WorkOrderCreateModal.vue';
import WorkOrderDetailModal from './pages/admin/WorkOrderDetailModal.vue';
import ReviewConsole from './pages/admin/ReviewConsole.vue';
import BaOpnameList from './pages/admin/BaOpnameList.vue';
import MasterVendors from './pages/admin/MasterVendors.vue';
import MasterTeams from './pages/admin/MasterTeams.vue';
import MasterAreas from './pages/admin/MasterAreas.vue';
import MasterJobTypes from './pages/admin/MasterJobTypes.vue';
import DocumentTemplateManager from './pages/admin/DocumentTemplateManager.vue';
import EvidenceGallery from './pages/admin/EvidenceGallery.vue';
import FieldIssuesManager from './pages/admin/FieldIssuesManager.vue';
import Reports from './pages/admin/Reports.vue';
import NotificationLogs from './pages/admin/NotificationLogs.vue';

// Field Team Pages
import FieldDashboard from './pages/field/FieldDashboard.vue';
import FieldTasks from './pages/field/FieldTasks.vue';
import FieldHistory from './pages/field/FieldHistory.vue';

// Client / Principal Pages (Indomarco, Smartfren, Perbankan)
import ClientDashboard from './pages/client/ClientDashboard.vue';
import ClientTaskList from './pages/client/ClientTaskList.vue';
import ClientBaList from './pages/client/ClientBaList.vue';

// Superuser Page
import SuperDashboard from './pages/superuser/SuperDashboard.vue';

const auth = useAuth();
const activeTab = ref('admin_dashboard');
const sidebarOpen = ref(false);
const publicTrackToken = ref('');
const isPublicTimestampCamera = ref(false);

function detectPublicTrackRoute() {
  const path = window.location.pathname;
  const searchParams = new URLSearchParams(window.location.search);
  const hash = window.location.hash;

  // 1. Detect Standalone Timestamp Camera Route
  if (
    path.includes('/timestamp') ||
    path.includes('/timeslip') ||
    path.includes('/gps-camera') ||
    searchParams.get('timestamp') ||
    searchParams.get('timeslip') ||
    hash.includes('timestamp') ||
    hash.includes('timeslip')
  ) {
    isPublicTimestampCamera.value = true;
    publicTrackToken.value = '';
    return;
  }
  isPublicTimestampCamera.value = false;

  // 2. Detect Live Work Tracker Route
  if (path.includes('/track/')) {
    const segments = path.split('/track/');
    publicTrackToken.value = segments[1]?.split('/')[0] || '';
  } else if (searchParams.get('track')) {
    publicTrackToken.value = searchParams.get('track').trim();
  } else if (hash.includes('track=')) {
    const parts = hash.split('track=');
    publicTrackToken.value = parts[1]?.split('&')[0] || '';
  } else if (hash.includes('/track/')) {
    const parts = hash.split('/track/');
    publicTrackToken.value = parts[1]?.split('/')[0] || '';
  }
}

detectPublicTrackRoute();
window.addEventListener('popstate', detectPublicTrackRoute);

// Global Modals State
const showCreateModal = ref(false);
const showProfileModal = ref(false);
const selectedWorkOrderId = ref(null);
const activeBaPreview = ref(null);
const fieldActiveTaskCount = ref(0);
const clientStoreCount = ref(0);
const clientBaCount = ref(0);

async function fetchFieldTaskCount() {
  if (auth.state.user?.role === 'FIELD_TEAM') {
    try {
      const res = await api.getWorkOrders();
      if (res.data) {
        fieldActiveTaskCount.value = res.data.filter(w => ['ASSIGNED', 'IN_PROGRESS', 'REVISION'].includes(w.status)).length;
      }
    } catch (e) {
      // Background load error suppressed
    }
  }
}

async function fetchClientBadgeCounts() {
  if (auth.state.user?.role === 'VENDOR') {
    try {
      const [woRes, baRes] = await Promise.all([
        api.getWorkOrders(),
        api.getBaList()
      ]);
      if (woRes.data) clientStoreCount.value = woRes.data.length;
      if (baRes.data) clientBaCount.value = baRes.data.length;
    } catch (e) {
      // Background load error suppressed
    }
  }
}

// Sync initial active tab when user role changes
watch(() => auth.state.user?.role, (newRole) => {
  if (newRole === 'FIELD_TEAM') {
    activeTab.value = 'field_dashboard';
    fetchFieldTaskCount();
  } else if (newRole === 'VENDOR') {
    activeTab.value = 'client_dashboard';
    fetchClientBadgeCounts();
  } else if (newRole === 'SUPERUSER') {
    activeTab.value = 'super_dashboard';
  } else {
    activeTab.value = 'admin_dashboard';
  }
}, { immediate: true });

onMounted(() => {
  fetchFieldTaskCount();
  fetchClientBadgeCounts();
});

const currentView = computed(() => {
  // Always guarantee SuperDashboard for SUPERUSER on executive dashboard
  if (auth.state.user?.role === 'SUPERUSER' && ['super_dashboard', 'dashboard', 'admin_dashboard'].includes(activeTab.value)) {
    return SuperDashboard;
  }

  switch (activeTab.value) {
    // SUPERUSER EXCLUSIVE
    case 'super_dashboard':
    case 'super_permissions':
    case 'super_users':
    case 'super_settings':
    case 'super_audit':
    case 'dashboard':
    case 'users':
    case 'settings':
    case 'audit_logs':
      return SuperDashboard;

    // FIELD TEAM
    case 'field_dashboard':
      return FieldDashboard;
    case 'field_tasks':
      return FieldTasks;
    case 'field_history':
      return FieldHistory;

    // CLIENT / PRINCIPAL (INDOMARCO, SMARTFREN, DLL)
    case 'client_dashboard':
    case 'vendor_dashboard':
      return ClientDashboard;
    case 'client_tasks':
    case 'vendor_tasks':
      return ClientTaskList;
    case 'client_ba':
    case 'vendor_ba':
      return ClientBaList;

    // ADMIN & OPERATIONAL WORK MONITORING
    case 'admin_spk':
      return WorkOrderList;
    case 'admin_review':
      return ReviewConsole;
    case 'admin_evidence':
      return EvidenceGallery;
    case 'admin_issues':
      return FieldIssuesManager;
    case 'admin_ba':
      return BaOpnameList;
    case 'admin_reports':
    case 'admin_audit':
      return Reports;
    case 'admin_vendors':
      return MasterVendors;
    case 'admin_teams':
      return MasterTeams;
    case 'admin_areas':
      return MasterAreas;
    case 'admin_jobtypes':
      return MasterJobTypes;
    case 'admin_templates':
      return DocumentTemplateManager;
    case 'admin_notifications':
      return NotificationLogs;
    case 'admin_dashboard':
    default:
      return AdminDashboard;
  }
});

const currentViewProps = computed(() => {
  switch (activeTab.value) {
    case 'field_tasks':
      return { initialWorkOrderId: selectedWorkOrderId.value };
    case 'super_dashboard':
    case 'dashboard':
      return { initialTab: 'dashboard' };
    case 'super_permissions':
      return { initialTab: 'permissions' };
    case 'super_users':
    case 'users':
      return { initialTab: 'users' };
    case 'super_settings':
    case 'settings':
      return { initialTab: 'settings' };
    case 'super_audit':
    case 'audit_logs':
      return { initialTab: 'audit' };
    case 'admin_vendors':
      return { activeSection: 'vendors' };
    case 'admin_teams':
      return { activeSection: 'teams' };
    case 'admin_areas':
      return { activeSection: 'areas' };
    case 'admin_jobtypes':
      return { activeSection: 'jobtypes' };
    case 'admin_review':
      return { selectedWorkOrderId: selectedWorkOrderId.value };
    default:
      return {};
  }
});

function handleSelectWorkOrder(id) {
  selectedWorkOrderId.value = id;
}

function handleFieldNavigate(tab, id = null) {
  if (id) {
    selectedWorkOrderId.value = id;
  }
  activeTab.value = tab;
}

function handleOpenReviewFromDetail(id) {
  selectedWorkOrderId.value = id;
  activeTab.value = 'admin_review';
}

function handleApprovedSuccess(id) {
  selectedWorkOrderId.value = id;
}

async function handleOpenBa(ba) {
  if (!ba) return;
  
  if (typeof ba === 'number' || typeof ba === 'string') {
    try {
      const res = await api.getBaById(ba);
      if (res.data) {
        activeBaPreview.value = res.data;
        return;
      }
    } catch (e) {
      console.warn('Could not fetch BA by ID, trying fallback:', e);
      alert('Gagal memuat dokumen BA: ' + e.message);
    }
  } else if (typeof ba === 'object') {
    // If ba is already a full BA object with ba_number or content
    if (ba.ba_number || ba.content_json || ba.content) {
      activeBaPreview.value = ba;
      return;
    }
    // If ba object only contains id or work_order_id
    const targetId = ba.id || ba.work_order_id;
    if (targetId) {
      try {
        const res = await api.getBaById(targetId);
        if (res.data) {
          activeBaPreview.value = res.data;
          return;
        }
      } catch (e) {
        console.warn('Could not fetch BA by object targetId:', e);
        alert('Gagal memuat dokumen BA: ' + e.message);
      }
    }
  }
}

function handleCreateSuccess() {
  activeTab.value = 'admin_spk';
}

function handleLoginSuccess(user) {
  if (user?.role === 'FIELD_TEAM') {
    activeTab.value = 'field_dashboard';
  } else if (user?.role === 'VENDOR') {
    activeTab.value = 'client_dashboard';
  } else if (user?.role === 'SUPERUSER') {
    activeTab.value = 'super_dashboard';
  } else {
    activeTab.value = 'admin_dashboard';
  }
}
</script>
