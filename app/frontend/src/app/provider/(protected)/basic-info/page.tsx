import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Datos Básicos | Panel Provider - Sumass",
  description: "Información básica del proveedor",
};

export default function BasicInfoPage() {
  return (
    <div className="p-6 md:p-8">
      <div className="mb-6">
        <h1 className="text-2xl md:text-3xl font-bold text-gray-900">
          Datos Básicos
        </h1>
        <p className="text-gray-600 mt-2">
          Información general de tu negocio
        </p>
      </div>

      <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8">
        <p className="text-gray-600">
          Aquí podrás gestionar nombre, descripción, categoría, horarios y otros datos básicos de tu negocio.
        </p>
      </div>
    </div>
  );
}
