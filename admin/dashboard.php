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

<section class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="card text-center">
        <span class="block text-3xl font-bold text-navy"><?= (int)$totalBookings ?></span>
        <span class="text-sm text-gray-500">Reservas totales</span>
    </div>
    <div class="card text-center">
        <span class="block text-3xl font-bold text-navy"><?= (int)$pendingBookings ?></span>
        <span class="text-sm text-gray-500">Pendientes</span>
    </div>
    <div class="card text-center">
        <span class="block text-3xl font-bold text-navy"><?= (int)$activeZones ?></span>
        <span class="text-sm text-gray-500">Zonas activas</span>
    </div>
    <div class="card text-center">
        <span class="block text-3xl font-bold text-navy"><?= (int)$galleryCount ?></span>
        <span class="text-sm text-gray-500">Fotos en carrusel</span>
    </div>
</section>

<section class="card overflow-x-auto">
    <h2 class="text-lg font-semibold text-navy mb-4">Últimas reservas</h2>
    <table class="w-full text-sm min-w-[520px]">
        <thead>
            <tr class="text-left text-gray-500 border-b border-gray-100">
                <th class="pb-2 font-medium">Nombre</th>
                <th class="pb-2 font-medium">Destino</th>
                <th class="pb-2 font-medium">Fecha</th>
                <th class="pb-2 font-medium">Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($recentBookings as $b): ?>
            <tr class="border-b border-gray-50">
                <td class="py-2.5"><?= htmlspecialchars($b['full_name']) ?></td>
                <td class="py-2.5"><?= htmlspecialchars($b['booking_type'] === 'wedding' ? $b['hotel'] : $b['zone_name']) ?></td>
                <td class="py-2.5"><?= htmlspecialchars($b['service_date']) ?></td>
                <td class="py-2.5"><span class="badge badge-<?= $b['status'] ?>"><?= htmlspecialchars($b['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$recentBookings): ?>
            <tr><td colspan="4" class="text-center py-8 text-gray-400">Todavia no hay reservas.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <a href="/admin/bookings.php" class="inline-block mt-4 text-coral text-sm font-medium hover:text-coral-dark">Ver todas las reservas -></a>
</section>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
