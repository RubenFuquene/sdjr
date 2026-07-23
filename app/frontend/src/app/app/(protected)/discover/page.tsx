"use client";

import dynamic from "next/dynamic";
import Image from "next/image";
import Link from "next/link";
import { useEffect, useState } from "react";
import { Bell, Clock3, MapPin } from "lucide-react";
import { LocationStatusBanner } from "@/components/shared/location-status-banner";
import { useUserLocation } from "@/hooks/use-user-location";
import { getNearbyProducts } from "@/lib/api/app-catalog";
import {
  mapNearbyProductsToDiscoverCards,
  mapNearbyProductsToMapPins,
  type DiscoverMapPin,
  type DiscoverNearbyCard,
} from "@/types/app-catalog.adapters";

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
  const [nearbyCards, setNearbyCards] = useState<DiscoverNearbyCard[]>([]);
  const [nearbyPins, setNearbyPins] = useState<DiscoverMapPin[]>([]);
  const [isNearbyLoading, setIsNearbyLoading] = useState(false);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [nearbyError, setNearbyError] = useState<string | null>(null);
  const [searchRadiusKm, setSearchRadiusKm] = useState<number | null>(null);
  const [selectedCategoryId] = useState<number | null>(null);
  const [selectedMaxPrice] = useState<number | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [retryTick, setRetryTick] = useState(0);

  useEffect(() => {
    if (state !== "ready" || !location) {
      setNearbyCards([]);
      setNearbyPins([]);
      setIsNearbyLoading(false);
      setNearbyError(null);
      return;
    }

    const currentLocation = location;

    let cancelled = false;

    async function loadNearbyProducts() {
      setIsNearbyLoading(true);
      setNearbyError(null);
      setCurrentPage(1);
      setLastPage(1);

      try {
        const response = await getNearbyProducts({
          latitude: currentLocation.lat,
          longitude: currentLocation.lng,
          radius: searchRadiusKm ?? undefined,
          categoryId: selectedCategoryId ?? undefined,
          maxPrice: selectedMaxPrice ?? undefined,
          page: 1,
          perPage: 6,
        });

        if (cancelled) {
          return;
        }

        setNearbyCards(mapNearbyProductsToDiscoverCards(response));
        setNearbyPins(mapNearbyProductsToMapPins(response));
        setCurrentPage(response.meta?.current_page ?? 1);
        setLastPage(response.meta?.last_page ?? 1);
      } catch {
        if (!cancelled) {
          setNearbyCards([]);
          setNearbyPins([]);
          setNearbyError("No se pudieron obtener productos cercanos en este momento.");
        }
      } finally {
        if (!cancelled) {
          setIsNearbyLoading(false);
        }
      }
    }

    void loadNearbyProducts();

    return () => {
      cancelled = true;
    };
  }, [location, retryTick, searchRadiusKm, selectedCategoryId, selectedMaxPrice, state]);

  const locationLabel = location
    ? `${location.lat.toFixed(4)}, ${location.lng.toFixed(4)}`
    : "Ubicacion pendiente";

  const showLocationDisabledNotice = state === "denied" || (state === "error" && !location);
  const showEmptyNearby = state === "ready" && !isNearbyLoading && !nearbyError && nearbyCards.length === 0;

  const handleRetryNearby = () => {
    setRetryTick((value) => value + 1);
  };

  const handleExpandRadius = () => {
    setSearchRadiusKm(20);
    setRetryTick((value) => value + 1);
  };

  const hasMorePages = currentPage < lastPage;

  const getDiscountPercentage = (price: number, originalPrice: number): number => {
    if (originalPrice <= 0 || originalPrice <= price) {
      return 0;
    }

    return Math.round(((originalPrice - price) / originalPrice) * 100);
  };

  // El detalle de producto obtiene sus propios datos por id (GET /catalog/products/{id});
  // ya no se transportan datos de negocio por query params. branchId es la
  // excepción: es solo un identificador (no datos duplicados) necesario para
  // crear la orden en el carrito, ya que un producto puede vender en varias
  // sucursales y el pedido requiere una específica.
  const buildProductHref = (card: DiscoverNearbyCard): string =>
    card.branchId ? `/app/product/${card.productId}?branchId=${card.branchId}` : `/app/product/${card.productId}`;

  const handleLoadMore = async () => {
    if (!location || state !== "ready" || isLoadingMore || !hasMorePages) {
      return;
    }

    const nextPage = currentPage + 1;
    setIsLoadingMore(true);

    try {
      const response = await getNearbyProducts({
        latitude: location.lat,
        longitude: location.lng,
        radius: searchRadiusKm ?? undefined,
        categoryId: selectedCategoryId ?? undefined,
        maxPrice: selectedMaxPrice ?? undefined,
        page: nextPage,
        perPage: 6,
      });

      const nextCards = mapNearbyProductsToDiscoverCards(response);
      const nextPins = mapNearbyProductsToMapPins(response);
      setNearbyCards((prev) => [...prev, ...nextCards]);
      setNearbyPins((prev) => {
        const byId = new Map<string, DiscoverMapPin>();

        for (const pin of prev) {
          byId.set(pin.id, pin);
        }

        for (const pin of nextPins) {
          byId.set(pin.id, pin);
        }

        return Array.from(byId.values()).sort((a, b) => a.distanceKm - b.distanceKm);
      });
      setCurrentPage(response.meta?.current_page ?? nextPage);
      setLastPage(response.meta?.last_page ?? nextPage);
    } catch {
      setNearbyError("No se pudieron cargar más productos en este momento.");
    } finally {
      setIsLoadingMore(false);
    }
  };

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
          <UserLocationMap location={location} nearbyPins={nearbyPins} isLoading={isLoading} onRefresh={refresh} />
        </div>
      </div>

      <div className="app-surface mt-4 p-4">
        <div className="mb-3 flex items-center justify-between gap-2">
          <h2 className="text-base text-[var(--color-app-text-dark)]">Productos cercanos</h2>
          <span className="text-xs text-[var(--color-app-text-secondary-purple)]">
            Radio: {searchRadiusKm === null ? "Default" : `${searchRadiusKm} km`}
          </span>
        </div>

        <div className="mb-3 flex items-center gap-2">
          <button
            type="button"
            onClick={() => setSearchRadiusKm(null)}
            className={`inline-flex h-8 items-center rounded-xl border px-3 text-xs transition ${
              searchRadiusKm === null
                ? "border-[var(--color-app-text-primary-purple)] bg-[var(--color-app-tomatillo-soft)] text-[var(--color-app-text-primary-purple)]"
                : "border-[var(--color-app-ui-divider)] text-[var(--color-app-text-secondary-purple)] hover:bg-[var(--color-app-ui-background-soft)]"
            }`}
          >
            Auto
          </button>
          {[5, 10, 20].map((radius) => (
            <button
              key={radius}
              type="button"
              onClick={() => setSearchRadiusKm(radius)}
              className={`inline-flex h-8 items-center rounded-xl border px-3 text-xs transition ${
                searchRadiusKm === radius
                  ? "border-[var(--color-app-text-primary-purple)] bg-[var(--color-app-tomatillo-soft)] text-[var(--color-app-text-primary-purple)]"
                  : "border-[var(--color-app-ui-divider)] text-[var(--color-app-text-secondary-purple)] hover:bg-[var(--color-app-ui-background-soft)]"
              }`}
            >
              {radius} km
            </button>
          ))}
        </div>

        {showLocationDisabledNotice && (
          <div className="app-surface-outlined p-3 text-sm text-[var(--color-app-text-secondary-purple)]">
            <p>No podemos obtener productos cercanos porque la ubicacion esta desactivada o no disponible.</p>
            <p className="mt-2 text-xs">Habilita permisos de ubicacion en el navegador y vuelve a intentar.</p>
            <button type="button" onClick={() => refresh()} className="mt-3 inline-flex h-9 items-center rounded-xl border border-[var(--color-app-ui-divider)] px-3 text-xs text-[var(--color-app-text-primary-purple)] transition hover:bg-[var(--color-app-ui-background-soft)]">
              Reintentar ubicacion
            </button>
          </div>
        )}

        {!showLocationDisabledNotice && nearbyError && (
          <div className="app-surface-outlined p-3 text-sm text-[var(--color-app-text-secondary-purple)]">
            <p>{nearbyError}</p>
            <button type="button" onClick={handleRetryNearby} className="mt-3 inline-flex h-9 items-center rounded-xl border border-[var(--color-app-ui-divider)] px-3 text-xs text-[var(--color-app-text-primary-purple)] transition hover:bg-[var(--color-app-ui-background-soft)]">
              Reintentar
            </button>
          </div>
        )}

        {!showLocationDisabledNotice && !nearbyError && isNearbyLoading && (
          <div className="space-y-3">
            <div className="overflow-hidden rounded-[18px] border border-[var(--color-app-ui-divider)] bg-white animate-pulse">
              <div className="h-36 bg-[var(--color-app-ui-divider)]" />
              <div className="space-y-2 p-4">
                <div className="h-4 w-2/3 rounded bg-[var(--color-app-ui-divider)]" />
                <div className="h-3 w-1/3 rounded bg-[var(--color-app-ui-divider)]" />
                <div className="h-3 w-5/6 rounded bg-[var(--color-app-ui-divider)]" />
                <div className="mt-3 h-6 w-28 rounded bg-[var(--color-app-ui-divider)]" />
              </div>
            </div>
            <div className="overflow-hidden rounded-[18px] border border-[var(--color-app-ui-divider)] bg-white animate-pulse">
              <div className="h-36 bg-[var(--color-app-ui-divider)]" />
              <div className="space-y-2 p-4">
                <div className="h-4 w-1/2 rounded bg-[var(--color-app-ui-divider)]" />
                <div className="h-3 w-1/4 rounded bg-[var(--color-app-ui-divider)]" />
                <div className="h-3 w-4/6 rounded bg-[var(--color-app-ui-divider)]" />
                <div className="mt-3 h-6 w-24 rounded bg-[var(--color-app-ui-divider)]" />
              </div>
            </div>
          </div>
        )}

        {!showLocationDisabledNotice && !nearbyError && !isNearbyLoading && showEmptyNearby && (
          <div className="app-surface-outlined p-3 text-sm text-[var(--color-app-text-secondary-purple)]">
            <p>No hay productos en el radio actual.</p>
            <button type="button" onClick={handleExpandRadius} className="mt-3 inline-flex h-9 items-center rounded-xl border border-[var(--color-app-ui-divider)] px-3 text-xs text-[var(--color-app-text-primary-purple)] transition hover:bg-[var(--color-app-ui-background-soft)]">
              Ampliar radio a 20 km
            </button>
          </div>
        )}

        {!showLocationDisabledNotice && !nearbyError && !isNearbyLoading && nearbyCards.length > 0 && (
          <>
            <div className="space-y-3">
              {nearbyCards.map((card) => {
                const discountPercentage = getDiscountPercentage(card.price, card.originalPrice);

                return (
                  <Link
                    key={`${card.productId}-${card.branchId ?? "no-branch"}`}
                    href={buildProductHref(card)}
                    className="group block overflow-hidden rounded-[18px] border border-[var(--color-app-ui-divider)] bg-white shadow-[var(--app-shadow-surface)] transition-transform hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-app-text-primary-purple)]"
                    aria-label={`Ver producto ${card.name}`}
                  >
                    <div className="relative h-36 overflow-hidden bg-gradient-to-br from-[#b9cb6f] via-[#88a255] to-[#4B236A]">
                      {card.imageUrl ? (
                        <Image
                          src={card.imageUrl}
                          alt={card.name}
                          fill
                          unoptimized
                          className="object-cover"
                        />
                      ) : (
                        <div className="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.25),transparent_45%),radial-gradient(circle_at_80%_10%,rgba(255,255,255,0.2),transparent_35%)]" />
                      )}

                      <div className="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent" />

                      {discountPercentage > 0 && (
                        <span className="absolute right-3 top-3 rounded-full bg-[var(--color-app-text-primary-purple)] px-2.5 py-1 text-[11px] text-white">
                          {discountPercentage}% OFF
                        </span>
                      )}

                      <span className="absolute bottom-3 left-3 inline-flex items-center gap-1 rounded-lg bg-white/95 px-2 py-1 text-xs text-[var(--color-app-text-dark)] shadow-sm backdrop-blur-sm">
                        <MapPin className="h-3.5 w-3.5 text-[var(--color-app-text-primary-purple)]" />
                        {card.distanceKm.toFixed(1)} km
                      </span>
                    </div>

                    <div className="p-4">
                      {/* Sin badge de rating: no existe modelo de reseñas aún (SCRUM-350, post-MVP) */}
                      <div className="mb-2 min-w-0">
                        <h3 className="truncate text-base text-[var(--color-app-text-dark)]">{card.name}</h3>
                        <p className="truncate text-sm text-[var(--color-app-text-secondary-purple)]">{card.category}</p>
                      </div>

                      <p className="truncate text-xs text-[var(--color-app-text-secondary-purple)]">{card.address}</p>

                      <div className="mt-3 flex items-end justify-between gap-3">
                        <div>
                          <p className="text-[28px] leading-none text-[#95af51]">${card.price.toLocaleString("es-CO")}</p>
                          {card.originalPrice > card.price && (
                            <p className="mt-1 text-sm text-[var(--color-app-text-secondary-purple)] line-through">
                              ${card.originalPrice.toLocaleString("es-CO")}
                            </p>
                          )}
                        </div>

                        <div className="text-right">
                          <p className="text-sm text-[var(--color-app-text-primary-purple)]">
                            {card.available} {card.available === 1 ? "disponible" : "disponibles"}
                          </p>
                          {card.pickupSchedule && (
                            <p className="mt-1 inline-flex items-center gap-1 text-xs text-[var(--color-app-text-secondary-purple)]">
                              <Clock3 className="h-3.5 w-3.5" />
                              {card.pickupSchedule}
                            </p>
                          )}
                        </div>
                      </div>
                    </div>
                  </Link>
                );
              })}
            </div>

            {hasMorePages && (
              <button
                type="button"
                onClick={handleLoadMore}
                disabled={isLoadingMore}
                className="mt-3 inline-flex h-9 items-center rounded-xl border border-[var(--color-app-ui-divider)] px-3 text-xs text-[var(--color-app-text-primary-purple)] transition hover:bg-[var(--color-app-ui-background-soft)] disabled:cursor-not-allowed disabled:opacity-60"
              >
                {isLoadingMore ? "Cargando..." : "Cargar mas"}
              </button>
            )}
          </>
        )}
      </div>
    </section>
  );
}
