<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' · ' : '' ?>Admin Cabo Bay</title>
    <link rel="stylesheet" href="/admin/assets/admin.css">
</head>
<body>
<div class="admin-layout">
    <?php include __DIR__ . '/admin-sidebar.php'; ?>
    <main class="admin-content">
        <header class="admin-topbar">
            <h1><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
            <div class="admin-user">
                <span><?= htmlspecialchars($_SESSION['admin_username']) ?></span>
                <a href="/admin/logout.php">Cerrar sesión</a>
            </div>
        </header>
