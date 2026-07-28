<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';

$pdo = Database::getConnection();

$totalBookings   = $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
$pendingBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$activeZones     = $pdo->query('SELECT COUNT(*) FROM zones WHERE is_active = 1')->fetchColumn();
$galleryCount    = $pdo->query('SELECT COUNT(*) FROM gallery_photos WHERE is_active = 1')->fetchColumn();

$recentBookings = $pdo->query(
    'SELECT id, full_name, service_date, status, zone_name, hotel, booking_type
     FROM bookings
     ORDER BY created_at DESC LIMIT 5'
)->fetchAll();

$pageTitle = 'Resumen';
require __DIR__ . '/includes/admin-header.php';
?>

<section class="admin-cards">
    <div class="card">
        <span class="card-number"><?= (int)$totalBookings ?></span>
        <span class="card-label">Reservas totales</span>
    </div>
    <div class="card">
        <span class="card-number"><?= (int)$pendingBookings ?></span>
        <span class="card-label">Pendientes</span>
    </div>
    <div class="card">
        <span class="card-number"><?= (int)$activeZones ?></span>
        <span class="card-label">Zonas activas</span>
    </div>
    <div class="card">
        <span class="card-number"><?= (int)$galleryCount ?></span>
        <span class="card-label">Fotos en carrusel</span>
    </div>
</section>

<section class="admin-table-wrap">
    <h2>Ultimas reservas</h2>
    <table class="admin-table">
        <thead>
            <tr><th>Nombre</th><th>Destino</th><th>Fecha</th><th>Estado</th></tr>
        </thead>
        <tbody>
        <?php foreach ($recentBookings as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b['full_name']) ?></td>
                <td><?= htmlspecialchars($b['booking_type'] === 'wedding' ? $b['hotel'] : $b['zone_name']) ?></td>
                <td><?= htmlspecialchars($b['service_date']) ?></td>
                <td><span class="badge badge-<?= $b['status'] ?>"><?= htmlspecialchars($b['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$recentBookings): ?>
            <tr><td colspan="4">Todavia no hay reservas.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
