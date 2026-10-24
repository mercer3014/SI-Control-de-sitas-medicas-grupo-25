<template>
  <AppLayout>
    <template #header>
      <span>Módulo 2: Gestión de Pacientes (CU-02)</span>
    </template>

    <div class="space-y-4">
      <!-- Barra Superior: Filtros y Registro -->
      <div class="bg-white p-3.5 rounded border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
          <!-- Buscador -->
          <div class="relative w-full sm:w-64">
            <input 
              v-model="search" 
              type="text" 
              placeholder="Buscar por CI, nombre, apellido..." 
              class="w-full pl-8 pr-3 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none"
              @keyup.enter="handleSearch"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <!-- Filtro Grupo Sanguíneo -->
          <select 
            v-model="selectedSangre" 
            class="px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none"
            @change="handleSearch"
          >
            <option value="">Tipo de sangre</option>
            <option value="O+">O+</option>
            <option value="O-">O-</option>
            <option value="A+">A+</option>
            <option value="A-">A-</option>
            <option value="B+">B+</option>
            <option value="B-">B-</option>
            <option value="AB+">AB+</option>
            <option value="AB-">AB-</option>
          </select>

          <!-- Filtro Estado -->
          <select 
            v-model="selectedEstado" 
            class="px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none"
            @change="handleSearch"
          >
            <option value="">Todos los estados</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
          </select>
        </div>

        <button 
          @click="openCreateModal"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs transition cursor-pointer"
        >
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          <span>Registrar Paciente</span>
        </button>
      </div>

      <!-- Tabla de Pacientes -->
      <div class="bg-white rounded border border-slate-200 shadow-xs overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-700 font-semibold uppercase text-[11px] tracking-wider">
              <th class="py-2.5 px-3">Paciente / Documento</th>
              <th class="py-2.5 px-3">Contacto</th>
              <th class="py-2.5 px-3">Grupo Sanguíneo</th>
              <th class="py-2.5 px-3">Contacto de Emergencia</th>
              <th class="py-2.5 px-3 text-center">Estado</th>
              <th class="py-2.5 px-3 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr 
              v-for="p in pacientes.data" 
              :key="p.id"
              class="hover:bg-slate-50 transition"
            >
              <td class="py-2.5 px-3">
                <span class="font-semibold text-slate-900 block">{{ p.persona?.nombre_completo }}</span>
                <span class="text-[11px] text-slate-500 font-mono">CI: {{ p.persona?.ci }} &bull; {{ p.persona?.genero === 'M' ? 'Masc' : (p.persona?.genero === 'F' ? 'Fem' : 'Otro') }}</span>
              </td>
              <td class="py-2.5 px-3 text-slate-600">
                <span class="block">{{ p.persona?.telefono || 'Sin teléfono' }}</span>
                <span class="text-[11px] text-slate-400 block truncate max-w-xs">{{ p.persona?.direccion || 'Sin dirección' }}</span>
              </td>
              <td class="py-2.5 px-3">
                <span 
                  v-if="p.tipo_sangre"
                  class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-50 text-rose-700 border border-rose-200"
                >
                  {{ p.tipo_sangre }}
                </span>
                <span v-else class="text-slate-400 italic text-[11px]">No registrado</span>
              </td>
              <td class="py-2.5 px-3 text-slate-600">
                <span class="block font-medium">{{ p.contacto_emergencia_nombre || 'Sin contacto' }}</span>
                <span class="text-[11px] text-slate-400 font-mono" v-if="p.contacto_emergencia_telefono">{{ p.contacto_emergencia_telefono }}</span>
              </td>
              <td class="py-2.5 px-3 text-center">
                <span 
                  :class="[
                    'inline-block px-2 py-0.5 text-[10px] rounded font-semibold',
                    p.estado ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                  ]"
                >
                  {{ p.estado ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td class="py-2.5 px-3 text-right space-x-1">
                <Link 
                  :href="`/pacientes/${p.id}`"
                  class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded transition"
                  title="Ver ficha clínica"
                >
                  Ficha
                </Link>
                <button 
                  @click="openEditModal(p)" 
                  class="px-2 py-1 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded transition cursor-pointer"
                >
                  Editar
                </button>
                <button 
                  @click="confirmToggle(p)" 
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded transition cursor-pointer',
                    p.estado ? 'text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200'
                  ]"
                >
                  {{ p.estado ? 'Desactivar' : 'Activar' }}
                </button>
              </td>
            </tr>

            <tr v-if="pacientes.data.length === 0">
              <td colspan="6" class="py-8 text-center text-xs text-slate-500">
                No se encontraron pacientes registrados con los filtros aplicados.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="pacientes.links.length > 3" class="flex justify-end gap-1 text-xs">
        <Link 
          v-for="(link, i) in pacientes.links" 
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

    <!-- Modal Formulario Paciente -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white rounded-lg shadow-xl border border-slate-200 w-full max-w-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="px-5 py-3.5 bg-slate-100 border-b border-slate-200 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-800">
            {{ isEditing ? 'Editar Datos del Paciente' : 'Registrar Nuevo Paciente' }}
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">&times;</button>
        </div>

        <form @submit.prevent="savePaciente" class="p-5 space-y-3.5 max-h-[85vh] overflow-y-auto">
          <!-- Datos Personales -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-medium text-slate-700 mb-1">CI / Documento de Identidad *</label>
              <input v-model="form.ci" type="text" required placeholder="Número de CI" class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none" />
              <p v-if="form.errors.ci" class="text-[10px] text-rose-600 mt-0.5">{{ form.errors.ci }}</p>
            </div>
            <div>
              <label class="block text-[11px] font-medium text-slate-700 mb-1">Tipo de Sangre</label>
              <select v-model="form.tipo_sangre" class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none">
                <option value="">Selecciona tipo</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-medium text-slate-700 mb-1">Nombres *</label>
              <input v-model="form.nombres" type="text" required class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none" />
            </div>
            <div>
              <label class="block text-[11px] font-medium text-slate-700 mb-1">Apellidos *</label>
              <input v-model="form.apellidos" type="text" required class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-[11px] font-medium text-slate-700 mb-1">Fecha Nacimiento *</label>
              <input v-model="form.fecha_nacimiento" type="date" required class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none" />
            </div>
            <div>
              <label class="block text-[11px] font-medium text-slate-700 mb-1">Género *</label>
              <select v-model="form.genero" required class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none">
                <option value="M">Masculino (M)</option>
                <option value="F">Femenino (F)</option>
                <option value="O">Otro (O)</option>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-medium text-slate-700 mb-1">Teléfono</label>
              <input v-model="form.telefono" type="text" placeholder="Celular" class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none" />
            </div>
          </div>

          <div>
            <label class="block text-[11px] font-medium text-slate-700 mb-1">Dirección de Domicilio</label>
            <input v-model="form.direccion" type="text" class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none" />
          </div>

          <!-- Contacto de Emergencia -->
          <hr class="border-slate-200 my-2" />
          <h4 class="text-xs font-semibold text-slate-800">Contacto de Emergencia</h4>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-medium text-slate-700 mb-1">Nombre Completo del Contacto</label>
              <input v-model="form.contacto_emergencia_nombre" type="text" placeholder="Familiar o apoderado" class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none" />
            </div>
            <div>
              <label class="block text-[11px] font-medium text-slate-700 mb-1">Teléfono de Emergencia</label>
              <input v-model="form.contacto_emergencia_telefono" type="text" placeholder="Teléfono de contacto" class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none" />
            </div>
          </div>

          <!-- Antecedentes Médicos (Solo en alta) -->
          <div v-if="!isEditing">
            <label class="block text-[11px] font-medium text-slate-700 mb-1">Antecedentes Clínicos / Alergias (Opcional)</label>
            <textarea v-model="form.antecedentes" rows="2" placeholder="Enfermedades crónicas, alergias a medicamentos, etc." class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none"></textarea>
          </div>

          <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 -mx-5 -mb-5 flex justify-end gap-2.5 mt-4">
            <button type="button" @click="showModal = false" class="px-4 py-1.5 text-xs text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-100 cursor-pointer">
              Cancelar
            </button>
            <button type="submit" :disabled="form.processing" class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded shadow-xs cursor-pointer">
              {{ isEditing ? 'Guardar Cambios' : 'Registrar Paciente' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Diálogo Modal Central de Confirmación para Bajas Lógicas -->
    <DialogModal
      :show="showConfirmModal"
      :title="pacienteToToggle?.estado ? 'Desactivar Paciente' : 'Activar Paciente'"
      :message="pacienteToToggle?.estado 
        ? `¿Confirmas la desactivación del paciente ${pacienteToToggle?.persona?.nombre_completo}? No podrá agendar nuevas citas mientras esté inactivo.` 
        : `¿Confirmas la reactivación del paciente ${pacienteToToggle?.persona?.nombre_completo}?`"
      confirm-text="Confirmar"
      cancel-text="Cancelar"
      :type="pacienteToToggle?.estado ? 'danger' : 'info'"
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
  pacientes: Object,
  filters: Object,
});

const search = ref(props.filters?.buscar || '');
const selectedSangre = ref(props.filters?.tipo_sangre || '');
const selectedEstado = ref(props.filters?.estado ?? '');

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const showConfirmModal = ref(false);
const pacienteToToggle = ref(null);

const form = useForm({
  ci: '',
  nombres: '',
  apellidos: '',
  fecha_nacimiento: '',
  genero: 'M',
  telefono: '',
  direccion: '',
  tipo_sangre: '',
  contacto_emergencia_nombre: '',
  contacto_emergencia_telefono: '',
  antecedentes: '',
  estado: true,
});

const handleSearch = () => {
  router.get('/pacientes', {
    buscar: search.value,
    tipo_sangre: selectedSangre.value,
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

const openEditModal = (p) => {
  isEditing.value = true;
  editingId.value = p.id;
  form.clearErrors();
  form.ci = p.persona?.ci || '';
  form.nombres = p.persona?.nombres || '';
  form.apellidos = p.persona?.apellidos || '';
  form.fecha_nacimiento = p.persona?.fecha_nacimiento ? p.persona.fecha_nacimiento.substring(0, 10) : '';
  form.genero = p.persona?.genero || 'M';
  form.telefono = p.persona?.telefono || '';
  form.direccion = p.persona?.direccion || '';
  form.tipo_sangre = p.tipo_sangre || '';
  form.contacto_emergencia_nombre = p.contacto_emergencia_nombre || '';
  form.contacto_emergencia_telefono = p.contacto_emergencia_telefono || '';
  form.estado = p.estado;
  showModal.value = true;
};

const savePaciente = () => {
  if (isEditing.value) {
    form.put(`/pacientes/${editingId.value}`, {
      onSuccess: () => { showModal.value = false; },
    });
  } else {
    form.post('/pacientes', {
      onSuccess: () => { showModal.value = false; },
    });
  }
};

const confirmToggle = (p) => {
  pacienteToToggle.value = p;
  showConfirmModal.value = true;
};

const executeToggle = () => {
  if (!pacienteToToggle.value) return;
  router.delete(`/pacientes/${pacienteToToggle.value.id}`, {
    onFinish: () => {
      showConfirmModal.value = false;
      pacienteToToggle.value = null;
    }
  });
};
</script>
