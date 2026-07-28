<?php
/**
 * includes/carousel-transfers.php
 * Rutas más populares — ahora leídas desde la tabla `zones` (is_featured = 1)
 * en vez de un array hardcodeado. El admin controla esto desde admin/pricing.php.
 */
require_once __DIR__ . '/../config/database.php';

// Fallback de imágenes remotas (Pexels/Unsplash) por si todavía no subiste la foto local.
// Esto es solo diseño, no dato de negocio, por eso se queda como config estática acá.
$remote_fallbacks = [
    'cabo-san-lucas' => 'https://images.pexels.com/photos/22912077/pexels-photo-22912077.jpeg',
    'corridor'       => 'https://images.pexels.com/photos/36864765/pexels-photo-36864765.jpeg',
    'san-jose'       => 'https://images.unsplash.com/photo-1512813195386-6cf811ad3542?w=640&q=80&fit=crop',
    'pacific'        => 'https://images.pexels.com/photos/13201409/pexels-photo-13201409.jpeg',
    'diamante'       => 'https://images.pexels.com/photos/17415445/pexels-photo-17415445.jpeg',
    'villas'         => 'https://images.pexels.com/photos/19168388/pexels-photo-19168388.jpeg',
];

$popular_routes = [];
try {
    $pdo = Database::getConnection();
    $stmt = $pdo->query(
        'SELECT * FROM zones WHERE is_featured = 1 AND is_active = 1 ORDER BY display_order ASC'
    );
    $popular_routes = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('[carousel-transfers load failed] ' . $e->getMessage());
    // $popular_routes queda vacío; la sección se oculta sola más abajo.
}

/**
 * Devuelve URL de imagen: local si existe físicamente, remota como fallback.
 */
function route_image_db(array $route, array $remote_fallbacks): string {
    if (!empty($route['image_path']) && file_exists(__DIR__ . '/../' . $route['image_path'])) {
        return htmlspecialchars($route['image_path']);
    }
    $fallback = $remote_fallbacks[$route['slug']] ?? 'https://images.pexels.com/photos/22912077/pexels-photo-22912077.jpeg';
    return htmlspecialchars($fallback);
}
?>

<?php if ($popular_routes): ?>
<section class="carousel-section" id="transfers">
  <div class="container">

    <p class="section-eyebrow">Most popular routes</p>
    <h2 class="section-title">Airport transfers</h2>
    <p class="section-sub">Los Cabos Airport &middot; Luxury SUV &middot; Up to 7 passengers</p>

    <div class="carousel-container">
      <button class="carousel-btn-prev" type="button" aria-label="Anterior">&#8249;</button>

      <div class="carousel" id="transferCarousel" data-carousel>
        <div class="carousel-track">

          <?php foreach ($popular_routes as $route): ?>

            <article class="card transfer-card"
                     data-slug="<?= htmlspecialchars($route['slug']) ?>"
                     data-one-way="<?= (float)$route['one_way_price'] ?>"
                     data-round-trip="<?= (float)$route['round_trip_price'] ?>">

              <div class="card-image">
                <img
                  src="<?= route_image_db($route, $remote_fallbacks) ?>"
                  alt="<?= htmlspecialchars($route['name']) ?> transfer"
                  loading="lazy"
                  width="640"
                  height="360"
                >
                <?php if (!empty($route['badge_text'])): ?>
                <span class="card-badge <?= htmlspecialchars($route['badge_class'] ?? '') ?>">
                  <?= htmlspecialchars($route['badge_text']) ?>
                </span>
                <?php endif; ?>
              </div>

              <div class="card-content">
                <h3 class="card-title"><?= htmlspecialchars($route['name']) ?></h3>
                <p class="card-hotels"><?= htmlspecialchars($route['hotels_summary'] ?? '') ?></p>

                <!-- Toggle one way / round trip -->
                <div class="trip-toggle" role="group" aria-label="Trip type">
                  <input type="radio"
                         name="trip-type-<?= htmlspecialchars($route['slug']) ?>"
                         id="one-way-<?= htmlspecialchars($route['slug']) ?>"
                         value="oneway" checked>
                  <label class="toggle-btn" for="one-way-<?= htmlspecialchars($route['slug']) ?>">One way</label>

                  <input type="radio"
                         name="trip-type-<?= htmlspecialchars($route['slug']) ?>"
                         id="round-trip-<?= htmlspecialchars($route['slug']) ?>"
                         value="roundtrip">
                  <label class="toggle-btn" for="round-trip-<?= htmlspecialchars($route['slug']) ?>">Round trip</label>
                </div>

                <div class="card-footer">
                  <div class="card-price-wrap">
                    <span class="card-price js-price">$<?= (int)$route['one_way_price'] ?></span>
                    <small class="card-price-note">per SUV</small>
                  </div>
                  <a href="pages/booking.php?zone=<?= urlencode($route['name']) ?>&type=oneway&price=<?= (int)$route['one_way_price'] ?>"
                     class="btn btn-outline btn-sm js-book-link">
                    Book now
                  </a>
                </div>

              </div>
            </article>

          <?php endforeach; ?>
        </div>
      </div>

      <button class="carousel-btn-next" type="button" aria-label="Siguiente">&#8250;</button>
    </div>

    <div class="carousel-indicators" role="tablist" aria-label="Transfer routes pages"></div>

  </div>
</section>

<script>
(function () {
    document.querySelectorAll('#transferCarousel .transfer-card').forEach(function (card) {
        var oneWay    = parseFloat(card.dataset.oneWay);
        var roundTrip = parseFloat(card.dataset.roundTrip);
        var zone      = card.querySelector('.card-title').textContent.trim();
        var priceEl   = card.querySelector('.js-price');
        var linkEl    = card.querySelector('.js-book-link');

        card.querySelectorAll('input[type="radio"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var isOneWay = this.value === 'oneway';
                var price    = isOneWay ? oneWay : roundTrip;
                priceEl.textContent = '$' + price;
                linkEl.href = 'pages/booking.php'
                    + '?zone='  + encodeURIComponent(zone)
                    + '&type='  + this.value
                    + '&price=' + price;
            });
        });
    });
}());
</script>
<?php endif; ?>