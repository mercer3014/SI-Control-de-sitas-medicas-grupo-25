<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-center items-center p-4">
    <div class="w-full max-w-sm bg-white rounded-lg border border-slate-200 shadow-sm p-6 sm:p-8">
      <div class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 mb-5 shadow-xs">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
        </svg>
      </div>

      <h1 class="text-lg font-bold text-slate-900 tracking-tight mb-2">Recuperar contraseña</h1>
      <p class="text-xs text-slate-500 mb-5 leading-relaxed">
        Ingresa tu correo electrónico registrado para recibir el código de verificación de 6 dígitos.
      </p>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-slate-700 mb-1.5">Correo electrónico</label>
          <input 
            v-model="form.email" 
            type="email" 
            required 
            placeholder="nombre@correo.com"
            class="w-full px-3 py-2 text-xs rounded border border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition text-slate-800"
            :class="{ 'border-rose-400': form.errors.email }"
          />
          <p v-if="form.errors.email" class="text-[11px] text-rose-600 mt-1">
            {{ form.errors.email }}
          </p>
        </div>

        <div class="pt-1 flex flex-col gap-2">
          <button 
            type="submit" 
            :disabled="form.processing"
            class="w-full py-2 px-4 text-xs font-semibold rounded text-white bg-slate-900 hover:bg-slate-800 transition shadow-xs disabled:opacity-50 cursor-pointer"
          >
            <span v-if="form.processing">Enviando...</span>
            <span v-else>Enviar código</span>
          </button>

          <Link 
            :href="'/login'" 
            class="text-center py-1.5 text-xs text-slate-500 hover:text-slate-800 transition"
          >
            Volver a inicio de sesión
          </Link>
        </div>
      </form>
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
