<template>
  <div class="min-h-screen w-full grid grid-cols-1 md:grid-cols-2 bg-white">
    <!-- Lado Izquierdo: Imagen Profesional Clínica -->
    <div class="hidden md:relative md:flex flex-col justify-between bg-slate-900 overflow-hidden">
      <img 
        src="/images/clinic-login.jpg" 
        alt="Instalaciones Médicas" 
        class="absolute inset-0 w-full h-full object-cover opacity-80"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/40 to-transparent"></div>
      
      <!-- Detalle sutil inferior -->
      <div class="relative z-10 p-10 text-white mt-auto">
        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded bg-white/10 backdrop-blur-sm border border-white/15 text-xs font-medium text-slate-200 mb-3">
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          Sistema Operativo Clínico
        </div>
        <p class="text-sm text-slate-300 max-w-md font-light leading-relaxed">
          Plataforma de gestión asistencial, programación médica y registro clínico ambulatorio.
        </p>
      </div>
    </div>

    <!-- Lado Derecho: Formulario Minimalista de Login -->
    <div class="flex items-center justify-center p-6 sm:p-10 lg:p-14 bg-white">
      <div class="w-full max-w-sm">
        <!-- Icono Simple y Discreto -->
        <div class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 mb-6 shadow-xs">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
        </div>

        <h1 class="text-xl font-bold text-slate-900 tracking-tight mb-6">Iniciar sesión</h1>

        <!-- Mensajes de Error Generales -->
        <div v-if="form.errors.email || form.errors.login" class="mb-4 p-3 bg-slate-50 border border-slate-300 rounded text-xs text-slate-700 leading-snug">
          {{ form.errors.email || form.errors.login }}
        </div>

        <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-slate-50 border border-slate-300 rounded text-xs text-slate-700 leading-snug">
          {{ $page.props.flash.success }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <!-- Campo Correo Electrónico -->
          <div>
            <label class="block text-xs font-medium text-slate-700 mb-1.5">Correo electrónico</label>
            <input 
              v-model="form.email" 
              type="email" 
              required 
              placeholder="nombre@correo.com"
              class="w-full px-3 py-2 text-xs rounded border border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition text-slate-800 placeholder:text-slate-400"
              :class="{ 'border-rose-400': form.errors.email || form.errors.login }"
            />
          </div>

          <!-- Campo Contraseña -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-xs font-medium text-slate-700">Contraseña</label>
              <Link :href="'/forgot-password'" class="text-[11px] text-slate-500 hover:text-slate-800 transition">
                ¿Olvidaste tu contraseña?
              </Link>
            </div>
            <div class="relative">
              <input 
                v-model="form.password" 
                :type="showPassword ? 'text' : 'password'" 
                required 
                placeholder="••••••••"
                class="w-full px-3 py-2 text-xs rounded border border-slate-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-600 outline-none transition pr-9 text-slate-800"
                :class="{ 'border-rose-400': form.errors.password }"
              />
              <button 
                type="button" 
                @click="showPassword = !showPassword"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs cursor-pointer p-1"
                :title="showPassword ? 'Ocultar' : 'Ver'"
              >
                <svg v-if="!showPassword" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
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
              class="h-3.5 w-3.5 text-slate-800 focus:ring-slate-600 border-slate-300 rounded cursor-pointer"
            />
            <label for="remember" class="ml-2 block text-xs text-slate-600 cursor-pointer">
              Recordar en este equipo
            </label>
          </div>

          <!-- Botón de Ingreso -->
          <div class="pt-2">
            <button 
              type="submit" 
              :disabled="form.processing"
              class="w-full py-2 px-4 text-xs font-semibold rounded text-white bg-slate-900 hover:bg-slate-800 active:bg-slate-950 transition shadow-xs disabled:opacity-50 cursor-pointer"
            >
              <span v-if="form.processing">Verificando...</span>
              <span v-else>Iniciar sesión</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

const showPassword = ref(false);

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>
