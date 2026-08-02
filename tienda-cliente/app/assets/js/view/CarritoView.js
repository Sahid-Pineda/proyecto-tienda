export class CarritoView {
  #contenedorItems;
  #elSubtotal;
  #elImpuesto;
  #elTotal;
  #btnPagar;
  #onCambiarCantidad;
  #onQuitar;

  constructor({ onCambiarCantidad, onQuitar }) {
    this.#contenedorItems = document.querySelector('[data-lista-items]');
    this.#elSubtotal = document.querySelector('[data-subtotal]');
    this.#elImpuesto = document.querySelector('[data-impuesto]');
    this.#elTotal = document.querySelector('[data-total]');
    this.#btnPagar = document.querySelector('[data-btn-pagar]');
    this.#onCambiarCantidad = onCambiarCantidad;
    this.#onQuitar = onQuitar;
  }

  render(items, { subtotal, impuesto, total }) {
    if (this.#contenedorItems) {
      this.#contenedorItems.innerHTML = '';

      if (items.length === 0) {
        this.#contenedorItems.innerHTML =
          '<div class="vacio">Tu carrito está vacío. <a href="index.html" style="color:var(--accent)">Ver catálogo →</a></div>';
      } else {
        items.forEach((item) => this.#contenedorItems.appendChild(this.#crearFila(item)));
      }
    }

    if (this.#elSubtotal) this.#elSubtotal.textContent = `L. ${subtotal.toFixed(2)}`;
    if (this.#elImpuesto) this.#elImpuesto.textContent = `L. ${impuesto.toFixed(2)}`;
    if (this.#elTotal) this.#elTotal.textContent = `L. ${total.toFixed(2)}`;
    if (this.#btnPagar) this.#btnPagar.disabled = items.length === 0;
  }

  #crearFila(item) {
    const fila = document.createElement('div');
    fila.className = 'fila-item';
    fila.innerHTML = `
      <div class="info">
        <h4></h4>
        <span class="precio-unit"></span>
      </div>
      <div class="control-cantidad">
        <button type="button" data-restar>−</button>
        <span data-cantidad></span>
        <button type="button" data-sumar>+</button>
      </div>
      <div style="text-align:right">
        <div class="precio" data-subtotal-item></div>
        <button type="button" class="quitar-item" data-quitar>Quitar</button>
      </div>
    `;

    fila.querySelector('h4').textContent = item.nombre;
    fila.querySelector('.precio-unit').textContent = `${item.precioFormateado} c/u`;
    fila.querySelector('[data-cantidad]').textContent = item.cantidad;
    fila.querySelector('[data-subtotal-item]').textContent = `L. ${item.subtotal.toFixed(2)}`;

    fila.querySelector('[data-restar]').addEventListener('click', () =>
      this.#onCambiarCantidad(item.id, item.cantidad - 1)
    );
    fila.querySelector('[data-sumar]').addEventListener('click', () =>
      this.#onCambiarCantidad(item.id, item.cantidad + 1)
    );
    fila.querySelector('[data-quitar]').addEventListener('click', () => this.#onQuitar(item.id));

    return fila;
  }
}
