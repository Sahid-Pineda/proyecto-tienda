from fastapi import APIRouter, Query
from typing import Optional
from ..repositories import producto_repo
from ..funcional import productos_bajo_stock, resumen_inventario, valor_total_inventario

router = APIRouter(prefix="/inventario", tags=["Inventario"])


@router.get("")
def consultar_inventario():
    productos = [p.to_dict() for p in producto_repo.get_all()]
    return {
        "productos": resumen_inventario(productos),
        "valor_total_inventario": valor_total_inventario(productos),
    }


@router.get("/bajo-stock")
def productos_con_bajo_stock(umbral: int = Query(default=5)):
    productos = [p.to_dict() for p in producto_repo.get_all()]
    return productos_bajo_stock(productos, umbral)
