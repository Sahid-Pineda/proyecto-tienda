import { ItemCarrito } from './ItemCarrito.js';
import { CarritoService } from '../services/CarritoService.js';
import { calcularSubtotal, calcularImpuesto, calcularTotal, contarUnidades } from '../util/funcional.js';

export class Carrito {
  #service;
  #clienteId;
  #items = [];

  constructor(clienteId) {
    this.#service = new CarritoService();
    this.#clienteId = clienteId ?? null;
  }

  async cargar() {
    if (this.#clienteId === null) {
      this.#items = [];
      return;
    }
    const data = await this.#service.ver(this.#clienteId);
    this.#items = (data.items ?? []).map((item) => ItemCarrito.fromJSON(item));
  }

  async agregar(productoId, cantidad = 1) {
    this.#exigirCliente();
    await this.#service.agregar(this.#clienteId, productoId, cantidad);
    await this.cargar();
  }

  async actualizarCantidad(itemId, cantidad) {
    this.#exigirCliente();
    if (cantidad <= 0) {
      await this.quitar(itemId);
      return;
    }
    await this.#service.actualizarCantidad(itemId, cantidad);
    await this.cargar();
  }

  async quitar(itemId) {
    this.#exigirCliente();
    await this.#service.quitar(itemId);
    await this.cargar();
  }

  async vaciar() {
    this.#exigirCliente();
    await this.#service.vaciar(this.#clienteId);
    this.#items = [];
  }

  #exigirCliente() {
    if (this.#clienteId === null) {
      throw new Error('Debes iniciar sesión para usar el carrito.');
    }
  }

  get items() {
    return this.#items;
  }

  get estaVacio() {
    return this.#items.length === 0;
  }

  get subtotal() {
    return calcularSubtotal(this.#items);
  }

  get impuesto() {
    return calcularImpuesto(this.subtotal);
  }

  get total() {
    return calcularTotal(this.subtotal, this.impuesto);
  }

  get totalUnidades() {
    return contarUnidades(this.#items);
  }
}
