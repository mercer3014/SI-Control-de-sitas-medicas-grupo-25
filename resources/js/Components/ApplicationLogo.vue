<template>
  <div class="flex items-center gap-3 select-none">
    <!-- Emblema de la Clínica -->
    <div
      :class="[
        'flex shrink-0 items-center justify-center shadow-sm transition-transform duration-200',
        sizeClasses.box,
        customClass
      ]"
    >
      <svg
        :class="sizeClasses.svg"
        viewBox="0 0 48 48"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <!-- Fondo con esquinas redondeadas y gradiente médico -->
        <defs>
          <linearGradient id="vsGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#059669" />
            <stop offset="100%" stop-color="#0d9488" />
          </linearGradient>
          <linearGradient id="vsPulse" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#ffffff" />
            <stop offset="100%" stop-color="#ecfdf5" />
          </linearGradient>
          <filter id="softGlow" x="-10%" y="-10%" width="120%" height="120%">
            <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="#059669" flood-opacity="0.3" />
          </filter>
        </defs>

        <rect width="48" height="48" rx="13" fill="url(#vsGradient)" />

        <!-- Silueta sutil de Cruz Médica de fondo -->
        <path
          d="M20 11C20 9.89543 20.8954 9 22 9H26C27.1046 9 28 9.89543 28 11V19H36C37.1046 19 38 19.8954 38 21V25C38 26.1046 37.1046 27 36 27H28V37C28 38.1046 27.1046 39 26 39H22C20.8954 39 20 38.1046 20 37V27H12C10.8954 27 10 26.1046 10 25V21C10 19.8954 10.8954 19 12 19H20V11Z"
          fill="white"
          fill-opacity="0.18"
        />

        <!-- Línea de Pulso Vital / Cardio (Vida Salud) -->
        <path
          d="M9 24.5H16L18.5 17L22.5 32L26 21L28.5 27L30.5 24.5H39"
          stroke="url(#vsPulse)"
          stroke-width="3"
          stroke-linecap="round"
          stroke-linejoin="round"
        />

        <!-- Punto de vida / chispa médica superior -->
        <circle cx="34" cy="14" r="2.5" fill="#a7f3d0" />
      </svg>
    </div>

    <!-- Tipografía de Marca: Clínica Vida Salud -->
    <div v-if="!collapsed" class="min-w-0 flex flex-col justify-center leading-none">
      <span
        :class="[
          'font-semibold uppercase tracking-widest text-[9.5px]',
          variant === 'dark' ? 'text-emerald-400' : 'text-emerald-600'
        ]"
      >
        CLÍNICA
      </span>
      <span
        :class="[
          'font-bold tracking-tight',
          sizeClasses.title,
          variant === 'dark' ? 'text-white' : 'text-slate-900'
        ]"
      >
        Vida Salud
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  size: {
    type: String,
    default: 'md', // sm, md, lg, xl
    validator: (v) => ['sm', 'md', 'lg', 'xl'].includes(v)
  },
  variant: {
    type: String,
    default: 'dark', // 'dark' (para fondos oscuros como sidebar) o 'light' (para login o fondos claros)
    validator: (v) => ['dark', 'light'].includes(v)
  },
  collapsed: {
    type: Boolean,
    default: false
  },
  customClass: {
    type: String,
    default: ''
  }
});

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return {
        box: 'h-8 w-8',
        svg: 'h-8 w-8',
        title: 'text-sm'
      };
    case 'lg':
      return {
        box: 'h-11 w-11',
        svg: 'h-11 w-11',
        title: 'text-xl'
      };
    case 'xl':
      return {
        box: 'h-14 w-14',
        svg: 'h-14 w-14',
        title: 'text-2xl'
      };
    case 'md':
    default:
      return {
        box: 'h-9 w-9',
        svg: 'h-9 w-9',
        title: 'text-base'
      };
  }
});
</script>
