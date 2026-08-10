"use client";

import { useEffect, useRef, type ReactNode } from "react";
import { AlertTriangle, X } from "lucide-react";

interface ConfirmationDialogShellProps {
  isOpen: boolean;
  titleId: string;
  descriptionId: string;
  title: string;
  description: ReactNode;
  confirmLabel: string;
  cancelLabel?: string;
  isLoading?: boolean;
  onConfirm: () => void | Promise<void>;
  onCancel: () => void;
  /** Cuerpo del diálogo — listas u otro contenido específico de cada caso. */
  children?: ReactNode;
}

const FOCUSABLE_SELECTOR =
  'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';

/**
 * SCRUM-362, Tarea 5.7: carcasa de modal (focus trap, ESC, backdrop, header
 * con ícono/título/cierre, footer con cancelar/confirmar) extraída de
 * PackageAdjustmentConfirmationDialog (SCRUM-361) para que un segundo
 * diálogo de confirmación con impacto (FiscalReclassificationConfirmationDialog)
 * no duplique la mecánica — solo cambia el contenido, vía `children`.
 */
export function ConfirmationDialogShell({
  isOpen,
  titleId,
  descriptionId,
  title,
  description,
  confirmLabel,
  cancelLabel = "Cancelar edición",
  isLoading = false,
  onConfirm,
  onCancel,
  children,
}: ConfirmationDialogShellProps) {
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
      aria-labelledby={titleId}
      aria-describedby={descriptionId}
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
              <h2 id={titleId} className="text-xl font-semibold text-[#1A1A1A]">
                {title}
              </h2>
              <p id={descriptionId} className="mt-1 text-sm text-[#6A6A6A] leading-relaxed">
                {description}
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

        {children}

        <div className="flex flex-col sm:flex-row gap-3 sm:justify-end pt-2">
          <button
            type="button"
            onClick={onCancel}
            disabled={isLoading}
            className="h-[52px] px-5 border border-[#E0E0E0] rounded-xl text-[#1A1A1A] hover:border-[#4B236A] hover:text-[#4B236A] transition disabled:opacity-60 disabled:cursor-not-allowed"
          >
            {cancelLabel}
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
            <span>{confirmLabel}</span>
          </button>
        </div>
      </div>
    </div>
  );
}
