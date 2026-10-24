<template>
  <div class="min-h-screen bg-surface">
    <Sidebar :collapsed="sidebarCollapsed" @toggle="sidebarCollapsed = !sidebarCollapsed" />

    <div
      :class="[
        'flex min-h-screen flex-col transition-[margin] duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]',
        sidebarCollapsed ? 'ml-[68px]' : 'ml-64'
      ]"
    >
      <Navbar @request-logout="showLogoutModal = true">
        <template #title>
          <slot name="header">Sistema de Citas Médicas</slot>
        </template>
      </Navbar>

      <!-- Contenido con transición de página -->
      <main class="flex-1 px-6 py-6 lg:px-8">
        <Transition name="page" mode="out-in">
          <div :key="$page.component">
            <slot />
          </div>
        </Transition>
      </main>

      <footer class="border-t border-slate-200/70 px-6 py-3 text-center text-[11px] text-slate-400">
        &copy; {{ new Date().getFullYear() }} — Sistema Clínico
      </footer>
    </div>

    <!-- Notificaciones -->
    <FlashToasts />

    <!-- Confirmación de cierre de sesión -->
    <DialogModal
      :show="showLogoutModal"
      title="Cerrar sesión"
      message="¿Estás seguro de que deseas salir del sistema? Cualquier operación sin guardar se perderá."
      confirm-text="Sí, cerrar sesión"
      cancel-text="Permanecer"
      type="danger"
      @confirm="handleLogout"
      @close="showLogoutModal = false"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import Navbar from '@/Components/Navbar.vue';
import DialogModal from '@/Components/DialogModal.vue';
import FlashToasts from '@/Components/FlashToasts.vue';

const sidebarCollapsed = ref(false);
const showLogoutModal = ref(false);

const handleLogout = () => {
  showLogoutModal.value = false;
  router.post('/logout');
};
</script>
