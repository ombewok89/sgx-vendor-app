<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-[10000] bg-slate-950/95 backdrop-blur-md flex flex-col justify-between overflow-hidden animate-fade-in select-none"
      @keydown.esc="close"
      @keydown.left="prevPhoto"
      @keydown.right="nextPhoto"
      tabindex="0"
      ref="lightboxRef"
    >
      <!-- Top Action Bar -->
      <div class="p-4 sm:px-6 flex items-center justify-between border-b border-white/10 bg-slate-900/60 backdrop-blur-md z-20 shrink-0">
        <!-- Photo Title & Stage -->
        <div class="flex items-center gap-3">
          <span :class="['px-2.5 py-1 rounded-lg font-bold text-xs border uppercase tracking-wider', getStageBadge(currentPhoto?.stage)]">
            {{ currentPhoto?.stage || 'EVIDENCE' }}
          </span>
          <div>
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
              <span>{{ currentPhoto?.work_order_title || currentPhoto?.item_name || 'Dokumentasi Foto Evidensi' }}</span>
              <span v-if="currentPhoto?.spk_number" class="text-xs font-mono text-purple-300 font-normal">
                ({{ currentPhoto.spk_number }})
              </span>
            </h3>
            <p class="text-[11px] text-slate-400 font-mono">
              Foto {{ currentIndex + 1 }} dari {{ photoList.length }}
            </p>
          </div>
        </div>

        <!-- Actions: Zoom, Download, Close -->
        <div class="flex items-center gap-2">
          <!-- Zoom Out -->
          <button
            @click="zoom = Math.max(0.5, zoom - 0.25)"
            class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-all active:scale-95 cursor-pointer"
            title="Perkecil (Zoom Out)"
          >
            <ZoomOut class="w-4 h-4" />
          </button>

          <!-- Zoom Reset -->
          <button
            @click="zoom = 1"
            class="px-2.5 py-1 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-mono font-bold transition-all cursor-pointer"
            title="Reset Ukuran"
          >
            {{ Math.round(zoom * 100) }}%
          </button>

          <!-- Zoom In -->
          <button
            @click="zoom = Math.min(3, zoom + 0.25)"
            class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition-all active:scale-95 cursor-pointer"
            title="Perbesar (Zoom In)"
          >
            <ZoomIn class="w-4 h-4" />
          </button>

          <div class="w-px h-6 bg-white/20 mx-1" />

          <!-- Download Button -->
          <button
            @click="handleDownload(currentPhoto)"
            class="px-3.5 py-2 bg-gradient-to-r from-purple-800 to-indigo-600 hover:from-purple-700 hover:to-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg flex items-center gap-1.5 active:scale-95 transition-all cursor-pointer"
            title="Unduh Foto Resolusi Asli"
          >
            <Download class="w-4 h-4" />
            <span class="hidden sm:inline">Unduh Foto</span>
          </button>

          <!-- Close Button -->
          <button
            @click="close"
            class="p-2 rounded-xl bg-white/10 hover:bg-rose-600/80 text-white transition-all active:scale-95 cursor-pointer ml-1"
            title="Tutup (ESC)"
          >
            <X class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Main Photo Canvas with Nav Arrows -->
      <div class="relative flex-1 flex items-center justify-center p-4 overflow-hidden min-h-0">
        <!-- Previous Photo Button -->
        <button
          v-if="photoList.length > 1"
          @click="prevPhoto"
          class="absolute left-4 z-20 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-purple-900 border border-white/20 text-white flex items-center justify-center shadow-2xl backdrop-blur-md transition-all active:scale-90 hover:scale-110 cursor-pointer"
          title="Foto Sebelumnya (Panah Kiri)"
        >
          <ChevronLeft class="w-6 h-6" />
        </button>

        <!-- Image with Zoom Transition -->
        <div class="w-full h-full flex items-center justify-center overflow-auto p-2">
          <img
            v-if="currentPhoto?.file_path"
            :src="getFileUrl(currentPhoto.file_path)"
            :alt="currentPhoto.file_name || 'Evidence Photo'"
            :style="{ transform: `scale(${zoom})`, transformOrigin: 'center center' }"
            class="max-w-full max-h-full object-contain rounded-xl shadow-2xl transition-transform duration-200 pointer-events-auto select-none"
            loading="eager"
          />
        </div>

        <!-- Next Photo Button -->
        <button
          v-if="photoList.length > 1"
          @click="nextPhoto"
          class="absolute right-4 z-20 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-purple-900 border border-white/20 text-white flex items-center justify-center shadow-2xl backdrop-blur-md transition-all active:scale-90 hover:scale-110 cursor-pointer"
          title="Foto Berikutnya (Panah Kanan)"
        >
          <ChevronRight class="w-6 h-6" />
        </button>
      </div>

      <!-- Bottom Forensic Metadata Bar -->
      <div class="p-4 sm:px-6 bg-slate-900/80 border-t border-white/10 backdrop-blur-md z-20 shrink-0 text-white text-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 max-w-7xl mx-auto">
          <!-- Left Forensic Details -->
          <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-[11px] text-slate-300">
            <!-- Timestamp -->
            <span class="flex items-center gap-1.5 font-mono">
              <Clock class="w-3.5 h-3.5 text-purple-400 shrink-0" />
              <span>{{ formatDateTime(currentPhoto?.server_timestamp || currentPhoto?.created_at) }}</span>
            </span>

            <!-- GPS Coordinates -->
            <span v-if="currentPhoto?.latitude" class="flex items-center gap-1.5 font-mono text-emerald-400">
              <MapPin class="w-3.5 h-3.5 shrink-0" />
              <span>GPS: {{ Number(currentPhoto.latitude).toFixed(5) }}, {{ Number(currentPhoto.longitude).toFixed(5) }} (±{{ currentPhoto.accuracy || 10 }}m)</span>
            </span>

            <!-- SHA-256 Integrity -->
            <span class="flex items-center gap-1 font-mono text-cyan-300 bg-cyan-950/80 px-2 py-0.5 rounded border border-cyan-500/30">
              <ShieldCheck class="w-3.5 h-3.5 text-cyan-400 shrink-0" />
              <span class="truncate max-w-[180px]">SHA-256: {{ currentPhoto?.file_hash?.substring(0, 16) || 'Verifikasi Terdaftar' }}...</span>
            </span>

            <!-- Uploader -->
            <span v-if="currentPhoto?.uploader_name" class="text-slate-400">
              Oleh: <strong class="text-slate-200">{{ currentPhoto.uploader_name }}</strong>
            </span>
          </div>

          <!-- Right Action: Open in Maps -->
          <div v-if="currentPhoto?.latitude" class="flex items-center gap-2">
            <a
              :href="`https://www.google.com/maps?q=${currentPhoto.latitude},${currentPhoto.longitude}`"
              target="_blank"
              rel="noopener noreferrer"
              class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-[11px] font-bold flex items-center gap-1.5 transition-all"
            >
              <ExternalLink class="w-3.5 h-3.5 text-purple-300" />
              <span>Buka di Google Maps</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import {
  X,
  ZoomIn,
  ZoomOut,
  Download,
  ChevronLeft,
  ChevronRight,
  Clock,
  MapPin,
  ShieldCheck,
  ExternalLink
} from 'lucide-vue-next';
import { getFileUrl } from '../services/api';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  photos: {
    type: Array,
    default: () => []
  },
  initialIndex: {
    type: Number,
    default: 0
  }
});

const emit = defineEmits(['close']);

const currentIndex = ref(props.initialIndex || 0);
const zoom = ref(1);
const lightboxRef = ref(null);

const photoList = computed(() => {
  return props.photos || [];
});

const currentPhoto = computed(() => {
  if (photoList.value.length === 0) return null;
  return photoList.value[currentIndex.value] || photoList.value[0];
});

function close() {
  zoom.value = 1;
  emit('close');
}

function nextPhoto() {
  zoom.value = 1;
  if (currentIndex.value < photoList.value.length - 1) {
    currentIndex.value++;
  } else {
    currentIndex.value = 0; // Loop around
  }
}

function prevPhoto() {
  zoom.value = 1;
  if (currentIndex.value > 0) {
    currentIndex.value--;
  } else {
    currentIndex.value = photoList.value.length - 1; // Loop around
  }
}

function getStageBadge(stage) {
  switch (stage) {
    case 'BEFORE':
      return 'bg-amber-500/20 text-amber-300 border-amber-500/40';
    case 'PROCESS':
      return 'bg-blue-500/20 text-blue-300 border-blue-500/40';
    case 'AFTER':
      return 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40';
    case 'ISSUE':
      return 'bg-rose-500/20 text-rose-300 border-rose-500/40';
    default:
      return 'bg-purple-500/20 text-purple-300 border-purple-500/40';
  }
}

function formatDateTime(dateStr) {
  if (!dateStr) return 'Waktu tercatat di server';
  return new Date(dateStr).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  }) + ' WIB';
}

function handleDownload(photo) {
  if (!photo?.file_path) return;
  const link = document.createElement('a');
  link.href = getFileUrl(photo.file_path);
  link.target = '_blank';
  const ext = photo.file_name?.split('.').pop() || 'jpg';
  const spk = photo.spk_number ? `${photo.spk_number}_` : '';
  link.download = `${spk}${photo.stage || 'EVIDENCE'}_${photo.sequence || currentIndex.value + 1}.${ext}`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

watch(() => props.initialIndex, (val) => {
  currentIndex.value = val || 0;
  zoom.value = 1;
});

watch(() => props.isOpen, (open) => {
  if (open) {
    zoom.value = 1;
    nextTick(() => {
      if (lightboxRef.value) {
        lightboxRef.value.focus();
      }
    });
  }
});
</script>
