<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white/80 p-5 rounded-3xl border border-slate-200/80 shadow-xs">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-900 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-brand-900/20">
          <Briefcase class="w-5 h-5" />
        </div>
        <div>
          <h2 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
            <span>Master Jenis Pekerjaan & Tarif Acuan Standar</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-100 text-brand-900 font-mono">
              {{ jobTypes.length }} Jenis
            </span>
          </h2>
          <p class="text-xs text-slate-500 font-medium">Katalog pekerjaan instalasi reklame/signage, tarif acuan standar Rp, dan aturan foto evidensi.</p>
        </div>
      </div>

      <button
        @click="openAddModal"
        class="px-4 py-2.5 bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 hover:from-brand-800 hover:to-brand-600 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-md shadow-brand-900/20 active:scale-95 transition-all self-start sm:self-auto cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>Tambah Jenis Pekerjaan</span>
      </button>
    </div>

    <!-- Table Container -->
    <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100/70 text-slate-600 font-bold border-b border-slate-200/80">
            <tr>
              <th class="py-3.5 px-4 w-36">Kode Pekerjaan</th>
              <th class="py-3.5 px-4">Nama Jenis Pekerjaan</th>
              <th class="py-3.5 px-4">Tarif Acuan Standar (Rp)</th>
              <th class="py-3.5 px-4">Mode Dokumentasi</th>
              <th class="py-3.5 px-4">Min. Foto / Tahap</th>
              <th class="py-3.5 px-4 text-center w-28">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/80 text-slate-700">
            <template v-if="loading">
              <tr>
                <td colspan="6" class="py-12 text-center text-slate-400 font-medium">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <div class="w-6 h-6 border-2 border-brand-900 border-t-transparent rounded-full animate-spin"></div>
                    <span>Memuat katalog jenis pekerjaan...</span>
                  </div>
                </td>
              </tr>
            </template>
            <template v-else-if="jobTypes.length > 0">
              <tr v-for="item in jobTypes" :key="item.id" class="hover:bg-brand-50/30 transition-colors">
                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                  <span class="px-2 py-0.5 rounded-lg bg-slate-100 border border-slate-200 text-slate-800">
                    {{ item.code }}
                  </span>
                </td>
                <td class="py-3.5 px-4 font-bold text-slate-900">{{ item.name }}</td>
                <td class="py-3.5 px-4 font-mono font-bold text-emerald-700 text-sm">
                  Rp {{ Number(item.standard_price || 0).toLocaleString('id-ID') }}
                </td>
                <td class="py-3.5 px-4">
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-800 border border-brand-200">
                    {{ item.doc_mode }}
                  </span>
                </td>
                <td class="py-3.5 px-4 font-bold text-slate-800">
                  {{ item.min_photos_per_stage }} Foto
                </td>
                <td class="py-3.5 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      @click="openEditModal(item)"
                      title="Edit Jenis Pekerjaan"
                      class="p-1.5 rounded-lg bg-slate-100 hover:bg-brand-100 hover:text-brand-900 text-slate-600 transition-colors active:scale-90 cursor-pointer"
                    >
                      <Pencil class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="handleDelete(item)"
                      title="Hapus Jenis Pekerjaan"
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
                <td colspan="6" class="py-12 text-center text-slate-400 font-medium">Belum ada jenis pekerjaan.</td>
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
            {{ isEditing ? 'Edit' : 'Tambah' }} Jenis Pekerjaan
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-3">
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block font-bold mb-1">Kode Pekerjaan *</label>
              <input
                required
                type="text"
                placeholder="STK, NEON, FAC"
                v-model="formInput.code"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 font-mono uppercase"
              />
            </div>
            <div>
              <label class="block font-bold mb-1">Mode Dokumentasi *</label>
              <select
                required
                v-model="formInput.doc_mode"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-xs font-bold"
              >
                <option value="BEFORE_PROCESS_AFTER">BEFORE + PROCESS + AFTER</option>
                <option value="AFTER_ONLY">AFTER ONLY</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block font-bold mb-1">Nama Jenis Pekerjaan *</label>
            <input
              required
              type="text"
              placeholder="Contoh: Pemasangan Signboard & Reklame"
              v-model="formInput.name"
              class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
            />
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block font-bold mb-1">Tarif Acuan Standar (Rp)</label>
              <input
                type="number"
                placeholder="15000000"
                v-model="formInput.standard_price"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 font-mono"
              />
            </div>
            <div>
              <label class="block font-bold mb-1">Min. Foto Tiap Tahap</label>
              <input
                type="number"
                min="1"
                max="10"
                v-model="formInput.min_photos_per_stage"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 font-mono"
              />
            </div>
          </div>
          <div v-if="formInput.standard_price" class="text-[11px] font-mono font-bold text-emerald-700 bg-emerald-50 p-2 rounded-lg border border-emerald-200">
            Preview Tarif: Rp {{ Number(formInput.standard_price).toLocaleString('id-ID') }}
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
              {{ isEditing ? 'Simpan Perubahan' : 'Simpan Jenis Pekerjaan' }}
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
import { Briefcase, Plus, Pencil, Trash2, X } from 'lucide-vue-next';

const jobTypes = ref([]);
const loading = ref(true);
const showModal = ref(false);
const isEditing = ref(false);
const formInput = ref({});

async function loadData() {
  loading.value = true;
  try {
    const res = await api.getJobTypes();
    jobTypes.value = res.data || [];
  } catch (err) {
    console.error('Failed to load job types:', err);
  } finally {
    loading.value = false;
  }
}

function openAddModal() {
  isEditing.value = false;
  formInput.value = {
    doc_mode: 'BEFORE_PROCESS_AFTER',
    min_photos_per_stage: 3,
    standard_price: 15000000
  };
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
      await api.updateJobType(formInput.value.id, formInput.value);
      alert('Data jenis pekerjaan berhasil diperbarui!');
    } else {
      await api.createJobType(formInput.value);
      alert('Jenis pekerjaan baru berhasil ditambahkan!');
    }
    showModal.value = false;
    formInput.value = {};
    loadData();
  } catch (err) {
    alert(`Gagal menyimpan jenis pekerjaan: ${err.message}`);
  }
}

async function handleDelete(item) {
  if (!confirm(`Hapus jenis pekerjaan "${item.name}"?`)) return;
  try {
    await api.deleteJobType(item.id);
    alert('Jenis pekerjaan berhasil dihapus!');
    loadData();
  } catch (err) {
    alert(`Gagal menghapus jenis pekerjaan: ${err.message}`);
  }
}

onMounted(() => {
  loadData();
});
</script>
