<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-purple-500 selection:text-white flex flex-col">
    
    <!-- Top Brand Header Bar -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur-md sticky top-0 z-40 px-4 py-3 shadow-md">
      <div class="max-w-5xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-purple-800 via-indigo-700 to-emerald-500 flex items-center justify-center text-white font-black text-sm shadow-md shadow-purple-900/30">
            SGX
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="font-extrabold text-sm text-white tracking-wide">LIVE WORK TRACKER</h1>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                REALTIME
              </span>
            </div>
            <p class="text-[11px] text-slate-400">PT Sinar Graha Kreatif — Sistem Pemantauan Progres Cabang</p>
          </div>
        </div>

        <button
          @click="fetchTrackingData"
          :disabled="loading"
          class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-all border border-slate-700 shadow-xs cursor-pointer active:scale-95"
          title="Segarkan Data"
        >
          <RefreshCw :class="['w-4 h-4', loading ? 'animate-spin text-purple-400' : '']" />
        </button>
      </div>
    </header>

    <!-- Main Content Stage -->
    <main class="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-6 md:p-8 space-y-6">
      
      <!-- Loading State -->
      <div v-if="loading && !wo" class="py-24 flex flex-col items-center justify-center space-y-3">
        <Loader2 class="w-10 h-10 animate-spin text-purple-500" />
        <p class="text-xs font-mono text-slate-400 tracking-wider">Memuat data pelacakan SPK...</p>
      </div>

      <!-- Error / Inactive State -->
      <div v-else-if="errorMessage" class="py-20 text-center space-y-4 max-w-md mx-auto">
        <div class="w-16 h-16 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 flex items-center justify-center mx-auto">
          <ShieldAlert class="w-8 h-8" />
        </div>
        <h2 class="text-lg font-bold text-white">Akses Pemantauan Tidak Tersedia</h2>
        <p class="text-xs text-slate-400 leading-relaxed">{{ errorMessage }}</p>
        <div class="pt-2">
          <a
            href="/"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-900 hover:bg-purple-800 text-white text-xs font-bold transition-all shadow-md"
          >
            <Home class="w-4 h-4" />
            <span>Kembali ke Beranda</span>
          </a>
        </div>
      </div>

      <!-- Loaded Work Order Tracking View -->
      <div v-else-if="wo" class="space-y-6 animate-fade-in">
        
        <!-- Work Order Hero Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-7 shadow-xl relative overflow-hidden">
          <!-- Background Glow Accent -->
          <div class="absolute -top-24 -right-24 w-64 h-64 bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>

          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800/80 pb-5">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-bold text-purple-400 bg-purple-950/80 px-2.5 py-0.5 rounded-lg border border-purple-800/50">
                  {{ wo.spk_number }}
                </span>
                <span class="text-xs font-medium text-slate-400">• {{ wo.area_name || '-' }}</span>
              </div>
              <h2 class="text-xl sm:text-2xl font-black text-white leading-tight">{{ wo.location_name }}</h2>
              <p class="text-xs text-slate-300">{{ wo.title }}</p>
            </div>

            <!-- Dynamic Status Badge -->
            <div class="shrink-0 flex items-center gap-2">
              <div
                :class="[
                  'px-3.5 py-1.5 rounded-xl border text-xs font-black uppercase tracking-wider flex items-center gap-2 shadow-xs',
                  getStatusBadgeClass(wo.status)
                ]"
              >
                <span class="w-2 h-2 rounded-full animate-ping" :class="getStatusDotClass(wo.status)"></span>
                <span>{{ getStatusLabel(wo.status) }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Metadata Grid -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-5 text-xs">
            <div>
              <span class="text-slate-500 block text-[11px] mb-0.5">Perusahaan Klien:</span>
              <strong class="text-slate-200 font-semibold flex items-center gap-1.5">
                <Building2 class="w-3.5 h-3.5 text-purple-400 shrink-0" />
                <span class="truncate">{{ wo.vendor?.name || 'Client SGX' }}</span>
              </strong>
            </div>
            <div>
              <span class="text-slate-500 block text-[11px] mb-0.5">Teknisi / Tim Lapangan:</span>
              <strong class="text-slate-200 font-semibold flex items-center gap-1.5">
                <User class="w-3.5 h-3.5 text-emerald-400 shrink-0" />
                <span class="truncate">{{ wo.pic?.name || 'Tim SGX' }}</span>
              </strong>
            </div>
            <div>
              <span class="text-slate-500 block text-[11px] mb-0.5">Target Penyelesaian (SLA):</span>
              <strong class="text-slate-200 font-semibold flex items-center gap-1.5 font-mono">
                <Calendar class="w-3.5 h-3.5 text-amber-400 shrink-0" />
                <span>{{ wo.deadline || '-' }}</span>
              </strong>
            </div>
            <div>
              <span class="text-slate-500 block text-[11px] mb-0.5">Presensi GPS Cabang:</span>
              <strong class="font-semibold flex items-center gap-1.5" :class="wo.check_in ? 'text-emerald-400' : 'text-amber-400'">
                <MapPin class="w-3.5 h-3.5 shrink-0" />
                <span>{{ wo.check_in ? 'Terverifikasi di Lokasi' : 'Menunggu Check-In' }}</span>
              </strong>
            </div>
          </div>

          <!-- Progress Bar if in progress -->
          <div class="mt-6 pt-4 border-t border-slate-800/80">
            <div class="flex items-center justify-between text-xs font-bold mb-1.5">
              <span class="text-slate-400">Total Progres Fisik:</span>
              <span class="font-mono text-purple-400">{{ wo.progress_percent }}%</span>
            </div>
            <div class="w-full h-2.5 bg-slate-800 rounded-full overflow-hidden p-0.5">
              <div
                class="h-full rounded-full bg-gradient-to-r from-purple-600 via-indigo-500 to-emerald-500 transition-all duration-700"
                :style="{ width: `${Math.min(100, Math.max(5, wo.progress_percent))}%` }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Real-Time Milestone Stepper -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
          <h3 class="font-black text-sm uppercase text-slate-300 tracking-wider flex items-center gap-2">
            <Activity class="w-4 h-4 text-purple-400" />
            <span>Tahapan Pengerjaan Real-Time</span>
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-2">
            <!-- Step 1: Penugasan -->
            <div class="p-3.5 rounded-2xl border bg-slate-950/60 border-slate-800 space-y-1">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-xs font-bold">
                  ✓
                </div>
                <span class="font-bold text-xs text-white">1. Penunjukan</span>
              </div>
              <p class="text-[11px] text-slate-400 pl-8">SPK resmi diterbitkan untuk tim teknisi.</p>
            </div>

            <!-- Step 2: Check-In -->
            <div
              :class="[
                'p-3.5 rounded-2xl border space-y-1',
                wo.check_in 
                  ? 'bg-slate-950/60 border-emerald-500/40 text-emerald-300' 
                  : 'bg-slate-950/30 border-slate-800/80 text-slate-500'
              ]"
            >
              <div class="flex items-center gap-2">
                <div
                  :class="[
                    'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold border',
                    wo.check_in ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-slate-800 text-slate-500 border-slate-700'
                  ]"
                >
                  {{ wo.check_in ? '✓' : '2' }}
                </div>
                <span class="font-bold text-xs" :class="wo.check_in ? 'text-white' : 'text-slate-400'">2. Check-In GPS</span>
              </div>
              <p class="text-[11px] text-slate-400 pl-8">
                {{ wo.check_in ? `Hadir di radius toko (${wo.check_in.check_in_time})` : 'Teknisi menuju lokasi toko.' }}
              </p>
            </div>

            <!-- Step 3: Evidensi Foto -->
            <div
              :class="[
                'p-3.5 rounded-2xl border space-y-1',
                wo.photos?.length > 0 
                  ? 'bg-slate-950/60 border-purple-500/40' 
                  : 'bg-slate-950/30 border-slate-800/80'
              ]"
            >
              <div class="flex items-center gap-2">
                <div
                  :class="[
                    'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold border',
                    wo.photos?.length > 0 ? 'bg-purple-500/20 text-purple-400 border-purple-500/30' : 'bg-slate-800 text-slate-500 border-slate-700'
                  ]"
                >
                  {{ wo.photos?.length > 0 ? '✓' : '3' }}
                </div>
                <span class="font-bold text-xs" :class="wo.photos?.length > 0 ? 'text-white' : 'text-slate-400'">3. Dokumentasi</span>
              </div>
              <p class="text-[11px] text-slate-400 pl-8">
                {{ wo.photos?.length > 0 ? `${wo.photos.length} foto bukti terverifikasi.` : 'Menunggu unggahan foto.' }}
              </p>
            </div>

            <!-- Step 4: Selesai / BA -->
            <div
              :class="[
                'p-3.5 rounded-2xl border space-y-1',
                ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status) 
                  ? 'bg-slate-950/60 border-emerald-500/40' 
                  : 'bg-slate-950/30 border-slate-800/80'
              ]"
            >
              <div class="flex items-center gap-2">
                <div
                  :class="[
                    'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold border',
                    ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status) ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-slate-800 text-slate-500 border-slate-700'
                  ]"
                >
                  {{ ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status) ? '✓' : '4' }}
                </div>
                <span class="font-bold text-xs" :class="['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status) ? 'text-white' : 'text-slate-400'">4. Pengesahan</span>
              </div>
              <p class="text-[11px] text-slate-400 pl-8">
                {{ wo.ba_document ? `BA No. ${wo.ba_document.ba_number}` : 'Pekerjaan selesai & BA terbit.' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Photo Documentation Gallery -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
            <div>
              <h3 class="font-black text-sm uppercase text-slate-200 tracking-wider flex items-center gap-2">
                <Camera class="w-4 h-4 text-purple-400" />
                <span>Dokumentasi Foto Lapangan</span>
              </h3>
              <p class="text-[11px] text-slate-400">Bukti visual digital dengan enkripsi stempel metadata resmi</p>
            </div>

            <!-- Stage Filter Tabs -->
            <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-800 text-xs font-bold">
              <button
                @click="activeStage = 'ALL'"
                :class="[
                  'px-3 py-1.5 rounded-lg transition-all cursor-pointer',
                  activeStage === 'ALL' ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-400 hover:text-white'
                ]"
              >
                Semua ({{ wo.photos?.length || 0 }})
              </button>
              <button
                @click="activeStage = 'BEFORE'"
                :class="[
                  'px-3 py-1.5 rounded-lg transition-all cursor-pointer',
                  activeStage === 'BEFORE' ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-400 hover:text-white'
                ]"
              >
                Before
              </button>
              <button
                @click="activeStage = 'PROCESS'"
                :class="[
                  'px-3 py-1.5 rounded-lg transition-all cursor-pointer',
                  activeStage === 'PROCESS' ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-400 hover:text-white'
                ]"
              >
                Process
              </button>
              <button
                @click="activeStage = 'AFTER'"
                :class="[
                  'px-3 py-1.5 rounded-lg transition-all cursor-pointer',
                  activeStage === 'AFTER' ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-400 hover:text-white'
                ]"
              >
                After
              </button>
            </div>
          </div>

          <!-- Photo Grid -->
          <div v-if="filteredPhotos.length === 0" class="py-12 text-center text-xs text-slate-500">
            <Camera class="w-8 h-8 mx-auto mb-2 text-slate-700" />
            <p>Belum ada foto dokumentasi untuk tahap yang dipilih.</p>
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div
              v-for="photo in filteredPhotos"
              :key="photo.id"
              class="group relative bg-slate-950 border border-slate-800 hover:border-purple-500/50 rounded-2xl overflow-hidden transition-all duration-300 shadow-md flex flex-col"
            >
              <!-- Photo Image -->
              <div class="relative aspect-4/3 overflow-hidden bg-slate-900 cursor-pointer" @click="activeLightboxPhoto = photo">
                <img
                  :src="getFileUrl(photo.file_path)"
                  :alt="`Dokumentasi ${photo.stage}`"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                  loading="lazy"
                  @error="$event.target.src = 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=500&auto=format&fit=crop&q=60'"
                />

                <!-- Stage Badge -->
                <div class="absolute top-2 left-2">
                  <span
                    :class="[
                      'px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border shadow-md',
                      photo.stage === 'BEFORE' ? 'bg-amber-950/90 text-amber-300 border-amber-500/40' :
                      photo.stage === 'PROCESS' ? 'bg-blue-950/90 text-blue-300 border-blue-500/40' :
                      'bg-emerald-950/90 text-emerald-300 border-emerald-500/40'
                    ]"
                  >
                    {{ photo.stage }}
                  </span>
                </div>

                <!-- Hover Overlay Icon -->
                <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                  <Maximize2 class="w-6 h-6 drop-shadow-md" />
                </div>
              </div>

              <!-- Photo Metadata Card -->
              <div class="p-3 bg-slate-900/90 border-t border-slate-800 text-[11px] space-y-1.5 flex-1 flex flex-col justify-between">
                <div class="space-y-0.5">
                  <div class="flex items-center justify-between text-slate-400 font-mono text-[10px]">
                    <span class="flex items-center gap-1">
                      <Clock class="w-3 h-3 text-slate-500" />
                      {{ photo.captured_at || '-' }}
                    </span>
                    <span v-if="photo.latitude" class="text-purple-400">
                      GPS ✓
                    </span>
                  </div>
                  <p v-if="photo.notes" class="text-slate-300 line-clamp-2 text-xs pt-1">{{ photo.notes }}</p>
                </div>

                <div v-if="photo.file_hash" class="pt-1.5 border-t border-slate-800/80 flex items-center justify-between text-[9px] font-mono text-slate-500">
                  <span class="truncate">Hash: {{ photo.file_hash.substring(0, 14) }}...</span>
                  <span class="text-emerald-400 shrink-0 font-bold">Terverifikasi</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Work Order Items List if available -->
        <div v-if="wo.items && wo.items.length > 0" class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-3">
          <h3 class="font-black text-sm uppercase text-slate-300 tracking-wider flex items-center gap-2">
            <CheckSquare class="w-4 h-4 text-purple-400" />
            <span>Daftar Sub-Pekerjaan & Lingkup Kerja</span>
          </h3>
          <div class="divide-y divide-slate-800">
            <div v-for="item in wo.items" :key="item.id" class="py-2.5 flex items-center justify-between text-xs">
              <div class="space-y-0.5">
                <span class="font-bold text-slate-200 block">{{ item.item_name }}</span>
                <span v-if="item.notes" class="text-[11px] text-slate-400">{{ item.notes }}</span>
              </div>
              <span
                :class="[
                  'px-2 py-0.5 rounded-md text-[10px] font-bold uppercase',
                  item.status === 'COMPLETED' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400'
                ]"
              >
                {{ item.status }}
              </span>
            </div>
          </div>
        </div>

      </div>
    </main>

    <!-- Photo Lightbox Modal -->
    <Teleport to="body">
      <div
        v-if="activeLightboxPhoto"
        class="fixed inset-0 z-50 bg-black/95 flex flex-col items-center justify-center p-4 animate-fade-in"
        @click.self="activeLightboxPhoto = null"
      >
        <button
          @click="activeLightboxPhoto = null"
          class="absolute top-4 right-4 p-2.5 rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors cursor-pointer"
        >
          <X class="w-6 h-6" />
        </button>

        <div class="max-w-4xl max-h-[85vh] flex flex-col items-center">
          <img
            :src="getFileUrl(activeLightboxPhoto.file_path)"
            :alt="`Bukti ${activeLightboxPhoto.stage}`"
            class="max-w-full max-h-[75vh] object-contain rounded-xl shadow-2xl border border-white/10 mb-3"
          />
          <div class="text-center text-xs text-slate-300 space-y-1">
            <div class="font-bold text-sm text-white">
              Tahap: <span class="text-purple-400 uppercase font-black">{{ activeLightboxPhoto.stage }}</span>
            </div>
            <p v-if="activeLightboxPhoto.notes">{{ activeLightboxPhoto.notes }}</p>
            <p class="font-mono text-[11px] text-slate-400">
              Waktu: {{ activeLightboxPhoto.captured_at || '-' }} | GPS: {{ activeLightboxPhoto.latitude ? `${activeLightboxPhoto.latitude}, ${activeLightboxPhoto.longitude}` : 'Tidak tercatat' }}
            </p>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-6 text-center text-xs text-slate-500 bg-slate-950">
      <p class="font-semibold text-slate-400">PT Sinar Graha Kreatif</p>
      <p class="text-[11px] mt-0.5">Enterprise Work Order & Real-Time Evidence Verification System</p>
    </footer>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { RefreshCw, Loader2, ShieldAlert, Home, Building2, User, Calendar, MapPin, Activity, Camera, Maximize2, Clock, CheckSquare, X } from 'lucide-vue-next';
import { api, getFileUrl } from '../../services/api';

const props = defineProps({
  token: {
    type: String,
    required: true
  }
});

const loading = ref(true);
const errorMessage = ref('');
const wo = ref(null);
const activeStage = ref('ALL');
const activeLightboxPhoto = ref(null);

const filteredPhotos = computed(() => {
  if (!wo.value?.photos) return [];
  if (activeStage.value === 'ALL') return wo.value.photos;
  return wo.value.photos.filter(p => p.stage === activeStage.value);
});

function getStatusBadgeClass(status) {
  switch (status) {
    case 'APPROVED':
    case 'BA_OPNAME':
    case 'COMPLETED':
      return 'bg-emerald-950 text-emerald-300 border-emerald-500/40';
    case 'SUBMITTED':
    case 'UNDER_REVIEW':
    case 'REVIEW':
      return 'bg-blue-950 text-blue-300 border-blue-500/40';
    case 'IN_PROGRESS':
    case 'CHECKED_IN':
      return 'bg-purple-950 text-purple-300 border-purple-500/40';
    case 'REVISION':
      return 'bg-rose-950 text-rose-300 border-rose-500/40';
    default:
      return 'bg-slate-900 text-slate-300 border-slate-700';
  }
}

function getStatusDotClass(status) {
  switch (status) {
    case 'APPROVED':
    case 'BA_OPNAME':
    case 'COMPLETED':
      return 'bg-emerald-400';
    case 'SUBMITTED':
    case 'UNDER_REVIEW':
    case 'REVIEW':
      return 'bg-blue-400';
    case 'IN_PROGRESS':
    case 'CHECKED_IN':
      return 'bg-purple-400';
    case 'REVISION':
      return 'bg-rose-400';
    default:
      return 'bg-slate-400';
  }
}

function getStatusLabel(status) {
  switch (status) {
    case 'APPROVED':
    case 'BA_OPNAME':
    case 'COMPLETED':
      return 'Disetujui 100%';
    case 'SUBMITTED':
    case 'UNDER_REVIEW':
    case 'REVIEW':
      return 'Sedang Direview';
    case 'IN_PROGRESS':
      return 'Dalam Pengerjaan';
    case 'CHECKED_IN':
      return 'Teknisi di Lokasi';
    case 'REVISION':
      return 'Perbaikan Revisi';
    case 'ASSIGNED':
      return 'Telah Ditugaskan';
    default:
      return status || 'Draft';
  }
}

async function fetchTrackingData() {
  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await api.getPublicTracking(props.token);
    if (res.success && res.data) {
      wo.value = res.data;
    } else {
      errorMessage.value = res.message || 'Data pemantauan tidak ditemukan.';
    }
  } catch (err) {
    errorMessage.value = err.message || 'Gagal memuat data pemantauan SPK. Pastikan tautan benar.';
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchTrackingData();
});
</script>
