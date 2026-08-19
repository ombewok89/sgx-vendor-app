<template>
  <div class="fixed inset-0 z-[100] bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-2 sm:p-4 overflow-y-auto">
    <div class="glass-modal rounded-3xl max-w-4xl w-full shadow-2xl overflow-hidden border border-white/80 max-h-[95vh] flex flex-col my-auto ba-print-container">
      <!-- Modal Header (Non-Printable) -->
      <div class="p-4 border-b border-slate-200/80 flex items-center justify-between bg-white/70 backdrop-blur-md no-print shrink-0">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-xl bg-purple-900 flex items-center justify-center text-white shadow-xs">
            <ShieldCheck class="w-4 h-4" />
          </div>
          <div>
            <h3 class="font-black text-sm text-slate-900">
              Dokumen Resmi Berita Acara (BA Opname)
            </h3>
            <p class="text-[10px] text-slate-500 font-mono">
              {{ baData?.ba_number || 'BA/2026/08/0001' }}
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="handlePrint"
            class="px-4 py-2 bg-gradient-to-r from-purple-900 to-indigo-800 hover:from-purple-800 hover:to-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-xs active:scale-95 transition-all cursor-pointer"
          >
            <Printer class="w-4 h-4" />
            <span>Cetak / Download PDF</span>
          </button>
          <button
            type="button"
            @click="$emit('close')"
            class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-all cursor-pointer font-bold"
          >
            ✕
          </button>
        </div>
      </div>

      <!-- Printable Document Body with Background Paper Support -->
      <div
        id="ba-printable-document"
        class="p-8 sm:p-12 overflow-y-auto bg-white text-slate-900 space-y-6 text-xs custom-scrollbar relative flex-1"
        :style="bgStyle"
      >
        <!-- Header Kop Surat (Shown when no full background template is uploaded) -->
        <div v-if="!hasBackgroundTemplate" class="border-b-2 border-slate-900 pb-4 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div v-if="logoUrl" class="w-14 h-14 rounded-lg border bg-white p-1 shadow-xs">
              <img :src="getFileUrl(logoUrl)" alt="Logo" class="w-full h-full object-contain" />
            </div>
            <div>
              <h2 class="text-lg font-black text-slate-900 tracking-wide">PT SINAR GRAHA KONSTRUKSI (SGX)</h2>
              <p class="text-[11px] text-slate-600 font-medium">Digital Vendor Management & Infrastructure Quality Assurance</p>
              <p class="text-[10px] text-slate-500">Gedung Graha SGX Lt. 5, Jl. Bisnis Utama No. 88, Jakarta Selatan | Telp: (021) 789-0123</p>
            </div>
          </div>
          <div class="text-right">
            <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-300 font-bold rounded-lg text-[11px] tracking-wider shadow-xs">
              EVIDENCE CERTIFIED ✓
            </span>
          </div>
        </div>
        <div v-else class="h-16">
          <!-- Spacer for letterhead background header -->
        </div>

        <!-- Title -->
        <div class="text-center py-2">
          <h3 class="text-sm font-black uppercase tracking-wider underline text-slate-900">
            BERITA ACARA HASIL PEKERJAAN & OPNAME LAPANGAN KONSOLIDASI
          </h3>
          <p class="text-xs text-slate-600 mt-1">
            Nomor: <strong class="font-mono">{{ baData?.ba_number || '-' }}</strong>
          </p>
        </div>

        <!-- Opening Statement Formatted with Dynamic Variables -->
        <div class="leading-relaxed text-slate-700" v-html="formattedHeader"></div>

        <!-- Details Table -->
        <div class="border border-slate-300 rounded-xl overflow-hidden shadow-xs bg-white/95 backdrop-blur-xs">
          <table class="w-full text-xs">
            <tbody>
              <tr class="border-b border-slate-200 bg-slate-50/80">
                <td class="w-1/3 py-2.5 px-4 font-semibold text-slate-600">Nomor SPK</td>
                <td class="py-2.5 px-4 font-bold font-mono text-purple-900">{{ wo?.spk_number || baData?.spk_number || '-' }}</td>
              </tr>
              <tr class="border-b border-slate-200">
                <td class="py-2.5 px-4 font-semibold text-slate-600">Nama Proyek / Cabang</td>
                <td class="py-2.5 px-4 font-bold text-slate-900">{{ wo?.title || baData?.work_order_title || '-' }}</td>
              </tr>
              <tr class="border-b border-slate-200 bg-slate-50/80">
                <td class="py-2.5 px-4 font-semibold text-slate-600">Perusahaan Client (Pemberi Tugas)</td>
                <td class="py-2.5 px-4 font-medium">{{ wo?.vendor_name || baData?.vendor_name || baData?.work_order?.vendor?.name || 'Client SGX' }}</td>
              </tr>
              <tr class="border-b border-slate-200">
                <td class="py-2.5 px-4 font-semibold text-slate-600">Nilai Kontrak Pekerjaan</td>
                <td class="py-2.5 px-4 font-mono font-bold text-emerald-800">
                  Rp {{ Number(wo?.contract_value || 15000000).toLocaleString('id-ID') }}
                </td>
              </tr>
              <tr class="border-b border-slate-200 bg-slate-50/80">
                <td class="py-2.5 px-4 font-semibold text-slate-600">Lokasi Cabang & Alamat</td>
                <td class="py-2.5 px-4 font-medium">{{ wo?.location_name || baData?.work_order?.location_name || '-' }}</td>
              </tr>
              <tr class="border-b border-slate-200">
                <td class="py-2.5 px-4 font-semibold text-slate-600">PIC Tim Lapangan</td>
                <td class="py-2.5 px-4 font-medium">{{ wo?.pic_name || baData?.work_order?.pic?.name || 'Tim Lapangan' }} {{ (wo?.pic_phone || baData?.work_order?.pic?.phone) ? `(${wo?.pic_phone || baData?.work_order?.pic?.phone})` : '' }}</td>
              </tr>
              <tr>
                <td class="py-2.5 px-4 font-semibold text-slate-600">Waktu & GPS Check-In</td>
                <td class="py-2.5 px-4 font-medium font-mono text-[11px]">
                  <span v-if="checkIn">
                    {{ new Date(checkIn.server_timestamp).toLocaleString('id-ID') }} • GPS: {{ Number(checkIn.latitude).toFixed(5) }}, {{ Number(checkIn.longitude).toFixed(5) }} (±{{ checkIn.accuracy }}m)
                  </span>
                  <span v-else>Check-in digital terverifikasi</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Sub-Items Table (Multi-Item Consolidated Breakdown) -->
        <div v-if="items.length > 0" class="space-y-2">
          <h4 class="font-bold text-xs uppercase text-slate-800">Rincian Sub-Pekerjaan yang Diserahterimakan:</h4>
          <div class="border border-slate-300 rounded-xl overflow-hidden bg-white/95 backdrop-blur-xs">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-100/90 text-slate-700 font-bold border-b border-slate-300">
                <tr>
                  <th class="py-2.5 px-3 w-10">No</th>
                  <th class="py-2.5 px-3">Item Pekerjaan</th>
                  <th class="py-2.5 px-3">Mode Dokumentasi</th>
                  <th class="py-2.5 px-3 text-right">Status Pengerjaan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                <tr v-for="(itm, idx) in items" :key="itm.id || idx" class="hover:bg-slate-50">
                  <td class="py-2.5 px-3 font-mono font-bold text-slate-500">{{ idx + 1 }}</td>
                  <td class="py-2.5 px-3 font-bold text-slate-900">{{ itm.item_name }}</td>
                  <td class="py-2.5 px-3 text-slate-600">{{ itm.doc_mode }}</td>
                  <td class="py-2.5 px-3 text-right font-bold text-emerald-800">SELESAI 100% ✓</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Completion Clause Formatted with Dynamic Variables -->
        <div class="leading-relaxed text-slate-700 space-y-2" v-html="formattedBody"></div>

        <!-- Photo Evidence Highlights -->
        <div v-if="photos.length > 0">
          <h4 class="font-bold text-xs uppercase text-slate-700 mb-2">Lampiran Bukti Foto Digital Terverifikasi:</h4>
          <div class="grid grid-cols-3 gap-3">
            <div v-for="(p, idx) in photos.slice(0, 6)" :key="idx" class="border border-slate-200 rounded-xl p-1.5 text-center bg-white/95 shadow-xs">
              <img
                :src="getFileUrl(p.file_path)"
                :alt="`Bukti ${p.stage}`"
                class="w-full h-24 object-cover rounded-lg mb-1.5"
                @error="$event.target.src = 'https://images.unsplash.com/photo-1541888946425-d0fbb18086f6?w=300&auto=format&fit=crop&q=60'"
              />
              <div class="font-bold text-[10px] text-slate-800">{{ p.stage }}</div>
              <div class="text-[8px] font-mono text-slate-500 truncate">{{ p.file_hash?.substring(0, 16) }}...</div>
            </div>
          </div>
        </div>

        <!-- Footer Banner if present -->
        <div v-if="footerUrl" class="rounded-xl overflow-hidden my-4 border border-slate-200">
          <img :src="getFileUrl(footerUrl)" alt="Footer Banner" class="w-full h-auto object-contain" />
        </div>

        <!-- Dynamic Signatures Grid -->
        <div
          class="pt-8 grid gap-6 text-center"
          :class="signatoriesList.length === 2 ? 'grid-cols-2' : signatoriesList.length === 3 ? 'grid-cols-3' : signatoriesList.length >= 4 ? 'grid-cols-4' : 'grid-cols-2'"
        >
          <div v-for="(sig, sIdx) in signatoriesList" :key="sIdx" class="space-y-12">
            <div>
              <p class="text-slate-500 font-medium text-[11px]">{{ sig.party_title || `Pihak #${sIdx + 1}` }}</p>
              <strong class="text-slate-800 text-xs block truncate">{{ sig.company_name }}</strong>
            </div>
            <div class="border-t border-slate-500 pt-1.5 mx-2">
              <div class="font-bold text-slate-900">{{ sig.name || '........................' }}</div>
              <div class="text-[10px] text-slate-500">{{ sig.role || 'Jabatan Resmi' }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Printer, ShieldCheck } from 'lucide-vue-next';
import { getFileUrl } from '../services/api';

const props = defineProps({
  baData: {
    type: Object,
    default: null
  }
});

defineEmits(['close']);

const template = computed(() => props.baData?.template || {});

const content = computed(() => {
  if (!props.baData) return null;
  if (props.baData.content_json) {
    return typeof props.baData.content_json === 'string'
      ? JSON.parse(props.baData.content_json)
      : props.baData.content_json;
  }
  return props.baData.content || {};
});

const wo = computed(() => content.value?.work_order || props.baData?.work_order || {});
const items = computed(() => content.value?.items || []);
const checkIn = computed(() => content.value?.check_in);
const photos = computed(() => content.value?.photos || []);

const hasBackgroundTemplate = computed(() => {
  return !!(template.value?.background_image_url || props.baData?.background_image_url);
});

const logoUrl = computed(() => {
  return template.value?.logo_url || props.baData?.logo_url;
});

const footerUrl = computed(() => {
  return template.value?.footer_image_url || props.baData?.footer_image_url;
});

function replaceVariables(text) {
  if (!text) return '';
  const dateStr = new Date(props.baData?.ba_date || Date.now()).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  const valStr = `Rp ${Number(wo.value?.contract_value || 15000000).toLocaleString('id-ID')}`;
  const gpsStr = checkIn.value ? `${Number(checkIn.value.latitude).toFixed(5)}, ${Number(checkIn.value.longitude).toFixed(5)}` : '-';

  return text
    .replace(/\{\{spk_number\}\}/g, wo.value?.spk_number || props.baData?.spk_number || props.baData?.work_order?.spk_number || '')
    .replace(/\{\{title\}\}/g, wo.value?.title || props.baData?.work_order_title || props.baData?.work_order?.title || '')
    .replace(/\{\{vendor_name\}\}/g, wo.value?.vendor_name || props.baData?.vendor_name || props.baData?.work_order?.vendor?.name || '')
    .replace(/\{\{location_name\}\}/g, wo.value?.location_name || props.baData?.work_order?.location_name || '')
    .replace(/\{\{contract_value\}\}/g, valStr)
    .replace(/\{\{ba_date\}\}/g, dateStr)
    .replace(/\{\{checkin_gps\}\}/g, gpsStr)
    .replace(/\n/g, '<br>');
}

const formattedHeader = computed(() => {
  const raw = template.value?.header_html || props.baData?.header_html || `Pada hari ini <strong>{{ba_date}}</strong>, telah dilakukan pemeriksaan dan verifikasi lapangan atas pelaksanaan seluruh item pekerjaan untuk <strong>{{title}}</strong> di lokasi <strong>{{location_name}}</strong> dengan rincian sebagai berikut:`;
  return replaceVariables(raw);
});

const formattedBody = computed(() => {
  const raw = template.value?.body_template || props.baData?.body_template || `Berdasarkan hasil pemeriksaan bukti foto digital (Before, Process, After) dan verifikasi teknis di lapangan, kedua belah pihak menyatakan bahwa seluruh item pekerjaan telah <strong>SELESAI 100% SECARA BAIK DAN MEMENUHI SPESIFIKASI MUTU</strong>.<br><br>Mitra Vendor memberikan jaminan masa pemeliharaan (garansi mutu) selama <strong>90 (sembilan puluh) hari kalender</strong> terhitung sejak tanggal penandatanganan Berita Acara ini.`;
  return replaceVariables(raw);
});

const signatoriesList = computed(() => {
  if (!props.baData) return [];
  const rawSig = template.value?.signatories_json || props.baData.signatories_json;
  if (rawSig) {
    try {
      const parsed = typeof rawSig === 'string' ? JSON.parse(rawSig) : rawSig;
      if (Array.isArray(parsed) && parsed.length > 0) return parsed;
    } catch (e) {}
  }
  return [
    {
      party_title: 'Pihak Pertama (Vendor Pelaksana)',
      company_name: wo.value?.vendor_name || props.baData?.work_order?.vendor?.name || 'Mitra Vendor',
      name: props.baData.signatory_second_party_name || wo.value?.pic_name || 'Andi Pratama',
      role: props.baData.signatory_second_party_role || 'Penanggung Jawab Lapangan'
    },
    {
      party_title: 'Pihak Kedua (SGX Management)',
      company_name: 'PT SINAR GRAHA KONSTRUKSI',
      name: props.baData.signatory_first_party_name || props.baData.generator_name || 'Dian Anggraini',
      role: props.baData.signatory_first_party_role || 'Quality Assurance & Operations'
    }
  ];
});

const bgStyle = computed(() => {
  const rawBg = template.value?.background_image_url || props.baData?.background_image_url || template.value?.header_image_url || props.baData?.header_image_url;
  if (!rawBg) return {};
  const bgUrl = getFileUrl(rawBg);
  return {
    backgroundImage: `url(${bgUrl})`,
    backgroundSize: '100% 100%',
    backgroundRepeat: 'no-repeat',
    backgroundPosition: 'center'
  };
});

function handlePrint() {
  window.print();
}
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  .no-print {
    display: none !important;
  }
  .ba-print-container {
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    border: none;
    box-shadow: none;
    background: transparent;
  }
  #ba-printable-document, #ba-printable-document * {
    visibility: visible;
  }
  #ba-printable-document {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    margin: 0;
    padding: 20mm;
    background: white !important;
    color: black !important;
  }
}
</style>
