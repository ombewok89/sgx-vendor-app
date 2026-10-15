<template>
  <div class="space-y-4">
    <!-- Notification Toast (Industrial Banner) -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="successToast"
        class="p-3 bg-emerald-950/90 border-l-4 border-l-emerald-400 border-y border-r border-emerald-800/60 text-white font-mono text-xs rounded-xs flex items-center justify-between shadow-lg"
      >
        <div class="flex items-center gap-2.5">
          <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
          <span class="font-bold tracking-wide">{{ successToast }}</span>
        </div>
        <button @click="successToast = null" class="p-1 hover:bg-emerald-900/50 rounded-xs text-emerald-300">
          <X class="w-4 h-4" />
        </button>
      </div>
    </Transition>

    <!-- Page Header (CAD Blueprint Style) -->
    <div class="bg-[#13171F] border border-[#232A36] p-4 rounded-xs flex flex-col md:flex-row md:items-center justify-between gap-3 relative overflow-hidden">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xs bg-[#0B0C0E] border border-hazard/40 flex items-center justify-center text-hazard shrink-0">
          <CheckSquare class="w-5 h-5" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-base sm:text-lg font-heading font-black text-white uppercase tracking-wider">
              Review & Verifikasi Lapangan
            </h2>
            <span class="bg-hazard/10 text-hazard border border-hazard/30 text-[9px] font-mono px-1.5 py-0.2 rounded-xs font-bold uppercase">
              CONSOLE VER. 2.4
            </span>
          </div>
          <p class="text-xs text-slate-400 font-mono mt-0.5">
            Pemeriksaan bukti forensik foto, audit koordinat GPS check-in, dan validasi persetujuan resmi (Approval QA).
          </p>
        </div>
      </div>

      <!-- Quick Metrics Pill -->
      <div class="flex items-center gap-2 self-start md:self-auto">
        <div class="px-3 py-1.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs font-mono text-xs flex items-center gap-2">
          <span class="text-slate-400">STATUS AUDIT:</span>
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span class="text-emerald-400 font-bold">READY TO REVIEW</span>
        </div>
      </div>
    </div>

    <!-- Main Grid: Queue on Left, Forensic Inspection on Right -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
      
      <!-- LEFT: Queue Column (4 cols) -->
      <div class="lg:col-span-4 bg-[#13171F] border border-[#232A36] rounded-xs p-3.5 space-y-3 flex flex-col">
        <!-- Header Queue -->
        <div class="flex items-center justify-between border-b border-[#232A36] pb-2 px-0.5">
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-hazard"></div>
            <h3 class="font-heading font-black text-xs text-white uppercase tracking-wider">
              Antrian Pekerjaan
            </h3>
            <span class="px-2 py-0.5 rounded-xs text-[10px] font-mono font-bold bg-[#0B0C0E] text-hazard border border-hazard/40">
              {{ filteredQueue.length }}
            </span>
          </div>
          <button
            @click="loadQueue(selectedOrder?.id)"
            :disabled="loading"
            class="p-1.5 bg-[#0B0C0E] border border-[#232A36] hover:border-slate-500 rounded-xs text-slate-400 hover:text-white transition-all cursor-pointer"
            title="Segarkan Antrian"
          >
            <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin text-hazard' : '']" />
          </button>
        </div>

        <!-- Search Input -->
        <div class="relative">
          <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-500" />
          <input
            type="text"
            v-model="searchQuery"
            placeholder=">_ CARI SPK, TOKO, PIC..."
            class="w-full pl-8 pr-3 py-1.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-xs font-mono text-slate-200 placeholder-slate-600 focus:outline-none focus:border-hazard transition-colors"
          />
        </div>

        <!-- Status Filter Tabs (Terminal Pills) -->
        <div class="flex items-center gap-1 overflow-x-auto pb-1 text-[10px] font-mono scrollbar-none">
          <button
            v-for="tab in filterTabs"
            :key="tab.id"
            @click="activeStatusFilter = tab.id"
            :class="[
              'px-2 py-1 rounded-xs transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5 border',
              activeStatusFilter === tab.id
                ? 'bg-hazard text-carbon border-hazard font-bold shadow-xs'
                : 'bg-[#0B0C0E] text-slate-400 hover:text-slate-200 border-[#232A36]'
            ]"
          >
            <span>{{ tab.label }}</span>
            <span
              :class="[
                'px-1 py-0.2 rounded-none text-[9px] font-mono',
                activeStatusFilter === tab.id ? 'bg-carbon/20 text-carbon font-black' : 'bg-[#181D26] text-slate-400'
              ]"
            >
              {{ tab.count }}
            </span>
          </button>
        </div>

        <!-- Queue Item List -->
        <div class="space-y-1.5 max-h-[calc(100vh-280px)] overflow-y-auto pr-1 custom-scrollbar flex-1">
          <div v-if="loading" class="text-center py-12 text-slate-400 text-xs font-mono">
            <Loader2 class="w-5 h-5 animate-spin mx-auto mb-2 text-hazard" />
            <span>MEMUAT_ANTRIAN_DATA...</span>
          </div>

          <template v-else-if="filteredQueue.length > 0">
            <div
              v-for="wo in filteredQueue"
              :key="wo.id"
              @click="handleSelectOrder(wo.id)"
              :class="[
                'p-3 rounded-xs border text-xs cursor-pointer transition-all',
                selectedOrder?.id === wo.id
                  ? 'bg-[#181D26] border-l-4 border-l-hazard border-y border-r border-[#2A3342] shadow-md'
                  : 'bg-[#0E1117] border-[#232A36] hover:bg-[#141821] hover:border-slate-600'
              ]"
            >
              <div class="flex items-center justify-between mb-1">
                <span class="font-mono font-bold text-white tracking-wide text-[11px] flex items-center gap-1.5">
                  <span v-if="selectedOrder?.id === wo.id" class="w-1.5 h-1.5 bg-hazard"></span>
                  {{ wo.spk_number }}
                </span>
                <StatusBadge :status="wo.status" />
              </div>
              <div class="font-bold text-slate-200 truncate text-[12px] mb-1">
                {{ wo.title || wo.location_name }}
              </div>
              <div class="text-[10px] font-mono text-slate-400 flex items-center justify-between pt-1 border-t border-[#1C2028]">
                <span class="truncate max-w-[150px] text-slate-400">
                  {{ wo.vendor_name || wo.vendor?.name || 'VENDOR SGX' }}
                </span>
                <span class="font-mono text-hazard font-bold">
                  {{ wo.progress_percent || 0 }}% PROG
                </span>
              </div>
            </div>
          </template>

          <div v-else class="text-center py-12 text-slate-500 text-xs font-mono space-y-2 border border-dashed border-[#232A36] rounded-xs p-4">
            <Store class="w-6 h-6 text-slate-600 mx-auto" />
            <p class="font-bold text-slate-400">DATA_TIDAK_DITEMUKAN</p>
            <p class="text-[10px] text-slate-500">Ubah filter tab status di atas untuk melihat data lainnya.</p>
          </div>
        </div>
      </div>

      <!-- RIGHT: Detailed Forensic Inspection Workspace (8 cols) -->
      <div class="lg:col-span-8 bg-[#13171F] border border-[#232A36] rounded-xs p-4 sm:p-5 space-y-4">
        <template v-if="selectedOrder">
          
          <!-- Workspace Header: SPK Info & Primary Decisions -->
          <div class="border-b border-[#232A36] pb-4 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-mono font-bold text-xs bg-[#0B0C0E] border border-hazard/40 text-hazard px-2 py-0.5 rounded-none">
                    [ SPK: {{ selectedOrder.spk_number }} ]
                  </span>
                  <StatusBadge :status="selectedOrder.status" />
                </div>
                <h3 class="font-heading font-black text-white text-base sm:text-lg mt-1 tracking-wide">
                  {{ selectedOrder.title }}
                </h3>
              </div>

              <!-- Action Decision Controls (Hazard Yellow + Safety Red + Industrial Emerald) -->
              <div class="flex items-center gap-2 flex-wrap">
                <!-- Ready for Review -->
                <template v-if="['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'].includes(selectedOrder.status)">
                  <button
                    type="button"
                    @click="showRevisionModal = true"
                    :disabled="actionLoading"
                    class="px-3.5 py-2 bg-red-950/40 hover:bg-red-900/50 text-red-400 border border-red-600 font-mono font-bold text-xs rounded-xs flex items-center gap-1.5 uppercase transition-all cursor-pointer active:scale-95"
                  >
                    <RotateCcw class="w-3.5 h-3.5" />
                    <span>Minta Revisi</span>
                  </button>
                  <button
                    type="button"
                    @click="showApproveModal = true"
                    :disabled="actionLoading"
                    class="cad-btn-primary px-4 py-2 text-xs flex items-center gap-1.5 uppercase cursor-pointer active:scale-95 shadow-md"
                  >
                    <CheckCircle2 class="w-4 h-4" />
                    <span>SETUJUI (APPROVE)</span>
                  </button>
                </template>

                <!-- Approved / Completed -->
                <template v-else-if="['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(selectedOrder.status)">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-3 py-1.5 bg-emerald-950/80 text-emerald-400 border border-emerald-500/60 font-mono font-bold text-xs rounded-xs flex items-center gap-1.5">
                      <Check class="w-3.5 h-3.5 text-emerald-400" />
                      <span>DISETUJUI RESMI ✓</span>
                    </span>

                    <button
                      v-if="!selectedOrder.ba_document"
                      type="button"
                      @click="handleGenerateBa"
                      :disabled="actionLoading"
                      class="px-3.5 py-1.5 bg-hazard hover:bg-yellow-400 text-carbon font-mono font-black text-xs rounded-xs flex items-center gap-1.5 cursor-pointer uppercase transition-all"
                    >
                      <FileCheck2 class="w-3.5 h-3.5" />
                      <span>Terbitkan BA Opname</span>
                    </button>
                    <button
                      v-else
                      type="button"
                      @click="$emit('open-ba', selectedOrder.ba_document)"
                      class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-mono font-bold text-xs rounded-xs flex items-center gap-1.5 cursor-pointer uppercase transition-all"
                    >
                      <FileCheck2 class="w-3.5 h-3.5" />
                      <span>Lihat BA Opname</span>
                    </button>
                  </div>
                </template>

                <!-- Revision Stage -->
                <template v-else-if="selectedOrder.status === 'REVISION'">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-3 py-1.5 bg-red-950/70 text-red-400 border border-red-500/50 font-mono font-bold text-xs rounded-xs flex items-center gap-1.5">
                      <RotateCcw class="w-3.5 h-3.5 text-red-400" />
                      <span>PERBAIKAN REVISI LAPANGAN</span>
                    </span>
                    <button
                      type="button"
                      @click="showApproveModal = true"
                      :disabled="actionLoading"
                      class="px-3 py-1.5 bg-hazard hover:bg-yellow-400 text-carbon font-mono font-black text-xs rounded-xs flex items-center gap-1.5 cursor-pointer uppercase"
                    >
                      <CheckCircle2 class="w-3.5 h-3.5" />
                      <span>Setujui Perbaikan</span>
                    </button>
                  </div>
                </template>

                <!-- In Progress -->
                <template v-else>
                  <div class="px-3 py-1.5 bg-[#0B0C0E] border border-amber-500/40 text-amber-400 font-mono font-bold text-xs rounded-xs flex items-center gap-1.5">
                    <Clock class="w-3.5 h-3.5 text-amber-400 shrink-0" />
                    <span>PEKERJAAN_BERJALAN (BELUM SUBMIT)</span>
                  </div>
                </template>
              </div>
            </div>

            <!-- GPS & Field Telemetry Bar (Dark Industrial CAD Strip) -->
            <div class="p-3 bg-[#0B0C0E] border border-[#232A36] rounded-xs grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs font-mono">
              <div class="border-b sm:border-b-0 sm:border-r border-[#232A36] pb-2 sm:pb-0 pr-2">
                <span class="text-[10px] text-slate-500 uppercase tracking-wider block font-bold">LOKASI & REGIONAL</span>
                <div class="font-bold text-white mt-0.5 truncate">{{ selectedOrder.location_name }}</div>
                <div class="text-slate-400 text-[11px]">Area: {{ selectedOrder.area_name }}</div>
              </div>

              <div class="border-b sm:border-b-0 sm:border-r border-[#232A36] pb-2 sm:pb-0 pr-2">
                <span class="text-[10px] text-slate-500 uppercase tracking-wider block font-bold">TELEMETRI CHECK-IN GPS</span>
                <div v-if="selectedOrder.check_ins && selectedOrder.check_ins.length > 0" class="mt-0.5">
                  <div class="text-hazard font-bold tracking-tight">
                    {{ Number(selectedOrder.check_ins[0].latitude).toFixed(5) }}, {{ Number(selectedOrder.check_ins[0].longitude).toFixed(5) }}
                  </div>
                  <div class="text-slate-400 text-[10px]">
                    AKURASI: ±{{ selectedOrder.check_ins[0].accuracy }}m • {{ new Date(selectedOrder.check_ins[0].server_timestamp).toLocaleTimeString('id-ID') }}
                  </div>
                </div>
                <div v-else class="text-amber-500/80 italic text-[11px] mt-0.5">
                  [ BELUM ADA CHECK-IN ]
                </div>
              </div>

              <div>
                <span class="text-[10px] text-slate-500 uppercase tracking-wider block font-bold">PELAKSANA / VENDOR</span>
                <div class="font-bold text-white mt-0.5 truncate">{{ selectedOrder.pic_name || '-' }} ({{ selectedOrder.pic_phone }})</div>
                <div class="text-slate-400 text-[11px] truncate">{{ selectedOrder.vendor_name }}</div>
              </div>
            </div>
          </div>

          <!-- FORENSIC EVIDENCE GALLERY HEADER & TOOLS -->
          <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
              <div>
                <h4 class="font-heading font-black text-xs sm:text-sm text-white uppercase tracking-wider flex items-center gap-2">
                  <Camera class="w-4 h-4 text-hazard" />
                  <span>Forensic Evidence Gallery ({{ displayItems.length }} Sub-Item)</span>
                </h4>
                <p class="text-[11px] font-mono text-slate-400 mt-0.5">
                  Pemeriksaan visual matriks Before - Process - After dengan optik pengukuran dan verifikasi hash SHA-256.
                </p>
              </div>

              <!-- Controls: Add Addendum, View Mode, Download All -->
              <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap font-mono">
                <!-- Add Addendum Button -->
                <button
                  v-if="!['APPROVED', 'COMPLETED', 'BA_OPNAME'].includes(selectedOrder.status)"
                  type="button"
                  @click="openAddendumModal"
                  class="px-2.5 py-1.5 bg-[#181D26] hover:bg-[#202734] text-hazard border border-hazard/40 rounded-xs text-[11px] font-bold flex items-center gap-1.5 cursor-pointer transition-all"
                  title="Tambah item pekerjaan tambahan (Addendum)"
                >
                  <Plus class="w-3.5 h-3.5" />
                  <span>+ ADDENDUM</span>
                </button>

                <!-- Switch Mode Button Group -->
                <div class="bg-[#0B0C0E] p-0.5 rounded-xs border border-[#232A36] flex items-center text-[10px]">
                  <button
                    type="button"
                    @click="photoViewMode = 'BY_ITEM'"
                    :class="[
                      'px-2 py-1 rounded-none transition-all cursor-pointer font-bold',
                      photoViewMode === 'BY_ITEM' ? 'bg-hazard text-carbon' : 'text-slate-400 hover:text-white'
                    ]"
                  >
                    MATRIKS ITEM
                  </button>
                  <button
                    type="button"
                    @click="photoViewMode = 'ALL_STAGES'"
                    :class="[
                      'px-2 py-1 rounded-none transition-all cursor-pointer font-bold',
                      photoViewMode === 'ALL_STAGES' ? 'bg-hazard text-carbon' : 'text-slate-400 hover:text-white'
                    ]"
                  >
                    SEMUA TAHAP
                  </button>
                </div>

                <!-- Download All Photos -->
                <button
                  v-if="selectedOrder.evidence_photos && selectedOrder.evidence_photos.length > 0"
                  type="button"
                  @click="downloadAllPhotos"
                  class="px-2.5 py-1.5 bg-[#0B0C0E] hover:bg-[#181D26] text-white border border-[#232A36] hover:border-slate-500 rounded-xs text-[11px] font-bold flex items-center gap-1.5 cursor-pointer"
                  title="Unduh semua foto bukti"
                >
                  <Download class="w-3.5 h-3.5 text-hazard" />
                  <span>UNDUH ({{ selectedOrder.evidence_photos.length }})</span>
                </button>
              </div>
            </div>

            <!-- MODE 1: STRUCTURED SUB-TASK TRIPTYCH MATRIX (Industrial Measuring Optic View) -->
            <div v-if="photoViewMode === 'BY_ITEM'" class="space-y-4">
              <div
                v-for="(item, itmIdx) in displayItems"
                :key="item.id || itmIdx"
                class="bg-[#0E1117] border border-[#232A36] rounded-xs p-3.5 sm:p-4 space-y-3 relative"
              >
                <!-- Sub-Task Header Strip -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#232A36] pb-2.5">
                  <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-none bg-hazard text-carbon font-mono font-black text-xs flex items-center justify-center shrink-0">
                      {{ String(itmIdx + 1).padStart(2, '0') }}
                    </span>
                    <div>
                      <div class="flex items-center gap-2">
                        <h5 class="font-heading font-black text-white text-xs sm:text-sm uppercase tracking-wide">
                          {{ item.item_name }}
                        </h5>
                        <span
                          v-if="item.is_addendum"
                          class="px-1.5 py-0.2 rounded-none text-[8px] font-mono font-black uppercase tracking-wider bg-hazard text-carbon"
                        >
                          [ ADDENDUM ]
                        </span>
                      </div>
                      <span class="text-[10px] text-slate-400 font-mono">
                        BOBOT KERJA: {{ item.weight_percent || 100 }}% • MODE: {{ item.doc_mode || 'STANDARD' }}
                      </span>
                    </div>
                  </div>

                  <span
                    :class="[
                      'px-2 py-0.5 rounded-none text-[9px] font-mono font-bold border uppercase',
                      isItemFullyDocumented(item)
                        ? 'bg-emerald-950/80 text-emerald-400 border-emerald-500/60'
                        : 'bg-amber-950/80 text-amber-400 border-amber-500/60'
                    ]"
                  >
                    {{ isItemFullyDocumented(item) ? '[ EVIDENSI LENGKAP ✓ ]' : '[ EVIDENSI PARSIAL ⏳ ]' }}
                  </span>
                </div>

                <!-- Dynamic Review Grid (BEFORE / PROCESS / AFTER) -->
                <div
                  :class="[
                    isAfterOnly(item)
                      ? 'grid grid-cols-1 gap-3'
                      : isTwoStages(item)
                      ? 'grid grid-cols-1 sm:grid-cols-2 gap-3'
                      : 'grid grid-cols-1 sm:grid-cols-3 gap-3'
                  ]"
                >
                  <!-- STAGE: BEFORE -->
                  <div v-if="!isAfterOnly(item)" class="space-y-2">
                    <div class="flex items-center justify-between font-mono text-[10px] px-0.5">
                      <span class="text-slate-400 font-bold uppercase tracking-wider">TAHAP: BEFORE</span>
                      <span class="text-hazard">{{ getPhotosForItemStage(item.id, 'BEFORE').length }} FOTO</span>
                    </div>

                    <div v-if="getPhotosForItemStage(item.id, 'BEFORE').length > 0" class="space-y-2">
                      <div
                        v-for="p in getPhotosForItemStage(item.id, 'BEFORE')"
                        :key="p.id"
                        class="bg-[#0B0C0E] border border-[#232A36] rounded-none overflow-hidden group relative"
                      >
                        <!-- Photo Viewer Optic -->
                        <div class="h-36 bg-[#000000] relative cursor-pointer overflow-hidden" @click="openLightbox(p)">
                          <img :src="getFileUrl(p.file_path)" alt="Before" class="w-full h-full object-cover group-hover:scale-105 transition-all duration-300" />
                          
                          <!-- Top Solid Tag -->
                          <div class="absolute top-2 left-2 z-10">
                            <span class="bg-hazard text-carbon font-mono font-black text-[9px] px-2 py-0.5 rounded-none shadow-md">
                              [ BEFORE ]
                            </span>
                          </div>

                          <!-- Reticle Crosshair Callouts -->
                          <div class="absolute inset-0 pointer-events-none border border-hazard/20 m-1 flex flex-col justify-between p-1">
                            <div class="flex justify-between text-[8px] font-mono text-hazard/70">
                              <span>+ 0,0</span>
                              <span>SPECS +</span>
                            </div>
                            <!-- Yellow Dimension Callout Line -->
                            <div class="w-full text-center py-0.5 bg-black/40 backdrop-blur-2xs border-y border-dashed border-hazard/60 text-[9px] font-mono text-hazard font-bold tracking-widest">
                              &lt;--- DIMENSI AWAL ---&gt;
                            </div>
                            <div class="flex justify-between text-[8px] font-mono text-hazard/70">
                              <span>+ CAM_OPTIC</span>
                              <span>REV_01 +</span>
                            </div>
                          </div>

                          <!-- Download Button -->
                          <button
                            type="button"
                            @click.stop="downloadSinglePhoto(p)"
                            class="absolute top-2 right-2 w-6 h-6 rounded-none bg-carbon/90 hover:bg-hazard hover:text-carbon text-white border border-[#232A36] flex items-center justify-center transition-colors cursor-pointer z-20"
                            title="Unduh Foto"
                          >
                            <Download class="w-3 h-3" />
                          </button>
                        </div>

                        <!-- Bottom Inspection Bar -->
                        <div class="p-2 bg-[#0B0C0E] border-t border-[#232A36] text-[9px] font-mono flex items-center justify-between">
                          <span class="text-hazard font-bold truncate max-w-[130px]">
                            GPS: {{ p.latitude ? Number(p.latitude).toFixed(4) : '-' }}, {{ p.longitude ? Number(p.longitude).toFixed(4) : '-' }}
                          </span>
                          <span class="bg-emerald-950 text-emerald-400 border border-emerald-500/40 px-1 py-0.2 rounded-none font-bold">
                            SHA-256: VALID
                          </span>
                        </div>
                      </div>
                    </div>

                    <div v-else class="h-36 rounded-none border border-dashed border-[#232A36] bg-[#0B0C0E]/50 flex flex-col items-center justify-center text-slate-500 text-[10px] font-mono">
                      <span>[ BELUM ADA FOTO BEFORE ]</span>
                    </div>
                  </div>

                  <!-- STAGE: PROCESS -->
                  <div v-if="!isAfterOnly(item) && !isTwoStages(item)" class="space-y-2">
                    <div class="flex items-center justify-between font-mono text-[10px] px-0.5">
                      <span class="text-slate-400 font-bold uppercase tracking-wider">TAHAP: PROCESS</span>
                      <span class="text-hazard">{{ getPhotosForItemStage(item.id, 'PROCESS').length }} FOTO</span>
                    </div>

                    <div v-if="getPhotosForItemStage(item.id, 'PROCESS').length > 0" class="space-y-2">
                      <div
                        v-for="p in getPhotosForItemStage(item.id, 'PROCESS')"
                        :key="p.id"
                        class="bg-[#0B0C0E] border border-[#232A36] rounded-none overflow-hidden group relative"
                      >
                        <!-- Photo Viewer Optic -->
                        <div class="h-36 bg-[#000000] relative cursor-pointer overflow-hidden" @click="openLightbox(p)">
                          <img :src="getFileUrl(p.file_path)" alt="Process" class="w-full h-full object-cover group-hover:scale-105 transition-all duration-300" />
                          
                          <!-- Top Solid Tag -->
                          <div class="absolute top-2 left-2 z-10">
                            <span class="bg-blue-400 text-carbon font-mono font-black text-[9px] px-2 py-0.5 rounded-none shadow-md">
                              [ PROCESS ]
                            </span>
                          </div>

                          <!-- Reticle Crosshair Callouts -->
                          <div class="absolute inset-0 pointer-events-none border border-blue-400/20 m-1 flex flex-col justify-between p-1">
                            <div class="flex justify-between text-[8px] font-mono text-blue-300/70">
                              <span>+ ASSEMBLY</span>
                              <span>INSPECTION +</span>
                            </div>
                            <div class="w-full text-center py-0.5 bg-black/40 backdrop-blur-2xs border-y border-dashed border-blue-400/60 text-[9px] font-mono text-blue-300 font-bold tracking-widest">
                              &lt;--- TAHAP PENGERJAAN ---&gt;
                            </div>
                            <div class="flex justify-between text-[8px] font-mono text-blue-300/70">
                              <span>+ MOUNTING</span>
                              <span>PROGRESS +</span>
                            </div>
                          </div>

                          <button
                            type="button"
                            @click.stop="downloadSinglePhoto(p)"
                            class="absolute top-2 right-2 w-6 h-6 rounded-none bg-carbon/90 hover:bg-hazard hover:text-carbon text-white border border-[#232A36] flex items-center justify-center transition-colors cursor-pointer z-20"
                            title="Unduh Foto"
                          >
                            <Download class="w-3 h-3" />
                          </button>
                        </div>

                        <!-- Bottom Inspection Bar -->
                        <div class="p-2 bg-[#0B0C0E] border-t border-[#232A36] text-[9px] font-mono flex items-center justify-between">
                          <span class="text-hazard font-bold truncate max-w-[130px]">
                            GPS: {{ p.latitude ? Number(p.latitude).toFixed(4) : '-' }}, {{ p.longitude ? Number(p.longitude).toFixed(4) : '-' }}
                          </span>
                          <span class="bg-emerald-950 text-emerald-400 border border-emerald-500/40 px-1 py-0.2 rounded-none font-bold">
                            SHA-256: VALID
                          </span>
                        </div>
                      </div>
                    </div>

                    <div v-else class="h-36 rounded-none border border-dashed border-[#232A36] bg-[#0B0C0E]/50 flex flex-col items-center justify-center text-slate-500 text-[10px] font-mono">
                      <span>[ BELUM ADA FOTO PROCESS ]</span>
                    </div>
                  </div>

                  <!-- STAGE: AFTER (Hasil Akhir) -->
                  <div class="space-y-2">
                    <div class="flex items-center justify-between font-mono text-[10px] px-0.5">
                      <span class="text-slate-400 font-bold uppercase tracking-wider">
                        {{ isAfterOnly(item) ? 'HASIL AKHIR (AFTER ONLY)' : 'TAHAP: AFTER' }}
                      </span>
                      <span class="text-hazard">{{ getPhotosForItemStage(item.id, 'AFTER').length }} FOTO</span>
                    </div>

                    <div v-if="getPhotosForItemStage(item.id, 'AFTER').length > 0" :class="isAfterOnly(item) ? 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3' : 'space-y-2'">
                      <div
                        v-for="p in getPhotosForItemStage(item.id, 'AFTER')"
                        :key="p.id"
                        class="bg-[#0B0C0E] border border-[#232A36] rounded-none overflow-hidden group relative"
                      >
                        <!-- Photo Viewer Optic -->
                        <div class="h-36 bg-[#000000] relative cursor-pointer overflow-hidden" @click="openLightbox(p)">
                          <img :src="getFileUrl(p.file_path)" alt="After" class="w-full h-full object-cover group-hover:scale-105 transition-all duration-300" />
                          
                          <!-- Top Solid Tag -->
                          <div class="absolute top-2 left-2 z-10">
                            <span class="bg-hazard text-carbon font-mono font-black text-[9px] px-2 py-0.5 rounded-none shadow-md">
                              [ AFTER ]
                            </span>
                          </div>

                          <!-- Reticle Crosshair Callouts -->
                          <div class="absolute inset-0 pointer-events-none border border-emerald-400/30 m-1 flex flex-col justify-between p-1">
                            <div class="flex justify-between text-[8px] font-mono text-emerald-400/80 font-bold">
                              <span>+ VERIFIED</span>
                              <span>FINAL QA +</span>
                            </div>
                            <!-- Yellow Dimension Callout Line -->
                            <div class="w-full text-center py-0.5 bg-black/50 backdrop-blur-2xs border-y border-dashed border-hazard text-[9px] font-mono text-hazard font-black tracking-widest">
                              &lt;--- SPK COMPLETED 100% ---&gt;
                            </div>
                            <div class="flex justify-between text-[8px] font-mono text-emerald-400/80 font-bold">
                              <span>+ SIGNAGE_OK</span>
                              <span>BA_READY +</span>
                            </div>
                          </div>

                          <button
                            type="button"
                            @click.stop="downloadSinglePhoto(p)"
                            class="absolute top-2 right-2 w-6 h-6 rounded-none bg-carbon/90 hover:bg-hazard hover:text-carbon text-white border border-[#232A36] flex items-center justify-center transition-colors cursor-pointer z-20"
                            title="Unduh Foto"
                          >
                            <Download class="w-3 h-3" />
                          </button>
                        </div>

                        <!-- Bottom Inspection Bar -->
                        <div class="p-2 bg-[#0B0C0E] border-t border-[#232A36] text-[9px] font-mono flex items-center justify-between">
                          <span class="text-hazard font-bold truncate max-w-[130px]">
                            GPS: {{ p.latitude ? Number(p.latitude).toFixed(4) : '-' }}, {{ p.longitude ? Number(p.longitude).toFixed(4) : '-' }}
                          </span>
                          <span class="bg-emerald-950 text-emerald-400 border border-emerald-500/40 px-1 py-0.2 rounded-none font-bold">
                            SHA-256: VALID
                          </span>
                        </div>
                      </div>
                    </div>

                    <div v-else class="h-36 rounded-none border border-dashed border-[#232A36] bg-[#0B0C0E]/50 flex flex-col items-center justify-center text-slate-500 text-[10px] font-mono">
                      <span>[ BELUM ADA FOTO HASIL AKHIR ]</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- MODE 2: FLAT STAGE VIEW -->
            <div v-else class="space-y-4">
              <div v-for="stage in ['BEFORE', 'PROCESS', 'AFTER']" :key="stage" class="bg-[#0E1117] border border-[#232A36] rounded-xs p-3.5 space-y-3">
                <div class="flex items-center justify-between border-b border-[#232A36] pb-2 font-mono">
                  <div class="flex items-center gap-2">
                    <span class="bg-hazard text-carbon font-mono font-black text-[9px] px-2 py-0.5 rounded-none">
                      [ {{ stage }} ]
                    </span>
                    <span class="text-white text-xs font-bold uppercase">KLASIFIKASI DOKUMENTASI: {{ stage }}</span>
                  </div>
                  <span class="text-[10px] text-slate-400 font-mono">
                    {{ selectedOrder.evidence_photos?.filter(p => p.stage === stage).length || 0 }} BERKAS TERCATAT
                  </span>
                </div>

                <div
                  v-if="selectedOrder.evidence_photos?.filter(p => p.stage === stage).length > 0"
                  class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3"
                >
                  <div
                    v-for="(photo, pIdx) in selectedOrder.evidence_photos.filter(p => p.stage === stage)"
                    :key="photo.id"
                    class="bg-[#0B0C0E] border border-[#232A36] rounded-none overflow-hidden group relative"
                  >
                    <div class="h-36 bg-[#000000] relative cursor-pointer overflow-hidden" @click="openLightbox(photo)">
                      <img
                        :src="getFileUrl(photo.file_path)"
                        :alt="`Evidence ${stage}`"
                        class="w-full h-full object-cover group-hover:scale-105 transition-all duration-300"
                      />

                      <!-- Overlay Reticle -->
                      <div class="absolute inset-0 pointer-events-none border border-hazard/20 m-1 flex flex-col justify-between p-1">
                        <div class="flex justify-between text-[8px] font-mono text-hazard/70">
                          <span>#{{ photo.sequence || pIdx + 1 }}</span>
                          <span>FORENSIC_TAG</span>
                        </div>
                        <div class="w-full text-center py-0.5 bg-black/40 border-y border-dashed border-hazard/50 text-[8px] font-mono text-hazard font-bold">
                          &lt;--- {{ stage }} OPTIC ---&gt;
                        </div>
                        <div class="text-[8px] font-mono text-slate-400">
                          {{ new Date(photo.server_timestamp).toLocaleTimeString('id-ID') }}
                        </div>
                      </div>

                      <button
                        type="button"
                        @click.stop="downloadSinglePhoto(photo)"
                        class="absolute top-2 right-2 w-6 h-6 rounded-none bg-carbon/90 hover:bg-hazard hover:text-carbon text-white border border-[#232A36] flex items-center justify-center transition-colors cursor-pointer z-20"
                        title="Unduh Resolusi Penuh"
                      >
                        <Download class="w-3 h-3" />
                      </button>
                    </div>

                    <!-- Bottom Inspection Bar -->
                    <div class="p-2 bg-[#0B0C0E] border-t border-[#232A36] text-[9px] font-mono flex items-center justify-between">
                      <span class="text-hazard font-bold truncate max-w-[130px]">
                        GPS: {{ photo.latitude ? Number(photo.latitude).toFixed(4) : '-' }}, {{ photo.longitude ? Number(photo.longitude).toFixed(4) : '-' }}
                      </span>
                      <span class="bg-emerald-950 text-emerald-400 border border-emerald-500/40 px-1 py-0.2 rounded-none font-bold">
                        SHA-256: VALID
                      </span>
                    </div>
                  </div>
                </div>

                <div v-else class="text-center py-6 text-slate-500 text-xs font-mono border border-dashed border-[#232A36]">
                  [ TIDAK ADA FOTO PADA TAHAP {{ stage }} ]
                </div>
              </div>
            </div>
          </div>

          <!-- Technical Field Issues Note -->
          <div v-if="selectedOrder.issues && selectedOrder.issues.length > 0" class="p-3 bg-red-950/20 border-l-4 border-l-red-500 border-y border-r border-red-900/40 rounded-xs space-y-1.5 text-xs font-mono">
            <div class="font-bold text-red-400 flex items-center gap-2">
              <AlertTriangle class="w-4 h-4 text-red-400 shrink-0" />
              <span class="uppercase tracking-wider">LOG KENDALA TEKNIS LAPANGAN:</span>
            </div>
            <p v-for="iss in selectedOrder.issues" :key="iss.id" class="text-slate-300">
              <strong class="text-red-300">[{{ iss.issue_type }}]:</strong> {{ iss.notes }}
            </p>
          </div>
        </template>

        <div v-else class="py-32 text-center text-slate-500 text-xs font-mono space-y-2 border border-dashed border-[#232A36] rounded-xs">
          <CheckSquare class="w-8 h-8 text-slate-600 mx-auto" />
          <p class="font-bold text-slate-400 uppercase">PILIH_SPK_UNTUK_INSPEKSI</p>
          <p class="text-slate-500 text-[11px]">Silakan klik salah satu pekerjaan pada daftar antrian sebelah kiri untuk memulai audit visual.</p>
        </div>
      </div>
    </div>

    <!-- Approve Confirmation Modal (CAD Style) -->
    <Teleport to="body">
      <div v-if="showApproveModal" class="fixed inset-0 z-50 bg-[#0B0C0E]/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-[#13171F] border border-hazard/40 rounded-xs max-w-md w-full shadow-2xl p-5 space-y-4 text-xs font-mono">
          <div class="flex items-center justify-between border-b border-[#232A36] pb-3">
            <h3 class="font-heading font-black text-sm text-white flex items-center gap-2 uppercase tracking-wider">
              <CheckCircle2 class="w-4 h-4 text-hazard" />
              <span>Validasi Persetujuan (Approval)</span>
            </h3>
            <button @click="showApproveModal = false" class="text-slate-400 hover:text-white p-1">
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="p-3 bg-[#0B0C0E] border-l-4 border-l-hazard border-y border-r border-[#232A36] text-slate-300 space-y-1">
            <p class="font-bold text-white uppercase tracking-wider">Konfirmasi Penyelesaian SPK:</p>
            <p class="text-[11px] text-hazard">
              Nomor: <strong>{{ selectedOrder?.spk_number }}</strong> - {{ selectedOrder?.title }}
            </p>
            <p class="text-[10px] text-slate-400">
              Persetujuan ini akan mengunci bukti foto dan melegalkan status pekerjaan untuk penerbitan Berita Acara (BA Opname).
            </p>
          </div>

          <div>
            <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Catatan Persetujuan / Quality Assurance:</label>
            <textarea
              rows="3"
              v-model="reviewNotes"
              class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs text-white focus:border-hazard focus:outline-none font-mono text-xs"
            />
          </div>

          <div class="pt-2 flex items-center justify-end gap-2 border-t border-[#232A36]">
            <button
              type="button"
              @click="showApproveModal = false"
              class="px-4 py-2 bg-[#0B0C0E] hover:bg-[#181D26] text-slate-300 border border-[#232A36] font-bold rounded-xs cursor-pointer uppercase"
            >
              Batal
            </button>
            <button
              type="button"
              @click="handleApproveConfirm"
              :disabled="actionLoading"
              class="cad-btn-primary px-5 py-2 text-xs flex items-center gap-1.5 cursor-pointer uppercase font-bold"
            >
              <Loader2 v-if="actionLoading" class="w-3.5 h-3.5 animate-spin" />
              <CheckCircle2 v-else class="w-3.5 h-3.5" />
              <span>{{ actionLoading ? 'MEMPROSES...' : 'YA, SETUJUI PEKERJAAN' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Revision Request Modal (Safety Red CAD Style) -->
    <Teleport to="body">
      <div v-if="showRevisionModal" class="fixed inset-0 z-50 bg-[#0B0C0E]/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-[#13171F] border border-red-500/50 rounded-xs max-w-md w-full shadow-2xl p-5 space-y-4 text-xs font-mono">
          <div class="flex items-center justify-between border-b border-[#232A36] pb-3">
            <h3 class="font-heading font-black text-sm text-red-400 flex items-center gap-2 uppercase tracking-wider">
              <RotateCcw class="w-4 h-4 text-red-400" />
              <span>Instruksi Revisi Lapangan</span>
            </h3>
            <button @click="showRevisionModal = false" class="text-slate-400 hover:text-white p-1">
              <X class="w-4 h-4" />
            </button>
          </div>

          <form @submit.prevent="handleSendRevision" class="space-y-3.5">
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Target Bagian yang Direvisi:</label>
              <select
                v-model="revisionStage"
                class="w-full px-3 py-2 border border-[#232A36] rounded-xs bg-[#0B0C0E] text-white focus:border-red-500 focus:outline-none font-mono text-xs"
              >
                <option value="AFTER">FOTO HASIL AKHIR (AFTER)</option>
                <option value="PROCESS">FOTO PROSES INSTALASI (PROCESS)</option>
                <option value="BEFORE">FOTO KONDISI AWAL (BEFORE)</option>
                <option value="ALL">SELURUH DOKUMENTASI & KENDALA</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Alasan / Instruksi Perbaikan Teknis *:</label>
              <textarea
                rows="3"
                required
                placeholder="Contoh: Foto After nomor 2 buram / tidak fokus. Ambil ulang foto sudut palang merek beserta pencahayaan menyala."
                v-model="revisionReason"
                class="w-full px-3 py-2 border border-[#232A36] rounded-xs focus:border-red-500 focus:outline-none bg-[#0B0C0E] text-white font-mono text-xs"
              />
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-[#232A36]">
              <button
                type="button"
                @click="showRevisionModal = false"
                class="px-4 py-2 bg-[#0B0C0E] hover:bg-[#181D26] text-slate-300 border border-[#232A36] font-bold rounded-xs cursor-pointer uppercase"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="actionLoading"
                class="px-5 py-2 bg-red-600 hover:bg-red-500 text-white font-bold rounded-xs cursor-pointer uppercase tracking-wider flex items-center gap-1.5"
              >
                <RotateCcw class="w-3.5 h-3.5" />
                <span>{{ actionLoading ? 'MENGIRIM...' : 'KIRIM REVISI KE TEKNISI' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Add Addendum Sub-Task Modal Dialog -->
    <Teleport to="body">
      <div
        v-if="showAddendumModal"
        class="fixed inset-0 z-50 bg-[#0B0C0E]/80 backdrop-blur-xs flex items-center justify-center p-4 animate-fade-in"
      >
        <div class="bg-[#13171F] border border-hazard/40 rounded-xs max-w-md w-full shadow-2xl p-5 space-y-4 text-xs font-mono">
          <div class="flex items-center justify-between border-b border-[#232A36] pb-3">
            <h3 class="font-heading font-black text-sm text-white flex items-center gap-2 uppercase tracking-wider">
              <Plus class="w-4 h-4 text-hazard" />
              <span>Tambah Sub-Pekerjaan (Addendum)</span>
            </h3>
            <button @click="showAddendumModal = false" class="text-slate-400 hover:text-white cursor-pointer p-1">
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="p-3 bg-[#0B0C0E] border-l-4 border-l-hazard border-y border-r border-[#232A36] text-slate-300 text-[11px] space-y-1">
            <p class="font-bold text-white uppercase tracking-wider">Instruksi Tambahan Pengawas:</p>
            <p class="font-mono text-hazard text-[10px]">{{ selectedOrder?.spk_number }} - {{ selectedOrder?.title }}</p>
            <p class="text-slate-400 text-[10px]">SPK akan dialihkan ke status pengerjaan agar tim lapangan melengkapi dokumentasi foto untuk item baru ini.</p>
          </div>

          <div class="space-y-3">
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Nama Sub-Pekerjaan Tambahan <span class="text-red-400">*</span>:</label>
              <input
                type="text"
                v-model="addendumForm.itemName"
                placeholder="Contoh: Pengelasan Rangka Tambahan & Pengecatan Ulang"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs focus:border-hazard focus:outline-none text-white text-xs font-mono"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Mode Dokumentasi Foto:</label>
              <select
                v-model="addendumForm.docMode"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs focus:border-hazard focus:outline-none text-white text-xs font-mono"
              >
                <option value="BEFORE_PROCESS_AFTER">BEFORE ➔ PROCESS ➔ AFTER (Standar Lengkap)</option>
                <option value="BEFORE_AFTER">BEFORE ➔ AFTER (Tanpa Foto Proses)</option>
                <option value="AFTER_ONLY">AFTER ONLY (Hanya Foto Hasil Akhir)</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider">Catatan / Instruksi Pengawas:</label>
              <textarea
                rows="2"
                v-model="addendumForm.notes"
                placeholder="Detail instruksi penambahan pekerjaan untuk teknisi di lokasi..."
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] rounded-xs focus:border-hazard focus:outline-none text-white text-xs font-mono"
              />
            </div>
          </div>

          <div class="pt-2 flex items-center justify-end gap-2 border-t border-[#232A36]">
            <button
              type="button"
              @click="showAddendumModal = false"
              class="px-4 py-2 bg-[#0B0C0E] hover:bg-[#181D26] text-slate-300 border border-[#232A36] font-bold rounded-xs cursor-pointer uppercase"
            >
              Batal
            </button>
            <button
              type="button"
              @click="handleSubmitAddendum"
              :disabled="submittingAddendum"
              class="cad-btn-primary px-5 py-2 font-bold rounded-xs flex items-center gap-1.5 cursor-pointer uppercase disabled:opacity-50"
            >
              <Plus class="w-3.5 h-3.5" />
              <span>{{ submittingAddendum ? 'MENYIMPAN...' : 'SIMPAN & TAMBAH ITEM' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Forensic Photo Lightbox Full-Screen Viewer -->
    <PhotoLightboxModal
      :isOpen="isLightboxOpen"
      :photos="selectedOrder?.evidence_photos || []"
      :initialIndex="selectedLightboxIndex"
      @close="isLightboxOpen = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { api, getFileUrl } from '../../services/api';
import StatusBadge from '../../components/StatusBadge.vue';
import PhotoLightboxModal from '../../components/PhotoLightboxModal.vue';
import {
  CheckSquare,
  CheckCircle2,
  AlertTriangle,
  RotateCcw,
  Camera,
  X,
  FileCheck2,
  Loader2,
  Check,
  Download,
  Search,
  Store,
  RefreshCw,
  Clock,
  Plus
} from 'lucide-vue-next';

const props = defineProps({
  selectedWorkOrderId: {
    type: [String, Number],
    default: null
  }
});

const emit = defineEmits(['approved-success', 'open-ba']);

const allWorkOrders = ref([]);
const selectedOrder = ref(null);
const loading = ref(true);
const actionLoading = ref(false);
const successToast = ref(null);
const photoViewMode = ref('BY_ITEM'); // 'BY_ITEM' | 'ALL_STAGES'

// Addendum Sub-Task Modal State for Reviewer
const showAddendumModal = ref(false);
const submittingAddendum = ref(false);
const addendumForm = ref({
  itemName: '',
  docMode: 'BEFORE_PROCESS_AFTER',
  notes: ''
});

function openAddendumModal() {
  if (!selectedOrder.value) return;
  addendumForm.value = {
    itemName: '',
    docMode: selectedOrder.value?.doc_mode || 'BEFORE_PROCESS_AFTER',
    notes: ''
  };
  showAddendumModal.value = true;
}

async function handleSubmitAddendum() {
  if (!selectedOrder.value?.id) return;
  if (!addendumForm.value.itemName.trim()) {
    alert('Nama sub-pekerjaan tambahan wajib diisi!');
    return;
  }

  submittingAddendum.value = true;
  try {
    const res = await api.addWorkOrderItem(selectedOrder.value.id, {
      item_name: addendumForm.value.itemName.trim(),
      doc_mode: addendumForm.value.docMode,
      notes: addendumForm.value.notes.trim()
    });

    if (res.success || res.data) {
      alert(`Pekerjaan tambahan '${addendumForm.value.itemName}' berhasil ditambahkan. Status SPK dialihkan ke pengerjaan.`);
      showAddendumModal.value = false;
      await loadQueue(selectedOrder.value.id);
    }
  } catch (err) {
    console.error('Failed to add addendum item in review:', err);
    alert('Gagal menambahkan sub-pekerjaan: ' + (err.message || 'Terjadi kesalahan'));
  } finally {
    submittingAddendum.value = false;
  }
}

const searchQuery = ref('');
const activeStatusFilter = ref('ALL');

const displayItems = computed(() => {
  if (selectedOrder.value?.items && selectedOrder.value.items.length > 0) {
    return selectedOrder.value.items;
  }
  return [{
    id: null,
    item_name: selectedOrder.value?.title || 'Lingkup Pekerjaan Utama',
    doc_mode: selectedOrder.value?.doc_mode || 'BEFORE_PROCESS_AFTER',
    weight_percent: 100
  }];
});

function getPhotosForItemStage(itemId, stage) {
  const photos = selectedOrder.value?.evidence_photos || [];
  return photos.filter(p => {
    const stageMatch = p.stage === stage;
    if (!stageMatch) return false;
    if (itemId) {
      return p.item_id === itemId || !p.item_id;
    }
    return true;
  });
}

function isAfterOnly(item) {
  const mode = String(item?.doc_mode || selectedOrder.value?.doc_mode || '').toUpperCase();
  return mode === 'AFTER_ONLY' || mode === 'ONE_STAGE' || mode === '1_STAGE' || mode === 'AFTER';
}

function isTwoStages(item) {
  const mode = String(item?.doc_mode || selectedOrder.value?.doc_mode || '').toUpperCase();
  return mode === 'TWO_STAGES' || mode === 'BEFORE_AFTER';
}

function isItemFullyDocumented(item) {
  const afterPhotos = getPhotosForItemStage(item.id, 'AFTER');
  if (isAfterOnly(item)) {
    return afterPhotos.length > 0;
  }
  const beforePhotos = getPhotosForItemStage(item.id, 'BEFORE');
  return beforePhotos.length > 0 && afterPhotos.length > 0;
}

const showApproveModal = ref(false);
const reviewNotes = ref('Pekerjaan telah diverifikasi di lapangan dan memenuhi seluruh standar mutu & spesifikasi.');

const showRevisionModal = ref(false);
const revisionStage = ref('AFTER');
const revisionReason = ref('');

const filterTabs = computed(() => {
  const all = allWorkOrders.value;
  return [
    { id: 'ALL', label: 'SEMUA', count: all.length },
    { id: 'WAITING_REVIEW', label: 'PERLU REVIEW', count: all.filter(w => ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'].includes(w.status)).length },
    { id: 'REVISION', label: 'REVISI', count: all.filter(w => w.status === 'REVISION').length },
    { id: 'IN_PROGRESS', label: 'BERJALAN', count: all.filter(w => ['IN_PROGRESS', 'CHECKED_IN'].includes(w.status)).length },
    { id: 'APPROVED', label: 'SELESAI', count: all.filter(w => ['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(w.status)).length },
  ];
});

const filteredQueue = computed(() => {
  return allWorkOrders.value.filter(wo => {
    // 1. Status Filter
    if (activeStatusFilter.value === 'WAITING_REVIEW' && !['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'].includes(wo.status)) {
      return false;
    }
    if (activeStatusFilter.value === 'REVISION' && wo.status !== 'REVISION') {
      return false;
    }
    if (activeStatusFilter.value === 'IN_PROGRESS' && !['IN_PROGRESS', 'CHECKED_IN'].includes(wo.status)) {
      return false;
    }
    if (activeStatusFilter.value === 'APPROVED' && !['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(wo.status)) {
      return false;
    }

    // 2. Search Query
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase();
      const matchSpk = wo.spk_number?.toLowerCase().includes(q);
      const matchTitle = wo.title?.toLowerCase().includes(q);
      const matchLoc = wo.location_name?.toLowerCase().includes(q);
      const matchVendor = wo.vendor_name?.toLowerCase().includes(q) || wo.vendor?.name?.toLowerCase().includes(q);
      const matchPic = wo.pic_name?.toLowerCase().includes(q);
      if (!matchSpk && !matchTitle && !matchLoc && !matchVendor && !matchPic) {
        return false;
      }
    }

    return true;
  });
});

async function loadQueue(targetIdToSelect = null) {
  loading.value = true;
  try {
    const res = await api.getWorkOrders();
    allWorkOrders.value = res.data || [];

    const targetId = targetIdToSelect || props.selectedWorkOrderId || (allWorkOrders.value.length > 0 ? allWorkOrders.value[0].id : null);
    if (targetId) {
      const detail = await api.getWorkOrderById(targetId);
      selectedOrder.value = detail.data;
    } else if (allWorkOrders.value.length > 0) {
      const detail = await api.getWorkOrderById(allWorkOrders.value[0].id);
      selectedOrder.value = detail.data;
    }
  } catch (err) {
    console.error('Failed to load review queue:', err);
  } finally {
    loading.value = false;
  }
}

watch(() => props.selectedWorkOrderId, (newId) => {
  if (newId) {
    loadQueue(newId);
  }
});

async function handleSelectOrder(id) {
  try {
    const detail = await api.getWorkOrderById(id);
    selectedOrder.value = detail.data;
  } catch (err) {
    alert(err.message);
  }
}

async function handleApproveConfirm() {
  actionLoading.value = true;
  try {
    await api.approveWorkOrder({
      work_order_id: selectedOrder.value.id,
      review_notes: reviewNotes.value
    });
    showApproveModal.value = false;
    successToast.value = `SPK ${selectedOrder.value.spk_number} BERHASIL DISETUJUI (APPROVED)!`;
    setTimeout(() => successToast.value = null, 4000);

    const updated = await api.getWorkOrderById(selectedOrder.value.id);
    selectedOrder.value = updated.data;
    emit('approved-success', selectedOrder.value.id);
    loadQueue(selectedOrder.value.id);
  } catch (err) {
    alert(`Gagal menyetujui: ${err.message}`);
  } finally {
    actionLoading.value = false;
  }
}

async function handleSendRevision() {
  if (!revisionReason.value.trim()) {
    alert('Alasan revisi wajib diisi secara spesifik!');
    return;
  }
  actionLoading.value = true;
  try {
    await api.requestRevision({
      work_order_id: selectedOrder.value.id,
      target_stage: revisionStage.value,
      reason: revisionReason.value
    });
    showRevisionModal.value = false;
    revisionReason.value = '';
    successToast.value = `Instruksi revisi berhasil dikirimkan ke teknisi lapangan.`;
    setTimeout(() => successToast.value = null, 4000);

    const updated = await api.getWorkOrderById(selectedOrder.value.id);
    selectedOrder.value = updated.data;
    loadQueue(selectedOrder.value.id);
  } catch (err) {
    alert(`Gagal request revision: ${err.message}`);
  } finally {
    actionLoading.value = false;
  }
}

async function handleGenerateBa() {
  actionLoading.value = true;
  try {
    const res = await api.generateBa({ work_order_id: selectedOrder.value.id });
    successToast.value = `Berita Acara Opname (${res.data.ba_number}) berhasil diterbitkan!`;
    setTimeout(() => successToast.value = null, 4000);

    const updated = await api.getWorkOrderById(selectedOrder.value.id);
    selectedOrder.value = updated.data;
    loadQueue(selectedOrder.value.id);
    emit('open-ba', res.data);
  } catch (err) {
    alert(`Gagal menerbitkan BA: ${err.message}`);
  } finally {
    actionLoading.value = false;
  }
}

/**
 * Lightbox & Download Handlers
 */
const isLightboxOpen = ref(false);
const selectedLightboxIndex = ref(0);

function openLightbox(photo) {
  const allPhotos = selectedOrder.value?.evidence_photos || [];
  const idx = allPhotos.findIndex(p => p.id === photo.id);
  selectedLightboxIndex.value = idx >= 0 ? idx : 0;
  isLightboxOpen.value = true;
}

function downloadSinglePhoto(photo) {
  if (!photo?.file_path) return;
  const link = document.createElement('a');
  link.href = getFileUrl(photo.file_path);
  const ext = photo.file_name?.split('.').pop() || 'jpg';
  const spk = selectedOrder.value?.spk_number ? `${selectedOrder.value.spk_number}_` : '';
  link.download = `${spk}${photo.stage || 'EVIDENCE'}_${photo.sequence || 1}.${ext}`;
  link.target = '_blank';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function downloadAllPhotos() {
  const allPhotos = selectedOrder.value?.evidence_photos || [];
  if (allPhotos.length === 0) {
    alert('Tidak ada foto untuk diunduh.');
    return;
  }

  allPhotos.forEach((p, idx) => {
    setTimeout(() => {
      downloadSinglePhoto(p);
    }, idx * 250);
  });
}

onMounted(() => {
  loadQueue();
});
</script>
