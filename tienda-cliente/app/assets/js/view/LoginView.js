export class LoginView {
  #form;
  #alerta;
  #onSubmit;

  constructor({ onSubmit }) {
    this.#form = document.querySelector('[data-form-login]');
    this.#alerta = document.querySelector('[data-alerta]');
    this.#onSubmit = onSubmit;

    this.#form?.addEventListener('submit', (evento) => {
      evento.preventDefault();
      this.limpiarAlerta();
      this.#onSubmit({
        correo: this.#form.correo.value.trim(),
        password: this.#form.password.value,
      });
    });
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
    if (boton) boton.disabled = bloqueado;
  }
}
