<template>
  <header class="sticky top-0 z-30 px-4 py-2 flex items-center justify-between border-b border-[#232A36] bg-[#0E1117]">
    <!-- Left: Brand & Industrial Signal Badges -->
    <div class="flex items-center gap-3">
      <button
        type="button"
        @click="$emit('menu-toggle')"
        class="p-2 rounded-xs text-slate-300 hover:text-[#FACC15] bg-[#13171F] hover:bg-[#1A202C] active:scale-95 transition-colors cursor-pointer border border-[#232A36] hover:border-[#FACC15]/80"
        title="Buka / Tutup Menu Slider"
        aria-label="Toggle Menu Slider"
      >
        <Menu class="w-4 h-4" />
      </button>

      <div class="flex items-center gap-2.5">
        <!-- Logo Resmi Perusahaan -->
        <div class="flex items-center">
          <img
            src="/sgx_logo.png"
            alt="PT Sinar Kreasindo Bencoolen Logo"
            class="h-8 w-8 object-contain rounded-xs border border-[#232A36]"
          />
        </div>
        <div class="flex flex-col">
          <div class="flex items-center gap-2">
            <span class="font-black text-white text-sm tracking-wider font-mono uppercase">SGX PORTAL</span>
            <span class="inline-flex items-center px-1.5 py-0.5 text-[9px] font-bold font-mono bg-[#FACC15] text-[#0B0C0E] rounded-xs uppercase tracking-wider">
              OPERATIONS CORE
            </span>
          </div>
          <p class="text-[9px] text-slate-400 font-mono tracking-tight hidden sm:block">PT SINAR KREASINDO BENCOOLEN // FIELD OPS</p>
        </div>
      </div>
    </div>

    <!-- Center: Terminal-Style Command Prompt Search (Cmd+K style) -->
    <div class="flex-1 max-w-md mx-4 hidden md:flex justify-center">
      <button
        type="button"
        @click="isCommandPaletteOpen = true"
        class="w-full flex items-center justify-between px-3 py-1.5 rounded-xs bg-[#13171F] hover:bg-[#1A202C] text-slate-400 hover:text-slate-200 border border-[#232A36] hover:border-[#FACC15]/70 transition-colors text-xs group cursor-pointer font-mono"
      >
        <div class="flex items-center gap-2 truncate">
          <span class="text-[#FACC15] font-bold text-xs select-none">&gt;_</span>
          <span class="truncate text-[11px] text-slate-400 group-hover:text-slate-200">CARI SPK, CLIENT, KOORDINAT GPS...</span>
        </div>
        <div class="flex items-center gap-1 shrink-0 ml-2">
          <kbd class="px-1.5 py-0.2 rounded-xs text-[10px] font-mono font-bold bg-[#1C2028] text-slate-400 border border-[#232A36]">CTRL+K</kbd>
        </div>
      </button>
    </div>

    <!-- Right: Live Ticker, Notification Bell, User Profile -->
    <div class="flex items-center gap-2 sm:gap-2.5">
      <!-- Live Status Ticker (as specified in .cursorrules) -->
      <div class="hidden lg:flex items-center gap-2 px-2.5 py-1 rounded-xs bg-[#13171F] border border-[#232A36] text-[10px] font-mono text-slate-300">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#FACC15] opacity-80"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-[#FACC15]"></span>
        </span>
        <span class="tracking-wider uppercase font-bold text-slate-200">SYSTEM ACTIVE // BENGKULU WORKSHOP</span>
      </div>

      <!-- Quick mobile search icon -->
      <button
        type="button"
        @click="isCommandPaletteOpen = true"
        class="md:hidden p-2 rounded-xs bg-[#13171F] text-slate-400 hover:text-[#FACC15] border border-[#232A36]"
        title="Buka Pencarian Global"
      >
        <Search class="w-4 h-4" />
      </button>

      <!-- In-App Notification Bell Button with Solid Yellow Badge -->
      <button
        @click="isNotificationOpen = true"
        :class="[
          'relative p-2 rounded-xs active:scale-95 transition-colors cursor-pointer border',
          unreadCount > 0 
            ? 'bg-[#13171F] text-[#FACC15] border-[#FACC15]/60 hover:bg-[#1A202C]' 
            : 'bg-[#13171F] text-slate-300 hover:text-[#FACC15] hover:bg-[#1A202C] border-[#232A36]'
        ]"
        title="Buka Notifikasi Pekerjaan"
      >
        <Bell :class="['w-4 h-4 transition-transform duration-300', isRinging ? 'animate-bounce text-[#FACC15]' : '']" />
        <span
          class="absolute -top-1.5 -right-1.5 min-w-4.5 h-4 px-1 rounded-xs bg-[#FACC15] text-[#0B0C0E] font-mono text-[9px] font-black flex items-center justify-center border border-[#0B0C0E]"
        >
          {{ unreadCount > 0 ? (unreadCount > 99 ? '99+' : unreadCount) : '21' }}
        </span>
      </button>

      <!-- User Profile Popover Button with Industrial Frame -->
      <div v-if="auth.state.user" class="flex items-center gap-1.5 pl-2 border-l border-[#232A36]">
        <button
          type="button"
          @click="isProfileOpen = true"
          class="flex items-center gap-2 p-1 rounded-xs hover:bg-[#1A202C] active:scale-95 transition-colors cursor-pointer group"
          title="Buka Pengaturan Profil & Keamanan Akun"
        >
          <div class="relative">
            <div class="w-7 h-7 rounded-xs bg-[#1C2028] border border-[#FACC15] text-[#FACC15] flex items-center justify-center font-mono font-bold text-xs">
              {{ auth.state.user.name?.charAt(0) || 'S' }}
            </div>
            <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full bg-[#10B981] border border-[#0B0C0E]"></span>
          </div>
          <div class="text-left hidden lg:block">
            <div class="text-xs font-bold text-slate-200 leading-tight group-hover:text-[#FACC15] flex items-center gap-1 font-mono">
              <span>{{ auth.state.user.name || 'SUPERADMIN' }}</span>
              <Settings class="w-3 h-3 text-slate-500 group-hover:text-[#FACC15]" />
            </div>
            <div class="text-[9px] font-mono text-slate-400 uppercase tracking-tight">
              ROLE: <span class="text-[#FACC15] font-bold">{{ auth.state.user.role === 'SUPERUSER' ? 'SUPERUSER' : auth.state.user.role }}</span>
            </div>
          </div>
        </button>

        <!-- Logout Button -->
        <button
          @click="auth.logout()"
          class="p-2 rounded-xs text-slate-400 hover:text-[#EF4444] hover:bg-[#1A202C] active:scale-95 transition-colors cursor-pointer border border-transparent hover:border-[#EF4444]/40"
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

    <!-- Command Palette (Cmd+K) Modal -->
    <CommandPaletteModal
      :isOpen="isCommandPaletteOpen"
      @close="isCommandPaletteOpen = false"
      @select-spk="(id) => $emit('select-spk', id)"
      @navigate-tab="(tab) => $emit('navigate-tab', tab)"
      @open-create-spk="() => $emit('open-create-spk')"
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Menu, Bell, LogOut, Settings, Search } from 'lucide-vue-next';
import { useAuth } from '../composables/useAuth';
import { api } from '../services/api';
import NotificationDrawer from './NotificationDrawer.vue';
import UserProfileModal from './UserProfileModal.vue';
import CommandPaletteModal from './CommandPaletteModal.vue';

defineEmits(['menu-toggle', 'select-spk', 'open-ba', 'navigate-tab', 'open-create-spk']);

const auth = useAuth();
const isNotificationOpen = ref(false);
const isProfileOpen = ref(false);
const isCommandPaletteOpen = ref(false);
const unreadCount = ref(21);
const isRinging = ref(false);
let pollingTimer = null;
let isFirstLoad = true;

const rolePillBadge = computed(() => {
  const r = auth.state.user?.role;
  if (r === 'SUPERUSER') return 'Superuser System Console';
  if (r === 'ADMIN') return 'Admin Operational Console';
  if (r === 'VENDOR') return 'Client Workspace';
  if (r === 'FIELD_TEAM') return 'Field Mobile System';
  return 'Enterprise Portal';
});

// Keyboard shortcut listener for Ctrl+K or Cmd+K
function handleKeyDown(e) {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault();
    isCommandPaletteOpen.value = !isCommandPaletteOpen.value;
  }
}

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
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  if (pollingTimer) clearInterval(pollingTimer);
  window.removeEventListener('keydown', handleKeyDown);
});
</script>
