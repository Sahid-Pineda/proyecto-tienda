export class RegistroView {
  #form;
  #alerta;
  #onSubmit;

  constructor({ onSubmit }) {
    this.#form = document.querySelector('[data-form-registro]');
    this.#alerta = document.querySelector('[data-alerta]');
    this.#onSubmit = onSubmit;

    this.#form?.addEventListener('submit', (evento) => {
      evento.preventDefault();
      this.limpiarAlerta();
      this.#onSubmit({
        nombre: this.#form.nombre.value.trim(),
        correo: this.#form.correo.value.trim(),
        password: this.#form.password.value,
        direccion: this.#form.direccion.value.trim(),
      });
    });
  }

  mostrarErrores(errores) {
    if (!this.#alerta) return;
    this.#alerta.className = 'alerta alerta-error';
    this.#alerta.innerHTML =
      errores.length === 1
        ? errores[0]
        : `<ul class="lista-errores">${errores.map((e) => `<li>${e}</li>`).join('')}</ul>`;
    this.#alerta.hidden = false;
  }

  limpiarAlerta() {
    if (!this.#alerta) return;
    this.#alerta.hidden = true;
  }

  bloquearBoton(bloqueado) {
    const boton = this.#form?.querySelector('button[type="submit"]');
    if (boton) boton.disabled = bloqueado;
  }
}
