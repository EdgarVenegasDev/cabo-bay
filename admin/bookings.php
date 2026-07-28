<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';

$pdo = Database::getConnection();

$statusFilter = $_GET['status'] ?? '';
$typeFilter   = $_GET['type'] ?? '';
$search       = trim($_GET['q'] ?? '');

$where  = [];
$params = [];

if (in_array($statusFilter, ['pending', 'confirmed', 'cancelled'], true)) {
    $where[] = 'status = ?';
    $params[] = $statusFilter;
}
if (in_array($typeFilter, ['regular', 'wedding'], true)) {
    $where[] = 'booking_type = ?';
    $params[] = $typeFilter;
}
if ($search !== '') {
    $where[] = '(full_name LIKE ? OR email LIKE ? OR reference LIKE ?)';
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $pdo->prepare("SELECT * FROM bookings $whereSql ORDER BY created_at DESC LIMIT 200");
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$pageTitle = 'Reservas';
require __DIR__ . '/includes/admin-header.php';
?>

<form method="GET" style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; align-items:end;">
    <div>
        <label style="font-size:12px; color:#666; display:block; margin-bottom:4px;">Buscar (nombre, email, referencia)</label>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Ej: CB-A1B2C3"
               style="padding:8px; border:1px solid #e1e4e8; border-radius:6px; min-width:220px;">
    </div>
    <div>
        <label style="font-size:12px; color:#666; display:block; margin-bottom:4px;">Estado</label>
        <select name="status" style="padding:8px; border:1px solid #e1e4e8; border-radius:6px;">
            <option value="">Todos</option>
            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
    </div>
    <div>
        <label style="font-size:12px; color:#666; display:block; margin-bottom:4px;">Tipo</label>
        <select name="type" style="padding:8px; border:1px solid #e1e4e8; border-radius:6px;">
            <option value="">Todos</option>
            <option value="regular" <?= $typeFilter === 'regular' ? 'selected' : '' ?>>Regular</option>
            <option value="wedding" <?= $typeFilter === 'wedding' ? 'selected' : '' ?>>Wedding</option>
        </select>
    </div>
    <button type="submit" style="background:#0f2a3f; color:#fff; border:none; padding:9px 20px; border-radius:6px; cursor:pointer;">
        Filtrar
    </button>
    <?php if ($statusFilter || $typeFilter || $search): ?>
        <a href="/admin/bookings.php" style="padding:9px 14px; color:#666; text-decoration:none; font-size:14px;">Limpiar filtros</a>
    <?php endif; ?>
</form>

<div id="feedback" style="display:none; padding:10px 16px; border-radius:8px; margin-bottom:16px; font-size:14px;"></div>

<section class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Ref</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>Destino</th>
                <th>Viaje</th>
                <th>Fecha</th>
                <th>Precio</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
            <tr data-booking-id="<?= $b['id'] ?>">
                <td><code style="font-size:12px;"><?= htmlspecialchars($b['reference']) ?></code></td>
                <td>
                    <span style="font-size:11px; padding:2px 8px; border-radius:10px; background:<?= $b['booking_type'] === 'wedding' ? '#fce4ec' : '#e3f2fd' ?>;">
                        <?= $b['booking_type'] === 'wedding' ? 'Wedding' : 'Regular' ?>
                    </span>
                </td>
                <td>
                    <?= htmlspecialchars($b['full_name']) ?><br>
                    <small style="color:#888;"><?= htmlspecialchars($b['email']) ?> - <?= htmlspecialchars($b['phone']) ?></small>
                </td>
                <td>
                    <?php if ($b['booking_type'] === 'wedding'): ?>
                        <?= htmlspecialchars($b['hotel'] ?? '-') ?>
                    <?php else: ?>
                        <?= htmlspecialchars($b['area'] ?? '-') ?><br>
                        <small style="color:#888;"><?= htmlspecialchars($b['zone_name'] ?? '') ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <?= $b['trip_type'] === 'roundtrip' ? 'Round Trip' : 'One Way' ?><br>
                    <small style="color:#888;"><?= (int)$b['passengers'] ?> pax</small>
                </td>
                <td>
                    <?= htmlspecialchars($b['service_date']) ?><br>
                    <small style="color:#888;"><?= htmlspecialchars($b['service_time']) ?></small>
                </td>
                <td>$<?= number_format((float)$b['price'], 2) ?></td>
                <td>
                    <select class="status-select" data-id="<?= $b['id'] ?>"
                            style="padding:5px 8px; border-radius:6px; border:1px solid #e1e4e8; font-size:12px;">
                        <option value="pending" <?= $b['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $b['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="cancelled" <?= $b['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </td>
                <td>
                    <a href="mailto:<?= htmlspecialchars($b['email']) ?>" title="Enviar email" style="text-decoration:none;">Email</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$bookings): ?>
            <tr><td colspan="9" style="text-align:center; padding:30px; color:#888;">No hay reservas que coincidan con el filtro.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>

<script>
function showFeedback(msg, ok) {
    const el = document.getElementById('feedback');
    el.textContent = msg;
    el.style.display = 'block';
    el.style.background = ok ? '#d4edda' : '#f8d7da';
    el.style.color = ok ? '#155724' : '#721c24';
    setTimeout(() => { el.style.display = 'none'; }, 3000);
}

document.querySelectorAll('.status-select').forEach(select => {
    const original = select.value;
    select.addEventListener('change', async () => {
        const id = select.dataset.id;
        const status = select.value;

        const res = await fetch('/api/admin_bookings.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'update_status', id, status }),
        });
        const result = await res.json();

        if (result.ok) {
            showFeedback('Estado actualizado', true);
        } else {
            select.value = original;
            showFeedback('Error: ' + result.error, false);
        }
    });
});
</script>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
