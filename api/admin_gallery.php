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

const GALLERY_DIR    = __DIR__ . '/../assets/media/gallery/';
const GALLERY_URL    = 'assets/media/gallery/';
const MAX_SIZE_BYTES  = 20 * 1024 * 1024; // 20MB (los videos pesan mas que fotos)
const ALLOWED_IMAGE_MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
const ALLOWED_VIDEO_MIME = ['video/mp4' => 'mp4'];

try {
    switch ($action) {

        case 'upload':
            if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                throw new InvalidArgumentException('No se recibio ningun archivo valido.');
            }

            $file = $_FILES['photo'];

            if ($file['size'] > MAX_SIZE_BYTES) {
                throw new InvalidArgumentException('El archivo supera el maximo de 20MB.');
            }

            // Validar el tipo REAL del archivo (no confiar en extension ni mime del navegador)
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $realMime = $finfo->file($file['tmp_name']);

            $mediaType = null;
            $ext       = null;

            if (isset(ALLOWED_IMAGE_MIME[$realMime])) {
                // Doble chequeo: confirmar que realmente es una imagen decodificable
                if (getimagesize($file['tmp_name']) === false) {
                    throw new InvalidArgumentException('El archivo no es una imagen valida.');
                }
                $ext       = ALLOWED_IMAGE_MIME[$realMime];
                $mediaType = 'image';
            } elseif (isset(ALLOWED_VIDEO_MIME[$realMime])) {
                $ext       = ALLOWED_VIDEO_MIME[$realMime];
                $mediaType = 'video';
            } else {
                throw new InvalidArgumentException('Formato no soportado. Solo JPG, PNG, WEBP, GIF o MP4.');
            }

            if (!is_dir(GALLERY_DIR)) {
                mkdir(GALLERY_DIR, 0755, true);
            }

            $filename = 'gal_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $destPath = GALLERY_DIR . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                throw new RuntimeException('No se pudo guardar el archivo en el servidor.');
            }

            $caption = trim($_POST['caption'] ?? '');
            $altText = trim($_POST['alt_text'] ?? '') ?: $caption;

            $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(display_order), 0) FROM gallery_photos')->fetchColumn();

            $stmt = $pdo->prepare(
                'INSERT INTO gallery_photos (filename, media_type, original_name, caption, alt_text, display_order, uploaded_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $filename,
                $mediaType,
                $file['name'],
                $caption ?: null,
                $altText ?: null,
                $maxOrder + 1,
                $_SESSION['admin_id'],
            ]);

            echo json_encode([
                'ok'         => true,
                'id'         => $pdo->lastInsertId(),
                'url'        => GALLERY_URL . $filename,
                'media_type' => $mediaType,
                'caption'    => $caption,
            ]);
            break;

        case 'delete':
            $id = (int)$_POST['id'];

            $stmt = $pdo->prepare('SELECT filename FROM gallery_photos WHERE id = ?');
            $stmt->execute([$id]);
            $photo = $stmt->fetch();

            if ($photo) {
                $path = GALLERY_DIR . $photo['filename'];
                if (file_exists($path)) {
                    unlink($path);
                }
                $del = $pdo->prepare('DELETE FROM gallery_photos WHERE id = ?');
                $del->execute([$id]);
            }

            echo json_encode(['ok' => true]);
            break;

        case 'toggle_active':
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare('UPDATE gallery_photos SET is_active = NOT is_active WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['ok' => true]);
            break;

        case 'move':
            $id        = (int)$_POST['id'];
            $direction = $_POST['direction'] ?? '';

            $stmt = $pdo->prepare('SELECT id, display_order FROM gallery_photos WHERE id = ?');
            $stmt->execute([$id]);
            $current = $stmt->fetch();
            if (!$current) throw new InvalidArgumentException('Foto no encontrada.');

            $cmp = $direction === 'up' ? '<' : '>';
            $ord = $direction === 'up' ? 'DESC' : 'ASC';

            $neighborStmt = $pdo->prepare(
                "SELECT id, display_order FROM gallery_photos WHERE display_order $cmp ? ORDER BY display_order $ord LIMIT 1"
            );
            $neighborStmt->execute([$current['display_order']]);
            $neighbor = $neighborStmt->fetch();

            if ($neighbor) {
                $pdo->prepare('UPDATE gallery_photos SET display_order = ? WHERE id = ?')
                    ->execute([$neighbor['display_order'], $current['id']]);
                $pdo->prepare('UPDATE gallery_photos SET display_order = ? WHERE id = ?')
                    ->execute([$current['display_order'], $neighbor['id']]);
            }

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
    error_log('[admin_gallery error] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno. Revisa el log del servidor.']);
}
