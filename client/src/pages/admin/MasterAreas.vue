<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white/80 p-5 rounded-3xl border border-slate-200/80 shadow-xs">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-900 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-brand-900/20">
          <MapPin class="w-5 h-5" />
        </div>
        <div>
          <h2 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
            <span>Master Area & Wilayah Operasional</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-100 text-brand-900 font-mono">
              {{ areas.length }} Area
            </span>
          </h2>
          <p class="text-xs text-slate-500 font-medium">Cakupan wilayah operasional, kota, kabupaten, dan provinsi penugasan SPK.</p>
        </div>
      </div>

      <button
        @click="openAddModal"
        class="px-4 py-2.5 bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 hover:from-brand-800 hover:to-brand-600 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-md shadow-brand-900/20 active:scale-95 transition-all self-start sm:self-auto cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>Tambah Area Baru</span>
      </button>
    </div>

    <!-- Table Container -->
    <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100/70 text-slate-600 font-bold border-b border-slate-200/80">
            <tr>
              <th class="py-3.5 px-4">Nama Wilayah Area</th>
              <th class="py-3.5 px-4">Kota / Kabupaten</th>
              <th class="py-3.5 px-4">Provinsi</th>
              <th class="py-3.5 px-4">Kecamatan</th>
              <th class="py-3.5 px-4 text-center w-28">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/80 text-slate-700">
            <template v-if="loading">
              <tr>
                <td colspan="5" class="py-12 text-center text-slate-400 font-medium">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <div class="w-6 h-6 border-2 border-brand-900 border-t-transparent rounded-full animate-spin"></div>
                    <span>Memuat data master area...</span>
                  </div>
                </td>
              </tr>
            </template>
            <template v-else-if="areas.length > 0">
              <tr v-for="item in areas" :key="item.id" class="hover:bg-brand-50/30 transition-colors">
                <td class="py-3.5 px-4 font-bold text-slate-900">{{ item.name }}</td>
                <td class="py-3.5 px-4 font-semibold text-slate-800">{{ item.city || '-' }}</td>
                <td class="py-3.5 px-4 text-slate-600">{{ item.province || '-' }}</td>
                <td class="py-3.5 px-4 text-slate-500">{{ item.district || '-' }}</td>
                <td class="py-3.5 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      @click="openEditModal(item)"
                      title="Edit Area"
                      class="p-1.5 rounded-lg bg-slate-100 hover:bg-brand-100 hover:text-brand-900 text-slate-600 transition-colors active:scale-90 cursor-pointer"
                    >
                      <Pencil class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="handleDelete(item)"
                      title="Hapus Area"
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
                <td colspan="5" class="py-12 text-center text-slate-400 font-medium">Belum ada area operasional.</td>
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
            {{ isEditing ? 'Edit' : 'Tambah' }} Master Area
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-3">
          <div>
            <label class="block font-bold mb-1">Nama Area Operasional *</label>
            <input
              required
              type="text"
              placeholder="Contoh: Jakarta Barat, Bandung Raya"
              v-model="formInput.name"
              class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
            />
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block font-bold mb-1">Kota / Kabupaten *</label>
              <input
                required
                type="text"
                placeholder="Kota Jakarta Barat"
                v-model="formInput.city"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
              />
            </div>
            <div>
              <label class="block font-bold mb-1">Provinsi *</label>
              <input
                required
                type="text"
                placeholder="DKI Jakarta"
                v-model="formInput.province"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
              />
            </div>
          </div>
          <div>
            <label class="block font-bold mb-1">Kecamatan (Opsional)</label>
            <input
              type="text"
              placeholder="Grogol Petamburan"
              v-model="formInput.district"
              class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
            />
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
              {{ isEditing ? 'Simpan Perubahan' : 'Simpan Area' }}
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
