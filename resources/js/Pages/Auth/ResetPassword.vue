<template>
  <div class="min-h-screen bg-slate-100 flex flex-col justify-center items-center p-4">
    <div class="w-full max-w-sm bg-white rounded-lg border border-slate-200 shadow-md overflow-hidden">
      <!-- Encabezado Clínico -->
      <div class="bg-[#0F2942] p-5 text-center text-white">
        <h1 class="text-sm font-bold tracking-tight">NUEVA CONTRASEÑA</h1>
        <p class="text-[11px] text-blue-200 mt-0.5">Clínica Vida Salud</p>
      </div>

      <div class="p-6">
        <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-xs text-blue-800">
          {{ $page.props.flash.success }}
        </div>

        <form @submit.prevent="submit" class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">Correo Electrónico</label>
            <input 
              v-model="form.email" 
              type="email" 
              required 
              readonly
              class="w-full px-3 py-1.5 text-xs rounded border border-slate-200 bg-slate-100 text-slate-600 outline-none"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">Código de Verificación (6 dígitos)</label>
            <input 
              v-model="form.token" 
              type="text" 
              required 
              maxlength="6"
              placeholder="123456"
              class="w-full px-3 py-1.5 text-xs font-mono font-bold tracking-widest text-center rounded border border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition"
              :class="{ 'border-rose-400 bg-rose-50/30': form.errors.token }"
            />
            <p v-if="form.errors.token" class="text-[11px] text-rose-600 mt-1">
              {{ form.errors.token }}
            </p>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">Nueva Contraseña</label>
            <input 
              v-model="form.password" 
              type="password" 
              required 
              placeholder="Mínimo 6 caracteres"
              class="w-full px-3 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition"
              :class="{ 'border-rose-400 bg-rose-50/30': form.errors.password }"
            />
            <p v-if="form.errors.password" class="text-[11px] text-rose-600 mt-1">
              {{ form.errors.password }}
            </p>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">Confirmar Contraseña</label>
            <input 
              v-model="form.password_confirmation" 
              type="password" 
              required 
              placeholder="Repite la contraseña"
              class="w-full px-3 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition"
            />
          </div>

          <div class="pt-2 flex flex-col gap-2">
            <button 
              type="submit" 
              :disabled="form.processing"
              class="w-full py-2 px-4 text-xs font-semibold rounded text-white bg-blue-600 hover:bg-blue-700 transition shadow-xs disabled:opacity-50 cursor-pointer"
            >
              <span v-if="form.processing">Actualizando...</span>
              <span v-else>Restablecer Contraseña</span>
            </button>

            <Link 
              :href="'/login'" 
              class="text-center py-1.5 text-xs text-slate-600 hover:text-slate-900 transition"
            >
              Cancelar y volver al login
            </Link>
          </div>
        </form>
      </div>
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
