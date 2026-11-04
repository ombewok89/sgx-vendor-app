<template>
  <div class="w-full py-2 font-mono text-slate-100">
    <!-- Percentage Progress Bar -->
    <div class="mb-3">
      <div class="flex items-center justify-between text-xs font-bold mb-1.5">
        <span class="text-slate-400 uppercase tracking-wider text-[11px]">// PROGRES PENYELESAIAN SPK:</span>
        <span class="text-[#FACC15] font-bold text-xs">{{ effectiveProgressPercent }}%</span>
      </div>
      <div class="w-full h-2 bg-[#0B0C0E] rounded-xs overflow-hidden border border-[#232A36]">
        <div
          :class="[
            'h-full transition-all duration-500 ease-out',
            isRevision ? 'bg-rose-500' : 'bg-gradient-to-r from-[#FACC15] via-teal-400 to-emerald-400'
          ]"
          :style="{ width: `${effectiveProgressPercent}%` }"
        />
      </div>
    </div>

    <!-- Horizontal Stepper — 7 canonical steps -->
    <div class="grid grid-cols-4 sm:grid-cols-7 gap-1.5">
      <div
        v-for="(step, idx) in STEPS"
        :key="step.id"
        class="flex flex-col items-center text-center group"
      >
        <div
          :class="[
            'w-6 h-6 rounded-xs flex items-center justify-center text-[10px] font-bold transition-all mb-1 border',
            isStepDone(idx)
              ? 'bg-emerald-950/80 text-emerald-400 border-emerald-500/60 shadow-xs'
              : idx === currentIndex
              ? isRevision
                ? 'bg-rose-950 text-rose-400 border-rose-500 ring-2 ring-rose-500/30'
                : 'bg-[#1C2028] text-[#FACC15] border-[#FACC15] ring-2 ring-[#FACC15]/20 animate-pulse'
              : 'bg-[#0B0C0E] text-slate-500 border-[#232A36]'
          ]"
        >
          <CheckCircle2 v-if="isStepDone(idx)" class="w-3.5 h-3.5" />
          <AlertCircle v-else-if="idx === currentIndex && isRevision" class="w-3.5 h-3.5" />
          <Clock v-else-if="idx === currentIndex" class="w-3.5 h-3.5" />
          <span v-else>{{ idx + 1 }}</span>
        </div>
        <span
          :class="[
            'text-[9px] sm:text-[10px] leading-tight transition-colors font-bold uppercase tracking-wider',
            isStepDone(idx)
              ? 'text-emerald-400'
              : idx === currentIndex && isRevision
              ? 'text-rose-400'
              : idx === currentIndex
              ? 'text-[#FACC15]'
              : 'text-slate-500'
          ]"
        >
          {{ idx === currentIndex && isRevision ? 'Revisi' : step.label }}
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
    case 'APPROVED':
    case 'BA_OPNAME':
    case 'COMPLETED':    return 6;   // Semua selesai — BA Opname Selesai (step 7) aktif/done
    default:             return 0;
  }
});

// Returns true if step[idx] should show green checkmark (done)
function isStepDone(idx) {
  // APPROVED/BA_OPNAME/COMPLETED → semua 7 step hijau ✓
  // (Saat approve, BA Opname otomatis terbit dan status → COMPLETED)
  if (['APPROVED', 'BA_OPNAME', 'COMPLETED'].includes(props.status)) return true;
  // Otherwise: semua step sebelum currentIndex
  return idx < currentIndex.value;
}

// Effective progress: gunakan prop jika > 0, fallback ke nilai berbasis status
const STATUS_PROGRESS = {
  DRAFT: 5, READY: 15, ASSIGNED: 30, CHECKED_IN: 45,
  IN_PROGRESS: 60, SUBMITTED: 80, UNDER_REVIEW: 80,
  REVIEW: 80, REVISION: 65,
  APPROVED: 100,   // BA auto-terbit saat approve → selesai 100%
  BA_OPNAME: 100, COMPLETED: 100
};

const effectiveProgressPercent = computed(() => {
  if (props.progressPercent > 0) return props.progressPercent;
  return STATUS_PROGRESS[props.status] ?? 5;
});

const isRevision = computed(() => props.status === 'REVISION');
</script>
