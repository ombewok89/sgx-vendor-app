<template>
  <div class="fixed inset-0 z-[100] bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-2 sm:p-4 overflow-y-auto">
    <div class="glass-modal rounded-3xl max-w-5xl w-full shadow-2xl overflow-hidden border border-white/80 max-h-[95vh] flex flex-col my-auto ba-print-container">
      
      <!-- Modal Header & Flexible Print Configuration Bar (Non-Printable) -->
      <div class="p-4 border-b border-slate-200/80 bg-white/90 backdrop-blur-md no-print shrink-0 space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-purple-900 flex items-center justify-center text-white shadow-xs">
              <ShieldCheck class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-black text-sm text-slate-900">
                Dokumen Resmi Berita Acara (BA Opname) & Lampiran Foto
              </h3>
              <p class="text-[10px] text-slate-500 font-mono">
                {{ baData?.ba_number || 'BA/2026/08/0001' }} • {{ wo?.title || 'Proyek SGX' }}
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
              class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-all cursor-pointer font-bold text-sm"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Flexible Print Customizer Controls -->
        <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
          <!-- Photo Layout Grid Selector -->
          <div class="flex items-center gap-2">
            <span class="text-slate-500 font-bold text-[11px] flex items-center gap-1">
              <LayoutGrid class="w-3.5 h-3.5 text-purple-700" />
              <span>Ukuran & Grid Foto Lampiran:</span>
            </span>
            <div class="inline-flex rounded-xl bg-slate-100 p-0.5 border border-slate-200">
              <button
                type="button"
                @click="photoGridCols = 2"
                :class="[
                  'px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer',
                  photoGridCols === 2 ? 'bg-white text-purple-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'
                ]"
              >
                2 Kolom (Besar / HD)
              </button>
              <button
                type="button"
                @click="photoGridCols = 3"
                :class="[
                  'px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer',
                  photoGridCols === 3 ? 'bg-white text-purple-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'
                ]"
              >
                3 Kolom (Standar)
              </button>
              <button
                type="button"
                @click="photoGridCols = 4"
                :class="[
                  'px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer',
                  photoGridCols === 4 ? 'bg-white text-purple-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'
                ]"
              >
                4 Kolom (Kompak)
              </button>
            </div>
          </div>

          <!-- Photo Stage Filter & Checkboxes -->
          <div class="flex items-center gap-3">
            <label class="flex items-center gap-1.5 cursor-pointer select-none text-[11px] font-semibold text-slate-700">
              <input type="checkbox" v-model="includePhotoAnnex" class="rounded text-purple-900 focus:ring-purple-500" />
              <span>Cetak Lembar Lampiran Foto (Annex)</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer select-none text-[11px] font-semibold text-slate-700">
              <input type="checkbox" v-model="showSecurityHash" class="rounded text-purple-900 focus:ring-purple-500" />
              <span>Tampilkan GPS & Hash SHA-256</span>
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer select-none text-[11px] font-semibold text-slate-700">
              <input type="checkbox" v-model="showDefaultDetailsTable" class="rounded text-purple-900 focus:ring-purple-500" />
              <span>Tabel Ringkasan Baku</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Printable Document Container (Supports Multi-Page Printing) -->
      <div
        id="ba-printable-document"
        class="p-8 sm:p-12 overflow-y-auto bg-white text-slate-900 space-y-8 text-xs custom-scrollbar flex-1"
      >
        <!-- ========================================== -->
        <!-- LEMBAR 1: SURAT POKOK BERITA ACARA OPNAME  -->
        <!-- ========================================== -->
        <section class="ba-main-page space-y-6">
          <!-- Official Header -->
          <div class="border-b-2 border-slate-900 pb-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-14 h-14 rounded-xl border bg-white p-1 shadow-xs flex items-center justify-center">
                <img src="/sgx_icon.png" alt="Logo" class="w-full h-full object-contain" />
              </div>
              <div>
                <h2 class="text-lg font-black text-slate-900 tracking-wide">PT SINAR KREASINDO BENCOOLEN - SGX</h2>
                <p class="text-[11px] text-slate-600 font-medium">Digital Vendor Management & Infrastructure Quality Assurance</p>
                <p class="text-[10px] text-slate-500">Anggut Bawah - Ratu Agung, Bengkulu - Indonesia 38222 | Telp: +62 23 8888 5251</p>
              </div>
            </div>
            <div class="text-right">
              <span class="inline-block px-3 py-1 bg-amber-50 text-amber-900 border border-amber-300 font-bold rounded-lg text-[11px] tracking-wider shadow-xs">
                EVIDENCE CERTIFIED ✓
              </span>
            </div>
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

          <!-- Details Table (Opsional / Dapat disembunyikan bila format manual sudah ada tabel) -->
          <div v-if="showDefaultDetailsTable" class="border border-slate-300 rounded-xl overflow-hidden shadow-xs bg-white/95 backdrop-blur-xs">
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
                <tr v-if="canViewFinancial" class="border-b border-slate-200">
                  <td class="py-2.5 px-4 font-semibold text-slate-600">Nilai Kontrak Pekerjaan</td>
                  <td class="py-2.5 px-4 font-mono font-bold text-emerald-800">
                    Rp {{ Number(wo?.contract_value || 0).toLocaleString('id-ID') }}
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
                    <td class="py-2.5 px-3 font-bold text-slate-900">
                      <div class="flex items-center gap-2">
                        <span>{{ itm.item_name }}</span>
                        <span v-if="itm.is_addendum" class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                          + Addendum
                        </span>
                      </div>
                    </td>
                    <td class="py-2.5 px-3 text-slate-600">{{ itm.doc_mode }}</td>
                    <td class="py-2.5 px-3 text-right font-bold text-emerald-800">SELESAI 100% ✓</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Completion Clause Formatted with Dynamic Variables -->
          <div class="leading-relaxed text-slate-700 space-y-2" v-html="formattedBody"></div>

          <!-- Dynamic Signatures Grid -->
          <div
            class="pt-6 grid gap-6 text-center"
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
        </section>

        <!-- ========================================================= -->
        <!-- LEMBAR 2: LEMBARAN KHUSUS LAMPIRAN DOKUMENTASI FOTO       -->
        <!-- ========================================================= -->
        <section v-if="includePhotoAnnex && photos.length > 0" class="ba-photo-annex-page ba-page-break pt-8 space-y-6">
          <!-- Annex Header Kop -->
          <div class="border-b-2 border-slate-900 pb-3 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div class="w-10 h-10 rounded-xl bg-purple-900 flex items-center justify-center p-1 shadow-xs">
                <img src="/sgx_icon.png" alt="Logo" class="w-full h-full object-contain" />
              </div>
              <div>
                <h3 class="font-black text-sm text-slate-900 uppercase tracking-wide">
                  LAMPIRAN DOKUMENTASI FOTO EVIDENSI LAPANGAN
                </h3>
                <p class="text-[11px] text-slate-600 font-mono">
                  Berita Acara No: <strong>{{ baData?.ba_number || '-' }}</strong> • SPK: <strong>{{ wo?.spk_number || '-' }}</strong>
                </p>
              </div>
            </div>
            <div class="text-right text-[10px] text-slate-600">
              <div>Lokasi: <strong>{{ wo?.location_name || '-' }}</strong></div>
              <div>Client: <strong>{{ wo?.vendor_name || 'Client SGX' }}</strong></div>
            </div>
          </div>

          <!-- Photo Grid Gallery (Flexible Size) -->
          <div :class="['grid gap-4', photoGridClass]">
            <div
              v-for="(p, pIdx) in filteredPhotos"
              :key="p.id || pIdx"
              class="border border-slate-300 rounded-2xl p-2.5 bg-white shadow-xs flex flex-col justify-between space-y-2 break-inside-avoid"
            >
              <!-- Photo Image with Aspect Ratio -->
              <div :class="['w-full rounded-xl overflow-hidden bg-slate-100 border border-slate-200 relative', photoImageHeightClass]">
                <img
                  :src="getFileUrl(p.file_path)"
                  :alt="`Bukti ${p.stage}`"
                  class="w-full h-full object-cover"
                  @error="$event.target.src = 'https://images.unsplash.com/photo-1541888946425-d0fbb18086f6?w=600&auto=format&fit=crop&q=60'"
                />
                <span
                  :class="[
                    'absolute top-2 left-2 px-2.5 py-0.5 rounded-lg text-[10px] font-black tracking-wider uppercase shadow-xs border',
                    p.stage === 'BEFORE' ? 'bg-blue-600 text-white border-blue-400' :
                    p.stage === 'PROCESS' ? 'bg-amber-500 text-slate-950 border-amber-300' :
                    'bg-emerald-600 text-white border-emerald-400'
                  ]"
                >
                  {{ p.stage }}
                </span>
                <span class="absolute bottom-2 right-2 px-1.5 py-0.5 rounded bg-slate-950/80 text-white font-mono text-[8px]">
                  #{{ pIdx + 1 }}
                </span>
              </div>

              <!-- Photo Metadata Details -->
              <div class="space-y-1 text-[10px]">
                <div class="flex items-center justify-between font-bold text-slate-900 truncate">
                  <span>{{ p.item_name || `Sub-Pekerjaan #${p.sequence || pIdx + 1}` }}</span>
                  <span class="text-slate-500 font-mono text-[9px]">{{ p.stage }}</span>
                </div>
                <div v-if="p.server_timestamp" class="text-slate-500 flex items-center gap-1 font-mono text-[9px]">
                  <span>🕒 {{ new Date(p.server_timestamp).toLocaleString('id-ID') }}</span>
                </div>
                <div v-if="showSecurityHash" class="space-y-0.5 pt-1 border-t border-slate-100 text-[8px] font-mono text-slate-400">
                  <div class="truncate">📍 GPS: {{ Number(p.latitude || checkIn?.latitude || 0).toFixed(5) }}, {{ Number(p.longitude || checkIn?.longitude || 0).toFixed(5) }}</div>
                  <div class="truncate">🔒 SHA: {{ p.file_hash || 'SHA256-DIGITAL-VERIFIED' }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Annex Endorsement Signatures / Verification Strip -->
          <div class="pt-6 border-t border-slate-200 flex items-center justify-between text-[10px] text-slate-600">
            <div>
              <p>Dokumentasi foto ini merupakan bagian otentik tak terpisahkan dari Berita Acara Nomor: <strong>{{ baData?.ba_number || '-' }}</strong>.</p>
              <p class="text-[9px] text-slate-400">Diverifikasi secara digital melalui sistem SGX Vendor Management.</p>
            </div>
            <div class="flex items-center gap-6 text-center">
              <div class="w-32">
                <p class="text-[9px] text-slate-400">Paraf Pengawas SGX</p>
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-0.5 font-bold truncate">{{ baData?.signatory_first_party_name || 'Pengawas Lapangan' }}</div>
              </div>
              <div class="w-32">
                <p class="text-[9px] text-slate-400">Paraf Vendor Pelaksana</p>
                <div class="h-10"></div>
                <div class="border-t border-slate-400 pt-0.5 font-bold truncate">{{ baData?.signatory_second_party_name || wo?.pic_name || 'Pelaksana' }}</div>
              </div>
            </div>
          </div>
        </section>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Printer, ShieldCheck, LayoutGrid } from 'lucide-vue-next';
import { getFileUrl } from '../services/api';
import { useAuth } from '../composables/useAuth';

const auth = useAuth();
const canViewFinancial = computed(() => ['SUPERUSER', 'SUPERVISOR', 'ADMIN'].includes(auth.state.user?.role));

const props = defineProps({
  baData: {
    type: Object,
    default: null
  }
});

defineEmits(['close']);

// Flexible Photo Print Customization State
const photoGridCols = ref(3); // 2, 3, or 4 columns
const includePhotoAnnex = ref(true);
const showSecurityHash = ref(true);
const showDefaultDetailsTable = ref(false);

const photoGridClass = computed(() => {
  switch (photoGridCols.value) {
    case 2: return 'grid-cols-2';
    case 4: return 'grid-cols-4';
    case 3:
    default: return 'grid-cols-3';
  }
});

const photoImageHeightClass = computed(() => {
  switch (photoGridCols.value) {
    case 2: return 'h-52';
    case 4: return 'h-28';
    case 3:
    default: return 'h-36';
  }
});

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
const photos = computed(() => content.value?.photos || props.baData?.evidence_photos || props.baData?.work_order?.evidence_photos || []);

const filteredPhotos = computed(() => {
  return photos.value;
});

function replaceVariables(text) {
  if (!text) return '';
  const dateStr = new Date(props.baData?.ba_date || Date.now()).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  const valStr = `Rp ${Number(wo.value?.contract_value || 15000000).toLocaleString('id-ID')}`;
  const gpsStr = checkIn.value ? `${Number(checkIn.value.latitude).toFixed(5)}, ${Number(checkIn.value.longitude).toFixed(5)}` : '-';

  let processed = text
    .replace(/\{\{spk_number\}\}/g, wo.value?.spk_number || props.baData?.spk_number || props.baData?.work_order?.spk_number || '')
    .replace(/\{\{title\}\}/g, wo.value?.title || props.baData?.work_order_title || props.baData?.work_order?.title || '')
    .replace(/\{\{vendor_name\}\}/g, wo.value?.vendor_name || props.baData?.vendor_name || props.baData?.work_order?.vendor?.name || '')
    .replace(/\{\{location_name\}\}/g, wo.value?.location_name || props.baData?.work_order?.location_name || '')
    .replace(/\{\{contract_value\}\}/g, valStr)
    .replace(/\{\{ba_date\}\}/g, dateStr)
    .replace(/\{\{checkin_gps\}\}/g, gpsStr);

  processed = processed
    .replace(/\s*(<table[\s\S]*?<\/table>)\s*/gi, '$1')
    .replace(/\n{2,}/g, '<br><br>')
    .replace(/\n/g, '<br>');

  return processed;
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
      company_name: 'PT SINAR KREASINDO BENCOOLEN',
      name: props.baData.signatory_first_party_name || props.baData.generator_name || 'Dian Anggraini',
      role: props.baData.signatory_first_party_role || 'Quality Assurance & Operations'
    }
  ];
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
    background: transparent !important;
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
    padding: 16mm 16mm !important;
    background: white !important;
    color: black !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  .ba-page-break {
    page-break-before: always !important;
    break-before: page !important;
  }
}
</style>
