from fastapi import APIRouter, HTTPException
from ..schemas import UsuarioCreate, UsuarioUpdate
from ..models import Usuario
from ..repositories import usuario_repo

router = APIRouter(prefix="/usuarios", tags=["Usuarios"])


@router.get("")
def listar_usuarios():
    return [u.to_dict() | {"password_hash": "***"} for u in usuario_repo.get_all()]


@router.get("/{usuario_id}")
def obtener_usuario(usuario_id: int):
    u = usuario_repo.get_by_id(usuario_id)
    if not u:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    d = u.to_dict()
    d["password_hash"] = "***"
    return d


@router.post("", status_code=201)
def crear_usuario(payload: UsuarioCreate):
    if usuario_repo.find(lambda x: x["username"] == payload.username):
        raise HTTPException(status_code=400, detail="El username ya existe")
    nuevo = Usuario(
        id=usuario_repo.next_id(),
        username=payload.username,
        password_hash=Usuario.hash_password(payload.password),
        nombre=payload.nombre,
        rol=payload.rol,
    )
    usuario_repo.create(nuevo)
    d = nuevo.to_dict()
    d["password_hash"] = "***"
    return d


@router.put("/{usuario_id}")
def actualizar_usuario(usuario_id: int, payload: UsuarioUpdate):
    cambios = payload.model_dump(exclude_none=True)
    if "password" in cambios:
        cambios["password_hash"] = Usuario.hash_password(cambios.pop("password"))
    actualizado = usuario_repo.update(usuario_id, cambios)
    if not actualizado:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    d = actualizado.to_dict()
    d["password_hash"] = "***"
    return d


@router.delete("/{usuario_id}", status_code=204)
def eliminar_usuario(usuario_id: int):
    if not usuario_repo.delete(usuario_id):
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
