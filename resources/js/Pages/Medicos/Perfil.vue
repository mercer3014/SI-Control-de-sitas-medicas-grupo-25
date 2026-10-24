<template>
  <AppLayout>
    <template #header>
      <span>Perfil del Médico</span>
    </template>

    <div class="space-y-4 max-w-4xl mx-auto">
      <!-- Tarjeta Principal del Médico -->
      <div class="bg-white rounded border border-slate-200 shadow-xs overflow-hidden">
        <div class="bg-[#0F2942] p-5 text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded bg-blue-600 flex items-center justify-center font-bold text-lg text-white shadow-xs">
              {{ medico.persona?.nombres?.charAt(0) }}{{ medico.persona?.apellidos?.charAt(0) }}
            </div>
            <div>
              <h1 class="text-base font-bold">Dr(a). {{ medico.persona?.nombre_completo }}</h1>
              <p class="text-xs text-blue-200 font-mono mt-0.5">
                Matrícula Profesional: {{ medico.matricula_profesional }} &bull; CI: {{ medico.persona?.ci }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <span 
              :class="[
                'px-2.5 py-1 text-xs rounded font-semibold',
                medico.estado ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-rose-500/20 text-rose-300 border border-rose-400/30'
              ]"
            >
              {{ medico.estado ? 'Habilitado para Citas' : 'Inactivo' }}
            </span>
            <Link 
              :href="'/medicos'" 
              class="px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded text-xs transition"
            >
              &larr; Volver
            </Link>
          </div>
        </div>

        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
          <!-- Datos Personales y Contacto -->
          <div class="space-y-3">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-1.5">
              Datos Personales y Contacto
            </h3>
            <div class="space-y-2">
              <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-500">Documento de Identidad:</span>
                <span class="font-semibold text-slate-800">{{ medico.persona?.ci }}</span>
              </div>
              <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-500">Fecha de Nacimiento:</span>
                <span class="font-semibold text-slate-800">{{ medico.persona?.fecha_nacimiento ? new Date(medico.persona.fecha_nacimiento).toLocaleDateString('es-BO') : '-' }}</span>
              </div>
              <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-500">Género:</span>
                <span class="font-semibold text-slate-800">{{ medico.persona?.genero === 'M' ? 'Masculino' : (medico.persona?.genero === 'F' ? 'Femenino' : 'Otro') }}</span>
              </div>
              <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-500">Teléfono / Celular:</span>
                <span class="font-semibold text-slate-800">{{ medico.persona?.telefono || 'No registrado' }}</span>
              </div>
              <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-500">Dirección:</span>
                <span class="font-semibold text-slate-800">{{ medico.persona?.direccion || 'No registrada' }}</span>
              </div>
            </div>
          </div>

          <!-- Especialidades Asignadas -->
          <div class="space-y-3">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-1.5">
              Especialidades y Aranceles
            </h3>
            <div class="divide-y divide-slate-100">
              <div 
                v-for="esp in medico.especialidades" 
                :key="esp.id"
                class="py-2 flex items-center justify-between"
              >
                <div>
                  <span class="font-semibold text-slate-800 block">{{ esp.nombre }}</span>
                  <span class="text-[11px] text-slate-500">{{ esp.descripcion || 'Sin descripción' }}</span>
                </div>
                <div class="text-right">
                  <span class="font-mono font-bold text-blue-700 block">Bs. {{ esp.pivot.precio_consulta }}</span>
                  <span class="text-[10px] text-slate-400">Por consulta</span>
                </div>
              </div>

              <div v-if="medico.especialidades.length === 0" class="py-4 text-center text-slate-400">
                No tiene especialidades vinculadas actualmente.
              </div>
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
  medico: Object,
});
</script>
