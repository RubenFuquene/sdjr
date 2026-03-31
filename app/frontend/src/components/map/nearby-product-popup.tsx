import type { DiscoverMapPin } from "@/types/app-catalog.adapters";

type NearbyProductPopupProps = {
  pin: DiscoverMapPin;
};

function formatPriceRange(minPrice: number, maxPrice: number): string {
  if (minPrice === maxPrice) {
    return `$${minPrice.toLocaleString("es-CO")}`;
  }

  return `$${minPrice.toLocaleString("es-CO")} - $${maxPrice.toLocaleString("es-CO")}`;
}

export function NearbyProductPopup({ pin }: NearbyProductPopupProps) {
  return (
    <div className="space-y-2" role="dialog" aria-label="Información de sucursal cercana">
      <div className="font-semibold text-[#2E2E2E] text-sm">{pin.title}</div>

      <div className="text-xs text-[#7A2E9A]">{pin.subtitle}</div>

      <div className="pt-2 border-t border-[#E6E6E6] space-y-1">
        <p className="text-xs text-[#2E2E2E]">Distancia: {pin.distanceKm.toFixed(1)} km</p>
        <p className="text-xs text-[#2E2E2E]">Productos: {pin.productCount}</p>
        <p className="text-xs text-[#2E2E2E]">Rango: {formatPriceRange(pin.minPrice, pin.maxPrice)}</p>
      </div>
    </div>
  );
}
