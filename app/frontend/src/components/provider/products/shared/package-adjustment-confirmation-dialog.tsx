"use client";

import { useEffect, useRef } from "react";
import { AlertTriangle, X } from "lucide-react";
import type { AffectedPackage } from "@/types/products";

interface PackageAdjustmentConfirmationDialogProps {
  isOpen: boolean;
  affectedPackages: AffectedPackage[];
  branchNameById: Map<number, string>;
  isLoading?: boolean;
  onConfirm: () => void | Promise<void>;
  onCancel: () => void;
}

const FOCUSABLE_SELECTOR =
  'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';

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
  const dialogRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!isOpen) {
      return;
    }

    const dialogNode = dialogRef.current;
    dialogNode?.querySelector<HTMLElement>(FOCUSABLE_SELECTOR)?.focus();

    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        onCancel();
        return;
      }

      if (event.key !== "Tab" || !dialogNode) {
        return;
      }

      const focusable = Array.from(dialogNode.querySelectorAll<HTMLElement>(FOCUSABLE_SELECTOR));
      if (focusable.length === 0) {
        return;
      }

      const first = focusable[0];
      const last = focusable[focusable.length - 1];

      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    };

    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, [isOpen, onCancel]);

  if (!isOpen) {
    return null;
  }

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center px-4"
      role="dialog"
      aria-modal="true"
      aria-labelledby="package-adjustment-title"
      aria-describedby="package-adjustment-description"
      ref={dialogRef}
    >
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onCancel} />

      <div className="relative w-full max-w-lg bg-white rounded-[18px] shadow-2xl p-8 space-y-6">
        <header className="flex items-start justify-between gap-4">
          <div className="flex items-center gap-3">
            <span className="w-12 h-12 rounded-full bg-[#FBEFD0] flex items-center justify-center text-[#B58A1A]">
              <AlertTriangle className="w-6 h-6" aria-hidden />
            </span>
            <div>
              <h2 id="package-adjustment-title" className="text-xl font-semibold text-[#1A1A1A]">
                Este cambio afecta packs existentes
              </h2>
              <p id="package-adjustment-description" className="mt-1 text-sm text-[#6A6A6A] leading-relaxed">
                Bajar este stock deja los siguientes packs sin suficientes componentes. Si
                confirmas, se ajustan automáticamente a la cantidad máxima que soportan.
              </p>
            </div>
          </div>
          <button
            type="button"
            onClick={onCancel}
            disabled={isLoading}
            className="w-10 h-10 flex items-center justify-center rounded-full bg-[#F7F7F7] text-[#6A6A6A] hover:text-[#1A1A1A] hover:bg-[#E0E0E0] transition disabled:opacity-60"
            aria-label="Cerrar"
          >
            <X className="w-5 h-5" />
          </button>
        </header>

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

        <div className="flex flex-col sm:flex-row gap-3 sm:justify-end pt-2">
          <button
            type="button"
            onClick={onCancel}
            disabled={isLoading}
            className="h-[52px] px-5 border border-[#E0E0E0] rounded-xl text-[#1A1A1A] hover:border-[#4B236A] hover:text-[#4B236A] transition disabled:opacity-60 disabled:cursor-not-allowed"
          >
            Cancelar edición
          </button>
          <button
            type="button"
            onClick={onConfirm}
            disabled={isLoading}
            className="h-[52px] px-6 rounded-xl font-semibold shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed bg-[#4B236A] hover:bg-[#5D2B7D] text-white"
          >
            {isLoading && (
              <span className="w-4 h-4 border-2 border-white/60 border-t-transparent rounded-full animate-spin" aria-hidden />
            )}
            <span>Confirmar y ajustar packs</span>
          </button>
        </div>
      </div>
    </div>
  );
}
