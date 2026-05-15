<?php
// views/layout/header.php
require_once __DIR__ . '/../../config/constants.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Sistema de Préstamos') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
</head>
<body class="bg-gray-100 text-gray-900 min-h-screen flex flex-col">

<nav class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between shadow-sm">
  <a href="index.php?action=<?= $_SESSION['usuario']['tipo'] === ROL_ADMIN ? 'dashboard' : 'equipos' ?>"
     class="flex items-center gap-2 font-bold text-blue-700 text-lg">
    <i class="ti ti-device-laptop text-2xl"></i>
    <span class="hidden sm:inline">Préstamos Equipos</span>
  </a>
  <div class="flex items-center gap-4 text-sm">
    <?php if (($_SESSION['usuario']['estado'] ?? '') === USUARIO_SANCIONADO): ?>
      <span class="bg-red-100 text-red-700 border border-red-300 px-3 py-1 rounded-full text-xs font-medium">
        <i class="ti ti-alert-triangle mr-1"></i> Sanción activa
      </span>
    <?php endif; ?>
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
        <?= strtoupper(substr($_SESSION['usuario']['nombre'], 0, 1)) ?>
      </div>
      <div class="hidden sm:block">
        <p class="font-medium leading-none"><?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></p>
        <p class="text-xs text-gray-500 capitalize"><?= htmlspecialchars($_SESSION['usuario']['tipo']) ?></p>
      </div>
    </div>
    <a href="index.php?action=perfil" class="text-gray-500 hover:text-blue-600 transition-colors" title="Mi perfil">
      <i class="ti ti-user text-xl"></i>
    </a>
    <a href="index.php?action=logout" class="text-gray-500 hover:text-red-600 transition-colors" title="Cerrar sesión">
      <i class="ti ti-logout text-xl"></i>
    </a>
  </div>
</nav>

<div class="flex flex-1">