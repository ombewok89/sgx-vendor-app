<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-purple-800 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-purple-900/20">
            <FileCode class="w-4 h-4" />
          </div>
          <span>Template Dokumen BA Opname</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1 font-medium">
          Kelola background template kertas kop surat resmi, logo, klausul serah terima visual, dan pengaturan kolom tanda tangan dinamis.
        </p>
      </div>
      <button
        @click="openAddModal"
        class="px-4 py-2 bg-gradient-to-r from-purple-900 via-purple-800 to-indigo-800 hover:from-purple-800 hover:to-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-md shadow-purple-900/20 active:scale-95 transition-all self-start sm:self-auto cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>Tambah Template Baru</span>
      </button>
    </div>

    <!-- Templates Grid Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="tmpl in templates"
        :key="tmpl.id"
        class="glass-card rounded-3xl p-5 border border-white/80 shadow-glass space-y-4 relative flex flex-col justify-between hover:border-purple-300 transition-all duration-300 group"
      >
        <div class="space-y-3">
          <!-- Background / Header Preview Container -->
          <div class="h-32 rounded-2xl bg-slate-100 border border-slate-200/80 overflow-hidden relative flex items-center justify-center">
            <img
              v-if="tmpl.background_image_url || tmpl.header_image_url"
              :src="tmpl.background_image_url || tmpl.header_image_url"
              alt="Background Template"
              class="w-full h-full object-cover"
            />
            <div v-else class="flex flex-col items-center gap-1.5 text-slate-400">
              <ImageIcon class="w-6 h-6 opacity-40" />
              <span class="text-[10px] font-medium">Background Polos (Standar)</span>
            </div>

            <!-- Logo Floating Badge -->
            <div
              v-if="tmpl.logo_url"
              class="absolute top-2 left-2 w-10 h-10 rounded-xl bg-white p-1 shadow-md border border-slate-200/80 overflow-hidden flex items-center justify-center"
            >
              <img :src="tmpl.logo_url" alt="Logo" class="w-full h-full object-contain" />
            </div>

            <!-- Default Badge -->
            <div v-if="tmpl.is_default" class="absolute top-2 right-2">
              <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-600 text-white shadow-xs flex items-center gap-1">
                <CheckCircle2 class="w-3 h-3" /> TEMPLATE UTAMA
              </span>
            </div>

            <div v-if="tmpl.background_image_url" class="absolute bottom-2 left-2">
              <span class="px-2 py-0.5 rounded-md text-[8px] font-bold bg-slate-900/80 text-white backdrop-blur-xs flex items-center gap-1">
                Kop Surat A4 Terpasang
              </span>
            </div>
          </div>

          <!-- Template Info -->
          <div>
            <div class="flex items-center justify-between">
              <h3 class="font-black text-sm text-slate-900 group-hover:text-purple-900 transition-colors">
                {{ tmpl.name }}
              </h3>
              <span class="text-[10px] font-mono text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">
                {{ tmpl.code }}
              </span>
            </div>
            <div class="text-[11px] text-slate-500 mt-1 line-clamp-2" v-html="stripHtml(tmpl.body_template)"></div>
          </div>

          <!-- Signatory Details Summary -->
          <div class="p-3 bg-slate-50/80 rounded-2xl border border-slate-200/60 text-[11px] space-y-1.5">
            <div class="font-bold text-slate-700 text-[10px] uppercase tracking-wider flex items-center justify-between">
              <span>Kolom Tanda Tangan:</span>
              <span class="px-1.5 py-0.2 rounded bg-purple-100 text-purple-800 text-[9px] font-mono">
                {{ getSignatoriesList(tmpl).length }} Pihak
              </span>
            </div>
            <div v-for="(sig, sIdx) in getSignatoriesList(tmpl).slice(0, 3)" :key="sIdx" class="flex items-center justify-between text-slate-600 text-[10px]">
              <span class="truncate max-w-[110px] text-slate-500">{{ sig.party_title || `Pihak #${sIdx+1}` }}:</span>
              <strong class="text-slate-800 truncate max-w-[130px]">{{ sig.name }}</strong>
            </div>
            <div v-if="getSignatoriesList(tmpl).length > 3" class="text-[9px] text-purple-700 font-bold text-right">
              + {{ getSignatoriesList(tmpl).length - 3 }} pihak lainnya
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
          <button
            @click="openPreview(tmpl)"
            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl flex items-center gap-1.5 transition-all cursor-pointer"
          >
            <Eye class="w-3.5 h-3.5" />
            <span>Pratinjau Cetak</span>
          </button>

          <div class="flex items-center gap-1.5">
            <button
              v-if="!tmpl.is_default"
              @click="handleSetDefault(tmpl)"
              title="Jadikan Default"
              class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold rounded-xl border border-emerald-200 transition-all cursor-pointer text-[11px]"
            >
              Set Default
            </button>
            <button
              @click="openEditModal(tmpl)"
              title="Edit Template & Branding"
              class="p-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-800 transition-colors cursor-pointer"
            >
              <Pencil class="w-3.5 h-3.5" />
            </button>
            <button
              v-if="!tmpl.is_default"
              @click="handleDelete(tmpl)"
              title="Hapus Template"
              class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 transition-colors cursor-pointer"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="glass-modal rounded-3xl max-w-3xl w-full shadow-2xl p-6 space-y-4 text-xs border border-white/80 max-h-[92vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
          <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
            <FileCode class="w-4 h-4 text-purple-700" />
            <span>{{ isEditing ? 'Edit Template Dokumen BA' : 'Tambah Template Dokumen BA Baru' }}</span>
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Form Tabs -->
        <div class="flex gap-2 border-b border-slate-100 pb-2 font-bold text-xs">
          <button
            type="button"
            @click="modalTab = 'branding'"
            :class="[
              'px-3.5 py-1.5 rounded-xl transition-all cursor-pointer',
              modalTab === 'branding' ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'
            ]"
          >
            1. Background Kop Surat & Logo
          </button>
          <button
            type="button"
            @click="modalTab = 'content'"
            :class="[
              'px-3.5 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5',
              modalTab === 'content' ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'
            ]"
          >
            <span>2. Klausul & Format Isi</span>
            <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-emerald-100 text-emerald-800 font-bold">Visual Editor</span>
          </button>
          <button
            type="button"
            @click="modalTab = 'signatories'"
            :class="[
              'px-3.5 py-1.5 rounded-xl transition-all cursor-pointer flex items-center gap-1.5',
              modalTab === 'signatories' ? 'bg-purple-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'
            ]"
          >
            <span>3. Kolom Penandatangan</span>
            <span class="px-1.5 py-0.2 rounded-full text-[9px]" :class="modalTab === 'signatories' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-900'">
              {{ signatoriesList.length }}
            </span>
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- TAB 1: Background Template & Logo Upload -->
          <div v-show="modalTab === 'branding'" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block font-bold mb-1">Nama Template *</label>
                <input
                  required
                  type="text"
                  placeholder="Contoh: Template Resmi BA SGX 2026"
                  v-model="formData.name"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-bold"
                />
              </div>
              <div>
                <label class="block font-bold mb-1">Kode Template *</label>
                <input
                  required
                  type="text"
                  placeholder="TMPL-BA-SGX-01"
                  v-model="formData.code"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-mono"
                />
              </div>
            </div>

            <!-- Upload Background Template Kop Surat -->
            <div class="p-4 bg-gradient-to-br from-purple-50/70 to-indigo-50/50 border-2 border-dashed border-purple-300 rounded-2xl space-y-2.5">
              <div class="flex items-start justify-between gap-2">
                <div>
                  <label class="block font-bold text-slate-900 text-xs flex items-center gap-1.5">
                    <ImageIcon class="w-4 h-4 text-purple-700" />
                    <span>Upload Background Template Kop Surat (Kertas Kop A4):</span>
                  </label>
                  <p class="text-[11px] text-slate-600 mt-0.5">
                    Unggah file gambar background / kertas kop surat resmi (Rasio A4, Resolusi ideal: 1240 x 1754 px).
                    Gambar ini akan menjadi latar belakang penuh saat dokumen BA dicetak atau diekspor ke PDF.
                  </p>
                </div>
                <button
                  v-if="bgPreview || formData.background_image_url || formData.header_image_url"
                  type="button"
                  @click="removeBackground"
                  class="text-[10px] font-bold text-rose-600 hover:text-rose-700 bg-white px-2 py-1 rounded-lg border border-rose-200 cursor-pointer"
                >
                  Hapus Background
                </button>
              </div>

              <!-- Preview Background -->
              <div
                v-if="bgPreview || formData.background_image_url || formData.header_image_url"
                class="h-36 rounded-xl border border-purple-200 overflow-hidden bg-white relative shadow-inner flex items-center justify-center"
              >
                <img
                  :src="bgPreview || formData.background_image_url || formData.header_image_url"
                  alt="Preview Background Template"
                  class="w-full h-full object-contain"
                />
                <span class="absolute bottom-2 right-2 px-2 py-0.5 bg-slate-900/80 text-white rounded text-[9px] font-mono">
                  Background A4 Terpasang
                </span>
              </div>

              <input
                type="file"
                accept="image/*"
                @change="handleBackgroundChange"
                class="text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-900 file:text-white hover:file:bg-purple-800 cursor-pointer"
              />
            </div>

            <!-- Upload Logo Perusahaan -->
            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-2">
              <div class="flex items-start justify-between gap-2">
                <label class="block font-bold text-slate-800">Logo Resmi Perusahaan (PNG Transparan / JPEG):</label>
                <button
                  v-if="logoPreview || formData.logo_url"
                  type="button"
                  @click="removeLogo"
                  class="text-[10px] font-bold text-rose-600 hover:text-rose-700 bg-white px-2 py-1 rounded-lg border border-rose-200 cursor-pointer"
                >
                  Hapus Logo
                </button>
              </div>
              <div class="flex items-center gap-3">
                <div v-if="logoPreview || formData.logo_url" class="w-14 h-14 rounded-xl bg-white border border-slate-300 p-1 flex items-center justify-center overflow-hidden">
                  <img :src="logoPreview || formData.logo_url" alt="Preview Logo" class="w-full h-full object-contain" />
                </div>
                <input
                  type="file"
                  accept="image/*"
                  @change="handleLogoChange"
                  class="text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-900 file:text-white hover:file:bg-purple-800 cursor-pointer"
                />
              </div>
            </div>

            <!-- Upload Footer / Stempel -->
            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-2">
              <div class="flex items-start justify-between gap-2">
                <div>
                  <label class="block font-bold text-slate-800">Gambar Footer / Catatan Bawah (Opsional):</label>
                  <p class="text-[11px] text-slate-500">Banner grafis opsional untuk bagian bawah halaman dokumen (Ukuran ideal: 1200 x 120 px).</p>
                </div>
                <button
                  v-if="footerPreview || formData.footer_image_url"
                  type="button"
                  @click="removeFooter"
                  class="text-[10px] font-bold text-rose-600 hover:text-rose-700 bg-white px-2 py-1 rounded-lg border border-rose-200 cursor-pointer"
                >
                  Hapus Footer
                </button>
              </div>
              <div v-if="footerPreview || formData.footer_image_url" class="h-14 rounded-xl border border-slate-300 overflow-hidden bg-white">
                <img :src="footerPreview || formData.footer_image_url" alt="Preview Footer" class="w-full h-full object-cover" />
              </div>
              <input
                type="file"
                accept="image/*"
                @change="handleFooterChange"
                class="text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-900 file:text-white hover:file:bg-purple-800 cursor-pointer"
              />
            </div>
          </div>

          <!-- TAB 2: Content & Clauses (Visual Rich Text Editor & Presets) -->
          <div v-show="modalTab === 'content'" class="space-y-4">
            <!-- Preset Clause Selector Bar -->
            <div class="p-3.5 bg-gradient-to-r from-purple-50 via-indigo-50 to-slate-50 border border-purple-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div>
                <span class="font-bold text-purple-900 text-xs flex items-center gap-1.5">
                  <Sparkles class="w-4 h-4 text-purple-700" />
                  <span>Pustaka Template Klausul Siap Pakai:</span>
                </span>
                <p class="text-[10px] text-slate-500">Pilih format teks dan pasal resmi serah terima yang sudah terstandarisasi.</p>
              </div>
              <div class="flex items-center gap-1.5">
                <select
                  v-model="selectedPresetKey"
                  class="px-3 py-1.5 bg-white border border-purple-300 rounded-xl text-xs font-semibold text-slate-800 cursor-pointer"
                >
                  <option value="standard">🌟 Standar Serah Terima (Garansi 90 Hari)</option>
                  <option value="facade">🌟 Branding Fasade & Signboard (Garansi 180 Hari)</option>
                  <option value="maintenance">🌟 Maintenance Panel Listrik (Garansi 60 Hari)</option>
                  <option value="partial">🌟 Serah Terima Bertahap / Parsial (Opname Progress)</option>
                </select>
                <button
                  type="button"
                  @click="applyPresetClause"
                  class="px-3 py-1.5 bg-purple-900 hover:bg-purple-800 text-white rounded-xl text-xs font-bold transition-all cursor-pointer shadow-xs active:scale-95"
                >
                  Terapkan
                </button>
              </div>
            </div>

            <!-- 1. Teks Pembuka Dokumen -->
            <div class="p-4 bg-white border border-slate-200 rounded-2xl space-y-2">
              <div class="flex items-center justify-between">
                <label class="block font-bold text-slate-900 text-xs">
                  1. Teks Pembuka Dokumen Berita Acara:
                </label>
                <!-- Formatting Toolbar -->
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg">
                  <button type="button" @click="wrapText('header_html', '<strong>', '</strong>')" title="Tebal (Bold)" class="px-2 py-0.5 font-bold hover:bg-white rounded text-xs cursor-pointer">B</button>
                  <button type="button" @click="wrapText('header_html', '<em>', '</em>')" title="Miring (Italic)" class="px-2 py-0.5 italic hover:bg-white rounded text-xs cursor-pointer">I</button>
                  <button type="button" @click="wrapText('header_html', '<u>', '</u>')" title="Garis Bawah (Underline)" class="px-2 py-0.5 underline hover:bg-white rounded text-xs cursor-pointer">U</button>
                </div>
              </div>

              <!-- Variable Insert Buttons -->
              <div class="flex flex-wrap items-center gap-1 py-1 text-[10px]">
                <span class="text-slate-400 font-medium mr-1">Sisip Variabel:</span>
                <button type="button" @click="insertVariable('header_html', '{{ba_date}}')" class="px-2 py-0.5 bg-purple-50 hover:bg-purple-100 text-purple-800 font-mono rounded-md border border-purple-200 cursor-pointer">+ Tanggal BA</button>
                <button type="button" @click="insertVariable('header_html', '{{title}}')" class="px-2 py-0.5 bg-purple-50 hover:bg-purple-100 text-purple-800 font-mono rounded-md border border-purple-200 cursor-pointer">+ Nama Proyek</button>
                <button type="button" @click="insertVariable('header_html', '{{location_name}}')" class="px-2 py-0.5 bg-purple-50 hover:bg-purple-100 text-purple-800 font-mono rounded-md border border-purple-200 cursor-pointer">+ Lokasi Cabang</button>
                <button type="button" @click="insertVariable('header_html', '{{vendor_name}}')" class="px-2 py-0.5 bg-purple-50 hover:bg-purple-100 text-purple-800 font-mono rounded-md border border-purple-200 cursor-pointer">+ Nama Client</button>
              </div>

              <textarea
                ref="headerTextarea"
                rows="3"
                v-model="formData.header_html"
                placeholder="Pada hari ini {{ba_date}}, telah dilakukan pemeriksaan dan verifikasi lapangan atas pelaksanaan seluruh item pekerjaan untuk {{title}} di lokasi {{location_name}}..."
                class="w-full px-3 py-2 bg-slate-50/50 border border-slate-200 rounded-xl leading-relaxed text-xs font-sans focus:bg-white transition-colors"
              />

              <!-- Live Formatted Preview Strip -->
              <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-xl text-[11px] text-slate-700 leading-relaxed">
                <span class="font-bold text-[9px] uppercase tracking-wider text-slate-400 block mb-0.5">Pratinjau Tampilan Cetak Pembuka:</span>
                <div v-html="renderClauseWithSampleData(formData.header_html)"></div>
              </div>
            </div>

            <!-- 2. Klausul Pernyataan Selesai & Garansi Mutu -->
            <div class="p-4 bg-white border border-slate-200 rounded-2xl space-y-2">
              <div class="flex items-center justify-between">
                <label class="block font-bold text-slate-900 text-xs">
                  2. Klausul Pernyataan Selesai & Garansi Mutu:
                </label>
                <!-- Formatting Toolbar -->
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg">
                  <button type="button" @click="wrapText('body_template', '<strong>', '</strong>')" title="Tebal (Bold)" class="px-2 py-0.5 font-bold hover:bg-white rounded text-xs cursor-pointer">B</button>
                  <button type="button" @click="wrapText('body_template', '<em>', '</em>')" title="Miring (Italic)" class="px-2 py-0.5 italic hover:bg-white rounded text-xs cursor-pointer">I</button>
                  <button type="button" @click="wrapText('body_template', '<u>', '</u>')" title="Garis Bawah (Underline)" class="px-2 py-0.5 underline hover:bg-white rounded text-xs cursor-pointer">U</button>
                  <button type="button" @click="insertBulletPoint" title="Poin Bullet" class="px-2 py-0.5 hover:bg-white rounded text-xs cursor-pointer font-bold">• List</button>
                </div>
              </div>

              <!-- Variable Insert Buttons -->
              <div class="flex flex-wrap items-center gap-1 py-1 text-[10px]">
                <span class="text-slate-400 font-medium mr-1">Sisip Variabel:</span>
                <button type="button" @click="insertVariable('body_template', '{{vendor_name}}')" class="px-2 py-0.5 bg-purple-50 hover:bg-purple-100 text-purple-800 font-mono rounded-md border border-purple-200 cursor-pointer">+ Nama Client</button>
                <button type="button" @click="insertVariable('body_template', '{{contract_value}}')" class="px-2 py-0.5 bg-purple-50 hover:bg-purple-100 text-purple-800 font-mono rounded-md border border-purple-200 cursor-pointer">+ Nilai Kontrak</button>
                <button type="button" @click="insertVariable('body_template', '{{spk_number}}')" class="px-2 py-0.5 bg-purple-50 hover:bg-purple-100 text-purple-800 font-mono rounded-md border border-purple-200 cursor-pointer">+ No. SPK</button>
                <button type="button" @click="insertVariable('body_template', '{{checkin_gps}}')" class="px-2 py-0.5 bg-purple-50 hover:bg-purple-100 text-purple-800 font-mono rounded-md border border-purple-200 cursor-pointer">+ GPS Check-in</button>
              </div>

              <textarea
                ref="bodyTextarea"
                rows="4"
                v-model="formData.body_template"
                placeholder="Berdasarkan pemeriksaan bukti foto digital Before, Process, After dan hasil verifikasi lapangan, kedua belah pihak menyatakan bahwa seluruh item pekerjaan telah SELESAI 100% SECARA BAIK..."
                class="w-full px-3 py-2 bg-slate-50/50 border border-slate-200 rounded-xl leading-relaxed text-xs font-sans focus:bg-white transition-colors"
              />

              <!-- Live Formatted Preview Strip -->
              <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-xl text-[11px] text-slate-700 leading-relaxed">
                <span class="font-bold text-[9px] uppercase tracking-wider text-slate-400 block mb-0.5">Pratinjau Tampilan Cetak Klausul:</span>
                <div v-html="renderClauseWithSampleData(formData.body_template)"></div>
              </div>
            </div>
          </div>

          <!-- TAB 3: Dynamic Signatories Builder -->
          <div v-show="modalTab === 'signatories'" class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="font-black text-slate-900 text-sm flex items-center gap-2">
                  <UserCheck class="w-4 h-4 text-purple-700" />
                  <span>Pengaturan Kolom Tanda Tangan Dokumen</span>
                </h4>
                <p class="text-[11px] text-slate-500">
                  Tambahkan atau sesuaikan kolom tanda tangan (cth: Pihak Pertama, Pihak Kedua, Kepala Cabang, Mengetahui GM).
                </p>
              </div>
              <button
                type="button"
                @click="addSignatoryColumn"
                class="px-3 py-1.5 bg-purple-100 hover:bg-purple-200 text-purple-900 font-bold rounded-xl flex items-center gap-1.5 text-xs transition-all cursor-pointer"
              >
                <Plus class="w-3.5 h-3.5" />
                <span>+ Tambah Kolom Tanda Tangan</span>
              </button>
            </div>

            <!-- Signatory Column Cards List -->
            <div class="space-y-3">
              <div
                v-for="(sig, index) in signatoriesList"
                :key="index"
                class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3 relative group"
              >
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                  <div class="flex items-center gap-2 font-bold text-slate-800 text-xs">
                    <span class="w-5 h-5 rounded-full bg-purple-900 text-white flex items-center justify-center text-[10px]">
                      {{ index + 1 }}
                    </span>
                    <span>Kolom Tanda Tangan #{{ index + 1 }}</span>
                  </div>
                  <button
                    v-if="signatoriesList.length > 1"
                    type="button"
                    @click="removeSignatoryColumn(index)"
                    title="Hapus Kolom Tanda Tangan Ini"
                    class="text-rose-500 hover:text-rose-700 p-1 rounded-lg hover:bg-rose-50 cursor-pointer flex items-center gap-1 text-[10px] font-bold"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                    <span>Hapus Kolom</span>
                  </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="block font-bold mb-0.5 text-[11px]">Label Posisi / Pihak *</label>
                    <input
                      required
                      type="text"
                      placeholder="Contoh: Pihak Pertama (Pemberi Tugas), Pihak Ketiga (Pimpinan Cabang)"
                      v-model="sig.party_title"
                      class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl"
                    />
                  </div>
                  <div>
                    <label class="block font-bold mb-0.5 text-[11px]">Nama Instansi / Perusahaan *</label>
                    <input
                      required
                      type="text"
                      placeholder="Contoh: PT SINAR GRAHA KREATIF, BANK MANDIRI"
                      v-model="sig.company_name"
                      class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl font-bold"
                    />
                  </div>
                  <div>
                    <label class="block font-bold mb-0.5 text-[11px]">Nama Pejabat Penandatangan *</label>
                    <input
                      required
                      type="text"
                      placeholder="Contoh: Dian Anggraini, S.T."
                      v-model="sig.name"
                      class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl"
                    />
                  </div>
                  <div>
                    <label class="block font-bold mb-0.5 text-[11px]">Jabatan Resmi *</label>
                    <input
                      required
                      type="text"
                      placeholder="Contoh: Koordinator Pengawas Proyek, Branch Manager"
                      v-model="sig.role"
                      class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Actions -->
          <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-5 py-2 bg-purple-900 hover:bg-purple-800 text-white font-bold rounded-xl shadow-xs active:scale-95 transition-all cursor-pointer disabled:opacity-50"
            >
              {{ saving ? 'Menyimpan...' : isEditing ? 'Simpan Perubahan Template' : 'Terbitkan Template Baru' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Live Preview Modal -->
    <div v-if="showPreviewModal && previewTmpl" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="glass-modal rounded-3xl max-w-4xl w-full shadow-2xl p-6 space-y-4 text-xs border border-white/80 max-h-[92vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
          <h3 class="font-black text-sm text-slate-900 flex items-center gap-2">
            <Eye class="w-4 h-4 text-purple-700" />
            <span>Simulasi Cetak Lembar Berita Acara ({{ previewTmpl.name }})</span>
          </h3>
          <button @click="showPreviewModal = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Simulated Document Body with Background Paper Template -->
        <div
          class="relative bg-white border border-slate-300 rounded-2xl p-8 sm:p-12 shadow-md space-y-6 text-slate-800 text-xs overflow-hidden min-h-[750px]"
          :style="getPreviewBgStyle(previewTmpl)"
        >
          <!-- Kop Surat Banner or Text Header -->
          <div v-if="!previewTmpl.background_image_url" class="flex items-center justify-between border-b-2 border-slate-900 pb-4 mb-4">
            <div class="flex items-center gap-3">
              <div v-if="previewTmpl.logo_url" class="w-14 h-14 rounded-lg border bg-white p-1 shadow-xs">
                <img :src="previewTmpl.logo_url" alt="Logo" class="w-full h-full object-contain" />
              </div>
              <div>
                <h2 class="font-black text-base text-slate-900 tracking-tight">PT SINAR GRAHA KREATIF</h2>
                <p class="text-[10px] text-slate-500">Digital Evidence & Vendor Work Management System</p>
              </div>
            </div>
            <div class="text-right text-[10px] text-slate-500 font-mono">
              <div>KODE DOKUMEN: {{ previewTmpl.code }}</div>
              <div>STATUS: TERVERIFIKASI RESMI</div>
            </div>
          </div>
          <div v-else class="h-16">
            <!-- Spacer to account for letterhead background header -->
          </div>

          <!-- Document Title -->
          <div class="text-center space-y-1">
            <h3 class="font-black text-base uppercase tracking-wide text-slate-900">
              BERITA ACARA SERAH TERIMA & OPNAME PEKERJAAN
            </h3>
            <p class="text-xs font-mono font-bold text-slate-600">Nomor: BA/SGX/2026/08/00125</p>
          </div>

          <!-- Opening Statement Formatted -->
          <div class="leading-relaxed text-slate-700" v-html="renderClauseWithSampleData(previewTmpl.header_html)"></div>

          <!-- Simulated Table -->
          <table class="w-full border border-slate-300 rounded-xl overflow-hidden text-xs bg-white/90 backdrop-blur-xs shadow-xs">
            <tbody>
              <tr class="border-b bg-slate-50/80">
                <td class="w-1/3 py-2 px-3 font-semibold text-slate-600">Nomor SPK</td>
                <td class="py-2 px-3 font-bold font-mono text-purple-900">SPK-2026-00125</td>
              </tr>
              <tr class="border-b">
                <td class="py-2 px-3 font-semibold text-slate-600">Nama Proyek Cabang</td>
                <td class="py-2 px-3 font-bold text-slate-900">Pemasangan Palang Merek & Signboard KCP Sukajadi</td>
              </tr>
              <tr class="border-b bg-slate-50/80">
                <td class="py-2 px-3 font-semibold text-slate-600">Mitra Vendor Pelaksana</td>
                <td class="py-2 px-3">PT Sinar Graha Kreatif (VND-001)</td>
              </tr>
              <tr class="border-b">
                <td class="py-2 px-3 font-semibold text-slate-600">Nilai Kontrak Pekerjaan</td>
                <td class="py-2 px-3 font-mono font-bold text-emerald-800">Rp 15.000.000</td>
              </tr>
              <tr>
                <td class="py-2 px-3 font-semibold text-slate-600">Lokasi & GPS Check-in</td>
                <td class="py-2 px-3 font-mono text-[11px]">-6.88500, 107.61360 (Akurasi ±12m)</td>
              </tr>
            </tbody>
          </table>

          <!-- Body Clause Formatted -->
          <div class="leading-relaxed text-slate-700 space-y-2" v-html="renderClauseWithSampleData(previewTmpl.body_template)"></div>

          <!-- Dynamic Signatures Grid -->
          <div
            class="grid gap-6 pt-8 text-center text-xs"
            :class="getSignatoryGridClass(getSignatoriesList(previewTmpl).length)"
          >
            <div
              v-for="(sig, sIdx) in getSignatoriesList(previewTmpl)"
              :key="sIdx"
              class="space-y-12"
            >
              <div>
                <p class="text-slate-500 font-medium text-[11px]">{{ sig.party_title || `Pihak #${sIdx + 1}` }}</p>
                <strong class="text-slate-800 text-xs block truncate">{{ sig.company_name }}</strong>
              </div>
              <div class="border-t border-slate-500 pt-1.5 mx-2">
                <div class="font-bold text-slate-900">{{ sig.name || '........................' }}</div>
                <div class="text-[10px] text-slate-500">{{ sig.role || 'Jabatan Resmi' }}</div>
              </div>
            </div>
          </div>

          <!-- Footer Image if exists -->
          <div v-if="previewTmpl.footer_image_url" class="rounded-xl overflow-hidden my-4 border border-slate-200">
            <img :src="previewTmpl.footer_image_url" alt="Footer Banner" class="w-full h-auto object-contain" />
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <button
            @click="showPreviewModal = false"
            class="px-5 py-2 bg-slate-900 text-white font-bold rounded-xl cursor-pointer active:scale-95 transition-all"
          >
            Tutup Pratinjau
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../../services/api';
import {
  FileCode,
  Plus,
  Pencil,
  Trash2,
  Eye,
  CheckCircle2,
  ImageIcon,
  UserCheck,
  Sparkles,
  X
} from 'lucide-vue-next';

const templates = ref([]);
const loading = ref(true);
const saving = ref(false);

const showModal = ref(false);
const isEditing = ref(false);
const modalTab = ref('branding');
const selectedPresetKey = ref('standard');

const headerTextarea = ref(null);
const bodyTextarea = ref(null);

const formData = ref({
  id: null,
  name: '',
  code: '',
  logo_url: '',
  background_image_url: '',
  header_image_url: '',
  footer_image_url: '',
  header_html: '',
  body_template: ''
});

const signatoriesList = ref([
  {
    party_title: 'Pihak Pertama (Pemberi Tugas / Pengawas)',
    company_name: 'PT SINAR GRAHA KREATIF',
    name: 'Dian Anggraini',
    role: 'Koordinator Pengawas Proyek'
  },
  {
    party_title: 'Pihak Kedua (Pelaksana)',
    company_name: 'MITRA VENDOR PELAKSANA',
    name: 'Andi Pratama',
    role: 'Project Manager Mitra Vendor'
  }
]);

const logoFile = ref(null);
const bgFile = ref(null);
const footerFile = ref(null);

const logoPreview = ref(null);
const bgPreview = ref(null);
const footerPreview = ref(null);

const showPreviewModal = ref(false);
const previewTmpl = ref(null);

// Preset Clauses Definitions
const clausePresets = {
  standard: {
    name: 'Standar Serah Terima SGX (Garansi 90 Hari)',
    header: 'Pada hari ini <strong>{{ba_date}}</strong>, telah dilakukan pemeriksaan dan verifikasi lapangan atas pelaksanaan seluruh item pekerjaan untuk <strong>{{title}}</strong> di lokasi <strong>{{location_name}}</strong> dengan rincian sebagai berikut:',
    body: 'Berdasarkan hasil pemeriksaan bukti foto digital (Before, Process, After) dan verifikasi teknis di lapangan, kedua belah pihak menyatakan bahwa seluruh item pekerjaan telah <strong>SELESAI 100% SECARA BAIK DAN MEMENUHI SPESIFIKASI MUTU</strong>.<br><br>Pihak Kontraktor Pelaksana (SGX) memberikan jaminan masa pemeliharaan (garansi mutu) kepada Pihak Client selama <strong>90 (sembilan puluh) hari kalender</strong> terhitung sejak tanggal penandatanganan Berita Acara ini.'
  },
  facade: {
    name: 'Branding Fasade & Signboard (Garansi 180 Hari)',
    header: 'Pada hari ini <strong>{{ba_date}}</strong>, telah dilaksanakan uji serah terima fisik pekerjaan Fabrikasi & Pemasangan Fasade Signboard <strong>{{title}}</strong> pada lokasi <strong>{{location_name}}</strong>:',
    body: 'Kedua belah pihak telah memeriksa seluruh aspek struktur tiang, panel ACP, pencahayaan modul LED, dan ketahanan cat. Seluruh item dinyatakan <strong>LULUS UJI KUALITAS & MEMENUHI STANDAR BRANDING</strong>.<br><br>Pihak Kontraktor Pelaksana (SGX) memberikan garansi struktural dan kelistrikan kepada Pihak Client selama <strong>180 (seratus delapan puluh) hari kalender</strong>.'
  },
  maintenance: {
    name: 'Maintenance Panel Listrik & ATM (Garansi 60 Hari)',
    header: 'Pada hari ini <strong>{{ba_date}}</strong>, telah selesai dilaksanakan pekerjaan pemeliharaan berkala kelistrikan dan panel untuk <strong>{{title}}</strong>:',
    body: 'Semua komponen yang diganti telah diuji coba tegangan beban kerja dan berfungsi normal tanpa anomali. Pekerjaan dinyatakan <strong>SELESAI 100% OPERASIONAL</strong> dengan garansi pemeliharaan selama <strong>60 (enam puluh) hari kalender</strong>.'
  },
  partial: {
    name: 'Serah Terima Bertahap / Parsial (Progress Opname)',
    header: 'Pada hari ini <strong>{{ba_date}}</strong>, telah dilakukan opname pengukuran kemajuan fisik pekerjaan lapangan untuk SPK <strong>{{spk_number}}</strong>:',
    body: 'Berdasarkan dokumentasi evidensi GPS dan pemeriksaan bersama, progres pekerjaan yang telah diselesaikan mencapai bobot persentase yang disepakati dan dinyatakan <strong>DAPAT DITERIMA UNTUK PROSES TERMIN PEMBAYARAN BERJALAN</strong>.'
  }
};

function applyPresetClause() {
  const preset = clausePresets[selectedPresetKey.value];
  if (!preset) return;
  formData.value.header_html = preset.header;
  formData.value.body_template = preset.body;
}

function wrapText(field, openTag, closeTag) {
  const currentVal = formData.value[field] || '';
  formData.value[field] = `${currentVal} ${openTag}Teks Tebal${closeTag} `;
}

function insertVariable(field, varTag) {
  const currentVal = formData.value[field] || '';
  formData.value[field] = `${currentVal} ${varTag} `;
}

function insertBulletPoint() {
  const currentVal = formData.value.body_template || '';
  formData.value.body_template = `${currentVal}\n• Poin Kesepakatan: `;
}

function stripHtml(html) {
  if (!html) return '';
  return html.replace(/<[^>]*>?/gm, ' ');
}

function renderClauseWithSampleData(text) {
  if (!text) return '-';
  return text
    .replace(/\{\{spk_number\}\}/g, '<strong>SPK-2026-00125</strong>')
    .replace(/\{\{title\}\}/g, '<strong>Pemasangan Palang Merek KCP Sukajadi</strong>')
    .replace(/\{\{vendor_name\}\}/g, '<strong>PT Sinar Graha Kreatif</strong>')
    .replace(/\{\{location_name\}\}/g, '<strong>Jl. Ir. H. Juanda No. 120 Bandung</strong>')
    .replace(/\{\{contract_value\}\}/g, '<strong>Rp 15.000.000</strong>')
    .replace(/\{\{ba_date\}\}/g, '<strong>Minggu, 16 Agustus 2026</strong>')
    .replace(/\{\{checkin_gps\}\}/g, '<strong>-6.88500, 107.61360</strong>')
    .replace(/\n/g, '<br>');
}

function getSignatoriesList(tmpl) {
  if (!tmpl) return [];
  if (tmpl.signatories_json) {
    try {
      const parsed = typeof tmpl.signatories_json === 'string' ? JSON.parse(tmpl.signatories_json) : tmpl.signatories_json;
      if (Array.isArray(parsed) && parsed.length > 0) return parsed;
    } catch (e) {}
  }
  return [
    {
      party_title: 'Pihak Pertama (Pemberi Tugas)',
      company_name: 'PT SINAR GRAHA KREATIF',
      name: tmpl.signatory_first_party_name || 'Dian Anggraini',
      role: tmpl.signatory_first_party_role || 'Koordinator Pengawas Proyek'
    },
    {
      party_title: 'Pihak Kedua (Pelaksana)',
      company_name: 'MITRA VENDOR PELAKSANA',
      name: tmpl.signatory_second_party_name || 'Andi Pratama',
      role: tmpl.signatory_second_party_role || 'Project Manager Mitra Vendor'
    }
  ];
}

function getSignatoryGridClass(count) {
  if (count <= 2) return 'grid-cols-2';
  if (count === 3) return 'grid-cols-3';
  if (count === 4) return 'grid-cols-2 sm:grid-cols-4';
  return 'grid-cols-2 sm:grid-cols-3';
}

function getPreviewBgStyle(tmpl) {
  const bgUrl = tmpl.background_image_url || tmpl.header_image_url;
  if (!bgUrl) return {};
  return {
    backgroundImage: `url(${bgUrl})`,
    backgroundSize: '100% 100%',
    backgroundRepeat: 'no-repeat',
    backgroundPosition: 'center'
  };
}

async function loadTemplates() {
  loading.value = true;
  try {
    const res = await api.getTemplates();
    templates.value = res.data || [];
  } catch (err) {
    console.error('Failed to load templates:', err);
  } finally {
    loading.value = false;
  }
}

function openAddModal() {
  isEditing.value = false;
  modalTab.value = 'branding';
  formData.value = {
    id: null,
    name: '',
    code: `TMPL-BA-${Date.now().toString().slice(-4)}`,
    logo_url: '',
    background_image_url: '',
    header_image_url: '',
    footer_image_url: '',
    header_html: clausePresets.standard.header,
    body_template: clausePresets.standard.body
  };
  signatoriesList.value = [
    {
      party_title: 'Pihak Pertama (Kontraktor Pelaksana)',
      company_name: 'PT SINAR GRAHA KREATIF',
      name: 'Dian Anggraini',
      role: 'Project Operations Manager'
    },
    {
      party_title: 'Pihak Kedua (Client / Pemberi Tugas)',
      company_name: 'PT INDOMARCO PRISMATAMA',
      name: 'Bapak Reza',
      role: 'Building Maintenance Manager (Client)'
    }
  ];
  logoFile.value = null;
  bgFile.value = null;
  footerFile.value = null;
  logoPreview.value = null;
  bgPreview.value = null;
  footerPreview.value = null;
  showModal.value = true;
}

function openEditModal(tmpl) {
  isEditing.value = true;
  modalTab.value = 'branding';
  formData.value = { ...tmpl };
  signatoriesList.value = getSignatoriesList(tmpl);
  logoFile.value = null;
  bgFile.value = null;
  footerFile.value = null;
  logoPreview.value = null;
  bgPreview.value = null;
  footerPreview.value = null;
  showModal.value = true;
}

function addSignatoryColumn() {
  const nextNum = signatoriesList.value.length + 1;
  signatoriesList.value.push({
    party_title: `Pihak #${nextNum} (Mengetahui / Penyetuju)`,
    company_name: '',
    name: '',
    role: ''
  });
}

function removeSignatoryColumn(index) {
  if (signatoriesList.value.length > 1) {
    signatoriesList.value.splice(index, 1);
  }
}

function handleLogoChange(e) {
  const file = e.target.files[0];
  if (file) {
    logoFile.value = file;
    logoPreview.value = URL.createObjectURL(file);
  }
}

function removeLogo() {
  logoFile.value = null;
  logoPreview.value = null;
  formData.value.logo_url = '';
}

function handleBackgroundChange(e) {
  const file = e.target.files[0];
  if (file) {
    bgFile.value = file;
    bgPreview.value = URL.createObjectURL(file);
  }
}

function removeBackground() {
  bgFile.value = null;
  bgPreview.value = null;
  formData.value.background_image_url = '';
  formData.value.header_image_url = '';
}

function handleFooterChange(e) {
  const file = e.target.files[0];
  if (file) {
    footerFile.value = file;
    footerPreview.value = URL.createObjectURL(file);
  }
}

function removeFooter() {
  footerFile.value = null;
  footerPreview.value = null;
  formData.value.footer_image_url = '';
}

async function handleSubmit() {
  saving.value = true;
  try {
    const payload = new FormData();
    payload.append('name', formData.value.name);
    payload.append('code', formData.value.code);
    payload.append('header_html', formData.value.header_html || '');
    payload.append('body_template', formData.value.body_template || '');
    payload.append('signatories_json', JSON.stringify(signatoriesList.value));

    // Compatibility with legacy single signatory columns
    if (signatoriesList.value.length > 0) {
      payload.append('signatory_first_party_name', signatoriesList.value[0]?.name || '');
      payload.append('signatory_first_party_role', signatoriesList.value[0]?.role || '');
    }
    if (signatoriesList.value.length > 1) {
      payload.append('signatory_second_party_name', signatoriesList.value[1]?.name || '');
      payload.append('signatory_second_party_role', signatoriesList.value[1]?.role || '');
    }

    // Logo handling
    if (logoFile.value) {
      payload.append('logo', logoFile.value);
    } else if (!formData.value.logo_url) {
      payload.append('remove_logo', '1');
      payload.append('logo_url', '');
    }

    // Background handling
    if (bgFile.value) {
      payload.append('background_image', bgFile.value);
    } else if (!formData.value.background_image_url) {
      payload.append('remove_background', '1');
      payload.append('background_image_url', '');
    }

    // Footer handling
    if (footerFile.value) {
      payload.append('footer_image', footerFile.value);
    } else if (!formData.value.footer_image_url) {
      payload.append('remove_footer', '1');
      payload.append('footer_image_url', '');
    }

    if (isEditing.value) {
      await api.updateTemplate(formData.value.id, payload);
      alert('Template dokumen BA berhasil diperbarui!');
    } else {
      await api.createTemplate(payload);
      alert('Template dokumen BA baru berhasil dibuat!');
    }

    showModal.value = false;
    loadTemplates();
  } catch (err) {
    alert(`Gagal menyimpan template: ${err.message}`);
  } finally {
    saving.value = false;
  }
}

async function handleSetDefault(tmpl) {
  try {
    await api.setDefaultTemplate(tmpl.id);
    alert(`Template "${tmpl.name}" telah diatur sebagai Template Utama (Default)!`);
    loadTemplates();
  } catch (err) {
    alert(`Gagal set default: ${err.message}`);
  }
}

async function handleDelete(tmpl) {
  const confirmed = window.confirm(`Apakah Anda yakin ingin menghapus template "${tmpl.name}"?`);
  if (!confirmed) return;

  try {
    await api.deleteTemplate(tmpl.id);
    alert('Template berhasil dihapus!');
    loadTemplates();
  } catch (err) {
    alert(`Gagal menghapus template: ${err.message}`);
  }
}

function openPreview(tmpl) {
  previewTmpl.value = tmpl;
  showPreviewModal.value = true;
}

onMounted(() => {
  loadTemplates();
});
</script>
