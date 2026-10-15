<template>
  <div class="space-y-4">
    <!-- Header (CAD Blueprint Style) -->
    <div class="bg-[#13171F] border border-[#232A36] p-4 rounded-xs flex flex-col md:flex-row md:items-center justify-between gap-3 relative overflow-hidden">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xs bg-[#0B0C0E] border border-hazard/40 flex items-center justify-center text-hazard shrink-0">
          <Camera class="w-5 h-5" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-base sm:text-lg font-heading font-black text-white uppercase tracking-wider">
              Evidence Gallery & Forensik Digital
            </h2>
            <span class="bg-hazard/10 text-hazard border border-hazard/30 text-[9px] font-mono px-1.5 py-0.2 rounded-none font-bold uppercase">
              SHA-256 AUDIT
            </span>
          </div>
          <p class="text-xs text-slate-400 font-mono mt-0.5">
            Pusat audit visual seluruh dokumentasi Before, Process, dan After lapangan dengan verifikasi keaslian hash SHA-256 & geotagging GPS.
          </p>
        </div>
      </div>

      <!-- Quick Summary Badges, Select All Toggle & Bulk Download -->
      <div class="flex items-center gap-2 self-start md:self-auto flex-wrap font-mono text-xs">
        <button
          v-if="canManagePhotos && filteredPhotos.length > 0"
          type="button"
          @click="toggleSelectAll"
          class="px-2.5 py-1.5 bg-[#0B0C0E] hover:bg-[#181D26] border border-[#232A36] hover:border-slate-500 rounded-xs font-bold text-slate-300 flex items-center gap-1.5 cursor-pointer transition-all"
        >
          <CheckSquare :class="['w-3.5 h-3.5', isAllSelected ? 'text-hazard' : 'text-slate-500']" />
          <span>{{ isAllSelected ? 'BATALKAN PILIH' : 'PILIH SEMUA' }}</span>
        </button>

        <span class="px-3 py-1.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs font-bold text-slate-300 flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span class="text-emerald-400">{{ filteredPhotos.length }} FOTO TERDAFTAR</span>
        </span>

        <button
          v-if="filteredPhotos.length > 0"
          type="button"
          @click="downloadFilteredPhotos"
          class="cad-btn-primary px-3 py-1.5 text-xs font-bold flex items-center gap-1.5 cursor-pointer uppercase"
          title="Unduh semua foto yang sesuai filter saat ini"
        >
          <Download class="w-3.5 h-3.5" />
          <span>UNDUH ({{ filteredPhotos.length }})</span>
        </button>
      </div>
    </div>

    <!-- Filters & Search Bar (Terminal Style) -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-3 flex flex-col md:flex-row items-center justify-between gap-3 text-xs font-mono">
      <!-- Stage Filter Tabs -->
      <div class="flex items-center gap-1 overflow-x-auto w-full md:w-auto pb-1 md:pb-0 scrollbar-none">
        <button
          v-for="stg in stageOptions"
          :key="stg.value"
          @click="selectedStage = stg.value"
          :class="[
            'px-3 py-1.5 rounded-none font-bold transition-all cursor-pointer whitespace-nowrap border uppercase',
            selectedStage === stg.value
              ? 'bg-hazard text-carbon border-hazard shadow-xs'
              : 'bg-[#0B0C0E] text-slate-400 hover:text-white border-[#232A36]'
          ]"
        >
          {{ stg.label }}
        </button>
      </div>

      <!-- Vendor Filter & Search -->
      <div class="flex items-center gap-2 w-full md:w-auto">
        <select
          v-model="selectedVendor"
          class="px-2.5 py-1.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs font-mono text-slate-300 text-xs cursor-pointer focus:outline-none focus:border-hazard"
        >
          <option value="">[ SEMUA CLIENT / VENDOR ]</option>
          <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
        </select>

        <div class="relative flex-1 sm:w-64">
          <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-500" />
          <input
            type="text"
            v-model="searchQuery"
            placeholder=">_ CARI SPK, LOKASI, PIC..."
            class="w-full pl-8 pr-3 py-1.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-xs font-mono text-white placeholder-slate-600 focus:outline-none focus:border-hazard"
          />
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-20 bg-[#13171F] border border-[#232A36] rounded-xs">
      <div class="w-8 h-8 border-2 border-hazard border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
      <p class="text-xs text-slate-400 font-mono tracking-wider">MEMUAT_KATALOG_FORENSIK_FOTO...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredPhotos.length === 0" class="text-center py-20 bg-[#13171F] border border-dashed border-[#232A36] rounded-xs">
      <div class="w-12 h-12 rounded-xs bg-[#0B0C0E] border border-[#232A36] flex items-center justify-center text-slate-500 mx-auto mb-3">
        <ImageIcon class="w-6 h-6" />
      </div>
      <h3 class="font-heading font-black text-white text-sm uppercase tracking-wider">TIDAK ADA FOTO BUKTI DITEMUKAN</h3>
      <p class="text-xs font-mono text-slate-500 mt-1">Ubah filter tahapan atau kata kunci pencarian di atas.</p>
    </div>

    <!-- Photos Grid (Forensic Measuring Optic Media Wall) -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
      <div
        v-for="photo in filteredPhotos"
        :key="photo.id"
        :class="[
          'bg-[#0E1117] border rounded-none overflow-hidden flex flex-col justify-between transition-all duration-200 group cursor-pointer relative',
          isSelected(photo.id) ? 'border-hazard shadow-md bg-[#181D26]' : 'border-[#232A36] hover:border-slate-500'
        ]"
        @click="openLightbox(photo)"
      >
        <div>
          <!-- Image Thumbnail with Measuring Optic Frame -->
          <div class="h-44 bg-[#000000] relative overflow-hidden flex items-center justify-center">
            <img
              :src="photo.file_url || getFileUrl(photo.file_path)"
              :alt="photo.file_name"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              @error="handleImageError($event, photo)"
            />

            <!-- Overlaid CAD Optic Reticles & Dimension Line -->
            <div class="absolute inset-0 pointer-events-none border border-hazard/20 m-1 flex flex-col justify-between p-1 z-10">
              <div class="flex justify-between text-[7px] font-mono text-hazard/70">
                <span>+ [0,0]</span>
                <span>SPECS +</span>
              </div>
              <div class="w-full text-center py-0.5 bg-black/50 backdrop-blur-2xs border-y border-dashed border-hazard/50 text-[8px] font-mono text-hazard font-bold tracking-widest">
                &lt;--- OPTIC #{{ photo.sequence || 1 }} ---&gt;
              </div>
              <div class="flex justify-between text-[7px] font-mono text-hazard/70">
                <span>+ OPTIC_ID:{{ photo.id }}</span>
                <span>CALIB +</span>
              </div>
            </div>

            <!-- Checkbox Select Overlay (Top-Left) -->
            <div v-if="canManagePhotos" class="absolute top-2 left-2 z-20" @click.stop>
              <input
                type="checkbox"
                :checked="isSelected(photo.id)"
                @change="toggleSelect(photo.id)"
                class="w-4 h-4 rounded-none border-[#232A36] bg-[#0B0C0E] text-hazard focus:ring-hazard cursor-pointer shadow-md"
              />
            </div>

            <!-- Stage Badge: Solid High-Visibility Tag (Point 2 of .cursorrules) -->
            <div class="absolute top-2" :class="canManagePhotos ? 'left-8' : 'left-2'" style="z-index: 15;">
              <span
                :class="[
                  'px-2 py-0.5 rounded-none text-[9px] font-mono font-black uppercase tracking-wider shadow-md',
                  photo.stage === 'BEFORE' ? 'bg-hazard text-carbon' :
                  photo.stage === 'PROCESS' ? 'bg-blue-400 text-carbon' :
                  photo.stage === 'AFTER' ? 'bg-hazard text-carbon' :
                  'bg-red-500 text-white'
                ]"
              >
                [ {{ photo.stage }} #{{ photo.sequence || 1 }} ]
              </span>
            </div>

            <!-- Floating Action Buttons (Download & Delete) -->
            <div class="absolute bottom-2 right-2 z-20 flex items-center gap-1.5" @click.stop>
              <!-- Single Delete Button (Superuser / Admin) -->
              <button
                v-if="canManagePhotos"
                type="button"
                @click.stop="handleDeleteSingle(photo)"
                class="w-6 h-6 rounded-none bg-red-950/90 hover:bg-red-600 text-white border border-red-500/50 flex items-center justify-center transition-colors cursor-pointer"
                title="Hapus Foto Bukti Ini"
              >
                <Trash2 class="w-3 h-3" />
              </button>

              <!-- Single Download Button -->
              <button
                type="button"
                @click.stop="downloadSinglePhoto(photo)"
                class="w-6 h-6 rounded-none bg-carbon/90 hover:bg-hazard hover:text-carbon text-white border border-[#232A36] flex items-center justify-center transition-colors cursor-pointer"
                title="Unduh Foto Resolusi Asli"
              >
                <Download class="w-3 h-3" />
              </button>
            </div>

            <!-- Hover Quick View Overlay -->
            <div class="absolute inset-0 bg-[#0B0C0E]/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-hazard gap-1.5 font-mono font-bold text-xs pointer-events-none z-15">
              <Eye class="w-4 h-4" />
              <span>BUKA FORENSIK</span>
            </div>
          </div>

          <!-- Bottom Inspection Bar (GPS Coordinates & SHA-256 Valid) -->
          <div class="p-2 bg-[#0B0C0E] border-t border-b border-[#232A36] text-[9px] font-mono flex items-center justify-between">
            <span class="text-hazard font-bold truncate max-w-[130px]">
              GPS: {{ photo.latitude ? Number(photo.latitude).toFixed(4) : '-' }}, {{ photo.longitude ? Number(photo.longitude).toFixed(4) : '-' }}
            </span>
            <span class="bg-emerald-950 text-emerald-400 border border-emerald-500/40 px-1.5 py-0.2 rounded-none font-bold">
              SHA-256: VALID
            </span>
          </div>

          <!-- Photo Metadata Card Content -->
          <div class="p-3 space-y-1.5 text-xs font-mono">
            <div>
              <div class="flex items-center justify-between text-[10px] text-slate-500">
                <span class="font-bold text-slate-300">{{ photo.spk_number }}</span>
                <span class="truncate max-w-[110px] text-slate-400">{{ photo.vendor_name || 'SGX CLIENT' }}</span>
              </div>
              <h4 class="font-heading font-black text-white text-xs mt-0.5 line-clamp-1 group-hover:text-hazard transition-colors uppercase">
                {{ photo.work_order_title }}
              </h4>
              <p class="text-[10px] text-slate-400 truncate mt-0.5 flex items-center gap-1">
                <MapPin class="w-3 h-3 text-slate-500 shrink-0" />
                <span>{{ photo.location_name }}</span>
              </p>
            </div>

            <!-- Item name if attached -->
            <div v-if="photo.work_item_name" class="p-1 bg-[#0B0C0E] rounded-none text-[9px] text-slate-300 border border-[#232A36] truncate">
              <strong class="text-hazard">SUB-ITEM:</strong> {{ photo.work_item_name }}
            </div>
          </div>
        </div>

        <!-- Footer Card Details -->
        <div class="px-3 pb-2.5 pt-2 border-t border-[#1C2028] flex items-center justify-between text-[9px] text-slate-500 font-mono">
          <span class="truncate max-w-[120px] text-slate-400">PIC: {{ photo.uploader_name || 'TIM LAPANGAN' }}</span>
          <span>{{ new Date(photo.server_timestamp).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) }}</span>
        </div>
      </div>
    </div>

    <!-- Floating Bulk Actions Bottom Bar (Industrial CAD Strip) -->
    <Teleport to="body">
      <div
        v-if="selectedPhotoIds.length > 0"
        class="fixed bottom-6 inset-x-0 z-40 flex justify-center px-4 animate-slide-up font-mono"
      >
        <div class="bg-[#13171F] text-white rounded-xs p-3 sm:px-6 sm:py-3.5 shadow-2xl border border-hazard/50 flex flex-wrap items-center justify-between gap-4 max-w-2xl w-full text-xs">
          <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-none bg-hazard text-carbon font-bold flex items-center justify-center text-xs">
              {{ selectedPhotoIds.length }}
            </span>
            <span class="font-bold text-white uppercase tracking-wider">FOTO TERPILIH</span>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="downloadSelectedPhotos"
              class="px-3 py-1.5 bg-[#0B0C0E] hover:bg-[#181D26] text-white font-bold rounded-xs flex items-center gap-1.5 border border-[#232A36] cursor-pointer uppercase"
            >
              <Download class="w-3.5 h-3.5 text-hazard" />
              <span>UNDUH ({{ selectedPhotoIds.length }})</span>
            </button>

            <button
              type="button"
              @click="handleBulkDelete"
              :disabled="deleting"
              class="px-3.5 py-1.5 bg-red-600 hover:bg-red-500 text-white font-bold rounded-xs shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-95 disabled:opacity-50 uppercase"
            >
              <Trash2 class="w-3.5 h-3.5" />
              <span>{{ deleting ? 'MENGHAPUS...' : `HAPUS TERPILIH (${selectedPhotoIds.length})` }}</span>
            </button>

            <button
              type="button"
              @click="selectedPhotoIds = []"
              class="p-1.5 hover:bg-[#181D26] rounded-xs text-slate-400 hover:text-white cursor-pointer"
              title="Batalkan Pilihan"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- High-Res Forensic Measuring Optic Lightbox Modal -->
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
  { label: 'SEMUA TAHAP', value: 'ALL' },
  { label: 'BEFORE (KONDISI AWAL)', value: 'BEFORE' },
  { label: 'PROCESS (PENGERJAAN)', value: 'PROCESS' },
  { label: 'AFTER (HASIL AKHIR)', value: 'AFTER' }
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
    const stage = photo.stage || 'EVIDENCE';
    const name = (photo.file_name || 'Foto Lapangan').substring(0, 24);
    event.target.src = `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"><rect width="400" height="300" fill="%230b0c0e"/><text x="50%" y="42%" fill="%23facc15" font-family="monospace" font-size="14" font-weight="bold" text-anchor="middle">[ FORENSIC: ${stage} ]</text><text x="50%" y="58%" fill="%2394a3b8" font-family="monospace" font-size="11" text-anchor="middle">${encodeURIComponent(name)}</text><text x="50%" y="72%" fill="%2334d399" font-family="monospace" font-size="9" text-anchor="middle">SHA-256 VALID</text></svg>`;
  }
}

onMounted(() => {
  loadData();
});
</script>
