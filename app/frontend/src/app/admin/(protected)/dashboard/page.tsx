import { ProfilesContent } from "@/components/admin";
import { mockAdministradores } from "@/lib/mocks/admin";

// Forzar renderizado dinámico (página usa cookies para autenticación)
export const dynamic = 'force-dynamic';

export const metadata = {
  title: "Perfiles | Admin | Sumass",
};

/**
 * Página principal del dashboard administrativo
 * Vista Perfiles con 4 tabs: Perfiles, Proveedores, Usuarios, Administradores
 * 
 * - Perfiles: Se cargan desde API /api/v1/roles (implementado con useRoleManagement hook)
 * - Proveedores: Se cargan desde API /api/v1/commerces (implementado con useCommerceManagement hook)
 * - Usuarios: Se cargan desde UsersView (auto-contenido, pendiente hook useUserManagement)
 * - Administradores: Usan mocks temporalmente (pendientes endpoints)
 */
export default async function AdminDashboardPage() {
  // Perfiles, Proveedores y Usuarios se cargan desde API en sus respectivas vistas (Client Components con hooks)
  // TODO: Implementar endpoints para administradores
  // const administradores = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/administrators`);

  return (
    <ProfilesContent
      administradores={mockAdministradores}
    />
  );
}
