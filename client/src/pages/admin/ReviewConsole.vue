<template>
  <div class="space-y-5">
    <!-- Toast Notification -->
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="successToast"
        class="p-4 bg-emerald-600/95 backdrop-blur-md text-white font-bold text-xs rounded-2xl shadow-xl flex items-center justify-between border border-emerald-400/50"
      >
        <div class="flex items-center gap-2.5">
          <CheckCircle2 class="w-5 h-5 text-emerald-200" />
          <span>{{ successToast }}</span>
        </div>
        <button @click="successToast = null" class="p-1 hover:bg-emerald-700 rounded-lg">
          <X class="w-4 h-4" />
        </button>
      </div>
    </Transition>

    <!-- Title -->
    <div>
      <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-purple-600/20">
          <CheckSquare class="w-4 h-4" />
        </div>
        <span>Review & Verifikasi Pekerjaan</span>
      </h2>
      <p class="text-xs text-slate-500 mt-1 font-medium">Pemeriksaan bukti foto lapangan, koordinat GPS check-in, dan persetujuan resmi (Approval).</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Left: Queue List (Glassmorphic Cards) -->
      <div class="glass-card rounded-3xl p-4 shadow-glass border border-white/80 space-y-3">
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5 px-1">
          <div class="flex items-center gap-2">
            <h3 class="font-bold text-xs text-slate-800">Antrian Review</h3>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-purple-100 text-purple-900 border border-purple-200">
              {{ filteredQueue.length }}
            </span>
          </div>
          <button
            @click="loadQueue(selectedOrder?.id)"
            :disabled="loading"
            class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-500 hover:text-slate-800 transition-all cursor-pointer active:scale-95"
            title="Segarkan Antrian"
          >
            <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin' : '']" />
          </button>
        </div>

        <!-- Search & Filter Controls -->
        <div class="space-y-2">
          <div class="relative">
            <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Cari SPK / Toko / PIC..."
              class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all"
            />
          </div>

          <!-- Status Filter Tabs -->
          <div class="flex items-center gap-1 overflow-x-auto pb-1 text-[10px] font-bold scrollbar-none">
            <button
              v-for="tab in filterTabs"
              :key="tab.id"
              @click="activeStatusFilter = tab.id"
              :class="[
                'px-2.5 py-1 rounded-lg transition-all cursor-pointer whitespace-nowrap flex items-center gap-1',
                activeStatusFilter === tab.id
                  ? 'bg-purple-900 text-white shadow-xs'
                  : 'bg-slate-100 hover:bg-slate-200 text-slate-600'
              ]"
            >
              <span>{{ tab.label }}</span>
              <span
                :class="[
                  'px-1.5 py-0.2 rounded-full text-[9px] font-mono',
                  activeStatusFilter === tab.id ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'
                ]"
              >
                {{ tab.count }}
              </span>
            </button>
          </div>
        </div>

        <!-- List Items -->
        <div class="space-y-2 max-h-[70vh] overflow-y-auto pr-1 custom-scrollbar">
          <div v-if="loading" class="text-center py-10 text-slate-400 text-xs">
            <Loader2 class="w-5 h-5 animate-spin mx-auto mb-2 text-purple-600" />
            <span>Memuat antrian review...</span>
          </div>

          <template v-else-if="filteredQueue.length > 0">
            <div
              v-for="wo in filteredQueue"
              :key="wo.id"
              @click="handleSelectOrder(wo.id)"
              :class="[
                'p-3.5 rounded-2xl border text-xs cursor-pointer transition-all duration-200',
                selectedOrder?.id === wo.id
                  ? 'glass-card border-brand-500 ring-2 ring-brand-500/20 shadow-sm translate-x-0.5'
                  : 'bg-white/60 border-slate-200/70 hover:bg-white hover:border-slate-300'
              ]"
            >
              <div class="flex items-center justify-between mb-1.5">
                <span class="font-mono font-bold text-slate-900">{{ wo.spk_number }}</span>
                <StatusBadge :status="wo.status" />
              </div>
              <div class="font-bold text-slate-800 truncate mb-1">{{ wo.title || wo.location_name }}</div>
              <div class="text-[11px] text-slate-500 flex items-center justify-between">
                <span class="truncate max-w-[120px]">{{ wo.vendor_name || wo.vendor?.name || 'Client SGX' }}</span>
                <span class="font-mono text-purple-800 font-bold">{{ wo.progress_percent || 0 }}%</span>
              </div>
            </div>
          </template>

          <div v-else class="text-center py-10 text-slate-400 text-xs space-y-1">
            <Store class="w-8 h-8 text-slate-300 mx-auto mb-1" />
            <p class="font-bold text-slate-700">Tidak ada SPK di kategori ini</p>
            <p class="text-[11px]">Silakan ubah filter status di atas untuk melihat SPK lainnya.</p>
          </div>
        </div>
      </div>

      <!-- Right: Detailed Inspection Workspace -->
      <div class="lg:col-span-2 glass-card rounded-3xl p-6 shadow-glass border border-white/80 space-y-6">
        <template v-if="selectedOrder">
          <!-- Header Info & Actions -->
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

            <!-- Review Decision Action Buttons -->
            <div class="flex items-center gap-2 flex-wrap">
              <!-- STATE 1: Ready for Review (Submitted by Field Team) -->
              <template v-if="['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'].includes(selectedOrder.status)">
                <button
                  type="button"
                  @click="showRevisionModal = true"
                  :disabled="actionLoading"
                  class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs rounded-xl flex items-center gap-1.5 transition-all cursor-pointer active:scale-95 shadow-2xs"
                >
                  <RotateCcw class="w-3.5 h-3.5" />
                  <span>Minta Revisi</span>
                </button>
                <button
                  type="button"
                  @click="showApproveModal = true"
                  :disabled="actionLoading"
                  class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold text-xs rounded-xl flex items-center gap-1.5 shadow-md shadow-emerald-900/20 transition-all active:scale-95 cursor-pointer"
                >
                  <CheckCircle2 class="w-4 h-4" />
                  <span>Setujui (Approve)</span>
                </button>
              </template>

              <!-- STATE 2: Approved / Completed -->
              <template v-else-if="['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(selectedOrder.status)">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="px-3 py-1.5 bg-emerald-50 text-emerald-800 border border-emerald-300 font-bold text-xs rounded-xl flex items-center gap-1.5">
                    <Check class="w-4 h-4 text-emerald-600" />
                    <span>Disetujui ✓</span>
                  </span>

                  <button
                    v-if="!selectedOrder.ba_document"
                    type="button"
                    @click="handleGenerateBa"
                    :disabled="actionLoading"
                    class="px-3.5 py-1.5 bg-gradient-to-r from-brand-900 to-brand-700 text-white font-bold text-xs rounded-xl flex items-center gap-1.5 shadow-xs cursor-pointer active:scale-95"
                  >
                    <FileCheck2 class="w-4 h-4" />
                    <span>Terbitkan BA Opname</span>
                  </button>
                  <template v-else>
                    <button
                      type="button"
                      @click="$emit('open-ba', selectedOrder.ba_document)"
                      class="px-3.5 py-1.5 bg-teal-700 hover:bg-teal-800 text-white font-bold text-xs rounded-xl flex items-center gap-1.5 shadow-xs cursor-pointer active:scale-95"
                    >
                      <FileCheck2 class="w-4 h-4" />
                      <span>Lihat BA Opname</span>
                    </button>
                  </template>
                </div>
              </template>

              <!-- STATE 3: Under Active Revision by Field Team -->
              <template v-else-if="selectedOrder.status === 'REVISION'">
                <div class="flex items-center gap-2">
                  <span class="px-3 py-1.5 bg-rose-50 text-rose-800 border border-rose-200 font-bold text-xs rounded-xl flex items-center gap-1.5 shadow-2xs">
                    <RotateCcw class="w-3.5 h-3.5 text-rose-600" />
                    <span>Sedang Dalam Perbaikan Revisi Lapangan</span>
                  </span>
                  <button
                    type="button"
                    @click="showApproveModal = true"
                    :disabled="actionLoading"
                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl flex items-center gap-1.5 shadow-xs transition-all active:scale-95 cursor-pointer"
                    title="Setujui jika hasil foto perbaikan telah lengkap"
                  >
                    <CheckCircle2 class="w-3.5 h-3.5" />
                    <span>Setujui Perbaikan</span>
                  </button>
                </div>
              </template>

              <!-- STATE 4: Not Submitted Yet (Assigned / In Progress) -->
              <template v-else>
                <div class="px-3.5 py-2 bg-amber-50 text-amber-900 border border-amber-200 font-bold text-xs rounded-xl flex items-center gap-1.5 shadow-2xs">
                  <Clock class="w-3.5 h-3.5 text-amber-600 shrink-0" />
                  <span>Pekerjaan Sedang Berjalan (Belum Diajukan Review)</span>
                </div>
              </template>
            </div>
          </div>

          <!-- Verified GPS & Timestamp Banner (Glassmorphic) -->
          <div class="p-4 bg-white/70 border border-slate-200/80 rounded-2xl grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs shadow-xs">
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase">LOKASI & AREA</span>
              <div class="font-bold text-slate-900 mt-0.5">{{ selectedOrder.location_name }}</div>
              <div class="text-slate-500 text-[11px]">Area: {{ selectedOrder.area_name }}</div>
            </div>
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase">CHECK-IN GPS RESMI</span>
              <div v-if="selectedOrder.check_ins && selectedOrder.check_ins.length > 0" class="mt-0.5">
                <div class="font-mono text-emerald-700 font-bold">
                  {{ Number(selectedOrder.check_ins[0].latitude).toFixed(5) }}, {{ Number(selectedOrder.check_ins[0].longitude).toFixed(5) }}
                </div>
                <div class="text-slate-500 text-[10px]">
                  Akurasi: ±{{ selectedOrder.check_ins[0].accuracy }}m • {{ new Date(selectedOrder.check_ins[0].server_timestamp).toLocaleTimeString('id-ID') }}
                </div>
              </div>
              <div v-else class="text-amber-600 italic">Belum Check-In</div>
            </div>
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase">PELAKSANA & VENDOR</span>
              <div class="font-bold text-slate-900 mt-0.5">{{ selectedOrder.pic_name || '-' }} ({{ selectedOrder.pic_phone }})</div>
              <div class="text-slate-500 text-[11px]">{{ selectedOrder.vendor_name }}</div>
            </div>
          </div>

          <!-- Photo Evidence Side-by-Side Review -->
          <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <h4 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                <Camera class="w-4 h-4 text-brand-600" />
                <span>Verifikasi Dokumentasi Foto (Before / Process / After)</span>
              </h4>
              <button
                v-if="selectedOrder.evidence_photos && selectedOrder.evidence_photos.length > 0"
                type="button"
                @click="downloadAllPhotos"
                class="px-3 py-1.5 bg-gradient-to-r from-purple-800 to-indigo-600 hover:from-purple-700 hover:to-indigo-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs active:scale-95 transition-all cursor-pointer self-start sm:self-auto"
                title="Unduh semua foto bukti SPK ini"
              >
                <Download class="w-3.5 h-3.5" />
                <span>Unduh Semua Foto SPK ({{ selectedOrder.evidence_photos.length }})</span>
              </button>
            </div>

            <div v-for="stage in ['BEFORE', 'PROCESS', 'AFTER']" :key="stage" class="glass-card rounded-2xl p-4 border border-white/70">
              <div class="flex items-center justify-between mb-3">
                <span class="font-bold text-xs text-slate-800 uppercase tracking-wider">TAHAP: {{ stage }}</span>
                <span class="text-[11px] font-medium text-slate-500">
                  ({{ selectedOrder.evidence_photos?.filter(p => p.stage === stage).length || 0 }} Foto Terlampir)
                </span>
              </div>

              <div
                v-if="selectedOrder.evidence_photos?.filter(p => p.stage === stage).length > 0"
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"
              >
                <div
                  v-for="(photo, pIdx) in selectedOrder.evidence_photos.filter(p => p.stage === stage)"
                  :key="photo.id"
                  @click="openLightbox(photo)"
                  class="bg-slate-900 rounded-2xl overflow-hidden shadow-xs group relative cursor-pointer hover:shadow-md transition-all"
                >
                  <img
                    :src="getFileUrl(photo.file_path)"
                    :alt="`Evidence ${stage}`"
                    class="w-full h-28 object-cover group-hover:scale-105 transition-transform duration-300"
                  />

                  <!-- DYNAMIC FLOATING CORNER DOWNLOAD BUTTON (Bottom-Right) -->
                  <div class="absolute bottom-1.5 right-1.5 z-10">
                    <button
                      type="button"
                      @click.stop="downloadSinglePhoto(photo)"
                      class="w-6 h-6 rounded-full bg-slate-900/90 hover:bg-purple-700 text-white shadow-md flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95 cursor-pointer border border-white/40 backdrop-blur-xs"
                      title="Unduh Foto Resolusi Asli"
                    >
                      <Download class="w-3 h-3" />
                    </button>
                  </div>

                  <div class="p-2 text-[10px] text-white">
                    <div class="font-bold truncate">Foto #{{ photo.sequence || pIdx + 1 }}</div>
                    <div class="text-[8px] font-mono text-emerald-400 truncate">SHA-256: {{ photo.file_hash?.substring(0, 12) }}...</div>
                    <div class="text-[9px] text-slate-300 mt-0.5 font-mono">
                      {{ new Date(photo.server_timestamp).toLocaleTimeString('id-ID') }}
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-5 text-slate-400 text-xs italic">
                Belum ada foto pada tahap {{ stage }}.
              </div>
            </div>
          </div>

          <!-- Technical Issues Note -->
          <div v-if="selectedOrder.issues && selectedOrder.issues.length > 0" class="p-4 bg-amber-500/10 border border-amber-300 rounded-2xl space-y-1.5 text-xs">
            <div class="font-bold text-amber-900 flex items-center gap-2">
              <AlertTriangle class="w-4 h-4 text-amber-600" />
              <span>Laporan Kendala Lapangan:</span>
            </div>
            <p v-for="iss in selectedOrder.issues" :key="iss.id" class="text-slate-800">
              <strong>{{ iss.issue_type }}:</strong> {{ iss.notes }}
            </p>
          </div>
        </template>
        <div v-else class="py-24 text-center text-slate-400 text-xs font-medium">
          Pilih salah satu SPK di sebelah kiri untuk memulai proses review dan verifikasi.
        </div>
      </div>
    </div>

    <!-- Approve Confirmation Modal -->
    <div v-if="showApproveModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="glass-modal rounded-3xl max-w-md w-full shadow-2xl p-6 space-y-4 text-xs border border-white/80">
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
          <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
            <CheckCircle2 class="w-5 h-5 text-emerald-600" />
            <span>Konfirmasi Persetujuan (Approval)</span>
          </h3>
          <button @click="showApproveModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 space-y-1">
          <p class="font-bold">Apakah Anda yakin menyetujui hasil pekerjaan ini?</p>
          <p class="text-[11px] text-emerald-800">
            Nomor SPK: <strong>{{ selectedOrder.spk_number }}</strong> ({{ selectedOrder.title }})
          </p>
        </div>

        <div>
          <label class="block font-bold text-slate-700 mb-1">Catatan Persetujuan / Quality Assurance:</label>
          <textarea
            rows="3"
            v-model="reviewNotes"
            class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none"
          />
        </div>

        <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
          <button
            type="button"
            @click="showApproveModal = false"
            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl"
          >
            Batal
          </button>
          <button
            type="button"
            @click="handleApproveConfirm"
            :disabled="actionLoading"
            class="px-5 py-2 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold rounded-xl shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-95"
          >
            <Loader2 v-if="actionLoading" class="w-3.5 h-3.5 animate-spin" />
            <CheckCircle2 v-else class="w-3.5 h-3.5" />
            <span>{{ actionLoading ? 'Memproses...' : 'Ya, Setujui Pekerjaan' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Revision Modal -->
    <div v-if="showRevisionModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="glass-modal rounded-3xl max-w-md w-full shadow-2xl p-6 space-y-4 text-xs border border-white/80">
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
          <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
            <RotateCcw class="w-4 h-4 text-rose-600" />
            <span>Permintaan Revisi Spesifik</span>
          </h3>
          <button @click="showRevisionModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="handleSendRevision" class="space-y-3.5">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Target Bagian yang Memerlukan Revisi:</label>
            <select
              v-model="revisionStage"
              class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-rose-500 focus:outline-none"
            >
              <option value="AFTER">Foto AFTER (Hasil Akhir)</option>
              <option value="PROCESS">Foto PROCESS (Proses Pengerjaan)</option>
              <option value="BEFORE">Foto BEFORE (Sebelum Pengerjaan)</option>
              <option value="ALL">Seluruh Dokumentasi & Kendala</option>
            </select>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Alasan / Instruksi Perbaikan Khusus *:</label>
            <textarea
              rows="3"
              required
              placeholder="Contoh: Foto After nomor 2 kurang fokus/buram. Mohon ambil ulang foto sudut palang merek secara jelas."
              v-model="revisionReason"
              class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:outline-none bg-white"
            />
          </div>

          <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
            <button
              type="button"
              @click="showRevisionModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="actionLoading"
              class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-xs cursor-pointer active:scale-95"
            >
              {{ actionLoading ? 'Mengirim...' : 'Kirim Revisi ke Lapangan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Reusable Photo Lightbox Full-Screen Viewer -->
    <PhotoLightboxModal
      :isOpen="isLightboxOpen"
      :photos="selectedOrder?.evidence_photos || []"
      :initialIndex="selectedLightboxIndex"
      @close="isLightboxOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { api, getFileUrl } from '../../services/api';
import StatusBadge from '../../components/StatusBadge.vue';
import PhotoLightboxModal from '../../components/PhotoLightboxModal.vue';
import {
  CheckSquare,
  CheckCircle2,
  AlertTriangle,
  RotateCcw,
  Camera,
  X,
  FileCheck2,
  Loader2,
  Check,
  Download,
  Search,
  Store,
  RefreshCw,
  Clock
} from 'lucide-vue-next';

const props = defineProps({
  selectedWorkOrderId: {
    type: [String, Number],
    default: null
  }
});

const emit = defineEmits(['approved-success', 'open-ba']);

const allWorkOrders = ref([]);
const selectedOrder = ref(null);
const loading = ref(true);
const actionLoading = ref(false);
const successToast = ref(null);

const searchQuery = ref('');
const activeStatusFilter = ref('ALL');

const showApproveModal = ref(false);
const reviewNotes = ref('Pekerjaan telah diverifikasi di lapangan dan memenuhi seluruh standar mutu & spesifikasi.');

const showRevisionModal = ref(false);
const revisionStage = ref('AFTER');
const revisionReason = ref('');

const filterTabs = computed(() => {
  const all = allWorkOrders.value;
  return [
    { id: 'ALL', label: 'Semua Antrian', count: all.length },
    { id: 'WAITING_REVIEW', label: 'Menunggu Review', count: all.filter(w => ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'].includes(w.status)).length },
    { id: 'REVISION', label: 'Perlu Revisi', count: all.filter(w => w.status === 'REVISION').length },
    { id: 'IN_PROGRESS', label: 'Sedang Berjalan', count: all.filter(w => ['IN_PROGRESS', 'CHECKED_IN'].includes(w.status)).length },
    { id: 'APPROVED', label: 'Disetujui / Selesai', count: all.filter(w => ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(w.status)).length },
  ];
});

const filteredQueue = computed(() => {
  return allWorkOrders.value.filter(wo => {
    // 1. Status Filter
    if (activeStatusFilter.value === 'WAITING_REVIEW' && !['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'].includes(wo.status)) {
      return false;
    }
    if (activeStatusFilter.value === 'REVISION' && wo.status !== 'REVISION') {
      return false;
    }
    if (activeStatusFilter.value === 'IN_PROGRESS' && !['IN_PROGRESS', 'CHECKED_IN'].includes(wo.status)) {
      return false;
    }
    if (activeStatusFilter.value === 'APPROVED' && !['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status)) {
      return false;
    }

    // 2. Search Query
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase();
      const matchSpk = wo.spk_number?.toLowerCase().includes(q);
      const matchTitle = wo.title?.toLowerCase().includes(q);
      const matchLoc = wo.location_name?.toLowerCase().includes(q);
      const matchVendor = wo.vendor_name?.toLowerCase().includes(q) || wo.vendor?.name?.toLowerCase().includes(q);
      const matchPic = wo.pic_name?.toLowerCase().includes(q);
      if (!matchSpk && !matchTitle && !matchLoc && !matchVendor && !matchPic) {
        return false;
      }
    }

    return true;
  });
});

async function loadQueue(targetIdToSelect = null) {
  loading.value = true;
  try {
    const res = await api.getWorkOrders();
    allWorkOrders.value = res.data || [];

    const targetId = targetIdToSelect || props.selectedWorkOrderId || (allWorkOrders.value.length > 0 ? allWorkOrders.value[0].id : null);
    if (targetId) {
      const detail = await api.getWorkOrderById(targetId);
      selectedOrder.value = detail.data;
    } else if (allWorkOrders.value.length > 0) {
      const detail = await api.getWorkOrderById(allWorkOrders.value[0].id);
      selectedOrder.value = detail.data;
    }
  } catch (err) {
    console.error('Failed to load review queue:', err);
  } finally {
    loading.value = false;
  }
}

watch(() => props.selectedWorkOrderId, (newId) => {
  if (newId) {
    loadQueue(newId);
  }
});

async function handleSelectOrder(id) {
  try {
    const detail = await api.getWorkOrderById(id);
    selectedOrder.value = detail.data;
  } catch (err) {
    alert(err.message);
  }
}

async function handleApproveConfirm() {
  actionLoading.value = true;
  try {
    await api.approveWorkOrder({
      work_order_id: selectedOrder.value.id,
      review_notes: reviewNotes.value
    });
    showApproveModal.value = false;
    successToast.value = `Pekerjaan ${selectedOrder.value.spk_number} berhasil disetujui (APPROVED)!`;
    setTimeout(() => successToast.value = null, 4000);

    const updated = await api.getWorkOrderById(selectedOrder.value.id);
    selectedOrder.value = updated.data;
    emit('approved-success', selectedOrder.value.id);
    loadQueue(selectedOrder.value.id);
  } catch (err) {
    alert(`Gagal menyetujui: ${err.message}`);
  } finally {
    actionLoading.value = false;
  }
}

async function handleSendRevision() {
  if (!revisionReason.value.trim()) {
    alert('Alasan revisi wajib diisi secara spesifik!');
    return;
  }
  actionLoading.value = true;
  try {
    await api.requestRevision({
      work_order_id: selectedOrder.value.id,
      target_stage: revisionStage.value,
      reason: revisionReason.value
    });
    showRevisionModal.value = false;
    revisionReason.value = '';
    successToast.value = `Permintaan revisi dikirimkan ke tim lapangan.`;
    setTimeout(() => successToast.value = null, 4000);

    const updated = await api.getWorkOrderById(selectedOrder.value.id);
    selectedOrder.value = updated.data;
    loadQueue(selectedOrder.value.id);
  } catch (err) {
    alert(`Gagal request revision: ${err.message}`);
  } finally {
    actionLoading.value = false;
  }
}

async function handleGenerateBa() {
  actionLoading.value = true;
  try {
    const res = await api.generateBa({ work_order_id: selectedOrder.value.id });
    successToast.value = `Berita Acara Opname (${res.data.ba_number}) berhasil diterbitkan!`;
    setTimeout(() => successToast.value = null, 4000);

    const updated = await api.getWorkOrderById(selectedOrder.value.id);
    selectedOrder.value = updated.data;
    loadQueue(selectedOrder.value.id);
    emit('open-ba', res.data);
  } catch (err) {
    alert(`Gagal menerbitkan BA: ${err.message}`);
  } finally {
    actionLoading.value = false;
  }
}



/**
 * Lightbox & Download Handlers
 */
const isLightboxOpen = ref(false);
const selectedLightboxIndex = ref(0);

function openLightbox(photo) {
  const allPhotos = selectedOrder.value?.evidence_photos || [];
  const idx = allPhotos.findIndex(p => p.id === photo.id);
  selectedLightboxIndex.value = idx >= 0 ? idx : 0;
  isLightboxOpen.value = true;
}

function downloadSinglePhoto(photo) {
  if (!photo?.file_path) return;
  const link = document.createElement('a');
  link.href = getFileUrl(photo.file_path);
  const ext = photo.file_name?.split('.').pop() || 'jpg';
  const spk = selectedOrder.value?.spk_number ? `${selectedOrder.value.spk_number}_` : '';
  link.download = `${spk}${photo.stage || 'EVIDENCE'}_${photo.sequence || 1}.${ext}`;
  link.target = '_blank';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function downloadAllPhotos() {
  const allPhotos = selectedOrder.value?.evidence_photos || [];
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

onMounted(() => {
  loadQueue();
});
</script>
