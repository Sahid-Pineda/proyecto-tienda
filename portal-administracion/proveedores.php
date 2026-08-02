<?php
declare(strict_types=1);
require_once 'config.php';
require_once 'src/services/ProveedorService.php';
require_once 'includes/header.php';

if (empty($_SESSION['admin_autenticado'])) {
    header('Location: login.php');
    exit;
}

$proveedorService = new ProveedorService(API_BASE_URL, false);
$proveedores = $proveedorService->listarProveedores();

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $contacto = trim($_POST['contacto'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($action === 'crear' && $nombre !== '') {
        $proveedorService->crearProveedor(['nombre' => $nombre, 'contacto' => $contacto, 'telefono' => $telefono, 'email' => $email, 'activo' => true]);
        header('Location: proveedores.php');
        exit;
    }

    if ($action === 'actualizar' && $id > 0) {
        $proveedor = new Proveedor($id, $nombre, $contacto, $telefono, $email, true);
        $proveedorService->actualizarProveedor($proveedor);
        header('Location: proveedores.php');
        exit;
    }

    if ($action === 'eliminar' && $id > 0) {
        $proveedorService->eliminarProveedor($id);
        header('Location: proveedores.php');
        exit;
    }
}

$sessionUser = $_SESSION['usuario_nombre'] ?? 'Administrador';
$title = 'Proveedores';
$activePage = 'proveedores';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Gestión de Proveedores</h1>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalProveedor">Nuevo Proveedor</button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Contacto</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proveedores as $proveedor): ?>
                    <tr>
                        <td><?= $proveedor->getId() ?></td>
                        <td><?= htmlspecialchars($proveedor->getNombre()) ?></td>
                        <td><?= htmlspecialchars($proveedor->getContacto()) ?></td>
                        <td><?= htmlspecialchars($proveedor->getTelefono()) ?></td>
                        <td><?= htmlspecialchars($proveedor->getEmail()) ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalProveedor" data-id="<?= $proveedor->getId() ?>" data-nombre="<?= htmlspecialchars($proveedor->getNombre()) ?>" data-contacto="<?= htmlspecialchars($proveedor->getContacto()) ?>" data-telefono="<?= htmlspecialchars($proveedor->getTelefono()) ?>" data-email="<?= htmlspecialchars($proveedor->getEmail()) ?>">Editar</button>
                            <form action="proveedores.php" method="post" class="d-inline">
                                <input type="hidden" name="action" value="eliminar">
                                <input type="hidden" name="id" value="<?= $proveedor->getId() ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este proveedor?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<div class="modal fade" id="modalProveedor" tabindex="-1" aria-labelledby="modalProveedorLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="proveedores.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProveedorLabel">Nuevo Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="crear" id="proveedorAction">
                    <input type="hidden" name="id" id="proveedorId">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" id="proveedorNombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contacto</label>
                        <input type="text" name="contacto" id="proveedorContacto" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" id="proveedorTelefono" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="proveedorEmail" class="form-control" required>
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
const modalProveedor = document.getElementById('modalProveedor');
modalProveedor.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const title = modalProveedor.querySelector('.modal-title');
    const actionInput = modalProveedor.querySelector('#proveedorAction');
    const idInput = modalProveedor.querySelector('#proveedorId');
    const nombreInput = modalProveedor.querySelector('#proveedorNombre');
    const contactoInput = modalProveedor.querySelector('#proveedorContacto');
    const telefonoInput = modalProveedor.querySelector('#proveedorTelefono');
    const emailInput = modalProveedor.querySelector('#proveedorEmail');

    if (button) {
        const proveedorId = button.getAttribute('data-id');
        if (proveedorId) {
            title.textContent = 'Editar Proveedor';
            actionInput.value = 'actualizar';
            idInput.value = proveedorId;
            nombreInput.value = button.getAttribute('data-nombre');
            contactoInput.value = button.getAttribute('data-contacto');
            telefonoInput.value = button.getAttribute('data-telefono');
            emailInput.value = button.getAttribute('data-email');
            return;
        }
    }

    title.textContent = 'Nuevo Proveedor';
    actionInput.value = 'crear';
    idInput.value = '';
    nombreInput.value = '';
    contactoInput.value = '';
    telefonoInput.value = '';
    emailInput.value = '';
});
</script>
</body>
</html>
