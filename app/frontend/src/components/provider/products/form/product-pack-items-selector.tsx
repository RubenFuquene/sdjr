"use client";

interface PackItemOption {
  id: number;
  title: string;
  originalPrice: number;
  quantityAvailable: number;
}

interface ProductPackItemsSelectorProps {
  options: PackItemOption[];
  selectedItems: Array<{ productId: number; quantity: number }>;
  disabled?: boolean;
  error?: string;
  onToggle: (productId: number) => void;
  onQuantityChange: (productId: number, quantity: number) => void;
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
}: ProductPackItemsSelectorProps) {
  const selectedById = new Map(selectedItems.map((item) => [item.productId, item.quantity]));

  return (
    <div className="space-y-2">
      <p className="text-sm font-medium text-[#1A1A1A]">Productos del Pack *</p>

      {options.length === 0 ? (
        <div className="rounded-[14px] border border-[#E0E0E0] bg-[#F7F7F7] p-4">
          <p className="text-sm text-[#6A6A6A]">
            No hay productos individuales disponibles para armar packs.
          </p>
        </div>
      ) : (
        <div className="max-h-56 overflow-y-auto rounded-[14px] border border-[#E0E0E0] p-2 space-y-2">
          {options.map((option) => {
            const selectedQuantity = selectedById.get(option.id) ?? 1;
            const checked = selectedById.has(option.id);

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
                    {formatCurrency(option.originalPrice)} · Disponible: {option.quantityAvailable}
                  </span>
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
                      max={option.quantityAvailable}
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

      {error ? <p className="text-sm text-red-600">{error}</p> : null}
    </div>
  );
}
