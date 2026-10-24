<template>
  <div class="mb-5 flex flex-wrap items-center gap-2.5 rounded-2xl border border-stone-200/80 bg-white p-3 shadow-card animate-fade-up">
    <!-- Buscador -->
    <div class="relative min-w-[220px] flex-1">
      <AppIcon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" />
      <input
        :value="search"
        type="text"
        :placeholder="searchPlaceholder"
        class="input-clinica pl-9"
        @input="$emit('update:search', $event.target.value)"
        @keyup.enter="$emit('apply')"
      />
    </div>

    <slot />

    <button
      v-if="hasFilters"
      type="button"
      class="btn-clinica border border-stone-200 bg-white text-stone-500 hover:text-stone-700 hover:bg-stone-50 !px-3"
      title="Limpiar filtros"
      @click="$emit('clear')"
    >
      <AppIcon name="refresh" class="h-4 w-4" />
      <span class="hidden sm:inline">Limpiar</span>
    </button>
  </div>
</template>

<script setup>
import AppIcon from './AppIcon.vue';

defineProps({
  search: { type: String, default: '' },
  searchPlaceholder: { type: String, default: 'Buscar…' },
  hasFilters: { type: Boolean, default: false },
});

defineEmits(['update:search', 'apply', 'clear']);
</script>
