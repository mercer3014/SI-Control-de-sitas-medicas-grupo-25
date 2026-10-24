<template>
  <div v-if="paginator && paginator.last_page > 1" class="flex flex-wrap items-center justify-between gap-3 border-t border-stone-100 px-4 py-3">
    <p class="text-[12.5px] text-stone-500">
      Mostrando <span class="font-semibold text-stone-700">{{ paginator.from ?? 0 }}–{{ paginator.to ?? 0 }}</span>
      de <span class="font-semibold text-stone-700">{{ paginator.total }}</span> registros
    </p>

    <div class="flex items-center gap-1">
      <template v-for="(link, i) in paginator.links" :key="i">
        <span
          v-if="!link.url"
          class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-[12.5px] text-stone-300"
          v-html="link.label"
        ></span>
        <Link
          v-else
          :href="link.url"
          preserve-scroll
          class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-[12.5px] font-semibold transition cursor-pointer"
          :class="link.active
            ? 'bg-brand-600 text-white shadow-sm'
            : 'text-stone-600 hover:bg-stone-100'"
          v-html="link.label"
        ></Link>
      </template>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  paginator: { type: Object, default: null },
});
</script>
