-- =============================================================================
-- SISTEMA DE INFORMACIÓN PARA EL CONTROL Y GESTIÓN DE CITAS MÉDICAS
-- CLÍNICA "VIDA SALUD" - SANTA CRUZ DE LA SIERRA, BOLIVIA
-- Materia: Sistemas de Información I (SI1) / Ingeniería de Software
-- Motor de Base de Datos: MySQL / MariaDB (Compatibilidad Motor InnoDB)
-- Estructura: DDL Completo, Población (>10 Registros/Tabla), Consultas, SP y Triggers
-- =============================================================================

CREATE DATABASE IF NOT EXISTS db_clinica_citas;
USE db_clinica_citas;

-- Configuración de Zona Horaria Oficial del Sistema (Bolivia GMT-4)
SET time_zone = '-04:00';
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- LIMPIEZA DE TABLAS PREVIAS (DROP TABLES SI EXISTEN)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS atenciones_clinicas;
DROP TABLE IF EXISTS citas;
DROP TABLE IF EXISTS ausencias_medicas;
DROP TABLE IF EXISTS horarios_atencion;
DROP TABLE IF EXISTS dias_semana;
DROP TABLE IF EXISTS estados_cita;
DROP TABLE IF EXISTS antecedentes_medicos;
DROP TABLE IF EXISTS pacientes;
DROP TABLE IF EXISTS medico_especialidad;
DROP TABLE IF EXISTS medicos;
DROP TABLE IF EXISTS especialidades;
DROP TABLE IF EXISTS bitacora;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS personas;
DROP TABLE IF EXISTS rol_permiso;
DROP TABLE IF EXISTS permisos;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- 1. DEFINICIÓN DEL ESQUEMA DDL (TABLAS, LLAVES PRIMARIAS Y FORÁNEAS)
-- =============================================================================

-- 1. Tabla ROLES (Catálogo de perfiles de usuario)
CREATE TABLE roles (
    id INT AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    estado TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla PERMISOS (Catálogo de acciones granulares del sistema RBAC)
CREATE TABLE permisos (
    id INT AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla ROL_PERMISO (Relación N:M entre Roles y Permisos)
CREATE TABLE rol_permiso (
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    PRIMARY KEY (rol_id, permiso_id),
    FOREIGN KEY (rol_id) REFERENCES roles(id) 
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) 
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla PERSONAS (Superclase para Identidad: Personal Médico, Recepción, Pacientes)
CREATE TABLE personas (
    id INT AUTO_INCREMENT NOT NULL,
    ci VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero CHAR(1) NOT NULL, -- 'M' = Masculino, 'F' = Femenino, 'O' = Otro
    telefono VARCHAR(20),
    direccion VARCHAR(200),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabla USUARIOS (Credenciales y Seguridad de Acceso al Sistema)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT NOT NULL,
    persona_id INT NOT NULL UNIQUE,
    rol_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta TIMESTAMP NULL DEFAULT NULL,
    estado TINYINT(1) DEFAULT 1,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (persona_id) REFERENCES personas(id) 
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (rol_id) REFERENCES roles(id) 
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabla BITACORA (Auditoría de Acciones y Eventos de Seguridad)
CREATE TABLE bitacora (
    id INT AUTO_INCREMENT NOT NULL,
    usuario_id INT NULL,
    accion VARCHAR(50) NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    detalle TEXT,
    ip_origen VARCHAR(45),
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) 
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabla ESPECIALIDADES (Catálogo de ramas de la medicina)
CREATE TABLE especialidades (
    id INT AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    estado TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabla MEDICOS (Subclase de Personal Clínico con Matrícula Profesional)
CREATE TABLE medicos (
    id INT AUTO_INCREMENT NOT NULL,
    persona_id INT NOT NULL UNIQUE,
    matricula_profesional VARCHAR(50) NOT NULL UNIQUE,
    estado TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (persona_id) REFERENCES personas(id) 
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tabla MEDICO_ESPECIALIDAD (Relación M:N Médicos - Especialidades con Arancel)
CREATE TABLE medico_especialidad (
    id INT AUTO_INCREMENT NOT NULL,
    medico_id INT NOT NULL,
    especialidad_id INT NOT NULL,
    precio_consulta DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_medico_especialidad (medico_id, especialidad_id),
    FOREIGN KEY (medico_id) REFERENCES medicos(id) 
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) 
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Tabla PACIENTES (Subclase de Personas que solicitan atención médica)
CREATE TABLE pacientes (
    id INT AUTO_INCREMENT NOT NULL,
    persona_id INT NOT NULL UNIQUE,
    contacto_emergencia_nombre VARCHAR(100),
    contacto_emergencia_telefono VARCHAR(20),
    tipo_sangre VARCHAR(5),
    estado TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (persona_id) REFERENCES personas(id) 
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Tabla ANTECEDENTES_MEDICOS (Historial patológico y alergias del paciente)
CREATE TABLE antecedentes_medicos (
    id INT AUTO_INCREMENT NOT NULL,
    paciente_id INT NOT NULL,
    condicion VARCHAR(150) NOT NULL,
    descripcion TEXT,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) 
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Tabla DIAS_SEMANA (Catálogo fijo de los días hábiles del calendario)
CREATE TABLE dias_semana (
    id SMALLINT NOT NULL,
    nombre VARCHAR(15) NOT NULL UNIQUE,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Tabla HORARIOS_ATENCION (Configuración de turnos regulares de atención)
CREATE TABLE horarios_atencion (
    id INT AUTO_INCREMENT NOT NULL,
    medico_especialidad_id INT NOT NULL,
    dia_id SMALLINT NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    intervalo_minutos INT DEFAULT 30,
    estado TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (medico_especialidad_id) REFERENCES medico_especialidad(id) 
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (dia_id) REFERENCES dias_semana(id) 
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Tabla AUSENCIAS_MEDICAS (Excepciones, permisos y vacaciones de los médicos)
CREATE TABLE ausencias_medicas (
    id INT AUTO_INCREMENT NOT NULL,
    medico_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    motivo VARCHAR(200) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (medico_id) REFERENCES medicos(id) 
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Tabla ESTADOS_CITA (Catálogo de estados del ciclo de vida de la cita)
CREATE TABLE estados_cita (
    id SMALLINT NOT NULL,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Tabla CITAS (Transacción central de reserva y programación de consulta)
CREATE TABLE citas (
    id INT AUTO_INCREMENT NOT NULL,
    codigo_reserva VARCHAR(20) NOT NULL UNIQUE,
    paciente_id INT NOT NULL,
    medico_especialidad_id INT NOT NULL,
    estado_id SMALLINT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    motivo_consulta TEXT,
    motivo_cancelacion TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) 
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (medico_especialidad_id) REFERENCES medico_especialidad(id) 
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (estado_id) REFERENCES estados_cita(id) 
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Tabla ATENCIONES_CLINICAS (Registro de diagnóstico y tratamiento médico)
CREATE TABLE atenciones_clinicas (
    id INT AUTO_INCREMENT NOT NULL,
    cita_id INT NOT NULL UNIQUE,
    diagnostico TEXT NOT NULL,
    tratamiento TEXT NOT NULL,
    notas_adicionales TEXT,
    fecha_atencion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (cita_id) REFERENCES citas(id) 
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Tabla PASSWORD_RESET_TOKENS (Soporte Laravel para recuperación)
CREATE TABLE password_reset_tokens (
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Tabla SESSIONS (Soporte Laravel para sesiones activas)
CREATE TABLE sessions (
    id VARCHAR(255) NOT NULL,
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    PRIMARY KEY (id),
    KEY sessions_user_id_index (user_id),
    KEY sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- 2. POBLACIÓN DE DATOS (INSERTS - MÍNIMO 10 REGISTROS POR TABLA)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 2.1. INSERTS EN DIAS_SEMANA (Catálogo Calendario Oficial: 7 Días Naturales)
-- -----------------------------------------------------------------------------
INSERT INTO dias_semana (id, nombre) VALUES
(1, 'Lunes'),
(2, 'Martes'),
(3, 'Miércoles'),
(4, 'Jueves'),
(5, 'Viernes'),
(6, 'Sábado'),
(7, 'Domingo');

-- -----------------------------------------------------------------------------
-- 2.2. INSERTS EN ESTADOS_CITA (Catálogo del Ciclo de Vida: 10 Estados)
-- -----------------------------------------------------------------------------
INSERT INTO estados_cita (id, nombre) VALUES
(1, 'Programada'),
(2, 'Confirmada'),
(3, 'Atendida'),
(4, 'Cancelada'),
(5, 'Inasistencia'),
(6, 'En Sala de Espera'),
(7, 'En Consulta'),
(8, 'Reprogramada'),
(9, 'Anulada por Emergencia'),
(10, 'Derivada a Interconsulta');

-- -----------------------------------------------------------------------------
-- 2.3. INSERTS EN ROLES (10 Registros de Perfiles Institucionales)
-- -----------------------------------------------------------------------------
INSERT INTO roles (id, nombre, descripcion, estado) VALUES
(1, 'Administrador', 'Control total de configuración, usuarios, seguridad y respaldos de base de datos.', 1),
(2, 'Recepcionista', 'Gestión operativa de pacientes, catálogo de médicos y programación de citas.', 1),
(3, 'Médico', 'Consulta de agenda diaria de citas, historial del paciente y registro de atención clínica.', 1),
(4, 'Paciente', 'Portal de autoservicio para consulta y reserva en línea de citas médicas.', 1),
(5, 'Director Médico', 'Supervisión de desempeño médico, turnos y estadísticas institucionales de atenciones.', 1),
(6, 'Enfermero de Triaje', 'Toma de signos vitales preliminares y cambio a sala de espera.', 1),
(7, 'Auditor Clínico', 'Revisión técnica de bitácora de auditoría y cumplimiento de protocolos de atención.', 1),
(8, 'Soporte Técnico', 'Mantenimiento del servidor, monitoreo de sesiones y optimización de base de datos.', 1),
(9, 'Coordinador de Agenda', 'Planificación de turnos de consulta externa y aprobación de ausencias médicas.', 1),
(10, 'Facturación y Aranceles', 'Gestión de precios referenciales de consultas médicas por especialidad.', 1);

-- -----------------------------------------------------------------------------
-- 2.4. INSERTS EN PERMISOS (16 Registros de Seguridad Granular)
-- -----------------------------------------------------------------------------
INSERT INTO permisos (id, nombre, descripcion) VALUES
(1, 'gestionar_usuarios', 'Crear, listar, editar y desactivar usuarios del sistema'),
(2, 'gestionar_roles', 'Ver y administrar perfiles de roles y asignación de permisos'),
(3, 'ver_bitacora', 'Consultar bitácora de auditoría de eventos del sistema'),
(4, 'gestionar_backups', 'Exportar y descargar respaldos SQL completos de la base de datos'),
(5, 'gestionar_especialidades', 'Administrar el catálogo de especialidades médicas de la clínica'),
(6, 'gestionar_medicos', 'Administrar registro, matrícula profesional y asignación de médicos'),
(7, 'gestionar_pacientes', 'Administrar ficha clínica, filiación y datos personales de pacientes'),
(8, 'gestionar_horarios', 'Configurar disponibilidad semanal y bloques de turnos médicos'),
(9, 'programar_citas', 'Registrar y reservar nuevas citas médicas para pacientes'),
(10, 'confirmar_citas', 'Confirmar asistencia telefónica o presencial de la cita médica'),
(11, 'cancelar_citas', 'Cancelar citas médicas programadas registrando el motivo'),
(12, 'reprogramar_citas', 'Cambiar fecha y hora de citas previamente agendadas'),
(13, 'registrar_atencion', 'Registrar diagnóstico, tratamiento clínico y recetas de la consulta'),
(14, 'ver_historial_clinico', 'Consultar historial médico previo y antecedentes del paciente'),
(15, 'gestionar_ausencias', 'Registrar licencias, permisos y vacaciones de los profesionales médicos'),
(16, 'ver_reportes', 'Generar reportes estadísticos y administrativos en PDF o Excel');

-- -----------------------------------------------------------------------------
-- 2.5. INSERTS EN ROL_PERMISO (Matriz de Autorización RBAC: >30 Asignaciones)
-- -----------------------------------------------------------------------------
-- Permisos del Administrador (Rol 1: Acceso Total)
INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8),
(1, 9), (1, 10), (1, 11), (1, 12), (1, 13), (1, 14), (1, 15), (1, 16);

-- Permisos del Recepcionista (Rol 2: Operativo Citas y Pacientes)
INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
(2, 5), (2, 6), (2, 7), (2, 8), (2, 9), (2, 10), (2, 11), (2, 12), (2, 14), (2, 16);

-- Permisos del Médico (Rol 3: Consulta y Atención Clínica)
INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
(3, 10), (3, 13), (3, 14), (3, 15);

-- Permisos del Paciente (Rol 4: Autoservicio de Citas)
INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
(4, 9), (4, 11), (4, 14);

-- Permisos del Director Médico (Rol 5: Supervisión y Reportes)
INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
(5, 3), (5, 5), (5, 6), (5, 7), (5, 8), (5, 14), (5, 15), (5, 16);

-- -----------------------------------------------------------------------------
-- 2.6. INSERTS EN PERSONAS (25 Registros: Personal, Médicos y Pacientes)
-- -----------------------------------------------------------------------------
INSERT INTO personas (id, ci, nombres, apellidos, fecha_nacimiento, genero, telefono, direccion) VALUES
-- Personal Administrativo y Recepción
(1, '10000001', 'Alex Brayan', 'Cerezo Choque', '1995-04-12', 'M', '71000001', 'Av. Busch y 2do Anillo, Clínica Vida Salud'),
(2, '20000001', 'Andrea Mariana', 'Morales Vaca', '1998-08-20', 'F', '72000002', 'Calle Sucre #240, Zona Central'),
(3, '20000002', 'Fernando David', 'Siles Vincenty', '1996-11-15', 'M', '72000003', 'Av. San Martín C. 5, Equipetrol'),
(4, '20000003', 'Paola Jimena', 'Ríos Montaño', '1997-03-25', 'F', '72000004', 'Barrio Sirari C. Las Palmas #78'),
(5, '20000004', 'Gonzalo Javier', 'Vargas Escalante', '1982-09-10', 'M', '71000005', 'Av. Monseñor Rivero #310'),

-- Médicos Titulares (10 Profesionales)
(6, '30000001', 'Carlos Alberto', 'Rodríguez Morales', '1980-03-14', 'M', '73000010', 'C. Beni #145, Santa Cruz'),
(7, '30000002', 'Mariana Elena', 'Flores Justiniano', '1985-07-22', 'F', '73000020', 'Av. Cristo Redentor #320'),
(8, '30000003', 'Jorge Luis', 'Mendoza Mercado', '1979-10-05', 'M', '73000030', 'Barrio Sirari C. Los Pinos #45'),
(9, '30000004', 'Valeria Sofía', 'Pinto Salvatierra', '1988-12-19', 'F', '73000040', 'C. Ballivián #210, Casco Viejo'),
(10, '30000005', 'Roberto Carlos', 'Suárez Céspedes', '1983-05-30', 'M', '73000050', 'Av. Banzer Km 4'),
(11, '30000006', 'Andrea Patricia', 'Campero Torrico', '1987-09-11', 'F', '73000060', 'Barrio Equipetrol C. 7 #88'),
(12, '30000007', 'Sergio Andrés', 'Claure Guzmán', '1976-02-18', 'M', '73000070', 'C. Bolívar #550'),
(13, '30000008', 'Álvaro Mauricio', 'Gutiérrez Siles', '1981-06-25', 'M', '73000080', 'Av. Irala #140'),
(14, '30000009', 'Gabriela Inés', 'Ortiz Montero', '1986-04-03', 'F', '73000090', 'C. Warnes #90'),
(15, '30000010', 'Marcelo David', 'Paz Quiroga', '1984-11-28', 'M', '73000100', 'Av. Las Américas #600'),

-- Pacientes Afiliados (10 Pacientes)
(16, '40000001', 'Lucía Camila', 'Vargas Méndez', '1993-02-14', 'F', '74000001', 'C. Arenales #120'),
(17, '40000002', 'Mateo Nicolás', 'Aguilera Roca', '1999-05-18', 'M', '74000002', 'Av. Mutualista C. 12 #45'),
(18, '40000003', 'Sofía Nicole', 'Hurtado Saucedo', '2001-08-30', 'F', '74000003', 'C. Charcas #340'),
(19, '40000004', 'Daniel Alejandro', 'Cuéllar Soliz', '1989-11-09', 'M', '74000004', 'Barrio Urbarí C. 3 #12'),
(20, '40000005', 'Camila Andrea', 'Ribera Justiniano', '1994-04-25', 'F', '74000005', 'C. Velasco #80'),
(21, '40000006', 'Andrés Felipe', 'Arteaga Villagómez', '1978-01-14', 'M', '74000006', 'Plan 3000, Barrio Los Lotes C. 4'),
(22, '40000007', 'Valentina Belén', 'Peinado Antelo', '2003-10-12', 'F', '74000007', 'Av. Santos Dumont C. 8'),
(23, '40000008', 'Lucas Gabriel', 'Melgar Vaca', '1997-06-03', 'M', '74000008', 'Villa 1ro de Mayo C. 5 #110'),
(24, '40000009', 'Isabella Rocío', 'Landívar Chávez', '1992-12-01', 'F', '74000009', 'Av. Cañoto #230'),
(25, '40000010', 'Sebastián Ignacio', 'Rojas Franco', '1986-07-17', 'M', '74000010', 'C. Seoane #410, Zona Central');

-- -----------------------------------------------------------------------------
-- 2.7. INSERTS EN USUARIOS (15 Cuentas con Acceso al Sistema)
-- -----------------------------------------------------------------------------
INSERT INTO usuarios (id, persona_id, rol_id, username, email, password, intentos_fallidos, estado) VALUES
(1, 1, 1, 'admin', 'admin@vidasalud.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1),
(2, 2, 2, 'recepcion', 'recepcion@vidasalud.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1),
(3, 3, 2, 'recepcion2', 'recepcion2@vidasalud.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1),
(4, 5, 5, 'director.medico', 'director@vidasalud.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1),
(5, 6, 3, 'dr.rodriguez', 'carlos.rodriguez@vidasalud.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1),
(6, 7, 3, 'dra.flores', 'mariana.flores@vidasalud.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1),
(7, 8, 3, 'dr.mendoza', 'jorge.mendoza@vidasalud.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1),
(8, 9, 3, 'dra.pinto', 'valeria.pinto@vidasalud.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1),
(9, 10, 3, 'dr.suarez', 'roberto.suarez@vidasalud.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1),
(10, 11, 3, 'dra.campero', 'andrea.campero@vidasalud.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1),
(11, 12, 3, 'dr.claure', 'sergio.claure@vidasalud.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1),
(12, 13, 3, 'dr.gutierrez', 'alvaro.gutierrez@vidasalud.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1),
(13, 16, 4, 'paciente.lucia', 'lucia.vargas@correo.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1),
(14, 17, 4, 'paciente.mateo', 'mateo.aguilera@correo.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1),
(15, 18, 4, 'paciente.sofia', 'sofia.hurtado@correo.com', '$2y$12$e80yvYI79hC1N4BwL41w0uRkl1WqJ5N2J0e7f7bY5zZ9n3n0vN6Ke', 0, 1);

-- -----------------------------------------------------------------------------
-- 2.8. INSERTS EN ESPECIALIDADES (12 Registros Clínicos Especializados)
-- -----------------------------------------------------------------------------
INSERT INTO especialidades (id, nombre, descripcion, estado) VALUES
(1, 'Medicina General', 'Atención médica primaria integral, diagnóstico preventivo y derivación clínica.', 1),
(2, 'Pediatría', 'Atención y cuidado integral del desarrollo del recién nacido, niños y adolescentes.', 1),
(3, 'Ginecología y Obstetricia', 'Salud ginecológica integral, control prenatal, ecografías y salud reproductiva.', 1),
(4, 'Odontología', 'Salud bucodental, profilaxis, rehabilitación oral, estética y cirugías dentales.', 1),
(5, 'Cardiología', 'Prevención, diagnóstico y tratamiento de enfermedades cardíacas e hipertensión.', 1),
(6, 'Traumatología y Ortopedia', 'Diagnóstico y rehabilitación de fracturas, luxaciones y patologías óseas.', 1),
(7, 'Dermatología', 'Tratamiento clínico y estético de patologías en piel, cabello y uñas.', 1),
(8, 'Oftalmología', 'Salud ocular, agudeza visual, fondo de ojo y tratamiento oftalmológico integral.', 1),
(9, 'Neurología', 'Evaluación de patologías del sistema nervioso central, cefaleas y epilepsia.', 1),
(10, 'Otorrinolaringología', 'Patologías de oídos, fosas nasales, senos paranasales, faringe y laringe.', 1),
(11, 'Urología', 'Tratamiento del tracto urinario en hombres y mujeres, y sistema reproductor masculino.', 1),
(12, 'Endocrinología', 'Manejo de trastornos hormonales, tiroides, diabetes y metabolismo.', 1);

-- -----------------------------------------------------------------------------
-- 2.9. INSERTS EN MEDICOS (10 Profesionales con Matrícula Médica Oficial)
-- -----------------------------------------------------------------------------
INSERT INTO medicos (id, persona_id, matricula_profesional, estado) VALUES
(1, 6, 'MP-1001-SCZ', 1), -- Carlos Alberto Rodríguez Morales
(2, 7, 'MP-1002-SCZ', 1), -- Mariana Elena Flores Justiniano
(3, 8, 'MP-1003-SCZ', 1), -- Jorge Luis Mendoza Mercado
(4, 9, 'MP-1004-SCZ', 1), -- Valeria Sofía Pinto Salvatierra
(5, 10, 'MP-1005-SCZ', 1), -- Roberto Carlos Suárez Céspedes
(6, 11, 'MP-1006-SCZ', 1), -- Andrea Patricia Campero Torrico
(7, 12, 'MP-1007-SCZ', 1), -- Sergio Andrés Claure Guzmán
(8, 13, 'MP-1008-SCZ', 1), -- Álvaro Mauricio Gutiérrez Siles
(9, 14, 'MP-1009-SCZ', 1), -- Gabriela Inés Ortiz Montero
(10, 15, 'MP-1010-SCZ', 1); -- Marcelo David Paz Quiroga

-- -----------------------------------------------------------------------------
-- 2.10. INSERTS EN MEDICO_ESPECIALIDAD (15 Asignaciones de Especialidad y Arancel)
-- -----------------------------------------------------------------------------
INSERT INTO medico_especialidad (id, medico_id, especialidad_id, precio_consulta) VALUES
(1, 1, 5, 180.00), -- Dr. Rodríguez: Cardiología
(2, 1, 1, 120.00), -- Dr. Rodríguez: Medicina General
(3, 2, 2, 150.00), -- Dra. Flores: Pediatría
(4, 3, 6, 200.00), -- Dr. Mendoza: Traumatología y Ortopedia
(5, 4, 3, 160.00), -- Dra. Pinto: Ginecología y Obstetricia
(6, 5, 4, 130.00), -- Dr. Suárez: Odontología
(7, 6, 7, 170.00), -- Dra. Campero: Dermatología
(8, 7, 8, 160.00), -- Dr. Claure: Oftalmología
(9, 8, 9, 220.00), -- Dr. Gutiérrez: Neurología
(10, 9, 10, 150.00), -- Dra. Ortiz: Otorrinolaringología
(11, 10, 1, 110.00), -- Dr. Paz: Medicina General
(12, 3, 1, 130.00), -- Dr. Mendoza: Medicina General (Guardias)
(13, 8, 1, 140.00), -- Dr. Gutiérrez: Medicina General
(14, 5, 1, 110.00), -- Dr. Suárez: Medicina General
(15, 2, 1, 120.00); -- Dra. Flores: Medicina General

-- -----------------------------------------------------------------------------
-- 2.11. INSERTS EN PACIENTES (10 Registros con Ficha Clínica y Emergencia)
-- -----------------------------------------------------------------------------
INSERT INTO pacientes (id, persona_id, contacto_emergencia_nombre, contacto_emergencia_telefono, tipo_sangre, estado) VALUES
(1, 16, 'Mario Vargas Méndez', '71092837', 'O+', 1),
(2, 17, 'Claudia Roca de Aguilera', '72103948', 'A+', 1),
(3, 18, 'Juan Carlos Hurtado', '73192038', 'B+', 1),
(4, 19, 'Patricia Soliz Cuéllar', '74102938', 'O-', 1),
(5, 20, 'Andrés Ribera Melgar', '75192039', 'AB+', 1),
(6, 21, 'Elena Villagómez de Arteaga', '76102938', 'A-', 1),
(7, 22, 'Rodrigo Peinado Suárez', '77102948', 'O+', 1),
(8, 23, 'Martha Vaca de Melgar', '78192039', 'B-', 1),
(9, 24, 'Sergio Landívar Ortiz', '79102948', 'O+', 1),
(10, 25, 'Verónica Franco de Rojas', '70102938', 'AB-', 1);

-- -----------------------------------------------------------------------------
-- 2.12. INSERTS EN ANTECEDENTES_MEDICOS (12 Registros Clínicos Detallados)
-- -----------------------------------------------------------------------------
INSERT INTO antecedentes_medicos (id, paciente_id, condicion, descripcion) VALUES
(1, 1, 'Hipertensión Arterial', 'Diagnosticada en 2021. Tratamiento activo con Enalapril 10mg diario. Sin alergias medicamentosas.'),
(2, 2, 'Alergia Medicamentosa', 'Alergia severa a Penicilina y derivados betalactámicos. Reacción de urticaria generalizada.'),
(3, 3, 'Rinitis Alérgica', 'Rinitis estacional controlada con antihistamínicos. Cuadros de broncoespasmo leve en la infancia.'),
(4, 4, 'Cirugía Previa', 'Apendicectomía laparoscópica realizada en 2022 sin complicaciones posteriores.'),
(5, 5, 'Hipotiroidismo Primario', 'Control con Levotiroxina sódica 50 mcg por la mañana en ayunas.'),
(6, 6, 'Diabetes Mellitus Tipo 2', 'Diagnosticada en 2019. Tratada con Metformina 850mg dos veces al día y dieta hipocalórica.'),
(7, 7, 'Gastritis Crónica', 'Gastritis superficial asociada a estrés. Tratamiento esporádico con Omeprazol 20mg.'),
(8, 8, 'Traumatismo / Fractura', 'Fractura diafisaria de radio izquierdo en 2021. Material de osteosíntesis retirado.'),
(9, 9, 'Asma Bronquial Leve', 'Uso intermitente de Salbutamol en aerosol ante cambios de clima.'),
(10, 10, 'Cefaleas Tensionales', 'Episodios recurrentes de cefalea occipital y cervicalgia postural mecánica.'),
(11, 1, 'Dislipidemia Mixta', 'Colesterol total elevado en control laboratorial del último semestre.'),
(12, 6, 'Hipertensión Arterial', 'Comorbilidad asociada a diabetes. En control con Losartán 50mg día.');

-- -----------------------------------------------------------------------------
-- 2.13. INSERTS EN HORARIOS_ATENCION (15 Turnos Médicos Semanales)
-- -----------------------------------------------------------------------------
INSERT INTO horarios_atencion (id, medico_especialidad_id, dia_id, hora_inicio, hora_fin, intervalo_minutos, estado) VALUES
(1, 1, 1, '08:00:00', '12:00:00', 30, 1), -- Dr. Rodríguez (Cardio) - Lunes Mañana
(2, 1, 3, '08:00:00', '12:00:00', 30, 1), -- Dr. Rodríguez (Cardio) - Miércoles Mañana
(3, 1, 5, '08:00:00', '12:00:00', 30, 1), -- Dr. Rodríguez (Cardio) - Viernes Mañana
(4, 2, 2, '14:00:00', '18:00:00', 30, 1), -- Dr. Rodríguez (General) - Martes Tarde
(5, 3, 1, '08:30:00', '12:30:00', 30, 1), -- Dra. Flores (Pediatría) - Lunes Mañana
(6, 3, 2, '08:30:00', '12:30:00', 30, 1), -- Dra. Flores (Pediatría) - Martes Mañana
(7, 3, 4, '08:30:00', '12:30:00', 30, 1), -- Dra. Flores (Pediatría) - Jueves Mañana
(8, 4, 2, '14:00:00', '19:00:00', 30, 1), -- Dr. Mendoza (Trauma) - Martes Tarde
(9, 4, 4, '14:00:00', '19:00:00', 30, 1), -- Dr. Mendoza (Trauma) - Jueves Tarde
(10, 5, 1, '15:00:00', '19:00:00', 30, 1), -- Dra. Pinto (Gineco) - Lunes Tarde
(11, 5, 3, '15:00:00', '19:00:00', 30, 1), -- Dra. Pinto (Gineco) - Miércoles Tarde
(12, 6, 2, '08:00:00', '12:00:00', 30, 1), -- Dr. Suárez (Odonto) - Martes Mañana
(13, 7, 3, '09:00:00', '13:00:00', 30, 1), -- Dra. Campero (Derma) - Miércoles Mañana
(14, 8, 4, '14:00:00', '18:00:00', 30, 1), -- Dr. Claure (Oftalmo) - Jueves Tarde
(15, 9, 5, '08:00:00', '12:00:00', 30, 1); -- Dr. Gutiérrez (Neuro) - Viernes Mañana

-- -----------------------------------------------------------------------------
-- 2.14. INSERTS EN AUSENCIAS_MEDICAS (10 Registros de Licencias y Permisos)
-- -----------------------------------------------------------------------------
INSERT INTO ausencias_medicas (id, medico_id, fecha_inicio, fecha_fin, motivo) VALUES
(1, 1, '2026-05-10', '2026-05-15', 'Asistencia al Congreso Internacional de Cardiología en Buenos Aires'),
(2, 2, '2026-06-01', '2026-06-07', 'Vacaciones semestrales programadas de medio año'),
(3, 3, '2026-04-18', '2026-04-20', 'Participación como ponente en Jornadas Quirúrgicas de Traumatología'),
(4, 4, '2026-07-15', '2026-07-22', 'Licencia por capacitación en ecografía fetal avanzada'),
(5, 5, '2026-05-02', '2026-05-04', 'Permiso particular por trámite gremial en Colegio de Odontólogos'),
(6, 6, '2026-08-10', '2026-08-14', 'Simposio Panamericano de Dermatología Clínica'),
(7, 7, '2026-09-01', '2026-09-05', 'Descanso pedagógico y actividades académicas universitarias'),
(8, 8, '2026-06-20', '2026-06-24', 'Congreso Latinoamericano de Neurología Clínica'),
(9, 9, '2026-04-28', '2026-04-30', 'Licencia médica por intervención odontológica menor'),
(10, 10, '2026-07-01', '2026-07-06', 'Vacaciones reglamentarias de invierno');

-- -----------------------------------------------------------------------------
-- 2.15. INSERTS EN CITAS (15 Citas Médicas en Distintos Estados del Ciclo)
-- -----------------------------------------------------------------------------
INSERT INTO citas (id, codigo_reserva, paciente_id, medico_especialidad_id, estado_id, fecha, hora_inicio, hora_fin, motivo_consulta, motivo_cancelacion, fecha_registro) VALUES
-- Citas Pasadas Atendidas (Estados = 3)
(1, 'CIT-20260401-0001', 1, 1, 3, '2026-04-06', '08:00:00', '08:30:00', 'Control periódico de presión arterial y fatiga ocasional.', NULL, '2026-04-01 09:15:00'),
(2, 'CIT-20260401-0002', 2, 3, 3, '2026-04-06', '08:30:00', '09:00:00', 'Evaluación de cuadro respiratorio y tos persistente en niño.', NULL, '2026-04-01 10:20:00'),
(3, 'CIT-20260402-0003', 3, 5, 3, '2026-04-06', '15:00:00', '15:30:00', 'Control ginecológico anual y ecografía pélvica de rutina.', NULL, '2026-04-02 11:00:00'),
(4, 'CIT-20260402-0004', 4, 4, 3, '2026-04-07', '14:00:00', '14:30:00', 'Dolor agudo en rodilla derecha tras práctica deportiva de fútbol.', NULL, '2026-04-02 14:10:00'),
(5, 'CIT-20260403-0005', 5, 7, 3, '2026-04-08', '09:00:00', '09:30:00', 'Aparición de manchas y prurito intenso en antebrazo derecho.', NULL, '2026-04-03 08:45:00'),
(6, 'CIT-20260403-0006', 6, 1, 3, '2026-04-08', '08:00:00', '08:30:00', 'Chequeo de palpitaciones nocturnas y ajuste de medicación.', NULL, '2026-04-03 16:30:00'),
(7, 'CIT-20260404-0007', 7, 6, 3, '2026-04-07', '08:00:00', '08:30:00', 'Limpieza dental con ultrasonido y dolor en molar 46.', NULL, '2026-04-04 10:00:00'),
(8, 'CIT-20260405-0008', 8, 8, 3, '2026-04-09', '14:00:00', '14:30:00', 'Disminución progresiva de agudeza visual y visión borrosa.', NULL, '2026-04-05 12:15:00'),
(9, 'CIT-20260405-0009', 9, 9, 3, '2026-04-10', '08:00:00', '08:30:00', 'Cefaleas tensionales intensas con fotosensibilidad frecuente.', NULL, '2026-04-05 15:40:00'),
(10, 'CIT-20260406-0010', 10, 2, 3, '2026-04-07', '14:00:00', '14:30:00', 'Malestar general, fiebre moderada y odinofagia.', NULL, '2026-04-06 08:30:00'),

-- Citas Confirmadas y Programadas (Estados = 2 y 1)
(11, 'CIT-20260410-0011', 1, 1, 2, '2026-04-27', '08:00:00', '08:30:00', 'Revisión de resultados de Holter de 24 horas.', NULL, '2026-04-10 11:20:00'),
(12, 'CIT-20260411-0012', 2, 3, 2, '2026-04-27', '08:30:00', '09:00:00', 'Control de crecimiento y desarrollo del infante.', NULL, '2026-04-11 09:05:00'),
(13, 'CIT-20260412-0013', 3, 4, 1, '2026-04-28', '14:00:00', '14:30:00', 'Control evolutivo de esguince de tobillo grado 1.', NULL, '2026-04-12 16:00:00'),

-- Citas Canceladas e Inasistencias (Estados = 4 y 5)
(14, 'CIT-20260413-0014', 4, 5, 4, '2026-04-29', '15:00:00', '15:30:00', 'Revisión prenatal mensual.', 'Paciente reportó viaje imprevisto de trabajo fuera de la ciudad.', '2026-04-13 14:00:00'),
(15, 'CIT-20260414-0015', 5, 7, 5, '2026-04-22', '09:00:00', '09:30:00', 'Seguimiento de respuesta a tratamiento dermatológico.', 'Paciente no se presentó a su cita médica programada.', '2026-04-14 10:30:00');

-- -----------------------------------------------------------------------------
-- 2.16. INSERTS EN ATENCIONES_CLINICAS (10 Atenciones Médicas Concluidas)
-- -----------------------------------------------------------------------------
INSERT INTO atenciones_clinicas (id, cita_id, diagnostico, tratamiento, notas_adicionales, fecha_atencion) VALUES
(1, 1, 'Hipertensión arterial estadio 1 controlada. Ligera taquicardia sinusal.', 'Continuar con Enalapril 10mg en la mañana. Se añade dieta baja en sodio y caminata 30 min diarios.', 'Solicitado electrocardiograma de control y perfil lipídico en 3 semanas.', '2026-04-06 08:28:00'),
(2, 2, 'Faringoamigdalitis aguda no bacteriana y rinorrea serosa.', 'Ibuprofeno suspensión 100mg/5ml cada 8 horas por 3 días. Hidratación constante y lavados nasales.', 'Madre instruida sobre signos de alarma respiratoria (tiraje o fiebre persistente).', '2026-04-06 08:55:00'),
(3, 3, 'Control ginecológico dentro de parámetros normales. Ecografía sin hallazgos patológicos.', 'Continuar con ácido fólico y multivitamínico general. Próximo control anual rutinario.', 'Se realizó Papanicolaou de pesquisa preventiva.', '2026-04-06 15:25:00'),
(4, 4, 'Esguince de ligamento colateral medial de rodilla derecha Grado 1.', 'Reposo articular relativo, crioterapia local por 15 min 3 veces al día, Ketorolaco 10mg cada 8 horas por 4 días.', 'Uso de rodillera elástica con refuerzo lateral. Cita de control en 15 días.', '2026-04-07 14:26:00'),
(5, 5, 'Dermatitis por contacto alérgica en antebrazo.', 'Hidrocortisona crema al 1% aplicar cada 12 horas por 5 días. Cetirizina 10mg noche por 7 días.', 'Suspender uso de jabones perfumados y detergentes industriales sin guantes.', '2026-04-08 09:27:00'),
(6, 6, 'Ansiedad somatizada con extrasístoles supraventriculares aisladas.', 'Higiene del sueño, suprimir café y bebidas energizantes. Propranolol 10mg condicional a palpitaciones.', 'Derivado a interconsulta con Psicología médica de la clínica.', '2026-04-08 08:29:00'),
(7, 7, 'Caries oclusal profunda en pieza 46 sin compromiso pulpar evidente.', 'Profilaxis dental general completada. Restauración con resina compuesta fotocurable en pieza 46.', 'Recomendada técnica de cepillado y uso diario de hilo dental.', '2026-04-07 08:35:00'),
(8, 8, 'Vicio de refracción: Astigmatismo miópico bilateral moderado.', 'Prescripción de lentes correctivos oftálmicos de uso permanente con filtro azul.', 'Control oftalmológico sugerido en 12 meses.', '2026-04-09 14:28:00'),
(9, 9, 'Cefalea de origen tensional crónica por estrés laboral.', 'Paracetamol 500mg + Cafeína 65mg cada 8 horas SOS. Ejercicios de elongación cervical y fisioterapia.', 'Buena respuesta preliminar a técnicas de relajación.', '2026-04-10 08:29:00'),
(10, 10, 'Faringitis viral estacional leve sin sobreinfección.', 'Paracetamol 500mg cada 8 horas por 3 días si hay dolor. Abundante líquido y gargarismos con solución salina.', 'Evolución favorable esperada en 72 horas.', '2026-04-07 14:25:00');

-- -----------------------------------------------------------------------------
-- 2.17. INSERTS EN BITACORA (15 Eventos de Auditoría del Sistema)
-- -----------------------------------------------------------------------------
INSERT INTO bitacora (id, usuario_id, accion, modulo, detalle, ip_origen, fecha_hora) VALUES
(1, 1, 'INICIALIZACION_SISTEMA', 'Seguridad', 'Configuración de catálogo maestro de roles, permisos y especialidades clínicas.', '127.0.0.1', '2026-04-01 08:00:00'),
(2, 1, 'REGISTRO_MEDICO', 'Médicos', 'Alta de Dr. Carlos Alberto Rodríguez Morales (Mat: MP-1001-SCZ) en Cardiología.', '127.0.0.1', '2026-04-01 08:15:00'),
(3, 1, 'REGISTRO_MEDICO', 'Médicos', 'Alta de Dra. Mariana Elena Flores Justiniano (Mat: MP-1002-SCZ) en Pediatría.', '127.0.0.1', '2026-04-01 08:20:00'),
(4, 2, 'REGISTRO_PACIENTE', 'Pacientes', 'Filiación de paciente Lucía Camila Vargas Méndez (CI: 40000001).', '192.168.1.15', '2026-04-01 09:00:00'),
(5, 2, 'PROGRAMACION_CITA', 'Citas', 'Reserva de cita CIT-20260401-0001 para paciente Lucía Vargas con Dr. Rodríguez.', '192.168.1.15', '2026-04-01 09:15:00'),
(6, 2, 'CONFIRMACION_CITA', 'Citas', 'Confirmación telefónica de cita CIT-20260401-0001.', '192.168.1.15', '2026-04-05 16:00:00'),
(7, 5, 'REGISTRO_ATENCION', 'Atención Médica', 'Dr. Rodríguez concluye atención clínica y prescripción de cita CIT-20260401-0001.', '192.168.1.50', '2026-04-06 08:30:00'),
(8, 2, 'PROGRAMACION_CITA', 'Citas', 'Reserva de cita CIT-20260401-0002 para paciente Mateo Aguilera con Dra. Flores.', '192.168.1.15', '2026-04-01 10:20:00'),
(9, 6, 'REGISTRO_ATENCION', 'Atención Médica', 'Dra. Flores registra diagnóstico y medicación pediátrica de cita CIT-20260401-0002.', '192.168.1.51', '2026-04-06 08:58:00'),
(10, 2, 'CANCELACION_CITA', 'Citas', 'Cancelación de cita CIT-20260413-0014 por viaje imprevisto reportado por paciente.', '192.168.1.15', '2026-04-13 14:05:00'),
(11, 1, 'REGISTRO_AUSENCIA', 'Médicos', 'Registro de licencia médica para Dr. Carlos Rodríguez por asistencia a congreso.', '127.0.0.1', '2026-04-14 11:00:00'),
(12, 1, 'INICIO_SESION', 'Seguridad', 'Inicio de sesión exitoso del usuario admin desde terminal local.', '127.0.0.1', '2026-04-15 07:45:00'),
(13, 2, 'INICIO_SESION', 'Seguridad', 'Inicio de sesión exitoso de la recepcionista Morales Andrea.', '192.168.1.15', '2026-04-15 08:00:00'),
(14, 5, 'INICIO_SESION', 'Seguridad', 'Inicio de sesión de médico especialista dr.rodriguez para consultar agenda.', '192.168.1.50', '2026-04-15 08:10:00'),
(15, 2, 'REGISTRO_PACIENTE', 'Pacientes', 'Filiación clínica de paciente Sebastián Ignacio Rojas Franco (CI: 40000010).', '192.168.1.15', '2026-04-15 10:30:00');


-- =============================================================================
-- 3. CONSULTAS SQL (SIMPLES, MÚLTIPLES Y SUBCONSULTAS)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 3.1. CONSULTAS SIMPLES (10 Consultas de Filtrado, Agrupación y Ordenamiento)
-- -----------------------------------------------------------------------------

-- 1. Listar todos los médicos activos en el sistema clínico
SELECT id, persona_id, matricula_profesional, estado 
FROM medicos 
WHERE estado = 1;

-- 2. Obtener los pacientes cuyo tipo de sangre sea 'O+' (donante universal compatible)
SELECT id, persona_id, contacto_emergencia_nombre, contacto_emergencia_telefono, tipo_sangre 
FROM pacientes 
WHERE tipo_sangre = 'O+';

-- 3. Filtrar especialidades que tengan la palabra 'integral' en su descripción
SELECT id, nombre, descripcion 
FROM especialidades 
WHERE descripcion LIKE '%integral%';

-- 4. Contar la cantidad total de citas agrupadas por su estado actual
SELECT estado_id, COUNT(*) AS total_citas 
FROM citas 
GROUP BY estado_id;

-- 5. Calcular el promedio, precio mínimo y precio máximo de las consultas médicas
SELECT 
    AVG(precio_consulta) AS precio_promedio,
    MIN(precio_consulta) AS precio_minimo,
    MAX(precio_consulta) AS precio_maximo
FROM medico_especialidad;

-- 6. Obtener las citas programadas para el día 06 de Abril de 2026
SELECT id, codigo_reserva, paciente_id, fecha, hora_inicio, hora_fin, estado_id 
FROM citas 
WHERE fecha = '2026-04-06';

-- 7. Listar los antecedentes médicos que contengan la palabra 'Hipertensión' o 'Alergia'
SELECT id, paciente_id, condicion, descripcion 
FROM antecedentes_medicos 
WHERE condicion LIKE '%Hipertensión%' OR condicion LIKE '%Alergia%';

-- 8. Listar usuarios con estado activo ordenados alfabéticamente por username
SELECT id, username, email, rol_id, estado 
FROM usuarios 
WHERE estado = 1 
ORDER BY username ASC;

-- 9. Mostrar los últimos 5 eventos registrados en la bitácora de auditoría
SELECT id, accion, modulo, detalle, ip_origen, fecha_hora 
FROM bitacora 
ORDER BY fecha_hora DESC 
LIMIT 5;

-- 10. Listar ausencias médicas con duración mayor o igual a 5 días
SELECT id, medico_id, fecha_inicio, fecha_fin, DATEDIFF(fecha_fin, fecha_inicio) + 1 AS dias_ausente, motivo 
FROM ausencias_medicas 
WHERE DATEDIFF(fecha_fin, fecha_inicio) + 1 >= 5;


-- -----------------------------------------------------------------------------
-- 3.2. CONSULTAS MÚLTIPLES (10 Consultas con JOINs entre Tablas Relacionadas)
-- -----------------------------------------------------------------------------

-- 1. Agenda del día completa: Paciente, Médico, Especialidad, Horario y Estado
SELECT 
    C.codigo_reserva,
    C.fecha,
    C.hora_inicio,
    CONCAT(P_Pac.nombres, ' ', P_Pac.apellidos) AS paciente,
    CONCAT(P_Med.nombres, ' ', P_Med.apellidos) AS medico,
    E.nombre AS especialidad,
    EC.nombre AS estado_cita
FROM citas C
JOIN pacientes PAC ON C.paciente_id = PAC.id
JOIN personas P_Pac ON PAC.persona_id = P_Pac.id
JOIN medico_especialidad ME ON C.medico_especialidad_id = ME.id
JOIN especialidades E ON ME.especialidad_id = E.id
JOIN medicos M ON ME.medico_id = M.id
JOIN personas P_Med ON M.persona_id = P_Med.id
JOIN estados_cita EC ON C.estado_id = EC.id
ORDER BY C.fecha DESC, C.hora_inicio ASC;

-- 2. Médicos y sus Especialidades con Arancel de Consulta asignado
SELECT 
    CONCAT(P.nombres, ' ', P.apellidos) AS medico,
    M.matricula_profesional,
    E.nombre AS especialidad,
    ME.precio_consulta
FROM medico_especialidad ME
JOIN medicos M ON ME.medico_id = M.id
JOIN personas P ON M.persona_id = P.id
JOIN especialidades E ON ME.especialidad_id = E.id
ORDER BY P.apellidos ASC;

-- 3. Historial de Citas Atendidas con Diagnóstico y Tratamiento Prescrito
SELECT 
    C.codigo_reserva,
    C.fecha,
    CONCAT(P.nombres, ' ', P.apellidos) AS paciente,
    A.diagnostico,
    A.tratamiento,
    A.fecha_atencion
FROM atenciones_clinicas A
JOIN citas C ON A.cita_id = C.id
JOIN pacientes PAC ON C.paciente_id = PAC.id
JOIN personas P ON PAC.persona_id = P.id
ORDER BY A.fecha_atencion DESC;

-- 4. Horarios de atención configurados por médico, día de la semana y rango de horas
SELECT 
    CONCAT(P.nombres, ' ', P.apellidos) AS medico,
    E.nombre AS especialidad,
    D.nombre AS dia,
    H.hora_inicio,
    H.hora_fin,
    H.intervalo_minutos
FROM horarios_atencion H
JOIN dias_semana D ON H.dia_id = D.id
JOIN medico_especialidad ME ON H.medico_especialidad_id = ME.id
JOIN especialidades E ON ME.especialidad_id = E.id
JOIN medicos M ON ME.medico_id = M.id
JOIN personas P ON M.persona_id = P.id
ORDER BY D.id ASC, H.hora_inicio ASC;

-- 5. Pacientes con sus Antecedentes Clínicos y Ficha de Emergencia
SELECT 
    P.ci,
    CONCAT(P.nombres, ' ', P.apellidos) AS paciente,
    PAC.tipo_sangre,
    PAC.contacto_emergencia_nombre AS contacto_emergencia,
    PAC.contacto_emergencia_telefono AS telefono_emergencia,
    AM.condicion,
    AM.descripcion AS detalle_antecedente
FROM pacientes PAC
JOIN personas P ON PAC.persona_id = P.id
JOIN antecedentes_medicos AM ON PAC.id = AM.paciente_id
ORDER BY P.apellidos ASC;

-- 6. Usuarios del sistema con su Persona asociada y Rol asignado
SELECT 
    U.id,
    U.username,
    U.email,
    R.nombre AS rol,
    CONCAT(P.nombres, ' ', P.apellidos) AS nombre_completo,
    P.ci,
    P.telefono
FROM usuarios U
JOIN roles R ON U.rol_id = R.id
JOIN personas P ON U.persona_id = P.id;

-- 7. Matriz de Permisos asignados a cada Rol del sistema
SELECT 
    R.nombre AS rol,
    P.nombre AS permiso,
    P.descripcion
FROM rol_permiso RP
JOIN roles R ON RP.rol_id = R.id
JOIN permisos P ON RP.permiso_id = P.id
ORDER BY R.id ASC, P.nombre ASC;

-- 8. Citas Canceladas con el motivo y datos de paciente y médico
SELECT 
    C.codigo_reserva,
    C.fecha,
    CONCAT(P_Pac.nombres, ' ', P_Pac.apellidos) AS paciente,
    CONCAT(P_Med.nombres, ' ', P_Med.apellidos) AS medico,
    C.motivo_cancelacion
FROM citas C
JOIN pacientes PAC ON C.paciente_id = PAC.id
JOIN personas P_Pac ON PAC.persona_id = P_Pac.id
JOIN medico_especialidad ME ON C.medico_especialidad_id = ME.id
JOIN medicos M ON ME.medico_id = M.id
JOIN personas P_Med ON M.persona_id = P_Med.id
WHERE C.estado_id = 4;

-- 9. Ausencias médicas programadas con nombre del profesional
SELECT 
    M.matricula_profesional,
    CONCAT(P.nombres, ' ', P.apellidos) AS medico,
    AM.fecha_inicio,
    AM.fecha_fin,
    AM.motivo
FROM ausencias_medicas AM
JOIN medicos M ON AM.medico_id = M.id
JOIN personas P ON M.persona_id = P.id
ORDER BY AM.fecha_inicio ASC;

-- 10. Bitácora de eventos con nombre de usuario, rol y detalle
SELECT 
    B.id,
    B.fecha_hora,
    COALESCE(U.username, 'SISTEMA') AS usuario,
    COALESCE(R.nombre, 'AUTOMATICO') AS rol,
    B.modulo,
    B.accion,
    B.detalle,
    B.ip_origen
FROM bitacora B
LEFT JOIN usuarios U ON B.usuario_id = U.id
LEFT JOIN roles R ON U.rol_id = R.id
ORDER BY B.id DESC;


-- -----------------------------------------------------------------------------
-- 3.3. SUBCONSULTAS (10 Consultas con NOT IN, EXISTS, Escalares y Anidadas)
-- -----------------------------------------------------------------------------

-- 1. Pacientes que NO tienen ninguna cita registrada en el sistema (Subconsulta NOT IN)
SELECT CONCAT(P.nombres, ' ', P.apellidos) AS paciente_sin_citas
FROM pacientes PAC
JOIN personas P ON PAC.persona_id = P.id
WHERE PAC.id NOT IN (SELECT DISTINCT paciente_id FROM citas);

-- 2. Médicos que tienen al menos una ausencia o licencia registrada (Subconsulta EXISTS)
SELECT CONCAT(P.nombres, ' ', P.apellidos) AS medico_con_ausencias
FROM medicos M
JOIN personas P ON M.persona_id = P.id
WHERE EXISTS (
    SELECT 1 
    FROM ausencias_medicas AM 
    WHERE AM.medico_id = M.id
);

-- 3. La asignación médico-especialidad con el precio de consulta más alto (Subconsulta Escalar)
SELECT 
    CONCAT(P.nombres, ' ', P.apellidos) AS medico,
    E.nombre AS especialidad,
    ME.precio_consulta
FROM medico_especialidad ME
JOIN medicos M ON ME.medico_id = M.id
JOIN personas P ON M.persona_id = P.id
JOIN especialidades E ON ME.especialidad_id = E.id
WHERE ME.precio_consulta = (SELECT MAX(precio_consulta) FROM medico_especialidad);

-- 4. Citas cuyo arancel de consulta supera el precio promedio de la clínica
SELECT 
    C.codigo_reserva, 
    C.fecha, 
    ME.precio_consulta
FROM citas C
JOIN medico_especialidad ME ON C.medico_especialidad_id = ME.id
WHERE ME.precio_consulta > (SELECT AVG(precio_consulta) FROM medico_especialidad);

-- 5. Pacientes que tienen antecedentes médicos relacionados a 'Hipertensión' (Subconsulta IN)
SELECT CONCAT(P.nombres, ' ', P.apellidos) AS paciente_hipertenso
FROM pacientes PAC
JOIN personas P ON PAC.persona_id = P.id
WHERE PAC.id IN (
    SELECT paciente_id 
    FROM antecedentes_medicos 
    WHERE condicion LIKE '%Hipertensión%'
);

-- 6. Especialidades que nunca han tenido una cita agendada (Subconsulta NOT EXISTS)
SELECT E.nombre AS especialidad_sin_citas
FROM especialidades E
WHERE NOT EXISTS (
    SELECT 1 
    FROM medico_especialidad ME
    JOIN citas C ON C.medico_especialidad_id = ME.id
    WHERE ME.especialidad_id = E.id
);

-- 7. Mostrar la cita más reciente registrada en el sistema (Subconsulta de ID Máximo)
SELECT 
    C.id, 
    C.codigo_reserva, 
    C.fecha, 
    CONCAT(P.nombres, ' ', P.apellidos) AS paciente
FROM citas C
JOIN pacientes PAC ON C.paciente_id = PAC.id
JOIN personas P ON PAC.persona_id = P.id
WHERE C.id = (SELECT MAX(id) FROM citas);

-- 8. Médicos que atienden en más de una especialidad médica (Subconsulta en el FROM)
SELECT medico, total_especialidades
FROM (
    SELECT 
        CONCAT(P.nombres, ' ', P.apellidos) AS medico,
        COUNT(ME.especialidad_id) AS total_especialidades
    FROM medicos M
    JOIN personas P ON M.persona_id = P.id
    JOIN medico_especialidad ME ON M.id = ME.medico_id
    GROUP BY M.id, P.nombres, P.apellidos
) AS ResumenMedicos
WHERE total_especialidades > 1;

-- 9. Número total de citas atendidas por cada médico (Subconsulta Correlacionada en SELECT)
SELECT 
    CONCAT(P.nombres, ' ', P.apellidos) AS medico,
    (
        SELECT COUNT(*) 
        FROM citas C
        JOIN medico_especialidad ME ON C.medico_especialidad_id = ME.id
        WHERE ME.medico_id = M.id AND C.estado_id = 3
    ) AS total_citas_atendidas
FROM medicos M
JOIN personas P ON M.persona_id = P.id;

-- 10. Roles que tienen asignado el permiso de 'registrar_atencion' (Subconsulta Anidada)
SELECT nombre AS rol_autorizado
FROM roles
WHERE id IN (
    SELECT rol_id 
    FROM rol_permiso 
    WHERE permiso_id = (SELECT id FROM permisos WHERE nombre = 'registrar_atencion')
);


-- =============================================================================
-- 4. PROCEDIMIENTOS ALMACENADOS (STORED PROCEDURES)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 4.1. PROCEDIMIENTO: REGISTRAR NUEVA CITA MÉDICA
-- Valida disponibilidad, genera código único, reserva la cita y audita en bitácora.
-- -----------------------------------------------------------------------------
DELIMITER //
CREATE PROCEDURE sp_RegistrarCita(
    IN p_paciente_id INT,
    IN p_medico_especialidad_id INT,
    IN p_fecha DATE,
    IN p_hora_inicio TIME,
    IN p_hora_fin TIME,
    IN p_motivo TEXT,
    IN p_usuario_id INT
)
BEGIN
    DECLARE v_solapamiento INT DEFAULT 0;
    DECLARE v_codigo VARCHAR(20);
    DECLARE v_medico_id INT;
    DECLARE v_ausencia INT DEFAULT 0;

    -- 1. Obtener el id del médico a partir de la asignación médico-especialidad
    SELECT medico_id INTO v_medico_id 
    FROM medico_especialidad 
    WHERE id = p_medico_especialidad_id;

    -- 2. Validar si el médico tiene registrada una ausencia para esa fecha
    SELECT COUNT(*) INTO v_ausencia
    FROM ausencias_medicas
    WHERE medico_id = v_medico_id 
      AND p_fecha BETWEEN fecha_inicio AND fecha_fin;

    IF v_ausencia > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error: El médico se encuentra ausente o de licencia en la fecha seleccionada.';
    END IF;

    -- 3. Validar solapamiento de horarios (citas activas en la misma fecha y hora)
    SELECT COUNT(*) INTO v_solapamiento
    FROM citas
    WHERE medico_especialidad_id = p_medico_especialidad_id
      AND fecha = p_fecha
      AND estado_id IN (1, 2, 6, 7) -- Estados activos
      AND (
          (p_hora_inicio >= hora_inicio AND p_hora_inicio < hora_fin) OR
          (p_hora_fin > hora_inicio AND p_hora_fin <= hora_fin)
      );

    IF v_solapamiento > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error: El médico ya tiene una cita agendada en ese rango horario.';
    ELSE
        -- 4. Generar código de reserva único basado en fecha y número secuencial
        SET v_codigo = CONCAT('CIT-', DATE_FORMAT(p_fecha, '%Y%m%d'), '-', LPAD(FLOOR(RAND() * 9000) + 1000, 4, '0'));

        -- 5. Insertar la cita con estado inicial 'Programada' (1)
        INSERT INTO citas (codigo_reserva, paciente_id, medico_especialidad_id, estado_id, fecha, hora_inicio, hora_fin, motivo_consulta, fecha_registro)
        VALUES (v_codigo, p_paciente_id, p_medico_especialidad_id, 1, p_fecha, p_hora_inicio, p_hora_fin, p_motivo, NOW());

        -- 6. Registrar en bitácora de auditoría
        INSERT INTO bitacora (usuario_id, accion, modulo, detalle, ip_origen, fecha_hora)
        VALUES (p_usuario_id, 'PROGRAMACION_CITA', 'Citas', CONCAT('Cita agendada exitosamente con código: ', v_codigo), '127.0.0.1', NOW());

        SELECT CONCAT('Cita agendada exitosamente con código: ', v_codigo) AS resultado;
    END IF;
END //
DELIMITER ;


-- -----------------------------------------------------------------------------
-- 4.2. PROCEDIMIENTO: CONFIRMAR CITA MÉDICA
-- Cambia el estado de Programada (1) a Confirmada (2).
-- -----------------------------------------------------------------------------
DELIMITER //
CREATE PROCEDURE sp_ConfirmarCita(
    IN p_cita_id INT,
    IN p_usuario_id INT
)
BEGIN
    DECLARE v_estado_actual INT;

    SELECT estado_id INTO v_estado_actual FROM citas WHERE id = p_cita_id;

    IF v_estado_actual IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: La cita médica especificada no existe.';
    ELSEIF v_estado_actual <> 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Solo las citas en estado Programada pueden confirmarse.';
    ELSE
        UPDATE citas 
        SET estado_id = 2, updated_at = NOW() 
        WHERE id = p_cita_id;

        INSERT INTO bitacora (usuario_id, accion, modulo, detalle, ip_origen, fecha_hora)
        VALUES (p_usuario_id, 'CONFIRMACION_CITA', 'Citas', CONCAT('Cita ID ', p_cita_id, ' confirmada.'), '127.0.0.1', NOW());

        SELECT CONCAT('Cita ID ', p_cita_id, ' confirmada con éxito.') AS resultado;
    END IF;
END //
DELIMITER ;


-- -----------------------------------------------------------------------------
-- 4.3. PROCEDIMIENTO: REGISTRAR ATENCIÓN CLÍNICA (FINALIZACIÓN MÉDICA)
-- Registra diagnóstico, tratamiento médico y pasa la cita a 'Atendida' (3).
-- -----------------------------------------------------------------------------
DELIMITER //
CREATE PROCEDURE sp_RegistrarAtencionClinica(
    IN p_cita_id INT,
    IN p_diagnostico TEXT,
    IN p_tratamiento TEXT,
    IN p_notas TEXT,
    IN p_usuario_id INT
)
BEGIN
    DECLARE v_existe_atencion INT DEFAULT 0;

    -- Validar si ya cuenta con atención previa
    SELECT COUNT(*) INTO v_existe_atencion FROM atenciones_clinicas WHERE cita_id = p_cita_id;

    IF v_existe_atencion > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Esta cita ya cuenta con atención clínica registrada.';
    ELSE
        -- 1. Insertar atención médica
        INSERT INTO atenciones_clinicas (cita_id, diagnostico, tratamiento, notas_adicionales, fecha_atencion)
        VALUES (p_cita_id, p_diagnostico, p_tratamiento, p_notas, NOW());

        -- 2. Actualizar estado de la cita a 'Atendida' (3)
        UPDATE citas 
        SET estado_id = 3, updated_at = NOW() 
        WHERE id = p_cita_id;

        -- 3. Registrar auditoría en bitácora
        INSERT INTO bitacora (usuario_id, accion, modulo, detalle, ip_origen, fecha_hora)
        VALUES (p_usuario_id, 'REGISTRO_ATENCION', 'Atención Médica', CONCAT('Atención clínica registrada para cita ID: ', p_cita_id), '127.0.0.1', NOW());

        SELECT CONCAT('Atención clínica registrada exitosamente para la cita ID: ', p_cita_id) AS resultado;
    END IF;
END //
DELIMITER ;


-- -----------------------------------------------------------------------------
-- 4.4. PROCEDIMIENTO: CANCELAR CITA MÉDICA
-- Cambia estado a Cancelada (4) registrando el motivo correspondiente.
-- -----------------------------------------------------------------------------
DELIMITER //
CREATE PROCEDURE sp_CancelarCita(
    IN p_cita_id INT,
    IN p_motivo TEXT,
    IN p_usuario_id INT
)
BEGIN
    DECLARE v_estado_actual INT;

    SELECT estado_id INTO v_estado_actual FROM citas WHERE id = p_cita_id;

    IF v_estado_actual IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: La cita médica especificada no existe.';
    ELSEIF v_estado_actual IN (3, 4) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No se puede cancelar una cita que ya ha sido atendida o previamente cancelada.';
    ELSE
        UPDATE citas 
        SET estado_id = 4, motivo_cancelacion = p_motivo, updated_at = NOW() 
        WHERE id = p_cita_id;

        INSERT INTO bitacora (usuario_id, accion, modulo, detalle, ip_origen, fecha_hora)
        VALUES (p_usuario_id, 'CANCELACION_CITA', 'Citas', CONCAT('Cita ID ', p_cita_id, ' cancelada. Motivo: ', p_motivo), '127.0.0.1', NOW());

        SELECT CONCAT('Cita ID ', p_cita_id, ' ha sido cancelada exitosamente.') AS resultado;
    END IF;
END //
DELIMITER ;


-- -----------------------------------------------------------------------------
-- 4.5. PROCEDIMIENTO: REGISTRAR AUSENCIA MÉDICA Y SUSPENDER AGENDA
-- Registra ausencia y audita la indisponibilidad del médico.
-- -----------------------------------------------------------------------------
DELIMITER //
CREATE PROCEDURE sp_RegistrarAusenciaMedica(
    IN p_medico_id INT,
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_motivo VARCHAR(200),
    IN p_usuario_id INT
)
BEGIN
    IF p_fecha_fin < p_fecha_inicio THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: La fecha de fin no puede ser anterior a la fecha de inicio.';
    ELSE
        INSERT INTO ausencias_medicas (medico_id, fecha_inicio, fecha_fin, motivo)
        VALUES (p_medico_id, p_fecha_inicio, p_fecha_fin, p_motivo);

        INSERT INTO bitacora (usuario_id, accion, modulo, detalle, ip_origen, fecha_hora)
        VALUES (p_usuario_id, 'REGISTRO_AUSENCIA', 'Médicos', CONCAT('Ausencia registrada para médico ID ', p_medico_id, ' del ', p_fecha_inicio, ' al ', p_fecha_fin), '127.0.0.1', NOW());

        SELECT CONCAT('Ausencia médica registrada exitosamente para el profesional ID ', p_medico_id) AS resultado;
    END IF;
END //
DELIMITER ;


-- -----------------------------------------------------------------------------
-- 4.6. PROCEDIMIENTO: REGISTRAR EVENTO DIRECTO EN BITÁCORA
-- -----------------------------------------------------------------------------
DELIMITER //
CREATE PROCEDURE sp_RegistrarBitacora(
    IN p_usuario_id INT,
    IN p_accion VARCHAR(50),
    IN p_modulo VARCHAR(50),
    IN p_detalle TEXT,
    IN p_ip VARCHAR(45)
)
BEGIN
    INSERT INTO bitacora (usuario_id, accion, modulo, detalle, ip_origen, fecha_hora)
    VALUES (p_usuario_id, p_accion, p_modulo, p_detalle, p_ip, NOW());
END //
DELIMITER ;


-- =============================================================================
-- 5. DISPARADORES (TRIGGERS)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 5.1. TRIGGER: AUDITORÍA AUTOMÁTICA AL CAMBIAR ESTADO DE CITA
-- Registra en bitácora cada transición de estado en las citas médicas.
-- -----------------------------------------------------------------------------
DELIMITER //
CREATE TRIGGER tg_AuditoriaCambioEstadoCita
AFTER UPDATE ON citas
FOR EACH ROW
BEGIN
    IF OLD.estado_id <> NEW.estado_id THEN
        INSERT INTO bitacora (usuario_id, accion, modulo, detalle, ip_origen, fecha_hora)
        VALUES (
            NULL, 
            'CAMBIO_ESTADO_CITA', 
            'Citas', 
            CONCAT('Cita ', NEW.codigo_reserva, ' cambió de estado ID ', OLD.estado_id, ' a ID ', NEW.estado_id), 
            'Sistema-Trigger', 
            NOW()
        );
    END IF;
END //
DELIMITER ;


-- -----------------------------------------------------------------------------
-- 5.2. TRIGGER: ATENCIÓN CLÍNICA FINALIZA AUTOMÁTICAMENTE LA CITA
-- Garantiza que al registrar una atención, el estado de la cita pase a 'Atendida' (3).
-- -----------------------------------------------------------------------------
DELIMITER //
CREATE TRIGGER tg_AtencionFinalizaCita
AFTER INSERT ON atenciones_clinicas
FOR EACH ROW
BEGIN
    UPDATE citas 
    SET estado_id = 3, updated_at = NOW()
    WHERE id = NEW.cita_id;
END //
DELIMITER ;


-- -----------------------------------------------------------------------------
-- 5.3. TRIGGER: AUDITAR CREACIÓN DE NUEVOS PACIENTES EN BITÁCORA
-- Registra en bitácora cada vez que se afilia a un nuevo paciente.
-- -----------------------------------------------------------------------------
DELIMITER //
CREATE TRIGGER tg_AuditoriaNuevoPaciente
AFTER INSERT ON pacientes
FOR EACH ROW
BEGIN
    INSERT INTO bitacora (usuario_id, accion, modulo, detalle, ip_origen, fecha_hora)
    VALUES (
        NULL,
        'NUEVO_PACIENTE',
        'Pacientes',
        CONCAT('Nuevo paciente afiliado ID: ', NEW.id, ' con Persona ID: ', NEW.persona_id, ' y Tipo de Sangre: ', COALESCE(NEW.tipo_sangre, 'N/D')),
        'Sistema-Trigger',
        NOW()
    );
END //
DELIMITER ;


-- -----------------------------------------------------------------------------
-- 5.4. TRIGGER: PREVENIR PROGRAMACIÓN DE CITAS EN DÍAS DE AUSENCIA MÉDICA
-- Valida antes del INSERT en citas que el médico no tenga ausencia vigente.
-- -----------------------------------------------------------------------------
DELIMITER //
CREATE TRIGGER tg_ValidarAusenciaAlCitar
BEFORE INSERT ON citas
FOR EACH ROW
BEGIN
    DECLARE v_medico_id INT;
    DECLARE v_en_ausencia INT DEFAULT 0;

    -- Localizar al médico
    SELECT medico_id INTO v_medico_id 
    FROM medico_especialidad 
    WHERE id = NEW.medico_especialidad_id;

    -- Comprobar si existe ausencia
    SELECT COUNT(*) INTO v_en_ausencia
    FROM ausencias_medicas
    WHERE medico_id = v_medico_id
      AND NEW.fecha BETWEEN fecha_inicio AND fecha_fin;

    IF v_en_ausencia > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Trigger Rechazo: El profesional médico tiene una ausencia o licencia programada en la fecha elegida.';
    END IF;
END //
DELIMITER ;


-- =============================================================================
-- 6. SIMULACIÓN DEL FLUJO COMPLETO Y PRUEBAS DE EJECUCIÓN
-- =============================================================================

-- Paso 1: Recepcionista agenda una nueva cita médica para el paciente 1 con el Dr. Rodríguez (medico_especialidad 1)
CALL sp_RegistrarCita(1, 1, '2026-05-25', '09:00:00', '09:30:00', 'Chequeo de presión y revisión de laboratorio', 2);

-- Verificar la cita recién creada:
SELECT * FROM citas WHERE fecha = '2026-05-25' AND hora_inicio = '09:00:00';

-- Paso 2: Recepcionista confirma la cita médica agendada (usamos el ID recién creado, ej: 16)
CALL sp_ConfirmarCita(16, 2);

-- Verificar el cambio de estado y el disparo del trigger tg_AuditoriaCambioEstadoCita en bitácora:
SELECT id, codigo_reserva, estado_id FROM citas WHERE id = 16;
SELECT * FROM bitacora ORDER BY id DESC LIMIT 2;

-- Paso 3: El médico concluye la atención clínica y prescribe tratamiento
CALL sp_RegistrarAtencionClinica(
    16, 
    'Hipertensión arterial controlada y buena respuesta al Enalapril', 
    'Mantener dosis habitual de Enalapril 10mg en ayunas. Control en 6 meses.', 
    'Paciente asiste puntual a su consulta.', 
    5
);

-- Verificar el registro de la atención clínica y el trigger tg_AtencionFinalizaCita:
SELECT * FROM atenciones_clinicas WHERE cita_id = 16;
SELECT id, codigo_reserva, estado_id FROM citas WHERE id = 16; -- Debe figurar con estado_id = 3 (Atendida)

-- Paso 4: Probar la cancelación de una cita médica
CALL sp_CancelarCita(13, 'Paciente solicita cancelación por motivos personales', 2);

-- Verificar estado cancelado:
SELECT id, codigo_reserva, estado_id, motivo_cancelacion FROM citas WHERE id = 13;

-- Paso 5: Probar el registro de una ausencia médica para un profesional
CALL sp_RegistrarAusenciaMedica(5, '2026-09-10', '2026-09-15', 'Vacaciones anuales de primavera', 1);

-- Verificar la ausencia registrada:
SELECT * FROM ausencias_medicas WHERE medico_id = 5 ORDER BY id DESC LIMIT 1;

-- Paso 6: Verificación de los últimos eventos registrados por los Triggers y Procedimientos en la Bitácora
SELECT 
    B.id,
    B.fecha_hora,
    COALESCE(U.username, 'TRIGGER_AUTO') AS usuario,
    B.modulo,
    B.accion,
    B.detalle
FROM bitacora B
LEFT JOIN usuarios U ON B.usuario_id = U.id
ORDER BY B.id DESC 
LIMIT 10;
