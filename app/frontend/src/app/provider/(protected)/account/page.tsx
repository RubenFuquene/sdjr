import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Mi Cuenta | Panel Provider - Sumass",
  description: "Configuración de cuenta del proveedor",
};

export default function AccountPage() {
  return (
    <div className="p-6 md:p-8">
      <div className="mb-6">
        <h1 className="text-2xl md:text-3xl font-bold text-gray-900">
          Mi Cuenta
        </h1>
        <p className="text-gray-600 mt-2">
          Configuración de tu perfil y preferencias
        </p>
      </div>

      <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8">
        <p className="text-gray-600">
          Aquí podrás actualizar tu información personal, cambiar contraseña y gestionar preferencias.
        </p>
      </div>
    </div>
  );
}
