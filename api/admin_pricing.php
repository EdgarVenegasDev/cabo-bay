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

        case 'update_zone':
            $id             = (int)$_POST['id'];
            $oneWay         = (float)$_POST['one_way_price'];
            $roundTrip      = (float)$_POST['round_trip_price'];
            $isFeatured     = isset($_POST['is_featured']) ? 1 : 0;
            $isActive       = isset($_POST['is_active']) ? 1 : 0;
            $badgeText      = trim($_POST['badge_text'] ?? '') ?: null;
            $badgeClass     = trim($_POST['badge_class'] ?? '') ?: null;
            $hotelsSummary  = trim($_POST['hotels_summary'] ?? '') ?: null;
            $displayOrder   = (int)($_POST['display_order'] ?? 0);

            if ($oneWay < 0 || $roundTrip < 0) {
                throw new InvalidArgumentException('Los precios no pueden ser negativos.');
            }

            $stmt = $pdo->prepare(
                'UPDATE zones SET
                    one_way_price = ?, round_trip_price = ?, is_featured = ?, is_active = ?,
                    badge_text = ?, badge_class = ?, hotels_summary = ?, display_order = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $oneWay, $roundTrip, $isFeatured, $isActive,
                $badgeText, $badgeClass, $hotelsSummary, $displayOrder,
                $id,
            ]);

            echo json_encode(['ok' => true]);
            break;

        case 'add_area':
            $zoneId = (int)$_POST['zone_id'];
            $name   = trim($_POST['name'] ?? '');

            if ($name === '') {
                throw new InvalidArgumentException('El nombre del hotel/área no puede estar vacío.');
            }

            $stmt = $pdo->prepare('INSERT INTO areas (zone_id, name) VALUES (?, ?)');
            $stmt->execute([$zoneId, $name]);

            echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
            break;

        case 'delete_area':
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare('DELETE FROM areas WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['ok' => true]);
            break;

        case 'rename_area':
            $id   = (int)$_POST['id'];
            $name = trim($_POST['name'] ?? '');

            if ($name === '') {
                throw new InvalidArgumentException('El nombre no puede estar vacío.');
            }

            $stmt = $pdo->prepare('UPDATE areas SET name = ? WHERE id = ?');
            $stmt->execute([$name, $id]);
            echo json_encode(['ok' => true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Acción desconocida.']);
    }
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('[admin_pricing error] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno. Revisa el log del servidor.']);
}