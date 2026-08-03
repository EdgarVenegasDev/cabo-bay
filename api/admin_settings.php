<?php
require_once __DIR__ . '/../admin/includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$pdo    = Database::getConnection();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        case 'update_rate':
            $rate = (float)($_POST['rate'] ?? 0);
            if ($rate <= 0 || $rate > 100) {
                throw new InvalidArgumentException('El tipo de cambio debe ser un numero razonable (entre 0 y 100).');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO settings (`key`, `value`) VALUES ('usd_to_mxn_rate', ?)
                 ON DUPLICATE KEY UPDATE value = ?"
            );
            $stmt->execute([$rate, $rate]);

            echo json_encode(['ok' => true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Accion desconocida.']);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('[admin_settings error] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno.']);
}
