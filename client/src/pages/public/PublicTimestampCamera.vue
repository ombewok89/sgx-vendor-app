<template>
  <div class="h-screen max-h-screen bg-[#1E1E1D] text-slate-100 font-sans selection:bg-[#EDC80A] selection:text-[#333231] flex flex-col overflow-hidden relative">
    
    <!-- Ambient Radial Glow Accents (Hitam #333231, Kuning #EDC80A & Oranye #F97316) -->
    <div class="fixed top-0 left-1/4 w-80 h-80 bg-[#EDC80A]/10 rounded-full blur-3xl pointer-events-none -translate-y-1/2"></div>
    <div class="fixed bottom-0 right-1/4 w-80 h-80 bg-[#F97316]/10 rounded-full blur-3xl pointer-events-none translate-y-1/2"></div>

    <!-- Top Header Bar with Official Company Banner Logo & GPS Status -->
    <header class="border-b border-[#EDC80A]/20 bg-[#333231]/95 backdrop-blur-md shrink-0 px-3 sm:px-4 py-2 shadow-md z-40">
      <div class="max-w-5xl mx-auto flex items-center justify-between gap-3">
        <!-- Logo & Title -->
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="flex items-center shrink-0">
            <img
              src="/sgx_logo.png"
              alt="PT Sinar Kreasindo Bencoolen Logo"
              class="h-8 sm:h-9 w-8 sm:w-9 object-contain rounded-xl shadow-xs border border-[#EDC80A]/40"
            />
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-1.5">
              <h1 class="font-extrabold text-xs sm:text-sm text-white tracking-wide truncate">TIMESLIP CAMERA</h1>
              <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-[#1E1E1D] text-[#EDC80A] border border-[#EDC80A]/30 shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1 animate-pulse"></span>
                AUTONOMOUS GPS
              </span>
            </div>
            <!-- Integrated Verified Location Subtitle in Header -->
            <p class="text-[10px] text-[#EDC80A] font-medium truncate flex items-center gap-1" :title="detectedAddress">
              <MapPin class="w-3 h-3 text-[#EDC80A] shrink-0" />
              <span>{{ detectedAddress || 'Mendeteksi alamat satelit...' }}</span>
            </p>
          </div>
        </div>

        <!-- Header Actions -->
        <div class="flex items-center gap-2 shrink-0">
          <button
            type="button"
            @click="refreshGps"
            class="p-2 rounded-xl bg-[#1E1E1D] hover:bg-[#2a2a28] text-[#EDC80A] hover:text-white transition-all border border-[#EDC80A]/30 cursor-pointer active:scale-95 shadow-sm"
            title="Segarkan GPS"
          >
            <RefreshCw class="w-3.5 h-3.5 text-[#EDC80A]" :class="{ 'animate-spin': fetchingGps }" />
          </button>
          <button
            type="button"
            @click="goBackHome"
            class="px-3 py-1.5 rounded-xl bg-[#1E1E1D] hover:bg-[#2a2a28] text-slate-200 hover:text-white text-xs font-bold transition-all border border-[#EDC80A]/30 flex items-center gap-1 cursor-pointer active:scale-95 shadow-sm"
          >
            <Home class="w-3.5 h-3.5 text-[#EDC80A]" />
            <span class="hidden sm:inline">Beranda</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content Area (No-Scroll Viewport Layout) -->
    <main class="flex-1 max-w-5xl w-full mx-auto p-3 sm:p-4 flex flex-col min-h-0 z-10">
      
      <!-- STATE 1: CAMERA & UPLOAD VIEWFINDER (Belum Ambil Foto) -->
      <div v-if="!capturedImage" class="flex-1 flex flex-col min-h-0 space-y-2.5">
        
        <!-- Compact Input Keterangan Pekerjaan (Default Kosong) -->
        <div class="shrink-0 relative">
          <div class="relative flex items-center">
            <div class="absolute left-3 text-slate-400 pointer-events-none">
              <FileText class="w-3.5 h-3.5 text-[#EDC80A]" />
            </div>
            <input
              type="text"
              v-model="stampForm.jobDescription"
              placeholder="Ketik Keterangan Pekerjaan (Opsional)"
              class="w-full pl-9 pr-3 py-2 bg-[#333231]/90 border border-slate-700 rounded-xl focus:border-[#EDC80A] focus:ring-2 focus:ring-[#EDC80A]/20 focus:outline-none text-white text-xs font-medium placeholder-slate-400 shadow-inner transition-all"
            />
          </div>
        </div>

        <!-- Focus Viewfinder Box (Fills Remaining Screen Height without Scrolling) -->
        <div class="flex-1 relative bg-black rounded-2xl border border-slate-700 overflow-hidden shadow-2xl flex items-center justify-center min-h-0">
          
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
            <div class="w-14 h-14 rounded-2xl bg-[#333231] border border-[#EDC80A]/30 text-[#EDC80A] flex items-center justify-center mx-auto shadow-inner">
              <Camera class="w-7 h-7 text-[#EDC80A]" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-white">Kamera Siap Digunakan</h3>
              <p class="text-[11px] text-slate-300 mt-0.5">Buka kamera langsung atau pilih foto dari galeri. Lokasi & waktu otomatis tercetak di bawah foto.</p>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-2.5 pt-1">
              <button
                type="button"
                @click="startCamera"
                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#EDC80A] via-amber-500 to-[#F97316] hover:from-[#f5d012] hover:to-[#ea580c] text-[#1E1E1D] text-xs font-black flex items-center gap-2 shadow-lg shadow-amber-500/25 active:scale-95 transition-all cursor-pointer"
              >
                <Camera class="w-4 h-4 text-[#1E1E1D]" />
                <span>Buka Kamera</span>
              </button>
              <label class="px-5 py-2.5 rounded-xl bg-[#333231] hover:bg-[#282826] text-slate-200 text-xs font-bold flex items-center gap-2 border border-[#EDC80A]/30 active:scale-95 transition-all cursor-pointer shadow-sm">
                <Upload class="w-4 h-4 text-[#EDC80A]" />
                <span>Pilih Galeri</span>
                <input type="file" accept="image/*" class="hidden" @change="handleFileSelected" />
              </label>
            </div>
          </div>

          <!-- Camera Live Controls Overlay (Ketika Kamera Aktif) -->
          <div v-if="isCameraActive" class="absolute top-3 inset-x-3 flex items-center justify-between z-20">
            <!-- GPS Status Pill -->
            <div class="px-2.5 py-1 rounded-full bg-[#333231]/90 backdrop-blur-md border border-[#EDC80A]/40 text-[10px] font-mono flex items-center gap-1.5 shadow-md text-[#EDC80A]">
              <MapPin class="w-3 h-3 text-[#EDC80A]" />
              <span>{{ gpsLocation ? `${gpsLocation.lat.toFixed(5)}, ${gpsLocation.lng.toFixed(5)}` : 'Mencari GPS...' }}</span>
            </div>

            <!-- Switch Camera Toggle & Close Button -->
            <div class="flex items-center gap-1.5">
              <button
                type="button"
                @click="toggleCameraFacing"
                class="p-2 rounded-full bg-[#333231]/90 hover:bg-[#282826] text-white backdrop-blur-md border border-[#EDC80A]/30 shadow-md active:scale-90 transition-all cursor-pointer"
                title="Ganti Kamera Depan/Belakang"
              >
                <SwitchCamera class="w-3.5 h-3.5 text-[#EDC80A]" />
              </button>
              <button
                type="button"
                @click="stopCamera"
                class="p-2 rounded-full bg-rose-600/85 hover:bg-rose-700 text-white backdrop-blur-md border border-white/20 shadow-md active:scale-90 transition-all cursor-pointer"
                title="Tutup Kamera"
              >
                <X class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

          <!-- Capture Shutter Button (Bottom Center Overlay) -->
          <div v-if="isCameraActive" class="absolute bottom-5 inset-x-0 flex items-center justify-center gap-5 z-20">
            <label class="p-3 rounded-full bg-[#333231]/90 hover:bg-[#282826] text-[#EDC80A] backdrop-blur-md border border-[#EDC80A]/30 shadow-lg cursor-pointer active:scale-90 transition-all" title="Upload dari File">
              <Upload class="w-4 h-4 text-[#EDC80A]" />
              <input type="file" accept="image/*" class="hidden" @change="handleFileSelected" />
            </label>

            <!-- Big Shutter Button with Golden Amber Glow -->
            <button
              type="button"
              @click="captureSnapshot"
              class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-white p-1 shadow-2xl shadow-amber-500/50 border-4 border-[#EDC80A] hover:scale-105 active:scale-95 transition-all cursor-pointer flex items-center justify-center"
            >
              <div class="w-full h-full rounded-full bg-gradient-to-tr from-[#EDC80A] via-amber-500 to-[#F97316] flex items-center justify-center text-[#1E1E1D]">
                <Camera class="w-6 h-6 sm:w-7 sm:h-7" />
              </div>
            </button>

            <button
              type="button"
              @click="refreshGps"
              class="p-3 rounded-full bg-[#333231]/90 hover:bg-[#282826] text-[#EDC80A] backdrop-blur-md border border-[#EDC80A]/30 shadow-lg cursor-pointer active:scale-90 transition-all"
              title="Perbarui GPS"
            >
              <RefreshCw class="w-4 h-4 text-[#EDC80A]" :class="{ 'animate-spin': fetchingGps }" />
            </button>
          </div>
        </div>

      </div>

      <!-- STATE 2: PREVIEW & RESULT STAGE (Setelah Ambil/Pilih Foto) -->
      <div v-else class="flex-1 flex flex-col min-h-0 space-y-3 animate-scale-up">
        
        <!-- Processed Watermarked Image Preview -->
        <div class="flex-1 bg-[#333231]/90 border border-[#EDC80A]/30 rounded-2xl p-3 shadow-2xl flex flex-col items-center justify-center min-h-0 overflow-hidden">
          <div class="w-full h-full rounded-xl overflow-hidden shadow-2xl border border-slate-700 bg-black relative flex items-center justify-center">
            <img
              :src="watermarkedImage"
              alt="Hasil Watermark Timestamp"
              class="w-full h-full object-contain mx-auto"
            />
            
            <div v-if="processingWatermark" class="absolute inset-0 bg-[#1E1E1D]/85 backdrop-blur-xs flex flex-col items-center justify-center space-y-2 text-xs text-white">
              <Loader2 class="w-8 h-8 animate-spin text-[#EDC80A]" />
              <p class="font-bold">Menerapkan Stempel Sinar Grafika...</p>
            </div>
          </div>
        </div>

        <!-- Quick Actions Bar -->
        <div class="shrink-0 flex flex-wrap items-center justify-center gap-2.5 w-full">
          <button
            type="button"
            @click="downloadResult"
            class="flex-1 min-w-[180px] px-5 py-3 rounded-xl bg-gradient-to-r from-[#EDC80A] via-amber-500 to-[#F97316] hover:from-[#f5d012] hover:to-[#ea580c] text-[#1E1E1D] text-xs font-black flex items-center justify-center gap-2 shadow-lg shadow-amber-500/25 active:scale-95 transition-all cursor-pointer"
          >
            <Download class="w-4 h-4 text-[#1E1E1D]" />
            <span>Simpan ke Galeri / Unduh</span>
          </button>

          <button
            v-if="canShare"
            type="button"
            @click="shareResult"
            class="px-4 py-3 rounded-xl bg-[#333231] hover:bg-[#282826] border border-[#EDC80A]/40 text-[#EDC80A] text-xs font-bold flex items-center justify-center gap-2 shadow-md active:scale-95 transition-all cursor-pointer"
          >
            <Share2 class="w-4 h-4 text-[#EDC80A]" />
            <span>Bagikan</span>
          </button>

          <button
            type="button"
            @click="resetCapture"
            class="px-4 py-3 rounded-xl bg-[#1E1E1D] hover:bg-[#282826] text-slate-300 hover:text-white text-xs font-bold flex items-center justify-center gap-2 border border-slate-700 active:scale-95 transition-all cursor-pointer"
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

// Direct Tile XYZ Calculation (Zoom 17 - Super Detail)
function latLngToTile(lat, lng, zoom = 17) {
  const n = Math.pow(2, zoom);
  const radLat = (Number(lat) * Math.PI) / 180;
  const x = Math.floor(((Number(lng) + 180) / 360) * n);
  const y = Math.floor((1 - Math.log(Math.tan(radLat) + (1 / Math.cos(radLat))) / Math.PI) / 2 * n);
  return { x, y, z: zoom };
}

// Memory Cache for Instant Tile Retrieval (0 ms)
let cachedSatelliteImg = null;
let cachedSatelliteKey = '';
let isPrefetchingSatellite = false;

// 1. High-Speed Multi-CDN Satellite Tile Loader (< 50 ms CDN delivery)
function loadTileFromUrl(url, timeoutMs = 2000) {
  return new Promise((resolve) => {
    const img = new Image();
    img.crossOrigin = 'anonymous';
    const timer = setTimeout(() => resolve(null), timeoutMs);
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

// 2. High-Speed Satellite Engine with Background Pre-Fetch
async function getFastSatelliteTile(lat, lng) {
  if (lat == null || lng == null) return null;
  const tile = latLngToTile(lat, lng, 17);
  const tileKey = `${tile.z}_${tile.x}_${tile.y}`;

  // If already in memory cache from background prefetch, return instantly (0 ms)
  if (cachedSatelliteImg && cachedSatelliteKey === tileKey && cachedSatelliteImg.complete && cachedSatelliteImg.naturalWidth > 0) {
    return cachedSatelliteImg;
  }

  // Priority 1: Google Satellite Slippy Tile CDN (Ultra-low latency CDN)
  const gTileUrl = `https://mt1.google.com/vt/lyrs=s&x=${tile.x}&y=${tile.y}&z=${tile.z}`;
  let img = await loadTileFromUrl(gTileUrl, 1500);

  // Priority 2: Esri World Imagery Direct Tile CDN
  if (!img) {
    const esriTileUrl = `https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/${tile.z}/${tile.y}/${tile.x}`;
    img = await loadTileFromUrl(esriTileUrl, 1500);
  }

  // Priority 3: ArcGIS Export BBOX Fallback
  if (!img) {
    const delta = 0.0012;
    const minLng = (Number(lng) - delta).toFixed(6);
    const maxLng = (Number(lng) + delta).toFixed(6);
    const minLat = (Number(lat) - (delta * 0.75)).toFixed(6);
    const maxLat = (Number(lat) + (delta * 0.75)).toFixed(6);
    const arcUrl = `https://services.arcgisonline.com/arcgis/rest/services/World_Imagery/MapServer/export?bbox=${minLng},${minLat},${maxLng},${maxLat}&bboxSR=4326&size=300,300&f=image`;
    img = await loadTileFromUrl(arcUrl, 2000);
  }

  if (img) {
    cachedSatelliteImg = img;
    cachedSatelliteKey = tileKey;
  }

  return img;
}

// 3. Live Background Pre-Fetching (Runs proactively before user takes photo)
async function prefetchSatelliteTile(lat, lng) {
  if (lat == null || lng == null || isPrefetchingSatellite) return;
  const tile = latLngToTile(lat, lng, 17);
  const tileKey = `${tile.z}_${tile.x}_${tile.y}`;
  if (cachedSatelliteKey === tileKey && cachedSatelliteImg) return;

  isPrefetchingSatellite = true;
  try {
    const img = await getFastSatelliteTile(lat, lng);
    if (img) {
      cachedSatelliteImg = img;
      cachedSatelliteKey = tileKey;
    }
  } catch (e) {
    console.warn('Satellite prefetch error:', e);
  } finally {
    isPrefetchingSatellite = false;
  }
}

function goBackHome() {
  window.location.href = '/';
}

let gpsWatchId = null;

// 1. Geolocation Handling: Two-Pass Fast-Lock & Continuous Watcher with Background Satellite Pre-Fetch
async function initFastGpsLock() {
  // Pass 0: Instant Cache from Local Storage (0 ms)
  try {
    const cachedGps = localStorage.getItem('sgx_last_gps');
    const cachedAddr = localStorage.getItem('sgx_last_address');
    if (cachedGps && !gpsLocation.value) {
      gpsLocation.value = JSON.parse(cachedGps);
      // Immediately prefetch satellite tile in background
      prefetchSatelliteTile(gpsLocation.value.lat, gpsLocation.value.lng);
    }
    if (cachedAddr && !detectedAddress.value) {
      detectedAddress.value = cachedAddr;
    }
  } catch (e) {}

  if (!navigator.geolocation) {
    if (!gpsLocation.value) {
      gpsLocation.value = { lat: -3.824921, lng: 102.286299, accuracy: 5 };
      prefetchSatelliteTile(gpsLocation.value.lat, gpsLocation.value.lng);
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
      prefetchSatelliteTile(gpsLocation.value.lat, gpsLocation.value.lng);
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

      // Proactively prefetch satellite tile in background
      prefetchSatelliteTile(newLat, newLng);

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
      prefetchSatelliteTile(gpsLocation.value.lat, gpsLocation.value.lng);
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

// 5. HTML5 Canvas Watermark Rendering Engine (Bottom Bar & Flexible Map)
async function renderWatermarkCanvas() {
  if (!capturedImage.value) return;
  processingWatermark.value = true;

  const logoImg = await loadLogoImage();
  const bannerLogoImg = await loadBannerLogoImage();
  const lat = gpsLocation.value ? Number(gpsLocation.value.lat) : -3.824921;
  const lng = gpsLocation.value ? Number(gpsLocation.value.lng) : 102.286299;
  const satelliteImg = await getFastSatelliteTile(lat, lng);

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

  // 2. Bar Dimensions at Bottom of Photo (Total Tinggi 470px untuk Jam 120px & Spasi Lega)
  const totalBarH = Math.round(470 * s); // Total Tinggi Bar 470px
  const footerBarH = Math.round(85 * s);  // White branding footer strip (85px)
  const mainBarH = totalBarH - footerBarH; // Main content area (385px)
  const barY = h - totalBarH;

  // Background 100% Transparan (Tanpa Background Gelap)

  // 3. Pembagian Kolom Lebar 70% : 30% (70% Tulisan : 30% Google Map Fleksibel)
  const leftColW = w * 0.70;  // 70% Lebar untuk Tulisan
  const rightColW = w * 0.30; // 30% Lebar untuk Google Map

  // A. Mini Map Satellite di Sisi Kanan (Ukuran Persegi 1:1 Presisi dengan Jarak ke Footer)
  const mapPadTop = Math.round(14 * s);
  const mapPadBottom = Math.round(20 * s); // Jarak lega ke bagian footer strip
  const availMapH = mainBarH - mapPadTop - mapPadBottom;
  const availMapW = rightColW - Math.round(28 * s);
  const mapSide = Math.min(availMapH, availMapW); // Ukuran Persegi 1:1

  const mapW = mapSide;
  const mapH = mapSide;
  const mapX = leftColW + Math.round((rightColW - mapSide) / 2); // Center di kolom kanan
  const mapY = barY + mapPadTop + Math.round((availMapH - mapSide) / 2);

  // B. Area Tulisan di Sisi Kiri (100% Lebar dari Kolom 70%)
  const textMarginL = Math.round(20 * s);
  const maxTextW = leftColW - textMarginL - Math.round(10 * s); // 100% lebar dari kolom kiri 70%

  // 4. BARIS 1: Jam Digital (120px) + Garis Emas Fleksibel + Tanggal (40px) & Hari (30px)
  let curY = barY + Math.round(96 * s);
  ctx.save();
  const clockFontS = Math.round(120 * s); // 120px
  ctx.font = `900 ${clockFontS}px "Inter", "Montserrat", "Segoe UI", Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0, 0, 0, 0.95)';
  ctx.shadowBlur = 18 * s;
  ctx.strokeStyle = 'rgba(0, 0, 0, 0.95)';
  ctx.lineWidth = 8.5 * s;
  ctx.strokeText(meta.timeStr, textMarginL, curY);
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText(meta.timeStr, textMarginL, curY);

  const timeW = ctx.measureText(meta.timeStr).width;

  // Vertical Gold Separator (Membentang Sepanjang Jam secara Fleksibel)
  const sepX = textMarginL + timeW + Math.round(18 * s);
  const sepH = Math.round(92 * s);
  const sepTopY = curY - Math.round(88 * s);
  ctx.fillStyle = '#EAB308';
  ctx.fillRect(sepX, sepTopY, Math.round(6 * s), sepH);

  // Tanggal (40px Putih) & Hari (30px Emas #FDE047)
  const dateTextX = sepX + Math.round(18 * s);
  ctx.font = `800 ${Math.round(40 * s)}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0, 0, 0, 0.95)';
  ctx.shadowBlur = 14 * s;
  ctx.strokeStyle = 'rgba(0, 0, 0, 0.95)';
  ctx.lineWidth = 6 * s;
  ctx.strokeText(meta.dateStr, dateTextX, sepTopY + Math.round(38 * s));
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText(meta.dateStr, dateTextX, sepTopY + Math.round(38 * s));

  ctx.font = `800 ${Math.round(30 * s)}px "Inter", "Montserrat", Arial, sans-serif`; // 30px
  ctx.strokeText(meta.dayName, dateTextX, sepTopY + Math.round(78 * s));
  ctx.fillStyle = '#FDE047';
  ctx.fillText(meta.dayName, dateTextX, sepTopY + Math.round(78 * s));
  ctx.restore();

  // 5. BARIS 2: Nama Jalan & Alamat Lengkap Google Maps (32px, Berjarak Lega dari Baris 1)
  curY += Math.round(62 * s);
  ctx.save();
  const addressFontS = Math.round(32 * s); // 32px
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

  // Rekam titik batas bawah alamat Baris 2
  const lastAddressY = curY - Math.round(40 * s);

  // 6. BARIS 3 & 4: BARIS 3 CENTER DI ANTARA BARIS 2 DAN BARIS 4 (TAG KERJA 32PX SEJAJAR BAWAH MAP)
  const mapBottomY = mapY + mapH; // Titik dasar bagian bawah map persegi

  if (meta.jobDescription) {
    // A. BARIS 4: Tag Keterangan Pekerjaan (Ukuran 32px Cyan Cerah Sejajar Bagian Bawah Map)
    const jobFontS = Math.round(32 * s); // 32px
    ctx.save();
    ctx.font = `800 ${jobFontS}px "Inter", "Montserrat", Arial, sans-serif`;
    const jobLines = wrapTextLines(ctx, `📌 ${meta.jobDescription}`, maxTextW, 2);
    const lineStep = Math.round(40 * s);

    // Baseline baris terakhir pas sejajar dengan garis bawah map
    const lastLineY = mapBottomY - Math.round(4 * s);
    const firstJobY = lastLineY - ((jobLines.length - 1) * lineStep);

    // B. BARIS 3: Titik Koordinat GPS (Center Sempurna di Antara Baris 2 dan Baris 4)
    const coordFontS = Math.round(35 * s); // 35px
    const coordText = `📍 ${meta.lat}, ${meta.lng}`;
    ctx.font = `800 ${coordFontS}px "Inter", "Segoe UI", monospace, Arial`;
    const coordTextW = ctx.measureText(coordText).width;
    const badgePadX = Math.round(18 * s);
    const badgeH = Math.round(50 * s);

    // Hitung posisi center Y tepat di tengah jarak antara Baris 2 dan Baris 4
    const midCenterY = Math.round((lastAddressY + firstJobY) / 2);
    const coordY = midCenterY + Math.round(8 * s);
    const badgeY = coordY - Math.round(37 * s);

    // Draw Baris 3 (Badge Pill Gelap Dinamis di Posisi Center)
    ctx.fillStyle = 'rgba(0, 0, 0, 0.78)';
    ctx.beginPath();
    ctx.roundRect(textMarginL, badgeY, coordTextW + (badgePadX * 2), badgeH, Math.round(12 * s));
    ctx.fill();

    ctx.fillStyle = '#FEF08A'; // Soft Gold
    ctx.shadowColor = 'rgba(0, 0, 0, 0.95)';
    ctx.shadowBlur = 9 * s;
    ctx.fillText(coordText, textMarginL + badgePadX, coordY);

    // Draw Baris 4 (Tag Keterangan Pekerjaan 32px Sejajar Bawah Map)
    ctx.font = `800 ${jobFontS}px "Inter", "Montserrat", Arial, sans-serif`;
    ctx.shadowColor = 'rgba(0, 0, 0, 0.95)';
    ctx.shadowBlur = 12 * s;
    ctx.strokeStyle = 'rgba(0, 0, 0, 0.95)';
    ctx.lineWidth = 6 * s;

    jobLines.forEach((line, idx) => {
      const lineY = firstJobY + (idx * lineStep);
      ctx.strokeText(line, textMarginL, lineY);
      ctx.fillStyle = '#38BDF8'; // Bright cyan text
      ctx.fillText(line, textMarginL, lineY);
    });
    ctx.restore();
  } else {
    // Jika tanpa keterangan kerja, Baris 3 (Koordinat 35px) Center antara Baris 2 dan Dasar Map
    const coordFontS = Math.round(35 * s); // 35px
    const coordText = `📍 ${meta.lat}, ${meta.lng}`;
    ctx.save();
    ctx.font = `800 ${coordFontS}px "Inter", "Segoe UI", monospace, Arial`;
    const coordTextW = ctx.measureText(coordText).width;
    const badgePadX = Math.round(18 * s);
    const badgeH = Math.round(50 * s);

    const midCenterY = Math.round((lastAddressY + mapBottomY) / 2);
    const coordY = midCenterY + Math.round(8 * s);
    const badgeY = coordY - Math.round(37 * s);

    ctx.fillStyle = 'rgba(0, 0, 0, 0.78)';
    ctx.beginPath();
    ctx.roundRect(textMarginL, badgeY, coordTextW + (badgePadX * 2), badgeH, Math.round(12 * s));
    ctx.fill();

    ctx.fillStyle = '#FEF08A';
    ctx.shadowColor = 'rgba(0, 0, 0, 0.95)';
    ctx.shadowBlur = 9 * s;
    ctx.fillText(coordText, textMarginL + badgePadX, coordY);
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
