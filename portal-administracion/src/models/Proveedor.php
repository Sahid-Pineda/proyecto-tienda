<?php
declare(strict_types=1);

class Proveedor {
    private int $id;
    private string $nombre;
    private string $contacto;
    private string $telefono;
    private string $email;
    private bool $activo;

    public function __construct(int $id, string $nombre, string $contacto, string $telefono, string $email, bool $activo = true) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->contacto = $contacto;
        $this->telefono = $telefono;
        $this->email = $email;
        $this->activo = $activo;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    public function getContacto(): string {
        return $this->contacto;
    }

    public function getTelefono(): string {
        return $this->telefono;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function isActivo(): bool {
        return $this->activo;
    }

    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }

    public function setContacto(string $contacto): void {
        $this->contacto = $contacto;
    }

    public function setTelefono(string $telefono): void {
        $this->telefono = $telefono;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setActivo(bool $activo): void {
        $this->activo = $activo;
    }

    public static function fromArray(array $data): self {
        return new self(
            (int)($data['id'] ?? 0),
            (string)($data['nombre'] ?? ''),
            (string)($data['contacto'] ?? ''),
            (string)($data['telefono'] ?? ''),
            (string)($data['email'] ?? ''),
            isset($data['activo']) ? (bool)$data['activo'] : true
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'contacto' => $this->contacto,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'activo' => $this->activo,
        ];
    }
}
