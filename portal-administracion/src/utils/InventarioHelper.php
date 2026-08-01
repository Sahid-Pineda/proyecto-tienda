<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Proveedor.php';
require_once __DIR__ . '/../Producto.php';

class InventarioHelper {
    public static function filtrarCategoriasActivas(array $categorias): array {
        return array_filter($categorias, fn(Categoria $categoria) => $categoria->isActivo());
    }

    public static function mapearCategoriasParaTabla(array $categorias): array {
        return array_map(fn(Categoria $categoria) => [
            'id' => $categoria->getId(),
            'nombre' => $categoria->getNombre(),
            'descripcion' => $categoria->getDescripcion(),
            'activo' => $categoria->isActivo() ? 'Activo' : 'Inactivo',
        ], $categorias);
    }

    public static function filtrarProveedoresPorEmail(array $proveedores, string $termino): array {
        return array_filter($proveedores, fn(Proveedor $proveedor) => stripos($proveedor->getEmail(), $termino) !== false);
    }

    public static function contarProveedores(array $proveedores): int {
        return array_reduce($proveedores, fn(int $carry, Proveedor $proveedor) => $carry + 1, 0);
    }

    public static function incrementarStock(array $productos, int $productoId, int $cantidad): array {
        return array_map(function($producto) use ($productoId, $cantidad) {
            if ($producto instanceof Producto && $producto->getId() === $productoId) {
                return new Producto(
                    $producto->getId(),
                    $producto->getNombre(),
                    $producto->getPrecio(),
                    $producto->getStock() + $cantidad,
                    $producto->getCategoriaId(),
                    $producto->getProveedorId()
                );
            }
            return $producto;
        }, $productos);
    }
}
