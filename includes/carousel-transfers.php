<?php
/**
 * includes/carousel-transfers.php
 * Rutas más populares — Los Cabos Airport Transfers
 * Vehículo único: Luxury SUV (hasta 7 pasajeros)
 *
 * image_local  → foto propia en assets/media/transfers/  (prioridad)
 * image_remote → Unsplash fallback hasta que tengas fotos propias
 *
 * Criterio de selección de rutas:
 *  1. Cabo San Lucas  — mayor volumen de turismo, más hoteles
 *  2. The Corridor    — resorts 5★, turismo de lujo
 *  3. San José del Cabo — segunda ciudad principal
 *  4. Pacific Zone    — Pueblo Bonito Pacifica/Sunset, muy solicitados
 *  5. Diamante        — Nobu + Hard Rock, clientela premium
 *  6. Villas          — Villa el Palmar/Valencia, grupos privados
 */

$popular_routes = [
    [
        'zone'         => 'Cabo San Lucas',
        'slug'         => 'cabo-san-lucas',
        'badge'        => 'Most popular',
        'badge_class'  => 'badge-popular',
        'featured'     => true,
        'hotels'       => 'ME Cabo, Waldorf Astoria, Pueblo Bonito, Sandos Finisterra, Grand Solmar…',
        'one_way'      => 85,
        'round_trip'   => 160,
        'image_local'  => 'assets/media/transfers/cabo-san-lucas.jpg',
        'image_remote' => 'https://images.pexels.com/photos/22912077/pexels-photo-22912077.jpeg',
        'image_alt'    => 'Marina de Cabo San Lucas al atardecer',
    ],
    [
        'zone'         => 'The Corridor',
        'slug'         => 'corridor',
        'badge'        => 'Top rated',
        'badge_class'  => 'badge-top',
        'featured'     => false,
        'hotels'       => 'One&Only Palmilla, Montage, Las Ventanas al Paraíso, Garza Blanca…',
        'one_way'      => 75,
        'round_trip'   => 140,
        'image_local'  => 'assets/media/transfers/corridor.jpg',
        'image_remote' => 'https://images.pexels.com/photos/36864765/pexels-photo-36864765.jpeg',
        'image_alt'    => 'Costa del Corredor con resorts frente al mar',
    ],
    [
        'zone'         => 'San José del Cabo',
        'slug'         => 'san-jose',
        'badge'        => 'Best value',
        'badge_class'  => 'badge-value',
        'featured'     => false,
        'hotels'       => 'Hyatt Ziva, JW Marriott, Secrets Puerto Los Cabos, Royal Solaris…',
        'one_way'      => 65,
        'round_trip'   => 120,
        'image_local'  => 'assets/media/transfers/san-jose.jpg',
        'image_remote' => 'https://images.unsplash.com/photo-1512813195386-6cf811ad3542?w=640&q=80&fit=crop',
        'image_alt'    => 'Centro histórico de San José del Cabo',
    ],
    [
        'zone'         => 'Pacific Zone',
        'slug'         => 'pacific',
        'badge'        => 'Scenic route',
        'badge_class'  => 'badge-scenic',
        'featured'     => false,
        'hotels'       => 'Pueblo Bonito Pacifica, Pueblo Bonito Sunset Beach, Diamante Quivira…',
        'one_way'      => 90,
        'round_trip'   => 160,
        'image_local'  => 'assets/media/transfers/pacific.jpg',
        'image_remote' => 'https://images.pexels.com/photos/13201409/pexels-photo-13201409.jpeg',
        'image_alt'    => 'Playa del Pacífico con olas y acantilados',
    ],
    [
        'zone'         => 'Diamante',
        'slug'         => 'diamante',
        'badge'        => 'Premium',
        'badge_class'  => 'badge-premium',
        'featured'     => false,
        'hotels'       => 'Nobu Hotel Los Cabos, Hard Rock Los Cabos…',
        'one_way'      => 95,
        'round_trip'   => 180,
        'image_local'  => 'assets/media/transfers/diamante.jpg',
        'image_remote' => 'https://images.pexels.com/photos/17415445/pexels-photo-17415445.jpeg',
        'image_alt'    => 'Resort de lujo en Diamante Los Cabos',
    ],
    [
        'zone'         => 'Villas',
        'slug'         => 'villas',
        'badge'        => 'Private',
        'badge_class'  => 'badge-private',
        'featured'     => false,
        'hotels'       => 'Villa el Palmar, Villa Valencia, Villa el Arco',
        'one_way'      => 80,
        'round_trip'   => 160,
        'image_local'  => 'assets/media/transfers/villas.jpg',
        'image_remote' => 'https://images.pexels.com/photos/19168388/pexels-photo-19168388.jpeg',
        'image_alt'    => 'Villa privada con alberca y vista al mar',
    ],
];

/**
 * Devuelve URL de imagen: local si existe, remota como fallback.
 */
function route_image(array $route): string {
    if (!empty($route['image_local']) && file_exists($route['image_local'])) {
        return htmlspecialchars($route['image_local']);
    }
    return htmlspecialchars($route['image_remote']);
}
?>

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

            <article class="card transfer-card<?= $route['featured'] ? ' transfer-card--featured' : '' ?>"
                     data-slug="<?= htmlspecialchars($route['slug']) ?>"
                     data-one-way="<?= (int)$route['one_way'] ?>"
                     data-round-trip="<?= (int)$route['round_trip'] ?>">

              <div class="card-image">
                <img
                  src="<?= route_image($route) ?>"
                  alt="<?= htmlspecialchars($route['image_alt']) ?>"
                  loading="lazy"
                  width="640"
                  height="360"
                >
                <span class="card-badge <?= htmlspecialchars($route['badge_class']) ?>">
                  <?= htmlspecialchars($route['badge']) ?>
                </span>
              </div>

              <div class="card-content">
                <h3 class="card-title"><?= htmlspecialchars($route['zone']) ?></h3>
                <p class="card-hotels"><?= htmlspecialchars($route['hotels']) ?></p>

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
                    <span class="card-price js-price">$<?= (int)$route['one_way'] ?></span>
                    <small class="card-price-note">per SUV</small>
                  </div>
                  <a href="pages/booking.php?zone=<?= urlencode($route['zone']) ?>&type=oneway&price=<?= (int)$route['one_way'] ?>"
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
        var oneWay    = parseInt(card.dataset.oneWay, 10);
        var roundTrip = parseInt(card.dataset.roundTrip, 10);
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