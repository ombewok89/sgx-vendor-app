<template>
  <div>
    <!-- Floating Bottom Dock Navigation Bar (Mobile / Tablet Only: lg:hidden) -->
    <nav class="fixed bottom-3 inset-x-3 sm:inset-x-8 z-40 lg:hidden pointer-events-none">
      <div class="glass-card bg-slate-900/90 backdrop-blur-xl border border-amber-500/25 rounded-2xl p-1.5 shadow-2xl shadow-purple-950/40 text-slate-200 pointer-events-auto flex items-center justify-around relative">
        
        <!-- 1. Dashboard Tab -->
        <button
          type="button"
          @click="selectTab(isSuperuser ? 'super_dashboard' : 'admin_dashboard')"
          :class="[
            'flex-1 py-1.5 px-1 flex flex-col items-center justify-center gap-1 rounded-xl transition-all cursor-pointer select-none active:scale-95',
            isDashboardActive
              ? 'text-[#EDC80A] font-bold'
              : 'text-slate-400 hover:text-slate-200'
          ]"
        >
          <div class="relative">
            <LayoutDashboard class="w-5 h-5" />
            <span v-if="isDashboardActive" class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-[#EDC80A]"></span>
          </div>
          <span class="text-[10px] tracking-tight">Dashboard</span>
        </button>

        <!-- 2. SPK Toko Tab -->
        <button
          type="button"
          @click="selectTab('admin_spk')"
          :class="[
            'flex-1 py-1.5 px-1 flex flex-col items-center justify-center gap-1 rounded-xl transition-all cursor-pointer select-none active:scale-95',
            activeTab === 'admin_spk'
              ? 'text-[#EDC80A] font-bold'
              : 'text-slate-400 hover:text-slate-200'
          ]"
        >
          <div class="relative">
            <FileText class="w-5 h-5" />
            <span v-if="activeTab === 'admin_spk'" class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-[#EDC80A]"></span>
          </div>
          <span class="text-[10px] tracking-tight">Daftar SPK</span>
        </button>

        <!-- 3. Center Elevated Action Hub (FAB) -->
        <div class="flex-1 flex items-center justify-center relative -top-3">
          <button
            type="button"
            @click="showQuickActionSheet = true"
            class="w-12 h-12 rounded-full bg-gradient-to-tr from-amber-500 via-amber-400 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 flex items-center justify-center shadow-lg shadow-amber-500/30 border-2 border-slate-900 active:scale-90 transition-all cursor-pointer"
            title="Menu Aksi Cepat"
          >
            <Plus class="w-6 h-6 stroke-[2.5]" />
          </button>
        </div>

        <!-- 4. Review Console Tab -->
        <button
          type="button"
          @click="selectTab('admin_review')"
          :class="[
            'flex-1 py-1.5 px-1 flex flex-col items-center justify-center gap-1 rounded-xl transition-all cursor-pointer select-none active:scale-95',
            activeTab === 'admin_review'
              ? 'text-[#EDC80A] font-bold'
              : 'text-slate-400 hover:text-slate-200'
          ]"
        >
          <div class="relative">
            <CheckSquare class="w-5 h-5" />
            <span
              v-if="reviewCount > 0"
              class="absolute -top-1 -right-2 min-w-4 h-4 px-1 rounded-full bg-rose-600 text-white font-mono text-[9px] font-black flex items-center justify-center border border-slate-900 animate-pulse"
            >
              {{ reviewCount }}
            </span>
            <span v-if="activeTab === 'admin_review'" class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-[#EDC80A]"></span>
          </div>
          <span class="text-[10px] tracking-tight">Review</span>
        </button>

        <!-- 5. More Menu Drawer Tab -->
        <button
          type="button"
          @click="showMoreMenuSheet = true"
          :class="[
            'flex-1 py-1.5 px-1 flex flex-col items-center justify-center gap-1 rounded-xl transition-all cursor-pointer select-none active:scale-95',
            isOtherActive
              ? 'text-[#EDC80A] font-bold'
              : 'text-slate-400 hover:text-slate-200'
          ]"
        >
          <div class="relative">
            <Grid class="w-5 h-5" />
            <span v-if="isOtherActive" class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-[#EDC80A]"></span>
          </div>
          <span class="text-[10px] tracking-tight">Lainnya</span>
        </button>

      </div>
    </nav>

    <!-- BACKDROP FOR BOTTOM SHEETS -->
    <div
      v-if="showQuickActionSheet || showMoreMenuSheet"
      @click="closeAllSheets"
      class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 lg:hidden transition-opacity"
    ></div>

    <!-- 1. QUICK ACTION BOTTOM SHEET -->
    <div
      v-if="showQuickActionSheet"
      class="fixed inset-x-0 bottom-0 z-50 lg:hidden bg-slate-900 text-white rounded-t-3xl border-t border-amber-500/30 p-5 shadow-2xl space-y-4 animate-slide-up max-h-[85vh] overflow-y-auto"
    >
      <div class="flex items-center justify-between border-b border-slate-800 pb-3">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-[#EDC80A] flex items-center justify-center border border-amber-500/30">
            <Sparkles class="w-4 h-4" />
          </div>
          <div>
            <h3 class="font-black text-sm text-white">Menu Aksi Cepat Superuser</h3>
            <p class="text-[10px] text-slate-400">Pintasan operasional dan administrasi cabang</p>
          </div>
        </div>
        <button
          type="button"
          @click="showQuickActionSheet = false"
          class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Quick Action Grid -->
      <div class="grid grid-cols-2 gap-2.5">
        <!-- + Terbitkan SPK Baru -->
        <button
          type="button"
          @click="handleAction('create_spk')"
          class="p-3 bg-gradient-to-br from-amber-500 to-amber-600 text-slate-950 rounded-2xl flex flex-col items-start gap-1.5 shadow-lg shadow-amber-500/20 active:scale-95 transition-all text-left cursor-pointer"
        >
          <div class="w-8 h-8 rounded-xl bg-slate-950/15 flex items-center justify-center">
            <Plus class="w-5 h-5 stroke-[2.5]" />
          </div>
          <div>
            <div class="font-black text-xs">Terbitkan SPK</div>
            <div class="text-[9px] text-slate-900 font-medium">Buat SPK Cabang Baru</div>
          </div>
        </button>

        <!-- BA Opname Resmi -->
        <button
          type="button"
          @click="handleNavigate('admin_ba')"
          class="p-3 bg-slate-800/90 hover:bg-slate-800 border border-slate-700/80 rounded-2xl flex flex-col items-start gap-1.5 active:scale-95 transition-all text-left cursor-pointer"
        >
          <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
            <FileCheck2 class="w-4 h-4" />
          </div>
          <div>
            <div class="font-bold text-xs text-white">BA Opname</div>
            <div class="text-[9px] text-slate-400">Dokumen Berita Acara</div>
          </div>
        </button>

        <!-- Evidence Gallery -->
        <button
          type="button"
          @click="handleNavigate('admin_evidence')"
          class="p-3 bg-slate-800/90 hover:bg-slate-800 border border-slate-700/80 rounded-2xl flex flex-col items-start gap-1.5 active:scale-95 transition-all text-left cursor-pointer"
        >
          <div class="w-8 h-8 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center border border-purple-500/30">
            <Camera class="w-4 h-4" />
          </div>
          <div>
            <div class="font-bold text-xs text-white">Evidence Gallery</div>
            <div class="text-[9px] text-slate-400">Semua Foto Lapangan</div>
          </div>
        </button>

        <!-- Kendala Lapangan -->
        <button
          type="button"
          @click="handleNavigate('admin_issues')"
          class="p-3 bg-slate-800/90 hover:bg-slate-800 border border-slate-700/80 rounded-2xl flex flex-col items-start gap-1.5 active:scale-95 transition-all text-left cursor-pointer"
        >
          <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center border border-rose-500/30">
            <AlertTriangle class="w-4 h-4" />
          </div>
          <div>
            <div class="font-bold text-xs text-white">Kendala Toko</div>
            <div class="text-[9px] text-slate-400">Catatan Masalah Teknis</div>
          </div>
        </button>
      </div>

      <!-- Bottom Shortcut Links -->
      <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-xs">
        <button
          type="button"
          @click="handleNavigate(isSuperuser ? 'super_settings' : 'admin_templates')"
          class="text-[#EDC80A] hover:underline font-bold flex items-center gap-1 cursor-pointer text-[11px]"
        >
          <Settings class="w-3.5 h-3.5" />
          <span>Pengaturan Sistem & Gateway WA</span>
        </button>
        <button
          type="button"
          @click="emit('open-profile')"
          class="text-slate-400 hover:text-white font-medium flex items-center gap-1 cursor-pointer text-[11px]"
        >
          <UserCheck class="w-3.5 h-3.5" />
          <span>Profil Akun</span>
        </button>
      </div>
    </div>

    <!-- 2. MORE MENU DRAWER (SELURUH MENU LENGKAP SUPERUSER) -->
    <div
      v-if="showMoreMenuSheet"
      class="fixed inset-x-0 bottom-0 z-50 lg:hidden bg-slate-900 text-white rounded-t-3xl border-t border-amber-500/30 p-5 shadow-2xl space-y-4 animate-slide-up max-h-[85vh] overflow-y-auto"
    >
      <div class="flex items-center justify-between border-b border-slate-800 pb-3">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center border border-purple-500/30">
            <Grid class="w-4 h-4" />
          </div>
          <div>
            <h3 class="font-black text-sm text-white">Menu Lengkap Portal</h3>
            <p class="text-[10px] text-slate-400">Akses seluruh modul administrasi & master data</p>
          </div>
        </div>
        <button
          type="button"
          @click="showMoreMenuSheet = false"
          class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Menu Categorized Grid -->
      <div class="space-y-4 text-xs">
        <!-- Section: Master Data -->
        <div class="space-y-1.5">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Master Data & Organisasi</span>
          <div class="grid grid-cols-2 gap-2">
            <button
              type="button"
              @click="handleNavigate('admin_vendors')"
              class="p-2.5 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 flex items-center gap-2 text-left active:scale-95 transition-all cursor-pointer"
            >
              <Building2 class="w-4 h-4 text-amber-400 shrink-0" />
              <span class="text-[11px] font-bold text-white truncate">Master Client</span>
            </button>
            <button
              type="button"
              @click="handleNavigate('admin_teams')"
              class="p-2.5 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 flex items-center gap-2 text-left active:scale-95 transition-all cursor-pointer"
            >
              <Users class="w-4 h-4 text-emerald-400 shrink-0" />
              <span class="text-[11px] font-bold text-white truncate">Tim Lapangan</span>
            </button>
            <button
              type="button"
              @click="handleNavigate('admin_areas')"
              class="p-2.5 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 flex items-center gap-2 text-left active:scale-95 transition-all cursor-pointer"
            >
              <MapPin class="w-4 h-4 text-blue-400 shrink-0" />
              <span class="text-[11px] font-bold text-white truncate">Master Area</span>
            </button>
            <button
              type="button"
              @click="handleNavigate('admin_jobtypes')"
              class="p-2.5 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 flex items-center gap-2 text-left active:scale-95 transition-all cursor-pointer"
            >
              <Briefcase class="w-4 h-4 text-purple-400 shrink-0" />
              <span class="text-[11px] font-bold text-white truncate">Jenis Pekerjaan</span>
            </button>
          </div>
        </div>

        <!-- Section: Laporan & Analitik -->
        <div class="space-y-1.5">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Laporan & Evidensi</span>
          <div class="grid grid-cols-2 gap-2">
            <button
              type="button"
              @click="handleNavigate('admin_ba')"
              class="p-2.5 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 flex items-center gap-2 text-left active:scale-95 transition-all cursor-pointer"
            >
              <FileCheck2 class="w-4 h-4 text-emerald-400 shrink-0" />
              <span class="text-[11px] font-bold text-white truncate">BA Opname Resmi</span>
            </button>
            <button
              type="button"
              @click="handleNavigate('admin_reports')"
              class="p-2.5 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 flex items-center gap-2 text-left active:scale-95 transition-all cursor-pointer"
            >
              <BarChart3 class="w-4 h-4 text-cyan-400 shrink-0" />
              <span class="text-[11px] font-bold text-white truncate">Laporan & Statistik</span>
            </button>
            <button
              type="button"
              @click="handleNavigate('admin_evidence')"
              class="p-2.5 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 flex items-center gap-2 text-left active:scale-95 transition-all cursor-pointer"
            >
              <Camera class="w-4 h-4 text-purple-400 shrink-0" />
              <span class="text-[11px] font-bold text-white truncate">Evidence Gallery</span>
            </button>
            <button
              type="button"
              @click="handleNavigate('admin_issues')"
              class="p-2.5 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 flex items-center gap-2 text-left active:scale-95 transition-all cursor-pointer"
            >
              <AlertTriangle class="w-4 h-4 text-rose-400 shrink-0" />
              <span class="text-[11px] font-bold text-white truncate">Kendala Lapangan</span>
            </button>
          </div>
        </div>

        <!-- Section: Kontrol Superuser & Sistem -->
        <div v-if="isSuperuser" class="space-y-1.5">
          <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider block">Kontrol Superuser & Keamanan</span>
          <div class="grid grid-cols-2 gap-2">
            <button
              type="button"
              @click="handleNavigate('super_permissions')"
              class="p-2.5 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 flex items-center gap-2 text-left active:scale-95 transition-all cursor-pointer"
            >
              <ShieldAlert class="w-4 h-4 text-rose-400 shrink-0" />
              <span class="text-[11px] font-bold text-white truncate">Matriks RBAC</span>
            </button>
            <button
              type="button"
              @click="handleNavigate('super_users')"
              class="p-2.5 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 flex items-center gap-2 text-left active:scale-95 transition-all cursor-pointer"
            >
              <Users class="w-4 h-4 text-emerald-400 shrink-0" />
              <span class="text-[11px] font-bold text-white truncate">Kelola User</span>
            </button>
            <button
              type="button"
              @click="handleNavigate('super_settings')"
              class="p-2.5 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 flex items-center gap-2 text-left active:scale-95 transition-all cursor-pointer"
            >
              <Settings class="w-4 h-4 text-amber-400 shrink-0" />
              <span class="text-[11px] font-bold text-white truncate">Setting Sistem</span>
            </button>
            <button
              type="button"
              @click="handleNavigate('super_audit')"
              class="p-2.5 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 flex items-center gap-2 text-left active:scale-95 transition-all cursor-pointer"
            >
              <History class="w-4 h-4 text-blue-400 shrink-0" />
              <span class="text-[11px] font-bold text-white truncate">Audit Trail</span>
            </button>
            <button
              type="button"
              @click="handleNavigate('admin_templates')"
              class="p-2.5 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 flex items-center gap-2 text-left active:scale-95 transition-all cursor-pointer"
            >
              <FileCode class="w-4 h-4 text-teal-400 shrink-0" />
              <span class="text-[11px] font-bold text-white truncate">Template Dokumen</span>
            </button>
            <button
              type="button"
              @click="handleNavigate('admin_notifications')"
              class="p-2.5 bg-slate-800/80 hover:bg-slate-800 rounded-xl border border-slate-700/60 flex items-center gap-2 text-left active:scale-95 transition-all cursor-pointer"
            >
              <Bell class="w-4 h-4 text-amber-400 shrink-0" />
              <span class="text-[11px] font-bold text-white truncate">WhatsApp Logs</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuth } from '../composables/useAuth';
import {
  LayoutDashboard,
  FileText,
  CheckSquare,
  Plus,
  Grid,
  Sparkles,
  X,
  FileCheck2,
  Camera,
  AlertTriangle,
  Settings,
  UserCheck,
  Building2,
  Users,
  MapPin,
  Briefcase,
  ShieldAlert,
  History,
  FileCode,
  Bell
} from 'lucide-vue-next';

const props = defineProps({
  activeTab: {
    type: String,
    required: true
  },
  reviewCount: {
    type: Number,
    default: 0
  }
});

const emit = defineEmits(['update:activeTab', 'open-create-spk', 'open-profile']);

const auth = useAuth();
const isSuperuser = computed(() => auth.state.user?.role === 'SUPERUSER');

const showQuickActionSheet = ref(false);
const showMoreMenuSheet = ref(false);

const isDashboardActive = computed(() => {
  return props.activeTab === 'super_dashboard' || props.activeTab === 'admin_dashboard';
});

const isOtherActive = computed(() => {
  return ![
    'super_dashboard',
    'admin_dashboard',
    'admin_spk',
    'admin_review'
  ].includes(props.activeTab);
});

function selectTab(tabId) {
  emit('update:activeTab', tabId);
  closeAllSheets();
}

function handleNavigate(tabId) {
  emit('update:activeTab', tabId);
  closeAllSheets();
}

function handleAction(actionName) {
  if (actionName === 'create_spk') {
    emit('open-create-spk');
  }
  closeAllSheets();
}

function closeAllSheets() {
  showQuickActionSheet.value = false;
  showMoreMenuSheet.value = false;
}
</script>

<style scoped>
@keyframes slideUp {
  from {
    transform: translateY(100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.animate-slide-up {
  animation: slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
