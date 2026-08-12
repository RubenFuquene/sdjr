"use client";

import type { ProductUpdateConfirmationImpact } from "@/types/products";
import { ConfirmationDialogShell } from "./confirmation-dialog-shell";

interface ProductUpdateConfirmationDialogProps {
  isOpen: boolean;
  fiscalImpact: ProductUpdateConfirmationImpact["fiscal"];
  stockImpact: ProductUpdateConfirmationImpact["stock"];
  branchNameById: Map<number, string>;
  isLoading?: boolean;
  onConfirm: () => void | Promise<void>;
  onCancel: () => void;
}

/**
 * SCRUM-362/361 (unificación — ver plan unificacionConfirmacion409Fiscal361):
 * reemplaza a PackageAdjustmentConfirmationDialog y
 * FiscalReclassificationConfirmationDialog. Un solo 409 puede traer los dos
 * motivos a la vez (reclasificar a otro_verificar despublica sedes/packs,
 * SCRUM-362 D9; bajar stock sobre-compromete packs, SCRUM-361), así que este
 * diálogo renderiza cada sección solo si su impacto vino en la respuesta —
 * el aliado confirma una sola vez con confirm_changes=true, sea uno o los
 * dos motivos.
 */
export function ProductUpdateConfirmationDialog({
  isOpen,
  fiscalImpact,
  stockImpact,
  branchNameById,
  isLoading = false,
  onConfirm,
  onCancel,
}: ProductUpdateConfirmationDialogProps) {
  const hasFiscalImpact = fiscalImpact !== null;
  const hasStockImpact = stockImpact !== null;

  const title =
    hasFiscalImpact && hasStockImpact
      ? "Este cambio requiere confirmación"
      : hasFiscalImpact
        ? "Este producto saldrá de la lista de compras"
        : "Este cambio afecta packs existentes";

  const description =
    hasFiscalImpact && hasStockImpact ? (
      <>
        Este cambio despublica el producto de las sedes y packs listados abajo (hasta que completes
        su clasificación fiscal) y además ajusta automáticamente los packs afectados por la baja de
        stock. Si confirmas, ambos cambios se aplican de inmediato.
      </>
    ) : hasFiscalImpact ? (
      <>
        Marcarlo como &quot;No estoy seguro&quot; lo despublica de las siguientes sedes y packs,
        hasta que completes su clasificación fiscal. Si confirmas, se aplica de inmediato.
      </>
    ) : (
      "Bajar este stock deja los siguientes packs sin suficientes componentes. Si confirmas, se ajustan automáticamente a la cantidad máxima que soportan."
    );

  const confirmLabel =
    hasFiscalImpact && hasStockImpact
      ? "Confirmar cambios"
      : hasFiscalImpact
        ? "Confirmar y despublicar"
        : "Confirmar y ajustar packs";

  return (
    <ConfirmationDialogShell
      isOpen={isOpen}
      titleId="product-update-confirmation-title"
      descriptionId="product-update-confirmation-description"
      title={title}
      description={description}
      confirmLabel={confirmLabel}
      isLoading={isLoading}
      onConfirm={onConfirm}
      onCancel={onCancel}
    >
      {fiscalImpact && fiscalImpact.affectedBranches.length > 0 ? (
        <div className="space-y-2">
          <p className="text-sm font-medium text-[#1A1A1A]">
            {hasStockImpact ? "Sedes que se despublicarán" : "Sedes afectadas"}
          </p>
          <ul className="max-h-40 overflow-y-auto space-y-2" aria-live="polite">
            {fiscalImpact.affectedBranches.map((branch) => (
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

      {fiscalImpact && fiscalImpact.affectedPackages.length > 0 ? (
        <div className="space-y-2">
          <p className="text-sm font-medium text-[#1A1A1A]">
            {hasStockImpact ? "Packs que se despublicarán" : "Packs afectados"}
          </p>
          <ul className="max-h-40 overflow-y-auto space-y-2" aria-live="polite">
            {fiscalImpact.affectedPackages.map((item) => (
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

      {stockImpact && stockImpact.affectedPackages.length > 0 ? (
        <div className="space-y-2">
          <p className="text-sm font-medium text-[#1A1A1A]">
            {hasFiscalImpact ? "Packs con inventario ajustado" : "Packs afectados"}
          </p>
          <ul className="max-h-64 overflow-y-auto space-y-2" aria-live="polite">
            {stockImpact.affectedPackages.map((item, index) => (
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
        </div>
      ) : null}
    </ConfirmationDialogShell>
  );
}
