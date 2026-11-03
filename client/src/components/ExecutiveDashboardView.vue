<template>
  <div class="space-y-6 text-slate-100 font-sans">
    <!-- Top Filter & Dashboard Control Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-[#13171F] border border-[#232A36] rounded-xs font-mono">
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-none bg-[#FACC15] animate-pulse"></span>
        <span class="text-xs font-black uppercase tracking-wider text-white">MISSION CONTROL // EXECUTIVE TELEMETRY</span>
        <span class="text-slate-600">|</span>
        <span class="text-[10px] text-slate-400">PERIODE Q1-Q3 2026</span>
      </div>

      <!-- Quick Chart View Mode Filter -->
      <div class="flex items-center gap-1.5 flex-wrap">
        <span class="text-[10px] text-slate-500 uppercase mr-1">TAMPILAN METRIK:</span>
        <button
          v-for="mode in viewModes"
          :key="mode.id"
          @click="activeViewMode = mode.id"
          :class="[
            'px-2.5 py-1 text-[10px] font-bold rounded-xs uppercase tracking-wider transition-all cursor-pointer border',
            activeViewMode === mode.id
              ? 'bg-[#FACC15] text-[#0B0C0E] border-[#FACC15] shadow-xs'
              : 'bg-[#0E1117] text-slate-400 hover:text-white border-[#232A36]'
          ]"
        >
          {{ mode.label }}
        </button>
      </div>
    </div>

    <!-- ==================================================== -->
    <!-- B. INDUSTRIAL CAD BENTO KPI GRID (4 METRIC CARDS)    -->
    <!-- ==================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5">
      <!-- Card 1: On-Time Delivery (SLA) -->
      <div class="cad-crosshair-card p-4 sm:p-5 group hover:border-[#FACC15]/80 transition-colors">
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
      <div class="cad-crosshair-card p-4 sm:p-5 group hover:border-[#FACC15]/80 transition-colors">
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
            <span>TREN 6 MINGGU:</span>
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
      <div class="cad-crosshair-card p-4 sm:p-5 group hover:border-[#FACC15]/80 transition-colors relative">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">// TOTAL VALUASI KONTRAK</span>
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
      <div class="cad-crosshair-card p-4 sm:p-5 group hover:border-[#10B981]/80 transition-colors relative">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">// FORENSIK FOTO & GPS</span>
          <div class="w-7 h-7 rounded-xs bg-[#1C2028] text-[#10B981] flex items-center justify-center border border-[#232A36]">
            <Lock class="w-3.5 h-3.5" />
          </div>
        </div>
        <div class="flex items-baseline justify-between gap-2 flex-wrap">
          <div class="text-3xl font-black font-mono tracking-tight text-[#10B981]">100%</div>
          <div class="relative group/hash inline-block shrink-0">
            <button
              type="button"
              @mouseenter="showHashTooltip = true"
              @mouseleave="showHashTooltip = false"
              class="text-[9px] font-mono font-bold text-[#10B981] bg-[#13171F] px-2 py-0.5 rounded-xs border border-[#10B981]/50 hover:bg-[#1C2028] cursor-pointer flex items-center gap-1 uppercase"
            >
              <ShieldCheck class="w-3 h-3 text-[#10B981]" />
              <span>SHA-256 PASS</span>
            </button>
            <div
              v-if="showHashTooltip"
              class="absolute right-0 bottom-full mb-2 w-72 p-3.5 bg-[#13171F] border border-[#10B981] rounded-xs shadow-2xl text-[10px] text-slate-200 z-50 pointer-events-none font-mono"
            >
              <div class="flex items-center gap-1.5 font-bold text-[#10B981] mb-1 border-b border-[#232A36] pb-1 uppercase tracking-wider">
                <Lock class="w-3.5 h-3.5" />
                <span>KRIPTOGRAFI BUKTI DIGITAL</span>
              </div>
              <p class="text-[9px] text-slate-300 leading-relaxed font-sans">
                Setiap foto bukti lapangan dikunci dengan hash 256-bit dan timestamp server real-time saat diunggah. Menjamin perlindungan anti-manipulasi dan sah untuk Berita Acara resmi.
              </p>
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
    <!-- SECTION 1: TREN WAKTU NYATA & KOMPOSISI PORTOFOLIO   -->
    <!-- ==================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
      <!-- Left Panel (7 Cols): Diagram Kurva Area & Multi-Line (Tren Produksi & Penyelesaian SPK Bulanan) -->
      <div class="lg:col-span-7 cad-surface p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#232A36] pb-3">
          <div>
            <h3 class="font-bold text-xs text-white uppercase font-mono tracking-wider flex items-center gap-2">
              <TrendingUp class="w-4 h-4 text-[#FACC15]" />
              <span>DIAGRAM KURVA TREN PRODUKSI & PENYELESAIAN (6 BULAN)</span>
            </h3>
            <p class="text-[10px] text-slate-400 font-mono mt-0.5">Analisis output volume pengerjaan SPK terbit vs verifikasi final.</p>
          </div>
          <div class="flex items-center gap-3 text-[9px] font-mono">
            <span class="flex items-center gap-1.5 text-slate-300">
              <span class="w-3 h-1 bg-[#475569]"></span> SPK TERBIT
            </span>
            <span class="flex items-center gap-1.5 text-[#FACC15]">
              <span class="w-3 h-1 bg-[#FACC15]"></span> SPK SELESAI
            </span>
          </div>
        </div>

        <!-- High-Precision SVG Area & Multi-Line Chart -->
        <div class="relative w-full h-56 pt-2 select-none">
          <svg class="w-full h-full overflow-visible" viewBox="0 0 600 200" preserveAspectRatio="none">
            <defs>
              <linearGradient id="yellowGradient" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#FACC15" stop-opacity="0.35" />
                <stop offset="100%" stop-color="#FACC15" stop-opacity="0.0" />
              </linearGradient>
              <linearGradient id="grayGradient" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#475569" stop-opacity="0.25" />
                <stop offset="100%" stop-color="#475569" stop-opacity="0.0" />
              </linearGradient>
            </defs>

            <!-- Grid Lines (CAD Horizontal Rulers) -->
            <line x1="0" y1="20" x2="600" y2="20" stroke="#232A36" stroke-dasharray="3,3" stroke-width="1" />
            <line x1="0" y1="65" x2="600" y2="65" stroke="#232A36" stroke-dasharray="3,3" stroke-width="1" />
            <line x1="0" y1="110" x2="600" y2="110" stroke="#232A36" stroke-dasharray="3,3" stroke-width="1" />
            <line x1="0" y1="155" x2="600" y2="155" stroke="#232A36" stroke-dasharray="3,3" stroke-width="1" />
            <line x1="0" y1="190" x2="600" y2="190" stroke="#475569" stroke-width="1.5" />

            <!-- Area under completed curve -->
            <polygon
              fill="url(#yellowGradient)"
              points="20,190 20,125 130,105 245,78 355,52 470,28 580,20 580,190"
            />

            <!-- Line 1: Created SPK (Slate) -->
            <polyline
              fill="none"
              stroke="#64748B"
              stroke-width="2.5"
              stroke-linecap="square"
              stroke-linejoin="miter"
              points="20,118 130,95 245,68 355,45 470,22 580,15"
            />

            <!-- Line 2: Completed SPK (Hazard Yellow) -->
            <polyline
              fill="none"
              stroke="#FACC15"
              stroke-width="3"
              stroke-linecap="square"
              stroke-linejoin="miter"
              points="20,125 130,105 245,78 355,52 470,28 580,20"
            />

            <!-- Data Dots & Tooltip Hotspots -->
            <g v-for="(p, idx) in trendPoints" :key="idx" class="cursor-pointer group/dot">
              <circle :cx="p.x" :cy="p.yCreated" r="3" fill="#64748B" />
              <circle :cx="p.x" :cy="p.yCompleted" r="4.5" fill="#FACC15" stroke="#0B0C0E" stroke-width="1.5" />
              <circle
                :cx="p.x"
                :cy="p.yCompleted"
                r="10"
                fill="transparent"
                @mouseenter="activeHoverPoint = p"
                @mouseleave="activeHoverPoint = null"
              />
            </g>
          </svg>

          <!-- Month Labels on X-Axis -->
          <div class="flex justify-between text-[10px] font-mono text-slate-400 mt-2 px-3">
            <span v-for="(m, i) in monthlyTrends" :key="i" class="text-center font-bold">
              {{ m.month }}
            </span>
          </div>

          <!-- Dynamic Floating Tooltip on Hover -->
          <div
            v-if="activeHoverPoint"
            class="absolute top-2 right-4 p-2.5 bg-[#0B0C0E] border border-[#FACC15] rounded-xs shadow-xl text-[10px] font-mono z-30"
          >
            <div class="text-[#FACC15] font-black uppercase mb-1">{{ activeHoverPoint.month }} 2026 // AUDIT LAPORAN</div>
            <div class="text-white flex justify-between gap-4"><span>Target Terbit:</span> <strong>{{ activeHoverPoint.created }} SPK</strong></div>
            <div class="text-emerald-400 flex justify-between gap-4"><span>Realisasi Selesai:</span> <strong>{{ activeHoverPoint.completed }} SPK ({{ Math.round((activeHoverPoint.completed / activeHoverPoint.created) * 100) }}%)</strong></div>
          </div>
        </div>

        <div class="grid grid-cols-3 gap-2 pt-2 border-t border-[#232A36] text-[10px] font-mono text-center">
          <div class="p-2 bg-[#0E1117] border border-[#232A36]">
            <span class="text-slate-500 block">TOTAL RILIS</span>
            <span class="text-white font-black text-sm">141 SPK</span>
          </div>
          <div class="p-2 bg-[#0E1117] border border-[#232A36]">
            <span class="text-slate-500 block">TOTAL SELESAI</span>
            <span class="text-[#FACC15] font-black text-sm">131 SPK</span>
          </div>
          <div class="p-2 bg-[#0E1117] border border-[#232A36]">
            <span class="text-slate-500 block">COMPLETION RATE</span>
            <span class="text-[#10B981] font-black text-sm">92.9%</span>
          </div>
        </div>
      </div>

      <!-- Right Panel (5 Cols): RE-ENGINEERED KOMPOSISI VALUASI PORTOFOLIO (FLEXIBLE & CLEAR) -->
      <div class="lg:col-span-5 cad-surface p-5 flex flex-col justify-between space-y-4">
        <!-- Panel Header with Toggle Mode View (Donut vs Bar Stack) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#232A36] pb-3">
          <div>
            <h3 class="font-bold text-xs text-white uppercase font-mono tracking-wider flex items-center gap-2">
              <Briefcase class="w-4 h-4 text-[#FACC15]" />
              <span>KOMPOSISI VALUASI PORTOFOLIO</span>
            </h3>
            <p class="text-[10px] text-slate-400 font-mono mt-0.5">Proporsi nilai per sektor pengerjaan proyek.</p>
          </div>

          <!-- Switcher Mode: Donut vs Breakdown -->
          <div class="flex items-center gap-1 bg-[#0E1117] p-0.5 rounded-xs border border-[#232A36] self-start sm:self-auto font-mono text-[9px]">
            <button
              type="button"
              @click="portfolioViewMode = 'donut'"
              :class="[
                'px-2 py-0.5 rounded-xs font-bold transition-all cursor-pointer uppercase',
                portfolioViewMode === 'donut'
                  ? 'bg-[#FACC15] text-[#0B0C0E]'
                  : 'text-slate-400 hover:text-white'
              ]"
            >
              Donut
            </button>
            <button
              type="button"
              @click="portfolioViewMode = 'bars'"
              :class="[
                'px-2 py-0.5 rounded-xs font-bold transition-all cursor-pointer uppercase',
                portfolioViewMode === 'bars'
                  ? 'bg-[#FACC15] text-[#0B0C0E]'
                  : 'text-slate-400 hover:text-white'
              ]"
            >
              Batang
            </button>
          </div>
        </div>

        <!-- Mode 1: Donut Vector with Clear Center Display & Hover Sync -->
        <div v-if="portfolioViewMode === 'donut'" class="relative flex flex-col items-center justify-center my-1">
          <div class="relative flex items-center justify-center">
            <svg viewBox="0 0 42 42" class="w-48 h-48 -rotate-90">
              <!-- Background Track -->
              <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#1C2028" stroke-width="4.5"></circle>

              <!-- Segments rendered dynamically from portfolioSectors -->
              <circle
                v-for="(sec, idx) in portfolioSectors"
                :key="idx"
                cx="21" cy="21" r="15.91549430918954" fill="transparent"
                :stroke="sec.color"
                :stroke-width="selectedSector?.code === sec.code ? 6.5 : 4.8"
                :stroke-dasharray="`${sec.percent} ${100 - sec.percent}`"
                :stroke-dashoffset="sec.offset"
                class="transition-all duration-200 cursor-pointer"
                @mouseenter="selectedSector = sec"
                @mouseleave="selectedSector = null"
                @click="selectedSector = sec"
              />
            </svg>

            <!-- Center Summary (Auto-Updated on Hover / Click) -->
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none font-mono px-3">
              <span class="text-[8px] uppercase tracking-wider text-slate-400">
                {{ selectedSector ? selectedSector.name : 'TOTAL NILAI KONTRAK' }}
              </span>
              <span
                class="text-xl font-black tracking-tight mt-0.5 transition-colors"
                :style="{ color: selectedSector ? selectedSector.color : '#FACC15' }"
              >
                {{ selectedSector ? selectedSector.valuation : 'Rp 960 Jt' }}
              </span>
              <span class="text-[9px] text-slate-300 font-bold mt-0.5">
                {{ selectedSector ? `${selectedSector.percent}% (${selectedSector.volume} SPK)` : '73 SPK Aktif' }}
              </span>
            </div>
          </div>
          <span class="text-[9px] font-mono text-slate-500 mt-1">Arahkan kursor ke segmen atau klik kartu di bawah</span>
        </div>

        <!-- Mode 2: Clean Linear Percentage Breakdown (Multi-Bar Visual) -->
        <div v-else class="space-y-3 py-1">
          <!-- Stacked Single Segment Bar -->
          <div class="space-y-1">
            <div class="flex justify-between text-[10px] font-mono text-slate-400">
              <span>DISTRIBUSI PORTOFOLIO TOTAL</span>
              <span class="text-[#FACC15] font-black">Rp 960 Jt // 100%</span>
            </div>
            <div class="w-full h-3.5 bg-[#0B0C0E] border border-[#232A36] flex overflow-hidden rounded-xs">
              <div
                v-for="(sec, idx) in portfolioSectors"
                :key="idx"
                class="h-full transition-all duration-200 hover:opacity-90 cursor-pointer"
                :style="{ width: `${sec.percent}%`, backgroundColor: sec.color }"
                :title="`${sec.name}: ${sec.valuation} (${sec.percent}%)`"
                @mouseenter="selectedSector = sec"
                @mouseleave="selectedSector = null"
              />
            </div>
          </div>

          <!-- Detailed Quick Stat List -->
          <div class="space-y-1.5 pt-1">
            <div
              v-for="(sec, idx) in portfolioSectors"
              :key="idx"
              @mouseenter="selectedSector = sec"
              @mouseleave="selectedSector = null"
              :class="[
                'p-2 rounded-xs border transition-all font-mono text-[10px] flex items-center justify-between cursor-pointer',
                selectedSector?.code === sec.code
                  ? 'bg-[#181D26] border-[#FACC15] shadow-xs'
                  : 'bg-[#0E1117] border-[#232A36] hover:border-slate-500'
              ]"
            >
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-none shrink-0" :style="{ backgroundColor: sec.color }" />
                <span class="font-bold text-slate-200">{{ sec.name }}</span>
              </div>
              <div class="text-right">
                <span class="font-black text-white mr-2">{{ sec.valuation }}</span>
                <span class="text-[#FACC15] font-bold">({{ sec.percent }}%)</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Interactive Legend Cards (Flexible Grid with Clear Nominal & Percentage) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-3 border-t border-[#232A36] font-mono text-[10px]">
          <div
            v-for="(sec, idx) in portfolioSectors"
            :key="idx"
            @mouseenter="selectedSector = sec"
            @mouseleave="selectedSector = null"
            :class="[
              'p-2 rounded-xs border transition-all flex items-center justify-between cursor-pointer',
              idx === 4 ? 'sm:col-span-2' : '',
              selectedSector?.code === sec.code
                ? 'bg-[#1C2028] border-[#FACC15] text-white'
                : 'bg-[#0E1117] border-[#232A36] text-slate-300 hover:border-slate-500'
            ]"
          >
            <div class="flex items-center gap-1.5 truncate">
              <span class="w-2 h-2 rounded-none shrink-0" :style="{ backgroundColor: sec.color }" />
              <span class="truncate">{{ sec.name }}</span>
            </div>
            <div class="text-right shrink-0 ml-1">
              <span class="font-black text-white">{{ sec.valuation }}</span>
              <span class="text-[#FACC15] ml-1">({{ sec.percent }}%)</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================================================== -->
    <!-- SECTION 2: LAPORAN DIAGRAM TAMBAHAN                  -->
    <!-- ==================================================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <!-- Diagram 1: Pipeline Status Corong (Funnel Horizontal) -->
      <div class="cad-surface p-5 space-y-3.5">
        <div class="flex items-center justify-between border-b border-[#232A36] pb-2.5">
          <div>
            <h3 class="font-bold text-xs text-white uppercase font-mono tracking-wider flex items-center gap-2">
              <Layers class="w-4 h-4 text-[#FACC15]" />
              <span>PIPELINE WORKFLOW SPK</span>
            </h3>
            <p class="text-[10px] text-slate-400 font-mono mt-0.5">Alur status pekerjaan dari penerbitan hingga BA.</p>
          </div>
          <span class="text-[9px] font-mono text-emerald-400 bg-[#13171F] border border-emerald-500/30 px-1.5 py-0.5 font-bold uppercase">
            ACTIVE FLOW
          </span>
        </div>

        <div class="space-y-2 pt-1 font-mono text-xs">
          <div v-for="(stage, idx) in pipelineStages" :key="idx" class="space-y-1">
            <div class="flex items-center justify-between text-[10px]">
              <span class="text-slate-300 font-bold flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 bg-[#FACC15]"></span>
                {{ stage.label }}
              </span>
              <span class="text-[#FACC15] font-black">{{ stage.count }} SPK ({{ stage.percent }}%)</span>
            </div>
            <div class="w-full bg-[#0B0C0E] h-2 border border-[#232A36] overflow-hidden">
              <div
                class="h-full transition-all duration-500"
                :class="stage.colorClass"
                :style="{ width: `${stage.percent}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Diagram 2: Distribusi Geografis & SLA Wilayah (Horizontal Bar Chart) -->
      <div class="cad-surface p-5 space-y-3.5">
        <div class="flex items-center justify-between border-b border-[#232A36] pb-2.5">
          <div>
            <h3 class="font-bold text-xs text-white uppercase font-mono tracking-wider flex items-center gap-2">
              <MapPin class="w-4 h-4 text-[#FACC15]" />
              <span>DISTRIBUSI SPK WILAYAH REGIONAL</span>
            </h3>
            <p class="text-[10px] text-slate-400 font-mono mt-0.5">Sebaran pengerjaan & kepatuhan SLA per area.</p>
          </div>
          <span class="text-[9px] font-mono text-[#FACC15] bg-[#1C2028] px-2 py-0.5 rounded-xs border border-[#232A36]">
            4 AREA
          </span>
        </div>

        <div class="space-y-2.5 pt-1 font-mono">
          <div v-for="(area, idx) in areaDistributions" :key="idx" class="p-2.5 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-1.5">
            <div class="flex items-center justify-between text-[10px]">
              <span class="font-bold text-slate-200 uppercase truncate max-w-[150px]">{{ area.name }}</span>
              <div class="text-right">
                <span class="text-[#FACC15] font-bold">{{ area.count }} SPK</span>
                <span class="text-emerald-400 ml-2 text-[9px]">// SLA {{ area.sla }}%</span>
              </div>
            </div>
            <div class="w-full bg-[#0B0C0E] h-2 border border-[#232A36] overflow-hidden flex">
              <div class="h-full bg-[#FACC15]" :style="{ width: `${area.percent}%` }" title="Volume Share" />
            </div>
          </div>
        </div>
      </div>

      <!-- Diagram 3: Analisis Kendala Lapangan & Waktu Respons -->
      <div class="cad-surface p-5 space-y-3.5">
        <div class="flex items-center justify-between border-b border-[#232A36] pb-2.5">
          <div>
            <h3 class="font-bold text-xs text-white uppercase font-mono tracking-wider flex items-center gap-2">
              <AlertTriangle class="w-4 h-4 text-[#EF4444]" />
              <span>FREKUENSI KENDALA LAPANGAN</span>
            </h3>
            <p class="text-[10px] text-slate-400 font-mono mt-0.5">Faktor penghambat utama & waktu resolusi tim.</p>
          </div>
          <span class="text-[9px] font-mono text-[#EF4444] bg-[#13171F] border border-[#EF4444]/40 px-1.5 py-0.5 font-bold uppercase">
            94% RESOLVED
          </span>
        </div>

        <div class="space-y-2 pt-1 font-mono text-xs">
          <div v-for="(issue, idx) in issueBreakdown" :key="idx" class="space-y-1">
            <div class="flex items-center justify-between text-[10px]">
              <span class="text-slate-300 font-bold">{{ issue.category }}</span>
              <span class="text-slate-400">
                <strong class="text-white">{{ issue.count }} kasus</strong> (avg {{ issue.avgResolution }})
              </span>
            </div>
            <div class="w-full bg-[#0B0C0E] h-2 border border-[#232A36] overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-amber-500 to-rose-500"
                :style="{ width: `${issue.percent}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================================================== -->
    <!-- SECTION 3: PERINGKAT DETAIL KATEGORI & BUKTI FORENSIK-->
    <!-- ==================================================== -->
    <div class="cad-surface p-5 space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#232A36] pb-3">
        <div>
          <h3 class="font-bold text-xs text-white uppercase font-mono tracking-wider flex items-center gap-2">
            <BarChart3 class="w-4 h-4 text-[#FACC15]" />
            <span>PERINGKAT VOLUMETRIK & METRIK MUTU KATEGORI</span>
          </h3>
          <p class="text-[10px] text-slate-400 font-mono mt-0.5">Rincian valuasi portofolio, rasio SLA kepatuhan, dan integrasi preview visual foto bukti.</p>
        </div>
        <div class="text-[9px] font-mono text-[#FACC15] bg-[#1C2028] px-2.5 py-1 rounded-xs border border-[#232A36] uppercase tracking-wider self-start sm:self-auto">
          ARAHKAN KURSOR // BUKTI FORENSIK FOTO
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

          <!-- Interactive Hover Card Preview -->
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
                  SAMPEL FORENSIK FOTO:
                </span>
                <span class="text-slate-300">SPK #{{ job.sampleSpk }}</span>
              </div>

              <!-- 3 Evidence Thumbnails with High-Visibility Tags -->
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
</template>

<script setup>
import { ref } from 'vue';
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
  MapPin,
  Layers,
  AlertTriangle
} from 'lucide-vue-next';

const activeViewMode = ref('ALL');
const viewModes = [
  { id: 'ALL', label: 'SEMUA METRIK' },
  { id: 'PROD', label: 'TREN PRODUKSI' },
  { id: 'REGIONAL', label: 'WILAYAH' }
];

const portfolioViewMode = ref('donut'); // 'donut' or 'bars'
const selectedSector = ref(null);

const showValuationTooltip = ref(false);
const showHashTooltip = ref(false);
const hoveredJobIdx = ref(null);
const activeHoverPoint = ref(null);

// Re-usable Portfolio Sectors Data Model with explicit values and offsets
const portfolioSectors = ref([
  {
    code: 'SIGNBOARD',
    name: 'Signboard & Reklame',
    valuation: 'Rp 360 Jt',
    percent: 37.5,
    volume: 24,
    color: '#FACC15',
    offset: 0
  },
  {
    code: 'FASADE',
    name: 'Branding Fasade Gedung',
    valuation: 'Rp 270 Jt',
    percent: 28.1,
    volume: 18,
    color: '#E2E8F0',
    offset: -37.5
  },
  {
    code: 'HURUF_TIMBUL',
    name: 'Huruf Timbul & Acrylic Display',
    valuation: 'Rp 180 Jt',
    percent: 18.8,
    volume: 15,
    color: '#64748B',
    offset: -65.6
  },
  {
    code: 'STIKER_ATM',
    name: 'Stiker Booth ATM & Kios',
    valuation: 'Rp 95 Jt',
    percent: 9.9,
    volume: 11,
    color: '#10B981',
    offset: -84.4
  },
  {
    code: 'ELECTRICAL_LED',
    name: 'Maintenance & Kelistrikan LED',
    valuation: 'Rp 55 Jt',
    percent: 5.7,
    volume: 5,
    color: '#CA8A04',
    offset: -94.3
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

// Coordinates computed for 600x200 viewBox
const trendPoints = [
  { x: 20, yCreated: 118, yCompleted: 125, month: 'JAN', created: 14, completed: 13 },
  { x: 130, yCreated: 95, yCompleted: 105, month: 'FEB', created: 18, completed: 16 },
  { x: 245, yCreated: 68, yCompleted: 78, month: 'MAR', created: 22, completed: 20 },
  { x: 355, yCreated: 45, yCompleted: 52, month: 'APR', created: 25, completed: 24 },
  { x: 470, yCreated: 22, yCompleted: 28, month: 'MEI', created: 30, completed: 28 },
  { x: 580, yCreated: 15, yCompleted: 20, month: 'JUN', created: 32, completed: 30 }
];

// Pipeline Stages Breakdown
const pipelineStages = ref([
  { label: 'DRAFT & PENUGASAN (READY)', count: 8, percent: 11, colorClass: 'bg-[#64748B]' },
  { label: 'SEDANG DIKERJAKAN LAPANGAN', count: 18, percent: 25, colorClass: 'bg-[#3B82F6]' },
  { label: 'SUBMITTED (MENUNGGU REVIEW)', count: 12, percent: 16, colorClass: 'bg-[#FACC15]' },
  { label: 'PERBAIKAN / REVISI TEKNIS', count: 4, percent: 5, colorClass: 'bg-[#EF4444]' },
  { label: 'APPROVED & BA OPNAME SELESAI', count: 31, percent: 43, colorClass: 'bg-[#10B981]' }
]);

// Regional Distribution
const areaDistributions = ref([
  { name: 'Jawa Barat & Bandung Raya', count: 32, percent: 44, sla: 96.5 },
  { name: 'DKI Jakarta & Banten', count: 22, percent: 30, sla: 94.0 },
  { name: 'Jawa Tengah (Semarang & Solo)', count: 12, percent: 16, sla: 92.8 },
  { name: 'Jawa Timur & Bali Regional', count: 7, percent: 10, sla: 91.5 }
]);

// Field Issue Breakdown
const issueBreakdown = ref([
  { category: 'Akses Lokasi & Izin Security', count: 14, percent: 40, avgResolution: '1.8 Jam' },
  { category: 'Cuaca Buruk / Hujan Lebat', count: 9, percent: 26, avgResolution: '3.5 Jam' },
  { category: 'Kelistrikan / Jalur Lampu Mati', count: 7, percent: 20, avgResolution: '2.1 Jam' },
  { category: 'Material Rusak / Penyesuaian Ukuran', count: 5, percent: 14, avgResolution: '4.2 Jam' }
]);

const rankedCategories = ref([
  {
    title: 'Pemasangan Signboard & Reklame',
    code: 'SIGNBOARD',
    valuation: 'Rp 360 Jt',
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
    valuation: 'Rp 270 Jt',
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
    valuation: 'Rp 180 Jt',
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
    valuation: 'Rp 95 Jt',
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
    valuation: 'Rp 55 Jt',
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
</script>
