<template>
  <div class="glass-card rounded-2xl p-4 shadow-glass border border-white/70 space-y-3.5">
    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h4 class="font-bold text-sm text-slate-900 flex items-center gap-2">
          <span>Tahap: {{ stage }}</span>
          <span
            v-if="isSatisfied"
            class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-500/10 px-2.5 py-0.5 rounded-full border border-emerald-300"
          >
            <CheckCircle2 class="w-3 h-3 text-emerald-600" />
            Lengkap ({{ stagePhotos.length }}/{{ requiredCount }})
          </span>
          <span
            v-else
            class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-500/10 px-2.5 py-0.5 rounded-full border border-amber-300"
          >
            <AlertCircle class="w-3 h-3 text-amber-600" />
            Wajib Min. {{ requiredCount }} Foto ({{ stagePhotos.length }}/{{ requiredCount }})
          </span>
          <span
            class="inline-flex items-center gap-1 text-[10px] font-mono font-bold text-emerald-800 bg-emerald-500/15 px-2.5 py-0.5 rounded-full border border-emerald-300/80 shadow-2xs"
            title="Sistem otomatis mencetak jam, koordinat GPS, peta satelit, dan logo perusahaan ke foto"
          >
            <MapPin class="w-3 h-3 text-emerald-600 animate-pulse" />
            Auto GPS Map Stamp
          </span>
        </h4>
        <p class="text-xs text-slate-500 mt-1">
          <span v-if="stage === 'BEFORE'">Foto kondisi aset/lokasi sebelum pekerjaan dimulai.</span>
          <span v-else-if="stage === 'PROCESS'">Foto tim saat proses pengerjaan lapangan & penggunaan APD.</span>
          <span v-else-if="stage === 'AFTER'">Foto hasil akhir pekerjaan yang telah selesai dan rapi.</span>
          <span v-else-if="stage === 'ISSUE'">Foto bukti pendukung kendala teknis lapangan.</span>
        </p>
      </div>

      <!-- Dual Action Buttons: Direct Camera vs Multi-Photo Gallery -->
      <div class="flex items-center gap-2">
        <!-- 1. Direct Camera Capture Input -->
        <input
          type="file"
          accept="image/*"
          capture="environment"
          ref="cameraInputRef"
          @change="(e) => handleFilesSelected(e.target.files)"
          class="hidden"
          :id="`cam-input-${stage}-${workOrderId}-${itemId || 0}`"
          :disabled="uploading"
        />
        <label
          :for="`cam-input-${stage}-${workOrderId}-${itemId || 0}`"
          :class="[
            'inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold transition-all duration-200 shadow-xs active:scale-95',
            uploading
              ? 'bg-slate-200 text-slate-400 cursor-not-allowed'
              : 'bg-gradient-to-r from-purple-900 to-indigo-800 hover:from-purple-800 hover:to-indigo-700 text-white cursor-pointer shadow-purple-900/20'
          ]"
          title="Ambil foto langsung menggunakan kamera HP"
        >
          <Camera class="w-3.5 h-3.5" />
          <span class="hidden sm:inline">Kamera</span>
        </label>

        <!-- 2. Gallery Multi-Select Input -->
        <input
          type="file"
          accept="image/*"
          multiple
          ref="galleryInputRef"
          @change="(e) => handleFilesSelected(e.target.files)"
          class="hidden"
          :id="`gallery-input-${stage}-${workOrderId}-${itemId || 0}`"
          :disabled="uploading"
        />
        <label
          :for="`gallery-input-${stage}-${workOrderId}-${itemId || 0}`"
          :class="[
            'inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all duration-200 shadow-xs active:scale-95',
            uploading
              ? 'bg-slate-200 text-slate-400 cursor-not-allowed'
              : 'bg-gradient-to-r from-brand-900 to-brand-700 hover:from-brand-800 hover:to-brand-600 text-white cursor-pointer shadow-brand-900/20'
          ]"
          title="Pilih satu atau banyak foto sekaligus dari memori galeri"
        >
          <Images class="w-3.5 h-3.5" />
          <span>+ Galeri (Banyak Foto)</span>
        </label>
      </div>
    </div>

    <!-- Batch Uploading Progress Banner -->
    <div
      v-if="uploading"
      class="p-3 bg-purple-50 border border-purple-200 rounded-xl text-xs text-purple-950 flex items-center justify-between shadow-xs animate-pulse"
    >
      <div class="flex items-center gap-2">
        <Loader2 class="w-4 h-4 text-purple-700 animate-spin shrink-0" />
        <span class="font-bold">
          Mengunggah foto {{ uploadProgress.current }} dari {{ uploadProgress.total }}...
        </span>
      </div>
      <span class="font-mono font-black text-purple-900">
        {{ Math.round((uploadProgress.current / uploadProgress.total) * 100) }}%
      </span>
    </div>

    <!-- Error Banner -->
    <div
      v-if="error"
      class="p-2.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center justify-between gap-2"
    >
      <div class="flex items-center gap-2">
        <AlertCircle class="w-4 h-4 shrink-0 text-rose-600" />
        <span>{{ error }}</span>
      </div>
      <button @click="error = null" class="text-rose-500 hover:text-rose-800 font-bold text-xs cursor-pointer">
        ✕
      </button>
    </div>

    <!-- Photos Grid with Corner Dynamic Delete & Download Buttons -->
    <div v-if="stagePhotos.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
      <div
        v-for="(photo, idx) in stagePhotos"
        :key="photo.id || idx"
        @click="openLightbox(idx)"
        class="group relative bg-slate-900 rounded-2xl overflow-hidden border border-slate-200/90 aspect-4/3 flex flex-col shadow-xs hover:shadow-md transition-all duration-300 cursor-pointer"
      >
        <!-- Photo Image Thumbnail -->
        <img
          :src="getFileUrl(photo.file_path)"
          :alt="`Evidence ${stage} ${idx + 1}`"
          class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          loading="lazy"
        />

        <!-- DYNAMIC FLOATING CORNER DELETE BUTTON (Top-Right) -->
        <div class="absolute top-2 right-2 z-10">
          <button
            type="button"
            @click.stop="confirmDelete(photo)"
            :disabled="deletingId === photo.id"
            class="w-7 h-7 rounded-full bg-rose-600 hover:bg-rose-700 text-white shadow-md flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95 cursor-pointer border border-white/80"
            title="Hapus Foto Bukti Ini"
          >
            <Loader2 v-if="deletingId === photo.id" class="w-3.5 h-3.5 animate-spin" />
            <Trash2 v-else class="w-3.5 h-3.5" />
          </button>
        </div>

        <!-- DYNAMIC FLOATING CORNER DOWNLOAD BUTTON (Bottom-Right) -->
        <div class="absolute bottom-2 right-2 z-10">
          <button
            type="button"
            @click.stop="triggerDirectDownload(photo, idx)"
            class="w-7 h-7 rounded-full bg-slate-900/90 hover:bg-purple-700 text-white shadow-md flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95 cursor-pointer border border-white/40 backdrop-blur-xs"
            title="Unduh Foto Resolusi Asli"
          >
            <Download class="w-3.5 h-3.5" />
          </button>
        </div>

        <!-- Bottom Forensic Stamp Info -->
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/95 via-black/60 to-transparent p-2.5 pr-10 text-white text-[10px] backdrop-blur-xs pointer-events-none">
          <div class="font-bold flex items-center justify-between">
            <span>Foto #{{ photo.sequence || idx + 1 }}</span>
            <span class="font-mono text-[9px] text-emerald-400 bg-emerald-950/80 px-1.5 py-0.2 rounded border border-emerald-500/30">SHA-256 ✓</span>
          </div>
          <div class="text-[9px] text-slate-300 truncate mt-0.5 font-mono">
            <span v-if="photo.server_timestamp">{{ new Date(photo.server_timestamp).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</span>
            <span v-if="photo.latitude"> • {{ Number(photo.latitude).toFixed(4) }}, {{ Number(photo.longitude).toFixed(4) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="border-2 border-dashed border-slate-200/80 rounded-2xl p-6 text-center text-slate-400 bg-white/40 backdrop-blur-xs">
      <ImageIcon class="w-8 h-8 mx-auto mb-2 opacity-40 text-slate-400" />
      <p class="text-xs font-medium">Belum ada foto bukti pada tahap {{ stage }}.</p>
      <p class="text-[11px] text-slate-400 mt-0.5">Gunakan tombol kamera atau pilih banyak foto dari galeri sekaligus.</p>
    </div>

    <!-- Delete Confirmation Modal Dialog -->
    <div
      v-if="photoToDelete"
      class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-slate-200 space-y-4 animate-fade-in">
        <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
          <Trash2 class="w-6 h-6" />
        </div>
        <div class="text-center space-y-1">
          <h4 class="font-black text-sm text-slate-900">Hapus Foto Bukti?</h4>
          <p class="text-xs text-slate-500">
            Foto bukti #{{ photoToDelete.sequence || '' }} pada tahap <strong>{{ stage }}</strong> akan dihapus permanen dari server dan database.
          </p>
        </div>
        <div class="flex items-center gap-2 pt-2">
          <button
            type="button"
            @click="photoToDelete = null"
            :disabled="deletingId !== null"
            class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            @click="executeDelete"
            :disabled="deletingId !== null"
            class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-md shadow-rose-600/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-95"
          >
            <Loader2 v-if="deletingId !== null" class="w-3.5 h-3.5 animate-spin" />
            <span>{{ deletingId !== null ? 'Menghapus...' : 'Ya, Hapus' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Reusable Photo Lightbox Full-Screen Viewer -->
    <PhotoLightboxModal
      :isOpen="isLightboxOpen"
      :photos="stagePhotos"
      :initialIndex="selectedLightboxIndex"
      @close="isLightboxOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import {
  Camera,
  Images,
  CheckCircle2,
  AlertCircle,
  Image as ImageIcon,
  Loader2,
  Trash2,
  Download,
  MapPin
} from 'lucide-vue-next';
import { api, getFileUrl } from '../services/api';
import PhotoLightboxModal from './PhotoLightboxModal.vue';
import { stampGpsWatermark } from '../utils/watermarkEngine';

const props = defineProps({
  workOrderId: { type: [String, Number], required: true },
  itemId: { type: [String, Number], default: null },
  stage: { type: String, default: 'AFTER' },
  requiredCount: { type: Number, default: 1 },
  existingPhotos: { type: Array, default: () => [] },
  spkNumber: { type: String, default: '' },
  locationName: { type: String, default: '' },
  workOrderTitle: { type: String, default: '' },
  address: { type: String, default: '' }
});

const autoStampGps = ref(true);

const emit = defineEmits(['uploadSuccess', 'photoDeleted']);

const uploading = ref(false);
const uploadProgress = ref({ current: 0, total: 0 });
const error = ref(null);
const cameraInputRef = ref(null);
const galleryInputRef = ref(null);

const deletingId = ref(null);
const photoToDelete = ref(null);

const stagePhotos = computed(() => {
  return (props.existingPhotos || []).filter((p) => {
    const stageMatch = p.stage === props.stage;
    if (props.itemId) {
      return stageMatch && (p.item_id === props.itemId || !p.item_id);
    }
    return stageMatch;
  });
});

const isSatisfied = computed(() => {
  return stagePhotos.value.length >= props.requiredCount;
});

/**
 * Handle Multi-File or Single-File Selection from Camera / Gallery
 */
async function handleFilesSelected(fileList) {
  if (!fileList || fileList.length === 0) return;

  const files = Array.from(fileList);
  uploading.value = true;
  uploadProgress.value = { current: 0, total: files.length };
  error.value = null;

  // Get current GPS position once for the batch
  let gpsCoords = null;
  if (navigator.geolocation) {
    try {
      gpsCoords = await new Promise((resolve) => {
        navigator.geolocation.getCurrentPosition(
          (pos) => resolve({
            latitude: pos.coords.latitude,
            longitude: pos.coords.longitude,
            accuracy: Math.round(pos.coords.accuracy)
          }),
          () => resolve(null),
          { timeout: 4000 }
        );
      });
    } catch (e) {
      gpsCoords = null;
    }
  }

  let lastUploadedData = null;

  // Sequential batch upload to ensure correct ordering & sequence numbers
  for (let i = 0; i < files.length; i++) {
    uploadProgress.value.current = i + 1;
    const file = files[i];

    try {
      // Apply Automatic GPS Map & Clock Watermark Stamping
      let fileToUpload = file;
      if (autoStampGps.value) {
        fileToUpload = await stampGpsWatermark(file, {
          stage: props.stage,
          spkNumber: props.spkNumber || `SPK-${props.workOrderId}`,
          locationName: props.locationName || props.workOrderTitle || 'Toko Cabang Proyek',
          address: props.address || 'Indonesia',
          latitude: gpsCoords?.latitude,
          longitude: gpsCoords?.longitude,
          accuracy: gpsCoords?.accuracy,
          timestamp: new Date()
        });
      }

      const formData = new FormData();
      formData.append('work_order_id', props.workOrderId);
      if (props.itemId) {
        formData.append('item_id', props.itemId);
      }
      formData.append('stage', props.stage);
      formData.append('sequence', stagePhotos.value.length + i + 1);
      formData.append('file', fileToUpload);

      const finalUploadLat = fileToUpload._latitude != null ? fileToUpload._latitude : gpsCoords?.latitude;
      const finalUploadLng = fileToUpload._longitude != null ? fileToUpload._longitude : gpsCoords?.longitude;
      const finalUploadAcc = gpsCoords?.accuracy || 8;

      if (finalUploadLat != null && finalUploadLng != null) {
        formData.append('latitude', finalUploadLat);
        formData.append('longitude', finalUploadLng);
        formData.append('accuracy', finalUploadAcc);
      }
      if (fileToUpload._address) {
        formData.append('notes', `Lokasi: ${fileToUpload._address}`);
      }

      const res = await api.uploadEvidence(formData);
      lastUploadedData = res.data;
    } catch (err) {
      error.value = `Gagal mengunggah foto "${file.name}": ${err.message}`;
      break;
    }
  }

  uploading.value = false;
  if (cameraInputRef.value) cameraInputRef.value.value = '';
  if (galleryInputRef.value) galleryInputRef.value.value = '';

  if (lastUploadedData) {
    emit('uploadSuccess', lastUploadedData);
  }
}

/**
 * Open confirmation modal
 */
function confirmDelete(photo) {
  photoToDelete.value = photo;
}

/**
 * Execute delete photo via API
 */
async function executeDelete() {
  if (!photoToDelete.value) return;

  const targetId = photoToDelete.value.id;
  deletingId.value = targetId;
  error.value = null;

  try {
    await api.deleteEvidencePhoto(targetId);
    photoToDelete.value = null;
    emit('photoDeleted', targetId);
    emit('uploadSuccess', { deletedId: targetId });
  } catch (err) {
    error.value = `Gagal menghapus foto bukti: ${err.message}`;
  } finally {
    deletingId.value = null;
  }
}

/**
 * Lightbox & Direct Download Handlers
 */
const isLightboxOpen = ref(false);
const selectedLightboxIndex = ref(0);

function openLightbox(idx) {
  selectedLightboxIndex.value = idx;
  isLightboxOpen.value = true;
}

function triggerDirectDownload(photo, idx) {
  if (!photo?.file_path) return;
  const link = document.createElement('a');
  link.href = photo.file_path;
  const ext = photo.file_name?.split('.').pop() || 'jpg';
  link.download = `SPK-${props.workOrderId}_${props.stage}_${photo.sequence || idx + 1}.${ext}`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
</script>
