import { Factura } from '../models/Factura.js';
import { FacturaView } from '../views/FacturaView.js';
import { inicializarNavbar } from '../util/Navbar.js';

const CLAVE_ULTIMA_FACTURA = 'vertice_ultima_factura';

function iniciar() {
  inicializarNavbar();
  const view = new FacturaView();
  const crudo = sessionStorage.getItem(CLAVE_ULTIMA_FACTURA);

  if (!crudo) {
    view.mostrarError('No hay ninguna factura reciente para mostrar. Realiza una compra desde el catálogo.');
    return;
  }

  const factura = Factura.fromJSON(JSON.parse(crudo));
  view.render(factura);
}

iniciar();
