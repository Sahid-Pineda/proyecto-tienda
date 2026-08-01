"""
funcional.py
------------
Funciones PURAS (no modifican estado externo, mismo input -> mismo output).
Aquí se concentra la evidencia de PROGRAMACIÓN FUNCIONAL pedida en el
proyecto: funciones de orden superior, lambdas, map/filter/reduce.
"""

from functools import reduce
from typing import List, Dict, Any


# ---------- Cálculos de facturación (funciones puras) ----------

def calcular_subtotal(detalle: List[Dict[str, Any]]) -> float:
    """Suma cantidad*precio_unitario de cada línea usando map + reduce."""
    montos = map(lambda item: item["cantidad"] * item["precio_unitario"], detalle)
    return round(reduce(lambda acc, x: acc + x, montos, 0.0), 2)


def calcular_impuesto(subtotal: float, tasa: float = 0.15) -> float:
    return round(subtotal * tasa, 2)


def calcular_total(subtotal: float, impuesto: float) -> float:
    return round(subtotal + impuesto, 2)


def construir_detalle_factura(productos: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
    """
    productos: lista de dicts {producto, cantidad} donde 'producto' es un dict
    ya resuelto (con precio). Devuelve el detalle con subtotal por línea,
    usando map (función de orden superior).
    """
    def a_linea(item):
        p = item["producto"]
        cantidad = item["cantidad"]
        return {
            "producto_id": p["id"],
            "nombre": p["nombre"],
            "cantidad": cantidad,
            "precio_unitario": p["precio"],
            "subtotal": round(p["precio"] * cantidad, 2),
        }

    return list(map(a_linea, productos))


# ---------- Consultas / filtros sobre productos e inventario ----------

def filtrar_por_categoria(productos: List[Dict[str, Any]], categoria_id: int) -> List[Dict[str, Any]]:
    """Filtra productos por categoría usando filter + lambda."""
    return list(filter(lambda p: p["categoria_id"] == categoria_id, productos))


def productos_bajo_stock(productos: List[Dict[str, Any]], umbral: int = 5) -> List[Dict[str, Any]]:
    """Devuelve productos cuyo stock está por debajo del umbral (filter + lambda)."""
    return list(filter(lambda p: p["stock"] < umbral, productos))


def ordenar_por_precio(productos: List[Dict[str, Any]], descendente: bool = False) -> List[Dict[str, Any]]:
    """Orden funcional usando sorted + lambda (no muta la lista original)."""
    return sorted(productos, key=lambda p: p["precio"], reverse=descendente)


def valor_total_inventario(productos: List[Dict[str, Any]]) -> float:
    """Suma stock*precio de todos los productos usando map + reduce."""
    valores = map(lambda p: p["stock"] * p["precio"], productos)
    return round(reduce(lambda acc, v: acc + v, valores, 0.0), 2)


def resumen_inventario(productos: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
    """Transforma productos a un resumen liviano usando map."""
    return list(map(
        lambda p: {
            "id": p["id"],
            "nombre": p["nombre"],
            "stock": p["stock"],
            "valor_en_stock": round(p["stock"] * p["precio"], 2),
        },
        productos,
    ))


def total_carrito(items_resueltos: List[Dict[str, Any]]) -> float:
    """items_resueltos: [{producto: {...precio}, cantidad: n}, ...]"""
    montos = map(lambda it: it["producto"]["precio"] * it["cantidad"], items_resueltos)
    return round(reduce(lambda acc, x: acc + x, montos, 0.0), 2)
