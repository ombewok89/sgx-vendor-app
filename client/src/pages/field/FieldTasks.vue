<template>
  <div class="space-y-5">
    <!-- Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-brand-600/20">
            <Smartphone class="w-4 h-4" />
          </div>
          <span>Pekerjaan Lapangan Saya</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1 font-medium">1x Check-In GPS terpadu per cabang dan dokumentasikan sub-pekerjaan secara terorganisir.</p>
      </div>
      <button
        @click="loadTasks()"
        class="self-start sm:self-auto px-3.5 py-2 glass-card hover:bg-white rounded-xl text-slate-700 hover:text-slate-900 text-xs font-bold flex items-center gap-2 shadow-xs transition-all duration-200 active:scale-95 border border-slate-200/80"
      >
        <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" />
        <span>Perbarui Daftar</span>
      </button>
    </div>

    <!-- Mobile Horizontal Task Switcher Carousel (Visible on Mobile Only) -->
    <div class="block lg:hidden space-y-2">
      <div class="flex items-center justify-between px-1">
        <h3 class="font-bold text-xs text-slate-500 uppercase tracking-wider">
          Pilih SPK Lapangan ({{ tasks.length }})
        </h3>
        <span class="text-[10px] font-semibold bg-brand-50 text-brand-700 px-2 py-0.5 rounded-full border border-brand-200">
          Geser untuk memilih
        </span>
      </div>

      <div class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-none snap-x snap-mandatory">
        <div
          v-for="task in tasks"
          :key="task.id"
          @click="handleSelectTask(task.id)"
          :class="[
            'min-w-[240px] max-w-[280px] p-3.5 rounded-2xl cursor-pointer transition-all duration-200 snap-start flex-shrink-0 relative overflow-hidden border',
            selectedTask?.id === task.id
              ? 'bg-slate-900 text-white border-slate-800 shadow-lg shadow-slate-900/20'
              : 'bg-white text-slate-900 border-slate-200 hover:border-slate-300'
          ]"
        >
          <div class="flex items-center justify-between gap-1.5 mb-1.5">
            <span
              :class="[
                'font-mono font-black text-[11px] px-2 py-0.5 rounded-md',
                selectedTask?.id === task.id ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-800'
              ]"
            >
              {{ task.spk_number }}
            </span>
            <StatusBadge :status="task.status" />
          </div>

          <div class="font-bold text-xs line-clamp-1 mb-1" :class="selectedTask?.id === task.id ? 'text-white' : 'text-slate-900'">
            {{ task.title || task.location_name }}
          </div>

          <div class="text-[11px] flex items-center justify-between" :class="selectedTask?.id === task.id ? 'text-slate-300' : 'text-slate-500'">
            <span class="truncate max-w-[140px]">📍 {{ task.location_name }}</span>
            <span class="font-bold font-mono text-emerald-400">{{ task.progress_percent || 0 }}%</span>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Desktop Task Cards List (Visible on Large Screens) -->
      <div class="hidden lg:block space-y-3">
        <div class="flex items-center justify-between px-1">
          <h3 class="font-bold text-xs text-slate-500 uppercase tracking-wider">
            Kartu Tugas Cabang ({{ tasks.length }})
          </h3>
          <span class="text-[10px] font-semibold bg-brand-50 text-brand-700 px-2.5 py-0.5 rounded-full border border-brand-200">
            Field Mode
          </span>
        </div>

        <div v-if="tasks.length > 0" class="space-y-3">
          <div
            v-for="task in tasks"
            :key="task.id"
            @click="handleSelectTask(task.id)"
            :class="[
              'p-4 rounded-2xl cursor-pointer transition-all duration-300 relative overflow-hidden',
              selectedTask?.id === task.id
                ? 'glass-card border-brand-500/80 shadow-glow-brand ring-2 ring-brand-500/20 translate-x-1'
                : 'glass-card glass-card-hover border-white/60 shadow-glass'
            ]"
          >
            <!-- Top Gradient Accent line if selected -->
            <div
              v-if="selectedTask?.id === task.id"
              class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-brand-600 via-indigo-500 to-emerald-500"
            />

            <div class="flex items-center justify-between mb-2">
              <span class="font-mono font-black text-xs text-slate-800 bg-slate-100/90 px-2 py-0.5 rounded-md border border-slate-200/60">
                {{ task.spk_number }}
              </span>
              <StatusBadge :status="task.status" />
            </div>

            <div class="font-bold text-slate-900 text-sm mb-1.5 line-clamp-1 group-hover:text-brand-700 transition-colors">
              {{ task.title }}
            </div>

            <div class="text-slate-500 text-xs flex items-center gap-1.5 mb-2 font-medium">
              <MapPin class="w-3.5 h-3.5 text-brand-500 shrink-0" />
              <span class="truncate">{{ task.location_name }}</span>
            </div>

            <!-- Multi-Item Badge -->
            <div class="flex items-center gap-1.5 mb-3">
              <span class="px-2 py-0.5 bg-brand-50 border border-brand-200/70 text-brand-700 font-bold text-[10px] rounded-md flex items-center gap-1">
                <Layers class="w-3 h-3" />
                {{ task.items?.length || 1 }} Sub-Pekerjaan
              </span>
            </div>

            <!-- Mini Progress bar on Task Card -->
            <div class="space-y-1 pt-2 border-t border-slate-100/80">
              <div class="flex items-center justify-between text-[10px] font-mono">
                <span class="text-slate-400">Target: {{ task.deadline }}</span>
                <span class="font-bold text-brand-700">{{ task.progress_percent }}%</span>
              </div>
              <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div
                  class="h-full bg-gradient-to-r from-brand-600 to-emerald-500 rounded-full transition-all duration-500"
                  :style="{ width: `${task.progress_percent}%` }"
                />
              </div>
            </div>
          </div>
        </div>

        <div v-else class="p-10 text-center glass-card rounded-2xl text-slate-400 text-xs border border-white/60">
          Belum ada penugasan pekerjaan untuk akun Anda.
        </div>
      </div>

      <!-- Task Execution Workspace -->
      <div class="lg:col-span-2 space-y-5">
        <div v-if="selectedTask" class="glass-card rounded-3xl p-5 sm:p-7 shadow-glass border border-white/80 space-y-6">
          <!-- Task Header -->
          <div class="border-b border-slate-200/80 pb-5">
            <div class="flex items-center justify-between gap-2 mb-2">
              <div class="flex items-center gap-2">
                <span class="font-mono font-black text-xs bg-brand-50 border border-brand-200 text-brand-800 px-2.5 py-1 rounded-lg">
                  SPK: {{ selectedTask.spk_number }}
                </span>
                <span class="px-2.5 py-1 bg-indigo-50 border border-indigo-200 text-indigo-800 text-xs font-bold rounded-lg flex items-center gap-1">
                  <Layers class="w-3.5 h-3.5" />
                  <span>{{ taskItems.length }} Sub-Pekerjaan</span>
                </span>
              </div>
              <StatusBadge :status="selectedTask.status" />
            </div>
            <h3 class="font-black text-slate-900 text-xl tracking-tight mt-1">{{ selectedTask.title }}</h3>
            <div class="text-xs text-slate-500 flex items-center gap-1.5 mt-2 font-medium">
              <MapPin class="w-4 h-4 text-brand-500" />
              <span>{{ selectedTask.location_name }} <strong class="text-slate-700">(Area: {{ selectedTask.area_name }})</strong></span>
            </div>
          </div>

          <!-- SPK Notes & Special Instructions from Admin -->
          <div
            v-if="selectedTask.notes"
            class="p-4 bg-gradient-to-r from-indigo-50/90 via-purple-50/80 to-blue-50/90 border border-indigo-200/90 rounded-2xl space-y-1.5 shadow-xs"
          >
            <div class="flex items-center gap-2 text-indigo-900 font-bold text-xs">
              <FileText class="w-4 h-4 text-indigo-600 shrink-0" />
              <span>CATATAN & INSTRUKSI KHUSUS PEKERJAAN (DARI ADMIN):</span>
            </div>
            <p class="text-xs text-indigo-950 font-medium whitespace-pre-line pl-6 leading-relaxed">
              {{ selectedTask.notes }}
            </p>
          </div>

          <!-- Revision Alert if in REVISION state -->
          <div
            v-if="selectedTask.status === 'REVISION'"
            class="p-5 bg-gradient-to-br from-rose-50 via-rose-100/60 to-red-50 border-2 border-rose-400/80 rounded-3xl space-y-3 shadow-md animate-fade-in"
          >
            <div class="flex items-center justify-between">
              <div class="font-black text-xs text-rose-950 flex items-center gap-2 tracking-wide">
                <div class="w-7 h-7 rounded-xl bg-rose-600 text-white flex items-center justify-center shadow-xs">
                  <RotateCcw class="w-4 h-4 animate-spin-slow" />
                </div>
                <span>PERMINTAAN REVISI / PERBAIKAN DARI ADMIN</span>
              </div>
              <span v-if="latestRevision?.target_stage" class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase bg-rose-200 text-rose-950 border border-rose-300">
                Target: {{ latestRevision.target_stage }}
              </span>
            </div>

            <div class="bg-white/95 p-4 rounded-2xl border border-rose-200 shadow-inner space-y-1.5">
              <div class="text-[10px] font-bold uppercase tracking-wider text-rose-700">Alasan & Instruksi Perbaikan:</div>
              <p class="text-xs text-slate-900 font-bold leading-relaxed whitespace-pre-line">
                "{{ latestRevision?.reason || selectedTask.revisions?.[0]?.reason || 'Mohon lengkapi dan perbaiki bukti dokumentasi foto sesuai standar mutu.' }}"
              </p>
              <div v-if="latestRevision?.requested_at" class="text-[10px] font-mono text-slate-400 pt-1">
                🕒 Diajukan pada: {{ new Date(latestRevision.requested_at).toLocaleString('id-ID') }}
              </div>
            </div>

            <div class="flex items-center gap-2 text-[11px] text-rose-700 font-medium pl-1">
              <AlertCircle class="w-4 h-4 shrink-0 text-rose-600" />
              <span>Silakan perbaiki bukti foto pada sub-pekerjaan terkait, lalu klik tombol <strong>Ajukan Ulang ke Admin</strong> di bawah.</span>
            </div>
          </div>

          <!-- Stepper Progress -->
          <div class="glass-card rounded-2xl p-4 border border-white/60">
            <StepperProgress :status="selectedTask.status" :progressPercent="selectedTask.progress_percent" />
          </div>

          <!-- Step 1: GPS Check-In (Unified 1x Check-in for whole branch) -->
          <div class="glass-card rounded-2xl p-5 space-y-4 border border-white/70">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <h4 class="font-bold text-xs text-slate-900 flex items-center gap-2">
                <Navigation class="w-4 h-4 text-brand-600" />
                <span>LANGKAH 1: Check-In GPS Terpadu Lokasi Cabang</span>
              </h4>
              <span
                v-if="isCheckedIn"
                class="px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-800 border border-emerald-300 flex items-center gap-1.5"
              >
                <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" /> TERVERIFIKASI (1x Untuk Semua Item)
              </span>
              <span
                v-else-if="!selectedTask?.require_checkin"
                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300 flex items-center gap-1"
              >
                <span>Bebas Lokasi (Opsional)</span>
              </span>
              <span
                v-else
                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-800 border border-amber-300"
              >
                Wajib Check-In
              </span>
            </div>

            <!-- Notice if require_checkin is disabled -->
            <div v-if="!selectedTask?.require_checkin && !isCheckedIn" class="p-3 bg-indigo-50/70 border border-indigo-200 rounded-xl text-xs text-indigo-950 flex items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <CheckCircle2 class="w-4 h-4 text-indigo-600 shrink-0" />
                <span>Pekerjaan ini berstatus <strong>Bebas Lokasi</strong>. Anda dapat langsung mengunggah foto bukti di Langkah 2, atau melakukan check-in lokasi di bawah.</span>
              </div>
            </div>

            <GeolocationCapture @locationCaptured="setCapturedGps" />

            <div v-if="!isCheckedIn" class="pt-2 space-y-2.5">
              <input
                type="text"
                placeholder="Catatan penanda lokasi saat tiba di cabang (opsional)..."
                v-model="addressNote"
                class="w-full px-3.5 py-2.5 bg-white/90 border border-slate-200/80 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none transition-all shadow-xs"
              />
              <button
                type="button"
                @click="handlePerformCheckIn"
                :disabled="checkingIn || !capturedGps"
                class="w-full py-3 bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 hover:from-brand-800 hover:to-brand-600 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-900/20 transition-all active:scale-98 flex items-center justify-center gap-2 cursor-pointer"
              >
                <Navigation class="w-4 h-4" />
                <span>{{ checkingIn ? 'Mencatat Check-In Real-time...' : (selectedTask?.require_checkin ? 'Mulai Pekerjaan & Check-In Cabang Sekarang' : 'Catat Presensi Lokasi GPS (Opsional)') }}</span>
              </button>
            </div>
            <div v-else class="p-3.5 bg-emerald-500/10 border border-emerald-200 rounded-xl text-xs text-emerald-950 flex items-center justify-between">
              <div>
                <div class="font-bold text-emerald-900">Check-In Resmi Terverifikasi di Server</div>
                <div class="text-[11px] text-emerald-700 font-mono mt-0.5">
                  {{ new Date(selectedTask.check_ins[0].server_timestamp).toLocaleString('id-ID') }} • GPS: {{ Number(selectedTask.check_ins[0].latitude).toFixed(5) }}, {{ Number(selectedTask.check_ins[0].longitude).toFixed(5) }} (±{{ selectedTask.check_ins[0].accuracy }}m)
                </div>
              </div>
              <CheckCircle2 class="w-5 h-5 text-emerald-600 shrink-0" />
            </div>
          </div>

          <!-- Step 2: Tabbed Sub-Items Evidence Engine -->
          <div class="glass-card rounded-2xl p-5 space-y-4 border border-white/70">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
              <div>
                <h4 class="font-bold text-xs text-slate-900 flex items-center gap-2">
                  <Camera class="w-4 h-4 text-brand-600" />
                  <span>LANGKAH 2: Dokumentasi Foto Bukti per Sub-Pekerjaan</span>
                </h4>
                <p class="text-[11px] text-slate-500 mt-0.5">Pilih tab pekerjaan untuk mengunggah bukti foto masing-masing item.</p>
              </div>
              <span class="text-[10px] font-bold text-brand-800 bg-brand-50 px-2.5 py-1 rounded-full border border-brand-200 self-start sm:self-auto">
                {{ completedItemsCount }}/{{ taskItems.length }} Item Selesai
              </span>
            </div>

            <!-- Sub-Item Tabs Navigation -->
            <div class="flex gap-2 overflow-x-auto pb-1.5 custom-scrollbar">
              <button
                v-for="(itm, idx) in taskItems"
                :key="itm.id || idx"
                @click="activeItemIndex = idx"
                :class="[
                  'flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all shrink-0 active:scale-95',
                  activeItemIndex === idx
                    ? 'bg-brand-900 text-white shadow-md shadow-brand-900/20 ring-2 ring-brand-500/30'
                    : 'bg-white/80 hover:bg-white text-slate-700 border border-slate-200/80'
                ]"
              >
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-mono" :class="activeItemIndex === idx ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">
                  {{ idx + 1 }}
                </span>
                <span class="truncate max-w-[160px]">{{ itm.item_name }}</span>
                <span v-if="itm.is_addendum" class="px-1.5 py-0.2 rounded text-[8px] font-black uppercase bg-amber-500 text-white shrink-0 shadow-2xs">
                  + Addendum
                </span>
                <CheckCircle2 v-if="isItemComplete(itm)" class="w-3.5 h-3.5 text-emerald-400 shrink-0" />
              </button>
            </div>

            <!-- Active Sub-Item Header Info -->
            <div v-if="activeItem" class="p-3 bg-brand-50/50 border border-brand-100 rounded-xl flex items-center justify-between text-xs">
              <div class="flex items-center gap-2">
                <span class="font-bold text-slate-900">{{ activeItem.item_name }}</span>
                <span v-if="activeItem.is_addendum" class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-amber-500 text-white shadow-2xs animate-pulse">
                  + PEKERJAAN TAMBAHAN (ADDENDUM)
                </span>
                <span class="text-[11px] text-slate-500">({{ activeItem.doc_mode }})</span>
              </div>
              <span v-if="isItemComplete(activeItem)" class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                Foto Lengkap ✓
              </span>
              <span v-else class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
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
          <div class="glass-card rounded-2xl p-5 space-y-4 border border-white/70 text-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <h4 class="font-bold text-slate-900 flex items-center gap-2">
                <AlertTriangle class="w-4 h-4 text-amber-600" />
                <span>LANGKAH 3: Laporan Kendala Teknis Cabang</span>
              </h4>
              <span
                v-if="selectedTask.issues && selectedTask.issues.length > 0"
                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200"
              >
                {{ selectedTask.issues.length }} Catatan Kendala
              </span>
            </div>

            <!-- Existing Issues Timeline Log -->
            <div v-if="selectedTask.issues && selectedTask.issues.length > 0" class="space-y-2">
              <span class="font-bold text-slate-700 text-[11px] block">Riwayat Kendala yang Pernah Dilaporkan:</span>
              <div
                v-for="iss in selectedTask.issues"
                :key="iss.id"
                class="p-3 bg-white/90 border border-slate-200 rounded-xl space-y-1.5 shadow-xs"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-1.5">
                    <span
                      :class="[
                        'px-2 py-0.2 rounded-md text-[9px] font-bold uppercase',
                        iss.status === 'RESOLVED' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'
                      ]"
                    >
                      {{ iss.status === 'RESOLVED' ? 'TERATASI ✓' : 'BUTUH TINDAKAN' }}
                    </span>
                    <span class="font-bold text-slate-800 text-[11px]">{{ iss.issue_type || 'Kendala Teknis' }}</span>
                  </div>
                  <span class="text-[10px] font-mono text-slate-400">
                    {{ new Date(iss.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                  </span>
                </div>

                <p class="text-slate-700 text-[11px] leading-relaxed">
                  "{{ iss.notes }}"
                </p>

                <!-- Resolution Solution if any -->
                <div v-if="iss.resolution_notes" class="p-2 bg-emerald-50 rounded-lg text-[10px] text-emerald-900 border border-emerald-200/60">
                  <strong>Solusi Pengawas:</strong> {{ iss.resolution_notes }}
                </div>
              </div>
            </div>

            <!-- Add / Report New Issue Form -->
            <div class="p-4 bg-amber-500/10 border border-amber-300/80 rounded-2xl space-y-3 backdrop-blur-md">
              <div class="flex items-center justify-between">
                <label class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                  <AlertCircle class="w-3.5 h-3.5 text-amber-700" />
                  <span>Tambah Laporan Kendala Teknis Baru:</span>
                </label>
              </div>

              <div>
                <label class="block font-bold text-slate-700 mb-1 text-[11px]">Kategori Kendala:</label>
                <select
                  v-model="issueType"
                  class="w-full px-3 py-1.5 border border-slate-200 rounded-xl bg-white/90 focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs"
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
                <label class="block font-bold text-slate-700 mb-1 text-[11px]">Deskripsi Kronologi Kendala:</label>
                <textarea
                  rows="2"
                  placeholder="Jelaskan kendala lapangan yang dialami..."
                  v-model="issueNotes"
                  class="w-full px-3 py-1.5 border border-slate-200 rounded-xl bg-white/90 focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs leading-relaxed"
                />
              </div>

              <div class="flex items-center justify-between pt-1">
                <span class="text-[10px] text-slate-500">
                  Laporan akan langsung tercatat di sistem pengawas & Client.
                </span>
                <button
                  type="button"
                  @click="handleSaveIssue"
                  :disabled="savingIssue || !issueNotes.trim()"
                  class="px-4 py-2 bg-gradient-to-r from-amber-700 to-rose-700 hover:from-amber-600 hover:to-rose-600 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer active:scale-95 transition-all disabled:opacity-50"
                >
                  {{ savingIssue ? 'Menyimpan...' : '+ Simpan Laporan Kendala' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Step 4: Submission Validation Gate -->
          <div class="glass-card-dark rounded-3xl p-6 space-y-4 shadow-xl border border-slate-700/60 text-white">
            <div>
              <h4 class="font-bold text-sm flex items-center gap-2">
                <CheckSquare class="w-4 h-4 text-emerald-400" />
                <span>LANGKAH 4: Submit Seluruh Pekerjaan Cabang</span>
              </h4>
              <p class="text-xs text-slate-400 mt-1">
                Server validation gate akan memeriksa 1x Check-In GPS dan kelengkapan foto dari seluruh sub-pekerjaan di cabang ini.
              </p>
            </div>

            <!-- Validation Checklist Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
              <div
                :class="[
                  'p-3.5 rounded-2xl border flex items-center gap-3 transition-all',
                  isCheckinSatisfied ? 'bg-emerald-950/60 border-emerald-600/80 text-emerald-300' : 'bg-slate-800/80 border-slate-700 text-slate-400'
                ]"
              >
                <CheckCircle2 :class="['w-4 h-4 shrink-0', isCheckinSatisfied ? 'text-emerald-400' : 'text-slate-500']" />
                <div>
                  <div class="font-bold text-white">Check-in GPS Lokasi Cabang</div>
                  <div class="text-[10px] opacity-80">{{ isCheckedIn ? 'Sudah Tervalidasi ✓' : (!selectedTask?.require_checkin ? 'Bebas Lokasi (Opsional) ✓' : 'Belum Check-In') }}</div>
                </div>
              </div>

              <div
                :class="[
                  'p-3.5 rounded-2xl border flex items-center gap-3 transition-all',
                  isAllItemsComplete ? 'bg-emerald-950/60 border-emerald-600/80 text-emerald-300' : 'bg-slate-800/80 border-slate-700 text-slate-400'
                ]"
              >
                <CheckCircle2 :class="['w-4 h-4 shrink-0', isAllItemsComplete ? 'text-emerald-400' : 'text-slate-500']" />
                <div>
                  <div class="font-bold text-white">Kelengkapan Foto Sub-Pekerjaan</div>
                  <div class="text-[10px] opacity-80">
                    {{ completedItemsCount }} dari {{ taskItems.length }} Item Lengkap
                  </div>
                </div>
              </div>
            </div>

            <button
              type="button"
              @click="handleSubmitWorkOrder"
              :disabled="submitting || ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW', 'APPROVED', 'COMPLETED', 'BA_OPNAME'].includes(selectedTask?.status)"
              :class="[
                'w-full py-4 rounded-2xl font-bold text-xs flex items-center justify-center gap-2 shadow-lg transition-all duration-200 active:scale-[0.99]',
                ['SUBMITTED', 'UNDER_REVIEW', 'REVIEW'].includes(selectedTask?.status)
                  ? 'bg-purple-900/80 text-purple-200 cursor-not-allowed border border-purple-700/60'
                  : ['APPROVED', 'COMPLETED', 'BA_OPNAME'].includes(selectedTask?.status)
                  ? 'bg-emerald-900/80 text-emerald-200 cursor-not-allowed border border-emerald-700/60'
                  : canSubmit
                  ? 'bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 cursor-pointer shadow-emerald-500/25 font-black text-sm'
                  : 'bg-slate-800 hover:bg-slate-700 text-slate-300 cursor-pointer border border-slate-700'
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

        <div v-else class="py-24 text-center glass-card rounded-3xl text-slate-400 text-xs border border-white/60">
          Pilih salah satu kartu tugas di sebelah kiri untuk membuka lembar kerja lapangan.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../../services/api';
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
  FileText
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

const canSubmit = computed(() => {
  return isCheckinSatisfied.value &&
    isAllItemsComplete.value &&
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
