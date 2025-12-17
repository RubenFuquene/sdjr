export const metadata = {
  title: "Dashboard | Admin | Sumass",
};

export default function AdminDashboardPage() {
  return (
    <main className="min-h-screen bg-white px-6 py-10 text-[var(--color-text)]">
      <div className="mx-auto flex max-w-4xl flex-col gap-4">
        <h1 className="text-2xl font-semibold">Dashboard (dummy)</h1>
        <p className="text-sm text-[var(--color-muted)]">
          Contenido provisional mientras se conecta la API real. Este stub existe para validar rutas protegidas.
        </p>
      </div>
    </main>
  );
}
