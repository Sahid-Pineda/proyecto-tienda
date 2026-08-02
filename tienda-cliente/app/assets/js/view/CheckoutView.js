export class CheckoutView {
  #form;
  #contenedorResumen;
  #alerta;
  #inputEntrega;
  #campoDireccion;
  #onSubmit;

  constructor({ onSubmit }) {
    this.#form = document.querySelector('[data-form-checkout]');
    this.#contenedorResumen = document.querySelector('[data-resumen-checkout]');
    this.#alerta = document.querySelector('[data-alerta]');
    this.#inputEntrega = document.querySelector('[data-check-entrega]');
    this.#campoDireccion = document.querySelector('[data-campo-direccion]');
    this.#onSubmit = onSubmit;

    this.#inputEntrega?.addEventListener('change', () => this.#alternarDireccion());
    this.#alternarDireccion();

    this.#form?.addEventListener('submit', (evento) => {
      evento.preventDefault();
      this.limpiarAlerta();
      this.#onSubmit({
        direccion: this.#form.direccion.value.trim(),
        entregaDomicilio: this.#inputEntrega ? this.#inputEntrega.checked : false,
      });
    });
  }

  #alternarDireccion() {
    if (!this.#campoDireccion || !this.#inputEntrega) return;
    this.#campoDireccion.style.display = this.#inputEntrega.checked ? 'flex' : 'none';
    this.#form.direccion.required = this.#inputEntrega.checked;
  }

  renderResumen(items, { subtotal, impuesto, total }) {
    if (!this.#contenedorResumen) return;
    const filas = items
      .map(
        (item) => `
      <div class="fila-resumen">
        <span>${item.cantidad} × ${escaparTexto(item.nombre)}</span>
        <span class="valor">L. ${item.subtotal.toFixed(2)}</span>
      </div>`
      )
      .join('');

    this.#contenedorResumen.innerHTML = `
      ${filas}
      <div class="fila-resumen"><span>Subtotal</span><span class="valor">L. ${subtotal.toFixed(2)}</span></div>
      <div class="fila-resumen"><span>ISV (15%)</span><span class="valor">L. ${impuesto.toFixed(2)}</span></div>
      <div class="fila-resumen total"><span>Total</span><span class="valor">L. ${total.toFixed(2)}</span></div>
    `;
  }

  mostrarError(mensaje) {
    if (!this.#alerta) return;
    this.#alerta.className = 'alerta alerta-error';
    this.#alerta.textContent = mensaje;
    this.#alerta.hidden = false;
  }

  limpiarAlerta() {
    if (!this.#alerta) return;
    this.#alerta.hidden = true;
  }

  bloquearBoton(bloqueado) {
    const boton = this.#form?.querySelector('button[type="submit"]');
    if (boton) {
      boton.disabled = bloqueado;
      boton.textContent = bloqueado ? 'Procesando…' : 'Confirmar compra';
    }
  }
}

function escaparTexto(texto) {
  const div = document.createElement('div');
  div.textContent = texto;
  return div.innerHTML;
}
