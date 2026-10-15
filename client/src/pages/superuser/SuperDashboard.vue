<template>
  <div class="space-y-5 text-slate-100 font-sans">
    <!-- Breadcrumb Context & Top Console Bar -->
    <div class="space-y-2">
      <!-- Breadcrumb -->
      <div class="flex items-center gap-2 text-[10px] font-mono text-slate-400 uppercase tracking-wider">
        <span class="hover:text-slate-200 transition-colors">PORTAL</span>
        <span class="text-slate-600">//</span>
        <span class="hover:text-slate-200 transition-colors">SUPERUSER</span>
        <span class="text-slate-600">//</span>
        <span class="text-[#FACC15] font-bold">SYSTEM CONSOLE</span>
      </div>

      <!-- Title Header & Actions -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
        <div>
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xs bg-[#1C2028] border border-[#FACC15] text-[#FACC15] flex items-center justify-center font-mono">
              <ShieldCheck class="w-4 h-4 text-[#FACC15]" />
            </div>
            <h2 class="text-lg sm:text-xl font-black text-white tracking-wider font-mono uppercase">SUPERUSER SYSTEM CONSOLE</h2>
          </div>
          <p class="text-[11px] text-slate-400 mt-1 font-mono">Pusat administrasi sistem, otorisasi RBAC, konfigurasi gateway, dan audit keamanan.</p>
        </div>

        <!-- Action Buttons as specified in .cursorrules (Primary Button: Solid Yellow with Black Bold Text) -->
        <div class="flex items-center gap-2 self-start sm:self-auto font-mono">
          <button
            @click="exportBaReport"
            class="px-3.5 py-2 rounded-xs bg-[#13171F] hover:bg-[#1A202C] text-slate-300 hover:text-white text-xs font-bold flex items-center gap-2 border border-[#232A36] hover:border-[#FACC15]/60 transition-colors cursor-pointer uppercase"
            title="Download Rekap BA Opname"
          >
            <Download class="w-3.5 h-3.5 text-[#FACC15]" />
            <span>EXPORT LAPORAN BA</span>
          </button>

          <button
            @click="loadData"
            class="px-3.5 py-2 rounded-xs bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] text-xs font-bold flex items-center gap-2 border border-[#FACC15] transition-colors active:scale-95 cursor-pointer uppercase tracking-wider"
          >
            <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin' : '']" />
            <span>SEGARKAN DATA</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Segmented Control / Tabs (Industrial CAD Style) with Telemetry Status -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 p-1 bg-[#13171F] border border-[#232A36] rounded-xs font-mono">
      <div class="flex items-center gap-1 overflow-x-auto custom-scrollbar p-0.5">
        <button
          @click="activeTab = 'dashboard'"
          :class="[
            'flex items-center gap-2 px-3 py-1.5 rounded-xs transition-colors cursor-pointer whitespace-nowrap text-xs uppercase',
            activeTab === 'dashboard'
              ? 'bg-[#1C2028] text-[#FACC15] border-l-[3px] border-[#FACC15] font-bold'
              : 'text-slate-400 hover:text-slate-200 hover:bg-[#1A202C]/60 border-l-[3px] border-transparent font-medium'
          ]"
        >
          <LayoutDashboard class="w-3.5 h-3.5 text-[#FACC15]" />
          <span>Dashboard Eksekutif</span>
        </button>

        <button
          @click="activeTab = 'permissions'"
          :class="[
            'flex items-center gap-2 px-3 py-1.5 rounded-xs transition-colors cursor-pointer whitespace-nowrap text-xs uppercase',
            activeTab === 'permissions'
              ? 'bg-[#1C2028] text-[#FACC15] border-l-[3px] border-[#FACC15] font-bold'
              : 'text-slate-400 hover:text-slate-200 hover:bg-[#1A202C]/60 border-l-[3px] border-transparent font-medium'
          ]"
        >
          <ShieldAlert class="w-3.5 h-3.5 text-[#FACC15]" />
          <span>Hak Akses & Role</span>
        </button>

        <button
          @click="activeTab = 'users'"
          :class="[
            'flex items-center gap-2 px-3 py-1.5 rounded-xs transition-colors cursor-pointer whitespace-nowrap text-xs uppercase',
            activeTab === 'users'
              ? 'bg-[#1C2028] text-[#FACC15] border-l-[3px] border-[#FACC15] font-bold'
              : 'text-slate-400 hover:text-slate-200 hover:bg-[#1A202C]/60 border-l-[3px] border-transparent font-medium'
          ]"
        >
          <Users class="w-3.5 h-3.5 text-[#FACC15]" />
          <span>Pengguna & Akun ({{ users.length || 14 }})</span>
        </button>

        <button
          @click="activeTab = 'settings'"
          :class="[
            'flex items-center gap-2 px-3 py-1.5 rounded-xs transition-colors cursor-pointer whitespace-nowrap text-xs uppercase',
            activeTab === 'settings'
              ? 'bg-[#1C2028] text-[#FACC15] border-l-[3px] border-[#FACC15] font-bold'
              : 'text-slate-400 hover:text-slate-200 hover:bg-[#1A202C]/60 border-l-[3px] border-transparent font-medium'
          ]"
        >
          <Settings class="w-3.5 h-3.5 text-[#FACC15]" />
          <span>Gateway & Sistem</span>
        </button>

        <button
          @click="activeTab = 'audit'"
          :class="[
            'flex items-center gap-2 px-3 py-1.5 rounded-xs transition-colors cursor-pointer whitespace-nowrap text-xs uppercase',
            activeTab === 'audit'
              ? 'bg-[#1C2028] text-[#FACC15] border-l-[3px] border-[#FACC15] font-bold'
              : 'text-slate-400 hover:text-slate-200 hover:bg-[#1A202C]/60 border-l-[3px] border-transparent font-medium'
          ]"
        >
          <History class="w-3.5 h-3.5 text-[#FACC15]" />
          <span>Audit Trail ({{ auditLogs.length || 100 }})</span>
        </button>
      </div>

      <!-- Right Telemetry Status Pill -->
      <div class="hidden xl:flex items-center gap-2 px-2.5 py-1 rounded-xs bg-[#0E1117] border border-[#232A36] text-[10px] font-mono text-slate-300 shrink-0">
        <span class="w-1.5 h-1.5 rounded-full bg-[#FACC15] animate-pulse"></span>
        <span class="text-slate-200 font-bold uppercase tracking-wider">LIVE TELEMETRY</span>
        <span class="text-slate-600">//</span>
        <span class="text-[#FACC15] font-bold">100% SLA SYNC</span>
      </div>
    </div>

    <!-- TAB 1: Executive Dashboard Tab -->
    <div v-if="activeTab === 'dashboard'">
      <ExecutiveDashboardView />
    </div>

    <!-- TAB 2: Permissions Matrix Tab -->
    <div v-if="activeTab === 'permissions'">
      <PermissionMatrix />
    </div>

    <!-- TAB 3: Users Management Tab (Industrial CAD) -->
    <div v-if="activeTab === 'users'" class="space-y-4">
      <!-- Search & Action Bar -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-2 w-full sm:w-auto font-mono">
          <div class="relative w-full sm:w-80">
            <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              v-model="userSearchQuery"
              type="text"
              placeholder="CARI NAMA / EMAIL / TELEPON..."
              class="w-full pl-9 pr-3 py-2 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs text-white placeholder-slate-500 font-mono tracking-wider focus:outline-none"
            />
          </div>

          <select
            v-model="userRoleFilter"
            class="px-3 py-2 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs font-mono text-slate-300 focus:outline-none"
          >
            <option value="">SEMUA ROLE ({{ users.length }})</option>
            <option value="SUPERUSER">SUPERUSER</option>
            <option value="ADMIN">ADMIN</option>
            <option value="FIELD_TEAM">FIELD_TEAM</option>
            <option value="VENDOR">CLIENT / VENDOR</option>
          </select>
        </div>

        <button
          @click="openAddUserModal"
          class="px-4 py-2 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] rounded-xs text-xs font-bold font-mono uppercase tracking-wider flex items-center gap-2 border border-[#FACC15] shadow-xs active:scale-95 transition-all cursor-pointer whitespace-nowrap self-end sm:self-auto"
        >
          <Plus class="w-4 h-4 stroke-[3]" />
          <span>TAMBAH PENGGUNA BARU</span>
        </button>
      </div>

      <div class="bg-[#13171F] border border-[#232A36] rounded-xs shadow-md overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-[#0E1117] text-slate-400 font-mono text-[10px] uppercase tracking-wider border-b border-[#232A36]">
              <tr>
                <th class="py-3 px-4">Nama Lengkap</th>
                <th class="py-3 px-4">Email Login</th>
                <th class="py-3 px-4">Telepon / WA</th>
                <th class="py-3 px-4">Role Hak Akses</th>
                <th class="py-3 px-4">Afiliasi Client</th>
                <th class="py-3 px-4">Status Akun</th>
                <th class="py-3 px-4 text-center w-28">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#232A36] text-slate-300 font-mono">
              <template v-if="loading">
                <tr>
                  <td colspan="7" class="py-10 text-center text-slate-500 font-mono uppercase tracking-wider">Memuat data pengguna...</td>
                </tr>
              </template>
              <template v-else-if="filteredUsers.length > 0">
                <tr v-for="u in filteredUsers" :key="u.id" class="hover:bg-[#1A202C]/60 transition-colors">
                  <td class="py-3.5 px-4 font-bold text-white font-sans">{{ u.name }}</td>
                  <td class="py-3.5 px-4 text-slate-300">{{ u.email }}</td>
                  <td class="py-3.5 px-4 text-slate-400">{{ u.phone || '-' }}</td>
                  <td class="py-3.5 px-4">
                    <span :class="[
                      'px-2 py-0.5 rounded-xs font-bold text-[10px] border tracking-wider uppercase',
                      u.role === 'SUPERUSER' ? 'bg-[#FACC15]/10 text-[#FACC15] border-[#FACC15]/40' :
                      u.role === 'ADMIN' ? 'bg-blue-500/10 text-blue-400 border-blue-500/30' :
                      u.role === 'FIELD_TEAM' ? 'bg-amber-500/10 text-amber-400 border-amber-500/30' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
                    ]">
                      {{ u.role === 'VENDOR' ? 'CLIENT' : u.role }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 font-sans text-slate-300">{{ u.vendor_name || '-' }}</td>
                  <td class="py-3.5 px-4">
                    <span class="px-2 py-0.5 rounded-xs text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center gap-1 w-fit">
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                      AKTIF
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                      <button
                        @click="openEditUserModal(u)"
                        title="Edit Pengguna"
                        class="p-1.5 rounded-xs bg-[#1C2028] hover:bg-[#232A36] text-slate-300 hover:text-[#FACC15] border border-[#232A36] hover:border-[#FACC15]/50 transition-colors cursor-pointer"
                      >
                        <Pencil class="w-3.5 h-3.5" />
                      </button>
                      <button
                        @click="handleDeleteUser(u)"
                        title="Hapus Pengguna"
                        class="p-1.5 rounded-xs bg-[#1C2028] hover:bg-rose-950/40 text-slate-300 hover:text-rose-400 border border-[#232A36] hover:border-rose-500/50 transition-colors cursor-pointer"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td colspan="7" class="py-10 text-center text-slate-500 font-mono uppercase tracking-wider">Tidak ada pengguna yang sesuai dengan filter.</td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 4: Gateway & System Settings Tab (Industrial CAD Layout) -->
    <div v-if="activeTab === 'settings'" class="space-y-5">
      <!-- 1. WhatsApp Gateway Configuration & Real-Time Test Card -->
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#232A36] pb-3">
          <div class="flex items-center gap-2.5">
            <div
              :class="[
                'w-8 h-8 rounded-xs text-white flex items-center justify-center border',
                gatewayInfo.state === 'ACTIVE'
                  ? 'bg-emerald-950/60 border-emerald-500/50 text-emerald-400'
                  : gatewayInfo.state === 'MOCK'
                  ? 'bg-amber-950/60 border-amber-500/50 text-amber-400'
                  : 'bg-rose-950/60 border-rose-500/50 text-rose-400'
              ]"
            >
              <MessageSquare class="w-4 h-4" />
            </div>
            <div>
              <h3 class="font-bold text-sm text-white font-mono uppercase tracking-wider">WhatsApp Gateway (Fonnte API)</h3>
              <p class="text-[11px] text-slate-400 font-mono">Integrasi pengiriman pesan notifikasi otomatis untuk SPK, Check-In, dan Berita Acara.</p>
            </div>
          </div>
          <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap font-mono">
            <!-- Master Power Switch Toggle Button -->
            <button
              type="button"
              @click="toggleWaGatewayPower"
              :disabled="savingSetting"
              :class="[
                'px-3 py-1.5 rounded-xs text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer border tracking-wider uppercase',
                waGatewayEnabled === '1'
                  ? 'bg-emerald-950/80 text-emerald-300 border-emerald-500 hover:bg-emerald-900'
                  : 'bg-rose-950/80 text-rose-300 border-rose-500 hover:bg-rose-900 animate-pulse'
              ]"
              :title="waGatewayEnabled === '1' ? 'Klik untuk mematikan WA Gateway' : 'Klik untuk menghidupkan WA Gateway'"
            >
              <Power v-if="waGatewayEnabled === '1'" class="w-3.5 h-3.5" />
              <PowerOff v-else class="w-3.5 h-3.5" />
              <span>{{ waGatewayEnabled === '1' ? 'GATEWAY: ON' : 'GATEWAY: OFF' }}</span>
            </button>

            <span
              :class="[
                'px-2.5 py-1 rounded-xs text-[10px] font-bold border flex items-center gap-1.5 uppercase tracking-wider',
                gatewayInfo.state === 'ACTIVE'
                  ? 'bg-emerald-950/40 text-emerald-400 border-emerald-500/40'
                  : gatewayInfo.state === 'DISABLED'
                  ? 'bg-rose-950/40 text-rose-400 border-rose-500/40'
                  : gatewayInfo.state === 'MOCK'
                  ? 'bg-amber-950/40 text-amber-400 border-amber-500/40'
                  : 'bg-rose-950/40 text-rose-400 border-rose-500/40'
              ]"
            >
              <span
                :class="[
                  'w-1.5 h-1.5 rounded-full',
                  gatewayInfo.state === 'ACTIVE'
                    ? 'bg-emerald-400 animate-pulse'
                    : gatewayInfo.state === 'DISABLED'
                    ? 'bg-rose-400'
                    : gatewayInfo.state === 'MOCK'
                    ? 'bg-amber-400'
                    : 'bg-rose-400'
                ]"
              ></span>
              <span>
                {{ gatewayInfo.state === 'ACTIVE' ? 'Fonnte Aktif' : gatewayInfo.state === 'DISABLED' ? 'Dimatikan (OFF)' : gatewayInfo.state === 'MOCK' ? 'Mode Mock (Testing)' : 'Belum Terkonfigurasi' }}
              </span>
            </span>

            <button
              type="button"
              @click="refreshGatewayStatus"
              :disabled="refreshingGateway"
              title="Periksa Ulang Status Gateway"
              class="p-1.5 bg-[#1C2028] hover:bg-[#232A36] border border-[#232A36] hover:border-[#FACC15]/50 rounded-xs text-slate-300 hover:text-white cursor-pointer text-[10px] font-bold flex items-center gap-1"
            >
              <RefreshCw :class="['w-3 h-3', refreshingGateway ? 'animate-spin text-[#FACC15]' : '']" />
            </button>
          </div>
        </div>

        <!-- Banner 0: Gateway Dimatikan (OFF) -->
        <div
          v-if="waGatewayEnabled === '0' || gatewayInfo.state === 'DISABLED'"
          class="p-4 bg-rose-950/20 border border-rose-500/40 rounded-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-rose-300 text-xs font-mono"
        >
          <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xs bg-rose-950/80 border border-rose-500 text-rose-300 flex items-center justify-center shrink-0 mt-0.5">
              <PowerOff class="w-4 h-4" />
            </div>
            <div>
              <div class="font-bold text-sm text-rose-200 flex items-center gap-2 uppercase tracking-wider">
                <span>WHATSAPP GATEWAY SAAT INI DIMATIKAN (OFF)</span>
              </div>
              <p class="text-[11px] text-rose-300/80 mt-0.5">
                Pengiriman notifikasi otomatis SPK, check-in, dan Berita Acara ke seluruh nomor WhatsApp dijeda. Klik tombol di samping untuk mengaktifkan kembali.
              </p>
            </div>
          </div>
          <button
            type="button"
            @click="toggleWaGatewayPower"
            :disabled="savingSetting"
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xs text-xs cursor-pointer active:scale-95 transition-all flex items-center gap-1.5 whitespace-nowrap self-start sm:self-auto uppercase tracking-wider"
          >
            <Power class="w-3.5 h-3.5" />
            <span>Nyalakan Gateway (ON)</span>
          </button>
        </div>

        <!-- Banner 1: Gateway Online / Aktif (Hijau) -->
        <div
          v-else-if="gatewayInfo.state === 'ACTIVE'"
          class="p-4 bg-emerald-950/20 border border-emerald-500/40 rounded-xs flex items-center gap-3 text-emerald-300 text-xs font-mono"
        >
          <ShieldCheck class="w-5 h-5 text-emerald-400 shrink-0" />
          <div class="flex-1">
            <div class="font-bold flex items-center gap-2 uppercase tracking-wider">
              <span>WhatsApp Gateway Siap & Terhubung Normal</span>
              <span v-if="gatewayInfo.masked_token" class="font-mono text-[10px] bg-emerald-950/60 text-emerald-300 px-2 py-0.5 border border-emerald-500/30 rounded-xs">
                Token: {{ gatewayInfo.masked_token }}
              </span>
            </div>
            <p class="text-[11px] text-emerald-400/80 mt-0.5">
              Notifikasi otomatis pengerjaan SPK, presensi GPS, dan Berita Acara siap dikirimkan ke nomor WhatsApp penerima.
            </p>
          </div>
        </div>

        <!-- Banner 2: Token sudah diisi tapi belum disimpan / Belum Dikonfigurasi -->
        <div
          v-else-if="gatewayInfo.state === 'UNCONFIGURED' && fonnteApiKey && fonnteApiKey.trim().length > 5"
          class="p-4 bg-amber-950/20 border border-amber-500/40 rounded-xs flex items-center gap-3 text-amber-300 text-xs font-mono"
        >
          <AlertTriangle class="w-5 h-5 text-amber-400 shrink-0" />
          <div class="flex-1">
            <div class="font-bold uppercase tracking-wider">Token Fonnte telah dimasukkan</div>
            <p class="text-[11px] text-amber-400/80 mt-0.5">
              Klik tombol <span class="font-bold underline text-[#FACC15]">"Simpan Token"</span> di bawah untuk mengaktifkan koneksi WhatsApp Gateway ke sistem.
            </p>
          </div>
        </div>

        <!-- Banner 3: Token Kosong (Merah) -->
        <div
          v-else-if="gatewayInfo.state === 'UNCONFIGURED'"
          class="p-4 bg-rose-950/20 border border-rose-500/40 rounded-xs flex items-center gap-3 text-rose-300 text-xs font-mono"
        >
          <ShieldAlert class="w-5 h-5 text-rose-400 shrink-0" />
          <div class="flex-1">
            <div class="font-bold uppercase tracking-wider">WhatsApp Gateway belum terkonfigurasi</div>
            <p class="text-[11px] text-rose-400/80 mt-0.5">
              API Token Fonnte masih kosong atau bernilai placeholder. Silakan masukkan token resmi dari dashboard Fonnte Anda di bawah ini lalu klik Simpan Token.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-1">
          <!-- Left: API Key Configuration -->
          <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-3">
            <div>
              <label class="block font-bold text-white text-xs mb-1 font-mono uppercase tracking-wider">Fonnte API Token / Secret Key</label>
              <p class="text-[11px] text-slate-400 mb-2 font-mono">Token API resmi dari akun Fonnte Anda untuk otorisasi pengiriman pesan WhatsApp.</p>
              <div class="flex items-center gap-2 font-mono">
                <input
                  type="text"
                  v-model="fonnteApiKey"
                  placeholder="Masukkan API Token Fonnte..."
                  class="w-full px-3.5 py-2 border border-[#232A36] focus:border-[#FACC15] rounded-xs bg-[#13171F] text-white focus:outline-none text-xs"
                />
                <button
                  @click="saveFonnteKey"
                  :disabled="savingSetting"
                  class="px-4 py-2 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] font-bold rounded-xs whitespace-nowrap active:scale-95 transition-all cursor-pointer text-xs uppercase tracking-wider"
                >
                  {{ savingSetting ? 'Menyimpan...' : 'Simpan Token' }}
                </button>
              </div>
            </div>

            <div class="p-3 bg-[#13171F] border border-[#232A36] rounded-xs text-[11px] text-slate-400 space-y-1 font-mono">
              <div class="font-bold text-slate-200 uppercase tracking-wider">// Info Endpoint Fonnte:</div>
              <div>URL: <span class="text-emerald-400 font-semibold">https://api.fonnte.com/send</span></div>
              <div>Format Target: 628xxxxxxxxxx</div>
            </div>
          </div>

          <!-- Right: Test Send WhatsApp Message Panel -->
          <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-3 font-mono">
            <div>
              <h4 class="font-bold text-white text-xs flex items-center gap-1.5 uppercase tracking-wider">
                <Send class="w-3.5 h-3.5 text-[#FACC15]" />
                <span>Uji Coba Pengiriman Notifikasi WhatsApp</span>
              </h4>
              <p class="text-[11px] text-slate-400 mt-0.5">Kirim pesan pengujian langsung ke nomor tujuan untuk memastikan gateway berfungsi.</p>
            </div>

            <div class="space-y-2 text-xs">
              <div>
                <label class="block font-bold text-slate-300 text-[11px] mb-1 uppercase tracking-wider">Nomor WhatsApp Tujuan</label>
                <input
                  type="text"
                  v-model="testWaPhone"
                  placeholder="Contoh: 081234567890"
                  class="w-full px-3 py-1.5 border border-[#232A36] focus:border-[#FACC15] rounded-xs bg-[#13171F] text-white focus:outline-none text-xs"
                />
              </div>

              <div>
                <label class="block font-bold text-slate-300 text-[11px] mb-1 uppercase tracking-wider">Isi Pesan Uji Coba</label>
                <textarea
                  v-model="testWaMessage"
                  rows="2"
                  class="w-full px-3 py-1.5 border border-[#232A36] focus:border-[#FACC15] rounded-xs bg-[#13171F] text-white focus:outline-none text-xs"
                  placeholder="Tulis pesan pengujian..."
                ></textarea>
              </div>

              <div class="flex items-center justify-between pt-1">
                <span v-if="testWaStatus" :class="[
                  'text-[11px] font-bold font-mono',
                  testWaStatus.success ? 'text-emerald-400' : 'text-rose-400'
                ]">
                  {{ testWaStatus.message }}
                </span>
                <span v-else></span>

                <button
                  type="button"
                  @click="handleTestWhatsApp"
                  :disabled="testingWa || !testWaPhone"
                  class="px-4 py-2 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] font-bold rounded-xs transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50 text-xs ml-auto uppercase tracking-wider"
                >
                  <Send :class="['w-3.5 h-3.5', testingWa ? 'animate-spin' : '']" />
                  <span>{{ testingWa ? 'Mengirim...' : 'Kirim Test WA' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. WhatsApp Notification Events Matrix Selection Card -->
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-5 space-y-4 font-mono">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#232A36] pb-3">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xs bg-[#1C2028] border border-[#232A36] text-[#FACC15] flex items-center justify-center">
              <Bell class="w-4 h-4 text-[#FACC15]" />
            </div>
            <div>
              <h3 class="font-bold text-sm text-white uppercase tracking-wider">Seleksi Otomasi Notifikasi WhatsApp (Event Triggers)</h3>
              <p class="text-[11px] text-slate-400 font-sans">Pilih event pekerjaan lapangan yang diizinkan mengirim notifikasi WhatsApp otomatis ke penerima.</p>
            </div>
          </div>
          <span class="px-2.5 py-1 rounded-xs text-[10px] font-bold bg-[#1C2028] text-[#FACC15] border border-[#232A36] self-start sm:self-auto uppercase tracking-wider">
            6 Kategori Event
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs font-mono">
          <!-- 1. SPK Baru / Ditugaskan -->
          <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-3 flex flex-col justify-between hover:border-[#FACC15]/40 transition-all">
            <div>
              <div class="flex items-center justify-between gap-2">
                <span class="font-bold text-white flex items-center gap-1.5 uppercase tracking-wider">
                  <Briefcase class="w-3.5 h-3.5 text-[#FACC15]" />
                  <span>SPK Baru & Penugasan</span>
                </span>
                <span :class="['px-2 py-0.5 rounded-xs text-[10px] font-bold border uppercase tracking-wider', waNotifSpkAssigned === '1' ? 'bg-emerald-950/40 text-emerald-400 border-emerald-500/40' : 'bg-slate-900 text-slate-500 border-slate-700']">
                  {{ waNotifSpkAssigned === '1' ? 'AKTIF' : 'NONAKTIF' }}
                </span>
              </div>
              <p class="text-[11px] text-slate-400 mt-1 font-sans">Kirim rincian SPK, target SLA, dan link lokasi ke Teknisi / Vendor.</p>
            </div>
            <button
              type="button"
              @click="toggleWaNotif('wa_notify_spk_assigned', waNotifSpkAssigned)"
              :class="[
                'w-full py-1.5 font-bold rounded-xs text-xs transition-all cursor-pointer border uppercase tracking-wider',
                waNotifSpkAssigned === '1'
                  ? 'bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 border-rose-500/50'
                  : 'bg-emerald-600 hover:bg-emerald-500 text-white border-emerald-500'
              ]"
            >
              {{ waNotifSpkAssigned === '1' ? 'Nonaktifkan' : 'Aktifkan Notifikasi' }}
            </button>
          </div>

          <!-- 2. Presensi GPS Check-In -->
          <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-3 flex flex-col justify-between hover:border-[#FACC15]/40 transition-all">
            <div>
              <div class="flex items-center justify-between gap-2">
                <span class="font-bold text-white flex items-center gap-1.5 uppercase tracking-wider">
                  <MapPin class="w-3.5 h-3.5 text-blue-400" />
                  <span>Presensi GPS Check-In</span>
                </span>
                <span :class="['px-2 py-0.5 rounded-xs text-[10px] font-bold border uppercase tracking-wider', waNotifGpsCheckin === '1' ? 'bg-emerald-950/40 text-emerald-400 border-emerald-500/40' : 'bg-slate-900 text-slate-500 border-slate-700']">
                  {{ waNotifGpsCheckin === '1' ? 'AKTIF' : 'NONAKTIF' }}
                </span>
              </div>
              <p class="text-[11px] text-slate-400 mt-1 font-sans">Konfirmasi kehadiran teknisi di radius toko valid dikirim ke Supervisor.</p>
            </div>
            <button
              type="button"
              @click="toggleWaNotif('wa_notify_gps_checkin', waNotifGpsCheckin)"
              :class="[
                'w-full py-1.5 font-bold rounded-xs text-xs transition-all cursor-pointer border uppercase tracking-wider',
                waNotifGpsCheckin === '1'
                  ? 'bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 border-rose-500/50'
                  : 'bg-emerald-600 hover:bg-emerald-500 text-white border-emerald-500'
              ]"
            >
              {{ waNotifGpsCheckin === '1' ? 'Nonaktifkan' : 'Aktifkan Notifikasi' }}
            </button>
          </div>

          <!-- 3. Upload Foto Bukti Kerja -->
          <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-3 flex flex-col justify-between hover:border-[#FACC15]/40 transition-all">
            <div>
              <div class="flex items-center justify-between gap-2">
                <span class="font-bold text-white flex items-center gap-1.5 uppercase tracking-wider">
                  <Camera class="w-3.5 h-3.5 text-emerald-400" />
                  <span>Upload Foto Bukti Kerja</span>
                </span>
                <span :class="['px-2 py-0.5 rounded-xs text-[10px] font-bold border uppercase tracking-wider', waNotifEvidenceUpload === '1' ? 'bg-emerald-950/40 text-emerald-400 border-emerald-500/40' : 'bg-slate-900 text-slate-500 border-slate-700']">
                  {{ waNotifEvidenceUpload === '1' ? 'AKTIF' : 'NONAKTIF' }}
                </span>
              </div>
              <p class="text-[11px] text-slate-400 mt-1 font-sans">Notifikasi saat foto Before/Process/After diunggah dikirim ke Supervisor.</p>
            </div>
            <button
              type="button"
              @click="toggleWaNotif('wa_notify_evidence_upload', waNotifEvidenceUpload)"
              :class="[
                'w-full py-1.5 font-bold rounded-xs text-xs transition-all cursor-pointer border uppercase tracking-wider',
                waNotifEvidenceUpload === '1'
                  ? 'bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 border-rose-500/50'
                  : 'bg-emerald-600 hover:bg-emerald-500 text-white border-emerald-500'
              ]"
            >
              {{ waNotifEvidenceUpload === '1' ? 'Nonaktifkan' : 'Aktifkan Notifikasi' }}
            </button>
          </div>

          <!-- 4. Laporan Kendala Lapangan (Urgent) -->
          <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-3 flex flex-col justify-between hover:border-[#FACC15]/40 transition-all">
            <div>
              <div class="flex items-center justify-between gap-2">
                <span class="font-bold text-white flex items-center gap-1.5 uppercase tracking-wider">
                  <AlertTriangle class="w-3.5 h-3.5 text-amber-400" />
                  <span>Kendala Lapangan (Urgent)</span>
                </span>
                <span :class="['px-2 py-0.5 rounded-xs text-[10px] font-bold border uppercase tracking-wider', waNotifIssueReported === '1' ? 'bg-emerald-950/40 text-emerald-400 border-emerald-500/40' : 'bg-slate-900 text-slate-500 border-slate-700']">
                  {{ waNotifIssueReported === '1' ? 'AKTIF' : 'NONAKTIF' }}
                </span>
              </div>
              <p class="text-[11px] text-slate-400 mt-1 font-sans">Peringatan prioritas tinggi jika ada hambatan kerja (cuaca/izin/listrik padam).</p>
            </div>
            <button
              type="button"
              @click="toggleWaNotif('wa_notify_issue_reported', waNotifIssueReported)"
              :class="[
                'w-full py-1.5 font-bold rounded-xs text-xs transition-all cursor-pointer border uppercase tracking-wider',
                waNotifIssueReported === '1'
                  ? 'bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 border-rose-500/50'
                  : 'bg-emerald-600 hover:bg-emerald-500 text-white border-emerald-500'
              ]"
            >
              {{ waNotifIssueReported === '1' ? 'Nonaktifkan' : 'Aktifkan Notifikasi' }}
            </button>
          </div>

          <!-- 5. SPK Selesai Dikerjakan -->
          <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-3 flex flex-col justify-between hover:border-[#FACC15]/40 transition-all">
            <div>
              <div class="flex items-center justify-between gap-2">
                <span class="font-bold text-white flex items-center gap-1.5 uppercase tracking-wider">
                  <CheckCircle2 class="w-3.5 h-3.5 text-teal-400" />
                  <span>SPK Selesai Dikerjakan</span>
                </span>
                <span :class="['px-2 py-0.5 rounded-xs text-[10px] font-bold border uppercase tracking-wider', waNotifSpkSubmitted === '1' ? 'bg-emerald-950/40 text-emerald-400 border-emerald-500/40' : 'bg-slate-900 text-slate-500 border-slate-700']">
                  {{ waNotifSpkSubmitted === '1' ? 'AKTIF' : 'NONAKTIF' }}
                </span>
              </div>
              <p class="text-[11px] text-slate-400 mt-1 font-sans">Notifikasi pengerjaan lapangan selesai dan siap diverifikasi oleh QC.</p>
            </div>
            <button
              type="button"
              @click="toggleWaNotif('wa_notify_spk_submitted', waNotifSpkSubmitted)"
              :class="[
                'w-full py-1.5 font-bold rounded-xs text-xs transition-all cursor-pointer border uppercase tracking-wider',
                waNotifSpkSubmitted === '1'
                  ? 'bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 border-rose-500/50'
                  : 'bg-emerald-600 hover:bg-emerald-500 text-white border-emerald-500'
              ]"
            >
              {{ waNotifSpkSubmitted === '1' ? 'Nonaktifkan' : 'Aktifkan Notifikasi' }}
            </button>
          </div>

          <!-- 6. Berita Acara (BA) Diterbitkan -->
          <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-3 flex flex-col justify-between hover:border-[#FACC15]/40 transition-all">
            <div>
              <div class="flex items-center justify-between gap-2">
                <span class="font-bold text-white flex items-center gap-1.5 uppercase tracking-wider">
                  <FileCheck class="w-3.5 h-3.5 text-blue-400" />
                  <span>Berita Acara (BA) Terbit</span>
                </span>
                <span :class="['px-2 py-0.5 rounded-xs text-[10px] font-bold border uppercase tracking-wider', waNotifBaIssued === '1' ? 'bg-emerald-950/40 text-emerald-400 border-emerald-500/40' : 'bg-slate-900 text-slate-500 border-slate-700']">
                  {{ waNotifBaIssued === '1' ? 'AKTIF' : 'NONAKTIF' }}
                </span>
              </div>
              <p class="text-[11px] text-slate-400 mt-1 font-sans">Kirim notifikasi dokumen BA digital dan link PDF resmi ke Client / Vendor.</p>
            </div>
            <button
              type="button"
              @click="toggleWaNotif('wa_notify_ba_issued', waNotifBaIssued)"
              :class="[
                'w-full py-1.5 font-bold rounded-xs text-xs transition-all cursor-pointer border uppercase tracking-wider',
                waNotifBaIssued === '1'
                  ? 'bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 border-rose-500/50'
                  : 'bg-emerald-600 hover:bg-emerald-500 text-white border-emerald-500'
              ]"
            >
              {{ waNotifBaIssued === '1' ? 'Nonaktifkan' : 'Aktifkan Notifikasi' }}
            </button>
          </div>
        </div>
      </div>

      <!-- 3. Security & Geofencing GPS Parameters Card -->
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-5 space-y-4 font-mono">
        <div class="flex items-center gap-2.5 border-b border-[#232A36] pb-3">
          <div class="w-8 h-8 rounded-xs bg-[#1C2028] border border-[#232A36] text-blue-400 flex items-center justify-center">
            <MapPin class="w-4 h-4 text-blue-400" />
          </div>
          <div>
            <h3 class="font-bold text-sm text-white uppercase tracking-wider">Keamanan Geofencing GPS & Integritas Evidensi</h3>
            <p class="text-[11px] text-slate-400 font-sans">Konfigurasi radius toleransi presensi teknisi lapangan dan segel kriptografi foto bukti.</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-mono">
          <!-- Radius Geofencing -->
          <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-2 flex flex-col justify-between">
            <div>
              <div class="font-bold text-white uppercase tracking-wider">Radius Geofencing (Meter)</div>
              <p class="text-[11px] text-slate-400 mt-0.5 font-sans">Toleransi jarak maksimum GPS teknisi dari titik toko saat check-in.</p>
            </div>
            <div class="flex items-center gap-2 pt-2">
              <input
                type="number"
                v-model="geofenceRadius"
                class="w-full px-3 py-1.5 border border-[#232A36] focus:border-[#FACC15] rounded-xs bg-[#13171F] text-white focus:outline-none font-mono text-xs"
              />
              <button
                @click="saveSetting('geofence_default_radius_meters', geofenceRadius)"
                class="px-3.5 py-1.5 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] font-bold rounded-xs text-xs cursor-pointer uppercase tracking-wider"
              >
                Simpan
              </button>
            </div>
          </div>

          <!-- Strict GPS Toggle -->
          <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-2 flex flex-col justify-between">
            <div>
              <div class="font-bold text-white uppercase tracking-wider">Validasi GPS Ketat</div>
              <p class="text-[11px] text-slate-400 mt-0.5 font-sans">Wajibkan akurasi GPS terverifikasi browser sebelum upload foto.</p>
            </div>
            <div class="flex items-center justify-between pt-2">
              <span class="font-bold text-xs font-mono" :class="strictGps === '1' ? 'text-emerald-400' : 'text-slate-500'">
                {{ strictGps === '1' ? 'AKTIF (1)' : 'NONAKTIF (0)' }}
              </span>
              <button
                @click="toggleStrictGps"
                class="px-3.5 py-1.5 rounded-xs font-bold text-xs cursor-pointer transition-all border uppercase tracking-wider"
                :class="strictGps === '1' ? 'bg-emerald-950/40 text-emerald-300 border-emerald-500 hover:bg-emerald-900' : 'bg-[#1C2028] text-slate-400 border-[#232A36] hover:bg-[#232A36]'"
              >
                {{ strictGps === '1' ? 'Nonaktifkan' : 'Aktifkan' }}
              </button>
            </div>
          </div>

          <!-- SHA256 Integrity Lock Toggle -->
          <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-2 flex flex-col justify-between">
            <div>
              <div class="font-bold text-white uppercase tracking-wider">Segel Integritas SHA-256</div>
              <p class="text-[11px] text-slate-400 mt-0.5 font-sans">Kunci hash kriptografis untuk membuktikan keaslian foto tanpa manipulasi.</p>
            </div>
            <div class="flex items-center justify-between pt-2">
              <span class="font-bold text-xs font-mono" :class="shaLock === '1' ? 'text-emerald-400' : 'text-slate-500'">
                {{ shaLock === '1' ? 'AKTIF (1)' : 'NONAKTIF (0)' }}
              </span>
              <button
                @click="toggleShaLock"
                class="px-3.5 py-1.5 rounded-xs font-bold text-xs cursor-pointer transition-all border uppercase tracking-wider"
                :class="shaLock === '1' ? 'bg-emerald-950/40 text-emerald-300 border-emerald-500 hover:bg-emerald-900' : 'bg-[#1C2028] text-slate-400 border-[#232A36] hover:bg-[#232A36]'"
              >
                {{ shaLock === '1' ? 'Nonaktifkan' : 'Aktifkan' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Identity & General Platform Settings Card -->
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs p-5 space-y-4 font-mono">
        <div class="flex items-center gap-2.5 border-b border-[#232A36] pb-3">
          <div class="w-8 h-8 rounded-xs bg-[#1C2028] border border-[#232A36] text-[#FACC15] flex items-center justify-center">
            <Globe class="w-4 h-4 text-[#FACC15]" />
          </div>
          <div>
            <h3 class="font-bold text-sm text-white uppercase tracking-wider">Identitas & Metadata Platform</h3>
            <p class="text-[11px] text-slate-400 font-sans">Nama resmi aplikasi yang tercantum pada dokumen PDF Berita Acara (BA) dan notifikasi.</p>
          </div>
        </div>

        <div class="p-4 bg-[#0E1117] border border-[#232A36] rounded-xs space-y-3 text-xs">
          <div>
            <label class="block font-bold text-white text-xs mb-1 uppercase tracking-wider">Nama Resmi Platform Aplikasi</label>
            <div class="flex items-center gap-2">
              <input
                type="text"
                v-model="appName"
                class="w-full px-3.5 py-2 border border-[#232A36] focus:border-[#FACC15] rounded-xs bg-[#13171F] text-white focus:outline-none font-mono text-xs"
              />
              <button
                @click="saveSetting('app_name', appName)"
                class="px-4 py-2 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] font-bold rounded-xs text-xs cursor-pointer uppercase tracking-wider whitespace-nowrap"
              >
                Simpan
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 5: Audit Trail Explorer Tab (Industrial CAD) -->
    <div v-if="activeTab === 'audit'" class="space-y-4">
      <!-- Search & Filters -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-3 font-mono">
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <div class="relative w-full sm:w-80">
            <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              v-model="auditSearchQuery"
              type="text"
              placeholder="CARI EVENT / USER / ENTITAS / IP..."
              class="w-full pl-9 pr-3 py-2 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs text-white placeholder-slate-500 font-mono tracking-wider focus:outline-none"
            />
          </div>

          <select
            v-model="auditActionFilter"
            class="px-3 py-2 bg-[#13171F] border border-[#232A36] focus:border-[#FACC15] rounded-xs text-xs font-mono text-slate-300 focus:outline-none"
          >
            <option value="">SEMUA EVENT ({{ auditLogs.length }})</option>
            <option value="LOGIN">LOGIN</option>
            <option value="CREATE">CREATE</option>
            <option value="UPDATE">UPDATE</option>
            <option value="DELETE">DELETE</option>
            <option value="GENERATE_BA">GENERATE_BA</option>
            <option value="SYSTEM">SYSTEM</option>
          </select>
        </div>

        <button
          @click="loadData"
          class="px-3.5 py-2 bg-[#13171F] hover:bg-[#1A202C] text-slate-300 hover:text-white border border-[#232A36] hover:border-[#FACC15]/50 rounded-xs text-xs font-bold flex items-center gap-1.5 cursor-pointer uppercase tracking-wider self-end sm:self-auto"
        >
          <RefreshCw :class="['w-3.5 h-3.5 text-[#FACC15]', loading ? 'animate-spin' : '']" />
          <span>Muat Ulang Log</span>
        </button>
      </div>

      <!-- Audit Logs Table -->
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs shadow-md overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs font-mono">
            <thead class="bg-[#0E1117] text-slate-400 text-[10px] uppercase tracking-wider border-b border-[#232A36]">
              <tr>
                <th class="py-3 px-4">Waktu (WIB)</th>
                <th class="py-3 px-4">Pengguna</th>
                <th class="py-3 px-4">Aksi / Event</th>
                <th class="py-3 px-4">Entitas</th>
                <th class="py-3 px-4">Detail Perubahan</th>
                <th class="py-3 px-4 text-center">Alamat IP</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#232A36] text-slate-300 text-[11px]">
              <template v-if="loading">
                <tr>
                  <td colspan="6" class="py-10 text-center text-slate-500 font-mono uppercase tracking-wider">Memuat log audit...</td>
                </tr>
              </template>
              <template v-else-if="filteredAuditLogs.length > 0">
                <tr v-for="log in filteredAuditLogs" :key="log.id" class="hover:bg-[#1A202C]/60 transition-colors">
                  <td class="py-3.5 px-4 whitespace-nowrap text-slate-400">
                    {{ new Date(log.created_at).toLocaleString('id-ID') }}
                  </td>
                  <td class="py-3.5 px-4 font-bold text-white">{{ log.user_name || 'System' }}</td>
                  <td class="py-3.5 px-4">
                    <span :class="[
                      'px-2 py-0.5 rounded-xs text-[10px] font-bold border uppercase tracking-wider',
                      log.action.includes('CREATE') ? 'bg-emerald-950/40 text-emerald-400 border-emerald-500/40' :
                      log.action.includes('UPDATE') ? 'bg-blue-950/40 text-blue-400 border-blue-500/40' :
                      log.action.includes('DELETE') ? 'bg-rose-950/40 text-rose-400 border-rose-500/40' :
                      log.action.includes('LOGIN') ? 'bg-[#FACC15]/10 text-[#FACC15] border-[#FACC15]/40' : 'bg-slate-900 text-slate-400 border-slate-700'
                    ]">
                      {{ log.action }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 font-bold text-[#FACC15]">{{ log.entity_type }} #{{ log.entity_id || '-' }}</td>
                  <td class="py-3.5 px-4 truncate max-w-xs text-slate-400 text-[11px]">
                    {{ log.new_value ? (typeof log.new_value === 'string' ? log.new_value : JSON.stringify(log.new_value)) : '-' }}
                  </td>
                  <td class="py-3.5 px-4 text-center text-slate-400">{{ log.ip_address || '127.0.0.1' }}</td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td colspan="6" class="py-10 text-center text-slate-500 uppercase tracking-wider">
                    Tidak ada log audit yang sesuai dengan filter.
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add / Edit User Modal (Industrial CAD) -->
    <div v-if="showUserModal" class="fixed inset-0 z-50 bg-[#0B0C0E]/80 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-[#13171F] border border-[#232A36] rounded-xs max-w-md w-full shadow-2xl p-6 space-y-4 text-xs font-mono text-white">
        <div class="flex items-center justify-between border-b border-[#232A36] pb-3">
          <h3 class="font-bold text-sm text-white uppercase tracking-wider flex items-center gap-2">
            <Users class="w-4 h-4 text-[#FACC15]" />
            <span>{{ isEditingUser ? 'Edit Akun Pengguna' : 'Tambah Akun Pengguna Baru' }}</span>
          </h3>
          <button @click="showUserModal = false" class="text-slate-400 hover:text-white cursor-pointer">
            ✕
          </button>
        </div>

        <form @submit.prevent="handleSaveUser" class="space-y-3 font-mono">
          <div>
            <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">Nama Lengkap *</label>
            <input
              required
              type="text"
              placeholder="Contoh: Dian Anggraini"
              v-model="newUser.name"
              class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] text-white rounded-xs focus:outline-none text-xs"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">Email Login *</label>
            <input
              required
              type="email"
              placeholder="dian.admin@sgx.com"
              v-model="newUser.email"
              class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] text-white rounded-xs focus:outline-none text-xs"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">Nomor Telepon / WhatsApp</label>
            <input
              type="text"
              placeholder="0812xxxxxxxx"
              v-model="newUser.phone"
              class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] text-white rounded-xs focus:outline-none text-xs"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">Role Hak Akses *</label>
            <select
              v-model="newUser.role"
              class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] text-white rounded-xs focus:outline-none text-xs font-mono"
            >
              <option value="ADMIN">ADMIN (Operasional & SPK)</option>
              <option value="FIELD_TEAM">FIELD_TEAM (Tim Lapangan Mobile)</option>
              <option value="VENDOR">CLIENT (Pemberi Tugas / Indomarco / Smartfren)</option>
              <option value="SUPERUSER">SUPERUSER (Administrator Sistem)</option>
            </select>
          </div>

          <div v-if="newUser.role === 'VENDOR'">
            <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">Pilih Perusahaan Client *</label>
            <select
              required
              v-model="newUser.vendor_id"
              class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] text-white rounded-xs focus:outline-none text-xs font-mono"
            >
              <option value="">-- Pilih Perusahaan Client --</option>
              <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }} ({{ v.code }})</option>
            </select>
          </div>

          <div>
            <label class="block font-bold text-slate-300 mb-1 uppercase tracking-wider text-[11px]">
              {{ isEditingUser ? 'Password Baru (Kosongkan jika tidak diubah)' : 'Password Awal' }}
            </label>
            <input
              type="text"
              placeholder="admin123"
              v-model="newUser.password"
              class="w-full px-3 py-2 bg-[#0B0C0E] border border-[#232A36] focus:border-[#FACC15] text-white rounded-xs focus:outline-none text-xs font-mono"
            />
          </div>

          <div class="pt-3 border-t border-[#232A36] flex items-center justify-end gap-2">
            <button
              type="button"
              @click="showUserModal = false"
              class="px-4 py-2 bg-[#1C2028] hover:bg-[#232A36] text-slate-300 hover:text-white border border-[#232A36] rounded-xs cursor-pointer uppercase tracking-wider"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-5 py-2 bg-[#FACC15] hover:bg-[#FFE033] text-[#0B0C0E] font-bold rounded-xs shadow-xs active:scale-95 transition-all cursor-pointer uppercase tracking-wider"
            >
              {{ isEditingUser ? 'Simpan Perubahan' : 'Buat Pengguna' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { api } from '../../services/api';
import PermissionMatrix from '../../components/PermissionMatrix.vue';
import ExecutiveDashboardView from '../../components/ExecutiveDashboardView.vue';
import {
  ShieldCheck,
  ShieldAlert,
  Users,
  Settings,
  History,
  Plus,
  RefreshCw,
  LayoutDashboard,
  Pencil,
  Trash2,
  Search,
  MessageSquare,
  Send,
  MapPin,
  Globe,
  AlertTriangle,
  Bell,
  Briefcase,
  Camera,
  CheckCircle2,
  FileCheck,
  Power,
  PowerOff,
  Download
} from 'lucide-vue-next';

function exportBaReport() {
  window.open('/api/reports/export-excel?period=MONTHLY', '_blank');
}

const props = defineProps({
  initialTab: {
    type: String,
    default: 'dashboard'
  }
});

const activeTab = ref(props.initialTab || 'dashboard');

watch(() => props.initialTab, (newTab) => {
  if (newTab) {
    activeTab.value = newTab;
  }
});

const users = ref([]);
const settings = ref([]);
const auditLogs = ref([]);
const vendors = ref([]);
const loading = ref(true);
const savingSetting = ref(false);

// WhatsApp Testing States
const fonnteApiKey = ref('');
const waGatewayEnabled = ref('1');
const testWaPhone = ref('');
const testWaMessage = ref('🔔 SGX System Test: Uji coba gateway WhatsApp Fonnte terkoneksi dengan sukses.');
const testingWa = ref(false);
const testWaStatus = ref(null);
const gatewayInfo = ref({
  state: 'UNCONFIGURED',
  token_configured: false,
  mock_enabled: false,
  total_sent: 0,
  total_failed: 0,
  success_rate: 0
});

// WhatsApp Notification Event Toggles (1 = Active, 0 = Disabled)
const waNotifSpkAssigned = ref('1');
const waNotifGpsCheckin = ref('1');
const waNotifEvidenceUpload = ref('1');
const waNotifIssueReported = ref('1');
const waNotifSpkSubmitted = ref('1');
const waNotifBaIssued = ref('1');

// Other Settings States
const geofenceRadius = ref('200');
const strictGps = ref('1');
const shaLock = ref('1');
const appName = ref('SGX Vendor Work Evidence');

// Filters
const userSearchQuery = ref('');
const userRoleFilter = ref('');
const auditSearchQuery = ref('');
const auditActionFilter = ref('');

const showUserModal = ref(false);
const isEditingUser = ref(false);
const newUser = ref({
  id: null,
  name: '',
  email: '',
  phone: '',
  password: 'admin123',
  role: 'ADMIN',
  vendor_id: ''
});

async function loadData() {
  loading.value = true;
  try {
    const results = await Promise.allSettled([
      api.getUsers(),
      api.getSettings(),
      api.getAuditLogs({ limit: 100 }),
      api.getVendors(),
      api.getGatewayStatus()
    ]);

    if (results[0].status === 'fulfilled' && results[0].value?.data) {
      users.value = results[0].value.data;
    }
    if (results[1].status === 'fulfilled' && results[1].value?.data) {
      settings.value = results[1].value.data;
      parseSettings();
    }
    if (results[2].status === 'fulfilled' && results[2].value?.data) {
      auditLogs.value = results[2].value.data;
    }
    if (results[3].status === 'fulfilled' && results[3].value?.data) {
      vendors.value = results[3].value.data;
    }
    if (results[4].status === 'fulfilled' && results[4].value?.data) {
      gatewayInfo.value = results[4].value.data;
    }
  } catch (err) {
    console.error('Failed to load superuser data:', err);
  } finally {
    loading.value = false;
  }
}

function parseSettings() {
  const getVal = (key, fallback) => {
    const item = settings.value.find(s => s.key === key);
    return item ? item.value : fallback;
  };
  fonnteApiKey.value = getVal('fonnte_api_key', '');
  waGatewayEnabled.value = String(getVal('wa_gateway_enabled', '1'));
  geofenceRadius.value = getVal('geofence_default_radius_meters', '200');
  strictGps.value = String(getVal('require_strict_gps', '1'));
  shaLock.value = String(getVal('sha256_integrity_lock', '1'));
  appName.value = getVal('app_name', 'SGX Vendor Work Evidence');

  // WhatsApp Event Toggles
  waNotifSpkAssigned.value = String(getVal('wa_notify_spk_assigned', '1'));
  waNotifGpsCheckin.value = String(getVal('wa_notify_gps_checkin', '1'));
  waNotifEvidenceUpload.value = String(getVal('wa_notify_evidence_upload', '1'));
  waNotifIssueReported.value = String(getVal('wa_notify_issue_reported', '1'));
  waNotifSpkSubmitted.value = String(getVal('wa_notify_spk_submitted', '1'));
  waNotifBaIssued.value = String(getVal('wa_notify_ba_issued', '1'));
}

const filteredUsers = computed(() => {
  return users.value.filter(u => {
    const matchSearch = !userSearchQuery.value ||
      (u.name?.toLowerCase().includes(userSearchQuery.value.toLowerCase())) ||
      (u.email?.toLowerCase().includes(userSearchQuery.value.toLowerCase())) ||
      (u.phone?.toLowerCase().includes(userSearchQuery.value.toLowerCase()));

    const matchRole = !userRoleFilter.value || (u.role === userRoleFilter.value);
    return matchSearch && matchRole;
  });
});

const filteredAuditLogs = computed(() => {
  return auditLogs.value.filter(log => {
    const q = auditSearchQuery.value.toLowerCase();
    const matchSearch = !q ||
      (log.action?.toLowerCase().includes(q)) ||
      (log.user_name?.toLowerCase().includes(q)) ||
      (log.entity_type?.toLowerCase().includes(q)) ||
      (log.ip_address?.toLowerCase().includes(q));

    const matchAction = !auditActionFilter.value || log.action?.includes(auditActionFilter.value);
    return matchSearch && matchAction;
  });
});

const refreshingGateway = ref(false);

async function refreshGatewayStatus() {
  refreshingGateway.value = true;
  try {
    const res = await api.getGatewayStatus();
    if (res?.data) {
      gatewayInfo.value = res.data;
    }
  } catch (err) {
    console.error('Failed to refresh gateway status:', err);
  } finally {
    refreshingGateway.value = false;
  }
}

async function saveSetting(key, value) {
  savingSetting.value = true;
  try {
    await api.updateSetting(key, value);
    alert(`Pengaturan '${key}' berhasil disimpan!`);
    loadData();
  } catch (err) {
    alert(`Gagal menyimpan pengaturan: ${err.message}`);
  } finally {
    savingSetting.value = false;
  }
}

async function saveFonnteApiKey() {
  if (!fonnteApiKey.value) return;
  savingSetting.value = true;
  try {
    await api.updateSetting('fonnte_api_key', fonnteApiKey.value.trim());
    await api.updateSetting('FONNTE_API_KEY', fonnteApiKey.value.trim());
    await loadData();
    alert(`Token WhatsApp Fonnte berhasil disimpan! Status Gateway: ${gatewayInfo.value.state === 'ACTIVE' ? 'ONLINE' : 'TERSIMPAN'}`);
  } catch (err) {
    alert(`Gagal menyimpan token: ${err.message}`);
  } finally {
    savingSetting.value = false;
  }
}

async function toggleWaGatewayPower() {
  const nextVal = waGatewayEnabled.value === '1' ? '0' : '1';
  waGatewayEnabled.value = nextVal;
  await saveSetting('wa_gateway_enabled', nextVal);
  await refreshGatewayStatus();
}

async function toggleStrictGps() {
  const nextVal = strictGps.value === '1' ? '0' : '1';
  strictGps.value = nextVal;
  await saveSetting('require_strict_gps', nextVal);
}

async function toggleShaLock() {
  const nextVal = shaLock.value === '1' ? '0' : '1';
  shaLock.value = nextVal;
  await saveSetting('sha256_integrity_lock', nextVal);
}

async function toggleWaNotif(key, currentRef) {
  const nextVal = currentRef === '1' ? '0' : '1';
  if (key === 'wa_notify_spk_assigned') waNotifSpkAssigned.value = nextVal;
  if (key === 'wa_notify_gps_checkin') waNotifGpsCheckin.value = nextVal;
  if (key === 'wa_notify_evidence_upload') waNotifEvidenceUpload.value = nextVal;
  if (key === 'wa_notify_issue_reported') waNotifIssueReported.value = nextVal;
  if (key === 'wa_notify_spk_submitted') waNotifSpkSubmitted.value = nextVal;
  if (key === 'wa_notify_ba_issued') waNotifBaIssued.value = nextVal;
  await saveSetting(key, nextVal);
}

async function handleTestWhatsApp() {
  if (!testWaPhone.value) return;
  testingWa.value = true;
  testWaStatus.value = null;

  try {
    const res = await api.testWhatsApp(testWaPhone.value, testWaMessage.value);
    testWaStatus.value = {
      success: true,
      message: res.message || 'Pesan uji coba WhatsApp berhasil dikirim!'
    };
    alert('Pesan uji coba WhatsApp berhasil dikirim!');
  } catch (err) {
    testWaStatus.value = {
      success: false,
      message: `Gagal kirim: ${err.message}`
    };
    alert(`Gagal kirim WA: ${err.message}`);
  } finally {
    testingWa.value = false;
  }
}

function openAddUserModal() {
  isEditingUser.value = false;
  newUser.value = {
    id: null,
    name: '',
    email: '',
    phone: '',
    password: 'admin123',
    role: 'ADMIN',
    vendor_id: ''
  };
  showUserModal.value = true;
}

function openEditUserModal(user) {
  isEditingUser.value = true;
  newUser.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    phone: user.phone || '',
    password: '',
    role: user.role,
    vendor_id: user.vendor_id || ''
  };
  showUserModal.value = true;
}

async function handleSaveUser() {
  try {
    if (isEditingUser.value) {
      await api.updateUser(newUser.value.id, newUser.value);
      alert('Data pengguna berhasil diperbarui!');
    } else {
      await api.createUser(newUser.value);
      alert('Pengguna baru berhasil dibuat!');
    }
    showUserModal.value = false;
    loadData();
  } catch (err) {
    alert(`Gagal menyimpan user: ${err.message}`);
  }
}

async function handleDeleteUser(user) {
  const confirmed = window.confirm(`Apakah Anda yakin ingin menghapus akun user "${user.name}" (${user.email})?`);
  if (!confirmed) return;

  try {
    await api.deleteUser(user.id);
    alert('User berhasil dihapus!');
    loadData();
  } catch (err) {
    alert(`Gagal menghapus user: ${err.message}`);
  }
}

onMounted(() => {
  loadData();
});
</script>
