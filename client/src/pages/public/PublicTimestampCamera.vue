<template>
  <div class="h-screen max-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-purple-500 selection:text-white flex flex-col overflow-hidden">
    
    <!-- Top Header Bar with Integrated Verified Location -->
    <header class="border-b border-slate-800 bg-slate-900/95 backdrop-blur-md shrink-0 px-3 sm:px-4 py-2.5 shadow-md z-40">
      <div class="max-w-5xl mx-auto flex items-center justify-between gap-3">
        <!-- Logo & Title -->
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-gradient-to-tr from-amber-500 via-purple-700 to-indigo-800 flex items-center justify-center text-white font-black text-xs sm:text-sm shadow-md shadow-amber-500/20 shrink-0">
            SGX
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-1.5">
              <h1 class="font-extrabold text-xs sm:text-sm text-white tracking-wide truncate">TIMESLIP CAMERA</h1>
              <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1 animate-pulse"></span>
                GPS
              </span>
            </div>
            <!-- Integrated Verified Location Subtitle in Header -->
            <p class="text-[10px] text-emerald-400 font-medium truncate flex items-center gap-1" :title="detectedAddress">
              <MapPin class="w-3 h-3 text-emerald-400 shrink-0" />
              <span>{{ detectedAddress || 'Mendeteksi alamat satelit...' }}</span>
            </p>
          </div>
        </div>

        <!-- Header Actions -->
        <div class="flex items-center gap-2 shrink-0">
          <button
            type="button"
            @click="refreshGps"
            class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-all border border-slate-700 cursor-pointer active:scale-95"
            title="Segarkan GPS"
          >
            <RefreshCw class="w-3.5 h-3.5 text-amber-400" :class="{ 'animate-spin': fetchingGps }" />
          </button>
          <button
            type="button"
            @click="goBackHome"
            class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold transition-all border border-slate-700 flex items-center gap-1 cursor-pointer active:scale-95"
          >
            <Home class="w-3.5 h-3.5" />
            <span class="hidden sm:inline">Beranda</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content Area (No-Scroll Viewport Layout) -->
    <main class="flex-1 max-w-5xl w-full mx-auto p-3 sm:p-4 flex flex-col min-h-0">
      
      <!-- STATE 1: CAMERA & UPLOAD VIEWFINDER (Belum Ambil Foto) -->
      <div v-if="!capturedImage" class="flex-1 flex flex-col min-h-0 space-y-2.5">
        
        <!-- Compact Input Keterangan Pekerjaan (Default Kosong) -->
        <div class="shrink-0 relative">
          <div class="relative flex items-center">
            <div class="absolute left-3 text-slate-400 pointer-events-none">
              <FileText class="w-3.5 h-3.5 text-purple-400" />
            </div>
            <input
              type="text"
              v-model="stampForm.jobDescription"
              placeholder="Ketik Keterangan Pekerjaan (Opsional)"
              class="w-full pl-9 pr-3 py-2 bg-slate-900 border border-slate-800 rounded-xl focus:border-purple-500 focus:outline-none text-white text-xs font-medium placeholder-slate-500 shadow-inner"
            />
          </div>
        </div>

        <!-- Focus Viewfinder Box (Fills Remaining Screen Height without Scrolling) -->
        <div class="flex-1 relative bg-black rounded-2xl border border-slate-800 overflow-hidden shadow-2xl flex items-center justify-center min-h-0">
          
          <!-- Live Video Stream -->
          <video
            v-show="isCameraActive"
            ref="videoRef"
            autoplay
            playsinline
            muted
            class="w-full h-full object-cover"
          ></video>

          <!-- Camera Inactive Placeholder -->
          <div v-if="!isCameraActive" class="p-6 text-center space-y-3 max-w-sm">
            <div class="w-14 h-14 rounded-2xl bg-slate-900 border border-slate-700 text-slate-400 flex items-center justify-center mx-auto shadow-inner">
              <Camera class="w-7 h-7 text-purple-400" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-white">Kamera Siap Digunakan</h3>
              <p class="text-[11px] text-slate-400 mt-0.5">Buka kamera langsung atau pilih foto dari galeri. Lokasi & waktu otomatis tercetak di bawah foto.</p>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-2.5 pt-1">
              <button
                type="button"
                @click="startCamera"
                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-700 to-indigo-600 hover:from-purple-600 hover:to-indigo-500 text-white text-xs font-bold flex items-center gap-2 shadow-lg shadow-purple-900/30 active:scale-95 transition-all cursor-pointer"
              >
                <Camera class="w-4 h-4" />
                <span>Buka Kamera</span>
              </button>
              <label class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold flex items-center gap-2 border border-slate-700 active:scale-95 transition-all cursor-pointer">
                <Upload class="w-4 h-4 text-emerald-400" />
                <span>Pilih Galeri</span>
                <input type="file" accept="image/*" class="hidden" @change="handleFileSelected" />
              </label>
            </div>
          </div>

          <!-- Camera Live Controls Overlay (Ketika Kamera Aktif) -->
          <div v-if="isCameraActive" class="absolute top-3 inset-x-3 flex items-center justify-between z-20">
            <!-- GPS Status Pill -->
            <div class="px-2.5 py-1 rounded-full bg-slate-900/85 backdrop-blur-md border border-slate-700 text-[10px] font-mono flex items-center gap-1.5 shadow-md text-white">
              <MapPin class="w-3 h-3" :class="gpsLocation ? 'text-emerald-400' : 'text-amber-400 animate-pulse'" />
              <span>{{ gpsLocation ? `${gpsLocation.lat.toFixed(5)}, ${gpsLocation.lng.toFixed(5)}` : 'Mencari GPS...' }}</span>
            </div>

            <!-- Switch Camera Toggle & Close Button -->
            <div class="flex items-center gap-1.5">
              <button
                type="button"
                @click="toggleCameraFacing"
                class="p-2 rounded-full bg-slate-900/85 hover:bg-slate-800 text-white backdrop-blur-md border border-slate-700 shadow-md active:scale-90 transition-all cursor-pointer"
                title="Ganti Kamera Depan/Belakang"
              >
                <SwitchCamera class="w-3.5 h-3.5" />
              </button>
              <button
                type="button"
                @click="stopCamera"
                class="p-2 rounded-full bg-rose-600/80 hover:bg-rose-700 text-white backdrop-blur-md border border-white/20 shadow-md active:scale-90 transition-all cursor-pointer"
                title="Tutup Kamera"
              >
                <X class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

          <!-- Capture Shutter Button (Bottom Center Overlay) -->
          <div v-if="isCameraActive" class="absolute bottom-5 inset-x-0 flex items-center justify-center gap-5 z-20">
            <label class="p-3 rounded-full bg-slate-900/85 hover:bg-slate-800 text-slate-300 backdrop-blur-md border border-slate-700 shadow-lg cursor-pointer active:scale-90 transition-all" title="Upload dari File">
              <Upload class="w-4 h-4 text-emerald-400" />
              <input type="file" accept="image/*" class="hidden" @change="handleFileSelected" />
            </label>

            <!-- Big Shutter Button -->
            <button
              type="button"
              @click="captureSnapshot"
              class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-white p-1 shadow-2xl shadow-purple-500/40 border-4 border-purple-600 hover:scale-105 active:scale-95 transition-all cursor-pointer flex items-center justify-center"
            >
              <div class="w-full h-full rounded-full bg-gradient-to-tr from-purple-700 to-indigo-600 flex items-center justify-center text-white">
                <Camera class="w-6 h-6 sm:w-7 sm:h-7" />
              </div>
            </button>

            <button
              type="button"
              @click="refreshGps"
              class="p-3 rounded-full bg-slate-900/85 hover:bg-slate-800 text-slate-300 backdrop-blur-md border border-slate-700 shadow-lg cursor-pointer active:scale-90 transition-all"
              title="Perbarui GPS"
            >
              <RefreshCw class="w-4 h-4 text-amber-400" :class="{ 'animate-spin': fetchingGps }" />
            </button>
          </div>
        </div>

      </div>

      <!-- STATE 2: PREVIEW & RESULT STAGE (Setelah Ambil/Pilih Foto) -->
      <div v-else class="flex-1 flex flex-col min-h-0 space-y-3 animate-scale-up">
        
        <!-- Processed Watermarked Image Preview -->
        <div class="flex-1 bg-slate-900 border border-slate-800 rounded-2xl p-3 shadow-2xl flex flex-col items-center justify-center min-h-0 overflow-hidden">
          <div class="w-full h-full rounded-xl overflow-hidden shadow-2xl border border-slate-700 bg-black relative flex items-center justify-center">
            <img
              :src="watermarkedImage"
              alt="Hasil Watermark Timestamp"
              class="w-full h-full object-contain mx-auto"
            />
            
            <div v-if="processingWatermark" class="absolute inset-0 bg-slate-950/85 backdrop-blur-xs flex flex-col items-center justify-center space-y-2 text-xs text-white">
              <Loader2 class="w-8 h-8 animate-spin text-purple-500" />
              <p class="font-bold">Menerapkan Stempel Sinar Grafika...</p>
            </div>
          </div>
        </div>

        <!-- Quick Actions Bar -->
        <div class="shrink-0 flex flex-wrap items-center justify-center gap-2.5 w-full">
          <button
            type="button"
            @click="downloadResult"
            class="flex-1 min-w-[180px] px-5 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white text-xs font-black flex items-center justify-center gap-2 shadow-lg shadow-emerald-900/30 active:scale-95 transition-all cursor-pointer"
          >
            <Download class="w-4 h-4" />
            <span>Simpan ke Galeri / Unduh</span>
          </button>

          <button
            v-if="canShare"
            type="button"
            @click="shareResult"
            class="px-4 py-3 rounded-xl bg-gradient-to-r from-purple-700 to-indigo-600 hover:from-purple-600 hover:to-indigo-500 text-white text-xs font-bold flex items-center justify-center gap-2 shadow-lg shadow-purple-900/30 active:scale-95 transition-all cursor-pointer"
          >
            <Share2 class="w-4 h-4" />
            <span>Bagikan</span>
          </button>

          <button
            type="button"
            @click="resetCapture"
            class="px-4 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold flex items-center justify-center gap-2 border border-slate-700 active:scale-95 transition-all cursor-pointer"
          >
            <RotateCcw class="w-4 h-4" />
            <span>Ambil Ulang</span>
          </button>
        </div>

      </div>

    </main>

    <!-- Hidden Rendering Canvas -->
    <canvas ref="canvasRef" class="hidden"></canvas>

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
  companyName: 'Sinar Grafika',
  companyPhone: '082388885251',
  jobDescription: '', // Default Kosong sesuai permintaan user
  timeZone: 'WIB'
});

// Cache Logos
let cachedLogoImg = null;
let cachedBannerLogoImg = null;

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

function loadBannerLogoImage() {
  return new Promise((resolve) => {
    if (cachedBannerLogoImg && cachedBannerLogoImg.complete && cachedBannerLogoImg.naturalWidth > 0) {
      return resolve(cachedBannerLogoImg);
    }
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
      cachedBannerLogoImg = img;
      resolve(img);
    };
    img.onerror = () => resolve(null);
    img.src = '/sgx_banner_logo.png';
  });
}

// Fetch 100% Real Satellite Imagery Tile from Esri World Imagery
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

let gpsWatchId = null;

// 1. Geolocation Handling: Two-Pass Fast-Lock & Continuous Watcher
async function initFastGpsLock() {
  // Pass 0: Instant Cache from Local Storage (0 ms)
  try {
    const cachedGps = localStorage.getItem('sgx_last_gps');
    const cachedAddr = localStorage.getItem('sgx_last_address');
    if (cachedGps && !gpsLocation.value) {
      gpsLocation.value = JSON.parse(cachedGps);
    }
    if (cachedAddr && !detectedAddress.value) {
      detectedAddress.value = cachedAddr;
    }
  } catch (e) {}

  if (!navigator.geolocation) {
    if (!gpsLocation.value) {
      gpsLocation.value = { lat: -3.824921, lng: 102.286299, accuracy: 5 };
      await updateAddressFromGps();
    }
    return;
  }

  fetchingGps.value = true;

  // Pass 1: Quick Position (Max Age 120s, Timeout 3s - Instant response from OS/WiFi/BTS)
  navigator.geolocation.getCurrentPosition(
    async (pos) => {
      gpsLocation.value = {
        lat: pos.coords.latitude,
        lng: pos.coords.longitude,
        accuracy: Math.round(pos.coords.accuracy || 5)
      };
      saveGpsCache();
      await updateAddressFromGps();
      fetchingGps.value = false;
    },
    (err) => {
      console.warn('Fast GPS error:', err);
    },
    { enableHighAccuracy: false, timeout: 3000, maximumAge: 120000 }
  );

  // Pass 2: High-Accuracy Continuous Watcher (Live Satellite GPS Keep-Alive)
  startContinuousGpsWatcher();
}

function startContinuousGpsWatcher() {
  if (!navigator.geolocation) return;
  if (gpsWatchId) {
    navigator.geolocation.clearWatch(gpsWatchId);
  }

  gpsWatchId = navigator.geolocation.watchPosition(
    async (pos) => {
      const newLat = pos.coords.latitude;
      const newLng = pos.coords.longitude;
      const newAcc = Math.round(pos.coords.accuracy || 5);

      // Update position
      const prev = gpsLocation.value;
      gpsLocation.value = { lat: newLat, lng: newLng, accuracy: newAcc };
      saveGpsCache();
      fetchingGps.value = false;

      // If moved > 15 meters or no address yet, re-geocode
      if (!prev || Math.abs(prev.lat - newLat) > 0.00015 || Math.abs(prev.lng - newLng) > 0.00015 || !detectedAddress.value) {
        await updateAddressFromGps();
      }
    },
    (err) => {
      console.warn('Continuous GPS watch error:', err);
      fetchingGps.value = false;
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 5000 }
  );
}

function saveGpsCache() {
  try {
    if (gpsLocation.value) {
      localStorage.setItem('sgx_last_gps', JSON.stringify(gpsLocation.value));
    }
    if (detectedAddress.value) {
      localStorage.setItem('sgx_last_address', detectedAddress.value);
    }
  } catch (e) {}
}

async function refreshGps() {
  fetchingGps.value = true;
  if (!navigator.geolocation) {
    fetchingGps.value = false;
    return;
  }

  navigator.geolocation.getCurrentPosition(
    async (pos) => {
      gpsLocation.value = {
        lat: pos.coords.latitude,
        lng: pos.coords.longitude,
        accuracy: Math.round(pos.coords.accuracy || 5)
      };
      saveGpsCache();
      await updateAddressFromGps();
      fetchingGps.value = false;
    },
    (err) => {
      console.warn('Manual GPS refresh error:', err);
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
      gpsLocation.value.lng
    );
    if (addr) {
      detectedAddress.value = addr;
      saveGpsCache();
    }
  } catch (e) {
    console.warn('Geocoding failed:', e);
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

// 5. HTML5 Canvas Watermark Rendering Engine (Bottom Bar 400px & Flexible Map)
async function renderWatermarkCanvas() {
  if (!capturedImage.value) return;
  processingWatermark.value = true;

  const logoImg = await loadLogoImage();
  const bannerLogoImg = await loadBannerLogoImage();
  const lat = gpsLocation.value ? Number(gpsLocation.value.lat) : -3.824921;
  const lng = gpsLocation.value ? Number(gpsLocation.value.lng) : 102.286299;
  const satelliteImg = await fetchRealSatelliteTile(lat, lng, 340, 260);

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
    const s = Math.max(1.0, w / 1000);

    // 3. Render Bottom Full-Width Watermark Bar (400px Scaled)
    renderBottomBarWatermark(ctx, w, h, s, {
      timeStr,
      dayName,
      dateStr,
      lat: latFormatted,
      lng: lngFormatted,
      address: detectedAddress.value || `Area Koordinat (${latFormatted}, ${lngFormatted})`,
      jobDescription: stampForm.jobDescription ? stampForm.jobDescription.trim() : '',
      logoImg,
      bannerLogoImg,
      satelliteImg
    });

    watermarkedImage.value = canvas.toDataURL('image/jpeg', 0.94);
    processingWatermark.value = false;
  };
  img.src = capturedImage.value;
}

/**
 * FULL-WIDTH BOTTOM WATERMARK BAR (TINGGI 400PX - TRANSPARAN TANPA BACKGROUND GELAP)
 * - Bar Penuh di BAWAH Foto (Lebar 100% Foto)
 * - Tinggi Bar: 400px (Scaled)
 * - Background: 100% Transparan
 * - Pembagian Lebar: 70% Tulisan (4 Baris Fleksibel) | 30% Google Satellite Mini-Map (Ukuran Fleksibel)
 * - Baris 1: Jam Digital (100px) + Garis Emas + Tanggal (40px) & Hari (28px)
 * - Baris 2: Nama Jalan & Alamat Lengkap Google Maps (Lebar Fleksibel 70%)
 * - Baris 3: Titik Koordinat GPS Fleksibel (📍 Lat, Lng)
 * - Baris 4: Tag Keterangan Pekerjaan Fleksibel 35px (📌 Job Description)
 * - Footer Strip Putih di Dasar Foto (vendor.sinargrafika.my.id)
 */
function renderBottomBarWatermark(ctx, w, h, s, meta) {
  // 1. Draw Top-Left Transparan & Samar SINAR GRAFIKA Banner Logo
  const topLogoX = Math.round(24 * s);
  const topLogoY = Math.round(24 * s);

  if (meta.bannerLogoImg) {
    const bannerW = Math.round(270 * s);
    const aspect = meta.bannerLogoImg.naturalHeight / (meta.bannerLogoImg.naturalWidth || 1);
    const bannerH = Math.round(bannerW * (aspect || 0.26));

    ctx.save();
    ctx.globalAlpha = 0.52; // Samar & Transparan elegan
    ctx.shadowColor = 'rgba(0, 0, 0, 0.6)';
    ctx.shadowBlur = 8 * s;
    ctx.drawImage(meta.bannerLogoImg, topLogoX, topLogoY, bannerW, bannerH);
    ctx.restore();
  } else if (meta.logoImg) {
    const topLogoSize = Math.round(90 * s);
    ctx.save();
    ctx.globalAlpha = 0.52;
    drawRoundedImage(ctx, meta.logoImg, topLogoX, topLogoY, topLogoSize, topLogoSize, 18 * s);
    ctx.restore();
  }

  // 2. Bar Dimensions at Bottom of Photo (Total Tinggi 440px untuk Jarak Baris Lega)
  const totalBarH = Math.round(440 * s); // Total Tinggi Bar 440px
  const footerBarH = Math.round(85 * s);  // White branding footer strip (85px)
  const mainBarH = totalBarH - footerBarH; // Main content area (355px)
  const barY = h - totalBarH;

  // Background 100% Transparan (Tanpa Background Gelap)

  // 3. Pembagian Kolom Lebar 70% : 30% (70% Tulisan : 30% Google Map Fleksibel)
  const leftColW = w * 0.70;  // 70% Lebar untuk Tulisan
  const rightColW = w * 0.30; // 30% Lebar untuk Google Map

  // A. Mini Map Satellite di Sisi Kanan (Dengan Jarak Jelas ke Bagian Footer)
  const mapPadX = Math.round(14 * s);
  const mapPadTop = Math.round(14 * s);
  const mapPadBottom = Math.round(20 * s); // Jarak lega antara map dan footer strip
  const mapH = mainBarH - mapPadTop - mapPadBottom;
  const mapW = rightColW - (mapPadX * 2);
  const mapX = leftColW + mapPadX;
  const mapY = barY + mapPadTop;

  // B. Area Tulisan di Sisi Kiri (100% Lebar dari Kolom 70%)
  const textMarginL = Math.round(20 * s);
  const maxTextW = leftColW - textMarginL - Math.round(10 * s); // 100% lebar dari kolom kiri 70%

  // 4. BARIS 1: Jam Digital (100px) + Garis Emas Fleksibel + Tanggal (40px) & Hari (28px)
  let curY = barY + Math.round(80 * s);
  ctx.save();
  const clockFontS = Math.round(100 * s);
  ctx.font = `900 ${clockFontS}px "Inter", "Montserrat", "Segoe UI", Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0, 0, 0, 0.95)';
  ctx.shadowBlur = 18 * s;
  ctx.strokeStyle = 'rgba(0, 0, 0, 0.95)';
  ctx.lineWidth = 8 * s;
  ctx.strokeText(meta.timeStr, textMarginL, curY);
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText(meta.timeStr, textMarginL, curY);

  const timeW = ctx.measureText(meta.timeStr).width;

  // Vertical Gold Separator (Membentang Sepanjang Jam secara Fleksibel)
  const sepX = textMarginL + timeW + Math.round(16 * s);
  const sepH = Math.round(76 * s);
  const sepTopY = curY - Math.round(72 * s);
  ctx.fillStyle = '#EAB308';
  ctx.fillRect(sepX, sepTopY, Math.round(5.5 * s), sepH);

  // Tanggal (40px Putih) & Hari (28px Emas #FDE047)
  const dateTextX = sepX + Math.round(16 * s);
  ctx.font = `800 ${Math.round(40 * s)}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0, 0, 0, 0.95)';
  ctx.shadowBlur = 14 * s;
  ctx.strokeStyle = 'rgba(0, 0, 0, 0.95)';
  ctx.lineWidth = 6 * s;
  ctx.strokeText(meta.dateStr, dateTextX, sepTopY + Math.round(36 * s));
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText(meta.dateStr, dateTextX, sepTopY + Math.round(36 * s));

  ctx.font = `800 ${Math.round(28 * s)}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.strokeText(meta.dayName, dateTextX, sepTopY + Math.round(72 * s));
  ctx.fillStyle = '#FDE047';
  ctx.fillText(meta.dayName, dateTextX, sepTopY + Math.round(72 * s));
  ctx.restore();

  // 5. BARIS 2: Nama Jalan & Alamat Lengkap Google Maps (Berjarak Lega dari Baris 1)
  curY += Math.round(58 * s);
  ctx.save();
  const addressFontS = Math.round(32 * s);
  ctx.font = `800 ${addressFontS}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0, 0, 0, 0.98)';
  ctx.shadowBlur = 15 * s;
  ctx.strokeStyle = 'rgba(0, 0, 0, 0.98)';
  ctx.lineWidth = 6.5 * s;

  const addressLines = wrapTextLines(ctx, meta.address, maxTextW, 2);
  addressLines.forEach((line) => {
    ctx.strokeText(line, textMarginL, curY);
    ctx.fillStyle = '#FFFFFF';
    ctx.fillText(line, textMarginL, curY);
    curY += Math.round(40 * s);
  });
  ctx.restore();

  // 6. BARIS 3: Titik Koordinat GPS Fleksibel (Ukuran 30px dengan Badge Pill Gelap Dinamis)
  curY += Math.round(22 * s);
  const coordText = `📍 ${meta.lat}, ${meta.lng}`;
  const coordFontS = Math.round(30 * s); // 30px
  ctx.save();
  ctx.font = `800 ${coordFontS}px "Inter", "Segoe UI", monospace, Arial`;
  const coordTextW = ctx.measureText(coordText).width;
  const badgePadX = Math.round(16 * s);
  const badgeH = Math.round(44 * s);
  const badgeY = curY - Math.round(33 * s);

  // Dynamic semi-transparent dark rounded badge pill
  ctx.fillStyle = 'rgba(0, 0, 0, 0.78)';
  ctx.beginPath();
  ctx.roundRect(textMarginL, badgeY, coordTextW + (badgePadX * 2), badgeH, Math.round(10 * s));
  ctx.fill();

  ctx.fillStyle = '#FEF08A'; // Soft Gold untuk kontras tinggi
  ctx.shadowColor = 'rgba(0, 0, 0, 0.95)';
  ctx.shadowBlur = 9 * s;
  ctx.fillText(coordText, textMarginL + badgePadX, curY);
  ctx.restore();

  // 7. BARIS 4: Tag Keterangan Pekerjaan Fleksibel (Ukuran 28px, Berjarak Lega dari Baris 3)
  if (meta.jobDescription) {
    curY += Math.round(48 * s);
    ctx.save();
    const jobFontS = Math.round(28 * s); // 28px
    ctx.font = `800 ${jobFontS}px "Inter", "Montserrat", Arial, sans-serif`;

    const jobLines = wrapTextLines(ctx, `📌 ${meta.jobDescription}`, maxTextW, 2);
    ctx.shadowColor = 'rgba(0, 0, 0, 0.95)';
    ctx.shadowBlur = 12 * s;
    ctx.strokeStyle = 'rgba(0, 0, 0, 0.95)';
    ctx.lineWidth = 6 * s;

    jobLines.forEach((line) => {
      ctx.strokeText(line, textMarginL, curY);
      ctx.fillStyle = '#38BDF8'; // Bright cyan text
      ctx.fillText(line, textMarginL, curY);
      curY += Math.round(34 * s);
    });
    ctx.restore();
  }

  // 8. Draw Right Google Mini-Map Satellite (Ukuran Fleksibel dengan Jarak dari Footer)
  drawGoogleSatelliteMiniMap(ctx, mapX, mapY, mapW, mapH, s, meta.satelliteImg);

  // 9. Draw Bottom Solid White Footer Bar (Dengan Padding Atas yang Lega)
  const footerY = h - footerBarH;
  ctx.save();
  ctx.fillStyle = '#FFFFFF';
  ctx.fillRect(0, footerY, w, footerBarH);

  // Top dividing line
  ctx.fillStyle = '#CBD5E1';
  ctx.fillRect(0, footerY, w, 2.5 * s);

  // Sisi Kiri Footer: Logo SGX + Nama Sinar Grafika + WhatsApp Contact
  const footerLogoSize = Math.round(56 * s);
  const footerLogoY = footerY + Math.round((footerBarH - footerLogoSize) / 2);
  if (meta.logoImg) {
    ctx.drawImage(meta.logoImg, textMarginL, footerLogoY, footerLogoSize, footerLogoSize);
  }

  const textX = textMarginL + footerLogoSize + Math.round(14 * s);
  ctx.fillStyle = '#0F172A';
  ctx.font = `900 ${Math.round(24 * s)}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.fillText(stampForm.companyName || 'Sinar Grafika', textX, footerY + Math.round(34 * s));

  ctx.fillStyle = '#334155';
  ctx.font = `700 ${Math.round(18 * s)}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.fillText(stampForm.companyPhone || '082388885251', textX, footerY + Math.round(62 * s));

  // Diagonal dividing slash di tengah
  const midX = leftColW;
  ctx.strokeStyle = '#CBD5E1';
  ctx.lineWidth = 2.5 * s;
  ctx.beginPath();
  ctx.moveTo(midX + Math.round(16 * s), footerY + Math.round(12 * s));
  ctx.lineTo(midX - Math.round(16 * s), footerY + footerBarH - Math.round(12 * s));
  ctx.stroke();

  // Sisi Kanan Footer: Branding Website Resmi vendor.sinargrafika.my.id
  const rightMarginR = Math.round(28 * s);
  ctx.fillStyle = '#0369A1'; // Deep sky blue
  ctx.font = `800 ${Math.round(22 * s)}px "Inter", "Montserrat", monospace, Arial, sans-serif`;
  ctx.textAlign = 'right';
  ctx.fillText('vendor.sinargrafika.my.id', w - rightMarginR, footerY + Math.round(52 * s));
  ctx.textAlign = 'left'; // Reset alignment
  ctx.restore();
}

/**
 * Draw Satellite Mini Map with Google watermark, Blue GPS Dot & Green Vision Cone
 */
function drawGoogleSatelliteMiniMap(ctx, x, y, width, height, s, satelliteImg) {
  ctx.save();

  // Rounded outer border
  const radius = 12 * s;
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
    ctx.lineWidth = 5 * s;
    ctx.beginPath();
    ctx.moveTo(x, y + height * 0.7);
    ctx.lineTo(x + width, y + height * 0.25);
    ctx.stroke();
  }

  // 2. Green Directional Radar Beam (Field of View Angle)
  const pinX = x + width * 0.52;
  const pinY = y + height * 0.48;

  ctx.fillStyle = 'rgba(16, 185, 129, 0.68)';
  ctx.beginPath();
  ctx.moveTo(pinX, pinY);
  ctx.arc(pinX, pinY, 44 * s, -Math.PI * 0.85, -Math.PI * 0.3);
  ctx.closePath();
  ctx.fill();

  // 3. Blue GPS Location Dot with White Ring
  ctx.fillStyle = '#0284C7';
  ctx.beginPath();
  ctx.arc(pinX, pinY, 10 * s, 0, Math.PI * 2);
  ctx.fill();

  ctx.strokeStyle = '#FFFFFF';
  ctx.lineWidth = 2.5 * s;
  ctx.stroke();

  // 4. "Google" Watermark at Bottom Left of Mini Map
  ctx.font = `bold ${14 * s}px Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0, 0, 0, 0.9)';
  ctx.shadowBlur = 4 * s;
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText('Google', x + (10 * s), y + height - (10 * s));

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
  initFastGpsLock();
});

onUnmounted(() => {
  stopCamera();
  if (gpsWatchId && navigator.geolocation) {
    navigator.geolocation.clearWatch(gpsWatchId);
    gpsWatchId = null;
  }
});
</script>
