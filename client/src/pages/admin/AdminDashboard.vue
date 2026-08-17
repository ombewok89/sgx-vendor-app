<template>
  <div class="space-y-6">
    <!-- Header with Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-brand-900 to-brand-600 flex items-center justify-center text-white shadow-md shadow-brand-900/20">
            <LayoutDashboard class="w-4 h-4" />
          </div>
          <span>Dashboard Operasional</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1 font-medium">Monitoring status pekerjaan vendor, GPS check-in, dan antrian review secara real-time.</p>
      </div>
      <div class="flex items-center gap-2">
        <button
          @click="loadDashboardData"
          class="px-3.5 py-2 glass-card hover:bg-white rounded-xl text-slate-700 hover:text-slate-900 text-xs font-bold flex items-center gap-2 shadow-xs transition-all duration-200 active:scale-95 border border-slate-200/80"
        >
          <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin' : '']" />
          <span class="hidden sm:inline">Refresh</span>
        </button>
        <button
          @click="$emit('open-create-spk')"
          class="px-4 py-2 bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 hover:from-brand-800 hover:to-brand-600 text-white rounded-xl transition-all duration-200 text-xs font-bold flex items-center gap-2 shadow-md shadow-brand-900/20 active:scale-95"
        >
          <Plus class="w-4 h-4" />
          <span>+ Buat SPK Baru</span>
        </button>
      </div>
    </div>

    <!-- Operational Alerts -->
    <div v-if="kpis?.alerts && kpis.alerts.length > 0" class="space-y-2">
      <div
        v-for="(alert, idx) in kpis.alerts"
        :key="idx"
        :class="[
          'p-3.5 rounded-2xl border flex items-center justify-between text-xs font-semibold backdrop-blur-md transition-all shadow-xs',
          alert.type === 'danger'
            ? 'bg-rose-500/10 border-rose-300 text-rose-900'
            : alert.type === 'warning'
            ? 'bg-purple-500/10 border-purple-300 text-purple-900'
            : 'bg-amber-500/10 border-amber-300 text-amber-900'
        ]"
      >
        <div class="flex items-center gap-2.5">
          <AlertTriangle class="w-4 h-4 shrink-0" />
          <span>{{ alert.message }}</span>
        </div>
        <button
          v-if="alert.type === 'warning'"
          @click="$emit('open-review')"
          class="px-3 py-1 bg-purple-700 text-white rounded-lg text-[11px] font-bold hover:bg-purple-800 transition-colors active:scale-95"
        >
          Buka Antrian Review
        </button>
      </div>
    </div>

    <!-- Glassmorphic KPI Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
      <div
        v-for="(kpi, idx) in kpiCards"
        :key="idx"
        @click="kpi.action ? kpi.action() : null"
        :class="[
          'glass-card p-4 rounded-2xl border border-white/70 shadow-glass transition-all duration-300 flex flex-col justify-between group',
          kpi.action ? 'cursor-pointer hover:border-brand-300 hover:-translate-y-1 hover:shadow-glass-hover' : ''
        ]"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-[10px] font-bold tracking-wider text-slate-400 uppercase">{{ kpi.label }}</span>
          <div :class="['p-2 rounded-xl border', kpi.bg, kpi.border]">
            <component :is="kpi.icon" :class="['w-4 h-4', kpi.color]" />
          </div>
        </div>
        <div :class="['text-2xl font-black font-mono tracking-tight group-hover:scale-105 transition-transform duration-200', kpi.color]">
          {{ kpi.value }}
        </div>
      </div>
    </div>

    <!-- Recent Work Orders Table (Glassmorphic Container) -->
    <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
      <div class="p-5 border-b border-slate-200/80 flex items-center justify-between">
        <div>
          <h3 class="font-bold text-sm text-slate-900">Pekerjaan / SPK Terbaru</h3>
          <p class="text-xs text-slate-400 mt-0.5">Daftar Surat Perintah Kerja yang baru diterbitkan dan sedang berjalan</p>
        </div>
        <button
          @click="$emit('navigate-to-spk')"
          class="text-xs font-bold text-brand-700 hover:text-brand-900 flex items-center gap-1 group"
        >
          <span>Lihat Semua SPK</span>
          <span class="group-hover:translate-x-0.5 transition-transform">→</span>
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100/60 text-slate-500 font-bold border-b border-slate-200/80">
            <tr>
              <th class="py-3 px-4">No. SPK & Nama Pekerjaan</th>
              <th class="py-3 px-4">Vendor</th>
              <th class="py-3 px-4">Area & Lokasi</th>
              <th class="py-3 px-4">PIC Lapangan</th>
              <th class="py-3 px-4">Deadline</th>
              <th class="py-3 px-4">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/80 text-slate-700">
            <template v-if="recentOrders.length > 0">
              <tr
                v-for="order in recentOrders"
                :key="order.id"
                class="hover:bg-brand-50/40 transition-colors"
              >
                <td class="py-3.5 px-4">
                  <div class="font-mono font-bold text-brand-900 text-[11px]">{{ order.spk_number }}</div>
                  <div class="text-slate-700 font-semibold truncate max-w-xs mt-0.5">{{ order.title }}</div>
                </td>
                <td class="py-3.5 px-4 font-bold text-slate-800">{{ order.vendor_name }}</td>
                <td class="py-3.5 px-4">
                  <div class="font-medium text-slate-800">{{ order.area_name }}</div>
                  <div class="text-slate-400 text-[10px] truncate max-w-[180px]">{{ order.location_name }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <span v-if="order.pic_name" class="font-medium text-slate-800">{{ order.pic_name }}</span>
                  <span v-else class="text-amber-600 italic font-medium">Belum ditentukan</span>
                </td>
                <td class="py-3.5 px-4 font-mono text-slate-600">{{ order.deadline }}</td>
                <td class="py-3.5 px-4">
                  <StatusBadge :status="order.status" />
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="6" class="py-10 text-center text-slate-400 font-medium">
                Belum ada data pekerjaan. Klik "Buat SPK Baru" untuk memulai.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../../services/api';
import StatusBadge from '../../components/StatusBadge.vue';
import {
  LayoutDashboard,
  Briefcase,
  Clock,
  CheckCircle2,
  AlertTriangle,
  AlertCircle,
  TrendingUp,
  Plus,
  RefreshCw
} from 'lucide-vue-next';

const emit = defineEmits(['navigate-to-spk', 'open-create-spk', 'open-review']);

const kpis = ref(null);
const recentOrders = ref([]);
const loading = ref(true);

async function loadDashboardData() {
  loading.value = true;
  try {
    const [kpiRes, ordersRes] = await Promise.all([
      api.getDashboardKpis(),
      api.getWorkOrders({ limit: 6 })
    ]);
    kpis.value = kpiRes.data;
    recentOrders.value = (ordersRes.data || []).slice(0, 6);
  } catch (err) {
    console.error('Error loading dashboard:', err);
  } finally {
    loading.value = false;
  }
}

const kpiCards = computed(() => [
  { label: 'TOTAL SPK', value: kpis.value?.total || 0, icon: Briefcase, color: 'text-slate-900', bg: 'bg-slate-50', border: 'border-slate-200' },
  { label: 'SEDANG BERJALAN', value: kpis.value?.in_progress || 0, icon: TrendingUp, color: 'text-blue-700', bg: 'bg-blue-50/50', border: 'border-blue-200' },
  { label: 'MENUNGGU CHECK-IN', value: kpis.value?.waiting_checkin || 0, icon: Clock, color: 'text-indigo-700', bg: 'bg-indigo-50/50', border: 'border-indigo-200' },
  { label: 'MENUNGGU REVIEW', value: kpis.value?.waiting_review || 0, icon: AlertCircle, color: 'text-purple-700', bg: 'bg-purple-50/50', border: 'border-purple-200', action: () => emit('open-review') },
  { label: 'PERLU REVISI', value: kpis.value?.revision || 0, icon: AlertTriangle, color: 'text-rose-700', bg: 'bg-rose-50/50', border: 'border-rose-200' },
  { label: 'SELESAI (BA OPNAME)', value: kpis.value?.completed || 0, icon: CheckCircle2, color: 'text-emerald-700', bg: 'bg-emerald-50/50', border: 'border-emerald-200' },
]);

onMounted(() => {
  loadDashboardData();
});
</script>
