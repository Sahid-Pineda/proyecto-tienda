<?php
declare(strict_types=1);
require_once 'config.php';
require_once 'src/services/VentaService.php';
require_once 'src/services/ProveedorService.php';
require_once 'includes/header.php';

if (empty($_SESSION['admin_autenticado'])) {
    header('Location: login.php');
    exit;
}

$ventaService = new VentaService(API_BASE_URL, false);
$proveedorService = new ProveedorService(API_BASE_URL, false);
$facturas = $ventaService->listarFacturas();

$proveedores = [];
foreach ($proveedorService->listarProveedores() as $proveedor) {
    $proveedores[$proveedor->getId()] = $proveedor->getNombre();
}

$sessionUser = $_SESSION['usuario_nombre'] ?? 'Administrador';
$title = 'Ventas';
$activePage = 'ventas';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3">Consulta de Ventas</h1>
            <p class="text-muted">Listado de facturas generadas y detalles de ventas.</p>
        </div>
        <a class="btn btn-secondary" href="categorias.php">Volver a Categorías</a>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Facturas registradas</h5>
                    <p class="card-text">Total de facturas: <?= count($facturas) ?></p>
                </div>
            </div>

            <div class="accordion" id="accordionFacturas">
                <?php foreach ($facturas as $factura): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $factura->getId() ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $factura->getId() ?>" aria-expanded="false" aria-controls="collapse<?= $factura->getId() ?>">
                                Factura #<?= $factura->getId() ?> - <?= htmlspecialchars($proveedores[$factura->getProveedorId()] ?? 'Proveedor desconocido') ?> - <?= $factura->getFecha() ?> - $<?= number_format($factura->getTotal(), 2) ?>
                            </button>
                        </h2>
                        <div id="collapse<?= $factura->getId() ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $factura->getId() ?>" data-bs-parent="#accordionFacturas">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Cantidad</th>
                                                <th>Precio unitario</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($factura->getItems() as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['producto'] ?? '') ?></td>
                                                    <td><?= (int)($item['cantidad'] ?? 0) ?></td>
                                                    <td>$<?= number_format((float)($item['precio_unitario'] ?? 0.0), 2) ?></td>
                                                    <td>$<?= number_format(((float)($item['precio_unitario'] ?? 0.0)) * ((int)($item['cantidad'] ?? 0)), 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
