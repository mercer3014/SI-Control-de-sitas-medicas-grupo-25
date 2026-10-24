<template>
  <AppLayout>
    <template #header>
      <span>Bitácora del Sistema</span>
    </template>

    <div class="space-y-4">
      <!-- Selector de Pestañas: Eventos de BD vs Logs del Servidor -->
      <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <button 
          type="button"
          @click="activeTab = 'eventos'"
          :class="[
            'px-3.5 py-1.5 text-xs font-semibold rounded transition cursor-pointer',
            activeTab === 'eventos' 
              ? 'bg-slate-900 text-white shadow-xs' 
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
          ]"
        >
          Eventos de Auditoría ({{ bitacoras.total }})
        </button>
        <button 
          type="button"
          @click="activeTab = 'servidor'"
          :class="[
            'px-3.5 py-1.5 text-xs font-semibold rounded transition cursor-pointer',
            activeTab === 'servidor' 
              ? 'bg-slate-900 text-white shadow-xs' 
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
          ]"
        >
          Logs del Servidor / Sistema
        </button>
      </div>

      <!-- PESTAÑA 1: EVENTOS DE AUDITORÍA -->
      <div v-if="activeTab === 'eventos'" class="space-y-4">
        <!-- Barra de Búsqueda y Filtros -->
        <div class="bg-white p-3.5 rounded border border-slate-200 shadow-xs flex flex-wrap items-center gap-2.5">
          <!-- Búsqueda General -->
          <div class="relative w-full sm:w-64">
            <input 
              v-model="buscar" 
              type="text" 
              placeholder="Buscar en el log, acción, usuario..." 
              class="w-full pl-8 pr-3 py-1.5 text-xs rounded border border-slate-300 focus:border-slate-600 outline-none"
              @keyup.enter="handleSearch"
            />
            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <!-- Filtro Módulo -->
          <div class="w-full sm:w-36">
            <input 
              v-model="modulo" 
              type="text" 
              placeholder="Módulo"
              class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-slate-600 outline-none"
              @keyup.enter="handleSearch"
            />
          </div>

          <!-- Filtro Fecha -->
          <div class="w-full sm:w-36">
            <input 
              v-model="fecha" 
              type="date" 
              class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-slate-600 outline-none"
              @change="handleSearch"
            />
          </div>

          <!-- Filtro Usuario -->
          <div class="w-full sm:w-44">
            <select 
              v-model="usuarioId" 
              class="w-full px-2.5 py-1.5 text-xs rounded border border-slate-300 focus:border-slate-600 outline-none"
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
            class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded text-xs font-semibold shadow-xs transition cursor-pointer"
          >
            Filtrar
          </button>

          <button 
            v-if="hasFilters"
            @click="resetFilters"
            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium transition cursor-pointer"
          >
            Limpiar
          </button>
        </div>

        <!-- Tabla de Auditoría con Mensaje de Log Claro -->
        <div class="bg-white rounded border border-slate-200 shadow-xs overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold uppercase text-[11px] tracking-wider">
                <th class="py-2.5 px-3 whitespace-nowrap">Fecha y Hora</th>
                <th class="py-2.5 px-3">Usuario</th>
                <th class="py-2.5 px-3">Módulo</th>
                <th class="py-2.5 px-3">Acción</th>
                <th class="py-2.5 px-3 min-w-[280px]">Registro de Log / Detalle</th>
                <th class="py-2.5 px-3 text-right">IP</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr 
                v-for="b in bitacoras.data" 
                :key="b.id"
                class="hover:bg-slate-50/80 transition"
              >
                <!-- Fecha y Hora -->
                <td class="py-2 px-3 text-slate-600 whitespace-nowrap text-[11px] font-mono">
                  {{ formatDateTime(b.fecha_hora) }}
                </td>

                <!-- Usuario -->
                <td class="py-2 px-3">
                  <span class="font-medium text-slate-900 block text-xs">
                    {{ b.usuario?.persona?.nombre_completo || b.usuario?.username || 'Sistema' }}
                  </span>
                  <span class="text-[10px] text-slate-400 font-mono" v-if="b.usuario">
                    @{{ b.usuario?.username }}
                  </span>
                </td>

                <!-- Módulo -->
                <td class="py-2 px-3">
                  <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                    {{ b.modulo }}
                  </span>
                </td>

                <!-- Acción -->
                <td class="py-2 px-3">
                  <span class="font-mono text-[10px] font-semibold text-slate-800 px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200">
                    {{ b.accion }}
                  </span>
                </td>

                <!-- Detalle del Evento (LOG) -->
                <td class="py-2 px-3 text-slate-800 text-xs leading-snug">
                  {{ b.detalle || '-' }}
                </td>

                <!-- Dirección IP -->
                <td class="py-2 px-3 text-right text-slate-500 text-[11px] font-mono whitespace-nowrap">
                  {{ b.ip_origen || '127.0.0.1' }}
                </td>
              </tr>

              <tr v-if="bitacoras.data.length === 0">
                <td colspan="6" class="py-8 text-center text-xs text-slate-500">
                  No hay registros en la bitácora que coincidan con la búsqueda.
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
              link.active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50',
              !link.url ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
            ]"
            v-html="link.label"
          />
        </div>
      </div>

      <!-- PESTAÑA 2: LOGS DEL SERVIDOR (LARAVEL.LOG) -->
      <div v-else class="space-y-3">
        <div class="bg-white rounded border border-slate-200 shadow-xs p-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-3">
            <span class="text-xs font-semibold text-slate-700">
              Últimas líneas registradas en storage/logs/laravel.log
            </span>
            <span class="text-[11px] text-slate-400 font-mono">
              {{ serverLogs.length }} entradas recientes
            </span>
          </div>

          <div class="bg-slate-950 text-slate-200 rounded p-4 font-mono text-[11px] max-h-96 overflow-y-auto space-y-1.5 leading-relaxed">
            <div 
              v-for="(logLine, index) in serverLogs" 
              :key="index"
              class="break-words border-b border-slate-800/60 pb-1 hover:text-white"
            >
              {{ logLine }}
            </div>

            <div v-if="serverLogs.length === 0" class="text-slate-500 italic text-center py-6">
              El archivo de registro del servidor está limpio o no contiene entradas recientes.
            </div>
          </div>
        </div>
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
  serverLogs: { type: Array, default: () => [] },
  filters: Object,
});

const activeTab = ref('eventos');

const buscar = ref(props.filters?.buscar || '');
const modulo = ref(props.filters?.modulo || '');
const fecha = ref(props.filters?.fecha || '');
const usuarioId = ref(props.filters?.usuario_id || '');

const hasFilters = computed(() => {
  return buscar.value || modulo.value || fecha.value || usuarioId.value;
});

const handleSearch = () => {
  router.get('/bitacora', {
    buscar: buscar.value || undefined,
    modulo: modulo.value || undefined,
    fecha: fecha.value || undefined,
    usuario_id: usuarioId.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  buscar.value = '';
  modulo.value = '';
  fecha.value = '';
  usuarioId.value = '';
  router.get('/bitacora');
};

const formatDateTime = (dateString) => {
  if (!dateString) return '-';
  try {
    const d = new Date(dateString.replace(' ', 'T'));
    if (isNaN(d.getTime())) return dateString;
    return d.toLocaleString('es-BO', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
    });
  } catch (e) {
    return dateString;
  }
};
</script>
