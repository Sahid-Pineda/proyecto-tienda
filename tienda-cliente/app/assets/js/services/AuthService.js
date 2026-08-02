import { ApiService } from './ApiService.js';
import { ApiException } from './ApiException.js';
import { Cliente } from '../models/Cliente.js';
import { ClienteService } from './ClienteService.js';

export class AuthService {
  #api;
  #clienteService;

  constructor() {
    this.#api = new ApiService();
    this.#clienteService = new ClienteService();
  }

  async registrar({ nombre, correo, password, direccion }) {
    const data = await this.#api.request('POST', '/clientes', {
      username: correo,
      password,
      nombre,
      direccion,
      email: correo,
    });
    return Cliente.fromJSON(data);
  }

  async login({ correo, password }) {
    const data = await this.#api.request('POST', '/login', { username: correo, password });

    if (data.tipo !== 'cliente') {
      throw new ApiException(403, 'Esa cuenta es de administrador. Usa el portal administrativo.');
    }

    return this.#clienteService.obtener(data.id);
  }
}
