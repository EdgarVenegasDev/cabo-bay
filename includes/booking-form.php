<?php
/* includes/booking-form.php — Widget rápido del hero */
require_once __DIR__ . '/../config/database.php';

$allAreas = [];
try {
    $pdo = Database::getConnection();
    $stmt = $pdo->query(
        'SELECT a.name, z.name AS zone_name, z.one_way_price, z.round_trip_price
         FROM areas a
         JOIN zones z ON z.id = a.zone_id
         WHERE a.is_active = 1 AND z.is_active = 1
         ORDER BY z.display_order ASC, a.display_order ASC, a.name ASC'
    );
    foreach ($stmt->fetchAll() as $row) {
        $allAreas[] = [
            'name'      => $row['name'],
            'zone'      => $row['zone_name'],
            'oneWay'    => $row['one_way_price'],
            'roundTrip' => $row['round_trip_price'],
        ];
    }
} catch (Throwable $e) {
    error_log('[booking-form areas load failed] ' . $e->getMessage());
    // $allAreas queda vacío; el <select> se ve sin opciones en vez de romper la página.
}
?>

<div class="booking-box">
<h3>Book your transfer</h3>

<form id="quickBookingForm" action="pages/booking.php" method="GET">

<!-- Tipo de recogida -->
<select name="pickup_type" id="pickupType" required>
<option value="airport">Airport → Hotel (SJD)</option>
<option value="hotel_airbnb">Hotel / Airbnb → Airport</option>
</select>

<!-- Destino -->
<div id="destinationGroup">
<select name="area" id="destinationSelect" required disabled>
<option value="">Select your hotel / area</option>
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
><?= htmlspecialchars($a['name']) ?></option>
<?php endforeach; if ($currentZone) echo '</optgroup>'; ?>
</select>
</div>

<!-- One way / Round trip -->
<div class="trip-toggle">
<input type="radio" name="trip_type" id="quick-oneway"    value="oneway"    checked>
<label class="toggle-btn" for="quick-oneway">One Way</label>
<input type="radio" name="trip_type" id="quick-roundtrip" value="roundtrip">
<label class="toggle-btn" for="quick-roundtrip">Round Trip</label>
</div>

<!-- Fecha de ida -->
<input type="date" name="date" id="date" required>

<!-- Fecha de regreso (oculta por defecto) -->
<div id="returnSection" style="display:none">
<input type="date" name="return_date" id="return_date" placeholder="Return date">
</div>

<!-- Pasajeros -->
<input type="number" name="passengers" id="passengers"
placeholder="Passengers (max 7)" min="1" max="7" value="1" required>

<input type="hidden" name="zone" id="zoneHidden">

<button type="submit" id="submitBtn">Book Now</button>
</form>
</div>

<!-- Modal contacto para hotel/airbnb -->
<div id="contactModal" class="modal">
<div class="modal-content">
<span class="close" aria-label="Cerrar">&times;</span>
<h3>We're here to help!</h3>
<p>For pickups from hotels or Airbnbs, please contact us directly and we'll arrange everything.</p>
<p><strong>WhatsApp:</strong>
<a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '5218009411956' ?>">
<?= defined('WHATSAPP_FORMATTED') ? WHATSAPP_FORMATTED : '+52 1 800 941 1956' ?>
</a>
</p>
<p><strong>Email:</strong>
<a href="mailto:<?= defined('INFO_EMAIL') ? INFO_EMAIL : 'info@cabo-bay.com' ?>">
<?= defined('INFO_EMAIL') ? INFO_EMAIL : 'info@cabo-bay.com' ?>
</a>
</p>
<button class="btn btn-primary" onclick="closeModal()">Close</button>
</div>
</div>