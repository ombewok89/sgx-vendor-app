<template>
  <div class="h-screen max-h-screen w-screen overflow-hidden bg-slate-950 text-slate-100 flex flex-col justify-between p-3 sm:p-4 md:p-6 lg:px-10 lg:py-6 relative selection:bg-purple-600 selection:text-white">
    <!-- Ambient Glow / Mesh Gradient Background -->
    <div class="absolute top-0 left-1/4 w-80 h-80 bg-purple-600/15 rounded-full blur-3xl pointer-events-none -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none translate-y-1/2"></div>
    <div class="absolute top-1/3 right-10 w-72 h-72 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Top Minimal Navbar (Brand & Status) -->
    <header class="flex items-center justify-between z-10 shrink-0">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-600 p-1 shadow-md shadow-amber-500/20 border border-white/20 shrink-0">
          <img src="/sgx_logo.png" alt="Sinar Grafika Logo" class="w-full h-full object-contain rounded-lg" />
        </div>
        <div>
          <div class="text-sm sm:text-base font-black tracking-tight text-white flex items-center gap-1.5">
            <span>Sinar Grafika</span>
            <span class="text-[10px] font-mono px-1.5 py-0.2 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/40">SGX</span>
          </div>
          <div class="text-[10px] text-slate-400 font-medium">Digital Evidence & Field Reporting System</div>
        </div>
      </div>

      <div class="hidden sm:flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          System Online
        </span>
        <span class="text-[11px] font-mono text-slate-500">v1.0.0 Enterprise</span>
      </div>
    </header>

    <!-- Main Content: Single Viewport 2-Column Grid -->
    <main class="flex-1 flex items-center justify-center my-auto z-10 min-h-0 py-2">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 items-center w-full max-w-7xl mx-auto h-full max-h-[82vh]">
        
        <!-- Sisi Kiri: Hero & Tabbed Quick Guide (Fits perfectly without scroll) -->
        <div class="lg:col-span-7 flex flex-col justify-center space-y-3.5 sm:space-y-4 h-full min-h-0">
          <!-- Main Hero Headline -->
          <div class="space-y-1.5">
            <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black text-white tracking-tight leading-tight">
              Sistem Bukti Kerja & Pelaporan Lapangan Digital
            </h2>
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed line-clamp-2">
              Platform pembuktian pekerjaan lapangan berbasis forensik digital, verifikasi GPS Geotagging, dan penerbitan Berita Acara Opname otomatis.
            </p>
          </div>

          <!-- 3 Feature Highlight Pills -->
          <div class="grid grid-cols-3 gap-2.5 shrink-0">
            <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800 backdrop-blur-md flex items-center gap-2.5 shadow-xs hover:border-purple-500/40 transition-all">
              <div class="w-8 h-8 rounded-lg bg-purple-900/60 text-purple-400 flex items-center justify-center shrink-0 border border-purple-500/20">
                <MapPin class="w-4 h-4" />
              </div>
              <div class="min-w-0">
                <div class="font-bold text-[11px] sm:text-xs text-white truncate">GPS Geotag</div>
                <div class="text-[9px] sm:text-[10px] text-slate-400 truncate">Radius Presisi</div>
              </div>
            </div>

            <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800 backdrop-blur-md flex items-center gap-2.5 shadow-xs hover:border-indigo-500/40 transition-all">
              <div class="w-8 h-8 rounded-lg bg-indigo-900/60 text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-500/20">
                <ShieldCheck class="w-4 h-4" />
              </div>
              <div class="min-w-0">
                <div class="font-bold text-[11px] sm:text-xs text-white truncate">SHA-256 Hash</div>
                <div class="text-[9px] sm:text-[10px] text-slate-400 truncate">Integritas Forensik</div>
              </div>
            </div>

            <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800 backdrop-blur-md flex items-center gap-2.5 shadow-xs hover:border-emerald-500/40 transition-all">
              <div class="w-8 h-8 rounded-lg bg-emerald-900/60 text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/20">
                <FileCheck2 class="w-4 h-4" />
              </div>
              <div class="min-w-0">
                <div class="font-bold text-[11px] sm:text-xs text-white truncate">BA Opname</div>
                <div class="text-[9px] sm:text-[10px] text-slate-400 truncate">Sertifikasi QR</div>
              </div>
            </div>
          </div>

          <!-- Pusat Panduan Penggunaan Interaktif (Tabbed Navigation - Compact & No Scroll) -->
          <div class="rounded-2xl bg-slate-900/70 border border-slate-800 p-3 sm:p-4 backdrop-blur-md space-y-2.5 shrink-0 shadow-lg">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
              <div class="flex items-center gap-1.5">
                <BookOpen class="w-4 h-4 text-purple-400" />
                <span class="text-xs font-bold text-white">Panduan Penggunaan Singkat</span>
              </div>
              <!-- Role Tab Selector -->
              <div class="flex items-center gap-1 bg-slate-950 p-0.5 rounded-lg border border-slate-800">
                <button
                  type="button"
                  @click="activeGuide = 'field'"
                  :class="['px-2 py-0.5 rounded text-[10px] font-bold transition-all cursor-pointer', activeGuide === 'field' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'text-slate-400 hover:text-slate-200']"
                >
                  Teknisi
                </button>
                <button
                  type="button"
                  @click="activeGuide = 'supervisor'"
                  :class="['px-2 py-0.5 rounded text-[10px] font-bold transition-all cursor-pointer', activeGuide === 'supervisor' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40' : 'text-slate-400 hover:text-slate-200']"
                >
                  Pengawas
                </button>
                <button
                  type="button"
                  @click="activeGuide = 'client'"
                  :class="['px-2 py-0.5 rounded text-[10px] font-bold transition-all cursor-pointer', activeGuide === 'client' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'text-slate-400 hover:text-slate-200']"
                >
                  Client
                </button>
              </div>
            </div>

            <!-- Tab 1: Teknisi Lapangan -->
            <div v-if="activeGuide === 'field'" class="text-[11px] text-slate-300 space-y-1 animate-fade-in">
              <p>• <strong class="text-amber-400">Check-In GPS:</strong> Aktifkan GPS pada ponsel dan lakukan check-in saat tiba di cabang.</p>
              <p>• <strong class="text-amber-400">Foto 3 Tahap:</strong> Unggah foto <em>Before</em>, <em>Process</em> (wajib APD lengkap), dan <em>After</em>.</p>
              <p>• <strong class="text-amber-400">Stempel Otomatis:</strong> Foto otomatis tercetak jam digital, alamat, koordinat, dan mini peta satelit.</p>
            </div>

            <!-- Tab 2: Pengawas / QA -->
            <div v-if="activeGuide === 'supervisor'" class="text-[11px] text-slate-300 space-y-1 animate-fade-in">
              <p>• <strong class="text-indigo-400">Konsol Review:</strong> Periksa ketajaman foto, integritas hash SHA-256, dan koordinat geotagging.</p>
              <p>• <strong class="text-indigo-400">Persetujuan/Revisi:</strong> Klik <em>Setujui</em> atau kirim instruksi revisi spesifik ke teknisi.</p>
              <p>• <strong class="text-indigo-400">BA Opname:</strong> Terbitkan dokumen Berita Acara digital setelah SPK disetujui 100%.</p>
            </div>

            <!-- Tab 3: Client -->
            <div v-if="activeGuide === 'client'" class="text-[11px] text-slate-300 space-y-1 animate-fade-in">
              <p>• <strong class="text-emerald-400">Portal Klien:</strong> Pantau progres pekerjaan seluruh cabang Indomarco / Smartfren secara real-time.</p>
              <p>• <strong class="text-emerald-400">Unduh Arsip:</strong> Unduh foto evidensi asli beresolusi tinggi berstempel resmi.</p>
              <p>• <strong class="text-emerald-400">BA Opname Resmi:</strong> Unduh dokumen sertifikasi ber-QR Code untuk verifikasi penagihan.</p>
            </div>

            <!-- Help Contact Bar -->
            <div class="pt-1.5 flex items-center justify-between text-[10px] text-slate-400 border-t border-slate-800/60">
              <span class="flex items-center gap-1">
                <Phone class="w-3 h-3 text-amber-400" />
                <span>Bantuan: <strong>0823-8888-5251</strong></span>
              </span>
              <span class="text-slate-500 font-mono">support@sgx.com</span>
            </div>
          </div>
        </div>

        <!-- Sisi Kanan: Kartu Login Glassmorphism (Compact & Clean) -->
        <div class="lg:col-span-5 w-full max-w-md mx-auto flex flex-col justify-center h-full min-h-0">
          <div class="rounded-2xl bg-slate-900/85 border border-slate-700/60 p-5 sm:p-6 shadow-2xl backdrop-blur-xl space-y-3.5 relative overflow-hidden shrink-0">
            <!-- Glow Accent -->
            <div class="absolute -top-12 -right-12 w-28 h-28 bg-purple-500/20 rounded-full blur-2xl pointer-events-none"></div>

            <div class="space-y-0.5">
              <h3 class="text-lg sm:text-xl font-black text-white tracking-tight">Masuk ke Akun Anda</h3>
              <p class="text-[11px] text-slate-400">Masukkan kredensial resmi untuk mengakses sistem.</p>
            </div>

            <!-- Error Banner -->
            <div v-if="error" class="p-2.5 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-300 text-[11px] flex items-center gap-2 animate-shake">
              <AlertCircle class="w-3.5 h-3.5 shrink-0 text-rose-400" />
              <span>{{ error }}</span>
            </div>

            <!-- Form Login -->
            <form @submit.prevent="handleLogin" class="space-y-3">
              <!-- Email Input -->
              <div class="space-y-1">
                <label class="block text-[11px] font-bold text-slate-300">Alamat Email / ID Pengguna</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <Mail class="w-3.5 h-3.5" />
                  </div>
                  <input
                    type="email"
                    required
                    v-model="email"
                    placeholder="nama@perusahaan.com"
                    class="w-full pl-9 pr-3 py-2 bg-slate-950/80 border border-slate-700/80 rounded-xl text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  />
                </div>
              </div>

              <!-- Password Input with Show/Hide Toggle -->
              <div class="space-y-1">
                <div class="flex items-center justify-between">
                  <label class="block text-[11px] font-bold text-slate-300">Kata Sandi (Password)</label>
                  <a href="#" @click.prevent="alertContactAdmin" class="text-[10px] text-purple-400 hover:text-purple-300 transition-colors">
                    Lupa sandi?
                  </a>
                </div>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                    <Lock class="w-3.5 h-3.5" />
                  </div>
                  <input
                    :type="showPassword ? 'text' : 'password'"
                    required
                    v-model="password"
                    placeholder="••••••••"
                    class="w-full pl-9 pr-9 py-2 bg-slate-950/80 border border-slate-700/80 rounded-xl text-xs text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300 cursor-pointer"
                  >
                    <EyeOff v-if="showPassword" class="w-3.5 h-3.5" />
                    <Eye v-else class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>

              <!-- Remember Me Checkbox -->
              <div class="flex items-center justify-between text-[11px]">
                <label class="flex items-center gap-1.5 cursor-pointer text-slate-400 hover:text-slate-300">
                  <input
                    type="checkbox"
                    v-model="rememberMe"
                    class="w-3.5 h-3.5 rounded bg-slate-950 border-slate-700 text-purple-600 focus:ring-purple-500"
                  />
                  <span>Ingat sesi saya</span>
                </label>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="loading"
                class="w-full py-2.5 bg-gradient-to-r from-purple-700 via-indigo-600 to-purple-600 hover:from-purple-600 hover:to-indigo-500 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-lg shadow-purple-900/30 active:scale-98 transition-all flex items-center justify-center gap-2 cursor-pointer"
              >
                <Loader2 v-if="loading" class="w-3.5 h-3.5 animate-spin" />
                <span v-else>Masuk ke Sistem</span>
                <ArrowRight v-if="!loading" class="w-3.5 h-3.5" />
              </button>
            </form>

            <!-- Footer Security Badges -->
            <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-[9px] text-slate-500">
              <span class="flex items-center gap-1 font-mono">
                <Lock class="w-2.5 h-2.5 text-emerald-500" />
                <span>256-Bit SSL Enkripsi</span>
              </span>
              <span>ISO/IEC 27001 Ready</span>
            </div>
          </div>
        </div>

      </div>
    </main>

    <!-- Footer Copyright (Slim) -->
    <footer class="p-1 text-center text-[10px] text-slate-600 border-t border-slate-900/80 z-10 font-mono shrink-0">
      © 2026 PT Sinar Grafika (SGX). Hak Cipta Dilindungi Undang-Undang.
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuth } from '../../composables/useAuth';
import {
  MapPin,
  ShieldCheck,
  FileCheck2,
  BookOpen,
  Phone,
  Mail,
  Lock,
  Eye,
  EyeOff,
  ArrowRight,
  AlertCircle,
  Loader2
} from 'lucide-vue-next';

const emit = defineEmits(['login-success']);
const { login } = useAuth();

const email = ref('');
const password = ref('');
const rememberMe = ref(true);
const showPassword = ref(false);
const loading = ref(false);
const error = ref(null);
const activeGuide = ref('field');

async function handleLogin() {
  if (!email.value || !password.value) {
    error.value = 'Silakan masukkan email dan kata sandi Anda.';
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    const user = await login(email.value.trim(), password.value);
    emit('login-success', user);
  } catch (err) {
    error.value = err.message || 'Email atau kata sandi tidak sesuai. Silakan coba lagi.';
  } finally {
    loading.value = false;
  }
}

function alertContactAdmin() {
  alert('Untuk reset kata sandi, silakan hubungi Administrator SGX melalui WhatsApp 0823-8888-5251 atau email support@sgx.com.');
}
</script>
