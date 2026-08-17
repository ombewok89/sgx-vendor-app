<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="glass-card rounded-3xl p-6 border border-white/80 shadow-glass bg-gradient-to-r from-purple-900/10 via-indigo-900/5 to-transparent relative overflow-hidden">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
        <div class="flex items-center gap-3.5">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-900 to-indigo-700 flex items-center justify-center text-white font-black shadow-lg shadow-purple-900/30">
            <Building2 class="w-6 h-6" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-900 border border-purple-200">
                PORTAL KLIEN / PEMBERI TUGAS
              </span>
              <span class="text-[10px] font-mono text-slate-400">PEMANTAUAN PROYEK DIGITAL</span>
            </div>
            <h2 class="text-xl font-black text-slate-900 tracking-tight mt-0.5">
              {{ clientCompany?.name || 'Klien / Principal Mitra SGX' }}
            </h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
              Pantau progres pekerjaan toko/cabang, verifikasi foto evidensi Before-After ber-GPS, dan unduh Berita Acara (BA) resmi.
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
          <button
            @click="loadClientData"
            class="p-2 bg-white hover:bg-slate-100 text-slate-600 rounded-xl border border-slate-200 shadow-xs transition-all cursor-pointer"
            title="Muat Ulang Data"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
          </button>
        </div>
      </div>
    </div>

    <!-- Executive Scorecards (Store/Branch Progress) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Total Stores/Projects -->
      <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-purple-100 text-purple-900 flex items-center justify-center shrink-0">
          <Store class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total Toko / Cabang</span>
          <div class="text-base font-black text-slate-900 font-mono truncate">
            {{ workOrders.length }} Cabang
          </div>
          <span class="text-[10px] text-purple-800 font-semibold">
            Dikerjakan oleh Kontraktor SGX
          </span>
        </div>
      </div>

      <!-- Completed 100% -->
      <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
          <CheckCircle2 class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Selesai 100% & Terbit BA</span>
          <div class="text-base font-black text-slate-900 font-mono truncate">
            {{ completedOrders.length }} Cabang
          </div>
          <span class="text-[10px] text-emerald-700 font-semibold">
            {{ completionRate }}% dari total portofolio
          </span>
        </div>
      </div>

      <!-- In Progress Active -->
      <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-900 flex items-center justify-center shrink-0">
          <Clock class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Sedang Dikerjakan</span>
          <div class="text-base font-black text-slate-900 font-mono truncate">
            {{ inProgressOrders.length }} Cabang
          </div>
          <span class="text-[10px] text-indigo-700 font-semibold">
            Tim teknisi sedang di lapangan
          </span>
        </div>
      </div>

      <!-- Field Issues & Mitigation -->
      <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-900 flex items-center justify-center shrink-0">
          <AlertTriangle class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Kendala Lapangan</span>
          <div class="text-base font-black text-slate-900 font-mono truncate">
            {{ openIssuesCount > 0 ? `${openIssuesCount} Butuh Solusi` : '0 Kendala Aktif' }}
          </div>
          <span class="text-[10px] text-amber-800 font-semibold">
            Izin security / cuaca / kelistrikan
          </span>
        </div>
      </div>
    </div>

    <!-- Main Grid: Branch Overview & Recent Evidence Stream -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column: Active Branches Progress List -->
      <div class="lg:col-span-2 glass-card rounded-3xl p-5 border border-white/80 shadow-glass space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
              <MapPin class="w-4 h-4 text-purple-700" />
              <span>Status Pengerjaan Cabang / Toko Terkini</span>
            </h3>
            <p class="text-[11px] text-slate-500">Daftar toko yang sedang dalam penanganan tim teknisi SGX.</p>
          </div>
          <button
            @click="$emit('switch-tab', 'client_tasks')"
            class="text-xs font-bold text-purple-900 hover:text-purple-700 flex items-center gap-1 cursor-pointer"
          >
            <span>Lihat Semua Toko</span>
            <ChevronRight class="w-4 h-4" />
          </button>
        </div>

        <div v-if="loading" class="text-center py-12 text-slate-400 text-xs">
          Memuat data cabang...
        </div>

        <div v-else-if="workOrders.length === 0" class="text-center py-12 text-slate-400 text-xs">
          Belum ada data pekerjaan cabang untuk akun ini.
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="wo in workOrders.slice(0, 5)"
            :key="wo.id"
            class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-purple-300 transition-all group"
          >
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="font-mono font-bold text-[10px] text-purple-900 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">
                  {{ wo.spk_number }}
                </span>
                <span class="text-[10px] text-slate-400 font-mono">{{ wo.area_name || 'Area Jawa Barat' }}</span>
              </div>
              <h4 class="font-black text-slate-900 text-xs group-hover:text-purple-900 transition-colors">
                {{ wo.title }}
              </h4>
              <p class="text-[11px] text-slate-500 truncate flex items-center gap-1">
                <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
                <span>{{ wo.location_name }}</span>
              </p>
            </div>

            <div class="flex sm:flex-col items-center sm:items-end justify-between gap-2 border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-100">
              <StatusBadge :status="wo.status" />
              <div class="text-[10px] font-mono text-slate-500 flex items-center gap-1">
                <span>Progress:</span>
                <strong class="text-slate-900 font-bold">{{ wo.progress_percent || 0 }}%</strong>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Evidence Photo Highlights & Quick Links -->
      <div class="space-y-5">
        <!-- Recent Photo Stream -->
        <div class="glass-card rounded-3xl p-5 border border-white/80 shadow-glass space-y-3.5">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
              <Camera class="w-4 h-4 text-purple-700" />
              <span>Evidensi Foto Terbaru</span>
            </h3>
            <span class="text-[10px] font-mono text-emerald-700 font-bold">GPS VERIFIED ✓</span>
          </div>

          <div v-if="recentPhotos.length > 0" class="grid grid-cols-2 gap-2.5">
            <div
              v-for="(p, idx) in recentPhotos.slice(0, 4)"
              :key="idx"
              class="group relative rounded-xl overflow-hidden bg-slate-900 border border-slate-200 aspect-video shadow-xs"
            >
              <img
                :src="getFileUrl(p.file_path)"
                :alt="p.stage"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                @error="$event.target.src = 'https://images.unsplash.com/photo-1541888946425-d0fbb18086f6?w=300&auto=format&fit=crop&q=60'"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent flex flex-col justify-end p-2 text-white">
                <span class="text-[9px] font-black uppercase tracking-wider">{{ p.stage }}</span>
                <span class="text-[8px] font-mono text-slate-300 truncate">{{ p.work_order_title || 'Toko Cabang' }}</span>
              </div>
            </div>
          </div>
          <p v-else class="text-xs text-slate-400 italic text-center py-6">
            Belum ada unggahan foto evidensi terbaru.
          </p>

          <button
            @click="$emit('switch-tab', 'client_tasks')"
            class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-all cursor-pointer"
          >
            <Eye class="w-3.5 h-3.5" />
            <span>Buka Galeri Before-After Lengkap</span>
          </button>
        </div>

        <!-- BA Opname Ready Card -->
        <div class="glass-card rounded-3xl p-5 border border-white/80 shadow-glass space-y-3 bg-gradient-to-br from-emerald-50/70 to-teal-50/40">
          <div class="flex items-center gap-2 text-emerald-900 font-bold text-xs">
            <FileCheck2 class="w-4 h-4 text-emerald-700" />
            <span>Dokumen Berita Acara (BA Opname)</span>
          </div>
          <p class="text-[11px] text-slate-600 leading-relaxed">
            Terdapat <strong>{{ completedOrders.length }} dokumen Berita Acara</strong> yang telah diverifikasi dan siap diunduh untuk kelengkapan administrasi & penagihan.
          </p>
          <button
            @click="$emit('switch-tab', 'client_ba')"
            class="w-full py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 shadow-xs active:scale-95 transition-all cursor-pointer"
          >
            <FileSpreadsheet class="w-3.5 h-3.5" />
            <span>Akses Pusat Dokumen BA Opname</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api, getFileUrl } from '../../services/api';
import { useAuth } from '../../composables/useAuth';
import StatusBadge from '../../components/StatusBadge.vue';
import {
  Building2,
  Store,
  CheckCircle2,
  Clock,
  AlertTriangle,
  MapPin,
  Camera,
  FileCheck2,
  FileSpreadsheet,
  RefreshCw,
  ChevronRight,
  Eye
} from 'lucide-vue-next';

defineEmits(['switch-tab']);

const auth = useAuth();
const workOrders = ref([]);
const recentPhotos = ref([]);
const issuesList = ref([]);
const clientCompany = ref(null);
const loading = ref(true);

async function loadClientData() {
  loading.value = true;
  try {
    const [woRes, photoRes, issRes, vRes] = await Promise.all([
      api.getWorkOrders(),
      api.getEvidencePhotos({ limit: 8 }),
      api.getFieldIssues(),
      api.getVendors()
    ]);
    workOrders.value = woRes.data || [];
    recentPhotos.value = photoRes.data || [];
    issuesList.value = issRes.data || [];

    // Identify client company name
    if (auth.state.user?.vendor_id) {
      clientCompany.value = (vRes.data || []).find(v => v.id === auth.state.user.vendor_id);
    } else if (vRes.data && vRes.data.length > 0) {
      clientCompany.value = vRes.data[0];
    }
  } catch (err) {
    console.error('Failed to load client data:', err);
  } finally {
    loading.value = false;
  }
}

const completedOrders = computed(() => {
  return workOrders.value.filter(wo => ['APPROVED', 'COMPLETED'].includes(wo.status));
});

const inProgressOrders = computed(() => {
  return workOrders.value.filter(wo => ['IN_PROGRESS', 'ASSIGNED', 'CHECKED_IN', 'SUBMITTED', 'UNDER_REVIEW'].includes(wo.status));
});

const openIssuesCount = computed(() => {
  return issuesList.value.filter(i => i.status === 'OPEN').length;
});

const completionRate = computed(() => {
  if (workOrders.value.length === 0) return 0;
  return Math.round((completedOrders.value.length / workOrders.value.length) * 100);
});

onMounted(() => {
  loadClientData();
});
</script>
