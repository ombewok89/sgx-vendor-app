<template>
  <div class="space-y-4 font-mono">
    <!-- Header (CAD Blueprint Style) -->
    <div class="bg-[#13171F] border border-[#232A36] p-4 rounded-xs flex flex-col md:flex-row md:items-center justify-between gap-3 relative overflow-hidden">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xs bg-[#0B0C0E] border border-emerald-500/40 flex items-center justify-center text-emerald-400 shrink-0">
          <FileCheck2 class="w-5 h-5" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-base sm:text-lg font-heading font-black text-white uppercase tracking-wider">
              Dokumen Berita Acara (BA) Opname
            </h2>
            <span class="bg-emerald-950 text-emerald-400 border border-emerald-500/40 text-[9px] font-mono px-1.5 py-0.2 rounded-none font-bold uppercase">
              LEGAL CERTIFIED
            </span>
          </div>
          <p class="text-xs text-slate-400 font-mono mt-0.5">
            Daftar Berita Acara resmi yang diterbitkan untuk pekerjaan yang telah disetujui (Approved) & diverifikasi forensik.
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2 self-start md:self-auto">
        <button
          @click="loadBaList"
          class="px-3 py-1.5 bg-[#0B0C0E] hover:bg-[#181D26] border border-[#232A36] hover:border-slate-500 rounded-xs text-slate-300 text-xs font-bold flex items-center gap-2 transition-all cursor-pointer uppercase"
        >
          <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin text-hazard' : '']" />
          <span>Segarkan</span>
        </button>
      </div>
    </div>

    <!-- Search Bar & Filters (Terminal Style) -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-3 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
      <div class="relative w-full sm:w-96">
        <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-500" />
        <input
          type="text"
          placeholder=">_ CARI NOMOR BA / SPK / VENDOR..."
          v-model="search"
          class="w-full pl-8 pr-3 py-1.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-xs font-mono text-white placeholder-slate-600 focus:outline-none focus:border-hazard"
        />
      </div>

      <div class="text-[11px] text-slate-400 flex items-center gap-2 self-start sm:self-auto">
        <span>TOTAL DOKUMEN:</span>
        <span class="font-bold text-hazard bg-[#0B0C0E] px-2 py-0.5 border border-[#232A36]">
          {{ filteredList.length }} REGISTERED
        </span>
      </div>
    </div>

    <!-- BA Table (CAD High-Density Module) -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-[#0E1117] text-slate-400 font-bold border-b border-[#232A36] uppercase text-[10px] tracking-wider">
            <tr>
              <th class="py-3 px-4">Nomor BA</th>
              <th class="py-3 px-4">Nomor SPK & Pekerjaan</th>
              <th class="py-3 px-4">Mitra Vendor</th>
              <th class="py-3 px-4">Tanggal BA</th>
              <th class="py-3 px-4">Tgl Selesai</th>
              <th class="py-3 px-4">Penerbit</th>
              <th class="py-3 px-4">Status Legalitas</th>
              <th class="py-3 px-4 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1C2028] text-slate-300">
            <template v-if="loading">
              <tr>
                <td colspan="8" class="py-12 text-center text-slate-500 text-xs font-mono">
                  <div class="w-6 h-6 border-2 border-hazard border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                  <span>MEMUAT_BERITA_ACARA_OPNAME...</span>
                </td>
              </tr>
            </template>
            <template v-else-if="filteredList.length > 0">
              <tr
                v-for="ba in filteredList"
                :key="ba.id"
                class="hover:bg-[#181D26] transition-colors"
              >
                <!-- Nomor BA -->
                <td class="py-3.5 px-4 font-mono font-bold text-hazard text-xs">
                  {{ ba.ba_number }}
                </td>

                <!-- SPK & Judul -->
                <td class="py-3.5 px-4">
                  <div class="font-mono text-slate-400 text-[10px]">[ {{ ba.spk_number || '—' }} ]</div>
                  <div class="font-heading font-black text-white truncate max-w-xs mt-0.5 uppercase tracking-wide">
                    {{ ba.work_order_title || ba.location_name || '—' }}
                  </div>
                </td>

                <!-- Vendor -->
                <td class="py-3.5 px-4 font-bold text-slate-200">
                  {{ ba.vendor_name || 'SGX CLIENT' }}
                </td>

                <!-- Tanggal BA -->
                <td class="py-3.5 px-4 font-mono text-slate-400 text-[11px]">
                  {{ ba.ba_date ? new Date(ba.ba_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—' }}
                </td>

                <!-- Tanggal Selesai -->
                <td class="py-3.5 px-4 font-mono text-emerald-400 font-semibold text-[11px]">
                  {{ ba.completed_at ? new Date(ba.completed_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : (ba.deadline ? new Date(ba.deadline).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—') }}
                </td>

                <!-- Penerbit -->
                <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                  {{ ba.generator_name || 'Admin SGX' }}
                </td>

                <!-- Status Legalitas -->
                <td class="py-3.5 px-4">
                  <span class="px-2 py-0.5 rounded-none text-[9px] font-mono font-bold bg-emerald-950 text-emerald-400 border border-emerald-500/40">
                    [ FINAL / CERTIFIED ✓ ]
                  </span>
                </td>

                <!-- Aksi -->
                <td class="py-3.5 px-4 text-right">
                  <button
                    @click="handlePreview(ba.id)"
                    class="cad-btn-primary px-3 py-1.5 text-xs font-bold flex items-center gap-1.5 ml-auto cursor-pointer uppercase active:scale-95"
                  >
                    <Eye class="w-3.5 h-3.5" />
                    <span>Lihat & Cetak</span>
                  </button>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="8" class="py-12 text-center text-slate-500 text-xs font-mono border border-dashed border-[#232A36]">
                  [ BELUM ADA DOKUMEN BERITA ACARA YANG DITERBITKAN ]
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
