"use client";

import { priceWithinPack } from "./product-form.utils";

interface PackItemOption {
  id: number;
  title: string;
  originalPrice: number;
  discountedPrice?: number | null;
  quantityAvailable: number;
  availableForPackaging: number;
}

interface ProductPackItemsSelectorProps {
  options: PackItemOption[];
  selectedItems: Array<{ productId: number; quantity: number }>;
  disabled?: boolean;
  error?: string;
  onToggle: (productId: number) => void;
  onQuantityChange: (productId: number, quantity: number) => void;
  /** SCRUM-361, Tarea 6.7: mensaje honesto que nombra la sede sin candidatos, en vez del genérico. */
  emptyStateMessage?: string;
  /**
   * Ticket derivado de SCRUM-361/323 (2026-08-04): descuento opcional del
   * pack (null si el aliado no puso uno) y su techo — necesarios para
   * prorratear P3 en vivo por componente.
   */
  packDiscountedPrice: number | null;
  packCeiling: number;
  /** Suma de los precios de lista (sin descuento) de los componentes seleccionados. */
  packListPrice: number;
}

function formatCurrency(value: number): string {
  return `$${value.toLocaleString("es-CO")}`;
}

export function ProductPackItemsSelector({
  options,
  selectedItems,
  disabled = false,
  error,
  onToggle,
  onQuantityChange,
  emptyStateMessage,
  packDiscountedPrice,
  packCeiling,
  packListPrice,
}: ProductPackItemsSelectorProps) {
  const selectedById = new Map(selectedItems.map((item) => [item.productId, item.quantity]));

  const selectedOptions = options.filter((option) => selectedById.has(option.id));

  // Ajuste 2026-08-04: los totales se derivan de packListPrice/packCeiling/
  // packDiscountedPrice directamente, NO sumando las filas ya redondeadas a
  // centavos de "Dentro del pack" — sumar filas independientemente
  // redondeadas puede acumular un centavo de diferencia frente al total real
  // (ej. dos filas de 41.785,71 y 46.428,57 sumaban un centavo de más de
  // "ahorro adicional" aunque cada precio individual mostrado era correcto).
  const totals = {
    listTotal: packListPrice,
    componentSavings: Number((packListPrice - packCeiling).toFixed(2)),
    packSavings: packDiscountedPrice === null ? 0 : Number((packCeiling - packDiscountedPrice).toFixed(2)),
  };

  return (
    <div className="space-y-2">
      <p className="text-sm font-medium text-[#1A1A1A]">Productos del Pack *</p>

      {options.length === 0 ? (
        <div className="rounded-[14px] border border-[#E0E0E0] bg-[#F7F7F7] p-4">
          <p className="text-sm text-[#6A6A6A]">
            {emptyStateMessage ?? "No hay productos individuales disponibles para armar packs."}
          </p>
        </div>
      ) : (
        <div className="max-h-56 overflow-y-auto rounded-[14px] border border-[#E0E0E0] p-2 space-y-2">
          {options.map((option) => {
            const selectedQuantity = selectedById.get(option.id) ?? 1;
            const checked = selectedById.has(option.id);
            const ownSalePrice = option.discountedPrice ?? option.originalPrice;
            const hasOwnDiscount = option.discountedPrice != null && option.discountedPrice < option.originalPrice;
            const withinPack = checked
              ? priceWithinPack({ componentSalePrice: ownSalePrice, packCeiling, packDiscountedPrice })
              : null;

            return (
              <div
                key={option.id}
                className="flex items-start gap-3 rounded-[12px] px-3 py-2 hover:bg-[#F7F7F7]"
              >
                <input
                  type="checkbox"
                  checked={checked}
                  disabled={disabled}
                  onChange={() => onToggle(option.id)}
                  className="mt-1 h-4 w-4 accent-[#4B236A]"
                />
                <span className="flex-1 text-sm">
                  <span className="block text-[#1A1A1A]">{option.title}</span>
                  <span className="block text-[#6A6A6A]">
                    {hasOwnDiscount ? (
                      <>
                        <span
                          className="line-through"
                          aria-label={`Precio de lista: ${formatCurrency(option.originalPrice)}`}
                        >
                          {formatCurrency(option.originalPrice)}
                        </span>{" "}
                        <span>Con descuento: {formatCurrency(ownSalePrice)}</span>
                      </>
                    ) : (
                      <span>{formatCurrency(ownSalePrice)}</span>
                    )}
                    {" "}· Disponible: {option.quantityAvailable} · Disponible para empacar:{" "}
                    {option.availableForPackaging}
                  </span>
                  {withinPack !== null ? (
                    <span className="block font-medium text-[#1A1A1A]">
                      Dentro del pack: {formatCurrency(withinPack)}
                    </span>
                  ) : null}
                </span>

                {checked ? (
                  <div className="flex flex-col gap-1 min-w-[92px]">
                    <label htmlFor={`pack-item-qty-${option.id}`} className="text-xs text-[#6A6A6A]">
                      Cantidad
                    </label>
                    <input
                      id={`pack-item-qty-${option.id}`}
                      type="number"
                      min={1}
                      max={option.availableForPackaging}
                      value={selectedQuantity}
                      disabled={disabled}
                      onChange={(event) => {
                        const parsed = Number(event.target.value);

                        if (!Number.isFinite(parsed)) {
                          return;
                        }

                        onQuantityChange(option.id, Math.trunc(parsed));
                      }}
                      className="h-9 w-[92px] rounded-[10px] border border-[#E0E0E0] px-2 text-sm text-[#1A1A1A] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4B236A]/30 disabled:opacity-60 disabled:cursor-not-allowed"
                    />
                  </div>
                ) : null}
              </div>
            );
          })}
        </div>
      )}

      {selectedOptions.length > 0 ? (
        <div className="rounded-[14px] border border-[#E0E0E0] bg-[#F7F7F7] p-3 text-sm text-[#1A1A1A] space-y-1">
          <p>
            Total de lista: <span className="font-medium">{formatCurrency(totals.listTotal)}</span>
          </p>
          <p>
            Ahorro que ya traían los productos:{" "}
            <span className="font-medium">{formatCurrency(totals.componentSavings)}</span>
          </p>
          <p>
            Ahorro adicional del pack: <span className="font-medium">{formatCurrency(totals.packSavings)}</span>
          </p>
        </div>
      ) : null}

      {error ? <p className="text-sm text-red-600">{error}</p> : null}
    </div>
  );
}
