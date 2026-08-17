<template>
  <div class="space-y-5">
    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/80 p-5 rounded-3xl border border-slate-200/80 shadow-xs">
      <div>
        <div class="flex items-center gap-2">
          <ShieldAlert class="w-5 h-5 text-purple-700" />
          <h3 class="text-base font-black text-slate-900">Konfigurasi Hak Akses Role & Menu (CRUD)</h3>
        </div>
        <p class="text-xs text-slate-500 mt-1">
          Atur izin membuka menu serta hak <strong>Tambah (Create)</strong>, <strong>Lihat (Read)</strong>, <strong>Ubah (Update)</strong>, dan <strong>Hapus (Delete)</strong> untuk setiap role.
        </p>
      </div>

      <!-- Role Selector Pills -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
        <button
          v-for="r in roles"
          :key="r.code"
          @click="selectRole(r.code)"
          :class="[
            'px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 active:scale-95 cursor-pointer whitespace-nowrap border',
            selectedRole === r.code
              ? 'bg-purple-900 text-white border-purple-900 shadow-md shadow-purple-900/20'
              : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'
          ]"
        >
          {{ r.name || r.code }}
        </button>
      </div>
    </div>

    <!-- Quick Batch Action Buttons & Save Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-purple-50/60 border border-purple-200/80 p-3.5 rounded-2xl">
      <div class="flex items-center gap-2 flex-wrap">
        <span class="text-[11px] font-bold text-purple-900 uppercase tracking-wider mr-1">Terapkan Cepat:</span>
        <button
          type="button"
          @click="setBatchPermissions('all')"
          class="px-3 py-1.5 bg-white hover:bg-purple-100 text-purple-900 border border-purple-200 text-xs font-bold rounded-lg shadow-2xs cursor-pointer transition-all active:scale-95 flex items-center gap-1.5"
        >
          <CheckSquare class="w-3.5 h-3.5 text-purple-700" />
          <span>Beri Semua Hak</span>
        </button>
        <button
          type="button"
          @click="setBatchPermissions('read_only')"
          class="px-3 py-1.5 bg-white hover:bg-purple-100 text-purple-900 border border-purple-200 text-xs font-bold rounded-lg shadow-2xs cursor-pointer transition-all active:scale-95 flex items-center gap-1.5"
        >
          <Eye class="w-3.5 h-3.5 text-purple-700" />
          <span>Hanya Baca (Read-Only)</span>
        </button>
        <button
          type="button"
          @click="setBatchPermissions('clear')"
          class="px-3 py-1.5 bg-white hover:bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold rounded-lg shadow-2xs cursor-pointer transition-all active:scale-95 flex items-center gap-1.5"
        >
          <X class="w-3.5 h-3.5" />
          <span>Kosongkan Semua</span>
        </button>
      </div>

      <div class="flex items-center gap-2.5 self-end sm:self-auto">
        <button
          type="button"
          @click="savePermissions"
          :disabled="saving"
          class="px-5 py-2.5 bg-gradient-to-r from-purple-900 via-purple-800 to-indigo-800 hover:from-purple-800 hover:to-indigo-700 text-white font-black text-xs rounded-xl shadow-md shadow-purple-900/30 flex items-center gap-2 active:scale-95 transition-all cursor-pointer"
        >
          <Save class="w-4 h-4" />
          <span>{{ saving ? 'Menyimpan Matriks...' : 'Simpan Pengaturan Hak Akses' }}</span>
        </button>
      </div>
    </div>

    <!-- Success / Error Toast -->
    <div v-if="toastMessage" class="p-3.5 bg-emerald-600 text-white font-semibold text-xs rounded-2xl shadow-lg flex items-center justify-between">
      <div class="flex items-center gap-2">
        <CheckCircle2 class="w-4 h-4 text-emerald-200" />
        <span>{{ toastMessage }}</span>
      </div>
      <button @click="toastMessage = null" class="p-1 hover:bg-emerald-700 rounded-lg">
        <X class="w-4 h-4" />
      </button>
    </div>

    <!-- Permission Matrix Table -->
    <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
      <table class="w-full text-left text-xs border-collapse">
        <thead class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-200/80 text-[11px] uppercase tracking-wider">
          <tr>
            <th class="py-3.5 px-5 w-[38%]">Modul / Menu Sistem</th>
            <th class="py-3.5 px-3 w-[13%] text-center">
              <div class="flex flex-col items-center">
                <span class="text-slate-800 font-bold">Lihat (Read)</span>
                <span class="text-[9px] text-slate-400 font-normal">Buka Menu</span>
              </div>
            </th>
            <th class="py-3.5 px-3 w-[13%] text-center">
              <div class="flex flex-col items-center">
                <span class="text-blue-900 font-bold">Tambah (Create)</span>
                <span class="text-[9px] text-slate-400 font-normal">Buat Data</span>
              </div>
            </th>
            <th class="py-3.5 px-3 w-[13%] text-center">
              <div class="flex flex-col items-center">
                <span class="text-amber-900 font-bold">Ubah (Update)</span>
                <span class="text-[9px] text-slate-400 font-normal">Edit & Proses</span>
              </div>
            </th>
            <th class="py-3.5 px-3 w-[13%] text-center">
              <div class="flex flex-col items-center">
                <span class="text-rose-900 font-bold">Hapus (Delete)</span>
                <span class="text-[9px] text-slate-400 font-normal">Hapus Entitas</span>
              </div>
            </th>
            <th class="py-3.5 px-4 w-[10%] text-center">Aksi Cepat</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-100 text-slate-700 bg-white/70">
          <template v-if="loading">
            <tr>
              <td colspan="6" class="py-14 text-center text-slate-400">
                <div class="flex flex-col items-center justify-center gap-2">
                  <RefreshCw class="w-6 h-6 animate-spin text-purple-700" />
                  <span>Memuat matriks hak akses...</span>
                </div>
              </td>
            </tr>
          </template>

          <template v-else>
            <tr
              v-for="item in matrix"
              :key="item.id"
              class="hover:bg-purple-50/40 transition-colors"
            >
              <!-- Module Name & Section -->
              <td class="py-3.5 px-5 align-middle">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-xl bg-purple-100/70 text-purple-900 flex items-center justify-center font-bold text-xs shrink-0">
                    <component :is="getModuleIcon(item.icon)" class="w-4 h-4" />
                  </div>
                  <div>
                    <div class="font-bold text-slate-900 text-sm">{{ item.name }}</div>
                    <div class="flex items-center gap-2 mt-0.5 text-[10px]">
                      <span class="font-semibold text-purple-700 bg-purple-50 px-1.5 py-0.2 rounded border border-purple-200">
                        {{ item.section }}
                      </span>
                      <span class="font-mono text-slate-400">id: {{ item.id }}</span>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Read / View Checkbox -->
              <td class="py-3.5 px-3 align-middle text-center">
                <label class="inline-flex items-center justify-center cursor-pointer p-2 rounded-xl hover:bg-purple-100/50 transition-colors">
                  <input
                    type="checkbox"
                    v-model="item.can_view"
                    class="w-4 h-4 rounded text-purple-700 focus:ring-purple-500 border-slate-300 cursor-pointer"
                  />
                </label>
              </td>

              <!-- Create Checkbox -->
              <td class="py-3.5 px-3 align-middle text-center">
                <label class="inline-flex items-center justify-center cursor-pointer p-2 rounded-xl hover:bg-blue-100/50 transition-colors">
                  <input
                    type="checkbox"
                    v-model="item.can_create"
                    class="w-4 h-4 rounded text-blue-700 focus:ring-blue-500 border-slate-300 cursor-pointer"
                  />
                </label>
              </td>

              <!-- Update Checkbox -->
              <td class="py-3.5 px-3 align-middle text-center">
                <label class="inline-flex items-center justify-center cursor-pointer p-2 rounded-xl hover:bg-amber-100/50 transition-colors">
                  <input
                    type="checkbox"
                    v-model="item.can_update"
                    class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-slate-300 cursor-pointer"
                  />
                </label>
              </td>

              <!-- Delete Checkbox -->
              <td class="py-3.5 px-3 align-middle text-center">
                <label class="inline-flex items-center justify-center cursor-pointer p-2 rounded-xl hover:bg-rose-100/50 transition-colors">
                  <input
                    type="checkbox"
                    v-model="item.can_delete"
                    class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-slate-300 cursor-pointer"
                  />
                </label>
              </td>

              <!-- Row Quick Toggle -->
              <td class="py-3.5 px-4 align-middle text-center">
                <button
                  type="button"
                  @click="toggleRow(item)"
                  class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[10px] rounded-lg cursor-pointer transition-all"
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
  { code: 'VENDOR', name: 'Mitra Vendor' }
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
    matrix.value = res.data.matrix || [];
    if (res.data.roles && res.data.roles.length > 0) {
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
      permissions: matrix.value
    });
    toastMessage.value = `Pengaturan hak akses untuk role '${selectedRole.value}' berhasil disimpan!`;
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
