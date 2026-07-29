<?php
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
<section class="py-20 bg-slate-50" id="gallery">
  <div class="max-w-7xl mx-auto px-6">

    <p class="text-coral text-sm font-semibold uppercase tracking-wider mb-2">Gallery</p>
    <h2 class="font-serif text-4xl text-navy font-semibold mb-2">Los Cabos in pictures</h2>
    <p class="text-slate-500 mb-10">A glimpse of the experiences waiting for you</p>

    <div class="snap-row snap-fade-edges no-scrollbar gap-5 pb-4 -mx-6 px-6">
      <?php foreach ($gallery_photos as $photo): ?>
        <article class="snap-item w-72 sm:w-80 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-shadow duration-300 bg-white">
          <img
            src="/assets/media/gallery/<?= htmlspecialchars($photo['filename']) ?>"
            alt="<?= htmlspecialchars($photo['alt_text'] ?? 'Cabo Bay gallery photo') ?>"
            loading="lazy"
            class="w-full h-56 object-cover"
          >
          <?php if (!empty($photo['caption'])): ?>
            <p class="px-4 py-3 text-sm text-slate-500"><?= htmlspecialchars($photo['caption']) ?></p>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>

  </div>
</section>
<?php endif; ?>
