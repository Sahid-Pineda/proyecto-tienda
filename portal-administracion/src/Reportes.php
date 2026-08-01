<?php

class ReportesHelper {
    
    // Función pura: Filtra los productos con bajo inventario (Filter)
    public static function obtenerProductosBajoStock(array $productos, int $limite = 5): array {
        return array_filter($productos, fn(Producto $p) => $p->getStock() <= $limite);
    }

    // Función pura: Calcula el valor total monetario en inventario (Reduce)
    public static function calcularValorTotalInventario(array $productos): float {
        return array_reduce($productos, function($acumulado, Producto $p) {
            return $acumulado + ($p->getPrecio() * $p->getStock());
        }, 0.0);
    }

    // Función pura: Mapea a un formato de resumen rápido (Map)
    public static function obtenerNombresPrecios(array $productos): array {
        return array_map(fn(Producto $p) => [
            'nombre' => $p->getNombre(),
            'precio_formateado' => '$' . number_format($p->getPrecio(), 2)
        ], $productos);
    }
}