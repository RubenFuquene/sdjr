"use client";

import dynamic from "next/dynamic";
import { LocationStatusBanner } from "@/components/shared/location-status-banner";
import { useUserLocation } from "@/hooks/use-user-location";

// Lazy load del mapa para evitar error de SSR (Leaflet necesita window)
const UserLocationMap = dynamic(
  () => import("@/components/map/user-location-map").then(mod => ({ default: mod.UserLocationMap })),
  {
    ssr: false,
    loading: () => (
      <div className="w-full h-full bg-gray-200 animate-pulse rounded-lg flex items-center justify-center">
        <p className="text-gray-500 text-sm">Cargando mapa...</p>
      </div>
    ),
  }
);

export default function AppDashboardPage() {
  const { location, state, refresh } = useUserLocation();

  const isLoading = state === "loading" || state === "idle";

  return (
    <div className="mx-auto w-full max-w-4xl px-6 py-10">
      {/* Banner de estado de ubicación - no intrusivo */}
      <LocationStatusBanner />

      {/* Mapa con ubicación del usuario */}
      <div className="mt-8">
        <h2 className="mb-4 text-lg font-semibold text-[#2E2E2E]">
          Tu ubicación
        </h2>
        <div className="overflow-hidden rounded-2xl border border-[#E6E6E6] shadow-sm md:shadow-md">
          {/* Mobile: altura 300px, Desktop: 400px */}
          <div className="h-[300px] w-full sm:h-[350px] md:h-[400px]">
            <UserLocationMap
              location={location}
              isLoading={isLoading}
              onRefresh={refresh}
            />
          </div>
        </div>
      </div>
    </div>
  );
}
