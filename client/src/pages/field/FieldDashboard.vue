<template>
  <div class="space-y-6 pb-12">
    <!-- Header Greeting Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white border border-indigo-900/40 shadow-xl relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -left-10 -top-10 w-48 h-48 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 rounded-full text-xs font-bold mb-3">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>PIC TEKNISI LAPANGAN</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
            Semangat Pagi, {{ userName }}! 👋
          </h1>
          <p class="text-slate-300 text-sm mt-1 max-w-xl">
            Pantau target pekerjaan harian, verifikasi radius GPS lokasi cabang, dan dokumentasikan hasil pengerjaan Anda.
          </p>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
          <div class="bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/10 text-xs flex items-center gap-2">
            <Clock class="w-4 h-4 text-amber-400" />
            <span class="font-bold font-mono">{{ currentTimeStr }}</span>
          </div>
          <button
            @click="refreshData"
            :disabled="loading"
            class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 active:scale-95 text-white text-xs font-bold rounded-2xl transition-all shadow-md shadow-indigo-600/30 flex items-center gap-2 cursor-pointer"
          >
            <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" />
            <span>Perbarui Data</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tugas Aktif</span>
          <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <Wrench class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-4">
          <div class="text-2xl sm:text-3xl font-black text-slate-900">{{ activeCount }}</div>
          <p class="text-[11px] text-amber-600 font-bold mt-0.5">Sedang dalam pengerjaan</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Perlu Revisi</span>
          <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
            <AlertTriangle class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-4">
          <div class="text-2xl sm:text-3xl font-black text-rose-600">{{ revisionCount }}</div>
          <p class="text-[11px] text-slate-500 font-medium mt-0.5">Butuh foto perbaikan</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Review Admin</span>
          <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <CheckCircle2 class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-4">
          <div class="text-2xl sm:text-3xl font-black text-slate-900">{{ reviewCount }}</div>
          <p class="text-[11px] text-indigo-600 font-bold mt-0.5">Menunggu verifikasi</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Selesai / BA</span>
          <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <FileCheck2 class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-4">
          <div class="text-2xl sm:text-3xl font-black text-emerald-600">{{ completedCount }}</div>
          <p class="text-[11px] text-slate-500 font-medium mt-0.5">Pekerjaan disetujui</p>
        </div>
      </div>
    </div>

    <!-- Main Grid: Urgent Tasks & GPS Proximity Widget -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left 2 Cols: Priority Tasks List -->
      <div class="lg:col-span-2 space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Briefcase class="w-5 h-5 text-indigo-600" />
            <h2 class="text-base font-extrabold text-slate-900">Pekerjaan Prioritas Hari Ini</h2>
          </div>
          <button
            @click="$emit('navigate', 'field_tasks')"
            class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 cursor-pointer"
          >
            <span>Buka Semua Tugas</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </button>
        </div>

        <div v-if="loading" class="bg-white rounded-2xl p-10 border border-slate-200 text-center text-slate-400">
          <Loader2 class="w-6 h-6 animate-spin mx-auto mb-2 text-indigo-600" />
          <p class="text-xs font-medium">Memuat tugas lapangan...</p>
        </div>

        <div v-else-if="activeTasks.length === 0" class="bg-white rounded-2xl p-8 border border-slate-200 text-center">
          <CheckCircle2 class="w-12 h-12 text-emerald-500 mx-auto mb-3" />
          <h3 class="text-base font-bold text-slate-800">Semua Tugas Sudah Selesai!</h3>
          <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
            Tidak ada pekerjaan yang tertunda saat ini. Anda dapat memeriksa riwayat pekerjaan yang telah selesai.
          </p>
          <button
            @click="$emit('navigate', 'field_history')"
            class="mt-4 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all"
          >
            Lihat Riwayat Tugas
          </button>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="task in activeTasks"
            :key="task.id"
            class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 hover:border-indigo-300 hover:shadow-md transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4"
          >
            <div class="space-y-1.5 flex-1">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-mono text-xs font-extrabold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-lg border border-indigo-100">
                  {{ task.spk_number }}
                </span>
                <span
                  class="text-[11px] font-bold px-2 py-0.5 rounded-md uppercase"
                  :class="getStatusBadgeClass(task.status)"
                >
                  {{ getStatusLabel(task.status) }}
                </span>
                <span v-if="task.area" class="text-xs text-slate-500 font-medium flex items-center gap-1">
                  <MapPin class="w-3 h-3 text-slate-400" />
                  {{ task.area.name }}
                </span>
              </div>

              <h3 class="text-sm sm:text-base font-extrabold text-slate-900">
                {{ task.title || task.location_name }}
              </h3>

              <p class="text-xs text-slate-500 line-clamp-1">
                📍 {{ task.address || task.location_name }}
              </p>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-center">
              <a
                v-if="task.target_latitude && task.target_longitude"
                :href="`https://www.google.com/maps/dir/?api=1&destination=${task.target_latitude},${task.target_longitude}`"
                target="_blank"
                class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-all"
                title="Buka Rute Google Maps"
              >
                <Navigation class="w-4 h-4" />
              </a>
              <button
                @click="$emit('navigate', 'field_tasks', task.id)"
                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm flex items-center gap-1.5 cursor-pointer active:scale-95 transition-all"
              >
                <span>Buka Tugas</span>
                <ChevronRight class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right 1 Col: Live GPS & Safety Notice -->
      <div class="space-y-6">
        <!-- Live GPS Widget -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Radio class="w-4 h-4 text-emerald-500 animate-pulse" />
              <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Radar GPS Perangkat</h3>
            </div>
            <button
              @click="fetchGps"
              class="text-[11px] font-bold text-indigo-600 hover:underline cursor-pointer"
            >
              Sinkron
            </button>
          </div>

          <div v-if="gpsCoords" class="space-y-2 text-xs">
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 font-mono space-y-1">
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
                <span class="font-bold text-emerald-600">±{{ gpsCoords.accuracy }} meter (Tinggi)</span>
              </div>
            </div>
            <p class="text-[11px] text-slate-500 italic">
              GPS otomatis dicantumkan saat mengambil foto bukti untuk menjamin keaslian forensik digital.
            </p>
          </div>

          <div v-else class="text-center py-4 text-xs text-slate-400">
            <MapPinOff class="w-6 h-6 mx-auto mb-1 text-slate-300" />
            <span>Sedang mengunci sinyal GPS perangkat...</span>
          </div>
        </div>

        <!-- Field Safety & SLA Notice -->
        <div class="bg-indigo-50/70 border border-indigo-100 rounded-2xl p-5 space-y-2.5">
          <div class="flex items-center gap-2 text-indigo-900 font-bold text-xs">
            <ShieldCheck class="w-4 h-4 text-indigo-600" />
            <span>STANDAR OPERASIONAL LAPANGAN</span>
          </div>
          <ul class="text-[11px] text-indigo-800 space-y-1.5 list-disc list-inside">
            <li>Lakukan <b>Check-In GPS</b> setibanya di lokasi toko/cabang.</li>
            <li>Ambil minimal 1 foto <b>BEFORE</b> sebelum mulai membongkar/memasang.</li>
            <li>Pastikan foto <b>AFTER</b> terlihat rapi, bersih, dan terang.</li>
            <li>Periksa stempel tanggal & koordinat pada foto sebelum submit.</li>
          </ul>
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
  ArrowRight,
  ChevronRight,
  MapPin,
  Radio,
  MapPinOff,
  ShieldCheck,
  Navigation,
  Loader2
} from 'lucide-vue-next';

const emit = defineEmits(['navigate']);

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
