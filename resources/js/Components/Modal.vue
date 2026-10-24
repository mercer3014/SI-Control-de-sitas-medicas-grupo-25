<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-[90] flex items-center justify-center p-4 sm:p-6"
        @keydown.esc="close"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-stone-950/45 backdrop-blur-[3px]" @click="closable && close()"></div>

        <!-- Panel -->
        <div
          class="modal-panel relative w-full overflow-hidden rounded-2xl bg-white shadow-modal flex flex-col max-h-[92vh]"
          :class="sizeClasses"
          role="dialog"
          aria-modal="true"
        >
          <!-- Header -->
          <div class="flex items-start justify-between gap-4 border-b border-stone-100 px-6 py-4">
            <div class="flex items-center gap-3 min-w-0">
              <div
                v-if="icon"
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                :class="iconBg"
              >
                <AppIcon :name="icon" class="h-5 w-5" :class="iconColor" />
              </div>
              <div class="min-w-0">
                <h3 class="text-[15px] font-bold text-stone-900 leading-tight truncate">{{ title }}</h3>
                <p v-if="subtitle" class="mt-0.5 text-[12.5px] text-stone-500 truncate">{{ subtitle }}</p>
              </div>
            </div>
            <button
              v-if="closable"
              type="button"
              class="rounded-lg p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-600 transition cursor-pointer"
              @click="close"
            >
              <AppIcon name="x" class="h-4.5 w-4.5" />
            </button>
          </div>

          <!-- Body -->
          <div class="overflow-y-auto px-6 py-5">
            <slot />
          </div>

          <!-- Footer -->
          <div v-if="$slots.footer" class="flex items-center justify-end gap-2.5 border-t border-stone-100 bg-stone-50/60 px-6 py-3.5">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, watch } from 'vue';
import AppIcon from './AppIcon.vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  icon: { type: String, default: '' },
  tone: { type: String, default: 'brand' },
  size: { type: String, default: 'md' }, // sm | md | lg | xl
  closable: { type: Boolean, default: true },
});

const emit = defineEmits(['close']);

const close = () => emit('close');

const sizes = {
  sm: 'max-w-md',
  md: 'max-w-xl',
  lg: 'max-w-3xl',
  xl: 'max-w-5xl',
};
const sizeClasses = computed(() => sizes[props.size] || sizes.md);

const toneMap = {
  brand:   { iconBg: 'bg-brand-100',   iconColor: 'text-brand-700' },
  danger:  { iconBg: 'bg-rose-100',    iconColor: 'text-rose-600' },
  warning: { iconBg: 'bg-amber-100',   iconColor: 'text-amber-700' },
  info:    { iconBg: 'bg-sky-100',     iconColor: 'text-sky-700' },
};
const iconBg = computed(() => toneMap[props.tone]?.iconBg || toneMap.brand.iconBg);
const iconColor = computed(() => toneMap[props.tone]?.iconColor || toneMap.brand.iconColor);

// Bloquear scroll del fondo mientras el modal está abierto
watch(() => props.show, (val) => {
  document.body.style.overflow = val ? 'hidden' : '';
});
</script>
