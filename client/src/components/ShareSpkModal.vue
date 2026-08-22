<template>
  <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 animate-fade-in">
    <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200">
      
      <!-- Modal Header -->
      <div class="px-5 py-4 bg-gradient-to-r from-purple-900 via-indigo-900 to-slate-900 text-white flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/20 shadow-inner">
            <Share2 class="w-5 h-5 text-amber-400" />
          </div>
          <div>
            <h3 class="font-bold text-sm leading-tight">Bagikan Live Tracking SPK</h3>
            <p class="text-[11px] text-purple-200 font-mono">{{ workOrder?.spk_number || 'SPK Tracker' }}</p>
          </div>
        </div>
        <button
          @click="$emit('close')"
          class="p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 transition-colors"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-5 space-y-4">
        <!-- Info Banner -->
        <div class="bg-purple-50/70 border border-purple-200/80 rounded-xl p-3.5 flex items-start gap-3 text-xs text-purple-950">
          <ShieldCheck class="w-5 h-5 text-purple-700 shrink-0 mt-0.5" />
          <div>
            <p class="font-bold text-purple-900 mb-0.5">Tautan Pemantauan Publik Aman</p>
            <p class="text-[11px] text-purple-800 leading-relaxed">
              Pihak luar (Kepala Toko, Pengawas, atau Auditor) dapat memantau progres fisik dan foto real-time tanpa perlu akun. <strong>Informasi nominal harga dan nilai kontrak disembunyikan 100%.</strong>
            </p>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="py-8 flex flex-col items-center justify-center text-xs text-slate-500 gap-2">
          <Loader2 class="w-6 h-6 animate-spin text-purple-600" />
          <span>Menyiapkan tautan pemantauan...</span>
        </div>

        <!-- Content State -->
        <div v-else class="space-y-4">
          <!-- Toggle Shareable Status -->
          <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 bg-slate-50/60">
            <div>
              <span class="text-xs font-bold text-slate-900 block">Status Akses Publik</span>
              <span class="text-[11px] text-slate-500">{{ isShareable ? 'Tautan aktif & dapat diakses publik' : 'Tautan dinonaktifkan (Privat)' }}</span>
            </div>
            <button
              @click="toggleShareStatus"
              :disabled="toggling"
              :class="[
                'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden',
                isShareable ? 'bg-purple-900' : 'bg-slate-300'
              ]"
            >
              <span
                :class="[
                  'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out',
                  isShareable ? 'translate-x-5' : 'translate-x-0'
                ]"
              />
            </button>
          </div>

          <!-- Share Link Box -->
          <div>
            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              URL Live Tracking:
            </label>
            <div class="flex items-center gap-2">
              <input
                type="text"
                readonly
                :value="shareUrl"
                class="flex-1 bg-slate-100 border border-slate-300 rounded-xl px-3 py-2 text-xs font-mono text-slate-800 select-all focus:outline-hidden focus:ring-2 focus:ring-purple-500"
              />
              <button
                @click="copyToClipboard"
                class="px-3.5 py-2 bg-purple-900 hover:bg-purple-800 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shrink-0 shadow-xs cursor-pointer active:scale-95"
              >
                <Check v-if="copied" class="w-4 h-4 text-emerald-300" />
                <Copy v-else class="w-4 h-4" />
                <span>{{ copied ? 'Tersalin!' : 'Salin' }}</span>
              </button>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="grid grid-cols-2 gap-2.5 pt-1">
            <a
              :href="whatsappShareUrl"
              target="_blank"
              class="flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs active:scale-95 text-center cursor-pointer"
            >
              <MessageSquare class="w-4 h-4 text-emerald-100" />
              <span>Kirim WhatsApp</span>
            </a>
            <button
              type="button"
              @click="openShareLink"
              class="flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-xs active:scale-95 text-center cursor-pointer"
            >
              <ExternalLink class="w-4 h-4 text-slate-300" />
              <span>Buka Tautan</span>
            </button>
          </div>

          <!-- QR Code Preview Helper -->
          <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-center gap-4">
            <div class="w-20 h-20 bg-white border border-slate-200 rounded-lg p-1 shrink-0 flex items-center justify-center shadow-2xs overflow-hidden">
              <img
                :src="qrCodeUrl"
                alt="QR Code"
                class="w-full h-full object-contain"
                @error="onQrError"
              />
            </div>
            <div class="text-[11px] text-slate-600 space-y-1">
              <p class="font-bold text-slate-900 text-xs">QR Code Pemantauan Cabang</p>
              <p>Dapat di-scan menggunakan kamera HP oleh Kepala Toko atau Tim Pengawas di lokasi cabang.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
        <button
          @click="$emit('close')"
          class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-xl text-xs font-bold transition-colors cursor-pointer"
        >
          Tutup
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Share2, X, ShieldCheck, Copy, Check, MessageSquare, ExternalLink, Loader2 } from 'lucide-vue-next';
import { api } from '../services/api';

const props = defineProps({
  workOrder: {
    type: Object,
    required: true
  }
});

defineEmits(['close']);

const loading = ref(true);
const toggling = ref(false);
const copied = ref(false);
const shareToken = ref('');
const isShareable = ref(true);

const defaultToken = computed(() => {
  const wo = props.workOrder;
  if (wo?.share_token) return wo.share_token;
  if (wo?.spk_number) {
    return 'spk-' + wo.spk_number.toString().toLowerCase().replace(/[^a-z0-9]/g, '-');
  }
  return 'spk-' + (wo?.id || 'live');
});

const shareUrl = computed(() => {
  const token = shareToken.value || defaultToken.value;
  const origin = window.location.origin;
  return `${origin}/track/${token}`;
});

const qrCodeUrl = ref('');

function updateQrCode(url) {
  qrCodeUrl.value = `https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=${encodeURIComponent(url)}`;
}

function onQrError(e) {
  // Fallback to Google Charts QR API if primary fails
  e.target.src = `https://chart.googleapis.com/chart?chs=160x160&cht=qr&chl=${encodeURIComponent(shareUrl.value)}&choe=UTF-8`;
}

const whatsappShareUrl = computed(() => {
  const url = shareUrl.value;
  const spkNo = props.workOrder?.spk_number || 'SPK';
  const loc = props.workOrder?.location_name || 'Lokasi Pekerjaan';
  const text = `Halo, berikut tautan pantau langsung (Live Tracking) progres pekerjaan SPK *${spkNo}* di *${loc}*:\n\n🔗 ${url}\n\n_Pantau progres foto & status teknis secara real-time._`;
  return `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;
});

async function loadShareToken() {
  loading.value = true;
  // Initialize with robust fallback immediately
  shareToken.value = defaultToken.value;
  updateQrCode(shareUrl.value);

  const woId = props.workOrder?.id || props.workOrder?.work_order_id || props.workOrder?.spk_number;
  if (woId) {
    try {
      const res = await api.getWorkOrderShareToken(woId);
      if (res && res.data && res.data.share_token) {
        shareToken.value = res.data.share_token;
        isShareable.value = res.data.is_shareable !== undefined ? res.data.is_shareable : true;
        updateQrCode(shareUrl.value);
      }
    } catch (err) {
      console.warn('Backend share token fetch fallback to SPK slug:', err);
    }
  }
  loading.value = false;
}

async function toggleShareStatus() {
  toggling.value = true;
  const targetState = !isShareable.value;
  isShareable.value = targetState; // Optimistic instant UI update

  const woId = props.workOrder?.id || props.workOrder?.work_order_id || props.workOrder?.spk_number;
  if (woId) {
    try {
      const res = await api.toggleWorkOrderShare(woId);
      if (res && res.is_shareable !== undefined) {
        isShareable.value = res.is_shareable;
      }
    } catch (err) {
      console.warn('Toggle share API warning (optimistic state preserved):', err);
    } finally {
      toggling.value = false;
    }
  } else {
    toggling.value = false;
  }
}

function openShareLink() {
  if (shareUrl.value) {
    window.open(shareUrl.value, '_blank');
  }
}

function copyToClipboard() {
  if (!shareUrl.value) return;
  navigator.clipboard.writeText(shareUrl.value).then(() => {
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  });
}

onMounted(() => {
  loadShareToken();
});
</script>
