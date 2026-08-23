<template>
  <div class="min-h-screen sm:h-screen w-full overflow-x-hidden overflow-y-auto sm:overflow-hidden bg-[#1E1E1D] text-slate-100 flex flex-col justify-between p-3.5 sm:p-5 md:p-6 lg:px-10 lg:py-6 relative selection:bg-[#EDC80A] selection:text-[#333231]">
    
    <!-- Ambient Radial Glow Accents (Hitam #333231, Kuning #EDC80A & Oranye #F97316) -->
    <div class="fixed top-0 left-1/4 w-80 sm:w-96 h-80 sm:h-96 bg-[#EDC80A]/12 rounded-full blur-3xl pointer-events-none -translate-y-1/2"></div>
    <div class="fixed bottom-0 right-1/4 w-96 h-96 bg-[#F97316]/12 rounded-full blur-3xl pointer-events-none translate-y-1/2"></div>
    <div class="fixed top-1/2 right-10 w-72 h-72 bg-[#EDC80A]/8 rounded-full blur-3xl pointer-events-none -translate-y-1/2"></div>

    <!-- Top Header Bar (Logo Resmi Perusahaan & Status) -->
    <header class="flex items-center justify-between z-10 shrink-0 w-full max-w-7xl mx-auto">
      <div class="flex items-center gap-3">
        <!-- Logo Resmi Perusahaan -->
        <div class="h-10 sm:h-12 flex items-center">
          <img
            src="/sgx_banner_logo.png"
            alt="Sinar Grafika Official Logo"
            class="h-9 sm:h-11 w-auto object-contain drop-shadow-md"
            onerror="this.onerror=null; this.src='/sgx_logo.png';"
          />
        </div>
        <div class="hidden xs:flex flex-col">
          <span class="text-[10px] sm:text-[11px] font-extrabold uppercase tracking-widest text-[#EDC80A] font-mono">
            Vendor Portal
          </span>
          <span class="text-[9px] text-slate-400 font-medium">Digital Evidence & Field Reporting</span>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] sm:text-[11px] font-bold bg-[#333231] text-[#EDC80A] border border-[#EDC80A]/30 shadow-xs">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>System Online</span>
        </span>
        <span class="hidden sm:inline text-[10px] font-mono text-slate-400">v2.4 Enterprise</span>
      </div>
    </header>

    <!-- Main Content Stage: 2-Column Responsive Layout (Mobile-First & Desktop Split) -->
    <main class="flex-1 flex items-center justify-center my-auto z-10 w-full max-w-7xl mx-auto py-3 sm:py-4">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-12 items-center w-full">
        
        <!-- Sisi Kiri (Desktop Info & Highlights - Tersembunyi Rapi di HP) -->
        <div class="hidden lg:flex lg:col-span-7 flex-col justify-center space-y-4">
          
          <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-[#333231] border border-[#EDC80A]/30 text-[#EDC80A] text-xs font-extrabold tracking-wide">
              <Sparkles class="w-3.5 h-3.5 text-[#EDC80A]" />
              <span>PLATFORM OPERASIONAL & VENDOR TERPADU</span>
            </div>
            <h2 class="text-3xl xl:text-4xl font-black text-white tracking-tight leading-tight">
              Sistem Bukti Kerja & Pelaporan Lapangan Presisi
            </h2>
            <p class="text-sm text-slate-300 leading-relaxed max-w-xl">
              Dokumentasi evidensi lapangan real-time dengan stempel GPS akurat, enkripsi integritas data, dan penerbitan Berita Acara Opname otomatis.
            </p>
          </div>

          <!-- 3 Feature Highlights (Bento Mini Cards) -->
          <div class="grid grid-cols-3 gap-3 pt-1">
            <div class="p-3.5 rounded-2xl bg-[#333231]/80 border border-[#EDC80A]/20 backdrop-blur-md flex flex-col gap-2 hover:border-[#EDC80A]/50 transition-all shadow-lg">
              <div class="w-9 h-9 rounded-xl bg-[#EDC80A]/20 text-[#EDC80A] flex items-center justify-center border border-[#EDC80A]/40">
                <MapPin class="w-4 h-4" />
              </div>
              <div>
                <div class="font-bold text-xs text-white">GPS Geotagging</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Satelit Akurasi Tinggi</div>
              </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-[#333231]/80 border border-[#F97316]/20 backdrop-blur-md flex flex-col gap-2 hover:border-[#F97316]/50 transition-all shadow-lg">
              <div class="w-9 h-9 rounded-xl bg-[#F97316]/20 text-[#F97316] flex items-center justify-center border border-[#F97316]/40">
                <ShieldCheck class="w-4 h-4" />
              </div>
              <div>
                <div class="font-bold text-xs text-white">Bukti Digital Asli</div>
                <div class="text-[10px] text-slate-400 mt-0.5">SHA-256 Validated</div>
              </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-[#333231]/80 border border-[#EDC80A]/20 backdrop-blur-md flex flex-col gap-2 hover:border-[#EDC80A]/50 transition-all shadow-lg">
              <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-[#EDC80A] flex items-center justify-center border border-[#EDC80A]/40">
                <FileCheck2 class="w-4 h-4" />
              </div>
              <div>
                <div class="font-bold text-xs text-white">BA Opname Instan</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Sertifikasi Dokumen</div>
              </div>
            </div>
          </div>

          <!-- Quick Interactive Guide Box -->
          <div class="rounded-2xl bg-[#333231]/90 border border-slate-700/60 p-4 backdrop-blur-md space-y-2.5 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-700/60 pb-2">
              <div class="flex items-center gap-2">
                <BookOpen class="w-4 h-4 text-[#EDC80A]" />
                <span class="text-xs font-bold text-white">Panduan Alur Sistem Singkat</span>
              </div>
              <div class="flex items-center gap-1 bg-[#1E1E1D] p-0.5 rounded-lg border border-slate-700">
                <button
                  type="button"
                  @click="activeGuide = 'field'"
                  :class="['px-2.5 py-0.5 rounded text-[10px] font-bold transition-all cursor-pointer', activeGuide === 'field' ? 'bg-[#EDC80A] text-[#333231]' : 'text-slate-400 hover:text-white']"
                >
                  Teknisi
                </button>
                <button
                  type="button"
                  @click="activeGuide = 'supervisor'"
                  :class="['px-2.5 py-0.5 rounded text-[10px] font-bold transition-all cursor-pointer', activeGuide === 'supervisor' ? 'bg-[#F97316] text-white' : 'text-slate-400 hover:text-white']"
                >
                  Pengawas
                </button>
                <button
                  type="button"
                  @click="activeGuide = 'client'"
                  :class="['px-2.5 py-0.5 rounded text-[10px] font-bold transition-all cursor-pointer', activeGuide === 'client' ? 'bg-[#EDC80A] text-[#333231]' : 'text-slate-400 hover:text-white']"
                >
                  Klien
                </button>
              </div>
            </div>

            <div v-if="activeGuide === 'field'" class="text-xs text-slate-300 space-y-1">
              <p>• <strong class="text-[#EDC80A]">Check-In Otomatis:</strong> Buka SPK di lokasi cabang dan sistem mengunci koordinat GPS.</p>
              <p>• <strong class="text-[#EDC80A]">Foto 3 Fase:</strong> Dokumentasikan foto <em>Before</em>, <em>Process</em> (wajib APD), dan <em>After</em>.</p>
              <p>• <strong class="text-[#EDC80A]">Stempel Resmi:</strong> Jam, tanggal, nama jalan, koordinat & peta satelit tercetak otomatis.</p>
            </div>
            <div v-if="activeGuide === 'supervisor'" class="text-xs text-slate-300 space-y-1">
              <p>• <strong class="text-[#F97316]">Verifikasi Evidensi:</strong> Review hasil foto lapangan, koordinat GPS, dan kelengkapan APD.</p>
              <p>• <strong class="text-[#F97316]">Persetujuan/Revisi:</strong> Berikan approval atau catatan revisi langsung ke tim lapangan.</p>
            </div>
            <div v-if="activeGuide === 'client'" class="text-xs text-slate-300 space-y-1">
              <p>• <strong class="text-[#EDC80A]">Monitoring Real-Time:</strong> Pantau progres instalasi & maintenance di seluruh cabang.</p>
              <p>• <strong class="text-[#EDC80A]">Unduh BA Digital:</strong> Download Berita Acara resmi ber-QR Code untuk kelancaran penagihan.</p>
            </div>
          </div>

        </div>

        <!-- Sisi Kanan: Kartu Login Glassmorphism Konsep 1 (Mobile & Desktop) -->
        <div class="lg:col-span-5 w-full max-w-md mx-auto flex flex-col justify-center">
          
          <div class="rounded-3xl bg-[#333231]/90 border border-[#EDC80A]/30 p-5 sm:p-7 shadow-2xl backdrop-blur-xl space-y-4 relative overflow-hidden">
            
            <!-- Glow Corner Accent -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-[#EDC80A]/20 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-[#F97316]/15 rounded-full blur-2xl pointer-events-none"></div>

            <!-- Header Card -->
            <div class="space-y-1 text-center sm:text-left">
              <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-[#EDC80A]/15 text-[#EDC80A] text-[10px] font-extrabold border border-[#EDC80A]/30 uppercase tracking-wide">
                <span>Portal Resmi Vendor</span>
              </div>
              <h3 class="text-xl sm:text-2xl font-black text-white tracking-tight">Selamat Datang Kembali</h3>
              <p class="text-xs text-slate-300">Masuk ke portal operasional & manajemen vendor terpadu.</p>
            </div>

            <!-- Error Banner -->
            <div v-if="error" class="p-3 bg-rose-500/15 border border-rose-500/40 rounded-xl text-rose-200 text-xs flex items-center gap-2 animate-shake">
              <AlertCircle class="w-4 h-4 shrink-0 text-rose-400" />
              <span>{{ error }}</span>
            </div>

            <!-- Form Login -->
            <form @submit.prevent="handleLogin" class="space-y-3.5">
              
              <!-- Input Email / Username -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-200">Email atau ID Pengguna</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <User class="w-4 h-4 text-[#EDC80A]" />
                  </div>
                  <input
                    type="email"
                    required
                    v-model="email"
                    placeholder="nama@perusahaan.com"
                    class="w-full pl-10 pr-3.5 py-2.5 sm:py-3 bg-[#1E1E1D]/90 border border-slate-700 rounded-xl text-xs sm:text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-[#EDC80A] focus:ring-2 focus:ring-[#EDC80A]/20 transition-all shadow-inner"
                  />
                </div>
              </div>

              <!-- Input Password with Eye Toggle -->
              <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                  <label class="block text-xs font-bold text-slate-200">Kata Sandi (Password)</label>
                  <a
                    href="#"
                    @click.prevent="alertContactAdmin"
                    class="text-[11px] font-bold text-[#EDC80A] hover:text-[#FACC15] transition-colors"
                  >
                    Lupa Kata Sandi?
                  </a>
                </div>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <Lock class="w-4 h-4 text-[#EDC80A]" />
                  </div>
                  <input
                    :type="showPassword ? 'text' : 'password'"
                    required
                    v-model="password"
                    placeholder="••••••••"
                    class="w-full pl-10 pr-10 py-2.5 sm:py-3 bg-[#1E1E1D]/90 border border-slate-700 rounded-xl text-xs sm:text-sm text-white placeholder:text-slate-500 focus:outline-none focus:border-[#EDC80A] focus:ring-2 focus:ring-[#EDC80A]/20 transition-all shadow-inner"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white cursor-pointer"
                  >
                    <EyeOff v-if="showPassword" class="w-4 h-4" />
                    <Eye v-else class="w-4 h-4" />
                  </button>
                </div>
              </div>

              <!-- Remember Me Toggle -->
              <div class="flex items-center justify-between text-xs pt-0.5">
                <label class="flex items-center gap-2 cursor-pointer text-slate-300 hover:text-white select-none">
                  <input
                    type="checkbox"
                    v-model="rememberMe"
                    class="w-4 h-4 rounded bg-[#1E1E1D] border-slate-700 text-[#EDC80A] focus:ring-[#EDC80A]/30 accent-[#EDC80A]"
                  />
                  <span>Ingat sesi perangkat ini</span>
                </label>
              </div>

              <!-- Primary Submit Button (Gradient Kuning #EDC80A -> Orange #F97316) -->
              <button
                type="submit"
                :disabled="loading"
                class="w-full py-3 sm:py-3.5 bg-gradient-to-r from-[#EDC80A] via-amber-500 to-[#F97316] hover:from-[#f5d012] hover:to-[#ea580c] disabled:opacity-50 text-[#1E1E1D] font-black text-xs sm:text-sm rounded-xl shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer mt-2"
              >
                <Loader2 v-if="loading" class="w-4 h-4 animate-spin text-[#1E1E1D]" />
                <span v-else>Masuk ke Akun</span>
                <ArrowRight v-if="!loading" class="w-4 h-4 text-[#1E1E1D]" />
              </button>

              <!-- Secondary Action: Public Timestamp Camera Shortcut Button -->
              <div class="pt-2">
                <a
                  href="/timestamp"
                  class="w-full py-2.5 sm:py-3 bg-[#1E1E1D] hover:bg-[#282826] border border-[#EDC80A]/40 hover:border-[#EDC80A] text-[#EDC80A] font-bold text-xs rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer text-center group"
                >
                  <Camera class="w-4 h-4 text-[#EDC80A] group-hover:scale-110 transition-transform" />
                  <span>Buka Kamera Timestamp GPS (Tanpa Login)</span>
                </a>
              </div>

            </form>

            <!-- Bottom Security Trust Badges -->
            <div class="pt-3 border-t border-slate-700/60 flex items-center justify-between text-[10px] text-slate-400">
              <span class="flex items-center gap-1.5 font-mono">
                <Lock class="w-3 h-3 text-emerald-400" />
                <span>256-Bit SSL Secured</span>
              </span>
              <a
                href="https://wa.me/6282388885251"
                target="_blank"
                class="flex items-center gap-1 text-[#EDC80A] hover:underline font-bold"
              >
                <Phone class="w-3 h-3 text-[#EDC80A]" />
                <span>Bantuan Admin</span>
              </a>
            </div>

          </div>

        </div>

      </div>
    </main>

    <!-- Footer Copyright (Clean & Compact) -->
    <footer class="p-2 text-center text-[10px] text-slate-400 border-t border-slate-800/80 z-10 font-mono shrink-0 w-full max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-1">
      <span>© 2026 PT Sinar Grafika (SGX). Hak Cipta Dilindungi Undang-Undang.</span>
      <span class="text-slate-500">vendor.sinargrafika.my.id</span>
    </footer>

  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuth } from '../../composables/useAuth';
import {
  User,
  Lock,
  Eye,
  EyeOff,
  ArrowRight,
  AlertCircle,
  Loader2,
  Camera,
  MapPin,
  ShieldCheck,
  FileCheck2,
  BookOpen,
  Phone,
  Sparkles
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
