"use client";

import type { ProductFromAPI } from "@/lib/api";
import { ProductCard } from "./product-card";

interface ProductsGridProps {
  products: ProductFromAPI[];
  onEditProduct?: (product: ProductFromAPI) => void;
  onDuplicateProduct?: (product: ProductFromAPI) => void;
  onDeleteProduct?: (product: ProductFromAPI) => void;
}

export function ProductsGrid({
  products,
  onEditProduct,
  onDuplicateProduct,
  onDeleteProduct,
}: ProductsGridProps) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {products.map((product) => (
        <ProductCard
          key={product.id}
          product={product}
          onEdit={onEditProduct}
          onDuplicate={onDuplicateProduct}
          onDelete={onDeleteProduct}
        />
      ))}
    </div>
  );
}
