<template>
  <AppLayout>
    <template #header>
      <span>Respaldos de Base de Datos</span>
    </template>

    <div class="space-y-4">
      <!-- Tarjeta Principal de Respaldo -->
      <div class="bg-white p-5 rounded border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
            <h3 class="text-sm font-bold text-slate-800">Motor Activo: PostgreSQL 18</h3>
          </div>
          <p class="text-xs text-slate-500 mt-1">
            Base de datos: <span class="font-mono font-semibold text-slate-700">{{ dbName }}</span> &bull; Tamaño actual: <span class="font-mono font-semibold text-blue-600">{{ dbSize }}</span>
          </p>
        </div>

        <a 
          :href="'/backups/download'"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs transition cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          <span>Descargar Copia de Respaldo (.SQL)</span>
        </a>
      </div>

      <!-- Estado de las Tablas en la Base de Datos -->
      <div class="bg-white rounded border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
          <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Tablas Registradas en la Base de Datos</h4>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-100/60 border-b border-slate-200 text-slate-600 font-semibold uppercase text-[11px]">
                <th class="py-2 px-4">Nombre de Tabla</th>
                <th class="py-2 px-4 text-center">Registros Estimados</th>
                <th class="py-2 px-4 text-right">Espacio Ocupado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-mono">
              <tr v-for="t in tables" :key="t.table_name" class="hover:bg-slate-50">
                <td class="py-2 px-4 font-semibold text-slate-800 font-sans">
                  {{ t.table_name }}
                </td>
                <td class="py-2 px-4 text-center text-slate-700">
                  {{ t.row_count }}
                </td>
                <td class="py-2 px-4 text-right text-slate-500">
                  {{ t.total_size }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  tables: Array,
  dbSize: String,
  dbName: String,
});
</script>
