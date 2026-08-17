<template>
  <!-- 1. Full Screen Loading State -->
  <div v-if="auth.loading.value" class="min-h-screen bg-slate-950 flex flex-col items-center justify-center space-y-4">
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
      <main class="flex-1 lg:pl-64 p-4 sm:p-6 md:p-8 max-w-[1600px] w-full mx-auto animate-fade-in">
        <component
          :is="currentView"
          v-bind="currentViewProps"
          @open-create-spk="showCreateModal = true"
          @open-create="showCreateModal = true"
          @navigate-to-spk="activeTab = 'admin_spk'"
          @open-review="activeTab = 'admin_review'"
          @select-work-order="handleSelectWorkOrder"
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
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useAuth } from './composables/useAuth';

// Auth Page
import LoginPage from './pages/auth/LoginPage.vue';

// Components
import Navbar from './components/Navbar.vue';
import Sidebar from './components/Sidebar.vue';
import BaOpnameViewer from './components/BaOpnameViewer.vue';

// Admin Pages
import AdminDashboard from './pages/admin/AdminDashboard.vue';
import WorkOrderList from './pages/admin/WorkOrderList.vue';
import WorkOrderCreateModal from './pages/admin/WorkOrderCreateModal.vue';
import WorkOrderDetailModal from './pages/admin/WorkOrderDetailModal.vue';
import ReviewConsole from './pages/admin/ReviewConsole.vue';
import BaOpnameList from './pages/admin/BaOpnameList.vue';
import MasterData from './pages/admin/MasterData.vue';
import DocumentTemplateManager from './pages/admin/DocumentTemplateManager.vue';
import EvidenceGallery from './pages/admin/EvidenceGallery.vue';
import FieldIssuesManager from './pages/admin/FieldIssuesManager.vue';
import Reports from './pages/admin/Reports.vue';
import NotificationLogs from './pages/admin/NotificationLogs.vue';

// Field Team Page
import FieldTasks from './pages/field/FieldTasks.vue';

// Client / Principal Pages (Indomarco, Smartfren, Perbankan)
import ClientDashboard from './pages/client/ClientDashboard.vue';
import ClientTaskList from './pages/client/ClientTaskList.vue';
import ClientBaList from './pages/client/ClientBaList.vue';

// Superuser Page
import SuperDashboard from './pages/superuser/SuperDashboard.vue';

const auth = useAuth();
const activeTab = ref('admin_dashboard');
const sidebarOpen = ref(false);

// Global Modals State
const showCreateModal = ref(false);
const selectedWorkOrderId = ref(null);
const activeBaPreview = ref(null);

// Sync initial active tab when user role changes
watch(() => auth.state.user?.role, (newRole) => {
  if (newRole === 'FIELD_TEAM') {
    activeTab.value = 'field_tasks';
  } else if (newRole === 'VENDOR') {
    activeTab.value = 'client_dashboard';
  } else if (newRole === 'SUPERUSER') {
    activeTab.value = 'super_dashboard';
  } else {
    activeTab.value = 'admin_dashboard';
  }
}, { immediate: true });

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
    case 'field_tasks':
    case 'field_history':
      return FieldTasks;

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
    case 'admin_teams':
    case 'admin_areas':
    case 'admin_jobtypes':
      return MasterData;
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

function handleOpenReviewFromDetail(id) {
  selectedWorkOrderId.value = id;
  activeTab.value = 'admin_review';
}

function handleApprovedSuccess(id) {
  selectedWorkOrderId.value = id;
}

async function handleOpenBa(ba) {
  if (typeof ba === 'number' || typeof ba === 'string') {
    try {
      const res = await api.getBaByWorkOrderId(ba);
      if (res.data) {
        activeBaPreview.value = res.data;
        return;
      }
    } catch (e) {
      console.warn('Could not fetch BA by ID, opening detail modal fallback:', e);
    }
    selectedWorkOrderId.value = ba;
  } else {
    activeBaPreview.value = ba;
  }
}

function handleCreateSuccess() {
  activeTab.value = 'admin_spk';
}

function handleLoginSuccess(user) {
  if (user?.role === 'FIELD_TEAM') {
    activeTab.value = 'field_tasks';
  } else if (user?.role === 'VENDOR') {
    activeTab.value = 'client_dashboard';
  } else if (user?.role === 'SUPERUSER') {
    activeTab.value = 'super_dashboard';
  } else {
    activeTab.value = 'admin_dashboard';
  }
}
</script>
