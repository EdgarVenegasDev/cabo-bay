<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';

$pdo = Database::getConnection();

$zones = $pdo->query('SELECT * FROM zones ORDER BY display_order ASC, name ASC')->fetchAll();

$areasStmt = $pdo->query('SELECT * FROM areas ORDER BY zone_id, name ASC');
$areasByZone = [];
foreach ($areasStmt->fetchAll() as $a) {
    $areasByZone[$a['zone_id']][] = $a;
}

$pageTitle = 'Precios y zonas';
require __DIR__ . '/includes/admin-header.php';
?>

<p class="text-sm text-gray-500 mb-5">
    Cada zona alimenta dos cosas al mismo tiempo: el selector de precios en el formulario de reserva del sitio,
    y (si esta marcada como "Destacar en home") el carrusel de "rutas mas populares" en la portada.
</p>

<div id="feedback" class="hidden px-4 py-2.5 rounded-lg mb-4 text-sm"></div>

<section class="card mb-5 bg-gray-50">
    <h4 class="text-sm font-semibold text-navy mb-3">Agregar nueva zona</h4>
    <form id="addZoneForm" class="flex gap-2">
        <input type="text" name="name" placeholder="Ej: Todos Santos Norte" required
               class="input-field max-w-xs">
        <button type="submit" class="btn-primary">+ Crear zona</button>
    </form>
    <p class="text-xs text-gray-400 mt-2">
        Se crea con precios en $0 y sin destacar - editala abajo apenas aparezca en la lista (recarga la pagina).
    </p>
</section>

<?php foreach ($zones as $zone): ?>
<section class="card mb-5">
    <form class="zone-form" data-zone-id="<?= $zone['id'] ?>">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-navy"><?= htmlspecialchars($zone['name']) ?></h2>
            <label class="text-sm flex items-center gap-2 text-gray-600">
                <input type="checkbox" name="is_active" <?= $zone['is_active'] ? 'checked' : '' ?>>
                Zona activa (visible en el sitio)
            </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mb-3.5">
            <div>
                <label class="label-sm">Precio One Way (USD)</label>
                <input type="number" step="0.01" min="0" name="one_way_price" value="<?= htmlspecialchars($zone['one_way_price']) ?>" class="input-field">
            </div>
            <div>
                <label class="label-sm">Precio Round Trip (USD)</label>
                <input type="number" step="0.01" min="0" name="round_trip_price" value="<?= htmlspecialchars($zone['round_trip_price']) ?>" class="input-field">
            </div>
            <div>
                <label class="label-sm">Orden en el carrusel</label>
                <input type="number" name="display_order" value="<?= (int)$zone['display_order'] ?>" class="input-field">
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-3.5 mb-3.5">
            <label class="text-sm font-semibold flex items-center gap-2 mb-2.5 text-gray-700">
                <input type="checkbox" name="is_featured" <?= $zone['is_featured'] ? 'checked' : '' ?>>
                Destacar en el carrusel de home ("viajes mas populares")
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3.5">
                <div>
                    <label class="label-sm">Texto del badge</label>
                    <input type="text" name="badge_text" value="<?= htmlspecialchars($zone['badge_text'] ?? '') ?>" placeholder="Ej: Most popular" class="input-field">
                </div>
                <div>
                    <label class="label-sm">Clase del badge</label>
                    <input type="text" name="badge_class" value="<?= htmlspecialchars($zone['badge_class'] ?? '') ?>" placeholder="Ej: badge-popular" class="input-field">
                </div>
                <div class="sm:col-span-2">
                    <label class="label-sm">Resumen de hoteles (texto libre)</label>
                    <input type="text" name="hotels_summary" value="<?= htmlspecialchars($zone['hotels_summary'] ?? '') ?>" placeholder="Ej: ME Cabo, Waldorf Astoria..." class="input-field">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <button type="submit" class="btn-primary">Guardar cambios</button>
            <button type="button" class="delete-zone btn-danger-outline" data-zone-id="<?= $zone['id'] ?>" data-zone-name="<?= htmlspecialchars($zone['name']) ?>">
                Borrar zona
            </button>
            <span class="save-status text-sm"></span>
        </div>
    </form>

    <hr class="my-5 border-gray-100">

    <div class="areas-block" data-zone-id="<?= $zone['id'] ?>">
        <h4 class="text-sm font-semibold text-navy mb-2.5">Hoteles / areas de esta zona</h4>
        <ul class="areas-list flex flex-wrap gap-2 mb-3">
            <?php foreach ($areasByZone[$zone['id']] ?? [] as $area): ?>
                <li data-area-id="<?= $area['id'] ?>" class="bg-gray-100 px-3 py-1.5 rounded-full text-sm flex items-center gap-2">
                    <span class="area-name"><?= htmlspecialchars($area['name']) ?></span>
                    <button type="button" class="delete-area text-red-500 hover:text-red-700 font-bold leading-none">&times;</button>
                </li>
            <?php endforeach; ?>
        </ul>
        <form class="add-area-form flex gap-2" data-zone-id="<?= $zone['id'] ?>">
            <input type="text" name="name" placeholder="Nombre del hotel nuevo" required class="input-field flex-1 max-w-sm text-sm">
            <button type="submit" class="btn-outline-navy text-sm">+ Agregar</button>
        </form>
    </div>
</section>
<?php endforeach; ?>

<script>
function showFeedback(msg, ok) {
    const el = document.getElementById('feedback');
    el.textContent = msg;
    el.classList.remove('hidden');
    el.className = 'px-4 py-2.5 rounded-lg mb-4 text-sm ' + (ok ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700');
    setTimeout(() => { el.classList.add('hidden'); }, 3500);
}

async function postAction(data) {
    const res = await fetch('/api/admin_pricing.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(data),
    });
    return res.json();
}

document.getElementById('addZoneForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const name = form.name.value.trim();
    if (!name) return;

    const result = await postAction({ action: 'add_zone', name });
    if (result.ok) {
        showFeedback('Zona creada. Recargando...', true);
        setTimeout(() => location.reload(), 800);
    } else {
        showFeedback('Error: ' + result.error, false);
    }
});

document.querySelectorAll('.zone-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const zoneId = form.dataset.zoneId;
        const statusEl = form.querySelector('.save-status');
        statusEl.textContent = 'Guardando...';
        statusEl.className = 'save-status text-sm text-gray-500';

        const data = {
            action: 'update_zone',
            id: zoneId,
            one_way_price: form.one_way_price.value,
            round_trip_price: form.round_trip_price.value,
            display_order: form.display_order.value,
            badge_text: form.badge_text.value,
            badge_class: form.badge_class.value,
            hotels_summary: form.hotels_summary.value,
        };
        if (form.is_featured.checked) data.is_featured = '1';
        if (form.is_active.checked) data.is_active = '1';

        const result = await postAction(data);
        if (result.ok) {
            statusEl.textContent = 'Guardado';
            statusEl.className = 'save-status text-sm text-emerald-600';
        } else {
            statusEl.textContent = 'Error: ' + result.error;
            statusEl.className = 'save-status text-sm text-red-600';
        }
        setTimeout(() => { statusEl.textContent = ''; }, 3000);
    });
});

document.querySelectorAll('.add-area-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const zoneId = form.dataset.zoneId;
        const name = form.name.value.trim();
        if (!name) return;

        const result = await postAction({ action: 'add_area', zone_id: zoneId, name });
        if (result.ok) {
            const li = document.createElement('li');
            li.dataset.areaId = result.id;
            li.className = 'bg-gray-100 px-3 py-1.5 rounded-full text-sm flex items-center gap-2';
            li.innerHTML = '<span class="area-name"></span><button type="button" class="delete-area text-red-500 hover:text-red-700 font-bold leading-none">&times;</button>';
            li.querySelector('.area-name').textContent = name;
            form.closest('.areas-block').querySelector('.areas-list').appendChild(li);
            form.name.value = '';
            attachDeleteHandler(li.querySelector('.delete-area'));
            showFeedback('Hotel agregado', true);
        } else {
            showFeedback('Error: ' + result.error, false);
        }
    });
});

function attachDeleteHandler(btn) {
    btn.addEventListener('click', async () => {
        const li = btn.closest('li');
        const areaId = li.dataset.areaId;
        if (!confirm('Borrar este hotel/area?')) return;

        const result = await postAction({ action: 'delete_area', id: areaId });
        if (result.ok) {
            li.remove();
            showFeedback('Hotel eliminado', true);
        } else {
            showFeedback('Error: ' + result.error, false);
        }
    });
}
document.querySelectorAll('.delete-area').forEach(attachDeleteHandler);

document.querySelectorAll('.delete-zone').forEach(btn => {
    btn.addEventListener('click', async () => {
        const zoneId = btn.dataset.zoneId;
        const zoneName = btn.dataset.zoneName;
        if (!confirm('Borrar la zona "' + zoneName + '" y todos sus hoteles? Las reservas ya hechas NO se borran, solo pierden el vinculo.')) return;

        const result = await postAction({ action: 'delete_zone', id: zoneId });
        if (result.ok) {
            btn.closest('section').remove();
            showFeedback('Zona eliminada', true);
        } else {
            showFeedback('Error: ' + result.error, false);
        }
    });
});
</script>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
