from fastapi import APIRouter, HTTPException
from ..schemas import CarritoAddRequest, CarritoUpdateRequest
from ..models import ItemCarrito
from ..repositories import carrito_repo, producto_repo
from ..funcional import total_carrito

router = APIRouter(prefix="/carrito", tags=["Carrito"])


def _resolver_items(cliente_id: int):
    items = carrito_repo.find(lambda i: i["cliente_id"] == cliente_id)
    resueltos = []
    for item in items:
        producto = producto_repo.get_by_id(item.producto_id)
        if producto:
            resueltos.append({"item": item, "producto": producto.to_dict(), "cantidad": item.cantidad})
    return resueltos


@router.get("/{cliente_id}")
def ver_carrito(cliente_id: int):
    resueltos = _resolver_items(cliente_id)
    detalle = [
        {
            "carrito_item_id": r["item"].id,
            "producto_id": r["producto"]["id"],
            "nombre": r["producto"]["nombre"],
            "precio": r["producto"]["precio"],
            "cantidad": r["cantidad"],
            "subtotal": round(r["producto"]["precio"] * r["cantidad"], 2),
        }
        for r in resueltos
    ]
    return {"cliente_id": cliente_id, "items": detalle, "total": total_carrito(resueltos)}


@router.post("", status_code=201)
def agregar_al_carrito(payload: CarritoAddRequest):
    producto = producto_repo.get_by_id(payload.producto_id)
    if not producto:
        raise HTTPException(status_code=404, detail="Producto no encontrado")

    existentes = carrito_repo.find(
        lambda i: i["cliente_id"] == payload.cliente_id and i["producto_id"] == payload.producto_id
    )
    if existentes:
        item = existentes[0]
        actualizado = carrito_repo.update(item.id, {"cantidad": item.cantidad + payload.cantidad})
        return actualizado.to_dict()

    nuevo = ItemCarrito(
        id=carrito_repo.next_id(),
        cliente_id=payload.cliente_id,
        producto_id=payload.producto_id,
        cantidad=payload.cantidad,
    )
    carrito_repo.create(nuevo)
    return nuevo.to_dict()


@router.put("/item/{item_id}")
def actualizar_item_carrito(item_id: int, payload: CarritoUpdateRequest):
    actualizado = carrito_repo.update(item_id, {"cantidad": payload.cantidad})
    if not actualizado:
        raise HTTPException(status_code=404, detail="Item de carrito no encontrado")
    return actualizado.to_dict()


@router.delete("/item/{item_id}", status_code=204)
def eliminar_item_carrito(item_id: int):
    if not carrito_repo.delete(item_id):
        raise HTTPException(status_code=404, detail="Item de carrito no encontrado")


@router.delete("/{cliente_id}/vaciar", status_code=204)
def vaciar_carrito(cliente_id: int):
    items = carrito_repo.find(lambda i: i["cliente_id"] == cliente_id)
    for item in items:
        carrito_repo.delete(item.id)
