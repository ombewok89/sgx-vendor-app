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
    <span :class="['w-1.5 h-1.5 rounded-none shrink-0', config.dot]"></span>
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
    bg: 'bg-[#141820]',
    text: 'text-slate-400',
    border: 'border-slate-700/60',
    dot: 'bg-slate-400'
  },
  READY: {
    label: 'SIAP PENUGASAN',
    bg: 'bg-[#18202A]',
    text: 'text-slate-300',
    border: 'border-slate-500/60',
    dot: 'bg-slate-400'
  },
  ASSIGNED: {
    label: 'TIM DITUGASKAN',
    bg: 'bg-[#08203E]',
    text: 'text-sky-300',
    border: 'border-sky-500/70',
    dot: 'bg-sky-400 animate-pulse'
  },
  CHECKED_IN: {
    label: 'GPS CHECK-IN',
    bg: 'bg-[#082329]',
    text: 'text-teal-300',
    border: 'border-teal-500/60',
    dot: 'bg-teal-400 animate-pulse'
  },
  IN_PROGRESS: {
    label: 'PROSES LAPANGAN',
    bg: 'bg-[#2A2206]',
    text: 'text-[#FACC15]',
    border: 'border-[#FACC15]',
    dot: 'bg-[#FACC15] animate-pulse'
  },
  SUBMITTED: {
    label: 'ANTRIAN REVIEW',
    bg: 'bg-[#1E1229]',
    text: 'text-purple-300',
    border: 'border-purple-400/60',
    dot: 'bg-purple-400 animate-pulse'
  },
  UNDER_REVIEW: {
    label: 'SEDANG DIREVIEW',
    bg: 'bg-[#16142E]',
    text: 'text-indigo-300',
    border: 'border-indigo-400/60',
    dot: 'bg-indigo-400'
  },
  REVIEW: {
    label: 'ANTRIAN REVIEW',
    bg: 'bg-[#1E1229]',
    text: 'text-purple-300',
    border: 'border-purple-400/60',
    dot: 'bg-purple-400 animate-pulse'
  },
  REVISION: {
    label: 'PERLU REVISI',
    bg: 'bg-[#291014]',
    text: 'text-rose-400',
    border: 'border-rose-500',
    dot: 'bg-rose-500 animate-ping'
  },
  APPROVED: {
    label: 'DISETUJUI // APPROVED',
    bg: 'bg-[#092218]',
    text: 'text-emerald-400',
    border: 'border-emerald-500/60',
    dot: 'bg-emerald-400'
  },
  BA_OPNAME: {
    label: 'BA OPNAME TERBIT',
    bg: 'bg-[#092218]',
    text: 'text-emerald-300',
    border: 'border-emerald-500/70',
    dot: 'bg-emerald-300'
  },
  COMPLETED: {
    label: '100% SELESAI',
    bg: 'bg-[#092218]',
    text: 'text-emerald-300',
    border: 'border-emerald-400',
    dot: 'bg-emerald-400'
  },
};

const config = computed(() => {
  return STATUS_CONFIG[props.status] || {
    label: props.status || 'UNKNOWN',
    bg: 'bg-[#141820]',
    text: 'text-slate-400',
    border: 'border-[#232A36]',
    dot: 'bg-slate-400'
  };
});
</script>
