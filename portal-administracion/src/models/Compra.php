<?php
declare(strict_types=1);

class Compra {
    private int $id;
    private int $productoId;
    private int $proveedorId;
    private int $cantidad;
    private string $fecha;
    private float $total;

    public function __construct(int $id, int $productoId, int $proveedorId, int $cantidad, string $fecha, float $total) {
        $this->id = $id;
        $this->productoId = $productoId;
        $this->proveedorId = $proveedorId;
        $this->cantidad = $cantidad;
        $this->fecha = $fecha;
        $this->total = $total;
    }

    public function getId(): int { return $this->id; }
    public function getProductoId(): int { return $this->productoId; }
    public function getProveedorId(): int { return $this->proveedorId; }
    public function getCantidad(): int { return $this->cantidad; }
    public function getFecha(): string { return $this->fecha; }
    public function getTotal(): float { return $this->total; }

    public function setCantidad(int $cantidad): void { $this->cantidad = $cantidad; }
    public function setFecha(string $fecha): void { $this->fecha = $fecha; }
    public function setTotal(float $total): void { $this->total = $total; }

    public static function fromArray(array $data): self {
        $productoId = (int)($data['producto_id'] ?? 0);
        $cantidad = (int)($data['cantidad'] ?? 0);

        if ($productoId === 0 && isset($data['detalle']) && is_array($data['detalle'])) {
            $primeraLinea = $data['detalle'][0] ?? [];
            $productoId = (int)($primeraLinea['producto_id'] ?? 0);
            $cantidad = (int)($primeraLinea['cantidad'] ?? 0);
        }

        return new self(
            (int)($data['id'] ?? 0),
            $productoId,
            (int)($data['proveedor_id'] ?? 0),
            $cantidad,
            (string)($data['fecha'] ?? date('Y-m-d')),
            (float)($data['total'] ?? 0.0)
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'producto_id' => $this->productoId,
            'proveedor_id' => $this->proveedorId,
            'cantidad' => $this->cantidad,
            'fecha' => $this->fecha,
            'total' => $this->total,
        ];
    }
}
