# Backend REST — Tienda de Artículos Deportivos (Python / FastAPI)

Este módulo corresponde a la parte del **Integrante 1 (Python)** del proyecto
"Paradigmas de Programación" (UNAH). Es el backend REST que consumirán el
portal administrativo en PHP y la tienda virtual en JavaScript.

## Contenido

- API REST completa en **FastAPI**, respuestas en **JSON**.
- **Login simple** (sin tokens ni sesiones complejas) para administradores y clientes.
- CRUD de: usuarios, clientes, categorías, productos, proveedores.
- **Compras** a proveedor → aumentan inventario automáticamente.
- **Carrito de compras** y **facturación** simulada → disminuyen inventario automáticamente.
- Persistencia en **archivos planos JSON** (carpeta `app/data/`), sin necesidad de base de datos.
- Evidencia de **POO**: clases (`app/models.py`), encapsulamiento (p. ej. `Producto.aumentar_stock`/
  `disminuir_stock` validan reglas de negocio), herencia (`JsonRepository` → repositorios concretos),
  composición (los routers/servicios usan repositorios y modelos).
- Evidencia de **Programación Funcional** (`app/funcional.py`): funciones puras, `map`, `filter`,
  `reduce` y `lambda` para cálculos de factura, filtrado de productos por categoría, reportes de
  inventario y productos con bajo stock.

## Estructura del proyecto

```
tienda_backend/
├── app/
│   ├── main.py            # App FastAPI, monta todos los routers, CORS habilitado
│   ├── models.py          # Clases de dominio (POO)
│   ├── storage.py         # Repositorio genérico sobre archivos JSON
│   ├── repositories.py    # Instancias concretas de repositorios por entidad
│   ├── schemas.py         # Esquemas Pydantic (validación de requests)
│   ├── funcional.py       # Funciones puras (map/filter/reduce/lambda)
│   ├── data/               # Archivos planos JSON (se crean/actualizan solos)
│   └── routers/
│       ├── auth_router.py     # POST /login
│       ├── usuarios.py        # /usuarios (CRUD)
│       ├── clientes.py        # /clientes (registro y CRUD)
│       ├── categorias.py      # /categorias (CRUD)
│       ├── proveedores.py     # /proveedores (CRUD)
│       ├── productos.py       # /productos (CRUD + filtro por categoría)
│       ├── compras.py         # /compras (aumenta inventario)
│       ├── carrito.py         # /carrito (agregar/ver/editar/eliminar)
│       ├── facturas.py        # /facturas (venta simulada, disminuye inventario)
│       └── inventario.py      # /inventario (consulta y reporte de bajo stock)
├── seed.py                 # Datos de ejemplo para probar de inmediato
└── requirements.txt
```

## Instalación y ejecución

1. Crear entorno virtual (opcional pero recomendado) e instalar dependencias:

   ```bash
   cd tienda_backend
   pip install -r requirements.txt
   ```

2. Cargar datos de ejemplo (solo la primera vez):

   ```bash
   python seed.py
   ```

   Esto crea:
   - Administrador → usuario: `admin` / contraseña: `admin123`
   - Cliente de prueba → usuario: `cliente1` / contraseña: `cliente123`
   - 3 categorías, 2 proveedores y 6 productos con stock inicial.

3. Levantar el servidor:

   ```bash
   uvicorn app.main:app --reload --port 8000
   ```

4. Abrir la documentación interactiva (Swagger) para probar todos los endpoints:

   ```
   http://127.0.0.1:8000/docs
   ```

El portal PHP y la tienda JavaScript deben apuntar a `http://127.0.0.1:8000` (o la URL/puerto donde
publiques este backend) para consumir la API. CORS ya está habilitado para cualquier origen.

## Endpoints principales

| Método | Ruta                          | Descripción                                      |
|--------|-------------------------------|---------------------------------------------------|
| POST   | `/login`                      | Login simple (admin o cliente)                     |
| GET/POST/PUT/DELETE | `/usuarios`      | CRUD de administradores                            |
| GET/POST/PUT/DELETE | `/clientes`      | CRUD / registro de clientes                        |
| GET/POST/PUT/DELETE | `/categorias`    | CRUD de categorías                                 |
| GET/POST/PUT/DELETE | `/proveedores`   | CRUD de proveedores                                |
| GET/POST/PUT/DELETE | `/productos`     | CRUD de productos (`?categoria_id=`, `?orden_precio=asc|desc`) |
| GET, POST | `/compras`                 | Registrar compra a proveedor (aumenta inventario)  |
| GET, POST, PUT, DELETE | `/carrito`       | Gestión del carrito de un cliente                  |
| GET, POST | `/facturas`                 | Generar factura desde el carrito (disminuye inventario) |
| GET    | `/inventario`                 | Reporte de inventario y valor total                |
| GET    | `/inventario/bajo-stock`      | Productos por debajo de un umbral de stock         |

### Ejemplo: login

```bash
curl -X POST http://127.0.0.1:8000/login \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "admin123"}'
```

### Ejemplo: flujo de compra de un cliente

```bash
# 1. Agregar productos al carrito
curl -X POST http://127.0.0.1:8000/carrito \
  -H "Content-Type: application/json" \
  -d '{"cliente_id": 1, "producto_id": 1, "cantidad": 2}'

# 2. Generar factura (vacía el carrito y descuenta inventario)
curl -X POST http://127.0.0.1:8000/facturas \
  -H "Content-Type: application/json" \
  -d '{"cliente_id": 1, "entrega_domicilio": true}'
```

## Notas

- La "base de datos" son los archivos JSON en `app/data/`; se pueden inspeccionar o respaldar
  directamente como texto plano.
- El login es intencionalmente simple (valida usuario/contraseña con hash sha256 y responde con los
  datos del usuario), sin JWT ni manejo de sesiones, tal como lo permite el enunciado del proyecto.
- Si necesitas reiniciar los datos, borra la carpeta `app/data/` y vuelve a correr `python seed.py`.
