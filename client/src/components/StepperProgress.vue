<template>
  <div class="w-full py-3">
    <!-- Percentage Progress Bar -->
    <div class="mb-4">
      <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
        <span class="text-slate-600 font-medium">Progress Penyelesaian Sistem:</span>
        <span class="text-brand-700 font-bold font-mono">{{ progressPercent }}%</span>
      </div>
      <div class="w-full h-2.5 bg-slate-100/90 rounded-full overflow-hidden border border-slate-200/80 p-0.5 backdrop-blur-xs">
        <div
          :class="[
            'h-full transition-all duration-700 ease-out rounded-full shadow-xs',
            isRevision ? 'bg-gradient-to-r from-rose-500 to-amber-500' : 'bg-gradient-to-r from-brand-600 via-indigo-500 to-emerald-500'
          ]"
          :style="{ width: `${progressPercent}%` }"
        />
      </div>
    </div>

    <!-- Horizontal Stepper -->
    <div class="grid grid-cols-4 sm:grid-cols-8 gap-2">
      <div
        v-for="(step, idx) in STEPS"
        :key="step.id"
        class="flex flex-col items-center text-center group"
      >
        <div
          :class="[
            'w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 mb-1.5 shadow-sm',
            idx < currentIndex || status === 'COMPLETED'
              ? 'bg-emerald-500 text-white shadow-emerald-500/20'
              : idx === currentIndex && status !== 'COMPLETED'
              ? isRevision
                ? 'bg-rose-500 text-white ring-4 ring-rose-100 shadow-rose-500/30'
                : 'bg-brand-600 text-white ring-4 ring-brand-100 shadow-brand-600/30 animate-pulse'
              : 'bg-slate-100 text-slate-400 border border-slate-200/80'
          ]"
        >
          <CheckCircle2 v-if="idx < currentIndex || status === 'COMPLETED'" class="w-4 h-4" />
          <AlertCircle v-else-if="idx === currentIndex && isRevision" class="w-4 h-4" />
          <Clock v-else-if="idx === currentIndex" class="w-4 h-4" />
          <span v-else class="text-[11px]">{{ idx + 1 }}</span>
        </div>
        <span
          :class="[
            'text-[10px] leading-tight transition-colors duration-200',
            idx < currentIndex || status === 'COMPLETED'
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
  }
});

const STEPS = [
  { id: 'READY', label: 'SPK Dibuat' },
  { id: 'ASSIGNED', label: 'Tim Ditugaskan' },
  { id: 'CHECKED_IN', label: 'Check-In GPS' },
  { id: 'IN_PROGRESS', label: 'Pelaksanaan' },
  { id: 'SUBMITTED', label: 'Evidence Submit' },
  { id: 'APPROVED', label: 'Review Disetujui' },
  { id: 'BA_OPNAME', label: 'BA Opname' },
  { id: 'COMPLETED', label: 'Selesai' }
];

const currentIndex = computed(() => {
  switch (props.status) {
    case 'DRAFT': return 0;
    case 'READY': return 0;
    case 'ASSIGNED': return 1;
    case 'CHECKED_IN': return 2;
    case 'IN_PROGRESS': return 3;
    case 'SUBMITTED':
    case 'UNDER_REVIEW': return 4;
    case 'REVISION': return 4;
    case 'APPROVED': return 5;
    case 'BA_OPNAME': return 6;
    case 'COMPLETED': return 7;
    default: return 0;
  }
});

const isRevision = computed(() => props.status === 'REVISION');
</script>
