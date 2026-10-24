<template>
  <div class="grid min-h-screen w-full grid-cols-1 bg-white lg:grid-cols-2">
    <!-- Panel izquierdo con imagen -->
    <div class="relative hidden overflow-hidden bg-slate-900 lg:flex items-center justify-center">
      <img
        src="/images/clinic-login.jpg"
        alt="Instalaciones médicas"
        class="absolute inset-0 h-full w-full object-cover opacity-60"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-900/40 to-slate-950/60"></div>

      <!-- Icono central sobrio -->
      <div class="relative z-10 flex flex-col items-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20 shadow-xl backdrop-blur-md">
          <AppIcon name="heart-pulse" class="h-9 w-9 text-white" :stroke-width="1.8" />
        </div>
      </div>
    </div>

    <!-- Panel derecho con formulario -->
    <div class="relative flex items-center justify-center bg-slate-50 px-6 py-12 sm:px-12">
      <!-- Icono visible en móvil -->
      <div class="absolute top-6 left-6 flex items-center gap-2 lg:hidden">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-white">
          <AppIcon name="heart-pulse" class="h-5 w-5" :stroke-width="2" />
        </div>
      </div>

      <div class="w-full max-w-[380px]">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import AppIcon from '@/Components/AppIcon.vue';
</script>
