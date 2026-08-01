"""
storage.py
-----------
Capa de persistencia basada en ARCHIVOS PLANOS (JSON).

Se define una clase base genérica `JsonRepository` que encapsula el detalle
de cómo se guardan y leen los datos en disco. Las clases concretas
(UsuarioRepository, ProductoRepository, etc.) heredan de ella, evidenciando:

- Encapsulamiento: el acceso al archivo (_load/_save) es privado/protegido,
  nadie fuera de la clase manipula el archivo directamente.
- Herencia: cada repositorio concreto extiende JsonRepository.
- Composición: los "Services" (capa de negocio) usan un repositorio por
  composición (tienen-un repositorio) en lugar de heredar de él.
"""

import json
import os
import threading
from functools import reduce
from typing import Generic, TypeVar, List, Optional, Dict, Any, Callable

T = TypeVar("T")

DATA_DIR = os.path.join(os.path.dirname(__file__), "data")


class JsonRepository(Generic[T]):
    """Repositorio genérico sobre un archivo JSON (lista de objetos)."""

    def __init__(self, filename: str, to_dict: Callable[[T], dict], from_dict: Callable[[dict], T]):
        self._path = os.path.join(DATA_DIR, filename)
        self._to_dict = to_dict
        self._from_dict = from_dict
        self._lock = threading.Lock()
        os.makedirs(DATA_DIR, exist_ok=True)
        if not os.path.exists(self._path):
            with open(self._path, "w", encoding="utf-8") as f:
                json.dump([], f)

    # ---- métodos "privados" de acceso a disco (encapsulamiento) ----
    def _load(self) -> List[dict]:
        with open(self._path, "r", encoding="utf-8") as f:
            return json.load(f)

    def _save(self, data: List[dict]) -> None:
        with open(self._path, "w", encoding="utf-8") as f:
            json.dump(data, f, indent=2, ensure_ascii=False)

    # ---- API pública CRUD ----
    def get_all(self) -> List[T]:
        return [self._from_dict(d) for d in self._load()]

    def get_by_id(self, entity_id: int) -> Optional[T]:
        data = self._load()
        found = next((d for d in data if d.get("id") == entity_id), None)
        return self._from_dict(found) if found else None

    def find(self, predicate: Callable[[dict], bool]) -> List[T]:
        """Búsqueda genérica usando una función (programación funcional: filter)."""
        return [self._from_dict(d) for d in filter(predicate, self._load())]

    def next_id(self) -> int:
        data = self._load()
        if not data:
            return 1
        # uso de reduce (programación funcional) para obtener el id máximo
        max_id = reduce(lambda acc, item: item.get("id", 0) if item.get("id", 0) > acc else acc, data, 0)
        return max_id + 1

    def create(self, entity: T) -> T:
        with self._lock:
            data = self._load()
            data.append(self._to_dict(entity))
            self._save(data)
        return entity

    def update(self, entity_id: int, updated_dict: Dict[str, Any]) -> Optional[T]:
        with self._lock:
            data = self._load()
            idx = next((i for i, d in enumerate(data) if d.get("id") == entity_id), None)
            if idx is None:
                return None
            data[idx].update(updated_dict)
            self._save(data)
            return self._from_dict(data[idx])

    def delete(self, entity_id: int) -> bool:
        with self._lock:
            data = self._load()
            new_data = [d for d in data if d.get("id") != entity_id]
            if len(new_data) == len(data):
                return False
            self._save(new_data)
            return True

    def replace_all(self, data: List[dict]) -> None:
        with self._lock:
            self._save(data)
