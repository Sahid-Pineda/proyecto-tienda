<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Factura.php';

class VentaService {
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

        if (!isset($_SESSION['_mock_facturas'])) {
            $_SESSION['_mock_facturas'] = [
                [
                    'id' => 1,
                    'proveedor_id' => 101,
                    'fecha' => '2026-07-25',
                    'total' => 560.50,
                    'items' => [
                        ['producto' => 'Balón de Fútbol Nike', 'cantidad' => 10, 'precio_unitario' => 35.50],
                        ['producto' => 'Tacos de Fútbol Adidas', 'cantidad' => 2, 'precio_unitario' => 89.99],
                    ],
                ],
                [
                    'id' => 2,
                    'proveedor_id' => 103,
                    'fecha' => '2026-07-29',
                    'total' => 240.00,
                    'items' => [
                        ['producto' => 'Raqueta de Tenis Wilson', 'cantidad' => 2, 'precio_unitario' => 120.00],
                    ],
                ],
            ];
        }
    }

    public function listarFacturas(): array {
        if ($this->useMock) {
            return array_map(fn(array $item) => Factura::fromArray($item), $_SESSION['_mock_facturas']);
        }

        $json = $this->sendRequest('GET', '/facturas');
        return array_map(fn(array $item) => Factura::fromArray($item), $json);
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
