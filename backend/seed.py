"""
seed.py
-------
Crea datos de ejemplo para poder probar la API inmediatamente:
- 1 usuario administrador (admin / admin123)
- 1 cliente (cliente1 / cliente123)
- 3 categorías, 2 proveedores, varios productos con stock.

Ejecutar UNA sola vez:
    python seed.py
"""

from app.repositories import (
    usuario_repo, cliente_repo, categoria_repo, proveedor_repo, producto_repo
)
from app.models import Usuario, Cliente, Categoria, Proveedor, Producto


def seed():
    if not usuario_repo.get_all():
        usuario_repo.create(Usuario(
            id=usuario_repo.next_id(),
            username="admin",
            password_hash=Usuario.hash_password("admin123"),
            nombre="Administrador General",
            rol="admin",
        ))
        print("Usuario admin creado -> username: admin / password: admin123")

    if not cliente_repo.get_all():
        cliente_repo.create(Cliente(
            id=cliente_repo.next_id(),
            username="cliente1",
            password_hash=Cliente.hash_password("cliente123"),
            nombre="Cliente de Prueba",
            direccion="Col. Kennedy, Tegucigalpa",
            email="cliente1@correo.com",
        ))
        print("Cliente de prueba creado -> username: cliente1 / password: cliente123")

    if not categoria_repo.get_all():
        cat_futbol = Categoria(id=categoria_repo.next_id(), nombre="Fútbol", descripcion="Artículos de fútbol")
        categoria_repo.create(cat_futbol)
        cat_basket = Categoria(id=categoria_repo.next_id(), nombre="Baloncesto", descripcion="Artículos de baloncesto")
        categoria_repo.create(cat_basket)
        cat_fit = Categoria(id=categoria_repo.next_id(), nombre="Fitness", descripcion="Ropa y accesorios de gimnasio")
        categoria_repo.create(cat_fit)
        print("Categorías creadas")

    if not proveedor_repo.get_all():
        prov1 = Proveedor(id=proveedor_repo.next_id(), nombre="Deportes Hondureños S.A.", contacto="Juan Pérez",
                           telefono="9999-0000", email="ventas@deporteshn.com")
        proveedor_repo.create(prov1)
        prov2 = Proveedor(id=proveedor_repo.next_id(), nombre="Importadora Atlética", contacto="María López",
                           telefono="9888-1111", email="contacto@atletica.com")
        proveedor_repo.create(prov2)
        print("Proveedores creados")

    if not producto_repo.get_all():
        categorias = categoria_repo.get_all()
        proveedores = proveedor_repo.get_all()
        cat_futbol_id = categorias[0].id
        cat_basket_id = categorias[1].id
        cat_fit_id = categorias[2].id
        prov1_id = proveedores[0].id
        prov2_id = proveedores[1].id

        base_id = producto_repo.next_id()
        datos_productos = [
            dict(nombre="Balón de Fútbol Pro", descripcion="Balón oficial talla 5",
                 precio=25.99, categoria_id=cat_futbol_id, proveedor_id=prov1_id, stock=30),
            dict(nombre="Camiseta de Fútbol", descripcion="Jersey transpirable",
                 precio=19.50, categoria_id=cat_futbol_id, proveedor_id=prov1_id, stock=50),
            dict(nombre="Balón de Baloncesto", descripcion="Balón indoor/outdoor",
                 precio=29.99, categoria_id=cat_basket_id, proveedor_id=prov2_id, stock=20),
            dict(nombre="Zapatos de Baloncesto", descripcion="Alto rendimiento",
                 precio=79.99, categoria_id=cat_basket_id, proveedor_id=prov2_id, stock=15),
            dict(nombre="Mancuernas 5kg (par)", descripcion="Mancuernas de hierro",
                 precio=22.00, categoria_id=cat_fit_id, proveedor_id=prov2_id, stock=3),
            dict(nombre="Colchoneta de Yoga", descripcion="Antideslizante",
                 precio=15.75, categoria_id=cat_fit_id, proveedor_id=prov1_id, stock=40),
        ]
        for offset, datos in enumerate(datos_productos):
            producto_repo.create(Producto(id=base_id + offset, **datos))
        print("Productos creados")

    print("\nSeed completado.")


if __name__ == "__main__":
    seed()
