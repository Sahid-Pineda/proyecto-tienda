export class FacturaView {
  #contenedor;

  constructor() {
    this.#contenedor = document.querySelector('[data-factura]');
  }

  render(factura) {
    if (!this.#contenedor) return;

    const filasDetalle = factura.detalle
      .map(
        (d) => `
      <tr>
        <td>${escaparTexto(d.nombre)}</td>
        <td class="num">${d.cantidad}</td>
        <td class="num">L. ${Number(d.precioUnitario).toFixed(2)}</td>
        <td class="num">L. ${Number(d.subtotal).toFixed(2)}</td>
      </tr>`
      )
      .join('');

    this.#contenedor.innerHTML = `
      <div class="factura-encabezado">
        <div>
          <div class="hero-eyebrow" style="margin-bottom:.3rem">Factura de compra</div>
          <div class="factura-numero">N.° ${escaparTexto(String(factura.numero))}</div>
        </div>
        <div class="factura-meta">
          <div>${escaparTexto(factura.fechaFormateada)}</div>
          <div>Cliente: ${escaparTexto(factura.clienteNombre)}</div>
          ${factura.entregaDomicilio ? `<div>Entrega a domicilio</div>` : `<div>Retiro en tienda</div>`}
        </div>
      </div>

      <table class="tabla-factura">
        <thead>
          <tr>
            <th>Artículo</th>
            <th class="num">Cant.</th>
            <th class="num">Precio</th>
            <th class="num">Subtotal</th>
          </tr>
        </thead>
        <tbody>${filasDetalle}</tbody>
      </table>

      <div class="factura-totales">
        <div class="fila-resumen"><span>Subtotal</span><span class="valor">L. ${Number(factura.subtotal).toFixed(2)}</span></div>
        <div class="fila-resumen"><span>ISV (15%)</span><span class="valor">L. ${Number(factura.impuesto).toFixed(2)}</span></div>
        <div class="fila-resumen total"><span>Total</span><span class="valor">L. ${Number(factura.total).toFixed(2)}</span></div>
      </div>

      ${
        factura.direccion
          ? `<div class="factura-nota"><strong>Dirección de entrega:</strong> ${escaparTexto(factura.direccion)}</div>`
          : ''
      }
      <div class="factura-nota">Compra simulada — proyecto académico UNAH, no representa una transacción real.</div>
    `;
  }

  mostrarError(mensaje) {
    if (!this.#contenedor) return;
    this.#contenedor.innerHTML = `<div class="vacio">${mensaje}</div>`;
  }
}

function escaparTexto(texto) {
  const div = document.createElement('div');
  div.textContent = texto ?? '';
  return div.innerHTML;
}
