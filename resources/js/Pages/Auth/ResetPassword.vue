<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-center items-center p-4">
    <div class="w-full max-w-sm bg-white rounded-lg border border-slate-200 shadow-sm p-6 sm:p-8">
      <div class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 mb-5 shadow-xs">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
      </div>

      <h1 class="text-lg font-bold text-slate-900 tracking-tight mb-2">Restablecer contraseña</h1>
      <p class="text-xs text-slate-500 mb-4 leading-relaxed">
        Ingresa el código de 6 dígitos enviado a tu correo y tu nueva contraseña.
      </p>

      <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-slate-50 border border-slate-300 rounded text-xs text-slate-700 leading-snug">
        {{ $page.props.flash.success }}
      </div>

      <form @submit.prevent="submit" class="space-y-3.5">
        <div>
          <label class="block text-xs font-medium text-slate-700 mb-1">Correo electrónico</label>
          <input 
            v-model="form.email" 
            type="email" 
            required 
            readonly
            class="w-full px-3 py-1.5 text-xs rounded border border-slate-200 bg-slate-100 text-slate-600 outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-700 mb-1">Código de verificación (6 dígitos)</label>
          <input 
            v-model="form.token" 
            type="text" 
            required 
            maxlength="6"
            placeholder="123456"
            class="w-full px-3 py-1.5 text-xs font-mono font-bold tracking-widest text-center rounded border border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition"
            :class="{ 'border-rose-400': form.errors.token }"
          />
          <p v-if="form.errors.token" class="text-[11px] text-rose-600 mt-1">
            {{ form.errors.token }}
          </p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-700 mb-1">Nueva contraseña</label>
          <input 
            v-model="form.password" 
            type="password" 
            required 
            placeholder="Mínimo 6 caracteres"
            class="w-full px-3 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition text-slate-800"
            :class="{ 'border-rose-400': form.errors.password }"
          />
          <p v-if="form.errors.password" class="text-[11px] text-rose-600 mt-1">
            {{ form.errors.password }}
          </p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-700 mb-1">Confirmar contraseña</label>
          <input 
            v-model="form.password_confirmation" 
            type="password" 
            required 
            placeholder="Repite la contraseña"
            class="w-full px-3 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition text-slate-800"
          />
        </div>

        <div class="pt-2 flex flex-col gap-2">
          <button 
            type="submit" 
            :disabled="form.processing"
            class="w-full py-2 px-4 text-xs font-semibold rounded text-white bg-slate-900 hover:bg-slate-800 transition shadow-xs disabled:opacity-50 cursor-pointer"
          >
            <span v-if="form.processing">Actualizando...</span>
            <span v-else>Guardar nueva contraseña</span>
          </button>

          <Link 
            :href="'/login'" 
            class="text-center py-1.5 text-xs text-slate-500 hover:text-slate-800 transition"
          >
            Cancelar y volver al login
          </Link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
  email: { type: String, default: '' },
});

const form = useForm({
  email: props.email,
  token: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post('/reset-password');
};
</script>
