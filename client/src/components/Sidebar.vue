<template>
  <!-- Mobile Drawer & Backdrop Teleported to Body (< lg) -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="isOpen"
          class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 lg:hidden"
          @click="$emit('close')"
        />
      </Transition>

      <aside
        class="lg:hidden fixed inset-y-0 left-0 z-50 w-72 bg-[#13171F] border-r border-[#232A36] shadow-2xl transition-transform duration-300 ease-out flex flex-col p-3 overflow-y-auto custom-scrollbar"
        :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
      >
        <!-- Mobile Header with Close Button -->
        <div class="flex items-center justify-between pb-3 mb-2 border-b border-[#232A36]">
          <div class="flex items-center gap-2">
            <img src="/sgx_logo.png" alt="SGX Logo" class="w-6 h-6 object-contain rounded-xs border border-[#232A36]" />
            <span class="font-bold text-white text-xs tracking-wider font-mono uppercase">SGX PORTAL</span>
          </div>
          <button
            type="button"
            @click="$emit('close')"
            class="p-1.5 rounded-xs text-slate-400 hover:text-[#FACC15] hover:bg-[#1C2028] active:scale-95 transition-colors cursor-pointer border border-[#232A36]"
            aria-label="Tutup Menu"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Mobile Navigation Menu -->
        <div class="flex-1">
          <!-- Active Role Indicator -->
          <div class="mb-3 p-2.5 rounded-xs bg-[#0E1117] text-slate-100 border border-[#232A36]">
            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider font-mono flex items-center justify-between mb-1">
              <span>PORTAL WORKSPACE</span>
              <span class="inline-flex items-center px-1.5 py-0.2 rounded-xs text-[9px] bg-[#13171F] text-[#FACC15] font-mono font-bold border border-[#FACC15]/40">
                <span class="w-1.5 h-1.5 rounded-full bg-[#FACC15] mr-1 animate-pulse"></span>
                ACTIVE
              </span>
            </div>
            <div class="text-xs font-bold text-slate-100 font-mono flex items-center justify-between">
              <span class="truncate uppercase">{{ roleTitle }}</span>
              <span v-if="role === 'SUPERUSER'" class="text-[9px] bg-[#FACC15] text-[#0B0C0E] px-1.5 py-0.2 rounded-xs font-mono font-black uppercase">
                SUPERUSER
              </span>
            </div>
          </div>

          <nav class="space-y-1">
            <template v-for="(item, idx) in navItems" :key="'m-' + item.id">
              <div
                v-if="item.section && (idx === 0 || navItems[idx - 1]?.section !== item.section)"
                class="pt-3 pb-1 px-2.5 text-[9px] font-mono font-bold uppercase text-slate-400 tracking-wider flex items-center justify-between"
              >
                <span>// {{ item.section }}</span>
              </div>

              <button
                @click="selectTab(item.id)"
                :class="[
                  'w-full flex items-center justify-between px-2.5 py-2 rounded-xs text-xs transition-colors cursor-pointer group font-mono',
                  activeTab === item.id
                    ? 'bg-[#1C2028] border-l-[3px] border-[#FACC15] text-[#FACC15] font-bold'
                    : 'text-slate-400 hover:text-white hover:bg-[#1A202C]/60 border-l-[3px] border-transparent font-medium'
                ]"
              >
                <div class="flex items-center gap-2.5 truncate">
                  <component
                    :is="item.icon"
                    :class="[
                      'w-4 h-4 transition-colors shrink-0',
                      activeTab === item.id ? 'text-[#FACC15]' : 'text-slate-400 group-hover:text-slate-200'
                    ]"
                  />
                  <span class="truncate tracking-tight">{{ item.label }}</span>
                </div>

                <span
                  v-if="item.badge"
                  class="px-1.5 py-0.2 rounded-xs text-[9px] font-mono font-bold bg-[#FACC15] text-[#0B0C0E] border border-[#0B0C0E]"
                >
                  {{ item.badge }}
                </span>
              </button>
            </template>
          </nav>
        </div>
      </aside>
    </Teleport>

    <!-- Desktop In-Flow Collapsible Sidebar (Never Overlaps, Natural Distance to Content) -->
    <aside
      class="hidden lg:block shrink-0 transition-all duration-300 ease-in-out self-start sticky top-[58px] z-20 py-4 pl-4"
      :class="isOpen ? 'w-72 mr-3 xl:mr-6 opacity-100' : 'w-0 mr-0 opacity-0 pointer-events-none pl-0 overflow-hidden'"
    >
      <div class="w-64 bg-[#13171F] rounded-xs border border-[#232A36] max-h-[calc(100vh-80px)] overflow-y-auto custom-scrollbar p-2.5">
        <!-- Active Role Indicator -->
        <div class="mb-3 p-2.5 rounded-xs bg-[#0E1117] text-slate-100 border border-[#232A36]">
          <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider font-mono flex items-center justify-between mb-1">
            <span>PORTAL WORKSPACE</span>
            <span class="inline-flex items-center px-1.5 py-0.2 rounded-xs text-[9px] bg-[#13171F] text-[#FACC15] font-mono font-bold border border-[#FACC15]/40">
              <span class="w-1.5 h-1.5 rounded-full bg-[#FACC15] mr-1 animate-pulse"></span>
              ACTIVE
            </span>
          </div>
          <div class="text-xs font-bold text-slate-100 font-mono flex items-center justify-between">
            <span class="truncate uppercase">{{ roleTitle }}</span>
            <span v-if="role === 'SUPERUSER'" class="text-[9px] bg-[#FACC15] text-[#0B0C0E] px-1.5 py-0.2 rounded-xs font-mono font-black uppercase">
              SUPERUSER
            </span>
          </div>
        </div>

        <nav class="space-y-1">
          <template v-for="(item, idx) in navItems" :key="'d-' + item.id">
            <!-- Section Header -->
            <div
              v-if="item.section && (idx === 0 || navItems[idx - 1]?.section !== item.section)"
              class="pt-3 pb-1 px-2.5 text-[9px] font-mono font-bold uppercase text-slate-400 tracking-wider flex items-center justify-between"
            >
              <span>// {{ item.section }}</span>
            </div>

            <!-- Nav Button with Solid 3px Yellow Left Border as in .cursorrules -->
            <button
              @click="selectTab(item.id)"
              :class="[
                'w-full flex items-center justify-between px-2.5 py-2 rounded-xs text-xs transition-colors cursor-pointer group font-mono',
                activeTab === item.id
                  ? 'bg-[#1C2028] border-l-[3px] border-[#FACC15] text-[#FACC15] font-bold'
                  : 'text-slate-400 hover:text-white hover:bg-[#1A202C]/60 border-l-[3px] border-transparent font-medium'
              ]"
            >
              <div class="flex items-center gap-2.5 truncate">
                <component
                  :is="item.icon"
                  :class="[
                    'w-4 h-4 transition-colors shrink-0',
                    activeTab === item.id ? 'text-[#FACC15]' : 'text-slate-400 group-hover:text-slate-200'
                  ]"
                />
                <span class="truncate tracking-tight">{{ item.label }}</span>
              </div>

              <!-- Badge Counters in Solid Yellow Pills with Black Numbers -->
              <span
                v-if="item.badge"
                class="px-1.5 py-0.2 rounded-xs text-[9px] font-mono font-bold bg-[#FACC15] text-[#0B0C0E] border border-[#0B0C0E]"
              >
                {{ item.badge }}
              </span>
            </button>
          </template>
        </nav>
      </div>
    </aside>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '../composables/useAuth';
import { api } from '../services/api';
import {
  LayoutDashboard,
  FileText,
  UserCheck,
  CheckSquare,
  Camera,
  AlertTriangle,
  FileCheck2,
  BarChart3,
  Building2,
  Users,
  MapPin,
  Briefcase,
  FileCode,
  Bell,
  History,
  Settings,
  ShieldCheck,
  ShieldAlert,
  Smartphone,
  Store,
  X
} from 'lucide-vue-next';

const props = defineProps({
  activeTab: {
    type: String,
    required: true
  },
  isOpen: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:activeTab', 'close']);

const auth = useAuth();
const role = computed(() => auth.state.user?.role || 'ADMIN');
const reviewCount = ref(0);

onMounted(async () => {
  try {
    const res = await api.getWorkOrders();
    if (res.data) {
      reviewCount.value = res.data.filter(w => ['SUBMITTED', 'UNDER_REVIEW', 'REVISION'].includes(w.status)).length;
    }
  } catch (e) {
    // Ignore on mount
  }
});

const roleTitle = computed(() => {
  switch (role.value) {
    case 'SUPERUSER': return 'Supervisor / Superuser';
    case 'FIELD_TEAM': return 'Tim Lapangan (Mobile)';
    case 'VENDOR': return 'Klien / Pemberi Tugas (Principal)';
    case 'ADMIN':
    default: return 'Admin Operasional';
  }
});

const navItems = computed(() => {
  let items = [];
  switch (role.value) {
    case 'SUPERUSER':
      // Omnipresent Unified 360° Access for Supervisor/Superuser
      items = [
        // EKSEKUTIF
        { id: 'super_dashboard', label: 'Executive Dashboard', icon: LayoutDashboard, section: 'EKSEKUTIF' },
        
        // MANAJEMEN PEKERJAAN
        { id: 'admin_spk', label: 'SPK / Work Orders', icon: FileText, section: 'MANAJEMEN PEKERJAAN' },
        { id: 'admin_review', label: 'Review & Verification', icon: CheckSquare, section: 'MANAJEMEN PEKERJAAN', badge: reviewCount.value > 0 ? reviewCount.value : null, badgeColor: 'bg-amber-500/15 text-amber-300 border border-amber-500/30 font-mono' },
        
        // DOKUMENTASI & KENDALA
        { id: 'admin_evidence', label: 'Evidence Gallery (GPS & SHA-256)', icon: Camera, section: 'DOKUMENTASI & KENDALA' },
        { id: 'admin_issues', label: 'Field Issues Tracker', icon: AlertTriangle, section: 'DOKUMENTASI & KENDALA' },
        
        // BERITA ACARA & LAPORAN
        { id: 'admin_ba', label: 'Official BA Opname', icon: FileCheck2, section: 'BERITA ACARA & LAPORAN' },
        { id: 'admin_reports', label: 'Analytics & Exports', icon: BarChart3, section: 'BERITA ACARA & LAPORAN' },
        
        // MASTER DATA PUSAT
        { id: 'admin_vendors', label: 'Clients (Master)', icon: Building2, section: 'MASTER DATA PUSAT' },
        { id: 'admin_teams', label: 'Field Technicians', icon: Users, section: 'MASTER DATA PUSAT' },
        { id: 'admin_areas', label: 'Regions & Areas', icon: MapPin, section: 'MASTER DATA PUSAT' },
        { id: 'admin_jobtypes', label: 'Job Types', icon: Briefcase, section: 'MASTER DATA PUSAT' },
        
        // KONTROL SISTEM
        { id: 'super_permissions', label: 'RBAC Matrix', icon: ShieldAlert, section: 'KONTROL SISTEM' },
        { id: 'super_audit', label: 'Audit Logs', icon: History, section: 'KONTROL SISTEM' },
        { id: 'super_users', label: 'Manajemen Pengguna', icon: Users, section: 'KONTROL SISTEM' },
        { id: 'super_settings', label: 'Gateway & Sistem', icon: Settings, section: 'KONTROL SISTEM' },
        { id: 'admin_templates', label: 'Template BA Opname', icon: FileCode, section: 'KONTROL SISTEM' },
      ];
      break;

    case 'FIELD_TEAM':
      items = [
        { id: 'field_dashboard', label: 'Dashboard Lapangan', icon: Smartphone },
        { id: 'field_tasks', label: 'Pekerjaan Saya', icon: CheckSquare },
        { id: 'field_history', label: 'Riwayat Tugas', icon: History },
      ];
      break;

    case 'VENDOR':
      items = [
        { id: 'client_dashboard', label: 'Dashboard Client', icon: LayoutDashboard },
        { id: 'client_tasks', label: 'Progres & Evidensi Toko', icon: Store },
        { id: 'client_issues', label: 'Kendala Lapangan', icon: AlertTriangle },
        { id: 'client_ba', label: 'Dokumen BA Opname', icon: FileCheck2 },
      ];
      break;

    case 'ADMIN':
    default:
      items = [
        { id: 'admin_dashboard', label: 'Dashboard', icon: LayoutDashboard, section: 'UTAMA' },
        { id: 'admin_spk', label: 'Pekerjaan / SPK', icon: FileText, section: 'WORK MANAGEMENT' },
        { id: 'admin_review', label: 'Review & Revisi', icon: CheckSquare, section: 'WORK MANAGEMENT' },
        { id: 'admin_evidence', label: 'Evidence Gallery', icon: Camera, section: 'DOCUMENTATION' },
        { id: 'admin_issues', label: 'Kendala Teknis', icon: AlertTriangle, section: 'DOCUMENTATION' },
        { id: 'admin_ba', label: 'BA Opname', icon: FileCheck2, section: 'REPORTING' },
        { id: 'admin_reports', label: 'Laporan & Statistik', icon: BarChart3, section: 'REPORTING' },
        { id: 'admin_vendors', label: 'Master Client / Klien', icon: Building2, section: 'MASTER DATA' },
        { id: 'admin_teams', label: 'Tim Lapangan', icon: Users, section: 'MASTER DATA' },
        { id: 'admin_areas', label: 'Master Area', icon: MapPin, section: 'MASTER DATA' },
        { id: 'admin_jobtypes', label: 'Jenis Pekerjaan', icon: Briefcase, section: 'MASTER DATA' },
        { id: 'admin_templates', label: 'Template Dokumen', icon: FileCode, section: 'SYSTEM' },
        { id: 'admin_notifications', label: 'WhatsApp Logs', icon: Bell, section: 'SYSTEM' },
        { id: 'admin_audit', label: 'Audit Trail', icon: History, section: 'SYSTEM' },
      ];
      break;
  }

  // Superuser, Vendor, and Field Team see their dedicated role items directly
  if (role.value === 'SUPERUSER' || role.value === 'VENDOR' || role.value === 'FIELD_TEAM') {
    return items;
  }

  return items.filter(item => auth.hasPermission(item.id, 'view'));
});

function selectTab(id) {
  emit('update:activeTab', id);
  if (window.innerWidth < 1024) {
    emit('close');
  }
}
</script>
