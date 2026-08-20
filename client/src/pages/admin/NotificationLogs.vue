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
          Monitoring feed informasi progres pekerjaan SPK real-time dan log pengiriman notifikasi WhatsApp gateway.
        </p>
      </div>

      <div class="flex items-center gap-2">
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
    <div v-else class="glass-card rounded-3xl border border-white/80 shadow-glass overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100/70 text-slate-500 font-bold border-b border-slate-200/80">
            <tr>
              <th class="py-3.5 px-4">Waktu Kirim</th>
              <th class="py-3.5 px-4">Provider</th>
              <th class="py-3.5 px-4">Nomor WhatsApp</th>
              <th class="py-3.5 px-4">Tipe Event</th>
              <th class="py-3.5 px-4">Isi Pesan</th>
              <th class="py-3.5 px-4">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100/80 text-slate-700">
            <template v-if="loading">
              <tr>
                <td colspan="6" class="py-10 text-center text-slate-400 font-medium">Memuat log WhatsApp...</td>
              </tr>
            </template>
            <template v-else-if="waLogs.length > 0">
              <tr v-for="log in waLogs" :key="log.id" class="hover:bg-emerald-50/30 transition-colors">
                <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                  {{ new Date(log.sent_at || log.created_at).toLocaleString('id-ID') }}
                </td>
                <td class="py-3.5 px-4 font-bold text-slate-900">{{ log.provider }}</td>
                <td class="py-3.5 px-4 font-mono font-bold text-emerald-700">{{ log.recipient }}</td>
                <td class="py-3.5 px-4">
                  <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-slate-100 text-slate-700 border border-slate-200">
                    {{ log.message_type }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-slate-600 max-w-sm">
                  <div class="line-clamp-2 text-[11px] whitespace-pre-line">
                    {{ log.message || parsePayload(log.payload).text || '-' }}
                  </div>
                  <div v-if="log.error_message" class="text-[10px] text-rose-600 font-medium mt-0.5 flex items-center gap-1">
                    <AlertTriangle class="w-3 h-3 text-rose-500 shrink-0" />
                    <span>{{ log.error_message }}</span>
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
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../../services/api';
import { Bell, MessageSquare, RefreshCw, CheckCircle2, Search, MapPin, Camera, AlertTriangle, FileCheck } from 'lucide-vue-next';

const activeTab = ref('inapp');
const inAppList = ref([]);
const waLogs = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const filterCategory = ref('');

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
    const res = await api.getNotifications();
    waLogs.value = res.data || [];
  } catch (err) {
    console.error('Failed to load WA logs:', err);
  } finally {
    loading.value = false;
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
