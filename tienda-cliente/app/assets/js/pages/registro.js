import { AuthService } from '../services/AuthService.js';
import { RegistroView } from '../views/RegistroView.js';
import { Sesion } from '../util/Sesion.js';
import { Flash } from '../util/Flash.js';
import { ApiException } from '../services/ApiException.js';
import { inicializarNavbar } from '../util/Navbar.js';

const PATRON_CORREO = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

class RegistroApp {
  #service;
  #view;

  constructor() {
    this.#service = new AuthService();
    this.#view = new RegistroView({ onSubmit: (datos) => this.registrar(datos) });
  }

  async iniciar() {
    await inicializarNavbar();
    if (Sesion.estaAutenticado()) {
      window.location.href = 'index.html';
    }
  }

  validar({ nombre, correo, password, direccion }) {
    const errores = [];
    if (nombre.length < 2) errores.push('Ingresa tu nombre completo.');
    if (!PATRON_CORREO.test(correo)) errores.push('El correo no tiene un formato válido.');
    if (password.length < 6) errores.push('La contraseña debe tener al menos 6 caracteres.');
    if (direccion.length < 5) errores.push('Ingresa una dirección de entrega válida.');
    return errores;
  }

  async registrar(datos) {
    const errores = this.validar(datos);
    if (errores.length > 0) {
      this.#view.mostrarErrores(errores);
      return;
    }

    this.#view.bloquearBoton(true);
    try {
      const cliente = await this.#service.registrar(datos);
      Sesion.iniciar(cliente);
      Flash.set('success', `Cuenta creada. ¡Bienvenido, ${cliente.nombre}!`);
      window.location.href = 'index.html';
    } catch (error) {
      const mensaje =
        error instanceof ApiException ? error.message : 'No se pudo conectar con el backend.';
      this.#view.mostrarErrores([mensaje]);
    } finally {
      this.#view.bloquearBoton(false);
    }
  }
}

new RegistroApp().iniciar();
