<template>
  <div class="space-y-4 font-mono">
    <!-- Header (CAD Blueprint Style) -->
    <div class="bg-[#13171F] border border-[#232A36] p-4 rounded-xs flex flex-col md:flex-row md:items-center justify-between gap-3 relative overflow-hidden">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xs bg-[#0B0C0E] border border-hazard/40 flex items-center justify-center text-hazard shrink-0">
          <Briefcase class="w-5 h-5" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-base sm:text-lg font-heading font-black text-white uppercase tracking-wider">
              Master Jenis Pekerjaan & Tarif Acuan
            </h2>
            <span class="bg-hazard/10 text-hazard border border-hazard/30 text-[9px] font-mono px-1.5 py-0.2 rounded-none font-bold uppercase">
              {{ jobTypes.length }} KATALOG
            </span>
          </div>
          <p class="text-xs text-slate-400 font-mono mt-0.5">
            Katalog pekerjaan instalasi reklame/signage, tarif acuan standar Rp, dan aturan foto evidensi lapangan.
          </p>
        </div>
      </div>

      <button
        @click="openAddModal"
        class="cad-btn-primary px-4 py-2 text-xs font-bold flex items-center gap-2 cursor-pointer uppercase active:scale-95 transition-all self-start sm:self-auto"
      >
        <Plus class="w-4 h-4" />
        <span>Tambah Jenis Pekerjaan</span>
      </button>
    </div>

    <!-- Table Container (CAD High Density) -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs font-mono">
          <thead class="bg-[#0E1117] text-slate-400 font-bold border-b border-[#232A36] uppercase text-[10px] tracking-wider">
            <tr>
              <th class="py-3 px-4 w-36">Kode Pekerjaan</th>
              <th class="py-3 px-4">Nama Jenis Pekerjaan</th>
              <th class="py-3 px-4 text-right">Tarif Acuan Standar (Rp)</th>
              <th class="py-3 px-4">Mode Dokumentasi</th>
              <th class="py-3 px-4 text-center">Min. Foto / Tahap</th>
              <th class="py-3 px-4 text-center w-28">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1C2028] text-slate-300">
            <template v-if="loading">
              <tr>
                <td colspan="6" class="py-12 text-center text-slate-500 font-medium">
                  <div class="w-6 h-6 border-2 border-hazard border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                  <span>MEMUAT_KATALOG_JENIS_PEKERJAAN...</span>
                </td>
              </tr>
            </template>
            <template v-else-if="jobTypes.length > 0">
              <tr v-for="item in jobTypes" :key="item.id" class="hover:bg-[#181D26] transition-colors">
                <td class="py-3.5 px-4 font-mono font-bold text-hazard text-xs">
                  <span class="px-1.5 py-0.5 rounded-none bg-[#0B0C0E] border border-[#232A36]">
                    {{ item.code }}
                  </span>
                </td>
                <td class="py-3.5 px-4 font-heading font-black text-white uppercase text-xs">
                  {{ item.name }}
                </td>
                <td class="py-3.5 px-4 font-mono font-bold text-emerald-400 text-right text-xs">
                  Rp {{ Number(item.standard_price || 0).toLocaleString('id-ID') }}
                </td>
                <td class="py-3.5 px-4">
                  <span class="px-2 py-0.5 rounded-none text-[9px] font-bold bg-[#0B0C0E] text-slate-300 border border-[#232A36] uppercase">
                    {{ item.doc_mode }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center font-bold text-slate-200">
                  {{ item.min_photos_per_stage }} FOTO
                </td>
                <td class="py-3.5 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      @click="openEditModal(item)"
                      title="Edit Jenis Pekerjaan"
                      class="p-1 rounded-none bg-[#0B0C0E] hover:bg-[#181D26] hover:text-white text-slate-400 border border-[#232A36] transition-colors cursor-pointer"
                    >
                      <Pencil class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="handleDelete(item)"
                      title="Hapus Jenis Pekerjaan"
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
                <td colspan="6" class="py-12 text-center text-slate-500 font-medium border border-dashed border-[#232A36]">
                  [ BELUM ADA JENIS PEKERJAAN TERDAFTAR ]
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
              {{ isEditing ? 'Edit' : 'Tambah' }} Jenis Pekerjaan
            </h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-white p-1">
              <X class="w-4 h-4" />
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-3">
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Kode Pekerjaan *</label>
                <input
                  required
                  type="text"
                  placeholder="STK, NEON, FAC"
                  v-model="formInput.code"
                  class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard font-mono uppercase text-xs"
                />
              </div>
              <div>
                <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Mode Dokumentasi *</label>
                <select
                  required
                  v-model="formInput.doc_mode"
                  class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard text-xs font-mono"
                >
                  <option value="BEFORE_PROCESS_AFTER">BEFORE + PROCESS + AFTER</option>
                  <option value="AFTER_ONLY">AFTER ONLY</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Nama Jenis Pekerjaan *</label>
              <input
                required
                type="text"
                placeholder="Contoh: Pemasangan Signboard & Reklame"
                v-model="formInput.name"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard text-xs"
              />
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Tarif Acuan (Rp)</label>
                <input
                  type="number"
                  placeholder="15000000"
                  v-model="formInput.standard_price"
                  class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard font-mono text-xs"
                />
              </div>
              <div>
                <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Min. Foto / Tahap</label>
                <input
                  type="number"
                  min="1"
                  max="10"
                  v-model="formInput.min_photos_per_stage"
                  class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard font-mono text-xs"
                />
              </div>
            </div>
            <div v-if="formInput.standard_price" class="text-[10px] font-mono font-bold text-emerald-400 bg-[#0B0C0E] p-2 rounded-none border border-emerald-500/40">
              PREVIEW TARIF: Rp {{ Number(formInput.standard_price).toLocaleString('id-ID') }}
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
                {{ isEditing ? 'SIMPAN PERUBAHAN' : 'SIMPAN JENIS PEKERJAAN' }}
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
