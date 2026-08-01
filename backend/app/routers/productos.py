from fastapi import APIRouter, HTTPException, Query
from typing import Optional
from ..schemas import ProductoCreate, ProductoUpdate
from ..models import Producto
from ..repositories import producto_repo
from ..funcional import filtrar_por_categoria, ordenar_por_precio

router = APIRouter(prefix="/productos", tags=["Productos"])


@router.get("")
def listar_productos(
    categoria_id: Optional[int] = Query(default=None),
    orden_precio: Optional[str] = Query(default=None, description="'asc' o 'desc'"),
):
    """Lista productos. Soporta filtro por categoría y orden por precio
    (ambos implementados con funciones puras: filter/sorted + lambda)."""
    productos = [p.to_dict() for p in producto_repo.get_all()]

    if categoria_id is not None:
        productos = filtrar_por_categoria(productos, categoria_id)

    if orden_precio in ("asc", "desc"):
        productos = ordenar_por_precio(productos, descendente=(orden_precio == "desc"))

    return productos


@router.get("/{producto_id}")
def obtener_producto(producto_id: int):
    p = producto_repo.get_by_id(producto_id)
    if not p:
        raise HTTPException(status_code=404, detail="Producto no encontrado")
    return p.to_dict()


@router.post("", status_code=201)
def crear_producto(payload: ProductoCreate):
    nuevo = Producto(id=producto_repo.next_id(), **payload.model_dump())
    producto_repo.create(nuevo)
    return nuevo.to_dict()


@router.put("/{producto_id}")
def actualizar_producto(producto_id: int, payload: ProductoUpdate):
    actualizado = producto_repo.update(producto_id, payload.model_dump(exclude_none=True))
    if not actualizado:
        raise HTTPException(status_code=404, detail="Producto no encontrado")
    return actualizado.to_dict()


@router.delete("/{producto_id}", status_code=204)
def eliminar_producto(producto_id: int):
    if not producto_repo.delete(producto_id):
        raise HTTPException(status_code=404, detail="Producto no encontrado")
