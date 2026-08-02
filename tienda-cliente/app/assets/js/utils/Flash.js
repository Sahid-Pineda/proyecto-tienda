export class Flash {
  static set(tipo, mensaje) {
    sessionStorage.setItem('flash', JSON.stringify({ tipo, mensaje }));
  }

  static take() {
    const valor = sessionStorage.getItem('flash');
    if (!valor) return null;
    sessionStorage.removeItem('flash');
    return JSON.parse(valor);
  }

  static renderInto(elemento) {
    const flash = Flash.take();
    if (!flash || !elemento) return;
    const div = document.createElement('div');
    div.className = `alerta alerta-${flash.tipo === 'success' ? 'exito' : 'error'}`;
    div.textContent = flash.mensaje;
    elemento.appendChild(div);
  }
}
