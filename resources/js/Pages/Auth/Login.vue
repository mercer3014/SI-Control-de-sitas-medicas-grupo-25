<template>
  <div class="min-h-screen bg-slate-100 flex flex-col justify-center items-center p-4">
    <!-- Contenedor de la Tarjeta de Login -->
    <div class="w-full max-w-sm bg-white rounded-lg border border-slate-200 shadow-md overflow-hidden">
      <!-- Encabezado Clínico -->
      <div class="bg-[#0F2942] p-5 text-center text-white">
        <div class="w-10 h-10 mx-auto mb-2 rounded bg-blue-600 flex items-center justify-center font-bold text-white text-base shadow-xs">
          VS
        </div>
        <h1 class="text-base font-bold tracking-tight">CLÍNICA VIDA SALUD</h1>
        <p class="text-xs text-blue-200 mt-0.5">Control de Citas Médicas</p>
      </div>

      <!-- Formulario -->
      <div class="p-6">
        <h2 class="text-sm font-semibold text-slate-800 mb-1">Iniciar Sesión</h2>
        <p class="text-xs text-slate-500 mb-4">Ingresa tus credenciales autorizadas</p>

        <!-- Mensajes de Error Generales del Servidor -->
        <div v-if="form.errors.login" class="mb-4 p-3 bg-rose-50 border border-rose-200 rounded text-xs text-rose-700 leading-snug">
          {{ form.errors.login }}
        </div>

        <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded text-xs text-emerald-700 leading-snug">
          {{ $page.props.flash.success }}
        </div>

        <form @submit.prevent="submit" class="space-y-3.5">
          <!-- Campo Usuario / Email -->
          <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">Usuario o Correo</label>
            <input 
              v-model="form.login" 
              type="text" 
              required 
              placeholder="ej. admin o usuario@correo.com"
              class="w-full px-3 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition"
              :class="{ 'border-rose-400 bg-rose-50/30': form.errors.login }"
            />
          </div>

          <!-- Campo Contraseña -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block text-xs font-medium text-slate-700">Contraseña</label>
              <Link :href="'/forgot-password'" class="text-[11px] text-blue-600 hover:text-blue-800 hover:underline">
                ¿Olvidaste tu contraseña?
              </Link>
            </div>
            <div class="relative">
              <input 
                v-model="form.password" 
                :type="showPassword ? 'text' : 'password'" 
                required 
                placeholder="••••••••"
                class="w-full px-3 py-1.5 text-xs rounded border border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition pr-8"
                :class="{ 'border-rose-400 bg-rose-50/30': form.errors.password }"
              />
              <button 
                type="button" 
                @click="showPassword = !showPassword"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs cursor-pointer"
              >
                {{ showPassword ? 'Ocultar' : 'Ver' }}
              </button>
            </div>
            <p v-if="form.errors.password" class="text-[11px] text-rose-600 mt-1">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Recordarme -->
          <div class="flex items-center">
            <input 
              id="remember" 
              v-model="form.remember" 
              type="checkbox" 
              class="h-3.5 w-3.5 text-blue-600 focus:ring-blue-500 border-slate-300 rounded cursor-pointer"
            />
            <label for="remember" class="ml-2 block text-xs text-slate-600 cursor-pointer">
              Recordar en este equipo
            </label>
          </div>

          <!-- Botón de Ingreso -->
          <div class="pt-1">
            <button 
              type="submit" 
              :disabled="form.processing"
              class="w-full py-2 px-4 text-xs font-semibold rounded text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 transition shadow-xs disabled:opacity-50 cursor-pointer"
            >
              <span v-if="form.processing">Verificando credenciales...</span>
              <span v-else>Ingresar al Sistema</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Pie de página del login -->
      <div class="px-6 py-2.5 bg-slate-50 border-t border-slate-200 text-center">
        <span class="text-[11px] text-slate-500">Acceso restringido para personal autorizado</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

const showPassword = ref(false);

const form = useForm({
  login: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>
