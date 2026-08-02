import { AuthService } from '../services/AuthService.js';
import { LoginView } from '../views/LoginView.js';
import { Sesion } from '../util/Sesion.js';
import { Flash } from '../util/Flash.js';
import { ApiException } from '../services/ApiException.js';
import { inicializarNavbar } from '../util/Navbar.js';

class LoginApp {
  #service;
  #view;

  constructor() {
    this.#service = new AuthService();
    this.#view = new LoginView({ onSubmit: (datos) => this.entrar(datos) });
  }

  async iniciar() {
    await inicializarNavbar();
    if (Sesion.estaAutenticado()) {
      window.location.href = 'index.html';
    }
  }

  async entrar({ correo, password }) {
    if (correo === '' || password === '') {
      this.#view.mostrarError('Ingresa tu correo y contraseña.');
      return;
    }

    this.#view.bloquearBoton(true);
    try {
      const cliente = await this.#service.login({ correo, password });
      Sesion.iniciar(cliente);
      Flash.set('success', `Bienvenido de nuevo, ${cliente.nombre}.`);
      window.location.href = 'index.html';
    } catch (error) {
      const mensaje =
        error instanceof ApiException ? error.message : 'No se pudo conectar con el backend.';
      this.#view.mostrarError(mensaje);
    } finally {
      this.#view.bloquearBoton(false);
    }
  }
}

new LoginApp().iniciar();
