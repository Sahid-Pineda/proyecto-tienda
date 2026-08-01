from fastapi import APIRouter, HTTPException
from ..schemas import CategoriaCreate, CategoriaUpdate
from ..models import Categoria
from ..repositories import categoria_repo

router = APIRouter(prefix="/categorias", tags=["Categorías"])


@router.get("")
def listar_categorias():
    return [c.to_dict() for c in categoria_repo.get_all()]


@router.get("/{categoria_id}")
def obtener_categoria(categoria_id: int):
    c = categoria_repo.get_by_id(categoria_id)
    if not c:
        raise HTTPException(status_code=404, detail="Categoría no encontrada")
    return c.to_dict()


@router.post("", status_code=201)
def crear_categoria(payload: CategoriaCreate):
    nueva = Categoria(id=categoria_repo.next_id(), **payload.model_dump())
    categoria_repo.create(nueva)
    return nueva.to_dict()


@router.put("/{categoria_id}")
def actualizar_categoria(categoria_id: int, payload: CategoriaUpdate):
    actualizado = categoria_repo.update(categoria_id, payload.model_dump(exclude_none=True))
    if not actualizado:
        raise HTTPException(status_code=404, detail="Categoría no encontrada")
    return actualizado.to_dict()


@router.delete("/{categoria_id}", status_code=204)
def eliminar_categoria(categoria_id: int):
    if not categoria_repo.delete(categoria_id):
        raise HTTPException(status_code=404, detail="Categoría no encontrada")
