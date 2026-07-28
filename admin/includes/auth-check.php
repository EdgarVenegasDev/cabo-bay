<?php
/**
 * Incluir esto como PRIMERA línea de cualquier página del admin
 * (después del <?php de apertura), antes de imprimir cualquier HTML.
 *
 * require_once __DIR__ . '/includes/auth-check.php';
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (!isset($_SESSION['last_regen'])) {
    $_SESSION['last_regen'] = time();
} elseif (time() - $_SESSION['last_regen'] > 900) {
    session_regenerate_id(true);
    $_SESSION['last_regen'] = time();
}

if (empty($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header('Location: /admin/login.php?expired=1');
    exit;
}
$_SESSION['last_activity'] = time();
