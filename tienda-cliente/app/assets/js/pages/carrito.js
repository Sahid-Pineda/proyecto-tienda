import { Carrito } from '../models/Carrito.js';
import { CarritoView } from '../views/CarritoView.js';
import { Sesion } from '../util/Sesion.js';
import { Flash } from '../util/Flash.js';
import { inicializarNavbar } from '../util/Navbar.js';

class CarritoApp {
  #carrito;
  #view;

  constructor(clienteId) {
    this.#carrito = new Carrito(clienteId);
    this.#view = new CarritoView({
      onCambiarCantidad: (itemId, cantidad) => this.cambiarCantidad(itemId, cantidad),
      onQuitar: (itemId) => this.quitar(itemId),
    });
  }

  async iniciar() {
    await inicializarNavbar();
    await this.cargarYRender();

    const btnPagar = document.querySelector('[data-btn-pagar]');
    btnPagar?.addEventListener('click', () => {
      window.location.href = 'checkout.html';
    });
  }

  async cargarYRender() {
    await this.#carrito.cargar();
    this.render();
  }

  render() {
    this.#view.render(this.#carrito.items, {
      subtotal: this.#carrito.subtotal,
      impuesto: this.#carrito.impuesto,
      total: this.#carrito.total,
    });
  }

  async cambiarCantidad(itemId, cantidad) {
    try {
      await this.#carrito.actualizarCantidad(itemId, cantidad);
    } catch (error) {
      alert(error.message);
    }
    this.render();
    await inicializarNavbar();
  }

  async quitar(itemId) {
    await this.#carrito.quitar(itemId);
    this.render();
    await inicializarNavbar();
  }
}

const cliente = Sesion.obtenerCliente();
if (!cliente) {
  Flash.set('error', 'Inicia sesión para ver tu carrito.');
  window.location.href = 'login.html';
} else {
  new CarritoApp(cliente.id).iniciar();
}
