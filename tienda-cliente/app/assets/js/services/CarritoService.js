import { ApiService } from './ApiService.js';

export class CarritoService {
  #api;

  constructor() {
    this.#api = new ApiService();
  }

  async ver(clienteId) {
    return this.#api.request('GET', `/carrito/${clienteId}`);
  }

  async agregar(clienteId, productoId, cantidad) {
    return this.#api.request('POST', '/carrito', {
      cliente_id: clienteId,
      producto_id: productoId,
      cantidad,
    });
  }

  async actualizarCantidad(itemId, cantidad) {
    return this.#api.request('PUT', `/carrito/item/${itemId}`, { cantidad });
  }

  async quitar(itemId) {
    return this.#api.request('DELETE', `/carrito/item/${itemId}`);
  }

  async vaciar(clienteId) {
    return this.#api.request('DELETE', `/carrito/${clienteId}/vaciar`);
  }
}
