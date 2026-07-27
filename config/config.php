<?php
// config.php - Configuración del sitio
// SITE
define('SITE_NAME', 'Cabo Bay Transportation');
define('SITE_TAGLINE', 'Private Transportation in Los Cabos');

// CONTACT
define('SITE_EMAIL', 'no-reply@cabobay.com');     // Sender email
define('ADMIN_EMAIL', 'Cabo.bay.transfers@gmail.com');       // Admin email to receive bookings
define('BOOKING_EMAIL', 'booking@cabo-bay.com');
define('ADMIN_NAME', 'Cabo Bay Admin');
define('INFO_EMAIL', 'info@cabo-bay.com');

define('PHONE_NUMBER', '+52 (624) 119 3290');

define('WHATSAPP_NUMBER', '+52 624 119 3290');

define('PHONE_FORMATTED', '+52 (624) 119 3290');

define('WHATSAPP_FORMATTED', '+52 624 242 4234');

//URL
define(
    'SITE_URL',
    (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? 'https://'
        : 'http://'
    ) . $_SERVER['HTTP_HOST']
);

define('BASE_URL', rtrim(SITE_URL, '/'));

define('ASSETS_URL', BASE_URL . '/assets');


// PATHS
define('ROOT_PATH', dirname(__DIR__));

define('BASE_PATH', '/');

define('INCLUDES_PATH', ROOT_PATH . '/includes');

define('ASSETS_PATH', ROOT_PATH . '/assets');

define('UPLOADS_PATH', ROOT_PATH . '/uploads');

define('DATA_PATH', ROOT_PATH . '/data');

//CURRENCY
define('DEFAULT_CURRENCY', 'USD');

//BODA
define('ENABLE_WEDDING_BOOKINGS', true);

// EMAIL SUBJECTS
define(
    'WEDDING_EMAIL_SUBJECT',
    'Wedding Transportation Request'
);

define(
    'BOOKING_EMAIL_SUBJECT',
    'Transportation Booking Confirmation'
);



// Configuración de tiempo
date_default_timezone_set('America/Mazatlan');
mb_internal_encoding('UTF-8');

// Configuración de reservas
define('MAX_PASSENGERS', 15);
define('FREE_WAITING_MINUTES', 45);
define('GROCERY_STOP_PRICE', 30);

// Configuración de seguridad
define('ENABLE_CSRF', true);
define('SESSION_TIMEOUT', 3600); // 1 hora

// Configuración de email
define('EMAIL_SMTP_HOST', 'localhost');
define('EMAIL_SMTP_PORT', 25);
define('EMAIL_FROM_NAME', SITE_NAME);
define('EMAIL_REPLY_TO', BOOKING_EMAIL);

// Modo debug (cambiar a false en producción)
define('DEBUG_MODE', false);
if (DEBUG_MODE) {

    ini_set('display_errors', 1);

    error_reporting(E_ALL);

} else {

    ini_set('display_errors', 0);
}
define('ENVIRONMENT', 'production');

// Iniciar sesión si no está iniciada
session_set_cookie_params([
    'lifetime' => SESSION_TIMEOUT,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Función para depuración
function debug_log($message, $data = null) {
    if (DEBUG_MODE) {
        $log = date('Y-m-d H:i:s') . ' - ' . $message;
        if ($data !== null) {
            $log .= ': ' . print_r($data, true);
        }
        error_log($log);
    }
}

// Función para sanitizar entradas
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    
    $data = trim($data);

    $data = htmlspecialchars(
        $data,
        ENT_QUOTES,
        'UTF-8'
    );
    
    return $data;
}

// Función para validar email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Función para validar teléfono
function validate_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10;
}

// Generar referencia de reserva
function generate_booking_reference() {

    return 'CB-'
        . strtoupper(
            bin2hex(random_bytes(3))
        );
}

// Verificar si es móvil
function is_mobile() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $mobile_agents = [
        'android', 'iphone', 'ipod', 'blackberry', 
        'webos', 'opera mini', 'windows phone', 'iemobile'
    ];
    
    foreach ($mobile_agents as $agent) {
        if (stripos($user_agent, $agent) !== false) {
            return true;
        }
    }
    return false;
}

// Obtener año actual
function current_year() {
    return date('Y');
}

// Redireccionar con mensaje
function redirect_with_message($url, $type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
    header('Location: ' . $url);
    exit;
}

// Mostrar mensaje flash
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        
        $classes = [
            'success' => 'alert-success',
            'error' => 'alert-error',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ];
        
        $class = $classes[$message['type']] ?? $classes['info'];
        
        return '<div class="alert ' . $class . '">' . htmlspecialchars($message['message']) . '</div>';
    }
    return '';
}

function asset($path) {

    return ASSETS_URL . '/' . ltrim($path, '/');
}

function redirect($url) {

    header('Location: ' . $url);

    exit;
}

?>