<template>
  <div class="space-y-5">
    <!-- Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-teal-600 to-emerald-500 flex items-center justify-center text-white shadow-md shadow-teal-600/20">
            <FileCheck2 class="w-4 h-4" />
          </div>
          <span>Dokumen Berita Acara (BA) Opname</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1 font-medium">Daftar Berita Acara resmi yang diterbitkan untuk pekerjaan yang telah disetujui.</p>
      </div>
      <button
        @click="loadBaList"
        class="px-3.5 py-2 glass-card hover:bg-white rounded-xl text-slate-700 hover:text-slate-900 text-xs font-bold flex items-center gap-2 shadow-xs transition-all duration-200 active:scale-95 border border-slate-200/80 self-start sm:self-auto"
      >
        <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin' : '']" />
        <span>Refresh</span>
      </button>
    </div>

    <!-- Search Bar (Glassmorphic) -->
    <div class="glass-card rounded-2xl p-4 shadow-glass border border-white/80">
      <div class="relative">
        <Search class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
        <input
          type="text"
          placeholder="Cari Nomor BA / Nomor SPK / Vendor..."
          v-model="search"
          class="w-full pl-9 pr-3 py-2.5 bg-white/80 border border-slate-200/80 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all shadow-xs"
        />
      </div>
    </div>

    <!-- BA Table (Glassmorphic Container) -->
    <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100/70 text-slate-500 font-bold border-b border-slate-200/80">
            <tr>
              <th class="py-3 px-4">Nomor BA</th>
              <th class="py-3 px-4">Nomor SPK & Pekerjaan</th>
              <th class="py-3 px-4">Mitra Vendor</th>
              <th class="py-3 px-4">Tanggal BA</th>
              <th class="py-3 px-4">Diterbitkan Oleh</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/80 text-slate-700">
            <template v-if="loading">
              <tr>
                <td colspan="7" class="py-10 text-center text-slate-400 font-medium">
                  Memuat daftar Berita Acara Opname...
                </td>
              </tr>
            </template>
            <template v-else-if="filteredList.length > 0">
              <tr
                v-for="ba in filteredList"
                :key="ba.id"
                class="hover:bg-teal-50/30 transition-colors"
              >
                <td class="py-3.5 px-4 font-mono font-bold text-teal-900">{{ ba.ba_number }}</td>
                <td class="py-3.5 px-4">
                  <div class="font-mono text-slate-500 text-[11px]">{{ ba.spk_number }}</div>
                  <div class="font-bold text-slate-900 truncate max-w-xs mt-0.5">{{ ba.work_order_title }}</div>
                </td>
                <td class="py-3.5 px-4 font-bold text-slate-800">{{ ba.vendor_name }}</td>
                <td class="py-3.5 px-4 font-mono text-slate-600">{{ ba.ba_date }}</td>
                <td class="py-3.5 px-4 font-medium">{{ ba.generator_name || 'Admin SGX' }}</td>
                <td class="py-3.5 px-4">
                  <span class="px-3 py-1 rounded-full text-xs font-bold bg-teal-500/10 text-teal-700 border border-teal-300">
                    FINAL / CERTIFIED ✓
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right">
                  <button
                    @click="handlePreview(ba.id)"
                    class="px-3.5 py-1.5 bg-gradient-to-r from-brand-900 to-brand-700 hover:from-brand-800 hover:to-brand-600 text-white rounded-xl font-bold text-xs transition-all shadow-xs flex items-center gap-1.5 ml-auto active:scale-95"
                  >
                    <Eye class="w-3.5 h-3.5" />
                    <span>Lihat & Cetak</span>
                  </button>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="7" class="py-10 text-center text-slate-400 font-medium">
                  Belum ada dokumen Berita Acara yang diterbitkan.
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
import { FileCheck2, Eye, Search, RefreshCw } from 'lucide-vue-next';

const emit = defineEmits(['preview-ba']);

const baList = ref([]);
const loading = ref(true);
const search = ref('');

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

const filteredList = computed(() => {
  const q = search.value.toLowerCase();
  return baList.value.filter(b =>
    (b.ba_number?.toLowerCase().includes(q)) ||
    (b.spk_number?.toLowerCase().includes(q)) ||
    (b.work_order_title?.toLowerCase().includes(q)) ||
    (b.vendor_name?.toLowerCase().includes(q))
  );
});

async function handlePreview(id) {
  try {
    const detail = await api.getBaById(id);
    emit('preview-ba', detail.data);
  } catch (err) {
    alert(err.message);
  }
}

onMounted(() => {
  loadBaList();
});
</script>
