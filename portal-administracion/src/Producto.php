<?php

class Producto {
    private int $id;
    private string $nombre;
    private float $precio;
    private int $stock;
    private int $categoriaId;
    private ?int $proveedorId;
    private string $descripcion;

    public function __construct(int $id, string $nombre, float $precio, int $stock, int $categoriaId, ?int $proveedorId = null, string $descripcion = '') {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->categoriaId = $categoriaId;
        $this->proveedorId = $proveedorId;
        $this->descripcion = $descripcion;
    }

    public function getId(): int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getPrecio(): float { return $this->precio; }
    public function getStock(): int { return $this->stock; }
    public function getCategoriaId(): int { return $this->categoriaId; }
    public function getProveedorId(): ?int { return $this->proveedorId; }
    public function getDescripcion(): string { return $this->descripcion; }

    public function setStock(int $stock): void { $this->stock = $stock; }
    public function setDescripcion(string $descripcion): void { $this->descripcion = $descripcion; }

    public static function fromArray(array $data): self {
        return new self(
            (int)($data['id'] ?? 0),
            (string)($data['nombre'] ?? ''),
            (float)($data['precio'] ?? 0.0),
            (int)($data['stock'] ?? 0),
            (int)($data['categoria_id'] ?? 0),
            isset($data['proveedor_id']) ? (int)$data['proveedor_id'] : null,
            (string)($data['descripcion'] ?? '')
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'precio' => $this->precio,
            'stock' => $this->stock,
            'categoria_id' => $this->categoriaId,
            'proveedor_id' => $this->proveedorId,
            'descripcion' => $this->descripcion,
        ];
    }
}