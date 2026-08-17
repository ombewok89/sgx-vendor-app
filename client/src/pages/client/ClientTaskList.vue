<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-purple-800 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-purple-900/20">
            <Store class="w-4 h-4" />
          </div>
          <span>Progres & Evidensi Cabang Toko</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1 font-medium">
          Pantau dokumentasi foto fisik Sebelum (Before) vs Sesudah (After), status GPS teknisi di toko, dan sub-item pekerjaan cabang.
        </p>
      </div>

      <!-- Quick Summary -->
      <div class="flex items-center gap-2 self-start sm:self-auto">
        <span class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 shadow-xs flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>{{ filteredOrders.length }} Cabang Toko Terdaftar</span>
        </span>
      </div>
    </div>

    <!-- Main Content Layout (Master - Detail) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
      <!-- Left Column: Stores / SPK List & Search -->
      <div class="glass-card rounded-3xl p-5 border border-white/80 shadow-glass space-y-3.5">
        <!-- Search & Filter -->
        <div class="space-y-2">
          <div class="relative">
            <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Cari toko, SPK, lokasi..."
              class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs"
            />
          </div>

          <div class="flex items-center gap-1 overflow-x-auto pb-1 text-[10px] font-bold custom-scrollbar">
            <button
              v-for="st in statusTabs"
              :key="st.value"
              @click="selectedStatus = st.value"
              :class="[
                'px-2.5 py-1 rounded-lg transition-all cursor-pointer whitespace-nowrap',
                selectedStatus === st.value
                  ? 'bg-purple-900 text-white shadow-xs'
                  : 'bg-slate-100 hover:bg-slate-200 text-slate-600'
              ]"
            >
              {{ st.label }}
            </button>
          </div>
        </div>

        <!-- Orders List -->
        <div v-if="loading" class="text-center py-12 text-slate-400 text-xs">
          Memuat daftar cabang...
        </div>

        <div v-else-if="filteredOrders.length === 0" class="text-center py-12 text-slate-400 text-xs">
          Tidak ada cabang yang sesuai filter.
        </div>

        <div v-else class="space-y-2.5 max-h-[650px] overflow-y-auto pr-1 custom-scrollbar">
          <div
            v-for="order in filteredOrders"
            :key="order.id"
            @click="handleSelectOrder(order.id)"
            :class="[
              'p-3.5 rounded-2xl border transition-all cursor-pointer space-y-1.5',
              selectedOrder?.id === order.id
                ? 'bg-purple-50/80 border-purple-300 shadow-md scale-[1.01]'
                : 'bg-white/90 border-slate-200 hover:border-purple-200 shadow-xs'
            ]"
          >
            <div class="flex items-center justify-between">
              <span class="font-mono font-bold text-[10px] text-purple-900 bg-purple-100/60 px-2 py-0.5 rounded">
                {{ order.spk_number }}
              </span>
              <StatusBadge :status="order.status" />
            </div>

            <h4 class="font-bold text-xs text-slate-900 line-clamp-1">
              {{ order.title }}
            </h4>

            <p class="text-[10px] text-slate-500 truncate flex items-center gap-1">
              <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
              <span>{{ order.location_name }}</span>
            </p>

            <div class="flex items-center justify-between text-[10px] pt-1 border-t border-slate-100 font-mono text-slate-500">
              <span>Progress: <strong class="text-slate-800">{{ order.progress_percent || 0 }}%</strong></span>
              <span v-if="order.has_issue" class="text-amber-700 font-bold flex items-center gap-0.5">
                <AlertTriangle class="w-3 h-3" /> Kendala
              </span>
              <span v-else class="text-emerald-700 font-bold">Lancar ✓</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Store Detail & Photo Evidence Comparator -->
      <div class="lg:col-span-2 space-y-5">
        <template v-if="selectedOrder">
          <!-- Store Header Card -->
          <div class="glass-card rounded-3xl p-6 border border-white/80 shadow-glass space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-4">
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-mono font-bold text-xs bg-purple-100 text-purple-900 px-2 py-0.5 rounded border border-purple-200">
                    {{ selectedOrder.spk_number }}
                  </span>
                  <StatusBadge :status="selectedOrder.status" />
                  <span class="text-xs text-slate-400 font-mono">{{ selectedOrder.area_name || 'Area Jawa Barat' }}</span>
                </div>
                <h3 class="font-black text-slate-900 text-base mt-1.5">{{ selectedOrder.title }}</h3>
                <p class="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5">
                  <MapPin class="w-3.5 h-3.5 text-purple-700 shrink-0" />
                  <span>{{ selectedOrder.location_name }}</span>
                </p>
              </div>

              <div class="flex items-center gap-2 self-start sm:self-auto">
                <button
                  v-if="selectedOrder.ba_document"
                  @click="$emit('preview-ba', selectedOrder.ba_document)"
                  class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold text-xs rounded-xl shadow-xs flex items-center gap-1.5 active:scale-95 transition-all cursor-pointer"
                >
                  <FileCheck2 class="w-4 h-4" />
                  <span>Lihat Dokumen BA</span>
                </button>
              </div>
            </div>

            <!-- Progress Tracker -->
            <div class="glass-card rounded-2xl p-4 border border-white/70">
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-800">Status & Tahapan Pengerjaan Toko:</span>
                <span class="font-mono font-bold text-xs text-purple-900">{{ selectedOrder.progress_percent || 0 }}% Selesai</span>
              </div>
              <StepperProgress :status="selectedOrder.status" :progressPercent="selectedOrder.progress_percent" />
            </div>

            <!-- Location & Field PIC Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
              <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-1.5 text-slate-600">
                <span class="font-bold text-slate-900 block text-xs">Pelaksana Lapangan SGX:</span>
                <div>PIC Teknisi: <strong class="text-slate-800">{{ selectedOrder.pic_name || 'Tim Alpha Lapangan' }}</strong></div>
                <div>Kontak PIC: <strong class="text-slate-800 font-mono">{{ selectedOrder.pic_phone || '0812-1111-2222' }}</strong></div>
                <div>Periode Kerja: <strong class="font-mono text-slate-800">{{ selectedOrder.start_date }} s/d {{ selectedOrder.deadline }}</strong></div>
              </div>

              <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-1.5 text-slate-600">
                <span class="font-bold text-slate-900 block text-xs">Verifikasi Kehadiran GPS:</span>
                <div v-if="selectedOrder.check_ins && selectedOrder.check_ins.length > 0">
                  <div class="font-bold text-emerald-700 flex items-center gap-1">
                    <CheckCircle2 class="w-3.5 h-3.5" />
                    <span>Sudah Check-In di Lokasi Toko ✓</span>
                  </div>
                  <div class="font-mono text-[11px] text-slate-600 mt-1">
                    GPS: {{ selectedOrder.check_ins[0].latitude?.toFixed(5) }}, {{ selectedOrder.check_ins[0].longitude?.toFixed(5) }}
                  </div>
                  <div class="text-[10px] text-slate-400 font-mono">
                    Waktu: {{ new Date(selectedOrder.check_ins[0].server_timestamp).toLocaleString('id-ID') }}
                  </div>
                </div>
                <div v-else class="text-amber-700 font-bold italic pt-1">
                  Menunggu kedatangan teknisi di lokasi toko.
                </div>
              </div>
            </div>
          </div>

          <!-- BEFORE vs AFTER Visual Comparator Card -->
          <div class="glass-card rounded-3xl p-6 border border-white/80 shadow-glass space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <div>
                <h4 class="font-black text-sm text-slate-900 flex items-center gap-2">
                  <Eye class="w-4 h-4 text-purple-700" />
                  <span>Perbandingan Visual Sebelum (Before) vs Sesudah (After)</span>
                </h4>
                <p class="text-[11px] text-slate-500">Verifikasi kualitas visual fisik branding dan plang toko cabang secara langsung.</p>
              </div>
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-900 border border-purple-200">
                SPLIT VIEW
              </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Before Photo Box -->
              <div class="border border-slate-200 rounded-2xl p-3 bg-amber-50/30 space-y-2">
                <div class="flex items-center justify-between">
                  <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-amber-500 text-white shadow-xs">
                    KONDISI AWAL (BEFORE)
                  </span>
                  <span class="text-[10px] font-mono text-slate-400">Sebelum Dikerjakan</span>
                </div>
                <div class="h-44 rounded-xl overflow-hidden bg-slate-900 relative">
                  <img
                    v-if="beforePhoto"
                    :src="beforePhoto.file_path"
                    alt="Foto Sebelum"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-400 text-xs">
                    <ImageIcon class="w-6 h-6 opacity-40 mb-1" />
                    <span>Foto Before Belum Tersedia</span>
                  </div>
                </div>
                <div v-if="beforePhoto" class="text-[10px] text-slate-500 font-mono truncate">
                  SHA-256: {{ beforePhoto.file_hash?.substring(0, 16) }}... ✓
                </div>
              </div>

              <!-- After Photo Box -->
              <div class="border border-slate-200 rounded-2xl p-3 bg-emerald-50/30 space-y-2">
                <div class="flex items-center justify-between">
                  <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-600 text-white shadow-xs">
                    HASIL AKHIR (AFTER)
                  </span>
                  <span class="text-[10px] font-mono text-emerald-700 font-bold">100% Selesai ✓</span>
                </div>
                <div class="h-44 rounded-xl overflow-hidden bg-slate-900 relative">
                  <img
                    v-if="afterPhoto"
                    :src="afterPhoto.file_path"
                    alt="Foto Selesai"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-400 text-xs">
                    <ImageIcon class="w-6 h-6 opacity-40 mb-1" />
                    <span>Foto After Belum Tersedia</span>
                  </div>
                </div>
                <div v-if="afterPhoto" class="text-[10px] text-slate-500 font-mono truncate">
                  SHA-256: {{ afterPhoto.file_hash?.substring(0, 16) }}... ✓
                </div>
              </div>
            </div>
          </div>

          <!-- Complete Photo Evidence Gallery -->
          <div class="glass-card rounded-3xl p-6 border border-white/80 shadow-glass space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
              <h4 class="font-black text-sm text-slate-900 flex items-center gap-2">
                <Camera class="w-4 h-4 text-purple-700" />
                <span>Seluruh Dokumentasi Foto Evidensi ({{ selectedOrder.evidence_photos?.length || 0 }} Foto)</span>
              </h4>
              <div class="flex items-center gap-2">
                <button
                  v-if="selectedOrder.evidence_photos && selectedOrder.evidence_photos.length > 0"
                  type="button"
                  @click="downloadAllPhotos"
                  class="px-3 py-1.5 bg-gradient-to-r from-purple-800 to-indigo-600 hover:from-purple-700 hover:to-indigo-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs active:scale-95 transition-all cursor-pointer"
                  title="Unduh semua foto bukti toko ini"
                >
                  <Download class="w-3.5 h-3.5" />
                  <span>Unduh Semua Foto SPK</span>
                </button>
                <span class="text-[10px] font-mono text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                  SHA-256 CERTIFIED ✓
                </span>
              </div>
            </div>

            <div v-if="selectedOrder.evidence_photos && selectedOrder.evidence_photos.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <div
                v-for="(p, pIdx) in selectedOrder.evidence_photos"
                :key="p.id"
                @click="openLightbox(p)"
                class="glass-card rounded-2xl overflow-hidden p-2 border border-white/80 shadow-xs hover:border-purple-300 transition-all group cursor-pointer relative"
              >
                <div class="h-32 rounded-xl overflow-hidden bg-slate-900 relative mb-2">
                  <img
                    :src="getFileUrl(p.file_path)"
                    :alt="`Bukti ${p.stage}`"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    @error="$event.target.src = 'https://images.unsplash.com/photo-1541888946425-d0fbb18086f6?w=300&auto=format&fit=crop&q=60'"
                  />
                  <span
                    :class="[
                      'absolute top-2 left-2 px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider',
                      p.stage === 'BEFORE' ? 'bg-amber-500 text-white' :
                      p.stage === 'PROCESS' ? 'bg-indigo-600 text-white' :
                      'bg-emerald-600 text-white'
                    ]"
                  >
                    {{ p.stage }} #{{ p.sequence || pIdx + 1 }}
                  </span>

                  <!-- DYNAMIC FLOATING CORNER DOWNLOAD BUTTON (Bottom-Right) -->
                  <div class="absolute bottom-1.5 right-1.5 z-10">
                    <button
                      type="button"
                      @click.stop="downloadSinglePhoto(p)"
                      class="w-6 h-6 rounded-full bg-slate-900/90 hover:bg-purple-700 text-white shadow-md flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95 cursor-pointer border border-white/40 backdrop-blur-xs"
                      title="Unduh Foto Resolusi Asli"
                    >
                      <Download class="w-3 h-3" />
                    </button>
                  </div>
                </div>

                <div class="px-1 space-y-1">
                  <div class="flex items-center justify-between text-[9px] font-mono text-slate-500">
                    <span class="truncate max-w-[120px]">{{ p.uploader_name || 'Tim Lapangan' }}</span>
                    <span class="text-emerald-700 font-bold">Valid ✓</span>
                  </div>
                  <div v-if="p.notes" class="text-[10px] text-slate-700 italic truncate">
                    "{{ p.notes }}"
                  </div>
                </div>
              </div>
            </div>
            <p v-else class="text-slate-400 text-xs italic text-center py-6">
              Foto dokumentasi akan muncul otomatis saat teknisi mengunggah bukti dari toko.
            </p>
          </div>

          <!-- Field Issues Log Card if any -->
          <div v-if="selectedOrder.issues && selectedOrder.issues.length > 0" class="glass-card rounded-3xl p-6 border border-white/80 shadow-glass space-y-3 bg-amber-50/30 border-amber-200">
            <div class="flex items-center justify-between border-b border-amber-200 pb-2">
              <h4 class="font-black text-xs text-amber-950 flex items-center gap-1.5">
                <AlertTriangle class="w-4 h-4 text-amber-700" />
                <span>Catatan Kendala Lapangan Cabang Ini:</span>
              </h4>
              <span class="text-[10px] font-bold text-amber-900">{{ selectedOrder.issues.length }} Catatan</span>
            </div>

            <div class="space-y-2">
              <div
                v-for="iss in selectedOrder.issues"
                :key="iss.id"
                class="p-3 bg-white rounded-xl border border-amber-200 text-xs space-y-1 shadow-xs"
              >
                <div class="flex items-center justify-between">
                  <span class="font-bold text-amber-900">{{ iss.issue_type || 'Kendala Lapangan' }}</span>
                  <span class="text-[9px] font-mono text-slate-400">{{ new Date(iss.created_at).toLocaleDateString('id-ID') }}</span>
                </div>
                <p class="text-slate-700 text-[11px]">"{{ iss.notes }}"</p>
                <div v-if="iss.resolution_notes" class="p-2 bg-emerald-50 rounded-lg text-[10px] text-emerald-900 border border-emerald-200">
                  <strong>Tindakan Solusi SGX:</strong> {{ iss.resolution_notes }}
                </div>
              </div>
            </div>
          </div>
        </template>

        <div v-else class="glass-card rounded-3xl p-16 text-center text-slate-400 text-xs font-medium border border-white/80">
          Pilih salah satu toko di panel sebelah kiri untuk memantau progres dan bukti foto evidensi.
        </div>
      </div>
    </div>

    <!-- Reusable Photo Lightbox Full-Screen Viewer -->
    <PhotoLightboxModal
      :isOpen="isLightboxOpen"
      :photos="selectedOrder?.evidence_photos || []"
      :initialIndex="selectedLightboxIndex"
      @close="isLightboxOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api, getFileUrl } from '../../services/api';
import StatusBadge from '../../components/StatusBadge.vue';
import StepperProgress from '../../components/StepperProgress.vue';
import PhotoLightboxModal from '../../components/PhotoLightboxModal.vue';
import {
  Store,
  Search,
  MapPin,
  Camera,
  CheckCircle2,
  AlertTriangle,
  FileCheck2,
  Eye,
  ImageIcon,
  Download
} from 'lucide-vue-next';

defineEmits(['preview-ba']);

const workOrders = ref([]);
const selectedOrder = ref(null);
const loading = ref(true);

const searchQuery = ref('');
const selectedStatus = ref('ALL');

const statusTabs = [
  { label: 'Semua Toko', value: 'ALL' },
  { label: 'Sedang Berjalan', value: 'IN_PROGRESS' },
  { label: 'Selesai 100%', value: 'COMPLETED' }
];

async function loadOrders() {
  loading.value = true;
  try {
    const res = await api.getWorkOrders();
    workOrders.value = res.data || [];
    if (workOrders.value.length > 0) {
      await handleSelectOrder(workOrders.value[0].id);
    }
  } catch (err) {
    console.error('Failed to load client store tasks:', err);
  } finally {
    loading.value = false;
  }
}

async function handleSelectOrder(id) {
  try {
    const detail = await api.getWorkOrderById(id);
    selectedOrder.value = detail.data;
  } catch (err) {
    console.error('Failed to get store details:', err);
  }
}

const filteredOrders = computed(() => {
  return workOrders.value.filter(wo => {
    if (selectedStatus.value === 'COMPLETED' && !['APPROVED', 'COMPLETED'].includes(wo.status)) {
      return false;
    }
    if (selectedStatus.value === 'IN_PROGRESS' && ['APPROVED', 'COMPLETED'].includes(wo.status)) {
      return false;
    }
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase();
      const matchSpk = wo.spk_number?.toLowerCase().includes(q);
      const matchTitle = wo.title?.toLowerCase().includes(q);
      const matchLoc = wo.location_name?.toLowerCase().includes(q);
      if (!matchSpk && !matchTitle && !matchLoc) return false;
    }
    return true;
  });
});

const beforePhoto = computed(() => {
  return selectedOrder.value?.evidence_photos?.find(p => p.stage === 'BEFORE') || null;
});

const afterPhoto = computed(() => {
  return selectedOrder.value?.evidence_photos?.find(p => p.stage === 'AFTER') || null;
});

/**
 * Lightbox & Photo Download Handlers
 */
const isLightboxOpen = ref(false);
const selectedLightboxIndex = ref(0);

function openLightbox(photo) {
  const allPhotos = selectedOrder.value?.evidence_photos || [];
  const idx = allPhotos.findIndex(p => p.id === photo.id);
  selectedLightboxIndex.value = idx >= 0 ? idx : 0;
  isLightboxOpen.value = true;
}

function downloadSinglePhoto(photo) {
  if (!photo?.file_path) return;
  const link = document.createElement('a');
  link.href = photo.file_path;
  const ext = photo.file_name?.split('.').pop() || 'jpg';
  const spk = selectedOrder.value?.spk_number ? `${selectedOrder.value.spk_number}_` : '';
  link.download = `${spk}${photo.stage || 'EVIDENCE'}_${photo.sequence || 1}.${ext}`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function downloadAllPhotos() {
  const allPhotos = selectedOrder.value?.evidence_photos || [];
  if (allPhotos.length === 0) {
    alert('Tidak ada foto untuk diunduh.');
    return;
  }

  allPhotos.forEach((p, idx) => {
    setTimeout(() => {
      downloadSinglePhoto(p);
    }, idx * 250);
  });
}

onMounted(() => {
  loadOrders();
});
</script>
