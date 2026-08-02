import { API_BASE_URL } from '../config.js';
import { ApiException } from './ApiException.js';

export class ApiService {
  #baseUrl;

  constructor(baseUrl = API_BASE_URL) {
    this.#baseUrl = baseUrl;
  }

  async request(method, path, body = null) {
    let respuesta;

    try {
      respuesta = await fetch(this.#baseUrl + path, {
        method,
        headers:
          body !== null
            ? { 'Content-Type': 'application/json', Accept: 'application/json' }
            : { Accept: 'application/json' },
        body: body !== null ? JSON.stringify(body) : undefined,
      });
    } catch {
      throw new ApiException(0, `No se pudo conectar con el backend (${this.#baseUrl}).`);
    }

    const texto = await respuesta.text();
    const data = texto !== '' ? JSON.parse(texto) : null;

    if (!respuesta.ok) {
      const detail = data && data.detail ? data.detail : 'Error desconocido del backend';
      throw new ApiException(
        respuesta.status,
        typeof detail === 'string' ? detail : JSON.stringify(detail)
      );
    }

    return data;
  }
}
