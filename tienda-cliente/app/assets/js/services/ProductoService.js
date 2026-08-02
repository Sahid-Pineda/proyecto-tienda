import { ApiService } from './ApiService.js';
import { ApiException } from './ApiException.js';
import { Producto } from '../models/Producto.js';

export class ProductoService {
  #api;

  constructor() {
    this.#api = new ApiService();
  }

  async listar(categorias = []) {
    const data = await this.#api.request('GET', '/productos');
    const nombrePorCategoria = new Map(categorias.map((c) => [c.id, c.nombre]));

    return data.map((p) =>
      Producto.fromJSON({ ...p, categoria_nombre: nombrePorCategoria.get(p.categoria_id) ?? '' })
    );
  }

  async obtener(id) {
    try {
      const data = await this.#api.request('GET', `/productos/${id}`);
      return Producto.fromJSON(data);
    } catch (error) {
      if (error instanceof ApiException && error.statusCode === 404) {
        return null;
      }
      throw error;
    }
  }
}
