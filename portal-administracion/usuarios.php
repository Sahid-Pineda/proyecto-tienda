<?php
declare(strict_types=1);
require_once 'config.php';
require_once 'src/services/UsuarioService.php';

if (empty($_SESSION['admin_autenticado'])) {
    header('Location: login.php');
    exit;
}

$usuarioService = new UsuarioService(API_BASE_URL, false);
$usuarios = $usuarioService->listarUsuarios();

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $rol = trim($_POST['rol'] ?? 'vendedor');

    if ($action === 'crear' && $nombre !== '' && $username !== '') {
        $usuarioService->crearUsuario([
            'nombre' => $nombre,
            'username' => $username,
            'email' => $email,
            'password' => $password !== '' ? $password : '123456',
            'rol' => $rol,
        ]);
        $mensaje = 'Usuario creado correctamente.';
        header('Location: usuarios.php');
        exit;
    }

    if ($action === 'actualizar' && $id > 0) {
        $usuario = new Usuario($id, $nombre, $email, $rol, $username !== '' ? $username : null);
        $usuarioService->actualizarUsuario($usuario);
        $mensaje = 'Usuario actualizado correctamente.';
        header('Location: usuarios.php');
        exit;
    }

    if ($action === 'eliminar' && $id > 0) {
        $usuarioService->eliminarUsuario($id);
        $mensaje = 'Usuario eliminado correctamente.';
        header('Location: usuarios.php');
        exit;
    }
}

$sessionUser = $_SESSION['usuario_nombre'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Portal Administrativo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="usuarios.php">Portal Admin</a>
        <div class="d-flex align-items-center">
            <span class="navbar-text text-white me-3">Hola, <?= htmlspecialchars($sessionUser) ?></span>
            <a class="btn btn-outline-light btn-sm" href="login.php?logout=1">Cerrar sesión</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Gestión de Usuarios</h1>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalUsuario">Nuevo Usuario</button>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= $usuario->getId() ?></td>
                        <td><?= htmlspecialchars($usuario->getNombre()) ?></td>
                        <td><?= htmlspecialchars($usuario->getUsername() ?? $usuario->getEmail()) ?></td>
                        <td><?= htmlspecialchars($usuario->getRol()) ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario" data-id="<?= $usuario->getId() ?>" data-nombre="<?= htmlspecialchars($usuario->getNombre()) ?>" data-username="<?= htmlspecialchars($usuario->getUsername() ?? $usuario->getEmail()) ?>" data-email="<?= htmlspecialchars($usuario->getEmail()) ?>" data-rol="<?= htmlspecialchars($usuario->getRol()) ?>">Editar</button>
                            <form action="usuarios.php" method="post" class="d-inline">
                                <input type="hidden" name="action" value="eliminar">
                                <input type="hidden" name="id" value="<?= $usuario->getId() ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="usuarios.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUsuarioLabel">Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="crear" id="usuarioAction">
                    <input type="hidden" name="id" id="usuarioId">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" id="usuarioNombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="username" id="usuarioUsername" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="usuarioEmail" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" id="usuarioPassword" class="form-control" placeholder="Dejar vacío para usar 123456">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="rol" id="usuarioRol" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="vendedor">Vendedor</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const modalUsuario = document.getElementById('modalUsuario');
modalUsuario.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const title = modalUsuario.querySelector('.modal-title');
    const actionInput = modalUsuario.querySelector('#usuarioAction');
    const idInput = modalUsuario.querySelector('#usuarioId');
    const nombreInput = modalUsuario.querySelector('#usuarioNombre');
    const usernameInput = modalUsuario.querySelector('#usuarioUsername');
    const emailInput = modalUsuario.querySelector('#usuarioEmail');
    const passwordInput = modalUsuario.querySelector('#usuarioPassword');
    const rolInput = modalUsuario.querySelector('#usuarioRol');

    if (button) {
        const usuarioId = button.getAttribute('data-id');
        if (usuarioId) {
            title.textContent = 'Editar Usuario';
            actionInput.value = 'actualizar';
            idInput.value = usuarioId;
            nombreInput.value = button.getAttribute('data-nombre');
            usernameInput.value = button.getAttribute('data-username') || '';
            emailInput.value = button.getAttribute('data-email') || '';
            passwordInput.value = '';
            rolInput.value = button.getAttribute('data-rol');
            return;
        }
    }

    title.textContent = 'Nuevo Usuario';
    actionInput.value = 'crear';
    idInput.value = '';
    nombreInput.value = '';
    usernameInput.value = '';
    emailInput.value = '';
    passwordInput.value = '';
    rolInput.value = 'vendedor';
});
</script>
</body>
</html>
