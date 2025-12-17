import { DashboardTabs } from "@/components/admin/dashboard-tabs";

export const metadata = {
  title: "Dashboard | Admin | Sumass",
};

export default function AdminDashboardPage() {
  // TODO: reemplazar dataset dummy por fetch Server Component al endpoint real y renderizar la tabla de perfiles.
  return (
    <div className="text-[var(--color-text)]">
      <section className="mx-auto flex max-w-6xl flex-col gap-4 px-1 py-2 md:px-2">
        <div className="flex flex-col gap-1">
          <h2 className="text-2xl font-semibold">Gestión de Perfiles</h2>
          <p className="text-sm text-[var(--color-muted)]">Administra roles, permisos y usuarios del sistema.</p>
        </div>

        <DashboardTabs />

        <div className="rounded-xl border border-dashed border-[var(--color-border)] bg-white px-4 py-8 text-center text-sm text-[var(--color-muted)]">
          Tabla dummy pendiente: aquí se renderizará la lista de perfiles con badges y acciones cuando avancemos al paso 4.
        </div>
      </section>
    </div>
  );
}
