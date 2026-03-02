import { Package } from "lucide-react";
import { AddProductButton } from "./add-product-button";

interface ProductsEmptyStateProps {
  onAddProduct: () => void;
}

export function ProductsEmptyState({ onAddProduct }: ProductsEmptyStateProps) {
  return (
    <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8 border border-dashed border-[#E0E0E0]">
      <div className="flex flex-col items-center justify-center py-16 text-center">
        <Package className="w-16 h-16 text-[#6A6A6A] mb-4" />
        <h2 className="text-[#1A1A1A] font-semibold mb-2">No hay productos registrados</h2>
        <p className="text-[#6A6A6A] mb-6 max-w-md">
          Comienza agregando productos para construir tu catálogo y ofrecerlos a tus clientes.
        </p>

        <AddProductButton
          label="Agregar Primer Producto"
          ariaLabel="Agregar primer producto"
          onClick={onAddProduct}
        />
      </div>
    </div>
  );
}
