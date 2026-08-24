<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-purple-500 selection:text-white flex flex-col">
    
    <!-- Top Brand Header Bar -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur-md sticky top-0 z-40 px-4 py-3 shadow-md">
      <div class="max-w-5xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="flex items-center">
            <img
              src="/sgx_logo.png"
              alt="PT Sinar Kreasindo Bencoolen Logo"
              class="h-9 sm:h-10 w-9 sm:w-10 object-contain rounded-xl shadow-xs"
            />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="font-black text-xs sm:text-sm text-white tracking-wide">LIVE WORK TRACKER</h1>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                REALTIME
              </span>
            </div>
            <p class="text-[10px] text-slate-400">PT Sinar Kreasindo Bencoolen — Sistem Pemantauan Progres Cabang</p>
          </div>
        </div>

        <button
          @click="fetchTrackingData"
          :disabled="loading"
          class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-all border border-slate-700 shadow-xs cursor-pointer active:scale-95"
          title="Segarkan Data"
        >
          <RefreshCw :class="['w-4 h-4', loading ? 'animate-spin text-purple-400' : '']" />
        </button>
      </div>
    </header>

    <!-- Main Content Stage -->
    <main class="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-6 md:p-8 space-y-6">
      
      <!-- Loading State -->
      <div v-if="loading && !wo" class="py-24 flex flex-col items-center justify-center space-y-3">
        <Loader2 class="w-10 h-10 animate-spin text-purple-500" />
        <p class="text-xs font-mono text-slate-400 tracking-wider">Memuat data pelacakan SPK...</p>
      </div>

      <!-- Error / Inactive State -->
      <div v-else-if="errorMessage" class="py-20 text-center space-y-4 max-w-md mx-auto">
        <div class="w-16 h-16 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 flex items-center justify-center mx-auto">
          <ShieldAlert class="w-8 h-8" />
        </div>
        <h2 class="text-lg font-bold text-white">Akses Pemantauan Tidak Tersedia</h2>
        <p class="text-xs text-slate-400 leading-relaxed">{{ errorMessage }}</p>
        <div class="pt-2">
          <a
            href="/"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-900 hover:bg-purple-800 text-white text-xs font-bold transition-all shadow-md"
          >
            <Home class="w-4 h-4" />
            <span>Kembali ke Beranda</span>
          </a>
        </div>
      </div>

      <!-- Loaded Work Order Tracking View -->
      <div v-else-if="wo" class="space-y-6 animate-fade-in">
        
        <!-- Work Order Hero Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-7 shadow-xl relative overflow-hidden">
          <!-- Background Glow Accent -->
          <div class="absolute -top-24 -right-24 w-64 h-64 bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>

          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800/80 pb-5">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-bold text-purple-400 bg-purple-950/80 px-2.5 py-0.5 rounded-lg border border-purple-800/50">
                  {{ wo.spk_number }}
                </span>
                <span class="text-xs font-medium text-slate-400">• {{ wo.area_name || '-' }}</span>
              </div>
              <h2 class="text-xl sm:text-2xl font-black text-white leading-tight">{{ wo.location_name }}</h2>
              <p class="text-xs text-slate-300">{{ wo.title }}</p>
            </div>

            <!-- Dynamic Status Badge -->
            <div class="shrink-0 flex items-center gap-2">
              <div
                :class="[
                  'px-3.5 py-1.5 rounded-xl border text-xs font-black uppercase tracking-wider flex items-center gap-2 shadow-xs',
                  getStatusBadgeClass(wo.status)
                ]"
              >
                <span class="w-2 h-2 rounded-full animate-ping" :class="getStatusDotClass(wo.status)"></span>
                <span>{{ getStatusLabel(wo.status) }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Metadata Grid -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-5 text-xs">
            <div>
              <span class="text-slate-500 block text-[11px] mb-0.5">Perusahaan Klien:</span>
              <strong class="text-slate-200 font-semibold flex items-center gap-1.5">
                <Building2 class="w-3.5 h-3.5 text-purple-400 shrink-0" />
                <span class="truncate">{{ wo.vendor?.name || 'Client SGX' }}</span>
              </strong>
            </div>
            <div>
              <span class="text-slate-500 block text-[11px] mb-0.5">Teknisi / Tim Lapangan:</span>
              <strong class="text-slate-200 font-semibold flex items-center gap-1.5">
                <User class="w-3.5 h-3.5 text-emerald-400 shrink-0" />
                <span class="truncate">{{ wo.pic?.name || 'Tim SGX' }}</span>
              </strong>
            </div>
            <div>
              <span class="text-slate-500 block text-[11px] mb-0.5">Target Penyelesaian (SLA):</span>
              <strong class="text-slate-200 font-semibold flex items-center gap-1.5 font-mono">
                <Calendar class="w-3.5 h-3.5 text-amber-400 shrink-0" />
                <span>{{ wo.deadline || '-' }}</span>
              </strong>
            </div>
            <div>
              <span class="text-slate-500 block text-[11px] mb-0.5">Presensi GPS Cabang:</span>
              <strong class="font-semibold flex items-center gap-1.5" :class="wo.check_in ? 'text-emerald-400' : 'text-amber-400'">
                <MapPin class="w-3.5 h-3.5 shrink-0" />
                <span>{{ wo.check_in ? 'Terverifikasi di Lokasi' : 'Menunggu Check-In' }}</span>
              </strong>
            </div>
          </div>

          <!-- Progress Bar if in progress -->
          <div class="mt-6 pt-4 border-t border-slate-800/80">
            <div class="flex items-center justify-between text-xs font-bold mb-1.5">
              <span class="text-slate-400">Total Progres Fisik:</span>
              <span class="font-mono text-purple-400">{{ wo.progress_percent }}%</span>
            </div>
            <div class="w-full h-2.5 bg-slate-800 rounded-full overflow-hidden p-0.5">
              <div
                class="h-full rounded-full bg-gradient-to-r from-purple-600 via-indigo-500 to-emerald-500 transition-all duration-700"
                :style="{ width: `${Math.min(100, Math.max(5, wo.progress_percent))}%` }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Real-Time Milestone Stepper -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
          <h3 class="font-black text-sm uppercase text-slate-300 tracking-wider flex items-center gap-2">
            <Activity class="w-4 h-4 text-purple-400" />
            <span>Tahapan Pengerjaan Real-Time</span>
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-2">
            <!-- Step 1: Penugasan -->
            <div class="p-3.5 rounded-2xl border bg-slate-950/60 border-slate-800 space-y-1">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-xs font-bold">
                  ✓
                </div>
                <span class="font-bold text-xs text-white">1. Penunjukan</span>
              </div>
              <p class="text-[11px] text-slate-400 pl-8">SPK resmi diterbitkan untuk tim teknisi.</p>
            </div>

            <!-- Step 2: Check-In -->
            <div
              :class="[
                'p-3.5 rounded-2xl border space-y-1',
                wo.check_in 
                  ? 'bg-slate-950/60 border-emerald-500/40 text-emerald-300' 
                  : 'bg-slate-950/30 border-slate-800/80 text-slate-500'
              ]"
            >
              <div class="flex items-center gap-2">
                <div
                  :class="[
                    'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold border',
                    wo.check_in ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-slate-800 text-slate-500 border-slate-700'
                  ]"
                >
                  {{ wo.check_in ? '✓' : '2' }}
                </div>
                <span class="font-bold text-xs" :class="wo.check_in ? 'text-white' : 'text-slate-400'">2. Check-In GPS</span>
              </div>
              <p class="text-[11px] text-slate-400 pl-8">
                {{ wo.check_in ? `Hadir di radius toko (${wo.check_in.check_in_time})` : 'Teknisi menuju lokasi toko.' }}
              </p>
            </div>

            <!-- Step 3: Evidensi Foto -->
            <div
              :class="[
                'p-3.5 rounded-2xl border space-y-1',
                wo.photos?.length > 0 
                  ? 'bg-slate-950/60 border-purple-500/40' 
                  : 'bg-slate-950/30 border-slate-800/80'
              ]"
            >
              <div class="flex items-center gap-2">
                <div
                  :class="[
                    'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold border',
                    wo.photos?.length > 0 ? 'bg-purple-500/20 text-purple-400 border-purple-500/30' : 'bg-slate-800 text-slate-500 border-slate-700'
                  ]"
                >
                  {{ wo.photos?.length > 0 ? '✓' : '3' }}
                </div>
                <span class="font-bold text-xs" :class="wo.photos?.length > 0 ? 'text-white' : 'text-slate-400'">3. Dokumentasi</span>
              </div>
              <p class="text-[11px] text-slate-400 pl-8">
                {{ wo.photos?.length > 0 ? `${wo.photos.length} foto bukti terverifikasi.` : 'Menunggu unggahan foto.' }}
              </p>
            </div>

            <!-- Step 4: Selesai / BA -->
            <div
              :class="[
                'p-3.5 rounded-2xl border space-y-1',
                ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status) 
                  ? 'bg-slate-950/60 border-emerald-500/40' 
                  : 'bg-slate-950/30 border-slate-800/80'
              ]"
            >
              <div class="flex items-center gap-2">
                <div
                  :class="[
                    'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold border',
                    ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status) ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-slate-800 text-slate-500 border-slate-700'
                  ]"
                >
                  {{ ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status) ? '✓' : '4' }}
                </div>
                <span class="font-bold text-xs" :class="['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status) ? 'text-white' : 'text-slate-400'">4. Pengesahan</span>
              </div>
              <p class="text-[11px] text-slate-400 pl-8">
                {{ wo.ba_document ? `BA No. ${wo.ba_document.ba_number}` : 'Pekerjaan selesai & BA terbit.' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Section: Structured Sub-Tasks & Photo Evidence -->
        <div class="space-y-5">
          <!-- Section Header & Flexible View Controls -->
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-900/80 border border-slate-800 p-4 sm:p-5 rounded-3xl shadow-xl">
            <div>
              <h3 class="font-black text-sm uppercase text-slate-200 tracking-wider flex items-center gap-2">
                <Camera class="w-4 h-4 text-purple-400" />
                <span>Lingkup Sub-Pekerjaan & Evidensi Fisik ({{ displayItems.length }} Item)</span>
              </h3>
              <p class="text-[11px] text-slate-400 mt-0.5">Dokumentasi hasil pengerjaan fisik terverifikasi GPS per sub-lingkup cabang.</p>
            </div>

            <!-- View Mode Switcher Toolbar -->
            <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-2xl border border-slate-800 text-xs font-bold self-start md:self-auto shadow-inner">
              <button
                type="button"
                @click="activeViewMode = 'FOCUSED_TAB'"
                :class="[
                  'px-3 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5 text-[11px]',
                  activeViewMode === 'FOCUSED_TAB' ? 'bg-gradient-to-r from-purple-900 to-indigo-800 text-white shadow-md' : 'text-slate-400 hover:text-white'
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
                  activeViewMode === 'ACCORDION' ? 'bg-gradient-to-r from-purple-900 to-indigo-800 text-white shadow-md' : 'text-slate-400 hover:text-white'
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
                  activeViewMode === 'ALL_PHOTOS' ? 'bg-gradient-to-r from-purple-900 to-indigo-800 text-white shadow-md' : 'text-slate-400 hover:text-white'
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
                    ? 'bg-purple-950/80 border-purple-500/60 text-white ring-1 ring-purple-500/30'
                    : 'bg-slate-900/80 border-slate-800/80 text-slate-400 hover:border-slate-700 hover:text-slate-200'
                ]"
              >
                <span
                  :class="[
                    'w-6 h-6 rounded-lg text-xs font-black flex items-center justify-center shrink-0',
                    selectedItemId === (item.id ?? 'default') ? 'bg-purple-600 text-white' : 'bg-slate-800 text-slate-400'
                  ]"
                >
                  {{ itmIdx + 1 }}
                </span>
                <div>
                  <div class="font-bold text-xs flex items-center gap-1.5">
                    <span class="truncate max-w-[150px] sm:max-w-[200px]">{{ item.item_name }}</span>
                    <span v-if="item.is_addendum" class="px-1.5 py-0.2 rounded text-[8px] font-black uppercase bg-amber-500 text-slate-950">
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
              class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-7 shadow-2xl space-y-6"
            >
              <!-- Item Details Header -->
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                  <span class="w-9 h-9 rounded-2xl bg-gradient-to-br from-purple-900 to-indigo-800 text-white font-black text-sm flex items-center justify-center shadow-md">
                    #{{ getCurrentItemIndex(currentSelectedItem.id) + 1 }}
                  </span>
                  <div>
                    <div class="flex items-center gap-2">
                      <h4 class="font-black text-slate-100 text-base sm:text-lg">
                        {{ currentSelectedItem.item_name }}
                      </h4>
                      <span
                        v-if="currentSelectedItem.is_addendum"
                        class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-amber-500 text-slate-950 shadow-sm"
                      >
                        + ADDENDUM
                      </span>
                    </div>
                    <p v-if="currentSelectedItem.notes" class="text-xs text-slate-400 mt-0.5">{{ currentSelectedItem.notes }}</p>
                    <span class="text-[10px] font-mono text-slate-500">Bobot Pekerjaan: {{ currentSelectedItem.weight_percent || 100 }}%</span>
                  </div>
                </div>

                <!-- Stage Tab Filters inside Sub-Item for Mobile -->
                <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-800 text-xs font-bold">
                  <button
                    type="button"
                    @click="mobileSubStage = 'ALL'"
                    :class="[
                      'px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[11px]',
                      mobileSubStage === 'ALL' ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-400 hover:text-white'
                    ]"
                  >
                    Semua ({{ getTotalPhotosForItem(currentSelectedItem.id) }})
                  </button>
                  <button
                    type="button"
                    @click="mobileSubStage = 'COMPARE'"
                    :class="[
                      'px-2.5 py-1 rounded-lg transition-all cursor-pointer text-[11px]',
                      mobileSubStage === 'COMPARE' ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-400 hover:text-white'
                    ]"
                  >
                    Before vs After
                  </button>
                </div>
              </div>

              <!-- Side-by-Side Comparison Mode -->
              <div v-if="mobileSubStage === 'COMPARE'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Before Side -->
                <div class="bg-slate-950/70 border border-slate-800 rounded-2xl p-4 space-y-3">
                  <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase bg-blue-950 text-blue-300 border border-blue-500/40">
                      BEFORE (KONDISI AWAL)
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">{{ getPhotosForItemStage(currentSelectedItem.id, 'BEFORE').length }} Foto</span>
                  </div>
                  <div v-if="getPhotosForItemStage(currentSelectedItem.id, 'BEFORE').length > 0" class="grid grid-cols-1 gap-2">
                    <div
                      v-for="p in getPhotosForItemStage(currentSelectedItem.id, 'BEFORE')"
                      :key="p.id"
                      class="h-44 rounded-xl overflow-hidden bg-slate-900 relative group cursor-pointer border border-slate-800"
                      @click="openLightbox(p)"
                    >
                      <img :src="getFileUrl(p.file_path)" alt="Before" class="w-full h-full object-cover group-hover:scale-105 transition-all" />
                      <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-2 text-[10px] text-white font-mono flex items-center justify-between">
                        <span class="text-blue-300 font-bold">BEFORE</span>
                        <span class="text-slate-400">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-else class="h-36 rounded-xl border border-dashed border-slate-800 flex items-center justify-center text-slate-500 text-xs">
                    Belum ada foto Before
                  </div>
                </div>

                <!-- After Side -->
                <div class="bg-slate-950/70 border border-slate-800 rounded-2xl p-4 space-y-3">
                  <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase bg-emerald-950 text-emerald-300 border border-emerald-500/40">
                      AFTER (HASIL SELESAI)
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">{{ getPhotosForItemStage(currentSelectedItem.id, 'AFTER').length }} Foto</span>
                  </div>
                  <div v-if="getPhotosForItemStage(currentSelectedItem.id, 'AFTER').length > 0" class="grid grid-cols-1 gap-2">
                    <div
                      v-for="p in getPhotosForItemStage(currentSelectedItem.id, 'AFTER')"
                      :key="p.id"
                      class="h-44 rounded-xl overflow-hidden bg-slate-900 relative group cursor-pointer border border-slate-800"
                      @click="openLightbox(p)"
                    >
                      <img :src="getFileUrl(p.file_path)" alt="After" class="w-full h-full object-cover group-hover:scale-105 transition-all" />
                      <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-2 text-[10px] text-white font-mono flex items-center justify-between">
                        <span class="text-emerald-300 font-bold">AFTER</span>
                        <span class="text-slate-400">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-else class="h-36 rounded-xl border border-dashed border-slate-800 flex items-center justify-center text-slate-500 text-xs">
                    Belum ada foto After
                  </div>
                </div>
              </div>

              <!-- Standard 3-Column View for Active Sub-Item -->
              <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- BEFORE -->
                <div class="bg-slate-950/70 border border-slate-800 rounded-2xl p-3.5 space-y-2.5">
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
                <div class="bg-slate-950/70 border border-slate-800 rounded-2xl p-3.5 space-y-2.5">
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
                <div class="bg-slate-950/70 border border-slate-800 rounded-2xl p-3.5 space-y-2.5">
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
              class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4"
            >
              <!-- Sub-Task Header -->
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                  <span class="w-7 h-7 rounded-xl bg-purple-900 text-white font-bold text-xs flex items-center justify-center shadow-md">
                    {{ itmIdx + 1 }}
                  </span>
                  <div>
                    <div class="flex items-center gap-2">
                      <h4 class="font-black text-slate-100 text-sm sm:text-base">
                        {{ item.item_name }}
                      </h4>
                      <span
                        v-if="item.is_addendum"
                        class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-amber-500 text-slate-950 shadow-md"
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
                <div class="bg-slate-950/60 border border-slate-800/80 rounded-2xl p-3 space-y-2">
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
                <div class="bg-slate-950/60 border border-slate-800/80 rounded-2xl p-3 space-y-2">
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
                <div class="bg-slate-950/60 border border-slate-800/80 rounded-2xl p-3 space-y-2">
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
          <div v-else-if="activeViewMode === 'ALL_PHOTOS'" class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-3">
              <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-300">Filter Tahapan:</span>
                <div class="inline-flex rounded-xl bg-slate-950 p-1 border border-slate-800 text-[11px] font-bold">
                  <button
                    v-for="st in ['ALL', 'BEFORE', 'PROCESS', 'AFTER']"
                    :key="st"
                    type="button"
                    @click="activeStage = st"
                    :class="[
                      'px-2.5 py-1 rounded-lg transition-all cursor-pointer',
                      activeStage === st ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-400 hover:text-white'
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
                class="rounded-2xl overflow-hidden bg-slate-950 border border-slate-800 group cursor-pointer relative shadow-sm"
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
            class="fixed inset-0 z-[200] bg-black/95 backdrop-blur-md flex items-center justify-center p-4"
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
              <div class="text-center text-xs text-slate-300 space-y-1.5 bg-slate-900/90 border border-slate-800 px-5 py-3 rounded-2xl backdrop-blur-md">
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

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-6 text-center text-xs text-slate-500 bg-slate-950">
      <p class="font-semibold text-slate-400">PT Sinar Graha Kreatif</p>
      <p class="text-[11px] mt-0.5">Enterprise Work Order & Real-Time Evidence Verification System</p>
    </footer>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { 
  RefreshCw, Loader2, ShieldAlert, Home, Building2, User, Calendar, 
  MapPin, Activity, Camera, Maximize2, Clock, CheckSquare, X,
  Layers, ListFilter, LayoutGrid
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
const mobileSubStage = ref('ALL'); // 'ALL' | 'COMPARE'
const activeLightboxPhoto = ref(null);

function isValidGps(val) {
  if (val == null || val === '' || isNaN(Number(val))) return false;
  return Math.abs(Number(val)) > 0.0001;
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
    if (p.stage !== stage) return false;
    
    // Strict match if photo is tied to an item_id
    if (p.item_id != null) {
      return Number(p.item_id) === Number(itemId);
    }
    
    // Legacy unassigned photo: attach ONLY to the first item (or if only 1 item exists)
    return isFirstItem || !itemId;
  });
}

function getTotalPhotosForItem(itemId) {
  const before = getPhotosForItemStage(itemId, 'BEFORE').length;
  const process = getPhotosForItemStage(itemId, 'PROCESS').length;
  const after = getPhotosForItemStage(itemId, 'AFTER').length;
  return before + process + after;
}

function isItemCompleted(item) {
  const afterPhotos = getPhotosForItemStage(item.id, 'AFTER');
  return afterPhotos.length > 0;
}

const filteredPhotos = computed(() => {
  if (!wo.value?.photos) return [];
  if (activeStage.value === 'ALL') return wo.value.photos;
  return wo.value.photos.filter(p => p.stage === activeStage.value);
});

// Lightbox Navigation Functions
function openLightbox(photo) {
  activeLightboxPhoto.value = photo;
}

function canNavigateLightbox(direction) {
  const photos = wo.value?.photos || [];
  if (!activeLightboxPhoto.value || photos.length <= 1) return false;
  const idx = photos.findIndex(p => p.id === activeLightboxPhoto.value.id);
  if (idx === -1) return false;
  if (direction === 'prev') return idx > 0;
  if (direction === 'next') return idx < photos.length - 1;
  return true;
}

function navigateLightbox(step) {
  const photos = wo.value?.photos || [];
  if (!activeLightboxPhoto.value || photos.length === 0) return;
  const idx = photos.findIndex(p => p.id === activeLightboxPhoto.value.id);
  if (idx === -1) return;
  const nextIdx = idx + step;
  if (nextIdx >= 0 && nextIdx < photos.length) {
    activeLightboxPhoto.value = photos[nextIdx];
  }
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'APPROVED':
    case 'BA_OPNAME':
    case 'COMPLETED':
      return 'bg-emerald-950 text-emerald-300 border-emerald-500/40';
    case 'SUBMITTED':
    case 'UNDER_REVIEW':
    case 'REVIEW':
      return 'bg-blue-950 text-blue-300 border-blue-500/40';
    case 'IN_PROGRESS':
    case 'CHECKED_IN':
      return 'bg-purple-950 text-purple-300 border-purple-500/40';
    case 'REVISION':
      return 'bg-rose-950 text-rose-300 border-rose-500/40';
    default:
      return 'bg-slate-900 text-slate-300 border-slate-700';
  }
}

function getStatusDotClass(status) {
  switch (status) {
    case 'APPROVED':
    case 'BA_OPNAME':
    case 'COMPLETED':
      return 'bg-emerald-400';
    case 'SUBMITTED':
    case 'UNDER_REVIEW':
    case 'REVIEW':
      return 'bg-blue-400';
    case 'IN_PROGRESS':
    case 'CHECKED_IN':
      return 'bg-purple-400';
    case 'REVISION':
      return 'bg-rose-400';
    default:
      return 'bg-slate-400';
  }
}

function getStatusLabel(status) {
  switch (status) {
    case 'APPROVED':
    case 'BA_OPNAME':
    case 'COMPLETED':
      return 'Disetujui 100%';
    case 'SUBMITTED':
    case 'UNDER_REVIEW':
    case 'REVIEW':
      return 'Sedang Direview';
    case 'IN_PROGRESS':
      return 'Dalam Pengerjaan';
    case 'CHECKED_IN':
      return 'Teknisi di Lokasi';
    case 'REVISION':
      return 'Perbaikan Revisi';
    case 'ASSIGNED':
      return 'Telah Ditugaskan';
    default:
      return status || 'Draft';
  }
}

async function fetchTrackingData() {
  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await api.getPublicTracking(props.token);
    if (res.success && res.data) {
      wo.value = res.data;
    } else {
      errorMessage.value = res.message || 'Data pemantauan tidak ditemukan.';
    }
  } catch (err) {
    errorMessage.value = err.message || 'Gagal memuat data pemantauan SPK. Pastikan tautan benar.';
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchTrackingData();
});
</script>
