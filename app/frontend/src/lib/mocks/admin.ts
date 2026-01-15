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
    permisosAdmin: [
      { name: "admin.profiles.view", description: "Ver perfiles" },
      { name: "admin.parametrization.view", description: "Ver parametrización" },
      { name: "admin.provider_validate.view", description: "Validar proveedores" },
      { name: "admin.marketing.view", description: "Ver marketing" },
      { name: "admin.dashboard.view", description: "Ver dashboard" },
      { name: "admin.support.view", description: "Ver soporte" }
    ],
    permisosProveedor: [],
    usuarios: 2,
    activo: true,
  },
  {
    id: 2,
    nombre: "Administrador de Marketing",
    descripcion: "Gestión de campañas y promociones",
    permisosAdmin: [
      { name: "admin.dashboard.view", description: "Ver dashboard" },
      { name: "admin.marketing.view", description: "Ver marketing" }
    ],
    permisosProveedor: [],
    usuarios: 5,
    activo: true,
  },
  {
    id: 3,
    nombre: "Proveedor Completo",
    descripcion: "Acceso completo para proveedores",
    permisosAdmin: [],
    permisosProveedor: [
      { name: "provider.basic_data.view", description: "Ver datos básicos" },
      { name: "provider.commerces.view", description: "Ver sucursales" },
      { name: "provider.products.view", description: "Ver productos" },
      { name: "provider.my_account.view", description: "Ver mi cuenta" },
      { name: "provider.my_wallet.view", description: "Ver mi billetera" },
      { name: "provider.dashboard.view", description: "Ver dashboard" },
      { name: "provider.support.view", description: "Ver soporte" }
    ],
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
    barrio: "El Poblado",
    direccion: "Calle 10 # 40-20",
    perfil: "Proveedor Completo",
    estado: true,
    verificado: true,
    documentos: [],
    sucursales: [],
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
    barrio: "Laureles",
    direccion: "Carrera 70 # 45-10",
    perfil: "Proveedor Completo",
    estado: false,
    verificado: false,
    documentos: [],
    sucursales: [],
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
