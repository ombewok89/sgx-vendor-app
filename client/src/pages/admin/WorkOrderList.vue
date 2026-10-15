<template>
  <div class="space-y-5 text-slate-100 font-sans">
    <!-- Header Title & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#232A36] pb-4">
      <div>
        <div class="flex items-center gap-2 text-[10px] font-mono text-slate-400 uppercase tracking-wider mb-1">
          <span>PORTAL</span>
          <span class="text-slate-600">//</span>
          <span>MANAJEMEN PEKERJAAN</span>
          <span class="text-slate-600">//</span>
          <span class="text-[#FACC15] font-bold">SPK REGISTER</span>
        </div>
        <h2 class="text-xl sm:text-2xl font-black text-white tracking-wider flex items-center gap-2.5 font-mono uppercase">
          <div class="w-8 h-8 rounded-xs bg-[#1C2028] border border-[#FACC15] flex items-center justify-center text-[#FACC15]">
            <FileText class="w-4 h-4" />
          </div>
          <span>MANAJEMEN PEKERJAAN // SPK</span>
        </h2>
        <p class="text-[11px] text-slate-400 mt-1 font-mono">
          Register Surat Perintah Kerja (SPK), pelacakan progres lapangan, validasi teknisi, dan Berita Acara resmi.
        </p>
      </div>

      <div class="flex items-center gap-2 flex-wrap font-mono">
        <!-- Export CSV / Excel Button -->
        <button
          @click="exportCSV"
          :disabled="workOrders.length === 0"
          class="px-3.5 py-2 bg-[#13171F] hover:bg-[#1A202C] text-slate-300 hover:text-white rounded-xs text-xs font-bold flex items-center gap-2 border border-[#232A36] hover:border-[#10B981] transition-colors active:scale-95 cursor-pointer disabled:opacity-50 uppercase tracking-wider"
          title="Export Data SPK Terfilter ke Excel / CSV"
        >
          <FileSpreadsheet class="w-3.5 h-3.5 text-[#10B981]" />
          <span>EXPORT EXCEL</span>
        </button>

        <!-- Superuser Archive Mode Toggle Tabs -->
        <div v-if="isSuperuser" class="bg-[#13171F] p-0.5 rounded-xs border border-[#232A36] flex items-center text-xs font-bold">
          <button
            type="button"
            @click="switchTab('ACTIVE')"
            :class="[
              'px-3 py-1 rounded-xs transition-colors cursor-pointer flex items-center gap-1.5 uppercase',
              activeTab === 'ACTIVE'
                ? 'bg-[#1C2028] text-[#FACC15] border-l-2 border-[#FACC15]'
                : 'text-slate-400 hover:text-white'
            ]"
          >
            <Layers class="w-3 h-3 text-[#FACC15]" />
            <span>SPK AKTIF</span>
          </button>

          <button
            type="button"
            @click="switchTab('ARCHIVED')"
            :class="[
              'px-3 py-1 rounded-xs transition-colors cursor-pointer flex items-center gap-1.5 uppercase',
              activeTab === 'ARCHIVED'
                ? 'bg-[#FACC15] text-[#0B0C0E] font-black'
                : 'text-slate-400 hover:text-[#FACC15]'
            ]"
          >
            <Archive class="w-3 h-3" />
            <span>ARSIP SPK</span>
          </button>
        </div>

        <button
          @click="loadData"
          class="px-3 py-2 bg-[#13171F] hover:bg-[#1A202C] rounded-xs text-slate-300 hover:text-white text-xs font-bold flex items-center gap-1.5 border border-[#232A36] hover:border-[#FACC15]/60 transition-colors active:scale-95 cursor-pointer uppercase"
          title="Segarkan Data"
        >
          <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin' : '']" />
          <span>REFRESH</span>
        </button>

        <button
          v-if="auth.hasPermission('admin_spk', 'create')"
          @click="$emit('open-create')"
          class="px-4 py-2 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] rounded-xs text-xs font-black flex items-center gap-1.5 border border-[#FACC15] transition-colors active:scale-95 cursor-pointer uppercase tracking-wider shadow-xs"
        >
          <Plus class="w-4 h-4 stroke-[3]" />
          <span>+ BUAT SPK BARU</span>
        </button>
      </div>
    </div>

    <!-- Quick Status Summary Cards (Interactive Filters) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 font-mono">
      <div
        @click="setStatusFilter('ALL')"
        :class="[
          'cad-crosshair-card p-3.5 border transition-colors cursor-pointer select-none',
          selectedQuickStatus === 'ALL'
            ? 'bg-[#1C2028] border-[#FACC15] shadow-xs'
            : 'bg-[#13171F] border-[#232A36] hover:border-[#FACC15]/60'
        ]"
      >
        <div class="flex items-center justify-between mb-1">
          <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">// TOTAL SEMUA SPK</div>
          <div class="w-6 h-6 rounded-xs bg-[#1C2028] text-slate-300 flex items-center justify-center border border-[#232A36]">
            <Layers class="w-3 h-3" />
          </div>
        </div>
        <div class="text-2xl font-black text-white tracking-tight">{{ totalWorkOrdersCount }}</div>
        <div class="text-[9px] text-slate-500 mt-0.5">Seluruh pesanan terdaftar</div>
      </div>

      <div
        @click="setStatusFilter('IN_PROGRESS')"
        :class="[
          'cad-crosshair-card p-3.5 border transition-colors cursor-pointer select-none',
          selectedQuickStatus === 'IN_PROGRESS'
            ? 'bg-[#1C2028] border-[#FACC15] shadow-xs'
            : 'bg-[#13171F] border-[#232A36] hover:border-[#FACC15]/60'
        ]"
      >
        <div class="flex items-center justify-between mb-1">
          <div class="text-[9px] font-bold text-[#FACC15] uppercase tracking-wider">// PROSES LAPANGAN</div>
          <div class="w-6 h-6 rounded-xs bg-[#1C2028] text-[#FACC15] flex items-center justify-center border border-[#FACC15]/40">
            <Clock class="w-3 h-3" />
          </div>
        </div>
        <div class="text-2xl font-black text-[#FACC15] tracking-tight">{{ inProgressCount }}</div>
        <div class="text-[9px] text-slate-500 mt-0.5">Penugasan & check-in aktif</div>
      </div>

      <div
        @click="setStatusFilter('REVIEW')"
        :class="[
          'cad-crosshair-card p-3.5 border transition-colors cursor-pointer select-none',
          selectedQuickStatus === 'REVIEW'
            ? 'bg-[#1C2028] border-amber-400 shadow-xs'
            : 'bg-[#13171F] border-[#232A36] hover:border-amber-400/60'
        ]"
      >
        <div class="flex items-center justify-between mb-1">
          <div class="text-[9px] font-bold text-amber-300 uppercase tracking-wider">// ANTRIAN REVIEW</div>
          <div class="w-6 h-6 rounded-xs bg-[#1C2028] text-amber-300 flex items-center justify-center border border-amber-400/40">
            <Eye class="w-3 h-3" />
          </div>
        </div>
        <div class="text-2xl font-black text-amber-300 tracking-tight">{{ reviewCount }}</div>
        <div class="text-[9px] text-slate-500 mt-0.5">Menunggu verifikasi admin</div>
      </div>

      <div
        @click="setStatusFilter('COMPLETED')"
        :class="[
          'cad-crosshair-card p-3.5 border transition-colors cursor-pointer select-none',
          selectedQuickStatus === 'COMPLETED'
            ? 'bg-[#1C2028] border-[#10B981] shadow-xs'
            : 'bg-[#13171F] border-[#232A36] hover:border-[#10B981]/60'
        ]"
      >
        <div class="flex items-center justify-between mb-1">
          <div class="text-[9px] font-bold text-[#10B981] uppercase tracking-wider">// SELESAI / BA</div>
          <div class="w-6 h-6 rounded-xs bg-[#1C2028] text-[#10B981] flex items-center justify-center border border-[#10B981]/40">
            <CheckCircle2 class="w-3 h-3" />
          </div>
        </div>
        <div class="text-2xl font-black text-[#10B981] tracking-tight">{{ completedCount }}</div>
        <div class="text-[9px] text-slate-500 mt-0.5">BA Opname resmi terbit</div>
      </div>
    </div>

    <!-- Quick Status Tab Pills Filter + View Mode Switcher -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 font-mono">
      <div class="flex items-center gap-1.5 overflow-x-auto pb-1 custom-scrollbar text-xs font-bold">
        <button
          type="button"
          @click="setStatusFilter('ALL')"
          :class="[
            'px-3 py-1.5 rounded-xs transition-colors cursor-pointer whitespace-nowrap flex items-center gap-1.5 border text-xs uppercase',
            selectedQuickStatus === 'ALL'
              ? 'bg-[#FACC15] text-[#0B0C0E] border-[#FACC15] font-black'
              : 'bg-[#13171F] text-slate-400 border-[#232A36] hover:text-white hover:bg-[#1A202C]'
          ]"
        >
          <Layers class="w-3.5 h-3.5" />
          <span>SEMUA SPK</span>
          <span class="px-1 py-0.2 rounded-xs text-[9px] font-mono bg-black/20 text-current">
            {{ totalWorkOrdersCount }}
          </span>
        </button>

        <button
          type="button"
          @click="setStatusFilter('IN_PROGRESS')"
          :class="[
            'px-3 py-1.5 rounded-xs transition-colors cursor-pointer whitespace-nowrap flex items-center gap-1.5 border text-xs uppercase',
            selectedQuickStatus === 'IN_PROGRESS'
              ? 'bg-[#FACC15] text-[#0B0C0E] border-[#FACC15] font-black'
              : 'bg-[#13171F] text-slate-400 border-[#232A36] hover:text-white hover:bg-[#1A202C]'
          ]"
        >
          <Clock class="w-3.5 h-3.5" />
          <span>PROSES LAPANGAN</span>
          <span class="px-1 py-0.2 rounded-xs text-[9px] font-mono bg-black/20 text-current">
            {{ inProgressCount }}
          </span>
        </button>

        <button
          type="button"
          @click="setStatusFilter('REVIEW')"
          :class="[
            'px-3 py-1.5 rounded-xs transition-colors cursor-pointer whitespace-nowrap flex items-center gap-1.5 border text-xs uppercase',
            selectedQuickStatus === 'REVIEW'
              ? 'bg-[#FACC15] text-[#0B0C0E] border-[#FACC15] font-black'
              : 'bg-[#13171F] text-slate-400 border-[#232A36] hover:text-white hover:bg-[#1A202C]'
          ]"
        >
          <Eye class="w-3.5 h-3.5" />
          <span>MENUNGGU REVIEW</span>
          <span class="px-1 py-0.2 rounded-xs text-[9px] font-mono bg-black/20 text-current">
            {{ reviewCount }}
          </span>
        </button>

        <button
          type="button"
          @click="setStatusFilter('COMPLETED')"
          :class="[
            'px-3 py-1.5 rounded-xs transition-colors cursor-pointer whitespace-nowrap flex items-center gap-1.5 border text-xs uppercase',
            selectedQuickStatus === 'COMPLETED'
              ? 'bg-[#10B981] text-[#0B0C0E] border-[#10B981] font-black'
              : 'bg-[#13171F] text-slate-400 border-[#232A36] hover:text-white hover:bg-[#1A202C]'
          ]"
        >
          <CheckCircle2 class="w-3.5 h-3.5" />
          <span>SELESAI & BA</span>
          <span class="px-1 py-0.2 rounded-xs text-[9px] font-mono bg-black/20 text-current">
            {{ completedCount }}
          </span>
        </button>
      </div>

      <!-- View Switcher (Dense CAD Table vs Warehouse Cards) -->
      <div class="flex items-center gap-1 bg-[#13171F] p-0.5 rounded-xs border border-[#232A36] self-start sm:self-auto text-xs font-mono">
        <button
          type="button"
          @click="viewMode = 'table'"
          :class="[
            'px-2.5 py-1 rounded-xs transition-colors cursor-pointer flex items-center gap-1.5 text-[11px] uppercase',
            viewMode === 'table'
              ? 'bg-[#1C2028] text-[#FACC15] border-l-2 border-[#FACC15] font-bold'
              : 'text-slate-400 hover:text-white'
          ]"
          title="Tampilan Tabel Berorientasi Data Padat"
        >
          <Layers class="w-3.5 h-3.5" />
          <span>TABEL CAD</span>
        </button>
        <button
          type="button"
          @click="viewMode = 'cards'"
          :class="[
            'px-2.5 py-1 rounded-xs transition-colors cursor-pointer flex items-center gap-1.5 text-[11px] uppercase',
            viewMode === 'cards'
              ? 'bg-[#1C2028] text-[#FACC15] border-l-2 border-[#FACC15] font-bold'
              : 'text-slate-400 hover:text-white'
          ]"
          title="Tampilan Kartu Warehouse Ber-tag Kuning"
        >
          <Briefcase class="w-3.5 h-3.5" />
          <span>KARTU WAREHOUSE</span>
        </button>
      </div>
    </div>

    <!-- Archive Mode Indicator Banner -->
    <div
      v-if="activeTab === 'ARCHIVED'"
      class="p-3 bg-[#1C2028] border border-amber-400/60 rounded-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-amber-200 font-mono text-xs"
    >
      <div class="flex items-center gap-2.5">
        <Archive class="w-4 h-4 text-amber-400 shrink-0" />
        <div>
          <div class="font-bold text-amber-300 uppercase">MODE ARSIP SPK // SUPERUSER ONLY</div>
          <div class="text-[10px] text-slate-400">Menampilkan data arsip pekerjaan lampau yang telah ditutup dari antrian aktif.</div>
        </div>
      </div>
      <button
        type="button"
        @click="switchTab('ACTIVE')"
        class="px-3 py-1 bg-[#13171F] hover:bg-[#1A202C] text-[#FACC15] border border-[#FACC15] font-bold text-xs rounded-xs cursor-pointer active:scale-95 transition-colors self-start sm:self-auto uppercase"
      >
        &larr; KEMBALI KE SPK AKTIF
      </button>
    </div>

    <!-- Industrial CAD Terminal Search & Multi-Filter Bar -->
    <div class="cad-surface p-3.5 space-y-3 font-mono">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-2.5 text-xs">
        <!-- Terminal Style Input -->
        <div class="relative md:col-span-2">
          <div class="absolute left-3 top-2.5 text-[#FACC15] font-bold text-xs select-none">&gt;_</div>
          <input
            type="text"
            v-model="search"
            placeholder="CARI NO. SPK, TOKO, WILAYAH..."
            class="w-full pl-8 pr-3 py-2 bg-[#0E1117] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white placeholder:text-slate-500 font-mono text-xs focus:outline-none transition-colors"
          />
        </div>

        <!-- Date Range Period Filter -->
        <div>
          <select
            v-model="datePeriodFilter"
            class="w-full px-2.5 py-2 bg-[#0E1117] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-slate-200 font-mono text-xs focus:outline-none transition-colors cursor-pointer"
          >
            <option value="ALL">🗓️ SEMUA PERIODE WAKTU</option>
            <option value="THIS_MONTH">🗓️ BULAN INI ({{ currentMonthName.toUpperCase() }})</option>
            <option value="LAST_MONTH">🗓️ BULAN LALU</option>
            <option value="THIS_QUARTER">🗓️ KUARTAL INI</option>
            <option value="CUSTOM">🗓️ KUSTOM TANGGAL...</option>
          </select>
        </div>

        <div>
          <select
            v-model="vendorFilter"
            class="w-full px-2.5 py-2 bg-[#0E1117] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-slate-200 font-mono text-xs focus:outline-none transition-colors cursor-pointer"
          >
            <option value="">🏢 SEMUA CLIENT / MITRA</option>
            <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
          </select>
        </div>

        <div>
          <select
            v-model="areaFilter"
            class="w-full px-2.5 py-2 bg-[#0E1117] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-slate-200 font-mono text-xs focus:outline-none transition-colors cursor-pointer"
          >
            <option value="">📍 SEMUA WILAYAH AREA</option>
            <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.name }}</option>
          </select>
        </div>
      </div>

      <!-- Custom Date Inputs (Appears when CUSTOM period selected) -->
      <div v-if="datePeriodFilter === 'CUSTOM'" class="flex flex-wrap items-center gap-3 pt-2 border-t border-[#232A36] text-xs">
        <div class="flex items-center gap-2">
          <span class="text-slate-400 font-bold">DARI:</span>
          <input
            type="date"
            v-model="customStartDate"
            class="px-2.5 py-1.5 bg-[#0E1117] border border-[#232A36] text-white rounded-xs font-mono text-xs"
          />
        </div>
        <div class="flex items-center gap-2">
          <span class="text-slate-400 font-bold">SAMPAI:</span>
          <input
            type="date"
            v-model="customEndDate"
            class="px-2.5 py-1.5 bg-[#0E1117] border border-[#232A36] text-white rounded-xs font-mono text-xs"
          />
        </div>
        <button
          type="button"
          @click="resetFilters"
          class="text-[10px] font-bold text-[#FACC15] hover:underline cursor-pointer uppercase"
        >
          [ RESET TANGGAL ]
        </button>
      </div>

      <!-- Active Filter Summary Indicator -->
      <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-[#232A36]">
        <span>MENAMPILKAN <strong class="text-[#FACC15]">{{ filteredWorkOrders.length }}</strong> DARI TOTAL {{ allWorkOrders.length }} SPK.</span>
        <button
          v-if="hasActiveFilters"
          type="button"
          @click="resetFilters"
          class="text-[#EF4444] hover:underline font-bold flex items-center gap-1 cursor-pointer uppercase"
        >
          <span>✕ HAPUS SEMUA FILTER</span>
        </button>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- VIEW MODE A: HIGH-DENSITY INDUSTRIAL CAD TABLE VIEW      -->
    <!-- ========================================================= -->
    <div v-if="viewMode === 'table'" class="cad-surface overflow-hidden">
      <div class="w-full overflow-x-auto custom-scrollbar">
        <table class="w-full text-left text-xs table-auto border-collapse font-sans">
          <thead class="bg-[#0E1117] text-slate-400 font-mono font-bold border-b border-[#232A36] text-[10px] uppercase tracking-wider">
            <tr>
              <th class="py-3 px-4 w-[34%]">// NO. SPK & PEKERJAAN</th>
              <th class="py-3 px-4 w-[22%]">// CLIENT & PELAKSANA SGX</th>
              <th class="py-3 px-4 w-[14%]">// DEADLINE TARGET</th>
              <th class="py-3 px-4 w-[18%]">// PROGRES & STATUS</th>
              <th class="py-3 px-4 w-[12%] text-right">// AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#232A36] text-slate-300 bg-[#13171F]">
            <template v-if="loading">
              <tr>
                <td colspan="5" class="py-14 text-center text-slate-400 font-mono">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <RefreshCw class="w-6 h-6 animate-spin text-[#FACC15]" />
                    <span>MEMUAT DATA PEKERJAAN SPK...</span>
                  </div>
                </td>
              </tr>
            </template>

            <template v-else-if="filteredWorkOrders.length > 0">
              <tr
                v-for="wo in filteredWorkOrders"
                :key="wo.id"
                @click="$emit('select-work-order', wo.id)"
                class="hover:bg-[#1A202C] cursor-pointer transition-colors group"
              >
                <!-- Column 1: SPK & Job Information (with Yellow Warehouse Tag) -->
                <td class="py-3 px-4 align-middle">
                  <div class="flex items-center gap-2 mb-1">
                    <!-- Physical Yellow-Tagged SPK Badge -->
                    <span class="font-mono font-black text-[10px] bg-[#FACC15] text-[#0B0C0E] px-1.5 py-0.2 rounded-xs border border-[#0B0C0E] tracking-wider uppercase">
                      SPK: {{ wo.spk_number }}
                    </span>
                    <span class="text-[9px] font-mono font-bold text-slate-400 bg-[#0E1117] px-1.5 py-0.2 rounded-xs border border-[#232A36] uppercase">
                      {{ wo.jobType?.name || wo.job_type?.name || wo.job_type_name || 'PROJECT' }}
                    </span>
                    <!-- Hazard Stripes Tag for Revision/Issue (as in .cursorrules section 4) -->
                    <span
                      v-if="wo.status === 'REVISION'"
                      class="hazard-stripes text-[#0B0C0E] font-black font-mono text-[8px] px-1.5 py-0.2 rounded-xs border border-[#0B0C0E] uppercase"
                    >
                      KENDALA
                    </span>
                  </div>
                  <div class="text-white font-bold text-xs leading-snug group-hover:text-[#FACC15] transition-colors">
                    {{ wo.title }}
                  </div>
                  <div class="flex items-center gap-1.5 text-slate-400 text-[10px] font-mono mt-1">
                    <MapPin class="w-3 h-3 text-[#FACC15] shrink-0" />
                    <span class="text-slate-200">{{ wo.area?.name || wo.area_name || 'WILAYAH' }}</span>
                    <span class="text-slate-600">//</span>
                    <span class="text-slate-400 truncate max-w-xs">{{ wo.location_name }}</span>
                  </div>
                </td>

                <!-- Column 2: Client & Field Team PIC -->
                <td class="py-3 px-4 align-middle font-mono">
                  <div class="font-bold text-white text-xs flex items-center gap-1.5 truncate">
                    <Building class="w-3 h-3 text-[#FACC15] shrink-0" />
                    <span class="truncate">{{ wo.vendor?.name || wo.vendor_name || 'CLIENT / MITRA' }}</span>
                  </div>
                  <div class="mt-1 flex items-center gap-1 text-[10px] text-slate-400">
                    <User class="w-3 h-3 text-slate-500 shrink-0" />
                    <div v-if="wo.pic?.name || wo.pic_name" class="truncate">
                      <span class="text-slate-300 font-bold">{{ wo.pic?.name || wo.pic_name }}</span>
                    </div>
                    <span v-else class="text-amber-400 italic text-[9px]">[ BELUM DITUGASKAN ]</span>
                  </div>
                </td>

                <!-- Column 3: Deadline Target -->
                <td class="py-3 px-4 align-middle font-mono">
                  <div class="flex items-center gap-1.5 text-slate-200 font-bold text-xs">
                    <Calendar class="w-3 h-3 text-[#FACC15] shrink-0" />
                    <span>{{ wo.deadline }}</span>
                  </div>
                  <div class="text-[9px] text-slate-500 mt-0.5 uppercase">
                    TARGET SELESAI
                  </div>
                </td>

                <!-- Column 4: Progress & Status -->
                <td class="py-3 px-4 align-middle">
                  <div class="space-y-1">
                    <div class="flex items-center justify-between gap-1 flex-wrap">
                      <div class="flex items-center gap-1">
                        <StatusBadge :status="wo.status" />
                        <span
                          v-if="wo.is_archived"
                          class="px-1 py-0.2 rounded-xs text-[8px] font-mono font-black uppercase bg-[#FACC15] text-[#0B0C0E]"
                        >
                          ARSIP
                        </span>
                      </div>
                      <span class="font-mono font-bold text-[10px] text-[#FACC15]">{{ wo.progress_percent }}%</span>
                    </div>
                    <div class="w-full bg-[#0B0C0E] h-1.5 overflow-hidden border border-[#232A36]">
                      <div
                        class="h-full bg-[#FACC15] transition-all duration-300"
                        :style="{ width: `${wo.progress_percent}%` }"
                      />
                    </div>
                  </div>
                </td>

                <!-- Column 5: Actions -->
                <td class="py-3 px-4 align-middle text-right font-mono">
                  <div class="flex items-center justify-end gap-1.5">
                    <button
                      v-if="isSupervisor"
                      @click.stop="openEditSpk(wo.id)"
                      class="p-1.5 bg-[#0E1117] hover:bg-[#1A202C] text-slate-300 hover:text-[#FACC15] rounded-xs transition-colors border border-[#232A36] hover:border-[#FACC15] cursor-pointer"
                      title="Edit Data SPK (Supervisor Only)"
                    >
                      <Pencil class="w-3 h-3" />
                    </button>
                    <button
                      @click.stop="$emit('select-work-order', wo.id)"
                      class="px-2.5 py-1 bg-[#13171F] group-hover:bg-[#FACC15] group-hover:text-[#0B0C0E] rounded-xs text-slate-200 font-bold transition-colors inline-flex items-center gap-1 border border-[#232A36] group-hover:border-[#FACC15] text-xs uppercase cursor-pointer"
                    >
                      <Eye class="w-3 h-3" />
                      <span>DETAIL</span>
                    </button>
                  </div>
                </td>
              </tr>
            </template>

            <template v-else>
              <tr>
                <td colspan="5" class="py-14 text-center text-slate-400 font-mono">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <Archive v-if="activeTab === 'ARCHIVED'" class="w-8 h-8 opacity-30 text-[#FACC15]" />
                    <FileText v-else class="w-8 h-8 opacity-30 text-slate-500" />
                    <p class="text-xs uppercase">
                      {{ activeTab === 'ARCHIVED' ? 'BELUM ADA PEKERJAAN SPK YANG DIARSIPKAN.' : 'TIDAK ADA DATA SPK YANG COCOK DENGAN FILTER.' }}
                    </p>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- VIEW MODE B: YELLOW-TAGGED WAREHOUSE WORK ORDER CARDS     -->
    <!-- (Strictly complying with .cursorrules Section 4)          -->
    <!-- ========================================================= -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 font-mono">
      <div
        v-for="wo in filteredWorkOrders"
        :key="wo.id"
        @click="$emit('select-work-order', wo.id)"
        :class="[
          'cad-crosshair-card p-4 transition-colors cursor-pointer group relative flex flex-col justify-between space-y-3 bg-[#13171F] border',
          wo.status === 'REVISION'
            ? 'border-[#EF4444] hover:border-[#EF4444]'
            : 'border-[#232A36] hover:border-[#FACC15]'
        ]"
      >
        <!-- Top Warehouse Yellow Header Strip -->
        <div class="flex items-start justify-between gap-2 border-b border-[#232A36] pb-2.5">
          <div class="flex flex-col gap-1">
            <div class="flex items-center gap-1.5 flex-wrap">
              <!-- Physical Yellow Tagged Badge -->
              <span class="bg-[#FACC15] text-[#0B0C0E] font-black text-[10px] px-2 py-0.5 rounded-xs border border-[#0B0C0E] tracking-wider uppercase">
                SPK: {{ wo.spk_number }}
              </span>
              <span class="text-[9px] font-bold text-slate-400 bg-[#0E1117] px-1.5 py-0.5 rounded-xs border border-[#232A36] uppercase">
                {{ wo.jobType?.name || wo.job_type?.name || wo.job_type_name || 'PROJECT' }}
              </span>
            </div>
            <div class="text-[9px] text-slate-500 uppercase">
              REGISTERED // SGX BENCOOLEN
            </div>
          </div>

          <div class="flex items-center gap-1">
            <!-- Diagonal Hazard Stripe Stamp for Revision / Field Blocker -->
            <span
              v-if="wo.status === 'REVISION'"
              class="hazard-stripes text-[#0B0C0E] font-black text-[9px] px-1.5 py-0.5 rounded-xs border border-[#0B0C0E] uppercase"
              title="Tiket Kendala Lapangan Aktif"
            >
              KENDALA LAPANGAN
            </span>
            <StatusBadge :status="wo.status" />
          </div>
        </div>

        <!-- Middle: Title & Store Location -->
        <div class="space-y-1.5">
          <h4 class="text-white font-bold text-sm leading-snug group-hover:text-[#FACC15] transition-colors font-sans">
            {{ wo.title }}
          </h4>
          <div class="flex items-center gap-1.5 text-slate-400 text-[10px]">
            <MapPin class="w-3 h-3 text-[#FACC15] shrink-0" />
            <span class="text-slate-200 font-bold">{{ wo.area?.name || wo.area_name || 'WILAYAH' }}</span>
            <span class="text-slate-600">//</span>
            <span class="truncate text-slate-400">{{ wo.location_name }}</span>
          </div>
        </div>

        <!-- Technical Metadata Grid (Client, PIC, Deadline) -->
        <div class="grid grid-cols-2 gap-2 p-2 bg-[#0E1117] border border-[#232A36] rounded-xs text-[10px]">
          <div>
            <span class="text-slate-500 text-[9px] uppercase">// CLIENT</span>
            <div class="text-slate-200 font-bold truncate">{{ wo.vendor?.name || wo.vendor_name || '-' }}</div>
          </div>
          <div>
            <span class="text-slate-500 text-[9px] uppercase">// TEKNISI PIC</span>
            <div class="text-slate-200 font-bold truncate">{{ wo.pic?.name || wo.pic_name || 'BELUM DITUGASKAN' }}</div>
          </div>
          <div>
            <span class="text-slate-500 text-[9px] uppercase">// DEADLINE</span>
            <div class="text-[#FACC15] font-bold">{{ wo.deadline || '-' }}</div>
          </div>
          <div>
            <span class="text-slate-500 text-[9px] uppercase">// PROGRES</span>
            <div class="text-[#FACC15] font-bold">{{ wo.progress_percent }}% SELESAI</div>
          </div>
        </div>

        <!-- Bottom: Progress Bar & Actions -->
        <div class="pt-1 space-y-2">
          <div class="w-full bg-[#0B0C0E] h-1.5 overflow-hidden border border-[#232A36]">
            <div class="bg-[#FACC15] h-full" :style="{ width: `${wo.progress_percent}%` }" />
          </div>

          <div class="flex items-center justify-between gap-2 pt-1">
            <span class="text-[9px] text-slate-500">ID: #{{ wo.id }}</span>
            <div class="flex items-center gap-1.5">
              <button
                v-if="isSupervisor"
                @click.stop="openEditSpk(wo.id)"
                class="p-1.5 bg-[#0E1117] hover:bg-[#1A202C] text-slate-300 hover:text-[#FACC15] rounded-xs border border-[#232A36] hover:border-[#FACC15]"
                title="Edit SPK"
              >
                <Pencil class="w-3 h-3" />
              </button>
              <button
                @click.stop="$emit('select-work-order', wo.id)"
                class="px-3 py-1 bg-[#FACC15] text-[#0B0C0E] hover:bg-[#FFE033] font-bold text-xs rounded-xs flex items-center gap-1 uppercase"
              >
                <Eye class="w-3 h-3 stroke-[2.5]" />
                <span>LIHAT DETAIL</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Supervisor Edit SPK Modal -->
    <WorkOrderEditModal
      :isOpen="editModalOpen"
      :workOrderId="editingWorkOrderId"
      @close="editModalOpen = false"
      @updated="loadData"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../../services/api';
import { useAuth } from '../../composables/useAuth';
import StatusBadge from '../../components/StatusBadge.vue';
import WorkOrderEditModal from './WorkOrderEditModal.vue';
import {
  Plus,
  RefreshCw,
  Eye,
  FileText,
  MapPin,
  Building,
  User,
  Calendar,
  Layers,
  Clock,
  CheckCircle2,
  Pencil,
  Archive,
  FileSpreadsheet,
  Briefcase
} from 'lucide-vue-next';

defineEmits(['open-create', 'select-work-order']);

const auth = useAuth();
const isSupervisor = computed(() => ['SUPERUSER', 'SUPERVISOR'].includes(auth.state.user?.role));
const isSuperuser = computed(() => auth.state.user?.role === 'SUPERUSER');
const editModalOpen = ref(false);
const editingWorkOrderId = ref(null);
const activeTab = ref('ACTIVE'); // 'ACTIVE' | 'ARCHIVED'
const viewMode = ref('table'); // 'table' | 'cards'

const allWorkOrders = ref([]);
const workOrders = computed(() => allWorkOrders.value);
const vendors = ref([]);
const areas = ref([]);
const loading = ref(true);

const search = ref('');
const selectedQuickStatus = ref('ALL'); // 'ALL' | 'IN_PROGRESS' | 'REVIEW' | 'COMPLETED'
const vendorFilter = ref('');
const areaFilter = ref('');
const datePeriodFilter = ref('ALL');
const customStartDate = ref('');
const customEndDate = ref('');

const currentMonthName = computed(() => {
  return new Date().toLocaleString('id-ID', { month: 'long', year: 'numeric' });
});

const totalWorkOrdersCount = computed(() => allWorkOrders.value.length);
const inProgressCount = computed(() => {
  return allWorkOrders.value.filter(w => ['ASSIGNED', 'CHECKED_IN', 'IN_PROGRESS'].includes(w.status)).length;
});
const reviewCount = computed(() => {
  return allWorkOrders.value.filter(w => ['SUBMITTED', 'UNDER_REVIEW', 'REVISION'].includes(w.status)).length;
});
const completedCount = computed(() => {
  return allWorkOrders.value.filter(w => ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(w.status)).length;
});

function setStatusFilter(status) {
  selectedQuickStatus.value = status;
}

const hasActiveFilters = computed(() => {
  return search.value || selectedQuickStatus.value !== 'ALL' || vendorFilter.value || areaFilter.value || datePeriodFilter.value !== 'ALL' || customStartDate.value || customEndDate.value;
});

function resetFilters() {
  search.value = '';
  selectedQuickStatus.value = 'ALL';
  vendorFilter.value = '';
  areaFilter.value = '';
  datePeriodFilter.value = 'ALL';
  customStartDate.value = '';
  customEndDate.value = '';
}

const filteredWorkOrders = computed(() => {
  let list = allWorkOrders.value;

  // 1. Quick Status Tab Filtering
  if (selectedQuickStatus.value === 'IN_PROGRESS') {
    list = list.filter(w => ['ASSIGNED', 'CHECKED_IN', 'IN_PROGRESS'].includes(w.status));
  } else if (selectedQuickStatus.value === 'REVIEW') {
    list = list.filter(w => ['SUBMITTED', 'UNDER_REVIEW', 'REVISION'].includes(w.status));
  } else if (selectedQuickStatus.value === 'COMPLETED') {
    list = list.filter(w => ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(w.status));
  }

  // 2. Vendor Filtering
  if (vendorFilter.value) {
    list = list.filter(w => String(w.vendor_id) === String(vendorFilter.value));
  }

  // 3. Area Filtering
  if (areaFilter.value) {
    list = list.filter(w => String(w.area_id) === String(areaFilter.value));
  }

  // 4. Date Period Filtering
  if (datePeriodFilter.value !== 'ALL') {
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth();

    if (datePeriodFilter.value === 'THIS_MONTH') {
      list = list.filter(w => {
        const d = new Date(w.start_date || w.created_at);
        return d.getFullYear() === currentYear && d.getMonth() === currentMonth;
      });
    } else if (datePeriodFilter.value === 'LAST_MONTH') {
      const lastMonth = currentMonth === 0 ? 11 : currentMonth - 1;
      const lastYear = currentMonth === 0 ? currentYear - 1 : currentYear;
      list = list.filter(w => {
        const d = new Date(w.start_date || w.created_at);
        return d.getFullYear() === lastYear && d.getMonth() === lastMonth;
      });
    } else if (datePeriodFilter.value === 'THIS_QUARTER') {
      const currentQuarter = Math.floor(currentMonth / 3);
      list = list.filter(w => {
        const d = new Date(w.start_date || w.created_at);
        return d.getFullYear() === currentYear && Math.floor(d.getMonth() / 3) === currentQuarter;
      });
    } else if (datePeriodFilter.value === 'CUSTOM') {
      if (customStartDate.value) {
        list = list.filter(w => (w.start_date || w.created_at) >= customStartDate.value);
      }
      if (customEndDate.value) {
        list = list.filter(w => (w.start_date || w.created_at) <= customEndDate.value);
      }
    }
  }

  // 5. Global Search Filtering
  if (search.value) {
    const q = search.value.toLowerCase().trim();
    list = list.filter(w => {
      const matchSpk = w.spk_number?.toLowerCase().includes(q);
      const matchTitle = w.title?.toLowerCase().includes(q);
      const matchLoc = w.location_name?.toLowerCase().includes(q);
      const matchVendor = (w.vendor?.name || w.vendor_name || '').toLowerCase().includes(q);
      const matchPic = (w.pic?.name || w.pic_name || '').toLowerCase().includes(q);
      return matchSpk || matchTitle || matchLoc || matchVendor || matchPic;
    });
  }

  return list;
});

function exportCSV() {
  const data = filteredWorkOrders.value;
  if (!data || data.length === 0) return;

  const headers = [
    'No. SPK',
    'Judul Pekerjaan',
    'Klien / Perusahaan',
    'Wilayah / Area',
    'Lokasi / Cabang Toko',
    'PIC Lapangan',
    'Tanggal Mulai',
    'Target Selesai',
    'Progres (%)',
    'Status SPK'
  ];

  const rows = data.map(wo => [
    `"${(wo.spk_number || '').replace(/"/g, '""')}"`,
    `"${(wo.title || '').replace(/"/g, '""')}"`,
    `"${(wo.vendor?.name || wo.vendor_name || '').replace(/"/g, '""')}"`,
    `"${(wo.area?.name || wo.area_name || '').replace(/"/g, '""')}"`,
    `"${(wo.location_name || '').replace(/"/g, '""')}"`,
    `"${(wo.pic?.name || wo.pic_name || '').replace(/"/g, '""')}"`,
    `"${wo.start_date || ''}"`,
    `"${wo.deadline || ''}"`,
    `"${wo.progress_percent || 0}%"`,
    `"${wo.status || ''}"`
  ]);

  const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `Rekapitulasi_SPK_Pemesanan_SGX_${new Date().toISOString().slice(0, 10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function switchTab(tab) {
  activeTab.value = tab;
  loadData();
}

function openEditSpk(id) {
  editingWorkOrderId.value = id;
  editModalOpen.value = true;
}

async function loadData() {
  loading.value = true;
  try {
    const params = {};
    if (activeTab.value === 'ARCHIVED') {
      params.archived = 'true';
    }

    const [woRes, vRes, aRes] = await Promise.all([
      api.getWorkOrders(params),
      api.getVendors(),
      api.getAreas()
    ]);
    allWorkOrders.value = woRes.data || [];
    vendors.value = vRes.data || [];
    areas.value = aRes.data || [];
  } catch (err) {
    console.error('Failed to load work orders:', err);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadData();
});
</script>
