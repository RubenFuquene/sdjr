import { ProfilesContent } from "@/components/admin";
import { mockUsuarios, mockAdministradores } from "@/lib/mocks/admin";

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
 * - Proveedores, Usuarios, Administradores: Usan mocks temporalmente (pendientes endpoints)
 */
export default async function AdminDashboardPage() {
  // Perfiles se cargan desde API en RolesView (Client Component con useRoleManagement)
  // TODO: Implementar endpoints para proveedores, usuarios y administradores
  // const proveedores = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/commerces`);
  // const usuarios = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/users`);
  // const administradores = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/administrators`);

  return (
    <ProfilesContent
      usuarios={mockUsuarios}
      administradores={mockAdministradores}
    />
  );
}
