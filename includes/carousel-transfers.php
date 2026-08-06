<?php
require_once __DIR__ . '/../config/database.php';

$remote_fallbacks = [
    'cabo-san-lucas' => 'https://images.pexels.com/photos/22912077/pexels-photo-22912077.jpeg?auto=compress&cs=tinysrgb&w=640',
    'corridor'       => 'https://images.pexels.com/photos/36864765/pexels-photo-36864765.jpeg?auto=compress&cs=tinysrgb&w=640',
    'san-jose'       => 'https://images.unsplash.com/photo-1512813195386-6cf811ad3542?w=640&q=80&fit=crop&auto=format',
    'pacific'        => 'https://images.pexels.com/photos/13201409/pexels-photo-13201409.jpeg?auto=compress&cs=tinysrgb&w=640',
    'diamante'       => 'https://images.pexels.com/photos/17415445/pexels-photo-17415445.jpeg?auto=compress&cs=tinysrgb&w=640',
    'villas'         => 'https://images.pexels.com/photos/19168388/pexels-photo-19168388.jpeg?auto=compress&cs=tinysrgb&w=640',
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
}

function route_image_db(array $route, array $remote_fallbacks): string {
    if (!empty($route['image_path']) && file_exists(__DIR__ . '/../' . $route['image_path'])) {
        return htmlspecialchars($route['image_path']);
    }
    $fallback = $remote_fallbacks[$route['slug']] ?? 'https://images.pexels.com/photos/22912077/pexels-photo-22912077.jpeg?auto=compress&cs=tinysrgb&w=640';
    return htmlspecialchars($fallback);
}

$badgeColors = [
    'badge-popular' => 'bg-coral text-white',
    'badge-top'     => 'bg-navy text-white',
    'badge-value'   => 'bg-emerald-600 text-white',
    'badge-scenic'  => 'bg-sky-600 text-white',
    'badge-premium' => 'bg-amber-500 text-white',
    'badge-private' => 'bg-slate-700 text-white',
];
?>

<?php if ($popular_routes): ?>
<section class="py-20" id="transfers">
  <div class="max-w-7xl mx-auto px-6">

    <p class="text-coral text-sm font-semibold uppercase tracking-wider mb-2">Most popular routes</p>
    <h2 class="font-serif text-4xl text-navy font-semibold mb-2">Airport transfers</h2>
    <p class="text-slate-500 mb-10">Los Cabos Airport &middot; Luxury SUV &middot; Up to 7 passengers</p>

    <div class="snap-row snap-fade-edges no-scrollbar gap-6 pb-4 -mx-6 px-6">
      <?php foreach ($popular_routes as $route): ?>
        <article class="snap-item transfer-card w-72 sm:w-80 rounded-2xl overflow-hidden bg-white border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                 data-slug="<?= htmlspecialchars($route['slug']) ?>"
                 data-one-way="<?= (float)$route['one_way_price'] ?>"
                 data-round-trip="<?= (float)$route['round_trip_price'] ?>">

          <div class="relative h-52 overflow-hidden">
            <img
              src="<?= route_image_db($route, $remote_fallbacks) ?>"
              alt="<?= htmlspecialchars($route['name']) ?> transfer"
              loading="lazy"
              class="w-full h-full object-cover"
            >
            <?php if (!empty($route['badge_text'])): ?>
            <span class="absolute top-3 left-3 text-xs font-semibold px-3 py-1 rounded-full <?= $badgeColors[$route['badge_class']] ?? 'bg-navy text-white' ?>">
              <?= htmlspecialchars($route['badge_text']) ?>
            </span>
            <?php endif; ?>
          </div>

          <div class="p-5">
            <h3 class="font-semibold text-lg text-navy mb-1"><?= htmlspecialchars($route['name']) ?></h3>
            <p class="text-xs text-slate-500 mb-4 line-clamp-2"><?= htmlspecialchars($route['hotels_summary'] ?? '') ?></p>

            <div class="flex bg-slate-100 rounded-full p-1 mb-4 text-xs">
              <input type="radio" name="trip-type-<?= htmlspecialchars($route['slug']) ?>" id="one-way-<?= htmlspecialchars($route['slug']) ?>" value="oneway" checked class="hidden peer/ow">
              <label for="one-way-<?= htmlspecialchars($route['slug']) ?>" class="flex-1 text-center py-1.5 rounded-full font-medium cursor-pointer peer-checked/ow:bg-navy peer-checked/ow:text-white transition-colors">One way</label>
              <input type="radio" name="trip-type-<?= htmlspecialchars($route['slug']) ?>" id="round-trip-<?= htmlspecialchars($route['slug']) ?>" value="roundtrip" class="hidden peer/rt">
              <label for="round-trip-<?= htmlspecialchars($route['slug']) ?>" class="flex-1 text-center py-1.5 rounded-full font-medium cursor-pointer peer-checked/rt:bg-navy peer-checked/rt:text-white transition-colors">Round trip</label>
            </div>

            <div class="flex items-end justify-between">
              <div>
                <span class="js-price text-2xl font-bold text-navy">$<?= (int)$route['one_way_price'] ?></span>
                <span class="text-xs text-slate-400 block">per SUV</span>
              </div>
              <a href="/pages/booking.php?zone=<?= urlencode($route['name']) ?>&type=oneway&price=<?= (int)$route['one_way_price'] ?>"
                 class="js-book-link border border-navy text-navy text-sm font-medium px-4 py-2 rounded-lg hover:bg-navy hover:text-white transition-colors">
                Book now
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<script>
(function () {
    document.querySelectorAll('#transfers .transfer-card').forEach(function (card) {
        var oneWay    = parseFloat(card.dataset.oneWay);
        var roundTrip = parseFloat(card.dataset.roundTrip);
        var zone      = card.querySelector('h3').textContent.trim();
        var priceEl   = card.querySelector('.js-price');
        var linkEl    = card.querySelector('.js-book-link');

        card.querySelectorAll('input[type="radio"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var isOneWay = this.value === 'oneway';
                var price    = isOneWay ? oneWay : roundTrip;
                priceEl.textContent = '$' + price;
                linkEl.href = '/pages/booking.php'
                    + '?zone='  + encodeURIComponent(zone)
                    + '&type='  + this.value
                    + '&price=' + price;
            });
        });
    });
}());
</script>
<?php endif; ?>
