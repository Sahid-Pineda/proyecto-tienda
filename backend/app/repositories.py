"""
repositories.py
----------------
Instancias concretas del repositorio genérico para cada entidad del dominio.
Cada uno "sabe" convertir su entidad de/​a diccionario (to_dict/from_dict).
"""

from .storage import JsonRepository
from .models import Usuario, Cliente, Categoria, Proveedor, Producto, Compra, ItemCarrito, Factura

usuario_repo = JsonRepository[Usuario](
    "usuarios.json",
    to_dict=lambda u: u.to_dict(),
    from_dict=lambda d: Usuario(**d),
)

cliente_repo = JsonRepository[Cliente](
    "clientes.json",
    to_dict=lambda c: c.to_dict(),
    from_dict=lambda d: Cliente(**d),
)

categoria_repo = JsonRepository[Categoria](
    "categorias.json",
    to_dict=lambda c: c.to_dict(),
    from_dict=lambda d: Categoria(**d),
)

proveedor_repo = JsonRepository[Proveedor](
    "proveedores.json",
    to_dict=lambda p: p.to_dict(),
    from_dict=lambda d: Proveedor(**d),
)

producto_repo = JsonRepository[Producto](
    "productos.json",
    to_dict=lambda p: p.to_dict(),
    from_dict=lambda d: Producto(**d),
)

compra_repo = JsonRepository[Compra](
    "compras.json",
    to_dict=lambda c: c.to_dict(),
    from_dict=lambda d: Compra(**d),
)

carrito_repo = JsonRepository[ItemCarrito](
    "carrito.json",
    to_dict=lambda i: i.to_dict(),
    from_dict=lambda d: ItemCarrito(**d),
)

factura_repo = JsonRepository[Factura](
    "facturas.json",
    to_dict=lambda f: f.to_dict(),
    from_dict=lambda d: Factura(**d),
)
