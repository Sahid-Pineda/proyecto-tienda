import { Carrito } from '../models/Carrito.js';
import { FacturaService } from '../services/FacturaService.js';
import { CheckoutView } from '../views/CheckoutView.js';
import { Sesion } from '../util/Sesion.js';
import { Flash } from '../util/Flash.js';
import { ApiException } from '../services/ApiException.js';
import { inicializarNavbar } from '../util/Navbar.js';

const CLAVE_ULTIMA_FACTURA = 'vertice_ultima_factura';

class CheckoutApp {
  #carrito;
  #service;
  #view;
  #cliente;

  constructor(cliente) {
    this.#cliente = cliente;
    this.#carrito = new Carrito(cliente.id);
    this.#service = new FacturaService();
    this.#view = new CheckoutView({ onSubmit: (datos) => this.confirmar(datos) });
  }

  async iniciar() {
    await inicializarNavbar();
    await this.#carrito.cargar();

    if (this.#carrito.estaVacio) {
      window.location.href = 'carrito.html';
      return;
    }

    this.#view.renderResumen(this.#carrito.items, {
      subtotal: this.#carrito.subtotal,
      impuesto: this.#carrito.impuesto,
      total: this.#carrito.total,
    });
  }

  async confirmar({ direccion, entregaDomicilio }) {
    if (entregaDomicilio && direccion === '') {
      this.#view.mostrarError('Ingresa la dirección de entrega.');
      return;
    }

    this.#view.bloquearBoton(true);

    try {
      const factura = await this.#service.generar({
        clienteId: this.#cliente.id,
        direccion: entregaDomicilio ? direccion : this.#cliente.direccion,
        entregaDomicilio,
      });

      sessionStorage.setItem(
        CLAVE_ULTIMA_FACTURA,
        JSON.stringify(serializarFactura(factura, this.#cliente.nombre))
      );
      window.location.href = 'factura.html';
    } catch (error) {
      const mensaje =
        error instanceof ApiException ? error.message : 'No se pudo conectar con el backend.';
      this.#view.mostrarError(mensaje);
      this.#view.bloquearBoton(false);
    }
  }
}

function serializarFactura(factura, clienteNombre) {
  return {
    numero: factura.numero,
    fecha: factura.fecha,
    cliente_nombre: clienteNombre,
    detalle: factura.detalle.map((d) => ({
      producto_id: d.productoId,
      nombre: d.nombre,
      cantidad: d.cantidad,
      precio_unitario: d.precioUnitario,
      subtotal: d.subtotal,
    })),
    subtotal: factura.subtotal,
    impuesto: factura.impuesto,
    total: factura.total,
    direccion: factura.direccion,
    entrega_domicilio: factura.entregaDomicilio,
  };
}

const cliente = Sesion.obtenerCliente();
if (!cliente) {
  Flash.set('error', 'Inicia sesión para completar tu compra.');
  window.location.href = 'login.html';
} else {
  new CheckoutApp(cliente).iniciar();
}
