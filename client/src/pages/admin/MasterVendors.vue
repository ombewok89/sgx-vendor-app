<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white/80 p-5 rounded-3xl border border-slate-200/80 shadow-xs">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-brand-900 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-brand-900/20">
          <Building2 class="w-6 h-6" />
        </div>
        <div>
          <h2 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
            <span>Master Client & Branding Perusahaan</span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-100 text-brand-900 font-mono">
              {{ vendors.length }} Entitas
            </span>
          </h2>
          <p class="text-xs text-slate-500 font-medium">Kelola profil resmi, logo corporate, dan cover banner perusahaan klien (Indomaret, Alfamart, Perbankan, dll).</p>
        </div>
      </div>

      <button
        @click="openAddModal"
        class="px-4 py-2.5 bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 hover:from-brand-800 hover:to-brand-600 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-md shadow-brand-900/20 active:scale-95 transition-all self-start sm:self-auto cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>Tambah Client Baru</span>
      </button>
    </div>

    <!-- Table Container -->
    <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100/70 text-slate-600 font-bold border-b border-slate-200/80">
            <tr>
              <th class="py-3.5 px-4 w-14 text-center">Logo</th>
              <th class="py-3.5 px-4 w-28">Kode Client</th>
              <th class="py-3.5 px-4">Nama Perusahaan Client</th>
              <th class="py-3.5 px-4">PIC & Kontak</th>
              <th class="py-3.5 px-4">Legalitas (NPWP & Web)</th>
              <th class="py-3.5 px-4">Alamat Kantor</th>
              <th class="py-3.5 px-4 text-center w-36">Aksi & Branding</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/80 text-slate-700">
            <template v-if="loading">
              <tr>
                <td colspan="7" class="py-12 text-center text-slate-400 font-medium">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <div class="w-6 h-6 border-2 border-brand-900 border-t-transparent rounded-full animate-spin"></div>
                    <span>Memuat data master client...</span>
                  </div>
                </td>
              </tr>
            </template>
            <template v-else-if="vendors.length > 0">
              <tr v-for="item in vendors" :key="item.id" class="hover:bg-brand-50/30 transition-colors">
                <!-- Logo Avatar -->
                <td class="py-3.5 px-4 text-center">
                  <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden mx-auto shadow-xs">
                    <img
                      v-if="item.logo_url"
                      :src="getFileUrl(item.logo_url)"
                      :alt="item.name"
                      class="w-full h-full object-contain p-1"
                    />
                    <Building2 v-else class="w-5 h-5 text-slate-400" />
                  </div>
                </td>

                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                  <span class="px-2 py-0.5 rounded-lg bg-slate-100 border border-slate-200 text-slate-800">
                    {{ item.code }}
                  </span>
                </td>
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900">{{ item.name }}</div>
                  <div class="flex flex-wrap items-center gap-1.5 mt-1">
                    <span v-if="item.banner_url" class="inline-flex items-center gap-1 text-[9px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded-md font-semibold">
                      ✓ Banner
                    </span>
                    <span v-if="item.ba_template_id" class="inline-flex items-center gap-1 text-[9px] text-purple-700 bg-purple-50 border border-purple-200 px-1.5 py-0.5 rounded-md font-semibold">
                      <FileCode class="w-2.5 h-2.5" />
                      <span>{{ getTemplateName(item.ba_template_id) }}</span>
                    </span>
                  </div>
                </td>
                <td class="py-3.5 px-4">
                  <div class="font-medium text-slate-800">{{ item.contact_person || '-' }}</div>
                  <div class="font-mono text-[11px] text-slate-500">{{ item.phone || '-' }}</div>
                </td>
                <td class="py-3.5 px-4 font-mono text-[11px]">
                  <div class="text-slate-700 font-semibold">{{ item.npwp || 'NPWP: -' }}</div>
                  <div v-if="item.website" class="text-brand-800 text-[10px] truncate max-w-[130px]">
                    <a :href="item.website.startsWith('http') ? item.website : `https://${item.website}`" target="_blank" class="hover:underline">
                      {{ item.website }}
                    </a>
                  </div>
                </td>
                <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">{{ item.address || '-' }}</td>
                <td class="py-3.5 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <!-- Tombol Bantuan Branding Superuser & Admin -->
                    <button
                      @click="openBrandingModal(item)"
                      title="Bantuan Kelola Branding & Logo Klien"
                      class="px-2.5 py-1 rounded-lg bg-purple-100 hover:bg-purple-200 text-purple-900 font-bold text-[11px] transition-colors flex items-center gap-1 cursor-pointer active:scale-95 shadow-xs"
                    >
                      <Sparkles class="w-3.5 h-3.5" />
                      <span>Branding</span>
                    </button>
                    <button
                      @click="openEditModal(item)"
                      title="Edit Data Client"
                      class="p-1.5 rounded-lg bg-slate-100 hover:bg-brand-100 hover:text-brand-900 text-slate-600 transition-colors active:scale-90 cursor-pointer"
                    >
                      <Pencil class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="handleDelete(item)"
                      title="Hapus Data Client"
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
                <td colspan="7" class="py-12 text-center text-slate-400 font-medium">Belum ada data client.</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form Tambah / Edit Data Master -->
    <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="glass-modal rounded-3xl max-w-md w-full shadow-2xl p-6 space-y-4 text-xs border border-white/80">
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5">
          <h3 class="font-black text-sm text-slate-900">
            {{ isEditing ? 'Edit' : 'Tambah' }} Master Client
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-3">
          <div>
            <label class="block font-bold mb-1">Kode Client *</label>
            <input
              required
              type="text"
              placeholder="Contoh: INDOMARCO, SMARTFREN"
              v-model="formInput.code"
              class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 font-mono uppercase"
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
          <div>
            <label class="block font-bold mb-1 flex items-center justify-between">
              <span class="flex items-center gap-1.5">
                <FileCode class="w-3.5 h-3.5 text-purple-700" />
                <span>Template Dokumen BA Opname Resmi</span>
              </span>
              <span class="text-[10px] text-slate-400 font-normal">Opsional</span>
            </label>
            <select
              v-model="formInput.ba_template_id"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 text-xs font-semibold text-slate-800"
            >
              <option :value="null">-- Gunakan Template Default Sistem SGX --</option>
              <option v-for="tmpl in availableTemplates" :key="tmpl.id" :value="tmpl.id">
                {{ tmpl.name }} ({{ tmpl.code }}) {{ tmpl.is_default ? '★ Default' : '' }}
              </option>
            </select>
            <p class="text-[10px] text-slate-500 mt-1">
              Template ini akan otomatis dipakai saat pencetakan Berita Acara (BA) untuk pekerjaan klien ini.
            </p>
          </div>

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
              :disabled="saving"
              class="px-5 py-2 bg-brand-900 hover:bg-brand-800 text-white font-bold rounded-xl shadow-xs active:scale-95 transition-all cursor-pointer"
            >
              {{ saving ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Simpan Data Client') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Khusus Branding & Logo Perusahaan (Akses Bantuan Superuser & Admin) -->
    <div v-if="showBrandingModal" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-md flex items-center justify-center p-4">
      <div class="glass-modal rounded-3xl max-w-lg w-full shadow-2xl p-6 sm:p-7 space-y-5 text-xs border border-white/80 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-900 flex items-center justify-center font-bold">
              <Sparkles class="w-4 h-4" />
            </div>
            <div>
              <h3 class="font-black text-sm text-slate-900">
                Kelola Branding & Logo Klien
              </h3>
              <p class="text-[11px] text-slate-500">{{ selectedVendor?.name }}</p>
            </div>
          </div>
          <button @click="showBrandingModal = false" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="handleSaveBranding" class="space-y-4">
          <!-- 1. Upload Logo Resmi Perusahaan -->
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
            <div class="flex items-center justify-between">
              <label class="font-bold text-slate-900 block">Logo Resmi Perusahaan (PNG/WebP/SVG Transparan)</label>
              <span class="text-[10px] text-slate-500 font-mono">Max 4MB</span>
            </div>
            
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 rounded-2xl bg-white border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                <img
                  v-if="logoPreviewUrl"
                  :src="logoPreviewUrl"
                  alt="Logo Preview"
                  class="w-full h-full object-contain p-1.5"
                />
                <Building2 v-else class="w-6 h-6 text-slate-400" />
              </div>

              <div class="space-y-1 flex-1">
                <input
                  type="file"
                  accept="image/png,image/jpeg,image/webp,image/svg+xml"
                  @change="handleLogoChange"
                  class="text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-900 file:text-white hover:file:bg-purple-800 cursor-pointer"
                />
                <p class="text-[10px] text-slate-500">Logo akan tampil di Berita Acara (BA), Live Tracker, dan Dashboard Klien.</p>
              </div>
            </div>
          </div>

          <!-- 2. Upload Hero Cover / Banner Perusahaan -->
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
            <div class="flex items-center justify-between">
              <label class="font-bold text-slate-900 block">Foto Cover / Banner Gedung Perusahaan (Panorama 16:9)</label>
              <span class="text-[10px] text-slate-500 font-mono">Max 8MB</span>
            </div>

            <div class="space-y-2">
              <div class="h-28 w-full rounded-2xl bg-white border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden shadow-xs relative">
                <img
                  v-if="bannerPreviewUrl"
                  :src="bannerPreviewUrl"
                  alt="Banner Preview"
                  class="w-full h-full object-cover"
                />
                <div v-else class="text-center text-slate-400 flex flex-col items-center gap-1">
                  <ImageIcon class="w-6 h-6" />
                  <span class="text-[10px]">Belum ada cover banner</span>
                </div>
              </div>

              <input
                type="file"
                accept="image/png,image/jpeg,image/webp"
                @change="handleBannerChange"
                class="text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-900 file:text-white hover:file:bg-purple-800 cursor-pointer"
              />
            </div>
          </div>

          <!-- 3. Legalitas & Website -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold mb-1">Nomor NPWP Perusahaan</label>
              <input
                type="text"
                placeholder="Contoh: 01.234.567.8-901.000"
                v-model="brandingForm.npwp"
                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 font-mono"
              />
            </div>
            <div>
              <label class="block font-bold mb-1">Website Resmi Perusahaan</label>
              <input
                type="text"
                placeholder="https://company.co.id"
                v-model="brandingForm.website"
                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 font-mono"
              />
            </div>
          </div>

          <!-- 4. Template Dokumen BA Opname Resmi Klien -->
          <div class="p-3.5 bg-purple-50/70 border border-purple-200 rounded-2xl space-y-1.5">
            <label class="block font-bold text-slate-900 text-xs flex items-center justify-between">
              <span class="flex items-center gap-1.5 text-purple-950">
                <FileCode class="w-4 h-4 text-purple-700" />
                <span>Template Dokumen BA Opname Khusus Klien</span>
              </span>
              <span class="text-[10px] text-purple-600 font-medium">Kustomisasi Dokumen</span>
            </label>
            <select
              v-model="brandingForm.ba_template_id"
              class="w-full px-3 py-2 bg-white border border-purple-300 rounded-xl focus:ring-2 focus:ring-purple-500 text-xs font-bold text-slate-800"
            >
              <option :value="null">-- Gunakan Template Default Sistem SGX --</option>
              <option v-for="tmpl in availableTemplates" :key="tmpl.id" :value="tmpl.id">
                {{ tmpl.name }} ({{ tmpl.code }}) {{ tmpl.is_default ? '★ Default' : '' }}
              </option>
            </select>
            <p class="text-[10px] text-slate-600">
              Dokumen BA Opname untuk pekerjaan klien ini akan otomatis menggunakan kertas kop surat, logo, dan format klausul dari template yang dipilih.
            </p>
          </div>

          <!-- Action Buttons -->
          <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="showBrandingModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-5 py-2 bg-gradient-to-r from-purple-900 to-indigo-800 text-white font-bold rounded-xl shadow-md active:scale-95 transition-all cursor-pointer flex items-center gap-1.5"
            >
              <Save class="w-4 h-4" />
              <span>{{ saving ? 'Menyimpan...' : 'Simpan Branding Klien' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api, getFileUrl } from '../../services/api';
import { Building2, Plus, Pencil, Trash2, X, Sparkles, Image as ImageIcon, Save, FileCode } from 'lucide-vue-next';

const vendors = ref([]);
const availableTemplates = ref([]);
const loading = ref(true);
const saving = ref(false);

const showModal = ref(false);
const isEditing = ref(false);
const formInput = ref({});

const showBrandingModal = ref(false);
const selectedVendor = ref(null);
const brandingForm = ref({});
const logoFile = ref(null);
const bannerFile = ref(null);
const logoPreviewUrl = ref('');
const bannerPreviewUrl = ref('');

async function fetchVendors() {
  loading.value = true;
  try {
    const [resVendors, resTemplates] = await Promise.all([
      api.getVendors(),
      api.getTemplates().catch(() => ({ data: [] }))
    ]);
    if (resVendors.success && resVendors.data) {
      vendors.value = resVendors.data;
    }
    if (resTemplates.data) {
      availableTemplates.value = resTemplates.data;
    }
  } catch (err) {
    console.error('Failed to load vendors', err);
  } finally {
    loading.value = false;
  }
}

function getTemplateName(id) {
  if (!id) return '';
  const tmpl = availableTemplates.value.find(t => t.id === Number(id));
  return tmpl ? tmpl.name : `Template #${id}`;
}

function openAddModal() {
  isEditing.value = false;
  formInput.value = { code: '', name: '', contact_person: '', phone: '', email: '', address: '', ba_template_id: null };
  showModal.value = true;
}

function openEditModal(item) {
  isEditing.value = true;
  formInput.value = { ...item, ba_template_id: item.ba_template_id || null };
  showModal.value = true;
}

function openBrandingModal(item) {
  selectedVendor.value = item;
  brandingForm.value = {
    npwp: item.npwp || '',
    website: item.website || '',
    ba_template_id: item.ba_template_id || null,
  };
  logoFile.value = null;
  bannerFile.value = null;
  logoPreviewUrl.value = item.logo_url ? getFileUrl(item.logo_url) : '';
  bannerPreviewUrl.value = item.banner_url ? getFileUrl(item.banner_url) : '';
  showBrandingModal.value = true;
}

function handleLogoChange(e) {
  const file = e.target.files[0];
  if (file) {
    logoFile.value = file;
    logoPreviewUrl.value = URL.createObjectURL(file);
  }
}

function handleBannerChange(e) {
  const file = e.target.files[0];
  if (file) {
    bannerFile.value = file;
    bannerPreviewUrl.value = URL.createObjectURL(file);
  }
}

async function handleSaveBranding() {
  if (!selectedVendor.value) return;
  saving.value = true;
  try {
    const fd = new FormData();
    if (logoFile.value) fd.append('logo', logoFile.value);
    if (bannerFile.value) fd.append('banner', bannerFile.value);
    if (brandingForm.value.npwp !== undefined) fd.append('npwp', brandingForm.value.npwp);
    if (brandingForm.value.website !== undefined) fd.append('website', brandingForm.value.website);
    if (brandingForm.value.ba_template_id !== undefined) {
      fd.append('ba_template_id', brandingForm.value.ba_template_id ? String(brandingForm.value.ba_template_id) : '');
    }

    const res = await api.updateVendorBranding(selectedVendor.value.id, fd);
    if (res.success) {
      alert('Branding dan logo perusahaan klien berhasil disimpan!');
      showBrandingModal.value = false;
      await fetchVendors();
    } else {
      alert(res.message || 'Gagal menyimpan branding klien.');
    }
  } catch (err) {
    alert(err.message || 'Terjadi kesalahan saat mengunggah branding.');
  } finally {
    saving.value = false;
  }
}

async function handleSubmit() {
  saving.value = true;
  try {
    if (isEditing.value) {
      await api.updateVendor(formInput.value.id, formInput.value);
    } else {
      await api.createVendor(formInput.value);
    }
    showModal.value = false;
    await fetchVendors();
  } catch (err) {
    alert(err.message || 'Gagal menyimpan data vendor.');
  } finally {
    saving.value = false;
  }
}

async function handleDelete(item) {
  if (!confirm(`Hapus master client ${item.name}?`)) return;
  try {
    await api.deleteVendor(item.id);
    await fetchVendors();
  } catch (err) {
    alert(err.message || 'Gagal menghapus client.');
  }
}

onMounted(() => {
  fetchVendors();
});
</script>
