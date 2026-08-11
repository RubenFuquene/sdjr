"use client";

import { useEffect, useMemo, useState } from "react";
import type {
  FiscalCodeOption,
  ProductBranchAssignment,
  ProductCategoryFromAPI,
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
  /** SCRUM-362: 'otro_verificar' bloquea el uso de este producto como componente de pack. */
  fiscalCode: string | null;
  branches: PackItemOptionBranch[];
};

/**
 * SCRUM-362: candidato mostrado en el selector de armado de pack, ya
 * asignado a la(s) sede(s) elegida(s) (con o sin stock) — se muestra
 * siempre, pero solo es marcable si isSelectable es true.
 */
export type PackItemCandidate = PackItemOption & {
  isSelectable: boolean;
  disabledReason?: "stock" | "fiscal";
};

/**
 * Única fuente de verdad de por qué un componente no es seleccionable —
 * usada tanto por packItemCandidates (universo mostrado, sedes ya en
 * estado) como por reconcilePackageItems (recibe branchIds explícito
 * porque se llama antes de que el nuevo estado de sedes se refleje en el
 * render). Fiscal tiene prioridad sobre stock si ambas aplican: es el
 * bloqueo que el aliado no puede resolver subiendo inventario.
 */
function computeDisabledReason(
  option: PackItemOption,
  branchIds: number[]
): "stock" | "fiscal" | undefined {
  if (option.fiscalCode === "otro_verificar") {
    return "fiscal";
  }

  const hasStockEverywhere = branchIds.every((branchId) => {
    const branchStock = option.branches.find((b) => b.branchId === branchId);
    return (branchStock?.quantityAvailable ?? 0) > 0;
  });

  return hasStockEverywhere ? undefined : "stock";
}

type UseProductFormStateParams = {
  initialData?: ProductFormInitialData | null;
  fieldErrors: ProviderProductFormFieldErrors;
  packItemOptions: PackItemOption[];
  /** SCRUM-362 (5.3): categorías cargadas, para sugerir default_fiscal_code al elegir una. */
  categories: ProductCategoryFromAPI[];
  /** SCRUM-362: conjunto de códigos fiscales permitidos para el comercio (Tarea 2). */
  fiscalCodeOptions: FiscalCodeOption[];
  onSubmit: (input: ProviderProductFormInput) => Promise<void>;
};

export function useProductFormState({
  initialData,
  fieldErrors,
  packItemOptions,
  categories,
  fiscalCodeOptions,
  onSubmit,
}: UseProductFormStateParams) {
  const initialDraft = mapInitialDataToDraft(initialData);

  const [title, setTitle] = useState(initialDraft.title);
  const [productType, setProductType] = useState<ProductType>(initialDraft.productType);
  const [productCategoryId, setProductCategoryIdState] = useState(initialDraft.productCategoryId);
  const [fiscalCode, setFiscalCode] = useState(initialDraft.fiscalCode);
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
  // SCRUM-362: productos que se sacaron solos de packageItems (por stock o
  // fiscal) durante esta sesión de edición — señal para la animación de
  // pulso en su fila del selector. No se limpia: la señal es persistente
  // mientras el formulario esté abierto (comportamiento intencional, no un
  // aviso temporal que desaparece).
  const [excludedProductIds, setExcludedProductIds] = useState<Set<number>>(new Set());

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
      fiscalCode: localErrors.fiscalCode ?? fieldErrors["product.fiscal_code"],
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

  // SCRUM-362: universo MOSTRADO en el selector — todo componente asignado a
  // la(s) sede(s) elegida(s) (con o sin stock), marcado con isSelectable/
  // disabledReason en vez de excluido del todo. Sin ninguna sede
  // seleccionada, no hay candidatos: primero hay que elegir dónde vivirá el
  // pack. Si ambas razones aplican a la vez, se reporta "fiscal" — es el
  // bloqueo que el aliado no puede resolver subiendo inventario.
  const packItemCandidates: PackItemCandidate[] = useMemo(() => {
    if (productType !== "package" || selectedBranchIds.length === 0) {
      return [];
    }

    return packItemOptions
      .filter((option) =>
        selectedBranchIds.every((branchId) => option.branches.some((b) => b.branchId === branchId))
      )
      .map((option) => {
        const disabledReason = computeDisabledReason(option, selectedBranchIds);
        return { ...option, isSelectable: disabledReason === undefined, disabledReason };
      });
  }, [productType, packItemOptions, selectedBranchIds]);

  // SCRUM-361, Tarea 6.2: subconjunto realmente elegible — misma regla que
  // valida el backend (SCRUM-323/362). Alimenta los cálculos de máximos por
  // componente; el universo MOSTRADO en el selector es packItemCandidates.
  const candidateOptions = useMemo(
    () => packItemCandidates.filter((option) => option.isSelectable),
    [packItemCandidates]
  );

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
      fiscalCode,
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

  /**
   * SCRUM-362 (5.3): sugiere el default_fiscal_code de la categoría elegida,
   * pero nunca a ciegas — solo si está dentro del conjunto que el comercio
   * tiene permitido (fiscalCodeOptions ya viene filtrado por franquicia
   * desde el backend, Tarea 2). El aliado puede sobrescribirla después
   * eligiendo otro valor en el desplegable de código fiscal.
   */
  const handleProductCategoryChange = (nextCategoryId: string) => {
    setProductCategoryIdState(nextCategoryId);
    setLocalErrors((previous) => ({ ...previous, productCategoryId: undefined }));

    const category = categories.find((item) => String(item.id) === nextCategoryId);
    const suggestion = category?.default_fiscal_code;

    if (suggestion && fiscalCodeOptions.some((option) => option.value === suggestion)) {
      setFiscalCode(suggestion);
      setLocalErrors((previous) => ({ ...previous, fiscalCode: undefined }));
    }
  };

  /**
   * Descarta de packageItems los componentes que ya no son candidatos
   * válidos para el conjunto de sedes dado — por stock (Tarea 6.3) o por
   * clasificación fiscal pendiente (SCRUM-362) — avisando cuántos y por
   * qué se quitaron, y marcándolos en excludedProductIds para la animación
   * de su fila (siguen visibles, inhabilitados, en el selector).
   *
   * Recibe branchIds explícito en vez de leer selectedBranchIds: se llama
   * desde handleToggleBranch ANTES de que el nuevo estado de sedes se
   * refleje en el render, así que el derivado packItemCandidates todavía
   * tendría las sedes viejas en ese momento.
   */
  const reconcilePackageItems = (branchIds: number[]) => {
    setPackageItems((previous) => {
      if (productType !== "package" || previous.length === 0) {
        return previous;
      }

      const optionsById = new Map(packItemOptions.map((option) => [option.id, option]));
      const removedTitlesByReason: { stock: string[]; fiscal: string[] } = { stock: [], fiscal: [] };
      const removedIds: number[] = [];

      const kept = previous.filter((item) => {
        const option = optionsById.get(item.productId);
        const reason = option ? computeDisabledReason(option, branchIds) : "stock";

        if (!reason) {
          return true;
        }

        removedTitlesByReason[reason].push(option?.title ?? `Producto #${item.productId}`);
        removedIds.push(item.productId);
        return false;
      });

      if (removedIds.length > 0) {
        setExcludedProductIds((prevIds) => new Set([...prevIds, ...removedIds]));

        const messages: string[] = [];

        if (removedTitlesByReason.fiscal.length > 0) {
          messages.push(
            removedTitlesByReason.fiscal.length === 1
              ? `Se quitó "${removedTitlesByReason.fiscal[0]}" del pack: su clasificación fiscal quedó pendiente de revisión.`
              : `Se quitaron ${removedTitlesByReason.fiscal.length} productos del pack porque su clasificación fiscal quedó pendiente de revisión (${removedTitlesByReason.fiscal.join(", ")}).`
          );
        }

        if (removedTitlesByReason.stock.length > 0) {
          messages.push(
            removedTitlesByReason.stock.length === 1
              ? "Se quitó 1 producto del pack: no tiene inventario en la nueva selección de sedes."
              : `Se quitaron ${removedTitlesByReason.stock.length} productos del pack: no tienen inventario en la nueva selección de sedes.`
          );
        }

        setReconciliationNotice(messages.join(" "));
      }

      return kept;
    });
  };

  // SCRUM-362: reconcilia también al MONTAR el formulario en modo edición —
  // hasta ahora reconcilePackageItems solo se disparaba al cambiar de sede
  // (handleToggleBranch); sin esto, un pack que YA contenía un componente
  // sin stock o pendiente de revisión fiscal al abrir el formulario nunca
  // se limpiaba solo, había que tocar la sede para que corriera.
  // eslint-disable-next-line react-hooks/exhaustive-deps
  useEffect(() => {
    reconcilePackageItems(selectedBranchIds);
  }, []);

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
        fiscalCode,
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
    handleProductCategoryChange,
    fiscalCode,
    setFiscalCode,
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
  };
}
