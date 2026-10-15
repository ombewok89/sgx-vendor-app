<template>
  <div class="space-y-4 font-mono">
    <!-- Header Controls (CAD Blueprint Style) -->
    <div class="bg-[#13171F] border border-[#232A36] p-4 rounded-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <ShieldAlert class="w-5 h-5 text-hazard" />
          <h3 class="font-heading font-black text-sm text-white uppercase tracking-wider">
            Konfigurasi Hak Akses Role & Menu (RBAC Matrix)
          </h3>
        </div>
        <p class="text-[11px] text-slate-400 mt-1">
          Atur izin membuka menu serta otorisasi <strong class="text-white">Create</strong>, <strong class="text-white">Read</strong>, <strong class="text-white">Update</strong>, dan <strong class="text-white">Delete</strong> untuk setiap role pengguna.
        </p>
      </div>

      <!-- Role Selector Pills -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
        <button
          v-for="r in roles"
          :key="r.code"
          @click="selectRole(r.code)"
          :class="[
            'px-3.5 py-1.5 rounded-none text-xs font-mono font-bold transition-all cursor-pointer whitespace-nowrap border uppercase',
            selectedRole === r.code
              ? 'bg-hazard text-carbon border-hazard shadow-xs'
              : 'bg-[#0B0C0E] text-slate-400 hover:text-white border-[#232A36]'
          ]"
        >
          {{ r.name || r.code }}
        </button>
      </div>
    </div>

    <!-- Quick Batch Action Buttons & Save Button (Industrial Action Strip) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#13171F] border border-[#232A36] p-3 rounded-xs text-xs">
      <div class="flex items-center gap-2 flex-wrap">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Terapkan Cepat:</span>
        <button
          type="button"
          @click="setBatchPermissions('all')"
          class="px-2.5 py-1 bg-[#0B0C0E] hover:bg-[#181D26] text-white border border-[#232A36] hover:border-slate-500 text-[10px] font-bold rounded-none cursor-pointer flex items-center gap-1.5 uppercase"
        >
          <CheckSquare class="w-3.5 h-3.5 text-hazard" />
          <span>Beri Semua Hak</span>
        </button>
        <button
          type="button"
          @click="setBatchPermissions('read_only')"
          class="px-2.5 py-1 bg-[#0B0C0E] hover:bg-[#181D26] text-white border border-[#232A36] hover:border-slate-500 text-[10px] font-bold rounded-none cursor-pointer flex items-center gap-1.5 uppercase"
        >
          <Eye class="w-3.5 h-3.5 text-blue-400" />
          <span>Hanya Baca (Read-Only)</span>
        </button>
        <button
          type="button"
          @click="setBatchPermissions('clear')"
          class="px-2.5 py-1 bg-[#0B0C0E] hover:bg-red-950/80 text-red-400 border border-[#232A36] hover:border-red-500/50 text-[10px] font-bold rounded-none cursor-pointer flex items-center gap-1.5 uppercase"
        >
          <X class="w-3.5 h-3.5" />
          <span>Kosongkan Semua</span>
        </button>
      </div>

      <div class="flex items-center gap-2 self-end sm:self-auto">
        <button
          type="button"
          @click="loadMatrix(selectedRole)"
          :disabled="loading || saving"
          class="px-3 py-1.5 bg-[#0B0C0E] hover:bg-[#181D26] text-slate-300 border border-[#232A36] hover:border-slate-500 text-xs font-bold rounded-none cursor-pointer flex items-center gap-1.5 uppercase"
        >
          <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin text-hazard' : '']" />
          <span>Muat Ulang</span>
        </button>

        <button
          type="button"
          @click="savePermissions"
          :disabled="saving"
          class="cad-btn-primary px-4 py-1.5 text-xs font-bold rounded-none flex items-center gap-2 cursor-pointer uppercase active:scale-95 disabled:opacity-50"
        >
          <Save class="w-4 h-4" />
          <span>{{ saving ? 'MENYIMPAN...' : 'SIMPAN PERUBAHAN' }}</span>
        </button>
      </div>
    </div>

    <!-- Success Toast Alert -->
    <div
      v-if="toastMessage"
      class="p-3 bg-emerald-950/90 border-l-4 border-l-emerald-400 border-y border-r border-emerald-800/60 text-white text-xs font-mono font-bold flex items-center gap-2.5 rounded-none shadow-md"
    >
      <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
      <span>{{ toastMessage }}</span>
    </div>

    <!-- Matrix Table (CAD High-Density Module) -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs overflow-hidden">
      <div v-if="loading" class="py-20 text-center text-slate-500 font-medium flex flex-col items-center gap-2">
        <RefreshCw class="w-6 h-6 animate-spin text-hazard" />
        <span class="text-xs">MEMUAT_KONFIGURASI_HAK_AKSES...</span>
      </div>

      <table v-else class="w-full text-left text-xs border-collapse font-mono">
        <thead class="bg-[#0E1117] text-slate-400 font-bold border-b border-[#232A36] uppercase text-[10px] tracking-wider">
          <tr>
            <th class="py-3 px-4 w-72">Modul / Menu Aplikasi</th>
            <th class="py-3 px-3 text-center w-28">Lihat (Read)</th>
            <th class="py-3 px-3 text-center w-28">Tambah (Create)</th>
            <th class="py-3 px-3 text-center w-28">Ubah (Update)</th>
            <th class="py-3 px-3 text-center w-28">Hapus (Delete)</th>
            <th class="py-3 px-4 text-center w-24">Aksi Baris</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#1C2028] text-slate-300 font-medium">
          <template v-for="(item, idx) in matrix" :key="item.id || item.module_id">
            <!-- Section Divider -->
            <tr
              v-if="idx === 0 || matrix[idx - 1].section !== item.section"
              class="bg-[#0B0C0E] font-heading font-black text-[10px] text-hazard uppercase tracking-wider border-y border-[#232A36]"
            >
              <td colspan="6" class="py-2 px-4">
                // SECTION: {{ item.section }}
              </td>
            </tr>

            <tr class="hover:bg-[#181D26] transition-colors">
              <!-- Module Name & Icon -->
              <td class="py-3 px-4">
                <div class="flex items-center gap-2.5">
                  <div class="w-7 h-7 rounded-none bg-[#0B0C0E] border border-[#232A36] text-hazard flex items-center justify-center shrink-0">
                    <component :is="getModuleIcon(item.icon)" class="w-3.5 h-3.5" />
                  </div>
                  <div>
                    <div class="font-bold text-white text-xs">{{ item.name }}</div>
                    <div class="text-[9px] text-slate-500 font-mono">id: {{ item.id || item.module_id }}</div>
                  </div>
                </div>
              </td>

              <!-- Read / View Checkbox -->
              <td class="py-3 px-3 align-middle text-center">
                <label class="inline-flex items-center justify-center cursor-pointer p-1">
                  <input
                    type="checkbox"
                    v-model="item.can_view"
                    class="w-4 h-4 rounded-none border-[#232A36] bg-[#0B0C0E] text-hazard focus:ring-hazard cursor-pointer"
                  />
                </label>
              </td>

              <!-- Create Checkbox -->
              <td class="py-3 px-3 align-middle text-center">
                <label class="inline-flex items-center justify-center cursor-pointer p-1">
                  <input
                    type="checkbox"
                    v-model="item.can_create"
                    class="w-4 h-4 rounded-none border-[#232A36] bg-[#0B0C0E] text-hazard focus:ring-hazard cursor-pointer"
                  />
                </label>
              </td>

              <!-- Update Checkbox -->
              <td class="py-3 px-3 align-middle text-center">
                <label class="inline-flex items-center justify-center cursor-pointer p-1">
                  <input
                    type="checkbox"
                    v-model="item.can_update"
                    class="w-4 h-4 rounded-none border-[#232A36] bg-[#0B0C0E] text-hazard focus:ring-hazard cursor-pointer"
                  />
                </label>
              </td>

              <!-- Delete Checkbox -->
              <td class="py-3 px-3 align-middle text-center">
                <label class="inline-flex items-center justify-center cursor-pointer p-1">
                  <input
                    type="checkbox"
                    v-model="item.can_delete"
                    class="w-4 h-4 rounded-none border-[#232A36] bg-[#0B0C0E] text-hazard focus:ring-hazard cursor-pointer"
                  />
                </label>
              </td>

              <!-- Row Quick Toggle -->
              <td class="py-3 px-4 align-middle text-center">
                <button
                  type="button"
                  @click="toggleRow(item)"
                  class="px-2 py-0.5 bg-[#0B0C0E] hover:bg-[#181D26] text-slate-300 hover:text-white border border-[#232A36] font-bold text-[9px] rounded-none cursor-pointer uppercase"
                >
                  {{ (item.can_view && item.can_create && item.can_update && item.can_delete) ? 'Reset' : 'Penuh' }}
                </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../services/api';
import {
  ShieldAlert,
  Save,
  CheckSquare,
  Eye,
  X,
  CheckCircle2,
  RefreshCw,
  LayoutDashboard,
  FileText,
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
  Smartphone
} from 'lucide-vue-next';

const roles = ref([
  { code: 'ADMIN', name: 'Admin Operasional' },
  { code: 'FIELD_TEAM', name: 'Tim Lapangan (Mobile)' },
  { code: 'VENDOR', name: 'Mitra Vendor' },
  { code: 'CLIENT', name: 'Client QA & Monitoring' }
]);

const selectedRole = ref('ADMIN');
const matrix = ref([]);
const loading = ref(true);
const saving = ref(false);
const toastMessage = ref(null);

const iconMap = {
  LayoutDashboard,
  FileText,
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
  Smartphone
};

function getModuleIcon(iconName) {
  return iconMap[iconName] || LayoutDashboard;
}

async function loadMatrix(role) {
  loading.value = true;
  try {
    const res = await api.getPermissionMatrix(role);
    const rawMatrix = res.data?.matrix || [];
    matrix.value = rawMatrix.map(item => ({
      ...item,
      can_view: Boolean(item.can_view),
      can_create: Boolean(item.can_create),
      can_update: Boolean(item.can_update),
      can_delete: Boolean(item.can_delete)
    }));
    if (res.data?.roles && res.data.roles.length > 0) {
      roles.value = res.data.roles.filter(r => r.code !== 'SUPERUSER');
    }
  } catch (err) {
    console.error('Failed to load permission matrix:', err);
  } finally {
    loading.value = false;
  }
}

function selectRole(roleCode) {
  selectedRole.value = roleCode;
  loadMatrix(roleCode);
}

function setBatchPermissions(mode) {
  matrix.value.forEach(item => {
    if (mode === 'all') {
      item.can_view = true;
      item.can_create = true;
      item.can_update = true;
      item.can_delete = true;
    } else if (mode === 'read_only') {
      item.can_view = true;
      item.can_create = false;
      item.can_update = false;
      item.can_delete = false;
    } else if (mode === 'clear') {
      item.can_view = false;
      item.can_create = false;
      item.can_update = false;
      item.can_delete = false;
    }
  });
}

function toggleRow(item) {
  const isAll = item.can_view && item.can_create && item.can_update && item.can_delete;
  item.can_view = !isAll;
  item.can_create = !isAll;
  item.can_update = !isAll;
  item.can_delete = !isAll;
}

async function savePermissions() {
  saving.value = true;
  try {
    await api.updateRolePermissions({
      role_code: selectedRole.value,
      matrix: matrix.value,
      permissions: matrix.value
    });
    toastMessage.value = `Pengaturan hak akses untuk role '${selectedRole.value}' berhasil disimpan ke database!`;
    await loadMatrix(selectedRole.value);
    setTimeout(() => {
      toastMessage.value = null;
    }, 4000);
  } catch (err) {
    alert(`Gagal menyimpan hak akses: ${err.message}`);
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  loadMatrix(selectedRole.value);
});
</script>
