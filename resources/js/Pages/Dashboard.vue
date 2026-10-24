<template>
  <AppLayout>
    <template #header>Panel General</template>

    <!-- Saludo -->
    <div class="mb-6 animate-fade-up">
      <h1 class="text-[22px] font-bold tracking-tight text-stone-900">
        {{ saludo }}, <span class="text-brand-700">{{ primerNombre }}</span>
      </h1>
      <p class="mt-1 text-[13.5px] text-stone-500">Este es el resumen operativo de la clínica al día de hoy.</p>
    </div>

    <!-- Tarjetas de métricas -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 stagger">
      <StatCard
        label="Pacientes"
        :value="stats.total_pacientes"
        icon="users"
        tone="brand"
        hint="Registrados y activos"
        hint-dot="bg-brand-500"
        href="/pacientes"
      />
      <StatCard
        label="Médicos"
        :value="stats.total_medicos"
        icon="stethoscope"
        tone="sky"
        hint="Profesionales habilitados"
        hint-dot="bg-sky-500"
        href="/medicos"
      />
      <StatCard
        label="Especialidades"
        :value="stats.total_especialidades"
        icon="clipboard-list"
        tone="violet"
        hint="Áreas de atención"
        hint-dot="bg-violet-500"
        href="/especialidades"
      />
      <StatCard
        label="Usuarios"
        :value="stats.total_usuarios"
        icon="id-card"
        tone="amber"
        hint="Cuentas habilitadas"
        hint-dot="bg-amber-500"
        href="/usuarios"
      />
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
      <!-- Actividad reciente -->
      <section class="xl:col-span-2 overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card animate-fade-up">
        <header class="flex items-center justify-between border-b border-stone-100 px-5 py-4">
          <div class="flex items-center gap-2.5">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100">
              <AppIcon name="activity" class="h-4 w-4 text-brand-700" />
            </div>
            <div>
              <h2 class="text-[14px] font-bold text-stone-800">Actividad reciente</h2>
              <p class="text-[11.5px] text-stone-400">Últimos eventos registrados en la bitácora</p>
            </div>
          </div>
          <Link
            href="/bitacora"
            class="group flex items-center gap-1 text-[12.5px] font-semibold text-brand-600 hover:text-brand-700 transition"
          >
            Ver bitácora
            <AppIcon name="arrow-right" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" />
          </Link>
        </header>

        <ul v-if="ultimasBitacoras.length" class="divide-y divide-stone-100">
          <li
            v-for="evento in ultimasBitacoras"
            :key="evento.id"
            class="flex items-start gap-3.5 px-5 py-3.5 transition hover:bg-stone-50/70"
          >
            <div
              class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
              :class="accionStyle(evento.accion).bg"
            >
              <AppIcon :name="accionStyle(evento.accion).icon" class="h-4 w-4" :class="accionStyle(evento.accion).color" />
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-md bg-stone-100 px-1.5 py-0.5 font-mono text-[10.5px] font-semibold text-stone-600">
                  {{ evento.accion }}
                </span>
                <span class="text-[11.5px] font-medium text-stone-400">{{ evento.modulo }}</span>
              </div>
              <p class="mt-1 text-[13px] leading-snug text-stone-700">{{ evento.detalle }}</p>
              <p class="mt-1 text-[11.5px] text-stone-400">
                {{ evento.usuario?.persona?.nombre_completo || evento.usuario?.username || 'Sistema' }}
                · IP {{ evento.ip_origen }}
              </p>
            </div>
            <time class="shrink-0 text-[11.5px] font-medium text-stone-400">{{ fechaCorta(evento.fecha_hora) }}</time>
          </li>
        </ul>
        <EmptyState
          v-else
          icon="history"
          title="Sin actividad registrada"
          message="Aún no hay eventos en la bitácora del sistema."
        />
      </section>

      <!-- Pacientes recientes -->
      <section class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-card animate-fade-up">
        <header class="flex items-center justify-between border-b border-stone-100 px-5 py-4">
          <div class="flex items-center gap-2.5">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-100">
              <AppIcon name="users" class="h-4 w-4 text-sky-700" />
            </div>
            <div>
              <h2 class="text-[14px] font-bold text-stone-800">Pacientes recientes</h2>
              <p class="text-[11.5px] text-stone-400">Últimas filiaciones clínicas</p>
            </div>
          </div>
          <Link
            href="/pacientes"
            class="group flex items-center gap-1 text-[12.5px] font-semibold text-brand-600 hover:text-brand-700 transition"
          >
            Gestionar
            <AppIcon name="arrow-right" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" />
          </Link>
        </header>

        <ul v-if="ultimosPacientes.length" class="divide-y divide-stone-100">
          <li
            v-for="paciente in ultimosPacientes"
            :key="paciente.id"
            class="flex items-center gap-3 px-5 py-3 transition hover:bg-stone-50/70"
          >
            <Avatar :name="paciente.persona?.nombre_completo || 'P'" size="sm" />
            <div class="min-w-0 flex-1">
              <p class="truncate text-[13px] font-semibold text-stone-800">
                {{ paciente.persona?.nombre_completo }}
              </p>
              <p class="text-[11.5px] text-stone-400">
                CI: {{ paciente.persona?.ci }}
                <template v-if="paciente.tipo_sangre"> · Sangre {{ paciente.tipo_sangre }}</template>
              </p>
            </div>
            <StatusBadge
              :label="paciente.estado ? 'Activo' : 'Inactivo'"
              :variant="paciente.estado ? 'success' : 'neutral'"
            />
          </li>
        </ul>
        <EmptyState
          v-else
          icon="users"
          title="Sin pacientes"
          message="Cuando registres pacientes aparecerán aquí."
        />
      </section>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Avatar from '@/Components/Avatar.vue';
import AppIcon from '@/Components/AppIcon.vue';

const props = defineProps({
  stats: { type: Object, required: true },
  ultimasBitacoras: { type: Array, default: () => [] },
  ultimosPacientes: { type: Array, default: () => [] },
});

const page = usePage();

const primerNombre = computed(() => {
  const nombres = page.props.auth?.user?.persona?.nombres || page.props.auth?.user?.username || '';
  return nombres.split(' ')[0];
});

const saludo = computed(() => {
  const hora = new Date().getHours();
  if (hora < 12) return 'Buenos días';
  if (hora < 19) return 'Buenas tardes';
  return 'Buenas noches';
});

const accionStyle = (accion = '') => {
  if (accion.includes('SESION') || accion.includes('LOGIN')) return { bg: 'bg-emerald-100', color: 'text-emerald-700', icon: 'key' };
  if (accion.includes('BLOQUEO') || accion.includes('DENEGADO')) return { bg: 'bg-rose-100', color: 'text-rose-700', icon: 'lock' };
  if (accion.includes('PACIENTE')) return { bg: 'bg-sky-100', color: 'text-sky-700', icon: 'users' };
  if (accion.includes('MEDICO')) return { bg: 'bg-violet-100', color: 'text-violet-700', icon: 'stethoscope' };
  if (accion.includes('PASSWORD') || accion.includes('RECUPERACION')) return { bg: 'bg-amber-100', color: 'text-amber-700', icon: 'shield' };
  return { bg: 'bg-stone-100', color: 'text-stone-600', icon: 'info' };
};

const fechaCorta = (fecha) => {
  if (!fecha) return '';
  const d = new Date(fecha);
  const ahora = new Date();
  const esHoy = d.toDateString() === ahora.toDateString();
  const hora = d.toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' });
  if (esHoy) return `Hoy, ${hora}`;
  return d.toLocaleDateString('es-BO', { day: '2-digit', month: 'short' }) + `, ${hora}`;
};
</script>
