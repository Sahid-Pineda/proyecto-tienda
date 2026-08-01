"""
schemas.py
----------
Esquemas Pydantic: validan lo que entra/sale por la API REST (JSON).
Separados de los modelos de dominio (models.py) a propósito, para no
mezclar "reglas de negocio" con "forma del request HTTP".
"""

from pydantic import BaseModel, Field
from typing import List, Optional


# ---- Auth ----
class LoginRequest(BaseModel):
    username: str
    password: str


class LoginResponse(BaseModel):
    ok: bool
    tipo: str  # "admin" | "cliente"
    id: int
    nombre: str
    mensaje: str = "Login exitoso"


# ---- Usuarios (administradores) ----
class UsuarioCreate(BaseModel):
    username: str
    password: str
    nombre: str
    rol: str = "admin"


class UsuarioUpdate(BaseModel):
    nombre: Optional[str] = None
    password: Optional[str] = None
    rol: Optional[str] = None


# ---- Clientes ----
class ClienteCreate(BaseModel):
    username: str
    password: str
    nombre: str
    direccion: str = ""
    email: str = ""


class ClienteUpdate(BaseModel):
    nombre: Optional[str] = None
    password: Optional[str] = None
    direccion: Optional[str] = None
    email: Optional[str] = None


# ---- Categorías ----
class CategoriaCreate(BaseModel):
    nombre: str
    descripcion: str = ""


class CategoriaUpdate(BaseModel):
    nombre: Optional[str] = None
    descripcion: Optional[str] = None


# ---- Proveedores ----
class ProveedorCreate(BaseModel):
    nombre: str
    contacto: str = ""
    telefono: str = ""
    email: str = ""


class ProveedorUpdate(BaseModel):
    nombre: Optional[str] = None
    contacto: Optional[str] = None
    telefono: Optional[str] = None
    email: Optional[str] = None


# ---- Productos ----
class ProductoCreate(BaseModel):
    nombre: str
    descripcion: str = ""
    precio: float = Field(gt=0)
    categoria_id: int
    proveedor_id: int
    stock: int = 0


class ProductoUpdate(BaseModel):
    nombre: Optional[str] = None
    descripcion: Optional[str] = None
    precio: Optional[float] = None
    categoria_id: Optional[int] = None
    proveedor_id: Optional[int] = None


# ---- Compras (aumentan inventario) ----
class LineaCompraIn(BaseModel):
    producto_id: int
    cantidad: int = Field(gt=0)
    costo_unitario: float = Field(gt=0)


class CompraCreate(BaseModel):
    proveedor_id: int
    detalle: List[LineaCompraIn]


# ---- Carrito ----
class CarritoAddRequest(BaseModel):
    cliente_id: int
    producto_id: int
    cantidad: int = Field(gt=0)


class CarritoUpdateRequest(BaseModel):
    cantidad: int = Field(gt=0)


# ---- Facturación (venta simulada) ----
class FacturaCreate(BaseModel):
    cliente_id: int
    direccion: Optional[str] = None
    entrega_domicilio: bool = False
