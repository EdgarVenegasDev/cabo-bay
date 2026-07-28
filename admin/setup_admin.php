<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';

define('SETUP_SECRET', 'cabobay-setup-2026-temporal');

$done = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['secret'] ?? '') !== SETUP_SECRET) {
        $error = 'Clave secreta incorrecta.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (strlen($password) < 8) {
            $error = 'La contrasena debe tener al menos 8 caracteres.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare(
                'INSERT INTO admin_users (username, email, password_hash, role) VALUES (?, ?, ?, "superadmin")'
            );
            $stmt->execute([$username, $email, $hash]);
            $done = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Setup admin</title></head>
<body style="font-family: sans-serif; max-width: 420px; margin: 60px auto;">
<h2>Crear primer usuario admin</h2>

<?php if ($done): ?>
    <p style="color: green;">Usuario creado. Borra este archivo del servidor ahora mismo.</p>
<?php else: ?>
    <?php if ($error): ?><p style="color: red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST">
        <label>Clave secreta del script</label><br>
        <input type="password" name="secret" required style="width:100%; margin-bottom:10px;"><br>
        <label>Usuario</label><br>
        <input type="text" name="username" required style="width:100%; margin-bottom:10px;"><br>
        <label>Email</label><br>
        <input type="email" name="email" required style="width:100%; margin-bottom:10px;"><br>
        <label>Contrasena</label><br>
        <input type="password" name="password" required style="width:100%; margin-bottom:10px;"><br>
        <button type="submit">Crear admin</button>
    </form>
<?php endif; ?>
</body>
</html>
