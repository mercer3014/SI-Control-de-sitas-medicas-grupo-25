<template>
  <AppLayout>
    <template #header>
      <span>Módulo 2: Gestión de Especialidades Médicas (CU-04)</span>
    </template>

    <div class="space-y-4">
      <!-- Barra Superior: Filtro y Registro -->
      <div class="bg-white p-3.5 rounded border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
          <!-- Buscador -->
          <div class="relative w-full sm:w-64">
            <input 
              v-model="search" 
              type="text" 
              placeholder="Buscar especialidad..." 
              class="w-full pl-8 pr-3 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none"
              @keyup.enter="handleSearch"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <!-- Filtro Estado -->
          <select 
            v-model="selectedEstado" 
            class="px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none"
            @change="handleSearch"
          >
            <option value="">Todos los estados</option>
            <option value="1">Activas</option>
            <option value="0">Inactivas</option>
          </select>
        </div>

        <button 
          @click="openCreateModal"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs transition cursor-pointer"
        >
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          <span>Nueva Especialidad</span>
        </button>
      </div>

      <!-- Tabla de Especialidades -->
      <div class="bg-white rounded border border-slate-200 shadow-xs overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-700 font-semibold uppercase text-[11px] tracking-wider">
              <th class="py-2.5 px-3">Especialidad</th>
              <th class="py-2.5 px-3">Descripción</th>
              <th class="py-2.5 px-3 text-center">Médicos Asignados</th>
              <th class="py-2.5 px-3 text-center">Estado</th>
              <th class="py-2.5 px-3 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr 
              v-for="esp in especialidades.data" 
              :key="esp.id"
              class="hover:bg-slate-50 transition"
            >
              <td class="py-2.5 px-3 font-semibold text-slate-800">
                {{ esp.nombre }}
              </td>
              <td class="py-2.5 px-3 text-slate-600 max-w-md">
                {{ esp.descripcion || 'Sin descripción' }}
              </td>
              <td class="py-2.5 px-3 text-center font-mono font-medium text-slate-700">
                {{ esp.medicos_count }} profesional(es)
              </td>
              <td class="py-2.5 px-3 text-center">
                <span 
                  :class="[
                    'inline-block px-2 py-0.5 text-[10px] rounded font-semibold',
                    esp.estado ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                  ]"
                >
                  {{ esp.estado ? 'Activa' : 'Inactiva' }}
                </span>
              </td>
              <td class="py-2.5 px-3 text-right space-x-1">
                <button 
                  @click="openEditModal(esp)" 
                  class="px-2 py-1 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded transition cursor-pointer"
                >
                  Editar
                </button>
                <button 
                  @click="confirmToggle(esp)" 
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded transition cursor-pointer',
                    esp.estado ? 'text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200'
                  ]"
                >
                  {{ esp.estado ? 'Desactivar' : 'Activar' }}
                </button>
              </td>
            </tr>

            <tr v-if="especialidades.data.length === 0">
              <td colspan="5" class="py-8 text-center text-xs text-slate-500">
                No se encontraron especialidades registradas.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="especialidades.links.length > 3" class="flex justify-end gap-1 text-xs">
        <Link 
          v-for="(link, i) in especialidades.links" 
          :key="i"
          :href="link.url || '#'"
          :class="[
            'px-2.5 py-1 rounded border',
            link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50',
            !link.url ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
          ]"
          v-html="link.label"
        />
      </div>
    </div>

    <!-- Modal Formulario Especialidad -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-lg shadow-xl border border-slate-200 w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="px-5 py-3.5 bg-slate-100 border-b border-slate-200 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-800">
            {{ isEditing ? 'Editar Especialidad' : 'Nueva Especialidad Médica' }}
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>

        <form @submit.prevent="saveEspecialidad" class="p-5 space-y-3.5">
          <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">Nombre de la Especialidad *</label>
            <input 
              v-model="form.nombre" 
              type="text" 
              required 
              placeholder="ej. Medicina General, Pediatría..."
              class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none"
              :class="{ 'border-rose-400 bg-rose-50/30': form.errors.nombre }"
            />
            <p v-if="form.errors.nombre" class="text-[10px] text-rose-600 mt-0.5">{{ form.errors.nombre }}</p>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">Descripción</label>
            <textarea 
              v-model="form.descripcion" 
              rows="3" 
              placeholder="Descripción breve del área de atención médica..."
              class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none"
            ></textarea>
          </div>

          <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 -mx-5 -mb-5 flex justify-end gap-2.5 mt-4">
            <button type="button" @click="showModal = false" class="px-4 py-1.5 text-xs text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-100 cursor-pointer">
              Cancelar
            </button>
            <button type="submit" :disabled="form.processing" class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded shadow-xs cursor-pointer">
              {{ isEditing ? 'Guardar Cambios' : 'Registrar' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Diálogo Modal Central de Confirmación para Bajas Lógicas -->
    <DialogModal
      :show="showConfirmModal"
      :title="espToToggle?.estado ? 'Desactivar Especialidad' : 'Activar Especialidad'"
      :message="espToToggle?.estado 
        ? `¿Confirmas la desactivación de la especialidad '${espToToggle?.nombre}'? Los médicos no podrán recibir nuevas citas bajo esta especialidad mientras esté inactiva.` 
        : `¿Confirmas la activación de la especialidad '${espToToggle?.nombre}'?`"
      confirm-text="Confirmar"
      cancel-text="Cancelar"
      :type="espToToggle?.estado ? 'danger' : 'info'"
      @confirm="executeToggle"
      @close="showConfirmModal = false"
    />
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';

const props = defineProps({
  especialidades: Object,
  filters: Object,
});

const search = ref(props.filters?.buscar || '');
const selectedEstado = ref(props.filters?.estado ?? '');

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const showConfirmModal = ref(false);
const espToToggle = ref(null);

const form = useForm({
  nombre: '',
  descripcion: '',
  estado: true,
});

const handleSearch = () => {
  router.get('/especialidades', {
    buscar: search.value,
    estado: selectedEstado.value,
  }, { preserveState: true });
};

const openCreateModal = () => {
  isEditing.value = false;
  editingId.value = null;
  form.reset();
  form.clearErrors();
  showModal.value = true;
};

const openEditModal = (esp) => {
  isEditing.value = true;
  editingId.value = esp.id;
  form.clearErrors();
  form.nombre = esp.nombre;
  form.descripcion = esp.descripcion || '';
  form.estado = esp.estado;
  showModal.value = true;
};

const saveEspecialidad = () => {
  if (isEditing.value) {
    form.put(`/especialidades/${editingId.value}`, {
      onSuccess: () => { showModal.value = false; },
    });
  } else {
    form.post('/especialidades', {
      onSuccess: () => { showModal.value = false; },
    });
  }
};

const confirmToggle = (esp) => {
  espToToggle.value = esp;
  showConfirmModal.value = true;
};

const executeToggle = () => {
  if (!espToToggle.value) return;
  router.delete(`/especialidades/${espToToggle.value.id}`, {
    onFinish: () => {
      showConfirmModal.value = false;
      espToToggle.value = null;
    }
  });
};
</script>
