"""
models.py
---------
Clases de dominio (Programación Orientada a Objetos).

- `Entidad` es una clase base abstracta-ish de la que heredan todas las
  demás (HERENCIA).
- Cada clase controla y valida sus propios atributos (ENCAPSULAMIENTO):
  por ejemplo, Producto no permite stock negativo y expone métodos
  `aumentar_stock` / `disminuir_stock` en lugar de dejar modificar el
  atributo libremente desde afuera.
- `Factura` se COMPONE de líneas (`LineaFactura`), y `Compra` se compone
  de líneas de compra (COMPOSICIÓN).
"""

from dataclasses import dataclass, field, asdict
from datetime import datetime
from typing import List, Optional
import hashlib


class StockInsuficienteError(Exception):
    pass


class Entidad:
    """Clase base de la que heredan todas las entidades del dominio."""

    def to_dict(self) -> dict:
        return asdict(self)

    @classmethod
    def from_dict(cls, data: dict):
        return cls(**data)


# ---------------------------------------------------------------------------
# Usuarios (administradores del portal PHP) y Clientes (tienda JS)
# ---------------------------------------------------------------------------

@dataclass
class Usuario(Entidad):
    id: int
    username: str
    password_hash: str
    nombre: str
    rol: str = "admin"

    @staticmethod
    def hash_password(password_plano: str) -> str:
        """Hash simple (sha256). Login sigue siendo 'simple': sin JWT ni sesiones."""
        return hashlib.sha256(password_plano.encode("utf-8")).hexdigest()

    def verificar_password(self, password_plano: str) -> bool:
        return self.password_hash == Usuario.hash_password(password_plano)


@dataclass
class Cliente(Entidad):
    id: int
    username: str
    password_hash: str
    nombre: str
    direccion: str = ""
    email: str = ""

    @staticmethod
    def hash_password(password_plano: str) -> str:
        return hashlib.sha256(password_plano.encode("utf-8")).hexdigest()

    def verificar_password(self, password_plano: str) -> bool:
        return self.password_hash == Cliente.hash_password(password_plano)


# ---------------------------------------------------------------------------
# Catálogo: Categoría, Proveedor, Producto
# ---------------------------------------------------------------------------

@dataclass
class Categoria(Entidad):
    id: int
    nombre: str
    descripcion: str = ""


@dataclass
class Proveedor(Entidad):
    id: int
    nombre: str
    contacto: str = ""
    telefono: str = ""
    email: str = ""


@dataclass
class Producto(Entidad):
    id: int
    nombre: str
    descripcion: str
    precio: float
    categoria_id: int
    proveedor_id: int
    stock: int = 0

    def aumentar_stock(self, cantidad: int) -> None:
        """Encapsula la regla de negocio: no se aumenta con cantidades negativas."""
        if cantidad <= 0:
            raise ValueError("La cantidad a aumentar debe ser positiva")
        self.stock += cantidad

    def disminuir_stock(self, cantidad: int) -> None:
        """Encapsula la regla: no se puede vender más de lo que hay en stock."""
        if cantidad <= 0:
            raise ValueError("La cantidad a disminuir debe ser positiva")
        if cantidad > self.stock:
            raise StockInsuficienteError(
                f"Stock insuficiente para '{self.nombre}': disponible {self.stock}, solicitado {cantidad}"
            )
        self.stock -= cantidad


# ---------------------------------------------------------------------------
# Compras (aumentan inventario) - composición con líneas
# ---------------------------------------------------------------------------

@dataclass
class LineaCompra:
    producto_id: int
    cantidad: int
    costo_unitario: float

    @property
    def subtotal(self) -> float:
        return round(self.cantidad * self.costo_unitario, 2)


@dataclass
class Compra(Entidad):
    id: int
    proveedor_id: int
    fecha: str
    detalle: List[dict]  # lista de dicts equivalentes a LineaCompra
    total: float

    @staticmethod
    def nueva(id: int, proveedor_id: int, detalle: List[LineaCompra]) -> "Compra":
        total = round(sum(l.subtotal for l in detalle), 2)
        return Compra(
            id=id,
            proveedor_id=proveedor_id,
            fecha=datetime.now().isoformat(timespec="seconds"),
            detalle=[l.__dict__ for l in detalle],
            total=total,
        )


# ---------------------------------------------------------------------------
# Carrito
# ---------------------------------------------------------------------------

@dataclass
class ItemCarrito(Entidad):
    id: int
    cliente_id: int
    producto_id: int
    cantidad: int


# ---------------------------------------------------------------------------
# Factura (venta a cliente) - disminuye inventario
# ---------------------------------------------------------------------------

@dataclass
class Factura(Entidad):
    id: int
    numero: str
    fecha: str
    cliente_id: int
    detalle: List[dict]
    subtotal: float
    impuesto: float
    total: float
    direccion: str
    entrega_domicilio: bool = False
