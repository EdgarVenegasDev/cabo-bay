<?php
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
}
?>

<div class="booking-box absolute right-[5%] top-1/2 -translate-y-1/2 z-30 bg-white/98 backdrop-blur-md p-7 rounded-2xl w-full max-w-sm shadow-2xl border border-white/20
            max-lg:relative max-lg:translate-y-0 max-lg:mx-auto max-lg:-mt-6 max-lg:max-w-[90%]">
<h3 class="font-serif text-2xl text-center text-navy mb-5">Book your transfer</h3>

<form id="quickBookingForm" action="/pages/booking.php" method="GET" class="flex flex-col gap-4">

<select name="pickup_type" id="pickupType" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
<option value="airport">Airport - Hotel (SJD)</option>
<option value="hotel_airbnb">Hotel / Airbnb - Airport</option>
</select>

<div id="destinationGroup">
<select name="area" id="destinationSelect" required disabled class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
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

<div class="flex bg-slate-100 rounded-full p-1">
<input type="radio" name="trip_type" id="quick-oneway" value="oneway" checked class="peer/ow hidden">
<label for="quick-oneway" class="flex-1 text-center py-2 rounded-full text-sm font-medium cursor-pointer text-slate-700 peer-checked/ow:bg-navy peer-checked/ow:text-white transition-colors">One Way</label>
<input type="radio" name="trip_type" id="quick-roundtrip" value="roundtrip" class="peer/rt hidden">
<label for="quick-roundtrip" class="flex-1 text-center py-2 rounded-full text-sm font-medium cursor-pointer text-slate-700 peer-checked/rt:bg-navy peer-checked/rt:text-white transition-colors">Round Trip</label>
</div>

<input type="date" name="date" id="date" required class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">

<div id="returnSection" style="display:none">
<input type="date" name="return_date" id="return_date" placeholder="Return date" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
</div>

<input type="number" name="passengers" id="passengers"
placeholder="Passengers (max 7)" min="1" max="7" value="1" required
class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">

<input type="hidden" name="zone" id="zoneHidden">

<button type="submit" id="submitBtn" class="bg-navy text-white font-semibold py-3 rounded-lg hover:bg-navy-dark hover:-translate-y-0.5 transition-all">Book Now</button>
</form>
</div>

<div id="contactModal" class="modal fixed inset-0 z-[1000] bg-black/60 backdrop-blur-sm hidden items-center justify-center">
<div class="bg-white rounded-2xl p-8 max-w-md w-[90%] text-center relative">
<span class="close absolute top-3 right-4 text-2xl text-gray-400 hover:text-navy cursor-pointer" aria-label="Cerrar">&times;</span>
<h3 class="font-serif text-xl text-navy mb-2">We're here to help!</h3>
<p class="text-sm text-gray-500 mb-4">For pickups from hotels or Airbnbs, please contact us directly and we'll arrange everything.</p>
<p class="text-sm mb-1"><strong>WhatsApp:</strong>
<a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '5218009411956' ?>" class="text-navy">
<?= defined('WHATSAPP_FORMATTED') ? WHATSAPP_FORMATTED : '+52 1 800 941 1956' ?>
</a>
</p>
<p class="text-sm mb-5"><strong>Email:</strong>
<a href="mailto:<?= defined('INFO_EMAIL') ? INFO_EMAIL : 'info@cabo-bay.com' ?>" class="text-navy">
<?= defined('INFO_EMAIL') ? INFO_EMAIL : 'info@cabo-bay.com' ?>
</a>
</p>
<button class="bg-navy text-white px-6 py-2.5 rounded-lg font-medium hover:bg-navy-dark transition-colors" onclick="closeModal()">Close</button>
</div>
</div>
