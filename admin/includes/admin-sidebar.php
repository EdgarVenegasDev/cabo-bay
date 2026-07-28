<?php $current = basename($_SERVER['SCRIPT_NAME']); ?>
<nav class="admin-sidebar">
    <div class="admin-brand">Cabo Bay Admin</div>
    <ul>
        <li><a href="/admin/dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Resumen</a></li>
        <li><a href="/admin/bookings.php" class="<?= $current === 'bookings.php' ? 'active' : '' ?>">Reservas</a></li>
        <li><a href="/admin/pricing.php" class="<?= $current === 'pricing.php' ? 'active' : '' ?>">Precios y viajes populares</a></li>
        <li><a href="/admin/gallery.php" class="<?= $current === 'gallery.php' ? 'active' : '' ?>">Galería / Carrusel</a></li>
    </ul>
</nav>
