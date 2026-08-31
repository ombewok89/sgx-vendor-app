<template>
  <div class="space-y-5">
    <!-- Header Title & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-900 to-brand-600 flex items-center justify-center text-white shadow-lg shadow-brand-900/20">
            <FileText class="w-5 h-5" />
          </div>
          <span>Manajemen Pekerjaan / SPK</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1 font-medium">
          Daftar seluruh Surat Perintah Kerja (SPK), progres lapangan, penugasan vendor, hingga penyelesaian Berita Acara.
        </p>
      </div>

      <div class="flex items-center gap-2.5 flex-wrap">
        <!-- Export CSV / Excel Button -->
        <button
          @click="exportCSV"
          :disabled="workOrders.length === 0"
          class="px-4 py-2.5 bg-gradient-to-r from-emerald-700 to-teal-700 hover:from-emerald-600 hover:to-teal-600 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-xs transition-all duration-200 active:scale-95 cursor-pointer disabled:opacity-50"
          title="Export Data SPK Terfilter ke Excel / CSV"
        >
          <FileSpreadsheet class="w-4 h-4" />
          <span>Export Excel</span>
        </button>

        <!-- Superuser Archive Mode Toggle Tabs -->
        <div v-if="isSuperuser" class="bg-slate-200/80 p-1 rounded-2xl flex items-center text-xs font-bold shadow-2xs">
          <button
            type="button"
            @click="switchTab('ACTIVE')"
            :class="[
              'px-3.5 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5',
              activeTab === 'ACTIVE'
                ? 'bg-white text-brand-950 shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            ]"
          >
            <Layers class="w-3.5 h-3.5 text-brand-700" />
            <span>SPK Aktif</span>
          </button>

          <button
            type="button"
            @click="switchTab('ARCHIVED')"
            :class="[
              'px-3.5 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5',
              activeTab === 'ARCHIVED'
                ? 'bg-amber-600 text-white shadow-xs'
                : 'text-slate-600 hover:text-amber-800'
            ]"
          >
            <Archive class="w-3.5 h-3.5" />
            <span>📦 Arsip SPK</span>
          </button>
        </div>

        <button
          @click="loadData"
          class="px-4 py-2.5 glass-card hover:bg-white rounded-xl text-slate-700 hover:text-slate-900 text-xs font-bold flex items-center gap-2 shadow-xs transition-all duration-200 active:scale-95 border border-slate-200/80 cursor-pointer"
        >
          <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin' : '']" />
          <span>Refresh</span>
        </button>
        <button
          v-if="auth.hasPermission('admin_spk', 'create')"
          @click="$emit('open-create')"
          class="px-5 py-2.5 bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 hover:from-brand-800 hover:to-brand-600 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-md shadow-brand-900/25 active:scale-95 transition-all duration-200 cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>+ Buat SPK Baru</span>
        </button>
      </div>
    </div>

    <!-- Quick Status Summary Cards (Interactive Filters) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
      <div
        @click="setStatusFilter('ALL')"
        :class="[
          'glass-card rounded-2xl p-3.5 border shadow-xs flex items-center gap-3 cursor-pointer transition-all duration-200 active:scale-95',
          selectedQuickStatus === 'ALL' ? 'bg-brand-50/80 border-brand-300 ring-2 ring-brand-500/20' : 'border-white/80 hover:bg-slate-50'
        ]"
      >
        <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
          <Layers class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">TOTAL SEMUA SPK</div>
          <div class="text-lg font-black text-slate-900">{{ totalWorkOrdersCount }}</div>
        </div>
      </div>

      <div
        @click="setStatusFilter('IN_PROGRESS')"
        :class="[
          'glass-card rounded-2xl p-3.5 border shadow-xs flex items-center gap-3 cursor-pointer transition-all duration-200 active:scale-95',
          selectedQuickStatus === 'IN_PROGRESS' ? 'bg-blue-50 border-blue-300 ring-2 ring-blue-500/20' : 'border-white/80 hover:bg-slate-50'
        ]"
      >
        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
          <Clock class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Proses Lapangan</div>
          <div class="text-lg font-black text-blue-950">
            {{ inProgressCount }}
          </div>
        </div>
      </div>

      <div
        @click="setStatusFilter('REVIEW')"
        :class="[
          'glass-card rounded-2xl p-3.5 border shadow-xs flex items-center gap-3 cursor-pointer transition-all duration-200 active:scale-95',
          selectedQuickStatus === 'REVIEW' ? 'bg-purple-50 border-purple-300 ring-2 ring-purple-500/20' : 'border-white/80 hover:bg-slate-50'
        ]"
      >
        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
          <Eye class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[10px] font-bold text-purple-600 uppercase tracking-wider">Antrian Review</div>
          <div class="text-lg font-black text-purple-950">
            {{ reviewCount }}
          </div>
        </div>
      </div>

      <div
        @click="setStatusFilter('COMPLETED')"
        :class="[
          'glass-card rounded-2xl p-3.5 border shadow-xs flex items-center gap-3 cursor-pointer transition-all duration-200 active:scale-95',
          selectedQuickStatus === 'COMPLETED' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-500/20' : 'border-white/80 hover:bg-slate-50'
        ]"
      >
        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
          <CheckCircle2 class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Selesai / BA</div>
          <div class="text-lg font-black text-emerald-950">
            {{ completedCount }}
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Status Tab Pills Filter (TOTAL + Stage Pills) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar text-xs font-bold">
      <button
        type="button"
        @click="setStatusFilter('ALL')"
        :class="[
          'px-3.5 py-2 rounded-xl transition-all cursor-pointer whitespace-nowrap flex items-center gap-2 border',
          selectedQuickStatus === 'ALL'
            ? 'bg-slate-900 text-white border-slate-900 shadow-sm'
            : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'
        ]"
      >
        <Layers class="w-3.5 h-3.5 text-amber-400" />
        <span>TOTAL SEMUA PEMESANAN</span>
        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono bg-white/20 text-white">
          {{ totalWorkOrdersCount }}
        </span>
      </button>

      <button
        type="button"
        @click="setStatusFilter('IN_PROGRESS')"
        :class="[
          'px-3.5 py-2 rounded-xl transition-all cursor-pointer whitespace-nowrap flex items-center gap-2 border',
          selectedQuickStatus === 'IN_PROGRESS'
            ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
            : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'
        ]"
      >
        <Clock class="w-3.5 h-3.5" />
        <span>Sedang Dikerjakan</span>
        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono bg-blue-100 text-blue-800">
          {{ inProgressCount }}
        </span>
      </button>

      <button
        type="button"
        @click="setStatusFilter('REVIEW')"
        :class="[
          'px-3.5 py-2 rounded-xl transition-all cursor-pointer whitespace-nowrap flex items-center gap-2 border',
          selectedQuickStatus === 'REVIEW'
            ? 'bg-purple-700 text-white border-purple-700 shadow-sm'
            : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'
        ]"
      >
        <Eye class="w-3.5 h-3.5" />
        <span>Menunggu Review</span>
        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono bg-purple-100 text-purple-800">
          {{ reviewCount }}
        </span>
      </button>

      <button
        type="button"
        @click="setStatusFilter('COMPLETED')"
        :class="[
          'px-3.5 py-2 rounded-xl transition-all cursor-pointer whitespace-nowrap flex items-center gap-2 border',
          selectedQuickStatus === 'COMPLETED'
            ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm'
            : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'
        ]"
      >
        <CheckCircle2 class="w-3.5 h-3.5" />
        <span>Selesai & BA</span>
        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono bg-emerald-100 text-emerald-800">
          {{ completedCount }}
        </span>
      </button>
    </div>

    <!-- Archive Mode Indicator Banner -->
    <div
      v-if="activeTab === 'ARCHIVED'"
      class="p-4 bg-amber-500/10 border border-amber-300 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-amber-950 backdrop-blur-md"
    >
      <div class="flex items-center gap-2.5">
        <Archive class="w-5 h-5 text-amber-700 shrink-0" />
        <div>
          <div class="font-bold text-sm">Mode Tampilan: 📦 Arsip SPK (Superuser Only)</div>
          <div class="text-[11px] text-amber-800">Menampilkan daftar pekerjaan lampau yang telah diarsipkan dari antrian aktif operasional.</div>
        </div>
      </div>
      <button
        type="button"
        @click="switchTab('ACTIVE')"
        class="px-3.5 py-1.5 bg-white text-slate-700 hover:text-slate-900 border border-slate-200 font-bold text-xs rounded-xl shadow-2xs cursor-pointer active:scale-95 transition-all self-start sm:self-auto"
      >
        ← Kembali ke SPK Aktif
      </button>
    </div>

    <!-- Modern Glass Search & Multi-Filter Bar -->
    <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass space-y-3">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
        <div class="relative md:col-span-2">
          <Search class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
          <input
            type="text"
            v-model="search"
            placeholder="Cari No. SPK, nama cabang/toko, judul..."
            class="w-full pl-9 pr-3 py-2.5 bg-white border border-slate-200/90 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all shadow-2xs font-medium placeholder:text-slate-400"
          />
        </div>

        <!-- Date Range Period Filter -->
        <div>
          <select
            v-model="datePeriodFilter"
            class="w-full px-3 py-2.5 bg-white border border-slate-200/90 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all shadow-2xs font-medium cursor-pointer"
          >
            <option value="ALL">🗓️ Semua Periode Waktu</option>
            <option value="THIS_MONTH">🗓️ Bulan Ini ({{ currentMonthName }})</option>
            <option value="LAST_MONTH">🗓️ Bulan Lalu</option>
            <option value="THIS_QUARTER">🗓️ Kuartal Ini</option>
            <option value="CUSTOM">🗓️ Kustom Tanggal...</option>
          </select>
        </div>

        <div>
          <select
            v-model="vendorFilter"
            class="w-full px-3 py-2.5 bg-white border border-slate-200/90 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all shadow-2xs font-medium cursor-pointer"
          >
            <option value="">🏢 Semua Client / Mitra</option>
            <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
          </select>
        </div>

        <div>
          <select
            v-model="areaFilter"
            class="w-full px-3 py-2.5 bg-white border border-slate-200/90 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all shadow-2xs font-medium cursor-pointer"
          >
            <option value="">📍 Semua Wilayah Area</option>
            <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.name }}</option>
          </select>
        </div>
      </div>

      <!-- Custom Date Inputs (Appears when CUSTOM period selected) -->
      <div v-if="datePeriodFilter === 'CUSTOM'" class="flex flex-wrap items-center gap-3 pt-2 border-t border-slate-100 text-xs">
        <div class="flex items-center gap-2">
          <span class="text-slate-500 font-bold">Dari Tanggal:</span>
          <input
            type="date"
            v-model="customStartDate"
            class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl font-mono text-xs"
          />
        </div>
        <div class="flex items-center gap-2">
          <span class="text-slate-500 font-bold">Sampai Tanggal:</span>
          <input
            type="date"
            v-model="customEndDate"
            class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl font-mono text-xs"
          />
        </div>
        <button
          type="button"
          @click="resetFilters"
          class="text-[11px] font-bold text-brand-700 hover:underline cursor-pointer"
        >
          Reset Filter Tanggal
        </button>
      </div>

      <!-- Active Filter Summary Indicator -->
      <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
        <span>Menampilkan <strong class="text-slate-800 font-bold">{{ filteredWorkOrders.length }}</strong> dari total {{ allWorkOrders.length }} pemesanan SPK.</span>
        <button
          v-if="hasActiveFilters"
          type="button"
          @click="resetFilters"
          class="text-rose-600 hover:text-rose-800 font-bold flex items-center gap-1 cursor-pointer"
        >
          <span>✕ Hapus Semua Filter</span>
        </button>
      </div>
    </div>

    <!-- Wide High-Density Information Table (No Sideways Scroll) -->
    <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
      <div class="w-full">
        <table class="w-full text-left text-xs table-auto border-collapse">
          <thead class="bg-slate-100/80 text-slate-600 font-bold border-b border-slate-200/80 text-[11px] uppercase tracking-wider">
            <tr>
              <th class="py-3.5 px-5 w-[34%]">No. SPK & Informasi Pekerjaan</th>
              <th class="py-3.5 px-4 w-[22%]">Client & Pelaksana SGX</th>
              <th class="py-3.5 px-4 w-[14%]">Target Deadline</th>
              <th class="py-3.5 px-4 w-[18%]">Progres & Status</th>
              <th class="py-3.5 px-5 w-[12%] text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/90 text-slate-700 bg-white/60">
            <template v-if="loading">
              <tr>
                <td colspan="5" class="py-14 text-center text-slate-400 font-medium">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <RefreshCw class="w-6 h-6 animate-spin text-brand-600" />
                    <span>Memuat data pekerjaan SPK...</span>
                  </div>
                </td>
              </tr>
            </template>

            <template v-else-if="filteredWorkOrders.length > 0">
              <tr
                v-for="wo in filteredWorkOrders"
                :key="wo.id"
                @click="$emit('select-work-order', wo.id)"
                class="hover:bg-brand-50/60 cursor-pointer transition-all duration-150 group"
              >
                <!-- Column 1: SPK & Job Information -->
                <td class="py-4 px-5 align-middle">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono font-bold text-[11px] bg-slate-100 group-hover:bg-brand-100 text-brand-950 px-2 py-0.5 rounded-lg border border-slate-200/60 transition-colors">
                      {{ wo.spk_number }}
                    </span>
                    <span class="text-[10px] font-semibold text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded">
                      {{ wo.jobType?.name || wo.job_type?.name || wo.job_type_name || 'Project' }}
                    </span>
                  </div>
                  <div class="text-slate-900 font-bold text-sm leading-snug group-hover:text-brand-900 transition-colors">
                    {{ wo.title }}
                  </div>
                  <div class="flex items-center gap-1.5 text-slate-500 text-[11px] mt-1">
                    <MapPin class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                    <span class="font-medium text-slate-700">{{ wo.area?.name || wo.area_name || 'Wilayah' }}</span>
                    <span class="text-slate-300">•</span>
                    <span class="text-slate-500 truncate max-w-md">{{ wo.location_name }}</span>
                  </div>
                </td>

                <!-- Column 2: Vendor & Field Team PIC -->
                <td class="py-4 px-4 align-middle">
                  <div class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                    <Building class="w-3.5 h-3.5 text-brand-600 shrink-0" />
                    <span class="truncate">{{ wo.vendor?.name || wo.vendor_name || 'Client / Mitra' }}</span>
                  </div>
                  <div class="mt-1 flex items-center gap-1.5 text-[11px]">
                    <User class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                    <div v-if="wo.pic?.name || wo.pic_name">
                      <span class="font-semibold text-slate-700">{{ wo.pic?.name || wo.pic_name }}</span>
                      <span class="text-slate-400 font-mono text-[10px] ml-1">({{ wo.pic?.phone || wo.pic_phone || '-' }})</span>
                    </div>
                    <span v-else class="text-amber-600 italic font-medium text-[10px]">Belum ditugaskan</span>
                  </div>
                </td>

                <!-- Column 3: Deadline -->
                <td class="py-4 px-4 align-middle">
                  <div class="flex items-center gap-1.5 text-slate-800 font-mono font-semibold text-xs">
                    <Calendar class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                    <span>{{ wo.deadline }}</span>
                  </div>
                  <div class="text-[10px] text-slate-400 mt-0.5">
                    Target Penyelesaian
                  </div>
                </td>

                <!-- Column 4: Progress & Status -->
                <td class="py-4 px-4 align-middle">
                  <div class="space-y-1.5">
                    <div class="flex items-center justify-between gap-1 flex-wrap">
                      <div class="flex items-center gap-1">
                        <StatusBadge :status="wo.status" />
                        <span
                          v-if="wo.is_archived"
                          class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-amber-500 text-white shadow-2xs"
                        >
                          📦 ARSIP
                        </span>
                      </div>
                      <span class="font-mono font-bold text-[11px] text-brand-900">{{ wo.progress_percent }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200/60">
                      <div
                        class="h-full rounded-full transition-all duration-300"
                        :class="getProgressClass(wo.status, wo.progress_percent)"
                        :style="{ width: `${wo.progress_percent}%` }"
                      />
                    </div>
                  </div>
                </td>

                <!-- Column 5: Actions -->
                <td class="py-4 px-5 align-middle text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <button
                      v-if="isSupervisor"
                      @click.stop="openEditSpk(wo.id)"
                      class="p-2 bg-white hover:bg-purple-700 hover:text-white rounded-xl text-purple-800 font-bold transition-all border border-purple-200/90 shadow-2xs active:scale-95 cursor-pointer"
                      title="Edit Data & Pengaturan SPK (Supervisor Only)"
                    >
                      <Pencil class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click.stop="$emit('select-work-order', wo.id)"
                      class="px-3.5 py-2 bg-white group-hover:bg-brand-900 group-hover:text-white rounded-xl text-slate-700 font-bold transition-all duration-200 inline-flex items-center gap-1.5 border border-slate-200/90 shadow-2xs active:scale-95 cursor-pointer"
                    >
                      <Eye class="w-3.5 h-3.5" />
                      <span>Detail</span>
                    </button>
                  </div>
                </td>
              </tr>
            </template>

            <template v-else>
              <tr>
                <td colspan="5" class="py-14 text-center text-slate-400">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <Archive v-if="activeTab === 'ARCHIVED'" class="w-8 h-8 opacity-30 text-amber-500" />
                    <FileText v-else class="w-8 h-8 opacity-30 text-slate-400" />
                    <p class="font-medium">
                      {{ activeTab === 'ARCHIVED' ? 'Belum ada pekerjaan SPK yang diarsipkan.' : 'Tidak ada data pekerjaan yang sesuai dengan filter.' }}
                    </p>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Supervisor Edit SPK Modal -->
    <WorkOrderEditModal
      :isOpen="editModalOpen"
      :workOrderId="editingWorkOrderId"
      @close="editModalOpen = false"
      @updated="loadData"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../../services/api';
import { useAuth } from '../../composables/useAuth';
import StatusBadge from '../../components/StatusBadge.vue';
import WorkOrderEditModal from './WorkOrderEditModal.vue';
import {
  Plus,
  Search,
  RefreshCw,
  Eye,
  FileText,
  MapPin,
  Building,
  User,
  Calendar,
  Layers,
  Clock,
  CheckCircle2,
  Pencil,
  Archive,
  FileSpreadsheet
} from 'lucide-vue-next';

defineEmits(['open-create', 'select-work-order']);

const auth = useAuth();
const isSupervisor = computed(() => ['SUPERUSER', 'SUPERVISOR'].includes(auth.state.user?.role));
const isSuperuser = computed(() => auth.state.user?.role === 'SUPERUSER');
const editModalOpen = ref(false);
const editingWorkOrderId = ref(null);
const activeTab = ref('ACTIVE'); // 'ACTIVE' | 'ARCHIVED'

const allWorkOrders = ref([]);
const workOrders = computed(() => allWorkOrders.value);
const vendors = ref([]);
const areas = ref([]);
const loading = ref(true);

const search = ref('');
const selectedQuickStatus = ref('ALL'); // 'ALL' | 'IN_PROGRESS' | 'REVIEW' | 'COMPLETED'
const vendorFilter = ref('');
const areaFilter = ref('');
const datePeriodFilter = ref('ALL');
const customStartDate = ref('');
const customEndDate = ref('');

const currentMonthName = computed(() => {
  return new Date().toLocaleString('id-ID', { month: 'long', year: 'numeric' });
});

const totalWorkOrdersCount = computed(() => allWorkOrders.value.length);
const inProgressCount = computed(() => {
  return allWorkOrders.value.filter(w => ['ASSIGNED', 'CHECKED_IN', 'IN_PROGRESS'].includes(w.status)).length;
});
const reviewCount = computed(() => {
  return allWorkOrders.value.filter(w => ['SUBMITTED', 'UNDER_REVIEW', 'REVISION'].includes(w.status)).length;
});
const completedCount = computed(() => {
  return allWorkOrders.value.filter(w => ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(w.status)).length;
});

function setStatusFilter(status) {
  selectedQuickStatus.value = status;
}

const hasActiveFilters = computed(() => {
  return search.value || selectedQuickStatus.value !== 'ALL' || vendorFilter.value || areaFilter.value || datePeriodFilter.value !== 'ALL' || customStartDate.value || customEndDate.value;
});

function resetFilters() {
  search.value = '';
  selectedQuickStatus.value = 'ALL';
  vendorFilter.value = '';
  areaFilter.value = '';
  datePeriodFilter.value = 'ALL';
  customStartDate.value = '';
  customEndDate.value = '';
}

const filteredWorkOrders = computed(() => {
  let list = allWorkOrders.value;

  // 1. Quick Status Tab Filtering
  if (selectedQuickStatus.value === 'IN_PROGRESS') {
    list = list.filter(w => ['ASSIGNED', 'CHECKED_IN', 'IN_PROGRESS'].includes(w.status));
  } else if (selectedQuickStatus.value === 'REVIEW') {
    list = list.filter(w => ['SUBMITTED', 'UNDER_REVIEW', 'REVISION'].includes(w.status));
  } else if (selectedQuickStatus.value === 'COMPLETED') {
    list = list.filter(w => ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(w.status));
  }

  // 2. Vendor Filtering
  if (vendorFilter.value) {
    list = list.filter(w => String(w.vendor_id) === String(vendorFilter.value));
  }

  // 3. Area Filtering
  if (areaFilter.value) {
    list = list.filter(w => String(w.area_id) === String(areaFilter.value));
  }

  // 4. Date Period Filtering
  if (datePeriodFilter.value !== 'ALL') {
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth();

    if (datePeriodFilter.value === 'THIS_MONTH') {
      list = list.filter(w => {
        const d = new Date(w.start_date || w.created_at);
        return d.getFullYear() === currentYear && d.getMonth() === currentMonth;
      });
    } else if (datePeriodFilter.value === 'LAST_MONTH') {
      const lastMonth = currentMonth === 0 ? 11 : currentMonth - 1;
      const lastYear = currentMonth === 0 ? currentYear - 1 : currentYear;
      list = list.filter(w => {
        const d = new Date(w.start_date || w.created_at);
        return d.getFullYear() === lastYear && d.getMonth() === lastMonth;
      });
    } else if (datePeriodFilter.value === 'THIS_QUARTER') {
      const currentQuarter = Math.floor(currentMonth / 3);
      list = list.filter(w => {
        const d = new Date(w.start_date || w.created_at);
        return d.getFullYear() === currentYear && Math.floor(d.getMonth() / 3) === currentQuarter;
      });
    } else if (datePeriodFilter.value === 'CUSTOM') {
      if (customStartDate.value) {
        list = list.filter(w => (w.start_date || w.created_at) >= customStartDate.value);
      }
      if (customEndDate.value) {
        list = list.filter(w => (w.start_date || w.created_at) <= customEndDate.value);
      }
    }
  }

  // 5. Global Search Filtering
  if (search.value) {
    const q = search.value.toLowerCase().trim();
    list = list.filter(w => {
      const matchSpk = w.spk_number?.toLowerCase().includes(q);
      const matchTitle = w.title?.toLowerCase().includes(q);
      const matchLoc = w.location_name?.toLowerCase().includes(q);
      const matchVendor = (w.vendor?.name || w.vendor_name || '').toLowerCase().includes(q);
      const matchPic = (w.pic?.name || w.pic_name || '').toLowerCase().includes(q);
      return matchSpk || matchTitle || matchLoc || matchVendor || matchPic;
    });
  }

  return list;
});

function exportCSV() {
  const data = filteredWorkOrders.value;
  if (!data || data.length === 0) return;

  const headers = [
    'No. SPK',
    'Judul Pekerjaan',
    'Klien / Perusahaan',
    'Wilayah / Area',
    'Lokasi / Cabang Toko',
    'PIC Lapangan',
    'Tanggal Mulai',
    'Target Selesai',
    'Progres (%)',
    'Status SPK'
  ];

  const rows = data.map(wo => [
    `"${(wo.spk_number || '').replace(/"/g, '""')}"`,
    `"${(wo.title || '').replace(/"/g, '""')}"`,
    `"${(wo.vendor?.name || wo.vendor_name || '').replace(/"/g, '""')}"`,
    `"${(wo.area?.name || wo.area_name || '').replace(/"/g, '""')}"`,
    `"${(wo.location_name || '').replace(/"/g, '""')}"`,
    `"${(wo.pic?.name || wo.pic_name || '').replace(/"/g, '""')}"`,
    `"${wo.start_date || ''}"`,
    `"${wo.deadline || ''}"`,
    `"${wo.progress_percent || 0}%"`,
    `"${wo.status || ''}"`
  ]);

  const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `Rekapitulasi_SPK_Pemesanan_SGX_${new Date().toISOString().slice(0, 10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function switchTab(tab) {
  activeTab.value = tab;
  loadData();
}

function openEditSpk(id) {
  editingWorkOrderId.value = id;
  editModalOpen.value = true;
}

function getProgressClass(status, progress) {
  if (['APPROVED', 'COMPLETED'].includes(status)) {
    return 'bg-gradient-to-r from-emerald-600 to-teal-500';
  }
  if (['SUBMITTED', 'UNDER_REVIEW'].includes(status)) {
    return 'bg-gradient-to-r from-purple-600 to-indigo-500';
  }
  if (status === 'REVISION') {
    return 'bg-gradient-to-r from-rose-500 to-amber-500';
  }
  return 'bg-gradient-to-r from-brand-600 to-brand-500';
}

async function loadData() {
  loading.value = true;
  try {
    const params = {};
    if (activeTab.value === 'ARCHIVED') {
      params.archived = 'true';
    }

    const [woRes, vRes, aRes] = await Promise.all([
      api.getWorkOrders(params),
      api.getVendors(),
      api.getAreas()
    ]);
    allWorkOrders.value = woRes.data || [];
    vendors.value = vRes.data || [];
    areas.value = aRes.data || [];
  } catch (err) {
    console.error('Failed to load work orders:', err);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadData();
});
</script>
