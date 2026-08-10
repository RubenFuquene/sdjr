"use client";

import type { AffectedFiscalBranch, AffectedFiscalPackage } from "@/types/products";
import { ConfirmationDialogShell } from "./confirmation-dialog-shell";

interface FiscalReclassificationConfirmationDialogProps {
  isOpen: boolean;
  affectedBranches: AffectedFiscalBranch[];
  affectedPackages: AffectedFiscalPackage[];
  isLoading?: boolean;
  onConfirm: () => void | Promise<void>;
  onCancel: () => void;
}

/**
 * SCRUM-362 (D9, Tarea 5.7): ante un 409 al reclasificar un producto a
 * "No estoy seguro", muestra las sedes y packs que quedarán fuera de la
 * lista de compras. Confirmar reenvía la edición con
 * confirm_fiscal_reclassification=true; cancelar descarta la edición — el
 * 409 nunca llegó a aplicarse en el servidor, no hay estado intermedio que
 * limpiar.
 */
export function FiscalReclassificationConfirmationDialog({
  isOpen,
  affectedBranches,
  affectedPackages,
  isLoading = false,
  onConfirm,
  onCancel,
}: FiscalReclassificationConfirmationDialogProps) {
  return (
    <ConfirmationDialogShell
      isOpen={isOpen}
      titleId="fiscal-reclassification-title"
      descriptionId="fiscal-reclassification-description"
      title="Este producto saldrá de la lista de compras"
      description={
        <>
          Marcarlo como &quot;No estoy seguro&quot; lo despublica de las siguientes sedes y packs,
          hasta que completes su clasificación fiscal. Si confirmas, se aplica de inmediato.
        </>
      }
      confirmLabel="Confirmar y despublicar"
      isLoading={isLoading}
      onConfirm={onConfirm}
      onCancel={onCancel}
    >
      {affectedBranches.length > 0 ? (
        <div className="space-y-2">
          <p className="text-sm font-medium text-[#1A1A1A]">Sedes afectadas</p>
          <ul className="max-h-40 overflow-y-auto space-y-2" aria-live="polite">
            {affectedBranches.map((branch) => (
              <li
                key={branch.commerceBranchId}
                className="rounded-[12px] border border-[#E0E0E0] bg-[#F7F7F7] px-4 py-2 text-sm text-[#1A1A1A]"
              >
                {branch.commerceBranchName || `Sede #${branch.commerceBranchId}`}
              </li>
            ))}
          </ul>
        </div>
      ) : null}

      {affectedPackages.length > 0 ? (
        <div className="space-y-2">
          <p className="text-sm font-medium text-[#1A1A1A]">Packs afectados</p>
          <ul className="max-h-40 overflow-y-auto space-y-2" aria-live="polite">
            {affectedPackages.map((item) => (
              <li
                key={item.packageId}
                className="rounded-[12px] border border-[#E0E0E0] bg-[#F7F7F7] px-4 py-2 text-sm text-[#1A1A1A]"
              >
                {item.packageTitle}
              </li>
            ))}
          </ul>
        </div>
      ) : null}
    </ConfirmationDialogShell>
  );
}
