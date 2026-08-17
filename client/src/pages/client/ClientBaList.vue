<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-800 to-teal-600 flex items-center justify-center text-white shadow-md shadow-emerald-900/20">
            <FileCheck2 class="w-4 h-4" />
          </div>
          <span>Dokumen Berita Acara (BA Opname) & Serah Terima</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1 font-medium">
          Pusat arsip Berita Acara resmi yang telah diverifikasi untuk kelengkapan administrasi serah terima dan penagihan invoice.
        </p>
      </div>

      <!-- Quick Summary Scorecard -->
      <div class="flex items-center gap-2 self-start sm:self-auto">
        <span class="px-3.5 py-1.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-bold text-emerald-800 shadow-xs flex items-center gap-1.5">
          <CheckCircle2 class="w-4 h-4 text-emerald-600" />
          <span>{{ baList.length }} Dokumen Resmi Terbit</span>
        </span>
      </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
      <div class="relative w-full sm:w-80">
        <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Cari nomor BA, SPK, nama cabang toko..."
          class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs"
        />
      </div>

      <div class="flex items-center gap-2 text-slate-500 font-mono text-[11px]">
        <span>Total Nilai Pengesahan:</span>
        <strong class="text-slate-900 font-bold text-xs font-mono">
          Rp {{ (totalApprovedValue / 1000000).toFixed(1) }} Juta
        </strong>
      </div>
    </div>

    <!-- Table -->
    <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100/80 text-slate-600 font-bold border-b border-slate-200/80">
            <tr>
              <th class="py-3 px-4">Nomor Dokumen BA</th>
              <th class="py-3 px-4">Nomor SPK</th>
              <th class="py-3 px-4">Nama Toko Cabang</th>
              <th class="py-3 px-4">Tanggal Terbit</th>
              <th class="py-3 px-4 text-right">Nilai Kontrak</th>
              <th class="py-3 px-4 text-center">Status Legalitas</th>
              <th class="py-3 px-4 text-center">Aksi Dokumen</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <template v-if="loading">
              <tr>
                <td colspan="7" class="py-12 text-center text-slate-400 font-medium">Memuat arsip Berita Acara...</td>
              </tr>
            </template>
            <template v-else-if="filteredBaList.length > 0">
              <tr v-for="ba in filteredBaList" :key="ba.id" class="hover:bg-purple-50/30 transition-colors">
                <td class="py-3.5 px-4 font-mono font-bold text-purple-900">
                  <div class="flex items-center gap-1.5">
                    <FileCode class="w-3.5 h-3.5 text-purple-700" />
                    <span>{{ ba.ba_number }}</span>
                  </div>
                </td>
                <td class="py-3.5 px-4 font-mono text-slate-600 font-semibold">{{ ba.spk_number }}</td>
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900">{{ ba.work_order_title }}</div>
                  <div class="text-[10px] text-slate-400 truncate">{{ ba.location_name }}</div>
                </td>
                <td class="py-3.5 px-4 font-mono text-slate-500">
                  {{ new Date(ba.ba_date || ba.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                </td>
                <td class="py-3.5 px-4 font-mono font-bold text-slate-900 text-right">
                  Rp {{ Number(ba.contract_value || 15000000).toLocaleString('id-ID') }}
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-600 text-white shadow-xs">
                    DISAHKAN ✓
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <button
                    @click="handlePreview(ba)"
                    class="px-3 py-1.5 bg-purple-900 hover:bg-purple-800 text-white font-bold rounded-xl flex items-center gap-1.5 mx-auto transition-all cursor-pointer shadow-xs active:scale-95 text-[11px]"
                  >
                    <Eye class="w-3 h-3" />
                    <span>Lihat & Cetak BA</span>
                  </button>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="7" class="py-16 text-center text-slate-400 space-y-2">
                  <FileSpreadsheet class="w-8 h-8 opacity-30 mx-auto" />
                  <p class="font-medium">Belum ada dokumen Berita Acara yang diterbitkan.</p>
                  <p class="text-[11px] text-slate-400">Dokumen BA akan otomatis terbit setelah pekerjaan toko disetujui 100% oleh Pengawas SGX.</p>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../../services/api';
import {
  FileCheck2,
  FileCode,
  FileSpreadsheet,
  Search,
  CheckCircle2,
  Eye
} from 'lucide-vue-next';

const emit = defineEmits(['preview-ba']);

const baList = ref([]);
const loading = ref(true);
const searchQuery = ref('');

async function loadBaList() {
  loading.value = true;
  try {
    const res = await api.getBaList();
    baList.value = res.data || [];
  } catch (err) {
    console.error('Failed to load BA list:', err);
  } finally {
    loading.value = false;
  }
}

const totalApprovedValue = computed(() => {
  return baList.value.reduce((sum, ba) => sum + Number(ba.contract_value || 15000000), 0);
});

const filteredBaList = computed(() => {
  if (!searchQuery.value) return baList.value;
  const q = searchQuery.value.toLowerCase();
  return baList.value.filter(ba => {
    const matchBa = ba.ba_number?.toLowerCase().includes(q);
    const matchSpk = ba.spk_number?.toLowerCase().includes(q);
    const matchTitle = ba.work_order_title?.toLowerCase().includes(q);
    const matchLoc = ba.location_name?.toLowerCase().includes(q);
    return matchBa || matchSpk || matchTitle || matchLoc;
  });
});

async function handlePreview(ba) {
  try {
    const res = await api.getBaById(ba.id);
    emit('preview-ba', res.data);
  } catch (err) {
    emit('preview-ba', ba);
  }
}

onMounted(() => {
  loadBaList();
});
</script>
