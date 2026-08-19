<template>
  <div class="w-full py-3">
    <!-- Percentage Progress Bar -->
    <div class="mb-4">
      <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
        <span class="text-slate-600 font-medium">Progress Penyelesaian Sistem:</span>
        <span class="text-brand-700 font-bold font-mono">{{ effectiveProgressPercent }}%</span>
      </div>
      <div class="w-full h-2.5 bg-slate-100/90 rounded-full overflow-hidden border border-slate-200/80 p-0.5 backdrop-blur-xs">
        <div
          :class="[
            'h-full transition-all duration-700 ease-out rounded-full shadow-xs',
            isRevision ? 'bg-gradient-to-r from-rose-500 to-amber-500' : 'bg-gradient-to-r from-brand-600 via-indigo-500 to-emerald-500'
          ]"
          :style="{ width: `${effectiveProgressPercent}%` }"
        />
      </div>
    </div>

    <!-- Horizontal Stepper — 7 canonical steps -->
    <div class="grid grid-cols-4 sm:grid-cols-7 gap-2">
      <div
        v-for="(step, idx) in STEPS"
        :key="step.id"
        class="flex flex-col items-center text-center group"
      >
        <div
          :class="[
            'w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 mb-1.5 shadow-sm',
            isStepDone(idx)
              ? 'bg-emerald-500 text-white shadow-emerald-500/20'
              : idx === currentIndex
              ? isRevision
                ? 'bg-rose-500 text-white ring-4 ring-rose-100 shadow-rose-500/30'
                : 'bg-brand-600 text-white ring-4 ring-brand-100 shadow-brand-600/30 animate-pulse'
              : 'bg-slate-100 text-slate-400 border border-slate-200/80'
          ]"
        >
          <CheckCircle2 v-if="isStepDone(idx)" class="w-4 h-4" />
          <AlertCircle v-else-if="idx === currentIndex && isRevision" class="w-4 h-4" />
          <Clock v-else-if="idx === currentIndex" class="w-4 h-4" />
          <span v-else class="text-[11px]">{{ idx + 1 }}</span>
        </div>
        <span
          :class="[
            'text-[10px] leading-tight transition-colors duration-200',
            isStepDone(idx)
              ? 'text-slate-800 font-semibold'
              : idx === currentIndex && isRevision
              ? 'text-rose-600 font-bold'
              : idx === currentIndex
              ? 'text-brand-700 font-bold'
              : 'text-slate-400 font-medium'
          ]"
        >
          {{ idx === currentIndex && isRevision ? 'Perlu Revisi' : step.label }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { CheckCircle2, AlertCircle, Clock } from 'lucide-vue-next';

const props = defineProps({
  status: {
    type: String,
    default: 'READY'
  },
  progressPercent: {
    type: Number,
    default: 0
  },
  hasBa: {
    type: Boolean,
    default: false
  }
});

// Canonical 7-step lifecycle
const STEPS = [
  { id: 'READY',      label: 'SPK Dibuat' },
  { id: 'ASSIGNED',   label: 'Tim Ditugaskan' },
  { id: 'CHECKED_IN', label: 'Check-In GPS' },
  { id: 'IN_PROGRESS',label: 'Pelaksanaan' },
  { id: 'SUBMITTED',  label: 'Evidence Submit' },
  { id: 'APPROVED',   label: 'Review Disetujui' },
  { id: 'BA_OPNAME',  label: 'BA Opname Selesai' }
];

// Status → current active step index (0-based)
const currentIndex = computed(() => {
  switch (props.status) {
    case 'DRAFT':
    case 'READY':        return 0;
    case 'ASSIGNED':     return 1;
    case 'CHECKED_IN':   return 2;
    case 'IN_PROGRESS':  return 3;
    case 'SUBMITTED':
    case 'UNDER_REVIEW':
    case 'REVIEW':       return 4;
    case 'REVISION':     return 4;
    case 'APPROVED':     return 5;   // Review Disetujui sudah selesai, BA menjadi langkah aktif berikutnya
    case 'BA_OPNAME':
    case 'COMPLETED':    return 6;   // Semua selesai, step terakhir aktif
    default:             return 0;
  }
});

// Returns true if step[idx] should show green checkmark (done)
function isStepDone(idx) {
  // Terminal states → semua 7 step hijau ✓
  if (['BA_OPNAME', 'COMPLETED'].includes(props.status)) return true;
  // APPROVED → step 0–5 hijau, step 6 (BA Opname) aktif berikutnya
  if (props.status === 'APPROVED') return idx < 6;
  // Otherwise: semua step sebelum currentIndex
  return idx < currentIndex.value;
}

// Effective progress: gunakan prop jika > 0, fallback ke nilai berbasis status
const STATUS_PROGRESS = {
  DRAFT: 5, READY: 15, ASSIGNED: 30, CHECKED_IN: 45,
  IN_PROGRESS: 60, SUBMITTED: 80, UNDER_REVIEW: 80,
  REVIEW: 80, REVISION: 65, APPROVED: 90,
  BA_OPNAME: 100, COMPLETED: 100
};

const effectiveProgressPercent = computed(() => {
  if (props.progressPercent > 0) return props.progressPercent;
  return STATUS_PROGRESS[props.status] ?? 5;
});

const isRevision = computed(() => props.status === 'REVISION');
</script>
