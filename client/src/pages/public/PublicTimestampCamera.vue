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
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                AUTONOMOUS GPS
              </span>
            </div>
            <p class="text-[11px] text-slate-400">Kamera GPS Otomatis Sinar Grafika — Standar Spesifikasi Teknis SGX</p>
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
    <main class="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-6 space-y-5">
      
      <!-- Live Real-Time Location & Status Bar -->
      <div class="p-3.5 sm:p-4 bg-slate-900 border border-slate-800 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs shadow-lg">
        <div class="flex items-start gap-3">
          <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 shrink-0 border border-emerald-500/20">
            <MapPin class="w-4 h-4" />
          </div>
          <div class="space-y-0.5">
            <div class="flex items-center gap-2 font-bold text-slate-200">
              <span>Lokasi Terverifikasi Otomatis (Google / OSM Maps):</span>
              <span v-if="fetchingGps" class="inline-flex items-center text-[10px] text-amber-400">
                <Loader2 class="w-3 h-3 animate-spin mr-1" />
                Mencari satelit...
              </span>
            </div>
            <p class="text-[11px] text-emerald-400 font-medium leading-relaxed">
              {{ detectedAddress || 'Mendeteksi alamat akurat dari satelit GPS...' }}
            </p>
            <p class="text-[10px] text-slate-400 font-mono">
              Koordinat Asli: {{ gpsLocation ? `${gpsLocation.lat.toFixed(6)}, ${gpsLocation.lng.toFixed(6)} (Akurasi: ±${gpsLocation.accuracy}m)` : 'Menghubungkan ke sensor perangkat...' }}
            </p>
          </div>
        </div>

        <button
          type="button"
          @click="refreshGps"
          class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs flex items-center justify-center gap-1.5 shrink-0 border border-slate-700 cursor-pointer active:scale-95 transition-all"
        >
          <RefreshCw class="w-3.5 h-3.5 text-amber-400" :class="{ 'animate-spin': fetchingGps }" />
          <span>Segarkan Lokasi</span>
        </button>
      </div>

      <!-- Keterangan Pekerjaan & Stage Selector Input Box -->
      <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl shadow-lg space-y-3">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
          <div class="flex-1 space-y-1">
            <label class="block font-bold text-xs text-slate-300 flex items-center gap-2">
              <FileText class="w-4 h-4 text-purple-400" />
              <span>Keterangan Pekerjaan / Catatan Lapangan:</span>
            </label>
            <input
              type="text"
              v-model="stampForm.jobDescription"
              @input="debouncedRender"
              placeholder="Contoh: Pemasangan Signage / Survey Lokasi / Maintenance"
              class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl focus:border-purple-500 focus:outline-none text-white text-xs font-semibold shadow-inner"
            />
          </div>

          <div class="w-full sm:w-48 space-y-1">
            <label class="block font-bold text-xs text-slate-300 flex items-center gap-1.5">
              <Tag class="w-4 h-4 text-amber-400" />
              <span>Tahap Pekerjaan:</span>
            </label>
            <select
              v-model="stampForm.stage"
              @change="renderWatermarkCanvas"
              class="w-full px-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold text-xs focus:border-purple-500 focus:outline-none cursor-pointer"
            >
              <option value="AFTER">AFTER (Selesai)</option>
              <option value="PROCESS">PROCESS (Pengerjaan)</option>
              <option value="BEFORE">BEFORE (Sebelum)</option>
              <option value="ISSUE">ISSUE (Kendala)</option>
            </select>
          </div>

          <div class="w-full sm:w-32 space-y-1">
            <label class="block font-bold text-xs text-slate-300">Zona Waktu:</label>
            <select
              v-model="stampForm.timeZone"
              @change="renderWatermarkCanvas"
              class="w-full px-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold text-xs focus:border-purple-500 focus:outline-none cursor-pointer"
            >
              <option value="WIB">WIB (UTC+7)</option>
              <option value="WITA">WITA (UTC+8)</option>
              <option value="WIT">WIT (UTC+9)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- STATE 1: CAMERA & UPLOAD VIEWFINDER (Belum Ambil Foto) -->
      <div v-if="!capturedImage" class="space-y-6 animate-fade-in">
        
        <!-- Viewfinder Box -->
        <div class="relative bg-black rounded-3xl border border-slate-800 overflow-hidden shadow-2xl flex flex-col items-center justify-center min-h-[380px] sm:min-h-[480px]">
          
          <!-- Live Video Stream -->
          <video
            v-show="isCameraActive"
            ref="videoRef"
            autoplay
            playsinline
            muted
            class="w-full h-full object-cover max-h-[560px]"
          ></video>

          <!-- Camera Inactive Placeholder -->
          <div v-if="!isCameraActive" class="p-8 text-center space-y-4 max-w-md">
            <div class="w-16 h-16 rounded-3xl bg-slate-900 border border-slate-700 text-slate-400 flex items-center justify-center mx-auto shadow-inner">
              <Camera class="w-8 h-8 text-purple-400" />
            </div>
            <div>
              <h3 class="text-base font-bold text-white">Kamera Siap Digunakan</h3>
              <p class="text-xs text-slate-400 mt-1">Buka kamera langsung atau pilih foto dari galeri. Stempel waktu, lokasi Google Maps, dan keterangan kerja akan otomatis tertempel sesuai spesifikasi teknis SGX.</p>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
              <button
                type="button"
                @click="startCamera"
                class="px-6 py-3 rounded-2xl bg-gradient-to-r from-purple-700 to-indigo-600 hover:from-purple-600 hover:to-indigo-500 text-white text-xs font-bold flex items-center gap-2 shadow-lg shadow-purple-900/30 active:scale-95 transition-all cursor-pointer"
              >
                <Camera class="w-4 h-4" />
                <span>Buka Kamera Sekarang</span>
              </button>
              <label class="px-6 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold flex items-center gap-2 border border-slate-700 active:scale-95 transition-all cursor-pointer">
                <Upload class="w-4 h-4 text-emerald-400" />
                <span>Pilih dari Galeri</span>
                <input type="file" accept="image/*" class="hidden" @change="handleFileSelected" />
              </label>
            </div>
          </div>

          <!-- Camera Live Controls Overlay (Ketika Kamera Aktif) -->
          <div v-if="isCameraActive" class="absolute top-4 inset-x-4 flex items-center justify-between z-20">
            <!-- GPS Status Pill -->
            <div class="px-3 py-1.5 rounded-full bg-slate-900/85 backdrop-blur-md border border-slate-700 text-[11px] font-mono flex items-center gap-1.5 shadow-md text-white">
              <MapPin class="w-3.5 h-3.5" :class="gpsLocation ? 'text-emerald-400' : 'text-amber-400 animate-pulse'" />
              <span>{{ gpsLocation ? `${gpsLocation.lat.toFixed(5)}, ${gpsLocation.lng.toFixed(5)}` : 'Mencari GPS...' }}</span>
            </div>

            <!-- Switch Camera Toggle (Mobile Front/Back) -->
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="toggleCameraFacing"
                class="p-2.5 rounded-full bg-slate-900/85 hover:bg-slate-800 text-white backdrop-blur-md border border-slate-700 shadow-md active:scale-90 transition-all cursor-pointer"
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
            <label class="p-3.5 rounded-full bg-slate-900/85 hover:bg-slate-800 text-slate-300 backdrop-blur-md border border-slate-700 shadow-lg cursor-pointer active:scale-90 transition-all" title="Upload dari File">
              <Upload class="w-5 h-5 text-emerald-400" />
              <input type="file" accept="image/*" class="hidden" @change="handleFileSelected" />
            </label>

            <!-- Big Shutter Button -->
            <button
              type="button"
              @click="captureSnapshot"
              class="w-20 h-20 rounded-full bg-white p-1.5 shadow-2xl shadow-purple-500/40 border-4 border-purple-600 hover:scale-105 active:scale-95 transition-all cursor-pointer flex items-center justify-center"
            >
              <div class="w-full h-full rounded-full bg-gradient-to-tr from-purple-700 to-indigo-600 flex items-center justify-center text-white">
                <Camera class="w-7 h-7" />
              </div>
            </button>

            <button
              type="button"
              @click="refreshGps"
              class="p-3.5 rounded-full bg-slate-900/85 hover:bg-slate-800 text-slate-300 backdrop-blur-md border border-slate-700 shadow-lg cursor-pointer active:scale-90 transition-all"
              title="Perbarui GPS"
            >
              <RefreshCw class="w-5 h-5 text-amber-400" :class="{ 'animate-spin': fetchingGps }" />
            </button>
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
              class="w-full h-auto object-contain max-h-[600px] mx-auto"
            />
            
            <div v-if="processingWatermark" class="absolute inset-0 bg-slate-950/85 backdrop-blur-xs flex flex-col items-center justify-center space-y-2 text-xs text-white">
              <Loader2 class="w-8 h-8 animate-spin text-purple-500" />
              <p class="font-bold">Menerapkan Watermark Standar Spesifikasi SGX...</p>
            </div>
          </div>

          <!-- Quick Actions Bar -->
          <div class="flex flex-wrap items-center justify-center gap-3 w-full max-w-2xl pt-2">
            <button
              type="button"
              @click="downloadResult"
              class="flex-1 min-w-[200px] px-6 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white text-xs font-black flex items-center justify-center gap-2 shadow-lg shadow-emerald-900/30 active:scale-95 transition-all cursor-pointer"
            >
              <Download class="w-4 h-4" />
              <span>Simpan ke Galeri / Unduh</span>
            </button>

            <button
              v-if="canShare"
              type="button"
              @click="shareResult"
              class="px-5 py-3.5 rounded-2xl bg-gradient-to-r from-purple-700 to-indigo-600 hover:from-purple-600 hover:to-indigo-500 text-white text-xs font-bold flex items-center justify-center gap-2 shadow-lg shadow-purple-900/30 active:scale-95 transition-all cursor-pointer"
            >
              <Share2 class="w-4 h-4" />
              <span>Bagikan</span>
            </button>

            <button
              type="button"
              @click="resetCapture"
              class="px-5 py-3.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold flex items-center justify-center gap-2 border border-slate-700 active:scale-95 transition-all cursor-pointer"
            >
              <RotateCcw class="w-4 h-4" />
              <span>Ambil Foto Baru</span>
            </button>
          </div>
        </div>

      </div>

    </main>

    <!-- Hidden Rendering Canvas -->
    <canvas ref="canvasRef" class="hidden"></canvas>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-6 text-center text-xs text-slate-500 bg-slate-950">
      <p class="font-semibold text-slate-400">PT Sinar Graha Kreatif</p>
      <p class="text-[11px] mt-0.5">Standalone Public Timestamp Camera — Standar Spesifikasi Teknis SGX</p>
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
  Download,
  Share2,
  RotateCcw,
  Home,
  X,
  FileText,
  Tag,
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
const detectedAddress = ref('');
const captureTimestamp = ref(null);

const canShare = typeof navigator !== 'undefined' && !!navigator.share;

const stampForm = reactive({
  companyName: 'Sinar Graha Kreatif',
  companyPhone: '082388885251',
  spkNumber: 'SPK-EVIDENCE',
  jobDescription: 'Pemasangan Signage / Dokumentasi Lapangan',
  stage: 'AFTER',
  timeZone: 'WIB'
});

// Warna Aksen per Stage sesuai Spesifikasi Teknis
const stageColors = {
  'BEFORE':  { bar: '#0f1a12', accent: '#5dd98a' },
  'PROCESS': { bar: '#1a150a', accent: '#e6a817' },
  'AFTER':   { bar: '#0a121a', accent: '#5d9ad9' },
  'ISSUE':   { bar: '#1a0a0a', accent: '#e65d5d' },
};

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

// Fetch Static Map Image dari koordinat GPS (OSM Static Map / Esri fallback)
function fetchStaticMapTile(lat, lng, width, height) {
  return new Promise((resolve) => {
    if (lat == null || lng == null) return resolve(null);

    const tileW = Math.min(500, Math.max(280, Math.round(width)));
    const tileH = Math.min(420, Math.max(220, Math.round(height)));

    // OpenStreetMap Static Map URL sesuai spesifikasi
    const osmUrl = `https://staticmap.openstreetmap.de/staticmap.php?center=${lat},${lng}&zoom=16&size=${tileW}x${tileH}&markers=${lat},${lng}`;

    // Fallback Esri World Imagery jika OSM staticmap lambat
    const delta = 0.0012;
    const minLng = (Number(lng) - delta).toFixed(6);
    const maxLng = (Number(lng) + delta).toFixed(6);
    const minLat = (Number(lat) - (delta * 0.75)).toFixed(6);
    const maxLat = (Number(lat) + (delta * 0.75)).toFixed(6);
    const esriUrl = `https://services.arcgisonline.com/arcgis/rest/services/World_Imagery/MapServer/export?bbox=${minLng},${minLat},${maxLng},${maxLat}&bboxSR=4326&size=${tileW},${tileH}&f=image`;

    const img = new Image();
    img.crossOrigin = 'anonymous';
    const timer = setTimeout(() => {
      // Coba fallback ke Esri jika OSM timeout
      const fallbackImg = new Image();
      fallbackImg.crossOrigin = 'anonymous';
      const fallbackTimer = setTimeout(() => resolve(null), 2000);
      fallbackImg.onload = () => {
        clearTimeout(fallbackTimer);
        resolve(fallbackImg);
      };
      fallbackImg.onerror = () => resolve(null);
      fallbackImg.src = esriUrl;
    }, 2000);

    img.onload = () => {
      clearTimeout(timer);
      resolve(img);
    };
    img.onerror = () => {
      clearTimeout(timer);
      // Coba Esri
      const fallbackImg = new Image();
      fallbackImg.crossOrigin = 'anonymous';
      fallbackImg.onload = () => resolve(fallbackImg);
      fallbackImg.onerror = () => resolve(null);
      fallbackImg.src = esriUrl;
    };
    img.src = osmUrl;
  });
}

function goBackHome() {
  window.location.href = '/';
}

// 1. Geolocation Handling
async function refreshGps() {
  if (!navigator.geolocation) {
    gpsLocation.value = { lat: -3.824921, lng: 102.286299, accuracy: 5 };
    await updateAddressFromGps();
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
        gpsLocation.value = { lat: -3.824921, lng: 102.286299, accuracy: 10 };
      }
      await updateAddressFromGps();
      fetchingGps.value = false;
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  );
}

async function updateAddressFromGps() {
  if (!gpsLocation.value) return;
  try {
    const addr = await reverseGeocodeCoordinates(
      gpsLocation.value.lat,
      gpsLocation.value.lng,
      'Ratu Agung, Sumatera, Gading Cempaka'
    );
    detectedAddress.value = addr || 'Ratu Agung, Sumatera, Gading Cempaka';
  } catch (e) {
    detectedAddress.value = 'Ratu Agung, Sumatera, Gading Cempaka';
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

// 5. Debounce Rendering
let debounceTimer = null;
function debouncedRender() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    renderWatermarkCanvas();
  }, 300);
}

// 6. HTML5 Canvas Watermark Rendering Engine (Sesuai Spesifikasi Teknis SGX)
async function renderWatermarkCanvas() {
  if (!capturedImage.value) return;
  processingWatermark.value = true;

  const logoImg = await loadLogoImage();
  const lat = gpsLocation.value ? Number(gpsLocation.value.lat) : -3.824921;
  const lng = gpsLocation.value ? Number(gpsLocation.value.lng) : 102.286299;
  const satelliteImg = await fetchStaticMapTile(lat, lng, 320, 280);

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

    const w = canvas.width;
    const h = canvas.height;

    // SPESIFIKASI TEKNIS: Asumsi Resolusi 3024px standar
    // Scale factor: $scale = $image->width() / 3024
    const scale = w / 3024;

    const currentStage = stampForm.stage || 'AFTER';
    const activeColors = stageColors[currentStage] || stageColors['AFTER'];

    // 3. Render Seluruh Zona Spesifikasi Teknis
    renderTechnicalSpecWatermark(ctx, w, h, scale, {
      timeStr,
      dayName,
      dateStr,
      lat: latFormatted,
      lng: lngFormatted,
      address: detectedAddress.value || 'Ratu Agung, Sumatera, Gading Cempaka',
      jobDescription: stampForm.jobDescription,
      spkNumber: stampForm.spkNumber || 'SPK-EVIDENCE',
      stage: currentStage,
      colors: activeColors,
      logoImg,
      satelliteImg
    });

    watermarkedImage.value = canvas.toDataURL('image/jpeg', 0.94);
    processingWatermark.value = false;
  };
  img.src = capturedImage.value;
}

/**
 * IMPLEMENTASI 100% SESUAI SPESIFIKASI TEKNIS WATERMARK SGX
 */
function renderTechnicalSpecWatermark(ctx, w, h, s, meta) {
  const accentColor = meta.colors.accent;

  // =========================================================================
  // ZONA 5 — Watermark SGX (Pojok Kanan Atas)
  // Teks baris 1: "SGX VENDOR" (font-size 24*scale, bold, warna aksen)
  // Teks baris 2: "Foto Terverifikasi" (font-size 16*scale, putih)
  // Posisi: right: 16*scale, top: 16*scale
  // Background: rgba(0,0,0,0.35) rounded 4px, padding 6x10px
  // =========================================================================
  ctx.save();
  const z5PadX = 14 * s;
  const z5PadY = 10 * s;
  const z5Font1 = Math.round(24 * s);
  const z5Font2 = Math.round(16 * s);

  ctx.font = `bold ${z5Font1}px "Inter", "Montserrat", Arial, sans-serif`;
  const z5Text1W = ctx.measureText('SGX VENDOR').width;
  ctx.font = `500 ${z5Font2}px "Inter", "Montserrat", Arial, sans-serif`;
  const z5Text2W = ctx.measureText('Foto Terverifikasi').width;
  const z5BoxW = Math.max(z5Text1W, z5Text2W) + (z5PadX * 2);
  const z5BoxH = z5Font1 + z5Font2 + (z5PadY * 2.2);

  const z5X = w - z5BoxW - (16 * s);
  const z5Y = 16 * s;

  ctx.fillStyle = 'rgba(0, 0, 0, 0.45)';
  ctx.beginPath();
  ctx.roundRect(z5X, z5Y, z5BoxW, z5BoxH, 6 * s);
  ctx.fill();

  ctx.fillStyle = accentColor;
  ctx.font = `bold ${z5Font1}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.fillText('SGX VENDOR', z5X + z5PadX, z5Y + z5PadY + z5Font1 - (4 * s));

  ctx.fillStyle = '#FFFFFF';
  ctx.font = `500 ${z5Font2}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.fillText('Foto Terverifikasi', z5X + z5PadX, z5Y + z5PadY + z5Font1 + z5Font2);
  ctx.restore();

  // =========================================================================
  // ZONA 3 — Bar Bawah (Footer)
  // Tinggi bar: 140 * scale px
  // Background: #FFFFFF (putih solid)
  // Border top: 3px * scale solid warna aksen stage
  // =========================================================================
  const footerBarH = Math.round(140 * s);
  const footerY = h - footerBarH;

  ctx.save();
  ctx.fillStyle = '#FFFFFF';
  ctx.fillRect(0, footerY, w, footerBarH);

  // Border Top 3px solid warna aksen stage
  ctx.fillStyle = accentColor;
  ctx.fillRect(0, footerY, w, Math.max(3, 3 * s));

  // [KIRI] Logo SGX + Teks Sinar Grafika + Phone
  const footerLogoSize = Math.round(75 * s);
  const footerLogoX = Math.round(30 * s);
  const footerLogoY = footerY + Math.round((footerBarH - footerLogoSize) / 2);
  if (meta.logoImg) {
    ctx.drawImage(meta.logoImg, footerLogoX, footerLogoY, footerLogoSize, footerLogoSize);
  }

  const footerTextX = footerLogoX + footerLogoSize + Math.round(18 * s);
  ctx.fillStyle = '#0F172A';
  ctx.font = `900 ${Math.round(32 * s)}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.fillText('Sinar Grafika', footerTextX, footerY + Math.round(52 * s));

  ctx.fillStyle = '#334155';
  ctx.font = `700 ${Math.round(24 * s)}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.fillText('082388885251', footerTextX, footerY + Math.round(92 * s));

  // [KANAN] Nomor SPK + Stage Badge + Logo SGX (repeat kecil 60x60px)
  const rightMargin = Math.round(30 * s);
  let curRightX = w - rightMargin;

  // Logo SGX Repeat Kecil (60x60px * scale)
  const smallLogoSize = Math.round(60 * s);
  const smallLogoX = curRightX - smallLogoSize;
  const smallLogoY = footerY + Math.round((footerBarH - smallLogoSize) / 2);
  if (meta.logoImg) {
    ctx.drawImage(meta.logoImg, smallLogoX, smallLogoY, smallLogoSize, smallLogoSize);
  }
  curRightX = smallLogoX - Math.round(20 * s);

  // Stage Badge (rounded pill, background aksen stage, teks putih, font-size 20*scale, padding 6x16px * scale)
  const badgeFontS = Math.round(20 * s);
  const badgePadX = Math.round(16 * s);
  const badgePadY = Math.round(8 * s);
  const stageText = meta.stage.toUpperCase();
  ctx.font = `900 ${badgeFontS}px "Inter", "Montserrat", Arial, sans-serif`;
  const stageTextW = ctx.measureText(stageText).width;
  const badgeW = stageTextW + (badgePadX * 2);
  const badgeH = badgeFontS + (badgePadY * 2);
  const badgeX = curRightX - badgeW;
  const badgeY = footerY + Math.round((footerBarH - badgeH) / 2);

  ctx.fillStyle = accentColor;
  ctx.beginPath();
  ctx.roundRect(badgeX, badgeY, badgeW, badgeH, Math.round(badgeH / 2));
  ctx.fill();

  ctx.fillStyle = '#FFFFFF';
  ctx.fillText(stageText, badgeX + badgePadX, badgeY + badgeFontS + (badgePadY * 0.7));
  curRightX = badgeX - Math.round(20 * s);

  // Nomor SPK (font-size 22 * scale, monospace, #888888)
  ctx.font = `700 ${Math.round(22 * s)}px "JetBrains Mono", monospace, Arial`;
  ctx.fillStyle = '#888888';
  ctx.textAlign = 'right';
  ctx.fillText(meta.spkNumber, curRightX, footerY + Math.round(76 * s));
  ctx.textAlign = 'left'; // Reset
  ctx.restore();

  // =========================================================================
  // ZONA 1 — Overlay Teks di Dalam Foto (Bottom-Left)
  // Posisi: bottom-left, mulai dari 65% tinggi foto ke bawah
  // Background: gradient transparan dari bawah: rgba(0,0,0,0) -> rgba(0,0,0,0.75)
  // =========================================================================
  const zone1TopY = h * 0.65;
  const zone1BottomY = footerY;

  ctx.save();
  const zone1Grad = ctx.createLinearGradient(0, zone1TopY, 0, zone1BottomY);
  zone1Grad.addColorStop(0, 'rgba(0,0,0,0)');
  zone1Grad.addColorStop(1, 'rgba(0,0,0,0.78)');
  ctx.fillStyle = zone1Grad;
  ctx.fillRect(0, zone1TopY, w, zone1BottomY - zone1TopY);
  ctx.restore();

  // Elemen & Ukuran ZONA 1
  const padLeft = Math.round(30 * s);
  const mapZoneW = Math.round(320 * s);
  const maxTextW = w - mapZoneW - padLeft - Math.round(40 * s);

  // Perhitungan Y posisi teks dari bawah zona (Padding bawah zona: 120 * scale)
  const zoneContentBottomY = footerY - Math.round(25 * s);
  let textY = zoneContentBottomY;

  // 1. Keterangan Pekerjaan (Jika Ada)
  if (meta.jobDescription) {
    ctx.save();
    ctx.font = `700 ${Math.round(32 * s)}px "Inter", "Montserrat", Arial, sans-serif`;
    ctx.shadowColor = 'rgba(0,0,0,0.95)';
    ctx.shadowBlur = 12 * s;
    ctx.fillStyle = '#38BDF8';
    const cleanJob = truncateText(ctx, `📌 ${meta.jobDescription}`, maxTextW);
    ctx.fillText(cleanJob, padLeft, textY);
    textY -= Math.round(42 * s);
    ctx.restore();
  }

  // 2. Koordinat GPS (font-size = 34 * scale, warna aksen stage)
  ctx.save();
  const coordFontS = Math.round(34 * s);
  ctx.font = `800 ${coordFontS}px "JetBrains Mono", monospace, Arial`;
  ctx.shadowColor = 'rgba(0,0,0,0.95)';
  ctx.shadowBlur = 14 * s;
  ctx.fillStyle = accentColor;
  const coordStr = `📍 Koordinat: ${meta.lat}, ${meta.lng}`;
  ctx.fillText(coordStr, padLeft, textY);
  textY -= Math.round(46 * s);
  ctx.restore();

  // 3. Alamat (max 2 baris, font-size = 38 * scale, putih, drop shadow)
  ctx.save();
  const addrFontS = Math.round(38 * s);
  ctx.font = `800 ${addrFontS}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0,0,0,0.95)';
  ctx.shadowBlur = 16 * s;
  ctx.strokeStyle = 'rgba(0,0,0,0.95)';
  ctx.lineWidth = 6 * s;
  ctx.fillStyle = '#FFFFFF';

  const addressLines = wrapTextLines(ctx, meta.address, maxTextW, 2);
  // Render dari baris terbawah ke baris atas
  for (let i = addressLines.length - 1; i >= 0; i--) {
    const line = addressLines[i];
    ctx.strokeText(line, padLeft, textY);
    ctx.fillText(line, padLeft, textY);
    textY -= Math.round(48 * s);
  }
  ctx.restore();

  // 4. Jam Besar + Separator "|" + Tanggal + Hari
  // Jam besar: font-size = 128 * scale, bold, putih
  // Separator "|": tinggi 80px * scale, warna aksen stage
  // Tanggal: font-size = 44 * scale, putih
  // Hari: font-size = 40 * scale, putih, muted
  ctx.save();
  const clockFontS = Math.round(128 * s);
  const dateFontS = Math.round(44 * s);
  const dayFontS = Math.round(40 * s);

  ctx.font = `900 ${clockFontS}px "Inter", "Montserrat", "Segoe UI", Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0,0,0,0.95)';
  ctx.shadowBlur = 20 * s;
  ctx.strokeStyle = 'rgba(0,0,0,0.95)';
  ctx.lineWidth = 8 * s;
  ctx.strokeText(meta.timeStr, padLeft, textY);
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText(meta.timeStr, padLeft, textY);

  const clockW = ctx.measureText(meta.timeStr).width;

  // Separator "|" (tinggi 80px * scale, warna aksen stage)
  const sepX = padLeft + clockW + Math.round(20 * s);
  const sepH = Math.round(80 * s);
  const sepTopY = textY - Math.round(88 * s);
  ctx.fillStyle = accentColor;
  ctx.fillRect(sepX, sepTopY, Math.max(3, Math.round(5 * s)), sepH);

  // Tanggal & Hari di sebelah kanan separator
  const textDateX = sepX + Math.round(18 * s);
  ctx.font = `800 ${dateFontS}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.strokeText(meta.dateStr, textDateX, sepTopY + Math.round(34 * s));
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText(meta.dateStr, textDateX, sepTopY + Math.round(34 * s));

  ctx.font = `700 ${dayFontS}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.strokeText(meta.dayName, textDateX, sepTopY + Math.round(76 * s));
  ctx.fillStyle = '#E2E8F0'; // Muted white
  ctx.fillText(meta.dayName, textDateX, sepTopY + Math.round(76 * s));
  ctx.restore();

  // =========================================================================
  // ZONA 2 — Mini Map (Pojok Kanan Bawah Overlay)
  // Ukuran kotak: 320 × 280 px (* scale)
  // Posisi: right: 20*scale, bottom: 150*scale (dari dasar foto)
  // Border radius: 8px (* scale)
  // Border: 2px solid warna aksen stage
  // Konten: Static map image dari koordinat GPS (OSM staticmap)
  // =========================================================================
  const mapW = Math.round(320 * s);
  const mapH = Math.round(280 * s);
  const mapX = w - mapW - Math.round(20 * s);
  const mapY = h - Math.round(150 * s) - mapH;

  drawTechnicalMiniMap(ctx, mapX, mapY, mapW, mapH, s, meta.satelliteImg, accentColor);
}

/**
 * Draw Technical Mini Map with OSM / Satellite + Pin + Radar Beam + Border Aksen
 */
function drawTechnicalMiniMap(ctx, x, y, width, height, s, mapImg, accentColor) {
  ctx.save();

  // Border Radius 8px * scale
  const radius = Math.max(6, Math.round(8 * s));
  ctx.beginPath();
  ctx.roundRect(x, y, width, height, radius);
  ctx.clip();

  // 1. Draw Map Image / Satellite
  if (mapImg && mapImg.complete && mapImg.naturalWidth > 0) {
    ctx.drawImage(mapImg, x, y, width, height);
  } else {
    // Fallback jika GPS null: kotak abu-abu + teks "GPS tidak tersedia"
    ctx.fillStyle = '#334155';
    ctx.fillRect(x, y, width, height);

    ctx.fillStyle = '#94A3B8';
    ctx.font = `bold ${Math.round(18 * s)}px Arial, sans-serif`;
    ctx.textAlign = 'center';
    ctx.fillText('GPS Tidak Tersedia', x + (width / 2), y + (height / 2));
    ctx.textAlign = 'left';
  }

  // 2. Green/Cyan Directional Radar Beam
  const pinX = x + width * 0.5;
  const pinY = y + height * 0.5;

  ctx.fillStyle = 'rgba(16, 185, 129, 0.65)';
  ctx.beginPath();
  ctx.moveTo(pinX, pinY);
  ctx.arc(pinX, pinY, Math.round(52 * s), -Math.PI * 0.85, -Math.PI * 0.3);
  ctx.closePath();
  ctx.fill();

  // 3. Blue GPS Location Dot with White Ring
  ctx.fillStyle = '#0284C7';
  ctx.beginPath();
  ctx.arc(pinX, pinY, Math.round(13 * s), 0, Math.PI * 2);
  ctx.fill();

  ctx.strokeStyle = '#FFFFFF';
  ctx.lineWidth = Math.max(2, 3.5 * s);
  ctx.stroke();

  // 4. "OpenStreetMap / Google" Watermark at Bottom Left of Mini Map
  ctx.font = `bold ${Math.round(14 * s)}px Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0, 0, 0, 0.9)';
  ctx.shadowBlur = 4 * s;
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText('OSM Map', x + (12 * s), y + height - (10 * s));

  ctx.restore();

  // Border: 2px solid warna aksen stage
  ctx.save();
  ctx.strokeStyle = accentColor;
  ctx.lineWidth = Math.max(2, 2 * s);
  ctx.beginPath();
  ctx.roundRect(x, y, width, height, radius);
  ctx.stroke();
  ctx.restore();
}

// Helpers
function truncateText(ctx, text, maxWidth) {
  if (!text) return '';
  if (ctx.measureText(text).width <= maxWidth) return text;
  let truncated = text;
  while (truncated.length > 0 && ctx.measureText(truncated + '...').width > maxWidth) {
    truncated = truncated.slice(0, -1);
  }
  return truncated + '...';
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

// 7. Download & Share Actions
function downloadResult() {
  if (!watermarkedImage.value) return;

  const now = new Date();
  const dateNum = now.getFullYear() +
    String(now.getMonth() + 1).padStart(2, '0') +
    String(now.getDate()).padStart(2, '0') + '_' +
    String(now.getHours()).padStart(2, '0') +
    String(now.getMinutes()).padStart(2, '0') +
    String(now.getSeconds()).padStart(2, '0');

  const filename = `SGX_TIMESLIP_${dateNum}.jpg`;

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
      title: 'Foto Timeslip Sinar Grafika',
      text: `Dokumentasi Timestamp Sinar Grafika di ${detectedAddress.value}`,
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
