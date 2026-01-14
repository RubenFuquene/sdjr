export default function ProviderLoading() {
  return (
    <div className="flex min-h-screen items-center justify-center">
      <div className="flex flex-col items-center gap-4">
        <div className="h-12 w-12 animate-spin rounded-full border-4 border-[#DDE8BB] border-t-[#4B236A]" />
        <p className="text-sm text-gray-600">Cargando...</p>
      </div>
    </div>
  );
}
