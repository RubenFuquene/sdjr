"use client";

import type { ProductBranchAssignment } from "@/types/products";

interface BranchOption {
  id: number;
  name: string;
}

interface ProductBranchAssignmentSelectorProps {
  options: BranchOption[];
  selectedItems: ProductBranchAssignment[];
  disabled?: boolean;
  error?: string;
  onToggle: (branchId: number) => void;
  onQuantityChange: (branchId: number, quantity: number) => void;
  onPublishedChange: (branchId: number, isPublished: boolean) => void;
}

export function ProductBranchAssignmentSelector({
  options,
  selectedItems,
  disabled = false,
  error,
  onToggle,
  onQuantityChange,
  onPublishedChange,
}: ProductBranchAssignmentSelectorProps) {
  const selectedByBranchId = new Map(selectedItems.map((item) => [item.branchId, item]));

  return (
    <div className="space-y-2">
      <p className="text-sm font-medium text-[#1A1A1A]">Sedes y disponibilidad *</p>

      {options.length === 0 ? (
        <div className="rounded-[14px] border border-[#E0E0E0] bg-[#F7F7F7] p-4">
          <p className="text-sm text-[#6A6A6A]">No hay sucursales disponibles para asignar.</p>
        </div>
      ) : (
        <div
          className="max-h-64 overflow-y-auto rounded-[14px] border border-[#E0E0E0] p-2 space-y-2"
          aria-live="polite"
        >
          {options.map((option) => {
            const selected = selectedByBranchId.get(option.id);

            return (
              <div
                key={option.id}
                className="flex flex-wrap items-start gap-3 rounded-[12px] px-3 py-2 hover:bg-[#F7F7F7]"
              >
                <input
                  id={`branch-assign-${option.id}`}
                  type="checkbox"
                  checked={Boolean(selected)}
                  disabled={disabled}
                  onChange={() => onToggle(option.id)}
                  className="mt-1 h-4 w-4 accent-[#4B236A]"
                />
                <label
                  htmlFor={`branch-assign-${option.id}`}
                  className="flex-1 min-w-[140px] text-sm text-[#1A1A1A] pt-1"
                >
                  {option.name}
                </label>

                {selected ? (
                  <>
                    <div className="flex flex-col gap-1 min-w-[92px]">
                      <label
                        htmlFor={`branch-assign-qty-${option.id}`}
                        className="text-xs text-[#6A6A6A]"
                      >
                        Cantidad
                      </label>
                      <input
                        id={`branch-assign-qty-${option.id}`}
                        type="number"
                        min={0}
                        value={selected.quantityAvailable}
                        disabled={disabled}
                        onChange={(event) => {
                          const parsed = Number(event.target.value);

                          if (!Number.isFinite(parsed)) {
                            return;
                          }

                          onQuantityChange(option.id, Math.max(0, Math.trunc(parsed)));
                        }}
                        className="h-9 w-[92px] rounded-[10px] border border-[#E0E0E0] px-2 text-sm text-[#1A1A1A] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4B236A]/30 disabled:opacity-60 disabled:cursor-not-allowed"
                      />
                    </div>

                    <div className="flex flex-col gap-1 min-w-[110px]">
                      <span className="text-xs text-[#6A6A6A]">Publicar</span>
                      <label
                        htmlFor={`branch-assign-pub-${option.id}`}
                        className="flex items-center gap-2 h-9 text-xs text-[#6A6A6A]"
                      >
                        <input
                          id={`branch-assign-pub-${option.id}`}
                          type="checkbox"
                          checked={selected.isPublished}
                          disabled={disabled || selected.quantityAvailable === 0}
                          onChange={(event) => onPublishedChange(option.id, event.target.checked)}
                          className="h-4 w-4 accent-[#4B236A] disabled:opacity-60"
                        />
                        {selected.quantityAvailable > 0 ? "Visible a clientes" : "Requiere stock"}
                      </label>
                    </div>
                  </>
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
