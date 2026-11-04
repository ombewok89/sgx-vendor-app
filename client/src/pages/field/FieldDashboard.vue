<template>
  <div class="space-y-4 sm:space-y-5 pb-12 font-sans text-slate-100">
    <!-- 1. Header CAD Blueprint Banner -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-4 sm:p-5 relative overflow-hidden shadow-xl">
      <!-- Background CAD decorative grid line -->
      <div class="absolute top-0 right-0 w-64 h-full bg-gradient-to-l from-[#FACC15]/5 to-transparent pointer-events-none"></div>

      <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-[10px] font-mono text-slate-400 uppercase tracking-wider mb-1.5">
            <span>PORTAL</span>
            <span class="text-slate-600">//</span>
            <span>OPERATOR LAPANGAN</span>
            <span class="text-slate-600">//</span>
            <span class="text-[#FACC15] font-bold">DASHBOARD KERJA</span>
          </div>
          <h1 class="text-lg sm:text-2xl font-black text-white tracking-wider flex items-center gap-2.5 font-mono uppercase">
            <div class="w-8 h-8 rounded-xs bg-[#0B0C0E] border border-[#FACC15] flex items-center justify-center text-[#FACC15] shrink-0">
              <HardHat class="w-4 h-4" />
            </div>
            <span>DASHBOARD OPERASIONAL // {{ userName }}</span>
          </h1>
          <p class="text-xs text-slate-400 font-mono mt-1 max-w-xl">
            Monitoring target tugas harian, telemetri koordinat GPS perangkat, dan alur pengerjaan lapangan terpadu.
          </p>
        </div>

        <div class="flex items-center gap-2.5 self-start md:self-center font-mono">
          <!-- Role & Live Clock -->
          <div class="bg-[#0B0C0E] border border-[#232A36] px-3 py-1.5 rounded-xs text-[11px] flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#10B981] animate-pulse"></span>
            <span class="text-[#FACC15] font-bold">ONLINE</span>
            <span class="text-slate-600">|</span>
            <Clock class="w-3.5 h-3.5 text-slate-400" />
            <span class="text-slate-200 font-bold">{{ currentTimeStr }}</span>
          </div>

          <!-- Refresh Button -->
          <button
            @click="refreshData"
            :disabled="loading"
            class="px-3 py-1.5 bg-[#1C2028] hover:bg-[#232A36] active:scale-95 text-slate-200 hover:text-white border border-[#232A36] text-[11px] font-bold rounded-xs transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer uppercase tracking-wider disabled:opacity-50"
            title="Segarkan Data"
          >
            <RefreshCw class="w-3.5 h-3.5 text-[#FACC15]" :class="{ 'animate-spin': loading }" />
            <span class="hidden sm:inline">Refresh</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 2. Radar GPS Telemetry Card (Tactical Satelit Telemetry) -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-3.5 sm:p-4 font-mono shadow-md">
      <div class="flex items-center justify-between mb-3 border-b border-[#232A36] pb-2.5">
        <div class="flex items-center gap-2">
          <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></div>
          <Radio class="w-4 h-4 text-emerald-400" />
          <h3 class="text-xs font-bold text-white uppercase tracking-wider">
            RADAR TELEMETRI GPS PERANGKAT
          </h3>
        </div>
        <button
          @click="fetchGps"
          class="text-[11px] font-bold text-[#FACC15] hover:text-[#FFE033] bg-[#0B0C0E] hover:bg-[#1C2028] border border-[#232A36] hover:border-[#FACC15]/40 px-2.5 py-1 rounded-xs transition-colors flex items-center gap-1.5 cursor-pointer active:scale-95 uppercase tracking-wider"
        >
          <RefreshCw class="w-3 h-3" />
          <span>Sinkronkan GPS</span>
        </button>
      </div>

      <div v-if="gpsCoords" class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
        <div class="bg-[#0B0C0E] p-2.5 rounded-xs border border-[#232A36] flex items-center justify-between">
          <span class="text-slate-400 text-[11px]">LATITUDE:</span>
          <span class="font-bold text-white font-mono">{{ gpsCoords.latitude.toFixed(6) }}</span>
        </div>
        <div class="bg-[#0B0C0E] p-2.5 rounded-xs border border-[#232A36] flex items-center justify-between">
          <span class="text-slate-400 text-[11px]">LONGITUDE:</span>
          <span class="font-bold text-white font-mono">{{ gpsCoords.longitude.toFixed(6) }}</span>
        </div>
        <div class="bg-[#0B0C0E] p-2.5 rounded-xs border border-emerald-500/40 flex items-center justify-between text-emerald-400">
          <span class="text-[11px] text-slate-400">AKURASI GPS:</span>
          <span class="font-bold font-mono">±{{ gpsCoords.accuracy }}m (PRESISI TINGGI)</span>
        </div>
      </div>

      <div v-else class="text-center py-3 text-xs text-slate-400 flex items-center justify-center gap-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs">
        <MapPinOff class="w-4 h-4 text-amber-400 animate-pulse" />
        <span>Mengunci sinyal satelit GPS perangkat...</span>
      </div>
    </div>

    <!-- 3. 4 Tactical KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 font-mono">
      <!-- 1. Tugas Aktif -->
      <div
        @click="$emit('navigate', 'field_tasks')"
        class="bg-[#13171F] border border-[#232A36] hover:border-[#FACC15] border-t-2 border-t-[#FACC15] rounded-xs p-3.5 sm:p-4 shadow-md transition-all cursor-pointer group flex flex-col justify-between"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">TUGAS AKTIF</span>
          <div class="w-7 h-7 rounded-xs bg-[#0B0C0E] border border-[#FACC15]/30 text-[#FACC15] flex items-center justify-center">
            <Wrench class="w-3.5 h-3.5" />
          </div>
        </div>
        <div class="mt-1">
          <div class="text-2xl sm:text-3xl font-black text-white group-hover:text-[#FACC15] transition-colors">
            {{ activeCount }}
          </div>
          <p class="text-[10px] text-[#FACC15] font-bold mt-1 flex items-center justify-between">
            <span>Dalam Pengerjaan</span>
            <span class="group-hover:translate-x-0.5 transition-transform">→</span>
          </p>
        </div>
      </div>

      <!-- 2. Perlu Revisi -->
      <div
        @click="$emit('navigate', 'field_tasks')"
        class="bg-[#13171F] border border-[#232A36] hover:border-rose-500 border-t-2 border-t-rose-500 rounded-xs p-3.5 sm:p-4 shadow-md transition-all cursor-pointer group flex flex-col justify-between"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">PERLU REVISI</span>
          <div class="w-7 h-7 rounded-xs bg-[#0B0C0E] border border-rose-500/30 text-rose-400 flex items-center justify-center">
            <AlertTriangle class="w-3.5 h-3.5" />
          </div>
        </div>
        <div class="mt-1">
          <div class="text-2xl sm:text-3xl font-black text-rose-400">
            {{ revisionCount }}
          </div>
          <p class="text-[10px] text-rose-300 font-bold mt-1 flex items-center justify-between">
            <span>Butuh Foto Ulang</span>
            <span class="group-hover:translate-x-0.5 transition-transform">→</span>
          </p>
        </div>
      </div>

      <!-- 3. Menunggu Review -->
      <div
        @click="$emit('navigate', 'field_history')"
        class="bg-[#13171F] border border-[#232A36] hover:border-sky-400 border-t-2 border-t-sky-400 rounded-xs p-3.5 sm:p-4 shadow-md transition-all cursor-pointer group flex flex-col justify-between"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">REVIEW ADMIN</span>
          <div class="w-7 h-7 rounded-xs bg-[#0B0C0E] border border-sky-400/30 text-sky-400 flex items-center justify-center">
            <CheckCircle2 class="w-3.5 h-3.5" />
          </div>
        </div>
        <div class="mt-1">
          <div class="text-2xl sm:text-3xl font-black text-sky-400">
            {{ reviewCount }}
          </div>
          <p class="text-[10px] text-sky-300 font-bold mt-1 flex items-center justify-between">
            <span>Validasi Supervisor</span>
            <span class="group-hover:translate-x-0.5 transition-transform">→</span>
          </p>
        </div>
      </div>

      <!-- 4. Selesai / BA -->
      <div
        @click="$emit('navigate', 'field_history')"
        class="bg-[#13171F] border border-[#232A36] hover:border-emerald-400 border-t-2 border-t-emerald-400 rounded-xs p-3.5 sm:p-4 shadow-md transition-all cursor-pointer group flex flex-col justify-between"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">SELESAI / BA</span>
          <div class="w-7 h-7 rounded-xs bg-[#0B0C0E] border border-emerald-400/30 text-emerald-400 flex items-center justify-center">
            <FileCheck2 class="w-3.5 h-3.5" />
          </div>
        </div>
        <div class="mt-1">
          <div class="text-2xl sm:text-3xl font-black text-emerald-400">
            {{ completedCount }}
          </div>
          <p class="text-[10px] text-emerald-300 font-bold mt-1 flex items-center justify-between">
            <span>Terbit Berita Acara</span>
            <span class="group-hover:translate-x-0.5 transition-transform">→</span>
          </p>
        </div>
      </div>
    </div>

    <!-- 4. Priority Tasks List Container (CAD Table/Cards) -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-4 sm:p-5 space-y-3.5 shadow-md">
      <div class="flex items-center justify-between border-b border-[#232A36] pb-3">
        <div class="flex items-center gap-2">
          <Briefcase class="w-4 h-4 text-[#FACC15]" />
          <h2 class="text-xs sm:text-sm font-bold text-white uppercase font-mono tracking-wider">
            // DAFTAR TUGAS PRIORITAS HARI INI
          </h2>
        </div>
        <div class="flex items-center gap-2">
          <span class="px-2 py-0.5 rounded-xs bg-[#0B0C0E] text-[#FACC15] border border-[#232A36] font-mono text-[10px] font-bold">
            {{ activeTasks.length }} SPK DITUGASKAN
          </span>
          <button
            @click="$emit('navigate', 'field_tasks')"
            class="hidden sm:flex text-xs font-mono font-bold text-[#FACC15] hover:text-white items-center gap-1 transition-colors cursor-pointer uppercase"
          >
            <span>Buka Semua Tugas</span>
            <span>→</span>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-[#0B0C0E] rounded-xs p-8 border border-[#232A36] text-center text-slate-400 font-mono">
        <Loader2 class="w-6 h-6 animate-spin mx-auto mb-2 text-[#FACC15]" />
        <p class="text-xs font-bold uppercase tracking-wider">Memuat data tugas lapangan...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="activeTasks.length === 0" class="bg-[#0B0C0E] rounded-xs p-8 border border-[#232A36] text-center font-mono space-y-2">
        <CheckCircle2 class="w-10 h-10 text-emerald-400 mx-auto mb-2" />
        <h3 class="text-sm font-bold text-white uppercase tracking-wider">SEMUA TUGAS TELAH SELESAI DIKERJAKAN</h3>
        <p class="text-xs text-slate-400 max-w-md mx-auto">
          Tidak ada pekerjaan tertunda saat ini. Anda dapat memeriksa riwayat pekerjaan dan Berita Acara yang telah terbit di tab Riwayat.
        </p>
        <button
          @click="$emit('navigate', 'field_history')"
          class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#1C2028] hover:bg-[#232A36] text-slate-200 border border-[#232A36] rounded-xs text-xs font-bold uppercase tracking-wider cursor-pointer"
        >
          <span>Buka Riwayat Tugas Selesai</span>
          <span>→</span>
        </button>
      </div>

      <!-- Active Task Cards List -->
      <div v-else class="space-y-2.5">
        <div
          v-for="(task, idx) in activeTasks"
          :key="task.id"
          class="bg-[#0E1117] hover:bg-[#161B24] border border-[#232A36] hover:border-[#FACC15]/40 rounded-xs p-3.5 sm:p-4 transition-all flex flex-col md:flex-row md:items-center justify-between gap-3 group"
        >
          <div class="space-y-1.5 flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap font-mono">
              <span class="text-[10px] font-bold text-slate-400 bg-[#1C2028] px-1.5 py-0.5 rounded-xs border border-[#232A36]">
                #{{ idx + 1 }}
              </span>
              <span class="text-[11px] font-black text-[#FACC15] bg-[#13171F] px-2 py-0.5 rounded-xs border border-[#FACC15]/30">
                {{ task.spk_number }}
              </span>
              <span
                class="text-[10px] font-bold px-2 py-0.5 rounded-xs uppercase font-mono tracking-wider"
                :class="getStatusBadgeClass(task.status)"
              >
                {{ getStatusLabel(task.status) }}
              </span>
              <span v-if="task.area" class="text-[11px] text-slate-400 font-mono flex items-center gap-1">
                <MapPin class="w-3 h-3 text-slate-500" />
                {{ task.area.name }}
              </span>
            </div>

            <h3 class="text-xs sm:text-sm font-bold text-white group-hover:text-[#FACC15] transition-colors truncate">
              {{ task.title || task.location_name }}
            </h3>

            <p class="text-[11px] text-slate-400 font-mono truncate flex items-center gap-1">
              <span class="text-[#FACC15]">📍</span>
              <span>{{ task.address || task.location_name }}</span>
            </p>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-2 self-end md:self-center font-mono shrink-0">
            <!-- Google Maps Navigation -->
            <a
              v-if="task.target_latitude && task.target_longitude"
              :href="`https://www.google.com/maps/dir/?api=1&destination=${task.target_latitude},${task.target_longitude}`"
              target="_blank"
              class="px-2.5 py-1.5 bg-[#1C2028] hover:bg-[#232A36] text-sky-400 hover:text-white border border-[#232A36] font-bold text-xs rounded-xs transition-colors flex items-center gap-1.5 active:scale-95 uppercase tracking-wider"
              title="Buka Navigasi Rute Google Maps"
            >
              <Navigation class="w-3.5 h-3.5 text-sky-400" />
              <span>RUTE MAPS</span>
            </a>

            <!-- Open Work Station Direct Action -->
            <button
              @click="$emit('navigate', 'field_tasks', task.id)"
              class="px-3 py-1.5 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] font-bold text-xs rounded-xs transition-all flex items-center gap-1.5 shadow-xs cursor-pointer active:scale-95 uppercase tracking-wider"
              title="Buka Lembar Kerja SPK Ini"
            >
              <span>KERJAKAN SPK</span>
              <ArrowRight class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 5. Standar Operasional Lapangan (SOP Teknis Lapangan) -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-4 sm:p-5 space-y-3 font-mono shadow-md">
      <div class="flex items-center gap-2 text-white font-bold text-xs border-b border-[#232A36] pb-2.5">
        <ShieldCheck class="w-4 h-4 text-[#FACC15]" />
        <span class="uppercase tracking-wider">// STANDAR OPERASIONAL LAPANGAN (SOP TEKNIS)</span>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 pt-1">
        <div class="bg-[#0B0C0E] p-3 rounded-xs border border-[#232A36] text-[11px] space-y-1">
          <span class="font-bold text-[#FACC15] block uppercase">01. CHECK-IN GPS</span>
          <p class="text-slate-400 leading-relaxed">
            Lakukan 1x Check-In GPS setibanya di titik toko/cabang dalam radius target.
          </p>
        </div>
        <div class="bg-[#0B0C0E] p-3 rounded-xs border border-[#232A36] text-[11px] space-y-1">
          <span class="font-bold text-[#FACC15] block uppercase">02. FOTO BEFORE</span>
          <p class="text-slate-400 leading-relaxed">
            Ambil minimal 1 foto kondisi awal sebelum mulai membongkar atau memasang.
          </p>
        </div>
        <div class="bg-[#0B0C0E] p-3 rounded-xs border border-[#232A36] text-[11px] space-y-1">
          <span class="font-bold text-[#FACC15] block uppercase">03. PROCESS & AFTER</span>
          <p class="text-slate-400 leading-relaxed">
            Dokumentasikan proses instalasi dan hasil akhir yang rapi, terang, serta bersih.
          </p>
        </div>
        <div class="bg-[#0B0C0E] p-3 rounded-xs border border-[#232A36] text-[11px] space-y-1">
          <span class="font-bold text-[#FACC15] block uppercase">04. PENGAJUAN REVIEW</span>
          <p class="text-slate-400 leading-relaxed">
            Periksa seluruh stempel digital (SHA-256) sebelum mengirimkan laporan ke Admin.
          </p>
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
  Loader2,
  HardHat,
  ArrowRight
} from 'lucide-vue-next';

defineEmits(['navigate', 'open-detail', 'switch-tab']);

const auth = useAuth();
const userName = computed(() => auth.state.user?.name || 'Teknisi SGX');

const loading = ref(false);
const workOrders = ref([]);
const gpsCoords = ref(null);
const currentTimeStr = ref('');
let timer = null;

const activeTasks = computed(() => {
  return workOrders.value.filter(w => ['ASSIGNED', 'CHECKED_IN', 'IN_PROGRESS', 'REVISION'].includes(w.status));
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
    case 'COMPLETED':
    case 'APPROVED':
    case 'BA_OPNAME':
      return 'bg-emerald-950/60 text-emerald-300 border border-emerald-500/40';
    case 'REVISION':
      return 'bg-rose-950/60 text-rose-300 border border-rose-500/40';
    case 'SUBMITTED':
    case 'REVIEW':
    case 'UNDER_REVIEW':
      return 'bg-purple-950/60 text-purple-300 border border-purple-500/40';
    case 'IN_PROGRESS':
      return 'bg-amber-950/60 text-[#FACC15] border border-[#FACC15]/40';
    case 'CHECKED_IN':
      return 'bg-teal-950/60 text-teal-300 border border-teal-500/40';
    case 'ASSIGNED':
      return 'bg-sky-950/60 text-sky-300 border border-sky-500/40';
    default:
      return 'bg-slate-900 text-slate-300 border border-[#232A36]';
  }
}

function getStatusLabel(status) {
  switch (status) {
    case 'ASSIGNED': return 'Ditugaskan';
    case 'CHECKED_IN': return 'Checked-In GPS';
    case 'IN_PROGRESS': return 'Sedang Dikerjakan';
    case 'SUBMITTED':
    case 'UNDER_REVIEW':
    case 'REVIEW': return 'Menunggu Review';
    case 'REVISION': return 'Perlu Revisi';
    case 'APPROVED': return 'Disetujui Admin';
    case 'BA_OPNAME': return 'BA Diterbitkan';
    case 'COMPLETED': return 'Selesai 100%';
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
