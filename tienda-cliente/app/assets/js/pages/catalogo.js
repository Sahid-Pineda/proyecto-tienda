import { ProductoService } from '../services/ProductoService.js';
import { CategoriaService } from '../services/CategoriaService.js';
import { CatalogoView } from '../views/CatalogoView.js';
import { Carrito } from '../models/Carrito.js';
import { Sesion } from '../util/Sesion.js';
import { Flash } from '../util/Flash.js';
import { aplicarPipelineCatalogo } from '../util/funcional.js';
import { inicializarNavbar } from '../util/Navbar.js';
import { ApiException } from '../services/ApiException.js';

class CatalogoApp {
  #productoService;
  #categoriaService;
  #view;
  #productos = [];
  #categoriaActiva = 'todas';
  #termino = '';

  constructor() {
    this.#productoService = new ProductoService();
    this.#categoriaService = new CategoriaService();
    this.#view = new CatalogoView({
      onFiltrarCategoria: (categoriaId) => this.filtrarPorCategoria(categoriaId),
      onBuscar: (termino) => this.buscar(termino),
      onAgregarAlCarrito: (producto, boton) => this.agregarAlCarrito(producto, boton),
    });
  }

  async iniciar() {
    await inicializarNavbar();
    try {
      const categorias = await this.#categoriaService.listar();
      const productos = await this.#productoService.listar(categorias);

      this.#productos = productos;
      this.#view.renderCategorias(categorias, this.#categoriaActiva);
      this.#renderConFiltros();
    } catch (error) {
      const mensaje =
        error instanceof ApiException
          ? `No se pudieron cargar los productos: ${error.message}`
          : 'No se pudo conectar con el backend. Verifica que la API esté corriendo.';
      this.#view.mostrarError(mensaje);
    }
  }

  filtrarPorCategoria(categoriaId) {
    this.#categoriaActiva = categoriaId;
    this.#renderConFiltros();
  }

  buscar(termino) {
    this.#termino = termino;
    this.#renderConFiltros();
  }

  #renderConFiltros() {
    const resultado = aplicarPipelineCatalogo(this.#productos, {
      categoriaId: this.#categoriaActiva,
      termino: this.#termino,
    });
    this.#view.renderProductos(resultado);
  }

  async agregarAlCarrito(producto, boton) {
    const cliente = Sesion.obtenerCliente();
    if (!cliente) {
      Flash.set('error', 'Inicia sesión para agregar artículos al carrito.');
      window.location.href = 'login.html';
      return;
    }

    boton.disabled = true;
    try {
      const carrito = new Carrito(cliente.id);
      await carrito.agregar(producto.id, 1);
      this.#view.mostrarConfirmacionAgregado(boton);
      await inicializarNavbar();
    } catch (error) {
      const mensaje = error instanceof ApiException ? error.message : error.message;
      alert(mensaje);
      boton.disabled = false;
    }
  }
}

new CatalogoApp().iniciar();
