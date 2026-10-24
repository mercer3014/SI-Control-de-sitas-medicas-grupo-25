<template>
  <AppLayout>
    <template #header>
      <span>Roles y Permisos</span>
    </template>

    <div class="space-y-6">
      <!-- Sección por cada Rol -->
      <div 
        v-for="r in roleForms" 
        :key="r.id"
        class="bg-white rounded border border-slate-200 shadow-xs overflow-hidden"
      >
        <!-- Encabezado de la Sección del Rol -->
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-slate-700"></span>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-slate-900 tracking-tight">{{ r.nombre }}</h3>
                <span class="text-[11px] px-2 py-0.5 rounded bg-slate-200/70 text-slate-700 font-mono">
                  {{ r.usuarios_count }} usuario(s)
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-0.5">{{ r.descripcion }}</p>
            </div>
          </div>

          <!-- Botón Guardar Cambios del Rol -->
          <button 
            type="button"
            @click="saveRole(r)"
            :disabled="r.saving"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded transition shadow-xs disabled:opacity-50 cursor-pointer shrink-0 self-end sm:self-auto"
          >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ r.saving ? 'Guardando...' : 'Guardar Permisos' }}</span>
          </button>
        </div>

        <!-- Matriz de Checks de Permisos -->
        <div class="p-5">
          <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
            <span class="text-xs font-semibold text-slate-700 uppercase tracking-wider text-[11px]">
              Permisos del Rol ({{ r.selectedPermisos.length }} de {{ permisos.length }} asignados)
            </span>
            <div class="flex gap-2">
              <button 
                type="button" 
                @click="selectAll(r)" 
                class="text-[11px] text-slate-600 hover:text-slate-900 underline cursor-pointer"
              >
                Seleccionar todos
              </button>
              <span class="text-slate-300">|</span>
              <button 
                type="button" 
                @click="deselectAll(r)" 
                class="text-[11px] text-slate-600 hover:text-slate-900 underline cursor-pointer"
              >
                Desmarcar todos
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
            <label 
              v-for="p in permisos" 
              :key="p.id"
              class="flex items-start gap-2.5 p-2 rounded border transition cursor-pointer select-none"
              :class="r.selectedPermisos.includes(p.id) 
                ? 'bg-slate-50 border-slate-300' 
                : 'bg-white border-slate-200 hover:bg-slate-50/50'"
            >
              <input 
                type="checkbox" 
                :value="p.id" 
                v-model="r.selectedPermisos"
                class="mt-0.5 rounded text-slate-800 focus:ring-slate-600 cursor-pointer"
              />
              <div class="leading-tight">
                <span class="text-xs font-medium text-slate-900 block">{{ p.nombre }}</span>
                <span class="text-[11px] text-slate-500 block mt-0.5 leading-snug">{{ p.descripcion }}</span>
              </div>
            </label>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  roles: Array,
  permisos: Array,
});

// Crear formularios reactivos individuales para cada rol
const roleForms = reactive(
  props.roles.map(r => ({
    id: r.id,
    nombre: r.nombre,
    descripcion: r.descripcion,
    usuarios_count: r.usuarios_count,
    estado: r.estado,
    selectedPermisos: r.permisos.map(p => p.id),
    saving: false,
  }))
);

const selectAll = (r) => {
  r.selectedPermisos = props.permisos.map(p => p.id);
};

const deselectAll = (r) => {
  r.selectedPermisos = [];
};

const saveRole = (r) => {
  r.saving = true;
  router.put(`/roles/${r.id}`, {
    descripcion: r.descripcion,
    estado: r.estado,
    permisos: r.selectedPermisos,
  }, {
    preserveScroll: true,
    onFinish: () => {
      r.saving = false;
    }
  });
};
</script>
