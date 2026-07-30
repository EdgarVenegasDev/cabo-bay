<?php $current = basename($_SERVER['SCRIPT_NAME']); ?>
<nav id="adminSidebar"
     class="fixed inset-y-0 left-0 z-50 w-64 bg-navy text-white py-6 flex-shrink-0
            transform -translate-x-full transition-transform duration-300 ease-in-out
            lg:static lg:translate-x-0 lg:w-56">
    <div class="flex items-center justify-between px-5 pb-6">
        <div class="font-bold text-lg tracking-tight">Cabo Bay Admin</div>
        <button id="sidebarClose" class="lg:hidden text-white/70 hover:text-white" aria-label="Cerrar menu">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>
    <ul class="space-y-1">
        <?php
        $links = [
            'dashboard.php' => 'Resumen',
            'bookings.php'  => 'Reservas',
            'pricing.php'   => 'Precios y viajes populares',
            'gallery.php'   => 'Galeria / Carrusel',
        ];
        foreach ($links as $file => $label):
            $isActive = $current === $file;
        ?>
        <li>
            <a href="/admin/<?= $file ?>"
               class="block px-5 py-3 text-sm transition-colors <?= $isActive ? 'bg-white/10 text-white font-medium border-l-2 border-coral' : 'text-slate-300 hover:bg-white/5 hover:text-white' ?>">
                <?= htmlspecialchars($label) ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>
