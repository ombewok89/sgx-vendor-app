<template>
  <div class="space-y-4 font-mono">
    <!-- Header (CAD Blueprint Style) -->
    <div class="bg-[#13171F] border border-[#232A36] p-4 rounded-xs flex flex-col md:flex-row md:items-center justify-between gap-3 relative overflow-hidden">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xs bg-[#0B0C0E] border border-hazard/40 flex items-center justify-center text-hazard shrink-0">
          <MapPin class="w-5 h-5" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-base sm:text-lg font-heading font-black text-white uppercase tracking-wider">
              Master Area & Wilayah Operasional
            </h2>
            <span class="bg-hazard/10 text-hazard border border-hazard/30 text-[9px] font-mono px-1.5 py-0.2 rounded-none font-bold uppercase">
              {{ areas.length }} REGION
            </span>
          </div>
          <p class="text-xs text-slate-400 font-mono mt-0.5">
            Cakupan wilayah operasional, kota, kabupaten, dan provinsi penugasan SPK lapangan.
          </p>
        </div>
      </div>

      <button
        @click="openAddModal"
        class="cad-btn-primary px-4 py-2 text-xs font-bold flex items-center gap-2 cursor-pointer uppercase active:scale-95 transition-all self-start sm:self-auto"
      >
        <Plus class="w-4 h-4" />
        <span>Tambah Area Baru</span>
      </button>
    </div>

    <!-- Table Container (CAD High Density) -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs font-mono">
          <thead class="bg-[#0E1117] text-slate-400 font-bold border-b border-[#232A36] uppercase text-[10px] tracking-wider">
            <tr>
              <th class="py-3 px-4">Nama Wilayah Area</th>
              <th class="py-3 px-4">Kota / Kabupaten</th>
              <th class="py-3 px-4">Provinsi</th>
              <th class="py-3 px-4">Kecamatan</th>
              <th class="py-3 px-4 text-center w-28">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1C2028] text-slate-300">
            <template v-if="loading">
              <tr>
                <td colspan="5" class="py-12 text-center text-slate-500 font-medium">
                  <div class="w-6 h-6 border-2 border-hazard border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                  <span>MEMUAT_DATA_MASTER_AREA...</span>
                </td>
              </tr>
            </template>
            <template v-else-if="areas.length > 0">
              <tr v-for="item in areas" :key="item.id" class="hover:bg-[#181D26] transition-colors">
                <td class="py-3.5 px-4 font-heading font-black text-white uppercase text-xs">
                  {{ item.name }}
                </td>
                <td class="py-3.5 px-4 font-bold text-slate-200">{{ item.city || '-' }}</td>
                <td class="py-3.5 px-4 text-slate-400">{{ item.province || '-' }}</td>
                <td class="py-3.5 px-4 text-slate-500 text-[11px]">{{ item.district || '-' }}</td>
                <td class="py-3.5 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      @click="openEditModal(item)"
                      title="Edit Area"
                      class="p-1 rounded-none bg-[#0B0C0E] hover:bg-[#181D26] hover:text-white text-slate-400 border border-[#232A36] transition-colors cursor-pointer"
                    >
                      <Pencil class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="handleDelete(item)"
                      title="Hapus Area"
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
                  [ BELUM ADA AREA OPERASIONAL ]
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
              {{ isEditing ? 'Edit' : 'Tambah' }} Master Area
            </h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-white p-1">
              <X class="w-4 h-4" />
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-3">
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Nama Area Operasional *</label>
              <input
                required
                type="text"
                placeholder="CONTOH: JAKARTA BARAT, BANDUNG RAYA"
                v-model="formInput.name"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard text-xs uppercase"
              />
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Kota / Kabupaten *</label>
                <input
                  required
                  type="text"
                  placeholder="Kota Jakarta Barat"
                  v-model="formInput.city"
                  class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard text-xs"
                />
              </div>
              <div>
                <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Provinsi *</label>
                <input
                  required
                  type="text"
                  placeholder="DKI Jakarta"
                  v-model="formInput.province"
                  class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard text-xs"
                />
              </div>
            </div>
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Kecamatan (Opsional)</label>
              <input
                type="text"
                placeholder="Grogol Petamburan"
                v-model="formInput.district"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard text-xs"
              />
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
                {{ isEditing ? 'SIMPAN PERUBAHAN' : 'SIMPAN AREA' }}
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
import { MapPin, Plus, Pencil, Trash2, X } from 'lucide-vue-next';

const areas = ref([]);
const loading = ref(true);
const showModal = ref(false);
const isEditing = ref(false);
const formInput = ref({});

async function loadData() {
  loading.value = true;
  try {
    const res = await api.getAreas();
    areas.value = res.data || [];
  } catch (err) {
    console.error('Failed to load areas:', err);
  } finally {
    loading.value = false;
  }
}

function openAddModal() {
  isEditing.value = false;
  formInput.value = {};
  showModal.value = true;
}

function openEditModal(item) {
  isEditing.value = true;
  formInput.value = { ...item };
  showModal.value = true;
}

async function handleSubmit() {
  try {
    if (isEditing.value) {
      await api.updateArea(formInput.value.id, formInput.value);
      alert('Data area berhasil diperbarui!');
    } else {
      await api.createArea(formInput.value);
      alert('Area baru berhasil ditambahkan!');
    }
    showModal.value = false;
    formInput.value = {};
    loadData();
  } catch (err) {
    alert(`Gagal menyimpan area: ${err.message}`);
  }
}

async function handleDelete(item) {
  if (!confirm(`Hapus area "${item.name}"?`)) return;
  try {
    await api.deleteArea(item.id);
    alert('Area berhasil dihapus!');
    loadData();
  } catch (err) {
    alert(`Gagal menghapus area: ${err.message}`);
  }
}

onMounted(() => {
  loadData();
});
</script>
