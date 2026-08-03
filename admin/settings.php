<?php
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/booking-helpers.php';

$currentRate = get_usd_to_mxn_rate();

$pageTitle = 'Configuracion de pagos';
require __DIR__ . '/includes/admin-header.php';
?>

<div id="feedback" class="hidden px-4 py-2.5 rounded-lg mb-4 text-sm"></div>

<section class="card max-w-lg">
    <h2 class="text-lg font-semibold text-navy mb-2">Tipo de cambio USD a MXN</h2>
    <p class="text-sm text-gray-500 mb-5">
        Tus precios se muestran en USD, pero Mercado Pago cobra en MXN. Este numero es el que se usa
        para calcular cuantos pesos se cobran por cada pago con tarjeta. Actualizalo cuando quieras
        reflejar el tipo de cambio del dia.
    </p>

    <form id="rateForm" class="flex items-end gap-3">
        <div class="flex-1">
            <label class="label-sm">1 USD equivale a (MXN)</label>
            <input type="number" step="0.01" min="0.01" name="rate" value="<?= htmlspecialchars($currentRate) ?>"
                   class="input-field text-lg font-semibold">
        </div>
        <button type="submit" class="btn-primary">Guardar</button>
    </form>

    <p class="text-xs text-gray-400 mt-4">
        Ejemplo: si una reserva cuesta $100 USD y el tipo de cambio es <?= htmlspecialchars($currentRate) ?>,
        se cobrarian $<?= number_format(100 * $currentRate, 2) ?> MXN por tarjeta.
    </p>
</section>

<script>
function showFeedback(msg, ok) {
    const el = document.getElementById('feedback');
    el.textContent = msg;
    el.classList.remove('hidden');
    el.className = 'px-4 py-2.5 rounded-lg mb-4 text-sm ' + (ok ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700');
    setTimeout(() => { el.classList.add('hidden'); }, 3500);
}

document.getElementById('rateForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const rate = form.rate.value;

    const res = await fetch('/api/admin_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'update_rate', rate }),
    });
    const result = await res.json();

    if (result.ok) {
        showFeedback('Tipo de cambio actualizado', true);
    } else {
        showFeedback('Error: ' + result.error, false);
    }
});
</script>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
