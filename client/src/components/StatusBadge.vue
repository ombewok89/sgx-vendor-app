<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 px-2 py-0.5 rounded-xs text-[10px] font-mono font-bold border transition-colors uppercase tracking-wider',
      config.bg,
      config.text,
      config.border,
      className
    ]"
  >
    <span :class="['w-1.5 h-1.5 rounded-none', config.dot]"></span>
    {{ config.label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    default: ''
  },
  className: {
    type: String,
    default: ''
  }
});

const STATUS_CONFIG = {
  DRAFT: {
    label: 'DRAFT',
    bg: 'bg-[#1C2028]',
    text: 'text-slate-400',
    border: 'border-[#232A36]',
    dot: 'bg-slate-500'
  },
  READY: {
    label: 'SIAP PENUGASAN',
    bg: 'bg-[#1C2028]',
    text: 'text-slate-200',
    border: 'border-[#475569]',
    dot: 'bg-slate-300'
  },
  ASSIGNED: {
    label: 'TIM DITUGASKAN',
    bg: 'bg-[#1C2028]',
    text: 'text-[#FACC15]',
    border: 'border-[#FACC15]/40',
    dot: 'bg-[#FACC15] animate-pulse'
  },
  CHECKED_IN: {
    label: 'GPS CHECK-IN',
    bg: 'bg-[#1C2028]',
    text: 'text-[#10B981]',
    border: 'border-[#10B981]/50',
    dot: 'bg-[#10B981] animate-pulse'
  },
  IN_PROGRESS: {
    label: 'PROSES LAPANGAN',
    bg: 'bg-[#1C2028]',
    text: 'text-[#FACC15]',
    border: 'border-[#FACC15]',
    dot: 'bg-[#FACC15] animate-pulse'
  },
  SUBMITTED: {
    label: 'ANTRIAN REVIEW',
    bg: 'bg-[#1C2028]',
    text: 'text-amber-300',
    border: 'border-amber-400/50',
    dot: 'bg-amber-400 animate-pulse'
  },
  UNDER_REVIEW: {
    label: 'SEDANG DIREVIEW',
    bg: 'bg-[#1C2028]',
    text: 'text-amber-300',
    border: 'border-amber-400',
    dot: 'bg-amber-400'
  },
  REVIEW: {
    label: 'ANTRIAN REVIEW',
    bg: 'bg-[#1C2028]',
    text: 'text-amber-300',
    border: 'border-amber-400/50',
    dot: 'bg-amber-400 animate-pulse'
  },
  REVISION: {
    label: 'PERLU REVISI',
    bg: 'bg-[#1C2028]',
    text: 'text-[#EF4444]',
    border: 'border-[#EF4444]',
    dot: 'bg-[#EF4444] animate-ping'
  },
  APPROVED: {
    label: 'DISパTUI // APPROVED',
    bg: 'bg-[#13171F]',
    text: 'text-[#10B981]',
    border: 'border-[#10B981]',
    dot: 'bg-[#10B981]'
  },
  BA_OPNAME: {
    label: 'BA OPNAME TERBIT',
    bg: 'bg-[#13171F]',
    text: 'text-[#10B981]',
    border: 'border-[#10B981]',
    dot: 'bg-[#10B981]'
  },
  COMPLETED: {
    label: '100% SELESAI',
    bg: 'bg-[#13171F]',
    text: 'text-[#10B981]',
    border: 'border-[#10B981]',
    dot: 'bg-[#10B981]'
  },
};

const config = computed(() => {
  return STATUS_CONFIG[props.status] || {
    label: props.status || 'UNKNOWN',
    bg: 'bg-[#1C2028]',
    text: 'text-slate-400',
    border: 'border-[#232A36]',
    dot: 'bg-slate-400'
  };
});
</script>
