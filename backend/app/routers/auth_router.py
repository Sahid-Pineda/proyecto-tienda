from fastapi import APIRouter, HTTPException
from ..schemas import LoginRequest, LoginResponse
from ..repositories import usuario_repo, cliente_repo

router = APIRouter(tags=["Login"])


@router.post("/login", response_model=LoginResponse)
def login(payload: LoginRequest):
    """
    Login simple (sin tokens ni sesiones complejas), tal como pide el
    proyecto. Revisa primero administradores y luego clientes.
    """
    admins = usuario_repo.find(lambda u: u["username"] == payload.username)
    if admins:
        admin = admins[0]
        if admin.verificar_password(payload.password):
            return LoginResponse(ok=True, tipo="admin", id=admin.id, nombre=admin.nombre)
        raise HTTPException(status_code=401, detail="Contraseña incorrecta")

    clientes = cliente_repo.find(lambda c: c["username"] == payload.username)
    if clientes:
        cliente = clientes[0]
        if cliente.verificar_password(payload.password):
            return LoginResponse(ok=True, tipo="cliente", id=cliente.id, nombre=cliente.nombre)
        raise HTTPException(status_code=401, detail="Contraseña incorrecta")

    raise HTTPException(status_code=404, detail="Usuario no encontrado")
