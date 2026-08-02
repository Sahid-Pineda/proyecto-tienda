export class Producto {
  #id;
  #nombre;
  #descripcion;
  #precio;
  #stock;
  #categoriaId;
  #categoriaNombre;
  #imagenUrl;

  constructor(id, nombre, descripcion, precio, stock, categoriaId, categoriaNombre, imagenUrl) {
    this.#id = id !== null && id !== undefined ? Number(id) : null;
    this.#nombre = String(nombre ?? '').trim();
    this.#descripcion = String(descripcion ?? '').trim();
    this.setPrecio(precio);
    this.setStock(stock);
    this.#categoriaId = categoriaId !== null && categoriaId !== undefined ? Number(categoriaId) : null;
    this.#categoriaNombre = categoriaNombre ? String(categoriaNombre) : '';
    this.#imagenUrl = imagenUrl ? String(imagenUrl) : '';
  }

  setPrecio(valor) {
    const precio = Number(valor);
    if (Number.isNaN(precio) || precio < 0) {
      throw new Error('El precio debe ser un número mayor o igual a 0');
    }
    this.#precio = precio;
  }

  setStock(valor) {
    const stock = Number(valor);
    if (Number.isNaN(stock) || stock < 0) {
      throw new Error('El stock debe ser un número mayor o igual a 0');
    }
    this.#stock = stock;
  }

  get id() {
    return this.#id;
  }
  get nombre() {
    return this.#nombre;
  }
  get descripcion() {
    return this.#descripcion;
  }
  get precio() {
    return this.#precio;
  }
  get stock() {
    return this.#stock;
  }
  get categoriaId() {
    return this.#categoriaId;
  }
  get categoriaNombre() {
    return this.#categoriaNombre;
  }
  get imagenUrl() {
    return this.#imagenUrl || 'assets/img/producto-placeholder.svg';
  }

  get disponible() {
    return this.#stock > 0;
  }

  get precioFormateado() {
    return `L. ${this.#precio.toFixed(2)}`;
  }

  toJSON() {
    return {
      id: this.#id,
      nombre: this.#nombre,
      descripcion: this.#descripcion,
      precio: this.#precio,
      stock: this.#stock,
      categoria_id: this.#categoriaId,
    };
  }

  static fromJSON(data) {
    return new Producto(
      data.id ?? null,
      data.nombre ?? '',
      data.descripcion ?? '',
      data.precio ?? 0,
      data.stock ?? 0,
      data.categoria_id ?? data.categoriaId ?? null,
      data.categoria_nombre ?? data.categoriaNombre ?? '',
      data.imagen_url ?? data.imagenUrl ?? ''
    );
  }
}
