from fastapi import APIRouter, HTTPException
from ..schemas import ProveedorCreate, ProveedorUpdate
from ..models import Proveedor
from ..repositories import proveedor_repo

router = APIRouter(prefix="/proveedores", tags=["Proveedores"])


@router.get("")
def listar_proveedores():
    return [p.to_dict() for p in proveedor_repo.get_all()]


@router.get("/{proveedor_id}")
def obtener_proveedor(proveedor_id: int):
    p = proveedor_repo.get_by_id(proveedor_id)
    if not p:
        raise HTTPException(status_code=404, detail="Proveedor no encontrado")
    return p.to_dict()


@router.post("", status_code=201)
def crear_proveedor(payload: ProveedorCreate):
    nuevo = Proveedor(id=proveedor_repo.next_id(), **payload.model_dump())
    proveedor_repo.create(nuevo)
    return nuevo.to_dict()


@router.put("/{proveedor_id}")
def actualizar_proveedor(proveedor_id: int, payload: ProveedorUpdate):
    actualizado = proveedor_repo.update(proveedor_id, payload.model_dump(exclude_none=True))
    if not actualizado:
        raise HTTPException(status_code=404, detail="Proveedor no encontrado")
    return actualizado.to_dict()


@router.delete("/{proveedor_id}", status_code=204)
def eliminar_proveedor(proveedor_id: int):
    if not proveedor_repo.delete(proveedor_id):
        raise HTTPException(status_code=404, detail="Proveedor no encontrado")
