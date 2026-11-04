<template>
  <div class="space-y-4 sm:space-y-5 pb-12 font-sans text-slate-100">
    <!-- 1. Header CAD Blueprint Banner -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-xl relative overflow-hidden font-mono">
      <div class="relative z-10">
        <div class="flex items-center gap-2 text-[10px] text-slate-400 uppercase tracking-wider mb-1.5">
          <span>PORTAL</span>
          <span class="text-slate-600">//</span>
          <span>OPERATOR LAPANGAN</span>
          <span class="text-slate-600">//</span>
          <span class="text-[#FACC15] font-bold">ARSIP RIWAYAT PEKERJAAN</span>
        </div>
        <h1 class="text-lg sm:text-2xl font-black text-white tracking-wider flex items-center gap-2.5 uppercase">
          <div class="w-8 h-8 rounded-xs bg-[#0B0C0E] border border-[#FACC15] flex items-center justify-center text-[#FACC15] shrink-0">
            <History class="w-4 h-4" />
          </div>
          <span>RIWAYAT TUGAS & BERITA ACARA</span>
        </h1>
        <p class="text-xs text-slate-400 mt-1 max-w-xl font-sans">
          Arsip dokumentasi pekerjaan lapangan yang telah selesai, verifikasi audit QA forensik, dan sertifikasi Berita Acara (BA) resmi.
        </p>
      </div>

      <div class="flex items-center gap-2.5 self-start md:self-center shrink-0">
        <button
          @click="loadHistory"
          :disabled="loading"
          class="px-3.5 py-2 bg-[#1C2028] hover:bg-[#232A36] active:scale-95 text-slate-200 hover:text-white border border-[#232A36] text-xs font-bold rounded-xs transition-colors flex items-center gap-2 cursor-pointer uppercase tracking-wider disabled:opacity-50"
          title="Segarkan Riwayat Tugas"
        >
          <RefreshCw class="w-3.5 h-3.5 text-[#FACC15]" :class="{ 'animate-spin': loading }" />
          <span>Segarkan</span>
        </button>
      </div>
    </div>

    <!-- 2. Search & Filter Bar -->
    <div class="bg-[#13171F] p-3 sm:p-3.5 rounded-xs border border-[#232A36] shadow-md flex flex-col sm:flex-row gap-3 items-center justify-between font-mono">
      <!-- Search Input -->
      <div class="relative w-full sm:w-84">
        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari No. SPK, cabang, lokasi toko..."
          class="w-full pl-9 pr-3 py-1.5 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] text-white rounded-xs text-xs focus:outline-none placeholder:text-slate-500 transition-colors"
        />
      </div>

      <!-- Filter Status Tabs -->
      <div class="flex items-center gap-1.5 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
        <button
          v-for="tab in filterTabs"
          :key="tab.id"
          @click="activeStatusFilter = tab.id"
          class="px-3 py-1.5 rounded-xs text-xs font-bold transition-all whitespace-nowrap cursor-pointer uppercase tracking-wider flex items-center gap-1.5"
          :class="activeStatusFilter === tab.id
            ? 'bg-[#1C2028] text-[#FACC15] border-l-2 border-[#FACC15] border-y border-r border-[#232A36] shadow-xs'
            : 'bg-[#0B0C0E] text-slate-400 hover:text-white border border-[#232A36]'"
        >
          <span>{{ tab.label }}</span>
          <span
            class="px-1.5 py-0.2 rounded-xs text-[10px] font-bold"
            :class="activeStatusFilter === tab.id ? 'bg-[#FACC15] text-[#0B0C0E]' : 'bg-[#1C2028] text-slate-300'"
          >
            {{ tab.count }}
          </span>
        </button>
      </div>
    </div>

    <!-- 3. Loading State -->
    <div v-if="loading" class="bg-[#13171F] rounded-xs p-16 border border-[#232A36] text-center text-slate-400 font-mono shadow-md">
      <Loader2 class="w-8 h-8 animate-spin mx-auto mb-3 text-[#FACC15]" />
      <p class="text-xs font-bold uppercase tracking-wider">Memuat riwayat pekerjaan...</p>
    </div>

    <!-- 4. Empty State -->
    <div v-else-if="filteredHistory.length === 0" class="bg-[#13171F] rounded-xs p-12 border border-[#232A36] text-center font-mono shadow-md space-y-2">
      <History class="w-12 h-12 text-slate-500 mx-auto mb-2" />
      <h3 class="text-sm font-bold text-white uppercase tracking-wider">TIDAK ADA RIWAYAT PEKERJAAN DITEMUKAN</h3>
      <p class="text-xs text-slate-400 max-w-sm mx-auto font-sans">
        Belum ada pekerjaan dengan kriteria filter tersebut yang telah berstatus selesai atau diajukan review.
      </p>
    </div>

    <!-- 5. History Cards Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="(wo, index) in filteredHistory"
        :key="wo.id"
        class="bg-[#13171F] rounded-xs border border-[#232A36] hover:border-[#FACC15]/40 p-4 sm:p-5 shadow-md transition-all flex flex-col justify-between space-y-3.5 group"
      >
        <!-- Card Top: Number, SPK badge, status badge -->
        <div class="space-y-2.5">
          <div class="flex items-center justify-between gap-2 flex-wrap font-mono">
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-bold text-slate-400 bg-[#0B0C0E] px-2 py-0.5 rounded-xs border border-[#232A36]">
                #{{ index + 1 }}
              </span>
              <span class="text-xs font-black text-[#FACC15] bg-[#0B0C0E] px-2.5 py-0.5 rounded-xs border border-[#FACC15]/30">
                {{ wo.spk_number }}
              </span>
            </div>
            <span
              class="text-[10px] font-bold px-2 py-0.5 rounded-xs uppercase tracking-wider font-mono"
              :class="getStatusBadgeClass(wo.status)"
            >
              {{ getStatusLabel(wo.status) }}
            </span>
          </div>

          <!-- Title & Client/Location details -->
          <div>
            <h3 class="text-sm sm:text-base font-bold text-white group-hover:text-[#FACC15] transition-colors leading-snug">
              {{ wo.title || wo.location_name }}
            </h3>

            <div class="space-y-1 text-xs text-slate-400 mt-1.5 font-mono">
              <div class="flex items-center gap-1.5">
                <Building2 class="w-3.5 h-3.5 text-slate-500 shrink-0" />
                <span class="text-slate-200 font-bold truncate">{{ wo.vendor?.name || 'Client Principal' }}</span>
                <span v-if="wo.area" class="text-slate-500">• {{ wo.area.name }}</span>
              </div>
              <div class="flex items-center gap-1.5">
                <MapPin class="w-3.5 h-3.5 text-slate-500 shrink-0" />
                <span class="truncate">{{ wo.address || wo.location_name }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Evidence Photos Strip Preview -->
        <div v-if="wo.evidence_photos && wo.evidence_photos.length > 0" class="pt-2 border-t border-[#232A36]">
          <div class="flex items-center justify-between text-xs font-mono mb-2">
            <span class="text-slate-400 text-[11px] uppercase tracking-wider font-bold">
              Foto Bukti ({{ wo.evidence_photos.length }} Foto)
            </span>
            <span class="text-[10px] text-emerald-400 flex items-center gap-1 font-bold">
              <CheckCircle2 class="w-3 h-3 text-emerald-400" />
              <span>SHA-256 VALID</span>
            </span>
          </div>

          <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar">
            <div
              v-for="(photo, idx) in wo.evidence_photos.slice(0, 4)"
              :key="photo.id"
              @click="openLightbox(wo.evidence_photos, idx)"
              class="w-16 h-16 rounded-xs bg-[#0B0C0E] overflow-hidden relative cursor-pointer group/img shrink-0 border border-[#232A36] hover:border-[#FACC15] transition-colors"
              title="Klik untuk memperbesar foto"
            >
              <img
                :src="getFileUrl(photo.file_path)"
                class="w-full h-full object-cover group-hover/img:scale-110 transition-transform duration-200"
                loading="lazy"
              />
              <div class="absolute bottom-0 inset-x-0 bg-[#0B0C0E]/85 text-[8px] font-mono text-slate-200 font-bold text-center py-0.5 uppercase tracking-wider">
                {{ photo.stage }}
              </div>
            </div>

            <!-- More photos count button -->
            <div
              v-if="wo.evidence_photos.length > 4"
              @click="openLightbox(wo.evidence_photos, 4)"
              class="w-16 h-16 rounded-xs bg-[#0B0C0E] hover:bg-[#1C2028] text-[#FACC15] text-xs font-mono font-bold flex items-center justify-center cursor-pointer shrink-0 border border-[#232A36] hover:border-[#FACC15] transition-colors"
              title="Lihat foto lainnya"
            >
              +{{ wo.evidence_photos.length - 4 }}
            </div>
          </div>
        </div>

        <!-- Card Footer Actions -->
        <div class="pt-3 border-t border-[#232A36] flex items-center justify-between gap-2 font-mono">
          <div class="text-[11px] text-slate-400 flex items-center gap-1.5">
            <Calendar class="w-3.5 h-3.5 text-slate-500" />
            <span class="truncate">Target: {{ wo.deadline || wo.due_date || wo.scheduled_date || '-' }}</span>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <!-- BA Document Action -->
            <button
              v-if="wo.ba_document"
              @click="$emit('open-ba', wo.ba_document)"
              class="px-2.5 py-1.5 bg-[#10B981]/15 hover:bg-[#10B981]/25 text-emerald-400 border border-emerald-500/40 font-bold text-xs rounded-xs flex items-center gap-1.5 transition-colors cursor-pointer active:scale-95 uppercase tracking-wider"
              title="Lihat Lembar Cetak Berita Acara"
            >
              <FileCheck2 class="w-3.5 h-3.5" />
              <span>LIHAT BA</span>
            </button>

            <!-- SPK Detail Action -->
            <button
              @click="$emit('open-detail', wo.id)"
              class="px-3 py-1.5 bg-[#1C2028] hover:bg-[#232A36] text-white hover:text-[#FACC15] border border-[#232A36] hover:border-[#FACC15]/40 font-bold text-xs rounded-xs flex items-center gap-1.5 transition-colors cursor-pointer active:scale-95 uppercase tracking-wider"
              title="Buka Rincian SPK"
            >
              <span>RINCIAN</span>
              <ExternalLink class="w-3 h-3" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reusable Photo Lightbox Modal -->
    <PhotoLightboxModal
      :isOpen="isLightboxOpen"
      :photos="selectedPhotos"
      :initialIndex="selectedPhotoIndex"
      @close="isLightboxOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api, getFileUrl } from '../../services/api';
import PhotoLightboxModal from '../../components/PhotoLightboxModal.vue';
import {
  FileCheck2,
  RefreshCw,
  Search,
  History,
  Building2,
  MapPin,
  CheckCircle2,
  Calendar,
  ExternalLink,
  Loader2
} from 'lucide-vue-next';

defineEmits(['open-detail', 'open-ba']);

const loading = ref(false);
const workOrders = ref([]);
const searchQuery = ref('');
const activeStatusFilter = ref('ALL');

// Lightbox state
const isLightboxOpen = ref(false);
const selectedPhotos = ref([]);
const selectedPhotoIndex = ref(0);

const finishedStatuses = ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW', 'APPROVED', 'COMPLETED', 'BA_OPNAME'];

const historyTasks = computed(() => {
  return workOrders.value.filter(w => finishedStatuses.includes(w.status));
});

const filterTabs = computed(() => [
  { id: 'ALL', label: 'Semua Selesai', count: historyTasks.value.length },
  { id: 'REVIEW', label: 'Review Admin', count: historyTasks.value.filter(w => ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'].includes(w.status)).length },
  { id: 'APPROVED', label: 'Disetujui / BA', count: historyTasks.value.filter(w => ['APPROVED', 'COMPLETED', 'BA_OPNAME'].includes(w.status)).length }
]);

const filteredHistory = computed(() => {
  let list = historyTasks.value;

  if (activeStatusFilter.value === 'REVIEW') {
    list = list.filter(w => ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'].includes(w.status));
  } else if (activeStatusFilter.value === 'APPROVED') {
    list = list.filter(w => ['APPROVED', 'COMPLETED', 'BA_OPNAME'].includes(w.status));
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(w =>
      (w.spk_number && w.spk_number.toLowerCase().includes(q)) ||
      (w.title && w.title.toLowerCase().includes(q)) ||
      (w.location_name && w.location_name.toLowerCase().includes(q)) ||
      (w.address && w.address.toLowerCase().includes(q))
    );
  }

  return list;
});

async function loadHistory() {
  loading.value = true;
  try {
    const res = await api.getWorkOrders();
    if (res.data) {
      workOrders.value = res.data;
    }
  } catch (err) {
    console.error('Failed to load history tasks:', err);
  } finally {
    loading.value = false;
  }
}

function openLightbox(photos, index) {
  selectedPhotos.value = photos;
  selectedPhotoIndex.value = index;
  isLightboxOpen.value = true;
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'APPROVED':
    case 'COMPLETED':
    case 'BA_OPNAME':
      return 'bg-emerald-950/60 text-emerald-300 border border-emerald-500/40';
    case 'REVIEW':
    case 'UNDER_REVIEW':
    case 'SUBMITTED':
      return 'bg-purple-950/60 text-purple-300 border border-purple-500/40';
    case 'REVISION':
      return 'bg-rose-950/60 text-rose-300 border border-rose-500/40';
    case 'IN_PROGRESS':
      return 'bg-amber-950/60 text-[#FACC15] border border-[#FACC15]/40';
    default:
      return 'bg-slate-900 text-slate-300 border border-[#232A36]';
  }
}

function getStatusLabel(status) {
  switch (status) {
    case 'SUBMITTED':
    case 'UNDER_REVIEW':
    case 'REVIEW':
      return 'Menunggu Review';
    case 'APPROVED':
      return 'Disetujui Admin';
    case 'BA_OPNAME':
      return 'BA Diterbitkan';
    case 'COMPLETED':
      return 'Selesai 100%';
    case 'REVISION':
      return 'Perlu Revisi';
    case 'IN_PROGRESS':
      return 'Sedang Dikerjakan';
    default:
      return status;
  }
}

onMounted(() => {
  loadHistory();
});
</script>
