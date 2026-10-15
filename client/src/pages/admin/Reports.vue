<template>
  <div class="space-y-4 font-mono">
    <!-- Header (CAD Blueprint Style) -->
    <div class="bg-[#13171F] border border-[#232A36] p-4 rounded-xs flex flex-col md:flex-row md:items-center justify-between gap-3 relative overflow-hidden">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xs bg-[#0B0C0E] border border-hazard/40 flex items-center justify-center text-hazard shrink-0">
          <BarChart3 class="w-5 h-5" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-base sm:text-lg font-heading font-black text-white uppercase tracking-wider">
              Laporan & Statistik Eksekutif
            </h2>
            <span class="bg-hazard/10 text-hazard border border-hazard/30 text-[9px] font-mono px-1.5 py-0.2 rounded-none font-bold uppercase">
              OPERATIONAL ANALYTICS
            </span>
          </div>
          <p class="text-xs text-slate-400 font-mono mt-0.5">
            Pusat analitik operasional, metrik kinerja mitra vendor, rekapitulasi finansial kontrak, dan distribusi pekerjaan.
          </p>
        </div>
      </div>

      <!-- Action Buttons (Export & Print) -->
      <div class="flex items-center gap-2 self-start md:self-auto text-xs">
        <button
          @click="handlePrintReport"
          class="px-3.5 py-1.5 bg-[#0B0C0E] hover:bg-[#181D26] text-slate-300 border border-[#232A36] hover:border-slate-500 rounded-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer uppercase"
        >
          <Printer class="w-3.5 h-3.5" />
          <span>Cetak Ringkasan</span>
        </button>
        <button
          @click="exportCSV"
          class="cad-btn-primary px-4 py-1.5 text-xs font-bold flex items-center gap-2 cursor-pointer uppercase active:scale-95 transition-all"
        >
          <FileSpreadsheet class="w-4 h-4" />
          <span>Export Excel / CSV</span>
        </button>
      </div>
    </div>

    <!-- Executive KPI Scorecards (High Density CAD Modules) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
      <!-- Total Nilai Kontrak -->
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-3.5 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xs bg-[#0B0C0E] border border-emerald-500/40 text-emerald-400 flex items-center justify-center shrink-0">
          <Banknote class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[9px] uppercase font-bold text-slate-500 block tracking-wider">TOTAL NILAI KONTRAK</span>
          <div class="text-base font-black text-white font-mono truncate">
            Rp {{ (totalContractValue / 1000000).toFixed(1) }} Jt
          </div>
          <span class="text-[9px] text-emerald-400 font-mono">
            {{ workOrders.length }} TOTAL SPK TERDATA
          </span>
        </div>
      </div>

      <!-- Completion Rate -->
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-3.5 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xs bg-[#0B0C0E] border border-hazard/40 text-hazard flex items-center justify-center shrink-0">
          <CheckCircle2 class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[9px] uppercase font-bold text-slate-500 block tracking-wider">TINGKAT PENYELESAIAN</span>
          <div class="text-base font-black text-hazard font-mono truncate">
            {{ completionRate }}%
          </div>
          <span class="text-[9px] text-slate-400 font-mono">
            {{ completedOrdersCount }} SPK SELESAI / TERBIT BA
          </span>
        </div>
      </div>

      <!-- In Progress Active -->
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-3.5 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xs bg-[#0B0C0E] border border-blue-500/40 text-blue-400 flex items-center justify-center shrink-0">
          <Clock class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[9px] uppercase font-bold text-slate-500 block tracking-wider">SEDANG DIKERJAKAN</span>
          <div class="text-base font-black text-white font-mono truncate">
            {{ inProgressOrdersCount }} SPK
          </div>
          <span class="text-[9px] text-blue-400 font-mono">
            PENGERJAAN & REVIEW AKTIF
          </span>
        </div>
      </div>

      <!-- Field Issues Mitigation Rate -->
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-3.5 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xs bg-[#0B0C0E] border border-red-500/40 text-red-400 flex items-center justify-center shrink-0">
          <AlertTriangle class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[9px] uppercase font-bold text-slate-500 block tracking-wider">KENDALA LAPANGAN</span>
          <div class="text-base font-black text-red-400 font-mono truncate">
            {{ openIssuesCount > 0 ? `${openIssuesCount} BUTUH AKSI` : 'TERKENDALI (0)' }}
          </div>
          <span class="text-[9px] text-slate-400 font-mono">
            {{ issuesList.length }} TOTAL LOG LAPANGAN
          </span>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs (Terminal Pills) -->
    <div class="flex gap-1.5 border-b border-[#232A36] pb-2 text-xs font-mono overflow-x-auto scrollbar-none">
      <button
        @click="activeTab = 'work_orders'"
        :class="[
          'flex items-center gap-2 px-3.5 py-1.5 rounded-none transition-all cursor-pointer whitespace-nowrap border uppercase font-bold',
          activeTab === 'work_orders'
            ? 'bg-hazard text-carbon border-hazard shadow-xs'
            : 'bg-[#0B0C0E] text-slate-400 hover:text-white border-[#232A36]'
        ]"
      >
        <FileText class="w-3.5 h-3.5" />
        <span>Rekapitulasi Proyek SPK ({{ filteredWorkOrders.length }})</span>
      </button>

      <button
        @click="activeTab = 'vendor_performance'"
        :class="[
          'flex items-center gap-2 px-3.5 py-1.5 rounded-none transition-all cursor-pointer whitespace-nowrap border uppercase font-bold',
          activeTab === 'vendor_performance'
            ? 'bg-hazard text-carbon border-hazard shadow-xs'
            : 'bg-[#0B0C0E] text-slate-400 hover:text-white border-[#232A36]'
        ]"
      >
        <Award class="w-3.5 h-3.5" />
        <span>Kinerja Portofolio Client ({{ vendorMetrics.length }})</span>
      </button>

      <button
        @click="activeTab = 'distribution'"
        :class="[
          'flex items-center gap-2 px-3.5 py-1.5 rounded-none transition-all cursor-pointer whitespace-nowrap border uppercase font-bold',
          activeTab === 'distribution'
            ? 'bg-hazard text-carbon border-hazard shadow-xs'
            : 'bg-[#0B0C0E] text-slate-400 hover:text-white border-[#232A36]'
        ]"
      >
        <PieChart class="w-3.5 h-3.5" />
        <span>Distribusi Jenis Pekerjaan & Area</span>
      </button>
    </div>

    <!-- TAB 1: Rekapitulasi Proyek SPK Table -->
    <div v-show="activeTab === 'work_orders'" class="space-y-3">
      <!-- Filter Bar (Terminal Style) -->
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-3 flex flex-col md:flex-row items-center justify-between gap-3 text-xs font-mono">
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
          <!-- Client Filter -->
          <select
            v-model="filterVendor"
            class="px-2.5 py-1.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-slate-300 text-xs cursor-pointer focus:outline-none focus:border-hazard"
          >
            <option value="">[ SEMUA CLIENT / VENDOR ]</option>
            <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
          </select>

          <!-- Area Filter -->
          <select
            v-model="filterArea"
            class="px-2.5 py-1.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-slate-300 text-xs cursor-pointer focus:outline-none focus:border-hazard"
          >
            <option value="">[ SEMUA AREA WILAYAH ]</option>
            <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.name }}</option>
          </select>

          <!-- Status Filter -->
          <select
            v-model="filterStatus"
            class="px-2.5 py-1.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-slate-300 text-xs cursor-pointer focus:outline-none focus:border-hazard"
          >
            <option value="">[ SEMUA STATUS SPK ]</option>
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
          <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-500" />
          <input
            type="text"
            v-model="searchQuery"
            placeholder=">_ CARI SPK, JUDUL, LOKASI..."
            class="w-full pl-8 pr-3 py-1.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-xs font-mono text-white placeholder-slate-600 focus:outline-none focus:border-hazard"
          />
        </div>
      </div>

      <!-- Table (CAD High-Density Module) -->
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs font-mono">
            <thead class="bg-[#0E1117] text-slate-400 font-bold border-b border-[#232A36] uppercase text-[10px] tracking-wider">
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
            <tbody class="divide-y divide-[#1C2028] text-slate-300">
              <template v-if="loading">
                <tr>
                  <td colspan="7" class="py-12 text-center text-slate-500 font-medium">
                    <div class="w-6 h-6 border-2 border-hazard border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                    <span>MEMUAT_DATA_LAPORAN_SPK...</span>
                  </td>
                </tr>
              </template>
              <template v-else-if="filteredWorkOrders.length > 0">
                <tr v-for="wo in filteredWorkOrders" :key="wo.id" class="hover:bg-[#181D26] transition-colors">
                  <td class="py-3.5 px-4 font-mono font-bold text-hazard text-xs">
                    {{ wo.spk_number }}
                  </td>
                  <td class="py-3.5 px-4">
                    <div class="font-heading font-black text-white text-xs uppercase">{{ wo.title }}</div>
                    <div class="text-[10px] text-slate-400 truncate">{{ wo.location_name }}</div>
                  </td>
                  <td class="py-3.5 px-4 font-bold text-slate-200">{{ wo.vendor_name || 'Mitra' }}</td>
                  <td class="py-3.5 px-4 text-slate-400">{{ wo.area_name || '-' }}</td>
                  <td class="py-3.5 px-4 font-mono font-bold text-white text-right">
                    Rp {{ Number(wo.contract_value || 15000000).toLocaleString('id-ID') }}
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span
                      class="px-2 py-0.5 rounded-none font-mono text-[10px] font-bold border"
                      :class="wo.progress_percent === 100 ? 'bg-emerald-950 text-emerald-400 border-emerald-500/40' : 'bg-[#0B0C0E] text-slate-300 border-[#232A36]'"
                    >
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
                  <td colspan="7" class="py-12 text-center text-slate-500 font-medium border border-dashed border-[#232A36]">
                    [ TIDAK ADA DATA PEKERJAAN YANG SESUAI FILTER ]
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 2: Vendor Performance & Quality Leaderboard -->
    <div v-show="activeTab === 'vendor_performance'" class="space-y-3">
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs overflow-hidden">
        <div class="p-3.5 border-b border-[#232A36] bg-[#0E1117] flex items-center justify-between">
          <div>
            <h3 class="font-heading font-black text-xs sm:text-sm text-white uppercase tracking-wider">
              Evaluasi Portofolio & Progres Perusahaan Client
            </h3>
            <p class="text-[10px] text-slate-400 font-mono">Metrik rasio penyelesaian tugas, total nilai rupiah, dan kualitas penanganan proyek per Client.</p>
          </div>
          <span class="px-2 py-0.5 rounded-none bg-[#0B0C0E] border border-hazard/40 text-hazard font-mono font-bold text-xs">
            {{ vendorMetrics.length }} CLIENT TERDATA
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs font-mono">
            <thead class="bg-[#0B0C0E] text-slate-400 font-bold border-b border-[#232A36] uppercase text-[10px] tracking-wider">
              <tr>
                <th class="py-3 px-4">Perusahaan Client</th>
                <th class="py-3 px-4 text-center">Total SPK</th>
                <th class="py-3 px-4 text-right">Nilai Proyek</th>
                <th class="py-3 px-4 text-center">Selesai (100%)</th>
                <th class="py-3 px-4 text-center">Sedang Berjalan</th>
                <th class="py-3 px-4 text-center">Kendala</th>
                <th class="py-3 px-4 text-center">Grade Mutu</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#1C2028] text-slate-300">
              <tr v-for="vm in vendorMetrics" :key="vm.id" class="hover:bg-[#181D26] transition-colors">
                <td class="py-3.5 px-4">
                  <div class="font-heading font-black text-white uppercase text-xs">{{ vm.name }}</div>
                  <div class="text-[10px] text-slate-500 font-mono">{{ vm.code || 'CLIENT-REG' }}</div>
                </td>
                <td class="py-3.5 px-4 text-center font-bold font-mono text-white">{{ vm.totalTasks }} SPK</td>
                <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-400">
                  Rp {{ (vm.totalValue / 1000000).toFixed(1) }} Jt
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span class="px-2 py-0.5 rounded-none text-[9px] font-bold bg-emerald-950 text-emerald-400 border border-emerald-500/40">
                    {{ vm.completedTasks }} ({{ vm.completionRate }}%)
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center font-mono font-semibold text-blue-400">{{ vm.inProgressTasks }} SPK</td>
                <td class="py-3.5 px-4 text-center">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded-none text-[9px] font-bold border',
                      vm.issuesCount > 0 ? 'bg-red-950 text-red-400 border-red-500/40' : 'bg-[#0B0C0E] text-slate-500 border-[#232A36]'
                    ]"
                  >
                    {{ vm.issuesCount }} TIKET
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span class="px-2 py-0.5 rounded-none text-[9px] font-bold bg-emerald-950 text-emerald-400 border border-emerald-500/60 shadow-xs">
                    [ GRADE A: PRIMA ]
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 3: Distribution by Job Type & Area -->
    <div v-show="activeTab === 'distribution'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Breakdown by Job Type -->
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-4 space-y-3 font-mono">
        <div class="flex items-center justify-between border-b border-[#232A36] pb-2.5">
          <h3 class="font-heading font-black text-xs sm:text-sm text-white uppercase tracking-wider flex items-center gap-2">
            <Layers class="w-4 h-4 text-hazard" />
            <span>Rekapitulasi per Jenis Pekerjaan</span>
          </h3>
        </div>

        <div class="space-y-2 text-xs">
          <div
            v-for="jt in jobTypeMetrics"
            :key="jt.id"
            class="p-2.5 bg-[#0B0C0E] border border-[#232A36] rounded-none flex items-center justify-between hover:border-slate-500 transition-colors"
          >
            <div>
              <div class="font-heading font-black text-white text-xs uppercase">{{ jt.name }}</div>
              <div class="text-[9px] text-slate-500 font-mono">KODE: {{ jt.code }}</div>
            </div>
            <div class="text-right font-mono">
              <div class="font-bold text-hazard">{{ jt.count }} SPK</div>
              <div class="text-[10px] text-emerald-400">Rp {{ (jt.totalValue / 1000000).toFixed(1) }} Jt</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Breakdown by Area -->
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-4 space-y-3 font-mono">
        <div class="flex items-center justify-between border-b border-[#232A36] pb-2.5">
          <h3 class="font-heading font-black text-xs sm:text-sm text-white uppercase tracking-wider flex items-center gap-2">
            <MapPin class="w-4 h-4 text-hazard" />
            <span>Rekapitulasi per Area Wilayah</span>
          </h3>
        </div>

        <div class="space-y-2 text-xs">
          <div
            v-for="ar in areaMetrics"
            :key="ar.id"
            class="p-2.5 bg-[#0B0C0E] border border-[#232A36] rounded-none flex items-center justify-between hover:border-slate-500 transition-colors"
          >
            <div>
              <div class="font-heading font-black text-white text-xs uppercase">{{ ar.name }}</div>
              <div class="text-[9px] text-slate-500">{{ ar.completed }} SELESAI / {{ ar.total }} TOTAL</div>
            </div>
            <div class="text-right font-mono">
              <div class="font-bold text-white">{{ ar.total }} SPK</div>
              <div class="text-[10px] font-bold text-emerald-400">{{ ar.rate }}% SELESAI</div>
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
