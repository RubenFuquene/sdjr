import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Productos | Panel Provider - Sumass",
  description: "Gestión de productos del proveedor",
};

export default function ProductsPage() {
  return (
    <div className="p-6 md:p-8">
      <div className="mb-6">
        <h1 className="text-2xl md:text-3xl font-bold text-gray-900">
          Productos
        </h1>
        <p className="text-gray-600 mt-2">
          Administra tu catálogo de productos
        </p>
      </div>

      <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8">
        <p className="text-gray-600">
          Aquí podrás crear, editar y gestionar todos los productos disponibles en tu negocio.
        </p>
      </div>
    </div>
  );
}
