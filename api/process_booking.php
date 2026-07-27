<?php
// api/process_booking.php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['booking_error'] = 'Invalid request method.';
    header('Location: ../error.php');
    exit;
}

/* ── Normalizar trip_type ───────────────────────────────────── */
function normalize_trip_type(string $raw): string {
    $raw = strtolower(trim($raw));
    if (in_array($raw, ['roundtrip', 'round_trip', 'round-trip'], true)) return 'roundtrip';
    return 'oneway';
}

/* ==============================================================
   FUNCIONES PARA EMAILS HTML CON PALETA AZUL MARINO / BLANCO
   ============================================================== */
function build_html_email(string $title, string $content, string $reference = ''): string {
    $year = date('Y');
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f7fc; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { background-color: #0a2540; padding: 24px 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 600; letter-spacing: -0.5px; }
        .content { padding: 30px 25px; color: #1e2a3a; line-height: 1.5; }
        .content h2 { color: #0a2540; font-size: 20px; margin-top: 0; border-left: 4px solid #0a2540; padding-left: 12px; }
        .details { background-color: #f8fafc; border-radius: 10px; padding: 16px 20px; margin: 20px 0; border: 1px solid #e2e8f0; }
        .details p { margin: 8px 0; }
        .label { font-weight: 700; color: #0a2540; display: inline-block; min-width: 130px; }
        .footer { background-color: #f0f4f9; padding: 20px; text-align: center; font-size: 12px; color: #4a5b6e; border-top: 1px solid #e2e8f0; }
        .footer a { color: #0a2540; text-decoration: none; }
        .badge { background-color: #0a2540; color: white; padding: 4px 12px; border-radius: 30px; font-size: 12px; display: inline-block; margin-bottom: 16px; }
        hr { border: none; border-top: 1px solid #e2e8f0; margin: 20px 0; }
    </style>
</head>
<body style="margin:0;padding:20px;background-color:#f4f7fc;font-family:Arial,Helvetica,sans-serif;">
    <div class="container" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
        <div class="header" style="background-color:#0a2540;padding:24px 20px;text-align:center;">
            <h1 style="color:#ffffff;margin:0;font-size:24px;font-weight:600;">Cabo Bay Transportation</h1>
        </div>
        <div class="content" style="padding:30px 25px;color:#1e2a3a;line-height:1.5;">
            {$content}
        </div>
        <div class="footer" style="background-color:#f0f4f9;padding:20px;text-align:center;font-size:12px;color:#4a5b6e;border-top:1px solid #e2e8f0;">
            <p>Cabo Bay Transportation — Premium airport transfers & private transportation in Los Cabos</p>
            <p><a href="mailto:info@cabo-bay.com" style="color:#0a2540;text-decoration:none;">info@cabo-bay.com</a> | <a href="tel:+526241193290" style="color:#0a2540;text-decoration:none;">+52 (624) 119 3290</a></p>
            <p>&copy; {$year} Cabo Bay Transportation. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
}

function format_detail_row(string $label, string $value): string {
    return '<p><span class="label" style="font-weight:700;color:#0a2540;display:inline-block;min-width:130px;">' . htmlspecialchars($label) . ':</span> ' . nl2br(htmlspecialchars($value)) . '</p>';
}

// Construir el contenido interior (client o admin) para wedding o regular
function build_booking_email_content(array $data, bool $isAdmin = false, bool $isWedding = false): string {
    $content = '';
    if ($isAdmin) {
        $content .= '<div class="badge" style="background-color:#0a2540;color:white;padding:4px 12px;border-radius:30px;font-size:12px;display:inline-block;margin-bottom:16px;">🔔 NUEVA RESERVA</div>';
        $content .= '<h2 style="color:#0a2540;font-size:20px;margin-top:0;border-left:4px solid #0a2540;padding-left:12px;">Detalles de la reserva</h2>';
    } else {
        $content .= '<h2 style="color:#0a2540;font-size:20px;margin-top:0;border-left:4px solid #0a2540;padding-left:12px;">¡Reserva confirmada, ' . htmlspecialchars($data['name']) . '!</h2>';
        $content .= '<p>Gracias por confiar en <strong>Cabo Bay Transportation</strong>. A continuación los detalles de tu servicio:</p>';
    }

    $content .= '<div class="details" style="background-color:#f8fafc;border-radius:10px;padding:16px 20px;margin:20px 0;border:1px solid #e2e8f0;">';
    
    $content .= format_detail_row('Referencia', $data['reference']);
    
    if ($isWedding) {
        $content .= format_detail_row('Cliente', $data['name'] . ' (' . $data['email'] . ' / ' . $data['phone'] . ')');
        $content .= format_detail_row('Hotel', $data['hotel']);
        $content .= format_detail_row('Pasajeros', (string)$data['passengers']);
        $content .= format_detail_row('Fecha ida', $data['date'] . ' a las ' . $data['time']);
        $content .= format_detail_row('Vuelo llegada', $data['flight_number']);
        $content .= format_detail_row('Tipo de viaje', $data['trip_type_label']);
        if ($data['is_round']) {
            $content .= '<hr style="border:none;border-top:1px solid #e2e8f0;margin:16px 0;">';
            $content .= format_detail_row('Fecha regreso', $data['return_date'] . ' a las ' . $data['return_time']);
            $content .= format_detail_row('Vuelo regreso', $data['return_flight']);
        }
        if (!empty($data['special_requests'])) {
            $content .= format_detail_row('Peticiones especiales', $data['special_requests']);
        }
        $content .= format_detail_row('Total', '$' . number_format($data['price'], 2) . ' USD');
        if (!$isAdmin) {
            $content .= '<p style="margin-top:16px; font-style:italic;">💵 Pago en efectivo directamente al conductor.</p>';
        }
    } else {
        // Regular booking
        $content .= format_detail_row('Cliente', $data['name'] . ' (' . $data['email'] . ' / ' . $data['phone'] . ')');
        $content .= format_detail_row('Destino', $data['area'] . ' (' . $data['zone'] . ')');
        $content .= format_detail_row('Tipo de viaje', $data['trip_type_label']);
        $content .= format_detail_row('Pasajeros', (string)$data['passengers']);
        $content .= format_detail_row('Fecha ida', $data['date'] . ' a las ' . $data['time']);
        if (!empty($data['flight_number'])) {
            $content .= format_detail_row('Vuelo llegada', $data['flight_number']);
        }
        if (!empty($data['arrival_time'])) {
            $content .= format_detail_row('Hora de llegada', $data['arrival_time']);
        }
        if ($data['is_round']) {
            $content .= '<hr style="border:none;border-top:1px solid #e2e8f0;margin:16px 0;">';
            $content .= format_detail_row('Fecha regreso', $data['return_date'] . ' a las ' . $data['return_time']);
            $content .= format_detail_row('Vuelo regreso', $data['return_flight_number']);
        }
        if (!empty($data['special_requests'])) {
            $content .= format_detail_row('Peticiones especiales', $data['special_requests']);
        }
        $content .= format_detail_row('Total', '$' . number_format($data['price'], 2) . ' USD');
        if (!$isAdmin) {
            $content .= '<p style="margin-top:16px; font-style:italic;">💵 Pago en efectivo al conductor (no incluye propina).</p>';
        }
    }
    
    $content .= '</div>';
    
    if (!$isAdmin) {
        $content .= '<p style="margin-top:20px;">Si tienes alguna pregunta o necesitas modificar tu reserva, responde a este correo o contáctanos al <strong>+52 (624) 119 3290</strong> (WhatsApp disponible).</p>';
        $content .= '<p style="margin-top:20px;">¡Disfruta tu viaje con nosotros!</p>';
        $content .= '<p>El equipo de <strong>Cabo Bay Transportation</strong></p>';
    } else {
        $content .= '<p style="margin-top:16px; font-size:13px; background-color:#eef2ff; padding:10px; border-radius:8px;">📌 Acción requerida: Por favor verifica los datos y coordina el servicio con el cliente.</p>';
    }
    
    return $content;
}

function send_html_email(string $to, string $subject, string $htmlContent, string $fromEmail): bool {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Cabo Bay Transportation <{$fromEmail}>\r\n";
    return @mail($to, $subject, $htmlContent, $headers);
}

/* ==============================================================
   PROCESAMIENTO DEL FORMULARIO
   ============================================================== */
$bookingType = $_POST['booking_type'] ?? 'regular';

/* ─────────────────────────────────────────────────────────────
   CASO WEDDING
   ───────────────────────────────────────────────────────────── */
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

    // Datos comunes para los correos
    $emailData = [
        'reference'        => $reference,
        'name'             => $name,
        'email'            => $email,
        'phone'            => $phone,
        'hotel'            => $hotel,
        'passengers'       => $passengers,
        'date'             => $date,
        'time'             => $time,
        'flight_number'    => $flight_number,
        'trip_type_label'  => $isRound ? 'Round Trip (ida y vuelta)' : 'One Way (solo ida)',
        'is_round'         => $isRound,
        'return_date'      => $return_date,
        'return_time'      => $return_time,
        'return_flight'    => $return_flight,
        'special_requests' => $special_req,
        'price'            => $price,
    ];

    // Contenido HTML
    $userHtml = build_html_email(
        "Wedding Transfer Confirmation - $reference",
        build_booking_email_content($emailData, false, true),
        $reference
    );
    $adminHtml = build_html_email(
        "New Wedding Booking - $reference",
        build_booking_email_content($emailData, true, true),
        $reference
    );

    $adminSent = send_html_email(ADMIN_EMAIL, "Wedding Transportation Booking — $reference", $adminHtml, BOOKING_EMAIL);
    $userSent  = send_html_email($email, "Wedding Transfer Confirmation — $reference | " . SITE_NAME, $userHtml, BOOKING_EMAIL);

    if ($adminSent && $userSent) {
        $_SESSION['booking_success'] = true;
        $_SESSION['booking_data'] = [
            'name' => $name, 'price' => $price,
            'date' => $date, 'reference' => $reference, 'type' => 'wedding',
        ];
        header('Location: ../success.php'); exit;
    }

    $_SESSION['booking_error'] = 'Error sending confirmation. Please try again.';
    header('Location: ../error.php'); exit;
}

/* ─────────────────────────────────────────────────────────────
   CASO REGULAR
   ───────────────────────────────────────────────────────────── */
$required_regular = ['zone', 'area', 'trip_type', 'passengers', 'date', 'time', 'name', 'email', 'phone'];
foreach ($required_regular as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['booking_error'] = "Missing required field: $field";
        header('Location: ../error.php'); exit;
    }
}

// Cargar precios
$pricingFile = __DIR__ . '/../data/pricing.json';
if (!file_exists($pricingFile)) {
    $_SESSION['booking_error'] = 'Pricing system unavailable.';
    header('Location: ../error.php'); exit;
}
$pricingData = json_decode(file_get_contents($pricingFile), true);
if (!$pricingData) {
    $_SESSION['booking_error'] = 'Pricing data invalid.';
    header('Location: ../error.php'); exit;
}

// Buscar zona
$selectedZone = null;
foreach ($pricingData['zones'] as $zone) {
    if ($zone['name'] === $_POST['zone']) { $selectedZone = $zone; break; }
}
if (!$selectedZone) {
    $_SESSION['booking_error'] = 'Invalid destination zone.';
    header('Location: ../error.php'); exit;
}

$tripType = normalize_trip_type($_POST['trip_type'] ?? 'oneway');
$isRound  = $tripType === 'roundtrip';
$price    = $isRound ? $selectedZone['roundTrip'] : $selectedZone['oneWay'];

// Sanitizar
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

// Datos para correos (regular)
$emailDataRegular = [
    'reference'            => $reference,
    'name'                 => $s['name'],
    'email'                => $s['email'],
    'phone'                => $s['phone'],
    'zone'                 => $s['zone'],
    'area'                 => $s['area'],
    'trip_type_label'      => $s['trip_type_label'],
    'passengers'           => $s['passengers'],
    'date'                 => $s['date'],
    'time'                 => $s['time'],
    'flight_number'        => $s['flight_number'],
    'arrival_time'         => $s['arrival_time'],
    'is_round'             => $isRound,
    'return_date'          => $s['return_date'],
    'return_time'          => $s['return_time'],
    'return_flight_number' => $s['return_flight_number'],
    'special_requests'     => $s['special_requests'],
    'price'                => $s['price'],
];

// Construir emails HTML
$userHtmlRegular = build_html_email(
    "Booking Confirmation - $reference",
    build_booking_email_content($emailDataRegular, false, false),
    $reference
);
$adminHtmlRegular = build_html_email(
    "New Booking - $reference | {$s['zone']}",
    build_booking_email_content($emailDataRegular, true, false),
    $reference
);

$userSent  = send_html_email($s['email'], "Booking Confirmation — $reference | " . SITE_NAME, $userHtmlRegular, BOOKING_EMAIL);
$adminSent = send_html_email(ADMIN_EMAIL, "New Booking — $reference | " . $s['zone'], $adminHtmlRegular, BOOKING_EMAIL);

if ($userSent && $adminSent) {
    $_SESSION['booking_success'] = true;
    $_SESSION['booking_data'] = [
        'name'      => $s['name'],
        'price'     => $s['price'],
        'date'      => $s['date'],
        'reference' => $reference,
        'type'      => 'regular',
    ];
    header('Location: ../success.php'); exit;
}

$_SESSION['booking_error'] = 'Error processing booking. Please try again.';
header('Location: ../error.php'); exit;
?>