<template>
  <div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="bg-gradient-to-r from-slate-900 via-amber-950/80 to-slate-900 rounded-3xl p-5 sm:p-6 text-white border border-amber-900/40 shadow-lg relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-amber-600/20 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -left-10 -top-10 w-48 h-48 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <div class="inline-flex items-center gap-2 px-2.5 py-0.5 bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-full text-[10px] font-bold mb-1.5">
            <AlertTriangle class="w-3 h-3" />
            <span>TRANSPARANSI KENDALA TEKNIS CABANG</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-black tracking-tight">
            Daftar Kendala & Mitigasi Lapangan
          </h1>
          <p class="text-slate-300 text-xs mt-0.5 max-w-xl">
            Monitoring kendala teknis (izin gedung, cuaca, kelistrikan) yang terdeteksi di cabang toko Anda beserta tindak lanjut solusinya oleh tim SGX.
          </p>
        </div>

        <!-- Quick Summary Badges -->
        <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
          <div class="px-3.5 py-1.5 bg-rose-500/20 border border-rose-400/30 rounded-xl text-xs font-bold text-rose-200 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse"></span>
            <span>{{ openIssuesCount }} Butuh Penanganan</span>
          </div>
          <div class="px-3.5 py-1.5 bg-emerald-500/20 border border-emerald-400/30 rounded-xl text-xs font-bold text-emerald-200 flex items-center gap-2">
            <CheckCircle2 class="w-3.5 h-3.5 text-emerald-400" />
            <span>{{ resolvedIssuesCount }} Teratasi</span>
          </div>
          <button
            @click="loadIssues"
            :disabled="loading"
            class="p-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl shadow-xs transition-all cursor-pointer active:scale-95"
            title="Segarkan Data"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
          </button>
        </div>
      </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex flex-col md:flex-row items-center justify-between gap-3 text-xs">
      <!-- Status Tabs -->
      <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0 custom-scrollbar font-bold">
        <button
          v-for="st in statusOptions"
          :key="st.value"
          @click="selectedStatus = st.value"
          :class="[
            'px-3.5 py-1.5 rounded-xl transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 border',
            selectedStatus === st.value
              ? 'bg-slate-900 text-white border-slate-900 shadow-xs'
              : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'
          ]"
        >
          <span>{{ st.label }}</span>
          <span v-if="st.value === 'OPEN' && openIssuesCount > 0" class="px-1.5 py-0.2 rounded-full text-[9px] bg-rose-500 text-white font-mono">
            {{ openIssuesCount }}
          </span>
        </button>
      </div>

      <!-- Search & Category -->
      <div class="flex items-center gap-2 w-full md:w-auto">
        <select
          v-model="selectedType"
          class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl font-semibold text-slate-700 text-xs cursor-pointer focus:outline-none"
        >
          <option value="ALL">Semua Kategori</option>
          <option value="CUACA_BURUK">Cuaca Buruk / Hujan</option>
          <option value="AKSES_LOKASI">Akses & Izin Gedung</option>
          <option value="KELISTRIKAN">Kelistrikan & Panel</option>
          <option value="MATERIAL">Material & Komponen</option>
          <option value="STRUKTUR_BANGUNAN">Struktur Tidak Sesuai</option>
        </select>

        <div class="relative flex-1 sm:w-64">
          <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Cari toko, SPK, catatan..."
            class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/20"
          />
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-16">
      <div class="w-8 h-8 border-3 border-amber-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
      <p class="text-xs text-slate-500 font-medium">Memuat data kendala teknis...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredIssues.length === 0" class="text-center py-16 bg-white rounded-3xl border border-dashed border-slate-300 shadow-xs">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 mx-auto mb-3">
        <CheckCircle2 class="w-6 h-6" />
      </div>
      <h3 class="font-bold text-slate-700 text-sm">Tidak Ada Kendala Lapangan</h3>
      <p class="text-xs text-slate-400 mt-1">Seluruh pekerjaan di cabang toko perusahaan Anda berjalan lancar tanpa kendala teknis aktif.</p>
    </div>

    <!-- Issues Card Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="issue in filteredIssues"
        :key="issue.id"
        :class="[
          'bg-white rounded-3xl p-5 border shadow-sm space-y-4 flex flex-col justify-between transition-all duration-300',
          issue.status === 'OPEN' ? 'border-amber-300 bg-amber-50/15 ring-1 ring-amber-200' : 'border-slate-200'
        ]"
      >
        <div class="space-y-3">
          <!-- Top Tag & Store Info -->
          <div class="flex items-center justify-between gap-2 flex-wrap">
            <div class="flex items-center gap-2">
              <span
                :class="[
                  'px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5',
                  issue.status === 'OPEN' ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800'
                ]"
              >
                <span v-if="issue.status === 'OPEN'" class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-ping"></span>
                <span>{{ issue.status === 'OPEN' ? 'DALAM PENANGANAN' : 'TERATASI' }}</span>
              </span>

              <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                {{ formatCategoryName(issue.issue_type) }}
              </span>
            </div>

            <span class="text-[10px] text-slate-400 font-medium">
              {{ formatDate(issue.created_at) }}
            </span>
          </div>

          <!-- SPK Title & Store Name -->
          <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 font-mono mb-1">
              <FileText class="w-3.5 h-3.5 text-slate-400" />
              <span class="font-bold text-slate-800">{{ issue.workOrder?.spk_number || 'SPK-' + issue.work_order_id }}</span>
            </div>
            <h4 class="font-black text-slate-900 text-sm leading-snug">
              {{ issue.workOrder?.title || issue.workOrder?.location_name || 'Pekerjaan Cabang Toko' }}
            </h4>
            <div class="flex items-center gap-1 text-[11px] text-slate-500 mt-1">
              <MapPin class="w-3.5 h-3.5 text-slate-400 shrink-0" />
              <span class="truncate">{{ issue.workOrder?.location_name || 'Lokasi Toko' }}</span>
            </div>
          </div>

          <!-- Issue Notes -->
          <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-xs text-slate-700 leading-relaxed">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Laporan dari Tim Lapangan:</div>
            <p class="font-medium text-slate-800">{{ issue.notes }}</p>
          </div>

          <!-- Resolution Notes (if resolved) -->
          <div v-if="issue.status === 'RESOLVED' && issue.resolution_notes" class="p-3.5 rounded-2xl bg-emerald-50/80 border border-emerald-200 text-xs text-emerald-950">
            <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider mb-1 flex items-center gap-1">
              <CheckCircle2 class="w-3 h-3 text-emerald-600" />
              <span>Solusi & Mitigasi SGX:</span>
            </div>
            <p class="font-medium text-emerald-900">{{ issue.resolution_notes }}</p>
            <div class="text-[10px] text-emerald-700 mt-1 font-semibold">
              Diselesaikan pada {{ formatDate(issue.resolved_at) }}
            </div>
          </div>
        </div>

        <!-- Footer Info -->
        <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
          <div class="flex items-center gap-1">
            <User class="w-3.5 h-3.5 text-slate-400" />
            <span>Pelapor: <strong>{{ issue.user?.name || 'Teknisi SGX' }}</strong></span>
          </div>
          <span class="text-[10px] font-mono text-slate-400">ID Tiket: #{{ issue.id }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../../services/api';
import {
  AlertTriangle,
  CheckCircle2,
  Search,
  RefreshCw,
  FileText,
  MapPin,
  User
} from 'lucide-vue-next';

const issues = ref([]);
const loading = ref(true);
const selectedStatus = ref('ALL');
const selectedType = ref('ALL');
const searchQuery = ref('');

const statusOptions = [
  { label: 'Semua Kendala', value: 'ALL' },
  { label: 'Butuh Tindakan', value: 'OPEN' },
  { label: 'Telah Teratasi', value: 'RESOLVED' }
];

const openIssuesCount = computed(() => {
  return issues.value.filter(i => i.status === 'OPEN').length;
});

const resolvedIssuesCount = computed(() => {
  return issues.value.filter(i => i.status === 'RESOLVED').length;
});

const filteredIssues = computed(() => {
  return issues.value.filter(issue => {
    if (selectedStatus.value !== 'ALL' && issue.status !== selectedStatus.value) {
      return false;
    }
    if (selectedType.value !== 'ALL' && issue.issue_type !== selectedType.value) {
      return false;
    }
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase();
      const matchNotes = issue.notes?.toLowerCase().includes(q);
      const matchSpk = issue.workOrder?.spk_number?.toLowerCase().includes(q);
      const matchTitle = issue.workOrder?.title?.toLowerCase().includes(q);
      const matchLoc = issue.workOrder?.location_name?.toLowerCase().includes(q);
      if (!matchNotes && !matchSpk && !matchTitle && !matchLoc) return false;
    }
    return true;
  });
});

function formatCategoryName(type) {
  const map = {
    CUACA_BURUK: 'Cuaca Buruk',
    AKSES_LOKASI: 'Akses / Izin Lokasi',
    KELISTRIKAN: 'Kelistrikan / Panel',
    MATERIAL: 'Material & Rangka',
    STRUKTUR_BANGUNAN: 'Struktur Bangunan',
    OTHER: 'Lainnya'
  };
  return map[type] || type || 'Kendala Lapangan';
}

function formatDate(isoStr) {
  if (!isoStr) return '-';
  const d = new Date(isoStr);
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

async function loadIssues() {
  loading.value = true;
  try {
    const res = await api.getIssues();
    issues.value = res.data || [];
  } catch (err) {
    console.error('Failed to load client issues:', err);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadIssues();
});
</script>
