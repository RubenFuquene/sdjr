/**
 * Skeleton loaders para estados de carga
 * Usa shimmer effect y estilos Figma
 */

export function TableLoadingState() {
  return (
    <div className="w-full space-y-4 animate-pulse">
      {/* Header row */}
      <div className="flex gap-4 px-6 py-3 bg-[#F7F7F7] rounded-[14px]">
        {[1, 2, 3, 4, 5, 6].map((i) => (
          <div key={i} className="h-4 bg-[#E0E0E0] rounded flex-1" />
        ))}
      </div>

      {/* Data rows */}
      {[1, 2, 3, 4, 5].map((row) => (
        <div key={row} className="flex gap-4 px-6 py-4 bg-white rounded-[14px] border border-[#E0E0E0]">
          {[1, 2, 3, 4, 5, 6].map((col) => (
            <div key={col} className="h-4 bg-[#F7F7F7] rounded flex-1" />
          ))}
        </div>
      ))}
    </div>
  );
}

export function CardLoadingState() {
  return (
    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      {[1, 2, 3, 4, 5, 6].map((i) => (
        <div key={i} className="bg-white rounded-[18px] border border-[#E0E0E0] p-6 space-y-4 animate-pulse">
          <div className="h-6 bg-[#F7F7F7] rounded w-2/3" />
          <div className="h-4 bg-[#F7F7F7] rounded w-full" />
          <div className="h-4 bg-[#F7F7F7] rounded w-4/5" />
          <div className="flex gap-2 mt-4">
            <div className="h-6 bg-[#F7F7F7] rounded w-16" />
            <div className="h-6 bg-[#F7F7F7] rounded w-16" />
          </div>
        </div>
      ))}
    </div>
  );
}
