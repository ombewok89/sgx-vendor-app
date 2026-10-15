<template>
  <Transition
    enter-active-class="transition duration-150 ease-out"
    enter-from-class="opacity-0 scale-95"
    enter-to-class="opacity-100 scale-100"
    leave-active-class="transition duration-100 ease-in"
    leave-from-class="opacity-100 scale-100"
    leave-to-class="opacity-0 scale-95"
  >
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-start justify-center pt-16 sm:pt-24 px-4 bg-slate-950/75 backdrop-blur-md"
      @click.self="$emit('close')"
      @keydown.esc="$emit('close')"
    >
      <div
        class="w-full max-w-2xl rounded-2xl bg-[#0B1120] border border-slate-800 shadow-2xl shadow-cyan-950/30 overflow-hidden flex flex-col text-slate-100"
        @keydown.down.prevent="navigateResults(1)"
        @keydown.up.prevent="navigateResults(-1)"
        @keydown.enter.prevent="selectCurrent"
      >
        <!-- Search Input Bar -->
        <div class="relative flex items-center px-4 py-3.5 border-b border-slate-800/80 bg-slate-900/40">
          <Search class="w-5 h-5 text-cyan-400 mr-3 shrink-0" />
          <input
            ref="inputRef"
            v-model="query"
            type="text"
            placeholder="Cari nomor SPK, nama Client, ID foto bukti, atau aksi sistem..."
            class="w-full bg-transparent text-sm text-slate-100 placeholder-slate-500 focus:outline-none font-medium"
            autofocus
          />
          <div class="flex items-center gap-1.5 ml-2 shrink-0">
            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-800 text-slate-400 border border-slate-700/80">ESC</span>
          </div>
        </div>

        <!-- Filter Chips -->
        <div class="flex items-center gap-1 px-4 py-2 border-b border-slate-800/50 bg-slate-950/60 overflow-x-auto text-[11px] font-semibold">
          <button
            v-for="cat in categories"
            :key="cat.id"
            @click="activeCategory = cat.id"
            :class="[
              'px-2.5 py-1 rounded-lg transition-colors cursor-pointer shrink-0',
              activeCategory === cat.id
                ? 'bg-cyan-500/15 text-cyan-300 border border-cyan-500/30'
                : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'
            ]"
          >
            {{ cat.label }}
          </button>
        </div>

        <!-- Results List -->
        <div class="max-h-80 overflow-y-auto p-2 space-y-1 custom-scrollbar">
          <!-- Loading state -->
          <div v-if="loading" class="p-6 text-center text-xs text-slate-500 flex items-center justify-center gap-2">
            <Loader2 class="w-4 h-4 animate-spin text-cyan-400" />
            <span>Mencari di seluruh database portal...</span>
          </div>

          <!-- Empty state -->
          <div v-else-if="filteredResults.length === 0" class="p-8 text-center text-xs text-slate-500">
            Tidak ditemukan data untuk kata kunci "<span class="text-slate-300 font-medium">{{ query }}</span>"
          </div>

          <!-- Result Items -->
          <template v-else>
            <div
              v-for="(item, idx) in filteredResults"
              :key="item.id || idx"
              @click="executeItem(item)"
              @mouseenter="selectedIndex = idx"
              :class="[
                'flex items-center justify-between p-2.5 rounded-xl cursor-pointer transition-all duration-150',
                selectedIndex === idx
                  ? 'bg-gradient-to-r from-slate-900 via-slate-850 to-slate-900 border border-cyan-500/30 text-white'
                  : 'hover:bg-slate-900/60 text-slate-300'
              ]"
            >
              <div class="flex items-center gap-3 truncate">
                <div
                  :class="[
                    'w-7 h-7 rounded-lg flex items-center justify-center shrink-0 border text-xs',
                    item.type === 'SPK' ? 'bg-cyan-950/60 text-cyan-400 border-cyan-800/60' :
                    item.type === 'CLIENT' ? 'bg-purple-950/60 text-purple-400 border-purple-800/60' :
                    item.type === 'ACTION' ? 'bg-emerald-950/60 text-emerald-400 border-emerald-800/60' :
                    'bg-slate-900 text-slate-400 border-slate-800'
                  ]"
                >
                  <component :is="item.icon" class="w-3.5 h-3.5" />
                </div>
                <div class="truncate">
                  <div class="text-xs font-bold flex items-center gap-2 truncate">
                    <span class="truncate">{{ item.title }}</span>
                    <span v-if="item.badge" class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-slate-800 text-slate-300 border border-slate-700">
                      {{ item.badge }}
                    </span>
                  </div>
                  <div class="text-[10px] text-slate-400 font-medium truncate mt-0.5">
                    {{ item.subtitle }}
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-2 shrink-0 text-[10px] text-slate-500 font-mono">
                <span>{{ item.meta }}</span>
                <ChevronRight class="w-3.5 h-3.5 text-slate-600" />
              </div>
            </div>
          </template>
        </div>

        <!-- Footer Shortcuts -->
        <div class="px-4 py-2 border-t border-slate-800/80 bg-slate-950/90 flex items-center justify-between text-[11px] text-slate-500 font-mono">
          <div class="flex items-center gap-3">
            <span>↑↓ Navigasi</span>
            <span>↵ Buka</span>
          </div>
          <span class="text-cyan-400 font-sans font-medium text-[10px] flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
            SGX Omnipresent Quick-Action
          </span>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { api } from '../services/api';
import {
  Search,
  FileText,
  Building2,
  Camera,
  CheckSquare,
  BarChart3,
  Users,
  Settings,
  ChevronRight,
  Loader2,
  PlusCircle,
  FileCheck2
} from 'lucide-vue-next';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'select-spk', 'navigate-tab', 'open-create-spk']);

const inputRef = ref(null);
const query = ref('');
const activeCategory = ref('all');
const loading = ref(false);
const selectedIndex = ref(0);

const workOrders = ref([]);
const clients = ref([]);

const categories = [
  { id: 'all', label: 'Semua' },
  { id: 'spk', label: 'SPK' },
  { id: 'client', label: 'Client / Klien' },
  { id: 'action', label: 'Aksi Sistem' }
];

const systemActions = [
  {
    type: 'ACTION',
    icon: PlusCircle,
    title: 'Buat SPK Pekerjaan Baru',
    subtitle: 'Buka formulir penugasan SPK untuk vendor atau tim teknisi',
    meta: 'Tindakan',
    action: () => emit('open-create-spk')
  },
  {
    type: 'ACTION',
    icon: CheckSquare,
    title: 'Review & Verifikasi Bukti',
    subtitle: 'Periksa foto before, process, after dan beri approval/revisi',
    meta: 'Navigasi',
    action: () => emit('navigate-tab', 'admin_review')
  },
  {
    type: 'ACTION',
    icon: FileCheck2,
    title: 'Daftar Berita Acara (BA) Opname',
    subtitle: 'Lihat dokumen BA resmi yang siap cetak & unduh PDF',
    meta: 'Navigasi',
    action: () => emit('navigate-tab', 'admin_ba')
  },
  {
    type: 'ACTION',
    icon: Camera,
    title: 'Evidence Gallery (GPS Verified)',
    subtitle: 'Eksplorasi seluruh foto evidence lapangan ber-watermark',
    meta: 'Navigasi',
    action: () => emit('navigate-tab', 'admin_evidence')
  },
  {
    type: 'ACTION',
    icon: BarChart3,
    title: 'Laporan & Analitik Finansial',
    subtitle: 'Ekspor data SPK ke spreadsheet Excel dan rekap kinerja',
    meta: 'Navigasi',
    action: () => emit('navigate-tab', 'admin_reports')
  }
];

async function loadData() {
  loading.value = true;
  try {
    const [woRes, clientRes] = await Promise.all([
      api.getWorkOrders().catch(() => ({ data: [] })),
      api.getVendors().catch(() => ({ data: [] }))
    ]);
    workOrders.value = woRes.data || [];
    clients.value = clientRes.data || [];
  } catch (e) {
    console.error('Failed to load command palette data', e);
  } finally {
    loading.value = false;
  }
}

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    query.value = '';
    selectedIndex.value = 0;
    activeCategory.value = 'all';
    loadData();
    nextTick(() => {
      inputRef.value?.focus();
    });
  }
});

const allResults = computed(() => {
  const items = [];

  // Actions
  systemActions.forEach(a => items.push(a));

  // SPKs
  workOrders.value.forEach(wo => {
    items.push({
      type: 'SPK',
      icon: FileText,
      id: wo.id,
      title: wo.spk_number || `SPK #${wo.id}`,
      subtitle: `${wo.title} • ${wo.vendor_name || 'Client'}`,
      badge: wo.status,
      meta: wo.area_name || 'Area',
      action: () => emit('select-spk', wo.id)
    });
  });

  // Clients
  clients.value.forEach(cl => {
    items.push({
      type: 'CLIENT',
      icon: Building2,
      id: cl.id,
      title: cl.name,
      subtitle: `Kode: ${cl.code || '-'} • Kontak: ${cl.contact_person || cl.phone || '-'}`,
      badge: 'CLIENT',
      meta: 'Master Data',
      action: () => emit('navigate-tab', 'admin_vendors')
    });
  });

  return items;
});

const filteredResults = computed(() => {
  let list = allResults.value;

  if (activeCategory.value === 'spk') {
    list = list.filter(i => i.type === 'SPK');
  } else if (activeCategory.value === 'client') {
    list = list.filter(i => i.type === 'CLIENT');
  } else if (activeCategory.value === 'action') {
    list = list.filter(i => i.type === 'ACTION');
  }

  if (!query.value.trim()) {
    return list.slice(0, 8);
  }

  const q = query.value.toLowerCase();
  return list.filter(item => {
    return (
      item.title?.toLowerCase().includes(q) ||
      item.subtitle?.toLowerCase().includes(q) ||
      item.badge?.toLowerCase().includes(q)
    );
  }).slice(0, 15);
});

function navigateResults(step) {
  if (filteredResults.value.length === 0) return;
  const newIdx = selectedIndex.value + step;
  if (newIdx < 0) {
    selectedIndex.value = filteredResults.value.length - 1;
  } else if (newIdx >= filteredResults.value.length) {
    selectedIndex.value = 0;
  } else {
    selectedIndex.value = newIdx;
  }
}

function selectCurrent() {
  const item = filteredResults.value[selectedIndex.value];
  if (item) {
    executeItem(item);
  }
}

function executeItem(item) {
  if (item.action) {
    item.action();
  }
  emit('close');
}
</script>
