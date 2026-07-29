<?php
/**
 * includes/gallery-carousel.php
 * Carrusel deslizable con tarjetas de tamano variado (efecto collage).
 * Soporta imagenes, GIFs (se muestran como img, animan solos) y videos MP4.
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

// Patron fijo de tamanos que se repite -> efecto collage sin layout shift entre cargas
$sizePattern = [
    'w-64 h-56',   // pequena
    'w-80 h-80',   // cuadrada mediana
    'w-72 h-96',   // alta/grande
    'w-72 h-64',   // mediana
    'w-96 h-72',   // ancha
];
?>

<?php if ($gallery_photos): ?>
<section class="py-20 bg-slate-50" id="gallery">
  <div class="max-w-7xl mx-auto px-6">

    <p class="text-coral text-sm font-semibold uppercase tracking-wider mb-2">Gallery</p>
    <h2 class="font-serif text-4xl text-navy font-semibold mb-2">Los Cabos in pictures</h2>
    <p class="text-slate-500 mb-10">A glimpse of the experiences waiting for you</p>

    <div class="snap-row snap-fade-edges no-scrollbar gap-5 pb-4 -mx-6 px-6 items-end">
      <?php foreach ($gallery_photos as $i => $photo): ?>
        <?php $size = $sizePattern[$i % count($sizePattern)]; ?>
        <article class="snap-item <?= $size ?> rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-shadow duration-300 bg-white relative">
          <?php if ($photo['media_type'] === 'video'): ?>
            <video
              src="/assets/media/gallery/<?= htmlspecialchars($photo['filename']) ?>"
              class="w-full h-full object-cover"
              autoplay muted loop playsinline
              aria-label="<?= htmlspecialchars($photo['alt_text'] ?? 'Cabo Bay gallery video') ?>"
            ></video>
          <?php else: ?>
            <img
              src="/assets/media/gallery/<?= htmlspecialchars($photo['filename']) ?>"
              alt="<?= htmlspecialchars($photo['alt_text'] ?? 'Cabo Bay gallery photo') ?>"
              loading="lazy"
              class="w-full h-full object-cover"
            >
          <?php endif; ?>
          <?php if (!empty($photo['caption'])): ?>
            <p class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent text-white text-xs px-3 py-2.5">
                <?= htmlspecialchars($photo['caption']) ?>
            </p>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>

  </div>
</section>
<?php endif; ?>
