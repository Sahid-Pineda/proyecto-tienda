from datetime import datetime
from fastapi import APIRouter, HTTPException
from ..schemas import FacturaCreate
from ..models import Factura, StockInsuficienteError
from ..repositories import factura_repo, carrito_repo, producto_repo, cliente_repo
from ..funcional import construir_detalle_factura, calcular_subtotal, calcular_impuesto, calcular_total

router = APIRouter(prefix="/facturas", tags=["Facturas"])


@router.get("")
def listar_facturas():
    return [f.to_dict() for f in factura_repo.get_all()]


@router.get("/{factura_id}")
def obtener_factura(factura_id: int):
    f = factura_repo.get_by_id(factura_id)
    if not f:
        raise HTTPException(status_code=404, detail="Factura no encontrada")
    return f.to_dict()


@router.post("", status_code=201)
def generar_factura(payload: FacturaCreate):
    """
    Toma el carrito actual del cliente, genera la factura (usando las
    funciones puras de funcional.py para subtotal/impuesto/total) y
    DISMINUYE el inventario de cada producto vendido.
    """
    cliente = cliente_repo.get_by_id(payload.cliente_id)
    if not cliente:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")

    items_carrito = carrito_repo.find(lambda i: i["cliente_id"] == payload.cliente_id)
    if not items_carrito:
        raise HTTPException(status_code=400, detail="El carrito está vacío")

    # Resolver productos y validar stock ANTES de tocar nada (evita estados inconsistentes)
    productos_resueltos = []
    for item in items_carrito:
        producto = producto_repo.get_by_id(item.producto_id)
        if not producto:
            raise HTTPException(status_code=404, detail=f"Producto {item.producto_id} no encontrado")
        if item.cantidad > producto.stock:
            raise HTTPException(
                status_code=400,
                detail=f"Stock insuficiente para '{producto.nombre}' (disponible {producto.stock})",
            )
        productos_resueltos.append({"producto": producto.to_dict(), "cantidad": item.cantidad})

    detalle = construir_detalle_factura(productos_resueltos)
    subtotal = calcular_subtotal(detalle)
    impuesto = calcular_impuesto(subtotal)
    total = calcular_total(subtotal, impuesto)

    factura = Factura(
        id=factura_repo.next_id(),
        numero=f"FAC-{datetime.now().strftime('%Y%m%d')}-{factura_repo.next_id():04d}",
        fecha=datetime.now().isoformat(timespec="seconds"),
        cliente_id=payload.cliente_id,
        detalle=detalle,
        subtotal=subtotal,
        impuesto=impuesto,
        total=total,
        direccion=payload.direccion or cliente.direccion,
        entrega_domicilio=payload.entrega_domicilio,
    )
    factura_repo.create(factura)

    # Disminuir inventario (encapsulado en Producto.disminuir_stock)
    for item in items_carrito:
        producto = producto_repo.get_by_id(item.producto_id)
        try:
            producto.disminuir_stock(item.cantidad)
        except StockInsuficienteError as e:
            raise HTTPException(status_code=400, detail=str(e))
        producto_repo.update(producto.id, {"stock": producto.stock})

    # Vaciar el carrito tras la compra
    for item in items_carrito:
        carrito_repo.delete(item.id)

    return factura.to_dict()
