<?php
$testimonials = [
    ['name' => 'María G.',  'initials' => 'MG', 'location' => 'México', 'date' => 'Marzo 2026',   'rating' => 5, 'comment' => 'Excelente servicio, el conductor nos esperó con letrero y el vehículo impecable.',     'avatar' => 'assets/media/images/avatars/maria.jpg'],
    ['name' => 'John D.',   'initials' => 'JD', 'location' => 'USA',    'date' => 'Febrero 2026', 'rating' => 5, 'comment' => 'The best transfer service in Los Cabos! Punctual, clean, and truly professional.',       'avatar' => 'assets/media/images/avatars/john.jpg'],
    ['name' => 'Laura K.',  'initials' => 'LK', 'location' => 'Canadá', 'date' => 'Enero 2026',   'rating' => 5, 'comment' => 'Traslado muy cómodo, vehículo impecable. Volveremos a usarlo sin duda alguna.',          'avatar' => 'assets/media/images/avatars/laura.jpg'],
];
?>

<section class="carousel-section" id="testimonials">
  <div class="container">

    <p class="section-eyebrow">TripAdvisor reviews</p>
    <h2 class="section-title">What our clients say</h2>
    <p class="section-sub">Real experiences, real people</p>

    <div class="carousel-container">
      <button class="carousel-btn-prev" type="button" aria-label="Anterior">&#8249;</button>

      <div class="carousel" id="testimonialCarousel" data-carousel>
        <div class="carousel-track">
          <?php foreach ($testimonials as $t): ?>

            <article class="card testimonial-card">
              <div class="testimonial-header">

                <div class="avatar">
                  <?php if (file_exists($t['avatar'])): ?>
                    <img
                      src="<?= htmlspecialchars($t['avatar']) ?>"
                      alt="Foto de <?= htmlspecialchars($t['name']) ?>"
                      loading="lazy"
                    >
                  <?php else: ?>
                    <span aria-hidden="true"><?= htmlspecialchars($t['initials']) ?></span>
                  <?php endif; ?>
                </div>

                <div class="testimonial-meta">
                  <h4 class="reviewer-name"><?= htmlspecialchars($t['name']) ?></h4>
                  <p class="reviewer-location"><?= htmlspecialchars($t['location']) ?></p>
                  <time class="reviewer-date"><?= htmlspecialchars($t['date']) ?></time>
                </div>

              </div>

              <div class="rating" aria-label="<?= $t['rating'] ?> de 5 estrellas">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <span class="star <?= $i <= $t['rating'] ? 'filled' : 'empty' ?>" aria-hidden="true">★</span>
                <?php endfor; ?>
              </div>

              <blockquote class="testimonial-comment">
                <?= htmlspecialchars($t['comment']) ?>
              </blockquote>

              <div class="tripadvisor-badge">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                  <circle cx="12" cy="12" r="10" fill="#34e0a1"/>
                  <text x="12" y="16" text-anchor="middle" font-size="9" font-weight="bold" fill="#fff">TA</text>
                </svg>
                <span>Verified on TripAdvisor</span>
              </div>
            </article>

          <?php endforeach; ?>
        </div>
      </div>

      <button class="carousel-btn-next" type="button" aria-label="Siguiente">&#8250;</button>
    </div>

    <div class="carousel-indicators" role="tablist" aria-label="Páginas de reseñas"></div>
  </div>
</section>