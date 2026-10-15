<template>
  <div class="space-y-6 text-slate-100 font-sans">
    <!-- Header -->
    <div class="space-y-2">
      <!-- Breadcrumb -->
      <div class="flex items-center gap-2 text-[10px] font-mono text-slate-400 uppercase tracking-wider">
        <span class="hover:text-slate-200 transition-colors">PORTAL</span>
        <span class="text-slate-600">//</span>
        <span class="hover:text-slate-200 transition-colors">KONTROL SISTEM</span>
        <span class="text-slate-600">//</span>
        <span class="text-[#FACC15] font-bold">TEMPLATE BA OPNAME</span>
      </div>

      <!-- Title Header & Actions -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
        <div>
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xs bg-[#1C2028] border border-[#FACC15] text-[#FACC15] flex items-center justify-center font-mono">
              <FileCode class="w-4 h-4 text-[#FACC15]" />
            </div>
            <h2 class="text-lg sm:text-xl font-black text-white tracking-wider font-mono uppercase">TEMPLATE DOKUMEN BA OPNAME</h2>
          </div>
          <p class="text-[11px] text-slate-400 mt-1 font-mono">
            Kelola background template kertas kop surat resmi, logo, klausul serah terima visual, dan pengaturan kolom tanda tangan dinamis.
          </p>
        </div>

        <button
          @click="openAddModal"
          class="px-4 py-2 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] rounded-xs text-xs font-bold font-mono uppercase tracking-wider flex items-center gap-2 border border-[#FACC15] shadow-xs active:scale-95 transition-all self-start sm:self-auto cursor-pointer"
        >
          <Plus class="w-4 h-4 stroke-[3]" />
          <span>TAMBAH TEMPLATE BARU</span>
        </button>
      </div>
    </div>

    <!-- Templates Grid Cards (Industrial CAD) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="tmpl in templates"
        :key="tmpl.id"
        class="bg-[#13171F] rounded-xs p-5 border border-[#232A36] shadow-md space-y-4 relative flex flex-col justify-between hover:border-[#FACC15]/40 transition-colors group font-mono"
      >
        <div class="space-y-3">
          <!-- Background / Header Preview Container -->
          <div class="h-32 rounded-xs bg-[#0E1117] border border-[#232A36] overflow-hidden relative flex items-center justify-center">
            <img
              v-if="tmpl.background_image_url || tmpl.header_image_url"
              :src="tmpl.background_image_url || tmpl.header_image_url"
              alt="Background Template"
              class="w-full h-full object-cover"
            />
            <div v-else class="flex flex-col items-center gap-1.5 text-slate-500">
              <ImageIcon class="w-6 h-6 opacity-40 text-slate-500" />
              <span class="text-[10px] uppercase tracking-wider">Background Polos (Standar)</span>
            </div>

            <!-- Logo Floating Badge -->
            <div
              v-if="tmpl.logo_url"
              class="absolute top-2 left-2 w-10 h-10 rounded-xs bg-[#0B0C0E] p-1 border border-[#232A36] overflow-hidden flex items-center justify-center"
            >
              <img :src="tmpl.logo_url" alt="Logo" class="w-full h-full object-contain" />
            </div>

            <!-- Default Badge -->
            <div v-if="tmpl.is_default" class="absolute top-2 right-2">
              <span class="px-2 py-0.5 rounded-xs text-[9px] font-bold bg-[#FACC15] text-[#0B0C0E] border border-[#FACC15] flex items-center gap-1 uppercase tracking-wider">
                <CheckCircle2 class="w-3 h-3 stroke-[2.5]" /> TEMPLATE UTAMA
              </span>
            </div>

            <div v-if="tmpl.background_image_url" class="absolute bottom-2 left-2">
              <span class="px-2 py-0.5 rounded-xs text-[8px] font-bold bg-[#0B0C0E]/90 text-white border border-[#232A36] flex items-center gap-1 uppercase tracking-wider">
                Kop Surat A4 Terpasang
              </span>
            </div>
          </div>

          <!-- Template Info -->
          <div>
            <div class="flex items-center justify-between">
              <h3 class="font-bold text-sm text-white group-hover:text-[#FACC15] transition-colors uppercase tracking-wider">
                {{ tmpl.name }}
              </h3>
              <span class="text-[10px] font-mono text-[#FACC15] bg-[#0E1117] px-2 py-0.5 rounded-xs border border-[#232A36]">
                {{ tmpl.code }}
              </span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1 line-clamp-2 font-sans" v-html="stripHtml(tmpl.body_template)"></div>
          </div>

          <!-- Signatory Details Summary -->
          <div class="p-3 bg-[#0E1117] rounded-xs border border-[#232A36] text-[11px] space-y-1.5 font-mono">
            <div class="font-bold text-slate-300 text-[10px] uppercase tracking-wider flex items-center justify-between">
              <span>Kolom Tanda Tangan:</span>
              <span class="px-1.5 py-0.2 rounded-xs bg-[#1C2028] text-[#FACC15] text-[9px] border border-[#232A36]">
                {{ getSignatoriesList(tmpl).length }} Pihak
              </span>
            </div>
            <div v-for="(sig, sIdx) in getSignatoriesList(tmpl).slice(0, 3)" :key="sIdx" class="flex items-center justify-between text-slate-400 text-[10px]">
              <span class="truncate max-w-[110px] text-slate-500">{{ sig.party_title || `Pihak #${sIdx+1}` }}:</span>
              <strong class="text-white truncate max-w-[130px]">{{ sig.name }}</strong>
            </div>
            <div v-if="getSignatoriesList(tmpl).length > 3" class="text-[9px] text-[#FACC15] font-bold text-right">
              + {{ getSignatoriesList(tmpl).length - 3 }} pihak lainnya
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-3 border-t border-[#232A36] flex items-center justify-between gap-2 text-xs font-mono">
          <button
            @click="openPreview(tmpl)"
            class="px-3 py-1.5 bg-[#1C2028] hover:bg-[#232A36] text-slate-300 hover:text-white font-bold rounded-xs flex items-center gap-1.5 border border-[#232A36] hover:border-[#FACC15]/40 transition-colors cursor-pointer uppercase tracking-wider text-[11px]"
          >
            <Eye class="w-3.5 h-3.5 text-[#FACC15]" />
            <span>Pratinjau</span>
          </button>

          <div class="flex items-center gap-1.5">
            <button
              v-if="!tmpl.is_default"
              @click="handleSetDefault(tmpl)"
              title="Jadikan Default"
              class="px-2.5 py-1.5 bg-emerald-950/40 hover:bg-emerald-900/60 text-emerald-300 font-bold rounded-xs border border-emerald-500/40 transition-colors cursor-pointer text-[10px] uppercase tracking-wider"
            >
              Set Default
            </button>
            <button
              @click="openEditModal(tmpl)"
              title="Edit Template & Branding"
              class="p-1.5 rounded-xs bg-[#1C2028] hover:bg-[#232A36] text-slate-300 hover:text-[#FACC15] border border-[#232A36] hover:border-[#FACC15]/40 transition-colors cursor-pointer"
            >
              <Pencil class="w-3.5 h-3.5" />
            </button>
            <button
              v-if="!tmpl.is_default"
              @click="handleDelete(tmpl)"
              title="Hapus Template"
              class="p-1.5 rounded-xs bg-[#1C2028] hover:bg-rose-950/40 text-slate-300 hover:text-rose-400 border border-[#232A36] hover:border-rose-500/40 transition-colors cursor-pointer"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create / Edit Modal (Industrial CAD) -->
    <div v-if="showModal" class="fixed inset-0 z-50 bg-[#0B0C0E]/80 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs max-w-3xl w-full shadow-2xl p-6 space-y-4 text-xs font-mono text-white max-h-[92vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-center justify-between border-b border-[#232A36] pb-3">
          <h3 class="font-bold text-sm text-white uppercase tracking-wider flex items-center gap-2">
            <FileCode class="w-4 h-4 text-[#FACC15]" />
            <span>{{ isEditing ? 'Edit Template Dokumen BA' : 'Tambah Template Dokumen BA Baru' }}</span>
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-white cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Form Tabs (Industrial CAD Segmented) -->
        <div class="flex flex-wrap gap-1 p-1 bg-[#0E1117] border border-[#232A36] rounded-xs font-mono text-xs">
          <button
            type="button"
            @click="modalTab = 'branding'"
            :class="[
              'px-3 py-1.5 rounded-xs transition-colors cursor-pointer uppercase tracking-wider',
              modalTab === 'branding' ? 'bg-[#1C2028] text-[#FACC15] border-l-[3px] border-[#FACC15] font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#1A202C]/60 border-l-[3px] border-transparent'
            ]"
          >
            1. Background & Logo
          </button>
          <button
            type="button"
            @click="modalTab = 'content'"
            :class="[
              'px-3 py-1.5 rounded-xs transition-colors cursor-pointer flex items-center gap-1.5 uppercase tracking-wider',
              modalTab === 'content' ? 'bg-[#1C2028] text-[#FACC15] border-l-[3px] border-[#FACC15] font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#1A202C]/60 border-l-[3px] border-transparent'
            ]"
          >
            <span>2. Klausul & Pernyataan</span>
          </button>
          <button
            type="button"
            @click="modalTab = 'table'"
            :class="[
              'px-3 py-1.5 rounded-xs transition-colors cursor-pointer flex items-center gap-1.5 uppercase tracking-wider',
              modalTab === 'table' ? 'bg-[#1C2028] text-[#FACC15] border-l-[3px] border-[#FACC15] font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#1A202C]/60 border-l-[3px] border-transparent'
            ]"
          >
            <Table class="w-3.5 h-3.5" />
            <span>3. Tabel Data</span>
            <span v-if="tableConfig.enabled" class="px-1.5 py-0.2 rounded-xs text-[9px] bg-emerald-950/60 text-emerald-400 border border-emerald-500/40 font-bold">
              {{ tableConfig.rows.length }} Baris
            </span>
            <span v-else class="px-1.5 py-0.2 rounded-xs text-[9px] bg-slate-800 text-slate-500">Off</span>
          </button>
          <button
            type="button"
            @click="modalTab = 'signatories'"
            :class="[
              'px-3 py-1.5 rounded-xs transition-colors cursor-pointer flex items-center gap-1.5 uppercase tracking-wider',
              modalTab === 'signatories' ? 'bg-[#1C2028] text-[#FACC15] border-l-[3px] border-[#FACC15] font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-[#1A202C]/60 border-l-[3px] border-transparent'
            ]"
          >
            <span>4. Tanda Tangan</span>
            <span class="px-1.5 py-0.2 rounded-xs text-[9px] bg-[#13171F] text-[#FACC15] border border-[#232A36]">
              {{ signatoriesList.length }}
            </span>
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4 font-mono">
          <!-- TAB 1: Background Template & Logo Upload -->
          <div v-show="modalTab === 'branding'" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">Nama Template *</label>
                <input
                  required
                  type="text"
                  placeholder="Contoh: Template Resmi BA SGX 2026"
                  v-model="formData.name"
                  class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white focus:outline-none font-bold text-xs"
                />
              </div>
              <div>
                <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">Kode Template *</label>
                <input
                  required
                  type="text"
                  placeholder="TMPL-BA-SGX-01"
                  v-model="formData.code"
                  class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white focus:outline-none font-mono text-xs"
                />
              </div>
            </div>

            <!-- Upload Background Template Kop Surat -->
            <div class="p-4 bg-[#0E1117] border-2 border-dashed border-[#232A36] rounded-xs space-y-2.5">
              <div class="flex items-start justify-between gap-2">
                <div>
                  <label class="block font-bold text-white text-xs flex items-center gap-1.5 uppercase tracking-wider">
                    <ImageIcon class="w-4 h-4 text-[#FACC15]" />
                    <span>Upload Background Kop Surat (Kertas Kop A4):</span>
                  </label>
                  <p class="text-[11px] text-slate-400 mt-0.5 font-sans">
                    Unggah file gambar background / kertas kop surat resmi (Rasio A4, Resolusi ideal: 1240 x 1754 px).
                    Gambar ini akan menjadi latar belakang penuh saat dokumen BA dicetak atau diekspor ke PDF.
                  </p>
                </div>
                <button
                  v-if="bgPreview || formData.background_image_url || formData.header_image_url"
                  type="button"
                  @click="removeBackground"
                  class="text-[10px] font-bold text-rose-400 hover:text-rose-300 bg-rose-950/40 px-2 py-1 rounded-xs border border-rose-500/40 cursor-pointer uppercase tracking-wider"
                >
                  Hapus Background
                </button>
              </div>

              <!-- Preview Background -->
              <div
                v-if="bgPreview || formData.background_image_url || formData.header_image_url"
                class="h-36 rounded-xs border border-[#232A36] overflow-hidden bg-[#0B0C0E] relative flex items-center justify-center"
              >
                <img
                  :src="bgPreview || formData.background_image_url || formData.header_image_url"
                  alt="Preview Background Template"
                  class="w-full h-full object-contain"
                />
                <span class="absolute bottom-2 right-2 px-2 py-0.5 bg-[#0B0C0E]/90 border border-[#232A36] text-white rounded-xs text-[9px] font-mono uppercase tracking-wider">
                  Background A4 Terpasang
                </span>
              </div>

              <input
                type="file"
                accept="image/*"
                @change="handleBackgroundChange"
                class="text-xs font-mono text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xs file:border file:border-[#232A36] file:text-xs file:font-bold file:bg-[#1C2028] file:text-slate-200 hover:file:bg-[#232A36] hover:file:text-white cursor-pointer"
              />
            </div>

            <!-- Upload Logo Perusahaan -->
            <div class="p-3.5 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-2">
              <div class="flex items-start justify-between gap-2">
                <label class="block font-bold text-white text-xs uppercase tracking-wider">Logo Resmi Perusahaan (PNG Transparan / JPEG):</label>
                <button
                  v-if="logoPreview || formData.logo_url"
                  type="button"
                  @click="removeLogo"
                  class="text-[10px] font-bold text-rose-400 hover:text-rose-300 bg-rose-950/40 px-2 py-1 rounded-xs border border-rose-500/40 cursor-pointer uppercase tracking-wider"
                >
                  Hapus Logo
                </button>
              </div>
              <div class="flex items-center gap-3">
                <div v-if="logoPreview || formData.logo_url" class="w-14 h-14 rounded-xs bg-[#0B0C0E] border border-[#232A36] p-1 flex items-center justify-center overflow-hidden">
                  <img :src="logoPreview || formData.logo_url" alt="Preview Logo" class="w-full h-full object-contain" />
                </div>
                <input
                  type="file"
                  accept="image/*"
                  @change="handleLogoChange"
                  class="text-xs font-mono text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xs file:border file:border-[#232A36] file:text-xs file:font-bold file:bg-[#1C2028] file:text-slate-200 hover:file:bg-[#232A36] hover:file:text-white cursor-pointer"
                />
              </div>
            </div>

            <!-- Upload Footer / Stempel -->
            <div class="p-3.5 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-2">
              <div class="flex items-start justify-between gap-2">
                <div>
                  <label class="block font-bold text-white text-xs uppercase tracking-wider">Gambar Footer / Catatan Bawah (Opsional):</label>
                  <p class="text-[11px] text-slate-400 font-sans">Banner grafis opsional untuk bagian bawah halaman dokumen (Ukuran ideal: 1200 x 120 px).</p>
                </div>
                <button
                  v-if="footerPreview || formData.footer_image_url"
                  type="button"
                  @click="removeFooter"
                  class="text-[10px] font-bold text-rose-400 hover:text-rose-300 bg-rose-950/40 px-2 py-1 rounded-xs border border-rose-500/40 cursor-pointer uppercase tracking-wider"
                >
                  Hapus Footer
                </button>
              </div>
              <div v-if="footerPreview || formData.footer_image_url" class="h-14 rounded-xs border border-[#232A36] overflow-hidden bg-[#0B0C0E]">
                <img :src="footerPreview || formData.footer_image_url" alt="Preview Footer" class="w-full h-full object-cover" />
              </div>
              <input
                type="file"
                accept="image/*"
                @change="handleFooterChange"
                class="text-xs font-mono text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xs file:border file:border-[#232A36] file:text-xs file:font-bold file:bg-[#1C2028] file:text-slate-200 hover:file:bg-[#232A36] hover:file:text-white cursor-pointer"
              />
            </div>
          </div>

          <!-- TAB 2: Content & Clauses (Industrial CAD Editor & Presets) -->
          <div v-show="modalTab === 'content'" class="space-y-4">
            <!-- Preset Clause Selector Bar -->
            <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
              <div>
                <span class="font-bold text-white text-xs flex items-center gap-1.5 uppercase tracking-wider">
                  <Sparkles class="w-4 h-4 text-[#FACC15]" />
                  <span>Pustaka Template Klausul & Format Isi Siap Pakai:</span>
                </span>
                <p class="text-[11px] text-slate-400 mt-0.5 font-sans">Pilih format kalimat resmi dan pasal garansi yang terstandarisasi untuk mempercepat pembuatan dokumen.</p>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <select
                  v-model="selectedPresetKey"
                  class="px-3 py-2 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs font-mono font-bold text-slate-200 cursor-pointer focus:outline-none"
                >
                  <option value="standard">Standar Serah Terima SGX (Garansi 90 Hari)</option>
                  <option value="facade">Branding Fasade & Signboard (Garansi 180 Hari)</option>
                  <option value="maintenance">Maintenance Panel Listrik (Garansi 60 Hari)</option>
                  <option value="partial">Serah Terima Bertahap / Opname Progress</option>
                  <option value="sticker">Penggantian Sticker & Acrylic NBL (Garansi 30 Hari)</option>
                  <option value="addendum">Berita Acara Addendum / Pekerjaan Tambahan</option>
                </select>
                <button
                  type="button"
                  @click="applyPresetClause"
                  class="px-4 py-2 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] rounded-xs text-xs font-bold transition-all cursor-pointer active:scale-95 flex items-center gap-1 uppercase tracking-wider"
                >
                  <span>Terapkan</span>
                </button>
              </div>
            </div>

            <!-- 1. Teks Pembuka Dokumen -->
            <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-3">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#232A36] pb-2.5">
                <div class="flex items-center gap-2">
                  <span class="w-5 h-5 rounded-xs bg-[#1C2028] border border-[#FACC15] text-[#FACC15] font-bold text-[10px] flex items-center justify-center font-mono">1</span>
                  <label class="font-bold text-white text-xs uppercase tracking-wider">Teks Pembuka Dokumen Berita Acara</label>
                </div>
                
                <!-- Rich Formatting Toolbar -->
                <div class="flex flex-wrap items-center gap-1 bg-[#13171F] p-1 rounded-xs border border-[#232A36]">
                  <button type="button" @click="smartWrapText('header_html', '<strong>', '</strong>', 'Teks Tebal', headerTextarea)" title="Tebal (Bold)" class="px-2 py-0.5 font-bold hover:bg-[#232A36] rounded-xs text-xs cursor-pointer text-white">B</button>
                  <button type="button" @click="smartWrapText('header_html', '<em>', '</em>', 'Teks Miring', headerTextarea)" title="Miring (Italic)" class="px-2 py-0.5 italic hover:bg-[#232A36] rounded-xs text-xs cursor-pointer text-white">I</button>
                  <button type="button" @click="smartWrapText('header_html', '<u>', '</u>', 'Garis Bawah', headerTextarea)" title="Garis Bawah (Underline)" class="px-2 py-0.5 underline hover:bg-[#232A36] rounded-xs text-xs cursor-pointer text-white">U</button>
                  <button type="button" @click="smartWrapText('header_html', '<mark class=\'bg-[#FACC15] text-[#0B0C0E] px-1 font-bold\'>', '</mark>', 'Sorotan Stabilo', headerTextarea)" title="Highlight Stabilo" class="px-2 py-0.5 bg-[#FACC15]/20 hover:bg-[#FACC15]/30 text-[#FACC15] font-bold rounded-xs text-xs cursor-pointer">Highlight</button>
                  <span class="w-px h-3.5 bg-[#232A36] mx-0.5"></span>
                  <button type="button" @click="resetToDefaultField('header_html')" title="Kembalikan ke Teks Standar" class="px-2 py-0.5 text-slate-400 hover:text-white hover:bg-[#232A36] rounded-xs text-[10px] cursor-pointer uppercase">Reset</button>
                </div>
              </div>

              <!-- Categorized Variable Insert Chips -->
              <div class="space-y-1.5 text-[11px] bg-[#13171F] p-2.5 rounded-xs border border-[#232A36]">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block font-mono">// Sisipkan Variabel Otomatis:</span>
                <div class="flex flex-wrap items-center gap-1.5 font-mono">
                  <button type="button" @click="insertVariableAtCursor('header_html', '{{ba_date}}', headerTextarea)" class="px-2.5 py-1 bg-[#1C2028] hover:bg-[#232A36] text-slate-200 font-bold text-[10px] rounded-xs border border-[#232A36] hover:border-[#FACC15]/40 transition-colors cursor-pointer">📅 Tanggal BA</button>
                  <button type="button" @click="insertVariableAtCursor('header_html', '{{title}}', headerTextarea)" class="px-2.5 py-1 bg-[#1C2028] hover:bg-[#232A36] text-slate-200 font-bold text-[10px] rounded-xs border border-[#232A36] hover:border-[#FACC15]/40 transition-colors cursor-pointer">🏷️ Nama Proyek</button>
                  <button type="button" @click="insertVariableAtCursor('header_html', '{{location_name}}', headerTextarea)" class="px-2.5 py-1 bg-[#1C2028] hover:bg-[#232A36] text-blue-300 font-bold text-[10px] rounded-xs border border-blue-500/30 transition-colors cursor-pointer">📍 Lokasi Cabang</button>
                  <button type="button" @click="insertVariableAtCursor('header_html', '{{vendor_name}}', headerTextarea)" class="px-2.5 py-1 bg-[#1C2028] hover:bg-[#232A36] text-emerald-300 font-bold text-[10px] rounded-xs border border-emerald-500/30 transition-colors cursor-pointer">🏢 Nama Client</button>
                  <button type="button" @click="insertVariableAtCursor('header_html', '{{spk_number}}', headerTextarea)" class="px-2.5 py-1 bg-[#1C2028] hover:bg-[#232A36] text-[#FACC15] font-mono font-bold text-[10px] rounded-xs border border-[#FACC15]/30 transition-colors cursor-pointer">📋 No. SPK</button>
                </div>
              </div>

              <textarea
                ref="headerTextarea"
                rows="3"
                v-model="formData.header_html"
                placeholder="Pada hari ini {{ba_date}}, telah dilakukan pemeriksaan dan verifikasi lapangan atas pelaksanaan seluruh item pekerjaan untuk {{title}} di lokasi {{location_name}}..."
                class="w-full px-3.5 py-2.5 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white focus:outline-none leading-relaxed text-xs font-mono"
              />

              <!-- Live Formatted Preview Strip -->
              <div class="p-3 bg-[#13171F] border border-[#232A36] rounded-xs text-[11px] text-slate-300 leading-relaxed font-sans">
                <div class="flex items-center justify-between mb-1">
                  <span class="font-bold text-[10px] uppercase tracking-wider text-[#FACC15] flex items-center gap-1 font-mono">
                    <CheckCircle2 class="w-3 h-3 text-emerald-400" />
                    <span>Pratinjau Tampilan Cetak Pembuka (Data Sampel):</span>
                  </span>
                </div>
                <div class="p-2.5 bg-[#0B0C0E] rounded-xs border border-[#232A36] text-slate-200" v-html="renderClauseWithSampleData(formData.header_html)"></div>
              </div>
            </div>

            <!-- 2. Klausul Pernyataan Selesai & Garansi Mutu -->
            <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-3">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#232A36] pb-2.5">
                <div class="flex items-center gap-2">
                  <span class="w-5 h-5 rounded-xs bg-[#1C2028] border border-[#FACC15] text-[#FACC15] font-bold text-[10px] flex items-center justify-center font-mono">2</span>
                  <label class="font-bold text-white text-xs uppercase tracking-wider">Klausul Pernyataan Selesai & Garansi Mutu</label>
                </div>
                
                <!-- Rich Formatting Toolbar -->
                <div class="flex flex-wrap items-center gap-1 bg-[#13171F] p-1 rounded-xs border border-[#232A36]">
                  <button type="button" @click="smartWrapText('body_template', '<strong>', '</strong>', 'Teks Tebal', bodyTextarea)" title="Tebal (Bold)" class="px-2 py-0.5 font-bold hover:bg-[#232A36] rounded-xs text-xs cursor-pointer text-white">B</button>
                  <button type="button" @click="smartWrapText('body_template', '<em>', '</em>', 'Teks Miring', bodyTextarea)" title="Miring (Italic)" class="px-2 py-0.5 italic hover:bg-[#232A36] rounded-xs text-xs cursor-pointer text-white">I</button>
                  <button type="button" @click="smartWrapText('body_template', '<u>', '</u>', 'Garis Bawah', bodyTextarea)" title="Garis Bawah (Underline)" class="px-2 py-0.5 underline hover:bg-[#232A36] rounded-xs text-xs cursor-pointer text-white">U</button>
                  <button type="button" @click="smartWrapText('body_template', '<mark class=\'bg-[#FACC15] text-[#0B0C0E] px-1 font-bold\'>', '</mark>', 'Sorotan Penting', bodyTextarea)" title="Highlight Stabilo" class="px-2 py-0.5 bg-[#FACC15]/20 hover:bg-[#FACC15]/30 text-[#FACC15] font-bold rounded-xs text-xs cursor-pointer">Highlight</button>
                  <span class="w-px h-3.5 bg-[#232A36] mx-0.5"></span>
                  <button type="button" @click="insertBulletPointCustom(bodyTextarea)" title="Poin Bullet" class="px-2 py-0.5 hover:bg-[#232A36] rounded-xs text-xs cursor-pointer font-bold text-white">• List</button>
                  <button type="button" @click="insertNumberedPointCustom(bodyTextarea)" title="Poin Penomoran" class="px-2 py-0.5 hover:bg-[#232A36] rounded-xs text-xs cursor-pointer font-bold text-white">1. List</button>
                  <button type="button" @click="insertParagraphBreak(bodyTextarea)" title="Paragraf Baru" class="px-2 py-0.5 hover:bg-[#232A36] rounded-xs text-xs cursor-pointer text-slate-400">¶ Paragraf</button>
                  <span class="w-px h-3.5 bg-[#232A36] mx-0.5"></span>
                  <button type="button" @click="resetToDefaultField('body_template')" title="Kembalikan ke Teks Standar" class="px-2 py-0.5 text-slate-400 hover:text-white hover:bg-[#232A36] rounded-xs text-[10px] cursor-pointer uppercase">Reset</button>
                </div>
              </div>

              <!-- Categorized Variable Insert Chips -->
              <div class="space-y-1.5 text-[11px] bg-[#13171F] p-2.5 rounded-xs border border-[#232A36]">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block font-mono">// Sisipkan Variabel Otomatis:</span>
                <div class="flex flex-wrap items-center gap-1.5 font-mono">
                  <button type="button" @click="insertVariableAtCursor('body_template', '{{vendor_name}}', bodyTextarea)" class="px-2.5 py-1 bg-[#1C2028] hover:bg-[#232A36] text-emerald-300 font-bold text-[10px] rounded-xs border border-emerald-500/30 transition-colors cursor-pointer">🏢 Nama Client</button>
                  <button type="button" @click="insertVariableAtCursor('body_template', '{{contract_value}}', bodyTextarea)" class="px-2.5 py-1 bg-[#1C2028] hover:bg-[#232A36] text-emerald-300 font-bold text-[10px] rounded-xs border border-emerald-500/30 transition-colors cursor-pointer">💰 Nilai Kontrak</button>
                  <button type="button" @click="insertVariableAtCursor('body_template', '{{spk_number}}', bodyTextarea)" class="px-2.5 py-1 bg-[#1C2028] hover:bg-[#232A36] text-[#FACC15] font-mono font-bold text-[10px] rounded-xs border border-[#FACC15]/30 transition-colors cursor-pointer">📋 No. SPK</button>
                  <button type="button" @click="insertVariableAtCursor('body_template', '{{checkin_gps}}', bodyTextarea)" class="px-2.5 py-1 bg-[#1C2028] hover:bg-[#232A36] text-blue-300 font-mono text-[10px] rounded-xs border border-blue-500/30 transition-colors cursor-pointer">🛰️ GPS Check-in</button>
                  <button type="button" @click="insertVariableAtCursor('body_template', '{{ba_date}}', bodyTextarea)" class="px-2.5 py-1 bg-[#1C2028] hover:bg-[#232A36] text-slate-200 font-bold text-[10px] rounded-xs border border-[#232A36] hover:border-[#FACC15]/40 transition-colors cursor-pointer">📅 Tanggal BA</button>
                </div>
              </div>

              <textarea
                ref="bodyTextarea"
                rows="5"
                v-model="formData.body_template"
                placeholder="Berdasarkan pemeriksaan bukti foto digital Before, Process, After dan hasil verifikasi lapangan, kedua belah pihak menyatakan bahwa seluruh item pekerjaan telah SELESAI 100% SECARA BAIK..."
                class="w-full px-3.5 py-2.5 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white focus:outline-none leading-relaxed text-xs font-mono"
              />

              <!-- Live Formatted Preview Strip -->
              <div class="p-3 bg-[#13171F] border border-[#232A36] rounded-xs text-[11px] text-slate-300 leading-relaxed font-sans">
                <div class="flex items-center justify-between mb-1">
                  <span class="font-bold text-[10px] uppercase tracking-wider text-[#FACC15] flex items-center gap-1 font-mono">
                    <CheckCircle2 class="w-3 h-3 text-emerald-400" />
                    <span>Pratinjau Tampilan Cetak Klausul (Data Sampel):</span>
                  </span>
                </div>
                <div class="p-2.5 bg-[#0B0C0E] rounded-xs border border-[#232A36] text-slate-200 leading-relaxed space-y-1" v-html="renderClauseWithSampleData(formData.body_template)"></div>
              </div>
            </div>
          </div>

          <!-- TAB 3: Visual Dynamic Table Builder (No-Code Table Grid) -->
          <div v-show="modalTab === 'table'" class="space-y-4">
            <!-- Header Controls & Toggle -->
            <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xs bg-[#1C2028] border border-[#232A36] text-[#FACC15] flex items-center justify-center font-mono">
                  <Table class="w-5 h-5 text-[#FACC15]" />
                </div>
                <div>
                  <h4 class="font-bold text-white text-xs flex items-center gap-2 uppercase tracking-wider">
                    <span>Penyusun Tabel Data SPK Otomatis</span>
                    <span class="px-2 py-0.5 rounded-xs text-[9px] font-bold" :class="tableConfig.enabled ? 'bg-emerald-950/60 text-emerald-400 border border-emerald-500/40' : 'bg-slate-800 text-slate-500'">
                      {{ tableConfig.enabled ? 'Aktif' : 'Dinonaktifkan' }}
                    </span>
                  </h4>
                  <p class="text-[11px] text-slate-400 mt-0.5 font-sans">
                    Buat dan susun baris data rincian SPK secara fleksibel tanpa perlu mengetik kode HTML.
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-2 shrink-0">
                <label class="flex items-center gap-2 cursor-pointer bg-[#13171F] px-3 py-1.5 rounded-xs border border-[#232A36]">
                  <input type="checkbox" v-model="tableConfig.enabled" class="rounded-xs accent-[#FACC15]" />
                  <span class="text-xs font-bold text-white uppercase tracking-wider">Tampilkan Tabel</span>
                </label>
                <button
                  type="button"
                  @click="resetTableRows"
                  class="px-2.5 py-1.5 bg-[#1C2028] hover:bg-[#232A36] text-slate-300 hover:text-white text-xs font-bold rounded-xs border border-[#232A36] cursor-pointer flex items-center gap-1 uppercase tracking-wider"
                  title="Kembalikan Susunan Baris Standar"
                >
                  <RotateCcw class="w-3.5 h-3.5 text-[#FACC15]" />
                  <span>Reset Baris</span>
                </button>
              </div>
            </div>

            <div v-if="tableConfig.enabled" class="space-y-4">
              <!-- Table Settings (Position & Style) -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 bg-[#0E1117] border border-[#232A36] rounded-xs text-xs font-mono">
                <div>
                  <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">Posisi Peletakan Tabel Dokumen:</label>
                  <select
                    v-model="tableConfig.position"
                    class="w-full px-3 py-1.5 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white font-mono focus:outline-none"
                  >
                    <option value="after_header">Di Bawah Kalimat Pembuka (Standar Resmi)</option>
                    <option value="after_body">Di Bawah Klausul Pernyataan Selesai</option>
                  </select>
                </div>
                <div>
                  <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">Gaya Tampilan Tabel:</label>
                  <select
                    v-model="tableConfig.style"
                    class="w-full px-3 py-1.5 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white font-mono focus:outline-none"
                  >
                    <option value="striped">Bergaris Selang-seling (Striped Dark)</option>
                    <option value="bordered">Border Penuh (Full Boxed)</option>
                    <option value="clean">Minimalis Bersih (Clean Divider)</option>
                  </select>
                </div>
              </div>

              <!-- Interactive Rows Builder -->
              <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-bold text-white uppercase tracking-wider">
                    Daftar Baris Rincian Data ({{ tableConfig.rows.length }} Baris):
                  </span>
                  <button
                    type="button"
                    @click="addTableRow"
                    class="px-3 py-1 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] rounded-xs text-xs font-bold flex items-center gap-1 cursor-pointer active:scale-95 uppercase tracking-wider"
                  >
                    <Plus class="w-3.5 h-3.5 stroke-[3]" />
                    <span>Tambah Baris</span>
                  </button>
                </div>

                <!-- Row Cards List -->
                <div class="space-y-2">
                  <div
                    v-for="(row, rIdx) in tableConfig.rows"
                    :key="rIdx"
                    class="p-3 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-2 hover:border-[#FACC15]/40 transition-colors font-mono"
                  >
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                      <!-- Row Number & Reorder -->
                      <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-xs bg-[#1C2028] border border-[#232A36] text-[#FACC15] font-mono text-[10px] font-bold flex items-center justify-center">
                          {{ rIdx + 1 }}
                        </span>
                        <div class="flex items-center gap-0.5">
                          <button
                            type="button"
                            :disabled="rIdx === 0"
                            @click="moveTableRow(rIdx, -1)"
                            class="p-1 text-slate-400 hover:text-white disabled:opacity-20 cursor-pointer"
                            title="Pindah ke Atas"
                          >
                            <ArrowUp class="w-3.5 h-3.5" />
                          </button>
                          <button
                            type="button"
                            :disabled="rIdx === tableConfig.rows.length - 1"
                            @click="moveTableRow(rIdx, 1)"
                            class="p-1 text-slate-400 hover:text-white disabled:opacity-20 cursor-pointer"
                            title="Pindah ke Bawah"
                          >
                            <ArrowDown class="w-3.5 h-3.5" />
                          </button>
                        </div>
                        <input
                          type="text"
                          v-model="row.label"
                          placeholder="Label Judul Baris"
                          class="px-2.5 py-1 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs font-bold text-white w-44 focus:outline-none"
                        />
                      </div>

                      <!-- Quick Presets Chips -->
                      <div class="flex flex-wrap items-center gap-1">
                        <span class="text-[10px] text-slate-500 mr-0.5 font-mono">Variabel:</span>
                        <button type="button" @click="setRowPresetVariable(row, '{{spk_number}}')" class="px-1.5 py-0.5 bg-[#1C2028] hover:bg-[#232A36] text-[#FACC15] rounded-xs text-[9px] font-mono border border-[#FACC15]/30 cursor-pointer">No SPK</button>
                        <button type="button" @click="setRowPresetVariable(row, '{{title}}')" class="px-1.5 py-0.5 bg-[#1C2028] hover:bg-[#232A36] text-slate-200 rounded-xs text-[9px] font-mono border border-[#232A36] cursor-pointer">Pekerjaan</button>
                        <button type="button" @click="setRowPresetVariable(row, '{{location_name}}')" class="px-1.5 py-0.5 bg-[#1C2028] hover:bg-[#232A36] text-blue-300 rounded-xs text-[9px] font-mono border border-blue-500/30 cursor-pointer">Lokasi</button>
                        <button type="button" @click="setRowPresetVariable(row, '{{contract_value}}')" class="px-1.5 py-0.5 bg-[#1C2028] hover:bg-[#232A36] text-emerald-300 rounded-xs text-[9px] font-mono border border-emerald-500/30 cursor-pointer">Nilai</button>
                        <button type="button" @click="setRowPresetVariable(row, '{{vendor_name}}')" class="px-1.5 py-0.5 bg-[#1C2028] hover:bg-[#232A36] text-slate-200 rounded-xs text-[9px] font-mono border border-[#232A36] cursor-pointer">Client</button>
                        <button type="button" @click="setRowPresetVariable(row, '{{checkin_gps}}')" class="px-1.5 py-0.5 bg-[#1C2028] hover:bg-[#232A36] text-amber-300 rounded-xs text-[9px] font-mono border border-amber-500/30 cursor-pointer">GPS</button>
                        <button
                          type="button"
                          @click="removeTableRow(rIdx)"
                          title="Hapus Baris Ini"
                          class="p-1 text-rose-400 hover:text-rose-300 hover:bg-rose-950/40 rounded-xs ml-1 cursor-pointer"
                        >
                          <Trash2 class="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </div>

                    <!-- Row Value & Formatting Options -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1 border-t border-[#232A36] text-xs">
                      <div class="sm:col-span-2">
                        <input
                          type="text"
                          v-model="row.value"
                          placeholder="Isi Nilai Baris / Variabel (cth: {{spk_number}})"
                          class="w-full px-2.5 py-1 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs font-mono text-white focus:outline-none"
                        />
                      </div>
                      <div class="flex items-center gap-2 justify-end">
                        <label class="flex items-center gap-1 cursor-pointer text-[11px] text-slate-300 font-bold uppercase">
                          <input type="checkbox" v-model="row.is_bold" class="rounded-xs accent-[#FACC15]" />
                          <span>Tebal</span>
                        </label>
                        <select
                          v-model="row.highlight"
                          class="px-2 py-0.5 bg-[#13171F] border border-[#232A36] rounded-xs text-[10px] font-bold text-slate-300 focus:outline-none uppercase"
                        >
                          <option value="normal">Warna Biasa</option>
                          <option value="purple">Kuning (SPK)</option>
                          <option value="emerald">Hijau (Rupiah)</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Live Formatted Table Preview Strip -->
              <div class="p-3.5 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-2">
                <span class="font-bold text-[10px] uppercase tracking-wider text-[#FACC15] flex items-center gap-1 font-mono">
                  <CheckCircle2 class="w-3.5 h-3.5 text-emerald-400" />
                  <span>Pratinjau Langsung Tampilan Cetak Tabel:</span>
                </span>
                <div class="p-3 bg-white rounded-xs border border-slate-300 text-slate-900">
                  <div v-html="renderTableHtmlFromConfig(tableConfig, true)"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 4: Dynamic Signatories Builder -->
          <div v-show="modalTab === 'signatories'" class="space-y-4 font-mono">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="font-bold text-white text-sm flex items-center gap-2 uppercase tracking-wider">
                  <UserCheck class="w-4 h-4 text-[#FACC15]" />
                  <span>Pengaturan Kolom Tanda Tangan Dokumen</span>
                </h4>
                <p class="text-[11px] text-slate-400 font-sans">
                  Tambahkan atau sesuaikan kolom tanda tangan (cth: Pihak Pertama, Pihak Kedua, Kepala Cabang, Mengetahui GM).
                </p>
              </div>
              <button
                type="button"
                @click="addSignatoryColumn"
                class="px-3 py-1.5 bg-[#1C2028] hover:bg-[#232A36] text-[#FACC15] border border-[#232A36] hover:border-[#FACC15]/40 font-bold rounded-xs flex items-center gap-1.5 text-xs transition-colors cursor-pointer uppercase tracking-wider"
              >
                <Plus class="w-3.5 h-3.5 stroke-[3]" />
                <span>+ Tambah Kolom</span>
              </button>
            </div>

            <!-- Signatory Column Cards List -->
            <div class="space-y-3">
              <div
                v-for="(sig, index) in signatoriesList"
                :key="index"
                class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-3 relative group"
              >
                <div class="flex items-center justify-between border-b border-[#232A36] pb-2">
                  <div class="flex items-center gap-2 font-bold text-white text-xs uppercase tracking-wider">
                    <span class="w-5 h-5 rounded-xs bg-[#1C2028] border border-[#FACC15] text-[#FACC15] flex items-center justify-center text-[10px]">
                      {{ index + 1 }}
                    </span>
                    <span>Kolom Tanda Tangan #{{ index + 1 }}</span>
                  </div>
                  <button
                    v-if="signatoriesList.length > 1"
                    type="button"
                    @click="removeSignatoryColumn(index)"
                    title="Hapus Kolom Tanda Tangan Ini"
                    class="text-rose-400 hover:text-rose-300 p-1 rounded-xs hover:bg-rose-950/40 cursor-pointer flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                    <span>Hapus Kolom</span>
                  </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                  <div>
                    <label class="block font-bold mb-0.5 text-[11px] text-slate-300 uppercase tracking-wider">Label Posisi / Pihak *</label>
                    <input
                      required
                      type="text"
                      placeholder="Contoh: Pihak Pertama (Pemberi Tugas)"
                      v-model="sig.party_title"
                      class="w-full px-3 py-1.5 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white focus:outline-none"
                    />
                  </div>
                  <div>
                    <label class="block font-bold mb-0.5 text-[11px] text-slate-300 uppercase tracking-wider">Nama Instansi / Perusahaan *</label>
                    <input
                      required
                      type="text"
                      placeholder="Contoh: PT SINAR GRAHA KREATIF"
                      v-model="sig.company_name"
                      class="w-full px-3 py-1.5 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white focus:outline-none font-bold"
                    />
                  </div>
                  <div>
                    <label class="block font-bold mb-0.5 text-[11px] text-slate-300 uppercase tracking-wider">Nama Pejabat Penandatangan *</label>
                    <input
                      required
                      type="text"
                      placeholder="Contoh: Dian Anggraini, S.T."
                      v-model="sig.name"
                      class="w-full px-3 py-1.5 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white focus:outline-none"
                    />
                  </div>
                  <div>
                    <label class="block font-bold mb-0.5 text-[11px] text-slate-300 uppercase tracking-wider">Jabatan Resmi *</label>
                    <input
                      required
                      type="text"
                      placeholder="Contoh: Koordinator Pengawas Proyek"
                      v-model="sig.role"
                      class="w-full px-3 py-1.5 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-white focus:outline-none"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Actions -->
          <div class="pt-3 border-t border-[#232A36] flex items-center justify-end gap-2 font-mono">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2 bg-[#1C2028] hover:bg-[#232A36] text-slate-300 hover:text-white font-bold rounded-xs cursor-pointer uppercase tracking-wider"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-5 py-2 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] font-bold rounded-xs shadow-xs active:scale-95 transition-all cursor-pointer disabled:opacity-50 uppercase tracking-wider"
            >
              {{ saving ? 'Menyimpan...' : isEditing ? 'Simpan Perubahan Template' : 'Terbitkan Template Baru' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Live Preview Modal (Industrial CAD with Printable Paper Rendering) -->
    <div v-if="showPreviewModal && previewTmpl" class="fixed inset-0 z-50 bg-[#0B0C0E]/80 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs max-w-4xl w-full shadow-2xl p-6 space-y-4 text-xs font-mono text-white max-h-[92vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-center justify-between border-b border-[#232A36] pb-3">
          <h3 class="font-bold text-sm text-white uppercase tracking-wider flex items-center gap-2">
            <Eye class="w-4 h-4 text-[#FACC15]" />
            <span>Simulasi Cetak Lembar Berita Acara ({{ previewTmpl.name }})</span>
          </h3>
          <button @click="showPreviewModal = false" class="text-slate-400 hover:text-white cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Simulated Document Body with Background Paper Template (Standard Printable Paper) -->
        <div
          class="relative bg-white border border-[#232A36] rounded-xs p-8 sm:p-12 shadow-md space-y-3.5 text-slate-800 text-xs overflow-hidden min-h-[750px]"
          :style="getPreviewBgStyle(previewTmpl)"
        >
          <!-- Kop Surat Banner or Text Header -->
          <div v-if="!previewTmpl.background_image_url" class="flex items-center justify-between border-b-2 border-slate-900 pb-4 mb-4">
            <div class="flex items-center gap-3">
              <div v-if="previewTmpl.logo_url" class="w-14 h-14 rounded-xs border bg-white p-1 shadow-xs">
                <img :src="previewTmpl.logo_url" alt="Logo" class="w-full h-full object-contain" />
              </div>
              <div>
                <h2 class="font-black text-base text-slate-900 tracking-tight">PT SINAR GRAHA KREATIF</h2>
                <p class="text-[10px] text-slate-500 font-mono">Digital Evidence & Vendor Work Management System</p>
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
            <h3 class="font-black text-base uppercase tracking-wide text-slate-900 font-heading">
              BERITA ACARA SERAH TERIMA & OPNAME PEKERJAAN
            </h3>
            <p class="text-xs font-mono font-bold text-slate-600">Nomor: BA/SGX/2026/08/00125</p>
          </div>

          <!-- Opening Statement Formatted -->
          <div class="leading-relaxed text-slate-700 mb-2 font-sans" v-html="renderClauseWithSampleData(previewTmpl.header_html)"></div>

          <!-- Dynamic Visual Table (Jika posisinya di bawah pembuka) -->
          <div v-if="getTableConfig(previewTmpl).enabled && getTableConfig(previewTmpl).position === 'after_header'" class="my-2" v-html="renderTableHtmlFromConfig(getTableConfig(previewTmpl), true)"></div>

          <!-- Body Clause Formatted -->
          <div class="leading-relaxed text-slate-700 my-2 space-y-1.5 font-sans" v-html="renderClauseWithSampleData(previewTmpl.body_template)"></div>

          <!-- Dynamic Visual Table (Jika posisinya di bawah klausul) -->
          <div v-if="getTableConfig(previewTmpl).enabled && getTableConfig(previewTmpl).position === 'after_body'" class="my-2" v-html="renderTableHtmlFromConfig(getTableConfig(previewTmpl), true)"></div>

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
                <p class="text-slate-500 font-medium text-[11px] font-mono uppercase">{{ sig.party_title || `Pihak #${sIdx + 1}` }}</p>
                <strong class="text-slate-800 text-xs block truncate">{{ sig.company_name }}</strong>
              </div>
              <div class="border-t border-slate-500 pt-1.5 mx-2">
                <div class="font-bold text-slate-900">{{ sig.name || '........................' }}</div>
                <div class="text-[10px] text-slate-500 font-mono">{{ sig.role || 'Jabatan Resmi' }}</div>
              </div>
            </div>
          </div>

          <!-- Footer Image if exists -->
          <div v-if="previewTmpl.footer_image_url" class="rounded-xs overflow-hidden my-4 border border-slate-200">
            <img :src="previewTmpl.footer_image_url" alt="Footer Banner" class="w-full h-auto object-contain" />
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <button
            @click="showPreviewModal = false"
            class="px-5 py-2 bg-[#1C2028] hover:bg-[#232A36] text-white border border-[#232A36] font-bold rounded-xs cursor-pointer active:scale-95 transition-all uppercase tracking-wider"
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
  Table,
  ArrowUp,
  ArrowDown,
  RotateCcw,
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

// Visual Dynamic Table Builder Configuration State
const defaultTableRows = [
  { label: 'Nomor SPK', value: '{{spk_number}}', custom_label: '', is_bold: true, highlight: 'purple' },
  { label: 'Nama Pekerjaan', value: '{{title}}', custom_label: '', is_bold: true, highlight: 'normal' },
  { label: 'Mitra Client', value: '{{vendor_name}}', custom_label: '', is_bold: false, highlight: 'normal' },
  { label: 'Lokasi / Cabang', value: '{{location_name}}', custom_label: '', is_bold: false, highlight: 'normal' },
  { label: 'Nilai Kontrak', value: '{{contract_value}}', custom_label: '', is_bold: true, highlight: 'emerald' },
  { label: 'GPS Check-in', value: '{{checkin_gps}}', custom_label: '', is_bold: false, highlight: 'normal' }
];

const tableConfig = ref({
  enabled: true,
  position: 'after_header', // 'after_header', 'after_body', 'none'
  style: 'striped', // 'striped', 'clean', 'bordered'
  rows: JSON.parse(JSON.stringify(defaultTableRows))
});

function addTableRow() {
  tableConfig.value.rows.push({
    label: 'Keterangan Tambahan',
    value: '',
    custom_label: '',
    is_bold: false,
    highlight: 'normal'
  });
}

function removeTableRow(index) {
  if (tableConfig.value.rows.length > 1) {
    tableConfig.value.rows.splice(index, 1);
  }
}

function moveTableRow(index, direction) {
  const targetIndex = index + direction;
  if (targetIndex < 0 || targetIndex >= tableConfig.value.rows.length) return;
  const temp = tableConfig.value.rows[index];
  tableConfig.value.rows[index] = tableConfig.value.rows[targetIndex];
  tableConfig.value.rows[targetIndex] = temp;
}

function resetTableRows() {
  tableConfig.value.rows = JSON.parse(JSON.stringify(defaultTableRows));
}

function setRowPresetVariable(row, varTag) {
  row.value = varTag;
  if (varTag === '{{spk_number}}') { row.label = 'Nomor SPK'; row.is_bold = true; row.highlight = 'purple'; }
  else if (varTag === '{{title}}') { row.label = 'Nama Pekerjaan'; row.is_bold = true; row.highlight = 'normal'; }
  else if (varTag === '{{vendor_name}}') { row.label = 'Perusahaan Client'; row.is_bold = false; row.highlight = 'normal'; }
  else if (varTag === '{{location_name}}') { row.label = 'Lokasi / Cabang'; row.is_bold = false; row.highlight = 'normal'; }
  else if (varTag === '{{contract_value}}') { row.label = 'Nilai Kontrak'; row.is_bold = true; row.highlight = 'emerald'; }
  else if (varTag === '{{ba_date}}') { row.label = 'Tanggal BA'; row.is_bold = false; row.highlight = 'normal'; }
  else if (varTag === '{{checkin_gps}}') { row.label = 'GPS Check-in'; row.is_bold = false; row.highlight = 'normal'; }
}

function renderTableHtmlFromConfig(config, isSample = false) {
  if (!config || !config.enabled || !config.rows || config.rows.length === 0) return '';
  const rowsHtml = config.rows.map((row, idx) => {
    const isEven = idx % 2 === 0;
    const bgClass = config.style === 'striped' && isEven ? 'bg-slate-50/90' : 'bg-white';
    const borderClass = config.style === 'clean' ? 'border-b border-slate-100' : 'border-b border-slate-200';
    
    let valClass = row.is_bold ? 'font-bold text-slate-900' : 'text-slate-800';
    if (row.highlight === 'purple') valClass = 'font-mono font-bold text-purple-900';
    else if (row.highlight === 'emerald') valClass = 'font-mono font-bold text-emerald-800';
    
    let valText = row.value || '-';
    if (isSample) {
      valText = valText
        .replace(/\{\{spk_number\}\}/g, 'SPK-2026-00125')
        .replace(/\{\{title\}\}/g, 'Pemasangan Palang Merek KCP Sukajadi')
        .replace(/\{\{vendor_name\}\}/g, 'PT Sinar Graha Kreatif')
        .replace(/\{\{location_name\}\}/g, 'Jl. Ir. H. Juanda No. 120 Bandung')
        .replace(/\{\{contract_value\}\}/g, 'Rp 15.000.000')
        .replace(/\{\{ba_date\}\}/g, 'Minggu, 16 Agustus 2026')
        .replace(/\{\{checkin_gps\}\}/g, '-6.88500, 107.61360');
    }
    
    return `<tr class="${borderClass} ${bgClass}"><td class="w-1/3 py-2 px-3.5 font-semibold text-slate-600">${row.label}</td><td class="py-2 px-3.5 ${valClass}">${valText}</td></tr>`;
  }).join('');

  const containerBorder = config.style === 'clean' ? 'border-t border-b border-slate-200' : 'border border-slate-300 rounded-xl overflow-hidden';
  return `<table class="w-full ${containerBorder} text-xs my-2 border-collapse shadow-xs"><tbody>${rowsHtml}</tbody></table>`;
}

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
  },
  sticker: {
    name: 'Penggantian Sticker & Acrylic NBL (Garansi 30 Hari)',
    header: 'Pada hari ini <strong>{{ba_date}}</strong>, telah dilaksanakan pekerjaan penggantian dan pemasangan stiker/acrylic visual toko untuk <strong>{{title}}</strong> pada lokasi <strong>{{location_name}}</strong>:',
    body: 'Pemasangan visual stiker dan display acrylic telah selesai rapi, bebas gelembung udara, presisi sesuai panduan brand guideline, dan dinyatakan <strong>SELESAI 100%</strong> dengan garansi rekatan selama <strong>30 (tiga puluh) hari kalender</strong>.'
  },
  addendum: {
    name: 'Berita Acara Addendum / Pekerjaan Tambahan',
    header: 'Pada hari ini <strong>{{ba_date}}</strong>, sehubungan dengan adanya penyesuaian lapangan pada SPK <strong>{{spk_number}}</strong>, telah dilakukan serah terima pekerjaan tambahan (addendum) untuk lokasi <strong>{{location_name}}</strong>:',
    body: 'Seluruh item sub-pekerjaan tambahan telah diverifikasi melalui dokumentasi foto satelit dan disetujui bersama oleh kedua belah pihak sebagai <strong>BAGIAN YANG TIDAK TERPISAHKAN DARI KONTRAK UTAMA</strong>.'
  }
};

function applyPresetClause() {
  const preset = clausePresets[selectedPresetKey.value];
  if (!preset) return;
  formData.value.header_html = preset.header;
  formData.value.body_template = preset.body;
}

function smartWrapText(field, openTag, closeTag, fallbackText, textareaRef) {
  const textarea = textareaRef?.value || textareaRef;
  const currentVal = formData.value[field] || '';
  
  if (textarea && typeof textarea.selectionStart === 'number') {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = currentVal.substring(start, end);
    const textToWrap = selectedText.length > 0 ? selectedText : fallbackText;
    
    const newVal = currentVal.substring(0, start) + `${openTag}${textToWrap}${closeTag}` + currentVal.substring(end);
    formData.value[field] = newVal;
    
    setTimeout(() => {
      textarea.focus();
      const newCursorPos = start + openTag.length + textToWrap.length + closeTag.length;
      textarea.setSelectionRange(newCursorPos, newCursorPos);
    }, 10);
  } else {
    formData.value[field] = `${currentVal} ${openTag}${fallbackText}${closeTag} `;
  }
}

function insertVariableAtCursor(field, varTag, textareaRef) {
  const textarea = textareaRef?.value || textareaRef;
  const currentVal = formData.value[field] || '';
  
  if (textarea && typeof textarea.selectionStart === 'number') {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const newVal = currentVal.substring(0, start) + ` ${varTag} ` + currentVal.substring(end);
    formData.value[field] = newVal;
    
    setTimeout(() => {
      textarea.focus();
      const newCursorPos = start + varTag.length + 2;
      textarea.setSelectionRange(newCursorPos, newCursorPos);
    }, 10);
  } else {
    formData.value[field] = `${currentVal} ${varTag} `;
  }
}

function insertBulletPointCustom(textareaRef) {
  const textarea = textareaRef?.value || textareaRef;
  const currentVal = formData.value.body_template || '';
  const bulletText = '\n• Poin Kesepakatan: ';
  
  if (textarea && typeof textarea.selectionStart === 'number') {
    const start = textarea.selectionStart;
    const newVal = currentVal.substring(0, start) + bulletText + currentVal.substring(start);
    formData.value.body_template = newVal;
    setTimeout(() => {
      textarea.focus();
      textarea.setSelectionRange(start + bulletText.length, start + bulletText.length);
    }, 10);
  } else {
    formData.value.body_template = `${currentVal}${bulletText}`;
  }
}

function insertNumberedPointCustom(textareaRef) {
  const textarea = textareaRef?.value || textareaRef;
  const currentVal = formData.value.body_template || '';
  const numText = '\n1. Butir Ketentuan: ';
  
  if (textarea && typeof textarea.selectionStart === 'number') {
    const start = textarea.selectionStart;
    const newVal = currentVal.substring(0, start) + numText + currentVal.substring(start);
    formData.value.body_template = newVal;
    setTimeout(() => {
      textarea.focus();
      textarea.setSelectionRange(start + numText.length, start + numText.length);
    }, 10);
  } else {
    formData.value.body_template = `${currentVal}${numText}`;
  }
}

function insertParagraphBreak(textareaRef) {
  const textarea = textareaRef?.value || textareaRef;
  const currentVal = formData.value.body_template || '';
  const breakText = '<br><br>';
  
  if (textarea && typeof textarea.selectionStart === 'number') {
    const start = textarea.selectionStart;
    const newVal = currentVal.substring(0, start) + breakText + currentVal.substring(start);
    formData.value.body_template = newVal;
    setTimeout(() => {
      textarea.focus();
      textarea.setSelectionRange(start + breakText.length, start + breakText.length);
    }, 10);
  } else {
    formData.value.body_template = `${currentVal}${breakText}`;
  }
}

function insertCustomTable(textareaRef) {
  const textarea = textareaRef?.value || textareaRef;
  const currentVal = formData.value.body_template || '';
  const tableHtml = `<table class="w-full border border-slate-300 rounded-lg text-xs border-collapse mt-1 mb-2">
  <tbody>
    <tr class="border-b border-slate-200 bg-slate-50">
      <td class="w-1/3 py-2 px-3 font-semibold text-slate-700">Nomor SPK</td>
      <td class="py-2 px-3 font-mono font-bold text-purple-900">{{spk_number}}</td>
    </tr>
    <tr class="border-b border-slate-200">
      <td class="py-2 px-3 font-semibold text-slate-700">Nama Pekerjaan</td>
      <td class="py-2 px-3 font-bold text-slate-900">{{title}}</td>
    </tr>
    <tr class="border-b border-slate-200 bg-slate-50">
      <td class="py-2 px-3 font-semibold text-slate-700">Lokasi / Cabang</td>
      <td class="py-2 px-3">{{location_name}}</td>
    </tr>
    <tr>
      <td class="py-2 px-3 font-semibold text-slate-700">Nilai Kontrak</td>
      <td class="py-2 px-3 font-mono font-bold text-emerald-800">{{contract_value}}</td>
    </tr>
  </tbody>
</table>`;

  if (textarea && typeof textarea.selectionStart === 'number') {
    const start = textarea.selectionStart;
    const newVal = currentVal.substring(0, start) + tableHtml + currentVal.substring(start);
    formData.value.body_template = newVal;
    setTimeout(() => {
      textarea.focus();
      textarea.setSelectionRange(start + tableHtml.length, start + tableHtml.length);
    }, 10);
  } else {
    formData.value.body_template = `${currentVal}${tableHtml}`;
  }
}

function resetToDefaultField(field) {
  if (field === 'header_html') {
    formData.value.header_html = clausePresets.standard.header;
  } else if (field === 'body_template') {
    formData.value.body_template = clausePresets.standard.body;
  }
}

function stripHtml(html) {
  if (!html) return '';
  return html.replace(/<[^>]*>?/gm, ' ');
}

function renderClauseWithSampleData(text) {
  if (!text) return '-';
  let processed = text
    .replace(/\{\{spk_number\}\}/g, '<strong>SPK-2026-00125</strong>')
    .replace(/\{\{title\}\}/g, '<strong>Pemasangan Palang Merek KCP Sukajadi</strong>')
    .replace(/\{\{vendor_name\}\}/g, '<strong>PT Sinar Graha Kreatif</strong>')
    .replace(/\{\{location_name\}\}/g, '<strong>Jl. Ir. H. Juanda No. 120 Bandung</strong>')
    .replace(/\{\{contract_value\}\}/g, '<strong>Rp 15.000.000</strong>')
    .replace(/\{\{ba_date\}\}/g, '<strong>Minggu, 16 Agustus 2026</strong>')
    .replace(/\{\{checkin_gps\}\}/g, '<strong>-6.88500, 107.61360</strong>');

  // Bersihkan newline dan spasi berlebih di awal/akhir dan di sekitar tag tabel
  processed = processed.trim()
    .replace(/(?:<br\s*\/?>\s*)+$/gi, '')
    .replace(/^(?:<br\s*\/?>\s*)+/gi, '')
    .replace(/\s*(<table[\s\S]*?<\/table>)\s*/gi, '$1')
    .replace(/\n{2,}/g, '<br><br>')
    .replace(/\n/g, '<br>');

  return processed;
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

function getTableConfig(tmpl) {
  if (!tmpl) return JSON.parse(JSON.stringify(tableConfig.value));
  if (tmpl.table_config_json) {
    try {
      const parsed = typeof tmpl.table_config_json === 'string' ? JSON.parse(tmpl.table_config_json) : tmpl.table_config_json;
      if (parsed && typeof parsed === 'object') {
        return JSON.parse(JSON.stringify(parsed));
      }
    } catch (e) {}
  }
  return {
    enabled: true,
    position: 'after_header',
    style: 'striped',
    rows: JSON.parse(JSON.stringify(defaultTableRows))
  };
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
  tableConfig.value = {
    enabled: true,
    position: 'after_header',
    style: 'striped',
    rows: JSON.parse(JSON.stringify(defaultTableRows))
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
  tableConfig.value = getTableConfig(tmpl);
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
    payload.append('table_config_json', JSON.stringify(tableConfig.value));
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
