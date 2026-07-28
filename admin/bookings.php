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

<form method="GET" class="flex gap-3 mb-5 flex-wrap items-end">
    <div>
        <label class="label-sm">Buscar (nombre, email, referencia)</label>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Ej: CB-A1B2C3" class="input-field min-w-56">
    </div>
    <div>
        <label class="label-sm">Estado</label>
        <select name="status" class="input-field">
            <option value="">Todos</option>
            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
    </div>
    <div>
        <label class="label-sm">Tipo</label>
        <select name="type" class="input-field">
            <option value="">Todos</option>
            <option value="regular" <?= $typeFilter === 'regular' ? 'selected' : '' ?>>Regular</option>
            <option value="wedding" <?= $typeFilter === 'wedding' ? 'selected' : '' ?>>Wedding</option>
        </select>
    </div>
    <button type="submit" class="btn-primary">Filtrar</button>
    <?php if ($statusFilter || $typeFilter || $search): ?>
        <a href="/admin/bookings.php" class="text-sm text-gray-500 hover:text-gray-700 px-2 py-2.5">Limpiar filtros</a>
    <?php endif; ?>
</form>

<div id="feedback" class="hidden px-4 py-2.5 rounded-lg mb-4 text-sm"></div>

<section class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b border-gray-100">
                <th class="pb-2 font-medium pr-4">Ref</th>
                <th class="pb-2 font-medium pr-4">Tipo</th>
                <th class="pb-2 font-medium pr-4">Cliente</th>
                <th class="pb-2 font-medium pr-4">Destino</th>
                <th class="pb-2 font-medium pr-4">Viaje</th>
                <th class="pb-2 font-medium pr-4">Fecha</th>
                <th class="pb-2 font-medium pr-4">Precio</th>
                <th class="pb-2 font-medium pr-4">Estado</th>
                <th class="pb-2 font-medium"></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
            <tr class="border-b border-gray-50" data-booking-id="<?= $b['id'] ?>">
                <td class="py-3 pr-4"><code class="text-xs bg-gray-50 px-1.5 py-0.5 rounded"><?= htmlspecialchars($b['reference']) ?></code></td>
                <td class="py-3 pr-4">
                    <span class="text-xs px-2 py-0.5 rounded-full <?= $b['booking_type'] === 'wedding' ? 'bg-pink-100 text-pink-700' : 'bg-blue-100 text-blue-700' ?>">
                        <?= $b['booking_type'] === 'wedding' ? 'Wedding' : 'Regular' ?>
                    </span>
                </td>
                <td class="py-3 pr-4">
                    <?= htmlspecialchars($b['full_name']) ?><br>
                    <small class="text-gray-400"><?= htmlspecialchars($b['email']) ?> - <?= htmlspecialchars($b['phone']) ?></small>
                </td>
                <td class="py-3 pr-4">
                    <?php if ($b['booking_type'] === 'wedding'): ?>
                        <?= htmlspecialchars($b['hotel'] ?? '-') ?>
                    <?php else: ?>
                        <?= htmlspecialchars($b['area'] ?? '-') ?><br>
                        <small class="text-gray-400"><?= htmlspecialchars($b['zone_name'] ?? '') ?></small>
                    <?php endif; ?>
                </td>
                <td class="py-3 pr-4">
                    <?= $b['trip_type'] === 'roundtrip' ? 'Round Trip' : 'One Way' ?><br>
                    <small class="text-gray-400"><?= (int)$b['passengers'] ?> pax</small>
                </td>
                <td class="py-3 pr-4">
                    <?= htmlspecialchars($b['service_date']) ?><br>
                    <small class="text-gray-400"><?= htmlspecialchars($b['service_time']) ?></small>
                </td>
                <td class="py-3 pr-4">$<?= number_format((float)$b['price'], 2) ?></td>
                <td class="py-3 pr-4">
                    <select class="status-select input-field text-xs py-1.5" data-id="<?= $b['id'] ?>">
                        <option value="pending" <?= $b['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $b['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="cancelled" <?= $b['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </td>
                <td class="py-3">
                    <a href="mailto:<?= htmlspecialchars($b['email']) ?>" title="Enviar email" class="text-coral hover:text-coral-dark text-xs font-medium">Email</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$bookings): ?>
            <tr><td colspan="9" class="text-center py-10 text-gray-400">No hay reservas que coincidan con el filtro.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>

<script>
function showFeedback(msg, ok) {
    const el = document.getElementById('feedback');
    el.textContent = msg;
    el.classList.remove('hidden');
    el.className = 'px-4 py-2.5 rounded-lg mb-4 text-sm ' + (ok ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700');
    setTimeout(() => { el.classList.add('hidden'); }, 3000);
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
