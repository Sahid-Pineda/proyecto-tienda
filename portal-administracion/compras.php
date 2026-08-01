<?php
declare(strict_types=1);
require_once 'config.php';
require_once 'src/services/ProveedorService.php';
require_once 'src/services/CategoriaService.php';
require_once 'src/services/CompraService.php';
require_once 'src/services/ProductoService.php';
require_once 'src/utils/InventarioHelper.php';

if (empty($_SESSION['admin_autenticado'])) {
    header('Location: login.php');
    exit;
}

$proveedorService = new ProveedorService(API_BASE_URL, false);
$categoriaService = new CategoriaService(API_BASE_URL, false);
$compraService = new CompraService(API_BASE_URL, false);
$productoService = new ProductoService(API_BASE_URL, false);

$proveedores = $proveedorService->listarProveedores();
$categorias = $categoriaService->listarCategorias();
$productos = $productoService->listarProductos();

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proveedorId = (int)($_POST['proveedor_id'] ?? 0);
    $productoId = (int)($_POST['producto_id'] ?? 0);
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $fecha = trim($_POST['fecha'] ?? date('Y-m-d'));

    if ($proveedorId > 0 && $productoId > 0 && $cantidad > 0) {
        $compra = $compraService->registrarCompra($productoId, $proveedorId, $cantidad, $fecha);
        $productos = $productoService->listarProductos();
        $mensaje = 'Compra registrada y stock actualizado correctamente. Total: $' . number_format($compra->getTotal(), 2);
    }
}

$proveedoresActivos = InventarioHelper::contarProveedores($proveedores);

$sessionUser = $_SESSION['usuario_nombre'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compras - Portal Administrativo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="compras.php">Portal Admin</a>
        <div class="d-flex align-items-center">
            <span class="navbar-text text-white me-3">Hola, <?= htmlspecialchars($sessionUser) ?></span>
            <a class="btn btn-outline-light btn-sm" href="login.php?logout=1">Cerrar sesión</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h1 class="h3">Registro de Compras</h1>
            <p class="text-muted">Registra la compra de inventario y actualiza el stock del producto.</p>
        </div>
        <div class="col-md-4 text-md-end align-self-center">
            <a class="btn btn-secondary" href="categorias.php">Volver a Categorías</a>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">Formulario de Compra</div>
                <div class="card-body">
                    <form method="post" action="compras.php">
                        <div class="mb-3">
                            <label class="form-label">Proveedor</label>
                            <select name="proveedor_id" class="form-select" required>
                                <option value="">Seleccione un proveedor</option>
                                <?php foreach ($proveedores as $proveedor): ?>
                                    <option value="<?= $proveedor->getId() ?>"><?= htmlspecialchars($proveedor->getNombre()) ?> (<?= htmlspecialchars($proveedor->getEmail()) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Producto</label>
                            <select name="producto_id" class="form-select" required>
                                <option value="">Seleccione un producto</option>
                                <?php foreach ($productos as $producto): ?>
                                    <option value="<?= $producto->getId() ?>"><?= htmlspecialchars($producto->getNombre()) ?> - Stock actual: <?= $producto->getStock() ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cantidad</label>
                            <input type="number" name="cantidad" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Registrar Compra</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">Inventario Actual</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Stock</th>
                                    <th>Categoría</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($producto->getNombre()) ?></td>
                                        <td><?= $producto->getStock() ?></td>
                                        <td><?= htmlspecialchars($categoriasPorId[$producto->getCategoriaId()] ?? 'Sin categoría') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Proveedores activos</h5>
                    <p class="card-text">Total de proveedores registrados: <strong><?= $proveedoresActivos ?></strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
