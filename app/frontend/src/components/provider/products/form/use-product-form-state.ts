"use client";

import { useMemo, useState } from "react";
import type {
  ProductType,
  ProviderProductFormFieldErrors,
  ProviderProductFormInput,
} from "@/types/products";
import {
  buildProductFormSubmitInput,
  mapInitialDataToDraft,
  parseDecimal,
  parseInteger,
} from "./product-form.utils";
import {
  type ProductFormValidationErrors,
  validateProductForm,
} from "./product-form.validation";

const MAX_PACKS_ERROR_PATTERN =
  /^The requested quantity_available \((\d+)\) exceeds the maximum packs available given current stock \(max: (\d+)\)\.$/;

function translateQuantityAvailableError(message?: string): string | undefined {
  if (!message) {
    return message;
  }

  const match = message.match(MAX_PACKS_ERROR_PATTERN);
  if (!match) {
    return message;
  }

  const [, requested, max] = match;
  return `La cantidad de paquetes solicitada (${requested}) supera el máximo disponible según el stock actual (máx: ${max}).`;
}

type ProductFormInitialData = {
  commerceId?: number;
  quantityTotal?: number;
  title?: string;
  description?: string | null;
  productType?: "single" | "package";
  productCategoryId?: number;
  originalPrice?: number;
  discountedPrice?: number | null;
  quantityAvailable?: number;
  branchId?: number | null;
  packageItems?: Array<{ productId: number; quantity: number }>;
};

type UseProductFormStateParams = {
  initialData?: ProductFormInitialData | null;
  fieldErrors: ProviderProductFormFieldErrors;
  packItemOptions: Array<{
    id: number;
    originalPrice: number;
    quantityAvailable: number;
    availableForPackaging: number;
  }>;
  onSubmit: (input: ProviderProductFormInput) => Promise<void>;
};

export function useProductFormState({
  initialData,
  fieldErrors,
  packItemOptions,
  onSubmit,
}: UseProductFormStateParams) {
  const initialDraft = mapInitialDataToDraft(initialData);

  const [title, setTitle] = useState(initialDraft.title);
  const [productType, setProductType] = useState<ProductType>(initialDraft.productType);
  const [productCategoryId, setProductCategoryId] = useState(initialDraft.productCategoryId);
  const [originalPrice, setOriginalPrice] = useState(initialDraft.originalPrice);
  const [discountedPrice, setDiscountedPrice] = useState(initialDraft.discountedPrice);
  const [quantityAvailable, setQuantityAvailable] = useState(initialDraft.quantityAvailable);
  const [description, setDescription] = useState(initialDraft.description);
  const [branchId, setBranchId] = useState(initialDraft.branchId);
  const [packageItems, setPackageItems] = useState<Array<{ productId: number; quantity: number }>>(
    initialDraft.packageItems
  );
  const [localErrors, setLocalErrors] = useState<ProductFormValidationErrors>({});

  const mergedErrors = useMemo(() => {
    const packageItemsFieldError =
      Object.entries(fieldErrors).find(
        ([key]) => key === "package_items" || key.startsWith("package_items.")
      )?.[1] ?? undefined;

    return {
      title: localErrors.title ?? fieldErrors["product.title"],
      productCategoryId:
        localErrors.productCategoryId ?? fieldErrors["product.product_category_id"],
      originalPrice: localErrors.originalPrice ?? fieldErrors["product.original_price"],
      discountedPrice:
        localErrors.discountedPrice ?? fieldErrors["product.discounted_price"],
      quantityAvailable:
        localErrors.quantityAvailable ??
        translateQuantityAvailableError(fieldErrors["product.quantity_available"]),
      branchId: localErrors.branchId ?? fieldErrors["commerce_branch_ids.0"],
      packageItems: localErrors.packageItems ?? packageItemsFieldError,
    };
  }, [fieldErrors, localErrors]);

  const packOriginalPrice = useMemo(() => {
    const optionsById = new Map(packItemOptions.map((option) => [option.id, option]));
    const total = packageItems.reduce((accumulator, selected) => {
      const option = optionsById.get(selected.productId);

      if (!option) {
        return accumulator;
      }

      return accumulator + option.originalPrice * selected.quantity;
    }, 0);
    return Number(total.toFixed(2));
  }, [packItemOptions, packageItems]);

  const effectiveOriginalPrice =
    productType === "package"
      ? packageItems.length > 0
        ? String(packOriginalPrice)
        : ""
      : originalPrice;

  // En modo edición, available_for_packaging ya descuenta el compromiso actual de
  // este mismo pack sobre cada producto; lo sumamos de vuelta para reflejar cuánto
  // queda disponible si este pack ajusta su cantidad.
  const effectiveAvailableForPackaging = useMemo(() => {
    const originalQuantities = new Map(
      (initialData?.packageItems ?? []).map((item) => [item.productId, item.quantity])
    );
    const currentPackQuantity = initialData?.quantityAvailable ?? 0;

    const result = new Map<number, number>();
    packItemOptions.forEach((option) => {
      const originalPivotQuantity = originalQuantities.get(option.id) ?? 0;
      result.set(
        option.id,
        option.availableForPackaging + originalPivotQuantity * currentPackQuantity
      );
    });

    return result;
  }, [packItemOptions, initialData]);

  const maxPacks = useMemo(() => {
    if (productType !== "package" || packageItems.length === 0) {
      return undefined;
    }

    return packageItems.reduce((min, item) => {
      const available = effectiveAvailableForPackaging.get(item.productId) ?? 0;
      const possiblePacks = item.quantity > 0 ? Math.floor(available / item.quantity) : 0;
      return Math.min(min, possiblePacks);
    }, Number.MAX_SAFE_INTEGER);
  }, [productType, packageItems, effectiveAvailableForPackaging]);

  const validate = (): boolean => {
    const nextErrors = validateProductForm({
      title,
      productCategoryId,
      originalPrice: effectiveOriginalPrice,
      discountedPrice,
      quantityAvailable,
      branchId,
      productType,
      packageItems,
      maxPacks,
    });

    setLocalErrors(nextErrors);
    return Object.keys(nextErrors).length === 0;
  };

  const handleProductTypeChange = (nextType: ProductType) => {
    setProductType(nextType);

    if (nextType === "single") {
      setPackageItems([]);
    }
  };

  const handleTogglePackItem = (productId: number) => {
    setPackageItems((previous) => {
      const existing = previous.find((item) => item.productId === productId);

      if (existing) {
        return previous.filter((item) => item.productId !== productId);
      }

      return [...previous, { productId, quantity: 1 }];
    });

    setLocalErrors((previous) => ({
      ...previous,
      packageItems: undefined,
    }));
  };

  const handlePackItemQuantityChange = (productId: number, quantity: number) => {
    const option = packItemOptions.find((item) => item.id === productId);
    const maxQuantity =
      effectiveAvailableForPackaging.get(productId) ??
      option?.quantityAvailable ??
      Number.MAX_SAFE_INTEGER;
    const normalizedQuantity = Math.max(1, Math.min(quantity, maxQuantity));

    setPackageItems((previous) => {
      return previous.map((item) => {
        if (item.productId !== productId) {
          return item;
        }

        return {
          ...item,
          quantity: normalizedQuantity,
        };
      });
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

    const parsedOriginalPrice = parseDecimal(effectiveOriginalPrice);
    const parsedDiscountedPrice = parseDecimal(discountedPrice);
    const parsedQuantityAvailable = parseInteger(quantityAvailable);

    if (parsedOriginalPrice === null || parsedQuantityAvailable === null) {
      return;
    }

    await onSubmit(
      buildProductFormSubmitInput({
        commerceId: initialData?.commerceId,
        quantityTotal: initialData?.quantityTotal,
        title,
        productCategoryId,
        productType,
        originalPrice: parsedOriginalPrice,
        discountedPrice: parsedDiscountedPrice,
        quantityAvailable: parsedQuantityAvailable,
        description,
        branchId,
        packageItems,
      })
    );
  };

  return {
    title,
    setTitle,
    productType,
    handleProductTypeChange,
    productCategoryId,
    setProductCategoryId,
    originalPrice: effectiveOriginalPrice,
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
    maxPacks,
    mergedErrors,
    handleTogglePackItem,
    handlePackItemQuantityChange,
    handleSubmit,
  };
}
