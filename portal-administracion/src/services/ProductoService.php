<?php
declare(strict_types=1);

require_once __DIR__ . '/../Producto.php';

class ProductoService {
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

        if (!isset($_SESSION['_mock_productos'])) {
            $_SESSION['_mock_productos'] = [
                ['id' => 1, 'nombre' => 'Balón de Fútbol Nike', 'precio' => 35.50, 'stock' => 12, 'categoria_id' => 1, 'proveedor_id' => 101],
                ['id' => 2, 'nombre' => 'Tacos de Fútbol Adidas', 'precio' => 89.99, 'stock' => 3, 'categoria_id' => 1, 'proveedor_id' => 102],
                ['id' => 3, 'nombre' => 'Raqueta de Tenis Wilson', 'precio' => 120.00, 'stock' => 0, 'categoria_id' => 2, 'proveedor_id' => 103],
                ['id' => 4, 'nombre' => 'Camiseta Basketball Jordan', 'precio' => 45.00, 'stock' => 25, 'categoria_id' => 3, 'proveedor_id' => 101],
            ];
        }
    }

    /**
     * Lista los productos del backend para poblar las pantallas del portal.
     */
    public function listarProductos(): array {
        if ($this->useMock) {
            return array_map(fn(array $item) => Producto::fromArray($item), $_SESSION['_mock_productos']);
        }

        $json = $this->sendRequest('GET', '/productos');
        return array_map(fn(array $item) => Producto::fromArray($item), $json);
    }

    /**
     * Obtiene un producto por ID para mostrar detalles o completar formularios.
     */
    public function obtenerProducto(int $id): ?Producto {
        if ($this->useMock) {
            foreach ($_SESSION['_mock_productos'] as $item) {
                if ((int)$item['id'] === $id) {
                    return Producto::fromArray($item);
                }
            }
            return null;
        }

        $json = $this->sendRequest('GET', "/productos/{$id}");
        return isset($json['id']) ? Producto::fromArray($json) : null;
    }

    /**
     * Crea un producto compatible con el schema del backend.
     */
    public function crearProducto(array $payload): Producto {
        if ($this->useMock) {
            $nextId = array_reduce($_SESSION['_mock_productos'], fn($carry, $item) => max($carry, (int)$item['id']), 0) + 1;
            $payload['id'] = $nextId;
            $_SESSION['_mock_productos'][] = $payload;
            return Producto::fromArray($payload);
        }

        $backendPayload = [
            'nombre' => $payload['nombre'] ?? '',
            'descripcion' => $payload['descripcion'] ?? '',
            'precio' => $payload['precio'] ?? 0.0,
            'stock' => $payload['stock'] ?? 0,
            'categoria_id' => $payload['categoria_id'] ?? 0,
            'proveedor_id' => $payload['proveedor_id'] ?? 0,
        ];
        $json = $this->sendRequest('POST', '/productos', $backendPayload);
        return Producto::fromArray($json);
    }

    /**
     * Actualiza los datos de un producto en el backend.
     */
    public function actualizarProducto(Producto $producto): ?Producto {
        if ($this->useMock) {
            foreach ($_SESSION['_mock_productos'] as $index => $item) {
                if ((int)$item['id'] === $producto->getId()) {
                    $_SESSION['_mock_productos'][$index] = $producto->toArray();
                    return $producto;
                }
            }
            return null;
        }

        $json = $this->sendRequest('PUT', '/productos/' . $producto->getId(), $producto->toArray());
        return isset($json['id']) ? Producto::fromArray($json) : null;
    }

    /**
     * Elimina un producto del backend.
     */
    public function eliminarProducto(int $id): bool {
        if ($this->useMock) {
            foreach ($_SESSION['_mock_productos'] as $index => $item) {
                if ((int)$item['id'] === $id) {
                    array_splice($_SESSION['_mock_productos'], $index, 1);
                    return true;
                }
            }
            return false;
        }

        $this->sendRequest('DELETE', '/productos/' . $id);
        return true;
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
