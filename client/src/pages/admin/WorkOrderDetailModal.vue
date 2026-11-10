<template>
  <div v-if="workOrderId" class="fixed inset-0 z-50 bg-[#0B0C0E]/85 flex items-center justify-center p-4 overflow-y-auto font-sans">
    <div class="bg-[#13171F] rounded-xs max-w-4xl w-full shadow-2xl overflow-hidden my-6 flex flex-col max-h-[90vh] border border-[#FACC15]">
      <!-- Header -->
      <div class="px-6 py-3.5 border-b border-[#232A36] flex items-center justify-between bg-[#0E1117] shrink-0 font-mono">
        <div>
          <div class="flex items-center gap-2">
            <span class="font-mono font-black text-xs bg-[#FACC15] text-[#0B0C0E] px-2 py-0.5 rounded-xs border border-[#0B0C0E] uppercase tracking-wider">
              SPK: {{ workOrder?.spk_number || 'MEMUAT...' }}
            </span>
            <StatusBadge :status="workOrder?.status" />
          </div>
          <h3 class="font-black text-white text-base mt-1 font-sans">{{ workOrder?.title }}</h3>
        </div>

        <div class="flex items-center gap-2 font-mono">
          <!-- Share Live Tracking Button -->
          <button
            type="button"
            @click="showShareModal = true"
            class="px-3 py-1.5 bg-[#1C2028] hover:bg-[#1A202C] text-[#FACC15] border border-[#232A36] hover:border-[#FACC15] font-bold text-xs rounded-xs flex items-center gap-1.5 transition-colors cursor-pointer uppercase"
            title="Bagikan Tautan Pemantauan Langsung (Live Tracking)"
          >
            <Share2 class="w-3.5 h-3.5 text-[#FACC15]" />
            <span class="hidden sm:inline">BAGIKAN</span>
          </button>

          <!-- Superuser Archive / Unarchive Action Buttons -->
          <template v-if="isSuperuser">
            <button
              v-if="!workOrder?.is_archived && ['APPROVED', 'COMPLETED', 'BA_OPNAME', 'CANCELLED'].includes(workOrder?.status)"
              type="button"
              @click="handleArchiveSpk"
              :disabled="archiving"
              class="px-3 py-1.5 bg-[#1C2028] hover:bg-[#1A202C] text-amber-300 border border-amber-400 font-bold text-xs rounded-xs flex items-center gap-1.5 transition-colors cursor-pointer disabled:opacity-50 uppercase"
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
              class="px-3 py-1.5 bg-[#1C2028] hover:bg-[#1A202C] text-[#10B981] border border-[#10B981] font-bold text-xs rounded-xs flex items-center gap-1.5 transition-colors cursor-pointer disabled:opacity-50 uppercase"
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
            class="px-3 py-1.5 bg-[#1C2028] hover:bg-[#1A202C] text-white border border-[#232A36] hover:border-[#FACC15] font-bold text-xs rounded-xs flex items-center gap-1.5 transition-colors cursor-pointer uppercase"
            title="Edit Data & Pengaturan SPK (Supervisor Only)"
          >
            <Pencil class="w-3.5 h-3.5 text-[#FACC15]" />
            <span>EDIT SPK</span>
          </button>

          <button
            @click="$emit('close')"
            class="p-1.5 text-slate-400 hover:text-[#FACC15] hover:bg-[#1C2028] rounded-xs transition-colors cursor-pointer border border-[#232A36]"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="p-6 overflow-y-auto space-y-5 text-xs text-slate-300 custom-scrollbar">
        <div v-if="loading" class="py-16 text-center text-slate-400 font-mono">
          MEMUAT DETAIL LENGKAP PEKERJAAN SPK...
        </div>

        <template v-else-if="workOrder">
          <!-- Stepper Progress -->
          <div class="cad-surface p-4">
            <StepperProgress :status="workOrder.status" :progressPercent="workOrder.progress_percent" :hasBa="!!workOrder.ba_document" />
          </div>

          <!-- Archived SPK Banner -->
          <div
            v-if="workOrder.is_archived"
            class="p-3 bg-[#1C2028] border border-amber-400/60 rounded-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-amber-200 font-mono"
          >
            <div class="flex items-center gap-2.5">
              <Archive class="w-4 h-4 text-amber-400 shrink-0" />
              <div>
                <div class="font-bold text-xs uppercase text-amber-300">SPK Ini Telah Diarsipkan (Archived)</div>
                <div class="text-[10px] text-slate-400">
                  Diarsipkan pada {{ workOrder.archived_at ? new Date(workOrder.archived_at).toLocaleString('id-ID') : 'sebelumnya' }}. Seluruh bukti dan riwayat tetap tersimpan aman.
                </div>
              </div>
            </div>
            <button
              v-if="isSuperuser"
              type="button"
              @click="handleUnarchiveSpk"
              :disabled="archiving"
              class="px-3 py-1.5 bg-[#13171F] hover:bg-[#1A202C] text-[#10B981] border border-[#10B981] font-bold text-xs rounded-xs cursor-pointer active:scale-95 transition-colors disabled:opacity-50 uppercase"
            >
              {{ archiving ? 'Memulihkan...' : 'Pulihkan ke Aktif' }}
            </button>
          </div>

          <!-- Action Banners -->
          <div
            v-if="['SUBMITTED', 'IN_PROGRESS', 'UNDER_REVIEW'].includes(workOrder.status)"
            class="p-3 bg-[#1C2028] border border-[#FACC15]/60 rounded-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 font-mono"
          >
            <div class="flex items-center gap-2.5 text-slate-200">
              <CheckCircle2 class="w-4 h-4 text-[#FACC15] shrink-0" />
              <div>
                <div class="font-bold text-xs uppercase text-[#FACC15]">Pekerjaan Siap Direview & Diverifikasi</div>
                <div class="text-[10px] text-slate-400">Foto dokumentasi dan data verifikasi lapangan telah tersedia.</div>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="handleQuickApprove"
                class="px-3 py-1.5 bg-[#10B981] hover:bg-emerald-400 text-[#0B0C0E] font-black text-xs rounded-xs cursor-pointer flex items-center gap-1.5 active:scale-95 transition-colors uppercase"
              >
                <CheckCircle2 class="w-3.5 h-3.5" />
                <span>SETUJUI (APPROVE)</span>
              </button>
              <button
                @click="handleGoToReview"
                class="px-3 py-1.5 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] font-black text-xs rounded-xs cursor-pointer active:scale-95 transition-colors uppercase"
              >
                REVIEW CONSOLE &rarr;
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
            <div class="cad-surface p-4 space-y-2.5 rounded-xs border border-[#232A36]">
              <h4 class="font-bold text-white border-b border-[#232A36] pb-2 font-mono uppercase tracking-wider flex items-center justify-between">
                <span>Informasi Pekerjaan</span>
                <span class="text-[10px] text-slate-400 font-normal">SPK DATA</span>
              </h4>
              <div class="space-y-2 text-slate-300">
                <div class="flex justify-between items-start">
                  <span class="text-slate-400 shrink-0">Judul Pekerjaan:</span>
                  <span class="font-bold text-white text-right max-w-[240px] text-[#FACC15]">{{ workOrder.title || '-' }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-slate-400">Jenis Pekerjaan:</span>
                  <span class="font-bold text-white">{{ workOrder.job_type?.name || workOrder.job_type_name || '-' }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-slate-400">Perusahaan Client:</span>
                  <span class="font-bold text-white text-right">{{ workOrder.vendor?.name || workOrder.vendor_name || 'Client' }} {{ (workOrder.vendor?.code || workOrder.vendor_code) ? `(${workOrder.vendor?.code || workOrder.vendor_code})` : '' }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-slate-400">Area Operasional:</span>
                  <span class="font-bold text-white">{{ workOrder.area?.name || workOrder.area_name || '-' }}</span>
                </div>
                <div v-if="canViewFinancial" class="flex justify-between items-center">
                  <span class="text-slate-400">Nilai Kontrak (Rp):</span>
                  <span class="font-bold font-mono text-emerald-400 bg-emerald-950/60 px-2 py-0.5 rounded-xs border border-emerald-600/50">
                    Rp {{ Number(workOrder.contract_value || 0).toLocaleString('id-ID') }}
                  </span>
                </div>
                <div class="flex justify-between items-start">
                  <span class="text-slate-400 shrink-0">Titik Lokasi:</span>
                  <span class="font-bold text-white text-right max-w-[240px]">{{ workOrder.location_name }}</span>
                </div>
                <div class="flex items-center justify-between text-[11px] pt-1 border-t border-[#232A36]">
                  <span class="text-slate-400">Target GPS:</span>
                  <div class="flex items-center gap-1.5">
                    <span class="font-mono text-[#FACC15] font-bold">
                      {{ workOrder.target_lat ? `${Number(workOrder.target_lat).toFixed(4)}, ${Number(workOrder.target_lng).toFixed(4)}` : 'Belum di-set' }}
                    </span>
                    <button
                      type="button"
                      @click="syncCurrentGpsToSpk"
                      class="px-2 py-0.5 bg-[#1C2028] hover:bg-[#232A36] text-[#FACC15] border border-[#232A36] hover:border-[#FACC15] text-[10px] font-bold rounded-xs transition-colors cursor-pointer"
                      title="Update target koordinat SPK ini sesuai posisi GPS saya saat ini"
                    >
                      📍 Set GPS Saya
                    </button>
                  </div>
                </div>
                <div class="flex justify-between items-center text-[11px]">
                  <span class="text-slate-400">Wajib Cek Lokasi:</span>
                  <button
                    type="button"
                    @click="toggleRequireCheckin"
                    class="px-2 py-0.5 rounded-xs text-[10px] font-bold border transition-colors cursor-pointer"
                    :class="workOrder.require_checkin ? 'bg-emerald-950/60 text-emerald-300 border-emerald-500' : 'bg-[#1C2028] text-slate-400 border-[#232A36]'"
                  >
                    {{ workOrder.require_checkin ? 'AKTIF (Wajib Check-In)' : 'NONAKTIF (Bebas Lokasi)' }}
                  </button>
                </div>
                <div class="flex justify-between items-center text-[11px]">
                  <span class="text-slate-400">Berkas Fisik BA Opname:</span>
                  <span
                    class="px-2 py-0.5 rounded-xs text-[10px] font-bold border"
                    :class="workOrder.require_ba_doc ? 'bg-amber-950/60 text-amber-300 border-amber-500' : 'bg-[#1C2028] text-slate-400 border-[#232A36]'"
                  >
                    {{ workOrder.require_ba_doc ? 'WAJIB (Diunggah Sebelum Submit)' : 'BEBAS (Opsional)' }}
                  </span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-slate-400">Periode:</span>
                  <span class="font-mono text-white font-bold">{{ workOrder.start_date }} s/d {{ workOrder.deadline }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-slate-400">Mode Evidence:</span>
                  <span class="font-bold text-[#FACC15]">{{ workOrder.doc_mode }}</span>
                </div>
              </div>
            </div>

            <!-- Team Assignment Card -->
            <div class="cad-surface p-4 space-y-3 rounded-xs border border-[#232A36]">
              <div class="flex items-center justify-between border-b border-[#232A36] pb-2">
                <h4 class="font-bold text-white font-mono uppercase tracking-wider">Penugasan Tim Lapangan</h4>
                <span class="text-[10px] text-slate-400 font-bold uppercase">PIC & ANGGOTA</span>
              </div>

              <div>
                <label class="block font-bold text-slate-200 mb-1">PIC Utama (Wajib):</label>
                <select
                  v-model="selectedPic"
                  class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] text-white rounded-xs focus:outline-none text-xs"
                >
                  <option value="" class="bg-[#0B0C0E] text-slate-400">-- Pilih PIC Lapangan --</option>
                  <option v-for="u in fieldUsers" :key="u.id" :value="u.id" class="bg-[#0B0C0E] text-white">{{ u.name }} ({{ u.phone }})</option>
                </select>
              </div>

              <div>
                <label class="block font-bold text-slate-200 mb-1">Anggota Tim Tambahan:</label>
                <div class="space-y-1.5 max-h-24 overflow-y-auto bg-[#0B0C0E] p-2.5 border border-[#232A36] rounded-xs custom-scrollbar">
                  <label
                    v-for="u in fieldUsers.filter(u => String(u.id) !== String(selectedPic))"
                    :key="u.id"
                    class="flex items-center gap-2 text-xs text-slate-200 cursor-pointer hover:text-white"
                  >
                    <input
                      type="checkbox"
                      :value="u.id"
                      v-model="selectedMembers"
                      class="rounded text-[#FACC15] focus:ring-[#FACC15]"
                    />
                    <span>{{ u.name }}</span>
                  </label>
                </div>
              </div>

              <button
                type="button"
                @click="handleSaveAssignment"
                :disabled="assigning"
                class="w-full py-2 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] font-bold rounded-xs transition-colors shadow-xs active:scale-95 disabled:opacity-50 uppercase tracking-wider cursor-pointer"
              >
                {{ assigning ? 'Menyimpan...' : 'Simpan Penugasan Tim' }}
              </button>
            </div>
          </div>

          <!-- Check-In History -->
          <div class="cad-surface p-4 space-y-2.5 rounded-xs border border-[#232A36]">
            <h4 class="font-bold text-white flex items-center gap-2 font-mono uppercase tracking-wider">
              <Navigation class="w-4 h-4 text-[#FACC15]" />
              <span>Riwayat Check-In GPS Lapangan</span>
            </h4>
            <div v-if="(workOrder.check_ins || workOrder.checkIns || []).length > 0" class="space-y-2">
              <div
                v-for="ci in (workOrder.check_ins || workOrder.checkIns || [])"
                :key="ci.id"
                class="p-3 bg-[#1C2028] border border-[#232A36] rounded-xs flex items-center justify-between"
              >
                <div>
                  <div class="font-bold text-white">{{ ci.user_name || ci.user?.name || 'Teknisi Lapangan' }}</div>
                  <div class="text-[11px] text-slate-300 font-mono mt-0.5">
                    GPS: {{ Number(ci.latitude || 0).toFixed(6) }}, {{ Number(ci.longitude || 0).toFixed(6) }} (±{{ ci.accuracy || 0 }}m)
                  </div>
                  <div v-if="ci.address_note" class="text-[10px] text-slate-400 italic mt-0.5">"{{ ci.address_note }}"</div>
                </div>
                <div class="text-right">
                  <span class="px-2.5 py-0.5 rounded-xs text-[10px] font-bold bg-emerald-950/60 text-emerald-300 border border-emerald-500">
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
          <div class="cad-surface p-4 space-y-3 rounded-xs border border-[#232A36]">
            <div class="flex items-center justify-between">
              <h4 class="font-bold text-white flex items-center gap-2 font-mono uppercase tracking-wider">
                <AlertTriangle class="w-4 h-4 text-amber-400" />
                <span>Riwayat Kendala Teknis & Mitigasi Lapangan</span>
              </h4>
              <span
                v-if="workOrder.issues && workOrder.issues.length > 0"
                class="px-2 py-0.5 rounded-xs text-[10px] font-bold border"
                :class="workOrder.issues.some(i => i.status === 'OPEN') ? 'bg-rose-950/80 text-rose-300 border-rose-500' : 'bg-emerald-950/80 text-emerald-300 border-emerald-500'"
              >
                {{ workOrder.issues.filter(i => i.status === 'OPEN').length > 0 ? `${workOrder.issues.filter(i => i.status === 'OPEN').length} Perlu Tindakan` : 'Semua Kendala Teratasi ✓' }}
              </span>
            </div>

            <div v-if="workOrder.issues && workOrder.issues.length > 0" class="space-y-2.5">
              <div
                v-for="iss in workOrder.issues"
                :key="iss.id"
                class="p-3.5 rounded-xs border border-[#232A36] bg-[#1C2028] space-y-2 text-xs"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span
                      :class="[
                        'px-2 py-0.5 rounded-xs text-[9px] font-black uppercase tracking-wider',
                        iss.status === 'OPEN' ? 'bg-rose-600 text-white animate-pulse' : 'bg-emerald-600 text-white'
                      ]"
                    >
                      {{ iss.status === 'OPEN' ? 'OPEN / BUTUH TINDAKAN' : 'RESOLVED ✓' }}
                    </span>
                    <span class="font-bold text-white">{{ iss.issue_type || 'Kendala Teknis' }}</span>
                  </div>
                  <span class="text-[10px] font-mono text-slate-400">
                    {{ new Date(iss.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                  </span>
                </div>

                <p class="text-slate-200 leading-relaxed font-medium">
                  "{{ iss.notes }}"
                </p>

                <!-- Solution note if resolved -->
                <div v-if="iss.resolution_notes" class="p-2.5 bg-[#13171F] rounded-xs text-[11px] text-slate-200 border border-emerald-500/50">
                  <div class="font-bold text-emerald-400 flex items-center justify-between mb-0.5">
                    <span>Tindakan Solusi Pengawas:</span>
                    <span class="font-mono text-[9px] text-emerald-300">Oleh: {{ iss.resolver_name || 'Pengawas SGX' }}</span>
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
                <h4 class="font-black text-sm text-white flex items-center gap-2 font-mono uppercase tracking-wider">
                  <CheckSquare class="w-4 h-4 text-[#FACC15]" />
                  <span>Lingkup Sub-Pekerjaan & Evidensi Fisik ({{ displayItems.length }} Item)</span>
                </h4>
                <p class="text-[11px] text-slate-400">Dokumentasi foto disusun dan dikelompokkan secara terstruktur per sub-lingkup pekerjaan.</p>
              </div>

              <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
                <!-- Add Sub-Work Order Item Button (Addendum) -->
                <button
                  v-if="!['APPROVED', 'COMPLETED', 'BA_OPNAME'].includes(workOrder.status) && isSupervisor"
                  type="button"
                  @click="openAddendumModal"
                  class="px-3 py-1.5 bg-[#1C2028] hover:bg-[#232A36] text-amber-300 border border-amber-500/60 rounded-xs text-xs font-bold flex items-center gap-1.5 shadow-xs active:scale-95 transition-colors cursor-pointer uppercase"
                  title="Tambah lingkup pekerjaan baru pada SPK ini"
                >
                  <Plus class="w-3.5 h-3.5" />
                  <span>+ Tambah Sub-Pekerjaan</span>
                </button>

                <!-- Superuser Direct Evidence & Document Upload Button -->
                <button
                  v-if="isSuperuser || isSupervisor"
                  type="button"
                  @click="openSuperuserUploadModal"
                  class="px-3 py-1.5 bg-[#1C2028] hover:bg-[#232A36] text-emerald-300 border border-emerald-500/60 rounded-xs text-xs font-bold flex items-center gap-1.5 shadow-xs active:scale-95 transition-colors cursor-pointer uppercase"
                  title="Upload foto evidensi atau dokumen fisik sebagai Superuser / Pengawas"
                >
                  <Upload class="w-3.5 h-3.5" />
                  <span>+ Upload Evidensi (Superuser)</span>
                </button>

                <!-- Toggle View Mode: By Item vs All Stages -->
                <div class="bg-[#0B0C0E] border border-[#232A36] p-0.5 rounded-xs flex items-center text-[10px] font-bold">
                  <button
                    type="button"
                    @click="photoViewMode = 'BY_ITEM'"
                    :class="[
                      'px-2.5 py-1 rounded-xs transition-colors cursor-pointer',
                      photoViewMode === 'BY_ITEM' ? 'bg-[#FACC15] text-[#0B0C0E] font-black shadow-2xs' : 'text-slate-400 hover:text-white'
                    ]"
                  >
                    Per Sub-Item
                  </button>
                  <button
                    type="button"
                    @click="photoViewMode = 'ALL_STAGES'"
                    :class="[
                      'px-2.5 py-1 rounded-xs transition-colors cursor-pointer',
                      photoViewMode === 'ALL_STAGES' ? 'bg-[#FACC15] text-[#0B0C0E] font-black shadow-2xs' : 'text-slate-400 hover:text-white'
                    ]"
                  >
                    Semua Tahap
                  </button>
                </div>

                <button
                  v-if="workOrder.evidence_photos && workOrder.evidence_photos.length > 0"
                  type="button"
                  @click="downloadAllPhotos"
                  class="px-3 py-1.5 bg-[#1C2028] hover:bg-[#232A36] text-[#FACC15] border border-[#232A36] hover:border-[#FACC15] rounded-xs text-xs font-bold flex items-center gap-1.5 shadow-xs active:scale-95 transition-colors cursor-pointer uppercase"
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
                class="cad-surface p-4 sm:p-5 border border-[#232A36] space-y-3.5 shadow-xs rounded-xs"
              >
                <!-- Sub-Task Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#232A36] pb-3">
                  <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-xs bg-[#FACC15] text-[#0B0C0E] font-black text-xs flex items-center justify-center shrink-0">
                      {{ itmIdx + 1 }}
                    </span>
                    <div>
                      <div class="flex items-center gap-2">
                        <h5 class="font-black text-white text-xs sm:text-sm font-sans tracking-wide">
                          {{ item.item_name }}
                        </h5>
                        <span
                          v-if="item.is_addendum"
                          class="px-2 py-0.5 rounded-xs text-[9px] font-black uppercase tracking-wider bg-amber-500 text-[#0B0C0E] animate-pulse"
                        >
                          + ADDENDUM / TAMBAHAN
                        </span>
                      </div>
                      <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-0.5">
                        <span class="bg-[#1C2028] border border-[#232A36] text-slate-300 px-1.5 py-0.5 rounded-xs font-mono">Bobot: {{ item.weight_percent || 100 }}%</span>
                        <span>•</span>
                        <span>Mode: <strong class="text-[#FACC15]">{{ item.doc_mode || workOrder.doc_mode }}</strong></span>
                      </div>
                    </div>
                  </div>

                  <!-- Item Completeness Status Badge -->
                  <div class="flex items-center gap-2">
                    <span
                      :class="[
                        'px-2.5 py-1 rounded-xs text-[10px] font-bold border',
                        isItemFullyDocumented(item)
                          ? 'bg-emerald-950/80 text-emerald-300 border-emerald-500'
                          : 'bg-amber-950/80 text-amber-300 border-amber-500'
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
                  <div v-if="!isAfterOnly(item)" class="p-3 bg-[#1A1811] rounded-xs border border-amber-600/40 space-y-2">
                    <div class="flex items-center justify-between">
                      <span class="px-2 py-0.5 rounded-xs text-[9px] font-black uppercase tracking-wider bg-amber-500 text-[#0B0C0E]">
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
                        class="h-32 rounded-xs overflow-hidden bg-slate-900 relative group cursor-pointer shadow-xs border border-[#232A36]"
                      >
                        <img
                          :src="getFileUrl(p.file_path)"
                          alt="Foto Before"
                          class="w-full h-full object-cover group-hover:scale-105 transition-all"
                        />
                        <button
                          type="button"
                          @click.stop="downloadSinglePhoto(p)"
                          class="absolute bottom-1.5 right-1.5 w-6 h-6 rounded-xs bg-[#0B0C0E]/90 hover:bg-[#FACC15] text-white hover:text-[#0B0C0E] shadow-md flex items-center justify-center transition-colors cursor-pointer border border-[#232A36] z-10"
                        >
                          <Download class="w-3 h-3" />
                        </button>
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/95 to-transparent p-1.5 text-[9px] text-white font-mono pointer-events-none">
                          <div class="truncate text-emerald-400">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</div>
                        </div>
                      </div>
                    </div>
                    <div v-else class="h-28 rounded-xs border-2 border-dashed border-[#232A36] flex flex-col items-center justify-center text-slate-500 text-[10px] text-center p-2">
                      <span>Belum ada foto Before</span>
                    </div>
                  </div>

                  <!-- PROCESS Column (Disembunyikan jika AFTER_ONLY atau TWO_STAGES) -->
                  <div v-if="!isAfterOnly(item) && !isTwoStages(item)" class="p-3 bg-[#111927] rounded-xs border border-blue-600/40 space-y-2">
                    <div class="flex items-center justify-between">
                      <span class="px-2 py-0.5 rounded-xs text-[9px] font-black uppercase tracking-wider bg-blue-500 text-white">
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
                        class="h-32 rounded-xs overflow-hidden bg-slate-900 relative group cursor-pointer shadow-xs border border-[#232A36]"
                      >
                        <img
                          :src="getFileUrl(p.file_path)"
                          alt="Foto Process"
                          class="w-full h-full object-cover group-hover:scale-105 transition-all"
                        />
                        <button
                          type="button"
                          @click.stop="downloadSinglePhoto(p)"
                          class="absolute bottom-1.5 right-1.5 w-6 h-6 rounded-xs bg-[#0B0C0E]/90 hover:bg-[#FACC15] text-white hover:text-[#0B0C0E] shadow-md flex items-center justify-center transition-colors cursor-pointer border border-[#232A36] z-10"
                        >
                          <Download class="w-3 h-3" />
                        </button>
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/95 to-transparent p-1.5 text-[9px] text-white font-mono pointer-events-none">
                          <div class="truncate text-emerald-400">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</div>
                        </div>
                      </div>
                    </div>
                    <div v-else class="h-28 rounded-xs border-2 border-dashed border-[#232A36] flex flex-col items-center justify-center text-slate-500 text-[10px] text-center p-2">
                      <span>Belum ada foto Process</span>
                    </div>
                  </div>

                  <!-- AFTER Column (Hasil Akhir) -->
                  <div class="p-3 bg-[#10221C] rounded-xs border border-emerald-600/40 space-y-2">
                    <div class="flex items-center justify-between">
                      <span class="px-2 py-0.5 rounded-xs text-[9px] font-black uppercase tracking-wider bg-emerald-500 text-[#0B0C0E]">
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
                        class="h-32 sm:h-36 rounded-xs overflow-hidden bg-slate-900 relative group cursor-pointer shadow-xs border border-[#232A36]"
                      >
                        <img
                          :src="getFileUrl(p.file_path)"
                          alt="Foto After"
                          class="w-full h-full object-cover group-hover:scale-105 transition-all"
                        />
                        <button
                          type="button"
                          @click.stop="downloadSinglePhoto(p)"
                          class="absolute bottom-1.5 right-1.5 w-6 h-6 rounded-xs bg-[#0B0C0E]/90 hover:bg-[#FACC15] text-white hover:text-[#0B0C0E] shadow-md flex items-center justify-center transition-colors cursor-pointer border border-[#232A36] z-10"
                        >
                          <Download class="w-3 h-3" />
                        </button>
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/95 to-transparent p-1.5 text-[9px] text-white font-mono pointer-events-none">
                          <div class="truncate text-emerald-400">📍 {{ p.latitude ? `${Number(p.latitude).toFixed(4)}, ${Number(p.longitude).toFixed(4)}` : 'GPS Valid' }}</div>
                        </div>
                      </div>
                    </div>
                    <div v-else class="h-28 rounded-xs border-2 border-dashed border-[#232A36] flex flex-col items-center justify-center text-slate-500 text-[10px] text-center p-2">
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
                  class="cad-surface p-3.5 border border-[#232A36] rounded-xs"
                >
                  <div class="font-bold text-xs text-white mb-2.5 flex items-center justify-between font-mono">
                    <span class="text-[#FACC15]">TAHAP: {{ stage }}</span>
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
                      class="bg-[#1C2028] border border-[#232A36] rounded-xs overflow-hidden p-1 shadow-xs group relative cursor-pointer hover:border-[#FACC15] transition-all"
                    >
                      <img
                        :src="getFileUrl(p.file_path)"
                        :alt="p.file_name"
                        class="w-full h-24 object-cover rounded-xs group-hover:scale-105 transition-transform duration-300"
                        @error="$event.target.src = 'https://images.unsplash.com/photo-1541888946425-d0fbb18086f6?w=400&auto=format&fit=crop&q=60'"
                      />

                      <!-- Corner Download Button (Bottom-Right) -->
                      <button
                        type="button"
                        @click.stop="downloadSinglePhoto(p)"
                        class="absolute bottom-2 right-2 w-6 h-6 rounded-xs bg-[#0B0C0E]/90 hover:bg-[#FACC15] text-white hover:text-[#0B0C0E] shadow-md flex items-center justify-center transition-colors cursor-pointer border border-[#232A36] z-10"
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
                  <div v-else class="text-slate-500 text-xs italic">Belum ada foto pada tahap {{ stage }}.</div>
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
        class="fixed inset-0 z-50 bg-[#0B0C0E]/85 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-in"
      >
        <div class="bg-[#13171F] border border-[#FACC15] rounded-xs max-w-md w-full shadow-2xl p-6 space-y-4 text-xs text-slate-100 font-sans">
          <div class="flex items-center justify-between border-b border-[#232A36] pb-3">
            <h3 class="font-black text-sm text-white flex items-center gap-2 font-mono uppercase tracking-wider">
              <Plus class="w-4 h-4 text-[#FACC15]" />
              <span>Tambah Sub-Pekerjaan (Addendum)</span>
            </h3>
            <button @click="showAddendumModal = false" class="text-slate-400 hover:text-white cursor-pointer">
              <X class="w-5 h-5" />
            </button>
          </div>

          <div class="p-3 bg-[#1C2028] border border-amber-500/40 rounded-xs text-amber-200 text-[11px] space-y-1 font-mono">
            <p class="font-bold text-amber-300">Penambahan lingkup kerja pada SPK berjalan:</p>
            <p class="text-[10px] text-white">{{ workOrder?.spk_number }} - {{ workOrder?.title }}</p>
            <p class="text-slate-400 text-[10px]">Item baru akan otomatis muncul di tugas teknisi lapangan dan portal klien dengan status pekerjaan tambahan.</p>
          </div>

          <div class="space-y-3">
            <div>
              <label class="block font-bold text-slate-200 mb-1 text-xs">Nama Lingkup / Sub-Pekerjaan Tambahan <span class="text-rose-500">*</span>:</label>
              <input
                type="text"
                v-model="addendumForm.itemName"
                placeholder="Contoh: Pemasangan Stiker Sandblast Kaca Depan (12 m²)"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white focus:outline-none text-xs"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-200 mb-1 text-xs">Mode Dokumentasi Foto:</label>
              <select
                v-model="addendumForm.docMode"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white focus:outline-none text-xs"
              >
                <option value="BEFORE_PROCESS_AFTER" class="bg-[#0B0C0E] text-white">BEFORE ➔ PROCESS ➔ AFTER (Standar Lengkap)</option>
                <option value="BEFORE_AFTER" class="bg-[#0B0C0E] text-white">BEFORE ➔ AFTER (Tanpa Foto Proses)</option>
                <option value="AFTER_ONLY" class="bg-[#0B0C0E] text-white">AFTER ONLY (Hanya Foto Hasil Akhir)</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-200 mb-1 text-xs">Catatan / Spesifikasi Tambahan:</label>
              <textarea
                rows="2"
                v-model="addendumForm.notes"
                placeholder="Spesifikasi material, ukuran, atau instruksi khusus dari klien/pengawas..."
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white focus:outline-none text-xs"
              />
            </div>
          </div>

          <div class="pt-2 flex items-center justify-end gap-2 border-t border-[#232A36]">
            <button
              type="button"
              @click="showAddendumModal = false"
              class="px-4 py-2 bg-[#1C2028] hover:bg-[#232A36] text-slate-300 font-bold rounded-xs border border-[#232A36] cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="handleSubmitAddendum"
              :disabled="submittingAddendum"
              class="px-5 py-2 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] font-black rounded-xs shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-95 disabled:opacity-50 uppercase tracking-wider"
            >
              <Plus class="w-3.5 h-3.5" />
              <span>{{ submittingAddendum ? 'Menyimpan...' : 'Simpan & Terbitkan Item' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Superuser Direct Evidence & Document Upload Modal Dialog -->
    <Teleport to="body">
      <div
        v-if="showSuperuserUploadModal"
        class="fixed inset-0 z-50 bg-[#0B0C0E]/85 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-in font-sans"
      >
        <div class="bg-[#13171F] border border-[#FACC15] rounded-xs max-w-lg w-full shadow-2xl p-6 space-y-4 text-xs text-slate-100 max-h-[92vh] overflow-y-auto">
          <div class="flex items-center justify-between border-b border-[#232A36] pb-3">
            <h3 class="font-black text-sm text-white flex items-center gap-2 font-mono uppercase tracking-wider">
              <Upload class="w-4 h-4 text-[#FACC15]" />
              <span>Upload Evidensi / Berkas Fisik (Superuser)</span>
            </h3>
            <button @click="closeSuperuserUploadModal" class="text-slate-400 hover:text-white cursor-pointer">
              <X class="w-5 h-5" />
            </button>
          </div>

          <div class="p-3 bg-[#1C2028] border border-emerald-500/50 rounded-xs text-emerald-300 text-[11px] space-y-1">
            <p class="font-bold flex items-center gap-1.5 text-emerald-400">
              <CheckCircle2 class="w-3.5 h-3.5 text-emerald-400 shrink-0" />
              <span>Akses Khusus Superuser & Administrator</span>
            </p>
            <p class="text-slate-300 text-[10px]">
              Anda dapat mengunggah foto pengerjaan (BEFORE / PROCESS / AFTER), foto kendala (ISSUE), atau berkas fisik Berita Acara (BA Opname) secara manual ke SPK ini dengan verifikasi SHA-256 otomatis dan tanpa blokir jarak/geofence teknisi.
            </p>
          </div>

          <div v-if="suUploadError" class="p-3 bg-rose-950/80 border border-rose-500 rounded-xs text-rose-300 text-xs flex items-start gap-2">
            <AlertTriangle class="w-4 h-4 shrink-0 mt-0.5" />
            <span>{{ suUploadError }}</span>
          </div>

          <div class="space-y-3">
            <!-- Sub-Task Selector -->
            <div>
              <label class="block font-bold text-slate-200 mb-1 text-xs">Pilih Lingkup Sub-Pekerjaan:</label>
              <select
                v-model="suUploadForm.item_id"
                style="background-color: #13171F; color: #FFFFFF;"
                class="w-full px-3 py-2 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] text-white rounded-xs focus:outline-none text-xs font-medium"
              >
                <option :value="null" style="background-color: #13171F; color: #E2E8F0;" class="bg-[#13171F] text-slate-200">-- Umum (Seluruh SPK / Lembar BA Opname) --</option>
                <option
                  v-for="item in (workOrder?.items || [])"
                  :key="item.id"
                  :value="item.id"
                  style="background-color: #13171F; color: #FFFFFF;"
                  class="bg-[#13171F] text-white font-semibold"
                >
                  {{ item.item_name }} {{ item.doc_mode ? `(${item.doc_mode})` : '' }}
                </option>
              </select>
            </div>

            <!-- Stage Selector -->
            <div>
              <label class="block font-bold text-slate-200 mb-1 text-xs">Tahap Evidensi <span class="text-rose-500">*</span>:</label>
              <select
                v-model="suUploadForm.stage"
                style="background-color: #13171F; color: #FFFFFF;"
                class="w-full px-3 py-2 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] text-white rounded-xs focus:outline-none text-xs font-semibold"
              >
                <option value="BEFORE" style="background-color: #13171F; color: #FCD34D;" class="bg-[#13171F] text-amber-300 font-semibold">BEFORE (Kondisi Awal / 0%)</option>
                <option value="PROCESS" style="background-color: #13171F; color: #93C5FD;" class="bg-[#13171F] text-blue-300 font-semibold">PROCESS (Proses Pengerjaan / 50%)</option>
                <option value="AFTER" style="background-color: #13171F; color: #86EFAC;" class="bg-[#13171F] text-emerald-300 font-semibold">AFTER (Hasil Selesai / 100%)</option>
                <option value="BA_OPNAME" style="background-color: #13171F; color: #FACC15;" class="bg-[#13171F] text-[#FACC15] font-semibold">BA_OPNAME (Foto Berkas Lembar Fisik BA Opname Resmi)</option>
                <option value="ISSUE" style="background-color: #13171F; color: #FCA5A5;" class="bg-[#13171F] text-rose-300 font-semibold">ISSUE (Kendala / Masalah Lapangan)</option>
              </select>
            </div>

            <!-- File Upload Box -->
            <div>
              <label class="block font-bold text-slate-200 mb-1 text-xs">Pilih File Foto atau PDF <span class="text-rose-500">*</span>:</label>
              <div class="mt-1 flex justify-center px-4 pt-4 pb-4 border-2 border-dashed border-[#232A36] hover:border-[#FACC15] rounded-xs bg-[#0E1117] transition-colors">
                <div class="space-y-2 text-center w-full">
                  <div v-if="suUploadForm.previewUrl" class="relative inline-block max-w-[200px] mx-auto">
                    <img :src="suUploadForm.previewUrl" class="h-32 w-auto object-cover rounded-xs shadow-md border border-[#232A36] mx-auto" alt="Preview" />
                    <button
                      type="button"
                      @click="removeSuFile"
                      class="absolute -top-2 -right-2 p-1 bg-rose-600 text-white rounded-full shadow-md hover:bg-rose-700 cursor-pointer"
                    >
                      <X class="w-3 h-3" />
                    </button>
                    <p class="text-[10px] text-slate-300 mt-1 truncate max-w-[200px]">{{ suUploadForm.file?.name }}</p>
                  </div>
                  <div v-else-if="suUploadForm.file" class="flex items-center justify-center gap-2 p-3 bg-[#1C2028] rounded-xs border border-[#232A36]">
                    <FileText class="w-6 h-6 text-[#FACC15] shrink-0" />
                    <div class="text-left text-xs truncate max-w-[240px]">
                      <p class="font-bold text-white truncate">{{ suUploadForm.file.name }}</p>
                      <p class="text-slate-400 text-[10px]">{{ (suUploadForm.file.size / 1024).toFixed(1) }} KB</p>
                    </div>
                    <button
                      type="button"
                      @click="removeSuFile"
                      class="p-1 text-slate-400 hover:text-rose-400 ml-auto cursor-pointer"
                    >
                      <X class="w-4 h-4" />
                    </button>
                  </div>
                  <div v-else>
                    <Upload class="mx-auto h-8 w-8 text-slate-400" />
                    <div class="text-xs text-slate-300 mt-1">
                      <label
                        for="su-evidence-file"
                        class="relative cursor-pointer bg-[#1C2028] hover:bg-[#232A36] px-3.5 py-1.5 rounded-xs font-bold text-[#FACC15] border border-[#232A36] hover:border-[#FACC15] shadow-2xs inline-block transition-colors"
                      >
                        <span>Pilih Foto / Dokumen</span>
                        <input
                          id="su-evidence-file"
                          name="su-evidence-file"
                          type="file"
                          accept="image/jpeg,image/png,image/webp,application/pdf"
                          class="sr-only"
                          @change="handleSuFileChange"
                        />
                      </label>
                      <p class="text-[10px] text-slate-400 mt-1.5">Mendukung format JPG, PNG, WEBP, atau PDF (Otomatis kompresi cepat)</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div>
              <label class="block font-bold text-slate-200 mb-1 text-xs">Catatan / Keterangan Dokumen:</label>
              <textarea
                rows="2"
                v-model="suUploadForm.notes"
                placeholder="Catatan tambahan (misal: 'Foto BA Opname cap basah toko dikirim via WA, diupload oleh Superuser')..."
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white focus:outline-none text-xs"
              />
            </div>

            <!-- Target Location Info -->
            <div class="p-2.5 bg-[#1C2028] border border-[#232A36] rounded-xs text-slate-300 text-[10px] flex items-center gap-2">
              <MapPin class="w-3.5 h-3.5 text-[#FACC15] shrink-0" />
              <span class="truncate">Lokasi Target SPK: <strong class="text-white">{{ workOrder?.location_name || 'Lokasi Terdaftar' }}</strong> (Lat: {{ workOrder?.target_lat || '-' }}, Lng: {{ workOrder?.target_lng || '-' }})</span>
            </div>
          </div>

          <div class="pt-2 flex items-center justify-end gap-2 border-t border-[#232A36]">
            <button
              type="button"
              @click="closeSuperuserUploadModal"
              :disabled="suUploading"
              class="px-4 py-2 bg-[#1C2028] hover:bg-[#232A36] text-slate-300 font-bold rounded-xs border border-[#232A36] cursor-pointer disabled:opacity-50"
            >
              Batal
            </button>
            <button
              type="button"
              @click="submitSuperuserUpload"
              :disabled="suUploading || !suUploadForm.file"
              class="px-5 py-2 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] font-black rounded-xs shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-95 disabled:opacity-50 uppercase tracking-wider"
            >
              <Upload class="w-3.5 h-3.5" />
              <span>{{ suUploading ? (suUploadStatusText || 'Mengunggah...') : 'Upload Sekarang' }}</span>
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
import { fastCompressImage } from '../../utils/imageCompressor';
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
  Archive,
  Upload,
  FileText
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

// Superuser Direct Evidence & BA Opname Upload State & Methods
const showSuperuserUploadModal = ref(false);
const suUploading = ref(false);
const suUploadStatusText = ref('');
const suUploadError = ref(null);
const suUploadForm = ref({
  item_id: null,
  stage: 'AFTER',
  file: null,
  previewUrl: null,
  notes: ''
});

function openSuperuserUploadModal() {
  suUploadForm.value = {
    item_id: null,
    stage: 'AFTER',
    file: null,
    previewUrl: null,
    notes: ''
  };
  suUploadError.value = null;
  suUploading.value = false;
  suUploadStatusText.value = '';
  showSuperuserUploadModal.value = true;
}

function closeSuperuserUploadModal() {
  if (suUploadForm.value.previewUrl) {
    URL.revokeObjectURL(suUploadForm.value.previewUrl);
  }
  showSuperuserUploadModal.value = false;
  suUploadError.value = null;
}

function removeSuFile() {
  if (suUploadForm.value.previewUrl) {
    URL.revokeObjectURL(suUploadForm.value.previewUrl);
  }
  suUploadForm.value.file = null;
  suUploadForm.value.previewUrl = null;
}

function handleSuFileChange(e) {
  const file = e.target?.files?.[0];
  if (!file) return;

  if (suUploadForm.value.previewUrl) {
    URL.revokeObjectURL(suUploadForm.value.previewUrl);
  }

  suUploadForm.value.file = file;
  if (file.type && file.type.startsWith('image/')) {
    suUploadForm.value.previewUrl = URL.createObjectURL(file);
  } else {
    suUploadForm.value.previewUrl = null;
  }
  suUploadError.value = null;
}

async function submitSuperuserUpload() {
  if (!suUploadForm.value.file) {
    suUploadError.value = 'Silakan pilih file foto atau dokumen PDF terlebih dahulu!';
    return;
  }

  suUploading.value = true;
  suUploadError.value = null;
  suUploadStatusText.value = 'Memproses berkas...';

  try {
    let fileToUpload = suUploadForm.value.file;

    // Fast image compression if file is an image
    if (fileToUpload.type && fileToUpload.type.startsWith('image/')) {
      suUploadStatusText.value = 'Mengompresi gambar...';
      try {
        fileToUpload = await fastCompressImage(fileToUpload, {
          maxWidth: 2048,
          maxHeight: 2048,
          quality: 0.82
        });
      } catch (cErr) {
        console.warn('Image compression fallback:', cErr);
      }
    }

    suUploadStatusText.value = 'Mengunggah ke server...';

    const formData = new FormData();
    formData.append('work_order_id', props.workOrderId);
    if (suUploadForm.value.item_id) {
      formData.append('item_id', suUploadForm.value.item_id);
    }
    formData.append('stage', suUploadForm.value.stage);
    formData.append('photo', fileToUpload);
    formData.append('file', fileToUpload);

    const lat = workOrder.value?.target_lat || -6.2088;
    const lng = workOrder.value?.target_lng || 106.8456;
    formData.append('latitude', lat);
    formData.append('longitude', lng);
    formData.append('accuracy', 5);

    const customNotes = suUploadForm.value.notes?.trim();
    const stagePrefix = suUploadForm.value.stage === 'BA_OPNAME' ? '[BA_OPNAME]' : `[${suUploadForm.value.stage}]`;
    const defaultNote = `${stagePrefix} Diunggah manual oleh Superuser via Detail SPK`;
    formData.append('notes', customNotes ? `${stagePrefix} ${customNotes}` : defaultNote);

    let res;
    try {
      res = await api.uploadEvidence(formData);
    } catch (uploadErr) {
      const errMsg = String(uploadErr.message || uploadErr || '');
      // Fallback if backend rejects BA_OPNAME
      if (
        (suUploadForm.value.stage === 'BA_OPNAME' || suUploadForm.value.stage === 'BA') &&
        (errMsg.includes('stage is invalid') || errMsg.includes('stage tidak valid') || errMsg.includes('selected stage'))
      ) {
        console.warn('[WorkOrderDetailModal] Backend rejected BA_OPNAME stage. Retrying with stage: AFTER...');
        const fallbackData = new FormData();
        fallbackData.append('work_order_id', props.workOrderId);
        if (suUploadForm.value.item_id) fallbackData.append('item_id', suUploadForm.value.item_id);
        fallbackData.append('stage', 'AFTER');
        fallbackData.append('photo', fileToUpload);
        fallbackData.append('file', fileToUpload);
        fallbackData.append('latitude', lat);
        fallbackData.append('longitude', lng);
        fallbackData.append('accuracy', 5);
        fallbackData.append('notes', `[BA_OPNAME] Berkas Fisik BA Opname: ${customNotes || fileToUpload.name}`);

        res = await api.uploadEvidence(fallbackData);
      } else {
        throw uploadErr;
      }
    }

    if (res.success || res.data) {
      alert(`Berkas evidensi tahap ${suUploadForm.value.stage} berhasil diunggah!`);
      closeSuperuserUploadModal();
      await loadDetail();
      emit('refresh-list');
    }
  } catch (err) {
    console.error('Failed to upload superuser evidence:', err);
    suUploadError.value = err.message || 'Gagal mengunggah berkas ke server.';
  } finally {
    suUploading.value = false;
    suUploadStatusText.value = '';
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
