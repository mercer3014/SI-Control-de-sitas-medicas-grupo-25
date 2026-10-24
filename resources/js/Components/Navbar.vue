<template>
  <header class="h-14 bg-white border-b border-slate-200 px-4 flex items-center justify-between sticky top-0 z-30 shadow-xs">
    <div class="flex items-center gap-3">
      <h2 class="text-sm font-bold text-slate-800 tracking-tight">
        <slot name="title">Clínica Vida Salud</slot>
      </h2>
    </div>

    <!-- Acciones de Usuario -->
    <div class="flex items-center gap-3">
      <div class="hidden sm:flex items-center gap-2 px-2.5 py-1 bg-slate-100 rounded border border-slate-200 text-xs">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <span class="font-medium text-slate-700">{{ $page.props.auth.user?.persona?.nombre_completo || $page.props.auth.user?.username }}</span>
        <span class="text-slate-400">|</span>
        <span class="font-semibold text-blue-700 uppercase text-[10px]">{{ $page.props.auth.user?.rol?.nombre }}</span>
      </div>

      <!-- Botón de Salir -->
      <button 
        type="button"
        @click="$emit('request-logout')"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded hover:bg-rose-100 transition cursor-pointer"
        title="Salir del sistema"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span>Salir</span>
      </button>
    </div>
  </header>
</template>

<script setup>
defineEmits(['request-logout']);
</script>
