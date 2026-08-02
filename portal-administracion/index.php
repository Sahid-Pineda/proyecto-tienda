<?php
require_once 'config.php';
require_once 'src/services/ProductoService.php';
require_once 'src/Reportes.php';
require_once 'includes/header.php';

$productoService = new ProductoService(API_BASE_URL, false);
$productos = $productoService->listarProductos();

$productosBajoStock = ReportesHelper::obtenerProductosBajoStock($productos, 5);
$valorTotal = ReportesHelper::calcularValorTotalInventario($productos);

$title = 'Inicio';
$activePage = 'index';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title">Panel de control</h1>
        <p class="page-subtitle">Resumen del inventario y estado de productos.</p>
    </div>
    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">Inventario activo</span>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Valor total</h5>
                <p class="display-6 mb-0">$<?= number_format($valorTotal, 2) ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Alertas de bajo stock</h5>
                <ul class="list-group">
                    <?php foreach ($productosBajoStock as $p): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($p->getNombre()) ?></span>
                            <span class="badge bg-warning text-dark">Quedan <?= $p->getStock() ?> unidades</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">Listado de productos</h5>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= $p->getId() ?></td>
                        <td><?= htmlspecialchars($p->getNombre()) ?></td>
                        <td>$<?= number_format($p->getPrecio(), 2) ?></td>
                        <td><?= $p->getStock() ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>