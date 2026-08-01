<?php

class Usuario {
    private int $id;
    private string $nombre;
    private string $email;
    private string $rol;
    private ?string $username;

    public function __construct(int $id, string $nombre, string $email, string $rol = 'admin', ?string $username = null) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->rol = $rol;
        $this->username = $username;
    }

    public function getId(): int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getEmail(): string { return $this->email; }
    public function getRol(): string { return $this->rol; }
    public function getUsername(): ?string { return $this->username; }

    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setRol(string $rol): void { $this->rol = $rol; }
    public function setUsername(?string $username): void { $this->username = $username; }

    public static function fromArray(array $data): self {
        $nombre = (string)($data['nombre'] ?? $data['username'] ?? '');
        $email = (string)($data['email'] ?? $data['username'] ?? '');
        $rol = (string)($data['rol'] ?? 'admin');
        $username = isset($data['username']) ? (string)$data['username'] : null;

        return new self(
            (int)($data['id'] ?? 0),
            $nombre,
            $email,
            $rol,
            $username
        );
    }

    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'rol' => $this->rol,
        ];

        if ($this->username !== null) {
            $data['username'] = $this->username;
        }

        return $data;
    }
}