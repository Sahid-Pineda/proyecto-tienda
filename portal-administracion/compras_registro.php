<?php
declare(strict_types=1);
require_once 'config.php';
require_once 'src/services/CompraService.php';
require_once 'src/services/ProveedorService.php';
require_once 'src/services/CategoriaService.php';
require_once 'src/services/ProductoService.php';
require_once 'includes/header.php';

if (empty($_SESSION['admin_autenticado'])) {
    header('Location: login.php');
    exit;
}

$compraService = new CompraService(API_BASE_URL, false);
$proveedorService = new ProveedorService(API_BASE_URL, false);
$categoriaService = new CategoriaService(API_BASE_URL, false);
$productoService = new ProductoService(API_BASE_URL, false);

$proveedores = $proveedorService->listarProveedores();
$categorias = $categoriaService->listarCategorias();
$productos = $productoService->listarProductos();
$compras = $compraService->listarCompras();

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proveedorId = (int)($_POST['proveedor_id'] ?? 0);
    $productoId = (int)($_POST['producto_id'] ?? 0);
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $fecha = trim($_POST['fecha'] ?? date('Y-m-d'));

    if ($proveedorId > 0 && $productoId > 0 && $cantidad > 0) {
        $compra = $compraService->registrarCompra($productoId, $proveedorId, $cantidad, $fecha);
        $productos = $productoService->listarProductos();
        $mensaje = 'Compra registrada. Total: $' . number_format($compra->getTotal(), 2);
    }
}

$sessionUser = $_SESSION['usuario_nombre'] ?? 'Administrador';
$title = 'Registro de Compras';
$activePage = 'compras';
?>

<div class="row mb-3">
        <div class="col-md-8">
            <h1 class="h3">Registro de Compras</h1>
            <p class="text-muted">Registra una compra a proveedor y actualiza el stock del producto.</p>
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
                    <form method="post" action="compras_registro.php">
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
                <div class="card-header">Detalle de Compras</div>
                <div class="card-body">
                    <?php if (empty($compras)): ?>
                        <p class="text-muted">No hay compras registradas aún.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Producto</th>
                                        <th>Proveedor</th>
                                        <th>Cantidad</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($compras as $compra): ?>
                                        <tr>
                                            <td><?= $compra->getId() ?></td>
                                            <td><?= htmlspecialchars($productoService->obtenerProducto($compra->getProductoId())->getNombre() ?? 'Desconocido') ?></td>
                                            <td><?= htmlspecialchars($proveedorService->obtenerProveedor($compra->getProveedorId())->getNombre() ?? 'Desconocido') ?></td>
                                            <td><?= $compra->getCantidad() ?></td>
                                            <td><?= htmlspecialchars($compra->getFecha()) ?></td>
                                            <td>$<?= number_format($compra->getTotal(), 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
