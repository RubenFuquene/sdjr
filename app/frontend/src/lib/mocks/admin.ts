import { Perfil, Proveedor, Usuario, Administrador } from "@/types/admin";

/**
 * Datos mock para desarrollo
 * TODO: Reemplazar con fetch a API Laravel cuando esté disponible
 */

export const mockPerfiles: Perfil[] = [
  {
    id: 1,
    nombre: "Super Administrador",
    descripcion: "Acceso completo a todas las funcionalidades",
    permisosAdmin: ["Perfiles", "Parametrización", "Validación de Proveedores", "Marketing", "Dashboard", "Soporte"],
    permisosProveedor: [],
    usuarios: 2,
    activo: true,
  },
  {
    id: 2,
    nombre: "Administrador de Marketing",
    descripcion: "Gestión de campañas y promociones",
    permisosAdmin: ["Dashboard", "Marketing"],
    permisosProveedor: [],
    usuarios: 5,
    activo: true,
  },
  {
    id: 3,
    nombre: "Proveedor Completo",
    descripcion: "Acceso completo para proveedores",
    permisosAdmin: [],
    permisosProveedor: ["Datos Básicos", "Sucursales", "Productos", "Mi Cuenta", "Mi Billetera", "Dashboard", "Soporte"],
    usuarios: 15,
    activo: true,
  },
];

export const mockProveedores: Proveedor[] = [
  {
    id: 1,
    nombreComercial: "Restaurante El Buen Sabor",
    nit: "900123456-7",
    representanteLegal: "Juan Pérez González",
    tipoEstablecimiento: "Restaurante",
    telefono: "+57 300 123 4567",
    email: "contacto@buensabor.com",
    departamento: "Antioquia",
    ciudad: "Medellín",
    perfil: "Proveedor Completo",
    activo: true,
  },
  {
    id: 2,
    nombreComercial: "Cafetería Aroma",
    nit: "800987654-3",
    representanteLegal: "María García Silva",
    tipoEstablecimiento: "Cafetería",
    telefono: "+57 310 987 6543",
    email: "info@cafeteriaaroma.com",
    departamento: "Antioquia",
    ciudad: "Medellín",
    perfil: "Proveedor Completo",
    activo: false,
  },
];

export const mockUsuarios: Usuario[] = [
  {
    id: 1,
    nombres: "Carlos",
    apellidos: "Ramírez López",
    celular: "+57 315 555 1234",
    email: "carlos.ramirez@email.com",
    perfil: "Usuario Premium",
    activo: true,
  },
  {
    id: 2,
    nombres: "Ana",
    apellidos: "Martínez Torres",
    celular: "+57 320 555 5678",
    email: "ana.martinez@email.com",
    perfil: "Usuario Básico",
    activo: true,
  },
];

export const mockAdministradores: Administrador[] = [
  {
    id: 1,
    nombres: "Roberto",
    apellidos: "Gómez Ruiz",
    correo: "roberto.gomez@sumass.com",
    area: "Tecnología",
    perfil: "Super Administrador",
    activo: true,
  },
  {
    id: 2,
    nombres: "Laura",
    apellidos: "Fernández Castro",
    correo: "laura.fernandez@sumass.com",
    area: "Marketing",
    perfil: "Administrador de Marketing",
    activo: true,
  },
];
