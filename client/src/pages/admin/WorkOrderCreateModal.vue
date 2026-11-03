<template>
  <div class="fixed inset-0 z-50 bg-[#0B0C0E]/85 backdrop-blur-xs flex items-center justify-center p-0 sm:p-4 overflow-y-auto font-sans">
    <div class="bg-[#13171F] border-0 sm:border border-[#232A36] rounded-none sm:rounded-xs max-w-4xl w-full shadow-2xl overflow-hidden my-0 sm:my-4 flex flex-col h-full sm:h-auto sm:max-h-[92vh] animate-fade-in font-mono text-white">
      <!-- Modal Header (Industrial CAD - Mobile Optimized) -->
      <div class="px-3.5 py-3 sm:px-6 sm:py-4 border-b border-[#232A36] flex items-center justify-between bg-[#0E1117] shrink-0 sticky top-0 z-20">
        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
          <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xs bg-[#1C2028] border border-[#FACC15] text-[#FACC15] flex items-center justify-center font-mono shrink-0">
            <FileText class="w-4 h-4 text-[#FACC15]" />
          </div>
          <div class="min-w-0">
            <h3 class="font-bold text-xs sm:text-base text-white flex items-center gap-1.5 uppercase tracking-wider font-mono truncate">
              <span>TERBITKAN SPK WORK ORDER</span>
            </h3>
            <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 font-mono truncate">Penerbitan SPK baru dengan rincian sub-pekerjaan & target lokasi.</p>
          </div>
        </div>

        <button
          type="button"
          @click="$emit('close')"
          class="p-2 sm:p-1.5 text-slate-400 hover:text-white bg-[#1C2028] hover:bg-[#232A36] active:bg-[#232A36] border border-[#232A36] rounded-xs transition-colors cursor-pointer shrink-0 ml-2"
          title="Tutup Modal"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Scrollable Form Body -->
      <div class="p-3.5 sm:p-6 overflow-y-auto space-y-4 text-xs custom-scrollbar bg-[#13171F] flex-1 min-h-0">
        <!-- Error / Validation Banner -->
        <div v-if="error" class="p-2.5 sm:p-3 bg-rose-950/40 border border-rose-500/50 text-rose-300 text-xs rounded-xs flex items-center justify-between gap-2 font-mono">
          <div class="flex items-center gap-2">
            <AlertCircle class="w-4 h-4 shrink-0 text-rose-400" />
            <span class="text-[11px]">{{ error }}</span>
          </div>
          <button type="button" @click="error = null" class="text-rose-400 hover:text-rose-200 font-bold text-xs cursor-pointer p-1">
            ✕
          </button>
        </div>

        <!-- Step Tabs (Industrial CAD Segmented - Mobile 50/50 Grid) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#232A36] pb-3">
          <div class="grid grid-cols-2 gap-1 w-full sm:w-auto p-1 bg-[#0E1117] border border-[#232A36] rounded-xs font-mono text-xs">
            <button
              type="button"
              @click="activeTab = 'info'"
              :class="[
                'px-2 sm:px-3 py-2 sm:py-1.5 rounded-xs transition-colors cursor-pointer uppercase tracking-wider font-mono text-center flex items-center justify-center gap-1.5 text-[11px] sm:text-xs',
                activeTab === 'info'
                  ? 'bg-[#1C2028] text-[#FACC15] border-l-[3px] border-[#FACC15] font-bold'
                  : 'text-slate-400 hover:text-slate-200 hover:bg-[#1A202C]/60 border-l-[3px] border-transparent'
              ]"
            >
              <span>1. Data Pokok</span>
              <span class="hidden sm:inline">& Cabang</span>
            </button>
            <button
              type="button"
              @click="activeTab = 'items'"
              :class="[
                'px-2 sm:px-3 py-2 sm:py-1.5 rounded-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5 uppercase tracking-wider font-mono text-center text-[11px] sm:text-xs',
                activeTab === 'items'
                  ? 'bg-[#1C2028] text-[#FACC15] border-l-[3px] border-[#FACC15] font-bold'
                  : 'text-slate-400 hover:text-slate-200 hover:bg-[#1A202C]/60 border-l-[3px] border-transparent'
              ]"
            >
              <span>2. Sub-Pekerjaan</span>
              <span class="px-1.5 py-0.2 rounded-xs bg-[#13171F] text-[#FACC15] border border-[#232A36] text-[10px] font-bold font-mono">
                {{ subItems.length }}
              </span>
            </button>
          </div>

          <div class="hidden sm:flex items-center gap-2 px-2.5 py-1 rounded-xs bg-[#0E1117] border border-[#232A36] text-[10px] font-mono text-slate-400">
            <span class="w-1.5 h-1.5 rounded-full bg-[#FACC15] animate-pulse"></span>
            <span>MODE: CAD WORK ORDER ENTRY</span>
          </div>
        </div>

        <!-- TAB 1: INFORMASI POKOK & CABANG TOKO -->
        <div v-show="activeTab === 'info'" class="space-y-4 font-mono">
          
          <!-- Row 1: Judul Proyek Cabang (Primary Focus) -->
          <div>
            <label class="block font-bold text-slate-300 mb-1 text-xs uppercase tracking-wider">
              // Judul / Nama Proyek Cabang <span class="text-[#FACC15]">*</span>
            </label>
            <input
              type="text"
              placeholder="Contoh: Pemasangan Signage & Kanopi Indomaret Adam Malik"
              v-model="formData.title"
              class="w-full px-3.5 py-2.5 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white focus:outline-none font-bold text-xs sm:text-sm"
            />
          </div>

          <!-- Row 2: Client & Area -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">
                // Perusahaan Client (Pemberi Tugas) <span class="text-[#FACC15]">*</span>
              </label>
              <select
                v-model="formData.vendor_id"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white font-mono focus:outline-none text-xs"
              >
                <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }} ({{ v.code }})</option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">
                // Area Operasional <span class="text-[#FACC15]">*</span>
              </label>
              <select
                v-model="formData.area_id"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white font-mono focus:outline-none text-xs"
              >
                <option v-for="a in areas" :key="a.id" :value="a.id">{{ a.name }} ({{ a.city }})</option>
              </select>
            </div>
          </div>

          <!-- Row 3: Location Name & Full Address -->
          <div>
            <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">
              // Nama Cabang & Alamat Lokasi Lengkap <span class="text-[#FACC15]">*</span>
            </label>
            <input
              type="text"
              placeholder="Contoh: Indomaret Cabang Adam Malik - Jl. Adam Malik No. 12, Bengkulu"
              v-model="formData.location_name"
              class="w-full px-3.5 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white focus:outline-none text-xs"
            />
          </div>

          <!-- Row 4: PIC & Dates -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">// PIC Teknisi Lapangan</label>
              <select
                v-model="formData.pic_user_id"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white font-mono focus:outline-none text-xs"
              >
                <option value="">Pilih nanti (Status: READY)</option>
                <option v-for="u in fieldUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.phone }})</option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">// Tanggal Mulai</label>
              <input
                type="date"
                v-model="formData.start_date"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white font-mono focus:outline-none text-xs"
              />
            </div>
            <div>
              <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">
                // Target Selesai / Deadline <span class="text-[#FACC15]">*</span>
              </label>
              <input
                type="date"
                v-model="formData.deadline"
                class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-[#FACC15] font-mono font-bold focus:outline-none text-xs"
              />
            </div>
          </div>

          <!-- ACCORDION: Opsi Finansial & Kategori Lanjutan -->
          <div class="pt-2">
            <div class="border border-[#232A36] rounded-xs overflow-hidden bg-[#0E1117]">
              <button
                type="button"
                @click="showFinancialOptions = !showFinancialOptions"
                class="w-full px-4 py-2.5 flex items-center justify-between text-left hover:bg-[#1A202C]/60 transition-colors cursor-pointer select-none font-mono"
              >
                <div class="flex items-center gap-2">
                  <BadgeDollarSign class="w-4 h-4 text-[#FACC15]" />
                  <span class="font-bold text-xs text-slate-200 uppercase tracking-wider">Informasi Finansial & Kategori Khusus (Opsional)</span>
                  <span v-if="formData.contract_value > 0" class="px-2 py-0.5 rounded-xs bg-emerald-950/60 text-emerald-400 border border-emerald-500/40 text-[10px] font-bold font-mono">
                    Rp {{ Number(formData.contract_value).toLocaleString('id-ID') }}
                  </span>
                </div>
                <div class="flex items-center gap-1.5 text-slate-400">
                  <span class="text-[10px] uppercase tracking-wider">{{ showFinancialOptions ? 'Tutup' : 'Buka' }}</span>
                  <ChevronDown :class="['w-4 h-4 transition-transform duration-200', showFinancialOptions ? 'rotate-180' : '']" />
                </div>
              </button>

              <div v-show="showFinancialOptions" class="p-4 border-t border-[#232A36] space-y-3.5 bg-[#0B0C0E]">
                <!-- Row: Kategori & Nilai Kontrak -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                  <div>
                    <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">Kategori Jenis Pekerjaan</label>
                    <select
                      v-model="formData.job_type_id"
                      @change="handleJobTypeChange"
                      class="w-full px-3 py-2 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white font-mono focus:outline-none"
                    >
                      <option value="">Pilih Kategori (Opsional)</option>
                      <option v-for="j in jobTypes" :key="j.id" :value="j.id">
                        {{ j.name }} (Tarif Standar: Rp {{ Number(j.standard_price || 0).toLocaleString('id-ID') }})
                      </option>
                    </select>
                  </div>

                  <div>
                    <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">Nilai Kontrak / Pekerjaan (Rp)</label>
                    <input
                      type="number"
                      v-model="formData.contract_value"
                      placeholder="Contoh: 15000000"
                      class="w-full px-3 py-2 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white font-mono focus:outline-none"
                    />
                    <div v-if="formData.contract_value" class="text-[11px] font-mono font-bold text-emerald-400 mt-1">
                      Rp {{ Number(formData.contract_value).toLocaleString('id-ID') }}
                    </div>
                  </div>
                </div>

                <!-- Custom SPK Number -->
                <div>
                  <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">Nomor SPK Kustom (Opsional)</label>
                  <input
                    type="text"
                    placeholder="Biarkan kosong untuk auto-generate nomor SPK resmi"
                    v-model="formData.spk_number"
                    class="w-full px-3 py-2 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white font-mono focus:outline-none text-xs"
                  />
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- TAB 2: LINGKUP SUB-PEKERJAAN & PENGATURAN GPS / TIMESTAMP -->
        <div v-show="activeTab === 'items'" class="space-y-4 font-mono">
          
          <!-- Sub-Pekerjaan Section with Fast 1-Click Presets -->
          <div class="bg-[#0E1117] rounded-xs p-4 border border-[#232A36] space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div>
                <h4 class="font-bold text-xs text-white flex items-center gap-1.5 uppercase tracking-wider">
                  <Layers class="w-4 h-4 text-[#FACC15]" />
                  <span>Lingkup Sub-Pekerjaan di Lokasi Ini ({{ subItems.length }} Item)</span>
                </h4>
                <p class="text-[11px] text-slate-400 font-sans">Tambahkan rincian pekerjaan atau gunakan tombol preset instan di bawah.</p>
              </div>

              <button
                type="button"
                @click="addSubItem"
                class="px-3 py-1.5 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] rounded-xs text-xs font-bold font-mono uppercase tracking-wider flex items-center gap-1.5 shadow-xs active:scale-95 transition-all cursor-pointer self-start sm:self-auto"
              >
                <Plus class="w-3.5 h-3.5 stroke-[3]" />
                <span>+ Tambah Sub-Item</span>
              </button>
            </div>

            <!-- Fast 1-Click Presets -->
            <div class="flex items-center gap-1.5 flex-wrap pt-2 border-t border-[#232A36]">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono">Preset Cepat:</span>
              <button
                v-for="preset in quickPresets"
                :key="preset"
                type="button"
                @click="addPresetItem(preset)"
                class="px-2 py-0.5 rounded-xs text-[10px] font-mono bg-[#13171F] hover:bg-[#1C2028] text-slate-200 hover:text-[#FACC15] border border-[#232A36] hover:border-[#FACC15]/40 transition-colors cursor-pointer active:scale-95"
              >
                + {{ preset }}
              </button>
            </div>

            <!-- List of Sub-Items -->
            <div class="space-y-2.5 max-h-60 overflow-y-auto custom-scrollbar pr-1">
              <div
                v-for="(itm, idx) in subItems"
                :key="idx"
                class="p-3 bg-[#13171F] border border-[#232A36] rounded-xs space-y-2 relative"
              >
                <div class="flex items-center justify-between gap-2">
                  <span class="font-bold text-[10px] text-[#FACC15] bg-[#1C2028] border border-[#232A36] px-2 py-0.5 rounded-xs uppercase tracking-wider font-mono">
                    Sub-Pekerjaan #{{ idx + 1 }}
                  </span>
                  <button
                    v-if="subItems.length > 1"
                    type="button"
                    @click="removeSubItem(idx)"
                    class="text-rose-400 hover:text-rose-200 p-1 rounded-xs hover:bg-rose-950/40 transition-colors cursor-pointer"
                    title="Hapus sub-pekerjaan ini"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                  <div>
                    <label class="block font-bold text-slate-300 text-[10px] mb-1 uppercase tracking-wider">Nama Sub-Pekerjaan</label>
                    <input
                      type="text"
                      placeholder="Contoh: Pemasangan Neon Box Depan Toko"
                      v-model="itm.item_name"
                      class="w-full px-3 py-1.5 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs text-white focus:outline-none"
                    />
                  </div>
                  <div>
                    <label class="block font-bold text-slate-300 text-[10px] mb-1 uppercase tracking-wider">Mode Evidence Foto</label>
                    <select
                      v-model="itm.doc_mode"
                      class="w-full px-3 py-1.5 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs text-white focus:outline-none font-mono"
                    >
                      <option value="BEFORE_PROCESS_AFTER">BEFORE + PROCESS + AFTER (3 Fase Lengkap)</option>
                      <option value="AFTER_ONLY">AFTER ONLY (Hasil Akhir Saja)</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- GPS Coordinates & Auto-Detect (Industrial Telemetry Box) -->
          <div class="p-3 sm:p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-2.5 font-mono">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <label class="block font-bold text-white text-xs flex items-center gap-1.5 uppercase tracking-wider">
                <MapPin class="w-3.5 h-3.5 text-blue-400 shrink-0" />
                <span>Titik Target Koordinat GPS Cabang (Opsional)</span>
              </label>
              <button
                type="button"
                @click="detectCurrentLocation"
                :disabled="detectingGps"
                class="w-full sm:w-auto justify-center px-2.5 py-1.5 bg-[#1C2028] hover:bg-[#232A36] text-[#FACC15] border border-[#232A36] hover:border-[#FACC15]/40 rounded-xs text-[10px] font-bold flex items-center gap-1.5 cursor-pointer active:scale-95 transition-colors uppercase tracking-wider"
              >
                <Navigation class="w-3 h-3 text-[#FACC15]" />
                <span>{{ detectingGps ? 'Mendeteksi...' : '📍 Ambil GPS Saya' }}</span>
              </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
              <div>
                <span class="block text-[10px] text-slate-400 font-bold mb-0.5 uppercase tracking-wider">Latitude:</span>
                <input
                  type="number"
                  step="any"
                  v-model="formData.target_lat"
                  placeholder="-3.79284"
                  class="w-full px-2.5 py-2 sm:py-1.5 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs font-mono text-white focus:outline-none"
                />
              </div>
              <div>
                <span class="block text-[10px] text-slate-400 font-bold mb-0.5 uppercase tracking-wider">Longitude:</span>
                <input
                  type="number"
                  step="any"
                  v-model="formData.target_lng"
                  placeholder="102.2607"
                  class="w-full px-2.5 py-2 sm:py-1.5 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs font-mono text-white focus:outline-none"
                />
              </div>
            </div>
          </div>

          <!-- Check-In & Timestamp Options -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 font-mono">
            <!-- 1. Check-in Requirement -->
            <div class="p-3 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-1.5">
              <div class="flex items-start gap-2.5">
                <input
                  type="checkbox"
                  id="require_checkin"
                  v-model="formData.require_checkin"
                  class="w-4 h-4 mt-0.5 rounded-xs accent-[#FACC15] cursor-pointer"
                />
                <div>
                  <label for="require_checkin" class="font-bold text-white cursor-pointer text-xs uppercase tracking-wider">
                    Wajib 1x Check-In GPS di Lokasi
                  </label>
                  <p class="text-[10px] text-slate-400 mt-0.5 font-sans">
                    Teknisi harus check-in lokasi cabang saat pertama kali tiba di lokasi.
                  </p>
                </div>
              </div>
            </div>

            <!-- 2. Use Timestamp Camera Option -->
            <div class="p-3 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-1.5">
              <div class="flex items-start gap-2.5">
                <input
                  type="checkbox"
                  id="use_timestamp"
                  v-model="formData.use_timestamp"
                  class="w-4 h-4 mt-0.5 rounded-xs accent-[#FACC15] cursor-pointer"
                />
                <div>
                  <label for="use_timestamp" class="font-bold text-white cursor-pointer text-xs flex items-center gap-1 uppercase tracking-wider">
                    <Camera class="w-3.5 h-3.5 text-[#FACC15]" />
                    <span>Wajib Kamera Timestamp GPS</span>
                  </label>
                  <p class="text-[10px] text-slate-400 mt-0.5 font-sans">
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

            <!-- 3. BA Opname Physical Document Requirement -->
            <div class="p-3 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-1.5 sm:col-span-2">
              <div class="flex items-start gap-2.5">
                <input
                  type="checkbox"
                  id="require_ba_doc"
                  v-model="formData.require_ba_doc"
                  class="w-4 h-4 mt-0.5 rounded-xs accent-[#FACC15] cursor-pointer"
                />
                <div>
                  <label for="require_ba_doc" class="font-bold text-white cursor-pointer text-xs flex items-center gap-1.5 uppercase tracking-wider">
                    <FileCheck2 class="w-3.5 h-3.5 text-[#FACC15]" />
                    <span>Wajib Upload Foto Berkas BA Opname</span>
                  </label>
                  <p class="text-[10px] text-slate-400 mt-0.5 font-sans">
                    <span v-if="formData.require_ba_doc" class="text-[#FACC15] font-bold">
                      ✓ Wajib (Pekerjaan baru dapat diajukan setelah mengunggah foto bukti fisik Berita Acara yang telah ditandatangani toko).
                    </span>
                    <span v-else class="text-slate-400">
                      Opsional / Bebas (Pekerjaan dapat diajukan tanpa foto berkas fisik BA Opname).
                    </span>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[10px] sm:text-[11px]">Catatan & Instruksi Tambahan</label>
            <textarea
              rows="2"
              placeholder="Tambahkan catatan khusus untuk tim lapangan..."
              v-model="formData.notes"
              class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs text-white focus:outline-none font-mono"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Action Buttons (Industrial CAD Actions - Mobile Ergonomic Layout) -->
      <div class="p-3 sm:px-6 sm:py-4 bg-[#0E1117] border-t border-[#232A36] shrink-0 font-mono sticky bottom-0 z-20">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
          <!-- Step toggle button (Full width on mobile) -->
          <div class="w-full sm:w-auto">
            <button
              v-if="activeTab === 'items'"
              type="button"
              @click="activeTab = 'info'"
              class="w-full sm:w-auto justify-center px-4 py-2.5 sm:py-2 bg-[#1C2028] hover:bg-[#232A36] active:bg-[#232A36] text-slate-300 hover:text-white font-bold rounded-xs border border-[#232A36] transition-colors cursor-pointer text-xs uppercase tracking-wider flex items-center gap-1.5"
            >
              <span>← Kembali ke Data Pokok</span>
            </button>
            <button
              v-if="activeTab === 'info'"
              type="button"
              @click="activeTab = 'items'"
              class="w-full sm:w-auto justify-center px-4 py-2.5 sm:py-2 bg-[#1C2028] hover:bg-[#232A36] active:bg-[#232A36] text-[#FACC15] border border-[#232A36] hover:border-[#FACC15]/40 font-bold rounded-xs transition-colors cursor-pointer text-xs uppercase tracking-wider flex items-center gap-1.5"
            >
              <span>Lanjut: Sub-Pekerjaan ({{ subItems.length }}) →</span>
            </button>
          </div>

          <!-- Action buttons (Cancel & Submit) -->
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <button
              type="button"
              @click="$emit('close')"
              class="w-1/3 sm:w-auto justify-center px-4 py-2.5 sm:py-2 bg-[#1C2028] hover:bg-[#232A36] active:bg-[#232A36] text-slate-300 hover:text-white font-bold rounded-xs border border-[#232A36] transition-colors cursor-pointer text-xs uppercase tracking-wider text-center"
            >
              Batal
            </button>
            <button
              type="button"
              @click="handleSubmit"
              :disabled="loading"
              class="w-2/3 sm:w-auto justify-center flex-1 sm:flex-initial px-5 py-2.5 sm:py-2 bg-[#FACC15] hover:bg-[#FFE033] active:bg-[#EAB308] text-[#0B0C0E] font-bold rounded-xs flex items-center gap-2 border border-[#FACC15] shadow-xs active:scale-[0.98] transition-all disabled:opacity-50 cursor-pointer text-xs uppercase tracking-wider text-center"
            >
              <Loader2 v-if="loading" class="w-4 h-4 animate-spin text-[#0B0C0E]" />
              <FileCheck2 v-else class="w-4 h-4 stroke-[2.5]" />
              <span class="truncate">{{ loading ? 'Menerbitkan...' : 'Terbitkan SPK' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { api } from '../../services/api';
import {
  X,
  FileText,
  AlertCircle,
  Layers,
  Plus,
  Trash2,
  MapPin,
  Navigation,
  Camera,
  FileCheck2,
  Loader2,
  BadgeDollarSign,
  ChevronDown
} from 'lucide-vue-next';

const emit = defineEmits(['close', 'success']);

const activeTab = ref('info');
const showFinancialOptions = ref(false);

const vendors = ref([]);
const areas = ref([]);
const jobTypes = ref([]);
const fieldUsers = ref([]);
const loading = ref(false);
const detectingGps = ref(false);
const error = ref(null);

const quickPresets = [
  'Pemasangan Signage Fasad',
  'Neon Box Depan',
  'Huruf Timbul LED',
  'Pylon Sign 6 Meter',
  'Cat & Finishing Dinding'
];

const subItems = ref([
  { item_name: '', doc_mode: 'BEFORE_PROCESS_AFTER' }
]);

const formData = reactive({
  spk_number: '',
  title: '',
  vendor_id: '',
  area_id: '',
  job_type_id: '',
  contract_value: 0,
  location_name: '',
  target_lat: null,
  target_lng: null,
  pic_user_id: '',
  start_date: new Date().toISOString().split('T')[0],
  deadline: new Date(Date.now() + 5 * 86400000).toISOString().split('T')[0],
  require_checkin: true,
  require_geofence: false,
  geofence_radius: 500,
  use_timestamp: true,
  require_ba_doc: false,
  notes: ''
});

function addPresetItem(presetName) {
  if (subItems.value.length === 1 && !subItems.value[0].item_name) {
    subItems.value[0].item_name = presetName;
  } else {
    subItems.value.push({
      item_name: presetName,
      doc_mode: 'BEFORE_PROCESS_AFTER'
    });
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

function handleJobTypeChange() {
  const selected = jobTypes.value.find(j => j.id === formData.job_type_id);
  if (selected && selected.standard_price) {
    formData.contract_value = selected.standard_price;
  }
}

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

onMounted(async () => {
  try {
    const [vRes, aRes, jRes, uRes] = await Promise.all([
      api.getVendors(),
      api.getAreas(),
      api.getJobTypes(),
      api.getUsers({ role: 'FIELD_TEAM' })
    ]);
    vendors.value = vRes.data || [];
    areas.value = aRes.data || [];
    jobTypes.value = jRes.data || [];
    fieldUsers.value = uRes.data || [];

    if (vendors.value.length > 0) formData.vendor_id = vendors.value[0].id;
    if (areas.value.length > 0) formData.area_id = areas.value[0].id;
  } catch (err) {
    console.error('Failed to load form options:', err);
  }
});

async function handleSubmit() {
  // Clear any previous error
  error.value = null;

  // Validate required fields (Only Essential Data)
  if (!formData.title || formData.title.trim().length === 0) {
    error.value = 'Judul / Nama Proyek Cabang wajib diisi.';
    activeTab.value = 'info';
    return;
  }

  if (!formData.vendor_id) {
    error.value = 'Silakan pilih Perusahaan Client (Pemberi Tugas).';
    activeTab.value = 'info';
    return;
  }

  if (!formData.area_id) {
    error.value = 'Silakan pilih Area Operasional.';
    activeTab.value = 'info';
    return;
  }

  if (!formData.location_name || formData.location_name.trim().length === 0) {
    error.value = 'Nama Cabang & Alamat Lokasi Lengkap wajib diisi.';
    activeTab.value = 'info';
    return;
  }

  if (!formData.deadline) {
    error.value = 'Target Selesai / Deadline wajib diisi.';
    activeTab.value = 'info';
    return;
  }

  // Filter valid sub-items or fallback automatically to project title
  let validItems = subItems.value
    .filter(i => i.item_name && i.item_name.trim().length > 0)
    .map(i => ({
      item_name: i.item_name.trim(),
      doc_mode: i.doc_mode || 'BEFORE_PROCESS_AFTER'
    }));

  if (validItems.length === 0) {
    validItems = [{
      item_name: formData.title.trim(),
      doc_mode: 'BEFORE_PROCESS_AFTER'
    }];
  }

  loading.value = true;
  try {
    const payload = {
      spk_number: formData.spk_number ? formData.spk_number.trim() : undefined,
      title: formData.title.trim(),
      vendor_id: formData.vendor_id,
      area_id: formData.area_id,
      job_type_id: formData.job_type_id ? formData.job_type_id : null,
      contract_value: formData.contract_value ? Number(formData.contract_value) : 0,
      location_name: formData.location_name.trim(),
      target_lat: formData.target_lat ? Number(formData.target_lat) : null,
      target_lng: formData.target_lng ? Number(formData.target_lng) : null,
      pic_user_id: formData.pic_user_id || null,
      start_date: formData.start_date || undefined,
      deadline: formData.deadline,
      require_checkin: !!formData.require_checkin,
      use_timestamp: !!formData.use_timestamp,
      require_ba_doc: !!formData.require_ba_doc,
      notes: formData.notes || null,
      items: validItems
    };

    await api.createWorkOrder(payload);
    emit('success');
    emit('close');
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Gagal menerbitkan SPK. Silakan periksa kembali data Anda.';
  } finally {
    loading.value = false;
  }
}
</script>
