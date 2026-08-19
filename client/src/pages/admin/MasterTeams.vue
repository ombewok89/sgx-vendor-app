<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white/80 p-5 rounded-3xl border border-slate-200/80 shadow-xs">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-900 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-brand-900/20">
          <Users class="w-5 h-5" />
        </div>
        <div>
          <h2 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
            <span>Master Tim & Teknisi Lapangan</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-100 text-brand-900 font-mono">
              {{ teams.length }} Tim
            </span>
          </h2>
          <p class="text-xs text-slate-500 font-medium">Pengelolaan tim kerja lapangan, penanggung jawab (PIC), dan penempatan wilayah.</p>
        </div>
      </div>

      <button
        @click="openAddModal"
        class="px-4 py-2.5 bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 hover:from-brand-800 hover:to-brand-600 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-md shadow-brand-900/20 active:scale-95 transition-all self-start sm:self-auto cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>Tambah Tim Baru</span>
      </button>
    </div>

    <!-- Table Container -->
    <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100/70 text-slate-600 font-bold border-b border-slate-200/80">
            <tr>
              <th class="py-3.5 px-4">Nama Tim Lapangan</th>
              <th class="py-3.5 px-4">Ketua Tim / PIC Lapangan</th>
              <th class="py-3.5 px-4">Area Penugasan Wilayah</th>
              <th class="py-3.5 px-4">Daftar Anggota Tim</th>
              <th class="py-3.5 px-4 text-center w-28">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/80 text-slate-700">
            <template v-if="loading">
              <tr>
                <td colspan="5" class="py-12 text-center text-slate-400 font-medium">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <div class="w-6 h-6 border-2 border-brand-900 border-t-transparent rounded-full animate-spin"></div>
                    <span>Memuat data tim lapangan...</span>
                  </div>
                </td>
              </tr>
            </template>
            <template v-else-if="teams.length > 0">
              <tr v-for="item in teams" :key="item.id" class="hover:bg-brand-50/30 transition-colors">
                <td class="py-3.5 px-4 font-bold text-slate-900">{{ item.name }}</td>
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900">{{ item.leader_name }}</div>
                  <div class="text-[11px] font-mono text-slate-500">{{ item.leader_phone }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 border border-slate-200 text-slate-800">
                    {{ item.area_name || 'Seluruh Wilayah' }}
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="m in item.members" :key="m.id" class="px-2 py-0.5 bg-brand-50 text-brand-800 border border-brand-200 rounded-md text-[10px] font-semibold">
                      {{ m.name }}
                    </span>
                    <span v-if="!item.members || item.members.length === 0" class="text-slate-400 italic">Hanya PIC</span>
                  </div>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      @click="openEditModal(item)"
                      title="Edit Tim"
                      class="p-1.5 rounded-lg bg-slate-100 hover:bg-brand-100 hover:text-brand-900 text-slate-600 transition-colors active:scale-90 cursor-pointer"
                    >
                      <Pencil class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="handleDelete(item)"
                      title="Hapus Tim"
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
                <td colspan="5" class="py-12 text-center text-slate-400 font-medium">Belum ada tim lapangan.</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="glass-modal rounded-3xl max-w-md w-full shadow-2xl p-6 space-y-4 text-xs border border-white/80">
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5">
          <h3 class="font-black text-sm text-slate-900">
            {{ isEditing ? 'Edit' : 'Tambah' }} Tim Lapangan
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-3">
          <div>
            <label class="block font-bold mb-1">Nama Tim Lapangan *</label>
            <input
              required
              type="text"
              placeholder="Contoh: Tim Alpha Jakarta"
              v-model="formInput.name"
              class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
            />
          </div>
          <div>
            <label class="block font-bold mb-1">Ketua Tim / PIC *</label>
            <select
              required
              v-model="formInput.leader_user_id"
              class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-xs font-bold"
            >
              <option value="" disabled>Pilih Teknisi / PIC</option>
              <option v-for="u in fieldUsers" :key="u.id" :value="u.id">
                {{ u.name }} ({{ u.phone }})
              </option>
            </select>
          </div>
          <div>
            <label class="block font-bold mb-1">Area Operasional Penugasan *</label>
            <select
              required
              v-model="formInput.area_id"
              class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-xs font-bold"
            >
              <option value="" disabled>Pilih Area</option>
              <option v-for="a in areasList" :key="a.id" :value="a.id">
                {{ a.name }} ({{ a.city }})
              </option>
            </select>
          </div>

          <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-5 py-2 bg-brand-900 text-white font-bold rounded-xl shadow-xs active:scale-95 transition-all cursor-pointer"
            >
              {{ isEditing ? 'Simpan Perubahan' : 'Simpan Tim' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../../services/api';
import { Users, Plus, Pencil, Trash2, X } from 'lucide-vue-next';

const teams = ref([]);
const fieldUsers = ref([]);
const areasList = ref([]);
const loading = ref(true);
const showModal = ref(false);
const isEditing = ref(false);
const formInput = ref({});

async function loadData() {
  loading.value = true;
  try {
    const [tRes, uRes, aRes] = await Promise.all([
      api.getFieldTeams(),
      api.getUsers({ role: 'FIELD_TEAM' }),
      api.getAreas()
    ]);
    teams.value = tRes.data || [];
    fieldUsers.value = uRes.data || [];
    areasList.value = aRes.data || [];
  } catch (err) {
    console.error('Failed to load teams:', err);
  } finally {
    loading.value = false;
  }
}

function openAddModal() {
  isEditing.value = false;
  formInput.value = {
    name: '',
    leader_user_id: fieldUsers.value[0]?.id || '',
    leader_id: fieldUsers.value[0]?.id || '',
    area_id: areasList.value[0]?.id || ''
  };
  showModal.value = true;
}

function openEditModal(item) {
  isEditing.value = true;
  formInput.value = {
    ...item,
    leader_user_id: item.leader_user_id || item.leader_id || item.leader?.id || '',
    leader_id: item.leader_user_id || item.leader_id || item.leader?.id || '',
    area_id: item.area_id || item.area?.id || ''
  };
  showModal.value = true;
}

async function handleSubmit() {
  try {
    const payload = {
      ...formInput.value,
      leader_user_id: formInput.value.leader_user_id || formInput.value.leader_id,
      leader_id: formInput.value.leader_user_id || formInput.value.leader_id
    };
    if (isEditing.value) {
      await api.updateFieldTeam(formInput.value.id, payload);
      alert('Data tim berhasil diperbarui!');
    } else {
      await api.createFieldTeam(payload);
      alert('Tim lapangan baru berhasil ditambahkan!');
    }
    showModal.value = false;
    formInput.value = {};
    loadData();
  } catch (err) {
    alert(`Gagal menyimpan tim: ${err.message}`);
  }
}

async function handleDelete(item) {
  if (!confirm(`Hapus tim "${item.name}"?`)) return;
  try {
    await api.deleteFieldTeam(item.id);
    alert('Tim berhasil dihapus!');
    loadData();
  } catch (err) {
    alert(`Gagal menghapus tim: ${err.message}`);
  }
}

onMounted(() => {
  loadData();
});
</script>
