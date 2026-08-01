<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Categoria.php';

class CategoriaService {
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

        if (!isset($_SESSION['_mock_categorias'])) {
            $_SESSION['_mock_categorias'] = [
                ['id' => 1, 'nombre' => 'Fútbol', 'descripcion' => 'Balones, tacos y equipamiento', 'activo' => true],
                ['id' => 2, 'nombre' => 'Tenis', 'descripcion' => 'Raquetas, cuerdas y pelotas', 'activo' => true],
                ['id' => 3, 'nombre' => 'Fitness', 'descripcion' => 'Pesas, bandas y ropa deportiva', 'activo' => true],
            ];
        }
    }

    public function listarCategorias(): array {
        if ($this->useMock) {
            return array_map(fn(array $item) => Categoria::fromArray($item), $_SESSION['_mock_categorias']);
        }

        $json = $this->sendRequest('GET', '/categorias');
        return array_map(fn(array $item) => Categoria::fromArray($item), $json);
    }

    public function obtenerCategoria(int $id): ?Categoria {
        if ($this->useMock) {
            foreach ($_SESSION['_mock_categorias'] as $item) {
                if ((int)$item['id'] === $id) {
                    return Categoria::fromArray($item);
                }
            }
            return null;
        }

        $json = $this->sendRequest('GET', "/categorias/{$id}");
        return isset($json['id']) ? Categoria::fromArray($json) : null;
    }

    public function crearCategoria(array $payload): Categoria {
        if ($this->useMock) {
            $nextId = array_reduce($_SESSION['_mock_categorias'], fn($carry, $item) => max($carry, (int)$item['id']), 0) + 1;
            $payload['id'] = $nextId;
            $payload['activo'] = isset($payload['activo']) ? (bool)$payload['activo'] : true;
            $_SESSION['_mock_categorias'][] = $payload;
            return Categoria::fromArray($payload);
        }

        $json = $this->sendRequest('POST', '/categorias', $payload);
        return Categoria::fromArray($json);
    }

    public function actualizarCategoria(Categoria $categoria): ?Categoria {
        if ($this->useMock) {
            foreach ($_SESSION['_mock_categorias'] as $index => $item) {
                if ((int)$item['id'] === $categoria->getId()) {
                    $_SESSION['_mock_categorias'][$index] = $categoria->toArray();
                    return $categoria;
                }
            }
            return null;
        }

        $json = $this->sendRequest('PUT', '/categorias/' . $categoria->getId(), $categoria->toArray());
        return isset($json['id']) ? Categoria::fromArray($json) : null;
    }

    public function eliminarCategoria(int $id): bool {
        if ($this->useMock) {
            foreach ($_SESSION['_mock_categorias'] as $index => $item) {
                if ((int)$item['id'] === $id) {
                    array_splice($_SESSION['_mock_categorias'], $index, 1);
                    return true;
                }
            }
            return false;
        }

        $this->sendRequest('DELETE', '/categorias/' . $id);
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
