<template>
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-[9999] overflow-hidden flex justify-end">
      <!-- Backdrop -->
      <div
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity cursor-pointer"
        @click="$emit('close')"
      />

      <!-- Sliding Drawer Panel with Guaranteed Full Viewport Height -->
      <aside class="relative w-full max-w-md bg-white shadow-2xl border-l border-slate-200 flex flex-col h-screen max-h-screen z-10 animate-fade-in">
      <!-- Header (Fixed Top) -->
      <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/90 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-900 flex items-center justify-center font-bold relative shrink-0 shadow-xs">
            <Bell class="w-5 h-5" />
            <span
              v-if="unreadCount > 0"
              class="absolute -top-1 -right-1 min-w-4.5 h-4.5 px-1 rounded-full bg-rose-600 text-white font-mono text-[9px] font-black flex items-center justify-center animate-pulse border-2 border-white shadow-xs"
            >
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </div>
          <div>
            <h3 class="font-black text-sm text-slate-900">Pusat Notifikasi</h3>
            <p class="text-[11px] text-slate-500 font-medium">Informasi status & progres pekerjaan SPK</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            v-if="unreadCount > 0"
            @click="handleMarkAllRead"
            :disabled="markingAll"
            class="px-2.5 py-1 text-[11px] font-bold text-purple-700 hover:text-purple-900 hover:bg-purple-50 rounded-lg transition-colors cursor-pointer border border-purple-200/80 bg-white"
          >
            {{ markingAll ? '...' : 'Tandai Semua Dibaca' }}
          </button>
          <button
            @click="$emit('close')"
            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-colors cursor-pointer"
            title="Tutup"
          >
            <X class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Filter Tabs & Refresh (Fixed Subheader) -->
      <div class="px-4 py-2.5 border-b border-slate-100 bg-white flex items-center justify-between gap-2 shrink-0">
        <div class="flex items-center gap-1.5 text-xs font-bold">
          <button
            @click="filterType = 'all'"
            :class="[
              'px-3 py-1.5 rounded-xl transition-all cursor-pointer font-bold',
              filterType === 'all'
                ? 'bg-purple-900 text-white shadow-xs'
                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
            ]"
          >
            Semua ({{ notifications.length }})
          </button>
          <button
            @click="filterType = 'unread'"
            :class="[
              'px-3 py-1.5 rounded-xl transition-all cursor-pointer font-bold',
              filterType === 'unread'
                ? 'bg-purple-900 text-white shadow-xs'
                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
            ]"
          >
            Belum Dibaca ({{ unreadCount }})
          </button>
        </div>

        <button
          @click="fetchNotifications"
          :disabled="loading"
          class="px-2.5 py-1 text-xs font-bold rounded-lg text-slate-500 hover:text-purple-900 hover:bg-purple-50 transition-colors cursor-pointer flex items-center gap-1.5 border border-slate-200"
          title="Muat Ulang"
        >
          <RefreshCw :class="['w-3 h-3', loading && 'animate-spin']" />
          <span class="text-[11px]">Refresh</span>
        </button>
      </div>

      <!-- Notifications Scrollable Feed List -->
      <div class="flex-1 overflow-y-auto p-4 space-y-3 min-h-0 bg-slate-50/50">
        <!-- Loading State -->
        <div v-if="loading && notifications.length === 0" class="py-16 text-center text-slate-400 text-xs space-y-2">
          <RefreshCw class="w-6 h-6 animate-spin mx-auto text-purple-700" />
          <p class="font-medium">Memuat notifikasi terbaru...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredList.length === 0" class="py-20 text-center space-y-3">
          <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center mx-auto shadow-xs">
            <BellOff class="w-6 h-6" />
          </div>
          <div>
            <p class="font-bold text-slate-800 text-xs">Tidak Ada Notifikasi</p>
            <p class="text-[11px] text-slate-500 mt-1 max-w-xs mx-auto">
              {{ filterType === 'unread' ? 'Seluruh status notifikasi pekerjaan telah Anda baca.' : 'Notifikasi status pekerjaan SPK akan muncul di sini secara otomatis.' }}
            </p>
          </div>
        </div>

        <!-- Notification Cards (Purely Informational Display) -->
        <div
          v-for="notif in filteredList"
          :key="notif.id"
          :class="[
            'p-4 rounded-2xl border transition-all space-y-2.5 bg-white shadow-xs',
            !notif.is_read
              ? 'border-purple-200 ring-1 ring-purple-100 bg-purple-50/20'
              : 'border-slate-200/90'
          ]"
        >
          <!-- Top Row: Category Badge, SPK Number, and Time -->
          <div class="flex items-start justify-between gap-2">
            <div class="flex items-center gap-2 flex-wrap">
              <span :class="['px-2.5 py-0.5 rounded-md font-bold text-[10px] inline-flex items-center gap-1 border', getCategoryStyle(notif.category).badge]">
                <component :is="getCategoryStyle(notif.category).icon" class="w-3 h-3" />
                <span>{{ getCategoryStyle(notif.category).label }}</span>
              </span>

              <span v-if="notif.spk_number" class="text-[10px] font-mono font-bold text-purple-900 bg-purple-100/80 px-2 py-0.5 rounded border border-purple-200">
                {{ notif.spk_number }}
              </span>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
              <span v-if="!notif.is_read" class="w-2 h-2 rounded-full bg-purple-700" title="Belum dibaca" />
              <span class="text-[10px] font-mono text-slate-400">
                {{ formatTimeAgo(notif.created_at) }}
              </span>
            </div>
          </div>

          <!-- Notification Title & Location -->
          <div>
            <h4 class="text-xs font-black text-slate-900 leading-snug">
              {{ notif.title }}
            </h4>
            <div v-if="notif.location_name" class="text-[10px] text-slate-500 font-medium flex items-center gap-1 mt-0.5 truncate">
              <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
              <span class="truncate">{{ notif.location_name }}</span>
            </div>
          </div>

          <!-- Notification Detailed Status Message -->
          <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 leading-relaxed font-normal">
            {{ notif.message }}
          </div>

          <!-- Card Bottom: Mark Single as Read if Unread -->
          <div v-if="!notif.is_read" class="flex justify-end pt-1">
            <button
              @click="handleMarkSingleRead(notif)"
              class="text-[10px] font-bold text-purple-700 hover:text-purple-900 transition-colors cursor-pointer flex items-center gap-1 hover:underline"
            >
              <Check class="w-3 h-3" />
              <span>Tandai Sudah Dibaca</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Footer Info (Fixed Bottom) -->
      <div class="p-3 bg-white border-t border-slate-100 text-center text-[10px] text-slate-400 shrink-0 font-medium">
        Notifikasi status pekerjaan SPK terisolasi khusus untuk perusahaan Client Anda.
      </div>
    </aside>
  </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { api } from '../services/api';
import { useAuth } from '../composables/useAuth';
import {
  Bell,
  BellOff,
  X,
  RefreshCw,
  Clock,
  MapPin,
  Camera,
  AlertTriangle,
  FileCheck,
  Check
} from 'lucide-vue-next';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'update-unread-count']);

const auth = useAuth();
const notifications = ref([]);
const unreadCount = ref(0);
const loading = ref(false);
const markingAll = ref(false);
const filterType = ref('all');

const filteredList = computed(() => {
  if (filterType.value === 'unread') {
    return notifications.value.filter(n => !n.is_read);
  }
  return notifications.value;
});

async function fetchNotifications(autoMarkRead = true) {
  loading.value = true;
  try {
    const res = await api.getNotificationFeed({ limit: 50 });
    const list = res.data || [];
    notifications.value = list;
    const unread = list.filter(n => !n.is_read).length;
    unreadCount.value = unread;
    emit('update-unread-count', unread);

    // Auto mark all as read upon opening the notification drawer
    if (autoMarkRead && unread > 0) {
      setTimeout(async () => {
        try {
          await api.markAllNotificationsRead();
          notifications.value.forEach(n => n.is_read = true);
          unreadCount.value = 0;
          emit('update-unread-count', 0);
        } catch (e) {
          // silently fail
        }
      }, 800);
    }
  } catch (err) {
    console.error('Failed to fetch notifications:', err);
  } finally {
    loading.value = false;
  }
}

async function handleMarkSingleRead(notif) {
  notif.is_read = true;
  unreadCount.value = Math.max(0, unreadCount.value - 1);
  emit('update-unread-count', unreadCount.value);
  try {
    await api.markNotificationRead(notif.id);
  } catch (e) {
    console.error('Failed to mark read:', e);
  }
}

async function handleMarkAllRead() {
  markingAll.value = true;
  try {
    await api.markAllNotificationsRead();
    notifications.value.forEach(n => n.is_read = true);
    unreadCount.value = 0;
    emit('update-unread-count', 0);
  } catch (err) {
    console.error('Failed to mark all as read:', err);
  } finally {
    markingAll.value = false;
  }
}

function getCategoryStyle(category) {
  switch (category) {
    case 'BA_ISSUED':
      return {
        label: 'BA Terbit',
        badge: 'bg-emerald-50 text-emerald-800 border-emerald-200',
        icon: FileCheck
      };
    case 'GPS_CHECKIN':
      return {
        label: 'Check-In GPS',
        badge: 'bg-blue-50 text-blue-800 border-blue-200',
        icon: MapPin
      };
    case 'EVIDENCE_UPLOAD':
      return {
        label: 'Evidensi Foto',
        badge: 'bg-purple-50 text-purple-800 border-purple-200',
        icon: Camera
      };
    case 'ISSUE_REPORTED':
      return {
        label: 'Kendala Lapangan',
        badge: 'bg-amber-50 text-amber-900 border-amber-200',
        icon: AlertTriangle
      };
    default:
      return {
        label: 'Info Pekerjaan',
        badge: 'bg-slate-100 text-slate-800 border-slate-200',
        icon: Bell
      };
  }
}

function formatTimeAgo(isoDate) {
  if (!isoDate) return 'Baru saja';
  const diffMs = Date.now() - new Date(isoDate).getTime();
  const diffMinutes = Math.floor(diffMs / 60000);
  if (diffMinutes < 1) return 'Baru saja';
  if (diffMinutes < 60) return `${diffMinutes}m lalu`;
  const diffHours = Math.floor(diffMinutes / 60);
  if (diffHours < 24) return `${diffHours}j lalu`;
  const diffDays = Math.floor(diffHours / 24);
  return `${diffDays}h lalu`;
}

watch(() => props.isOpen, (open) => {
  if (open) {
    fetchNotifications(true);
  }
});

watch(() => auth.state.user?.id, () => {
  if (auth.state.user?.id) {
    fetchNotifications(false);
  }
});

onMounted(() => {
  fetchNotifications(false);
});
</script>
