<template>
  <div class="mb-6 animate-fade-up">
    <!-- Breadcrumb -->
    <nav v-if="breadcrumbs.length" class="mb-1.5 flex items-center gap-1.5 text-[12px] text-stone-500">
      <template v-for="(crumb, i) in breadcrumbs" :key="i">
        <AppIcon v-if="i > 0" name="chevron-right" class="h-3 w-3 text-stone-300" />
        <Link
          v-if="crumb.href"
          :href="crumb.href"
          class="hover:text-brand-700 transition font-medium"
        >{{ crumb.label }}</Link>
        <span v-else class="text-stone-700 font-medium">{{ crumb.label }}</span>
      </template>
    </nav>

    <div class="flex flex-wrap items-end justify-between gap-3">
      <div class="min-w-0">
        <h1 class="text-[22px] font-bold tracking-tight text-stone-900 leading-tight">{{ title }}</h1>
        <p v-if="description" class="mt-1 text-[13.5px] text-stone-500 max-w-2xl">{{ description }}</p>
      </div>
      <div v-if="$slots.actions" class="flex items-center gap-2 shrink-0">
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppIcon from './AppIcon.vue';

defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
  breadcrumbs: { type: Array, default: () => [] },
});
</script>
