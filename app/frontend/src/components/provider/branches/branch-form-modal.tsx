"use client";

import { X } from "lucide-react";
import { BranchForm } from "./branch-form";
import type {
  BranchFormInitialData,
  BranchFormMode,
} from "./branch-form";
import type {
  ProviderBranchFormFieldErrors,
  ProviderBranchFormInput,
} from "@/hooks/provider/use-provider-branch-form";

interface BranchFormModalProps {
  isOpen: boolean;
  mode: BranchFormMode;
  initialData?: BranchFormInitialData | null;
  submitting: boolean;
  error: string | null;
  fieldErrors: ProviderBranchFormFieldErrors;
  onClose: () => void;
  onSubmit: (input: ProviderBranchFormInput) => Promise<void>;
}

export function BranchFormModal({
  isOpen,
  mode,
  initialData,
  submitting,
  error,
  fieldErrors,
  onClose,
  onSubmit,
}: BranchFormModalProps) {
  if (!isOpen) {
    return null;
  }

  return (
    <div className="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
      <div className="bg-white rounded-[18px] shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div className="flex items-center justify-between p-6 md:p-8 border-b border-[#E0E0E0]">
          <div>
            <h2 className="text-2xl font-bold text-[#1A1A1A]">
              {mode === "edit" ? "Editar Sucursal" : "Nueva Sucursal"}
            </h2>
            <p className="text-[#6A6A6A] mt-1">
              {mode === "edit"
                ? "Actualiza la información de la sucursal"
                : "Completa los datos de la sucursal"}
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
          <BranchForm
            mode={mode}
            initialData={initialData}
            submitting={submitting}
            apiError={error}
            fieldErrors={fieldErrors}
            onCancel={onClose}
            onSubmit={onSubmit}
          />
        </div>
      </div>
    </div>
  );
}
