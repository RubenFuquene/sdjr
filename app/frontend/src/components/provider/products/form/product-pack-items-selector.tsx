"use client";

interface PackItemOption {
  id: number;
  title: string;
  originalPrice: number;
  quantityAvailable: number;
}

interface ProductPackItemsSelectorProps {
  options: PackItemOption[];
  selectedIds: number[];
  disabled?: boolean;
  error?: string;
  onToggle: (productId: number) => void;
}

function formatCurrency(value: number): string {
  return `$${value.toLocaleString("es-CO")}`;
}

export function ProductPackItemsSelector({
  options,
  selectedIds,
  disabled = false,
  error,
  onToggle,
}: ProductPackItemsSelectorProps) {
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
            const checked = selectedIds.includes(option.id);

            return (
              <label
                key={option.id}
                className="flex items-start gap-3 rounded-[12px] px-3 py-2 hover:bg-[#F7F7F7] cursor-pointer"
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
              </label>
            );
          })}
        </div>
      )}

      {error ? <p className="text-sm text-red-600">{error}</p> : null}
    </div>
  );
}
