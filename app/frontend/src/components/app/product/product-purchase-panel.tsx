"use client";

import Link from "next/link";
import { useState } from "react";
import { Minus, Plus } from "lucide-react";

interface ProductPurchasePanelProps {
  productId: number;
  branchId: number | null;
  price: number;
  maxQuantity: number;
}

function formatPrice(value: number): string {
  return `$${value.toLocaleString("es-CO")}`;
}

/**
 * qty viaja como id suelto (no dato de negocio duplicado) igual que
 * productId/branchId: el carrito vuelve a pedir el producto por id y usa
 * qty solo como valor inicial editable (ver Fase 2 SCRUM-291).
 */
function buildCartHref(productId: number, branchId: number | null, quantity: number): string {
  const params = new URLSearchParams({ productId: String(productId), qty: String(quantity) });

  if (branchId) {
    params.set("branchId", String(branchId));
  }

  return `/app/cart?${params.toString()}`;
}

/**
 * Selector de cantidad + total en vivo del detalle de producto (CA-02/CA-04
 * de SCRUM-158). Extraído a client component porque la página de detalle es
 * server component (fetch de datos); la interactividad vive aquí.
 */
export function ProductPurchasePanel({ productId, branchId, price, maxQuantity }: ProductPurchasePanelProps) {
  const safeMax = Math.max(maxQuantity, 1);
  const [quantity, setQuantity] = useState(1);
  const total = price * quantity;

  return (
    <>
      <div className="app-surface p-4">
        <h2 className="text-base text-[var(--color-app-text-dark)]">Cantidad</h2>
        <div className="mt-3 flex items-center gap-3">
          <button
            type="button"
            onClick={() => setQuantity((prev) => Math.max(1, prev - 1))}
            disabled={quantity <= 1}
            className="flex h-10 w-10 items-center justify-center rounded-full border-2 border-[var(--color-app-text-primary-purple)] text-[var(--color-app-text-primary-purple)] transition hover:bg-[var(--color-app-ui-background-soft)] disabled:cursor-not-allowed disabled:opacity-50"
            aria-label="Disminuir cantidad"
          >
            <Minus className="h-4 w-4" />
          </button>
          <span className="w-8 text-center text-xl text-[var(--color-app-text-dark)]">{quantity}</span>
          <button
            type="button"
            onClick={() => setQuantity((prev) => Math.min(safeMax, prev + 1))}
            disabled={quantity >= safeMax}
            className="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--color-app-text-primary-purple)] text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
            aria-label="Aumentar cantidad"
          >
            <Plus className="h-4 w-4" />
          </button>
          <span className="ml-2 text-sm text-[var(--color-app-text-secondary-purple)]">
            Máximo {safeMax} disponibles
          </span>
        </div>
      </div>

      <div className="app-surface p-4">
        <div className="flex items-center justify-between gap-3">
          <div>
            <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Total</p>
            <p className="text-2xl text-[var(--color-app-text-dark)]">{formatPrice(total)}</p>
          </div>

          <Link href={buildCartHref(productId, branchId, quantity)} className="app-btn-primary gap-2">
            Agregar al carrito
          </Link>
        </div>
      </div>
    </>
  );
}