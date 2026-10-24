<template>
  <span
    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[12px] font-semibold ring-1 ring-inset"
    :class="variantClasses"
  >
    <span class="h-1.5 w-1.5 rounded-full" :class="[dotClasses, pulse ? 'dot-pulse' : '']"></span>
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: { type: String, required: true },
  variant: { type: String, default: 'neutral' }, // success | danger | warning | info | brand | neutral
  pulse: { type: Boolean, default: false },
});

const variants = {
  success: { badge: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', dot: 'bg-emerald-500' },
  danger:  { badge: 'bg-rose-50 text-rose-700 ring-rose-600/20',         dot: 'bg-rose-500' },
  warning: { badge: 'bg-amber-50 text-amber-800 ring-amber-600/25',      dot: 'bg-amber-500' },
  info:    { badge: 'bg-sky-50 text-sky-700 ring-sky-600/20',            dot: 'bg-sky-500' },
  brand:   { badge: 'bg-brand-50 text-brand-700 ring-brand-600/20',      dot: 'bg-brand-500' },
  neutral: { badge: 'bg-stone-100 text-stone-600 ring-stone-500/15',     dot: 'bg-stone-400' },
};

const variantClasses = computed(() => variants[props.variant]?.badge || variants.neutral.badge);
const dotClasses = computed(() => variants[props.variant]?.dot || variants.neutral.dot);
</script>
