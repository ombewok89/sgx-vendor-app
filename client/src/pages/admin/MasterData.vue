<template>
  <div class="space-y-5">
    <!-- Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-brand-900 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-brand-900/20">
            <Building2 class="w-4 h-4" />
          </div>
          <span>Master Data Management</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1 font-medium">Kelola entitas inti: Perusahaan Client (Indomarco, Smartfren, dll), Tim Lapangan, Area Operasional, dan Jenis Pekerjaan.</p>
      </div>
      <button
        @click="openAddModal"
        class="px-4 py-2 bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 hover:from-brand-800 hover:to-brand-600 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-md shadow-brand-900/20 active:scale-95 transition-all self-start sm:self-auto cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>Tambah {{ currentSectionLabel }}</span>
      </button>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 border-b border-slate-200/80 pb-2.5 overflow-x-auto text-xs font-bold">
      <button
        @click="section = 'vendors'"
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer',
          section === 'vendors' ? 'bg-brand-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100/80'
        ]"
      >
        <Building2 class="w-4 h-4" />
        <span>Client ({{ section === 'vendors' ? data.length : '' }})</span>
      </button>

      <button
        @click="section = 'teams'"
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer',
          section === 'teams' ? 'bg-brand-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100/80'
        ]"
      >
        <Users class="w-4 h-4" />
        <span>Tim Lapangan ({{ section === 'teams' ? data.length : '' }})</span>
      </button>

      <button
        @click="section = 'areas'"
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer',
          section === 'areas' ? 'bg-brand-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100/80'
        ]"
      >
        <MapPin class="w-4 h-4" />
        <span>Area ({{ section === 'areas' ? data.length : '' }})</span>
      </button>

      <button
        @click="section = 'jobtypes'"
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer',
          section === 'jobtypes' ? 'bg-brand-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100/80'
        ]"
      >
        <Briefcase class="w-4 h-4" />
        <span>Jenis Pekerjaan ({{ section === 'jobtypes' ? data.length : '' }})</span>
      </button>
    </div>

    <!-- Table Display (Glassmorphic Container) -->
    <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100/70 text-slate-500 font-bold border-b border-slate-200/80">
            <tr v-if="section === 'vendors'">
              <th class="py-3 px-4">Kode Client</th>
              <th class="py-3 px-4">Nama Perusahaan Client</th>
              <th class="py-3 px-4">PIC / Kontak Client</th>
              <th class="py-3 px-4">Telepon & Email</th>
              <th class="py-3 px-4">Alamat Kantor</th>
              <th class="py-3 px-4 text-center w-28">Aksi</th>
            </tr>
            <tr v-else-if="section === 'teams'">
              <th class="py-3 px-4">Nama Tim</th>
              <th class="py-3 px-4">Ketua Tim / PIC</th>
              <th class="py-3 px-4">Area Penugasan</th>
              <th class="py-3 px-4">Anggota</th>
              <th class="py-3 px-4 text-center w-28">Aksi</th>
            </tr>
            <tr v-else-if="section === 'areas'">
              <th class="py-3 px-4">Nama Area</th>
              <th class="py-3 px-4">Kota / Kabupaten</th>
              <th class="py-3 px-4">Provinsi</th>
              <th class="py-3 px-4">Kecamatan</th>
              <th class="py-3 px-4 text-center w-28">Aksi</th>
            </tr>
            <tr v-else-if="section === 'jobtypes'">
              <th class="py-3 px-4">Kode</th>
              <th class="py-3 px-4">Nama Pekerjaan</th>
              <th class="py-3 px-4">Tarif Acuan Standar (Rp)</th>
              <th class="py-3 px-4">Mode Dokumentasi</th>
              <th class="py-3 px-4">Min. Foto</th>
              <th class="py-3 px-4 text-center w-28">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/80 text-slate-700">
            <template v-if="loading">
              <tr>
                <td colspan="6" class="py-10 text-center text-slate-400 font-medium">Memuat data master...</td>
              </tr>
            </template>
            <template v-else-if="data.length > 0">
              <tr v-for="item in data" :key="item.id" class="hover:bg-brand-50/30 transition-colors group">
                <template v-if="section === 'vendors'">
                  <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ item.code }}</td>
                  <td class="py-3.5 px-4 font-bold text-slate-900">{{ item.name }}</td>
                  <td class="py-3.5 px-4">{{ item.contact_person || '-' }}</td>
                  <td class="py-3.5 px-4 font-mono text-[11px]">{{ item.phone }} • {{ item.email }}</td>
                  <td class="py-3.5 px-4 text-slate-500">{{ item.address || '-' }}</td>
                </template>
                <template v-else-if="section === 'teams'">
                  <td class="py-3.5 px-4 font-bold text-slate-900">{{ item.name }}</td>
                  <td class="py-3.5 px-4 font-semibold text-slate-800">{{ item.leader_name }} ({{ item.leader_phone }})</td>
                  <td class="py-3.5 px-4 text-slate-600">{{ item.area_name || '-' }}</td>
                  <td class="py-3.5 px-4">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="m in item.members" :key="m.id" class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-md text-[10px] font-semibold">
                        {{ m.name }}
                      </span>
                    </div>
                  </td>
                </template>
                <template v-else-if="section === 'areas'">
                  <td class="py-3.5 px-4 font-bold text-slate-900">{{ item.name }}</td>
                  <td class="py-3.5 px-4">{{ item.city || '-' }}</td>
                  <td class="py-3.5 px-4">{{ item.province || '-' }}</td>
                  <td class="py-3.5 px-4 text-slate-500">{{ item.district || '-' }}</td>
                </template>
                <template v-else-if="section === 'jobtypes'">
                  <td class="py-3.5 px-4 font-mono font-bold text-slate-900">{{ item.code }}</td>
                  <td class="py-3.5 px-4 font-bold text-slate-900">{{ item.name }}</td>
                  <td class="py-3.5 px-4 font-mono font-bold text-emerald-700">Rp {{ Number(item.standard_price || 0).toLocaleString('id-ID') }}</td>
                  <td class="py-3.5 px-4">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-700 border border-brand-200">
                      {{ item.doc_mode }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 font-bold text-slate-800">{{ item.min_photos_per_stage }} Foto</td>
                </template>

                <!-- Actions Column -->
                <td class="py-3.5 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      @click="openEditModal(item)"
                      title="Edit Data"
                      class="p-1.5 rounded-lg bg-slate-100 hover:bg-purple-100 hover:text-purple-800 text-slate-600 transition-colors active:scale-90 cursor-pointer"
                    >
                      <Pencil class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="handleDelete(item)"
                      title="Hapus Data"
                      class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-100 hover:text-rose-700 text-slate-600 transition-colors active:scale-90 cursor-pointer"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="6" class="py-10 text-center text-slate-400 font-medium">Belum ada data.</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add / Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="glass-modal rounded-3xl max-w-md w-full shadow-2xl p-6 space-y-4 text-xs border border-white/80">
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5">
          <h3 class="font-black text-sm text-slate-900">
            {{ isEditing ? 'Edit' : 'Tambah' }} {{ currentSectionLabel }}
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-3">
          <!-- Client Form -->
          <template v-if="section === 'vendors'">
            <div>
              <label class="block font-bold mb-1">Kode Client *</label>
              <input
                required
                type="text"
                placeholder="Contoh: INDOMARCO, SMARTFREN"
                v-model="formInput.code"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 font-mono"
              />
            </div>
            <div>
              <label class="block font-bold mb-1">Nama Perusahaan Client *</label>
              <input
                required
                type="text"
                placeholder="Contoh: PT INDOMARCO PRISMATAMA"
                v-model="formInput.name"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
              />
            </div>
            <div>
              <label class="block font-bold mb-1">Contact Person (PIC Client)</label>
              <input
                type="text"
                placeholder="Nama PIC Client"
                v-model="formInput.contact_person"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
              />
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block font-bold mb-1">Telepon / WhatsApp</label>
                <input
                  type="text"
                  placeholder="0812xxxxxxxx"
                  v-model="formInput.phone"
                  class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 font-mono"
                />
              </div>
              <div>
                <label class="block font-bold mb-1">Email Client</label>
                <input
                  type="email"
                  placeholder="pic@client-company.com"
                  v-model="formInput.email"
                  class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
                />
              </div>
            </div>
            <div>
              <label class="block font-bold mb-1">Alamat Kantor Client</label>
              <textarea
                rows="2"
                placeholder="Alamat lengkap kantor client"
                v-model="formInput.address"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
              />
            </div>
          </template>

          <!-- Field Team Form -->
          <template v-else-if="section === 'teams'">
            <div>
              <label class="block font-bold mb-1">Nama Tim Lapangan *</label>
              <input
                required
                type="text"
                placeholder="Contoh: Tim Alpha - Bandung Raya"
                v-model="formInput.name"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
              />
            </div>
            <div>
              <label class="block font-bold mb-1">Ketua Tim / PIC Utama</label>
              <select
                v-model="formInput.leader_user_id"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
              >
                <option value="">-- Pilih PIC Lapangan --</option>
                <option v-for="u in fieldUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.phone }})</option>
              </select>
            </div>
            <div>
              <label class="block font-bold mb-1">Area Operasional Penugasan</label>
              <select
                v-model="formInput.area_id"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
              >
                <option value="">-- Pilih Area --</option>
                <option v-for="a in areasList" :key="a.id" :value="a.id">{{ a.name }} ({{ a.city }})</option>
              </select>
            </div>
          </template>

          <!-- Area Form -->
          <template v-else-if="section === 'areas'">
            <div>
              <label class="block font-bold mb-1">Nama Wilayah Area *</label>
              <input
                required
                type="text"
                placeholder="Contoh: Bandung Raya"
                v-model="formInput.name"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
              />
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block font-bold mb-1">Kota / Kabupaten</label>
                <input
                  type="text"
                  placeholder="Bandung"
                  v-model="formInput.city"
                  class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
                />
              </div>
              <div>
                <label class="block font-bold mb-1">Provinsi</label>
                <input
                  type="text"
                  placeholder="Jawa Barat"
                  v-model="formInput.province"
                  class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
                />
              </div>
            </div>
            <div>
              <label class="block font-bold mb-1">Kecamatan / District</label>
              <input
                type="text"
                placeholder="Contoh: Coblong, Sukajadi, dll"
                v-model="formInput.district"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
              />
            </div>
          </template>

          <!-- Job Types Form -->
          <template v-else-if="section === 'jobtypes'">
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block font-bold mb-1">Kode Pekerjaan *</label>
                <input
                  required
                  type="text"
                  placeholder="SIGNBOARD"
                  v-model="formInput.code"
                  class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 font-mono"
                />
              </div>
              <div>
                <label class="block font-bold mb-1">Mode Evidence *</label>
                <select
                  v-model="formInput.doc_mode"
                  class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-xs font-bold"
                >
                  <option value="BEFORE_PROCESS_AFTER">BEFORE + PROCESS + AFTER</option>
                  <option value="AFTER_ONLY">AFTER ONLY</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block font-bold mb-1">Nama Jenis Pekerjaan *</label>
              <input
                required
                type="text"
                placeholder="Contoh: Pemasangan Signboard & Reklame"
                v-model="formInput.name"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
              />
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block font-bold mb-1">Tarif Acuan Standar (Rp)</label>
                <input
                  type="number"
                  placeholder="15000000"
                  v-model="formInput.standard_price"
                  class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 font-mono"
                />
              </div>
              <div>
                <label class="block font-bold mb-1">Min. Foto Tiap Tahap</label>
                <input
                  type="number"
                  min="1"
                  max="10"
                  v-model="formInput.min_photos_per_stage"
                  class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 font-mono"
                />
              </div>
            </div>
            <div v-if="formInput.standard_price" class="text-[11px] font-mono font-bold text-emerald-700">
              Preview Tarif: Rp {{ Number(formInput.standard_price).toLocaleString('id-ID') }}
            </div>
          </template>

          <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-5 py-2 bg-brand-900 text-white font-bold rounded-xl shadow-xs active:scale-95 transition-all cursor-pointer"
            >
              {{ isEditing ? 'Simpan Perubahan' : 'Simpan Data' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { api } from '../../services/api';
import { Building2, MapPin, Briefcase, Users, Plus, Pencil, Trash2, X } from 'lucide-vue-next';

const props = defineProps({
  activeSection: {
    type: String,
    default: 'vendors'
  }
});

const section = ref(props.activeSection);
const data = ref([]);
const loading = ref(true);

const showModal = ref(false);
const isEditing = ref(false);
const formInput = ref({});

const fieldUsers = ref([]);
const areasList = ref([]);

const currentSectionLabel = computed(() => {
  switch (section.value) {
    case 'vendors': return 'Client / Pemberi Tugas';
    case 'teams': return 'Tim Lapangan';
    case 'areas': return 'Area Operasional';
    case 'jobtypes': return 'Jenis Pekerjaan';
    default: return 'Data';
  }
});

async function loadSectionData(targetSection) {
  loading.value = true;
  try {
    if (targetSection === 'vendors') {
      const res = await api.getVendors();
      data.value = res.data || [];
    } else if (targetSection === 'areas') {
      const res = await api.getAreas();
      data.value = res.data || [];
    } else if (targetSection === 'jobtypes') {
      const res = await api.getJobTypes();
      data.value = res.data || [];
    } else if (targetSection === 'teams') {
      const [tRes, uRes, aRes] = await Promise.all([
        api.getFieldTeams(),
        api.getUsers({ role: 'FIELD_TEAM' }),
        api.getAreas()
      ]);
      data.value = tRes.data || [];
      fieldUsers.value = uRes.data || [];
      areasList.value = aRes.data || [];
    }
  } catch (err) {
    console.error('Failed to load master data:', err);
  } finally {
    loading.value = false;
  }
}

watch(section, (newSec) => {
  loadSectionData(newSec);
});

function openAddModal() {
  isEditing.value = false;
  formInput.value = {
    doc_mode: 'BEFORE_PROCESS_AFTER',
    min_photos_per_stage: 3,
    standard_price: 15000000
  };
  showModal.value = true;
}

function openEditModal(item) {
  isEditing.value = true;
  formInput.value = { ...item };
  showModal.value = true;
}

async function handleSubmit() {
  try {
    if (isEditing.value) {
      // UPDATE
      if (section.value === 'vendors') {
        await api.updateVendor(formInput.value.id, formInput.value);
      } else if (section.value === 'areas') {
        await api.updateArea(formInput.value.id, formInput.value);
      } else if (section.value === 'jobtypes') {
        await api.updateJobType(formInput.value.id, formInput.value);
      } else if (section.value === 'teams') {
        await api.updateFieldTeam(formInput.value.id, formInput.value);
      }
      alert('Perubahan data berhasil disimpan!');
    } else {
      // CREATE
      if (section.value === 'vendors') {
        await api.createVendor(formInput.value);
      } else if (section.value === 'areas') {
        await api.createArea(formInput.value);
      } else if (section.value === 'jobtypes') {
        await api.createJobType(formInput.value);
      } else if (section.value === 'teams') {
        await api.createFieldTeam(formInput.value);
      }
      alert('Data master baru berhasil ditambahkan!');
    }

    showModal.value = false;
    formInput.value = {};
    loadSectionData(section.value);
  } catch (err) {
    alert(`Gagal menyimpan data: ${err.message}`);
  }
}

async function handleDelete(item) {
  const confirmed = window.confirm(`Apakah Anda yakin ingin menghapus data "${item.name}"?`);
  if (!confirmed) return;

  try {
    if (section.value === 'vendors') {
      await api.deleteVendor(item.id);
    } else if (section.value === 'areas') {
      await api.deleteArea(item.id);
    } else if (section.value === 'jobtypes') {
      await api.deleteJobType(item.id);
    } else if (section.value === 'teams') {
      await api.deleteFieldTeam(item.id);
    }
    alert('Data berhasil dihapus!');
    loadSectionData(section.value);
  } catch (err) {
    alert(`Gagal menghapus data: ${err.message}`);
  }
}

onMounted(() => {
  loadSectionData(section.value);
});
</script>
