<template>
  <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2.5 w-full max-w-sm pointer-events-none">
    <TransitionGroup name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-start gap-3 rounded-xl border bg-white p-3.5 shadow-lg animate-slide-in-right"
        :class="styles[toast.type].border"
      >
        <div
          class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg"
          :class="styles[toast.type].iconBg"
        >
          <AppIcon :name="styles[toast.type].icon" class="h-4 w-4" :class="styles[toast.type].iconColor" />
        </div>
        <div class="min-w-0 flex-1 pt-0.5">
          <p class="text-[13px] font-semibold text-stone-800 leading-snug">{{ styles[toast.type].title }}</p>
          <p class="text-[13px] text-stone-600 leading-snug mt-0.5">{{ toast.message }}</p>
        </div>
        <button
          type="button"
          class="shrink-0 rounded-md p-1 text-stone-400 hover:text-stone-600 hover:bg-stone-100 transition cursor-pointer"
          @click="dismiss(toast.id)"
        >
          <AppIcon name="x" class="h-3.5 w-3.5" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppIcon from './AppIcon.vue';

const page = usePage();
const toasts = ref([]);
let nextId = 1;

const styles = {
  success: {
    border: 'border-emerald-200',
    iconBg: 'bg-emerald-100',
    iconColor: 'text-emerald-600',
    icon: 'check-circle',
    title: 'Operación exitosa',
  },
  error: {
    border: 'border-rose-200',
    iconBg: 'bg-rose-100',
    iconColor: 'text-rose-600',
    icon: 'x-circle',
    title: 'Ocurrió un problema',
  },
  info: {
    border: 'border-sky-200',
    iconBg: 'bg-sky-100',
    iconColor: 'text-sky-600',
    icon: 'info',
    title: 'Información',
  },
};

const dismiss = (id) => {
  toasts.value = toasts.value.filter((t) => t.id !== id);
};

const push = (message, type = 'success') => {
  if (!message) return;
  const id = nextId++;
  toasts.value.push({ id, message, type });
  setTimeout(() => dismiss(id), 5200);
};

const syncFlash = () => {
  const flash = page.props.flash || {};
  if (flash.success) {
    push(flash.success, 'success');
    flash.success = null;
  }
  if (flash.error) {
    push(flash.error, 'error');
    flash.error = null;
  }
};

onMounted(syncFlash);
watch(() => page.props.flash, syncFlash, { deep: true });
</script>
