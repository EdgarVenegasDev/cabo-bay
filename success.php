<?php
session_start();
if (!isset($_SESSION['booking_success']) || $_SESSION['booking_success'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'config/config.php';

$data      = $_SESSION['booking_data'] ?? [];
$isWedding = ($data['type'] ?? '') === 'wedding';

unset($_SESSION['booking_success'], $_SESSION['booking_data']);

function fmt_date_success(string $d): string {
    $ts = $d ? strtotime($d) : false;
    return $ts ? date('M j, Y', $ts) : $d;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed | <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/tailwind.css">
</head>
<body class="font-sans text-slate-900 antialiased">
<?php include 'includes/navbar.php'; ?>

<section class="min-h-[80vh] bg-slate-50 flex items-center justify-center px-6 pt-32 pb-16">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xl max-w-lg w-full p-8">

        <div class="w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-5">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                 stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>

        <h1 class="font-serif text-2xl font-bold text-navy text-center mb-1.5">
            <?= $isWedding ? 'Wedding transfer confirmed!' : 'Booking confirmed!' ?>
        </h1>
        <p class="text-slate-500 text-sm text-center mb-5">
            Thank you, <strong class="text-slate-700"><?= htmlspecialchars($data['name'] ?? 'Guest') ?></strong> -
            <?= $isWedding ? 'your wedding transportation is all set.' : 'your transfer is all set.' ?>
        </p>

        <div class="flex justify-center mb-6">
            <span class="inline-block bg-navy/10 text-navy text-xs font-bold px-4 py-1.5 rounded-full tracking-wide">
                Ref: <?= htmlspecialchars($data['reference'] ?? 'N/A') ?>
            </span>
        </div>

        <div class="bg-slate-50 rounded-xl border border-slate-100 p-5 mb-6 text-left">

            <?php if (!empty($data['area'])): ?>
            <div class="flex justify-between items-center py-2 border-b border-slate-100 text-sm">
                <span class="text-slate-500">Destination</span>
                <span class="font-semibold text-navy text-right"><?= htmlspecialchars($data['area']) ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($data['zone'])): ?>
            <div class="flex justify-between items-center py-2 border-b border-slate-100 text-sm">
                <span class="text-slate-500">Zone</span>
                <span class="font-semibold text-navy text-right"><?= htmlspecialchars($data['zone']) ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($data['trip_type_label'])): ?>
            <div class="flex justify-between items-center py-2 border-b border-slate-100 text-sm">
                <span class="text-slate-500">Trip type</span>
                <span class="font-semibold text-navy text-right"><?= htmlspecialchars($data['trip_type_label']) ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($data['date'])): ?>
            <div class="flex justify-between items-center py-2 border-b border-slate-100 text-sm">
                <span class="text-slate-500"><?= !empty($data['return_date']) ? 'Departure' : 'Service date' ?></span>
                <span class="font-semibold text-navy text-right">
                    <?= htmlspecialchars(fmt_date_success($data['date'])) ?>
                    <?= !empty($data['time']) ? ' · ' . htmlspecialchars($data['time']) : '' ?>
                </span>
            </div>
            <?php endif; ?>

            <?php if (!empty($data['return_date'])): ?>
            <div class="flex justify-between items-center py-2 border-b border-slate-100 text-sm">
                <span class="text-slate-500">Return</span>
                <span class="font-semibold text-navy text-right">
                    <?= htmlspecialchars(fmt_date_success($data['return_date'])) ?>
                    <?= !empty($data['return_time']) ? ' · ' . htmlspecialchars($data['return_time']) : '' ?>
                </span>
            </div>
            <?php endif; ?>

            <?php if (!empty($data['passengers'])): ?>
            <div class="flex justify-between items-center py-2 border-b border-slate-100 text-sm">
                <span class="text-slate-500">Passengers</span>
                <span class="font-semibold text-navy text-right"><?= (int)$data['passengers'] ?></span>
            </div>
            <?php endif; ?>

            <div class="flex justify-between items-center py-2 border-b border-slate-100 text-sm">
                <span class="text-slate-500">Vehicle</span>
                <span class="font-semibold text-navy text-right">Luxury SUV</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-slate-100 text-sm">
                <span class="text-slate-500">Payment</span>
                <span class="font-semibold text-navy text-right">Cash to driver</span>
            </div>

            <div class="flex justify-between items-center pt-2.5 text-sm">
                <span class="text-slate-500">Total</span>
                <span class="font-bold text-navy text-right text-lg">$<?= number_format($data['price'] ?? 0, 2) ?> USD</span>
            </div>

        </div>

        <p class="flex items-center justify-center gap-2 text-xs text-slate-400 mb-7">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
            A confirmation email has been sent to your address
        </p>

        <div class="flex flex-wrap justify-center gap-3">
            <a href="/index.php" class="border border-navy text-navy px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-navy hover:text-white transition-colors">- Return home</a>
            <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>" class="inline-flex items-center gap-2 bg-[#25D366] text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-[#1ebe5b] hover:-translate-y-0.5 transition-all" target="_blank">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                WhatsApp us
            </a>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
