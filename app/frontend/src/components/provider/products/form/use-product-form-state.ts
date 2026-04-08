"use client";

import { useEffect, useMemo, useState } from "react";
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

type ProductFormMode = "create" | "edit";

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
  packageItemIds?: number[];
};

type UseProductFormStateParams = {
  mode: ProductFormMode;
  initialData?: ProductFormInitialData | null;
  fieldErrors: ProviderProductFormFieldErrors;
  packItemOptions: Array<{ id: number; originalPrice: number }>;
  onSubmit: (input: ProviderProductFormInput) => Promise<void>;
};

export function useProductFormState({
  mode,
  initialData,
  fieldErrors,
  packItemOptions,
  onSubmit,
}: UseProductFormStateParams) {
  const initialDraft = useMemo(() => mapInitialDataToDraft(initialData), [initialData]);

  const [title, setTitle] = useState(initialDraft.title);
  const [productType, setProductType] = useState<ProductType>(initialDraft.productType);
  const [productCategoryId, setProductCategoryId] = useState(initialDraft.productCategoryId);
  const [originalPrice, setOriginalPrice] = useState(initialDraft.originalPrice);
  const [discountedPrice, setDiscountedPrice] = useState(initialDraft.discountedPrice);
  const [quantityAvailable, setQuantityAvailable] = useState(initialDraft.quantityAvailable);
  const [description, setDescription] = useState(initialDraft.description);
  const [branchId, setBranchId] = useState(initialDraft.branchId);
  const [packageItemIds, setPackageItemIds] = useState<number[]>(initialDraft.packageItemIds);
  const [localErrors, setLocalErrors] = useState<ProductFormValidationErrors>({});

  useEffect(() => {
    const nextDraft = mapInitialDataToDraft(initialData);
    setTitle(nextDraft.title);
    setProductType(nextDraft.productType);
    setProductCategoryId(nextDraft.productCategoryId);
    setOriginalPrice(nextDraft.originalPrice);
    setDiscountedPrice(nextDraft.discountedPrice);
    setQuantityAvailable(nextDraft.quantityAvailable);
    setDescription(nextDraft.description);
    setBranchId(nextDraft.branchId);
    setPackageItemIds(nextDraft.packageItemIds);
    setLocalErrors({});
  }, [initialData, mode]);

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

  const packOriginalPrice = useMemo(() => {
    const selectedProducts = packItemOptions.filter((option) => packageItemIds.includes(option.id));
    const total = selectedProducts.reduce((accumulator, option) => accumulator + option.originalPrice, 0);
    return Number(total.toFixed(2));
  }, [packItemOptions, packageItemIds]);

  useEffect(() => {
    if (productType !== "package") {
      return;
    }

    const nextValue = packageItemIds.length > 0 ? String(packOriginalPrice) : "";
    setOriginalPrice(nextValue);
  }, [packOriginalPrice, packageItemIds.length, productType]);

  const validate = (): boolean => {
    const nextErrors = validateProductForm({
      title,
      productCategoryId,
      originalPrice,
      discountedPrice,
      quantityAvailable,
      branchId,
      productType,
      packageItemIds,
    });

    setLocalErrors(nextErrors);
    return Object.keys(nextErrors).length === 0;
  };

  const handleProductTypeChange = (nextType: ProductType) => {
    setProductType(nextType);

    if (nextType === "single") {
      setPackageItemIds([]);
    }
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
        packageItemIds,
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
    packageItemIds,
    mergedErrors,
    handleTogglePackItem,
    handleSubmit,
  };
}
