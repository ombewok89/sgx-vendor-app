<template>
  <div class="space-y-4 sm:space-y-5 text-slate-100 font-sans pb-12">
    <!-- Title Header -->
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xl font-mono">
      <div>
        <div class="flex items-center gap-2 text-[10px] text-slate-400 uppercase tracking-wider mb-1">
          <span>PORTAL</span>
          <span class="text-slate-600">//</span>
          <span>OPERATOR LAPANGAN</span>
          <span class="text-slate-600">//</span>
          <span class="text-[#FACC15] font-bold">LEMBAR KERJA SPK</span>
        </div>
        <h2 class="text-lg sm:text-2xl font-black text-white tracking-wider flex items-center gap-2.5 uppercase">
          <div class="w-8 h-8 rounded-xs bg-[#0B0C0E] border border-[#FACC15] flex items-center justify-center text-[#FACC15] shrink-0">
            <Smartphone class="w-4 h-4" />
          </div>
          <span>PEKERJAAN LAPANGAN SAYA</span>
        </h2>
        <p class="text-xs text-slate-400 mt-1 font-sans">
          Check-in GPS terpadu per cabang, dokumentasi foto forensik digital sub-pekerjaan, dan pengajuan verifikasi QA.
        </p>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <button
          @click="loadTasks()"
          class="px-3.5 py-2 bg-[#1C2028] hover:bg-[#232A36] active:scale-95 text-slate-200 hover:text-white text-xs font-bold rounded-xs transition-colors border border-[#232A36] hover:border-[#FACC15]/40 flex items-center gap-2 cursor-pointer uppercase tracking-wider shadow-xs"
        >
          <RefreshCw class="w-3.5 h-3.5 text-[#FACC15]" :class="{ 'animate-spin': loading }" />
          <span>Perbarui Daftar</span>
        </button>
      </div>
    </div>

    <!-- Mobile Horizontal Task Switcher Carousel (Visible on Mobile Only) -->
    <div class="block lg:hidden space-y-2 font-mono">
      <div class="flex items-center justify-between px-1">
        <h3 class="font-bold text-xs text-slate-400 uppercase tracking-wider">
          // PILIH SPK CABANG ({{ tasks.length }})
        </h3>
        <span class="text-[10px] font-bold bg-[#13171F] text-[#FACC15] px-2 py-0.5 rounded-xs border border-[#232A36]">
          Geser untuk memilih
        </span>
      </div>

      <div class="flex gap-2.5 overflow-x-auto pb-2 custom-scrollbar snap-x snap-mandatory">
        <div
          v-for="(task, idx) in tasks"
          :key="task.id"
          @click="handleSelectTask(task.id)"
          :class="[
            'min-w-[260px] max-w-[290px] p-3.5 rounded-xs cursor-pointer transition-all duration-200 snap-start shrink-0 relative overflow-hidden border',
            selectedTask?.id === task.id
              ? 'bg-[#1C2028] border-l-4 border-l-[#FACC15] border-y border-r border-[#232A36] shadow-md'
              : 'bg-[#13171F] hover:bg-[#161B24] border border-[#232A36]'
          ]"
        >
          <div class="flex items-center justify-between gap-1.5 mb-2">
            <div class="flex items-center gap-1.5">
              <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-xs bg-[#0B0C0E] text-slate-400 border border-[#232A36]">#{{ idx + 1 }}</span>
              <span class="text-xs font-black px-2 py-0.5 rounded-xs bg-[#0B0C0E] text-[#FACC15] border border-[#FACC15]/30">
                {{ task.spk_number }}
              </span>
            </div>
            <StatusBadge :status="task.status" />
          </div>

          <div class="font-bold text-xs line-clamp-1 mb-1 text-white">
            {{ task.title || task.location_name }}
          </div>

          <div class="text-[11px] flex items-center justify-between text-slate-400">
            <span class="truncate max-w-[150px] font-mono">📍 {{ task.location_name }}</span>
            <span class="font-bold font-mono text-[#FACC15]">{{ task.progress_percent || 0 }}%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Workspace: Split Left (Task Cards) & Right (Workstation) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-5">
      <!-- Desktop Task Cards List (Visible on Large Screens) -->
      <div class="hidden lg:block space-y-2.5 font-mono">
        <div class="flex items-center justify-between px-1 border-b border-[#232A36] pb-2">
          <h3 class="font-bold text-xs text-slate-400 uppercase tracking-wider">
            // DAFTAR SPK CABANG ({{ tasks.length }})
          </h3>
          <span class="text-[10px] font-bold bg-[#13171F] text-[#FACC15] px-2 py-0.5 rounded-xs border border-[#232A36]">
            FIELD WORKSTATION
          </span>
        </div>

        <div v-if="tasks.length > 0" class="space-y-2.5">
          <div
            v-for="(task, idx) in tasks"
            :key="task.id"
            @click="handleSelectTask(task.id)"
            :class="[
              'p-3.5 sm:p-4 rounded-xs cursor-pointer transition-all border relative overflow-hidden group',
              selectedTask?.id === task.id
                ? 'bg-[#1C2028] border-l-4 border-l-[#FACC15] border-y border-r border-[#232A36] shadow-lg translate-x-1'
                : 'bg-[#13171F] hover:bg-[#161B24] border border-[#232A36] hover:border-[#FACC15]/40'
            ]"
          >
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-1.5">
                <span class="font-mono font-bold text-xs text-slate-400 bg-[#0B0C0E] px-2 py-0.5 rounded-xs border border-[#232A36]">
                  #{{ idx + 1 }}
                </span>
                <span class="font-mono font-black text-xs text-[#FACC15] bg-[#0B0C0E] px-2 py-0.5 rounded-xs border border-[#FACC15]/30">
                  {{ task.spk_number }}
                </span>
              </div>
              <StatusBadge :status="task.status" />
            </div>

            <div class="font-bold text-white text-xs sm:text-sm mb-1.5 line-clamp-1 group-hover:text-[#FACC15] transition-colors">
              {{ task.title }}
            </div>

            <div class="text-slate-400 text-xs flex items-center gap-1.5 mb-2 font-mono">
              <MapPin class="w-3.5 h-3.5 text-[#FACC15] shrink-0" />
              <span class="truncate">{{ task.location_name }}</span>
            </div>

            <!-- Multi-Item Badge -->
            <div class="flex items-center gap-1.5 mb-3 font-mono">
              <span class="px-2 py-0.5 bg-[#0B0C0E] border border-[#232A36] text-slate-300 font-bold text-[10px] rounded-xs flex items-center gap-1">
                <Layers class="w-3 h-3 text-[#FACC15]" />
                {{ task.items?.length || 1 }} Sub-Pekerjaan
              </span>
            </div>

            <!-- Mini Progress bar on Task Card -->
            <div class="space-y-1 pt-2 border-t border-[#232A36] font-mono">
              <div class="flex items-center justify-between text-[10px]">
                <span class="text-slate-500">Target: {{ task.deadline || '-' }}</span>
                <span class="font-bold text-[#FACC15]">{{ task.progress_percent }}%</span>
              </div>
              <div class="w-full h-1.5 bg-[#0B0C0E] rounded-none overflow-hidden border border-[#232A36]">
                <div
                  class="h-full bg-gradient-to-r from-[#FACC15] to-emerald-400 transition-all duration-500"
                  :style="{ width: `${task.progress_percent}%` }"
                />
              </div>
            </div>
          </div>
        </div>

        <div v-else class="p-8 text-center bg-[#13171F] rounded-xs text-slate-400 text-xs border border-[#232A36] font-mono">
          Belum ada penugasan pekerjaan untuk akun Anda.
        </div>
      </div>

      <!-- Task Execution Workspace (Right Pane) -->
      <div class="lg:col-span-2 space-y-4">
        <div v-if="selectedTask" class="bg-[#13171F] rounded-xs p-4 sm:p-6 border border-[#232A36] shadow-xl space-y-5">
          <!-- Task Header -->
          <div class="border-b border-[#232A36] pb-4 font-mono">
            <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
              <div class="flex items-center gap-2">
                <span class="font-black text-xs bg-[#0B0C0E] border border-[#FACC15]/40 text-[#FACC15] px-2.5 py-1 rounded-xs">
                  SPK: {{ selectedTask.spk_number }}
                </span>
                <span class="px-2.5 py-1 bg-[#0B0C0E] border border-[#232A36] text-slate-300 text-xs font-bold rounded-xs flex items-center gap-1.5">
                  <Layers class="w-3.5 h-3.5 text-[#FACC15]" />
                  <span>{{ taskItems.length }} Sub-Pekerjaan</span>
                </span>
              </div>
              <StatusBadge :status="selectedTask.status" />
            </div>

            <h3 class="font-bold text-white text-base sm:text-xl tracking-tight mt-2 font-sans">
              {{ selectedTask.title }}
            </h3>

            <div class="text-xs text-slate-400 flex items-center gap-1.5 mt-2">
              <MapPin class="w-4 h-4 text-[#FACC15] shrink-0" />
              <span>{{ selectedTask.location_name }} <strong class="text-slate-200">(Area: {{ selectedTask.area_name || selectedTask.area?.name || '-' }})</strong></span>
            </div>
          </div>

          <!-- SPK Notes & Special Instructions from Admin -->
          <div
            v-if="selectedTask.notes"
            class="p-4 bg-[#0B0C0E] border-l-4 border-l-indigo-400 border-y border-r border-[#232A36] rounded-xs space-y-1.5 shadow-sm font-mono"
          >
            <div class="flex items-center gap-2 text-indigo-300 font-bold text-xs">
              <FileText class="w-4 h-4 text-indigo-400 shrink-0" />
              <span class="uppercase tracking-wider">// CATATAN & INSTRUKSI KHUSUS ADMIN:</span>
            </div>
            <p class="text-xs text-slate-200 font-sans whitespace-pre-line pl-6 leading-relaxed">
              {{ selectedTask.notes }}
            </p>
          </div>

          <!-- Revision Alert if in REVISION state -->
          <div
            v-if="selectedTask.status === 'REVISION'"
            class="p-4 sm:p-5 bg-rose-950/70 border-l-4 border-l-rose-500 border-y border-r border-rose-500/60 rounded-xs space-y-3 shadow-md animate-fade-in font-mono"
          >
            <div class="flex items-center justify-between flex-wrap gap-2">
              <div class="font-bold text-xs text-rose-300 flex items-center gap-2 uppercase tracking-wider">
                <div class="w-7 h-7 rounded-xs bg-rose-600 text-white flex items-center justify-center shrink-0">
                  <RotateCcw class="w-4 h-4 animate-spin-slow" />
                </div>
                <span>PERMINTAAN REVISI / PERBAIKAN DARI ADMIN</span>
              </div>
              <span v-if="latestRevision?.target_stage" class="px-2 py-0.5 rounded-xs text-[10px] font-black uppercase bg-[#0B0C0E] text-rose-400 border border-rose-500/50">
                TARGET: {{ latestRevision.target_stage }}
              </span>
            </div>

            <div class="bg-[#0B0C0E] p-3.5 rounded-xs border border-rose-500/40 space-y-1.5">
              <div class="text-[10px] font-bold uppercase tracking-wider text-rose-400">Instruksi Perbaikan Supervisor:</div>
              <p class="text-xs text-white font-sans font-medium leading-relaxed whitespace-pre-line">
                "{{ latestRevision?.reason || selectedTask.revisions?.[0]?.reason || 'Mohon lengkapi dan perbaiki bukti dokumentasi foto sesuai standar mutu.' }}"
              </p>
              <div v-if="latestRevision?.requested_at" class="text-[10px] text-slate-400 pt-1">
                🕒 Diajukan pada: {{ new Date(latestRevision.requested_at).toLocaleString('id-ID') }}
              </div>
            </div>

            <div class="flex items-center gap-2 text-xs text-rose-300 font-sans">
              <AlertCircle class="w-4 h-4 shrink-0 text-rose-400" />
              <span>Silakan unggah ulang foto pada sub-pekerjaan terkait, kemudian klik tombol <strong>Ajukan Seluruh Pekerjaan</strong> di bawah.</span>
            </div>
          </div>

          <!-- Stepper Progress -->
          <div class="bg-[#0E1117] rounded-xs p-4 border border-[#232A36]">
            <StepperProgress :status="selectedTask.status" :progressPercent="selectedTask.progress_percent" />
          </div>

          <!-- Step 1: GPS Check-In (Unified 1x Check-in for whole branch) -->
          <div class="bg-[#0E1117] rounded-xs p-4 sm:p-5 space-y-4 border border-[#232A36]">
            <div class="flex items-center justify-between border-b border-[#232A36] pb-3 flex-wrap gap-2 font-mono">
              <h4 class="font-bold text-xs text-white flex items-center gap-2 uppercase tracking-wider">
                <Navigation class="w-4 h-4 text-[#FACC15]" />
                <span>LANGKAH 1: Check-In GPS Terpadu Cabang</span>
              </h4>
              <span
                v-if="isCheckedIn"
                class="px-2.5 py-1 rounded-xs text-[10px] font-bold bg-emerald-950/70 text-emerald-300 border border-emerald-500/50 flex items-center gap-1.5 uppercase tracking-wider"
              >
                <CheckCircle2 class="w-3.5 h-3.5 text-emerald-400" /> TERVERIFIKASI (1x Untuk Semua Item)
              </span>
              <span
                v-else-if="!selectedTask?.require_checkin"
                class="px-2.5 py-0.5 rounded-xs text-[10px] font-bold bg-[#1C2028] text-slate-300 border border-[#232A36] flex items-center gap-1 uppercase tracking-wider"
              >
                <span>Bebas Lokasi (Opsional)</span>
              </span>
              <span
                v-else
                class="px-2.5 py-0.5 rounded-xs text-[10px] font-bold bg-amber-950/70 text-amber-300 border border-amber-500/50 uppercase tracking-wider"
              >
                Wajib Check-In
              </span>
            </div>

            <!-- Notice if require_checkin is disabled -->
            <div v-if="!selectedTask?.require_checkin && !isCheckedIn" class="p-3 bg-[#0B0C0E] border border-indigo-500/40 rounded-xs text-xs text-slate-300 flex items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <CheckCircle2 class="w-4 h-4 text-indigo-400 shrink-0" />
                <span>Pekerjaan ini berstatus <strong>Bebas Lokasi</strong>. Anda dapat langsung mengunggah foto bukti di Langkah 2, atau melakukan check-in lokasi di bawah.</span>
              </div>
            </div>

            <GeolocationCapture @locationCaptured="setCapturedGps" />

            <div v-if="!isCheckedIn" class="pt-2 space-y-2.5 font-mono">
              <input
                type="text"
                placeholder="Catatan penanda lokasi saat tiba di cabang (opsional)..."
                v-model="addressNote"
                class="w-full px-3.5 py-2.5 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] text-white rounded-xs text-xs focus:outline-none transition-colors"
              />
              <button
                type="button"
                @click="handlePerformCheckIn"
                :disabled="checkingIn || !capturedGps"
                class="w-full py-2.5 bg-[#FACC15] hover:bg-[#FFE033] disabled:opacity-50 text-[#0B0C0E] font-bold text-xs rounded-xs shadow-xs transition-all active:scale-98 flex items-center justify-center gap-2 cursor-pointer uppercase tracking-wider"
              >
                <Navigation class="w-4 h-4" />
                <span>{{ checkingIn ? 'Mencatat Check-In Real-time...' : (selectedTask?.require_checkin ? 'Mulai Pekerjaan & Check-In Cabang Sekarang' : 'Catat Presensi Lokasi GPS (Opsional)') }}</span>
              </button>
            </div>
            <div v-else class="p-3.5 bg-[#0B0C0E] border border-emerald-500/40 rounded-xs text-xs text-slate-200 flex items-center justify-between font-mono">
              <div>
                <div class="font-bold text-emerald-400 uppercase tracking-wider">Check-In Resmi Terverifikasi di Server</div>
                <div class="text-[11px] text-slate-400 mt-0.5">
                  {{ new Date(selectedTask.check_ins[0].server_timestamp).toLocaleString('id-ID') }} • GPS: {{ Number(selectedTask.check_ins[0].latitude).toFixed(5) }}, {{ Number(selectedTask.check_ins[0].longitude).toFixed(5) }} (±{{ selectedTask.check_ins[0].accuracy }}m)
                </div>
              </div>
              <CheckCircle2 class="w-5 h-5 text-emerald-400 shrink-0" />
            </div>
          </div>

          <!-- Step 2: Tabbed Sub-Items Evidence Engine -->
          <div class="bg-[#0E1117] rounded-xs p-4 sm:p-5 space-y-4 border border-[#232A36]">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#232A36] pb-3 font-mono">
              <div>
                <h4 class="font-bold text-xs text-white flex items-center gap-2 uppercase tracking-wider">
                  <Camera class="w-4 h-4 text-[#FACC15]" />
                  <span>LANGKAH 2: Dokumentasi Foto Bukti per Sub-Pekerjaan</span>
                </h4>
                <p class="text-[11px] text-slate-400 mt-0.5 font-sans">Pilih tab sub-pekerjaan untuk mengunggah bukti foto masing-masing item.</p>
              </div>
              <span class="text-[10px] font-bold text-[#FACC15] bg-[#0B0C0E] px-2.5 py-1 rounded-xs border border-[#232A36] self-start sm:self-auto uppercase tracking-wider">
                {{ completedItemsCount }}/{{ taskItems.length }} Item Selesai
              </span>
            </div>

            <!-- Sub-Item Tabs Navigation -->
            <div class="flex gap-2 overflow-x-auto pb-1.5 custom-scrollbar font-mono">
              <button
                v-for="(itm, idx) in taskItems"
                :key="itm.id || idx"
                @click="activeItemIndex = idx"
                :class="[
                  'flex items-center gap-2 px-3 py-2 rounded-xs text-xs font-bold transition-all shrink-0 cursor-pointer uppercase tracking-wider border',
                  activeItemIndex === idx
                    ? 'bg-[#1C2028] text-[#FACC15] border-l-2 border-[#FACC15] border-y border-r border-[#232A36] shadow-xs'
                    : 'bg-[#0B0C0E] hover:bg-[#13171F] text-slate-400 hover:text-white border-[#232A36]'
                ]"
              >
                <span class="w-4 h-4 rounded-xs flex items-center justify-center text-[10px]" :class="activeItemIndex === idx ? 'bg-[#FACC15] text-[#0B0C0E]' : 'bg-[#13171F] text-slate-400'">
                  {{ idx + 1 }}
                </span>
                <span class="truncate max-w-[160px]">{{ itm.item_name }}</span>
                <span v-if="itm.is_addendum" class="px-1.5 py-0.2 rounded-xs text-[8px] font-black uppercase bg-amber-500 text-slate-950 shrink-0">
                  + Addendum
                </span>
                <CheckCircle2 v-if="isItemComplete(itm)" class="w-3.5 h-3.5 text-emerald-400 shrink-0" />
              </button>
            </div>

            <!-- Active Sub-Item Header Info -->
            <div v-if="activeItem" class="p-3 bg-[#0B0C0E] border border-[#232A36] rounded-xs flex items-center justify-between text-xs font-mono">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-bold text-white uppercase">{{ activeItem.item_name }}</span>
                <span v-if="activeItem.is_addendum" class="px-2 py-0.5 rounded-xs text-[9px] font-black uppercase bg-amber-500 text-slate-950 animate-pulse">
                  + ADDENDUM
                </span>
                <span class="text-[11px] text-slate-400">({{ activeItem.doc_mode }})</span>
              </div>
              <span v-if="isItemComplete(activeItem)" class="px-2 py-0.5 rounded-xs text-[10px] font-bold bg-emerald-950/60 text-emerald-300 border border-emerald-500/40 uppercase tracking-wider">
                Foto Lengkap ✓
              </span>
              <span v-else class="px-2 py-0.5 rounded-xs text-[10px] font-bold bg-amber-950/60 text-amber-300 border border-amber-500/40 uppercase tracking-wider">
                Belum Lengkap
              </span>
            </div>

            <!-- Photo Uploaders for Active Item -->
            <div v-if="activeItem">
              <div v-if="activeItem.doc_mode === 'BEFORE_PROCESS_AFTER'" class="space-y-4">
                <PhotoUploader
                  :workOrderId="selectedTask.id"
                  :itemId="activeItem.id"
                  stage="BEFORE"
                  :requiredCount="1"
                  :existingPhotos="selectedTask.evidence_photos || []"
                  :spkNumber="selectedTask.spk_number"
                  :locationName="selectedTask.location_name"
                  :workOrderTitle="selectedTask.title"
                  :address="selectedTask.address_note || selectedTask.location_name"
                  :targetLat="selectedTask.target_lat"
                  :targetLng="selectedTask.target_lng"
                  :checkInLat="selectedTask.check_ins?.[0]?.latitude"
                  :checkInLng="selectedTask.check_ins?.[0]?.longitude"
                  :useTimestamp="selectedTask.use_timestamp !== false"
                  @uploadSuccess="() => handleSelectTask(selectedTask.id)"
                />
                <PhotoUploader
                  :workOrderId="selectedTask.id"
                  :itemId="activeItem.id"
                  stage="PROCESS"
                  :requiredCount="1"
                  :existingPhotos="selectedTask.evidence_photos || []"
                  :spkNumber="selectedTask.spk_number"
                  :locationName="selectedTask.location_name"
                  :workOrderTitle="selectedTask.title"
                  :address="selectedTask.address_note || selectedTask.location_name"
                  :targetLat="selectedTask.target_lat"
                  :targetLng="selectedTask.target_lng"
                  :checkInLat="selectedTask.check_ins?.[0]?.latitude"
                  :checkInLng="selectedTask.check_ins?.[0]?.longitude"
                  :useTimestamp="selectedTask.use_timestamp !== false"
                  @uploadSuccess="() => handleSelectTask(selectedTask.id)"
                />
                <PhotoUploader
                  :workOrderId="selectedTask.id"
                  :itemId="activeItem.id"
                  stage="AFTER"
                  :requiredCount="1"
                  :existingPhotos="selectedTask.evidence_photos || []"
                  :spkNumber="selectedTask.spk_number"
                  :locationName="selectedTask.location_name"
                  :workOrderTitle="selectedTask.title"
                  :address="selectedTask.address_note || selectedTask.location_name"
                  :targetLat="selectedTask.target_lat"
                  :targetLng="selectedTask.target_lng"
                  :checkInLat="selectedTask.check_ins?.[0]?.latitude"
                  :checkInLng="selectedTask.check_ins?.[0]?.longitude"
                  :useTimestamp="selectedTask.use_timestamp !== false"
                  @uploadSuccess="() => handleSelectTask(selectedTask.id)"
                />
              </div>
              <div v-else class="space-y-4">
                <PhotoUploader
                  :workOrderId="selectedTask.id"
                  :itemId="activeItem.id"
                  stage="AFTER"
                  :requiredCount="1"
                  :existingPhotos="selectedTask.evidence_photos || []"
                  :spkNumber="selectedTask.spk_number"
                  :locationName="selectedTask.location_name"
                  :workOrderTitle="selectedTask.title"
                  :address="selectedTask.address_note || selectedTask.location_name"
                  :targetLat="selectedTask.target_lat"
                  :targetLng="selectedTask.target_lng"
                  :checkInLat="selectedTask.check_ins?.[0]?.latitude"
                  :checkInLng="selectedTask.check_ins?.[0]?.longitude"
                  :useTimestamp="selectedTask.use_timestamp !== false"
                  @uploadSuccess="() => handleSelectTask(selectedTask.id)"
                />
              </div>
            </div>
          </div>

          <!-- Step 3: Kendala Teknis Lapangan -->
          <div class="bg-[#0E1117] rounded-xs p-4 sm:p-5 space-y-4 border border-[#232A36] text-xs font-mono">
            <div class="flex items-center justify-between border-b border-[#232A36] pb-3">
              <h4 class="font-bold text-white flex items-center gap-2 uppercase tracking-wider">
                <AlertTriangle class="w-4 h-4 text-[#FACC15]" />
                <span>LANGKAH 3: Laporan Kendala Teknis Cabang (Opsional)</span>
              </h4>
              <span
                v-if="selectedTask.issues && selectedTask.issues.length > 0"
                class="px-2 py-0.5 rounded-xs text-[10px] font-bold bg-amber-950/60 text-amber-300 border border-amber-500/50 uppercase"
              >
                {{ selectedTask.issues.length }} Catatan Kendala
              </span>
            </div>

            <!-- Existing Issues Timeline Log -->
            <div v-if="selectedTask.issues && selectedTask.issues.length > 0" class="space-y-2">
              <span class="font-bold text-slate-300 text-[11px] block uppercase tracking-wider">// RIWAYAT KENDALA YANG PERNAH DILAPORKAN:</span>
              <div
                v-for="iss in selectedTask.issues"
                :key="iss.id"
                class="p-3 bg-[#0B0C0E] border border-[#232A36] rounded-xs space-y-1.5"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-1.5">
                    <span
                      :class="[
                        'px-2 py-0.2 rounded-xs text-[9px] font-bold uppercase',
                        iss.status === 'RESOLVED' ? 'bg-emerald-950/60 text-emerald-300 border border-emerald-500/40' : 'bg-rose-950/60 text-rose-300 border border-rose-500/40'
                      ]"
                    >
                      {{ iss.status === 'RESOLVED' ? 'TERATASI ✓' : 'BUTUH TINDAKAN' }}
                    </span>
                    <span class="font-bold text-white text-[11px] uppercase">{{ iss.issue_type || 'Kendala Teknis' }}</span>
                  </div>
                  <span class="text-[10px] font-mono text-slate-400">
                    {{ new Date(iss.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                  </span>
                </div>

                <p class="text-slate-300 text-[11px] font-sans leading-relaxed">
                  "{{ iss.notes }}"
                </p>

                <!-- Resolution Solution if any -->
                <div v-if="iss.resolution_notes" class="p-2 bg-emerald-950/40 rounded-xs text-[10px] text-emerald-300 border border-emerald-500/40 font-mono">
                  <strong>Solusi Pengawas:</strong> {{ iss.resolution_notes }}
                </div>
              </div>
            </div>

            <!-- Add / Report New Issue Form -->
            <div class="p-4 bg-[#0B0C0E] border border-[#232A36] rounded-xs space-y-3">
              <div class="flex items-center justify-between">
                <label class="font-bold text-white text-xs flex items-center gap-1.5 uppercase tracking-wider">
                  <AlertCircle class="w-3.5 h-3.5 text-[#FACC15]" />
                  <span>Tambah Laporan Kendala Teknis Baru:</span>
                </label>
              </div>

              <div>
                <label class="block font-bold text-slate-300 mb-1 text-[11px] uppercase tracking-wider">Kategori Kendala:</label>
                <select
                  v-model="issueType"
                  class="w-full px-3 py-1.5 border border-[#232A36] rounded-xs bg-[#13171F] text-white focus:border-[#FACC15] focus:outline-none text-xs font-mono"
                >
                  <option value="Cuaca / Hujan">Cuaca Buruk / Hujan Lebat</option>
                  <option value="Akses Lokasi">Akses Lokasi Ditutup / Dibatasi Security</option>
                  <option value="Material Kurang">Kekurangan Material / Komponen Rusak</option>
                  <option value="Izin Lingkungan">Kendala Perizinan Warga / Kawasan</option>
                  <option value="Listrik Mati">Sumber Daya Listrik Tidak Tersedia</option>
                  <option value="Lainnya">Lainnya</option>
                </select>
              </div>

              <div>
                <label class="block font-bold text-slate-300 mb-1 text-[11px] uppercase tracking-wider">Deskripsi Kronologi Kendala:</label>
                <textarea
                  rows="2"
                  placeholder="Jelaskan kendala lapangan yang dialami..."
                  v-model="issueNotes"
                  class="w-full px-3 py-1.5 border border-[#232A36] rounded-xs bg-[#13171F] text-white focus:border-[#FACC15] focus:outline-none text-xs leading-relaxed font-sans"
                />
              </div>

              <div class="flex items-center justify-between pt-1">
                <span class="text-[10px] text-slate-400">
                  Laporan akan langsung tercatat di sistem pengawas & Client.
                </span>
                <button
                  type="button"
                  @click="handleSaveIssue"
                  :disabled="savingIssue || !issueNotes.trim()"
                  class="px-3.5 py-1.5 bg-[#1C2028] hover:bg-[#232A36] text-[#FACC15] border border-[#232A36] hover:border-[#FACC15]/40 font-bold text-xs rounded-xs cursor-pointer active:scale-95 transition-all disabled:opacity-50 uppercase tracking-wider"
                >
                  {{ savingIssue ? 'Menyimpan...' : '+ Simpan Laporan Kendala' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Step 4: Upload Foto Berkas Fisik BA Opname & Generate Model Berkas PDF -->
          <div
            class="bg-[#0E1117] rounded-xs p-4 sm:p-5 space-y-4 border text-xs transition-all font-mono"
            :class="selectedTask?.require_ba_doc ? 'border-amber-500/60' : 'border-[#232A36]'"
          >
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#232A36] pb-3">
              <div>
                <h4 class="font-bold text-white flex items-center gap-2 uppercase tracking-wider">
                  <FileText class="w-4 h-4 text-[#FACC15]" />
                  <span>LANGKAH 4: Upload Foto Berkas & Generate PDF BA Opname</span>
                  <span
                    v-if="selectedTask?.require_ba_doc"
                    class="px-2 py-0.5 rounded-xs text-[10px] font-bold bg-amber-950/60 text-amber-300 border border-amber-500/50 uppercase"
                  >
                    Wajib Sebelum Submit
                  </span>
                  <span
                    v-else
                    class="px-2 py-0.5 rounded-xs text-[10px] font-bold bg-[#1C2028] text-slate-300 border border-[#232A36] uppercase"
                  >
                    Standar Resmi
                  </span>
                </h4>
                <p class="text-[11px] text-slate-400 mt-1 font-sans">
                  Unggah foto bukti lembar fisik dokumen Berita Acara (BA) Opname resmi yang telah ditandatangani dan dicap oleh Kepala Toko / PIC Cabang, lalu generate berkas model format PDF.
                </p>
              </div>

              <div v-if="hasBaPhotoUploaded" class="self-start sm:self-auto flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-400 bg-emerald-950/60 px-2.5 py-1 rounded-xs border border-emerald-500/40 uppercase">
                  <CheckCircle2 class="w-3.5 h-3.5 text-emerald-400" />
                  {{ baPhotos.length }} Foto Berkas Terlampir ✓
                </span>
              </div>
            </div>

            <!-- Foto Uploader untuk Lembar BA Opname -->
            <PhotoUploader
              :workOrderId="selectedTask.id"
              :itemId="null"
              stage="BA_OPNAME"
              :requiredCount="1"
              :existingPhotos="selectedTask.evidence_photos || []"
              :spkNumber="selectedTask.spk_number"
              :locationName="selectedTask.location_name"
              :workOrderTitle="selectedTask.title"
              :address="selectedTask.address_note || selectedTask.location_name"
              :targetLat="selectedTask.target_lat"
              :targetLng="selectedTask.target_lng"
              :checkInLat="selectedTask.check_ins?.[0]?.latitude"
              :checkInLng="selectedTask.check_ins?.[0]?.longitude"
              :useTimestamp="selectedTask.use_timestamp !== false"
              @uploadSuccess="() => handleSelectTask(selectedTask.id)"
            />

            <!-- Panel Khusus Generate Model Berkas Format PDF -->
            <div
              v-if="hasBaPhotoUploaded"
              class="p-4 bg-[#0B0C0E] border border-[#232A36] rounded-xs space-y-3"
            >
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                  <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-bold text-white text-xs uppercase tracking-wider">
                      MODEL BERKAS DIGITAL (FORMAT PDF RESMI)
                    </span>
                    <span v-if="generatedPdfData" class="px-1.5 py-0.2 rounded-xs bg-emerald-950 text-emerald-300 border border-emerald-500/40 text-[9px] font-bold">
                      PDF SIAP ✓
                    </span>
                  </div>
                  <p class="text-[11px] text-slate-400 font-sans mt-0.5">
                    Kompilasi foto fisik lembar BA Opname ke dalam format dokumen A4 resmi standar SGX lengkap dengan kop, nomor SPK, dan stempel verifikasi digital.
                  </p>
                </div>

                <div class="flex items-center gap-2 flex-wrap font-mono">
                  <!-- Generate PDF Button -->
                  <button
                    type="button"
                    @click="handleGeneratePdf"
                    :disabled="generatingPdf"
                    class="px-3.5 py-2 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] font-bold text-xs rounded-xs flex items-center gap-2 transition-all cursor-pointer active:scale-95 disabled:opacity-50 uppercase tracking-wider shadow-xs"
                    title="Generate foto berkas menjadi dokumen PDF format A4"
                  >
                    <FileText class="w-3.5 h-3.5 text-[#0B0C0E]" />
                    <span>{{ generatingPdf ? 'Membuat PDF...' : (generatedPdfData ? 'Generate Ulang PDF' : 'Generate Berkas PDF (A4)') }}</span>
                  </button>

                  <!-- Preview PDF Button -->
                  <button
                    v-if="generatedPdfData"
                    type="button"
                    @click="showPdfPreviewModal = true"
                    class="px-3 py-2 bg-[#1C2028] hover:bg-[#232A36] text-white border border-[#232A36] font-bold text-xs rounded-xs flex items-center gap-1.5 cursor-pointer uppercase tracking-wider"
                    title="Lihat lembar pratinjau PDF"
                  >
                    <Eye class="w-3.5 h-3.5 text-sky-400" />
                    <span>Pratinjau</span>
                  </button>

                  <!-- Download PDF Button -->
                  <button
                    v-if="generatedPdfData"
                    type="button"
                    @click="handleDownloadPdf"
                    class="px-3 py-2 bg-emerald-950 hover:bg-emerald-900 text-emerald-300 border border-emerald-500/50 font-bold text-xs rounded-xs flex items-center gap-1.5 cursor-pointer uppercase tracking-wider active:scale-95"
                    title="Unduh file dokumen PDF ke perangkat"
                  >
                    <Download class="w-3.5 h-3.5 text-emerald-400" />
                    <span>Unduh PDF</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 5: Submission Validation Gate -->
          <div class="bg-[#0B0C0E] rounded-xs p-5 sm:p-6 space-y-4 border border-[#232A36] font-mono">
            <div>
              <h4 class="font-bold text-sm text-white flex items-center gap-2 uppercase tracking-wider">
                <CheckSquare class="w-4 h-4 text-emerald-400" />
                <span>LANGKAH 5: Submit Seluruh Pekerjaan Cabang</span>
              </h4>
              <p class="text-xs text-slate-400 mt-1 font-sans">
                Server validation gate akan memeriksa 1x Check-In GPS, kelengkapan foto dari seluruh sub-pekerjaan, serta berkas fisik BA Opname di cabang ini.
              </p>
            </div>

            <!-- Validation Checklist Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
              <div
                :class="[
                  'p-3.5 rounded-xs border flex items-center gap-3 transition-all',
                  isCheckinSatisfied ? 'bg-emerald-950/60 border-emerald-500/50 text-emerald-300' : 'bg-[#13171F] border-[#232A36] text-slate-400'
                ]"
              >
                <CheckCircle2 :class="['w-4 h-4 shrink-0', isCheckinSatisfied ? 'text-emerald-400' : 'text-slate-500']" />
                <div>
                  <div class="font-bold text-white uppercase">Check-in GPS Cabang</div>
                  <div class="text-[10px] opacity-80">{{ isCheckedIn ? 'Sudah Tervalidasi ✓' : (!selectedTask?.require_checkin ? 'Bebas Lokasi (Opsional) ✓' : 'Belum Check-In') }}</div>
                </div>
              </div>

              <div
                :class="[
                  'p-3.5 rounded-xs border flex items-center gap-3 transition-all',
                  isAllItemsComplete ? 'bg-emerald-950/60 border-emerald-500/50 text-emerald-300' : 'bg-[#13171F] border-[#232A36] text-slate-400'
                ]"
              >
                <CheckCircle2 :class="['w-4 h-4 shrink-0', isAllItemsComplete ? 'text-emerald-400' : 'text-slate-500']" />
                <div>
                  <div class="font-bold text-white uppercase">Foto Sub-Pekerjaan</div>
                  <div class="text-[10px] opacity-80">
                    {{ completedItemsCount }} dari {{ taskItems.length }} Item Lengkap
                  </div>
                </div>
              </div>

              <div
                :class="[
                  'p-3.5 rounded-xs border flex items-center gap-3 transition-all',
                  isBaPhotoSatisfied ? 'bg-emerald-950/60 border-emerald-500/50 text-emerald-300' : 'bg-amber-950/60 border-amber-500/50 text-amber-300'
                ]"
              >
                <CheckCircle2 :class="['w-4 h-4 shrink-0', isBaPhotoSatisfied ? 'text-emerald-400' : 'text-amber-400']" />
                <div>
                  <div class="font-bold text-white uppercase">Berkas BA & PDF</div>
                  <div class="text-[10px] opacity-80">
                    {{ isBaPhotoSatisfied
                      ? (hasBaPhotoUploaded ? 'Foto BA Terlampir ✓' : 'Bebas / Opsional ✓')
                      : 'Wajib Diunggah (Belum Ada)' }}
                  </div>
                </div>
              </div>
            </div>

            <button
              type="button"
              @click="handleSubmitWorkOrder"
              :disabled="submitting || ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW', 'APPROVED', 'COMPLETED', 'BA_OPNAME'].includes(selectedTask?.status)"
              :class="[
                'w-full py-3.5 rounded-xs font-bold text-xs flex items-center justify-center gap-2 shadow-lg transition-all active:scale-[0.99] uppercase tracking-wider',
                ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'].includes(selectedTask?.status)
                  ? 'bg-purple-950 text-purple-300 cursor-not-allowed border border-purple-500/40'
                  : ['APPROVED', 'COMPLETED', 'BA_OPNAME'].includes(selectedTask?.status)
                  ? 'bg-emerald-950 text-emerald-300 cursor-not-allowed border border-emerald-500/40'
                  : canSubmit
                  ? 'bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] cursor-pointer shadow-[#FACC15]/20 font-black text-sm'
                  : 'bg-[#1C2028] hover:bg-[#232A36] text-slate-300 cursor-pointer border border-[#232A36]'
              ]"
            >
              <Send class="w-4 h-4" />
              <span>
                {{ submitting
                  ? 'Memproses Pengajuan...'
                  : ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'].includes(selectedTask?.status)
                  ? 'Pekerjaan Sedang Dalam Review Admin ✓'
                  : ['APPROVED', 'COMPLETED', 'BA_OPNAME'].includes(selectedTask?.status)
                  ? 'Pekerjaan Telah Disetujui & Selesai ✓'
                  : 'Ajukan Seluruh Pekerjaan Cabang untuk Direview Admin' }}
              </span>
            </button>
          </div>
        </div>

        <div v-else class="py-24 text-center bg-[#13171F] rounded-xs text-slate-400 text-xs border border-[#232A36] font-mono space-y-2">
          <Smartphone class="w-10 h-10 text-slate-500 mx-auto mb-2 opacity-50" />
          <p class="font-bold text-white uppercase tracking-wider">PILIH SALAH SATU SPK DARI DAFTAR DI SEBELAH KIRI</p>
          <p class="text-[11px] text-slate-500 font-sans">Untuk membuka lembar kerja lapangan, verifikasi check-in GPS, dan upload bukti foto.</p>
        </div>
      </div>
    </div>

    <!-- Modal Pratinjau Model Berkas PDF BA Opname -->
    <div
      v-if="showPdfPreviewModal && generatedPdfData"
      class="fixed inset-0 z-50 bg-[#0B0C0E]/85 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4 font-mono"
    >
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs max-w-4xl w-full h-[92vh] flex flex-col shadow-2xl overflow-hidden">
        <!-- Modal Header -->
        <div class="p-3.5 sm:px-5 border-b border-[#232A36] flex items-center justify-between bg-[#0E1117] shrink-0">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xs bg-[#0B0C0E] border border-[#FACC15] flex items-center justify-center text-[#FACC15]">
              <FileText class="w-4 h-4" />
            </div>
            <div>
              <h3 class="font-bold text-xs sm:text-sm text-white uppercase tracking-wider">
                PRATINJAU MODEL BERKAS PDF BA OPNAME (A4)
              </h3>
              <p class="text-[10px] text-slate-400">
                SPK: <span class="text-[#FACC15] font-bold">{{ selectedTask?.spk_number }}</span> • {{ generatedPdfData.fileName }}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="handleDownloadPdf"
              class="px-3.5 py-1.5 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] font-bold text-xs rounded-xs flex items-center gap-1.5 uppercase cursor-pointer active:scale-95 transition-all shadow-xs"
              title="Unduh file dokumen PDF resmi"
            >
              <Download class="w-3.5 h-3.5" />
              <span>Unduh PDF</span>
            </button>
            <button
              type="button"
              @click="showPdfPreviewModal = false"
              class="p-1.5 text-slate-400 hover:text-white bg-[#1C2028] hover:bg-[#232A36] border border-[#232A36] rounded-xs transition-colors cursor-pointer"
              title="Tutup Modal"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Embedded PDF Viewer / Object -->
        <div class="flex-1 bg-[#0B0C0E] p-1 overflow-hidden relative flex flex-col">
          <iframe
            :src="generatedPdfData.dataUrl"
            class="w-full h-full border-none rounded-none"
            title="Pratinjau PDF Berita Acara"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../../services/api';
import { generateBaOpnamePdf } from '../../utils/baPdfGenerator';
import StatusBadge from '../../components/StatusBadge.vue';
import GeolocationCapture from '../../components/GeolocationCapture.vue';
import PhotoUploader from '../../components/PhotoUploader.vue';
import StepperProgress from '../../components/StepperProgress.vue';
import {
  Smartphone,
  Navigation,
  Camera,
  AlertTriangle,
  Send,
  CheckCircle2,
  AlertCircle,
  MapPin,
  RotateCcw,
  CheckSquare,
  RefreshCw,
  Layers,
  FileText,
  Download,
  Eye,
  X
} from 'lucide-vue-next';

const props = defineProps({
  initialWorkOrderId: {
    type: Number,
    default: null
  }
});

const tasks = ref([]);
const selectedTask = ref(null);
const loading = ref(true);
const submitting = ref(false);
const activeItemIndex = ref(0);

const latestRevision = computed(() => {
  if (!selectedTask.value?.revisions || selectedTask.value.revisions.length === 0) return null;
  return selectedTask.value.revisions[selectedTask.value.revisions.length - 1];
});

// Check-In State
const capturedGps = ref(null);
const addressNote = ref('');
const checkingIn = ref(false);

// Issue State
const hasIssue = ref(false);
const issueType = ref('Cuaca / Hujan');
const issueNotes = ref('');
const savingIssue = ref(false);

const taskItems = computed(() => {
  if (selectedTask.value?.items && selectedTask.value.items.length > 0) {
    return selectedTask.value.items;
  }
  return [{
    id: null,
    item_name: selectedTask.value?.title || 'Pekerjaan Utama',
    doc_mode: selectedTask.value?.doc_mode || 'BEFORE_PROCESS_AFTER'
  }];
});

const activeItem = computed(() => {
  return taskItems.value[activeItemIndex.value] || taskItems.value[0];
});

function isItemComplete(item) {
  const allPhotos = selectedTask.value?.evidence_photos || [];
  const itemPhotos = allPhotos.filter(p => item.id ? p.item_id === item.id : true);
  const before = itemPhotos.filter(p => p.stage === 'BEFORE').length;
  const process = itemPhotos.filter(p => p.stage === 'PROCESS').length;
  const after = itemPhotos.filter(p => p.stage === 'AFTER').length;

  if (item.doc_mode === 'AFTER_ONLY') {
    return after >= 1;
  }
  return before >= 1 && process >= 1 && after >= 1;
}

const completedItemsCount = computed(() => {
  return taskItems.value.filter(itm => isItemComplete(itm)).length;
});

const isAllItemsComplete = computed(() => {
  return taskItems.value.every(itm => isItemComplete(itm));
});

async function loadTasks(preserveSelectedId = null) {
  loading.value = true;
  try {
    const res = await api.getWorkOrders();
    tasks.value = res.data || [];

    const targetId = preserveSelectedId || props.initialWorkOrderId || selectedTask.value?.id || (tasks.value.length > 0 ? tasks.value[0].id : null);
    if (targetId) {
      await handleSelectTask(targetId);
    }
  } catch (err) {
    console.error('Failed to load field tasks:', err);
  } finally {
    loading.value = false;
  }
}

async function handleSelectTask(id) {
  try {
    const detail = await api.getWorkOrderById(id);
    selectedTask.value = detail.data;
    activeItemIndex.value = 0;
    generatedPdfData.value = null;
    hasIssue.value = detail.data.issues?.length > 0 && detail.data.issues[0].has_issue === 1;
    if (hasIssue.value) {
      issueType.value = detail.data.issues[0].issue_type || 'Cuaca / Hujan';
      issueNotes.value = detail.data.issues[0].notes || '';
    }
  } catch (err) {
    alert(err.message);
  }
}

function setCapturedGps(coords) {
  capturedGps.value = coords;
}

async function handlePerformCheckIn() {
  if (!capturedGps.value) {
    alert('Koordinat GPS belum terdeteksi. Mohon izinkan akses lokasi.');
    return;
  }

  checkingIn.value = true;
  try {
    await api.checkIn({
      work_order_id: selectedTask.value.id,
      latitude: capturedGps.value.latitude,
      longitude: capturedGps.value.longitude,
      accuracy: capturedGps.value.accuracy,
      client_timestamp: new Date().toISOString(),
      address_note: addressNote.value || 'Check-in di lokasi cabang'
    });
    alert('Check-In GPS Berhasil! Berlaku untuk seluruh sub-pekerjaan di cabang ini.');
    await handleSelectTask(selectedTask.value.id);
    loadTasks(selectedTask.value.id);
  } catch (err) {
    alert(`Gagal Check-In: ${err.message}`);
  } finally {
    checkingIn.value = false;
  }
}

async function handleSaveIssue() {
  if (!issueNotes.value.trim()) return;
  savingIssue.value = true;
  try {
    await api.reportIssue({
      work_order_id: selectedTask.value.id,
      has_issue: 1,
      issue_type: issueType.value || 'Lainnya',
      notes: issueNotes.value.trim()
    });
    alert('Laporan kendala lapangan berhasil dicatat ke dalam log SPK!');
    issueNotes.value = '';
    await handleSelectTask(selectedTask.value.id);
  } catch (err) {
    alert(`Gagal menyimpan kendala: ${err.message}`);
  } finally {
    savingIssue.value = false;
  }
}

// Validation Gate
const isCheckedIn = computed(() => {
  return selectedTask.value?.check_ins && selectedTask.value.check_ins.length > 0;
});

const isCheckinSatisfied = computed(() => {
  return !selectedTask.value?.require_checkin || isCheckedIn.value;
});

const baPhotos = computed(() => {
  const photos = selectedTask.value?.evidence_photos || [];
  return photos.filter(p => p.stage === 'BA_OPNAME' || p.stage === 'BA');
});

const hasBaPhotoUploaded = computed(() => {
  return baPhotos.value.length > 0;
});

// BA Opname PDF Generation State & Methods
const generatingPdf = ref(false);
const generatedPdfData = ref(null);
const showPdfPreviewModal = ref(false);

async function handleGeneratePdf() {
  if (baPhotos.value.length === 0) {
    alert('Unggah minimal 1 foto lembar fisik Berita Acara (BA) terlebih dahulu sebelum generate PDF.');
    return;
  }
  generatingPdf.value = true;
  try {
    const res = await generateBaOpnamePdf(selectedTask.value, baPhotos.value);
    generatedPdfData.value = res;
    showPdfPreviewModal.value = true;
  } catch (err) {
    alert(`Gagal generate PDF: ${err.message}`);
  } finally {
    generatingPdf.value = false;
  }
}

function handleDownloadPdf() {
  if (!generatedPdfData.value) return;
  generatedPdfData.value.doc.save(generatedPdfData.value.fileName);
}

const isBaPhotoSatisfied = computed(() => {
  if (!selectedTask.value?.require_ba_doc) return true;
  return hasBaPhotoUploaded.value;
});

const canSubmit = computed(() => {
  return isCheckinSatisfied.value &&
    isAllItemsComplete.value &&
    isBaPhotoSatisfied.value &&
    !['SUBMITTED', 'UNDER_REVIEW', 'REVIEW', 'APPROVED', 'COMPLETED', 'BA_OPNAME'].includes(selectedTask.value?.status);
});

async function handleSubmitWorkOrder() {
  if (!isCheckinSatisfied.value) {
    alert('Gagal: Wajib melakukan Check-In GPS di lokasi terlebih dahulu!');
    return;
  }
  if (!isAllItemsComplete.value) {
    alert('Gagal: Dokumentasi foto dari seluruh sub-pekerjaan belum lengkap.');
    return;
  }
  if (!isBaPhotoSatisfied.value) {
    alert('Gagal: SPK ini mewajibkan foto bukti berkas fisik Berita Acara (BA) Opname yang telah ditandatangani dan dicap toko sebelum dapat diajukan!');
    return;
  }
  if (!selectedTask.value?.id) {
    alert('Gagal: Data SPK tidak valid.');
    return;
  }

  submitting.value = true;
  try {
    const res = await api.submitWorkOrder(selectedTask.value.id);
    alert(res.message || 'Pekerjaan cabang berhasil diajukan untuk direview Admin!');
    await handleSelectTask(selectedTask.value.id);
    await loadTasks(selectedTask.value.id);
  } catch (err) {
    alert(`Gagal Submit: ${err.message || 'Terjadi kendala saat mengajukan'}`);
  } finally {
    submitting.value = false;
  }
}

onMounted(() => {
  loadTasks();
});
</script>
