<?php
// config/constants.php
// Constantes globales del sistema.
// Se incluye una sola vez desde index.php y queda disponible en todo el proyecto.


// ROLES DE USUARIO
define('ROL_ADMIN',      'admin');
define('ROL_DOCENTE',    'docente');
define('ROL_ESTUDIANTE', 'estudiante');

// ESTADOS DE USUARIO
define('USUARIO_ACTIVO',     'activo');
define('USUARIO_SANCIONADO', 'sancionado');

// ESTADOS DE EQUIPO
define('EQUIPO_DISPONIBLE',    'disponible');
define('EQUIPO_PRESTADO',      'prestado');
define('EQUIPO_MANTENIMIENTO', 'mantenimiento');
define('EQUIPO_BAJA',          'baja');

// CONDICIÓN DE EQUIPO
define('CONDICION_BUENO',   'bueno');
define('CONDICION_REGULAR', 'regular');
define('CONDICION_DANADO',  'dañado');

// ESTADOS DE PRÉSTAMO
define('PRESTAMO_PENDIENTE', 'pendiente');
define('PRESTAMO_APROBADO',  'aprobado');
define('PRESTAMO_ACTIVO',    'activo');
define('PRESTAMO_DEVUELTO',  'devuelto');
define('PRESTAMO_RECHAZADO', 'rechazado');
define('PRESTAMO_VENCIDO',   'vencido');

// ─────────────────────────────────────────
// ESTADOS DE SANCIÓN
// ─────────────────────────────────────────
define('SANCION_ACTIVA',   'activa');
define('SANCION_CUMPLIDA', 'cumplida');

// ─────────────────────────────────────────
// MOTIVOS DE SANCIÓN
// ─────────────────────────────────────────
define('MOTIVO_RETRASO', 'retraso');
define('MOTIVO_DANO',    'daño');
define('MOTIVO_PERDIDA', 'perdida');

// ─────────────────────────────────────────
// TIPOS DE MANTENIMIENTO
// ─────────────────────────────────────────
define('MANT_PREVENTIVO', 'preventivo');
define('MANT_CORRECTIVO', 'correctivo');

// ─────────────────────────────────────────
// REGLAS DE NEGOCIO
// ─────────────────────────────────────────
// Días mínimos de sanción cuando hay retraso o daño
define('SANCION_DIAS_MINIMO', 3);

// Multiplicador de días de retraso para calcular la sanción
// Ejemplo: 2 días de retraso → 2 * 2 = 4 días de sanción
define('SANCION_MULTIPLICADOR_RETRASO', 2);

// ─────────────────────────────────────────
// URL BASE DEL SISTEMA
// ─────────────────────────────────────────
// Ajusta esta ruta según el nombre de tu carpeta en XAMPP.
// Si tu proyecto está en C:/xampp/htdocs/simulador-prestamos/ → '/simulador-prestamos/'
// Si está directo en htdocs/ → '/'
define('BASE_URL', '/prestamos-equipos/');