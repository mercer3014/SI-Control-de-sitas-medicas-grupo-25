<template>
  <component
    :is="href ? Link : 'div'"
    :href="href"
    class="group relative overflow-hidden rounded-2xl border border-stone-200/80 bg-white p-5 shadow-card transition-all duration-200 animate-fade-up"
    :class="{ 'hover:shadow-card-hover hover:-translate-y-0.5 hover:border-stone-300 cursor-pointer': href }"
  >
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="text-[12px] font-semibold uppercase tracking-[0.08em] text-stone-500">{{ label }}</p>
        <p class="mt-2 text-[32px] font-bold leading-none tracking-tight text-stone-900 tabular-nums">{{ value }}</p>
        <p v-if="hint" class="mt-2 text-[12.5px] text-stone-500 flex items-center gap-1.5">
          <span v-if="hintDot" class="h-1.5 w-1.5 rounded-full" :class="hintDot"></span>
          {{ hint }}
        </p>
      </div>
      <div
        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-transform duration-200 group-hover:scale-105"
        :class="iconBg"
      >
        <AppIcon :name="icon" class="h-[22px] w-[22px]" :class="iconColor" :stroke-width="1.6" />
      </div>
    </div>
    <!-- Marca de agua decorativa -->
    <AppIcon
      :name="icon"
      class="pointer-events-none absolute -bottom-5 -right-5 h-24 w-24 opacity-[0.045] text-stone-900 transition-transform duration-300 group-hover:scale-110 group-hover:opacity-[0.07]"
      :stroke-width="1"
    />
  </component>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppIcon from './AppIcon.vue';

const props = defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  icon: { type: String, required: true },
  hint: { type: String, default: '' },
  hintDot: { type: String, default: '' },
  tone: { type: String, default: 'brand' },
  href: { type: String, default: null },
});

const tones = {
  brand:   { iconBg: 'bg-brand-100',   iconColor: 'text-brand-700' },
  sky:     { iconBg: 'bg-sky-100',     iconColor: 'text-sky-700' },
  violet:  { iconBg: 'bg-violet-100',  iconColor: 'text-violet-700' },
  amber:   { iconBg: 'bg-amber-100',   iconColor: 'text-amber-700' },
  emerald: { iconBg: 'bg-emerald-100', iconColor: 'text-emerald-700' },
  rose:    { iconBg: 'bg-rose-100',    iconColor: 'text-rose-700' },
};

const iconBg = computed(() => tones[props.tone]?.iconBg || tones.brand.iconBg);
const iconColor = computed(() => tones[props.tone]?.iconColor || tones.brand.iconColor);
</script>
