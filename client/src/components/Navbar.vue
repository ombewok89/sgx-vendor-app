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
      <!-- In-App Notification Bell Button -->
      <button
        @click="isNotificationOpen = true"
        className="relative p-2 rounded-xl text-slate-600 hover:text-purple-900 hover:bg-purple-50 active:scale-95 transition-all cursor-pointer border border-slate-200/80 bg-white/80 shadow-2xs"
        title="Buka Notifikasi Pekerjaan"
      >
        <Bell className="w-4.5 h-4.5" />
        <span
          v-if="unreadCount > 0"
          className="absolute -top-1 -right-1 min-w-4.5 h-4.5 px-1 rounded-full bg-rose-600 text-white font-mono text-[9px] font-black flex items-center justify-center shadow-xs animate-pulse border-2 border-white"
        >
          {{ unreadCount > 9 ? '9+' : unreadCount }}
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
import { ref } from 'vue';
import { Menu, Bell, LogOut, Settings } from 'lucide-vue-next';
import { useAuth } from '../composables/useAuth';
import NotificationDrawer from './NotificationDrawer.vue';
import UserProfileModal from './UserProfileModal.vue';

defineEmits(['menu-toggle', 'select-spk', 'open-ba']);

const auth = useAuth();
const isNotificationOpen = ref(false);
const isProfileOpen = ref(false);
const unreadCount = ref(0);
</script>
