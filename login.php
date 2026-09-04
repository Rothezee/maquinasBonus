<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: admin.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    $token = (string) ($_POST['csrf'] ?? '');

    if (!csrf_verify($token)) {
        $error = 'Sesion vencida. Recarga e intenta de nuevo.';
    } elseif (!hash_equals(ADMIN_PASSWORD, $password)) {
        $error = 'Contrasena incorrecta.';
    } else {
        $_SESSION['admin'] = true;
        session_regenerate_id(true);
        header('Location: admin.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar - <?php echo e(SITE_NAME); ?></title>
    <link rel="icon" href="<?php echo e(FAVICON_URL); ?>" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
    <div class="noise" aria-hidden="true"></div>
    <main class="auth-card">
        <?php echo brand_html('auth'); ?>
        <h1>Entrar al panel</h1>
        <p>Desde aca cargas maquinas, fotos y marcas cuales estan alquiladas.</p>
        <?php if ($error !== ''): ?>
            <p class="flash error"><?php echo e($error); ?></p>
        <?php endif; ?>
        <form method="post" class="form">
            <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
            <label>
                Contrasena
                <input type="password" name="password" required autofocus>
            </label>
            <button class="btn btn-gold" type="submit">Ingresar</button>
        </form>
        <p class="hint">La contrasena se cambia en <code>includes/config.php</code>.</p>
        <a class="back" href="index.php">Volver al catalogo</a>
    </main>
</body>
</html>
