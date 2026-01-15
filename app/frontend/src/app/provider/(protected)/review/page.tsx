import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Revisión | Panel Provider - Sumass",
  description: "Estado de revisión del proveedor",
};

export default function ReviewPage() {
  return (
    <div className="p-6 md:p-8">
      <div className="mb-6">
        <h1 className="text-2xl md:text-3xl font-bold text-gray-900">
          Revisión
        </h1>
        <p className="text-gray-600 mt-2">
          Estado de aprobación y observaciones
        </p>
      </div>

      <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8">
        <p className="text-gray-600">
          Aquí podrás ver el estado de revisión de tu perfil y cualquier observación del equipo de Sumass.
        </p>
      </div>
    </div>
  );
}
