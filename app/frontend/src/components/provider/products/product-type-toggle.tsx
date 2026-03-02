"use client";

import type { ProductType } from "@/lib/api";

interface ProductTypeToggleProps {
  value: ProductType;
  disabled?: boolean;
  onChange: (next: ProductType) => void;
}

export function ProductTypeToggle({ value, disabled = false, onChange }: ProductTypeToggleProps) {
  return (
    <div className="space-y-2">
      <p id="product-type-label" className="text-sm font-medium text-[#1A1A1A]">Tipo de Producto *</p>
      <div
        role="radiogroup"
        aria-labelledby="product-type-label"
        className="grid grid-cols-2 gap-2 rounded-[14px] bg-[#F7F7F7] p-1"
      >
        <button
          type="button"
          role="radio"
          aria-checked={value === "single"}
          disabled={disabled}
          onClick={() => onChange("single")}
          className={`h-[44px] rounded-[12px] text-sm font-medium transition-colors ${
            value === "single"
              ? "bg-white text-[#4B236A] shadow-sm"
              : "text-[#6A6A6A] hover:text-[#1A1A1A]"
          } disabled:opacity-60 disabled:cursor-not-allowed`}
        >
          Producto
        </button>
        <button
          type="button"
          role="radio"
          aria-checked={value === "package"}
          disabled={disabled}
          onClick={() => onChange("package")}
          className={`h-[44px] rounded-[12px] text-sm font-medium transition-colors ${
            value === "package"
              ? "bg-white text-[#4B236A] shadow-sm"
              : "text-[#6A6A6A] hover:text-[#1A1A1A]"
          } disabled:opacity-60 disabled:cursor-not-allowed`}
        >
          Pack
        </button>
      </div>
    </div>
  );
}
