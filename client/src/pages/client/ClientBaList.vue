<template>
  <div class="space-y-5 pb-12">
    <!-- Header -->
    <div class="bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 rounded-3xl p-5 sm:p-6 text-white border border-emerald-900/40 shadow-lg relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -left-10 -top-10 w-48 h-48 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <div class="inline-flex items-center gap-2 px-2.5 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-full text-[10px] font-bold mb-1.5">
            <FileCheck2 class="w-3 h-3" />
            <span>PUSAT ARSIP LEGALITAS & PENAGIHAN</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-black tracking-tight">
            Dokumen Berita Acara (BA Opname)
          </h1>
          <p class="text-slate-300 text-xs mt-0.5 max-w-xl">
            Arsip Berita Acara Serah Terima resmi yang telah diverifikasi untuk kelengkapan administrasi dan penagihan invoice.
          </p>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
          <span class="px-3 py-1.5 bg-white/10 backdrop-blur-md border border-white/10 rounded-xl text-xs font-bold text-white flex items-center gap-1.5">
            <CheckCircle2 class="w-4 h-4 text-emerald-400" />
            <span>{{ baList.length }} Dokumen Resmi Terbit</span>
          </span>
          <button
            @click="loadBaList"
            :disabled="loading"
            class="p-2 bg-emerald-700 hover:bg-emerald-600 text-white rounded-xl shadow-xs transition-all cursor-pointer active:scale-95"
            title="Segarkan Data"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
          </button>
        </div>
      </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
      <div class="relative w-full sm:w-80">
        <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Cari nomor BA, SPK, nama cabang toko..."
          class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition-all"
        />
      </div>

      <div class="flex items-center gap-2 text-slate-500 font-mono text-[11px] self-end sm:self-auto">
        <span>Total Nilai Pengesahan:</span>
        <strong class="text-slate-900 font-bold text-xs font-mono">
          Rp {{ (totalApprovedValue / 1000000).toFixed(1) }} Juta
        </strong>
      </div>
    </div>

    <!-- Mobile Card View (Visible on Screens < md) -->
    <div class="block md:hidden space-y-3">
      <div v-if="loading" class="bg-white rounded-2xl p-8 text-center text-slate-400 text-xs">
        <Loader2 class="w-6 h-6 animate-spin mx-auto mb-2 text-emerald-600" />
        <span>Memuat arsip Berita Acara...</span>
      </div>

      <div v-else-if="filteredBaList.length === 0" class="bg-white rounded-2xl p-8 text-center text-slate-400 text-xs space-y-2">
        <FileSpreadsheet class="w-8 h-8 text-slate-300 mx-auto" />
        <p class="font-bold text-slate-700">Belum ada dokumen BA</p>
        <p class="text-[11px]">Dokumen BA akan otomatis terbit saat pekerjaan disetujui 100%.</p>
      </div>

      <div
        v-else
        v-for="ba in filteredBaList"
        :key="ba.id"
        class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-sm space-y-2.5"
      >
        <div class="flex items-center justify-between">
          <span class="font-mono font-black text-xs text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-200">
            {{ ba.ba_number }}
          </span>
          <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-600 text-white shadow-xs">
            DISAHKAN ✓
          </span>
        </div>

        <div>
          <h4 class="font-bold text-xs text-slate-900">{{ ba.work_order_title || ba.location_name }}</h4>
          <p class="text-[11px] text-slate-500 font-mono mt-0.5">SPK: {{ ba.spk_number }}</p>
        </div>

        <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100 font-mono">
          <span class="text-slate-500">
            {{ new Date(ba.ba_date || ba.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}
          </span>
          <strong class="text-slate-900 font-bold">
            Rp {{ Number(ba.contract_value || 15000000).toLocaleString('id-ID') }}
          </strong>
        </div>

        <button
          @click="handlePreview(ba)"
          class="w-full py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-xs active:scale-95 text-xs"
        >
          <Eye class="w-3.5 h-3.5" />
          <span>Lihat & Cetak BA</span>
        </button>
      </div>
    </div>

    <!-- Desktop Table (Visible on Screens >= md) -->
    <div class="hidden md:block bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200/80">
            <tr>
              <th class="py-3.5 px-4">Nomor Dokumen BA</th>
              <th class="py-3.5 px-4">Nomor SPK</th>
              <th class="py-3.5 px-4">Nama Toko Cabang</th>
              <th class="py-3.5 px-4">Tanggal Terbit</th>
              <th class="py-3.5 px-4">Tgl Selesai</th>
              <th class="py-3.5 px-4 text-right">Nilai Kontrak</th>
              <th class="py-3.5 px-4 text-center">Status Legalitas</th>
              <th class="py-3.5 px-4 text-center">Aksi Dokumen</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <template v-if="loading">
              <tr>
                <td colspan="8" class="py-12 text-center text-slate-400 font-medium">
                  <Loader2 class="w-6 h-6 animate-spin mx-auto mb-2 text-emerald-600" />
                  <span>Memuat arsip Berita Acara...</span>
                </td>
              </tr>
            </template>
            <template v-else-if="filteredBaList.length > 0">
              <tr v-for="ba in filteredBaList" :key="ba.id" class="hover:bg-emerald-50/30 transition-colors">
                <td class="py-3.5 px-4 font-mono font-bold text-emerald-900">
                  <div class="flex items-center gap-1.5">
                    <FileCode class="w-3.5 h-3.5 text-emerald-700" />
                    <span>{{ ba.ba_number }}</span>
                  </div>
                </td>
                <td class="py-3.5 px-4 font-mono text-slate-700 font-semibold text-[11px]">
                  {{ ba.spk_number || '—' }}
                </td>
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900">{{ ba.work_order_title || ba.location_name || '—' }}</div>
                  <div class="text-[10px] text-slate-400 truncate">{{ ba.location_name || ba.area_name || '' }}</div>
                </td>
                <td class="py-3.5 px-4 font-mono text-slate-500 text-[11px]">
                  {{ ba.ba_date ? new Date(ba.ba_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—' }}
                </td>
                <td class="py-3.5 px-4 font-mono text-emerald-700 font-semibold text-[11px]">
                  {{ ba.completed_at ? new Date(ba.completed_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : (ba.deadline ? new Date(ba.deadline).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—') }}
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
                    class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl flex items-center gap-1.5 mx-auto transition-all cursor-pointer shadow-xs active:scale-95 text-[11px]"
                  >
                    <Eye class="w-3 h-3" />
                    <span>Lihat & Cetak BA</span>
                  </button>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="8" class="py-16 text-center text-slate-400 space-y-2">
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
  Eye,
  RefreshCw,
  Loader2
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
