"use client";

import Image from "next/image";
import { Copy, Edit, Image as ImageIcon, Package, Trash2 } from "lucide-react";
import type { ProductFromAPI } from "@/lib/api";

interface ProductCardProps {
  product: ProductFromAPI;
  onEdit?: (product: ProductFromAPI) => void;
  onDuplicate?: (product: ProductFromAPI) => void;
  onDelete?: (product: ProductFromAPI) => void;
}

function formatCurrency(value: number): string {
  return `$${value.toLocaleString("es-CO")}`;
}

function isAbsoluteUrl(value: string): boolean {
  return /^https?:\/\//i.test(value);
}

function getCoverImageUrl(product: ProductFromAPI): string | null {
  const firstPhoto = product.photos?.[0];

  if (!firstPhoto) {
    return null;
  }

  if (firstPhoto.presigned_url && isAbsoluteUrl(firstPhoto.presigned_url)) {
    return firstPhoto.presigned_url;
  }

  if (firstPhoto.file_path && isAbsoluteUrl(firstPhoto.file_path)) {
    return firstPhoto.file_path;
  }

  return null;
}

export function ProductCard({ product, onEdit, onDuplicate, onDelete }: ProductCardProps) {
  const coverImageUrl = getCoverImageUrl(product);
  const hasDiscount =
    product.discounted_price !== null && product.discounted_price < product.original_price;

  const discountPercentage = hasDiscount
    ? Math.round(
        ((product.original_price - (product.discounted_price ?? product.original_price)) /
          product.original_price) *
          100
      )
    : 0;

  return (
    <article className="overflow-hidden rounded-[18px] border border-[#E0E0E0] bg-white shadow-sm hover:shadow-lg transition-shadow">
      <div className="h-48 bg-[#F7F7F7] relative">
        {coverImageUrl ? (
          <Image
            src={coverImageUrl}
            alt={product.title}
            fill
            className="object-cover"
            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
          />
        ) : (
          <div className="w-full h-full flex items-center justify-center">
            <ImageIcon className="w-12 h-12 text-[#6A6A6A]" />
          </div>
        )}

        {hasDiscount && (
          <div className="absolute top-2 right-2 rounded-full bg-red-500 px-3 py-1 text-sm text-white">
            -{discountPercentage}%
          </div>
        )}
      </div>

      <div className="p-5 space-y-3">
        <div className="flex items-start justify-between gap-3">
          <div>
            <h3 className="text-[#1A1A1A] font-semibold text-lg line-clamp-1">{product.title}</h3>
            <p className="text-sm text-[#6A6A6A] capitalize">
              {product.product_type === "package" ? "Pack" : "Producto"}
            </p>
          </div>

          <div className="text-right">
            {hasDiscount ? (
              <>
                <p className="text-sm text-[#6A6A6A] line-through">
                  {formatCurrency(product.original_price)}
                </p>
                <p className="text-[#4B236A] font-semibold">
                  {formatCurrency(product.discounted_price ?? product.original_price)}
                </p>
              </>
            ) : (
              <p className="text-[#1A1A1A] font-semibold">{formatCurrency(product.original_price)}</p>
            )}
          </div>
        </div>

        <div className="space-y-2 text-sm text-[#6A6A6A]">
          <div className="flex items-center justify-between gap-2">
            <span>Disponible:</span>
            <span className={product.quantity_available > 0 ? "text-green-600" : "text-red-600"}>
              {product.quantity_available} unidades
            </span>
          </div>

          <div className="flex items-center justify-between gap-2">
            <span>Tipo:</span>
            <span className="text-[#1A1A1A] inline-flex items-center gap-1">
              <Package className="w-4 h-4 text-[#4B236A]" />
              {product.product_type === "package" ? "Pack" : "Individual"}
            </span>
          </div>
        </div>

        <div className="flex gap-2 pt-1">
          <button
            type="button"
            onClick={() => onEdit?.(product)}
            className="flex-1 h-[42px] rounded-[14px] border border-[#E0E0E0] text-[#1A1A1A] hover:bg-[#F7F7F7] transition-colors inline-flex items-center justify-center gap-2"
          >
            <Edit className="w-4 h-4" />
            Editar
          </button>
          <button
            type="button"
            onClick={() => onDuplicate?.(product)}
            className="h-[42px] w-[42px] rounded-[14px] border border-[#E0E0E0] text-[#4B236A] hover:bg-[#F7F7F7] transition-colors inline-flex items-center justify-center"
            aria-label={`Duplicar producto ${product.title}`}
          >
            <Copy className="w-4 h-4" />
          </button>
          <button
            type="button"
            onClick={() => onDelete?.(product)}
            className="h-[42px] w-[42px] rounded-[14px] border border-[#E0E0E0] text-red-600 hover:bg-red-50 transition-colors inline-flex items-center justify-center"
            aria-label={`Eliminar producto ${product.title}`}
          >
            <Trash2 className="w-4 h-4" />
          </button>
        </div>
      </div>
    </article>
  );
}
