<template>
  <AppLayout>
    <template #header>
      <span>Módulo 1: Gestión de Roles y Permisos (CU-09)</span>
    </template>

    <div class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div 
          v-for="r in roles" 
          :key="r.id"
          class="bg-white rounded border border-slate-200 shadow-xs p-4 flex flex-col justify-between"
        >
          <div>
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                <h3 class="text-sm font-bold text-slate-800">{{ r.nombre }}</h3>
              </div>
              <span class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-mono">
                {{ r.usuarios_count }} usuario(s)
              </span>
            </div>

            <p class="text-xs text-slate-600 mt-2.5 leading-relaxed">{{ r.descripcion }}</p>

            <!-- Permisos Asignados -->
            <div class="mt-3">
              <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">
                Permisos asignados ({{ r.permisos.length }}):
              </span>
              <div class="flex flex-wrap gap-1">
                <span 
                  v-for="p in r.permisos" 
                  :key="p.id"
                  class="px-1.5 py-0.5 text-[10px] bg-blue-50 text-blue-700 border border-blue-200 rounded font-medium"
                >
                  {{ p.nombre }}
                </span>
                <span v-if="r.permisos.length === 0" class="text-[11px] text-slate-400 italic">
                  Sin permisos asignados
                </span>
              </div>
            </div>
          </div>

          <div class="pt-4 mt-3 border-t border-slate-100 flex justify-end">
            <button 
              @click="openEditModal(r)"
              class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium transition cursor-pointer"
            >
              Configurar Permisos
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Editar Permisos del Rol -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-lg shadow-xl border border-slate-200 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="px-5 py-3.5 bg-slate-100 border-b border-slate-200 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-800">
            Configurar Rol: {{ selectedRol?.nombre }}
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>

        <form @submit.prevent="saveRole" class="p-5 space-y-3.5">
          <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">Descripción</label>
            <input v-model="form.descripcion" type="text" class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none" />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-2">Permisos del Sistema</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-56 overflow-y-auto p-2 border border-slate-200 rounded bg-slate-50">
              <label 
                v-for="p in permisos" 
                :key="p.id"
                class="flex items-start gap-2 p-1.5 rounded hover:bg-white text-xs cursor-pointer"
              >
                <input 
                  type="checkbox" 
                  :value="p.id" 
                  v-model="form.permisos"
                  class="mt-0.5 rounded text-blue-600 focus:ring-blue-500"
                />
                <div>
                  <span class="font-medium text-slate-800 block text-[11px]">{{ p.nombre }}</span>
                  <span class="text-[10px] text-slate-500 block leading-tight">{{ p.descripcion }}</span>
                </div>
              </label>
            </div>
          </div>

          <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 -mx-5 -mb-5 flex justify-end gap-2.5 mt-4">
            <button type="button" @click="showModal = false" class="px-4 py-1.5 text-xs text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-100 cursor-pointer">
              Cancelar
            </button>
            <button type="submit" :disabled="form.processing" class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded shadow-xs cursor-pointer">
              Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  roles: Array,
  permisos: Array,
});

const showModal = ref(false);
const selectedRol = ref(null);

const form = useForm({
  descripcion: '',
  permisos: [],
  estado: true,
});

const openEditModal = (r) => {
  selectedRol.value = r;
  form.descripcion = r.descripcion || '';
  form.permisos = r.permisos.map(p => p.id);
  form.estado = r.estado;
  showModal.value = true;
};

const saveRole = () => {
  if (!selectedRol.value) return;
  form.put(`/roles/${selectedRol.value.id}`, {
    onSuccess: () => { showModal.value = false; }
  });
};
</script>
