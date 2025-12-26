import { ProfilesContent } from "@/components/admin/profiles-content";
import { mockProveedores, mockUsuarios, mockAdministradores } from "@/lib/mocks/admin";

export const metadata = {
  title: "Perfiles | Admin | Sumass",
};

/**
 * Página principal del dashboard administrativo
 * Vista Perfiles con 4 tabs: Perfiles, Proveedores, Usuarios, Administradores
 * 
 * - Perfiles: Se cargan desde API /api/v1/roles (implementado con useRoles hook)
 * - Proveedores, Usuarios, Administradores: Usan mocks temporalmente (pendientes endpoints)
 */
export default async function AdminDashboardPage() {
  // Perfiles se cargan desde API en ProfilesContent (Client Component con useRoles)
  // TODO: Implementar endpoints para proveedores, usuarios y administradores
  // const proveedores = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/providers`);
  // const usuarios = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/users`);
  // const administradores = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/administrators`);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-semibold text-[#1A1A1A]">Gestión de Perfiles</h1>
        <p className="text-sm text-[#6A6A6A] mt-1">
          Administra perfiles, proveedores, usuarios y administradores del sistema
        </p>
      </div>

      <ProfilesContent
        proveedores={mockProveedores}
        usuarios={mockUsuarios}
        administradores={mockAdministradores}
      />
    </div>
  );
}
