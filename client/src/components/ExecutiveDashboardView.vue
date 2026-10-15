<template>
  <div class="space-y-6 text-slate-100 font-sans">
    <!-- Skeleton Loading State (Micro-interaction) -->
    <div v-if="loadingSkeleton" class="space-y-6 animate-pulse">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="i in 4" :key="i" class="h-36 rounded-xs bg-[#13171F] border border-[#232A36] p-5 space-y-3">
          <div class="h-3 w-24 bg-[#1C2028] rounded-xs"></div>
          <div class="h-8 w-32 bg-[#1C2028] rounded-xs"></div>
          <div class="h-2 w-full bg-[#1C2028] rounded-xs"></div>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-5 h-80 rounded-xs bg-[#13171F] border border-[#232A36]"></div>
        <div class="lg:col-span-7 h-80 rounded-xs bg-[#13171F] border border-[#232A36]"></div>
      </div>
    </div>

    <template v-else>
      <!-- ==================================================== -->
      <!-- B. INDUSTRIAL CAD BENTO KPI GRID (4 METRIC CARDS)    -->
      <!-- ==================================================== -->
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 lg:gap-6">
        <!-- Card 1: On-Time Delivery (SLA) -->
        <div class="cad-crosshair-card p-5 sm:p-6 group hover:border-[#FACC15]/80 transition-colors">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">// ON-TIME DELIVERY (SLA)</span>
            <div class="w-7 h-7 rounded-xs bg-[#1C2028] text-[#FACC15] flex items-center justify-center border border-[#232A36]">
              <Clock class="w-3.5 h-3.5" />
            </div>
          </div>
          <div class="flex items-baseline justify-between gap-2 flex-wrap">
            <div class="text-3xl font-black font-mono tracking-tight text-[#FACC15]">94.2%</div>
            <span class="inline-flex items-center gap-1 text-[9px] font-mono font-bold text-[#10B981] bg-[#13171F] px-2 py-0.5 rounded-xs border border-[#10B981]/50 shrink-0 uppercase">
              <span class="w-1.5 h-1.5 rounded-full bg-[#10B981] animate-pulse"></span>
              TARGET &ge; 90% PASS
            </span>
          </div>
          <div class="flex items-center gap-1.5 text-[10px] font-mono text-[#10B981] mt-1.5">
            <TrendingUp class="w-3 h-3" />
            <span>+2.4% VS BULAN LALU</span>
          </div>
          <div class="w-full bg-[#0B0C0E] h-1.5 mt-3.5 overflow-hidden border border-[#232A36]">
            <div class="bg-[#FACC15] h-full w-[94.2%]" />
          </div>
        </div>

        <!-- Card 2: First-Pass Approval Rate -->
        <div class="cad-crosshair-card p-5 sm:p-6 group hover:border-[#FACC15]/80 transition-colors">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">// FIRST-PASS APPROVAL</span>
            <div class="w-7 h-7 rounded-xs bg-[#1C2028] text-[#FACC15] flex items-center justify-center border border-[#232A36]">
              <CheckCircle2 class="w-3.5 h-3.5" />
            </div>
          </div>
          <div class="flex items-baseline justify-between gap-2 flex-wrap">
            <div class="text-3xl font-black font-mono tracking-tight text-[#FACC15]">88.5%</div>
            <span class="text-[9px] font-mono font-bold text-[#0B0C0E] bg-[#FACC15] px-2 py-0.5 rounded-xs uppercase tracking-wider shrink-0">
              HIGH QUALITY
            </span>
          </div>
          <!-- Weekly Trend Visual Sparkline -->
          <div class="mt-2.5 flex items-center justify-between">
            <div class="flex items-center gap-1 text-[10px] font-mono text-slate-400 uppercase">
              <span>TREN MINGGUAN (6 WK):</span>
            </div>
            <!-- SVG Sparkline in Industrial Hazard Yellow -->
            <svg class="w-24 h-5 overflow-visible" viewBox="0 0 100 24">
              <polyline
                fill="none"
                stroke="#FACC15"
                stroke-width="2"
                stroke-linecap="square"
                stroke-linejoin="miter"
                points="0,18 20,14 40,16 60,9 80,11 100,4"
              />
              <circle cx="100" cy="4" r="2.5" fill="#FACC15" class="animate-ping" />
              <circle cx="100" cy="4" r="2" fill="#FFFFFF" />
            </svg>
          </div>
          <div class="w-full bg-[#0B0C0E] h-1.5 mt-3 overflow-hidden border border-[#232A36]">
            <div class="bg-[#FACC15] h-full w-[88.5%]" />
          </div>
        </div>

        <!-- Card 3: Total Nilai Proyek SPK -->
        <div class="cad-crosshair-card p-5 sm:p-6 group hover:border-[#FACC15]/80 transition-colors relative">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">// TOTAL NILAI PROYEK SPK</span>
            <div class="w-7 h-7 rounded-xs bg-[#1C2028] text-[#FACC15] flex items-center justify-center border border-[#232A36]">
              <Coins class="w-3.5 h-3.5" />
            </div>
          </div>
          <div class="flex items-baseline justify-between gap-2 flex-wrap">
            <div class="text-2xl lg:text-3xl font-black font-mono tracking-tight text-[#FACC15] whitespace-nowrap">Rp 960 Jt</div>
            <!-- Interactive Tooltip Trigger for Breakdown -->
            <div class="relative group/tooltip inline-block shrink-0">
              <button
                type="button"
                @mouseenter="showValuationTooltip = true"
                @mouseleave="showValuationTooltip = false"
                class="text-[9px] font-mono font-bold text-[#0B0C0E] bg-[#FACC15] px-2 py-0.5 rounded-xs hover:bg-[#FFE033] cursor-pointer flex items-center gap-1 uppercase"
              >
                <span>73 ACTIVE SPK</span>
                <Info class="w-3 h-3 text-[#0B0C0E]" />
              </button>
              <!-- Tooltip Box -->
              <div
                v-if="showValuationTooltip"
                class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-64 p-3 bg-[#13171F] border border-[#FACC15] rounded-xs shadow-2xl text-[10px] text-slate-200 z-50 pointer-events-none font-mono"
              >
                <div class="font-bold text-[#FACC15] mb-1.5 border-b border-[#232A36] pb-1 uppercase tracking-wider">Distribusi Nilai Client:</div>
                <div class="space-y-1 font-mono text-[9px]">
                  <div class="flex justify-between"><span>• PT Indomarco (IDM):</span> <strong class="text-white">Rp 450 Jt (47%)</strong></div>
                  <div class="flex justify-between"><span>• PT Smartfren Tbk:</span> <strong class="text-white">Rp 290 Jt (30%)</strong></div>
                  <div class="flex justify-between"><span>• Regional & Lainnya:</span> <strong class="text-white">Rp 220 Jt (23%)</strong></div>
                </div>
              </div>
            </div>
          </div>
          <p class="text-[10px] font-mono text-slate-400 mt-1">Akumulasi estimasi nilai seluruh kontrak pekerjaan vendor.</p>
          <div class="w-full bg-[#0B0C0E] h-1.5 mt-3 overflow-hidden border border-[#232A36]">
            <div class="bg-[#FACC15] h-full w-[85%]" />
          </div>
        </div>

        <!-- Card 4: Integritas Bukti Foto -->
        <div class="cad-crosshair-card p-5 sm:p-6 group hover:border-[#10B981]/80 transition-colors relative">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">// INTEGRITAS BUKTI FOTO</span>
            <div class="w-7 h-7 rounded-xs bg-[#1C2028] text-[#10B981] flex items-center justify-center border border-[#232A36]">
              <Lock class="w-3.5 h-3.5" />
            </div>
          </div>
          <div class="flex items-baseline justify-between gap-2 flex-wrap">
            <div class="text-3xl font-black font-mono tracking-tight text-[#10B981]">100%</div>
            <!-- Interactive Tooltip explaining SHA-256 cryptographic proof -->
            <div class="relative group/hash inline-block shrink-0">
              <button
                type="button"
                @mouseenter="showHashTooltip = true"
                @mouseleave="showHashTooltip = false"
                class="text-[9px] font-mono font-bold text-[#10B981] bg-[#13171F] px-2 py-0.5 rounded-xs border border-[#10B981]/50 hover:bg-[#1C2028] cursor-pointer flex items-center gap-1 uppercase"
              >
                <ShieldCheck class="w-3 h-3 text-[#10B981]" />
                <span>SHA-256 VALIDATED</span>
              </button>
              <!-- Hover Tooltip Box -->
              <div
                v-if="showHashTooltip"
                class="absolute right-0 bottom-full mb-2 w-72 p-3.5 bg-[#13171F] border border-[#10B981] rounded-xs shadow-2xl text-[10px] text-slate-200 z-50 pointer-events-none font-mono"
              >
                <div class="flex items-center gap-1.5 font-bold text-[#10B981] mb-1 border-b border-[#232A36] pb-1 uppercase tracking-wider">
                  <Lock class="w-3.5 h-3.5" />
                  <span>KRIPTOGRAFI BUKTI DIGITAL (SHA-256)</span>
                </div>
                <p class="text-[9px] text-slate-300 leading-relaxed font-sans">
                  Setiap foto bukti lapangan dikunci dengan hash 256-bit dan timestamp server real-time saat diunggah. Menjamin perlindungan anti-manipulasi, anti-EXIF spoofing, dan sah untuk Berita Acara resmi.
                </p>
                <div class="mt-2 text-[9px] font-mono text-[#10B981] flex items-center gap-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-[#10B981] animate-pulse"></span>
                  GPS GEOFENCING: RADIUS &le; 200M VERIFIED
                </div>
              </div>
            </div>
          </div>
          <p class="text-[10px] font-mono text-slate-400 mt-1">Seluruh foto terkunci enkripsi anti-manipulasi & GPS resmi.</p>
          <div class="w-full bg-[#0B0C0E] h-1.5 mt-3 overflow-hidden border border-[#232A36]">
            <div class="bg-[#10B981] h-full w-full" />
          </div>
        </div>
      </div>

      <!-- ==================================================== -->
      <!-- C. DEEP ANALYTICS SECTION (2-COLUMN INDUSTRIAL CAD) -->
      <!-- ==================================================== -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Panel (5 Cols): Komposisi Valuasi & Kategori Pekerjaan -->
        <div class="lg:col-span-5 cad-surface p-5 flex flex-col items-center justify-between space-y-5">
          <div class="w-full flex items-center justify-between border-b border-[#232A36] pb-2.5">
            <div>
              <h3 class="font-bold text-xs text-white uppercase font-mono tracking-wider flex items-center gap-2">
                <Briefcase class="w-4 h-4 text-[#FACC15]" />
                <span>KOMPOSISI VALUASI & KATEGORI</span>
              </h3>
              <p class="text-[10px] text-slate-400 font-mono mt-0.5">Rasio nilai portofolio pekerjaan per sektor.</p>
            </div>
            <span class="text-[9px] font-mono font-bold text-[#FACC15] bg-[#1C2028] px-2 py-0.5 rounded-xs border border-[#232A36] uppercase">
              5 KATEGORI
            </span>
          </div>

          <!-- Interactive Semi-Donut Vector Chart (Industrial Colors) -->
          <div class="relative flex items-center justify-center my-2">
            <svg viewBox="0 0 42 42" class="w-52 h-52 -rotate-90">
              <!-- Background Circle Track -->
              <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#1C2028" stroke-width="4.5"></circle>
              <!-- Segment 1: Signboard (37.5%) - Hazard Yellow -->
              <circle
                cx="21" cy="21" r="15.91549430918954" fill="transparent"
                stroke="#FACC15" stroke-width="4.8"
                stroke-dasharray="37.5 62.5" stroke-dashoffset="0"
                class="hover:stroke-width-6 transition-all duration-200 cursor-pointer"
                @mouseenter="hoveredSlice = 'Signboard & Reklame (37.5%)'"
                @mouseleave="hoveredSlice = null"
              />
              <!-- Segment 2: Branding Fasade (28.1%) - Clean White -->
              <circle
                cx="21" cy="21" r="15.91549430918954" fill="transparent"
                stroke="#E2E8F0" stroke-width="4.8"
                stroke-dasharray="28.1 71.9" stroke-dashoffset="-37.5"
                class="hover:stroke-width-6 transition-all duration-200 cursor-pointer"
                @mouseenter="hoveredSlice = 'Branding Fasade (28.1%)'"
                @mouseleave="hoveredSlice = null"
              />
              <!-- Segment 3: Huruf Timbul & Display (18.8%) - Technical Line -->
              <circle
                cx="21" cy="21" r="15.91549430918954" fill="transparent"
                stroke="#475569" stroke-width="4.8"
                stroke-dasharray="18.8 81.2" stroke-dashoffset="-65.6"
                class="hover:stroke-width-6 transition-all duration-200 cursor-pointer"
                @mouseenter="hoveredSlice = 'Huruf Timbul & Acrylic (18.8%)'"
                @mouseleave="hoveredSlice = null"
              />
              <!-- Segment 4: Stiker Booth ATM (9.9%) - Forensic Green -->
              <circle
                cx="21" cy="21" r="15.91549430918954" fill="transparent"
                stroke="#10B981" stroke-width="4.8"
                stroke-dasharray="9.9 90.1" stroke-dashoffset="-84.4"
                class="hover:stroke-width-6 transition-all duration-200 cursor-pointer"
                @mouseenter="hoveredSlice = 'Stiker Booth ATM (9.9%)'"
                @mouseleave="hoveredSlice = null"
              />
              <!-- Segment 5: Kelistrikan LED (5.7%) - Dark Ochre -->
              <circle
                cx="21" cy="21" r="15.91549430918954" fill="transparent"
                stroke="#CA8A04" stroke-width="4.8"
                stroke-dasharray="5.7 94.3" stroke-dashoffset="-94.3"
                class="hover:stroke-width-6 transition-all duration-200 cursor-pointer"
                @mouseenter="hoveredSlice = 'Kelistrikan LED (5.7%)'"
                @mouseleave="hoveredSlice = null"
              />
            </svg>

            <!-- Center Summary in Industrial Mono -->
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none font-mono">
              <span class="text-[8px] uppercase tracking-wider text-slate-400">TOTAL PORTOFOLIO</span>
              <span class="text-xl font-black text-[#FACC15] tracking-tight mt-0.5">Rp 960M</span>
              <span class="text-[9px] text-slate-300 font-bold mt-0.5">
                {{ hoveredSlice || 'Avg Rp 13.1M / SPK' }}
              </span>
            </div>
          </div>

          <!-- Color Legend Grid -->
          <div class="grid grid-cols-2 gap-x-3 gap-y-2 w-full text-[10px] pt-3 border-t border-[#232A36] font-mono">
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-none bg-[#FACC15] shrink-0 border border-[#0B0C0E]"></span>
              <span class="truncate text-slate-300">Signboard (37.5%)</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-none bg-[#E2E8F0] shrink-0 border border-[#0B0C0E]"></span>
              <span class="truncate text-slate-300">Fasade (28.1%)</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-none bg-[#475569] shrink-0 border border-[#0B0C0E]"></span>
              <span class="truncate text-slate-300">Huruf Timbul (18.8%)</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-none bg-[#10B981] shrink-0 border border-[#0B0C0E]"></span>
              <span class="truncate text-slate-300">Stiker ATM (9.9%)</span>
            </div>
            <div class="flex items-center gap-1.5 col-span-2">
              <span class="w-2.5 h-2.5 rounded-none bg-[#CA8A04] shrink-0 border border-[#0B0C0E]"></span>
              <span class="truncate text-slate-300">Kelistrikan LED (5.7%)</span>
            </div>
          </div>
        </div>

        <!-- Right Panel (7 Cols): Peringkat Kategori Pekerjaan & Metrik Mutu -->
        <div class="lg:col-span-7 cad-surface p-5 space-y-4">
          <div class="flex items-center justify-between border-b border-[#232A36] pb-2.5">
            <div>
              <h3 class="font-bold text-xs text-white uppercase font-mono tracking-wider flex items-center gap-2">
                <BarChart3 class="w-4 h-4 text-[#FACC15]" />
                <span>PERINGKAT KATEGORI PEKERJAAN & METRIK MUTU</span>
              </h3>
              <p class="text-[10px] text-slate-400 font-mono mt-0.5">Rangking volume, valuasi, SLA kualitas, dan sampel bukti foto terverifikasi.</p>
            </div>
            <div class="text-[9px] font-mono text-slate-400 hidden sm:block uppercase">
              HOVER BARIS // DETAIL BUKTI FOTO
            </div>
          </div>

          <!-- Ranked List with Yellow Indicator Bars -->
          <div class="space-y-2.5">
            <div
              v-for="(job, idx) in rankedCategories"
              :key="idx"
              @mouseenter="hoveredJobIdx = idx"
              @mouseleave="hoveredJobIdx = null"
              class="p-3 rounded-xs bg-[#0E1117] border border-[#232A36] hover:border-[#FACC15]/80 transition-colors relative group font-mono"
            >
              <!-- Row Header -->
              <div class="flex items-center justify-between gap-2 mb-1.5">
                <div class="flex items-center gap-2">
                  <span class="w-5 h-5 rounded-xs bg-[#1C2028] text-[#FACC15] font-mono font-black text-[10px] flex items-center justify-center border border-[#232A36]">
                    #{{ idx + 1 }}
                  </span>
                  <div>
                    <h4 class="font-bold text-xs text-slate-200 group-hover:text-[#FACC15] transition-colors flex items-center gap-1.5">
                      <span>{{ job.title }}</span>
                      <span class="text-[9px] font-mono text-slate-500 font-normal">({{ job.code }})</span>
                    </h4>
                  </div>
                </div>

                <div class="text-right">
                  <div class="font-black font-mono text-xs text-[#FACC15]">
                    {{ job.valuation }}
                  </div>
                  <div class="text-[9px] font-mono text-slate-400">
                    {{ job.volume }} SPK
                  </div>
                </div>
              </div>

              <!-- Progress Bar & SLA Mutu with Solid Yellow Bars -->
              <div class="space-y-1">
                <div class="flex items-center justify-between text-[9px] font-mono text-slate-400">
                  <span class="text-[#FACC15] font-bold">
                    {{ job.tag }}
                  </span>
                  <span class="text-[#10B981] font-bold">SLA MUTU {{ job.sla }}%</span>
                </div>
                <div class="w-full bg-[#0B0C0E] h-1.5 overflow-hidden border border-[#232A36]">
                  <div class="h-full bg-[#FACC15] transition-all duration-300" :style="{ width: `${job.percent}%` }" />
                </div>
              </div>

              <!-- Interactive Hover Card Preview (Section 2 Forensic Evidence Gallery Style) -->
              <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0 translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-1"
              >
                <div
                  v-if="hoveredJobIdx === idx"
                  class="mt-2.5 p-2 rounded-xs bg-[#13171F] border border-[#FACC15] flex items-center justify-between gap-3 text-[9px] font-mono animate-fade-in"
                >
                  <div class="flex items-center gap-2">
                    <span class="text-[#FACC15] font-bold flex items-center gap-1">
                      <Camera class="w-3 h-3" />
                      BUKTI FOTO RESMI:
                    </span>
                    <span class="text-slate-300">SPK #{{ job.sampleSpk }}</span>
                  </div>

                  <!-- 3 Evidence Thumbnails with High-Visibility Tags as in .cursorrules Section 2 -->
                  <div class="flex items-center gap-2">
                    <div class="relative w-14 h-10 rounded-xs overflow-hidden border border-[#FACC15] bg-[#0B0C0E]">
                      <img :src="job.thumbnails[0]" alt="Before" class="w-full h-full object-cover" />
                      <span class="absolute top-0 left-0 bg-[#FACC15] text-[#0B0C0E] text-[7px] font-mono font-black px-1">[ BEFORE ]</span>
                    </div>
                    <div class="relative w-14 h-10 rounded-xs overflow-hidden border border-[#232A36] bg-[#0B0C0E]">
                      <img :src="job.thumbnails[1]" alt="Process" class="w-full h-full object-cover" />
                      <span class="absolute top-0 left-0 bg-[#1C2028] text-[#FACC15] border border-[#FACC15]/40 text-[7px] font-mono font-black px-1">[ PROCESS ]</span>
                    </div>
                    <div class="relative w-14 h-10 rounded-xs overflow-hidden border border-[#10B981] bg-[#0B0C0E]">
                      <img :src="job.thumbnails[2]" alt="After" class="w-full h-full object-cover" />
                      <span class="absolute top-0 left-0 bg-[#10B981] text-[#0B0C0E] text-[7px] font-mono font-black px-1">[ AFTER ]</span>
                    </div>
                  </div>
                </div>
              </Transition>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================================================== -->
      <!-- CHARTS ROW 2: SPK COMPLETION & REGIONAL COVERAGE    -->
      <!-- ==================================================== -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly SPK Trend -->
        <div class="cad-surface p-5 space-y-4">
          <div class="flex items-center justify-between border-b border-[#232A36] pb-2.5">
            <div>
              <h3 class="font-bold text-xs text-white uppercase font-mono tracking-wider flex items-center gap-2">
                <Activity class="w-4 h-4 text-[#FACC15]" />
                <span>TREN PENYELESAIAN SPK BULANAN</span>
              </h3>
              <p class="text-[10px] text-slate-400 font-mono mt-0.5">Perbandingan SPK Terbit vs SPK 100% Selesai.</p>
            </div>
            <div class="flex items-center gap-3 text-[9px] font-mono">
              <span class="flex items-center gap-1.5 text-slate-300">
                <span class="w-2 h-2 rounded-none bg-[#232A36] border border-[#FACC15]"></span> TERBIT
              </span>
              <span class="flex items-center gap-1.5 text-[#FACC15]">
                <span class="w-2 h-2 rounded-none bg-[#FACC15]"></span> SELESAI
              </span>
            </div>
          </div>

          <!-- Bar Chart Comparison in Industrial Mono -->
          <div class="space-y-2.5 pt-1">
            <div v-for="(m, i) in monthlyTrends" :key="i" class="space-y-1">
              <div class="flex items-center justify-between text-[10px] font-mono text-slate-400">
                <span>{{ m.month }} 2026</span>
                <span class="text-white">{{ m.completed }} / {{ m.created }} SELESAI ({{ Math.round((m.completed / m.created) * 100) }}%)</span>
              </div>
              <div class="grid grid-cols-2 gap-2">
                <div class="w-full bg-[#0B0C0E] h-2 overflow-hidden border border-[#232A36]">
                  <div class="bg-[#475569] h-full" :style="{ width: `${(m.created / 35) * 100}%` }" />
                </div>
                <div class="w-full bg-[#0B0C0E] h-2 overflow-hidden border border-[#232A36]">
                  <div class="bg-[#FACC15] h-full" :style="{ width: `${(m.completed / 35) * 100}%` }" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Regional Distribution & SLA Compliance -->
        <div class="cad-surface p-5 space-y-4">
          <div class="flex items-center justify-between border-b border-[#232A36] pb-2.5">
            <div>
              <h3 class="font-bold text-xs text-white uppercase font-mono tracking-wider flex items-center gap-2">
                <MapPin class="w-4 h-4 text-[#FACC15]" />
                <span>CAKUPAN REGIONAL & KEPATUHAN SLA</span>
              </h3>
              <p class="text-[10px] text-slate-400 font-mono mt-0.5">Sebaran pengerjaan SPK aktif di 4 provinsi regional.</p>
            </div>
            <span class="text-[9px] font-mono font-bold text-[#10B981] bg-[#13171F] border border-[#10B981]/50 px-2 py-0.5 rounded-xs uppercase">
              4 WILAYAH AKTIF
            </span>
          </div>

          <div class="space-y-2.5 pt-1">
            <div v-for="(area, idx) in areaDistributions" :key="idx" class="p-2.5 rounded-xs bg-[#0E1117] border border-[#232A36]">
              <div class="flex items-center justify-between text-[10px] font-mono mb-1">
                <span class="font-bold text-slate-200 uppercase">{{ area.name }}</span>
                <span class="text-[#FACC15] font-bold">{{ area.count }} SPK ({{ area.percent }}%)</span>
              </div>
              <div class="w-full bg-[#0B0C0E] h-1.5 overflow-hidden border border-[#232A36]">
                <div class="h-full bg-[#FACC15]" :style="{ width: `${area.percent}%` }" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../services/api';
import {
  Clock,
  CheckCircle2,
  Lock,
  Coins,
  ShieldCheck,
  TrendingUp,
  Info,
  Briefcase,
  BarChart3,
  Camera,
  Activity,
  MapPin
} from 'lucide-vue-next';

const loadingSkeleton = ref(false);
const showValuationTooltip = ref(false);
const showHashTooltip = ref(false);
const hoveredSlice = ref(null);
const hoveredJobIdx = ref(null);

const rankedCategories = ref([
  {
    title: 'Pemasangan Signboard & Reklame',
    code: 'SIGNBOARD',
    valuation: 'Rp 360M',
    volume: 24,
    percent: 37.5,
    sla: 96,
    tag: 'BEFORE / PROCESS / AFTER VERIFIED',
    sampleSpk: '2026-00125',
    thumbnails: [
      '/ba_background.jpg',
      '/ba_letterhead_bg.jpg',
      '/TEMPLATE_TIMESLAP_SG.png'
    ]
  },
  {
    title: 'Pengecatan & Branding Fasade Gedung',
    code: 'BRANDING_FASADE',
    valuation: 'Rp 270M',
    volume: 18,
    percent: 28.1,
    sla: 94.5,
    tag: 'BEFORE / PROCESS / AFTER VERIFIED',
    sampleSpk: '2026-00118',
    thumbnails: [
      '/ba_letterhead_bg.jpg',
      '/ba_background.jpg',
      '/TEMPLATE_TIMESLAP_SG.png'
    ]
  },
  {
    title: 'Huruf Timbul & Acrylic Display',
    code: 'ACRYLIC_DISPLAY',
    valuation: 'Rp 180M',
    volume: 15,
    percent: 18.8,
    sla: 95.8,
    tag: 'ISO 9001 SPECS VERIFIED',
    sampleSpk: '2026-00104',
    thumbnails: [
      '/ba_background.jpg',
      '/TEMPLATE_TIMESLAP_SG.png',
      '/ba_letterhead_bg.jpg'
    ]
  },
  {
    title: 'Stiker & Branding Booth ATM',
    code: 'STIKER_ATM',
    valuation: 'Rp 95M',
    volume: 11,
    percent: 9.9,
    sla: 98,
    tag: 'FAST TURNAROUND VERIFIED',
    sampleSpk: '2026-00098',
    thumbnails: [
      '/TEMPLATE_TIMESLAP_SG.png',
      '/ba_background.jpg',
      '/ba_letterhead_bg.jpg'
    ]
  },
  {
    title: 'Maintenance & Kelistrikan LED',
    code: 'ELECTRICAL_LED',
    valuation: 'Rp 55M',
    volume: 5,
    percent: 5.7,
    sla: 92,
    tag: 'FIELD MONITORED',
    sampleSpk: '2026-00085',
    thumbnails: [
      '/ba_letterhead_bg.jpg',
      '/TEMPLATE_TIMESLAP_SG.png',
      '/ba_background.jpg'
    ]
  }
]);

const monthlyTrends = ref([
  { month: 'JAN', created: 14, completed: 13 },
  { month: 'FEB', created: 18, completed: 16 },
  { month: 'MAR', created: 22, completed: 20 },
  { month: 'APR', created: 25, completed: 24 },
  { month: 'MEI', created: 30, completed: 28 },
  { month: 'JUN', created: 32, completed: 30 }
]);

const areaDistributions = ref([
  { name: 'Jawa Barat & Bandung Raya', count: 32, percent: 44 },
  { name: 'DKI Jakarta & Banten', count: 22, percent: 30 },
  { name: 'Jawa Tengah (Semarang & Solo)', count: 12, percent: 16 },
  { name: 'Jawa Timur & Bali', count: 7, percent: 10 }
]);
</script>
