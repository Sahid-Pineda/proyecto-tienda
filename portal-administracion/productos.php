<?php
declare(strict_types=1);
require_once 'config.php';
require_once 'src/services/ProductoService.php';
require_once 'src/services/CategoriaService.php';
require_once 'src/services/ProveedorService.php';
require_once 'src/Producto.php';
require_once 'includes/header.php';

if (empty($_SESSION['admin_autenticado'])) {
    header('Location: login.php');
    exit;
}

$productoService = new ProductoService(API_BASE_URL, false);
$categoriaService = new CategoriaService(API_BASE_URL, true);
$proveedorService = new ProveedorService(API_BASE_URL, false);

$productos = $productoService->listarProductos();
$categorias = $categoriaService->listarCategorias();
$proveedores = $proveedorService->listarProveedores();

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = (float)($_POST['precio'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $categoriaId = (int)($_POST['categoria_id'] ?? 0);
    $proveedorId = (int)($_POST['proveedor_id'] ?? 0);

    if ($action === 'crear' && $nombre !== '' && $categoriaId > 0 && $proveedorId > 0) {
        $productoService->crearProducto([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock,
            'categoria_id' => $categoriaId,
            'proveedor_id' => $proveedorId,
        ]);
        header('Location: productos.php');
        exit;
    }

    if ($action === 'actualizar' && $id > 0) {
        $producto = new Producto($id, $nombre, $precio, $stock, $categoriaId, $proveedorId, $descripcion);
        $productoService->actualizarProducto($producto);
        header('Location: productos.php');
        exit;
    }

    if ($action === 'eliminar' && $id > 0) {
        $productoService->eliminarProducto($id);
        header('Location: productos.php');
        exit;
    }
}

$sessionUser = $_SESSION['usuario_nombre'] ?? 'Administrador';
$title = 'Productos';
$activePage = 'productos';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Gestión de Productos</h1>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalProducto">Nuevo Producto</button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Categoría</th>
                    <th>Proveedor</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?= $producto->getId() ?></td>
                        <td><?= htmlspecialchars($producto->getNombre()) ?></td>
                        <td><?= htmlspecialchars($producto->getDescripcion()) ?></td>
                        <td>$<?= number_format($producto->getPrecio(), 2) ?></td>
                        <td><?= $producto->getStock() ?></td>
                        <td><?= htmlspecialchars(array_reduce($categorias, fn($carry, $cat) => $carry ?? ($cat->getId() === $producto->getCategoriaId() ? $cat->getNombre() : null), null) ?? 'Sin categoría') ?></td>
                        <td><?= htmlspecialchars(array_reduce($proveedores, fn($carry, $prov) => $carry ?? ($prov->getId() === $producto->getProveedorId() ? $prov->getNombre() : null), null) ?? 'Sin proveedor') ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalProducto" data-id="<?= $producto->getId() ?>" data-nombre="<?= htmlspecialchars($producto->getNombre()) ?>" data-descripcion="<?= htmlspecialchars($producto->getDescripcion()) ?>" data-precio="<?= $producto->getPrecio() ?>" data-stock="<?= $producto->getStock() ?>" data-categoria="<?= $producto->getCategoriaId() ?>" data-proveedor="<?= $producto->getProveedorId() ?>">Editar</button>
                            <form action="productos.php" method="post" class="d-inline">
                                <input type="hidden" name="action" value="eliminar">
                                <input type="hidden" name="id" value="<?= $producto->getId() ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este producto?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="productos.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProductoLabel">Nuevo Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="crear" id="productoAction">
                    <input type="hidden" name="id" id="productoId">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" id="productoNombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" id="productoDescripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio</label>
                        <input type="number" step="0.01" name="precio" id="productoPrecio" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" id="productoStock" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="categoria_id" id="productoCategoria" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria->getId() ?>"><?= htmlspecialchars($categoria->getNombre()) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Proveedor</label>
                        <select name="proveedor_id" id="productoProveedor" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <option value="<?= $proveedor->getId() ?>"><?= htmlspecialchars($proveedor->getNombre()) ?></option>
                            <?php endforeach; ?>
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
const modalProducto = document.getElementById('modalProducto');
modalProducto.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const title = modalProducto.querySelector('.modal-title');
    const actionInput = modalProducto.querySelector('#productoAction');
    const idInput = modalProducto.querySelector('#productoId');
    const nombreInput = modalProducto.querySelector('#productoNombre');
    const descripcionInput = modalProducto.querySelector('#productoDescripcion');
    const precioInput = modalProducto.querySelector('#productoPrecio');
    const stockInput = modalProducto.querySelector('#productoStock');
    const categoriaInput = modalProducto.querySelector('#productoCategoria');
    const proveedorInput = modalProducto.querySelector('#productoProveedor');

    if (button) {
        const productoId = button.getAttribute('data-id');
        if (productoId) {
            title.textContent = 'Editar Producto';
            actionInput.value = 'actualizar';
            idInput.value = productoId;
            nombreInput.value = button.getAttribute('data-nombre');
            descripcionInput.value = button.getAttribute('data-descripcion') || '';
            precioInput.value = button.getAttribute('data-precio');
            stockInput.value = button.getAttribute('data-stock');
            categoriaInput.value = button.getAttribute('data-categoria');
            proveedorInput.value = button.getAttribute('data-proveedor');
            return;
        }
    }

    title.textContent = 'Nuevo Producto';
    actionInput.value = 'crear';
    idInput.value = '';
    nombreInput.value = '';
    descripcionInput.value = '';
    precioInput.value = '';
    stockInput.value = 0;
    categoriaInput.value = '';
    proveedorInput.value = '';
});
</script>
</body>
</html>
