"use client";

import { useMemo, useState } from "react";
import { toast } from "sonner";
import { ConfirmationDialog } from "@/components/admin/shared/confirmation-dialog";
import { useProviderBranches } from "@/hooks/provider/use-provider-branches";
import { useProviderProductForm } from "@/hooks/provider/use-provider-product-form";
import { useProviderProducts } from "@/hooks/provider/use-provider-products";
import type { ProductFromAPI } from "@/lib/api";
import { ProductFormModal } from "./product-form-modal";
import type { ProductFormInitialData, ProductFormMode } from "./product-form";
import { ProductsPageHeader } from "./products-page-header";
import { ProductsPageContent } from "./products-page-content";

export function ProductsPageClient() {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState<ProductFormMode>("create");
  const [editingProductId, setEditingProductId] = useState<number | null>(null);
  const [editingInitialData, setEditingInitialData] = useState<ProductFormInitialData | null>(null);
  const [productPendingDeletion, setProductPendingDeletion] = useState<ProductFromAPI | null>(null);

  const { products, loading, error, hasCommerce, hasProducts, commerceId, refresh } = useProviderProducts();
  const { branches } = useProviderBranches();
  const {
    submitting,
    error: formError,
    fieldErrors,
    createProduct,
    updateProduct,
    deleteProduct,
    resetErrors,
  } =
    useProviderProductForm();

  const branchOptions = useMemo(
    () => branches.map((branch) => ({ id: branch.id, name: branch.name })),
    [branches]
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
    originalPrice: product.original_price,
    discountedPrice: product.discounted_price,
    quantityAvailable: product.quantity_available,
    quantityTotal: product.quantity_total,
    branchId: null,
    packageItemIds: [],
  });

  const handleEditProduct = (product: ProductFromAPI) => {
    setModalMode("edit");
    setEditingProductId(product.id);
    setEditingInitialData(mapProductToInitialData(product));
    resetErrors();
    setIsModalOpen(true);
  };

  const handleSubmitProductForm = async (input: Parameters<typeof createProduct>[0]) => {
    if (modalMode === "edit" && editingProductId) {
      const updated = await updateProduct(editingProductId, input);

      if (!updated) {
        return;
      }

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
