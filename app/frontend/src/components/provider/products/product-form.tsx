"use client";

import { useEffect, useMemo, useRef, useState } from "react";
import {
  getProductCategories,
  type ProductCategoryFromAPI,
  type ProductFromAPI,
  type ProductType,
} from "@/lib/api";
import type {
  ProviderProductFormFieldErrors,
  ProviderProductFormInput,
} from "@/hooks/provider/use-provider-product-form";
import type { ProductBranchOption } from "./product-form-modal";
import { ProductTypeToggle } from "./product-type-toggle";
import { ProductPackItemsSelector } from "./product-pack-items-selector";

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
  packageItemIds?: number[];
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

interface LocalFormErrors {
  title?: string;
  productCategoryId?: string;
  originalPrice?: string;
  discountedPrice?: string;
  quantityAvailable?: string;
  branchId?: string;
  packageItems?: string;
}

function parseDecimal(value: string): number | null {
  if (!value.trim()) {
    return null;
  }

  const parsed = Number(value.replace(",", "."));
  if (!Number.isFinite(parsed)) {
    return null;
  }

  return parsed;
}

function parseInteger(value: string): number | null {
  if (!value.trim()) {
    return null;
  }

  const parsed = Number(value);
  if (!Number.isInteger(parsed)) {
    return null;
  }

  return parsed;
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

  const [categories, setCategories] = useState<ProductCategoryFromAPI[]>([]);
  const [categoriesLoading, setCategoriesLoading] = useState(true);
  const [categoriesError, setCategoriesError] = useState<string | null>(null);

  const [title, setTitle] = useState(initialData?.title ?? "");
  const [productType, setProductType] = useState<ProductType>(
    initialData?.productType ?? "single"
  );
  const [productCategoryId, setProductCategoryId] = useState<string>(
    initialData?.productCategoryId ? String(initialData.productCategoryId) : ""
  );
  const [originalPrice, setOriginalPrice] = useState(
    initialData?.originalPrice !== undefined ? String(initialData.originalPrice) : ""
  );
  const [discountedPrice, setDiscountedPrice] = useState(
    initialData?.discountedPrice !== undefined && initialData?.discountedPrice !== null
      ? String(initialData.discountedPrice)
      : ""
  );
  const [quantityAvailable, setQuantityAvailable] = useState(
    initialData?.quantityAvailable !== undefined ? String(initialData.quantityAvailable) : ""
  );
  const [description, setDescription] = useState(initialData?.description ?? "");
  const [branchId, setBranchId] = useState<string>(
    initialData?.branchId ? String(initialData.branchId) : ""
  );
  const [packageItemIds, setPackageItemIds] = useState<number[]>(initialData?.packageItemIds ?? []);
  const [localErrors, setLocalErrors] = useState<LocalFormErrors>({});

  useEffect(() => {
    titleRef.current?.focus();
  }, [mode, initialData]);

  useEffect(() => {
    let isMounted = true;

    const fetchCategories = async () => {
      try {
        setCategoriesLoading(true);
        setCategoriesError(null);

        const response = await getProductCategories({ page: 1, perPage: 100, status: "1" });

        if (!isMounted) {
          return;
        }

        setCategories(response.data ?? []);
      } catch {
        if (!isMounted) {
          return;
        }

        setCategoriesError("No pudimos cargar las categorías de producto.");
        setCategories([]);
      } finally {
        if (isMounted) {
          setCategoriesLoading(false);
        }
      }
    };

    fetchCategories();

    return () => {
      isMounted = false;
    };
  }, []);

  useEffect(() => {
    setTitle(initialData?.title ?? "");
    setProductType(initialData?.productType ?? "single");
    setProductCategoryId(initialData?.productCategoryId ? String(initialData.productCategoryId) : "");
    setOriginalPrice(initialData?.originalPrice !== undefined ? String(initialData.originalPrice) : "");
    setDiscountedPrice(
      initialData?.discountedPrice !== undefined && initialData?.discountedPrice !== null
        ? String(initialData.discountedPrice)
        : ""
    );
    setQuantityAvailable(
      initialData?.quantityAvailable !== undefined ? String(initialData.quantityAvailable) : ""
    );
    setDescription(initialData?.description ?? "");
    setBranchId(initialData?.branchId ? String(initialData.branchId) : "");
    setPackageItemIds(initialData?.packageItemIds ?? []);
    setLocalErrors({});
  }, [initialData, mode]);

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

  const mergedErrors = useMemo(() => {
    return {
      title: localErrors.title ?? fieldErrors["product.title"],
      productCategoryId:
        localErrors.productCategoryId ?? fieldErrors["product.product_category_id"],
      originalPrice: localErrors.originalPrice ?? fieldErrors["product.original_price"],
      discountedPrice:
        localErrors.discountedPrice ?? fieldErrors["product.discounted_price"],
      quantityAvailable:
        localErrors.quantityAvailable ?? fieldErrors["product.quantity_available"],
      branchId:
        localErrors.branchId ?? fieldErrors["commerce_branch_ids.0"] ?? fieldErrors["commerce_branches.0"],
      packageItems:
        localErrors.packageItems ?? fieldErrors["package_items.0"] ?? fieldErrors["package_items"],
    };
  }, [fieldErrors, localErrors]);

  const validate = (): boolean => {
    const nextErrors: LocalFormErrors = {};

    if (!title.trim()) {
      nextErrors.title = "El nombre del producto es obligatorio.";
    }

    if (!productCategoryId) {
      nextErrors.productCategoryId = "Selecciona una categoría.";
    }

    const parsedOriginalPrice = parseDecimal(originalPrice);
    if (parsedOriginalPrice === null || parsedOriginalPrice < 0) {
      nextErrors.originalPrice = "Ingresa un precio válido.";
    }

    const parsedDiscountPrice = parseDecimal(discountedPrice);
    if (discountedPrice && (parsedDiscountPrice === null || parsedDiscountPrice < 0)) {
      nextErrors.discountedPrice = "Ingresa un descuento válido.";
    }

    if (
      parsedOriginalPrice !== null &&
      parsedDiscountPrice !== null &&
      parsedDiscountPrice > parsedOriginalPrice
    ) {
      nextErrors.discountedPrice = "El descuento no puede ser mayor al precio original.";
    }

    const parsedQuantityAvailable = parseInteger(quantityAvailable);
    if (parsedQuantityAvailable === null || parsedQuantityAvailable < 0) {
      nextErrors.quantityAvailable = "Ingresa una cantidad disponible válida.";
    }

    if (!branchId) {
      nextErrors.branchId = "Selecciona una sucursal.";
    }

    if (productType === "package" && packageItemIds.length === 0) {
      nextErrors.packageItems = "Selecciona al menos un producto para el pack.";
    }

    setLocalErrors(nextErrors);
    return Object.keys(nextErrors).length === 0;
  };

  const handleTogglePackItem = (productId: number) => {
    setPackageItemIds((previous) => {
      if (previous.includes(productId)) {
        return previous.filter((id) => id !== productId);
      }

      return [...previous, productId];
    });

    setLocalErrors((previous) => ({
      ...previous,
      packageItems: undefined,
    }));
  };

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (!validate()) {
      return;
    }

    const parsedOriginalPrice = parseDecimal(originalPrice);
    const parsedDiscountedPrice = parseDecimal(discountedPrice);
    const parsedQuantityAvailable = parseInteger(quantityAvailable);

    if (parsedOriginalPrice === null || parsedQuantityAvailable === null) {
      return;
    }

    await onSubmit({
      commerceId: initialData?.commerceId,
      title: title.trim(),
      productCategoryId: Number(productCategoryId),
      productType,
      originalPrice: parsedOriginalPrice,
      discountedPrice: parsedDiscountedPrice,
      quantityAvailable: parsedQuantityAvailable,
      quantityTotal: initialData?.quantityTotal ?? parsedQuantityAvailable,
      description: description.trim() ? description.trim() : null,
      branchId: Number(branchId),
      packageItemIds: productType === "package" ? packageItemIds : [],
      photos: [],
    });
  };

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
        onChange={(nextType) => {
          setProductType(nextType);

          if (nextType === "single") {
            setPackageItemIds([]);
          }
        }}
      />

      <div className="space-y-2">
        <label htmlFor="product-title" className="text-sm font-medium text-[#1A1A1A]">
          Nombre *
        </label>
        <input
          ref={titleRef}
          id="product-title"
          type="text"
          value={title}
          onChange={(event) => setTitle(event.target.value)}
          disabled={submitting}
          aria-invalid={Boolean(mergedErrors.title)}
          aria-describedby={mergedErrors.title ? "product-title-error" : undefined}
          placeholder="Ej: Hamburguesa Especial"
          className="w-full h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 text-[#1A1A1A] focus:outline-none focus:ring-2 focus:ring-[#4B236A]/30 disabled:opacity-60 disabled:cursor-not-allowed"
        />
        {mergedErrors.title ? (
          <p id="product-title-error" className="text-sm text-red-600">
            {mergedErrors.title}
          </p>
        ) : null}
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div className="space-y-2">
          <label htmlFor="product-category" className="text-sm font-medium text-[#1A1A1A]">
            Categoría *
          </label>
          <select
            id="product-category"
            value={productCategoryId}
            onChange={(event) => setProductCategoryId(event.target.value)}
            disabled={submitting || categoriesLoading}
            aria-invalid={Boolean(mergedErrors.productCategoryId)}
            aria-describedby={
              mergedErrors.productCategoryId
                ? "product-category-error"
                : categoriesError
                ? "product-category-fetch-error"
                : undefined
            }
            className="w-full h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 text-[#1A1A1A] bg-white focus:outline-none focus:ring-2 focus:ring-[#4B236A]/30 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <option value="">Selecciona una categoría</option>
            {categories.map((category) => (
              <option key={category.id} value={category.id}>
                {category.name}
              </option>
            ))}
          </select>
          {categoriesError ? (
            <p id="product-category-fetch-error" className="text-sm text-red-600">
              {categoriesError}
            </p>
          ) : null}
          {mergedErrors.productCategoryId ? (
            <p id="product-category-error" className="text-sm text-red-600">
              {mergedErrors.productCategoryId}
            </p>
          ) : null}
        </div>

        <div className="space-y-2">
          <label htmlFor="product-branch" className="text-sm font-medium text-[#1A1A1A]">
            Sucursal *
          </label>
          <select
            id="product-branch"
            value={branchId}
            onChange={(event) => setBranchId(event.target.value)}
            disabled={submitting || branchOptions.length === 0}
            aria-invalid={Boolean(mergedErrors.branchId)}
            aria-describedby={mergedErrors.branchId ? "product-branch-error" : undefined}
            className="w-full h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 text-[#1A1A1A] bg-white focus:outline-none focus:ring-2 focus:ring-[#4B236A]/30 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <option value="">Selecciona una sucursal</option>
            {branchOptions.map((branch) => (
              <option key={branch.id} value={branch.id}>
                {branch.name}
              </option>
            ))}
          </select>
          {branchOptions.length === 0 ? (
            <p className="text-sm text-[#6A6A6A]">No hay sucursales disponibles para seleccionar.</p>
          ) : null}
          {mergedErrors.branchId ? (
            <p id="product-branch-error" className="text-sm text-red-600">
              {mergedErrors.branchId}
            </p>
          ) : null}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div className="space-y-2">
          <label htmlFor="product-price" className="text-sm font-medium text-[#1A1A1A]">
            Precio *
          </label>
          <input
            id="product-price"
            type="number"
            min="0"
            step="0.01"
            value={originalPrice}
            onChange={(event) => setOriginalPrice(event.target.value)}
            disabled={submitting}
            aria-invalid={Boolean(mergedErrors.originalPrice)}
            aria-describedby={mergedErrors.originalPrice ? "product-price-error" : undefined}
            placeholder="0"
            className="w-full h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 text-[#1A1A1A] focus:outline-none focus:ring-2 focus:ring-[#4B236A]/30 disabled:opacity-60 disabled:cursor-not-allowed"
          />
          {mergedErrors.originalPrice ? (
            <p id="product-price-error" className="text-sm text-red-600">
              {mergedErrors.originalPrice}
            </p>
          ) : null}
        </div>

        <div className="space-y-2">
          <label htmlFor="product-discounted-price" className="text-sm font-medium text-[#1A1A1A]">
            Precio con Descuento
          </label>
          <input
            id="product-discounted-price"
            type="number"
            min="0"
            step="0.01"
            value={discountedPrice}
            onChange={(event) => setDiscountedPrice(event.target.value)}
            disabled={submitting}
            aria-invalid={Boolean(mergedErrors.discountedPrice)}
            aria-describedby={mergedErrors.discountedPrice ? "product-discounted-price-error" : undefined}
            placeholder="Opcional"
            className="w-full h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 text-[#1A1A1A] focus:outline-none focus:ring-2 focus:ring-[#4B236A]/30 disabled:opacity-60 disabled:cursor-not-allowed"
          />
          {mergedErrors.discountedPrice ? (
            <p id="product-discounted-price-error" className="text-sm text-red-600">
              {mergedErrors.discountedPrice}
            </p>
          ) : null}
        </div>
      </div>

      <div className="space-y-2">
        <label htmlFor="product-quantity" className="text-sm font-medium text-[#1A1A1A]">
          Cantidad Disponible *
        </label>
        <input
          id="product-quantity"
          type="number"
          min="0"
          step="1"
          value={quantityAvailable}
          onChange={(event) => setQuantityAvailable(event.target.value)}
          disabled={submitting}
          aria-invalid={Boolean(mergedErrors.quantityAvailable)}
          aria-describedby={mergedErrors.quantityAvailable ? "product-quantity-error" : undefined}
          placeholder="0"
          className="w-full h-[50px] rounded-[14px] border border-[#E0E0E0] px-4 text-[#1A1A1A] focus:outline-none focus:ring-2 focus:ring-[#4B236A]/30 disabled:opacity-60 disabled:cursor-not-allowed"
        />
        {mergedErrors.quantityAvailable ? (
          <p id="product-quantity-error" className="text-sm text-red-600">
            {mergedErrors.quantityAvailable}
          </p>
        ) : null}
      </div>

      <div className="space-y-2">
        <label htmlFor="product-description" className="text-sm font-medium text-[#1A1A1A]">
          Descripción
        </label>
        <textarea
          id="product-description"
          value={description}
          onChange={(event) => setDescription(event.target.value)}
          disabled={submitting}
          placeholder="Describe el producto..."
          rows={4}
          className="w-full rounded-[14px] border border-[#E0E0E0] px-4 py-3 text-[#1A1A1A] resize-none focus:outline-none focus:ring-2 focus:ring-[#4B236A]/30 disabled:opacity-60 disabled:cursor-not-allowed"
        />
      </div>

      {productType === "package" ? (
        <ProductPackItemsSelector
          options={packItemOptions}
          selectedIds={packageItemIds}
          disabled={submitting}
          error={mergedErrors.packageItems}
          onToggle={handleTogglePackItem}
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
