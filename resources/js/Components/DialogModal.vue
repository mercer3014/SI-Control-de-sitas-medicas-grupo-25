<template>
  <Modal
    :show="show"
    :title="title"
    :icon="iconName"
    :tone="type"
    size="sm"
    @close="$emit('close')"
  >
    <p class="text-[13.5px] leading-relaxed text-stone-600">{{ message }}</p>

    <template #footer>
      <button
        type="button"
        class="btn-clinica border border-stone-300 bg-white text-stone-700 hover:bg-stone-50"
        @click="$emit('close')"
      >{{ cancelText }}</button>
      <button
        type="button"
        class="btn-clinica text-white shadow-sm"
        :class="confirmClasses"
        @click="$emit('confirm')"
      >
        <AppIcon :name="type === 'danger' ? 'logout' : 'check'" class="h-4 w-4" />
        {{ confirmText }}
      </button>
    </template>
  </Modal>
</template>

<script setup>
import { computed } from 'vue';
import Modal from './Modal.vue';
import AppIcon from './AppIcon.vue';

/**
 * Diálogo de confirmación (compatible con la API anterior).
 * type: 'danger' | 'brand' | 'warning'
 */
const props = defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: 'Confirmar acción' },
  message: { type: String, default: '' },
  confirmText: { type: String, default: 'Confirmar' },
  cancelText: { type: String, default: 'Cancelar' },
  type: { type: String, default: 'brand' },
});

defineEmits(['confirm', 'close']);

const iconName = computed(() => (props.type === 'danger' ? 'alert' : props.type === 'warning' ? 'alert' : 'info'));

const confirmClasses = computed(() => {
  if (props.type === 'danger') return 'bg-rose-600 hover:bg-rose-700';
  if (props.type === 'warning') return 'bg-amber-600 hover:bg-amber-700';
  return 'bg-brand-600 hover:bg-brand-700';
});
</script>
