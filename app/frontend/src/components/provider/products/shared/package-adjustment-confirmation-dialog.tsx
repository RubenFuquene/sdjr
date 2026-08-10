"use client";

import type { AffectedPackage } from "@/types/products";
import { ConfirmationDialogShell } from "./confirmation-dialog-shell";

interface PackageAdjustmentConfirmationDialogProps {
  isOpen: boolean;
  affectedPackages: AffectedPackage[];
  branchNameById: Map<number, string>;
  isLoading?: boolean;
  onConfirm: () => void | Promise<void>;
  onCancel: () => void;
}

/**
 * SCRUM-361, Tarea 3.3/6.4: ante un 409 al bajar el stock de un componente,
 * muestra los packs afectados con su cantidad actual y la resultante.
 * Confirmar reenvía la edición con confirm_package_adjustments=true;
 * cancelar descarta también la edición del stock (no hay un estado
 * intermedio: el 409 nunca llegó a aplicarse en el servidor).
 */
export function PackageAdjustmentConfirmationDialog({
  isOpen,
  affectedPackages,
  branchNameById,
  isLoading = false,
  onConfirm,
  onCancel,
}: PackageAdjustmentConfirmationDialogProps) {
  return (
    <ConfirmationDialogShell
      isOpen={isOpen}
      titleId="package-adjustment-title"
      descriptionId="package-adjustment-description"
      title="Este cambio afecta packs existentes"
      description="Bajar este stock deja los siguientes packs sin suficientes componentes. Si confirmas, se ajustan automáticamente a la cantidad máxima que soportan."
      confirmLabel="Confirmar y ajustar packs"
      isLoading={isLoading}
      onConfirm={onConfirm}
      onCancel={onCancel}
    >
      <ul className="max-h-64 overflow-y-auto space-y-2" aria-live="polite">
        {affectedPackages.map((item, index) => (
          <li
            key={`${item.packageId}-${item.commerceBranchId}-${index}`}
            className="flex items-center justify-between gap-3 rounded-[12px] border border-[#E0E0E0] bg-[#F7F7F7] px-4 py-3 text-sm"
          >
            <div>
              <p className="font-medium text-[#1A1A1A]">{item.packageTitle}</p>
              <p className="text-[#6A6A6A]">
                {branchNameById.get(item.commerceBranchId) ?? `Sede #${item.commerceBranchId}`}
              </p>
            </div>
            <p className="text-[#1A1A1A] whitespace-nowrap">
              {item.currentQuantity} <span className="text-[#6A6A6A]">&rarr;</span>{" "}
              <span className="font-semibold text-[#B58A1A]">{item.adjustedQuantity}</span> packs
            </p>
          </li>
        ))}
      </ul>
    </ConfirmationDialogShell>
  );
}
