window.addEventListener('scroll', () => {
  const navbar = document.querySelector('.navbar');
  if (!navbar) return;
  if (window.scrollY > 50) {
    navbar.classList.add('nav-scrolled');
  } else {
    navbar.classList.remove('nav-scrolled');
  }
});

(function () {
  const toggle = document.getElementById('navToggle');
  const closeBtn = document.getElementById('navClose');
  const menu = document.getElementById('mobileMenu');
  if (!toggle || !menu) return;

  function openMenu() {
    menu.classList.remove('hidden');
    menu.classList.add('flex');
    document.body.style.overflow = 'hidden';
  }
  function closeMenu() {
    menu.classList.add('hidden');
    menu.classList.remove('flex');
    document.body.style.overflow = '';
  }

  toggle.addEventListener('click', openMenu);
  closeBtn?.addEventListener('click', closeMenu);

  menu.querySelectorAll('.mobile-nav-link').forEach(link => {
    link.addEventListener('click', closeMenu);
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) closeMenu();
  });
})();
