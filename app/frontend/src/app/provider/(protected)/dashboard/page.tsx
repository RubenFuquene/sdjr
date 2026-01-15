import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Dashboard | Panel Provider - Sumass",
  description: "Panel principal del proveedor",
};

export default function ProviderDashboardPage() {
  return (
    <div className="p-6 md:p-8">
      <div className="mb-6">
        <h1 className="text-2xl md:text-3xl font-bold text-gray-900">
          Dashboard
        </h1>
        <p className="text-gray-600 mt-2">
          Bienvenido a tu panel de proveedor
        </p>
      </div>

      <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8">
        <p className="text-gray-600">
          Aquí verás tus métricas principales, órdenes recientes y resumen de actividad.
        </p>
      </div>
    </div>
  );
}
