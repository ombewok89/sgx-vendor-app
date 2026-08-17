<template>
  <div>
    <!-- Mobile backdrop -->
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
        class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 lg:hidden"
        @click="$emit('close')"
      />
    </Transition>

    <aside
      :class="[
        'fixed top-[57px] bottom-0 left-0 z-40 w-64 glass-card border-r border-slate-200/80 transition-transform duration-300 ease-out lg:translate-x-0 overflow-y-auto custom-scrollbar',
        isOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'
      ]"
    >
      <div class="p-3">
        <!-- Active Role Indicator -->
        <div class="mb-3.5 p-3 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-md shadow-slate-900/10 border border-slate-700/50">
          <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between mb-1">
            <span>Portal Workspace</span>
            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/30">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1 animate-pulse"></span>
              ONLINE
            </span>
          </div>
          <div class="text-xs font-bold text-white flex items-center justify-between">
            <span class="truncate">{{ roleTitle }}</span>
            <span v-if="role === 'SUPERUSER'" class="text-[9px] bg-purple-500/30 text-purple-300 px-1.5 py-0.2 rounded font-mono font-bold">
              360° ALL ACCESS
            </span>
          </div>
        </div>

        <nav class="space-y-1">
          <template v-for="(item, idx) in navItems" :key="item.id">
            <!-- Section Header -->
            <div
              v-if="item.section && (idx === 0 || navItems[idx - 1]?.section !== item.section)"
              class="pt-3 pb-1 px-3 text-[10px] font-bold uppercase text-slate-400 tracking-wider flex items-center justify-between"
            >
              <span>{{ item.section }}</span>
            </div>

            <!-- Nav Button -->
            <button
              @click="selectTab(item.id)"
              :class="[
                'w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 active:scale-[0.98] cursor-pointer',
                activeTab === item.id
                  ? 'bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 text-white shadow-md shadow-brand-900/25 font-bold'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
              ]"
            >
              <div class="flex items-center gap-3 truncate">
                <component
                  :is="item.icon"
                  :class="[
                    'w-4 h-4 transition-colors shrink-0',
                    activeTab === item.id ? 'text-brand-200' : 'text-slate-400'
                  ]"
                />
                <span class="truncate">{{ item.label }}</span>
              </div>

              <!-- Optional Badge Counters -->
              <span
                v-if="item.badge"
                :class="[
                  'px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold',
                  item.badgeColor || 'bg-brand-500/20 text-brand-300'
                ]"
              >
                {{ item.badge }}
              </span>
            </button>
          </template>
        </nav>
      </div>
    </aside>
  </div>
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
  Store
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
        { id: 'super_dashboard', label: 'Dashboard Eksekutif', icon: LayoutDashboard, section: 'EKSEKUTIF' },
        
        // WORK MONITORING
        { id: 'admin_spk', label: 'Pekerjaan / SPK', icon: FileText, section: 'MANAJEMEN PEKERJAAN' },
        { id: 'admin_review', label: 'Review & Verifikasi', icon: CheckSquare, section: 'MANAJEMEN PEKERJAAN', badge: reviewCount.value > 0 ? reviewCount.value : null, badgeColor: 'bg-purple-100 text-purple-800 border border-purple-200' },
        
        // EVIDENCE & ISSUES
        { id: 'admin_evidence', label: 'Evidence Gallery', icon: Camera, section: 'DOKUMENTASI & KENDALA' },
        { id: 'admin_issues', label: 'Kendala Lapangan', icon: AlertTriangle, section: 'DOKUMENTASI & KENDALA' },
        
        // REPORTING
        { id: 'admin_ba', label: 'BA Opname Resmi', icon: FileCheck2, section: 'BERITA ACARA & LAPORAN' },
        { id: 'admin_reports', label: 'Laporan & Analitik', icon: BarChart3, section: 'BERITA ACARA & LAPORAN' },
        
        // MASTER DATA
        { id: 'admin_vendors', label: 'Master Client / Klien', icon: Building2, section: 'MASTER DATA PUSAT' },
        { id: 'admin_teams', label: 'Tim Lapangan', icon: Users, section: 'MASTER DATA PUSAT' },
        { id: 'admin_areas', label: 'Master Area', icon: MapPin, section: 'MASTER DATA PUSAT' },
        { id: 'admin_jobtypes', label: 'Jenis Pekerjaan', icon: Briefcase, section: 'MASTER DATA PUSAT' },
        
        // SYSTEM & SECURITY
        { id: 'super_permissions', label: 'Matriks Hak Akses RBAC', icon: ShieldAlert, section: 'KONTROL SISTEM (SUPERVISOR)' },
        { id: 'super_users', label: 'Manajemen Pengguna', icon: Users, section: 'KONTROL SISTEM (SUPERVISOR)' },
        { id: 'super_settings', label: 'Pengaturan Gateway & Sistem', icon: Settings, section: 'KONTROL SISTEM (SUPERVISOR)' },
        { id: 'super_audit', label: 'Audit Trail Explorer', icon: History, section: 'KONTROL SISTEM (SUPERVISOR)' },
        { id: 'admin_templates', label: 'Template Dokumen BA', icon: FileCode, section: 'KONTROL SISTEM (SUPERVISOR)' },
        { id: 'admin_notifications', label: 'WhatsApp Logs', icon: Bell, section: 'KONTROL SISTEM (SUPERVISOR)' },
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
