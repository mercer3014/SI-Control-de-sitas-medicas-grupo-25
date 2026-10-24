# Sistema de Citas Médicas "Vida Salud" - Plan de Implementación (Ciclo 1)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implementar el 50% funcional del Sistema de Citas Médicas "Vida Salud" (Ciclo 1: Módulos 1 y 2 completos, cubriendo 8 casos de uso), con backend Laravel 11 MVC, frontend Vue 3 / Inertia, base de datos PostgreSQL y conexión con Resend API para recuperación de contraseñas.

**Architecture:** Arquitectura limpia MVC con Laravel 11 y PostgreSQL para persistencia; vistas SPA fluidas con Vue 3 e Inertia.js; diseño clínico responsivo con densidad de datos compacta, menú lateral colapsable y ventanas emergentes de diálogo centradas.

**Tech Stack:** PHP 8.2, Laravel 11, PostgreSQL 18, Vue 3 (Composition API), Inertia.js, Vite, Tailwind CSS v4 / Vanilla CSS, Resend API.

**Spec:** [docs/superpowers/specs/2026-10-23-sistema-citas-ciclo-1-design.md](file:///C:/Users/Cerezo%20Alex/OneDrive/Escritorio/CONTROL%20DE%20CITAS/docs/superpowers/specs/2026-10-23-sistema-citas-ciclo-1-design.md)

## Global Constraints

- Backend estricto en PHP Laravel con correspondencia directa Modelo-Controlador-Vista.
- Base de datos en PostgreSQL (conexión PDO `pgsql`), sin sentencias ni tipos incompatibles de MySQL.
- Teléfono y dirección integrados en `personas` (sin sobre-normalización innecesaria).
- Bajas lógicas (`estado = FALSE`) en lugar de eliminaciones físicas destructivas.
- Resend API (mediante variable de entorno `RESEND_API_KEY`) para envío de correos de recuperación.
- Diálogos emergentes modales centrados para salida y acciones críticas (estilo Windows Forms / C#), prescindiendo de toasts flotantes efímeros.
- Textos humanizados, directos y concisos; interfaz con padding controlado (8px a 12px) y menú colapsable.

## Review Focus

1. **Bloqueo de autenticación:** Bloquear al usuario tras 5 intentos fallidos consecutivos e informar el tiempo restante de espera.
2. **Unicidad de datos clave:** Rechazo inmediato en servidor de CI duplicado (Pacientes/Médicos), nombre duplicado de Especialidad y matrícula duplicada de Médicos.
3. **Auditoría inalterable:** Cada login, logout, creación, edición y desactivación debe registrarse en la tabla `bitacora`.
4. **Recuperación segura:** Token de expiración de 15 minutos verificado en base de datos al restablecer contraseña.
5. **Sesiones y control de acceso:** Denegar acceso a páginas internas si no hay sesión activa o si el rol no tiene el permiso correspondiente.

---

### Task 1: Scaffolding de Laravel 11 e Inertia con Vue 3

**Files:**
- Create/Configure: `composer.json`, `package.json`, `vite.config.js`, `.env`

- [ ] Instalar o inicializar la estructura de Laravel 11 en el directorio raíz.
- [ ] Configurar `.env` con la conexión a PostgreSQL (`DB_CONNECTION=pgsql`, `DB_PORT=5432`, `DB_DATABASE=clinica`).
- [ ] Instalar dependencias de Inertia.js y Vue 3 (`@inertiajs/vue3`, `vue`, `@vitejs/plugin-vue`).
- [ ] Configurar `vite.config.js` y `resources/js/app.js` con el soporte de Inertia.
- [ ] Ejecutar `npm run build` para comprobar que la compilación de frontend funcione sin errores.

---

### Task 2: Migraciones y Modelos de Base de Datos para el Ciclo 1

**Files:**
- Create: Migraciones en `database/migrations/`
- Create: Modelos en `app/Models/` (`Persona.php`, `Usuario.php`, `Rol.php`, `Permiso.php`, `Bitacora.php`, `Especialidad.php`, `Medico.php`, `MedicoEspecialidad.php`, `Paciente.php`)
- Create: `database/seeders/DatabaseSeeder.php`

- [ ] Crear migración para `roles`, `permisos` y `rol_permiso`.
- [ ] Crear migración para `personas` (con `ci`, `nombres`, `apellidos`, `fecha_nacimiento`, `genero`, `telefono`, `direccion`).
- [ ] Crear migración para `usuarios` (con `persona_id`, `rol_id`, `username`, `email`, `password_hash`, `intentos_fallidos`, `bloqueado_hasta`, `estado`).
- [ ] Crear migración para `bitacora` (con `usuario_id`, `accion`, `modulo`, `detalle`, `ip_origen`, `fecha_hora`).
- [ ] Crear migración para `especialidades`, `medicos`, `medico_especialidad` y `pacientes`.
- [ ] Crear los modelos Eloquent con sus relaciones (`belongsTo`, `hasMany`, `belongsToMany`).
- [ ] Crear y ejecutar el Seeder inicial con roles (Administrador, Recepcionista, Médico, Paciente), permisos y usuario administrador base (`admin` / `admin123`).

---

### Task 3: Servicio de Bitácora y Auditoría Automática (CU-10)

**Files:**
- Create: `app/Services/BitacoraService.php`
- Create: `app/Http/Controllers/BitacoraController.php`
- Create: `resources/js/Pages/Bitacora/Index.vue`

- [ ] Implementar `BitacoraService::registrar($accion, $modulo, $detalle, $usuarioId = null)`.
- [ ] Crear `BitacoraController@index` para consultar el historial de auditoría con filtros por fecha, usuario y módulo.
- [ ] Crear vista Vue `Bitacora/Index.vue` con tabla densa, buscador y detalles expandibles.

---

### Task 4: Servicio de Correo Resend API y Recuperación de Contraseña (CU-01)

**Files:**
- Create: `app/Services/ResendService.php`
- Create: `app/Http/Controllers/PasswordResetController.php`
- Create: `resources/js/Pages/Auth/ForgotPassword.vue`
- Create: `resources/js/Pages/Auth/ResetPassword.vue`

- [ ] Implementar `ResendService` utilizando la API Key proporcionada para enviar correos transaccionales vía HTTPS.
- [ ] Crear endpoint y lógica para solicitar token temporal de recuperación de contraseña.
- [ ] Crear formulario Vue para solicitar recuperación por email.
- [ ] Crear endpoint y formulario para ingresar token y nueva contraseña con validación de seguridad.

---

### Task 5: Autenticación, Bloqueo por Intentos y Cierre con Diálogo Modal (CU-01)

**Files:**
- Create: `app/Http/Controllers/AuthController.php`
- Create: `resources/js/Pages/Auth/Login.vue`
- Create: `resources/js/Components/DialogModal.vue`

- [ ] Crear vista de Login Vue con diseño clínico sobrio, campos validados y mensajes claros.
- [ ] Implementar validación en `AuthController@login` con verificación de `bloqueado_hasta` e incremento de `intentos_fallidos` (bloqueo al 5º intento).
- [ ] Implementar registro de inicio de sesión exitoso y fallido en la `bitacora`.
- [ ] Crear componente `DialogModal.vue` estilo escritorio/Windows Forms con botones Confirmar y Cancelar.
- [ ] Integrar el diálogo de confirmación para el cierre de sesión (`AuthController@logout`).

---

### Task 6: Gestión de Roles y Permisos (CU-09)

**Files:**
- Create: `app/Http/Controllers/RolController.php`
- Create: `resources/js/Pages/Roles/Index.vue`
- Create: `app/Http/Middleware/CheckRole.php`

- [ ] Crear `RolController` para listar roles, ver permisos asignados y editar descripciones o estados.
- [ ] Crear vista `Roles/Index.vue` con tarjetas compactas o tabla de roles y badges de permisos.
- [ ] Implementar middleware para proteger rutas administrativas según el rol autenticado.

---

### Task 7: Gestión de Usuarios del Sistema (CU-01)

**Files:**
- Create: `app/Http/Controllers/UsuarioController.php`
- Create: `resources/js/Pages/Usuarios/Index.vue`
- Create: `resources/js/Pages/Usuarios/CreateEditModal.vue`

- [ ] Implementar CRUD de usuarios en `UsuarioController` (crear vinculando a persona, listar con búsqueda, editar rol/estado, baja lógica).
- [ ] Crear vista `Usuarios/Index.vue` con listado rápido, filtros de estado y modal de alta/edición.
- [ ] Validar unicidad de username y email tanto en frontend como en el Form Request.

---

### Task 8: Respaldo de Base de Datos (CU-11)

**Files:**
- Create: `app/Http/Controllers/BackupController.php`
- Create: `resources/js/Pages/Backups/Index.vue`

- [ ] Implementar controlador para consultar estado de las tablas en PostgreSQL y generar exportación SQL/dump descargable.
- [ ] Crear vista `Backups/Index.vue` con métricas de almacenamiento y botón de descarga directa de respaldo.

---

### Task 9: Catálogo de Especialidades Médicas (CU-04)

**Files:**
- Create: `app/Http/Controllers/EspecialidadController.php`
- Create: `resources/js/Pages/Especialidades/Index.vue`

- [ ] Implementar `EspecialidadController` (listar, registrar, editar, desactivar/baja lógica).
- [ ] Validar que no se registren especialidades duplicadas (con normalización de texto en minúsculas).
- [ ] Crear vista `Especialidades/Index.vue` con tabla compacta, buscador reactivo y diálogo modal de confirmación para bajas.

---

### Task 10: Gestión de Médicos y Perfil Médico Base (CU-03, CU-12)

**Files:**
- Create: `app/Http/Controllers/MedicoController.php`
- Create: `resources/js/Pages/Medicos/Index.vue`
- Create: `resources/js/Pages/Medicos/Form.vue`
- Create: `resources/js/Pages/Medicos/Perfil.vue`

- [ ] Implementar `MedicoController` (crear persona médica + registro médico + asignación de especialidades con arancel).
- [ ] Validar unicidad de CI y matrícula profesional en backend.
- [ ] Crear vista `Medicos/Index.vue` con lista de doctores, especialidades activas y estado.
- [ ] Crear vista `Medicos/Perfil.vue` (CU-12) para consultar la ficha completa del médico.

---

### Task 11: Gestión de Pacientes y Ficha Clínica (CU-02)

**Files:**
- Create: `app/Http/Controllers/PacienteController.php`
- Create: `resources/js/Pages/Pacientes/Index.vue`
- Create: `resources/js/Pages/Pacientes/Form.vue`

- [ ] Implementar `PacienteController` (crear persona + datos clínicos como tipo de sangre, contacto de emergencia).
- [ ] Validar unicidad de CI en backend.
- [ ] Crear vista `Pacientes/Index.vue` con búsqueda rápida por CI, nombre o apellido.
- [ ] Crear formulario modal o página compacta para registro y edición de pacientes.

---

### Task 12: Layout Clínico Profesional y Menú Lateral Retráctil

**Files:**
- Create: `resources/js/Layouts/AppLayout.vue`
- Create: `resources/js/Components/Sidebar.vue`
- Create: `resources/js/Components/Navbar.vue`
- Create: `resources/css/app.css`

- [ ] Implementar layout principal con barra lateral retráctil (icono para colapsar/expandir a 60px / 240px).
- [ ] Estructurar la navegación en secciones:
  - **Seguridad:** Usuarios, Roles, Bitácora, Respaldos.
  - **Catálogos Médicos:** Médicos, Especialidades, Pacientes.
- [ ] Aplicar diseño con paleta clínica (#0F2942, #2563EB, #0D9488), densidad compacta de información y transiciones suaves.
- [ ] Conectar el modal de confirmación de salida al botón de cerrar sesión.

---

### Task 13: Pruebas de Calidad (QA), Validaciones y Verificación Integral

- [ ] Probar registro de paciente con CI duplicado y verificar que el servidor rechaza la operación con error amigable.
- [ ] Probar login con contraseña errónea 5 veces consecutivas y verificar bloqueo de cuenta.
- [ ] Probar recuperación de contraseña mediante la API de Resend y verificar recepción de email.
- [ ] Probar bajas lógicas de especialidad, médico y paciente verificando que `estado = FALSE` y que no se eliminan físicamente.
- [ ] Probar que las acciones se reflejan en la tabla `bitacora`.

---

### Task 14: Commit y Sincronización con Repositorio GitHub

- [ ] Revisar `git status` y verificar que no se filtren archivos innecesarios (`.gitignore`).
- [ ] Realizar commit descriptivo en la rama `DEV-AC`.
- [ ] Subir los cambios al remoto: `git push origin DEV-AC`.
