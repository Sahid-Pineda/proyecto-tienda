export class CatalogoView {
  #contenedorPildoras;
  #contenedorGrid;
  #inputBusqueda;
  #onFiltrarCategoria;
  #onBuscar;
  #onAgregarAlCarrito;

  constructor({ onFiltrarCategoria, onBuscar, onAgregarAlCarrito }) {
    this.#contenedorPildoras = document.querySelector('[data-pildoras-categoria]');
    this.#contenedorGrid = document.querySelector('[data-grid-productos]');
    this.#inputBusqueda = document.querySelector('[data-input-busqueda]');
    this.#onFiltrarCategoria = onFiltrarCategoria;
    this.#onBuscar = onBuscar;
    this.#onAgregarAlCarrito = onAgregarAlCarrito;

    if (this.#inputBusqueda) {
      let temporizador;
      this.#inputBusqueda.addEventListener('input', (evento) => {
        clearTimeout(temporizador);
        const termino = evento.target.value;
        temporizador = setTimeout(() => this.#onBuscar(termino), 220);
      });
    }
  }

  renderCategorias(categorias, categoriaActivaId) {
    if (!this.#contenedorPildoras) return;
    this.#contenedorPildoras.innerHTML = '';

    const todas = this.#crearPildora('Todas', 'todas', categoriaActivaId === 'todas' || categoriaActivaId === null);
    this.#contenedorPildoras.appendChild(todas);

    categorias.forEach((categoria) => {
      const pildora = this.#crearPildora(
        categoria.nombre,
        categoria.id,
        Number(categoriaActivaId) === categoria.id
      );
      this.#contenedorPildoras.appendChild(pildora);
    });
  }

  #crearPildora(texto, valor, activa) {
    const boton = document.createElement('button');
    boton.type = 'button';
    boton.className = `pildora${activa ? ' activa' : ''}`;
    boton.textContent = texto;
    boton.addEventListener('click', () => this.#onFiltrarCategoria(valor));
    return boton;
  }

  renderProductos(productos) {
    if (!this.#contenedorGrid) return;
    this.#contenedorGrid.innerHTML = '';

    if (productos.length === 0) {
      const vacio = document.createElement('div');
      vacio.className = 'vacio';
      vacio.textContent = 'No encontramos artículos con ese criterio. Prueba con otro filtro o búsqueda.';
      this.#contenedorGrid.appendChild(vacio);
      return;
    }

    productos.forEach((producto) => {
      this.#contenedorGrid.appendChild(this.#crearTarjeta(producto));
    });
  }

  #crearTarjeta(producto) {
    const tarjeta = document.createElement('article');
    tarjeta.className = 'card-producto';

    tarjeta.innerHTML = `
      <span class="etiqueta-categoria"></span>
      <div class="imagen"><img alt=""></div>
      <div class="cuerpo">
        <h3></h3>
        <p class="descripcion-corta"></p>
        <div class="fila-precio">
          <span class="precio"></span>
          <span class="stock-tag"></span>
        </div>
        <button type="button" class="btn btn-primario btn-block">Agregar al carrito</button>
      </div>
    `;

    tarjeta.querySelector('.etiqueta-categoria').textContent = producto.categoriaNombre || 'Deportes';
    const img = tarjeta.querySelector('.imagen img');
    img.src = producto.imagenUrl;
    img.alt = producto.nombre;
    img.onerror = () => {
      img.onerror = null;
      img.src = 'assets/img/producto-placeholder.svg';
    };

    tarjeta.querySelector('h3').textContent = producto.nombre;
    tarjeta.querySelector('.descripcion-corta').textContent = producto.descripcion;
    tarjeta.querySelector('.precio').textContent = producto.precioFormateado;

    const stockTag = tarjeta.querySelector('.stock-tag');
    if (producto.disponible) {
      stockTag.textContent = `${producto.stock} en stock`;
    } else {
      stockTag.textContent = 'Agotado';
      stockTag.classList.add('agotado');
    }

    const boton = tarjeta.querySelector('button');
    boton.disabled = !producto.disponible;
    if (!producto.disponible) boton.textContent = 'Sin stock';
    boton.addEventListener('click', () => this.#onAgregarAlCarrito(producto, boton));

    return tarjeta;
  }

  mostrarConfirmacionAgregado(boton) {
    const textoOriginal = boton.textContent;
    boton.textContent = 'Agregado ✓';
    boton.disabled = true;
    setTimeout(() => {
      boton.textContent = textoOriginal;
      boton.disabled = false;
    }, 900);
  }

  mostrarError(mensaje) {
    if (!this.#contenedorGrid) return;
    this.#contenedorGrid.innerHTML = `<div class="vacio">${mensaje}</div>`;
  }
}
