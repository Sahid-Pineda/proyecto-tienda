from fastapi import APIRouter, HTTPException
from ..schemas import CompraCreate
from ..models import Compra, LineaCompra
from ..repositories import compra_repo, producto_repo, proveedor_repo

router = APIRouter(prefix="/compras", tags=["Compras"])


@router.get("")
def listar_compras():
    return [c.to_dict() for c in compra_repo.get_all()]


@router.get("/{compra_id}")
def obtener_compra(compra_id: int):
    c = compra_repo.get_by_id(compra_id)
    if not c:
        raise HTTPException(status_code=404, detail="Compra no encontrada")
    return c.to_dict()


@router.post("", status_code=201)
def registrar_compra(payload: CompraCreate):
    """
    Registra una compra a un proveedor y AUMENTA el inventario de cada
    producto involucrado (regla encapsulada en Producto.aumentar_stock).
    """
    proveedor = proveedor_repo.get_by_id(payload.proveedor_id)
    if not proveedor:
        raise HTTPException(status_code=404, detail="Proveedor no encontrado")

    lineas = []
    for linea in payload.detalle:
        producto = producto_repo.get_by_id(linea.producto_id)
        if not producto:
            raise HTTPException(status_code=404, detail=f"Producto {linea.producto_id} no encontrado")
        lineas.append(LineaCompra(
            producto_id=linea.producto_id,
            cantidad=linea.cantidad,
            costo_unitario=linea.costo_unitario,
        ))

    compra = Compra.nueva(id=compra_repo.next_id(), proveedor_id=payload.proveedor_id, detalle=lineas)
    compra_repo.create(compra)

    # Aumentar inventario producto por producto (encapsulamiento en el modelo)
    for linea in lineas:
        producto = producto_repo.get_by_id(linea.producto_id)
        producto.aumentar_stock(linea.cantidad)
        producto_repo.update(producto.id, {"stock": producto.stock})

    return compra.to_dict()
