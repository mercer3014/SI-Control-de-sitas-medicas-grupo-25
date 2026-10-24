<template>
  <GuestLayout>
    <Head title="Recuperar contraseña" />

    <div class="mb-8">
      <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-100">
        <AppIcon name="key" class="h-6 w-6 text-brand-700" />
      </div>
      <h1 class="text-[24px] font-bold tracking-tight text-stone-900">Recuperar contraseña</h1>
      <p class="mt-1.5 text-[13.5px] leading-relaxed text-stone-500">
        Ingresa tu correo institucional y te enviaremos un código de verificación de 6 dígitos válido por 15 minutos.
      </p>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <FormField
        id="email"
        label="Correo electrónico"
        :error="form.errors.email"
        required
      >
        <template #default="{ inputClass, id }">
          <div class="relative">
            <AppIcon name="mail" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" />
            <input
              :id="id"
              v-model="form.email"
              type="email"
              required
              autocomplete="username"
              placeholder="nombre@correo.com"
              :class="[inputClass, 'pl-9']"
              style="padding-left: 2.5rem !important;"
            />
          </div>
        </template>
      </FormField>

      <button
        type="submit"
        :disabled="form.processing"
        class="btn-clinica w-full bg-brand-600 py-2.5 text-[14px] text-white shadow-sm hover:bg-brand-700"
      >
        <svg v-if="form.processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
        </svg>
        {{ form.processing ? 'Enviando código…' : 'Enviar código de verificación' }}
      </button>

      <Link
        href="/login"
        class="group flex items-center justify-center gap-1.5 py-1 text-[13px] font-semibold text-stone-500 transition hover:text-brand-700"
      >
        <AppIcon name="chevron-left" class="h-3.5 w-3.5 transition-transform group-hover:-translate-x-0.5" />
        Volver a inicio de sesión
      </Link>
    </form>
  </GuestLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import FormField from '@/Components/FormField.vue';
import AppIcon from '@/Components/AppIcon.vue';

const form = useForm({
  email: '',
});

const submit = () => {
  form.post('/forgot-password');
};
</script>
