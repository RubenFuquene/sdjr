import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Mi Billetera | Panel Provider - Sumass",
  description: "Gestión financiera del proveedor",
};

export default function WalletPage() {
  return (
    <div className="p-6 md:p-8">
      <div className="mb-6">
        <h1 className="text-2xl md:text-3xl font-bold text-gray-900">
          Mi Billetera
        </h1>
        <p className="text-gray-600 mt-2">
          Balance, transacciones y métodos de pago
        </p>
      </div>

      <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8">
        <p className="text-gray-600">
          Aquí podrás ver tu balance actual, historial de transacciones y configurar métodos de pago.
        </p>
      </div>
    </div>
  );
}
