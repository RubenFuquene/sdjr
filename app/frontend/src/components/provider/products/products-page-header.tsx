"use client";

import { toast } from "sonner";
import { AddProductButton } from "./add-product-button";

interface ProductsPageHeaderProps {
  onAddProduct?: () => void;
  onAddPack?: () => void;
}

export function ProductsPageHeader({ onAddProduct, onAddPack }: ProductsPageHeaderProps) {
  const handleAddProduct = () => {
    if (onAddProduct) {
      onAddProduct();
      return;
    }

    toast.info("Creación de productos disponible en la siguiente fase.");
  };

  const handleAddPack = () => {
    if (onAddPack) {
      onAddPack();
      return;
    }

    toast.info("Creación de packs disponible en la siguiente fase.");
  };

  return (
    <div className="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
      <div>
        <h1 className="text-2xl md:text-3xl font-bold text-[#1A1A1A]">Productos</h1>
        <p className="text-[#6A6A6A] mt-2">Administra tu catálogo de productos</p>
      </div>

      <div className="flex w-full md:w-auto flex-col sm:flex-row gap-3">
        <AddProductButton
          label="Agregar Producto"
          className="w-full md:w-auto"
          onClick={handleAddProduct}
        />

        <button
          type="button"
          onClick={handleAddPack}
          className="inline-flex items-center justify-center h-[52px] px-6 rounded-[14px] bg-[#DDE8BB] hover:bg-[#C8D86D] text-[#4B236A] shadow-md transition-colors"
        >
          + Crear Pack
        </button>
      </div>
    </div>
  );
}
