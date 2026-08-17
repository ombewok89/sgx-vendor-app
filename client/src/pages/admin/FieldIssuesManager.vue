<template>
  <div class="space-y-6">
    <!-- Header & Scorecards -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-600 to-rose-600 flex items-center justify-center text-white shadow-md shadow-amber-900/20">
            <AlertTriangle class="w-4 h-4" />
          </div>
          <span>Kendala & Hambatan Lapangan</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1 font-medium">
          Monitoring tiket kendala teknis, mitigasi risiko keterlambatan SLA, dan pencatatan tindakan penyelesaian pengawas.
        </p>
      </div>

      <!-- Quick Metrics -->
      <div class="flex items-center gap-2 self-start sm:self-auto">
        <div class="px-3.5 py-1.5 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-800 flex items-center gap-2 shadow-xs">
          <span class="w-2 h-2 rounded-full bg-rose-600 animate-pulse"></span>
          <span>{{ openIssuesCount }} Butuh Tindakan</span>
        </div>
        <div class="px-3.5 py-1.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-bold text-emerald-800 flex items-center gap-2 shadow-xs">
          <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" />
          <span>{{ resolvedIssuesCount }} Teratasi</span>
        </div>
      </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex flex-col md:flex-row items-center justify-between gap-3 text-xs">
      <!-- Status Filter Tabs -->
      <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0 custom-scrollbar">
        <button
          v-for="st in statusOptions"
          :key="st.value"
          @click="selectedStatus = st.value"
          :class="[
            'px-3.5 py-1.5 rounded-xl font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5',
            selectedStatus === st.value
              ? 'bg-slate-900 text-white shadow-xs'
              : 'bg-slate-100 hover:bg-slate-200 text-slate-600'
          ]"
        >
          <span>{{ st.label }}</span>
          <span v-if="st.value === 'OPEN' && openIssuesCount > 0" class="px-1.5 py-0.2 rounded-full text-[9px] bg-rose-500 text-white font-mono">
            {{ openIssuesCount }}
          </span>
        </button>
      </div>

      <!-- Category Filter & Search -->
      <div class="flex items-center gap-2 w-full md:w-auto">
        <select
          v-model="selectedType"
          class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl font-semibold text-slate-700 text-xs cursor-pointer"
        >
          <option value="ALL">Semua Kategori Kendala</option>
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
            placeholder="Cari SPK, catatan kendala..."
            class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs"
          />
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-16">
      <div class="w-8 h-8 border-3 border-amber-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
      <p class="text-xs text-slate-500 font-medium">Memuat tiket kendala lapangan...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredIssues.length === 0" class="text-center py-16 glass-card rounded-3xl border border-dashed border-slate-300">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 mx-auto mb-3">
        <CheckCircle2 class="w-6 h-6" />
      </div>
      <h3 class="font-bold text-slate-700 text-sm">Tidak ada kendala lapangan aktif</h3>
      <p class="text-xs text-slate-400 mt-1">Seluruh pekerjaan proyek berjalan lancar sesuai rencana.</p>
    </div>

    <!-- Issues List -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="issue in filteredIssues"
        :key="issue.id"
        :class="[
          'glass-card rounded-3xl p-5 border shadow-glass space-y-4 flex flex-col justify-between transition-all duration-300',
          issue.status === 'OPEN' ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200/80 bg-white/80'
        ]"
      >
        <div class="space-y-3">
          <!-- Top Row: Badge Status & Category -->
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-1.5">
              <span
                :class="[
                  'px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider flex items-center gap-1 shadow-xs',
                  issue.status === 'OPEN' ? 'bg-rose-600 text-white animate-pulse' : 'bg-emerald-600 text-white'
                ]"
              >
                <AlertCircle v-if="issue.status === 'OPEN'" class="w-3 h-3" />
                <CheckCircle2 v-else class="w-3 h-3" />
                <span>{{ issue.status === 'OPEN' ? 'BUTUH TINDAKAN' : 'TERATASI' }}</span>
              </span>

              <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                {{ formatCategory(issue.issue_type) }}
              </span>
            </div>

            <span class="text-[10px] font-mono text-slate-400">
              {{ new Date(issue.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
            </span>
          </div>

          <!-- Project & Location Details -->
          <div>
            <div class="text-[10px] font-mono text-purple-900 font-bold">
              {{ issue.spk_number }} • {{ issue.vendor_name || 'Client' }}
            </div>
            <h3 class="font-black text-sm text-slate-900 mt-0.5">
              {{ issue.work_order_title }}
            </h3>
            <p class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
              <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
              <span>{{ issue.location_name }} (Area: {{ issue.area_name || '-' }})</span>
            </p>
          </div>

          <!-- Issue Description Box -->
          <div class="p-3 bg-white rounded-2xl border border-slate-200/80 text-xs space-y-1 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kronologi Kendala:</span>
            <p class="text-slate-800 leading-relaxed font-medium">
              "{{ issue.notes || 'Tidak ada catatan kronologi tertulis.' }}"
            </p>
            <div class="text-[10px] text-slate-500 pt-1 flex items-center justify-between">
              <span>Dilaporkan oleh: <strong>{{ issue.reporter_name || 'Tim Lapangan' }}</strong></span>
              <span v-if="issue.reporter_phone" class="font-mono">📞 {{ issue.reporter_phone }}</span>
            </div>
          </div>

          <!-- Resolution Details Box (if resolved) -->
          <div v-if="issue.status === 'RESOLVED'" class="p-3 bg-emerald-50/80 border border-emerald-200 rounded-2xl text-xs space-y-1">
            <span class="text-[10px] font-bold text-emerald-900 uppercase tracking-wider flex items-center gap-1">
              <CheckCircle2 class="w-3 h-3 text-emerald-700" />
              <span>Tindakan Solusi Pengawas:</span>
            </span>
            <p class="text-emerald-950 font-medium leading-relaxed">
              {{ issue.resolution_notes || 'Kendala telah diselesaikan dan pekerjaan dilanjutkan.' }}
            </p>
            <div class="text-[10px] text-emerald-800/80 pt-1 flex items-center justify-between font-mono">
              <span>Ditangani oleh: {{ issue.resolver_name || 'Pengawas SGX' }}</span>
              <span>{{ issue.resolved_at ? new Date(issue.resolved_at).toLocaleDateString('id-ID') : '-' }}</span>
            </div>
          </div>
        </div>

        <!-- Action Footer -->
        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2 text-xs">
          <button
            v-if="issue.status === 'OPEN'"
            @click="openResolveModal(issue)"
            class="px-4 py-2 bg-gradient-to-r from-amber-600 to-rose-600 hover:from-amber-500 hover:to-rose-500 text-white font-bold rounded-xl flex items-center gap-1.5 shadow-xs active:scale-95 transition-all cursor-pointer"
          >
            <Sparkles class="w-3.5 h-3.5" />
            <span>Tindak Lanjuti & Selesaikan</span>
          </button>
          <span v-else class="text-[11px] font-bold text-emerald-700 flex items-center gap-1">
            <CheckCircle2 class="w-3.5 h-3.5" /> Selesai & Teregister
          </span>
        </div>
      </div>
    </div>

    <!-- Resolution Action Modal -->
    <div v-if="showModal && activeIssue" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="glass-modal rounded-3xl max-w-lg w-full shadow-2xl p-6 space-y-4 text-xs border border-white/80 bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
          <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
            <AlertTriangle class="w-4 h-4 text-amber-600" />
            <span>Form Solusi & Penyelesaian Kendala</span>
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl space-y-1">
          <div class="text-[10px] font-mono text-purple-900 font-bold">
            {{ activeIssue.spk_number }} • {{ activeIssue.work_order_title }}
          </div>
          <p class="text-[11px] text-slate-700 font-medium">
            <strong>Kendala:</strong> "{{ activeIssue.notes }}"
          </p>
        </div>

        <form @submit.prevent="handleResolveSubmit" class="space-y-4">
          <div>
            <label class="block font-bold text-slate-900 mb-1">
              Catatan Tindakan Solusi Lapangan *
            </label>
            <textarea
              required
              rows="4"
              v-model="resolutionNotes"
              placeholder="Contoh: Telah dikoordinasikan dengan pengelola gedung dan surat izin kerja malam telah disetujui. Pekerjaan dijadwalkan ulang pukul 19:00 WIB..."
              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl leading-relaxed text-xs focus:bg-white transition-colors"
            />
          </div>

          <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs active:scale-95 transition-all cursor-pointer disabled:opacity-50"
            >
              {{ saving ? 'Menyimpan...' : 'Selesaikan Tiket Kendala ✓' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../../services/api';
import {
  AlertTriangle,
  AlertCircle,
  CheckCircle2,
  Search,
  MapPin,
  Sparkles,
  X
} from 'lucide-vue-next';

const issues = ref([]);
const loading = ref(true);
const saving = ref(false);

const selectedStatus = ref('ALL');
const selectedType = ref('ALL');
const searchQuery = ref('');

const showModal = ref(false);
const activeIssue = ref(null);
const resolutionNotes = ref('');

const statusOptions = [
  { label: 'Semua Tiket', value: 'ALL' },
  { label: '🔴 Butuh Tindakan (OPEN)', value: 'OPEN' },
  { label: '🟢 Teratasi (RESOLVED)', value: 'RESOLVED' }
];

async function loadIssues() {
  loading.value = true;
  try {
    const res = await api.getFieldIssues();
    issues.value = res.data || [];
  } catch (err) {
    console.error('Failed to load field issues:', err);
  } finally {
    loading.value = false;
  }
}

const openIssuesCount = computed(() => issues.value.filter(i => i.status === 'OPEN').length);
const resolvedIssuesCount = computed(() => issues.value.filter(i => i.status === 'RESOLVED').length);

const filteredIssues = computed(() => {
  return issues.value.filter(i => {
    if (selectedStatus.value !== 'ALL' && i.status !== selectedStatus.value) {
      return false;
    }
    if (selectedType.value !== 'ALL' && i.issue_type !== selectedType.value) {
      return false;
    }
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase();
      const matchSpk = i.spk_number?.toLowerCase().includes(q);
      const matchTitle = i.work_order_title?.toLowerCase().includes(q);
      const matchNotes = i.notes?.toLowerCase().includes(q);
      const matchRes = i.resolution_notes?.toLowerCase().includes(q);
      if (!matchSpk && !matchTitle && !matchNotes && !matchRes) return false;
    }
    return true;
  });
});

function formatCategory(type) {
  const map = {
    CUACA_BURUK: 'Cuaca Buruk',
    AKSES_LOKASI: 'Izin & Akses Lokasi',
    KELISTRIKAN: 'Kelistrikan & Panel',
    MATERIAL: 'Material & Logistik',
    STRUKTUR_BANGUNAN: 'Struktur Lapangan'
  };
  return map[type] || type || 'Kendala Teknis';
}

function openResolveModal(issue) {
  activeIssue.value = issue;
  resolutionNotes.value = '';
  showModal.value = true;
}

async function handleResolveSubmit() {
  if (!activeIssue.value) return;
  saving.value = true;
  try {
    await api.resolveFieldIssue(activeIssue.value.id, {
      resolution_notes: resolutionNotes.value,
      status: 'RESOLVED'
    });
    alert('Kendala lapangan berhasil diselesaikan!');
    showModal.value = false;
    loadIssues();
  } catch (err) {
    alert(`Gagal menyelesaikan kendala: ${err.message}`);
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  loadIssues();
});
</script>
