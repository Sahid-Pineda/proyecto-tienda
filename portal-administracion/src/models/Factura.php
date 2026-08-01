<?php
declare(strict_types=1);

class Factura {
    private int $id;
    private int $proveedorId;
    private string $fecha;
    private float $total;
    private array $items;

    public function __construct(int $id, int $proveedorId, string $fecha, float $total, array $items = []) {
        $this->id = $id;
        $this->proveedorId = $proveedorId;
        $this->fecha = $fecha;
        $this->total = $total;
        $this->items = $items;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getProveedorId(): int {
        return $this->proveedorId;
    }

    public function getFecha(): string {
        return $this->fecha;
    }

    public function getTotal(): float {
        return $this->total;
    }

    public function getItems(): array {
        return $this->items;
    }

    public static function fromArray(array $data): self {
        $items = $data['items'] ?? [];
        if (empty($items) && isset($data['detalle']) && is_array($data['detalle'])) {
            $items = array_map(function (array $item): array {
                $producto = $item['producto'] ?? [];
                $precioUnitario = (float)($item['precio_unitario'] ?? $producto['precio'] ?? 0.0);
                return [
                    'producto' => $producto['nombre'] ?? '',
                    'cantidad' => (int)($item['cantidad'] ?? 0),
                    'precio_unitario' => $precioUnitario,
                ];
            }, $data['detalle']);
        }

        return new self(
            (int)($data['id'] ?? 0),
            (int)($data['proveedor_id'] ?? $data['cliente_id'] ?? 0),
            (string)($data['fecha'] ?? ''),
            (float)($data['total'] ?? 0.0),
            $items
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'proveedor_id' => $this->proveedorId,
            'fecha' => $this->fecha,
            'total' => $this->total,
            'items' => $this->items,
        ];
    }
}
