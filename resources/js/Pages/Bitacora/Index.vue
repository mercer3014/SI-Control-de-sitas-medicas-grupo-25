<template>
  <AppLayout>
    <template #header>
      <span>Módulo 1: Bitácora de Auditoría del Sistema (CU-10)</span>
    </template>

    <div class="space-y-4">
      <!-- Filtros de Búsqueda -->
      <div class="bg-white p-3.5 rounded border border-slate-200 shadow-xs flex flex-wrap items-center gap-2.5">
        <!-- Filtro Módulo -->
        <div class="w-full sm:w-44">
          <input 
            v-model="modulo" 
            type="text" 
            placeholder="Módulo (ej. Seguridad)"
            class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none"
            @keyup.enter="handleSearch"
          />
        </div>

        <!-- Filtro Acción -->
        <div class="w-full sm:w-44">
          <input 
            v-model="accion" 
            type="text" 
            placeholder="Acción (ej. LOGIN, CREACION)"
            class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none"
            @keyup.enter="handleSearch"
          />
        </div>

        <!-- Filtro Fecha -->
        <div class="w-full sm:w-36">
          <input 
            v-model="fecha" 
            type="date" 
            class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none"
            @change="handleSearch"
          />
        </div>

        <!-- Filtro Usuario -->
        <div class="w-full sm:w-48">
          <select 
            v-model="usuarioId" 
            class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 outline-none"
            @change="handleSearch"
          >
            <option value="">Todos los usuarios</option>
            <option v-for="u in usuarios" :key="u.id" :value="u.id">
              {{ u.username }} ({{ u.persona?.nombres }})
            </option>
          </select>
        </div>

        <button 
          @click="handleSearch"
          class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs transition cursor-pointer"
        >
          Filtrar
        </button>

        <button 
          v-if="hasFilters"
          @click="resetFilters"
          class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded text-xs font-medium transition cursor-pointer"
        >
          Limpiar
        </button>
      </div>

      <!-- Tabla de Auditoría -->
      <div class="bg-white rounded border border-slate-200 shadow-xs overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-700 font-semibold uppercase text-[11px] tracking-wider">
              <th class="py-2.5 px-3">Fecha y Hora</th>
              <th class="py-2.5 px-3">Usuario Responsable</th>
              <th class="py-2.5 px-3">Módulo</th>
              <th class="py-2.5 px-3">Acción Registrada</th>
              <th class="py-2.5 px-3">Detalle del Evento</th>
              <th class="py-2.5 px-3 text-right">Dirección IP</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-mono">
            <tr 
              v-for="b in bitacoras.data" 
              :key="b.id"
              class="hover:bg-slate-50 transition"
            >
              <td class="py-2 px-3 text-slate-600 whitespace-nowrap text-[11px]">
                {{ new Date(b.fecha_hora).toLocaleString('es-BO') }}
              </td>
              <td class="py-2 px-3 font-sans">
                <span class="font-medium text-slate-900 block text-xs">
                  {{ b.usuario?.persona?.nombre_completo || b.usuario?.username || 'Sistema' }}
                </span>
                <span class="text-[10px] text-slate-400 font-mono" v-if="b.usuario">
                  @{{ b.usuario?.username }}
                </span>
              </td>
              <td class="py-2 px-3 font-sans">
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                  {{ b.modulo }}
                </span>
              </td>
              <td class="py-2 px-3">
                <span class="font-bold text-blue-700 text-[11px]">
                  {{ b.accion }}
                </span>
              </td>
              <td class="py-2 px-3 font-sans text-slate-700 text-xs">
                {{ b.detalle || '-' }}
              </td>
              <td class="py-2 px-3 text-right text-slate-500 text-[11px]">
                {{ b.ip_origen || '127.0.0.1' }}
              </td>
            </tr>

            <tr v-if="bitacoras.data.length === 0">
              <td colspan="6" class="py-8 text-center text-xs text-slate-500 font-sans">
                No hay eventos registrados en la bitácora que coincidan con los filtros.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="bitacoras.links.length > 3" class="flex justify-end gap-1 text-xs">
        <Link 
          v-for="(link, i) in bitacoras.links" 
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
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  bitacoras: Object,
  usuarios: Array,
  filters: Object,
});

const modulo = ref(props.filters?.modulo || '');
const accion = ref(props.filters?.accion || '');
const fecha = ref(props.filters?.fecha || '');
const usuarioId = ref(props.filters?.usuario_id || '');

const hasFilters = computed(() => {
  return modulo.value || accion.value || fecha.value || usuarioId.value;
});

const handleSearch = () => {
  router.get('/bitacora', {
    modulo: modulo.value,
    accion: accion.value,
    fecha: fecha.value,
    usuario_id: usuarioId.value,
  }, { preserveState: true });
};

const resetFilters = () => {
  modulo.value = '';
  accion.value = '';
  fecha.value = '';
  usuarioId.value = '';
  handleSearch();
};
</script>
