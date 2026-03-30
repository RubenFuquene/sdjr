"use client";

import dynamic from "next/dynamic";
import Image from "next/image";
import Link from "next/link";
import { Bell, MapPin } from "lucide-react";
import { LocationStatusBanner } from "@/components/shared/location-status-banner";
import { useUserLocation } from "@/hooks/use-user-location";

const UserLocationMap = dynamic(
  () => import("@/components/map/user-location-map").then((mod) => ({ default: mod.UserLocationMap })),
  {
    ssr: false,
    loading: () => (
      <div className="flex h-full w-full items-center justify-center rounded-2xl bg-[var(--color-app-ui-background-soft)]">
        <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Cargando mapa...</p>
      </div>
    ),
  }
);

export default function AppDiscoverPage() {
  const { location, state, refresh } = useUserLocation();
  const isLoading = state === "loading" || state === "idle";
  const locationLabel = location
    ? `${location.lat.toFixed(4)}, ${location.lng.toFixed(4)}`
    : "Ubicacion pendiente";

  return (
    <section className="px-4 pb-6 pt-4">
      <header className="app-page-header app-page-header-accent">
        <div className="flex items-center justify-between gap-4">
          <div className="flex items-center gap-3">
            <Image
              src="/brand/app/sumass-logo.png"
              alt="Sumass"
              width={48}
              height={48}
              className="h-12 w-12 rounded-full bg-white object-contain p-1"
              priority
            />
            <div>
              <h1 className="text-xl text-[var(--color-app-text-primary-purple)]">Descubre</h1>
              <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Tu Sumass al planeta</p>
            </div>
          </div>

          <Link
            href="/app/notifications"
            className="rounded-xl bg-white p-2 text-[var(--color-app-text-primary-purple)] shadow-[var(--app-shadow-button)]"
            aria-label="Notificaciones"
          >
            <Bell className="h-5 w-5" />
          </Link>
        </div>

        <div className="mt-4 flex items-center gap-2 rounded-xl bg-white/80 px-3 py-2 text-sm text-[var(--color-app-text-primary-purple)]">
          <MapPin className="h-4 w-4" />
          <span className="truncate">{locationLabel}</span>
        </div>
      </header>

      <div className="mt-4">
        <LocationStatusBanner />
      </div>

      <div className="app-surface mt-4 overflow-hidden border border-[var(--color-app-ui-divider)]">
        <div className="border-b border-[var(--color-app-ui-divider)] px-4 py-3">
          <h2 className="text-base text-[var(--color-app-text-dark)]">Mapa de comercios cercanos</h2>
          <p className="text-sm text-[var(--color-app-text-secondary-purple)]">
            Usa tu ubicacion actual para ver opciones disponibles.
          </p>
        </div>
        <div className="h-[320px] w-full">
          <UserLocationMap location={location} isLoading={isLoading} onRefresh={refresh} />
        </div>
      </div>

      <div className="mt-4 grid grid-cols-2 gap-2">
        <Link
          href="/app/store/1"
          className="app-card-action rounded-xl bg-[var(--color-app-ui-background)] px-3 py-3 text-center text-sm text-[var(--color-app-text-primary-purple)] shadow-[var(--app-shadow-card)]"
        >
          Ver tienda
        </Link>
        <Link
          href="/app/product/1"
          className="app-btn-primary"
        >
          Ver producto
        </Link>
      </div>
    </section>
  );
}
