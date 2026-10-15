<template>
  <div class="space-y-4 font-mono">
    <!-- Header (CAD Blueprint Style) -->
    <div class="bg-[#13171F] border border-[#232A36] p-4 rounded-xs flex flex-col md:flex-row md:items-center justify-between gap-3 relative overflow-hidden">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xs bg-[#0B0C0E] border border-hazard/40 flex items-center justify-center text-hazard shrink-0">
          <Users class="w-5 h-5" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-base sm:text-lg font-heading font-black text-white uppercase tracking-wider">
              Master Tim & Teknisi Lapangan
            </h2>
            <span class="bg-hazard/10 text-hazard border border-hazard/30 text-[9px] font-mono px-1.5 py-0.2 rounded-none font-bold uppercase">
              {{ teams.length }} TIM AKTIF
            </span>
          </div>
          <p class="text-xs text-slate-400 font-mono mt-0.5">
            Pengelolaan regu kerja lapangan, penanggung jawab (PIC), dan penempatan wilayah penugasan SPK.
          </p>
        </div>
      </div>

      <button
        @click="openAddModal"
        class="cad-btn-primary px-4 py-2 text-xs font-bold flex items-center gap-2 cursor-pointer uppercase active:scale-95 transition-all self-start sm:self-auto"
      >
        <Plus class="w-4 h-4" />
        <span>Tambah Tim Baru</span>
      </button>
    </div>

    <!-- Table Container (CAD High Density) -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs font-mono">
          <thead class="bg-[#0E1117] text-slate-400 font-bold border-b border-[#232A36] uppercase text-[10px] tracking-wider">
            <tr>
              <th class="py-3 px-4">Nama Tim Lapangan</th>
              <th class="py-3 px-4">Ketua Tim / PIC Lapangan</th>
              <th class="py-3 px-4">Area Penugasan Wilayah</th>
              <th class="py-3 px-4">Daftar Anggota Tim</th>
              <th class="py-3 px-4 text-center w-28">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1C2028] text-slate-300">
            <template v-if="loading">
              <tr>
                <td colspan="5" class="py-12 text-center text-slate-500 font-medium">
                  <div class="w-6 h-6 border-2 border-hazard border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                  <span>MEMUAT_DATA_TIM_LAPANGAN...</span>
                </td>
              </tr>
            </template>
            <template v-else-if="teams.length > 0">
              <tr v-for="item in teams" :key="item.id" class="hover:bg-[#181D26] transition-colors">
                <td class="py-3.5 px-4 font-heading font-black text-white uppercase text-xs">
                  {{ item.name }}
                </td>
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-200">{{ item.leader?.name || item.leader_name || '-' }}</div>
                  <div class="text-[10px] font-mono text-hazard">{{ item.leader?.phone || item.leader_phone || '' }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <span class="px-2 py-0.5 rounded-none text-[9px] font-bold bg-[#0B0C0E] border border-[#232A36] text-slate-300 uppercase">
                    {{ item.area?.name || item.area_name || 'SELURUH WILAYAH' }}
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="m in item.members" :key="m.id" class="px-1.5 py-0.2 bg-[#0B0C0E] text-slate-300 border border-[#232A36] rounded-none text-[9px]">
                      {{ m.name }}
                    </span>
                    <span v-if="!item.members || item.members.length === 0" class="text-slate-500 text-[10px] italic">[ HANYA PIC ]</span>
                  </div>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      @click="openEditModal(item)"
                      title="Edit Tim"
                      class="p-1 rounded-none bg-[#0B0C0E] hover:bg-[#181D26] hover:text-white text-slate-400 border border-[#232A36] transition-colors cursor-pointer"
                    >
                      <Pencil class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="handleDelete(item)"
                      title="Hapus Tim"
                      class="p-1 rounded-none bg-[#0B0C0E] hover:bg-red-950/80 hover:text-red-400 text-slate-400 border border-[#232A36] transition-colors cursor-pointer"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="5" class="py-12 text-center text-slate-500 font-medium border border-dashed border-[#232A36]">
                  [ BELUM ADA TIM LAPANGAN TERDAFTAR ]
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form (CAD Style) -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 bg-[#0B0C0E]/80 backdrop-blur-xs flex items-center justify-center p-4 font-mono">
        <div class="bg-[#13171F] border border-hazard/40 rounded-xs max-w-md w-full shadow-2xl p-5 space-y-4 text-xs">
          <div class="flex items-center justify-between border-b border-[#232A36] pb-3">
            <h3 class="font-heading font-black text-sm text-white uppercase tracking-wider">
              {{ isEditing ? 'Edit' : 'Tambah' }} Tim Lapangan
            </h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-white p-1">
              <X class="w-4 h-4" />
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-3">
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Nama Tim Lapangan *</label>
              <input
                required
                type="text"
                placeholder="CONTOH: TIM ALPHA JAKARTA"
                v-model="formInput.name"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard text-xs uppercase"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Ketua Tim / PIC *</label>
              <select
                required
                v-model="formInput.leader_user_id"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard text-xs font-mono"
              >
                <option value="" disabled>PILIH TEKNISI / PIC</option>
                <option v-for="u in fieldUsers" :key="u.id" :value="u.id">
                  {{ u.name }} ({{ u.phone || 'No HP -' }})
                </option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Area Operasional Penugasan *</label>
              <select
                required
                v-model="formInput.area_id"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard text-xs font-mono"
              >
                <option value="" disabled>PILIH AREA</option>
                <option v-for="a in areasList" :key="a.id" :value="a.id">
                  {{ a.name }} ({{ a.city || a.province || 'Wilayah' }})
                </option>
              </select>
            </div>

            <!-- Team Members Multi-Selection Checkbox -->
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Anggota Tim Tambahan (Opsional)</label>
              <div class="max-h-36 overflow-y-auto p-2 bg-[#0B0C0E] border border-[#232A36] rounded-none space-y-1">
                <label
                  v-for="u in fieldUsers.filter(u => u.id !== formInput.leader_user_id)"
                  :key="u.id"
                  class="flex items-center gap-2 p-1.5 hover:bg-[#181D26] rounded-none cursor-pointer text-xs"
                >
                  <input
                    type="checkbox"
                    :value="u.id"
                    v-model="selectedMemberIds"
                    class="rounded-none border-[#232A36] bg-[#07080A] text-hazard focus:ring-hazard"
                  />
                  <span class="text-slate-300">{{ u.name }} ({{ u.phone || 'No HP -' }})</span>
                </label>
                <div v-if="fieldUsers.filter(u => u.id !== formInput.leader_user_id).length === 0" class="text-slate-500 text-[10px] p-1 italic">
                  [ Tidak ada teknisi lain yang tersedia ]
                </div>
              </div>
            </div>

            <div class="pt-3 border-t border-[#232A36] flex items-center justify-end gap-2">
              <button
                type="button"
                @click="showModal = false"
                class="px-4 py-2 bg-[#0B0C0E] hover:bg-[#181D26] text-slate-300 border border-[#232A36] font-bold rounded-xs cursor-pointer uppercase"
              >
                Batal
              </button>
              <button
                type="submit"
                class="cad-btn-primary px-5 py-2 font-bold rounded-xs uppercase cursor-pointer active:scale-95 transition-all"
              >
                {{ isEditing ? 'SIMPAN PERUBAHAN' : 'SIMPAN TIM' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
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
const selectedMemberIds = ref([]);

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
  selectedMemberIds.value = [];
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
  selectedMemberIds.value = (item.members || []).map(m => m.id);
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
      leader_id: formInput.value.leader_user_id || formInput.value.leader_id,
      member_ids: selectedMemberIds.value
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
    selectedMemberIds.value = [];
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
