/**
 * CaboBayCarousel — factory function, una instancia por carrusel
 * @param {object} opts
 * @param {HTMLElement} opts.track       - .carousel-track
 * @param {HTMLElement} opts.prevBtn
 * @param {HTMLElement} opts.nextBtn
 * @param {HTMLElement} opts.dotsEl      - .carousel-indicators
 * @param {boolean}     [opts.autoplay]  - solo testimonials
 */
function CaboBayCarousel({ track, prevBtn, nextBtn, dotsEl, autoplay = false }) {

  const cards = track.querySelectorAll('.card');
  if (!cards.length) return;

  let idx      = 0;
  let autoTimer = null;

  /* ── helpers ── */

  function cardWidth() {
    return cards[0].getBoundingClientRect().width;
  }

  function gap() {
    return parseFloat(getComputedStyle(track).gap) || 0;
  }

  function visibleCount() {
    const w   = track.parentElement.offsetWidth;
    const cw  = cardWidth();
    const g   = gap();
    return Math.max(1, Math.floor((w + g) / (cw + g)));
  }

  function maxIndex() {
    return Math.max(0, cards.length - visibleCount());
  }

  /* ── move ── */

  function go(n) {
    idx = Math.min(Math.max(n, 0), maxIndex());

    track.style.transform =
      `translateX(${-(idx * (cardWidth() + gap()))}px)`;

    if (prevBtn) prevBtn.disabled = idx === 0;
    if (nextBtn) nextBtn.disabled = idx >= maxIndex();

    updateDots();
  }

  /* ── indicators ── */

  function buildDots() {
    dotsEl.innerHTML = '';
    const pages = Math.ceil(cards.length / visibleCount());

    for (let i = 0; i < pages; i++) {
      const btn = document.createElement('button');
      btn.className   = 'carousel-indicator' + (i === 0 ? ' active' : '');
      btn.setAttribute('aria-label', `Página ${i + 1}`);
      btn.addEventListener('click', () => go(i * visibleCount()));
      dotsEl.appendChild(btn);
    }
  }

  function updateDots() {
    const page = Math.floor(idx / visibleCount());
    dotsEl.querySelectorAll('.carousel-indicator')
      .forEach((d, i) => d.classList.toggle('active', i === page));
  }

  /* ── autoplay ── */

  function startAutoplay() {
    if (!autoplay) return;
    clearInterval(autoTimer);
    autoTimer = setInterval(() => {
      go(idx >= maxIndex() ? 0 : idx + 1);
    }, 4500);
  }

  function pauseAutoplay() {
    clearInterval(autoTimer);
  }

  /* ── events ── */

  prevBtn?.addEventListener('click', () => { go(idx - 1); startAutoplay(); });
  nextBtn?.addEventListener('click', () => { go(idx + 1); startAutoplay(); });

  if (autoplay) {
    track.addEventListener('mouseenter', pauseAutoplay);
    track.addEventListener('mouseleave', startAutoplay);
  }

  /* touch / drag */
  let startX = 0, currentX = 0, dragging = false;

  track.addEventListener('touchstart', (e) => {
    startX   = e.touches[0].clientX;
    currentX = startX;
    dragging = true;
    track.style.transition = 'none';
  }, { passive: true });

  track.addEventListener('touchmove', (e) => {
    if (!dragging) return;
    currentX = e.touches[0].clientX;
    const base = -(idx * (cardWidth() + gap()));
    track.style.transform = `translateX(${base - (startX - currentX)}px)`;
  }, { passive: true });

  track.addEventListener('touchend', () => {
    if (!dragging) return;
    dragging = false;
    track.style.transition = '';
    const diff = startX - currentX;
    if      (diff >  50) go(idx + 1);
    else if (diff < -50) go(idx - 1);
    else                 go(idx);
    startAutoplay();
  });

  /* resize */
  window.addEventListener('resize', () => {
    buildDots();
    go(Math.min(idx, maxIndex()));
  });

  /* ── init ── */
  buildDots();
  go(0);
  startAutoplay();
}

/* ── auto-init en DOMContentLoaded ── */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-carousel]').forEach(carousel => {
    const section = carousel.closest('.carousel-section');

    CaboBayCarousel({
      track:    carousel.querySelector('.carousel-track'),
      prevBtn:  section?.querySelector('.carousel-btn-prev'),
      nextBtn:  section?.querySelector('.carousel-btn-next'),
      dotsEl:   section?.querySelector('.carousel-indicators'),
      autoplay: carousel.id === 'testimonialCarousel',
    });
  });
});