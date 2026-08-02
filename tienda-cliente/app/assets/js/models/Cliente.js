const PATRON_CORREO = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export class Cliente {
  #id;
  #username;
  #nombre;
  #direccion;
  #email;

  constructor(id, username, nombre, direccion = '', email = '') {
    this.#id = id !== null && id !== undefined ? Number(id) : null;
    this.setUsername(username);
    this.#nombre = String(nombre ?? '').trim();
    this.#direccion = String(direccion ?? '').trim();
    this.#email = String(email ?? username ?? '').trim();
  }

  setUsername(valor) {
    const username = String(valor ?? '').trim();
    if (username === '') {
      throw new Error('El usuario/correo no puede estar vacío');
    }
    this.#username = username;
  }

  get id() {
    return this.#id;
  }
  get username() {
    return this.#username;
  }
  get correo() {
    return this.#username;
  }
  get nombre() {
    return this.#nombre;
  }
  get direccion() {
    return this.#direccion;
  }
  set direccion(valor) {
    this.#direccion = String(valor ?? '').trim();
  }
  get email() {
    return this.#email;
  }

  static validarCorreo(correo) {
    return PATRON_CORREO.test(String(correo ?? '').trim());
  }

  toJSON() {
    return {
      id: this.#id,
      username: this.#username,
      nombre: this.#nombre,
      direccion: this.#direccion,
      email: this.#email,
    };
  }

  static fromJSON(data) {
    return new Cliente(
      data.id ?? null,
      data.username ?? data.correo ?? '',
      data.nombre ?? '',
      data.direccion ?? '',
      data.email ?? ''
    );
  }
}
