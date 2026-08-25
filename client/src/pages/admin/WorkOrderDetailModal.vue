<template>
  <div v-if="workOrderId" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass-modal rounded-3xl max-w-4xl w-full shadow-2xl overflow-hidden my-6 flex flex-col max-h-[90vh] border border-white/80">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between bg-slate-100/60 shrink-0">
        <div>
          <div class="flex items-center gap-2">
            <span class="font-mono font-bold text-xs bg-slate-200 px-2 py-0.5 rounded text-slate-800">
              {{ workOrder?.spk_number || 'Memuat...' }}
            </span>
            <StatusBadge :status="workOrder?.status" />
          </div>
          <h3 class="font-black text-slate-900 text-base mt-1">{{ workOrder?.title }}</h3>
        </div>

        <div class="flex items-center gap-2">
          <!-- Share Live Tracking Button -->
          <button
            type="button"
            @click="showShareModal = true"
            class="px-3 py-2 bg-purple-50 hover:bg-purple-100 text-purple-900 border border-purple-200 font-bold text-xs rounded-xl shadow-2xs flex items-center gap-1.5 transition-all active:scale-95 cursor-pointer"
            title="Bagikan Tautan Pemantauan Langsung (Live Tracking)"
          >
            <Share2 class="w-3.5 h-3.5 text-purple-700" />
            <span class="hidden sm:inline">Bagikan</span>
          </button>

          <!-- Superuser Archive / Unarchive Action Buttons -->
          <template v-if="isSuperuser">
            <button
              v-if="!workOrder?.is_archived && ['APPROVED', 'COMPLETED', 'BA_OPNAME', 'CANCELLED'].includes(workOrder?.status)"
              type="button"
              @click="handleArchiveSpk"
              :disabled="archiving"
              class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-xs flex items-center gap-1.5 transition-all active:scale-95 cursor-pointer disabled:opacity-50"
              title="Arsipkan SPK yang telah selesai (Hanya Superuser)"
            >
              <Archive class="w-3.5 h-3.5" />
              <span class="hidden sm:inline">{{ archiving ? 'Mengarsipkan...' : 'Arsipkan SPK' }}</span>
            </button>

            <button
              v-else-if="workOrder?.is_archived"
              type="button"
              @click="handleUnarchiveSpk"
              :disabled="archiving"
              class="px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs flex items-center gap-1.5 transition-all active:scale-95 cursor-pointer disabled:opacity-50"
              title="Pulihkan SPK kembali ke daftar aktif"
            >
              <RotateCcw class="w-3.5 h-3.5" />
              <span class="hidden sm:inline">{{ archiving ? 'Memulihkan...' : 'Pulihkan SPK' }}</span>
            </button>
          </template>

          <!-- Edit SPK Button (Supervisor Only) -->
          <button
            v-if="isSupervisor"
            @click="isEditModalOpen = true"
            type="button"
            class="px-3.5 py-2 bg-gradient-to-r from-purple-800 to-indigo-700 hover:from-purple-700 hover:to-indigo-600 text-white font-bold text-xs rounded-xl shadow-xs flex items-center gap-1.5 transition-all active:scale-95 cursor-pointer"
            title="Edit Data & Pengaturan SPK (Supervisor Only)"
          >
            <Pencil class="w-3.5 h-3.5" />
            <span>Edit SPK</span>
          </button>

          <button
            @click="$emit('close')"
            class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 rounded-xl transition-all cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="p-6 overflow-y-auto space-y-6 text-xs text-slate-700 custom-scrollbar">
        <div v-if="loading" class="py-16 text-center text-slate-400 font-medium">
          Memuat detail lengkap pekerjaan SPK...
        </div>

        <template v-else-if="workOrder">
          <!-- Stepper Progress -->
          <div class="glass-card rounded-2xl p-4 border border-white/60">
            <StepperProgress :status="workOrder.status" :progressPercent="workOrder.progress_percent" :hasBa="!!workOrder.ba_document" />
          </div>

          <!-- Archived SPK Banner -->
          <div
            v-if="workOrder.is_archived"
            class="p-4 bg-amber-500/15 border border-amber-300 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 backdrop-blur-md"
          >
            <div class="flex items-center gap-2.5 text-amber-950">
              <Archive class="w-5 h-5 text-amber-700 shrink-0" />
              <div>
                <div class="font-bold text-sm">SPK Ini Telah Diarsipkan (Archived)</div>
                <div class="text-[11px] text-amber-800">
                  Diarsipkan pada {{ workOrder.archived_at ? new Date(workOrder.archived_at).toLocaleString('id-ID') : 'sebelumnya' }}. Seluruh bukti dan riwayat tetap tersimpan aman.
                </div>
              </div>
            </div>
            <button
              v-if="isSuperuser"
              type="button"
              @click="handleUnarchiveSpk"
              :disabled="archiving"
              class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer active:scale-95 transition-all disabled:opacity-50"
            >
              {{ archiving ? 'Memulihkan...' : 'Pulihkan ke Aktif' }}
            </button>
          </div>

          <!-- Action Banners -->
          <div
            v-if="['SUBMITTED', 'IN_PROGRESS', 'UNDER_REVIEW'].includes(workOrder.status)"
            class="p-4 bg-purple-500/10 border border-purple-300 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 backdrop-blur-md"
          >
            <div class="flex items-center gap-2.5 text-purple-950">
              <CheckCircle2 class="w-5 h-5 text-purple-600 shrink-0" />
              <div>
                <div class="font-bold text-sm">Pekerjaan Siap Direview & Diverifikasi</div>
                <div class="text-[11px] text-purple-700">Foto dokumentasi dan data verifikasi lapangan telah tersedia.</div>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="handleQuickApprove"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer flex items-center gap-1.5 active:scale-95 transition-all"
              >
                <CheckCircle2 class="w-4 h-4" />
                <span>Setujui (Approve)</span>
              </button>
              <button
                @click="handleGoToReview"
                class="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer active:scale-95 transition-all"
              >
                Buka Review Console →
              </button>
            </div>
          </div>

          <!-- Approved & Ready for BA Generation Banner (Shown ONLY when BA is NOT yet generated) -->
          <div
            v-if="workOrder.status === 'APPROVED' && !workOrder.ba_document"
            class="p-4 bg-emerald-500/10 border border-emerald-300 rounded-2xl flex items-center justify-between backdrop-blur-md"
          >
            <div class="flex items-center gap-2.5 text-emerald-950">
              <CheckCircle2 class="w-5 h-5 text-emerald-600" />
              <div>
                <div class="font-bold text-sm">Pekerjaan Telah Disetujui (Approved)</div>
                <div class="text-[11px] text-emerald-700">Siap untuk diterbitkan Berita Acara (BA) Opname resmi.</div>
              </div>
            </div>
            <button
              type="button"
              @click="handleGenerateBa"
              class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs flex items-center gap-1.5 active:scale-95 transition-all cursor-pointer"
            >
              <FileCheck2 class="w-4 h-4" />
              <span>Terbitkan BA Opname</span>
            </button>
          </div>

          <!-- BA Opname Generated Banner -->
          <div
            v-if="workOrder.ba_document"
            class="p-4 bg-teal-500/10 border border-teal-300 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 backdrop-blur-md"
          >
            <div class="flex items-center gap-2.5 text-teal-950">
              <FileCheck2 class="w-5 h-5 text-teal-600 shrink-0" />
              <div>
                <div class="font-bold text-sm">BA Opname Resmi Terbit ({{ workOrder.ba_document.ba_number }})</div>
                <div class="text-[11px] text-teal-700">Diterbitkan pada {{ new Date(workOrder.ba_document.ba_date).toLocaleDateString('id-ID') }}</div>
              </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
              <button
                type="button"
                @click="openBaViewer(workOrder.ba_document || workOrder.id)"
                class="px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer flex items-center gap-1.5 active:scale-95 transition-all"
              >
                <FileCheck2 class="w-4 h-4" />
                <span>Lihat BA</span>
              </button>
            </div>
          </div>

          <!-- Info & Assignment Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Information Card -->
            <div class="glass-card rounded-2xl p-4 space-y-2.5 border border-white/70">
              <h4 class="font-bold text-slate-900 border-b border-slate-200/80 pb-2">Informasi Pekerjaan</h4>
              <div class="space-y-2 text-slate-600">
                <div class="flex justify-between">
                  <span>Perusahaan Client:</span>
                  <span class="font-bold text-slate-900">{{ workOrder.vendor?.name || workOrder.vendor_name || 'Client' }} {{ (workOrder.vendor?.code || workOrder.vendor_code) ? `(${workOrder.vendor?.code || workOrder.vendor_code})` : '' }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Area Operasional:</span>
                  <span class="font-bold text-slate-900">{{ workOrder.area?.name || workOrder.area_name || '-' }}</span>
                </div>
                <div v-if="canViewFinancial" class="flex justify-between">
                  <span>Nilai Kontrak (Rp):</span>
                  <span class="font-bold font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                    Rp {{ Number(workOrder.contract_value || 0).toLocaleString('id-ID') }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span>Titik Lokasi:</span>
                  <span class="font-bold text-slate-900 text-right max-w-[200px]">{{ workOrder.location_name }}</span>
                </div>
                <div class="flex items-center justify-between text-[11px] pt-1 border-t border-slate-100">
                  <span class="text-slate-500">Target GPS:</span>
                  <div class="flex items-center gap-1.5">
                    <span class="font-mono text-slate-800 font-bold">
                      {{ workOrder.target_lat ? `${Number(workOrder.target_lat).toFixed(4)}, ${Number(workOrder.target_lng).toFixed(4)}` : 'Belum di-set' }}
                    </span>
                    <button
                      type="button"
                      @click="syncCurrentGpsToSpk"
                      class="px-2 py-0.5 bg-brand-100 hover:bg-brand-200 text-brand-900 text-[10px] font-bold rounded-md transition-all active:scale-95 cursor-pointer"
                      title="Update target koordinat SPK ini sesuai posisi GPS saya saat ini"
                    >
                      📍 Set GPS Saya
                    </button>
                  </div>
                </div>
                <div class="flex justify-between items-center text-[11px]">
                  <span class="text-slate-500">Wajib Cek Lokasi:</span>
                  <button
                    type="button"
                    @click="toggleRequireCheckin"
                    class="px-2 py-0.5 rounded-md text-[10px] font-bold border transition-all cursor-pointer"
                    :class="workOrder.require_checkin ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-600 border-slate-300'"
                  >
                    {{ workOrder.require_checkin ? 'AKTIF (Wajib Check-In)' : 'NONAKTIF (Bebas Lokasi)' }}
                  </button>
                </div>
                <div class="flex justify-between">
                  <span>Periode:</span>
                  <span class="font-mono text-slate-900 font-bold">{{ workOrder.start_date }} s/d {{ workOrder.deadline }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Mode Evidence:</span>
                  <span class="font-bold text-brand-700">{{ workOrder.doc_mode }}</span>
                </div>
              </div>
            </div>

            <!-- Team Assignment Card -->
            <div class="glass-card rounded-2xl p-4 space-y-3 border border-white/70">
              <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                <h4 class="font-bold text-slate-900">Penugasan Tim Lapangan</h4>
                <span class="text-[10px] text-slate-400 font-bold uppercase">PIC & ANGGOTA</span>
              </div>

              <div>
                <label class="block font-bold text-slate-700 mb-1">PIC Utama (Wajib):</label>
                <select
                  v-model="selectedPic"
                  class="w-full px-3 py-2 bg-white/90 border border-slate-200/80 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none text-xs"
                >
                  <option value="">-- Pilih PIC Lapangan --</option>
                  <option v-for="u in fieldUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.phone }})</option>
                </select>
              </div>

              <div>
                <label class="block font-bold text-slate-700 mb-1">Anggota Tim Tambahan:</label>
                <div class="space-y-1.5 max-h-24 overflow-y-auto bg-white/90 p-2.5 border border-slate-200/80 rounded-xl custom-scrollbar">
                  <label
                    v-for="u in fieldUsers.filter(u => String(u.id) !== String(selectedPic))"
                    :key="u.id"
                    class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer"
                  >
                    <input
                      type="checkbox"
                      :value="u.id"
                      v-model="selectedMembers"
                      class="rounded text-brand-600 focus:ring-brand-500"
                    />
                    <span>{{ u.name }}</span>
                  </label>
                </div>
              </div>

              <button
                type="button"
                @click="handleSaveAssignment"
                :disabled="assigning"
                class="w-full py-2 bg-gradient-to-r from-brand-900 to-brand-700 hover:from-brand-800 hover:to-brand-600 text-white font-bold rounded-xl transition-all shadow-xs active:scale-95 disabled:opacity-50"
              >
                {{ assigning ? 'Menyimpan...' : 'Simpan Penugasan Tim' }}
              </button>
            </div>
          </div>

          <!-- Check-In History -->
          <div class="glass-card rounded-2xl p-4 space-y-2 border border-white/70">
            <h4 class="font-bold text-slate-900 flex items-center gap-2">
              <Navigation class="w-4 h-4 text-brand-600" />
              <span>Riwayat Check-In GPS Lapangan</span>
            </h4>
            <div v-if="(workOrder.check_ins || workOrder.checkIns || []).length > 0" class="space-y-2">
              <div
                v-for="ci in (workOrder.check_ins || workOrder.checkIns || [])"
                :key="ci.id"
                class="p-3 bg-white/90 border border-slate-200/80 rounded-xl flex items-center justify-between shadow-xs"
              >
                <div>
                  <div class="font-bold text-slate-900">{{ ci.user_name || ci.user?.name || 'Teknisi Lapangan' }}</div>
                  <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                    GPS: {{ Number(ci.latitude || 0).toFixed(6) }}, {{ Number(ci.longitude || 0).toFixed(6) }} (±{{ ci.accuracy || 0 }}m)
                  </div>
                  <div v-if="ci.address_note" class="text-[10px] text-slate-400 italic mt-0.5">"{{ ci.address_note }}"</div>
                </div>
                <div class="text-right">
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 border border-emerald-300">
                    TERVERIFIKASI ✓
                  </span>
                  <div class="text-[10px] text-slate-400 font-mono mt-1">
                    {{ ci.server_timestamp ? new Date(ci.server_timestamp).toLocaleString('id-ID') : '-' }}
                  </div>
                </div>
              </div>
            </div>
            <p v-else class="text-slate-400 text-xs italic">Belum ada catatan check-in GPS untuk pekerjaan ini.</p>
          </div>

          <!-- Technical Issues / Kendala Lapangan Log -->
          <div class="glass-card rounded-2xl p-4 space-y-3 border border-white/70">
            <div class="flex items-center justify-between">
              <h4 class="font-bold text-slate-900 flex items-center gap-2">
                <AlertTriangle class="w-4 h-4 text-amber-600" />
                <span>Riwayat Kendala Teknis & Mitigasi Lapangan</span>
              </h4>
              <span
                v-if="workOrder.issues && workOrder.issues.length > 0"
                class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                :class="workOrder.issues.some(i => i.status === 'OPEN') ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800'"
              >
                {{ workOrder.issues.filter(i => i.status === 'OPEN').length > 0 ? `${workOrder.issues.filter(i => i.status === 'OPEN').length} Perlu Tindakan` : 'Semua Kendala Teratasi ✓' }}
              </span>
            </div>

            <div v-if="workOrder.issues && workOrder.issues.length > 0" class="space-y-2.5">
              <div
                v-for="iss in workOrder.issues"
                :key="iss.id"
                class="p-3.5 rounded-xl border space-y-2 text-xs shadow-xs"
                :class="iss.status === 'OPEN' ? 'bg-amber-50/60 border-amber-200' : 'bg-slate-50/60 border-slate-200'"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span
                      :class="[
                        'px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider',
                        iss.status === 'OPEN' ? 'bg-rose-600 text-white animate-pulse' : 'bg-emerald-600 text-white'
                      ]"
                    >
                      {{ iss.status === 'OPEN' ? 'OPEN / BUTUH TINDAKAN' : 'RESOLVED ✓' }}
                    </span>
                    <span class="font-bold text-slate-800">{{ iss.issue_type || 'Kendala Teknis' }}</span>
                  </div>
                  <span class="text-[10px] font-mono text-slate-400">
                    {{ new Date(iss.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                  </span>
                </div>

                <p class="text-slate-800 leading-relaxed font-medium">
                  "{{ iss.notes }}"
                </p>

                <!-- Solution note if resolved -->
                <div v-if="iss.resolution_notes" class="p-2.5 bg-emerald-50 rounded-lg text-[11px] text-emerald-950 border border-emerald-200/80">
                  <div class="font-bold text-emerald-800 flex items-center justify-between mb-0.5">
                    <span>Tindakan Solusi Pengawas:</span>
                    <span class="font-mono text-[9px] text-emerald-700">Oleh: {{ iss.resolver_name || 'Pengawas SGX' }}</span>
                  </div>
                  <p>{{ iss.resolution_notes }}</p>
                </div>
              </div>
            </div>
            <p v-else class="text-slate-400 text-xs italic">Tidak ada catatan kendala teknis untuk pekerjaan cabang ini (Pekerjaan berjalan lancar).</p>
          </div>

          <!-- Sub-Tasks & Grouped Photo Evidence Section -->
          <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div>
                <h4 class="font-black text-sm text-slate-900 flex items-center gap-2">
                  <CheckSquare class="w-4 h-4 text-purple-700" />
                  <span>Lingkup Sub-Pekerjaan & Evidensi Fisik ({{ displayItems.length }} Item)</span>
                </h4>
                <p class="text-[11px] text-slate-500">Dokumentasi foto disusun dan dikelompokkan secara terstruktur per sub-lingkup pekerjaan.</p>
              </div>

              <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
                <!-- Add Sub-Work Order Item Button (Addendum) -->
                <button
                  v-if="!['APPROVED', 'COMPLETED', 'BA_OPNAME'].includes(workOrder.status) && isSupervisor"
                  type="button"
                  @click="openAddendumModal"
                  class="px-3 py-1.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs active:scale-95 transition-all cursor-pointer"
                  title="Tambah lingkup pekerjaan baru pada SPK ini"
                >
                  <Plus class="w-3.5 h-3.5" />
                  <span>+ Tambah Sub-Pekerjaan</span>
                </button>

                <!-- Toggle View Mode: By Item vs All Stages -->
                <div class="bg-slate-200/80 p-0.5 rounded-xl flex items-center text-[10px] font-bold">
                  <button
                    type="button"
                    @click="photoViewMode = 'BY_ITEM'"
                    :class="[
                      'px-2.5 py-1 rounded-lg transition-all cursor-pointer',
                      photoViewMode === 'BY_ITEM' ? 'bg-white text-purple-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'
                    ]"
                  >
                    Per Sub-Item
                  </button>
                  <button
                    type="button"
                    @click="photoViewMode = 'ALL_STAGES'"
                    :class="[
                      'px-2.5 py-1 rounded-lg transition-all cursor-pointer',
                      photoViewMode === 'ALL_STAGES' ? 'bg-white text-purple-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'
                    ]"
                  >
                    Semua Tahap
                  </button>
                </div>

                <button
                  v-if="workOrder.evidence_photos && workOrder.evidence_photos.length > 0"
                  type="button"
                  @click="downloadAllPhotos"
                  class="px-3 py-1.5 bg-gradient-to-r from-purple-800 to-indigo-600 hover:from-purple-700 hover:to-indigo-500 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs active:scale-95 transition-all cursor-pointer"
                  title="Unduh semua foto bukti SPK ini"
                >
                  <Download class="w-3.5 h-3.5" />
                  <span class="hidden sm:inline">Unduh Semua Foto</span>
                </button>
              </div>
            </div>

            <!-- MODE 1: STRUCTURED SUB-TASK TRIPTYCH CARDS (Per Sub-Pekerjaan) -->
            <div v-if="photoViewMode === 'BY_ITEM'" class="space-y-4">
              <div
                v-for="(item, itmIdx) in displayItems"
                :key="item.id || itmIdx"
                class="glass-card rounded-2xl p-4 sm:p-5 border border-white/80 space-y-3.5 shadow-xs"
              >
                <!-- Sub-Task Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200/80 pb-3">
                  <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-purple-900 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                      {{ itmIdx + 1 }}
                    </span>
                    <div>
                      <div class="flex items-center gap-2">
                        <h5 class="font-black text-slate-900 text-xs sm:text-sm">
                          {{ item.item_name }}
                        </h5>
                        <span
                          v-if="item.is_addendum"
                          class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-amber-500 text-white shadow-2xs animate-pulse"
                        >
                          + ADDENDUM / TAMBAHAN
                        </span>
                      </div>
                      <div class="flex items-center gap-2 text-[10px] text-slate-500 mt-0.5">
                        <span class="bg-slate-100 px-1.5 py-0.5 rounded font-mono">Bobot: {{ item.weight_percent || 100 }}%</span>
                        <span>•</span>
                        <span>Mode: <strong class="text-purple-900">{{ item.doc_mode || workOrder.doc_mode }}</strong></span>
                      </div>
                    </div>
                  </div>

                  <!-- Item Completeness Status Badge -->
                  <div class="flex items-center gap-2">
                    <span
                      :class="[
                        'px-2.5 py-1 rounded-full text-[10px] font-bold border',
                        isItemFullyDocumented(item)
                          ? 'bg-emerald-50 text-emerald-800 border-emerald-300'
                          : 'bg-amber-50 text-amber-800 border-amber-300'
                      ]"
                    >
                      {{ isItemFullyDocumented(item) ? 'Evidensi Lengkap ✓' : 'Dalam Pengerjaan ⏳' }}
                    </span>
                  </div>
                </div>

                <!-- Dynamic Evidence Grid: Adapts to doc_mode (1-Stage, 2-Stage, 3-Stage) -->
                <div
                  :class="[
                    isAfterOnly(item)
                      ? 'grid grid-cols-1 gap-3'
                      : isTwoStages(item)
                      ? 'grid grid-cols-1 sm:grid-cols-2 gap-3'
                      : 'grid grid-cols-1 sm:grid-cols-3 gap-3'
                  ]"
                >
                  <!-- BEFORE Column (Disembunyikan jika AFTER_ONLY) -->
                  <div v-if="!isAfterOnly(item)" class="p-3 bg-amber-50/40 rounded-xl border border-amber-200/70 space-y-2">
                    <div class="flex items-center justify-between">
                      <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-500 text-white shadow-2xs">
                        KONDISI AWAL (BEFORE)
                      </span>
                      <span class="text-[9px] font-mono text-slate-400">
                        {{ getPhotosForItemStage(item.id, 'BEFORE').length }} Foto
                      </span>
                    </div>

                    <div v-if="getPhotosForItemStage(item.id, 'BEFORE').length > 0" class="space-y-2">
                      <div
                        v-for="(p, pIdx) in getPhotosForItemStage(item.id, 'BEFORE')"
                        :key="p.id"
                        @click="openLightbox(p)"
                        class="h-32 rounded-lg overflow-hidden bg-slate-900 relative group cursor-pointer shadow-xs border border-white/60"
                      >
                        <img
                          :src="getFileUrl(p.file_path)"
                          alt="Foto Before"
                          class="w-full h-full object-cover group-hover:scale-105 transition-all"
                        />
                        <button
                          type="button"
                          @click.stop="downloadSinglePhoto(p)"
                          class="absolute bottom-1.5 right-1.5 w-6 h-6 rounded-full bg-slate-900/90 hover:bg-purple-700 text-white shadow-md flex items-center justify-center transition-all cursor-pointer border border-white/40 z-10"
                        >
                          <Download class="w-3 h-3" />
                        </button>
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-1.5 text-[9px] text-white font-mono pointer-events-none">
                          <div class="truncate text-emerald-300">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</div>
                        </div>
                      </div>
                    </div>
                    <div v-else class="h-28 rounded-lg border-2 border-dashed border-amber-200/80 flex flex-col items-center justify-center text-slate-400 text-[10px] text-center p-2">
                      <span>Belum ada foto Before</span>
                    </div>
                  </div>

                  <!-- PROCESS Column (Disembunyikan jika AFTER_ONLY atau TWO_STAGES) -->
                  <div v-if="!isAfterOnly(item) && !isTwoStages(item)" class="p-3 bg-blue-50/40 rounded-xl border border-blue-200/70 space-y-2">
                    <div class="flex items-center justify-between">
                      <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-blue-600 text-white shadow-2xs">
                        PROSES KERJA (PROCESS)
                      </span>
                      <span class="text-[9px] font-mono text-slate-400">
                        {{ getPhotosForItemStage(item.id, 'PROCESS').length }} Foto
                      </span>
                    </div>

                    <div v-if="getPhotosForItemStage(item.id, 'PROCESS').length > 0" class="space-y-2">
                      <div
                        v-for="(p, pIdx) in getPhotosForItemStage(item.id, 'PROCESS')"
                        :key="p.id"
                        @click="openLightbox(p)"
                        class="h-32 rounded-lg overflow-hidden bg-slate-900 relative group cursor-pointer shadow-xs border border-white/60"
                      >
                        <img
                          :src="getFileUrl(p.file_path)"
                          alt="Foto Process"
                          class="w-full h-full object-cover group-hover:scale-105 transition-all"
                        />
                        <button
                          type="button"
                          @click.stop="downloadSinglePhoto(p)"
                          class="absolute bottom-1.5 right-1.5 w-6 h-6 rounded-full bg-slate-900/90 hover:bg-purple-700 text-white shadow-md flex items-center justify-center transition-all cursor-pointer border border-white/40 z-10"
                        >
                          <Download class="w-3 h-3" />
                        </button>
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-1.5 text-[9px] text-white font-mono pointer-events-none">
                          <div class="truncate text-emerald-300">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</div>
                        </div>
                      </div>
                    </div>
                    <div v-else class="h-28 rounded-lg border-2 border-dashed border-blue-200/80 flex flex-col items-center justify-center text-slate-400 text-[10px] text-center p-2">
                      <span>Belum ada foto Process</span>
                    </div>
                  </div>

                  <!-- AFTER Column (Hasil Akhir) -->
                  <div class="p-3 bg-emerald-50/40 rounded-xl border border-emerald-200/70 space-y-2">
                    <div class="flex items-center justify-between">
                      <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-emerald-600 text-white shadow-2xs">
                        {{ isAfterOnly(item) ? 'DOKUMENTASI HASIL PEKERJAAN (AFTER)' : 'HASIL AKHIR (AFTER)' }}
                      </span>
                      <span class="text-[9px] font-mono text-slate-400">
                        {{ getPhotosForItemStage(item.id, 'AFTER').length }} Foto
                      </span>
                    </div>

                    <div v-if="getPhotosForItemStage(item.id, 'AFTER').length > 0" :class="isAfterOnly(item) ? 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5' : 'space-y-2'">
                      <div
                        v-for="(p, pIdx) in getPhotosForItemStage(item.id, 'AFTER')"
                        :key="p.id"
                        @click="openLightbox(p)"
                        class="h-32 sm:h-36 rounded-lg overflow-hidden bg-slate-900 relative group cursor-pointer shadow-xs border border-white/60"
                      >
                        <img
                          :src="getFileUrl(p.file_path)"
                          alt="Foto After"
                          class="w-full h-full object-cover group-hover:scale-105 transition-all"
                        />
                        <button
                          type="button"
                          @click.stop="downloadSinglePhoto(p)"
                          class="absolute bottom-1.5 right-1.5 w-6 h-6 rounded-full bg-slate-900/90 hover:bg-purple-700 text-white shadow-md flex items-center justify-center transition-all cursor-pointer border border-white/40 z-10"
                        >
                          <Download class="w-3 h-3" />
                        </button>
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-1.5 text-[9px] text-white font-mono pointer-events-none">
                          <div class="truncate text-emerald-300">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</div>
                        </div>
                      </div>
                    </div>
                    <div v-else class="h-28 rounded-lg border-2 border-dashed border-emerald-200/80 flex flex-col items-center justify-center text-slate-400 text-[10px] text-center p-2">
                      <span>Belum ada foto After</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- MODE 2: FLAT STAGE VIEW (Semua Tahap) -->
            <div v-else class="space-y-3">
              <template v-for="stage in ['BEFORE', 'PROCESS', 'AFTER', 'ISSUE']" :key="stage">
                <div
                  v-if="!(workOrder.evidence_photos?.filter(p => p.stage === stage).length === 0 && workOrder.doc_mode === 'AFTER_ONLY' && stage !== 'AFTER')"
                  class="glass-card rounded-2xl p-3.5 border border-white/70"
                >
                  <div class="font-bold text-xs text-slate-800 mb-2.5 flex items-center justify-between">
                    <span>TAHAP: {{ stage }}</span>
                    <span class="text-slate-400 font-normal">({{ workOrder.evidence_photos?.filter(p => p.stage === stage).length || 0 }} foto)</span>
                  </div>
                  <div
                    v-if="workOrder.evidence_photos?.filter(p => p.stage === stage).length > 0"
                    class="grid grid-cols-2 sm:grid-cols-4 gap-2.5"
                  >
                    <div
                      v-for="(p, pIdx) in workOrder.evidence_photos.filter(p => p.stage === stage)"
                      :key="p.id"
                      @click="openLightbox(p)"
                      class="bg-slate-900 rounded-xl overflow-hidden p-1 shadow-xs group relative cursor-pointer hover:shadow-md transition-all"
                    >
                      <img
                        :src="getFileUrl(p.file_path)"
                        :alt="p.file_name"
                        class="w-full h-24 object-cover rounded-lg group-hover:scale-105 transition-transform duration-300"
                        @error="$event.target.src = 'https://images.unsplash.com/photo-1541888946425-d0fbb18086f6?w=400&auto=format&fit=crop&q=60'"
                      />

                      <!-- Corner Download Button (Bottom-Right) -->
                      <button
                        type="button"
                        @click.stop="downloadSinglePhoto(p)"
                        class="absolute bottom-2 right-2 w-6 h-6 rounded-full bg-slate-900/90 hover:bg-purple-700 text-white shadow-md flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95 cursor-pointer border border-white/40 backdrop-blur-xs z-10"
                        title="Unduh Foto Resolusi Asli"
                      >
                        <Download class="w-3 h-3" />
                      </button>

                      <div class="p-1.5 text-[10px] text-white">
                        <div class="font-bold truncate">Foto #{{ p.sequence || pIdx + 1 }}</div>
                        <div class="text-[8px] font-mono text-emerald-400 truncate">SHA-256: {{ p.file_hash?.substring(0, 10) }}...</div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-slate-400 text-xs italic">Belum ada foto pada tahap {{ stage }}.</div>
                </div>
              </template>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- Reusable Photo Lightbox Full-Screen Viewer -->
    <PhotoLightboxModal
      :isOpen="isLightboxOpen"
      :photos="workOrder?.evidence_photos || []"
      :initialIndex="selectedLightboxIndex"
      @close="isLightboxOpen = false"
    />

    <!-- Supervisor Edit SPK Modal -->
    <WorkOrderEditModal
      :isOpen="isEditModalOpen"
      :workOrderId="workOrder?.id"
      @close="isEditModalOpen = false"
      @updated="onSpkUpdated"
    />

    <!-- Share Live Tracking SPK Modal -->
    <ShareSpkModal
      v-if="showShareModal && workOrder"
      :workOrder="workOrder"
      @close="showShareModal = false"
    />

    <!-- Add Addendum Sub-Task Modal Dialog -->
    <Teleport to="body">
      <div
        v-if="showAddendumModal"
        class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-in"
      >
        <div class="glass-modal rounded-3xl max-w-md w-full shadow-2xl p-6 space-y-4 text-xs border border-white/80">
          <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
            <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
              <Plus class="w-4 h-4 text-amber-600" />
              <span>Tambah Sub-Pekerjaan (Addendum)</span>
            </h3>
            <button @click="showAddendumModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
              <X class="w-5 h-5" />
            </button>
          </div>

          <div class="p-3 bg-amber-50 border border-amber-200 rounded-2xl text-amber-900 text-[11px] space-y-1">
            <p class="font-bold">Penambahan lingkup kerja pada SPK berjalan:</p>
            <p class="font-mono text-[10px]">{{ workOrder?.spk_number }} - {{ workOrder?.title }}</p>
            <p class="text-amber-800 text-[10px]">Item baru akan otomatis muncul di tugas teknisi lapangan dan portal klien dengan status pekerjaan tambahan.</p>
          </div>

          <div class="space-y-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Nama Lingkup / Sub-Pekerjaan Tambahan <span class="text-rose-500">*</span>:</label>
              <input
                type="text"
                v-model="addendumForm.itemName"
                placeholder="Contoh: Pemasangan Stiker Sandblast Kaca Depan (12 m²)"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Mode Dokumentasi Foto:</label>
              <select
                v-model="addendumForm.docMode"
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs"
              >
                <option value="BEFORE_PROCESS_AFTER">BEFORE ➔ PROCESS ➔ AFTER (Standar Lengkap)</option>
                <option value="BEFORE_AFTER">BEFORE ➔ AFTER (Tanpa Foto Proses)</option>
                <option value="AFTER_ONLY">AFTER ONLY (Hanya Foto Hasil Akhir)</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Catatan / Spesifikasi Tambahan:</label>
              <textarea
                rows="2"
                v-model="addendumForm.notes"
                placeholder="Spesifikasi material, ukuran, atau instruksi khusus dari klien/pengawas..."
                class="w-full px-3 py-2 bg-white/90 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs"
              />
            </div>
          </div>

          <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
            <button
              type="button"
              @click="showAddendumModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="handleSubmitAddendum"
              :disabled="submittingAddendum"
              class="px-5 py-2 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold rounded-xl shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-95 disabled:opacity-50"
            >
              <Plus class="w-3.5 h-3.5" />
              <span>{{ submittingAddendum ? 'Menyimpan...' : 'Simpan & Terbitkan Item' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { api, getFileUrl } from '../../services/api';
import { useAuth } from '../../composables/useAuth';
import StatusBadge from '../../components/StatusBadge.vue';
import StepperProgress from '../../components/StepperProgress.vue';
import PhotoLightboxModal from '../../components/PhotoLightboxModal.vue';
import WorkOrderEditModal from './WorkOrderEditModal.vue';
import ShareSpkModal from '../../components/ShareSpkModal.vue';
import {
  X,
  MapPin,
  CheckCircle2,
  AlertTriangle,
  FileCheck2,
  Navigation,
  Camera,
  RotateCcw,
  Download,
  Pencil,
  Check,
  Share2,
  CheckSquare,
  Plus,
  Archive
} from 'lucide-vue-next';

const auth = useAuth();
const isSuperuser = computed(() => auth.state.user?.role === 'SUPERUSER');
const isSupervisor = computed(() => ['SUPERUSER', 'SUPERVISOR', 'ADMIN'].includes(auth.state.user?.role));
const canViewFinancial = computed(() => ['SUPERUSER', 'SUPERVISOR', 'ADMIN'].includes(auth.state.user?.role));
const isEditModalOpen = ref(false);
const showShareModal = ref(false);
const photoViewMode = ref('BY_ITEM'); // 'BY_ITEM' | 'ALL_STAGES'
const archiving = ref(false);

async function handleArchiveSpk() {
  if (!confirm(`Apakah Anda yakin ingin mengarsipkan SPK ${workOrder.value?.spk_number}?\nSPK yang diarsipkan akan disembunyikan dari daftar aktif operasional.`)) {
    return;
  }

  archiving.value = true;
  try {
    const res = await api.archiveWorkOrder(props.workOrderId);
    if (res.success || res.data) {
      alert(`SPK ${workOrder.value?.spk_number} berhasil diarsipkan.`);
      await loadDetail();
      emit('refresh-list');
    }
  } catch (err) {
    console.error('Failed to archive SPK:', err);
    alert('Gagal mengarsipkan SPK: ' + (err.message || 'Terjadi kesalahan'));
  } finally {
    archiving.value = false;
  }
}

async function handleUnarchiveSpk() {
  if (!confirm(`Apakah Anda yakin ingin memulihkan SPK ${workOrder.value?.spk_number} kembali ke daftar aktif?`)) {
    return;
  }

  archiving.value = true;
  try {
    const res = await api.unarchiveWorkOrder(props.workOrderId);
    if (res.success || res.data) {
      alert(`SPK ${workOrder.value?.spk_number} berhasil dipulihkan ke daftar aktif.`);
      await loadDetail();
      emit('refresh-list');
    }
  } catch (err) {
    console.error('Failed to unarchive SPK:', err);
    alert('Gagal memulihkan SPK: ' + (err.message || 'Terjadi kesalahan'));
  } finally {
    archiving.value = false;
  }
}

// Addendum Sub-Task Modal State
const showAddendumModal = ref(false);
const submittingAddendum = ref(false);
const addendumForm = ref({
  itemName: '',
  docMode: 'BEFORE_PROCESS_AFTER',
  notes: ''
});

function openAddendumModal() {
  addendumForm.value = {
    itemName: '',
    docMode: workOrder.value?.doc_mode || 'BEFORE_PROCESS_AFTER',
    notes: ''
  };
  showAddendumModal.value = true;
}

async function handleSubmitAddendum() {
  if (!addendumForm.value.itemName.trim()) {
    alert('Nama sub-pekerjaan tambahan wajib diisi!');
    return;
  }

  submittingAddendum.value = true;
  try {
    const res = await api.addWorkOrderItem(props.workOrderId, {
      item_name: addendumForm.value.itemName.trim(),
      doc_mode: addendumForm.value.docMode,
      notes: addendumForm.value.notes.trim()
    });

    if (res.success || res.data) {
      alert(`Pekerjaan tambahan '${addendumForm.value.itemName}' berhasil ditambahkan ke SPK.`);
      showAddendumModal.value = false;
      await loadDetail();
      emit('refresh-list');
    }
  } catch (err) {
    console.error('Failed to add addendum item:', err);
    alert('Gagal menambahkan sub-pekerjaan: ' + (err.message || 'Terjadi kesalahan'));
  } finally {
    submittingAddendum.value = false;
  }
}

const props = defineProps({
  workOrderId: {
    type: [String, Number],
    default: null
  }
});

const emit = defineEmits(['close', 'open-review', 'open-ba', 'refresh-list']);

const workOrder = ref(null);
const fieldUsers = ref([]);
const loading = ref(true);
const assigning = ref(false);
const selectedPic = ref('');
const selectedMembers = ref([]);

const displayItems = computed(() => {
  if (workOrder.value?.items && workOrder.value.items.length > 0) {
    return workOrder.value.items;
  }
  return [{
    id: null,
    item_name: workOrder.value?.title || 'Lingkup Pekerjaan Utama',
    doc_mode: workOrder.value?.doc_mode || 'BEFORE_PROCESS_AFTER',
    weight_percent: 100
  }];
});

function getPhotosForItemStage(itemId, stage) {
  const photos = workOrder.value?.evidence_photos || [];
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
  const mode = String(item?.doc_mode || workOrder.value?.doc_mode || '').toUpperCase();
  return mode === 'AFTER_ONLY' || mode === 'ONE_STAGE' || mode === '1_STAGE' || mode === 'AFTER';
}

function isTwoStages(item) {
  const mode = String(item?.doc_mode || workOrder.value?.doc_mode || '').toUpperCase();
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

async function onSpkUpdated(updatedData) {
  await loadDetail();
  emit('refresh-list');
}

async function loadDetail() {
  if (!props.workOrderId) return;
  loading.value = true;
  try {
    const woRes = await api.getWorkOrderById(props.workOrderId);
    workOrder.value = woRes.data || woRes;
    selectedPic.value = workOrder.value?.pic_user_id || '';
    const memberIds = (workOrder.value?.assignments || [])
      .filter(a => (a.pivot?.role_in_team || a.role_in_team) === 'MEMBER')
      .map(a => a.id || a.user_id);
    selectedMembers.value = memberIds;

    try {
      const usersRes = await api.getUsers({ role: 'FIELD_TEAM' });
      fieldUsers.value = usersRes.data || [];
    } catch (uErr) {
      console.warn('Field users load failed:', uErr);
    }
  } catch (err) {
    console.error('Failed to load work order detail:', err);
    alert('Gagal memuat detail SPK: ' + (err.message || 'Koneksi bermasalah'));
  } finally {
    loading.value = false;
  }
}

watch(() => props.workOrderId, () => {
  loadDetail();
}, { immediate: true });

async function handleSaveAssignment() {
  if (!selectedPic.value) {
    alert('PIC tim lapangan wajib dipilih!');
    return;
  }
  assigning.value = true;
  try {
    const picId = parseInt(selectedPic.value, 10);
    const memberIds = selectedMembers.value.map(id => parseInt(id, 10));
    await api.assignTeam(props.workOrderId, {
      pic_user_id: picId,
      picUserId: picId,
      member_ids: memberIds,
      memberUserIds: memberIds
    });
    await loadDetail();
    emit('refresh-list');
    alert('Penugasan tim berhasil disimpan!');
  } catch (err) {
    alert(`Gagal assign tim: ${err.message}`);
  } finally {
    assigning.value = false;
  }
}

async function syncCurrentGpsToSpk() {
  if (!navigator.geolocation) {
    alert('Browser tidak mendukung deteksi lokasi.');
    return;
  }
  navigator.geolocation.getCurrentPosition(
    async (pos) => {
      const lat = parseFloat(pos.coords.latitude.toFixed(6));
      const lng = parseFloat(pos.coords.longitude.toFixed(6));
      try {
        await api.updateWorkOrderLocation(props.workOrderId, lat, lng);
        await loadDetail();
        emit('refresh-list');
        alert(`Target GPS SPK berhasil disinkronkan ke lokasi Anda: ${lat}, ${lng}`);
      } catch (err) {
        alert('Gagal update GPS: ' + err.message);
      }
    },
    (err) => alert('Gagal mendeteksi GPS: ' + err.message),
    { enableHighAccuracy: true, timeout: 8000 }
  );
}

async function toggleRequireCheckin() {
  const newVal = !workOrder.value.require_checkin;
  try {
    await api.toggleWorkOrderCheckin(props.workOrderId, newVal);
    await loadDetail();
    emit('refresh-list');
    alert(`Status Wajib Cek Lokasi berhasil diubah menjadi: ${newVal ? 'AKTIF' : 'NONAKTIF'}`);
  } catch (err) {
    alert('Gagal mengubah pengaturan: ' + err.message);
  }
}

async function handleQuickApprove() {
  try {
    await api.approveWorkOrder({
      work_order_id: workOrder.value.id,
      review_notes: 'Pekerjaan telah disetujui melalui Detail SPK.'
    });
    alert(`Pekerjaan ${workOrder.value.spk_number} berhasil Disetujui (APPROVED)!`);
    await loadDetail();
    emit('refresh-list');
  } catch (e) {
    alert(`Gagal approve: ${e.message}`);
  }
}

function handleGoToReview() {
  emit('close');
  emit('open-review', workOrder.value.id);
}

function openBaViewer(ba) {
  emit('open-ba', ba);
}

async function handleGenerateBa() {
  try {
    const res = await api.generateBa({ work_order_id: workOrder.value.id });
    alert('BA Opname berhasil diterbitkan!');
    await loadDetail();
    emit('open-ba', res.data);
  } catch (err) {
    alert(err.message);
  }
}



/**
 * Lightbox & Photo Download Handlers
 */
const isLightboxOpen = ref(false);
const selectedLightboxIndex = ref(0);

function openLightbox(photo) {
  const allPhotos = workOrder.value?.evidence_photos || [];
  const idx = allPhotos.findIndex(p => p.id === photo.id);
  selectedLightboxIndex.value = idx >= 0 ? idx : 0;
  isLightboxOpen.value = true;
}

function downloadSinglePhoto(photo) {
  if (!photo?.file_path) return;
  const link = document.createElement('a');
  link.href = getFileUrl(photo.file_path);
  const ext = photo.file_name?.split('.').pop() || 'jpg';
  const spk = workOrder.value?.spk_number ? `${workOrder.value.spk_number}_` : '';
  link.download = `${spk}${photo.stage || 'EVIDENCE'}_${photo.sequence || 1}.${ext}`;
  link.target = '_blank';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function downloadAllPhotos() {
  const allPhotos = workOrder.value?.evidence_photos || [];
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
</script>
