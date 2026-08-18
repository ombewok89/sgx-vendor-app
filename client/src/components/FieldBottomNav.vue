<template>
  <nav
    class="fixed bottom-0 inset-x-0 z-40 lg:hidden px-3 pb-[calc(env(safe-area-inset-bottom,0px)+8px)] pt-2"
  >
    <!-- Floating Glass Island -->
    <div
      class="max-w-md mx-auto bg-slate-900/95 backdrop-blur-xl border border-white/10 text-white rounded-3xl shadow-2xl shadow-slate-950/40 px-2 py-1.5 flex items-center justify-around"
    >
      <!-- 1. Dashboard Tab -->
      <button
        type="button"
        @click="$emit('update:activeTab', 'field_dashboard')"
        class="flex-1 flex flex-col items-center justify-center py-1.5 px-2 rounded-2xl transition-all duration-200 active:scale-90 cursor-pointer relative"
        :class="activeTab === 'field_dashboard' ? 'text-indigo-400 font-black' : 'text-slate-400 hover:text-slate-200'"
      >
        <div
          v-if="activeTab === 'field_dashboard'"
          class="absolute inset-0 bg-indigo-500/20 rounded-2xl border border-indigo-500/30"
        />
        <div class="relative z-10 flex flex-col items-center gap-0.5">
          <LayoutDashboard class="w-5 h-5 transition-transform" :class="{ 'scale-110': activeTab === 'field_dashboard' }" />
          <span class="text-[10px] tracking-tight">Dashboard</span>
        </div>
      </button>

      <!-- 2. Tugas / Active Workstation Tab -->
      <button
        type="button"
        @click="$emit('update:activeTab', 'field_tasks')"
        class="flex-1 flex flex-col items-center justify-center py-1.5 px-2 rounded-2xl transition-all duration-200 active:scale-90 cursor-pointer relative"
        :class="activeTab === 'field_tasks' ? 'text-emerald-400 font-black' : 'text-slate-400 hover:text-slate-200'"
      >
        <div
          v-if="activeTab === 'field_tasks'"
          class="absolute inset-0 bg-emerald-500/20 rounded-2xl border border-emerald-500/30"
        />
        <div class="relative z-10 flex flex-col items-center gap-0.5">
          <div class="relative">
            <CheckSquare class="w-5 h-5 transition-transform" :class="{ 'scale-110': activeTab === 'field_tasks' }" />
            <span
              v-if="activeTaskCount > 0"
              class="absolute -top-1 -right-2.5 min-w-4 h-4 px-1 rounded-full bg-emerald-500 text-slate-950 font-mono text-[9px] font-black flex items-center justify-center border-2 border-slate-900"
            >
              {{ activeTaskCount }}
            </span>
          </div>
          <span class="text-[10px] tracking-tight">Pekerjaan</span>
        </div>
      </button>

      <!-- 3. Riwayat Tab -->
      <button
        type="button"
        @click="$emit('update:activeTab', 'field_history')"
        class="flex-1 flex flex-col items-center justify-center py-1.5 px-2 rounded-2xl transition-all duration-200 active:scale-90 cursor-pointer relative"
        :class="activeTab === 'field_history' ? 'text-sky-400 font-black' : 'text-slate-400 hover:text-slate-200'"
      >
        <div
          v-if="activeTab === 'field_history'"
          class="absolute inset-0 bg-sky-500/20 rounded-2xl border border-sky-500/30"
        />
        <div class="relative z-10 flex flex-col items-center gap-0.5">
          <History class="w-5 h-5 transition-transform" :class="{ 'scale-110': activeTab === 'field_history' }" />
          <span class="text-[10px] tracking-tight">Riwayat</span>
        </div>
      </button>

      <!-- 4. Profil / Settings Quick Tap -->
      <button
        type="button"
        @click="$emit('open-profile')"
        class="flex-1 flex flex-col items-center justify-center py-1.5 px-2 rounded-2xl transition-all duration-200 active:scale-90 cursor-pointer text-slate-400 hover:text-slate-200 relative"
      >
        <div class="flex flex-col items-center gap-0.5">
          <div class="w-5 h-5 rounded-full bg-gradient-to-tr from-brand-500 to-indigo-500 text-white flex items-center justify-center text-[10px] font-bold">
            {{ userInitial }}
          </div>
          <span class="text-[10px] tracking-tight">Profil</span>
        </div>
      </button>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue';
import { useAuth } from '../composables/useAuth';
import {
  LayoutDashboard,
  CheckSquare,
  History
} from 'lucide-vue-next';

defineProps({
  activeTab: {
    type: String,
    required: true
  },
  activeTaskCount: {
    type: Number,
    default: 0
  }
});

defineEmits(['update:activeTab', 'open-profile']);

const auth = useAuth();
const userInitial = computed(() => auth.state.user?.name?.charAt(0)?.toUpperCase() || 'T');
</script>
