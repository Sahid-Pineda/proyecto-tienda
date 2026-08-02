import { ApiService } from './ApiService.js';
import { Factura } from '../models/Factura.js';

export class FacturaService {
  #api;

  constructor() {
    this.#api = new ApiService();
  }

  async generar({ clienteId, direccion, entregaDomicilio }) {
    const data = await this.#api.request('POST', '/facturas', {
      cliente_id: clienteId,
      direccion,
      entrega_domicilio: entregaDomicilio,
    });
    return Factura.fromJSON(data);
  }
}
