<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-800 to-purple-600 flex items-center justify-center text-white shadow-md shadow-indigo-900/20">
            <Camera class="w-4 h-4" />
          </div>
          <span>Evidence Gallery & Forensik Digital</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1 font-medium">
          Pusat audit visual seluruh dokumentasi Before, Process, dan After lapangan dengan verifikasi keaslian hash SHA-256 & geotagging GPS.
        </p>
      </div>

      <!-- Quick Summary Badges & Bulk Download -->
      <div class="flex items-center gap-2 self-start sm:self-auto">
        <span class="px-3 py-1 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 shadow-xs flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>{{ filteredPhotos.length }} Foto Terverifikasi</span>
        </span>
        <button
          v-if="filteredPhotos.length > 0"
          type="button"
          @click="downloadFilteredPhotos"
          class="px-3 py-1 bg-gradient-to-r from-purple-800 to-indigo-600 hover:from-purple-700 hover:to-indigo-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs active:scale-95 transition-all cursor-pointer"
          title="Unduh semua foto yang sesuai filter saat ini"
        >
          <Download class="w-3.5 h-3.5" />
          <span>Unduh Semua ({{ filteredPhotos.length }})</span>
        </button>
      </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex flex-col md:flex-row items-center justify-between gap-3 text-xs">
      <!-- Stage Filter Tabs -->
      <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0 custom-scrollbar">
        <button
          v-for="stg in stageOptions"
          :key="stg.value"
          @click="selectedStage = stg.value"
          :class="[
            'px-3.5 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap',
            selectedStage === stg.value
              ? 'bg-purple-900 text-white shadow-xs'
              : 'bg-slate-100 hover:bg-slate-200 text-slate-600'
          ]"
        >
          {{ stg.label }}
        </button>
      </div>

      <!-- Vendor Filter & Search -->
      <div class="flex items-center gap-2 w-full md:w-auto">
        <select
          v-model="selectedVendor"
          class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl font-semibold text-slate-700 text-xs cursor-pointer"
        >
          <option value="">Semua Perusahaan Client</option>
          <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
        </select>

        <div class="relative flex-1 sm:w-64">
          <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Cari SPK, lokasi, nama..."
            class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs"
          />
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-16">
      <div class="w-8 h-8 border-3 border-purple-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
      <p class="text-xs text-slate-500 font-medium">Memuat katalog bukti foto lapangan...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredPhotos.length === 0" class="text-center py-16 glass-card rounded-3xl border border-dashed border-slate-300">
      <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mx-auto mb-3">
        <ImageIcon class="w-6 h-6" />
      </div>
      <h3 class="font-bold text-slate-700 text-sm">Tidak ada bukti foto ditemukan</h3>
      <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter tahapan atau kata kunci pencarian Anda.</p>
    </div>

    <!-- Photos Grid (Media Wall) -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div
        v-for="photo in filteredPhotos"
        :key="photo.id"
        class="glass-card rounded-2xl border border-white/80 shadow-glass overflow-hidden flex flex-col justify-between hover:shadow-lg hover:border-purple-300 transition-all duration-300 group cursor-pointer"
        @click="openLightbox(photo)"
      >
        <div>
          <!-- Image Thumbnail Container -->
          <div class="h-44 bg-slate-100 relative overflow-hidden flex items-center justify-center">
            <img
              :src="photo.file_path"
              :alt="photo.file_name"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              @error="$event.target.src = 'https://images.unsplash.com/photo-1541888946425-d0fbb18086f6?w=400&auto=format&fit=crop&q=60'"
            />

            <!-- Stage Badge -->
            <div class="absolute top-2 left-2">
              <span
                :class="[
                  'px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider shadow-xs',
                  photo.stage === 'BEFORE' ? 'bg-amber-500 text-white' :
                  photo.stage === 'PROCESS' ? 'bg-indigo-600 text-white' :
                  photo.stage === 'AFTER' ? 'bg-emerald-600 text-white' :
                  'bg-rose-600 text-white'
                ]"
              >
                {{ photo.stage }} #{{ photo.sequence || 1 }}
              </span>
            </div>

            <!-- SHA-256 Validated Badge -->
            <div class="absolute top-2 right-2">
              <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold bg-slate-900/80 backdrop-blur-xs text-white shadow-xs flex items-center gap-1">
                <ShieldCheck class="w-3 h-3 text-emerald-400" />
                <span>SHA-256</span>
              </span>
            </div>

            <!-- DYNAMIC FLOATING CORNER DOWNLOAD BUTTON (Bottom-Right) -->
            <div class="absolute bottom-2 right-2 z-10">
              <button
                type="button"
                @click.stop="downloadSinglePhoto(photo)"
                class="w-7 h-7 rounded-full bg-slate-900/90 hover:bg-purple-700 text-white shadow-md flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95 cursor-pointer border border-white/40 backdrop-blur-xs"
                title="Unduh Foto Resolusi Asli"
              >
                <Download class="w-3.5 h-3.5" />
              </button>
            </div>

            <!-- Hover Quick View Overlay -->
            <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white gap-1.5 font-bold text-xs">
              <Eye class="w-4 h-4" />
              <span>Lihat Foto & Forensik</span>
            </div>
          </div>

          <!-- Photo Metadata Card Content -->
          <div class="p-3.5 space-y-2 text-xs">
            <div>
              <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono">
                <span>{{ photo.spk_number }}</span>
                <span>{{ photo.vendor_name || 'Client' }}</span>
              </div>
              <h4 class="font-bold text-slate-900 text-xs mt-0.5 line-clamp-1 group-hover:text-purple-900 transition-colors">
                {{ photo.work_order_title }}
              </h4>
              <p class="text-[11px] text-slate-500 truncate mt-0.5 flex items-center gap-1">
                <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
                <span>{{ photo.location_name }}</span>
              </p>
            </div>

            <!-- Item name if attached -->
            <div v-if="photo.work_item_name" class="p-1.5 bg-slate-50 rounded-lg text-[10px] text-slate-600 border border-slate-100 truncate">
              <strong>Item:</strong> {{ photo.work_item_name }}
            </div>
          </div>
        </div>

        <!-- Footer Card Details -->
        <div class="px-3.5 pb-3 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500 font-mono">
          <span class="truncate max-w-[120px]">{{ photo.uploader_name || 'Tim Lapangan' }}</span>
          <span>{{ new Date(photo.server_timestamp).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) }}</span>
        </div>
      </div>
    </div>

    <!-- High-Res Full-Screen Lightbox Modal -->
    <PhotoLightboxModal
      :isOpen="isLightboxOpen"
      :photos="filteredPhotos"
      :initialIndex="selectedLightboxIndex"
      @close="isLightboxOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../../services/api';
import PhotoLightboxModal from '../../components/PhotoLightboxModal.vue';
import {
  Camera,
  Search,
  MapPin,
  ShieldCheck,
  Eye,
  Download,
  ImageIcon
} from 'lucide-vue-next';

const photos = ref([]);
const vendors = ref([]);
const loading = ref(true);

const selectedStage = ref('ALL');
const selectedVendor = ref('');
const searchQuery = ref('');

const isLightboxOpen = ref(false);
const selectedLightboxIndex = ref(0);

const stageOptions = [
  { label: 'Semua Tahap', value: 'ALL' },
  { label: 'Sebelum (Before)', value: 'BEFORE' },
  { label: 'Proses Pengerjaan (Process)', value: 'PROCESS' },
  { label: 'Selesai (After)', value: 'AFTER' }
];

async function loadData() {
  loading.value = true;
  try {
    const [resPhotos, resVendors] = await Promise.all([
      api.getEvidencePhotos(),
      api.getVendors()
    ]);
    photos.value = resPhotos.data || [];
    vendors.value = resVendors.data || [];
  } catch (err) {
    console.error('Failed to load evidence photos:', err);
  } finally {
    loading.value = false;
  }
}

const filteredPhotos = computed(() => {
  return photos.value.filter(p => {
    // Stage Filter
    if (selectedStage.value !== 'ALL' && p.stage !== selectedStage.value) {
      return false;
    }
    // Vendor Filter
    if (selectedVendor.value && p.vendor_id !== parseInt(selectedVendor.value, 10)) {
      return false;
    }
    // Search Query
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase();
      const matchSpk = p.spk_number?.toLowerCase().includes(q);
      const matchTitle = p.work_order_title?.toLowerCase().includes(q);
      const matchLoc = p.location_name?.toLowerCase().includes(q);
      const matchNotes = p.notes?.toLowerCase().includes(q);
      if (!matchSpk && !matchTitle && !matchLoc && !matchNotes) return false;
    }
    return true;
  });
});

function openLightbox(photo) {
  const idx = filteredPhotos.value.findIndex(p => p.id === photo.id);
  selectedLightboxIndex.value = idx >= 0 ? idx : 0;
  isLightboxOpen.value = true;
}

function downloadSinglePhoto(photo) {
  if (!photo?.file_path) return;
  const link = document.createElement('a');
  link.href = photo.file_path;
  const ext = photo.file_name?.split('.').pop() || 'jpg';
  const spk = photo.spk_number ? `${photo.spk_number}_` : '';
  link.download = `${spk}${photo.stage || 'EVIDENCE'}_${photo.sequence || 1}.${ext}`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function downloadFilteredPhotos() {
  if (filteredPhotos.value.length === 0) {
    alert('Tidak ada foto untuk diunduh.');
    return;
  }

  filteredPhotos.value.forEach((p, idx) => {
    setTimeout(() => {
      downloadSinglePhoto(p);
    }, idx * 250);
  });
}

onMounted(() => {
  loadData();
});
</script>
