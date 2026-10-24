<template>
  <GuestLayout>
    <Head title="Restablecer contraseña" />

    <div class="mb-8">
      <ApplicationLogo size="lg" variant="light" class="mb-5" />
      <h1 class="text-[24px] font-bold tracking-tight text-stone-900">Restablecer contraseña</h1>
      <p class="mt-1.5 text-[13.5px] leading-relaxed text-stone-500">
        Ingresa el código de 6 dígitos que enviamos a tu correo y define tu nueva contraseña.
      </p>
    </div>

    <div
      v-if="$page.props.flash?.success"
      class="mb-5 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-3 text-[13px] font-medium text-emerald-700 animate-scale-in"
    >
      <AppIcon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0" />
      <span>{{ $page.props.flash.success }}</span>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <FormField id="email" label="Correo electrónico">
        <template #default="{ inputClass, id }">
          <div class="relative">
            <AppIcon name="mail" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" />
            <input
              :id="id"
              v-model="form.email"
              type="email"
              readonly
              :class="[inputClass, 'pl-9 bg-stone-100 text-stone-500']"
            />
          </div>
        </template>
      </FormField>

      <FormField
        id="token"
        label="Código de verificación"
        :error="form.errors.token"
        hint="Revisa tu bandeja de entrada; el código expira en 15 minutos."
        required
      >
        <template #default="{ inputClass, id }">
          <input
            :id="id"
            v-model="form.token"
            type="text"
            inputmode="numeric"
            required
            maxlength="6"
            placeholder="●●●●●●"
            :class="[inputClass, 'text-center font-mono text-[18px] font-bold tracking-[0.5em]']"
          />
        </template>
      </FormField>

      <FormField
        id="password"
        label="Nueva contraseña"
        :error="form.errors.password"
        required
      >
        <template #default="{ inputClass, id }">
          <input
            :id="id"
            v-model="form.password"
            type="password"
            required
            autocomplete="new-password"
            placeholder="Mínimo 6 caracteres"
            :class="inputClass"
          />
        </template>
      </FormField>

      <FormField id="password_confirmation" label="Confirmar contraseña" required>
        <template #default="{ inputClass, id }">
          <input
            :id="id"
            v-model="form.password_confirmation"
            type="password"
            required
            autocomplete="new-password"
            placeholder="Repite la contraseña"
            :class="inputClass"
          />
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
        {{ form.processing ? 'Actualizando…' : 'Guardar nueva contraseña' }}
      </button>

      <Link
        href="/login"
        class="group flex items-center justify-center gap-1.5 py-1 text-[13px] font-semibold text-stone-500 transition hover:text-brand-700"
      >
        <AppIcon name="chevron-left" class="h-3.5 w-3.5 transition-transform group-hover:-translate-x-0.5" />
        Cancelar y volver al login
      </Link>
    </form>
  </GuestLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import FormField from '@/Components/FormField.vue';
import AppIcon from '@/Components/AppIcon.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

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
