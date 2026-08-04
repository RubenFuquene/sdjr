"use client";

import { useMemo, useState } from "react";
import type {
  ProductBranchAssignment,
  ProductType,
  ProviderProductFormFieldErrors,
  ProviderProductFormInput,
} from "@/types/products";
import {
  buildProductFormSubmitInput,
  mapInitialDataToDraft,
  parseDecimal,
} from "./product-form.utils";
import {
  type ProductFormValidationErrors,
  validateProductForm,
} from "./product-form.validation";

const MAX_PACKS_ERROR_PATTERN =
  /^The requested quantity_available \((\d+)\) exceeds the maximum packs available in this branch given current stock \(max: (\d+)\)\.$/;

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
  title?: string;
  description?: string | null;
  productType?: "single" | "package";
  productCategoryId?: number;
  originalPrice?: number;
  discountedPrice?: number | null;
  branches?: ProductBranchAssignment[];
  packageItems?: Array<{ productId: number; quantity: number }>;
};

/** SCRUM-361, Tarea 6: disponibilidad de un componente candidato, por sede. */
export type PackItemOptionBranch = {
  branchId: number;
  /** Stock crudo asignado en esa sede — "asignado con stock" (SCRUM-323), no capacidad recortada. */
  quantityAvailable: number;
  /** Stock libre para comprometer en packs en esa sede. */
  availableForPackaging: number;
};

export type PackItemOption = {
  id: number;
  title: string;
  originalPrice: number;
  discountedPrice?: number | null;
  branches: PackItemOptionBranch[];
};

type UseProductFormStateParams = {
  initialData?: ProductFormInitialData | null;
  fieldErrors: ProviderProductFormFieldErrors;
  packItemOptions: PackItemOption[];
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
  const [description, setDescription] = useState(initialDraft.description);
  const [branches, setBranches] = useState<ProductBranchAssignment[]>(initialDraft.branches);
  const [packageItems, setPackageItems] = useState<Array<{ productId: number; quantity: number }>>(
    initialDraft.packageItems
  );
  const [localErrors, setLocalErrors] = useState<ProductFormValidationErrors>({});
  // SCRUM-361, Tarea 6.3: aviso de reconciliación al cambiar de sede — región
  // viva para que se anuncie, no solo aparezca visualmente (wcag.md).
  const [reconciliationNotice, setReconciliationNotice] = useState<string | null>(null);

  const totalQuantityAcrossBranches = useMemo(
    () => branches.reduce((sum, branch) => sum + branch.quantityAvailable, 0),
    [branches]
  );

  const mergedErrors = useMemo(() => {
    const packageItemsFieldError =
      Object.entries(fieldErrors).find(
        ([key]) => key === "package_items" || key.startsWith("package_items.")
      )?.[1] ?? undefined;
    const branchesFieldError =
      Object.entries(fieldErrors).find(
        ([key]) => key === "commerce_branches" || key.startsWith("commerce_branches.")
      )?.[1] ?? undefined;

    return {
      title: localErrors.title ?? fieldErrors["product.title"],
      productCategoryId:
        localErrors.productCategoryId ?? fieldErrors["product.product_category_id"],
      originalPrice: localErrors.originalPrice ?? fieldErrors["product.original_price"],
      discountedPrice:
        localErrors.discountedPrice ?? fieldErrors["product.discounted_price"],
      branches:
        localErrors.branches ??
        translateQuantityAvailableError(branchesFieldError),
      packageItems: localErrors.packageItems ?? packageItemsFieldError,
    };
  }, [fieldErrors, localErrors]);

  // Ajuste 2026-08-04 (ticket derivado de SCRUM-361/323): el techo del pack
  // suma los precios YA CON DESCUENTO de los componentes, no sus precios de
  // lista — de lo contrario un pack sin descuento propio podía costar más
  // que comprar las partes sueltas.
  const packOriginalPrice = useMemo(() => {
    const optionsById = new Map(packItemOptions.map((option) => [option.id, option]));
    const total = packageItems.reduce((accumulator, selected) => {
      const option = optionsById.get(selected.productId);

      if (!option) {
        return accumulator;
      }

      return accumulator + (option.discountedPrice ?? option.originalPrice) * selected.quantity;
    }, 0);
    return Number(total.toFixed(2));
  }, [packItemOptions, packageItems]);

  // Ajuste 2026-08-04: referencia de cuánto costarían los componentes a
  // precio de lista, sin ningún descuento — para que el aliado vea de un
  // vistazo cuánto descuento ya traen los productos antes de packOriginalPrice
  // (que es la suma CON descuento, el techo real del pack).
  const packListPrice = useMemo(() => {
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

  const selectedBranchIds = useMemo(() => branches.map((branch) => branch.branchId), [branches]);

  // En edición, available_for_packaging por sede ya descuenta el compromiso
  // actual de este mismo pack en esa sede; se suma de vuelta para reflejar
  // cuánto queda disponible si el pack conserva/ajusta su cantidad ahí.
  const effectiveAvailableForPackaging = useMemo(() => {
    const originalQuantities = new Map(
      (initialData?.packageItems ?? []).map((item) => [item.productId, item.quantity])
    );
    const originalBranchQuantities = new Map(
      (initialData?.branches ?? []).map((branch) => [branch.branchId, branch.quantityAvailable])
    );

    // productId -> branchId -> cantidad efectiva disponible
    const result = new Map<number, Map<number, number>>();

    packItemOptions.forEach((option) => {
      const originalItemQuantity = originalQuantities.get(option.id) ?? 0;
      const perBranch = new Map<number, number>();

      option.branches.forEach((branch) => {
        const originalPackQuantityInBranch = originalBranchQuantities.get(branch.branchId) ?? 0;
        perBranch.set(
          branch.branchId,
          branch.availableForPackaging + originalItemQuantity * originalPackQuantityInBranch
        );
      });

      result.set(option.id, perBranch);
    });

    return result;
  }, [packItemOptions, initialData]);

  // SCRUM-361, Tarea 6.2: candidatos = componentes con stock asignado (>0)
  // en TODAS las sedes actualmente seleccionadas para el pack (misma regla
  // que valida el backend, SCRUM-323). Sin ninguna sede seleccionada, no hay
  // candidatos: primero hay que elegir dónde vivirá el pack.
  const candidateOptions = useMemo(() => {
    if (productType !== "package" || selectedBranchIds.length === 0) {
      return [];
    }

    return packItemOptions.filter((option) =>
      selectedBranchIds.every((branchId) => {
        const branchStock = option.branches.find((b) => b.branchId === branchId);
        return (branchStock?.quantityAvailable ?? 0) > 0;
      })
    );
  }, [productType, packItemOptions, selectedBranchIds]);

  // Límite para la cantidad de un componente por pack: lo más restrictivo
  // entre todas las sedes seleccionadas (no se puede necesitar, por pack,
  // más de lo que la sede más chica soporta).
  const maxQuantityPerComponent = useMemo(() => {
    const result = new Map<number, number>();

    candidateOptions.forEach((option) => {
      const perBranch = effectiveAvailableForPackaging.get(option.id);
      if (!perBranch || selectedBranchIds.length === 0) {
        result.set(option.id, 0);
        return;
      }

      const min = Math.min(...selectedBranchIds.map((branchId) => perBranch.get(branchId) ?? 0));
      result.set(option.id, min);
    });

    return result;
  }, [candidateOptions, effectiveAvailableForPackaging, selectedBranchIds]);

  // Máximo de packs ofrecibles, por cada sede seleccionada — gobernado por
  // el componente más escaso en esa sede (Tarea 6.1, hint junto al campo).
  const maxPacksByBranch = useMemo(() => {
    const result = new Map<number, number>();

    if (productType !== "package" || packageItems.length === 0) {
      return result;
    }

    selectedBranchIds.forEach((branchId) => {
      const max = packageItems.reduce((min, item) => {
        const available = effectiveAvailableForPackaging.get(item.productId)?.get(branchId) ?? 0;
        const possiblePacks = item.quantity > 0 ? Math.floor(available / item.quantity) : 0;
        return Math.min(min, possiblePacks);
      }, Number.MAX_SAFE_INTEGER);

      result.set(branchId, max === Number.MAX_SAFE_INTEGER ? 0 : max);
    });

    return result;
  }, [productType, packageItems, selectedBranchIds, effectiveAvailableForPackaging]);

  const validate = (): boolean => {
    const nextErrors = validateProductForm({
      title,
      productCategoryId,
      originalPrice: effectiveOriginalPrice,
      discountedPrice,
      branches,
      productType,
      packageItems,
      maxPacksByBranch,
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

  /** Descarta de packageItems los componentes que ya no son candidatos válidos
   * para el conjunto de sedes dado, avisando cuántos se quitaron (Tarea 6.3). */
  const reconcilePackageItems = (branchIds: number[]) => {
    setPackageItems((previous) => {
      if (productType !== "package" || previous.length === 0) {
        return previous;
      }

      const validIds = new Set(
        packItemOptions
          .filter((option) =>
            branchIds.every((branchId) => {
              const branchStock = option.branches.find((b) => b.branchId === branchId);
              return (branchStock?.quantityAvailable ?? 0) > 0;
            })
          )
          .map((option) => option.id)
      );

      const kept = previous.filter((item) => validIds.has(item.productId));
      const removedCount = previous.length - kept.length;

      if (removedCount > 0) {
        setReconciliationNotice(
          removedCount === 1
            ? "Se quitó 1 producto del pack: no tiene inventario en la nueva selección de sedes."
            : `Se quitaron ${removedCount} productos del pack: no tienen inventario en la nueva selección de sedes.`
        );
      }

      return kept;
    });
  };

  const handleToggleBranch = (branchId: number) => {
    setBranches((previous) => {
      let next: ProductBranchAssignment[];

      if (productType === "package") {
        // Ajuste funcional 2026-08-03: un pack vive en una sola sede.
        // Elegir otra sede reemplaza la actual — no se acumulan. La nueva
        // sede arranca sin cantidad ni publicación: son propias de esa
        // sede, no tiene sentido arrastrar el compromiso de la anterior.
        const alreadySelected = previous.length === 1 && previous[0].branchId === branchId;
        next = alreadySelected ? previous : [{ branchId, quantityAvailable: 0, isPublished: false }];

        if (!alreadySelected) {
          reconcilePackageItems([branchId]);
        }
      } else {
        const existing = previous.find((item) => item.branchId === branchId);
        next = existing
          ? previous.filter((item) => item.branchId !== branchId)
          : [...previous, { branchId, quantityAvailable: 0, isPublished: false }];
      }

      return next;
    });

    setLocalErrors((previous) => ({ ...previous, branches: undefined }));
  };

  const handleBranchQuantityChange = (branchId: number, quantity: number) => {
    const normalizedQuantity = Math.max(0, quantity);

    setBranches((previous) =>
      previous.map((item) =>
        item.branchId === branchId
          ? {
              ...item,
              quantityAvailable: normalizedQuantity,
              // No puede quedar publicada sin inventario/compromiso: si la
              // cantidad baja a 0, se despublica automáticamente.
              isPublished: normalizedQuantity > 0 ? item.isPublished : false,
            }
          : item
      )
    );

    setLocalErrors((previous) => ({ ...previous, branches: undefined }));
  };

  const handleBranchPublishedChange = (branchId: number, isPublished: boolean) => {
    setBranches((previous) =>
      previous.map((item) =>
        item.branchId === branchId && item.quantityAvailable > 0
          ? { ...item, isPublished }
          : item
      )
    );

    setLocalErrors((previous) => ({ ...previous, branches: undefined }));
  };

  const dismissReconciliationNotice = () => setReconciliationNotice(null);

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
    const maxQuantity = maxQuantityPerComponent.get(productId) ?? Number.MAX_SAFE_INTEGER;
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

    if (parsedOriginalPrice === null) {
      return;
    }

    await onSubmit(
      buildProductFormSubmitInput({
        commerceId: initialData?.commerceId,
        title,
        productCategoryId,
        productType,
        originalPrice: parsedOriginalPrice,
        discountedPrice: parsedDiscountedPrice,
        description,
        branches,
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
    description,
    setDescription,
    branches,
    totalQuantityAcrossBranches,
    handleToggleBranch,
    handleBranchQuantityChange,
    handleBranchPublishedChange,
    packageItems,
    candidateOptions,
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
  };
}
