<template>
  <div class="flex flex-col items-center justify-center px-6 py-14 text-center animate-fade-in">
    <div class="relative mb-4">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-stone-100">
        <AppIcon :name="icon" class="h-8 w-8 text-stone-400" :stroke-width="1.4" />
      </div>
      <div class="absolute -right-1 -bottom-1 flex h-6 w-6 items-center justify-center rounded-full bg-white shadow-sm border border-stone-100">
        <AppIcon name="search" class="h-3 w-3 text-stone-400" />
      </div>
    </div>
    <h3 class="text-[14.5px] font-bold text-stone-800">{{ title }}</h3>
    <p class="mt-1 max-w-sm text-[13px] leading-relaxed text-stone-500">{{ message }}</p>
    <div v-if="$slots.default" class="mt-5">
      <slot />
    </div>
  </div>
</template>

<script setup>
import AppIcon from './AppIcon.vue';

defineProps({
  icon: { type: String, default: 'clipboard' },
  title: { type: String, default: 'Sin resultados' },
  message: { type: String, default: 'No se encontraron registros para mostrar.' },
});
</script>
