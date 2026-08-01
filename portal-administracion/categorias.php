<?php
declare(strict_types=1);
require_once 'config.php';
require_once 'src/services/CategoriaService.php';
require_once 'src/services/ProveedorService.php';
require_once 'src/utils/InventarioHelper.php';

if (empty($_SESSION['admin_autenticado'])) {
    header('Location: login.php');
    exit;
}

$categoriaService = new CategoriaService(API_BASE_URL, false);
$proveedorService = new ProveedorService(API_BASE_URL, false);

$categorias = $categoriaService->listarCategorias();
$categoriasActivas = InventarioHelper::filtrarCategoriasActivas($categorias);
$categoriasTabla = InventarioHelper::mapearCategoriasParaTabla($categorias);
$proveedores = $proveedorService->listarProveedores();

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'crear') {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $activo = isset($_POST['activo']) ? true : false;

        if ($nombre !== '') {
            $categoriaService->crearCategoria(['nombre' => $nombre, 'descripcion' => $descripcion, 'activo' => $activo]);
            $mensaje = 'Categoría creada correctamente.';
            header('Location: categorias.php');
            exit;
        }
    }

    if ($action === 'actualizar') {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $activo = isset($_POST['activo']) ? true : false;

        $categoria = new Categoria($id, $nombre, $descripcion, $activo);
        $categoriaService->actualizarCategoria($categoria);
        $mensaje = 'Categoría actualizada correctamente.';
        header('Location: categorias.php');
        exit;
    }

    if ($action === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);
        $categoriaService->eliminarCategoria($id);
        $mensaje = 'Categoría eliminada correctamente.';
        header('Location: categorias.php');
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
    <title>Categorías - Portal Administrativo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="categorias.php">Portal Admin</a>
        <div class="d-flex align-items-center">
            <span class="navbar-text text-white me-3">Hola, <?= htmlspecialchars($sessionUser) ?></span>
            <a class="btn btn-outline-light btn-sm" href="login.php?logout=1">Cerrar sesión</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Gestión de Categorías</h1>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCategoria">Nueva Categoría</button>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Resumen</h5>
            <p class="card-text">Total de categorías: <?= count($categorias) ?> | Categorías activas: <?= count($categoriasActivas) ?></p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categoriasTabla as $categoria): ?>
                    <tr>
                        <td><?= $categoria['id'] ?></td>
                        <td><?= htmlspecialchars($categoria['nombre']) ?></td>
                        <td><?= htmlspecialchars($categoria['descripcion']) ?></td>
                        <td><?= $categoria['activo'] ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCategoria" data-id="<?= $categoria['id'] ?>" data-nombre="<?= htmlspecialchars($categoria['nombre']) ?>" data-descripcion="<?= htmlspecialchars($categoria['descripcion']) ?>" data-activo="<?= $categoria['activo'] === 'Activo' ? '1' : '0' ?>">Editar</button>
                            <form action="categorias.php" method="post" class="d-inline">
                                <input type="hidden" name="action" value="eliminar">
                                <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta categoría?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <h2 class="h5">Proveedores registrados</h2>
        <ul class="list-group">
            <?php foreach ($proveedores as $proveedor): ?>
                <li class="list-group-item">
                    <?= htmlspecialchars($proveedor->getNombre()) ?> - <?= htmlspecialchars($proveedor->getEmail()) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="categorias.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCategoriaLabel">Nueva Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="crear" id="categoriaAction">
                    <input type="hidden" name="id" id="categoriaId">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" id="categoriaNombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" id="categoriaDescripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" id="categoriaActivo" checked>
                        <label class="form-check-label" for="categoriaActivo">Activo</label>
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
const modalCategoria = document.getElementById('modalCategoria');
modalCategoria.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const title = modalCategoria.querySelector('.modal-title');
    const actionInput = modalCategoria.querySelector('#categoriaAction');
    const idInput = modalCategoria.querySelector('#categoriaId');
    const nombreInput = modalCategoria.querySelector('#categoriaNombre');
    const descripcionInput = modalCategoria.querySelector('#categoriaDescripcion');
    const activoInput = modalCategoria.querySelector('#categoriaActivo');

    if (button) {
        const categoriaId = button.getAttribute('data-id');
        const categoriaNombre = button.getAttribute('data-nombre');
        const categoriaDescripcion = button.getAttribute('data-descripcion');
        const categoriaActivo = button.getAttribute('data-activo');

        if (categoriaId) {
            title.textContent = 'Editar Categoría';
            actionInput.value = 'actualizar';
            idInput.value = categoriaId;
            nombreInput.value = categoriaNombre;
            descripcionInput.value = categoriaDescripcion;
            activoInput.checked = categoriaActivo === '1';
            return;
        }
    }

    title.textContent = 'Nueva Categoría';
    actionInput.value = 'crear';
    idInput.value = '';
    nombreInput.value = '';
    descripcionInput.value = '';
    activoInput.checked = true;
});
</script>
</body>
</html>
