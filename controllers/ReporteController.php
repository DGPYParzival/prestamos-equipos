<?php
// controllers/ReporteController.php
// Genera las estadísticas y métricas del sistema para el dashboard.
// Los datos calculados aquí alimentan los gráficos de Chart.js en la vista.
// Solo accesible para administradores.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Prestamo.php';
require_once __DIR__ . '/../models/Equipo.php';
require_once __DIR__ . '/../models/Sancion.php';
require_once __DIR__ . '/../models/Mantenimiento.php';
require_once __DIR__ . '/../models/Usuario.php';

class ReporteController
{
    private Prestamo      $prestamoModel;
    private Equipo        $equipoModel;
    private Sancion       $sancionModel;
    private Mantenimiento $mantenimientoModel;
    private Usuario       $usuarioModel;

    public function __construct()
    {
        $this->prestamoModel      = new Prestamo();
        $this->equipoModel        = new Equipo();
        $this->sancionModel       = new Sancion();
        $this->mantenimientoModel = new Mantenimiento();
        $this->usuarioModel       = new Usuario();
    }

    // ─────────────────────────────────────────
    // DASHBOARD PRINCIPAL
    // ─────────────────────────────────────────

    /**
     * Compila todas las métricas necesarias para el dashboard y
     * las pasa a la vista como variables individuales.
     */
    public function dashboard(): void
    {
        // ── Tarjetas de resumen (KPIs) ─────────
        $totalEquipos          = $this->equipoModel->contarPorEstado();
        $prestamosActivos      = $this->prestamoModel->contarPorEstado(PRESTAMO_ACTIVO);
        $solicitudesPendientes = $this->prestamoModel->contarPorEstado(PRESTAMO_PENDIENTE);
        $sancionesActivas      = $this->sancionModel->contarActivas();
        $enMantenimiento       = $this->equipoModel->contarEnMantenimiento();
        $usuariosPorTipo       = $this->usuarioModel->contarPorTipo();

        // ── Datos para gráfico de barras ───────
        // Top 5 equipos más solicitados (nombre + cantidad de préstamos)
        $equiposMasSolicitados = $this->prestamoModel->topEquiposMasSolicitados(5);

        // ── Datos para gráfico de línea ────────
        // Tasa de devolución a tiempo por mes (últimos 6 meses)
        $tasaDevolucion = $this->prestamoModel->tasaDevolucionPorMes(6);

        // ── Tabla de préstamos recientes ───────
        $prestamosRecientes = $this->prestamoModel->obtenerRecientes(10);

        // ── Equipos en mantenimiento activo ────
        $mantenimientosActivos = $this->mantenimientoModel->obtenerActivos();

        // ── Top usuarios con más préstamos ─────
        $topUsuarios = $this->usuarioModel->topConMasPrestamos(5);

        $pageTitle = 'Dashboard · Panel de Administración';
        require_once __DIR__ . '/../views/reportes/dashboard.php';
    }

    // ─────────────────────────────────────────
    // REPORTE DETALLADO (exportable)
    // ─────────────────────────────────────────

    /**
     * Genera un reporte tabular completo con filtros de fecha.
     * Útil para copiar los datos al artículo IEEE.
     */
    public function reportes(): void
    {
        // Filtro por rango de fechas (por defecto: mes actual)
        $fechaIni = $_GET['fecha_ini'] ?? date('Y-m-01'); // primer día del mes
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');  // hoy

        $prestamos      = $this->prestamoModel->obtenerEnRango($fechaIni, $fechaFin);
        $totalPrestamos = count($prestamos);

        // Calcular tasa de devolución a tiempo en el período
        $devueltosATiempo = array_filter(
            $prestamos,
            fn($p) => $p['estado'] === PRESTAMO_DEVUELTO
                   && $p['fecha_devolucion_real'] <= $p['fecha_devolucion_esperada']
        );

        $tasaATiempo = $totalPrestamos > 0
            ? round(count($devueltosATiempo) / $totalPrestamos * 100, 1)
            : 0;

        // Top equipos y usuarios para la tabla del reporte
        $topEquipos  = $this->prestamoModel->topEquiposMasSolicitados(10);
        $topUsuarios = $this->usuarioModel->topConMasPrestamos(10);

        $pageTitle = 'Reportes del Sistema';
        require_once __DIR__ . '/../views/reportes/reportes.php';
    }
}