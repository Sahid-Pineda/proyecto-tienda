"""
main.py
-------
Punto de entrada de la API REST (FastAPI). Une todos los routers.

Para ejecutar:
    uvicorn app.main:app --reload --port 8000

Documentación interactiva automática en:
    http://127.0.0.1:8000/docs
"""

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from .routers import (
    auth_router,
    usuarios,
    clientes,
    categorias,
    proveedores,
    productos,
    compras,
    carrito,
    facturas,
    inventario,
)

app = FastAPI(
    title="API Tienda de Artículos Deportivos",
    description="Backend REST (FastAPI) - Proyecto Final Paradigmas de Programación",
    version="1.0.0",
)

# Habilita que el portal PHP y la tienda JS (en otros puertos/orígenes) consuman la API
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(auth_router.router)
app.include_router(usuarios.router)
app.include_router(clientes.router)
app.include_router(categorias.router)
app.include_router(proveedores.router)
app.include_router(productos.router)
app.include_router(compras.router)
app.include_router(carrito.router)
app.include_router(facturas.router)
app.include_router(inventario.router)


@app.get("/", tags=["Root"])
def root():
    return {
        "mensaje": "API Tienda de Artículos Deportivos activa",
        "docs": "/docs",
    }
