<template>
  <div class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-modal rounded-3xl max-w-4xl w-full shadow-2xl overflow-hidden my-6 border border-white/80 animate-fade-in">
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between bg-slate-100/70">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-brand-900 text-white flex items-center justify-center shadow-md shadow-brand-900/20">
            <FileText class="w-5 h-5" />
          </div>
          <div>
            <h3 class="font-black text-slate-900 text-base flex items-center gap-2">
              <span>Terbitkan SPK / Work Order Lokasi Cabang</span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Formulir fleksibel untuk penerbitan SPK baru dengan lingkup sub-pekerjaan multi-item.</p>
          </div>
        </div>

        <button
          type="button"
          @click="$emit('close')"
          class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 rounded-2xl transition-all cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Error / Validation Banner -->
      <div v-if="error" class="mx-6 mt-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-2xl flex items-center justify-between gap-2 font-medium shadow-xs">
        <div class="flex items-center gap-2">
          <AlertCircle class="w-4 h-4 shrink-0 text-rose-600" />
          <span>{{ error }}</span>
        </div>
        <button type="button" @click="error = null" class="text-rose-500 hover:text-rose-800 font-bold text-xs cursor-pointer">
          ✕
        </button>
      </div>

      <!-- Form Body with Flexible 2-Column Responsive Layout -->
      <div class="p-6 space-y-5 text-xs">
        
        <!-- Step Tabs for Maximum Flexibility -->
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="activeTab = 'info'"
              :class="[
                'px-4 py-2 rounded-xl font-bold text-xs transition-all flex items-center gap-2 cursor-pointer',
                activeTab === 'info'
                  ? 'bg-brand-900 text-white shadow-md shadow-brand-900/20 ring-1 ring-brand-900'
                  : 'bg-slate-100 hover:bg-slate-200 text-slate-700'
              ]"
            >
              <span>1. Data Pokok & Cabang Toko</span>
            </button>
            <button
              type="button"
              @click="activeTab = 'items'"
              :class="[
                'px-4 py-2 rounded-xl font-bold text-xs transition-all flex items-center gap-2 cursor-pointer',
                activeTab === 'items'
                  ? 'bg-brand-900 text-white shadow-md shadow-brand-900/20 ring-1 ring-brand-900'
                  : 'bg-slate-100 hover:bg-slate-200 text-slate-700'
              ]"
            >
              <span>2. Sub-Pekerjaan & Pengaturan GPS/Timestamp</span>
              <span class="w-5 h-5 rounded-full bg-purple-100 text-purple-900 text-[10px] font-black flex items-center justify-center">
                {{ subItems.length }}
              </span>
            </button>
          </div>

          <span class="text-[11px] text-slate-400 hidden sm:inline font-mono">
            Mode: Fleksibel Cepat
          </span>
        </div>

        <!-- TAB 1: INFORMASI POKOK & CABANG TOKO -->
        <div v-show="activeTab === 'info'" class="space-y-4 animate-fade-in">
          <!-- Row 1: SPK Number & Title -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Nomor SPK (Opsional)</label>
              <input
                type="text"
                placeholder="Auto-generate otomatis jika kosong"
                v-model="formData.spk_number"
                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all font-mono"
              />
            </div>
            <div class="sm:col-span-2">
              <label class="block font-bold text-slate-800 mb-1">Judul / Nama Proyek Cabang <span class="text-rose-500">*</span></label>
              <input
                type="text"
                placeholder="Contoh: Pemasangan Signage & Kanopi KCP Sukajadi"
                v-model="formData.title"
                class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all font-bold text-slate-900"
              />
            </div>
          </div>

          <!-- Row 2: Client & Area -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Perusahaan Client (Pemberi Tugas) <span class="text-rose-500">*</span></label>
              <select
                v-model="formData.vendor_id"
                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all font-medium"
              >
                <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }} ({{ v.code }})</option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Area Operasional <span class="text-rose-500">*</span></label>
              <select
                v-model="formData.area_id"
                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all font-medium"
              >
                <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.name }} ({{ a.city }})</option>
              </select>
            </div>
          </div>

          <!-- Row 3: Primary Job Type & Contract Valuation -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Kategori Utama Jenis Pekerjaan <span class="text-rose-500">*</span></label>
              <select
                v-model="formData.job_type_id"
                @change="handleJobTypeChange"
                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all font-medium"
              >
                <option v-for="j in jobTypes" :key="j.id" :value="j.id">
                  {{ j.name }} (Tarif Standar: Rp {{ Number(j.standard_price || 0).toLocaleString('id-ID') }})
                </option>
              </select>
            </div>
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="block font-bold text-slate-700">Nilai Kontrak / Pekerjaan (Rp) <span class="text-rose-500">*</span></label>
                <span class="text-[9px] text-emerald-700 font-bold bg-emerald-50 px-2 py-0.2 rounded-md border border-emerald-200">
                  Auto-fill Standar
                </span>
              </div>
              <input
                type="number"
                v-model="formData.contract_value"
                placeholder="Contoh: 15000000"
                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all font-mono font-bold text-slate-900"
              />
              <div v-if="formData.contract_value" class="text-[11px] font-mono font-bold text-emerald-700 mt-1">
                Rp {{ Number(formData.contract_value).toLocaleString('id-ID') }}
              </div>
            </div>
          </div>

          <!-- Row 4: Location Name -->
          <div>
            <label class="block font-bold text-slate-800 mb-1">Nama Cabang & Alamat Lokasi Lengkap <span class="text-rose-500">*</span></label>
            <input
              type="text"
              placeholder="Contoh: Indomaret Cabang Adam Malik - Jl. Adam Malik No. 12, Bengkulu"
              v-model="formData.location_name"
              class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all text-slate-900"
            />
          </div>

          <!-- Row 5: PIC & Dates -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
            <div>
              <label class="block font-bold text-slate-700 mb-1">PIC Teknisi Lapangan</label>
              <select
                v-model="formData.pic_user_id"
                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all font-medium"
              >
                <option value="">Pilih nanti (Status: READY)</option>
                <option v-for="u in fieldUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.phone }})</option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai</label>
              <input
                type="date"
                v-model="formData.start_date"
                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all"
              />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Target Selesai / Deadline <span class="text-rose-500">*</span></label>
              <input
                type="date"
                v-model="formData.deadline"
                class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all font-bold text-slate-900"
              />
            </div>
          </div>
        </div>

        <!-- TAB 2: LINGKUP SUB-PEKERJAAN & PENGATURAN GPS / TIMESTAMP -->
        <div v-show="activeTab === 'items'" class="space-y-4 animate-fade-in">
          
          <!-- Sub-Pekerjaan Section with Fast 1-Click Presets -->
          <div class="bg-slate-50/80 rounded-2xl p-4 border border-slate-200 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div>
                <h4 class="font-black text-xs text-slate-900 flex items-center gap-1.5">
                  <Layers class="w-4 h-4 text-brand-700" />
                  <span>Lingkup Sub-Pekerjaan di Lokasi Ini ({{ subItems.length }} Item)</span>
                </h4>
                <p class="text-[11px] text-slate-500">Tambahkan rincian pekerjaan atau gunakan tombol preset instan di bawah.</p>
              </div>

              <button
                type="button"
                @click="addSubItem"
                class="px-3 py-1.5 bg-brand-900 hover:bg-brand-800 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs active:scale-95 transition-all cursor-pointer self-start sm:self-auto"
              >
                <Plus class="w-3.5 h-3.5" />
                <span>+ Tambah Sub-Item</span>
              </button>
            </div>

            <!-- Fast 1-Click Presets -->
            <div class="flex items-center gap-1.5 flex-wrap pt-1 border-t border-slate-200/60">
              <span class="text-[10px] font-bold text-slate-400">Preset Cepat:</span>
              <button
                v-for="preset in quickPresets"
                :key="preset"
                type="button"
                @click="addPresetItem(preset)"
                class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-white hover:bg-purple-50 text-purple-900 border border-purple-200 transition-all cursor-pointer active:scale-95 shadow-2xs"
              >
                + {{ preset }}
              </button>
            </div>

            <!-- List of Sub-Items -->
            <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
              <div
                v-for="(itm, idx) in subItems"
                :key="idx"
                class="p-3.5 bg-white border border-slate-200 rounded-xl space-y-2 relative shadow-xs"
              >
                <div class="flex items-center justify-between gap-2">
                  <span class="font-bold text-[11px] text-brand-900 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded-md">
                    Sub-Pekerjaan #{{ idx + 1 }}
                  </span>
                  <button
                    v-if="subItems.length > 1"
                    type="button"
                    @click="removeSubItem(idx)"
                    class="text-rose-500 hover:text-rose-700 p-1 rounded-lg hover:bg-rose-50 transition-all cursor-pointer"
                    title="Hapus sub-pekerjaan ini"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                  <div>
                    <label class="block font-bold text-slate-700 text-[10px] mb-1">Nama Sub-Pekerjaan</label>
                    <input
                      type="text"
                      placeholder="Contoh: Pemasangan Neon Box Depan Toko"
                      v-model="itm.item_name"
                      class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none"
                    />
                  </div>
                  <div>
                    <label class="block font-bold text-slate-700 text-[10px] mb-1">Mode Evidence Foto</label>
                    <select
                      v-model="itm.doc_mode"
                      class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none"
                    >
                      <option value="BEFORE_PROCESS_AFTER">BEFORE + PROCESS + AFTER (3 Fase Lengkap)</option>
                      <option value="AFTER_ONLY">AFTER ONLY (Hasil Akhir Saja)</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- GPS Coordinates & Auto-Detect -->
          <div class="p-3.5 bg-purple-50/40 border border-purple-200/80 rounded-2xl space-y-2">
            <div class="flex items-center justify-between">
              <label class="block font-bold text-slate-800 text-xs flex items-center gap-1.5">
                <MapPin class="w-3.5 h-3.5 text-purple-700" />
                <span>Titik Target Koordinat GPS Cabang (Opsional / Otomatis)</span>
              </label>
              <button
                type="button"
                @click="detectCurrentLocation"
                :disabled="detectingGps"
                class="px-2.5 py-1 bg-purple-800 hover:bg-purple-700 text-white rounded-lg text-[10px] font-bold flex items-center gap-1 shadow-xs cursor-pointer active:scale-95 transition-all"
              >
                <Navigation class="w-3 h-3" />
                <span>{{ detectingGps ? 'Mendeteksi GPS...' : '📍 Ambil GPS Saya Saat Ini' }}</span>
              </button>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <span class="block text-[10px] text-slate-500 font-bold mb-0.5">Latitude:</span>
                <input
                  type="number"
                  step="any"
                  v-model="formData.target_lat"
                  placeholder="-3.79284"
                  class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-mono"
                />
              </div>
              <div>
                <span class="block text-[10px] text-slate-500 font-bold mb-0.5">Longitude:</span>
                <input
                  type="number"
                  step="any"
                  v-model="formData.target_lng"
                  placeholder="102.2607"
                  class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-mono"
                />
              </div>
            </div>
          </div>

          <!-- Check-In & Timestamp Options -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- 1. Check-in Requirement -->
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl space-y-1.5">
              <div class="flex items-start gap-2">
                <input
                  type="checkbox"
                  id="require_checkin"
                  v-model="formData.require_checkin"
                  class="w-4 h-4 mt-0.5 text-brand-600 rounded cursor-pointer"
                />
                <div>
                  <label for="require_checkin" class="font-bold text-slate-800 cursor-pointer text-xs">
                    Wajib 1x Check-In GPS di Lokasi
                  </label>
                  <p class="text-[10px] text-slate-500 mt-0.5">
                    Teknisi harus check-in lokasi cabang saat pertama kali tiba di lokasi.
                  </p>
                </div>
              </div>
            </div>

            <!-- 2. Use Timestamp Camera Option -->
            <div class="p-3 bg-purple-50/60 border border-purple-200 rounded-2xl space-y-1.5">
              <div class="flex items-start gap-2">
                <input
                  type="checkbox"
                  id="use_timestamp"
                  v-model="formData.use_timestamp"
                  class="w-4 h-4 mt-0.5 text-purple-600 rounded cursor-pointer accent-purple-700"
                />
                <div>
                  <label for="use_timestamp" class="font-bold text-slate-900 cursor-pointer text-xs flex items-center gap-1">
                    <Camera class="w-3.5 h-3.5 text-purple-700" />
                    <span>Wajib Kamera Timestamp GPS</span>
                  </label>
                  <p class="text-[10px] text-slate-500 mt-0.5">
                    <span v-if="formData.use_timestamp" class="text-purple-900 font-bold">
                      ✓ Aktif (Stempel satelit & map pada foto).
                    </span>
                    <span v-else class="text-amber-800 font-bold">
                      ⚠️ Nonaktif (Mode upload bebas tanpa stempel).
                    </span>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label class="block font-bold text-slate-700 mb-1">Catatan & Instruksi Tambahan</label>
            <textarea
              rows="2"
              placeholder="Tambahkan catatan khusus untuk tim lapangan..."
              v-model="formData.notes"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none"
            ></textarea>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 border-t border-slate-200 flex items-center justify-between gap-3">
          <div class="flex items-center gap-2">
            <button
              v-if="activeTab === 'items'"
              type="button"
              @click="activeTab = 'info'"
              class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-all cursor-pointer"
            >
              ← Kembali ke Data Pokok
            </button>
            <button
              v-if="activeTab === 'info'"
              type="button"
              @click="activeTab = 'items'"
              class="px-4 py-2.5 bg-purple-50 hover:bg-purple-100 text-purple-900 border border-purple-200 font-bold rounded-xl transition-all cursor-pointer"
            >
              Lanjut ke Sub-Pekerjaan & GPS →
            </button>
          </div>

          <div class="flex items-center gap-2.5">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-all cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="handleSubmit"
              :disabled="loading"
              class="px-6 py-2.5 bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 hover:from-brand-800 hover:to-brand-600 text-white font-bold rounded-xl flex items-center gap-2 shadow-md shadow-brand-900/25 active:scale-95 transition-all duration-200 disabled:opacity-50 cursor-pointer"
            >
              <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
              <FileCheck2 v-else class="w-4 h-4" />
              <span>{{ loading ? 'Menerbitkan SPK...' : 'Terbitkan SPK Sekarang' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { api } from '../../services/api';
import {
  X,
  FileText,
  AlertCircle,
  Layers,
  Plus,
  Trash2,
  MapPin,
  Navigation,
  Camera,
  FileCheck2,
  Loader2
} from 'lucide-vue-next';

const emit = defineEmits(['close', 'success']);

const activeTab = ref('info');
const vendors = ref([]);
const areas = ref([]);
const jobTypes = ref([]);
const fieldUsers = ref([]);
const loading = ref(false);
const detectingGps = ref(false);
const error = ref(null);

const quickPresets = [
  'Pemasangan Signage Fasad',
  'Neon Box Depan',
  'Huruf Timbul LED',
  'Pylon Sign 6 Meter',
  'Cat & Finishing Dinding'
];

const subItems = ref([
  { item_name: '', doc_mode: 'BEFORE_PROCESS_AFTER' }
]);

const formData = reactive({
  spk_number: '',
  title: '',
  vendor_id: '',
  area_id: '',
  job_type_id: '',
  contract_value: 0,
  location_name: '',
  target_lat: null,
  target_lng: null,
  pic_user_id: '',
  start_date: new Date().toISOString().split('T')[0],
  deadline: new Date(Date.now() + 5 * 86400000).toISOString().split('T')[0],
  require_checkin: true,
  require_geofence: false,
  geofence_radius: 500,
  use_timestamp: true,
  notes: ''
});

function addPresetItem(presetName) {
  if (subItems.value.length === 1 && !subItems.value[0].item_name) {
    subItems.value[0].item_name = presetName;
  } else {
    subItems.value.push({
      item_name: presetName,
      doc_mode: 'BEFORE_PROCESS_AFTER'
    });
  }
}

function detectCurrentLocation() {
  if (!navigator.geolocation) {
    alert('Browser Anda tidak mendukung deteksi lokasi otomatis.');
    return;
  }
  detectingGps.value = true;
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      formData.target_lat = parseFloat(pos.coords.latitude.toFixed(6));
      formData.target_lng = parseFloat(pos.coords.longitude.toFixed(6));
      detectingGps.value = false;
      alert(`Lokasi GPS terdeteksi: ${formData.target_lat}, ${formData.target_lng}`);
    },
    (err) => {
      detectingGps.value = false;
      alert('Gagal mendeteksi lokasi: ' + err.message);
    },
    { enableHighAccuracy: true, timeout: 8000 }
  );
}

function handleJobTypeChange() {
  const selected = jobTypes.value.find(j => j.id === formData.job_type_id);
  if (selected && selected.standard_price) {
    formData.contract_value = selected.standard_price;
  }
}

function addSubItem() {
  subItems.value.push({
    item_name: '',
    doc_mode: 'BEFORE_PROCESS_AFTER'
  });
}

function removeSubItem(idx) {
  if (subItems.value.length > 1) {
    subItems.value.splice(idx, 1);
  }
}

onMounted(async () => {
  try {
    const [vRes, aRes, jRes, uRes] = await Promise.all([
      api.getVendors(),
      api.getAreas(),
      api.getJobTypes(),
      api.getUsers({ role: 'FIELD_TEAM' })
    ]);
    vendors.value = vRes.data || [];
    areas.value = aRes.data || [];
    jobTypes.value = jRes.data || [];
    fieldUsers.value = uRes.data || [];

    if (vendors.value.length > 0) formData.vendor_id = vendors.value[0].id;
    if (areas.value.length > 0) formData.area_id = areas.value[0].id;
    if (jobTypes.value.length > 0) {
      formData.job_type_id = jobTypes.value[0].id;
      formData.contract_value = jobTypes.value[0].standard_price || 15000000;
    }
  } catch (err) {
    console.error('Failed to load form options:', err);
  }
});

async function handleSubmit() {
  // Clear any previous error
  error.value = null;

  // Validate required fields
  if (!formData.title || formData.title.trim().length === 0) {
    error.value = 'Judul / Nama Proyek Cabang wajib diisi.';
    activeTab.value = 'info';
    return;
  }

  if (!formData.vendor_id) {
    error.value = 'Silakan pilih Perusahaan Client (Pemberi Tugas).';
    activeTab.value = 'info';
    return;
  }

  if (!formData.area_id) {
    error.value = 'Silakan pilih Area Operasional.';
    activeTab.value = 'info';
    return;
  }

  if (!formData.job_type_id) {
    error.value = 'Silakan pilih Kategori Utama Jenis Pekerjaan.';
    activeTab.value = 'info';
    return;
  }

  if (!formData.location_name || formData.location_name.trim().length === 0) {
    error.value = 'Nama Cabang & Alamat Lokasi Lengkap wajib diisi.';
    activeTab.value = 'info';
    return;
  }

  if (!formData.deadline) {
    error.value = 'Target Selesai / Deadline wajib diisi.';
    activeTab.value = 'info';
    return;
  }

  // Filter valid sub-items or fallback automatically to project title
  let validItems = subItems.value
    .filter(i => i.item_name && i.item_name.trim().length > 0)
    .map(i => ({
      item_name: i.item_name.trim(),
      doc_mode: i.doc_mode || 'BEFORE_PROCESS_AFTER'
    }));

  if (validItems.length === 0) {
    validItems = [{
      item_name: formData.title.trim(),
      doc_mode: 'BEFORE_PROCESS_AFTER'
    }];
  }

  loading.value = true;
  try {
    const payload = {
      spk_number: formData.spk_number || undefined,
      title: formData.title.trim(),
      vendor_id: formData.vendor_id,
      area_id: formData.area_id,
      job_type_id: formData.job_type_id,
      contract_value: formData.contract_value ? Number(formData.contract_value) : 0,
      location_name: formData.location_name.trim(),
      target_lat: formData.target_lat ? Number(formData.target_lat) : null,
      target_lng: formData.target_lng ? Number(formData.target_lng) : null,
      pic_user_id: formData.pic_user_id || null,
      start_date: formData.start_date || undefined,
      deadline: formData.deadline,
      require_checkin: !!formData.require_checkin,
      use_timestamp: !!formData.use_timestamp,
      notes: formData.notes || null,
      items: validItems
    };

    await api.createWorkOrder(payload);
    emit('success');
    emit('close');
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Gagal menerbitkan SPK. Silakan periksa kembali data Anda.';
  } finally {
    loading.value = false;
  }
}
</script>
