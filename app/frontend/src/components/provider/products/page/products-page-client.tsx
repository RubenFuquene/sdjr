"use client";

import { useMemo, useState } from "react";
import { toast } from "sonner";
import { ConfirmationDialog } from "@/components/admin/shared/confirmation-dialog";
import { ProductUpdateConfirmationDialog } from "../shared";
import { ApiError, getPackageItemsByProductId, getProductById } from "@/lib/api";
import { useProviderBranches, useProviderProductForm, useProviderProducts } from "@/hooks/index";
import type { ProductFromAPI, ProviderProductFormInput } from "@/types/products";
import { ProductFormModal } from "../form";
import type { ProductFormInitialData, ProductFormMode } from "../form";
import { ProductsPageHeader } from "./products-page-header";
import { ProductsPageContent } from "./products-page-content";

export function ProductsPageClient() {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState<ProductFormMode>("create");
  const [editingProductId, setEditingProductId] = useState<number | null>(null);
  const [editingInitialData, setEditingInitialData] = useState<ProductFormInitialData | null>(null);
  const [productPendingDeletion, setProductPendingDeletion] = useState<ProductFromAPI | null>(null);
  // SCRUM-362/361 (unificación): la edición que disparó el 409, guardada
  // para poder reenviarla con confirmChanges=true si el aliado confirma.
  const [pendingConfirmation, setPendingConfirmation] = useState<{
    productId: number;
    input: ProviderProductFormInput;
  } | null>(null);

  const { products, loading, error, hasCommerce, hasProducts, commerceId, refresh } = useProviderProducts();
  const { branches } = useProviderBranches();
  const {
    submitting,
    error: formError,
    fieldErrors,
    confirmationImpact,
    createProduct,
    updateProduct,
    deleteProduct,
    clearConfirmationImpact,
    resetErrors,
  } =
    useProviderProductForm();

  const branchOptions = useMemo(
    () => branches.map((branch) => ({ id: branch.id, name: branch.name })),
    [branches]
  );

  const branchNameById = useMemo(
    () => new Map(branchOptions.map((branch) => [branch.id, branch.name])),
    [branchOptions]
  );

  const availableSingleProducts = useMemo(
    () => products.filter((product) => product.product_type === "single"),
    [products]
  );

  const closeModal = () => {
    setIsModalOpen(false);
    setModalMode("create");
    setEditingProductId(null);
    setEditingInitialData(null);
    resetErrors();
  };

  const openCreateSingleModal = () => {
    setModalMode("create");
    setEditingProductId(null);
    setEditingInitialData({
      commerceId: commerceId ?? undefined,
      productType: "single",
    });
    resetErrors();
    setIsModalOpen(true);
  };

  const openCreatePackModal = () => {
    setModalMode("create");
    setEditingProductId(null);
    setEditingInitialData({
      commerceId: commerceId ?? undefined,
      productType: "package",
    });
    resetErrors();
    setIsModalOpen(true);
  };

  const mapProductToInitialData = (product: ProductFromAPI): ProductFormInitialData => ({
    id: product.id,
    commerceId: product.commerce_id,
    title: product.title,
    description: product.description,
    productType: product.product_type,
    productCategoryId: product.product_category_id,
    fiscalCode: product.fiscal_code,
    originalPrice: product.original_price,
    discountedPrice: product.discounted_price,
    // SCRUM-361: ambos tipos reconstruyen la asignación multi-sede completa
    // desde commerce_branches[] — para packs, quantityAvailable es el
    // compromiso comprometido en esa sede, no stock físico.
    branches: (product.commerce_branches ?? []).map((branch) => ({
      branchId: branch.id,
      quantityAvailable: branch.quantity_available ?? 0,
      isPublished: branch.is_published ?? false,
    })),
    packageItems: [],
  });

  const hydrateProductInitialData = async (productId: number): Promise<ProductFormInitialData> => {
    const productResponse = await getProductById(productId);
    const product = productResponse.data;

    let packageItems: Array<{ productId: number; quantity: number }> = [];

    if (product.product_type === "package") {
      const packageItemsResponse = await getPackageItemsByProductId(productId);

      packageItems = packageItemsResponse.data.map((item) => ({
        productId: item.id,
        quantity: item.quantity ?? item.pivot?.quantity ?? 1,
      }));
    }

    return {
      ...mapProductToInitialData(product),
      packageItems,
    };
  };

  const handleEditProduct = async (product: ProductFromAPI) => {
    try {
      setModalMode("edit");
      setEditingProductId(product.id);
      resetErrors();

      const hydratedInitialData = await hydrateProductInitialData(product.id);
      setEditingInitialData(hydratedInitialData);
      setIsModalOpen(true);
    } catch (error) {
      if (error instanceof ApiError) {
        toast.error(error.message || "No pudimos cargar el detalle del producto.");
        return;
      }

      toast.error("Error inesperado al cargar el detalle del producto.");
    }
  };

  const handleDuplicateProduct = async (product: ProductFromAPI) => {
    try {
      setModalMode("create");
      setEditingProductId(null);
      resetErrors();

      const hydratedInitialData = await hydrateProductInitialData(product.id);

      setEditingInitialData({
        ...hydratedInitialData,
        id: undefined,
        title: `${hydratedInitialData.title ?? product.title} (copia)`,
      });
      setIsModalOpen(true);

      if (product.product_type === "package") {
        toast.info("Verifica los items del pack antes de guardar la copia.");
      }
    } catch (error) {
      if (error instanceof ApiError) {
        toast.error(error.message || "No pudimos cargar el detalle para duplicar el producto.");
        return;
      }

      toast.error("Error inesperado al preparar la copia del producto.");
    }
  };

  const handleSubmitProductForm = async (input: ProviderProductFormInput) => {
    if (modalMode === "edit" && editingProductId) {
      const updated = await updateProduct(editingProductId, input);

      if (!updated) {
        // Si fue un 409, useProviderProductForm ya dejó confirmationImpact
        // poblado — guardamos la edición para poder reenviarla si confirma.
        setPendingConfirmation({ productId: editingProductId, input });
        return;
      }

      setPendingConfirmation(null);
      await refresh();
      closeModal();
      toast.success("Producto actualizado correctamente");
      return;
    }

    const created = await createProduct(input);

    if (!created) {
      return;
    }

    await refresh();
    closeModal();
    toast.success(input.productType === "package" ? "Pack creado correctamente" : "Producto creado correctamente");
  };

  const handleConfirmChanges = async () => {
    if (!pendingConfirmation) {
      return;
    }

    const updated = await updateProduct(pendingConfirmation.productId, {
      ...pendingConfirmation.input,
      confirmChanges: true,
    });

    if (!updated) {
      return;
    }

    setPendingConfirmation(null);
    await refresh();
    closeModal();
    toast.success("Producto actualizado correctamente");
  };

  const handleCancelChanges = () => {
    setPendingConfirmation(null);
    clearConfirmationImpact();
  };

  const handleDeleteProduct = (product: ProductFromAPI) => {
    setProductPendingDeletion(product);
  };

  const handleCloseDeleteDialog = () => {
    setProductPendingDeletion(null);
  };

  const handleConfirmDeleteProduct = async () => {
    if (!productPendingDeletion) {
      return;
    }

    const removed = await deleteProduct(productPendingDeletion.id);

    if (!removed) {
      return;
    }

    await refresh();
    toast.success("Producto eliminado correctamente");
    setProductPendingDeletion(null);
  };

  return (
    <>
      <ProductsPageHeader onAddProduct={openCreateSingleModal} onAddPack={openCreatePackModal} />

      <ProductsPageContent
        products={products}
        loading={loading}
        error={error}
        hasCommerce={hasCommerce}
        hasProducts={hasProducts}
        onAddProduct={openCreateSingleModal}
        onEditProduct={handleEditProduct}
        onDuplicateProduct={handleDuplicateProduct}
        onDeleteProduct={handleDeleteProduct}
      />

      <ProductFormModal
        isOpen={isModalOpen}
        mode={modalMode}
        initialData={editingInitialData}
        submitting={submitting}
        error={formError}
        fieldErrors={fieldErrors}
        branchOptions={branchOptions}
        availableSingleProducts={availableSingleProducts}
        onClose={closeModal}
        onSubmit={handleSubmitProductForm}
      />

      <ProductUpdateConfirmationDialog
        isOpen={Boolean(confirmationImpact)}
        fiscalImpact={confirmationImpact?.fiscal ?? null}
        stockImpact={confirmationImpact?.stock ?? null}
        branchNameById={branchNameById}
        isLoading={submitting}
        onConfirm={handleConfirmChanges}
        onCancel={handleCancelChanges}
      />

      <ConfirmationDialog
        isOpen={Boolean(productPendingDeletion)}
        title="Eliminar producto"
        description={
          productPendingDeletion
            ? `¿Deseas eliminar el producto ${productPendingDeletion.title}? Esta acción es permanente.`
            : undefined
        }
        confirmText="Eliminar"
        cancelText="Cancelar"
        variant="danger"
        isLoading={submitting}
        onClose={handleCloseDeleteDialog}
        onConfirm={handleConfirmDeleteProduct}
      />
    </>
  );
}
