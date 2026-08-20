<template>
  <div class="space-y-6">
    <!-- Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-purple-700 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-purple-700/20">
            <ShieldCheck class="w-4 h-4" />
          </div>
          <h2 class="text-xl font-black text-slate-900 tracking-tight">Superuser System Console</h2>
        </div>
        <p class="text-xs text-slate-500 mt-1 font-medium">Pusat administrasi sistem, otorisasi RBAC, konfigurasi gateway, dan audit keamanan.</p>
      </div>
      <button
        @click="loadData"
        class="px-3.5 py-2 glass-card hover:bg-white rounded-xl text-slate-700 hover:text-slate-900 text-xs font-bold flex items-center gap-2 shadow-xs transition-all duration-200 active:scale-95 border border-slate-200/80 self-start sm:self-auto cursor-pointer"
      >
        <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin' : '']" />
        <span>Segarkan Data</span>
      </button>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex gap-2 border-b border-slate-200/80 pb-2.5 text-xs font-bold overflow-x-auto">
      <button
        @click="activeTab = 'dashboard'"
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer whitespace-nowrap',
          activeTab === 'dashboard' ? 'bg-purple-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100/80'
        ]"
      >
        <LayoutDashboard class="w-4 h-4" />
        <span>Dashboard Eksekutif</span>
      </button>

      <button
        @click="activeTab = 'permissions'"
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer whitespace-nowrap',
          activeTab === 'permissions' ? 'bg-purple-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100/80'
        ]"
      >
        <ShieldAlert class="w-4 h-4" />
        <span>Hak Akses & Role (CRUD)</span>
      </button>

      <button
        @click="activeTab = 'users'"
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer whitespace-nowrap',
          activeTab === 'users' ? 'bg-purple-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100/80'
        ]"
      >
        <Users class="w-4 h-4" />
        <span>Pengguna & Akun ({{ users.length }})</span>
      </button>

      <button
        @click="activeTab = 'settings'"
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer whitespace-nowrap',
          activeTab === 'settings' ? 'bg-purple-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100/80'
        ]"
      >
        <Settings class="w-4 h-4" />
        <span>Pengaturan Gateway & Sistem</span>
      </button>

      <button
        @click="activeTab = 'audit'"
        :class="[
          'flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 cursor-pointer whitespace-nowrap',
          activeTab === 'audit' ? 'bg-purple-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100/80'
        ]"
      >
        <History class="w-4 h-4" />
        <span>Audit Trail Lengkap ({{ auditLogs.length }})</span>
      </button>
    </div>

    <!-- TAB 1: Executive Dashboard Tab -->
    <div v-if="activeTab === 'dashboard'">
      <ExecutiveDashboardView />
    </div>

    <!-- TAB 2: Permissions Matrix Tab -->
    <div v-if="activeTab === 'permissions'">
      <PermissionMatrix />
    </div>

    <!-- TAB 3: Users Management Tab -->
    <div v-if="activeTab === 'users'" class="space-y-4">
      <!-- Search & Action Bar -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <div class="relative w-full sm:w-72">
            <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              v-model="userSearchQuery"
              type="text"
              placeholder="Cari nama, email, atau telepon..."
              class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-purple-700 focus:outline-none shadow-2xs"
            />
          </div>

          <select
            v-model="userRoleFilter"
            class="px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-purple-700 shadow-2xs"
          >
            <option value="">Semua Role ({{ users.length }})</option>
            <option value="SUPERUSER">SUPERUSER</option>
            <option value="ADMIN">ADMIN</option>
            <option value="FIELD_TEAM">FIELD_TEAM</option>
            <option value="VENDOR">CLIENT / VENDOR</option>
          </select>
        </div>

        <button
          @click="openAddUserModal"
          class="px-4 py-2 bg-gradient-to-r from-purple-900 to-indigo-800 hover:from-purple-800 hover:to-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-md shadow-purple-900/20 active:scale-95 transition-all cursor-pointer whitespace-nowrap self-end sm:self-auto"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Pengguna Baru</span>
        </button>
      </div>

      <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-100/70 text-slate-500 font-bold border-b border-slate-200/80">
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
            <tbody class="divide-y divide-slate-100/80 text-slate-700">
              <template v-if="loading">
                <tr>
                  <td colspan="7" class="py-10 text-center text-slate-400 font-medium">Memuat data pengguna...</td>
                </tr>
              </template>
              <template v-else-if="filteredUsers.length > 0">
                <tr v-for="u in filteredUsers" :key="u.id" class="hover:bg-purple-50/30 transition-colors">
                  <td class="py-3.5 px-4 font-bold text-slate-900">{{ u.name }}</td>
                  <td class="py-3.5 px-4 font-mono">{{ u.email }}</td>
                  <td class="py-3.5 px-4 font-mono text-slate-600">{{ u.phone || '-' }}</td>
                  <td class="py-3.5 px-4">
                    <span :class="[
                      'px-2.5 py-0.5 rounded-full font-bold text-[10px] border',
                      u.role === 'SUPERUSER' ? 'bg-purple-100 text-purple-800 border-purple-200' :
                      u.role === 'ADMIN' ? 'bg-blue-100 text-blue-800 border-blue-200' :
                      u.role === 'FIELD_TEAM' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200'
                    ]">
                      {{ u.role === 'VENDOR' ? 'CLIENT' : u.role }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 font-medium">{{ u.vendor_name || '-' }}</td>
                  <td class="py-3.5 px-4">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                      AKTIF ✓
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                      <button
                        @click="openEditUserModal(u)"
                        title="Edit Pengguna"
                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-purple-100 hover:text-purple-800 text-slate-600 transition-colors active:scale-90 cursor-pointer"
                      >
                        <Pencil class="w-3.5 h-3.5" />
                      </button>
                      <button
                        @click="handleDeleteUser(u)"
                        title="Hapus Pengguna"
                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-100 hover:text-rose-700 text-slate-600 transition-colors active:scale-90 cursor-pointer"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td colspan="7" class="py-10 text-center text-slate-400 font-medium">Tidak ada pengguna yang sesuai dengan filter.</td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 4: Gateway & System Settings Tab (Enterprise Layout) -->
    <div v-if="activeTab === 'settings'" class="space-y-5">
      <!-- 1. WhatsApp Gateway Configuration & Real-Time Test Card -->
      <div class="glass-card rounded-3xl p-6 shadow-glass border border-white/80 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200/80 pb-3">
          <div class="flex items-center gap-2.5">
            <div
              :class="[
                'w-8 h-8 rounded-xl text-white flex items-center justify-center shadow-sm',
                gatewayInfo.state === 'ACTIVE'
                  ? 'bg-emerald-600'
                  : gatewayInfo.state === 'MOCK'
                  ? 'bg-amber-600'
                  : 'bg-rose-600'
              ]"
            >
              <MessageSquare class="w-4 h-4" />
            </div>
            <div>
              <h3 class="font-black text-sm text-slate-900">WhatsApp Gateway (Fonnte API)</h3>
              <p class="text-[11px] text-slate-500">Integrasi pengiriman pesan notifikasi otomatis untuk SPK, Check-In, dan Berita Acara.</p>
            </div>
          </div>
          <div class="flex items-center gap-2 self-start sm:self-auto">
            <span
              :class="[
                'px-2.5 py-1 rounded-full text-[10px] font-bold border flex items-center gap-1.5',
                gatewayInfo.state === 'ACTIVE'
                  ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                  : gatewayInfo.state === 'MOCK'
                  ? 'bg-amber-50 text-amber-800 border-amber-200'
                  : 'bg-rose-50 text-rose-800 border-rose-200'
              ]"
            >
              <span
                :class="[
                  'w-2 h-2 rounded-full',
                  gatewayInfo.state === 'ACTIVE'
                    ? 'bg-emerald-500 animate-pulse'
                    : gatewayInfo.state === 'MOCK'
                    ? 'bg-amber-500'
                    : 'bg-rose-500'
                ]"
              ></span>
              <span>
                {{ gatewayInfo.state === 'ACTIVE' ? 'Gateway Fonnte Aktif' : gatewayInfo.state === 'MOCK' ? 'Mode Mock (Testing)' : 'Belum Dikonfigurasi (Offline)' }}
              </span>
            </span>

            <button
              type="button"
              @click="refreshGatewayStatus"
              :disabled="refreshingGateway"
              title="Periksa Ulang Status Gateway"
              class="p-1.5 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-slate-600 hover:text-slate-900 cursor-pointer shadow-2xs text-[10px] font-bold flex items-center gap-1"
            >
              <RefreshCw :class="['w-3 h-3', refreshingGateway ? 'animate-spin text-purple-700' : '']" />
            </button>
          </div>
        </div>

        <!-- Banner 1: Gateway Online / Aktif (Hijau) -->
        <div
          v-if="gatewayInfo.state === 'ACTIVE'"
          class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-950 text-xs font-medium shadow-xs"
        >
          <ShieldCheck class="w-5 h-5 text-emerald-600 shrink-0" />
          <div class="flex-1">
            <div class="font-bold flex items-center gap-2">
              <span>WhatsApp Gateway Siap & Terhubung Normal</span>
              <span v-if="gatewayInfo.masked_token" class="font-mono text-[10px] bg-emerald-200/80 text-emerald-900 px-2 py-0.5 rounded-md">
                Token: {{ gatewayInfo.masked_token }}
              </span>
            </div>
            <p class="text-[11px] text-emerald-700 mt-0.5">
              Notifikasi otomatis pengerjaan SPK, presensi GPS, dan Berita Acara siap dikirimkan ke nomor WhatsApp penerima.
            </p>
          </div>
        </div>

        <!-- Banner 2: Token sudah diisi tapi belum disimpan / Belum Dikonfigurasi -->
        <div
          v-else-if="gatewayInfo.state === 'UNCONFIGURED' && fonnteApiKey && fonnteApiKey.trim().length > 5"
          class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-3 text-amber-950 text-xs font-medium shadow-xs"
        >
          <AlertTriangle class="w-5 h-5 text-amber-600 shrink-0" />
          <div class="flex-1">
            <div class="font-bold">💡 Token Fonnte telah dimasukkan</div>
            <p class="text-[11px] text-amber-800 mt-0.5">
              Klik tombol <span class="font-bold underline">"Simpan Token"</span> di bawah untuk mengaktifkan koneksi WhatsApp Gateway ke sistem.
            </p>
          </div>
        </div>

        <!-- Banner 3: Token Kosong (Merah) -->
        <div
          v-else-if="gatewayInfo.state === 'UNCONFIGURED'"
          class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-rose-900 text-xs font-medium shadow-xs"
        >
          <ShieldAlert class="w-5 h-5 text-rose-600 shrink-0" />
          <div class="flex-1">
            <div class="font-bold">⚠️ WhatsApp Gateway belum terkonfigurasi</div>
            <p class="text-[11px] text-rose-700 mt-0.5">
              API Token Fonnte masih kosong atau bernilai placeholder. Silakan masukkan token resmi dari dashboard Fonnte Anda di bawah ini lalu klik Simpan Token.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-1">
          <!-- Left: API Key Configuration -->
          <div class="p-4 bg-white/80 border border-slate-200/80 rounded-2xl space-y-3 shadow-xs">
            <div>
              <label class="block font-bold text-slate-800 text-xs mb-1">Fonnte API Token / Secret Key</label>
              <p class="text-[11px] text-slate-500 mb-2">Token API resmi dari akun Fonnte Anda untuk otorisasi pengiriman pesan WhatsApp.</p>
              <div class="flex items-center gap-2">
                <input
                  type="text"
                  v-model="fonnteApiKey"
                  placeholder="Masukkan API Token Fonnte..."
                  class="w-full px-3.5 py-2 border border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-purple-700 font-mono text-xs shadow-xs"
                />
                <button
                  @click="saveFonnteKey"
                  :disabled="savingSetting"
                  class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl whitespace-nowrap shadow-xs active:scale-95 transition-all cursor-pointer text-xs"
                >
                  {{ savingSetting ? 'Menyimpan...' : 'Simpan Token' }}
                </button>
              </div>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl text-[11px] text-slate-600 space-y-1 font-mono">
              <div class="font-bold text-slate-800">📌 Info Endpoint Fonnte:</div>
              <div>URL: <span class="text-emerald-700 font-semibold">https://api.fonnte.com/send</span></div>
              <div>Format Target: 628xxxxxxxxxx</div>
            </div>
          </div>

          <!-- Right: Test Send WhatsApp Message Panel -->
          <div class="p-4 bg-purple-50/50 border border-purple-200/80 rounded-2xl space-y-3 shadow-xs">
            <div>
              <h4 class="font-bold text-purple-950 text-xs flex items-center gap-1.5">
                <Send class="w-3.5 h-3.5 text-purple-700" />
                <span>Uji Coba Pengiriman Notifikasi WhatsApp</span>
              </h4>
              <p class="text-[11px] text-slate-600 mt-0.5">Kirim pesan pengujian langsung ke nomor tujuan untuk memastikan gateway berfungsi.</p>
            </div>

            <div class="space-y-2 text-xs">
              <div>
                <label class="block font-bold text-slate-700 text-[11px] mb-1">Nomor WhatsApp Tujuan</label>
                <input
                  type="text"
                  v-model="testWaPhone"
                  placeholder="Contoh: 081234567890"
                  class="w-full px-3 py-1.5 border border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-purple-700 font-mono text-xs"
                />
              </div>

              <div>
                <label class="block font-bold text-slate-700 text-[11px] mb-1">Isi Pesan Uji Coba</label>
                <textarea
                  v-model="testWaMessage"
                  rows="2"
                  class="w-full px-3 py-1.5 border border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-purple-700 text-xs font-mono"
                  placeholder="Tulis pesan pengujian..."
                ></textarea>
              </div>

              <div class="flex items-center justify-between pt-1">
                <span v-if="testWaStatus" :class="[
                  'text-[11px] font-bold',
                  testWaStatus.success ? 'text-emerald-700' : 'text-rose-700'
                ]">
                  {{ testWaStatus.message }}
                </span>
                <span v-else></span>

                <button
                  type="button"
                  @click="handleTestWhatsApp"
                  :disabled="testingWa || !testWaPhone"
                  class="px-4 py-2 bg-gradient-to-r from-purple-900 to-indigo-800 hover:from-purple-800 hover:to-indigo-700 text-white font-bold rounded-xl shadow-xs active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50 text-xs ml-auto"
                >
                  <Send :class="['w-3.5 h-3.5', testingWa ? 'animate-spin' : '']" />
                  <span>{{ testingWa ? 'Mengirim...' : 'Kirim Test WA' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Security & Geofencing GPS Parameters Card -->
      <div class="glass-card rounded-3xl p-6 shadow-glass border border-white/80 space-y-4">
        <div class="flex items-center gap-2.5 border-b border-slate-200/80 pb-3">
          <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-sm">
            <MapPin class="w-4 h-4" />
          </div>
          <div>
            <h3 class="font-black text-sm text-slate-900">Keamanan Geofencing GPS & Integritas Evidensi</h3>
            <p class="text-[11px] text-slate-500">Konfigurasi radius toleransi presensi teknisi lapangan dan segel kriptografi foto bukti.</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
          <!-- Radius Geofencing -->
          <div class="p-4 bg-white/80 border border-slate-200/80 rounded-2xl space-y-2 shadow-xs flex flex-col justify-between">
            <div>
              <div class="font-bold text-slate-900">Radius Geofencing (Meter)</div>
              <p class="text-[11px] text-slate-500 mt-0.5">Toleransi jarak maksimum GPS teknisi dari titik toko saat check-in.</p>
            </div>
            <div class="flex items-center gap-2 pt-2">
              <input
                type="number"
                v-model="geofenceRadius"
                class="w-full px-3 py-1.5 border border-slate-200 rounded-xl bg-white font-mono text-xs"
              />
              <button
                @click="saveSetting('geofence_default_radius_meters', geofenceRadius)"
                class="px-3.5 py-1.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl text-xs cursor-pointer"
              >
                Simpan
              </button>
            </div>
          </div>

          <!-- Strict GPS Toggle -->
          <div class="p-4 bg-white/80 border border-slate-200/80 rounded-2xl space-y-2 shadow-xs flex flex-col justify-between">
            <div>
              <div class="font-bold text-slate-900">Validasi GPS Ketat</div>
              <p class="text-[11px] text-slate-500 mt-0.5">Wajibkan akurasi GPS terverifikasi browser sebelum upload foto.</p>
            </div>
            <div class="flex items-center justify-between pt-2">
              <span class="font-bold text-xs font-mono" :class="strictGps === '1' ? 'text-emerald-700' : 'text-slate-500'">
                {{ strictGps === '1' ? 'AKTIF (1)' : 'NONAKTIF (0)' }}
              </span>
              <button
                @click="toggleStrictGps"
                class="px-3.5 py-1.5 rounded-xl font-bold text-xs cursor-pointer transition-all"
                :class="strictGps === '1' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'"
              >
                {{ strictGps === '1' ? 'Nonaktifkan' : 'Aktifkan' }}
              </button>
            </div>
          </div>

          <!-- SHA256 Integrity Lock Toggle -->
          <div class="p-4 bg-white/80 border border-slate-200/80 rounded-2xl space-y-2 shadow-xs flex flex-col justify-between">
            <div>
              <div class="font-bold text-slate-900">Segel Integritas SHA-256</div>
              <p class="text-[11px] text-slate-500 mt-0.5">Kunci hash kriptografis untuk membuktikan keaslian foto tanpa manipulasi.</p>
            </div>
            <div class="flex items-center justify-between pt-2">
              <span class="font-bold text-xs font-mono" :class="shaLock === '1' ? 'text-emerald-700' : 'text-slate-500'">
                {{ shaLock === '1' ? 'AKTIF (1)' : 'NONAKTIF (0)' }}
              </span>
              <button
                @click="toggleShaLock"
                class="px-3.5 py-1.5 rounded-xl font-bold text-xs cursor-pointer transition-all"
                :class="shaLock === '1' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'"
              >
                {{ shaLock === '1' ? 'Nonaktifkan' : 'Aktifkan' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Identity & General Platform Settings Card -->
      <div class="glass-card rounded-3xl p-6 shadow-glass border border-white/80 space-y-4">
        <div class="flex items-center gap-2.5 border-b border-slate-200/80 pb-3">
          <div class="w-8 h-8 rounded-xl bg-purple-700 text-white flex items-center justify-center shadow-sm">
            <Globe class="w-4 h-4" />
          </div>
          <div>
            <h3 class="font-black text-sm text-slate-900">Identitas & Metadata Platform</h3>
            <p class="text-[11px] text-slate-500">Nama resmi aplikasi yang tercantum pada dokumen PDF Berita Acara (BA) dan notifikasi.</p>
          </div>
        </div>

        <div class="p-4 bg-white/80 border border-slate-200/80 rounded-2xl space-y-3 shadow-xs text-xs">
          <div>
            <label class="block font-bold text-slate-800 text-xs mb-1">Nama Resmi Platform Aplikasi</label>
            <div class="flex items-center gap-2">
              <input
                type="text"
                v-model="appName"
                class="w-full px-3.5 py-2 border border-slate-200 rounded-xl bg-white font-medium text-xs shadow-xs"
              />
              <button
                @click="saveSetting('app_name', appName)"
                class="px-4 py-2 bg-purple-900 hover:bg-purple-800 text-white font-bold rounded-xl text-xs cursor-pointer"
              >
                Simpan
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 5: Audit Trail Explorer Tab -->
    <div v-if="activeTab === 'audit'" class="space-y-4">
      <!-- Search & Filters -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <div class="relative w-full sm:w-72">
            <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              v-model="auditSearchQuery"
              type="text"
              placeholder="Cari event, pengguna, entitas, atau IP..."
              class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-purple-700 focus:outline-none shadow-2xs"
            />
          </div>

          <select
            v-model="auditActionFilter"
            class="px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-purple-700 shadow-2xs"
          >
            <option value="">Semua Event ({{ auditLogs.length }})</option>
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
          class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-2xs self-end sm:self-auto"
        >
          <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin' : '']" />
          <span>Muat Ulang Log</span>
        </button>
      </div>

      <!-- Audit Logs Table -->
      <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-100/70 text-slate-500 font-bold border-b border-slate-200/80">
              <tr>
                <th class="py-3 px-4">Waktu (WIB)</th>
                <th class="py-3 px-4">Pengguna</th>
                <th class="py-3 px-4">Aksi / Event</th>
                <th class="py-3 px-4">Entitas</th>
                <th class="py-3 px-4">Detail Perubahan</th>
                <th class="py-3 px-4 text-center">Alamat IP</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80 text-slate-700 font-mono text-[11px]">
              <template v-if="loading">
                <tr>
                  <td colspan="6" class="py-10 text-center text-slate-400 font-medium font-sans">Memuat log audit...</td>
                </tr>
              </template>
              <template v-else-if="filteredAuditLogs.length > 0">
                <tr v-for="log in filteredAuditLogs" :key="log.id" class="hover:bg-purple-50/30 transition-colors">
                  <td class="py-3.5 px-4 whitespace-nowrap text-slate-600">
                    {{ new Date(log.created_at).toLocaleString('id-ID') }}
                  </td>
                  <td class="py-3.5 px-4 font-bold text-slate-900 font-sans">{{ log.user_name || 'System' }}</td>
                  <td class="py-3.5 px-4">
                    <span :class="[
                      'px-2 py-0.5 rounded-full text-[10px] font-bold border',
                      log.action.includes('CREATE') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                      log.action.includes('UPDATE') ? 'bg-blue-50 text-blue-700 border-blue-200' :
                      log.action.includes('DELETE') ? 'bg-rose-50 text-rose-700 border-rose-200' :
                      log.action.includes('LOGIN') ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-slate-100 text-slate-700 border-slate-200'
                    ]">
                      {{ log.action }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 font-bold text-slate-800">{{ log.entity_type }} #{{ log.entity_id || '-' }}</td>
                  <td class="py-3.5 px-4 truncate max-w-xs font-sans text-slate-600 text-[11px]">
                    {{ log.new_value ? (typeof log.new_value === 'string' ? log.new_value : JSON.stringify(log.new_value)) : '-' }}
                  </td>
                  <td class="py-3.5 px-4 text-center text-slate-400">{{ log.ip_address || '127.0.0.1' }}</td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td colspan="6" class="py-10 text-center text-slate-400 font-medium font-sans">
                    Tidak ada log audit yang sesuai dengan filter.
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add / Edit User Modal -->
    <div v-if="showUserModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="glass-modal rounded-3xl max-w-md w-full shadow-2xl p-6 space-y-4 text-xs border border-white/80">
        <h3 class="font-black text-sm text-slate-900 border-b border-slate-200/80 pb-2.5">
          {{ isEditingUser ? 'Edit Akun Pengguna' : 'Tambah Akun Pengguna Baru' }}
        </h3>

        <form @submit.prevent="handleSaveUser" class="space-y-3">
          <div>
            <label class="block font-bold mb-1">Nama Lengkap *</label>
            <input
              required
              type="text"
              placeholder="Contoh: Dian Anggraini"
              v-model="newUser.name"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl"
            />
          </div>

          <div>
            <label class="block font-bold mb-1">Email Login *</label>
            <input
              required
              type="email"
              placeholder="dian.admin@sgx.com"
              v-model="newUser.email"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl"
            />
          </div>

          <div>
            <label class="block font-bold mb-1">Nomor Telepon / WhatsApp</label>
            <input
              type="text"
              placeholder="0812xxxxxxxx"
              v-model="newUser.phone"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl"
            />
          </div>

          <div>
            <label class="block font-bold mb-1">Role Hak Akses *</label>
            <select
              v-model="newUser.role"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-medium"
            >
              <option value="ADMIN">ADMIN (Operasional & SPK)</option>
              <option value="FIELD_TEAM">FIELD_TEAM (Tim Lapangan Mobile)</option>
              <option value="VENDOR">CLIENT (Pemberi Tugas / Indomarco / Smartfren)</option>
              <option value="SUPERUSER">SUPERUSER (Administrator Sistem)</option>
            </select>
          </div>

          <div v-if="newUser.role === 'VENDOR'">
            <label class="block font-bold mb-1">Pilih Perusahaan Client *</label>
            <select
              required
              v-model="newUser.vendor_id"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl"
            >
              <option value="">-- Pilih Perusahaan Client --</option>
              <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }} ({{ v.code }})</option>
            </select>
          </div>

          <div>
            <label class="block font-bold mb-1">
              {{ isEditingUser ? 'Password Baru (Kosongkan jika tidak diubah)' : 'Password Awal' }}
            </label>
            <input
              type="text"
              placeholder="admin123"
              v-model="newUser.password"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-mono"
            />
          </div>

          <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="showUserModal = false"
              class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-5 py-2 bg-purple-900 hover:bg-purple-800 text-white font-bold rounded-xl shadow-xs active:scale-95 transition-all cursor-pointer"
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
  AlertTriangle
} from 'lucide-vue-next';

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
  geofenceRadius.value = getVal('geofence_default_radius_meters', '200');
  strictGps.value = String(getVal('require_strict_gps', '1'));
  shaLock.value = String(getVal('sha256_integrity_lock', '1'));
  appName.value = getVal('app_name', 'SGX Vendor Work Evidence');
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
    if (res.data) {
      gatewayInfo.value = res.data;
    }
  } catch (err) {
    console.warn('Failed to refresh gateway status:', err);
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

async function saveFonnteKey() {
  const token = (fonnteApiKey.value || '').trim();
  if (!token) {
    alert('Mohon masukkan Token Fonnte terlebih dahulu.');
    return;
  }
  savingSetting.value = true;
  try {
    await api.updateSetting('fonnte_api_key', token);
    await refreshGatewayStatus();
    alert('Token WhatsApp Fonnte berhasil disimpan! Status Gateway: ONLINE');
    loadData();
  } catch (err) {
    alert(`Gagal menyimpan token: ${err.message}`);
  } finally {
    savingSetting.value = false;
  }
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
