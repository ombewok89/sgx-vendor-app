<template>
  <span
    :class="[
      'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border backdrop-blur-md transition-all duration-200',
      config.bg,
      config.text,
      config.border,
      config.shadow,
      className
    ]"
  >
    <span :class="['w-1.5 h-1.5 rounded-full animate-pulse', config.dot]"></span>
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
  DRAFT: { label: 'Draft', bg: 'bg-slate-500/10', text: 'text-slate-600', border: 'border-slate-300/80', dot: 'bg-slate-400', shadow: '' },
  READY: { label: 'Siap Ditugaskan', bg: 'bg-sky-500/10', text: 'text-sky-700', border: 'border-sky-300/80', dot: 'bg-sky-500', shadow: 'shadow-sm shadow-sky-500/10' },
  ASSIGNED: { label: 'Tim Ditugaskan', bg: 'bg-blue-500/10', text: 'text-blue-700', border: 'border-blue-300/80', dot: 'bg-blue-500', shadow: 'shadow-sm shadow-blue-500/10' },
  CHECKED_IN: { label: 'Check-In GPS', bg: 'bg-indigo-500/10', text: 'text-indigo-700', border: 'border-indigo-300/80', dot: 'bg-indigo-500', shadow: 'shadow-sm shadow-indigo-500/10' },
  IN_PROGRESS: { label: 'Sedang Dikerjakan', bg: 'bg-amber-500/15', text: 'text-amber-700', border: 'border-amber-300/80', dot: 'bg-amber-500', shadow: 'shadow-sm shadow-amber-500/10' },
  SUBMITTED: { label: 'Menunggu Review', bg: 'bg-purple-500/15', text: 'text-purple-700', border: 'border-purple-300/80', dot: 'bg-purple-500', shadow: 'shadow-sm shadow-purple-500/10' },
  UNDER_REVIEW: { label: 'Sedang Direview', bg: 'bg-purple-500/20', text: 'text-purple-800', border: 'border-purple-400/80', dot: 'bg-purple-600', shadow: 'shadow-sm shadow-purple-500/15' },
  REVIEW: { label: 'Menunggu Review', bg: 'bg-purple-500/15', text: 'text-purple-700', border: 'border-purple-300/80', dot: 'bg-purple-500', shadow: 'shadow-sm shadow-purple-500/10' },
  REVISION: { label: 'Perlu Revisi', bg: 'bg-rose-500/15', text: 'text-rose-700', border: 'border-rose-300/80', dot: 'bg-rose-500', shadow: 'shadow-sm shadow-rose-500/10' },
  APPROVED: { label: 'Disetujui (Approved)', bg: 'bg-emerald-500/15', text: 'text-emerald-700', border: 'border-emerald-300/80', dot: 'bg-emerald-500', shadow: 'shadow-sm shadow-emerald-500/10' },
  BA_OPNAME: { label: 'BA Opname Terbit', bg: 'bg-teal-500/15', text: 'text-teal-700', border: 'border-teal-300/80', dot: 'bg-teal-500', shadow: 'shadow-sm shadow-teal-500/10' },
  COMPLETED: { label: 'Selesai', bg: 'bg-emerald-500/20', text: 'text-emerald-800', border: 'border-emerald-400/80', dot: 'bg-emerald-600', shadow: 'shadow-sm shadow-emerald-500/15' },
};

const config = computed(() => {
  return STATUS_CONFIG[props.status] || {
    label: props.status || 'Unknown',
    bg: 'bg-slate-100',
    text: 'text-slate-700',
    border: 'border-slate-200',
    dot: 'bg-slate-400',
    shadow: ''
  };
});
</script>
