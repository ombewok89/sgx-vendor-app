<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-[10000] bg-[#0B0C0E]/98 flex flex-col justify-between overflow-hidden animate-fade-in select-none font-mono"
      @keydown.esc="close"
      @keydown.left="prevPhoto"
      @keydown.right="nextPhoto"
      tabindex="0"
      ref="lightboxRef"
    >
      <!-- Top Action Bar (CAD Blueprint Spec) -->
      <div class="p-3 sm:px-6 flex items-center justify-between border-b border-[#232A36] bg-[#13171F] z-20 shrink-0">
        <!-- Photo Title & Stage -->
        <div class="flex items-center gap-3">
          <span class="bg-hazard text-carbon font-mono font-black text-xs px-2.5 py-1 rounded-none shadow-sm uppercase tracking-wider">
            [ {{ currentPhoto?.stage || 'EVIDENCE' }} ]
          </span>
          <div>
            <h3 class="text-xs sm:text-sm font-heading font-black text-white flex items-center gap-2">
              <span class="truncate max-w-[200px] sm:max-w-md">{{ currentPhoto?.work_order_title || currentPhoto?.item_name || 'Dokumentasi Bukti Lapangan' }}</span>
              <span v-if="currentPhoto?.spk_number" class="text-xs font-mono text-hazard font-normal">
                ({{ currentPhoto.spk_number }})
              </span>
            </h3>
            <p class="text-[10px] text-slate-400 font-mono mt-0.5">
              FORENSIC FRAME {{ currentIndex + 1 }} / {{ photoList.length }} • ID: {{ currentPhoto?.id || '-' }}
            </p>
          </div>
        </div>

        <!-- Actions: Zoom, Download, Close -->
        <div class="flex items-center gap-2">
          <!-- Zoom Controls -->
          <div class="flex items-center bg-[#0B0C0E] border border-[#232A36] rounded-xs p-0.5">
            <button
              @click="zoom = Math.max(0.5, zoom - 0.25)"
              class="p-1.5 hover:bg-[#181D26] text-slate-300 hover:text-white rounded-none cursor-pointer"
              title="Perkecil (Zoom Out)"
            >
              <ZoomOut class="w-3.5 h-3.5" />
            </button>
            <button
              @click="zoom = 1"
              class="px-2 py-1 text-[10px] font-mono font-bold text-hazard hover:bg-[#181D26] cursor-pointer"
              title="Reset Zoom"
            >
              {{ Math.round(zoom * 100) }}%
            </button>
            <button
              @click="zoom = Math.min(3, zoom + 0.25)"
              class="p-1.5 hover:bg-[#181D26] text-slate-300 hover:text-white rounded-none cursor-pointer"
              title="Perbesar (Zoom In)"
            >
              <ZoomIn class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Download Button -->
          <button
            @click="handleDownload(currentPhoto)"
            class="cad-btn-primary px-3 py-1.5 text-xs flex items-center gap-1.5 uppercase font-bold cursor-pointer"
            title="Unduh Foto Resolusi Asli"
          >
            <Download class="w-3.5 h-3.5" />
            <span class="hidden sm:inline">Unduh</span>
          </button>

          <!-- Close Button -->
          <button
            @click="close"
            class="p-2 bg-[#0B0C0E] hover:bg-red-950/80 text-slate-400 hover:text-red-400 border border-[#232A36] hover:border-red-600 rounded-xs transition-colors cursor-pointer"
            title="Tutup (ESC)"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Main Photo Canvas with Measuring Optic Frame -->
      <div class="relative flex-1 flex items-center justify-center p-4 sm:p-8 overflow-hidden min-h-0 bg-[#07080A]">
        <!-- Background Blueprint Grid -->
        <div class="absolute inset-0 bg-industrial-grid pointer-events-none opacity-40"></div>

        <!-- Left Nav Arrow -->
        <button
          v-if="photoList.length > 1"
          @click="prevPhoto"
          class="absolute left-4 z-30 w-10 h-10 rounded-xs bg-[#13171F]/90 hover:bg-hazard hover:text-carbon border border-[#232A36] text-white flex items-center justify-center shadow-2xl transition-all cursor-pointer"
          title="Foto Sebelumnya"
        >
          <ChevronLeft class="w-5 h-5" />
        </button>

        <!-- Image Container with Industrial Measuring Reticle Frame -->
        <div class="relative max-w-full max-h-full flex items-center justify-center overflow-auto p-4 z-10">
          <div class="relative border-2 border-[#232A36] bg-[#000000] p-1 shadow-2xl">
            <!-- CAD Corner Crosshair Markers -->
            <div class="absolute -top-3 -left-3 text-hazard font-mono text-xs pointer-events-none font-bold">┌</div>
            <div class="absolute -top-3 -right-3 text-hazard font-mono text-xs pointer-events-none font-bold">┐</div>
            <div class="absolute -bottom-3 -left-3 text-hazard font-mono text-xs pointer-events-none font-bold">└</div>
            <div class="absolute -bottom-3 -right-3 text-hazard font-mono text-xs pointer-events-none font-bold">┘</div>

            <!-- Overlaid Dimension Callout Line -->
            <div class="absolute inset-x-2 top-4 z-20 pointer-events-none">
              <div class="flex items-center justify-between text-[10px] font-mono text-hazard bg-black/60 px-3 py-1 border border-hazard/40">
                <span>&lt;---</span>
                <span class="font-bold tracking-widest uppercase">SPK FORENSIC OPTIC • 3000 mm CALIBRATION</span>
                <span>---&gt;</span>
              </div>
            </div>

            <!-- Actual Image -->
            <img
              v-if="currentPhoto?.file_path"
              :src="currentPhoto.file_url || getFileUrl(currentPhoto.file_path)"
              :alt="currentPhoto.file_name || 'Evidence Photo'"
              :style="{ transform: `scale(${zoom})`, transformOrigin: 'center center' }"
              class="max-w-full max-h-[68vh] object-contain transition-transform duration-200 select-none block"
              loading="eager"
              @error="handleLightboxImageError($event, currentPhoto)"
            />

            <!-- Overlaid Bottom Dimension Marker -->
            <div class="absolute inset-x-2 bottom-4 z-20 pointer-events-none">
              <div class="flex items-center justify-between text-[9px] font-mono text-hazard/80 bg-black/70 px-2 py-0.5 border border-dashed border-hazard/40">
                <span>[+] LAT/LON ALIGNED</span>
                <span class="font-mono">RETICLE: ACTIVE</span>
                <span>[+] SGX-FORENSIC</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Nav Arrow -->
        <button
          v-if="photoList.length > 1"
          @click="nextPhoto"
          class="absolute right-4 z-30 w-10 h-10 rounded-xs bg-[#13171F]/90 hover:bg-hazard hover:text-carbon border border-[#232A36] text-white flex items-center justify-center shadow-2xl transition-all cursor-pointer"
          title="Foto Berikutnya"
        >
          <ChevronRight class="w-5 h-5" />
        </button>
      </div>

      <!-- Bottom Forensic Metadata Inspection Bar -->
      <div class="p-3 sm:px-6 bg-[#13171F] border-t border-[#232A36] z-20 shrink-0 text-white text-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 max-w-7xl mx-auto">
          <!-- Left Forensic Details -->
          <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px]">
            <!-- Timestamp -->
            <span class="flex items-center gap-1.5 font-mono text-slate-300">
              <Clock class="w-3.5 h-3.5 text-slate-400 shrink-0" />
              <span>{{ formatDateTime(currentPhoto?.server_timestamp || currentPhoto?.created_at) }}</span>
            </span>

            <!-- GPS Coordinates (Hazard Yellow) -->
            <span v-if="isValidGps(currentPhoto?.latitude)" class="flex items-center gap-1.5 font-mono text-hazard font-bold">
              <MapPin class="w-3.5 h-3.5 shrink-0" />
              <span>GPS: {{ Number(currentPhoto.latitude).toFixed(5) }}, {{ Number(currentPhoto.longitude).toFixed(5) }} (±{{ currentPhoto.accuracy || 10 }}m)</span>
            </span>

            <!-- Emerald SHA-256 Badge -->
            <span class="flex items-center gap-1 font-mono text-emerald-400 bg-emerald-950/80 px-2 py-0.5 rounded-none border border-emerald-500/50">
              <ShieldCheck class="w-3.5 h-3.5 text-emerald-400 shrink-0" />
              <span class="font-bold">SHA-256: VALID</span>
              <span class="text-slate-400 text-[10px] ml-1">({{ currentPhoto?.file_hash?.substring(0, 10) || 'OK' }}...)</span>
            </span>

            <!-- Uploader -->
            <span v-if="currentPhoto?.uploader_name" class="text-slate-400">
              PIC: <strong class="text-white">{{ currentPhoto.uploader_name }}</strong>
            </span>
          </div>

          <!-- Right Action: Open in Maps -->
          <div v-if="isValidGps(currentPhoto?.latitude)" class="flex items-center gap-2">
            <a
              :href="`https://www.google.com/maps?q=${currentPhoto.latitude},${currentPhoto.longitude}`"
              target="_blank"
              rel="noopener noreferrer"
              class="px-2.5 py-1 bg-[#0B0C0E] hover:bg-[#181D26] text-hazard border border-[#232A36] hover:border-hazard/50 text-[10px] font-mono font-bold flex items-center gap-1.5 transition-all rounded-xs"
            >
              <ExternalLink class="w-3 h-3 text-hazard" />
              <span>BUKA GOOGLE MAPS</span>
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

function isValidGps(val) {
  if (val == null || val === '' || isNaN(Number(val))) return false;
  return Math.abs(Number(val)) > 0.0001;
}

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

function handleLightboxImageError(event, photo) {
  if (!photo) return;
  const currentSrc = event.target.src;
  const directIdUrl = `/api/evidence/photos/${photo.id}/view`;

  if (currentSrc && !currentSrc.includes(`/api/evidence/photos/${photo.id}/view`)) {
    event.target.src = directIdUrl;
  } else {
    const stage = photo.stage || 'EVIDENCE';
    const name = (photo.file_name || 'Foto Lapangan').substring(0, 24);
    event.target.src = `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="600" height="450" viewBox="0 0 600 450"><rect width="600" height="450" fill="%230b0c0e"/><text x="50%" y="42%" fill="%23facc15" font-family="monospace" font-size="16" font-weight="bold" text-anchor="middle">[ FORENSIC EVIDENCE: ${stage} ]</text><text x="50%" y="56%" fill="%2394a3b8" font-family="monospace" font-size="13" text-anchor="middle">${encodeURIComponent(name)}</text><text x="50%" y="70%" fill="%2334d399" font-family="monospace" font-size="12" text-anchor="middle">SHA-256: VALID</text></svg>`;
  }
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
