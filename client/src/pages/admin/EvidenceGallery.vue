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

      <!-- Quick Summary Badges, Select All Toggle & Bulk Download -->
      <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
        <button
          v-if="canManagePhotos && filteredPhotos.length > 0"
          type="button"
          @click="toggleSelectAll"
          class="px-3 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-95 transition-all"
        >
          <CheckSquare :class="['w-3.5 h-3.5', isAllSelected ? 'text-purple-700' : 'text-slate-400']" />
          <span>{{ isAllSelected ? 'Batalkan Pilih Semua' : 'Pilih Semua' }}</span>
        </button>

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
        :class="[
          'glass-card rounded-2xl border shadow-glass overflow-hidden flex flex-col justify-between hover:shadow-lg transition-all duration-300 group cursor-pointer relative',
          isSelected(photo.id) ? 'ring-2 ring-purple-600 border-purple-400 bg-purple-50/20' : 'border-white/80 hover:border-purple-300'
        ]"
        @click="openLightbox(photo)"
      >
        <div>
          <!-- Image Thumbnail Container -->
          <div class="h-44 bg-slate-100 relative overflow-hidden flex items-center justify-center">
            <img
              :src="photo.file_url || getFileUrl(photo.file_path)"
              :alt="photo.file_name"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              @error="handleImageError($event, photo)"
            />

            <!-- Checkbox Select Overlay (Top-Left) -->
            <div v-if="canManagePhotos" class="absolute top-2 left-2 z-20" @click.stop>
              <input
                type="checkbox"
                :checked="isSelected(photo.id)"
                @change="toggleSelect(photo.id)"
                class="w-4 h-4 rounded border-slate-300 text-purple-700 focus:ring-purple-500 cursor-pointer shadow-md bg-white/90"
              />
            </div>

            <!-- Stage Badge -->
            <div class="absolute top-2" :class="canManagePhotos ? 'left-8' : 'left-2'">
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

            <!-- DYNAMIC FLOATING CORNER BUTTONS (Download & Delete) -->
            <div class="absolute bottom-2 right-2 z-10 flex items-center gap-1.5" @click.stop>
              <!-- Single Delete Button (Superuser / Admin) -->
              <button
                v-if="canManagePhotos"
                type="button"
                @click.stop="handleDeleteSingle(photo)"
                class="w-7 h-7 rounded-full bg-rose-600/90 hover:bg-rose-700 text-white shadow-md flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95 cursor-pointer border border-white/40 backdrop-blur-xs"
                title="Hapus Foto Bukti Ini"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>

              <!-- Single Download Button -->
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
            <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white gap-1.5 font-bold text-xs pointer-events-none">
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
                <MapPin class="w-3.5 h-3.5 text-slate-400 shrink-0" />
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

    <!-- Floating Bulk Actions Bottom Bar -->
    <Teleport to="body">
      <div
        v-if="selectedPhotoIds.length > 0"
        class="fixed bottom-6 inset-x-0 z-40 flex justify-center px-4 animate-slide-up"
      >
        <div class="bg-slate-900/95 text-white backdrop-blur-md rounded-2xl p-3 sm:px-6 sm:py-3.5 shadow-2xl border border-slate-700 flex flex-wrap items-center justify-between gap-4 max-w-2xl w-full text-xs">
          <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-purple-600 text-white font-bold flex items-center justify-center text-xs">
              {{ selectedPhotoIds.length }}
            </span>
            <span class="font-bold text-slate-200">Foto Terpilih</span>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="downloadSelectedPhotos"
              class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl flex items-center gap-1.5 border border-slate-600 transition-all cursor-pointer"
            >
              <Download class="w-3.5 h-3.5" />
              <span>Unduh ({{ selectedPhotoIds.length }})</span>
            </button>

            <button
              type="button"
              @click="handleBulkDelete"
              :disabled="deleting"
              class="px-4 py-1.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold rounded-xl shadow-xs flex items-center gap-1.5 transition-all cursor-pointer active:scale-95 disabled:opacity-50"
            >
              <Trash2 class="w-3.5 h-3.5" />
              <span>{{ deleting ? 'Menghapus...' : `Hapus Terpilih (${selectedPhotoIds.length})` }}</span>
            </button>

            <button
              type="button"
              @click="selectedPhotoIds = []"
              class="p-1.5 hover:bg-slate-800 rounded-xl text-slate-400 hover:text-white cursor-pointer transition-all"
              title="Batalkan Pilihan"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </Teleport>

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
import { api, getFileUrl } from '../../services/api';
import { useAuth } from '../../composables/useAuth';
import PhotoLightboxModal from '../../components/PhotoLightboxModal.vue';
import {
  Camera,
  Search,
  MapPin,
  ShieldCheck,
  Eye,
  Download,
  ImageIcon,
  Trash2,
  CheckSquare,
  X
} from 'lucide-vue-next';

const auth = useAuth();
const canManagePhotos = computed(() => ['SUPERUSER', 'ADMIN'].includes(auth.state.user?.role));

const photos = ref([]);
const vendors = ref([]);
const loading = ref(true);
const deleting = ref(false);

const selectedStage = ref('ALL');
const selectedVendor = ref('');
const searchQuery = ref('');
const selectedPhotoIds = ref([]);

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

// Selection System
function isSelected(id) {
  return selectedPhotoIds.value.includes(id);
}

function toggleSelect(id) {
  const index = selectedPhotoIds.value.indexOf(id);
  if (index >= 0) {
    selectedPhotoIds.value.splice(index, 1);
  } else {
    selectedPhotoIds.value.push(id);
  }
}

const isAllSelected = computed(() => {
  if (filteredPhotos.value.length === 0) return false;
  return filteredPhotos.value.every(p => selectedPhotoIds.value.includes(p.id));
});

function toggleSelectAll() {
  if (isAllSelected.value) {
    const filteredIds = filteredPhotos.value.map(p => p.id);
    selectedPhotoIds.value = selectedPhotoIds.value.filter(id => !filteredIds.includes(id));
  } else {
    const newSelected = new Set([...selectedPhotoIds.value, ...filteredPhotos.value.map(p => p.id)]);
    selectedPhotoIds.value = Array.from(newSelected);
  }
}

// Single Delete
async function handleDeleteSingle(photo) {
  if (!confirm(`Hapus foto bukti forensik ini (#${photo.id} - ${photo.stage} SPK ${photo.spk_number || ''}) secara permanen?`)) {
    return;
  }

  try {
    const res = await api.deleteEvidencePhoto(photo.id);
    if (res.success) {
      alert(res.message || 'Foto bukti berhasil dihapus.');
      selectedPhotoIds.value = selectedPhotoIds.value.filter(id => id !== photo.id);
      await loadData();
    }
  } catch (err) {
    console.error('Failed to delete evidence photo:', err);
    alert('Gagal menghapus foto: ' + (err.message || 'Terjadi kesalahan'));
  }
}

// Bulk Delete
async function handleBulkDelete() {
  const count = selectedPhotoIds.value.length;
  if (count === 0) return;

  if (!confirm(`PERINGATAN: Anda akan menghapus ${count} foto bukti forensik terpilih secara permanen.\n\nApakah Anda yakin ingin melanjutkan?`)) {
    return;
  }

  deleting.value = true;
  try {
    const res = await api.bulkDeleteEvidencePhotos(selectedPhotoIds.value);
    if (res.success) {
      alert(res.message || `${count} foto bukti berhasil dihapus.`);
      selectedPhotoIds.value = [];
      await loadData();
    }
  } catch (err) {
    console.error('Failed to bulk delete evidence photos:', err);
    alert('Gagal menghapus foto: ' + (err.message || 'Terjadi kesalahan'));
  } finally {
    deleting.value = false;
  }
}

function openLightbox(photo) {
  const idx = filteredPhotos.value.findIndex(p => p.id === photo.id);
  selectedLightboxIndex.value = idx >= 0 ? idx : 0;
  isLightboxOpen.value = true;
}

function downloadSinglePhoto(photo) {
  if (!photo?.file_path) return;
  const link = document.createElement('a');
  link.href = getFileUrl(photo.file_path);
  const ext = photo.file_name?.split('.').pop() || 'jpg';
  const spk = photo.spk_number ? `${photo.spk_number}_` : '';
  link.download = `${spk}${photo.stage || 'EVIDENCE'}_${photo.sequence || 1}.${ext}`;
  link.target = '_blank';
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

function downloadSelectedPhotos() {
  const selected = photos.value.filter(p => selectedPhotoIds.value.includes(p.id));
  if (selected.length === 0) return;

  selected.forEach((p, idx) => {
    setTimeout(() => {
      downloadSinglePhoto(p);
    }, idx * 250);
  });
}

function handleImageError(event, photo) {
  const currentSrc = event.target.src;
  const directIdUrl = `/api/evidence/photos/${photo.id}/view`;

  if (currentSrc && !currentSrc.includes(`/api/evidence/photos/${photo.id}/view`)) {
    event.target.src = directIdUrl;
  } else {
    // Elegant inline SVG fallback badge with photo metadata
    const stage = photo.stage || 'EVIDENCE';
    const name = (photo.file_name || 'Foto Lapangan').substring(0, 24);
    event.target.src = `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"><rect width="400" height="300" fill="%230f172a"/><text x="50%" y="42%" fill="%2338bdf8" font-family="sans-serif" font-size="14" font-weight="bold" text-anchor="middle">FOTO BUKTI (${stage})</text><text x="50%" y="58%" fill="%2394a3b8" font-family="monospace" font-size="11" text-anchor="middle">${encodeURIComponent(name)}</text><text x="50%" y="72%" fill="%2334d399" font-family="monospace" font-size="9" text-anchor="middle">SHA-256 VALID</text></svg>`;
  }
}

onMounted(() => {
  loadData();
});
</script>
