<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-purple-800 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-purple-900/20">
            <BarChart3 class="w-4 h-4" />
          </div>
          <span>Laporan & Statistik Eksekutif</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1 font-medium">
          Pusat analitik operasional, metrik kinerja mitra vendor, rekapitulasi finansial kontrak, dan distribusi pekerjaan.
        </p>
      </div>

      <!-- Action Buttons (Export & Print) -->
      <div class="flex items-center gap-2 self-start sm:self-auto">
        <button
          @click="handlePrintReport"
          class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer shadow-xs active:scale-95"
        >
          <Printer class="w-3.5 h-3.5" />
          <span>Cetak Ringkasan</span>
        </button>
        <button
          @click="exportCSV"
          class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-md shadow-emerald-900/20 active:scale-95 transition-all cursor-pointer"
        >
          <FileSpreadsheet class="w-4 h-4" />
          <span>Export Excel / CSV</span>
        </button>
      </div>
    </div>

    <!-- Executive KPI Scorecards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Total Nilai Kontrak -->
      <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
          <Banknote class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total Nilai Kontrak</span>
          <div class="text-base font-black text-slate-900 font-mono truncate">
            Rp {{ (totalContractValue / 1000000).toFixed(1) }} Jt
          </div>
          <span class="text-[10px] text-emerald-700 font-semibold">
            Dari {{ workOrders.length }} total SPK aktif
          </span>
        </div>
      </div>

      <!-- Completion Rate -->
      <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-purple-100 text-purple-900 flex items-center justify-center shrink-0">
          <CheckCircle2 class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Tingkat Penyelesaian</span>
          <div class="text-base font-black text-slate-900 font-mono truncate">
            {{ completionRate }}%
          </div>
          <span class="text-[10px] text-purple-800 font-semibold">
            {{ completedOrdersCount }} SPK Selesai & Terbit BA
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
            {{ inProgressOrdersCount }} SPK
          </div>
          <span class="text-[10px] text-indigo-700 font-semibold">
            Dalam tahap pengerjaan & review
          </span>
        </div>
      </div>

      <!-- Field Issues Mitigation Rate -->
      <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-900 flex items-center justify-center shrink-0">
          <AlertTriangle class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Kendala Lapangan</span>
          <div class="text-base font-black text-slate-900 font-mono truncate">
            {{ openIssuesCount > 0 ? `${openIssuesCount} Butuh Aksi` : '100% Teratasi' }}
          </div>
          <span class="text-[10px] text-amber-800 font-semibold">
            {{ issuesList.length }} total laporan kendala
          </span>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex gap-2 border-b border-slate-200/80 pb-2.5 text-xs font-bold overflow-x-auto custom-scrollbar">
      <button
        @click="activeTab = 'work_orders'"
        :class="[
          'flex items-center gap-2 px-4 py-2 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer whitespace-nowrap',
          activeTab === 'work_orders' ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'
        ]"
      >
        <FileText class="w-3.5 h-3.5" />
        <span>Rekapitulasi Proyek SPK ({{ filteredWorkOrders.length }})</span>
      </button>

      <button
        @click="activeTab = 'vendor_performance'"
        :class="[
          'flex items-center gap-2 px-4 py-2 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer whitespace-nowrap',
          activeTab === 'vendor_performance' ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'
        ]"
      >
        <Award class="w-3.5 h-3.5" />
        <span>Kinerja Portofolio Client ({{ vendorMetrics.length }})</span>
      </button>

      <button
        @click="activeTab = 'distribution'"
        :class="[
          'flex items-center gap-2 px-4 py-2 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer whitespace-nowrap',
          activeTab === 'distribution' ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'
        ]"
      >
        <PieChart class="w-3.5 h-3.5" />
        <span>Distribusi Jenis Pekerjaan & Area</span>
      </button>
    </div>

    <!-- TAB 1: Rekapitulasi Proyek SPK Table -->
    <div v-show="activeTab === 'work_orders'" class="space-y-4">
      <!-- Filter Bar -->
      <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex flex-col md:flex-row items-center justify-between gap-3 text-xs">
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
          <!-- Client Filter -->
          <select
            v-model="filterVendor"
            class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl font-semibold text-slate-700 text-xs cursor-pointer"
          >
            <option value="">Semua Perusahaan Client</option>
            <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
          </select>

          <!-- Area Filter -->
          <select
            v-model="filterArea"
            class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl font-semibold text-slate-700 text-xs cursor-pointer"
          >
            <option value="">Semua Area Wilayah</option>
            <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.name }}</option>
          </select>

          <!-- Status Filter -->
          <select
            v-model="filterStatus"
            class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl font-semibold text-slate-700 text-xs cursor-pointer"
          >
            <option value="">Semua Status SPK</option>
            <option value="DRAFT">DRAFT</option>
            <option value="ASSIGNED">ASSIGNED</option>
            <option value="IN_PROGRESS">IN_PROGRESS</option>
            <option value="SUBMITTED">SUBMITTED</option>
            <option value="UNDER_REVIEW">UNDER_REVIEW</option>
            <option value="REVISION_REQUESTED">REVISION_REQUESTED</option>
            <option value="APPROVED">APPROVED</option>
            <option value="COMPLETED">COMPLETED</option>
          </select>
        </div>

        <!-- Search Box -->
        <div class="relative w-full md:w-64">
          <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Cari SPK, judul, lokasi..."
            class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs"
          />
        </div>
      </div>

      <!-- Table -->
      <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-100/80 text-slate-600 font-bold border-b border-slate-200/80">
              <tr>
                <th class="py-3 px-4">No. SPK</th>
                <th class="py-3 px-4">Nama Proyek / Cabang</th>
                <th class="py-3 px-4">Mitra Vendor</th>
                <th class="py-3 px-4">Area Wilayah</th>
                <th class="py-3 px-4 text-right">Nilai Kontrak</th>
                <th class="py-3 px-4 text-center">Progress</th>
                <th class="py-3 px-4">Status SPK</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <template v-if="loading">
                <tr>
                  <td colspan="7" class="py-12 text-center text-slate-400 font-medium">Memuat data laporan SPK...</td>
                </tr>
              </template>
              <template v-else-if="filteredWorkOrders.length > 0">
                <tr v-for="wo in filteredWorkOrders" :key="wo.id" class="hover:bg-purple-50/30 transition-colors">
                  <td class="py-3.5 px-4 font-mono font-bold text-purple-900">{{ wo.spk_number }}</td>
                  <td class="py-3.5 px-4">
                    <div class="font-bold text-slate-900">{{ wo.title }}</div>
                    <div class="text-[10px] text-slate-400 truncate">{{ wo.location_name }}</div>
                  </td>
                  <td class="py-3.5 px-4 font-semibold text-slate-800">{{ wo.vendor_name || 'Mitra' }}</td>
                  <td class="py-3.5 px-4 text-slate-600">{{ wo.area_name || '-' }}</td>
                  <td class="py-3.5 px-4 font-mono font-bold text-slate-900 text-right">
                    Rp {{ Number(wo.contract_value || 15000000).toLocaleString('id-ID') }}
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="px-2 py-0.5 rounded-md font-mono text-[10px] font-bold" :class="wo.progress_percent === 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'">
                      {{ wo.progress_percent || 0 }}%
                    </span>
                  </td>
                  <td class="py-3.5 px-4">
                    <StatusBadge :status="wo.status" />
                  </td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td colspan="7" class="py-12 text-center text-slate-400 font-medium">
                    Tidak ada data pekerjaan yang sesuai dengan kriteria filter.
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 2: Vendor Performance & Quality Leaderboard -->
    <div v-show="activeTab === 'vendor_performance'" class="space-y-4">
      <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
          <div>
            <h3 class="font-black text-sm text-slate-900">Evaluasi Portofolio & Progres Perusahaan Client</h3>
            <p class="text-[11px] text-slate-500">Metrik rasio penyelesaian tugas, total nilai rupiah, dan kualitas penanganan proyek per Client.</p>
          </div>
          <span class="px-2.5 py-1 rounded-xl bg-purple-100 text-purple-900 font-bold text-xs">
            {{ vendorMetrics.length }} Client Terdaftar
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-100/80 text-slate-600 font-bold border-b border-slate-200/80">
              <tr>
                <th class="py-3 px-4">Nama Perusahaan Client</th>
                <th class="py-3 px-4 text-center">Total SPK</th>
                <th class="py-3 px-4 text-right">Total Nilai Proyek</th>
                <th class="py-3 px-4 text-center">Selesai (100%)</th>
                <th class="py-3 px-4 text-center">Sedang Berjalan</th>
                <th class="py-3 px-4 text-center">Kendala Lapangan</th>
                <th class="py-3 px-4 text-center">Status Mutu</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="vm in vendorMetrics" :key="vm.id" class="hover:bg-purple-50/30 transition-colors">
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900">{{ vm.name }}</div>
                  <div class="text-[10px] text-slate-400 font-mono">{{ vm.code || 'CLIENT-REG' }}</div>
                </td>
                <td class="py-3.5 px-4 text-center font-bold font-mono text-slate-900">{{ vm.totalTasks }} SPK</td>
                <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-800">
                  Rp {{ (vm.totalValue / 1000000).toFixed(1) }} Jt
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                    {{ vm.completedTasks }} ({{ vm.completionRate }}%)
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center font-mono font-semibold text-indigo-900">{{ vm.inProgressTasks }} SPK</td>
                <td class="py-3.5 px-4 text-center">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded-full text-[10px] font-bold',
                      vm.issuesCount > 0 ? 'bg-amber-100 text-amber-900' : 'bg-slate-100 text-slate-500'
                    ]"
                  >
                    {{ vm.issuesCount }} Tiket
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-600 text-white shadow-xs">
                    GRADE A (PRIMA)
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 3: Distribution by Job Type & Area -->
    <div v-show="activeTab === 'distribution'" class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <!-- Breakdown by Job Type -->
      <div class="glass-card rounded-3xl p-5 border border-white/80 shadow-glass space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
            <Layers class="w-4 h-4 text-purple-700" />
            <span>Rekapitulasi per Jenis Pekerjaan</span>
          </h3>
        </div>

        <div class="space-y-3 text-xs">
          <div
            v-for="jt in jobTypeMetrics"
            :key="jt.id"
            class="p-3 bg-slate-50 border border-slate-200/80 rounded-2xl flex items-center justify-between"
          >
            <div>
              <div class="font-bold text-slate-900">{{ jt.name }}</div>
              <div class="text-[10px] text-slate-400 font-mono">Kode: {{ jt.code }}</div>
            </div>
            <div class="text-right font-mono">
              <div class="font-bold text-purple-900">{{ jt.count }} SPK</div>
              <div class="text-[10px] font-semibold text-emerald-700">Rp {{ (jt.totalValue / 1000000).toFixed(1) }} Jt</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Breakdown by Area -->
      <div class="glass-card rounded-3xl p-5 border border-white/80 shadow-glass space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
            <MapPin class="w-4 h-4 text-indigo-700" />
            <span>Rekapitulasi per Area Wilayah</span>
          </h3>
        </div>

        <div class="space-y-3 text-xs">
          <div
            v-for="ar in areaMetrics"
            :key="ar.id"
            class="p-3 bg-slate-50 border border-slate-200/80 rounded-2xl flex items-center justify-between"
          >
            <div>
              <div class="font-bold text-slate-900">{{ ar.name }}</div>
              <div class="text-[10px] text-slate-400">{{ ar.completed }} selesai / {{ ar.total }} total SPK</div>
            </div>
            <div class="text-right font-mono">
              <div class="font-bold text-indigo-900">{{ ar.total }} SPK</div>
              <div class="text-[10px] font-bold text-emerald-700">{{ ar.rate }}% Selesai</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../../services/api';
import StatusBadge from '../../components/StatusBadge.vue';
import {
  BarChart3,
  FileSpreadsheet,
  FileText,
  CheckCircle2,
  Clock,
  AlertTriangle,
  Banknote,
  Award,
  PieChart,
  Layers,
  MapPin,
  Search,
  Printer
} from 'lucide-vue-next';

const activeTab = ref('work_orders');
const workOrders = ref([]);
const vendors = ref([]);
const areas = ref([]);
const jobTypes = ref([]);
const issuesList = ref([]);
const loading = ref(true);

const filterVendor = ref('');
const filterArea = ref('');
const filterStatus = ref('');
const searchQuery = ref('');

async function loadAllData() {
  loading.value = true;
  try {
    const [woRes, vRes, aRes, jtRes, issRes] = await Promise.all([
      api.getWorkOrders(),
      api.getVendors(),
      api.getAreas(),
      api.getJobTypes(),
      api.getFieldIssues()
    ]);
    workOrders.value = woRes.data || [];
    vendors.value = vRes.data || [];
    areas.value = aRes.data || [];
    jobTypes.value = jtRes.data || [];
    issuesList.value = issRes.data || [];
  } catch (err) {
    console.error('Failed to load report data:', err);
  } finally {
    loading.value = false;
  }
}

// Total Financial Metric
const totalContractValue = computed(() => {
  return workOrders.value.reduce((sum, wo) => sum + Number(wo.contract_value || 15000000), 0);
});

const completedOrdersCount = computed(() => {
  return workOrders.value.filter(wo => ['APPROVED', 'COMPLETED'].includes(wo.status)).length;
});

const inProgressOrdersCount = computed(() => {
  return workOrders.value.filter(wo => ['IN_PROGRESS', 'ASSIGNED', 'CHECKED_IN', 'SUBMITTED', 'UNDER_REVIEW'].includes(wo.status)).length;
});

const openIssuesCount = computed(() => {
  return issuesList.value.filter(i => i.status === 'OPEN').length;
});

const completionRate = computed(() => {
  if (workOrders.value.length === 0) return 0;
  return Math.round((completedOrdersCount.value / workOrders.value.length) * 100);
});

// Filtered Work Orders
const filteredWorkOrders = computed(() => {
  return workOrders.value.filter(wo => {
    if (filterVendor.value && wo.vendor_id !== parseInt(filterVendor.value, 10)) return false;
    if (filterArea.value && wo.area_id !== parseInt(filterArea.value, 10)) return false;
    if (filterStatus.value && wo.status !== filterStatus.value) return false;
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

// Vendor Performance Metrics
const vendorMetrics = computed(() => {
  return vendors.value.map(v => {
    const tasks = workOrders.value.filter(wo => wo.vendor_id === v.id);
    const completed = tasks.filter(wo => ['APPROVED', 'COMPLETED'].includes(wo.status)).length;
    const inProg = tasks.filter(wo => ['IN_PROGRESS', 'ASSIGNED', 'CHECKED_IN', 'SUBMITTED', 'UNDER_REVIEW'].includes(wo.status)).length;
    const totalVal = tasks.reduce((sum, wo) => sum + Number(wo.contract_value || 15000000), 0);
    const vIssues = issuesList.value.filter(i => i.vendor_id === v.id).length;

    return {
      id: v.id,
      name: v.name,
      code: v.code,
      totalTasks: tasks.length,
      completedTasks: completed,
      inProgressTasks: inProg,
      totalValue: totalVal,
      issuesCount: vIssues,
      completionRate: tasks.length > 0 ? Math.round((completed / tasks.length) * 100) : 0
    };
  });
});

// Job Type Metrics
const jobTypeMetrics = computed(() => {
  return jobTypes.value.map(jt => {
    const tasks = workOrders.value.filter(wo => wo.job_type_id === jt.id || wo.title?.toLowerCase().includes(jt.name?.toLowerCase()));
    const totalVal = tasks.reduce((sum, wo) => sum + Number(wo.contract_value || jt.standard_price || 15000000), 0);
    return {
      id: jt.id,
      name: jt.name,
      code: jt.code,
      count: tasks.length,
      totalValue: totalVal
    };
  });
});

// Area Metrics
const areaMetrics = computed(() => {
  return areas.value.map(ar => {
    const tasks = workOrders.value.filter(wo => wo.area_id === ar.id);
    const completed = tasks.filter(wo => ['APPROVED', 'COMPLETED'].includes(wo.status)).length;
    return {
      id: ar.id,
      name: ar.name,
      total: tasks.length,
      completed,
      rate: tasks.length > 0 ? Math.round((completed / tasks.length) * 100) : 0
    };
  });
});

function exportCSV() {
  if (filteredWorkOrders.value.length === 0) {
    alert('Tidak ada data yang dapat diexport.');
    return;
  }

  const headers = ['No SPK', 'Judul Pekerjaan', 'Mitra Vendor', 'Area', 'Lokasi', 'Nilai Kontrak', 'Progress', 'Status'];
  const rows = filteredWorkOrders.value.map(wo => [
    `"${wo.spk_number || ''}"`,
    `"${(wo.title || '').replace(/"/g, '""')}"`,
    `"${(wo.vendor_name || '').replace(/"/g, '""')}"`,
    `"${(wo.area_name || '').replace(/"/g, '""')}"`,
    `"${(wo.location_name || '').replace(/"/g, '""')}"`,
    Number(wo.contract_value || 15000000),
    `${wo.progress_percent || 0}%`,
    `"${wo.status || ''}"`
  ]);

  const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `rekap_laporan_sgx_${new Date().toISOString().slice(0, 10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function handlePrintReport() {
  window.print();
}

onMounted(() => {
  loadAllData();
});
</script>
