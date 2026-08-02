export class Categoria {
  #id;
  #nombre;

  constructor(id, nombre) {
    this.#id = id !== null && id !== undefined ? Number(id) : null;
    this.#nombre = String(nombre).trim();
  }

  get id() {
    return this.#id;
  }

  get nombre() {
    return this.#nombre;
  }

  toJSON() {
    return { id: this.#id, nombre: this.#nombre };
  }

  static fromJSON(data) {
    return new Categoria(data.id ?? null, data.nombre ?? '');
  }
}
