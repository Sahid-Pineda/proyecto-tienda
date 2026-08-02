import { ApiService } from './ApiService.js';
import { Categoria } from '../models/Categoria.js';

export class CategoriaService {
  #api;

  constructor() {
    this.#api = new ApiService();
  }

  async listar() {
    const data = await this.#api.request('GET', '/categorias');
    return data.map((c) => Categoria.fromJSON(c));
  }
}
