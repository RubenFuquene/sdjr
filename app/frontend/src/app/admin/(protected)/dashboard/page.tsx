import { ProfilesContent } from "@/components/admin/profiles-content";
import { mockPerfiles, mockProveedores, mockUsuarios, mockAdministradores } from "@/lib/mocks/admin";

export const metadata = {
  title: "Perfiles | Admin | Sumass",
};

/**
 * Página principal del dashboard administrativo
 * Vista Perfiles con 4 tabs: Perfiles, Proveedores, Usuarios, Administradores
 * Basado en diseño Figma - Perfiles.tsx
 */
export default async function AdminDashboardPage() {
  // TODO: Reemplazar mocks por fetch a API Laravel
  // const perfiles = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/admin/profiles`);
  // const proveedores = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/admin/providers`);
  // const usuarios = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/admin/users`);
  // const administradores = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/admin/administrators`);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-semibold text-[#1A1A1A]">Gestión de Perfiles</h1>
        <p className="text-sm text-[#6A6A6A] mt-1">
          Administra perfiles, proveedores, usuarios y administradores del sistema
        </p>
      </div>

      <ProfilesContent
        perfiles={mockPerfiles}
        proveedores={mockProveedores}
        usuarios={mockUsuarios}
        administradores={mockAdministradores}
      />
    </div>
  );
}
