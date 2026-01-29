"use client";

import { ReactNode, useEffect } from "react";
import { AlertTriangle, Info, ShieldAlert, X } from "lucide-react";

export type ConfirmationDialogVariant = "danger" | "warning" | "info";

interface ConfirmationDialogProps {
  isOpen: boolean;
  title: string;
  description?: ReactNode;
  confirmText?: string;
  cancelText?: string;
  variant?: ConfirmationDialogVariant;
  isLoading?: boolean;
  onConfirm: () => void | Promise<void>;
  onClose: () => void;
}

const variantStyles: Record<ConfirmationDialogVariant, {
  accent: string;
  confirm: string;
  iconColor: string;
}> = {
  danger: {
    accent: "text-red-600",
    confirm: "bg-red-600 hover:bg-red-700 text-white",
    iconColor: "text-red-600",
  },
  warning: {
    accent: "text-[#C8D86D]",
    confirm: "bg-[#4B236A] hover:bg-[#5D2B7D] text-white",
    iconColor: "text-[#C8D86D]",
  },
  info: {
    accent: "text-[#4B236A]",
    confirm: "bg-[#4B236A] hover:bg-[#5D2B7D] text-white",
    iconColor: "text-[#4B236A]",
  },
};

const variantIcon: Record<ConfirmationDialogVariant, typeof Info> = {
  danger: ShieldAlert,
  warning: AlertTriangle,
  info: Info,
};

export function ConfirmationDialog({
  isOpen,
  title,
  description,
  confirmText = "Confirmar",
  cancelText = "Cancelar",
  variant = "danger",
  isLoading = false,
  onConfirm,
  onClose,
}: ConfirmationDialogProps) {
  // Cierra con tecla ESC para accesibilidad
  useEffect(() => {
    if (!isOpen) return;
    const handler = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        onClose();
      }
    };
    window.addEventListener("keydown", handler);
    return () => window.removeEventListener("keydown", handler);
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  const Icon = variantIcon[variant];
  const styles = variantStyles[variant];

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center px-4" role="dialog" aria-modal="true" aria-labelledby="confirmation-title">
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />

      <div className="relative w-full max-w-md bg-white rounded-[18px] shadow-2xl p-8 space-y-6">
        <header className="flex items-start justify-between gap-4">
          <div className="flex items-center gap-3">
            <span className={`w-12 h-12 rounded-full bg-[#DDE8BB] flex items-center justify-center ${styles.iconColor}`}>
              <Icon className="w-6 h-6" aria-hidden />
            </span>
            <div>
              <h2 id="confirmation-title" className="text-xl font-semibold text-[#1A1A1A]">
                {title}
              </h2>
              {description && (
                <p className="mt-1 text-sm text-[#6A6A6A] leading-relaxed">
                  {description}
                </p>
              )}
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="w-10 h-10 flex items-center justify-center rounded-full bg-[#F7F7F7] text-[#6A6A6A] hover:text-[#1A1A1A] hover:bg-[#E0E0E0] transition"
            aria-label="Cerrar"
          >
            <X className="w-5 h-5" />
          </button>
        </header>

        <div className="flex flex-col gap-3">
          <div className="flex flex-col gap-2">
            <p className={`text-sm ${styles.accent}`}>
              Esta acción puede afectar datos de forma permanente.
            </p>
          </div>

          <div className="flex flex-col sm:flex-row gap-3 sm:justify-end pt-2">
            <button
              type="button"
              onClick={onClose}
              disabled={isLoading}
              className="h-[52px] px-5 border border-[#E0E0E0] rounded-xl text-[#1A1A1A] hover:border-[#4B236A] hover:text-[#4B236A] transition disabled:opacity-60 disabled:cursor-not-allowed"
            >
              {cancelText}
            </button>
            <button
              type="button"
              onClick={onConfirm}
              disabled={isLoading}
              className={`h-[52px] px-6 rounded-xl font-semibold shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed ${styles.confirm}`}
            >
              {isLoading && (
                <span className="w-4 h-4 border-2 border-white/60 border-t-transparent rounded-full animate-spin" aria-hidden />
              )}
              <span>{confirmText}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
