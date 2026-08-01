<?php
declare(strict_types=1);
require_once 'config.php';

function autenticarEnApi(string $usuario, string $clave): ?array {
    $ch = curl_init(API_BASE_URL . '/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => $usuario, 'password' => $clave]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode >= 400) {
        return null;
    }

    $data = json_decode($response, true);
    return is_array($data) ? $data : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $clave = trim($_POST['clave'] ?? '');

    $respuesta = autenticarEnApi($usuario, $clave);

    if ($respuesta !== null && !empty($respuesta['ok'])) {
        $_SESSION['admin_autenticado'] = true;
        $_SESSION['usuario_nombre'] = $respuesta['nombre'] ?? $usuario;
        $_SESSION['usuario_tipo'] = $respuesta['tipo'] ?? 'admin';
        $_SESSION['usuario_id'] = $respuesta['id'] ?? 0;
        header('Location: categorias.php');
        exit;
    }

    $error = 'Usuario o contraseña incorrectos.';
}

if (!empty($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Portal Administrativo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Acceso Administrador</h2>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="post" action="login.php">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuario</label>
                            <input type="text" id="usuario" name="usuario" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="clave" class="form-label">Contraseña</label>
                            <input type="password" id="clave" name="clave" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3 text-muted">
                Usuario: admin / Clave: admin123
            </div>
        </div>
    </div>
</div>
</body>
</html>
