<template>
  <aside 
    :class="[
      'fixed inset-y-0 left-0 z-40 bg-[#0F2942] text-slate-200 transition-all duration-200 ease-in-out flex flex-col border-r border-slate-800 shadow-md',
      collapsed ? 'w-16' : 'w-60'
    ]"
  >
    <!-- Cabecera del Sidebar con Logotipo y Toggle -->
    <div class="h-14 flex items-center justify-between px-3.5 border-b border-slate-700/60 bg-[#0A1D30]">
      <div v-if="!collapsed" class="flex items-center gap-2.5 overflow-hidden">
        <div class="w-7 h-7 rounded bg-blue-600 flex items-center justify-center font-bold text-white text-sm shadow-xs shrink-0">
          VS
        </div>
        <div class="leading-tight">
          <span class="block text-xs font-bold text-white tracking-wide">VIDA SALUD</span>
          <span class="block text-[10px] text-blue-300 font-medium">Control de Citas</span>
        </div>
      </div>
      <div v-else class="mx-auto">
        <div class="w-8 h-8 rounded bg-blue-600 flex items-center justify-center font-bold text-white text-xs shadow-xs">
          VS
        </div>
      </div>

      <!-- Botón colapsar / expandir -->
      <button 
        type="button"
        @click="$emit('toggle')"
        class="p-1 rounded text-slate-400 hover:text-white hover:bg-slate-700/50 transition cursor-pointer"
        :title="collapsed ? 'Expandir menú' : 'Colapsar menú'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': collapsed }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m15 18-6-6 6-6"/>
        </svg>
      </button>
    </div>

    <!-- Navegación por Módulos -->
    <div class="flex-1 overflow-y-auto py-3 px-2 space-y-4">
      <!-- Sección: Panel Principal -->
      <div>
        <Link 
          :href="route('dashboard')" 
          :class="[
            'flex items-center gap-3 px-2.5 py-2 rounded text-xs font-medium transition cursor-pointer',
            $page.component === 'Dashboard' 
              ? 'bg-blue-600 text-white shadow-xs' 
              : 'text-slate-300 hover:bg-slate-800 hover:text-white'
          ]"
          :title="collapsed ? 'Panel General' : ''"
        >
          <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span v-if="!collapsed" class="truncate">Panel General</span>
        </Link>
      </div>

      <!-- Sección 1: Gestión de Seguridad y Usuarios (Admin) -->
      <div v-if="isAdmin">
        <p v-if="!collapsed" class="px-2.5 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
          Seguridad y Acceso
        </p>
        <div class="space-y-0.5">
          <Link 
            :href="route('usuarios.index')" 
            :class="[
              'flex items-center gap-3 px-2.5 py-1.5 rounded text-xs font-medium transition cursor-pointer',
              $page.component.startsWith('Usuarios') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'
            ]"
            :title="collapsed ? 'Usuarios' : ''"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span v-if="!collapsed" class="truncate">Usuarios (CU-01)</span>
          </Link>

          <Link 
            :href="route('roles.index')" 
            :class="[
              'flex items-center gap-3 px-2.5 py-1.5 rounded text-xs font-medium transition cursor-pointer',
              $page.component.startsWith('Roles') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'
            ]"
            :title="collapsed ? 'Roles y Permisos' : ''"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span v-if="!collapsed" class="truncate">Roles y Permisos (CU-09)</span>
          </Link>

          <Link 
            :href="route('bitacora.index')" 
            :class="[
              'flex items-center gap-3 px-2.5 py-1.5 rounded text-xs font-medium transition cursor-pointer',
              $page.component.startsWith('Bitacora') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'
            ]"
            :title="collapsed ? 'Bitácora de Auditoría' : ''"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span v-if="!collapsed" class="truncate">Bitácora Auditoría (CU-10)</span>
          </Link>

          <Link 
            :href="route('backups.index')" 
            :class="[
              'flex items-center gap-3 px-2.5 py-1.5 rounded text-xs font-medium transition cursor-pointer',
              $page.component.startsWith('Backups') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'
            ]"
            :title="collapsed ? 'Respaldos Base de Datos' : ''"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5 1 3.5 3M4 7h16m-8 4v6m0 0l-2-2m2 2l2-2" />
            </svg>
            <span v-if="!collapsed" class="truncate">Respaldos BD (CU-11)</span>
          </Link>
        </div>
      </div>

      <!-- Sección 2: Catálogos Clínicos y Personal -->
      <div>
        <p v-if="!collapsed" class="px-2.5 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
          Catálogos Médicos
        </p>
        <div class="space-y-0.5">
          <Link 
            v-if="isAdmin"
            :href="route('especialidades.index')" 
            :class="[
              'flex items-center gap-3 px-2.5 py-1.5 rounded text-xs font-medium transition cursor-pointer',
              $page.component.startsWith('Especialidades') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'
            ]"
            :title="collapsed ? 'Especialidades' : ''"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span v-if="!collapsed" class="truncate">Especialidades (CU-04)</span>
          </Link>

          <Link 
            v-if="isAdmin || isRecepcionista"
            :href="route('medicos.index')" 
            :class="[
              'flex items-center gap-3 px-2.5 py-1.5 rounded text-xs font-medium transition cursor-pointer',
              $page.component.startsWith('Medicos') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'
            ]"
            :title="collapsed ? 'Médicos y Especialidad' : ''"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span v-if="!collapsed" class="truncate">Médicos (CU-03, CU-12)</span>
          </Link>

          <Link 
            v-if="isAdmin || isRecepcionista"
            :href="route('pacientes.index')" 
            :class="[
              'flex items-center gap-3 px-2.5 py-1.5 rounded text-xs font-medium transition cursor-pointer',
              $page.component.startsWith('Pacientes') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'
            ]"
            :title="collapsed ? 'Pacientes' : ''"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span v-if="!collapsed" class="truncate">Pacientes (CU-02)</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- Pie del Sidebar: Información de Usuario -->
    <div class="p-3 border-t border-slate-700/60 bg-[#0A1D30]">
      <div v-if="!collapsed" class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-full bg-slate-700 text-blue-300 flex items-center justify-center font-bold text-xs uppercase shrink-0">
          {{ $page.props.auth.user?.username?.charAt(0) || 'U' }}
        </div>
        <div class="overflow-hidden leading-tight">
          <p class="text-xs font-semibold text-white truncate">{{ $page.props.auth.user?.persona?.nombre_completo || $page.props.auth.user?.username }}</p>
          <p class="text-[10px] text-blue-400 font-medium truncate">{{ $page.props.auth.user?.rol?.nombre }}</p>
        </div>
      </div>
      <div v-else class="flex justify-center">
        <div class="w-7 h-7 rounded-full bg-slate-700 text-blue-300 flex items-center justify-center font-bold text-xs uppercase">
          {{ $page.props.auth.user?.username?.charAt(0) || 'U' }}
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
  collapsed: { type: Boolean, default: false }
});

defineEmits(['toggle']);

const page = usePage();

const isAdmin = computed(() => {
  return page.props.auth?.user?.rol?.nombre?.toLowerCase() === 'administrador';
});

const isRecepcionista = computed(() => {
  return page.props.auth?.user?.rol?.nombre?.toLowerCase() === 'recepcionista';
});

const route = window.route || ((name) => {
  const map = {
    'dashboard': '/dashboard',
    'usuarios.index': '/usuarios',
    'roles.index': '/roles',
    'bitacora.index': '/bitacora',
    'backups.index': '/backups',
    'especialidades.index': '/especialidades',
    'medicos.index': '/medicos',
    'pacientes.index': '/pacientes',
  };
  return map[name] || `/${name}`;
});
</script>
