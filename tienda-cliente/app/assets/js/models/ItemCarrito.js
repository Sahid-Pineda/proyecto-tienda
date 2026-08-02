export class ItemCarrito {
  #id;
  #productoId;
  #nombre;
  #precio;
  #cantidad;

  constructor({ id, productoId, nombre, precio, cantidad }) {
    this.#id = id;
    this.#productoId = productoId;
    this.#nombre = String(nombre ?? '');
    this.#precio = Number(precio);
    this.setCantidad(cantidad);
  }

  setCantidad(valor) {
    const cantidad = Number(valor);
    if (!Number.isInteger(cantidad) || cantidad < 1) {
      throw new Error('La cantidad debe ser un entero mayor a 0');
    }
    this.#cantidad = cantidad;
  }

  get id() {
    return this.#id;
  }
  get productoId() {
    return this.#productoId;
  }
  get nombre() {
    return this.#nombre;
  }
  get precio() {
    return this.#precio;
  }
  get precioFormateado() {
    return `L. ${this.#precio.toFixed(2)}`;
  }
  get cantidad() {
    return this.#cantidad;
  }

  get subtotal() {
    return this.#precio * this.#cantidad;
  }

  static fromJSON(data) {
    return new ItemCarrito({
      id: data.carrito_item_id,
      productoId: data.producto_id,
      nombre: data.nombre,
      precio: data.precio,
      cantidad: data.cantidad,
    });
  }
}
