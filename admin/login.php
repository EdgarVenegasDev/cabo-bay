<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'secure' => true,
        'httponly' => true, 'samesite' => 'Lax',
    ]);
    session_start();
}

if (!empty($_SESSION['admin_id'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
    $_SESSION['first_attempt']  = $_SESSION['first_attempt'] ?? time();

    if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['first_attempt']) < 300) {
        $error = 'Demasiados intentos. Espera 5 minutos antes de volver a intentar.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Completa usuario y contrasena.';
        } else {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM admin_users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id']       = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_role']     = $user['role'];
                $_SESSION['last_activity']  = time();
                $_SESSION['last_regen']     = time();
                unset($_SESSION['login_attempts'], $_SESSION['first_attempt']);

                $upd = $pdo->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?');
                $upd->execute([$user['id']]);

                header('Location: /admin/dashboard.php');
                exit;
            } else {
                $_SESSION['login_attempts']++;
                $error = 'Usuario o contrasena incorrectos.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Cabo Bay</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/admin/assets/admin.css">
</head>
<body class="min-h-screen flex items-center justify-center bg-navy font-sans">
    <form class="bg-white p-10 rounded-2xl w-80 shadow-2xl" method="POST" autocomplete="off">
        <h1 class="text-xl font-bold text-navy mb-6">Cabo Bay Admin</h1>

        <?php if (!empty($_GET['expired'])): ?>
            <p class="text-amber-600 text-sm bg-amber-50 rounded-lg px-3 py-2 mb-4">Tu sesion expiro por inactividad.</p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="text-red-600 text-sm bg-red-50 rounded-lg px-3 py-2 mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <label class="label-sm" for="username">Usuario</label>
        <input class="input-field mb-4" type="text" id="username" name="username" required autofocus>

        <label class="label-sm" for="password">Contrasena</label>
        <input class="input-field mb-6" type="password" id="password" name="password" required>

        <button class="btn-primary w-full" type="submit">Ingresar</button>
    </form>
</body>
</html>
