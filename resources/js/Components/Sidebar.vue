<template>
  <aside
    :class="[
      'fixed inset-y-0 left-0 z-40 flex flex-col bg-slate-900 text-slate-300 transition-[width] duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] border-r border-slate-800',
      collapsed ? 'w-[68px]' : 'w-60'
    ]"
  >
    <!-- Marca / Cabecera -->
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-800 px-4">
      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-800 text-white border border-slate-700 shadow-sm">
        <AppIcon name="heart-pulse" class="h-5 w-5" :stroke-width="2" />
      </div>
      <div v-if="!collapsed" class="min-w-0 leading-tight">
        <span class="block text-sm font-bold tracking-wide text-white">CLÍNICA</span>
      </div>
      <button
        type="button"
        @click="$emit('toggle')"
        class="ml-auto rounded p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white transition cursor-pointer"
        :class="{ 'hidden': collapsed }"
        title="Colapsar menú"
      >
        <AppIcon name="chevron-left" class="h-4 w-4" />
      </button>
    </div>

    <!-- Botón flotante para expandir cuando está colapsado -->
    <button
      v-if="collapsed"
      type="button"
      @click="$emit('toggle')"
      class="absolute -right-3 top-[70px] z-50 flex h-6 w-6 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-600 shadow-sm transition hover:text-slate-900 cursor-pointer"
      title="Expandir menú"
    >
      <AppIcon name="chevron-right" class="h-3.5 w-3.5" />
    </button>

    <!-- Navegación: Catálogos ARRIBA, Seguridad ABAJO -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6 [scrollbar-width:thin]">
      <div v-for="section in sections" :key="section.title">
        <p
          v-if="!collapsed && section.title"
          class="mb-2 px-2.5 text-[10px] font-bold uppercase tracking-wider text-slate-400"
        >{{ section.title }}</p>
        <div v-else-if="section.title" class="mx-3 mb-2 border-t border-slate-800"></div>

        <div class="space-y-1">
          <Link
            v-for="item in section.items"
            :key="item.label"
            :href="item.href"
            class="group relative flex items-center gap-3 rounded-lg px-2.5 py-2 text-xs font-medium transition cursor-pointer"
            :class="isActive(item)
              ? 'bg-slate-800 text-white font-semibold'
              : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200'"
            :title="collapsed ? item.label : ''"
          >
            <!-- Indicador activo -->
            <span
              v-if="isActive(item)"
              class="absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full bg-blue-500"
            ></span>
            <AppIcon
              :name="item.icon"
              class="h-4 w-4 shrink-0 transition-colors"
              :class="isActive(item) ? 'text-blue-400' : 'text-slate-400 group-hover:text-slate-300'"
            />
            <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
          </Link>
        </div>
      </div>
    </nav>

    <!-- Usuario -->
    <div class="shrink-0 border-t border-slate-800 bg-slate-950/60 p-3">
      <div class="flex items-center gap-2.5" :class="{ 'justify-center': collapsed }">
        <Avatar :name="userName" size="sm" class="ring-1 ring-slate-700" />
        <div v-if="!collapsed" class="min-w-0 leading-tight">
          <p class="truncate text-xs font-semibold text-white">{{ userName }}</p>
          <p class="truncate text-[10.5px] font-medium text-slate-400">{{ userRole }}</p>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppIcon from './AppIcon.vue';
import Avatar from './Avatar.vue';

defineProps({
  collapsed: { type: Boolean, default: false },
});

defineEmits(['toggle']);

const page = usePage();

const userName = computed(() =>
  page.props.auth?.user?.persona?.nombre_completo || page.props.auth?.user?.username || 'Usuario'
);
const userRole = computed(() => page.props.auth?.user?.rol?.nombre || '');

const isAdmin = computed(() => page.props.auth?.user?.rol?.nombre?.toLowerCase() === 'administrador');
const isRecepcionista = computed(() => page.props.auth?.user?.rol?.nombre?.toLowerCase() === 'recepcionista');

const sections = computed(() => {
  // 1. Catálogos Clínicos SIEMPRE ARRIBA
  const catalogos = {
    title: 'Catálogos Clínicos',
    items: [],
  };

  if (isAdmin.value) {
    catalogos.items.push({ label: 'Especialidades', icon: 'clipboard-list', href: '/especialidades', component: 'Especialidades' });
  }

  if (isAdmin.value || isRecepcionista.value) {
    catalogos.items.push(
      { label: 'Médicos', icon: 'stethoscope', href: '/medicos', component: 'Medicos' },
      { label: 'Pacientes', icon: 'users', href: '/pacientes', component: 'Pacientes' },
    );
  }

  // 2. Seguridad y Acceso SIEMPRE ABAJO
  const seguridad = {
    title: 'Seguridad y Acceso',
    items: [],
  };

  if (isAdmin.value) {
    seguridad.items.push(
      { label: 'Usuarios', icon: 'id-card', href: '/usuarios', component: 'Usuarios' },
      { label: 'Roles y Permisos', icon: 'shield-check', href: '/roles', component: 'Roles' },
      { label: 'Bitácora', icon: 'history', href: '/bitacora', component: 'Bitacora' },
      { label: 'Respaldos BD', icon: 'database', href: '/backups', component: 'Backups' },
    );
  }

  return [catalogos, seguridad].filter((s) => s.items.length > 0);
});

const isActive = (item) => page.component.startsWith(item.component);
</script>
