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
  packItemOptions: Array<{ id: number; originalPrice: number, quantityAvailable: number }>;
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
        localErrors.quantityAvailable ?? fieldErrors["product.quantity_available"],
      branchId:
        localErrors.branchId ?? fieldErrors["commerce_branch_ids.0"] ?? fieldErrors["commerce_branches.0"],
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
    const maxQuantity = option?.quantityAvailable ?? Number.MAX_SAFE_INTEGER;
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
    mergedErrors,
    handleTogglePackItem,
    handlePackItemQuantityChange,
    handleSubmit,
  };
}
