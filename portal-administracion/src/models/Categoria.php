<?php
declare(strict_types=1);

class Categoria {
    private int $id;
    private string $nombre;
    private string $descripcion;
    private bool $activo;

    public function __construct(int $id, string $nombre, string $descripcion = '', bool $activo = true) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->activo = $activo;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    public function getDescripcion(): string {
        return $this->descripcion;
    }

    public function isActivo(): bool {
        return $this->activo;
    }

    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }

    public function setDescripcion(string $descripcion): void {
        $this->descripcion = $descripcion;
    }

    public function setActivo(bool $activo): void {
        $this->activo = $activo;
    }

    public static function fromArray(array $data): self {
        return new self(
            (int)($data['id'] ?? 0),
            (string)($data['nombre'] ?? ''),
            (string)($data['descripcion'] ?? ''),
            isset($data['activo']) ? (bool)$data['activo'] : true
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
        ];
    }
}
