# Especificación de Diseño: Sistema de Citas Médicas "Vida Salud" (Ciclo 1)

**Proyecto:** Sistema de Información para la Gestión, Programación y Control de Citas Médicas  
**Institución:** UAGRM - Facultad de Ciencias de la Computación y Telecomunicaciones  
**Materia:** Ingeniería de Requisitos y Modelado - SC (Semestre 2-2026)  
**Grupo:** #25 (Alex Brayan Cerezo Choque, David Leonel Vincenty Mamani)  
**Docente:** Ing. Garzón Cuéllar Angélica  
**Versión:** 1.0.0 (Avance 50% - Ciclo 1)  

---

## 1. Alcance y Objetivos del Ciclo 1

El Ciclo 1 abarca exactamente el 50% funcional del proyecto, cerrando de forma completa e integrada los dos primeros módulos del sistema:

* **MÓDULO 1: Gestión de Seguridad y Usuarios**
  * `CU-01`: Gestionar Usuarios (Autenticación estricta, bloqueo por 5 intentos fallidos, recuperación de contraseña por email mediante Resend API, diálogo modal de confirmación al salir).
  * `CU-09`: Gestionar Roles y Permisos (Administrador, Recepcionista, Médico, Paciente).
  * `CU-10`: Consultar Bitácora de Auditoría (Registro automático de accesos y operaciones con IP, usuario y fecha).
  * `CU-11`: Gestionar Respaldos de Base de Datos (Exportación y visualización del estado de la base de datos).

* **MÓDULO 2: Gestión de Catálogos (Registros)**
  * `CU-04`: Gestionar Especialidades (CRUD con validación de unicidad de nombre).
  * `CU-03`: Gestionar Médicos (CRUD, matrícula profesional única, teléfono, estado activo/inactivo, asignación de especialidades).
  * `CU-12`: Gestionar Perfil Médico Base (Información profesional, especialidades y aranceles).
  * `CU-02`: Gestionar Pacientes (CRUD, CI único, teléfono de contacto, tipo de sangre, contacto de emergencia, bajas lógicas).

---

## 2. Arquitectura de Software

* **Patrón Arquitectónico:** MVC estricto con separación de responsabilidades.
  * **Modelos (`app/Models`):** `Persona`, `Usuario`, `Rol`, `Permiso`, `Bitacora`, `Paciente`, `Medico`, `Especialidad`, `MedicoEspecialidad`.
  * **Controladores (`app/Http/Controllers`):** `AuthController`, `UsuarioController`, `RolController`, `BitacoraController`, `BackupController`, `EspecialidadController`, `MedicoController`, `PacienteController`.
  * **Vistas (`resources/js/Pages`):** Componentes Vue 3 organizados por módulo (`Auth/`, `Usuarios/`, `Roles/`, `Bitacora/`, `Especialidades/`, `Medicos/`, `Pacientes/`).
* **Backend:** PHP 8.2+ con Laravel 11 / PDO PostgreSQL.
* **Frontend:** Vue 3 (Composition API / `<script setup>`), Inertia.js, Vite.
* **Estilos y UI:**
  * Paleta clínica sobria (Azul Naval `#0F2942`, Azul Salud `#2563EB`, Teal `#0D9488`, Slate `#0F172A`).
  * Alta densidad de datos y padding compacto (8px - 12px) para máxima eficiencia de pantalla.
  * Menú lateral (*Sidebar*) colapsable a barra de iconos.
  * Diálogos modales centrales para confirmaciones críticas y cierre de sesión (estilo C# / escritorio).
* **Base de Datos:** PostgreSQL 18 (Local y VPS).

---

## 3. Modelo de Datos Simplificado (PostgreSQL)

Tablas principales para el Ciclo 1:
1. `roles` (id, nombre, descripcion, estado)
2. `permisos` (id, nombre, descripcion)
3. `rol_permiso` (rol_id, permiso_id)
4. `personas` (id, ci, nombres, apellidos, fecha_nacimiento, genero, telefono, direccion, fecha_registro)
5. `usuarios` (id, persona_id, rol_id, username, email, password_hash, intentos_fallidos, bloqueado_hasta, estado)
6. `bitacora` (id, usuario_id, accion, modulo, detalle, ip_origen, fecha_hora)
7. `especialidades` (id, nombre, descripcion, estado)
8. `medicos` (id, persona_id, matricula_profesional, estado)
9. `medico_especialidad` (id, medico_id, especialidad_id, precio_consulta)
10. `pacientes` (id, persona_id, contacto_emergencia_nombre, contacto_emergencia_telefono, tipo_sangre, estado)

---

## 4. Lineamientos de Seguridad y Validaciones

1. **Hash de contraseñas:** `password_hash()` con algoritmo Bcrypt y verificación `password_verify()`.
2. **Protección contra ataques de fuerza bruta:** Bloqueo de cuenta por 15 minutos tras 5 intentos fallidos consecutivos.
3. **Recuperación de contraseña:** Envío de token de un solo uso por correo utilizando la API de Resend (`RESEND_API_KEY`).
4. **Bajas lógicas:** Ningún registro de paciente, médico, especialidad o usuario se borra físicamente de la base de datos si tiene datos asociados; se desactiva su `estado = FALSE`.
5. **Bitácora transversal:** Middleware / Observer que registra automáticamente cada acción sensible en `bitacora`.
