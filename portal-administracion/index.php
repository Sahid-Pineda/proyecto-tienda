<?php
require_once 'src/services/ProductoService.php';
require_once 'src/Reportes.php';

$productoService = new ProductoService();
$productos = $productoService->listarProductos();

// Aplicando funciones puras
$productosBajoStock = ReportesHelper::obtenerProductosBajoStock($productos, 5);
$valorTotal = ReportesHelper::calcularValorTotalInventario($productos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Portal Admin - Inventario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">

    <h1>Panel de Control (Admin)</h1>
    <p><strong>Valor Total en Inventario:</strong> $<?= number_format($valorTotal, 2) ?></p>

    <h2 class="mt-4">Alertas de Bajo Stock (<?= count($productosBajoStock) ?>)</h2>
    <ul class="list-group mb-4">
        <?php foreach ($productosBajoStock as $p): ?>
            <li class="list-group-item list-group-item-warning">
                <?= $p->getNombre() ?> - Quedan <?= $p->getStock() ?> unidades.
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Listado de Productos</h2>
    <table class="table table-striped">
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
                <td><?= $p->getNombre() ?></td>
                <td>$<?= number_format($p->getPrecio(), 2) ?></td>
                <td><?= $p->getStock() ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>