<template>
  <div class="space-y-5">
    <!-- Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-purple-700 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-purple-700/20">
            <ShieldCheck class="w-4 h-4" />
          </div>
          <h2 class="text-xl font-black text-slate-900 tracking-tight">Superuser System Console</h2>
        </div>
        <p class="text-xs text-slate-500 mt-1 font-medium">Pusat administrasi sistem, otorisasi RBAC, konfigurasi gateway, dan audit keamanan.</p>
      </div>
      <button
        @click="loadData"
        class="px-3.5 py-2 glass-card hover:bg-white rounded-xl text-slate-700 hover:text-slate-900 text-xs font-bold flex items-center gap-2 shadow-xs transition-all duration-200 active:scale-95 border border-slate-200/80 self-start sm:self-auto cursor-pointer"
      >
        <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin' : '']" />
        <span>Refresh</span>
      </button>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex gap-2 border-b border-slate-200/80 pb-2.5 text-xs font-bold overflow-x-auto">
      <button
        @click="activeTab = 'dashboard'"
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer',
          activeTab === 'dashboard' ? 'bg-purple-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100/80'
        ]"
      >
        <LayoutDashboard class="w-4 h-4" />
        <span>Dashboard Eksekutif</span>
      </button>

      <button
        @click="activeTab = 'permissions'"
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer',
          activeTab === 'permissions' ? 'bg-purple-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100/80'
        ]"
      >
        <ShieldAlert class="w-4 h-4" />
        <span>Hak Akses & Role (CRUD)</span>
      </button>

      <button
        @click="activeTab = 'users'"
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer',
          activeTab === 'users' ? 'bg-purple-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100/80'
        ]"
      >
        <Users class="w-4 h-4" />
        <span>Pengguna & Akun ({{ users.length }})</span>
      </button>

      <button
        @click="activeTab = 'settings'"
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer',
          activeTab === 'settings' ? 'bg-purple-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100/80'
        ]"
      >
        <Settings class="w-4 h-4" />
        <span>Pengaturan Gateway & Sistem ({{ settings.length }})</span>
      </button>

      <button
        @click="activeTab = 'audit'"
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer',
          activeTab === 'audit' ? 'bg-purple-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100/80'
        ]"
      >
        <History class="w-4 h-4" />
        <span>Audit Trail Lengkap ({{ auditLogs.length }})</span>
      </button>
    </div>

    <!-- Executive Dashboard Tab -->
    <div v-if="activeTab === 'dashboard'">
      <ExecutiveDashboardView />
    </div>

    <!-- Permissions Matrix Tab -->
    <div v-if="activeTab === 'permissions'">
      <PermissionMatrix />
    </div>

    <!-- Users Tab -->
    <div v-if="activeTab === 'users'" class="space-y-4">
      <div class="flex justify-end">
        <button
          @click="openAddUserModal"
          class="px-4 py-2 bg-gradient-to-r from-purple-900 to-indigo-800 hover:from-purple-800 hover:to-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-md shadow-purple-900/20 active:scale-95 transition-all cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Pengguna Baru</span>
        </button>
      </div>

      <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-100/70 text-slate-500 font-bold border-b border-slate-200/80">
              <tr>
                <th class="py-3 px-4">Nama Lengkap</th>
                <th class="py-3 px-4">Email Login</th>
                <th class="py-3 px-4">Telepon</th>
                <th class="py-3 px-4">Role Hak Akses</th>
                <th class="py-3 px-4">Afiliasi Client</th>
                <th class="py-3 px-4">Status Akun</th>
                <th class="py-3 px-4 text-center w-28">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80 text-slate-700">
              <template v-if="loading">
                <tr>
                  <td colspan="7" class="py-10 text-center text-slate-400 font-medium">Memuat data pengguna...</td>
                </tr>
              </template>
              <template v-else-if="users.length > 0">
                <tr v-for="u in users" :key="u.id" class="hover:bg-purple-50/30 transition-colors">
                  <td class="py-3.5 px-4 font-bold text-slate-900">{{ u.name }}</td>
                  <td class="py-3.5 px-4 font-mono">{{ u.email }}</td>
                  <td class="py-3.5 px-4 font-mono">{{ u.phone || '-' }}</td>
                  <td class="py-3.5 px-4">
                    <span :class="[
                      'px-2.5 py-0.5 rounded-full font-bold text-[10px] border',
                      u.role === 'SUPERUSER' ? 'bg-purple-100 text-purple-800 border-purple-200' :
                      u.role === 'ADMIN' ? 'bg-blue-100 text-blue-800 border-blue-200' :
                      u.role === 'FIELD_TEAM' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200'
                    ]">
                      {{ u.role === 'VENDOR' ? 'CLIENT' : u.role }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 font-medium">{{ u.vendor_name || '-' }}</td>
                  <td class="py-3.5 px-4">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                      AKTIF ✓
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                      <button
                        @click="openEditUserModal(u)"
                        title="Edit Pengguna"
                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-purple-100 hover:text-purple-800 text-slate-600 transition-colors active:scale-90 cursor-pointer"
                      >
                        <Pencil class="w-3.5 h-3.5" />
                      </button>
                      <button
                        @click="handleDeleteUser(u)"
                        title="Hapus Pengguna"
                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-100 hover:text-rose-700 text-slate-600 transition-colors active:scale-90 cursor-pointer"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td colspan="7" class="py-10 text-center text-slate-400 font-medium">Belum ada pengguna.</td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Settings Tab -->
    <div v-if="activeTab === 'settings'" class="glass-card rounded-3xl p-6 shadow-glass border border-white/80 space-y-4 text-xs">
      <h3 class="font-black text-sm text-slate-900 border-b border-slate-200/80 pb-2.5">
        Konfigurasi Sistem Global & Integrasi
      </h3>

      <div class="space-y-4">
        <div v-for="s in settings" :key="s.id" class="p-4.5 bg-white/70 border border-slate-200/80 rounded-2xl space-y-2 shadow-xs">
          <div class="flex items-center justify-between">
            <div>
              <span class="font-mono font-bold text-slate-900 text-sm">{{ s.key }}</span>
              <p class="text-slate-500 text-[11px] mt-0.5 font-medium">{{ s.description }}</p>
            </div>
            <span class="text-[10px] text-slate-400 font-mono">
              Diperbarui: {{ new Date(s.updated_at).toLocaleString('id-ID') }}
            </span>
          </div>

          <div class="flex items-center gap-2 pt-1">
            <input
              type="text"
              v-model="s.value"
              class="w-full px-3.5 py-2.5 border border-slate-200/80 rounded-xl bg-white focus:ring-2 focus:ring-purple-500 font-mono text-xs shadow-xs"
            />
            <button
              @click="handleUpdateSetting(s.key, s.value)"
              :disabled="savingSetting"
              class="px-5 py-2.5 bg-gradient-to-r from-purple-900 to-indigo-800 hover:from-purple-800 hover:to-indigo-700 text-white font-bold rounded-xl whitespace-nowrap shadow-xs active:scale-95 transition-all cursor-pointer"
            >
              Simpan
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Audit Tab -->
    <div v-if="activeTab === 'audit'" class="space-y-4">
      <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-100/70 text-slate-500 font-bold border-b border-slate-200/80">
              <tr>
                <th class="py-3 px-4">Waktu (WIB)</th>
                <th class="py-3 px-4">Pengguna</th>
                <th class="py-3 px-4">Aksi / Event</th>
                <th class="py-3 px-4">Entitas</th>
                <th class="py-3 px-4">Detail Perubahan</th>
                <th class="py-3 px-4">Alamat IP</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80 text-slate-700 font-mono text-[11px]">
              <template v-if="loading">
                <tr>
                  <td colspan="6" class="py-10 text-center text-slate-400 font-medium font-sans">Memuat log audit...</td>
                </tr>
              </template>
              <template v-else-if="auditLogs.length > 0">
                <tr v-for="log in auditLogs" :key="log.id" class="hover:bg-purple-50/30 transition-colors">
                  <td class="py-3.5 px-4 whitespace-nowrap">{{ new Date(log.created_at).toLocaleString('id-ID') }}</td>
                  <td class="py-3.5 px-4 font-bold text-slate-900">{{ log.user_name || 'System' }}</td>
                  <td class="py-3.5 px-4">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-900 border border-purple-200">
                      {{ log.action }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4">{{ log.entity_type }} #{{ log.entity_id || '-' }}</td>
                  <td class="py-3.5 px-4 truncate max-w-xs font-sans text-slate-600">
                    {{ log.new_value ? (typeof log.new_value === 'string' ? log.new_value : JSON.stringify(log.new_value)) : '-' }}
                  </td>
                  <td class="py-3.5 px-4 text-slate-400">{{ log.ip_address || '127.0.0.1' }}</td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add / Edit User Modal -->
    <div v-if="showUserModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="glass-modal rounded-3xl max-w-md w-full shadow-2xl p-6 space-y-4 text-xs border border-white/80">
        <h3 class="font-black text-sm text-slate-900 border-b border-slate-200/80 pb-2.5">
          {{ isEditingUser ? 'Edit Akun Pengguna' : 'Tambah Akun Pengguna Baru' }}
        </h3>

        <form @submit.prevent="handleSaveUser" class="space-y-3">
          <div>
            <label class="block font-bold mb-1">Nama Lengkap *</label>
            <input
              required
              type="text"
              placeholder="Contoh: Dian Anggraini"
              v-model="newUser.name"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl"
            />
          </div>

          <div>
            <label class="block font-bold mb-1">Email Login *</label>
            <input
              required
              type="email"
              placeholder="dian.admin@sgx.com"
              v-model="newUser.email"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl"
            />
          </div>

          <div>
            <label class="block font-bold mb-1">Nomor Telepon / WhatsApp</label>
            <input
              type="text"
              placeholder="0812xxxxxxxx"
              v-model="newUser.phone"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl"
            />
          </div>

          <div>
            <label class="block font-bold mb-1">Role Hak Akses *</label>
            <select
              v-model="newUser.role"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-medium"
            >
              <option value="ADMIN">ADMIN (Operasional & SPK)</option>
              <option value="FIELD_TEAM">FIELD_TEAM (Tim Lapangan Mobile)</option>
              <option value="VENDOR">CLIENT (Pemberi Tugas / Indomarco / Smartfren)</option>
              <option value="SUPERUSER">SUPERUSER (Administrator Sistem)</option>
            </select>
          </div>

          <div v-if="newUser.role === 'VENDOR'">
            <label class="block font-bold mb-1">Pilih Perusahaan Client *</label>
            <select
              required
              v-model="newUser.vendor_id"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl"
            >
              <option value="">-- Pilih Perusahaan Client --</option>
              <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }} ({{ v.code }})</option>
            </select>
          </div>

          <div>
            <label class="block font-bold mb-1">
              {{ isEditingUser ? 'Password Baru (Kosongkan jika tidak diubah)' : 'Password Awal' }}
            </label>
            <input
              type="text"
              placeholder="admin123"
              v-model="newUser.password"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-mono"
            />
          </div>

          <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="showUserModal = false"
              class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-5 py-2 bg-purple-900 hover:bg-purple-800 text-white font-bold rounded-xl shadow-xs active:scale-95 transition-all cursor-pointer"
            >
              {{ isEditingUser ? 'Simpan Perubahan' : 'Buat Pengguna' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { api } from '../../services/api';
import PermissionMatrix from '../../components/PermissionMatrix.vue';
import ExecutiveDashboardView from '../../components/ExecutiveDashboardView.vue';
import {
  ShieldCheck,
  ShieldAlert,
  Users,
  Settings,
  History,
  Plus,
  RefreshCw,
  LayoutDashboard,
  Pencil,
  Trash2
} from 'lucide-vue-next';

const props = defineProps({
  initialTab: {
    type: String,
    default: 'dashboard'
  }
});

const activeTab = ref(props.initialTab || 'dashboard');

watch(() => props.initialTab, (newTab) => {
  if (newTab) {
    activeTab.value = newTab;
  }
});

const users = ref([]);
const settings = ref([]);
const auditLogs = ref([]);
const vendors = ref([]);
const loading = ref(true);
const savingSetting = ref(false);

const showUserModal = ref(false);
const isEditingUser = ref(false);
const newUser = ref({
  id: null,
  name: '',
  email: '',
  phone: '',
  password: 'admin123',
  role: 'ADMIN',
  vendor_id: ''
});

async function loadData() {
  loading.value = true;
  try {
    const [uRes, sRes, aRes, vRes] = await Promise.all([
      api.getUsers(),
      api.getSettings(),
      api.getAuditLogs({ limit: 100 }),
      api.getVendors()
    ]);
    users.value = uRes.data || [];
    settings.value = sRes.data || [];
    auditLogs.value = aRes.data || [];
    vendors.value = vRes.data || [];
  } catch (err) {
    console.error('Failed to load superuser data:', err);
  } finally {
    loading.value = false;
  }
}

function openAddUserModal() {
  isEditingUser.value = false;
  newUser.value = {
    id: null,
    name: '',
    email: '',
    phone: '',
    password: 'admin123',
    role: 'ADMIN',
    vendor_id: ''
  };
  showUserModal.value = true;
}

function openEditUserModal(user) {
  isEditingUser.value = true;
  newUser.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    phone: user.phone || '',
    password: '',
    role: user.role,
    vendor_id: user.vendor_id || ''
  };
  showUserModal.value = true;
}

async function handleSaveUser() {
  try {
    if (isEditingUser.value) {
      await api.updateUser(newUser.value.id, newUser.value);
      alert('Data pengguna berhasil diperbarui!');
    } else {
      await api.createUser(newUser.value);
      alert('Pengguna baru berhasil dibuat!');
    }
    showUserModal.value = false;
    loadData();
  } catch (err) {
    alert(`Gagal menyimpan user: ${err.message}`);
  }
}

async function handleDeleteUser(user) {
  const confirmed = window.confirm(`Apakah Anda yakin ingin menghapus akun user "${user.name}" (${user.email})?`);
  if (!confirmed) return;

  try {
    await api.deleteUser(user.id);
    alert('User berhasil dihapus!');
    loadData();
  } catch (err) {
    alert(`Gagal menghapus user: ${err.message}`);
  }
}

async function handleUpdateSetting(key, value) {
  savingSetting.value = true;
  try {
    await api.updateSetting(key, value);
    alert(`Pengaturan '${key}' berhasil diperbarui!`);
    loadData();
  } catch (err) {
    alert(`Gagal update setting: ${err.message}`);
  } finally {
    savingSetting.value = false;
  }
}

onMounted(() => {
  loadData();
});
</script>
