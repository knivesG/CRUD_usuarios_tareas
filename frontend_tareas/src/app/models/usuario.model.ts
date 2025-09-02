export interface Usuario {
  id?: number;
  usuario: string;
  nombre: string;
  fecha_nacimiento: string | null;
  fecha_creacion: string | null;
  email: string;
}
