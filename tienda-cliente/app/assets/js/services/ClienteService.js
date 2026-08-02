import { ApiService } from './ApiService.js';
import { Cliente } from '../models/Cliente.js';

export class ClienteService {
  #api;

  constructor() {
    this.#api = new ApiService();
  }

  async obtener(id) {
    const data = await this.#api.request('GET', `/clientes/${id}`);
    return Cliente.fromJSON(data);
  }
}
