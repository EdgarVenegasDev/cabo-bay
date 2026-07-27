<?php
require_once '../config/config.php';

/* ── Parámetros GET ── */
$preSelectedArea = trim($_GET['area']          ?? '');
$preSelectedZone = trim($_GET['zone']          ?? '');
$tripType        = $_GET['type'] ?? $_GET['trip_type'] ?? 'oneway';
$passengers      = max(1, min(7, (int)($_GET['passengers'] ?? 1)));
$preServiceDate  = trim($_GET['date']                 ?? '');
$preReturnDate   = trim($_GET['return_date']          ?? '');
$preReturnTime   = trim($_GET['return_time']          ?? '');
$preReturnFlight = trim($_GET['return_flight_number'] ?? '');

/* Normalizar trip_type */
if ($tripType === 'one_way')    $tripType = 'oneway';
if ($tripType === 'round_trip') $tripType = 'roundtrip';
$isRound = ($tripType === 'roundtrip');

/* ── Datos de precios ── */
$pricingData = json_decode(file_get_contents('../data/pricing.json'), true);
$zones       = $pricingData['zones'] ?? [];

function findZoneByArea(string $area, array $zones): ?array {
    foreach ($zones as $zone) {
        if (in_array($area, $zone['areas'], true)) return $zone;
    }
    return null;
}
function findZoneByName(string $name, array $zones): ?array {
    foreach ($zones as $zone) {
        if ($zone['name'] === $name) return $zone;
    }
    return null;
}

$selectedZoneObj = null;
if ($preSelectedArea) $selectedZoneObj = findZoneByArea($preSelectedArea, $zones);
if (!$selectedZoneObj && $preSelectedZone) $selectedZoneObj = findZoneByName($preSelectedZone, $zones);

$priceEstimate = null;
if ($selectedZoneObj) {
    $priceEstimate = $isRound
        ? $selectedZoneObj['roundTrip']
        : $selectedZoneObj['oneWay'];
}

/* Lista plana de áreas (cuando llega sin pre-selección) */
$allAreas = [];
foreach ($zones as $zone) {
    foreach ($zone['areas'] as $area) {
        $allAreas[] = [
            'name'      => $area,
            'zone'      => $zone['name'],
            'oneWay'    => $zone['oneWay'],
            'roundTrip' => $zone['roundTrip'],
        ];
    }
}

/* Formato de fecha legible para el summary */
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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/utilities.css">
    <script src="../assets/js/booking.js" defer></script>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <main class="booking-page">
        <div class="container">
            <div class="booking-layout">

                <!-- ── Formulario principal ── -->
                <div class="booking-form-container">
                    <h1>Complete your booking</h1>

                    <!-- ══ BOOKING SUMMARY ══════════════════════════════════
                         Muestra todo lo que ya sabemos del usuario.
                         Solo aparece si viene con zona pre-seleccionada.
                    ══════════════════════════════════════════════════════ -->
                    <?php if ($selectedZoneObj): ?>
                    <div class="booking-summary">
                        <h3>Booking summary</h3>

                        <?php if ($preSelectedArea): ?>
                        <p><strong>Destination:</strong> <?= htmlspecialchars($preSelectedArea) ?></p>
                        <?php endif; ?>

                        <p><strong>Zone:</strong> <?= htmlspecialchars($selectedZoneObj['name']) ?></p>
                        <p><strong>Trip type:</strong> <?= $isRound ? 'Round Trip' : 'One Way' ?></p>
                        <p><strong>Passengers:</strong> <?= (int)$passengers ?></p>

                        <?php if ($preServiceDate): ?>
                        <p><strong>Departure date:</strong> <?= htmlspecialchars(fmt_date($preServiceDate)) ?></p>
                        <?php endif; ?>

                        <?php if ($isRound && $preReturnDate): ?>
                        <p><strong>Return date:</strong> <?= htmlspecialchars(fmt_date($preReturnDate)) ?></p>
                        <?php endif; ?>

                        <?php if ($priceEstimate): ?>
                        <p><strong>Estimated price:</strong>
                            <span style="font-size:1.1rem;font-weight:700;color:var(--color-primary)">
                                $<?= number_format($priceEstimate, 2) ?> USD
                            </span>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- ══ FORMULARIO ══════════════════════════════════════ -->
                    <form id="fullBookingForm" action="../api/process_booking.php" method="POST">

                        <!-- ── Campos pre-capturados como hidden ── -->
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
                            <!-- Sin pre-selección: el usuario elige todo aquí -->
                            <div class="form-group">
                                <label for="areaSelect">Hotel / Area *</label>
                                <select id="areaSelect" name="area" required>
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
                                            data-one-way="<?= (int)$a['oneWay'] ?>"
                                            data-round-trip="<?= (int)$a['roundTrip'] ?>"
                                        ><?= htmlspecialchars($a['name']) ?> (<?= htmlspecialchars($a['zone']) ?>)</option>
                                    <?php endforeach; if ($currentZone) echo '</optgroup>'; ?>
                                </select>
                            </div>
                            <input type="hidden" name="zone" id="zoneHidden">

                            <div class="form-group">
                                <label>Trip type *</label>
                                <div class="trip-toggle">
                                    <input type="radio" name="trip_type" id="oneway"    value="oneway"    <?= !$isRound ? 'checked' : '' ?>>
                                    <label class="toggle-btn" for="oneway">One Way</label>
                                    <input type="radio" name="trip_type" id="roundtrip" value="roundtrip" <?= $isRound  ? 'checked' : '' ?>>
                                    <label class="toggle-btn" for="roundtrip">Round Trip</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="passengers">Passengers *</label>
                                <input type="number" id="passengers" name="passengers"
                                       min="1" max="7" value="<?= (int)$passengers ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="date">Service date *</label>
                                <input type="date" id="date" name="date"
                                       min="<?= date('Y-m-d') ?>"
                                       value="<?= htmlspecialchars($preServiceDate) ?>" required>
                            </div>
                        <?php endif; ?>

                        <!-- ── Hora de servicio (siempre se pide) ── -->
                        <div class="form-group">
                            <label for="time">Service time *</label>
                            <input type="time" id="time" name="time" required>
                        </div>

                        <div class="form-group">
                            <label for="flight_number">Arrival flight number</label>
                            <input type="text" id="flight_number" name="flight_number"
                                   placeholder="e.g., AA1234">
                        </div>

                        <div class="form-group">
                            <label for="arrival_time">Arrival time</label>
                            <input type="time" id="arrival_time" name="arrival_time">
                        </div>

                        <!-- ── Sección de regreso ── -->
                        <!-- return_date ya viene como hidden; aquí solo pedimos hora y vuelo -->
                        <div id="returnSection" style="<?= $isRound ? '' : 'display:none;' ?>">
                            <div class="form-group">
                                <label for="return_time">Return pickup time *</label>
                                <input type="time" id="return_time" name="return_time"
                                       value="<?= htmlspecialchars($preReturnTime) ?>"
                                       <?= $isRound ? 'required' : '' ?>>
                            </div>
                            <div class="form-group">
                                <label for="return_flight_number">Return flight number *</label>
                                <input type="text" id="return_flight_number" name="return_flight_number"
                                       placeholder="e.g., UA5678"
                                       value="<?= htmlspecialchars($preReturnFlight) ?>"
                                       <?= $isRound ? 'required' : '' ?>>
                            </div>
                        </div>

                        <!-- ── Datos personales ── -->
                        <div class="form-group">
                            <label for="name">Full name *</label>
                            <input type="text" id="name" name="name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone *</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>

                        <div class="form-group">
                            <label for="special_requests">Special requests</label>
                            <textarea id="special_requests" name="special_requests" rows="2"
                                      placeholder="Baby seat, luggage assistance, etc."></textarea>
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Payment method *</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="cash">Cash (upon arrival)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%;">
                            Confirm booking
                        </button>

                    </form>
                </div>

                <!-- ── Panel lateral ── -->
                <aside class="booking-info">
                    <h3>Important information</h3>
                    <ul>
                        <li>Luxury SUV — up to 7 passengers</li>
                        <li>Private, non-shared transfer</li>
                        <li>Meet &amp; greet at the airport</li>
                        <li>Payment in cash to the driver</li>
                        <li>Email confirmation sent immediately</li>
                        <li>Free cancellation up to 24 hours prior</li>
                        <li>Flight monitoring included</li>
                    </ul>
                    <?php if ($priceEstimate): ?>
                    <div class="booking-summary" style="margin-top:1.5rem">
                        <h3>Price breakdown</h3>
                        <p><strong>Vehicle:</strong> Luxury SUV</p>
                        <p><strong>Trip type:</strong> <?= $isRound ? 'Round Trip' : 'One Way' ?></p>
                        <p style="font-size:1.3rem;font-weight:700;color:var(--color-primary);margin-top:.5rem">
                            $<?= number_format($priceEstimate, 2) ?> USD
                        </p>
                    </div>
                    <?php endif; ?>
                </aside>

            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>