<template>
  <div class="space-y-4">
    <!-- Header (CAD Blueprint Style) -->
    <div class="bg-[#13171F] border border-[#232A36] p-4 rounded-xs flex flex-col md:flex-row md:items-center justify-between gap-3 relative overflow-hidden">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xs bg-[#0B0C0E] border border-red-500/40 flex items-center justify-center text-red-400 shrink-0">
          <AlertTriangle class="w-5 h-5" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-base sm:text-lg font-heading font-black text-white uppercase tracking-wider">
              Kendala & Hambatan Lapangan
            </h2>
            <span class="bg-red-950/80 text-red-400 border border-red-500/40 text-[9px] font-mono px-1.5 py-0.2 rounded-none font-bold uppercase">
              FIELD TICKETING
            </span>
          </div>
          <p class="text-xs text-slate-400 font-mono mt-0.5">
            Monitoring tiket kendala teknis, mitigasi risiko keterlambatan SLA, dan pencatatan tindakan penyelesaian pengawas.
          </p>
        </div>
      </div>

      <!-- Quick Metrics (Industrial Badges) -->
      <div class="flex items-center gap-2 self-start md:self-auto font-mono text-xs">
        <div class="px-3 py-1.5 bg-[#0B0C0E] border border-red-500/40 rounded-xs font-bold text-red-400 flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
          <span>{{ openIssuesCount }} BUTUH TINDAKAN</span>
        </div>
        <div class="px-3 py-1.5 bg-[#0B0C0E] border border-emerald-500/40 rounded-xs font-bold text-emerald-400 flex items-center gap-2">
          <CheckCircle2 class="w-3.5 h-3.5 text-emerald-400" />
          <span>{{ resolvedIssuesCount }} TERATASI</span>
        </div>
      </div>
    </div>

    <!-- Filters & Search Bar (Terminal Style) -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-3 flex flex-col md:flex-row items-center justify-between gap-3 text-xs font-mono">
      <!-- Status Filter Tabs -->
      <div class="flex items-center gap-1 overflow-x-auto w-full md:w-auto pb-1 md:pb-0 scrollbar-none">
        <button
          v-for="st in statusOptions"
          :key="st.value"
          @click="selectedStatus = st.value"
          :class="[
            'px-3 py-1.5 rounded-none font-bold transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 border uppercase',
            selectedStatus === st.value
              ? 'bg-hazard text-carbon border-hazard shadow-xs'
              : 'bg-[#0B0C0E] text-slate-400 hover:text-white border-[#232A36]'
          ]"
        >
          <span>{{ st.label }}</span>
          <span
            v-if="st.value === 'OPEN' && openIssuesCount > 0"
            :class="[
              'px-1.5 py-0.2 rounded-none text-[9px] font-mono',
              selectedStatus === st.value ? 'bg-carbon text-hazard font-black' : 'bg-red-950 text-red-400 border border-red-500/40'
            ]"
          >
            {{ openIssuesCount }}
          </span>
        </button>
      </div>

      <!-- Category Filter & Search -->
      <div class="flex items-center gap-2 w-full md:w-auto">
        <select
          v-model="selectedType"
          class="px-2.5 py-1.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs font-mono text-slate-300 text-xs cursor-pointer focus:outline-none focus:border-hazard"
        >
          <option value="ALL">[ SEMUA KATEGORI KENDALA ]</option>
          <option value="CUACA_BURUK">Cuaca Buruk / Hujan</option>
          <option value="AKSES_LOKASI">Akses & Izin Gedung</option>
          <option value="KELISTRIKAN">Kelistrikan & Panel</option>
          <option value="MATERIAL">Material & Komponen</option>
          <option value="STRUKTUR_BANGUNAN">Struktur Tidak Sesuai</option>
        </select>

        <div class="relative flex-1 sm:w-64">
          <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-500" />
          <input
            type="text"
            v-model="searchQuery"
            placeholder=">_ CARI SPK, KENDALA..."
            class="w-full pl-8 pr-3 py-1.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-xs font-mono text-white placeholder-slate-600 focus:outline-none focus:border-hazard"
          />
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-20 bg-[#13171F] border border-[#232A36] rounded-xs">
      <div class="w-8 h-8 border-2 border-red-500 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
      <p class="text-xs text-slate-400 font-mono tracking-wider">MEMUAT_TIKET_KENDALA_LAPANGAN...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredIssues.length === 0" class="text-center py-20 bg-[#13171F] border border-dashed border-[#232A36] rounded-xs">
      <div class="w-12 h-12 rounded-xs bg-[#0B0C0E] border border-emerald-500/30 flex items-center justify-center text-emerald-400 mx-auto mb-3">
        <CheckCircle2 class="w-6 h-6" />
      </div>
      <h3 class="font-heading font-black text-white text-sm uppercase tracking-wider">TIDAK ADA KENDALA LAPANGAN AKTIF</h3>
      <p class="text-xs font-mono text-slate-500 mt-1">Seluruh pekerjaan proyek berjalan lancar sesuai rencana dan standar operasional.</p>
    </div>

    <!-- Issues Grid (Industrial CAD Incident Modules) -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
      <div
        v-for="issue in filteredIssues"
        :key="issue.id"
        :class="[
          'bg-[#0E1117] border rounded-none p-4 space-y-3 flex flex-col justify-between transition-all duration-200',
          issue.status === 'OPEN'
            ? 'border-l-4 border-l-red-500 border-y border-r border-[#232A36] bg-[#120F12]'
            : 'border-l-4 border-l-emerald-500 border-y border-r border-[#232A36]'
        ]"
      >
        <div class="space-y-3">
          <!-- Top Row: Badge Status & Category & Timestamp -->
          <div class="flex items-center justify-between gap-2 font-mono">
            <div class="flex items-center gap-1.5">
              <span
                :class="[
                  'px-2 py-0.5 rounded-none text-[9px] font-black uppercase tracking-wider flex items-center gap-1 border',
                  issue.status === 'OPEN'
                    ? 'bg-red-950/90 text-red-400 border-red-500/60 animate-pulse'
                    : 'bg-emerald-950/90 text-emerald-400 border-emerald-500/60'
                ]"
              >
                <AlertCircle v-if="issue.status === 'OPEN'" class="w-3 h-3 text-red-400" />
                <CheckCircle2 v-else class="w-3 h-3 text-emerald-400" />
                <span>{{ issue.status === 'OPEN' ? '[ BUTUH TINDAKAN ]' : '[ TERATASI ]' }}</span>
              </span>

              <span class="px-2 py-0.5 rounded-none text-[9px] font-bold bg-[#0B0C0E] text-hazard border border-[#232A36] uppercase">
                {{ formatCategory(issue.issue_type) }}
              </span>
            </div>

            <span class="text-[10px] text-slate-500">
              {{ new Date(issue.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
            </span>
          </div>

          <!-- Project & Location Details -->
          <div>
            <div class="text-[10px] font-mono text-hazard font-bold">
              [ {{ issue.spk_number }} ] • {{ issue.vendor_name || 'SGX CLIENT' }}
            </div>
            <h3 class="font-heading font-black text-sm text-white mt-0.5 uppercase tracking-wide">
              {{ issue.work_order_title }}
            </h3>
            <p class="text-[11px] font-mono text-slate-400 flex items-center gap-1 mt-0.5">
              <MapPin class="w-3 h-3 text-slate-500 shrink-0" />
              <span>{{ issue.location_name }} (Area: {{ issue.area_name || '-' }})</span>
            </p>
          </div>

          <!-- Issue Description Box (Dark Industrial Strip) -->
          <div class="p-3 bg-[#0B0C0E] rounded-none border border-[#232A36] text-xs font-mono space-y-1">
            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">LOG KRONOLOGI KENDALA:</span>
            <p class="text-slate-200 leading-relaxed font-sans text-xs">
              "{{ issue.notes || 'Tidak ada catatan kronologi tertulis.' }}"
            </p>
            <div class="text-[10px] text-slate-500 pt-1 border-t border-[#1C2028] flex items-center justify-between">
              <span>Pelapor: <strong class="text-slate-300">{{ issue.reporter_name || 'Tim Lapangan' }}</strong></span>
              <span v-if="issue.reporter_phone" class="font-mono text-slate-400">TEL: {{ issue.reporter_phone }}</span>
            </div>
          </div>

          <!-- Resolution Details Box (if resolved) -->
          <div v-if="issue.status === 'RESOLVED'" class="p-3 bg-[#0B0C0E] border-l-2 border-l-emerald-500 border-y border-r border-[#232A36] rounded-none text-xs font-mono space-y-1">
            <span class="text-[9px] font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1">
              <CheckCircle2 class="w-3 h-3 text-emerald-400" />
              <span>TINDAKAN SOLUSI PENGAWAS:</span>
            </span>
            <p class="text-slate-300 font-sans text-xs leading-relaxed">
              {{ issue.resolution_notes || 'Kendala telah diselesaikan dan pekerjaan dilanjutkan.' }}
            </p>
            <div class="text-[10px] text-slate-500 pt-1 border-t border-[#1C2028] flex items-center justify-between">
              <span>Ditangani: <strong class="text-slate-300">{{ issue.resolver_name || 'Pengawas SGX' }}</strong></span>
              <span>{{ issue.resolved_at ? new Date(issue.resolved_at).toLocaleDateString('id-ID') : '-' }}</span>
            </div>
          </div>
        </div>

        <!-- Action Footer -->
        <div class="pt-3 border-t border-[#1C2028] flex items-center justify-end gap-2 text-xs font-mono">
          <button
            v-if="issue.status === 'OPEN'"
            @click="openResolveModal(issue)"
            class="cad-btn-primary px-3.5 py-1.5 text-xs font-bold rounded-none flex items-center gap-1.5 uppercase cursor-pointer active:scale-95 transition-all"
          >
            <Sparkles class="w-3.5 h-3.5" />
            <span>Tindak Lanjuti & Selesaikan</span>
          </button>
          <span v-else class="text-[10px] font-bold text-emerald-400 flex items-center gap-1 uppercase">
            <CheckCircle2 class="w-3.5 h-3.5" /> TIKET SELESAI & TEREGISTER
          </span>
        </div>
      </div>
    </div>

    <!-- Resolution Action Modal (CAD Style) -->
    <Teleport to="body">
      <div v-if="showModal && activeIssue" class="fixed inset-0 z-50 bg-[#0B0C0E]/80 backdrop-blur-xs flex items-center justify-center p-4 font-mono">
        <div class="bg-[#13171F] border border-hazard/40 rounded-xs max-w-lg w-full shadow-2xl p-5 space-y-4 text-xs">
          <div class="flex items-center justify-between border-b border-[#232A36] pb-3">
            <h3 class="font-heading font-black text-sm text-white flex items-center gap-2 uppercase tracking-wider">
              <AlertTriangle class="w-4 h-4 text-hazard" />
              <span>Form Solusi & Penyelesaian Kendala</span>
            </h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-white p-1 cursor-pointer">
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="p-3 bg-[#0B0C0E] border-l-4 border-l-hazard border-y border-r border-[#232A36] text-slate-300 space-y-1">
            <div class="text-[10px] text-hazard font-bold">
              [ {{ activeIssue.spk_number }} ] • {{ activeIssue.work_order_title }}
            </div>
            <p class="text-[11px] text-slate-300 font-sans">
              <strong class="text-white">Kendala:</strong> "{{ activeIssue.notes }}"
            </p>
          </div>

          <form @submit.prevent="handleResolveSubmit" class="space-y-4">
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">
                Catatan Tindakan Solusi Lapangan *
              </label>
              <textarea
                required
                rows="4"
                v-model="resolutionNotes"
                placeholder="Contoh: Telah dikoordinasikan dengan pengelola gedung dan surat izin kerja malam telah disetujui. Pekerjaan dijadwalkan ulang pukul 19:00 WIB..."
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard focus:outline-none leading-relaxed text-xs font-mono"
              />
            </div>

            <div class="pt-2 border-t border-[#232A36] flex items-center justify-end gap-2">
              <button
                type="button"
                @click="showModal = false"
                class="px-4 py-2 bg-[#0B0C0E] hover:bg-[#181D26] text-slate-300 border border-[#232A36] font-bold rounded-xs cursor-pointer uppercase"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="cad-btn-primary px-5 py-2 font-bold rounded-xs cursor-pointer active:scale-95 uppercase disabled:opacity-50"
              >
                {{ saving ? 'MENYIMPAN...' : 'SELESAIKAN TIKET KENDALA ✓' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
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
  { label: 'SEMUA TIKET', value: 'ALL' },
  { label: 'BUTUH TINDAKAN (OPEN)', value: 'OPEN' },
  { label: 'TERATASI (RESOLVED)', value: 'RESOLVED' }
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
