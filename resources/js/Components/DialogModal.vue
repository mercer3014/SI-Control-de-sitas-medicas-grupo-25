<template>
  <Transition name="fade">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div 
        class="bg-white rounded-lg shadow-xl border border-slate-200 w-full max-w-md overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-150"
        role="dialog"
        aria-modal="true"
      >
        <!-- Encabezado estilo ventana de diálogo -->
        <div class="px-5 py-3.5 bg-slate-100 border-b border-slate-200 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span v-if="type === 'danger'" class="w-3 h-3 rounded-full bg-rose-500"></span>
            <span v-else-if="type === 'warning'" class="w-3 h-3 rounded-full bg-amber-500"></span>
            <span v-else class="w-3 h-3 rounded-full bg-blue-600"></span>
            <h3 class="text-sm font-semibold text-slate-800 tracking-tight">{{ title }}</h3>
          </div>
          <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 text-lg leading-none cursor-pointer">
            &times;
          </button>
        </div>

        <!-- Contenido -->
        <div class="p-5 text-sm text-slate-600 leading-relaxed">
          <slot>
            <p>{{ message }}</p>
          </slot>
        </div>

        <!-- Botones de Acción (Estilo Diálogo de Sistema) -->
        <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex justify-end gap-2.5">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-100 focus:outline-none transition cursor-pointer"
          >
            {{ cancelText }}
          </button>
          <button
            type="button"
            @click="$emit('confirm')"
            :class="[
              'px-4 py-1.5 text-xs font-semibold rounded text-white shadow-xs focus:outline-none transition cursor-pointer',
              type === 'danger' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-blue-600 hover:bg-blue-700'
            ]"
          >
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: 'Confirmación' },
  message: { type: String, default: '¿Deseas confirmar esta acción?' },
  confirmText: { type: String, default: 'Aceptar' },
  cancelText: { type: String, default: 'Cancelar' },
  type: { type: String, default: 'info' } // info, warning, danger
});

defineEmits(['confirm', 'close']);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
