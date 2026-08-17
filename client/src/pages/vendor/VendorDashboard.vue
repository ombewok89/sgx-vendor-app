<template>
  <div class="space-y-5">
    <!-- Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center gap-2.5">
          <h2 class="text-xl font-black text-slate-900 tracking-tight">Portal Monitoring Mitra Vendor</h2>
          <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-800 border border-emerald-300">
            {{ user?.vendor_name || 'Vendor Terdaftar' }}
          </span>
        </div>
        <p class="text-xs text-slate-500 mt-1 font-medium">Pantau progres pekerjaan, bukti dokumentasi lapangan, dan unduh Berita Acara (BA) Opname.</p>
      </div>
      <button
        @click="loadVendorData"
        class="px-3.5 py-2 glass-card hover:bg-white rounded-xl text-slate-700 hover:text-slate-900 text-xs font-bold flex items-center gap-2 shadow-xs transition-all duration-200 active:scale-95 border border-slate-200/80 self-start sm:self-auto"
      >
        <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin' : '']" />
        <span>Refresh</span>
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Left: Work Orders List (Glassmorphic Task Cards) -->
      <div class="glass-card rounded-3xl p-4 shadow-glass border border-white/80 space-y-3">
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5 px-1">
          <h3 class="font-bold text-xs text-slate-800">Daftar Pekerjaan ({{ workOrders.length }})</h3>
          <span class="text-[10px] text-slate-400 font-bold uppercase">ISOLATED VENDOR</span>
        </div>

        <div class="space-y-2.5 max-h-[75vh] overflow-y-auto pr-1 custom-scrollbar">
          <template v-if="workOrders.length > 0">
            <div
              v-for="wo in workOrders"
              :key="wo.id"
              @click="handleSelectOrder(wo.id)"
              :class="[
                'p-4 rounded-2xl border text-xs cursor-pointer transition-all duration-200',
                selectedOrder?.id === wo.id
                  ? 'glass-card border-emerald-500 ring-2 ring-emerald-500/20 shadow-sm translate-x-0.5'
                  : 'bg-white/60 border-slate-200/70 hover:bg-white hover:border-slate-300'
              ]"
            >
              <div class="flex items-center justify-between mb-1.5">
                <span class="font-mono font-bold text-slate-900">{{ wo.spk_number }}</span>
                <StatusBadge :status="wo.status" />
              </div>
              <div class="font-bold text-slate-900 truncate mb-1.5">{{ wo.title }}</div>
              <div class="text-[11px] text-slate-500 flex items-center justify-between font-medium">
                <span>Area: {{ wo.area_name }}</span>
                <span class="font-bold text-emerald-800 font-mono">{{ wo.progress_percent }}%</span>
              </div>
            </div>
          </template>
          <p v-else class="text-slate-400 text-xs text-center py-10 font-medium">Belum ada penugasan pekerjaan untuk vendor Anda.</p>
        </div>
      </div>

      <!-- Right: Vendor Work Order Progress Tracker -->
      <div class="lg:col-span-2 glass-card rounded-3xl p-6 shadow-glass border border-white/80 space-y-6">
        <template v-if="selectedOrder">
          <!-- Header -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-4">
            <div>
              <div class="flex items-center gap-2">
                <span class="font-mono font-bold text-xs bg-slate-100 px-2 py-0.5 rounded text-slate-800">
                  {{ selectedOrder.spk_number }}
                </span>
                <StatusBadge :status="selectedOrder.status" />
              </div>
              <h3 class="font-black text-slate-900 text-base mt-1">{{ selectedOrder.title }}</h3>
            </div>

            <button
              v-if="selectedOrder.ba_document"
              @click="$emit('preview-ba', selectedOrder.ba_document)"
              class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-900/20 flex items-center gap-1.5 active:scale-95 transition-all self-start sm:self-auto"
            >
              <FileCheck2 class="w-4 h-4" />
              <span>Lihat & Unduh BA Opname</span>
            </button>
          </div>

          <!-- Progress Stepper -->
          <div class="glass-card rounded-2xl p-4 border border-white/60">
            <h4 class="font-bold text-xs text-slate-800 mb-2">Status & Tracking Pekerjaan Real-Time</h4>
            <StepperProgress :status="selectedOrder.status" :progressPercent="selectedOrder.progress_percent" />
          </div>

          <!-- Work Details Info -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 bg-white/70 border border-slate-200/80 rounded-2xl space-y-2 text-slate-600 shadow-xs">
              <span class="font-bold text-slate-900 block mb-1">Rincian Lokasi & Waktu</span>
              <div>Alamat: <strong class="text-slate-900">{{ selectedOrder.location_name }}</strong></div>
              <div>Area: <strong class="text-slate-900">{{ selectedOrder.area_name }}</strong></div>
              <div>Periode: <strong class="font-mono text-slate-900">{{ selectedOrder.start_date }} s/d {{ selectedOrder.deadline }}</strong></div>
            </div>

            <div class="p-4 bg-white/70 border border-slate-200/80 rounded-2xl space-y-2 text-slate-600 shadow-xs">
              <span class="font-bold text-slate-900 block mb-1">Pelaksana Lapangan</span>
              <div>PIC Tim: <strong class="text-slate-900">{{ selectedOrder.pic_name || 'Menunggu Penugasan' }}</strong></div>
              <div>Kontak PIC: <strong class="text-slate-900">{{ selectedOrder.pic_phone || '-' }}</strong></div>
              <div>Check-In GPS: <strong class="text-emerald-700">{{ selectedOrder.check_ins?.length > 0 ? 'Sudah Check-In ✓' : 'Belum' }}</strong></div>
            </div>
          </div>

          <!-- Technical Issues / Kendala Lapangan Log -->
          <div class="glass-card rounded-2xl p-4 space-y-3 border border-white/60">
            <div class="flex items-center justify-between">
              <h4 class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                <AlertTriangle class="w-4 h-4 text-amber-600" />
                <span>Monitoring Kendala Lapangan Cabang</span>
              </h4>
              <span
                v-if="selectedOrder.issues && selectedOrder.issues.length > 0"
                class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                :class="selectedOrder.issues.some(i => i.status === 'OPEN') ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800'"
              >
                {{ selectedOrder.issues.filter(i => i.status === 'OPEN').length > 0 ? `${selectedOrder.issues.filter(i => i.status === 'OPEN').length} Butuh Penanganan` : 'Semua Kendala Teratasi ✓' }}
              </span>
            </div>

            <div v-if="selectedOrder.issues && selectedOrder.issues.length > 0" class="space-y-2.5">
              <div
                v-for="iss in selectedOrder.issues"
                :key="iss.id"
                class="p-3 rounded-xl border space-y-1.5 text-xs shadow-xs"
                :class="iss.status === 'OPEN' ? 'bg-amber-50/60 border-amber-200' : 'bg-slate-50/60 border-slate-200'"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span
                      :class="[
                        'px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider',
                        iss.status === 'OPEN' ? 'bg-rose-600 text-white' : 'bg-emerald-600 text-white'
                      ]"
                    >
                      {{ iss.status === 'OPEN' ? 'OPEN' : 'RESOLVED ✓' }}
                    </span>
                    <strong class="text-slate-800 text-[11px]">{{ iss.issue_type || 'Kendala Teknis' }}</strong>
                  </div>
                  <span class="text-[10px] font-mono text-slate-400">
                    {{ new Date(iss.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                  </span>
                </div>

                <p class="text-slate-700 leading-relaxed text-[11px]">
                  "{{ iss.notes }}"
                </p>

                <!-- Solution note if resolved -->
                <div v-if="iss.resolution_notes" class="p-2 bg-emerald-50 rounded-lg text-[10px] text-emerald-950 border border-emerald-200/80">
                  <span class="font-bold text-emerald-800 block mb-0.5">Solusi & Tindak Lanjut:</span>
                  <p>{{ iss.resolution_notes }}</p>
                </div>
              </div>
            </div>
            <p v-else class="text-slate-400 text-xs italic">Pekerjaan berjalan lancar tanpa kendala teknis.</p>
          </div>

          <!-- Evidence Photos Viewer -->
          <div class="space-y-3">
            <h4 class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
              <Camera class="w-4 h-4 text-brand-600" />
              <span>Dokumentasi Foto Pekerjaan ({{ selectedOrder.evidence_photos?.length || 0 }} Foto)</span>
            </h4>

            <div v-if="selectedOrder.evidence_photos && selectedOrder.evidence_photos.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <div
                v-for="p in selectedOrder.evidence_photos"
                :key="p.id"
                class="glass-card rounded-2xl overflow-hidden p-2 border border-white/70 shadow-xs"
              >
                <img
                  :src="getFileUrl(p.file_path)"
                  :alt="`Bukti ${p.stage}`"
                  class="w-full h-28 object-cover rounded-xl mb-1.5"
                  @error="$event.target.src = 'https://images.unsplash.com/photo-1541888946425-d0fbb18086f6?w=400&auto=format&fit=crop&q=60'"
                />
                <div class="flex items-center justify-between text-[10px] font-bold text-slate-800 px-1">
                  <span>{{ p.stage }} #{{ p.sequence }}</span>
                  <span class="text-[8px] font-mono text-emerald-700">TERVERIFIKASI ✓</span>
                </div>
              </div>
            </div>
            <p v-else class="text-slate-400 text-xs italic">Foto dokumentasi akan muncul saat tim lapangan mengunggah bukti pekerjaan.</p>
          </div>
        </template>
        <div v-else class="py-24 text-center text-slate-400 text-xs font-medium">
          Pilih salah satu pekerjaan di sebelah kiri untuk melihat progress tracking.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api, getFileUrl } from '../../services/api';
import { useAuth } from '../../composables/useAuth';
import StatusBadge from '../../components/StatusBadge.vue';
import StepperProgress from '../../components/StepperProgress.vue';
import { FileCheck2, Camera, RefreshCw, AlertTriangle } from 'lucide-vue-next';

defineEmits(['preview-ba']);

const auth = useAuth();
const user = auth.state.user;

const workOrders = ref([]);
const selectedOrder = ref(null);
const loading = ref(true);

async function loadVendorData() {
  loading.value = true;
  try {
    const res = await api.getWorkOrders();
    workOrders.value = res.data || [];
    if (workOrders.value.length > 0) {
      const detail = await api.getWorkOrderById(workOrders.value[0].id);
      selectedOrder.value = detail.data;
    }
  } catch (err) {
    console.error('Failed to load vendor data:', err);
  } finally {
    loading.value = false;
  }
}

async function handleSelectOrder(id) {
  loading.value = true;
  try {
    const detail = await api.getWorkOrderById(id);
    selectedOrder.value = detail.data;
  } catch (err) {
    alert(err.message);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadVendorData();
});
</script>
