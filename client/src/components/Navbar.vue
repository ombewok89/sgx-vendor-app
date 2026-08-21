<template>
  <header className="sticky top-0 z-30 glass-nav px-4 py-2.5 flex items-center justify-between shadow-xs">
    <div className="flex items-center gap-3">
      <button
        @click="$emit('menu-toggle')"
        className="p-2 rounded-xl text-slate-600 hover:bg-slate-100/80 active:scale-95 transition-all lg:hidden"
        aria-label="Toggle Menu"
      >
        <Menu className="w-5 h-5" />
      </button>
      <div className="flex items-center gap-3">
        <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-900 via-brand-700 to-emerald-500 flex items-center justify-center text-white font-black text-sm shadow-md shadow-brand-700/20 tracking-wider">
          SGX
        </div>
        <div>
          <h1 className="font-bold text-slate-900 text-sm leading-none flex items-center gap-2">
            SGX Client Work Evidence & Management
            <span className="hidden sm:inline-flex items-center px-2 py-0.5 text-[10px] font-semibold bg-emerald-50 text-emerald-700 rounded-full border border-emerald-200">
              <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>
              PROD v1.0
            </span>
          </h1>
          <p className="text-[11px] text-slate-500 hidden sm:block mt-0.5">Digital Evidence & Client Operational Reporting System</p>
        </div>
      </div>
    </div>

    <div className="flex items-center gap-2.5 sm:gap-3">
      <!-- In-App Notification Bell Button with Sound & Badge -->
      <button
        @click="isNotificationOpen = true"
        :class="[
          'relative p-2 rounded-xl active:scale-95 transition-all cursor-pointer border shadow-2xs',
          unreadCount > 0 
            ? 'bg-purple-50 text-purple-900 border-purple-300 ring-2 ring-purple-500/20' 
            : 'bg-white/80 text-slate-600 hover:text-purple-900 hover:bg-purple-50 border-slate-200/80'
        ]"
        title="Buka Notifikasi Pekerjaan"
      >
        <Bell :class="['w-4.5 h-4.5 transition-transform duration-300', isRinging ? 'animate-bounce text-purple-700' : '']" />
        <span
          v-if="unreadCount > 0"
          class="absolute -top-1.5 -right-1.5 min-w-5 h-5 px-1.5 rounded-full bg-rose-600 text-white font-mono text-[10px] font-black flex items-center justify-center shadow-md animate-pulse border-2 border-white ring-2 ring-rose-500/30"
        >
          {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
      </button>

      <!-- User Badge & Profile Button -->
      <div v-if="auth.state.user" class="flex items-center gap-1.5 pl-2 border-l border-slate-200/80">
        <button
          type="button"
          @click="isProfileOpen = true"
          class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100/80 active:scale-95 transition-all cursor-pointer group"
          title="Buka Pengaturan Profil & Keamanan Akun"
        >
          <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs group-hover:ring-2 group-hover:ring-purple-400 transition-all">
            {{ auth.state.user.name?.charAt(0) || 'U' }}
          </div>
          <div class="text-left hidden lg:block">
            <div class="text-xs font-bold text-slate-900 leading-tight group-hover:text-purple-900 flex items-center gap-1">
              <span>{{ auth.state.user.name }}</span>
              <Settings class="w-3 h-3 text-slate-400 group-hover:text-purple-600" />
            </div>
            <div class="text-[10px] font-medium text-slate-500">{{ auth.state.user.vendor_name || (auth.state.user.role === 'VENDOR' ? 'Perusahaan Client' : auth.state.user.role) }}</div>
          </div>
        </button>

        <!-- Logout Button -->
        <button
          @click="auth.logout()"
          class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 active:scale-95 transition-all cursor-pointer border border-transparent hover:border-rose-200"
          title="Keluar dari Akun (Logout)"
        >
          <LogOut class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- User Profile & Security Modal -->
    <UserProfileModal
      v-if="isProfileOpen"
      @close="isProfileOpen = false"
    />

    <!-- Notification Drawer Component -->
    <NotificationDrawer
      :isOpen="isNotificationOpen"
      @close="isNotificationOpen = false"
      @select-spk="(id) => $emit('select-spk', id)"
      @open-ba="(id) => $emit('open-ba', id)"
      @update-unread-count="(count) => unreadCount = count"
    />
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Menu, Bell, LogOut, Settings } from 'lucide-vue-next';
import { useAuth } from '../composables/useAuth';
import { api } from '../services/api';
import NotificationDrawer from './NotificationDrawer.vue';
import UserProfileModal from './UserProfileModal.vue';

defineEmits(['menu-toggle', 'select-spk', 'open-ba']);

const auth = useAuth();
const isNotificationOpen = ref(false);
const isProfileOpen = ref(false);
const unreadCount = ref(0);
const isRinging = ref(false);
let pollingTimer = null;
let isFirstLoad = true;

/**
 * Web Audio API Chime Synthesizer
 * Plays a pleasant, crystal-clear 2-tone melodic notification chime.
 */
function playNotificationChime() {
  try {
    const AudioContext = window.AudioContext || window.webkitAudioContext;
    if (!AudioContext) return;

    const ctx = new AudioContext();
    const now = ctx.currentTime;

    // First Tone: E5 (659.25 Hz)
    const osc1 = ctx.createOscillator();
    const gain1 = ctx.createGain();
    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(659.25, now);
    gain1.gain.setValueAtTime(0.25, now);
    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
    osc1.connect(gain1);
    gain1.connect(ctx.destination);
    osc1.start(now);
    osc1.stop(now + 0.35);

    // Second Tone: A5 (880.00 Hz)
    const osc2 = ctx.createOscillator();
    const gain2 = ctx.createGain();
    osc2.type = 'sine';
    osc2.frequency.setValueAtTime(880.00, now + 0.12);
    gain2.gain.setValueAtTime(0.35, now + 0.12);
    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.65);
    osc2.connect(gain2);
    gain2.connect(ctx.destination);
    osc2.start(now + 0.12);
    osc2.stop(now + 0.65);
  } catch (e) {
    // Non-blocking if audio context is restricted
  }
}

async function fetchUnreadCount() {
  if (!auth.state.user) return;
  try {
    const res = await api.getNotificationFeed({ limit: 50 });
    if (res.data && Array.isArray(res.data)) {
      const newCount = res.data.filter(n => !n.is_read).length;

      // Detect newly arrived notifications
      if (!isFirstLoad && newCount > unreadCount.value) {
        playNotificationChime();
        isRinging.value = true;
        setTimeout(() => {
          isRinging.value = false;
        }, 1500);
      }

      unreadCount.value = newCount;
      isFirstLoad = false;
    }
  } catch (err) {
    // non-blocking
  }
}

onMounted(() => {
  fetchUnreadCount();
  // Poll every 25 seconds for real-time notification alerts
  pollingTimer = setInterval(fetchUnreadCount, 25000);
});

onUnmounted(() => {
  if (pollingTimer) clearInterval(pollingTimer);
});
</script>
