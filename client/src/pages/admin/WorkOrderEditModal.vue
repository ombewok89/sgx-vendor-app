<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-modal rounded-3xl max-w-3xl w-full shadow-2xl overflow-hidden my-6 flex flex-col max-h-[92vh] border border-white/90 animate-in fade-in zoom-in duration-200">
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-purple-600/30 border border-purple-500/40 flex items-center justify-center text-purple-300">
            <ShieldCheck class="w-5 h-5" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-mono font-black text-xs bg-purple-500/20 text-purple-300 border border-purple-500/30 px-2.5 py-0.5 rounded-lg">
                SUPERVISOR ACCESS
              </span>
              <span class="text-xs text-slate-300 font-mono">{{ formData.spk_number }}</span>
            </div>
            <h3 class="font-black text-sm sm:text-base text-white mt-0.5">
              Edit Data & Pengaturan SPK
            </h3>
          </div>
        </div>

        <button
          @click="$emit('close')"
          class="p-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition-all cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 overflow-y-auto space-y-5 text-xs text-slate-700 custom-scrollbar bg-slate-50/50">
        <!-- Loading State -->
        <div v-if="loadingData" class="py-16 text-center text-slate-400 font-medium">
          <Loader2 class="w-7 h-7 animate-spin mx-auto mb-2 text-purple-600" />
          <span>Memuat data konfigurasi SPK...</span>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Supervisor Banner Info -->
          <div class="p-3.5 bg-gradient-to-r from-purple-900/15 via-indigo-900/10 to-purple-900/15 border border-purple-200 rounded-2xl flex items-center gap-2.5 text-xs text-purple-950 font-bold">
            <Lock class="w-4 h-4 text-purple-700 shrink-0" />
            <span>Fitur ini khusus Supervisor/Superuser untuk memperbarui data SPK, mengubah penugasan PIC & Tim, serta menyesuaikan parameter checklist dan GPS.</span>
          </div>

          <!-- Error Alert -->
          <div v-if="error" class="p-3 bg-rose-500/10 border border-rose-300 text-rose-800 text-xs rounded-xl flex items-center gap-2 font-medium">
            <AlertCircle class="w-4 h-4 shrink-0 text-rose-600" />
            <span>{{ error }}</span>
          </div>

          <!-- Section 1: Identitas & No SPK -->
          <div class="p-4 bg-white rounded-2xl border border-slate-200/90 shadow-2xs space-y-3">
            <h4 class="font-black text-xs text-slate-900 uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-100 pb-2">
              <FileText class="w-3.5 h-3.5 text-purple-700" />
              <span>1. Identitas & Judul Pekerjaan</span>
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block font-bold text-slate-700 mb-1">Nomor SPK *</label>
                <input
                  type="text"
                  required
                  v-model="formData.spk_number"
                  class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono font-bold text-xs focus:bg-white focus:ring-2 focus:ring-purple-500/20"
                />
              </div>
              <div class="sm:col-span-2">
                <label class="block font-bold text-slate-700 mb-1">Judul / Nama Proyek SPK *</label>
                <input
                  type="text"
                  required
                  v-model="formData.title"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-bold text-xs focus:ring-2 focus:ring-purple-500/20"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block font-bold text-slate-700 mb-1">Perusahaan Client *</label>
                <select
                  required
                  v-model="formData.vendor_id"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-bold text-xs focus:ring-2 focus:ring-purple-500/20"
                >
                  <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }} ({{ v.code }})</option>
                </select>
              </div>

              <div>
                <label class="block font-bold text-slate-700 mb-1">Area Operasional *</label>
                <select
                  required
                  v-model="formData.area_id"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-bold text-xs focus:ring-2 focus:ring-purple-500/20"
                >
                  <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.name }} ({{ a.city || a.province || 'Wilayah' }})</option>
                </select>
              </div>

              <div>
                <label class="block font-bold text-slate-700 mb-1">Kategori Utama *</label>
                <select
                  required
                  v-model="formData.job_type_id"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-bold text-xs focus:ring-2 focus:ring-purple-500/20"
                >
                  <option v-for="j in jobTypes" :key="j.id" :value="j.id">{{ j.name }}</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Nama Cabang & Alamat Lengkap *</label>
              <input
                type="text"
                required
                v-model="formData.location_name"
                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-purple-500/20"
              />
            </div>
          </div>

          <!-- Section 2: Penugasan Tim & Jadwal -->
          <div class="p-4 bg-white rounded-2xl border border-slate-200/90 shadow-2xs space-y-3">
            <h4 class="font-black text-xs text-slate-900 uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-100 pb-2">
              <Users class="w-3.5 h-3.5 text-purple-700" />
              <span>2. Penugasan Tim Lapangan & Jadwal Target</span>
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block font-bold text-slate-700 mb-1">Ketua Tim / PIC Lapangan *</label>
                <select
                  v-model="formData.pic_user_id"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-bold text-xs focus:ring-2 focus:ring-purple-500/20"
                >
                  <option value="">-- Belum Ditugaskan --</option>
                  <option v-for="u in fieldUsers" :key="u.id" :value="u.id">
                    {{ u.name }} ({{ u.phone || 'No HP -' }})
                  </option>
                </select>
              </div>

              <div>
                <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai Pengerjaan *</label>
                <input
                  type="date"
                  required
                  v-model="formData.start_date"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono"
                />
              </div>

              <div>
                <label class="block font-bold text-slate-700 mb-1">Deadline Selesai *</label>
                <input
                  type="date"
                  required
                  v-model="formData.deadline"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono"
                />
              </div>
            </div>

            <!-- Anggota Tim Tambahan -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Anggota Tim Tambahan (Penugasan Multi-Teknisi)</label>
              <div class="max-h-32 overflow-y-auto p-2 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                <label
                  v-for="u in fieldUsers.filter(u => u.id !== formData.pic_user_id)"
                  :key="u.id"
                  class="flex items-center gap-2 p-1.5 hover:bg-white rounded-lg cursor-pointer text-xs"
                >
                  <input
                    type="checkbox"
                    :value="u.id"
                    v-model="selectedMemberIds"
                    class="rounded border-slate-300 text-purple-600 focus:ring-purple-500"
                  />
                  <span>{{ u.name }} ({{ u.phone || 'No HP -' }})</span>
                </label>
                <div v-if="fieldUsers.filter(u => u.id !== formData.pic_user_id).length === 0" class="text-slate-400 text-[11px] p-1 italic">
                  Pilih PIC terlebih dahulu untuk menentukan anggota lainnya.
                </div>
              </div>
            </div>
          </div>

          <!-- Section 3: Sub Pekerjaan & Rincian Checklist -->
          <div class="p-4 bg-white rounded-2xl border border-slate-200/90 shadow-2xs space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
              <h4 class="font-black text-xs text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                <CheckSquare class="w-3.5 h-3.5 text-purple-700" />
                <span>3. Rincian Sub Pekerjaan ({{ subItems.length }} Item Checklist)</span>
              </h4>
              <button
                type="button"
                @click="addSubItem"
                class="px-2.5 py-1 bg-purple-50 hover:bg-purple-100 text-purple-800 font-bold rounded-lg text-[10px] flex items-center gap-1 transition-all cursor-pointer"
              >
                <Plus class="w-3 h-3" />
                <span>Tambah Sub Item</span>
              </button>
            </div>

            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
              <div
                v-for="(item, idx) in subItems"
                :key="idx"
                class="flex items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl"
              >
                <span class="w-5 h-5 rounded-full bg-purple-100 text-purple-900 font-bold text-[10px] flex items-center justify-center shrink-0">
                  {{ idx + 1 }}
                </span>

                <input
                  type="text"
                  required
                  placeholder="Nama item pekerjaan (misal: Pasang Rangka Hollo 4x4)"
                  v-model="item.item_name"
                  class="flex-1 px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold focus:ring-1 focus:ring-purple-500"
                />

                <select
                  v-model="item.doc_mode"
                  class="px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-[10px] font-bold"
                >
                  <option value="BEFORE_PROCESS_AFTER">3 Tahap (Before/Process/After)</option>
                  <option value="AFTER_ONLY">1 Tahap (After Saja)</option>
                </select>

                <button
                  type="button"
                  @click="removeSubItem(idx)"
                  :disabled="subItems.length <= 1"
                  class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg disabled:opacity-30 cursor-pointer"
                  title="Hapus Item"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>

          <!-- Section 4: Pengaturan Forensik, GPS, & Status Override -->
          <div class="p-4 bg-white rounded-2xl border border-slate-200/90 shadow-2xs space-y-3">
            <h4 class="font-black text-xs text-slate-900 uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-100 pb-2">
              <Settings class="w-3.5 h-3.5 text-purple-700" />
              <span>4. Pengaturan Forensik, GPS & Status SPK</span>
            </h4>

            <!-- GPS Target Location -->
            <div class="p-3 bg-purple-50/40 border border-purple-200/70 rounded-xl space-y-2">
              <div class="flex items-center justify-between">
                <label class="block font-bold text-slate-800 text-xs flex items-center gap-1">
                  <MapPin class="w-3.5 h-3.5 text-purple-700" />
                  <span>Titik Target Koordinat GPS Lokasi</span>
                </label>
                <button
                  type="button"
                  @click="detectCurrentLocation"
                  :disabled="detectingGps"
                  class="px-2 py-0.5 bg-purple-700 hover:bg-purple-800 text-white rounded-lg text-[10px] font-bold flex items-center gap-1 cursor-pointer"
                >
                  <Navigation class="w-3 h-3" />
                  <span>{{ detectingGps ? 'Mendeteksi...' : '📍 Ambil GPS Saat Ini' }}</span>
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

            <!-- Checkin Requirement Toggle & Status Override -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
              <div class="flex items-center gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                <input
                  type="checkbox"
                  id="edit_require_checkin"
                  v-model="formData.require_checkin"
                  class="w-4 h-4 text-purple-600 rounded cursor-pointer"
                />
                <label for="edit_require_checkin" class="font-bold text-slate-800 cursor-pointer text-xs">
                  Wajib Check-In GPS di lokasi sebelum teknisi upload foto
                </label>
              </div>

              <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                <label class="block font-bold text-slate-800 text-xs mb-1">Override Status SPK (Supervisor Only)</label>
                <select
                  v-model="formData.status"
                  class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold"
                >
                  <option value="DRAFT">DRAFT — Draft Baru</option>
                  <option value="ASSIGNED">ASSIGNED — Tim Ditugaskan</option>
                  <option value="IN_PROGRESS">IN_PROGRESS — Sedang Dikerjakan</option>
                  <option value="SUBMITTED">SUBMITTED — Siap Direview</option>
                  <option value="REVISION">REVISION — Permintaan Revisi</option>
                  <option value="APPROVED">APPROVED — Disetujui</option>
                  <option value="BA_OPNAME">BA_OPNAME — BA Opname Terbit</option>
                  <option value="COMPLETED">COMPLETED — Selesai 100%</option>
                  <option value="CANCELLED">CANCELLED — Dibatalkan</option>
                </select>
              </div>
            </div>

            <!-- Notes -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Catatan / Instruksi Khusus Supervisor</label>
              <textarea
                rows="2"
                v-model="formData.notes"
                placeholder="Catatan pengerjaan, instruksi K3, atau persyaratan khusus..."
                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs"
              ></textarea>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition-all cursor-pointer"
            >
              Batal
            </button>

            <button
              type="submit"
              :disabled="submitting"
              class="px-6 py-2.5 bg-gradient-to-r from-purple-800 via-indigo-900 to-slate-900 hover:from-purple-700 hover:to-indigo-800 text-white font-bold rounded-xl shadow-md flex items-center gap-2 active:scale-95 transition-all cursor-pointer disabled:opacity-50"
            >
              <Loader2 v-if="submitting" class="w-4 h-4 animate-spin" />
              <Save v-else class="w-4 h-4" />
              <span>{{ submitting ? 'Menyimpan Perubahan...' : 'Simpan Perubahan SPK' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { api } from '../../services/api';
import {
  ShieldCheck,
  X,
  FileText,
  Users,
  CheckSquare,
  Settings,
  MapPin,
  Navigation,
  Plus,
  Trash2,
  Lock,
  Save,
  Loader2,
  AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  workOrderId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits(['close', 'updated']);

const loadingData = ref(false);
const submitting = ref(false);
const error = ref('');
const detectingGps = ref(false);

const vendors = ref([]);
const areas = ref([]);
const jobTypes = ref([]);
const fieldUsers = ref([]);
const selectedMemberIds = ref([]);
const subItems = ref([]);

const formData = reactive({
  id: null,
  spk_number: '',
  title: '',
  vendor_id: '',
  area_id: '',
  job_type_id: '',
  location_name: '',
  target_lat: null,
  target_lng: null,
  pic_user_id: '',
  start_date: '',
  deadline: '',
  require_checkin: true,
  doc_mode: 'BEFORE_PROCESS_AFTER',
  status: 'ASSIGNED',
  notes: ''
});

async function loadInitialData() {
  if (!props.workOrderId) return;
  loadingData.value = true;
  error.value = '';

  try {
    const [vRes, aRes, jRes, uRes, woRes] = await Promise.all([
      api.getVendors(),
      api.getAreas(),
      api.getJobTypes(),
      api.getUsers({ role: 'FIELD_TEAM' }),
      api.getWorkOrderById(props.workOrderId)
    ]);

    vendors.value = vRes.data || [];
    areas.value = aRes.data || [];
    jobTypes.value = jRes.data || [];
    fieldUsers.value = uRes.data || [];

    const wo = woRes.data;
    if (wo) {
      formData.id = wo.id;
      formData.spk_number = wo.spk_number || '';
      formData.title = wo.title || '';
      formData.vendor_id = wo.vendor_id || (vendors.value[0]?.id || '');
      formData.area_id = wo.area_id || (areas.value[0]?.id || '');
      formData.job_type_id = wo.job_type_id || (jobTypes.value[0]?.id || '');
      formData.location_name = wo.location_name || '';
      formData.target_lat = wo.target_lat ? parseFloat(wo.target_lat) : null;
      formData.target_lng = wo.target_lng ? parseFloat(wo.target_lng) : null;
      formData.pic_user_id = wo.pic_user_id || '';
      formData.start_date = wo.start_date || '';
      formData.deadline = wo.deadline || '';
      formData.require_checkin = typeof wo.require_checkin === 'boolean' ? wo.require_checkin : true;
      formData.doc_mode = wo.doc_mode || 'BEFORE_PROCESS_AFTER';
      formData.status = wo.status || 'ASSIGNED';
      formData.notes = wo.notes || '';

      // Selected Members
      const members = (wo.assignments || []).filter(a => a.pivot?.role_in_team === 'MEMBER').map(a => a.id);
      selectedMemberIds.value = members;

      // Sub Items
      if (wo.items && wo.items.length > 0) {
        subItems.value = wo.items.map(i => ({
          id: i.id,
          item_name: i.item_name,
          doc_mode: i.doc_mode || 'BEFORE_PROCESS_AFTER',
          notes: i.notes
        }));
      } else {
        subItems.value = [
          { item_name: 'Pekerjaan Utama', doc_mode: 'BEFORE_PROCESS_AFTER' }
        ];
      }
    }
  } catch (err) {
    console.error('Failed to load SPK detail for edit:', err);
    error.value = 'Gagal memuat data SPK: ' + err.message;
  } finally {
    loadingData.value = false;
  }
}

watch(
  () => props.isOpen,
  (val) => {
    if (val && props.workOrderId) {
      loadInitialData();
    }
  },
  { immediate: true }
);

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

async function handleSubmit() {
  if (!formData.title || !formData.location_name) {
    error.value = 'Judul proyek dan alamat lokasi cabang wajib diisi.';
    return;
  }

  const validItems = subItems.value
    .filter(i => i.item_name && i.item_name.trim().length > 0)
    .map(i => ({
      id: i.id || null,
      item_name: i.item_name.trim(),
      doc_mode: i.doc_mode
    }));

  if (validItems.length === 0) {
    error.value = 'Minimal harus memiliki 1 rincian sub pekerjaan.';
    return;
  }

  submitting.value = true;
  error.value = '';

  try {
    const payload = {
      ...formData,
      member_ids: selectedMemberIds.value,
      items: validItems
    };

    const res = await api.updateWorkOrder(props.workOrderId, payload);
    alert(`Data & pengaturan SPK ${formData.spk_number} berhasil diperbarui!`);
    emit('updated', res.data);
    emit('close');
  } catch (err) {
    error.value = 'Gagal menyimpan perubahan SPK: ' + err.message;
  } finally {
    submitting.value = false;
  }
}
</script>
