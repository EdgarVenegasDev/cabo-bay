<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/booking-helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['booking_error'] = 'Invalid request method.';
    header('Location: ../error.php');
    exit;
}

$bookingType = $_POST['booking_type'] ?? 'regular';

if ($bookingType === 'wedding') {

    $required = ['name', 'email', 'phone', 'date', 'time', 'flight_number', 'hotel', 'passengers'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['booking_error'] = "Missing required field: $field";
            header('Location: ../error.php'); exit;
        }
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
    $price         = isset($_POST['price']) ? (float)$_POST['price'] : 85.00;

    if (!validate_email($email)) {
        $_SESSION['booking_error'] = 'Invalid email address.';
        header('Location: ../error.php'); exit;
    }

    if ($isRound) {
        if (empty($return_date) || empty($return_time) || empty($return_flight)) {
            $_SESSION['booking_error'] = 'Please provide return date, time and flight number.';
            header('Location: ../error.php'); exit;
        }
        if ($return_date < $date) {
            $_SESSION['booking_error'] = 'Return date cannot be before the service date.';
            header('Location: ../error.php'); exit;
        }
    }

    $reference = generate_booking_reference();

    save_booking_to_db([
        'reference'             => $reference,
        'booking_type'          => 'wedding',
        'full_name'             => $name,
        'email'                 => $email,
        'phone'                 => $phone,
        'hotel'                 => $hotel,
        'trip_type'             => $tripType,
        'passengers'            => $passengers,
        'service_date'          => $date,
        'service_time'          => $time,
        'flight_number'         => $flight_number,
        'return_date'           => $return_date,
        'return_time'           => $return_time,
        'return_flight_number'  => $return_flight,
        'special_requests'      => $special_req,
        'price'                 => $price,
        'payment_method'        => 'cash',
        'payment_status'        => 'none',
    ]);

    $emailData = [
        'reference' => $reference, 'name' => $name, 'email' => $email, 'phone' => $phone,
        'hotel' => $hotel, 'passengers' => $passengers, 'date' => $date, 'time' => $time,
        'flight_number' => $flight_number,
        'trip_type_label' => $isRound ? 'Round Trip (ida y vuelta)' : 'One Way (solo ida)',
        'is_round' => $isRound, 'return_date' => $return_date, 'return_time' => $return_time,
        'return_flight' => $return_flight, 'special_requests' => $special_req, 'price' => $price,
        'payment_method' => 'cash',
    ];

    $userHtml  = build_html_email("Wedding Transfer Confirmation - $reference", build_booking_email_content($emailData, false, true), $reference);
    $adminHtml = build_html_email("New Wedding Booking - $reference", build_booking_email_content($emailData, true, true), $reference);

    $adminSent = send_html_email(ADMIN_EMAIL, "Wedding Transportation Booking - $reference", $adminHtml, BOOKING_EMAIL);
    $userSent  = send_html_email($email, "Wedding Transfer Confirmation - $reference | " . SITE_NAME, $userHtml, BOOKING_EMAIL);

    if ($adminSent && $userSent) {
        $_SESSION['booking_success'] = true;
        $_SESSION['booking_data'] = ['name' => $name, 'price' => $price, 'date' => $date, 'reference' => $reference, 'type' => 'wedding'];
        header('Location: ../success.php'); exit;
    }

    $_SESSION['booking_error'] = 'Error sending confirmation. Please try again.';
    header('Location: ../error.php'); exit;
}

$required_regular = ['zone', 'area', 'trip_type', 'passengers', 'date', 'time', 'name', 'email', 'phone'];
foreach ($required_regular as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['booking_error'] = "Missing required field: $field";
        header('Location: ../error.php'); exit;
    }
}

$selectedZone = null;
try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare('SELECT id, name, one_way_price, round_trip_price FROM zones WHERE name = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$_POST['zone']]);
    $selectedZone = $stmt->fetch();
} catch (Throwable $e) {
    error_log('[zone lookup failed] ' . $e->getMessage());
}

if (!$selectedZone) {
    $_SESSION['booking_error'] = 'Invalid destination zone.';
    header('Location: ../error.php'); exit;
}

$tripType = normalize_trip_type($_POST['trip_type'] ?? 'oneway');
$isRound  = $tripType === 'roundtrip';
$price    = $isRound ? (float)$selectedZone['round_trip_price'] : (float)$selectedZone['one_way_price'];

$s = [
    'zone'                 => sanitize_input($_POST['zone']),
    'area'                 => sanitize_input($_POST['area']),
    'trip_type_label'      => $isRound ? 'Round Trip' : 'One Way',
    'passengers'           => (int)$_POST['passengers'],
    'date'                 => sanitize_input($_POST['date']),
    'time'                 => sanitize_input($_POST['time']),
    'flight_number'        => sanitize_input($_POST['flight_number']        ?? ''),
    'arrival_time'         => sanitize_input($_POST['arrival_time']         ?? ''),
    'special_requests'     => sanitize_input($_POST['special_requests']     ?? ''),
    'name'                 => sanitize_input($_POST['name']),
    'email'                => sanitize_input($_POST['email']),
    'phone'                => sanitize_input($_POST['phone']),
    'return_date'          => sanitize_input($_POST['return_date']          ?? ''),
    'return_time'          => sanitize_input($_POST['return_time']          ?? ''),
    'return_flight_number' => sanitize_input($_POST['return_flight_number'] ?? ''),
    'price'                => $price,
];

if (!validate_email($s['email'])) {
    $_SESSION['booking_error'] = 'Invalid email address.';
    header('Location: ../error.php'); exit;
}

if ($isRound) {
    if (empty($s['return_date']) || empty($s['return_time']) || empty($s['return_flight_number'])) {
        $_SESSION['booking_error'] = 'Please provide return date, time and flight number.';
        header('Location: ../error.php'); exit;
    }
    if ($s['return_date'] < $s['date']) {
        $_SESSION['booking_error'] = 'Return date cannot be before the service date.';
        header('Location: ../error.php'); exit;
    }
}

$reference = generate_booking_reference();

save_booking_to_db([
    'reference'             => $reference,
    'booking_type'          => 'regular',
    'full_name'             => $s['name'],
    'email'                 => $s['email'],
    'phone'                 => $s['phone'],
    'zone_id'               => $selectedZone['id'],
    'zone_name'             => $selectedZone['name'],
    'area'                  => $s['area'],
    'trip_type'             => $tripType,
    'passengers'            => $s['passengers'],
    'service_date'          => $s['date'],
    'service_time'          => $s['time'],
    'flight_number'         => $s['flight_number'],
    'arrival_time'          => $s['arrival_time'],
    'return_date'           => $s['return_date'],
    'return_time'           => $s['return_time'],
    'return_flight_number'  => $s['return_flight_number'],
    'special_requests'      => $s['special_requests'],
    'price'                 => $s['price'],
    'payment_method'        => 'cash',
    'payment_status'        => 'none',
]);

$emailDataRegular = array_merge($s, [
    'reference'  => $reference,
    'is_round'   => $isRound,
    'payment_method' => 'cash',
]);

$userHtmlRegular  = build_html_email("Booking Confirmation - $reference", build_booking_email_content($emailDataRegular, false, false), $reference);
$adminHtmlRegular = build_html_email("New Booking - $reference | {$s['zone']}", build_booking_email_content($emailDataRegular, true, false), $reference);

$userSent  = send_html_email($s['email'], "Booking Confirmation - $reference | " . SITE_NAME, $userHtmlRegular, BOOKING_EMAIL);
$adminSent = send_html_email(ADMIN_EMAIL, "New Booking - $reference | " . $s['zone'], $adminHtmlRegular, BOOKING_EMAIL);

if ($userSent && $adminSent) {
    $_SESSION['booking_success'] = true;
    $_SESSION['booking_data'] = ['name' => $s['name'], 'price' => $s['price'], 'date' => $s['date'], 'reference' => $reference, 'type' => 'regular'];
    header('Location: ../success.php'); exit;
}

$_SESSION['booking_error'] = 'Error processing booking. Please try again.';
header('Location: ../error.php'); exit;
