<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mercadopago.php';
require_once __DIR__ . '/../includes/booking-helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

function json_error(string $msg, int $code = 422): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

$token           = $_POST['token']              ?? '';
$paymentMethodId = $_POST['payment_method_id']  ?? '';
$installments    = (int)($_POST['installments'] ?? 1);
$issuerId        = $_POST['issuer_id']           ?? null;

if (!$token || !$paymentMethodId) {
    json_error('Datos de pago incompletos. Intenta de nuevo.');
}

$bookingType = $_POST['booking_type'] ?? 'regular';

$emailForCharge = '';
$priceUsd       = 0.0;
$bookingRow     = [];
$emailData      = [];

if ($bookingType === 'wedding') {

    $required = ['name', 'email', 'phone', 'date', 'time', 'flight_number', 'hotel', 'passengers'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) json_error("Missing required field: $field");
    }

    $name          = sanitize_input($_POST['name']);
    $email         = sanitize_input($_POST['email']);
    $phone         = sanitize_input($_POST['phone']);
    $date          = sanitize_input($_POST['date']);
    $time          = sanitize_input($_POST['time']);
    $flight_number = sanitize_input($_POST['flight_number']);
    $hotel         = sanitize_input($_POST['hotel']);
    $passengers    = (int)$_POST['passengers'];
    $special_req   = sanitize_input($_POST['special_requests'] ?? '');
    $tripType      = normalize_trip_type($_POST['trip_type'] ?? 'oneway');
    $isRound       = $tripType === 'roundtrip';
    $return_date   = $isRound ? sanitize_input($_POST['return_date']          ?? '') : '';
    $return_time   = $isRound ? sanitize_input($_POST['return_time']          ?? '') : '';
    $return_flight = $isRound ? sanitize_input($_POST['return_flight_number'] ?? '') : '';
    $priceUsd      = isset($_POST['price']) ? (float)$_POST['price'] : 85.00;

    if (!validate_email($email)) json_error('Invalid email address.');
    if ($isRound && (empty($return_date) || empty($return_time) || empty($return_flight))) {
        json_error('Please provide return date, time and flight number.');
    }

    $emailForCharge = $email;

    $bookingRow = [
        'booking_type' => 'wedding', 'full_name' => $name, 'email' => $email, 'phone' => $phone,
        'hotel' => $hotel, 'trip_type' => $tripType, 'passengers' => $passengers,
        'service_date' => $date, 'service_time' => $time, 'flight_number' => $flight_number,
        'return_date' => $return_date, 'return_time' => $return_time,
        'return_flight_number' => $return_flight, 'special_requests' => $special_req, 'price' => $priceUsd,
    ];

    $emailData = [
        'name' => $name, 'email' => $email, 'phone' => $phone, 'hotel' => $hotel,
        'passengers' => $passengers, 'date' => $date, 'time' => $time, 'flight_number' => $flight_number,
        'trip_type_label' => $isRound ? 'Round Trip (ida y vuelta)' : 'One Way (solo ida)',
        'is_round' => $isRound, 'return_date' => $return_date, 'return_time' => $return_time,
        'return_flight' => $return_flight, 'special_requests' => $special_req, 'price' => $priceUsd,
    ];

} else {

    $required_regular = ['zone', 'area', 'trip_type', 'passengers', 'date', 'time', 'name', 'email', 'phone'];
    foreach ($required_regular as $field) {
        if (empty($_POST[$field])) json_error("Missing required field: $field");
    }

    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT id, name, one_way_price, round_trip_price FROM zones WHERE name = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$_POST['zone']]);
    $selectedZone = $stmt->fetch();

    if (!$selectedZone) json_error('Invalid destination zone.');

    $tripType = normalize_trip_type($_POST['trip_type'] ?? 'oneway');
    $isRound  = $tripType === 'roundtrip';
    $priceUsd = $isRound ? (float)$selectedZone['round_trip_price'] : (float)$selectedZone['one_way_price'];

    $s = [
        'zone' => sanitize_input($_POST['zone']), 'area' => sanitize_input($_POST['area']),
        'passengers' => (int)$_POST['passengers'], 'date' => sanitize_input($_POST['date']),
        'time' => sanitize_input($_POST['time']),
        'flight_number' => sanitize_input($_POST['flight_number'] ?? ''),
        'arrival_time' => sanitize_input($_POST['arrival_time'] ?? ''),
        'special_requests' => sanitize_input($_POST['special_requests'] ?? ''),
        'name' => sanitize_input($_POST['name']), 'email' => sanitize_input($_POST['email']),
        'phone' => sanitize_input($_POST['phone']),
        'return_date' => sanitize_input($_POST['return_date'] ?? ''),
        'return_time' => sanitize_input($_POST['return_time'] ?? ''),
        'return_flight_number' => sanitize_input($_POST['return_flight_number'] ?? ''),
    ];

    if (!validate_email($s['email'])) json_error('Invalid email address.');
    if ($isRound && (empty($s['return_date']) || empty($s['return_time']) || empty($s['return_flight_number']))) {
        json_error('Please provide return date, time and flight number.');
    }

    $emailForCharge = $s['email'];

    $bookingRow = array_merge($s, [
        'booking_type' => 'regular', 'full_name' => $s['name'], 'zone_id' => $selectedZone['id'],
        'zone_name' => $selectedZone['name'], 'trip_type' => $tripType,
        'service_date' => $s['date'], 'service_time' => $s['time'], 'price' => $priceUsd,
    ]);

    $emailData = array_merge($s, [
        'trip_type_label' => $isRound ? 'Round Trip' : 'One Way',
        'is_round' => $isRound, 'price' => $priceUsd,
    ]);
}

if ($priceUsd <= 0) json_error('No se pudo calcular el precio de la reserva.');

$rate      = get_usd_to_mxn_rate();
$amountMxn = round($priceUsd * $rate, 2);
$reference = generate_booking_reference();

$payload = [
    'transaction_amount' => $amountMxn,
    'token'               => $token,
    'description'         => 'Cabo Bay Transportation - ' . $reference,
    'installments'        => max(1, $installments),
    'payment_method_id'   => $paymentMethodId,
    'payer'               => ['email' => $emailForCharge],
    'external_reference'  => $reference,
];
if ($issuerId) {
    $payload['issuer_id'] = $issuerId;
}

try {
    $ch = curl_init('https://api.mercadopago.com/v1/payments');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . MP_ACCESS_TOKEN,
            'X-Idempotency-Key: ' . $reference,
        ],
        CURLOPT_TIMEOUT => 25,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new RuntimeException('cURL error: ' . $curlErr);
    }

    $mpResult = json_decode($response, true);

} catch (Throwable $e) {
    error_log('[MP charge failed] ref=' . $reference . ' error=' . $e->getMessage());
    json_error('No pudimos conectar con el procesador de pagos. Intenta de nuevo.', 502);
}

$status       = $mpResult['status']        ?? null;
$statusDetail = $mpResult['status_detail'] ?? null;
$mpPaymentId  = $mpResult['id']            ?? null;

$rejectionMessages = [
    'cc_rejected_insufficient_amount' => 'Fondos insuficientes en la tarjeta.',
    'cc_rejected_bad_filled_security_code' => 'El codigo de seguridad (CVV) es incorrecto.',
    'cc_rejected_bad_filled_date' => 'La fecha de vencimiento es incorrecta.',
    'cc_rejected_bad_filled_card_number' => 'El numero de tarjeta es incorrecto.',
    'cc_rejected_call_for_authorize' => 'Tu banco requiere autorizacion. Contacta a tu banco o usa otra tarjeta.',
    'cc_rejected_card_disabled' => 'La tarjeta esta deshabilitada, contacta a tu banco.',
    'cc_rejected_high_risk' => 'El pago fue rechazado por seguridad. Intenta con otra tarjeta.',
];

if ($status !== 'approved') {
    $friendlyMsg = $rejectionMessages[$statusDetail] ?? 'El pago fue rechazado. Verifica los datos de tu tarjeta o intenta con otra.';
    error_log("[MP payment not approved] ref=$reference status=$status detail=$statusDetail");
    json_error($friendlyMsg);
}

$bookingRow['reference']      = $reference;
$bookingRow['payment_method'] = 'card';
$bookingRow['payment_status'] = 'paid';
$bookingRow['mp_payment_id']  = (string)$mpPaymentId;
$bookingRow['amount_mxn']     = $amountMxn;

save_booking_to_db($bookingRow);

$emailData['reference']      = $reference;
$emailData['payment_method'] = 'card';
$emailData['amount_mxn']     = $amountMxn;

$isWedding = $bookingType === 'wedding';
$subjectTag = $isWedding ? 'Wedding Transfer' : 'Booking';

$userHtml  = build_html_email("$subjectTag Confirmation - $reference", build_booking_email_content($emailData, false, $isWedding), $reference);
$adminHtml = build_html_email("New $subjectTag (Card Paid) - $reference", build_booking_email_content($emailData, true, $isWedding), $reference);

send_html_email(ADMIN_EMAIL, "New Booking (PAID) - $reference", $adminHtml, BOOKING_EMAIL);
send_html_email($emailForCharge, "Booking Confirmation - $reference | " . SITE_NAME, $userHtml, BOOKING_EMAIL);

$_SESSION['booking_success'] = true;
$_SESSION['booking_data'] = [
    'name'      => $bookingRow['full_name'],
    'price'     => $priceUsd,
    'date'      => $bookingRow['service_date'],
    'reference' => $reference,
    'type'      => $isWedding ? 'wedding' : 'regular',
];

echo json_encode(['ok' => true, 'redirect' => '/success.php']);
