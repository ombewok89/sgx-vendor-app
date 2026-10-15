<template>
  <div class="space-y-4 font-mono">
    <!-- Header (CAD Blueprint Style) -->
    <div class="bg-[#13171F] border border-[#232A36] p-4 rounded-xs flex flex-col md:flex-row md:items-center justify-between gap-3 relative overflow-hidden">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xs bg-[#0B0C0E] border border-hazard/40 flex items-center justify-center text-hazard shrink-0">
          <Building2 class="w-5 h-5" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-base sm:text-lg font-heading font-black text-white uppercase tracking-wider">
              Master Client & Branding Perusahaan
            </h2>
            <span class="bg-hazard/10 text-hazard border border-hazard/30 text-[9px] font-mono px-1.5 py-0.2 rounded-none font-bold uppercase">
              {{ vendors.length }} ENTITAS
            </span>
          </div>
          <p class="text-xs text-slate-400 font-mono mt-0.5">
            Kelola profil resmi, logo corporate, dan cover banner perusahaan klien (Indomaret, Alfamart, Perbankan, dll).
          </p>
        </div>
      </div>

      <button
        @click="openAddModal"
        class="cad-btn-primary px-4 py-2 text-xs font-bold flex items-center gap-2 cursor-pointer uppercase active:scale-95 transition-all self-start sm:self-auto"
      >
        <Plus class="w-4 h-4" />
        <span>Tambah Client Baru</span>
      </button>
    </div>

    <!-- Table Container (CAD High Density) -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs font-mono">
          <thead class="bg-[#0E1117] text-slate-400 font-bold border-b border-[#232A36] uppercase text-[10px] tracking-wider">
            <tr>
              <th class="py-3 px-4 w-14 text-center">Logo</th>
              <th class="py-3 px-4 w-28">Kode Client</th>
              <th class="py-3 px-4">Nama Perusahaan Client</th>
              <th class="py-3 px-4">PIC & Kontak</th>
              <th class="py-3 px-4">Legalitas (NPWP & Web)</th>
              <th class="py-3 px-4">Alamat Kantor</th>
              <th class="py-3 px-4 text-center w-40">Aksi & Branding</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1C2028] text-slate-300">
            <template v-if="loading">
              <tr>
                <td colspan="7" class="py-12 text-center text-slate-500 font-medium">
                  <div class="w-6 h-6 border-2 border-hazard border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                  <span>MEMUAT_DATA_MASTER_CLIENT...</span>
                </td>
              </tr>
            </template>
            <template v-else-if="vendors.length > 0">
              <tr v-for="item in vendors" :key="item.id" class="hover:bg-[#181D26] transition-colors">
                <!-- Logo Avatar -->
                <td class="py-3 px-4 text-center">
                  <div class="w-9 h-9 rounded-none bg-[#0B0C0E] border border-[#232A36] flex items-center justify-center overflow-hidden mx-auto shadow-xs">
                    <img
                      v-if="item.logo_url"
                      :src="getFileUrl(item.logo_url)"
                      :alt="item.name"
                      class="w-full h-full object-contain p-1"
                    />
                    <Building2 v-else class="w-4 h-4 text-slate-600" />
                  </div>
                </td>

                <td class="py-3 px-4 font-mono font-bold text-hazard text-xs">
                  <span class="px-1.5 py-0.5 rounded-none bg-[#0B0C0E] border border-[#232A36]">
                    {{ item.code }}
                  </span>
                </td>
                <td class="py-3 px-4">
                  <div class="font-heading font-black text-white uppercase text-xs">{{ item.name }}</div>
                  <div class="flex flex-wrap items-center gap-1.5 mt-1 font-mono text-[9px]">
                    <span v-if="item.banner_url" class="inline-flex items-center gap-1 text-emerald-400 bg-emerald-950 border border-emerald-500/40 px-1 py-0.2 rounded-none font-bold">
                      ✓ BANNER
                    </span>
                    <span v-if="item.ba_template_id" class="inline-flex items-center gap-1 text-hazard bg-hazard/10 border border-hazard/30 px-1 py-0.2 rounded-none font-bold">
                      <FileCode class="w-2.5 h-2.5" />
                      <span>{{ getTemplateName(item.ba_template_id) }}</span>
                    </span>
                  </div>
                </td>
                <td class="py-3 px-4">
                  <div class="font-bold text-slate-200">{{ item.contact_person || '-' }}</div>
                  <div class="font-mono text-[10px] text-slate-500">{{ item.phone || '-' }}</div>
                </td>
                <td class="py-3 px-4 font-mono text-[10px]">
                  <div class="text-slate-300 font-semibold">{{ item.npwp || 'NPWP: -' }}</div>
                  <div v-if="item.website" class="text-hazard text-[9px] truncate max-w-[130px]">
                    <a :href="item.website.startsWith('http') ? item.website : `https://${item.website}`" target="_blank" class="hover:underline">
                      {{ item.website }}
                    </a>
                  </div>
                </td>
                <td class="py-3 px-4 text-slate-400 max-w-xs truncate text-[11px]">{{ item.address || '-' }}</td>
                <td class="py-3 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <!-- Tombol Branding -->
                    <button
                      @click="openBrandingModal(item)"
                      title="Kelola Branding & Logo Klien"
                      class="px-2 py-1 rounded-none bg-[#0B0C0E] hover:bg-hazard hover:text-carbon text-hazard border border-[#232A36] font-bold text-[10px] uppercase transition-colors flex items-center gap-1 cursor-pointer active:scale-95"
                    >
                      <Sparkles class="w-3 h-3" />
                      <span>Branding</span>
                    </button>
                    <button
                      @click="openEditModal(item)"
                      title="Edit Data Client"
                      class="p-1 rounded-none bg-[#0B0C0E] hover:bg-[#181D26] hover:text-white text-slate-400 border border-[#232A36] transition-colors cursor-pointer"
                    >
                      <Pencil class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="handleDelete(item)"
                      title="Hapus Data Client"
                      class="p-1 rounded-none bg-[#0B0C0E] hover:bg-red-950/80 hover:text-red-400 text-slate-400 border border-[#232A36] transition-colors cursor-pointer"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="7" class="py-12 text-center text-slate-500 font-medium border border-dashed border-[#232A36]">
                  [ BELUM ADA DATA MASTER CLIENT ]
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form Tambah / Edit Data Master (CAD Style) -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 bg-[#0B0C0E]/80 backdrop-blur-xs flex items-center justify-center p-4 font-mono">
        <div class="bg-[#13171F] border border-hazard/40 rounded-xs max-w-md w-full shadow-2xl p-5 space-y-4 text-xs">
          <div class="flex items-center justify-between border-b border-[#232A36] pb-3">
            <h3 class="font-heading font-black text-sm text-white uppercase tracking-wider">
              {{ isEditing ? 'Edit' : 'Tambah' }} Master Client
            </h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-white p-1">
              <X class="w-4 h-4" />
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-3">
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Kode Client *</label>
              <input
                required
                type="text"
                placeholder="CONTOH: INDOMARCO, SMARTFREN"
                v-model="formInput.code"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard font-mono uppercase text-xs"
              />
            </div>
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Nama Perusahaan Client *</label>
              <input
                required
                type="text"
                placeholder="Contoh: PT INDOMARCO PRISMATAMA"
                v-model="formInput.name"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard text-xs"
              />
            </div>
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Contact Person (PIC Client)</label>
              <input
                type="text"
                placeholder="Nama PIC Client"
                v-model="formInput.contact_person"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard text-xs"
              />
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Telepon / WA</label>
                <input
                  type="text"
                  placeholder="0812xxxxxxxx"
                  v-model="formInput.phone"
                  class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard font-mono text-xs"
                />
              </div>
              <div>
                <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Email Client</label>
                <input
                  type="email"
                  placeholder="pic@client.com"
                  v-model="formInput.email"
                  class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard text-xs"
                />
              </div>
            </div>
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Alamat Kantor</label>
              <textarea
                rows="2"
                placeholder="Alamat lengkap kantor client"
                v-model="formInput.address"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard text-xs"
              />
            </div>
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider flex items-center justify-between">
                <span>Template Dokumen BA Opname</span>
                <span class="text-[9px] text-slate-500 font-normal">OPSIONAL</span>
              </label>
              <select
                v-model="formInput.ba_template_id"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard text-xs font-mono"
              >
                <option :value="null">-- Gunakan Template Default Sistem SGX --</option>
                <option v-for="tmpl in availableTemplates" :key="tmpl.id" :value="tmpl.id">
                  {{ tmpl.name }} ({{ tmpl.code }}) {{ tmpl.is_default ? '★ Default' : '' }}
                </option>
              </select>
            </div>

            <div class="pt-3 border-t border-[#232A36] flex items-center justify-end gap-2">
              <button
                type="button"
                @click="showModal = false"
                class="px-4 py-2 bg-[#0B0C0E] hover:bg-[#181D26] text-slate-300 border border-[#232A36] font-bold rounded-xs cursor-pointer uppercase"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="cad-btn-primary px-5 py-2 font-bold rounded-xs uppercase cursor-pointer active:scale-95 transition-all"
              >
                {{ saving ? 'MENYIMPAN...' : (isEditing ? 'SIMPAN PERUBAHAN' : 'SIMPAN DATA CLIENT') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Modal Khusus Branding & Logo Perusahaan (CAD Style) -->
    <Teleport to="body">
      <div v-if="showBrandingModal" class="fixed inset-0 z-50 bg-[#0B0C0E]/80 backdrop-blur-xs flex items-center justify-center p-4 font-mono">
        <div class="bg-[#13171F] border border-hazard/40 rounded-xs max-w-lg w-full shadow-2xl p-5 sm:p-6 space-y-4 text-xs max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between border-b border-[#232A36] pb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-none bg-[#0B0C0E] border border-hazard/40 text-hazard flex items-center justify-center font-bold">
                <Sparkles class="w-4 h-4" />
              </div>
              <div>
                <h3 class="font-heading font-black text-sm text-white uppercase tracking-wider">
                  Kelola Branding & Logo Klien
                </h3>
                <p class="text-[10px] text-slate-400">{{ selectedVendor?.name }}</p>
              </div>
            </div>
            <button @click="showBrandingModal = false" class="text-slate-400 hover:text-white p-1">
              <X class="w-4 h-4" />
            </button>
          </div>

          <form @submit.prevent="handleSaveBranding" class="space-y-3.5">
            <!-- 1. Upload Logo Resmi Perusahaan -->
            <div class="p-3.5 rounded-none bg-[#0B0C0E] border border-[#232A36] space-y-2">
              <div class="flex items-center justify-between">
                <label class="font-bold text-white block uppercase text-[10px]">Logo Resmi (PNG/WebP/SVG Transparan)</label>
                <span class="text-[9px] text-slate-500 font-mono">MAX 4MB</span>
              </div>
              
              <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-none bg-[#07080A] border border-[#232A36] flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                  <img
                    v-if="logoPreviewUrl"
                    :src="logoPreviewUrl"
                    alt="Logo Preview"
                    class="w-full h-full object-contain p-1"
                  />
                  <Building2 v-else class="w-5 h-5 text-slate-600" />
                </div>

                <div class="space-y-1 flex-1">
                  <input
                    type="file"
                    accept="image/png,image/jpeg,image/webp,image/svg+xml"
                    @change="handleLogoChange"
                    class="text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-none file:border-0 file:text-[10px] file:font-bold file:bg-hazard file:text-carbon hover:file:bg-yellow-400 cursor-pointer font-mono"
                  />
                  <p class="text-[9px] text-slate-500">Logo akan tampil di Berita Acara (BA), Live Tracker, dan Dashboard Klien.</p>
                </div>
              </div>
            </div>

            <!-- 2. Upload Hero Cover / Banner Perusahaan -->
            <div class="p-3.5 rounded-none bg-[#0B0C0E] border border-[#232A36] space-y-2">
              <div class="flex items-center justify-between">
                <label class="font-bold text-white block uppercase text-[10px]">Foto Cover / Banner Klien (16:9)</label>
                <span class="text-[9px] text-slate-500 font-mono">MAX 8MB</span>
              </div>

              <div class="space-y-2">
                <div class="h-24 w-full rounded-none bg-[#07080A] border border-[#232A36] flex items-center justify-center overflow-hidden shadow-xs relative">
                  <img
                    v-if="bannerPreviewUrl"
                    :src="bannerPreviewUrl"
                    alt="Banner Preview"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="text-center text-slate-600 flex flex-col items-center gap-1">
                    <ImageIcon class="w-5 h-5" />
                    <span class="text-[9px]">[ BELUM ADA COVER BANNER ]</span>
                  </div>
                </div>

                <input
                  type="file"
                  accept="image/png,image/jpeg,image/webp"
                  @change="handleBannerChange"
                  class="text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-none file:border-0 file:text-[10px] file:font-bold file:bg-hazard file:text-carbon hover:file:bg-yellow-400 cursor-pointer font-mono"
                />
              </div>
            </div>

            <!-- 3. Legalitas & Website -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <div>
                <label class="block font-bold text-slate-300 mb-1 uppercase text-[10px]">Nomor NPWP</label>
                <input
                  type="text"
                  placeholder="01.234.567.8-901.000"
                  v-model="brandingForm.npwp"
                  class="w-full px-3 py-1.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard font-mono text-xs"
                />
              </div>
              <div>
                <label class="block font-bold text-slate-300 mb-1 uppercase text-[10px]">Website Resmi</label>
                <input
                  type="text"
                  placeholder="https://company.co.id"
                  v-model="brandingForm.website"
                  class="w-full px-3 py-1.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard font-mono text-xs"
                />
              </div>
            </div>

            <!-- 4. Template Dokumen BA Opname -->
            <div class="p-3 bg-[#0B0C0E] border border-[#232A36] rounded-none space-y-1">
              <label class="block font-bold text-white text-xs flex items-center justify-between uppercase">
                <span class="flex items-center gap-1 text-hazard">
                  <FileCode class="w-3.5 h-3.5" />
                  <span>Template Dokumen BA Opname Klien</span>
                </span>
              </label>
              <select
                v-model="brandingForm.ba_template_id"
                class="w-full px-3 py-1.5 bg-[#13171F] border border-[#232A36] rounded-none focus:border-hazard text-xs font-mono text-white"
              >
                <option :value="null">-- Gunakan Template Default Sistem SGX --</option>
                <option v-for="tmpl in availableTemplates" :key="tmpl.id" :value="tmpl.id">
                  {{ tmpl.name }} ({{ tmpl.code }}) {{ tmpl.is_default ? '★ Default' : '' }}
                </option>
              </select>
            </div>

            <!-- Action Buttons -->
            <div class="pt-3 border-t border-[#232A36] flex items-center justify-end gap-2">
              <button
                type="button"
                @click="showBrandingModal = false"
                class="px-4 py-2 bg-[#0B0C0E] hover:bg-[#181D26] text-slate-300 border border-[#232A36] font-bold rounded-xs cursor-pointer uppercase"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="cad-btn-primary px-5 py-2 font-bold rounded-xs uppercase cursor-pointer flex items-center gap-1.5"
              >
                <Save class="w-4 h-4" />
                <span>{{ saving ? 'MENYIMPAN...' : 'SIMPAN BRANDING KLIEN' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
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
