import { Carrito } from '../models/Carrito.js';
import { Sesion } from './Sesion.js';

export async function inicializarNavbar() {
  const zonaUsuario = document.querySelector('[data-zona-usuario]');
  const cliente = Sesion.obtenerCliente();

  if (zonaUsuario) {
    if (cliente) {
      zonaUsuario.innerHTML = `
        <span>Hola, ${escaparTexto(cliente.nombre.split(' ')[0])}</span>
        <button type="button" class="link" data-cerrar-sesion>Salir</button>
      `;
      zonaUsuario.querySelector('[data-cerrar-sesion]').addEventListener('click', () => {
        Sesion.cerrar();
        window.location.href = 'index.html';
      });
    } else {
      zonaUsuario.innerHTML = `
        <a href="login.html">Iniciar sesión</a>
        <a href="registro.html">Crear cuenta</a>
      `;
    }
  }

  const badge = document.querySelector('[data-badge-carrito]');
  if (!badge) return;

  if (!cliente) {
    badge.textContent = '0';
    return;
  }

  try {
    const carrito = new Carrito(cliente.id);
    await carrito.cargar();
    badge.textContent = String(carrito.totalUnidades);
  } catch {
    badge.textContent = '0';
  }
}

function escaparTexto(texto) {
  const div = document.createElement('div');
  div.textContent = texto;
  return div.innerHTML;
}
