import { TASA_IMPUESTO } from '../config.js';

export const calcularSubtotal = (items) => items.reduce((acumulado, item) => acumulado + item.subtotal, 0);

export const calcularImpuesto = (subtotal, tasa = TASA_IMPUESTO) => Number((subtotal * tasa).toFixed(2));

export const calcularTotal = (subtotal, impuesto) => Number((subtotal + impuesto).toFixed(2));

export const construirDetalleFactura = (items) =>
  items.map((item) => ({
    productoId: item.producto.id,
    nombre: item.producto.nombre,
    cantidad: item.cantidad,
    precioUnitario: item.producto.precio,
    subtotal: item.subtotal,
  }));

export const contarUnidades = (items) => items.reduce((total, item) => total + item.cantidad, 0);

export const filtrarPorCategoria = (productos, categoriaId) =>
  categoriaId === null || categoriaId === undefined || categoriaId === 'todas'
    ? productos
    : productos.filter((producto) => producto.categoriaId === Number(categoriaId));

export const buscarPorTexto = (productos, termino) => {
  const valor = termino.trim().toLowerCase();
  if (valor === '') return productos;
  return productos.filter(
    (producto) =>
      producto.nombre.toLowerCase().includes(valor) || producto.descripcion.toLowerCase().includes(valor)
  );
};

export const compararPor = (criterio, orden = 'asc') => (a, b) => {
  const factor = orden === 'asc' ? 1 : -1;
  if (a[criterio] < b[criterio]) return -1 * factor;
  if (a[criterio] > b[criterio]) return 1 * factor;
  return 0;
};

export const aplicarPipelineCatalogo = (productos, { categoriaId, termino, orden }) => {
  let resultado = filtrarPorCategoria(productos, categoriaId);
  resultado = buscarPorTexto(resultado, termino ?? '');
  if (orden) {
    resultado = [...resultado].sort(compararPor(orden.criterio, orden.direccion));
  }
  return resultado;
};
