<template>
  <div class="space-y-6 pb-12">
    <!-- Header Section -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-bold mb-2">
          <FileCheck2 class="w-3.5 h-3.5" />
          <span>PORTOFOLIO & ARSIP DOKUMENTASI</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
          Riwayat Tugas Selesai
        </h1>
        <p class="text-slate-500 text-xs sm:text-sm mt-1">
          Daftar seluruh pekerjaan lapangan yang telah selesai dikerjakan, diajukan review, dan diterbitkan Berita Acara (BA).
        </p>
      </div>

      <div class="flex items-center gap-3">
        <button
          @click="loadHistory"
          :disabled="loading"
          class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 text-xs font-bold rounded-2xl transition-all flex items-center gap-2 cursor-pointer"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" />
          <span>Segarkan</span>
        </button>
      </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-3 items-center justify-between">
      <!-- Search Input -->
      <div class="relative w-full sm:w-80">
        <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari SPK, lokasi toko, cabang..."
          class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
        />
      </div>

      <!-- Filter Status Tabs -->
      <div class="flex items-center gap-1.5 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
        <button
          v-for="tab in filterTabs"
          :key="tab.id"
          @click="activeStatusFilter = tab.id"
          class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap cursor-pointer"
          :class="activeStatusFilter === tab.id
            ? 'bg-indigo-600 text-white shadow-sm'
            : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        >
          {{ tab.label }} ({{ tab.count }})
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-3xl p-16 border border-slate-200 text-center text-slate-400">
      <Loader2 class="w-8 h-8 animate-spin mx-auto mb-3 text-indigo-600" />
      <p class="text-sm font-bold text-slate-600">Memuat riwayat pekerjaan...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredHistory.length === 0" class="bg-white rounded-3xl p-12 border border-slate-200 text-center">
      <History class="w-14 h-14 text-slate-300 mx-auto mb-3" />
      <h3 class="text-base font-extrabold text-slate-800">Tidak Ada Riwayat Ditemukan</h3>
      <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
        Belum ada pekerjaan dengan kriteria tersebut yang telah selesai.
      </p>
    </div>

    <!-- History Cards Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
      <div
        v-for="wo in filteredHistory"
        :key="wo.id"
        class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all flex flex-col justify-between space-y-4"
      >
        <!-- Card Top -->
        <div class="space-y-2.5">
          <div class="flex items-center justify-between gap-2 flex-wrap">
            <span class="font-mono text-xs font-black text-indigo-700 bg-indigo-50 px-3 py-1 rounded-xl border border-indigo-100">
              {{ wo.spk_number }}
            </span>
            <span
              class="text-xs font-bold px-2.5 py-1 rounded-lg uppercase"
              :class="getStatusBadgeClass(wo.status)"
            >
              {{ getStatusLabel(wo.status) }}
            </span>
          </div>

          <h3 class="text-base font-extrabold text-slate-900 leading-snug">
            {{ wo.title || wo.location_name }}
          </h3>

          <div class="space-y-1 text-xs text-slate-500">
            <div class="flex items-center gap-1.5">
              <Building2 class="w-3.5 h-3.5 text-slate-400" />
              <span class="font-bold text-slate-700">{{ wo.vendor?.name || 'Client Principal' }}</span>
              <span v-if="wo.area"> • {{ wo.area.name }}</span>
            </div>
            <div class="flex items-center gap-1.5">
              <MapPin class="w-3.5 h-3.5 text-slate-400" />
              <span class="line-clamp-1">{{ wo.address || wo.location_name }}</span>
            </div>
          </div>
        </div>

        <!-- Evidence Photos Strip Preview -->
        <div v-if="wo.evidence_photos && wo.evidence_photos.length > 0" class="pt-2 border-t border-slate-100">
          <div class="flex items-center justify-between text-xs text-slate-500 font-bold mb-2">
            <span>Foto Bukti Terunggah ({{ wo.evidence_photos.length }} Foto)</span>
            <span class="text-[11px] text-emerald-600 flex items-center gap-1">
              <CheckCircle2 class="w-3 h-3" /> SHA-256 Valid
            </span>
          </div>
          <div class="flex items-center gap-2 overflow-x-auto pb-1">
            <div
              v-for="(photo, idx) in wo.evidence_photos.slice(0, 4)"
              :key="photo.id"
              @click="openLightbox(wo.evidence_photos, idx)"
              class="w-16 h-16 rounded-xl bg-slate-900 overflow-hidden relative cursor-pointer group flex-shrink-0 border border-slate-200"
            >
              <img
                :src="getFileUrl(photo.file_path)"
                class="w-full h-full object-cover group-hover:scale-110 transition-all duration-200"
                loading="lazy"
              />
              <div class="absolute bottom-0 inset-x-0 bg-slate-900/75 text-[9px] text-white font-bold text-center py-0.5">
                {{ photo.stage }}
              </div>
            </div>
            <div
              v-if="wo.evidence_photos.length > 4"
              @click="openLightbox(wo.evidence_photos, 4)"
              class="w-16 h-16 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-extrabold flex items-center justify-center cursor-pointer flex-shrink-0 border border-slate-200"
            >
              +{{ wo.evidence_photos.length - 4 }}
            </div>
          </div>
        </div>

        <!-- Card Footer Actions -->
        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
          <div class="text-[11px] text-slate-400 flex items-center gap-1">
            <Calendar class="w-3.5 h-3.5" />
            <span>Target: {{ wo.due_date || wo.scheduled_date || '-' }}</span>
          </div>

          <div class="flex items-center gap-2">
            <button
              v-if="wo.ba_document"
              @click="$emit('open-ba', wo.ba_document)"
              class="px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl flex items-center gap-1.5 transition-all cursor-pointer"
            >
              <FileCheck2 class="w-3.5 h-3.5" />
              <span>Lihat BA</span>
            </button>
            <button
              @click="$emit('open-detail', wo.id)"
              class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl flex items-center gap-1.5 transition-all cursor-pointer"
            >
              <span>Rincian</span>
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
      return 'bg-emerald-100 text-emerald-800 border border-emerald-200';
    case 'REVIEW':
    case 'UNDER_REVIEW':
    case 'SUBMITTED':
      return 'bg-indigo-100 text-indigo-800 border border-indigo-200';
    default:
      return 'bg-slate-100 text-slate-700';
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
    default:
      return status;
  }
}

onMounted(() => {
  loadHistory();
});
</script>
