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

<p style="color:#666; margin-bottom:20px; font-size:14px;">
    Cada zona alimenta dos cosas al mismo tiempo: el selector de precios en el formulario de reserva del sitio,
    y (si esta marcada como "Destacar en home") el carrusel de "rutas mas populares" en la portada.
</p>

<div id="feedback" style="display:none; padding:10px 16px; border-radius:8px; margin-bottom:16px; font-size:14px;"></div>

<section class="admin-table-wrap" style="margin-bottom:20px; background:#f8fafc;">
    <h4 style="margin-bottom:10px; font-size:14px;">Agregar nueva zona</h4>
    <form id="addZoneForm" style="display:flex; gap:8px;">
        <input type="text" name="name" placeholder="Ej: Todos Santos Norte" required
               style="flex:1; max-width:320px; padding:8px 10px; border:1px solid #e1e4e8; border-radius:6px; font-size:13px;">
        <button type="submit" style="background:#0f2a3f; color:#fff; border:none; padding:8px 18px; border-radius:6px; cursor:pointer; font-size:13px;">
            + Crear zona
        </button>
    </form>
    <p style="font-size:12px; color:#888; margin-top:8px; margin-bottom:0;">
        Se crea con precios en $0 y sin destacar - editala abajo apenas aparezca en la lista (recarga la pagina).
    </p>
</section>

<?php foreach ($zones as $zone): ?>
<section class="admin-table-wrap" style="margin-bottom:20px;">
    <form class="zone-form" data-zone-id="<?= $zone['id'] ?>">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h2 style="margin:0;"><?= htmlspecialchars($zone['name']) ?></h2>
            <label style="font-size:13px; display:flex; align-items:center; gap:6px;">
                <input type="checkbox" name="is_active" <?= $zone['is_active'] ? 'checked' : '' ?>>
                Zona activa (visible en el sitio)
            </label>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:14px; margin-bottom:14px;">
            <div>
                <label style="font-size:12px; color:#666; display:block; margin-bottom:4px;">Precio One Way (USD)</label>
                <input type="number" step="0.01" min="0" name="one_way_price" value="<?= htmlspecialchars($zone['one_way_price']) ?>"
                       style="width:100%; padding:8px; border:1px solid #e1e4e8; border-radius:6px;">
            </div>
            <div>
                <label style="font-size:12px; color:#666; display:block; margin-bottom:4px;">Precio Round Trip (USD)</label>
                <input type="number" step="0.01" min="0" name="round_trip_price" value="<?= htmlspecialchars($zone['round_trip_price']) ?>"
                       style="width:100%; padding:8px; border:1px solid #e1e4e8; border-radius:6px;">
            </div>
            <div>
                <label style="font-size:12px; color:#666; display:block; margin-bottom:4px;">Orden en el carrusel</label>
                <input type="number" name="display_order" value="<?= (int)$zone['display_order'] ?>"
                       style="width:100%; padding:8px; border:1px solid #e1e4e8; border-radius:6px;">
            </div>
        </div>

        <div style="background:#f8fafc; border-radius:8px; padding:14px; margin-bottom:14px;">
            <label style="font-size:13px; display:flex; align-items:center; gap:6px; margin-bottom:10px; font-weight:600;">
                <input type="checkbox" name="is_featured" <?= $zone['is_featured'] ? 'checked' : '' ?>>
                Destacar en el carrusel de home ("viajes mas populares")
            </label>

            <div style="display:grid; grid-template-columns:1fr 1fr 2fr; gap:14px;">
                <div>
                    <label style="font-size:12px; color:#666; display:block; margin-bottom:4px;">Texto del badge</label>
                    <input type="text" name="badge_text" value="<?= htmlspecialchars($zone['badge_text'] ?? '') ?>"
                           placeholder="Ej: Most popular"
                           style="width:100%; padding:8px; border:1px solid #e1e4e8; border-radius:6px;">
                </div>
                <div>
                    <label style="font-size:12px; color:#666; display:block; margin-bottom:4px;">Clase del badge</label>
                    <input type="text" name="badge_class" value="<?= htmlspecialchars($zone['badge_class'] ?? '') ?>"
                           placeholder="Ej: badge-popular"
                           style="width:100%; padding:8px; border:1px solid #e1e4e8; border-radius:6px;">
                </div>
                <div>
                    <label style="font-size:12px; color:#666; display:block; margin-bottom:4px;">Resumen de hoteles (texto libre)</label>
                    <input type="text" name="hotels_summary" value="<?= htmlspecialchars($zone['hotels_summary'] ?? '') ?>"
                           placeholder="Ej: ME Cabo, Waldorf Astoria..."
                           style="width:100%; padding:8px; border:1px solid #e1e4e8; border-radius:6px;">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-sm" style="background:#0f2a3f; color:#fff; border:none; padding:8px 18px; border-radius:6px; cursor:pointer;">
            Guardar cambios
        </button>
        <button type="button" class="delete-zone" data-zone-id="<?= $zone['id'] ?>" data-zone-name="<?= htmlspecialchars($zone['name']) ?>"
                style="background:none; border:1px solid #c0392b; color:#c0392b; padding:8px 14px; border-radius:6px; cursor:pointer; margin-left:8px;">
            Borrar zona
        </button>
        <span class="save-status" style="margin-left:10px; font-size:13px;"></span>
    </form>

    <hr style="margin:20px 0; border:none; border-top:1px solid #e1e4e8;">

    <div class="areas-block" data-zone-id="<?= $zone['id'] ?>">
        <h4 style="margin-bottom:10px; font-size:14px;">Hoteles / areas de esta zona</h4>
        <ul class="areas-list" style="list-style:none; padding:0; margin:0 0 12px 0; display:flex; flex-wrap:wrap; gap:8px;">
            <?php foreach ($areasByZone[$zone['id']] ?? [] as $area): ?>
                <li data-area-id="<?= $area['id'] ?>"
                    style="background:#eef2f6; padding:6px 10px; border-radius:20px; font-size:13px; display:flex; align-items:center; gap:8px;">
                    <span class="area-name"><?= htmlspecialchars($area['name']) ?></span>
                    <button type="button" class="delete-area" style="border:none; background:none; color:#c0392b; cursor:pointer; font-weight:bold;">&times;</button>
                </li>
            <?php endforeach; ?>
        </ul>
        <form class="add-area-form" data-zone-id="<?= $zone['id'] ?>" style="display:flex; gap:8px;">
            <input type="text" name="name" placeholder="Nombre del hotel nuevo" required
                   style="flex:1; padding:6px 10px; border:1px solid #e1e4e8; border-radius:6px; font-size:13px;">
            <button type="submit" class="btn-link" style="background:none; border:1px solid #0f2a3f; color:#0f2a3f; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:13px;">
                + Agregar
            </button>
        </form>
    </div>
</section>
<?php endforeach; ?>

<script>
function showFeedback(msg, ok) {
    const el = document.getElementById('feedback');
    el.textContent = msg;
    el.style.display = 'block';
    el.style.background = ok ? '#d4edda' : '#f8d7da';
    el.style.color = ok ? '#155724' : '#721c24';
    setTimeout(() => { el.style.display = 'none'; }, 3500);
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
            statusEl.style.color = '#155724';
        } else {
            statusEl.textContent = 'Error: ' + result.error;
            statusEl.style.color = '#721c24';
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
            li.style.cssText = 'background:#eef2f6; padding:6px 10px; border-radius:20px; font-size:13px; display:flex; align-items:center; gap:8px;';
            li.innerHTML = '<span class="area-name"></span><button type="button" class="delete-area" style="border:none; background:none; color:#c0392b; cursor:pointer; font-weight:bold;">&times;</button>';
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