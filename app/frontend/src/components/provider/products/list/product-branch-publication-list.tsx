"use client";

import { useEffect, useState } from "react";
import { toast } from "sonner";
import { ApiError, updateProductBranchPublication } from "@/lib/api";
import type { ProductCommerceBranchFromAPI } from "@/types/products";

interface ProductBranchPublicationListProps {
  productId: number;
  branches: ProductCommerceBranchFromAPI[];
}

function extractFirstErrorMessage(data: unknown): string | undefined {
  if (!data || typeof data !== "object") {
    return undefined;
  }

  const errors = (data as { errors?: unknown }).errors;
  if (!errors || typeof errors !== "object") {
    return undefined;
  }

  const firstValue = Object.values(errors as Record<string, unknown>)[0];

  if (Array.isArray(firstValue) && firstValue.length > 0) {
    return String(firstValue[0]);
  }

  if (typeof firstValue === "string") {
    return firstValue;
  }

  return undefined;
}

function translatePublicationError(err: ApiError): string {
  const detail = extractFirstErrorMessage(err.data) ?? err.message;

  if (detail.toLowerCase().includes("zero available quantity")) {
    return "No puedes publicar una sede sin inventario cargado.";
  }

  if (detail.toLowerCase().includes("packages cannot be published")) {
    return "Los packs aún no se pueden publicar.";
  }

  if (detail.toLowerCase().includes("not assigned to the given branch")) {
    return "Este producto ya no está asignado a esa sede.";
  }

  return "No pudimos actualizar la publicación. Intenta nuevamente.";
}

/**
 * Interruptor de publicación por sede en la tarjeta del listado (SCRUM-277
 * Fase 1, Tarea 5). Actualización optimista: refleja el cambio de inmediato
 * y revierte si el servidor lo rechaza (ej. sede sin inventario), en vez de
 * esperar una recarga completa del listado para cada toggle.
 */
export function ProductBranchPublicationList({
  productId,
  branches,
}: ProductBranchPublicationListProps) {
  const [localBranches, setLocalBranches] = useState(branches);
  const [pendingBranchId, setPendingBranchId] = useState<number | null>(null);

  useEffect(() => {
    setLocalBranches(branches);
  }, [branches]);

  const publishedCount = localBranches.filter((branch) => branch.is_published).length;

  const handleToggle = async (branch: ProductCommerceBranchFromAPI) => {
    const nextPublished = !branch.is_published;
    const previousBranches = localBranches;

    setPendingBranchId(branch.id);
    setLocalBranches((current) =>
      current.map((item) => (item.id === branch.id ? { ...item, is_published: nextPublished } : item))
    );

    try {
      await updateProductBranchPublication(productId, branch.id, nextPublished);
    } catch (err) {
      setLocalBranches(previousBranches);
      toast.error(
        err instanceof ApiError
          ? translatePublicationError(err)
          : "Error inesperado al actualizar la publicación."
      );
    } finally {
      setPendingBranchId(null);
    }
  };

  if (localBranches.length === 0) {
    return (
      <p className="text-sm text-[#6A6A6A]">Asigna una sede al producto para poder publicarlo.</p>
    );
  }

  return (
    <div className="space-y-2">
      <div className="flex items-center justify-between gap-2">
        <span className="text-sm text-[#6A6A6A]">Publicación por sede</span>
        {publishedCount === 0 ? (
          <span className="text-xs font-medium text-red-600">No visible para clientes</span>
        ) : (
          <span className="text-xs font-medium text-green-700">
            Publicado en {publishedCount} de {localBranches.length} sedes
          </span>
        )}
      </div>

      <ul className="space-y-1">
        {localBranches.map((branch) => {
          const canPublish = (branch.quantity_available ?? 0) > 0;
          const isPending = pendingBranchId === branch.id;

          return (
            <li key={branch.id} className="flex items-center justify-between gap-2 text-sm">
              <span className="min-w-0 truncate" title={branch.name}>
                <span className="text-[#1A1A1A]">{branch.name}</span>
                <span className={canPublish ? "text-[#6A6A6A]" : "text-red-600"}>
                  {" "}
                  · {branch.quantity_available ?? 0} unidades
                </span>
              </span>
              <button
                type="button"
                role="switch"
                aria-checked={branch.is_published}
                aria-label={`${branch.is_published ? "Despublicar" : "Publicar"} en ${branch.name}${
                  !canPublish && !branch.is_published ? " (requiere inventario)" : ""
                }`}
                disabled={isPending || (!branch.is_published && !canPublish)}
                onClick={() => handleToggle(branch)}
                // Base de posición conocida (inline-flex, sin position:absolute):
                // el thumb parte de left:0 dentro del track y se desplaza con
                // translate-x, sin depender de la "static position" implícita
                // que un span absolute sin left explícito deja a criterio del
                // navegador (causaba que el thumb se saliera del track).
                className={`inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors disabled:opacity-50 disabled:cursor-not-allowed ${
                  branch.is_published ? "bg-[#4B236A]" : "bg-[#E0E0E0]"
                }`}
              >
                <span
                  className={`inline-block h-5 w-5 rounded-full bg-white shadow transform transition-transform ${
                    branch.is_published ? "translate-x-5" : "translate-x-0.5"
                  }`}
                />
              </button>
            </li>
          );
        })}
      </ul>
    </div>
  );
}
