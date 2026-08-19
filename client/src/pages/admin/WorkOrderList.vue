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

      <div class="flex items-center gap-2.5">
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

    <!-- Quick Status Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
      <div class="glass-card rounded-2xl p-3.5 border border-white/80 shadow-xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
          <Layers class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total SPK</div>
          <div class="text-lg font-black text-slate-900">{{ workOrders.length }}</div>
        </div>
      </div>

      <div class="glass-card rounded-2xl p-3.5 border border-white/80 shadow-xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
          <Clock class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Proses Lapangan</div>
          <div class="text-lg font-black text-blue-950">
            {{ workOrders.filter(w => ['ASSIGNED', 'CHECKED_IN', 'IN_PROGRESS'].includes(w.status)).length }}
          </div>
        </div>
      </div>

      <div class="glass-card rounded-2xl p-3.5 border border-white/80 shadow-xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
          <Eye class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[10px] font-bold text-purple-600 uppercase tracking-wider">Antrian Review</div>
          <div class="text-lg font-black text-purple-950">
            {{ workOrders.filter(w => ['SUBMITTED', 'UNDER_REVIEW', 'REVISION'].includes(w.status)).length }}
          </div>
        </div>
      </div>

      <div class="glass-card rounded-2xl p-3.5 border border-white/80 shadow-xs flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
          <CheckCircle2 class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Selesai / BA</div>
          <div class="text-lg font-black text-emerald-950">
            {{ workOrders.filter(w => ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(w.status)).length }}
          </div>
        </div>
      </div>
    </div>

    <!-- Modern Glass Search & Filter Bar -->
    <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass">
      <form @submit.prevent="loadData" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
        <div class="relative">
          <Search class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
          <input
            type="text"
            v-model="search"
            placeholder="Cari No. SPK, judul, lokasi..."
            class="w-full pl-9 pr-3 py-2.5 bg-white border border-slate-200/90 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all shadow-2xs font-medium placeholder:text-slate-400"
          />
        </div>

        <div>
          <select
            v-model="statusFilter"
            @change="loadData"
            class="w-full px-3 py-2.5 bg-white border border-slate-200/90 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all shadow-2xs font-medium cursor-pointer"
          >
            <option value="">Semua Status SPK</option>
            <option value="DRAFT">DRAFT — Draft Baru</option>
            <option value="ASSIGNED">ASSIGNED — Ditugaskan</option>
            <option value="IN_PROGRESS">IN_PROGRESS — Sedang Dikerjakan</option>
            <option value="SUBMITTED">SUBMITTED — Siap Direview</option>
            <option value="REVISION">REVISION — Permintaan Revisi</option>
            <option value="APPROVED">APPROVED — Disetujui</option>
            <option value="BA_OPNAME">BA_OPNAME — BA Diterbitkan</option>
            <option value="COMPLETED">COMPLETED — Selesai 100%</option>
          </select>
        </div>

        <div>
          <select
            v-model="vendorFilter"
            @change="loadData"
            class="w-full px-3 py-2.5 bg-white border border-slate-200/90 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all shadow-2xs font-medium cursor-pointer"
          >
            <option value="">Semua Perusahaan Client</option>
            <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
          </select>
        </div>

        <div>
          <select
            v-model="areaFilter"
            @change="loadData"
            class="w-full px-3 py-2.5 bg-white border border-slate-200/90 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all shadow-2xs font-medium cursor-pointer"
          >
            <option value="">Semua Area Operasional</option>
            <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.name }}</option>
          </select>
        </div>
      </form>
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

            <template v-else-if="workOrders.length > 0">
              <tr
                v-for="wo in workOrders"
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
                    <div class="flex items-center justify-between">
                      <StatusBadge :status="wo.status" />
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
                  <button
                    @click.stop="$emit('select-work-order', wo.id)"
                    class="px-3.5 py-2 bg-white group-hover:bg-brand-900 group-hover:text-white rounded-xl text-slate-700 font-bold transition-all duration-200 inline-flex items-center gap-1.5 border border-slate-200/90 shadow-2xs active:scale-95 cursor-pointer"
                  >
                    <Eye class="w-3.5 h-3.5" />
                    <span>Detail</span>
                  </button>
                </td>
              </tr>
            </template>

            <template v-else>
              <tr>
                <td colspan="5" class="py-14 text-center text-slate-400">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <FileText class="w-8 h-8 opacity-30 text-slate-400" />
                    <p class="font-medium">Tidak ada data pekerjaan yang sesuai dengan filter.</p>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../../services/api';
import { useAuth } from '../../composables/useAuth';
import StatusBadge from '../../components/StatusBadge.vue';

const auth = useAuth();
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
  CheckCircle2
} from 'lucide-vue-next';

defineEmits(['open-create', 'select-work-order']);

const workOrders = ref([]);
const vendors = ref([]);
const areas = ref([]);
const loading = ref(true);

const search = ref('');
const statusFilter = ref('');
const vendorFilter = ref('');
const areaFilter = ref('');

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
    const [woRes, vRes, aRes] = await Promise.all([
      api.getWorkOrders({
        search: search.value,
        status: statusFilter.value,
        vendor_id: vendorFilter.value,
        area_id: areaFilter.value
      }),
      api.getVendors(),
      api.getAreas()
    ]);
    workOrders.value = woRes.data || [];
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
