<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Admin Cabo Bay</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/admin/assets/admin.css">
</head>
<body class="font-sans bg-gray-50 text-gray-900">
<div class="flex min-h-screen">

    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

    <?php include __DIR__ . '/admin-sidebar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 min-w-0">
        <header class="flex justify-between items-center mb-6 lg:mb-8 gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <button id="sidebarToggle" class="lg:hidden p-2 -ml-2 text-navy flex-shrink-0" aria-label="Abrir menu">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <h1 class="text-xl lg:text-2xl font-bold text-navy truncate"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
            </div>
            <div class="flex items-center gap-2 sm:gap-4 text-sm flex-shrink-0">
                <span class="text-gray-500 hidden sm:inline"><?= htmlspecialchars($_SESSION['admin_username']) ?></span>
                <a href="/admin/logout.php" class="text-coral font-medium hover:text-coral-dark whitespace-nowrap">Cerrar sesion</a>
            </div>
        </header>
