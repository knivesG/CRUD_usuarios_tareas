export interface Tarea {
  id: number | null;
  titulo: string;
  descripcion: string;
  estado: string;
  entrega_fecha: string | null;
  usuario_id: number | null;
  usuario_nombre?: string;
}
