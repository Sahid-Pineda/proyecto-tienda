<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Proveedor.php';

class ProveedorService {
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

        if (!isset($_SESSION['_mock_proveedores'])) {
            $_SESSION['_mock_proveedores'] = [
                ['id' => 101, 'nombre' => 'Proveedores Elite', 'contacto' => 'Ana López', 'telefono' => '+541112223334', 'email' => 'ana@proveedoreselite.com', 'activo' => true],
                ['id' => 102, 'nombre' => 'Suministros Deportivos SRL', 'contacto' => 'Carlos Méndez', 'telefono' => '+549115556667', 'email' => 'carlos@suministros.com', 'activo' => true],
                ['id' => 103, 'nombre' => 'Distribuciones Sport', 'contacto' => 'María Pérez', 'telefono' => '+549117778899', 'email' => 'maria@distribucionesport.com', 'activo' => true],
            ];
        }
    }

    public function listarProveedores(): array {
        if ($this->useMock) {
            return array_map(fn(array $item) => Proveedor::fromArray($item), $_SESSION['_mock_proveedores']);
        }

        $json = $this->sendRequest('GET', '/proveedores');
        return array_map(fn(array $item) => Proveedor::fromArray($item), $json);
    }

    public function obtenerProveedor(int $id): ?Proveedor {
        if ($this->useMock) {
            foreach ($_SESSION['_mock_proveedores'] as $item) {
                if ((int)$item['id'] === $id) {
                    return Proveedor::fromArray($item);
                }
            }
            return null;
        }

        $json = $this->sendRequest('GET', "/proveedores/{$id}");
        return isset($json['id']) ? Proveedor::fromArray($json) : null;
    }

    public function crearProveedor(array $payload): Proveedor {
        if ($this->useMock) {
            $nextId = array_reduce($_SESSION['_mock_proveedores'], fn($carry, $item) => max($carry, (int)$item['id']), 100) + 1;
            $payload['id'] = $nextId;
            $payload['activo'] = isset($payload['activo']) ? (bool)$payload['activo'] : true;
            $_SESSION['_mock_proveedores'][] = $payload;
            return Proveedor::fromArray($payload);
        }

        $json = $this->sendRequest('POST', '/proveedores', $payload);
        return Proveedor::fromArray($json);
    }

    public function actualizarProveedor(Proveedor $proveedor): ?Proveedor {
        if ($this->useMock) {
            foreach ($_SESSION['_mock_proveedores'] as $index => $item) {
                if ((int)$item['id'] === $proveedor->getId()) {
                    $_SESSION['_mock_proveedores'][$index] = $proveedor->toArray();
                    return $proveedor;
                }
            }
            return null;
        }

        $json = $this->sendRequest('PUT', '/proveedores/' . $proveedor->getId(), $proveedor->toArray());
        return isset($json['id']) ? Proveedor::fromArray($json) : null;
    }

    public function eliminarProveedor(int $id): bool {
        if ($this->useMock) {
            foreach ($_SESSION['_mock_proveedores'] as $index => $item) {
                if ((int)$item['id'] === $id) {
                    array_splice($_SESSION['_mock_proveedores'], $index, 1);
                    return true;
                }
            }
            return false;
        }

        $this->sendRequest('DELETE', '/proveedores/' . $id);
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
