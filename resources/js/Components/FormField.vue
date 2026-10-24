<template>
  <div>
    <label v-if="label" :for="id" class="mb-1.5 flex items-center gap-1 text-[12.5px] font-semibold text-stone-700">
      {{ label }}
      <span v-if="required" class="text-rose-500" title="Campo obligatorio">*</span>
      <span v-if="optional" class="text-[11px] font-normal text-stone-400">(opcional)</span>
    </label>
    <slot :inputClass="inputClass" :id="id" />
    <p v-if="error" class="mt-1.5 flex items-center gap-1 text-[12px] font-medium text-rose-600 animate-fade-in">
      <AppIcon name="alert" class="h-3.5 w-3.5 shrink-0" />
      {{ error }}
    </p>
    <p v-else-if="hint" class="mt-1.5 text-[12px] text-stone-400">{{ hint }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import AppIcon from './AppIcon.vue';

const props = defineProps({
  id: { type: String, default: '' },
  label: { type: String, default: '' },
  error: { type: String, default: '' },
  hint: { type: String, default: '' },
  required: { type: Boolean, default: false },
  optional: { type: Boolean, default: false },
});

const inputClass = computed(() => ['input-clinica', props.error ? 'input-error' : '']);
</script>
