<header class="navbar fixed top-0 left-0 w-full z-[1000] px-6 lg:px-10 py-4 flex justify-between items-center transition-all duration-300">
  <a href="/index.php" class="logo font-serif text-2xl font-semibold text-white tracking-tight">Cabo Bay</a>

  <nav class="hidden md:flex items-center gap-6 lg:gap-8">
    <a href="/index.php" class="text-white text-sm font-medium relative group">
      Home
      <span class="absolute -bottom-1 left-0 w-0 h-px bg-white transition-all duration-300 group-hover:w-full"></span>
    </a>
    <a href="/index.php#transfers" class="text-white text-sm font-medium relative group">
      Transfers
      <span class="absolute -bottom-1 left-0 w-0 h-px bg-white transition-all duration-300 group-hover:w-full"></span>
    </a>
    <a href="/pages/wedding.php" class="text-white text-sm font-medium relative group">
      Weddings
      <span class="absolute -bottom-1 left-0 w-0 h-px bg-white transition-all duration-300 group-hover:w-full"></span>
    </a>
    <a href="/index.php#contact" class="text-white text-sm font-medium relative group">
      Contact
      <span class="absolute -bottom-1 left-0 w-0 h-px bg-white transition-all duration-300 group-hover:w-full"></span>
    </a>
  </nav>

  <button id="navToggle" class="md:hidden text-white p-2 -mr-2" aria-label="Abrir menu">
    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
      <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
  </button>
</header>

<div id="mobileMenu" class="fixed inset-0 bg-navy-dark z-[1100] hidden flex-col items-center justify-center gap-8 md:hidden">
  <button id="navClose" class="absolute top-5 right-6 text-white p-2" aria-label="Cerrar menu">
    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
      <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
  </button>
  <a href="/index.php" class="mobile-nav-link text-white text-2xl font-serif font-medium">Home</a>
  <a href="/index.php#transfers" class="mobile-nav-link text-white text-2xl font-serif font-medium">Transfers</a>
  <a href="/pages/wedding.php" class="mobile-nav-link text-white text-2xl font-serif font-medium">Weddings</a>
  <a href="/index.php#contact" class="mobile-nav-link text-white text-2xl font-serif font-medium">Contact</a>
</div>
