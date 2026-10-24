<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Menú Lateral Retráctil -->
    <Sidebar 
      :collapsed="sidebarCollapsed" 
      @toggle="sidebarCollapsed = !sidebarCollapsed" 
    />

    <!-- Contenedor Principal (Ajusta margen dinámicamente) -->
    <div 
      :class="[
        'flex-1 flex flex-col transition-all duration-200 ease-in-out min-w-0',
        sidebarCollapsed ? 'ml-16' : 'ml-60'
      ]"
    >
      <!-- Barra Superior -->
      <Navbar @request-logout="showLogoutModal = true">
        <template #title>
          <slot name="header">Sistema de Citas Médicas</slot>
        </template>
      </Navbar>

      <!-- Mensajes Informativos Compactos del Servidor (Flash) -->
      <div v-if="$page.props.flash?.success || $page.props.flash?.error" class="px-5 pt-3">
        <div 
          v-if="$page.props.flash?.success"
          class="flex items-center justify-between px-3.5 py-2 bg-emerald-50 border border-emerald-200 rounded text-xs text-emerald-800"
        >
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>{{ $page.props.flash.success }}</span>
          </div>
          <button @click="$page.props.flash.success = null" class="text-emerald-600 hover:text-emerald-900 font-bold">&times;</button>
        </div>

        <div 
          v-if="$page.props.flash?.error"
          class="flex items-center justify-between px-3.5 py-2 bg-rose-50 border border-rose-200 rounded text-xs text-rose-800 mt-2"
        >
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            <span>{{ $page.props.flash.error }}</span>
          </div>
          <button @click="$page.props.flash.error = null" class="text-rose-600 hover:text-rose-900 font-bold">&times;</button>
        </div>
      </div>

      <!-- Contenido de la Página (Padding compacto y optimizado) -->
      <main class="flex-1 p-5">
        <slot />
      </main>

      <!-- Pie de página discreto -->
      <footer class="px-5 py-2.5 bg-white border-t border-slate-200 text-center text-[11px] text-slate-500">
        Clínica Vida Salud &copy; {{ new Date().getFullYear() }} — Sistema de Control y Gestión de Citas Médicas (UAGRM Grupo #25)
      </footer>
    </div>

    <!-- Diálogo Modal Central de Confirmación para Salir del Sistema -->
    <DialogModal
      :show="showLogoutModal"
      title="Confirmar salida del sistema"
      message="¿Estás seguro de que deseas cerrar tu sesión actual?"
      confirm-text="Cerrar sesión"
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

const sidebarCollapsed = ref(false);
const showLogoutModal = ref(false);

const handleLogout = () => {
  showLogoutModal.value = false;
  router.post('/logout');
};
</script>
