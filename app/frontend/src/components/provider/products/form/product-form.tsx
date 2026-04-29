"use client";

import { useEffect, useMemo, useRef } from "react";
import type {
  ProductFromAPI,
  ProviderProductFormFieldErrors,
  ProviderProductFormInput,
} from "@/types/products";
import { FormField, InputField, SelectField, Textarea } from "@/components/provider/ui";
import { useProductCategories } from "@/hooks/index";
import type { ProductBranchOption } from "./product-form-modal";
import { ProductTypeToggle } from "./product-type-toggle";
import { ProductPackItemsSelector } from "./product-pack-items-selector";
import { useProductFormState } from "./use-product-form-state";

export type ProductFormMode = "create" | "edit";

export interface ProductFormInitialData {
  id?: number;
  commerceId?: number;
  title?: string;
  description?: string | null;
  productType?: "single" | "package";
  productCategoryId?: number;
  originalPrice?: number;
  discountedPrice?: number | null;
  quantityAvailable?: number;
  quantityTotal?: number;
  branchId?: number | null;
  packageItems?: Array<{ productId: number; quantity: number }>;
}

interface ProductFormProps {
  mode: ProductFormMode;
  initialData?: ProductFormInitialData | null;
  submitting: boolean;
  apiError: string | null;
  fieldErrors: ProviderProductFormFieldErrors;
  branchOptions: ProductBranchOption[];
  availableSingleProducts?: ProductFromAPI[];
  onCancel: () => void;
  onSubmit: (input: ProviderProductFormInput) => Promise<void>;
}

export function ProductForm({
  mode,
  initialData,
  submitting,
  apiError,
  fieldErrors,
  branchOptions,
  availableSingleProducts = [],
  onCancel,
  onSubmit,
}: ProductFormProps) {
  const titleRef = useRef<HTMLInputElement>(null);

  const { categories, categoriesLoading, categoriesError } = useProductCategories();

  const packItemOptions = useMemo(() => {
    return availableSingleProducts
      .filter((product) => product.quantity_available > 0)
      .map((product) => ({
        id: product.id,
        title: product.title,
        originalPrice: product.original_price,
        quantityAvailable: product.quantity_available,
      }));
  }, [availableSingleProducts]);

  const {
    title,
    setTitle,
    productType,
    handleProductTypeChange,
    productCategoryId,
    setProductCategoryId,
    originalPrice,
    setOriginalPrice,
    discountedPrice,
    setDiscountedPrice,
    quantityAvailable,
    setQuantityAvailable,
    description,
    setDescription,
    branchId,
    setBranchId,
    packageItems,
    mergedErrors,
    handleTogglePackItem,
    handlePackItemQuantityChange,
    handleSubmit,
  } = useProductFormState({
    initialData,
    fieldErrors,
    packItemOptions,
    onSubmit,
  });

  useEffect(() => {
    titleRef.current?.focus();
  }, [mode, initialData]);

  const categoryOptions = useMemo(() => {
    return categories.map((category) => ({ value: String(category.id), label: category.name }));
  }, [categories]);

  const branchSelectOptions = useMemo(() => {
    return branchOptions.map((branch) => ({ value: String(branch.id), label: branch.name }));
  }, [branchOptions]);

  return (
    <form
      onSubmit={handleSubmit}
      className="space-y-5"
      aria-describedby={apiError ? "product-form-api-error" : undefined}
    >
      {apiError ? (
        <div
          id="product-form-api-error"
          role="alert"
          className="rounded-[14px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"
        >
          {apiError}
        </div>
      ) : null}

      <ProductTypeToggle
        value={productType}
        disabled={submitting}
        onChange={handleProductTypeChange}
      />

      <InputField
        ref={titleRef}
        id="product-title"
        label="Nombre"
        required
        type="text"
        value={title}
        onChange={(event) => setTitle(event.target.value)}
        disabled={submitting}
        error={mergedErrors.title}
        placeholder="Ej: Hamburguesa Especial"
      />

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div className="space-y-2">
          <SelectField
            id="product-category"
            label="Categoría"
            required
            value={productCategoryId}
            onValueChange={setProductCategoryId}
            disabled={submitting || categoriesLoading}
            error={mergedErrors.productCategoryId}
            describedBy={categoriesError ? "product-category-fetch-error" : undefined}
            placeholder="Selecciona una categoría"
            options={categoryOptions}
          />
          {categoriesError ? (
            <p id="product-category-fetch-error" className="text-sm text-red-600">
              {categoriesError}
            </p>
          ) : null}
        </div>

        <SelectField
          id="product-branch"
          label="Sucursal"
          required
          value={branchId}
          onValueChange={setBranchId}
          disabled={submitting || branchOptions.length === 0}
          error={mergedErrors.branchId}
          helperText={
            branchOptions.length === 0 ? "No hay sucursales disponibles para seleccionar." : undefined
          }
          placeholder="Selecciona una sucursal"
          options={branchSelectOptions}
        />
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <InputField
          id="product-price"
          label="Precio"
          required
          type="number"
          min="0"
          step="0.01"
          value={originalPrice}
          onChange={(event) => setOriginalPrice(event.target.value)}
          disabled={submitting || productType === "package"}
          error={mergedErrors.originalPrice}
          helperText={
            productType === "package"
              ? "Se calcula automaticamente segun los productos seleccionados en el pack."
              : undefined
          }
          placeholder="0"
        />

        <InputField
          id="product-discounted-price"
          label="Precio con Descuento"
          type="number"
          min="0"
          step="0.01"
          value={discountedPrice}
          onChange={(event) => setDiscountedPrice(event.target.value)}
          disabled={submitting}
          error={mergedErrors.discountedPrice}
          placeholder="Opcional"
        />
      </div>

      <InputField
        id="product-quantity"
        label="Cantidad Disponible"
        required
        type="number"
        min="0"
        step="1"
        value={quantityAvailable}
        onChange={(event) => setQuantityAvailable(event.target.value)}
        disabled={submitting}
        error={mergedErrors.quantityAvailable}
        placeholder="0"
      />

      <FormField id="product-description" label="Descripción">
        <Textarea
          id="product-description"
          value={description}
          onChange={(event) => setDescription(event.target.value)}
          disabled={submitting}
          placeholder="Describe el producto..."
          rows={4}
          className="w-full rounded-[14px] border border-[#E0E0E0] px-4 py-3 text-[#1A1A1A] focus-visible:ring-2 focus-visible:ring-[#4B236A]/30 disabled:opacity-60 disabled:cursor-not-allowed"
        />
      </FormField>

      {productType === "package" ? (
        <ProductPackItemsSelector
          options={packItemOptions}
          selectedItems={packageItems}
          disabled={submitting}
          error={mergedErrors.packageItems}
          onToggle={handleTogglePackItem}
          onQuantityChange={handlePackItemQuantityChange}
        />
      ) : null}

      <div className="space-y-2">
        <p className="text-sm font-medium text-[#1A1A1A]">Fotos</p>
        <div className="rounded-[14px] border border-dashed border-[#E0E0E0] bg-[#F7F7F7] p-4">
          <p className="text-sm text-[#6A6A6A]">
            Carga de fotos disponible en la siguiente fase. En este MVP se mantiene placeholder.
          </p>
        </div>
      </div>

      <div className="pt-2 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
        <button
          type="button"
          onClick={onCancel}
          disabled={submitting}
          className="h-[50px] rounded-[14px] border border-[#E0E0E0] px-5 text-[#4B236A] hover:bg-[#F7F7F7] transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
        >
          Cancelar
        </button>
        <button
          type="submit"
          disabled={submitting || categoriesLoading || !!categoriesError}
          className="h-[52px] rounded-[14px] px-5 bg-[#4B236A] hover:bg-[#5D2B7D] text-white shadow-md transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
        >
          {submitting ? "Guardando..." : mode === "edit" ? "Guardar Cambios" : "Guardar Producto"}
        </button>
      </div>
    </form>
  );
}
