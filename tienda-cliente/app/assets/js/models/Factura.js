export class Factura {
  #numero;
  #fecha;
  #clienteNombre;
  #detalle;
  #subtotal;
  #impuesto;
  #total;
  #direccion;
  #entregaDomicilio;

  constructor(numero, fecha, clienteNombre, detalle, subtotal, impuesto, total, direccion, entregaDomicilio) {
    this.#numero = numero;
    this.#fecha = fecha;
    this.#clienteNombre = clienteNombre;
    this.#detalle = detalle;
    this.#subtotal = subtotal;
    this.#impuesto = impuesto;
    this.#total = total;
    this.#direccion = direccion;
    this.#entregaDomicilio = Boolean(entregaDomicilio);
  }

  get numero() {
    return this.#numero;
  }
  get fecha() {
    return this.#fecha;
  }
  get clienteNombre() {
    return this.#clienteNombre;
  }
  get detalle() {
    return this.#detalle;
  }
  get subtotal() {
    return this.#subtotal;
  }
  get impuesto() {
    return this.#impuesto;
  }
  get total() {
    return this.#total;
  }
  get direccion() {
    return this.#direccion;
  }
  get entregaDomicilio() {
    return this.#entregaDomicilio;
  }

  get fechaFormateada() {
    const fecha = new Date(this.#fecha);
    if (Number.isNaN(fecha.getTime())) return this.#fecha;
    return fecha.toLocaleDateString('es-HN', { year: 'numeric', month: 'long', day: 'numeric' });
  }

  static fromJSON(data) {
    const detalle = (data.detalle ?? []).map((d) => ({
      productoId: d.producto_id ?? d.productoId,
      nombre: d.nombre,
      cantidad: d.cantidad,
      precioUnitario: d.precio_unitario ?? d.precioUnitario,
      subtotal: d.subtotal,
    }));

    return new Factura(
      data.numero ?? data.numero_factura ?? '—',
      data.fecha ?? new Date().toISOString(),
      data.cliente_nombre ?? data.cliente?.nombre ?? '',
      detalle,
      data.subtotal ?? 0,
      data.impuesto ?? 0,
      data.total ?? 0,
      data.direccion ?? '',
      data.entrega_domicilio ?? data.entregaDomicilio ?? false
    );
  }
}
