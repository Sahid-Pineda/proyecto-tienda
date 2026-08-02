import { Cliente } from '../models/Cliente.js';

const CLAVE_STORAGE = 'vertice_cliente';

export class Sesion {
  static iniciar(cliente) {
    localStorage.setItem(CLAVE_STORAGE, JSON.stringify(cliente.toJSON()));
  }

  static obtenerCliente() {
    const crudo = localStorage.getItem(CLAVE_STORAGE);
    if (!crudo) return null;
    try {
      return Cliente.fromJSON(JSON.parse(crudo));
    } catch {
      return null;
    }
  }

  static estaAutenticado() {
    return Sesion.obtenerCliente() !== null;
  }

  static cerrar() {
    localStorage.removeItem(CLAVE_STORAGE);
  }
}
