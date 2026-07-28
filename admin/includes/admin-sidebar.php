<?php $current = basename($_SERVER['SCRIPT_NAME']); ?>
<nav class="w-56 bg-navy text-white py-6 flex-shrink-0">
    <div class="font-bold px-5 pb-6 text-lg tracking-tight">Cabo Bay Admin</div>
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
