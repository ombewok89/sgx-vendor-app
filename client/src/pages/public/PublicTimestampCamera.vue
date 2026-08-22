<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-purple-500 selection:text-white flex flex-col">
    
    <!-- Top Header Bar -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur-md sticky top-0 z-40 px-4 py-3 shadow-md">
      <div class="max-w-5xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 via-purple-700 to-indigo-800 flex items-center justify-center text-white font-black text-sm shadow-md shadow-amber-500/20">
            SGX
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="font-extrabold text-sm text-white tracking-wide">TIMESLIP CAMERA</h1>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5 animate-pulse"></span>
                STANDALONE
              </span>
            </div>
            <p class="text-[11px] text-slate-400">Kamera Stempel Waktu & GPS Digital — Simpan Langsung ke Galeri HP/PC</p>
          </div>
        </div>

        <button
          type="button"
          @click="goBackHome"
          class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold transition-all border border-slate-700 flex items-center gap-1.5 cursor-pointer active:scale-95"
        >
          <Home class="w-3.5 h-3.5" />
          <span>Beranda</span>
        </button>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-6 space-y-6">
      
      <!-- Privacy & Storage Zero-Load Banner -->
      <div class="p-3.5 sm:p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl flex items-center gap-3 text-emerald-300 text-xs">
        <ShieldCheck class="w-5 h-5 shrink-0 text-emerald-400" />
        <div class="leading-relaxed">
          <strong>100% Privat & Aman:</strong> Foto diproses langsung di browser Anda dan disimpan ke memori HP/PC Anda. Foto tidak diunggah ke server database.
        </div>
      </div>

      <!-- STATE 1: CAMERA & UPLOAD VIEWFINDER (Belum Ambil Foto) -->
      <div v-if="!capturedImage" class="space-y-6 animate-fade-in">
        
        <!-- Viewfinder Box -->
        <div class="relative bg-black rounded-3xl border border-slate-800 overflow-hidden shadow-2xl flex flex-col items-center justify-center min-h-[380px] sm:min-h-[460px]">
          
          <!-- Live Video Stream -->
          <video
            v-show="isCameraActive"
            ref="videoRef"
            autoplay
            playsinline
            muted
            class="w-full h-full object-cover max-h-[540px]"
          ></video>

          <!-- Camera Inactive Placeholder -->
          <div v-if="!isCameraActive" class="p-8 text-center space-y-4 max-w-md">
            <div class="w-16 h-16 rounded-3xl bg-slate-900 border border-slate-700 text-slate-400 flex items-center justify-center mx-auto shadow-inner">
              <Camera class="w-8 h-8 text-purple-400" />
            </div>
            <div>
              <h3 class="text-base font-bold text-white">Kamera Belum Aktif</h3>
              <p class="text-xs text-slate-400 mt-1">Nyalakan kamera browser atau pilih foto yang sudah ada dari galeri ponsel/PC Anda.</p>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
              <button
                type="button"
                @click="startCamera"
                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-700 to-indigo-600 hover:from-purple-600 hover:to-indigo-500 text-white text-xs font-bold flex items-center gap-2 shadow-lg shadow-purple-900/30 active:scale-95 transition-all cursor-pointer"
              >
                <Camera class="w-4 h-4" />
                <span>Buka Kamera Sekarang</span>
              </button>
              <label class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold flex items-center gap-2 border border-slate-700 active:scale-95 transition-all cursor-pointer">
                <Upload class="w-4 h-4 text-emerald-400" />
                <span>Pilih dari Galeri</span>
                <input type="file" accept="image/*" class="hidden" @change="handleFileSelected" />
              </label>
            </div>
          </div>

          <!-- Camera Live Controls Overlay (Ketika Kamera Aktif) -->
          <div v-if="isCameraActive" class="absolute top-4 inset-x-4 flex items-center justify-between z-20">
            <!-- GPS Status Pill -->
            <div class="px-3 py-1 rounded-full bg-slate-900/80 backdrop-blur-md border border-slate-700 text-[11px] font-mono flex items-center gap-1.5 shadow-md">
              <MapPin class="w-3.5 h-3.5" :class="gpsLocation ? 'text-emerald-400' : 'text-amber-400 animate-pulse'" />
              <span>{{ gpsLocation ? `${gpsLocation.lat.toFixed(5)}, ${gpsLocation.lng.toFixed(5)}` : 'Mencari GPS...' }}</span>
            </div>

            <!-- Switch Camera Toggle (Mobile Front/Back) -->
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="toggleCameraFacing"
                class="p-2.5 rounded-full bg-slate-900/80 hover:bg-slate-800 text-white backdrop-blur-md border border-slate-700 shadow-md active:scale-90 transition-all cursor-pointer"
                title="Ganti Kamera Depan/Belakang"
              >
                <SwitchCamera class="w-4 h-4" />
              </button>
              <button
                type="button"
                @click="stopCamera"
                class="p-2.5 rounded-full bg-rose-600/80 hover:bg-rose-700 text-white backdrop-blur-md border border-white/20 shadow-md active:scale-90 transition-all cursor-pointer"
                title="Tutup Kamera"
              >
                <X class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Capture Shutter Button (Bottom Overlay) -->
          <div v-if="isCameraActive" class="absolute bottom-6 inset-x-0 flex items-center justify-center gap-6 z-20">
            <label class="p-3 rounded-full bg-slate-900/80 hover:bg-slate-800 text-slate-300 backdrop-blur-md border border-slate-700 shadow-lg cursor-pointer active:scale-90 transition-all" title="Upload dari File">
              <Upload class="w-5 h-5 text-emerald-400" />
              <input type="file" accept="image/*" class="hidden" @change="handleFileSelected" />
            </label>

            <!-- Big Shutter Button -->
            <button
              type="button"
              @click="captureSnapshot"
              class="w-18 h-18 rounded-full bg-white p-1.5 shadow-2xl shadow-purple-500/40 border-4 border-purple-600 hover:scale-105 active:scale-95 transition-all cursor-pointer flex items-center justify-center"
            >
              <div class="w-full h-full rounded-full bg-gradient-to-tr from-purple-700 to-indigo-600 flex items-center justify-center text-white">
                <Camera class="w-6 h-6" />
              </div>
            </button>

            <button
              type="button"
              @click="refreshGps"
              class="p-3 rounded-full bg-slate-900/80 hover:bg-slate-800 text-slate-300 backdrop-blur-md border border-slate-700 shadow-lg cursor-pointer active:scale-90 transition-all"
              title="Perbarui GPS"
            >
              <RefreshCw class="w-5 h-5 text-amber-400" :class="{ 'animate-spin': fetchingGps }" />
            </button>
          </div>
        </div>

        <!-- Customization Settings Form -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-5">
          <h3 class="font-black text-sm uppercase text-slate-300 tracking-wider flex items-center gap-2">
            <Sliders class="w-4 h-4 text-purple-400" />
            <span>Pengaturan Keterangan & Stempel Watermark</span>
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
            <div>
              <label class="block font-bold text-slate-400 mb-1">Nama Perusahaan / Header:</label>
              <input
                type="text"
                v-model="stampForm.companyName"
                placeholder="Sinar Grafika"
                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl focus:border-purple-500 focus:outline-none text-white font-medium shadow-inner"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-400 mb-1">Nomor Kontak / WhatsApp:</label>
              <input
                type="text"
                v-model="stampForm.companyPhone"
                placeholder="082388885251"
                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl focus:border-purple-500 focus:outline-none text-white font-medium shadow-inner"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-400 mb-1">Alamat / Lokasi Lapangan:</label>
              <input
                type="text"
                v-model="stampForm.addressText"
                placeholder="Alamat akan terisi otomatis via GPS..."
                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl focus:border-purple-500 focus:outline-none text-white font-medium shadow-inner"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-400 mb-1">Pilihan Desain Template:</label>
              <select
                v-model="stampForm.templatePreset"
                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl focus:border-purple-500 focus:outline-none text-white font-bold cursor-pointer shadow-inner"
              >
                <option value="SGX_PREMIUM">SGX Premium 1/3 Screen (Standar Sinar Grafika)</option>
                <option value="COMPACT_CORNER">Compact Corner Badge (Minimalis)</option>
                <option value="SIDEBAR_REPORT">Sidebar Technical Strip (Samping)</option>
                <option value="CLASSIC_GPS">Classic GPS Map Camera (Klasik Kuning)</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-400 mb-1">Zona Waktu:</label>
              <select
                v-model="stampForm.timeZone"
                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl focus:border-purple-500 focus:outline-none text-white font-bold cursor-pointer shadow-inner"
              >
                <option value="WIB">WIB (Waktu Indonesia Barat - UTC+7)</option>
                <option value="WITA">WITA (Waktu Indonesia Tengah - UTC+8)</option>
                <option value="WIT">WIT (Waktu Indonesia Timur - UTC+9)</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-400 mb-1">Keterangan / Proyek (Opsional):</label>
              <input
                type="text"
                v-model="stampForm.projectName"
                placeholder="Contoh: Pemasangan Signage / Survey"
                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl focus:border-purple-500 focus:outline-none text-white font-medium shadow-inner"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- STATE 2: PREVIEW & RESULT STAGE (Setelah Ambil/Pilih Foto) -->
      <div v-else class="space-y-6 animate-scale-up">
        
        <!-- Processed Watermarked Image Preview -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 sm:p-6 shadow-2xl flex flex-col items-center space-y-4">
          <div class="w-full max-w-2xl rounded-2xl overflow-hidden shadow-2xl border border-slate-700 bg-black relative">
            <img
              :src="watermarkedImage"
              alt="Hasil Watermark Timestamp"
              class="w-full h-auto object-contain max-h-[580px] mx-auto"
            />
            
            <div v-if="processingWatermark" class="absolute inset-0 bg-slate-950/80 backdrop-blur-xs flex flex-col items-center justify-center space-y-2 text-xs text-white">
              <Loader2 class="w-8 h-8 animate-spin text-purple-500" />
              <p class="font-bold">Menerapkan Stempel Sinar Grafika (1/3 Layar)...</p>
            </div>
          </div>

          <!-- Quick Actions Bar -->
          <div class="flex flex-wrap items-center justify-center gap-3 w-full max-w-2xl pt-2">
            <button
              type="button"
              @click="downloadResult"
              class="flex-1 min-w-[200px] px-6 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white text-xs font-black flex items-center justify-center gap-2 shadow-lg shadow-emerald-900/30 active:scale-95 transition-all cursor-pointer"
            >
              <Download class="w-4 h-4" />
              <span>Simpan ke Galeri / Unduh</span>
            </button>

            <button
              v-if="canShare"
              type="button"
              @click="shareResult"
              class="px-5 py-3 rounded-2xl bg-gradient-to-r from-purple-700 to-indigo-600 hover:from-purple-600 hover:to-indigo-500 text-white text-xs font-bold flex items-center justify-center gap-2 shadow-lg shadow-purple-900/30 active:scale-95 transition-all cursor-pointer"
            >
              <Share2 class="w-4 h-4" />
              <span>Bagikan</span>
            </button>

            <button
              type="button"
              @click="resetCapture"
              class="px-5 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold flex items-center justify-center gap-2 border border-slate-700 active:scale-95 transition-all cursor-pointer"
            >
              <RotateCcw class="w-4 h-4" />
              <span>Ambil Foto Baru</span>
            </button>
          </div>
        </div>

        <!-- Re-Customize Result Form -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-4">
          <div class="flex items-center justify-between">
            <h4 class="font-bold text-xs text-slate-300 uppercase tracking-wider flex items-center gap-2">
              <Sliders class="w-4 h-4 text-purple-400" />
              <span>Ubah Keterangan & Template (Hasil Diperbarui Otomatis)</span>
            </h4>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
              <label class="block text-slate-400 font-semibold mb-1 text-[11px]">Template:</label>
              <select
                v-model="stampForm.templatePreset"
                @change="renderWatermarkCanvas"
                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold text-xs"
              >
                <option value="SGX_PREMIUM">SGX Premium 1/3 Screen</option>
                <option value="COMPACT_CORNER">Compact Corner Badge</option>
                <option value="SIDEBAR_REPORT">Sidebar Technical Strip</option>
                <option value="CLASSIC_GPS">Classic GPS Map Camera</option>
              </select>
            </div>

            <div>
              <label class="block text-slate-400 font-semibold mb-1 text-[11px]">Alamat / Lokasi:</label>
              <input
                type="text"
                v-model="stampForm.addressText"
                @input="debouncedRender"
                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs"
              />
            </div>

            <div>
              <label class="block text-slate-400 font-semibold mb-1 text-[11px]">Nomor WhatsApp:</label>
              <input
                type="text"
                v-model="stampForm.companyPhone"
                @input="debouncedRender"
                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs"
              />
            </div>
          </div>
        </div>
      </div>

    </main>

    <!-- Hidden Rendering Canvas -->
    <canvas ref="canvasRef" class="hidden"></canvas>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-6 text-center text-xs text-slate-500 bg-slate-950">
      <p class="font-semibold text-slate-400">PT Sinar Graha Kreatif</p>
      <p class="text-[11px] mt-0.5">Standalone Public Timestamp Camera — Zero Cloud Overhead</p>
    </footer>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import {
  Camera,
  Upload,
  SwitchCamera,
  MapPin,
  RefreshCw,
  Sliders,
  Download,
  Share2,
  RotateCcw,
  ShieldCheck,
  Home,
  X,
  Loader2
} from 'lucide-vue-next';
import { reverseGeocodeCoordinates } from '../../utils/geoCoder';

const videoRef = ref(null);
const canvasRef = ref(null);
const isCameraActive = ref(false);
const facingMode = ref('environment'); // 'environment' | 'user'
const mediaStream = ref(null);
const fetchingGps = ref(false);
const processingWatermark = ref(false);

const capturedImage = ref(null);
const watermarkedImage = ref(null);
const gpsLocation = ref(null);
const captureTimestamp = ref(null);

const canShare = typeof navigator !== 'undefined' && !!navigator.share;

const stampForm = reactive({
  companyName: 'Sinar Grafika',
  companyPhone: '082388885251',
  projectName: 'Dokumentasi Lapangan',
  addressText: '',
  templatePreset: 'SGX_PREMIUM',
  timeZone: 'WIB'
});

// Cache Logo SGX
let cachedLogoImg = null;
function loadLogoImage() {
  return new Promise((resolve) => {
    if (cachedLogoImg && cachedLogoImg.complete && cachedLogoImg.naturalWidth > 0) {
      return resolve(cachedLogoImg);
    }
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
      cachedLogoImg = img;
      resolve(img);
    };
    img.onerror = () => resolve(null);
    img.src = '/sgx_logo.png';
  });
}

// Fetch 100% Real Satellite Imagery Tile from Esri World Imagery (No API Key Required)
function fetchRealSatelliteTile(lat, lng, width, height) {
  return new Promise((resolve) => {
    if (lat == null || lng == null) return resolve(null);
    const delta = 0.0012;
    const minLng = (Number(lng) - delta).toFixed(6);
    const maxLng = (Number(lng) + delta).toFixed(6);
    const minLat = (Number(lat) - (delta * 0.75)).toFixed(6);
    const maxLat = (Number(lat) + (delta * 0.75)).toFixed(6);
    const tileW = Math.min(500, Math.max(260, Math.round(width)));
    const tileH = Math.min(420, Math.max(200, Math.round(height)));

    const url = `https://services.arcgisonline.com/arcgis/rest/services/World_Imagery/MapServer/export?bbox=${minLng},${minLat},${maxLng},${maxLat}&bboxSR=4326&size=${tileW},${tileH}&f=image`;

    const img = new Image();
    img.crossOrigin = 'anonymous';
    const timer = setTimeout(() => resolve(null), 2500);

    img.onload = () => {
      clearTimeout(timer);
      resolve(img);
    };
    img.onerror = () => {
      clearTimeout(timer);
      resolve(null);
    };
    img.src = url;
  });
}

function goBackHome() {
  window.location.href = '/';
}

// 1. Geolocation Handling
async function refreshGps() {
  if (!navigator.geolocation) {
    gpsLocation.value = { lat: -3.293262, lng: 102.895628, accuracy: 5 };
    updateAddressFromGps();
    return;
  }

  fetchingGps.value = true;
  navigator.geolocation.getCurrentPosition(
    async (pos) => {
      gpsLocation.value = {
        lat: pos.coords.latitude,
        lng: pos.coords.longitude,
        accuracy: Math.round(pos.coords.accuracy || 5)
      };
      await updateAddressFromGps();
      fetchingGps.value = false;
    },
    async (err) => {
      console.warn('GPS access error:', err);
      if (!gpsLocation.value) {
        gpsLocation.value = { lat: -3.293262, lng: 102.895628, accuracy: 10 };
      }
      await updateAddressFromGps();
      fetchingGps.value = false;
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  );
}

async function updateAddressFromGps() {
  if (!gpsLocation.value) return;
  if (!stampForm.addressText || stampForm.addressText === 'Alamat akan terisi otomatis via GPS...') {
    try {
      const addr = await reverseGeocodeCoordinates(
        gpsLocation.value.lat,
        gpsLocation.value.lng,
        'Taba Jemekeh, Kec. Lubuk Linggau Tim. I, Kota Lubuklinggau, Sumatera Selatan 31625'
      );
      if (addr) {
        stampForm.addressText = addr;
      }
    } catch (e) {
      stampForm.addressText = 'Taba Jemekeh, Kec. Lubuk Linggau Tim. I, Kota Lubuklinggau, Sumatera Selatan 31625';
    }
  }
}

// 2. Camera Controls
async function startCamera() {
  try {
    if (mediaStream.value) {
      stopCamera();
    }

    const constraints = {
      video: {
        facingMode: { ideal: facingMode.value },
        width: { ideal: 1920 },
        height: { ideal: 1080 }
      },
      audio: false
    };

    mediaStream.value = await navigator.mediaDevices.getUserMedia(constraints);
    if (videoRef.value) {
      videoRef.value.srcObject = mediaStream.value;
      await videoRef.value.play();
      isCameraActive.value = true;
    }
  } catch (err) {
    console.error('Camera open error:', err);
    alert('Tidak dapat mengakses kamera: ' + (err.message || 'Mohon izinkan akses kamera di browser Anda.'));
  }
}

function stopCamera() {
  if (mediaStream.value) {
    mediaStream.value.getTracks().forEach(track => track.stop());
    mediaStream.value = null;
  }
  isCameraActive.value = false;
}

function toggleCameraFacing() {
  facingMode.value = facingMode.value === 'environment' ? 'user' : 'environment';
  startCamera();
}

// 3. Snapshot Capture
function captureSnapshot() {
  if (!videoRef.value || !isCameraActive.value) return;

  const video = videoRef.value;
  const canvas = document.createElement('canvas');
  canvas.width = video.videoWidth || 1280;
  canvas.height = video.videoHeight || 720;
  const ctx = canvas.getContext('2d');
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

  capturedImage.value = canvas.toDataURL('image/jpeg', 0.95);
  captureTimestamp.value = new Date();
  stopCamera();

  renderWatermarkCanvas();
}

// 4. File Upload
function handleFileSelected(event) {
  const file = event.target.files?.[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = (e) => {
    capturedImage.value = e.target.result;
    captureTimestamp.value = new Date();
    stopCamera();
    renderWatermarkCanvas();
  };
  reader.readAsDataURL(file);
}

// 5. HTML5 Canvas Watermark Rendering Engine
let debounceTimer = null;
function debouncedRender() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    renderWatermarkCanvas();
  }, 300);
}

async function renderWatermarkCanvas() {
  if (!capturedImage.value) return;
  processingWatermark.value = true;

  const logoImg = await loadLogoImage();
  const lat = gpsLocation.value ? Number(gpsLocation.value.lat) : -3.293262;
  const lng = gpsLocation.value ? Number(gpsLocation.value.lng) : 102.895628;
  const satelliteImg = await fetchRealSatelliteTile(lat, lng, 320, 240);

  const img = new Image();
  img.crossOrigin = 'anonymous';
  img.onload = () => {
    const canvas = canvasRef.value || document.createElement('canvas');
    canvas.width = img.naturalWidth || img.width;
    canvas.height = img.naturalHeight || img.height;
    const ctx = canvas.getContext('2d');

    // 1. Draw base photo
    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

    // 2. Prepare Metadata Strings
    const now = captureTimestamp.value || new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const timeStr = `${hours}:${minutes}`;

    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const dayName = days[now.getDay()];
    const dateStr = `${String(now.getDate()).padStart(2, '0')}/${String(now.getMonth() + 1).padStart(2, '0')}/${now.getFullYear()}`;

    const latFormatted = lat.toFixed(6);
    const lngFormatted = lng.toFixed(6);
    const acc = gpsLocation.value?.accuracy || 5;

    const w = canvas.width;
    const h = canvas.height;
    const s = Math.max(1.0, w / 1100);

    // 3. Render Chosen Template
    if (stampForm.templatePreset === 'SGX_PREMIUM') {
      renderSgxPremiumTemplate(ctx, w, h, s, {
        timeStr,
        dayName,
        dateStr,
        lat: latFormatted,
        lng: lngFormatted,
        acc,
        logoImg,
        satelliteImg
      });
    } else if (stampForm.templatePreset === 'COMPACT_CORNER') {
      renderCompactCornerTemplate(ctx, w, h, s, { timeStr, dateStr, lat: latFormatted, lng: lngFormatted, acc });
    } else if (stampForm.templatePreset === 'SIDEBAR_REPORT') {
      renderSidebarReportTemplate(ctx, w, h, s, { timeStr, dateStr, lat: latFormatted, lng: lngFormatted });
    } else {
      renderClassicGpsTemplate(ctx, w, h, s, { timeStr, dateStr, lat: latFormatted, lng: lngFormatted, acc });
    }

    watermarkedImage.value = canvas.toDataURL('image/jpeg', 0.94);
    processingWatermark.value = false;
  };
  img.src = capturedImage.value;
}

/**
 * TEMPLATE 1: SGX PREMIUM 1/3 SCREEN (PERSIS SEPERTI GAMBAR CONTOH USER)
 * - Top Left: Official SGX Diamond Logo
 * - Bottom ~1/3 Screen:
 *   - Left: Extra Large Digital Clock (e.g. 14:16) + Gold Separator + Date (07/08/2026) & Day (Jumat)
 *   - Full Address Text with strong drop shadow & wrapping
 *   - Dark pill badge: "Koordinat: -3.293262, 102.895628"
 *   - Right: Real Satellite Google Mini-Map with blue dot & green radar beam + "Google" watermark
 *   - Bottom Footer: Solid White Bar with Logo + "Sinar Grafika" + "082388885251" + diagonal dividing line + watermark logo
 */
function renderSgxPremiumTemplate(ctx, w, h, s, meta) {
  // 1. Draw Top-Left SGX Diamond Logo
  const topLogoSize = Math.round(100 * s);
  const topLogoX = Math.round(28 * s);
  const topLogoY = Math.round(28 * s);

  if (meta.logoImg) {
    drawRoundedImage(ctx, meta.logoImg, topLogoX, topLogoY, topLogoSize, topLogoSize, 20 * s);
  }

  // 2. Calculate 1/3 Screen Proportions for Bottom Watermark
  const footerBarH = Math.round(105 * s); // White branding bar height
  const infoPanelH = Math.round(h * 0.26); // Overlay area (~26% of photo height, total ~35% or 1/3 screen)
  const totalWatermarkH = infoPanelH + footerBarH;
  const panelY = h - totalWatermarkH;

  // 3. Mini Map Satellite on the Right Side
  const mapMarginR = Math.round(28 * s);
  const mapH = Math.min(infoPanelH - (20 * s), Math.round(220 * s));
  const mapW = Math.round(mapH * 1.05);
  const mapX = w - mapW - mapMarginR;
  const mapY = panelY + Math.round((infoPanelH - mapH) / 2);

  // 4. Left Content Column
  const textMarginL = Math.round(32 * s);
  const maxTextW = mapX - textMarginL - (24 * s);

  let curY = panelY + (72 * s);

  // A. DIGITAL CLOCK (EXTRA LARGE e.g. 14:16)
  ctx.save();
  ctx.font = `900 ${88 * s}px "Inter", "Montserrat", "Segoe UI", Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0, 0, 0, 0.95)';
  ctx.shadowBlur = 16 * s;
  ctx.strokeStyle = 'rgba(0, 0, 0, 0.9)';
  ctx.lineWidth = 7 * s;
  ctx.strokeText(meta.timeStr, textMarginL, curY);
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText(meta.timeStr, textMarginL, curY);

  const timeW = ctx.measureText(meta.timeStr).width;

  // B. VERTICAL GOLD SEPARATOR
  const sepX = textMarginL + timeW + (18 * s);
  const sepTopY = panelY + (8 * s);
  const sepH = 72 * s;
  ctx.fillStyle = '#EAB308';
  ctx.fillRect(sepX, sepTopY, 4.5 * s, sepH);

  // C. DATE (07/08/2026) & DAY NAME (Jumat)
  ctx.font = `800 ${28 * s}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.strokeText(meta.dateStr, sepX + (16 * s), curY - (40 * s));
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText(meta.dateStr, sepX + (16 * s), curY - (40 * s));

  ctx.font = `800 ${30 * s}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.strokeText(meta.dayName, sepX + (16 * s), curY - (4 * s));
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText(meta.dayName, sepX + (16 * s), curY - (4 * s));
  ctx.restore();

  // D. ADDRESS TEXT (Multi-line with bold drop shadow)
  curY += (44 * s);
  const fullAddress = stampForm.addressText || 'Taba Jemekeh, Kec. Lubuk Linggau Tim. I, Kota Lubuklinggau, Sumatera Selatan 31625';
  
  ctx.save();
  ctx.font = `800 ${26 * s}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0, 0, 0, 0.95)';
  ctx.shadowBlur = 14 * s;
  ctx.strokeStyle = 'rgba(0, 0, 0, 0.9)';
  ctx.lineWidth = 6 * s;

  const addressLines = wrapTextLines(ctx, fullAddress, maxTextW, 2);
  addressLines.forEach((line) => {
    ctx.strokeText(line, textMarginL, curY);
    ctx.fillStyle = '#FFFFFF';
    ctx.fillText(line, textMarginL, curY);
    curY += (34 * s);
  });
  ctx.restore();

  // E. DARK COORDINATE PILL BADGE: "Koordinat: -3.293262, 102.895628"
  curY += (6 * s);
  const coordText = `Koordinat: ${meta.lat}, ${meta.lng}`;
  ctx.save();
  ctx.font = `700 ${22 * s}px "Inter", "Segoe UI", monospace, Arial`;
  const coordTextW = ctx.measureText(coordText).width;
  const badgePadX = 16 * s;
  const badgeH = 38 * s;

  // Semi-transparent rounded background pill
  ctx.fillStyle = 'rgba(15, 23, 42, 0.65)';
  ctx.beginPath();
  ctx.roundRect(textMarginL, curY, coordTextW + (badgePadX * 2), badgeH, 8 * s);
  ctx.fill();

  ctx.fillStyle = '#FFFFFF';
  ctx.shadowColor = 'rgba(0, 0, 0, 0.8)';
  ctx.shadowBlur = 8 * s;
  ctx.fillText(coordText, textMarginL + badgePadX, curY + (26 * s));
  ctx.restore();

  // 5. Draw Right Google Mini-Map Satellite
  drawGoogleSatelliteMiniMap(ctx, mapX, mapY, mapW, mapH, s, meta.satelliteImg);

  // 6. Draw Solid White Footer Bar with Logo, Name & Phone
  const footerY = h - footerBarH;
  ctx.save();
  ctx.fillStyle = '#FFFFFF';
  ctx.fillRect(0, footerY, w, footerBarH);

  // Top separator line
  ctx.fillStyle = '#E2E8F0';
  ctx.fillRect(0, footerY, w, 2 * s);

  // Left SGX Logo in footer
  const footerLogoSize = Math.round(75 * s);
  const footerLogoY = footerY + Math.round((footerBarH - footerLogoSize) / 2);
  if (meta.logoImg) {
    ctx.drawImage(meta.logoImg, textMarginL, footerLogoY, footerLogoSize, footerLogoSize);
  }

  // Sinar Grafika + Phone Number Text
  const textX = textMarginL + footerLogoSize + (18 * s);
  ctx.fillStyle = '#0F172A';
  ctx.font = `900 ${28 * s}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.fillText(stampForm.companyName || 'Sinar Grafika', textX, footerY + (44 * s));

  ctx.fillStyle = '#334155';
  ctx.font = `700 ${22 * s}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.fillText(stampForm.companyPhone || '082388885251', textX, footerY + (78 * s));

  // Diagonal dividing slash in center
  const midX = w * 0.68;
  ctx.strokeStyle = '#CBD5E1';
  ctx.lineWidth = 2 * s;
  ctx.beginPath();
  ctx.moveTo(midX + (20 * s), footerY + (12 * s));
  ctx.lineTo(midX - (20 * s), footerY + footerBarH - (12 * s));
  ctx.stroke();

  // Right SGX Emblem in Footer
  if (meta.logoImg) {
    const rightLogoX = w - footerLogoSize - (45 * s);
    ctx.drawImage(meta.logoImg, rightLogoX, footerLogoY, footerLogoSize, footerLogoSize);
  }
  ctx.restore();
}

/**
 * Draw Satellite Mini Map with Google watermark, Blue GPS Dot & Green Vision Cone
 */
function drawGoogleSatelliteMiniMap(ctx, x, y, width, height, s, satelliteImg) {
  ctx.save();

  // Rounded outer border
  const radius = 14 * s;
  ctx.beginPath();
  ctx.roundRect(x, y, width, height, radius);
  ctx.clip();

  // 1. Draw Satellite Imagery
  if (satelliteImg && satelliteImg.complete && satelliteImg.naturalWidth > 0) {
    ctx.drawImage(satelliteImg, x, y, width, height);
  } else {
    // Terrain fallback
    ctx.fillStyle = '#2C3E50';
    ctx.fillRect(x, y, width, height);
    ctx.fillStyle = '#274E13';
    ctx.fillRect(x, y, width * 0.5, height * 0.5);
    ctx.fillStyle = '#38761D';
    ctx.fillRect(x + width * 0.5, y + height * 0.4, width * 0.5, height * 0.6);

    // Street line
    ctx.strokeStyle = '#D97706';
    ctx.lineWidth = 6 * s;
    ctx.beginPath();
    ctx.moveTo(x, y + height * 0.7);
    ctx.lineTo(x + width, y + height * 0.25);
    ctx.stroke();
  }

  // 2. Green Directional Radar Beam (Field of View Angle)
  const pinX = x + width * 0.52;
  const pinY = y + height * 0.48;

  ctx.fillStyle = 'rgba(16, 185, 129, 0.65)';
  ctx.beginPath();
  ctx.moveTo(pinX, pinY);
  ctx.arc(pinX, pinY, 46 * s, -Math.PI * 0.85, -Math.PI * 0.3);
  ctx.closePath();
  ctx.fill();

  // 3. Blue GPS Location Dot with White Ring
  ctx.fillStyle = '#0284C7';
  ctx.beginPath();
  ctx.arc(pinX, pinY, 12 * s, 0, Math.PI * 2);
  ctx.fill();

  ctx.strokeStyle = '#FFFFFF';
  ctx.lineWidth = 3 * s;
  ctx.stroke();

  // 4. "Google" Watermark at Bottom Left of Mini Map
  ctx.font = `bold ${16 * s}px Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0, 0, 0, 0.9)';
  ctx.shadowBlur = 4 * s;
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText('Google', x + (12 * s), y + height - (12 * s));

  ctx.restore();

  // White Border around Mini Map
  ctx.save();
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.95)';
  ctx.lineWidth = 2.5 * s;
  ctx.beginPath();
  ctx.roundRect(x, y, width, height, radius);
  ctx.stroke();
  ctx.restore();
}

// Template 2: Compact Corner Badge
function renderCompactCornerTemplate(ctx, w, h, s, meta) {
  const boxW = Math.min(w * 0.75, 420 * s);
  const boxH = 110 * s;
  const pad = 16 * s;
  const x = pad;
  const y = h - boxH - pad;

  ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
  ctx.beginPath();
  ctx.roundRect(x, y, boxW, boxH, 12 * s);
  ctx.fill();
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
  ctx.lineWidth = 1.5 * s;
  ctx.stroke();

  ctx.fillStyle = '#f59e0b';
  ctx.font = `bold ${Math.round(13 * s)}px sans-serif`;
  ctx.fillText('SGX TIMESLIP EVIDENCE', x + 12 * s, y + 22 * s);

  ctx.fillStyle = '#ffffff';
  ctx.font = `bold ${Math.round(14 * s)}px sans-serif`;
  ctx.fillText(stampForm.projectName || 'Dokumentasi Lapangan', x + 12 * s, y + 44 * s);

  ctx.fillStyle = '#38bdf8';
  ctx.font = `${Math.round(11 * s)}px monospace`;
  ctx.fillText(`GPS: ${meta.lat}, ${meta.lng} | ±${meta.acc}m`, x + 12 * s, y + 68 * s);

  ctx.fillStyle = '#fde047';
  ctx.font = `bold ${Math.round(11 * s)}px monospace`;
  ctx.fillText(`${meta.dateStr} ${meta.timeStr}`, x + 12 * s, y + 90 * s);
}

// Template 3: Sidebar Technical Strip
function renderSidebarReportTemplate(ctx, w, h, s, meta) {
  const stripW = Math.max(w * 0.24, 180 * s);
  const stripX = w - stripW;

  ctx.fillStyle = 'rgba(15, 23, 42, 0.92)';
  ctx.fillRect(stripX, 0, stripW, h);

  ctx.fillStyle = '#9333ea';
  ctx.fillRect(stripX, 0, 4 * s, h);

  const pad = stripX + 14 * s;
  let curY = 30 * s;

  ctx.fillStyle = '#f59e0b';
  ctx.font = `bold ${Math.round(12 * s)}px sans-serif`;
  ctx.fillText('SGX FIELD REPORT', pad, curY);

  curY += 25 * s;
  ctx.fillStyle = '#ffffff';
  ctx.font = `bold ${Math.round(14 * s)}px sans-serif`;
  ctx.fillText(stampForm.projectName || 'Dokumentasi', pad, curY);

  curY += 28 * s;
  ctx.fillStyle = '#94a3b8';
  ctx.font = `${Math.round(10 * s)}px sans-serif`;
  ctx.fillText('WAKTU:', pad, curY);
  curY += 16 * s;
  ctx.fillStyle = '#fde047';
  ctx.font = `bold ${Math.round(11 * s)}px monospace`;
  ctx.fillText(meta.dateStr, pad, curY);
  curY += 16 * s;
  ctx.fillText(meta.timeStr, pad, curY);

  curY += 28 * s;
  ctx.fillStyle = '#94a3b8';
  ctx.font = `${Math.round(10 * s)}px sans-serif`;
  ctx.fillText('KOORDINAT GPS:', pad, curY);
  curY += 16 * s;
  ctx.fillStyle = '#38bdf8';
  ctx.font = `bold ${Math.round(10 * s)}px monospace`;
  ctx.fillText(`${meta.lat},`, pad, curY);
  curY += 14 * s;
  ctx.fillText(`${meta.lng}`, pad, curY);
}

// Template 4: Classic GPS Map Camera
function renderClassicGpsTemplate(ctx, w, h, s, meta) {
  const pad = 20 * s;
  let y = h - 80 * s;

  ctx.fillStyle = 'rgba(0, 0, 0, 0.6)';
  ctx.fillRect(0, y - 20 * s, w, 100 * s);

  ctx.shadowColor = '#000000';
  ctx.shadowBlur = 4;
  ctx.fillStyle = '#fde047';
  ctx.font = `bold ${Math.round(16 * s)}px sans-serif`;
  ctx.fillText(`${stampForm.projectName} - ${stampForm.addressText || 'Lokasi Pekerjaan'}`, pad, y);

  y += 24 * s;
  ctx.fillStyle = '#ffffff';
  ctx.font = `bold ${Math.round(14 * s)}px monospace`;
  ctx.fillText(`Lat: ${meta.lat} | Long: ${meta.lng} (±${meta.acc}m)`, pad, y);

  y += 22 * s;
  ctx.fillStyle = '#38bdf8';
  ctx.fillText(`${meta.dateStr}, ${meta.timeStr} | ${stampForm.companyName}`, pad, y);

  ctx.shadowBlur = 0;
}

// Helpers
function drawRoundedImage(ctx, img, x, y, width, height, radius) {
  ctx.save();
  ctx.beginPath();
  ctx.roundRect(x, y, width, height, radius);
  ctx.clip();
  ctx.drawImage(img, x, y, width, height);
  ctx.restore();

  ctx.save();
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.9)';
  ctx.lineWidth = Math.max(2, radius * 0.15);
  ctx.beginPath();
  ctx.roundRect(x, y, width, height, radius);
  ctx.stroke();
  ctx.restore();
}

function wrapTextLines(ctx, text, maxWidth, maxLines = 2) {
  if (!text) return [];
  const words = text.split(' ');
  const lines = [];
  let currentLine = words[0] || '';

  for (let i = 1; i < words.length; i++) {
    const word = words[i];
    const width = ctx.measureText(currentLine + ' ' + word).width;
    if (width < maxWidth) {
      currentLine += ' ' + word;
    } else {
      lines.push(currentLine);
      currentLine = word;
      if (lines.length === maxLines - 1) {
        break;
      }
    }
  }
  lines.push(currentLine);
  return lines.slice(0, maxLines);
}

// 6. Download & Share Actions
function downloadResult() {
  if (!watermarkedImage.value) return;

  const now = new Date();
  const dateNum = now.getFullYear() +
    String(now.getMonth() + 1).padStart(2, '0') +
    String(now.getDate()).padStart(2, '0') + '_' +
    String(now.getHours()).padStart(2, '0') +
    String(now.getMinutes()).padStart(2, '0') +
    String(now.getSeconds()).padStart(2, '0');

  const cleanTitle = (stampForm.companyName || 'SGX').replace(/[^a-zA-Z0-9]/g, '_');
  const filename = `SGX_TIMESLIP_${cleanTitle}_${dateNum}.jpg`;

  const link = document.createElement('a');
  link.href = watermarkedImage.value;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

async function shareResult() {
  if (!navigator.share || !watermarkedImage.value) return;

  try {
    const res = await fetch(watermarkedImage.value);
    const blob = await res.blob();
    const file = new File([blob], 'SGX_Timeslip.jpg', { type: 'image/jpeg' });

    await navigator.share({
      title: stampForm.companyName || 'Foto Timeslip SGX',
      text: `Dokumentasi Timestamp Sinar Grafika di ${stampForm.addressText}`,
      files: [file]
    });
  } catch (err) {
    if (err.name !== 'AbortError') {
      console.warn('Web Share failed:', err);
    }
  }
}

function resetCapture() {
  capturedImage.value = null;
  watermarkedImage.value = null;
  startCamera();
}

onMounted(() => {
  refreshGps();
});

onUnmounted(() => {
  stopCamera();
});
</script>
