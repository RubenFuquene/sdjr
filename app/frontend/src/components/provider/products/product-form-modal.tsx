"use client";

import { useEffect, useRef } from "react";
import { X } from "lucide-react";
import { ProductForm } from "./product-form";
import type { ProductFormInitialData, ProductFormMode } from "./product-form";
import type { ProviderProductFormFieldErrors, ProviderProductFormInput } from "@/hooks/provider/use-provider-product-form";
import type { ProductFromAPI } from "@/lib/api";

export interface ProductBranchOption {
  id: number;
  name: string;
}

interface ProductFormModalProps {
  isOpen: boolean;
  mode: ProductFormMode;
  initialData?: ProductFormInitialData | null;
  submitting: boolean;
  error: string | null;
  fieldErrors: ProviderProductFormFieldErrors;
  branchOptions?: ProductBranchOption[];
  availableSingleProducts?: ProductFromAPI[];
  onClose: () => void;
  onSubmit: (input: ProviderProductFormInput) => Promise<void>;
}

export function ProductFormModal({
  isOpen,
  mode,
  initialData,
  submitting,
  error,
  fieldErrors,
  branchOptions = [],
  availableSingleProducts = [],
  onClose,
  onSubmit,
}: ProductFormModalProps) {
  const modalRef = useRef<HTMLDivElement>(null);
  const previousFocusedElementRef = useRef<HTMLElement | null>(null);

  useEffect(() => {
    if (!isOpen) {
      return;
    }

    previousFocusedElementRef.current =
      document.activeElement instanceof HTMLElement ? document.activeElement : null;

    const originalOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";

    const focusableSelector =
      'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

    const focusInitialElement = () => {
      const container = modalRef.current;
      if (!container) {
        return;
      }

      const focusables = Array.from(
        container.querySelectorAll<HTMLElement>(focusableSelector)
      ).filter((element) => !element.hasAttribute("disabled") && element.tabIndex !== -1);

      if (focusables.length > 0) {
        focusables[0].focus();
      } else {
        container.focus();
      }
    };

    focusInitialElement();

    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        event.preventDefault();
        onClose();
        return;
      }

      if (event.key !== "Tab") {
        return;
      }

      const container = modalRef.current;
      if (!container) {
        return;
      }

      const focusables = Array.from(
        container.querySelectorAll<HTMLElement>(focusableSelector)
      ).filter((element) => !element.hasAttribute("disabled") && element.tabIndex !== -1);

      if (focusables.length === 0) {
        event.preventDefault();
        return;
      }

      const first = focusables[0];
      const last = focusables[focusables.length - 1];
      const activeElement = document.activeElement;

      if (event.shiftKey && activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    };

    window.addEventListener("keydown", onKeyDown);

    return () => {
      document.body.style.overflow = originalOverflow;
      window.removeEventListener("keydown", onKeyDown);

      if (previousFocusedElementRef.current) {
        previousFocusedElementRef.current.focus();
      }
    };
  }, [isOpen, onClose]);

  if (!isOpen) {
    return null;
  }

  return (
    <div className="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
      <div
        ref={modalRef}
        role="dialog"
        aria-modal="true"
        aria-labelledby="product-modal-title"
        tabIndex={-1}
        className="bg-white rounded-[18px] shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
      >
        <div className="flex items-center justify-between p-6 md:p-8 border-b border-[#E0E0E0]">
          <div>
            <h2 id="product-modal-title" className="text-2xl font-bold text-[#1A1A1A]">
              {mode === "edit" ? "Editar Producto" : "Nuevo Producto"}
            </h2>
            <p className="text-[#6A6A6A] mt-1">
              {mode === "edit"
                ? "Actualiza la información del producto"
                : "Completa los datos del producto"}
            </p>
          </div>
          <button
            type="button"
            onClick={onClose}
            aria-label="Cerrar modal"
            className="text-[#6A6A6A] hover:text-[#1A1A1A] transition-colors"
          >
            <X size={24} />
          </button>
        </div>

        <div className="p-6 md:p-8">
          <ProductForm
            mode={mode}
            initialData={initialData}
            submitting={submitting}
            apiError={error}
            fieldErrors={fieldErrors}
            branchOptions={branchOptions}
            availableSingleProducts={availableSingleProducts}
            onCancel={onClose}
            onSubmit={onSubmit}
          />
        </div>
      </div>
    </div>
  );
}
