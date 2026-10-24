<template>
  <div
    class="flex shrink-0 items-center justify-center rounded-full font-bold uppercase select-none"
    :class="[sizeClasses, palette.bg, palette.text]"
    :title="name"
  >{{ initials }}</div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  name: { type: String, default: 'U' },
  size: { type: String, default: 'md' }, // xs | sm | md | lg
});

const sizes = {
  xs: 'h-6 w-6 text-[9px]',
  sm: 'h-8 w-8 text-[11px]',
  md: 'h-9 w-9 text-[12px]',
  lg: 'h-11 w-11 text-[14px]',
};
const sizeClasses = computed(() => sizes[props.size] || sizes.md);

const palettes = [
  { bg: 'bg-brand-100',  text: 'text-brand-700' },
  { bg: 'bg-sky-100',    text: 'text-sky-700' },
  { bg: 'bg-violet-100', text: 'text-violet-700' },
  { bg: 'bg-amber-100',  text: 'text-amber-800' },
  { bg: 'bg-rose-100',   text: 'text-rose-700' },
  { bg: 'bg-emerald-100', text: 'text-emerald-700' },
];

const initials = computed(() => {
  const parts = (props.name || 'U').trim().split(/\s+/);
  const first = parts[0]?.charAt(0) || 'U';
  const last = parts.length > 1 ? parts[parts.length - 1].charAt(0) : '';
  return (first + last).toUpperCase();
});

const palette = computed(() => {
  let hash = 0;
  for (const ch of props.name) hash = (hash * 31 + ch.charCodeAt(0)) >>> 0;
  return palettes[hash % palettes.length];
});
</script>
