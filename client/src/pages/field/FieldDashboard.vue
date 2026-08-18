<template>
  <div class="space-y-4 sm:space-y-5 pb-12">
    <!-- Compact Header Greeting Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-4 sm:p-5 text-white border border-indigo-900/40 shadow-md relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-36 h-36 bg-indigo-600/20 rounded-full blur-2xl pointer-events-none"></div>
      <div class="absolute -left-10 -top-10 w-36 h-36 bg-sky-500/10 rounded-full blur-2xl pointer-events-none"></div>

      <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 rounded-full text-[10px] font-bold mb-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>PIC TEKNISI LAPANGAN</span>
          </div>
          <h1 class="text-lg sm:text-xl font-extrabold tracking-tight">
            Semangat Pagi, {{ userName }}! 👋
          </h1>
          <p class="text-slate-300 text-xs mt-0.5 max-w-lg">
            Pantau target pekerjaan harian dan verifikasi radius GPS lokasi cabang.
          </p>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-center">
          <div class="bg-white/10 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/10 text-[11px] flex items-center gap-1.5">
            <Clock class="w-3.5 h-3.5 text-amber-400" />
            <span class="font-bold font-mono">{{ currentTimeStr }}</span>
          </div>
          <button
            @click="refreshData"
            :disabled="loading"
            class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white text-[11px] font-bold rounded-xl transition-all shadow-sm flex items-center gap-1.5 cursor-pointer"
          >
            <RefreshCw class="w-3 h-3" :class="{ 'animate-spin': loading }" />
            <span>Perbarui</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tugas Aktif</span>
          <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
            <Wrench class="w-4 h-4" />
          </div>
        </div>
        <div class="mt-2.5">
          <div class="text-xl sm:text-2xl font-black text-slate-900">{{ activeCount }}</div>
          <p class="text-[10px] text-amber-600 font-bold mt-0.5">Sedang dalam pengerjaan</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Perlu Revisi</span>
          <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
            <AlertTriangle class="w-4 h-4" />
          </div>
        </div>
        <div class="mt-2.5">
          <div class="text-xl sm:text-2xl font-black text-rose-600">{{ revisionCount }}</div>
          <p class="text-[10px] text-slate-500 font-medium mt-0.5">Butuh foto perbaikan</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Review Admin</span>
          <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <CheckCircle2 class="w-4 h-4" />
          </div>
        </div>
        <div class="mt-2.5">
          <div class="text-xl sm:text-2xl font-black text-slate-900">{{ reviewCount }}</div>
          <p class="text-[10px] text-indigo-600 font-bold mt-0.5">Menunggu verifikasi</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Selesai / BA</span>
          <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <FileCheck2 class="w-4 h-4" />
          </div>
        </div>
        <div class="mt-2.5">
          <div class="text-xl sm:text-2xl font-black text-emerald-600">{{ completedCount }}</div>
          <p class="text-[10px] text-slate-500 font-medium mt-0.5">Pekerjaan disetujui</p>
        </div>
      </div>
    </div>

    <!-- Main Grid: Priority Tasks & GPS Proximity Widget -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-5">
      <!-- Left 2 Cols: Priority Tasks List (Monitoring Only - No Approve / Open Button) -->
      <div class="lg:col-span-2 space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Briefcase class="w-4 h-4 text-indigo-600" />
            <h2 class="text-sm font-extrabold text-slate-900">Pekerjaan Prioritas Hari Ini</h2>
          </div>
          <span class="text-[11px] font-semibold text-slate-500">
            {{ activeTasks.length }} SPK Ditugaskan
          </span>
        </div>

        <div v-if="loading" class="bg-white rounded-2xl p-8 border border-slate-200 text-center text-slate-400">
          <Loader2 class="w-5 h-5 animate-spin mx-auto mb-2 text-indigo-600" />
          <p class="text-xs font-medium">Memuat tugas lapangan...</p>
        </div>

        <div v-else-if="activeTasks.length === 0" class="bg-white rounded-2xl p-6 border border-slate-200 text-center">
          <CheckCircle2 class="w-10 h-10 text-emerald-500 mx-auto mb-2" />
          <h3 class="text-sm font-bold text-slate-800">Semua Tugas Sudah Selesai!</h3>
          <p class="text-xs text-slate-500 max-w-md mx-auto mt-0.5">
            Tidak ada pekerjaan yang tertunda saat ini. Anda dapat memeriksa riwayat pekerjaan yang telah selesai.
          </p>
        </div>

        <div v-else class="space-y-2.5">
          <div
            v-for="task in activeTasks"
            :key="task.id"
            class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-200 hover:border-indigo-200 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3"
          >
            <div class="space-y-1 flex-1">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-mono text-[11px] font-extrabold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                  {{ task.spk_number }}
                </span>
                <span
                  class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase"
                  :class="getStatusBadgeClass(task.status)"
                >
                  {{ getStatusLabel(task.status) }}
                </span>
                <span v-if="task.area" class="text-[11px] text-slate-500 font-medium flex items-center gap-1">
                  <MapPin class="w-3 h-3 text-slate-400" />
                  {{ task.area.name }}
                </span>
              </div>

              <h3 class="text-xs sm:text-sm font-bold text-slate-900">
                {{ task.title || task.location_name }}
              </h3>

              <p class="text-[11px] text-slate-500 line-clamp-1">
                📍 {{ task.address || task.location_name }}
              </p>
            </div>

            <!-- Action: Navigation Only (No Buka Tugas / No Approve Action) -->
            <div class="flex items-center gap-2 self-end sm:self-center">
              <a
                v-if="task.target_latitude && task.target_longitude"
                :href="`https://www.google.com/maps/dir/?api=1&destination=${task.target_latitude},${task.target_longitude}`"
                target="_blank"
                class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] rounded-lg transition-all flex items-center gap-1.5"
                title="Buka Rute Google Maps"
              >
                <Navigation class="w-3.5 h-3.5 text-indigo-600" />
                <span>Rute Maps</span>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Right 1 Col: Live GPS Widget -->
      <div class="space-y-4">
        <!-- Live GPS Widget -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-1.5">
              <Radio class="w-3.5 h-3.5 text-emerald-500 animate-pulse" />
              <h3 class="text-[11px] font-bold text-slate-800 uppercase tracking-wider">Radar GPS Perangkat</h3>
            </div>
            <button
              @click="fetchGps"
              class="text-[10px] font-bold text-indigo-600 hover:underline cursor-pointer"
            >
              Sinkron
            </button>
          </div>

          <div v-if="gpsCoords" class="space-y-1.5 text-xs">
            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 font-mono text-[11px] space-y-1">
              <div class="flex justify-between">
                <span class="text-slate-500">Latitude:</span>
                <span class="font-bold text-slate-800">{{ gpsCoords.latitude.toFixed(6) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Longitude:</span>
                <span class="font-bold text-slate-800">{{ gpsCoords.longitude.toFixed(6) }}</span>
              </div>
              <div class="flex justify-between pt-1 border-t border-slate-200">
                <span class="text-slate-500">Akurasi:</span>
                <span class="font-bold text-emerald-600">±{{ gpsCoords.accuracy }}m (Tinggi)</span>
              </div>
            </div>
            <p class="text-[10px] text-slate-500 italic">
              GPS otomatis dicantumkan saat mengambil foto bukti di menu Pekerjaan.
            </p>
          </div>

          <div v-else class="text-center py-3 text-xs text-slate-400">
            <MapPinOff class="w-5 h-5 mx-auto mb-1 text-slate-300" />
            <span class="text-[11px]">Sedang mengunci sinyal GPS...</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Standar Operasional Lapangan (Moved to Bottom Full-Width) -->
    <div class="bg-indigo-50/70 border border-indigo-100 rounded-2xl p-4 sm:p-5 space-y-2">
      <div class="flex items-center gap-2 text-indigo-900 font-bold text-xs">
        <ShieldCheck class="w-4 h-4 text-indigo-600" />
        <span>STANDAR OPERASIONAL LAPANGAN (SOP)</span>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 pt-1">
        <div class="bg-white/80 p-2.5 rounded-xl border border-indigo-100/60 text-[11px] text-indigo-950">
          <span class="font-bold text-indigo-700 block mb-0.5">1. Check-In GPS</span>
          Lakukan 1x Check-In GPS setibanya di lokasi toko/cabang.
        </div>
        <div class="bg-white/80 p-2.5 rounded-xl border border-indigo-100/60 text-[11px] text-indigo-950">
          <span class="font-bold text-indigo-700 block mb-0.5">2. Foto BEFORE</span>
          Ambil minimal 1 foto sebelum mulai membongkar/memasang.
        </div>
        <div class="bg-white/80 p-2.5 rounded-xl border border-indigo-100/60 text-[11px] text-indigo-950">
          <span class="font-bold text-indigo-700 block mb-0.5">3. Foto PROCESS & AFTER</span>
          Pastikan foto rapi, bersih, dan tampak terang.
        </div>
        <div class="bg-white/80 p-2.5 rounded-xl border border-indigo-100/60 text-[11px] text-indigo-950">
          <span class="font-bold text-indigo-700 block mb-0.5">4. Pengajuan Review</span>
          Periksa stempel tanggal & koordinat sebelum kirim ke Admin.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useAuth } from '../../composables/useAuth';
import { api } from '../../services/api';
import {
  Clock,
  RefreshCw,
  Wrench,
  AlertTriangle,
  CheckCircle2,
  FileCheck2,
  Briefcase,
  MapPin,
  Radio,
  MapPinOff,
  ShieldCheck,
  Navigation,
  Loader2
} from 'lucide-vue-next';

defineEmits(['navigate']);

const auth = useAuth();
const userName = computed(() => auth.state.user?.name || 'Teknisi SGX');

const loading = ref(false);
const workOrders = ref([]);
const gpsCoords = ref(null);
const currentTimeStr = ref('');
let timer = null;

const activeTasks = computed(() => {
  return workOrders.value.filter(w => ['ASSIGNED', 'IN_PROGRESS', 'REVISION'].includes(w.status));
});

const activeCount = computed(() => activeTasks.value.length);
const revisionCount = computed(() => workOrders.value.filter(w => w.status === 'REVISION').length);
const reviewCount = computed(() => workOrders.value.filter(w => ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'].includes(w.status)).length);
const completedCount = computed(() => workOrders.value.filter(w => ['APPROVED', 'COMPLETED', 'BA_OPNAME'].includes(w.status)).length);

function updateTime() {
  const now = new Date();
  const options = { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' };
  currentTimeStr.value = now.toLocaleDateString('id-ID', options);
}

function fetchGps() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        gpsCoords.value = {
          latitude: pos.coords.latitude,
          longitude: pos.coords.longitude,
          accuracy: Math.round(pos.coords.accuracy)
        };
      },
      (err) => {
        console.warn('GPS signal pending:', err.message);
      },
      { enableHighAccuracy: true, timeout: 5000 }
    );
  }
}

async function refreshData() {
  loading.value = true;
  try {
    const res = await api.getWorkOrders();
    if (res.data) {
      workOrders.value = res.data;
    }
  } catch (err) {
    console.error('Failed to load dashboard data:', err);
  } finally {
    loading.value = false;
  }
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'ASSIGNED': return 'bg-sky-100 text-sky-800 border border-sky-200';
    case 'IN_PROGRESS': return 'bg-amber-100 text-amber-800 border border-amber-200';
    case 'REVISION': return 'bg-rose-100 text-rose-800 border border-rose-200';
    default: return 'bg-slate-100 text-slate-700';
  }
}

function getStatusLabel(status) {
  switch (status) {
    case 'ASSIGNED': return 'Ditugaskan';
    case 'IN_PROGRESS': return 'Sedang Dikerjakan';
    case 'REVISION': return 'Perlu Revisi';
    default: return status;
  }
}

onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 10000);
  fetchGps();
  refreshData();
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>
