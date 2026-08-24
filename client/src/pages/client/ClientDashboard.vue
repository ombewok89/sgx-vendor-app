<template>
  <div class="space-y-6">
    <!-- Corporate Hero Branding Banner -->
    <div class="relative rounded-3xl overflow-hidden shadow-2xl border border-slate-200/80 bg-slate-900 text-white">
      <!-- Background Cover Banner -->
      <div class="absolute inset-0 z-0">
        <img
          v-if="clientCompany?.banner_url"
          :src="getFileUrl(clientCompany.banner_url)"
          alt="Company Cover Banner"
          class="w-full h-full object-cover"
        />
        <div v-else class="w-full h-full bg-gradient-to-r from-purple-950 via-slate-900 to-indigo-950"></div>
        <!-- Frosted Dark Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-slate-950/40 backdrop-blur-[2px]"></div>
      </div>

      <!-- Hero Content -->
      <div class="relative z-10 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
          <!-- Corporate Logo Frame -->
          <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-white/95 p-2 shadow-2xl border-2 border-white/80 flex items-center justify-center shrink-0 overflow-hidden backdrop-blur-md">
            <img
              v-if="clientCompany?.logo_url"
              :src="getFileUrl(clientCompany.logo_url)"
              :alt="clientCompany?.name || 'Client Logo'"
              class="w-full h-full object-contain"
            />
            <div v-else class="w-full h-full rounded-2xl bg-purple-900 text-white flex flex-col items-center justify-center font-black">
              <Building2 class="w-8 h-8 text-[#EDC80A]" />
              <span class="text-[9px] mt-1 font-mono uppercase">LOGO</span>
            </div>
          </div>

          <!-- Corporate Info -->
          <div class="space-y-1.5">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-[#EDC80A] text-[#1E1E1D] shadow-xs">
                MITRA KORPORAT RESMI
              </span>
              <span v-if="clientCompany?.code" class="text-[10px] font-mono text-slate-300 bg-slate-800/80 px-2 py-0.5 rounded-md border border-slate-700">
                ID: {{ clientCompany.code }}
              </span>
              <span class="text-[10px] text-emerald-400 font-bold flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Portal Terverifikasi
              </span>
            </div>

            <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
              {{ clientCompany?.name || 'Klien / Principal Mitra SGX' }}
            </h2>

            <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-300 font-medium">
              <span v-if="clientCompany?.contact_person" class="flex items-center gap-1.5">
                <User class="w-3.5 h-3.5 text-[#EDC80A]" />
                <span>PIC: {{ clientCompany.contact_person }}</span>
              </span>
              <span v-if="clientCompany?.npwp" class="flex items-center gap-1.5 font-mono">
                <span class="text-slate-400">NPWP:</span>
                <span>{{ clientCompany.npwp }}</span>
              </span>
              <span v-if="clientCompany?.website" class="flex items-center gap-1.5">
                <Globe class="w-3.5 h-3.5 text-blue-400" />
                <a :href="clientCompany.website.startsWith('http') ? clientCompany.website : `https://${clientCompany.website}`" target="_blank" class="hover:underline text-blue-300">
                  {{ clientCompany.website }}
                </a>
              </span>
            </div>

            <p v-if="clientCompany?.address" class="text-[11px] text-slate-400 flex items-center gap-1.5 max-w-xl">
              <MapPin class="w-3.5 h-3.5 text-rose-400 shrink-0" />
              <span class="truncate">{{ clientCompany.address }}</span>
            </p>
          </div>
        </div>

        <!-- Right Quick Action Buttons -->
        <div class="flex items-center gap-2.5 self-start md:self-auto flex-wrap">
          <button
            @click="openBrandingModal"
            class="px-4 py-2.5 bg-white/15 hover:bg-white/25 text-white border border-white/30 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-lg backdrop-blur-md transition-all active:scale-95 cursor-pointer"
          >
            <Sparkles class="w-4 h-4 text-[#EDC80A]" />
            <span>Kelola Logo & Branding</span>
          </button>

          <button
            @click="loadClientData"
            class="p-2.5 bg-white/10 hover:bg-white/20 text-white rounded-2xl border border-white/20 shadow-xs transition-all cursor-pointer"
            title="Muat Ulang Data Proyek"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
          </button>
        </div>
      </div>
    </div>

    <!-- Executive Scorecards (Store/Branch Progress) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Total Stores/Projects -->
      <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-purple-100 text-purple-900 flex items-center justify-center shrink-0">
          <Store class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total Toko / Cabang</span>
          <div class="text-base font-black text-slate-900 font-mono truncate">
            {{ workOrders.length }} Cabang
          </div>
          <span class="text-[10px] text-purple-800 font-semibold">
            Dikerjakan oleh Kontraktor SGX
          </span>
        </div>
      </div>

      <!-- Completed 100% -->
      <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
          <CheckCircle2 class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Selesai 100% & Terbit BA</span>
          <div class="text-base font-black text-slate-900 font-mono truncate">
            {{ completedOrders.length }} Cabang
          </div>
          <span class="text-[10px] text-emerald-700 font-semibold">
            {{ completionRate }}% dari total portofolio
          </span>
        </div>
      </div>

      <!-- In Progress Active -->
      <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-900 flex items-center justify-center shrink-0">
          <Clock class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Sedang Dikerjakan</span>
          <div class="text-base font-black text-slate-900 font-mono truncate">
            {{ inProgressOrders.length }} Cabang
          </div>
          <span class="text-[10px] text-indigo-700 font-semibold">
            Tim teknisi sedang di lapangan
          </span>
        </div>
      </div>

      <!-- Field Issues & Mitigation -->
      <div class="glass-card rounded-2xl p-4 border border-white/80 shadow-glass flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-900 flex items-center justify-center shrink-0">
          <AlertTriangle class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Kendala Lapangan</span>
          <div class="text-base font-black text-slate-900 font-mono truncate">
            {{ openIssuesCount > 0 ? `${openIssuesCount} Butuh Solusi` : '0 Kendala Aktif' }}
          </div>
          <span class="text-[10px] text-amber-800 font-semibold">
            Izin security / cuaca / kelistrikan
          </span>
        </div>
      </div>
    </div>

    <!-- Main Section: Store Tasks & Evidence Feed -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left 2 Cols: Active Work Orders List -->
      <div class="lg:col-span-2 space-y-4">
        <div class="glass-card rounded-3xl p-5 border border-white/80 shadow-glass space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
              <h3 class="font-black text-sm text-slate-900 tracking-tight flex items-center gap-2">
                <Store class="w-4 h-4 text-purple-900" />
                <span>Daftar Proyek Cabang & Toko</span>
              </h3>
              <p class="text-[11px] text-slate-400 mt-0.5">Pemantauan progres per lokasi toko secara real-time.</p>
            </div>
            <button
              @click="$emit('switch-tab', 'client_tasks')"
              class="text-xs font-bold text-purple-900 hover:text-purple-700 flex items-center gap-1 cursor-pointer"
            >
              <span>Lihat Semua SPK</span>
              <ChevronRight class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Work Order Rows -->
          <div v-if="workOrders.length > 0" class="space-y-2.5">
            <div
              v-for="wo in workOrders.slice(0, 5)"
              :key="wo.id"
              class="p-3.5 rounded-2xl bg-white border border-slate-100 hover:border-purple-200 transition-all flex items-center justify-between gap-3 shadow-xs"
            >
              <div class="min-w-0 space-y-0.5">
                <div class="flex items-center gap-2">
                  <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-700">
                    {{ wo.spk_number }}
                  </span>
                  <span class="text-xs font-bold text-slate-900 truncate">{{ wo.location_name }}</span>
                </div>
                <div class="text-[11px] text-slate-500 flex items-center gap-3">
                  <span>{{ wo.title }}</span>
                  <span>•</span>
                  <span class="font-mono text-purple-900 font-bold">Progres: {{ wo.progress_percent || 0 }}%</span>
                </div>
              </div>

              <div class="flex items-center gap-2 shrink-0">
                <StatusBadge :status="wo.status" />
                <button
                  @click="$emit('switch-tab', 'client_tasks')"
                  class="p-1.5 rounded-lg bg-slate-50 hover:bg-purple-100 text-slate-500 hover:text-purple-900 transition-colors cursor-pointer"
                  title="Buka Detail SPK"
                >
                  <Eye class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
          <div v-else class="py-8 text-center text-slate-400 text-xs font-medium">
            Belum ada SPK yang terdaftar untuk perusahaan Anda.
          </div>
        </div>
      </div>

      <!-- Right 1 Col: Recent Visual Evidence & BA Download -->
      <div class="space-y-4">
        <!-- Live Evidence Preview -->
        <div class="glass-card rounded-3xl p-5 border border-white/80 shadow-glass space-y-3">
          <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
            <h3 class="font-black text-xs uppercase tracking-wider text-slate-800 flex items-center gap-2">
              <Camera class="w-4 h-4 text-purple-900" />
              <span>Evidensi Foto Terbaru</span>
            </h3>
            <span class="text-[10px] font-mono text-slate-400">GPS Satelit</span>
          </div>

          <div v-if="recentPhotos.length > 0" class="grid grid-cols-2 gap-2">
            <div
              v-for="p in recentPhotos.slice(0, 4)"
              :key="p.id"
              class="h-24 rounded-xl overflow-hidden bg-slate-100 relative group cursor-pointer border border-slate-200"
            >
              <img :src="getFileUrl(p.file_path)" alt="Evidence" class="w-full h-full object-cover group-hover:scale-105 transition-all" />
              <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-1.5 text-[8px] text-white font-mono flex items-center justify-between">
                <span class="font-bold uppercase text-[#EDC80A]">{{ p.stage }}</span>
                <span class="truncate max-w-[60px]">📍 Valid</span>
              </div>
            </div>
          </div>
          <div v-else class="py-6 text-center text-slate-400 text-xs">
            Belum ada foto evidensi masuk.
          </div>

          <button
            @click="$emit('switch-tab', 'client_tasks')"
            class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-all cursor-pointer"
          >
            <Eye class="w-3.5 h-3.5" />
            <span>Buka Galeri Before-After Lengkap</span>
          </button>
        </div>

        <!-- BA Opname Ready Card -->
        <div class="glass-card rounded-3xl p-5 border border-white/80 shadow-glass space-y-3 bg-gradient-to-br from-emerald-50/70 to-teal-50/40">
          <div class="flex items-center gap-2 text-emerald-900 font-bold text-xs">
            <FileCheck2 class="w-4 h-4 text-emerald-700" />
            <span>Dokumen Berita Acara (BA Opname)</span>
          </div>
          <p class="text-[11px] text-slate-600 leading-relaxed">
            Terdapat <strong>{{ completedOrders.length }} dokumen Berita Acara</strong> yang telah diverifikasi dan siap diunduh untuk kelengkapan administrasi & penagihan.
          </p>
          <button
            @click="$emit('switch-tab', 'client_ba')"
            class="w-full py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 shadow-xs active:scale-95 transition-all cursor-pointer"
          >
            <FileSpreadsheet class="w-3.5 h-3.5" />
            <span>Akses Pusat Dokumen BA Opname</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Mandiri Klien: Kelola Profil & Branding Perusahaan -->
    <div v-if="showBrandingModal" class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-md flex items-center justify-center p-4">
      <div class="glass-modal rounded-3xl max-w-lg w-full shadow-2xl p-6 sm:p-7 space-y-5 text-xs border border-white/80 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-2xl bg-purple-100 text-purple-900 flex items-center justify-center font-bold">
              <Sparkles class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-black text-sm text-slate-900">
                Profil & Branding Resmi Perusahaan
              </h3>
              <p class="text-[11px] text-slate-500">Kustomisasi identitas logo & cover banner portal Anda.</p>
            </div>
          </div>
          <button @click="showBrandingModal = false" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="handleSaveClientBranding" class="space-y-4">
          <!-- 1. Upload Logo Resmi Perusahaan -->
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
            <div class="flex items-center justify-between">
              <label class="font-bold text-slate-900 block">Logo Perusahaan (PNG/WebP/SVG Transparan)</label>
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
                <p class="text-[10px] text-slate-500">Logo akan tampil di Berita Acara (BA), Live Tracker, dan Dashboard Anda.</p>
              </div>
            </div>
          </div>

          <!-- 2. Upload Hero Cover / Banner Perusahaan -->
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2.5">
            <div class="flex items-center justify-between">
              <label class="font-bold text-slate-900 block">Foto Cover / Banner Gedung Kantor (Panorama 16:9)</label>
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

          <!-- 3. Legalitas & Kontak Tambahan -->
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
              <label class="block font-bold mb-1">Website Resmi</label>
              <input
                type="text"
                placeholder="https://company.co.id"
                v-model="brandingForm.website"
                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 font-mono"
              />
            </div>
          </div>

          <div>
            <label class="block font-bold mb-1">Alamat Kantor Pusat</label>
            <textarea
              rows="2"
              placeholder="Alamat kantor lengkap perusahaan"
              v-model="brandingForm.address"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500"
            />
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
              <span>{{ saving ? 'Menyimpan...' : 'Simpan Profil & Branding' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api, getFileUrl } from '../../services/api';
import { useAuth } from '../../composables/useAuth';
import StatusBadge from '../../components/StatusBadge.vue';
import {
  Building2,
  Store,
  CheckCircle2,
  Clock,
  AlertTriangle,
  MapPin,
  Camera,
  FileCheck2,
  FileSpreadsheet,
  RefreshCw,
  ChevronRight,
  Eye,
  Sparkles,
  User,
  Globe,
  X,
  Image as ImageIcon,
  Save
} from 'lucide-vue-next';

defineEmits(['switch-tab']);

const auth = useAuth();
const workOrders = ref([]);
const recentPhotos = ref([]);
const issuesList = ref([]);
const clientCompany = ref(null);
const loading = ref(true);
const saving = ref(false);

const showBrandingModal = ref(false);
const brandingForm = ref({});
const logoFile = ref(null);
const bannerFile = ref(null);
const logoPreviewUrl = ref('');
const bannerPreviewUrl = ref('');

async function loadClientData() {
  loading.value = true;
  try {
    const [woRes, photoRes, issRes, vRes] = await Promise.all([
      api.getWorkOrders(),
      api.getEvidencePhotos({ limit: 8 }),
      api.getFieldIssues(),
      api.getVendors()
    ]);
    workOrders.value = woRes.data || [];
    recentPhotos.value = photoRes.data || [];
    issuesList.value = issRes.data || [];

    // Identify client company
    if (auth.state.user?.vendor_id) {
      clientCompany.value = (vRes.data || []).find(v => v.id === auth.state.user.vendor_id);
    } else if (vRes.data && vRes.data.length > 0) {
      clientCompany.value = vRes.data[0];
    }
  } catch (err) {
    console.error('Failed to load client data:', err);
  } finally {
    loading.value = false;
  }
}

function openBrandingModal() {
  if (!clientCompany.value) return;
  brandingForm.value = {
    npwp: clientCompany.value.npwp || '',
    website: clientCompany.value.website || '',
    address: clientCompany.value.address || '',
  };
  logoFile.value = null;
  bannerFile.value = null;
  logoPreviewUrl.value = clientCompany.value.logo_url ? getFileUrl(clientCompany.value.logo_url) : '';
  bannerPreviewUrl.value = clientCompany.value.banner_url ? getFileUrl(clientCompany.value.banner_url) : '';
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

async function handleSaveClientBranding() {
  if (!clientCompany.value) return;
  saving.value = true;
  try {
    const fd = new FormData();
    if (logoFile.value) fd.append('logo', logoFile.value);
    if (bannerFile.value) fd.append('banner', bannerFile.value);
    if (brandingForm.value.npwp !== undefined) fd.append('npwp', brandingForm.value.npwp);
    if (brandingForm.value.website !== undefined) fd.append('website', brandingForm.value.website);
    if (brandingForm.value.address !== undefined) fd.append('address', brandingForm.value.address);

    const res = await api.updateVendorBranding(clientCompany.value.id, fd);
    if (res.success) {
      alert('Profil & branding perusahaan berhasil diperbarui!');
      showBrandingModal.value = false;
      await loadClientData();
    } else {
      alert(res.message || 'Gagal menyimpan branding perusahaan.');
    }
  } catch (err) {
    alert(err.message || 'Terjadi kesalahan saat menyimpan branding.');
  } finally {
    saving.value = false;
  }
}

const completedOrders = computed(() => {
  return workOrders.value.filter(wo => ['APPROVED', 'COMPLETED'].includes(wo.status));
});

const inProgressOrders = computed(() => {
  return workOrders.value.filter(wo => ['IN_PROGRESS', 'ASSIGNED', 'CHECKED_IN', 'SUBMITTED', 'UNDER_REVIEW'].includes(wo.status));
});

const openIssuesCount = computed(() => {
  return issuesList.value.filter(i => i.status === 'OPEN').length;
});

const completionRate = computed(() => {
  if (workOrders.value.length === 0) return 0;
  return Math.round((completedOrders.value.length / workOrders.value.length) * 100);
});

onMounted(() => {
  loadClientData();
});
</script>
