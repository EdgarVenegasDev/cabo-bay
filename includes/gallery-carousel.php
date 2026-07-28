<?php
/**
 * includes/gallery-carousel.php
 * Carrusel de fotos subidas por el admin (admin/gallery.php).
 */
require_once __DIR__ . '/../config/database.php';

$gallery_photos = [];
try {
    $pdo = Database::getConnection();
    $stmt = $pdo->query(
        'SELECT * FROM gallery_photos WHERE is_active = 1 ORDER BY display_order ASC'
    );
    $gallery_photos = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('[gallery-carousel load failed] ' . $e->getMessage());
}
?>

<?php if ($gallery_photos): ?>
<section class="carousel-section" id="gallery">
  <div class="container">

    <p class="section-eyebrow">Gallery</p>
    <h2 class="section-title">Los Cabos in pictures</h2>
    <p class="section-sub">A glimpse of the experiences waiting for you</p>

    <div class="carousel-container">
      <button class="carousel-btn-prev" type="button" aria-label="Anterior">&#8249;</button>

      <div class="carousel" id="galleryCarousel" data-carousel>
        <div class="carousel-track">
          <?php foreach ($gallery_photos as $photo): ?>
            <article class="card" style="width:300px; flex-shrink:0; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
              <img
                src="/assets/media/gallery/<?= htmlspecialchars($photo['filename']) ?>"
                alt="<?= htmlspecialchars($photo['alt_text'] ?? 'Cabo Bay gallery photo') ?>"
                loading="lazy"
                style="width:100%; height:220px; object-fit:cover; display:block;"
              >
              <?php if (!empty($photo['caption'])): ?>
                <p style="padding:10px 12px; margin:0; font-size:0.85rem; color:#475569;">
                    <?= htmlspecialchars($photo['caption']) ?>
                </p>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      </div>

      <button class="carousel-btn-next" type="button" aria-label="Siguiente">&#8250;</button>
    </div>

    <div class="carousel-indicators" role="tablist" aria-label="Gallery pages"></div>

  </div>
</section>
<?php endif; ?>
