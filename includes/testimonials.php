<?php
$testimonials = [
    ['name' => 'Maria G.',  'initials' => 'MG', 'location' => 'Mexico', 'date' => 'Marzo 2026',   'rating' => 5, 'comment' => 'Excelente servicio, el conductor nos espero con letrero y el vehiculo impecable.', 'avatar' => __DIR__ . '/../assets/media/images/avatars/maria.jpg'],
    ['name' => 'John D.',   'initials' => 'JD', 'location' => 'USA',    'date' => 'Febrero 2026', 'rating' => 5, 'comment' => 'The best transfer service in Los Cabos! Punctual, clean, and truly professional.', 'avatar' => __DIR__ . '/../assets/media/images/avatars/john.jpg'],
    ['name' => 'Laura K.',  'initials' => 'LK', 'location' => 'Canada', 'date' => 'Enero 2026',   'rating' => 5, 'comment' => 'Traslado muy comodo, vehiculo impecable. Volveremos a usarlo sin duda alguna.', 'avatar' => __DIR__ . '/../assets/media/images/avatars/laura.jpg'],
];
?>

<section class="py-20" id="testimonials pb-60 md:pb-60">
  <div class="max-w-7xl mx-auto px-6">

    <p class="text-coral text-sm font-semibold uppercase tracking-wider mb-2">TripAdvisor reviews</p>
    <h2 class="font-serif text-4xl text-navy font-semibold mb-2">What our clients say</h2>
    <p class="text-slate-500 mb-10">Real experiences, real people</p>

    <div class="snap-row snap-fade-edges no-scrollbar gap-6 pb-4 -mx-6 px-6">
      <?php foreach ($testimonials as $t): ?>
        <article class="snap-item w-80 sm:w-96 rounded-2xl border border-slate-100 shadow-sm p-6 bg-white">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-11 h-11 rounded-full bg-navy/10 text-navy font-semibold flex items-center justify-center overflow-hidden flex-shrink-0">
              <?php if (file_exists($t['avatar'])): ?>
                <img src="/assets/media/images/avatars/<?= htmlspecialchars(basename($t['avatar'])) ?>" alt="Foto de <?= htmlspecialchars($t['name']) ?>" class="w-full h-full object-cover">
              <?php else: ?>
                <span aria-hidden="true"><?= htmlspecialchars($t['initials']) ?></span>
              <?php endif; ?>
            </div>
            <div>
              <h4 class="font-semibold text-navy text-sm"><?= htmlspecialchars($t['name']) ?></h4>
              <p class="text-xs text-slate-400"><?= htmlspecialchars($t['location']) ?> - <?= htmlspecialchars($t['date']) ?></p>
            </div>
          </div>

          <div class="text-amber-400 text-sm mb-3" aria-label="<?= $t['rating'] ?> de 5 estrellas">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <span class="<?= $i <= $t['rating'] ? '' : 'text-slate-200' ?>">*</span>
            <?php endfor; ?>
          </div>

          <blockquote class="text-sm text-slate-600 leading-relaxed mb-4">
            <?= htmlspecialchars($t['comment']) ?>
          </blockquote>

          <div class="flex items-center gap-2 text-xs text-slate-400">
            <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#34e0a1"/><text x="12" y="16" text-anchor="middle" font-size="9" font-weight="bold" fill="#fff">TA</text></svg>
            <span>Verified on TripAdvisor</span>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

  </div>
</section>
