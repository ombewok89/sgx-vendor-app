<template>
  <div v-if="workOrderId" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-modal rounded-3xl max-w-4xl w-full shadow-2xl overflow-hidden my-6 flex flex-col max-h-[90vh] border border-white/80">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between bg-slate-100/60 shrink-0">
        <div>
          <div class="flex items-center gap-2">
            <span class="font-mono font-bold text-xs bg-slate-200 px-2 py-0.5 rounded text-slate-800">
              {{ workOrder?.spk_number || 'Memuat...' }}
            </span>
            <StatusBadge :status="workOrder?.status" />
          </div>
          <h3 class="font-black text-slate-900 text-base mt-1">{{ workOrder?.title }}</h3>
        </div>
        <button
          @click="$emit('close')"
          class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 rounded-xl transition-all"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 overflow-y-auto space-y-6 text-xs text-slate-700 custom-scrollbar">
        <div v-if="loading" class="py-16 text-center text-slate-400 font-medium">
          Memuat detail lengkap pekerjaan SPK...
        </div>

        <template v-else-if="workOrder">
          <!-- Stepper Progress -->
          <div class="glass-card rounded-2xl p-4 border border-white/60">
            <StepperProgress :status="workOrder.status" :progressPercent="workOrder.progress_percent" />
          </div>

          <!-- Action Banners -->
          <div
            v-if="['SUBMITTED', 'IN_PROGRESS', 'UNDER_REVIEW'].includes(workOrder.status)"
            class="p-4 bg-purple-500/10 border border-purple-300 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 backdrop-blur-md"
          >
            <div class="flex items-center gap-2.5 text-purple-950">
              <CheckCircle2 class="w-5 h-5 text-purple-600 shrink-0" />
              <div>
                <div class="font-bold text-sm">Pekerjaan Siap Direview & Diverifikasi</div>
                <div class="text-[11px] text-purple-700">Foto dokumentasi dan data verifikasi lapangan telah tersedia.</div>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="handleQuickApprove"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer flex items-center gap-1.5 active:scale-95 transition-all"
              >
                <CheckCircle2 class="w-4 h-4" />
                <span>Setujui (Approve)</span>
              </button>
              <button
                @click="handleGoToReview"
                class="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer active:scale-95 transition-all"
              >
                Buka Review Console →
              </button>
            </div>
          </div>

          <div
            v-if="workOrder.status === 'APPROVED'"
            class="p-4 bg-emerald-500/10 border border-emerald-300 rounded-2xl flex items-center justify-between backdrop-blur-md"
          >
            <div class="flex items-center gap-2.5 text-emerald-950">
              <CheckCircle2 class="w-5 h-5 text-emerald-600" />
              <div>
                <div class="font-bold text-sm">Pekerjaan Telah Disetujui (Approved)</div>
                <div class="text-[11px] text-emerald-700">Siap untuk diterbitkan Berita Acara (BA) Opname resmi.</div>
              </div>
            </div>
            <button
              @click="handleGenerateBa"
              class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs flex items-center gap-1.5 active:scale-95 transition-all"
            >
              <FileCheck2 class="w-4 h-4" />
              <span>Terbitkan BA Opname</span>
            </button>
          </div>

          <div
            v-if="workOrder.ba_document"
            class="p-4 bg-teal-500/10 border border-teal-300 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 backdrop-blur-md"
          >
            <div class="flex items-center gap-2.5 text-teal-950">
              <FileCheck2 class="w-5 h-5 text-teal-600 shrink-0" />
              <div>
                <div class="font-bold text-sm">BA Opname Resmi Terbit ({{ workOrder.ba_document.ba_number }})</div>
                <div class="text-[11px] text-teal-700">Diterbitkan pada {{ new Date(workOrder.ba_document.ba_date).toLocaleDateString('id-ID') }}</div>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="$emit('open-ba', workOrder.ba_document)"
                class="px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer flex items-center gap-1.5 active:scale-95 transition-all"
              >
                <FileCheck2 class="w-4 h-4" />
                <span>Lihat BA</span>
              </button>
              <button
                v-if="workOrder.status !== 'COMPLETED'"
                @click="handleCompleteWorkOrder"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer flex items-center gap-1.5 active:scale-95 transition-all"
              >
                <CheckCircle2 class="w-4 h-4" />
                <span>Tandai Selesai (100%)</span>
              </button>
            </div>
          </div>

          <!-- Info & Assignment Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Information Card -->
            <div class="glass-card rounded-2xl p-4 space-y-2.5 border border-white/70">
              <h4 class="font-bold text-slate-900 border-b border-slate-200/80 pb-2">Informasi Pekerjaan</h4>
              <div class="space-y-2 text-slate-600">
                <div class="flex justify-between">
                  <span>Perusahaan Client:</span>
                  <span class="font-bold text-slate-900">{{ workOrder.vendor_name }} ({{ workOrder.vendor_code }})</span>
                </div>
                <div class="flex justify-between">
                  <span>Area Operasional:</span>
                  <span class="font-bold text-slate-900">{{ workOrder.area_name }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Nilai Kontrak (Rp):</span>
                  <span class="font-bold font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                    Rp {{ Number(workOrder.contract_value || 0).toLocaleString('id-ID') }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span>Titik Lokasi:</span>
                  <span class="font-bold text-slate-900 text-right max-w-[200px]">{{ workOrder.location_name }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Periode:</span>
                  <span class="font-mono text-slate-900 font-bold">{{ workOrder.start_date }} s/d {{ workOrder.deadline }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Mode Evidence:</span>
                  <span class="font-bold text-brand-700">{{ workOrder.doc_mode }}</span>
                </div>
              </div>
            </div>

            <!-- Team Assignment Card -->
            <div class="glass-card rounded-2xl p-4 space-y-3 border border-white/70">
              <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                <h4 class="font-bold text-slate-900">Penugasan Tim Lapangan</h4>
                <span class="text-[10px] text-slate-400 font-bold uppercase">PIC & ANGGOTA</span>
              </div>

              <div>
                <label class="block font-bold text-slate-700 mb-1">PIC Utama (Wajib):</label>
                <select
                  v-model="selectedPic"
                  class="w-full px-3 py-2 bg-white/90 border border-slate-200/80 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none text-xs"
                >
                  <option value="">-- Pilih PIC Lapangan --</option>
                  <option v-for="u in fieldUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.phone }})</option>
                </select>
              </div>

              <div>
                <label class="block font-bold text-slate-700 mb-1">Anggota Tim Tambahan:</label>
                <div class="space-y-1.5 max-h-24 overflow-y-auto bg-white/90 p-2.5 border border-slate-200/80 rounded-xl custom-scrollbar">
                  <label
                    v-for="u in fieldUsers.filter(u => String(u.id) !== String(selectedPic))"
                    :key="u.id"
                    class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer"
                  >
                    <input
                      type="checkbox"
                      :value="u.id"
                      v-model="selectedMembers"
                      class="rounded text-brand-600 focus:ring-brand-500"
                    />
                    <span>{{ u.name }}</span>
                  </label>
                </div>
              </div>

              <button
                type="button"
                @click="handleSaveAssignment"
                :disabled="assigning"
                class="w-full py-2 bg-gradient-to-r from-brand-900 to-brand-700 hover:from-brand-800 hover:to-brand-600 text-white font-bold rounded-xl transition-all shadow-xs active:scale-95 disabled:opacity-50"
              >
                {{ assigning ? 'Menyimpan...' : 'Simpan Penugasan Tim' }}
              </button>
            </div>
          </div>

          <!-- Check-In History -->
          <div class="glass-card rounded-2xl p-4 space-y-2 border border-white/70">
            <h4 class="font-bold text-slate-900 flex items-center gap-2">
              <Navigation class="w-4 h-4 text-brand-600" />
              <span>Riwayat Check-In GPS Lapangan</span>
            </h4>
            <div v-if="workOrder.check_ins && workOrder.check_ins.length > 0" class="space-y-2">
              <div
                v-for="ci in workOrder.check_ins"
                :key="ci.id"
                class="p-3 bg-white/90 border border-slate-200/80 rounded-xl flex items-center justify-between shadow-xs"
              >
                <div>
                  <div class="font-bold text-slate-900">{{ ci.user_name }}</div>
                  <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                    GPS: {{ ci.latitude?.toFixed(6) }}, {{ ci.longitude?.toFixed(6) }} (±{{ ci.accuracy }}m)
                  </div>
                  <div v-if="ci.address_note" class="text-[10px] text-slate-400 italic mt-0.5">"{{ ci.address_note }}"</div>
                </div>
                <div class="text-right">
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 border border-emerald-300">
                    TERVERIFIKASI ✓
                  </span>
                  <div class="text-[10px] text-slate-400 font-mono mt-1">
                    {{ new Date(ci.server_timestamp).toLocaleString('id-ID') }}
                  </div>
                </div>
              </div>
            </div>
            <p v-else class="text-slate-400 text-xs italic">Belum ada catatan check-in GPS untuk pekerjaan ini.</p>
          </div>

          <!-- Technical Issues / Kendala Lapangan Log -->
          <div class="glass-card rounded-2xl p-4 space-y-3 border border-white/70">
            <div class="flex items-center justify-between">
              <h4 class="font-bold text-slate-900 flex items-center gap-2">
                <AlertTriangle class="w-4 h-4 text-amber-600" />
                <span>Riwayat Kendala Teknis & Mitigasi Lapangan</span>
              </h4>
              <span
                v-if="workOrder.issues && workOrder.issues.length > 0"
                class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                :class="workOrder.issues.some(i => i.status === 'OPEN') ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800'"
              >
                {{ workOrder.issues.filter(i => i.status === 'OPEN').length > 0 ? `${workOrder.issues.filter(i => i.status === 'OPEN').length} Perlu Tindakan` : 'Semua Kendala Teratasi ✓' }}
              </span>
            </div>

            <div v-if="workOrder.issues && workOrder.issues.length > 0" class="space-y-2.5">
              <div
                v-for="iss in workOrder.issues"
                :key="iss.id"
                class="p-3.5 rounded-xl border space-y-2 text-xs shadow-xs"
                :class="iss.status === 'OPEN' ? 'bg-amber-50/60 border-amber-200' : 'bg-slate-50/60 border-slate-200'"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span
                      :class="[
                        'px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider',
                        iss.status === 'OPEN' ? 'bg-rose-600 text-white animate-pulse' : 'bg-emerald-600 text-white'
                      ]"
                    >
                      {{ iss.status === 'OPEN' ? 'OPEN / BUTUH TINDAKAN' : 'RESOLVED ✓' }}
                    </span>
                    <span class="font-bold text-slate-800">{{ iss.issue_type || 'Kendala Teknis' }}</span>
                  </div>
                  <span class="text-[10px] font-mono text-slate-400">
                    {{ new Date(iss.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                  </span>
                </div>

                <p class="text-slate-800 leading-relaxed font-medium">
                  "{{ iss.notes }}"
                </p>

                <!-- Solution note if resolved -->
                <div v-if="iss.resolution_notes" class="p-2.5 bg-emerald-50 rounded-lg text-[11px] text-emerald-950 border border-emerald-200/80">
                  <div class="font-bold text-emerald-800 flex items-center justify-between mb-0.5">
                    <span>Tindakan Solusi Pengawas:</span>
                    <span class="font-mono text-[9px] text-emerald-700">Oleh: {{ iss.resolver_name || 'Pengawas SGX' }}</span>
                  </div>
                  <p>{{ iss.resolution_notes }}</p>
                </div>
              </div>
            </div>
            <p v-else class="text-slate-400 text-xs italic">Tidak ada catatan kendala teknis untuk pekerjaan cabang ini (Pekerjaan berjalan lancar).</p>
          </div>

          <!-- Photo Evidence Section -->
          <div class="space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <h4 class="font-bold text-slate-900 flex items-center gap-2">
                <Camera class="w-4 h-4 text-brand-600" />
                <span>Dokumentasi Foto Evidence Terunggah ({{ workOrder.evidence_photos?.length || 0 }} Foto)</span>
              </h4>
              <button
                v-if="workOrder.evidence_photos && workOrder.evidence_photos.length > 0"
                type="button"
                @click="downloadAllPhotos"
                class="px-3 py-1.5 bg-gradient-to-r from-purple-800 to-indigo-600 hover:from-purple-700 hover:to-indigo-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs active:scale-95 transition-all cursor-pointer self-start sm:self-auto"
                title="Unduh semua foto bukti SPK ini"
              >
                <Download class="w-3.5 h-3.5" />
                <span>Unduh Semua Foto SPK</span>
              </button>
            </div>

            <template v-for="stage in ['BEFORE', 'PROCESS', 'AFTER', 'ISSUE']" :key="stage">
              <div
                v-if="!(workOrder.evidence_photos?.filter(p => p.stage === stage).length === 0 && workOrder.doc_mode === 'AFTER_ONLY' && stage !== 'AFTER')"
                class="glass-card rounded-2xl p-3.5 border border-white/70"
              >
                <div class="font-bold text-xs text-slate-800 mb-2.5 flex items-center justify-between">
                  <span>TAHAP: {{ stage }}</span>
                  <span class="text-slate-400 font-normal">({{ workOrder.evidence_photos?.filter(p => p.stage === stage).length || 0 }} foto)</span>
                </div>
                <div
                  v-if="workOrder.evidence_photos?.filter(p => p.stage === stage).length > 0"
                  class="grid grid-cols-2 sm:grid-cols-4 gap-2.5"
                >
                  <div
                    v-for="(p, pIdx) in workOrder.evidence_photos.filter(p => p.stage === stage)"
                    :key="p.id"
                    @click="openLightbox(p)"
                    class="bg-slate-900 rounded-xl overflow-hidden p-1 shadow-xs group relative cursor-pointer hover:shadow-md transition-all"
                  >
                    <img
                      :src="p.file_path"
                      :alt="p.file_name"
                      class="w-full h-24 object-cover rounded-lg group-hover:scale-105 transition-transform duration-300"
                      @error="$event.target.src = 'https://images.unsplash.com/photo-1541888946425-d0fbb18086f6?w=400&auto=format&fit=crop&q=60'"
                    />

                    <!-- Corner Download Button (Bottom-Right) -->
                    <button
                      type="button"
                      @click.stop="downloadSinglePhoto(p)"
                      class="absolute bottom-2 right-2 w-6 h-6 rounded-full bg-slate-900/90 hover:bg-purple-700 text-white shadow-md flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95 cursor-pointer border border-white/40 backdrop-blur-xs z-10"
                      title="Unduh Foto Resolusi Asli"
                    >
                      <Download class="w-3 h-3" />
                    </button>

                    <div class="p-1.5 text-[10px] text-white">
                      <div class="font-bold truncate">Foto #{{ p.sequence || pIdx + 1 }}</div>
                      <div class="text-[8px] font-mono text-emerald-400 truncate">SHA-256: {{ p.file_hash?.substring(0, 10) }}...</div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-slate-400 text-xs italic">Belum ada foto pada tahap {{ stage }}.</div>
              </div>
            </template>
          </div>
        </template>
      </div>
    </div>

    <!-- Reusable Photo Lightbox Full-Screen Viewer -->
    <PhotoLightboxModal
      :isOpen="isLightboxOpen"
      :photos="workOrder?.evidence_photos || []"
      :initialIndex="selectedLightboxIndex"
      @close="isLightboxOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { api } from '../../services/api';
import StatusBadge from '../../components/StatusBadge.vue';
import StepperProgress from '../../components/StepperProgress.vue';
import PhotoLightboxModal from '../../components/PhotoLightboxModal.vue';
import {
  X,
  MapPin,
  CheckCircle2,
  AlertTriangle,
  FileCheck2,
  Navigation,
  Camera,
  RotateCcw,
  Download
} from 'lucide-vue-next';

const props = defineProps({
  workOrderId: {
    type: [String, Number],
    default: null
  }
});

const emit = defineEmits(['close', 'open-review', 'open-ba', 'refresh-list']);

const workOrder = ref(null);
const fieldUsers = ref([]);
const loading = ref(true);
const assigning = ref(false);
const selectedPic = ref('');
const selectedMembers = ref([]);

async function loadDetail() {
  if (!props.workOrderId) return;
  loading.value = true;
  try {
    const [woRes, usersRes] = await Promise.all([
      api.getWorkOrderById(props.workOrderId),
      api.getUsers({ role: 'FIELD_TEAM' })
    ]);
    workOrder.value = woRes.data;
    fieldUsers.value = usersRes.data || [];
    selectedPic.value = woRes.data?.pic_user_id || '';
    const memberIds = (woRes.data?.assignments || [])
      .filter(a => a.role_in_team === 'MEMBER')
      .map(a => a.user_id);
    selectedMembers.value = memberIds;
  } catch (err) {
    console.error('Failed to load work order detail:', err);
  } finally {
    loading.value = false;
  }
}

watch(() => props.workOrderId, () => {
  loadDetail();
}, { immediate: true });

async function handleSaveAssignment() {
  if (!selectedPic.value) {
    alert('PIC tim lapangan wajib dipilih!');
    return;
  }
  assigning.value = true;
  try {
    await api.assignTeam(props.workOrderId, {
      picUserId: parseInt(selectedPic.value, 10),
      memberUserIds: selectedMembers.value.map(id => parseInt(id, 10))
    });
    await loadDetail();
    emit('refresh-list');
    alert('Penugasan tim berhasil disimpan!');
  } catch (err) {
    alert(`Gagal assign tim: ${err.message}`);
  } finally {
    assigning.value = false;
  }
}

async function handleQuickApprove() {
  try {
    await api.approveWorkOrder({
      work_order_id: workOrder.value.id,
      review_notes: 'Pekerjaan telah disetujui melalui Detail SPK.'
    });
    alert(`Pekerjaan ${workOrder.value.spk_number} berhasil Disetujui (APPROVED)!`);
    await loadDetail();
    emit('refresh-list');
  } catch (e) {
    alert(`Gagal approve: ${e.message}`);
  }
}

function handleGoToReview() {
  emit('close');
  emit('open-review', workOrder.value.id);
}

async function handleGenerateBa() {
  try {
    const res = await api.generateBa({ work_order_id: workOrder.value.id });
    alert('BA Opname berhasil diterbitkan!');
    await loadDetail();
    emit('open-ba', res.data);
  } catch (err) {
    alert(err.message);
  }
}

async function handleCompleteWorkOrder() {
  try {
    await api.completeWorkOrder(workOrder.value.id);
    alert(`Pekerjaan ${workOrder.value.spk_number} telah ditandai SELESAI (COMPLETED 100%)!`);
    await loadDetail();
    emit('refresh-list');
  } catch (e) {
    alert(`Gagal menyelesaikan pekerjaan: ${e.message}`);
  }
}

/**
 * Lightbox & Photo Download Handlers
 */
const isLightboxOpen = ref(false);
const selectedLightboxIndex = ref(0);

function openLightbox(photo) {
  const allPhotos = workOrder.value?.evidence_photos || [];
  const idx = allPhotos.findIndex(p => p.id === photo.id);
  selectedLightboxIndex.value = idx >= 0 ? idx : 0;
  isLightboxOpen.value = true;
}

function downloadSinglePhoto(photo) {
  if (!photo?.file_path) return;
  const link = document.createElement('a');
  link.href = photo.file_path;
  const ext = photo.file_name?.split('.').pop() || 'jpg';
  const spk = workOrder.value?.spk_number ? `${workOrder.value.spk_number}_` : '';
  link.download = `${spk}${photo.stage || 'EVIDENCE'}_${photo.sequence || 1}.${ext}`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function downloadAllPhotos() {
  const allPhotos = workOrder.value?.evidence_photos || [];
  if (allPhotos.length === 0) {
    alert('Tidak ada foto untuk diunduh.');
    return;
  }

  allPhotos.forEach((p, idx) => {
    setTimeout(() => {
      downloadSinglePhoto(p);
    }, idx * 250);
  });
}
</script>
