from fastapi import APIRouter, HTTPException
from ..schemas import ClienteCreate, ClienteUpdate
from ..models import Cliente
from ..repositories import cliente_repo

router = APIRouter(prefix="/clientes", tags=["Clientes"])


@router.get("")
def listar_clientes():
    return [c.to_dict() | {"password_hash": "***"} for c in cliente_repo.get_all()]


@router.get("/{cliente_id}")
def obtener_cliente(cliente_id: int):
    c = cliente_repo.get_by_id(cliente_id)
    if not c:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    d = c.to_dict()
    d["password_hash"] = "***"
    return d


@router.post("", status_code=201)
def registrar_cliente(payload: ClienteCreate):
    if cliente_repo.find(lambda x: x["username"] == payload.username):
        raise HTTPException(status_code=400, detail="El username ya existe")
    nuevo = Cliente(
        id=cliente_repo.next_id(),
        username=payload.username,
        password_hash=Cliente.hash_password(payload.password),
        nombre=payload.nombre,
        direccion=payload.direccion,
        email=payload.email,
    )
    cliente_repo.create(nuevo)
    d = nuevo.to_dict()
    d["password_hash"] = "***"
    return d


@router.put("/{cliente_id}")
def actualizar_cliente(cliente_id: int, payload: ClienteUpdate):
    cambios = payload.model_dump(exclude_none=True)
    if "password" in cambios:
        cambios["password_hash"] = Cliente.hash_password(cambios.pop("password"))
    actualizado = cliente_repo.update(cliente_id, cambios)
    if not actualizado:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    d = actualizado.to_dict()
    d["password_hash"] = "***"
    return d


@router.delete("/{cliente_id}", status_code=204)
def eliminar_cliente(cliente_id: int):
    if not cliente_repo.delete(cliente_id):
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
