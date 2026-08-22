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
              <label class="block font-bold text-slate-400 mb-1">Nama Kegiatan / Proyek:</label>
              <input
                type="text"
                v-model="stampForm.projectName"
                placeholder="Contoh: Pemasangan Signage / Monitoring"
                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl focus:border-purple-500 focus:outline-none text-white font-medium shadow-inner"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-400 mb-1">Nama Petugas / Pelaksana:</label>
              <input
                type="text"
                v-model="stampForm.officerName"
                placeholder="Contoh: Budi Santoso"
                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl focus:border-purple-500 focus:outline-none text-white font-medium shadow-inner"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-400 mb-1">Nama Lokasi / Cabang:</label>
              <input
                type="text"
                v-model="stampForm.locationName"
                placeholder="Contoh: Cabang Kalimalang / Toko A"
                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl focus:border-purple-500 focus:outline-none text-white font-medium shadow-inner"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-400 mb-1">Pilihan Desain Template:</label>
              <select
                v-model="stampForm.templatePreset"
                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl focus:border-purple-500 focus:outline-none text-white font-bold cursor-pointer shadow-inner"
              >
                <option value="SGX_PREMIUM">SGX Premium Dark Badge (Default)</option>
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
              <label class="block font-bold text-slate-400 mb-1">Catatan Tambahan (Opsional):</label>
              <input
                type="text"
                v-model="stampForm.extraNotes"
                placeholder="Contoh: Selesai 100% / Kondisi Cerah"
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
              <p class="font-bold">Menerapkan Stempel Digital...</p>
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
                <option value="SGX_PREMIUM">SGX Premium Dark Badge</option>
                <option value="COMPACT_CORNER">Compact Corner Badge</option>
                <option value="SIDEBAR_REPORT">Sidebar Technical Strip</option>
                <option value="CLASSIC_GPS">Classic GPS Map Camera</option>
              </select>
            </div>

            <div>
              <label class="block text-slate-400 font-semibold mb-1 text-[11px]">Nama Kegiatan:</label>
              <input
                type="text"
                v-model="stampForm.projectName"
                @input="debouncedRender"
                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs"
              />
            </div>

            <div>
              <label class="block text-slate-400 font-semibold mb-1 text-[11px]">Nama Petugas:</label>
              <input
                type="text"
                v-model="stampForm.officerName"
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
  projectName: 'Dokumentasi Lapangan',
  officerName: 'Teknisi SGX',
  locationName: 'Lokasi Pekerjaan',
  extraNotes: 'Kondisi Selesai / Terverifikasi',
  templatePreset: 'SGX_PREMIUM',
  timeZone: 'WIB'
});

function goBackHome() {
  window.location.href = '/';
}

// 1. Geolocation Handling
function refreshGps() {
  if (!navigator.geolocation) {
    gpsLocation.value = { lat: -6.2088, lng: 106.8456, accuracy: 10 };
    return;
  }

  fetchingGps.value = true;
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      gpsLocation.value = {
        lat: pos.coords.latitude,
        lng: pos.coords.longitude,
        accuracy: Math.round(pos.coords.accuracy || 5)
      };
      fetchingGps.value = false;
    },
    (err) => {
      console.warn('GPS access error:', err);
      // Fallback Jakarta coordinate if denied
      if (!gpsLocation.value) {
        gpsLocation.value = { lat: -6.2088, lng: 106.8456, accuracy: 15 };
      }
      fetchingGps.value = false;
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  );
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

function renderWatermarkCanvas() {
  if (!capturedImage.value) return;
  processingWatermark.value = true;

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
    const dateStr = now.toLocaleDateString('id-ID', {
      day: '2-digit',
      month: 'long',
      year: 'numeric'
    });
    const timeStr = now.toLocaleTimeString('id-ID', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    }) + ` ${stampForm.timeZone}`;

    const lat = gpsLocation.value ? Number(gpsLocation.value.lat).toFixed(6) : '-6.208800';
    const lng = gpsLocation.value ? Number(gpsLocation.value.lng).toFixed(6) : '106.845600';
    const acc = gpsLocation.value?.accuracy || 5;

    const w = canvas.width;
    const h = canvas.height;
    const baseScale = Math.max(w, h) / 1000;

    // 3. Render Chosen Template
    if (stampForm.templatePreset === 'SGX_PREMIUM') {
      renderSgxPremiumTemplate(ctx, w, h, baseScale, { dateStr, timeStr, lat, lng, acc });
    } else if (stampForm.templatePreset === 'COMPACT_CORNER') {
      renderCompactCornerTemplate(ctx, w, h, baseScale, { dateStr, timeStr, lat, lng, acc });
    } else if (stampForm.templatePreset === 'SIDEBAR_REPORT') {
      renderSidebarReportTemplate(ctx, w, h, baseScale, { dateStr, timeStr, lat, lng, acc });
    } else {
      renderClassicGpsTemplate(ctx, w, h, baseScale, { dateStr, timeStr, lat, lng, acc });
    }

    watermarkedImage.value = canvas.toDataURL('image/jpeg', 0.92);
    processingWatermark.value = false;
  };
  img.src = capturedImage.value;
}

// Template 1: SGX Premium Dark Badge
function renderSgxPremiumTemplate(ctx, w, h, s, meta) {
  const badgeH = Math.max(120 * s, 160);
  const badgeY = h - badgeH;

  // Background gradient overlay
  const grad = ctx.createLinearGradient(0, badgeY, 0, h);
  grad.addColorStop(0, 'rgba(15, 23, 42, 0.88)');
  grad.addColorStop(1, 'rgba(2, 6, 23, 0.98)');
  ctx.fillStyle = grad;
  ctx.fillRect(0, badgeY, w, badgeH);

  // Top accent line
  const accentGrad = ctx.createLinearGradient(0, 0, w, 0);
  accentGrad.addColorStop(0, '#f59e0b');
  accentGrad.addColorStop(0.5, '#9333ea');
  accentGrad.addColorStop(1, '#10b981');
  ctx.fillStyle = accentGrad;
  ctx.fillRect(0, badgeY, w, Math.max(4 * s, 4));

  // Left SGX Emblem Badge
  const pad = 16 * s;
  ctx.fillStyle = '#f59e0b';
  ctx.font = `bold ${Math.round(14 * s)}px sans-serif`;
  ctx.fillText('PT SINAR GRAHA KREATIF', pad, badgeY + 28 * s);

  ctx.fillStyle = '#ffffff';
  ctx.font = `bold ${Math.round(18 * s)}px sans-serif`;
  ctx.fillText(stampForm.projectName || 'Dokumentasi Lapangan', pad, badgeY + 54 * s);

  // Metadata Grid
  ctx.fillStyle = '#cbd5e1';
  ctx.font = `${Math.round(12 * s)}px sans-serif`;
  ctx.fillText(`👤 Petugas : ${stampForm.officerName || 'Teknisi SGX'}`, pad, badgeY + 78 * s);
  ctx.fillText(`📍 Lokasi  : ${stampForm.locationName || 'Lokasi Pekerjaan'}`, pad, badgeY + 98 * s);

  // Right Side Info (Time & GPS)
  ctx.textAlign = 'right';
  ctx.fillStyle = '#fde047';
  ctx.font = `bold ${Math.round(15 * s)}px monospace`;
  ctx.fillText(`${meta.dateStr} | ${meta.timeStr}`, w - pad, badgeY + 34 * s);

  ctx.fillStyle = '#38bdf8';
  ctx.font = `bold ${Math.round(13 * s)}px monospace`;
  ctx.fillText(`GPS: ${meta.lat}, ${meta.lng} (±${meta.acc}m)`, w - pad, badgeY + 58 * s);

  if (stampForm.extraNotes) {
    ctx.fillStyle = '#94a3b8';
    ctx.font = `italic ${Math.round(11 * s)}px sans-serif`;
    ctx.fillText(`Note: ${stampForm.extraNotes}`, w - pad, badgeY + 82 * s);
  }

  ctx.fillStyle = '#34d399';
  ctx.font = `bold ${Math.round(10 * s)}px monospace`;
  ctx.fillText('🛡️ DIGITAL TIMESTAMP VERIFIED', w - pad, badgeY + 104 * s);

  ctx.textAlign = 'left'; // Reset
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
  ctx.fillText(stampForm.projectName, pad, curY);

  curY += 30 * s;
  ctx.fillStyle = '#94a3b8';
  ctx.font = `${Math.round(10 * s)}px sans-serif`;
  ctx.fillText('PETUGAS:', pad, curY);
  curY += 16 * s;
  ctx.fillStyle = '#ffffff';
  ctx.font = `bold ${Math.round(11 * s)}px sans-serif`;
  ctx.fillText(stampForm.officerName, pad, curY);

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
  ctx.fillText(`${stampForm.projectName} - ${stampForm.locationName}`, pad, y);

  y += 24 * s;
  ctx.fillStyle = '#ffffff';
  ctx.font = `bold ${Math.round(14 * s)}px monospace`;
  ctx.fillText(`Lat: ${meta.lat} | Long: ${meta.lng} (±${meta.acc}m)`, pad, y);

  y += 22 * s;
  ctx.fillStyle = '#38bdf8';
  ctx.fillText(`${meta.dateStr}, ${meta.timeStr} | Oleh: ${stampForm.officerName}`, pad, y);

  ctx.shadowBlur = 0; // Reset
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

  const cleanTitle = (stampForm.projectName || 'TIMESLIP').replace(/[^a-zA-Z0-9]/g, '_');
  const filename = `SGX_${cleanTitle}_${dateNum}.jpg`;

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
      title: stampForm.projectName || 'Foto Timeslip SGX',
      text: `Dokumentasi Timestamp: ${stampForm.projectName} di ${stampForm.locationName}`,
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
