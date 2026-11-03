<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 bg-[#0B0C0E]/80 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto font-sans">
    <div class="bg-[#13171F] border border-[#232A36] rounded-xs max-w-4xl w-full shadow-2xl overflow-hidden my-4 flex flex-col max-h-[92vh] font-mono text-white animate-fade-in">
      <!-- Modal Header (Industrial CAD Standard) -->
      <div class="px-6 py-4 border-b border-[#232A36] flex items-center justify-between bg-[#0E1117] shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xs bg-[#1C2028] border border-[#FACC15] text-[#FACC15] flex items-center justify-center font-mono">
            <ShieldCheck class="w-4 h-4 text-[#FACC15]" />
          </div>
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-mono font-bold text-[10px] bg-[#1C2028] text-[#FACC15] border border-[#FACC15]/40 px-2 py-0.5 rounded-xs tracking-wider uppercase">
                SUPERVISOR ACCESS // EDIT MODE
              </span>
              <span class="text-xs text-slate-300 font-mono font-bold bg-[#0B0C0E] border border-[#232A36] px-2 py-0.5 rounded-xs">
                SPK: {{ formData.spk_number }}
              </span>
            </div>
            <h3 class="font-bold text-sm sm:text-base text-white uppercase tracking-wider mt-0.5 font-mono">
              EDIT DATA & PENGATURAN SPK
            </h3>
          </div>
        </div>

        <button
          type="button"
          @click="$emit('close')"
          class="p-1.5 text-slate-400 hover:text-white bg-[#1C2028] hover:bg-[#232A36] border border-[#232A36] rounded-xs transition-colors cursor-pointer"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 overflow-y-auto space-y-5 text-xs text-slate-300 custom-scrollbar bg-[#13171F] flex-1">
        <!-- Loading State -->
        <div v-if="loadingData" class="py-16 text-center text-slate-400 font-mono">
          <Loader2 class="w-6 h-6 animate-spin mx-auto mb-2 text-[#FACC15]" />
          <span>MEMUAT DATA KONFIGURASI SPK...</span>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Supervisor Alert Banner -->
          <div class="p-3 bg-[#0E1117] border border-[#232A36] rounded-xs flex items-center gap-2.5 text-xs text-slate-300 font-mono">
            <Lock class="w-4 h-4 text-[#FACC15] shrink-0" />
            <span class="text-[11px]">
              Otorisasi Supervisor Aktif: Anda berwenang memperbarui data SPK, mengubah penugasan PIC & Tim, serta mengatur parameter verifikasi lapangan.
            </span>
          </div>

          <!-- Error Alert -->
          <div v-if="error" class="p-3 bg-rose-950/40 border border-rose-500/50 text-rose-300 text-xs rounded-xs flex items-center justify-between gap-2 font-mono">
            <div class="flex items-center gap-2">
              <AlertCircle class="w-4 h-4 shrink-0 text-rose-400" />
              <span>{{ error }}</span>
            </div>
            <button type="button" @click="error = ''" class="text-rose-400 hover:text-rose-200 font-bold text-xs cursor-pointer">
              ✕
            </button>
          </div>

          <!-- Step Tabs (Industrial CAD Segmented) -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#232A36] pb-3">
            <div class="flex items-center gap-1 p-1 bg-[#0E1117] border border-[#232A36] rounded-xs font-mono text-xs">
              <button
                type="button"
                @click="activeTab = 'info'"
                :class="[
                  'px-3 py-1.5 rounded-xs transition-colors cursor-pointer uppercase tracking-wider font-mono',
                  activeTab === 'info'
                    ? 'bg-[#1C2028] text-[#FACC15] border-l-[3px] border-[#FACC15] font-bold'
                    : 'text-slate-400 hover:text-slate-200 hover:bg-[#1A202C]/60 border-l-[3px] border-transparent'
                ]"
              >
                <span>1. Data Pokok & Tim Lapangan</span>
              </button>
              <button
                type="button"
                @click="activeTab = 'items'"
                :class="[
                  'px-3 py-1.5 rounded-xs transition-colors cursor-pointer flex items-center gap-2 uppercase tracking-wider font-mono',
                  activeTab === 'items'
                    ? 'bg-[#1C2028] text-[#FACC15] border-l-[3px] border-[#FACC15] font-bold'
                    : 'text-slate-400 hover:text-slate-200 hover:bg-[#1A202C]/60 border-l-[3px] border-transparent'
                ]"
              >
                <span>2. Sub-Pekerjaan & Parameter Lapangan</span>
                <span class="px-1.5 py-0.2 rounded-xs bg-[#13171F] text-[#FACC15] border border-[#232A36] text-[10px] font-bold font-mono">
                  {{ subItems.length }}
                </span>
              </button>
            </div>

            <div class="hidden sm:flex items-center gap-2 px-2.5 py-1 rounded-xs bg-[#0E1117] border border-[#232A36] text-[10px] font-mono text-slate-400">
              <span class="w-1.5 h-1.5 rounded-full bg-[#FACC15] animate-pulse"></span>
              <span>MODE: SPV OVERRIDE</span>
            </div>
          </div>

          <!-- TAB 1: DATA POKOK & TIM LAPANGAN -->
          <div v-show="activeTab === 'info'" class="space-y-4 font-mono">
            <!-- Section 1: Identitas & No SPK -->
            <div class="p-4 bg-[#0E1117] rounded-xs border border-[#232A36] space-y-3">
              <h4 class="font-bold text-xs text-white uppercase tracking-wider flex items-center gap-1.5 border-b border-[#232A36] pb-2">
                <FileText class="w-3.5 h-3.5 text-[#FACC15]" />
                <span>// IDENTITAS & JUDUL PEKERJAAN</span>
              </h4>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                  <label class="block font-bold text-slate-400 mb-1 text-[11px] uppercase tracking-wider">
                    Nomor SPK <span class="text-[#FACC15]">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    v-model="formData.spk_number"
                    class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs font-mono font-bold text-xs text-[#FACC15] focus:outline-none"
                  />
                </div>
                <div class="sm:col-span-2">
                  <label class="block font-bold text-slate-400 mb-1 text-[11px] uppercase tracking-wider">
                    Judul / Nama Proyek SPK <span class="text-[#FACC15]">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    v-model="formData.title"
                    class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs font-bold text-xs text-white focus:outline-none"
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                  <label class="block font-bold text-slate-400 mb-1 text-[11px] uppercase tracking-wider">
                    Perusahaan Client <span class="text-[#FACC15]">*</span>
                  </label>
                  <select
                    required
                    v-model="formData.vendor_id"
                    class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs font-bold text-xs text-white focus:outline-none"
                  >
                    <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }} ({{ v.code }})</option>
                  </select>
                </div>

                <div>
                  <label class="block font-bold text-slate-400 mb-1 text-[11px] uppercase tracking-wider">
                    Area Operasional <span class="text-[#FACC15]">*</span>
                  </label>
                  <select
                    required
                    v-model="formData.area_id"
                    class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs font-bold text-xs text-white focus:outline-none"
                  >
                    <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.name }} ({{ a.city || a.province || 'Wilayah' }})</option>
                  </select>
                </div>

                <div>
                  <label class="block font-bold text-slate-400 mb-1 text-[11px] uppercase tracking-wider">
                    Kategori Utama <span class="text-[#FACC15]">*</span>
                  </label>
                  <select
                    required
                    v-model="formData.job_type_id"
                    class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs font-bold text-xs text-white focus:outline-none"
                  >
                    <option v-for="j in jobTypes" :key="j.id" :value="j.id">{{ j.name }}</option>
                  </select>
                </div>
              </div>

              <div>
                <label class="block font-bold text-slate-400 mb-1 text-[11px] uppercase tracking-wider">
                  Nama Cabang & Alamat Lengkap <span class="text-[#FACC15]">*</span>
                </label>
                <input
                  type="text"
                  required
                  v-model="formData.location_name"
                  class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs text-white focus:outline-none"
                />
              </div>
            </div>

            <!-- Section 2: Penugasan Tim & Jadwal -->
            <div class="p-4 bg-[#0E1117] rounded-xs border border-[#232A36] space-y-3">
              <h4 class="font-bold text-xs text-white uppercase tracking-wider flex items-center gap-1.5 border-b border-[#232A36] pb-2">
                <Users class="w-3.5 h-3.5 text-[#FACC15]" />
                <span>// PENUGASAN TIM LAPANGAN & JADWAL TARGET</span>
              </h4>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                  <label class="block font-bold text-slate-400 mb-1 text-[11px] uppercase tracking-wider">
                    Ketua Tim / PIC Lapangan <span class="text-[#FACC15]">*</span>
                  </label>
                  <select
                    v-model="formData.pic_user_id"
                    class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs font-bold text-xs text-white focus:outline-none"
                  >
                    <option value="">-- Belum Ditugaskan --</option>
                    <option v-for="u in fieldUsers" :key="u.id" :value="u.id">
                      {{ u.name }} ({{ u.phone || 'No HP -' }})
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block font-bold text-slate-400 mb-1 text-[11px] uppercase tracking-wider">
                    Tanggal Mulai <span class="text-[#FACC15]">*</span>
                  </label>
                  <input
                    type="date"
                    required
                    v-model="formData.start_date"
                    class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs font-mono text-white focus:outline-none"
                  />
                </div>

                <div>
                  <label class="block font-bold text-slate-400 mb-1 text-[11px] uppercase tracking-wider">
                    Deadline Target <span class="text-[#FACC15]">*</span>
                  </label>
                  <input
                    type="date"
                    required
                    v-model="formData.deadline"
                    class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs font-mono text-[#FACC15] font-bold focus:outline-none"
                  />
                </div>
              </div>

              <!-- Anggota Tim Tambahan -->
              <div>
                <label class="block font-bold text-slate-400 mb-1 text-[11px] uppercase tracking-wider">
                  Anggota Tim Tambahan (Penugasan Multi-Teknisi)
                </label>
                <div class="max-h-36 overflow-y-auto p-2.5 bg-[#0B0C0E] border border-[#232A36] rounded-xs space-y-1 custom-scrollbar">
                  <label
                    v-for="u in fieldUsers.filter(u => u.id !== formData.pic_user_id)"
                    :key="u.id"
                    class="flex items-center gap-2 p-1.5 hover:bg-[#181D26] rounded-xs cursor-pointer text-xs"
                  >
                    <input
                      type="checkbox"
                      :value="u.id"
                      v-model="selectedMemberIds"
                      class="rounded-xs border-[#232A36] accent-[#FACC15] cursor-pointer"
                    />
                    <span class="text-slate-300 font-mono">{{ u.name }}</span>
                    <span class="text-slate-500 text-[10px]">({{ u.phone || 'No HP -' }})</span>
                  </label>
                  <div v-if="fieldUsers.filter(u => u.id !== formData.pic_user_id).length === 0" class="text-slate-500 text-[11px] p-1 italic font-mono">
                    Pilih PIC terlebih dahulu untuk menentukan anggota lainnya.
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 2: SUB-PEKERJAAN & PARAMETER LAPANGAN -->
          <div v-show="activeTab === 'items'" class="space-y-4 font-mono">
            <!-- Section 3: Sub Pekerjaan & Rincian Checklist -->
            <div class="p-4 bg-[#0E1117] rounded-xs border border-[#232A36] space-y-3">
              <div class="flex items-center justify-between border-b border-[#232A36] pb-2">
                <div>
                  <h4 class="font-bold text-xs text-white uppercase tracking-wider flex items-center gap-1.5">
                    <CheckSquare class="w-3.5 h-3.5 text-[#FACC15]" />
                    <span>// RINCIAN SUB-PEKERJAAN ({{ subItems.length }} ITEM CHECKLIST)</span>
                  </h4>
                  <p class="text-[10px] text-slate-400 mt-0.5">Item lingkup tugas yang harus didokumentasikan teknisi di lapangan.</p>
                </div>
                <button
                  type="button"
                  @click="addSubItem"
                  class="px-2.5 py-1 bg-[#1C2028] hover:bg-[#232A36] text-[#FACC15] border border-[#232A36] hover:border-[#FACC15]/40 font-bold rounded-xs text-[10px] flex items-center gap-1 transition-colors cursor-pointer uppercase tracking-wider"
                >
                  <Plus class="w-3 h-3 stroke-[2.5]" />
                  <span>+ Tambah Sub-Item</span>
                </button>
              </div>

              <div class="space-y-2 max-h-52 overflow-y-auto pr-1 custom-scrollbar">
                <div
                  v-for="(item, idx) in subItems"
                  :key="idx"
                  class="flex items-center gap-2 p-2.5 bg-[#13171F] border border-[#232A36] rounded-xs"
                >
                  <span class="min-w-[24px] h-6 rounded-xs bg-[#0E1117] text-[#FACC15] border border-[#232A36] font-bold text-[10px] flex items-center justify-center shrink-0">
                    #{{ idx + 1 }}
                  </span>

                  <input
                    type="text"
                    required
                    placeholder="Nama item pekerjaan (misal: Pasang Rangka Hollo 4x4)"
                    v-model="item.item_name"
                    class="flex-1 px-3 py-1.5 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs font-semibold text-white focus:outline-none"
                  />

                  <select
                    v-model="item.doc_mode"
                    class="px-2.5 py-1.5 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-[10px] font-bold text-slate-200 focus:outline-none font-mono"
                  >
                    <option value="BEFORE_PROCESS_AFTER">3 Tahap (Before/Process/After)</option>
                    <option value="AFTER_ONLY">1 Tahap (After Saja)</option>
                  </select>

                  <button
                    type="button"
                    @click="removeSubItem(idx)"
                    :disabled="subItems.length <= 1"
                    class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-950/40 rounded-xs disabled:opacity-30 cursor-pointer transition-colors"
                    title="Hapus Item"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Section 4: Pengaturan Forensik, GPS, & Status Override -->
            <div class="p-4 bg-[#0E1117] rounded-xs border border-[#232A36] space-y-3">
              <h4 class="font-bold text-xs text-white uppercase tracking-wider flex items-center gap-1.5 border-b border-[#232A36] pb-2">
                <Settings class="w-3.5 h-3.5 text-[#FACC15]" />
                <span>// TELEMETRI GPS & PARAMETER KONTROL LAPANGAN</span>
              </h4>

              <!-- GPS Target Location -->
              <div class="p-3 bg-[#13171F] border border-[#232A36] rounded-xs space-y-2">
                <div class="flex items-center justify-between">
                  <label class="block font-bold text-slate-200 text-xs flex items-center gap-1 uppercase tracking-wider">
                    <MapPin class="w-3.5 h-3.5 text-sky-400" />
                    <span>Titik Target Koordinat GPS Lokasi</span>
                  </label>
                  <button
                    type="button"
                    @click="detectCurrentLocation"
                    :disabled="detectingGps"
                    class="px-2.5 py-1 bg-[#1C2028] hover:bg-[#232A36] text-[#FACC15] border border-[#232A36] hover:border-[#FACC15]/40 rounded-xs text-[10px] font-bold flex items-center gap-1 cursor-pointer transition-colors uppercase tracking-wider"
                  >
                    <Navigation class="w-3 h-3 text-[#FACC15]" />
                    <span>{{ detectingGps ? 'Mendeteksi...' : '📍 Ambil GPS Saat Ini' }}</span>
                  </button>
                </div>

                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <span class="block text-[10px] text-slate-400 font-bold mb-0.5 uppercase tracking-wider">Latitude:</span>
                    <input
                      type="number"
                      step="any"
                      v-model="formData.target_lat"
                      placeholder="-3.79284"
                      class="w-full px-2.5 py-1.5 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs font-mono text-white focus:outline-none"
                    />
                  </div>
                  <div>
                    <span class="block text-[10px] text-slate-400 font-bold mb-0.5 uppercase tracking-wider">Longitude:</span>
                    <input
                      type="number"
                      step="any"
                      v-model="formData.target_lng"
                      placeholder="102.2607"
                      class="w-full px-2.5 py-1.5 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs font-mono text-white focus:outline-none"
                    />
                  </div>
                </div>
              </div>

              <!-- Check-In, Timestamp, BA Doc, & Status Override Grid -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <!-- 1. Checkin Requirement -->
                <div class="p-3 bg-[#13171F] border border-[#232A36] rounded-xs space-y-1">
                  <div class="flex items-start gap-2.5">
                    <input
                      type="checkbox"
                      id="edit_require_checkin"
                      v-model="formData.require_checkin"
                      class="w-4 h-4 mt-0.5 rounded-xs accent-[#FACC15] cursor-pointer"
                    />
                    <div>
                      <label for="edit_require_checkin" class="font-bold text-white cursor-pointer text-xs uppercase tracking-wider">
                        Wajib 1x Check-In GPS
                      </label>
                      <p class="text-[10px] text-slate-400 mt-0.5">
                        Teknisi harus check-in koordinat lokasi cabang saat tiba.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- 2. Status Override -->
                <div class="p-3 bg-[#13171F] border border-[#232A36] rounded-xs space-y-1">
                  <label class="block font-bold text-white text-xs uppercase tracking-wider">
                    Override Status SPK (Supervisor)
                  </label>
                  <select
                    v-model="formData.status"
                    class="w-full px-2.5 py-1.5 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs font-bold text-[#FACC15] focus:outline-none font-mono"
                  >
                    <option value="DRAFT">DRAFT — Draft Baru</option>
                    <option value="ASSIGNED">ASSIGNED — Tim Ditugaskan</option>
                    <option value="IN_PROGRESS">IN_PROGRESS — Sedang Dikerjakan</option>
                    <option value="SUBMITTED">SUBMITTED — Siap Direview</option>
                    <option value="REVISION">REVISION — Permintaan Revisi</option>
                    <option value="APPROVED">APPROVED — Disetujui</option>
                    <option value="BA_OPNAME">BA_OPNAME — BA Opname Terbit</option>
                    <option value="COMPLETED">COMPLETED — Selesai 100%</option>
                    <option value="CANCELLED">CANCELLED — Dibatalkan</option>
                  </select>
                </div>

                <!-- 3. Timestamp Camera Option -->
                <div class="p-3 bg-[#13171F] border border-[#232A36] rounded-xs space-y-1">
                  <div class="flex items-start gap-2.5">
                    <input
                      type="checkbox"
                      id="edit_use_timestamp"
                      v-model="formData.use_timestamp"
                      class="w-4 h-4 mt-0.5 rounded-xs accent-[#FACC15] cursor-pointer"
                    />
                    <div>
                      <label for="edit_use_timestamp" class="font-bold text-white cursor-pointer text-xs flex items-center gap-1.5 uppercase tracking-wider">
                        <span>📷 Wajib Kamera Timestamp GPS</span>
                      </label>
                      <p class="text-[10px] text-slate-400 mt-0.5">
                        <span v-if="formData.use_timestamp" class="text-[#FACC15] font-bold">
                          ✓ Aktif (Stempel satelit & map pada foto).
                        </span>
                        <span v-else class="text-amber-400 font-bold">
                          ⚠️ Nonaktif (Mode upload bebas tanpa stempel).
                        </span>
                      </p>
                    </div>
                  </div>
                </div>

                <!-- 4. BA Opname Physical Document Requirement -->
                <div class="p-3 bg-[#13171F] border border-[#232A36] rounded-xs space-y-1">
                  <div class="flex items-start gap-2.5">
                    <input
                      type="checkbox"
                      id="edit_require_ba_doc"
                      v-model="formData.require_ba_doc"
                      class="w-4 h-4 mt-0.5 rounded-xs accent-[#FACC15] cursor-pointer"
                    />
                    <div>
                      <label for="edit_require_ba_doc" class="font-bold text-white cursor-pointer text-xs flex items-center gap-1.5 uppercase tracking-wider">
                        <span>📄 Wajib Upload Berkas BA Opname</span>
                      </label>
                      <p class="text-[10px] text-slate-400 mt-0.5">
                        <span v-if="formData.require_ba_doc" class="text-[#FACC15] font-bold">
                          ✓ Wajib (Harus upload bukti fisik BA bertandatangan).
                        </span>
                        <span v-else class="text-slate-400">
                          Bebas / Opsional (Dapat diajukan tanpa berkas BA).
                        </span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Notes -->
              <div>
                <label class="block font-bold text-slate-400 mb-1 text-[11px] uppercase tracking-wider">
                  Catatan / Instruksi Khusus Supervisor
                </label>
                <textarea
                  rows="2"
                  v-model="formData.notes"
                  placeholder="Catatan pengerjaan, instruksi K3, atau persyaratan khusus..."
                  class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs text-white focus:outline-none font-mono"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Modal Footer (Industrial CAD Actions) -->
          <div class="pt-4 border-t border-[#232A36] flex items-center justify-between gap-3 font-mono">
            <div class="flex items-center gap-2">
              <button
                v-if="activeTab === 'items'"
                type="button"
                @click="activeTab = 'info'"
                class="px-3.5 py-2 bg-[#1C2028] hover:bg-[#232A36] text-slate-300 hover:text-white font-bold rounded-xs border border-[#232A36] transition-colors cursor-pointer text-xs uppercase tracking-wider"
              >
                ← Data Pokok
              </button>
              <button
                v-if="activeTab === 'info'"
                type="button"
                @click="activeTab = 'items'"
                class="px-3.5 py-2 bg-[#1C2028] hover:bg-[#232A36] text-[#FACC15] border border-[#232A36] hover:border-[#FACC15]/40 font-bold rounded-xs transition-colors cursor-pointer text-xs uppercase tracking-wider flex items-center gap-1"
              >
                <span>Sub-Pekerjaan & Parameter →</span>
              </button>
            </div>

            <div class="flex items-center gap-2.5">
              <button
                type="button"
                @click="$emit('close')"
                class="px-4 py-2 bg-[#1C2028] hover:bg-[#232A36] text-slate-300 hover:text-white font-bold rounded-xs border border-[#232A36] transition-colors cursor-pointer text-xs uppercase tracking-wider"
              >
                Batal
              </button>

              <button
                type="submit"
                :disabled="submitting"
                class="px-6 py-2 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] font-bold rounded-xs flex items-center gap-2 border border-[#FACC15] shadow-xs active:scale-95 transition-all disabled:opacity-50 cursor-pointer text-xs uppercase tracking-wider"
              >
                <Loader2 v-if="submitting" class="w-4 h-4 animate-spin text-[#0B0C0E]" />
                <Save v-else class="w-4 h-4 stroke-[2.5]" />
                <span>{{ submitting ? 'Menyimpan...' : 'Simpan Perubahan SPK' }}</span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { api } from '../../services/api';
import {
  ShieldCheck,
  X,
  FileText,
  Users,
  CheckSquare,
  Settings,
  MapPin,
  Navigation,
  Plus,
  Trash2,
  Lock,
  Save,
  Loader2,
  AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  workOrderId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits(['close', 'updated']);

const activeTab = ref('info'); // 'info' | 'items'
const loadingData = ref(false);
const submitting = ref(false);
const error = ref('');
const detectingGps = ref(false);

const vendors = ref([]);
const areas = ref([]);
const jobTypes = ref([]);
const fieldUsers = ref([]);
const selectedMemberIds = ref([]);
const subItems = ref([]);

const formData = reactive({
  id: null,
  spk_number: '',
  title: '',
  vendor_id: '',
  area_id: '',
  job_type_id: '',
  location_name: '',
  target_lat: null,
  target_lng: null,
  pic_user_id: '',
  start_date: '',
  deadline: '',
  require_checkin: true,
  use_timestamp: true,
  require_ba_doc: false,
  doc_mode: 'BEFORE_PROCESS_AFTER',
  status: 'ASSIGNED',
  notes: ''
});

async function loadInitialData() {
  if (!props.workOrderId) return;
  loadingData.value = true;
  error.value = '';

  try {
    const [vRes, aRes, jRes, uRes, woRes] = await Promise.all([
      api.getVendors(),
      api.getAreas(),
      api.getJobTypes(),
      api.getUsers({ role: 'FIELD_TEAM' }),
      api.getWorkOrderById(props.workOrderId)
    ]);

    vendors.value = vRes.data || [];
    areas.value = aRes.data || [];
    jobTypes.value = jRes.data || [];
    fieldUsers.value = uRes.data || [];

    const wo = woRes.data;
    if (wo) {
      formData.id = wo.id;
      formData.spk_number = wo.spk_number || '';
      formData.title = wo.title || '';
      formData.vendor_id = wo.vendor_id || (vendors.value[0]?.id || '');
      formData.area_id = wo.area_id || (areas.value[0]?.id || '');
      formData.job_type_id = wo.job_type_id || (jobTypes.value[0]?.id || '');
      formData.location_name = wo.location_name || '';
      formData.target_lat = wo.target_lat ? parseFloat(wo.target_lat) : null;
      formData.target_lng = wo.target_lng ? parseFloat(wo.target_lng) : null;
      formData.pic_user_id = wo.pic_user_id || '';
      formData.start_date = wo.start_date || '';
      formData.deadline = wo.deadline || '';
      formData.require_checkin = typeof wo.require_checkin === 'boolean' ? wo.require_checkin : true;
      formData.use_timestamp = typeof wo.use_timestamp === 'boolean' ? wo.use_timestamp : true;
      formData.require_ba_doc = typeof wo.require_ba_doc === 'boolean' ? wo.require_ba_doc : false;
      formData.doc_mode = wo.doc_mode || 'BEFORE_PROCESS_AFTER';
      formData.status = wo.status || 'ASSIGNED';
      formData.notes = wo.notes || '';

      // Selected Members
      const members = (wo.assignments || []).filter(a => a.pivot?.role_in_team === 'MEMBER').map(a => a.id);
      selectedMemberIds.value = members;

      // Sub Items
      if (wo.items && wo.items.length > 0) {
        subItems.value = wo.items.map(i => ({
          id: i.id,
          item_name: i.item_name,
          doc_mode: i.doc_mode || 'BEFORE_PROCESS_AFTER',
          notes: i.notes
        }));
      } else {
        subItems.value = [
          { item_name: 'Pekerjaan Utama', doc_mode: 'BEFORE_PROCESS_AFTER' }
        ];
      }
    }
  } catch (err) {
    console.error('Failed to load SPK detail for edit:', err);
    error.value = 'Gagal memuat data SPK: ' + err.message;
  } finally {
    loadingData.value = false;
  }
}

watch(
  () => props.isOpen,
  (val) => {
    if (val && props.workOrderId) {
      loadInitialData();
    }
  },
  { immediate: true }
);

function addSubItem() {
  subItems.value.push({
    item_name: '',
    doc_mode: 'BEFORE_PROCESS_AFTER'
  });
}

function removeSubItem(idx) {
  if (subItems.value.length > 1) {
    subItems.value.splice(idx, 1);
  }
}

function detectCurrentLocation() {
  if (!navigator.geolocation) {
    alert('Browser Anda tidak mendukung deteksi lokasi otomatis.');
    return;
  }
  detectingGps.value = true;
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      formData.target_lat = parseFloat(pos.coords.latitude.toFixed(6));
      formData.target_lng = parseFloat(pos.coords.longitude.toFixed(6));
      detectingGps.value = false;
      alert(`Lokasi GPS terdeteksi: ${formData.target_lat}, ${formData.target_lng}`);
    },
    (err) => {
      detectingGps.value = false;
      alert('Gagal mendeteksi lokasi: ' + err.message);
    },
    { enableHighAccuracy: true, timeout: 8000 }
  );
}

async function handleSubmit() {
  if (!formData.title || !formData.location_name) {
    error.value = 'Judul proyek dan alamat lokasi cabang wajib diisi.';
    return;
  }

  const validItems = subItems.value
    .filter(i => i.item_name && i.item_name.trim().length > 0)
    .map(i => ({
      id: i.id || null,
      item_name: i.item_name.trim(),
      doc_mode: i.doc_mode
    }));

  if (validItems.length === 0) {
    error.value = 'Minimal harus memiliki 1 rincian sub pekerjaan.';
    return;
  }

  submitting.value = true;
  error.value = '';

  try {
    const payload = {
      ...formData,
      member_ids: selectedMemberIds.value,
      items: validItems
    };

    const res = await api.updateWorkOrder(props.workOrderId, payload);
    alert(`Data & pengaturan SPK ${formData.spk_number} berhasil diperbarui!`);
    emit('updated', res.data);
    emit('close');
  } catch (err) {
    error.value = 'Gagal menyimpan perubahan SPK: ' + err.message;
  } finally {
    submitting.value = false;
  }
}
</script>
