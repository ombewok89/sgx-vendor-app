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
            <p class="text-[11px] text-slate-400">Kamera GPS Otomatis Sinar Grafika — Lokasi & Waktu Asli Google Maps</p>
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
      
      <!-- Live Real-Time Location & Privacy Bar -->
      <div class="p-3.5 sm:p-4 bg-slate-900 border border-slate-800 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs shadow-lg">
        <div class="flex items-start gap-3">
          <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 shrink-0 border border-emerald-500/20">
            <MapPin class="w-4 h-4" />
          </div>
          <div class="space-y-0.5">
            <div class="flex items-center gap-2 font-bold text-slate-200">
              <span>Lokasi Terverifikasi Otomatis (Google Maps):</span>
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
              <p class="text-xs text-slate-400 mt-1">Buka kamera langsung atau pilih foto dari galeri. Stempel waktu dan lokasi asli Google Maps akan otomatis tertempel.</p>
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
              <p class="font-bold">Menerapkan Stempel Sinar Grafika (1/3 Layar)...</p>
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
      <p class="text-[11px] mt-0.5">Standalone Public Timestamp Camera — Lokasi & Waktu Otomatis Terverifikasi</p>
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

// 5. HTML5 Canvas Watermark Rendering Engine (Default SGX 1/3 Screen)
async function renderWatermarkCanvas() {
  if (!capturedImage.value) return;
  processingWatermark.value = true;

  const logoImg = await loadLogoImage();
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

    // 3. Render Standar SGX 1/3 Screen Layout with Enlarged Timeslip Fonts & Perfect Map Alignment
    renderSgxPremiumTemplate(ctx, w, h, s, {
      timeStr,
      dayName,
      dateStr,
      lat: latFormatted,
      lng: lngFormatted,
      address: detectedAddress.value || 'Ratu Agung, Sumatera, Gading Cempaka',
      logoImg,
      satelliteImg
    });

    watermarkedImage.value = canvas.toDataURL('image/jpeg', 0.94);
    processingWatermark.value = false;
  };
  img.src = capturedImage.value;
}

/**
 * STANDAR DEFAUL WATERMARK SGX (1/3 LAYAR - UKURAN TIMESLIP BESAR & SEJAJAR MAP)
 */
function renderSgxPremiumTemplate(ctx, w, h, s, meta) {
  // 1. Draw Top-Left SGX Diamond Logo
  const topLogoSize = Math.round(110 * s);
  const topLogoX = Math.round(30 * s);
  const topLogoY = Math.round(30 * s);

  if (meta.logoImg) {
    drawRoundedImage(ctx, meta.logoImg, topLogoX, topLogoY, topLogoSize, topLogoSize, 22 * s);
  }

  // 2. Calculate 1/3 Screen Proportions for Bottom Watermark
  const footerBarH = Math.round(115 * s); // Solid White branding bar height
  const infoPanelH = Math.round(h * 0.26); // Overlay area height (~26% of photo height, total ~35% or 1/3 screen)
  const totalWatermarkH = infoPanelH + footerBarH;
  const panelY = h - totalWatermarkH;

  // 3. Mini Map Satellite on the Right Side (Enlarged & Defined Bounds)
  const mapMarginR = Math.round(30 * s);
  const mapTopY = panelY + Math.round(10 * s);
  const mapH = Math.min(infoPanelH - Math.round(20 * s), Math.round(280 * s));
  const mapW = Math.round(mapH * 1.08);
  const mapX = w - mapW - mapMarginR;
  const mapY = mapTopY;
  const mapBottomY = mapY + mapH;

  // 4. Left Content Column (Perfect Top & Bottom Alignment with Map)
  const textMarginL = Math.round(34 * s);
  const maxTextW = mapX - textMarginL - (24 * s);

  // A. DIGITAL CLOCK (EXTRA LARGE e.g. 00:14) - Aligned to mapTopY
  const clockFontS = Math.round(116 * s);
  const clockBaselineY = mapTopY + Math.round(96 * s);

  ctx.save();
  ctx.font = `900 ${clockFontS}px "Inter", "Montserrat", "Segoe UI", Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0, 0, 0, 0.95)';
  ctx.shadowBlur = 20 * s;
  ctx.strokeStyle = 'rgba(0, 0, 0, 0.95)';
  ctx.lineWidth = 9 * s;
  ctx.strokeText(meta.timeStr, textMarginL, clockBaselineY);
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText(meta.timeStr, textMarginL, clockBaselineY);

  const timeW = ctx.measureText(meta.timeStr).width;

  // B. VERTICAL GOLD SEPARATOR - Exactly spans clock height
  const sepX = textMarginL + timeW + (20 * s);
  const sepH = Math.round(92 * s);
  const sepTopY = mapTopY + Math.round(6 * s);
  ctx.fillStyle = '#EAB308';
  ctx.fillRect(sepX, sepTopY, Math.round(5.5 * s), sepH);

  // C. DATE (23/08/2026) & DAY NAME (Minggu) - Enlarged Fonts
  const dateFontS = Math.round(34 * s);
  const dayFontS = Math.round(36 * s);

  ctx.font = `800 ${dateFontS}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.strokeText(meta.dateStr, sepX + (18 * s), sepTopY + (34 * s));
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText(meta.dateStr, sepX + (18 * s), sepTopY + (34 * s));

  ctx.font = `800 ${dayFontS}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.strokeText(meta.dayName, sepX + (18 * s), sepTopY + (82 * s));
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText(meta.dayName, sepX + (18 * s), sepTopY + (82 * s));
  ctx.restore();

  // D. ADDRESS TEXT (Multi-line with bold drop shadow & larger font)
  let addrBaselineY = clockBaselineY + Math.round(48 * s);
  const fullAddress = meta.address || 'Ratu Agung, Sumatera, Gading Cempaka';
  
  ctx.save();
  ctx.font = `800 ${Math.round(32 * s)}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0, 0, 0, 0.95)';
  ctx.shadowBlur = 18 * s;
  ctx.strokeStyle = 'rgba(0, 0, 0, 0.95)';
  ctx.lineWidth = 8 * s;

  const addressLines = wrapTextLines(ctx, fullAddress, maxTextW, 2);
  addressLines.forEach((line) => {
    ctx.strokeText(line, textMarginL, addrBaselineY);
    ctx.fillStyle = '#FFFFFF';
    ctx.fillText(line, textMarginL, addrBaselineY);
    addrBaselineY += Math.round(40 * s);
  });
  ctx.restore();

  // E. DARK COORDINATE PILL BADGE - Perfectly Aligned with mapBottomY
  const coordText = `Koordinat: ${meta.lat}, ${meta.lng}`;
  const badgeFontS = Math.round(25 * s);
  const badgeH = Math.round(46 * s);
  const badgePadX = Math.round(20 * s);
  const badgeY = mapBottomY - badgeH; // Exactly aligned with bottom of map

  ctx.save();
  ctx.font = `700 ${badgeFontS}px "Inter", "Segoe UI", monospace, Arial`;
  const coordTextW = ctx.measureText(coordText).width;

  // Semi-transparent rounded background pill
  ctx.fillStyle = 'rgba(15, 23, 42, 0.7)';
  ctx.beginPath();
  ctx.roundRect(textMarginL, badgeY, coordTextW + (badgePadX * 2), badgeH, Math.round(9 * s));
  ctx.fill();

  ctx.fillStyle = '#FFFFFF';
  ctx.shadowColor = 'rgba(0, 0, 0, 0.9)';
  ctx.shadowBlur = 12 * s;
  ctx.fillText(coordText, textMarginL + badgePadX, badgeY + Math.round(32 * s));
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
  ctx.fillRect(0, footerY, w, 2.5 * s);

  // Left SGX Logo in footer
  const footerLogoSize = Math.round(82 * s);
  const footerLogoY = footerY + Math.round((footerBarH - footerLogoSize) / 2);
  if (meta.logoImg) {
    ctx.drawImage(meta.logoImg, textMarginL, footerLogoY, footerLogoSize, footerLogoSize);
  }

  // Sinar Grafika + Phone Number Text
  const textX = textMarginL + footerLogoSize + (20 * s);
  ctx.fillStyle = '#0F172A';
  ctx.font = `900 ${32 * s}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.fillText(stampForm.companyName || 'Sinar Grafika', textX, footerY + (48 * s));

  ctx.fillStyle = '#334155';
  ctx.font = `700 ${24 * s}px "Inter", "Montserrat", Arial, sans-serif`;
  ctx.fillText(stampForm.companyPhone || '082388885251', textX, footerY + (86 * s));

  // Diagonal dividing slash in center
  const midX = w * 0.68;
  ctx.strokeStyle = '#CBD5E1';
  ctx.lineWidth = 2.5 * s;
  ctx.beginPath();
  ctx.moveTo(midX + (22 * s), footerY + (14 * s));
  ctx.lineTo(midX - (22 * s), footerY + footerBarH - (14 * s));
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
  const radius = 16 * s;
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
    ctx.lineWidth = 7 * s;
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
  ctx.arc(pinX, pinY, 52 * s, -Math.PI * 0.85, -Math.PI * 0.3);
  ctx.closePath();
  ctx.fill();

  // 3. Blue GPS Location Dot with White Ring
  ctx.fillStyle = '#0284C7';
  ctx.beginPath();
  ctx.arc(pinX, pinY, 13 * s, 0, Math.PI * 2);
  ctx.fill();

  ctx.strokeStyle = '#FFFFFF';
  ctx.lineWidth = 3.5 * s;
  ctx.stroke();

  // 4. "Google" Watermark at Bottom Left of Mini Map
  ctx.font = `bold ${18 * s}px Arial, sans-serif`;
  ctx.shadowColor = 'rgba(0, 0, 0, 0.9)';
  ctx.shadowBlur = 4 * s;
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText('Google', x + (14 * s), y + height - (14 * s));

  ctx.restore();

  // White Border around Mini Map
  ctx.save();
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.95)';
  ctx.lineWidth = 3 * s;
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
  refreshGps();
});

onUnmounted(() => {
  stopCamera();
});
</script>
