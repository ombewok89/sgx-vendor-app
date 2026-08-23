<template>
  <div class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-modal rounded-3xl max-w-2xl w-full shadow-2xl overflow-hidden my-6 border border-white/80">
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between bg-slate-100/60">
        <div>
          <h3 class="font-black text-slate-900 text-sm flex items-center gap-2">
            <FileText class="w-4 h-4 text-brand-600" />
            <span>Terbitkan SPK / Work Order Lokasi Cabang</span>
          </h3>
          <p class="text-[11px] text-slate-500 mt-0.5">Dapat memuat satu atau beberapa item sub-pekerjaan sekaligus dalam 1 cabang / lokasi.</p>
        </div>
        <button
          @click="$emit('close')"
          class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 rounded-xl transition-all"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div v-if="error" class="m-6 mb-0 p-3 bg-rose-500/10 border border-rose-300 text-rose-800 text-xs rounded-xl flex items-center gap-2 font-medium">
        <AlertCircle class="w-4 h-4 shrink-0 text-rose-600" />
        <span>{{ error }}</span>
      </div>

      <form @submit.prevent="handleSubmit" class="p-6 space-y-4 text-xs">
        <!-- Row 1: SPK Number & Title -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Nomor SPK (Opsional)</label>
            <input
              type="text"
              placeholder="Auto-generated jika kosong"
              v-model="formData.spk_number"
              class="w-full px-3 py-2 bg-white/80 border border-slate-200/80 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all"
            />
          </div>
          <div class="sm:col-span-2">
            <label class="block font-bold text-slate-700 mb-1">Judul / Nama Proyek Cabang *</label>
            <input
              type="text"
              required
              placeholder="Contoh: Pemasangan Signage & Kanopi KCP Sukajadi"
              v-model="formData.title"
              class="w-full px-3 py-2 bg-white/80 border border-slate-200/80 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all"
            />
          </div>
        </div>

        <!-- Row 2: Client & Area -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Perusahaan Client (Pemberi Tugas) *</label>
            <select
              v-model="formData.vendor_id"
              class="w-full px-3 py-2 bg-white/80 border border-slate-200/80 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all"
            >
              <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }} ({{ v.code }})</option>
            </select>
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Area Operasional *</label>
            <select
              v-model="formData.area_id"
              class="w-full px-3 py-2 bg-white/80 border border-slate-200/80 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all"
            >
              <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.name }} ({{ a.city }})</option>
            </select>
          </div>
        </div>

        <!-- Row 3: Primary Job Type & Contract Valuation -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Kategori Utama Jenis Pekerjaan *</label>
            <select
              v-model="formData.job_type_id"
              @change="handleJobTypeChange"
              class="w-full px-3 py-2 bg-white/80 border border-slate-200/80 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all"
            >
              <option v-for="j in jobTypes" :key="j.id" :value="j.id">
                {{ j.name }} (Tarif Standar: Rp {{ Number(j.standard_price || 0).toLocaleString('id-ID') }})
              </option>
            </select>
          </div>
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block font-bold text-slate-700">Nilai Kontrak / Pekerjaan (Rp) *</label>
              <span class="text-[9px] text-emerald-700 font-bold bg-emerald-50 px-2 py-0.2 rounded-md border border-emerald-200">
                Auto-fill Master
              </span>
            </div>
            <input
              type="number"
              v-model="formData.contract_value"
              placeholder="Contoh: 15000000"
              class="w-full px-3 py-2 bg-white/80 border border-slate-200/80 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all font-mono"
            />
            <div v-if="formData.contract_value" class="text-[11px] font-mono font-bold text-emerald-700 mt-1">
              Rp {{ Number(formData.contract_value).toLocaleString('id-ID') }}
            </div>
          </div>
        </div>

        <!-- Row 4: Location Name -->
        <div>
          <label class="block font-bold text-slate-700 mb-1">Nama Cabang & Alamat Lokasi Lengkap *</label>
          <input
            type="text"
            required
            placeholder="Contoh: Bank Mandiri KCP Sukajadi - Jl. Sukajadi No. 182, Bandung"
            v-model="formData.location_name"
            class="w-full px-3 py-2 bg-white/80 border border-slate-200/80 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- Row 4: Multi-Item Work Orders Section -->
        <div class="glass-card rounded-2xl p-4 border border-brand-200/60 bg-brand-50/20 space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="font-black text-xs text-slate-900 flex items-center gap-1.5">
              <Layers class="w-4 h-4 text-brand-700" />
              <span>Daftar Sub-Pekerjaan di Lokasi Ini ({{ subItems.length }} Item)</span>
            </h4>
            <button
              type="button"
              @click="addSubItem"
              class="px-2.5 py-1 bg-brand-900 text-white rounded-lg text-[11px] font-bold flex items-center gap-1 shadow-xs active:scale-95 transition-all"
            >
              <Plus class="w-3.5 h-3.5" />
              <span>Tambah Sub-Pekerjaan</span>
            </button>
          </div>

          <div class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
            <div
              v-for="(itm, idx) in subItems"
              :key="idx"
              class="p-3 bg-white/90 border border-slate-200/80 rounded-xl space-y-2 relative shadow-xs"
            >
              <div class="flex items-center justify-between gap-2">
                <span class="font-bold text-[11px] text-brand-900 bg-brand-100/70 px-2 py-0.5 rounded">
                  Item #{{ idx + 1 }}
                </span>
                <button
                  v-if="subItems.length > 1"
                  type="button"
                  @click="removeSubItem(idx)"
                  class="text-rose-500 hover:text-rose-700 p-1 rounded hover:bg-rose-50 transition-all"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div>
                  <label class="block font-bold text-slate-700 text-[10px] mb-0.5">Nama Pekerjaan *</label>
                  <input
                    type="text"
                    required
                    placeholder="Contoh: Pemasangan Pylon Signage 6M"
                    v-model="itm.item_name"
                    class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs"
                  />
                </div>
                <div>
                  <label class="block font-bold text-slate-700 text-[10px] mb-0.5">Mode Evidence Foto</label>
                  <select
                    v-model="itm.doc_mode"
                    class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium"
                  >
                    <option value="BEFORE_PROCESS_AFTER">BEFORE + PROCESS + AFTER</option>
                    <option value="AFTER_ONLY">AFTER ONLY (Hasil Akhir)</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Row 5: PIC & Dates -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">PIC Lapangan (Opsional)</label>
            <select
              v-model="formData.pic_user_id"
              class="w-full px-3 py-2 bg-white/80 border border-slate-200/80 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all"
            >
              <option value="">Pilih nanti (Status: READY)</option>
              <option v-for="u in fieldUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.phone }})</option>
            </select>
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai *</label>
            <input
              type="date"
              required
              v-model="formData.start_date"
              class="w-full px-3 py-2 bg-white/80 border border-slate-200/80 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all"
            />
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Deadline Selesai *</label>
            <input
              type="date"
              required
              v-model="formData.deadline"
              class="w-full px-3 py-2 bg-white/80 border border-slate-200/80 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all"
            />
          </div>
        </div>

        <!-- Row 5.5: Target GPS Coordinates with Auto-Detect -->
        <div class="p-3 bg-brand-50/40 border border-brand-200/80 rounded-2xl space-y-2">
          <div class="flex items-center justify-between">
            <label class="block font-bold text-slate-800 text-xs flex items-center gap-1.5">
              <MapPin class="w-3.5 h-3.5 text-brand-700" />
              <span>Titik Target Koordinat GPS Cabang</span>
            </label>
            <button
              type="button"
              @click="detectCurrentLocation"
              :disabled="detectingGps"
              class="px-2.5 py-1 bg-gradient-to-r from-indigo-700 to-brand-800 hover:from-indigo-600 hover:to-brand-700 text-white rounded-lg text-[10px] font-bold flex items-center gap-1 shadow-xs cursor-pointer active:scale-95 transition-all"
            >
              <Navigation class="w-3 h-3" />
              <span>{{ detectingGps ? 'Mendeteksi GPS...' : '📍 Gunakan Lokasi GPS Saya Saat Ini' }}</span>
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
          <p class="text-[10px] text-slate-500">
            * Koordinat ini digunakan sebagai acuan radius check-in teknisi lapangan.
          </p>
        </div>

        <!-- Row 6: Check-In Requirement & Geofencing Verification (Point 3.1) -->
        <div class="space-y-2 p-3 bg-slate-50/90 border border-slate-200/80 rounded-2xl">
          <div class="flex items-center gap-2">
            <input
              type="checkbox"
              id="require_checkin"
              v-model="formData.require_checkin"
              class="w-4 h-4 text-brand-600 rounded cursor-pointer"
            />
            <label for="require_checkin" class="font-bold text-slate-800 cursor-pointer text-xs">
              Wajib 1x Check-In GPS di lokasi cabang sebelum tim mengunggah foto evidence
            </label>
          </div>

          <!-- Geofencing Checklist Toggle -->
          <div v-if="formData.require_checkin" class="pl-6 pt-2 border-t border-slate-200/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2">
              <input
                type="checkbox"
                id="require_geofence"
                v-model="formData.require_geofence"
                class="w-4 h-4 text-purple-600 rounded cursor-pointer"
              />
              <label for="require_geofence" class="text-xs font-semibold text-slate-700 cursor-pointer flex items-center gap-1.5">
                <span class="text-purple-700 font-bold">🎯 Verifikasi Geofencing:</span>
                <span>Validasi radius kecocokan GPS target</span>
              </label>
            </div>

            <div v-if="formData.require_geofence" class="flex items-center gap-2 text-xs">
              <span class="text-slate-500 font-medium">Toleransi Radius:</span>
              <select
                v-model="formData.geofence_radius"
                class="px-2 py-1 bg-white border border-slate-300 rounded-lg text-xs font-bold text-purple-900 focus:outline-none"
              >
                <option :value="250">250 Meter (Standar Cabang Retail)</option>
                <option :value="500">500 Meter (Area Kantor / Ruko)</option>
                <option :value="1000">1.000 Meter (Area Luas / Kawasan)</option>
                <option :value="5000">5.000 Meter (Kota / Wilayah Luas)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Row 6.5: Timestamp Camera Option (Use Timestamp vs Upload Bebas) -->
        <div class="p-3 bg-gradient-to-r from-amber-500/10 via-purple-500/10 to-indigo-500/10 border border-purple-200/80 rounded-2xl space-y-1.5">
          <div class="flex items-start gap-2.5">
            <input
              type="checkbox"
              id="use_timestamp"
              v-model="formData.use_timestamp"
              class="w-4 h-4 mt-0.5 text-purple-600 rounded cursor-pointer accent-purple-700"
            />
            <div class="space-y-0.5">
              <label for="use_timestamp" class="font-bold text-slate-900 cursor-pointer text-xs flex items-center gap-1.5">
                <Camera class="w-3.5 h-3.5 text-purple-700" />
                <span>Wajib Dokumentasi Menggunakan Kamera Timestamp GPS (Stempel Otomatis)</span>
              </label>
              <p class="text-[11px] text-slate-500">
                <span v-if="formData.use_timestamp" class="text-purple-900 font-medium">
                  ✓ <strong>Aktif:</strong> Teknisi wajib membidik foto menggunakan kamera bawaan berstempel jam, GPS satelit, alamat OpenStreetMap & mini map.
                </span>
                <span v-else class="text-amber-800 font-bold">
                  ⚠️ <strong>Nonaktif:</strong> Mode upload bebas. Dokumentasi foto murni melalui upload file galeri dan <strong>tidak ada tampilan/stempel timeslipe pada foto</strong>.
                </span>
              </p>
            </div>
          </div>
        </div>

        <!-- Row 7: Notes -->
        <div>
          <label class="block font-bold text-slate-700 mb-1">Instruksi Khusus / Catatan Lapangan</label>
          <textarea
            rows="2"
            placeholder="Tambahkan catatan khusus untuk tim pelaksana..."
            v-model="formData.notes"
            class="w-full px-3 py-2 bg-white/80 border border-slate-200/80 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all"
          />
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 border-t border-slate-200/80 flex items-center justify-end gap-2.5">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2.5 glass-card hover:bg-slate-100 text-slate-700 font-bold rounded-xl transition-all"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-6 py-2.5 bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 hover:from-brand-800 hover:to-brand-600 text-white font-bold rounded-xl flex items-center gap-2 shadow-md shadow-brand-900/25 active:scale-95 transition-all duration-200 disabled:opacity-50"
          >
            {{ loading ? 'Menerbitkan SPK...' : 'Terbitkan SPK' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { api } from '../../services/api';
import { X, FileText, AlertCircle, Layers, Plus, Trash2, MapPin, Navigation, Camera } from 'lucide-vue-next';

const emit = defineEmits(['close', 'success']);

const vendors = ref([]);
const areas = ref([]);
const jobTypes = ref([]);
const fieldUsers = ref([]);
const loading = ref(false);
const detectingGps = ref(false);
const error = ref(null);

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
  if (!formData.title || !formData.location_name) {
    error.value = 'Nama proyek dan alamat lokasi cabang wajib diisi.';
    return;
  }

  // Filter valid sub-items
  const validItems = subItems.value
    .filter(i => i.item_name && i.item_name.trim().length > 0)
    .map(i => ({
      item_name: i.item_name.trim(),
      doc_mode: i.doc_mode
    }));

  loading.value = true;
  error.value = null;
  try {
    const payload = {
      ...formData,
      items: validItems.length > 0 ? validItems : [{ item_name: formData.title, doc_mode: 'BEFORE_PROCESS_AFTER' }]
    };
    await api.createWorkOrder(payload);
    emit('success');
    emit('close');
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}
</script>
