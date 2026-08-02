# Vértice Deportes — Módulo JavaScript (Integrante 3)

Tienda virtual para clientes: registro/login, catálogo con filtro por
categoría y búsqueda, carrito de compras, checkout con compra simulada,
factura y opción de entrega a domicilio. Todo el acceso a datos pasa por la
API REST (nunca se toca una base de datos directamente desde el navegador).

## Cómo correrlo

**1. Backend (Python/FastAPI):**

```bash
cd tienda_backend
pip install -r requirements.txt
python seed.py          # solo la primera vez, crea datos de ejemplo
uvicorn app.main:app --reload --port 8000
```

**2. Frontend** (los ES Modules no cargan desde `file://`, necesita un
servidor estático):

```bash
cd app
python -m http.server 5500
```

Abre `http://127.0.0.1:5500` en el navegador.

Si el backend corre en otra URL/puerto, cambia una sola línea en
`app/assets/js/config.js`:

```js
export const API_BASE_URL = 'http://127.0.0.1:8000';
```

Usuario de prueba que crea `seed.py`: `cliente1` / `cliente123`.

## Estructura

```
app/
├── index.html          # Catálogo (con filtro por categoría y búsqueda)
├── login.html
├── registro.html
├── carrito.html
├── checkout.html        # Dirección de entrega + confirmar compra
├── factura.html
└── assets/
    ├── css/style.css
    └── js/
        ├── config.js             # API_BASE_URL
        ├── models/                # Clases POO (Producto, Cliente, Carrito...)
        ├── services/               # Clientes fetch hacia el backend
        ├── views/                  # Pintan el DOM, sin saber de HTTP
        ├── util/                   # funcional.js, Sesion, Flash, Navbar
        └── pages/                  # Controladores (uno por página)
```

### Dónde está la POO
- Clases con **campos privados** (`#id`, `#precio`...) y *getters/setters*
  con validación: `Producto`, `Cliente`, `ItemCarrito`, `Carrito`, `Factura`.
- Encapsulamiento real: `producto.setPrecio()` / `item.setCantidad()`
  lanzan error si el valor es inválido.
- `Carrito` se compone de `ItemCarrito`; ambos usan el `CarritoService`
  correspondiente para hablar con el backend (composición).

### Dónde está la programación funcional
Todo en `assets/js/util/funcional.js`:
- **Funciones puras**: `calcularSubtotal`, `calcularImpuesto`, `calcularTotal`.
- **map/filter/reduce**: totales del carrito, filtro por categoría, búsqueda
  por texto, construcción del detalle de factura.
- **Funciones de orden superior**: `compararPor(criterio, orden)` devuelve
  una función comparadora; `aplicarPipelineCatalogo` compone filtro +
  búsqueda + orden en un pipeline.

## Contrato de API

| Método | Endpoint                       | Body                                                   | Respuesta |
|--------|----------------------------------|------------------------------------------------------------|-----------|
| GET    | `/categorias`                    | —                                                            | `[{id, nombre, descripcion}]` |
| GET    | `/productos`                      | —                                                            | `[{id, nombre, descripcion, precio, categoria_id, proveedor_id, stock}]` |
| GET    | `/productos/{id}`                 | —                                                            | producto o `404` |
| POST   | `/clientes`                       | `{username, password, nombre, direccion, email}`            | cliente creado (`password_hash` viene enmascarado) |
| GET    | `/clientes/{id}`                  | —                                                            | perfil completo del cliente |
| POST   | `/login`                          | `{username, password}`                                       | `{ok, tipo: "admin"|"cliente", id, nombre, mensaje}` |
| GET    | `/carrito/{cliente_id}`           | —                                                            | `{cliente_id, items:[{carrito_item_id, producto_id, nombre, precio, cantidad, subtotal}], total}` |
| POST   | `/carrito`                        | `{cliente_id, producto_id, cantidad}`                        | item del carrito (si ya existía, suma la cantidad) |
| PUT    | `/carrito/item/{item_id}`         | `{cantidad}`                                                 | item actualizado |
| DELETE | `/carrito/item/{item_id}`         | —                                                            | `204` |
| DELETE | `/carrito/{cliente_id}/vaciar`    | —                                                            | `204` |
| POST   | `/facturas`                       | `{cliente_id, direccion, entrega_domicilio}` (**sin items**) | factura con detalle, subtotal, impuesto (15%), total |

## Notas
- El login es simple (sin tokens): el cliente autenticado se guarda en
  `localStorage` del navegador.
- La búsqueda y el filtro por categoría del catálogo se resuelven en el
  navegador (sobre `GET /productos` completo) con funciones puras de
  `funcional.js`. El backend también soporta `?categoria_id=` y
  `?orden_precio=asc|desc` por si luego quieres usarlos.
- No hay pasarela de pago real, como indica el enunciado del proyecto.
