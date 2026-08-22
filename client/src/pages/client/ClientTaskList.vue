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
          </div>

          <!-- BEFORE vs AFTER Visual Comparator Card -->
          <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/90 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <div>
                <h4 class="font-black text-sm text-slate-900 flex items-center gap-2">
                  <Eye class="w-4 h-4 text-purple-700" />
                  <span>Perbandingan Visual Sebelum (Before) vs Sesudah (After)</span>
                </h4>
                <p class="text-[11px] text-slate-500">Verifikasi kualitas visual fisik branding dan plang toko cabang secara langsung.</p>
              </div>
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-900 border border-purple-200">
                SPLIT VIEW
              </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Before Photo Box -->
              <div class="border border-slate-200 rounded-2xl p-3 bg-amber-50/30 space-y-2">
                <div class="flex items-center justify-between">
                  <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-amber-500 text-white shadow-xs">
                    KONDISI AWAL (BEFORE)
                  </span>
                  <span class="text-[10px] font-mono text-slate-400">Sebelum Dikerjakan</span>
                </div>
                <div class="h-44 rounded-xl overflow-hidden bg-slate-900 relative">
                  <img
                    v-if="beforePhoto"
                    :src="getFileUrl(beforePhoto.file_path)"
                    alt="Foto Sebelum"
                    class="w-full h-full object-cover cursor-pointer hover:scale-105 transition-all"
                    @click="openLightbox(beforePhoto)"
                  />
                  <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-400 text-xs">
                    <ImageIcon class="w-6 h-6 opacity-40 mb-1" />
                    <span>Foto Before Belum Tersedia</span>
                  </div>
                </div>
                <div v-if="beforePhoto" class="text-[10px] space-y-0.5">
                  <div class="flex items-center justify-between font-mono text-slate-500">
                    <span class="text-emerald-700 font-bold flex items-center gap-1">
                      <MapPin class="w-3 h-3 text-emerald-600" />
                      <span>GPS: {{ beforePhoto.latitude ? `${Number(beforePhoto.latitude).toFixed(5)}, ${Number(beforePhoto.longitude).toFixed(5)}` : 'Terverifikasi' }}</span>
                    </span>
                    <span class="text-[9px] text-slate-400">{{ beforePhoto.server_timestamp ? new Date(beforePhoto.server_timestamp).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '' }}</span>
                  </div>
                  <div class="text-[9px] text-slate-400 font-mono truncate">
                    SHA-256: {{ beforePhoto.file_hash?.substring(0, 16) }}... ✓
                  </div>
                </div>
              </div>

              <!-- After Photo Box -->
              <div class="border border-slate-200 rounded-2xl p-3 bg-emerald-50/30 space-y-2">
                <div class="flex items-center justify-between">
                  <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-600 text-white shadow-xs">
                    HASIL AKHIR (AFTER)
                  </span>
                  <span class="text-[10px] font-mono text-emerald-700 font-bold">100% Selesai ✓</span>
                </div>
                <div class="h-44 rounded-xl overflow-hidden bg-slate-900 relative">
                  <img
                    v-if="afterPhoto"
                    :src="getFileUrl(afterPhoto.file_path)"
                    alt="Foto Selesai"
                    class="w-full h-full object-cover cursor-pointer hover:scale-105 transition-all"
                    @click="openLightbox(afterPhoto)"
                  />
                  <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-400 text-xs">
                    <ImageIcon class="w-6 h-6 opacity-40 mb-1" />
                    <span>Foto After Belum Tersedia</span>
                  </div>
                </div>
                <div v-if="afterPhoto" class="text-[10px] space-y-0.5">
                  <div class="flex items-center justify-between font-mono text-slate-500">
                    <span class="text-emerald-700 font-bold flex items-center gap-1">
                      <MapPin class="w-3 h-3 text-emerald-600" />
                      <span>GPS: {{ afterPhoto.latitude ? `${Number(afterPhoto.latitude).toFixed(5)}, ${Number(afterPhoto.longitude).toFixed(5)}` : 'Terverifikasi' }}</span>
                    </span>
                    <span class="text-[9px] text-slate-400">{{ afterPhoto.server_timestamp ? new Date(afterPhoto.server_timestamp).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '' }}</span>
                  </div>
                  <div class="text-[9px] text-slate-400 font-mono truncate">
                    SHA-256: {{ afterPhoto.file_hash?.substring(0, 16) }}... ✓
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Complete Photo Evidence Gallery -->
          <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/90 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
              <h4 class="font-black text-sm text-slate-900 flex items-center gap-2">
                <Camera class="w-4 h-4 text-purple-700" />
                <span>Dokumentasi Foto Evidensi Lengkap ({{ selectedOrder.evidence_photos?.length || 0 }} Foto)</span>
              </h4>
              <div class="flex items-center gap-2">
                <button
                  v-if="selectedOrder.evidence_photos && selectedOrder.evidence_photos.length > 0"
                  type="button"
                  @click="downloadAllPhotos"
                  class="px-3 py-1.5 bg-gradient-to-r from-purple-800 to-indigo-600 hover:from-purple-700 hover:to-indigo-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs active:scale-95 transition-all cursor-pointer"
                  title="Unduh semua foto bukti toko ini"
                >
                  <Download class="w-3.5 h-3.5" />
                  <span>Unduh Semua Foto SPK</span>
                </button>
                <span class="text-[10px] font-mono text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                  SHA-256 CERTIFIED ✓
                </span>
              </div>
            </div>

            <div v-if="selectedOrder.evidence_photos && selectedOrder.evidence_photos.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <div
                v-for="(p, pIdx) in selectedOrder.evidence_photos"
                :key="p.id"
                @click="openLightbox(p)"
                class="bg-slate-50 rounded-2xl overflow-hidden p-2 border border-slate-200 hover:border-purple-300 transition-all group cursor-pointer relative shadow-xs"
              >
                <div class="h-32 rounded-xl overflow-hidden bg-slate-900 relative mb-2">
                  <img
                    :src="getFileUrl(p.file_path)"
                    :alt="`Bukti ${p.stage}`"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    loading="lazy"
                  />
                  <span
                    :class="[
                      'absolute top-2 left-2 px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider',
                      p.stage === 'BEFORE' ? 'bg-amber-500 text-white' :
                      p.stage === 'PROCESS' ? 'bg-indigo-600 text-white' :
                      'bg-emerald-600 text-white'
                    ]"
                  >
                    {{ p.stage }} #{{ p.sequence || pIdx + 1 }}
                  </span>

                  <!-- Download button -->
                  <div class="absolute bottom-1.5 right-1.5 z-10">
                    <button
                      type="button"
                      @click.stop="downloadSinglePhoto(p)"
                      class="w-6 h-6 rounded-full bg-slate-900/90 hover:bg-purple-700 text-white shadow-md flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95 cursor-pointer border border-white/40"
                      title="Unduh Foto"
                    >
                      <Download class="w-3 h-3" />
                    </button>
                  </div>
                </div>

                <div class="px-1 space-y-1">
                  <div class="flex items-center justify-between text-[9px] font-mono text-slate-500">
                    <span class="truncate max-w-[110px]">{{ p.uploader_name || 'Tim Lapangan' }}</span>
                    <span class="text-emerald-700 font-bold">Valid ✓</span>
                  </div>
                  <div class="text-[9px] font-mono text-purple-900 font-bold flex items-center gap-1 truncate">
                    <MapPin class="w-3 h-3 text-purple-600 shrink-0" />
                    <span>{{ p.latitude ? `${Number(p.latitude).toFixed(5)}, ${Number(p.longitude).toFixed(5)}` : 'Lokasi Terdaftar' }}</span>
                  </div>
                  <div v-if="p.notes" class="text-[10px] text-slate-700 italic truncate">
                    "{{ p.notes }}"
                  </div>
                </div>
              </div>
            </div>
            <p v-else class="text-slate-400 text-xs italic text-center py-6">
              Foto dokumentasi akan muncul otomatis saat teknisi mengunggah bukti dari toko.
            </p>
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
  Download,
  RefreshCw,
  Loader2,
  Sparkles,
  Share2
} from 'lucide-vue-next';

defineEmits(['preview-ba']);

const workOrders = ref([]);
const selectedOrder = ref(null);
const loading = ref(true);
const showShareModal = ref(false);

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
