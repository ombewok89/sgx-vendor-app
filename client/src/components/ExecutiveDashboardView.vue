<template>
  <div class="space-y-6">
    <!-- Executive Top Strategic Scorecard -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- KPI 1: On-Time Delivery -->
      <div class="glass-card rounded-3xl p-5 border border-white/80 shadow-glass relative overflow-hidden group hover:border-purple-300 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">On-Time Delivery (SLA)</span>
          <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200">
            <Clock class="w-4 h-4" />
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <div class="text-3xl font-black font-mono text-emerald-950">94.2%</div>
          <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
            Target ≥ 90% ✓
          </span>
        </div>
        <p class="text-[11px] text-slate-500 mt-2">Persentase pekerjaan diselesaikan tepat waktu sebelum batas deadline.</p>
        <div class="w-full bg-slate-100 h-1.5 rounded-full mt-3 overflow-hidden">
          <div class="bg-gradient-to-r from-emerald-600 to-teal-500 h-full rounded-full w-[94.2%]" />
        </div>
      </div>

      <!-- KPI 2: First-Pass Yield -->
      <div class="glass-card rounded-3xl p-5 border border-white/80 shadow-glass relative overflow-hidden group hover:border-purple-300 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">First-Pass Approval</span>
          <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-200">
            <CheckCircle2 class="w-4 h-4" />
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <div class="text-3xl font-black font-mono text-purple-950">88.5%</div>
          <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200">
            Mutu Tinggi
          </span>
        </div>
        <p class="text-[11px] text-slate-500 mt-2">Pekerjaan yang langsung disetujui tanpa perlu adanya permintaan revisi.</p>
        <div class="w-full bg-slate-100 h-1.5 rounded-full mt-3 overflow-hidden">
          <div class="bg-gradient-to-r from-purple-600 to-indigo-500 h-full rounded-full w-[88.5%]" />
        </div>
      </div>

      <!-- KPI 3: Total Project Valuation -->
      <div class="glass-card rounded-3xl p-5 border border-white/80 shadow-glass relative overflow-hidden group hover:border-purple-300 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Nilai Proyek SPK</span>
          <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center border border-teal-200">
            <Coins class="w-4 h-4" />
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <div class="text-2xl font-black font-mono text-teal-950">Rp 960 Jt</div>
          <span class="text-[10px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-full border border-teal-200">
            73 SPK
          </span>
        </div>
        <p class="text-[11px] text-slate-500 mt-2">Akumulasi estimasi nilai seluruh kontrak pekerjaan vendor yang terdata.</p>
        <div class="w-full bg-slate-100 h-1.5 rounded-full mt-3 overflow-hidden">
          <div class="bg-gradient-to-r from-teal-600 to-emerald-500 h-full rounded-full w-[85%]" />
        </div>
      </div>

      <!-- KPI 4: Evidence Integrity -->
      <div class="glass-card rounded-3xl p-5 border border-white/80 shadow-glass relative overflow-hidden group hover:border-purple-300 transition-all duration-300">
        <div class="flex items-center justify-between mb-3">
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Integritas Bukti Foto</span>
          <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200">
            <ShieldCheck class="w-4 h-4" />
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <div class="text-3xl font-black font-mono text-blue-950">100%</div>
          <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">
            SHA-256 Valid
          </span>
        </div>
        <p class="text-[11px] text-slate-500 mt-2">Seluruh foto terkunci enkripsi hash anti-manipulasi & GPS check-in resmi.</p>
        <div class="w-full bg-slate-100 h-1.5 rounded-full mt-3 overflow-hidden">
          <div class="bg-gradient-to-r from-blue-600 to-cyan-500 h-full rounded-full w-full" />
        </div>
      </div>
    </div>

    <!-- EXCLUSIVE REDESIGN: Analisis Kategori / Jenis Pekerjaan Terbanyak & Nilai Anggaran dengan Donut Chart -->
    <div class="glass-card rounded-3xl p-6 sm:p-7 border border-white/80 shadow-glass space-y-6">
      <!-- Section Header with Executive Summary Pills -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-5">
        <div>
          <h3 class="font-black text-lg text-slate-900 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-purple-800 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-purple-900/20">
              <Briefcase class="w-4 h-4" />
            </div>
            <span>Kategori Jenis Pekerjaan Terbanyak & Valuasi Kontrak</span>
          </h3>
          <p class="text-xs text-slate-500 mt-1 font-medium">
            Distribusi volume pengerjaan proyek untuk Client, rasio nilai anggaran rupiah, dan standar SLA mutu hasil kerja per kategori.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
          <div class="px-3.5 py-1.5 rounded-xl bg-purple-50 border border-purple-200 text-purple-900 font-mono text-xs font-bold shadow-2xs flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-purple-600"></span>
            <span>Total Valuasi: <strong>Rp 960.000.000</strong></span>
          </div>
          <div class="px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 font-mono text-xs font-semibold">
            Volume: <strong>73 SPK</strong>
          </div>
          <div class="px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-mono text-xs font-semibold">
            Rata-rata: <strong>Rp 13.1 Jt / SPK</strong>
          </div>
        </div>
      </div>

      <!-- Main Content: Dual-Column Executive Split Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
        <!-- Left Column (5 Cols): Interactive Vector Donut Chart & Portfolio Share -->
        <div class="lg:col-span-5 flex flex-col items-center justify-center p-6 bg-gradient-to-b from-slate-50/80 to-purple-50/30 rounded-3xl border border-slate-200/80 shadow-xs space-y-5">
          <div class="w-full flex items-center justify-between">
            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Komposisi Valuasi (Share %):</span>
            <span class="text-[10px] font-bold text-purple-900 bg-purple-100 px-2.5 py-0.5 rounded-full border border-purple-200">
              5 Kategori Utama
            </span>
          </div>

          <!-- Circular Vector Donut Chart with Center Metric -->
          <div class="relative flex items-center justify-center my-2">
            <svg viewBox="0 0 42 42" class="w-52 h-52 -rotate-90 drop-shadow-sm">
              <!-- Background Circle Track -->
              <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#f1f5f9" stroke-width="4.5"></circle>
              <!-- Segment 1: Signboard (37.5%) -->
              <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#7e22ce" stroke-width="4.8" stroke-dasharray="37.5 62.5" stroke-dashoffset="0" class="hover:stroke-width-6 transition-all duration-300"></circle>
              <!-- Segment 2: Branding Fasade (28.1%) -->
              <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#0284c7" stroke-width="4.8" stroke-dasharray="28.1 71.9" stroke-dashoffset="-37.5" class="hover:stroke-width-6 transition-all duration-300"></circle>
              <!-- Segment 3: Kanopi & Pylon (15.6%) -->
              <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#d97706" stroke-width="4.8" stroke-dasharray="15.6 84.4" stroke-dashoffset="-65.6" class="hover:stroke-width-6 transition-all duration-300"></circle>
              <!-- Segment 4: Stiker ATM (12.5%) -->
              <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#059669" stroke-width="4.8" stroke-dasharray="12.5 87.5" stroke-dashoffset="-81.2" class="hover:stroke-width-6 transition-all duration-300"></circle>
              <!-- Segment 5: Kelistrikan LED (6.3%) -->
              <circle cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#e11d48" stroke-width="4.8" stroke-dasharray="6.3 93.7" stroke-dashoffset="-93.7" class="hover:stroke-width-6 transition-all duration-300"></circle>
            </svg>

            <!-- Center Centerpiece Badge -->
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
              <span class="text-[9px] uppercase tracking-wider font-bold text-slate-400">Total Portofolio</span>
              <span class="text-xl font-black text-slate-900 font-mono tracking-tight mt-0.5">Rp 960 Jt</span>
              <span class="text-[10px] font-bold text-purple-700 font-mono mt-0.5">73 SPK Selesai</span>
            </div>
          </div>

          <!-- Color Legend Grid -->
          <div class="grid grid-cols-2 gap-x-3 gap-y-2 w-full text-[11px] pt-2 border-t border-slate-200/80">
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-purple-700 shrink-0"></span>
              <span class="truncate text-slate-700">Signboard (37.5%)</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-blue-600 shrink-0"></span>
              <span class="truncate text-slate-700">Fasade (28.1%)</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-amber-600 shrink-0"></span>
              <span class="truncate text-slate-700">Kanopi (15.6%)</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 shrink-0"></span>
              <span class="truncate text-slate-700">Stiker ATM (12.5%)</span>
            </div>
            <div class="flex items-center gap-1.5 col-span-2">
              <span class="w-2.5 h-2.5 rounded-full bg-rose-600 shrink-0"></span>
              <span class="truncate text-slate-700">Kelistrikan LED (6.3%)</span>
            </div>
          </div>

          <!-- Key Takeaway Card -->
          <div class="w-full p-3 bg-white rounded-2xl border border-purple-200 text-xs text-purple-950 flex items-start gap-2 shadow-2xs">
            <Sparkles class="w-4 h-4 text-purple-700 shrink-0 mt-0.5" />
            <p class="text-[11px] leading-relaxed">
              <strong>Kontributor Terbesar:</strong> Kategori <em>Signboard & Reklame</em> menyumbang <strong>37.5%</strong> dari total valuasi dengan nilai <strong>Rp 360.000.000</strong>.
            </p>
          </div>
        </div>

        <!-- Right Column (7 Cols): Ranked Spacious Executive Category Cards -->
        <div class="lg:col-span-7 space-y-3">
          <div
            v-for="(job, idx) in jobTypeStats"
            :key="idx"
            class="p-4 rounded-2xl bg-white border border-slate-200/90 hover:border-purple-300 shadow-xs hover:shadow-md transition-all space-y-2.5 group"
          >
            <!-- Top Line: Rank Badge, Title, and Big Monospace Valuation -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div class="flex items-center gap-3">
                <!-- Rank Pill -->
                <span class="w-6 h-6 rounded-lg bg-slate-900 text-white font-mono font-black text-xs flex items-center justify-center shadow-xs">
                  #{{ idx + 1 }}
                </span>
                <!-- Icon & Title -->
                <div class="flex items-center gap-2">
                  <div :class="['w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs shrink-0', job.iconBg, job.iconColor]">
                    <component :is="job.icon" class="w-3.5 h-3.5" />
                  </div>
                  <div>
                    <h4 class="font-bold text-slate-900 text-xs group-hover:text-purple-900 transition-colors">
                      {{ job.name }}
                    </h4>
                    <span class="text-[10px] text-slate-400 font-mono">{{ job.code }}</span>
                  </div>
                </div>
              </div>

              <!-- Big Bold Valuation -->
              <div class="text-right self-end sm:self-auto">
                <div class="font-black text-slate-900 font-mono text-sm tracking-tight text-brand-900">
                  {{ job.totalValueFormatted }}
                </div>
                <div class="text-[10px] text-slate-400 font-mono">
                  {{ job.volume }} SPK <span class="text-slate-300">•</span> @{{ job.avgValue }}
                </div>
              </div>
            </div>

            <!-- Progress Bar with Glow Accent -->
            <div class="space-y-1">
              <div class="flex items-center justify-between text-[10px] text-slate-500 font-mono">
                <span>Porsi Kontrak: <strong class="text-slate-800">{{ job.percent }}%</strong></span>
                <span class="text-emerald-700 font-bold">SLA Mutu: {{ job.slaRate }}% (Sangat Baik ✓)</span>
              </div>
              <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                <div :class="['h-full rounded-full transition-all duration-700', job.barColor]" :style="{ width: `${job.percent}%` }" />
              </div>
            </div>

            <!-- Bottom Metadata Line -->
            <div class="flex items-center justify-between text-[10px] text-slate-500 pt-1 border-t border-slate-100">
              <span class="truncate">Mode Bukti: <strong class="text-slate-700 font-medium">{{ job.docMode }}</strong></span>
              <span :class="['px-2 py-0.5 rounded-md font-mono font-bold text-[9px] border', job.badgeClass]">
                KONTRIBUSI {{ job.percent }}%
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row 1: Monthly Trend & Vendor Performance -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <!-- Chart 1: Monthly SPK Completion Trend -->
      <div class="glass-card rounded-3xl p-6 border border-white/80 shadow-glass space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
              <BarChart3 class="w-4 h-4 text-purple-700" />
              <span>Tren Pengerjaan & Penyelesaian SPK (6 Bulan Terakhir)</span>
            </h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Perbandingan jumlah SPK yang diterbitkan vs SPK yang selesai 100%.</p>
          </div>
          <div class="flex items-center gap-3 text-[10px] font-bold">
            <span class="flex items-center gap-1 text-purple-900">
              <span class="w-2.5 h-2.5 rounded-full bg-purple-700"></span> Diterbitkan
            </span>
            <span class="flex items-center gap-1 text-emerald-700">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Selesai (BA)
            </span>
          </div>
        </div>

        <!-- SVG Visual Trend Chart -->
        <div class="h-56 w-full flex items-end justify-between gap-3 pt-6 px-2">
          <div v-for="(item, idx) in monthlyTrends" :key="idx" class="flex-1 flex flex-col items-center gap-2 h-full justify-end group">
            <div class="w-full flex items-end justify-center gap-1.5 h-full">
              <!-- Target Bar -->
              <div
                class="w-4 sm:w-6 bg-purple-200 group-hover:bg-purple-300 rounded-t-lg transition-all relative"
                :style="{ height: `${(item.created / maxTrendVal) * 100}%` }"
              >
                <div class="opacity-0 group-hover:opacity-100 absolute -top-6 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[9px] font-mono px-1.5 py-0.5 rounded shadow whitespace-nowrap transition-opacity pointer-events-none">
                  {{ item.created }} SPK
                </div>
              </div>
              <!-- Completed Bar -->
              <div
                class="w-4 sm:w-6 bg-gradient-to-t from-emerald-600 to-teal-400 rounded-t-lg transition-all relative shadow-xs"
                :style="{ height: `${(item.completed / maxTrendVal) * 100}%` }"
              >
                <div class="opacity-0 group-hover:opacity-100 absolute -top-6 left-1/2 -translate-x-1/2 bg-emerald-950 text-emerald-300 text-[9px] font-mono px-1.5 py-0.5 rounded shadow whitespace-nowrap transition-opacity pointer-events-none">
                  {{ item.completed }} Selesai
                </div>
              </div>
            </div>
            <span class="text-[10px] font-bold text-slate-600">{{ item.month }}</span>
          </div>
        </div>
      </div>

      <!-- Chart 2: Vendor Performance Ranking -->
      <div class="glass-card rounded-3xl p-6 border border-white/80 shadow-glass space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
              <Building2 class="w-4 h-4 text-purple-700" />
              <span>Peringkat & Skor Kinerja Mitra Vendor</span>
            </h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Evaluasi volume penyelesaian, SLA ketepatan waktu, dan rating mutu.</p>
          </div>
          <span class="text-[10px] font-bold text-slate-400 uppercase">Q3 2026</span>
        </div>

        <div class="space-y-3 pt-1">
          <div
            v-for="(vendor, idx) in vendorRanks"
            :key="idx"
            class="p-3.5 rounded-2xl bg-white/70 border border-slate-200/80 hover:bg-white transition-all space-y-2"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-lg bg-slate-900 text-white font-bold text-xs flex items-center justify-center font-mono">
                  #{{ idx + 1 }}
                </span>
                <div>
                  <span class="font-bold text-slate-900 text-xs">{{ vendor.name }}</span>
                  <span class="text-[10px] text-slate-400 font-mono ml-1.5">({{ vendor.completedSpk }} SPK Selesai)</span>
                </div>
              </div>
              <div class="flex items-center gap-1 text-amber-500 font-bold text-xs font-mono">
                <span>★ {{ vendor.rating }}</span>
              </div>
            </div>

            <!-- Performance Bar -->
            <div class="space-y-1">
              <div class="flex items-center justify-between text-[10px]">
                <span class="text-slate-500">SLA Ketepatan Waktu:</span>
                <span class="font-bold font-mono text-slate-800">{{ vendor.slaRate }}%</span>
              </div>
              <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full bg-gradient-to-r from-brand-900 via-purple-700 to-emerald-500"
                  :style="{ width: `${vendor.slaRate}%` }"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row 2: Regional Distribution & Field Issues Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Chart 3: Regional Workload Distribution -->
      <div class="glass-card rounded-3xl p-6 border border-white/80 shadow-glass space-y-4">
        <div class="border-b border-slate-100 pb-3">
          <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
            <MapPin class="w-4 h-4 text-purple-700" />
            <span>Sebaran Area Operasional</span>
          </h3>
          <p class="text-[11px] text-slate-500 mt-0.5">Proporsi sebaran proyek di setiap wilayah kerja.</p>
        </div>

        <div class="space-y-3 pt-1 text-xs">
          <div v-for="(area, idx) in areaDistributions" :key="idx" class="space-y-1">
            <div class="flex items-center justify-between text-[11px]">
              <span class="font-bold text-slate-800">{{ area.name }}</span>
              <span class="font-mono font-semibold text-slate-500">{{ area.count }} SPK ({{ area.percent }}%)</span>
            </div>
            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
              <div :class="['h-full rounded-full', area.color]" :style="{ width: `${area.percent}%` }" />
            </div>
          </div>
        </div>
      </div>

      <!-- Chart 4: Pareto Analysis of Field Issues -->
      <div class="glass-card rounded-3xl p-6 border border-white/80 shadow-glass space-y-4">
        <div class="border-b border-slate-100 pb-3">
          <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
            <AlertTriangle class="w-4 h-4 text-amber-600" />
            <span>Analisis Kendala Lapangan</span>
          </h3>
          <p class="text-[11px] text-slate-500 mt-0.5">Kategori kendala paling sering dialami teknisi.</p>
        </div>

        <div class="space-y-2.5 pt-1 text-xs">
          <div v-for="(issue, idx) in issueBreakdowns" :key="idx" class="p-2.5 rounded-xl bg-amber-50/50 border border-amber-200/60 flex items-center justify-between">
            <div class="flex items-center gap-2 truncate">
              <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
              <span class="font-bold text-slate-800 text-[11px] truncate">{{ issue.category }}</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="font-mono font-bold text-amber-900 text-xs">{{ issue.percent }}%</span>
              <span class="text-[10px] text-slate-400">({{ issue.count }}x)</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Live Gateway & Activity Feed -->
      <div class="glass-card rounded-3xl p-6 border border-white/80 shadow-glass space-y-4">
        <div class="border-b border-slate-100 pb-3">
          <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
            <Activity class="w-4 h-4 text-emerald-600" />
            <span>Gateway & Live Activity Stream</span>
          </h3>
          <p class="text-[11px] text-slate-500 mt-0.5">Status integrasi WhatsApp & aktivitas terbaru.</p>
        </div>

        <!-- WhatsApp Gateway Status Box (Dinamis & Jujur) -->
        <div
          :class="[
            'p-3 border rounded-2xl flex items-center justify-between transition-all',
            gatewayData.state === 'ACTIVE'
              ? 'bg-emerald-50 border-emerald-200 text-emerald-950'
              : gatewayData.state === 'MOCK'
              ? 'bg-amber-50 border-amber-200 text-amber-950'
              : 'bg-rose-50 border-rose-200 text-rose-950'
          ]"
        >
          <div class="flex items-center gap-2.5">
            <div
              :class="[
                'w-8 h-8 rounded-xl text-white flex items-center justify-center font-bold text-xs shadow-xs',
                gatewayData.state === 'ACTIVE'
                  ? 'bg-emerald-600'
                  : gatewayData.state === 'MOCK'
                  ? 'bg-amber-600'
                  : 'bg-rose-600'
              ]"
            >
              WA
            </div>
            <div>
              <div class="font-bold text-xs flex items-center gap-1.5">
                <span>WhatsApp Gateway (Fonnte)</span>
                <span
                  :class="[
                    'text-[9px] font-mono font-bold px-1.5 py-0.2 rounded',
                    gatewayData.state === 'ACTIVE'
                      ? 'bg-emerald-200/80 text-emerald-800'
                      : gatewayData.state === 'MOCK'
                      ? 'bg-amber-200/80 text-amber-800'
                      : 'bg-rose-200/80 text-rose-800'
                  ]"
                >
                  {{ gatewayData.state === 'ACTIVE' ? 'LIVE ONLINE' : gatewayData.state === 'MOCK' ? 'MOCK MODE' : 'OFFLINE' }}
                </span>
              </div>
              <div
                :class="[
                  'text-[10px] font-medium mt-0.5',
                  gatewayData.state === 'ACTIVE'
                    ? 'text-emerald-700'
                    : gatewayData.state === 'MOCK'
                    ? 'text-amber-700'
                    : 'text-rose-700'
                ]"
              >
                {{
                  gatewayData.state === 'ACTIVE'
                    ? (gatewayData.has_logs ? `Tingkat Pengiriman: ${gatewayData.success_rate}% (${gatewayData.total_sent}/${gatewayData.total_logs} Terkirim)` : 'Gateway Aktif (Siap Kirim Notifikasi)')
                    : gatewayData.state === 'MOCK'
                    ? 'Simulasi Pengujian Lokal (Mock Mode Aktif)'
                    : '⚠️ Belum Dikonfigurasi (Token Kosong di Dashboard/.env)'
                }}
              </div>
            </div>
          </div>
          <span
            :class="[
              'w-2.5 h-2.5 rounded-full',
              gatewayData.state === 'ACTIVE'
                ? 'bg-emerald-500 animate-ping'
                : gatewayData.state === 'MOCK'
                ? 'bg-amber-500'
                : 'bg-rose-500'
            ]"
          ></span>
        </div>

        <!-- Live Feed List -->
        <div class="space-y-2 text-xs">
          <div class="font-bold text-[10px] text-slate-400 uppercase tracking-wider">Aktivitas Sistem Terkini:</div>
          <div class="space-y-2 max-h-36 overflow-y-auto pr-1">
            <div class="p-2 bg-slate-50 rounded-xl border border-slate-200/60 text-[11px]">
              <div class="flex items-center justify-between font-bold text-slate-800">
                <span>Andi Pratama (Field Team)</span>
                <span class="text-[9px] text-slate-400 font-mono">2m lalu</span>
              </div>
              <p class="text-[10px] text-slate-500 truncate mt-0.5">Check-in GPS di Cabang Dago Bandung (±15m)</p>
            </div>

            <div class="p-2 bg-slate-50 rounded-xl border border-slate-200/60 text-[11px]">
              <div class="flex items-center justify-between font-bold text-slate-800">
                <span>Dian Anggraini (Admin)</span>
                <span class="text-[9px] text-slate-400 font-mono">15m lalu</span>
              </div>
              <p class="text-[10px] text-slate-500 truncate mt-0.5">Menyetujui pengerjaan SPK-2026-00125 (Approved)</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../services/api';
import {
  Clock,
  CheckCircle2,
  ShieldCheck,
  Zap,
  BarChart3,
  Building2,
  MapPin,
  AlertTriangle,
  Activity,
  Coins,
  Briefcase,
  Layers,
  Sparkles,
  Paintbrush,
  Wrench,
  Sticker,
  Lightbulb
} from 'lucide-vue-next';

const gatewayData = ref({
  state: 'UNCONFIGURED',
  token_configured: false,
  mock_enabled: false,
  total_sent: 0,
  total_failed: 0,
  total_logs: 0,
  success_rate: 0,
  has_logs: false
});

async function loadGatewayStatus() {
  try {
    const res = await api.getGatewayStatus();
    if (res.data) {
      gatewayData.value = res.data;
    }
  } catch (err) {
    console.warn('Failed to load gateway status:', err.message);
  }
}

onMounted(() => {
  loadGatewayStatus();
});

const maxTrendVal = 35;

// Job Types Breakdown & Financial Valuation Data
const jobTypeStats = ref([
  {
    name: 'Pemasangan Signboard & Reklame',
    code: 'SIGNBOARD',
    volume: 24,
    percent: 37.5,
    totalValueFormatted: 'Rp 360.000.000',
    avgValue: 'Rp 15 Jt',
    docMode: 'Before / Process / After',
    slaRate: 96.0,
    icon: Sparkles,
    iconBg: 'bg-purple-100',
    iconColor: 'text-purple-700',
    badgeClass: 'bg-purple-50 text-purple-700 border-purple-200',
    barColor: 'bg-gradient-to-r from-purple-700 to-indigo-600'
  },
  {
    name: 'Pengecatan & Branding Fasade Gedung',
    code: 'BRANDING_FASADE',
    volume: 18,
    percent: 28.1,
    totalValueFormatted: 'Rp 270.000.000',
    avgValue: 'Rp 15 Jt',
    docMode: 'Before / Process / After',
    slaRate: 94.5,
    icon: Paintbrush,
    iconBg: 'bg-blue-100',
    iconColor: 'text-blue-700',
    badgeClass: 'bg-blue-50 text-blue-700 border-blue-200',
    barColor: 'bg-gradient-to-r from-blue-600 to-cyan-500'
  },
  {
    name: 'Pemeliharaan Kanopi & Pylon Sign',
    code: 'MAINTENANCE_PYLON',
    volume: 10,
    percent: 15.6,
    totalValueFormatted: 'Rp 150.000.000',
    avgValue: 'Rp 15 Jt',
    docMode: 'Before / Process / After',
    slaRate: 91.0,
    icon: Wrench,
    iconBg: 'bg-amber-100',
    iconColor: 'text-amber-700',
    badgeClass: 'bg-amber-50 text-amber-700 border-amber-200',
    barColor: 'bg-gradient-to-r from-amber-500 to-orange-500'
  },
  {
    name: 'Stiker Sandblast & Kaca ATM',
    code: 'STIKER_ATM',
    volume: 15,
    percent: 12.5,
    totalValueFormatted: 'Rp 120.000.000',
    avgValue: 'Rp 8 Jt',
    docMode: 'After Only',
    slaRate: 98.0,
    icon: Sticker,
    iconBg: 'bg-emerald-100',
    iconColor: 'text-emerald-700',
    badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    barColor: 'bg-gradient-to-r from-emerald-600 to-teal-500'
  },
  {
    name: 'Instalasi Kelistrikan & Panel LED',
    code: 'ELECTRICAL_LED',
    volume: 6,
    percent: 6.3,
    totalValueFormatted: 'Rp 60.000.000',
    avgValue: 'Rp 10 Jt',
    docMode: 'Before / Process / After',
    slaRate: 93.5,
    icon: Lightbulb,
    iconBg: 'bg-rose-100',
    iconColor: 'text-rose-700',
    badgeClass: 'bg-rose-50 text-rose-700 border-rose-200',
    barColor: 'bg-gradient-to-r from-rose-500 to-pink-500'
  }
]);

const monthlyTrends = ref([
  { month: 'Jan', created: 14, completed: 13 },
  { month: 'Feb', created: 18, completed: 16 },
  { month: 'Mar', created: 22, completed: 20 },
  { month: 'Apr', created: 25, completed: 24 },
  { month: 'Mei', created: 30, completed: 28 },
  { month: 'Jun', created: 32, completed: 30 }
]);

const vendorRanks = ref([
  { name: 'PT Sinar Graha Kreatif', completedSpk: 18, slaRate: 97.5, rating: '4.9' },
  { name: 'CV Mahakarya Reklame', completedSpk: 12, slaRate: 93.0, rating: '4.7' },
  { name: 'PT Visual Prima Pratama', completedSpk: 8, slaRate: 89.5, rating: '4.5' }
]);

const areaDistributions = ref([
  { name: 'Jawa Barat (Bandung & Sekitarnya)', count: 18, percent: 45, color: 'bg-purple-700' },
  { name: 'DKI Jakarta & Banten', count: 12, percent: 30, color: 'bg-brand-600' },
  { name: 'Jawa Tengah (Semarang & Solo)', count: 6, percent: 15, color: 'bg-emerald-500' },
  { name: 'Jawa Timur & Bali', count: 4, percent: 10, color: 'bg-amber-500' }
]);

const issueBreakdowns = ref([
  { category: 'Cuaca Buruk / Hujan Lebat', percent: 45, count: 9 },
  { category: 'Akses Lokasi Ditutup / Dibatasi', percent: 25, count: 5 },
  { category: 'Kekurangan Material Khusus', percent: 15, count: 3 },
  { category: 'Izin Lingkungan / Warga', percent: 10, count: 2 },
  { category: 'Sumber Daya Listrik Padam', percent: 5, count: 1 }
]);
</script>
