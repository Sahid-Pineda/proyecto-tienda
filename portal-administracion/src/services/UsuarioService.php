<?php
declare(strict_types=1);

require_once __DIR__ . '/../Usuario.php';

class UsuarioService {
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

        if (!isset($_SESSION['_mock_usuarios'])) {
            $_SESSION['_mock_usuarios'] = [
                ['id' => 1, 'nombre' => 'Admin Principal', 'email' => 'admin@example.com', 'rol' => 'admin'],
                ['id' => 2, 'nombre' => 'Empleado 1', 'email' => 'empleado1@example.com', 'rol' => 'vendedor'],
            ];
        }
    }

    /**
     * Obtiene los usuarios registrados desde el backend o desde el almacenamiento mock.
     */
    public function listarUsuarios(): array {
        if ($this->useMock) {
            return array_map(fn(array $item) => Usuario::fromArray($item), $_SESSION['_mock_usuarios']);
        }

        $json = $this->sendRequest('GET', '/usuarios');
        return array_map(fn(array $item) => Usuario::fromArray($item), $json);
    }

    /**
     * Busca un usuario por ID usando la API del backend.
     */
    public function obtenerUsuario(int $id): ?Usuario {
        if ($this->useMock) {
            return array_reduce($_SESSION['_mock_usuarios'], fn($carry, $item) => (int)$item['id'] === $id ? Usuario::fromArray($item) : $carry, null);
        }

        $json = $this->sendRequest('GET', "/usuarios/{$id}");
        return isset($json['id']) ? Usuario::fromArray($json) : null;
    }

    /**
     * Crea un usuario compatible con el contrato del backend.
     * El backend espera username, password, nombre y rol.
     */
    public function crearUsuario(array $payload): Usuario {
        if ($this->useMock) {
            $nextId = array_reduce($_SESSION['_mock_usuarios'], fn($carry, $item) => max($carry, (int)$item['id']), 0) + 1;
            $payload['id'] = $nextId;
            $_SESSION['_mock_usuarios'][] = $payload;
            return Usuario::fromArray($payload);
        }

        $backendPayload = [
            'username' => $payload['username'] ?? $payload['email'] ?? 'usuario' . uniqid(),
            'password' => $payload['password'] ?? '123456',
            'nombre' => $payload['nombre'] ?? '',
            'rol' => $payload['rol'] ?? 'admin',
        ];
        $json = $this->sendRequest('POST', '/usuarios', $backendPayload);
        return Usuario::fromArray($json);
    }

    /**
     * Actualiza los campos básicos del usuario en el backend.
     */
    public function actualizarUsuario(Usuario $usuario): ?Usuario {
        if ($this->useMock) {
            foreach ($_SESSION['_mock_usuarios'] as $index => $item) {
                if ((int)$item['id'] === $usuario->getId()) {
                    $_SESSION['_mock_usuarios'][$index] = $usuario->toArray();
                    return $usuario;
                }
            }
            return null;
        }

        $backendPayload = [
            'nombre' => $usuario->getNombre(),
            'rol' => $usuario->getRol(),
        ];

        $json = $this->sendRequest('PUT', '/usuarios/' . $usuario->getId(), $backendPayload);
        return isset($json['id']) ? Usuario::fromArray($json) : null;
    }

    /**
     * Elimina un usuario por ID.
     */
    public function eliminarUsuario(int $id): bool {
        if ($this->useMock) {
            foreach ($_SESSION['_mock_usuarios'] as $index => $item) {
                if ((int)$item['id'] === $id) {
                    array_splice($_SESSION['_mock_usuarios'], $index, 1);
                    return true;
                }
            }
            return false;
        }

        $this->sendRequest('DELETE', '/usuarios/' . $id);
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
