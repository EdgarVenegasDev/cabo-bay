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
    <?php include __DIR__ . '/admin-sidebar.php'; ?>
    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-navy"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
            <div class="flex items-center gap-4 text-sm">
                <span class="text-gray-500"><?= htmlspecialchars($_SESSION['admin_username']) ?></span>
                <a href="/admin/logout.php" class="text-coral font-medium hover:text-coral-dark">Cerrar sesion</a>
            </div>
        </header>
