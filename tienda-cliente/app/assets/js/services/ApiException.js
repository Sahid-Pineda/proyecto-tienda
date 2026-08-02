export class ApiException extends Error {
  #statusCode;

  constructor(statusCode, detail) {
    super(detail);
    this.name = 'ApiException';
    this.#statusCode = statusCode;
  }

  get statusCode() {
    return this.#statusCode;
  }
}
