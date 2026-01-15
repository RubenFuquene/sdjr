import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Soporte | Panel Provider - Sumass",
  description: "Centro de ayuda y soporte para proveedores",
};

export default function SupportPage() {
  return (
    <div className="p-6 md:p-8">
      <div className="mb-6">
        <h1 className="text-2xl md:text-3xl font-bold text-gray-900">
          Soporte
        </h1>
        <p className="text-gray-600 mt-2">
          Centro de ayuda y asistencia
        </p>
      </div>

      <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8">
        <p className="text-gray-600">
          Aquí podrás acceder a la documentación de ayuda, preguntas frecuentes y contactar con soporte.
        </p>
      </div>
    </div>
  );
}
