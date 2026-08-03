<?php
function normalize_trip_type(string $raw): string {
    $raw = strtolower(trim($raw));
    if (in_array($raw, ['roundtrip', 'round_trip', 'round-trip'], true)) return 'roundtrip';
    return 'oneway';
}

function save_booking_to_db(array $b): ?int {
    try {
        $pdo = Database::getConnection();

        $sql = "INSERT INTO bookings (
                    reference, booking_type, full_name, email, phone,
                    zone_id, zone_name, area, hotel,
                    trip_type, passengers, service_date, service_time,
                    flight_number, arrival_time,
                    return_date, return_time, return_flight_number,
                    special_requests, price, status,
                    payment_method, payment_status, mp_payment_id, amount_mxn
                ) VALUES (
                    :reference, :booking_type, :full_name, :email, :phone,
                    :zone_id, :zone_name, :area, :hotel,
                    :trip_type, :passengers, :service_date, :service_time,
                    :flight_number, :arrival_time,
                    :return_date, :return_time, :return_flight_number,
                    :special_requests, :price, 'pending',
                    :payment_method, :payment_status, :mp_payment_id, :amount_mxn
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':reference'             => $b['reference'],
            ':booking_type'          => $b['booking_type'],
            ':full_name'             => $b['full_name'],
            ':email'                 => $b['email'],
            ':phone'                 => $b['phone'],
            ':zone_id'               => $b['zone_id'] ?? null,
            ':zone_name'             => $b['zone_name'] ?? null,
            ':area'                  => $b['area'] ?? null,
            ':hotel'                 => $b['hotel'] ?? null,
            ':trip_type'             => $b['trip_type'],
            ':passengers'            => $b['passengers'],
            ':service_date'          => $b['service_date'],
            ':service_time'          => $b['service_time'],
            ':flight_number'         => $b['flight_number'] ?: null,
            ':arrival_time'          => $b['arrival_time'] ?: null,
            ':return_date'           => $b['return_date'] ?: null,
            ':return_time'           => $b['return_time'] ?: null,
            ':return_flight_number'  => $b['return_flight_number'] ?: null,
            ':special_requests'      => $b['special_requests'] ?: null,
            ':price'                 => $b['price'],
            ':payment_method'        => $b['payment_method'] ?? 'cash',
            ':payment_status'        => $b['payment_status'] ?? 'none',
            ':mp_payment_id'         => $b['mp_payment_id'] ?? null,
            ':amount_mxn'            => $b['amount_mxn'] ?? null,
        ]);

        return (int)$pdo->lastInsertId();

    } catch (Throwable $e) {
        error_log('[booking DB save failed] ref=' . ($b['reference'] ?? '?') . ' error=' . $e->getMessage());
        return null;
    }
}

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

function build_booking_email_content(array $data, bool $isAdmin = false, bool $isWedding = false): string {
    $content = '';
    if ($isAdmin) {
        $content .= '<div class="badge" style="background-color:#0a2540;color:white;padding:4px 12px;border-radius:30px;font-size:12px;display:inline-block;margin-bottom:16px;">NUEVA RESERVA</div>';
        $content .= '<h2 style="color:#0a2540;font-size:20px;margin-top:0;border-left:4px solid #0a2540;padding-left:12px;">Detalles de la reserva</h2>';
    } else {
        $content .= '<h2 style="color:#0a2540;font-size:20px;margin-top:0;border-left:4px solid #0a2540;padding-left:12px;">Reserva confirmada, ' . htmlspecialchars($data['name']) . '!</h2>';
        $content .= '<p>Gracias por confiar en <strong>Cabo Bay Transportation</strong>. A continuacion los detalles de tu servicio:</p>';
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
    } else {
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
    }

    if (!empty($data['payment_method']) && $data['payment_method'] === 'card') {
        $content .= format_detail_row('Metodo de pago', 'Tarjeta (pagado en linea)');
        if (!empty($data['amount_mxn'])) {
            $content .= format_detail_row('Cargo procesado', '$' . number_format($data['amount_mxn'], 2) . ' MXN');
        }
        if (!$isAdmin) {
            $content .= '<p style="margin-top:16px; font-style:italic;">Tu pago ya fue procesado, no necesitas pagar nada al conductor.</p>';
        }
    } else {
        if (!$isAdmin) {
            $content .= '<p style="margin-top:16px; font-style:italic;">Pago en efectivo al conductor (no incluye propina).</p>';
        }
    }

    $content .= '</div>';

    if (!$isAdmin) {
        $content .= '<p style="margin-top:20px;">Si tienes alguna pregunta o necesitas modificar tu reserva, responde a este correo o contactanos al <strong>+52 (624) 119 3290</strong> (WhatsApp disponible).</p>';
        $content .= '<p style="margin-top:20px;">Disfruta tu viaje con nosotros!</p>';
        $content .= '<p>El equipo de <strong>Cabo Bay Transportation</strong></p>';
    } else {
        $content .= '<p style="margin-top:16px; font-size:13px; background-color:#eef2ff; padding:10px; border-radius:8px;">Accion requerida: Por favor verifica los datos y coordina el servicio con el cliente.</p>';
    }

    return $content;
}

function send_html_email(string $to, string $subject, string $htmlContent, string $fromEmail): bool {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Cabo Bay Transportation <{$fromEmail}>\r\n";
    return @mail($to, $subject, $htmlContent, $headers);
}

function get_usd_to_mxn_rate(): float {
    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'usd_to_mxn_rate' LIMIT 1");
        $stmt->execute();
        $rate = $stmt->fetchColumn();
        return $rate ? (float)$rate : 18.50;
    } catch (Throwable $e) {
        error_log('[get_usd_to_mxn_rate failed] ' . $e->getMessage());
        return 18.50;
    }
}
