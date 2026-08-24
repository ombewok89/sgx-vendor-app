<template>
  <div class="min-h-screen bg-[#0B0F19] text-slate-100 font-sans selection:bg-[#EDC80A] selection:text-[#1E1E1D] flex flex-col relative overflow-x-hidden">
    
    <!-- Ambient Radial Glow Accents (Hitam Obsidian, Emas #EDC80A & Oranye #F59E0B) -->
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-[#EDC80A]/10 rounded-full blur-3xl pointer-events-none -translate-y-1/2"></div>
    <div class="fixed bottom-0 right-1/4 w-[30rem] h-[30rem] bg-[#F59E0B]/10 rounded-full blur-3xl pointer-events-none translate-y-1/2"></div>
    <div class="fixed top-1/2 right-5 w-80 h-80 bg-purple-600/5 rounded-full blur-3xl pointer-events-none -translate-y-1/2"></div>

    <!-- Top Brand Header Bar (Luxury Enterprise Glass) -->
    <header class="border-b border-amber-500/20 bg-[#111827]/90 backdrop-blur-2xl sticky top-0 z-40 px-4 py-3 sm:py-3.5 shadow-xl shadow-black/40">
      <div class="max-w-6xl mx-auto flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <!-- Logo Resmi Perusahaan (Gold Emblem) -->
          <div class="flex items-center">
            <img
              src="/sgx_logo.png"
              alt="PT Sinar Kreasindo Bencoolen Logo"
              class="h-9 sm:h-11 w-9 sm:w-11 object-contain rounded-2xl shadow-lg shadow-amber-500/20 border border-[#EDC80A]/40"
            />
          </div>
          <div class="flex flex-col">
            <div class="flex items-center gap-1.5 flex-wrap">
              <h1 class="font-black text-xs sm:text-sm text-white tracking-tight flex items-center gap-1.5">
                <span>PT Sinar Kreasindo Bencoolen</span>
                <span class="text-[9px] font-extrabold uppercase tracking-widest text-[#EDC80A] font-mono bg-[#1E1E1D] px-1.5 py-0.2 rounded border border-[#EDC80A]/30">
                  SGX
                </span>
              </h1>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#1E1E1D] text-[#EDC80A] border border-[#EDC80A]/30 shadow-xs">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                LIVE TRACKER
              </span>
            </div>
            <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium">Real-Time Field Evidence & Project Verification Portal</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="handleShare"
            class="px-3 py-1.5 rounded-xl bg-[#1E1E1D] hover:bg-[#282826] border border-[#EDC80A]/30 hover:border-[#EDC80A] text-[#EDC80A] text-xs font-bold transition-all shadow-xs cursor-pointer active:scale-95 flex items-center gap-1.5"
            title="Bagikan Tautan Pemantauan"
          >
            <Share2 class="w-3.5 h-3.5" />
            <span class="hidden sm:inline">Bagikan</span>
          </button>

          <button
            @click="fetchTrackingData"
            :disabled="loading"
            class="p-2 rounded-xl bg-[#1E1E1D] hover:bg-[#282826] text-slate-300 hover:text-white transition-all border border-slate-700 shadow-xs cursor-pointer active:scale-95"
            title="Segarkan Data Real-Time"
          >
            <RefreshCw :class="['w-4 h-4', loading ? 'animate-spin text-[#EDC80A]' : '']" />
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content Stage -->
    <main class="flex-1 max-w-6xl w-full mx-auto p-4 sm:p-6 md:p-8 space-y-6 relative z-10">
      
      <!-- Loading State -->
      <div v-if="loading && !wo" class="py-24 flex flex-col items-center justify-center space-y-3">
        <Loader2 class="w-10 h-10 animate-spin text-[#EDC80A]" />
        <p class="text-xs font-mono text-slate-400 tracking-wider">Menghubungkan ke satelit pelacakan SPK...</p>
      </div>

      <!-- Error / Inactive State -->
      <div v-else-if="errorMessage" class="py-20 text-center space-y-4 max-w-md mx-auto">
        <div class="w-16 h-16 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 flex items-center justify-center mx-auto shadow-lg">
          <ShieldAlert class="w-8 h-8" />
        </div>
        <h2 class="text-lg font-bold text-white">Akses Pemantauan Tidak Ditemukan</h2>
        <p class="text-xs text-slate-400 leading-relaxed">{{ errorMessage }}</p>
        <div class="pt-2">
          <a
            href="/"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#EDC80A] to-[#F59E0B] text-[#1E1E1D] text-xs font-black transition-all shadow-lg hover:shadow-amber-500/20 active:scale-95"
          >
            <Home class="w-4 h-4" />
            <span>Kembali ke Beranda</span>
          </a>
        </div>
      </div>

      <!-- Loaded Work Order Tracking View -->
      <div v-else-if="wo" class="space-y-6 animate-fade-in">
        
        <!-- Work Order Hero Card (Luxury Dark Glassmorphic) -->
        <div class="bg-[#111827]/90 border border-amber-500/20 rounded-3xl p-5 sm:p-7 shadow-2xl backdrop-blur-2xl relative overflow-hidden space-y-5">
          <!-- Background Glow Accent -->
          <div class="absolute -top-24 -right-24 w-72 h-72 bg-[#EDC80A]/10 rounded-full blur-3xl pointer-events-none"></div>
          <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-[#F59E0B]/10 rounded-full blur-3xl pointer-events-none"></div>

          <!-- Header Section -->
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800/80 pb-5">
            <div class="space-y-1.5">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-mono font-black text-[#EDC80A] bg-[#1E1E1D] px-3 py-1 rounded-xl border border-[#EDC80A]/30 shadow-xs">
                  {{ wo.spk_number }}
                </span>
                <span class="text-xs font-bold text-slate-300 bg-slate-800/80 px-2.5 py-0.5 rounded-lg border border-slate-700">
                  📍 Area: {{ wo.area_name || '-' }}
                </span>
                <span v-if="wo.doc_mode" class="text-[10px] font-mono text-slate-400 bg-slate-900 px-2 py-0.5 rounded border border-slate-800">
                  Mode: {{ wo.doc_mode }}
                </span>
              </div>
              <h2 class="text-xl sm:text-2xl font-black text-white leading-tight tracking-tight">{{ wo.location_name }}</h2>
              <p class="text-xs sm:text-sm text-slate-300 font-medium">{{ wo.title }}</p>
            </div>

            <!-- Dynamic Status Badge -->
            <div class="shrink-0 flex items-center gap-2">
              <div
                :class="[
                  'px-4 py-2 rounded-2xl border text-xs font-black uppercase tracking-wider flex items-center gap-2.5 shadow-lg',
                  getStatusBadgeClass(wo.status)
                ]"
              >
                <span class="w-2.5 h-2.5 rounded-full animate-ping" :class="getStatusDotClass(wo.status)"></span>
                <span>{{ getStatusLabel(wo.status) }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Metadata Grid -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div class="p-3 bg-[#0B0F19]/60 rounded-2xl border border-slate-800/80 space-y-1">
              <span class="text-slate-500 block text-[11px]">Perusahaan Klien:</span>
              <strong class="text-white font-bold flex items-center gap-1.5">
                <img
                  v-if="wo.vendor?.logo_url"
                  :src="getFileUrl(wo.vendor.logo_url)"
                  :alt="wo.vendor?.name"
                  class="w-4 h-4 object-contain rounded shrink-0"
                />
                <Building2 v-else class="w-3.5 h-3.5 text-[#EDC80A] shrink-0" />
                <span class="truncate">{{ wo.vendor?.name || 'Client SGX' }}</span>
              </strong>
            </div>
            <div class="p-3 bg-[#0B0F19]/60 rounded-2xl border border-slate-800/80 space-y-1">
              <span class="text-slate-500 block text-[11px]">PIC Teknisi Lapangan:</span>
              <strong class="text-white font-bold flex items-center gap-1.5">
                <User class="w-3.5 h-3.5 text-emerald-400 shrink-0" />
                <span class="truncate">{{ wo.pic?.name || 'Tim Lapangan SGX' }}</span>
              </strong>
            </div>
            <div class="p-3 bg-[#0B0F19]/60 rounded-2xl border border-slate-800/80 space-y-1">
              <span class="text-slate-500 block text-[11px]">Batas Waktu (SLA):</span>
              <strong class="text-white font-bold flex items-center gap-1.5 font-mono">
                <Calendar class="w-3.5 h-3.5 text-amber-400 shrink-0" />
                <span>{{ wo.deadline ? formatDate(wo.deadline) : '-' }}</span>
              </strong>
            </div>
            <div class="p-3 bg-[#0B0F19]/60 rounded-2xl border border-slate-800/80 space-y-1">
              <span class="text-slate-500 block text-[11px]">Presensi GPS Cabang:</span>
              <strong class="font-bold flex items-center gap-1.5" :class="wo.check_in ? 'text-emerald-400' : 'text-amber-400'">
                <MapPin class="w-3.5 h-3.5 shrink-0" />
                <span>{{ wo.check_in ? 'Hadir di Lokasi ✓' : 'Menunggu Tiba' }}</span>
              </strong>
            </div>
          </div>

          <!-- SPK Notes & Special Instructions from Admin -->
          <div
            v-if="wo.notes"
            class="p-4 bg-gradient-to-r from-amber-500/10 via-[#EDC80A]/5 to-transparent border border-amber-500/20 rounded-2xl space-y-1 text-xs"
          >
            <div class="flex items-center gap-2 text-[#EDC80A] font-bold text-[11px] uppercase tracking-wider">
              <FileText class="w-4 h-4 text-[#EDC80A] shrink-0" />
              <span>Catatan & Instruksi Khusus Proyek:</span>
            </div>
            <p class="text-slate-300 font-medium whitespace-pre-line pl-6 leading-relaxed">
              {{ wo.notes }}
            </p>
          </div>

          <!-- Quality Assurance Revision Banner -->
          <div
            v-if="wo.status === 'REVISION'"
            class="p-4 bg-gradient-to-r from-rose-500/15 via-amber-500/10 to-transparent border-2 border-rose-500/40 rounded-2xl space-y-2 text-xs animate-fade-in"
          >
            <div class="flex items-center justify-between">
              <div class="font-black text-rose-300 flex items-center gap-2 tracking-wide">
                <RotateCcw class="w-4 h-4 text-rose-400 animate-spin-slow" />
                <span>KONTROL MUTU & PENYESUAIAN TEKNIS LAPANGAN (QUALITY ASSURANCE)</span>
              </div>
              <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase bg-rose-500/30 text-rose-200 border border-rose-400/40">
                Dalam Penyempurnaan
              </span>
            </div>
            <div class="bg-[#0B0F19]/80 p-3 rounded-xl border border-rose-500/20 text-slate-200 font-medium">
              <p class="italic text-rose-100">
                "{{ latestRevisionReason || 'Pekerjaan sedang disempurnakan oleh teknisi agar memenuhi standar mutu terbaik sebelum disahkan.' }}"
              </p>
            </div>
          </div>

          <!-- Total Real Progress Bar -->
          <div class="pt-2">
            <div class="flex items-center justify-between text-xs font-bold mb-2">
              <span class="text-slate-400 flex items-center gap-1.5">
                <Activity class="w-3.5 h-3.5 text-[#EDC80A]" />
                <span>Akumulasi Kemajuan Fisik Lapangan:</span>
              </span>
              <span class="font-mono text-sm font-black text-[#EDC80A]">{{ wo.progress_percent }}% Selesai</span>
            </div>
            <div class="w-full h-3 bg-slate-950 rounded-full overflow-hidden p-0.5 border border-slate-800">
              <div
                class="h-full rounded-full bg-gradient-to-r from-[#EDC80A] via-amber-500 to-emerald-400 transition-all duration-700 shadow-md shadow-amber-500/20"
                :style="{ width: `${Math.min(100, Math.max(5, wo.progress_percent))}%` }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Real-Time Milestone Stepper (Bento Luxury 4 Steps) -->
        <div class="bg-[#111827]/90 border border-amber-500/20 rounded-3xl p-5 sm:p-6 shadow-2xl backdrop-blur-2xl space-y-4">
          <h3 class="font-black text-xs uppercase text-[#EDC80A] tracking-wider flex items-center gap-2">
            <Sparkles class="w-4 h-4 text-[#EDC80A]" />
            <span>4 Tahapan Pengerjaan Real-Time Terverifikasi</span>
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
            <!-- Step 1: Penugasan -->
            <div class="p-3.5 rounded-2xl border bg-[#0B0F19]/70 border-emerald-500/30 space-y-1 shadow-xs">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center text-xs font-black">
                  ✓
                </div>
                <span class="font-bold text-xs text-white">1. Penunjukan SPK</span>
              </div>
              <p class="text-[11px] text-slate-400 pl-8">SPK resmi diterbitkan untuk tim teknisi.</p>
            </div>

            <!-- Step 2: Check-In GPS -->
            <div
              :class="[
                'p-3.5 rounded-2xl border space-y-1 shadow-xs transition-all',
                wo.check_in 
                  ? 'bg-[#0B0F19]/70 border-emerald-500/40 text-emerald-300' 
                  : 'bg-[#0B0F19]/30 border-slate-800 text-slate-500'
              ]"
            >
              <div class="flex items-center gap-2">
                <div
                  :class="[
                    'w-6 h-6 rounded-full flex items-center justify-center text-xs font-black border',
                    wo.check_in ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40' : 'bg-slate-800 text-slate-500 border-slate-700'
                  ]"
                >
                  {{ wo.check_in ? '✓' : '2' }}
                </div>
                <span class="font-bold text-xs" :class="wo.check_in ? 'text-white' : 'text-slate-400'">2. Presensi GPS</span>
              </div>
              <p class="text-[11px] text-slate-400 pl-8">
                {{ wo.check_in ? `Hadir di radius toko (${wo.check_in.check_in_time || 'Tervalidasi'})` : 'Teknisi menuju lokasi toko.' }}
              </p>
            </div>

            <!-- Step 3: Evidensi Foto -->
            <div
              :class="[
                'p-3.5 rounded-2xl border space-y-1 shadow-xs transition-all',
                wo.photos?.length > 0 
                  ? 'bg-[#0B0F19]/70 border-[#EDC80A]/40' 
                  : 'bg-[#0B0F19]/30 border-slate-800'
              ]"
            >
              <div class="flex items-center gap-2">
                <div
                  :class="[
                    'w-6 h-6 rounded-full flex items-center justify-center text-xs font-black border',
                    wo.photos?.length > 0 ? 'bg-amber-500/20 text-[#EDC80A] border-amber-500/40' : 'bg-slate-800 text-slate-500 border-slate-700'
                  ]"
                >
                  {{ wo.photos?.length > 0 ? '✓' : '3' }}
                </div>
                <span class="font-bold text-xs" :class="wo.photos?.length > 0 ? 'text-white' : 'text-slate-400'">3. Dokumentasi Foto</span>
              </div>
              <p class="text-[11px] text-slate-400 pl-8">
                {{ wo.photos?.length > 0 ? `${wo.photos.length} foto bukti terverifikasi.` : 'Menunggu unggahan foto.' }}
              </p>
            </div>

            <!-- Step 4: Selesai / BA -->
            <div
              :class="[
                'p-3.5 rounded-2xl border space-y-1 shadow-xs transition-all',
                ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status) 
                  ? 'bg-[#0B0F19]/70 border-emerald-500/40' 
                  : 'bg-[#0B0F19]/30 border-slate-800'
              ]"
            >
              <div class="flex items-center gap-2">
                <div
                  :class="[
                    'w-6 h-6 rounded-full flex items-center justify-center text-xs font-black border',
                    ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status) ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40' : 'bg-slate-800 text-slate-500 border-slate-700'
                  ]"
                >
                  {{ ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status) ? '✓' : '4' }}
                </div>
                <span class="font-bold text-xs" :class="['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status) ? 'text-white' : 'text-slate-400'">4. Pengesahan BA</span>
              </div>
              <p class="text-[11px] text-slate-400 pl-8">
                {{ wo.ba_document ? `BA No. ${wo.ba_document.ba_number}` : 'Pekerjaan selesai & BA terbit.' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Official Berita Acara (BA) Opname Certificate Showcase Card (Visible on Completion) -->
        <div
          v-if="wo.ba_document || ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status)"
          class="p-5 sm:p-7 rounded-3xl bg-gradient-to-br from-amber-500/15 via-[#111827] to-emerald-500/10 border-2 border-[#EDC80A]/40 shadow-2xl backdrop-blur-2xl space-y-4 animate-fade-in"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
              <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#EDC80A] to-amber-600 text-[#1E1E1D] flex items-center justify-center font-black shadow-lg shadow-amber-500/25 shrink-0">
                <FileCheck2 class="w-6 h-6" />
              </div>
              <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-[#EDC80A]/20 text-[#EDC80A] text-[10px] font-black border border-[#EDC80A]/40 uppercase tracking-wider mb-1">
                  <span>Dokumen Sah & Tersertifikasi</span>
                </div>
                <h4 class="text-base sm:text-lg font-black text-white tracking-tight">
                  Berita Acara (BA) Opname Fisik Telah Diterbitkan
                </h4>
                <p class="text-xs text-slate-300 font-mono mt-0.5">
                  Nomor BA: <strong class="text-[#EDC80A]">{{ wo.ba_document?.ba_number || `BA-SGX-${wo.spk_number}` }}</strong>
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
              <a
                v-if="wo.ba_document?.id"
                :href="`/api/ba-documents/${wo.ba_document.id}/pdf`"
                target="_blank"
                class="px-5 py-2.5 bg-gradient-to-r from-[#EDC80A] via-amber-500 to-[#F59E0B] hover:from-[#f5d012] hover:to-[#ea580c] text-[#1E1E1D] font-black text-xs rounded-xl shadow-lg shadow-amber-500/25 active:scale-95 transition-all flex items-center gap-2 cursor-pointer"
              >
                <Download class="w-4 h-4" />
                <span>Unduh Dokumen BA (PDF)</span>
              </a>
              <button
                v-else
                @click="alertBaReady"
                class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-500 text-white font-black text-xs rounded-xl shadow-lg active:scale-95 transition-all flex items-center gap-2 cursor-pointer"
              >
                <CheckSquare class="w-4 h-4" />
                <span>Pekerjaan Telah Disahkan ✓</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Section: Structured Sub-Tasks & Photo Evidence -->
        <div class="space-y-5">
          <!-- Section Header & Flexible View Controls -->
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-[#111827]/90 border border-amber-500/20 p-4 sm:p-5 rounded-3xl shadow-xl backdrop-blur-2xl">
            <div>
              <h3 class="font-black text-sm uppercase text-slate-100 tracking-wider flex items-center gap-2">
                <Camera class="w-4 h-4 text-[#EDC80A]" />
                <span>Lingkup Sub-Pekerjaan & Evidensi Fisik ({{ displayItems.length }} Item)</span>
              </h3>
              <p class="text-[11px] text-slate-400 mt-0.5">Dokumentasi hasil pengerjaan fisik terverifikasi GPS per sub-lingkup cabang.</p>
            </div>

            <!-- View Mode Switcher Toolbar -->
            <div class="flex items-center gap-1 bg-[#0B0F19] p-1 rounded-2xl border border-slate-800 text-xs font-bold self-start md:self-auto shadow-inner">
              <button
                type="button"
                @click="activeViewMode = 'FOCUSED_TAB'"
                :class="[
                  'px-3 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5 text-[11px]',
                  activeViewMode === 'FOCUSED_TAB' ? 'bg-gradient-to-r from-amber-600 to-amber-700 text-white shadow-md' : 'text-slate-400 hover:text-white'
                ]"
              >
                <Layers class="w-3.5 h-3.5" />
                <span>Tab Sub-Item</span>
              </button>
              <button
                type="button"
                @click="activeViewMode = 'ACCORDION'"
                :class="[
                  'px-3 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5 text-[11px]',
                  activeViewMode === 'ACCORDION' ? 'bg-gradient-to-r from-amber-600 to-amber-700 text-white shadow-md' : 'text-slate-400 hover:text-white'
                ]"
              >
                <ListFilter class="w-3.5 h-3.5" />
                <span>Semua Item</span>
              </button>
              <button
                type="button"
                @click="activeViewMode = 'ALL_PHOTOS'"
                :class="[
                  'px-3 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5 text-[11px]',
                  activeViewMode === 'ALL_PHOTOS' ? 'bg-gradient-to-r from-amber-600 to-amber-700 text-white shadow-md' : 'text-slate-400 hover:text-white'
                ]"
              >
                <LayoutGrid class="w-3.5 h-3.5" />
                <span>Galeri Foto ({{ wo.photos?.length || 0 }})</span>
              </button>
            </div>
          </div>

          <!-- ======================================================== -->
          <!-- MODE 1: FOCUSED SUB-ITEM TAB VIEW (SUPER MOBILE FRIENDLY) -->
          <!-- ======================================================== -->
          <div v-if="activeViewMode === 'FOCUSED_TAB'" class="space-y-4">
            <!-- Horizontal Scrollable Sub-Item Pills Navigation Bar -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar scroll-smooth">
              <button
                v-for="(item, itmIdx) in displayItems"
                :key="item.id || itmIdx"
                type="button"
                @click="selectedItemId = item.id ?? 'default'"
                :class="[
                  'px-4 py-2.5 rounded-2xl border text-left shrink-0 transition-all cursor-pointer flex items-center gap-2.5 shadow-sm',
                  selectedItemId === (item.id ?? 'default')
                    ? 'bg-[#1E1E1D] border-[#EDC80A]/60 text-white ring-2 ring-[#EDC80A]/20'
                    : 'bg-[#111827]/80 border-slate-800 text-slate-400 hover:border-slate-700 hover:text-slate-200'
                ]"
              >
                <span
                  :class="[
                    'w-6 h-6 rounded-lg text-xs font-black flex items-center justify-center shrink-0',
                    selectedItemId === (item.id ?? 'default') ? 'bg-[#EDC80A] text-[#1E1E1D]' : 'bg-slate-800 text-slate-400'
                  ]"
                >
                  {{ itmIdx + 1 }}
                </span>
                <div>
                  <div class="font-bold text-xs flex items-center gap-1.5">
                    <span class="truncate max-w-[150px] sm:max-w-[200px]">{{ item.item_name }}</span>
                    <span v-if="item.is_addendum" class="px-1.5 py-0.2 rounded text-[8px] font-black uppercase bg-amber-500 text-[#1E1E1D]">
                      Addendum
                    </span>
                  </div>
                  <div class="text-[10px] text-slate-400 font-mono flex items-center gap-1.5">
                    <span>{{ getTotalPhotosForItem(item.id) }} Foto</span>
                    <span>•</span>
                    <span :class="isItemCompleted(item) ? 'text-emerald-400' : 'text-amber-400'">
                      {{ isItemCompleted(item) ? 'Selesai ✓' : 'Proses' }}
                    </span>
                  </div>
                </div>
              </button>
            </div>

            <!-- Active Selected Sub-Item Card Container -->
            <div
              v-if="currentSelectedItem"
              class="bg-[#111827]/90 border border-amber-500/20 rounded-3xl p-5 sm:p-7 shadow-2xl backdrop-blur-2xl space-y-6"
            >
              <!-- Item Details Header -->
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                  <span class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#EDC80A] to-amber-600 text-[#1E1E1D] font-black text-sm flex items-center justify-center shadow-md shadow-amber-500/20">
                    #{{ getCurrentItemIndex(currentSelectedItem.id) + 1 }}
                  </span>
                  <div>
                    <div class="flex items-center gap-2">
                      <h4 class="font-black text-slate-100 text-base sm:text-lg">
                        {{ currentSelectedItem.item_name }}
                      </h4>
                      <span
                        v-if="currentSelectedItem.is_addendum"
                        class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-amber-500 text-[#1E1E1D] shadow-sm"
                      >
                        + ADDENDUM
                      </span>
                    </div>
                    <p v-if="currentSelectedItem.notes" class="text-xs text-slate-400 mt-0.5">{{ currentSelectedItem.notes }}</p>
                    <span class="text-[10px] font-mono text-slate-500">Bobot Sub-Pekerjaan: {{ currentSelectedItem.weight_percent || 100 }}%</span>
                  </div>
                </div>

                <!-- Stage Tab Filters inside Sub-Item for Mobile (Segmented Stage Pills) -->
                <div class="flex items-center gap-1 bg-[#0B0F19] p-1 rounded-2xl border border-slate-800 text-xs font-bold overflow-x-auto no-scrollbar scroll-smooth">
                  <!-- 1. AFTER PILL (Hasil Akhir) -->
                  <button
                    type="button"
                    @click="mobileSubStage = 'AFTER'"
                    :class="[
                      'px-3 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5 text-[11px] whitespace-nowrap',
                      mobileSubStage === 'AFTER'
                        ? 'bg-gradient-to-r from-emerald-600 to-teal-500 text-white shadow-md shadow-emerald-900/30'
                        : 'text-slate-400 hover:text-emerald-300'
                    ]"
                  >
                    <Sparkles class="w-3.5 h-3.5" />
                    <span>Sesudah ({{ getPhotosForItemStage(currentSelectedItem.id, 'AFTER').length }})</span>
                  </button>

                  <!-- 2. BEFORE PILL (Kondisi Awal) -->
                  <button
                    type="button"
                    @click="mobileSubStage = 'BEFORE'"
                    :class="[
                      'px-3 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5 text-[11px] whitespace-nowrap',
                      mobileSubStage === 'BEFORE'
                        ? 'bg-gradient-to-r from-blue-700 to-indigo-600 text-white shadow-md shadow-blue-900/30'
                        : 'text-slate-400 hover:text-blue-300'
                    ]"
                  >
                    <Camera class="w-3.5 h-3.5" />
                    <span>Sebelum ({{ getPhotosForItemStage(currentSelectedItem.id, 'BEFORE').length }})</span>
                  </button>

                  <!-- 3. PROCESS PILL (Pengerjaan) -->
                  <button
                    type="button"
                    @click="mobileSubStage = 'PROCESS'"
                    :class="[
                      'px-3 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5 text-[11px] whitespace-nowrap',
                      mobileSubStage === 'PROCESS'
                        ? 'bg-gradient-to-r from-amber-600 to-orange-500 text-white shadow-md shadow-amber-900/30'
                        : 'text-slate-400 hover:text-amber-300'
                    ]"
                  >
                    <Layers class="w-3.5 h-3.5" />
                    <span>Proses ({{ getPhotosForItemStage(currentSelectedItem.id, 'PROCESS').length }})</span>
                  </button>

                  <!-- 4. COMPARE PILL (Before vs After) -->
                  <button
                    type="button"
                    @click="mobileSubStage = 'COMPARE'"
                    :class="[
                      'px-3 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5 text-[11px] whitespace-nowrap',
                      mobileSubStage === 'COMPARE'
                        ? 'bg-gradient-to-r from-[#EDC80A] to-amber-500 text-[#1E1E1D] shadow-md shadow-amber-500/25 font-black'
                        : 'text-slate-400 hover:text-[#EDC80A]'
                    ]"
                  >
                    <span>⚖️ Komparasi B/A</span>
                  </button>

                  <!-- 5. ALL PILL -->
                  <button
                    type="button"
                    @click="mobileSubStage = 'ALL'"
                    :class="[
                      'px-3 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5 text-[11px] whitespace-nowrap',
                      mobileSubStage === 'ALL'
                        ? 'bg-slate-800 text-white shadow-xs'
                        : 'text-slate-400 hover:text-white'
                    ]"
                  >
                    <span>Semua ({{ getTotalPhotosForItem(currentSelectedItem.id) }})</span>
                  </button>
                </div>
              </div>

              <!-- ======================================================== -->
              <!-- STAGE VIEW 1: SINGLE STAGE FOCUSED GRID (BEFORE / PROCESS / AFTER) -->
              <!-- ======================================================== -->
              <div v-if="['AFTER', 'BEFORE', 'PROCESS'].includes(mobileSubStage)" class="space-y-3 animate-fade-in">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span
                      :class="[
                        'px-3 py-1 rounded-xl text-[11px] font-black uppercase tracking-wider border flex items-center gap-1.5',
                        mobileSubStage === 'AFTER'
                          ? 'bg-emerald-950/80 text-emerald-300 border-emerald-500/40'
                          : mobileSubStage === 'BEFORE'
                          ? 'bg-blue-950/80 text-blue-300 border-blue-500/40'
                          : 'bg-amber-950/80 text-amber-300 border-amber-500/40'
                      ]"
                    >
                      <Sparkles v-if="mobileSubStage === 'AFTER'" class="w-3.5 h-3.5" />
                      <Camera v-else-if="mobileSubStage === 'BEFORE'" class="w-3.5 h-3.5" />
                      <Layers v-else class="w-3.5 h-3.5" />
                      <span>TAHAP: {{ mobileSubStage === 'AFTER' ? 'SESUDAH (AFTER) — HASIL SELESAI' : mobileSubStage === 'BEFORE' ? 'SEBELUM (BEFORE) — KONDISI AWAL' : 'PROSES (PROCESS) — PENGERJAAN FISIK' }}</span>
                    </span>
                  </div>

                  <span class="text-xs font-mono text-slate-400">
                    {{ getPhotosForItemStage(currentSelectedItem.id, mobileSubStage).length }} Foto Bukti
                  </span>
                </div>

                <!-- Photos Grid: 2 Columns on Mobile, 3-4 Columns on Desktop -->
                <div
                  v-if="getPhotosForItemStage(currentSelectedItem.id, mobileSubStage).length > 0"
                  class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5"
                >
                  <div
                    v-for="p in getPhotosForItemStage(currentSelectedItem.id, mobileSubStage)"
                    :key="p.id"
                    class="h-52 sm:h-56 rounded-2xl overflow-hidden bg-slate-950 relative group cursor-pointer border border-slate-800 shadow-md hover:border-amber-500/60 transition-all duration-300"
                    @click="openLightbox(p)"
                  >
                    <img
                      :src="getFileUrl(p.file_path)"
                      :alt="mobileSubStage"
                      class="w-full h-full object-cover group-hover:scale-105 transition-all duration-300"
                    />
                    
                    <!-- Overlay Badge & GPS Metadata -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/30 to-transparent flex flex-col justify-between p-3 pointer-events-none">
                      <div class="flex items-center justify-between">
                        <span
                          :class="[
                            'px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider',
                            mobileSubStage === 'AFTER' ? 'bg-emerald-600 text-white' : mobileSubStage === 'BEFORE' ? 'bg-blue-600 text-white' : 'bg-amber-600 text-white'
                          ]"
                        >
                          {{ mobileSubStage }}
                        </span>
                        <span class="text-[10px] bg-black/70 px-2 py-0.5 rounded-md text-slate-200 font-mono">
                          🔍 Klik Zoom
                        </span>
                      </div>

                      <div class="space-y-0.5 text-white font-mono text-[10px]">
                        <div class="text-emerald-300 font-bold flex items-center gap-1">
                          <MapPin class="w-3 h-3 shrink-0" />
                          <span class="truncate">{{ p.latitude ? `${Number(p.latitude).toFixed(5)}, ${Number(p.longitude).toFixed(5)}` : 'GPS Lokasi Valid' }}</span>
                        </div>
                        <div class="text-slate-400 text-[9px]">
                          {{ new Date(p.created_at || Date.now()).toLocaleString('id-ID') }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Empty State for this stage -->
                <div
                  v-else
                  class="py-12 rounded-2xl border border-dashed border-slate-800 bg-[#0B0F19]/40 flex flex-col items-center justify-center text-center p-5 space-y-2"
                >
                  <Camera class="w-8 h-8 text-slate-600 mb-1" />
                  <div class="text-xs font-bold text-slate-300">Belum ada foto dokumentasi tahap {{ mobileSubStage }}.</div>
                  <p class="text-[11px] text-slate-500 max-w-sm">Teknisi lapangan akan mengunggah bukti foto tahap ini saat pengerjaan berlangsung di lokasi.</p>
                </div>
              </div>

              <!-- ======================================================== -->
              <!-- STAGE VIEW 2: SIDE-BY-SIDE BEFORE VS AFTER COMPARISON   -->
              <!-- ======================================================== -->
              <div v-else-if="mobileSubStage === 'COMPARE'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 animate-fade-in">
                <!-- Before Side -->
                <div class="bg-[#0B0F19]/80 border border-slate-800 rounded-2xl p-4 space-y-3">
                  <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase bg-blue-950 text-blue-300 border border-blue-500/40 flex items-center gap-1">
                      <Camera class="w-3 h-3" />
                      <span>BEFORE (KONDISI AWAL)</span>
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">{{ getPhotosForItemStage(currentSelectedItem.id, 'BEFORE').length }} Foto</span>
                  </div>
                  <div v-if="getPhotosForItemStage(currentSelectedItem.id, 'BEFORE').length > 0" class="grid grid-cols-1 gap-2.5">
                    <div
                      v-for="p in getPhotosForItemStage(currentSelectedItem.id, 'BEFORE')"
                      :key="p.id"
                      class="h-48 rounded-xl overflow-hidden bg-slate-900 relative group cursor-pointer border border-slate-800"
                      @click="openLightbox(p)"
                    >
                      <img :src="getFileUrl(p.file_path)" alt="Before" class="w-full h-full object-cover group-hover:scale-105 transition-all duration-300" />
                      <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-2.5 text-[10px] text-white font-mono flex items-center justify-between">
                        <span class="text-blue-300 font-bold">BEFORE</span>
                        <span class="text-slate-400">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-else class="h-40 rounded-xl border border-dashed border-slate-800 flex items-center justify-center text-slate-500 text-xs">
                    Belum ada foto Before
                  </div>
                </div>

                <!-- After Side -->
                <div class="bg-[#0B0F19]/80 border border-slate-800 rounded-2xl p-4 space-y-3">
                  <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase bg-emerald-950 text-emerald-300 border border-emerald-500/40 flex items-center gap-1">
                      <Sparkles class="w-3 h-3" />
                      <span>AFTER (HASIL SELESAI)</span>
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">{{ getPhotosForItemStage(currentSelectedItem.id, 'AFTER').length }} Foto</span>
                  </div>
                  <div v-if="getPhotosForItemStage(currentSelectedItem.id, 'AFTER').length > 0" class="grid grid-cols-1 gap-2.5">
                    <div
                      v-for="p in getPhotosForItemStage(currentSelectedItem.id, 'AFTER')"
                      :key="p.id"
                      class="h-48 rounded-xl overflow-hidden bg-slate-900 relative group cursor-pointer border border-slate-800"
                      @click="openLightbox(p)"
                    >
                      <img :src="getFileUrl(p.file_path)" alt="After" class="w-full h-full object-cover group-hover:scale-105 transition-all duration-300" />
                      <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-2.5 text-[10px] text-white font-mono flex items-center justify-between">
                        <span class="text-emerald-300 font-bold">AFTER</span>
                        <span class="text-slate-400">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-else class="h-40 rounded-xl border border-dashed border-slate-800 flex items-center justify-center text-slate-500 text-xs">
                    Belum ada foto After
                  </div>
                </div>
              </div>

              <!-- ======================================================== -->
              <!-- STAGE VIEW 3: STANDARD 3-COLUMN OVERVIEW (ALL STAGES)    -->
              <!-- ======================================================== -->
              <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4 animate-fade-in">
                <!-- BEFORE -->
                <div class="bg-[#0B0F19]/80 border border-slate-800 rounded-2xl p-3.5 space-y-2.5">
                  <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase bg-blue-950 text-blue-300 border border-blue-500/40">
                      BEFORE
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">{{ getPhotosForItemStage(currentSelectedItem.id, 'BEFORE').length }} Foto</span>
                  </div>
                  <div v-if="getPhotosForItemStage(currentSelectedItem.id, 'BEFORE').length > 0" class="space-y-2">
                    <div
                      v-for="p in getPhotosForItemStage(currentSelectedItem.id, 'BEFORE')"
                      :key="p.id"
                      class="h-36 rounded-xl overflow-hidden bg-slate-900 relative group cursor-pointer border border-slate-800/80"
                      @click="openLightbox(p)"
                    >
                      <img :src="getFileUrl(p.file_path)" alt="Before" class="w-full h-full object-cover group-hover:scale-105 transition-all" />
                      <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-2 text-[9px] text-white font-mono flex items-center justify-between">
                        <span class="text-blue-300 font-bold">BEFORE</span>
                        <span class="text-slate-400">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-else class="h-28 rounded-xl border border-dashed border-slate-800 flex items-center justify-center text-slate-500 text-xs">
                    Belum ada foto
                  </div>
                </div>

                <!-- PROCESS -->
                <div class="bg-[#0B0F19]/80 border border-slate-800 rounded-2xl p-3.5 space-y-2.5">
                  <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase bg-amber-950 text-amber-300 border border-amber-500/40">
                      PROCESS
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">{{ getPhotosForItemStage(currentSelectedItem.id, 'PROCESS').length }} Foto</span>
                  </div>
                  <div v-if="getPhotosForItemStage(currentSelectedItem.id, 'PROCESS').length > 0" class="space-y-2">
                    <div
                      v-for="p in getPhotosForItemStage(currentSelectedItem.id, 'PROCESS')"
                      :key="p.id"
                      class="h-36 rounded-xl overflow-hidden bg-slate-900 relative group cursor-pointer border border-slate-800/80"
                      @click="openLightbox(p)"
                    >
                      <img :src="getFileUrl(p.file_path)" alt="Process" class="w-full h-full object-cover group-hover:scale-105 transition-all" />
                      <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-2 text-[9px] text-white font-mono flex items-center justify-between">
                        <span class="text-amber-300 font-bold">PROCESS</span>
                        <span class="text-slate-400">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-else class="h-28 rounded-xl border border-dashed border-slate-800 flex items-center justify-center text-slate-500 text-xs">
                    Belum ada foto
                  </div>
                </div>

                <!-- AFTER -->
                <div class="bg-[#0B0F19]/80 border border-slate-800 rounded-2xl p-3.5 space-y-2.5">
                  <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase bg-emerald-950 text-emerald-300 border border-emerald-500/40">
                      AFTER
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">{{ getPhotosForItemStage(currentSelectedItem.id, 'AFTER').length }} Foto</span>
                  </div>
                  <div v-if="getPhotosForItemStage(currentSelectedItem.id, 'AFTER').length > 0" class="space-y-2">
                    <div
                      v-for="p in getPhotosForItemStage(currentSelectedItem.id, 'AFTER')"
                      :key="p.id"
                      class="h-36 rounded-xl overflow-hidden bg-slate-900 relative group cursor-pointer border border-slate-800/80"
                      @click="openLightbox(p)"
                    >
                      <img :src="getFileUrl(p.file_path)" alt="After" class="w-full h-full object-cover group-hover:scale-105 transition-all" />
                      <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-2 text-[9px] text-white font-mono flex items-center justify-between">
                        <span class="text-emerald-300 font-bold">AFTER</span>
                        <span class="text-slate-400">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-else class="h-28 rounded-xl border border-dashed border-slate-800 flex items-center justify-center text-slate-500 text-xs">
                    Belum ada foto
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ======================================================== -->
          <!-- MODE 2: ACCORDION LIST (ALL SUB-ITEMS EXTENDED)          -->
          <!-- ======================================================== -->
          <div v-else-if="activeViewMode === 'ACCORDION'" class="space-y-4">
            <div
              v-for="(item, itmIdx) in displayItems"
              :key="item.id || itmIdx"
              class="bg-[#111827]/90 border border-amber-500/20 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4 backdrop-blur-2xl"
            >
              <!-- Sub-Task Header -->
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                  <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-[#EDC80A] to-amber-600 text-[#1E1E1D] font-black text-xs flex items-center justify-center shadow-md">
                    {{ itmIdx + 1 }}
                  </span>
                  <div>
                    <div class="flex items-center gap-2">
                      <h4 class="font-black text-slate-100 text-sm sm:text-base">
                        {{ item.item_name }}
                      </h4>
                      <span
                        v-if="item.is_addendum"
                        class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-amber-500 text-[#1E1E1D] shadow-md"
                      >
                        + ADDENDUM
                      </span>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400">Bobot: {{ item.weight_percent || 100 }}%</span>
                  </div>
                </div>

                <span
                  :class="[
                    'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border',
                    isItemCompleted(item)
                      ? 'bg-emerald-950 text-emerald-300 border-emerald-500/40'
                      : 'bg-amber-950 text-amber-300 border-amber-500/40'
                  ]"
                >
                  {{ isItemCompleted(item) ? 'Evidensi Lengkap ✓' : 'Dalam Pengerjaan ⏳' }}
                </span>
              </div>

              <!-- Grid Before / Process / After for this Sub-Task -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- BEFORE -->
                <div class="bg-[#0B0F19]/80 border border-slate-800/80 rounded-2xl p-3 space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-blue-950 text-blue-300 border border-blue-500/30">
                      BEFORE
                    </span>
                    <span class="text-[9px] font-mono text-slate-500">{{ getPhotosForItemStage(item.id, 'BEFORE').length }} Foto</span>
                  </div>
                  <div v-if="getPhotosForItemStage(item.id, 'BEFORE').length > 0" class="space-y-2">
                    <div
                      v-for="p in getPhotosForItemStage(item.id, 'BEFORE')"
                      :key="p.id"
                      class="h-32 rounded-xl overflow-hidden bg-slate-900 relative group cursor-pointer"
                      @click="openLightbox(p)"
                    >
                      <img :src="getFileUrl(p.file_path)" alt="Before" class="w-full h-full object-cover group-hover:scale-105 transition-all" />
                      <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-1.5 text-[9px] text-white font-mono">
                        <span class="text-blue-300">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-else class="h-24 rounded-xl border border-dashed border-slate-800 flex items-center justify-center text-slate-500 text-[10px]">
                    Belum ada foto
                  </div>
                </div>

                <!-- PROCESS -->
                <div class="bg-[#0B0F19]/80 border border-slate-800/80 rounded-2xl p-3 space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-950 text-amber-300 border border-amber-500/30">
                      PROCESS
                    </span>
                    <span class="text-[9px] font-mono text-slate-500">{{ getPhotosForItemStage(item.id, 'PROCESS').length }} Foto</span>
                  </div>
                  <div v-if="getPhotosForItemStage(item.id, 'PROCESS').length > 0" class="space-y-2">
                    <div
                      v-for="p in getPhotosForItemStage(item.id, 'PROCESS')"
                      :key="p.id"
                      class="h-32 rounded-xl overflow-hidden bg-slate-900 relative group cursor-pointer"
                      @click="openLightbox(p)"
                    >
                      <img :src="getFileUrl(p.file_path)" alt="Process" class="w-full h-full object-cover group-hover:scale-105 transition-all" />
                      <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-1.5 text-[9px] text-white font-mono">
                        <span class="text-amber-300">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-else class="h-24 rounded-xl border border-dashed border-slate-800 flex items-center justify-center text-slate-500 text-[10px]">
                    Belum ada foto
                  </div>
                </div>

                <!-- AFTER -->
                <div class="bg-[#0B0F19]/80 border border-slate-800/80 rounded-2xl p-3 space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-500/30">
                      AFTER
                    </span>
                    <span class="text-[9px] font-mono text-slate-500">{{ getPhotosForItemStage(item.id, 'AFTER').length }} Foto</span>
                  </div>
                  <div v-if="getPhotosForItemStage(item.id, 'AFTER').length > 0" class="space-y-2">
                    <div
                      v-for="p in getPhotosForItemStage(item.id, 'AFTER')"
                      :key="p.id"
                      class="h-32 rounded-xl overflow-hidden bg-slate-900 relative group cursor-pointer"
                      @click="openLightbox(p)"
                    >
                      <img :src="getFileUrl(p.file_path)" alt="After" class="w-full h-full object-cover group-hover:scale-105 transition-all" />
                      <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-1.5 text-[9px] text-white font-mono">
                        <span class="text-emerald-300">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-else class="h-24 rounded-xl border border-dashed border-slate-800 flex items-center justify-center text-slate-500 text-[10px]">
                    Belum ada foto
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ======================================================== -->
          <!-- MODE 3: ALL PHOTOS FLAT GALLERY                          -->
          <!-- ======================================================== -->
          <div v-else-if="activeViewMode === 'ALL_PHOTOS'" class="bg-[#111827]/90 border border-amber-500/20 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4 backdrop-blur-2xl">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-3">
              <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-300">Filter Tahapan:</span>
                <div class="inline-flex rounded-xl bg-[#0B0F19] p-1 border border-slate-800 text-[11px] font-bold">
                  <button
                    v-for="st in ['ALL', 'BEFORE', 'PROCESS', 'AFTER']"
                    :key="st"
                    type="button"
                    @click="activeStage = st"
                    :class="[
                      'px-2.5 py-1 rounded-lg transition-all cursor-pointer',
                      activeStage === st ? 'bg-[#EDC80A] text-[#1E1E1D] font-black shadow-xs' : 'text-slate-400 hover:text-white'
                    ]"
                  >
                    {{ st }}
                  </button>
                </div>
              </div>
              <span class="text-xs font-mono text-slate-400">{{ filteredPhotos.length }} Foto Ditampilkan</span>
            </div>

            <div v-if="filteredPhotos.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
              <div
                v-for="p in filteredPhotos"
                :key="p.id"
                class="rounded-2xl overflow-hidden bg-[#0B0F19] border border-slate-800 group cursor-pointer relative shadow-sm hover:border-amber-500/60 transition-all"
                @click="openLightbox(p)"
              >
                <div class="h-36 sm:h-44 w-full bg-slate-900 relative overflow-hidden">
                  <img :src="getFileUrl(p.file_path)" :alt="p.stage" class="w-full h-full object-cover group-hover:scale-105 transition-all" />
                  <span
                    :class="[
                      'absolute top-2 left-2 px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider shadow-sm border',
                      p.stage === 'BEFORE' ? 'bg-blue-600 text-white border-blue-400' :
                      p.stage === 'PROCESS' ? 'bg-amber-500 text-slate-950 border-amber-300' :
                      'bg-emerald-600 text-white border-emerald-400'
                    ]"
                  >
                    {{ p.stage }}
                  </span>
                </div>
                <div class="p-2.5 space-y-1 text-[10px]">
                  <p class="font-bold text-slate-200 truncate">{{ p.item_name || 'Sub-Item' }}</p>
                  <p class="text-slate-400 font-mono text-[9px] truncate">🕒 {{ p.captured_at || '-' }}</p>
                </div>
              </div>
            </div>
            <div v-else class="py-12 text-center text-slate-500 text-xs">
              Tidak ada foto untuk tahapan {{ activeStage }}.
            </div>
          </div>
        </div>

        <!-- Lightbox Modal Fullscreen with Next & Previous Navigation -->
        <Teleport to="body">
          <div
            v-if="activeLightboxPhoto"
            class="fixed inset-0 z-[200] bg-black/95 backdrop-blur-xl flex items-center justify-center p-4"
            @click.self="activeLightboxPhoto = null"
          >
            <button
              type="button"
              @click="activeLightboxPhoto = null"
              class="absolute top-4 right-4 p-2.5 rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors cursor-pointer z-10"
            >
              <X class="w-6 h-6" />
            </button>

            <!-- Previous Button -->
            <button
              v-if="canNavigateLightbox('prev')"
              type="button"
              @click.stop="navigateLightbox(-1)"
              class="absolute left-4 top-1/2 -translate-y-1/2 p-3 rounded-full bg-white/10 hover:bg-white/20 text-white transition-all cursor-pointer z-10 hidden sm:flex items-center justify-center"
            >
              ❮
            </button>

            <!-- Next Button -->
            <button
              v-if="canNavigateLightbox('next')"
              type="button"
              @click.stop="navigateLightbox(1)"
              class="absolute right-4 top-1/2 -translate-y-1/2 p-3 rounded-full bg-white/10 hover:bg-white/20 text-white transition-all cursor-pointer z-10 hidden sm:flex items-center justify-center"
            >
              ❯
            </button>

            <div class="max-w-4xl max-h-[90vh] flex flex-col items-center select-none">
              <img
                :src="getFileUrl(activeLightboxPhoto.file_path)"
                :alt="`Bukti ${activeLightboxPhoto.stage}`"
                class="max-w-full max-h-[75vh] object-contain rounded-2xl shadow-2xl border border-white/10 mb-3"
              />
              <div class="text-center text-xs text-slate-300 space-y-1.5 bg-[#111827]/95 border border-amber-500/30 px-5 py-3 rounded-2xl backdrop-blur-2xl shadow-2xl">
                <div class="flex items-center justify-center gap-2">
                  <span
                    :class="[
                      'px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase',
                      activeLightboxPhoto.stage === 'BEFORE' ? 'bg-blue-600 text-white' :
                      activeLightboxPhoto.stage === 'PROCESS' ? 'bg-amber-500 text-slate-950' :
                      'bg-emerald-600 text-white'
                    ]"
                  >
                    {{ activeLightboxPhoto.stage }}
                  </span>
                  <span class="font-bold text-sm text-white">{{ activeLightboxPhoto.item_name || 'Dokumentasi Evidensi' }}</span>
                </div>
                <p v-if="activeLightboxPhoto.notes" class="text-slate-300">{{ activeLightboxPhoto.notes }}</p>
                <div class="font-mono text-[11px] text-slate-400 flex flex-wrap items-center justify-center gap-3">
                  <span>🕒 {{ activeLightboxPhoto.captured_at || '-' }}</span>
                  <span>📍 GPS: {{ isValidGps(activeLightboxPhoto.latitude) ? `${Number(activeLightboxPhoto.latitude).toFixed(5)}, ${Number(activeLightboxPhoto.longitude).toFixed(5)}` : 'Terverifikasi Lokasi' }}</span>
                  <span v-if="activeLightboxPhoto.file_hash" class="text-[10px] text-slate-500">🔒 SHA: {{ activeLightboxPhoto.file_hash.substring(0, 16) }}...</span>
                </div>
              </div>
            </div>
          </div>
        </Teleport>

      </div>
    </main>

    <!-- Footer (Luxury Dark) -->
    <footer class="border-t border-slate-800/80 py-6 text-center text-xs text-slate-500 bg-[#0B0F19] relative z-10">
      <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
        <p class="font-bold text-slate-300 flex items-center gap-1.5">
          <span>PT Sinar Kreasindo Bencoolen</span>
          <span class="text-[#EDC80A] font-mono text-[10px]">SGX</span>
        </p>
        <p class="text-[11px] text-slate-500">Enterprise Work Order & Real-Time Evidence Verification System</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { 
  RefreshCw, Loader2, ShieldAlert, Home, Building2, User, Calendar, 
  MapPin, Activity, Camera, Maximize2, Clock, CheckSquare, X,
  Layers, ListFilter, LayoutGrid, Sparkles, FileText, RotateCcw,
  FileCheck2, Download, Share2
} from 'lucide-vue-next';
import { api, getFileUrl } from '../../services/api';

const props = defineProps({
  token: {
    type: String,
    required: true
  }
});

const loading = ref(true);
const errorMessage = ref('');
const wo = ref(null);
const activeStage = ref('ALL');
const activeViewMode = ref('FOCUSED_TAB'); // 'FOCUSED_TAB' | 'ACCORDION' | 'ALL_PHOTOS'
const selectedItemId = ref('default');
const mobileSubStage = ref('AFTER'); // 'AFTER' | 'BEFORE' | 'PROCESS' | 'COMPARE' | 'ALL'
const activeLightboxPhoto = ref(null);

function isValidGps(val) {
  if (val == null || val === '' || isNaN(Number(val))) return false;
  return Math.abs(Number(val)) > 0.0001;
}

function formatDate(dStr) {
  if (!dStr) return '-';
  try {
    const d = new Date(dStr);
    if (isNaN(d.getTime())) return dStr;
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
  } catch (e) {
    return dStr;
  }
}

const displayItems = computed(() => {
  if (wo.value?.items && wo.value.items.length > 0) {
    return wo.value.items;
  }
  return [{
    id: null,
    item_name: wo.value?.title || 'Lingkup Pekerjaan Utama',
    weight_percent: 100
  }];
});

// Automatically set selected item when data arrives
watch(displayItems, (newItems) => {
  if (newItems && newItems.length > 0 && selectedItemId.value === 'default') {
    selectedItemId.value = newItems[0].id ?? 'default';
  }
}, { immediate: true });

const currentSelectedItem = computed(() => {
  const items = displayItems.value;
  if (!items || items.length === 0) return null;
  if (selectedItemId.value === 'default') return items[0];
  return items.find(i => (i.id ?? 'default') === selectedItemId.value) || items[0];
});

function getCurrentItemIndex(itemId) {
  const items = displayItems.value;
  return items.findIndex(i => i.id === itemId);
}

// Strict Photo Filtering per Sub-Task Stage
function getPhotosForItemStage(itemId, stage) {
  const photos = wo.value?.photos || [];
  const items = displayItems.value;
  const isFirstItem = items.length > 0 && items[0].id === itemId;

  return photos.filter(p => {
    const stageMatch = (p.stage === stage);
    if (!stageMatch) return false;

    if (p.item_id !== null && p.item_id !== undefined) {
      return Number(p.item_id) === Number(itemId);
    }
    return isFirstItem;
  });
}

function getTotalPhotosForItem(itemId) {
  const beforeCount = getPhotosForItemStage(itemId, 'BEFORE').length;
  const processCount = getPhotosForItemStage(itemId, 'PROCESS').length;
  const afterCount = getPhotosForItemStage(itemId, 'AFTER').length;
  return beforeCount + processCount + afterCount;
}

function isItemCompleted(item) {
  const afterPhotos = getPhotosForItemStage(item.id, 'AFTER');
  return afterPhotos.length > 0;
}

const filteredPhotos = computed(() => {
  const photos = wo.value?.photos || [];
  if (activeStage.value === 'ALL') return photos;
  return photos.filter(p => p.stage === activeStage.value);
});

const latestRevisionReason = computed(() => {
  if (wo.value?.revisions && wo.value.revisions.length > 0) {
    return wo.value.revisions[wo.value.revisions.length - 1].reason;
  }
  return null;
});

function getStatusLabel(status) {
  const map = {
    'READY': 'Siap Ditugaskan',
    'ASSIGNED': 'Ditugaskan ke Lapangan',
    'IN_PROGRESS': 'Sedang Dikerjakan (In Progress)',
    'SUBMITTED': 'Pengajuan Selesai (Menunggu Review)',
    'UNDER_REVIEW': 'Sedang Ditinjau Pengawas',
    'REVIEW': 'Dalam Proses Review',
    'REVISION': 'Dalam Penyempurnaan (Revisi)',
    'APPROVED': 'Disetujui (100% Selesai)',
    'COMPLETED': 'Pekerjaan Selesai & Berita Acara Terbit',
    'BA_OPNAME': 'Berita Acara (BA) Sah',
    'CANCELLED': 'Dibatalkan'
  };
  return map[status] || status;
}

function getStatusBadgeClass(status) {
  if (['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(status)) {
    return 'bg-emerald-950/90 text-emerald-300 border-emerald-500/40 shadow-emerald-900/30';
  }
  if (status === 'REVISION') {
    return 'bg-rose-950/90 text-rose-300 border-rose-500/40 shadow-rose-900/30 animate-pulse';
  }
  if (['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'].includes(status)) {
    return 'bg-[#EDC80A]/20 text-[#EDC80A] border-[#EDC80A]/40 shadow-amber-900/20';
  }
  return 'bg-amber-950/90 text-amber-300 border-amber-500/40 shadow-amber-900/20';
}

function getStatusDotClass(status) {
  if (['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(status)) return 'bg-emerald-400';
  if (status === 'REVISION') return 'bg-rose-400';
  if (['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'].includes(status)) return 'bg-[#EDC80A]';
  return 'bg-amber-400';
}

function openLightbox(photo) {
  activeLightboxPhoto.value = photo;
}

function canNavigateLightbox(dir) {
  if (!activeLightboxPhoto.value) return false;
  const currentList = wo.value?.photos || [];
  const idx = currentList.findIndex(p => p.id === activeLightboxPhoto.value.id);
  if (dir === 'prev') return idx > 0;
  if (dir === 'next') return idx < currentList.length - 1;
  return false;
}

function navigateLightbox(step) {
  if (!activeLightboxPhoto.value) return;
  const currentList = wo.value?.photos || [];
  const idx = currentList.findIndex(p => p.id === activeLightboxPhoto.value.id);
  const newIdx = idx + step;
  if (newIdx >= 0 && newIdx < currentList.length) {
    activeLightboxPhoto.value = currentList[newIdx];
  }
}

function handleShare() {
  if (navigator.share) {
    navigator.share({
      title: `Live Work Tracker: ${wo.value?.spk_number || 'SPK SGX'}`,
      text: `Pantau langsung progres pekerjaan cabang: ${wo.value?.location_name || ''}`,
      url: window.location.href
    }).catch(() => {});
  } else {
    navigator.clipboard.writeText(window.location.href);
    alert('Tautan Live Tracking berhasil disalin ke clipboard!');
  }
}

function alertBaReady() {
  alert('Pekerjaan telah 100% selesai dan disahkan oleh pengawas.');
}

async function fetchTrackingData() {
  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await (api.getPublicTracking ? api.getPublicTracking(props.token) : api.getPublicSpkTracking(props.token));
    if (res.success && res.data) {
      wo.value = res.data;
      if (res.data.photos && res.data.photos.length > 0) {
        const hasAfter = res.data.photos.some(p => p.stage === 'AFTER');
        if (hasAfter) {
          mobileSubStage.value = 'AFTER';
        } else {
          const hasProcess = res.data.photos.some(p => p.stage === 'PROCESS');
          mobileSubStage.value = hasProcess ? 'PROCESS' : 'BEFORE';
        }
      }
    } else {
      errorMessage.value = res.message || 'Data pelacakan SPK tidak ditemukan.';
    }
  } catch (err) {
    errorMessage.value = err.message || 'Gagal memuat data pemantauan dari server.';
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchTrackingData();
});
</script>
