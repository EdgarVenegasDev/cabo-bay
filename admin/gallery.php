<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';

$pdo = Database::getConnection();
$photos = $pdo->query('SELECT * FROM gallery_photos ORDER BY display_order ASC')->fetchAll();

$pageTitle = 'Galeria / Carrusel';
require __DIR__ . '/includes/admin-header.php';
?>

<p style="color:#666; margin-bottom:20px; font-size:14px;">
    Las fotos que subas aca (y que esten "Activa") aparecen en el carrusel de galeria de la pagina principal,
    en el orden que definas con las flechas.
</p>

<div id="feedback" style="display:none; padding:10px 16px; border-radius:8px; margin-bottom:16px; font-size:14px;"></div>

<section class="admin-table-wrap" style="margin-bottom:20px; background:#f8fafc;">
    <h4 style="margin-bottom:10px; font-size:14px;">Subir nueva foto</h4>
    <form id="uploadForm" enctype="multipart/form-data" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
        <div>
            <label style="font-size:12px; color:#666; display:block; margin-bottom:4px;">Archivo (JPG, PNG o WEBP, max 5MB)</label>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required>
        </div>
        <div>
            <label style="font-size:12px; color:#666; display:block; margin-bottom:4px;">Descripcion (opcional)</label>
            <input type="text" name="caption" placeholder="Ej: Atardecer en Cabo San Lucas"
                   style="padding:8px; border:1px solid #e1e4e8; border-radius:6px; min-width:220px;">
        </div>
        <button type="submit" id="uploadBtn" style="background:#0f2a3f; color:#fff; border:none; padding:9px 20px; border-radius:6px; cursor:pointer;">
            Subir foto
        </button>
    </form>
</section>

<section class="admin-table-wrap">
    <div id="galleryGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:16px;">
        <?php foreach ($photos as $p): ?>
            <div class="photo-card" data-id="<?= $p['id'] ?>" style="border:1px solid #e1e4e8; border-radius:10px; overflow:hidden; <?= !$p['is_active'] ? 'opacity:0.5;' : '' ?>">
                <img src="/assets/media/gallery/<?= htmlspecialchars($p['filename']) ?>" alt="<?= htmlspecialchars($p['alt_text'] ?? '') ?>"
                     style="width:100%; height:140px; object-fit:cover; display:block;">
                <div style="padding:10px;">
                    <p style="font-size:12px; margin:0 0 8px 0; color:#444; min-height:16px;"><?= htmlspecialchars($p['caption'] ?? '') ?></p>
                    <div style="display:flex; gap:6px; align-items:center; justify-content:space-between;">
                        <div style="display:flex; gap:4px;">
                            <button type="button" class="move-btn" data-dir="up" title="Mover antes" style="border:1px solid #e1e4e8; background:#fff; border-radius:4px; cursor:pointer; padding:2px 8px;">Arriba</button>
                            <button type="button" class="move-btn" data-dir="down" title="Mover despues" style="border:1px solid #e1e4e8; background:#fff; border-radius:4px; cursor:pointer; padding:2px 8px;">Abajo</button>
                        </div>
                        <label style="font-size:11px; display:flex; align-items:center; gap:4px;">
                            <input type="checkbox" class="toggle-active" <?= $p['is_active'] ? 'checked' : '' ?>>
                            Activa
                        </label>
                        <button type="button" class="delete-photo" style="border:none; background:none; color:#c0392b; cursor:pointer; font-size:12px;">Borrar</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (!$photos): ?>
        <p style="text-align:center; color:#888; padding:30px;">Todavia no subiste ninguna foto.</p>
    <?php endif; ?>
</section>

<script>
function showFeedback(msg, ok) {
    const el = document.getElementById('feedback');
    el.textContent = msg;
    el.style.display = 'block';
    el.style.background = ok ? '#d4edda' : '#f8d7da';
    el.style.color = ok ? '#155724' : '#721c24';
    setTimeout(() => { el.style.display = 'none'; }, 3500);
}

document.getElementById('uploadForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('uploadBtn');
    btn.disabled = true;
    btn.textContent = 'Subiendo...';

    const formData = new FormData(form);
    formData.append('action', 'upload');

    try {
        const res = await fetch('/api/admin_gallery.php', { method: 'POST', body: formData });
        const result = await res.json();

        if (result.ok) {
            showFeedback('Foto subida correctamente', true);
            location.reload();
        } else {
            showFeedback('Error: ' + result.error, false);
        }
    } catch (err) {
        showFeedback('Error de red al subir la foto', false);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Subir foto';
    }
});

async function postAction(data) {
    const res = await fetch('/api/admin_gallery.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(data),
    });
    return res.json();
}

document.querySelectorAll('.delete-photo').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('Borrar esta foto? No se puede deshacer.')) return;
        const card = btn.closest('.photo-card');
        const id = card.dataset.id;
        const result = await postAction({ action: 'delete', id });
        if (result.ok) {
            card.remove();
            showFeedback('Foto eliminada', true);
        } else {
            showFeedback('Error: ' + result.error, false);
        }
    });
});

document.querySelectorAll('.toggle-active').forEach(cb => {
    cb.addEventListener('change', async () => {
        const card = cb.closest('.photo-card');
        const id = card.dataset.id;
        const result = await postAction({ action: 'toggle_active', id });
        if (result.ok) {
            card.style.opacity = cb.checked ? '1' : '0.5';
            showFeedback('Actualizado', true);
        } else {
            cb.checked = !cb.checked;
            showFeedback('Error: ' + result.error, false);
        }
    });
});

document.querySelectorAll('.move-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const card = btn.closest('.photo-card');
        const id = card.dataset.id;
        const direction = btn.dataset.dir;
        const result = await postAction({ action: 'move', id, direction });
        if (result.ok) {
            location.reload();
        } else {
            showFeedback('Error: ' + result.error, false);
        }
    });
});
</script>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
