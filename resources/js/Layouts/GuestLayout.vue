<template>
  <div class="grid min-h-screen w-full grid-cols-1 bg-white lg:grid-cols-2">
    <!-- Panel izquierdo con imagen limpia (sin iconos encima) -->
    <div class="relative hidden overflow-hidden bg-slate-900 lg:flex items-center justify-center">
      <img
        src="/images/clinic-login.jpg"
        alt="Instalaciones médicas"
        class="absolute inset-0 h-full w-full object-cover"
      />
      <div class="absolute inset-0 bg-slate-900/20"></div>
    </div>

    <!-- Panel derecho con formulario -->
    <div class="relative flex items-center justify-center bg-white px-6 py-12 sm:px-12">
      <!-- Logo visible en móvil -->
      <div class="absolute top-6 left-6 flex items-center lg:hidden">
        <ApplicationLogo size="sm" variant="light" />
      </div>

      <div class="w-full max-w-[380px]">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>
