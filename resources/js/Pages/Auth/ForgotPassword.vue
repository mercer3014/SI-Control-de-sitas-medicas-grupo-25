<template>
  <div class="min-h-screen bg-slate-100 flex flex-col justify-center items-center p-4">
    <div class="w-full max-w-sm bg-white rounded-lg border border-slate-200 shadow-md overflow-hidden">
      <!-- Encabezado Clínico -->
      <div class="bg-[#0F2942] p-5 text-center text-white">
        <h1 class="text-sm font-bold tracking-tight">RECUPERACIÓN DE CONTRASEÑA</h1>
        <p class="text-[11px] text-blue-200 mt-0.5">Clínica Vida Salud</p>
      </div>

      <div class="p-6">
        <p class="text-xs text-slate-600 mb-4 leading-relaxed">
          Ingresa el correo electrónico asociado a tu cuenta. Te enviaremos un código de seguridad para restablecer tu acceso.
        </p>

        <form @submit.prevent="submit" class="space-y-3.5">
          <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">Correo Electrónico</label>
            <input 
              v-model="form.email" 
              type="email" 
              required 
              placeholder="tu-correo@ejemplo.com"
              class="w-full px-3 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition"
              :class="{ 'border-rose-400 bg-rose-50/30': form.errors.email }"
            />
            <p v-if="form.errors.email" class="text-[11px] text-rose-600 mt-1">
              {{ form.errors.email }}
            </p>
          </div>

          <div class="pt-2 flex flex-col gap-2">
            <button 
              type="submit" 
              :disabled="form.processing"
              class="w-full py-2 px-4 text-xs font-semibold rounded text-white bg-blue-600 hover:bg-blue-700 transition shadow-xs disabled:opacity-50 cursor-pointer"
            >
              <span v-if="form.processing">Enviando código...</span>
              <span v-else>Enviar Código de Recuperación</span>
            </button>

            <Link 
              :href="'/login'" 
              class="text-center py-1.5 text-xs text-slate-600 hover:text-slate-900 transition"
            >
              Volver al inicio de sesión
            </Link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
  email: '',
});

const submit = () => {
  form.post('/forgot-password');
};
</script>
