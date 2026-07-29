<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';

$pdo = Database::getConnection();
$photos = $pdo->query('SELECT * FROM gallery_photos ORDER BY display_order ASC')->fetchAll();

$pageTitle = 'Galeria / Carrusel';
require __DIR__ . '/includes/admin-header.php';
?>

<p class="text-sm text-gray-500 mb-5">
    Las fotos y videos que subas aca (y que esten "Activa") aparecen en el carrusel de galeria de la pagina principal,
    en el orden que definas con las flechas. Los tamanos varian automaticamente para dar efecto de collage.
</p>

<div id="feedback" class="hidden px-4 py-2.5 rounded-lg mb-4 text-sm"></div>

<section class="card mb-5 bg-gray-50">
    <h4 class="text-sm font-semibold text-navy mb-3">Subir foto, GIF o video</h4>
    <form id="uploadForm" enctype="multipart/form-data" class="flex gap-3 flex-wrap items-end">
        <div>
            <label class="label-sm">Archivo (JPG, PNG, WEBP, GIF o MP4, max 20MB)</label>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif,video/mp4" required class="text-sm">
        </div>
        <div>
            <label class="label-sm">Descripcion (opcional)</label>
            <input type="text" name="caption" placeholder="Ej: Atardecer en Cabo San Lucas" class="input-field min-w-56">
        </div>
        <button type="submit" id="uploadBtn" class="btn-primary">Subir</button>
    </form>
</section>

<section class="card">
    <div id="galleryGrid" class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">
        <?php foreach ($photos as $p): ?>
            <div class="photo-card border border-gray-200 rounded-lg overflow-hidden <?= !$p['is_active'] ? 'opacity-50' : '' ?>" data-id="<?= $p['id'] ?>">
                <?php if ($p['media_type'] === 'video'): ?>
                    <video src="/assets/media/gallery/<?= htmlspecialchars($p['filename']) ?>" class="w-full h-36 object-cover block" muted loop autoplay playsinline></video>
                <?php else: ?>
                    <img src="/assets/media/gallery/<?= htmlspecialchars($p['filename']) ?>" alt="<?= htmlspecialchars($p['alt_text'] ?? '') ?>"
                         class="w-full h-36 object-cover block">
                <?php endif; ?>
                <div class="p-2.5">
                    <p class="text-[10px] uppercase tracking-wide text-gray-400 mb-1"><?= $p['media_type'] === 'video' ? 'Video' : 'Imagen' ?></p>
                    <p class="text-xs text-gray-600 mb-2 min-h-4"><?= htmlspecialchars($p['caption'] ?? '') ?></p>
                    <div class="flex gap-1.5 items-center justify-between">
                        <div class="flex gap-1">
                            <button type="button" class="move-btn text-xs border border-gray-200 rounded px-2 py-0.5 hover:bg-gray-50" data-dir="up" title="Mover antes">Arriba</button>
                            <button type="button" class="move-btn text-xs border border-gray-200 rounded px-2 py-0.5 hover:bg-gray-50" data-dir="down" title="Mover despues">Abajo</button>
                        </div>
                        <label class="text-xs flex items-center gap-1 text-gray-600">
                            <input type="checkbox" class="toggle-active" <?= $p['is_active'] ? 'checked' : '' ?>>
                            Activa
                        </label>
                        <button type="button" class="delete-photo text-xs text-red-500 hover:text-red-700">Borrar</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (!$photos): ?>
        <p class="text-center text-gray-400 py-10">Todavia no subiste nada.</p>
    <?php endif; ?>
</section>

<script>
function showFeedback(msg, ok) {
    const el = document.getElementById('feedback');
    el.textContent = msg;
    el.classList.remove('hidden');
    el.className = 'px-4 py-2.5 rounded-lg mb-4 text-sm ' + (ok ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700');
    setTimeout(() => { el.classList.add('hidden'); }, 3500);
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
            showFeedback('Subido correctamente', true);
            location.reload();
        } else {
            showFeedback('Error: ' + result.error, false);
        }
    } catch (err) {
        showFeedback('Error de red al subir el archivo', false);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Subir';
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
        if (!confirm('Borrar este archivo? No se puede deshacer.')) return;
        const card = btn.closest('.photo-card');
        const id = card.dataset.id;
        const result = await postAction({ action: 'delete', id });
        if (result.ok) {
            card.remove();
            showFeedback('Eliminado', true);
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
            card.classList.toggle('opacity-50', !cb.checked);
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
