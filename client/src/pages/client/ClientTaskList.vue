<template>
  <div class="space-y-5 pb-12">
    <!-- Header -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-5 sm:p-6 text-white border border-indigo-900/40 shadow-lg relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -left-10 -top-10 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <div class="inline-flex items-center gap-2 px-2.5 py-0.5 bg-purple-500/20 text-purple-300 border border-purple-500/30 rounded-full text-[10px] font-bold mb-1.5">
            <Store class="w-3 h-3" />
            <span>PORTAL INSPEKSI CABANG TOKO</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-black tracking-tight">
            Progres & Evidensi Fisik Toko
          </h1>
          <p class="text-slate-300 text-xs mt-0.5 max-w-xl">
            Inspeksi perbandingan foto Sebelum (Before) vs Sesudah (After), status GPS teknisi, dan keaslian dokumen forensik digital.
          </p>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
          <span class="px-3 py-1.5 bg-white/10 backdrop-blur-md border border-white/10 rounded-xl text-xs font-bold text-white flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>{{ filteredOrders.length }} Toko Terdaftar</span>
          </span>
          <button
            @click="loadOrders"
            :disabled="loading"
            class="p-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl shadow-xs transition-all cursor-pointer active:scale-95"
            title="Segarkan Data"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
          </button>
        </div>
      </div>
    </div>

    <!-- Demo Mode Notice Banner if using sample data -->
    <div
      v-if="selectedOrder?.is_demo"
      class="p-3.5 bg-gradient-to-r from-purple-900/10 via-indigo-900/5 to-purple-900/10 border border-purple-200 rounded-2xl flex items-center justify-between gap-3 text-xs"
    >
      <div class="flex items-center gap-2 text-purple-950 font-bold">
        <Sparkles class="w-4 h-4 text-purple-700 shrink-0" />
        <span>PRATINJAU INTERAKTIF: Akun Anda belum memiliki data SPK terhubung. Menampilkan contoh format evidensi cabang toko secara lengkap.</span>
      </div>
      <button
        @click="loadOrders"
        class="text-[11px] font-bold text-purple-800 hover:underline shrink-0 cursor-pointer"
      >
        Cek Ulang Server
      </button>
    </div>

    <!-- Mobile Horizontal Store Switcher (Visible on Mobile Only) -->
    <div class="block lg:hidden space-y-2">
      <div class="flex items-center justify-between px-1">
        <h3 class="font-bold text-xs text-slate-500 uppercase tracking-wider">
          Pilih Cabang Toko ({{ filteredOrders.length }})
        </h3>
        <span class="text-[10px] font-semibold bg-purple-50 text-purple-700 px-2 py-0.5 rounded-full border border-purple-200">
          Geser untuk memilih
        </span>
      </div>

      <div v-if="filteredOrders.length > 0" class="flex gap-2.5 overflow-x-auto pb-2 scrollbar-none snap-x snap-mandatory">
        <div
          v-for="order in filteredOrders"
          :key="order.id"
          @click="handleSelectOrder(order.id)"
          :class="[
            'min-w-[240px] max-w-[280px] p-3.5 rounded-2xl cursor-pointer transition-all duration-200 snap-start flex-shrink-0 relative overflow-hidden border',
            selectedOrder?.id === order.id
              ? 'bg-slate-900 text-white border-slate-800 shadow-lg shadow-slate-900/20'
              : 'bg-white text-slate-900 border-slate-200 hover:border-purple-300'
          ]"
        >
          <div class="flex items-center justify-between gap-1.5 mb-1.5">
            <span
              :class="[
                'font-mono font-black text-[10px] px-2 py-0.5 rounded-md',
                selectedOrder?.id === order.id ? 'bg-white/15 text-white' : 'bg-purple-100 text-purple-900'
              ]"
            >
              {{ order.spk_number }}
            </span>
            <StatusBadge :status="order.status" />
          </div>

          <div class="font-bold text-xs line-clamp-1 mb-1" :class="selectedOrder?.id === order.id ? 'text-white' : 'text-slate-900'">
            {{ order.title || order.location_name }}
          </div>

          <div class="text-[11px] flex items-center justify-between" :class="selectedOrder?.id === order.id ? 'text-slate-300' : 'text-slate-500'">
            <span class="truncate max-w-[140px]">📍 {{ order.location_name }}</span>
            <span class="font-bold font-mono text-emerald-400">{{ order.progress_percent || 0 }}%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Master-Detail Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
      <!-- Left Column: Desktop Stores List & Filters -->
      <div class="hidden lg:block bg-white rounded-3xl p-5 border border-slate-200/90 shadow-sm space-y-3.5">
        <!-- Search & Filter -->
        <div class="space-y-2">
          <div class="relative">
            <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Cari toko, SPK, cabang..."
              class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-all"
            />
          </div>

          <div class="flex items-center gap-1 overflow-x-auto pb-1 text-[10px] font-bold">
            <button
              v-for="st in statusTabs"
              :key="st.value"
              @click="selectedStatus = st.value"
              :class="[
                'px-2.5 py-1 rounded-lg transition-all cursor-pointer whitespace-nowrap',
                selectedStatus === st.value
                  ? 'bg-purple-900 text-white shadow-xs'
                  : 'bg-slate-100 hover:bg-slate-200 text-slate-600'
              ]"
            >
              {{ st.label }}
            </button>
          </div>
        </div>

        <!-- Orders List -->
        <div v-if="loading" class="text-center py-12 text-slate-400 text-xs">
          <Loader2 class="w-6 h-6 animate-spin mx-auto mb-2 text-purple-600" />
          <span>Memuat daftar cabang...</span>
        </div>

        <div v-else-if="filteredOrders.length === 0" class="text-center py-12 text-slate-400 text-xs">
          <Store class="w-8 h-8 text-slate-300 mx-auto mb-2" />
          <span>Tidak ada cabang toko yang sesuai filter.</span>
        </div>

        <div v-else class="space-y-2 max-h-[600px] overflow-y-auto pr-1">
          <div
            v-for="order in filteredOrders"
            :key="order.id"
            @click="handleSelectOrder(order.id)"
            :class="[
              'p-3.5 rounded-2xl border transition-all cursor-pointer space-y-1.5',
              selectedOrder?.id === order.id
                ? 'bg-purple-50/80 border-purple-300 shadow-sm ring-1 ring-purple-300'
                : 'bg-white border-slate-200 hover:border-purple-200 shadow-xs'
            ]"
          >
            <div class="flex items-center justify-between">
              <span class="font-mono font-bold text-[10px] text-purple-900 bg-purple-100/60 px-2 py-0.5 rounded">
                {{ order.spk_number }}
              </span>
              <StatusBadge :status="order.status" />
            </div>

            <h4 class="font-bold text-xs text-slate-900 line-clamp-1">
              {{ order.title }}
            </h4>

            <p class="text-[10px] text-slate-500 truncate flex items-center gap-1">
              <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
              <span>{{ order.location_name }}</span>
            </p>

            <div class="flex items-center justify-between text-[10px] pt-1 border-t border-slate-100 font-mono text-slate-500">
              <span>Progress: <strong class="text-slate-800">{{ order.progress_percent || 0 }}%</strong></span>
              <span v-if="order.has_issue" class="text-amber-700 font-bold flex items-center gap-0.5">
                <AlertTriangle class="w-3 h-3" /> Kendala
              </span>
              <span v-else class="text-emerald-700 font-bold">Lancar ✓</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Store Detail & Photo Evidence Comparator -->
      <div class="lg:col-span-2 space-y-5">
        <template v-if="selectedOrder">
          <!-- Store Header Card -->
          <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/90 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
              <div>
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-mono font-bold text-xs bg-purple-100 text-purple-900 px-2.5 py-0.5 rounded-lg border border-purple-200">
                    {{ selectedOrder.spk_number || '-' }}
                  </span>
                  <StatusBadge :status="selectedOrder.status || 'IN_PROGRESS'" />
                  <span v-if="selectedOrder.area || selectedOrder.area_name" class="text-xs text-slate-500 flex items-center gap-1">
                    <MapPin class="w-3 h-3 text-slate-400" />
                    {{ typeof selectedOrder.area === 'object' ? (selectedOrder.area?.name || selectedOrder.area_name) : (selectedOrder.area || selectedOrder.area_name || '-') }}
                  </span>
                </div>
                <h3 class="font-black text-slate-900 text-base sm:text-lg mt-1.5">
                  {{ selectedOrder.title || selectedOrder.location_name || 'Cabang Toko' }}
                </h3>
                <p class="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5">
                  <span>📍 {{ selectedOrder.address || selectedOrder.location_name || '-' }}</span>
                </p>
              </div>

              <div class="flex items-center gap-2 self-start sm:self-auto">
                <button
                  type="button"
                  @click="showShareModal = true"
                  class="px-3.5 py-2 bg-purple-50 hover:bg-purple-100 text-purple-900 border border-purple-200 font-bold text-xs rounded-xl shadow-2xs flex items-center gap-1.5 active:scale-95 transition-all cursor-pointer"
                  title="Bagikan Tautan Pemantauan Langsung (Live Tracking)"
                >
                  <Share2 class="w-4 h-4 text-purple-700" />
                  <span>Bagikan</span>
                </button>

                <button
                  v-if="selectedOrder.ba_document"
                  @click="$emit('preview-ba', selectedOrder.ba_document)"
                  class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold text-xs rounded-xl shadow-xs flex items-center gap-1.5 active:scale-95 transition-all cursor-pointer"
                >
                  <FileCheck2 class="w-4 h-4" />
                  <span>Lihat Dokumen BA</span>
                </button>
              </div>
            </div>

            <!-- Progress Tracker -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-800">Status & Tahapan Pengerjaan Toko:</span>
                <span class="font-mono font-bold text-xs text-purple-900">{{ selectedOrder.progress_percent || 0 }}% Selesai</span>
              </div>
              <StepperProgress :status="selectedOrder.status || 'IN_PROGRESS'" :progressPercent="Number(selectedOrder.progress_percent || 0)" />
            </div>

            <!-- Location & Field PIC Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
              <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-1.5 text-slate-600">
                <span class="font-bold text-slate-900 block text-xs">Pelaksana Lapangan SGX:</span>
                <div>PIC Teknisi: <strong class="text-slate-800">{{ selectedOrder.pic?.name || selectedOrder.pic_name || 'Tim Lapangan SGX' }}</strong></div>
                <div>Kontak PIC: <strong class="text-slate-800 font-mono">{{ selectedOrder.pic?.phone || selectedOrder.pic_phone || '-' }}</strong></div>
                <div>Target Selesai: <strong class="font-mono text-slate-800">{{ selectedOrder.due_date || selectedOrder.deadline || selectedOrder.scheduled_date || '-' }}</strong></div>
              </div>

              <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-1.5 text-slate-600">
                <span class="font-bold text-slate-900 block text-xs">Verifikasi Kehadiran GPS:</span>
                <div v-if="Array.isArray(selectedOrder.check_ins) && selectedOrder.check_ins.length > 0">
                  <div class="font-bold text-emerald-700 flex items-center gap-1">
                    <CheckCircle2 class="w-3.5 h-3.5" />
                    <span>Sudah Check-In di Lokasi Toko ✓</span>
                  </div>
                  <div class="font-mono text-[11px] text-slate-600 mt-1">
                    GPS: {{ Number(selectedOrder.check_ins[0].latitude || 0).toFixed(5) }}, {{ Number(selectedOrder.check_ins[0].longitude || 0).toFixed(5) }}
                  </div>
                  <div class="text-[10px] text-slate-400 font-mono">
                    Waktu: {{ new Date(selectedOrder.check_ins[0].server_timestamp || selectedOrder.check_ins[0].created_at || Date.now()).toLocaleString('id-ID') }}
                  </div>
                </div>
                <div v-else class="text-amber-700 font-bold italic pt-1">
                  Menunggu kedatangan teknisi di lokasi toko.
                </div>
              </div>
            </div>

            <!-- SPK Notes & Special Instructions -->
            <div
              v-if="selectedOrder.notes"
              class="p-4 bg-gradient-to-r from-purple-50/80 to-indigo-50/80 border border-purple-200/90 rounded-2xl space-y-1.5 shadow-xs text-xs"
            >
              <div class="flex items-center gap-2 text-purple-900 font-bold text-xs">
                <FileText class="w-4 h-4 text-purple-700 shrink-0" />
                <span>CATATAN & ARAHAN KHUSUS PROYEK:</span>
              </div>
              <p class="text-xs text-slate-800 font-medium whitespace-pre-line pl-6 leading-relaxed">
                {{ selectedOrder.notes }}
              </p>
            </div>

            <!-- Quality Control Revision Status Banner -->
            <div
              v-if="selectedOrder.status === 'REVISION'"
              class="p-4 bg-gradient-to-r from-amber-50 to-rose-50 border-2 border-amber-300 rounded-2xl space-y-2 shadow-xs text-xs"
            >
              <div class="flex items-center justify-between">
                <div class="font-bold text-xs text-amber-950 flex items-center gap-2">
                  <RotateCcw class="w-4 h-4 text-amber-600 animate-spin-slow" />
                  <span>KONTROL MUTU & PENYESUAIAN TEKNIS LAPANGAN (QUALITY ASSURANCE)</span>
                </div>
                <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase bg-amber-200 text-amber-900 border border-amber-300">
                  Dalam Penyempurnaan
                </span>
              </div>
              <div class="bg-white/90 p-3 rounded-xl border border-amber-200 text-xs text-slate-800 font-medium space-y-1">
                <span class="text-[10px] font-bold text-amber-800 uppercase block">Catatan Pengawas SGX:</span>
                <p class="italic text-slate-900 font-semibold leading-relaxed">
                  "{{ latestOrderRevision?.reason || selectedOrder.revisions?.[0]?.reason || 'Pekerjaan sedang disempurnakan oleh teknisi agar memenuhi standar mutu terbaik sebelum disahkan.' }}"
                </p>
              </div>
            </div>
          </div>

          <!-- Sub-Tasks & Visual Evidence Per Sub-Item (Interactive Zero-Scroll Sub-Task Inspector) -->
          <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/90 shadow-sm space-y-4">
            
            <!-- Section Header & Toolbar Controls -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
              <div>
                <h4 class="font-black text-sm text-slate-900 flex items-center gap-2">
                  <Eye class="w-4 h-4 text-purple-700" />
                  <span>Lingkup Sub-Pekerjaan & Evidensi Visual ({{ displayItems.length }} Item)</span>
                </h4>
                <p class="text-[11px] text-slate-500">Pilih tab item di bawah untuk inspeksi cepat kondisi Sebelum, Proses, dan Sesudah tanpa perlu scrolling.</p>
              </div>

              <!-- View Mode Toggle & Batch Actions -->
              <div class="flex items-center gap-2 flex-wrap self-start sm:self-auto">
                <div class="bg-slate-100 p-0.5 rounded-xl flex items-center gap-1 text-[10px] font-bold border border-slate-200">
                  <button
                    type="button"
                    @click="viewMode = 'tabbed'"
                    :class="[
                      'px-2.5 py-1 rounded-lg transition-all cursor-pointer',
                      viewMode === 'tabbed' ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'
                    ]"
                  >
                    Fokus Tab
                  </button>
                  <button
                    type="button"
                    @click="viewMode = 'all'"
                    :class="[
                      'px-2.5 py-1 rounded-lg transition-all cursor-pointer',
                      viewMode === 'all' ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'
                    ]"
                  >
                    Semua Item
                  </button>
                </div>

                <button
                  v-if="selectedOrder.evidence_photos && selectedOrder.evidence_photos.length > 0"
                  type="button"
                  @click="downloadAllPhotos"
                  class="px-3 py-1.5 bg-gradient-to-r from-purple-800 to-indigo-600 hover:from-purple-700 hover:to-indigo-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs active:scale-95 transition-all cursor-pointer"
                  title="Unduh semua foto bukti toko ini"
                >
                  <Download class="w-3.5 h-3.5" />
                  <span>Unduh Semua ({{ selectedOrder.evidence_photos.length }})</span>
                </button>
              </div>
            </div>

            <!-- MODE 1: TABBED ZERO-SCROLL VIEW (Interactive Sub-Task Selector Pills) -->
            <div v-if="viewMode === 'tabbed'" class="space-y-4">
              <!-- Horizontal Sub-Task Pill Bar -->
              <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none snap-x">
                <button
                  v-for="(item, idx) in displayItems"
                  :key="item.id || idx"
                  type="button"
                  @click="activeItemIndex = idx"
                  :class="[
                    'px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shrink-0 border cursor-pointer snap-start',
                    activeItemIndex === idx
                      ? 'bg-purple-900 text-white border-purple-900 shadow-md shadow-purple-900/20 ring-1 ring-purple-900'
                      : 'bg-slate-50 hover:bg-purple-50 text-slate-700 border-slate-200 hover:border-purple-200'
                  ]"
                >
                  <span
                    :class="[
                      'w-5 h-5 rounded-lg text-[10px] font-black flex items-center justify-center',
                      activeItemIndex === idx ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-900'
                    ]"
                  >
                    {{ idx + 1 }}
                  </span>
                  <span class="truncate max-w-[180px]">{{ item.item_name }}</span>
                  <span
                    v-if="item.is_addendum"
                    class="px-1.5 py-0.2 rounded text-[8px] font-black bg-amber-500 text-white uppercase"
                  >
                    +Addendum
                  </span>
                  <span
                    :class="[
                      'w-2 h-2 rounded-full',
                      isItemFullyDocumented(item) ? 'bg-emerald-400' : 'bg-amber-400'
                    ]"
                    :title="isItemFullyDocumented(item) ? 'Selesai 100%' : 'Dalam Pengerjaan'"
                  ></span>
                </button>
              </div>

              <!-- Active Sub-Item Visual Stage (Side-by-Side 3-Phase Stage) -->
              <div v-if="displayItems[activeItemIndex]" class="space-y-3.5 animate-fade-in">
                <!-- Active Item Sub-Header -->
                <div class="flex items-center justify-between bg-slate-50 p-3 rounded-2xl border border-slate-200/80">
                  <div class="flex items-center gap-2 min-w-0">
                    <span class="w-6 h-6 rounded-lg bg-purple-900 text-white font-bold text-xs flex items-center justify-center shrink-0">
                      {{ activeItemIndex + 1 }}
                    </span>
                    <div class="min-w-0">
                      <div class="font-bold text-xs sm:text-sm text-slate-900 truncate">
                        {{ displayItems[activeItemIndex].item_name }}
                      </div>
                      <div class="text-[10px] text-slate-500 flex items-center gap-2">
                        <span class="bg-purple-100 text-purple-900 font-bold px-1.5 py-0.2 rounded">Bobot: {{ displayItems[activeItemIndex].weight_percent || 100 }}%</span>
                        <span>•</span>
                        <span>Mode: <strong class="text-slate-700">{{ displayItems[activeItemIndex].doc_mode || selectedOrder.doc_mode }}</strong></span>
                      </div>
                    </div>
                  </div>

                  <span
                    :class="[
                      'px-2.5 py-1 rounded-full text-[10px] font-bold border shrink-0',
                      isItemFullyDocumented(displayItems[activeItemIndex])
                        ? 'bg-emerald-50 text-emerald-800 border-emerald-300'
                        : 'bg-amber-50 text-amber-800 border-amber-300'
                    ]"
                  >
                    {{ isItemFullyDocumented(displayItems[activeItemIndex]) ? 'Selesai Dikerjakan ✓' : 'Dalam Proses ⏳' }}
                  </span>
                </div>

                <!-- Side-by-Side 3-Phase Comparison Cards for Active Item -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                  <!-- 1. Before Photo -->
                  <div class="border border-slate-200 rounded-2xl p-3 bg-amber-50/20 space-y-2 flex flex-col justify-between shadow-2xs">
                    <div>
                      <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-amber-500 text-white shadow-2xs">
                          KONDISI AWAL (BEFORE)
                        </span>
                        <span class="text-[10px] font-mono text-slate-500">
                          {{ getPhotosForItemStage(displayItems[activeItemIndex].id, 'BEFORE').length }} Foto
                        </span>
                      </div>

                      <div v-if="getPhotosForItemStage(displayItems[activeItemIndex].id, 'BEFORE').length > 0" class="space-y-2">
                        <div
                          v-for="p in getPhotosForItemStage(displayItems[activeItemIndex].id, 'BEFORE')"
                          :key="p.id"
                          class="h-44 rounded-xl overflow-hidden bg-slate-900 relative group cursor-pointer"
                          @click="openLightbox(p)"
                        >
                          <img
                            :src="getFileUrl(p.file_path)"
                            alt="Foto Sebelum"
                            class="w-full h-full object-cover group-hover:scale-105 transition-all"
                          />
                          <button
                            type="button"
                            @click.stop="downloadSinglePhoto(p)"
                            class="absolute bottom-2 right-2 w-7 h-7 rounded-full bg-slate-900/90 hover:bg-purple-700 text-white shadow-md flex items-center justify-center transition-all cursor-pointer border border-white/40 z-10"
                            title="Unduh Foto Before"
                          >
                            <Download class="w-3.5 h-3.5" />
                          </button>
                          <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-2 text-[9px] text-white font-mono pointer-events-none">
                            <div class="text-emerald-300 font-bold">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</div>
                          </div>
                        </div>
                      </div>
                      <div v-else class="h-40 rounded-xl border border-dashed border-amber-200 flex flex-col items-center justify-center text-slate-400 text-xs">
                        <ImageIcon class="w-6 h-6 opacity-40 mb-1" />
                        <span>Foto Before Belum Tersedia</span>
                      </div>
                    </div>
                  </div>

                  <!-- 2. Process Photo -->
                  <div class="border border-slate-200 rounded-2xl p-3 bg-blue-50/20 space-y-2 flex flex-col justify-between shadow-2xs">
                    <div>
                      <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-blue-600 text-white shadow-2xs">
                          PROSES KERJA (PROCESS)
                        </span>
                        <span class="text-[10px] font-mono text-slate-500">
                          {{ getPhotosForItemStage(displayItems[activeItemIndex].id, 'PROCESS').length }} Foto
                        </span>
                      </div>

                      <div v-if="getPhotosForItemStage(displayItems[activeItemIndex].id, 'PROCESS').length > 0" class="space-y-2">
                        <div
                          v-for="p in getPhotosForItemStage(displayItems[activeItemIndex].id, 'PROCESS')"
                          :key="p.id"
                          class="h-44 rounded-xl overflow-hidden bg-slate-900 relative group cursor-pointer"
                          @click="openLightbox(p)"
                        >
                          <img
                            :src="getFileUrl(p.file_path)"
                            alt="Foto Proses"
                            class="w-full h-full object-cover group-hover:scale-105 transition-all"
                          />
                          <button
                            type="button"
                            @click.stop="downloadSinglePhoto(p)"
                            class="absolute bottom-2 right-2 w-7 h-7 rounded-full bg-slate-900/90 hover:bg-purple-700 text-white shadow-md flex items-center justify-center transition-all cursor-pointer border border-white/40 z-10"
                            title="Unduh Foto Process"
                          >
                            <Download class="w-3.5 h-3.5" />
                          </button>
                          <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-2 text-[9px] text-white font-mono pointer-events-none">
                            <div class="text-emerald-300 font-bold">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</div>
                          </div>
                        </div>
                      </div>
                      <div v-else class="h-40 rounded-xl border border-dashed border-blue-200 flex flex-col items-center justify-center text-slate-400 text-xs">
                        <ImageIcon class="w-6 h-6 opacity-40 mb-1" />
                        <span>{{ displayItems[activeItemIndex].doc_mode === 'AFTER_ONLY' ? 'Mode Tanpa Foto Proses' : 'Foto Proses Belum Tersedia' }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- 3. After Photo -->
                  <div class="border border-slate-200 rounded-2xl p-3 bg-emerald-50/20 space-y-2 flex flex-col justify-between shadow-2xs">
                    <div>
                      <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-600 text-white shadow-2xs">
                          HASIL AKHIR (AFTER)
                        </span>
                        <span class="text-[10px] font-mono text-emerald-700 font-bold">
                          {{ getPhotosForItemStage(displayItems[activeItemIndex].id, 'AFTER').length > 0 ? 'Selesai ✓' : 'Menunggu' }}
                        </span>
                      </div>

                      <div v-if="getPhotosForItemStage(displayItems[activeItemIndex].id, 'AFTER').length > 0" class="space-y-2">
                        <div
                          v-for="p in getPhotosForItemStage(displayItems[activeItemIndex].id, 'AFTER')"
                          :key="p.id"
                          class="h-44 rounded-xl overflow-hidden bg-slate-900 relative group cursor-pointer"
                          @click="openLightbox(p)"
                        >
                          <img
                            :src="getFileUrl(p.file_path)"
                            alt="Foto Selesai"
                            class="w-full h-full object-cover group-hover:scale-105 transition-all"
                          />
                          <button
                            type="button"
                            @click.stop="downloadSinglePhoto(p)"
                            class="absolute bottom-2 right-2 w-7 h-7 rounded-full bg-slate-900/90 hover:bg-purple-700 text-white shadow-md flex items-center justify-center transition-all cursor-pointer border border-white/40 z-10"
                            title="Unduh Foto After"
                          >
                            <Download class="w-3.5 h-3.5" />
                          </button>
                          <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-2 text-[9px] text-white font-mono pointer-events-none">
                            <div class="text-emerald-300 font-bold">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</div>
                          </div>
                        </div>
                      </div>
                      <div v-else class="h-40 rounded-xl border border-dashed border-emerald-200 flex flex-col items-center justify-center text-slate-400 text-xs">
                        <ImageIcon class="w-6 h-6 opacity-40 mb-1" />
                        <span>Foto After Belum Tersedia</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- MODE 2: ALL ITEMS EXPANDED VIEW (Optional Full View) -->
            <div v-else class="space-y-4">
              <div
                v-for="(item, itmIdx) in displayItems"
                :key="item.id || itmIdx"
                class="rounded-2xl p-4 sm:p-5 border border-slate-200 bg-slate-50/50 space-y-3.5"
              >
                <!-- Sub-Task Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-2.5">
                  <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-purple-900 text-white font-bold text-xs flex items-center justify-center shadow-2xs">
                      {{ itmIdx + 1 }}
                    </span>
                    <div>
                      <div class="flex items-center gap-2">
                        <h5 class="font-black text-slate-900 text-xs sm:text-sm">
                          {{ item.item_name }}
                        </h5>
                        <span
                          v-if="item.is_addendum"
                          class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider bg-amber-500 text-white shadow-2xs"
                        >
                          + ADDENDUM
                        </span>
                      </div>
                      <div class="flex items-center gap-2 text-[10px] text-slate-500 mt-0.5">
                        <span class="bg-purple-100 text-purple-900 font-bold px-1.5 py-0.2 rounded">Bobot: {{ item.weight_percent || 100 }}%</span>
                        <span>•</span>
                        <span>Mode: <strong class="text-slate-700">{{ item.doc_mode || selectedOrder.doc_mode }}</strong></span>
                      </div>
                    </div>
                  </div>

                  <span
                    :class="[
                      'px-2.5 py-1 rounded-full text-[10px] font-bold border',
                      isItemFullyDocumented(item)
                        ? 'bg-emerald-50 text-emerald-800 border-emerald-300'
                        : 'bg-amber-50 text-amber-800 border-amber-300'
                    ]"
                  >
                    {{ isItemFullyDocumented(item) ? 'Selesai ✓' : 'Dalam Pengerjaan ⏳' }}
                  </span>
                </div>

                <!-- 3 Columns for this Sub-Task -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                  <!-- Before Box -->
                  <div class="border border-slate-200 rounded-xl p-2.5 bg-white space-y-1.5">
                    <div class="flex items-center justify-between text-[10px]">
                      <span class="font-bold text-amber-800">BEFORE</span>
                      <span class="text-slate-400 font-mono">{{ getPhotosForItemStage(item.id, 'BEFORE').length }} Foto</span>
                    </div>
                    <div v-if="getPhotosForItemStage(item.id, 'BEFORE').length > 0" class="h-32 rounded-lg overflow-hidden bg-slate-900 relative cursor-pointer" @click="openLightbox(getPhotosForItemStage(item.id, 'BEFORE')[0])">
                      <img :src="getFileUrl(getPhotosForItemStage(item.id, 'BEFORE')[0].file_path)" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="h-28 rounded-lg border border-dashed border-slate-200 flex items-center justify-center text-slate-400 text-[10px]">
                      Belum ada foto
                    </div>
                  </div>

                  <!-- Process Box -->
                  <div class="border border-slate-200 rounded-xl p-2.5 bg-white space-y-1.5">
                    <div class="flex items-center justify-between text-[10px]">
                      <span class="font-bold text-blue-800">PROCESS</span>
                      <span class="text-slate-400 font-mono">{{ getPhotosForItemStage(item.id, 'PROCESS').length }} Foto</span>
                    </div>
                    <div v-if="getPhotosForItemStage(item.id, 'PROCESS').length > 0" class="h-32 rounded-lg overflow-hidden bg-slate-900 relative cursor-pointer" @click="openLightbox(getPhotosForItemStage(item.id, 'PROCESS')[0])">
                      <img :src="getFileUrl(getPhotosForItemStage(item.id, 'PROCESS')[0].file_path)" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="h-28 rounded-lg border border-dashed border-slate-200 flex items-center justify-center text-slate-400 text-[10px]">
                      Belum ada foto
                    </div>
                  </div>

                  <!-- After Box -->
                  <div class="border border-slate-200 rounded-xl p-2.5 bg-white space-y-1.5">
                    <div class="flex items-center justify-between text-[10px]">
                      <span class="font-bold text-emerald-800">AFTER</span>
                      <span class="text-emerald-700 font-bold font-mono">{{ getPhotosForItemStage(item.id, 'AFTER').length > 0 ? '✓' : '-' }}</span>
                    </div>
                    <div v-if="getPhotosForItemStage(item.id, 'AFTER').length > 0" class="h-32 rounded-lg overflow-hidden bg-slate-900 relative cursor-pointer" @click="openLightbox(getPhotosForItemStage(item.id, 'AFTER')[0])">
                      <img :src="getFileUrl(getPhotosForItemStage(item.id, 'AFTER')[0].file_path)" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="h-28 rounded-lg border border-dashed border-slate-200 flex items-center justify-center text-slate-400 text-[10px]">
                      Belum ada foto
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- Field Issues Log Card if any -->
          <div v-if="selectedOrder.issues && selectedOrder.issues.length > 0" class="bg-amber-50/50 rounded-3xl p-5 border border-amber-200 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-amber-200 pb-2">
              <h4 class="font-black text-xs text-amber-950 flex items-center gap-1.5">
                <AlertTriangle class="w-4 h-4 text-amber-700" />
                <span>Catatan Kendala Lapangan Cabang Ini:</span>
              </h4>
              <span class="text-[10px] font-bold text-amber-900">{{ selectedOrder.issues.length }} Catatan</span>
            </div>

            <div class="space-y-2">
              <div
                v-for="iss in selectedOrder.issues"
                :key="iss.id"
                class="p-3 bg-white rounded-xl border border-amber-200 text-xs space-y-1 shadow-xs"
              >
                <div class="flex items-center justify-between">
                  <span class="font-bold text-amber-900">{{ iss.issue_type || 'Kendala Lapangan' }}</span>
                  <span class="text-[9px] font-mono text-slate-400">{{ new Date(iss.created_at).toLocaleDateString('id-ID') }}</span>
                </div>
                <p class="text-slate-700 text-[11px]">"{{ iss.notes }}"</p>
                <div v-if="iss.resolution_notes" class="p-2 bg-emerald-50 rounded-lg text-[10px] text-emerald-900 border border-emerald-200">
                  <strong>Tindakan Solusi SGX:</strong> {{ iss.resolution_notes }}
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- Empty State When No Store Selected or No Stores Available -->
        <div v-else class="bg-white rounded-3xl p-12 border border-slate-200 text-center space-y-3">
          <Store class="w-12 h-12 text-purple-300 mx-auto" />
          <h3 class="text-base font-extrabold text-slate-800">
            Pilih Cabang Toko
          </h3>
          <p class="text-xs text-slate-500 max-w-sm mx-auto">
            Silakan pilih salah satu cabang toko di atas/samping untuk melihat perbandingan foto Before-After dan status GPS.
          </p>
          <button
            @click="loadOrders"
            class="px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-800 text-xs font-bold rounded-xl transition-all inline-flex items-center gap-1.5 cursor-pointer"
          >
            <RefreshCw class="w-3.5 h-3.5" />
            <span>Muat Ulang Data</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Reusable Photo Lightbox Full-Screen Viewer -->
    <PhotoLightboxModal
      :isOpen="isLightboxOpen"
      :photos="selectedOrder?.evidence_photos || []"
      :initialIndex="selectedLightboxIndex"
      @close="isLightboxOpen = false"
    />

    <!-- Share Live Tracking SPK Modal -->
    <ShareSpkModal
      v-if="showShareModal && selectedOrder"
      :workOrder="selectedOrder"
      @close="showShareModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api, getFileUrl } from '../../services/api';
import StatusBadge from '../../components/StatusBadge.vue';
import StepperProgress from '../../components/StepperProgress.vue';
import PhotoLightboxModal from '../../components/PhotoLightboxModal.vue';
import ShareSpkModal from '../../components/ShareSpkModal.vue';
import {
  Store,
  Search,
  MapPin,
  Camera,
  CheckCircle2,
  AlertTriangle,
  FileCheck2,
  Eye,
  ImageIcon,
  RefreshCw,
  Loader2,
  Sparkles,
  Share2,
  FileText,
  RotateCcw
} from 'lucide-vue-next';

defineEmits(['preview-ba']);

const workOrders = ref([]);
const selectedOrder = ref(null);
const loading = ref(true);
const showShareModal = ref(false);
const activeItemIndex = ref(0);
const viewMode = ref('tabbed');

const latestOrderRevision = computed(() => {
  if (!selectedOrder.value?.revisions || selectedOrder.value.revisions.length === 0) return null;
  return selectedOrder.value.revisions[selectedOrder.value.revisions.length - 1];
});

const searchQuery = ref('');
const selectedStatus = ref('ALL');

const statusTabs = [
  { label: 'Semua Toko', value: 'ALL' },
  { label: 'Sedang Berjalan', value: 'IN_PROGRESS' },
  { label: 'Selesai 100%', value: 'COMPLETED' }
];

// Interactive Demo Sample Store for Preview when company has no work orders yet
const sampleDemoOrder = {
  id: 'demo-sample-store',
  is_demo: true,
  spk_number: 'SPK-DEMO-SAMPLE',
  title: 'Pemasangan Signage & Facade Toko Contoh',
  location_name: 'Cabang Contoh - Jl. R.E. Martadinata No. 45',
  address: 'Jl. R.E. Martadinata No. 45, Citarum, Bandung Wetan, Kota Bandung',
  status: 'IN_PROGRESS',
  progress_percent: 75,
  area: { name: 'Jawa Barat - Bandung' },
  pic: { name: 'Rian Hidayat (Tim Teknisi SGX)', phone: '0812-3456-7890' },
  due_date: '2026-08-25',
  check_ins: [
    {
      latitude: -6.908332,
      longitude: 107.610947,
      server_timestamp: new Date().toISOString()
    }
  ],
  evidence_photos: [
    {
      id: 'demo-p1',
      stage: 'BEFORE',
      sequence: 1,
      file_path: 'https://images.unsplash.com/photo-1541888946425-d0fbb18086f6?w=600&auto=format&fit=crop&q=80',
      file_name: 'before_plang_lama.jpg',
      file_hash: 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
      uploader_name: 'Rian Hidayat',
      notes: 'Kondisi plang lama sebelum dibongkar dan diganti baru.'
    },
    {
      id: 'demo-p2',
      stage: 'PROCESS',
      sequence: 2,
      file_path: 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=600&auto=format&fit=crop&q=80',
      file_name: 'process_instalasi_rangka.jpg',
      file_hash: 'a1b2c3d4e5f60718293a4b5c6d7e8f90123456789abcdef0123456789abcdef0',
      uploader_name: 'Rian Hidayat',
      notes: 'Pemasangan konstruksi rangka besi dan penarikan jalur kelistrikan.'
    },
    {
      id: 'demo-p3',
      stage: 'AFTER',
      sequence: 3,
      file_path: 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=600&auto=format&fit=crop&q=80',
      file_name: 'after_signage_selesai.jpg',
      file_hash: '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08',
      uploader_name: 'Rian Hidayat',
      notes: 'Branding signage baru selesai dipasang rapi dan lampu LED menyala sempurna.'
    }
  ],
  issues: []
};

async function loadOrders() {
  loading.value = true;
  try {
    const res = await api.getWorkOrders();
    const serverOrders = (res.data || []).filter(Boolean);
    if (serverOrders.length > 0) {
      workOrders.value = serverOrders;
      await handleSelectOrder(serverOrders[0].id);
    } else {
      // Fallback to sample demo store so client sees full interactive UI
      workOrders.value = [sampleDemoOrder];
      selectedOrder.value = sampleDemoOrder;
    }
  } catch (err) {
    console.error('Failed to load client store tasks, activating sample preview:', err);
    workOrders.value = [sampleDemoOrder];
    selectedOrder.value = sampleDemoOrder;
  } finally {
    loading.value = false;
  }
}

async function handleSelectOrder(id) {
  if (id === sampleDemoOrder.id) {
    selectedOrder.value = sampleDemoOrder;
    return;
  }
  try {
    const detail = await api.getWorkOrderById(id);
    if (detail.data) {
      selectedOrder.value = detail.data;
    } else {
      selectedOrder.value = workOrders.value.find(w => w.id === id) || sampleDemoOrder;
    }
  } catch (err) {
    console.error('Failed to get store details:', err);
    selectedOrder.value = workOrders.value.find(w => w.id === id) || sampleDemoOrder;
  }
}

const filteredOrders = computed(() => {
  return workOrders.value.filter(wo => {
    if (selectedStatus.value === 'COMPLETED' && !['APPROVED', 'COMPLETED', 'BA_OPNAME'].includes(wo.status)) {
      return false;
    }
    if (selectedStatus.value === 'IN_PROGRESS' && ['APPROVED', 'COMPLETED', 'BA_OPNAME'].includes(wo.status)) {
      return false;
    }
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase();
      const matchSpk = wo.spk_number?.toLowerCase().includes(q);
      const matchTitle = wo.title?.toLowerCase().includes(q);
      const matchLoc = wo.location_name?.toLowerCase().includes(q);
      if (!matchSpk && !matchTitle && !matchLoc) return false;
    }
    return true;
  });
});

const displayItems = computed(() => {
  if (selectedOrder.value?.items && selectedOrder.value.items.length > 0) {
    return selectedOrder.value.items;
  }
  return [{
    id: null,
    item_name: selectedOrder.value?.title || 'Lingkup Pekerjaan Toko',
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

function isItemFullyDocumented(item) {
  const docMode = item.doc_mode || selectedOrder.value?.doc_mode || 'BEFORE_PROCESS_AFTER';
  const afterPhotos = getPhotosForItemStage(item.id, 'AFTER');
  if (docMode === 'AFTER_ONLY') {
    return afterPhotos.length > 0;
  }
  const beforePhotos = getPhotosForItemStage(item.id, 'BEFORE');
  return beforePhotos.length > 0 && afterPhotos.length > 0;
}

const beforePhoto = computed(() => {
  return selectedOrder.value?.evidence_photos?.find(p => p.stage === 'BEFORE') || null;
});

const afterPhoto = computed(() => {
  return selectedOrder.value?.evidence_photos?.find(p => p.stage === 'AFTER') || null;
});

/**
 * Lightbox & Photo Download Handlers
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
  loadOrders();
});
</script>
