"use client";

import { useEffect, useMemo, useRef } from "react";
import type {
  ProductBranchAssignment,
  ProductFromAPI,
  ProviderProductFormFieldErrors,
  ProviderProductFormInput,
} from "@/types/products";
import { FormField, InputField, SelectField, Textarea } from "@/components/provider/ui";
import { useCommerceFiscalCodes, useProductCategories } from "@/hooks/index";
import type { ProductBranchOption } from "./product-form-modal";
import { ProductTypeToggle } from "./product-type-toggle";
import { ProductPackItemsSelector } from "./product-pack-items-selector";
import { ProductBranchAssignmentSelector } from "./product-branch-assignment-selector";
import { useProductFormState } from "./use-product-form-state";
import type { PackItemOption } from "./use-product-form-state";
import { parseDecimal } from "./product-form.utils";

export type ProductFormMode = "create" | "edit";

export interface ProductFormInitialData {
  id?: number;
  commerceId?: number;
  title?: string;
  description?: string | null;
  productType?: "single" | "package";
  productCategoryId?: number;
  fiscalCode?: string | null;
  originalPrice?: number;
  discountedPrice?: number | null;
  /** Asignación multi-sede, para ambos tipos de producto (SCRUM-361). */
  branches?: ProductBranchAssignment[];
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
  const {
    fiscalCodes,
    fiscalCodesLoading,
    fiscalCodesError,
  } = useCommerceFiscalCodes(initialData?.commerceId);

  // SCRUM-361: cada candidato trae su disponibilidad por sede (no un único
  // número global) — el armado de packs filtra y calcula por sede
  // seleccionada (Tarea 6.2).
  const packItemOptions: PackItemOption[] = useMemo(() => {
    return availableSingleProducts
      .filter((product) => (product.commerce_branches?.length ?? 0) > 0)
      .map((product) => ({
        id: product.id,
        title: product.title,
        originalPrice: product.original_price,
        discountedPrice: product.discounted_price,
        fiscalCode: product.fiscal_code ?? null,
        branches: (product.commerce_branches ?? []).map((branch) => ({
          branchId: branch.id,
          quantityAvailable: branch.quantity_available ?? 0,
          availableForPackaging: branch.available_for_packaging ?? 0,
        })),
      }));
  }, [availableSingleProducts]);

  const {
    title,
    setTitle,
    productType,
    handleProductTypeChange,
    productCategoryId,
    handleProductCategoryChange,
    fiscalCode,
    setFiscalCode,
    originalPrice,
    setOriginalPrice,
    discountedPrice,
    setDiscountedPrice,
    description,
    setDescription,
    branches,
    totalQuantityAcrossBranches,
    handleToggleBranch,
    handleBranchQuantityChange,
    handleBranchPublishedChange,
    packageItems,
    packItemCandidates,
    excludedProductIds,
    maxQuantityPerComponent,
    maxPacksByBranch,
    packOriginalPrice,
    packListPrice,
    reconciliationNotice,
    dismissReconciliationNotice,
    mergedErrors,
    handleTogglePackItem,
    handlePackItemQuantityChange,
    handleSubmit,
  } = useProductFormState({
    initialData,
    fieldErrors,
    packItemOptions,
    categories,
    fiscalCodeOptions: fiscalCodes,
    onSubmit,
  });

  useEffect(() => {
    titleRef.current?.focus();
  }, [mode, initialData]);

  const categoryOptions = useMemo(() => {
    return categories.map((category) => ({ value: String(category.id), label: category.name }));
  }, [categories]);

  const fiscalCodeOptions = useMemo(() => {
    return fiscalCodes.map((option) => ({ value: option.value, label: option.label }));
  }, [fiscalCodes]);

  const packItemSelectorOptions = useMemo(() => {
    return packItemCandidates.map((option) => {
      const selectedBranchIds = branches.map((b) => b.branchId);
      const minRawStock = selectedBranchIds.length
        ? Math.min(
            ...selectedBranchIds.map(
              (branchId) => option.branches.find((b) => b.branchId === branchId)?.quantityAvailable ?? 0
            )
          )
        : 0;

      return {
        id: option.id,
        title: option.title,
        originalPrice: option.originalPrice,
        discountedPrice: option.discountedPrice,
        quantityAvailable: minRawStock,
        availableForPackaging: maxQuantityPerComponent.get(option.id) ?? 0,
        isSelectable: option.isSelectable,
        disabledReason: option.disabledReason,
        wasAutoExcluded: excludedProductIds.has(option.id),
      };
    });
  }, [packItemCandidates, branches, maxQuantityPerComponent, excludedProductIds]);

  // Ticket derivado de SCRUM-361/323 (2026-08-04): descuento opcional que el
  // aliado le puso al pack completo, para prorratear P3 en vivo en el
  // selector de componentes.
  const packDiscountedPrice = productType === "package" ? parseDecimal(discountedPrice) : null;

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

      {/* SCRUM-370: un pack no elige categoría — se deriva de sus
          componentes por valor prorrateado. Preguntarla no tiene respuesta
          correcta cuando el pack mezcla varias categorías. */}
      {productType === "single" ? (
        <div className="space-y-2">
          <SelectField
            id="product-category"
            label="Categoría"
            required
            value={productCategoryId}
            onValueChange={handleProductCategoryChange}
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
      ) : null}

      {/* SCRUM-362: clasificación fiscal obligatoria por producto. El
          aliado nunca digita un porcentaje — el desplegable solo muestra
          las etiquetas que FiscalCodeResolver permite para este comercio
          (tipo de establecimiento + franquicia), y se sugiere sola al
          elegir categoría (handleProductCategoryChange). */}
      {productType === "single" ? (
        <div className="space-y-2">
          <SelectField
            id="product-fiscal-code"
            label="Clasificación fiscal"
            required
            value={fiscalCode}
            onValueChange={setFiscalCode}
            disabled={submitting || fiscalCodesLoading}
            error={mergedErrors.fiscalCode}
            describedBy={fiscalCodesError ? "product-fiscal-code-fetch-error" : undefined}
            placeholder={fiscalCodesLoading ? "Cargando clasificaciones..." : "Selecciona una clasificación"}
            options={fiscalCodeOptions}
            helperText="Esta clasificación determina el IVA o INC que se aplicará en tu factura. Si no estás seguro, selecciona &quot;No estoy seguro&quot; y el equipo de ÑAPA te contactará."
          />
          {fiscalCodesError ? (
            <p id="product-fiscal-code-fetch-error" className="text-sm text-red-600">
              {fiscalCodesError}
            </p>
          ) : null}
        </div>
      ) : null}

      {/* Ajuste 2026-08-04: referencia de precio de lista antes de la grilla
          principal — sin esto, "Precio" (que para un pack ya es la suma CON
          descuento) no tenía con qué compararse a simple vista; había que
          bajar hasta el resumen agregado del selector para encontrar el
          precio de lista. */}
      {productType === "package" && packageItems.length > 0 ? (
        <InputField
          id="product-list-price-reference"
          label="Precio de lista de los componentes (sin descuento)"
          type="number"
          value={String(packListPrice)}
          disabled
          readOnly
          helperText="Referencia: lo que costarían los productos del pack sin ningún descuento."
        />
      ) : null}

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <InputField
          id="product-price"
          label={productType === "package" ? "Precio con descuento (suma por producto)" : "Precio"}
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
              ? "Suma de los precios CON el descuento propio de cada producto. El desglose por producto está más abajo."
              : undefined
          }
          placeholder="0"
        />

        <InputField
          id="product-discounted-price"
          label="Precio con Descuento"
          required={productType !== "package"}
          type="number"
          min="0"
          step="0.01"
          value={discountedPrice}
          onChange={(event) => setDiscountedPrice(event.target.value)}
          disabled={submitting}
          error={mergedErrors.discountedPrice}
          placeholder={productType === "package" ? "Opcional" : "0"}
        />
      </div>

      {/* SCRUM-361 + ajuste 2026-08-03: los individuales usan el selector
          multi-sede de siempre. Los packs usan el mismo componente en modo
          de selección única (un pack vive en una sola sede) — se arma antes
          que los componentes, porque el catálogo de candidatos depende de
          qué sede se eligió. Para replicar un pack en otra sede se usa
          "Duplicar" y se cambia la sede ahí. */}
      <div className="space-y-3">
        <ProductBranchAssignmentSelector
          options={branchOptions}
          selectedItems={branches}
          disabled={submitting}
          error={mergedErrors.branches}
          onToggle={handleToggleBranch}
          onQuantityChange={handleBranchQuantityChange}
          onPublishedChange={handleBranchPublishedChange}
          heading={productType === "package" ? "Sede y compromiso del pack *" : "Sedes y disponibilidad *"}
          quantityLabel={productType === "package" ? "Packs" : "Cantidad"}
          maxHintByBranchId={productType === "package" ? maxPacksByBranch : undefined}
          selectionMode={productType === "package" ? "single" : "multiple"}
        />
        {productType === "single" ? (
          <p className="text-sm text-[#6A6A6A]">
            Total en todas las sedes:{" "}
            <span className="font-medium text-[#1A1A1A]">
              {totalQuantityAcrossBranches} unidades
            </span>
          </p>
        ) : null}
      </div>

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
        <div className="space-y-2">
          {reconciliationNotice ? (
            <div
              role="status"
              aria-live="polite"
              className="flex items-start justify-between gap-3 rounded-[14px] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
            >
              <span>{reconciliationNotice}</span>
              <button
                type="button"
                onClick={dismissReconciliationNotice}
                className="text-amber-700 hover:text-amber-900 underline shrink-0"
              >
                Entendido
              </button>
            </div>
          ) : null}

          {branches.length === 0 ? (
            <div className="rounded-[14px] border border-[#E0E0E0] bg-[#F7F7F7] p-4">
              <p className="text-sm text-[#6A6A6A]">
                Selecciona una sede arriba para ver qué productos puedes incluir en el pack.
              </p>
            </div>
          ) : (
            <ProductPackItemsSelector
              options={packItemSelectorOptions}
              selectedItems={packageItems}
              disabled={submitting}
              error={mergedErrors.packageItems}
              onToggle={handleTogglePackItem}
              onQuantityChange={handlePackItemQuantityChange}
              emptyStateMessage={`No hay productos individuales con inventario en ${
                branchOptions.find((b) => b.id === branches[0]?.branchId)?.name ?? "esta sede"
              }.`}
              packDiscountedPrice={packDiscountedPrice}
              packCeiling={packOriginalPrice}
              packListPrice={packListPrice}
            />
          )}
        </div>
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
