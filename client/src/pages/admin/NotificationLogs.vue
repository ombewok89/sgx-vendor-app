<template>
  <div class="space-y-5">
    <!-- Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-purple-800 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-purple-900/20">
            <Bell class="w-4 h-4" />
          </div>
          <span>Pusat Notifikasi & Log Pengiriman Pesan</span>
        </h2>
        <p class="text-xs text-slate-500 mt-1 font-medium">
          Monitoring feed informasi progres pekerjaan SPK real-time dan log pengiriman notifikasi WhatsApp gateway Fonnte.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          v-if="activeTab === 'wa'"
          @click="showTestModal = true"
          class="px-3.5 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-xs transition-all active:scale-95 cursor-pointer"
        >
          <Send class="w-3.5 h-3.5" />
          <span>Uji Coba WhatsApp</span>
        </button>

        <button
          @click="activeTab === 'inapp' ? loadInAppNotifications() : loadWaLogs()"
          class="px-3.5 py-2 glass-card hover:bg-white rounded-xl text-slate-700 hover:text-slate-900 text-xs font-bold flex items-center gap-2 shadow-xs transition-all duration-200 active:scale-95 border border-slate-200/80 cursor-pointer"
        >
          <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin' : '']" />
          <span>Muat Ulang</span>
        </button>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-1 text-xs font-bold">
      <button
        @click="activeTab = 'inapp'"
        :class="[
          'px-4 py-2 rounded-xl transition-all cursor-pointer flex items-center gap-2',
          activeTab === 'inapp'
            ? 'bg-purple-900 text-white shadow-xs'
            : 'text-slate-600 hover:text-slate-900 hover:bg-white'
        ]"
      >
        <Bell class="w-3.5 h-3.5" />
        <span>Feed Notifikasi Pekerjaan In-App ({{ inAppList.length }})</span>
      </button>

      <button
        @click="activeTab = 'wa'"
        :class="[
          'px-4 py-2 rounded-xl transition-all cursor-pointer flex items-center gap-2',
          activeTab === 'wa'
            ? 'bg-purple-900 text-white shadow-xs'
            : 'text-slate-600 hover:text-slate-900 hover:bg-white'
        ]"
      >
        <MessageSquare class="w-3.5 h-3.5" />
        <span>WhatsApp Gateway Logs ({{ waLogs.length }})</span>
      </button>
    </div>

    <!-- TAB 1: In-App Notifications Feed Table -->
    <div v-if="activeTab === 'inapp'" class="space-y-4">
      <!-- Search & Filters -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-72">
          <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari notifikasi / nomor SPK / lokasi..."
            class="w-full pl-9 pr-3 py-2 bg-white/80 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-purple-700 focus:outline-none"
          />
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
          <select
            v-model="filterCategory"
            class="px-3 py-2 bg-white/80 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-purple-700"
          >
            <option value="">Semua Kategori</option>
            <option value="GPS_CHECKIN">Check-in GPS Teknisi</option>
            <option value="EVIDENCE_UPLOAD">Upload Evidensi Foto</option>
            <option value="ISSUE_REPORTED">Kendala Lapangan</option>
            <option value="BA_ISSUED">Berita Acara (BA) Terbit</option>
          </select>
        </div>
      </div>

      <!-- Feed List Container -->
      <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-100/70 text-slate-500 font-bold border-b border-slate-200/80">
              <tr>
                <th class="py-3.5 px-4">Waktu</th>
                <th class="py-3.5 px-4">Kategori & Event</th>
                <th class="py-3.5 px-4">No. SPK & Cabang</th>
                <th class="py-3.5 px-4">Judul Notifikasi</th>
                <th class="py-3.5 px-4">Rincian Pesan</th>
                <th class="py-3.5 px-4 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80 text-slate-700">
              <template v-if="loading">
                <tr>
                  <td colspan="6" class="py-10 text-center text-slate-400 font-medium">Memuat data notifikasi...</td>
                </tr>
              </template>
              <template v-else-if="filteredInApp.length > 0">
                <tr
                  v-for="item in filteredInApp"
                  :key="item.id"
                  class="hover:bg-purple-50/40 transition-colors"
                >
                  <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                    {{ new Date(item.created_at).toLocaleString('id-ID') }}
                  </td>
                  <td class="py-3.5 px-4">
                    <span :class="['px-2.5 py-1 rounded-full font-bold text-[10px] inline-flex items-center gap-1 border', getBadgeStyle(item.category)]">
                      {{ formatCategoryName(item.category) }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 whitespace-nowrap">
                    <div class="font-mono font-bold text-purple-900">{{ item.spk_number || '-' }}</div>
                    <div class="text-[10px] text-slate-400 truncate max-w-xs">📍 {{ item.location_name || '-' }}</div>
                  </td>
                  <td class="py-3.5 px-4 font-bold text-slate-900">
                    {{ item.title }}
                  </td>
                  <td class="py-3.5 px-4 text-slate-600 max-w-sm">
                    {{ item.message }}
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span
                      :class="[
                        'px-2.5 py-0.5 rounded-full text-[10px] font-bold',
                        item.is_read ? 'bg-slate-100 text-slate-500' : 'bg-rose-100 text-rose-800'
                      ]"
                    >
                      {{ item.is_read ? 'Sudah Dibaca' : 'Belum Dibaca' }}
                    </span>
                  </td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td colspan="6" class="py-10 text-center text-slate-400 font-medium">Tidak ada notifikasi yang sesuai filter.</td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 2: WhatsApp Gateway Logs Table -->
    <div v-else class="space-y-4">
      <!-- Search & Filters -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-72">
          <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            v-model="searchWaQuery"
            type="text"
            placeholder="Cari nomor WA / event / pesan..."
            class="w-full pl-9 pr-3 py-2 bg-white/80 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-purple-700 focus:outline-none"
          />
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="testConnection"
            :disabled="testingConnection"
            class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer"
          >
            <Radio class="w-3.5 h-3.5 text-emerald-600" :class="{ 'animate-pulse': testingConnection }" />
            <span>{{ testingConnection ? 'Menguji...' : 'Cek Status Gateway' }}</span>
          </button>
        </div>
      </div>

      <!-- Logs Table -->
      <div class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-100/70 text-slate-500 font-bold border-b border-slate-200/80">
              <tr>
                <th class="py-3.5 px-4">Waktu</th>
                <th class="py-3.5 px-4">Provider</th>
                <th class="py-3.5 px-4">Nomor WhatsApp</th>
                <th class="py-3.5 px-4">Event / Template</th>
                <th class="py-3.5 px-4">Isi Pesan</th>
                <th class="py-3.5 px-4 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80 text-slate-700">
              <template v-if="loading">
                <tr>
                  <td colspan="6" class="py-10 text-center text-slate-400 font-medium">Memuat log WhatsApp...</td>
                </tr>
              </template>
              <template v-else-if="filteredWaLogs.length > 0">
                <tr v-for="log in filteredWaLogs" :key="log.id" class="hover:bg-emerald-50/30 transition-colors">
                  <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                    {{ new Date(log.sent_at || log.created_at).toLocaleString('id-ID') }}
                  </td>
                  <td class="py-3.5 px-4 font-bold text-slate-900">{{ log.provider }}</td>
                  <td class="py-3.5 px-4 font-mono font-bold text-emerald-800">{{ log.recipient }}</td>
                  <td class="py-3.5 px-4">
                    <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-slate-100 text-slate-700 border border-slate-200">
                      {{ log.message_type }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-slate-600 max-w-sm">
                    <div class="line-clamp-2 text-[11px] whitespace-pre-line">{{ parsePayload(log.payload).text || '-' }}</div>
                    <div v-if="log.error_message" class="text-[10px] text-rose-600 font-medium mt-0.5">
                      ⚠️ {{ log.error_message }}
                    </div>
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span
                      v-if="log.status === 'SENT'"
                      class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1"
                    >
                      <CheckCircle2 class="w-3 h-3" />
                      <span>TERKIRIM</span>
                    </span>
                    <span
                      v-else-if="log.status === 'FAILED'"
                      class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300 inline-flex items-center gap-1"
                    >
                      <AlertTriangle class="w-3 h-3" />
                      <span>GAGAL</span>
                    </span>
                    <span
                      v-else
                      class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-300"
                    >
                      {{ log.status }}
                    </span>
                  </td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td colspan="6" class="py-10 text-center text-slate-400 font-medium">Belum ada catatan pengiriman WhatsApp.</td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MODAL TEST WHATSAPP -->
    <div
      v-if="showTestModal"
      class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
              <Send class="w-4 h-4" />
            </div>
            <div>
              <h3 class="font-black text-sm text-slate-900">Uji Coba WhatsApp Gateway</h3>
              <p class="text-[11px] text-slate-500">Kirim pesan uji coba menggunakan FonnteService</p>
            </div>
          </div>
          <button @click="showTestModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer text-lg font-bold">
            ✕
          </button>
        </div>

        <div class="space-y-3 text-xs">
          <div>
            <label class="font-bold text-slate-700 block mb-1">Nomor WhatsApp Tujuan:</label>
            <input
              v-model="testForm.phone"
              type="text"
              placeholder="Contoh: 08123456789 atau 628123456789"
              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-mono text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
          </div>

          <div>
            <label class="font-bold text-slate-700 block mb-1">Pilih Template Pesan:</label>
            <select
              v-model="testForm.template_key"
              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
              <option value="TEST_MESSAGE">TEST_MESSAGE (Uji Coba Sistem)</option>
              <option value="WORK_ORDER_CREATED">WORK_ORDER_CREATED (SPK Baru)</option>
              <option value="WORK_ORDER_ASSIGNED">WORK_ORDER_ASSIGNED (Penugasan Teknisi)</option>
              <option value="CHECK_IN_SUCCESS">CHECK_IN_SUCCESS (Check-in GPS)</option>
              <option value="SUBMISSION_RECEIVED">SUBMISSION_RECEIVED (Bukti Terkirim)</option>
              <option value="REVIEW_APPROVED">REVIEW_APPROVED (Pekerjaan Selesai 100%)</option>
              <option value="REVISION_REQUIRED">REVISION_REQUIRED (Permintaan Revisi)</option>
              <option value="BA_ISSUED">BA_ISSUED (BA Opname Terbit)</option>
            </select>
          </div>

          <div v-if="testResult" :class="['p-3 rounded-xl border font-mono text-[11px]', testResult.success ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200']">
            <div class="font-bold mb-0.5">{{ testResult.success ? '✅ Berhasil' : '❌ Gagal' }}</div>
            <div>{{ testResult.message }}</div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
          <button
            @click="showTestModal = false"
            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer"
          >
            Tutup
          </button>
          <button
            @click="sendTestMessage"
            :disabled="sendingTest || !testForm.phone"
            class="px-4 py-2 bg-emerald-700 hover:bg-emerald-600 disabled:opacity-50 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow-xs transition-all active:scale-95 cursor-pointer"
          >
            <Send class="w-3.5 h-3.5" :class="{ 'animate-pulse': sendingTest }" />
            <span>{{ sendingTest ? 'Mengirim...' : 'Kirim Pesan Uji Coba' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../../services/api';
import {
  Bell,
  MessageSquare,
  RefreshCw,
  CheckCircle2,
  AlertTriangle,
  Search,
  Send,
  Radio
} from 'lucide-vue-next';

const activeTab = ref('inapp');
const inAppList = ref([]);
const waLogs = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const searchWaQuery = ref('');
const filterCategory = ref('');

// Test Message Modal State
const showTestModal = ref(false);
const sendingTest = ref(false);
const testingConnection = ref(false);
const testResult = ref(null);
const testForm = ref({
  phone: '',
  template_key: 'TEST_MESSAGE',
  custom_text: ''
});

const filteredInApp = computed(() => {
  return inAppList.value.filter(item => {
    if (filterCategory.value && item.category !== filterCategory.value) return false;
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase();
      const matchSpk = item.spk_number?.toLowerCase().includes(q);
      const matchTitle = item.title?.toLowerCase().includes(q);
      const matchLoc = item.location_name?.toLowerCase().includes(q);
      const matchMsg = item.message?.toLowerCase().includes(q);
      if (!matchSpk && !matchTitle && !matchLoc && !matchMsg) return false;
    }
    return true;
  });
});

const filteredWaLogs = computed(() => {
  if (!searchWaQuery.value) return waLogs.value;
  const q = searchWaQuery.value.toLowerCase();
  return waLogs.value.filter(log => {
    const matchRec = log.recipient?.toLowerCase().includes(q);
    const matchType = log.message_type?.toLowerCase().includes(q);
    const matchPayload = log.payload?.toLowerCase().includes(q);
    const matchErr = log.error_message?.toLowerCase().includes(q);
    return matchRec || matchType || matchPayload || matchErr;
  });
});

async function loadInAppNotifications() {
  loading.value = true;
  try {
    const res = await api.getNotificationFeed({ limit: 100 });
    inAppList.value = res.data || [];
  } catch (err) {
    console.error('Failed to load in-app notifications:', err);
  } finally {
    loading.value = false;
  }
}

async function loadWaLogs() {
  loading.value = true;
  try {
    const res = await api.getWhatsAppLogs({ limit: 100 });
    waLogs.value = res.data || [];
  } catch (err) {
    console.error('Failed to load WA logs:', err);
  } finally {
    loading.value = false;
  }
}

async function testConnection() {
  testingConnection.value = true;
  try {
    const res = await api.testWhatsAppConnection();
    alert(res.message || 'Koneksi ke Fonnte berhasil.');
  } catch (err) {
    alert(err.message || 'Gagal terhubung ke WhatsApp Gateway.');
  } finally {
    testingConnection.value = false;
  }
}

async function sendTestMessage() {
  if (!testForm.value.phone) return;
  sendingTest.value = true;
  testResult.value = null;

  try {
    const res = await api.sendTestWhatsAppMessage(testForm.value);
    testResult.value = { success: true, message: res.message || 'Pesan berhasil dikirim.' };
    await loadWaLogs();
  } catch (err) {
    testResult.value = { success: false, message: err.message || 'Gagal mengirim pesan.' };
    await loadWaLogs();
  } finally {
    sendingTest.value = false;
  }
}

function parsePayload(payloadStr) {
  try {
    return payloadStr ? JSON.parse(payloadStr) : {};
  } catch {
    return {};
  }
}

function getBadgeStyle(cat) {
  switch (cat) {
    case 'BA_ISSUED':
      return 'bg-emerald-50 text-emerald-800 border-emerald-200';
    case 'GPS_CHECKIN':
      return 'bg-blue-50 text-blue-800 border-blue-200';
    case 'EVIDENCE_UPLOAD':
      return 'bg-purple-50 text-purple-800 border-purple-200';
    case 'ISSUE_REPORTED':
      return 'bg-amber-50 text-amber-900 border-amber-200';
    default:
      return 'bg-slate-100 text-slate-800 border-slate-200';
  }
}

function formatCategoryName(cat) {
  switch (cat) {
    case 'BA_ISSUED': return 'BA Terbit';
    case 'GPS_CHECKIN': return 'Check-In GPS';
    case 'EVIDENCE_UPLOAD': return 'Upload Foto';
    case 'ISSUE_REPORTED': return 'Kendala Lapangan';
    default: return 'Notifikasi';
  }
}

onMounted(async () => {
  await Promise.all([loadInAppNotifications(), loadWaLogs()]);
});
</script>
