<template>
  <AppLayout>
    <template #header>
      <span>Panel General de Control</span>
    </template>

    <div class="space-y-5">
      <!-- Tarjetas de Métricas Principales (KPIs Compactos) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- KPI 1: Pacientes -->
        <div class="p-4 bg-white rounded border border-slate-200 shadow-xs flex items-center justify-between">
          <div>
            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Pacientes Activos</span>
            <span class="block text-2xl font-bold text-slate-800 mt-1">{{ stats.total_pacientes }}</span>
            <span class="block text-[11px] text-slate-400 mt-0.5">Catálogo general</span>
          </div>
          <div class="w-10 h-10 rounded bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
        </div>

        <!-- KPI 2: Médicos -->
        <div class="p-4 bg-white rounded border border-slate-200 shadow-xs flex items-center justify-between">
          <div>
            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Médicos Habilitados</span>
            <span class="block text-2xl font-bold text-slate-800 mt-1">{{ stats.total_medicos }}</span>
            <span class="block text-[11px] text-slate-400 mt-0.5">Cuerpo profesional</span>
          </div>
          <div class="w-10 h-10 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
        </div>

        <!-- KPI 3: Especialidades -->
        <div class="p-4 bg-white rounded border border-slate-200 shadow-xs flex items-center justify-between">
          <div>
            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Especialidades</span>
            <span class="block text-2xl font-bold text-slate-800 mt-1">{{ stats.total_especialidades }}</span>
            <span class="block text-[11px] text-slate-400 mt-0.5">Áreas de atención</span>
          </div>
          <div class="w-10 h-10 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
        </div>

        <!-- KPI 4: Usuarios -->
        <div class="p-4 bg-white rounded border border-slate-200 shadow-xs flex items-center justify-between">
          <div>
            <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuarios Activos</span>
            <span class="block text-2xl font-bold text-slate-800 mt-1">{{ stats.total_usuarios }}</span>
            <span class="block text-[11px] text-slate-400 mt-0.5">Cuentas autorizadas</span>
          </div>
          <div class="w-10 h-10 rounded bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Cuadrícula Inferior: Bitácora y Pacientes Recientes -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Registro Reciente de Auditoría (Bitácora) -->
        <div class="lg:col-span-2 bg-white rounded border border-slate-200 shadow-xs overflow-hidden">
          <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Últimos Eventos en Bitácora</h3>
            <Link :href="'/bitacora'" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Ver todo &rarr;</Link>
          </div>

          <div class="divide-y divide-slate-100">
            <div 
              v-for="b in ultimasBitacoras" 
              :key="b.id"
              class="px-4 py-2.5 flex items-start justify-between text-xs hover:bg-slate-50 transition"
            >
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 bg-slate-100 text-slate-700 rounded border border-slate-200">
                    {{ b.accion }}
                  </span>
                  <span class="text-slate-500 font-medium text-[11px]">[{{ b.modulo }}]</span>
                </div>
                <p class="text-slate-700 mt-1 text-[11px] leading-snug">{{ b.detalle || 'Sin detalle adicional' }}</p>
                <span class="text-[10px] text-slate-400 mt-0.5 block">
                  Por: {{ b.usuario?.persona?.nombre_completo || b.usuario?.username || 'Sistema' }} &bull; IP: {{ b.ip_origen }}
                </span>
              </div>
              <span class="text-[10px] text-slate-400 font-mono shrink-0 ml-4">
                {{ new Date(b.fecha_hora).toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' }) }}
              </span>
            </div>

            <div v-if="ultimasBitacoras.length === 0" class="p-6 text-center text-xs text-slate-400">
              No se han registrado eventos recientes en la bitácora.
            </div>
          </div>
        </div>

        <!-- Pacientes Registrados Recientemente -->
        <div class="bg-white rounded border border-slate-200 shadow-xs overflow-hidden">
          <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pacientes Recientes</h3>
            <Link :href="'/pacientes'" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Gestionar &rarr;</Link>
          </div>

          <div class="divide-y divide-slate-100">
            <div 
              v-for="p in ultimosPacientes" 
              :key="p.id" 
              class="px-4 py-2.5 flex items-center justify-between text-xs hover:bg-slate-50 transition"
            >
              <div>
                <span class="font-semibold text-slate-800 block">{{ p.persona?.nombre_completo }}</span>
                <span class="text-[11px] text-slate-400 font-mono">CI: {{ p.persona?.ci }} &bull; Sangre: {{ p.tipo_sangre || 'N/A' }}</span>
              </div>
              <span class="px-2 py-0.5 text-[10px] rounded font-medium" :class="p.estado ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'">
                {{ p.estado ? 'Activo' : 'Inactivo' }}
              </span>
            </div>

            <div v-if="ultimosPacientes.length === 0" class="p-6 text-center text-xs text-slate-400">
              Aún no hay pacientes registrados.
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  stats: { type: Object, required: true },
  ultimasBitacoras: { type: Array, default: () => [] },
  ultimosPacientes: { type: Array, default: () => [] },
});
</script>
