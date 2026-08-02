# Portal Administrativo

Portal web en PHP para administrar productos, categorías, proveedores, usuarios, compras y ventas consumiendo la API del backend FastAPI.

## Requisitos

- PHP 8.x
- Extensión cURL habilitada en PHP
- Python 3.11+ para ejecutar el backend
- Un servidor web local o PHP built-in

## Estructura

- `index.php`: panel principal y resumen de inventario
- `categorias.php`: gestión de categorías
- `productos.php`: gestión de productos
- `proveedores.php`: gestión de proveedores
- `usuarios.php`: gestión de usuarios
- `compras.php` y `compras_registro.php`: registro y consulta de compras
- `ventas.php`: consulta de ventas y facturas
- `includes/`: header, footer y estilos compartidos
- `src/`: servicios, modelos y utilidades PHP

## Ejecutar el portal

### Opción 1: usando PHP built-in

```bash
cd portal-administracion
php -S 127.0.0.1:8080
```

Luego abrir en el navegador:

```text
http://127.0.0.1:8080
```

### Opción 2: usando un servidor web local

Configura tu servidor web para apuntar al directorio `portal-administracion` y accede a la URL correspondiente.

## Ejecutar el backend

Desde la raíz del proyecto:

```bash
cd backend
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --reload --host 127.0.0.1 --port 8000
```

## Credenciales de acceso

El login del portal se realiza contra la API del backend.

Usuario de ejemplo:

```text
admin
admin123
```

## Notas

- El portal espera que el backend esté disponible en `http://127.0.0.1:8000`.
- Si cambias la URL del backend, ajusta la constante `API_BASE_URL` en `config.php`.
