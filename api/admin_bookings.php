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

$validStatuses = ['pending', 'confirmed', 'cancelled'];

try {
    switch ($action) {

        case 'update_status':
            $id     = (int)$_POST['id'];
            $status = $_POST['status'] ?? '';

            if (!in_array($status, $validStatuses, true)) {
                throw new InvalidArgumentException('Estado invalido.');
            }

            $stmt = $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?');
            $stmt->execute([$status, $id]);

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
    error_log('[admin_bookings error] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno. Revisa el log del servidor.']);
}
