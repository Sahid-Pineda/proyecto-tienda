<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Compra.php';
require_once __DIR__ . '/../Producto.php';

class CompraService {
    private string $baseUrl;
    private bool $useMock;

    public function __construct(string $baseUrl = 'http://127.0.0.1:8000', bool $useMock = false) {
        $this->baseUrl = $baseUrl;
        $this->useMock = $useMock;
        if ($this->useMock) {
            $this->initializeMockStore();
        }
    }

    private function initializeMockStore(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['_mock_compras'])) {
            $_SESSION['_mock_compras'] = [];
        }

        if (!isset($_SESSION['_mock_productos'])) {
            $_SESSION['_mock_productos'] = [
                ['id' => 1, 'nombre' => 'Balón de Fútbol Nike', 'precio' => 35.50, 'stock' => 12, 'categoria_id' => 1, 'proveedor_id' => 101],
                ['id' => 2, 'nombre' => 'Tacos de Fútbol Adidas', 'precio' => 89.99, 'stock' => 3, 'categoria_id' => 1, 'proveedor_id' => 102],
                ['id' => 3, 'nombre' => 'Raqueta de Tenis Wilson', 'precio' => 120.00, 'stock' => 0, 'categoria_id' => 2, 'proveedor_id' => 103],
                ['id' => 4, 'nombre' => 'Camiseta Basketball Jordan', 'precio' => 45.00, 'stock' => 25, 'categoria_id' => 3, 'proveedor_id' => 101],
            ];
        }
    }

    public function listarCompras(): array {
        if ($this->useMock) {
            return array_map(fn(array $item) => Compra::fromArray($item), $_SESSION['_mock_compras']);
        }

        $json = $this->sendRequest('GET', '/compras');
        return array_map(fn(array $item) => Compra::fromArray($item), $json);
    }

    public function registrarCompra(int $productoId, int $proveedorId, int $cantidad, string $fecha): Compra {
        if ($this->useMock) {
            $producto = $this->obtenerProductoMock($productoId);
            if ($producto === null) {
                throw new RuntimeException('Producto no encontrado para registrar compra');
            }

            $total = $producto->getPrecio() * $cantidad;
            $nextId = array_reduce($_SESSION['_mock_compras'], fn($carry, $item) => max($carry, (int)$item['id']), 0) + 1;
            $compraData = [
                'id' => $nextId,
                'producto_id' => $productoId,
                'proveedor_id' => $proveedorId,
                'cantidad' => $cantidad,
                'fecha' => $fecha,
                'total' => $total,
            ];

            $_SESSION['_mock_compras'][] = $compraData;
            $this->actualizarStockMock($productoId, $cantidad);

            return Compra::fromArray($compraData);
        }

        $productoPrecio = $this->obtenerPrecioProducto($productoId);
        $json = $this->sendRequest('POST', '/compras', [
            'proveedor_id' => $proveedorId,
            'detalle' => [[
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'costo_unitario' => $productoPrecio,
            ]],
        ]);

        return Compra::fromArray($json);
    }

    private function obtenerProductoMock(int $id): ?Producto {
        foreach ($_SESSION['_mock_productos'] as $item) {
            if ((int)$item['id'] === $id) {
                return Producto::fromArray($item);
            }
        }
        return null;
    }

    private function actualizarStockMock(int $productoId, int $cantidad): void {
        $_SESSION['_mock_productos'] = array_map(function (array $item) use ($productoId, $cantidad) {
            if ((int)$item['id'] === $productoId) {
                $item['stock'] = (int)$item['stock'] + $cantidad;
            }
            return $item;
        }, $_SESSION['_mock_productos']);
    }

    private function obtenerPrecioProducto(int $productoId): float {
        if ($this->useMock) {
            $producto = $this->obtenerProductoMock($productoId);
            return $producto?->getPrecio() ?? 0.0;
        }

        $json = $this->sendRequest('GET', '/productos/' . $productoId);
        return isset($json['precio']) ? (float)$json['precio'] : 0.0;
    }

    private function sendRequest(string $method, string $path, ?array $payload = null): array {
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($payload !== null) {
            $body = json_encode($payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Error en la comunicación con la API: ' . $curlError);
        }

        return json_decode($response, true) ?? [];
    }
}
