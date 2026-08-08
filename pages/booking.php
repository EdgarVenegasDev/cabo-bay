<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/mercadopago.php';
require_once '../includes/booking-helpers.php';

$exchangeRate = get_usd_to_mxn_rate();

$preSelectedArea = trim($_GET['area']          ?? '');
$preSelectedZone = trim($_GET['zone']          ?? '');
$tripType        = $_GET['type'] ?? $_GET['trip_type'] ?? 'oneway';
$passengers      = max(1, min(7, (int)($_GET['passengers'] ?? 1)));
$preServiceDate  = trim($_GET['date']                 ?? '');
$preReturnDate   = trim($_GET['return_date']          ?? '');
$preReturnTime   = trim($_GET['return_time']          ?? '');
$preReturnFlight = trim($_GET['return_flight_number'] ?? '');

if ($tripType === 'one_way')    $tripType = 'oneway';
if ($tripType === 'round_trip') $tripType = 'roundtrip';
$isRound = ($tripType === 'roundtrip');

$pdo = Database::getConnection();

$selectedZoneObj = null;

if ($preSelectedArea) {
    $stmt = $pdo->prepare(
        'SELECT z.* FROM areas a JOIN zones z ON z.id = a.zone_id
         WHERE a.name = ? AND a.is_active = 1 AND z.is_active = 1 LIMIT 1'
    );
    $stmt->execute([$preSelectedArea]);
    $selectedZoneObj = $stmt->fetch() ?: null;
}

if (!$selectedZoneObj && $preSelectedZone) {
    $stmt = $pdo->prepare('SELECT * FROM zones WHERE name = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$preSelectedZone]);
    $selectedZoneObj = $stmt->fetch() ?: null;
}

$priceEstimate = null;
if ($selectedZoneObj) {
    $priceEstimate = $isRound
        ? (float)$selectedZoneObj['round_trip_price']
        : (float)$selectedZoneObj['one_way_price'];
}

$allAreas = [];
$areasStmt = $pdo->query(
    'SELECT a.name, z.name AS zone_name, z.one_way_price, z.round_trip_price
     FROM areas a JOIN zones z ON z.id = a.zone_id
     WHERE a.is_active = 1 AND z.is_active = 1
     ORDER BY z.display_order ASC, a.display_order ASC, a.name ASC'
);
foreach ($areasStmt->fetchAll() as $row) {
    $allAreas[] = [
        'name'      => $row['name'],
        'zone'      => $row['zone_name'],
        'oneWay'    => $row['one_way_price'],
        'roundTrip' => $row['round_trip_price'],
    ];
}

function fmt_date(string $d): string {
    if (!$d) return '';
    $ts = strtotime($d);
    return $ts ? date('M j, Y', $ts) : $d;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complete Your Booking | <?= SITE_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=Jost:wght@300;400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=Jost:wght@300;400;500;600;700&display=swap"></noscript>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <script src="/assets/js/booking.js" defer></script>
</head>
<body class="font-sans text-slate-900 antialiased bg-slate-50">
    <?php include '../includes/navbar.php'; ?>

    <main class="pt-32 pb-56 px-6 md_pb-66">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 lg:p-8">
                    <h1 class="font-serif text-3xl font-bold text-navy mb-6">Complete your booking</h1>

                    <?php if ($selectedZoneObj): ?>
                        <div class="rounded-2xl overflow-hidden border border-navy-light shadow-sm mb-6">
                            <div class="bg-navy px-6 py-5 flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-white/60 text-xs uppercase tracking-wider mb-1">Your transfer</p>
                                    <p class="text-white font-semibold text-lg truncate"><?= htmlspecialchars($preSelectedArea ?: $selectedZoneObj['name']) ?></p>
                                </div>
                                <?php if ($priceEstimate): ?>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-white/60 text-xs uppercase tracking-wider mb-1">Total</p>
                                    <p class="text-white font-bold text-3xl leading-none">$<?= number_format($priceEstimate, 0) ?><span class="text-sm font-medium text-white/70"> USD</span></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="bg-navy-light/25 divide-y divide-navy-light">
                                <?php if ($preSelectedArea): ?>
                                <div class="flex justify-between items-center px-6 py-3">
                                    <span class="text-xs uppercase tracking-wide text-slate-500">Destination</span>
                                    <span class="text-sm font-semibold text-navy"><?= htmlspecialchars($preSelectedArea) ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="flex justify-between items-center px-6 py-3">
                                    <span class="text-xs uppercase tracking-wide text-slate-500">Zone</span>
                                    <span class="text-sm font-semibold text-navy"><?= htmlspecialchars($selectedZoneObj['name']) ?></span>
                                </div>
                                <div class="flex justify-between items-center px-6 py-3">
                                    <span class="text-xs uppercase tracking-wide text-slate-500">Trip type</span>
                                    <span class="text-sm font-semibold text-navy"><?= $isRound ? 'Round Trip' : 'One Way' ?></span>
                                </div>
                                <div class="flex justify-between items-center px-6 py-3">
                                    <span class="text-xs uppercase tracking-wide text-slate-500">Passengers</span>
                                    <span class="text-sm font-semibold text-navy"><?= (int)$passengers ?></span>
                                </div>
                                <?php if ($preServiceDate): ?>
                                <div class="flex justify-between items-center px-6 py-3">
                                    <span class="text-xs uppercase tracking-wide text-slate-500">Departure</span>
                                    <span class="text-sm font-semibold text-navy"><?= htmlspecialchars(fmt_date($preServiceDate)) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ($isRound && $preReturnDate): ?>
                                <div class="flex justify-between items-center px-6 py-3">
                                    <span class="text-xs uppercase tracking-wide text-slate-500">Return</span>
                                    <span class="text-sm font-semibold text-navy"><?= htmlspecialchars(fmt_date($preReturnDate)) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                    <form id="fullBookingForm" action="/api/process_booking.php" method="POST" class="space-y-5">

                        <?php if ($selectedZoneObj): ?>
                            <input type="hidden" name="trip_type"  value="<?= htmlspecialchars($tripType) ?>">
                            <input type="hidden" name="zone"       value="<?= htmlspecialchars($selectedZoneObj['name']) ?>">
                            <input type="hidden" name="area"       value="<?= htmlspecialchars($preSelectedArea) ?>">
                            <input type="hidden" name="passengers" value="<?= (int)$passengers ?>">
                            <?php if ($preServiceDate): ?>
                            <input type="hidden" name="date"       value="<?= htmlspecialchars($preServiceDate) ?>">
                            <?php endif; ?>
                            <?php if ($isRound && $preReturnDate): ?>
                            <input type="hidden" name="return_date" value="<?= htmlspecialchars($preReturnDate) ?>">
                            <?php endif; ?>

                        <?php else: ?>
                            <div>
                                <label for="areaSelect" class="text-sm font-medium text-slate-700 block mb-1.5">Hotel / Area *</label>
                                <select id="areaSelect" name="area" required
                                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                                    <option value="">Select your hotel or area</option>
                                    <?php
                                    $currentZone = '';
                                    foreach ($allAreas as $a):
                                        if ($currentZone !== $a['zone']):
                                            if ($currentZone !== '') echo '</optgroup>';
                                            echo '<optgroup label="' . htmlspecialchars($a['zone']) . '">';
                                            $currentZone = $a['zone'];
                                        endif;
                                    ?>
                                        <option
                                            value="<?= htmlspecialchars($a['name']) ?>"
                                            data-zone="<?= htmlspecialchars($a['zone']) ?>"
                                            data-one-way="<?= (float)$a['oneWay'] ?>"
                                            data-round-trip="<?= (float)$a['roundTrip'] ?>"
                                        ><?= htmlspecialchars($a['name']) ?> (<?= htmlspecialchars($a['zone']) ?>)</option>
                                    <?php endforeach; if ($currentZone) echo '</optgroup>'; ?>
                                </select>
                            </div>
                            <input type="hidden" name="zone" id="zoneHidden">

                            <div>
                                <label class="text-sm font-medium text-slate-700 block mb-1.5">Trip type *</label>
                                <div class="flex bg-slate-100 rounded-full p-1 max-w-xs">
                                    <input type="radio" name="trip_type" id="oneway" value="oneway" <?= !$isRound ? 'checked' : '' ?> class="hidden peer/ow">
                                    <label for="oneway" class="flex-1 text-center py-2 rounded-full text-sm font-medium cursor-pointer peer-checked/ow:bg-navy peer-checked/ow:text-white transition-colors">One Way</label>
                                    <input type="radio" name="trip_type" id="roundtrip" value="roundtrip" <?= $isRound ? 'checked' : '' ?> class="hidden peer/rt">
                                    <label for="roundtrip" class="flex-1 text-center py-2 rounded-full text-sm font-medium cursor-pointer peer-checked/rt:bg-navy peer-checked/rt:text-white transition-colors">Round Trip</label>
                                </div>
                            </div>

                            <p id="priceEstimate" class="text-navy font-bold text-lg"></p>

                            <div>
                                <label for="passengers" class="text-sm font-medium text-slate-700 block mb-1.5">Passengers *</label>
                                <input type="number" id="passengers" name="passengers"
                                       min="1" max="7" value="<?= (int)$passengers ?>" required
                                       class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                            </div>

                            <div>
                                <label for="date" class="text-sm font-medium text-slate-700 block mb-1.5">Service date *</label>
                                <input type="date" id="date" name="date"
                                       min="<?= date('Y-m-d') ?>"
                                       value="<?= htmlspecialchars($preServiceDate) ?>" required
                                       class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                            </div>
                        <?php endif; ?>

                        <div>
                            <label for="time" class="text-sm font-medium text-slate-700 block mb-1.5">Service time *</label>
                            <input type="time" id="time" name="time" required
                                   class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        </div>

                        <div>
                            <label for="flight_number" class="text-sm font-medium text-slate-700 block mb-1.5">Arrival flight number</label>
                            <input type="text" id="flight_number" name="flight_number" placeholder="e.g., AA1234"
                                   class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        </div>

                        <div>
                            <label for="arrival_time" class="text-sm font-medium text-slate-700 block mb-1.5">Arrival time</label>
                            <input type="time" id="arrival_time" name="arrival_time"
                                   class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        </div>

                        <div id="returnSection" class="space-y-5 <?= $isRound ? '' : 'hidden' ?>" style="<?= $isRound ? '' : 'display:none;' ?>">
                            <div>
                                <label for="return_time" class="text-sm font-medium text-slate-700 block mb-1.5">Return pickup time *</label>
                                <input type="time" id="return_time" name="return_time"
                                       value="<?= htmlspecialchars($preReturnTime) ?>"
                                       <?= $isRound ? 'required' : '' ?>
                                       class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                            </div>
                            <div>
                                <label for="return_flight_number" class="text-sm font-medium text-slate-700 block mb-1.5">Return flight number *</label>
                                <input type="text" id="return_flight_number" name="return_flight_number"
                                       placeholder="e.g., UA5678"
                                       value="<?= htmlspecialchars($preReturnFlight) ?>"
                                       <?= $isRound ? 'required' : '' ?>
                                       class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                            </div>
                        </div>

                        <div>
                            <label for="name" class="text-sm font-medium text-slate-700 block mb-1.5">Full name *</label>
                            <input type="text" id="name" name="name" required
                                   class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        </div>

                        <div>
                            <label for="email" class="text-sm font-medium text-slate-700 block mb-1.5">Email *</label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        </div>

                        <div>
                            <label for="phone" class="text-sm font-medium text-slate-700 block mb-1.5">Phone *</label>
                            <input type="tel" id="phone" name="phone" required
                                   class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        </div>

                        <div>
                            <label for="special_requests" class="text-sm font-medium text-slate-700 block mb-1.5">Special requests</label>
                            <textarea id="special_requests" name="special_requests" rows="2"
                                      placeholder="Baby seat, luggage assistance, etc."
                                      class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy resize-y"></textarea>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700 block mb-1.5">Payment method *</label>
                            <div class="flex bg-slate-100 rounded-full p-1 max-w-xs mb-3">
                                <input type="radio" name="payment_method" id="pay-cash" value="cash" checked class="hidden peer/cash">
                                <label for="pay-cash" class="flex-1 text-center py-2 rounded-full text-sm font-medium cursor-pointer peer-checked/cash:bg-navy peer-checked/cash:text-white transition-colors">Cash</label>
                                <input type="radio" name="payment_method" id="pay-card" value="card" <?= $priceEstimate ? '' : 'disabled' ?> class="hidden peer/card">
                                <label for="pay-card" class="flex-1 text-center py-2 rounded-full text-sm font-medium cursor-pointer peer-checked/card:bg-navy peer-checked/card:text-white transition-colors <?= $priceEstimate ? '' : 'opacity-40 cursor-not-allowed' ?>">Card</label>
                            </div>
                            <?php if (!$priceEstimate): ?>
                                <p class="text-xs text-amber-600 mb-3">Select your hotel/area above to enable card payment.</p>
                            <?php endif; ?>
                        </div>

                        <div id="cashSubmitWrap">
                            <button type="submit" class="w-full bg-navy text-white font-semibold py-3.5 rounded-lg hover:bg-navy-dark hover:-translate-y-0.5 transition-all">
                                Confirm booking
                            </button>
                        </div>

                        <div id="cardPaymentWrap" class="hidden">
                            <div id="paymentBrick_container"></div>
                            <p id="cardError" class="text-red-600 text-sm mt-2 hidden"></p>
                        </div>

                    </form>
                </div>

                <aside class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 lg:sticky lg:top-28">
                    
                    <div class="border-b border-slate-100 pb-3 mb-5">
                        <h3 class="font-serif text-base font-semibold text-navy tracking-wide uppercase">Important Information</h3>
                    </div>
                    
                    
                    <ul class="space-y-3.5 text-sm text-slate-500">
                        <?php foreach ([
                            'Luxury SUV &mdash; up to 7 passengers',
                            'Private, non-shared premium transfer',
                            'Professional meet & greet at the airport',
                            'Instant email confirmation secure booking',
                            'Flexible cancellation up to 24h prior',
                            'Real-time flight monitoring included',
                        ] as $item): ?>
                        <li class="flex items-start gap-3 group">
                            
                            <span class="flex-shrink-0 text-navy mt-0.5 transition-transform group-hover:scale-110 duration-200">
                                <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </span>
                            <span class="font-medium text-slate-600 transition-colors group-hover:text-navy duration-200"><?= $item ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if ($priceEstimate): ?>
                    
                    <div class="mt-6 pt-5 border-t border-slate-100">
                        <div class="bg-slate-50/50 rounded-xl p-4 border border-slate-100">
                            <h4 class="font-serif text-xs font-semibold text-navy/70 uppercase tracking-wider mb-2.5">Price breakdown</h4>
                            <div class="space-y-1.5 text-xs text-slate-500 mb-3.5">
                                <div class="flex justify-between"><span>Vehicle</span><span class="font-medium text-slate-700">Luxury SUV</span></div>
                                <div class="flex justify-between"><span>Service</span><span class="font-medium text-slate-700"><?= $isRound ? 'Round Trip' : 'One Way' ?></span></div>
                            </div>
                            <div class="flex items-baseline justify-between pt-2 border-t border-slate-100">
                                <span class="text-xs font-semibold text-navy uppercase tracking-wider">Total</span>
                                <p class="text-2xl font-bold text-navy tracking-tight">
                                    $<?= number_format($priceEstimate, 2) ?><span class="text-xs font-medium text-slate-400 ml-1">USD</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </aside>

            </div>
        </div>
    </main>

    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>
    (function () {
        const form     = document.getElementById('fullBookingForm');
        const payCash  = document.getElementById('pay-cash');
        const payCard  = document.getElementById('pay-card');
        const cashWrap = document.getElementById('cashSubmitWrap');
        const cardWrap = document.getElementById('cardPaymentWrap');
        const cardError = document.getElementById('cardError');

        const MP_PUBLIC_KEY = <?= json_encode(MP_PUBLIC_KEY) ?>;
        const rate     = <?= json_encode($exchangeRate) ?>;
        const priceUsd = <?= json_encode($priceEstimate ?: 0) ?>;

        let brickInitialized = false;

        function togglePaymentUI() {
            if (payCard.checked) {
                cashWrap.classList.add('hidden');
                cardWrap.classList.remove('hidden');
                if (!brickInitialized) initBrick();
            } else {
                cashWrap.classList.remove('hidden');
                cardWrap.classList.add('hidden');
            }
        }
        payCash?.addEventListener('change', togglePaymentUI);
        payCard?.addEventListener('change', togglePaymentUI);

        form?.addEventListener('submit', function (e) {
            if (payCard && payCard.checked) e.preventDefault();
        });

        async function initBrick() {
            if (!priceUsd || !MP_PUBLIC_KEY) return;
            brickInitialized = true;

            const mp = new MercadoPago(MP_PUBLIC_KEY, { locale: 'es-MX' });
            const bricksBuilder = mp.bricks();
            const amountMxn = Math.round(priceUsd * rate * 100) / 100;

            await bricksBuilder.create('payment', 'paymentBrick_container', {
                initialization: {
                    amount: amountMxn,
                },
                customization: {
                    paymentMethods: {
                        creditCard: 'all',
                        debitCard: 'all',
                    },
                },
                callbacks: {
                    onReady: () => {
                        // El Brick ya terminó de cargar y se puede interactuar con el
                    },
                    onSubmit: ({ formData }) => {
                        return new Promise((resolve, reject) => {
                            if (!form.reportValidity()) {
                                reject(new Error('Please complete all required fields.'));
                                return;
                            }
                            cardError.classList.add('hidden');

                            const fd = new FormData(form);
                            fd.set('payment_method', 'card');
                            fd.set('token', formData.token);
                            fd.set('payment_method_id', formData.payment_method_id);
                            fd.set('installments', formData.installments);
                            if (formData.issuer_id) fd.set('issuer_id', formData.issuer_id);

                            fetch('/api/process_card_payment.php', { method: 'POST', body: fd })
                                .then(function (res) { return res.json(); })
                                .then(function (result) {
                                    if (result.ok) {
                                        window.location.href = result.redirect;
                                        resolve();
                                    } else {
                                        cardError.textContent = result.error || 'Payment failed. Please try again.';
                                        cardError.classList.remove('hidden');
                                        reject(new Error(result.error || 'payment failed'));
                                    }
                                })
                                .catch(function (err) {
                                    cardError.textContent = 'Network error. Please try again.';
                                    cardError.classList.remove('hidden');
                                    reject(err);
                                });
                        });
                    },
                    onError: (error) => {
                        console.error('[MP Brick error]', error);
                    },
                },
            });
        }
    })();
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
