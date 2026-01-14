import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Legal | Panel Provider - Sumass",
  description: "Documentación legal del proveedor",
};

export default function LegalPage() {
  return (
    <div className="p-6 md:p-8">
      <div className="mb-6">
        <h1 className="text-2xl md:text-3xl font-bold text-gray-900">
          Legal
        </h1>
        <p className="text-gray-600 mt-2">
          Documentos y permisos legales
        </p>
      </div>

      <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8">
        <p className="text-gray-600">
          Aquí podrás subir y gestionar los documentos legales requeridos para operar en la plataforma.
        </p>
      </div>
    </div>
  );
}
