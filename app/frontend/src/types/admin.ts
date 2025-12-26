/**
 * Tipos para la gestión administrativa
 * Basado en diseños Figma para las 4 secciones: Perfiles, Proveedores, Usuarios, Administradores
 */

export interface Perfil {
  id: number;
  nombre: string;
  descripcion: string;
  permisosAdmin: string[];
  permisosProveedor: string[];
  usuarios: number;
  activo: boolean;
}

export interface Proveedor {
  id: number;
  nombreComercial: string;
  nit: string;
  representanteLegal: string;
  tipoEstablecimiento: string;
  telefono: string;
  email: string;
  departamento: string;
  ciudad: string;
  perfil: string;
  activo: boolean;
}

export interface Usuario {
  id: number;
  nombres: string;
  apellidos: string;
  celular: string;
  email: string;
  perfil: string;
  activo: boolean;
}

export interface Administrador {
  id: number;
  nombres: string;
  apellidos: string;
  correo: string;
  area: string;
  perfil: string;
  activo: boolean;
}

export type Vista = 'perfiles' | 'proveedores' | 'usuarios' | 'administradores';
