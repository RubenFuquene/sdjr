"use client";

import { useMemo, useState } from "react";
import { toast } from "sonner";
import type { ProductFromAPI, ProductType } from "@/lib/api";
import { ProductsEmptyState } from "./products-empty-state";
import { ProductsGrid } from "./products-grid";
import { ProductsTabs } from "./products-tabs";

interface ProductsPageContentProps {
  products: ProductFromAPI[];
  loading: boolean;
  error: string | null;
  hasCommerce: boolean;
  hasProducts: boolean;
  onAddProduct?: () => void;
  onEditProduct?: (product: ProductFromAPI) => void;
  onDeleteProduct?: (product: ProductFromAPI) => void;
}

export function ProductsPageContent({
  products,
  loading,
  error,
  hasCommerce,
  hasProducts,
  onAddProduct,
  onEditProduct,
  onDeleteProduct,
}: ProductsPageContentProps) {
  const [activeTab, setActiveTab] = useState<ProductType>("single");

  const singleProducts = useMemo(
    () => products.filter((product) => product.product_type === "single"),
    [products]
  );

  const packageProducts = useMemo(
    () => products.filter((product) => product.product_type === "package"),
    [products]
  );

  const activeProducts = activeTab === "single" ? singleProducts : packageProducts;

  const handleAddProduct = () => {
    if (onAddProduct) {
      onAddProduct();
      return;
    }

    toast.info("Creación de productos disponible en la siguiente fase.");
  };

  const handleEditProduct = (product: ProductFromAPI) => {
    if (onEditProduct) {
      onEditProduct(product);
      return;
    }

    toast.info(`Editar producto \"${product.title}\" estará disponible pronto.`);
  };

  const handleDuplicateProduct = (product: ProductFromAPI) => {
    toast.info(`Duplicar producto \"${product.title}\" estará disponible pronto.`);
  };

  const handleDeleteProduct = (product: ProductFromAPI) => {
    if (onDeleteProduct) {
      onDeleteProduct(product);
      return;
    }

    toast.info(`Eliminar producto \"${product.title}\" estará disponible pronto.`);
  };

  if (loading) {
    return (
      <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8 border border-[#E0E0E0]">
        <p className="text-[#6A6A6A]">Cargando productos...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8 border border-red-200">
        <p className="text-red-600">{error}</p>
      </div>
    );
  }

  if (!hasCommerce || !hasProducts || products.length === 0) {
    return <ProductsEmptyState onAddProduct={handleAddProduct} />;
  }

  return (
    <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8 border border-[#E0E0E0] space-y-6">
      <ProductsTabs
        activeTab={activeTab}
        singleCount={singleProducts.length}
        packageCount={packageProducts.length}
        onTabChange={setActiveTab}
      />

      {activeProducts.length === 0 ? (
        <div
          role="tabpanel"
          id={`products-panel-${activeTab}`}
          aria-labelledby={`products-tab-${activeTab}`}
          className="rounded-[14px] border border-dashed border-[#E0E0E0] bg-[#F7F7F7] p-8 text-center"
        >
          <p className="text-[#6A6A6A]">
            No hay productos de tipo {activeTab === "single" ? "individual" : "pack"}.
          </p>
        </div>
      ) : (
        <div
          role="tabpanel"
          id={`products-panel-${activeTab}`}
          aria-labelledby={`products-tab-${activeTab}`}
        >
          <ProductsGrid
            products={activeProducts}
            onEditProduct={handleEditProduct}
            onDuplicateProduct={handleDuplicateProduct}
            onDeleteProduct={handleDeleteProduct}
          />
        </div>
      )}
    </div>
  );
}
